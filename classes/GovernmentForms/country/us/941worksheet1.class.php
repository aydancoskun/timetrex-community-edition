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
class GovernmentForms_US_941WorkSheet1 extends GovernmentForms_US {
	public $pdf_template = '941worksheet1.pdf';

	public $credit_percent = 0.50; //Multiplier for Line 1d
	public $retention_credit_percent = 0.70; //Multiplier for Line 3d
	public $employer_medicare_rate = 0.0145; //1.45%

	public function getTemplateSchema( $name = null ) {
		$template_schema = [
			//Initialize page1, replace years on template.
			[
					'page'          => 1,
					'template_page' => 1,
					'value'         => '(Rev. ' . $this->year . ')',
					'on_background' => true,
					'coordinates'   => [
							'x'          => 498,
							'y'          => 766,
							'h'          => 12,
							'w'          => 60,
							'halign'     => 'L',
							'fill_color' => [ 255, 255, 255 ],
					],
					'font'          => [
							'size' => 9,
							'type' => 'B',
					],
			],
			//Finish initializing page 1.

			'l1a' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 433,
							'y'      => 156,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1b' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 433,
							'y'      => 166,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1c' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL1C', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 433,
							'y'      => 177,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1d' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL1D', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 433,
							'y'      => 188,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1e' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 433,
							'y'      => 211,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1f' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL1F', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 433,
							'y'      => 222,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1g' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 433,
							'y'      => 238,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1h' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL1H', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 503,
							'y'      => 249,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1i' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 433,
							'y'      => 260,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1j' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 433,
							'y'      => 272,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1ji' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 433,
							'y'      => 282,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1k' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL1K', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 503,
							'y'      => 297,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1l' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL1L', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 503,
							'y'      => 308,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],


			'l2a' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 433,
							'y'      => 336,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2ai' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 433,
							'y'      => 359,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2aii' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2Aii', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 433,
							'y'      => 370,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2aiii' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2Aii', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 433,
							'y'      => 385,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2b' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 433,
							'y'      => 402,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2c' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2C', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 433,
							'y'      => 418,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2d' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2D', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 503,
							'y'      => 429,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2e' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 433,
							'y'      => 439,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2ei' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 433,
							'y'      => 462,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2eii' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2Eii', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 433,
							'y'      => 474,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2eiii' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2Eii', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 433,
							'y'      => 490,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],

