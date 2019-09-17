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
 * @package Modules\Schedule
 */
class RecurringScheduleTemplateFactory extends Factory {
	protected $table = 'recurring_schedule_template';
	protected $pk_sequence_name = 'recurring_schedule_template_id_seq'; //PK Sequence name

	protected $schedule_policy_obj = NULL;
	protected $recurring_schedule_template_control_obj = NULL;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = NULL ) {

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$sf = TTNew('ScheduleFactory'); /** @var ScheduleFactory $sf */
				$retval = $sf->getOptions('status');
				break;
			case 'columns':
				$retval = array(
										'-1010-week' => TTi18n::gettext('Week'),

										'-1101-sun' => TTi18n::gettext('S'),
										'-1102-mon' => TTi18n::gettext('M'),
										'-1103-tue' => TTi18n::gettext('T'),
										'-1104-wed' => TTi18n::gettext('W'),
										'-1105-thu' => TTi18n::gettext('T'),
										'-1106-fri' => TTi18n::gettext('F'),
										'-1107-sat' => TTi18n::gettext('S'),

										'-1200-start_time' => TTi18n::gettext('In'),
										'-1210-end_time' => TTi18n::gettext('Out'),

										'-1220-schedule_policy' => TTi18n::gettext('Schedule Policy'),
										'-1225-status' => TTi18n::gettext('Status'),

										'-1230-branch' => TTi18n::gettext('Branch'),
										'-1240-department' => TTi18n::gettext('Department'),
										'-1250-job' => TTi18n::gettext('Job'),
										'-1260-job_item' => TTi18n::gettext('Task'),

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
								'week',
								'sun',
								'mon',
								'tue',
								'wed',
								'thu',
								'fri',
								'sat',
								'start_time',
								'end_time',
								'schedule_policy',
								'branch',
								'department',
								'job',
								'job_item',
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
										'status_id' => 'Status',
										'recurring_schedule_template_control_id' => 'RecurringScheduleTemplateControl',
										'week' => 'Week',
										'sun' => 'Sun',
										'mon' => 'Mon',
										'tue' => 'Tue',
										'wed' => 'Wed',
										'thu' => 'Thu',
										'fri' => 'Fri',
										'sat' => 'Sat',
										'start_time' => 'StartTime',
										'end_time' => 'EndTime',
										'total_time' => 'TotalTime',
										'schedule_policy_id' => 'SchedulePolicyID',
										'branch_id' => 'Branch',
										'department_id' => 'Department',
										'job_id' => 'Job',
										'job_item_id' => 'JobItem',
										'absence_policy_id' => 'AbsencePolicyID',
										'open_shift_multiplier' => 'OpenShiftMultiplier',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getRecurringScheduleTemplateControlObject() {
		return $this->getGenericObject( 'RecurringScheduleTemplateControlListFactory', $this->getRecurringScheduleTemplateControl(), 'recurring_schedule_template_control_obj' );
	}

	/**
	 * @return bool
	 */
	function getSchedulePolicyObject() {
		return $this->getGenericObject( 'SchedulePolicyListFactory', $this->getSchedulePolicyID(), 'schedule_policy_obj' );
	}

	/**
	 * @return bool|mixed
	 */
	function getRecurringScheduleTemplateControl() {
		return $this->getGenericDataValue( 'recurring_schedule_template_control_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setRecurringScheduleTemplateControl( $value ) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'recurring_schedule_template_control_id', $value );
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
	function setStatus( $value ) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getWeek() {
		return $this->getGenericDataValue( 'week' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setWeek( $value ) {
		$value = (int)trim($value);
		if ( $value > 0 ) {
			return $this->setGenericDataValue( 'week', $value );
		}
		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getSun() {
		return $this->fromBool( $this->getGenericDataValue( 'sun' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSun( $value ) {
		return $this->setGenericDataValue( 'sun', $this->toBool($value) );
	}

	/**
	 * @return bool
	 */
	function getMon() {
		return $this->fromBool( $this->getGenericDataValue( 'mon' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMon( $value) {
		return $this->setGenericDataValue( 'mon', $this->toBool($value) );
	}

	/**
	 * @return bool
	 */
	function getTue() {
		return $this->fromBool( $this->getGenericDataValue( 'tue' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTue( $value) {
		return $this->setGenericDataValue( 'tue', $this->toBool($value) );
	}

	/**
	 * @return bool
	 */
	function getWed() {
		return $this->fromBool( $this->getGenericDataValue( 'wed' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setWed( $value) {
		return $this->setGenericDataValue( 'wed', $this->toBool($value) );
	}

	/**
	 * @return bool
	 */
	function getThu() {
		return $this->fromBool( $this->getGenericDataValue( 'thu' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setThu( $value) {
		return $this->setGenericDataValue( 'thu', $this->toBool($value) );
	}

	/**
	 * @return bool
	 */
	function getFri() {
		return $this->fromBool( $this->getGenericDataValue( 'fri' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setFri( $value) {
		return $this->setGenericDataValue( 'fri', $this->toBool($value) );
	}

	/**
	 * @return bool
	 */
	function getSat() {
		return $this->fromBool( $this->getGenericDataValue( 'sat' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSat( $value) {
		return $this->setGenericDataValue( 'sat', $this->toBool($value) );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getStartTime( $raw = FALSE ) {
		$value = $this->getGenericDataValue( 'start_time' );
		if ( $value !== FALSE ) {
			if ( $raw === TRUE) {
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
	function setStartTime( $value ) {
		$value = ( !is_int($value) ) ? trim($value) : $value; //Dont trim integer values, as it changes them to strings.
		Debug::Text('Start Time: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		return $this->setGenericDataValue( 'start_time', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getEndTime( $raw = FALSE ) {
		$value = $this->getGenericDataValue( 'end_time' );
		if ( $value !== FALSE ) {
			if ( $raw === TRUE) {
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
	function setEndTime( $value ) {
		$value = ( !is_int($value) ) ? trim($value) : $value; //Dont trim integer values, as it changes them to strings.
		return $this->setGenericDataValue( 'end_time', $value );
	}

	/**
	 * @return mixed
	 */
	function getTotalTime() {
		$sf = TTnew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */

		//This helps calculate the schedule total time based on schedule policy or policy groups.
		$sf->setCompany( $this->getRecurringScheduleTemplateControlObject()->getCompany() );
		$sf->setStartTime( $this->getStartTime() );
		$sf->setEndTime( $this->getEndTime() );
		if ( $this->getSchedulePolicyObject() != FALSE ) {
			$sf->setSchedulePolicyId( $this->getSchedulePolicyObject()->getID() );
		}
		$sf->preSave();

		return $sf->getTotalTime();
	}

	/**
	 * @return bool|mixed
	 */
	function getSchedulePolicyID() {
		return $this->getGenericDataValue( 'schedule_policy_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setSchedulePolicyID( $value ) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'schedule_policy_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getBranch() {
		return $this->getGenericDataValue( 'branch_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setBranch( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::text('Branch ID: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		//not_exist_id is for user default branch.
		return $this->setGenericDataValue( 'branch_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDepartment() {
		return $this->getGenericDataValue( 'department_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setDepartment( $value ) {
		$value = TTUUID::castUUID( $value );
		//-1 is for user default department.
		return $this->setGenericDataValue( 'department_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getJob() {
		return $this->getGenericDataValue( 'job_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setJob( $value ) {
		$value = TTUUID::castUUID( $value );
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			$value = TTUUID::getZeroID();

		}
		return $this->setGenericDataValue( 'job_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getJobItem() {
		return $this->getGenericDataValue( 'job_item_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setJobItem( $value ) {
		$value = TTUUID::castUUID( $value );
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			$value = TTUUID::getZeroID();
		}
		return $this->setGenericDataValue( 'job_item_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getAbsencePolicyID() {
		return $this->getGenericDataValue( 'absence_policy_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setAbsencePolicyID( $value ) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'absence_policy_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getOpenShiftMultiplier() {
		return $this->getGenericDataValue( 'open_shift_multiplier' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOpenShiftMultiplier( $value ) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'open_shift_multiplier', $value );
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function isActiveShiftDay( $epoch ) {
		$day_of_week = strtolower( date('D', $epoch) );
		if ( isset( $this->data[$day_of_week] ) ) {
			return $this->fromBool( $this->data[$day_of_week] );
		}

		return FALSE;
	}

	/**
	 * @param int $start_date EPOCH
	 * @param int $end_date EPOCH
	 * @param array $holiday_data
	 * @param $n
	 * @param array $shifts
	 * @param array $shifts_index
	 * @param array $open_shift_conflict_index
	 * @param string $permission_children_ids UUID
	 * @return array|bool
	 */
	function getShifts( $start_date, $end_date, &$holiday_data = array(), &$n, &$shifts = array(), &$shifts_index = array(), $open_shift_conflict_index = array(), $permission_children_ids = NULL ) {
		//Debug::text('Start Date: '. TTDate::getDate('DATE+TIME', $start_date) .' End Date: '. TTDate::getDate('DATE+TIME', $end_date), __FILE__, __LINE__, __METHOD__, 10);

		$recurring_schedule_control_start_date = TTDate::strtotime( $this->getColumn('recurring_schedule_control_start_date') );
		//Debug::text('Recurring Schedule Control Start Date: '. TTDate::getDate('DATE+TIME', $recurring_schedule_control_start_date), __FILE__, __LINE__, __METHOD__, 10);

		$current_template_week = (int)$this->getColumn('remapped_week');
		$max_week = (int)$this->getColumn('max_week');
		//Debug::text('Template Week: '. $current_template_week .' Max Week: '. $this->getColumn('max_week') .' ReMapped Week: '. $this->getColumn('remapped_week'), __FILE__, __LINE__, __METHOD__, 10);

		if ( $recurring_schedule_control_start_date == ''  ) {
			return FALSE;
		}

		$ppsf = TTnew('PayPeriodScheduleFactory'); /** @var PayPeriodScheduleFactory $ppsf */

		//Get week of start_date
		$start_date_week = TTDate::getBeginWeekEpoch( $recurring_schedule_control_start_date, 0 ); //Start week on Sunday to match Recurring Schedule.
		//Debug::text('Week of Start Date: '. $start_date_week, __FILE__, __LINE__, __METHOD__, 10);

		for ( $i = $start_date; $i <= $end_date; $i += (86400 + 43200) ) {
			//Handle DST by adding 12hrs to the date to get the mid-day epoch, then forcing it back to the beginning of the day.
			$i = TTDate::getBeginDayEpoch( $i );

			if ( ( $this->getColumn('hire_date') != '' AND $i < strtotime( $this->getColumn('hire_date') ) )
					OR ( $this->getColumn('termination_date') != '' AND $i > strtotime( $this->getColumn('termination_date') ) )
					) {
				//Debug::text('Skipping due to Hire/Termination date: User ID: '. $this->getColumn('user_id') .' I: '. $i .' Hire Date: '. $this->getColumn('hire_date') .' Termination Date: '. $this->getColumn('termination_date'), __FILE__, __LINE__, __METHOD__, 10);
				continue;
			}

			//This needs to take into account weeks spanning January 1st of each year. Where the week goes from 53 to 1.
			//Rather then use the week of the year, calculate the weeks between the recurring schedule start date and now.
			$current_week = round( ( TTDate::getBeginWeekEpoch( $i, 0 ) - $start_date_week ) / ( 604800 ) ); //Find out which week we are on based on the recurring schedule start date. Use round due to DST the week might be 6.9 or 7.1, so we need to round to the nearest full week.
			//Debug::text('I: '. $i .' User ID: '. $this->getColumn('user_id') .' Current Date: '. TTDate::getDate('DATE+TIME', $i) .' Current Week: '. $current_week .' Start Week: '. $start_date_week, __FILE__, __LINE__, __METHOD__, 10);

			$template_week = ( ( $current_week % $max_week ) + 1 );
			//Debug::text('Template Week: '. $template_week .' Max Week: '. $max_week, __FILE__, __LINE__, __METHOD__, 10);

			if ( $template_week == $current_template_week ) {
				//Debug::text('Current Date: '. TTDate::getDate('DATE+TIME', $i) .' Current Week: '. $current_week, __FILE__, __LINE__, __METHOD__, 10);
				//Debug::text('&nbsp;Template Week: '. $template_week .' Max Week: '. $max_week, __FILE__, __LINE__, __METHOD__, 10);

				if ( $this->isActiveShiftDay( $i ) ) {
					//Debug::text('&nbsp;&nbsp;Active Shift on this day...', __FILE__, __LINE__, __METHOD__, 10);
					$start_time = TTDate::getTimeLockedDate( $this->getStartTime(), $i );
					$end_time = TTDate::getTimeLockedDate( $this->getEndTime(), $i );
					if ( $end_time < $start_time ) {
						//Spans the day boundary, add 86400 to just the date *not* the end_time.
						//Because if the shift spans Nov 1st 2015 2:00AM (DST switchout) the end time will be less one hour than normal.
						//$end_time = ( $end_time + 86400 );
						$end_time = TTDate::getTimeLockedDate( $this->getEndTime(), ( TTDate::getMiddleDayEpoch($i) + 86400 ) );
						//Debug::text('    Schedule spans day boundary, bumping endtime to next day...', __FILE__, __LINE__, __METHOD__, 10);
					}

					$iso_date_stamp = TTDate::getISODateStamp( $ppsf->getShiftAssignedDate( $start_time, $end_time, $this->getColumn('shift_assigned_day_id') ) );

					$open_shift_multiplier = ( $this->getColumn('user_id') == TTUUID::getZeroID() ) ? ( ( getTTProductEdition() == TT_PRODUCT_COMMUNITY OR $this->getRecurringScheduleTemplateControlObject()->getCompanyObject()->getProductEdition() == 10 ) ) ? 0 : $this->getOpenShiftMultiplier() : 1;
					//Debug::text('Open Shift Multiplier: '. $open_shift_multiplier, __FILE__, __LINE__, __METHOD__, 10);
					for( $x = 0; $x < $open_shift_multiplier; $x++ ) {
						//Check all non-OPEN shifts for conflicts.
						if ( TTUUID::isUUID( $this->getColumn('user_id') ) AND $this->getColumn('user_id') != TTUUID::getZeroID() AND $this->getColumn('user_id') != TTUUID::getNotExistID()
								AND isset($shifts_index[$iso_date_stamp][$this->getColumn('user_id')]) ) {
							//User has previous recurring schedule shifts, check for overlap.
							//Loop over each employees shift for this day and check for conflicts
							foreach( $shifts_index[$iso_date_stamp][$this->getColumn('user_id')] as $shift_key ) {
								if ( isset($shifts[$iso_date_stamp][$shift_key]) ) {
									//Must use parseDateTime() when called from the API due to date formats that strtotime() fails on.
									if ( TTDate::isTimeOverLap( ( defined('TIMETREX_API') ) ? TTDate::parseDateTime($shifts[$iso_date_stamp][$shift_key]['start_date']) : $shifts[$iso_date_stamp][$shift_key]['start_date'],
																( defined('TIMETREX_API') ) ? TTDate::parseDateTime($shifts[$iso_date_stamp][$shift_key]['end_date']) : $shifts[$iso_date_stamp][$shift_key]['end_date'],
																$start_time,
																$end_time ) == TRUE ) {
										//Debug::text('&nbsp;&nbsp;Found overlapping recurring schedules! User ID: '. $this->getColumn('user_id') .' Start Time: '. $start_time, __FILE__, __LINE__, __METHOD__, 10);
										continue 2;
									}
								}
							}
							unset($shift_key);
						} elseif ( $this->getColumn('user_id') == TTUUID::getZeroID() AND isset($shifts_index[$iso_date_stamp]) ) {
							//Debug::text('	   Checking OPEN shift conflicts... Date: '. $iso_date_stamp, __FILE__, __LINE__, __METHOD__, 10);

							//Check all OPEN shifts for conflicts.
							//This is special, since there can be multiple open shifts for the same branch, department, job, task, so we need to check if are conflicts with *any* employee.
							//Do we allow conflicting shifts between committed and recurring OPEN shifts? For example what if there are two open shifts on the same day
							//6AM-3PM (x2) and they want to override one of those shifts to 7AM-4PM? If we use this check:
							//	 ( $shifts[$iso_date_stamp][$shift_key]['user_id'] > 0 OR ( isset($shifts[$iso_date_stamp][$shift_key]['id']) AND $shifts[$iso_date_stamp][$shift_key]['id'] > 0 ) )
							//That allows committed OPEN shifts to override recurring open shifts, which is great, but it prevents adding additional open shifts that may
							//also overlap unless they override all recurring shifts first. I think this is the trade-off we have to make as its more likely that they
							//will adjust an open shift time rather than add/remove specific shifts. Removing recurring OPEN shifts can be done by making them ABSENT.
							//This will also affect when recurring OPEN shifts are committed by preventing the shifts from doubling up.
							foreach( $shifts_index[$iso_date_stamp] as $tmp_index_user_id => $tmp_index_arr ) {
								foreach( $tmp_index_arr as $shift_key ) {
									$tmp_start_date = ( defined('TIMETREX_API') ) ? TTDate::parseDateTime($shifts[$iso_date_stamp][$shift_key]['start_date']) : $shifts[$iso_date_stamp][$shift_key]['start_date'];
									$tmp_end_date = ( defined('TIMETREX_API') ) ? TTDate::parseDateTime($shifts[$iso_date_stamp][$shift_key]['end_date']) : $shifts[$iso_date_stamp][$shift_key]['end_date'];
									if (
											(
													( TTUUID::isUUID( $shifts[$iso_date_stamp][$shift_key]['user_id'] ) AND $shifts[$iso_date_stamp][$shift_key]['user_id'] != TTUUID::getZeroID() AND $shifts[$iso_date_stamp][$shift_key]['user_id'] != TTUUID::getNotExistID() )
													OR ( isset($shifts[$iso_date_stamp][$shift_key]['id']) AND
															TTUUID::isUUID( $shifts[$iso_date_stamp][$shift_key]['id'] ) AND $shifts[$iso_date_stamp][$shift_key]['id'] != TTUUID::getNotExistID() AND $shifts[$iso_date_stamp][$shift_key]['id'] != TTUUID::getZeroID()
															) )
											AND ( !isset($open_shift_conflict_index['open'][$this->getID()][$shift_key]) AND ( isset( $shifts[$iso_date_stamp][$shift_key]['id'] ) AND !isset($open_shift_conflict_index['scheduled'][$shifts[$iso_date_stamp][$shift_key]['id']]) ) )
											AND $this->getColumn('schedule_branch_id') == $shifts[$iso_date_stamp][$shift_key]['branch_id']
											AND $this->getColumn('schedule_department_id') == $shifts[$iso_date_stamp][$shift_key]['department_id']
											AND $this->getColumn('job_id') == $shifts[$iso_date_stamp][$shift_key]['job_id']
											AND $this->getColumn('job_item_id') == $shifts[$iso_date_stamp][$shift_key]['job_item_id']
											AND ( $tmp_start_date == $start_time AND $tmp_end_date == $end_time )
											//AND TTDate::isTimeOverLap(	( defined('TIMETREX_API') ) ? TTDate::parseDateTime($shifts[$iso_date_stamp][$shift_key]['start_date']) : $shifts[$iso_date_stamp][$shift_key]['start_date'],
											//							( defined('TIMETREX_API') ) ? TTDate::parseDateTime($shifts[$iso_date_stamp][$shift_key]['end_date']) : $shifts[$iso_date_stamp][$shift_key]['end_date'],
											//							$start_time,
											//							$end_time ) == TRUE
											) {
										//Debug::text('		 Found OPEN shift conflict... Skipping...! Shift Key: '. $shift_key, __FILE__, __LINE__, __METHOD__, 10);

										//We need to track each shift_key that caused a conflict so it can't cause another conflict later on.
										//	Make sure we just track it on a per template basis though, otherwise the same $shift_key from a previous template can affect other templates.
										//	The above issue would show up as OPEN shifts not being overridden.
										//We also need to track which scheduled shift that caused a conflict so it can't cause another one later on.
										//	This prevents a single scheduled shift from overriding multiple OPEN shifts of different times.
										//However we need to be smarter about which shifts override which OPEN shifts...
										//	So if there are two open shifts, 10AM-4PM and 3:50PM-9PM, a 10AM-4PM scheduled shift overrides the OPEN shift that best fits it (10AM to 4PM, *not* 3:50-9PM)
										//	For now require an exact match to override an OPEN shift, if we start using partial schedules it gets much more complicated.
										//	Or we could introduce a hardcoded "fudge factor" setting (ie: 5 mins) that is always used instead.
										$open_shift_conflict_index['open'][$this->getID()][$shift_key] = TRUE;
										$open_shift_conflict_index['scheduled'][$shifts[$iso_date_stamp][$shift_key]['id']] = TRUE;
										continue 3;
									}
									unset( $tmp_start_date, $tmp_end_date );
								}
							}
							unset($tmp_index_user_id, $tmp_index_arr);
						}

						//This check has to occur after the committed schedule check, otherwise no committed schedules will appear.
						if ( ( $this->getColumn('recurring_schedule_control_start_date') != '' AND $i < TTDate::strtotime( $this->getColumn('recurring_schedule_control_start_date') ) )
								OR ( $this->getColumn('recurring_schedule_control_end_date') != '' AND $i > TTDate::strtotime( $this->getColumn('recurring_schedule_control_end_date') ) ) ) {
							//Debug::text('Skipping due to Recurring Schedule Start/End date: ID: '. $this->getColumn('id') .' User ID: '. $this->getColumn('user_id') .' I: '. $i .' Start Date: '. $this->getColumn('recurring_schedule_control_start_date') .' ('. TTDate::strtotime( $this->getColumn('recurring_schedule_control_start_date') ) .') End Date: '. $this->getColumn('recurring_schedule_control_end_date'), __FILE__, __LINE__, __METHOD__, 10);
							continue;
						}

						//Debug::text('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Start Date: '. TTDate::getDate('DATE+TIME', $start_time) .' End Date: '. TTDate::getDate('DATE+TIME', $end_time), __FILE__, __LINE__, __METHOD__, 10);

						$status_id = (int)$this->getColumn('status_id');
						$absence_policy_id = TTUUID::castUUID($this->getColumn('absence_policy_id'));
						$policy_group_id = TTUUID::castUUID($this->getColumn('policy_group_id'));

						if ( isset($holiday_data[$policy_group_id][$iso_date_stamp]) ) {
							//We have to assume they are eligible, because we really won't know
							//if they will have worked enough days or not. We could assume they
							//work whatever their schedule is, but chances are they will be eligible then anyways.
							//Debug::text('&nbsp;&nbsp;Found Holiday on this day...', __FILE__, __LINE__, __METHOD__, 10);
							$status_id = (int)$holiday_data[$policy_group_id][$iso_date_stamp]['status_id'];
							if ( isset($holiday_data[$policy_group_id][$iso_date_stamp]['absence_policy_id']) ) {
								$absence_policy_id = TTUUID::castUUID($holiday_data[$policy_group_id][$iso_date_stamp]['absence_policy_id']);
							}
						}

						/*
						$hourly_rate = Misc::MoneyFormat( $this->getColumn('user_wage_hourly_rate'), FALSE );

						if ( TTUUID::isUUID($absence_policy_id) AND $absence_policy_id != TTUUID::getZeroID() AND $absence_policy_id != TTUUID::getNotExistID()
								AND in_array( $absence_policy_type_id, $absence_policy_paid_type_options ) == FALSE ) {
							//UnPaid Absence.
							$total_time_wage = Misc::MoneyFormat(0);
						} else {
							$total_time_wage = Misc::MoneyFormat( bcmul( TTDate::getHours( $this->getTotalTime() ), $hourly_rate ), FALSE );
						}
						*/

						//Debug::text('I: '. $i .' N: '. $n .' User ID: '. $this->getColumn('user_id') .' Current Date: '. TTDate::getDate('DATE+TIME', $i) .' Current Week: '. $current_week .' Start Time: '. TTDate::getDate('DATE+TIME', $start_time ) .' Absence Policy: '. $absence_policy, __FILE__, __LINE__, __METHOD__, 10);
						//$shifts[$iso_date_stamp][$this->getColumn('user_id').$start_time] = array(
						$shifts[$iso_date_stamp][$n] = array(
															'pay_period_id' => FALSE,
															'user_id' => TTUUID::castUUID( $this->getColumn('user_id') ),
															//'user_created_by' => (int)$this->getColumn('user_created_by'),
															//'user_full_name' => ( $this->getColumn('user_id') > 0 ) ? Misc::getFullName( $this->getColumn('first_name'), NULL, $this->getColumn('last_name'), FALSE, FALSE ) : TTi18n::getText('OPEN'),
															//'user_full_name' => Misc::getFullName( $this->getColumn('first_name'), NULL, $this->getColumn('last_name'), FALSE, FALSE ),
															//'first_name' => $this->getColumn('first_name'),
															//'last_name' => $this->getColumn('last_name'),
															//'title_id' => (int)$this->getColumn('title_id'),
															//'title' => $this->getColumn('title'),
															//'group_id' => (int)$this->getColumn('group_id'),
															//'group' => $this->getColumn('group'),
															//'default_branch_id' => (int)$this->getColumn('default_branch_id'),
															//'default_branch' => $this->getColumn('default_branch'),
															//'default_department_id' => (int)$this->getColumn('default_department_id'),
															//'default_department' => $this->getColumn('default_department'),

															'branch_id' => $this->getBranch(),
															'department_id' => $this->getDepartment(),
															'job_id' => $this->getJob(),
															'job_item_id' => $this->getJobItem(),

															//'job_id' => (int)$this->getColumn('job_id'),
															//'job' => $this->getColumn('job'),
															//'job_status_id' => (int)$this->getColumn('job_status_id'),
															//'job_manual_id' => (int)$this->getColumn('job_manual_id'),
															//'job_branch_id' => (int)$this->getColumn('job_branch_id'),
															//'job_department_id' => (int)$this->getColumn('job_department_id'),
															//'job_group_id' => (int)$this->getColumn('job_group_id'),
															//'job_item_id' => (int)$this->getColumn('job_item_id'),
															//'job_item' => $this->getColumn('job_item'),

															'type_id' => 20, //Recurring
															'status_id' => (int)$status_id,

															'date_stamp' => TTDate::getAPIDate('DATE', strtotime( $iso_date_stamp ) ), //Date the schedule is displayed on
															'start_date_stamp' => ( defined('TIMETREX_API') ) ? TTDate::getAPIDate('DATE', $start_time ) : $start_time, //Date the schedule starts on.
															'start_date' => ( defined('TIMETREX_API') ) ? TTDate::getAPIDate('DATE+TIME', $start_time ) : $start_time,
															'end_date' => ( defined('TIMETREX_API') ) ? TTDate::getAPIDate('DATE+TIME', $end_time ) : $end_time,
															'start_time' => ( defined('TIMETREX_API') ) ? TTDate::getAPIDate('TIME', $start_time ) : $start_time,
															'end_time' => ( defined('TIMETREX_API') ) ? TTDate::getAPIDate('TIME', $end_time ) : $end_time,

															'start_time_stamp' => $start_time,
															'end_time_stamp' => $end_time,

															//These are no longer used.
															//'raw_start_time' => TTDate::getDate('DATE+TIME', $start_time ),
															//'raw_end_time' => TTDate::getDate('DATE+TIME', $end_time ),

															//Let RecurringScheduleFactory calculate these at that time instead.
															//'total_time' => $this->getTotalTime(),
															//'hourly_rate' => $hourly_rate,
															//'total_time_wage' => $total_time_wage,

															'note' => FALSE,

															'schedule_policy_id' => TTUUID::castUUID($this->getSchedulePolicyID()),
															'absence_policy_id' => $absence_policy_id,
															//'absence_policy' => $absence_policy,
															//'branch_id' => (int)$this->getColumn('schedule_branch_id'),
															//'branch' => $this->getColumn('schedule_branch'),
															//'department_id' => (int)$this->getColumn('schedule_department_id'),
															//'department' => $this->getColumn('schedule_department'),

															'created_by_id' => $this->getColumn('recurring_schedule_control_created_by'), //Whoever created the recurring schedule control object is consider the owner.
															'created_date' => $this->getCreatedDate(),
															'updated_date' => $this->getUpdatedDate(),
															);

						//Make sure we add in permission columns.
						$this->getPermissionColumns( $shifts[$iso_date_stamp][$n], TTUUID::castUUID( $this->getColumn('user_id') ), $this->getColumn('recurring_schedule_control_created_by'), $permission_children_ids );

						//$shifts_index[$iso_date_stamp][$this->getColumn('user_id')][] = $this->getColumn('user_id').$start_time;
						$shifts_index[$iso_date_stamp][$this->getColumn('user_id')][] = $n;

						$n++;
					}
					unset($open_shift_multiplier);
					unset($start_time, $end_time);
				} //else { //Debug::text('&nbsp;&nbsp;NOT active shift on this day... ID: '. $this->getColumn('id') .' User ID: '. $this->getColumn('user_id') .' Start Time: '. TTDate::getDate('DATE+TIME', $i), __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		if ( isset($shifts) ) {
			//Debug::Arr($shifts, 'Template Shifts: ', __FILE__, __LINE__, __METHOD__, 10);
			return $shifts;
		}

		return FALSE;
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
						case 'start_time':
						case 'end_time':
							$this->$function( TTDate::parseDateTime( $data[$key] ) );
							break;
						case 'total_time': //Ignore this field when setting data.
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
		$variable_function_map = $this->getVariableToFunctionMap();
		$data = array();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'status':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'start_time':
						case 'end_time':
							//$data[$variable] = ( defined('TIMETREX_API') ) ? TTDate::getAPIDate( 'TIME', TTDate::strtotime( $this->$function() ) ) : $this->$function();
							$data[$variable] = ( defined('TIMETREX_API') ) ? TTDate::getAPIDate( 'TIME', TTDate::strtotime( $this->$function() ) ) : $this->$function();
							//Need to include the raw_start_time, raw_end_time columns that are in EPOCH format so getShiftsByStartDateAndEndDate() can convert them as needed.
							$data['raw_'.$variable] = $this->$function();
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
	 * @return bool
	 */
	function preSave() {
		if ( $this->getStatus() == '' ) {
			$this->setStatus(10); //Working
		}

		if ( $this->getWeek() == '' ) {
			$this->setWeek(1);
		}

		if ( $this->getOpenShiftMultiplier() == '' ) {
			$this->setOpenShiftMultiplier(1);
		}

		if ( $this->getEndTime() < $this->getStartTime() ) {
			Debug::Text('EndTime spans midnight boundary! Increase by 24hrs ', __FILE__, __LINE__, __METHOD__, 10);
			$this->setEndTime( $this->getEndTime() + 86400 ); //End time spans midnight, add 24hrs.
		}

		if ( $this->getOpenShiftMultiplier() == '' ) {
			$this->setOpenShiftMultiplier( 1 );
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
		// Recurring Schedule Template Control
		if ( $this->getRecurringScheduleTemplateControl() == TTUUID::getZeroID() ) {
			$rstclf = TTnew( 'RecurringScheduleTemplateControlListFactory' ); /** @var RecurringScheduleTemplateControlListFactory $rstclf */
			$this->Validator->isResultSetWithRows(	'recurring_schedule_template_control',
															$rstclf->getByID($this->getRecurringScheduleTemplateControl()),
															TTi18n::gettext('Recurring Schedule Template Control is invalid')
														);
		}
		// Status
		if ( $this->getStatus() != '' ) {
			$this->Validator->inArrayKey( 'status',
										  $this->getStatus(),
										  TTi18n::gettext( 'Incorrect Status' ),
										  $this->getOptions( 'status' )
			);
		}
		// Week
		if ( $this->getWeek() > 0 ) {
			$this->Validator->isNumeric('week'.$this->getLabelID(),
												$this->getWeek(),
												TTi18n::gettext('Week is invalid')
											);
		}
		// In time
		$this->Validator->isDate(		'start_time'.$this->getLabelID(),
												$this->getStartTime(),
												TTi18n::gettext('Incorrect In time')
											);
		// Out time
		$this->Validator->isDate(		'end_time'.$this->getLabelID(),
												$this->getEndTime(),
												TTi18n::gettext('Incorrect Out time')
											);
		// Schedule Policy
		if ( $this->getSchedulePolicyID() != '' AND $this->getSchedulePolicyID() != TTUUID::getZeroID() ) {
			$splf = TTnew( 'SchedulePolicyListFactory' ); /** @var SchedulePolicyListFactory $splf */
			$this->Validator->isResultSetWithRows(	'schedule_policy',
															$splf->getByID($this->getSchedulePolicyID()),
															TTi18n::gettext('Schedule Policy is invalid')
														);
		}
		// Branch
		if ( $this->getBranch() != '' AND $this->getBranch() != TTUUID::getZeroID() AND $this->getBranch() != TTUUID::getNotExistID() ) {
			$blf = TTnew( 'BranchListFactory' ); /** @var BranchListFactory $blf */
			$this->Validator->isResultSetWithRows(	'branch',
															$blf->getByID($this->getBranch()),
															TTi18n::gettext('Branch does not exist')
														);
		}
		// Department
		if ( $this->getDepartment() != '' AND $this->getDepartment() != TTUUID::getZeroID() AND $this->getDepartment() != TTUUID::getNotExistID() ) {
			$dlf = TTnew( 'DepartmentListFactory' ); /** @var DepartmentListFactory $dlf */
			$this->Validator->isResultSetWithRows(	'department',
															$dlf->getByID($this->getDepartment()),
															TTi18n::gettext('Department does not exist')
														);
		}
		// Job
		if ( $this->getJob() != '' AND $this->getJob() != TTUUID::getZeroID() AND $this->getJob() != TTUUID::getNotExistID() ) {
			$jlf = TTnew( 'JobListFactory' ); /** @var JobListFactory $jlf */
			$this->Validator->isResultSetWithRows(	'job',
															$jlf->getByID($this->getJob()),
															TTi18n::gettext('Job does not exist')
														);
		}
		// Job Item
		if ( $this->getJobItem() != '' AND $this->getJobItem() != TTUUID::getZeroID() AND $this->getJobItem() != TTUUID::getNotExistID() ) {
			$jilf = TTnew( 'JobItemListFactory' ); /** @var JobItemListFactory $jilf */
			$this->Validator->isResultSetWithRows(	'job_item',
															$jilf->getByID($this->getJobItem()),
															TTi18n::gettext('Job Item does not exist')
														);
		}
		// Absence Policy ID
		if ( $this->getAbsencePolicyID()  != '' AND $this->getAbsencePolicyID() != TTUUID::getZeroID() ) {
			$aplf = TTnew( 'AbsencePolicyListFactory' ); /** @var AbsencePolicyListFactory $aplf */
			$this->Validator->isResultSetWithRows(	'absence_policy',
															$aplf->getByID($this->getAbsencePolicyID()),
															TTi18n::gettext('Invalid Absence Policy ID')
														);
		}
		// Open Shift Multiplier
		if ( $this->getOpenShiftMultiplier() != '' ) {
			$this->Validator->isNumeric( 'open_shift_multiplier',
										 $this->getOpenShiftMultiplier(),
										 TTi18n::gettext( 'Invalid Open Shift Multiplier' )
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		if ( $this->getRecurringScheduleTemplateControl() == FALSE ) {
			$this->Validator->isTRUE(		'recurring_schedule_template_control_id',
											FALSE,
											TTi18n::gettext('Invalid Recurring Schedule Template Control') );
		}

		//Get all pay period schedules and see if the total time exceeds the largest maximum shift time.
		$largest_maximum_shift_time = NULL;

		if ( is_object( $this->getRecurringScheduleTemplateControlObject() ) ) {
			$ppslf = TTnew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $ppslf */
			$ppslf->getByCompanyId( $this->getRecurringScheduleTemplateControlObject()->getCompany() );
			if ( $ppslf->getRecordCount() > 0 ) {
				foreach ( $ppslf as $pps_obj ) {
					if ( $pps_obj->getMaximumShiftTime() > $largest_maximum_shift_time ) {
						$largest_maximum_shift_time = $pps_obj->getMaximumShiftTime();
					}
				}
			}
			unset( $ppslf, $pps_obj );

			if ( $largest_maximum_shift_time > 0 AND $this->getTotalTime() > $largest_maximum_shift_time ) {
				$this->Validator->isTRUE( 'end_time' . $this->getLabelID(),
										  FALSE,
										  TTi18n::gettext( 'Schedule total time exceeds maximum shift time of' ) . ' ' . TTDate::getTimeUnit( $largest_maximum_shift_time ) . ' ' . TTi18n::getText( 'hrs set for all pay period schedules' ) );
			}
		}

		return TRUE;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getRecurringScheduleTemplateControl(), $log_action, TTi18n::getText('Recurring Schedule Week').': '. $this->getWeek() .' '. TTi18n::getText('from') .' '. TTDate::getDate('TIME', $this->getStartTime() ) .' - '. TTDate::getDate('TIME', $this->getEndTime() ), NULL, $this->getTable(), $this );
	}
}
?>
