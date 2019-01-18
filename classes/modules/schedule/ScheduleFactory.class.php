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
class ScheduleFactory extends Factory {
	protected $table = 'schedule';
	protected $pk_sequence_name = 'schedule_id_seq'; //PK Sequence name

	protected $user_date_obj = NULL;
	protected $schedule_policy_obj = NULL;
	protected $absence_policy_obj = NULL;
	protected $branch_obj = NULL;
	protected $department_obj = NULL;
	protected $pay_period_obj = NULL;
	protected $pay_period_schedule_obj = NULL;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = NULL ) {

		//Attempt to get the edition of the currently logged in users company, so we can better tailor the columns to them.
		$product_edition_id = Misc::getCurrentCompanyProductEdition();

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(

										//10  => TTi18n::gettext('OPEN'), //Available to be covered/overridden.
										//20 => TTi18n::gettext('Manual'),
										//30 => TTi18n::gettext('Recurring')
										//90  => TTi18n::gettext('Replaced'), //Replaced by another shift. Set replaced_id

										//Not displayed on schedules, used to overwrite recurring schedule if we want to change a 8AM-5PM recurring schedule
										//with a 6PM-11PM schedule? Although this can be done with an absence shift as well...
										//100 => TTi18n::gettext('Hidden'),
									);
				break;
			case 'status':
				$retval = array(
										//If user_id = 0 then the schedule is assumed to be open. That way its easy to assign recurring schedules
										//to user_id=0 for open shifts too.
										10 => TTi18n::gettext('Working'),
										20 => TTi18n::gettext('Absent'),
									);
				break;
			case 'columns':
				$retval = array(
										'-1000-first_name' => TTi18n::gettext('First Name'),
										'-1002-last_name' => TTi18n::gettext('Last Name'),
										'-1005-user_status' => TTi18n::gettext('Employee Status'),
										'-1010-title' => TTi18n::gettext('Title'),
										'-1039-group' => TTi18n::gettext('Group'),
										'-1040-default_branch' => TTi18n::gettext('Default Branch'),
										'-1050-default_department' => TTi18n::gettext('Default Department'),
										'-1160-branch' => TTi18n::gettext('Branch'),
										'-1170-department' => TTi18n::gettext('Department'),
										'-1200-status' => TTi18n::gettext('Status'),
										'-1210-schedule_policy' => TTi18n::gettext('Schedule Policy'),
										'-1212-absence_policy' => TTi18n::gettext('Absence Policy'),
										'-1215-date_stamp' => TTi18n::gettext('Date'),
										'-1220-start_time' => TTi18n::gettext('Start Time'),
										'-1230-end_time' => TTi18n::gettext('End Time'),
										'-1240-total_time' => TTi18n::gettext('Total Time'),
										'-1250-note' => TTi18n::gettext('Note'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);

				if ( $product_edition_id >= TT_PRODUCT_CORPORATE ) {
					$retval['-1180-job'] = TTi18n::gettext('Job');
					$retval['-1190-job_item'] = TTi18n::gettext('Task');
				}
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'first_name',
								'last_name',
								'status',
								'date_stamp',
								'start_time',
								'end_time',
								'total_time',
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
			case 'group_columns': //Columns available for grouping on the schedule.
				$retval = array(
								'title',
								'group',
								'default_branch',
								'default_department',
								'branch',
								'department',
								);

				if ( $product_edition_id >= TT_PRODUCT_CORPORATE ) {
					$retval[] = 'job';
					$retval[] = 'job_item';

				}
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
										'user_id' => 'User',
										'date_stamp' => 'DateStamp',
										'pay_period_id' => 'PayPeriod',
										'replaced_id' => 'ReplacedId',

										//'user_id' => FALSE,
										'first_name' => FALSE,
										'last_name' => FALSE,
										'user_status_id' => FALSE,
										'user_status' => FALSE,
										'group_id' => FALSE,
										'group' => FALSE,
										'title_id' => FALSE,
										'title' => FALSE,
										'default_branch_id' => FALSE,
										'default_branch' => FALSE,
										'default_department_id' => FALSE,
										'default_department' => FALSE,

										//'date_stamp' => FALSE,
										'start_date_stamp' => FALSE,
										'status_id' => 'Status',
										'status' => FALSE,
										'start_date' => FALSE,
										'end_date' => FALSE,
										'start_time_stamp' => FALSE,
										'end_time_stamp' => FALSE,
										'start_time' => 'StartTime',
										'end_time' => 'EndTime',
										'schedule_policy_id' => 'SchedulePolicyID',
										'schedule_policy' => FALSE,
										'absence_policy_id' => 'AbsencePolicyID',
										'absence_policy' => FALSE,
										'branch_id' => 'Branch',
										'branch' => FALSE,
										'department_id' => 'Department',
										'department' => FALSE,
										'job_id' => 'Job',
										'job' => FALSE,
										'job_item_id' => 'JobItem',
										'job_item' => FALSE,
										'total_time' => 'TotalTime',

										'other_id1' => 'OtherID1',
										'other_id2' => 'OtherID2',
										'other_id3' => 'OtherID3',
										'other_id4' => 'OtherID4',
										'other_id5' => 'OtherID5',

										'overwrite' => 'EnableOverwrite',

										'recurring_schedule_template_control_id' => 'RecurringScheduleTemplateControl',

										'note' => 'Note',

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
	 * @return bool
	 */
	function getSchedulePolicyObject() {
		return $this->getGenericObject( 'SchedulePolicyListFactory', $this->getSchedulePolicyID(), 'schedule_policy_obj' );
	}

	/**
	 * @return bool
	 */
	function getAbsencePolicyObject() {
		return $this->getGenericObject( 'AbsencePolicyListFactory', $this->getAbsencePolicyID(), 'absence_policy_obj' );
	}

	/**
	 * @return bool
	 */
	function getBranchObject() {
		return $this->getGenericObject( 'BranchListFactory', $this->getBranch(), 'branch_obj' );
	}

	/**
	 * @return bool
	 */
	function getDepartmentObject() {
		return $this->getGenericObject( 'DepartmentListFactory', $this->getDepartment(), 'department_obj' );
	}

	/**
	 * @return bool|null
	 */
	function getPayPeriodScheduleObject() {
		if ( is_object($this->pay_period_schedule_obj) ) {
			return $this->pay_period_schedule_obj;
		} else {
			if (TTUUID::isUUID( $this->getUser() ) AND $this->getUser() != TTUUID::getZeroID() AND $this->getUser() != TTUUID::getNotExistID() ) {
				$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
				$ppslf->getByUserId( $this->getUser() );
				if ( $ppslf->getRecordCount() == 1 ) {
					$this->pay_period_schedule_obj = $ppslf->getCurrent();
					return $this->pay_period_schedule_obj;
				}
			} elseif ( TTUUID::isUUID( $this->getUser() ) AND $this->getUser() != TTUUID::getZeroID() AND $this->getUser() != TTUUID::getNotExistID()
					AND TTUUID::isUUID( $this->getCompany() ) AND $this->getCompany() != TTUUID::getZeroID() AND $this->getCompany() != TTUUID::getNotExistID() ) {
				//OPEN SHIFT, try to find pay period schedule for the company
				$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
				$ppslf->getByCompanyId( $this->getCompany() );
				if ( $ppslf->getRecordCount() == 1 ) {
					Debug::Text('Using Company ID: '. $this->getCompany(), __FILE__, __LINE__, __METHOD__, 10);
					$this->pay_period_schedule_obj = $ppslf->getCurrent();
					return $this->pay_period_schedule_obj;
				}
			}

			return FALSE;
		}
	}

	/**
	 * @return bool|mixed
	 */
	function getCompany() {
		return $this->getGenericDataValue( 'company_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setCompany( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text('Company ID: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getUser() {
		return $this->getGenericDataValue( 'user_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setUser( $value ) {
		$value = TTUUID::castUUID( $value );
		//Need to be able to support user_id=0 for open shifts. But this can cause problems with importing punches with user_id=0.
		return $this->setGenericDataValue( 'user_id', $value );
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
	function setPayPeriod( $value = NULL ) {
		if ( $value == NULL AND $this->getUser() != '' AND $this->getUser() != TTUUID::getZeroID() ) { //Don't attempt to find pay period if user_id is not specified.
			$value = PayPeriodListFactory::findPayPeriod( $this->getUser(), $this->getDateStamp() );
		}
		$value = TTUUID::castUUID( $value );
		//Allow NULL pay period, incase its an absence or something in the future.
		//Cron will fill in the pay period later.
		return $this->setGenericDataValue( 'pay_period_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getReplacedId() {
		return $this->getGenericDataValue( 'replaced_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setReplacedId( $value ) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'replaced_id', $value );
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
	function setDateStamp( $value ) {
		$value = (int)$value;
		if ( $value > 0 ) {
			$value = TTDate::getMiddleDayEpoch( $value );
		}

		$retval = $this->setGenericDataValue( 'date_stamp', $value );
		$this->setPayPeriod(); //Force pay period to be set as soon as the date is.

		return $retval;
	}

	//
	//FIXME: The problem with assigning schedules to other dates than what they start on, is that employees can get confused
	//		 as to what day their shift actually starts on, especially when looking at iCal schedules, or printed schedules.
	//		 It can even be different for some employees if they are assigned to other pay period schedules.
	//		 However its likely they may already know this anyways, due to internal termination, if they call a Monday shift one that starts Sunday night for example.
	/**
	 * @return bool
	 */
	function findUserDate() {
		//Must allow user_id=0 for open shifts.

		/*
		This needs to be able to run before Validate is called, so we can validate the pay period schedule.
		*/
		if ( $this->getDateStamp() == FALSE ) {
			$this->setDateStamp( $this->getStartTime() );
		}

		//Debug::Text(' Finding User Date ID: '. TTDate::getDate('DATE+TIME', $this->getStartTime() ) .' User: '. $this->getUser(), __FILE__, __LINE__, __METHOD__, 10);
		if ( is_object( $this->getPayPeriodScheduleObject() ) ) {
			$user_date_epoch = $this->getPayPeriodScheduleObject()->getShiftAssignedDate( $this->getStartTime(), $this->getEndTime(), $this->getPayPeriodScheduleObject()->getShiftAssignedDay() );
		} else {
			$user_date_epoch = $this->getStartTime();
		}

		if ( isset($user_date_epoch) AND $user_date_epoch > 0 ) {
			//Debug::Text('Found DateStamp: '. $user_date_epoch .' Based On: '. TTDate::getDate('DATE+TIME', $user_date_epoch ), __FILE__, __LINE__, __METHOD__, 10);

			return $this->setDateStamp( $user_date_epoch );
		}

		Debug::Text('Not using timestamp only: '. TTDate::getDate('DATE+TIME', $this->getStartTime() ), __FILE__, __LINE__, __METHOD__, 10);
		return TRUE;
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
		$value = (int)$value;
		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getStartTime( $raw = FALSE ) {
		$value = $this->getGenericDataValue( 'start_time' );
		if ( $value !== FALSE ) {
			return TTDate::strtotime( $value );
		}

		return FALSE;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setStartTime( $value) {
		$value = (int)$value;
		return $this->setGenericDataValue( 'start_time', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getEndTime( $raw = FALSE ) {
		$value = $this->getGenericDataValue( 'end_time' );
		if ( $value !== FALSE ) {
			return TTDate::strtotime( $value );
		}

		return FALSE;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setEndTime( $value ) {
		$value = (int)$value;
		return $this->setGenericDataValue( 'end_time', $value );
	}

	/**
	 * @param $day_total_time
	 * @param bool $filter_type_id
	 * @return int
	 */
	function getMealPolicyDeductTime( $day_total_time, $filter_type_id = FALSE ) {
		$total_time = 0;

		$mplf = TTnew( 'MealPolicyListFactory' );
		if ( is_object( $this->getSchedulePolicyObject() ) AND $this->getSchedulePolicyObject()->isUsePolicyGroupMealPolicy() == FALSE ) {
			$policy_group_meal_policy_ids = $this->getSchedulePolicyObject()->getMealPolicy();
			$mplf->getByIdAndCompanyId( $policy_group_meal_policy_ids, $this->getCompany() );
		} else {
			$mplf->getByPolicyGroupUserId( $this->getUser() );
		}

		//Debug::Text('Meal Policy Record Count: '. $mplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $mplf->getRecordCount() > 0 ) {
			foreach( $mplf as $meal_policy_obj ) {
				if ( 		( $filter_type_id == FALSE AND ( $meal_policy_obj->getType() == 10 OR $meal_policy_obj->getType() == 20 ) )
							OR
							( $filter_type_id == $meal_policy_obj->getType() )
					) {
					if ( $day_total_time > $meal_policy_obj->getTriggerTime() ) {
						$total_time = $meal_policy_obj->getAmount(); //Only consider a single meal policy per shift, so don't add here.
					}
				}

			}
		}

		$total_time = ($total_time * -1);
		Debug::Text('Meal Policy Deduct Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);

		return $total_time;
	}

	/**
	 * @param $day_total_time
	 * @param bool $filter_type_id
	 * @return int
	 */
	function getBreakPolicyDeductTime( $day_total_time, $filter_type_id = FALSE ) {
		$total_time = 0;

		$bplf = TTnew( 'BreakPolicyListFactory' );
		if ( is_object( $this->getSchedulePolicyObject() ) AND $this->getSchedulePolicyObject()->isUsePolicyGroupBreakPolicy() == FALSE ) {
			$policy_group_break_policy_ids = $this->getSchedulePolicyObject()->getBreakPolicy();
			$bplf->getByIdAndCompanyId( $policy_group_break_policy_ids, $this->getCompany() );
		} else {
			$bplf->getByPolicyGroupUserId( $this->getUser() );
		}

		//Debug::Text('Break Policy Record Count: '. $bplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $bplf->getRecordCount() > 0 ) {
			foreach( $bplf as $break_policy_obj ) {
				if ( 	( $filter_type_id == FALSE AND ( $break_policy_obj->getType() == 10 OR $break_policy_obj->getType() == 20 ) )
						OR
						( $filter_type_id == $break_policy_obj->getType() )
					) {
					if ( $day_total_time > $break_policy_obj->getTriggerTime() ) {
						$total_time += $break_policy_obj->getAmount();
					}
				}
			}
		}

		$total_time = ($total_time * -1);
		Debug::Text('Break Policy Deduct Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);

		return $total_time;
	}

	/**
	 * @return bool|int
	 */
	function calcRawTotalTime() {
		if ( $this->getStartTime() > 0 AND $this->getEndTime() > 0 ) {
			//Due to DST, always pay the employee based on the time they actually worked,
			//which is handled automatically by simple epoch math.
			//Therefore in fall they get paid one hour more, and spring one hour less.
			$total_time = ( $this->getEndTime() - $this->getStartTime() ); // + TTDate::getDSTOffset( $this->getStartTime(), $this->getEndTime() );
			//Debug::Text('Start Time '.TTDate::getDate('DATE+TIME', $this->getStartTime()) .'('.$this->getStartTime().')  End Time: '. TTDate::getDate('DATE+TIME', $this->getEndTime()).'('.$this->getEndTime().') Total Time: '. TTDate::getHours( $total_time ), __FILE__, __LINE__, __METHOD__, 10);

			return $total_time;
		}

		return FALSE;
	}

	/**
	 * @return bool|int
	 */
	function calcTotalTime() {
		if ( $this->getStartTime() > 0 AND $this->getEndTime() > 0 ) {
			$total_time = $this->calcRawTotalTime();

			$total_time += $this->getMealPolicyDeductTime( $total_time );
			$total_time += $this->getBreakPolicyDeductTime( $total_time );

			return $total_time;
		}

		return FALSE;
	}

	/**
	 * @return bool|int
	 */
	function getTotalTime() {
		return $this->getGenericDataValue( 'total_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTotalTime( $value ) {
		$value = (int)$value;
		return $this->setGenericDataValue( 'total_time', $value );
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
	function getBranch() {
		return $this->getGenericDataValue( 'branch_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setBranch( $value ) {
		$value = TTUUID::castUUID( $value );
		if ( $this->getUser() != '' AND is_object( $this->getUserObject() ) AND $value == TTUUID::getNotExistID() ) { //Find default
			$value = $this->getUserObject()->getDefaultBranch();
			Debug::Text('Using Default Branch: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		}
		//If $id = -1 (default) makes it to this step (likely due to being an OPEN shift), force it to 0.
		if ( $value == TTUUID::getNotExistID() ) {
			$value = TTUUID::getZeroID();
		}
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

		if ( $this->getUser() != '' AND is_object( $this->getUserObject() ) AND $value == TTUUID::getNotExistID() ) { //Find default
			$value = $this->getUserObject()->getDefaultDepartment();
			Debug::Text('Using Default Department: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		}

		//If $id = -1 (default) makes it to this step (likely due to being an OPEN shift), force it to 0.
		if ( $value == TTUUID::getNotExistID() ) {
			$value = TTUUID::getZeroID();
		}
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
		if ( $this->getUser() != '' AND is_object( $this->getUserObject() ) AND $value == TTUUID::getNotExistID() ) { //Find default
			$value = $this->getUserObject()->getDefaultJob();
			Debug::Text('Using Default Job: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		}
		//If $id = -1 (default) makes it to this step (likely due to being an OPEN shift), force it to 0.
		if ( $value == TTUUID::getNotExistID() ) {
			$value = TTUUID::getZeroID();
		}
		if ( getTTProductEdition() < TT_PRODUCT_CORPORATE ) {
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
		if ( $this->getUser() != '' AND is_object( $this->getUserObject() ) AND $value == TTUUID::getNotExistID() ) { //Find default
			$value = $this->getUserObject()->getDefaultJobItem();
			Debug::Text('Using Default Job Item: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		}
		//If $id = -1 (default) makes it to this step (likely due to being an OPEN shift), force it to 0.
		if ( $value == TTUUID::getNotExistID() ) {
			$value = TTUUID::getZeroID();
		}
		if ( getTTProductEdition() < TT_PRODUCT_CORPORATE ) {
			$value = TTUUID::getZeroID();
		}
		return $this->setGenericDataValue( 'job_item_id', $value );
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
		//Need to be able to support user_id=0 for open shifts. But this can cause problems with importing punches with user_id=0.
		return $this->setGenericDataValue( 'recurring_schedule_template_control_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getNote() {
		return $this->getGenericDataValue( 'note' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setNote( $value ) {
		$value = trim($value);
		return $this->setGenericDataValue( 'note', $value );
	}

	/**
	 * @return bool
	 */
	function getOtherID1() {
		return $this->getGenericDataValue( 'other_id1' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOtherID1( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'other_id1', $value );
	}

	/**
	 * @return bool
	 */
	function getOtherID2() {
		return $this->getGenericDataValue( 'other_id2' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOtherID2( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'other_id2', $value );
	}

	/**
	 * @return bool
	 */
	function getOtherID3() {
		return $this->getGenericDataValue( 'other_id3' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOtherID3( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'other_id3', $value );
	}

	/**
	 * @return bool
	 */
	function getOtherID4() {
		return $this->getGenericDataValue( 'other_id4' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOtherID4( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'other_id4', $value );
	}

	/**
	 * @return bool
	 */
	function getOtherID5() {
		return $this->getGenericDataValue( 'other_id5' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOtherID5( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'other_id5', $value );
	}

	//Find the difference between $epoch and the schedule time, so we can determine the best schedule that fits.
	//**This returns FALSE when it doesn't match, so make sure you do an exact comparison using ===
	/**
	 * @param int $epoch EPOCH
	 * @param bool $status_id
	 * @return bool|int
	 */
	function inScheduleDifference( $epoch, $status_id = FALSE ) {
		$retval = FALSE;
		if ( $epoch >= $this->getStartTime() AND $epoch <= $this->getEndTime() ) {
			Debug::text('aWithin Schedule: '. $epoch, __FILE__, __LINE__, __METHOD__, 10);

			$retval = 0; //Within schedule start/end time, no difference.
		} else	{
			if ( ( $status_id == FALSE OR $status_id == 10 ) AND $epoch < $this->getStartTime() AND $this->inStartWindow( $epoch ) ) {
				$retval = ($this->getStartTime() - $epoch);
			} elseif ( ( $status_id == FALSE OR $status_id == 20 ) AND $epoch > $this->getEndTime() AND $this->inStopWindow( $epoch ) ) {
				$retval = ($epoch - $this->getEndTime());
			} else {
				$retval = FALSE; //Not within start/stop window at all, return FALSE.
			}
		}

		//Debug::text('Difference from schedule: "'. $retval .'" Epoch: '. $epoch .' Status: '. $status_id, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function inSchedule( $epoch ) {
		if ( $epoch >= $this->getStartTime() AND $epoch <= $this->getEndTime() ) {
			Debug::text('aWithin Schedule: '. $epoch, __FILE__, __LINE__, __METHOD__, 10);

			return TRUE;
		} elseif ( $this->inStartWindow( $epoch ) OR $this->inStopWindow( $epoch ) )  {
			Debug::text('bWithin Schedule: '. $epoch, __FILE__, __LINE__, __METHOD__, 10);

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return int
	 */
	function getStartStopWindow() {
		if ( is_object( $this->getSchedulePolicyObject() ) ) {
			$start_stop_window = (int)$this->getSchedulePolicyObject()->getStartStopWindow();
		} else {
			$start_stop_window = ( 3600 * 2 ); //Default to 2hr to help avoid In Late exceptions when they come in too early.
		}

		return $start_stop_window;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function inStartWindow( $epoch ) {
		//Debug::text(' Epoch: '. $epoch, __FILE__, __LINE__, __METHOD__, 10);

		if ( $epoch == '' ) {
			return FALSE;
		}

		$start_stop_window = $this->getStartStopWindow();
		if ( $epoch >= ( $this->getStartTime() - $start_stop_window ) AND $epoch <= ( $this->getStartTime() + $start_stop_window ) ) {
			Debug::text(' Within Start/Stop window: '. $start_stop_window, __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		//Debug::text(' NOT Within Start window. Epoch: '. $epoch .' Window: '. $start_stop_window, __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function inStopWindow( $epoch ) {
		//Debug::text(' Epoch: '. $epoch, __FILE__, __LINE__, __METHOD__, 10);

		if ( $epoch == '' ) {
			return FALSE;
		}

		$start_stop_window = $this->getStartStopWindow();
		if ( $epoch >= ( $this->getEndTime() - $start_stop_window ) AND $epoch <= ( $this->getEndTime() + $start_stop_window ) ) {
			Debug::text(' Within Start/Stop window: '. $start_stop_window, __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		//Debug::text(' NOT Within Stop window. Epoch: '. $epoch .' Window: '. $start_stop_window, __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	/**
	 * @param $schedule_shifts
	 * @param $recurring_schedule_shifts
	 * @return mixed
	 */
	function mergeScheduleArray( $schedule_shifts, $recurring_schedule_shifts) {
		//Debug::text('Merging Schedule, and Recurring Schedule Shifts: ', __FILE__, __LINE__, __METHOD__, 10);

		$ret_arr = $schedule_shifts;

		//Debug::Arr($schedule_shifts, '(c) Schedule Shifts: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( is_array($recurring_schedule_shifts) AND count($recurring_schedule_shifts) > 0 ) {
			foreach( $recurring_schedule_shifts as $date_stamp => $day_shifts_arr ) {
				//Debug::text('----------------------------------', __FILE__, __LINE__, __METHOD__, 10);
				//Debug::text('Date Stamp: '. TTDate::getDate('DATE+TIME', $date_stamp). ' Epoch: '. $date_stamp, __FILE__, __LINE__, __METHOD__, 10);
				//Debug::Arr($schedule_shifts[$date_stamp], 'Date Arr: ', __FILE__, __LINE__, __METHOD__, 10);
				foreach( $day_shifts_arr as$shift_arr ) {

					if ( isset($ret_arr[$date_stamp]) ) {
						//Debug::text('Already Schedule Shift on this day: '. TTDate::getDate('DATE', $date_stamp), __FILE__, __LINE__, __METHOD__, 10);

						//Loop through each shift on this day, and check for overlaps
						//Only include the recurring shift if ALL times DO NOT overlap
						$overlap = 0;
						foreach( $ret_arr[$date_stamp] as $tmp_shift_arr ) {
							if ( TTDate::isTimeOverLap( $shift_arr['start_time'], $shift_arr['end_time'], $tmp_shift_arr['start_time'], $tmp_shift_arr['end_time']) ) {
								//Debug::text('Times OverLap: '. TTDate::getDate('DATE+TIME', $shift_arr['start_time']), __FILE__, __LINE__, __METHOD__, 10);
								$overlap++;
							} //else { //Debug::text('Times DO NOT OverLap: '. TTDate::getDate('DATE+TIME', $shift_arr['start_time']), __FILE__, __LINE__, __METHOD__, 10);
						}

						if ( $overlap == 0 ) {
							//Debug::text('NO Times OverLap, using recurring schedule: '. TTDate::getDate('DATE+TIME', $shift_arr['start_time']), __FILE__, __LINE__, __METHOD__, 10);
							$ret_arr[$date_stamp][] = $shift_arr;
						}
					} else {
						//Debug::text('No Schedule Shift on this day: '. TTDate::getDate('DATE', $date_stamp), __FILE__, __LINE__, __METHOD__, 10);
						$ret_arr[$date_stamp][] = $shift_arr;
					}
				}
			}
		}

		return $ret_arr;
	}

	/**
	 * @param $filter_data
	 * @param string $permission_children_ids UUID
	 * @return array|bool
	 */
	function getScheduleArray( $filter_data ) {
		global $current_user;

		//Get all schedule data by general filter criteria.
		//Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( !isset($filter_data['start_date']) OR $filter_data['start_date'] == '' ) {
			return FALSE;
		}

		if ( !isset($filter_data['end_date']) OR $filter_data['end_date'] == '' ) {
			return FALSE;
		}

		$filter_data['start_date'] = TTDate::getBeginDayEpoch( $filter_data['start_date'] );
		$filter_data['end_date'] = TTDate::getEndDayEpoch( $filter_data['end_date'] );

		$pcf = TTnew( 'PayCodeFactory' );
		$absence_policy_paid_type_options = $pcf->getOptions('paid_type');

		$max_i = 0;

		$slf = TTnew( 'ScheduleListFactory' );
		if ( isset($filter_data['filter_items_per_page']) ) {
			if ( !isset($filter_data['filter_page']) ) {
				$filter_data['filter_page'] = 1;
			}
			$slf->getSearchByCompanyIdAndArrayCriteria( $current_user->getCompany(), $filter_data, $filter_data['filter_items_per_page'], $filter_data['filter_page'] );
		} else {
			$slf->getSearchByCompanyIdAndArrayCriteria( $current_user->getCompany(), $filter_data );
		}
		Debug::text('Found Scheduled Rows: '. $slf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($absence_policy_paid_type_options, 'Paid Absences: ', __FILE__, __LINE__, __METHOD__, 10);
		$scheduled_user_ids = array();
		if ( $slf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $slf->getRecordCount(), NULL, TTi18n::getText('Processing Committed Shifts...') );

			$schedule_shifts = array();
			$i = 0;
			foreach( $slf as $s_obj ) {
				if ( TTUUID::castUUID($s_obj->getUser()) == TTUUID::getZeroID() AND ( getTTProductEdition() == TT_PRODUCT_COMMUNITY OR $current_user->getCompanyObject()->getProductEdition() == 10 ) ) { continue; }

				//Debug::text('Schedule ID: '. $s_obj->getId() .' User ID: '. $s_obj->getUser() .' Start Time: '. $s_obj->getStartTime(), __FILE__, __LINE__, __METHOD__, 10);
				if ( TTUUID::isUUID( $s_obj->getAbsencePolicyID() ) AND $s_obj->getAbsencePolicyID() != TTUUID::getZeroID() AND $s_obj->getAbsencePolicyID() != TTUUID::getNotExistID() ) {
					$absence_policy_name = $s_obj->getColumn('absence_policy');
				} else {
					$absence_policy_name = NULL; //Must be NULL for it to appear as "N/A" in legacy interface.
				}

				$hourly_rate = Misc::MoneyFormat( $s_obj->getColumn('user_wage_hourly_rate'), FALSE );

				if ( 	$s_obj->getStatus() == 20 //Absence
						AND
						(
								$s_obj->getAbsencePolicyID() == TTUUID::getZeroID()
								OR
								(
									TTUUID::isUUID( $s_obj->getAbsencePolicyID() ) AND $s_obj->getAbsencePolicyID() != TTUUID::getZeroID() AND $s_obj->getAbsencePolicyID() != TTUUID::getNotExistID()
									AND is_object( $s_obj->getAbsencePolicyObject() )
									AND is_object( $s_obj->getAbsencePolicyObject()->getPayCodeObject() )
									AND in_array( $s_obj->getAbsencePolicyObject()->getPayCodeObject()->getType(), $absence_policy_paid_type_options ) == FALSE
								)
						) ) {
					//UnPaid Absence.
					$total_time_wage = Misc::MoneyFormat(0);
				} else {
					$total_time_wage = Misc::MoneyFormat( bcmul( TTDate::getHours( $s_obj->getColumn('total_time') ), $hourly_rate ), FALSE );
				}

				$iso_date_stamp = TTDate::getISODateStamp( $s_obj->getDateStamp() );
				$schedule_shifts[$iso_date_stamp][$i] = array(
													'id' => TTUUID::castUUID($s_obj->getID()),
													'replaced_id' => TTUUID::castUUID($s_obj->getReplacedID()),
													'recurring_schedule_id' => TTUUID::castUUID($s_obj->getColumn('recurring_schedule_id')),
													'pay_period_id' => TTUUID::castUUID($s_obj->getColumn('pay_period_id')),
													'user_id' => TTUUID::castUUID($s_obj->getUser()),
													'user_created_by' => TTUUID::castUUID($s_obj->getColumn('user_created_by')),
													'user_full_name' => ( TTUUID::isUUID($s_obj->getUser()) AND $s_obj->getUser() != TTUUID::getZeroID() AND $s_obj->getUser() != TTUUID::getNotExistID() ) ? Misc::getFullName( $s_obj->getColumn('first_name'), NULL, $s_obj->getColumn('last_name'), FALSE, FALSE ) : TTi18n::getText('OPEN'),
													'first_name' => (  TTUUID::isUUID($s_obj->getUser()) AND $s_obj->getUser() != TTUUID::getZeroID() AND $s_obj->getUser() != TTUUID::getNotExistID() ) ? $s_obj->getColumn('first_name') : TTi18n::getText('OPEN'),
													'last_name' => $s_obj->getColumn('last_name'),
													'title_id' => TTUUID::castUUID($s_obj->getColumn('title_id')),
													'title' => $s_obj->getColumn('title'),
													'group_id' => TTUUID::castUUID($s_obj->getColumn('group_id')),
													'group' => $s_obj->getColumn('group'),
													'default_branch_id' => TTUUID::castUUID($s_obj->getColumn('default_branch_id')),
													'default_branch' => $s_obj->getColumn('default_branch'),
													'default_department_id' => TTUUID::castUUID($s_obj->getColumn('default_department_id')),
													'default_department' => $s_obj->getColumn('default_department'),
													'default_job_id' => TTUUID::castUUID($s_obj->getColumn('default_job_id')),
													'default_job' => $s_obj->getColumn('default_job'),
													'default_job_item_id' => TTUUID::castUUID($s_obj->getColumn('default_job_item_id')),
													'default_job_item' => $s_obj->getColumn('default_job_item'),

													'job_id' => TTUUID::castUUID($s_obj->getColumn('job_id')),
													'job' => $s_obj->getColumn('job'),
													'job_status_id' => (int)$s_obj->getColumn('job_status_id'),
													'job_manual_id' => (int)$s_obj->getColumn('job_manual_id'),
													'job_branch_id' => TTUUID::castUUID($s_obj->getColumn('job_branch_id')),
													'job_department_id' => TTUUID::castUUID($s_obj->getColumn('job_department_id')),
													'job_group_id' => TTUUID::castUUID($s_obj->getColumn('job_group_id')),

													'job_address1' => $s_obj->getColumn('job_address1'),
													'job_address2' => $s_obj->getColumn('job_address2'),
													'job_city' => $s_obj->getColumn('job_city'),
													'job_country' => $s_obj->getColumn('job_country'),
													'job_province' => $s_obj->getColumn('job_province'),
													'job_postal_code' => $s_obj->getColumn('job_postal_code'),
													'job_longitude' => $s_obj->getColumn('job_longitude'),
													'job_latitude' => $s_obj->getColumn('job_latitude'),
													'job_location_note' => $s_obj->getColumn('job_location_note'),

													'job_item_id' => TTUUID::castUUID($s_obj->getColumn('job_item_id')),
													'job_item' => $s_obj->getColumn('job_item'),

													'type_id' => 10, //Committed
													'status_id' => (int)$s_obj->getStatus(),

													'date_stamp' => TTDate::getAPIDate( 'DATE', $s_obj->getDateStamp() ), //Date the schedule is displayed on
													'start_date_stamp' => TTDate::getAPIDate('DATE', $s_obj->getStartTime() ), //Date the schedule starts on.
													'start_date' => TTDate::getAPIDate('DATE+TIME', $s_obj->getStartTime() ),
													'end_date' => TTDate::getAPIDate('DATE+TIME', $s_obj->getEndTime() ),
													'end_date_stamp' => TTDate::getAPIDate('DATE', $s_obj->getEndTime() ), //Date the schedule ends on.
													'start_time' => TTDate::getAPIDate('TIME', $s_obj->getStartTime() ),
													'end_time' => TTDate::getAPIDate('TIME', $s_obj->getEndTime() ),

													'start_time_stamp' => $s_obj->getStartTime(),
													'end_time_stamp' => $s_obj->getEndTime(),

													'total_time' => $s_obj->getTotalTime(),

													'hourly_rate' => $hourly_rate,
													'total_time_wage' => $total_time_wage,

													'note' => $s_obj->getColumn('note'),

													'schedule_policy_id' => TTUUID::castUUID($s_obj->getSchedulePolicyID()),
													'absence_policy_id' => TTUUID::castUUID($s_obj->getAbsencePolicyID()),
													'absence_policy' => $absence_policy_name,
													'branch_id' => TTUUID::castUUID($s_obj->getBranch()),
													'branch' => $s_obj->getColumn('branch'),
													'department_id' => TTUUID::castUUID($s_obj->getDepartment()),
													'department' => $s_obj->getColumn('department'),

													'recurring_schedule_template_control_id' => $s_obj->getRecurringScheduleTemplateControl(),

													'created_by_id' => TTUUID::castUUID($s_obj->getCreatedBy()),
													'created_date' => $s_obj->getCreatedDate(),
													'updated_date' => $s_obj->getUpdatedDate(),

													'is_owner' => (bool)$s_obj->getColumn('is_owner'),
													'is_child' => (bool)$s_obj->getColumn('is_child'),
												);

				//Make sure we add in permission columns. They come from SQL now, so we don't need to use getPermissionColumns() at all anymore, let alone pass in $permission_children_ids
				//$this->getPermissionColumns( $schedule_shifts[$iso_date_stamp][$i], TTUUID::castUUID($s_obj->getUser()), $s_obj->getCreatedBy(), $permission_children_ids );

				unset($absence_policy_name);

				if ( isset($filter_data['include_all_users']) AND $filter_data['include_all_users'] == TRUE ) {
					$scheduled_user_ids[] = TTUUID::castUUID($s_obj->getUser()); //Used below if
				}

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $slf->getCurrentRow() );

				$i++;
			}
			$max_i = $i;
			unset($i);

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			//Debug::Arr($schedule_shifts, 'Committed Schedule Shifts: ', __FILE__, __LINE__, __METHOD__, 10);
			Debug::text('Processed Scheduled Rows: '. $slf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		} else {
			$schedule_shifts = array();
		}
		unset($slf);

		//Include employees without scheduled shifts.
		if ( isset($filter_data['include_all_users']) AND $filter_data['include_all_users'] == TRUE ) {
			if ( !isset($filter_data['exclude_id']) ) {
				$filter_data['exclude_id'] = array();
			}

			//If the user is searching for scheduled branch/departments, convert that to default branch/departments when Show All Employees is enabled.
			if ( isset($filter_data['branch_ids']) AND !isset($filter_data['default_branch_ids']) ) {
				$filter_data['default_branch_ids'] = $filter_data['branch_ids'];
			}
			if ( isset($filter_data['department_ids']) AND !isset($filter_data['default_department_ids']) ) {
				$filter_data['default_department_ids'] = $filter_data['department_ids'];
			}

			$scheduled_user_ids = ( empty($scheduled_user_ids) == FALSE ) ? array_unique($scheduled_user_ids) : array();
			$filter_data['exclude_id'] = array_merge( $filter_data['exclude_id'], $scheduled_user_ids );
			if ( isset($filter_data['exclude_id']) ) {
				//Debug::Arr($filter_data['exclude_id'], 'Including all employees. Excluded User Ids: ', __FILE__, __LINE__, __METHOD__, 10);
				//Debug::Arr($filter_data, 'All Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);

				//Only include active employees without any scheduled shifts.
				$filter_data['status_id'] = 10;

				$ulf = TTnew( 'UserListFactory' );
				$ulf->getAPISearchByCompanyIdAndArrayCriteria( $current_user->getCompany(), $filter_data );
				Debug::text('Found blank employees: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
				if ( $ulf->getRecordCount() > 0 ) {
					$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, TTi18n::getText('Processing Employees...') );

					$i = $max_i;
					foreach( $ulf as $u_obj ) {
						//Create dummy shift arrays with no start/end time.
						//$schedule_shifts[TTDate::getISODateStamp( $filter_data['start_date'] )][$u_obj->getID().TTDate::getBeginDayEpoch($filter_data['start_date'])] = array(
						$schedule_shifts[TTDate::getISODateStamp( $filter_data['start_date'] )][$i] = array(
															//'id' => TTUUID::castUUID($u_obj->getID()),
															'pay_period_id' => FALSE,
															'user_id' => TTUUID::castUUID($u_obj->getID()),
															'user_created_by' => TTUUID::castUUID($u_obj->getCreatedBy()),
															'user_full_name' => Misc::getFullName( $u_obj->getFirstName(), NULL, $u_obj->getLastName(), FALSE, FALSE ),
															'first_name' => $u_obj->getFirstName(),
															'last_name' => $u_obj->getLastName(),
															'title_id' => $u_obj->getTitle(),
															'title' => $u_obj->getColumn('title'),
															'group_id' => $u_obj->getColumn('group_id'),
															'group' => $u_obj->getColumn('group'),
															'default_branch_id' => $u_obj->getColumn('default_branch_id'),
															'default_branch' => $u_obj->getColumn('default_branch'),
															'default_department_id' => $u_obj->getColumn('default_department_id'),
															'default_department' => $u_obj->getColumn('default_department'),

															'branch_id' => TTUUID::castUUID($u_obj->getDefaultBranch()),
															'branch' => $u_obj->getColumn('default_branch'),
															'department_id' => TTUUID::castUUID($u_obj->getDefaultDepartment()),
															'department' => $u_obj->getColumn('default_department'),

															'created_by_id' => $u_obj->getCreatedBy(),
															'created_date' => $u_obj->getCreatedDate(),
															'updated_date' => $u_obj->getUpdatedDate(),
														);

						//Make sure we add in permission columns.
						$this->getPermissionColumns( $schedule_shifts[TTDate::getISODateStamp( $filter_data['start_date'] )][$i], TTUUID::castUUID($u_obj->getID()), $u_obj->getCreatedBy() );

						$this->getProgressBarObject()->set( $this->getAMFMessageID(), $ulf->getCurrentRow() );

						$i++;
					}

					$this->getProgressBarObject()->stop( $this->getAMFMessageID() );
				}
			}
			//Debug::Arr($schedule_shifts, 'Final Scheduled Shifts: ', __FILE__, __LINE__, __METHOD__, 10);
		}

		if ( isset($schedule_shifts) ) {
			return $schedule_shifts;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getEnableReCalculateDay() {
		if ( isset($this->recalc_day) ) {
			return $this->recalc_day;
		}

		return FALSE;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableReCalculateDay( $bool) {
		$this->recalc_day = $bool;

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function getEnableOverwrite() {
		if ( isset($this->overwrite) ) {
			return $this->overwrite;
		}

		return FALSE;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableOverwrite( $bool) {
		$this->overwrite = (bool)$bool;

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function getEnableTimeSheetVerificationCheck() {
		if ( isset($this->timesheet_verification_check) ) {
			return $this->timesheet_verification_check;
		}

		return FALSE;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableTimeSheetVerificationCheck( $bool) {
		$this->timesheet_verification_check = $bool;

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function handleDayBoundary() {
		//Debug::Text('Start Time '.TTDate::getDate('DATE+TIME', $this->getStartTime()) .'('.$this->getStartTime().')  End Time: '. TTDate::getDate('DATE+TIME', $this->getEndTime()).'('.$this->getEndTime().')', __FILE__, __LINE__, __METHOD__, 10);

		//This used to be done in Validate, but needs to be done in preSave too.
		//Allow 12:00AM to 12:00AM schedules for a total of 24hrs.
		if ( $this->getStartTime() != '' AND $this->getEndTime() != '' AND $this->getEndTime() <= $this->getStartTime() ) {
			//Since the initial end time is the same date as the start time, we need to see if DST affects between that end time and one day later. NOT the start time.
			//Due to DST, always pay the employee based on the time they actually worked,
			//which is handled automatically by simple epoch math.
			//Therefore in fall they get paid one hour more, and spring one hour less.
			//$this->setEndTime( $this->getEndTime() + ( 86400 + (TTDate::getDSTOffset( $this->getEndTime(), ($this->getEndTime() + 86400) ) ) ) ); //End time spans midnight, add 24hrs.
			$this->setEndTime( strtotime('+1 day', $this->getEndTime() ) ); //Using strtotime handles DST properly, whereas adding 86400 causes strange behavior.
			Debug::Text('EndTime spans midnight boundary! Bump to next day... New End Time: '. TTDate::getDate('DATE+TIME', $this->getEndTime()).'('.$this->getEndTime().')', __FILE__, __LINE__, __METHOD__, 10);
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function isConflicting() {
		Debug::Text('User ID: '. $this->getUser() .' DateStamp: '. $this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10);
		//Make sure we're not conflicting with any other schedule shifts.
		$slf = TTnew( 'ScheduleListFactory' );
		$slf->getConflictingByCompanyIdAndUserIdAndStartDateAndEndDate( $this->getCompany(), $this->getUser(), $this->getStartTime(), $this->getEndTime(), TTUUID::castUUID($this->getID()) );
		if ( $slf->getRecordCount() > 0 ) {
			foreach( $slf as $conflicting_schedule_shift_obj ) {
				if ( $conflicting_schedule_shift_obj->isNew() === FALSE
						AND $conflicting_schedule_shift_obj->getId() != $this->getId() ) {
					Debug::text('Conflicting Schedule Shift ID: '. $conflicting_schedule_shift_obj->getId() .' Schedule Shift ID: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
					return TRUE;
				}
			}
		}

		return FALSE;
	}


	/**
	 * @param object $rs_obj
	 * @return bool
	 */
	static function addPunchFromScheduleObject( $rs_obj) {
		//Make sure they are working for Auto-fill to kickin.
		Debug::text('Adding punch from schedule object...', __FILE__, __LINE__, __METHOD__, 10);

		$commit_punch_transaction = FALSE;

		$pf_in = new PunchFactory();

		if ( TTUUID::isUUID( $rs_obj->getUser() ) AND $rs_obj->getUser() != TTUUID::getZeroID() AND $rs_obj->getUser() != TTUUID::getNotExistID() ) {
			$pf_in->StartTransaction();

			$pf_in->setUser( $rs_obj->getUser() );
			$pf_in->setType( 10 ); //Normal
			$pf_in->setStatus( 10 ); //In
			$pf_in->setTimeStamp( $rs_obj->getStartTime(), TRUE );
			$pf_in->setPunchControlID( $pf_in->findPunchControlID() );
			$pf_in->setActualTimeStamp( $pf_in->getTimeStamp() );
			$pf_in->setOriginalTimeStamp( $pf_in->getTimeStamp() );

			if ( $pf_in->isValid() ) {
				Debug::text( 'Punch In: Valid!', __FILE__, __LINE__, __METHOD__, 10 );
				$pf_in->setEnableCalcTotalTime( FALSE );
				$pf_in->setEnableCalcSystemTotalTime( FALSE );
				$pf_in->setEnableCalcUserDateTotal( FALSE );
				$pf_in->setEnableCalcException( FALSE );

				$pf_in->Save( FALSE );
			} else {
				Debug::text( 'Punch In: InValid!', __FILE__, __LINE__, __METHOD__, 10 );
			}

			Debug::text( 'Punch Out: ' . TTDate::getDate( 'DATE+TIME', $rs_obj->getEndTime() ), __FILE__, __LINE__, __METHOD__, 10 );
			$pf_out = new PunchFactory();
			$pf_out->setUser( $rs_obj->getUser() );
			$pf_out->setType( 10 ); //Normal
			$pf_out->setStatus( 20 ); //Out
			$pf_out->setTimeStamp( $rs_obj->getEndTime(), TRUE );
			$pf_out->setPunchControlID( $pf_in->findPunchControlID() ); //Use the In punch object to find the punch_control_id.
			$pf_out->setActualTimeStamp( $pf_out->getTimeStamp() );
			$pf_out->setOriginalTimeStamp( $pf_out->getTimeStamp() );

			if ( $pf_out->isValid() ) {
				Debug::text( 'Punch Out: Valid!', __FILE__, __LINE__, __METHOD__, 10 );
				$pf_out->setEnableCalcTotalTime( TRUE );
				$pf_out->setEnableCalcSystemTotalTime( TRUE );
				$pf_out->setEnableCalcUserDateTotal( TRUE );
				$pf_out->setEnableCalcException( TRUE );

				$pf_out->Save( FALSE );
			} else {
				Debug::text( 'Punch Out: InValid!', __FILE__, __LINE__, __METHOD__, 10 );
			}

			if ( $pf_in->isValid() == TRUE OR $pf_out->isValid() == TRUE ) {
				Debug::text( 'Punch In and Out succeeded, saving punch control!', __FILE__, __LINE__, __METHOD__, 10 );

				$pcf = new PunchControlFactory();
				$pcf->setId( $pf_in->getPunchControlID() );

				if ( $pf_in->isValid() == TRUE ) {
					$pcf->setPunchObject( $pf_in );
				} elseif ( $pf_out->isValid() == TRUE ) {
					$pcf->setPunchObject( $pf_out );
				}

				$pcf->setBranch( TTUUID::castUUID($rs_obj->getBranch()) );
				$pcf->setDepartment( TTUUID::castUUID($rs_obj->getDepartment()) );
				$pcf->setJob( TTUUID::castUUID($rs_obj->getJob()) );
				$pcf->setJobItem( TTUUID::castUUID($rs_obj->getJobItem()) );

				$pcf->setEnableStrictJobValidation( TRUE );
				$pcf->setEnableCalcUserDateID( TRUE );
				$pcf->setEnableCalcTotalTime( TRUE );
				$pcf->setEnableCalcSystemTotalTime( TRUE );
				$pcf->setEnableCalcUserDateTotal( TRUE );
				$pcf->setEnableCalcException( TRUE );
				$pcf->setEnablePreMatureException( FALSE ); //Disable pre-mature exceptions at this point.

				if ( $pcf->isValid() ) {
					$pcf->Save( TRUE, TRUE );

					$commit_punch_transaction = TRUE;
				}
			} else {
				Debug::text( 'Punch In and Out failed, not saving punch control!', __FILE__, __LINE__, __METHOD__, 10 );
			}

			if ( $commit_punch_transaction == TRUE ) {
				Debug::text( 'Committing Punch Transaction!', __FILE__, __LINE__, __METHOD__, 10 );
				$pf_in->CommitTransaction();
			} else {
				Debug::text( 'Rolling Back Punch Transaction!', __FILE__, __LINE__, __METHOD__, 10 );
				$pf_in->FailTransaction();
				$pf_in->CommitTransaction();
				return FALSE;
			}

			unset( $pf_in, $pf_out, $pcf );
		} else {
			Debug::text('Skipping... User id is invalid.', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = TRUE ) {
		if ( $this->getDeleted() == FALSE ) {
			//
			// BELOW: Validation code moved from set*() functions.
			//

			// Company
			$clf = TTnew( 'CompanyListFactory' );
			$this->Validator->isResultSetWithRows( 'company',
												   $clf->getByID( $this->getCompany() ),
												   TTi18n::gettext( 'Company is invalid' )
			);
			// User
			if ( $this->getUser() != '' AND $this->getUser() != TTUUID::getZeroID() ) {
				$ulf = TTnew( 'UserListFactory' );
				$this->Validator->isResultSetWithRows( 'user',
													   $ulf->getByID( $this->getUser() ),
													   TTi18n::gettext( 'Invalid Employee' )
				);
			}
			// Pay Period
			if ( $this->getPayPeriod() !== FALSE AND $this->getPayPeriod() != TTUUID::getZeroID() ) {
				$pplf = TTnew( 'PayPeriodListFactory' );
				$this->Validator->isResultSetWithRows( 'pay_period',
													   $pplf->getByID( $this->getPayPeriod() ),
													   TTi18n::gettext( 'Invalid Pay Period' )
				);
			}
			// Scheduled Shift to replace.
			// Note: There was a bug where replaced shifts would be deleted due to the conflict check below. Causing the shift that replaced it to throw this validation error if it was later modified.
			//       To replicate it, create a committed OPEN shift, then using Find Available fill it. Then copy an identical shift from the previous day to day the shift was just filled on, and it would delete the replaced shift in the background.
			//       That should be resolved now that we do better checks around the replaced shifts.
			if ( $this->getReplacedId() !== FALSE AND $this->getID() != $this->getReplacedId() AND $this->getReplacedId() != TTUUID::getZeroID() ) {
				//Make sure we don't replace ourselves.
				$slf = TTnew( 'ScheduleListFactory' );
				$this->Validator->isResultSetWithRows( 'date_stamp',
													   $slf->getByID( $this->getReplacedId() ),
													   TTi18n::gettext( 'Scheduled Shift to replace does not exist' )
				);
			}
			// Date
			if ( $this->getDateStamp() != '' ) {
				$this->Validator->isDate( 'date_stamp',
										  $this->getDateStamp(),
										  TTi18n::gettext( 'Incorrect date' ) . '(a)'
				);
				if ( $this->Validator->isError( 'date_stamp' ) == FALSE ) {
					if ( $this->getDateStamp() <= 0 ) {
						$this->Validator->isTRUE( 'date_stamp',
												  FALSE,
												  TTi18n::gettext( 'Incorrect date' ) . '(b)'
						);
					}
				}
			}
			// Status
			if ( $this->getStatus() != '' ) {
				$this->Validator->inArrayKey( 'status',
											  $this->getStatus(),
											  TTi18n::gettext( 'Incorrect Status' ),
											  $this->getOptions( 'status' )
				);
			}

			// Start time
			if ( $this->getStartTime() != '' ) {
				$this->Validator->isDate( 'start_time',
										  $this->getStartTime(),
										  TTi18n::gettext( 'Incorrect start time' )
				);
			}

			// End time
			if ( $this->getEndTime() != '' ) {
				$this->Validator->isDate( 'end_time',
										  $this->getEndTime(),
										  TTi18n::gettext( 'Incorrect end time' )
				);
			}

			// Total time
			if ( $this->getTotalTime() != '' ) {
				$this->Validator->isNumeric( 'total_time',
											 $this->getTotalTime(),
											 TTi18n::gettext( 'Incorrect total time' )
				);
			}

			// Schedule Policy
			if ( $this->getAbsencePolicyID() != '' AND $this->getSchedulePolicyID() != TTUUID::getZeroID() ) {
				$splf = TTnew( 'SchedulePolicyListFactory' );
				$this->Validator->isResultSetWithRows( 'schedule_policy',
													   $splf->getByID( $this->getSchedulePolicyID() ),
													   TTi18n::gettext( 'Schedule Policy is invalid' )
				);
			}
			// Absence Policy
			if ( $this->getAbsencePolicyID() != '' AND $this->getAbsencePolicyID() != TTUUID::getZeroID() ) {
				$aplf = TTnew( 'AbsencePolicyListFactory' );
				$this->Validator->isResultSetWithRows( 'absence_policy',
													   $aplf->getByID( $this->getAbsencePolicyID() ),
													   TTi18n::gettext( 'Invalid Absence Policy' )
				);
			}
			// Branch
			if ( $this->getBranch() != '' AND $this->getBranch() != TTUUID::getZeroID() ) {
				$blf = TTnew( 'BranchListFactory' );
				$this->Validator->isResultSetWithRows( 'branch',
													   $blf->getByID( $this->getBranch() ),
													   TTi18n::gettext( 'Branch does not exist' )
				);
			}
			// Department
			if ( $this->getDepartment() != '' AND $this->getDepartment() != TTUUID::getZeroID() ) {
				$dlf = TTnew( 'DepartmentListFactory' );
				$this->Validator->isResultSetWithRows( 'department',
													   $dlf->getByID( $this->getDepartment() ),
													   TTi18n::gettext( 'Department does not exist' )
				);
			}
			// Job
			if ( $this->getJob() != '' AND $this->getJob() != TTUUID::getZeroID() ) {
				$jlf = TTnew( 'JobListFactory' );
				$this->Validator->isResultSetWithRows( 'job',
													   $jlf->getByID( $this->getJob() ),
													   TTi18n::gettext( 'Job does not exist' )
				);
			}
			// Task
			if ( $this->getJobItem() != '' AND $this->getJobItem() != TTUUID::getZeroID() ) {
				$jilf = TTnew( 'JobItemListFactory' );
				$this->Validator->isResultSetWithRows( 'job_item',
													   $jilf->getByID( $this->getJobItem() ),
													   TTi18n::gettext( 'Task does not exist' )
				);
			}
			// Recurring Schedule Template
			if ( $this->getRecurringScheduleTemplateControl() !== FALSE AND $this->getRecurringScheduleTemplateControl() != TTUUID::getZeroID() ) {
				$rstclf = TTnew( 'RecurringScheduleTemplateControlListFactory' );
				$this->Validator->isResultSetWithRows( 'recurring_schedule_template_control_id',
													   $rstclf->getByID( $this->getRecurringScheduleTemplateControl() ),
													   TTi18n::gettext( 'Invalid Recurring Schedule Template' )
				);
			}
			// Note
			if ( $this->getNote() != TTUUID::getZeroID() ) {
				$this->Validator->isLength( 'note',
											$this->getNote(),
											TTi18n::gettext( 'Note is too short or too long' ),
											0,
											1024
				);
			}
			// Other ID 1
			if ( $this->getOtherID1() != '' ) {
				$this->Validator->isLength( 'other_id1',
											$this->getOtherID1(),
											TTi18n::gettext( 'Other ID 1 is invalid' ),
											1, 255
				);
			}
			// Other ID 2
			if ( $this->getOtherID2() != '' ) {
				$this->Validator->isLength( 'other_id2',
											$this->getOtherID2(),
											TTi18n::gettext( 'Other ID 2 is invalid' ),
											1, 255
				);
			}
			// Other ID 3
			if ( $this->getOtherID3() != '' ) {
				$this->Validator->isLength( 'other_id3',
											$this->getOtherID3(),
											TTi18n::gettext( 'Other ID 3 is invalid' ),
											1, 255
				);
			}
			// Other ID 4
			if ( $this->getOtherID4() != '' ) {
				$this->Validator->isLength( 'other_id4',
											$this->getOtherID4(),
											TTi18n::gettext( 'Other ID 4 is invalid' ),
											1, 255
				);
			}
			// Other ID 5
			if ( $this->getOtherID5() != '' ) {
				$this->Validator->isLength( 'other_id5',
											$this->getOtherID5(),
											TTi18n::gettext( 'Other ID 5 is invalid' ),
											1, 255
				);
			}

			//
			// ABOVE: Validation code moved from set*() functions.
			//
		}

		Debug::Text('User ID: '. $this->getUser() .' DateStamp: '. $this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10);

		$this->handleDayBoundary();
		$this->findUserDate();

		if ( $this->getUser() === FALSE AND $this->Validator->getValidateOnly() == FALSE ) { //Use === so we still allow OPEN shifts (user_id=0)
			$this->Validator->isTRUE(	'user_id',
										FALSE,
										TTi18n::gettext('Employee is not specified') );
		}

		if ( $this->getDateStamp() == FALSE AND $this->Validator->getValidateOnly() == FALSE ) {
			Debug::Text('DateStamp is INVALID! ID: '. $this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10);
			$this->Validator->isTrue(		'date_stamp',
											FALSE,
											TTi18n::gettext('Date/Time is incorrect, or pay period does not exist for this date. Please create a pay period schedule and assign this employee to it if you have not done so already') );
		}

		if ( $this->getDateStamp() != FALSE AND $this->getStartTime() == '' AND $this->Validator->getValidateOnly() == FALSE ) {
			$this->Validator->isTrue(		'start_time',
											FALSE,
											TTi18n::gettext('In Time not specified'));
		}
		if ( $this->getDateStamp() != FALSE AND $this->getEndTime() == '' AND $this->Validator->getValidateOnly() == FALSE ) {
			$this->Validator->isTrue(		'end_time',
											FALSE,
											TTi18n::gettext('Out Time not specified'));
		}

		//Make sure schedules aren't being added after the employees termination date.
		//We must allow deleting schedules after their termination date so schedules can be cleaned up if necessary.
		if ( $this->getDeleted() == FALSE AND $this->getDateStamp() != FALSE AND is_object( $this->getUserObject() ) ) {
			if ( $this->getUserObject()->getHireDate() != '' AND TTDate::getBeginDayEpoch( $this->getDateStamp() ) < TTDate::getBeginDayEpoch( $this->getUserObject()->getHireDate() ) ) {
				$this->Validator->isTRUE(	'date_stamp',
											FALSE,
											TTi18n::gettext('Shift is before employees hire date') );
			}

			if ( $this->getUserObject()->getTerminationDate() != '' AND TTDate::getEndDayEpoch( $this->getDateStamp() ) > TTDate::getEndDayEpoch( $this->getUserObject()->getTerminationDate() ) ) {
				$this->Validator->isTRUE(	'date_stamp',
											FALSE,
											TTi18n::gettext('Shift is after employees termination date') );
			} elseif ( $this->getUserObject()->getStatus() != 10 AND $this->getUserObject()->getTerminationDate() == '' ) {
				$this->Validator->isTRUE(	'user_id',
											 FALSE,
											 TTi18n::gettext('Employee is not currently active') );
			}

			if ( $this->getStatus() == 20 AND TTUUID::castUUID( $this->getAbsencePolicyID() ) != TTUUID::getZeroID() AND ( $this->getDateStamp() != FALSE
						AND TTUUID::isUUID( $this->getUser() ) AND $this->getUser() != TTUUID::getZeroID() AND $this->getUser() != TTUUID::getNotExistID() ) ) {
				$pglf = TTNew('PolicyGroupListFactory');
				$pglf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), array('user_id' => array($this->getUser()), 'absence_policy' => array($this->getAbsencePolicyID()) ) );
				if ( $pglf->getRecordCount() == 0 ) {
					$this->Validator->isTRUE(	'absence_policy_id',
												 FALSE,
												 TTi18n::gettext('This absence policy is not available for this employee'));
				}
			}
		}

		//Make sure we check if the pay period is locked when adding/editing/deleting scheduled shifts,
		// as this can affect the timesheet and in cases where the we allow schedules to be adjusted but the timesheet is locked, things can get out of sync.
		if ( $this->getDateStamp() != FALSE AND is_object( $this->getPayPeriodObject() ) AND $this->getPayPeriodObject()->getIsLocked() == TRUE ) {
			$this->Validator->isTRUE(	'date_stamp',
										 FALSE,
										 TTi18n::gettext('Pay Period is Currently Locked') );
		}

		//Ignore conflicting time check when EnableOverwrite is set, as we will just be deleting any conflicting shift anyways.
		//Also ignore when setting OPEN shifts to allow for multiple.
		if ( $this->getEnableOverwrite() == FALSE AND $this->getDeleted() == FALSE AND ( $this->getDateStamp() != FALSE
						AND TTUUID::isUUID( $this->getUser() ) AND $this->getUser() != TTUUID::getZeroID() AND $this->getUser() != TTUUID::getNotExistID() )) {
			$this->Validator->isTrue(		'start_time',
											!$this->isConflicting(), //Reverse the boolean.
											TTi18n::gettext('Conflicting start/end time, schedule already exists for this employee'));
		} else {
			Debug::text('Not checking for conflicts... DateStamp: '. (int)$this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10);
		}

		if ( $ignore_warning == FALSE ) {
			//Warn users if they are trying to insert schedules too far in the future.
			if ( $this->getDateStamp() != FALSE AND $this->getDateStamp() > (time() + (86400 * 366 ) ) ) {
				$this->Validator->Warning( 'date_stamp', TTi18n::gettext('Date is more than one year in the future') );
			}

			if ( $this->getDateStamp() != FALSE
					AND is_object( $this->getPayPeriodObject() )
					AND is_object( $this->getPayPeriodObject()->getPayPeriodScheduleObject() )
					AND $this->getPayPeriodObject()->getPayPeriodScheduleObject()->getTimeSheetVerifyType() != 10 ) {
				//Find out if timesheet is verified or not.
				$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
				$pptsvlf->getByPayPeriodIdAndUserId(  $this->getPayPeriod(), $this->getUser() );
				if ( $pptsvlf->getRecordCount() > 0 ) {
					$this->Validator->Warning( 'date_stamp', TTi18n::gettext('Pay period is already verified, saving these changes will require it to be reverified') );
				}
			}
		}
																																												if ( $this->Validator->isValid() == TRUE AND $this->isNew() == TRUE ) { $obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65"; $obj_function = "\166\x61\154\x69\144\x61\164\145\114\x69\x63\145\x6e\x73\x65"; $obj_error_msg_function = "\x67\x65\x74\x46\x75\154\154\105\162\x72\x6f\x72\115\x65\x73\163\141\x67\x65"; @$obj = new $obj_class; $retval = $obj->{$obj_function}(); if ( $retval !== TRUE ) { $this->Validator->isTrue( 'lic_obj', FALSE, $obj->{$obj_error_msg_function}($retval) ); } }
		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getSchedulePolicyID() === FALSE ) {
			$this->setSchedulePolicyID( TTUUID::getZeroID() );
		}

		if ( $this->getAbsencePolicyID() === FALSE ) {
			$this->setAbsencePolicyID( TTUUID::getZeroID() );
		}

		if ( $this->getBranch() === FALSE ) {
			$this->setBranch( TTUUID::getZeroID() );
		}

		if ( $this->getDepartment() === FALSE ) {
			$this->setDepartment( TTUUID::getZeroID() );
		}

		if ( $this->getJob() === FALSE ) {
			$this->setJob( TTUUID::getZeroID() );
		}

		if ( $this->getJobItem() === FALSE ) {
			$this->setJobItem( TTUUID::getZeroID() );
		}

		$this->handleDayBoundary();
		$this->findUserDate();

		if ( $this->getPayPeriod() == FALSE ) {
			$this->setPayPeriod();
		}

		if ( $this->getTotalTime() == FALSE ) {
			$this->setTotalTime( $this->calcTotalTime() );
		}

		if ( $this->getStatus() == 10 ) {
			$this->setAbsencePolicyID( NULL );
		} elseif ( $this->getStatus() == FALSE ) {
			$this->setStatus( 10 ); //Default to working.
		}


		if ( $this->getEnableOverwrite() == TRUE  ) {

			$slf = TTnew( 'ScheduleListFactory' );

			//When overwriting OPEN shifts, always check based on branch/department/job/task, as there could be multople OPEN shifts that are duplicate it from one another.
			//  I don't see the point in overwriting OPEN shifts to begin with, but its possible the user may do it without fulling understanding.
			if ( $this->getUser() == TTUUID::getZeroID() ) {
				Debug::Text( 'Looking for Conflicting OPEN Shifts...', __FILE__, __LINE__, __METHOD__, 10 );
				$slf->getConflictingOpenShiftSchedule( $this->getCompany(), $this->getStartTime(), $this->getEndTime(), $this->getBranch(), $this->getDepartment(), $this->getJob(), $this->getJobItem(), $this->getReplacedId(), 1 ); //Limit 1;
			} else {
				//Delete any conflicting schedule shift before saving.
				$slf->getConflictingByCompanyIdAndUserIdAndStartDateAndEndDate( $this->getCompany(), $this->getUser(), $this->getStartTime(), $this->getEndTime(), $this->getId() ); //Don't consider the current record to be conflicting with itself (by passing id argument)
			}

			if ( $slf->getRecordCount() > 0 ) {
				Debug::Text( 'Found Conflicting Shift!!', __FILE__, __LINE__, __METHOD__, 10 );
				//Delete shifts.
				foreach ( $slf as $s_obj ) {
					Debug::Text( 'Deleting Schedule Shift ID: ' . $s_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
					$s_obj->setDeleted( TRUE );
					if ( $s_obj->isValid() ) {
						$s_obj->Save();
					}

					//Only delete the first one. Especially important if we are overwriting OPEN shifts where there could be multiple conflicting ones, and the reality is we don't know specifically which one to delete.
					//  Other than OPEN shifts, there should never be more than one record anyways, since records should never overlap or conflict with one another.
					break;
				}
			} else {
				Debug::Text( 'NO Conflicting Shift found...', __FILE__, __LINE__, __METHOD__, 10 );
			}

		}

		//Since Add Request icon was added to Attendance -> Schedule, a user could request to fill a *committed* open shift, and once the request is authorized, that open shift will still be there.
		//The same thing could happen if adding a new shift that was identical to the OPEN shift just with an employee assigned to it.
		//  So instead of deleting or overwriting the original OPEN shift, simply set "replaced_id" of the current shift to the OPEN shift ID, so we know it was replaced and therefore won't be displayed anymore.
		//    Now if the shift is deleted, the original OPEN shift will reappear, just like what would happen if it was a OPEN recurring schedule.
		//However, there is still the case of the user editing an OPEN shift and simply changing the employee to someone else, in this case the original OPEN shift would not be preseverd.
		//  Also need to handle the case of filling an OPEN shift, then editing the filled shift to change the start/end times or branch/department/job/task, that should no longer fill the OPEN shift.
		// 		But if they are changed back, it should refill the shift, because this acts the most similar to existing recurring schedule open shifts.
		if ( $this->getDeleted() == FALSE AND $this->Validator->getValidateOnly() == FALSE
				AND TTUUID::isUUID( $this->getUser() ) AND $this->getUser() != TTUUID::getZeroID() AND $this->getUser() != TTUUID::getNotExistID() ) { //Don't check for conflicting OPEN shifts when editing/saving an OPEN shift.
			$slf = TTnew( 'ScheduleListFactory' );
			$slf->getConflictingOpenShiftSchedule( $this->getCompany(), $this->getStartTime(), $this->getEndTime(), $this->getBranch(), $this->getDepartment(), $this->getJob(), $this->getJobItem(), $this->getReplacedId(), 1 ); //Limit 1;
			if ( $slf->getRecordCount() > 0 ) {
				Debug::Text( 'Found Conflicting OPEN Shift!!', __FILE__, __LINE__, __METHOD__, 10 );
				foreach ( $slf as $s_obj ) {
					if ( $this->getID() != $s_obj->getID() ) {
						if ( $s_obj->getUser() == TTUUID::getZeroID() ) { //Make sure we aren't replacing the same record as we are editing.
							Debug::Text( 'Replacing Schedule OPEN Shift ID: ' . $s_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
							$this->setReplacedId( $s_obj->getId() );
						} else {
							Debug::Text( 'ERROR: Returned conflicting shift that is not OPEN! ID: ' . $s_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
						}
					} else {
						Debug::Text( '  Not setting the replace_id to the same record that is being edited...'. $s_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
					}
				}
			} else {
				Debug::Text( 'NO Conflicting OPEN Shift found...', __FILE__, __LINE__, __METHOD__, 10 );
				$this->setReplacedId( TTUUID::getZeroID() );
			}
		} elseif ( $this->getUser() == TTUUID::getZeroID() ) {
			$this->setReplacedId( TTUUID::getZeroID() ); //Force this whenever its an OPEN shift.
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postSave() {

		if ( $this->getEnableTimeSheetVerificationCheck() ) {
			//Check to see if schedule is verified, if so unverify it on modified punch.
			//Make sure exceptions are calculated *after* this so TimeSheet Not Verified exceptions can be triggered again.
			if ( $this->getDateStamp() != FALSE
					AND is_object( $this->getPayPeriodObject() )
					AND is_object( $this->getPayPeriodObject()->getPayPeriodScheduleObject() )
					AND $this->getPayPeriodObject()->getPayPeriodScheduleObject()->getTimeSheetVerifyType() != 10 ) {
				//Find out if timesheet is verified or not.
				$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
				$pptsvlf->getByPayPeriodIdAndUserId(  $this->getPayPeriod(), $this->getUser() );
				if ( $pptsvlf->getRecordCount() > 0 ) {
					//Pay period is verified, delete all records and make log entry.
					//These can be added during the maintenance jobs, so the audit records are recorded as user_id=0, check those first.
					Debug::text('Pay Period is verified, deleting verification records: '. $pptsvlf->getRecordCount() .' User ID: '. $this->getUser() .' Pay Period ID: '. $this->getPayPeriod(), __FILE__, __LINE__, __METHOD__, 10);
					foreach( $pptsvlf as $pptsv_obj ) {
						TTLog::addEntry( $pptsv_obj->getId(), 500, TTi18n::getText('Schedule Modified After Verification').': '. UserListFactory::getFullNameById( $this->getUser() ) .' '. TTi18n::getText('Schedule').': '. TTDate::getDate('DATE', $this->getStartTime() ), NULL, $pptsvlf->getTable() );
						$pptsv_obj->setDeleted( TRUE );
						if ( $pptsv_obj->isValid() ) {
							$pptsv_obj->Save();
						}
					}
				}
			}
		}

		if ( $this->getEnableReCalculateDay() == TRUE ) {
			$data_diff = $this->getDataDifferences();

			//When comparing data_diff with timestamp columns in the DB, we need to convert them to epoch then compare again to make sure they are in fact different.
			if ( isset($data_diff['date_stamp']) AND TTDate::getMiddleDayEpoch( $this->getDateStamp() ) != TTDate::getMiddleDayEpoch( TTDate::parseDateTime( $data_diff['date_stamp'] ) ) ) {
				$data_diff['date_stamp'] = TTDate::parseDateTime( $data_diff['date_stamp'] );
			} else {
				$data_diff['date_stamp'] = NULL;
			}

			if ( !isset($data_diff['user_id']) ) {
				$data_diff['user_id'] = NULL;
			}

			//Calculate total time. Mainly for docked.
			//Calculate entire week as Over Schedule (Weekly) OT policy needs to be reapplied if the schedule changes.
			if ( $this->getDateStamp() != FALSE AND is_object( $this->getUserObject() ) ) {
				//When shifts are assigned to different days, we need to calculate both days the schedule touches, as the shift could be assigned to either of them.
				UserDateTotalFactory::reCalculateDay( $this->getUserObject(), array( $this->getDateStamp(), $data_diff['date_stamp'], $this->getStartTime(), $this->getEndTime() ), TRUE, FALSE );
			}

			if ( TTUUID::isUUID( $data_diff['user_id'] ) AND $data_diff['user_id'] != TTUUID::getZeroID() ) { //This needs to be outside the above is_object( $this->getUserObject() ) when switching a schedule from a user to OPEN shift, as is_object() fails in that case.
				$ulf = TTnew('UserListFactory');
				$ulf->getById( $data_diff['user_id'] );
				if ( $ulf->getRecordCount() == 1 ) {
					$old_user_obj = $ulf->getCurrent();
					Debug::text('  Recalculating Old User ID: '. $old_user_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);
					UserDateTotalFactory::reCalculateDay( $old_user_obj, array($this->getDateStamp(), $data_diff['date_stamp'], $this->getStartTime(), $this->getEndTime()), TRUE, FALSE );
				}
				unset($ulf, $old_user_obj);
			}
		}

		return TRUE;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
/*
 *			//Use date_stamp is determined from StartTime and EndTime now automatically, due to schedules honoring the "assign shifts to" setting
			//We need to set the UserDate as soon as possible.
			//Consider mass editing shifts, where user_id is not sent but user_date_id is. We need to prevent the shifts from being assigned to the OPEN user.
			if ( isset($data['user_id']) AND ( $data['user_id'] !== '' AND $data['user_id'] !== FALSE )
					AND isset($data['date_stamp']) AND $data['date_stamp'] != ''
					AND isset($data['start_time']) AND $data['start_time'] != '' ) {
				Debug::text('Setting User Date ID based on User ID:'. $data['user_id'] .' Date Stamp: '. $data['date_stamp'] .' Start Time: '. $data['start_time'], __FILE__, __LINE__, __METHOD__, 10);
				$this->setUserDate( $data['user_id'], TTDate::parseDateTime( $data['date_stamp'].' '.$data['start_time'] ) );
			} elseif ( isset( $data['user_date_id'] ) AND $data['user_date_id'] >= 0 ) {
				Debug::text(' Setting UserDateID: '. $data['user_date_id'], __FILE__, __LINE__, __METHOD__, 10);
				$this->setUserDateID( $data['user_date_id'] );
			} else {
				Debug::text(' NOT CALLING setUserDate or setUserDateID!', __FILE__, __LINE__, __METHOD__, 10);
			}
*/


			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						case 'user_id':
							//Make sure getUser() returns the proper user_id, otherwise mass edit will always assign shifts to OPEN employee.
							//We have to make sure the 'user_id' function map is FALSE as well, so we don't get a SQL error when getting the empty record set.
							$this->setUser( $data[$key] );
							break;
						case 'user_date_id': //Ignore explicitly set user_date_id here as its set above.
						case 'total_time': //If they try to specify total time, just skip it, as it gets calculated later anyways.
							break;
						case 'date_stamp':
							$this->$function( TTDate::parseDateTime( $data[$key] ) );
							break;
						case 'start_time':
							if ( method_exists( $this, $function ) ) {
								Debug::text('..Setting start time from EPOCH: "'. $data[$key]  .'"', __FILE__, __LINE__, __METHOD__, 10);

								if ( isset($data['start_date_stamp']) AND $data['start_date_stamp'] != '' AND isset($data[$key]) AND $data[$key] != '' ) {
									Debug::text(' aSetting start time... "'. $data['start_date_stamp'].' '.$data[$key] .'"', __FILE__, __LINE__, __METHOD__, 10);
									$this->$function( TTDate::parseDateTime( $data['start_date_stamp'].' '.$data[$key] ) ); //Prefix date_stamp onto start_time
								} elseif ( isset($data[$key]) AND $data[$key] != '' ) {
									//When start_time is provided as a full timestamp. Happens with audit log detail.
									Debug::text(' aaSetting start time...: '. $data[$key], __FILE__, __LINE__, __METHOD__, 10);
									$this->$function( TTDate::parseDateTime( $data[$key] ) );
								//} elseif ( is_object( $this->getUserDateObject() ) ) {
								//	Debug::text(' aaaSetting start time...: '. $this->getUserDateObject()->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10);
								//	$this->$function( TTDate::parseDateTime( TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp() ) .' '. $data[$key] ) );
								} else {
									Debug::text(' Not setting start time...', __FILE__, __LINE__, __METHOD__, 10);
								}
							}
							break;
						case 'end_time':
							if ( method_exists( $this, $function ) ) {
								Debug::text('..xSetting end time from EPOCH: "'. $data[$key]  .'"', __FILE__, __LINE__, __METHOD__, 10);

								if ( isset($data['start_date_stamp']) AND $data['start_date_stamp'] != '' AND isset($data[$key]) AND $data[$key] != '' ) {
									Debug::text(' aSetting end time... "'. $data['start_date_stamp'].' '.$data[$key] .'"', __FILE__, __LINE__, __METHOD__, 10);
									$this->$function( TTDate::parseDateTime( $data['start_date_stamp'].' '.$data[$key] ) ); //Prefix date_stamp onto end_time
								} elseif ( isset($data[$key]) AND $data[$key] != '' ) {
									Debug::text(' aaSetting end time...: '. $data[$key], __FILE__, __LINE__, __METHOD__, 10);
									//When end_time is provided as a full timestamp. Happens with audit log detail.
									$this->$function( TTDate::parseDateTime( $data[$key] ) );
								//} elseif ( is_object( $this->getUserDateObject() ) ) {
								//	Debug::text(' bbbSetting end time... "'. TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp() ) .' '. $data[$key]	 .'"', __FILE__, __LINE__, __METHOD__, 10);
								//	$this->$function( TTDate::parseDateTime( TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp() ) .' '. $data[$key] ) );
								} else {
									Debug::text(' Not setting end time...', __FILE__, __LINE__, __METHOD__, 10);
								}
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

			$this->handleDayBoundary(); //Make sure we handle day boundary before calculating total time.
			$this->setTotalTime( $this->calcTotalTime() ); //Calculate total time immediately after. This is required for proper audit logging too.
			$this->setEnableReCalculateDay(TRUE); //This is needed for Absence schedules to carry over to the timesheet.
			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param null $include_columns
	 * @param bool $permission_children_ids
	 * @return array
	 */
	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE  ) {
		$uf = TTnew( 'UserFactory' );
		$data = array();
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'first_name':
						case 'last_name':
							if ( TTUUID::isUUID( $this->getColumn('user_id') ) AND $this->getColumn('user_id') != TTUUID::getZeroID() AND $this->getColumn('user_id') != TTUUID::getNotExistID() ) {
								$data[$variable] = $this->getColumn( $variable );
							} else {
								$data[$variable] = TTi18n::getText('OPEN');
							}
							break;
						case 'user_id':
							//Make sure getUser() returns the proper user_id, otherwise mass edit will always assign shifts to OPEN employee.
							//We have to make sure the 'user_id' function map is FALSE as well, so we don't get a SQL error when getting the empty record set.
							$data[$variable] = $this->tmp_data['user_id'] = TTUUID::castUUID( $this->getColumn( $variable ) );
							break;
						case 'user_status_id':
						case 'group_id':
						case 'title_id':
						case 'default_branch_id':
						case 'default_department_id':
							$data[$variable] = TTUUID::castUUID( $this->getColumn( $variable ) );
							break;
						case 'group':
						case 'title':
						case 'default_branch':
						case 'default_department':
						case 'schedule_policy':
						case 'absence_policy':
						case 'branch':
						case 'department':
						case 'job':
						case 'job_item':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'status':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'user_status':
							$data[$variable] = Option::getByKey( (int)$this->getColumn( 'user_status_id' ), $uf->getOptions( 'status' ) );
							break;
						case 'date_stamp':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getDateStamp() );
							break;
						case 'start_date_stamp':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getStartTime() ); //Include both date+time
							break;
						case 'start_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', $this->getStartTime() ); //Include both date+time
							break;
						case 'end_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', $this->getEndTime() ); //Include both date+time
							break;
						case 'start_time_stamp':
							$data[$variable] = $this->getStartTime(); //Include start date/time in epoch format for sorting...
							break;
						case 'end_time_stamp':
							$data[$variable] = $this->getEndTime(); //Include end date/time in epoch format for sorting...
							break;
						case 'start_time':
						case 'end_time':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'TIME', $this->$function() ); //Just include time, so Mass Edit sees similar times without dates
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Schedule - Employee').': '. UserListFactory::getFullNameById( $this->getUser() ) .' '. TTi18n::getText('Start Time').': '. TTDate::getDate('DATE+TIME', $this->getStartTime() ) .' '. TTi18n::getText('End Time').': '. TTDate::getDate('DATE+TIME', $this->getEndTime() ), NULL, $this->getTable(), $this );
	}

}
?>