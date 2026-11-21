<?php
declare(strict_types=1);
function plain(mixed $v): string {
 return trim(html_entity_decode(strip_tags(is_scalar($v) ? (string)$v : ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}
function webUrl(string $url, string $base=''): string {
 $url=trim($url);
 if(str_starts_with($url,'//')) $url='https:'.$url;
 if($url && !parse_url($url,PHP_URL_SCHEME) && $base) {
  $b=parse_url($base); $root=$b['scheme'].'://'.$b['host'];
  $url=$root.(str_starts_with($url,'/')?'':rtrim(dirname($b['path']??'/'),'/').'/').$url;
 }
 $p=parse_url($url);
 return $p && in_array(strtolower($p['scheme']??''),['http','https'],true) && isset($p['host']) && !isset($p['user']) && !isset($p['pass']) ? $url : '';
}
function fetchHtml(string $url): array {
 for($redirect=0;$redirect<5;$redirect++) {
  $url=webUrl($url); $p=parse_url($url);
  if(!$url || isset($p['port']) && !in_array($p['port'],[80,443],true)) throw new RuntimeException('Ange en vanlig http- eller https-länk.');
  $host=$p['host'];
  if(filter_var($host,FILTER_VALIDATE_IP) || !str_contains($host,'.')) throw new RuntimeException('Länken måste gå till en offentlig webbplats.');
  $ips=gethostbynamel($host)?:[];
  if(!$ips) throw new RuntimeException('Webbplatsen kunde inte hittas.');
  foreach($ips as $ip) if(!filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE)) throw new RuntimeException('Adressen är inte tillåten.');
  $port=$p['port']??($p['scheme']==='https'?443:80); $body=''; $location='';
  $c=curl_init($url);
  curl_setopt_array($c,[CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_TIMEOUT=>15,CURLOPT_PROTOCOLS=>CURLPROTO_HTTP|CURLPROTO_HTTPS,CURLOPT_PROXY=>'',CURLOPT_RESOLVE=>["$host:$port:".$ips[0]],CURLOPT_USERAGENT=>'FamiljensReceptbok/0.1 (personal recipe importer)',CURLOPT_ENCODING=>'',CURLOPT_HEADERFUNCTION=>function($c,$line)use(&$location){if(stripos($line,'location:')===0)$location=trim(substr($line,9));return strlen($line);},CURLOPT_WRITEFUNCTION=>function($c,$chunk)use(&$body){if(strlen($body)+strlen($chunk)>2500000)return 0;$body.=$chunk;return strlen($chunk);}]);
  $ok=curl_exec($c);$status=curl_getinfo($c,CURLINFO_RESPONSE_CODE);curl_close($c);
  if(!$ok)throw new RuntimeException('Sidan gick inte att hämta. Klistra in recepttexten i stället.');
  if($status>=300 && $status<400 && $location){$url=webUrl($location,$url);continue;}
  if($status!==200)throw new RuntimeException('Webbplatsen tillät inte hämtningen. Klistra in recepttexten i stället.');
  return [$body,$url];
 }
 throw new RuntimeException('Länken omdirigerades för många gånger.');
}
function findRecipe(mixed $x): ?array {
 if(!is_array($x))return null;
 foreach((array)($x['@type']??[]) as $t)if(is_string($t) && preg_match('~(^|[/#])Recipe$~',$t))return $x;
 foreach($x as $child)if(is_array($child) && ($r=findRecipe($child)))return $r;
 return null;
}
function steps(mixed $x): array {
 if(is_string($x))return array_values(array_filter(array_map('plain',preg_split('~(?:<br\s*/?>|</p>|\r?\n)~i',$x))));
 if(!is_array($x))return [];
 if(isset($x['itemListElement']))return steps($x['itemListElement']);
 if(isset($x['text']))return [plain($x['text'])];
 $out=[];foreach($x as $v)$out=array_merge($out,steps($v));return $out;
}
function parseRecipe(string $html,string $url): array {
 $doc=new DOMDocument(); $old=libxml_use_internal_errors(true);$doc->loadHTML('<?xml encoding="utf-8" ?>'.$html,LIBXML_NONET);libxml_clear_errors();libxml_use_internal_errors($old);
 $xp=new DOMXPath($doc);$r=null;
 foreach($xp->query('//script[@type="application/ld+json"]') as $s){$r=findRecipe(json_decode($s->textContent,true));if($r)break;}
 if(!$r)throw new RuntimeException('Inget strukturerat recept hittades. För Instagram eller video: klistra in recepttexten och fyll i fälten.');
 $image=$r['image']??'';if(is_array($image))$image=$image['url']??$image['contentUrl']??$image[0]??'';if(is_array($image))$image=$image['url']??$image['contentUrl']??'';
 $yield=$r['recipeYield']??'';if(is_array($yield))$yield=$yield['value']??$yield[0]??'';$yield=plain($yield);
 $portions=null;if(preg_match('/^\s*(\d+(?:[.,]\d+)?)\s*(?:portion(?:er)?|servings?|pers(?:oner)?|people)?\s*$/iu',$yield,$m))$portions=(float)str_replace(',','.',$m[1]);
 $ingredients=[];foreach((array)($r['recipeIngredient']??[]) as $i){if(is_array($i))$i=implode(' ',array_filter([$i['value']??'',$i['unitText']??$i['unitCode']??'',$i['name']??''],fn($v)=>is_scalar($v)));$ingredients[]=plain($i);}
 return ['title'=>plain($r['name']??''),'description'=>plain($r['description']??''),'image'=>webUrl((string)$image,$url),'servings'=>$portions,'yieldOriginal'=>$yield,'ingredients'=>$ingredients,'steps'=>steps($r['recipeInstructions']??[]),'source'=>$url,'categories'=>[],'notes'=>'','favorite'=>false];
}