			'l2f' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 433,
							'y'      => 506,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2g' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2G', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 433,
							'y'      => 521,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2h' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2H', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 503,
							'y'      => 532,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2i' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2I', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 503,
							'y'      => 544,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2j' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2J', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 503,
							'y'      => 559,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2k' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2k', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 503,
							'y'      => 575,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],


			'l3a' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 433,
							'y'      => 607,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l3b' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 433,
							'y'      => 622,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l3c' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL3C', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 433,
							'y'      => 634,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l3d' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL3D', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 503,
							'y'      => 645,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l3e' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL3E', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 433,
							'y'      => 656,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l3f' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL3F', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 433,
							'y'      => 671,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l3g' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL3G', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 433,
							'y'      => 683,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l3h' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL3H', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 503,
							'y'      => 698,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l3i' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL3I', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 503,
							'y'      => 715,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
		];

		if ( isset( $template_schema[$name] ) ) {
			return $name;
		} else {
			return $template_schema;
		}
	}

	function calcL1C( $value = null, $schema = null ) {
		$this->l1c = $this->MoneyFormat( bcadd( $this->l1a, $this->l1b ) );

		return $this->l1c;
	}

	function calcL1D( $value = null, $schema = null ) {
		$this->l1d = $this->MoneyFormat( bcmul( $this->l1c, $this->credit_percent ) );

		return $this->l1d;
	}

	function calcL1F( $value = null, $schema = null ) {
		$this->l1f = $this->MoneyFormat( bcsub( $this->l1d, $this->l1e ) );

		return $this->l1f;
	}

	function calcL1H( $value = null, $schema = null ) {
		$this->l1h = $this->MoneyFormat( bcadd( $this->l1f, $this->l1g ) );

		return $this->l1h;
	}

	function calcL1K( $value = null, $schema = null ) {
		$this->l1k = $this->MoneyFormat( bcadd( $this->l1i, bcadd( $this->l1j, $this->l1ji ) ) );

		return $this->l1k;
	}

	function calcL1L( $value = null, $schema = null ) {
		$this->l1l = $this->MoneyFormat( max( bcsub( $this->l1h, $this->l1k ), 0 ) ); //Don't go below $0

		return $this->l1l;
	}


	function calcL2Aii( $value = null, $schema = null ) {
		$this->l2aii = $this->MoneyFormat( bcadd( $this->l2a, $this->l2ai ) );

		return $this->l2aii;
	}

	function calcL2C( $value = null, $schema = null ) {
		$this->l2c = $this->MoneyFormat( bcmul( $this->l2aii, $this->employer_medicare_rate ) );

		return $this->l2c;
	}

	function calcL2D( $value = null, $schema = null ) {
		$this->l2d = $this->MoneyFormat( bcadd( $this->l2aii, bcadd( $this->l2b, $this->l2c ) ) );

		return $this->l2d;
	}

	function calcL2Eii( $value = null, $schema = null ) {
		$this->l2eii = $this->MoneyFormat( bcadd( $this->l2e, $this->l2ei ) );

		return $this->l2eii;
	}

	function calcL2G( $value = null, $schema = null ) {
		$this->l2g = $this->MoneyFormat( bcmul( $this->l2eii, $this->employer_medicare_rate ) );

		return $this->l2g;
	}

	function calcL2H( $value = null, $schema = null ) {
		$this->l2h = $this->MoneyFormat( bcadd( $this->l2eii, bcadd( $this->l2f, $this->l2g ) ) );

		return $this->l2h;
	}

	function calcL2I( $value = null, $schema = null ) {
		$this->l2i = $this->MoneyFormat( bcadd( $this->l2d, $this->l2h ) );

		return $this->l2i;
	}

	function calcL2J( $value = null, $schema = null ) {
		$this->l2j = min( $this->l1l, $this->l2i );

		return $this->l2j;
	}

	function calcL2k( $value = null, $schema = null ) {
		$this->l2k = $this->MoneyFormat( bcsub( $this->l2i, $this->l2j ) );

		return $this->l2k;
	}

	function calcL3C( $value = null, $schema = null ) {
		$this->l3c = $this->MoneyFormat( bcadd( $this->l3a, $this->l3b ) );

		return $this->l3c;
	}

	function calcL3D( $value = null, $schema = null ) {
		$this->l3d = $this->MoneyFormat( bcmul( $this->l3c, $this->retention_credit_percent ) );

		return $this->l3d;
	}

	function calcL3E( $value = null, $schema = null ) {
		$this->l3e = $this->MoneyFormat( $this->l1l );

		return $this->l3e;
	}

	function calcL3F( $value = null, $schema = null ) {
		$this->l3f = $this->MoneyFormat( $this->l2j );

		return $this->l3f;
	}

	function calcL3G( $value = null, $schema = null ) {
		$this->l3g = $this->MoneyFormat( bcsub( $this->l3e, $this->l3f ) );

		return $this->l3g;
	}

	function calcL3H( $value = null, $schema = null ) {
		$this->l3h = min( $this->l3d, $this->l3g );

		return $this->l3h;
	}

	function calcL3I( $value = null, $schema = null ) {
		$this->l3i = $this->MoneyFormat( bcsub( $this->l3d, $this->l3h ) );

		return $this->l3i;
	}

	function _outputPDF( $type ) {
		//Initialize PDF with template.
		$pdf = $this->getPDFObject();

		if ( $this->getShowBackground() == true ) {
			$pdf->setSourceFile( $this->getTemplateDirectory() . DIRECTORY_SEPARATOR . $this->pdf_template );

			$this->template_index[1] = $pdf->ImportPage( 1 );
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
}

?>