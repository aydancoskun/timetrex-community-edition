<?php /** @noinspection PhpPossiblePolymorphicInvocationInspection */
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


/**
 * @package API\Import
 */
class APIImport extends APIFactory {

	public $import_obj = null;

	/**
	 * APIImport constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		//When APIImport()->getImportObjects() is called directly, there won't be a main class to call.
		if ( isset( $this->main_class ) && $this->main_class != '' ) {
			$this->import_obj = new $this->main_class;
			$this->import_obj->company_id = $this->getCurrentCompanyObject()->getID();
			$this->import_obj->user_id = $this->getCurrentUserObject()->getID();

			global $authentication;
			if ( is_object( $authentication ) && $authentication->getSessionID() != '' ) {
				Debug::text( 'Session ID: ' . $authentication->getSessionID(), __FILE__, __LINE__, __METHOD__, 10 );
				$this->import_obj->session_id = $authentication->getSessionID();
			}

			Debug::Text( 'Setting main class: ' . $this->main_class . ' Company ID: ' . $this->import_obj->company_id, __FILE__, __LINE__, __METHOD__, 10 );
		} else {
			Debug::Text( 'NOT Setting main class... Company ID: ' . $this->getCurrentCompanyObject()->getID(), __FILE__, __LINE__, __METHOD__, 10 );
		}

		return true;
	}

	/**
	 * @return null
	 */
	function getImportObject() {
		return $this->import_obj;
	}

	/**
	 * @return array|bool
	 */
	function getImportObjects() {
		$retarr = [];

		if ( $this->getPermissionObject()->Check( 'user', 'add' ) && ( $this->getPermissionObject()->Check( 'user', 'edit' ) || $this->getPermissionObject()->Check( 'user', 'edit_child' ) ) ) {
			$retarr['-1010-User'] = TTi18n::getText( 'Employees' );
		}
		if ( $this->getPermissionObject()->Check( 'remittance_destination_account', 'edit' ) || $this->getPermissionObject()->Check( 'remittance_destination_account', 'edit_child' ) ) {
			$retarr['-1015-RemittanceDestinationAccount'] = TTi18n::getText( 'Employee Pay Methods' );
		}
		if ( $this->getPermissionObject()->Check( 'branch', 'add' ) && $this->getPermissionObject()->Check( 'branch', 'edit' ) ) {
			$retarr['-1020-Branch'] = TTi18n::getText( 'Branches' );
		}
		if ( $this->getPermissionObject()->Check( 'department', 'add' ) && $this->getPermissionObject()->Check( 'department', 'edit' ) ) {
			$retarr['-1030-Department'] = TTi18n::getText( 'Departments' );
		}
		if ( $this->getPermissionObject()->Check( 'wage', 'add' ) && ( $this->getPermissionObject()->Check( 'wage', 'edit' ) || $this->getPermissionObject()->Check( 'wage', 'edit_child' ) ) ) {
			$retarr['-1050-UserWage'] = TTi18n::getText( 'Employee Wages' );
		}
		if ( $this->getPermissionObject()->Check( 'pay_period_schedule', 'add' ) && $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit' ) ) {
			$retarr['-1060-PayPeriod'] = TTi18n::getText( 'Pay Periods' );
		}
		if ( $this->getPermissionObject()->Check( 'pay_stub_amendment', 'add' ) && $this->getPermissionObject()->Check( 'pay_stub_amendment', 'edit' ) ) {
			$retarr['-1200-PayStubAmendment'] = TTi18n::getText( 'Pay Stub Amendments' );
		}
		if ( $this->getPermissionObject()->Check( 'accrual', 'add' ) && ( $this->getPermissionObject()->Check( 'accrual', 'edit' ) || $this->getPermissionObject()->Check( 'accrual', 'edit_child' ) ) ) {
			$retarr['-1300-Accrual'] = TTi18n::getText( 'Accruals' );
		}

		if ( $this->getCurrentCompanyObject()->getProductEdition() >= 15 ) {
			if ( $this->getPermissionObject()->Check( 'punch', 'add' ) && ( $this->getPermissionObject()->Check( 'punch', 'edit' ) || $this->getPermissionObject()->Check( 'punch', 'edit_child' ) ) ) {
				$retarr['-1100-Punch'] = TTi18n::getText( 'Punches' );
			}
			if ( $this->getPermissionObject()->Check( 'punch', 'add' ) && ( $this->getPermissionObject()->Check( 'punch', 'edit' ) || $this->getPermissionObject()->Check( 'punch', 'edit_child' ) ) ) {
				$retarr['-1110-UserDateTotal'] = TTi18n::getText( 'Manual TimeSheet' );
			}
			if ( $this->getPermissionObject()->Check( 'schedule', 'add' ) && ( $this->getPermissionObject()->Check( 'schedule', 'edit' ) || $this->getPermissionObject()->Check( 'schedule', 'edit_child' ) ) ) {
				$retarr['-1150-Schedule'] = TTi18n::getText( 'Scheduled Shifts' );
			}
		}

		if ( $this->getCurrentCompanyObject()->getProductEdition() >= 20 ) {
			if ( $this->getPermissionObject()->Check( 'client', 'add' ) && $this->getPermissionObject()->Check( 'client', 'edit' ) ) {
				$retarr['-1500-Client'] = TTi18n::getText( 'Clients' );
			}
			if ( $this->getPermissionObject()->Check( 'job', 'add' ) && $this->getPermissionObject()->Check( 'job', 'edit' ) ) {
				$retarr['-1600-Job'] = TTi18n::getText( 'Jobs' );
			}
			if ( $this->getPermissionObject()->Check( 'job_item', 'add' ) && $this->getPermissionObject()->Check( 'job_item', 'edit' ) ) {
				$retarr['-1605-JobItem'] = TTi18n::getText( 'Tasks' );
			}
		}

		return $this->returnHandler( $retarr );
	}

