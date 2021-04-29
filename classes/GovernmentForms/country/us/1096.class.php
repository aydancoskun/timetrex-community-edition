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
class GovernmentForms_US_1096 extends GovernmentForms_US {
	public $pdf_template = '1096.pdf';

	public function getTemplateSchema( $name = null ) {
		$template_schema = [
				[
						'page'          => 1,
						'template_page' => 2,
						'value'         => $this->year,
						'on_background' => true,
						'coordinates'   => [
								'x'          => 510,
								'y'          => 65,
								'h'          => 20,
								'w'          => 60,
								'halign'     => 'C',
								'fill_color' => [ 255, 255, 255 ],
						],
						'font'          => [
								'size' => 18,
								'type' => 'B',
						],
				],
				[
						'value'         => $this->year,
						'on_background' => true,
						'coordinates'   => [
								'x'          => 95,
								'y'          => 597,
								'h'          => 10,
								'w'          => 20,
								'halign'     => 'C',
								'fill_color' => [ 255, 255, 255 ],
						],
						'font'          => [
								'size' => 8,
								'type' => '',
						],
				],
				[
						'value'         => $this->year,
						'on_background' => true,
						'coordinates'   => [
								'x'          => 79,
								'y'          => 744,
								'h'          => 10,
								'w'          => 20,
								'halign'     => 'C',
								'fill_color' => [ 255, 255, 255 ],
						],
						'font'          => [
								'size' => 8,
								'type' => 'B',
						],
				],
				[
						'value'         => '(' . $this->year . ')',
						'on_background' => true,
						'coordinates'   => [
								'x'          => 555,
								'y'          => 733,
								'h'          => 11,
								'w'          => 22,
								'halign'     => 'C',
								'fill_color' => [ 255, 255, 255 ],
						],
						'font'          => [
								'size' => 8,
								'type' => '',
						],
				],
				[
						'value'         => ( $this->year + 1 ),
						'on_background' => true,
						'coordinates'   => [
								'x'          => 357,
								'y'          => 485,
								'h'          => 10,
								'w'          => 20,
								'halign'     => 'C',
								'fill_color' => [ 255, 255, 255 ],
						],
						'font'          => [
								'size' => 8,
								'type' => '',
						],
				],
				[
						'value'         => ( $this->year + 1 ),
						'on_background' => true,
						'coordinates'   => [
								'x'          => 475,
								'y'          => 497,
								'h'          => 10,
								'w'          => 20,
								'halign'     => 'C',
								'fill_color' => [ 255, 255, 255 ],
						],
						'font'          => [
								'size' => 8,
								'type' => '',
						],
				],
				[
						'value'         => ( $this->year + 1 ),
						'on_background' => true,
						'coordinates'   => [
								'x'          => 445,
								'y'          => 509,
								'h'          => 10,
								'w'          => 20,
								'halign'     => 'C',
								'fill_color' => [ 255, 255, 255 ],
						],
						'font'          => [
								'size' => 8,
								'type' => '',
						],
				],
				'ein'                => [
						'function'    => [ 'prefilter' => [ 'stripNonNumeric', 'isNumeric' ], 'draw' => [ 'formatEIN', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 53,
								'y'      => 250,
								'h'      => 15,
								'w'      => 80,
								'halign' => 'L',
						],
				],
				'trade_name'         => [
						'coordinates' => [
								'x'      => 66,
								'y'      => 110,
								'h'      => 15,
								'w'      => 220,
								'halign' => 'L',
						],
				],
				'company_address'    => [
						'function'    => [ 'draw' => [ 'filterCompanyAddress', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 66,
								'y'      => 142,
								'h'      => 30,
								'w'      => 220,
								'halign' => 'L',
						],
						'font'        => [
								'size' => 8,
								'type' => '',
						],
						'multicell'   => true,
				],
				'company_city_state' => [
						'function'    => [ 'draw' => [ 'filterCompanyCityStateZIP', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 66,
								'y'      => 178,
								'h'      => 15,
								'w'      => 220,
								'halign' => 'L',
						],
						'font'        => [
								'size' => 8,
								'type' => '',
						],
				],
				'contact_name'       => [
						'coordinates' => [
								'x'      => 53,
								'y'      => 200,
								'h'      => 15,
								'w'      => 200,
								'halign' => 'L',
						],
				],
				'contact_phone'      => [
						'coordinates' => [
								'x'      => 255,
								'y'      => 200,
								'h'      => 15,
								'w'      => 140,
								'halign' => 'L',
						],
				],
				'contact_email'      => [
						'coordinates' => [
								'x'      => 53,
								'y'      => 225,
								'h'      => 15,
								'w'      => 200,
								'halign' => 'L',
						],
				],
				'contact_fax'        => [
						'coordinates' => [
								'x'      => 255,
								'y'      => 225,
								'h'      => 15,
								'w'      => 140,
								'halign' => 'L',
						],
				],

				'l3'                 => [ //Total 1099 forms
										  'coordinates' => [
												  'x'      => 255,
												  'y'      => 251,
												  'h'      => 15,
												  'w'      => 80,
												  'halign' => 'C',
										  ],
				],
				'form_type_1099nec' => [
						'value'       => true,
						'function'    => [ 'draw' => 'drawCheckBox' ],
						'coordinates' => [
								[
										'x'      => 122,
										'y'      => 354,
										'h'      => 8,
										'w'      => 12,
										'halign' => 'C',
								],
						],
						'font'        => [
								'size' => 10,
								'type' => 'B',
						],
				],

				'l4' => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 345,
								'y'      => 251,
								'h'      => 15,
								'w'      => 80,
								'halign' => 'L',
						],
				],
				'l5' => [
						'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
						'coordinates' => [
								'x'      => 445,
								'y'      => 251,
								'h'      => 15,
								'w'      => 80,
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
		//Combine company address for multicell display.
		$retarr[] = $this->company_address1;
		if ( $this->company_address2 != '' ) {
			$retarr[] = $this->company_address2;
		}

		return implode( "\n", $retarr );
	}

	function filterCompanyCityStateZIP( $value ) {
		$retval = $this->company_city . ', ' . $this->company_state . ' ' . $this->company_zip_code;

		return $retval;
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