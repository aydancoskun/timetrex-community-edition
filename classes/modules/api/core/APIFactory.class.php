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
 * @package API\Core
 */
abstract class APIFactory {

	public $data = [];

	protected $main_class_obj = null;

	protected $api_message_id = null;

	protected $pager_obj = null;

	protected $current_company = null;
	protected $current_user = null;
	protected $current_user_prefs = null;
	protected $permission = null;

	protected $progress_bar_obj = null;

	/**
	 * APIFactory constructor.
	 */
	function __construct() {
		global $current_company, $current_user, $current_user_prefs;

		$this->current_company = $current_company;
		$this->current_user = $current_user;
		$this->current_user_prefs = $current_user_prefs;

		$this->permission = new Permission();

		return true;
	}

	/**
	 * @return int
	 */
	function getProtocolVersion() {
		if ( isset( $_GET['v'] ) && $_GET['v'] != '' ) {
			return (int)$_GET['v'];     //1=Initial, 2=Always return detailed
		}

		//NOTE: Mobile app currently requires API v1, but older versions of the app don't send the protocol version. So we can't default to v2 without breaking the app.
		return 1;
	}

	/**
	 * Returns the API messageID for each individual call.
	 * @return bool|null
	 */
	function getAPIMessageID() {
		if ( $this->api_message_id != null ) {
			return $this->api_message_id;
		}

		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setAPIMessageID( $id ) {
		if ( $id != '' ) {
			global $api_message_id; //Make this global so Debug() class can reference it on Shutdown()
			$this->api_message_id = $api_message_id = $id;

			return true;
		}

		return false;
	}

	/**
	 * @return bool|CompanyFactory
	 */
	function getCurrentCompanyObject() {
		if ( is_object( $this->current_company ) ) {
			return $this->current_company;
		}

		return false;
	}

	/**
	 * @return bool|UserFactory
	 */
	function getCurrentUserObject() {
		if ( is_object( $this->current_user ) ) {
			return $this->current_user;
		}

		return false;
	}

	/**
	 * @return bool|UserPreferenceFactory
	 */
	function getCurrentUserPreferenceObject() {
		if ( is_object( $this->current_user_prefs ) ) {
			return $this->current_user_prefs;
		}

		return false;
	}

	/**
	 * @return bool|null|Permission
	 */
	function getPermissionObject() {
		if ( is_object( $this->permission ) ) {
			return $this->permission;
		}

		return false;
	}

	/**
	 * @return null|ProgressBar
	 */
	function getProgressBarObject() {
		if ( !is_object( $this->progress_bar_obj ) ) {
			$this->progress_bar_obj = new ProgressBar();
		}

		return $this->progress_bar_obj;
	}

	/**
	 * @param object $lf
	 * @return bool
	 */
	function setPagerObject( $lf ) {
		if ( is_object( $lf ) ) {
			$this->pager_obj = new Pager( $lf );
		}

		return true;
	}

	/**
	 * @return array|bool
	 */
	function getPagerData() {
		if ( is_object( $this->pager_obj ) ) {
			return $this->pager_obj->getPageVariables();
		}

		return false;
	}

	/**
	 * Allow storing the main class object persistently in memory, so we can build up other variables to help out things like getOptions()
	 * Mainly used for the APIReport class.
	 * @param object $obj
	 * @return bool
	 */
	function setMainClassObject( $obj ) {
		if ( is_object( $obj ) ) {
			$this->main_class_obj = $obj;

			return true;
		}

		return false;
	}

	/**
	 * @return string
	 */
	function getMainClassObject() {
		if ( !is_object( $this->main_class_obj ) ) {
			$this->main_class_obj = new $this->main_class;

			return $this->main_class_obj;
		} else {
			return $this->main_class_obj;
		}
	}

	/**
	 * @param array $data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function initializeFilterAndPager( $data, $disable_paging = false ) {
		//If $data is not an array, it will trigger PHP errors, so force it that way and report an error so we can troubleshoot if needed.
		//This will avoid the PHP fatal errors that look like the below, but it doesn't actually fix the root cause, which is currently unknown.
		//		DEBUG [L0228] [00014ms] Array: [Function](): Arguments: (Size: 114)
		//		array(4) {
		//					["POST_/api/json/api_php?Class"]=> string(18) "APIUserGenericData"
		//					["Method"]=> string(18) "getUserGenericData"
		//					["v"]=> string(1) "2"
		//					["MessageID"]=> string(26) "5dd90933-f97c-9001-9efe-e2"
		//		}
		//		DEBUG [L0139] [00030ms] Array: Debug::ErrorHandler(): Raw POST Request:
		//		string(114) "POST /api/json/api.php?Class=APIUserGenericData&Method=getUserGenericData&v=2&MessageID=5dd90933-f97c-9001-9efe-e2"
		if ( is_array( $data ) == false ) {
			Debug::Arr( $data, 'ERROR: Input data is not an array: ', __FILE__, __LINE__, __METHOD__, 10 );
			$data = [];
		}

		//Preset values for LF search function.
		$data = Misc::preSetArrayValues( $data, [ 'filter_data', 'filter_columns', 'filter_items_per_page', 'filter_page', 'filter_sort' ], null );

		if ( $disable_paging == false && (int)$data['filter_items_per_page'] <= 0 ) { //Used to check $data['filter_items_per_page'] === NULL
			$data['filter_items_per_page'] = $this->getCurrentUserPreferenceObject()->getItemsPerPage();
		}

		if ( $disable_paging == true ) {
			$data['filter_items_per_page'] = $data['filter_page'] = false;
		}

		//Debug::Arr($data, 'Getting Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return $data;
	}

	/**
	 * In cases where data can be displayed in just a list_view (dropdown boxes), ie: branch, department, job, task in In/Out punch view
	 * restrict the dropdown box to just a subset of columns, so not all data is shown.
	 * @param array $filter_columns
	 * @param array $allowed_columns
	 * @return array|null
	 */
	function handlePermissionFilterColumns( $filter_columns, $allowed_columns ) {
		//Always allow these columns to be returned.
		$allowed_columns['id'] = true;
		$allowed_columns['is_owner'] = true;
		$allowed_columns['is_child'] = true;

		if ( is_array( $filter_columns ) ) {
			$retarr = Misc::arrayIntersectByKey( $allowed_columns, $filter_columns );
		} else {
			$retarr = $allowed_columns;
		}

		//If no valid columns are being returned, revert back to allowed columns.
		//Never return *NULL* or a blank array from here, as that will allow all columns to be displayed.
		if ( !is_array( $retarr ) ) {
			//Return all allowed columns
			$retarr = $allowed_columns;
		}

		return $retarr;
	}

	/**
	 * @param array $data
	 * @return mixed
	 */
	function convertToSingleRecord( $data ) {
		if ( isset( $data[0] ) && !isset( $data[1] ) ) {
			return $data[0];
		} else {
			return $data;
		}
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function convertToMultipleRecords( $data ) {
		//if ( isset($data[0]) AND is_array($data[0]) ) {
		//Better way to detect if $data has numeric or string keys, which works across sparse arrays that could come from importing. ie: 3 => array(), 6 => array(), ...
		//  Array indexes can only be integer or string, so  (string)"8" can never happen as it would always be (int)8
		if ( count( array_filter( array_keys( $data ), 'is_string' ) ) == 0 ) {
			$retarr = [
				//'data' => $data,
				//'total_records' => count($data)
				//Switch to an array that is compatible with list() rather than extract() as it allows IDEs to better inspect code.
				$data,
				count( $data ),
			];
		} else {
			$retarr = [
				//'data' => array( 0 => $data ),
				//'total_records' => 1
				//Switch to an array that is compatible with list() rather than extract() as it allows IDEs to better inspect code.
				[ 0 => $data ],
				1,
			];
		}

		//Debug::Arr($retarr, 'Array: ', __FILE__, __LINE__, __METHOD__, 10);

		return $retarr;
	}

	/**
	 * downloaded a result_set as a csv.
	 * @param string $format
	 * @param string $file_name
	 * @param array $result
	 * @param array $filter_columns
	 * @return array|bool
	 */
	function exportRecords( $format, $file_name, $result, $filter_columns ) {
		if ( isset( $result[0] ) && is_array( $result[0] ) && is_array( $filter_columns ) && count( $filter_columns ) > 0 ) {
			$columns = Misc::arrayIntersectByKey( array_keys( $filter_columns ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );

			$file_extension = $format;
			$mime_type = 'application/' . $format;
			$output = '';

			if ( $format == 'csv' ) {
				$output = Misc::Array2CSV( $result, $columns, false );
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );
			if ( $output !== false ) {
				Misc::APIFileDownload( $file_name . '.' . $file_extension, $mime_type, $output );
			} else {
				return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'ERROR: No data to export...' ) );
			}
		}

		return $this->returnHandler( true ); //No records returned.
	}

	/**
	 * @return string
	 */
	function getNextInsertID() {
		return $this->getMainClassObject()->getNextInsertId();
	}

	/**
	 * Pass-thru to Factory class.
	 * @param $transaction_function
	 * @param int $retry_max_attempts
	 * @param int $retry_sleep
	 * @return mixed
	 */
	function RetryTransaction( $transaction_function, $retry_max_attempts = 4, $retry_sleep = 1 ) {
		return $this->getMainClassObject()->RetryTransaction( $transaction_function, $retry_max_attempts, $retry_sleep );
	}

	/**
	 * @return array
	 */
	function getPermissionChildren() {
		return $this->getPermissionObject()->getPermissionHierarchyChildren( $this->getCurrentCompanyObject()->getId(), $this->getCurrentUserObject()->getId() );
		/*
		$hlf = TTnew( 'HierarchyListFactory' );
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $this->getCurrentCompanyObject()->getId(), $this->getCurrentUserObject()->getId(), 100 );

		Debug::Arr($permission_children_ids, 'Permission Child IDs: ', __FILE__, __LINE__, __METHOD__, 10);

		return $permission_children_ids;
		*/
	}

	/**
	 * Controls returning information to client in a standard format.
	 * FIXME: Need to return the original request (with any modified values due to restrictions/validation issues)
	 *        Also need to return paging data variables here too, as JSON can't make multiple calls.
	 *        In order to do this we need to always return a special data structure that includes this information.
	 *        static function returnHandler( $retval = TRUE, $args = array( 'code' => FALSE, 'description' => FALSE, 'details' = FALSE, 'validator_stats' => FALSE, 'user_generic_status_batch_id' => FALSE ) ) {
	 *        The above will require too many changes, just add two more variables at the end, as it will only really be used by API->get*() functions.
	 * FIXME: Use a requestHandler() to handle all input requests, so we can parse out things like validate_only, ignore_warning (for user acknowledgable warnings) and handling all parameter parsing in a central place.
	 *        static function returnHandler( $retval = TRUE, $code = FALSE, $description = FALSE, $details = FALSE, $validator_stats = FALSE, $user_generic_status_batch_id = FALSE, $request = FALSE, $pager = FALSE ) {
	 * @param bool $retval
	 * @param bool $code
	 * @param bool $description
	 * @param bool $details
	 * @param bool $validator_stats
	 * @param bool $user_generic_status_batch_id
	 * @param bool $request_data
	 * @return array|bool
	 */
	function returnHandler( $retval = true, $code = false, $description = false, $details = false, $validator_stats = false, $user_generic_status_batch_id = false, $request_data = false ) {
		if ( $this->getProtocolVersion() == 1 ) {
			if ( $retval === false || ( $retval === true && $code !== false ) || ( $user_generic_status_batch_id !== false ) ) {
				if ( $retval === false ) {
					if ( $code == '' ) {
						$code = 'GENERAL';
					}
					if ( $description == '' ) {
						$description = 'Insufficient data to carry out action';
					}
				} else if ( $retval === true ) {
					if ( $code == '' ) {
						$code = 'SUCCESS';
					}
				}

				$validator_stats = Misc::preSetArrayValues( $validator_stats, [ 'total_records', 'valid_records', 'invalids_records' ], 0 );

				$retarr = [
						'api_retval'  => $retval,
						'api_details' => [
								'code'                         => $code,
								'description'                  => $description,
								'record_details'               => [
										'total'   => $validator_stats['total_records'],
										'valid'   => $validator_stats['valid_records'],
										'invalid' => ( $validator_stats['total_records'] - $validator_stats['valid_records'] ),
								],
								'user_generic_status_batch_id' => $user_generic_status_batch_id,
								'details'                      => $details,
						],
				];

				if ( $retval === false ) {
					Debug::Arr( $retarr, 'returnHandler v1 ERROR: ' . (int)$retval, __FILE__, __LINE__, __METHOD__, 10 );
				}

				//Handle progress bar here, make sure they are stopped and if an error occurs display the error.
				if ( $retval === false ) {
					//Try to show detailed validation error messages if at all possible.
					// Check for $details[0] because returnHandlers that lead into this seem to force an array with '0' key as per:
					//   $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), array( 0 => $validation_obj->getErrorsArray() ), array('total_records' => 1, 'valid_records' => 0 ) );
					if ( isset( $details ) && is_array( $details ) && isset( $details[0] ) ) {
						$validator = new Validator();
						$description .= "<br>\n<br>\n" . $validator->getTextErrors( true, $details[0] );
						unset( $validator );
					}
					$this->getProgressBarObject()->error( $this->getAPIMessageID(), $description );
				} else {
					$this->getProgressBarObject()->stop( $this->getAPIMessageID() );
				}

				return $retarr;
			}

			//No errors, or additional information, return unmodified data.
			return $retval;
		} else {
			if ( $retval === false ) {
				if ( $code == '' ) {
					$code = 'GENERAL';
				}
				if ( $description == '' ) {
					$description = 'Insufficient data to carry out action';
				}
			} else if ( $retval === true ) {
				if ( $code == '' ) {
					$code = 'SUCCESS';
				}
			}

			$validator_stats = Misc::preSetArrayValues( $validator_stats, [ 'total_records', 'valid_records', 'invalids_records' ], 0 );

			$retarr = [
					'api_retval'  => $retval,
					'api_details' => [
							'code'                         => $code,
							'description'                  => $description,
							'record_details'               => [
									'total'   => $validator_stats['total_records'],
									'valid'   => $validator_stats['valid_records'],
									'invalid' => ( $validator_stats['total_records'] - $validator_stats['valid_records'] ),
							],
							'user_generic_status_batch_id' => $user_generic_status_batch_id,
							//Allows the API to modify the original request data to send back to the UI for notifying the user.
							//We would like to implement validation on non-set*() calls as well perhaps?
							'request'                      => $request_data,
							'pager'                        => $this->getPagerData(),
							'details'                      => $details,
					],
			];

			if ( $retval === false ) {
				Debug::Arr( $retarr, 'returnHandler v2 ERROR: ' . (int)$retval, __FILE__, __LINE__, __METHOD__, 10 );
			}

			//Handle progress bar here, make sure they are stopped and if an error occurs display the error.
			if ( $retval === false ) {
				//Try to show detailed validation error messages if at all possible.
				// Check for $details[0] because returnHandlers that lead into this seem to force an array with '0' key as per:
				//   $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), array( 0 => $validation_obj->getErrorsArray() ), array('total_records' => 1, 'valid_records' => 0 ) );
				if ( isset( $details ) && is_array( $details ) && isset( $details[0] ) ) {
					$validator = new Validator();
					$description .= "<br>\n<br>\n" . $validator->getTextErrors( true, $details[0] );
					unset( $validator );
				}
				$this->getProgressBarObject()->error( $this->getAPIMessageID(), $description );
			} else {
				$this->getProgressBarObject()->stop( $this->getAPIMessageID() );
			}

			//Debug::Arr($retarr, 'returnHandler: '. (int)$retval, __FILE__, __LINE__, __METHOD__, 10);
			return $retarr;
		}
	}

	/**
	 * @param mixed $retarr
	 * @return mixed
	 */
	function stripReturnHandler( $retarr ) {
		if ( isset( $retarr['api_retval'] ) ) {
			return $retarr['api_retval'];
		}

		return $retarr;
	}

	/**
	 * Bridge to main class getOptions factory.
	 * @param bool $name
	 * @param string|int $parent
	 * @return array|bool
	 */
	function getOptions( $name = false, $parent = null ) {
		if ( $name != '' ) {
			if ( method_exists( $this->getMainClassObject(), 'getOptions' ) ) {
				return $this->getMainClassObject()->getOptions( $name, $parent );
			} else {
				Debug::Text( 'getOptions() function does not exist for object: ' . get_class( $this->getMainClassObject() ), __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			Debug::Text( 'ERROR: Name not provided, unable to return data...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * Bridge to main class getVariableToFunctionMap factory.
	 * @param string $name
	 * @param string|int $parent
	 * @return array
	 */
	function getVariableToFunctionMap( $name, $parent = null ) {
		return $this->getMainClassObject()->getVariableToFunctionMap( $name, $parent );
	}

	/**
	 * Take a API ReturnHandler array and pulls out the Validation errors/warnings to be merged back into another Validator
	 * This is useful for calling one API function from another one when their are sub-classes.
	 * @param $api_retarr
	 * @param bool $validator_obj
	 * @return bool|Validator
	 */
	function convertAPIReturnHandlerToValidatorObject( $api_retarr, $validator_obj = false ) {
		if ( is_object( $validator_obj ) ) {
			$validator = $validator_obj;
		} else {
			$validator = new Validator;
		}

		if ( isset( $api_retarr['api_retval'] ) && $api_retarr['api_retval'] === false && isset( $api_retarr['api_details']['details'] ) ) {
			foreach ( $api_retarr['api_details']['details'] as $tmp_validation_error_label => $validation_row ) {
				if ( isset( $validation_row['error'] ) ) {
					foreach ( $validation_row['error'] as $validation_error_label => $validation_error_msg ) {
						$validator->Error( $validation_error_label, $validation_error_msg[0] );
					}
				}

				if ( isset( $validation_row['warning'] ) ) {
					foreach ( $validation_row['warning'] as $validation_warning_label => $validation_warning_msg ) {
						$validator->Warning( $validation_warning_label, $validation_warning_msg[0] );
					}
				}

				//Before warnings were added, validation errors were just directly in the details array, so try to handle those here.
				//  This is used by TimeTrexPaymentServices API, since it doesn't use warnings.
				if ( !isset( $validation_row['error'] ) && !isset( $validation_row['warning'] ) ) {
					foreach ( $validation_row as $tmp_validation_error_label_b => $validation_error_msg ) {
						$validator->Error( $tmp_validation_error_label_b, $validation_error_msg[0] );
					}
				}
			}
		}

		return $validator;
	}

	/**
	 * @param string $primary_validator   UUID
	 * @param string $secondary_validator UUID
	 * @param bool $tertiary_validator
	 * @return array|bool
	 */
	function setValidationArray( $primary_validator, $secondary_validator, $tertiary_validator = false ) {
		//Handle primary validator first
		$validator = [];

		//Sometimes a Factory object is passed in, so we have to pull the ->Validator property from that if it happens.
		if ( is_a( $primary_validator, 'Validator' ) == false && isset( $primary_validator->Validator ) && is_a( $primary_validator->Validator, 'Validator' ) ) {
			$primary_validator = $primary_validator->Validator;
		}

		if ( is_a( $secondary_validator, 'Validator' ) == false && isset( $secondary_validator->Validator ) && is_a( $secondary_validator->Validator, 'Validator' ) ) {
			$secondary_validator = $secondary_validator->Validator;
		}

		if ( is_a( $tertiary_validator, 'Validator' ) == false && isset( $tertiary_validator->Validator ) && is_a( $tertiary_validator->Validator, 'Validator' ) ) {
			$tertiary_validator = $tertiary_validator->Validator;
		}

		if ( $this->getProtocolVersion() == 1 ) { //Don't return any warnings and therefore don't put errors in its own array element.
			if ( $primary_validator->isError() === true ) {
				$validator = $primary_validator->getErrorsArray();
			} else {
				//Check secondary validator for errors.
				if ( $secondary_validator->isError() === true ) {
					$validator = $secondary_validator->getErrorsArray();
				} else {
					//Check tertiary validator for errors.
					if ( $tertiary_validator->isError() === true ) {
						$validator = $tertiary_validator->getErrorsArray();
					}
				}
			}
		} else {
			if ( $primary_validator->isError() === true ) {
				$validator['error'] = $primary_validator->getErrorsArray();
			} else {
				//Check for primary validator warnings next.
				if ( $primary_validator->isWarning() === true ) {
					$validator['warning'] = $primary_validator->getWarningsArray();
				} else {
					//Check secondary validator for errors.
					if ( $secondary_validator->isError() === true ) {
						$validator['error'] = $secondary_validator->getErrorsArray();
					} else {
						//Check secondary validator for warnings.
						if ( $secondary_validator->isWarning() === true ) {
							$validator['warning'] = $secondary_validator->getWarningsArray();
						} else {
							//Check tertiary validator for errors.
							if ( $tertiary_validator->isError() === true ) {
								$validator['error'] = $tertiary_validator->getErrorsArray();
							} else {
								//Check tertiary validator for warnings.
								if ( $tertiary_validator->isWarning() === true ) {
									$validator['warning'] = $tertiary_validator->getWarningsArray();
								}
							}
						}
					}
				}
			}
		}

		if ( count( $validator ) > 0 ) {
			return $validator;
		}

		return false;
	}


	/**
	 * @param object|bool $validator
	 * @param array $validator_stats
	 * @param int $key
	 * @param array|bool $save_result
	 * @param bool $user_generic_status_batch_id
	 * @return array
	 */
	function handleRecordValidationResults( $validator, $validator_stats, $key, $save_result, $user_generic_status_batch_id = false ) {
		if ( $validator_stats['valid_records'] > 0 && $validator_stats['total_records'] == $validator_stats['valid_records'] ) {
			if ( $validator_stats['total_records'] == 1 ) {
				return $this->returnHandler( $save_result[$key], true, false, false, false, $user_generic_status_batch_id ); //Single valid record
			} else {
				return $this->returnHandler( true, 'SUCCESS', TTi18n::getText( 'MULTIPLE RECORDS SAVED' ), $save_result, $validator_stats, $user_generic_status_batch_id ); //Multiple valid records
			}
		} else {
			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), $validator, $validator_stats, $user_generic_status_batch_id );
		}
	}
}

?>
