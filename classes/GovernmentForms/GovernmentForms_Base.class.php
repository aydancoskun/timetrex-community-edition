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


/**
 * @package GovernmentForms
 */
class GovernmentForms_Base {

	public $debug = false;
	public $data = null;        //Form data is stored here in an array.
	public $records = [];       //Store multiple records here to process on a single form. ie: T4's where two employees can be on a single page.
	public $records_total = []; //Total for all records.

	public $class_directory = null;

	/*
	 * PDF related variables
	 */
	public $pdf_object = null;
	public $template_index = [];
	public $current_template_index = null;

	public $page_margins = [ 0, 43 ];    //x, y - 43pt = 15mm Absolute margins that affect all drawing and templates.
	public $page_offsets = [ 0, 0 ];     //x, y - Only affects drawing.
	public $template_offsets = [ 0, 0 ]; //x, y - Only affects templates.

	public $temp_page_offsets = [ 0, 0 ]; //x, y - Only affects drawing and is reset based on page_offets above.

	public $show_background = true; //Shows the PDF background
	public $default_font = 'helvetica';

	function __construct() {
		$this->temp_page_offsets = $this->page_offsets; //Default temp page offets to whatever page offsets is originally set to.

		return true;
	}

	function setDebug( $bool ) {
		$this->debug = $bool;
	}

	function getDebug() {
		return $this->debug;
	}

	function setClassDirectory( $dir ) {
		$this->class_directory = $dir;
	}

	function getClassDirectory() {
		return $this->class_directory;
	}

	function Output( $type ) {
		$this->calculate(); //Run all calculation functions prior to outputting anything.
		switch ( strtolower( $type ) ) {
			case 'pdf':
				$retval = $this->_outputPDF( $type );
				break;
			case 'xml':
				$retval = $this->_outputXML( $type );
				break;
			case 'efile':
				$retval = $this->_outputEFILE();
				break;
			default:
				$retval = false;
				break;
		}

		return $retval;
	}

	function getRecords() {
		return $this->records;
	}

	function setRecords( $data ) {
		if ( is_array( $data ) ) {
			foreach ( $data as $record ) {
				$this->addRecord( $record ); //Make sure preCalc() is called for each record.
			}
		} else {
			$this->records = $data;
		}

		return true;
	}

	function addRecord( $data ) {
		//Filter functions should only be used for drawing the PDF, they do not modify the actual values themselves.
		//preCalc functions should be used to modify the actual values themselves, prior to drawing on the PDF, as well prior to totalling.
		//This is also important for calculating totals, so we can cap maximum contributions and such and get totals based on those properly.
		//preCalc functions can modify any other value in the record as well.
		if ( is_array( $data ) ) {
			$template_schema = $this->getTemplateSchema();
			if ( is_array( $template_schema ) ) {
				foreach ( $data as $key => $value ) {
					if ( isset( $template_schema[$key]['function']['precalc'] ) ) {
						$filter_function = $template_schema[$key]['function']['precalc'];
						if ( $filter_function != '' ) {
							if ( !is_array( $filter_function ) ) {
								$filter_function = (array)$filter_function;
							}

							foreach ( $filter_function as $function ) {
								//Call function
								if ( method_exists( $this, $function ) ) {
									$value = $this->$function( $value, $key, $data );
								}
							}
							unset( $function );
						}

						$data[$key] = $value;
					}
				}
			}

			$this->records[] = $data;
		}

		return true;
	}

	function clearRecords() {
		$this->records = [];
	}

	function countRecords() {
		return count( $this->records );
	}

	//Totals all the values for all the records.
	function sumRecords() {
		//Make sure we handle array elements with letters, so we can properly combine boxes with the same letters.
		$this->records_total = Misc::ArrayAssocSum( $this->records, null, null, true );

		return true;
	}

	function getRecordsTotal() {
		return $this->records_total;
	}

