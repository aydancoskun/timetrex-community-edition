<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2020 TimeTrex Software Inc.
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
class GovernmentForms_US_941 extends GovernmentForms_US {

	//Testing requirements for Form 941: https://www.irs.gov/e-file-providers/tax-year-2018-94x-mef-ats-information
	public $xml_schema = '94x/94x/IRS941.xsd';

	public $pdf_template = '941.pdf';
	//public $template_offsets = array( -2, +35 ); //x, y
	public $page_offsets = [ 0, 0 ]; //x, y

	public $social_security_rate = 0.124; //Line: 5a2, 5b2

	public $medicare_rate = 0.029;            //Line: 5c2
	public $medicare_additional_rate = 0.009; //Line: 5d2

	public $line_16_cutoff_amount = 2500; //Line 16

	public $schedule_b_total = 0; //Total from F941 Schedule B so we can show a warning if it doesn't match.

	public function getTemplateSchema( $name = null ) {
		$template_schema = [
			//Initialize page1, replace years on template.
			[
					'page'          => 1,
					'template_page' => 1,
					'value'         => 'Form',
					'on_background' => true,
					'coordinates'   => [
							'x'          => 35,
							'y'          => 71,
							'h'          => 23,
							'w'          => 22,
							'halign'     => 'L',
							'fill_color' => [ 255, 255, 255 ],
					],
					'font'          => [
							'size' => 8,
							'type' => '',
					],
			],

			[
					'value'         => '941 for ' . $this->year,
					'on_background' => true,
					'coordinates'   => [
							'x'          => 57,
							'y'          => 66,
							'h'          => 28,
							'w'          => 97,
							'halign'     => 'C',
							'fill_color' => [ 255, 255, 255 ],
					],
					'font'          => [
							'size' => 16,
							'type' => 'B',
					],
			],

			[
					'value'         => $this->year, //Top right, in quarter checkbox section.
					'on_background' => true,
					'coordinates'   => [
							'x'          => 525,
							'y'          => 99.5,
							'h'          => 8,
							'w'          => 21,
							'halign'     => 'C',
							'text_color' => [ 255, 255, 255 ],
							'fill_color' => [ 30, 30, 30 ],
					],
					'font'          => [
							'size' => 10,
							'type' => 'B',
					],
			],

			[
					'value'         => '(Rev. ' . $this->year . ')', //Bottom right of first page.
					'on_background' => true,
					'coordinates'   => [
							'x'          => 518,
							'y'          => 772,
							'h'          => 11,
							'w'          => 45,
							'halign'     => 'C',
							'fill_color' => [ 255, 255, 255 ],
					],
					'font'          => [
							'size' => 7,
					],
			],
			//Finish initializing page 1.

			'ein' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'prefilter' => [ 'stripNonNumeric', 'isNumeric' ], 'draw' => 'drawChars' ], //custom drawing function.
					'coordinates'   => [
							[
									'type'   => 'static', //static or relative
									'x'      => 148,
									'y'      => 100,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							],
							[
									'x'      => 172,
									'y'      => 100,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							],
							[
									'x'      => 209,
									'y'      => 100,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							],
							[
									'x'      => 233,
									'y'      => 100,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							],
							[
									'x'      => 258,
									'y'      => 100,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							],
							[
									'x'      => 283,
									'y'      => 100,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							],
							[
									'x'      => 307,
									'y'      => 100,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							],
							[
									'x'      => 332,
									'y'      => 100,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							],
							[
									'x'      => 357,
									'y'      => 100,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							],
					],
					'font'          => [
							'size' => 12,
							'type' => 'B',
					],
			],

