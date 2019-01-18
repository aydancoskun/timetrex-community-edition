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
class GovernmentForms_US_940SA extends GovernmentForms_US {
	public $pdf_template = '940sa.pdf';

	public $credit_reduction_rates = array( 'CA' => 0.021, 'VI' => 0.021 ); //Tax Year: 2017

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
					'value'         => $this->year,
					'on_background' => TRUE,
					'coordinates'   => array(
							'x'          => 233.5,
							'y'          => 30,
							'h'          => 28,
							'w'          => 40,
							'halign'     => 'C',
							'fill_color' => array(255, 255, 255),
					),
					'font'          => array(
							'size' => 16,
							'type' => 'B',
					),
			),
			array(
					'value'         => $this->year, //Page Footer
					'on_background' => TRUE,
					'coordinates'   => array(
							'x'          => 559,
							'y'          => 746,
							'h'          => 11,
							'w'          => 18,
							'halign'     => 'C',
							'fill_color' => array(255, 255, 255),
					),
					'font'          => array(
							'size' => 7,
					),
			),
			//Finish initializing page 1.

			'ein' => array(
					'page'          => 1,
					'template_page' => 1,
					'function'      => 'drawChars', //custom drawing function.
					'coordinates'   => array(
							array(
									'type'   => 'static', //static or relative
									'x'      => 181,
									'y'      => 97,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							),
							array(
									'x'      => 206,
									'y'      => 97,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							),
							array(
									'x'      => 244,
									'y'      => 97,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							),
							array(
									'x'      => 269,
									'y'      => 97,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							),
							array(
									'x'      => 294,
									'y'      => 97,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							),
							array(
									'x'      => 320,
									'y'      => 97,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							),
							array(
									'x'      => 344,
									'y'      => 97,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							),
							array(
									'x'      => 369,
									'y'      => 97,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							),
							array(
									'x'      => 395,
									'y'      => 97,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							),
					),
					'font'          => array(
							'size' => 12,
							'type' => 'B',
					),
			),

			'name'    => array(
					'coordinates' => array(
							'x'      => 136,
							'y'      => 119,
							'h'      => 18,
							'w'      => 246,
							'halign' => 'L',
					),
			),

			'state_amounts' => array(
					'function'    => array( 'calcStateCheckBoxes', 'drawSplitDecimalFloatGrid' ), //Calculate the state checkboxes as early as possible, otherwise the below state_checkboxes variable won't exist and it can't be calculated at that point.
					'coordinates' => array(
						//Column 1
						'CA'  => array(
								array(
										'x'      => 89,
										'y'      => 301,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								),
								array(
										'x'      => 157,
										'y'      => 301,
										'h'      => 14,
										'w'      => 15,
										'halign' => 'C',
								),
						),
						//Column 2
						'VI'  => array(
								array(
										'x'      => 361,
										'y'      => 679,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								),
								array(
										'x'      => 430,
										'y'      => 679,
										'h'      => 14,
										'w'      => 15,
										'halign' => 'C',
								),
						),
					),
			),

			'state_credit_reduction' => array(
					'function'    => array('calcStateCreditReduction', 'drawSplitDecimalFloatGrid'),
					'coordinates' => array(
						//Column 1
						'CA'  => array(
								array(
										'x'      => 219,  //189
										'y'      => 301,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								),
								array(
										'x'      => 287,
										'y'      => 301,
										'h'      => 14,
										'w'      => 15,
										'halign' => 'C',
								),
						),
						//Column 2
						'VI'  => array(
								array(
										'x'      => 491,
										'y'      => 679,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								),
								array(
										'x'      => 560,
										'y'      => 679,
										'h'      => 14,
										'w'      => 15,
										'halign' => 'C',
								),
						),
					),
			),

			'state_checkboxes' => array(
					'function'    => 'drawCheckBox',
					'coordinates' => array(
							//State codes must be lower case.

							//Column 1
							'ak' => array(
									'x'      => 40,
									'y'      => 230,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'al' => array(
									'x'      => 40,
									'y'      => 249,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),


							'ar' => array(
									'x'      => 40,
									'y'      => 267,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'az' => array(
									'x'      => 40,
									'y'      => 285,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'ca' => array(
									'x'      => 40,
									'y'      => 303,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'co' => array(
									'x'      => 40,
									'y'      => 321,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'ct' => array(
									'x'      => 40,
									'y'      => 339,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'dc' => array(
									'x'      => 40,
									'y'      => 357,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'de' => array(
									'x'      => 40,
									'y'      => 375,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'fl' => array(
									'x'      => 40,
									'y'      => 393,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'ga' => array(
									'x'      => 40,
									'y'      => 411,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'hi' => array(
									'x'      => 40,
									'y'      => 429,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'ia' => array(
									'x'      => 40,
									'y'      => 447,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'id' => array(
									'x'      => 40,
									'y'      => 465,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'il' => array(
									'x'      => 40,
									'y'      => 483,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'in' => array(
									'x'      => 40,
									'y'      => 501,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'ks' => array(
									'x'      => 40,
									'y'      => 519,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'ky' => array(
									'x'      => 40,
									'y'      => 537,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'la' => array(
									'x'      => 40,
									'y'      => 555,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'ma' => array(
									'x'      => 40,
									'y'      => 573,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'md' => array(
									'x'      => 40,
									'y'      => 591,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'me' => array(
									'x'      => 40,
									'y'      => 609,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'mi' => array(
									'x'      => 40,
									'y'      => 627,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'mn' => array(
									'x'      => 40,
									'y'      => 645,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'mo' => array(
									'x'      => 40,
									'y'      => 663,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'ms' => array(
									'x'      => 40,
									'y'      => 681,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'mt' => array(
									'x'      => 40,
									'y'      => 699,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),

							//Column 2
							'nc' => array(
									'x'      => 306,
									'y'      => 230,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'nd' => array(
									'x'      => 306,
									'y'      => 249,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'ne' => array(
									'x'      => 306,
									'y'      => 267,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'nh' => array(
									'x'      => 306,
									'y'      => 285,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'nj' => array(
									'x'      => 306,
									'y'      => 303,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'nm' => array(
									'x'      => 306,
									'y'      => 321,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'nv' => array(
									'x'      => 306,
									'y'      => 339,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'ny' => array(
									'x'      => 306,
									'y'      => 357,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'oh' => array(
									'x'      => 306,
									'y'      => 375,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'ok' => array(
									'x'      => 306,
									'y'      => 393,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'or' => array(
									'x'      => 306,
									'y'      => 411,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'pa' => array(
									'x'      => 306,
									'y'      => 429,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'ri' => array(
									'x'      => 306,
									'y'      => 447,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'sc' => array(
									'x'      => 306,
									'y'      => 465,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'sd' => array(
									'x'      => 306,
									'y'      => 483,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'tn' => array(
									'x'      => 306,
									'y'      => 501,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'tx' => array(
									'x'      => 306,
									'y'      => 519,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'ut' => array(
									'x'      => 306,
									'y'      => 537,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'va' => array(
									'x'      => 306,
									'y'      => 555,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'vt' => array(
									'x'      => 306,
									'y'      => 573,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'wa' => array(
									'x'      => 306,
									'y'      => 591,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'wi' => array(
									'x'      => 306,
									'y'      => 609,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'wv' => array(
									'x'      => 306,
									'y'      => 627,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'wy' => array(
									'x'      => 306,
									'y'      => 645,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'pr' => array(
									'x'      => 306,
									'y'      => 663,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
							'vi' => array(
									'x'      => 306,
									'y'      => 681,
									'h'      => 12,
									'w'      => 13,
									'halign' => 'C',
							),
					),
					'font'        => array(
							'size' => 10,
							'type' => 'B',
					),
			),

			'total'        => array(
					'function'    => array('calcTotal', 'drawSplitDecimalFloat'),
					'coordinates' => array(
							array(
									'x'      => 455,
									'y'      => 722,
									'h'      => 14,
									'w'      => 95,
									'halign' => 'R',
							),
							array(
									'x'      => 553,
									'y'      => 722,
									'h'      => 14,
									'w'      => 20,
									'halign' => 'C',
							),
					),
			),

		);

		if ( isset( $template_schema[ $name ] ) ) {
			return $name;
		} else {
			return $template_schema;
		}
	}

	function calcStateCheckBoxes() {
		if ( is_array( $this->state_amounts ) ) {
			foreach( $this->state_amounts as $state => $amount ) {
				if ( $amount > 0 ) {
					$state_checkboxes[] = strtolower( $state ); //strtolower is required for handling checkboxes.
				}
			}

			$this->state_checkboxes = $state_checkboxes;
		}

		return $this->state_amounts; //Must return $state_amounts, *not* the state_checkboxes, otherwise FUTA taxable wages won't be printed.
	}

	function calcStateCreditReduction() {
		$state_credit_reduction = array();

		if ( isset($this->state_amounts) AND is_array($this->state_amounts) ) {
			foreach ( $this->state_amounts as $state => $amount ) {
				if ( isset( $this->credit_reduction_rates[ $state ] ) ) {
					$state_credit_reduction[ $state ] = bcmul( $amount, $this->credit_reduction_rates[ $state ] );
				}
			}

			$this->state_credit_reduction = $state_credit_reduction;
		}

		return $this->state_credit_reduction;
	}

	function calcTotal() {
		$this->calcStateCreditReduction(); //Make sure state credit reduction is calculated first.
		$this->total = 0;

		if ( isset($this->state_credit_reduction) AND is_array($this->state_credit_reduction) ) {
			foreach ( $this->state_credit_reduction as $state => $amount ) {
				$this->total = bcadd( $this->total, $amount );
			}
		}

		return $this->total;
	}

	function _outputPDF() {
		//Initialize PDF with template.
		$pdf = $this->getPDFObject();

		if ( $this->getShowBackground() == TRUE ) {
			$pdf->setSourceFile( $this->getTemplateDirectory() . DIRECTORY_SEPARATOR . $this->pdf_template );

			$this->template_index[1] = $pdf->ImportPage( 1 );
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
}

?>