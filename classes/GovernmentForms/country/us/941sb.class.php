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
class GovernmentForms_US_941SB extends GovernmentForms_US {
	public $pdf_template = '941sb.pdf';

	public function getFilterFunction( $name ) {
		$variable_function_map = [
				'year' => 'isNumeric',
				'ein'  => [ 'stripNonNumeric', 'isNumeric' ],
		];

		if ( isset( $variable_function_map[$name] ) ) {
			return $variable_function_map[$name];
		}

		return false;
	}

	public function getTemplateSchema( $name = null ) {
		$template_schema = [
			//Initialize page1, replace years on template.
			[
					'page'          => 1,
					'template_page' => 1,
					'value'         => '(Rev. ' . $this->year . ')',
					'on_background' => true,
					'coordinates'   => [
							'x'          => 32,
							'y'          => 100,
							'h'          => 11,
							'w'          => 70,
							'halign'     => 'L',
							'fill_color' => [ 255, 255, 255 ],
					],
					'font'          => [
							'size' => 7,
					],
			],
			[
					'value'         => '(Rev. ' . $this->year . ')',
					'on_background' => true,
					'coordinates'   => [
							'x'          => 521,
							'y'          => 778,
							'h'          => 11,
							'w'          => 40,
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
					'function'      => 'drawChars', //custom drawing function.
					'coordinates'   => [
							[
									'type'   => 'static', //static or relative
									'x'      => 142,
									'y'      => 119,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							],
							[
									'x'      => 167,
									'y'      => 119,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							],
							[
									'x'      => 205,
									'y'      => 119,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							],
							[
									'x'      => 229,
									'y'      => 119,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							],
							[
									'x'      => 254,
									'y'      => 119,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							],
							[
									'x'      => 279,
									'y'      => 119,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							],
							[
									'x'      => 304,
									'y'      => 119,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							],
							[
									'x'      => 328,
									'y'      => 119,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							],
							[
									'x'      => 353,
									'y'      => 119,
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

			'name'    => [
					'coordinates' => [
							'x'      => 126,
							'y'      => 140,
							'h'      => 18,
							'w'      => 246,
							'halign' => 'L',
					],
			],
			'year'    => [
					'function'    => 'drawChars', //custom drawing function.
					'coordinates' => [
							[
									'type'   => 'static', //static or relative
									'x'      => 143,
									'y'      => 162,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							],
							[
									'x'      => 167,
									'y'      => 162,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							],
							[
									'x'      => 192,
									'y'      => 162,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							],
							[
									'x'      => 217,
									'y'      => 162,
									'h'      => 17,
									'w'      => 19,
									'halign' => 'C',
							],
					],
					'font'        => [
							'size' => 12,
							'type' => 'B',
					],
			],
			'quarter' => [
					'function'    => 'drawCheckBox',
					'coordinates' => [
							1 => [
									'x'      => 412,
									'y'      => 147,
									'h'      => 11,
									'w'      => 12,
									'halign' => 'C',
							],
							2 => [
									'x'      => 412,
									'y'      => 165,
									'h'      => 11,
									'w'      => 12,
									'halign' => 'C',
							],
							3 => [
									'x'      => 412,
									'y'      => 182,
									'h'      => 11,
									'w'      => 12,
									'halign' => 'C',
							],
							4 => [
									'x'      => 412,
									'y'      => 200,
									'h'      => 11,
									'w'      => 12,
									'halign' => 'C',
							],
					],
					'font'        => [
							'size' => 10,
							'type' => 'B',
					],
			],

			'month1' => [
					'function'    => 'drawSplitDecimalFloatGrid',
					'coordinates' => [
						//Column 1
						1  => [
								[
										'x'      => 41,
										'y'      => 299,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 299,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						2  => [
								[
										'x'      => 41,
										'y'      => 317,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 317,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						3  => [
								[
										'x'      => 41,
										'y'      => 334,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 334,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						4  => [
								[
										'x'      => 41,
										'y'      => 351,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 351,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						5  => [
								[
										'x'      => 41,
										'y'      => 368,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 368,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						6  => [
								[
										'x'      => 41,
										'y'      => 386,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 386,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						7  => [
								[
										'x'      => 41,
										'y'      => 403,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 403,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						8  => [
								[
										'x'      => 41,
										'y'      => 421,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 421,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],

						//Column 2
						9  => [
								[
										'x'      => 143,
										'y'      => 299,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 299,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						10 => [
								[
										'x'      => 143,
										'y'      => 317,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 317,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						11 => [
								[
										'x'      => 143,
										'y'      => 334,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 334,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						12 => [
								[
										'x'      => 143,
										'y'      => 351,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 351,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						13 => [
								[
										'x'      => 143,
										'y'      => 368,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 368,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						14 => [
								[
										'x'      => 143,
										'y'      => 386,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 386,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						15 => [
								[
										'x'      => 143,
										'y'      => 403,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 403,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						16 => [
								[
										'x'      => 143,
										'y'      => 421,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 421,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],

						//Column 3
						17 => [
								[
										'x'      => 245,
										'y'      => 299,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 299,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						18 => [
								[
										'x'      => 245,
										'y'      => 317,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 317,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						19 => [
								[
										'x'      => 245,
										'y'      => 334,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 334,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						20 => [
								[
										'x'      => 245,
										'y'      => 351,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 351,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						21 => [
								[
										'x'      => 245,
										'y'      => 368,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 368,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						22 => [
								[
										'x'      => 245,
										'y'      => 386,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 386,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						23 => [
								[
										'x'      => 245,
										'y'      => 403,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 403,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						24 => [
								[
										'x'      => 245,
										'y'      => 421,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 421,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],

						//Column 3
						25 => [
								[
										'x'      => 347,
										'y'      => 299,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 416,
										'y'      => 299,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						26 => [
								[
										'x'      => 347,
										'y'      => 317,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 416,
										'y'      => 317,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						27 => [
								[
										'x'      => 347,
										'y'      => 334,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 416,
										'y'      => 334,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						28 => [
								[
										'x'      => 347,
										'y'      => 351,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 416,
										'y'      => 351,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						29 => [
								[
										'x'      => 347,
										'y'      => 368,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 416,
										'y'      => 368,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						30 => [
								[
										'x'      => 347,
										'y'      => 386,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 416,
										'y'      => 386,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						31 => [
								[
										'x'      => 347,
										'y'      => 403,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 416,
										'y'      => 403,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],

					],
			],
			'month2' => [
					'function'    => 'drawSplitDecimalFloatGrid',
					'coordinates' => [
						//Column 1
						1  => [
								[
										'x'      => 41,
										'y'      => 452,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 452,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						2  => [
								[
										'x'      => 41,
										'y'      => 469,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 469,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						3  => [
								[
										'x'      => 41,
										'y'      => 486,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 486,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						4  => [
								[
										'x'      => 41,
										'y'      => 503,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 503,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						5  => [
								[
										'x'      => 41,
										'y'      => 521,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 521,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						6  => [
								[
										'x'      => 41,
										'y'      => 539,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 539,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						7  => [
								[
										'x'      => 41,
										'y'      => 556,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 556,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						8  => [
								[
										'x'      => 41,
										'y'      => 573,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 573,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],

						//Column 2
						9  => [
								[
										'x'      => 143,
										'y'      => 452,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 452,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						10 => [
								[
										'x'      => 143,
										'y'      => 469,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 469,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						11 => [
								[
										'x'      => 143,
										'y'      => 486,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 486,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						12 => [
								[
										'x'      => 143,
										'y'      => 503,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 503,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						13 => [
								[
										'x'      => 143,
										'y'      => 521,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 521,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						14 => [
								[
										'x'      => 143,
										'y'      => 539,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 539,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						15 => [
								[
										'x'      => 143,
										'y'      => 556,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 556,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						16 => [
								[
										'x'      => 143,
										'y'      => 573,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 573,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],


						//Column 3
						17 => [
								[
										'x'      => 245,
										'y'      => 452,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 452,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						18 => [
								[
										'x'      => 245,
										'y'      => 469,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 469,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						19 => [
								[
										'x'      => 245,
										'y'      => 486,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 486,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						20 => [
								[
										'x'      => 245,
										'y'      => 503,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 503,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						21 => [
								[
										'x'      => 245,
										'y'      => 521,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 521,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						22 => [
								[
										'x'      => 245,
										'y'      => 539,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 539,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						23 => [
								[
										'x'      => 245,
										'y'      => 556,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 556,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						24 => [
								[
										'x'      => 245,
										'y'      => 573,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 573,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						//Column 4
						25 => [
								[
										'x'      => 347,
										'y'      => 452,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 416,
										'y'      => 452,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						26 => [
								[
										'x'      => 347,
										'y'      => 469,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 416,
										'y'      => 469,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						27 => [
								[
										'x'      => 347,
										'y'      => 486,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 416,
										'y'      => 486,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						28 => [
								[
										'x'      => 347,
										'y'      => 503,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 416,
										'y'      => 503,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						29 => [
								[
										'x'      => 347,
										'y'      => 521,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 416,
										'y'      => 521,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						30 => [
								[
										'x'      => 347,
										'y'      => 539,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 416,
										'y'      => 539,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						31 => [
								[
										'x'      => 347,
										'y'      => 556,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 416,
										'y'      => 556,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
					],
			],
			'month3' => [
					'function'    => 'drawSplitDecimalFloatGrid',
					'coordinates' => [
						//Column 1
						1  => [
								[
										'x'      => 41,
										'y'      => 604,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 604,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						2  => [
								[
										'x'      => 41,
										'y'      => 621,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 621,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						3  => [
								[
										'x'      => 41,
										'y'      => 639,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 639,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						4  => [
								[
										'x'      => 41,
										'y'      => 656,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 656,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						5  => [
								[
										'x'      => 41,
										'y'      => 673,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 673,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						6  => [
								[
										'x'      => 41,
										'y'      => 691,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 691,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						7  => [
								[
										'x'      => 41,
										'y'      => 708,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 708,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						8  => [
								[
										'x'      => 41,
										'y'      => 726,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 110,
										'y'      => 726,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						//Column 2
						9  => [
								[
										'x'      => 143,
										'y'      => 604,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 604,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						10 => [
								[
										'x'      => 143,
										'y'      => 621,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 621,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						11 => [
								[
										'x'      => 143,
										'y'      => 639,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 639,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						12 => [
								[
										'x'      => 143,
										'y'      => 656,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 656,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						13 => [
								[
										'x'      => 143,
										'y'      => 673,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 673,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						14 => [
								[
										'x'      => 143,
										'y'      => 691,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 691,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						15 => [
								[
										'x'      => 143,
										'y'      => 708,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 708,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						16 => [
								[
										'x'      => 143,
										'y'      => 726,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 212,
										'y'      => 726,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],

						//Column 3
						17 => [
								[
										'x'      => 245,
										'y'      => 604,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 604,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						18 => [
								[
										'x'      => 245,
										'y'      => 621,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 621,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						19 => [
								[
										'x'      => 245,
										'y'      => 639,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 639,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						20 => [
								[
										'x'      => 245,
										'y'      => 656,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 656,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						21 => [
								[
										'x'      => 245,
										'y'      => 673,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 673,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						22 => [
								[
										'x'      => 245,
										'y'      => 691,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 691,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						23 => [
								[
										'x'      => 245,
										'y'      => 708,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 708,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						24 => [
								[
										'x'      => 245,
										'y'      => 726,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 314,
										'y'      => 726,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],

						//Column 4
						25 => [
								[
										'x'      => 347,
										'y'      => 604,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 416,
										'y'      => 604,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						26 => [
								[
										'x'      => 347,
										'y'      => 621,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 416,
										'y'      => 621,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						27 => [
								[
										'x'      => 347,
										'y'      => 639,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 416,
										'y'      => 639,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						28 => [
								[
										'x'      => 347,
										'y'      => 656,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 416,
										'y'      => 656,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						29 => [
								[
										'x'      => 347,
										'y'      => 673,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 416,
										'y'      => 673,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						30 => [
								[
										'x'      => 347,
										'y'      => 691,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 416,
										'y'      => 691,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
						31 => [
								[
										'x'      => 347,
										'y'      => 708,
										'h'      => 14,
										'w'      => 66,
										'halign' => 'R',
								],
								[
										'x'      => 416,
										'y'      => 708,
										'h'      => 14,
										'w'      => 22,
										'halign' => 'C',
								],
						],
					],
			],

			'month1_total' => [
					'function'    => [ 'calcMonth1Total', 'drawSplitDecimalFloat' ],
					'coordinates' => [
							[
									'x'      => 442,
									'y'      => 320,
									'h'      => 14,
									'w'      => 95,
									'halign' => 'R',
							],
							[
									'x'      => 540,
									'y'      => 320,
									'h'      => 14,
									'w'      => 20,
									'halign' => 'C',
							],
					],
			],
			'month2_total' => [
					'function'    => [ 'calcMonth2Total', 'drawSplitDecimalFloat' ],
					'coordinates' => [
							[
									'x'      => 442,
									'y'      => 473,
									'h'      => 14,
									'w'      => 95,
									'halign' => 'R',
							],
							[
									'x'      => 540,
									'y'      => 473,
									'h'      => 14,
									'w'      => 20,
									'halign' => 'C',
							],
					],
			],
			'month3_total' => [
					'function'    => [ 'calcMonth3Total', 'drawSplitDecimalFloat' ],
					'coordinates' => [
							[
									'x'      => 442,
									'y'      => 627,
									'h'      => 14,
									'w'      => 95,
									'halign' => 'R',
							],
							[
									'x'      => 540,
									'y'      => 627,
									'h'      => 14,
									'w'      => 20,
									'halign' => 'C',
							],
					],
			],
			'total'        => [
					'function'    => [ 'calcTotal', 'drawSplitDecimalFloat' ],
					'coordinates' => [
							[
									'x'      => 442,
									'y'      => 758,
									'h'      => 14,
									'w'      => 95,
									'halign' => 'R',
							],
							[
									'x'      => 540,
									'y'      => 758,
									'h'      => 14,
									'w'      => 20,
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


	function calcMonth1Total( $value, $schema ) {
		$this->month1_total = $this->arraySum( (array)$this->month1 );

		return $this->month1_total;
	}

	function calcMonth2Total( $value, $schema ) {
		$this->month2_total = $this->arraySum( (array)$this->month2 );

		return $this->month2_total;
	}

	function calcMonth3Total( $value, $schema ) {
		$this->month3_total = $this->arraySum( (array)$this->month3 );

		return $this->month3_total;
	}

	function calcTotal( $value, $schema ) {
		$this->total = bcadd( bcadd( $this->month1_total, $this->month2_total ), $this->month3_total );

		return $this->total;
	}

	function _outputPDF() {
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