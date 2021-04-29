<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2021 TimeTrex Software Inc.
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

/*
Concise list to all the states W2 eFile Format Specifications:
	https://www.realtaxtools.com/state-w2-efile-frequently-asked-questions/
*/

/**
 * @package GovernmentForms
 */
class GovernmentForms_US_W2C extends GovernmentForms_US {
	public $xml_schema = '1040/IndividualIncomeTax/Common/IRSW2/IRSW2C.xsd';
	public $pdf_template = 'w2c.pdf';

	public $page_margins = [ 0, 5 ];    //**NOTE: When printing be sure turn *off* "Fit to Page" or any scaling: x, y - 43pt = 15mm Absolute margins that affect all drawing and templates.

	private $w2_obj = null; //Prevent __set() from sticking this into the data property.

	function getW2Object() {
		if ( !isset( $this->w2_obj ) ) {
			$gf = new GovernmentForms();
			$this->w2_obj = $gf->getFormObject( 'w2', 'US' );
		}

		return $this->w2_obj;
	}

	function getOptions( $name ) {
		$retval = null;
		switch ( $name ) {
			case 'type':
				$retval = [
						'government' => TTi18n::gettext( 'Government' ),
						'employee'   => TTi18n::gettext( 'Employee' ),
				];
				break;
		}

		return $retval;
	}

	//Set the type of form to display/print. Typically this would be:
	// government or employee.
	function getType() {
		if ( isset( $this->type ) ) {
			return $this->type;
		}

		return false;
	}

	function setType( $value ) {
		$this->type = trim( $value );

		return true;
	}

	function getShowInstructionPage() {
		if ( isset( $this->show_instruction_page ) ) {
			return $this->show_instruction_page;
		}

		return false;
	}

	function setShowInstructionPage( $value ) {
		$this->show_instruction_page = (bool)trim( $value );

		return true;
	}

