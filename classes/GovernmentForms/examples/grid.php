<?php
/*
 * Use Scribus to get exact coordinates for elements in PDFs!
 */
require_once( '../../../includes/global.inc.php' );
require_once( '../../../includes/CLI.inc.php' );
require_once( '../../GovernmentForms/GovernmentForms.class.php' );
$gf = new GovernmentForms();

$grid_obj = $gf->getFormObject( 'grid' );
$grid_obj->setDebug( false );
$grid_obj->setShowBackground( true );
$grid_obj->setTemplate( '../country/us/templates/w3c.pdf' );
$grid_obj->setTemplatePages( 2 );

$gf->addForm( $grid_obj );

$output = $gf->output( 'PDF' );
file_put_contents( 'grid.pdf', $output );
?>