	/**
	 * @return array|bool
	 */
	function returnFileValidationError() {
		//Make sure we return a complete validation error to be displayed to the user.
		$validator_obj = new Validator();
		$validator_stats = [ 'total_records' => 1, 'valid_records' => 0 ];

		$validator_obj->isTrue( 'file', false, TTi18n::getText( 'Please upload file again' ) );

		$validator = [];
		$validator[0]['error'] = $validator_obj->getErrorsArray();

		return $this->returnHandler( false, 'IMPORT_FILE', TTi18n::getText( 'INVALID DATA' ), $validator, $validator_stats );
	}

	/**
	 * @param null $maximum_lines
	 * @return array|bool
	 */
	function returnMaximumLineValidationError( $maximum_lines = null ) {
		//Make sure we return a complete validation error to be displayed to the user.
		$validator_obj = new Validator();
		$validator_stats = [ 'total_records' => 1, 'valid_records' => 0 ];

		$validator_obj->isTrue( 'records', false, TTi18n::getText( 'File exceeds maximum number of records (%1) that can be imported at one time', $maximum_lines ) );

		$validator = [];
		$validator[0]['error'] = $validator_obj->getErrorsArray();

		return $this->returnHandler( false, 'IMPORT_FILE', TTi18n::getText( 'INVALID DATA' ), $validator, $validator_stats );
	}

	/**
	 * @return array|bool
	 */
	function generateColumnMap() {
		if ( $this->getImportObject()->getRawDataFromFile() == false ) {
			$this->returnFileValidationError();
		}

		return $this->returnHandler( $this->getImportObject()->generateColumnMap() );
	}

	/**
	 * @param $saved_column_map
	 * @return array|bool
	 */
	function mergeColumnMap( $saved_column_map ) {
		if ( $this->getImportObject()->getRawDataFromFile() == false ) {
			$this->returnFileValidationError();
		}

		return $this->returnHandler( $this->getImportObject()->mergeColumnMap( $saved_column_map ) );
	}

