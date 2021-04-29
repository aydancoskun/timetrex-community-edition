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
class GovernmentForms_US_1096 extends GovernmentForms_US {
	public $pdf_template = '1096.pdf';

	public $template_offsets = array(0, 0);

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
				array(
						'page'          => 1,
						'template_page' => 2,
						'value'         => $this->year,
						'on_background' => TRUE,
						'coordinates'   => array(
								'x'          => 510,
								'y'          => 65,
								'h'          => 20,
								'w'          => 60,
								'halign'     => 'C',
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
								'x'          => 95,
								'y'          => 597,
								'h'          => 10,
								'w'          => 20,
								'halign'     => 'C',
								'fill_color' => array(255, 255, 255),
						),
						'font'          => array(
								'size' => 8,
								'type' => '',
						),
				),
				array(
						'value'         => $this->year,
						'on_background' => TRUE,
						'coordinates'   => array(
								'x'          => 79,
								'y'          => 744,
								'h'          => 10,
								'w'          => 20,
								'halign'     => 'C',
								'fill_color' => array(255, 255, 255),
						),
						'font'          => array(
								'size' => 8,
								'type' => 'B',
						),
				),
				array(
						'value'         => '('. $this->year .')',
						'on_background' => TRUE,
						'coordinates'   => array(
								'x'          => 555,
								'y'          => 733,
								'h'          => 11,
								'w'          => 22,
								'halign'     => 'C',
								'fill_color' => array(255, 255, 255),
						),
						'font'          => array(
								'size' => 8,
								'type' => '',
						),
				),
				array(
						'value'         => ( $this->year + 1 ),
						'on_background' => TRUE,
						'coordinates'   => array(
								'x'          => 357,
								'y'          => 485,
								'h'          => 10,
								'w'          => 20,
								'halign'     => 'C',
								'fill_color' => array(255, 255, 255),
						),
						'font'          => array(
								'size' => 8,
								'type' => '',
						),
				),
				array(
						'value'         => ( $this->year + 1 ),
						'on_background' => TRUE,
						'coordinates'   => array(
								'x'          => 475,
								'y'          => 497,
								'h'          => 10,
								'w'          => 20,
								'halign'     => 'C',
								'fill_color' => array(255, 255, 255),
						),
						'font'          => array(
								'size' => 8,
								'type' => '',
						),
				),
				array(
						'value'         => ( $this->year + 1 ),
						'on_background' => TRUE,
						'coordinates'   => array(
								'x'          => 445,
								'y'          => 509,
								'h'          => 10,
								'w'          => 20,
								'halign'     => 'C',
								'fill_color' => array(255, 255, 255),
						),
						'font'          => array(
								'size' => 8,
								'type' => '',
						),
				),
				'ein'             => array(
						'function'    => array('formatEIN', 'drawNormal'),
						'coordinates' => array(
								'x'      => 53,
								'y'      => 250,
								'h'      => 15,
								'w'      => 80,
								'halign' => 'L',
						),
				),
				'trade_name'      => array(
						'coordinates' => array(
								'x'      => 66,
								'y'      => 110,
								'h'      => 15,
								'w'      => 220,
								'halign' => 'L',
						),
				),
				'company_address' => array(
						'function'    => array('filterCompanyAddress', 'drawNormal'),
						'coordinates' => array(
								'x'      => 66,
								'y'      => 142,
								'h'      => 30,
								'w'      => 220,
								'halign' => 'L',
						),
						'font'        => array(
								'size' => 8,
								'type' => '',
						),
						'multicell'   => TRUE,
				),
				'company_city_state' => array(
						'function'    => array('filterCompanyCityStateZIP', 'drawNormal'),
						'coordinates' => array(
								'x'      => 66,
								'y'      => 178,
								'h'      => 15,
								'w'      => 220,
								'halign' => 'L',
						),
						'font'        => array(
								'size' => 8,
								'type' => '',
						),
				),
				'contact_name'  => array(
						'coordinates' => array(
								'x'      => 53,
								'y'      => 200,
								'h'      => 15,
								'w'      => 200,
								'halign' => 'L',
						),
				),
				'contact_phone' => array(
						'coordinates' => array(
								'x'      => 255,
								'y'      => 200,
								'h'      => 15,
								'w'      => 140,
								'halign' => 'L',
						),
				),
				'contact_email' => array(
						'coordinates' => array(
								'x'      => 53,
								'y'      => 225,
								'h'      => 15,
								'w'      => 200,
								'halign' => 'L',
						),
				),
				'contact_fax'   => array(
						'coordinates' => array(
								'x'      => 255,
								'y'      => 225,
								'h'      => 15,
								'w'      => 140,
								'halign' => 'L',
						),
				),

				'l3' => array( //Total 1099 forms
											'coordinates' => array(
													'x'      => 255,
													'y'      => 251,
													'h'      => 15,
													'w'      => 80,
													'halign' => 'C',
											),
				),
				'form_type_1099misc' => array(
						'value' => TRUE,
						'function'    => 'drawCheckBox',
						'coordinates' => array(
								array(
										'x'      => 91,
										'y'      => 354,
										'h'      => 8,
										'w'      => 12,
										'halign' => 'C',
								),
						),
						'font'        => array(
								'size' => 10,
								'type' => 'B',
						),
				),

				'l4' => array(
						'function'    => array('MoneyFormat', 'drawNormal'),
						'coordinates' => array(
								'x'      => 345,
								'y'      => 251,
								'h'      => 15,
								'w'      => 80,
								'halign' => 'L',
						),
				),
				'l5' => array(
						'function'    => array('MoneyFormat', 'drawNormal'),
						'coordinates' => array(
								'x'      => 445,
								'y'      => 251,
								'h'      => 15,
								'w'      => 80,
								'halign' => 'L',
						),
				),

		);

		if ( isset( $template_schema[ $name ] ) ) {
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

	function _outputPDF() {
		//Initialize PDF with template.
		$pdf = $this->getPDFObject();

		if ( $this->getShowBackground() == TRUE ) {
			$pdf->setSourceFile( $this->getTemplateDirectory() . DIRECTORY_SEPARATOR . $this->pdf_template );

			$this->template_index[2] = $pdf->ImportPage( 2 );
		}

		if ( $this->year == '' ) {
			$this->year = $this->getYear();
		}

		//Get location map, start looping over each variable and drawing
		$template_schema = $this->getTemplateSchema();
		if ( is_array( $template_schema ) ) {

			$template_page = NULL;

			foreach ( $template_schema as $field => $schema ) {
				//Debug::text('Drawing Cell... Field: '. $field, __FILE__, __LINE__, __METHOD__, 10);
				$this->Draw( $this->$field, $schema );
			}
		}

		return TRUE;
	}
}

?>