	/*
	 *
	 * Math functions
	 *
	 */
	function MoneyFormatPretty( $value ) {
		if ( !is_numeric( $value ) ) {
			return false;
		}

		return number_format( $value, 2, '.', ',' );
	}

	function MoneyFormat( $value ) {
		if ( !is_numeric( $value ) ) {
			return false;
		}

		return number_format( $value, 2, '.', '' );
	}

	function getBeforeDecimal( $float ) {
		$float = $this->MoneyFormat( $float );

		$float_array = preg_split( '/\./', $float );

		if ( isset( $float_array[0] ) ) {
			return $float_array[0];
		}

		return false;
	}

	function getAfterDecimal( $float, $format_number = true ) {
		if ( $format_number == true ) {
			$float = $this->MoneyFormat( $float );
		}

		$float_array = preg_split( '/\./', $float );

		if ( isset( $float_array[1] ) ) {
			return str_pad( $float_array[1], 2, '0' );
		}

		return false;
	}

	/**
	 * Need to use bcmath for large numbers, especially on 32bit PHP installs.
	 * @param $array
	 * @return int|string
	 */
	static function arraySum( $array ) {
		$retval = 0;
		foreach ( $array as $value ) {
			$retval = bcadd( $retval, $value );
		}

		return $retval;
	}

	/*
	 *
	 * Date functions
	 *
	 */
	public function getYear( $epoch = null ) {
		if ( $epoch == null ) {
			$epoch = TTDate::getTime();
		}

		return date( 'Y', $epoch );
	}

	/*
	 *
	 * Formatting functions
	 *
	 */
	public function formatSSN( $value ) {
		$value = substr_replace( $value, '-', 3, 0 );
		$value = substr_replace( $value, '-', 6, 0 );

		return $value;
	}

	public function formatEIN( $value ) {
		return substr_replace( $value, '-', 2, 0 );
	}

	/*
	 *
	 * Validation functions
	 *
	 */
	function isNumeric( $value ) {
		if ( is_numeric( $value ) ) {
			return $value;
		}

		return false;
	}

	/*
	 *
	 * Filter functions
	 *
	 */
	function stripSpaces( $value ) {
		return str_replace( ' ', '', trim( $value ) );
	}

	function stripNonNumeric( $value ) {
		$retval = preg_replace( '/[^0-9]/', '', $value );

		return $retval;
	}

	function stripNonAlphaNumeric( $value ) {
		$retval = preg_replace( '/[^A-Za-z0-9\ ]/', '', $value ); //Don't strip spaces

		return $retval;
	}

	function stripNonFloat( $value ) {
		$retval = preg_replace( '/[^-0-9\.]/', '', $value );

		return $retval;
	}

	/*
	 *
	 * EFILE (Fixed Length) Helper functions
	 *
	 */
	function removeDecimal( $value ) {
		$retval = str_replace( '.', '', number_format( $value, 2, '.', '' ) );

		return $retval;
	}

	function padRecord( $value, $length, $type ) {
		$type = strtolower( $type );

		//Trim record incase its too long.
		$value = substr( $value, 0, $length );

		switch ( $type ) {
			case 'n':
				$retval = str_pad( $value, $length, 0, STR_PAD_LEFT );
				break;
			case 'an':
				$retval = str_pad( $value, $length, ' ', STR_PAD_RIGHT );
				break;
		}

		return $retval;
	}

	function padLine( $line, $length = false ) {
		if ( $line == '' ) {
			return false;
		}

		$retval = str_pad( $line, ( $length == false ) ? strlen( $line ) : $length, ' ', STR_PAD_RIGHT );

		return $retval . "\r\n";
	}

	/*
	 *
	 * XML helper functions
	 *
	 */
	function setXMLObject( &$obj ) {
		$this->xml_object = $obj;

		return true;
	}

	function getXMLObject() {
		return $this->xml_object;
	}

	/*
	 *
	 * PDF helper functions
	 *
	 */
	function setPDFObject( &$obj ) {
		$this->pdf_object = $obj;

		return true;
	}

