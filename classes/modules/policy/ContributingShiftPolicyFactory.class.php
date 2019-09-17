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
 * @package Modules\Policy
 */
class ContributingShiftPolicyFactory extends Factory {
	protected $table = 'contributing_shift_policy';
	protected $pk_sequence_name = 'contributing_shift_policy_id_seq'; //PK Sequence name

	protected $company_obj = NULL;
	protected $contributing_time_policy_obj = NULL;
	protected $branch_map = NULL;
	protected $department_map = NULL;
	protected $job_group_map = NULL;
	protected $job_map = NULL;
	protected $job_item_group_map = NULL;
	protected $job_item_map = NULL;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = NULL ) {
		$retval = NULL;
		switch( $name ) {
			case 'include_schedule_shift_type':
				$retval = array(
										10 => TTi18n::gettext('Schedules have no effect'),
										20 => TTi18n::gettext('Only Scheduled Shifts'),
										30 => TTi18n::gettext('Never Scheduled Shifts'),
									);
				break;

			//alter table contributing_shift_policy  add column include_shift_type_id integer DEFAULT 100;
			case 'include_shift_type':
				$retval = array(
										//If shift meets below criteria, only the part that meets it is included.
										100 => TTi18n::gettext('Split Shift (Partial)'), //Splits the worked time to the filter Start/End time.
										//110 => TTi18n::gettext('Partial Shift (Shift Must Start)'), //Normal Punch In between Start/End Time
										//120 => TTi18n::gettext('Partial Shift (Shift Must End)'), //Normal Punch Out between Start/End Time
										//130 => TTi18n::gettext('Partial Shift (Majority of Shift)'), //Majority of shift falls between Start/End time


										//If shift meets below criteria, the entire shift is included.
										200 => TTi18n::gettext('Full Shift (Must Start & End)'), //Does not split worked time to the Start/End time. Full shift must fall within filter times.
										210 => TTi18n::gettext('Full Shift (Must Start)'), //Normal Punch In between filter Start/End Time
										220 => TTi18n::gettext('Full Shift (Must End)'), //Normal Punch Out between filter Start/End Time
										230 => TTi18n::gettext('Full Shift (Majority of Shift Worked)'), //Majority of time worked falls between filter Start/End time. Tie breaker (50/50%) goes to start time.
										//232 => TTi18n::gettext('Full Shift (Majority of Shift Worked [Start])'), //Majority of shift worked falls between Start/End time. Using Start time as tie breaker.
										//234 => TTi18n::gettext('Full Shift (Majority of Shift Worked [End])'), //Majority of shift worked falls between Start/End time. Using End time as tie breaker.

										330 => TTi18n::gettext('Full Shift (Majority of Shift Observed)'), //Majority of shift observed (only considers shift start/end time not time worked) falls between filter Start/End time. Tie breaker (50/50%) goes to start time.

										//FIXME: In future, perhaps add types to be based on the schedule time, not the worked time.
										//Differential is paid on what they work, but determined (rate of pay) by what they were supposed to work (schedule).
				);

				if ( Misc::getCurrentCompanyProductEdition() == 10 ) {
					unset( $retval[210], $retval[220], $retval[230]);
				}
				break;
			case 'include_holiday_type':
				$retval = array(
										10 => TTi18n::gettext('Have no effect'),
										20 => TTi18n::gettext('Always on Holidays'), //Eligible or not.
										25 => TTi18n::gettext('Always on Eligible Holidays'), //Only Eligible
										30 => TTi18n::gettext('Never on Holidays'),
									);
				break;
			case 'branch_selection_type':
				$retval = array(
										10 => TTi18n::gettext('All Branches'),
										20 => TTi18n::gettext('Only Selected Branches'),
										30 => TTi18n::gettext('All Except Selected Branches'),
									);
				break;
			case 'department_selection_type':
				$retval = array(
										10 => TTi18n::gettext('All Departments'),
										20 => TTi18n::gettext('Only Selected Departments'),
										30 => TTi18n::gettext('All Except Selected Departments'),
									);
				break;
			case 'job_group_selection_type':
				$retval = array(
										10 => TTi18n::gettext('All Job Groups'),
										20 => TTi18n::gettext('Only Selected Job Groups'),
										30 => TTi18n::gettext('All Except Selected Job Groups'),
									);
				break;
			case 'job_selection_type':
				$retval = array(
										10 => TTi18n::gettext('All Jobs'),
										20 => TTi18n::gettext('Only Selected Jobs'),
										30 => TTi18n::gettext('All Except Selected Jobs'),
									);
				break;
			case 'job_item_group_selection_type':
				$retval = array(
										10 => TTi18n::gettext('All Task Groups'),
										20 => TTi18n::gettext('Only Selected Task Groups'),
										30 => TTi18n::gettext('All Except Selected Task Groups'),
									);
				break;
			case 'job_item_selection_type':
				$retval = array(
										10 => TTi18n::gettext('All Tasks'),
										20 => TTi18n::gettext('Only Selected Tasks'),
										30 => TTi18n::gettext('All Except Selected Tasks'),
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-name' => TTi18n::gettext('Name'),
										'-1020-description' => TTi18n::gettext('Description'),

										'-1900-in_use' => TTi18n::gettext('In Use'),

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
								'description',
								'updated_date',
								'updated_by',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name',
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
										'name' => 'Name',
										'description' => 'Description',

										'contributing_pay_code_policy_id' => 'ContributingPayCodePolicy',

										'filter_start_date' => 'FilterStartDate',
										'filter_end_date' => 'FilterEndDate',
										'filter_start_time' => 'FilterStartTime',
										'filter_end_time' => 'FilterEndTime',
										'filter_minimum_time' => 'FilterMinimumTime',
										'filter_maximum_time' => 'FilterMaximumTime',
										'include_shift_type_id' => 'IncludeShiftType',

										'branch' => 'Branch',
										'branch_selection_type_id' => 'BranchSelectionType',
										'branch_selection_type' => FALSE,
										'exclude_default_branch' => 'ExcludeDefaultBranch',
										'department' => 'Department',
										'department_selection_type_id' => 'DepartmentSelectionType',
										'department_selection_type' => FALSE,
										'exclude_default_department' => 'ExcludeDefaultDepartment',
										'job_group' => 'JobGroup',
										'job_group_selection_type_id' => 'JobGroupSelectionType',
										'job_group_selection_type' => FALSE,
										'job' => 'Job',
										'job_selection_type_id' => 'JobSelectionType',
										'job_selection_type' => FALSE,
										'exclude_default_job' => 'ExcludeDefaultJob',
										'job_item_group' => 'JobItemGroup',
										'job_item_group_selection_type_id' => 'JobItemGroupSelectionType',
										'job_item_group_selection_type' => FALSE,
										'job_item' => 'JobItem',
										'job_item_selection_type_id' => 'JobItemSelectionType',
										'job_item_selection_type' => FALSE,
										'exclude_default_job_item' => 'ExcludeDefaultJobItem',

										'sun' => 'Sun',
										'mon' => 'Mon',
										'tue' => 'Tue',
										'wed' => 'Wed',
										'thu' => 'Thu',
										'fri' => 'Fri',
										'sat' => 'Sat',

										'include_holiday_type_id' => 'IncludeHolidayType',
										'holiday_policy' => 'HolidayPolicy',

										'in_use' => FALSE,
										'deleted' => 'Deleted',
										);
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
	function getContributingPayCodePolicyObject() {
		return $this->getGenericObject( 'ContributingPayCodePolicyListFactory', $this->getContributingPayCodePolicy(), 'contributing_pay_code_policy_obj' );
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
	function setCompany( $value) {
		$value = TTUUID::castUUID( $value );

		Debug::Text('Company ID: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name) {
		$name = trim($name);
		if ( $name == '' ) {
			return FALSE;
		}

		$ph = array(
					'company_id' => TTUUID::castUUID($this->getCompany()),
					'name' => TTi18n::strtolower($name),
					);

		$query = 'select id from '. $this->getTable() .' where company_id = ? AND lower(name) = ? AND deleted=0';
		$id = $this->db->GetOne($query, $ph);
		Debug::Arr($id, 'Unique: '. $name, __FILE__, __LINE__, __METHOD__, 10);

		if ( $id === FALSE ) {
			return TRUE;
		} else {
			if ($id == $this->getId() ) {
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
	function setDescription( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'description', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getContributingPayCodePolicy() {
		return $this->getGenericDataValue( 'contributing_pay_code_policy_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setContributingPayCodePolicy( $value) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'contributing_pay_code_policy_id', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getFilterStartDate( $raw = FALSE ) {
		$value = $this->getGenericDataValue( 'filter_start_date' );
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
	function setFilterStartDate( $value) {
		$value = ( !is_int($value) ) ? trim($value) : $value; //Dont trim integer values, as it changes them to strings.
		return $this->setGenericDataValue( 'filter_start_date', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getFilterEndDate( $raw = FALSE ) {
		$value = $this->getGenericDataValue( 'filter_end_date' );
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
	function setFilterEndDate( $value) {
		$value = ( !is_int($value) ) ? trim($value) : $value; //Dont trim integer values, as it changes them to strings.
		return $this->setGenericDataValue( 'filter_end_date', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getFilterStartTime( $raw = FALSE ) {
		$value = $this->getGenericDataValue( 'filter_start_time' );
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
	function setFilterStartTime( $value) {
		$value = ( !is_int($value) ) ? trim($value) : $value; //Dont trim integer values, as it changes them to strings.
		return $this->setGenericDataValue( 'filter_start_time', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getFilterEndTime( $raw = FALSE ) {
		$value = $this->getGenericDataValue( 'filter_end_time' );
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
	function setFilterEndTime( $value) {
		$value = ( !is_int($value) ) ? trim($value) : $value; //Dont trim integer values, as it changes them to strings.
		return $this->setGenericDataValue( 'filter_end_time', $value );
	}

	/**
	 * @return bool|int
	 */
	function getFilterMinimumTime() {
		return $this->getGenericDataValue( 'filter_minimum_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setFilterMinimumTime( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'filter_minimum_time', $value );
	}

	/**
	 * @return bool|int
	 */
	function getFilterMaximumTime() {
		return $this->getGenericDataValue( 'filter_maximum_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setFilterMaximumTime( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'filter_maximum_time', $value );
	}

	/**
	 * @return bool|int
	 */
	function getIncludeShiftType() {
		return (int)$this->getGenericDataValue( 'include_shift_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setIncludeShiftType( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'include_shift_type_id', $value );
	}

	/*

	Branch/Department/Job/Task filter functions

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
	function setBranchSelectionType( $value) {
		$value = (int)trim($value);
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
	function setExcludeDefaultBranch( $value) {
		return $this->setGenericDataValue( 'exclude_default_branch', $this->toBool($value) );
	}

	/**
	 * @return mixed
	 */
	function getBranch() {
		return $this->getCompanyGenericMapData( $this->getCompany(), 610, $this->getID(), 'branch_map' );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setBranch( $ids) {
		Debug::text('Setting Branch IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 610, $this->getID(), (array)$ids );
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
	function setDepartmentSelectionType( $value) {
		$value = (int)trim($value);
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
	function setExcludeDefaultDepartment( $value) {
		return $this->setGenericDataValue( 'exclude_default_department', $this->toBool($value) );
	}

	/**
	 * @return mixed
	 */
	function getDepartment() {
		return $this->getCompanyGenericMapData( $this->getCompany(), 620, $this->getID(), 'department_map' );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setDepartment( $ids) {
		Debug::text('Setting Department IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 620, $this->getID(), (array)$ids );
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
	function setJobGroupSelectionType( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'job_group_selection_type_id', $value );
	}

	/**
	 * @return mixed
	 */
	function getJobGroup() {
		return $this->getCompanyGenericMapData( $this->getCompany(), 640, $this->getID(), 'job_group_map' );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setJobGroup( $ids) {
		Debug::text('Setting Job Group IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 640, $this->getID(), (array)$ids );
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
	function setJobSelectionType( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'job_selection_type_id', $value );
	}

	/**
	 * @return mixed
	 */
	function getJob() {
		return $this->getCompanyGenericMapData( $this->getCompany(), 630, $this->getID(), 'job_map' );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setJob( $ids) {
		Debug::text('Setting Job IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 630, $this->getID(), (array)$ids );
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
	function setExcludeDefaultJob( $value) {
		return $this->setGenericDataValue( 'exclude_default_job', $this->toBool($value) );
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
	function setJobItemGroupSelectionType( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'job_item_group_selection_type_id', $value );
	}

	/**
	 * @return mixed
	 */
	function getJobItemGroup() {
		return $this->getCompanyGenericMapData( $this->getCompany(), 660, $this->getID(), 'job_item_group_map' );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setJobItemGroup( $ids) {
		Debug::text('Setting Task Group IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 660, $this->getID(), (array)$ids );
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
	function setJobItemSelectionType( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'job_item_selection_type_id', $value );
	}

	/**
	 * @return mixed
	 */
	function getJobItem() {
		return $this->getCompanyGenericMapData( $this->getCompany(), 650, $this->getID(), 'job_item_map' );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setJobItem( $ids) {
		Debug::text('Setting Task IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 650, $this->getID(), (array)$ids );
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
	function setExcludeDefaultJobItem( $value) {
		return $this->setGenericDataValue( 'exclude_default_job_item', $this->toBool($value) );
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
	function setSun( $value) {
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
	 * @return bool|int
	 */
	function getIncludeScheduleShiftType() {
		return (int)$this->getGenericDataValue( 'include_schedule_shift_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setIncludeScheduleShiftType( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'include_schedule_shift_type_id', $value );
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
	function setIncludeHolidayType( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'include_holiday_type_id', $value );
	}

	/**
	 * @return array|bool
	 */
	function getHolidayPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 690, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setHolidayPolicy( $ids) {
		Debug::text('Setting Holiday Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 690, $this->getID(), (array)$ids );
	}

	/**
	 * @param int $epoch EPOCH
	 * @param object $calculate_policy_obj
	 * @return bool
	 */
	function isHoliday( $epoch, $calculate_policy_obj ) {
		if ( $epoch == '' OR !is_object($calculate_policy_obj) ) {
			return FALSE;
		}

		if ( $this->isHolidayRestricted() == TRUE ) {
			//Get holidays from all holiday policies assigned to this contributing shift policy
			$holiday_policy_ids = $this->getHolidayPolicy();
			if ( is_array($holiday_policy_ids) AND count($holiday_policy_ids) > 0 ) {
				foreach( $holiday_policy_ids as $holiday_policy_id ) {
					if ( isset($calculate_policy_obj->holiday_policy[$holiday_policy_id]) ) {
						$holiday_policies = $calculate_policy_obj->filterHoliday( $epoch, $calculate_policy_obj->holiday_policy[$holiday_policy_id], NULL );
						if ( is_array( $holiday_policies ) AND count( $holiday_policies ) > 0 ) {
							Debug::text(' Is Holiday: User ID: '. $calculate_policy_obj->getUserObject()->getID() .' Date: '. TTDate::getDate('DATE', $epoch), __FILE__, __LINE__, __METHOD__, 10);

							//Check if its only eligible holidays or all holidays.
							if ( $this->getIncludeHolidayType() == 20 OR $this->getIncludeHolidayType() == 30 ) {
								Debug::text(' Active for all Holidays', __FILE__, __LINE__, __METHOD__, 10);
								return TRUE;
							} elseif ( $this->getIncludeHolidayType() == 25 AND $calculate_policy_obj->isEligibleForHoliday( $epoch, $calculate_policy_obj->holiday_policy[$holiday_policy_id] ) == TRUE ) {
								Debug::text(' Is Eligible for Holiday', __FILE__, __LINE__, __METHOD__, 10);
								return TRUE;
							}
						}
					}
				}
			}
		}

		Debug::text(' Not Holiday: User ID: '. $calculate_policy_obj->getUserObject()->getID() .' Date: '. TTDate::getDate('DATE', $epoch), __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	/**
	 * @return bool
	 */
	function isHolidayRestricted() {
		if ( $this->getIncludeHolidayType() == 20 OR $this->getIncludeHolidayType() == 25 OR $this->getIncludeHolidayType() == 30 ) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Check if this premium policy is restricted by time.
	 * If its not, we can apply it to non-punched hours.
	 * @return bool
	 */
	function isTimeRestricted() {
		//If time restrictions account for over 23.5 hours, then we assume
		//that this policy is not time restricted at all.
		//The above is flawed, as a time restriction of 6AM to 6AM the next day is perfectly valid.
		if ( $this->getFilterStartTime() != '' AND $this->getFilterEndTime() != '' ) {
			Debug::text('IS time restricted...', __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		Debug::text('NOT time restricted...Filter Start Time: '. TTDate::getDate('DATE+TIME', $this->getFilterStartTime() ) .' End Time: '. TTDate::getDate('DATE+TIME', $this->getFilterEndTime() ), __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	/**
	 * @param int $date_epoch EPOCH
	 * @param object $calculate_policy_obj
	 * @return bool
	 */
	function isActiveDayOfWeekOrHoliday( $date_epoch, $calculate_policy_obj ) {
		//Debug::text(' Date: '. TTDate::getDate('DATE+TIME', $date_epoch) .' Include Holiday Type: '. $this->getIncludeHolidayType(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $this->getIncludeHolidayType() > 10 AND is_object( $calculate_policy_obj ) ) {
			$is_holiday = $this->isHoliday( TTDate::getMiddleDayEpoch( $date_epoch ), $calculate_policy_obj );
		} else {
			$is_holiday = FALSE;
		}

		if ( ( $this->getIncludeHolidayType() == 10 AND $this->isActiveFilterDate($date_epoch) == TRUE AND $this->isActiveFilterDayOfWeek($date_epoch) == TRUE )
				OR ( ( $this->getIncludeHolidayType() == 20 OR $this->getIncludeHolidayType() == 25 ) AND ( ( $this->isActiveFilterDate($date_epoch) == TRUE AND $this->isActiveFilterDayOfWeek($date_epoch) == TRUE ) OR $is_holiday == TRUE ) )
				OR ( $this->getIncludeHolidayType() == 30 AND ( ( $this->isActiveFilterDate($date_epoch) == TRUE AND $this->isActiveFilterDayOfWeek($date_epoch) == TRUE ) AND $is_holiday == FALSE ) )
		) {
			Debug::text('Active Date/DayOfWeek: '. TTDate::getDate('DATE+TIME', $date_epoch), __FILE__, __LINE__, __METHOD__, 10);

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param int $date_epoch EPOCH
	 * @param int $in_epoch EPOCH
	 * @param int $out_epoch EPOCH
	 * @param null $udt_key
	 * @param null $shift_data
	 * @param object $calculate_policy_obj
	 * @return bool
	 */
	function isActive( $date_epoch, $in_epoch = NULL, $out_epoch = NULL, $udt_key = NULL, $shift_data = NULL, $calculate_policy_obj = NULL ) {
		//Debug::text(' Date Epoch: '. $date_epoch .' In: '. $in_epoch .' Out: '. $out_epoch, __FILE__, __LINE__, __METHOD__, 10);
		//Make sure date_epoch is always specified so we can still determine isActive even if in_epoch/out_epoch are not specified themselves.
		if ( $date_epoch == '' AND $in_epoch == '' ) {
			Debug::text(' ERROR: Date/In epoch not specified...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		//If we're including Full Shift types, try to use the shift start/end time rather than just the start/end time of the UDT record.
		//Otherwise a shift that spans midnight with daily overtime (being in the next day only) and evening premium set to only include the first day, the premium won't be calculated as it won't match the date.
		if ( $udt_key != '' AND $this->getIncludeShiftType() >= 200 AND isset($shift_data['user_date_total_key_map'][$udt_key]) ) {
			$udt_shift_data = ( isset($shift_data['user_date_total_key_map'][$udt_key]) ) ? $shift_data[$shift_data['user_date_total_key_map'][$udt_key]] : FALSE;
			if ( is_array( $udt_shift_data ) AND isset($udt_shift_data['first_in']) AND isset($udt_shift_data['last_out']) ) {
				$date_epoch = $calculate_policy_obj->user_date_total[$udt_shift_data['first_in']]->getDateStamp();
				$in_epoch = $calculate_policy_obj->user_date_total[$udt_shift_data['first_in']]->getStartTimeStamp();
				$out_epoch = $calculate_policy_obj->user_date_total[$udt_shift_data['last_out']]->getEndTimeStamp();
			}
		}

		if ( $date_epoch != '' AND $in_epoch == '' ) {
			$in_epoch = $date_epoch;
		}

		if ( $out_epoch == '' ) {
			$out_epoch = $in_epoch;
		}

		//Debug::text(' In: '. TTDate::getDate('DATE+TIME', $in_epoch) .' Out: '. TTDate::getDate('DATE+TIME', $out_epoch), __FILE__, __LINE__, __METHOD__, 10);
		$i = $in_epoch;
		$last_iteration = 0;
		//Make sure we loop on the in_epoch, out_epoch and every day inbetween. $last_iteration allows us to always hit the out_epoch.
		while( $i <= $out_epoch AND $last_iteration <= 1 ) {
			//Debug::text(' I: '. TTDate::getDate('DATE+TIME', $i) .' Include Holiday Type: '. $this->getIncludeHolidayType(), __FILE__, __LINE__, __METHOD__, 10);
			$tmp_retval = $this->isActiveDayOfWeekOrHoliday( $i, $calculate_policy_obj );
			if ( $tmp_retval == TRUE ) {
				return TRUE;
			}

			//If there is more than one day between $i and $out_epoch, add one day to $i.
			if ( $i < ( $out_epoch - 86400 ) ) {
				$i += 86400;
			} else {
				//When less than one day until $out_epoch, skip to $out_epoch and loop once more.
				$i = $out_epoch;
				$last_iteration++;
			}
		}

		//Debug::text('NOT Active Date/DayOfWeek: '. TTDate::getDate('DATE+TIME', $i), __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	/**
	 * @param $filter_start_time_stamp
	 * @param $filter_end_time_stamp
	 * @param $shift_data
	 * @param object $calculate_policy_obj
	 * @return array|bool
	 */
	function calculateShiftDataOverlapFilterTime( $filter_start_time_stamp, $filter_end_time_stamp, $shift_data, $calculate_policy_obj = NULL ) {
		if ( is_array($shift_data) ) {
			if ( isset($shift_data['user_date_total_keys']) ) {
				foreach( $shift_data['user_date_total_keys'] as $udt_key ) {
					if ( isset($calculate_policy_obj->user_date_total[$udt_key]) AND is_object($calculate_policy_obj->user_date_total[$udt_key])) {
						$udt_obj = $calculate_policy_obj->user_date_total[$udt_key];

						//Debug::Text(' UDT Start: '. TTDate::getDate('DATE+TIME', $udt_obj->getStartTimeStamp() ) .' End: '. TTDate::getDate('DATE+TIME', $udt_obj->getEndTimeStamp() ) .' Filter: Start: '. TTDate::getDate('DATE+TIME', $filter_start_time_stamp ) .' End: '. TTDate::getDate('DATE+TIME', $filter_end_time_stamp ), __FILE__, __LINE__, __METHOD__, 10);
						$time_overlap_arr = TTDate::getTimeOverLap($udt_obj->getStartTimeStamp(), $udt_obj->getEndTimeStamp(), $filter_start_time_stamp, $filter_end_time_stamp );
						if ( is_array($time_overlap_arr) ) {
							$time_overlap = ( $time_overlap_arr['end_date'] - $time_overlap_arr['start_date'] );
							if ( !isset($shift_data['total_time_filter_overlap'] ) ) {
								$shift_data['total_time_filter_overlap'] = 0;
							}
							$shift_data['total_time_filter_overlap'] += $time_overlap;
						}
					}
				}
			}

			return $shift_data;
		}

		return FALSE;
	}

	/**
	 * Check if this time is within the start/end time.
	 * @param int $in_epoch EPOCH
	 * @param int $out_epoch EPOCH
	 * @param null $udt_key
	 * @param null $shift_data
	 * @param object $calculate_policy_obj
	 * @return bool
	 */
	function isActiveFilterTime( $in_epoch, $out_epoch, $udt_key = NULL, $shift_data = NULL, $calculate_policy_obj = NULL ) {
		//Debug::text(' Checking for Active Time with: In: '. TTDate::getDate('DATE+TIME', $in_epoch) .' Out: '. TTDate::getDate('DATE+TIME', $out_epoch), __FILE__, __LINE__, __METHOD__, 10);
		if ( $in_epoch == '' OR $out_epoch == '' ) {
			//Debug::text(' Empty time stamps, returning TRUE.', __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		//If no start/end time filters are set, we can short circuit this by making sure the exact date (no forward/backward date checks) matches and return TRUE.
		if ( $this->getFilterStartTime() == '' AND $this->getFilterEndTime() == '' AND isset($calculate_policy_obj->user_date_total[$udt_key]) ) {
			return $this->isActive( $calculate_policy_obj->user_date_total[$udt_key]->getDateStamp(), NULL, NULL, NULL, NULL, $calculate_policy_obj );
		}

		//Debug::text(' PP Raw Start TimeStamp('.$this->getFilterStartTime(TRUE).'): '. TTDate::getDate('DATE+TIME', $this->getFilterStartTime() ) .' Raw End TimeStamp: '. TTDate::getDate('DATE+TIME', $this->getFilterEndTime() ), __FILE__, __LINE__, __METHOD__, 10);
		$start_time_stamp = TTDate::getTimeLockedDate( $this->getFilterStartTime(), $in_epoch); //Base the end time on day of the in_epoch.
		$end_time_stamp = TTDate::getTimeLockedDate( $this->getFilterEndTime(), $in_epoch); //Base the end time on day of the in_epoch.

		//Check if end timestamp is before start, if it is, move end timestamp to next day.
		if ( $end_time_stamp < $start_time_stamp ) {
			Debug::text(' Moving End TimeStamp to next day.', __FILE__, __LINE__, __METHOD__, 10);
			$end_time_stamp = TTDate::getTimeLockedDate( $this->getFilterEndTime(), ( TTDate::getMiddleDayEpoch($end_time_stamp) + 86400 ) ); //Due to DST, jump ahead 1.5 days, then jump back to the time locked date.
		}

		//Debug::text(' Start TimeStamp: '. TTDate::getDate('DATE+TIME', $start_time_stamp) .' End TimeStamp: '. TTDate::getDate('DATE+TIME', $end_time_stamp), __FILE__, __LINE__, __METHOD__, 10);
		//Check to see if start/end time stamps are not set or are equal, we always return TRUE if they are.
		if ( $this->getIncludeHolidayType() == 10
				AND ( $start_time_stamp == '' OR $end_time_stamp == '' OR $start_time_stamp == $end_time_stamp ) ) {
			//Debug::text(' Start/End time not set, assume it always matches.', __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		} else {
			//If the premium policy start/end time spans midnight, there could be multiple windows to check
			//where the premium policy applies, make sure we check all windows.
			for( $i = (TTDate::getMiddleDayEpoch($start_time_stamp) - 86400); $i <= (TTDate::getMiddleDayEpoch($end_time_stamp) + 86400); $i += 86400 ) {
				$tmp_start_time_stamp = TTDate::getTimeLockedDate( $this->getFilterStartTime(), TTDate::getBeginDayEpoch( $i ) );
				$next_i = ( $tmp_start_time_stamp + ($end_time_stamp - $start_time_stamp) ); //Get next date to base the end_time_stamp on, and to calculate if we need to adjust for DST.
				$tmp_end_time_stamp = TTDate::getTimeLockedDate( $end_time_stamp, ( $next_i + ( TTDate::getDSTOffset( $tmp_start_time_stamp, $next_i ) * -1 ) ) ); //Use $end_time_stamp as it can be modified above due to being near midnight. Also adjust for DST by reversing it.
				if ( $this->isActive( $tmp_start_time_stamp, $tmp_start_time_stamp, $tmp_end_time_stamp, $udt_key, $shift_data, $calculate_policy_obj ) == TRUE ) {
					Debug::text(' Checking against Start TimeStamp: '. TTDate::getDate('DATE+TIME', $tmp_start_time_stamp) .'('.$tmp_start_time_stamp.') End TimeStamp: '. TTDate::getDate('DATE+TIME', $tmp_end_time_stamp) .'('.$tmp_end_time_stamp.')', __FILE__, __LINE__, __METHOD__, 10);
					if ( $this->getIncludeShiftType() == 100 AND TTDate::isTimeOverLap( $in_epoch, $out_epoch, $tmp_start_time_stamp, $tmp_end_time_stamp) == TRUE ) { //100=Partial Shift
						//When dealing with partial punches, any overlap whatsoever activates the policy.
						Debug::text(' Partial Punch Within Active Time!', __FILE__, __LINE__, __METHOD__, 10);
						return TRUE;
					} elseif ( $this->getIncludeShiftType() == 200 AND $in_epoch >= $tmp_start_time_stamp AND $out_epoch <= $tmp_end_time_stamp
							AND $this->isActiveDayOfWeekOrHoliday( $tmp_start_time_stamp, $calculate_policy_obj ) AND $this->isActiveDayOfWeekOrHoliday( $tmp_end_time_stamp, $calculate_policy_obj ) ) { //200=Full Shift (Must Start & End)
						//Non partial punches, they must punch in AND out (entire shift) within the time window.
						Debug::text(' Within Active Time!', __FILE__, __LINE__, __METHOD__, 10);
						return TRUE;
					} elseif (  getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL
								AND ( in_array( $this->getIncludeShiftType(), array( 210, 220, 230, 330 ) ) )
								AND ( isset($calculate_policy_obj->user_date_total[$udt_key]) AND is_object($calculate_policy_obj->user_date_total[$udt_key]) )
								AND isset($shift_data['user_date_total_key_map'][$udt_key])
								AND isset($shift_data[$shift_data['user_date_total_key_map'][$udt_key]]) ) {
						$tmp_shift_data = $this->calculateShiftDataOverlapFilterTime( $tmp_start_time_stamp, $tmp_end_time_stamp, $shift_data[$shift_data['user_date_total_key_map'][$udt_key]], $calculate_policy_obj );
						//Debug::Arr($tmp_shift_data, ' Majority Shift Data: UDT Key: '. $udt_key, __FILE__, __LINE__, __METHOD__, 10);

						if ( $this->getIncludeShiftType() == 210 ) { //210=Full Shift (Shift Must Start)
							if ( isset( $tmp_shift_data['first_in'] )
									AND isset( $calculate_policy_obj->user_date_total[$tmp_shift_data['first_in']] )
									AND $calculate_policy_obj->user_date_total[$tmp_shift_data['first_in']]->getStartTimeStamp() >= $tmp_start_time_stamp
									AND $calculate_policy_obj->user_date_total[$tmp_shift_data['first_in']]->getStartTimeStamp() <= $tmp_end_time_stamp
									AND $this->isActiveDayOfWeekOrHoliday( $tmp_start_time_stamp, $calculate_policy_obj ) ) {
								Debug::text( ' Matched within Shift Start Time: UDT Key: ' . $udt_key, __FILE__, __LINE__, __METHOD__, 10 );
								return TRUE;
							}
//							else {
//								Debug::text( ' NOT Matched within Shift Start Time: UDT Key: ' . $udt_key, __FILE__, __LINE__, __METHOD__, 10 );
//							}
						} elseif ( $this->getIncludeShiftType() == 220 ) { //220=Full Shift (Shift Must End)
							if ( isset( $tmp_shift_data['last_out'] )
								AND isset( $calculate_policy_obj->user_date_total[$tmp_shift_data['last_out']] )
									AND $calculate_policy_obj->user_date_total[$tmp_shift_data['last_out']]->getEndTimeStamp() >= $tmp_start_time_stamp
									AND $calculate_policy_obj->user_date_total[$tmp_shift_data['last_out']]->getEndTimeStamp() <= $tmp_end_time_stamp
									AND $this->isActiveDayOfWeekOrHoliday( $tmp_end_time_stamp, $calculate_policy_obj ) ) {
								Debug::text( ' Matched within Shift End Time: UDT Key: ' . $udt_key, __FILE__, __LINE__, __METHOD__, 10 );
								return TRUE;
							}
//							else {
//								Debug::text( ' NOT Matched within Shift End Time: UDT Key: ' . $udt_key, __FILE__, __LINE__, __METHOD__, 10 );
//							}
						} elseif( $this->getIncludeShiftType() == 230 ) { //230=Full Shift (Majority of Shift Worked)
							if ( isset( $tmp_shift_data['total_time_filter_overlap'] ) AND $tmp_shift_data['total_time_filter_overlap'] > ( $tmp_shift_data['total_time'] / 2 )
									AND ( isset( $tmp_shift_data['day_with_most_time'] ) AND $this->isActiveDayOfWeekOrHoliday( $tmp_shift_data['day_with_most_time'], $calculate_policy_obj ) ) ) {
								Debug::text( ' Matched within Majority Shift: UDT Key: ' . $udt_key, __FILE__, __LINE__, __METHOD__, 10 );
								return TRUE;
							} elseif ( isset( $tmp_shift_data['total_time_filter_overlap'] ) AND $tmp_shift_data['total_time_filter_overlap'] == ( $tmp_shift_data['total_time'] / 2 ) ) {
								Debug::text( ' Shift has 50/50 split: UDT Key: ' . $udt_key, __FILE__, __LINE__, __METHOD__, 10 );
								if ( isset( $tmp_shift_data['first_in'] )
										AND isset( $calculate_policy_obj->user_date_total[$tmp_shift_data['first_in']] )
										AND $calculate_policy_obj->user_date_total[$tmp_shift_data['first_in']]->getStartTimeStamp() >= $tmp_start_time_stamp
										AND $calculate_policy_obj->user_date_total[$tmp_shift_data['first_in']]->getStartTimeStamp() <= $tmp_end_time_stamp
										AND $this->isActiveDayOfWeekOrHoliday( $tmp_start_time_stamp, $calculate_policy_obj ) ) {
									Debug::text( ' Matched within Majority Shift, 50/50 split: UDT Key: ' . $udt_key, __FILE__, __LINE__, __METHOD__, 10 );
									return TRUE;
								} else {
									Debug::text( ' NOT Matched within Majority Shift, 50/50 split: UDT Key: ' . $udt_key, __FILE__, __LINE__, __METHOD__, 10 );
								}
							}
//							else {
//								Debug::text( ' NOT Matched within Majority Shift: UDT Key: ' . $udt_key, __FILE__, __LINE__, __METHOD__, 10 );
//							}
						} elseif( $this->getIncludeShiftType() == 330 ) { //430=Full Shift (Majority of Shift Observed) where Observed is just the shift start time and end time, regardless of how much time they worked between that.
							Debug::text( ' Checking within Majority Shift Observed:  UDT Key: ' . $udt_key, __FILE__, __LINE__, __METHOD__, 10 );
							if ( isset( $tmp_shift_data['first_in'] ) AND isset( $calculate_policy_obj->user_date_total[$tmp_shift_data['first_in']] )
									AND isset( $tmp_shift_data['last_out'] ) AND isset( $calculate_policy_obj->user_date_total[$tmp_shift_data['last_out']] ) ) {
								$time_overlap_arr = TTDate::getTimeOverLap( $tmp_start_time_stamp, $tmp_end_time_stamp, $calculate_policy_obj->user_date_total[$tmp_shift_data['first_in']]->getStartTimeStamp(), $calculate_policy_obj->user_date_total[$tmp_shift_data['last_out']]->getEndTimeStamp() );
								$total_observed_shift_time = ( $calculate_policy_obj->user_date_total[$tmp_shift_data['last_out']]->getEndTimeStamp() - $calculate_policy_obj->user_date_total[$tmp_shift_data['first_in']]->getStartTimeStamp() );
								if ( is_array($time_overlap_arr) ) {
									if ( ( $time_overlap_arr['end_date'] - $time_overlap_arr['start_date'] ) > ( $total_observed_shift_time / 2 )
											AND $this->isActiveDayOfWeekOrHoliday( $tmp_shift_data['day_with_most_time'], $calculate_policy_obj ) ) {
										Debug::text( '   Matched within Majority Shift Observed: UDT Key: ' . $udt_key, __FILE__, __LINE__, __METHOD__, 10 );
										return TRUE;
									} elseif ( ( $time_overlap_arr['end_date'] - $time_overlap_arr['start_date'] ) == ( $total_observed_shift_time / 2 ) ) {
										Debug::text( '   Shift has 50/50 split: UDT Key: ' . $udt_key, __FILE__, __LINE__, __METHOD__, 10 );
										if ( $calculate_policy_obj->user_date_total[ $tmp_shift_data['first_in'] ]->getStartTimeStamp() >= $tmp_start_time_stamp
												AND $calculate_policy_obj->user_date_total[ $tmp_shift_data['first_in'] ]->getStartTimeStamp() <= $tmp_end_time_stamp
												AND $this->isActiveDayOfWeekOrHoliday( $tmp_start_time_stamp, $calculate_policy_obj ) ) {
											Debug::text( '   Matched within Majority Shift Observed, 50/50 split: UDT Key: ' . $udt_key, __FILE__, __LINE__, __METHOD__, 10 );
											return TRUE;
										} else {
											Debug::text( '   NOT Matched within Majority Shift Observed, 50/50 split: UDT Key: ' . $udt_key, __FILE__, __LINE__, __METHOD__, 10 );
										}
									}
								}
//								else {
//									Debug::text( '   No overlap within Majority Shift Observed: UDT Key: ' . $udt_key, __FILE__, __LINE__, __METHOD__, 10 );
//								}
								unset( $time_overlap_arr, $total_observed_shift_time );
							}
						}
					} elseif ( ( $start_time_stamp == '' OR $end_time_stamp == '' OR $start_time_stamp == $end_time_stamp ) ) { //Must go AFTER the above IF statements.
						//When IncludeHolidayType != 10 this trigger here.
						Debug::text(' No Start/End Date/Time!', __FILE__, __LINE__, __METHOD__, 10);
						return TRUE;
					}
//					else {
//						Debug::text( ' No match...', __FILE__, __LINE__, __METHOD__, 10 );
//					}
				} else {
					Debug::text(' Not Active on this day: Start: '. TTDate::getDate('DATE+TIME', $tmp_start_time_stamp) .' End: '. TTDate::getDate('DATE+TIME', $tmp_end_time_stamp), __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		}

		Debug::text(' NOT Within Active Time!', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	/**
	 * Check if this date is within the effective date range
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function isActiveFilterDate( $epoch ) {
		//Debug::text(' Checking for Active Date: '. TTDate::getDate('DATE+TIME', $epoch), __FILE__, __LINE__, __METHOD__, 10);
		$epoch = TTDate::getBeginDayEpoch( $epoch );

		if ( $this->getFilterStartDate() == '' AND $this->getFilterEndDate() == '') {
			return TRUE;
		}

		if ( $epoch >= (int)$this->getFilterStartDate()
				AND ( $epoch <= (int)$this->getFilterEndDate() OR $this->getFilterEndDate() == '' ) ) {
			return TRUE;
		}

		Debug::text(' Not active FilterDate!', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	/**
	 * Check if this day of the week is active
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function isActiveFilterDayOfWeek( $epoch ) {
		//Debug::Arr($epoch, ' Checking for Active Day of Week: '. $epoch, __FILE__, __LINE__, __METHOD__, 10);
		$day_of_week = strtolower( date('D', $epoch) );

		switch ($day_of_week) {
			case 'sun':
				if ( $this->getSun() == TRUE ) {
					return TRUE;
				}
				break;
			case 'mon':
				if ( $this->getMon() == TRUE ) {
					return TRUE;
				}
				break;
			case 'tue':
				if ( $this->getTue() == TRUE ) {
					return TRUE;
				}
				break;
			case 'wed':
				if ( $this->getWed() == TRUE ) {
					return TRUE;
				}
				break;
			case 'thu':
				if ( $this->getThu() == TRUE ) {
					return TRUE;
				}
				break;
			case 'fri':
				if ( $this->getFri() == TRUE ) {
					return TRUE;
				}
				break;
			case 'sat':
				if ( $this->getSat() == TRUE ) {
					return TRUE;
				}
				break;
		}

		Debug::text(' Not active FilterDayOfWeek!', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	/**
	 * @param object $udt_obj
	 * @param $udt_key
	 * @param object $calculate_policy_obj
	 * @return array|bool
	 */
	function getPartialUserDateTotalObject( $udt_obj, $udt_key, $calculate_policy_obj = NULL ) {
		if ( !is_object($udt_obj) ) {
			return FALSE;
		}

		Debug::text(' Checking for Active Time for '. $this->getName() .': In: '. TTDate::getDate('DATE+TIME', $udt_obj->getStartTimeStamp()) .' Out: '. TTDate::getDate('DATE+TIME', $udt_obj->getEndTimeStamp()), __FILE__, __LINE__, __METHOD__, 10);
		if ( $udt_obj->getStartTimeStamp() == '' OR $udt_obj->getEndTimeStamp() == '' ) {
			Debug::text(' Empty time stamps, returning object untouched...', __FILE__, __LINE__, __METHOD__, 10);
			return array( $udt_key => $udt_obj );
		}

		$filter_start_time_stamp = TTDate::getTimeLockedDate( $this->getFilterStartTime(), $udt_obj->getStartTimeStamp() ); //Base the end time on day of the in_epoch.
		$filter_end_time_stamp = TTDate::getTimeLockedDate( $this->getFilterEndTime(), $udt_obj->getStartTimeStamp() ); //Base the end time on day of the in_epoch.
		//Debug::text(' bChecking for Active Time with: In: '. TTDate::getDate('DATE+TIME', $filter_start_time_stamp ) .' Out: '. TTDate::getDate('DATE+TIME', $filter_end_time_stamp ), __FILE__, __LINE__, __METHOD__, 10);

		//Check if end timestamp is before start, if it is, move end timestamp to next day.
		if ( $filter_end_time_stamp < $filter_start_time_stamp ) {
			Debug::text(' Moving End TimeStamp to next day.', __FILE__, __LINE__, __METHOD__, 10);
			$filter_end_time_stamp = TTDate::getTimeLockedDate( $this->getFilterEndTime(), ( TTDate::getMiddleDayEpoch($filter_end_time_stamp) + 86400 ) ); //Due to DST, jump ahead 1.5 days, then jump back to the time locked date.
		}

		//Handle the last second of the day, so punches that span midnight like 11:00PM to 6:00AM get a full 1 hour for the time before midnight, rather than 59mins and 59secs.
		if ( TTDate::getHour( $filter_end_time_stamp ) == 23 AND TTDate::getMinute( $filter_end_time_stamp ) == 59 ) {
			$filter_end_time_stamp = ( TTDate::getEndDayEpoch( $filter_end_time_stamp ) + 1 );
			Debug::text(' End time stamp is within the last minute of day, make sure we include the last second of the day as well.', __FILE__, __LINE__, __METHOD__, 10);
		}

		if ( $filter_start_time_stamp == $filter_end_time_stamp ) {
			Debug::text(' Start/End time filters match, nothing to do...', __FILE__, __LINE__, __METHOD__, 10);
			return array( $udt_key => $udt_obj );
		}

		if ( $udt_obj->getStartTimeStamp() == $udt_obj->getEndTimeStamp() ) {
			Debug::text(' Start/End time match, nothing to do...', __FILE__, __LINE__, __METHOD__, 10);
			return array( $udt_key => $udt_obj );
		}

		$split_udt_time_stamps = TTDate::splitDateRangeAtMidnight( $udt_obj->getStartTimeStamp(), $udt_obj->getEndTimeStamp(), $filter_start_time_stamp, $filter_end_time_stamp );
		if ( is_array($split_udt_time_stamps) AND count($split_udt_time_stamps) > 0 ) {
			$i = 0;
			foreach ( $split_udt_time_stamps as $split_udt_time_stamp ) {
				$tmp_udt_obj = clone $udt_obj; //Make sure we clone the object so we don't modify the original record for all subsequent accesses.

				if ( $i > 0 ) {
					$udt_key = $calculate_policy_obj->user_date_total_insert_id;
					$tmp_udt_obj->setId( 0 ); //Reset the object ID to 0 for all but the first record, so it can be inserted as new rather than update/overwrite existing records.
				}

				$tmp_udt_obj->setStartTimeStamp( $split_udt_time_stamp['start_time_stamp'] );
				$tmp_udt_obj->setEndTimeStamp( $split_udt_time_stamp['end_time_stamp'] );

				//In cases where auto-deduct meal policies exist, the total time may be negative, and without digging into the source object we may never be able to determine that.
				//So when splitting records, if the total time is already negative, keep it as such.
				$total_time = $tmp_udt_obj->calcTotalTime();
				if ( $tmp_udt_obj->getTotalTime() < 0 ) {
					$total_time = ($total_time * -1);
					Debug::text(' Total Time was negative, maintain minus... Total Time: Before: '. $tmp_udt_obj->getTotalTime() .' After: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);
				}
				$tmp_udt_obj->setTotalTime( $total_time );
				$tmp_udt_obj->setIsPartialShift( TRUE );
				$tmp_udt_obj->setEnableCalcSystemTotalTime( FALSE );
				if ( $tmp_udt_obj->isValid() ) {
					$tmp_udt_obj->preSave(); //Call this so TotalTime, TotalTimeAmount is calculated immediately, as we don't save these records until later.
				}

				$retarr[$udt_key] = $tmp_udt_obj;
				$calculate_policy_obj->user_date_total_insert_id--;

				$i++;
			}

			//If no split actually occurred (at least more than 1 record), return the original record untouched.
			//Because splitting the record recalculates the TotalTime and sets isPartialShift(TRUE), we want to avoid modifying the data if at all possible.
			//This manifested itself as a bug when manually overriding UDT records to 0hrs, but leaving the Start/End timestamps at thier original value.
			if ( count($retarr) > 1 ) {
				return $retarr;
			}
		}

		Debug::text(' Nothing to split, returning original UDT record...', __FILE__, __LINE__, __METHOD__, 10);
		return array( $udt_key => $udt_obj );
	}

	/**
	 * @param $selection_type
	 * @param $exclude_default_item
	 * @param $current_item
	 * @param $allowed_items
	 * @param null $default_item
	 * @return bool
	 */
	function checkIndividualDifferentialCriteria( $selection_type, $exclude_default_item, $current_item, $allowed_items, $default_item = NULL ) {
		//Debug::Arr($allowed_items, '    Allowed Items: Selection Type: '. $selection_type .' Current Item: '. $current_item, __FILE__, __LINE__, __METHOD__, 10);

		//Used to use AND ( $allowed_items === FALSE OR ( is_array( $allowed_items ) AND in_array( $current_item, $allowed_items ) ) ) )
		// But checking $allowed_items === FALSE  makes it so if $selection_type = 20 and no selection is made it will still be accepted,
		// which is the exact opposite of what we want.
		// If $selection_type = (20,30) a selection must be made for it to match.
		if ( 	( $selection_type == 10
						AND ( $exclude_default_item == FALSE
								OR ( $exclude_default_item == TRUE AND $current_item != $default_item ) ) )

				OR ( $selection_type == 20
						AND ( is_array( $allowed_items ) AND in_array( $current_item, $allowed_items ) ) )
						AND ( $exclude_default_item == FALSE
								OR ( $exclude_default_item == TRUE AND $current_item != $default_item ) )

				OR ( $selection_type == 30
						AND ( is_array( $allowed_items ) AND !in_array( $current_item, $allowed_items ) ) )
						AND ( $exclude_default_item == FALSE
								OR ( $exclude_default_item == TRUE AND $current_item != $default_item ) )

				) {
			return TRUE;
		}

		//Debug::text('    Returning FALSE!', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	/**
	 * @param object $udt_obj
	 * @param object $user_obj
	 * @return bool
	 */
	function isActiveDifferential( $udt_obj, $user_obj ) {
		//Debug::Arr( array( $this->getBranchSelectionType(), (int)$this->getExcludeDefaultBranch(), $udt_obj->getBranch(), $user_obj->getDefaultBranch() ), ' Branch Selection: ', __FILE__, __LINE__, __METHOD__, 10);

		$retval = FALSE;

		//Optimization if all selection types are set to "All".
		if ( $this->getBranchSelectionType() == 10 AND $this->getDepartmentSelectionType() == 10 AND $this->getJobGroupSelectionType() == 10 AND $this->getJobSelectionType() == 10 AND $this->getJobItemGroupSelectionType() == 10 AND $this->getJobItemSelectionType() == 10
			AND $this->getExcludeDefaultBranch() == FALSE AND $this->getExcludeDefaultDepartment() == FALSE AND $this->getExcludeDefaultJob() == FALSE AND $this->getExcludeDefaultJobItem() == FALSE ) {
			return TRUE;
		}

		if ( $this->checkIndividualDifferentialCriteria( $this->getBranchSelectionType(), $this->getExcludeDefaultBranch(), $udt_obj->getBranch(), $this->getBranch(), $user_obj->getDefaultBranch() ) ) {
			//Debug::text(' Shift Differential... Meets Branch Criteria! Select Type: '. $this->getBranchSelectionType() .' Exclude Default Branch: '. (int)$this->getExcludeDefaultBranch() .' Default Branch: '.  $user_obj->getDefaultBranch(), __FILE__, __LINE__, __METHOD__, 10);

			if ( $this->checkIndividualDifferentialCriteria( $this->getDepartmentSelectionType(), $this->getExcludeDefaultDepartment(), $udt_obj->getDepartment(), $this->getDepartment(), $user_obj->getDefaultDepartment() ) ) {
				//Debug::text(' Shift Differential... Meets Department Criteria! Select Type: '. $this->getDepartmentSelectionType() .' Exclude Default Department: '. (int)$this->getExcludeDefaultDepartment() .' Default Department: '.  $user_obj->getDefaultDepartment(), __FILE__, __LINE__, __METHOD__, 10);

				$job_group = ( is_object( $udt_obj->getJobObject() ) ) ? $udt_obj->getJobObject()->getGroup() : NULL;
				if ( $this->checkIndividualDifferentialCriteria( $this->getJobGroupSelectionType(), NULL, $job_group, $this->getJobGroup() ) ) {
					//Debug::text(' Shift Differential... Meets Job Group Criteria! Select Type: '. $this->getJobGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

					if ( $this->checkIndividualDifferentialCriteria( $this->getJobSelectionType(), $this->getExcludeDefaultJob(), $udt_obj->getJob(), $this->getJob(), $user_obj->getDefaultJob() ) ) {
						//Debug::text(' Shift Differential... Meets Job Criteria! Select Type: '. $this->getJobSelectionType() .' Exclude Default Job: '. (int)$this->getExcludeDefaultJob() .' Default Job: '.  $user_obj->getDefaultJob(), __FILE__, __LINE__, __METHOD__, 10);

						$job_item_group = ( is_object( $udt_obj->getJobItemObject() ) ) ? $udt_obj->getJobItemObject()->getGroup() : NULL;
						if ( $this->checkIndividualDifferentialCriteria( $this->getJobItemGroupSelectionType(), NULL, $job_item_group, $this->getJobItemGroup() ) ) {
							//Debug::text(' Shift Differential... Meets Task Group Criteria! Select Type: '. $this->getJobItemGroupSelectionType(), __FILE__, __LINE__, __METHOD__, 10);

							if ( $this->checkIndividualDifferentialCriteria( $this->getJobItemSelectionType(), $this->getExcludeDefaultJobItem(), $udt_obj->getJobItem(), $this->getJobItem(), $user_obj->getDefaultJobItem() ) ) {
								//Debug::text(' Shift Differential... Meets Task Criteria! Select Type: '. $this->getJobSelectionType() .' Exclude Default Task: '. (int)$this->getExcludeDefaultJobItem() .' Default Task: '.  $user_obj->getDefaultJobItem(), __FILE__, __LINE__, __METHOD__, 10);
								$retval = TRUE;
							}
						}
					}
				}
			}
		}
		unset($job_group, $job_item_group);

		//Debug::text(' Active Shift Differential Result: '. (int)$retval, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
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
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$this->Validator->isResultSetWithRows(	'company',
														$clf->getByID($this->getCompany()),
														TTi18n::gettext('Company is invalid')
													);
		// Name
		if ( $this->Validator->getValidateOnly() == FALSE ) { //Don't check the below when mass editing, but must check when adding a new record..
			if ( $this->getName() == '' ) {
				$this->Validator->isTRUE( 'name',
											FALSE,
											TTi18n::gettext( 'Please specify a name' ) );
			}
		}
		if ( $this->getName() !== FALSE ) {
			if ( $this->getName() != '' AND $this->Validator->isError('name') == FALSE ) {
				$this->Validator->isLength(	'name',
													$this->getName(),
													TTi18n::gettext('Name is too short or too long'),
													2, 75
												);
			}
			if ( $this->getName() != '' AND $this->Validator->isError('name') == FALSE ) {
				$this->Validator->isTrue(	'name',
													$this->isUniqueName($this->getName()),
													TTi18n::gettext('Name is already in use')
												);
			}
		}
		// Description
		if ( $this->getDescription() != '' ) {
			$this->Validator->isLength(	'description',
												$this->getDescription(),
												TTi18n::gettext('Description is invalid'),
												1, 250
											);
		}
		// Contributing Pay Code Policy
		if ( $this->getContributingPayCodePolicy() !== FALSE ) {
			$cpcplf = TTnew( 'ContributingPayCodePolicyListFactory' ); /** @var ContributingPayCodePolicyListFactory $cpcplf */
			$this->Validator->isResultSetWithRows(	'contributing_pay_code_policy_id',
															$cpcplf->getByID($this->getContributingPayCodePolicy()),
															TTi18n::gettext('Contributing Pay Code Policy is invalid')
														);
		}
		// Start date
		if ( $this->getFilterStartDate() != '' ) {
			$this->Validator->isDate(		'filter_start_date',
													$this->getFilterStartDate(),
													TTi18n::gettext('Incorrect start date')
												);
		}
		// End date
		if ( $this->getFilterEndDate() != '' ) {
			$this->Validator->isDate(		'filter_end_date',
													$this->getFilterEndDate(),
													TTi18n::gettext('Incorrect end date')
												);
		}
		// Start time
		if ( $this->getFilterStartTime() != '' ) {
			$this->Validator->isDate(		'filter_start_time',
													$this->getFilterStartTime(),
													TTi18n::gettext('Incorrect Start time')
												);
		}
		// End time
		if ( $this->getFilterEndTime() != '' ) {
			$this->Validator->isDate(		'filter_end_time',
													$this->getFilterEndTime(),
													TTi18n::gettext('Incorrect End time')
												);
		}
		// Minimum Time
		if ( $this->getFilterMinimumTime() !== FALSE ) {
			$this->Validator->isNumeric(		'filter_minimum_time',
														$this->getFilterMinimumTime(),
														TTi18n::gettext('Incorrect Minimum Time')
													);
		}
		// Maximum Time
		if ( $this->getFilterMaximumTime() !== FALSE ) {
			$this->Validator->isNumeric(		'filter_maximum_time',
														$this->getFilterMaximumTime(),
														TTi18n::gettext('Incorrect Maximum Time')
													);
		}
		// Shift Type
		if ( $this->getIncludeShiftType() != '' ) {
			$this->Validator->inArrayKey(	  'include_shift_type_id',
														$this->getIncludeShiftType(),
														TTi18n::gettext('Incorrect Shift Type'),
														$this->getOptions('include_shift_type')
													);
		}
		// Branch Selection Type
		if ( $this->getBranchSelectionType() != '' ) {
			$this->Validator->inArrayKey(	'branch_selection_type_id',
													$this->getBranchSelectionType(),
													TTi18n::gettext('Incorrect Branch Selection Type'),
													$this->getOptions('branch_selection_type')
												);
		}
		// Department Selection Type
		if ( $this->getDepartmentSelectionType() != '' ) {
			$this->Validator->inArrayKey(	'department_selection_type_id',
													$this->getDepartmentSelectionType(),
													TTi18n::gettext('Incorrect Department Selection Type'),
													$this->getOptions('department_selection_type')
												);
		}
		// Job Group Selection Type
		if ( $this->getJobGroupSelectionType() != '' ) {
			$this->Validator->inArrayKey(	'job_group_selection_type_id',
													$this->getJobGroupSelectionType(),
													TTi18n::gettext('Incorrect Job Group Selection Type'),
													$this->getOptions('job_group_selection_type')
												);
		}
		// Job Selection Type
		if ( $this->getJobSelectionType() != '' ) {
			$this->Validator->inArrayKey(	'job_selection_type_id',
													$this->getJobSelectionType(),
													TTi18n::gettext('Incorrect Job Selection Type'),
													$this->getOptions('job_selection_type')
												);
		}
		// Task Group Selection Type
		if ( $this->getJobItemGroupSelectionType() != '' ) {
			$this->Validator->inArrayKey(	'job_item_group_selection_type_id',
													$this->getJobItemGroupSelectionType(),
													TTi18n::gettext('Incorrect Task Group Selection Type'),
													$this->getOptions('job_item_group_selection_type')
												);
		}
		// Task Selection Type
		if ( $this->getJobItemSelectionType() != '' ) {
			$this->Validator->inArrayKey(	'job_item_selection_type_id',
													$this->getJobItemSelectionType(),
													TTi18n::gettext('Incorrect Task Selection Type'),
													$this->getOptions('job_item_selection_type')
												);
		}
		// Include Schedule Shift Type
		if ( $this->getIncludeScheduleShiftType() != '' ) {
			$this->Validator->inArrayKey(	'include_schedule_shift_type_id',
													$this->getIncludeScheduleShiftType(),
													TTi18n::gettext('Incorrect Include Schedule Shift Type'),
													$this->getOptions('include_schedule_shift_type')
												);
		}
		// Include Holiday Type
		if ( $this->getIncludeHolidayType() != '' ) {
			$this->Validator->inArrayKey(	'include_holiday_type_id',
													$this->getIncludeHolidayType(),
													TTi18n::gettext('Incorrect Include Holiday Type'),
													$this->getOptions('include_holiday_type')
												);
		}
		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->getDeleted() == TRUE ) {
			$rtplf = TTNew('RegularTimePolicyListFactory'); /** @var RegularTimePolicyListFactory $rtplf */
			$rtplf->getByCompanyIdAndContributingShiftPolicyId( $this->getCompany(), $this->getId() );
			if ( $rtplf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
											FALSE,
											TTi18n::gettext('This contributing shift policy is currently in use') .' '. TTi18n::gettext('by regular time policies') );
			}

			$otplf = TTNew('OverTimePolicyListFactory'); /** @var OverTimePolicyListFactory $otplf */
			$otplf->getByCompanyIdAndContributingShiftPolicyId( $this->getCompany(), $this->getId() );
			if ( $otplf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
											FALSE,
											TTi18n::gettext('This contributing shift policy is currently in use') .' '. TTi18n::gettext('by overtime policies') );
			}

			$pplf = TTNew('PremiumPolicyListFactory'); /** @var PremiumPolicyListFactory $pplf */
			$pplf->getByCompanyIdAndContributingShiftPolicyId( $this->getCompany(), $this->getId() );
			if ( $pplf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
											FALSE,
											TTi18n::gettext('This contributing shift policy is currently in use') .' '. TTi18n::gettext('by premium policies') );
			}

			$hplf = TTNew('HolidayPolicyListFactory'); /** @var HolidayPolicyListFactory $hplf */
			$hplf->getByCompanyIdAndContributingShiftPolicyId( $this->getCompany(), $this->getId() );
			if ( $hplf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
											 FALSE,
											 TTi18n::gettext('This contributing shift policy is currently in use') .' '. TTi18n::gettext('by holiday policies') );
			}

			$aplf = TTNew('AccrualPolicyListFactory'); /** @var AccrualPolicyListFactory $aplf */
			$aplf->getByCompanyIdAndContributingShiftPolicyId( $this->getCompany(), $this->getId() );
			if ( $aplf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
											 FALSE,
											 TTi18n::gettext('This contributing shift policy is currently in use') .' '. TTi18n::gettext('by accrual policies') );
			}

			$pfplf = TTNew('PayFormulaPolicyListFactory'); /** @var PayFormulaPolicyListFactory $pfplf */
			$pfplf->getByCompanyIdAndContributingShiftPolicyId( $this->getCompany(), $this->getId() );
			if ( $pfplf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
											 FALSE,
											 TTi18n::gettext('This contributing shift policy is currently in use') .' '. TTi18n::gettext('by pay formula policies') );
			}
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getBranchSelectionType() === FALSE OR $this->getBranchSelectionType() < 10 ) {
			$this->setBranchSelectionType(10); //All
		}
		if ( $this->getDepartmentSelectionType() === FALSE OR $this->getDepartmentSelectionType() < 10 ) {
			$this->setDepartmentSelectionType(10); //All
		}
		if ( $this->getJobGroupSelectionType() === FALSE OR $this->getJobGroupSelectionType() < 10 ) {
			$this->setJobGroupSelectionType(10); //All
		}
		if ( $this->getJobSelectionType() === FALSE OR $this->getJobSelectionType() < 10 ) {
			$this->setJobSelectionType(10); //All
		}
		if ( $this->getJobItemGroupSelectionType() === FALSE OR $this->getJobItemGroupSelectionType() < 10 ) {
			$this->setJobItemGroupSelectionType(10); //All
		}
		if ( $this->getJobItemSelectionType() === FALSE OR $this->getJobItemSelectionType() < 10 ) {
			$this->setJobItemSelectionType(10); //All
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );

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
						case 'filter_start_date':
						case 'filter_end_date':
						case 'filter_start_time':
						case 'filter_end_time':
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
						case 'in_use':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'filter_start_date':
						case 'filter_end_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
							}
							break;
						case 'filter_start_time':
						case 'filter_end_time':
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Contributing Shift Policy'), NULL, $this->getTable(), $this );
	}
}
?>
