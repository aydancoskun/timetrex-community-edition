<?php
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
 * @package Modules\Policy
 */
class PremiumPolicyFactory extends Factory {
	protected $table = 'premium_policy';
	protected $pk_sequence_name = 'premium_policy_id_seq'; //PK Sequence name

	protected $company_obj = null;
	protected $contributing_shift_policy_obj = null;
	protected $pay_code_obj = null;

	protected $branch_map = null;
	protected $department_map = null;
	protected $job_group_map = null;
	protected $job_map = null;
	protected $job_item_group_map = null;
	protected $job_item_map = null;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {

		$retval = null;
		switch ( $name ) {
			case 'type':
				$retval = [
						10  => TTi18n::gettext( 'Date/Time' ),
						20  => TTi18n::gettext( 'Shift Differential' ),
						30  => TTi18n::gettext( 'Meal/Break' ),
						40  => TTi18n::gettext( 'Callback' ),
						50  => TTi18n::gettext( 'Minimum Shift Time' ),
						90  => TTi18n::gettext( 'Holiday' ),
						100 => TTi18n::gettext( 'Advanced' ),
				];
				break;
			case 'min_max_time_type':
				$retval = [
						10 => TTi18n::gettext( 'Each Day' ),
						//20 => TTi18n::gettext('Each Shift'),
						30 => TTi18n::gettext( 'Each Punch Pair' ),
				];
				break;
			case 'pay_type':
				//How to calculate flat rate. Base it off the DIFFERENCE between there regular hourly rate
				//and the premium. So the PS Account could be postitive or negative amount
				$retval = [
						10 => TTi18n::gettext( 'Pay Multiplied By Factor' ),
						20 => TTi18n::gettext( 'Pay + Premium' ), //This is the same a Flat Hourly Rate (Absolute)
						30 => TTi18n::gettext( 'Flat Hourly Rate (Relative to Wage)' ), //This is a relative rate based on their hourly rate.
						32 => TTi18n::gettext( 'Flat Hourly Rate' ), //NOT relative to their default rate.
						40 => TTi18n::gettext( 'Minimum Hourly Rate (Relative to Wage)' ), //Pays whichever is greater, this rate or the employees original rate.
						42 => TTi18n::gettext( 'Minimum Hourly Rate' ), //Pays whichever is greater, this rate or the employees original rate.
				];
				break;
			case 'include_holiday_type':
				$retval = [
						10 => TTi18n::gettext( 'Have no effect' ),
						20 => TTi18n::gettext( 'Always on Holidays' ),
						30 => TTi18n::gettext( 'Never on Holidays' ),
				];
				break;
			case 'branch_selection_type':
				$retval = [
						10 => TTi18n::gettext( 'All Branches' ),
						20 => TTi18n::gettext( 'Only Selected Branches' ),
						30 => TTi18n::gettext( 'All Except Selected Branches' ),
				];
				break;
			case 'department_selection_type':
				$retval = [
						10 => TTi18n::gettext( 'All Departments' ),
						20 => TTi18n::gettext( 'Only Selected Departments' ),
						30 => TTi18n::gettext( 'All Except Selected Departments' ),
				];
				break;
			case 'job_group_selection_type':
				$retval = [
						10 => TTi18n::gettext( 'All Job Groups' ),
						20 => TTi18n::gettext( 'Only Selected Job Groups' ),
						30 => TTi18n::gettext( 'All Except Selected Job Groups' ),
				];
				break;
			case 'job_selection_type':
				$retval = [
						10 => TTi18n::gettext( 'All Jobs' ),
						20 => TTi18n::gettext( 'Only Selected Jobs' ),
						30 => TTi18n::gettext( 'All Except Selected Jobs' ),
				];
				break;
			case 'job_item_group_selection_type':
				$retval = [
						10 => TTi18n::gettext( 'All Task Groups' ),
						20 => TTi18n::gettext( 'Only Selected Task Groups' ),
						30 => TTi18n::gettext( 'All Except Selected Task Groups' ),
				];
				break;
			case 'job_item_selection_type':
				$retval = [
						10 => TTi18n::gettext( 'All Tasks' ),
						20 => TTi18n::gettext( 'Only Selected Tasks' ),
						30 => TTi18n::gettext( 'All Except Selected Tasks' ),
				];
				break;

			case 'columns':
				$retval = [
						'-1010-type'        => TTi18n::gettext( 'Type' ),
						'-1030-name'        => TTi18n::gettext( 'Name' ),
						'-1035-description' => TTi18n::gettext( 'Description' ),

						'-1900-in_use' => TTi18n::gettext( 'In Use' ),

						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'name',
						'description',
						'type',
						'updated_date',
						'updated_by',
				];
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [
						'name',
				];
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = [];
				break;
		}


		return $retval;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = [
				'id'                   => 'ID',
				'company_id'           => 'Company',
				'type_id'              => 'Type',
				'type'                 => false,
				'min_max_time_type_id' => 'MinMaxTimeType',
				'min_max_time_type'    => false,
				'name'                 => 'Name',
				'description'          => 'Description',

				'pay_type_id' => 'PayType',
				'pay_type'    => false,

				'start_date'                  => 'StartDate',
				'end_date'                    => 'EndDate',
				'start_time'                  => 'StartTime',
				'end_time'                    => 'EndTime',
				'daily_trigger_time'          => 'DailyTriggerTime',
				'maximum_daily_trigger_time'  => 'MaximumDailyTriggerTime',
				'weekly_trigger_time'         => 'WeeklyTriggerTime',
				'maximum_weekly_trigger_time' => 'MaximumWeeklyTriggerTime',
				'sun'                         => 'Sun',
				'mon'                         => 'Mon',
				'tue'                         => 'Tue',
				'wed'                         => 'Wed',
				'thu'                         => 'Thu',
				'fri'                         => 'Fri',
				'sat'                         => 'Sat',
				'include_holiday_type_id'     => 'IncludeHolidayType',
				'include_partial_punch'       => 'IncludePartialPunch',
				'maximum_no_break_time'       => 'MaximumNoBreakTime',
				'minimum_break_time'          => 'MinimumBreakTime',
				'minimum_time_between_shift'  => 'MinimumTimeBetweenShift',
				'minimum_first_shift_time'    => 'MinimumFirstShiftTime',
				'minimum_shift_time'          => 'MinimumShiftTime',
				'minimum_time'                => 'MinimumTime',
				'maximum_time'                => 'MaximumTime',
				'include_meal_policy'         => 'IncludeMealPolicy',
				'include_break_policy'        => 'IncludeBreakPolicy',

				'contributing_shift_policy_id' => 'ContributingShiftPolicy',
				'contributing_shift_policy'    => false,
				'pay_code_id'                  => 'PayCode',
				'pay_code'                     => false,
				'pay_formula_policy_id'        => 'PayFormulaPolicy',
				'pay_formula_policy'           => false,

				'branch'                           => 'Branch',
				'branch_selection_type_id'         => 'BranchSelectionType',
				'branch_selection_type'            => false,
				'exclude_default_branch'           => 'ExcludeDefaultBranch',
				'department'                       => 'Department',
				'department_selection_type_id'     => 'DepartmentSelectionType',
				'department_selection_type'        => false,
				'exclude_default_department'       => 'ExcludeDefaultDepartment',
				'job_group'                        => 'JobGroup',
				'job_group_selection_type_id'      => 'JobGroupSelectionType',
				'job_group_selection_type'         => false,
				'job'                              => 'Job',
				'job_selection_type_id'            => 'JobSelectionType',
				'job_selection_type'               => false,
				'job_item_group'                   => 'JobItemGroup',
				'job_item_group_selection_type_id' => 'JobItemGroupSelectionType',
				'job_item_group_selection_type'    => false,
				'job_item'                         => 'JobItem',
				'job_item_selection_type_id'       => 'JobItemSelectionType',
				'job_item_selection_type'          => false,
				'in_use'                           => false,
				'deleted'                          => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getCompanyObject() {
		return $this->getGenericObject( 'CompanyListFactory', $this->getCompany(), 'company_obj' );
	}

	/**
	 * @return bool
	 */
	function getContributingShiftPolicyObject() {
		return $this->getGenericObject( 'ContributingShiftPolicyListFactory', $this->getContributingShiftPolicy(), 'contributing_shift_policy_obj' );
	}

	/**
	 * @return bool
	 */
	function getPayCodeObject() {
		return $this->getGenericObject( 'PayCodeListFactory', $this->getPayCode(), 'pay_code_obj' );
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

		Debug::Text( 'Company ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getType() {
		return $this->getGenericDataValue( 'type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'type_id', $value );
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name ) {
		$name = trim( $name );
		if ( $name == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $this->getCompany() ),
				'name'       => TTi18n::strtolower( $name ),
		];

		$query = 'select id from ' . $this->getTable() . ' where company_id = ? AND lower(name) = ? AND deleted=0';
		$id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $id, 'Unique: ' . $name, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $id === false ) {
			return true;
		} else {
			if ( $id == $this->getId() ) {
				return true;
			}
		}

		return false;
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
	function setName( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDescription() {
		return $this->getGenericDataValue( 'description' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDescription( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'description', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getContributingShiftPolicy() {
		return $this->getGenericDataValue( 'contributing_shift_policy_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setContributingShiftPolicy( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'contributing_shift_policy_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getPayType() {
		return $this->getGenericDataValue( 'pay_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPayType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'pay_type_id', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getStartDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'start_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setStartDate( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'start_date', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getEndDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'end_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setEndDate( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'end_date', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getStartTime( $raw = false ) {
		$value = $this->getGenericDataValue( 'start_time' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setStartTime( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'start_time', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getEndTime( $raw = false ) {
		$value = $this->getGenericDataValue( 'end_time' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setEndTime( $value ) {
		$value = ( !is_int( $value ) ) ? trim( $value ) : $value; //Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'end_time', $value );
	}

	/**
	 * @return bool|int
	 */
	function getDailyTriggerTime() {
		return $this->getGenericDataValue( 'daily_trigger_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDailyTriggerTime( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'daily_trigger_time', $value );
	}

	/**
	 * @return bool|int
	 */
	function getWeeklyTriggerTime() {
		return $this->getGenericDataValue( 'weekly_trigger_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setWeeklyTriggerTime( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'weekly_trigger_time', $value );
	}

	/**
	 * @return bool|int
	 */
	function getMaximumDailyTriggerTime() {
		return $this->getGenericDataValue( 'maximum_daily_trigger_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMaximumDailyTriggerTime( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'maximum_daily_trigger_time', $value );
	}

	/**
	 * @return bool|int
	 */
	function getMaximumWeeklyTriggerTime() {
		return $this->getGenericDataValue( 'maximum_weekly_trigger_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMaximumWeeklyTriggerTime( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'maximum_weekly_trigger_time', $value );
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
		return $this->setGenericDataValue( 'sun', $this->toBool( $value ) );
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
	function setMon( $value ) {
		return $this->setGenericDataValue( 'mon', $this->toBool( $value ) );
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
	function setTue( $value ) {
		return $this->setGenericDataValue( 'tue', $this->toBool( $value ) );
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
	function setWed( $value ) {
		return $this->setGenericDataValue( 'wed', $this->toBool( $value ) );
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
	function setThu( $value ) {
		return $this->setGenericDataValue( 'thu', $this->toBool( $value ) );
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
	function setFri( $value ) {
		return $this->setGenericDataValue( 'fri', $this->toBool( $value ) );
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
	function setSat( $value ) {
		return $this->setGenericDataValue( 'sat', $this->toBool( $value ) );
	}


	/**
	 * @return bool
	 */
	function getIncludePartialPunch() {
		return $this->fromBool( $this->getGenericDataValue( 'include_partial_punch' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setIncludePartialPunch( $value ) {
		return $this->setGenericDataValue( 'include_partial_punch', $this->toBool( $value ) );
	}

	/**
	 * @return bool|int
	 */
	function getMaximumNoBreakTime() {
		return $this->getGenericDataValue( 'maximum_no_break_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMaximumNoBreakTime( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'maximum_no_break_time', $value );
	}

	/**
	 * @return bool|int
	 */
	function getMinimumBreakTime() {
		return $this->getGenericDataValue( 'minimum_break_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinimumBreakTime( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'minimum_break_time', $value );
	}

	/**
	 * @return bool|int
	 */
	function getMinimumTimeBetweenShift() {
		return $this->getGenericDataValue( 'minimum_time_between_shift' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinimumTimeBetweenShift( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'minimum_time_between_shift', $value );
	}

	/**
	 * @return bool|int
	 */
	function getMinimumFirstShiftTime() {
		return $this->getGenericDataValue( 'minimum_first_shift_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinimumFirstShiftTime( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'minimum_first_shift_time', $value );
	}

	/**
	 * @return bool|int
	 */
	function getMinimumShiftTime() {
		return $this->getGenericDataValue( 'minimum_shift_time' );
	}


	/**
	 * @param $value
	 * @return bool
	 */
	function setMinimumShiftTime( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'minimum_shift_time', $value );
	}


	/**
	 * @return bool|int
	 */
	function getMinMaxTimeType() {
		return $this->getGenericDataValue( 'min_max_time_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinMaxTimeType( $value ) {
		return $this->setGenericDataValue( 'min_max_time_type_id', (int)trim( $value ) );
	}

	/**
	 * @return bool|int
	 */
	function getMinimumTime() {
		return $this->getGenericDataValue( 'minimum_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinimumTime( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'minimum_time', $value );
	}

	/**
	 * @return bool|int
	 */
	function getMaximumTime() {
		return $this->getGenericDataValue( 'maximum_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMaximumTime( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'maximum_time', $value );
	}

	/**
	 * @return bool
	 */
	function getIncludeMealPolicy() {
		return $this->fromBool( $this->getGenericDataValue( 'include_meal_policy' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setIncludeMealPolicy( $value ) {
		return $this->setGenericDataValue( 'include_meal_policy', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getIncludeBreakPolicy() {
		return $this->fromBool( $this->getGenericDataValue( 'include_break_policy' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setIncludeBreakPolicy( $value ) {
		return $this->setGenericDataValue( 'include_break_policy', $this->toBool( $value ) );
	}

	/**
	 * @return bool|int
	 */
	function getIncludeHolidayType() {
		return $this->getGenericDataValue( 'include_holiday_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setIncludeHolidayType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'include_holiday_type_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPayCode() {
		return $this->getGenericDataValue( 'pay_code_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPayCode( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'pay_code_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPayFormulaPolicy() {
		return $this->getGenericDataValue( 'pay_formula_policy_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPayFormulaPolicy( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'pay_formula_policy_id', $value );
	}

	/*

	Branch/Department/Job/Task differential functions

	*/
	/**
	 * @return bool|int
	 */
	function getBranchSelectionType() {
		return $this->getGenericDataValue( 'branch_selection_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setBranchSelectionType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'branch_selection_type_id', $value );
	}

	/**
	 * @return bool
	 */
	function getExcludeDefaultBranch() {
		return $this->fromBool( $this->getGenericDataValue( 'exclude_default_branch' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setExcludeDefaultBranch( $value ) {
		return $this->setGenericDataValue( 'exclude_default_branch', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getBranch() {
		if ( TTUUID::isUUID( $this->getId() ) && $this->getId() != TTUUID::getZeroID() && $this->getId() != TTUUID::getNotExistID()
				&& isset( $this->branch_map[$this->getId()] ) ) {
			return $this->branch_map[$this->getId()];
		} else {
			$lf = TTnew( 'PremiumPolicyBranchListFactory' ); /** @var PremiumPolicyBranchListFactory $lf */
			$lf->getByPremiumPolicyId( $this->getId() );
			$list = [];
			foreach ( $lf as $obj ) {
				$list[] = $obj->getBranch();
			}

			if ( empty( $list ) == false ) {
				$this->branch_map[$this->getId()] = $list;

				return $this->branch_map[$this->getId()];
			}
		}

		return false;
	}

	/**
	 * @param string|string[] $ids UUID
	 * @return bool
	 */
	function setBranch( $ids ) {
		if ( !is_array( $ids ) && $ids != '' && TTUUID::isUUID( $ids ) ) { //Sometimes awesome box may pass through a zero UUID not as an array.
			$ids = [ $ids ];
		}

		Debug::text( 'Setting IDs...', __FILE__, __LINE__, __METHOD__, 10 );
		//Debug::Arr($ids, 'Setting Branch IDs...', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array( $ids ) ) {
			$tmp_ids = [];

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = TTnew( 'PremiumPolicyBranchListFactory' ); /** @var PremiumPolicyBranchListFactory $lf_a */
				$lf_a->getByPremiumPolicyId( $this->getId() );

				foreach ( $lf_a as $obj ) {
					$id = $obj->getBranch();
					Debug::text( 'Branch ID: ' . $obj->getBranch() . ' ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

					//Delete users that are not selected.
					if ( !in_array( $id, $ids ) ) {
						Debug::text( 'Deleting: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$obj->Delete();
						$obj->postSave(); //Clear cache.
					} else {
						//Save ID's that need to be updated.
						Debug::text( 'NOT Deleting : ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$tmp_ids[] = $id;
					}
				}
				unset( $id, $obj );
			}

			//Insert new mappings.
			$lf_b = TTnew( 'BranchListFactory' ); /** @var BranchListFactory $lf_b */

			foreach ( $ids as $id ) {
				if ( isset( $ids )
						&& TTUUID::isUUID( $id ) && $id != TTUUID::getZeroID() && $id != TTUUID::getNotExistID()
						&& !in_array( $id, $tmp_ids ) ) {
					$f = TTnew( 'PremiumPolicyBranchFactory' ); /** @var PremiumPolicyBranchFactory $f */
					$f->setPremiumPolicy( $this->getId() );
					$f->setBranch( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ( $this->Validator->isTrue( 'branch',
												   $f->isValid(),
												   TTi18n::gettext( 'Selected Branch is invalid' ) . ' (' . $obj->getName() . ')' ) ) {
						$f->save();
					}
				}
			}

			return true;
		}

		Debug::text( 'No IDs to set.', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return bool|int
	 */
	function getDepartmentSelectionType() {
		return $this->getGenericDataValue( 'department_selection_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDepartmentSelectionType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'department_selection_type_id', $value );
	}

	/**
	 * @return bool
	 */
	function getExcludeDefaultDepartment() {
		return $this->fromBool( $this->getGenericDataValue( 'exclude_default_department' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setExcludeDefaultDepartment( $value ) {
		return $this->setGenericDataValue( 'exclude_default_department', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getDepartment() {
		if ( TTUUID::isUUID( $this->getId() ) && $this->getId() != TTUUID::getNotExistID() && $this->getId() != TTUUID::getZeroID() && isset( $this->department_map[$this->getId()] ) ) {
			return $this->department_map[$this->getId()];
		} else {
			$lf = TTnew( 'PremiumPolicyDepartmentListFactory' ); /** @var PremiumPolicyDepartmentListFactory $lf */
			$lf->getByPremiumPolicyId( $this->getId() );
			$list = [];
			foreach ( $lf as $obj ) {
				$list[] = $obj->getDepartment();
			}

			if ( empty( $list ) == false ) {
				$this->department_map[$this->getId()] = $list;

				return $this->department_map[$this->getId()];
			}
		}

		return false;
	}

	/**
	 * @param string|string[] $ids UUID
	 * @return bool
	 */
	function setDepartment( $ids ) {
		if ( !is_array( $ids ) && $ids != '' && TTUUID::isUUID( $ids ) ) { //Sometimes awesome box may pass through a zero UUID not as an array.
			$ids = [ $ids ];
		}

		Debug::text( 'Setting IDs...', __FILE__, __LINE__, __METHOD__, 10 );
		if ( is_array( $ids ) ) {
			$tmp_ids = [];

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = TTnew( 'PremiumPolicyDepartmentListFactory' ); /** @var PremiumPolicyDepartmentListFactory $lf_a */
				$lf_a->getByPremiumPolicyId( $this->getId() );

				foreach ( $lf_a as $obj ) {
					$id = $obj->getDepartment();
					Debug::text( 'Department ID: ' . $obj->getDepartment() . ' ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

					//Delete users that are not selected.
					if ( !in_array( $id, $ids ) ) {
						Debug::text( 'Deleting: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$obj->Delete();
						$obj->postSave(); //Clear cache.
					} else {
						//Save ID's that need to be updated.
						Debug::text( 'NOT Deleting : ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$tmp_ids[] = $id;
					}
				}
				unset( $id, $obj );
			}

			//Insert new mappings.
			$lf_b = TTnew( 'DepartmentListFactory' ); /** @var DepartmentListFactory $lf_b */

			foreach ( $ids as $id ) {
				if ( isset( $ids )
						&& TTUUID::isUUID( $id ) && $id != TTUUID::getZeroID() && $id != TTUUID::getNotExistID()
						&& !in_array( $id, $tmp_ids ) ) {
					$f = TTnew( 'PremiumPolicyDepartmentFactory' ); /** @var PremiumPolicyDepartmentFactory $f */
					$f->setPremiumPolicy( $this->getId() );
					$f->setDepartment( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ( $this->Validator->isTrue( 'department',
												   $f->isValid(),
												   TTi18n::gettext( 'Selected Department is invalid' ) . ' (' . $obj->getName() . ')' ) ) {
						$f->save();
					}
				}
			}

			return true;
		}

		Debug::text( 'No IDs to set.', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return bool|int
	 */
	function getJobGroupSelectionType() {
		return $this->getGenericDataValue( 'job_group_selection_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setJobGroupSelectionType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'job_group_selection_type_id', $value );
	}

	/**
	 * @return bool
	 */
	function getJobGroup() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return false;
		}

		if ( TTUUID::isUUID( $this->getId() ) && $this->getId() != TTUUID::getZeroID() && $this->getId() != TTUUID::getNotExistID() && isset( $this->job_group_map[$this->getId()] ) ) {
			return $this->job_group_map[$this->getId()];
		} else {
			$lf = TTnew( 'PremiumPolicyJobGroupListFactory' ); /** @var PremiumPolicyJobGroupListFactory $lf */
			$lf->getByPremiumPolicyId( $this->getId() );
			$list = [];
			foreach ( $lf as $obj ) {
				$list[] = $obj->getJobGroup();
			}

			if ( empty( $list ) == false ) {
				$this->job_group_map[$this->getId()] = $list;

				return $this->job_group_map[$this->getId()];
			}
		}

		return false;
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setJobGroup( $ids ) {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return false;
		}

		if ( !is_array( $ids ) && $ids != '' && TTUUID::isUUID( $ids ) ) { //Sometimes awesome box may pass through a zero UUID not as an array.
			$ids = [ $ids ];
		}

		Debug::text( 'Setting IDs...', __FILE__, __LINE__, __METHOD__, 10 );
		if ( is_array( $ids ) ) {
			$tmp_ids = [];

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = TTnew( 'PremiumPolicyJobGroupListFactory' ); /** @var PremiumPolicyJobGroupListFactory $lf_a */
				$lf_a->getByPremiumPolicyId( $this->getId() );

				foreach ( $lf_a as $obj ) {
					$id = $obj->getJobGroup();
					Debug::text( 'Job Group ID: ' . $obj->getJobGroup() . ' ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

					//Delete users that are not selected.
					if ( !in_array( $id, $ids ) ) {
						Debug::text( 'Deleting: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$obj->Delete();
						$obj->postSave(); //Clear cache.
					} else {
						//Save ID's that need to be updated.
						Debug::text( 'NOT Deleting : ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$tmp_ids[] = $id;
					}
				}
				unset( $id, $obj );
			}

			//Insert new mappings.
			$lf_b = TTnew( 'JobGroupListFactory' ); /** @var JobGroupListFactory $lf_b */

			foreach ( $ids as $id ) {
				if ( isset( $ids )
						&& TTUUID::isUUID( $id ) && $id != TTUUID::getZeroID() && $id != TTUUID::getNotExistID()
						&& !in_array( $id, $tmp_ids ) ) {
					$f = TTnew( 'PremiumPolicyJobGroupFactory' ); /** @var PremiumPolicyJobGroupFactory $f */
					$f->setPremiumPolicy( $this->getId() );
					$f->setJobGroup( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ( $this->Validator->isTrue( 'job_group',
												   $f->isValid(),
												   TTi18n::gettext( 'Selected Job Group is invalid' ) . ' (' . $obj->getName() . ')' ) ) {
						$f->save();
					}
				}
			}

			return true;
		}

		Debug::text( 'No IDs to set.', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return bool|int
	 */
	function getJobSelectionType() {
		return $this->getGenericDataValue( 'job_selection_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setJobSelectionType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'job_selection_type_id', $value );
	}

	/**
	 * @return bool
	 */
	function getExcludeDefaultJob() {
		return $this->fromBool( $this->getGenericDataValue( 'exclude_default_job' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setExcludeDefaultJob( $value ) {
		return $this->setGenericDataValue( 'exclude_default_job', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getJob() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return false;
		}

		if ( TTUUID::isUUID( $this->getId() ) && $this->getId() != TTUUID::getZeroID() && $this->getId() != TTUUID::getNotExistID()
				&& isset( $this->job_map[$this->getId()] ) ) {
			return $this->job_map[$this->getId()];
		} else {
			$lf = TTnew( 'PremiumPolicyJobListFactory' ); /** @var PremiumPolicyJobListFactory $lf */
			$lf->getByPremiumPolicyId( $this->getId() );
			$list = [];
			foreach ( $lf as $obj ) {
				$list[] = $obj->getjob();
			}

			if ( empty( $list ) == false ) {
				$this->job_map[$this->getId()] = $list;

				return $this->job_map[$this->getId()];
			}
		}

		return false;
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setJob( $ids ) {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return false;
		}

		if ( !is_array( $ids ) && $ids != '' && TTUUID::isUUID( $ids ) ) { //Sometimes awesome box may pass through a zero UUID not as an array.
			$ids = [ $ids ];
		}

		Debug::text( 'Setting IDs...', __FILE__, __LINE__, __METHOD__, 10 );
		if ( is_array( $ids ) ) {
			$tmp_ids = [];

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = TTnew( 'PremiumPolicyJobListFactory' ); /** @var PremiumPolicyJobListFactory $lf_a */
				$lf_a->getByPremiumPolicyId( $this->getId() );

				foreach ( $lf_a as $obj ) {
					$id = $obj->getjob();
					Debug::text( 'Job ID: ' . $obj->getJob() . ' ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

					//Delete users that are not selected.
					if ( !in_array( $id, $ids ) ) {
						Debug::text( 'Deleting: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$obj->Delete();
						$obj->postSave(); //Clear cache.
					} else {
						//Save ID's that need to be updated.
						Debug::text( 'NOT Deleting : ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$tmp_ids[] = $id;
					}
				}
				unset( $id, $obj );
			}

			//Insert new mappings.
			$lf_b = TTnew( 'JobListFactory' ); /** @var JobListFactory $lf_b */

			foreach ( $ids as $id ) {
				if ( isset( $ids ) && TTUUID::isUUID( $id ) && $id != TTUUID::getZeroID() && $id != TTUUID::getNotExistID() && !in_array( $id, $tmp_ids ) ) {
					$f = TTnew( 'PremiumPolicyJobFactory' ); /** @var PremiumPolicyJobFactory $f */
					$f->setPremiumPolicy( $this->getId() );
					$f->setJob( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ( $this->Validator->isTrue( 'job',
												   $f->isValid(),
												   TTi18n::gettext( 'Selected Job is invalid' ) . ' (' . $obj->getName() . ')' ) ) {
						$f->save();
					}
				}
			}

			return true;
		}

		Debug::text( 'No IDs to set.', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return bool|int
	 */
	function getJobItemGroupSelectionType() {
		return $this->getGenericDataValue( 'job_item_group_selection_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setJobItemGroupSelectionType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'job_item_group_selection_type_id', $value );
	}

	/**
	 * @return bool
	 */
	function getExcludeDefaultJobItem() {
		return $this->fromBool( $this->getGenericDataValue( 'exclude_default_job_item' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setExcludeDefaultJobItem( $value ) {
		return $this->setGenericDataValue( 'exclude_default_job_item', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getJobItemGroup() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return false;
		}

		if ( TTUUID::isUUID( $this->getId() ) && $this->getId() != TTUUID::getZeroID() && $this->getId() != TTUUID::getNotExistID() && isset( $this->job_item_group_map[$this->getId()] ) ) {
			return $this->job_item_group_map[$this->getId()];
		} else {
			$lf = TTnew( 'PremiumPolicyJobItemGroupListFactory' ); /** @var PremiumPolicyJobItemGroupListFactory $lf */
			$lf->getByPremiumPolicyId( $this->getId() );
			$list = [];
			foreach ( $lf as $obj ) {
				$list[] = $obj->getJobItemGroup();
			}

			if ( empty( $list ) == false ) {
				$this->job_item_group_map[$this->getId()] = $list;

				return $this->job_item_group_map[$this->getId()];
			}
		}

		return false;
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setJobItemGroup( $ids ) {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return false;
		}

		if ( !is_array( $ids ) && $ids != '' && TTUUID::isUUID( $ids ) ) { //Sometimes awesome box may pass through a zero UUID not as an array.
			$ids = [ $ids ];
		}

		Debug::text( 'Setting IDs...', __FILE__, __LINE__, __METHOD__, 10 );
		if ( is_array( $ids ) ) {
			$tmp_ids = [];

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = TTnew( 'PremiumPolicyJobItemGroupListFactory' ); /** @var PremiumPolicyJobItemGroupListFactory $lf_a */
				$lf_a->getByPremiumPolicyId( $this->getId() );

				foreach ( $lf_a as $obj ) {
					$id = $obj->getJobItemGroup();
					Debug::text( 'Job Item Group ID: ' . $obj->getJobItemGroup() . ' ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

					//Delete users that are not selected.
					if ( !in_array( $id, $ids ) ) {
						Debug::text( 'Deleting: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$obj->Delete();
						$obj->postSave(); //Clear cache.
					} else {
						//Save ID's that need to be updated.
						Debug::text( 'NOT Deleting : ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$tmp_ids[] = $id;
					}
				}
				unset( $id, $obj );
			}

			//Insert new mappings.
			$lf_b = TTnew( 'JobItemGroupListFactory' ); /** @var JobItemGroupListFactory $lf_b */

			foreach ( $ids as $id ) {
				if ( isset( $ids ) && TTUUID::isUUID( $id ) && $id != TTUUID::getZeroID() && $id != TTUUID::getNotExistID() && !in_array( $id, $tmp_ids ) ) {
					$f = TTnew( 'PremiumPolicyJobItemGroupFactory' ); /** @var PremiumPolicyJobItemGroupFactory $f */
					$f->setPremiumPolicy( $this->getId() );
					$f->setJobItemGroup( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ( $this->Validator->isTrue( 'job_item_group',
												   $f->isValid(),
												   TTi18n::gettext( 'Selected Task Group is invalid' ) . ' (' . $obj->getName() . ')' ) ) {
						$f->save();
					}
				}
			}

			return true;
		}

		Debug::text( 'No IDs to set.', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return bool|int
	 */
	function getJobItemSelectionType() {
		return $this->getGenericDataValue( 'job_item_selection_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setJobItemSelectionType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'job_item_selection_type_id', $value );
	}

	/**
	 * @return bool
	 */
	function getJobItem() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return false;
		}

		if ( TTUUID::isUUID( $this->getId() ) && $this->getId() != TTUUID::getZeroID() && $this->getId() != TTUUID::getNotExistID() && isset( $this->job_item_map[$this->getId()] ) ) {
			return $this->job_item_map[$this->getId()];
		} else {
			$lf = TTnew( 'PremiumPolicyJobItemListFactory' ); /** @var PremiumPolicyJobItemListFactory $lf */
			$lf->getByPremiumPolicyId( $this->getId() );
			$list = [];
			foreach ( $lf as $obj ) {
				$list[] = $obj->getJobItem();
			}

			if ( empty( $list ) == false ) {
				$this->job_item_map[$this->getId()] = $list;

				return $this->job_item_map[$this->getId()];
			}
		}

		return false;
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setJobItem( $ids ) {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return false;
		}

		if ( !is_array( $ids ) && $ids != '' && TTUUID::isUUID( $ids ) ) { //Sometimes awesome box may pass through a zero UUID not as an array.
			$ids = [ $ids ];
		}

		Debug::text( 'Setting IDs...', __FILE__, __LINE__, __METHOD__, 10 );
		if ( is_array( $ids ) ) {
			$tmp_ids = [];

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = TTnew( 'PremiumPolicyJobItemListFactory' ); /** @var PremiumPolicyJobItemListFactory $lf_a */
				$lf_a->getByPremiumPolicyId( $this->getId() );

				foreach ( $lf_a as $obj ) {
					$id = $obj->getJobItem();
					Debug::text( 'Job Item ID: ' . $obj->getJobItem() . ' ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

					//Delete users that are not selected.
					if ( !in_array( $id, $ids ) ) {
						Debug::text( 'Deleting: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$obj->Delete();
						$obj->postSave(); //Clear cache.
					} else {
						//Save ID's that need to be updated.
						Debug::text( 'NOT Deleting : ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$tmp_ids[] = $id;
					}
				}
				unset( $id, $obj );
			}

			//Insert new mappings.
			$lf_b = TTnew( 'JobItemListFactory' ); /** @var JobItemListFactory $lf_b */

			foreach ( $ids as $id ) {
				if ( isset( $ids ) && TTUUID::isUUID( $id ) && $id != TTUUID::getZeroID() && $id != TTUUID::getNotExistID() && !in_array( $id, $tmp_ids ) ) {
					$f = TTnew( 'PremiumPolicyJobItemFactory' ); /** @var PremiumPolicyJobItemFactory $f */
					$f->setPremiumPolicy( $this->getId() );
					$f->setJobItem( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ( $this->Validator->isTrue( 'job',
												   $f->isValid(),
												   TTi18n::gettext( 'Selected JobItem is invalid' ) . ' (' . $obj->getName() . ')' ) ) {
						$f->save();
					}
				}
			}

			return true;
		}

		Debug::text( 'No IDs to set.', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param int $in_epoch  EPOCH
	 * @param int $out_epoch EPOCH
	 * @param object $calculate_policy_obj
	 * @return bool
	 */
	function isActive( $in_epoch, $out_epoch = null, $calculate_policy_obj = null ) {
		if ( $out_epoch == '' ) {
			$out_epoch = $in_epoch;
		}

		//Debug::text(' In: '. TTDate::getDate('DATE+TIME', $in_epoch) .' Out: '. TTDate::getDate('DATE+TIME', $out_epoch), __FILE__, __LINE__, __METHOD__, 10);
		$i = $in_epoch;
		$last_iteration = 0;
		//Make sure we loop on the in_epoch, out_epoch and every day inbetween. $last_iteration allows us to always hit the out_epoch.
		while ( $i <= $out_epoch && $last_iteration <= 1 ) {
			//Debug::text(' I: '. TTDate::getDate('DATE+TIME', $i), __FILE__, __LINE__, __METHOD__, 10);
			if ( $this->getIncludeHolidayType() > 10 && is_object( $calculate_policy_obj ) ) {
				//$is_holiday = $this->isHoliday( $i, $user_id );
				$is_holiday = ( count( $calculate_policy_obj->filterHoliday( $i ) ) > 0 ) ? true : false;
			} else {
				$is_holiday = false;
			}

			if ( ( $this->getIncludeHolidayType() == 10 && $this->isActiveDate( $i ) == true && $this->isActiveDayOfWeek( $i ) == true )
					|| ( $this->getIncludeHolidayType() == 20 && ( ( $this->isActiveDate( $i ) == true && $this->isActiveDayOfWeek( $i ) == true ) || $is_holiday == true ) )
					|| ( $this->getIncludeHolidayType() == 30 && ( ( $this->isActiveDate( $i ) == true && $this->isActiveDayOfWeek( $i ) == true ) && $is_holiday == false ) )
			) {
				Debug::text( 'Active Date/DayOfWeek: ' . TTDate::getDate( 'DATE+TIME', $i ), __FILE__, __LINE__, __METHOD__, 10 );

				return true;
			}

			//If there is more than one day between $i and $out_epoch, add one day to $i.
			if ( $i < ( $out_epoch - 86400 ) ) {
				$i += 86400;
			} else {
				//When less than one day untl $out_epoch, skip to $out_epoch and loop once more.
				$i = $out_epoch;
				$last_iteration++;
			}
		}

		Debug::text( 'NOT Active Date/DayOfWeek: ' . TTDate::getDate( 'DATE+TIME', $i ), __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * Check if this premium policy is restricted by time.
	 * If its not, we can apply it to non-punched hours.
	 * @return bool
	 */
	function isTimeRestricted() {
		//If time restrictions account for over 23.5 hours, then we assume
		//that this policy is not time restricted at all.
		$time_diff = abs( $this->getEndTime() - $this->getStartTime() );
		if ( $time_diff > 0 && $time_diff < ( 23.5 * 3600 ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function isHourRestricted() {
		if ( $this->getDailyTriggerTime() > 0 || $this->getWeeklyTriggerTime() > 0 || $this->getMaximumDailyTriggerTime() > 0 || $this->getMaximumWeeklyTriggerTime() > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function isDayOfWeekRestricted() {
		if ( $this->getSun() == false || $this->getMon() == false || $this->getTue() == false || $this->getWed() == false || $this->getThu() == false || $this->getFri() == false || $this->getSat() == false ) {
			return true;
		}

		return false;
	}

	/**
	 * @param int $in_epoch  EPOCH
	 * @param int $out_epoch EPOCH
	 * @param $total_time
	 * @param object $calculate_policy_obj
	 * @return bool|int|mixed
	 */
	function getPartialPunchTotalTime( $in_epoch, $out_epoch, $total_time, $calculate_policy_obj = null ) {
		$retval = $total_time;

		//If a premium policy only activates on say Sat, but the Start/End times are blank/0,
		//it won't calculate just the time on Sat if an employee works from Fri 8:00PM to Sat 3:00AM.
		//So check for StartTime/EndTime > 0 OR isDayOfWeekRestricted()
		//Then if no StartTime/EndTime is set, force it to cover the entire 24hr period.
		if ( $this->isActiveTime( $in_epoch, $out_epoch, $calculate_policy_obj )
				&& $this->getIncludePartialPunch() == true
				&& (
						( $this->getStartTime() > 0 || $this->getEndTime() > 0 )
						|| $this->isDayOfWeekRestricted() == true
				)
		) {
			if ( $this->getStartTime() == '' ) {
				$this->setStartTime( strtotime( '12:00 AM' ) );
			}
			if ( $this->getEndTime() == '' ) {
				$this->setEndTime( strtotime( '11:59 PM' ) );
			}

			Debug::text( ' Checking for Active Time with: In: ' . TTDate::getDate( 'DATE+TIME', $in_epoch ) . ' Out: ' . TTDate::getDate( 'DATE+TIME', $out_epoch ), __FILE__, __LINE__, __METHOD__, 10 );

			Debug::text( ' Raw Start TimeStamp(' . $this->getStartTime( true ) . '): ' . TTDate::getDate( 'DATE+TIME', $this->getStartTime() ) . ' Raw End TimeStamp(' . $this->getEndTime( true ) . '): ' . TTDate::getDate( 'DATE+TIME', $this->getEndTime() ), __FILE__, __LINE__, __METHOD__, 10 );
			$start_time_stamp = TTDate::getTimeLockedDate( $this->getStartTime(), $in_epoch );
			$end_time_stamp = TTDate::getTimeLockedDate( $this->getEndTime(), $in_epoch );

			//Check if end timestamp is before start, if it is, move end timestamp to next day.
			if ( $end_time_stamp < $start_time_stamp ) {
				Debug::text( ' Moving End TimeStamp to next day.', __FILE__, __LINE__, __METHOD__, 10 );
				$end_time_stamp = TTDate::getTimeLockedDate( $this->getEndTime(), ( TTDate::getMiddleDayEpoch( $end_time_stamp ) + 86400 ) ); //Due to DST, jump ahead 1.5 days, then jump back to the time locked date.
			}

			//Handle the last second of the day, so punches that span midnight like 11:00PM to 6:00AM get a full 1 hour for the time before midnight, rather than 59mins and 59secs.
			if ( TTDate::getHour( $end_time_stamp ) == 23 && TTDate::getMinute( $end_time_stamp ) == 59 ) {
				$end_time_stamp = ( TTDate::getEndDayEpoch( $end_time_stamp ) + 1 );
				Debug::text( ' End time stamp is within the last minute of day, make sure we include the last second of the day as well.', __FILE__, __LINE__, __METHOD__, 10 );
			}

			$retval = 0;
			foreach ( TTDate::getDatePeriod( TTDate::incrementDate( $start_time_stamp, -1, 'day' ), TTDate::incrementDate( $end_time_stamp, 1, 'day' ), 'P1D' ) as $i ) {
				//Due to DST, we need to make sure we always lock time of day so its the exact same. Without this it can walk by one hour either way.
				//Similar $tmp_end_time_stamp handling code is also in isActiveTime() and getPartialPunchTotalTime()
				$tmp_start_time_stamp = TTDate::getTimeLockedDate( $this->getStartTime(), $i );
				$tmp_end_time_stamp = TTDate::getTimeLockedDate( $end_time_stamp, $i );
				if ( $tmp_end_time_stamp <= $tmp_start_time_stamp ) {
					$tmp_end_time_stamp = TTDate::getTimeLockedDate( $end_time_stamp, TTDate::incrementDate( $tmp_end_time_stamp, 1, 'day' ) ); //Due to DST, jump ahead 1.5 days, then jump back to the time locked date.
				}

				if ( $this->isActiveTime( $tmp_start_time_stamp, $tmp_end_time_stamp, $calculate_policy_obj ) == true ) {
					$retval += TTDate::getTimeOverLapDifference( $tmp_start_time_stamp, $tmp_end_time_stamp, $in_epoch, $out_epoch );
					Debug::text( ' Calculating partial time against Start TimeStamp: ' . TTDate::getDate( 'DATE+TIME', $tmp_start_time_stamp ) . ' End TimeStamp: ' . TTDate::getDate( 'DATE+TIME', $tmp_end_time_stamp ) . ' Total: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );
				} else {
					Debug::text( ' Not Active on this day: ' . TTDate::getDate( 'DATE+TIME', $i ), __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
		} else {
			Debug::text( '   Not calculating partial punch, just using total time...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		Debug::text( ' Partial Punch Total Time: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	/**
	 * Check if this time is within the start/end time.
	 * @param int $in_epoch  EPOCH
	 * @param int $out_epoch EPOCH
	 * @param object $calculate_policy_obj
	 * @return bool
	 */
	function isActiveTime( $in_epoch, $out_epoch, $calculate_policy_obj = null ) {
		Debug::text( ' Checking for Active Time with: In: ' . TTDate::getDate( 'DATE+TIME', $in_epoch ) . ' Out: ' . TTDate::getDate( 'DATE+TIME', $out_epoch ), __FILE__, __LINE__, __METHOD__, 10 );

		Debug::text( ' PP Raw Start TimeStamp(' . $this->getStartTime( true ) . '): ' . TTDate::getDate( 'DATE+TIME', $this->getStartTime() ) . ' Raw End TimeStamp: ' . TTDate::getDate( 'DATE+TIME', $this->getEndTime() ), __FILE__, __LINE__, __METHOD__, 10 );
		$start_time_stamp = TTDate::getTimeLockedDate( $this->getStartTime(), $in_epoch ); //Base the end time on day of the in_epoch.
		$end_time_stamp = TTDate::getTimeLockedDate( $this->getEndTime(), $in_epoch );     //Base the end time on day of the in_epoch.

		//Check if end timestamp is before start, if it is, move end timestamp to next day.
		if ( $end_time_stamp < $start_time_stamp ) {
			Debug::text( ' Moving End TimeStamp to next day.', __FILE__, __LINE__, __METHOD__, 10 );
			$end_time_stamp = TTDate::getTimeLockedDate( $this->getEndTime(), ( TTDate::getMiddleDayEpoch( $end_time_stamp ) + 86400 ) ); //Due to DST, jump ahead 1.5 days, then jump back to the time locked date.
		}

		Debug::text( ' Start TimeStamp: ' . TTDate::getDate( 'DATE+TIME', $start_time_stamp ) . ' End TimeStamp: ' . TTDate::getDate( 'DATE+TIME', $end_time_stamp ), __FILE__, __LINE__, __METHOD__, 10 );
		//Check to see if start/end time stamps are not set or are equal, we always return TRUE if they are.
		if ( $this->getIncludeHolidayType() == 10
				&& ( $start_time_stamp == '' || $end_time_stamp == '' || $start_time_stamp == $end_time_stamp ) ) {
			Debug::text( ' Start/End time not set, assume it always matches.', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			//If the premium policy start/end time spans midnight, there could be multiple windows to check
			//where the premium policy applies, make sure we check all windows.
			//for( $i = (TTDate::getMiddleDayEpoch($start_time_stamp) - 86400); $i <= (TTDate::getMiddleDayEpoch($end_time_stamp) + 86400); $i += 86400 ) {
			foreach ( TTDate::getDatePeriod( TTDate::incrementDate( $start_time_stamp, -1, 'day' ), TTDate::incrementDate( $end_time_stamp, 1, 'day' ), 'P1D' ) as $i ) {
				//Due to DST, we need to make sure we always lock time of day so its the exact same. Without this it can walk by one hour either way.
				//Similar $tmp_end_time_stamp handling code is also in isActiveTime() and getPartialPunchTotalTime()
				$tmp_start_time_stamp = TTDate::getTimeLockedDate( $this->getStartTime(), $i );
				$tmp_end_time_stamp = TTDate::getTimeLockedDate( $end_time_stamp, $i );
				if ( $tmp_end_time_stamp <= $tmp_start_time_stamp ) {
					$tmp_end_time_stamp = TTDate::getTimeLockedDate( $end_time_stamp, TTDate::incrementDate( $tmp_end_time_stamp, 1, 'day' ) ); //Due to DST, jump ahead 1.5 days, then jump back to the time locked date.
				}

				if ( $this->isActive( $tmp_start_time_stamp, $tmp_end_time_stamp, $calculate_policy_obj ) == true ) {
					Debug::text( ' Checking against Start TimeStamp: ' . TTDate::getDate( 'DATE+TIME', $tmp_start_time_stamp ) . '(' . $tmp_start_time_stamp . ') End TimeStamp: ' . TTDate::getDate( 'DATE+TIME', $tmp_end_time_stamp ) . '(' . $tmp_end_time_stamp . ')', __FILE__, __LINE__, __METHOD__, 10 );
					if ( $this->getIncludePartialPunch() == true && TTDate::isTimeOverLap( $in_epoch, $out_epoch, $tmp_start_time_stamp, $tmp_end_time_stamp ) == true ) {
						//When dealing with partial punches, any overlap whatsoever activates the policy.
						Debug::text( ' Partial Punch Within Active Time!', __FILE__, __LINE__, __METHOD__, 10 );

						return true;
					} else if ( $in_epoch >= $tmp_start_time_stamp && $out_epoch <= $tmp_end_time_stamp ) {
						//Non partial punches, they must punch in AND out (entire shift) within the time window.
						Debug::text( ' Within Active Time!', __FILE__, __LINE__, __METHOD__, 10 );

						return true;
					} else if ( ( $start_time_stamp == '' || $end_time_stamp == '' || $start_time_stamp == $end_time_stamp ) ) { //Must go AFTER the above IF statements.
						//When IncludeHolidayType != 10 this trigger here.
						Debug::text( ' No Start/End Date/Time!', __FILE__, __LINE__, __METHOD__, 10 );

						return true;
					} else {
						Debug::text( ' No match...', __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::text( ' Not Active on this day: Start: ' . TTDate::getDate( 'DATE+TIME', $tmp_start_time_stamp ) . ' End: ' . TTDate::getDate( 'DATE+TIME', $tmp_end_time_stamp ), __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
		}

		Debug::text( ' NOT Within Active Time!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}
	/*
		function isHoliday( $epoch, $user_id ) {
			if ( $epoch == '' OR $user_id == '' ) {
				return FALSE;
			}

			$hlf = TTnew( 'HolidayListFactory' );
			$hlf->getByPolicyGroupUserIdAndDate( $user_id, $epoch );
			if ( $hlf->getRecordCount() > 0 ) {
				$holiday_obj = $hlf->getCurrent();
				Debug::text(' Found Holiday: '. $holiday_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);

				if ( $holiday_obj->getHolidayPolicyObject()->getForceOverTimePolicy() == TRUE
						OR $holiday_obj->isEligible( $user_id ) ) {
					Debug::text(' Is Eligible for Holiday: '. $holiday_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);

					return TRUE;
				} else {
					Debug::text(' Not Eligible for Holiday: '. $holiday_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);
					return FALSE; //Skip to next policy
				}
			} else {
				Debug::text(' Not Holiday: User ID: '. $user_id .' Date: '. TTDate::getDate('DATE', $epoch), __FILE__, __LINE__, __METHOD__, 10);
				return FALSE; //Skip to next policy
			}
			unset($hlf, $holiday_obj);

			return FALSE;
		}
	*/

	/**
	 * Check if this date is within the effective date range
	 * Need to take into account shifts that span midnight too.
	 * @param int $epoch EPOCH
	 * @param int $maximum_shift_time
	 * @return bool
	 */
	function isActiveDate( $epoch, $maximum_shift_time = 0 ) {
		//Debug::text(' Checking for Active Date: '. TTDate::getDate('DATE+TIME', $epoch) .' PP Start Date: '. TTDate::getDate('DATE+TIME', $this->getStartDate()) .' Maximum Shift Time: '. $maximum_shift_time, __FILE__, __LINE__, __METHOD__, 10);
		$epoch = TTDate::getBeginDayEpoch( $epoch );

		if ( $this->getStartDate() == '' && $this->getEndDate() == '' ) {
			return true;
		}

		if ( $epoch >= ( TTDate::getBeginDayEpoch( (int)$this->getStartDate() ) - (int)$maximum_shift_time )
				&& ( $epoch <= ( TTDate::getEndDayEpoch( (int)$this->getEndDate() ) + (int)$maximum_shift_time ) || $this->getEndDate() == '' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if this day of the week is active
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function isActiveDayOfWeek( $epoch ) {
		//Debug::text(' Checking for Active Day of Week.', __FILE__, __LINE__, __METHOD__, 10);
		$day_of_week = strtolower( date( 'D', $epoch ) );

		switch ( $day_of_week ) {
			case 'sun':
				if ( $this->getSun() == true ) {
					return true;
				}
				break;
			case 'mon':
				if ( $this->getMon() == true ) {
					return true;
				}
				break;
			case 'tue':
				if ( $this->getTue() == true ) {
					return true;
				}
				break;
			case 'wed':
				if ( $this->getWed() == true ) {
					return true;
				}
				break;
			case 'thu':
				if ( $this->getThu() == true ) {
					return true;
				}
				break;
			case 'fri':
				if ( $this->getFri() == true ) {
					return true;
				}
				break;
			case 'sat':
				if ( $this->getSat() == true ) {
					return true;
				}
				break;
		}

		return false;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Company
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$this->Validator->isResultSetWithRows( 'company',
											   $clf->getByID( $this->getCompany() ),
											   TTi18n::gettext( 'Company is invalid' )
		);
		// Type
		if ( $this->getType() !== false ) {
			$this->Validator->inArrayKey( 'type',
										  $this->getType(),
										  TTi18n::gettext( 'Incorrect Type' ),
										  $this->getOptions( 'type' )
			);
		}
		// Name
		if ( $this->Validator->getValidateOnly() == false ) {
			if ( $this->getName() == '' ) {
				$this->Validator->isTRUE( 'name',
										  false,
										  TTi18n::gettext( 'Please specify a name' ) );
			}
		}
		if ( $this->getName() != '' && $this->Validator->isError( 'name' ) == false ) {
			$this->Validator->isLength( 'name',
										$this->getName(),
										TTi18n::gettext( 'Name is too short or too long' ),
										2, 50
			);
		}
		if ( $this->getName() != '' && $this->Validator->isError( 'name' ) == false ) {
			$this->Validator->isTrue( 'name',
									  $this->isUniqueName( $this->getName() ),
									  TTi18n::gettext( 'Name is already in use' )
			);
		}
		// Description
		if ( $this->getDescription() != '' ) {
			$this->Validator->isLength( 'description',
										$this->getDescription(),
										TTi18n::gettext( 'Description is invalid' ),
										1, 250
			);
		}
		// Contributing Shift Policy
		if ( $this->getContributingShiftPolicy() !== false ) {
			$csplf = TTnew( 'ContributingShiftPolicyListFactory' ); /** @var ContributingShiftPolicyListFactory $csplf */
			$this->Validator->isResultSetWithRows( 'contributing_shift_policy_id',
												   $csplf->getByID( $this->getContributingShiftPolicy() ),
												   TTi18n::gettext( 'Contributing Shift Policy is invalid' )
			);
		}
		// Pay Type
		if ( $this->getPayType() !== false ) {
			$this->Validator->inArrayKey( 'pay_type_id',
										  $this->getPayType(),
										  TTi18n::gettext( 'Incorrect Pay Type' ),
										  $this->getOptions( 'pay_type' )
			);
		}
		// Start date
		if ( $this->getStartDate() != '' ) {
			$this->Validator->isDate( 'start_date',
									  $this->getStartDate(),
									  TTi18n::gettext( 'Incorrect start date' )
			);
		}
		// End date
		if ( $this->getEndDate() != '' ) {
			$this->Validator->isDate( 'end_date',
									  $this->getEndDate(),
									  TTi18n::gettext( 'Incorrect end date' )
			);
		}
		// Start time
		if ( $this->getStartTime() != '' ) {
			$this->Validator->isDate( 'start_time',
									  $this->getStartTime(),
									  TTi18n::gettext( 'Incorrect Start time' )
			);
		}
		// End time
		if ( $this->getEndTime() != '' ) {
			$this->Validator->isDate( 'end_time',
									  $this->getEndTime(),
									  TTi18n::gettext( 'Incorrect End time' )
			);
		}
		// Daily trigger time
		if ( $this->getDailyTriggerTime() !== false ) {
			$this->Validator->isNumeric( 'daily_trigger_time',
										 $this->getDailyTriggerTime(),
										 TTi18n::gettext( 'Incorrect daily trigger time' )
			);
		}
		// Weekly trigger time
		if ( $this->getWeeklyTriggerTime() !== false ) {
			$this->Validator->isNumeric( 'weekly_trigger_time',
										 $this->getWeeklyTriggerTime(),
										 TTi18n::gettext( 'Incorrect weekly trigger time' )
			);
		}
		// Maximum daily trigger time
		if ( $this->getMaximumDailyTriggerTime() !== false ) {
			$this->Validator->isNumeric( 'daily_trigger_time',
										 $this->getMaximumDailyTriggerTime(),
										 TTi18n::gettext( 'Incorrect maximum daily trigger time' )
			);
		}
		// Maximum weekly trigger time
		if ( $this->getMaximumWeeklyTriggerTime() !== false ) {
			$this->Validator->isNumeric( 'weekly_trigger_time',
										 $this->getMaximumWeeklyTriggerTime(),
										 TTi18n::gettext( 'Incorrect maximum weekly trigger time' )
			);
		}
		// Maximum Time Without Break
		if ( $this->getMaximumNoBreakTime() != '' ) {
			$this->Validator->isNumeric( 'maximum_no_break_time',
										 $this->getMaximumNoBreakTime(),
										 TTi18n::gettext( 'Incorrect Maximum Time Without Break' )
			);
		}
		// Minimum Break Time
		if ( $this->getMinimumBreakTime() != '' ) {
			$this->Validator->isNumeric( 'minimum_break_time',
										 $this->getMinimumBreakTime(),
										 TTi18n::gettext( 'Incorrect Minimum Break Time' )
			);
		}
		// Minimum Time Between Shifts
		if ( $this->getMinimumTimeBetweenShift() != '' ) {
			$this->Validator->isNumeric( 'minimum_time_between_shift',
										 $this->getMinimumTimeBetweenShift(),
										 TTi18n::gettext( 'Incorrect Minimum Time Between Shifts' )
			);
		}
		// Minimum First Shift Time
		if ( $this->getMinimumFirstShiftTime() != '' ) {
			$this->Validator->isNumeric( 'minimum_first_shift_time',
										 $this->getMinimumFirstShiftTime(),
										 TTi18n::gettext( 'Incorrect Minimum First Shift Time' )
			);
		}
		// Minimum Shift Time
		if ( $this->getMinimumShiftTime() != '' ) {
			$this->Validator->isNumeric( 'minimum_shift_time',
										 $this->getMinimumShiftTime(),
										 TTi18n::gettext( 'Incorrect Minimum Shift Time' )
			);
		}
		// Minimum Time
		if ( $this->getMinimumTime() !== false ) {
			$this->Validator->isNumeric( 'minimum_time',
										 $this->getMinimumTime(),
										 TTi18n::gettext( 'Incorrect Minimum Time' )
			);
		}
		// Maximum Time
		if ( $this->getMaximumTime() !== false ) {
			$this->Validator->isNumeric( 'maximum_time',
										 $this->getMaximumTime(),
										 TTi18n::gettext( 'Incorrect Maximum Time' )
			);
		}
		// Min/Max Time Type
		if ( $this->getType() !== false ) {
			$this->Validator->inArrayKey( 'min_max_time_type_id',
										  $this->getMinMaxTimeType(),
										  TTi18n::gettext( 'Incorrect Minimum/Maximum Reset' ),
										  $this->getOptions( 'min_max_time_type' )
			);
		}
		// Include Holiday Type
		if ( $this->getIncludeHolidayType() !== false ) {
			$this->Validator->inArrayKey( 'include_holiday_type',
										  $this->getIncludeHolidayType(),
										  TTi18n::gettext( 'Incorrect Include Holiday Type' ),
										  $this->getOptions( 'include_holiday_type' )
			);
		}
		// Pay Code
		if ( $this->getPayCode() !== false && $this->getPayCode() != TTUUID::getZeroID() ) {
			$pclf = TTnew( 'PayCodeListFactory' ); /** @var PayCodeListFactory $pclf */
			$this->Validator->isResultSetWithRows( 'pay_code_id',
												   $pclf->getById( $this->getPayCode() ),
												   TTi18n::gettext( 'Invalid Pay Code' )
			);
		}
		// Pay Formula Policy
		if ( $this->getPayFormulaPolicy() !== false && $this->getPayFormulaPolicy() != TTUUID::getZeroID() ) {
			$pfplf = TTnew( 'PayFormulaPolicyListFactory' ); /** @var PayFormulaPolicyListFactory $pfplf */
			$this->Validator->isResultSetWithRows( 'pay_formula_policy_id',
												   $pfplf->getByID( $this->getPayFormulaPolicy() ),
												   TTi18n::gettext( 'Pay Formula Policy is invalid' )
			);
		}
		// Branch Selection Type
		if ( $this->getBranchSelectionType() != '' ) {
			$this->Validator->inArrayKey( 'branch_selection_type',
										  $this->getBranchSelectionType(),
										  TTi18n::gettext( 'Incorrect Branch Selection Type' ),
										  $this->getOptions( 'branch_selection_type' )
			);
		}
		// Department Selection Type
		if ( $this->getDepartmentSelectionType() != '' ) {
			$this->Validator->inArrayKey( 'department_selection_type',
										  $this->getDepartmentSelectionType(),
										  TTi18n::gettext( 'Incorrect Department Selection Type' ),
										  $this->getOptions( 'department_selection_type' )
			);
		}
		// Job Group Selection Type
		if ( $this->getJobGroupSelectionType() != '' ) {
			$this->Validator->inArrayKey( 'job_group_selection_type',
										  $this->getJobGroupSelectionType(),
										  TTi18n::gettext( 'Incorrect Job Group Selection Type' ),
										  $this->getOptions( 'job_group_selection_type' )
			);
		}
		// Job Selection Type
		if ( $this->getJobSelectionType() != '' ) {
			$this->Validator->inArrayKey( 'job_selection_type',
										  $this->getJobSelectionType(),
										  TTi18n::gettext( 'Incorrect Job Selection Type' ),
										  $this->getOptions( 'job_selection_type' )
			);
		}
		// Task Group Selection Type
		if ( $this->getJobItemGroupSelectionType() != '' ) {
			$this->Validator->inArrayKey( 'job_item_group_selection_type',
										  $this->getJobItemGroupSelectionType(),
										  TTi18n::gettext( 'Incorrect Task Group Selection Type' ),
										  $this->getOptions( 'job_item_group_selection_type' )
			);
		}
		// Task Selection Type
		if ( $this->getJobItemSelectionType() != '' ) {
			$this->Validator->inArrayKey( 'job_item_selection_type',
										  $this->getJobItemSelectionType(),
										  TTi18n::gettext( 'Incorrect Task Selection Type' ),
										  $this->getOptions( 'job_item_selection_type' )
			);
		}
		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->getDeleted() != true && $this->Validator->getValidateOnly() == false ) { //Don't check the below when mass editing.

			if ( $this->getPayCode() == TTUUID::getZeroID() ) {
				$this->Validator->isTRUE( 'pay_code_id',
										  false,
										  TTi18n::gettext( 'Please choose a Pay Code' ) );
			}

			//Make sure Pay Formula Policy is defined somewhere.
			if ( $this->getPayFormulaPolicy() == TTUUID::getZeroID() && ( TTUUID::isUUID( $this->getPayCode() ) && $this->getPayCode() != TTUUID::getZeroID() && $this->getPayCode() != TTUUID::getNotExistID() ) && ( !is_object( $this->getPayCodeObject() ) || ( is_object( $this->getPayCodeObject() ) && $this->getPayCodeObject()->getPayFormulaPolicy() == TTUUID::getZeroID() ) ) ) {
				$this->Validator->isTRUE( 'pay_formula_policy_id',
										  false,
										  TTi18n::gettext( 'Selected Pay Code does not have a Pay Formula Policy defined' ) );
			}
		}

		if ( $this->getDeleted() == true ) {
			//Check to make sure nothing else references this policy, so we can be sure its okay to delete it.
			$pglf = TTnew( 'PolicyGroupListFactory' ); /** @var PolicyGroupListFactory $pglf */
			$pglf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), [ 'premium_policy' => $this->getId() ], 1 );
			if ( $pglf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  false,
										  TTi18n::gettext( 'This policy is currently in use' ) . ' ' . TTi18n::gettext( 'by policy groups' ) );
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preValidate() {
		if ( $this->getBranchSelectionType() === false || $this->getBranchSelectionType() < 10 ) {
			$this->setBranchSelectionType( 10 ); //All
		}
		if ( $this->getDepartmentSelectionType() === false || $this->getDepartmentSelectionType() < 10 ) {
			$this->setDepartmentSelectionType( 10 ); //All
		}
		if ( $this->getJobGroupSelectionType() === false || $this->getJobGroupSelectionType() < 10 ) {
			$this->setJobGroupSelectionType( 10 ); //All
		}
		if ( $this->getJobSelectionType() === false || $this->getJobSelectionType() < 10 ) {
			$this->setJobSelectionType( 10 ); //All
		}
		if ( $this->getJobItemGroupSelectionType() === false || $this->getJobItemGroupSelectionType() < 10 ) {
			$this->setJobItemGroupSelectionType( 10 ); //All
		}
		if ( $this->getJobItemSelectionType() === false || $this->getJobItemSelectionType() < 10 ) {
			$this->setJobItemSelectionType( 10 ); //All
		}

		if ( $this->getPayType() === false ) {
			$this->setPayType( 10 );
		}

		if ( $this->getMinMaxTimeType() === false ) {
			$this->setMinMaxTimeType( 10 ); //10=Per Day
		}

		$this->data['rate'] = 0; //This is required until the schema removes the NOT NULL constraint.

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );

		return true;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach ( $variable_function_map as $key => $function ) {
				if ( isset( $data[$key] ) ) {

					$function = 'set' . $function;
					switch ( $key ) {
						case 'start_date':
						case 'end_date':
						case 'start_time':
						case 'end_time':
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

			return true;
		}

		return false;
	}

	/**
	 * @param null $include_columns
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null ) {
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'in_use':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'type':
						case 'min_max_time_type':
						case 'pay_type':
						case 'branch_selection_type':
						case 'department_selection_type':
						case 'job_group_selection_type':
						case 'job_selection_type':
						case 'job_item_group_selection_type':
						case 'job_item_selection_type':
							$function = 'get' . str_replace( '_', '', $variable );
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'start_date':
						case 'end_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
							}
							break;
						case 'start_time':
						case 'end_time':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'TIME', $this->$function() );
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Premium Policy' ), null, $this->getTable(), $this );
	}
}

?>
