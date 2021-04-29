<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2018 TimeTrex Software Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by
 * the Free Software Foundation with the addition of the following permission
 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
 * WORK IN WHICH THE COPYRIGHT IS OWNED BY TIMETREX, TIMETREX DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact TimeTrex headquarters at Unit 22 - 2475 Dobbin Rd. Suite
 * #292 West Kelowna, BC V4T 2E9, Canada or at email address info@timetrex.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Powered by TimeTrex" logo. If the display of the logo is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Powered by TimeTrex".
 ********************************************************************************/


include_once( 'US.class.php' );

/**
 * @package GovernmentForms
 */
class GovernmentForms_US_940 extends GovernmentForms_US {

	//Testing requirements for Form 940: https://www.irs.gov/e-file-providers/tax-year-2018-94x-mef-ats-information
	public $xml_schema = '94x/94x/IRS940.xsd';
	public $pdf_template = '940.pdf';

	public $payment_cutoff_amount = 7000; //Line5

	public $futa_tax_before_adjustment_rate = 0.006; //Line8

	public $futa_tax_rate = 0.054; //Line9

	//See 940sa.class.php for Credit Reduction States/Rates

	public $line_16_cutoff_amount = 500; //Line16

	public function getFilterFunction( $name ) {
		$variable_function_map = array(
				'year' => 'isNumeric',
				'ein'  => array('stripNonNumeric', 'isNumeric'),
		);

		if ( isset( $variable_function_map[ $name ] ) ) {
			return $variable_function_map[ $name ];
		}

		return FALSE;
	}

