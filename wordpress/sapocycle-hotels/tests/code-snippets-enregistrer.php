<?php
// Enregistre et active un snippet via les fonctions internes de Code Snippets (même chemin que l'éditeur).
$_SERVER['HTTP_HOST']='127.0.0.1:8099'; $_SERVER['REQUEST_URI']='/';
require __DIR__.'/wptest/wordpress/wp-load.php';
wp_set_current_user(1);
$code = file_get_contents($argv[1]);
// 1) Validateur de Code Snippets
$v = new Code_Snippets\Utils\Validator($code); $r = $v->validate();
echo 'Validateur : ', $r ? 'ERREUR '.json_encode($r, JSON_UNESCAPED_UNICODE) : 'OK', "\n";
// 2) Enregistrement + test d'exécution + activation (comme le bouton « Enregistrer et activer »)
foreach (Code_Snippets\get_snippets() as $old) { Code_Snippets\delete_snippet($old->id); }
$snip = new Code_Snippets\Model\Snippet(['name'=>'Carte des hôtels SapoCycle','code'=>$code,'scope'=>'global','active'=>true]);
Code_Snippets\test_snippet_code($snip);
echo 'Test d\'exécution : ', $snip->code_error ? 'ERREUR '.json_encode($snip->code_error, JSON_UNESCAPED_UNICODE) : 'OK', "\n";
$saved = Code_Snippets\save_snippet($snip);
echo 'Enregistré id=', $saved->id, ' actif=', var_export($saved->active, true), "\n";
