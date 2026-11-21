<?php
declare(strict_types=1);
require __DIR__.'/../src/import.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
function respond(mixed $data,int $code=200): never {http_response_code($code);echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);exit;}
set_exception_handler(function(Throwable $e){error_log('Receptbok: '.$e->getMessage());respond(['error'=>'Ett serverfel uppstod. Kontrollera konfiguration och databas.'],500);});
if(!is_file(__DIR__.'/../config.php'))respond(['error'=>'Appen behöver först anslutas till databasen. Se README.md.'],503);
$config=require __DIR__.'/../config.php';
if(empty($config['family_password_hash']))respond(['error'=>'Ett familjelösenord behöver konfigureras.'],503);
session_set_cookie_params(['httponly'=>true,'secure'=>$config['secure_cookies']??true,'samesite'=>'Strict']);
ini_set('session.use_strict_mode','1');session_start();
$_SESSION['csrf']??=bin2hex(random_bytes(24));
$action=$_GET['action']??'session';$method=$_SERVER['REQUEST_METHOD'];
if($method!=='GET' && $method!=='POST')respond(['error'=>'Metoden stöds inte.'],405);
$input=[];
if($method==='POST'){
 if(!hash_equals($_SESSION['csrf'],$_SERVER['HTTP_X_CSRF_TOKEN']??''))respond(['error'=>'Ladda om sidan och försök igen.'],403);
 $raw=file_get_contents('php://input',false,null,0,150001);if(strlen($raw)>150000)respond(['error'=>'Receptet är för stort.'],413);
 $input=json_decode($raw,true);if(!is_array($input))respond(['error'=>'Ogiltig data.'],400);
}
if($action==='session')respond(['authenticated'=>!empty($_SESSION['auth']),'csrf'=>$_SESSION['csrf']]);
$db=new PDO($config['dsn'],$config['user'],$config['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false]);
if($action==='login' && $method==='POST'){
 $key=hash('sha256',$_SERVER['REMOTE_ADDR']??'unknown');$now=time();
 $q=$db->prepare('INSERT INTO login_limits (key_hash,attempts,started) VALUES (?,0,?) ON DUPLICATE KEY UPDATE key_hash=VALUES(key_hash)');$q->execute([$key,$now]);
 $db->beginTransaction();$q=$db->prepare('SELECT attempts,started FROM login_limits WHERE key_hash=? FOR UPDATE');$q->execute([$key]);$limit=$q->fetch(PDO::FETCH_ASSOC);
 $attempts=($now-(int)$limit['started']>900)?0:(int)$limit['attempts'];
 if($attempts>=10){$db->commit();respond(['error'=>'För många försök. Vänta 15 minuter.'],429);}
 $q=$db->prepare('UPDATE login_limits SET attempts=?,started=? WHERE key_hash=?');$q->execute([$attempts+1,$attempts===0?$now:$limit['started'],$key]);$db->commit();
 if(!password_verify((string)($input['password']??''),$config['family_password_hash']))respond(['error'=>'Fel lösenord.'],401);
 $q=$db->prepare('DELETE FROM login_limits WHERE key_hash=?');$q->execute([$key]);
 session_regenerate_id(true);$_SESSION['auth']=true;$_SESSION['csrf']=bin2hex(random_bytes(24));respond(['csrf'=>$_SESSION['csrf']]);
}
if(empty($_SESSION['auth']))respond(['error'=>'Logga in för att öppna familjens receptbok.'],401);
if($action==='logout' && $method==='POST'){$_SESSION=[];session_destroy();respond(['ok'=>true]);}
if($action==='list' && $method==='GET'){
 $out=[];foreach($db->query('SELECT id,body,revision FROM recipes ORDER BY updated_at DESC') as $row){$r=json_decode($row['body'],true);$r['id']=$row['id'];$r['revision']=(int)$row['revision'];$out[]=$r;}respond($out);
}
if($action==='import' && $method==='POST'){
 session_write_close();try{[$html,$url]=fetchHtml((string)($input['url']??''));respond(parseRecipe($html,$url));}catch(RuntimeException $e){respond(['error'=>$e->getMessage()],422);}
}
if($action==='save' && $method==='POST'){
 $r=$input['recipe']??[];
 if(!is_array($r))respond(['error'=>'Ogiltigt recept.'],422);
 $title=plain($r['title']??'');$servings=filter_var($r['servings']??null,FILTER_VALIDATE_FLOAT);
 if(!$title || strlen($title)>300 || $servings===false || $servings<=0 || $servings>1000)respond(['error'=>'Fyll i namn och ett portionsantal mellan 0 och 1000.'],422);
 $clean=['title'=>$title,'description'=>plain($r['description']??''),'servings'=>$servings,'image'=>webUrl((string)($r['image']??'')),'source'=>webUrl((string)($r['source']??'')),'notes'=>plain($r['notes']??''),'yieldOriginal'=>plain($r['yieldOriginal']??''),'favorite'=>(bool)($r['favorite']??false)];
 foreach(['ingredients','steps','categories'] as $field){if(!is_array($r[$field]??null))respond(['error'=>'Ogiltigt fält: '.$field],422);$clean[$field]=array_values(array_filter(array_map('plain',$r[$field])));}
 if(!$clean['ingredients']||!$clean['steps'])respond(['error'=>'Lägg till ingredienser och tillagningssteg.'],422);
 $clean['categories']=array_values(array_unique($clean['categories']));
 // Upprepad text kan vara avsiktlig i stegen.
 $clean['steps']=array_values(array_filter(array_map('plain',$r['steps'])));
 $id=(string)($r['id']??'');$body=json_encode($clean,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
 if($id){if(!preg_match('/^[a-f0-9]{32}$/',$id))respond(['error'=>'Ogiltigt recept-ID.'],422);$q=$db->prepare('UPDATE recipes SET body=?,revision=revision+1 WHERE id=? AND revision=?');$q->execute([$body,$id,(int)($r['revision']??0)]);if(!$q->rowCount())respond(['error'=>'Receptet ändrades på en annan enhet. Kopiera dina ändringar och ladda om innan du sparar igen.'],409);$revision=(int)$r['revision']+1;}
 else{$id=bin2hex(random_bytes(16));$revision=1;$q=$db->prepare('INSERT INTO recipes (id,body) VALUES (?,?)');$q->execute([$id,$body]);}
 respond(array_merge($clean,['id'=>$id,'revision'=>$revision]));
}
if($action==='delete' && $method==='POST'){$q=$db->prepare('DELETE FROM recipes WHERE id=? AND revision=?');$q->execute([(string)($input['id']??''),(int)($input['revision']??0)]);if(!$q->rowCount())respond(['error'=>'Receptet har ändrats. Ladda om och försök igen.'],409);respond(['ok'=>true]);}
respond(['error'=>'Okänd åtgärd.'],404);