	public function getTemplateSchema( $name = NULL ) {
		$template_schema = array(
			//Initialize page1, replace years on template.
			array(
					'page'          => 1,
					'template_page' => 1,
					'value'         => '940 for ' . $this->year,
					'on_background' => TRUE,
					'coordinates'   => array(
							'x'          => 53,
							'y'          => 30,
							'h'          => 28,
							'w'          => 99,
							'halign'     => 'C',
							'fill_color' => array(255, 255, 255),
					),
					'font'          => array(
							'size' => 16,
							'type' => 'B',
					),
			),
			array(
					'value'         => $this->year, //Type of Return section
					'on_background' => TRUE,
					'coordinates'   => array(
							'x'          => 449,
							'y'          => 140,
							'h'          => 8,
							'w'          => 20,
							'halign'     => 'C',
							'fill_color' => array(245, 245, 245),
					),
					'font'          => array(
							'size' => 9,
					),
			),
			array(
					'value'         => '(' . $this->year . ')', //Page Footer
					'on_background' => TRUE,
					'coordinates'   => array(
							'x'          => 556,
							'y'          => 757,
							'h'          => 11,
							'w'          => 25,
							'halign'     => 'C',
							'fill_color' => array(255, 255, 255),
					),
					'font'          => array(
							'size' => 7,
					),
			),
			//Finish initializing page 1.

			'ein'        => array(
					'page'          => 1,
					'template_page' => 1,
					'function'      => 'drawChars', //custom drawing function.
					'coordinates'   => array(
							array(
									'type'   => 'static', //static or relative
									'x'      => 152,
									'y'      => 67,
									'h'      => 18,
									'w'      => 19,
									'halign' => 'C',
							),
							array(
									'x'      => 180,
									'y'      => 67,
									'h'      => 18,
									'w'      => 19,
									'halign' => 'C',
							),
							array(
									'x'      => 218,
									'y'      => 67,
									'h'      => 18,
									'w'      => 19,
									'halign' => 'C',
							),
							array(
									'x'      => 243,
									'y'      => 67,
									'h'      => 18,
									'w'      => 19,
									'halign' => 'C',
							),
							array(
									'x'      => 268,
									'y'      => 67,
									'h'      => 18,
									'w'      => 19,
									'halign' => 'C',
							),
							array(
									'x'      => 294,
									'y'      => 67,
									'h'      => 18,
									'w'      => 19,
									'halign' => 'C',
							),
							array(
									'x'      => 320,
									'y'      => 67,
									'h'      => 18,
									'w'      => 19,
									'halign' => 'C',
							),
							array(
									'x'      => 345,
									'y'      => 67,
									'h'      => 18,
									'w'      => 19,
									'halign' => 'C',
							),
							array(
									'x'      => 370,
									'y'      => 67,
									'h'      => 18,
									'w'      => 19,
									'halign' => 'C',
							),
					),
					'font'          => array(
							'size' => 12,
							'type' => 'B',
					),
			),
			'name'       => array(
					'coordinates' => array(
							'x'      => 136,
							'y'      => 91,
							'h'      => 18,
							'w'      => 252,
							'halign' => 'L',
					),
			),
			'trade_name' => array(
					'coordinates' => array(
							'x'      => 115,
							'y'      => 115,
							'h'      => 18,
							'w'      => 273,
							'halign' => 'L',
					),
			),
			'address'    => array(
					'coordinates' => array(
							'x'      => 79,
							'y'      => 139,
							'h'      => 18,
							'w'      => 310,
							'halign' => 'L',
					),
			),
			'city'       => array(
					'coordinates' => array(
							'x'      => 79,
							'y'      => 174,
							'h'      => 18,
							'w'      => 186,
							'halign' => 'L',
					),
			),
			'state'      => array(
					'coordinates' => array(
							'x'      => 274,
							'y'      => 174,
							'h'      => 18,
							'w'      => 36,
							'halign' => 'C',
					),
			),
			'zip_code'   => array(
					'coordinates' => array(
							'x'      => 317,
							'y'      => 174,
							'h'      => 18,
							'w'      => 72,
							'halign' => 'C',
					),
			),

			'return_type' => array(
					'function'    => 'drawCheckBox',
					'coordinates' => array(
							'a' => array(
									'x'      => 426,
									'y'      => 97,
									'h'      => 11,
									'w'      => 12,
									'halign' => 'C',
							),
							'b' => array(
									'x'      => 426,
									'y'      => 115,
									'h'      => 11,
									'w'      => 12,
									'halign' => 'C',
							),
							'c' => array(
									'x'      => 426,
									'y'      => 133,
									'h'      => 11,
									'w'      => 12,
									'halign' => 'C',
							),
							'd' => array(
									'x'      => 426,
									'y'      => 151,
									'h'      => 11,
									'w'      => 12,
									'halign' => 'C',
							),
					),
					'font'        => array(
							'size' => 10,
							'type' => 'B',
					),
			),

			'l1a' => array(
					'function'    => 'drawChars',
					'coordinates' => array(
							array(
									'x'      => 455,
									'y'      => 270,
									'h'      => 18,
									'w'      => 22,
									'halign' => 'C',
							),
							array(
									'x'      => 490,
									'y'      => 270,
									'h'      => 18,
									'w'      => 23,
									'halign' => 'C',
							),
					),
			),
			'l1b' => array(
					'function'    => 'drawCheckbox',
					'coordinates' => array(
							array(
									'x'      => 454,
									'y'      => 299,
									'h'      => 8,
									'w'      => 10,
									'halign' => 'C',
							),
					),
					'font'        => array(
							'size' => 10,
							'type' => 'B',
					),
			),
			'l2'  => array(
					'function'    => array('filterL2', 'drawCheckbox'),
					'coordinates' => array(
							array(
									'x'      => 454,
									'y'      => 317,
									'h'      => 8,
									'w'      => 10,
									'halign' => 'C',
							),
					),
					'font'        => array(
							'size' => 10,
							'type' => 'B',
					),

			),
			'l3'  => array(
					'function'    => 'drawSplitDecimalFloat',
					'coordinates' => array(
							array(
									'x'      => 454,
									'y'      => 354,
									'h'      => 18,
									'w'      => 95,
									'halign' => 'R',
							),
							array(
									'x'      => 554,
									'y'      => 354,
									'h'      => 18,
									'w'      => 23,
									'halign' => 'C',
							),
					),
			),
			'l4'  => array(
					'function'    => 'drawSplitDecimalFloat',
					'coordinates' => array(
							array(
									'x'      => 310,
									'y'      => 372,
									'h'      => 18,
									'w'      => 95,
									'halign' => 'R',
							),
							array(
									'x'      => 410,
									'y'      => 372,
									'h'      => 18,
									'w'      => 23,
									'halign' => 'C',
							),
					),
			),
			'l4a' => array(
					'function'    => 'drawCheckbox',
					'coordinates' => array(
							array(
									'x'      => 158.5,
									'y'      => 395.5,
									'h'      => 8,
									'w'      => 10,
									'halign' => 'C',
							),
					),
					'font'        => array(
							'size' => 10,
							'type' => 'B',
					),

			),
			'l4b' => array(
					'function'    => 'drawCheckbox',
					'coordinates' => array(
							array(
									'x'      => 158.5,
									'y'      => 406,
									'h'      => 8,
									'w'      => 10,
									'halign' => 'C',
							),
					),
					'font'        => array(
							'size' => 10,
							'type' => 'B',
					),

			),
			'l4c' => array(
					'function'    => 'drawCheckbox',
					'coordinates' => array(
							array(
									'x'      => 310,
									'y'      => 395.5,
									'h'      => 8,
									'w'      => 10,
									'halign' => 'C',
							),
					),
					'font'        => array(
							'size' => 10,
							'type' => 'B',
					),

			),
			'l4d' => array(
					'function'    => 'drawCheckbox',
					'coordinates' => array(
							array(
									'x'      => 310,
									'y'      => 406,
									'h'      => 8,
									'w'      => 10,
									'halign' => 'C',
							),
					),
					'font'        => array(
							'size' => 10,
							'type' => 'B',
					),

			),
			'l4e' => array(
					'function'    => 'drawCheckbox',
					'coordinates' => array(
							array(
									'x'      => 432,
									'y'      => 395.5,
									'h'      => 8,
									'w'      => 10,
									'halign' => 'C',
							),
					),
					'font'        => array(
							'size' => 10,
							'type' => 'B',
					),

			),

			'l5'   => array(
					'function'    => 'drawSplitDecimalFloat',
					'coordinates' => array(
							array(
									'x'      => 310,
									'y'      => 426,
									'h'      => 18,
									'w'      => 95,
									'halign' => 'R',
							),
							array(
									'x'      => 410,
									'y'      => 426,
									'h'      => 18,
									'w'      => 23,
									'halign' => 'C',
							),
					),
			),
			'l6'   => array(
					'function'    => array('calcL6', 'drawSplitDecimalFloat'),
					'coordinates' => array(
							array(
									'x'      => 454,
									'y'      => 444.5,
									'h'      => 18,
									'w'      => 95,
									'halign' => 'R',
							),
							array(
									'x'      => 554,
									'y'      => 444.5,
									'h'      => 18,
									'w'      => 23,
									'halign' => 'C',
							),
					),
			),
			'l7'   => array(
					'function'    => array('calcL7', 'drawSplitDecimalFloat'),
					'coordinates' => array(
							array(
									'x'      => 454,
									'y'      => 468,
									'h'      => 18,
									'w'      => 95,
									'halign' => 'R',
							),
							array(
									'x'      => 554,
									'y'      => 468,
									'h'      => 18,
									'w'      => 23,
									'halign' => 'C',
							),
					),
			),
			'l8'   => array(
					'function'    => array('calcL8', 'drawSplitDecimalFloat'),
					'coordinates' => array(
							array(
									'x'      => 454,
									'y'      => 492.5,
									'h'      => 18,
									'w'      => 95,
									'halign' => 'R',
							),
							array(
									'x'      => 554,
									'y'      => 492.5,
									'h'      => 18,
									'w'      => 23,
									'halign' => 'C',
							),
					),
			),
			'l9'   => array(
					'function'    => array('calcL9', 'drawSplitDecimalFloat'),
					'coordinates' => array(
							array(
									'x'      => 454,
									'y'      => 533,
									'h'      => 18,
									'w'      => 95,
									'halign' => 'R',
							),
							array(
									'x'      => 554,
									'y'      => 533,
									'h'      => 18,
									'w'      => 23,
									'halign' => 'C',
							),
					),
			),
			'l10'  => array(
					'function'    => array('filterL10', 'drawSplitDecimalFloat'),
					'coordinates' => array(
							array(
									'x'      => 454,
									'y'      => 564,
									'h'      => 18,
									'w'      => 95,
									'halign' => 'R',
							),
							array(
									'x'      => 554,
									'y'      => 564,
									'h'      => 18,
									'w'      => 23,
									'halign' => 'C',
							),
					),
			),
			'l11'  => array(
					'function'    =>  array('filterL11', 'drawSplitDecimalFloat'),
					'coordinates' => array(
							array(
									'x'      => 454,
									'y'      => 586,
									'h'      => 18,
									'w'      => 95,
									'halign' => 'R',
							),
							array(
									'x'      => 554,
									'y'      => 586,
									'h'      => 18,
									'w'      => 23,
									'halign' => 'C',
							),
					),
			),
			'l12'  => array(
					'function'    => array('calcL12', 'drawSplitDecimalFloat'),
					'coordinates' => array(
							array(
									'x'      => 454,
									'y'      => 623,
									'h'      => 18,
									'w'      => 95,
									'halign' => 'R',
							),
							array(
									'x'      => 554,
									'y'      => 623,
									'h'      => 18,
									'w'      => 23,
									'halign' => 'C',
							),
					),
			),
			'l13'  => array(
					'function'    => array('filterL13', 'drawSplitDecimalFloat'),
					'draw_zero_value' => TRUE,
					'coordinates' => array(
							array(
									'x'      => 454,
									'y'      => 646,
									'h'      => 18,
									'w'      => 95,
									'halign' => 'R',
							),
							array(
									'x'      => 554,
									'y'      => 646,
									'h'      => 18,
									'w'      => 23,
									'halign' => 'C',
							),
					),
			),
			'l14'  => array(
					'function'    => array('calcL14', 'drawSplitDecimalFloat'),
					'coordinates' => array(
							array(
									'x'      => 454,
									'y'      => 684,
									'h'      => 18,
									'w'      => 95,
									'halign' => 'R',
							),
							array(
									'x'      => 554,
									'y'      => 684,
									'h'      => 18,
									'w'      => 23,
									'halign' => 'C',
							),
					),
			),
			'l15'  => array(
					'function'    => array('calcL15', 'drawSplitDecimalFloat'),
					'coordinates' => array(
							array(
									'x'      => 454,
									'y'      => 708,
									'h'      => 18,
									'w'      => 95,
									'halign' => 'R',
							),
							array(
									'x'      => 554,
									'y'      => 708,
									'h'      => 18,
									'w'      => 23,
									'halign' => 'C',
							),
					),
			),
			'l15a' => array(
					'function'    => array('filterL15', 'drawCheckbox'),
					'coordinates' => array(
							array(
									'x'      => 420.5,
									'y'      => 727,
									'h'      => 8,
									'w'      => 10,
									'halign' => 'C',
							),
					),
					'font'        => array(
							'size' => 10,
							'type' => 'B',
					),
			),
			'l15b' => array(
					'function'    => array('filterL15', 'drawCheckbox'),
					'coordinates' => array(
							array(
									'x'      => 492,
									'y'      => 727,
									'h'      => 8,
									'w'      => 10,
									'halign' => 'C',
							),
					),
					'font'        => array(
							'size' => 10,
							'type' => 'B',
					),

			),

			//Initialize Page 2
			array(
					'page'          => 2,
					'template_page' => 2,
					'value'         => $this->name,
					'coordinates'   => array(
							'x'      => 37,
							'y'      => 56,
							'h'      => 15,
							'w'      => 355,
							'halign' => 'L',
					),
			),
			array(
					'value'       => $this->ein,
					'coordinates' => array(
							'x'      => 400,
							'y'      => 56,
							'h'      => 15,
							'w'      => 175,
							'halign' => 'C',
					),
			),

			array(
					'value'         => '(' . $this->year . ')',
					'on_background' => TRUE,
					'coordinates'   => array(
							'x'          => 554,
							'y'          => 697,
							'h'          => 11,
							'w'          => 25,
							'halign'     => 'C',
							'fill_color' => array(255, 255, 255),
					),
					'font'          => array(
							'size' => 7,
					),
			),
			//Finish initialize Page 2

			'l16a' => array(
					'page'          => 2,
					'template_page' => 2,
					'function'      => array('filterL16', 'drawSplitDecimalFloat'),
					'coordinates'   => array(
							array(
									'x'      => 346,
									'y'      => 120,
									'h'      => 18,
									'w'      => 95,
									'halign' => 'R',
							),
							array(
									'x'      => 446,
									'y'      => 120,
									'h'      => 18,
									'w'      => 23,
									'halign' => 'C',
							),
					),
			),
			'l16b' => array(
					'function'    => array('filterL16', 'drawSplitDecimalFloat'),
					'coordinates' => array(
							array(
									'x'      => 346,
									'y'      => 144,
									'h'      => 18,
									'w'      => 95,
									'halign' => 'R',
							),
							array(
									'x'      => 446,
									'y'      => 144,
									'h'      => 18,
									'w'      => 23,
									'halign' => 'C',
							),
					),
			),
			'l16c' => array(
					'function'    => array('filterL16', 'drawSplitDecimalFloat'),
					'coordinates' => array(
							array(
									'x'      => 346,
									'y'      => 168,
									'h'      => 18,
									'w'      => 95,
									'halign' => 'R',
							),
							array(
									'x'      => 446,
									'y'      => 168,
									'h'      => 18,
									'w'      => 23,
									'halign' => 'C',
							),
					),
			),
			'l16d' => array(
					'function'    => array( 'filterL16', 'calcL16d', 'drawSplitDecimalFloat'),
					'coordinates' => array(
							array(
									'x'      => 346,
									'y'      => 192,
									'h'      => 18,
									'w'      => 95,
									'halign' => 'R',
							),
							array(
									'x'      => 446,
									'y'      => 192,
									'h'      => 18,
									'w'      => 23,
									'halign' => 'C',
							),
					),
			),
			'l17'  => array(
					'function'    => array('calcL17', 'drawSplitDecimalFloat', 'showL17MisMatchTotals'),
					'coordinates' => array(
							array(
									'x'      => 346,
									'y'      => 216.5,
									'h'      => 17,
									'w'      => 95,
									'halign' => 'R',
							),
							array(
									'x'      => 446,
									'y'      => 216.5,
									'h'      => 17,
									'w'      => 22,
									'halign' => 'C',
							),
					),
			),

			//Initialize Page 3
			array(
					'page'          => 3,
					'template_page' => 3,
					'value'         => substr( $this->year, 2, 2 ),
					'on_background' => TRUE,
					'coordinates'   => array( //Large print in payment voucher.
							'x'          => 536,
							'y'          => 582.5,
							'h'          => 0,
							'w'          => 30,
							'halign'     => 'L',
							'fill_color' => array(255, 255, 255),
					),
					'font'          => array(
							'size' => 18,
							'type' => 'B',
					),
			),
			array(
					'value'         => $this->year,
					'on_background' => TRUE,
					'coordinates'   => array(
							'x'          => 258,
							'y'          => 174.5,
							'h'          => 11,
							'w'          => 22,
							'halign'     => 'C',
							'fill_color' => array(255, 255, 255),
					),
					'font'          => array(
							'size' => 10,
					),
			),
			array(
					'value'         => $this->year,
					'on_background' => TRUE,
					'coordinates'   => array(
							'x'          => 397.5,
							'y'          => 260,
							'h'          => 11,
							'w'          => 22,
							'halign'     => 'C',
							'fill_color' => array(255, 255, 255),
					),
					'font'          => array(
							'size' => 10,
					),
			),
			//Finish initialize Page 3

			array(
					'page'          => 3,
					'template_page' => 3,
					'value'         => $this->ein,
					//'function' => 'drawPage3EIN',
					'coordinates'   =>
							array(
									'x'      => 95,
									'y'      => 620,
									'h'      => 15,
									'w'      => 100,
									'halign' => 'C',
							),
					'font'          => array(
							'size' => 10,
					),
			),
			array(
					'function'    => array('calcL14', 'drawSplitDecimalFloat'),
					'coordinates' => array(
							array(
									'x'      => 440,
									'y'      => 613,
									'h'      => 15,
									'w'      => 95,
									'halign' => 'R',
							),
							array(
									'x'      => 542,
									'y'      => 613,
									'h'      => 15,
									'w'      => 32,
									'halign' => 'C',
							),
					),
					'font'        => array(
							'size' => 22,
					),

			),

			array(
					'value'       => $this->trade_name,
					'coordinates' => array(
							'x'      => 229,
							'y'      => 651,
							'h'      => 15,
							'w'      => 250,
							'halign' => 'L',
					),
					'font'        => array(
							'size' => 10,
					),
			),
			array(
					'value'       => $this->address,
					'coordinates' => array(
							'x'      => 229,
							'y'      => 674,
							'h'      => 15,
							'w'      => 250,
							'halign' => 'L',
					),
					'font'        => array(
							'size' => 10,
					),
			),
			array(
					'value'       => $this->city . ', ' . $this->state . ', ' . $this->zip_code,
					'coordinates' => array(
							'x'      => 229,
							'y'      => 698,
							'h'      => 15,
							'w'      => 250,
							'halign' => 'L',
					),
					'font'        => array(
							'size' => 10,
					),
			),
		);

		if ( isset( $template_schema[ $name ] ) ) {
			return $name;
		} else {
			return $template_schema;
		}
	}

