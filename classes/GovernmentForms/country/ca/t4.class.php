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


include_once( 'CA.class.php' );

/**
 * @package GovernmentForms
 */
class GovernmentForms_CA_T4 extends GovernmentForms_CA {
	public $pdf_template = 't4flat-10b.pdf';

	public $template_offsets = [ -10, 0 ];

	private $payroll_deduction_obj = null; //Prevent __set() from sticking this into the data property.

	function getOptions( $name ) {
		$retval = null;
		switch ( $name ) {
			case 'status':
				$retval = [
						'-1010-O' => TTi18n::getText( 'Original' ),
						'-1020-A' => TTi18n::getText( 'Amended' ),
						'-1030-C' => TTi18n::getText( 'Cancel' ),
				];
				break;
			case 'type':
				$retval = [
						'government' => TTi18n::gettext( 'Government (Multiple Employees/Page)' ),
						'employee'   => TTi18n::gettext( 'Employee (One Employee/Page)' ),
				];
				break;
		}

		return $retval;
	}

	function getPayrollDeductionObject() {
		if ( !isset( $this->payroll_deduction_obj ) ) {
			require_once( Environment::getBasePath() . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'payroll_deduction' . DIRECTORY_SEPARATOR . 'PayrollDeduction.class.php' );
			$this->payroll_deduction_obj = new PayrollDeduction( 'CA', null );
			$this->payroll_deduction_obj->setDate( TTDate::getTimeStamp( $this->year, 12, 31 ) );
		}

		return $this->payroll_deduction_obj;
	}

	function getCPPMaximumEarnings() {
		return $this->getPayrollDeductionObject()->getCPPMaximumEarnings();
	}