	public function getTemplateSchema( $name = null ) {
		$template_schema = [
				[
				  'value'              => $this->year,
				  'on_background'      => true,
				  'coordinates'        => [
						  'x'          => 325,
						  'y'          => 130,
						  'h'          => 6,
						  'w'          => 19,
						  'halign'     => 'C',
						  'fill_color' => [ 255, 255, 255 ],
				  ],
				  'font'               => [
						  'size' => 7,
						  'type' => '',
				  ],
				],

				//Finish initializing page 1.
				'previous_ssn'             => [
						'function'    => [ 'draw' => [ 'formatSSN', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 303,
								'y'      => 185,
								'h'      => 15,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'ssn'             => [
						'function'    => [ 'draw' => [ 'formatSSN', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 435,
								'y'      => 125,
								'h'      => 15,
								'w'      => 125,
								'halign' => 'C',
						],
				],
				'ein'             => [
						'function'    => [ 'prefilter' => [ 'stripNonNumeric', 'isNumeric' ], 'draw' => [ 'formatEIN', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 38,
								'y'      => 208,
								'h'      => 15,
								'w'      => 240,
								'halign' => 'L',
						],
				],

				'trade_name'      => [
						'coordinates' => [
								'x'      => 38,  //Make sure there is enough spacing to fit in double windowed envelope with buffer.
								'y'      => 120, //Make sure there is enough spacing to fit in double windowed envelope with buffer.
								'h'      => 15,
								'w'      => 260,
								'halign' => 'L',
						],
				],
				'company_address' => [
						'function'    => [ 'draw' => [ 'filterCompanyAddress', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 38,
								'y'      => 140,
								'h'      => 48,
								'w'      => 260,
								'halign' => 'L',
						],
						'font'        => [
								'size' => 8,
								'type' => '',
						],
						'multicell'   => true,
				],



				'previous_name'  => [
						'function'    => [ 'draw' => [ 'filterPreviousName', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 303,
								'y'      => 209,
								'h'      => 15,
								'w'      => 100,
								'halign' => 'L',
						],
				],

				'first_name'  => [
						'coordinates' => [
								'x'      => 303,
								'y'      => 231,
								'h'      => 15,
								'w'      => 100,
								'halign' => 'L',
						],
				],
				'middle_name' => [
						'function'    => [ 'draw' => [ 'filterMiddleName', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 418,
								'y'      => 231,
								'h'      => 15,
								'w'      => 15,
								'halign' => 'L',
						],
				],
				'last_name'   => [
						'coordinates' => [
								'x'      => 435,
								'y'      => 231,
								'h'      => 15,
								'w'      => 100,
								'halign' => 'L',
						],
				],

				'le'          => [
						'function'    => [ 'draw' => [ 'filterLe', 'drawCheckBox' ] ],
						'coordinates' => [
								[
										'x'      => 545.5,
										'y'      => 151.5,
										'h'      => 11,
										'w'      => 10,
										'halign' => 'C',
								]
						],
				],

				'address'     => [
						'function'    => [ 'draw' => [ 'filterAddress', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 303,  //Make sure there is enough spacing to fit in double windowed envelope with buffer.
								'y'      => 248, //Make sure there is enough spacing to fit in double windowed envelope with buffer.
								'h'      => 32,
								'w'      => 250,
								'halign' => 'L',
						],
						'font'        => [
								'size' => 8,
								'type' => '',
						],
						'multicell'   => true,
				],


				'previous_l1'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 38,
								'y'      => 315,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l1'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 171,
								'y'      => 315,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l2'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 305,
								'y'      => 315,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l2'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 438,
								'y'      => 315,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l3'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 38,
								'y'      => 340,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l3'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 171,
								'y'      => 340,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l4'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 305,
								'y'      => 340,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l4'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 438,
								'y'      => 340,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l5'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 38,
								'y'      => 362,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l5'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 171,
								'y'      => 362,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l6'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 305,
								'y'      => 362,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l6'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 438,
								'y'      => 362,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l7'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 38,
								'y'      => 385,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l7'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 171,
								'y'      => 385,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l8'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 305,
								'y'      => 385,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l8'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 438,
								'y'      => 385,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l10'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 305,
								'y'      => 407,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l10'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 438,
								'y'      => 407,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l11'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 38,
								'y'      => 433,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l11'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 171,
								'y'      => 433,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l12a_code'          => [
						'function'    => [ 'draw' => [ 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 310,
								'y'      => 433,
								'h'      => 10,
								'w'      => 25,
								'halign' => 'C',
						],
				],
				'previous_l12a'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 337,
								'y'      => 433,
								'h'      => 10,
								'w'      => 95,
								'halign' => 'L',
						],
				],

				'l12a_code'          => [
						'function'    => [ 'draw' => [ 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 443,
								'y'      => 433,
								'h'      => 10,
								'w'      => 25,
								'halign' => 'C',
						],
				],
				'l12a'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 470,
								'y'      => 433,
								'h'      => 10,
								'w'      => 88,
								'halign' => 'L',
						],
				],

				'previous_l12b_code'          => [
						'function'    => [ 'draw' => [ 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 310,
								'y'      => 457,
								'h'      => 10,
								'w'      => 25,
								'halign' => 'C',
						],
				],
				'previous_l12b'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 337,
								'y'      => 457,
								'h'      => 10,
								'w'      => 95,
								'halign' => 'L',
						],
				],

				'l12b_code'          => [
						'function'    => [ 'draw' => [ 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 443,
								'y'      => 457,
								'h'      => 10,
								'w'      => 25,
								'halign' => 'C',
						],
				],
				'l12b'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 470,
								'y'      => 457,
								'h'      => 10,
								'w'      => 88,
								'halign' => 'L',
						],
				],

				'previous_l12c_code'          => [
						'function'    => [ 'draw' => [ 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 310,
								'y'      => 480,
								'h'      => 10,
								'w'      => 25,
								'halign' => 'C',
						],
				],
				'previous_l12c'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 337,
								'y'      => 480,
								'h'      => 10,
								'w'      => 95,
								'halign' => 'L',
						],
				],

				'l12c_code'          => [
						'function'    => [ 'draw' => [ 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 443,
								'y'      => 480,
								'h'      => 10,
								'w'      => 25,
								'halign' => 'C',
						],
				],
				'l12c'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 470,
								'y'      => 480,
								'h'      => 10,
								'w'      => 88,
								'halign' => 'L',
						],
				],

				'previous_l12d_code'          => [
						'function'    => [ 'draw' => [ 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 310,
								'y'      => 515,
								'h'      => 10,
								'w'      => 25,
								'halign' => 'C',
						],
				],
				'previous_l12d'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 337,
								'y'      => 515,
								'h'      => 10,
								'w'      => 95,
								'halign' => 'L',
						],
				],

				'l12d_code'          => [
						'function'    => [ 'draw' => [ 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 443,
								'y'      => 515,
								'h'      => 10,
								'w'      => 25,
								'halign' => 'C',
						],
				],
				'l12d'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 470,
								'y'      => 515,
								'h'      => 10,
								'w'      => 88,
								'halign' => 'L',
						],
				],

				'previous_l13a'          => [
						'function'    => [ 'draw' => [ 'drawCheckBox' ] ],
						'coordinates' => [
								[
									'x'      => 49,
									'y'      => 456,
									'h'      => 11,
									'w'      => 10,
									'halign' => 'C',
								]
						],
				],
				'l13a'          => [
						'function'    => [ 'draw' => [ 'drawCheckBox' ] ],
						'coordinates' => [
								[
									'x'      => 182,
									'y'      => 456,
									'h'      => 11,
									'w'      => 10,
									'halign' => 'C',
								]
						],
				],

				'previous_l13b'          => [
						'function'    => [ 'draw' => [ 'drawCheckBox' ] ],
						'coordinates' => [
								[
										'x'      => 84,
										'y'      => 456,
										'h'      => 11,
										'w'      => 10,
										'halign' => 'C',
								]
						],
				],
				'l13b'          => [
						'function'    => [ 'draw' => [ 'drawCheckBox' ] ],
						'coordinates' => [
								[
										'x'      => 217,
										'y'      => 456,
										'h'      => 11,
										'w'      => 10,
										'halign' => 'C',
								]
						],
				],

				'previous_l13c'          => [
						'function'    => [ 'draw' => [ 'drawCheckBox' ] ],
						'coordinates' => [
								[
										'x'      => 125,
										'y'      => 456,
										'h'      => 11,
										'w'      => 10,
										'halign' => 'C',
								]
						],
				],
				'l13c'          => [
						'function'    => [ 'draw' => [ 'drawCheckBox' ] ],
						'coordinates' => [
								[
										'x'      => 258,
										'y'      => 456,
										'h'      => 11,
										'w'      => 10,
										'halign' => 'C',
								]
						],
				],


				'previous_l14a_name'          => [
						'coordinates' => [
								'x'      => 38,
								'y'      => 478,
								'h'      => 12,
								'w'      => 92,
								'halign' => 'L',
						],
				],
				'previous_l14a'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 130,
								'y'      => 478,
								'h'      => 12,
								'w'      => 35,
								'halign' => 'R',
						],
				],
				'l14a_name'          => [
						'coordinates' => [
								'x'      => 170,
								'y'      => 478,
								'h'      => 12,
								'w'      => 92,
								'halign' => 'L',
						],
				],
				'l14a'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 262,
								'y'      => 478,
								'h'      => 12,
								'w'      => 35,
								'halign' => 'R',
						],
				],

				'previous_l14b_name'          => [
						'coordinates' => [
								'x'      => 38,
								'y'      => 490,
								'h'      => 12,
								'w'      => 92,
								'halign' => 'L',
						],
				],
				'previous_l14b'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 130,
								'y'      => 490,
								'h'      => 12,
								'w'      => 35,
								'halign' => 'R',
						],
				],
				'l14b_name'          => [
						'coordinates' => [
								'x'      => 170,
								'y'      => 490,
								'h'      => 12,
								'w'      => 92,
								'halign' => 'L',
						],
				],
				'l14b'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 262,
								'y'      => 490,
								'h'      => 12,
								'w'      => 35,
								'halign' => 'R',
						],
				],

				'previous_l14c_name'          => [
						'coordinates' => [
								'x'      => 38,
								'y'      => 502,
								'h'      => 12,
								'w'      => 92,
								'halign' => 'L',
						],
				],
				'previous_l14c'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 130,
								'y'      => 502,
								'h'      => 12,
								'w'      => 35,
								'halign' => 'R',
						],
				],
				'l14c_name'          => [
						'coordinates' => [
								'x'      => 170,
								'y'      => 502,
								'h'      => 12,
								'w'      => 92,
								'halign' => 'L',
						],
				],
				'l14c'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 262,
								'y'      => 502,
								'h'      => 12,
								'w'      => 35,
								'halign' => 'R',
						],
				],

				'previous_l14d_name'          => [
						'coordinates' => [
								'x'      => 38,
								'y'      => 514,
								'h'      => 12,
								'w'      => 92,
								'halign' => 'L',
						],
				],
				'previous_l14d'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 130,
								'y'      => 514,
								'h'      => 12,
								'w'      => 35,
								'halign' => 'R',
						],
				],
				'l14d_name'          => [
						'coordinates' => [
								'x'      => 170,
								'y'      => 514,
								'h'      => 12,
								'w'      => 92,
								'halign' => 'L',
						],
				],
				'l14d'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 262,
								'y'      => 514,
								'h'      => 12,
								'w'      => 35,
								'halign' => 'R',
						],
				],

				//State (Line 1)
				'previous_l15a_state'    => [
						'coordinates' => [
								'x'      => 38,
								'y'      => 560,
								'h'      => 12,
								'w'      => 27,
								'halign' => 'L',
						],
				],
				'l15a_state'    => [
						'coordinates' => [
								'x'      => 170,
								'y'      => 560,
								'h'      => 12,
								'w'      => 27,
								'halign' => 'L',
						],
				],
				'previous_l15a_state_id' => [
						'coordinates' => [
								'x'      => 38,
								'y'      => 583,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'l15a_state_id' => [
						'coordinates' => [
								'x'      => 170,
								'y'      => 583,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'previous_l16a'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 38,
								'y'      => 605,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'l16a'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 170,
								'y'      => 605,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],

				'previous_l17a'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 38,
								'y'      => 630,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'l17a'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 170,
								'y'      => 630,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'previous_l18a'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 38,
								'y'      => 675,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'l18a'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 170,
								'y'      => 675,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'previous_l19a'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 38,
								'y'      => 700,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'l19a'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 170,
								'y'      => 700,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'previous_l20a_district' => [
						'coordinates' => [
								'x'      => 38,
								'y'      => 723,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'l20a_district' => [
						'coordinates' => [
								'x'      => 170,
								'y'      => 723,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],



				//State (Line 2)
				'previous_l15b_state'    => [
						'coordinates' => [
								'x'      => 305,
								'y'      => 560,
								'h'      => 12,
								'w'      => 27,
								'halign' => 'L',
						],
				],
				'l15b_state'    => [
						'coordinates' => [
								'x'      => 438,
								'y'      => 560,
								'h'      => 12,
								'w'      => 27,
								'halign' => 'L',
						],
				],
				'previous_l15b_state_id' => [
						'coordinates' => [
								'x'      => 305,
								'y'      => 583,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'l15b_state_id' => [
						'coordinates' => [
								'x'      => 438,
								'y'      => 583,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'previous_l16b'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 305,
								'y'      => 605,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'l16b'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 438,
								'y'      => 605,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],

				'previous_l17b'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 305,
								'y'      => 630,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'l17b'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 438,
								'y'      => 630,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'previous_l18b'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 305,
								'y'      => 675,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'l18b'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 438,
								'y'      => 675,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'previous_l19b'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 305,
								'y'      => 700,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'l19b'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 438,
								'y'      => 700,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'previous_l20b_district' => [
						'coordinates' => [
								'x'      => 305,
								'y'      => 723,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'l20b_district' => [
						'coordinates' => [
								'x'      => 438,
								'y'      => 723,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
		];

		if ( isset( $template_schema[$name] ) ) {
			return $name;
		} else {
			return $template_schema;
		}
	}

	function filterLe( $value ) {
		//Corrected SSN and/or name (check this box)
		if ( $this->previous_ssn != '' || $this->previous_first_name != '' || $this->previous_middle_name != '' || $this->previous_last_name != '' ) {
			Debug::Text( 'Name or SSN changed!', __FILE__, __LINE__, __METHOD__, 10 );
			return true;
		}

		return false;
	}

	function filterMiddleName( $value ) {
		//Return just initial
		$value = substr( $value, 0, 1 );

		return $value;
	}

	function filterCompanyAddress( $value ) {
		//Debug::Text('Filtering company address: '. $value, __FILE__, __LINE__, __METHOD__, 10);

		//Combine company address for multicell display.
		$retarr[] = $this->company_address1;
		if ( $this->company_address2 != '' ) {
			$retarr[] = $this->company_address2;
		}
		$retarr[] = $this->company_city . ', ' . $this->company_state . ' ' . $this->company_zip_code;

		return implode( "\n", $retarr );
	}

	function filterPreviousName( $value ) {
		$retval = $this->previous_first_name .' '. $this->previous_middle_name .' '. $this->previous_last_name;

		return $retval;
	}

	function filterAddress( $value ) {
		//Combine company address for multicell display.
		$retarr[] = $this->address1;
		if ( $this->address2 != '' ) {
			$retarr[] = $this->address2;
		}
		$retarr[] = $this->city . ', ' . $this->state . ' ' . $this->zip_code;

		return implode( "\n", $retarr );
	}

	function _getStateName( $state ) {
		return $this->getW2Object()->_getStateName( $state );
	}

	function _getStateNumericCode( $state ) {
		return $this->getW2Object()->_getStateNumericCode( $state );
	}

	function _getL12AmountByCode( $code ) {
		Debug::Text( 'Checking for Code:' . $code, __FILE__, __LINE__, __METHOD__, 10 );
		foreach ( range( 'a', 'z' ) as $z ) {
			if ( isset( $this->{'l12' . $z . '_code'} ) && $this->{'l12' . $z . '_code'} == $code ) {
				Debug::Text( 'Found amount for Code:' . $code, __FILE__, __LINE__, __METHOD__, 10 );

				return $this->{'l12' . $z};
			}
		}

		Debug::Text( 'Not amount found, Code:' . $code, __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function _compileRA() { //RA (Submitter) Record
		if ( in_array( strtoupper( $this->efile_state ), [ 'AL' ] ) ) { //Skip for eFiling in these states.
			return false;
		}

		$line[] = 'RA';                                                                                                                                                        //RA Record

		Debug::Text( 'RA Record State: ' . $this->efile_state, __FILE__, __LINE__, __METHOD__, 10 );
		switch ( strtolower( $this->efile_state ) ) {
			case 'ny': //New York
				$efile_user_id = null;
				$is_resub = null;

				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                                                                                     //EIN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $efile_user_id ), 8, 'AN' );                                                                           //User ID
				$line[] = $this->padRecord( '', 4, 'AN' );                                                                                                                      //Software Vendor code
				$line[] = $this->padRecord( '', 5, 'AN' );                                                                                                                      //Blank
				$line[] = $this->padRecord( $is_resub, 1, 'AN' );                                                                                                               //Resub
				$line[] = $this->padRecord( '', 6, 'AN' );                                                                                                                      //Blank
				$line[] = $this->padRecord( '', 2, 'AN' );                                                                                                                      //Software Code
				$line[] = $this->padRecord( '', 57, 'AN' );                                                                                                                     //Company Name
				$line[] = $this->padRecord( '', 22, 'AN' );                                                                                                                     //Company Location Address
				$line[] = $this->padRecord( '', 22, 'AN' );                                                                                                                     //Company Delivery Address
				$line[] = $this->padRecord( '', 22, 'AN' );                                                                                                                     //Company City
				$line[] = $this->padRecord( '', 2, 'AN' );                                                                                                                      //Company State
				$line[] = $this->padRecord( '', 5, 'AN' );                                                                                                                      //Company Zip Code
				$line[] = $this->padRecord( '', 4, 'AN' );                                                                                                                      //Company Zip Code Extension
				$line[] = $this->padRecord( '', 5, 'AN' );                                                                                                                      //Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                                                                                     //Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                                                                                     //Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                                                                                      //Company Country, fill with blanks if its the US
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->trade_name ), 57, 'AN' );                                                                       //Submitter organization.
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( ( $this->company_address2 != '' ) ? $this->company_address2 : $this->company_address1 ), 22, 'AN' );   //Submitter Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address1 ), 22, 'AN' );                                                                 //Submitter Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_city ), 22, 'AN' );                                                                     //Submitter City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_state ), 2, 'AN' );                                                                     //Submitter State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_zip_code ), 5, 'AN' );                                                                  //Submitter Zip Code
				$line[] = $this->padRecord( '', 4, 'AN' );                                                                                                                      //Submitter Zip Code Extension
				$line[] = $this->padRecord( '', 5, 'AN' );                                                                                                                      //Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                                                                                     //Submitter Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                                                                                     //Submitter Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                                                                                      //Submitter Country, fill with blanks if its the US
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->contact_name ), 27, 'AN' );                                                                     //Contact Name
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->contact_phone ), 15, 'AN' );                                                                         //Contact Phone
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->contact_phone_ext ), 5, 'AN' );                                                                      //Contact Phone Ext
				$line[] = $this->padRecord( '', 3, 'AN' );                                                                                                                      //Blank
				$line[] = $this->padRecord( $this->contact_email, 40, 'AN' );                                                                                                   //Contact Email
				$line[] = $this->padRecord( '', 3, 'AN' );                                                                                                                      //Blank
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->contact_fax ), 10, 'AN' );                                                                           //Contact Fax
				$line[] = $this->padRecord( '', 1, 'AN' );                                                                                                                      //Blank
				$line[] = $this->padRecord( '', 1, 'AN' );                                                                                                                      //PreParers Code
				$line[] = $this->padRecord( '', 12, 'AN' );                                                                                                                     //Blank
				break;
			default:
				$is_resub = 0;

				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                                                                                     //EIN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->efile_user_id ), 8, 'AN' );                                                                     //User ID
				$line[] = $this->padRecord( '', 4, 'AN' );                                                                                                                      //Software Vendor code
				$line[] = $this->padRecord( '', 5, 'AN' );                                                                                                                      //Blank
				$line[] = $this->padRecord( $is_resub, 1, 'AN' );                                                                                                               //Resub
				$line[] = $this->padRecord( '', 6, 'AN' );                                                                                                                      //Blank
				$line[] = $this->padRecord( '98', 2, 'AN' );                                                                                                                    //Software Code
				$line[] = $this->padRecord( $this->trade_name, 57, 'AN' );                                                                                                      //Company Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address2 ), 22, 'AN' );                                                                 //Company Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address1 ), 22, 'AN' );                                                                 //Company Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_city ), 22, 'AN' );                                                                     //Company City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_state ), 2, 'AN' );                                                                     //Company State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_zip_code ), 5, 'AN' );                                                                  //Company Zip Code
				$line[] = $this->padRecord( '', 4, 'AN' );                                                                                                                      //Company Zip Code Extension
				$line[] = $this->padRecord( '', 5, 'AN' );                                                                                                                      //Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                                                                                     //Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                                                                                     //Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                                                                                      //Company Country, fill with blanks if its the US
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->trade_name ), 57, 'AN' );                                                                       //Submitter organization.
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( ( $this->company_address2 != '' ) ? $this->company_address2 : $this->company_address1 ), 22, 'AN' );   //Submitter Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address1 ), 22, 'AN' );                                                                 //Submitter Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_city ), 22, 'AN' );                                                                     //Submitter City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_state ), 2, 'AN' );                                                                     //Submitter State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_zip_code ), 5, 'AN' );                                                                  //Submitter Zip Code
				$line[] = $this->padRecord( '', 4, 'AN' );                                                                                                                      //Submitter Zip Code Extension
				$line[] = $this->padRecord( '', 5, 'AN' );                                                                                                                      //Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                                                                                     //Submitter Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                                                                                     //Submitter Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                                                                                      //Submitter Country, fill with blanks if its the US
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->contact_name ), 27, 'AN' );                                                                     //Contact Name
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->contact_phone ), 15, 'AN' );                                                                         //Contact Phone
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->contact_phone_ext ), 5, 'AN' );                                                                      //Contact Phone Ext
				$line[] = $this->padRecord( '', 3, 'AN' );                                                                                                                      //Blank
				$line[] = $this->padRecord( $this->contact_email, 40, 'AN' );                                                                                                   //Contact Email
				$line[] = $this->padRecord( '', 3, 'AN' );                                                                                                                      //Blank
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->contact_fax ), 10, 'AN' );                                                                           //Contact Fax
				$line[] = $this->padRecord( '', 1, 'AN' );                                                                                                                      //Blank
				$line[] = $this->padRecord( 'L', 1, 'AN' );                                                                                                                     //PreParers Code
				$line[] = $this->padRecord( '', 12, 'AN' );                                                                                                                     //Blank
				break;
		}

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 512 ) {
			Debug::Text( 'ERROR! RA Record length is incorrect, should be 512 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( 'RA Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _compileRE() {  //RE (Employer) Record
		if ( in_array( strtoupper( $this->efile_state ), [ 'AL' ] ) ) { //Skip for eFiling in these states.
			return false;
		}

		switch ( strtolower( $this->efile_state ) ) {
			case 'ny': //New York
				$line[] = 'RE';                                                                                   //(1-2) RE Record
				$line[] = $this->padRecord( '', 4, 'AN' );                                                        //(3-6) Tax Year
				$line[] = $this->padRecord( '', 1, 'AN' );                                                        //(7) Agent Indicator
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                       //(8-16 ) EIN
				$line[] = $this->padRecord( '', 9, 'AN' );                                                        //(17-25) Agent for EIN
				$line[] = $this->padRecord( '', 1, 'AN' );                                                        //(26) Terminating Business
				$line[] = $this->padRecord( '', 4, 'AN' );                                                        //(27-30) Establishment Number
				$line[] = $this->padRecord( '', 9, 'AN' );                                                        //(31-39) Other EIN
				$line[] = $this->padRecord( $this->trade_name, 57, 'AN' );                                        //(40-96) Company Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address2 ), 22, 'AN' );   //(97-118) Company Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address1 ), 22, 'AN' );   //(119-140) Company Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_city ), 22, 'AN' );       //(141-162) Company City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_state ), 2, 'AN' );       //(163-164) Company State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_zip_code ), 5, 'AN' );    //(165-169) Company Zip Code
				$line[] = $this->padRecord( '', 4, 'AN' );                                                        //(170-173) Company Zip Code Extension
				$line[] = $this->padRecord( '42020', 5, 'AN' );                                                   //(174) Kind of Employer ???????
				//$line[] = $this->padRecord( '', 4, 'AN' );                                                      //(175-178) Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                       //(179-201) Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                       //(202-216) Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                        //(217-218) Country, fill with blanks if its the US
				$line[] = $this->padRecord( '', 1, 'AN' );                                                        //(219) Employment Code - 941 Form
				$line[] = $this->padRecord( '', 1, 'AN' );                                                        //(220) Tax Jurisdiction
				$line[] = $this->padRecord( '', 1, 'AN' );                                                        //(221) Third Party Sick Pay
				$line[] = $this->padRecord( '', 27, 'AN' );                                                       //(222-248) Contact Name
				$line[] = $this->padRecord( '', 15, 'AN' );                                                       //(249-263) Contact Phone
				$line[] = $this->padRecord( '', 5, 'AN' );                                                        //(264-268) Contact Phone Ext
				$line[] = $this->padRecord( '', 10, 'AN' );                                                       //(269-278) Contact Fax
				$line[] = $this->padRecord( '', 40, 'AN' );                                                       //(279-318) Contact Email
				$line[] = $this->padRecord( '', 194, 'AN' );                                                      //(319-512) Blank
				break;
			default:
				$line[] = 'RE';                                                                                 //(1-2) RE Record
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->year ), 4, 'N' );                    //(3-6) Tax Year
				$line[] = $this->padRecord( '', 1, 'AN' );                                                      //(7) Agent Indicator
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                     //(8-16 ) EIN
				$line[] = $this->padRecord( '', 9, 'AN' );                                                      //(17-25) Agent for EIN
				$line[] = $this->padRecord( '0', 1, 'N' );                                                      //(26) Terminating Business
				$line[] = $this->padRecord( '', 4, 'AN' );                                                      //(27-30) Establishment Number
				$line[] = $this->padRecord( '', 9, 'AN' );                                                      //(31-39) Other EIN
				$line[] = $this->padRecord( $this->trade_name, 57, 'AN' );                                      //(40-96) Company Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address2 ), 22, 'AN' ); //(97-118) Company Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address1 ), 22, 'AN' ); //(119-140) Company Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_city ), 22, 'AN' );     //(141-162) Company City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_state ), 2, 'AN' );     //(163-164) Company State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_zip_code ), 5, 'AN' );  //(165-169) Company Zip Code
				$line[] = $this->padRecord( '', 4, 'AN' );                                                      //(170-173) Company Zip Code Extension
				$line[] = $this->padRecord( strtoupper( $this->kind_of_employer ), 1, 'AN' );                   //(174) Kind of Employer
				$line[] = $this->padRecord( '', 4, 'AN' );                                                      //(175-178) Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                     //(179-201) Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                     //(202-216) Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                      //(217-218) Country, fill with blanks if its the US
				$line[] = $this->padRecord( 'R', 1, 'AN' );                                                     //(219) Employment Code - 941 Form
				$line[] = $this->padRecord( '', 1, 'AN' );                                                      //(220) Tax Jurisdiction
				$line[] = $this->padRecord( ( $this->l13c == '' ) ? 0 : 1, 1, 'N' );                            //(221) Third Party Sick Pay
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->contact_name ), 27, 'AN' );     //(222-248) Contact Name
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->contact_phone ), 15, 'AN' );         //(249-263) Contact Phone
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->contact_phone_ext ), 5, 'AN' );      //(264-268) Contact Phone Ext
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->contact_fax ), 10, 'AN' );           //(269-278) Contact Fax
				$line[] = $this->padRecord( $this->contact_email, 40, 'AN' );                                   //(279-318) Contact Email
				$line[] = $this->padRecord( '', 194, 'AN' );                                                    //(319-512) Blank
				break;
		}

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 512 ) {
			Debug::Text( 'ERROR! RE Record length is incorrect, should be 512 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( 'RE Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _compileRW() { //RW (Employee) Record
		if ( in_array( strtoupper( $this->efile_state ), [ 'NY', 'AL', 'CO', 'CT', 'DE', 'MA', 'PA', 'VA' ] ) ) { //Skip for eFiling in these states.
			return false;
		}

		switch ( strtolower( $this->efile_state ) ) {
			default:
				$line[] = 'RW';                                                                                    //RW Record
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                        //SSN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 15, 'AN' );          //First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 15, 'AN' );         //Middle Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );           //Last Name
				$line[] = $this->padRecord( '', 4, 'AN' );                                                         //Suffix
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address2 ), 22, 'AN' );            //Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address1 ), 22, 'AN' );            //Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->city ), 22, 'AN' );                //City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->state ), 2, 'AN' );                //State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->zip_code ), 5, 'AN' );             //Zip
				$line[] = $this->padRecord( '', 4, 'AN' );                                                         //Zip Extension
				$line[] = $this->padRecord( '', 5, 'AN' );                                                         //Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                        //Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                        //Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                         //Country, fill with blanks if its the US
				$line[] = $this->padRecord( $this->removeDecimal( $this->l1 ), 11, 'N' );                          //(188-198) Wages, Tips and Other Compensation
				$line[] = $this->padRecord( $this->removeDecimal( $this->l2 ), 11, 'N' );                          //(199-209) Federal Income Tax
				$line[] = $this->padRecord( $this->removeDecimal( $this->l3 ), 11, 'N' );                          //(210-220) Social Security Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->l4 ), 11, 'N' );                          //(221-231) Social Security Tax
				$line[] = $this->padRecord( $this->removeDecimal( $this->l5 ), 11, 'N' );                          //(232-242) Medicare Wages and Tips
				$line[] = $this->padRecord( $this->removeDecimal( $this->l6 ), 11, 'N' );                          //(243-253) Medicare Tax
				$line[] = $this->padRecord( $this->removeDecimal( $this->l7 ), 11, 'N' );                          //(254-264) Social Security Tips
				$line[] = $this->padRecord( '', 11, 'AN' );                                                        //(265-275) Blank
				$line[] = $this->padRecord( $this->removeDecimal( $this->l10 ), 11, 'N' );                         //(276-286) Dependant Care Benefits
				$line[] = $this->padRecord( $this->removeDecimal( $this->_getL12AmountByCode( 'D' ) ), 11, 'N' );  //Deferred Compensation Contributions to 401K //Code D in any of the Box 12(a throug d).
				$line[] = $this->padRecord( $this->removeDecimal( $this->_getL12AmountByCode( 'E' ) ), 11, 'N' );  //Deferred Compensation Contributions to 403(b) //Code E in any of the Box 12(a throug d).
				$line[] = $this->padRecord( $this->removeDecimal( $this->_getL12AmountByCode( 'F' ) ), 11, 'N' );  //Deferred Compensation Contributions to 408(k)(6) //Code F in any of the Box 12(a throug d).
				$line[] = $this->padRecord( $this->removeDecimal( $this->_getL12AmountByCode( 'G' ) ), 11, 'N' );  //Deferred Compensation Contributions to 457(b) //Code G in any of the Box 12(a throug d).
				$line[] = $this->padRecord( $this->removeDecimal( $this->_getL12AmountByCode( 'H' ) ), 11, 'N' );  //(331-341) Deferred Compensation Contributions to 501(c)(18)(D) //Code H in any of the Box 12(a throug d).
				$line[] = $this->padRecord( '', 11, 'AN' );                                                        //(342-352) Blank
				$line[] = $this->padRecord( $this->removeDecimal( $this->l11 ), 11, 'N' );                         //(353-363) Non-qualified Plan Section 457
				$line[] = $this->padRecord( $this->removeDecimal( $this->_getL12AmountByCode( 'W' ) ), 11, 'N' );  //(364-374) Employer Contributions to Health Savings Account //Code W in any of the Box 12(a throug d).
				$line[] = $this->padRecord( '', 11, 'N' );                                                         //Non-qualified NOT Plan Section 457
				$line[] = $this->padRecord( '', 11, 'N' );                                                         //Non taxable combat pay
				$line[] = $this->padRecord( '', 11, 'AN' );                                                        //Blank
				$line[] = $this->padRecord( '', 11, 'N' );                                                         //Employer Cost of Premiums for Group Term Life Insurance over $50K
				$line[] = $this->padRecord( '', 11, 'N' );                                                         //Income from the Exercise of Nonstatutory Stock Options
				$line[] = $this->padRecord( '', 11, 'N' );                                                         //Deferrals Under a Section 409A non-qualified plan
				$line[] = $this->padRecord( $this->removeDecimal( $this->_getL12AmountByCode( 'AA' ) ), 11, 'N' ); //(441-451) Desiginated Roth Contributions under a section 401K //Code AA in any of the Box 12(a throug d).
				$line[] = $this->padRecord( $this->removeDecimal( $this->_getL12AmountByCode( 'BB' ) ), 11, 'N' ); //(452-462) Desiginated Roth Contributions under a section 403B //Code BB in any of the Box 12(a throug d).
				$line[] = $this->padRecord( $this->removeDecimal( $this->_getL12AmountByCode( 'DD' ) ), 11, 'N' ); //(463-473) Cost of Employer Sponsored Health Coverage //Code DD in any of the Box 12(a throug d).
				$line[] = $this->padRecord( '', 11, 'N' );                                                         //(474-484) Blank
				$line[] = $this->padRecord( '', 1, 'AN' );                                                         //(485) Blank
				$line[] = $this->padRecord( ( ( $this->l13a == '' ) ? 0 : 1 ), 1, 'N' );                           //(486) Statutory Employee
				$line[] = $this->padRecord( '', 1, 'AN' );                                                         //(487) Blank
				$line[] = $this->padRecord( ( ( $this->l13b == '' ) ? 0 : 1 ), 1, 'N' );                           //(488) Retirement Plan Indicator
				$line[] = $this->padRecord( ( ( $this->l13c == '' ) ? 0 : 1 ), 1, 'N' );                           //(489) 3rd Party Sick Pay Indicator
				$line[] = $this->padRecord( '', 23, 'AN' );                                                        //(490-512) Blank
				break;
		}

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 512 ) {
			Debug::Text( 'ERROR! RW Record length is incorrect, should be 512 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( 'RW Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	//ID is the state identifier like: a, b, c, d,...
	function _compileRS( $id ) { //RS (State) Record
		if ( $this->efile_state == '' ) { //Federal filing does not need any RS record at all.
			return false;
		}

		$l15_state = 'l15' . $id . '_state';
		$l15_state_id = 'l15' . $id . '_state_id';
		$l15_state_control_number = 'l15' . $id . '_state_control_number';

		$l16 = 'l16' . $id;
		$l17 = 'l17' . $id;
		$l18 = 'l18' . $id;
		$l19 = 'l19' . $id;
		$l20 = 'l20' . $id . '_district';

		if ( !isset( $this->$l15_state ) ) {
			return false;
		}

		if ( strtolower( $this->efile_state ) != strtolower( $this->$l15_state ) ) {
			Debug::Text( ' eFile Format for State: ' . $this->efile_state . ' Employee Record State: ' . $this->$l15_state, __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( 'RS Record State: ' . $this->efile_state, __FILE__, __LINE__, __METHOD__, 10 );
		switch ( strtolower( $this->efile_state ) ) {
			case 'al': //Alabama
				//Withholding Number for State format is the State ID number.
				$line[] = 'RS';                                                                               //(1-2)[2]: RS Record
				$line[] = $this->padRecord( '', 2, 'AN' );                                                    //(3-4)[2]: State Code
				$line[] = $this->padRecord( '', 5, 'AN' );                                                    //(5-9)[5]: Tax Entity Code [Leave Blank]
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                   //(10-18)[9]: SSN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 15, 'AN' );     //(19-33)[15]: First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 15, 'AN' );    //(34-48)[15]: Middle Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );      //(49-68)[20]: Last Name
				$line[] = $this->padRecord( '', 4, 'AN' );                                                    //(69-72)[4]: Suffix
				$line[] = $this->padRecord( '', 22, 'AN' );                                                   //(73-94)[22]: Location Address
				$line[] = $this->padRecord( '', 22, 'AN' );                                                   //(95-116)[22]: Delivery Address
				$line[] = $this->padRecord( '', 22, 'AN' );                                                   //(117-138)[22]: City
				$line[] = $this->padRecord( '', 2, 'AN' );                                                    //(139-140)[2]: State
				$line[] = $this->padRecord( '', 5, 'AN' );                                                    //(141-145)[5]: Zip
				$line[] = $this->padRecord( '', 4, 'AN' );                                                    //(146-149)[4]: Zip Extension
				$line[] = $this->padRecord( '', 5, 'AN' );                                                    //(150-154)[5]: Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                   //(155-177)[23]: Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                   //(178-192)[15]: Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                    //(193-194)[2]: Country, fill with blanks if its the US

				//Unemployment reporting
				$line[] = $this->padRecord( '', 2, 'AN' );                                                    //(195-196)[2]: Optional Code
				$line[] = $this->padRecord( '', 6, 'AN' );                                                    //(197-202)[6]: Reporting Period
				$line[] = $this->padRecord( '', 11, 'AN' );                                                   //(203-213)[11]: State Quarterly Unemployment Total
				$line[] = $this->padRecord( '', 11, 'AN' );                                                   //(214-224)[11]: State Quarterly Unemployment Insurance
				$line[] = $this->padRecord( '', 2, 'AN' );                                                    //(225-226)[2]: Number of weeks worked
				$line[] = $this->padRecord( '', 8, 'AN' );                                                    //(227-234)[8]: Date first employed
				$line[] = $this->padRecord( '', 8, 'AN' );                                                    //(235-242)[8]: Date of separation
				$line[] = $this->padRecord( '', 5, 'AN' );                                                    //(243-247)[5]: Blank
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->$l15_state_id ), 10, 'N' );        //(248-257)[10]: State Employer Account Number - For AL: Numbers below 700000 – right justify and zero fill. Numbers 700000 and above – R followed by nine digits.
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                   //(258-266)[9]: FEIN
				$line[] = $this->padRecord( '', 7, 'AN' );                                                    //(267-273)[7]: Blank

				//Income Tax Reporting
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );       //(274-275)[2]: State Code
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l16 ), 11, 'N' );                   //(276-286)[11]: State Taxable Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l17 ), 11, 'N' );                   //(287-297)[11]: State income tax
				$line[] = $this->padRecord( $this->removeDecimal( $this->l2 ), 10, 'N' );                     //(298-307)[10]: AL: Federal Income Tax Withheld
				$line[] = $this->padRecord( '', 1, 'AN' );                                                    //(308)[1]: Tax Type Code [C=City, D=County, E=School District, F=Other]
				$line[] = $this->padRecord( '', 11, 'AN' );                                                   //(309-319)[11]: Local Wages
				$line[] = $this->padRecord( '', 11, 'AN' );                                                   //(320-330)[11]: Local Income Tax
				$line[] = $this->padRecord( '', 7, 'AN' );                                                    //(331-337)[7]: State Control Number
				$line[] = $this->padRecord( 0, 11, 'N' );                                                     //(338-348)[11]: AL: Other income (1099, W2G, etc.), columns 338-348. (Only use this field to report other income reported on 1099, W2G, etc. from which Alabama tax was withheld. Zero fill if not applicable.)
				$line[] = $this->padRecord( '', 44, 'AN' );                                                   //(349-392)[44]: Supplemental Data 1
				$line[] = $this->padRecord( $this->year, 4, 'N' );                                            //(393-396)[4]: AL: Payment Year
				$line[] = $this->padRecord( '', 116, 'AN' );                                                  //(397-512)[116]: Supplemental Data 1
				break;
			case 'ar': //Arkansas - https://www.dfa.arkansas.gov/images/uploads/incomeTaxOffice/Mag_Media.pdf
				//Withholding Number for State format is the State ID number.
				$line[] = 'RS';                                                                               //(1-2)[2]: RS Record
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );       //(3-4)[2]: State Code
				$line[] = $this->padRecord( '', 5, 'AN' );                                                    //(5-9)[5]: Tax Entity Code [Leave Blank]
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                   //(10-18)[9]: SSN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 15, 'AN' );     //(19-33)[15]: First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 15, 'AN' );    //(34-48)[15]: Middle Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );      //(49-68)[20]: Last Name
				$line[] = $this->padRecord( '', 4, 'AN' );                                                    //(69-72)[4]: Suffix
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address2 ), 22, 'AN' );       //(73-94)[22]: Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address1 ), 22, 'AN' );       //(95-116)[22]: Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->city ), 22, 'AN' );           //(117-138)[22]: City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->state ), 2, 'AN' );           //(139-140)[2]: State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->zip_code ), 5, 'AN' );        //(141-145)[5]: Zip
				$line[] = $this->padRecord( '', 4, 'AN' );                                                    //(146-149)[4]: Zip Extension
				$line[] = $this->padRecord( '', 5, 'AN' );                                                    //(150-154)[5]: Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                   //(155-177)[23]: Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                   //(178-192)[15]: Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                    //(193-194)[2]: Country, fill with blanks if its the US

				//Unemployment reporting
				$line[] = $this->padRecord( '', 2, 'AN' );                                                    //(195-196)[2]: Optional Code
				$line[] = $this->padRecord( '122020', 6, 'AN' );                                              //(197-202)[6]: AR: Last month and four digit year for the calendar quarter. Unemployment only, but might be required?
				$line[] = $this->padRecord( '', 11, 'N' );                                                    //(203-213)[11]: State Quarterly Unemployment Total
				$line[] = $this->padRecord( '', 11, 'N' );                                                    //(214-224)[11]: State Quarterly Unemployment Insurance
				$line[] = $this->padRecord( '', 2, 'N' );                                                     //(225-226)[2]: Number of weeks worked
				$line[] = $this->padRecord( '', 8, 'AN' );                                                    //(227-234)[8]: Date first employed
				$line[] = $this->padRecord( '', 8, 'AN' );                                                    //(235-242)[8]: Date of separation
				$line[] = $this->padRecord( '', 5, 'AN' );                                                    //(243-247)[5]: Blank
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 20, 'AN' );                 //(248-267)[20]: AR: FEIN
				$line[] = $this->padRecord( '', 6, 'AN' );                                                    //(268-273)[6]: Blank

				//Income Tax Reporting
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );       //(274-275)[2]: State Code
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l16 ), 11, 'N' );                   //(276-286)[11]: State Taxable Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l17 ), 11, 'N' );                   //(287-297)[11]: State income tax
				$line[] = $this->padRecord( '', 10, 'AN' );                                                   //(298-307)[10]: Other State Data
				$line[] = $this->padRecord( '', 1, 'AN' );                                                    //(308)[1]: Tax Type Code [C=City, D=County, E=School District, F=Other]
				$line[] = $this->padRecord( '', 11, 'AN' );                                                   //(309-319)[11]: Local Wages
				$line[] = $this->padRecord( '', 11, 'AN' );                                                   //(320-330)[11]: Local Income Tax
				$line[] = $this->padRecord( '', 7, 'AN' );                                                    //(331-337)[7]: State Control Number
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 75, 'AN' );                 //(338-412)[75]: AR: Same as 248-267. EIN from RE Record.
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->$l15_state_id ), 75, 'AN' );       //(413-487)[75]: AR: 11 digit State of Arkansas ID number. Omit hypens.
				$line[] = $this->padRecord( '', 25, 'AN' );                                                   //(488-512)[25]: Blank
				break;
			case 'ga': //Georgia -- Has Local Taxes
				// File Format Specifications: https://dor.georgia.gov/sites/dor.georgia.gov/files/related_files/document/Federal%20Format%20Specs%202015.pdf
				$line[] = 'RS';                                                                                             //RS Record
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );                     //State Code
				$line[] = $this->padRecord( '', 5, 'AN' );                                                                  //Tax Entity Code (Leave Blank)
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                                 //SSN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 15, 'AN' );                   //First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 15, 'AN' );                  //Middle Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );                    //Last Name
				$line[] = $this->padRecord( '', 4, 'AN' );                                                                  //Suffix
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address2 ), 22, 'AN' );                     //Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address1 ), 22, 'AN' );                     //Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->city ), 22, 'AN' );                         //City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->state ), 2, 'AN' );                         //State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->zip_code ), 5, 'AN' );                      //Zip
				$line[] = $this->padRecord( '', 4, 'AN' );                                                                  //Zip Extension
				$line[] = $this->padRecord( '', 5, 'AN' );                                                                  //Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                                 //Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                                 //Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                                  //Country, fill with blanks if its the US

				//Unemployment reporting
				$line[] = $this->padRecord( '', 2, 'AN' );                                                                  //Optional Code
				$line[] = $this->padRecord( '', 6, 'AN' );                                                                  //Reporting Period
				$line[] = $this->padRecord( '', 11, 'N' );                                                                  //State Quarterly Unemployment Total
				$line[] = $this->padRecord( '', 11, 'N' );                                                                  //State Quarterly Unemployment Insurance
				$line[] = $this->padRecord( '', 2, 'AN' );                                                                  //Number of weeks worked
				$line[] = $this->padRecord( '', 8, 'AN' );                                                                  //Date first employed
				$line[] = $this->padRecord( '', 8, 'AN' );                                                                  //Date of separation
				$line[] = $this->padRecord( '', 5, 'AN' );                                                                  //Blank
				$line[] = $this->padRecord( '', 20, 'AN' );                                                                 //State Employer Account Number
				$line[] = $this->padRecord( '', 6, 'AN' );                                                                  //Blank

				//Income Tax Reporting
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );                     //State Code
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l16 ), 11, 'N' );                                 //State Taxable Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l17 ), 11, 'N' );                                 //State income tax
				$line[] = $this->padRecord( '12/31/' . $this->year, 10, 'AN' );                                             //Period End Date (last day of the year)
				$line[] = $this->padRecord( '', 1, 'AN' );                                                                  //Tax Type Code
				$line[] = $this->padRecord( '', 11, 'AN' );                                                                 //Local Wages (blank)
				$line[] = $this->padRecord( '', 11, 'AN' );                                                                 //Local Income Tax (blank)
				$line[] = $this->padRecord( str_replace( [ '-', ' ' ], '', strtoupper( $this->$l15_state_id ) ), 9, 'AN' ); //Withholding Number, no hyphen and upper case alpha
				$line[] = $this->padRecord( $this->trade_name, 57, 'AN' );                                                  //Company Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address2 ), 22, 'AN' );             //Company Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_address1 ), 22, 'AN' );             //Company Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_city ), 22, 'AN' );                 //Company City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_state ), 2, 'AN' );                 //Company State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->company_zip_code ), 5, 'AN' );              //Company Zip Code
				$line[] = $this->padRecord( '', 4, 'AN' );                                                                  //Company Zip Code Extension
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                                 //EIN
				$line[] = $this->padRecord( '', 5, 'AN' );                                                                  //Blank
				$line[] = $this->padRecord( '', 25, 'AN' );                                                                 //Blank

				break;
			case 'ks': //Kansas - https://www.ksrevenue.org/pdf/K-2MT.pdf
				$line[] = 'RS';                                                                                      //(1-2)[2]: RS Record
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );              //(3-4)[2]: State Code
				$line[] = $this->padRecord( '', 5, 'AN' );                                                           //(5-9)[5]: Tax Entity Code [Leave Blank]
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                          //(10-18)[9]: SSN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 15, 'AN' );            //(19-33)[15]: First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 15, 'AN' );           //(34-48)[15]: Middle Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );             //(49-68)[20]: Last Name
				$line[] = $this->padRecord( '', 4, 'AN' );                                                           //(69-72)[4]: Suffix
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address2 ), 22, 'AN' );              //(73-94)[22]: Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address1 ), 22, 'AN' );              //(95-116)[22]: Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->city ), 22, 'AN' );                  //(117-138)[22]: City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->state ), 2, 'AN' );                  //(139-140)[2]: State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->zip_code ), 5, 'AN' );               //(141-145)[5]: Zip
				$line[] = $this->padRecord( '', 4, 'AN' );                                                           //(146-149)[4]: Zip Extension
				$line[] = $this->padRecord( '', 5, 'AN' );                                                           //(150-154)[5]: Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                          //(155-177)[23]: Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                          //(178-192)[15]: Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                           //(193-194)[2]: Country, fill with blanks if its the US

				//Unemployment reporting
				$line[] = $this->padRecord( '', 2, 'AN' );                                                           //(195-196)[2]: Optional Code
				$line[] = $this->padRecord( '', 6, 'AN' );                                                           //(197-202)[6]: Reporting Period
				$line[] = $this->padRecord( '', 11, 'N' );                                                           //(203-213)[11]: State Quarterly Unemployment Total
				$line[] = $this->padRecord( '', 11, 'N' );                                                           //(214-224)[11]: State Quarterly Unemployment Insurance
				$line[] = $this->padRecord( '', 2, 'AN' );                                                           //(225-226)[2]: Number of weeks worked
				$line[] = $this->padRecord( '', 8, 'AN' );                                                           //(227-234)[8]: Date first employed
				$line[] = $this->padRecord( '', 8, 'AN' );                                                           //(235-242)[8]: Date of separation
				$line[] = $this->padRecord( '', 5, 'AN' );                                                           //(243-247)[5]: Blank
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->$l15_state_id ), 20, 'AN' );         //(248-267)[20]: State Employer Account Number
				$line[] = $this->padRecord( '', 6, 'AN' );                                                           //(268-273)[6]: Blank


				//Income Tax Reporting
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );              //(274-275)[2]: State Code
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l16 ), 11, 'N' );                          //(276-286)[11]: State Taxable Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l17 ), 11, 'N' );                          //(287-297)[11]: State income tax
				$line[] = $this->padRecord( '', 10, 'AN' );                                                          //(298-307)[10]: Other State Data
				$line[] = $this->padRecord( '', 1, 'AN' );                                                           //(308)[1]: Tax Type Code [C=City, D=County, E=School District, F=Other]
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l18 ), 11, 'N' );                          //(309-319)[11]: Local Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l19 ), 11, 'N' );                          //(320-330)[11]: Local Income Tax
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->$l15_state_control_number ), 7, 'AN' );   //(331-337)[7]: State Control Number
				$line[] = $this->padRecord( $this->removeDecimal( $this->getL14AmountByName( 'KPER' ) ), 11, 'N' );  //(338-348)[11]: KS: Employee Contribution to KPERS, KP & F and Judges - "KPER" or "KPERS" should appear on W2 under Box 14. - https://www.ksrevenue.org/kpers.html
				$line[] = $this->padRecord( '', 64, 'AN' );                                                          //(349-412)[64]: Supplemental Data 1
				$line[] = $this->padRecord( '', 75, 'AN' );                                                          //(413-487)[75]: Supplemental Data 2
				$line[] = $this->padRecord( '', 25, 'AN' );                                                          //(488-512)[25]: Blank
				break;
			case 'ma': //Massachusetts
				//Withholding Number for State format is the State ID number.
				$line[] = 'RS';                                                                            //(1-2)[2]: RS Record
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );    //(3-4)[2]: State Code
				$line[] = $this->padRecord( '', 5, 'AN' );                                                 //(5-9)[5]: Tax Entity Code [Leave Blank]
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                //(10-18)[9]: SSN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 15, 'AN' );  //(19-33)[15]: First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 15, 'AN' ); //(34-48)[15]: Middle Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );   //(49-68)[20]: Last Name
				$line[] = $this->padRecord( '', 4, 'AN' );                                                 //(69-72)[4]: Suffix
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address2 ), 22, 'AN' );    //(73-94)[22]: Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address1 ), 22, 'AN' );    //(95-116)[22]: Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->city ), 22, 'AN' );        //(117-138)[22]: City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->state ), 2, 'AN' );        //(139-140)[2]: State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->zip_code ), 5, 'AN' );     //(141-145)[5]: Zip
				$line[] = $this->padRecord( '', 4, 'AN' );                                                 //(146-149)[4]: Zip Extension
				$line[] = $this->padRecord( '', 5, 'AN' );                                                 //(150-154)[5]: Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                //(155-177)[23]: Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                //(178-192)[15]: Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                 //(193-194)[2]: Country, fill with blanks if its the US

				//Unemployment reporting
				$line[] = $this->padRecord( '', 2, 'AN' );                                                 //(195-196)[2]: Optional Code
				$line[] = $this->padRecord( '', 6, 'AN' );                                                 //(197-202)[6]: Reporting Period
				$line[] = $this->padRecord( '', 11, 'AN' );                                                //(203-213)[11]: State Quarterly Unemployment Total
				$line[] = $this->padRecord( '', 11, 'AN' );                                                //(214-224)[11]: State Quarterly Unemployment Insurance
				$line[] = $this->padRecord( '', 2, 'AN' );                                                 //(225-226)[2]: Number of weeks worked
				$line[] = $this->padRecord( '', 8, 'AN' );                                                 //(227-234)[8]: Date first employed
				$line[] = $this->padRecord( '', 8, 'AN' );                                                 //(235-242)[8]: Date of separation
				$line[] = $this->padRecord( '', 5, 'AN' );                                                 //(243-247)[5]: Blank
				$line[] = $this->padRecord( '', 20, 'AN' );                                                //(248-267)[20]: State Employer Account Number
				$line[] = $this->padRecord( '', 6, 'AN' );                                                 //(268-273)[6]: Blank

				//Income Tax Reporting
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );    //(274-275)[2]: State Code
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l16 ), 11, 'N' );                //(276-286)[11]: State Taxable Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l17 ), 11, 'N' );                //(287-297)[11]: State income tax
				$line[] = $this->padRecord( '', 10, 'AN' );                                                //(298-307)[10]: Other State Data
				$line[] = $this->padRecord( '', 1, 'AN' );                                                 //(308)[1]: Tax Type Code [C=City, D=County, E=School District, F=Other]
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l18 ), 11, 'N' );                //(309-319)[11]: Local Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l19 ), 11, 'N' );                //(320-330)[11]: Local Income Tax
				$line[] = $this->padRecord( '', 7, 'AN' );                                                 //(331-337)[7]: State Control Number
				$line[] = $this->padRecord( '', 75, 'AN' );                                                //(338-412)[75]: Supplemental Data 1
				$line[] = $this->padRecord( '', 75, 'AN' );                                                //(413-487)[75]: Supplemental Data 2
				$line[] = $this->padRecord( '', 25, 'AN' );                                                //(488-512)[25]: Blank
				break;
			case 'oh': //Ohio - They share with Federal SSA. This is for RITA/Local format instead. It does not include school district taxes.
				//File format specifications: https://www.tax.ohio.gov/Portals/0/employer_withholding/2019%20tax%20year/2018_W2_Specs_v3.pdf
				//Ohio Regional for City reporting.
				//File format specifications: https://cdn.ritaohio.com/Media/700682/2018%20W-2%20SPECS.pdf
				$municipality_code = false;
				$municipality_name = null;
				$tax_type = 'C';

				//District/City Name must contain: [NNNA] or [NNNNA] ie: [123R] or [1234R] or [123C] -- Where R is tax based on residence location and C is tax based on work location.
				$municipality_match = preg_match( '/\[([0-9]{3,4})([RCDEF]{1})\]/i', $this->$l20, $matches );
				if ( isset( $matches[0] ) ) {
					if ( isset( $matches[1] ) ) {
						$municipality_code = str_pad( trim( $matches[1] ), 4, '0', STR_PAD_LEFT );
					}
					if ( isset( $matches[2] ) ) {
						$tax_type = trim( $matches[2] );
					}
				}

				$municipality_name = strtoupper( trim( preg_replace( '/\[.*\]/', '', $this->$l20 ) ) ); //Strip off the square brackets []

				if ( $municipality_code != '' && $tax_type != '' ) {
					//Withholding Number for State format is the State ID number.
					$line[] = 'RS';                                                                            //RS Record
					$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );    //State Code
					$line[] = $this->padRecord( 'R' . $municipality_code, 5, 'AN' );                           //Tax Entity Code (Leave Blank) [5-9]
					$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                //SSN [10-18]
					$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 15, 'AN' );  //First Name
					$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 15, 'AN' ); //Middle Name
					$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );   //Last Name
					$line[] = $this->padRecord( '', 4, 'AN' );                                                 //Suffix
					$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address2 ), 22, 'AN' );    //Location Address
					$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address1 ), 22, 'AN' );    //Delivery Address
					$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->city ), 22, 'AN' );        //City
					$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->state ), 2, 'AN' );        //State
					$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->zip_code ), 5, 'AN' );     //Zip
					$line[] = $this->padRecord( '', 4, 'AN' );                                                 //Zip Extension [146-149]
					$line[] = $this->padRecord( '', 5, 'AN' );                                                 //Blank
					$line[] = $this->padRecord( '', 23, 'AN' );                                                //Foreign State/Province
					$line[] = $this->padRecord( '', 15, 'AN' );                                                //Foreign Postal Code
					$line[] = $this->padRecord( '', 2, 'AN' );                                                 //Country, fill with blanks if its the US

					//Unemployment reporting: Starts at 194
					$line[] = $this->padRecord( '', 2, 'AN' );                                                 //Optional Code
					$line[] = $this->padRecord( '', 6, 'AN' );                                                 //Reporting Period
					$line[] = $this->padRecord( '', 11, 'N' );                                                 //State Quarterly Unemployment Total
					$line[] = $this->padRecord( '', 11, 'N' );                                                 //State Quarterly Unemployment Insurance
					$line[] = $this->padRecord( '', 2, 'AN' );                                                 //Number of weeks worked
					$line[] = $this->padRecord( '', 8, 'AN' );                                                 //Date first employed
					$line[] = $this->padRecord( '', 8, 'AN' );                                                 //Date of separation
					$line[] = $this->padRecord( '', 5, 'AN' );                                                 //Blank
					$line[] = $this->padRecord( $this->stripNonNumeric( $this->$l15_state_id ), 20, 'N' );     //State Employer Account Number
					$line[] = $this->padRecord( '', 6, 'AN' );                                                 //Blank

					//Income Tax Reporting: Starts at 273
					$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );    //State Code [273-275]
					$line[] = $this->padRecord( $this->removeDecimal( $this->$l16 ), 11, 'N' );                //State Taxable Wages [276-286]
					$line[] = $this->padRecord( $this->removeDecimal( $this->$l17 ), 11, 'N' );                //State income tax [287-297]
					$line[] = $this->padRecord( '', 10, 'AN' );                                                //Other State Data [298-307]
					$line[] = $this->padRecord( strtoupper( $tax_type ), 1, 'AN' );                            //Tax Type Code [308] //C=City, D=County, E=School District, F=Other -- For City: R=Residence C=Company/Work Location
					$line[] = $this->padRecord( $this->removeDecimal( $this->$l18 ), 11, 'N' );                //Local Wages [309-319]
					$line[] = $this->padRecord( $this->removeDecimal( $this->$l19 ), 11, 'N' );                //Local Income Tax [320-330]
					$line[] = $this->padRecord( '', 7, 'AN' );                                                 //State Control Number

					if ( isset( $this->efile_agency_id ) && $this->efile_agency_id == '30:US:OH:00:0010' ) {
						$line[] = $this->padRecord( $municipality_name, 75, 'AN' );                                      //Supplemental Data 1
						$line[] = $this->padRecord( strtoupper( $this->_getStateName( $this->$l15_state ) ), 75, 'AN' ); //Supplemental Data 2
					} else {
						$line[] = $this->padRecord( '', 75, 'AN' ); //Supplemental Data 1
						$line[] = $this->padRecord( '', 75, 'AN' ); //Supplemental Data 2
					}
					$line[] = $this->padRecord( '', 25, 'AN' ); //Blank
				} else {
					Debug::Text( 'Skipping RS Record due to incorrect Municipality Code: ' . $municipality_code . ' Tax Type: ' . $tax_type, __FILE__, __LINE__, __METHOD__, 10 );
				}
				break;
			case 'ok': //https://www.ok.gov/tax/documents/What%20is%20the%20purpose%20of%20the%20RS.pdf
				$line[] = 'RS';                                                                            //(1-2)[2]: RS Record
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );    //(3-4)[2]: State Code
				$line[] = $this->padRecord( '', 5, 'AN' );                                                 //(5-9)[5]: Tax Entity Code [Leave Blank]
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                //(10-18)[9]: SSN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 15, 'AN' );  //(19-33)[15]: First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 15, 'AN' ); //(34-48)[15]: Middle Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );   //(49-68)[20]: Last Name
				$line[] = $this->padRecord( '', 4, 'AN' );                                                 //(69-72)[4]: Suffix
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address2 ), 22, 'AN' );    //(73-94)[22]: Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address1 ), 22, 'AN' );    //(95-116)[22]: Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->city ), 22, 'AN' );        //(117-138)[22]: City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->state ), 2, 'AN' );        //(139-140)[2]: State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->zip_code ), 5, 'AN' );     //(141-145)[5]: Zip
				$line[] = $this->padRecord( '', 4, 'AN' );                                                 //(146-149)[4]: Zip Extension
				$line[] = $this->padRecord( '', 5, 'AN' );                                                 //(150-154)[5]: Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                //(155-177)[23]: Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                //(178-192)[15]: Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                 //(193-194)[2]: Country, fill with blanks if its the US

				//Unemployment reporting
				$line[] = $this->padRecord( '', 2, 'AN' );                                                 //(195-196)[2]: Optional Code
				$line[] = $this->padRecord( '', 6, 'AN' );                                                 //(197-202)[6]: Reporting Period
				$line[] = $this->padRecord( '', 11, 'AN' );                                                //(203-213)[11]: State Quarterly Unemployment Total
				$line[] = $this->padRecord( '', 11, 'AN' );                                                //(214-224)[11]: State Quarterly Unemployment Insurance
				$line[] = $this->padRecord( '', 2, 'AN' );                                                 //(225-226)[2]: Number of weeks worked
				$line[] = $this->padRecord( '', 8, 'AN' );                                                 //(227-234)[8]: Date first employed
				$line[] = $this->padRecord( '', 8, 'AN' );                                                 //(235-242)[8]: Date of separation
				$line[] = $this->padRecord( '', 5, 'AN' );                                                 //(243-247)[5]: Blank
				$line[] = $this->padRecord( '', 20, 'AN' );                                                //(248-267)[20]: UI: State Employer Account Number
				$line[] = $this->padRecord( '', 6, 'AN' );                                                 //(268-273)[6]: Blank

				//Income Tax Reporting
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );    //(274-275)[2]: State Code
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l16 ), 11, 'N' );                //(276-286)[11]: State Taxable Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l17 ), 11, 'N' );                //(287-297)[11]: State income tax
				$line[] = $this->padRecord( '', 10, 'AN' );                                                //(298-307)[10]: Other State Data
				$line[] = $this->padRecord( '', 1, 'AN' );                                                 //(308)[1]: Tax Type Code [C=City, D=County, E=School District, F=Other]
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l18 ), 11, 'N' );                //(309-319)[11]: Local Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l19 ), 11, 'N' );                //(320-330)[11]: Local Income Tax
				$line[] = $this->padRecord( '', 7, 'AN' );                                                 //(331-337)[7]: State Control Number
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->$l15_state_id ), 15, 'AN' );    //(338-352)[15]: OK: Oklahoma withholding (WTH) Account Number
				$line[] = $this->padRecord( '', 60, 'AN' );                                                //(353-412)[60]: Supplemental Data 1
				$line[] = $this->padRecord( '', 75, 'AN' );                                                //(413-487)[75]: Supplemental Data 2
				$line[] = $this->padRecord( '', 25, 'AN' );                                                //(488-512)[25]: Blank
				break;
			case 'or': //https://www.oregon.gov/dor/programs/businesses/Documents/iWire-w2-specifications.pdf
				$line[] = 'RS';                                                                            //(1-2)[2]: RS Record
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );    //(3-4)[2]: State Code
				$line[] = $this->padRecord( '', 5, 'AN' );                                                 //(5-9)[5]: Tax Entity Code [Leave Blank]
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                //(10-18)[9]: SSN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 15, 'AN' );  //(19-33)[15]: First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 15, 'AN' ); //(34-48)[15]: Middle Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );   //(49-68)[20]: Last Name
				$line[] = $this->padRecord( '', 4, 'AN' );                                                 //(69-72)[4]: Suffix
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address2 ), 22, 'AN' );    //(73-94)[22]: Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address1 ), 22, 'AN' );    //(95-116)[22]: Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->city ), 22, 'AN' );        //(117-138)[22]: City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->state ), 2, 'AN' );        //(139-140)[2]: State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->zip_code ), 5, 'AN' );     //(141-145)[5]: Zip
				$line[] = $this->padRecord( '', 4, 'AN' );                                                 //(146-149)[4]: Zip Extension
				$line[] = $this->padRecord( '', 5, 'AN' );                                                 //(150-154)[5]: Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                //(155-177)[23]: Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                //(178-192)[15]: Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                 //(193-194)[2]: Country, fill with blanks if its the US

				//Unemployment reporting
				$line[] = $this->padRecord( '', 2, 'AN' );                                                 //(195-196)[2]: Optional Code
				$line[] = $this->padRecord( '', 6, 'AN' );                                                 //(197-202)[6]: Reporting Period
				$line[] = $this->padRecord( '', 11, 'AN' );                                                //(203-213)[11]: State Quarterly Unemployment Total
				$line[] = $this->padRecord( '', 11, 'AN' );                                                //(214-224)[11]: State Quarterly Unemployment Insurance
				$line[] = $this->padRecord( '', 2, 'AN' );                                                 //(225-226)[2]: Number of weeks worked
				$line[] = $this->padRecord( '', 8, 'N' );                                                  //(227-234)[8]: Date first employed
				$line[] = $this->padRecord( '', 8, 'N' );                                                  //(235-242)[8]: Date of separation
				$line[] = $this->padRecord( '', 5, 'AN' );                                                 //(243-247)[5]: Blank
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->$l15_state_id ), 20, 'N' );     //(248-267)[20]: State Employer Account Number
				$line[] = $this->padRecord( '', 6, 'AN' );                                                 //(268-273)[6]: Blank

				//Income Tax Reporting
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );    //(274-275)[2]: State Code
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l16 ), 11, 'N' );                //(276-286)[11]: State Taxable Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l17 ), 11, 'N' );                //(287-297)[11]: State income tax
				$line[] = $this->padRecord( '', 50, 'AN' );                                                //(298-347)[50]: OR: Other State Data
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l18 ), 11, 'N' );                //(348-358)[11]: Statewide Transit Tax Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l19 ), 11, 'N' );                //(359-369)[11]: Statewide Transit Tax Income Tax Withheld
				$line[] = $this->padRecord( '', 143, 'AN' );                                               //(370-512)[143]: Blank
				break;
			case 'pa': //https://www.revenue.pa.gov/GeneralTaxInformation/Tax%20Types%20and%20Information/EmployerWithholding/Documents/EFW2-EFW2C_reporting_inst_and_specs.pdf
				$line[] = 'RS';                                                                            //(1-2)[2]: RS Record
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );    //(3-4)[2]: State Code
				$line[] = $this->padRecord( '', 5, 'AN' );                                                 //(5-9)[5]: Tax Entity Code [Leave Blank]
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                //(10-18)[9]: SSN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 15, 'AN' );  //(19-33)[15]: First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 15, 'AN' ); //(34-48)[15]: Middle Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );   //(49-68)[20]: Last Name
				$line[] = $this->padRecord( '', 4, 'AN' );                                                 //(69-72)[4]: Suffix
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address2 ), 22, 'AN' );    //(73-94)[22]: Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address1 ), 22, 'AN' );    //(95-116)[22]: Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->city ), 22, 'AN' );        //(117-138)[22]: City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->state ), 2, 'AN' );        //(139-140)[2]: State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->zip_code ), 5, 'AN' );     //(141-145)[5]: Zip
				$line[] = $this->padRecord( '', 4, 'AN' );                                                 //(146-149)[4]: Zip Extension
				$line[] = $this->padRecord( '', 5, 'AN' );                                                 //(150-154)[5]: Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                //(155-177)[23]: Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                //(178-192)[15]: Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                 //(193-194)[2]: Country, fill with blanks if its the US

				//Unemployment reporting
				$line[] = $this->padRecord( '', 2, 'AN' );                                                 //(195-196)[2]: Optional Code
				$line[] = $this->padRecord( '', 6, 'AN' );                                                 //(197-202)[6]: Reporting Period
				$line[] = $this->padRecord( '', 11, 'N' );                                                 //(203-213)[11]: State Quarterly Unemployment Total
				$line[] = $this->padRecord( '', 11, 'N' );                                                 //(214-224)[11]: State Quarterly Unemployment Insurance
				$line[] = $this->padRecord( '', 2, 'AN' );                                                 //(225-226)[2]: Number of weeks worked
				$line[] = $this->padRecord( '', 8, 'AN' );                                                 //(227-234)[8]: Date first employed
				$line[] = $this->padRecord( '', 8, 'AN' );                                                 //(235-242)[8]: Date of separation
				$line[] = $this->padRecord( '', 5, 'AN' );                                                 //(243-247)[5]: Blank
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->$l15_state_id ), 8, 'N' );      //(248-255)[8]: State Employer Account Number
				$line[] = $this->padRecord( '', 12, 'AN' );                                                //(256-267)[12]: Blank
				$line[] = $this->padRecord( '', 6, 'AN' );                                                 //(268-273)[6]: Blank

				//Income Tax Reporting
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );    //(274-275)[2]: State Code
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l16 ), 11, 'N' );                //(276-286)[11]: State Taxable Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l17 ), 11, 'N' );                //(287-297)[11]: State income tax
				$line[] = $this->padRecord( '', 10, 'AN' );                                                //(298-307)[10]: Other State Data
				$line[] = $this->padRecord( '', 1, 'AN' );                                                 //(308)[1]: Tax Type Code [C=City, D=County, E=School District, F=Other]
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l18 ), 11, 'N' );                //(309-319)[11]: Local Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l19 ), 11, 'N' );                //(320-330)[11]: Local Income Tax
				$line[] = $this->padRecord( '', 7, 'AN' );                                                 //(331-337)[7]: State Control Number
				$line[] = $this->padRecord( 0, 9, 'N' );                                                   //(338-346)[9]: Employees ITIN as shown on card issued by SSA
				$line[] = $this->padRecord( '', 166, 'AN' );                                               //(347-512)[166]: Blank
				break;
			case 'in': // Indiana - Has Local Taxes - https://www.in.gov/dor/files/w-2book.pdf
				$line[] = 'RS';                                                                                          //(1-2)[2]: RS Record
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );                  //(3-4)[2]: State Code
				$line[] = $this->padRecord( '', 5, 'AN' );                                                               //(5-9)[5]: Tax Entity Code [Leave Blank]
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                              //(10-18)[9]: SSN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 15, 'AN' );                //(19-33)[15]: First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 15, 'AN' );               //(34-48)[15]: Middle Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );                 //(49-68)[20]: Last Name
				$line[] = $this->padRecord( '', 4, 'AN' );                                                               //(69-72)[4]: Suffix
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address2 ), 22, 'AN' );                  //(73-94)[22]: Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address1 ), 22, 'AN' );                  //(95-116)[22]: Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->city ), 22, 'AN' );                      //(117-138)[22]: City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->state ), 2, 'AN' );                      //(139-140)[2]: State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->zip_code ), 5, 'AN' );                   //(141-145)[5]: Zip
				$line[] = $this->padRecord( '', 4, 'AN' );                                                               //(146-149)[4]: Zip Extension
				$line[] = $this->padRecord( '', 5, 'AN' );                                                               //(150-154)[5]: Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                              //(155-177)[23]: Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                              //(178-192)[15]: Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                               //(193-194)[2]: Country, fill with blanks if its the US

				//Unemployment reporting
				$line[] = $this->padRecord( '', 2, 'AN' );                                                               //(195-196)[2]: Optional Code
				$line[] = $this->padRecord( '', 6, 'AN' );                                                               //(197-202)[6]: Reporting Period
				$line[] = $this->padRecord( '', 11, 'N' );                                                               //(203-213)[11]: State Quarterly Unemployment Total
				$line[] = $this->padRecord( '', 11, 'N' );                                                               //(214-224)[11]: State Quarterly Unemployment Insurance
				$line[] = $this->padRecord( '', 2, 'AN' );                                                               //(225-226)[2]: Number of weeks worked
				$line[] = $this->padRecord( '', 8, 'AN' );                                                               //(227-234)[8]: Date first employed
				$line[] = $this->padRecord( '', 8, 'AN' );                                                               //(235-242)[8]: Date of separation
				$line[] = $this->padRecord( '', 5, 'AN' );                                                               //(243-247)[5]: Blank
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->$l15_state_id ), 20, 'N' );                   //(248-267)[20]: State Employer Account Number
				$line[] = $this->padRecord( '', 6, 'AN' );                                                               //(268-273)[6]: Blank

				//Income Tax Reporting
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );                  //(274-275)[2]: State Code
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l16 ), 11, 'N' );                              //(276-286)[11]: State Taxable Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l17 ), 11, 'N' );                              //(287-297)[11]: State income tax
				$line[] = $this->padRecord( '', 10, 'AN' );                                                              //(298-307)[10]: Other State Data
				$line[] = $this->padRecord( '', 1, 'AN' );                                                               //(308)[1]: Tax Type Code [C=City, D=County, E=School District, F=Other]
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l18 ), 11, 'N' );                              //(309-319)[11]: Local Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l19 ), 11, 'N' );                              //(320-330)[11]: Local Income Tax
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->$l15_state_id ), 10, 'N' );                   //(331-340)[10]: IN: Indiana Employer Taxpayer ID (TID). Does not include the 3 digit location.
				$line[] = $this->padRecord( $this->stripNonNumeric( substr( $this->$l15_state_id, -3 ) ), 3, 'N' );      //(341-343)[3]: IN: Indiana Employer Taxpayer ID (TID) Location. Last 3 digits of the State ID.
				$line[] = $this->padRecord( '', 169, 'AN' );                                                             //(444-512)[169]: Blank
				break;
			default: //Federal
				//Withholding Number for State format is the State ID number.
				$line[] = 'RS';                                                                                    //(1-2)[2]: RS Record
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );            //(3-4)[2]: State Code
				$line[] = $this->padRecord( '', 5, 'AN' );                                                         //(5-9)[5]: Tax Entity Code [Leave Blank]
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->ssn ), 9, 'N' );                        //(10-18)[9]: SSN
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->first_name ), 15, 'AN' );          //(19-33)[15]: First Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->middle_name ), 15, 'AN' );         //(34-48)[15]: Middle Name
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->last_name ), 20, 'AN' );           //(49-68)[20]: Last Name
				$line[] = $this->padRecord( '', 4, 'AN' );                                                         //(69-72)[4]: Suffix
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address2 ), 22, 'AN' );            //(73-94)[22]: Location Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->address1 ), 22, 'AN' );            //(95-116)[22]: Delivery Address
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->city ), 22, 'AN' );                //(117-138)[22]: City
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->state ), 2, 'AN' );                //(139-140)[2]: State
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->zip_code ), 5, 'AN' );             //(141-145)[5]: Zip
				$line[] = $this->padRecord( '', 4, 'AN' );                                                         //(146-149)[4]: Zip Extension
				$line[] = $this->padRecord( '', 5, 'AN' );                                                         //(150-154)[5]: Blank
				$line[] = $this->padRecord( '', 23, 'AN' );                                                        //(155-177)[23]: Foreign State/Province
				$line[] = $this->padRecord( '', 15, 'AN' );                                                        //(178-192)[15]: Foreign Postal Code
				$line[] = $this->padRecord( '', 2, 'AN' );                                                         //(193-194)[2]: Country, fill with blanks if its the US

				//Unemployment reporting
				$line[] = $this->padRecord( '', 2, 'AN' );                                                         //(195-196)[2]: Optional Code
				$line[] = $this->padRecord( '', 6, 'AN' );                                                         //(197-202)[6]: Reporting Period
				$line[] = $this->padRecord( '', 11, 'N' );                                                         //(203-213)[11]: State Quarterly Unemployment Total
				$line[] = $this->padRecord( '', 11, 'N' );                                                         //(214-224)[11]: State Quarterly Unemployment Insurance
				$line[] = $this->padRecord( '', 2, 'AN' );                                                         //(225-226)[2]: Number of weeks worked
				$line[] = $this->padRecord( '', 8, 'AN' );                                                         //(227-234)[8]: Date first employed
				$line[] = $this->padRecord( '', 8, 'AN' );                                                         //(235-242)[8]: Date of separation
				$line[] = $this->padRecord( '', 5, 'AN' );                                                         //(243-247)[5]: Blank
				$line[] = $this->padRecord( $this->stripNonAlphaNumeric( $this->$l15_state_id ), 20, 'AN' );       //(248-267)[20]: State Employer Account Number
				$line[] = $this->padRecord( '', 6, 'AN' );                                                         //(268-273)[6]: Blank

				//Income Tax Reporting
				$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );            //(274-275)[2]: State Code
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l16 ), 11, 'N' );                        //(276-286)[11]: State Taxable Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l17 ), 11, 'N' );                        //(287-297)[11]: State income tax
				$line[] = $this->padRecord( '', 10, 'AN' );                                                        //(298-307)[10]: Other State Data
				$line[] = $this->padRecord( '', 1, 'AN' );                                                         //(308)[1]: Tax Type Code [C=City, D=County, E=School District, F=Other]
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l18 ), 11, 'N' );                        //(309-319)[11]: Local Wages
				$line[] = $this->padRecord( $this->removeDecimal( $this->$l19 ), 11, 'N' );                        //(320-330)[11]: Local Income Tax
				$line[] = $this->padRecord( $this->stripNonNumeric( $this->$l15_state_control_number ), 7, 'AN' ); //(331-337)[7]: State Control Number
				$line[] = $this->padRecord( '', 75, 'AN' );                                                        //(338-412)[75]: Supplemental Data 1
				$line[] = $this->padRecord( '', 75, 'AN' );                                                        //(413-487)[75]: Supplemental Data 2
				$line[] = $this->padRecord( '', 25, 'AN' );                                                        //(488-512)[25]: Blank
				break;
		}

		if ( isset( $line ) ) {
			$retval = implode( ( $this->debug == true ) ? ',' : '', $line );
			if ( $this->debug == false && strlen( $retval ) != 512 ) {
				Debug::Text( 'ERROR! RS Record length is incorrect, should be 512 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}

			Debug::Text( 'RS Record: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		} else {
			Debug::Text( 'Skipping RS Record... ', __FILE__, __LINE__, __METHOD__, 10 );
		}
	}

	function _compileRO() { //RO (Employee Optional)
		if ( in_array( strtoupper( $this->efile_state ), [ 'NY', 'AL', 'CO', 'CT', 'DE', 'KS', 'MA', 'PA', 'VA' ] ) ) { //Skip for eFiling in these states.
			return false;
		}

		$line[] = 'RO';                                                                                                                                //Employee Optional
		$line[] = $this->padRecord( '', 9, 'AN' );                                                                                                     //Blanks
		$line[] = $this->padRecord( $this->removeDecimal( $this->l8 ), 11, 'N' );                                                                      //(12-22) Allocated Tips
		$line[] = $this->padRecord( $this->removeDecimal( bcadd( $this->_getL12AmountByCode( 'A' ), $this->_getL12AmountByCode( 'B' ) ) ), 11, 'N' );  //(23-33) Uncollected Employee Tax on Tips (Codes A and B)
		$line[] = $this->padRecord( $this->removeDecimal( $this->_getL12AmountByCode( 'R' ) ), 11, 'N' );                                              //(34-44) Medical Savings Account (Code R)
		$line[] = $this->padRecord( $this->removeDecimal( $this->_getL12AmountByCode( 'S' ) ), 11, 'N' );                                              //(45-55) Simple Retirement Account (Code S)
		$line[] = $this->padRecord( $this->removeDecimal( $this->_getL12AmountByCode( 'T' ) ), 11, 'N' );                                              //(56-66) Qualified Adoption Expenses (Code T)
		$line[] = $this->padRecord( $this->removeDecimal( $this->_getL12AmountByCode( 'M' ) ), 11, 'N' );                                              //(67-77) Uncollected Social Security or RRTA Tax on Cost ofGroup Term Life Insurance Over $50,000 (Code M)
		$line[] = $this->padRecord( $this->removeDecimal( $this->_getL12AmountByCode( 'N' ) ), 11, 'N' );                                              //(78-88) Uncollected Medicare Tax on Cost of Group TermLife Insurance Over$50,000 (Code N)
		$line[] = $this->padRecord( $this->removeDecimal( $this->_getL12AmountByCode( 'Z' ) ), 11, 'N' );                                              //(89-99) Income Under a NonqualifiedDeferredCompensation PlanThat Fails to SatisfySection 409A (Code Z)
		$line[] = $this->padRecord( '', 11, 'AN' );                                                                                                    //(100-110) Blank
		$line[] = $this->padRecord( $this->removeDecimal( $this->_getL12AmountByCode( 'EE' ) ), 11, 'N' );                                             //(111-121) Designated Roth Contributions Under a Governmental Section 457(b) Plan (Code EE)
		$line[] = $this->padRecord( $this->removeDecimal( $this->_getL12AmountByCode( 'GG' ) ), 11, 'N' );                                             //(122-132) Income from Qualified Equity Grants Under Section 83(i) (Code GG)
		$line[] = $this->padRecord( $this->removeDecimal( $this->_getL12AmountByCode( 'HH' ) ), 11, 'N' );                                             //(133-143) Aggregate Deferrals Under Section 83(i) Elections as of theClose of the CalendarYear (Code HH)
		$line[] = $this->padRecord( '', 131, 'AN' );                                                                                                   //(144-274) Blank
		$line[] = $this->padRecord( 0, 11, 'N' );                                                                                                      //(275-285) Wages Subject to Puerto Rico Tax
		$line[] = $this->padRecord( 0, 11, 'N' );                                                                                                      //(286-296) Commissions Subject to Puerto Rico Tax
		$line[] = $this->padRecord( 0, 11, 'N' );                                                                                                      //(297-307) Allowances Subject to Puerto Rico Tax
		$line[] = $this->padRecord( 0, 11, 'N' );                                                                                                      //(308-318) Tips Subject to Puerto Rico Tax
		$line[] = $this->padRecord( 0, 11, 'N' );                                                                                                      //(319-329) Total Wages, Commissions, Tips  and Allowances Subject to Puerto Rico Tax
		$line[] = $this->padRecord( 0, 11, 'N' );                                                                                                      //(330-340) Puerto Rico Tax Withheld
		$line[] = $this->padRecord( 0, 11, 'N' );                                                                                                      //(341-351) Retirement Fund Annual Contributions
		$line[] = $this->padRecord( '', 11, 'AN' );                                                                                                    //(352-362) Blank
		$line[] = $this->padRecord( 0, 11, 'N' );                                                                                                      //(363-373) Total Wages, Tips and Other Compensation Subject to Virgin Islands, Guam, American Samoa or Northern Mariana Islands Income Tax
		$line[] = $this->padRecord( 0, 11, 'N' );                                                                                                      //(374-384) Virgin Islands, Guam, American Samoa or  Northern Mariana Islands Income Tax Withheld
		$line[] = $this->padRecord( '', 128, 'AN' );                                                                                                   //Blank

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 512 ) {
			Debug::Text( 'ERROR! RO Record length is incorrect, should be 512 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( 'RO Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _compileRU( $total ) { //RU (Total Optional) Record
		if ( in_array( strtoupper( $this->efile_state ), [ 'AL', 'CO', 'CT', 'DE', 'KS', 'MA', 'PA', 'VA' ] ) ) { //Skip for eFiling in these states.
			return false;
		}

		$line[] = 'RU';                                                                                           //Employee Optional
		$line[] = $this->padRecord( $total->total, 7, 'N' );                                                      //(3-9) Total Number of RO Records

		switch ( strtolower( $this->efile_state ) ) {
			case 'ny':
				$line[] = $this->padRecord( '', 503, 'AN' );                                                          //(10-512) Blank
				break;
			default:
				$line[] = $this->padRecord( $this->removeDecimal( $total->l8 ), 15, 'N' );                            //(10-24) Total Allocated Tips
				$line[] = $this->padRecord( $this->removeDecimal( bcadd( $total->l12a, $total->l12b ) ), 15, 'N' );   //(25-39) Total Uncollected Employee Tax on Tips (Codes A and B)
				$line[] = $this->padRecord( $this->removeDecimal( $total->l12r ), 15, 'N' );                          //(40-54) Total Medica Savings Account (Code R)
				$line[] = $this->padRecord( $this->removeDecimal( $total->l12s ), 15, 'N' );                          //(55-69) Total Simpl Retirement Account (Code S)
				$line[] = $this->padRecord( $this->removeDecimal( $total->l12t ), 15, 'N' );                          //(70-84) Total Qualified Adoption Expenses (Code T)
				$line[] = $this->padRecord( $this->removeDecimal( $total->l12m ), 15, 'N' );                          //(85-99) Total Uncollected Social Security or RRTA Tax on Cost of Group Term Life Insurance Over $50,000 (Code M)
				$line[] = $this->padRecord( $this->removeDecimal( $total->l12n ), 15, 'N' );                          //(100-114) Total Uncollected Medicare Tax on Cost of Group Term Life Insurance Over $50,000 (Code N)
				$line[] = $this->padRecord( $this->removeDecimal( $total->l12z ), 15, 'N' );                          //(115-129) Total Income Under a Nonqualified Deferred Compensation Plan That Fails to Satisfy Section 409A (Code Z)
				$line[] = $this->padRecord( '', 15, 'AN' );                                                           //(130-144) Blank
				$line[] = $this->padRecord( $this->removeDecimal( $total->l12ee ), 15, 'N' );                         //(145-159) Total Designated Roth Contributions Under a Governmental Section 457(b) Plan (Code EE)
				$line[] = $this->padRecord( $this->removeDecimal( $total->l12gg ), 15, 'N' );                         //(160-174) Total Income from Qualified Equity Grants Under Section 83(i) (Code GG)
				$line[] = $this->padRecord( $this->removeDecimal( $total->l12hh ), 15, 'N' );                         //(175-189) Total Aggregate Deferrals Under Section 83(i) Elections as of the Close of the Calendar Year(Code HH)
				$line[] = $this->padRecord( '', 165, 'AN' );                                                          //(190-354) Blank
				$line[] = $this->padRecord( 0, 15, 'N' );                                                             //(355-369) Wages Subject to Puerto Rico Tax
				$line[] = $this->padRecord( 0, 15, 'N' );                                                             //(370-384) Commissions Subject to Puerto Rico Tax
				$line[] = $this->padRecord( 0, 15, 'N' );                                                             //(385-399) Allowances Subject to Puerto Rico Tax
				$line[] = $this->padRecord( 0, 15, 'N' );                                                             //(400-414) Tips Subject to Puerto Rico Tax
				$line[] = $this->padRecord( 0, 15, 'N' );                                                             //(415-429) Total Wages, Commissions, Tips  and Allowances Subject to Puerto Rico Tax
				$line[] = $this->padRecord( 0, 15, 'N' );                                                             //(430-444) Puerto Rico Tax Withheld
				$line[] = $this->padRecord( 0, 15, 'N' );                                                             //(445-459) Retirement Fund Annual Contributions
				$line[] = $this->padRecord( 0, 15, 'N' );                                                             //(460-474) Total Wages, Tips and Other Compensation Subject to Virgin Islands, Guam, American Samoa or Northern Mariana Islands Income Tax
				$line[] = $this->padRecord( 0, 15, 'N' );                                                             //(475-489) Virgin Islands, Guam, American Samoa or  Northern Mariana Islands Income Tax Withheld
				$line[] = $this->padRecord( '', 23, 'AN' );                                                           //(490-512) Blank
				break;
		}

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 512 ) {
			Debug::Text( 'ERROR! RU Record length is incorrect, should be 512 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( 'RU Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _compileRT( $total ) { //RT (Total) Record - Total number of RW records reported since the last RE (Employer) record.
		if ( in_array( strtoupper( $this->efile_state ), [ 'NY', 'AL', 'CO', 'DE', 'PA', 'VA' ] ) ) { //Skip for eFiling in these states.
			return false;
		}

		$line[] = 'RT';                                                               //(1-2)[2]: RT Record

		switch ( strtolower( $this->efile_state ) ) {
			case 'ct':
				$line[] = $this->padRecord( $total->total, 7, 'N' );                                          //(3-9)[7]: Total RS records.
				$line[] = $this->padRecord( $this->removeDecimal( $total->state_taxable_wages ), 15, 'N' );   //(10-24)[15]: CT: State Taxable Wages
				$line[] = $this->padRecord( $this->removeDecimal( $total->state_income_tax ), 15, 'N' );      //(25-39)[15]: CT: State Tax Withheld
				$line[] = $this->padRecord( '', 473, 'AN' );                                                  //(40-512)[473]: Blank
				break;
			case 'ma':
				$line[] = $this->padRecord( $total->total, 7, 'N' );                                          //(3-9)[7]: Total RS records.
				$line[] = $this->padRecord( $this->removeDecimal( $total->state_taxable_wages ), 15, 'N' );   //(10-24)[15]: MA: State Taxable Wages
				$line[] = $this->padRecord( $this->removeDecimal( $total->state_income_tax ), 15, 'N' );      //(25-39)[15]: MA: State Tax Withheld
				$line[] = $this->padRecord( '', 473, 'AN' );                                                  //(40-512)[473]: Blank
				break;
			default:
				$line[] = $this->padRecord( $total->total, 7, 'N' );                          //(3-9)[7]: Total RW records.
				$line[] = $this->padRecord( $this->removeDecimal( $total->l1 ), 15, 'N' );    //(10-24)[15]: Wages, Tips and Other Compensation
				$line[] = $this->padRecord( $this->removeDecimal( $total->l2 ), 15, 'N' );    //(25-39)[15]: Federal Income Tax
				$line[] = $this->padRecord( $this->removeDecimal( $total->l3 ), 15, 'N' );    //(40-54)[15]: Social Security Wages
				$line[] = $this->padRecord( $this->removeDecimal( $total->l4 ), 15, 'N' );    //(55-69)[15]: Social Security Tax
				$line[] = $this->padRecord( $this->removeDecimal( $total->l5 ), 15, 'N' );    //(70-84)[15]: Medicare Wages and Tips
				$line[] = $this->padRecord( $this->removeDecimal( $total->l6 ), 15, 'N' );    //(85-99)[15]: Medicare Tax
				$line[] = $this->padRecord( $this->removeDecimal( $total->l7 ), 15, 'N' );    //(100-114)[15]: Social Security Tips
				$line[] = $this->padRecord( '', 15, 'AN' );                                   //(115-129)[15]: Blank
				$line[] = $this->padRecord( $this->removeDecimal( $total->l10 ), 15, 'N' );   //(130-144)[15]: Dependant Care Benefits
				$line[] = $this->padRecord( $this->removeDecimal( $total->l12d ), 15, 'N' );  //(145-159)[15]: Deferred Compensation Contributions to 401K (Code D)
				$line[] = $this->padRecord( $this->removeDecimal( $total->l12e ), 15, 'N' );  //(160-174)[15]: Deferred Compensation Contributions to 403(b) (Code E)
				$line[] = $this->padRecord( $this->removeDecimal( $total->l12f ), 15, 'N' );  //(175-189)[15]: Deferred Compensation Contributions to 408(k)(6) (Code F)
				$line[] = $this->padRecord( $this->removeDecimal( $total->l12g ), 15, 'N' );  //(190-204)[15]: Deferred Compensation Contributions to 457(b) (Code G)
				$line[] = $this->padRecord( $this->removeDecimal( $total->l12h ), 15, 'N' );  //(205-219)[15]: Deferred Compensation Contributions to 501(c)(18)(D) (Code H)
				$line[] = $this->padRecord( '', 15, 'AN' );                                   //(220-234)[15]: Blank
				$line[] = $this->padRecord( $this->removeDecimal( $total->l11 ), 15, 'N' );   //(235-249)[15]: Non-qualified Plan Section 457
				$line[] = $this->padRecord( $this->removeDecimal( $total->l12w ), 15, 'N' );  //(250-264)[15]: Employer Contributions to Health Savings Account (Code W)
				$line[] = $this->padRecord( 0, 15, 'N' );                                     //(265-279)[15]: Non-qualified NOT Plan Section 457
				$line[] = $this->padRecord( 0, 15, 'N' );                                     //(280-294)[15]: Non taxable combat pay
				$line[] = $this->padRecord( $this->removeDecimal( $total->l12dd ), 15, 'N' ); //(295-309)[15]: Cost of Employer Sponsored Health Coverage (Code DD)
				$line[] = $this->padRecord( 0, 15, 'N' );                                     //(310-324)[15]: Employer Cost of Premiums for Group Term Life Insurance over $50K
				$line[] = $this->padRecord( 0, 15, 'N' );                                     //(325-339)[15]: 3rd party sick pay.
				$line[] = $this->padRecord( 0, 15, 'N' );                                     //(340-354)[15]: Income from the Exercise of Nonstatutory Stock Options
				$line[] = $this->padRecord( 0, 15, 'N' );                                     //(355-369)[15]: Deferrals Under a Section 409A non-qualified plan
				$line[] = $this->padRecord( $this->removeDecimal( $total->l12aa ), 15, 'N' ); //(370-384)[15]: Desiginated Roth Contributions under a section 401K (Code AA)
				$line[] = $this->padRecord( $this->removeDecimal( $total->l12bb ), 15, 'N' ); //(385-399)[15]: Desiginated Roth Contributions under a section 403B (Code BB)
				$line[] = $this->padRecord( 0, 15, 'N' );                                     //(400-414)[15]: Permitted Benefits Under a Qualified Small Employer Health Reimbursement (Code FF)
				$line[] = $this->padRecord( '', 98, 'AN' );                                   //(415-512)[98]: Blank
				break;
		}

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 512 ) {
			Debug::Text( 'ERROR! RT Record length is incorrect, should be 512 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Text( 'RT Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _compileRV( $total ) { //RV (State Total) Record - **OPTIONAL** Not processed or shared by SSA or IRS. Custom to each state.
		if ( $this->efile_state == '' ) { //Federal filing does not need any RS record at all.
			return false;
		}

		if ( $this->efile_state == '' || !in_array( strtoupper( $this->efile_state ), [ 'IL', 'MO', 'MT', 'ND', 'NE', 'OK', 'OR', 'PA' ] ) ) { //**THIS IS OPPOSITE AS OTHER FUNCTIONS** - Skip for eFiling in these states.
			return false;
		}


		if ( $total->total > 0 ) {
			$line[] = 'RV';                                                                             //RT Record

			switch ( strtolower( $this->efile_state ) ) {
				case 'mo': // https://dor.mo.gov/business/withhold/documents/MissouriAnnualW-2FilingGuidelines.pdf
					foreach ( range( 'a', 'z' ) as $z ) {
						$l15_state = 'l15' . $z . '_state';
						if ( strtolower( $this->$l15_state ) == strtolower( $this->efile_state ) ) {
							$l15_state_id = 'l15' . $z . '_state_id';
							break;
						}
					}

					$line[] = $this->padRecord( $this->year, 4, 'N' );                                                       //(3-6)[4]: Tax Year
					$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                              //(7-15)[9]: FEIN
					$line[] = $this->padRecord( $this->stripNonNumeric( $this->$l15_state_id ), 8, 'N' );                    //(16-23)[8]: State ID Number
					$line[] = $this->padRecord( $this->trade_name, 57, 'AN' );                                               //(24-80)[57]: Employer Name
					$line[] = $this->padRecord( $total->total, 6, 'N' );                                                     //(81-86)[6]: Employer Number of W2's
					$line[] = $this->padRecord( $this->removeDecimal( $total->state_income_tax ), 12, 'N' );                 //(87-98)[12]: Employer TOtal Tax Withheld as shown on W2s
					$line[] = $this->padRecord( '', 414, 'AN' );                                                             //(99-512)[414]: Blank
					break;
				case 'ok': // https://www.ok.gov/tax/documents/What%20is%20the%20purpose%20of%20the%20RV.pdf
					foreach ( range( 'a', 'z' ) as $z ) {
						$l15_state = 'l15' . $z . '_state';
						if ( strtolower( $this->$l15_state ) == strtolower( $this->efile_state ) ) {
							$l15_state_id = 'l15' . $z . '_state_id';
							break;
						}
					}

					$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );                     //(3-4)[2]: State Code
					$line[] = $this->padRecord( $total->total, 7, 'N' );                                                        //(5-11)[7]: Total RS records.
					$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                                 //(12-20)[9]: FEIN
					$line[] = $this->padRecord( '', 21, 'AN' );                                                                 //(21-41)[21]: Blank
					$line[] = $this->padRecord( $this->removeDecimal( $total->state_taxable_wages ), 15, 'N' );                 //(42-56)[15]: State Taxable Wages
					$line[] = $this->padRecord( $this->removeDecimal( $total->state_income_tax ), 15, 'N' );                    //(57-71)[15]: State Income Tax Withheld
					$line[] = $this->padRecord( $this->removeDecimal( $total->local_taxable_wages ), 15, 'N' );                 //(72-86)[15]: Local Taxable Wages
					$line[] = $this->padRecord( $this->removeDecimal( $total->local_income_tax ), 15, 'N' );                    //(87-101)[15]: Local Income Tax Withheld
					$line[] = $this->padRecord( '', 411, 'AN' );                                                                //(102-512)[411]: Blank
					break;
				case 'or': // https://www.oregon.gov/dor/programs/businesses/Documents/iWire-w2-specifications.pdf
					$line[] = $this->padRecord( $total->total, 7, 'N' );                                                     //(3-9)[2]: Total RS records.
					$line[] = $this->padRecord( $this->removeDecimal( $total->state_taxable_wages ), 15, 'N' );              //(10-24)[15]: State Taxable Wages
					$line[] = $this->padRecord( $this->removeDecimal( $total->state_income_tax ), 15, 'N' );                 //(25-39)[15]: State Income Tax Withheld
					$line[] = $this->padRecord( $this->removeDecimal( $total->local_taxable_wages ), 15, 'N' );              //(40-54)[15]: OR: Satewide Transit Taxable Wages
					$line[] = $this->padRecord( $this->removeDecimal( $total->local_income_tax ), 15, 'N' );                 //(55-69)[15]: OR: Satewide Transit Tax Withheld
					$line[] = $this->padRecord( '', 443, 'AN' );                                                             //(70-512)[443]: Blank
					break;
				case 'pa': // https://www.revenue.pa.gov/GeneralTaxInformation/Tax%20Types%20and%20Information/EmployerWithholding/Documents/EFW2-EFW2C_reporting_inst_and_specs.pdf
					foreach ( range( 'a', 'z' ) as $z ) {
						$l15_state = 'l15' . $z . '_state';
						if ( strtolower( $this->$l15_state ) == strtolower( $this->efile_state ) ) {
							$l15_state_id = 'l15' . $z . '_state_id';
							break;
						}
					}

					$line[] = $this->padRecord( $this->_getStateNumericCode( $this->$l15_state ), 2, 'N' );                  //(3-4)[2]: State Code
					$line[] = $this->padRecord( $this->year, 4, 'N' );                                                       //(5-8)[4]: Tax Year
					$line[] = $this->padRecord( $this->stripNonNumeric( $this->$l15_state_id ), 8, 'N' );                    //(9-16)[8]: PA Employer Account ID
					$line[] = $this->padRecord( $this->stripNonNumeric( $this->ein ), 9, 'N' );                              //(17-25)[9]: Employer Entity ID
					$line[] = $this->padRecord( $total->total, 7, 'N' );                                                     //(26-32)[7]: Total RS records.
					$line[] = $this->padRecord( $this->removeDecimal( $total->state_taxable_wages ), 15, 'N' );              //(33-47)[15]: State Taxable Wages
					$line[] = $this->padRecord( $this->removeDecimal( $total->state_income_tax ), 15, 'N' );                 //(48-62)[15]: State Income Tax Withheld
					$line[] = $this->padRecord( '', 450, 'AN' );                                                             //(63-512)[450]: Blank
					break;
				default:
					//SSA specifications state RV records are optional and not processed or shared anyways.
					//$retval = false;

					$line[] = $this->padRecord( $total->total, 7, 'N' );                                        //Total RW records.
					$line[] = $this->padRecord( $this->removeDecimal( $total->state_taxable_wages ), 15, 'N' ); //State Wages, Tips and Other Compensation
					$line[] = $this->padRecord( $this->removeDecimal( $total->state_income_tax ), 15, 'N' );    //State Income Tax
					$line[] = $this->padRecord( '', 473, 'AN' );                                                //Blank

					break;
			}

			$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

			if ( $this->debug == false && strlen( $retval ) != 512 ) {
				Debug::Text( 'ERROR! RV Record length is incorrect, should be 512 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}

			Debug::Text( 'RV Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}

		return false;
	}

	function _compileRF( $total ) { //RF (Final) Record - Total number of RW (Employee) Records reported on the entire file.
		if ( in_array( strtoupper( $this->efile_state ), [ 'AL' ] ) ) { //Skip for eFiling in these states.
			return false;
		}

		$line[] = 'RF';                                          //RF Record

		switch ( strtolower( $this->efile_state ) ) {
			case 'ct':
				$line[] = $this->padRecord( $total->total, 9, 'N' );                                          //(3-11)[9]: Total RS records.
				$line[] = $this->padRecord( $this->removeDecimal( $total->state_taxable_wages ), 16, 'N' );   //(12-27)[16]: CT: State Taxable Wages
				$line[] = $this->padRecord( $this->removeDecimal( $total->state_income_tax ), 16, 'N' );      //(28-43)[16]: CT: State Tax Withheld
				$line[] = $this->padRecord( '', 469, 'AN' );                                                  //(44-512)[469]: Blank
				break;
			case 'ny':
			case 'pa':
				$line[] = $this->padRecord( '', 510, 'AN' );          //Blank
				break;
			default:
				$line[] = $this->padRecord( '', 5, 'AN' );            //Blank
				$line[] = $this->padRecord( $total->total, 9, 'N' );  //Total RW records.
				$line[] = $this->padRecord( '', 496, 'AN' );          //Blank
				break;
		}

		$retval = implode( ( $this->debug == true ) ? ',' : '', $line );

		if ( $this->debug == false && strlen( $retval ) != 512 ) {
			Debug::Text( 'ERROR! RF Record length is incorrect, should be 512 is: ' . strlen( $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
		Debug::Text( 'RF Record:' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	//Fixed length field EFW2C format
	function _outputEFILE( $type = null ) {
		//DISABLED FOR NOW as it hasn't been implemented yet!
		return false;


		/*
		 Submitter Record (RA)
		 Employer Record (RE)
		 Employee Wage Records (RW AND RO)
		 State Wage Record (RS)
		 Total Records (RT and RU)
		 State Total Record (RV) - Page 64
		 Final Record (RF)

		 Publication 42-007: http://www.ssa.gov/employer/EFW2&EFW2C.htm

		 Download: AccuWage from the bottom of this website for testing: http://www.socialsecurity.gov/employer/accuwage/index.html
		 */

		$records = $this->getRecords();

		//Debug::Arr($records, 'Output EFILE Records: ',__FILE__, __LINE__, __METHOD__, 10);

		if ( is_array( $records ) && count( $records ) > 0 ) {
			$retval = $this->padLine( $this->_compileRA() );
			$retval .= $this->padLine( $this->_compileRE() );

			$rt_total = Misc::preSetArrayValues( new stdClass(), [ 'total', 'l1', 'l2', 'l3', 'l4', 'l5', 'l6', 'l7', 'l10', 'l11', 'l12d', 'l12e', 'l12f', 'l12g', 'l12h', 'l12w', 'l12aa', 'l12bb', 'l12dd' ], 0 );
			$rw_total = Misc::preSetArrayValues( new stdClass(), [ 'total' ], 0 );
			$ro_total = Misc::preSetArrayValues( new stdClass(), [ 'total', 'l8', 'l12a', 'l12b', 'l12r', 'l12s', 'l12t', 'l12m', 'l12n', 'l12z', 'l12ee', 'l12gg', 'l12hh' ], 0 );

			$i = 0;
			foreach ( $records as $w2_data ) {
				$this->arrayToObject( $w2_data ); //Convert record array to object


				$compile_rw_retval = $this->padLine( $this->_compileRW() );
				if ( $compile_rw_retval != '' ) {
					$retval .= $compile_rw_retval;
					$rw_total->total += 1;
				}

				$retval .= $this->padLine( $this->_compileRO() );

				$state_total = Misc::preSetArrayValues( new stdClass(), [ 'total', 'state_taxable_wages', 'state_income_tax', 'local_taxable_wages', 'local_income_tax' ], 0 );
				foreach ( range( 'a', 'z' ) as $z ) {
					$compile_rs_retval = $this->padLine( $this->_compileRS( $z ) );

					if ( $compile_rs_retval != '' ) {
						$retval .= $compile_rs_retval;

						$state_total->total += 1;
						$state_total->state_taxable_wages += $this->{'l16' . $z};
						$state_total->state_income_tax += $this->{'l17' . $z};
						$state_total->local_taxable_wages += $this->{'l18' . $z};
						$state_total->local_income_tax += $this->{'l19' . $z};
					}
				}

				$rt_total->total += 1;
				$rt_total->l1 += $this->l1;
				$rt_total->l2 += $this->l2;
				$rt_total->l3 += $this->l3;
				$rt_total->l4 += $this->l4;
				$rt_total->l5 += $this->l5;
				$rt_total->l6 += $this->l6;
				$rt_total->l7 += $this->l7;
				$rt_total->l10 += $this->l10;
				$rt_total->l11 += $this->l11;
				$rt_total->l12d += $this->_getL12AmountByCode( 'D' );
				$rt_total->l12e += $this->_getL12AmountByCode( 'E' );
				$rt_total->l12f += $this->_getL12AmountByCode( 'F' );
				$rt_total->l12g += $this->_getL12AmountByCode( 'G' );
				$rt_total->l12h += $this->_getL12AmountByCode( 'H' );
				$rt_total->l12w += $this->_getL12AmountByCode( 'W' );
				$rt_total->l12aa += $this->_getL12AmountByCode( 'AA' );
				$rt_total->l12bb += $this->_getL12AmountByCode( 'BB' );
				$rt_total->l12dd += $this->_getL12AmountByCode( 'DD' );

				$ro_total->total += 1;
				$ro_total->l8 += $this->l8;
				$ro_total->l12a += $this->_getL12AmountByCode( 'A' );
				$ro_total->l12b += $this->_getL12AmountByCode( 'B' );
				$ro_total->l12r += $this->_getL12AmountByCode( 'R' );
				$ro_total->l12s += $this->_getL12AmountByCode( 'S' );
				$ro_total->l12t += $this->_getL12AmountByCode( 'T' );
				$ro_total->l12m += $this->_getL12AmountByCode( 'M' );
				$ro_total->l12n += $this->_getL12AmountByCode( 'N' );
				$ro_total->l12z += $this->_getL12AmountByCode( 'Z' );
				$ro_total->l12ee += $this->_getL12AmountByCode( 'EE' );
				$ro_total->l12gg += $this->_getL12AmountByCode( 'GG' );
				$ro_total->l12hh += $this->_getL12AmountByCode( 'HH' );

				$this->revertToOriginalDataState();

				$i++;
			}

			if ( in_array( $this->efile_state, [ 'CT', 'MA' ] ) ) {
				$retval .= $this->padLine( $this->_compileRT( $state_total ) ); //CT uses the RT record like an RV record, showing state totals.
			} else {
				$retval .= $this->padLine( $this->_compileRT( $rt_total ) );
			}
			$retval .= $this->padLine( $this->_compileRU( $ro_total ) );
			$retval .= $this->padLine( $this->_compileRV( $state_total ) ); //State Total Record

			if ( in_array( $this->efile_state, [ 'CT', 'MA', 'VA' ] ) ) {
				$retval .= $this->padLine( $this->_compileRF( $state_total ) );
			} else {
				$retval .= $this->padLine( $this->_compileRF( $rw_total ) );
			}
		}

		if ( isset( $retval ) ) {
			return $retval;
		}

		return false;
	}

	//Gets Line 14 amount by the name associated with that Line.
	function getL14AmountByName( $name ) {
		$name = trim( $name );

		$retval = 0;
		if ( $name != '' ) {
			foreach ( range( 'a', 'd' ) as $z ) {
				$l14_field = 'l14' . $z;
				$l14_name_field = 'l14' . $z . '_name';
				if ( strtolower( trim( $this->$l14_name_field ) ) == strtolower( $name ) ) {
					$retval = $this->$l14_field;
					break;
				}
			}
		}

		return $retval;
	}

	//This takes a single employee record and moves state/locality data from rows c, d, e, f, ... into rows a,b.
	// Because it changes the data, it can be run multiple times on the same input data.
	function handleThreeOrMoreStateData( $data ) {
		//FIXME: Add support for more than two states.
		return false;
	}

	//Return just array elements that *do not* match in each record. Always keep mandatory elements too though.
	function comparePreviousAndCorrectRecords( $previous_record, $correct_record ) {
		//Always keep these elements in the correct record.
		$mandatory_elements_to_keep = [ 'user_id', 'ssn', 'first_name', 'middle_name', 'last_name', 'address1', 'address2', 'city', 'province', 'postal_code' ];
		$previous_record_elements_to_remove = [ 'ssn', 'first_name', 'middle_name', 'last_name' ];

		foreach( $correct_record as $correct_record_key => $correct_record_value ) {
			if ( in_array( $correct_record_key, $mandatory_elements_to_keep ) ) {
				if ( in_array( $correct_record_key, $previous_record_elements_to_remove ) && isset( $previous_record[$correct_record_key] ) && $previous_record[$correct_record_key] == $correct_record_value ) {
					unset( $previous_record[$correct_record_key] );
				}
			} else {
				if ( isset( $previous_record[$correct_record_key] ) && $previous_record[$correct_record_key] == $correct_record_value ) {
					unset( $previous_record[$correct_record_key], $correct_record[$correct_record_key] );
				}
			}
		}

		return [ $previous_record, $correct_record ];
	}

	function convertW2ObjectToPrevious( $previous_record ) {
		if ( is_array( $previous_record ) && !empty( $previous_record ) ) {
			$retarr = [];
			foreach( $previous_record as $key => $value ) {
				$retarr['previous_'.$key] = $value;
			}

			return $retarr;
		}

		return [];
	}

	function mergeCorrectAndPreviousW2Objects( $correct_fw2_obj, $previous_fw2_obj ) {
		//Loop over previos records, and find matching correct records to merge in.

		$this->year = $correct_fw2_obj->year;
		$this->ein = $correct_fw2_obj->ein;
		$this->name = $correct_fw2_obj->name;
		$this->trade_name = $correct_fw2_obj->trade_name;
		$this->company_address1 = $correct_fw2_obj->company_address1;
		$this->company_address2 = $correct_fw2_obj->company_address2;
		$this->company_city = $correct_fw2_obj->company_city;
		$this->company_state = $correct_fw2_obj->company_state;
		$this->company_zip_code = $correct_fw2_obj->company_zip_code;


		//Get array of both sets of records.
		$previous_records = $previous_fw2_obj->getRecords();
		$correct_records = $correct_fw2_obj->getRecords();

		if ( is_array( $previous_records ) && !empty( $previous_records ) ) {
			foreach( $previous_records as $previous_record ) {
				//Find matching correct record.
				if ( is_array( $correct_records ) && !empty( $correct_records ) ) {
					foreach ( $correct_records as $correct_record_key => $correct_record ) {
						if ( isset($previous_record['user_id']) && isset( $correct_record['user_id'] ) && $previous_record['user_id'] == $correct_record['user_id'] ) {
							//Find only the differences between each record.
							[ $previous_record, $correct_record ] = $this->comparePreviousAndCorrectRecords( $previous_record, $correct_record );

							//Merge both records.
							$this->addRecord( array_merge( $this->convertW2ObjectToPrevious( $previous_record ), $correct_record ) );
							unset( $correct_records[$correct_record_key] ); //Remove correct record from list now thats it been used.

							break;
						}
					}
				}
			}

			return true;
		}

		return false;
	}

	//This takes a single employee record that has three or more states/localities and splits them into multiple records to simplify generating the PDFs.
	function handleMultipleForms( $records ) {
		$tmp_records = [];
		if ( is_array( $records ) && count( $records ) > 0 ) {
			foreach ( $records as $employee_data ) {
				$tmp_records[] = $employee_data;

				$tmp_record = $employee_data;
				do {
					$tmp_record = $this->handleThreeOrMoreStateData( $tmp_record );
					if ( is_array( $tmp_record ) ) {
						$tmp_records[] = $tmp_record;
					}
				} while ( is_array( $tmp_record ) );
			}
		}

		$this->clearRecords();
		$this->setRecords( $tmp_records );

		return $this->getRecords();
	}

	function _outputPDF( $type ) {
		//Initialize PDF with template.
		$pdf = $this->getPDFObject();

		if ( $this->getShowBackground() == true ) {
			$pdf->setSourceFile( $this->getTemplateDirectory() . DIRECTORY_SEPARATOR . $this->pdf_template );

			for ( $tp = 1; $tp <= 9; $tp++ ) {
				$this->template_index[$tp] = $pdf->ImportPage( $tp );
			}
		}

		if ( $this->year == '' ) {
			$this->year = $this->getYear();
		}

		if ( $this->getType() == 'government' ) {
			if ( $this->efile_state != '' ) { //When doing state filing, only include the state copy of the W2.
				$form_template_pages = [ 3 ]; //Template pages to use.
			} else {
				$form_template_pages = [ 2, 3, 8 ]; //Template pages to use. 2=SSA, 3=State, City, Local, 10=Employer
			}
		} else {
			$form_template_pages = [ 4, 5, 7 ]; //Template pages to use. 4=Employee Federal Return, 6=Employee Records, 7=Employee State, City Local
		}

		//Get location map, start looping over each variable and drawing
		$records = $this->handleMultipleForms( $this->getRecords() );

		if ( is_array( $records ) && count( $records ) > 0 ) {
			$template_schema = $this->getTemplateSchema();

			foreach ( $form_template_pages as $key => $form_template_page ) {
				//Set the template used.
				$template_schema[0]['template_page'] = $form_template_page;

				$e = 0;
				foreach ( $records as $employee_data ) {
					//Debug::Arr($employee_data, 'Employee Data: ', __FILE__, __LINE__, __METHOD__,10);
					$this->arrayToObject( $employee_data ); //Convert record array to object

					$this->setTempPageOffsets( $this->getPageOffsets( 'x' ), $this->getPageOffsets( 'y' ) );

					foreach ( $template_schema as $field => $schema ) {
						$this->Draw( $this->$field, $schema );
					}

					$this->resetTemplatePage();

					$this->revertToOriginalDataState();

					$e++;
				}
			}
		}

		return true;
	}
}

?>