	function filterL2( $value ) {
		if ( $this->l11 > 0 ) {
			return TRUE;
		}

		return FALSE;
	}

	function filterL10( $value ) {
		if ( $this->l9 > 0 ) { //If L9 is specified, L10 does not apply.
			return FALSE;
		}

		return $value;
	}
	function filterL11( $value ) {
		if ( $this->l9 > 0 ) { //If L9 is specified, L11 does not apply.
			return FALSE;
		}

		return $value;
	}

	function filterL13( $value, $schema ) {
		if ( $this->l13 != '' ) {
			return $value;
		} else {
			$this->l13 = $this->l12; //If no deposit amount is specified, assume they deposit the amount calculated.
			return $this->l13;
		}

		return FALSE;
	}

	function filterL15( $value ) {
		if ( $this->l15 > 0 ) {
			return $value;
		}

		return FALSE;
	}

	function filterL16( $value ) {
		if ( $this->l12 > $this->line_16_cutoff_amount ) {
			return $value;
		}

		return FALSE;
	}

	function calcL6( $value, $schema ) {
		//Subtotal: Line 4 + Line 5
		$this->l6 = bcadd( $this->l4, $this->l5 );

		return $this->l6;
	}

	function calcL7( $value, $schema ) {
		//Total Taxable FUTA wages: Line 3 - Line 6
		$this->l7 = bcsub( $this->l3, $this->l6 );

		return $this->l7;
	}

