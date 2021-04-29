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

/**
 * @package GovernmentForms
 */
class GovernmentForms_US_W3C extends GovernmentForms_US {
	public $pdf_template = 'w3c.pdf';

	public $page_margins = [ 0, 10 ]; //x, y - 43pt = 15mm Absolute margins that affect all drawing and templates.

	public function getTemplateSchema( $name = null ) {
		$template_schema = [
				[
						'page'          => 1,
						'template_page' => 2,
						'value'              => $this->year,
						'on_background'      => true,
						'coordinates'        => [
								'x'          => 120,
								'y'          => 92,
								'h'          => 6,
								'w'          => 40,
								'halign'     => 'R',
								'fill_color' => [ 255, 255, 255 ],
						],
						'font'               => [
								'size' => 10,
								'type' => '',
						],
				],

				[
						'value'              => '2', //W-2
						'on_background'      => true,
						'coordinates'        => [
								'x'          => 182,
								'y'          => 92,
								'h'          => 6,
								'w'          => 40,
								'halign'     => 'L',
								'fill_color' => [ 255, 255, 255 ],
						],
						'font'               => [
								'size' => 10,
								'type' => '',
						],
				],


				'trade_name'      => [
						'coordinates' => [
								'x'      => 38,
								'y'      => 120,
								'h'      => 15,
								'w'      => 220,
								'halign' => 'L',
						],
				],
				'company_address' => [
						'function'    => [ 'draw' => [ 'filterCompanyAddress', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 38,
								'y'      => 135,
								'h'      => 40,
								'w'      => 220,
								'halign' => 'L',
						],
						'font'        => [
								'size' => 8,
								'type' => '',
						],
						'multicell'   => true,
				],

				'kind_of_payer'        => [
						'function'    => [ 'draw' => 'drawCheckBox' ],
						'coordinates' => [
								'941'      => [
										'x'      => 272.5,
										'y'      => 127.5,
										'h'      => 10,
										'w'      => 11,
										'halign' => 'C',
								],
								'military' => [
										'x'      => 305.5,
										'y'      => 127.5,
										'h'      => 10,
										'w'      => 11,
										'halign' => 'C',
								],
								'943'      => [
										'x'      => 342.5,
										'y'      => 127.5,
										'h'      => 10,
										'w'      => 11,
										'halign' => 'C',
								],
								'944'      => [
										'x'      => 377.5,
										'y'      => 127.5,
										'h'      => 10,
										'w'      => 11,
										'halign' => 'C',
								],

								'ct-1'     => [
										'x'      => 272.5,
										'y'      => 162.5,
										'h'      => 10,
										'w'      => 11,
										'halign' => 'C',
								],
								'hshld'    => [
										'x'      => 305.5,
										'y'      => 162.5,
										'h'      => 10,
										'w'      => 11,
										'halign' => 'C',
								],
								'medicare' => [
										'x'      => 342.5,
										'y'      => 162.5,
										'h'      => 10,
										'w'      => 11,
										'halign' => 'C',
								],
						],
						'font'        => [
								'size' => 10,
								'type' => 'B',
						],
				],
				'kind_of_employer'     => [
						'function'    => [ 'draw' => [ 'strtolower', 'drawCheckBox' ] ],
						'coordinates' => [
								'n' => [
										'x'      => 420,
										'y'      => 127.5,
										'h'      => 10,
										'w'      => 11,
										'halign' => 'C',
								],
								't' => [
										'x'      => 461,
										'y'      => 127.5,
										'h'      => 10,
										'w'      => 11,
										'halign' => 'C',
								],
								's' => [
										'x'      => 420,
										'y'      => 162.5,
										'h'      => 10,
										'w'      => 11,
										'halign' => 'C',
								],
								'y' => [
										'x'      => 460,
										'y'      => 162.5,
										'h'      => 10,
										'w'      => 11,
										'halign' => 'C',
								],
								'f' => [
										'x'      => 497,
										'y'      => 162.5,
										'h'      => 10,
										'w'      => 11,
										'halign' => 'C',
								],
						],
						'font'        => [
								'size' => 10,
								'type' => 'B',
						],
				],
				'third_party_sick_pay' => [
						'function'    => [ 'draw' => 'drawCheckBox' ],
						'coordinates' => [
								[
										'x'      => 532,
										'y'      => 127.5,
										'h'      => 10,
										'w'      => 11,
										'halign' => 'C',
								],
						],
						'font'        => [
								'size' => 10,
								'type' => 'B',
						],
				],

				'lc'              => [ //Total W2 forms
									   'coordinates' => [
											   'x'      => 38,
											   'y'      => 186,
											   'h'      => 15,
											   'w'      => 110,
											   'halign' => 'C',
									   ],
				],
				'ein'             => [
						'function'    => [ 'prefilter' => [ 'stripNonNumeric', 'isNumeric' ], 'draw' => [ 'formatEIN', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 175,
								'y'      => 186,
								'h'      => 15,
								'w'      => 100,
								'halign' => 'L',
						],
				],
				'ld'              => [ //Establishment Number
									   'coordinates' => [
											   'x'      => 310,
											   'y'      => 186,
											   'h'      => 15,
											   'w'      => 110,
											   'halign' => 'C',
									   ],
				],
				'state_id1'       => [
						'coordinates' => [
								'x'      => 440,
								'y'      => 186,
								'h'      => 15,
								'w'      => 100,
								'halign' => 'L',
						],
				],



				'contact_name'  => [
						'coordinates' => [
								'x'      => 38,
								'y'      => 548,
								'h'      => 15,
								'w'      => 220,
								'halign' => 'L',
						],
				],
				'contact_phone' => [
						'coordinates' => [
								'x'      => 290,
								'y'      => 548,
								'h'      => 15,
								'w'      => 150,
								'halign' => 'L',
						],
				],
				'contact_fax' => [
						'coordinates' => [
								'x'      => 38,
								'y'      => 570,
								'h'      => 15,
								'w'      => 220,
								'halign' => 'L',
						],
				],
				'contact_email'   => [
						'coordinates' => [
								'x'      => 290,
								'y'      => 570,
								'h'      => 15,
								'w'      => 200,
								'halign' => 'L',
						],
				],


				'previous_l1'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 38,
								'y'      => 257, //Was: 315
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l1'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 171,
								'y'      => 257,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l2'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 305,
								'y'      => 257,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l2'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 438,
								'y'      => 257,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l3'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 38,
								'y'      => 282,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l3'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 171,
								'y'      => 282,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l4'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 305,
								'y'      => 282,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l4'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 438,
								'y'      => 282,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l5'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 38,
								'y'      => 304,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l5'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 171,
								'y'      => 304,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l6'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 305,
								'y'      => 304,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l6'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 438,
								'y'      => 304,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l7'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 38,
								'y'      => 327,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l7'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 171,
								'y'      => 327,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l8'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 305,
								'y'      => 327,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l8'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 438,
								'y'      => 327,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l10'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 305,
								'y'      => 349,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l10'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 438,
								'y'      => 349,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l11'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 38,
								'y'      => 375,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l11'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 171,
								'y'      => 375,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l12a'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 305,
								'y'      => 375,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l12a'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 438,
								'y'      => 375,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l14'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 38,
								'y'      => 395,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],
				'l14'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 171,
								'y'      => 395,
								'h'      => 10,
								'w'      => 115,
								'halign' => 'L',
						],
				],

				'previous_l16'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 38,
								'y'      => 420,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'l16'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 170,
								'y'      => 420,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],

