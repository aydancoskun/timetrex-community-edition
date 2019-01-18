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
class PayPeriodFactory extends Factory {
	protected $table = 'pay_period';
	protected $pk_sequence_name = 'pay_period_id_seq'; //PK Sequence name
	protected $old_status_id = NULL;

	var $pay_period_schedule_obj = NULL;

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
										10 => TTi18n::gettext('OPEN'),
										12 => TTi18n::gettext('Locked - Pending Approval'), //Go to this state as soon as date2 is passed
										//15 => TTi18n::gettext('Locked - Pending Transaction'), //Go to this as soon as approved, or 48hrs before transaction date.
										20 => TTi18n::gettext('CLOSED'), //Once paid
										30 => TTi18n::gettext('Post Adjustment')
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-type' => TTi18n::gettext('Type'),
										'-1020-status' => TTi18n::gettext('Status'),
										'-1030-pay_period_schedule' => TTi18n::gettext('Pay Period Schedule'),

										'-1040-start_date' => TTi18n::gettext('Start Date'),
										'-1050-end_date' => TTi18n::gettext('End Date'),
										'-1060-transaction_date' => TTi18n::gettext('Transaction Date'),

										'-1500-total_punches' => TTi18n::gettext('Punches'),
										'-1501-total_manual_timesheets' => TTi18n::gettext('Manual TimeSheets'),
										'-1502-total_absences' => TTi18n::gettext('Absences'),
										'-1505-pending_requests' => TTi18n::gettext('Pending Requests'),
										'-1510-exceptions_critical' => TTi18n::gettext('Critical'),
										'-1510-exceptions_high' => TTi18n::gettext('High'),
										'-1512-exceptions_medium' => TTi18n::gettext('Medium'),
										'-1514-exceptions_low' => TTi18n::gettext('Low'),
										'-1520-verified_timesheets' => TTi18n::gettext('Verified'),
										'-1522-pending_timesheets' => TTi18n::gettext('Pending'),
										'-1524-total_timesheets' => TTi18n::gettext('Total'),
										'-1530-ps_amendments' => TTi18n::gettext('PS Amendments'),
										'-1540-pay_stubs' => TTi18n::gettext('Pay Stubs'),
										'-1542-pay_stubs_open' => TTi18n::gettext('Pay Stubs (OPEN)'),

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
								'pay_period_schedule',
								'type',
								'status',
								'start_date',
								'end_date',
								'transaction_date'
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'start_date',
								'end_date',
								'transaction_date',
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
											'company_id' => 'Company',
											'status_id' => 'Status',
											'status' => FALSE,
											'type_id' => FALSE,
											'type' => FALSE,
											'pay_period_schedule_id' => 'PayPeriodSchedule',
											'pay_period_schedule' => FALSE,
											'start_date' => 'StartDate',
											'end_date' => 'EndDate',
											'transaction_date' => 'TransactionDate',
											//'advance_transaction_date' => 'AdvanceTransactionDate',
											//'advance_transaction_date' => 'Primary',
											//'is_primary' => 'PayStubStatus',
											//'tainted' => 'Tainted',
											//'tainted_date' => 'TaintedDate',
											//'tainted_by' => 'TaintedBy',

											'total_punches' => 'TotalPunches',
											'total_manual_timesheets' => 'TotalManualTimeSheets',
											'total_absences' => 'TotalAbsences',
											'pending_requests' => 'PendingRequests',
											'exceptions_critical' => 'Exceptions',
											'exceptions_high' => 'Exceptions',
											'exceptions_medium' => 'Exceptions',
											'exceptions_low' => 'Exceptions',
											'verified_timesheets' => 'TimeSheets',
											'pending_timesheets' => 'TimeSheets',
											'total_timesheets' => 'TimeSheets',
											'ps_amendments' => 'PayStubAmendments',
											'pay_stubs' => 'PayStubs',
											'pay_stubs_open' => 'PayStubsOpen',

