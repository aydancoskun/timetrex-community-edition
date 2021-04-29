<?php
require_once( '../../../../../includes/global.inc.php' );
require_once( '../../../../GovernmentForms/GovernmentForms.class.php' );
$gf = new GovernmentForms();

$return940 = $gf->getFormObject( 'RETURN940', 'US' );
$return940->TaxPeriodEndDate = '1967-08-13';
$return940->ReturnType = '940PR';
$return940->ein = '000000000';
$return940->BusinessName1 = '#';
$return940->BusinessNameControl = '-';
$return940->AddressLine = '-';
$return940->City = 'A';
$return940->State = 'WY';
$return940->ZIPCode = '00000';

$gf->addForm( $return940 );

$f940_obj = $gf->getFormObject( '940', 'US' );
$f940_obj->setDebug( false );
$f940_obj->setShowBackground( true );
$f940_obj->year = 2009;
$f940_obj->return_type = [ 'a', 'b', 'c', 'd' ];
$f940_obj->ein = '12-3456789';
$f940_obj->name = 'John Doe';
$f940_obj->trade_name = 'ABC Company';
$f940_obj->address = '#1232 Main St';
$f940_obj->city = 'New York';
$f940_obj->state = 'NY';
$f940_obj->zip_code = '12345';

$f940_obj->l3 = 223456.99;
$f940_obj->l4 = 567.01;

$f940_obj->l4a = true;
$f940_obj->l4b = true;
$f940_obj->l4c = true;
$f940_obj->l4d = true;
$f940_obj->l4e = true;

$f940_obj->l5 = 123456.99;

$f940_obj->l13 = 0;

$f940_obj->l15a = true;
$f940_obj->l15b = true;

$f940_obj->l16a = 1001.00;
$f940_obj->l16b = 1002.00;
$f940_obj->l16c = 1003.00;
$f940_obj->l16d = 1004.00;
$gf->addForm( $f940_obj );


$output = $gf->output( 'xml' );
file_put_contents( '940.xml', $output );

Debug::writeToLog();


?>

