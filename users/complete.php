<?php
/**
 * help to look for users - VERSION ULTRA-PROPRE
 *
 * Cette version évite tout output parasite avant le JSON
 */

// common definitions and initial processing
include_once '../shared/global.php';

// IMPORTANT : Vider tous les buffers existants
while(ob_get_level()) {
    ob_end_clean();
}

// Démarrer un nouveau buffer propre
ob_start();

// ensure browser always look for fresh data
http::expire(0);

// stop here on scripts/validate.php
if(isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] == 'HEAD'))
    exit;

// some input is mandatory
if(!isset($_REQUEST['term']) || !$_REQUEST['term']) {
    header('Content-Type: application/json; charset=utf-8');
    echo '[]';
    exit;
}

// just for sanity
$_REQUEST['term'] = preg_replace(FORBIDDEN_IN_NAMES, '_', $_REQUEST['term']);

// stop crawlers
if(Surfer::is_crawler()) {
    header('Content-Type: application/json; charset=utf-8');
    echo '[]';
    exit;
}

// Recherche directe avec LIKE pour supporter les mots courts
$pattern = SQL::escape($_REQUEST['term']);

// limit the scope of the request
$where = "users.active='Y'";
if(Surfer::is_logged())
    $where .= " OR users.active='R'";
if(Surfer::is_associate())
    $where .= " OR users.active='N'";
$where = '('.$where.')';

// do not show blocked users, except to associates
if(!Surfer::is_associate())
    $where .= " AND (users.capability IN ('S', 'M', 'A'))";

// Recherche LIKE sur nick_name et full_name
$query = "SELECT id, nick_name, full_name, email FROM ".SQL::table_name('users')." AS users"
    ." WHERE ("
    ."    nick_name LIKE '".SQL::escape($pattern)."%'"
    ."    OR full_name LIKE '%".SQL::escape($pattern)."%'"
    ." )"
    ." AND ".$where
    ." ORDER BY nick_name ASC"
    ." LIMIT 50";

$result = SQL::query($query, FALSE, $context['users_connection']);

// build the autocomplete JSON response
$items = array();
if($result && SQL::count($result)) {
    while($row = SQL::fetch($result)) {
        // value = nick_name (ce qui sera inséré dans le champ)
        $value = $row['nick_name'];
        
        // label = affichage complet (nick_name + full_name si disponible)
        $label = $row['nick_name'];
        if(!empty($row['full_name']) && ($row['full_name'] != $row['nick_name']))
            $label .= ' (' . $row['full_name'] . ')';
        
        $items[] = array('value' => $value, 'label' => $label);
    }
}

// IMPORTANT : Récupérer le buffer et le nettoyer
$buffer = ob_get_clean();

// CRITICAL : Envoyer UNIQUEMENT le JSON, rien d'autre
header('Content-Type: application/json; charset='.$context['charset']);

// format as JSON
if(count($items)) {
    $output = '[';
    $i = 0;
    foreach($items as $item) {
        if($i > 0)
            $output .= ',';
        $i++;
        $output .= '{"value":"'.str_replace('"', '\\"', $item['value']).'","label":"'.str_replace('"', '\\"', $item['label']).'"}';
    }
    $output .= ']';
} else {
    $output = '[]';
}

// Output final
echo $output;

// IMPORTANT : Exit immédiatement pour éviter tout output supplémentaire de finalize_page()
exit;
?>