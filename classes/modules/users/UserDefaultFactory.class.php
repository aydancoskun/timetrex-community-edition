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
 * @package Modules\Users
 */
class UserDefaultFactory extends Factory {
	protected $table = 'user_default';
	protected $pk_sequence_name = 'user_default_id_seq'; //PK Sequence name

	protected $company_obj = NULL;
	protected $title_obj = NULL;

	protected $city_validator_regex = '/^[a-zA-Z0-9-,_\.\'#\ |\x{0080}-\x{FFFF}]{1,250}$/iu';

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
						'-1090-title' => TTi18n::gettext('Title'),
						'-1102-default_branch' => TTi18n::gettext('Branch'),
						'-1103-default_department' => TTi18n::gettext('Department'),
						'-1104-default_job' => TTi18n::gettext('Job'),
						'-1105-default_job_item' => TTi18n::gettext('Task'),
						'-1106-currency' => TTi18n::gettext('Currency'),

						'-1108-permission_control' => TTi18n::gettext('Permission Group'),
						'-1110-pay_period_schedule' => TTi18n::gettext('Pay Period Schedule'),
						'-1112-policy_group' => TTi18n::gettext('Policy Group'),


						'-1150-city' => TTi18n::gettext('City'),
						'-1160-province' => TTi18n::gettext('Province/State'),
						'-1170-country' => TTi18n::gettext('Country'),
						'-1190-work_phone' => TTi18n::gettext('Work Phone'),
						'-1191-work_phone_ext' => TTi18n::gettext('Work Phone Ext'),
						'-1240-work_email' => TTi18n::gettext('Work Email'),
						'-2000-created_by' => TTi18n::gettext('Created By'),
						'-2010-created_date' => TTi18n::gettext('Created Date'),
						'-2020-updated_by' => TTi18n::gettext('Updated By'),
						'-2030-updated_date' => TTi18n::gettext('Updated Date'),
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
											'legal_entity_id' => 'LegalEntity',
											'permission_control_id' => 'PermissionControl',
											'pay_period_schedule_id' => 'PayPeriodSchedule',
											'policy_group_id' => 'PolicyGroup',
											'employee_number' => 'EmployeeNumber',
											'title_id' => 'Title',
											'default_branch_id' => 'DefaultBranch',
											'default_department_id' => 'DefaultDepartment',
											'currency_id' => 'Currency',
											'city' => 'City',
											'country' => 'Country',
											'province' => 'Province',
											'work_phone' => 'WorkPhone',
											'work_phone_ext' => 'WorkPhoneExt',
											'work_email' => 'WorkEmail',
											'hire_date' => 'HireDate',
											'language' => 'Language',
											'date_format' => 'DateFormat',
											'time_format' => 'TimeFormat',
											'time_zone' => 'TimeZone',
											'time_unit_format' => 'TimeUnitFormat',
											'distance_format' => 'DistanceFormat',
											'items_per_page' => 'ItemsPerPage',
											'start_week_day' => 'StartWeekDay',
											'enable_email_notification_exception' => 'EnableEmailNotificationException',
											'enable_email_notification_message' => 'EnableEmailNotificationMessage',
											'enable_email_notification_pay_stub' => 'EnableEmailNotificationPayStub',
											'enable_email_notification_home' => 'EnableEmailNotificationHome',
											'company_deduction' => 'CompanyDeduction',
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
	function getTitleObject() {
		return $this->getGenericObject( 'UserTitleListFactory', $this->getTitle(), 'title_obj' );
	}