				'previous_l17'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 305,
								'y'      => 420,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'l17'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 438,
								'y'      => 420,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'previous_l18'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 38,
								'y'      => 445,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'l18'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 170,
								'y'      => 445,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'previous_l19'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 305,
								'y'      => 445,
								'h'      => 12,
								'w'      => 125,
								'halign' => 'L',
						],
				],
				'l19'          => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 438,
								'y'      => 445,
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

	function _outputPDF( $type ) {
		//Initialize PDF with template.
		$pdf = $this->getPDFObject();

		if ( $this->getShowBackground() == true ) {
			$pdf->setSourceFile( $this->getTemplateDirectory() . DIRECTORY_SEPARATOR . $this->pdf_template );

			$this->template_index[2] = $pdf->ImportPage( 2 );
		}

		if ( $this->year == '' ) {
			$this->year = $this->getYear();
		}

		//Get location map, start looping over each variable and drawing
		$template_schema = $this->getTemplateSchema();
		if ( is_array( $template_schema ) ) {

			$template_page = null;

			foreach ( $template_schema as $field => $schema ) {
				//Debug::text('Drawing Cell... Field: '. $field, __FILE__, __LINE__, __METHOD__, 10);
				$this->Draw( $this->$field, $schema );
			}
		}

		return true;
	}
}

?>