	/**
	 * @param int $limit Limit the number of records returned
	 * @return array|bool
	 */
	function getRawData( $limit = null ) {
		if ( !is_object( $this->getImportObject() ) || $this->getImportObject()->getRawDataFromFile() == false ) {
			$this->returnFileValidationError();
		}

		return $this->returnHandler( $this->getImportObject()->getRawData( $limit ) );
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function setRawData( $data ) {
		return $this->returnHandler( $this->getImportObject()->saveRawDataToFile( $data ) );
	}

	/**
	 * @return array|bool
	 */
	function getParsedData() {
		return $this->returnHandler( $this->getParsedData() );
	}

	/**
	 * @param $column_map
	 * @param array $import_options
	 * @param bool $validate_only
	 * @return array|bool
	 */
	function Import( $column_map, $import_options = [], $validate_only = false ) {
		if ( Misc::isSystemLoadValid() == false ) { //Check system load as the user could ask to calculate decades worth at a time.
			Debug::Text( 'ERROR: System load exceeded, preventing new imports from starting...', __FILE__, __LINE__, __METHOD__, 10 );

			return $this->returnHandler( false );
		}

		global $config_vars;
		if ( isset( $config_vars['other']['import_maximum_execution_limit'] ) && $config_vars['other']['import_maximum_execution_limit'] != '' ) {
			$maximum_execution_time = $config_vars['other']['import_maximum_execution_limit'];
		} else {
			if ( DEPLOYMENT_ON_DEMAND == true ) {
				$maximum_execution_time = 3600;
			} else {
				$maximum_execution_time = 0;
			}
		}

		if ( $validate_only == true ) { //When validating, reduce the maximum execution time substantially to limit resource/time waste.
			$maximum_execution_time = ceil( $maximum_execution_time / 2 );
		}

		ini_set( 'max_execution_time', $maximum_execution_time );
		Debug::Text( 'Maximum Execution Time for Import: ' . $maximum_execution_time . ' Validate Only: ' . (int)$validate_only, __FILE__, __LINE__, __METHOD__, 10 );

		if ( isset( $config_vars['other']['import_maximum_records_limit'] ) && $config_vars['other']['import_maximum_records_limit'] != '' ) {
			$maximum_record_limit = $config_vars['other']['import_maximum_records_limit'];
		} else {
			if ( DEPLOYMENT_ON_DEMAND == true ) {
				$maximum_record_limit = 1000;
			} else {
				$maximum_record_limit = 0;
			}
		}
		Debug::Text( 'Maximum Records allowed for Import: ' . $maximum_record_limit . ' Validate Only: ' . (int)$validate_only, __FILE__, __LINE__, __METHOD__, 10 );

		if ( function_exists( 'proc_nice' ) ) {
			proc_nice( 19 ); //Low priority.
		}

		if ( $this->getImportObject()->setColumnMap( $column_map ) == false ) {
			return $this->returnHandler( false );
		}

		if ( is_array( $import_options ) && $this->getImportObject()->setImportOptions( $import_options ) == false ) {
			return $this->returnHandler( false );
		}

		if ( $this->getImportObject()->getRawDataFromFile() == false ) {
			return $this->returnFileValidationError();
		}

		if ( $maximum_record_limit > 0 && $this->getImportObject()->getRawDataLines() > $maximum_record_limit ) {
			return $this->returnMaximumLineValidationError( $maximum_record_limit );
		}

		//Force this while testing.
		//$validate_only = TRUE;
		$this->getImportObject()->setAPIMessageID( $this->getAPIMessageID() ); //This must be set *after* the all constructor functions are called.

		return $this->getImportObject()->Process( $validate_only ); //Don't need return handler here as a API function is called anyways.
	}

}

?>