	function getPDFObject() {
		return $this->pdf_object;
	}

	function setShowBackground( $bool ) {
		$this->show_background = $bool;

		return true;
	}

	function getShowBackground() {
		return $this->show_background;
	}

	function setPageMargins( $x, $y ) {
		$this->page_margins = [ $x, $y ];

		return true;
	}

	function getPageMargins( $type = null ) {
		switch ( strtolower( $type ) ) {
			case 'x':
				return $this->page_margins[0];
				break;
			case 'y':
				return $this->page_margins[1];
				break;
			default:
				return $this->page_margins;
				break;
		}
	}

	function setTempPageOffsets( $x, $y ) {
		$this->temp_page_offsets = [ $x, $y ];

		return true;
	}

	function getTempPageOffsets( $type = null ) {
		switch ( strtolower( $type ) ) {
			case 'x':
				return $this->temp_page_offsets[0];
				break;
			case 'y':
				return $this->temp_page_offsets[1];
				break;
			default:
				return $this->temp_page_offsets;
				break;
		}
	}

	function setPageOffsets( $x, $y ) {
		$this->page_offsets = [ $x, $y ];

		$this->setTempPageOffsets( $x, $y ); //Update temp page offsets each time this is called.

		return true;
	}

	function getPageOffsets( $type = null ) {
		switch ( strtolower( $type ) ) {
			case 'x':
				return $this->page_offsets[0];
				break;
			case 'y':
				return $this->page_offsets[1];
				break;
			default:
				return $this->page_offsets;
				break;
		}
	}

	function setTemplateOffsets( $x, $y ) {
		$this->template_offsets = [ $x, $y ];

		return true;
	}

	function getTemplateOffsets( $type = null ) {
		switch ( strtolower( $type ) ) {
			case 'x':
				return $this->template_offsets[0];
				break;
			case 'y':
				return $this->template_offsets[1];
				break;
			default:
				return $this->template_offsets;
				break;
		}
	}

	function getTemplateDirectory() {
		$dir = $this->getClassDirectory() . DIRECTORY_SEPARATOR . 'templates';

		return $dir;
	}

	function getSchemaSpecificCoordinates( $schema, $key, $sub_key1 = null ) {

		unset( $schema['function'] );

		if ( $sub_key1 !== null ) {
			if ( isset( $schema['coordinates'][$key][$sub_key1] ) ) {
				return [ 'coordinates' => $schema['coordinates'][$key][$sub_key1] ];
			}
		} else {
			if ( isset( $schema['coordinates'][$key] ) ) {
				return [ 'coordinates' => $schema['coordinates'][$key], 'font' => ( isset( $schema['font'] ) ) ? $schema['font'] : [] ];
			}
		}

		return false;
	}

	//This gives the same affect of adding a new page on the next time Draw() is called.
	//Can be used when multiple records are processed for a single form.
	function resetTemplatePage() {
		$this->current_template_index = null;

		return true;
	}

	//Draw all digits before the decimal in the first location, and after the decimal in the second location.
	function drawSplitDecimalFloat( $value, $schema ) {
		if ( $value != 0 || isset( $schema['draw_zero_value'] ) && $schema['draw_zero_value'] == true ) {
			$this->Draw( $this->getBeforeDecimal( $value ), $this->getSchemaSpecificCoordinates( $schema, 0 ) );
			$this->Draw( $this->getAfterDecimal( $value ), $this->getSchemaSpecificCoordinates( $schema, 1 ) );
		}

		return true;
	}