	function calcL8( $value, $schema ) {
		//FUTA tax before adjustments
		$this->l8 = round( bcmul( $this->l7, $this->futa_tax_before_adjustment_rate ), 2 );

		return $this->l8;
	}

	function calcL9( $value, $schema ) {
		//If line 9 is specified, line 10 and 11 don't apply.
		if ( $this->l9 == TRUE ) {
			$this->l9 = round( bcmul( $this->l7, $this->futa_tax_rate ), 2);
			$this->l10 = NULL;
			$this->l11 = NULL;

			return $this->l9;
		}

		return FALSE;
	}

	function calcL12( $value, $schema ) {
		//Total FUTA tax after adjustments
		$this->l12 = bcadd( $this->l8, bcadd( $this->l9, bcadd( $this->l10, $this->l11 ) ) );

		return $this->l12;
	}

	function calcL14( $value, $schema ) {
		//Balance Due
		if ( $this->l12 > $this->l13 ) {
			$this->l14 = bcsub( $this->l12, $this->l13 );

			return $this->l14;
		}

		return FALSE;
	}

	function calcL15( $value, $schema ) {
		//Overpayment
		if ( $this->l13 > $this->l12 ) {
			$this->l15 = bcsub( $this->l13, $this->l12 );

			return $this->l15;
		}

		return FALSE;
	}