	/**
	 * @param string $company_id UUID
	 * @return bool
	 */
	function isUniqueCompany( $company_id) {
		$ph = array(
					'company_id' => TTUUID::castUUID($company_id),
					);

		$query = 'select id from '. $this->getTable() .' where company_id = ? AND deleted=0';
		$unique_company_id = $this->db->GetOne($query, $ph);
		Debug::Arr($unique_company_id, 'Unique Company: '. $this->getID(), __FILE__, __LINE__, __METHOD__, 10);

		if ( $unique_company_id === FALSE ) {
			return TRUE;
		} else {
			if ($unique_company_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
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
	function getLegalEntity() {
		return $this->getGenericDataValue( 'legal_entity_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setLegalEntity( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text('Legal Entity ID: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		return $this->setGenericDataValue( 'legal_entity_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPermissionControl() {
		return $this->getGenericDataValue( 'permission_control_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPermissionControl( $value ) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'permission_control_id', $value );
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
	function setPayPeriodSchedule( $value ) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'pay_period_schedule_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPolicyGroup() {
		return $this->getGenericDataValue( 'policy_group_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPolicyGroup( $value ) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'policy_group_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getEmployeeNumber() {
		return $this->getGenericDataValue( 'employee_number' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEmployeeNumber( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'employee_number', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getTitle() {
		return $this->getGenericDataValue( 'title_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setTitle( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text('Title ID: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		return $this->setGenericDataValue( 'title_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDefaultBranch() {
		return $this->getGenericDataValue( 'default_branch_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setDefaultBranch( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text('Branch ID: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		return $this->setGenericDataValue( 'default_branch_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDefaultDepartment() {
		return $this->getGenericDataValue( 'default_department_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setDefaultDepartment( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text('Department ID: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		return $this->setGenericDataValue( 'default_department_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCurrency() {
		return $this->getGenericDataValue( 'currency_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setCurrency( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text('Currency ID: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		return $this->setGenericDataValue( 'currency_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCity() {
		return $this->getGenericDataValue( 'city' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCity( $value ) {
		$value = trim($value);
		return $this->setGenericDataValue( 'city', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCountry() {
		return $this->getGenericDataValue( 'country' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCountry( $value ) {
		$value = trim($value);
		return $this->setGenericDataValue( 'country', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getProvince() {
		return $this->getGenericDataValue( 'province' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setProvince( $value  ) {
		$value = trim($value);
		Debug::Text('Country: '. $this->getCountry() .' Province: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		//If country isn't set yet, accept the value and re-validate on save.
		return $this->setGenericDataValue( 'province', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getWorkPhone() {
		return $this->getGenericDataValue( 'work_phone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setWorkPhone( $value ) {
		$value = trim($value);
		return $this->setGenericDataValue( 'work_phone', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getWorkPhoneExt() {
		return $this->getGenericDataValue( 'work_phone_ext' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setWorkPhoneExt( $value ) {
		$value = $this->Validator->stripNonNumeric( trim($value) );
		return $this->setGenericDataValue( 'work_phone_ext', $value );

	}

	/**
	 * @return bool|mixed
	 */
	function getWorkEmail() {
		return $this->getGenericDataValue( 'work_email' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setWorkEmail( $value ) {
		$value = trim($value);
		return $this->setGenericDataValue( 'work_email', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getHireDate() {
		return $this->getGenericDataValue( 'hire_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setHireDate( $value ) {
		return $this->setGenericDataValue( 'hire_date', $value );
	}

	/*

		User Preferences

	*/
	/**
	 * @return bool|mixed
	 */
	function getLanguage() {
		return $this->getGenericDataValue( 'language' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLanguage( $value ) {
		$value = trim($value);
		return $this->setGenericDataValue( 'language', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDateFormat() {
		return $this->getGenericDataValue( 'date_format' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setDateFormat( $value ) {
		$value = trim($value);
		return $this->setGenericDataValue( 'date_format', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getTimeFormat() {
		return $this->getGenericDataValue( 'time_format' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTimeFormat( $value ) {
		$value = trim($value);
		return $this->setGenericDataValue( 'time_format', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getTimeZone() {
		return $this->getGenericDataValue( 'time_zone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTimeZone( $value ) {
		$value = Misc::trimSortPrefix( trim($value) );
		return $this->setGenericDataValue( 'time_zone', $value );
	}

	/**
	 * @return mixed
	 */
	function getTimeUnitFormatExample() {
		$options = $this->getOptions('time_unit_format');

		return $options[$this->getTimeUnitFormat()];
	}

	/**
	 * @return bool|mixed
	 */
	function getTimeUnitFormat() {
		return $this->getGenericDataValue( 'time_unit_format' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTimeUnitFormat( $value ) {
		$value = trim($value);
		return $this->setGenericDataValue( 'time_unit_format', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDistanceFormat() {
		return $this->getGenericDataValue( 'distance_format' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDistanceFormat( $value ) {
		$value = trim($value);
		return $this->setGenericDataValue( 'distance_format', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getItemsPerPage() {
		return $this->getGenericDataValue( 'items_per_page' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setItemsPerPage( $value ) {
		$value = trim($value);
		return $this->setGenericDataValue( 'items_per_page', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getStartWeekDay() {
		return $this->getGenericDataValue( 'start_week_day' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStartWeekDay( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'start_week_day', $value );
	}

	/**
	 * @return bool
	 */
	function getEnableEmailNotificationException() {
		return $this->fromBool( $this->getGenericDataValue( 'enable_email_notification_exception' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableEmailNotificationException( $value ) {
		return $this->setGenericDataValue( 'enable_email_notification_exception', $this->toBool($value) );
	}

	/**
	 * @return bool
	 */
	function getEnableEmailNotificationMessage() {
		return $this->fromBool( $this->getGenericDataValue( 'enable_email_notification_message' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableEmailNotificationMessage( $value) {
		return $this->setGenericDataValue( 'enable_email_notification_message', $this->toBool($value) );
	}

	/**
	 * @return bool
	 */
	function getEnableEmailNotificationPayStub() {
		return $this->fromBool( $this->getGenericDataValue( 'enable_email_notification_pay_stub' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableEmailNotificationPayStub( $value) {
		return $this->setGenericDataValue( 'enable_email_notification_pay_stub', $this->toBool($value) );
	}

	/**
	 * @return bool
	 */
	function getEnableEmailNotificationHome() {
		return $this->fromBool( $this->getGenericDataValue( 'enable_email_notification_home' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableEmailNotificationHome( $value) {
		return $this->setGenericDataValue( 'enable_email_notification_home', $this->toBool($value) );
	}

	/*

		Company Deductions

	*/
	/**
	 * @return array|bool
	 */
	function getCompanyDeduction() {
		$udcdlf = TTnew( 'UserDefaultCompanyDeductionListFactory' ); /** @var UserDefaultCompanyDeductionListFactory $udcdlf */
		$udcdlf->getByUserDefaultId( $this->getId() );

		$list = array();
		foreach ($udcdlf as $obj) {
			$list[] = $obj->getCompanyDeduction();
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
	function setCompanyDeduction( $ids) {
		Debug::text('Setting Company Deduction IDs : ', __FILE__, __LINE__, __METHOD__, 10);

		if ( $ids == '' ) {
			$ids = array(); //This is for the API, it sends FALSE when no branches are selected, so this will delete all branches.
		}

		if ( is_array($ids) ) {
			$tmp_ids = array();
			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$udcdlf = TTnew( 'UserDefaultCompanyDeductionListFactory' ); /** @var UserDefaultCompanyDeductionListFactory $udcdlf */
				$udcdlf->getByUserDefaultId( $this->getId() );
				foreach ($udcdlf as $obj) {
					$id = $obj->getCompanyDeduction();
					Debug::text('ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

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
			//$lf = TTnew( 'UserListFactory' );
			$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */

			foreach ($ids as $id) {
				if ( $id != FALSE AND isset($ids) AND !in_array($id, $tmp_ids) ) {
					$udcdf = TTnew( 'UserDefaultCompanyDeductionFactory' ); /** @var UserDefaultCompanyDeductionFactory $udcdf */
					$udcdf->setUserDefault( $this->getId() );
					$udcdf->setCompanyDeduction( $id );

					$obj = $cdlf->getById( $id )->getCurrent();

					if ($this->Validator->isTrue(		'company_deduction',
														$udcdf->Validator->isValid(),
														TTi18n::gettext('Deduction is invalid').' ('. $obj->getName() .')' )) {
						$udcdf->save();
					}
				}
			}

			return TRUE;
		}

		Debug::text('No IDs to set.', __FILE__, __LINE__, __METHOD__, 10);
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
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$this->Validator->isResultSetWithRows(	'company',
													$clf->getByID($this->getCompany()),
													TTi18n::gettext('Company is invalid')
												);
		if ( $this->Validator->isError('company') == FALSE ) {
			$this->Validator->isTrue(		'company',
													$this->isUniqueCompany($this->getCompany()),
													TTi18n::gettext('Default settings for this company already exist')
												);
		}
		// Legal entity
		$clf = TTnew( 'LegalEntityListFactory' ); /** @var LegalEntityListFactory $clf */
		$this->Validator->isResultSetWithRows(	'legal_entity_id',
														$clf->getByID($this->getLegalEntity()),
														TTi18n::gettext('Legal entity is invalid')
													);
		// Permission Group
		if ( $this->getPermissionControl() != '' AND $this->getPermissionControl() != TTUUID::getZeroID() ) {
			$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
			$this->Validator->isResultSetWithRows( 'permission_control_id',
												   $pclf->getByID( $this->getPermissionControl() ),
												   TTi18n::gettext( 'Permission Group is invalid' )
			);
		}

		// Pay Period schedule
		if ( $this->getPayPeriodSchedule() != '' AND $this->getPayPeriodSchedule() != TTUUID::getZeroID() ) {
			$ppslf = TTnew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $ppslf */
			$this->Validator->isResultSetWithRows(	'pay_period_schedule_id',
															$ppslf->getByID($this->getPayPeriodSchedule()),
															TTi18n::gettext('Pay Period schedule is invalid')
														);
		}
		// Policy Group
		if ( $this->getPolicyGroup() != '' AND $this->getPolicyGroup() != TTUUID::getZeroID() ) {
			$pglf = TTnew( 'PolicyGroupListFactory' ); /** @var PolicyGroupListFactory $pglf */
			$this->Validator->isResultSetWithRows(	'policy_group_id',
															$pglf->getByID($this->getPolicyGroup()),
															TTi18n::gettext('Policy Group is invalid')
														);
		}
		// Employee number
		if ( $this->getEmployeeNumber() != '' ) {
			$this->Validator->isLength(		'employee_number',
													$this->getEmployeeNumber(),
													TTi18n::gettext('Employee number is too short or too long'),
													1,
													100
												);
		}
		// Title
		if ( $this->getTitle() != '' AND $this->getTitle() != TTUUID::getZeroID() ) {
			$utlf = TTnew( 'UserTitleListFactory' ); /** @var UserTitleListFactory $utlf */
			$this->Validator->isResultSetWithRows(	'title',
															$utlf->getByID($this->getTitle()),
															TTi18n::gettext('Title is invalid')
														);
		}
		// Default Branch
		if ( $this->getDefaultBranch() != '' AND $this->getDefaultBranch() != TTUUID::getZeroID() ) {
			$blf = TTnew( 'BranchListFactory' ); /** @var BranchListFactory $blf */
			$this->Validator->isResultSetWithRows(	'default_branch',
															$blf->getByID($this->getDefaultBranch()),
															TTi18n::gettext('Invalid Default Branch')
														);
		}
		// Default Department
		if ( $this->getDefaultDepartment() != '' AND $this->getDefaultDepartment() != TTUUID::getZeroID() ) {
			$dlf = TTnew( 'DepartmentListFactory' ); /** @var DepartmentListFactory $dlf */
			$this->Validator->isResultSetWithRows(	'default_department',
															$dlf->getByID($this->getDefaultDepartment()),
															TTi18n::gettext('Invalid Default Department')
														);
		}
		// Currency
		if ( $this->getCurrency() != '' AND $this->getCurrency() != TTUUID::getZeroID() ) {
			$culf = TTnew( 'CurrencyListFactory' ); /** @var CurrencyListFactory $culf */
			$this->Validator->isResultSetWithRows( 'currency',
												   $culf->getByID( $this->getCurrency() ),
												   TTi18n::gettext( 'Invalid Currency' )
			);
		}
		// City
		if ( $this->getCity() != '' ) {
			$this->Validator->isRegEx(		'city',
													$this->getCity(),
													TTi18n::gettext('City contains invalid characters'),
													$this->city_validator_regex
												);
			if ( $this->Validator->isError('city') == FALSE ) {
				$this->Validator->isLength(		'city',
														$this->getCity(),
														TTi18n::gettext('City name is too short or too long'),
														2,
														250
													);
			}
		}
		// Country
		$cf = TTnew( 'CompanyFactory' ); /** @var CompanyFactory $cf */
		$this->Validator->inArrayKey(		'country',
													$this->getCountry(),
													TTi18n::gettext('Invalid Country'),
													$cf->getOptions('country')
												);
		// Province/State
		if ( $this->getCountry() !== FALSE ) {
			$options_arr = $cf->getOptions('province');
			if ( isset($options_arr[$this->getCountry()]) ) {
				$options = $options_arr[$this->getCountry()];
			} else {
				$options = array();
			}
			$this->Validator->inArrayKey(	'province',
													$this->getProvince(),
													TTi18n::gettext('Invalid Province/State'),
													$options
												);
		}
		// Work phone
		if ( $this->getWorkPhone() != '' ) {
			$this->Validator->isPhoneNumber(		'work_phone',
															$this->getWorkPhone(),
															TTi18n::gettext('Work phone number is invalid')
														);
		}
		// Work phone number extension
		if ( $this->getWorkPhoneExt() != '' ) {
			$this->Validator->isLength(		'work_phone_ext',
													$this->getWorkPhoneExt(),
													TTi18n::gettext('Work phone number extension is too short or too long'),
													2,
													10
												);
		}
		// Work Email address
		if ( $this->getWorkEmail() != '' ) {
			$this->Validator->isEmail(	'work_email',
												$this->getWorkEmail(),
												TTi18n::gettext('Work Email address is invalid')
											);
		}
		// Hire date
		if ( $this->getHireDate() != '' ) {
			$this->Validator->isDate(		'hire_date',
													$this->getHireDate(),
													TTi18n::gettext('Hire date is invalid')
												);
		}
		// Language
		$language_options = TTi18n::getLanguageArray();
		$this->Validator->inArrayKey(	'language',
												$this->getLanguage(),
												TTi18n::gettext('Incorrect language'),
												$language_options
											);
		// Date format
		$upf = TTnew( 'UserPreferenceFactory' ); /** @var UserPreferenceFactory $upf */
		$this->Validator->inArrayKey(	'date_format',
												$this->getDateFormat(),
												TTi18n::gettext('Incorrect date format'),
												Misc::trimSortPrefix( $upf->getOptions('date_format') )
											);
		// Time format
		$this->Validator->inArrayKey(	'time_format',
												$this->getTimeFormat(),
												TTi18n::gettext('Incorrect time format'),
												$upf->getOptions('time_format')
											);
		// Time zone
		$this->Validator->inArrayKey(	'time_zone',
												$this->getTimeZone(),
												TTi18n::gettext('Incorrect time zone'),
												Misc::trimSortPrefix( $upf->getOptions('time_zone') )
											);
		// time units
		$this->Validator->inArrayKey(	'time_unit_format',
												$this->getTimeUnitFormat(),
												TTi18n::gettext('Incorrect time units'),
												$upf->getOptions('time_unit_format')
											);
		// Distance units
		$this->Validator->inArrayKey(	'distance_format',
												$this->getDistanceFormat(),
												TTi18n::gettext('Incorrect distance units'),
												$upf->getOptions('distance_format')
											);
		// Items per page
		if ( $this->getItemsPerPage() == '' OR $this->getItemsPerPage() < 1 OR $this->getItemsPerPage() > 200 ) {
			$this->Validator->isTrue(		'items_per_page',
											FALSE,
											TTi18n::gettext('Items per page must be between 10 and 200')
										);
		}
		// Day to start a week on
		$this->Validator->inArrayKey(	'start_week_day',
												$this->getStartWeekDay(),
												TTi18n::gettext('Incorrect day to start a week on'),
												$upf->getOptions('start_week_day')
											);

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->getCompany() == FALSE ) {
			$this->Validator->isTrue(		'company',
											FALSE,
											TTi18n::gettext('Company is invalid'));
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		return TRUE;
	}

	/**
	 * Support setting created_by, updated_by especially for importing data.
	 * Make sure data is set based on the getVariableToFunctionMap order.
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
						case 'hire_date':
							$this->setHireDate( TTDate::parseDateTime( $data['hire_date'] ) );
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
						case 'hire_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getHireDate() );
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Employee Default Information'), NULL, $this->getTable(), $this );
	}

}
?>
