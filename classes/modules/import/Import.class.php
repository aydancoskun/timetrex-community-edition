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



/**
 * @package Modules\Import
 */
class Import {

	public $company_id = NULL;
	public $user_id = NULL;

	private $user_id_cache = NULL; //getUserIDByRowData cache.

	public $class_name = NULL;
	public $obj = NULL;
	public $data = array();

	protected $company_obj = NULL;
	protected $progress_bar_obj = NULL;
	protected $AMF_message_id = NULL;

	public $branch_options = FALSE;
	public $branch_manual_id_options = FALSE;
	public $department_options = FALSE;
	public $department_manual_id_options = FALSE;

	public $job_options = FALSE;
	public $job_manual_id_options = FALSE;
	public $job_item_options = FALSE;
	public $job_item_manual_id_options = FALSE;

	protected $search_column_priority = NULL;

	/**
	 * @return null|object
	 */
	function getObject() {
		if ( !is_object($this->obj) ) {
			$this->obj = TTnew( $this->class_name );
			$this->obj->setAMFMessageID( $this->getAMFMessageID() ); //Need to transfer the same AMF message id so progress bars continue to work.
		}

		return $this->obj;
	}

	/**
	 * @return bool
	 */
	function getCompanyObject() {
		$cf = new CompanyFactory();
		return $cf->getGenericObject( 'CompanyListFactory', $this->company_id, 'company_obj' );
	}

	/**
	 * @return null|ProgressBar
	 */
	function getProgressBarObject() {
		if	( !is_object( $this->progress_bar_obj ) ) {
			$this->progress_bar_obj = new ProgressBar();
		}

		return $this->progress_bar_obj;
	}
	//Returns the AMF messageID for each individual call.