	//Draw each char/digit one at a time in different locations.
	function drawChars( $value, $schema ) {
		$value = (string)$value; //convert integer to string.
		$max = strlen( $value );
		for ( $i = 0; $i < $max; $i++ ) {
			$this->Draw( $value[$i], $this->getSchemaSpecificCoordinates( $schema, $i ) );
		}

		return true;
	}
	// Draw the same data at different locations
	// value should be string
	function drawPiecemeal( $value, $schema ) {
		unset( $schema['function'] );
		foreach ( $schema['coordinates'] as $key => $coordinates ) {
			if ( is_array( $coordinates ) ) {
				if ( isset( $schema['font'] ) ) {
					$this->Draw( $value, [ 'coordinates' => $coordinates, 'font' => $schema['font'] ] );
				} else {
					$this->Draw( $value, [ 'coordinates' => $coordinates ] );
				}
			}
		}

		return true;
	}

	//Draw each element of an array at different locations.
	//Value must be an array.
	function drawSegments( $value, $schema ) {

		if ( is_array( $value ) ) {
			$i = 0;
			foreach ( $value as $segment ) {
				$this->Draw( $segment, $this->getSchemaSpecificCoordinates( $schema, $i ) );
				$i++;
			}
		}

		return true;
	}

	//Draw an normal values in a grid.
	function drawNormalGrid( $value, $schema ) {
		if ( !is_array( $value ) ) {
			$value = (array)$value;
		}

		foreach ( $value as $key => $tmp_value ) {

			if ( $tmp_value !== false ) {
				//var_dump($tmp_value, $schema['coordinates'][$key] );

				//$this->Draw( $this->getBeforeDecimal( $value ),  array('coordinates' => $schema['coordinates'][$key][0] ) );
				//var_dump( $this->getSchemaSpecificCoordinates( $schema, $key, 0 ) );
				//$this->Draw( $this->getBeforeDecimal( $value ), $this->getSchemaSpecificCoordinates( $schema, $key, 0 ) );

				if ( is_array( $tmp_value ) ) {

					foreach ( $tmp_value as $value ) {
						$this->drawNormal( $value, $this->getSchemaSpecificCoordinates( $schema, $key ) );
					}
				} else {
					$this->drawNormal( $tmp_value, $this->getSchemaSpecificCoordinates( $schema, $key ) );
				}
				//$this->Draw( $tmp_value, $this->getSchemaSpecificCoordinates( $schema, $key ) );
			}
		}

		return true;
	}

	//Draw an split decimal values in a grid.
	function drawSplitDecimalFloatGrid( $value, $schema ) {
		if ( !is_array( $value ) ) {
			$value = (array)$value;
		}

		foreach ( $value as $key => $tmp_value ) {

			if ( $tmp_value !== false ) {
				//var_dump($tmp_value, $schema['coordinates'][$key] );

				//$this->Draw( $this->getBeforeDecimal( $value ),  array('coordinates' => $schema['coordinates'][$key][0] ) );
				//var_dump( $this->getSchemaSpecificCoordinates( $schema, $key, 0 ) );
				//$this->Draw( $this->getBeforeDecimal( $value ), $this->getSchemaSpecificCoordinates( $schema, $key, 0 ) );

				if ( is_array( $tmp_value ) ) {

					foreach ( $tmp_value as $value ) {
						$this->drawSplitDecimalFloat( $value, $this->getSchemaSpecificCoordinates( $schema, $key ) );
					}
				} else {
					$this->drawSplitDecimalFloat( $tmp_value, $this->getSchemaSpecificCoordinates( $schema, $key ) );
				}
				//$this->Draw( $tmp_value, $this->getSchemaSpecificCoordinates( $schema, $key ) );
			}
		}

		return true;
	}

	//Draw an X in each of the specified locations
	//$value must be an array.
	function drawCheckBox( $value, $schema ) {
		$char = 'x';

		if ( !is_array( $value ) ) {
			$value = (array)$value;
		}

		foreach ( $value as $tmp_value ) {
			//Skip any false values.
			if ( $tmp_value === false ) {
				continue;
			}

			if ( is_string( $tmp_value ) ) {
				$tmp_value = strtolower( $tmp_value );
			}

			if ( is_bool( $tmp_value ) && $tmp_value == true ) {
				$tmp_value = 0;
			}

			$this->Draw( $char, $this->getSchemaSpecificCoordinates( $schema, $tmp_value ) );
		}

		return true;
	}