	//Calculate the 4th quarter amount by taking Line 12 and working backwards.
	function calcL16d( $value, $schema ) {
		//The proper way to handle this is as per the 940 instructions Part 5: "To figure your FUTA tax liability for the fourth quarter, complete Form 940 through line 12. Then copy the amount from line 12 onto line 17. Lastly, subtract the sum of lines 16a through 16c from line 17 and enter the result on line 16d."
		if ( $this->l12 > $this->line_16_cutoff_amount ) {
			$this->l16d = bcsub( $this->l12, bcadd( $this->l16a, bcadd( $this->l16b, $this->l16c ) ) );

			return $this->l16d;
		}

		return FALSE;
	}

	function calcL17( $value, $schema ) {
		//Total tax liability for the year
		if ( $this->l12 > $this->line_16_cutoff_amount ) {
			//$this->l17 = bcadd( $this->l16a, bcadd( $this->l16b, bcadd( $this->l16c, $this->l16d ) ) );
			$this->l17 = $this->l12;

			return $this->l17;
		}

		return FALSE;
	}

	function showL17MisMatchTotals() {
		if ( isset( $this->l12 ) AND isset( $this->l17 ) AND $this->l12 > $this->line_16_cutoff_amount ) {
			$l17_to_l12_diff = abs( round( bcsub( $this->l17, $this->l12 ), 2 ) );
			if ( $l17_to_l12_diff > 0.01 ) {
				Debug::Text( 'L17 seems incorrect, show warning...', __FILE__, __LINE__, __METHOD__, 10 );
				$pdf = $this->getPDFObject();

				//Show warning on Page 2
				$pdf->setTextColor( 255, 0, 0 );
				$pdf->setXY( ( 155 + $this->getTempPageOffsets( 'x' ) + $this->getPageMargins( 'x') ), ( 221 + $this->getTempPageOffsets( 'y' ) + $this->getPageMargins( 'y') ) );

				$pdf->Cell( 165, 13, 'WARNING: Mismatch with Line 12', 1, 0, 'C', 1, FALSE, 1 );
			}
		}

		return TRUE;
	}


