<?php
require_once('../../../includes/global.inc.php');
require_once('../../ChequeForms/ChequeForms.class.php');
$cf = new ChequeForms();

    $c2398 = $cf->getFormObject( 'MBL2398' );

	$c2398->setDebug(FALSE);
	$c2398->setShowBackground(FALSE);

	$c2398->date = 1342206000;
	$c2398->amount = 724.0900;

	//    $c2398->stub_left_column =  " Mr. Administrator\n Identification #: 000000000000023\n Net Pay: $724.09\n ";
	//    $c2398->stub_right_column = " Pay Start Date: 24-Jun-12\n Pay End Date: 07-Jul-12\n Payment Date: 13-Jul-12\n ";

	$c2398->stub_left_column =  "This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page.";
	$c2398->stub_right_column = "This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. ";

	$c2398->full_name = 'Mr. Administrator';
	$c2398->address1 = '1719 Main St';
	$c2398->address2 = 'Unit #461';
	$c2398->city = 'New York';
	$c2398->province = 'NY';
	$c2398->postal_code = '00420';
	$c2398->country = 'US';
	$c2398->company_name = 'ABC Company';
	$c2398->symbol = '$';

    $cf->addForm( $c2398 );


$output = $cf->output( 'PDF' );
file_put_contents( 'mbl2398.pdf', $output );
?>