	function getEIMaximumEarnings() {
		return $this->getPayrollDeductionObject()->getEIMaximumEarnings();
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

	//Set the submission status. Original, Amended, Cancel.
	function getStatus() {
		if ( isset( $this->status ) ) {
			return $this->status;
		}

		return 'O'; //Original
	}

	function setStatus( $value ) {
		$this->status = strtoupper( trim( $value ) );

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

				'year'                   => [
						'page'          => 1,
						'template_page' => 1,
						'on_background' => true,
						'function' => [ 'prefilter' => 'isNumeric' ],
						'coordinates'   => [
								'x'      => 349,
								'y'      => 37,
								'h'      => 17,
								'w'      => 57,
								'halign' => 'C',
								//'fill_color' => array( 255, 255, 255 ),
						],
						'font'          => [
								'size' => 14,
								'type' => 'B',
						],
				],

				//Company information
				'company_name'           => [
						'coordinates' => [
								'x'      => 35,
								'y'      => 52,
								'h'      => 12,
								'w'      => 210,
								'halign' => 'L',
						],
						'font'        => [
								'size' => 8,
								'type' => 'B',
						],
				],
				'employment_province'    => [ //Province of employment
											  'coordinates' => [
													  'x'      => 297,
													  'y'      => 109,
													  'h'      => 18,
													  'w'      => 28,
													  'halign' => 'C',
											  ],
				],
				'payroll_account_number' => [
						'function'    => [ 'draw' => [ 'filterPayrollAccountNumber', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 52,
								'y'      => 110,
								'h'      => 17,
								'w'      => 214,
								'halign' => 'L',
						],
						'font'        => [
								'size' => 8,
								'type' => '',
						],
				],

				//Employee information.
				'sin'                    => [
						'coordinates' => [
								'x'      => 52,
								'y'      => 145,
								'h'      => 17,
								'w'      => 120,
								'halign' => 'C',
						],
				],
				'cpp_exempt'             => [
						'function'    => [ 'precalc' => 'preCalcCPPExempt', 'draw' => [ 'drawCheckBox' ] ],
						'coordinates' => [
								[
										'x'      => 202,
										'y'      => 145,
										'h'      => 18,
										'w'      => 15,
										'halign' => 'C',
								],
						],
				],
				'ei_exempt'              => [
						'function'    => [ 'precalc' => 'preCalcEIExempt', 'draw' => [ 'drawCheckBox' ] ],
						'coordinates' => [
								[
										'x'      => 226,
										'y'      => 145,
										'h'      => 18,
										'w'      => 15,
										'halign' => 'C',
								],
						],
				],
				'ppip_exempt'            => [
						'function'    => [ 'precalc' => 'preCalcPPIPExempt', 'draw' => [ 'drawCheckBox' ] ],
						'coordinates' => [
								[
										'x'      => 252,
										'y'      => 145,
										'h'      => 18,
										'w'      => 15,
										'halign' => 'C',
								],
						],
				],
				'employment_code'        => [
						'coordinates' => [
								'x'      => 296,
								'y'      => 145,
								'h'      => 18,
								'w'      => 29,
								'halign' => 'C',
						],
				],
				'last_name'              => [
						'coordinates' => [
								'x'      => 49,
								'y'      => 197,
								'h'      => 14,
								'w'      => 170,
								'halign' => 'L',
						],
				],
				'first_name'             => [
						'coordinates' => [
								'x'      => 222,
								'y'      => 197,
								'h'      => 14,
								'w'      => 60,
								'halign' => 'L',
						],
				],
				'middle_name'            => [
						'function'    => [ 'draw' => [ 'filterMiddleName', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 290,
								'y'      => 197,
								'h'      => 14,
								'w'      => 30,
								'halign' => 'R',
						],
				],

				'address' => [
						'function'    => [ 'draw' => [ 'filterAddress', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 49,
								'y'      => 215,
								'h'      => 42,
								'w'      => 270,
								'halign' => 'L',
						],
						'font'        => [
								'size' => 8,
								'type' => '',
						],
						'multicell'   => true,
				],
				'l14'     => [
						'function'    => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
						'coordinates' => [
								[
										'x'      => 320,
										'y'      => 72.5,
										'h'      => 18,
										'w'      => 98,
										'halign' => 'R',
								],
								[
										'x'      => 418,
										'y'      => 72.5,
										'h'      => 18,
										'w'      => 33,
										'halign' => 'C',
								],
						],
				],
				'l16'     => [
						'function'    => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
						'coordinates' => [
								[
										'x'      => 348,
										'y'      => 109,
										'h'      => 18,
										'w'      => 70,
										'halign' => 'R',
								],
								[
										'x'      => 418,
										'y'      => 109,
										'h'      => 18,
										'w'      => 33,
										'halign' => 'C',
								],
						],
				],
				'l17'     => [
						'function'    => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
						'coordinates' => [
								[
										'x'      => 348,
										'y'      => 145,
										'h'      => 18,
										'w'      => 70,
										'halign' => 'R',
								],
								[
										'x'      => 418,
										'y'      => 145,
										'h'      => 18,
										'w'      => 33,
										'halign' => 'C',
								],
						],
				],
				'l18'     => [
						'function'    => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
						'coordinates' => [
								[
										'x'      => 348,
										'y'      => 180,
										'h'      => 18,
										'w'      => 70,
										'halign' => 'R',
								],
								[
										'x'      => 418,
										'y'      => 180,
										'h'      => 18,
										'w'      => 33,
										'halign' => 'C',
								],
						],
				],
				'l20'     => [
						'function'    => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
						'coordinates' => [
								[
										'x'      => 348,
										'y'      => 217,
										'h'      => 18,
										'w'      => 70,
										'halign' => 'R',
								],
								[
										'x'      => 418,
										'y'      => 217,
										'h'      => 18,
										'w'      => 33,
										'halign' => 'C',
								],
						],
				],
				'l52'     => [
						'function'    => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
						'coordinates' => [
								[
										'x'      => 348,
										'y'      => 253,
										'h'      => 18,
										'w'      => 70,
										'halign' => 'R',
								],
								[
										'x'      => 418,
										'y'      => 253,
										'h'      => 18,
										'w'      => 33,
										'halign' => 'C',
								],
						],
				],
				'l55'     => [
						'function'    => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
						'coordinates' => [
								[
										'x'      => 348,
										'y'      => 290,
										'h'      => 18,
										'w'      => 70,
										'halign' => 'R',
								],
								[
										'x'      => 418,
										'y'      => 290,
										'h'      => 18,
										'w'      => 33,
										'halign' => 'C',
								],
						],
				],

				'l22' => [
						'function'    => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
						'coordinates' => [
								[
										'x'      => 470,
										'y'      => 72.5,
										'h'      => 18,
										'w'      => 83,
										'halign' => 'R',
								],
								[
										'x'      => 553,
										'y'      => 72.5,
										'h'      => 18,
										'w'      => 32,
										'halign' => 'C',
								],
						],
				],
				'l24' => [
						'function'    => [ 'precalc' => 'preCalcL24', 'draw' => [ 'drawSplitDecimalFloat' ] ],
						'coordinates' => [
								[
										'x'      => 483,
										'y'      => 109,
										'h'      => 18,
										'w'      => 70,
										'halign' => 'R',
								],
								[
										'x'      => 553,
										'y'      => 109,
										'h'      => 18,
										'w'      => 32,
										'halign' => 'C',
								],
						],
				],
				'l26' => [
						'function'    => [ 'precalc' => 'preCalcL26', 'draw' => [ 'drawSplitDecimalFloat' ] ],
						'coordinates' => [
								[
										'x'      => 483,
										'y'      => 145,
										'h'      => 18,
										'w'      => 70,
										'halign' => 'R',
								],
								[
										'x'      => 553,
										'y'      => 145,
										'h'      => 18,
										'w'      => 32,
										'halign' => 'C',
								],
						],
				],
				'l44' => [
						'function'    => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
						'coordinates' => [
								[
										'x'      => 483,
										'y'      => 180,
										'h'      => 18,
										'w'      => 70,
										'halign' => 'R',
								],
								[
										'x'      => 553,
										'y'      => 180,
										'h'      => 18,
										'w'      => 32,
										'halign' => 'C',
								],
						],
				],
				'l46' => [
						'function'    => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
						'coordinates' => [
								[
										'x'      => 483,
										'y'      => 217,
										'h'      => 18,
										'w'      => 70,
										'halign' => 'R',
								],
								[
										'x'      => 553,
										'y'      => 217,
										'h'      => 18,
										'w'      => 32,
										'halign' => 'C',
								],
						],
				],
				'l50' => [
						'function'    => [ 'draw' => [ 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 483,
								'y'      => 253,
								'h'      => 18,
								'w'      => 103,
								'halign' => 'R',
						],
				],
				'l56' => [
						'function'    => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
						'coordinates' => [
								[
										'x'      => 483,
										'y'      => 290,
										'h'      => 18,
										'w'      => 70,
										'halign' => 'R',
								],
								[
										'x'      => 553,
										'y'      => 290,
										'h'      => 18,
										'w'      => 32,
										'halign' => 'C',
								],
						],
				],

				'other_box_0_code' => [
						'coordinates' => [
								'x'      => 106,
								'y'      => 325,
								'h'      => 16,
								'w'      => 27,
								'halign' => 'C',
						],
				],
				'other_box_0'      => [
						'function'    => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
						'coordinates' => [
								[
										'x'      => 142,
										'y'      => 325,
										'h'      => 16,
										'w'      => 84,
										'halign' => 'R',
								],
								[
										'x'      => 226,
										'y'      => 325,
										'h'      => 16,
										'w'      => 32,
										'halign' => 'C',
								],
						],
				],
				'other_box_1_code' => [
						'coordinates' => [
								'x'      => 268,
								'y'      => 325,
								'h'      => 16,
								'w'      => 27,
								'halign' => 'C',
						],
				],
				'other_box_1'      => [
						'function'    => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
						'coordinates' => [
								[
										'x'      => 304,
										'y'      => 325,
										'h'      => 16,
										'w'      => 84,
										'halign' => 'R',
								],
								[
										'x'      => 388,
										'y'      => 325,
										'h'      => 16,
										'w'      => 32,
										'halign' => 'C',
								],
						],
				],
				'other_box_2_code' => [
						'coordinates' => [
								'x'      => 430,
								'y'      => 325,
								'h'      => 16,
								'w'      => 27,
								'halign' => 'C',
						],
				],
				'other_box_2'      => [
						'function'    => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
						'coordinates' => [
								[
										'x'      => 466,
										'y'      => 325,
										'h'      => 16,
										'w'      => 84,
										'halign' => 'R',
								],
								[
										'x'      => 550,
										'y'      => 325,
										'h'      => 16,
										'w'      => 32,
										'halign' => 'C',
								],
						],
				],
				'other_box_3_code' => [
						'coordinates' => [
								'x'      => 106,
								'y'      => 357,
								'h'      => 16,
								'w'      => 27,
								'halign' => 'C',
						],
				],
				'other_box_3'      => [
						'function'    => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
						'coordinates' => [
								[
										'x'      => 142,
										'y'      => 357,
										'h'      => 16,
										'w'      => 84,
										'halign' => 'R',
								],
								[
										'x'      => 226,
										'y'      => 357,
										'h'      => 16,
										'w'      => 30,
										'halign' => 'C',
								],
						],
				],
				'other_box_4_code' => [
						'coordinates' => [
								'x'      => 268,
								'y'      => 357,
								'h'      => 16,
								'w'      => 27,
								'halign' => 'C',
						],
				],
				'other_box_4'      => [
						'function'    => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
						'coordinates' => [
								[
										'x'      => 304,
										'y'      => 357,
										'h'      => 16,
										'w'      => 84,
										'halign' => 'R',
								],
								[
										'x'      => 388,
										'y'      => 357,
										'h'      => 16,
										'w'      => 32,
										'halign' => 'C',
								],
						],
				],
				'other_box_5_code' => [
						'coordinates' => [
								'x'      => 430,
								'y'      => 357,
								'h'      => 16,
								'w'      => 27,
								'halign' => 'C',
						],
				],
				'other_box_5'      => [
						'function'    => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
						'coordinates' => [
								[
										'x'      => 466,
										'y'      => 357,
										'h'      => 16,
										'w'      => 84,
										'halign' => 'R',
								],
								[
										'x'      => 550,
										'y'      => 357,
										'h'      => 16,
										'w'      => 32,
										'halign' => 'C',
								],
						],
				],
		];

		if ( isset( $template_schema[$name] ) ) {
			return $name;
		} else {
			return $template_schema;
		}
	}

	function preCalcL24( $value, $key, &$array ) {
		Debug::Text( 'EI Earning: ' . $value . ' Maximum: ' . $this->getEIMaximumEarnings(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $value > $this->getEIMaximumEarnings() ) {
			return $this->getEIMaximumEarnings();
		}

		return $value;
	}

	function preCalcL26( $value, $key, &$array ) {
		if ( $value > $this->getCPPMaximumEarnings() ) {
			$value = $this->getCPPMaximumEarnings();
		}

		return $value;
	}

	function preCalcEIExempt( $value, $key, &$array ) {
		if ( $value == true ) {
			$array['l24'] = 0;
		}

		return $value;
	}

	function preCalcCPPExempt( $value, $key, &$array ) {
		if ( $value == true ) {
			$array['l26'] = 0;
		}

		return $value;
	}

	function preCalcPPIPExempt( $value, $key, &$array ) {
		if ( $value == true ) {
			$array['l56'] = 0;
		}

		return $value;
	}

	function _outputXML( $type = null ) {
		//Maps other income box codes to XML element names.
		$other_box_code_map = [
				30 => 'hm_brd_lodg_amt',
				31 => 'spcl_wrk_site_amt',
				32 => 'prscb_zn_trvl_amt',
				33 => 'med_trvl_amt',
				34 => 'prsnl_vhcl_amt',
				35 => 'rsn_per_km_amt',
				36 => 'low_int_loan_amt',
				37 => 'empe_hm_loan_amt',
				38 => 'sob_a00_feb_amt',
				39 => 'sod_d_a00_feb',
				40 => 'oth_tx_ben_amt',
				41 => 'sod_d1_a00_feb_amt',
				42 => 'empt_cmsn_amt',
				43 => 'cfppa_amt',
				53 => 'dfr_sob_amt',
				57 => 'empt_inc_amt_covid_prd1',
				58 => 'empt_inc_amt_covid_prd2',
				59 => 'empt_inc_amt_covid_prd3',
				60 => 'empt_inc_amt_covid_prd4',
				66 => 'elg_rtir_amt',
				67 => 'nelg_rtir_amt',
				68 => 'indn_elg_rtir_amt',
				69 => 'indn_nelg_rtir_amt',
				70 => 'mun_ofcr_examt',
				71 => 'indn_empe_amt',
				72 => 'oc_incamt',
				73 => 'oc_dy_cnt',
				74 => 'pr_90_cntrbr_amt',
				75 => 'pr_90_ncntrbr_amt',
				77 => 'cmpn_rpay_empr_amt',
				78 => 'fish_gro_ern_amt',
				79 => 'fish_net_ptnr_amt',
				80 => 'fish_shr_prsn_amt',
				81 => 'plcmt_emp_agcy_amt',
				82 => 'drvr_taxis_oth_amt',
				83 => 'brbr_hrdrssr_amt',
				84 => 'pub_trnst_pass',
				85 => 'epaid_hlth_pln_amt',
				86 => 'stok_opt_csh_out_eamt',
				87 => 'vlntr_emergencyworker_xmpt_amt',
				88 => 'indn_txmpt_sei_amt',
		];

		if ( is_object( $this->getXMLObject() ) ) {
			$xml = $this->getXMLObject();
		} else {
			return false; //No XML object to append too. Needs T619 form first.
		}

		$xml->Return->addChild( 'T4' );

		$records = $this->handleMultipleForms( $this->getRecords() ); //Just like the paper form, only 6 other boxes are allowed per T4 record. If there is more, it must be split up onto other T4 records.
		if ( is_array( $records ) && count( $records ) > 0 ) {
			$e = 0;
			foreach ( $records as $employee_data ) {
				//Debug::Arr($employee_data, 'Employee Data: ', __FILE__, __LINE__, __METHOD__,10);
				$this->arrayToObject( $employee_data ); //Convert record array to object

				$xml->Return->T4->addChild( 'T4Slip' );

				$xml->Return->T4->T4Slip[$e]->addChild( 'EMPE_NM' );                                             //Employee name
				$xml->Return->T4->T4Slip[$e]->EMPE_NM->addChild( 'snm', substr( $this->last_name, 0, 20 ) );     //Surname
				$xml->Return->T4->T4Slip[$e]->EMPE_NM->addChild( 'gvn_nm', substr( $this->first_name, 0, 12 ) ); //Given name
				if ( $this->filterMiddleName( $this->middle_name ) != '' ) {
					$xml->Return->T4->T4Slip[$e]->EMPE_NM->addChild( 'init', $this->filterMiddleName( $this->middle_name ) );
				}

				$xml->Return->T4->T4Slip[$e]->addChild( 'EMPE_ADDR' ); //Employee Address
				if ( $this->address1 != '' ) {
					$xml->Return->T4->T4Slip[$e]->EMPE_ADDR->addChild( 'addr_l1_txt', substr( Misc::stripHTMLSpecialChars( $this->address1 ), 0, 30 ) );
				}
				if ( $this->address2 != '' ) {
					$xml->Return->T4->T4Slip[$e]->EMPE_ADDR->addChild( 'addr_l2_txt', substr( Misc::stripHTMLSpecialChars( $this->address2 ), 0, 30 ) );
				}
				if ( $this->city != '' ) {
					$xml->Return->T4->T4Slip[$e]->EMPE_ADDR->addChild( 'cty_nm', $this->city );
				}
				if ( $this->province != '' ) {
					$xml->Return->T4->T4Slip[$e]->EMPE_ADDR->addChild( 'prov_cd', $this->province );
				}
				$xml->Return->T4->T4Slip[$e]->EMPE_ADDR->addChild( 'cntry_cd', $this->formatAlpha3CountryCode( $this->country_code ) );
				if ( $this->postal_code != '' ) {
					$xml->Return->T4->T4Slip[$e]->EMPE_ADDR->addChild( 'pstl_cd', $this->postal_code );
				}

				$xml->Return->T4->T4Slip[$e]->addChild( 'sin', ( $this->sin != '' ) ? $this->sin : '000000000' ); //Required
				if ( $this->employee_number != '' ) {
					$xml->Return->T4->T4Slip[$e]->addChild( 'empe_nbr', substr( $this->employee_number, 0, 20 ) );
				}
				$xml->Return->T4->T4Slip[$e]->addChild( 'bn', $this->formatPayrollAccountNumber( $this->payroll_account_number ) ); //Payroll Account Number. Remove any spaces from the number.
				if ( isset( $this->l50 ) && $this->l50 != '' ) {
					$xml->Return->T4->T4Slip[$e]->addChild( 'rpp_dpsp_rgst_nbr', substr( $this->l50, 0, 7 ) );
				}

				$xml->Return->T4->T4Slip[$e]->addChild( 'cpp_qpp_xmpt_cd', (int)$this->cpp_exempt ); //CPP Exempt
				$xml->Return->T4->T4Slip[$e]->addChild( 'ei_xmpt_cd', (int)$this->ei_exempt );       //EI Exempt
				//$xml->Return->T4->T4Slip[$e]->addChild('rpt_tcd', 'O' ); //Report Type Code: O = Originals, A = Amendment, C = Cancel
				$xml->Return->T4->T4Slip[$e]->addChild( 'rpt_tcd', $this->getStatus() );             //Report Type Code: O = Originals, A = Amendment, C = Cancel
				$xml->Return->T4->T4Slip[$e]->addChild( 'empt_prov_cd', $this->employment_province );
				//$xml->Return->T4->T4Slip[$e]->addChild('rpp_dpsp_rgst_nbr', $this->l50 ); //Box 50: RPP Registration number
				//$xml->Return->T4->T4Slip[$e]->addChild('prov_ppip_xmpt_cd', '' ); //PPIP Exempt
				//$xml->Return->T4->T4Slip[$e]->addChild('empt_cd', '' ); //Box 29: Employment Code

				$xml->Return->T4->T4Slip[$e]->addChild( 'T4_AMT' ); //T4 Amounts

				if ( isset( $this->l14 ) && is_numeric( $this->l14 ) ) {
					$xml->Return->T4->T4Slip[$e]->T4_AMT->addChild( 'empt_incamt', $this->MoneyFormat( (float)$this->l14 ) );
				}
				if ( isset( $this->l16 ) && is_numeric( $this->l16 ) ) {
					$xml->Return->T4->T4Slip[$e]->T4_AMT->addChild( 'cpp_cntrb_amt', $this->MoneyFormat( (float)$this->l16 ) );
				}
				//$xml->Return->T4->T4Slip[$e]->T4_AMT->addChild('qpp_cntrb_amt', $this->MoneyFormat( $this->l17, FALSE ) );
				if ( isset( $this->l18 ) && is_numeric( $this->l18 ) ) {
					$xml->Return->T4->T4Slip[$e]->T4_AMT->addChild( 'empe_eip_amt', $this->MoneyFormat( (float)$this->l18 ) );
				}
				if ( isset( $this->l20 ) && is_numeric( $this->l20 ) ) {
					$xml->Return->T4->T4Slip[$e]->T4_AMT->addChild( 'rpp_cntrb_amt', $this->MoneyFormat( (float)$this->l20 ) );
				}
				if ( isset( $this->l22 ) && is_numeric( $this->l22 ) ) {
					$xml->Return->T4->T4Slip[$e]->T4_AMT->addChild( 'itx_ddct_amt', $this->MoneyFormat( (float)$this->l22 ) );
				}

				if ( $this->ei_exempt == false && isset( $this->l24 ) && is_numeric( $this->l24 ) ) {
					$xml->Return->T4->T4Slip[$e]->T4_AMT->addChild( 'ei_insu_ern_amt', $this->MoneyFormat( (float)$this->l24 ) );
				}
				if ( $this->cpp_exempt == false && isset( $this->l26 ) && is_numeric( $this->l26 ) ) {
					$xml->Return->T4->T4Slip[$e]->T4_AMT->addChild( 'cpp_qpp_ern_amt', $this->MoneyFormat( (float)$this->l26 ) );
				}
				if ( isset( $this->l44 ) && is_numeric( $this->l44 ) ) {
					$xml->Return->T4->T4Slip[$e]->T4_AMT->addChild( 'unn_dues_amt', $this->MoneyFormat( (float)$this->l44 ) );
				}
				if ( isset( $this->l46 ) && is_numeric( $this->l46 ) ) {
					$xml->Return->T4->T4Slip[$e]->T4_AMT->addChild( 'chrty_dons_amt', $this->MoneyFormat( (float)$this->l46 ) );
				}
				if ( isset( $this->l52 ) && is_numeric( $this->l52 ) ) {
					$xml->Return->T4->T4Slip[$e]->T4_AMT->addChild( 'padj_amt', $this->MoneyFormat( (float)$this->l52 ) );
				}
				if ( isset( $this->l55 ) && is_numeric( $this->l55 ) ) {
					$xml->Return->T4->T4Slip[$e]->T4_AMT->addChild( 'prov_pip_amt', $this->MoneyFormat( (float)$this->l55 ) );
				}
				if ( isset( $this->l56 ) && is_numeric( $this->l56 ) ) {
					$xml->Return->T4->T4Slip[$e]->T4_AMT->addChild( 'prov_insu_ern_amt', $this->MoneyFormat( (float)$this->l56 ) );
				}

				$xml->Return->T4->T4Slip[$e]->addChild( 'OTH_INFO' ); //Other Income Fields
				for ( $i = 0; $i <= 5; $i++ ) { //Just like the paper form, only 6 other boxes are allowed per T4 record. If there is more, it must be split up onto other T4 records.
					if ( isset( $this->{'other_box_' . $i . '_code'} ) ) {
						if ( isset( $other_box_code_map[$this->{'other_box_' . $i . '_code'}] ) ) {
							$xml->Return->T4->T4Slip[$e]->OTH_INFO->addChild( $other_box_code_map[$this->{'other_box_' . $i . '_code'}], $this->MoneyFormat( (float)$this->{'other_box_' . $i} ) );
						} else {
							Debug::Text( 'ERROR: Other Box Code is invalid and not mapped in the XSD! Code: '. $this->{'other_box_' . $i . '_code'}, __FILE__, __LINE__, __METHOD__, 10 );
						}
					}
				}

				$this->revertToOriginalDataState();

				$e++;
			}
		}

		return true;
	}

	//This takes a single employee record and moves other box data from fields 6, 7, 8, 9, ... into fields 0, 1.
	// Because it changes the data, it can be run multiple times on the same input data.
	function handleSevenOrMoreOtherBoxData( $data ) {
		//Clear all variables that should be empty when generating multiple T4 forms. (everything except other boxes)
		$data['l14'] = null;
		$data['l22'] = null;
		$data['l16'] = null;
		$data['l17'] = null;
		$data['l24'] = null;
		$data['l26'] = null;
		$data['l18'] = null;
		$data['l44'] = null;
		$data['l20'] = null;
		$data['l46'] = null;
		$data['l52'] = null;
		$data['l50'] = null;

		$data['l19'] = null;
		$data['l27'] = null;

		//Clear all variables for Other Boxes 0-5 (Boxes 1-6) which are displayed on the first page of the form.
		for ( $n = 0; $n <= 5; $n++ ) {
			$data['other_box_' . $n .'_code'] = null;
			$data['other_box_' . $n] = null;
		}

		$data_changed = false;

		//Copy non-NULL data from rows 6+ to rows 0-5.
		$destination_position = 0;
		for ( $n = 6; $n <= 23; $n++ ) { //Skip 0-5 range, and start on 7, as we always copy data in to the 0-6 range.
			if ( !( ( !isset( $data['other_box_' . $n .'_code'] ) || $data['other_box_' . $n .'_code'] == null ) && ( !isset( $data['other_box_' . $n] ) || $data['other_box_' . $n] == null ) ) ) {
				Debug::Text( 'Found 6+ Other Box, moving to position: ' . $destination_position, __FILE__, __LINE__, __METHOD__, 10 );

				$data_changed = true;

				$data['other_box_' . $destination_position .'_code'] = $data['other_box_' . $n .'_code'];
				$data['other_box_' . $destination_position] = $data['other_box_' . $n];

				$data['other_box_' . $n .'_code'] = null;
				$data['other_box_' . $n] = null;

				$destination_position++;
				if ( $destination_position == 6 ) {
					break;
				}
			}
		}

		if ( $data_changed == true ) {
			return $data;
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
					$tmp_record = $this->handleSevenOrMoreOtherBoxData( $tmp_record );
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

			$this->template_index[1] = $pdf->ImportPage( 1 );
			$this->template_index[2] = $pdf->ImportPage( 2 );
			//$this->template_index[3] = $pdf->ImportPage(3);
		}

		if ( $this->year == '' ) {
			$this->year = $this->getYear();
		}

		if ( $this->getType() == 'government' ) {
			$employees_per_page = 2;
			$n = 1; //Don't loop the same employee.
		} else {
			$employees_per_page = 1;
			$n = 2; //Loop the same employee twice.
		}

		//Get location map, start looping over each variable and drawing
		$records = $this->handleMultipleForms( $this->getRecords() );

		if ( is_array( $records ) && count( $records ) > 0 ) {

			$template_schema = $this->getTemplateSchema();

			$e = 0;
			foreach ( $records as $employee_data ) {
				//Debug::Arr($employee_data, 'Employee Data: ', __FILE__, __LINE__, __METHOD__,10);
				$this->arrayToObject( $employee_data ); //Convert record array to object

				$template_page = null;

				for ( $i = 0; $i < $n; $i++ ) {
					$this->setTempPageOffsets( $this->getPageOffsets( 'x' ), $this->getPageOffsets( 'y' ) );

					if ( ( $employees_per_page == 1 && $i > 0 )
							|| ( $employees_per_page == 2 && $e % 2 != 0 )
					) {
						$this->setTempPageOffsets( $this->getPageOffsets( 'x' ), ( 394 + $this->getPageOffsets( 'y' ) ) );
					}

					foreach ( $template_schema as $field => $schema ) {
						$this->Draw( $this->$field, $schema );
					}
				}

				if ( $employees_per_page == 1 || ( $employees_per_page == 2 && $e % $employees_per_page != 0 ) ) {
					$this->resetTemplatePage();
					if ( $this->getShowInstructionPage() == true ) {
						$this->addPage( [ 'template_page' => 2 ] );
					}
				}

				$this->revertToOriginalDataState();

				$e++;
			}
		}

		return true;
	}
}

?>