	function _outputPDF() {
		//Initialize PDF with template.
		$pdf = $this->getPDFObject();

		if ( $this->getShowBackground() == TRUE ) {
			$pdf->setSourceFile( $this->getTemplateDirectory() . DIRECTORY_SEPARATOR . $this->pdf_template );

			$this->template_index[1] = $pdf->ImportPage( 1 );
			$this->template_index[2] = $pdf->ImportPage( 2 );
			$this->template_index[3] = $pdf->ImportPage( 3 );
		}

		if ( $this->year == '' ) {
			$this->year = $this->getYear();
		}

		//Get location map, start looping over each variable and drawing
		$template_schema = $this->getTemplateSchema();
		if ( is_array( $template_schema ) ) {

			$template_page = NULL;

			foreach ( $template_schema as $field => $schema ) {
				$this->Draw( $this->$field, $schema );
			}
		}

		return TRUE;
	}

	function _outputXML() {

		if ( is_object( $this->getXMLObject() ) ) {
			$xml = $this->getXMLObject();
		} else {
			return FALSE; //No XML object to append too. Needs return940 form first.
		}

		if ( isset( $this->return_type ) ) {
			foreach ( $this->return_type as $return_type ) {
				switch ( $return_type ) {
					case 'b':
						$xml->IRS940->addChild( 'SuccessorEmployer', 'X' );
						break;
					case 'c':
						$xml->IRS940->addChild( 'NoPayments', 'X' );
						break;
					case 'd':
						$xml->IRS940->addChild( 'FinalReturn', 'X' );
						break;
				}
			}
		}

		if ( isset( $this->l1a ) ) {
			$xml->IRS940->addChild( 'SingleStateCode', $this->l1a );
		} elseif ( isset( $this->l1b ) ) {
			$xml->IRS940->addChild( 'MultiStateContribution', $this->l1b );
		}

		if ( isset( $this->l3 ) ) {
			$xml->IRS940->addChild( 'TotalWages', $this->l3 );
		}

		if ( isset( $this->l4 ) ) {
			$xml->IRS940->addChild( 'ExemptWages' );

			$xml->IRS940->ExemptWages->addChild( 'ExemptWagesAmt', $this->l4 );

			$xml->IRS940->ExemptWages->addChild( 'ExemptionCategory' );
			foreach ( range( 'a', 'e' ) as $z ) {
				$col = 'l4' . $z;
				if ( isset( $this->$col ) ) {
					switch ( $z ) {
						case 'a':
							$xml->IRS940->ExemptWages->ExemptionCategory->addChild( 'FringeBenefits', 'X' );
							break;
						case 'b':
							$xml->IRS940->ExemptWages->ExemptionCategory->addChild( 'GroupTermLifeIns', 'X' );
							break;
						case 'c':
							$xml->IRS940->ExemptWages->ExemptionCategory->addChild( 'RetirementPension', 'X' );
							break;
						case 'd':
							$xml->IRS940->ExemptWages->ExemptionCategory->addChild( 'DependentCare', 'X' );
							break;
						case 'e':
							$xml->IRS940->ExemptWages->ExemptionCategory->addChild( 'OtherExemption', 'X' );
							break;
					}
				}

			}
		}

		if ( isset( $this->l5 ) ) {
			$xml->IRS940->addChild( 'WagesOverLimitAmt', $this->l5 );
		}

		if ( $this->calcL6( NULL, NULL ) >= 0 ) {
			$xml->IRS940->addChild( 'TotalExemptWagesAmt', $this->calcL6( NULL, NULL ) );
		}
		if ( $this->calcL7( NULL, NULL ) >= 0 ) {
			$xml->IRS940->addChild( 'TotalTaxableWagesAmt', $this->calcL7( NULL, NULL ) );
		} else {
			$xml->IRS940->addChild( 'TotalTaxableWagesAmt', 0.00 );
		}

		if ( $this->calcL8( NULL, NULL ) >= 0 ) {
			$xml->IRS940->addChild( 'FUTATaxBeforeAdjustmentsAmt', $this->calcL8( NULL, NULL ) );
		}

		if ( $this->calcL9( NULL, NULL ) >= 0 ) {
			$xml->IRS940->addChild( 'MaximumCreditAmt', $this->calcL9( NULL, NULL ) );
		} elseif ( isset( $this->l10 ) ) {
			$xml->IRS940->addChild( 'AdjustmentsToFUTATax' );
			$xml->IRS940->AdjustmentsToFUTATax->addChild( 'FUTAAdjustmentAmt', $this->l10 );

		}

		if ( $this->calcL12( NULL, NULL ) >= 0 ) {
			$xml->IRS940->addChild( 'FUTATaxAfterAdjustments', $this->calcL12( NULL, NULL ) );
		}

		if ( isset( $this->l13 ) ) {
			$xml->IRS940->addChild( 'TotalTaxDepositedAmt', $this->l13 );
		}

		if ( $this->calcL14( NULL, NULL ) >= 0 ) {
			$xml->IRS940->addChild( 'BalanceDue', $this->calcL14( NULL, NULL ) );
		} elseif ( $this->calcL15( NULL, NULL ) >= 0 ) {
			$xml->IRS940->addChild( 'Overpayment' );
			$xml->IRS940->Overpayment->addChild( 'Amount', $this->calcL15( NULL, NULL ) );
			$xml->IRS940->Overpayment->addChild( 'Refund', 'X' );
		}

		foreach ( range( 'a', 'd' ) as $z ) {
			$col = 'l16' . $z;
			if ( isset( $this->$col ) ) {
				switch ( $z ) {
					case 'a':
						$xml->IRS940->addChild( 'Quarter1LiabilityAmt', $this->$col );
						break;
					case 'b':
						$xml->IRS940->addChild( 'Quarter2LiabilityAmt', $this->$col );
						break;
					case 'c':
						$xml->IRS940->addChild( 'Quarter3LiabilityAmt', $this->$col );
						break;
					case 'd':
						$xml->IRS940->addChild( 'Quarter4LiabilityAmt', $this->$col );
						break;
				}
			}

		}

		if ( $this->calcL17( NULL, NULL ) >= 0 ) {
			$xml->IRS940->addChild( 'TotalYearLiabilityAmt', $this->calcL17( NULL, NULL ) );
		}


		return TRUE;
	}


}

?>