											'deleted' => 'Deleted',
											);
			return $variable_function_map;
	}

	/**
	 * @return bool|null
	 */
	function getPayPeriodScheduleObject() {
		if ( is_object($this->pay_period_schedule_obj) ) {
			return $this->pay_period_schedule_obj;
		} else {
			$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
			//$this->pay_period_schedule_obj = $ppslf->getById( $this->getPayPeriodSchedule() )->getCurrent();
			$ppslf->getById( $this->getPayPeriodSchedule() );
			if ( $ppslf->getRecordCount() > 0 ) {
				$this->pay_period_schedule_obj = $ppslf->getCurrent();
				return $this->pay_period_schedule_obj;
			}

			return FALSE;
		}
	}

	/**
	 * @return mixed
	 */
	function getCompany() {
		return $this->getGenericDataValue( 'company_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setCompany( $value) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'company_id', $value );
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
		Debug::Text('Current Status: '. $this->getStatus() .' New Status: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		$this->old_status_id = $this->getStatus();
		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPayPeriodSchedule() {
		return $this->getGenericDataValue( 'pay_period_schedule_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPayPeriodSchedule( $value) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'pay_period_schedule_id', $value );
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function isValidStartDate( $epoch) {
		if ( $this->isNew() ) {
			$id = TTUUID::getZeroID();
		} else {
			$id = $this->getId();
		}

		$ph = array(
					'pay_period_schedule_id' => TTUUID::castUUID($this->getPayPeriodSchedule()),
					'start_date' => $this->db->BindTimeStamp($epoch),
					'end_date' => $this->db->BindTimeStamp($epoch),
					'id' => TTUUID::castUUID($id),
					);

		//Used to have LIMIT 1 at the end, but GetOne() should do that for us.
		$query = 'select id from '. $this->getTable() .'
					where	pay_period_schedule_id = ?
						AND start_date <= ?
						AND end_date >= ?
						AND deleted=0
						AND id != ?
					';
		$id = $this->db->GetOne($query, $ph);
		Debug::Arr($id, 'Pay Period ID of conflicting pay period: '. $epoch, __FILE__, __LINE__, __METHOD__, 10);

		if ( $id === FALSE ) {
			//Debug::Text('aReturning TRUE!', __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		} else {
			if ($id == $this->getId() ) {
				//Debug::Text('bReturning TRUE!', __FILE__, __LINE__, __METHOD__, 10);
				return TRUE;
			}
		}

		Debug::Text('Returning FALSE!', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	/**
	 * @return bool
	 */
	function isConflicting() {
		Debug::Text('PayPeriod Schedule ID: '. $this->getPayPeriodSchedule() .' DateStamp: '. $this->getStartDate(), __FILE__, __LINE__, __METHOD__, 10);
		//Make sure we're not conflicting with any other schedule shifts.
		$pplf = TTnew( 'PayPeriodListFactory' );
		$pplf->getConflictingByPayPeriodScheduleIdAndStartDateAndEndDate( $this->getPayPeriodSchedule(), $this->getStartDate(), $this->getEndDate(), TTUUID::castUUID($this->getID()) );
		if ( $pplf->getRecordCount() > 0 ) {
			foreach( $pplf as $conflicting_pp_obj ) {
				if ( $conflicting_pp_obj->isNew() === FALSE
						AND $conflicting_pp_obj->getId() != $this->getId() ) {
					Debug::text('Conflicting Pay Period ID: '. $conflicting_pp_obj->getId() .' PayPeriod ID: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	/**
	 * @param int $filter_start_date EPOCH
	 * @param int $filter_end_date EPOCH
	 * @param bool $include_pay_period_id
	 * @return array|bool
	 */
	function getPayPeriodDates( $filter_start_date = NULL, $filter_end_date = NULL, $include_pay_period_id = FALSE ) {
		//Debug::Text('Start Date: '. TTDate::getDate('DATE', $this->getStartDate()) .' End Date: '. TTDate::getDate('DATE', $this->getEndDate()) .' Filter: Start: '. TTDate::getDate('DATE', $filter_start_date ) .' End: '. TTDate::getDate('DATE', $filter_end_date), __FILE__, __LINE__, __METHOD__, 10);
		if ( $this->getStartDate() > 0 AND $this->getEndDate() > 0 ) {
			$retarr = array();

			for( $i = (int)$this->getStartDate(); $i <= (int)$this->getEndDate(); $i += 93600) {
				$i = TTDate::getBeginDayEpoch($i);

				if ( ( $filter_start_date == '' OR $filter_start_date <= $i )
						AND ( $filter_end_date == '' OR $filter_end_date >= $i ) ) {
					if ( $include_pay_period_id == TRUE ) {
						$retarr[TTDate::getAPIDate('DATE', $i)] = $this->getID();
					} else {
						$retarr[] = TTDate::getAPIDate('DATE', $i);
					}
				} //else { //Debug::Text('Filter didnt match!', __FILE__, __LINE__, __METHOD__, 10);
			}

			//Debug::Arr($retarr, 'Pay Period Dates: ', __FILE__, __LINE__, __METHOD__, 10);

			return $retarr;
		}

		return FALSE;
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getStartDate( $raw = FALSE ) {
		$value = $this->getGenericDataValue( 'start_date' );
		if ( $value !== FALSE ) {
			if ( $raw === TRUE ) {
				return $value;
			} else {
				//return $this->db->UnixTimeStamp( $this->data['start_date'] );
				//strtotime is MUCH faster than UnixTimeStamp
				//Must use ADODB for times pre-1970 though.
				return TTDate::strtotime( $value );
			}
		}

		return FALSE;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setStartDate( $value) {
		$value = ( !is_int($value) ) ? trim($value) : $value; //Dont trim integer values, as it changes them to strings.

		if ( $value != '' ) {
			//Make sure all pay periods start at the first second of the day.
			$value = TTDate::getTimeLockedDate( strtotime('00:00:00', $value), $value);
		}
		return $this->setGenericDataValue( 'start_date', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getEndDate( $raw = FALSE ) {
		$value = $this->getGenericDataValue( 'end_date' );
		if ( $value !== FALSE ) {
			if ( $raw === TRUE ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return FALSE;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setEndDate( $value) {
		$value = ( !is_int($value) ) ? trim($value) : $value; //Dont trim integer values, as it changes them to strings.

		if ( $value != '' ) {
			//Make sure all pay periods end at the last second of the day.
			$value = TTDate::getTimeLockedDate( strtotime('23:59:59', $value), $value);
		}
		return $this->setGenericDataValue( 'end_date', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getTransactionDate( $raw = FALSE ) {
		$value = $this->getGenericDataValue( 'transaction_date' );
		if ( $value !== FALSE ) {
			if ( $raw === TRUE ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return FALSE;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setTransactionDate( $value) {
		$value = ( !is_int($value) ) ? trim($value) : $value; //Dont trim integer values, as it changes them to strings.

		if ( $value != '' ) {
			//Make sure all pay periods transact at noon.
			$value = TTDate::getTimeLockedDate( strtotime('12:00:00', $value), $value);

			//Unless they are on the same date as the end date, then it should match that.
			if ( $this->getEndDate() != '' AND $this->getEndDate() > $value ) {
				$value = $this->getEndDate();
			}
		}
		return $this->setGenericDataValue( 'transaction_date', $value );
	}

	/*
	function getAdvanceEndDate( $raw = FALSE ) {
		if ( isset($this->data['advance_end_date']) ) {
			return TTDate::strtotime($this->data['advance_end_date']);
		}

		return FALSE;
	}
	function setAdvanceEndDate($epoch) {
		$epoch = ( !is_int($epoch) ) ? trim($epoch) : $epoch; //Dont trim integer values, as it changes them to strings.

		if	(	$epoch == FALSE
				OR
				$this->Validator->isDate(		'advance_end_date',
												$epoch,
												TTi18n::gettext('Incorrect advance end date')) ) {

			$this->setGenericDataValue( 'advance_end_date', $epoch );

			return TRUE;
		}

		return FALSE;
	}

	function getAdvanceTransactionDate() {
		if ( isset($this->data['advance_transaction_date']) ) {
			return TTDate::strtotime($this->data['advance_transaction_date']);
			//if ( (int)$this->data['advance_transaction_date'] == 0 ) {
			//	return strtotime( $this->data['advance_transaction_date'] );
			//} else {
			//	return $this->data['advance_transaction_date'];
			//}
		}

		return FALSE;
	}
	function setAdvanceTransactionDate($epoch) {
		$epoch = ( !is_int($epoch) ) ? trim($epoch) : $epoch; //Dont trim integer values, as it changes them to strings.

		if	(	$epoch == FALSE
				OR
				$this->Validator->isDate(		'advance_transaction_date',
												$epoch,
												TTi18n::gettext('Incorrect advance transaction date')) ) {

			$this->setGenericDataValue( 'advance_transaction_date', $epoch );

			return TRUE;
		}

		return FALSE;
	}
	*/

	/**
	 * @return bool
	 */
	function getPrimary() {
		return $this->fromBool( $this->getGenericDataValue( 'is_primary' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPrimary( $value) {
		return $this->setGenericDataValue( 'is_primary', $this->toBool($value) );
	}

	/**
	 * @param $status
	 * @return bool
	 */
	function setPayStubStatus( $status, $dry_run = FALSE ) {
		Debug::text('setPayStubStatus: '. $status, __FILE__, __LINE__, __METHOD__, 10);

		$this->StartTransaction();

		$pslf = TTnew( 'PayStubListFactory' );
		$pslf->getByPayPeriodId( $this->getId() );
		foreach($pslf as $pay_stub) {
			//Don't switch Opening Balance (100) pay stubs to PAID.
			if ( $pay_stub->getStatus() != 100 AND $pay_stub->getStatus() != $status ) {
				Debug::text('Changing Status of Pay Stub ID: '. $pay_stub->getId(), __FILE__, __LINE__, __METHOD__, 10);
				$pay_stub->setStatus($status);
				if ( $pay_stub->isValid() ) {
					if ( $dry_run != TRUE ) { //Dry-run can be used to validate that all pay stubs can at least be changed to PAID and saved, so we can give better validation error messages to the user on closing of pay periods.
						$pay_stub->Save();
					}
				} else {
					Debug::text('  ERROR: Changing pay stub to paid failed, rolling back transaction!', __FILE__, __LINE__, __METHOD__, 10);
					$this->FailTransaction();
					$this->CommitTransaction();
					return FALSE;
				}
			}
		}

		$this->CommitTransaction();

		return TRUE;
	}

	function purgePayStubAmendments() {
		if ( $this->getStatus() == 20 ) { //20=Closed
			//Don't wrap this in a transaction in case something fails the pay period can't be closed then.
			//$this->StartTransaction();

			$psalf = TTnew( 'PayStubAmendmentListFactory' );
			$psalf->getByCompanyIdAndPayPeriodScheduleIdAndStatusAndBeforeStartDate( $this->getCompany(), $this->getPayPeriodSchedule(), 50, $this->getStartDate() ); //50=Active
			Debug::text( '  Active Pay Stub Amendments before this pay period: '. $psalf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
			if ( $psalf->getRecordCount() > 0 ) {
				foreach ( $psalf as $psa_obj ) {
					//Make sure the pay stub amendment isn't in use by a pay stub.
					if ( is_object( $psa_obj->getPayStubObject() ) AND $psa_obj->getPayStubObject()->getStatus() == 40 ) {
						//Help fix PSA that didn't get marked PAID previously for whatever reason. 
						$psa_obj->setEnablePayStubStatusChange( TRUE ); //Tell PSA that its the pay stub changing the status, so we can ignore some validation checks.
						$psa_obj->setStatus( 55 );
						if ( $psa_obj->isValid() == TRUE ) {
							Debug::text( '  Found pay stub amendment assigned to pay stub, marking as PAID: ' . $psa_obj->getId() . ' User ID: ' . $psa_obj->getUser(), __FILE__, __LINE__, __METHOD__, 10 );
							$psa_obj->Save();
						}
					} else {
						$psa_obj->setDeleted( TRUE );
						if ( $psa_obj->isValid() == TRUE ) {
							Debug::text( '  Deleting ACTIVE pay stub amendment prior to this pay period: ' . $psa_obj->getId() . ' User ID: ' . $psa_obj->getUser(), __FILE__, __LINE__, __METHOD__, 10 );
							$psa_obj->Save();
						} else {
							Debug::text( '  ERROR: Deleting ACTIVE pay stub amendment prior to this pay period failed, rolling back transaction! ID: ' . $psa_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
							//$this->FailTransaction();
							//$this->CommitTransaction();

							return FALSE;
						}
					}
				}
			}

			//$this->CommitTransaction();
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function getTainted() {
		return $this->fromBool( $this->getGenericDataValue( 'tainted' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTainted( $value) {
		return $this->setGenericDataValue( 'tainted', $this->toBool($value) );
	}

	/**
	 * @return bool|mixed
	 */
	function getTaintedDate() {
		return $this->getGenericDataValue( 'tainted_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setTaintedDate( $value = NULL) {
		$value = ( !is_int($value) ) ? trim($value) : $value; //Dont trim integer values, as it changes them to strings.

		if ($value == NULL) {
			$value = TTDate::getTime();
		}
		return $this->setGenericDataValue( 'tainted_date', $value );

	}

	/**
	 * @return bool|mixed
	 */
	function getTaintedBy() {
		return $this->getGenericDataValue( 'tainted_by' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setTaintedBy( $value = NULL) {
		$value = trim($value);

		if ( empty($value) ) {
			global $current_user;

			if ( is_object($current_user) ) {
				$value = $current_user->getID();
			} else {
				return FALSE;
			}
		}
		return $this->setGenericDataValue( 'tainted_by', $value );
	}

	/**
	 * @return bool
	 */
	function getTimeSheetVerifyType() {
		if ( is_object( $this->getPayPeriodScheduleObject() ) ) {
			return $this->getPayPeriodScheduleObject()->getTimeSheetVerifyType();
		}

		return FALSE;
	}

	/**
	 * @return bool|int
	 */
	function getTimeSheetVerifyWindowStartDate() {
		if ( is_object( $this->getPayPeriodScheduleObject() ) ) {
			//Since PP end dates are usually at 11:59:59PM, add one second to the PP end date prior to calculating the timesheet verification window start date,
			//so we don't confuse people by saying it starts on Aug 22nd with its really Aug 22 @ 11:59:59.
			return (int)( ( $this->getEndDate() + 1 ) - ( $this->getPayPeriodScheduleObject()->getTimeSheetVerifyBeforeEndDate() * 86400 ) );
		}

		return $this->getEndDate();
	}

	/**
	 * @return bool|int
	 */
	function getTimeSheetVerifyWindowEndDate() {
		if ( is_object( $this->getPayPeriodScheduleObject() ) ) {
			return (int)( $this->getTransactionDate() - ( $this->getPayPeriodScheduleObject()->getTimeSheetVerifyBeforeTransactionDate() * 86400 ) );
		}

		return $this->getTransactionDate();
	}

	/**
	 * @return bool
	 */
	function getIsLocked() {
		if ( $this->getStatus() == 10 OR $this->getStatus() == 30 OR $this->isNew() == TRUE ) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * @param bool $include_schedule_name
	 * @return string
	 */
	function getName( $include_schedule_name = FALSE) {
		$schedule_name = NULL;
		if ( $include_schedule_name == TRUE AND is_object( $this->getPayPeriodScheduleObject() ) ) {
			$schedule_name = '('. $this->getPayPeriodScheduleObject()->getName() .') ';
		}

		$retval = $schedule_name . TTDate::getDate('DATE', $this->getStartDate() ).' -> '. TTDate::getDate('DATE', $this->getEndDate() );

		return $retval;
	}

	/**
	 * @return bool
	 */
	function getEnableImportOrphanedData() {
		if ( isset($this->import_orphaned_data) ) {
			return $this->import_orphaned_data;
		}

		return FALSE;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableImportOrphanedData( $bool) {
		$this->import_orphaned_data = $bool;

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function getEnableImportData() {
		if ( isset($this->import_data) ) {
			return $this->import_data;
		}

		return FALSE;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableImportData( $bool) {
		$this->import_data = $bool;

		return TRUE;
	}

	//Check to make sure previous pay period is closed.

	/**
	 * @return bool
	 */
	function isPreviousPayPeriodClosed() {
		$pplf = TTnew('PayPeriodListFactory');
		$pplf->getPreviousPayPeriodById( $this->getID() );
		if ( $pplf->getRecordCount() > 0 ) {
			$pp_obj = $pplf->getCurrent();
			Debug::text(' Previous Pay Period ID: '. $pp_obj->getID() .' Status: '. $pp_obj->getStatus(), __FILE__, __LINE__, __METHOD__, 10);
			if ( $pp_obj->getStatus() == 10 OR $pp_obj->getStatus() == 12 ) {
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function isFirstPayPeriodInYear() {
		$pplf = TTnew('PayPeriodListFactory');
		$pplf->getPreviousPayPeriodById( $this->getID() );
		if ( $pplf->getRecordCount() > 0 ) {
			$pp_obj = $pplf->getCurrent();
			Debug::text(' Previous Pay Period ID: '. $pp_obj->getID() .' Transaction Date: '. $pp_obj->getTransactionDate(), __FILE__, __LINE__, __METHOD__, 10);
			if ( TTDate::getYear( $pp_obj->getTransactionDate() ) != TTDate::getYear( $this->getTransactionDate() ) ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	//Imports only data not assigned to other pay periods

	/**
	 * @return bool
	 */
	function importOrphanedData() {
		//Make sure current pay period isnt closed.
		if ( $this->getStatus() == 20 ) {
			return FALSE;
		}

		$pps_obj = $this->getPayPeriodScheduleObject();

		if ( is_object( $pps_obj ) AND is_array( $pps_obj->getUser() ) AND count( $pps_obj->getUser() ) > 0 ) {
			$pplf = TTnew('PayPeriodListFactory');
			$pplf->StartTransaction();

			//UserDateTotal
			/** @var UserDateTotalFactory $f */
			$f = TTnew('UserDateTotalFactory');
			$ph = array(
						'start_date' => $this->db->BindDate( $this->getStartDate() ),
						'end_date' => $this->db->BindDate( $this->getEndDate() ),
						);
			$query = 'UPDATE '. $f->getTable() .' SET pay_period_id = \''. TTUUID::castUUID($this->getID()) .'\' WHERE ( pay_period_id = \''. TTUUID::getZeroID() .'\' OR pay_period_id IS NULL ) AND date_stamp >= ? AND date_stamp <= ? AND user_id in ('. $this->getListSQL( $pps_obj->getUser(), $ph, 'uuid') .')';
			$f->db->Execute( $query, $ph );
			Debug::Arr($ph, 'UserDateTotal Query: '. $query .' Affected Rows: '. $f->db->Affected_Rows(), __FILE__, __LINE__, __METHOD__, 10);

			//PunchControl
			$f = TTnew('PunchControlFactory');
			$ph = array(
						'start_date' => $this->db->BindDate( $this->getStartDate() ),
						'end_date' => $this->db->BindDate( $this->getEndDate() ),
						);
			$query = 'UPDATE '. $f->getTable() .' SET pay_period_id = \''. TTUUID::castUUID($this->getID()) .'\' WHERE ( pay_period_id = \''. TTUUID::getZeroID() .'\' OR pay_period_id IS NULL ) AND date_stamp >= ? AND date_stamp <= ? AND user_id in ('. $this->getListSQL( $pps_obj->getUser(), $ph, 'uuid') .')';
			$f->db->Execute( $query, $ph );
			Debug::Arr($ph, 'PunchControl Query: '. $query .' Affected Rows: '. $f->db->Affected_Rows(), __FILE__, __LINE__, __METHOD__, 10);

			//Schedule
			$f = TTnew('ScheduleFactory');
			$ph = array(
						'start_date' => $this->db->BindDate( $this->getStartDate() ),
						'end_date' => $this->db->BindDate( $this->getEndDate() ),
						);
			$query = 'UPDATE '. $f->getTable() .' SET pay_period_id = \''. TTUUID::castUUID($this->getID()) .'\' WHERE ( pay_period_id = \''. TTUUID::getZeroID() .'\' OR pay_period_id IS NULL ) AND date_stamp >= ? AND date_stamp <= ? AND user_id in ('. $this->getListSQL( $pps_obj->getUser(), $ph, 'uuid') .')';
			$f->db->Execute( $query, $ph );
			Debug::Arr($ph, 'Schedule Query: '. $query .' Affected Rows: '. $f->db->Affected_Rows(), __FILE__, __LINE__, __METHOD__, 10);

			//Requests
			$f = TTnew('RequestFactory');
			$ph = array(
						'start_date' => $this->db->BindDate( $this->getStartDate() ),
						'end_date' => $this->db->BindDate( $this->getEndDate() ),
						);
			$query = 'UPDATE '. $f->getTable() .' SET pay_period_id = \''. TTUUID::castUUID($this->getID()) .'\' WHERE ( pay_period_id = \''. TTUUID::getZeroID() .'\' OR pay_period_id IS NULL ) AND date_stamp >= ? AND date_stamp <= ? AND user_id in ('. $this->getListSQL( $pps_obj->getUser(), $ph, 'uuid') .')';
			$f->db->Execute( $query, $ph );
			Debug::Arr($ph, 'Request Query: '. $query .' Affected Rows: '. $f->db->Affected_Rows(), __FILE__, __LINE__, __METHOD__, 10);

			//Exceptions
			$f = TTnew('ExceptionFactory');
			$ph = array(
						'start_date' => $this->db->BindDate( $this->getStartDate() ),
						'end_date' => $this->db->BindDate( $this->getEndDate() ),
						);
			$query = 'UPDATE '. $f->getTable() .' SET pay_period_id = \''. TTUUID::castUUID($this->getID()) .'\' WHERE ( pay_period_id = \''. TTUUID::getZeroID() .'\' OR pay_period_id IS NULL ) AND date_stamp >= ? AND date_stamp <= ? AND user_id in ('. $this->getListSQL( $pps_obj->getUser(), $ph, 'uuid') .')';
			$f->db->Execute( $query, $ph );
			Debug::Arr($ph, 'Exception Query: '. $query .' Affected Rows: '. $f->db->Affected_Rows(), __FILE__, __LINE__, __METHOD__, 10);

			//PayStubs
			$f = TTnew('PayStubFactory');
			$ph = array(
						'start_date' => $this->db->BindDate( $this->getStartDate() ),
						'end_date' => $this->db->BindDate( $this->getEndDate() ),
						);
			$query = 'UPDATE '. $f->getTable() .' SET pay_period_id = \''. TTUUID::castUUID($this->getID()) .'\' WHERE ( pay_period_id = \''. TTUUID::getZeroID() .'\' OR pay_period_id IS NULL ) AND start_date >= ? AND end_date <= ? AND user_id in ('. $this->getListSQL( $pps_obj->getUser(), $ph, 'uuid') .')';
			$f->db->Execute( $query, $ph );
			Debug::Arr($ph, 'PayStub Query: '. $query .' Affected Rows: '. $f->db->Affected_Rows(), __FILE__, __LINE__, __METHOD__, 10);

			TTLog::addEntry( $this->getId(), 500, TTi18n::getText('Import Orphan Data: Pay Period') .' - '. TTi18n::getText('Start Date') .': '. TTDate::getDate('DATE+TIME', $this->getStartDate() ) .' '. TTi18n::getText('End Date') .': '. TTDate::getDate('DATE+TIME', $this->getEndDate() ) .' '. TTi18n::getText('Transaction Date') .': '. TTDate::getDate('DATE+TIME', $this->getTransactionDate() ), NULL, $this->getTable(), $this );

			//$pplf->FailTransaction();
			$pplf->CommitTransaction();

			return TRUE;
		}

		return FALSE;
	}

	//Imports all data from other pay periods into this one.

	/**
	 * @param bool $user_ids
	 * @param bool $pay_period_id
	 * @return bool
	 */
	function importData( $user_ids = FALSE, $pay_period_id = FALSE ) {
		$pps_obj = $this->getPayPeriodScheduleObject();

		//Make sure current pay period isnt closed.
		if ( $this->getStatus() == 20 ) {
			return FALSE;
		}

		if ( $user_ids == FALSE ) {
			$user_ids = $pps_obj->getUser();
		} else {
			Debug::Text('  Custom user_ids specified, only importing for them...', __FILE__, __LINE__, __METHOD__, 10);
			if ( !is_array( $user_ids )) {
				$user_ids = array($user_ids);
			}
		}

		$pay_period_ids = array( TTUUID::getZeroID() ); //Always include a 0 pay_period_id so orphaned data is pulled over too.

		$pplf = TTnew('PayPeriodListFactory');
		$pplf->StartTransaction();

		if ( $pay_period_id == FALSE ) {
			//Get a list of all pay periods that are not closed != 20, so we can restrict the below queries to just those pay periods.
			$pplf->getByCompanyIdAndStatus( $this->getCompany(), array(10, 12, 30) );
			if ( $pplf->getRecordCount() ) {
				foreach( $pplf as $pp_obj ) {
					$pay_period_ids[] = $pp_obj->getId();
				}
			}
			Debug::Text('  Found non-Closed Pay Periods: '. $pplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		} else {
			Debug::Text('  Custom pay_period_ids specified, only importing for them...', __FILE__, __LINE__, __METHOD__, 10);

			$pplf->getByIdAndCompanyId( $pay_period_id, $this->getCompany() );
			unset($pay_period_id);
			if ( $pplf->getRecordCount() ) {
				foreach( $pplf as $pp_obj ) {
					if ( in_array( $pp_obj->getStatus(), array( 10, 12, 30) ) ) {
						$pay_period_ids[] = $pp_obj->getId();
					} else {
						Debug::Text('  Skipping closed pay period...', __FILE__, __LINE__, __METHOD__, 10);
					}
				}
			}
		}

		if ( isset($pay_period_ids) AND is_array($pay_period_ids) AND count($pay_period_ids) > 0 AND $this->getID() != '' ) {
			//UserDateTotal
			$f = TTnew('UserDateTotalFactory');
			$ph = array(
						'start_date' => $this->db->BindDate( $this->getStartDate() ),
						'end_date' => $this->db->BindDate( $this->getEndDate() ),
						);
			$query = 'UPDATE '. $f->getTable() .' SET pay_period_id = \''. TTUUID::castUUID($this->getID()) .'\' WHERE pay_period_id != \''. TTUUID::castUUID($this->getID()) .'\' AND date_stamp >= ? AND date_stamp <= ? AND user_id in ('. $this->getListSQL( $user_ids, $ph, 'uuid') .') AND pay_period_id in ('. $this->getListSQL( $pay_period_ids, $ph, 'uuid') .')';
			$f->db->Execute( $query, $ph );
			Debug::Arr($ph, 'UserDateTotal Query: '. $query .' Affected Rows: '. $f->db->Affected_Rows(), __FILE__, __LINE__, __METHOD__, 10);


			//PunchControl
			$f = TTnew('PunchControlFactory');
			$ph = array(
						'start_date' => $this->db->BindDate( $this->getStartDate() ),
						'end_date' => $this->db->BindDate( $this->getEndDate() ),
						);
			$query = 'UPDATE '. $f->getTable() .' SET pay_period_id = \''. TTUUID::castUUID($this->getID()) .'\' WHERE pay_period_id != \''. TTUUID::castUUID($this->getID()) .'\' AND date_stamp >= ? AND date_stamp <= ? AND user_id in ('. $this->getListSQL( $user_ids, $ph, 'uuid') .') AND pay_period_id in ('. $this->getListSQL( $pay_period_ids, $ph, 'uuid') .')';
			$f->db->Execute( $query, $ph );
			Debug::Arr($ph, 'PunchControl Query: '. $query .' Affected Rows: '. $f->db->Affected_Rows(), __FILE__, __LINE__, __METHOD__, 10);


			//Schedule
			$f = TTnew('ScheduleFactory');
			$ph = array(
						'start_date' => $this->db->BindDate( $this->getStartDate() ),
						'end_date' => $this->db->BindDate( $this->getEndDate() ),
						);
			$query = 'UPDATE '. $f->getTable() .' SET pay_period_id = \''. TTUUID::castUUID($this->getID()) .'\' WHERE pay_period_id != \''. TTUUID::castUUID($this->getID()) .'\' AND date_stamp >= ? AND date_stamp <= ? AND user_id in ('. $this->getListSQL( $user_ids, $ph, 'uuid') .') AND pay_period_id in ('. $this->getListSQL( $pay_period_ids, $ph, 'uuid') .')';
			$f->db->Execute( $query, $ph );
			Debug::Arr($ph, 'Schedule Query: '. $query .' Affected Rows: '. $f->db->Affected_Rows(), __FILE__, __LINE__, __METHOD__, 10);


			//Requests
			$f = TTnew('RequestFactory');
			$ph = array(
						'start_date' => $this->db->BindDate( $this->getStartDate() ),
						'end_date' => $this->db->BindDate( $this->getEndDate() ),
						);
			$query = 'UPDATE '. $f->getTable() .' SET pay_period_id = \''. TTUUID::castUUID($this->getID()) .'\' WHERE pay_period_id != \''. TTUUID::castUUID($this->getID()) .'\' AND date_stamp >= ? AND date_stamp <= ? AND user_id in ('. $this->getListSQL( $user_ids, $ph, 'uuid') .') AND pay_period_id in ('. $this->getListSQL( $pay_period_ids, $ph, 'uuid') .')';
			$f->db->Execute( $query, $ph );
			Debug::Arr($ph, 'Request Query: '. $query .' Affected Rows: '. $f->db->Affected_Rows(), __FILE__, __LINE__, __METHOD__, 10);


			//Exceptions
			$f = TTnew('ExceptionFactory');
			$ph = array(
						'start_date' => $this->db->BindDate( $this->getStartDate() ),
						'end_date' => $this->db->BindDate( $this->getEndDate() ),
						);
			$query = 'UPDATE '. $f->getTable() .' SET pay_period_id = \''. TTUUID::castUUID($this->getID()) .'\' WHERE pay_period_id != \''. TTUUID::castUUID($this->getID()) .'\' AND date_stamp >= ? AND date_stamp <= ? AND user_id in ('. $this->getListSQL( $user_ids, $ph, 'uuid') .') AND pay_period_id in ('. $this->getListSQL( $pay_period_ids, $ph, 'uuid') .')';
			$f->db->Execute( $query, $ph );
			Debug::Arr($ph, 'Exception Query: '. $query .' Affected Rows: '. $f->db->Affected_Rows(), __FILE__, __LINE__, __METHOD__, 10);

			//PayStubs
			$f = TTnew('PayStubFactory');
			$ph = array(
						'start_date' => $this->db->BindDate( $this->getStartDate() ),
						'end_date' => $this->db->BindDate( $this->getEndDate() ),
						);
			$query = 'UPDATE '. $f->getTable() .' SET pay_period_id = \''. TTUUID::castUUID($this->getID()) .'\' WHERE ( pay_period_id = \''. TTUUID::getZeroID() .'\' OR pay_period_id IS NULL ) AND start_date >= ? AND end_date <= ? AND user_id in ('. $this->getListSQL( $user_ids, $ph, 'uuid') .')';
			$f->db->Execute( $query, $ph );
			Debug::Arr($ph, 'PayStub Query: '. $query .' Affected Rows: '. $f->db->Affected_Rows(), __FILE__, __LINE__, __METHOD__, 10);

			TTLog::addEntry( $this->getId(), 500, TTi18n::getText('Import Data: Pay Period') .' - '. TTi18n::getText('Start Date') .': '. TTDate::getDate('DATE+TIME', $this->getStartDate() ) .' '. TTi18n::getText('End Date') .': '. TTDate::getDate('DATE+TIME', $this->getEndDate() ) .' '. TTi18n::getText('Transaction Date') .': '. TTDate::getDate('DATE+TIME', $this->getTransactionDate() ), NULL, $this->getTable(), $this );
		} else {
			Debug::Text('ERROR: Unable to import data into pay period...', __FILE__, __LINE__, __METHOD__, 10);
		}

		//$pplf->FailTransaction();
		$pplf->CommitTransaction();

		return TRUE;
	}

	//Delete all data assigned to this pay period.

	/**
	 * @return bool
	 */
	function deleteData() {
		//Make sure current pay period isnt closed.
		if ( $this->getStatus() == 20 ) {
			return FALSE;
		}

		$pplf = TTnew('PayPeriodListFactory');
		$pplf->StartTransaction();

		if ( $this->getID() != '' ) {
			//UserDateTotal
			$f = TTnew('UserDateTotalFactory');
			$query = 'UPDATE '. $f->getTable() .' SET deleted = 1 WHERE pay_period_id = \''. TTUUID::castUUID($this->getID()) .'\' AND deleted = 0';
			$f->db->Execute( $query );
			Debug::Text('Query: '. $query .' Affected Rows: '. $f->db->Affected_Rows(), __FILE__, __LINE__, __METHOD__, 10);


			//PunchControl
			$f = TTnew('PunchControlFactory');
			$query = 'UPDATE '. $f->getTable() .' SET deleted = 1 WHERE pay_period_id = \''. TTUUID::castUUID($this->getID()) .'\' AND deleted = 0';
			$f->db->Execute( $query );
			Debug::Text('Query: '. $query .' Affected Rows: '. $f->db->Affected_Rows(), __FILE__, __LINE__, __METHOD__, 10);


			//Schedule
			$f = TTnew('ScheduleFactory');
			$query = 'UPDATE '. $f->getTable() .' SET deleted = 1 WHERE pay_period_id = \''. TTUUID::castUUID($this->getID()) .'\' AND deleted = 0';
			$f->db->Execute( $query );
			Debug::Text('Query: '. $query .' Affected Rows: '. $f->db->Affected_Rows(), __FILE__, __LINE__, __METHOD__, 10);


			//Requests
			$f = TTnew('RequestFactory');
			$query = 'UPDATE '. $f->getTable() .' SET deleted = 1 WHERE pay_period_id = \''. TTUUID::castUUID($this->getID()) .'\' AND deleted = 0';
			$f->db->Execute( $query );
			Debug::Text('Query: '. $query .' Affected Rows: '. $f->db->Affected_Rows(), __FILE__, __LINE__, __METHOD__, 10);


			//Exceptions
			$f = TTnew('ExceptionFactory');
			$query = 'UPDATE '. $f->getTable() .' SET deleted = 1 WHERE pay_period_id = \''. TTUUID::castUUID($this->getID()) .'\' AND deleted = 0';
			$f->db->Execute( $query );
			Debug::Text('Query: '. $query .' Affected Rows: '. $f->db->Affected_Rows(), __FILE__, __LINE__, __METHOD__, 10);

			TTLog::addEntry( $this->getId(), 500, TTi18n::getText('Delete Data: Pay Period') .' - '. TTi18n::getText('Start Date') .': '. TTDate::getDate('DATE+TIME', $this->getStartDate() ) .' '. TTi18n::getText('End Date') .': '. TTDate::getDate('DATE+TIME', $this->getEndDate() ) .' '. TTi18n::getText('Transaction Date') .': '. TTDate::getDate('DATE+TIME', $this->getTransactionDate() ), NULL, $this->getTable(), $this );
		} else {
			Debug::Text('ERROR: Unable to import data into pay period...', __FILE__, __LINE__, __METHOD__, 10);
		}

		//$pplf->FailTransaction();
		$pplf->CommitTransaction();

		return TRUE;
	}

	/**
	 * @return bool|int
	 */
	function getPendingRequests() {
		if ( $this->getCompany() != '' AND $this->isNew() == FALSE ) {
			//Get all pending requests
			$rlf = TTnew( 'RequestListFactory' );
			$rlf->getSumByCompanyIDAndPayPeriodIdAndStatus( $this->getCompany(), $this->getID(), 30 );
			if ( $rlf->getRecordCount() == 1 ) {
				return $rlf->getCurrent()->getColumn('total');
			}

			return 0;
		}

		return FALSE;
	}

	/**
	 * @return array
	 */
	function getExceptions() {
		$retarr = array(
						'exceptions_low' => 0,
						'exceptions_medium' => 0,
						'exceptions_high' => 0,
						'exceptions_critical' => 0,
						);

		$elf = TTnew( 'ExceptionListFactory' );
		$elf->getSumExceptionsByPayPeriodIdAndBeforeDate( $this->getID(), $this->getEndDate() );
		if ( $elf->getRecordCount() > 0 ) {
			//Debug::Text(' Found Exceptions: '. $elf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			foreach($elf as $e_obj ) {
				if ( $e_obj->getColumn('severity_id') == 10 ) {
					$retarr['exceptions_low'] = $e_obj->getColumn('count');
				}
				if ( $e_obj->getColumn('severity_id') == 20 ) {
					$retarr['exceptions_medium'] = $e_obj->getColumn('count');
				}
				if ( $e_obj->getColumn('severity_id') == 25 ) {
					$retarr['exceptions_high'] = $e_obj->getColumn('count');
				}
				if ( $e_obj->getColumn('severity_id') == 30 ) {
					$retarr['exceptions_critical'] = $e_obj->getColumn('count');
				}
			}
		} //else { //Debug::Text(' No Exceptions!', __FILE__, __LINE__, __METHOD__, 10);

		return $retarr;
	}

	/**
	 * @return mixed
	 */
	function getTotalPunches() {
		//Count how many punches are in this pay period.
		$plf = TTnew( 'PunchListFactory' );
		$retval = $plf->getByPayPeriodId( $this->getID() )->getRecordCount();
		Debug::Text(' Total Punches: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	/**
	 * @return mixed
	 */
	function getTotalManualTimeSheets() {
		//Count how many punches are in this pay period.
		$udtlf = TTnew( 'UserDateTotalListFactory' );
		$retval = $udtlf->getTotalByPayPeriodIdAndObjectTypeAndOverride( $this->getID(), 10, TRUE );
		Debug::Text(' Total Manual TimeSheets: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	/**
	 * @return mixed
	 */
	function getTotalAbsences() {
		//Count how many punches are in this pay period.
		$udtlf = TTnew( 'UserDateTotalListFactory' );
		$retval = $udtlf->getTotalByPayPeriodIdAndObjectTypeAndOverride( $this->getID(), 25, FALSE );
		Debug::Text(' Total Absence Records: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	/**
	 * @return array
	 */
	function getTimeSheets() {
		$retarr = array(
						'verified_timesheets' => 0,
						'pending_timesheets' => 0,
						'total_timesheets' => 0,
						);

		//Get verified timesheets
		$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
		$pptsvlf->getByPayPeriodIdAndCompanyId( $this->getID(), $this->getCompany() );
		if ( $pptsvlf->getRecordCount() > 0 ) {
			foreach( $pptsvlf as $pptsv_obj ) {
				//Status is the critical thing to check here due to supervisors authorizing the timesheets before employees.
				if ( $pptsv_obj->getStatus() == 50 ) {
					$retarr['verified_timesheets']++;
				} elseif (	$pptsv_obj->getStatus() == 30 OR $pptsv_obj->getStatus() == 45 ) {
					$retarr['pending_timesheets']++;
				}
			}
		}

		//Get total employees with time for this pay period.
		$udtlf = TTnew( 'UserDateTotalListFactory' );
		$retarr['total_timesheets'] = $udtlf->getWorkedUsersByPayPeriodId( $this->getID() );

		return $retarr;
	}

	/**
	 * @return int
	 */
	function getPayStubAmendments() {
		//Get PS Amendments.
		$psalf = TTnew( 'PayStubAmendmentListFactory' );
		$psalf->getByCompanyIdAndAuthorizedAndStartDateAndEndDate( $this->getCompany(), TRUE, $this->getStartDate(), $this->getEndDate() );
		$total_ps_amendments = 0;
		if ( is_object($psalf) ) {
			$total_ps_amendments = $psalf->getRecordCount();
		}

		Debug::Text(' Total PS Amendments: '. $total_ps_amendments, __FILE__, __LINE__, __METHOD__, 10);
		return $total_ps_amendments;
	}

	/**
	 * @return mixed
	 */
	function getPayStubs() {
		//Count how many pay stubs for each pay period.
		$pslf = TTnew( 'PayStubListFactory' );
		$total_pay_stubs = $pslf->getByPayPeriodId( $this->getId() )->getRecordCount();
		//$total_pay_stubs = $pslf->getByCompanyIdAndPayPeriodIdAndStatusId( $this->getCompany(), $this->getId());
		//Debug::Text(' Total Pay Stubs: '. $total_pay_stubs, __FILE__, __LINE__, __METHOD__, 10);
		return $total_pay_stubs;
	}

	/**
	 * @return mixed
	 */
	function getPayStubsOpen() {
		//Count how many pay stubs for each pay period.
		$pslf = TTnew( 'PayStubListFactory' );
		$total_pay_stubs = $pslf->getByCompanyIdAndPayPeriodIdAndStatusId( $this->getCompany(), $this->getId(), 25 )->getRecordCount(); //25=Open
		//Debug::Text(' Total Pay Stubs: '. $total_pay_stubs, __FILE__, __LINE__, __METHOD__, 10);
		return $total_pay_stubs;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = TRUE ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Company
		$clf = TTnew( 'CompanyListFactory' );
		$this->Validator->isResultSetWithRows(	'company',
														$clf->getByID($this->getCompany()),
														TTi18n::gettext('Company is invalid')
													);
		// Status
		if ( $this->getStatus() !== FALSE ) {
			$status_options = $this->getOptions('status');
			$validate_msg = TTi18n::gettext('Invalid Status');
			switch ( $this->old_status_id ) {
				case 20: //Closed
					$valid_statuses = array( 20, 30 );
					$status_options = Misc::arrayIntersectByKey( $valid_statuses, $status_options );
					$validate_msg = TTi18n::gettext('Status can only be changed from Closed to Post Adjustment');
					break;
				case 30: //Post Adjustment
					$valid_statuses = array( 20, 30 );
					$status_options = Misc::arrayIntersectByKey( $valid_statuses, $status_options );
					$validate_msg = TTi18n::gettext('Status can only be changed from Post Adjustment to Closed');
					break;
				default:
					break;
			}
			$this->Validator->inArrayKey(	'status_id',
													$this->getStatus(),
													$validate_msg,
													$status_options
												);
		}

		// Pay Period Schedule
		//When mass editing pay periods, we try to validate with no pay period schedule set because it could be editing across multiple pay period schedules. In this case ignore this check.
		if ( $this->Validator->getValidateOnly() == FALSE ) {
			if ( $this->getPayPeriodSchedule() == '' OR $this->getPayPeriodSchedule() == TTUUID::getZeroID() ) {
				$this->Validator->isTrue(		'pay_period_schedule',
												 FALSE,
												 TTi18n::gettext('Pay Period Schedule is not specified') );

			} else {
				$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
				$this->Validator->isResultSetWithRows( 'pay_period_schedule',
													   $ppslf->getByID( $this->getPayPeriodSchedule() ),
													   TTi18n::gettext( 'Pay Period Schedule is invalid' )
				);
			}
		}

		// Start Date
		if ( $this->getStartDate() !== FALSE ) {
			$this->Validator->isDate(		'start_date',
													$this->getStartDate(),
													TTi18n::gettext('Incorrect start date')
												);
			if ( $this->Validator->isError('start_date') == FALSE ) {
				$this->Validator->isTrue(		'start_date',
														$this->isValidStartDate( $this->getStartDate() ),
														TTi18n::gettext('Conflicting start date')
													);
			}
		}
		// End Date
		if ( $this->getEndDate() !== FALSE ) {
			$this->Validator->isDate(		'end_date',
													$this->getEndDate(),
													TTi18n::gettext('Incorrect end date')
												);
		}
		// Transaction date
		if ( $this->getTransactionDate() !== FALSE ) {
			$this->Validator->isDate(		'transaction_date',
													$this->getTransactionDate(),
													TTi18n::gettext('Incorrect transaction date')
												);
		}
		// Tainted date
		if ( $this->getTaintedDate() !== FALSE ) {
			$this->Validator->isDate(		'tainted_date',
													$this->getTaintedDate(),
													TTi18n::gettext('Incorrect tainted date')
												);
		}
		// Tainted employee
		if ( $this->getTaintedBy() !== FALSE ) {
			$ulf = TTnew( 'UserListFactory' );
			$this->Validator->isResultSetWithRows(	'tainted_by',
															$ulf->getByID($this->getTaintedBy()),
															TTi18n::gettext('Incorrect tainted employee')
														);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		//Make sure we aren't trying to create a pay period with no dates...
		if ( $this->isNew() == TRUE AND $this->Validator->getValidateOnly() == FALSE ) {
			Debug::text('New: Start Date: '. $this->getStartDate() .' End Date: '. $this->getEndDate(), __FILE__, __LINE__, __METHOD__, 10);
			if ( $this->getStartDate() == '' ) {
				$this->Validator->isTrue(		'start_date',
												FALSE,
												TTi18n::gettext('Start date not specified') );
			}

			if ( $this->getEndDate() == '' ) {
				$this->Validator->isTrue(		'end_date',
												FALSE,
												TTi18n::gettext('End date not specified') );
			}

			if ( $this->getTransactionDate() == '' ) {
				$this->Validator->isTrue(		'transaction_date',
												FALSE,
												TTi18n::gettext('Transaction date not specified') );
			}
		}

		//Make sure there aren't conflicting pay periods.
		//Start date checks that...
		//Make sure End Date is after Start Date, and transaction date is the same or after End Date.
		Debug::text('Start Date: '. $this->getStartDate() .' End Date: '. $this->getEndDate(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $this->getStartDate() != '' AND $this->getEndDate() != '' AND $this->getEndDate() <= $this->getStartDate() ) {
			$this->Validator->isTrue(		'end_date',
											FALSE,
											TTi18n::gettext('Conflicting end date'));
		}

		if ( $this->getDeleted() == FALSE AND ( $this->getStartDate() != FALSE AND $this->getEndDate() != ''
						AND TTUUID::isUUID( $this->getPayPeriodSchedule() ) AND $this->getPayPeriodSchedule() != TTUUID::getZeroID() AND $this->getPayPeriodSchedule() != TTUUID::getNotExistID() ) ) {
			$this->Validator->isTrue(		'start_date',
											 !$this->isConflicting(), //Reverse the boolean.
											 TTi18n::gettext('Conflicting start/end date, pay period already exists'));
		} else {
			Debug::text('Not checking for conflicts... DateStamp: '. (int)$this->getStartDate(), __FILE__, __LINE__, __METHOD__, 10);
		}

		if ( $this->getEndDate() != '' AND $this->getTransactionDate() != '' AND $this->getTransactionDate() < $this->getEndDate() ) {
			$this->Validator->isTrue(		'transaction_date',
											FALSE,
											TTi18n::gettext('Conflicting transaction date'));
		}

		if ( ( $this->getStatus() == 20 OR $this->getStatus() == 30 ) AND $this->getEndDate() > 0 AND TTDate::getBeginDayEpoch( time() ) <= $this->getEndDate() ) {
			$this->Validator->isTrue(		'status_id',
											FALSE,
											TTi18n::gettext('Invalid status, unable to lock or close pay periods before their end date'));
		}

		if ( $this->getDeleted() == TRUE AND $this->getStatus() == 20 ) {
			$this->Validator->isTrue(		'status_id',
											 FALSE,
											 TTi18n::gettext('Closed Pay Periods can not be deleted'));
		}

		$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
		$ppslf->getById( $this->getPayPeriodSchedule() );
		if ( $this->getStartDate() != '' AND $this->getPayPeriodSchedule() == TTUUID::getZeroID() ) {
			Debug::text('Pay Period Schedule not found: '. $this->getPayPeriodSchedule(), __FILE__, __LINE__, __METHOD__, 10);
			$this->Validator->isTrue(		'pay_period_schedule_id',
											FALSE,
											TTi18n::gettext('Pay Period Schedule is not specified') );
		}

		if ( $this->getStatus() == 20 ) { //Closed
			//Mark pay stubs as PAID once the pay period is closed?
			if ( $this->setPayStubStatus(40, TRUE ) == FALSE ) { //Dry-run only to ensure that they can be closed.
				$this->Validator->isTrue(		'status_id',
												 FALSE,
												 TTi18n::gettext('Unable to set pay stubs to PAID. Please ensure all pay stubs have transactions and that they have been processed (PAID).') );
			}
		}


		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		$this->StartTransaction();

		if ( $this->getStatus() == FALSE ) {
			$this->setStatus( 10 ) ;
		}

		if ( $this->getStatus() == 30 ) {
			$this->setTainted(TRUE);
		}

		//Only update these when we are setting the pay period to Post-Adjustment status.
		if ( $this->getStatus() == 30 AND $this->getTainted() == TRUE ) {
			$this->setTaintedBy();
			$this->setTaintedDate();
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );

		if ( $this->getDeleted() == TRUE ) {
			Debug::text('Delete TRUE: ', __FILE__, __LINE__, __METHOD__, 10);
			//Unassign user_date_total rows from this pay period, no need to delete this data anymore as it can be easily done otherways
			//and users don't realize how much data will actually be deleted.
			$udtf = TTnew( 'UserDateTotalFactory' );
			$query = 'update '. $udtf->getTable() .' set pay_period_id = \''. TTUUID::getZeroID() .'\' where pay_period_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->db->Execute($query);

			$pcf = TTnew( 'PunchControlFactory' );
			$query = 'update '. $pcf->getTable() .' set pay_period_id = \''. TTUUID::getZeroID() .'\' where pay_period_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->db->Execute($query);

			$sf = TTnew( 'ScheduleFactory' );
			$query = 'update '. $sf->getTable() .' set pay_period_id = \''. TTUUID::getZeroID() .'\' where pay_period_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->db->Execute($query);

			$rf = TTnew( 'RequestFactory' );
			$query = 'update '. $rf->getTable() .' set pay_period_id = \''. TTUUID::getZeroID() .'\' where pay_period_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->db->Execute($query);

			$ef = TTnew( 'ExceptionFactory' );
			$query = 'update '. $ef->getTable() .' set pay_period_id = \''. TTUUID::getZeroID() .'\' where pay_period_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->db->Execute($query);

			//Now that v9 has multiple payroll runs, if the user tries deleting multiple pay periods that have pay stubs assigned to them, this will fail due to unique constraint.
			//May need to try and get the latest payroll run_id for the pay_period_id = 0 case, and increment that instead...
			//Can't use getCurrentPayRun() here as it ignores invalid pay period IDs (ie: 0)
			$psf = TTnew( 'PayStubFactory' );
			$uf = TTNew('UserFactory');

			$query = 'SELECT  max(run_id) FROM '. $psf->getTable() .' as a LEFT JOIN '. $uf->getTable() .' as b ON ( a.user_id = b.id ) WHERE b.company_id = \''. TTUUID::castUUID($this->getCompany()) .'\' AND a.pay_period_id = \''. TTUUID::getZeroID() .'\'';
			$run_id = (int)$this->db->GetOne($query);
			Debug::text('Next Run ID for PayPeriodID=0: '. $run_id .' Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

			//Rather than update run_id to whatever the last run_id + 1 is, which will fail if there are multiple pay runs in the deleted pay period as its consolidating them all into a single payroll run
			//  update run_id to always add the maximum run number and that should avoid the unique constraint issue.
			$query = 'UPDATE '. $psf->getTable() .' SET pay_period_id = \''. TTUUID::getZeroID() .'\', run_id = ( run_id + '. (int)$run_id .' ) WHERE pay_period_id = \''. TTUUID::castUUID($this->getId()) .'\' AND deleted = 0';
			$this->db->Execute($query);
		} else {
			if ( $this->getStatus() == 20 ) { //Closed
				//Mark pay stubs as PAID once the pay period is closed?
				TTLog::addEntry( $this->getId(), 20, TTi18n::getText('Setting Pay Period to Closed'), NULL, $this->getTable() );
				$this->setPayStubStatus(40);

				//Delete pay stub amendemnts effective before this pay period started that are still active.
				//  This will help clean-up records that will never be used and prevent warnings when generating pay stubs.
				$this->purgePayStubAmendments();
			} elseif ( $this->getStatus() == 30 ) {
				TTLog::addEntry( $this->getId(), 20, TTi18n::getText('Setting Pay Period to Post-Adjustment'), NULL, $this->getTable() );
			}

			//When creating the 2nd pay period of the year (the previous pay period is the 1st), run the first pay period maintenance.
			//By this time (2-4days before the first pay period in the year ends) they should have made any corrections from the previous pay period,
			//  which was the last pay period in the previous year.
			$pplf = TTnew('PayPeriodListFactory');
			$pplf->getPreviousPayPeriodById( $this->getID() );
			if ( $pplf->getRecordCount() > 0 ) {
				$pp_obj = $pplf->getCurrent();
				if ( $pp_obj->isFirstPayPeriodInYear() == TRUE
						AND time() >= $pp_obj->getStartDate() //Can't be end or transaction date, as those are too late. This helps prevent manual pay periods created in the future from triggering the maintenance.
						AND time() <= $this->getEndDate() //In cases of modifying old pay periods, make sure we aren't past the transaction date of the first pay period in the year.
					) {
					Debug::text('Creating/Modifying 2nd Pay Period in Year... Running maintenance for 1st pay period in year...', __FILE__, __LINE__, __METHOD__, 10);
					$cd_obj = TTnew('CompanyDeductionFactory');
					$cd_obj->setCompany( $this->getCompany() );
					$cd_obj->updateCompanyDeductionForTaxYear( $pp_obj->getTransactionDate() );
				} else {
					Debug::text('NOT running maintenance, maybe not past the start date of the last pay period yet, or not 2nd pay period in the year, or modifying pay period more than 90days old... 1st PP Start Date: '. TTDate::getDate('DATE+TIME', $pp_obj->getStartDate() ) .' 2nd PP End Date: '. TTDate::getDate('DATE+TIME', $this->getEndDate() ), __FILE__, __LINE__, __METHOD__, 10);
				}
			}
			unset($pplf, $pp_obj, $cd_obj);

			//If there is only one pay period schedule, and they are editing a OPEN pay period
			//  always import data when editing pay periods. (preferrably only if the start/end dates change though)
			//  This can help avoid issues with users changing pay period dates and not importing the data manually.
			//  FIXME: It would be nice to only do this if the start OR end date change, but we can't determine that for certain right now.
			//  **This causes UNIT TESTs to fail due to deadlock, so disable this functionality during those tests.
			if ( $this->getEnableImportData() == TRUE AND $this->getStatus() == 10 ) { //Only consider open pay periods.
				$ppslf = TTnew('PayPeriodScheduleListFactory');
				$ppslf->getByCompanyId( $this->getCompany() );
				if ( $ppslf->getRecordCount() == 1 ) {
					Debug::text('Only one PP schedule, importing data...', __FILE__, __LINE__, __METHOD__, 10);
					$this->importData( FALSE, $this->getID() );
				}
			}

			if ( $this->getEnableImportOrphanedData() == TRUE ) {
				$this->importOrphanedData();
				//$this->importData();
			}
		}

		$this->CommitTransaction();

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
						case 'start_date':
						case 'end_date':
						case 'transaction_date':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[$key] ) );
							}
							break;
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
	 * @return array
	 */
	function getObjectAsArray( $include_columns = NULL ) {
		$data = array();
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			$ppsf = TTnew( 'PayPeriodScheduleFactory' );

			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					$exceptions_arr = array();
					$timesheet_arr = array();
					switch( $variable ) {
						case 'tainted': //Don't allow this to be set from the API.
						case 'tainted_by':
						case 'tainted_date':
							break;
						case 'status':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'type':
							//Make sure type_id is set first.
							$data[$variable] = Option::getByKey( $this->getColumn('type_id'), $ppsf->getOptions( $variable ) );
							break;
						case 'type_id':
						case 'pay_period_schedule':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'start_date':
						case 'end_date':
						case 'transaction_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', $this->$function() );
							}
							break;
						case 'total_punches':
						case 'total_manual_timesheets':
						case 'total_absences':
						case 'pending_requests':
						case 'ps_amendments':
						case 'pay_stubs':
						case 'pay_stubs_open':
							//These functions are slow to obtain, so make sure the column is requested explicitly before we include it.
							if ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) {
								$data[$variable] = $this->$function();
							}
							break;
						case 'exceptions_critical':
						case 'exceptions_high':
						case 'exceptions_medium':
						case 'exceptions_low':
							//These functions are slow to obtain, so make sure the column is requested explicitly before we include it.
							if ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) {
								if ( empty($exceptions_arr) ) {
									$exceptions_arr = $this->getExceptions();
								}

								$data[$variable] = $exceptions_arr[$variable];
							}
							break;
						case 'verified_timesheets':
						case 'pending_timesheets':
						case 'total_timesheets':
							//These functions are slow to obtain, so make sure the column is requested explicitly before we include it.
							if ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) {
								if ( empty($timesheet_arr) ) {
									$timesheet_arr = $this->getTimeSheets();
								}

								$data[$variable] = $timesheet_arr[$variable];
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
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Pay Period') .' - '. TTi18n::getText('Start Date') .': '. TTDate::getDate('DATE+TIME', $this->getStartDate() ) .' '. TTi18n::getText('End Date') .': '. TTDate::getDate('DATE+TIME', $this->getEndDate() ) .' '. TTi18n::getText('Transaction Date') .': '. TTDate::getDate('DATE+TIME', $this->getTransactionDate() ), NULL, $this->getTable(), $this );
	}
}
?>
