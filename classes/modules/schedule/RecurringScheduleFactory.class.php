<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2017 TimeTrex Software Inc.
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
class RecurringScheduleFactory extends Factory {
	protected $table = 'recurring_schedule';
	protected $pk_sequence_name = 'recurring_schedule_id_seq'; //PK Sequence name

	protected $user_date_obj = NULL;
	protected $schedule_policy_obj = NULL;
	protected $absence_policy_obj = NULL;
	protected $branch_obj = NULL;
	protected $department_obj = NULL;
	protected $pay_period_schedule_obj = NULL;

	function _getFactoryOptions( $name, $parent = NULL ) {

		//Attempt to get the edition of the currently logged in users company, so we can better tailor the columns to them.
		$product_edition_id = Misc::getCurrentCompanyProductEdition();

		$retval = NULL;
		switch( $name ) {
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

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'company_id' => 'Company',
										'user_id' => 'User',
										'date_stamp' => 'DateStamp',
										'pay_period_id' => 'PayPeriod',

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
										'pay_period_id' => FALSE,
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

										'note' => 'Note',
										'auto_fill' => 'AutoFill',

										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	function getPayPeriodObject() {
		return $this->getGenericObject( 'PayPeriodListFactory', $this->getPayPeriod(), 'pay_period_obj' );
	}

	function getSchedulePolicyObject() {
		return $this->getGenericObject( 'SchedulePolicyListFactory', $this->getSchedulePolicyID(), 'schedule_policy_obj' );
	}

	function getAbsencePolicyObject() {
		return $this->getGenericObject( 'AbsencePolicyListFactory', $this->getAbsencePolicyID(), 'absence_policy_obj' );
	}

	function getBranchObject() {
		return $this->getGenericObject( 'BranchListFactory', $this->getBranch(), 'branch_obj' );
	}

	function getDepartmentObject() {
		return $this->getGenericObject( 'DepartmentListFactory', $this->getDepartment(), 'department_obj' );
	}

	function getPayPeriodScheduleObject() {
		if ( is_object($this->pay_period_schedule_obj) ) {
			return $this->pay_period_schedule_obj;
		} else {
			if ( $this->getUser() > 0 ) {
				$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
				$ppslf->getByUserId( $this->getUser() );
				if ( $ppslf->getRecordCount() == 1 ) {
					$this->pay_period_schedule_obj = $ppslf->getCurrent();
					return $this->pay_period_schedule_obj;
				}
			} elseif ( $this->getUser() == 0 AND $this->getCompany() > 0 ) {
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

	function getCompany() {
		if ( isset($this->data['company_id']) ) {
			return (int)$this->data['company_id'];
		}

		return FALSE;
	}
	function setCompany($id) {
		$id = trim($id);

		$clf = TTnew( 'CompanyListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'company',
													$clf->getByID($id),
													TTi18n::gettext('Company is invalid')
													) ) {
			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return (int)$this->data['user_id'];
		}

		return FALSE;
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = TTnew( 'UserListFactory' );

		//Need to be able to support user_id=0 for open shifts. But this can cause problems with importing punches with user_id=0.
		if ( $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															TTi18n::gettext('Invalid User')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getRecurringScheduleControl() {
		if ( isset($this->data['recurring_schedule_control_id']) ) {
			return (int)$this->data['recurring_schedule_control_id'];
		}

		return FALSE;
	}
	function setRecurringScheduleControl($id) {
		$id = trim($id);

		$rsclf = TTnew( 'RecurringScheduleControlListFactory' );

		//Need to be able to support user_id=0 for open shifts. But this can cause problems with importing punches with user_id=0.
		if ( $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'recurring_schedule_control_id',
															$rsclf->getByID($id),
															TTi18n::gettext('Invalid Recurring Schedule')
															) ) {
			$this->data['recurring_schedule_control_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getRecurringScheduleTemplateControl() {
		if ( isset($this->data['recurring_schedule_template_control_id']) ) {
			return (int)$this->data['recurring_schedule_template_control_id'];
		}

		return FALSE;
	}
	function setRecurringScheduleTemplateControl($id) {
		$id = trim($id);

		$rstclf = TTnew( 'RecurringScheduleTemplateControlListFactory' );

		//Need to be able to support user_id=0 for open shifts. But this can cause problems with importing punches with user_id=0.
		if ( $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'recurring_schedule_template_control_id',
														$rstclf->getByID($id),
														TTi18n::gettext('Invalid Recurring Schedule Template')
														) ) {
			$this->data['recurring_schedule_template_control_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDateStamp( $raw = FALSE ) {
		if ( isset($this->data['date_stamp']) ) {
			if ( $raw === TRUE ) {
				return $this->data['date_stamp'];
			} else {
				return TTDate::strtotime( $this->data['date_stamp'] );
			}
		}

		return FALSE;
	}
	function setDateStamp($epoch) {
		$epoch = (int)$epoch;
		if ( $epoch > 0 ) {
			$epoch = TTDate::getMiddleDayEpoch( $epoch );
		}

		if	(	$this->Validator->isDate(		'date_stamp',
												$epoch,
												TTi18n::gettext('Incorrect date').'(a)')
			) {

			if	( $epoch > 0 ) {
				//Debug::Text(' Setting DateStamp to: '. (int)$epoch, __FILE__, __LINE__, __METHOD__, 10);
				$this->data['date_stamp'] = $epoch;
				return TRUE;
			} else {
				$this->Validator->isTRUE(		'date_stamp',
												FALSE,
												TTi18n::gettext('Incorrect date').'(b)');
			}
		}

		return FALSE;
	}

	//
	//FIXME: The problem with assigning schedules to other dates than what they start on, is that employees can get confused
	//		 as to what day their shift actually starts on, especially when looking at iCal schedules, or printed schedules.
	//		 It can even be different for some employees if they are assigned to other pay period schedules.
	//		 However its likely they may already know this anyways, due to internal termination, if they call a Monday shift one that starts Sunday night for example.
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

	function getStatus() {
		if ( isset($this->data['status_id']) ) {
			return (int)$this->data['status_id'];
		}

		return FALSE;
	}
	function setStatus($status) {
		$status = (int)$status;

		if ( $this->Validator->inArrayKey(	'status',
											$status,
											TTi18n::gettext('Incorrect Status'),
											$this->getOptions('status')) ) {

			$this->data['status_id'] = $status;

			return TRUE;
		}

		return FALSE;
	}

	function getStartTime( $raw = FALSE ) {
		if ( isset($this->data['start_time']) ) {
			return TTDate::strtotime( $this->data['start_time'] );
		}

		return FALSE;
	}
	function setStartTime($epoch) {
		$epoch = (int)$epoch;

		if	(	$this->Validator->isDate(		'start_time',
												$epoch,
												TTi18n::gettext('Incorrect start time'))
			) {

			$this->data['start_time'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getEndTime( $raw = FALSE ) {
		if ( isset($this->data['end_time']) ) {
			return TTDate::strtotime( $this->data['end_time'] );
		}

		return FALSE;
	}
	function setEndTime($epoch) {
		$epoch = (int)$epoch;

		if	(	$this->Validator->isDate(		'end_time',
												$epoch,
												TTi18n::gettext('Incorrect end time'))
			) {

			$this->data['end_time'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

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
	function calcTotalTime() {
		if ( $this->getStartTime() > 0 AND $this->getEndTime() > 0 ) {
			$total_time = $this->calcRawTotalTime();

			$total_time += $this->getMealPolicyDeductTime( $total_time );
			$total_time += $this->getBreakPolicyDeductTime( $total_time );

			return $total_time;
		}

		return FALSE;
	}

	function getTotalTime() {
		if ( isset($this->data['total_time']) ) {
			return (int)$this->data['total_time'];
		}
		return FALSE;
	}
	function setTotalTime($int) {
		$int = (int)$int;

		if	(	$this->Validator->isNumeric(		'total_time',
													$int,
													TTi18n::gettext('Incorrect total time')) ) {
			$this->data['total_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}


	function getSchedulePolicyID() {
		if ( isset($this->data['schedule_policy_id']) ) {
			return (int)$this->data['schedule_policy_id'];
		}

		return FALSE;
	}
	function setSchedulePolicyID($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		$splf = TTnew( 'SchedulePolicyListFactory' );

		if ( 	$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'schedule_policy',
														$splf->getByID($id),
														TTi18n::gettext('Schedule Policy is invalid')
													) ) {

			$this->data['schedule_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getAbsencePolicyID() {
		if ( isset($this->data['absence_policy_id']) ) {
			return (int)$this->data['absence_policy_id'];
		}

		return FALSE;
	}
	function setAbsencePolicyID($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		$aplf = TTnew( 'AbsencePolicyListFactory' );

		if (	$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'absence_policy',
														$aplf->getByID($id),
														TTi18n::gettext('Invalid Absence Policy')
														) ) {
			$this->data['absence_policy_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getBranch() {
		if ( isset($this->data['branch_id']) ) {
			return (int)$this->data['branch_id'];
		}

		return FALSE;
	}
	function setBranch($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		$blf = TTnew( 'BranchListFactory' );

		//Allow setting to Default (-1) so we can update it in real-time for the regular schedule view.
		if (  ( $id == -1 OR $id == 0 )
				OR
				$this->Validator->isResultSetWithRows(	'branch',
														$blf->getByID($id),
														TTi18n::gettext('Branch does not exist')
														) ) {
			$this->data['branch_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDepartment() {
		if ( isset($this->data['department_id']) ) {
			return (int)$this->data['department_id'];
		}

		return FALSE;
	}
	function setDepartment($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		$dlf = TTnew( 'DepartmentListFactory' );

		//Allow setting to Default (-1) so we can update it in real-time for the regular schedule view.
		if (  ( $id == -1 OR $id == 0 )
				OR
				$this->Validator->isResultSetWithRows(	'department',
														$dlf->getByID($id),
														TTi18n::gettext('Department does not exist')
														) ) {
			$this->data['department_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getJob() {
		if ( isset($this->data['job_id']) ) {
			return (int)$this->data['job_id'];
		}

		return FALSE;
	}
	function setJob($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jlf = TTnew( 'JobListFactory' );
		} else {
			$id = 0;
		}

		//Allow setting to Default (-1) so we can update it in real-time for the regular schedule view.
		if (  ( $id == -1 OR $id == 0 )
				OR
				$this->Validator->isResultSetWithRows(	'job',
														$jlf->getByID($id),
														TTi18n::gettext('Job does not exist')
														) ) {
			$this->data['job_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getJobItem() {
		if ( isset($this->data['job_item_id']) ) {
			return (int)$this->data['job_item_id'];
		}

		return FALSE;
	}
	function setJobItem($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jilf = TTnew( 'JobItemListFactory' );
		} else {
			$id = 0;
		}

		//Allow setting to Default (-1) so we can update it in real-time for the regular schedule view.
		if (  ( $id == -1 OR $id == 0 )
				OR
				$this->Validator->isResultSetWithRows(	'job_item',
														$jilf->getByID($id),
														TTi18n::gettext('Task does not exist')
														) ) {
			$this->data['job_item_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getNote() {
		if ( isset($this->data['note']) ) {
			return $this->data['note'];
		}

		return FALSE;
	}
	function setNote($val) {
		$val = trim($val);

		if	(	$val == ''
				OR
				$this->Validator->isLength(		'note',
												$val,
												TTi18n::gettext('Note is too short or too long'),
												0,
												1024) ) {

			$this->data['note'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getAutoFill() {
		if ( isset($this->data['auto_fill']) ) {
			return $this->fromBool( $this->data['auto_fill'] );
		}

		return FALSE;
	}
	function setAutoFill($bool) {
		$this->data['auto_fill'] = $this->toBool($bool);

		return TRUE;
	}
	
	//Find the difference between $epoch and the schedule time, so we can determine the best schedule that fits.
	//**This returns FALSE when it doesn't match, so make sure you do an exact comparison using ===
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

		Debug::text('Difference from schedule: "'. $retval .'" Epoch: '. $epoch .' Status: '. $status_id, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

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

	function inStartWindow( $epoch ) {
		//Debug::text(' Epoch: '. $epoch, __FILE__, __LINE__, __METHOD__, 10);

		if ( $epoch == '' ) {
			return FALSE;
		}

		if ( is_object( $this->getSchedulePolicyObject() ) ) {
			$start_stop_window = (int)$this->getSchedulePolicyObject()->getStartStopWindow();
		} else {
			$start_stop_window = ( 3600 * 2 ); //Default to 2hr to help avoid In Late exceptions when they come in too early.
		}

		if ( $epoch >= ( $this->getStartTime() - $start_stop_window ) AND $epoch <= ( $this->getStartTime() + $start_stop_window ) ) {
			Debug::text(' Within Start/Stop window: '. $start_stop_window, __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		//Debug::text(' NOT Within Start window. Epoch: '. $epoch .' Window: '. $start_stop_window, __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	function inStopWindow( $epoch ) {
		//Debug::text(' Epoch: '. $epoch, __FILE__, __LINE__, __METHOD__, 10);

		if ( $epoch == '' ) {
			return FALSE;
		}

		if ( is_object( $this->getSchedulePolicyObject() ) ) {
			$start_stop_window = (int)$this->getSchedulePolicyObject()->getStartStopWindow();
		} else {
			$start_stop_window = ( 3600 * 2 ) ; //Default to 2hr
		}

		if ( $epoch >= ( $this->getEndTime() - $start_stop_window ) AND $epoch <= ( $this->getEndTime() + $start_stop_window ) ) {
			Debug::text(' Within Start/Stop window: '. $start_stop_window, __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		//Debug::text(' NOT Within Stop window. Epoch: '. $epoch .' Window: '. $start_stop_window, __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

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

	function isConflicting() {
		Debug::Text('User ID: '. $this->getUser() .' DateStamp: '. $this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10);
		//Make sure we're not conflicting with any other schedule shifts.
		$slf = TTnew( 'RecurringScheduleListFactory' );
		$slf->getConflictingByUserIdAndStartDateAndEndDate( $this->getUser(), $this->getStartTime(), $this->getEndTime(), (int)$this->getID() );
		if ( $slf->getRecordCount() > 0 ) {
			foreach( $slf as $conflicting_schedule_shift_obj ) {
				if ( $conflicting_schedule_shift_obj->isNew() === FALSE
						AND $conflicting_schedule_shift_obj->getId() != $this->getId() ) {
					Debug::text('Conflicting Schedule Shift ID:'. $conflicting_schedule_shift_obj->getId() .' Schedule Shift ID: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	function Validate() {
		$this->handleDayBoundary();
		$this->findUserDate();
		Debug::Text('User ID: '. $this->getUser() .' DateStamp: '. $this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10);

		if ( $this->getUser() === FALSE ) { //Use === so we still allow OPEN shifts (user_id=0)
			$this->Validator->isTRUE(	'user_id',
										FALSE,
										TTi18n::gettext('Employee is not specified') );
		}

		if ( $this->getCompany() == FALSE ) {
			$this->Validator->isTrue(		'company_id',
											FALSE,
											TTi18n::gettext('Company is invalid'));
		}

		if ( $this->getDateStamp() == FALSE ) {
			Debug::Text('DateStamp is INVALID! ID: '. $this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10);
			$this->Validator->isTrue(		'date_stamp',
											FALSE,
											TTi18n::gettext('Date/Time is incorrect, or pay period does not exist for this date. Please create a pay period schedule and assign this employee to it if you have not done so already') );
		}

		if ( $this->getDateStamp() != FALSE AND $this->getStartTime() == '' ) {
			$this->Validator->isTrue(		'start_time',
											FALSE,
											TTi18n::gettext('In Time not specified'));
		}
		if ( $this->getDateStamp() != FALSE AND $this->getEndTime() == '' ) {
			$this->Validator->isTrue(		'end_time',
											FALSE,
											TTi18n::gettext('Out Time not specified'));
		}

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
			}
		}

		if ( $this->getStatus() == 20 AND $this->getAbsencePolicyID() != FALSE AND ( $this->getDateStamp() != FALSE AND is_object( $this->getUserObject() ) ) ) {
			$pglf = TTNew('PolicyGroupListFactory');
			$pglf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), array('user_id' => array($this->getUser()), 'absence_policy' => array($this->getAbsencePolicyID()) ) );
			if ( $pglf->getRecordCount() == 0 ) {
				$this->Validator->isTRUE(	'absence_policy_id',
								FALSE,
								TTi18n::gettext('This absence policy is not available for this employee'));
			}
		}

		//Ignore conflicting time check when EnableOverwrite is set, as we will just be deleting any conflicting shift anyways.
		//Also ignore when setting OPEN shifts to allow for multiple.
		if ( $this->getDeleted() == FALSE AND ( $this->getDateStamp() != FALSE AND $this->getUser() > 0 )) {
			$this->Validator->isTrue(		'start_time',
											!$this->isConflicting(), //Reverse the boolean.
											TTi18n::gettext('Conflicting start/end time, schedule already exists for this employee'));
		} else {
			Debug::text('Not checking for conflicts... DateStamp: '. (int)$this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10);
		}

		return TRUE;
	}

	function preSave() {
		if ( $this->getSchedulePolicyID() === FALSE ) {
			$this->setSchedulePolicyID(0);
		}

		if ( $this->getAbsencePolicyID() === FALSE ) {
			$this->setAbsencePolicyID(0);
		}

		if ( $this->getBranch() === FALSE ) {
			$this->setBranch(0);
		}

		if ( $this->getDepartment() === FALSE ) {
			$this->setDepartment(0);
		}

		if ( $this->getJob() === FALSE ) {
			$this->setJob(0);
		}

		if ( $this->getJobItem() === FALSE ) {
			$this->setJobItem(0);
		}

		$this->handleDayBoundary();
		$this->findUserDate();

		if ( $this->getTotalTime() == FALSE ) {
			$this->setTotalTime( $this->calcTotalTime() );
		}

		if ( $this->getStatus() == 10 ) {
			$this->setAbsencePolicyID( NULL );
		} elseif ( $this->getStatus() == FALSE ) {
			$this->setStatus( 10 ); //Default to working.
		}

		return TRUE;
	}

	function recalculateRecurringSchedules( $user_id, $start_date, $end_date ) {
		global $amf_message_id;

		//Used in UserFactory->postSave() to update recurring schedules immediately after employees are terminated/re-hired.

		$current_epoch = TTDate::getBeginWeekEpoch( TTDate::getBeginWeekEpoch( time() ) - 86400 );

		$start_date = TTDate::getBeginDayEpoch( $start_date );
		$end_date = TTDate::getEndDayEpoch( $end_date );
		Debug::text('Start Date: '. TTDate::getDate('DATE+TIME', $start_date ) .' End Date: '. TTDate::getDate('DATE+TIME', $end_date ), __FILE__, __LINE__, __METHOD__, 10);

		$rsclf = TTnew('RecurringScheduleControlListFactory');
		$rsclf->getByUserIDAndStartDateAndEndDate($user_id, $start_date, $end_date );
		if ( $rsclf->getRecordCount() > 0 ) {
			foreach( $rsclf as $rsc_obj ) {
				$rsf = TTnew('RecurringScheduleFactory');
				$rsf->StartTransaction();
				$rsf->clearRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getID(), ( $current_epoch - (86400 * 720) ), ( $current_epoch + (86400 * 720) ) );
				if ( $this->getDeleted() == FALSE ) {
					//FIXME: Put a cap on this perhaps, as 3mths into the future so we don't spend a ton of time doing this
					//if the user puts sets it to display 1-2yrs in the future. Leave creating the rest of the rows to the maintenance job?
					//Since things may change we will want to delete all schedules with each change, but only add back in X weeks at most unless from a maintenance job.
					$maximum_end_date = ( TTDate::getBeginWeekEpoch($current_epoch) + ( $rsc_obj->getDisplayWeeks() * ( 86400 * 7 ) ) );
					if ( $rsc_obj->getEndDate() != '' AND $maximum_end_date > $rsc_obj->getEndDate() ) {
						$maximum_end_date = $rsc_obj->getEndDate();
					}
					Debug::text('Recurring Schedule ID: '. $rsc_obj->getID() .' Maximum End Date: '. TTDate::getDate('DATE+TIME', $maximum_end_date ), __FILE__, __LINE__, __METHOD__, 10);

					$rsf->addRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getCompany(), $rsc_obj->getID(), $current_epoch, $maximum_end_date );
				}
				$rsf->CommitTransaction();
			}
		}

		return TRUE;
	}
	
	function clearRecurringSchedulesFromRecurringScheduleControl( $id, $start_date, $end_date ) {
		global $amf_message_id;
		
		$start_date = TTDate::getBeginDayEpoch( $start_date );
		$end_date = TTDate::getEndDayEpoch( $end_date );
		
		//$id can be an array, as HolidayFactory uses that to recalculate schedules on holidays.
		
		$rslf = TTnew('RecurringScheduleListFactory');
		$rslf->getByRecurringScheduleControlIDAndStartDateAndEndDate( $id, $start_date, $end_date );
		if ( $rslf->getRecordCount() ) {
			Debug::Arr($id, 'Recurring Schedule Control ID: Start Date: '. TTDate::getDate('DATE+TIME', $start_date) .' End Date: '. TTDate::getDate('DATE+TIME', $end_date) .' Deleting: '. $rslf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			foreach( $rslf as $rs_obj ) {
				$rs_obj->setDeleted(TRUE);
				if ( $rs_obj->isValid() ) {
					$rs_obj->Save();
				}
			}
		} else {
			Debug::text('No records to delete...', __FILE__, __LINE__, __METHOD__, 10);
		}

		Debug::text('Done...', __FILE__, __LINE__, __METHOD__, 10);
		return TRUE;
	}

	function addRecurringSchedulesFromRecurringScheduleControl( $company_id, $id, $start_date, $end_date ) {
		global $amf_message_id, $profiler;
		
		$start_date = TTDate::getBeginDayEpoch( $start_date );
		$end_date = TTDate::getEndDayEpoch( $end_date );

		//Get holidays
		//Make sure holiday policies are segragated by policy_group_id, otherwise all policies apply to all employees.
		$holiday_data = array();
		$hlf = TTnew( 'HolidayListFactory' );
		$hlf->getByCompanyIdAndStartDateAndEndDate( $company_id, $start_date, $end_date );
		Debug::text('Found Holiday Rows: '. $hlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		foreach( $hlf as $h_obj ) {
			//If there are conflicting holidays, one being absent and another being working, don't override the working one.
			//That way we default to working just in case.
			if ( !isset($holiday_data[(int)$h_obj->getColumn('policy_group_id')][TTDate::getISODateStamp($h_obj->getDateStamp())])
				AND is_object( $h_obj->getHolidayPolicyObject() )
				AND is_object( $h_obj->getHolidayPolicyObject()->getAbsencePolicyObject() )
				AND is_object( $h_obj->getHolidayPolicyObject()->getAbsencePolicyObject()->getPayCodeObject() ) ) {
				$holiday_data[(int)$h_obj->getColumn('policy_group_id')][TTDate::getISODateStamp($h_obj->getDateStamp())] = array('status_id' => (int)$h_obj->getHolidayPolicyObject()->getDefaultScheduleStatus(), 'absence_policy_id' => $h_obj->getHolidayPolicyObject()->getAbsencePolicyID(), 'type_id' => $h_obj->getHolidayPolicyObject()->getAbsencePolicyObject()->getPayCodeObject()->getType(), 'absence_policy' => $h_obj->getHolidayPolicyObject()->getAbsencePolicyObject()->getName() );
			} else {
				$holiday_data[(int)$h_obj->getColumn('policy_group_id')][TTDate::getISODateStamp($h_obj->getDateStamp())] = array('status_id' => 10 ); //Working
			}
		}
		unset($hlf);

		$rsclf = TTnew('RecurringScheduleControlListFactory');
		$rsclf->getByCompanyIdAndIDAndStartDateAndEndDate( $company_id, $id, $start_date, $end_date );
		if ( $rsclf->getRecordCount() > 0 ) {
			Debug::text('Recurring Schedule Control List Record Count: '. $rsclf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			foreach( $rsclf as $rsc_obj ) {
				$display_weeks_end_date = TTDate::getEndWeekEpoch( ( TTDate::getBeginWeekEpoch( time() ) + ( $rsc_obj->getDisplayWeeks() * (86400 * 7) ) ) );
				if ( $end_date > $display_weeks_end_date ) {
					$end_date = $display_weeks_end_date;
					Debug::text('  Adjusting End Date to: '. TTDate::getDate('DATE', $display_weeks_end_date), __FILE__, __LINE__, __METHOD__, 10);
				}
				unset($display_weeks_end_date);
				//$rsclf->StartTransaction(); Wrap each individual schedule in its own transaction instead.

				Debug::text('Recurring Schedule ID: '. $rsc_obj->getID() .' Start Date: '. TTDate::getDate('DATE', $start_date) .' End Date: '. TTDate::getDate('DATE', $end_date), __FILE__, __LINE__, __METHOD__, 10);
				//Debug::Arr($rsc_obj->getUser(), 'Users assigned to Schedule', __FILE__, __LINE__, __METHOD__, 10);

				$max_i = 0;
				$open_shift_conflict_index = array();
				$schedule_shifts = array();
				$schedule_shifts_index = array();

				$rstlf = TTnew( 'RecurringScheduleTemplateListFactory' );
				$rstlf->getByRecurringScheduleControlIdAndStartDateAndEndDate( $rsc_obj->getId(), $start_date, $end_date );
				//$rstlf->getSearchByCompanyIdAndArrayCriteria( $company_id, array('recurring_schedule_template_control_id' => array( $rsc_obj->getRecurringScheduleTemplateControl() ), 'include_user_ids' => (array)$rsc_obj->getUser(), 'start_date' => $start_date, 'end_date' => $end_date ), NULL, NULL, NULL, array( 'c.start_date' => 'asc', 'cb.user_id' => 'desc', 'a.week' => 'asc', 'a.start_time' => 'asc' ) );
				if ( $rstlf->getRecordCount() > 0 ) {
					$this->getProgressBarObject()->start( $amf_message_id, $rstlf->getRecordCount(), NULL, TTi18n::getText('ReCalculating Templates...') );

					Debug::Text('Total Templates: '. $rstlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					foreach( $rstlf as $rst_obj ) {
						$rst_obj->getShifts( $start_date, $end_date, $holiday_data, $max_i, $schedule_shifts, $schedule_shifts_index, $open_shift_conflict_index );
						$this->getProgressBarObject()->set( $amf_message_id, $rstlf->getCurrentRow() );
					}

					$this->getProgressBarObject()->stop( $amf_message_id );
				}
				//Debug::Arr($schedule_shifts, 'Recurring Schedule Shifts', __FILE__, __LINE__, __METHOD__, 10);

				if ( $schedule_shifts !== FALSE ) {
					$i = 0;
					$key = 0;
					$this->getProgressBarObject()->start( $amf_message_id, count($schedule_shifts), NULL, TTi18n::getText('ReCalculating Shifts...') );

					foreach( $schedule_shifts as $date_stamp => $recurring_schedule_shifts ) {
						Debug::text('Recurring Schedule Shift Date Stamp: '. $date_stamp .' Total Shifts: '. count($recurring_schedule_shifts), __FILE__, __LINE__, __METHOD__, 10);
						foreach($recurring_schedule_shifts as $recurring_schedule_shift ) {
							//Date is formatted as per date preferences, so make sure we parse it properly here/
							$recurring_schedule_shift_start_time = TTDate::parseDateTime( $recurring_schedule_shift['start_date'] );
							$recurring_schedule_shift_end_time = TTDate::parseDateTime( $recurring_schedule_shift['end_date'] );

							Debug::text('(After User TimeZone)Recurring Schedule Shift Start Time: '. TTDate::getDate('DATE+TIME', $recurring_schedule_shift_start_time ) .'('. $recurring_schedule_shift['start_date'] .') End Time: '. TTDate::getDate('DATE+TIME', $recurring_schedule_shift_end_time ), __FILE__, __LINE__, __METHOD__, 10);
							//Make sure punch pairs fall within limits

							//Debug::text('Recurring Schedule Shift Start Time falls within Limits: '. TTDate::getDate('DATE+TIME', $recurring_schedule_shift_start_time ), __FILE__, __LINE__, __METHOD__, 10);

							//Need to support recurring scheduled absences.
							$status_id = $recurring_schedule_shift['status_id'];
							$absence_policy_id = $recurring_schedule_shift['absence_policy_id'];

							//Make sure we not already added this schedule shift.
							//And that no schedule shifts overlap this one.
							//Use the isValid() function for this
							$rsf = TTnew('RecurringScheduleFactory');

							//$sf->StartTransaction(); //Transactions here may cause SQL upgrades to fail due to v1067

							$rsf->setCompany( $company_id );
							$rsf->setUser( $recurring_schedule_shift['user_id'] );
							$rsf->setRecurringScheduleControl( $rsc_obj->getID() );
							$rsf->setRecurringScheduleTemplateControl( $rsc_obj->getRecurringScheduleTemplateControl() );

							//Find the date that the shift will be assigned to so we know if its a holiday or not.
							if ( is_object( $rsf->getPayPeriodScheduleObject() ) ) {
								$date_stamp = $rsf->getPayPeriodScheduleObject()->getShiftAssignedDate( $recurring_schedule_shift_start_time, $recurring_schedule_shift_end_time, $rsf->getPayPeriodScheduleObject()->getShiftAssignedDay() );
							} else {
								$date_stamp = $recurring_schedule_shift_start_time;
							}

							//Is this a holiday?
							$hlf = new HolidayListFactory();
							$hlf->getByPolicyGroupUserIdAndDate( $recurring_schedule_shift['user_id'], TTDate::getBeginDayEpoch( $date_stamp ) );
							if ( $hlf->getRecordCount() > 0 ) {
								$h_obj = $hlf->getCurrent();
								Debug::text('Found Holiday! Name: '. $h_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);
								//Ignore after holiday eligibility when scheduling, since it will always fail.
								if ( $h_obj->isEligible( $recurring_schedule_shift['user_id'], TRUE ) ) {
									Debug::text('User is Eligible...', __FILE__, __LINE__, __METHOD__, 10);

									//Get Holiday Policy info
									$status_id = $h_obj->getHolidayPolicyObject()->getDefaultScheduleStatus();
									$absence_policy_id = $h_obj->getHolidayPolicyObject()->getAbsencePolicyID();
									Debug::text('Default Schedule Status: '. $status_id, __FILE__, __LINE__, __METHOD__, 10);
								} else {
									Debug::text('User is NOT Eligible...', __FILE__, __LINE__, __METHOD__, 10);
								}
							} else {
								Debug::text('No Holidays on this day: ', __FILE__, __LINE__, __METHOD__, 10);
							}
							unset($hlf, $h_obj);
							Debug::text('Schedule Status ID: '. $status_id, __FILE__, __LINE__, __METHOD__, 10);

							$profiler->startTimer( 'Add Schedule' );

							$rsf->setStatus( $status_id ); //Working
							$rsf->setStartTime( $recurring_schedule_shift_start_time );
							$rsf->setEndTime( $recurring_schedule_shift_end_time );
							$rsf->setSchedulePolicyID( (int)$recurring_schedule_shift['schedule_policy_id'] );

							if ( isset($absence_policy_id) AND $absence_policy_id > 0 ) {
								$rsf->setAbsencePolicyID( (int)$absence_policy_id );
							}
							unset($absence_policy_id);

							$rsf->setBranch( (int)$recurring_schedule_shift['branch_id'] );
							$rsf->setDepartment( (int)$recurring_schedule_shift['department_id'] );
							$rsf->setJob( (int)$recurring_schedule_shift['job_id'] );
							$rsf->setJobItem( (int)$recurring_schedule_shift['job_item_id'] );

							$rsf->setAutoFill( (int)$rsc_obj->getAutoFill() );

							//This causes confusion when debugging issues, they should only be set to the currently logged in user if triggered by them,
							//otherwise it can be set by the cron job.
							//$rsf->setUpdatedDate( $recurring_schedule_shift['updated_date'] );
							//$rsf->setCreatedDate( $recurring_schedule_shift['created_date'] );
							//if ( $recurring_schedule_shift['created_by_id'] > 0 ) {
							//	$rsf->setCreatedBy( $recurring_schedule_shift['created_by_id'] );
							//}
							
							if ( $rsf->isValid() ) {
								$rsf->Save();
								//$sf->CommitTransaction();
							} else {
								//$sf->FailTransaction();
								//$sf->CommitTransaction();
								Debug::text('Bad or conflicting Schedule: '. TTDate::getDate('DATE+TIME', $recurring_schedule_shift_start_time ), __FILE__, __LINE__, __METHOD__, 10);
							}

							$profiler->stopTimer( 'Add Schedule');

							$i++;
						}

						$this->getProgressBarObject()->set( $amf_message_id, $key );
						$key++;
					}
					Debug::text('Total Recurring Shifts added: '. $i, __FILE__, __LINE__, __METHOD__, 10);

					$this->getProgressBarObject()->stop( $amf_message_id );
				} else {
					Debug::text('No Recurring Schedule Days To Add! User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
				}

				//Set timezone back to default before we loop to the next user.
				//Without this the next start/end date will be in the last users timezone
				//and cause schedules to be included.
				//TTDate::setTimeZone();

				unset($ulf, $user_obj, $user_obj_prefs, $rsf);
			}
		}

		Debug::text('Done...', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	function addScheduleFromRecurringSchedule( $company_obj, $start_date, $end_date ) {
		$current_epoch = time();
		
		$company_id = $company_obj->getID();
		
		$rslf = TTNew('RecurringScheduleListFactory');
		$rslf->getByCompanyIDAndStartDateAndEndDateAndNoConflictingSchedule($company_id, $start_date, $end_date );
		Debug::text('Recurring Schedules Pending Commit: '. $rslf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $rslf->getRecordCount() > 0 ) {
			foreach( $rslf as $rs_obj ) {
				if ( $rs_obj->getUser() > 0 ) {
					$ulf = TTnew( 'UserListFactory' );
					$ulf->getById( $rs_obj->getUser() );
					if ( $ulf->getRecordCount() > 0 ) {
							$ulf->getCurrent()->getUserPreferenceObject()->setTimeZonePreferences();
					} else {
							//Use system timezone.
							TTDate::setTimeZone();
					}
				} else {
					//Use system timezone.
					TTDate::setTimeZone();
				}
				
				$sf = TTnew('ScheduleFactory');

				$sf->StartTransaction();

				$sf->setCompany( $company_id );
				$sf->setUser( $rs_obj->getUser() );
				//$sf->setRecurringScheduleControl( $rs_obj->getRecurringScheduleControl() );
				$sf->setRecurringScheduleTemplateControl( $rs_obj->getRecurringScheduleTemplateControl() );

				//Find the date that the shift will be assigned to so we know if its a holiday or not.
				//This is already determined in addRecurringSchedulesFromRecurringScheduleControl() above, no need to do it again?
				//if ( is_object( $sf->getPayPeriodScheduleObject() ) ) {
				//	$date_stamp = $sf->getPayPeriodScheduleObject()->getShiftAssignedDate( $rs_obj->getStartTime(), $rs_obj->getEndTime(), $sf->getPayPeriodScheduleObject()->getShiftAssignedDay() );
				//} else {
				//	$date_stamp = $rs_obj->getDateStamp();
				//}

				$sf->setStatus( $rs_obj->getStatus() ); //Working
				$sf->setStartTime( $rs_obj->getStartTime() );
				$sf->setEndTime( $rs_obj->getEndTime() );
				$sf->setSchedulePolicyID( (int)$rs_obj->getSchedulePolicyID() );
				$sf->setAbsencePolicyID( (int)$rs_obj->getAbsencePolicyID() );

				$sf->setBranch( (int)$rs_obj->getBranch() );
				$sf->setDepartment( (int)$rs_obj->getDepartment() );
				$sf->setJob( (int)$rs_obj->getJob() );
				$sf->setJobItem( (int)$rs_obj->getJobItem() );

				if ( $sf->isValid() ) {
					//Recalculate if its a absence schedule, so the holiday
					//policy takes effect.
					//Always re-calculate, this way it automatically applies dock time and holiday time.
					//Recalculate at the end of the day in a cronjob.
					//Part of the reason is that if they have a dock policy, it will show up as
					//docking them time during the entire day.
					//$sf->setEnableReCalculateDay(FALSE);

					//Only for holidays do we calculate the day right away.
					//So they don't have to wait 24hrs to see stat time.
					//Also need to recalculate if the schedule was added after the schedule has already started.
					if ( ( $rs_obj->getStatus() == 20 AND $rs_obj->getAutoFill() == FALSE )
							OR $rs_obj->getStartTime() <= $current_epoch ) {
						$sf->setEnableReCalculateDay(TRUE);
					} else {
						$sf->setEnableReCalculateDay(FALSE); //Don't need to re-calc right now?
					}

					$schedule_result = $sf->Save( FALSE );
					Debug::text('  Saving Commited Schedule: '. (int)$schedule_result .' Start Time: '. TTDate::getDate('DATE+TIME', $sf->getStartTime() ), __FILE__, __LINE__, __METHOD__, 10);

					$sf->CommitTransaction();

					if ( $schedule_result == TRUE
							AND $rs_obj->getUser() > 0
							AND ( $rs_obj->getAutoFill() == TRUE AND $company_obj->getProductEdition() >= TT_PRODUCT_PROFESSIONAL )
							AND $rs_obj->getStatus() == 10 ) {

						ScheduleFactory::addPunchFromScheduleObject( $rs_obj );
					} else {
						Debug::text('  Not processing AutoFill... AutoFill: '. (int)$rs_obj->getAutoFill() .' Status: '. $rs_obj->getStatus() .' Edition: '. $company_obj->getProductEdition(), __FILE__, __LINE__, __METHOD__, 10);
					}
				} else {
					$sf->FailTransaction();
					$sf->CommitTransaction();
					Debug::text('Bad or conflicting Schedule: '. TTDate::getDate('DATE+TIME', $rs_obj->getStartTime() ), __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		}

		return TRUE;
	}

	function postSave() {
		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {

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
			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE  ) {
		$uf = TTnew( 'UserFactory' );

		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'first_name':
						case 'last_name':
							if ( $this->getColumn('user_id') > 0 ) {
								$data[$variable] = $this->getColumn( $variable );
							} else {
								$data[$variable] = TTi18n::getText('OPEN');
							}
							break;
						case 'user_id':
							//Make sure getUser() returns the proper user_id, otherwise mass edit will always assign shifts to OPEN employee.
							//We have to make sure the 'user_id' function map is FALSE as well, so we don't get a SQL error when getting the empty record set.
							$data[$variable] = $this->tmp_data['user_id'] = (int)$this->getColumn( $variable );
							break;
						case 'user_status_id':
						case 'group_id':
						case 'title_id':
						case 'default_branch_id':
						case 'default_department_id':
						case 'pay_period_id':
							$data[$variable] = (int)$this->getColumn( $variable );
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
}
?>