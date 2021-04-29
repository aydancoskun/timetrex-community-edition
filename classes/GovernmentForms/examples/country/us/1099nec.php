<?php

require_once( '../../../../../includes/global.inc.php' );
require_once( '../../../../GovernmentForms/GovernmentForms.class.php' );
$gf = new GovernmentForms();

$f1099nec_obj = $gf->getFormObject( '1099nec', 'US' );

$f1099nec_obj->setType( 'government' );
//$fw2_obj->setType( 'employee' );

$f1099nec_obj->setDebug( true );
$f1099nec_obj->setShowBackground( true );
$f1099nec_obj->year = 2020;
$f1099nec_obj->ein = '12-3456789';
$f1099nec_obj->trade_name = 'ABC Company';
$f1099nec_obj->company_address1 = '#1232 Main St';
$f1099nec_obj->company_address2 = '123 #Suite';
$f1099nec_obj->company_city = 'New York';
$f1099nec_obj->company_state = 'NY';
$f1099nec_obj->company_zip_code = '12345';

$ee_data = [
		'ssn'      => '123 456 789',
		'address1' => '#1232 Main St',
		'address2' => 'Suite #123',
		'city'     => 'New York',
		'state'    => 'NY',
		'zip_code' => '12345',

		//'control_number' => '0001',


		'first_name'  => 'George',
		'middle_name' => 'george',
		'last_name'   => 'doe',

		'recipient_id' => 123456999,
		'payer_id' => 123456888,


		'account_number' => 123456,
		'l1' => 223456.99,
		'l4' => 223456.98,

		'l5a' => 223456.97,
		'l6a' => 123456789,
		'l7a' => 223456.95,

		'l5b' => 223456.94,
		'l6b' => 1234567899,
		'l7b' => 223456.93,

];

$f1099nec_obj->addRecord( $ee_data );
$gf->addForm( $f1099nec_obj );

$f1099nec_obj->addRecord( $ee_data );
$gf->addForm( $f1099nec_obj );

$f1096_obj = $gf->getFormObject( '1096', 'US' );
$f1096_obj->setDebug( true );
$f1096_obj->setShowBackground( true );

$f1096_obj->year = $f1099nec_obj->year;
$f1096_obj->ein = $f1099nec_obj->ein;
$f1096_obj->trade_name = $f1099nec_obj->trade_name;
$f1096_obj->company_address1 = $f1099nec_obj->company_address1;
$f1096_obj->company_address2 = $f1099nec_obj->company_address2;
$f1096_obj->company_city = $f1099nec_obj->company_city;
$f1096_obj->company_state = $f1099nec_obj->company_state;
$f1096_obj->company_zip_code = $f1099nec_obj->company_zip_code;

$f1096_obj->contact_name = 'John Doe';
$f1096_obj->contact_phone = '555-555-5555';
$f1096_obj->contact_phone_ext = '555';
$f1096_obj->contact_email = 'john@company.com';

$gf->addForm( $f1096_obj );

//$output = $gf->output( 'xml' );
$output = $gf->output( 'pdf' );
file_put_contents( '/tmp/1099nec.pdf', $output );

//file_put_contents( 'w2.xml', $output );
Debug::writeToLog();
?>