	function drawNormal( $value, $schema ) {
		if ( $value !== false ) {         //If value is FALSE don't draw anything, this prevents a blank cell from being drawn overtop of other text.
			unset( $schema['function'] ); //Strip off the function element to prevent infinite loop
			$this->Draw( $value, $schema );

			return true;
		}

		return false;
	}

	function drawGrid( $value, $schema ) {

		unset( $schema['function'] );

		if ( isset( $schema['grid'] ) ) {
			$grid = $schema['grid'];
		}

		if ( is_array( $value ) ) {

			if ( isset( $grid ) && is_array( $grid ) ) {

				$top_left_x = $x = $grid['top_left_x'];
				$top_left_y = $y = $grid['top_left_y'];
				$h = $grid['h'];
				$w = $grid['w'];
				$step_x = $grid['step_x'];
				$step_y = $grid['step_y'];
				$col = $grid['column'];

				$i = 1;
				foreach ( $value as $val ) {

					$coordinates = [
							'x' => $x,
							'y' => $y,
							'h' => $h,
							'w' => $w,
					];

					$schema['coordinates'] = array_merge( $schema['coordinates'], $coordinates );

					$this->Draw( $val, $schema );

					if ( $i > 0 && $i % $col == 0 ) {
						$x = $top_left_x;
						$y += $step_y;
					} else {
						$x += $step_x;
					}
					$i++;
				}
			}
		}

		return true;
	}


	function addPage( $schema = null ) {
		$pdf = $this->getPDFObject();

		$pdf->AddPage();
		if ( $this->getShowBackground() == true && isset( $this->template_index[$schema['template_page']] ) ) {
			if ( isset( $schema['combine_templates'] ) && is_array( $schema['combine_templates'] ) ) {
				$template_schema = $this->getTemplateSchema();

				//Handle combining multiple template together with a X,Y offset.
				foreach ( $schema['combine_templates'] as $combine_template ) {
					//Debug::text('Combining Template Pages... Template: '. $combine_template['template_page'] .' Y: '. $combine_template['y'], __FILE__, __LINE__, __METHOD__, 10);
					$pdf->useTemplate( $this->template_index[$combine_template['template_page']], ( $combine_template['x'] + $this->getTemplateOffsets( 'x' ) + $this->getPageMargins( 'x' ) ), ( $combine_template['y'] + $this->getTemplateOffsets( 'y' ) + $this->getPageMargins( 'y' ) ) );

					$this->setTempPageOffsets( ( $combine_template['x'] + $this->getPageOffsets( 'x' ) ), ( $combine_template['y'] + $this->getPageOffsets( 'y' ) ) );
					$this->current_template_index = $combine_template['template_page'];

					//For things like W2 instruction templates at the bottom half of the page, allow the initPage() function to be disabled for the template.
					if ( !isset( $combine_template['init'] ) || ( isset( $combine_template['init'] ) && $combine_template['init'] == true ) ) {
						$this->initPage( $template_schema );
					}
				}
				unset( $combine_templates );
				$this->setTempPageOffsets( $this->getPageOffsets( 'x' ), $this->getPageOffsets( 'y' ) ); //Reset page offsets after each template is initialized.
			} else {
				$pdf->useTemplate( $this->template_index[$schema['template_page']], ( $this->getTemplateOffsets( 'x' ) + $this->getPageMargins( 'x' ) ), ( $this->getTemplateOffsets( 'y' ) + $this->getPageMargins( 'y' ) ) );
			}
		}
		$this->current_template_index = $schema['template_page'];


		return true;
	}

	function initPage( $template_schema ) {
		if ( is_array( $template_schema ) ) {
			foreach ( $template_schema as $field => $init_schema ) {
				if ( is_numeric( $field ) ) {
					//Debug::text(' Initializing Template Page... Field: '. $field, __FILE__, __LINE__, __METHOD__, 10);
					$this->Draw( $this->$field, $init_schema );
				}
			}
			unset( $template_schema, $field, $init_schema );

			return true;
		}

		return false;
	}