			'name' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 132,
							'y'      => 124,
							'h'      => 18,
							'w'      => 246,
							'halign' => 'L',
					],
			],

			'trade_name' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 112,
							'y'      => 147,
							'h'      => 18,
							'w'      => 267,
							'halign' => 'L',
					],
			],

			'address' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 77,
							'y'      => 170,
							'h'      => 18,
							'w'      => 302,
							'halign' => 'L',
					],
			],

			'city'     => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 76,
							'y'      => 200,
							'h'      => 18,
							'w'      => 182,
							'halign' => 'L',
					],
			],
			'state'    => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 266,
							'y'      => 200,
							'h'      => 18,
							'w'      => 35,
							'halign' => 'C',
					],
			],
			'zip_code' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 308,
							'y'      => 200,
							'h'      => 18,
							'w'      => 70,
							'halign' => 'C',
					],
			],


			'quarter' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'draw' => 'drawCheckBox' ],
					'coordinates'   => [
							1 => [
									'x'      => 413,
									'y'      => 126,
									'h'      => 10,
									'w'      => 10,
									'halign' => 'C',
							],
							2 => [
									'x'      => 413,
									'y'      => 143,
									'h'      => 10,
									'w'      => 10,
									'halign' => 'C',
							],
							3 => [
									'x'      => 413,
									'y'      => 160,
									'h'      => 10,
									'w'      => 10,
									'halign' => 'C',
							],
							4 => [
									'x'      => 413,
									'y'      => 176,
									'h'      => 10,
									'w'      => 10,
									'halign' => 'C',
							],
					],
					'font'          => [
							'size' => 10,
							'type' => 'B',
					],
			],

			'l1' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'prefilter' => [ 'stripNonNumeric', 'isNumeric' ] ],
					'coordinates'   => [
							'x'      => 434,
							'y'      => 298,
							'h'      => 15,
							'w'      => 127,
							'halign' => 'C',
					],
			],

			'l2'  => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'prefilter' => [ 'stripNonFloat', 'isNumeric' ], 'draw' => 'drawSplitDecimalFloat' ],
					'coordinates'   => [
							[
									'x'      => 434,
									'y'      => 322,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 322,
									'h'      => 14,
									'w'      => 26,
									'halign' => 'C',
							],
					],
			],
			'l3'  => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'prefilter' => [ 'stripNonFloat', 'isNumeric' ], 'draw' => 'drawSplitDecimalFloat' ],
					'coordinates'   => [
							[
									'x'      => 434,
									'y'      => 345,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 345,
									'h'      => 14,
									'w'      => 26,
									'halign' => 'C',
							],
					],
			],
			'l4'  => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'draw' => 'drawCheckbox' ],
					'coordinates'   => [
							[
									'x'      => 434,
									'y'      => 370,
									'h'      => 6,
									'w'      => 10,
									'halign' => 'C',
							],
					],
					'font'          => [
							'size' => 8,
							'type' => 'B',
					],
			],
			'l5a' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'prefilter' => [ 'stripNonFloat', 'isNumeric' ], 'draw' => 'drawSplitDecimalFloat' ],
					'coordinates'   => [
							[
									'x'      => 210,
									'y'      => 398,
									'h'      => 14,
									'w'      => 65,
									'halign' => 'R',
							],
							[
									'x'      => 280,
									'y'      => 398,
									'h'      => 14,
									'w'      => 20,
									'halign' => 'C',
							],
					],
			],
			'l5ai' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'draw' => 'drawSplitDecimalFloat' ],
					'coordinates'   => [
							[
									'x'      => 210,
									'y'      => 415,
									'h'      => 14,
									'w'      => 65,
									'halign' => 'R',
							],
							[
									'x'      => 280,
									'y'      => 415,
									'h'      => 14,
									'w'      => 20,
									'halign' => 'C',
							],
					],
			],
			'l5aii' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'draw' => 'drawSplitDecimalFloat' ],
					'coordinates'   => [
							[
									'x'      => 210,
									'y'      => 432,
									'h'      => 14,
									'w'      => 65,
									'halign' => 'R',
							],
							[
									'x'      => 280,
									'y'      => 432,
									'h'      => 14,
									'w'      => 20,
									'halign' => 'C',
							],
					],
			],
			'l5b' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'prefilter' => [ 'stripNonFloat', 'isNumeric' ], 'draw' => 'drawSplitDecimalFloat' ],
					'coordinates'   => [
							[
									'x'      => 210,
									'y'      => 450,
									'h'      => 14,
									'w'      => 65,
									'halign' => 'R',
							],
							[
									'x'      => 280,
									'y'      => 450,
									'h'      => 14,
									'w'      => 20,
									'halign' => 'C',
							],
					],
			],
			'l5c' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'prefilter' => [ 'stripNonFloat', 'isNumeric' ], 'draw' => 'drawSplitDecimalFloat' ],
					'coordinates'   => [
							[
									'x'      => 210,
									'y'      => 468,
									'h'      => 14,
									'w'      => 65,
									'halign' => 'R',
							],
							[
									'x'      => 280,
									'y'      => 468,
									'h'      => 14,
									'w'      => 20,
									'halign' => 'C',
							],
					],
			],
			'l5d' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'prefilter' => [ 'stripNonFloat', 'isNumeric' ], 'draw' => 'drawSplitDecimalFloat' ],
					'coordinates'   => [
							[
									'x'      => 210,
									'y'      => 491,
									'h'      => 14,
									'w'      => 65,
									'halign' => 'R',
							],
							[
									'x'      => 280,
									'y'      => 491,
									'h'      => 14,
									'w'      => 20,
									'halign' => 'C',
							],
					],
			],

			'l5a2'   => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL5A2', 'draw' => 'drawSplitDecimalFloat' ],
					'coordinates'   => [
							[
									'x'      => 343,
									'y'      => 398,
									'h'      => 14,
									'w'      => 66,
									'halign' => 'R',
							],
							[
									'x'      => 414,
									'y'      => 398,
									'h'      => 14,
									'w'      => 19,
									'halign' => 'C',
							],
					],
			],
			'l5ai2' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL5Ai2', 'draw' => 'drawSplitDecimalFloat' ],
					'coordinates'   => [
							[
									'x'      => 343,
									'y'      => 415,
									'h'      => 14,
									'w'      => 66,
									'halign' => 'R',
							],
							[
									'x'      => 414,
									'y'      => 415,
									'h'      => 14,
									'w'      => 19,
									'halign' => 'C',
							],
					],
			],
			'l5aii2' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL5Aii2', 'draw' => 'drawSplitDecimalFloat' ],
					'coordinates'   => [
							[
									'x'      => 343,
									'y'      => 432,
									'h'      => 14,
									'w'      => 66,
									'halign' => 'R',
							],
							[
									'x'      => 414,
									'y'      => 432,
									'h'      => 14,
									'w'      => 19,
									'halign' => 'C',
							],
					],
			],
			'l5b2' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL5B2', 'draw' => 'drawSplitDecimalFloat' ],
					'coordinates'   => [
							[
									'x'      => 343,
									'y'      => 450,
									'h'      => 14,
									'w'      => 66,
									'halign' => 'R',
							],
							[
									'x'      => 414,
									'y'      => 450,
									'h'      => 14,
									'w'      => 19,
									'halign' => 'C',
							],
					],
			],
			'l5c2' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL5C2', 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'coordinates'   => [
							[
									'x'      => 343,
									'y'      => 468,
									'h'      => 14,
									'w'      => 66,
									'halign' => 'R',
							],
							[
									'x'      => 414,
									'y'      => 468,
									'h'      => 14,
									'w'      => 19,
									'halign' => 'C',
							],
					],
			],
			'l5d2' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL5D2', 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'coordinates'   => [
							[
									'x'      => 343,
									'y'      => 491,
									'h'      => 14,
									'w'      => 66,
									'halign' => 'R',
							],
							[
									'x'      => 414,
									'y'      => 491,
									'h'      => 14,
									'w'      => 19,
									'halign' => 'C',
							],
					],
			],
			'l5e'  => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL5e', 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'coordinates'   => [
							[
									'x'      => 434,
									'y'      => 514,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 514,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],
			'l5f'  => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'coordinates'   => [
							[
									'x'      => 434,
									'y'      => 538,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 538,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],
			'l6'   => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL6', 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'coordinates'   => [
							[
									'x'      => 434,
									'y'      => 561,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 561,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],
			'l7'   => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL7', 'draw' => [ 'drawSplitDecimalFloat', 'showL5Warning' ] ], //showL5Warning requires calcL7 to be run first.
					'coordinates'   => [
							[
									'x'      => 434,
									'y'      => 585,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 585,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],
			'l8'   => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'draw' => 'drawSplitDecimalFloat' ],
					'coordinates'   => [
							[
									'x'      => 434,
									'y'      => 608,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 608,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],
			'l9'   => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'prefilter' => [ 'stripNonFloat', 'isNumeric' ], 'draw' => 'drawSplitDecimalFloat' ],
					'coordinates'   => [
							[
									'x'      => 434,
									'y'      => 631,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 631,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],

			'l10' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL10', 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'coordinates'   => [
							[
									'x'      => 434,
									'y'      => 655,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 655,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],
			'l11a' => [  //Qualified Small Busiess payroll tax credit.
						 'page'          => 1,
						 'template_page' => 1,
						 'function'      => [ 'draw' => 'drawSplitDecimalFloat' ],
						 'coordinates'   => [
								 [
										 'x'      => 434,
										 'y'      => 678,
										 'h'      => 14,
										 'w'      => 99,
										 'halign' => 'R',
								 ],
								 [
										 'x'      => 539,
										 'y'      => 678,
										 'h'      => 14,
										 'w'      => 22,
										 'halign' => 'C',
								 ],
						 ],
			],
			'l11b' => [ //Nonrefundable portion of credit for qualified sick and family leave from Worksheet 1
						'page'          => 1,
						'template_page' => 1,
						'function'      => [ 'draw' => 'drawSplitDecimalFloat' ],
						'coordinates'   => [
								[
										'x'      => 434,
										'y'      => 701,
										'h'      => 14,
										'w'      => 99,
										'halign' => 'R',
								],
								[
										'x'      => 539,
										'y'      => 701,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
			],
			'l11c' => [ //Nonrefundable portion of employee retention credit Worksheet 1
						'page'          => 1,
						'template_page' => 1,
						'function'      => [ 'draw' => 'drawSplitDecimalFloat' ],
						'coordinates'   => [
								[
										'x'      => 434,
										'y'      => 725,
										'h'      => 14,
										'w'      => 99,
										'halign' => 'R',
								],
								[
										'x'      => 539,
										'y'      => 725,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
			],

			//Initialize Page 2
			[
					'page'          => 2,
					'template_page' => 2,
					'value'         => $this->name,
					'coordinates'   => [
							'x'      => 36,
							'y'      => 89,
							'h'      => 15,
							'w'      => 350,
							'halign' => 'L',
					],
			],
			[
					'value'       => $this->ein,
					'coordinates' => [
							'x'      => 398,
							'y'      => 89,
							'h'      => 15,
							'w'      => 175,
							'halign' => 'C',
					],
			],
			[
					'value'         => '(Rev. ' . $this->year . ')',
					'on_background' => true,
					'coordinates'   => [
							'x'          => 518,
							'y'          => 620.5,
							'h'          => 11,
							'w'          => 45,
							'halign'     => 'C',
							'fill_color' => [ 255, 255, 255 ],
					],
					'font'          => [
							'size' => 7,
					],
			],
			//Finish initialize Page 2


			'l11d' => [ //Nonrefundable portion of employee retention credit Worksheet 1
						'page'          => 2,
						'template_page' => 2,
						'function'      => [ 'calc' => 'calcL11d', 'draw' => [ 'drawSplitDecimalFloat' ] ],
						'coordinates'   => [
								[
										'x'      => 434,
										'y'      => 124,
										'h'      => 14,
										'w'      => 99,
										'halign' => 'R',
								],
								[
										'x'      => 539,
										'y'      => 124,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
			],

			'l12' => [  //Total Tases after adjustments and credits
						'page'          => 2,
						'template_page' => 2,
						'function'      => [ 'calc' => 'calcL12', 'draw' => [ 'drawSplitDecimalFloat', 'showSBMisMatchTotals' ] ],
						'coordinates'   => [
								[
										'x'      => 434,
										'y'      => 147,
										'h'      => 14,
										'w'      => 99,
										'halign' => 'R',
								],
								[
										'x'      => 539,
										'y'      => 147,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
			],

			'l13a'  => [
					'page'            => 2,
					'template_page'   => 2,
					'function'        => [ 'calc' => 'calcL13a', 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'draw_zero_value' => true,
					'coordinates'     => [
							[
									'x'      => 434,
									'y'      => 177,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 177,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],
			'l13b'  => [
					'page'            => 2,
					'template_page'   => 2,
					'function'        => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'draw_zero_value' => true,
					'coordinates'     => [
							[
									'x'      => 434,
									'y'      => 200,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 200,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],
			'l13c'  => [
					'page'            => 2,
					'template_page'   => 2,
					'function'        => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'draw_zero_value' => true,
					'coordinates'     => [
							[
									'x'      => 434,
									'y'      => 223,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 223,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],
			'l13d'  => [
					'page'            => 2,
					'template_page'   => 2,
					'function'        => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'draw_zero_value' => true,
					'coordinates'     => [
							[
									'x'      => 434,
									'y'      => 246,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 246,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],
			'l13e'  => [
					'page'            => 2,
					'template_page'   => 2,
					'function'        => [ 'calc' => 'calcL13e', 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'draw_zero_value' => true,
					'coordinates'     => [
							[
									'x'      => 434,
									'y'      => 269,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 269,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],
			'l13f'  => [
					'page'            => 2,
					'template_page'   => 2,
					'function'        => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'draw_zero_value' => true,
					'coordinates'     => [
							[
									'x'      => 434,
									'y'      => 293,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 293,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],
			'l13g'  => [
					'page'            => 2,
					'template_page'   => 2,
					'function'        => [ 'calc' => 'calcL13g', 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'draw_zero_value' => true,
					'coordinates'     => [
							[
									'x'      => 434,
									'y'      => 316,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 316,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],

			'l14'  => [
					'page'            => 2,
					'template_page'   => 2,
					'function'        => [ 'calc' => 'calcL14', 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'draw_zero_value' => true,
					'coordinates'     => [
							[
									'x'      => 434,
									'y'      => 340,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 340,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],
			'l15'  => [
					'page'          => 2,
					'template_page' => 2,
					'function'      => [ 'calc' => 'calcL15', 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'coordinates'   => [
							[
									'x'      => 294,
									'y'      => 363,
									'h'      => 14,
									'w'      => 65,
									'halign' => 'R',
							],
							[
									'x'      => 362,
									'y'      => 363,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],
			'l15a' => [
					'page'          => 2,
					'template_page' => 2,
					'function'      => [ 'draw' => [ 'filterL15A', 'drawCheckbox' ] ],
					'coordinates'   => [
							[
									'x'      => 434,
									'y'      => 366,
									'h'      => 6,
									'w'      => 10,
									'halign' => 'C',
							],
					],
					'font'          => [
							'size' => 8,
							'type' => 'B',
					],
			],
			'l15b' => [
					'page'          => 2,
					'template_page' => 2,
					'function'      => [ 'draw' => [ 'filterL15B', 'drawCheckbox' ] ],
					'coordinates'   => [
							[
									'x'      => 504,
									'y'      => 366,
									'h'      => 6,
									'w'      => 10,
									'halign' => 'C',
							],
					],
					'font'          => [
							'size' => 8,
							'type' => 'B',
					],
			],


			//Put this after Month1,Month2,Month3 are set, as we can automatically determine it for the most part.
			'l16'  => [
					'page'          => 2,
					'template_page' => 2,
					'function'      => [ 'draw' => [ 'filterL16', 'drawCheckbox' ] ],
					'coordinates'   => [
							'a' => [
									'x'      => 106,
									'y'      => 421,
									'h'      => 6,
									'w'      => 10,
									'halign' => 'C',
							],
							'b' => [
									'x'      => 106,
									'y'      => 467,
									'h'      => 6,
									'w'      => 10,
									'halign' => 'C',
							],
							'c' => [
									'x'      => 106,
									'y'      => 579,
									'h'      => 6,
									'w'      => 10,
									'halign' => 'C',
							],

					],
					'font'          => [
							'size' => 8,
							'type' => 'B',
					],
			],

			'l16_month1'      => [
					'page'          => 2,
					'template_page' => 2,
					'function'      => [ 'prefilter' => [ 'stripNonFloat', 'isNumeric' ], 'draw' => 'drawSplitDecimalFloat' ],
					'coordinates'   => [
							[
									'x'      => 224,
									'y'      => 497,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 325,
									'y'      => 497,
									'h'      => 14,
									'w'      => 26,
									'halign' => 'C',
							],
					],
			],
			'l16_month2'      => [
					'page'          => 2,
					'template_page' => 2,
					'function'      => [ 'prefilter' => [ 'stripNonFloat', 'isNumeric' ], 'draw' => 'drawSplitDecimalFloat' ],
					'coordinates'   => [
							[
									'x'      => 224,
									'y'      => 517,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 325,
									'y'      => 517,
									'h'      => 14,
									'w'      => 26,
									'halign' => 'C',
							],
					],
			],
			'l16_month3'      => [
					'page'          => 2,
					'template_page' => 2,
					'function'      => [ 'prefilter' => [ 'stripNonFloat', 'isNumeric' ], 'draw' => 'drawSplitDecimalFloat' ],
					'coordinates'   => [
							[
									'x'      => 224,
									'y'      => 539,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 325,
									'y'      => 539,
									'h'      => 14,
									'w'      => 26,
									'halign' => 'C',
							],
					],
			],
			'l16_month_total' => [
					'page'          => 2,
					'template_page' => 2,
					'function'      => [ 'calc' => 'calcL16MonthTotal', 'draw' => [ 'drawSplitDecimalFloat', 'showL16MisMatchTotals' ] ],
					'coordinates'   => [
							[
									'x'      => 224,
									'y'      => 560,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 325,
									'y'      => 560,
									'h'      => 14,
									'w'      => 26,
									'halign' => 'C',
							],
					],
			],

			//Initialize Page 3
			[
					'page'          => 3,
					'template_page' => 3,
					'value'         => $this->name,
					'coordinates'   => [
							'x'      => 36,
							'y'      => 89,
							'h'      => 15,
							'w'      => 350,
							'halign' => 'L',
					],
			],
			[
					'value'       => $this->ein,
					'coordinates' => [
							'x'      => 398,
							'y'      => 89,
							'h'      => 15,
							'w'      => 175,
							'halign' => 'C',
					],
			],
			[
					'value'         => '(Rev. ' . $this->year . ')',
					'on_background' => true,
					'coordinates'   => [
							'x'          => 519,
							'y'          => 719.5,
							'h'          => 11,
							'w'          => 45,
							'halign'     => 'C',
							'fill_color' => [ 255, 255, 255 ],
					],
					'font'          => [
							'size' => 7,
					],
			],
			//Finish initialize Page 3

			'l19'  => [
					'page'            => 3,
					'template_page'   => 3,
					'function'        => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'draw_zero_value' => true,
					'coordinates'     => [
							[
									'x'      => 434,
									'y'      => 182,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 182,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],
			'l20'  => [
					'page'            => 3,
					'template_page'   => 3,
					'function'        => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'draw_zero_value' => true,
					'coordinates'     => [
							[
									'x'      => 434,
									'y'      => 205,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 205,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],
			'l21'  => [
					'page'            => 3,
					'template_page'   => 3,
					'function'        => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'draw_zero_value' => true,
					'coordinates'     => [
							[
									'x'      => 434,
									'y'      => 229,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 229,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],
			'l22'  => [
					'page'            => 3,
					'template_page'   => 3,
					'function'        => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'draw_zero_value' => true,
					'coordinates'     => [
							[
									'x'      => 434,
									'y'      => 252,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 252,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],
			'l23'  => [
					'page'            => 3,
					'template_page'   => 3,
					'function'        => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'draw_zero_value' => true,
					'coordinates'     => [
							[
									'x'      => 434,
									'y'      => 275,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 275,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],
			'l24'  => [
					'page'            => 3,
					'template_page'   => 3,
					'function'        => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'draw_zero_value' => true,
					'coordinates'     => [
							[
									'x'      => 434,
									'y'      => 305,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 305,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],
			'l25'  => [
					'page'            => 3,
					'template_page'   => 3,
					'function'        => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'draw_zero_value' => true,
					'coordinates'     => [
							[
									'x'      => 434,
									'y'      => 334,
									'h'      => 14,
									'w'      => 99,
									'halign' => 'R',
							],
							[
									'x'      => 539,
									'y'      => 334,
									'h'      => 14,
									'w'      => 22,
									'halign' => 'C',
							],
					],
			],

			//Initialize Page 5 (Page 4 was left blank)
			[
					'page'          => 5,
					'template_page' => 5,
					'value'         => substr( $this->year, 2, 2 ),
					'on_background' => true,
					'coordinates'   => [
							'x'          => 521,
							'y'          => 587,
							'h'          => 0,
							'w'          => 30,
							'halign'     => 'L',
							'fill_color' => [ 255, 255, 255 ],
					],
					'font'          => [
							'size' => 20,
							'type' => 'B',
					],
			],
			//Finish initialize Page 5

			[
					'page'          => 5,
					'template_page' => 5,
					'function'      => [ 'draw' => 'drawPage3EIN' ],
					'coordinates'   => [
							[
									'x'      => 54,
									'y'      => 630,
									'h'      => 15,
									'w'      => 30,
									'halign' => 'C',
							],
							[
									'x'      => 87,
									'y'      => 630,
									'h'      => 15,
									'w'      => 50,
									'halign' => 'C',
							],
					],
					'font'          => [
							'size' => 10,
					],
			],

			[
					'page'          => 5,
					'template_page' => 5,
					'function'      => [ 'calc' => 'calcL14', 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'coordinates'   => [
							[
									'x'      => 423,
									'y'      => 620,
									'h'      => 17,
									'w'      => 95,
									'halign' => 'R',
							],
							[
									'x'      => 526,
									'y'      => 620,
									'h'      => 17,
									'w'      => 32,
									'halign' => 'C',
							],
					],
					'font'          => [
							'size' => 22,
					],
			],

			[
					'page'          => 5,
					'template_page' => 5,
					'value'         => $this->trade_name,
					'coordinates'   => [
							'x'      => 229,
							'y'      => 656,
							'h'      => 15,
							'w'      => 250,
							'halign' => 'L',
					],
					'font'          => [
							'size' => 10,
					],
			],
			[
					'page'          => 5,
					'template_page' => 5,
					'value'         => $this->address,
					'coordinates'   => [
							'x'      => 229,
							'y'      => 680,
							'h'      => 15,
							'w'      => 250,
							'halign' => 'L',
					],
					'font'          => [
							'size' => 10,
					],
			],
			[
					'page'          => 5,
					'template_page' => 5,
					'value'         => $this->city . ', ' . $this->state . ', ' . $this->zip_code,
					'coordinates'   => [
							'x'      => 229,
							'y'      => 703,
							'h'      => 15,
							'w'      => 250,
							'halign' => 'L',
					],
					'font'          => [
							'size' => 10,
					],
			],
			[
					'page'          => 5,
					'template_page' => 5,
					'function'      => [ 'draw' => [ 'drawPage3Quarter', 'drawCheckBox' ] ],
					'coordinates'   => [
							1 => [
									'x'      => 49,
									'y'      => 670,
									'h'      => 10,
									'w'      => 11,
									'halign' => 'C',
							],
							2 => [
									'x'      => 49,
									'y'      => 699,
									'h'      => 10,
									'w'      => 11,
									'halign' => 'C',
							],
							3 => [
									'x'      => 134,
									'y'      => 670,
									'h'      => 10,
									'w'      => 11,
									'halign' => 'C',
							],
							4 => [
									'x'      => 134,
									'y'      => 699,
									'h'      => 10,
									'w'      => 11,
									'halign' => 'C',
							],
					],
					'font'          => [
							'size' => 10,
							'type' => 'B',
					],
			],

		];

		if ( isset( $template_schema[$name] ) ) {
			return $name;
		} else {
			return $template_schema;
		}
	}

	function filterL15A( $value, $schema ) {
		if ( $this->l15 > 0 ) {
			return $value;
		}

		return false;
	}

	function filterL15B( $value, $schema ) {
		if ( $this->l15 > 0 ) {
			return $value;
		}

		return false;
	}

	function filterL16( $value, $schema ) {
		if ( $this->l12 < $this->line_16_cutoff_amount ) {
			$value = [ 'a' ];
			unset( $this->l16_month1, $this->l16_month2, $this->l16_month3, $this->l16_month_total );
		} else if ( $this->l16_month1 > 0 || $this->l16_month2 > 0 || $this->l16_month3 > 0 ) {
			$value = [ 'b' ];
		} else {
			$value = [ 'c' ];
		}

		return $value;
	}

	function drawPage3Quarter( $value, $schema ) {
		return $this->quarter;
	}

	function drawPage3EIN( $value, $schema ) {
		$value = $this->ein;

		$this->Draw( substr( $value, 0, 2 ), $this->getSchemaSpecificCoordinates( $schema, 0 ) );
		$this->Draw( substr( $value, 2, 7 ), $this->getSchemaSpecificCoordinates( $schema, 1 ) );

		return true;
	}

	function calcL5A2( $value = null, $schema = null ) {
		$this->l5a2 = $this->MoneyFormat( bcmul( $this->l5a, $this->social_security_rate ) );

		return $this->l5a2;
	}

	function calcL5Ai2( $value = null, $schema = null ) {
		$this->l5ai2 = $this->MoneyFormat( bcmul( $this->l5ai, bcdiv( $this->social_security_rate, 2 ) ) );

		return $this->l5ai2;
	}
	function calcL5Aii2( $value = null, $schema = null ) {
		$this->l5aii2 = $this->MoneyFormat( bcmul( $this->l5aii, bcdiv( $this->social_security_rate, 2 ) ) );

		return $this->l5aii2;
	}

	function calcL5B2( $value = null, $schema = null ) {
		$this->l5b2 = $this->MoneyFormat( bcmul( $this->l5b, $this->social_security_rate ) );

		return $this->l5b2;
	}

	function calcL5C2( $value = null, $schema = null ) {
		$this->l5c2 = $this->MoneyFormat( bcmul( $this->l5c, $this->medicare_rate ) );

		return $this->l5c2;
	}

	function calcL5D2( $value = null, $schema = null ) {
		$this->l5d2 = $this->MoneyFormat( bcmul( $this->l5d, $this->medicare_additional_rate ) );

		return $this->l5d2;
	}

	function calcL5E( $value = null, $schema = null ) {
		$this->l5e = bcadd( bcadd( bcadd( bcadd( bcadd( $this->l5a2, $this->l5b2 ), $this->l5c2 ), $this->l5d2 ), $this->l5ai2 ), $this->l5aii2 );

		if ( $this->l5e > 0 ) {
			$this->l4 = true;
		} else {
			$this->l4 = false;
		}

		return $this->l5e;
	}

	function calcL6( $value = null, $schema = null ) {
		$this->l6 = bcadd( bcadd( $this->l3, $this->l5e ), $this->l5f );

		return $this->l6;
	}

	function showL5Warning() {
		if ( isset( $this->l5_actual_deducted ) ) {
			$l5e_actual_diff = round( bcsub( $this->l5_actual_deducted, $this->l5e ), 2 );
			Debug::Text( 'L5 Actual Deducted: ' . $this->l5_actual_deducted . ' L5e: ' . $this->l5e, __FILE__, __LINE__, __METHOD__, 10 );


			$threshold_diff = abs( $this->l7 * 2 );
			if ( $threshold_diff == 0 ) {
				$threshold_diff = 0.01; //Don't show warning if its less than 0.01. This can happen due to PayrollDeduction and how it used to add regular medicare and additional medicare together, then round. It was later switched to rounding them separately before adding.
			}
			Debug::Text( 'L5e Actual Difference: ' . $l5e_actual_diff . ' L7: ' . $this->l7 . ' Threshold Diff: ' . $threshold_diff, __FILE__, __LINE__, __METHOD__, 10 );

			//Only show warning if Line 13a (Total Deposits for Quarter) is *not* specified. If it is specified assume they don't match what was expected and are making manual corrections/adjustments, so hide the warning.
			//As a precaution, show warning if calculated vs. actual amount is off more than twice the fraction of cents value.
			if ( ( ( isset( $this->l13a ) && (int)$this->l13a == 0 ) || !isset( $this->l13a ) || $this->l13a == $this->l12 ) && abs( $l5e_actual_diff ) > $threshold_diff ) { //Was: abs( $l5e_actual_diff ) > ( ( $this->l1 / 100 ) * 12 )
				Debug::Text( 'L5e seems incorrect, show warning...', __FILE__, __LINE__, __METHOD__, 10 );
				$pdf = $this->getPDFObject();

				$pdf->setTextColor( 255, 0, 0 );
				$pdf->setXY( ( 251 + $this->getTempPageOffsets( 'x' ) + $this->getPageMargins( 'x' ) ), ( 516 + $this->getTempPageOffsets( 'y' ) + $this->getPageMargins( 'y' ) ) );

				$pdf->Cell( 180, 10, 'WARNING: Mismatch with amounts deducted from employees by ' . $l5e_actual_diff, 1, 0, 'C', 1, false, 1 );
			}
		}

		return true;
	}


	//This requires 'l7z' to be passed in as a total of all the amounts actually deducted from the employee.
	//So we can compare that with the calculated amounts that should have been deducted, the result of which is l7.
	//  Take for a example the case where the form calculates $100 should have been paid, but only 99.80 was paid due to rounding.
	//  Line 7 should be -0.20, so it reduces what should have been paid by that amount and therefore no balance would be owning.
	//  Therefore the calculation should be l7z (what was actually withheld) - l5e (what should have been withheld).
	function calcL7( $value, $schema ) {
		$this->l7 = ( $this->l7z > 0 ) ? ( bcsub( $this->l7z, $this->l5e ) ) : 0;
		Debug::Text( 'Raw: L7: ' . $this->l7 . ' L5e: ' . $this->l5e . ' L7z: ' . $this->l7z, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->l7;
	}

	function calcL10( $value, $schema ) {
		$this->l10 = bcadd( bcadd( bcadd( $this->l6, $this->l7 ), $this->l8 ), $this->l9 );

		return $this->l10;
	}

	function showSBMisMatchTotals( $value, $schema ) {
		$schedule_b_total_to_l12_diff = abs( round( bcsub( $this->schedule_b_total, $this->l12 ), 2 ) );
		if ( isset( $this->schedule_b_total ) && $this->schedule_b_total > 0 && $schedule_b_total_to_l12_diff > 0 ) {
			$pdf = $this->getPDFObject();

			$pdf->setTextColor( 255, 0, 0 );
			$pdf->setXY( ( 300 + $this->getTempPageOffsets( 'x' ) + $this->getPageMargins( 'x' ) ), ( 148 + $this->getTempPageOffsets( 'y' ) + $this->getPageMargins( 'y' ) ) );

			$pdf->Cell( 131, 10, 'WARNING: Does not match total from Schedule B', 1, 0, 'C', 1, false, 1 );
		}

		return true;
	}

	function calcL11d( $value, $schema ) {
		$this->l11d = bcadd( $this->l11a, bcadd( $this->l11b, $this->l11c ) );

		return $this->l11d;
	}

	function calcL12( $value, $schema ) {
		$this->l12 = round( bcsub( $this->l10, $this->l11d ), 2 );

		return $this->l12;
	}

	function calcL13a( $value, $schema ) {
		if ( !is_numeric( $this->l13a ) ) {
			$this->l13a = $this->l12; //If no deposit amount is specified, assume they deposit the amount calculated.
		}

		return $this->l13a;
	}

	function calcL13e( $value, $schema ) {
		$this->l13e = bcadd( $this->l13a, bcadd( $this->l13b, bcadd( $this->l13c, $this->l13d ) ) );

		return $this->l13e;
	}

	function calcL13g( $value, $schema ) {
		$this->l13g = bcsub( $this->l13e, $this->l13f );

		return $this->l13g;
	}

	function calcL14( $value, $schema ) {
		if ( $this->l13g != '' && $this->l12 > $this->l13g ) {
			$this->l14 = bcsub( $this->l12, $this->l13g );

			return $this->l14;
		}
	}

	function calcL15( $value, $schema ) {
		if ( $this->l13g > $this->l12 ) {
			$this->l15 = bcsub( $this->l13g, $this->l12 );

			return $this->l15;
		}
	}

	function showL16MisMatchTotals() {
		if ( isset( $this->l16_month_total ) && $this->l16_month_total > 0 && isset( $this->l12 ) && $this->l12 > 0 ) {
			$l16_to_l12_diff = abs( round( bcsub( $this->l16_month_total, $this->l12 ), 2 ) );
			if ( $l16_to_l12_diff > 0 ) {
				Debug::Text( 'L16 seems incorrect, show warning...', __FILE__, __LINE__, __METHOD__, 10 );
				$pdf = $this->getPDFObject();

				//Show warning on Page 2
				$pdf->setTextColor( 255, 0, 0 );
				$pdf->setXY( ( 355 + $this->getTempPageOffsets( 'x' ) + $this->getPageMargins( 'x' ) ), ( 561 + $this->getTempPageOffsets( 'y' ) + $this->getPageMargins( 'y' ) ) );

				$pdf->Cell( 160, 13, 'WARNING: Mismatch with Line 12', 1, 0, 'C', 1, false, 1 );
			}
		}

		return true;
	}

	function calcL16MonthTotal( $value, $schema ) {
		$this->l16_month_total = bcadd( bcadd( $this->l16_month1, $this->l16_month2 ), $this->l16_month3 );

		return $this->l16_month_total;
	}

	function _outputPDF() {
		//Initialize PDF with template.
		$pdf = $this->getPDFObject();

		if ( $this->getShowBackground() == true ) {
			$pdf->setSourceFile( $this->getTemplateDirectory() . DIRECTORY_SEPARATOR . $this->pdf_template );

			$this->template_index[1] = $pdf->ImportPage( 1 );
			$this->template_index[2] = $pdf->ImportPage( 2 );
			$this->template_index[3] = $pdf->ImportPage( 3 );
			$this->template_index[4] = $pdf->ImportPage( 4 );
			$this->template_index[5] = $pdf->ImportPage( 5 );
		}

		if ( $this->year == '' ) {
			$this->year = $this->getYear();
		}

		//Get location map, start looping over each variable and drawing
		$template_schema = $this->getTemplateSchema();
		if ( is_array( $template_schema ) ) {

			$template_page = null;

			foreach ( $template_schema as $field => $schema ) {
				$this->Draw( $this->$field, $schema );
			}
		}

		return true;
	}


	function _outputXML() {

		if ( is_object( $this->getXMLObject() ) ) {
			$xml = $this->getXMLObject();
		} else {
			return false; //No XML object to append too. Needs return940 form first.
		}

		$xml->IRS941->addAttribute( 'documentId', 0 ); //  Must be unique within the return.
		if ( isset( $this->l1 ) ) {
			$xml->IRS941->addChild( 'NumberOfEmployees', $this->l1 );
		}

		if ( isset( $this->l2 ) ) {
			$xml->IRS941->addChild( 'TotalWages', $this->l2 );
		}
		if ( isset( $this->l3 ) ) {
			$xml->IRS941->addChild( 'TotalIncomeTaxWithheld', $this->l3 );
		}

		if ( isset( $this->l5a ) ) {
			$xml->IRS941->addChild( 'TaxableSocialSecurityWages', $this->l5a );
			if ( $this->calcL5A2( null, null ) > 0 ) {
				$xml->IRS941->addChild( 'TaxOnSocialSecurityWages', $this->calcL5A2( null, null ) );
			}
		}
		if ( isset( $this->l5b ) ) {
			$xml->IRS941->addChild( 'TaxableSocialSecurityTips', $this->l5b );
			if ( $this->calcL5B2( null, null ) > 0 ) {
				$xml->IRS941->addChild( 'TaxOnSocialSecurityTips', $this->calcL5B2( null, null ) );
			}
		}
		if ( isset( $this->l5c ) ) {
			$xml->IRS941->addChild( 'TaxableMedicareWagesTips', $this->l5c );
			if ( $this->calcL5C2( null, null ) > 0 ) {
				$xml->IRS941->addChild( 'TaxOnMedicareWagesTips', $this->calcL5C2( null, null ) );
			}
		}
		if ( $this->calcL5D( null, null ) > 0 ) {
			$xml->IRS941->addChild( 'TotalSocialSecurityMedTaxes', $this->calcL5D( null, null ) );
			$xml->IRS941->addChild( 'WagesNotSubjToSSMedicareTaxes', 'X' );
		}
		if ( $this->calcL6E( null, null ) > 0 ) {
			$xml->IRS941->addChild( 'TotalTaxesBeforeAdjustmentsAmt', $this->calcL6E( null, null ) );
		}
		if ( isset( $this->l7 ) ) {
			$xml->IRS941->addChild( 'FractionsOfCentsAdjustment', $this->l7 );
		}
		if ( isset( $this->l9 ) ) {
			$xml->IRS941->addChild( 'TipsGroupTermLifeInsAdjAmount', $this->l9 );
		}
		if ( $this->calcL12( null, null ) > 0 ) {
			$xml->IRS941->addChild( 'TotalTax', $this->calcL12( null, null ) );
		} else {
			$xml->IRS941->addChild( 'TotalTax', 0.00 );
		}

		$xml->IRS941->addChild( 'TotalDepositsOverpaymentForQtr', $this->l13 );
		if ( $this->calcL13( null, null ) > 0 ) {
			$xml->IRS941->addChild( 'PaymentCreditTotal', $this->calcL13( null, null ) );
		} else {
			$xml->IRS941->addChild( 'PaymentCreditTotal', 0.00 );
		}

		if ( $this->calcL14( null, null ) > 0 ) {
			$xml->IRS941->addChild( 'BalanceDue', $this->calcL14( null, null ) );
		} else {
			$xml->IRS941->addChild( 'Overpayment' );
			if ( $this->calcL15( null, null ) > 0 ) {
				$xml->IRS941->Overpayment->addChild( 'Amount', $this->calcL15( null, null ) );
				$xml->IRS941->Overpayment->addChild( 'CreditElect', 'X' );
			} else {
				$xml->IRS941->Overpayment->addChild( 'Amount', 0.00 );
			}
		}

		if ( isset( $this->l16 ) ) {
			$xml->IRS941->addChild( 'DepositStateCode', $this->l16 );
		}

		if ( is_array( $this->filterL16( null, null ) ) ) {
			$L16_ARR = $this->filterL16( null, null );
			foreach ( $L16_ARR as $l16 ) {
				switch ( $l16 ) {
					case 'a':
						$xml->IRS941->addChild( 'LessThan2500', 'X' );
						break;
					case 'b':
						$xml->IRS941->addChild( 'MonthlyDepositorGroup' );
						$xml->IRS941->MonthlyDepositorGroup->addChild( 'MonthlyScheduleDepositor', 'X' );
						if ( isset( $this->l16_month1 ) ) {
							$xml->IRS941->MonthlyDepositorGroup->addChild( 'Month1Liability', $this->l16_month1 );
						}
						if ( isset( $this->l16_month2 ) ) {
							$xml->IRS941->MonthlyDepositorGroup->addChild( 'Month2Liability', $this->l16_month2 );
						}
						if ( isset( $this->l16_month3 ) ) {
							$xml->IRS941->MonthlyDepositorGroup->addChild( 'Month3Liability', $this->l16_month3 );
						}
						if ( $this->calcL16MonthTotal( null, null ) > 0 ) {
							$xml->IRS941->MonthlyDepositorGroup->addChild( 'TotalQuarterLiability', $this->calcL16MonthTotal( null, null ) );
						}

						break;
					case 'c':
						$xml->IRS941->addChild( 'SemiweeklyScheduleDepositor', 'X' );
						break;
				}
			}
		}
	}
}

?>