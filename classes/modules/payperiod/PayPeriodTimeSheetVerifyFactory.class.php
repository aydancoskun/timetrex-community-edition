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
 * @package Modules\PayPeriod
 */
class PayPeriodTimeSheetVerifyFactory extends Factory {
	protected $table = 'pay_period_time_sheet_verify';
	protected $pk_sequence_name = 'pay_period_time_sheet_verify_id_seq'; //PK Sequence name

	var $user_obj = NULL;
	var $pay_period_obj = NULL;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = NULL ) {

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
										10 => TTi18n::gettext('INCOMPLETE'),
										20 => TTi18n::gettext('OPEN'),
										30 => TTi18n::gettext('PENDING AUTHORIZATION'),
										40 => TTi18n::gettext('AUTHORIZATION OPEN'),
										45 => TTi18n::gettext('PENDING EMPLOYEE VERIFICATION'), //Fully authorized, waiting on employee verification.
										50 => TTi18n::gettext('Verified'),
										55 => TTi18n::gettext('AUTHORIZATION DECLINED'),
										60 => TTi18n::gettext('DISABLED')
									);
				break;
			case 'filter_report_status':
				//show values custom to report with the addition of not verified.
				$retval = array(
						0 => TTi18n::gettext('Not Verified'),
						30 => TTi18n::gettext('PENDING AUTHORIZATION'),
						45 => TTi18n::gettext('PENDING EMPLOYEE VERIFICATION'), //Fully authorized, waiting on employee verification.
						50 => TTi18n::gettext('Verified'),
						55 => TTi18n::gettext('AUTHORIZATION DECLINED'),
				);
				break;
			case 'columns':
				$retval = array(
										'-1010-first_name' => TTi18n::gettext('First Name'),
										'-1020-last_name' => TTi18n::gettext('Last Name'),
										'-1060-title' => TTi18n::gettext('Title'),
										'-1070-user_group' => TTi18n::gettext('Group'),
										'-1080-default_branch' => TTi18n::gettext('Branch'),
										'-1090-default_department' => TTi18n::gettext('Department'),

										'-1110-start_date' => TTi18n::gettext('Start Date'),
										'-1112-end_date' => TTi18n::gettext('End Date'),
										'-1115-transaction_date' => TTi18n::gettext('Transaction Date'),
										'-1118-window_start_date' => TTi18n::gettext('Window Start Date'),
										'-1119-window_end_date' => TTi18n::gettext('Window End Date'),

										'-1120-status' => TTi18n::gettext('Status'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'first_name',
								'last_name',
								'start_date',
								'end_date',
								'status'
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
								);
				break;

		}

		return $retval;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'pay_period_id' => 'PayPeriod',
										'start_date' => FALSE, //PayPeriod
										'end_date' => FALSE, //PayPeriod
										'transaction_date' => FALSE, //PayPeriod
										'window_start_date' => FALSE,
										'window_end_date' => FALSE,
										'user_id' => 'User',
										'first_name' => FALSE,
										'last_name' => FALSE,
										'default_branch' => FALSE,
										'default_department' => FALSE,
										'user_group' => FALSE,
										'title' => FALSE,
										'status_id' => 'Status',
										'status' => FALSE,
										'user_verified' => 'UserVerified',
										'user_verified_date' => 'UserVerifiedDate',
										'authorized' => 'Authorized',
										'authorization_level' => 'AuthorizationLevel',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	/**
	 * @return bool
	 */
	function getPayPeriodObject() {
		return $this->getGenericObject( 'PayPeriodListFactory', $this->getPayPeriod(), 'pay_period_obj' );
	}

	/**
	 * @return bool|mixed
	 */
	function getPayPeriod() {
		return $this->getGenericDataValue( 'pay_period_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPayPeriod( $value = NULL) {
		$value = trim($value);

		if ( $value == NULL ) {
			$value = $this->findPayPeriod();
		}
		return $this->setGenericDataValue( 'pay_period_id', $value );
	}

	//Stores the current user in memory, so we can determine if its the employee verifying, or a superior.

	/**
	 * @return mixed
	 */
	function getCurrentUser() {
		return $this->getGenericTempDataValue( 'current_user_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setCurrentUser( $value) {
		$value = trim($value);
		return $this->setGenericTempDataValue( 'current_user_id', $value );
	}

	/**
	 * @return mixed
	 */
	function getUser() {
		return $this->getGenericDataValue( 'user_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setUser( $value) {
		$value = TTUUID::castUUID($value);
		if ( $value == '' ) {
			$value = TTUUID::getZeroID();
		}
		return $this->setGenericDataValue( 'user_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getStatus() {
		return $this->getGenericDataValue( 'status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStatus( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'status_id', $value );
	}

	//Set this to TRUE when the user has actually verified their own timesheets.

	/**
	 * @return bool|null
	 */
	function getUserVerified() {
		$value = $this->getGenericDataValue( 'user_verified' );
		if ( $value !== FALSE AND $value !== NULL ) {
			return $this->fromBool( $value );
		}

		return NULL;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserVerified( $value) {
		$this->setGenericDataValue('user_verified', $this->toBool($value) );
		$this->setUserVerifiedDate();

		return TRUE;
	}

	/**
	 * @return bool|mixed
	 */
	function getUserVerifiedDate() {
		return $this->getGenericDataValue( 'user_verified_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setUserVerifiedDate( $value = NULL) {
		$value = ( !is_int($value) ) ? trim($value) : $value; //Dont trim integer values, as it changes them to strings.

		if ($value == NULL) {
			$value = TTDate::getTime();
		}
		return $this->setGenericDataValue( 'user_verified_date', $value );

	}

	/**
	 * @return bool|null
	 */
	function getAuthorized() {
		$value = $this->getGenericDataValue( 'authorized' );
		if ( $value !== FALSE AND $value !== NULL) {
			return $this->fromBool( $value );
		}

		return NULL;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAuthorized( $value) {
		return $this->setGenericDataValue( 'authorized', $this->toBool($value) );
	}

	/**
	 * @return bool|mixed
	 */
	function getAuthorizationLevel() {
		return $this->getGenericDataValue( 'authorization_level' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAuthorizationLevel( $value) {
		$value = (int)trim( $value );

		if ( $value < 0 ) {
			$value = 0;
		}
		return $this->setGenericDataValue( 'authorization_level', $value );
	}

	/**
	 * @return bool
	 */
	function getVerificationType() {
		if ( is_object( $this->getPayPeriodObject() ) AND $this->getPayPeriodObject()->getPayPeriodScheduleObject() != FALSE ) {
			$time_sheet_verification_type_id = $this->getPayPeriodObject()->getPayPeriodScheduleObject()->getTimeSheetVerifyType();
			Debug::Text('TimeSheet Verification Type: '. $time_sheet_verification_type_id, __FILE__, __LINE__, __METHOD__, 10);
			return $time_sheet_verification_type_id;
		}

		return FALSE;
	}

	//Returns the start and end date of the verification window.

	/**
	 * @return array|bool
	 */
	function getVerificationWindowDates() {
		if ( is_object( $this->getPayPeriodObject() ) ) {
			return array( 'start' => $this->getPayPeriodObject()->getTimeSheetVerifyWindowStartDate(), 'end' => $this->getPayPeriodObject()->getTimeSheetVerifyWindowEndDate() );
		}

		return FALSE;
	}

	//Determines the color of the verification box.

	/**
	 * @return bool|string
	 */
	function getVerificationBoxColor() {
		$retval = FALSE;
		if ( is_object( $this->getPayPeriodObject() )
				AND TTDate::getTime() >= $this->getPayPeriodObject()->getTimeSheetVerifyWindowStartDate()
				AND TTDate::getTime() <= $this->getPayPeriodObject()->getTimeSheetVerifyWindowEndDate() ) {

			if ( $this->getStatus() == 55 ) { //Declined
				$retval = '#FF0000';
			} elseif ( $this->getStatus() != 50 ) {
				$retval = '#FFFF00';
			}
		}

		return $retval;
	}

	/**
	 * @param int $status_id ID
	 * @return string
	 */
	function getVerificationStatusShortDisplay( $status_id = NULL ) {
		if ( $status_id == '' ) {
			$status_id = $this->getStatus();
		}

		//If no verification object exists, we assume "No" for verification status.
		if ( $status_id == 50 ) {
			$retval = TTi18n::getText('Yes');
		} elseif ( $status_id == 30 OR $status_id == 45 ) {
			$retval = TTi18n::getText('Pending');
		} elseif ( $status_id == 55 )  {
			$retval = TTi18n::getText('Declined');
		} else {
			$retval = TTi18n::getText('No');
		}

		return $retval;
	}

	/**
	 * @return bool|string
	 */
	function getVerificationStatusDisplay() {
		$retval = TTi18n::getText('Not Verified');
		if ( $this->getUserVerifiedDate() == TRUE AND $this->getAuthorized() == TRUE ) {
			$retval = TTi18n::getText('Verified @').' '.TTDate::getDate('DATE+TIME', $this->getUserVerifiedDate() ); //Date verification took place for employee.
		} else {
			if ( $this->isNew() == TRUE
					AND ( is_object( $this->getUserObject() )
							AND is_object( $this->getPayPeriodObject() )
							AND ( TTDate::getMiddleDayEpoch( $this->getUserObject()->getHireDate() ) <= TTDate::getMiddleDayEpoch( $this->getPayPeriodObject()->getEndDate() ) )
							AND ( $this->getUserObject()->getTerminationDate() == '' OR ( $this->getUserObject()->getTerminationDate() != '' AND TTDate::getMiddleDayEpoch( $this->getUserObject()->getTerminationDate() ) >= TTDate::getMiddleDayEpoch( $this->getPayPeriodObject()->getStartDate() ) ) )
						)
					AND TTDate::getTime() >= $this->getPayPeriodObject()->getTimeSheetVerifyWindowStartDate()
					AND TTDate::getTime() <= $this->getPayPeriodObject()->getTimeSheetVerifyWindowEndDate() ) {
				$pay_period_verify_type_id = $this->getVerificationType();
				if ( $pay_period_verify_type_id == 20 OR $pay_period_verify_type_id == 40 ) {
					$retval = Option::getByKey( 45, $this->getOptions('status') ); //Pending employee verification.
				} else {
					$retval = Option::getByKey( 30, $this->getOptions('status') ); //Pending authorization.
				}
			//} elseif ( $this->isNew() == TRUE ) {
				//Use Default: Not Verified
			} else {
				if ( $this->getStatus() == 50 OR $this->getStatus() == 55 ) {
					$retval = Option::getByKey( $this->getStatus(), $this->getOptions('status') ).' @ '.TTDate::getDate('DATE+TIME', $this->getUpdatedDate() );
				} elseif ( $this->getStatus() !== FALSE )  {
					$retval = Option::getByKey( $this->getStatus(), $this->getOptions('status') );
				} // else { //Verify record has not been created yet, and the window hasnt opened yet, so display the default "Not Verified".
			}
		}

		return $retval;
	}

	/**
	 * @return bool|string
	 */
	function getVerificationConfirmationMessage() {
		$pp_obj = $this->getPayPeriodObject();
		if ( is_object( $pp_obj ) ) {
			$retval = TTi18n::getText('I hereby certify that this timesheet for the pay period of').' '. TTDate::getDate('DATE', $pp_obj->getStartDate() ) .' '. TTi18n::getText('to') .' '. TTDate::getDate('DATE', $pp_obj->getEndDate() ) .' '. TTi18n::getText('is accurate and correct.');

			return $retval;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getPreviousPayPeriodObject() {
		$pplf = TTnew( 'PayPeriodListFactory' );
		$pplf->getPreviousPayPeriodById( $this->getPayPeriod() );
		if ( $pplf->getRecordCount() > 0 ) {
			return $pplf->getCurrent();
		}

		return FALSE;
	}

	/**
	 * @param string $user_id UUID
	 * @return bool
	 */
	function isPreviousPayPeriodVerified( $user_id = NULL ) {
		if ( $user_id == '' ) {
			$user_id = $this->getUser();
		}

		//Check if previous pay period was verified or not
		$is_previous_time_sheet_verified = FALSE;

		$previous_pay_period_obj = $this->getPreviousPayPeriodObject();
		if ( is_object($previous_pay_period_obj) ) {
			if (	( is_object( $this->getUserObject() )
					AND TTDate::getMiddleDayEpoch( $this->getUserObject()->getHireDate() ) >= TTDate::getMiddleDayEpoch( $previous_pay_period_obj->getEndDate() ) )
					AND ( $this->getUserObject()->getTerminationDate() == '' OR ( $this->getUserObject()->getTerminationDate() != '' AND TTDate::getMiddleDayEpoch( $this->getUserObject()->getTerminationDate() ) >= TTDate::getMiddleDayEpoch( $previous_pay_period_obj->getStartDate() ) ) )
				) {
				Debug::text('Hired after previous pay period ended...', __FILE__, __LINE__, __METHOD__, 10);
				$is_previous_time_sheet_verified = TRUE;
			} elseif ( $previous_pay_period_obj->getStatus() == 20 ) {
				$is_previous_time_sheet_verified = TRUE;
			} else {
				$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
				$pptsvlf->getByPayPeriodIdAndUserId( $previous_pay_period_obj->getId(), $user_id );
				if ( $pptsvlf->getRecordCount() > 0 ) {
					$pptsv_obj = $pptsvlf->getCurrent();
					if ( $pptsv_obj->getAuthorized() == TRUE ) {
						$is_previous_time_sheet_verified = TRUE;
					}
				}
			}
		} else {
			$is_previous_time_sheet_verified = TRUE; //There is no previous pay period
		}
		unset($previous_pay_period_obj, $pptsvlf, $pptsv_obj);

		return $is_previous_time_sheet_verified;
	}

	/**
	 * @param string $current_user_id UUID
	 * @param string $user_id UUID
	 * @return bool
	 */
	function displayPreviousPayPeriodVerificationNotice( $current_user_id = NULL, $user_id = NULL ) {
		if ( $current_user_id == '' ) {
			$current_user_id = $this->getCurrentUser();
		}
		if ( $current_user_id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			$user_id = $this->getUser();
		}

		$previous_pay_period_obj = $this->getPreviousPayPeriodObject();
		$is_previous_time_sheet_verified = $this->isPreviousPayPeriodVerified( $user_id );
		Debug::text('Previous Pay Period Verified: '. (int)$is_previous_time_sheet_verified, __FILE__, __LINE__, __METHOD__, 10);

		$pay_period_verify_type_id = $this->getVerificationType();
		$is_timesheet_superior = $this->isHierarchySuperior( $current_user_id, $user_id );
		if (
				(
					( $pay_period_verify_type_id == 20 AND $current_user_id == $user_id )
					OR
					( $pay_period_verify_type_id == 30 AND $is_timesheet_superior == TRUE )
					OR
					( $pay_period_verify_type_id == 40 AND ( ( $current_user_id == $user_id ) OR ( $is_timesheet_superior == TRUE AND !in_array($current_user_id, (array)$this->getAuthorizedUsers() )	) ) )
				)
				AND
				( $is_previous_time_sheet_verified == FALSE AND TTDate::getTime() <= $previous_pay_period_obj->getTimeSheetVerifyWindowEndDate() )
			) {
			return TRUE;
		}

		return FALSE;

	}
	//Determine if we need to display the verification button or not.

	/**
	 * @param string $current_user_id UUID
	 * @param string $user_id UUID
	 * @return bool
	 */
	function displayVerifyButton( $current_user_id = NULL, $user_id = NULL ) {
		if ( $current_user_id == '' ) {
			$current_user_id = $this->getCurrentUser();
		}
		if ( $current_user_id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			$user_id = $this->getUser();
		}

		$pay_period_verify_type_id = $this->getVerificationType();
		$is_timesheet_superior = $this->isHierarchySuperior( $current_user_id, $user_id );
		Debug::text('Current User ID: '. $current_user_id .' User ID: '. $user_id .' Verification Type ID: '. $pay_period_verify_type_id .' TimeSheet Superior: '. (int)$is_timesheet_superior .' Status: '. (int)$this->getStatus(), __FILE__, __LINE__, __METHOD__, 10);
		//Debug::text('Hire Date: '. TTDate::getDATE('DATE+TIME', $this->getUserObject()->getHireDate() ) .' Termination Date: '. TTDate::getDATE('DATE+TIME', $this->getUserObject()->getTerminationDate() ), __FILE__, __LINE__, __METHOD__, 10);

		if (
				(
					( $pay_period_verify_type_id == 20 AND $current_user_id == $user_id )
					OR
					( $pay_period_verify_type_id == 30 AND $this->getStatus() != 50 AND ( $is_timesheet_superior == TRUE AND $current_user_id != $user_id AND !in_array($current_user_id, (array)$this->getAuthorizedUsers() ) ) )
					OR
					( $pay_period_verify_type_id == 40 AND ( $this->getStatus() == 55 OR ( $current_user_id == $user_id AND $this->getUserVerified() == 0 ) OR ( $is_timesheet_superior == TRUE AND !in_array($current_user_id, (array)$this->getAuthorizedUsers() )  ) ) )
				)
				AND
				(
					//If the employee is hired on the last day of a pay period, allow them to verify that timesheet, so <= is required here.
					(
						is_object( $this->getUserObject() )
						AND
						( TTDate::getMiddleDayEpoch( $this->getUserObject()->getHireDate() ) <= TTDate::getMiddleDayEpoch( $this->getPayPeriodObject()->getEndDate() ) )
						AND
						( $this->getUserObject()->getTerminationDate() == '' OR ( $this->getUserObject()->getTerminationDate() != '' AND TTDate::getMiddleDayEpoch( $this->getUserObject()->getTerminationDate() ) >= TTDate::getMiddleDayEpoch( $this->getPayPeriodObject()->getStartDate() ) ) )
					)
					AND
					TTDate::getTime() >= $this->getPayPeriodObject()->getTimeSheetVerifyWindowStartDate() AND TTDate::getTime() <= $this->getPayPeriodObject()->getTimeSheetVerifyWindowEndDate() AND $this->getStatus() != 50
				)
			) {

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param string $current_user_id UUID
	 * @param string $user_id UUID
	 * @return bool
	 */
	function isHierarchySuperior( $current_user_id = NULL, $user_id = NULL ) {
		if ( $current_user_id == '' ) {
			$current_user_id = $this->getCurrentUser();
		}
		if ( $current_user_id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			$user_id = $this->getUser();
		}

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getById( $user_id );
		if ( $ulf->getRecordCount() == 1 ) {
			$user_obj = $ulf->getCurrent();

			$hlf = TTnew( 'HierarchyListFactory' );
			//Get timesheet verification hierarchy, so we know who the superiors are.
			//Immediate superiors only can verify timesheets directly so we set $immediate_parents_only = TRUE
			//  However this prevents superiors from dropping down levels and authorizing, as the superior wouldn't appear in the superior list then, so set $immediate_parents_only = FALSE
			$timesheet_parent_level_user_ids = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $user_obj->getCompany(), $user_obj->getId(), 90, FALSE, FALSE );
			Debug::Arr( $timesheet_parent_level_user_ids, 'TimeSheet Parent Level Ids', __FILE__, __LINE__, __METHOD__, 10);
			if ( in_array( $current_user_id, (array)$timesheet_parent_level_user_ids ) ) {
				Debug::text('Is TimeSheet Hierarchy Superior: Yes', __FILE__, __LINE__, __METHOD__, 10);
				return TRUE;
			}
			unset($hlf, $timesheet_parent_level_user_ids);
		}

		Debug::text('Is TimeSheet Hierarchy Superior: No', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//Returns all superiors that have authorized this timesheet so far.

	/**
	 * @return array
	 */
	function getAuthorizedUsers() {
		$retarr = array();

		$alf = TTnew( 'AuthorizationListFactory' );
		$alf->getByObjectTypeAndObjectId(90, $this->getId() );
		if ( $alf->getRecordCount() > 0 ) {
			foreach( $alf as $a_obj ) {
				if ( $a_obj->getAuthorized() == TRUE ) {
					$retarr[] = $a_obj->getCreatedBy();
				}
			}
		}

		return $retarr;
	}


	/**
	 * @return bool
	 */
	function calcStatus() {
		//Get pay period schedule verification type.
		$time_sheet_verification_type_id = $this->getVerificationType();
		if ( $time_sheet_verification_type_id > 10 ) { //10 = Disabled
			$is_timesheet_superior = FALSE;
			if ( $time_sheet_verification_type_id == 30 OR $time_sheet_verification_type_id == 40 ) { //Superior or Employee & Superior
				$is_timesheet_superior = $this->isHierarchySuperior( $this->getCurrentUser() );
			}

			if ( $time_sheet_verification_type_id == 20 ) { //Employee Only
				if ( $this->getCurrentUser() == $this->getUser() ) {
					Debug::Text('aEmployee is verifiying their own timesheet...', __FILE__, __LINE__, __METHOD__, 10);

					//Employee is verifiying their own timesheet.
					$this->setStatus( 50 ); //Authorized
					$this->setAuthorized( TRUE );
					$this->setUserVerified( TRUE );
				}
			} elseif ( $time_sheet_verification_type_id == 30 ) { //Superior Only
				//Make sure superiors can drop down levels and verify timesheets in this mode.
				if ( $this->getCurrentUser() != $this->getUser() AND $is_timesheet_superior == TRUE ) {
					Debug::Text('Superior is verifiying their suborindates timesheet...', __FILE__, __LINE__, __METHOD__, 10);
					$this->setStatus( 30 ); //Pending Authorization
				} elseif( $this->getCurrentUser() == $this->getUser() ) {
					Debug::Text('ERROR: Superior is trying to verifiy their own timesheet...', __FILE__, __LINE__, __METHOD__, 10);
				} else {
					Debug::Text('ERROR: Superior is not in the hierarchy?', __FILE__, __LINE__, __METHOD__, 10);
				}
			} elseif ( $time_sheet_verification_type_id == 40 ) { //Superior & Employee
				if ( $this->isNew() == TRUE ) {
					$this->setStatus( 30 ); //Pending Authorization
				}

				if ( $this->getCurrentUser() == $this->getUser() ) {
					Debug::Text('bEmployee is verifiying their own timesheet...', __FILE__, __LINE__, __METHOD__, 10);
					//Employee is verifiying their own timesheet.
					$this->setUserVerified( TRUE );

					if (  $this->getAuthorized() == TRUE ) { //If this has already been verified by superiors, and the employee is the last step, make sure mark this as verified.
						$this->setStatus( 50 ); //Verified
					} else {
						$this->setStatus( 30 ); //Pending Authorization.
					}
				}

				//If the top-level superior authorizes the timesheet before the employee has, make sure we keep the status as 30.
				if ( $this->getStatus() == 50 AND $this->getUserVerified() == FALSE ) {
					$this->setStatus( 45 ); //Pending Employee Verification
				}
			}

			//If this is a new verification, find the current authorization level to assign to it.
			if ( ( $this->isNew() == TRUE OR $this->getStatus() == 55 ) AND ( $time_sheet_verification_type_id == 30 OR $time_sheet_verification_type_id == 40 ) ) {
				$hierarchy_highest_level = AuthorizationFactory::getInitialHierarchyLevel( ( is_object( $this->getUserObject() ) ? $this->getUserObject()->getCompany() : 0 ), ( is_object( $this->getUserObject() ) ? $this->getUserObject()->getID() : 0 ), 90 );
				$this->setAuthorizationLevel( $hierarchy_highest_level );
			}
		}

		return TRUE;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = TRUE ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Pay Period
		$pplf = TTnew( 'PayPeriodListFactory' );
		$this->Validator->isResultSetWithRows(	'pay_period',
														$pplf->getByID($this->getPayPeriod()),
														TTi18n::gettext('Invalid Pay Period')
													);
		// User
		if ( $this->getUser() != TTUUID::getZeroID() ) {
			$ulf = TTnew( 'UserListFactory' );
			$this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($this->getUser()),
															TTi18n::gettext('Invalid Employee')
														);
		}

		// Status
		if ( $this->getStatus() !== FALSE ) {
			$this->Validator->inArrayKey( 'status',
										  $this->getStatus(),
										  TTi18n::gettext( 'Incorrect Status' ),
										  $this->getOptions( 'status' )
			);
		}

		// Date
		if ( $this->getUserVerifiedDate() !== FALSE ) {
			$this->Validator->isDate( 'user_verified_date',
									  $this->getUserVerifiedDate(),
									  TTi18n::gettext( 'Incorrect Date' )
			);
		}

		// Authorization level
		if ( $this->getAuthorizationLevel() !== FALSE ) {
			$this->Validator->isNumeric( 'authorization_level',
										 $this->getAuthorizationLevel(),
										 TTi18n::gettext( 'Incorrect authorization level' )
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		$this->calcStatus();

		if ( $this->getStatus() == '' ) {
			$this->Validator->isTrue(		'status',
											FALSE,
											TTi18n::gettext('Status is invalid'));
		}

		if ( $this->getDeleted() == FALSE AND $this->getStatus() != 55 ) { //Declined
			//Check to make sure no critical severity exceptions exist.
			//Make sure we ignore the 'V1 - TimeSheet Not Verified' exception, as that could be critical and prevent them from ever verifying their timesheet.
			$elf = TTNew('ExceptionListFactory');
			$elf->getByCompanyIDAndUserIdAndPayPeriodIdAndSeverityAndNotTypeID( $this->getUserObject()->getCompany(), $this->getUser(), $this->getPayPeriod(), array(30), array( 'V1' ) );
			Debug::Text(' Critcal Severity Exceptions: '. $elf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			if ( $elf->getRecordCount() > 0 ) {
				$this->Validator->isTrue(	'exception',
											FALSE,
											TTi18n::gettext('Unable to verify this timesheet when critical severity exceptions exist in the pay period'));
			}
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		$this->calcStatus();

		if ( $this->getAuthorized() == TRUE ) {
			$this->setAuthorizationLevel( 0 );
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		//If status is pending auth (55=declined) delete all authorization history, because they could be re-verifying.
		if ( $this->getCurrentUser() != FALSE AND $this->getStatus() == 55 ) {
			$alf = TTnew( 'AuthorizationListFactory' );
			$alf->getByObjectTypeAndObjectId( 90, $this->getId() );
			if ( $alf->getRecordCount() > 0 ) {
				foreach( $alf as $a_obj ) {
					//Delete the record outright for now, as marking it as deleted causes transaction issues
					//and it never gets committed.
					$a_obj->Delete();
				}
			}
		}

		$time_sheet_verification_type_id = $this->getVerificationType();
		if ( $time_sheet_verification_type_id > 10 ) { //10 = Disabled

			$authorize_timesheet = FALSE;
			if ( $time_sheet_verification_type_id == 20 ) { //Employee Only
				$authorize_timesheet = TRUE;
			} elseif ( $time_sheet_verification_type_id == 30 ) { //Superior Only
				if ( $this->getStatus() == 30 AND $this->getCurrentUser() != FALSE ) { //Check on CurrentUser so we don't loop indefinitely through AuthorizationFactory.
					Debug::Text(' aAuthorizing TimeSheet as superior...', __FILE__, __LINE__, __METHOD__, 10);
					$authorize_timesheet = TRUE;
				}
			} elseif ( $time_sheet_verification_type_id == 40 ) { //Superior & Employee
				if ( $this->getStatus() == 30 AND $this->getCurrentUser() != FALSE AND $this->getCurrentUser() != $this->getUser() ) { //Check on CurrentUser so we don't loop indefinitely through AuthorizationFactory.
					Debug::Text(' bAuthorizing TimeSheet as superior...', __FILE__, __LINE__, __METHOD__, 10);
					$authorize_timesheet = TRUE;
				}
			}

			if ( $authorize_timesheet == TRUE ) {
				$af = TTnew( 'AuthorizationFactory' );
				$af->setCurrentUser( $this->getCurrentUser() );
				$af->setObjectType( 90 ); //TimeSheet
				$af->setObject( $this->getId() );
				$af->setAuthorized(TRUE);
				if ( $af->isValid() ) {
					$af->Save();
				}
			} else {
				Debug::Text('Not authorizing timesheet...', __FILE__, __LINE__, __METHOD__, 10);

				//Send initial Pending Authorization email to superiors. -- This should only happen on first save by the regular employee.
				AuthorizationFactory::emailAuthorizationOnInitialObjectSave( $this->getCurrentUser(), 90, $this->getId() );
			}

			if ( $authorize_timesheet == TRUE OR $this->getAuthorized() == TRUE ) {
				//Recalculate exceptions on the last day of pay period to remove any TimeSheet Not Verified exceptions.
				//Get user_date_id.
				if ( is_object( $this->getPayPeriodObject() ) ) {
					$flags = array(
									'meal' => FALSE,
									'undertime_absence' => FALSE,
									'break' => FALSE,
									'holiday' => FALSE,
									'schedule_absence' => FALSE,
									'absence' => FALSE,
									'regular' => FALSE,
									'overtime' => FALSE,
									'premium' => FALSE,
									'accrual' => FALSE,

									'exception' => TRUE,
									//Exception options
									'exception_premature' => FALSE, //Calculates premature exceptions
									'exception_future' => FALSE, //Calculates exceptions in the future.

									//Calculate policies for future dates.
									'future_dates' => FALSE, //Calculates dates in the future.
									'past_dates' => FALSE, //Calculates dates in the past. This is only needed when Pay Formulas that use averaging are enabled?*
									);

					$cp = TTNew('CalculatePolicy');
					$cp->setFlag( $flags );
					$cp->setUserObject( $this->getUserObject() );
					$cp->calculate( $this->getPayPeriodObject()->getEndDate() ); //This sets timezone itself.
					$cp->Save();
				} else {
					Debug::Text('No Pay Period found...', __FILE__, __LINE__, __METHOD__, 10);
				}
			} else {
				Debug::Text('Not recalculating last day of pay period...', __FILE__, __LINE__, __METHOD__, 10);
			}
		} else {
			Debug::Text('TimeSheet Verification is disabled...', __FILE__, __LINE__, __METHOD__, 10);
		}

		return TRUE;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param null $include_columns
	 * @param string $permission_children_ids UUID
	 * @return array
	 */
	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = NULL ) {
		$data = array();
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'first_name':
						case 'last_name':
						case 'title':
						case 'user_group':
						case 'default_branch':
						case 'default_department':
						case 'user_id':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'start_date':
						case 'end_date':
						case 'transaction_date':
						case 'window_start_date':
						case 'window_end_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', TTDate::strtotime( $this->getColumn( $variable ) ) );
							break;
						case 'status':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getPermissionColumns( $data, $this->getColumn( 'user_id' ), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		//Should the object_id be the pay period ID instead, that way its easier to find the audit logs?
		if ( is_object( $this->getPayPeriodObject() ) ) {
			return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('TimeSheet Verify').' - '. TTi18n::getText('Employee') .': '. UserListFactory::getFullNameById( $this->getUser() ) .' '. TTi18n::getText('Pay Period') .': '.	TTDate::getDate('DATE', $this->getPayPeriodObject()->getStartDate() ) .' -> '. TTDate::getDate('DATE', $this->getPayPeriodObject()->getEndDate() ), NULL, $this->getTable() );
		}
	}
}
?>