	//Run all calculate functions on their own.
	//  This separates calculating values from the drawing process, so we can easily pull out calculated values before anything is drawn, as other forms may need that data.
	function calculate() {
		//Get location map, start looping over each variable and drawing
		$template_schema = $this->getTemplateSchema();
		if ( is_array( $template_schema ) ) {
			foreach ( $template_schema as $field => $schema ) {
				//If custom function is defined, pass off to that immediate.
				//Else, try the generic drawing method.
				if ( isset( $schema['function']['calc'] ) ) {
					if ( !is_array( $schema['function']['calc'] ) ) {
						$schema['function']['calc'] = (array)$schema['function']['calc'];
					}
					foreach ( $schema['function']['calc'] as $function ) {
						if ( method_exists( $this, $function ) ) {
							if ( !isset( $template_schema[$field]['value'] ) ) {
								//$template_schema[$field]['value'] = ( isset( $this->{$field} ) ? $this->{$field} : null ); //This passes in the field value, which the calculate function can get on its own without a problem.
								$template_schema[$field]['value'] = null;
							}
							$template_schema[$field]['value'] = $this->$function( $template_schema[$field]['value'], $schema );
						}
					}
					unset( $function );
				}
			}
		}

		return true;
	}

	//Generic draw function that works strictly off the coordinate map.
	//It checks for a variable specific function before running though, so we can handle more complex
	//drawing functionality.
	function Draw( $value, $schema ) {
		if ( !is_array( $schema ) ) {
			return false;
		}

		//If its set, use the static value from the schema.
		if ( isset( $schema['value'] ) ) {
			$value = $schema['value'];
			unset( $schema['value'] );
		}

		//If custom function is defined, pass off to that immediate.
		//Else, try the generic drawing method.
		if ( isset( $schema['function']['draw'] ) ) {
			if ( !is_array( $schema['function']['draw'] ) ) {
				$schema['function']['draw'] = (array)$schema['function']['draw'];
			}
			foreach ( $schema['function']['draw'] as $function ) {
				if ( method_exists( $this, $function ) ) {
					$value = $this->$function( $value, $schema );
				}
			}
			unset( $function );

			return $value;
		}

		$pdf = $this->getPDFObject();

		//Make sure we don't load the same template more than once.
		if ( isset( $schema['template_page'] ) && $schema['template_page'] != $this->current_template_index ) {
			//Debug::text('Adding new page: '. $schema .' Template Page: '. $schema['template_page'], __FILE__, __LINE__, __METHOD__, 10);
			$this->addPage( $schema );
		} else {
			//Debug::text('Skipping template... Value: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		}

		//If only_template_page is set, then only draw when we are on that template.
		if ( isset( $schema['only_template_page'] ) && ( ( is_array( $schema['only_template_page'] ) && !in_array( $this->current_template_index, $schema['only_template_page'] ) ) || ( !is_array( $schema['only_template_page'] ) && $schema['only_template_page'] != $this->current_template_index ) ) ) {
			//Debug::text('Skipping template based on filter... Value: '. $value, __FILE__, __LINE__, __METHOD__, 10);
			return false;
		}

		//on_background flag forces that item to only be shown if the background is as well.
		//This has to go below any addPage() call, otherwise pages won't be added if the first cell is only to be shown on the background.
		if ( isset( $schema['on_background'] ) && $schema['on_background'] == true && $this->getShowBackground() == false ) {
			return false;
		}

		if ( isset( $schema['font'] ) ) {
			if ( !isset( $schema['font']['font'] ) ) {
				$schema['font']['font'] = $this->default_font;
			}
			if ( !isset( $schema['font']['type'] ) ) {
				$schema['font']['type'] = '';
			}
			if ( !isset( $schema['font']['size'] ) ) {
				$schema['font']['size'] = 8;
			}

			$pdf->SetFont( $schema['font']['font'], $schema['font']['type'], $schema['font']['size'] );
		} else {
			$pdf->SetFont( $this->default_font, '', 8 );
		}

		if ( isset( $schema['coordinates'] ) ) {
			$coordinates = $schema['coordinates'];
			//var_dump( Debug::BackTrace() );

			if ( isset( $coordinates['text_color'] ) && is_array( $coordinates['text_color'] ) ) {
				$pdf->setTextColor( $coordinates['text_color'][0], $coordinates['text_color'][1], $coordinates['text_color'][2] );
			} else {
				$pdf->setTextColor( 0, 0, 0 ); //Black text.
			}

			if ( isset( $coordinates['fill_color'] ) && is_array( $coordinates['fill_color'] ) ) {
				$pdf->setFillColor( $coordinates['fill_color'][0], $coordinates['fill_color'][1], $coordinates['fill_color'][2] );
				$coordinates['fill'] = 1;
			} else {
				$pdf->setFillColor( 255, 255, 255 ); //White
				$coordinates['fill'] = 0;
			}

			$pdf->setXY( ( $coordinates['x'] + $this->getTempPageOffsets( 'x' ) + $this->getPageMargins( 'x' ) ), ( $coordinates['y'] + $this->getTempPageOffsets( 'y' ) + $this->getPageMargins( 'y' ) ) );

			if ( $this->getDebug() == true ) {
				$pdf->setDrawColor( 0, 0, 255 );
				$coordinates['border'] = 1;
			} else {
				if ( !isset( $coordinates['border'] ) ) {
					$coordinates['border'] = 0;
				}
			}

			if ( isset( $schema['multicell'] ) && $schema['multicell'] == true ) {
				//Debug::text('Drawing MultiCell... Value: '. $value, __FILE__, __LINE__, __METHOD__, 10);
				$pdf->MultiCell( $coordinates['w'], $coordinates['h'], $value, $coordinates['border'], strtoupper( $coordinates['halign'] ), $coordinates['fill'] );
			} else {
				//Debug::text('Drawing Cell... Value: '. $value, __FILE__, __LINE__, __METHOD__, 10);
				$pdf->Cell( $coordinates['w'], $coordinates['h'], $value, $coordinates['border'], 0, strtoupper( $coordinates['halign'] ), $coordinates['fill'], false, 1 );
			}
			unset( $coordinates );
		} else {
			Debug::text( 'NOT Drawing Cell... Value: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return true;
	}

	//Make sure we pass *ALL* data to this function, as it will overwrite existing data, but if one record has a field and another one doesn't,
	//we need to send blank fields so the data is overwritten correctly.
	function arrayToObject( $array ) {
		if ( is_array( $array ) ) {
			foreach ( $array as $key => $value ) {
				$this->$key = $value;
			}
		}

		return true;
	}

	/*
	 *
	 * Magic functions.
	 *
	 */
	function __set( $name, $value ) {
		$template_schema = $this->getTemplateSchema();
		if ( is_array( $template_schema ) && isset( $template_schema[$name]['function']['prefilter'] ) ) {
			$filter_function = $template_schema[$name]['function']['prefilter'];
			if ( $filter_function != '' ) {
				if ( !is_array( $filter_function ) ) {
					$filter_function = (array)$filter_function;
				}

				foreach ( $filter_function as $function ) {
					//Call function
					if ( method_exists( $this, $function ) ) {
						$value = $this->$function( $value );

						if ( $value === false ) {
							return false;
						}
					}
				}
				unset( $function );
			}
		}

		$this->data[$name] = $value;

		return true;
	}

	function __get( $name ) {
		if ( isset( $this->data[$name] ) ) {
			return $this->data[$name];
		}

		return false;
	}

	public function __isset( $name ) {
		return isset( $this->data[$name] );
	}

	public function __unset( $name ) {
		unset( $this->data[$name] );
	}
}

?>