<?php
require __DIR__.'/../src/import.php';
function check($condition,$message){if(!$condition)throw new RuntimeException($message);echo "OK: $message\n";}
$fixture=['@context'=>'https://schema.org','@graph'=>[['@type'=>'WebPage'],['@type'=>['Recipe'],'name'=>'Pasta &amp; citron','description'=>'<p>Gott &amp; enkelt</p>','recipeYield'=>'4 portioner','image'=>[['url'=>'/pasta.jpg']],'recipeIngredient'=>['400 g pasta','1½ dl grädde'],'recipeInstructions'=>[['@type'=>'HowToSection','itemListElement'=>[['@type'=>'HowToStep','text'=>'Koka pasta.'],['@type'=>'HowToStep','text'=>'Blanda såsen.']]]]]]];
$html='<html><script type="application/ld+json">'.json_encode($fixture).'</script></html>';
$r=parseRecipe($html,'https://example.com/recept/pasta');
check($r['title']==='Pasta & citron','entities');check($r['servings']===4.0,'portions');check($r['image']==='https://example.com/pasta.jpg','relative image');check(count($r['steps'])===2,'nested sections');check(count($r['ingredients'])===2,'ingredients');
$fixture['@graph'][1]['recipeYield']='12 kakor';$r=parseRecipe('<script type="application/ld+json">'.json_encode($fixture).'</script>','https://example.com');check($r['servings']===null,'unknown yield requires review');
check(webUrl('javascript:alert(1)')==='','unsafe scheme');check(webUrl('https://user:secret@example.com')==='','credentials rejected');
try{parseRecipe('<p>No recipe here</p>','https://example.com');throw new Exception('Missing recipe accepted');}catch(RuntimeException $e){check(str_contains($e->getMessage(),'Inget strukturerat'),'missing recipe feedback');}
foreach(['http://127.0.0.1/','http://localhost/','http://example.com:3306/','file:///etc/passwd'] as $url){try{fetchHtml($url);throw new Exception('Unsafe URL accepted');}catch(RuntimeException){echo "OK: URL rejected\n";}}
