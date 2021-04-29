<?php
require_once( '../../../../../includes/global.inc.php' );
require_once( '../../../../GovernmentForms/GovernmentForms.class.php' );

$gf = new GovernmentForms();

$f941worksheet1_obj = $gf->getFormObject( '941worksheet1', 'US' );
$f941worksheet1_obj->setDebug(TRUE);
$f941worksheet1_obj->setShowBackground(TRUE);

$f941worksheet1_obj->year = 2020;

$f941worksheet1_obj->l1a = 10000.01;
$f941worksheet1_obj->l1b = 5000.02;
$f941worksheet1_obj->l1e = 4000.03;

$f941worksheet1_obj->l1g = 1000.99;
$f941worksheet1_obj->l1h = 9999.99;
$f941worksheet1_obj->l1i = 1000.10;
$f941worksheet1_obj->l1j = 1000.11;
$f941worksheet1_obj->l1ji = 1000.12;
$f941worksheet1_obj->l1k = 9999.99;
$f941worksheet1_obj->l1l = 9999.99;

$f941worksheet1_obj->l2a = 9999.99;
$f941worksheet1_obj->l2ai = 9999.96;
$f941worksheet1_obj->l2aiii = 9999.98;
$f941worksheet1_obj->l2b = 9999.99;
$f941worksheet1_obj->l2e = 9999.99;
$f941worksheet1_obj->l2ei = 9999.99;
$f941worksheet1_obj->l2eiii = 9999.99;
$f941worksheet1_obj->l2f = 9999.99;

$f941worksheet1_obj->l3a = 9999.99;
$f941worksheet1_obj->l3b = 9999.99;


$gf->addForm( $f941worksheet1_obj );
$output = $gf->output( 'PDF' );
file_put_contents( '941worksheet1.pdf', $output );
?>