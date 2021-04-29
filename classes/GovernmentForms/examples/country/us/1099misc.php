<?php

require_once('../../../../../includes/global.inc.php');
require_once('../../../../GovernmentForms/GovernmentForms.class.php');
$gf = new GovernmentForms();
$gf->tcpdf_dir = '../tcpdf';
$gf->fpdi_dir = '../fpdi';

    $f1099m_obj = $gf->getFormObject( '1099misc', 'US' );

    $f1099m_obj->setType( 'government' );
    //$fw2_obj->setType( 'employee' );

    $f1099m_obj->setDebug( FALSE );
    $f1099m_obj->setShowBackground( TRUE);
    $f1099m_obj->year = 2011;
    $f1099m_obj->ein = '12-3456789';
    $f1099m_obj->trade_name = 'ABC Company';
    $f1099m_obj->company_address1 = '#1232 Main St';
    $f1099m_obj->company_address2 = '123 #Suite';
    $f1099m_obj->company_city = 'New York';
    $f1099m_obj->company_state = 'NY';
    $f1099m_obj->company_zip_code = '12345';

    $ee_data = array(
                        'ssn' => '123 456 789',
                        'address1' => '#1232 Main St',
                        'address2' => 'Suite #123',
                        'city' => 'New York',
                        'state' => 'NY',
                        'zip_code' => '12345',

                        //'control_number' => '0001',

                        'first_name' => 'George',
                        'middle_name' => 'george',
                        'last_name' => 'doe',

                        'l4' => 223456.99,
						'l6' => 223456.98,
						'l7' => 223456.97,

                       );
    $f1099m_obj->addRecord( $ee_data );
    $gf->addForm( $f1099m_obj );

	$f1096_obj = $gf->getFormObject( '1096', 'US' );
	$f1096_obj->setDebug( FALSE );
	$f1096_obj->setShowBackground( TRUE);

	$f1096_obj->year = $f1099m_obj->year;
	$f1096_obj->ein = $f1099m_obj->ein;
	$f1096_obj->trade_name = $f1099m_obj->trade_name;
	$f1096_obj->company_address1 = $f1099m_obj->company_address1;
	$f1096_obj->company_address2 = $f1099m_obj->company_address2;
	$f1096_obj->company_city =  $f1099m_obj->company_city;
	$f1096_obj->company_state = $f1099m_obj->company_state;
	$f1096_obj->company_zip_code = $f1099m_obj->company_zip_code;

	$f1096_obj->contact_name = 'John Doe';
	$f1096_obj->contact_phone = '555-555-5555';
	$f1096_obj->contact_phone_ext = '555';
	$f1096_obj->contact_email = 'john@company.com';

	$f1096_obj->l3 = 99;
	$f1096_obj->l4 = 223456.99;
	$f1096_obj->l5 = 223456.98;
	$gf->addForm( $f1096_obj );

//$output = $gf->output( 'xml' );
$output = $gf->output( 'pdf' );
file_put_contents( '/tmp/1099misc.pdf', $output );

//file_put_contents( 'w2.xml', $output );
Debug::writeToLog();
?>