	/**
	 * @return bool|null
	 */
	function getAMFMessageID() {
		if ( $this->AMF_message_id != NULL ) {
			return $this->AMF_message_id;
		}
		return FALSE;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setAMFMessageID( $id ) {
		if ( $id != '' ) {
			$this->AMF_message_id = $id;
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|bool|mixed
	 */
	function getOptions( $name, $parent = NULL) {
		if ( $parent == NULL OR $parent == '') {
			$retarr = $this->_getFactoryOptions( $name );
		} else {
			$retarr = $this->_getFactoryOptions( $name );
			if ( isset($retarr[$parent]) ) {
				$retarr = $retarr[$parent];
			}
		}

		if ( $name == 'columns' ) {
			//Remove columns that can never be imported.
			$retarr = Misc::trimSortPrefix( $retarr );
			unset($retarr['created_by'], $retarr['created_date'], $retarr['updated_by'], $retarr['updated_date'] );
			$retarr = Misc::addSortPrefix( $retarr );
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}

	/**
	 * @param $name
	 * @param null $parent
	 * @return bool
	 */
	protected function _getFactoryOptions( $name, $parent = NULL ) {
		return FALSE;
	}

	/**
	 * @return int|bool
	 */
	function getRawDataLines() {
		if ( isset($this->data['raw_data']) ) {
			Debug::Text('Raw Data Lines: '. count($this->data['raw_data']), __FILE__, __LINE__, __METHOD__, 10);
			return count($this->data['raw_data']);
		}

		return FALSE;
	}

	/**
	 * @param int $limit Limit the number of records returned
	 * @return array|bool|mixed
	 */
	function getRawData( $limit = NULL ) {
		if ( isset($this->data['raw_data']) ) {
			Debug::Text('zRaw Data Size: '. count($this->data['raw_data']), __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($this->data['raw_data'], 'Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

			//FIXME: There appears to be a bug in Flex where if the file has a blank column header column, no data is parsed at all in the column map step of the wizard.
			if ( $limit > 0 ) {
				Debug::Text('azRaw Data Size: '. count($this->data['raw_data']), __FILE__, __LINE__, __METHOD__, 10);
				return array_slice( $this->data['raw_data'], 0, (int)$limit );
			} else {
				Debug::Text('bzRaw Data Size: '. count($this->data['raw_data']), __FILE__, __LINE__, __METHOD__, 10);
				return $this->data['raw_data'];
			}
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRawData( $value) {
		if ( $value != '' ) {
			Debug::Text('Raw Data Size: '. count($value), __FILE__, __LINE__, __METHOD__, 10);
			$this->data['raw_data'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getRawDataFromFile() {
		$file_name = $this->getStoragePath().$this->getLocalFileName();
		if ( file_exists( $file_name ) ) {
			Debug::Text('Loading data from file: '. $file_name .'...', __FILE__, __LINE__, __METHOD__, 10);
			return $this->setRawData( Misc::parseCSV( $file_name, TRUE, FALSE, ',', 9216, 0 ) );
		}

		Debug::Text('Loading data from file: '. $file_name .' Failed!', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	/**
	 * @param $data
	 * @return bool|int
	 */
	function saveRawDataToFile( $data ) {
		Debug::Text('Company ID: '. $this->company_id, __FILE__, __LINE__, __METHOD__, 10);
		$dir = $this->getStoragePath();
		Debug::Text('Storage Path: '. $dir, __FILE__, __LINE__, __METHOD__, 10);
		if ( isset($dir) ) {
			@mkdir($dir, 0700, TRUE);

			return file_put_contents( $dir.$this->getLocalFileName(), $data );
		}

		return FALSE;
	}

	/**
	 * @return array|bool
	 */
	function getRawDataColumns() {
		$raw_data = $this->getRawData();
		if ( is_array( $raw_data ) ) {

			$retarr = array();
			foreach( $raw_data as $raw_data_row ) {
				foreach( $raw_data_row as $raw_data_column => $raw_data_column_data ) {
					$retarr[] = $raw_data_column;
				}
				unset($raw_data_column_data); //code standards
				break;
			}
			Debug::Arr($retarr, 'Raw Data Columns: ', __FILE__, __LINE__, __METHOD__, 10);

			return $retarr;
		}

		return FALSE;
	}

	/**
	 * @return mixed
	 */
	function getParsedData() {
		if ( isset($this->data['parsed_data']) ) {
			return $this->data['parsed_data'];
		}
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setParsedData( $value) {
		if ( $value != '' ) {
			$this->data['parsed_data'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	//Generates a "best fit" column map array.

	/**
	 * @return array|bool
	 */
	function generateColumnMap() {
		$raw_data_columns = $this->getRawDataColumns();
		Debug::Arr($raw_data_columns, 'Raw Data Columns:', __FILE__, __LINE__, __METHOD__, 10);

		$columns = Misc::trimSortPrefix( $this->getOptions('columns') );
		Debug::Arr($columns, 'Object Columns:', __FILE__, __LINE__, __METHOD__, 10);

		//unset($columns['middle_name']); //This often conflicts with Last Name, so ignore mapping it by default. But then it won't work even for an exact match.

		if ( is_array( $raw_data_columns ) AND is_array($columns) ) {
			//Loop through all raw_data_columns finding best matches.
			$matched_columns = array();
			foreach( $raw_data_columns as $raw_data_key => $raw_data_column ) {
				$matched_column_key = Misc::findClosestMatch( $raw_data_column, $columns, 60 );
				if ( $matched_column_key !== FALSE AND isset( $columns[ $matched_column_key ] ) ) {
					if ( in_array( $matched_column_key, $matched_columns) !== FALSE ) { //Once a file column is matched to a TimeTrex field, don't match it again.
						Debug::Text( 'Column already matched: ' . $raw_data_column, __FILE__, __LINE__, __METHOD__, 10 );
					} else {
						Debug::Text( 'Close match for: ' . $raw_data_column . ' Match: ' . $matched_column_key, __FILE__, __LINE__, __METHOD__, 10 );
						$matched_columns[ $raw_data_column ] = $matched_column_key;
					}
				} else {
					Debug::Text( 'No close match for: ' . $raw_data_column, __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
			unset($raw_data_column, $raw_data_key, $matched_column_key);
			$matched_columns = array_flip( $matched_columns );

			$retval = array();
			foreach( $columns as $column => $column_name ) {
				$retval[$column] = array(
										'import_column' => $column,
										'map_column_name' => ( isset($matched_columns[$column]) AND $matched_columns[$column] != '' ) ? $matched_columns[$column] : NULL,
										'default_value' => NULL,
										'parse_hint' => NULL,
										);
			}
			unset($column_name); //code standards

			if ( isset($retval) ) {
				Debug::Arr($retval, 'Generate Column Map:', __FILE__, __LINE__, __METHOD__, 10);
				return $retval;
			}

		}

		return FALSE;
	}

	//Takes a saved column map and tries to merge it with existing column data from the file.
	//Needs to account for manually added columns that don't exist in the file already.
	//Needs to account for less/more columns added to the file itself.
	/**
	 * @param $saved_column_map
	 * @return mixed
	 */
	function mergeColumnMap( $saved_column_map ) {
		return $saved_column_map;
	}

	/**
	 * @return mixed
	 */
	function getColumnMap() {
		if ( isset($this->data['column_map']) ) {
			return $this->data['column_map'];
		}
	}

	/**
	 * @param $import_map_arr
	 * @return bool
	 */
	function setColumnMap( $import_map_arr ) {
		//
		// Array(
		//			$column_name => array( 'map_column_name' => 'user_name', 'default_value' => 'blah', 'parse_hint' => 'm/d/y' ),
		//			$column_name => array( 'map_column_name' => 'user_name', 'default_value' => 'blah', 'parse_hint' => 'm/d/y' ),
		//		)
		//
		// This must support columns that may not exist in the actual system, so they can be converted to ones that do.
		$filtered_import_map = array();
		if ( is_array( $import_map_arr ) ) {
			foreach ( $import_map_arr as $import_column => $map_cols ) {
				if ( ( isset( $map_cols['map_column_name'] ) AND isset( $map_cols['default_value'] ) )
						AND ( $map_cols['map_column_name'] != '' OR $map_cols['default_value'] != '' ) ) {
					Debug::Text( 'Import Column: ' . $import_column . ' => ' . $map_cols['map_column_name'] . ' Default: ' . $map_cols['default_value'], __FILE__, __LINE__, __METHOD__, 10 );

					$filtered_import_map[ $import_column ] = array(
							'import_column'   => $import_column,
							'map_column_name' => $map_cols['map_column_name'],
							'default_value'   => $map_cols['default_value'],
							'parse_hint'      => $map_cols['parse_hint'],
					);

				} else {
					Debug::Text( 'Import Column: ' . $import_column . ' Skipping...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
		}

		if ( empty($filtered_import_map) == FALSE ) {
			//Debug::Arr($filtered_import_map, 'Filtered Import Map:', __FILE__, __LINE__, __METHOD__, 10);
			$this->data['column_map'] = $filtered_import_map;
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param null $key
	 * @return bool|mixed|null
	 */
	function getImportOptions( $key = NULL ) {
		if ( isset($this->data['import_options']) ) {
			if ( $key == '' ) {
				return $this->data['import_options'];
			} else {
				if ( isset($this->data['import_options'][$key]) ) {
					Debug::Text('Found specific import options key: '. $key .' returning: '. $this->data['import_options'][$key], __FILE__, __LINE__, __METHOD__, 10);
					return $this->data['import_options'][$key];
				} else {
					return NULL;
				}
			}
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setImportOptions( $value ) {
		if ( is_array($value) ) {
			$this->data['import_options'] = Misc::trimSortPrefix( $value );
			Debug::Arr($this->data['import_options'], 'Import Options: ', __FILE__, __LINE__, __METHOD__, 10);

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param $function_name
	 * @param $map_data
	 * @param null $raw_row
	 * @return mixed|string
	 */
	function callInputParseFunction( $function_name, $map_data, $raw_row = NULL ) {
		$full_function_name = 'parse_'.$function_name;

		$input = '';
//		if ( isset($map_data[$function_name]['map_column_name'])
//				AND isset($raw_row[$map_data[$function_name]['map_column_name']]) AND $raw_row[$map_data[$function_name]['map_column_name']] != '' ) {
//
//			//Make sure we check for proper UTF8 encoding and if its not remove the data so we don't cause a PGSQL invalid byte sequence error.
//			if ( function_exists('mb_check_encoding') AND mb_check_encoding( $raw_row[$map_data[$function_name]['map_column_name']], 'UTF-8' ) === TRUE ) {
//				$input = $raw_row[$map_data[$function_name]['map_column_name']];
//			} else {
//				Debug::Text('Bad UTF8 encoding!: '. $input, __FILE__, __LINE__, __METHOD__, 10);
//			}
//		}

		//Data is mapped in mapRowData() now, so we just need to handle parsing here.
		if ( isset($raw_row[$function_name]) ) {
			$input = $raw_row[$function_name];
		}

		$default_value = '';
		if ( isset($map_data[$function_name]['default_value']) ) {
			$default_value = $map_data[$function_name]['default_value'];
		}

		$parse_hint = '';
		if ( isset($map_data[$function_name]['parse_hint']) ) {
			$parse_hint = $map_data[$function_name]['parse_hint'];
		}

		if ( $input == '' AND $default_value != '' ) {
			$input = $default_value;
		}

		$input = trim($input); //This can affect things like Country/Province matching.
		if ( method_exists( $this, $full_function_name ) ) {
			$retval = call_user_func( array( $this, $full_function_name ), $input, $default_value, $parse_hint, $map_data, $raw_row );
			//Debug::Arr( $retval, 'Input: '. $input .' Parse Hint: '. $parse_hint .' Default Value: '. $default_value .' Retval: ', __FILE__, __LINE__, __METHOD__, 10);
			return $retval;
		} else {
			if ( $input == '' AND $default_value != '' ) {
				return $default_value;
			}
		}

		return $input;
	}


	/**
	 * @return bool
	 */
	function preProcess() {
		if ( method_exists( $this, '_preProcess' ) ) {
			return $this->_preProcess();
		}

		return TRUE;
	}

	/**
	 * @param $column_map
	 * @param $raw_row
	 * @return bool
	 */
	function mapRowData( $column_map, $raw_row ) {
		foreach( $column_map as $import_column => $import_data ) {
			//Debug::Arr($import_data, 'Import Data: Column: '. $import_column .' File Column Name: '. $import_data['map_column_name'], __FILE__, __LINE__, __METHOD__, 10);
			//Don't allow importing "id" columns.
			if ( strtolower($import_column) != 'id' AND $import_column !== 0 ) {
				if ( isset($column_map[$import_column]['map_column_name']) ) {
					if ( isset( $raw_row[ $column_map[$import_column]['map_column_name'] ] ) ) { //Don't check for != '' here, as that will prevent blank fields from being imported, like blank termination dates that need to be blanked out: AND $raw_row[$column_map[$import_column]['map_column_name']] != ''
						$input = '';
						//Make sure we check for proper UTF8 encoding and if its not remove the data so we don't cause a PGSQL invalid byte sequence error.
						if ( function_exists( 'mb_check_encoding' ) AND mb_check_encoding( $raw_row[ $column_map[ $import_column ]['map_column_name'] ], 'UTF-8' ) === TRUE ) {
							$input = $raw_row[ $column_map[ $import_column ]['map_column_name'] ];
						} else {
							Debug::Text( 'Bad UTF8 encoding!: ' . $input, __FILE__, __LINE__, __METHOD__, 10 );
						}

						$input = trim( $input ); //This can affect things like Country/Province matching.
						$retarr[ $import_column ] = $input;
					} elseif( isset( $column_map[$import_column]['default_value'] ) AND $column_map[$import_column]['default_value'] != '' )  { //If a simulated/non-existent column is added just to the mapping, and doesn't exist in the file, make sure we still pass that through so default values can be used and such.
						$retarr[ $import_column ] = '';
					}
				} else {
					Debug::Text('  Column not mapped: '. $import_column, __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		}

		//Debug::Arr($retarr, 'bRaw Row: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( isset($retarr) ) {
			return $retarr;
		}

		Debug::Text('ERROR: Unable to map row!', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	/**
	 * @param $row_number
	 * @param $raw_row
	 * @return mixed
	 */
	function preParseRow( $row_number, $raw_row ) {
		if ( method_exists( $this, '_preParseRow' ) ) {
			return $this->_preParseRow( $row_number, $raw_row );
		}

		return $raw_row;
	}

	/**
	 * @param $row_number
	 * @param $raw_row
	 * @return mixed
	 */
	function postParseRow( $row_number, $raw_row ) {

		if ( method_exists( $this, '_postParseRow' ) ) {
			$retval = $this->_postParseRow( $row_number, $raw_row );
		} else {
			$retval = $raw_row;
		}

		//Handle column aliases.
		$column_aliases = $this->getOptions('column_aliases');
		if ( is_array($column_aliases) ) {
			foreach( $column_aliases as $search => $replace ) {
				if ( isset($retval[$search]) ) {
					$retval[$replace] = $retval[$search];
					//unset($retval[$search]); //Don't unset old values, as the column might be used for validation or reporting back to the user.
				}
			}
		}

		return $retval;
	}

	//Parse data while applying any parse hints.
	//This converts the raw data into something that can be passed directly to the setObjectAsArray functions for this object.
	//Which may include converting one column into multiples and vice versa.
	/**
	 * @return bool
	 */
	function parseData() {
		$raw_data = $this->getRawData();
		$column_map = $this->getColumnMap();
		$parsed_data = array();

		//Debug::Arr($column_map, 'Column Map: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( !is_array($raw_data) ) {
			Debug::Text('Invalid raw data...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		//Debug::Arr($raw_data, 'Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($raw_data), NULL, TTi18n::getText('Parsing import data...') );

		$x = 0;
		foreach( $raw_data as $raw_row ) {
			//Map the data first, so all other functions see consistent data.
			//This is important for getUserIDByRowData(), as sometimes we need to know the user_id in the preParseRow() function for getDefaultData() functions.
			$raw_row = $this->mapRowData( $column_map, $raw_row );

			$parsed_data[$x] = $this->preParseRow( $x, $raw_row ); //This needs to run for each row so things like manual_ids can get updated automatically.
			//Debug::Arr($parsed_data[$x], 'Default Data: X: '. $x, __FILE__, __LINE__, __METHOD__, 10);

//			foreach( $column_map as $import_column => $import_data ) {
//				//Debug::Arr($import_data, 'Import Data X: '. $x .' Column: '. $import_column .' File Column Name: '. $import_data['map_column_name'], __FILE__, __LINE__, __METHOD__, 10);
//				//Don't allow importing "id" columns.
//				if ( strtolower($import_column) != 'id' AND $import_column !== 0 ) {
//					$parsed_data[$x][$import_column] = $this->callInputParseFunction( $import_column, $column_map, $raw_row );
//					//Debug::Arr($parsed_data[$x][$import_column], 'Import Column: '. $import_column .' Value: ', __FILE__, __LINE__, __METHOD__, 10);
//				} else {
//					//Don't allow importing "id" columns.
//					unset($parsed_data[$x][$import_data['map_column_name']]);
//				}
//
//				if ( $import_column != $import_data['map_column_name'] ) {
//					//Unset the original unmapped data so it doesn't conflict, especially if its an "id" column.
//					//Only if the two columns don't match though, as there was a bug that if someone tried to import column names that matched the TimeTrex
//					//names exactly, it would just unset them all.
//					unset($parsed_data[$x][$import_data['map_column_name']]);
//				}
//			}

			//each row needs to carry the overwrite flag
			if ( $this->getImportOptions('overwrite') ) {
				$parsed_data[$x]['overwrite'] = TRUE;
			}

			if ( is_array($raw_row) ) {
				foreach( $raw_row as $import_column => $import_data ) {
					//Debug::Arr($import_data, 'Import Data X: '. $x .' Column: '. $import_column .' File Column Name: '. $import_data['map_column_name'], __FILE__, __LINE__, __METHOD__, 10);
					$parsed_data[$x][$import_column] = $this->callInputParseFunction( $import_column, $column_map, $raw_row );
					//Debug::Arr($parsed_data[$x][$import_column], 'Import Column: '. $import_column .' Value: ', __FILE__, __LINE__, __METHOD__, 10);
				}

				$post_parsed_row = $this->postParseRow( $x, $parsed_data[$x] );
				if ( is_array( $post_parsed_row ) ) {
					$parsed_data[$x] = $post_parsed_row; //This needs to run for each row so things like manual_ids can get updated automatically.
				} else {
					Debug::Text('  Skipping blank or row that failed postParseRow(): '. $x, __FILE__, __LINE__, __METHOD__, 10);
					unset($parsed_data[$x]);
				}
			}

			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $x );

			$x++;
		}

		//Don't stop the current progress bar, let it continue into the process/_import function.
		//$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

		Debug::Arr($parsed_data, 'Parsed Data: ', __FILE__, __LINE__, __METHOD__, 10);
		return $this->setParsedData( $parsed_data );
	}

	//This function can't be named "import" as it will be called during __construct() then.

	/**
	 * @param bool $validate_only
	 * @return bool
	 */
	function process( $validate_only = FALSE ) {
		//Because parse functions can create additional records (like groups, titles, branches)
		//we need to wrap those in a transaction so they can be rolled back on validate_only calls.
		//However non-validate_only calls can't be in any transaction, otherwise skipped records will get rolled back.
		$f = TTnew('UserFactory');

		if ( $validate_only == TRUE ) {
			$f->StartTransaction();
		}

		if ( $this->parseData() == TRUE ) {
			//Call sub-class import function to handle all the processing.
			//This function can call the API*()->set*(), or it can handle creating the objects on its own in advanced cases.
			//FIXME: Should this be wrapped in one big transaction, so its an all or nothing import, or allow the option for this?
			$this->preProcess(); //PreProcess data as a whole before importing.
			$retval = $this->_import( $validate_only );

			if ( $validate_only == FALSE ) {
				$lf = TTnew('LogFactory');
				$table_options = $lf->getOptions('table_name');

				$log_description = TTi18n::getText('Imported').' ';
				if ( isset($table_options[$this->getObject()->getMainClassObject()->getTable()]) ) {
					$log_description .= $table_options[$this->getObject()->getMainClassObject()->getTable()];
				} else {
					$log_description .= TTi18n::getText('Unknown');
				}
				$log_description .= ' '. TTi18n::getText('Records');

				if ( isset($retval['api_details']) AND isset($retval['api_details']['record_details']) ) {
					$log_description .= ' - '. TTi18n::getText('Total').': '. $retval['api_details']['record_details']['total'] .' '. TTi18n::getText('Valid').': '. $retval['api_details']['record_details']['valid'] .' '. TTi18n::getText('Invalid').': '. $retval['api_details']['record_details']['invalid'];
				}
				TTLog::addEntry( $this->user_id, 500, $log_description, $this->user_id, 'users' );
				$this->cleanStoragePath();
			}

			if ( $validate_only == TRUE ) {
				$f->FailTransaction();
				$f->CommitTransaction();
			}

			return $retval;
		}

		if ( $validate_only == TRUE ) {
			$f->FailTransaction();
			$f->CommitTransaction();
		}

		return FALSE;
	}

	//
	// File upload functions.
	//
	/**
	 * @return array|bool
	 */
	function getLocalFileData() {
		$file_name = $this->getStoragePath().$this->getLocalFileName();
		if ( file_exists($file_name) ) {
			return array(
							'size' => filesize( $file_name )
						);
		}

		return FALSE;
	}

	/**
	 * @return mixed
	 */
	function getRemoteFileName() {
		if ( isset($this->data['remote_file_name']) ) {
			return $this->data['remote_file_name'];
		}
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRemoteFileName( $value) {
		if ( $value != '' ) {
			$this->data['remote_file_name'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return string
	 */
	function getLocalFileName() {
		$retval = md5( $this->company_id.$this->user_id );
		Debug::Text('Local File Name: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	/**
	 * @param string $company_id UUID
	 * @return bool
	 */
	function cleanStoragePath( $company_id = NULL) {
		if ( $company_id == '' ) {
			$company_id = $this->company_id;
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		$dir = $this->getStoragePath( $company_id ) . DIRECTORY_SEPARATOR;

		if ( $dir != '' ) {
			//Delete tmp files.
			foreach(glob($dir.'*') as $filename) {
				unlink($filename);
				Misc::deleteEmptyDirectory( dirname( $filename ), 0 ); //Recurse to $user_id parent level and remove empty directories.
			}
		}

		return TRUE;
	}

	/**
	 * @param string $company_id UUID
	 * @return bool|string
	 */
	function getStoragePath( $company_id = NULL ) {
		if ( $company_id == '' ) {
			$company_id = $this->company_id;
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		global $config_vars;
		return $config_vars['cache']['dir'] . DIRECTORY_SEPARATOR .'import'. DIRECTORY_SEPARATOR . $company_id . DIRECTORY_SEPARATOR;
	}

	/**
	 * @return bool
	 */
	function renameLocalFile() {
		$src_file = $this->getStoragePath().$this->getRemoteFileName();
		$dst_file = $this->getStoragePath().$this->getLocalFileName();
		Debug::Text('Src File: '. $src_file .' Dst File: '. $dst_file, __FILE__, __LINE__, __METHOD__, 10);
		if ( file_exists( $src_file ) AND is_file($src_file) ) {
			$this->deleteLocalFile(); //Delete the dst_file before renaming, just in case.
			return rename( $src_file, $dst_file );
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function deleteLocalFile() {
		$file = $this->getStoragePath().$this->getLocalFileName();

		if ( file_exists($file) ) {
			Debug::Text('Deleting Local File: '. $file, __FILE__, __LINE__, __METHOD__, 10);
			@unlink($file);
		}

		return TRUE;
	}

	//
	// Generic parser functions.
	//
	/**
	 * @param $input
	 * @param $options
	 * @param int $match_percent
	 * @return array|bool|mixed
	 */
	function findClosestMatch( $input, $options, $match_percent = 50 ) {
		//We used to check for the option KEY, but that causes problems if the job code/job name are numeric values
		//that happen to match the record ID in the database. Use this as a fallback method instead perhaps?
		//Also consider things like COUNTRY/PROVINCE matches that are not numeric.
		//if ( isset($options[strtoupper($input)]) ) {
		//	return $input;
		//} else {

		if ( $this->getImportOptions('fuzzy_match') == TRUE ) {
			$retval = Misc::findClosestMatch( $input, $options, $match_percent );
			if ( $retval !== FALSE ) {
				return $retval;
			}
		} else {
			$retval = array_search( strtolower($input), array_map('strtolower', (array)$options) );
			if ( $retval !== FALSE ) {
				return $retval;
			}
		}

		return FALSE; //So we know if no match was made, rather than return $input
	}

	//Used by sub-classes to get general users while importing data.

	/**
	 * @param string $user_id UUID
	 * @return bool
	 */
	function getUserObject( $user_id ) {
		if ( $user_id != '' ) {
			$ulf = TTnew( 'UserListFactory' );
			$ulf->getByCompanyIdAndID( $this->company_id, $user_id );
			if ( $ulf->getRecordCount() == 1 ) {
				return $ulf->getCurrent();
			}
		}
		return FALSE;
	}

	/**
	 * @return array|null
	 */
	function getUserIdentificationColumns() {
		$uf = TTNew('UserFactory');
		$retval = Misc::arrayIntersectByKey( array('user_name', 'employee_number', 'sin'), Misc::trimSortPrefix( $uf->getOptions('columns') ) );

		return $retval;
	}

	/**
	 * @param $raw_row
	 * @return bool
	 */
	function getUserIDByRowData( $raw_row ) {
		//NOTE: Keep in mind that employee numbers can be duplicate based on status (ACTIVE vs TERMINATED), so
		//if there are ever duplicate employee numbers, the import process won't be able to differentiate between them, and the
		//update process will not work.
		//Actually, the above is no longer the case.
		//  **Make sure data is mapped before passed into this function, otherwise it will fail completely. We added mapRowData() to handle this and it should be called before preParseData() is now.
		//  If they want to import/update the user_name, they can't, since its the 1st priority and used to find the employee record, but it wouldn't exist as they would be importing the new value.
		//    So maybe we could use the order specified in the mapping to define the priority to be used during searches?

		//Cache the $this->search_column_priority in the object so we aren't doing this work on every row.
		if ( $this->search_column_priority == NULL ) {
			//Possible search columns, in priority order.
			$search_columns = array('user_name', 'sin', 'employee_number');

			//Loop over column map to find any $search_columns in the order specified in the map "parse hint" column, ie: 1, 2, 3
			$column_map = Sort::arrayMultiSort( $this->getColumnMap(), array('parse_hint' => SORT_ASC) );
			foreach ( $column_map as $key => $map_data ) {
				if ( in_array( (string)$map_data['import_column'], $search_columns ) ) { //Cast to string to avoid (int)0 matching in every case.
					$this->search_column_priority[] = $map_data['import_column'];
				}
			}

			//If no search columns are found in the map, just use defaults.
			if ( !is_array( $this->search_column_priority ) ) {
				$this->search_column_priority = $search_columns;
			}

			Debug::Arr($this->search_column_priority, 'Search Column Priority Arr: ', __FILE__, __LINE__, __METHOD__, 10);
		}

		$filter_data = NULL;
		foreach( $this->search_column_priority as $search_column ) {
			if ( isset($raw_row[$search_column]) AND $raw_row[$search_column] != '' ) {
				$value = $raw_row[$search_column];
				if ( $search_column == 'user_name' ) {
					$value = '"'. $value .'"'; //When matching username, make sure its an exact match rather than a fuzzy match, otherwise its easy to match multiple records, causing the import to fail. ie: "john.doe" matches "john.does"
				}

				$filter_data = array( $search_column => $value );
				Debug::Text('Searching for existing record based on Column: '. $search_column .' Value: '. $raw_row[$search_column], __FILE__, __LINE__, __METHOD__, 10);
				break;
			}
		}

		if ( !isset($filter_data) ) {
			Debug::Text('No suitable columns for identifying the employee were specified... ', __FILE__, __LINE__, __METHOD__, 10);
		}

		if ( isset($filter_data) ) {
			//Cache this lookup to help speed up importing of mass data. This is about a 1000x speedup for large imports.
			$cache_id = md5( $this->company_id.serialize( $filter_data ) );
			if ( isset($this->user_id_cache[$cache_id]) ) {
				Debug::Text('Found existing cached record ID: '. $this->user_id_cache[$cache_id], __FILE__, __LINE__, __METHOD__, 10);
				return $this->user_id_cache[$cache_id];
			} else {
				$ulf = TTnew( 'UserListFactory' );
				$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->company_id, $filter_data );
				if ( $ulf->getRecordCount() == 1 ) {
					$tmp_user_obj = $ulf->getCurrent();
					Debug::Text('Found existing record ID: '. $tmp_user_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);

					//return $tmp_user_obj->getID();
					$this->user_id_cache[$cache_id] = $tmp_user_obj->getID();
					return $this->user_id_cache[$cache_id];
				} elseif ( $ulf->getRecordCount() > 0 ) {
					Debug::Text('Found more than one record, unable to match...', __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		}

		Debug::Text('NO employee found!', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	/**
	 * @param $column
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @param null $raw_row
	 * @return mixed|string
	 */
	function _parse_name( $column, $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		if ( $parse_hint == '' ) {
			$parse_hint = 'first_name';
		}

		$retval = preg_replace('!\s+!', ' ', $input); //Replace any double or more spaces with single to avoid problems with parsing middle/first names below.
		switch ( $parse_hint ) {
			case 'first_name':
			case 'last_name':
			case 'middle_name':
				$retval = $input;
				break;
			case 'first_last_name':
				if ( $column == 'first_name' ) {
					$offset = 0;
				} else {
					$offset = 1;
				}
				$split_full_name = explode( ' ', $input );
				if ( isset($split_full_name[$offset]) ) {
					$retval = $split_full_name[$offset];
				}
				break;
			case 'last_first_name':
				if ( $column == 'first_name' ) {
					$offset = 1;
				} else {
					$offset = 0;
				}
				$split_full_name = explode( ',', $input );
				if ( isset($split_full_name[$offset]) ) {
					$retval = $split_full_name[$offset];
				}
				break;
			case 'last_first_middle_name':
				if ( $column == 'first_name' OR $column == 'middle_name' ) {
					$offset = 1;
				} else {
					$offset = 0;
				}
				$split_full_name = explode( ',', trim( $input ) );
				if ( isset($split_full_name[$offset]) ) {
					$retval = trim( $split_full_name[$offset] );
					if ( $column == 'first_name' OR $column == 'middle_name' ) {
						if ( $column == 'first_name' ) {
							$offset = 0;
						} else {
							$offset = 1;
						}

						$split_retval = explode( ' ', $retval);
						if ( isset($split_retval[$offset]) ) {
							$retval = trim( $split_retval[$offset] );
						} else {
							$retval = '';
						}
					}
				}
				break;
			default:
				$retval = $input;
				break;
		}

		Debug::Text('Column: '. $column .' Parse Hint: '. $parse_hint .' Retval: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @param null $raw_row
	 * @return mixed|string
	 */
	function parse_first_name( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		return $this->_parse_name( 'first_name', $input, $default_value, $parse_hint, $raw_row );
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @param null $raw_row
	 * @return mixed|string
	 */
	function parse_middle_name( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		return $this->_parse_name( 'middle_name', $input, $default_value, $parse_hint, $raw_row );
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @param null $raw_row
	 * @return mixed|string
	 */
	function parse_last_name( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		return $this->_parse_name( 'last_name', $input, $default_value, $parse_hint, $raw_row );
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @param null $raw_row
	 * @return string
	 */
	function parse_postal_code( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		//Excel likes to strip leading zeros from fields, so take 4 digit US zip codes and prepend the zero.
		if ( is_numeric( $input) AND strlen( $input ) <= 4 AND strlen( $input ) >= 1 ) {
			return str_pad( $input, 5, 0, STR_PAD_LEFT );
		}

		return $input;
	}


	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @param null $raw_row
	 * @return mixed
	 */
	function parse_phone( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		$input = str_replace( array('/'), '-', $input);

		return $input;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @param null $raw_row
	 * @return mixed
	 */
	function parse_work_phone( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		return $this->parse_phone( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL );
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @param null $raw_row
	 * @return mixed
	 */
	function parse_home_phone( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		return $this->parse_phone( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL );
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @param null $raw_row
	 * @return mixed
	 */
	function parse_fax_phone( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		return $this->parse_phone( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL );
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return false|int
	 */
	function parse_date( $input, $default_value = NULL, $parse_hint = NULL ) {
		if ( $input != '' ) { //Don't try to parse a blank date, this helps in cases where hire/termination dates are imported blank.
			if ( isset($parse_hint) AND $parse_hint != '' ) {
				TTDate::setDateFormat( $parse_hint );
				return TTDate::getMiddleDayEpoch( TTDate::parseDateTime( $input ) );
			} else {
				return TTDate::getMiddleDayEpoch( TTDate::strtotime( $input ) );
			}
		}

		return $input;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return bool|float|int|number|string
	 */
	function parse_time_unit( $input, $default_value = NULL, $parse_hint = NULL ) {
		if ( $input != '' ) { //Don't try to parse a blank date, this helps in cases where hire/termination dates are imported blank.
			if ( isset($parse_hint) AND $parse_hint != '' ) {
				TTDate::setTimeUnitFormat( $parse_hint );
				return TTDate::parseTimeUnit( $input );
			} else {
				return TTDate::parseTimeUnit( $input );
			}
		}

		return $input;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @param null $raw_row
	 * @return int
	 */
	function parse_sex( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		if ( strtolower( $input ) == 'f'
				OR strtolower( $input ) == 'female' ) {
			$retval = 20;
		} elseif ( strtolower( $input ) == 'm'
				OR strtolower( $input ) == 'male' ) {
			$retval = 10;
		} else {
			$retval = 5; //Unspecified
		}

		return $retval;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|mixed
	 */
	function parse_country( $input, $default_value = NULL, $parse_hint = NULL ) {
		$cf = TTnew('CompanyFactory');
		$options = $cf->getOptions( 'country' );

		if ( isset($options[strtoupper($input)]) ) {
			return $input;
		} else {
			if ( $this->getImportOptions('fuzzy_match') == TRUE ) {
				return $this->findClosestMatch( $input, $options, 50 );
			} else {
				return array_search( strtolower($input), array_map('strtolower', $options) );
			}
		}
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @param null $map_data
	 * @param null $raw_row
	 * @return array|bool|mixed|string
	 */
	function parse_province( $input, $default_value = NULL, $parse_hint = NULL, $map_data = NULL, $raw_row = NULL ) {
		$country = $this->callInputParseFunction( 'country', $map_data, $raw_row );
		Debug::Text('Input: '. $input .' Country: '. $country, __FILE__, __LINE__, __METHOD__, 10);

		//If country is not mapped during the import process (which it often isn't), try to use the user default data country instead to help avoid majority of Invalid Province/State errors.
		//  UserDefaults can't be passed through to here in the preParseRow() unless the country field is actually mapped (otherwise it would always cause an update of fields the user wasn't expect),
		//  in which case it would avoid this issue to begin with too.
		if ( $country == '' AND is_object( $this->getCompanyObject() ) AND is_object( $this->getCompanyObject()->getUserDefaultObject() ) ) {
			$country = $this->getCompanyObject()->getUserDefaultObject()->getCountry();
			Debug::Text(' Country not mapped or defined, defaulting to Company Country: '. $country, __FILE__, __LINE__, __METHOD__, 10);
		}

		$options = array();
		if ( $country != '' ) {
			$cf = TTnew('CompanyFactory');
			$options = (array)$cf->getOptions( 'province', $country );
		}

		if ( !isset($options[strtoupper($input)]) ) {
			if ( $this->getImportOptions('fuzzy_match') == TRUE ) {
				$retval = Misc::findClosestMatch( $input, $options );
				if ( $retval !== FALSE ) {
					return $retval;
				} else {
					$input = '00';
				}
			} else {
				$retval = array_search( strtolower($input), array_map('strtolower', $options) );
				if ( $retval !== FALSE ) {
					return $retval;
				} else {
					$input = '00';
				}
			}
		}

		return $input;
	}

	/**
	 * @return bool
	 */
	function getBranchOptions() {
		$this->branch_options = $this->branch_manual_id_options = array();
		$blf = TTNew('BranchListFactory');
		$blf->getByCompanyId( $this->company_id );
		if ( $blf->getRecordCount() > 0 ) {
			foreach( $blf as $b_obj ) {
				$this->branch_options[$b_obj->getId()] = $b_obj->getName();
				$this->branch_manual_id_options[$b_obj->getId()] = $b_obj->getManualId();
			}
		}
		unset($blf, $b_obj);

		return TRUE;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|int|mixed
	 */
	function parse_branch( $input, $default_value = NULL, $parse_hint = NULL ) {
		if ( trim($input) == '' ) {
			return TTUUID::getZeroID(); //No branch
		}

		if ( trim($input) == '-1' ) {
			return TTUUID::getNotExistID(); //User default branch
		}

		if ( !is_array( $this->branch_options ) ) {
			$this->getBranchOptions();
		}

		if ( is_numeric( $input ) AND strtolower($parse_hint) == 'manual_id' ) {
			//Find based on manual_id/code.
			$retval = $this->findClosestMatch( $input, $this->branch_manual_id_options, 90 );
		} else {
			$retval = $this->findClosestMatch( $input, $this->branch_options );
		}

		if ( $retval === FALSE ) {
			$retval = TTUUID::getNotExistID( 1 ); //Make sure this fails. ZeroID is allowed and NotExistID gets converted to default job.
		}

		return $retval;
	}

	/**
	 * @return bool
	 */
	function getDepartmentOptions() {
		//Get departments
		$this->department_options = $this->department_manual_id_options = array();
		$dlf = TTNew('DepartmentListFactory');
		$dlf->getByCompanyId( $this->company_id );
		if ( $dlf->getRecordCount() > 0 ) {
			foreach( $dlf as $d_obj ) {
				$this->department_options[$d_obj->getId()] = $d_obj->getName();
				$this->department_manual_id_options[$d_obj->getId()] = $d_obj->getManualId();
			}
		}
		unset($dlf, $d_obj);

		return TRUE;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|int|mixed
	 */
	function parse_department( $input, $default_value = NULL, $parse_hint = NULL ) {
		if ( trim($input) == '' ) {
			return TTUUID::getZeroID(); //No department
		}

		if ( trim($input) == '-1' ) {
			return TTUUID::getNotExistID(); //User default department
		}

		if ( !is_array( $this->department_options ) ) {
			$this->getDepartmentOptions();
		}

		//Always fall back to searching by name unless we know for sure its by manual_id
		if ( is_numeric( $input ) AND strtolower($parse_hint) == 'manual_id' ) {
			//Find based on manual_id/code.
			$retval = $this->findClosestMatch( $input, $this->department_manual_id_options, 90 );
		} else {
			$retval = $this->findClosestMatch( $input, $this->department_options );
		}

		if ( $retval === FALSE ) {
			$retval = TTUUID::getNotExistID( 1 ); //Make sure this fails. ZeroID is allowed and NotExistID gets converted to default job.
		}

		return $retval;
	}

	/**
	 * @return bool
	 */
	function getJobOptions() {
		//Get jobs
		$this->job_options = $this->job_manual_id_options = array();
		$dlf = TTNew('JobListFactory');
		$dlf->getByCompanyId( $this->company_id );
		if ( $dlf->getRecordCount() > 0 ) {
			foreach( $dlf as $d_obj ) {
				$this->job_options[$d_obj->getId()] = $d_obj->getName();
				$this->job_manual_id_options[$d_obj->getId()] = $d_obj->getManualId();
			}
		}
		unset($dlf, $d_obj);

		return TRUE;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|int|mixed
	 */
	function parse_job( $input, $default_value = NULL, $parse_hint = NULL ) {
		if ( trim($input) == '' ) {
			return TTUUID::getZeroID(); //No job
		}

		if ( trim($input) == '-1' ) {
			return TTUUID::getNotExistID(); //User default job
		}

		if ( !is_array( $this->job_options ) ) {
			$this->getJobOptions();
		}

		if ( is_numeric( $input ) AND strtolower($parse_hint) == 'manual_id' ) {
			//Find based on manual_id/code.
			$retval = $this->findClosestMatch( $input, $this->job_manual_id_options, 90 );
		} else {
			$retval = $this->findClosestMatch( $input, $this->job_options );
		}

		if ( $retval === FALSE ) {
			$retval = TTUUID::getNotExistID( 1 ); //Make sure this fails. ZeroID is allowed and NotExistID gets converted to default job.
		}

		return $retval;
	}

	/**
	 * @return bool
	 */
	function getJobItemOptions() {
		//Get job_items
		$this->job_item_options = $this->job_item_manual_id_options = array();
		$dlf = TTNew('JobItemListFactory');
		$dlf->getByCompanyId( $this->company_id );
		if ( $dlf->getRecordCount() > 0 ) {
			foreach( $dlf as $d_obj ) {
				$this->job_item_options[$d_obj->getId()] = $d_obj->getName();
				$this->job_item_manual_id_options[$d_obj->getId()] = $d_obj->getManualId();
			}
		}
		unset($dlf, $d_obj);

		return TRUE;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|int|mixed
	 */
	function parse_job_item( $input, $default_value = NULL, $parse_hint = NULL ) {
		if ( trim($input) == '' ) {
			return TTUUID::getZeroID(); //No job_item
		}

		if ( trim($input) == '-1' ) {
			return TTUUID::getNotExistID(); //User default job_item
		}

		if ( !is_array( $this->job_item_options ) ) {
			$this->getJobItemOptions();
		}

		if ( is_numeric( $input ) AND strtolower($parse_hint) == 'manual_id' ) {
			//Find based on manual_id/code.
			$retval = $this->findClosestMatch( $input, $this->job_item_manual_id_options, 90 );
		} else {
			$retval = $this->findClosestMatch( $input, $this->job_item_options );
		}

		if ( $retval === FALSE ) {
			$retval = TTUUID::getNotExistID( 1 ); //Make sure this fails. ZeroID is allowed and NotExistID gets converted to default job.
		}

		return $retval;
	}
}
?>
