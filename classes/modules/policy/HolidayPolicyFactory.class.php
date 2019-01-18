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
class HolidayPolicyFactory extends Factory {
	protected $table = 'holiday_policy';
	protected $pk_sequence_name = 'holiday_policy_id_seq'; //PK Sequence name

	protected $company_obj = NULL;
	protected $round_interval_policy_obj = NULL;
	protected $absence_policy_obj = NULL;
	protected $contributing_shift_policy_obj = NULL;
	protected $eligible_contributing_shift_policy_obj = NULL;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = NULL ) {

		$retval = NULL;
		switch( $name ) {
			case 'default_schedule_status':
				$sf = TTnew( 'ScheduleFactory' );
				$retval = $sf->getOptions('status');
				break;
			case 'type':
				$retval = array(
										10 => TTi18n::gettext('Standard'),
										20 => TTi18n::gettext('Advanced: Fixed'),
										30 => TTi18n::gettext('Advanced: Average'),
									);
				break;
			case 'average_time_frequency_type':
				$retval = array(
						10 => TTi18n::gettext('Day(s)'), //Default
						15 => TTi18n::gettext('Week(s)'),
						20 => TTi18n::gettext('Pay Period(s)'), //Was used by Ontario from Jan 1st 2018 to Jul 1st 2018.
						//30 => TTi18n::gettext('Month'),
				);
				break;
			case 'scheduled_day':
				$retval = array(
										0 => TTi18n::gettext('Calendar Days'),
										1 => TTi18n::gettext('Scheduled Days'),
										2 => TTi18n::gettext('Holiday Week Days'),
									);
				break;
			case 'shift_on_holiday_type':

				//Label: On the Holiday the Employee:
				$retval = array(
						0 => TTi18n::gettext('May Work or May Not Work'),
						10 => TTi18n::gettext('Must Always Work'),
						20 => TTi18n::gettext('Must Never Work'),
						30 => TTi18n::gettext('Must Work (Only if Scheduled)'), //If scheduled to work, they must work. Otherwise if not scheduled (or scheduled absent) and they don't work its fine too.
						40 => TTi18n::gettext('Must Not Work (Only if Scheduled Absent)'), //If scheduled absent, they must not work. Otherwise if not scheduled, or scheduled to work and they work that is fine too.
						//50 => TTi18n::gettext('Must Work (if Scheduled), May Work if Not Scheduled)'), //If scheduled to work, they must work, otherwise if not scheduled (or scheduled absent) they don't work its fine too.
						//60 => TTi18n::gettext('Must Not Work (if Scheduled Absent), May Work if Not Scheduled)'), //If scheduled absent, they must not work.

						//70 => TTi18n::gettext('Must Not Work (Must be Scheduled)'), //Must not work, and must be scheduled to work.
						72 => TTi18n::gettext('Must Not Work (Must be Scheduled Absent)'), //Must not work, and must be scheduled absent. This is useful for holidays that fall on a day that the employee *is* normally scheduled to work.
						75 => TTi18n::gettext('Must Not Work (Must not be Scheduled)'), //Must not work, and must not be scheduled to work OR scheduled absent. This is useful for holidays that fall on a day that the employee *is not* normally scheduled to work.
				);
				break;
			case 'columns':
				$retval = array(
										'-1010-type' => TTi18n::gettext('Type'),
										'-1020-name' => TTi18n::gettext('Name'),
										'-1025-description' => TTi18n::gettext('Description'),

										'-1030-default_schedule_status' => TTi18n::gettext('Default Schedule Status'),
										'-1040-minimum_employed_days' => TTi18n::gettext('Minimum Employed Days'),

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
								'type',
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
										'type_id' => 'Type',
										'type' => FALSE,
										'name' => 'Name',
										'description' => 'Description',
										'default_schedule_status_id' => 'DefaultScheduleStatus',
										'default_schedule_status' => FALSE,
										'minimum_employed_days' => 'MinimumEmployedDays',
										'minimum_worked_period_days' => 'MinimumWorkedPeriodDays',
										'minimum_worked_days' => 'MinimumWorkedDays',
										'worked_scheduled_days' => 'WorkedScheduledDays',
										'shift_on_holiday_type_id' => 'ShiftOnHolidayType',
										'minimum_worked_after_period_days' => 'MinimumWorkedAfterPeriodDays',
										'minimum_worked_after_days' => 'MinimumWorkedAfterDays',
										'worked_after_scheduled_days' => 'WorkedAfterScheduledDays',
										'average_time_frequency_type_id' => 'AverageTimeFrequencyType',
										'average_time_days' => 'AverageTimeDays',
										'average_days' => 'AverageDays',
										'average_time_worked_days' => 'AverageTimeWorkedDays',
										'minimum_time' => 'MinimumTime',
										'maximum_time' => 'MaximumTime',
										'round_interval_policy_id' => 'RoundIntervalPolicyID',
										//'time' => 'Time',
										'paid_absence_as_worked' => 'PaidAbsenceAsWorked',
										'force_over_time_policy' => 'ForceOverTimePolicy',

										'contributing_shift_policy_id' => 'ContributingShiftPolicy',
										'contributing_shift_policy' => FALSE,
										'eligible_contributing_shift_policy_id' => 'EligibleContributingShiftPolicy',
										'eligible_contributing_shift_policy' => FALSE,

										'include_over_time' => 'IncludeOverTime',
										'include_paid_absence_time' => 'IncludePaidAbsenceTime',
										'absence_policy_id' => 'AbsencePolicyID',
										'recurring_holiday_id' => 'RecurringHoliday',
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
	function getRoundIntervalPolicyObject() {
		return $this->getGenericObject( 'RoundIntervalPolicyListFactory', $this->getRoundIntervalPolicyID(), 'round_interval_policy_obj' );
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
	function getContributingShiftPolicyObject() {
		return $this->getGenericObject( 'ContributingShiftPolicyListFactory', $this->getContributingShiftPolicy(), 'contributing_shift_policy_obj' );
	}

	/**
	 * @return bool
	 */
	function getEligibleContributingShiftPolicyObject() {
		return $this->getGenericObject( 'ContributingShiftPolicyListFactory', $this->getEligibleContributingShiftPolicy(), 'eligible_contributing_shift_policy_obj' );
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
	 * @return bool|int
	 */
	function getType() {
		return $this->getGenericDataValue( 'type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setType( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'type_id', $value );
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
	 * @return bool|int
	 */
	function getDefaultScheduleStatus() {
		return $this->getGenericDataValue( 'default_schedule_status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDefaultScheduleStatus( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'default_schedule_status_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getMinimumEmployedDays() {
		return $this->getGenericDataValue( 'minimum_employed_days' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinimumEmployedDays( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'minimum_employed_days', $value );
	}

	/**
	 * @return bool|int
	 */
	function getMinimumWorkedPeriodDays() {
		return $this->getGenericDataValue( 'minimum_worked_period_days' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinimumWorkedPeriodDays( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'minimum_worked_period_days', $value );
	}

	/**
	 * @return bool|int
	 */
	function getMinimumWorkedDays() {
		return $this->getGenericDataValue( 'minimum_worked_days' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinimumWorkedDays( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'minimum_worked_days', $value );
	}

	/**
	 * @return bool|int
	 */
	function getWorkedScheduledDays() {
		return $this->getGenericDataValue( 'worked_scheduled_days' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setWorkedScheduledDays( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'worked_scheduled_days', $value );
	}

	/**
	 * @return bool|int
	 */
	function getShiftOnHolidayType() {
		return $this->getGenericDataValue( 'shift_on_holiday_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setShiftOnHolidayType( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'shift_on_holiday_type_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getMinimumWorkedAfterPeriodDays() {
		return $this->getGenericDataValue( 'minimum_worked_after_period_days' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinimumWorkedAfterPeriodDays( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'minimum_worked_after_period_days', $value );
	}

	/**
	 * @return bool|int
	 */
	function getMinimumWorkedAfterDays() {
		return $this->getGenericDataValue( 'minimum_worked_after_days' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinimumWorkedAfterDays( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'minimum_worked_after_days', $value );
	}

	/**
	 * @return bool|int
	 */
	function getWorkedAfterScheduledDays() {
		return $this->getGenericDataValue( 'worked_after_scheduled_days' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setWorkedAfterScheduledDays( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'worked_after_scheduled_days', $value );
	}

	/**
	 * @return bool|int
	 */
	function getAverageTimeFrequencyType() {
		return $this->getGenericDataValue( 'average_time_frequency_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAverageTimeFrequencyType( $value ) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'average_time_frequency_type_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getAverageTimeDays() {
		return $this->getGenericDataValue( 'average_time_days' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAverageTimeDays( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'average_time_days', $value );
	}

	/**
	 * This is the divisor in the time averaging formula, as some provinces total time over 30 days and divide by 20 days.
	 * @return bool|int
	 */
	function getAverageDays() {
		return (int)$this->getGenericDataValue( 'average_days' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAverageDays( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'average_days', $value );
	}

	/**
	 * If true, uses only worked days to average time over.
	 * If false, always uses the above average days to average time over.
	 * @return bool
	 */
	function getAverageTimeWorkedDays() {
		return $this->fromBool( $this->getGenericDataValue( 'average_time_worked_days' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAverageTimeWorkedDays( $value) {
		return $this->setGenericDataValue( 'average_time_worked_days', $this->toBool($value) );
	}

	/**
	 * @return bool|int
	 */
	function getMinimumTime() {
		return (int)$this->getGenericDataValue( 'minimum_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinimumTime( $value) {
		$value = trim($value);
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
	function setMaximumTime( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'maximum_time', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getRoundIntervalPolicyID() {
		return $this->getGenericDataValue( 'round_interval_policy_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setRoundIntervalPolicyID( $value) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'round_interval_policy_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getEligibleContributingShiftPolicy() {
		return $this->getGenericDataValue( 'eligible_contributing_shift_policy_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setEligibleContributingShiftPolicy( $value) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'eligible_contributing_shift_policy_id', $value );
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
	function setContributingShiftPolicy( $value) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'contributing_shift_policy_id', $value );
	}

	/**
	 * Count all paid absence time as worked time.
	 * @return bool
	 */
	function getPaidAbsenceAsWorked() {
		return $this->fromBool( $this->getGenericDataValue( 'paid_absence_as_worked' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPaidAbsenceAsWorked( $value) {
		return $this->setGenericDataValue( 'paid_absence_as_worked', $this->toBool($value) );
	}

	/**
	 * Always applies over time policy even if they are not eligible for the holiday.
	 * @return bool
	 */
	function getForceOverTimePolicy() {
		return $this->fromBool( $this->getGenericDataValue( 'force_over_time_policy' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setForceOverTimePolicy( $value) {
		return $this->setGenericDataValue( 'force_over_time_policy', $this->toBool($value) );
	}

	/**
	 * @return bool
	 */
	function getIncludeOverTime() {
		return $this->fromBool( $this->getGenericDataValue( 'include_over_time' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setIncludeOverTime( $value) {
		return $this->setGenericDataValue( 'include_over_time', $this->toBool($value) );
	}

	/**
	 * @return bool
	 */
	function getIncludePaidAbsenceTime() {
		return $this->fromBool( $this->getGenericDataValue( 'include_paid_absence_time' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setIncludePaidAbsenceTime( $value) {
		return $this->setGenericDataValue( 'include_paid_absence_time', $this->toBool($value) );
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
	function setAbsencePolicyID( $value) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'absence_policy_id', $value );
	}

	/**
	 * @return array|bool
	 */
	function getRecurringHoliday() {
		$hprhlf = TTnew( 'HolidayPolicyRecurringHolidayListFactory' );
		$hprhlf->getByHolidayPolicyId( $this->getId() );
		Debug::text('Found Recurring Holidays Attached to this Policy: '. $hprhlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

		$list = array();
		foreach ($hprhlf as $obj) {
			$list[] = $obj->getRecurringHoliday();
		}

		if ( empty($list) == FALSE ) {
			return $list;
		}

		return FALSE;
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setRecurringHoliday( $ids) {
		Debug::text('Setting Recurring Holiday IDs : ', __FILE__, __LINE__, __METHOD__, 10);
		if (is_array($ids) AND count($ids) > 0) {
			$tmp_ids = array();
			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$hprhlf = TTnew( 'HolidayPolicyRecurringHolidayListFactory' );
				$hprhlf->getByHolidayPolicyId( $this->getId() );

				foreach ($hprhlf as $obj) {
					$id = $obj->getRecurringHoliday();
					Debug::text('Policy ID: '. $obj->getHolidayPolicy() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array($id, $ids) ) {
						Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset($id, $obj);
			}

			//Insert new mappings.
			$rhlf = TTnew( 'RecurringHolidayListFactory' );

			foreach ($ids as $id) {
				if ( isset($ids) AND !in_array($id, $tmp_ids) AND TTUUID::isUUID( $id ) AND $id != TTUUID::getZeroID() AND $id != TTUUID::getNotExistID() ) {
					$hprhf = TTnew( 'HolidayPolicyRecurringHolidayFactory' );
					$hprhf->setHolidayPolicy( $this->getId() );
					$hprhf->setRecurringHoliday( $id );


					$rhlf->getById( $id );
					if ( $rhlf->getRecordCount() > 0 ) {
						$obj = $rhlf->getCurrent();

						if ($this->Validator->isTrue(		'recurring_holiday',
															 $hprhf->isValid(),
															 TTi18n::gettext('Selected Recurring Holiday is invalid').' ('. $obj->getName() .')' )
						) {
							$hprhf->save();
						}
					}
				}
			}

			return TRUE;
		}

		Debug::text('No User IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
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
		// Company
		$clf = TTnew( 'CompanyListFactory' );
		$this->Validator->isResultSetWithRows(	'company',
														$clf->getByID($this->getCompany()),
														TTi18n::gettext('Company is invalid')
													);
		// Type
		if ( $this->getType() !== FALSE ) {
			$this->Validator->inArrayKey(	'type',
													$this->getType(),
													TTi18n::gettext('Incorrect Type'),
													$this->getOptions('type')
												);
		}
		// Name
		if ( $this->Validator->getValidateOnly() == FALSE ) { //Don't check the below when mass editing.
			if ( $this->getName() == '' ) {
				$this->Validator->isTRUE(	'name',
											FALSE,
											TTi18n::gettext('Please specify a name') );
			}
		}
		if ( $this->getName() != '' AND $this->Validator->isError('name') == FALSE ) {
			$this->Validator->isLength(	'name',
												$this->getName(),
												TTi18n::gettext('Name is too short or too long'),
												2, 50
											);
		}
		if ( $this->getName() != '' AND $this->Validator->isError('name') == FALSE ) {
			$this->Validator->isTrue(	'name',
												$this->isUniqueName($this->getName()),
												TTi18n::gettext('Name is already in use')
											);
		}
		// Description
		if ( $this->getDescription() != '' ) {
			$this->Validator->isLength(	'description',
												$this->getDescription(),
												TTi18n::gettext('Description is invalid'),
												1, 250
											);
		}
		// Default Schedule Status
		if ( $this->getDefaultScheduleStatus() !== FALSE ) {
			$sf = TTnew( 'ScheduleFactory' );
			$this->Validator->inArrayKey(	'default_schedule_status',
													$this->getDefaultScheduleStatus(),
													TTi18n::gettext('Incorrect Default Schedule Status'),
													$sf->getOptions('status')
												);
		}
		// Minimum Employed days
		if ( $this->getMinimumEmployedDays() !== FALSE ) {
			$this->Validator->isNumeric(		'minimum_employed_days',
														$this->getMinimumEmployedDays(),
														TTi18n::gettext('Incorrect Minimum Employed days')
													);
		}

		// Minimum Worked Period days
		if ( $this->getMinimumWorkedPeriodDays() !== FALSE ) {
			$this->Validator->isNumeric(		'minimum_worked_period_days',
														$this->getMinimumWorkedPeriodDays(),
														TTi18n::gettext('Incorrect Minimum Worked Period days')
													);
		}
		// Minimum Worked days
		if ( $this->getMinimumWorkedDays() !== FALSE ) {
			$this->Validator->isNumeric(		'minimum_worked_days',
														$this->getMinimumWorkedDays(),
														TTi18n::gettext('Incorrect Minimum Worked days')
													);
		}

		// Eligibility Type
		if ( $this->getWorkedScheduledDays() !== FALSE ) {
			$this->Validator->isNumeric(		'minimum_worked_period_days',
														$this->getWorkedScheduledDays(),
														TTi18n::gettext('Incorrect Eligibility Type')
													);
		}

		// On Holiday Eligibility Type
		if ( $this->getShiftOnHolidayType() !== FALSE ) {
			$this->Validator->isNumeric(		'shift_on_holiday_type_id',
														$this->getShiftOnHolidayType(),
														TTi18n::gettext('Incorrect On Holiday Eligibility Type')
													);
		}

		// Minimum Worked After Period days
		if ( $this->getMinimumWorkedAfterPeriodDays() !== FALSE ) {
			$this->Validator->isNumeric(		'minimum_worked_after_period_days',
														$this->getMinimumWorkedAfterPeriodDays(),
														TTi18n::gettext('Incorrect Minimum Worked After Period days')
													);
		}

		// Minimum Worked After days
		if ( $this->getMinimumWorkedAfterDays() !== FALSE ) {
			$this->Validator->isNumeric(		'minimum_worked_after_days',
														$this->getMinimumWorkedAfterDays(),
														TTi18n::gettext('Incorrect Minimum Worked After days')
													);
		}

		// Eligibility Type
		if ( $this->getWorkedAfterScheduledDays() !== FALSE ) {
			$this->Validator->isNumeric(		'minimum_worked_after_period_days',
														$this->getWorkedAfterScheduledDays(),
														TTi18n::gettext('Incorrect Eligibility Type')
													);
		}

		// Days to Total Time over
		if ( $this->getAverageTimeDays() !== FALSE ) {
			$this->Validator->isNumeric(		'average_time_days',
														$this->getAverageTimeDays(),
														TTi18n::gettext('Incorrect Days to Total Time over')
													);
		}
		// Average Time Over Frequency Type
		if ( $this->getAverageTimeFrequencyType() !== FALSE ) {
			$this->Validator->inArrayKey(	'average_time_frequency_type_id',
											 $this->getAverageTimeFrequencyType(),
											 TTi18n::gettext('Incorrect Total Time Over Frequency'),
											 $this->getOptions('average_time_frequency_type')
			);
		}

		// Days to Average Time over
		$this->Validator->isNumeric(		'average_days',
													$this->getAverageDays(),
													TTi18n::gettext('Incorrect Days to Average Time over')
												);
		// Minimum Time
		$this->Validator->isNumeric(		'minimum_time',
													$this->getMinimumTime(),
													TTi18n::gettext('Incorrect Minimum Time')
												);
		// Maximum Time
		if ( $this->getMaximumTime() !== FALSE ) {
			$this->Validator->isNumeric(		'maximum_time',
													$this->getMaximumTime(),
													TTi18n::gettext('Incorrect Maximum Time')
												);
		}
		// Rounding Policy
		if ( $this->getRoundIntervalPolicyID() !== FALSE AND $this->getRoundIntervalPolicyID() != TTUUID::getZeroID() ) {
			$riplf = TTnew( 'RoundIntervalPolicyListFactory' );
			$this->Validator->isResultSetWithRows(	'round_interval_policy',
															$riplf->getByID($this->getRoundIntervalPolicyID()),
															TTi18n::gettext('Rounding Policy is invalid')
														);
		}
		// Eligible Contributing Shift Policy
		if ( $this->getEligibleContributingShiftPolicy() !== FALSE AND $this->getEligibleContributingShiftPolicy() != TTUUID::getZeroID() ) {
			$csplf = TTnew( 'ContributingShiftPolicyListFactory' );
			$this->Validator->isResultSetWithRows(	'eligible_contributing_shift_policy_id',
															$csplf->getByID($this->getEligibleContributingShiftPolicy()),
															TTi18n::gettext('Eligible Contributing Shift Policy is invalid')
														);
		}
		// Contributing Shift Policy
		if ( $this->getContributingShiftPolicy() !== FALSE AND $this->getContributingShiftPolicy() != TTUUID::getZeroID() ) {
			$csplf = TTnew( 'ContributingShiftPolicyListFactory' );
			$this->Validator->isResultSetWithRows(	'contributing_shift_policy_id',
															$csplf->getByID($this->getContributingShiftPolicy()),
															TTi18n::gettext('Contributing Shift Policy is invalid')
														);
		}
		// Absence Policy
		if ( $this->getAbsencePolicyID() !== FALSE AND $this->getAbsencePolicyID() != TTUUID::getZeroID() ) {
			$aplf = TTnew( 'AbsencePolicyListFactory' );
			$this->Validator->isResultSetWithRows(	'absence_policy_id',
															$aplf->getByID($this->getAbsencePolicyID()),
															TTi18n::gettext('Absence Policy is invalid')
														);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->getDeleted() != TRUE AND $this->Validator->getValidateOnly() == FALSE ) { //Don't check the below when mass editing.
			if ( $this->getAbsencePolicyID() == TTUUID::getZeroID() ) {
				$this->Validator->isTrue(		'absence_policy_id',
												FALSE,
												TTi18n::gettext('Please specify an Absence Policy') );
			}
		}

		if ( $this->getDeleted() == TRUE ) {
			//Check to make sure nothing else references this policy, so we can be sure its okay to delete it.
			$pglf = TTnew( 'PolicyGroupListFactory' );
			$pglf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), array('holiday_policy' => $this->getId() ), 1 );
			if ( $pglf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  FALSE,
										  TTi18n::gettext( 'This policy is currently in use' ) . ' ' . TTi18n::gettext( 'by policy groups' ) );
			}
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
						case 'type':
						case 'default_schedule_status':
							$function = 'get'.str_replace('_', '', $variable);
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
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Holiday Policy'), NULL, $this->getTable(), $this );
	}
}
?>
