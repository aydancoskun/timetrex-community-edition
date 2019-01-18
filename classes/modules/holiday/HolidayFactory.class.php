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
 * @package Modules\Holiday
 */
class HolidayFactory extends Factory {
	protected $table = 'holidays';
	protected $pk_sequence_name = 'holidays_id_seq'; //PK Sequence name

	protected $holiday_policy_obj = NULL;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = NULL ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$retval = array(
										'-1010-name' => TTi18n::gettext('Name'),
										'-1020-date_stamp' => TTi18n::gettext('Date'),

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
								'name',
								'date_stamp',
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
										'holiday_policy_id' => 'HolidayPolicyID',
										'date_stamp' => 'DateStamp',
										'name' => 'Name',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getHolidayPolicyObject() {
		return $this->getGenericObject( 'HolidayPolicyListFactory', $this->getHolidayPolicyID(), 'holiday_policy_obj' );
	}

	/**
	 * @return bool|mixed
	 */
	function getHolidayPolicyID() {
		return $this->getGenericDataValue( 'holiday_policy_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setHolidayPolicyID( $value) {
		$value = trim($value);
		$value = TTUUID::castUUID( $value );
		if ( $value == '' ) {
			$value = TTUUID::getZeroID();
		}
		return $this->setGenericDataValue( 'holiday_policy_id', $value );
	}

	/**
	 * @param int $date_stamp EPOCH
	 * @return bool
	 */
	function isUniqueDateStamp( $date_stamp) {
		$ph = array(
					'policy_id' => $this->getHolidayPolicyID(),
					'date_stamp' => $this->db->BindDate( $date_stamp ),
					);

		$query = 'select id from '. $this->getTable() .'
					where holiday_policy_id = ?
						AND date_stamp = ?
						AND deleted=0';
		$date_stamp_id = $this->db->GetOne($query, $ph);
		Debug::Arr($date_stamp_id, 'Unique Date Stamp: '. $date_stamp, __FILE__, __LINE__, __METHOD__, 10);

		if ( $date_stamp_id === FALSE ) {
			return TRUE;
		} else {
			if ($date_stamp_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getDateStamp( $raw = FALSE ) {
		$value = $this->getGenericDataValue( 'date_stamp' );
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
	function setDateStamp( $value) {
		$value = ( !is_int($value) ) ? trim($value) : $value; //Dont trim integer values, as it changes them to strings.
		return $this->setGenericDataValue( 'date_stamp', $value );
	}

	/**
	 * @return bool
	 */
	function getOldDateStamp() {
		return $this->getGenericTempDataValue( 'old_date_stamp' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setOldDateStamp( $value) {
		Debug::Text(' Setting Old DateStamp: '. TTDate::getDate('DATE', $value ), __FILE__, __LINE__, __METHOD__, 10);
		return $this->setGenericTempDataValue( 'old_date_stamp', TTDate::getMiddleDayEpoch( $value ) );
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name) {
		//BindDate() causes a deprecated error if date_stamp is not set, so just return TRUE so we can throw a invalid date error elsewhere instead.
		//This also causes it so we can never have a invalid date and invalid name validation errors at the same time.
		if ( $this->getDateStamp() == '' ) {
			return TRUE;
		}

		$name = trim($name);
		if ( $name == '' ) {
			return FALSE;
		}

		//When a holiday gets moved back/forward due to falling on weekend, it can throw off the check to see if the holiday
		//appears in the same year. For example new years 01-Jan-2011 gets moved to 31-Dec-2010, its in the same year
		//as the previous New Years day or 01-Jan-2010, so this check fails.
		//
		//I think this can only happen with New Years, or other holidays that fall within two days of the new year.
		//So exclude the first three days of the year to allow for weekend adjustments.
		$ph = array(
					'policy_id' => $this->getHolidayPolicyID(),
					'name' => TTi18n::strtolower($name),
					'start_date1' => $this->db->BindDate( ( TTDate::getBeginYearEpoch( $this->getDateStamp() ) + (86400 * 3) ) ),
					'end_date1' => $this->db->BindDate( TTDate::getEndYearEpoch( $this->getDateStamp() ) ),
					'start_date2' => $this->db->BindDate( ( $this->getDateStamp() - ( 86400 * 15 ) ) ),
					'end_date2' => $this->db->BindDate( ( $this->getDateStamp() + ( 86400 * 15 ) ) ),
					);

		$query = 'select id from '. $this->getTable() .'
					where holiday_policy_id = ?
						AND lower(name) = ?
						AND
							(
								(
								date_stamp >= ?
								AND date_stamp <= ?
								)
							OR
								(
								date_stamp >= ?
								AND date_stamp <= ?
								)
							)
						AND deleted=0';
		$name_id = $this->db->GetOne($query, $ph);
		Debug::Arr($name_id, 'Unique Name: '. $name, __FILE__, __LINE__, __METHOD__, 10);

		if ( $name_id === FALSE ) {
			return TRUE;
		} else {
			if ($name_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @return bool|mixed
	 */
	function getName() {
		return $this->getGenericDataValue( 'name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setName( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'name', $value );
	}

	//ignore_after_eligibility is used when scheduling employees as absent on a holiday, since they haven't worked after the holiday
	// when the schedule is created, it will always fail.
	/**
	 * @param string $user_id UUID
	 * @param bool $ignore_after_eligibility
	 * @return bool
	 */
	function isEligible( $user_id, $ignore_after_eligibility = FALSE ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		$original_time_zone = TTDate::getTimeZone(); //Store current timezone so we can return to it after.

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getById( $user_id );
		if ( $ulf->getRecordCount() == 1 ) {
			$user_obj = $ulf->getCurrent();

			//Use CalculatePolicy to determine if they are eligible for the holiday or not.
			$flags = array(
								'meal' => FALSE,
								'undertime_absence' => FALSE,
								'break' => FALSE,
								'holiday' => TRUE,
								'schedule_absence' => FALSE,
								'absence' => FALSE,
								'regular' => FALSE,
								'overtime' => FALSE,
								'premium' => FALSE,
								'accrual' => FALSE,
								'exception' => FALSE,

								//Exception options
								'exception_premature' => FALSE, //Calculates premature exceptions
								'exception_future' => FALSE, //Calculates exceptions in the future.

								//Calculate policies for future dates.
								'future_dates' => FALSE, //Calculates dates in the future.
								'past_dates' => FALSE, //Calculates dates in the past. This is only needed when Pay Formulas that use averaging are enabled?*
							);
			$cp = TTNew('CalculatePolicy');
			$cp->setFlag( $flags );
			$cp->setUserObject( $user_obj );
			$cp->getRequiredData( $this->getDateStamp() );

			$retval = $cp->isEligibleForHoliday( $this->getDateStamp(), $this->getHolidayPolicyObject(), $ignore_after_eligibility );

			TTDate::setTimeZone( $original_time_zone ); //Store current timezone so we can return to it after.

			return $retval;
		}

		Debug::text('ERROR: Unable to get user object...', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;

	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = TRUE ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Holiday Policy
		$hplf = TTnew( 'HolidayPolicyListFactory' );
		$this->Validator->isResultSetWithRows(	'holiday_policy',
														$hplf->getByID($this->getHolidayPolicyID()),
														TTi18n::gettext('Holiday Policy is invalid')
													);
		// Date stamp
		$this->Validator->isDate(		'date_stamp',
												$this->getDateStamp(),
												TTi18n::gettext('Incorrect date')
											);
		if ( $this->Validator->isError('date_stamp') == FALSE ) {
			$this->Validator->isTrue(		'date_stamp',
													$this->isUniqueDateStamp( $this->getDateStamp() ),
													TTi18n::gettext('Date is already in use by another Holiday')
												);
		}
		if ( $this->Validator->isError('date_stamp') == FALSE ) {
			$value = $this->getDateStamp();
			if	( $value > 0 ) {
				if ( $this->getDateStamp() !== $value AND $this->getOldDateStamp() != $this->getDateStamp() ) {
					Debug::Text(' Setting Old DateStamp... Current Old DateStamp: '. (int)$this->getOldDateStamp() .' Current DateStamp: '. (int)$this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10);
					$this->setOldDateStamp( $this->getDateStamp() );
				}
			} else {
				$this->Validator->isTRUE(		'date_stamp',
												FALSE,
												TTi18n::gettext('Incorrect date'));
			}
		}
		// Name
		$this->Validator->isLength(	'name',
											$this->getName(),
											TTi18n::gettext('Name is invalid'),
											2, 50
										);
		if ( $this->Validator->isError('name') == FALSE ) {
			$this->Validator->isTrue(		'name',
											$this->isUniqueName($this->getName()),
											TTi18n::gettext('Name is already in use in this year, or within 30 days')
										);
		}





		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->Validator->hasError('date_stamp') == FALSE AND $this->getDateStamp() == '' ) {
			$this->Validator->isTrue(		'date_stamp',
											FALSE,
											TTi18n::gettext('Date is invalid'));
		}


		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		//ReCalculate Recurring Schedule records based on this holiday, assuming its in the future.
		if ( TTDate::getMiddleDayEpoch( $this->getDateStamp() ) >= TTDate::getMiddleDayEpoch( time() ) ) {
			Debug::text('Holiday is today or in the future, try to recalculate recurring schedules on this date: '. TTDate::getDate('DATE', $this->getDateStamp() ), __FILE__, __LINE__, __METHOD__, 10);

			$date_ranges = array();
			if ( TTDate::getMiddleDayEpoch( $this->getDateStamp() ) != TTDate::getMiddleDayEpoch( $this->getOldDateStamp() ) ) {
				$date_ranges[] = array( 'start_date' => TTDate::getBeginDayEpoch( $this->getOldDateStamp() ), 'end_date' => TTDate::getEndDayEpoch( $this->getOldDateStamp() ) );
			}
			$date_ranges[] = array( 'start_date' => TTDate::getBeginDayEpoch( $this->getDateStamp() ), 'end_date' => TTDate::getEndDayEpoch( $this->getDateStamp() ) );

			foreach( $date_ranges as $date_range ) {
				$start_date = $date_range['start_date'];
				$end_date = $date_range['end_date'];

				//Get existing recurring_schedule rows on the holiday day, so we can figure out which recurring_schedule_control records to recalculate.
				$recurring_schedule_control_ids = array();

				$rslf = TTnew('RecurringScheduleListFactory');
				$rslf->getByCompanyIDAndStartDateAndEndDateAndNoConflictingSchedule( $this->getHolidayPolicyObject()->getCompany(), $start_date, $end_date );
				Debug::text('Recurring Schedule Record Count: '. $rslf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
				if ( $rslf->getRecordCount() > 0 ) {
					foreach( $rslf as $rs_obj ) {
						if ( TTUUID::isUUID( $rs_obj->getRecurringScheduleControl() ) AND $rs_obj->getRecurringScheduleControl() != TTUUID::getZeroID() AND $rs_obj->getRecurringScheduleControl() != TTUUID::getNotExistID() ) {
							$recurring_schedule_control_ids[] = $rs_obj->getRecurringScheduleControl();
						}
					}
				}
				$recurring_schedule_control_ids = array_unique($recurring_schedule_control_ids);
				Debug::Arr($recurring_schedule_control_ids, 'Recurring Schedule Control IDs: ', __FILE__, __LINE__, __METHOD__, 10);

				if ( count($recurring_schedule_control_ids) > 0 ) {
					//
					//**THIS IS DONE IN RecurringScheduleControlFactory, RecurringScheduleTemplateControlFactory, HolidayFactory postSave() as well.
					//
					$rsf = TTnew('RecurringScheduleFactory');
					$rsf->StartTransaction();
					$rsf->clearRecurringSchedulesFromRecurringScheduleControl( $recurring_schedule_control_ids, $start_date, $end_date );
					$rsf->addRecurringSchedulesFromRecurringScheduleControl( $this->getHolidayPolicyObject()->getCompany(), $recurring_schedule_control_ids, $start_date, $end_date );
					$rsf->CommitTransaction();
				}
			}
		} else {
			Debug::text('Holiday is not in the future...', __FILE__, __LINE__, __METHOD__, 10);
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
						case 'date_stamp':
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
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'date_stamp':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Holiday'), NULL, $this->getTable(), $this );
	}

}
?>
