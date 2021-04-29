<?php
require_once( '../../../../../includes/global.inc.php' );
require_once( '../../../../GovernmentForms/GovernmentForms.class.php' );

$gf = new GovernmentForms();
$gf->tcpdf_dir = '../tcpdf';
$gf->fpdi_dir = '../fpdi';
$f941worksheet1_obj = $gf->getFormObject( '941worksheet1', 'US' );
$f941worksheet1_obj->setDebug(TRUE);
$f941worksheet1_obj->setShowBackground(TRUE);

$f941worksheet1_obj->year = 2020;

$f941worksheet1_obj->l1a = 9999.99;
$f941worksheet1_obj->l1b = 9999.99;
$f941worksheet1_obj->l1c = 9999.99;
$f941worksheet1_obj->l1d = 9999.99;
$f941worksheet1_obj->l1e = 9999.99;

$f941worksheet1_obj->l1f = 9999.99;
$f941worksheet1_obj->l1g = 9999.99;
$f941worksheet1_obj->l1h = 9999.99;
$f941worksheet1_obj->l1i = 9999.99;
$f941worksheet1_obj->l1j = 9999.99;
$f941worksheet1_obj->l1k = 9999.99;
$f941worksheet1_obj->l1l = 9999.99;

$f941worksheet1_obj->l2a = 9999.99;
$f941worksheet1_obj->l2ai = 9999.99;
$f941worksheet1_obj->l2aii = 9999.99;
$f941worksheet1_obj->l2b = 9999.99;
$f941worksheet1_obj->l2c = 9999.99;
$f941worksheet1_obj->l2d = 9999.99;
$f941worksheet1_obj->l2e = 9999.99;
$f941worksheet1_obj->l2ei = 9999.99;
$f941worksheet1_obj->l2eii = 9999.99;
$f941worksheet1_obj->l2f = 9999.99;
$f941worksheet1_obj->l2g = 9999.99;
$f941worksheet1_obj->l2h = 9999.99;
$f941worksheet1_obj->l2i = 9999.99;
$f941worksheet1_obj->l2j = 9999.99;
$f941worksheet1_obj->l2k = 9999.99;

$f941worksheet1_obj->l3a = 9999.99;
$f941worksheet1_obj->l3b = 9999.99;
$f941worksheet1_obj->l3c = 9999.99;
$f941worksheet1_obj->l3d = 9999.99;
$f941worksheet1_obj->l3e = 9999.99;
$f941worksheet1_obj->l3f = 9999.99;
$f941worksheet1_obj->l3g = 9999.99;
$f941worksheet1_obj->l3h = 9999.99;
$f941worksheet1_obj->l3i = 9999.99;
$f941worksheet1_obj->l3j = 9999.99;
$f941worksheet1_obj->l3k = 9999.99;


$gf->addForm( $f941worksheet1_obj );
$output = $gf->output( 'PDF' );
file_put_contents( '941worksheet1.pdf', $output );
?>