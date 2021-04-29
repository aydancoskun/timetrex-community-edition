<?php

require_once( '../../../../../includes/global.inc.php' );
require_once( '../../../../GovernmentForms/GovernmentForms.class.php' );
$gf = new GovernmentForms();
$gf->tcpdf_dir = '../tcpdf';
$gf->fpdi_dir = '../fpdi';


//$return1040 = $gf->getFormObject( 'RETURN1040', 'US' );
//$return1040->return_created_timestamp = '2001-12-17T09:30:47Z';
//$return1040->year = '1000';
//$return1040->tax_period_begin_date = '1967-08-13';
//$return1040->tax_period_end_date = '1967-08-13';
//$return1040->software_id = '00000000';
//$return1040->originator_efin = '000000';
//$return1040->originator_type_code = 'FinancialAgent';
//$return1040->pin_type_code = 'Practitioner';
//$return1040->jurat_disclosure_code = 'Practitioner PIN';
//$return1040->pin_entered_by = 'Taxpayer';
//$return1040->signature_date = '1967-08-13';
//$return1040->return_type = '1040A';
//$return1040->ssn = '000000000';
//$return1040->name = 'A#';
//$return1040->name_control = 'A';
//$return1040->address1 = '0';
//$return1040->city = 'A';
//$return1040->state = 'SC';
//$return1040->zip_code = '00000';
//$return1040->ip_address = '0.0.0.0';
//$return1040->ip_date = '1967-08-13';
//$return1040->ip_time = '00:00:00';
//$return1040->timezone = 'HS';
//
//$gf->addForm( $return1040 );

$fw2c_obj = $gf->getFormObject( 'w2c', 'US' );
$fw2c_obj->setDebug( true );
$fw2c_obj->setShowBackground( true );
$fw2c_obj->setType( 'government' );
$fw2c_obj->year = 2011;
$fw2c_obj->ein = '123456789';
$fw2c_obj->trade_name = 'ABC Company';
$fw2c_obj->company_address1 = '#1232 Main St';
$fw2c_obj->company_address2 = '123 #Suite';
$fw2c_obj->company_city = 'New York';
$fw2c_obj->company_state = 'NY';
$fw2c_obj->company_zip_code = '12345';





$fw2_obj = $gf->getFormObject( 'w2', 'US' );
$fw2_obj->setType( 'government' );
//$fw2_obj->setType( 'employee' );

$fw2_obj->setDebug( true );
$fw2_obj->setShowBackground( true );
$fw2_obj->year = 2011;
$fw2_obj->ein = '123456789';
$fw2_obj->trade_name = 'ABC Company';
$fw2_obj->company_address1 = '#1232 Main St';
$fw2_obj->company_address2 = '123 #Suite';
$fw2_obj->company_city = 'New York';
$fw2_obj->company_state = 'NY';
$fw2_obj->company_zip_code = '12345';

$ee_data = [
		'user_id'  => '12345', //Used to match other records on, UUID that will never change?
		'ssn'      => '287654321',
		//'ssn'      => '187654321',
		'address1' => '#1232 Main St',
		'address2' => 'Suite #123',
		'city'     => 'New York',
		'state'    => 'NY',
		'zip_code' => '12345',

		'first_name'  => 'John',
		'middle_name' => 'Middle',
		'last_name'   => 'Doe',

		'l1' 		  => 223457.00,
		'l2' 		  => 223456.99,
		'l3' 		  => 2234.98,
		'l4'          => 2234.97,
		'l5'          => 223456.96,
		'l6'          => 2234.95,
		'l7'          => 223456.94,
		'l8'          => 223456.93,
		'l10'         => 223456.92,
		'l11'         => 223456.91,
		'l12a_code'   => 'A2',
		'l12a'        => 223456.90,
		'l12b_code'   => 'B2',
		'l12b'        => 223456.89,
		'l12c_code'   => 'C2',
		'l12c'        => 223456.88,
		'l12d_code'   => 'D2',
		'l12d'        => 223456.87,

		'l13a' => false,
		'l13b' => true,
		'l13c' => false,

		'l14a_name' => 'Test1b',
		'l14a'      => 23.55,
		'l14b_name' => 'Test2b',
		'l14b'      => 255.56,
		'l14c_name' => 'Test3b',
		'l14c'      => 2253345.57,
		'l14d_name' => 'Test4b',
		'l14d'      => 213.58,

		'l15a_state'    => 'NYa',
		'l15a_state_id' => '287654321',
		'l16a'          => '223456789.99',
		'l17a'          => '223456789.99',
		'l18a'          => '223456789.99',
		'l19a'          => '223456789.99',
		'l20a'          => '223456789.99',
		'l20a_district' => 'YONKERSb',

		'l15b_state'    => 'NYb',
		'l15b_state_id' => '2435',
		'l16b'          => '245',
		'l17b'          => '2435.99',
		'l18b'          => '2345.99',
		'l19b'          => '223434556789.99',
		'l20b'          => '22334456789.99',
		'l20b_district' => 'YONKERSb',
];
$fw2_obj->addRecord( $ee_data );


