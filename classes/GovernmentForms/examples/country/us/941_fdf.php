<?php
require_once( '../../../../../includes/global.inc.php' );
/*
 * Import the PDF into the desktop Scribus application, then save as a PDF to make the form fillable in PHP. This still requires some changes though, such as text alignment in form fields, and checkboxes.
 *   Scribus can also show exact coordinates in the PDF, so that might make the overlay PDF option easier to deal with.
 *   Filling out large forms is quite slow.
 *
 * Dump PDF form field names: pdftk classes/GovernmentForms/country/us/templates/941.orig.pdf dump_data_fields
 *
 * If an error of: "Fast Web View mode is not supported" is returned, use: cpdf in.pdf -o out.pdf
 *                  https://stackoverflow.com/questions/18741208/disable-fast-web-view-on-a-pdf-file
 *
 *
 * ***NOTE*** This only works with AcroForms and not XFA forms which the IRS primarily uses.
 *   pdftk f941.pdf output f941_dropped_xfa.pdf drop_xfa
 *   pdftk f941_dropped_xfa.pdf output f941_dropped_xfa_uncompressed.pdf uncompress
 *
 */


//require_once( '../../../../other/forge_fdf.php' );
//$ffdf = new ForgeFDF();
//
////Dump PDF form field names: pdftk classes/GovernmentForms/country/us/templates/941.orig.pdf dump_data_fields
//
//$pdf_form_url = "http://demo.timetrex.com/classes/GovernmentForms/country/us/941.pdf";
//$fdf_data_strings = [ 'topmostSubform[0].Page1[0].Header[0].EntityArea[0].f1_01[0]' => 'z2z' ];
//
////var_dump( $ffdf->forge_fdf( $pdf_form_url, $fdf_data_strings, $fdf_data_names, $fields_hidden, $fields_readonly ) );
//file_put_contents( '941.fdf', $ffdf->forge_fdf( $pdf_form_url, $fdf_data_strings, [], [], [] ) );


//Alternative method, but still doesn't work with IRS PDFs.
require_once( '../../../../fpdm/fpdm.php' );

$fields = array(
		'f1_1[0]' => '12',
		'f1_2[0]' => '34567',
		//'f1_3[0]' => '3',
		//'f1_4[0]' => '4',
		//'f1_5[0]' => 'f1_5',
		//'f1_6[0]' => 'f1_6',
		//'f1_7[0]' => 'f1_7',
		//'f1_8[0]' => 'f1_8',
		//'f1_9[0]' => 'f1_9',
		//'f1_10[0]' => 'f1_10',

		//'april_cb' => true,
		'c1_1[1]' => true,
		'c1_1[2]' => true,

		//'f1_13[0]' => '1234',
		//'f1_14[0]' => '99',
);

$pdf = new FPDM('f941b.pdf');
$pdf->useCheckboxParser = true; //IMPORTANT: This must be enabled.
//$pdf->verbose = true;
//$pdf->verbose_level = 1;
$pdf->Load($fields, false); // second parameter: false if field values are in ISO-8859-1, true if UTF-8
$pdf->Merge();
//$data = $pdf->Output( '/tmp/', 'test.pdf' );
$data = $pdf->Output( 'S' );
file_put_contents('test.pdf', $data );

Debug::writeToLog();
?>



