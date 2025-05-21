<?php
/**
 * Declaration of 2 columns forest layout with parallax effect
 */
if(count(get_included_files()) < 3 || !defined('YACS')) {
    exit('Script must be included');
}

global $hooks;

$hooks[] = array(
    'id'        => '2col_parallax',
    'type'      => 'layout',
    'supported' => 'article,section,category',
    'script'    => 'layouts/layout_as_2col_parallax/layout_as_2col_parallax.php',
    'style'     => 'layouts/layout_as_2col_parallax/layout_as_2col_parallax.css',
    'label_en'  => '2 Columns  (Parallax)',
    'label_fr'  => '2 Colonnes  (Parallaxe)'
);