$fw2_prev_obj = $gf->getFormObject( 'w2', 'US' );
$fw2_prev_obj->setType( 'government' );
//$fw2_obj->setType( 'employee' );

$fw2_prev_obj->setDebug( true );
$fw2_prev_obj->setShowBackground( true );
$fw2_prev_obj->year = 2011;
$fw2_prev_obj->ein = '123456789';
$fw2_prev_obj->trade_name = 'ABC Company';
$fw2_prev_obj->company_address1 = '#1232 Main St';
$fw2_prev_obj->company_address2 = '123 #Suite';
$fw2_prev_obj->company_city = 'New York';
$fw2_prev_obj->company_state = 'NY';
$fw2_prev_obj->company_zip_code = '12345';

$ee_data = [
		'user_id'  => '12345', //Used to match other records on, UUID that will never change?
		'ssn'      => '187654321',
		'address1' => '#1232 Main St',
		'address2' => 'Suite #123',
		'city'     => 'New York',
		'state'    => 'NY',
		'zip_code' => '12345',

		'first_name'  => 'John',
		'middle_name' => 'Middle',
		'last_name'   => 'Doe',

		'first_name'  => 'Prev John',
		'middle_name' => 'Prev Mid',
		'last_name'   => 'Prev Doe',

		'l1' 		  => 123457.00,
		'l2' 		  => 123456.99,
		'l3' 		  => 1234.98,
		'l4'          => 1234.97,
		'l5'          => 123456.96,
		'l6'          => 1234.95,
		'l7'          => 123456.94,
		'l8'          => 123456.93,
		'l10'         => 123456.92,
		'l11'         => 123456.91,
		'l12a_code'   => 'A1',
		'l12a'        => 123456.90,
		'l12b_code'   => 'B1',
		'l12b'        => 123456.89,
		'l12c_code'   => 'C1',
		'l12c'        => 123456.88,
		'l12d_code'   => 'D1',
		'l12d'        => 123456.87,

		'l13a' => true,
		'l13b' => false,
		'l13c' => true,

		'l14a_name' => 'Test1a',
		'l14a'      => 13.55,
		'l14b_name' => 'Test2a',
		'l14b'      => 155.56,
		'l14c_name' => 'Test3a',
		'l14c'      => 1253345.57,
		'l14d_name' => 'Test4a',
		'l14d'      => 113.58,

		'l15a_state'    => 'NY',
		'l15a_state_id' => '187654321',
		'l16a'          => '123456789.99',
		'l17a'          => '123456789.99',
		'l18a'          => '123456789.99',
		'l19a'          => '123456789.99',
		'l20a'          => '123456789.99',
		'l20a_district' => 'YONKERSa',

		'l15b_state'    => 'NY',
		'l15b_state_id' => '1435',
		'l16b'          => '145',
		'l17b'          => '1435.99',
		'l18b'          => '1345.99',
		'l19b'          => '123434556789.99',
		'l20b'          => '12334456789.99',
		'l20b_district' => 'YONKERSa',
];
$fw2_prev_obj->addRecord( $ee_data );


//Consider two objects, $fw2_obj and $fw2_prev_obj, that each have 10 employee records.
//They then need to be merged with renamed keys, and converted into w2c records.
// Standard line numbers and such will always be the "Correct" version of the W2.
$fw2c_obj->mergeCorrectAndPreviousW2Objects( $fw2_obj, $fw2_prev_obj );

$gf->addForm( $fw2c_obj );


$output = $gf->output( 'pdf' );
file_put_contents( '/tmp/w2.pdf', $output );
//file_put_contents( 'w2.xml', $output );

Debug::writeToLog();
?>
