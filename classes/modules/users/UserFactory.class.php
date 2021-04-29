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
 * @package Modules\Users
 */
class UserFactory extends Factory {
	protected $table = 'users';
	protected $pk_sequence_name = 'users_id_seq'; //PK Sequence name

	protected $permission_obj = null;
	protected $user_preference_obj = null;
	protected $user_tax_obj = null;
	protected $legal_entity_obj = null;
	protected $company_obj = null;
	protected $title_obj = null;
	protected $branch_obj = null;
	protected $department_obj = null;
	protected $group_obj = null;
	protected $currency_obj = null;

	public $username_validator_regex = '/^[a-z0-9-_\.@\+]{1,250}$/i'; //Authentication class needs to access this.
	public $phoneid_validator_regex = '/^[0-9]{1,250}$/i';
	protected $phonepassword_validator_regex = '/^[0-9]{1,250}$/i';
	protected $name_validator_regex = '/^[a-zA-Z- ,\.\'()\[\]|\x{0080}-\x{FFFF}]{1,250}$/iu'; //Allow ()/[] so nicknames can be specified. Allow "," so names can be: Doe, Jr. or: Doe, III
	protected $address_validator_regex = '/^[a-zA-Z0-9-,_\/\.\'#\ |\x{0080}-\x{FFFF}]{1,250}$/iu';
	protected $city_validator_regex = '/^[a-zA-Z0-9-,_\.\'#\ |\x{0080}-\x{FFFF}]{1,250}$/iu';

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {

		$retval = null;
		switch ( $name ) {
			case 'status':
				$retval = [
					//Add System users (for APIs and reseller admin accounts)
					//Add "New Hire" status for employees going through the onboarding process or newly imported employees.
					10 => TTi18n::gettext( 'Active' ),
					11 => TTi18n::gettext( 'Inactive' ), //Add option that isn't terminated/leave but is still not billed/active.
					12 => TTi18n::gettext( 'Leave - Illness/Injury' ),
					14 => TTi18n::gettext( 'Leave - Maternity/Parental' ),
					16 => TTi18n::gettext( 'Leave - Other' ),
					20 => TTi18n::gettext( 'Terminated' ),
				];
				break;
			case 'sex':
				$retval = [
						5  => TTi18n::gettext( 'Unspecified' ),
						10 => TTi18n::gettext( 'Male' ),
						20 => TTi18n::gettext( 'Female' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1005-company'         => TTi18n::gettext( 'Company' ),
						'-1008-legal_name'      => TTi18n::getText( 'Legal Entity Name' ),
						'-1010-employee_number' => TTi18n::gettext( 'Employee #' ),
						'-1020-status'          => TTi18n::gettext( 'Status' ),
						'-1030-user_name'       => TTi18n::gettext( 'User Name' ),
						'-1040-phone_id'        => TTi18n::gettext( 'Quick Punch ID' ),

						'-1060-first_name'  => TTi18n::gettext( 'First Name' ),
						'-1070-middle_name' => TTi18n::gettext( 'Middle Name' ),
						'-1080-last_name'   => TTi18n::gettext( 'Last Name' ),
						'-1082-full_name'   => TTi18n::gettext( 'Full Name' ),

						'-1090-title'              => TTi18n::gettext( 'Title' ),
						'-1099-user_group'         => TTi18n::gettext( 'Group' ), //Update ImportUser class if sort order is changed for this.
						'-1100-ethnic_group'       => TTi18n::gettext( 'Ethnicity' ),
						'-1102-default_branch'     => TTi18n::gettext( 'Branch' ),
						'-1103-default_department' => TTi18n::gettext( 'Department' ),
						'-1106-currency'           => TTi18n::gettext( 'Currency' ),

						'-1108-permission_control'  => TTi18n::gettext( 'Permission Group' ),
						'-1110-pay_period_schedule' => TTi18n::gettext( 'Pay Period Schedule' ),
						'-1112-policy_group'        => TTi18n::gettext( 'Policy Group' ),

						'-1120-sex' => TTi18n::gettext( 'Gender' ),

						'-1130-address1' => TTi18n::gettext( 'Address 1' ),
						'-1140-address2' => TTi18n::gettext( 'Address 2' ),

						'-1150-city'                      => TTi18n::gettext( 'City' ),
						'-1160-province'                  => TTi18n::gettext( 'Province/State' ),
						'-1170-country'                   => TTi18n::gettext( 'Country' ),
						'-1180-postal_code'               => TTi18n::gettext( 'Postal Code' ),
						'-1190-work_phone'                => TTi18n::gettext( 'Work Phone' ),
						'-1191-work_phone_ext'            => TTi18n::gettext( 'Work Phone Ext' ),
						'-1200-home_phone'                => TTi18n::gettext( 'Home Phone' ),
						'-1210-mobile_phone'              => TTi18n::gettext( 'Mobile Phone' ),
						'-1220-fax_phone'                 => TTi18n::gettext( 'Fax Phone' ),
						'-1230-home_email'                => TTi18n::gettext( 'Home Email' ),
						'-1240-work_email'                => TTi18n::gettext( 'Work Email' ),
						'-1250-birth_date'                => TTi18n::gettext( 'Birth Date' ),
						'-1251-birth_date_age'            => TTi18n::gettext( 'Age' ),
						'-1260-hire_date'                 => TTi18n::gettext( 'Hire Date' ),
						'-1261-hire_date_age'             => TTi18n::gettext( 'Length of Service' ),
						'-1270-termination_date'          => TTi18n::gettext( 'Termination Date' ),
						'-1280-sin'                       => TTi18n::gettext( 'SIN/SSN' ),
						'-1290-note'                      => TTi18n::gettext( 'Note' ),
						'-1300-tag'                       => TTi18n::gettext( 'Tags' ),
						'-1400-hierarchy_control_display' => TTi18n::gettext( 'Hierarchy' ),
						'-1401-hierarchy_level_display'   => TTi18n::gettext( 'Hierarchy Superiors' ),
						'-1500-last_login_date'           => TTi18n::gettext( 'Last Login Date' ),
						'-1510-max_punch_time_stamp'      => TTi18n::gettext( 'Last Punch Time' ),

						'-1600-enable_login'                  => TTi18n::gettext( 'Login Enabled' ),
						'-1610-login_expire_date'             => TTi18n::gettext( 'Login Expires' ),
						'-1620-terminated_permission_control' => TTi18n::gettext( 'Terminated Permission Group' ),

						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];

				if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$retval = array_merge( [
												   '-1104-default_job'      => TTi18n::gettext( 'Job' ),
												   '-1105-default_job_item' => TTi18n::gettext( 'Task' ),
										   ],
										   $retval
					);
					ksort( $retval );
				}
				break;
			case 'user_secure_columns': //Regular employee secure columns (Used in MessageFactory)
				$retval = [
						'first_name',
						'middle_name',
						'last_name',
				];
				$retval = Misc::arrayIntersectByKey( $retval, Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'user_child_secure_columns': //Superior employee secure columns (Used in MessageFactory)
				$retval = [
						'first_name',
						'middle_name',
						'last_name',
						'title',
						'user_group',
						'default_branch',
						'default_department',
				];
				$retval = Misc::arrayIntersectByKey( $retval, Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'status',
						'employee_number',
						'first_name',
						'last_name',
						'home_phone',
				];
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [
						'user_name',
						'phone_id',
						'employee_number',
						'sin',
				];
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = [
						'country',
						'province',
						'postal_code',
				];
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
				'id'                           => 'ID',
				'company_id'                   => 'Company',
				'company'                      => false,
				'legal_entity_id'              => 'LegalEntity',
				'legal_name'                   => false,
				'status_id'                    => 'Status',
				'status'                       => false,
				'group_id'                     => 'Group',
				'user_group'                   => false,
				'ethnic_group_id'              => 'EthnicGroup',
				'ethnic_group'                 => false,
				'user_name'                    => 'UserName',
				'phone_id'                     => 'PhoneId',
				'employee_number'              => 'EmployeeNumber',
				'title_id'                     => 'Title',
				'title'                        => false,
				'default_branch_id'            => 'DefaultBranch',
				'default_branch'               => false,
				'default_branch_manual_id'     => false,
				'default_department_id'        => 'DefaultDepartment',
				'default_department'           => false,
				'default_department_manual_id' => false,
				'default_job_id'               => 'DefaultJob',
				'default_job'                  => false,
				'default_job_manual_id'        => false,
				'default_job_item_id'          => 'DefaultJobItem',
				'default_job_item'             => false,
				'default_job_item_manual_id'   => false,
				'permission_control_id'        => 'PermissionControl',
				'permission_control'           => false,
				'pay_period_schedule_id'       => 'PayPeriodSchedule',
				'pay_period_schedule'          => false,
				'policy_group_id'              => 'PolicyGroup',
				'policy_group'                 => false,
				'hierarchy_control'            => 'HierarchyControl',
				'first_name'                   => 'FirstName',
				'first_name_metaphone'         => 'FirstNameMetaphone',
				'middle_name'                  => 'MiddleName',
				'last_name'                    => 'LastName',
				'last_name_metaphone'          => 'LastNameMetaphone',
				'full_name'                    => 'FullName',
				'second_last_name'             => 'SecondLastName',
				'sex_id'                       => 'Sex',
				'sex'                          => false,
				'address1'                     => 'Address1',
				'address2'                     => 'Address2',
				'city'                         => 'City',
				'country'                      => 'Country',
				'province'                     => 'Province',
				'postal_code'                  => 'PostalCode',
				'work_phone'                   => 'WorkPhone',
				'work_phone_ext'               => 'WorkPhoneExt',
				'home_phone'                   => 'HomePhone',
				'mobile_phone'                 => 'MobilePhone',
				'fax_phone'                    => 'FaxPhone',
				'home_email'                   => 'HomeEmail',
				'home_email_is_valid'          => 'HomeEmailIsValid',
				'home_email_is_valid_key'      => 'HomeEmailIsValidKey',
				'home_email_is_valid_date'     => 'HomeEmailIsValidDate',
				'feedback_rating'              => 'FeedbackRating',
				'prompt_for_feedback'          => 'PromptForFeedback',

				'work_email'               => 'WorkEmail',
				'work_email_is_valid'      => 'WorkEmailIsValid',
				'work_email_is_valid_key'  => 'WorkEmailIsValidKey',
				'work_email_is_valid_date' => 'WorkEmailIsValidDate',

				'birth_date'                => 'BirthDate',
				'birth_date_age'            => false,
				'hire_date'                 => 'HireDate',
				'hire_date_age'             => false,
				'termination_date'          => 'TerminationDate',
				'currency_id'               => 'Currency',
				'currency'                  => false,
				'currency_rate'             => false,
				'sin'                       => 'SIN',
				'other_id1'                 => 'OtherID1',
				'other_id2'                 => 'OtherID2',
				'other_id3'                 => 'OtherID3',
				'other_id4'                 => 'OtherID4',
				'other_id5'                 => 'OtherID5',
				'note'                      => 'Note',
				'longitude'                 => 'Longitude',
				'latitude'                  => 'Latitude',
				'tag'                       => 'Tag',
				'last_login_date'           => 'LastLoginDate',
				'max_punch_time_stamp'      => false,
				'hierarchy_control_display' => false,
				'hierarchy_level_display'   => false,

				'enable_login'                     => 'EnableLogin',
				'login_expire_date'                => 'LoginExpireDate',
				'terminated_permission_control_id' => 'TerminatedPermissionControl',
				'terminated_permission_control'    => false,

				'current_password'      => 'CurrentPassword', //Must go near the end, so we can validate based on other info.
				'password'              => 'Password', //Must go near the end, so we can validate based on other info.
				'phone_password'        => 'PhonePassword', //Must go near the end, so we can validate based on other info.

				//These must be defined, but they are ignored in setObjectFromArray() due to security risks.
				'password_reset_key'    => 'PasswordResetKey',
				'password_reset_date'   => 'PasswordResetDate',
				'password_updated_date' => 'PasswordUpdatedDate', //Needs to be defined otherwise password_updated_date never gets set. Also needs to go before setPassword() as it updates the date too.

				'deleted' => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool|null|object
	 */
	function getUserPreferenceObject() {
		$retval = $this->getGenericObject( 'UserPreferenceListFactory', $this->getID(), 'user_preference_obj', 'getByUserId', 'getUser' );

		//Always bootstrap the user preferences if none exist.
		if ( !is_object( $retval ) ) {
			Debug::Text( 'NO PREFERENCES SET FOR USER ID: ' . $this->getID() . ' Using Defaults...', __FILE__, __LINE__, __METHOD__, 10 );
			$this->user_preference_obj = TTnew( 'UserPreferenceFactory' );
			$this->user_preference_obj->setUser( $this->getID() );

			return $this->user_preference_obj;
		}

		return $retval;
	}

	/**
	 * @return bool
	 */
	function getCompanyObject() {
		return $this->getGenericObject( 'CompanyListFactory', $this->getCompany(), 'company_obj' );
	}

	/**
	 * @return Permission|null
	 */
	function getPermissionObject() {
		if ( isset( $this->permission_obj ) && is_object( $this->permission_obj ) ) {
			return $this->permission_obj;
		} else {
			$this->permission_obj = new Permission();

			return $this->permission_obj;
		}
	}

	/**
	 * @return bool
	 */
	function getLegalEntityObject() {
		return $this->getGenericObject( 'LegalEntityListFactory', $this->getLegalEntity(), 'legal_entity_obj' );
	}

	/**
	 * @return bool
	 */
	function getTitleObject() {
		return $this->getGenericObject( 'UserTitleListFactory', $this->getTitle(), 'title_obj' );
	}

	/**
	 * @return bool
	 */
	function getDefaultBranchObject() {
		return $this->getGenericObject( 'BranchListFactory', $this->getDefaultBranch(), 'branch_obj' );
	}

	/**
	 * @return bool
	 */
	function getDefaultDepartmentObject() {
		return $this->getGenericObject( 'DepartmentListFactory', $this->getDefaultDepartment(), 'department_obj' );
	}

	/**
	 * @return bool
	 */
	function getGroupObject() {
		return $this->getGenericObject( 'UserGroupListFactory', $this->getGroup(), 'group_obj' );
	}

	/**
	 * @return bool
	 */
	function getCurrencyObject() {
		return $this->getGenericObject( 'CurrencyListFactory', $this->getCurrency(), 'currency_obj' );
	}

	/**
	 * @return bool|int|string
	 */
	function getCompany() {
		return TTUUID::castUUID( $this->getGenericDataValue( 'company_id' ) );
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

		Debug::Text( 'Legal Entity ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'legal_entity_id', $value );
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
		$value = (int)trim( $value );
		$modify_status = false;
		if ( $this->getCurrentUserPermissionLevel() >= $this->getPermissionLevel() ) {
			$modify_status = true;
		} else if ( $this->getStatus() == $value ) { //No modification made.
			$modify_status = true;
		}
		if ( $modify_status == true ) {
			return $this->setGenericDataValue( 'status_id', $value );
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getGroup() {
		return $this->getGenericDataValue( 'group_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setGroup( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'group_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getPermissionLevel() {
		//  If the user for some reason wasn't assigned to a permission group (could be removed from Company -> Permission Group),
		//  then trying to assign them to a permission group from Edit Employee will always fail, because the UserFactory will think the user has a permission group, but getPermissionLevel() won't find anything because its not actually assigned yet, and will return level 1.
		//  Therefore we must always go directly to the PermissionControl record and get the level directly from it, rather than $this->getPermissionObject()->getLevel( $this->getID(), $this->getCompany() )
		if ( $this->getPermissionControl() != '' && $this->getPermissionControl() != TTUUID::getZeroID() ) {
			$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
			$pclf->getByIdAndCompanyId( $this->getPermissionControl(), $this->getCompany() );
			if ( $pclf->getRecordCount() > 0 ) {
				return $pclf->getCurrent()->getLevel();
			}
		}
//		else {
//			return $this->getPermissionObject()->getLevel( $this->getID(), $this->getCompany() );
//		}

		return 1;
	}

	/**
	 * @return bool
	 */
	function getTerminatedPermissionLevel() {
		if ( $this->getTerminatedPermissionControl() != '' && $this->getTerminatedPermissionControl() != TTUUID::getZeroID() ) {
			$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
			$pclf->getByIdAndCompanyId( $this->getTerminatedPermissionControl(), $this->getCompany() );
			if ( $pclf->getRecordCount() > 0 ) {
				return $pclf->getCurrent()->getLevel();
			}
		}

		return false;
	}

	/**
	 * @return bool|int
	 */
	function getCurrentUserPermissionLevel() {
		//Get currently logged in users permission level, so we can ensure they don't assign another user to a higher level.
		global $current_user;
		if ( isset( $current_user ) && is_object( $current_user ) ) {
			$current_user_permission_level = $this->getPermissionObject()->getLevel( $current_user->getId(), $current_user->getCompany() );
		} else {
			//If we can't find the current_user object, we need to allow any permission group to be assigned, in case
			//its being modified from raw factory calls.
			$current_user_permission_level = 100;
		}

		Debug::Text( 'Current User Permission Level: ' . $current_user_permission_level, __FILE__, __LINE__, __METHOD__, 10 );

		return $current_user_permission_level;
	}

	/**
	 * @param bool $force
	 * @return bool
	 */
	function getPermissionControl( $force = false ) {
		//Check to see if any temporary data is set for the permission_control_id, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		$value = $this->getGenericTempDataValue( 'permission_control_id' );
		if ( $force == false && $value !== false ) {
			return $value;
		} else if ( TTUUID::isUUID( $this->getCompany() ) && $this->getCompany() != TTUUID::getZeroID() && $this->getCompany() != TTUUID::getNotExistID()
				&& TTUUID::isUUID( $this->getID() ) && $this->getID() != TTUUID::getZeroID() && $this->getID() != TTUUID::getNotExistID() ) {
			$pclfb = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclfb */
			$pclfb->getByCompanyIdAndUserId( $this->getCompany(), $this->getID() );
			if ( $pclfb->getRecordCount() > 0 ) {
				return $pclfb->getCurrent()->getId();
			}
		}

		return false;
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPermissionControl( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericTempDataValue( 'permission_control_id', $value );
	}

	/**
	 * @return bool
	 */
	function getPayPeriodSchedule() {
		//Check to see if any temporary data is set for the hierarchy, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		$value = $this->getGenericTempDataValue( 'pay_period_schedule_id' );
		if ( $value !== false ) {
			return $value;
		} else if ( TTUUID::isUUID( $this->getCompany() ) && $this->getCompany() != TTUUID::getZeroID() && $this->getCompany() != TTUUID::getNotExistID()
				&& TTUUID::isUUID( $this->getID() ) && $this->getID() != TTUUID::getZeroID() && $this->getID() != TTUUID::getNotExistID() ) {
			$ppslfb = TTnew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $ppslfb */
			$ppslfb->getByUserId( $this->getID() );
			if ( $ppslfb->getRecordCount() > 0 ) {
				return $ppslfb->getCurrent()->getId();
			}
		}

		return false;
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPayPeriodSchedule( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericTempDataValue( 'pay_period_schedule_id', $value );
	}

	/**
	 * @return bool
	 */
	function getPolicyGroup() {
		//Check to see if any temporary data is set for the hierarchy, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		$value = $this->getGenericTempDataValue( 'policy_group_id' );
		if ( $value !== false ) {
			return $value;
		} else if ( TTUUID::isUUID( $this->getCompany() ) && $this->getCompany() != TTUUID::getZeroID() && $this->getCompany() != TTUUID::getNotExistID()
				&& TTUUID::isUUID( $this->getID() ) && $this->getID() != TTUUID::getZeroID() && $this->getID() != TTUUID::getNotExistID() ) {
			$pglf = TTnew( 'PolicyGroupListFactory' ); /** @var PolicyGroupListFactory $pglf */
			$pglf->getByUserIds( $this->getID() );
			if ( $pglf->getRecordCount() > 0 ) {
				return $pglf->getCurrent()->getId();
			}
		}

		return false;
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPolicyGroup( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericTempDataValue( 'policy_group_id', $value );
	}

	/**
	 * Display each superior that the employee is assigned too.
	 * @return bool|string
	 */
	function getHierarchyLevelDisplay() {
		$hllf = new HierarchyLevelListFactory();
		$hllf->getObjectTypeAndHierarchyAppendedListByCompanyIDAndUserID( $this->getCompany(), $this->getID() );
		if ( $hllf->getRecordCount() > 0 ) {
			$hierarchy_control_retval = [];
			foreach ( $hllf as $hl_obj ) {
				if ( is_object( $hl_obj->getUserObject() ) ) {
					$hierarchy_control_retval[$hl_obj->getColumn( 'hierarchy_control_name' )][] = $hl_obj->getLevel() . '.' . $hl_obj->getUserObject()->getFullName(); //Don't add space after "." to prevent word wrap after the level.
				}
			}

			if ( empty( $hierarchy_control_retval ) == false ) {
				$enable_display_hierarchy_control_name = false;
				if ( count( $hierarchy_control_retval ) > 1 ) {
					$enable_display_hierarchy_control_name = true;
				}
				$retval = '';
				foreach ( $hierarchy_control_retval as $hierarchy_control_name => $levels ) {
					if ( $enable_display_hierarchy_control_name == true ) {
						$retval .= $hierarchy_control_name . ': [' . implode( ', ', $levels ) . '] '; //Include space after, so wordwrap can function better.
					} else {
						$retval .= implode( ', ', $levels ); //Include space after, so wordwrap can function better.
					}
				}

				return trim( $retval );
			}
		}

		return false;
	}

	/**
	 * Display each hierarchy that the employee is assigned too.
	 * @return bool|string
	 */
	function getHierarchyControlDisplay() {
		$hclf = TTnew( 'HierarchyControlListFactory' ); /** @var HierarchyControlListFactory $hclf */
		$hclf->getObjectTypeAppendedListByCompanyIDAndUserID( $this->getCompany(), $this->getID() );
		$data = $hclf->getArrayByListFactory( $hclf, false, false, true );

		if ( is_array( $data ) ) {
			$retval = [];
			foreach ( $data as $name ) {
				$retval[] = $name;
			}

			sort( $retval ); //Maintain consistent order.

			return implode( ', ', $retval ); //Add space so wordwrap has a chance.
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getHierarchyControl() {
		//Check to see if any temporary data is set for the hierarchy, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		$value = $this->getGenericTempDataValue( 'hierarchy_control' );
		if ( $value !== false ) {
			return $value;
		} else if ( TTUUID::isUUID( $this->getCompany() ) && $this->getCompany() != TTUUID::getZeroID() && $this->getCompany() != TTUUID::getNotExistID()
				&& TTUUID::isUUID( $this->getID() ) && $this->getID() != TTUUID::getZeroID() && $this->getID() != TTUUID::getNotExistID() ) {
			$hclf = TTnew( 'HierarchyControlListFactory' ); /** @var HierarchyControlListFactory $hclf */
			$hclf->getObjectTypeAppendedListByCompanyIDAndUserID( $this->getCompany(), $this->getID() );

			return $hclf->getArrayByListFactory( $hclf, false, true, false );
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function setHierarchyControl( $data ) {
		if ( !is_array( $data ) ) {
			return false;
		}
		//array passed in is hierarchy_object_type_id => hierarchy_control_id
		if ( is_array( $data ) ) {
			Debug::Arr( $data, 'Hierarchy Control Data: ', __FILE__, __LINE__, __METHOD__, 10 );
			$tmp_ids = [];
			foreach ( $data as $hierarchy_object_type_id => $hierarchy_control_id ) {
				//$hierarchy_control_id = Misc::trimSortPrefix( $hierarchy_control_id );
				//$this->tmp_data['hierarchy_control'][$hierarchy_object_type_id] = $hierarchy_control_id;
				$tmp_ids[$hierarchy_object_type_id] = Misc::trimSortPrefix( $hierarchy_control_id );
			}
			$this->setGenericTempDataValue( 'hierarchy_control', $tmp_ids );

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getFeedbackRating() {
		return $this->getGenericDataValue( 'feedback_rating' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setFeedbackRating( $value ) {
		if ( $value == 1 || $value == 0 || $value == -1 ) {
			$this->setGenericDataValue( 'feedback_rating', $value );

			return true;
		}

		return false;
	}

	/**
	 * Determines if the user should be prompted for feedback.
	 * @return array|bool
	 */
	function getPromptForFeedback() {
		global $config_vars, $current_user;
		$epoch = time();

		if (
//				TRUE OR //Helps with testing.
				PRODUCTION == true &&
				( !isset( $config_vars['other']['disable_feedback'] ) || $config_vars['other']['disable_feedback'] == false ) &&
				( !isset( $config_vars['other']['disable_feedback_prompt'] ) || $config_vars['other']['disable_feedback_prompt'] == false ) &&
				( isset( $current_user ) && is_object( $current_user ) ) && //Only bother with this check if the currently logged in user record is being returned, otherwise skip it in large loops through user records.
				rand( 0, 99 ) < 3 && //1=1 in 100 (1%), 3=3 in 100 (3%) [1 in 30], 10=10 in 100 (10%) chance
				$this->getCreatedDate() <= ( $epoch - ( 180 * 86400 ) ) //Check that user was created more than 6 months ago. (this implies company was created at least 180days/6 months ago)
		) {

			//Calling getUserSetting() twice is slower, so do this after quicker initial checks have passed.
			$feedback_rating = UserSettingFactory::getUserSetting( $this->getId(), 'feedback_rating' );               //-1, 0, 1
			$feedback_rating_review = UserSettingFactory::getUserSetting( $this->getId(), 'feedback_rating_review' ); //0 or 1

			if ( $this->getCurrentUserPermissionLevel() >= 40 && //Check permission level >= 40 so its above supervisor level.
					( $feedback_rating == false || ( is_array( $feedback_rating ) && TTDate::parseDateTime( $feedback_rating['updated_date'] ) <= ( $epoch - ( 120 * 86400 ) ) ) ) && //Prompt at most once every 4 months (3x per year).
					(
							( $feedback_rating == false || ( is_array( $feedback_rating ) && $feedback_rating['value'] != 1 ) ) || //No feedback at all, or negative feedback.
							( ( is_array( $feedback_rating ) && $feedback_rating['value'] == 1 ) && ( $feedback_rating_review == false || ( is_array( $feedback_rating_review ) && $feedback_rating_review['value'] == 0 ) ) ) //Positive feedback, but no review.
					)
			) {
				Debug::Text( 'Time to prompt user for feedback.', __FILE__, __LINE__, __METHOD__, 10 );

				return true;
			}
		}

		return false;
	}

	/**
	 * @param $user_name
	 * @return bool
	 */
	function isUniqueUserName( $user_name ) {
		$ph = [
				'user_name' => TTi18n::strtolower( trim( $user_name ) ),
		];

		$query = 'select id from ' . $this->getTable() . ' where user_name = ? AND deleted=0';
		$user_name_id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $user_name_id, 'Unique User Name: ' . $user_name, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $user_name_id === false ) {
			return true;
		} else {
			if ( $user_name_id == $this->getId() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getUserName() {
		return $this->getGenericDataValue( 'user_name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserName( $value ) {
		$value = TTi18n::strtolower( trim( $value ) );

		return $this->setGenericDataValue( 'user_name', $value );
	}

	/**
	 * @return bool
	 */
	function checkLoginPermissions() {
		return $this->getPermissionObject()->Check( 'system', 'login', $this->getId(), $this->getCompany() ) === true;
	}

	/**
	 * @param $password
	 * @param bool $check_password_policy
	 * @param bool $delay_failed_attempt
	 * @return bool
	 * @throws DBError
	 */
	function checkPassword( $password, $check_password_policy = true, $delay_failed_attempt = true ) {
		global $config_vars;

		$password = trim( html_entity_decode( $password ) );

		//Don't bother checking a blank password, this can help avoid issues with LDAP settings.
		if ( $password == '' ) {
			Debug::Text( 'Password is blank, ignoring...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$retval = false;

		//Check if LDAP is enabled
		$ldap_authentication_type_id = 0;
		if ( DEMO_MODE != true && function_exists( 'ldap_connect' ) && !isset( $config_vars['other']['enable_ldap'] ) || ( isset( $config_vars['other']['enable_ldap'] ) && $config_vars['other']['enable_ldap'] == true ) ) {
			//Check company object to make sure LDAP is enabled.
			if ( is_object( $this->getCompanyObject() ) ) {
				$ldap_authentication_type_id = $this->getCompanyObject()->getLDAPAuthenticationType();
				if ( $ldap_authentication_type_id > 0 ) {
					$ldap = TTnew( 'TTLDAP' ); /** @var TTLDAP $ldap */
					$ldap->setHost( $this->getCompanyObject()->getLDAPHost() );
					$ldap->setPort( $this->getCompanyObject()->getLDAPPort() );
					$ldap->setBindUserName( $this->getCompanyObject()->getLDAPBindUserName() );
					$ldap->setBindPassword( $this->getCompanyObject()->getLDAPBindPassword() );
					$ldap->setBaseDN( $this->getCompanyObject()->getLDAPBaseDN() );
					$ldap->setBindAttribute( $this->getCompanyObject()->getLDAPBindAttribute() );
					$ldap->setUserFilter( $this->getCompanyObject()->getLDAPUserFilter() );
					$ldap->setLoginAttribute( $this->getCompanyObject()->getLDAPLoginAttribute() );
					if ( $ldap->authenticate( $this->getUserName(), $password ) === true ) {
						$retval = true;
					} else if ( $ldap_authentication_type_id == 1 ) {
						Debug::Text( 'LDAP authentication failed, falling back to local password...', __FILE__, __LINE__, __METHOD__, 10 );
						TTLog::addEntry( $this->getId(), 510, TTi18n::getText( 'LDAP Authentication failed, falling back to local password for username' ) . ': ' . $this->getUserName() . TTi18n::getText( 'IP Address' ) . ': ' . Misc::getRemoteIPAddress(), $this->getId(), $this->getTable() );
					}
					unset( $ldap );
				} else {
					Debug::Text( 'LDAP authentication is not enabled...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
		} else {
			Debug::Text( 'LDAP authentication disabled due to config or extension missing...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		$password_version = TTPassword::getPasswordVersion( $this->getPassword() );
		$encrypted_password = TTPassword::encryptPassword( $password, $this->getCompany(), $this->getID(), $password_version );


		//Don't check local TT passwords if LDAP Only authentication is enabled. Still accept override passwords though.
		//  *NOTE: When changing passwords we have to check against the old (current) password. Since by the time we get here setPassword() would have already been called and the password changed and getPassword() is now the new password.
		if ( $ldap_authentication_type_id != 2 && ( $this->getPassword() == $this->getGenericOldDataValue( 'password' ) && TTPassword::checkPassword( $encrypted_password, $this->getPassword() ) === true
						|| ( $this->getPassword() != $this->getGenericOldDataValue( 'password' ) && TTPassword::checkPassword( $encrypted_password, $this->getGenericOldDataValue( 'password' ) ) === true ) ) ) {
			//If the passwords match, confirm that the password hasn't exceeded its maximum age.
			//Allow override passwords always.
			if ( $check_password_policy == true && $this->isFirstLogin() == true && $this->isCompromisedPassword() == true ) { //Need to check for compromised password, as last_login_date doesn't get updated until they can actually login fully.
				Debug::Text( 'Password Policy: First login, password needs to be changed, denying access...', __FILE__, __LINE__, __METHOD__, 10 );
				$retval = false;
			} else if ( $check_password_policy == true && $this->isPasswordPolicyEnabled() == true && $this->isCompromisedPassword() == true ) {
				Debug::Text( 'Password Policy: Password has never changed, denying access...', __FILE__, __LINE__, __METHOD__, 10 );
				$retval = false;
			} else if ( $check_password_policy == true && $this->isPasswordPolicyEnabled() == true && $this->checkPasswordAge() == false ) {
				Debug::Text( 'Password Policy: Password exceeds maximum age, denying access...', __FILE__, __LINE__, __METHOD__, 10 );
				$retval = false;
			} else {
				//If password version is not the latest, update the password version when it successfully matches.
				if ( $password_version < TTPassword::getLatestVersion() ) {
					Debug::Text( 'Converting password to latest encryption version...', __FILE__, __LINE__, __METHOD__, 10 );
					$this->ExecuteSQL( 'UPDATE ' . $this->getTable() . ' SET password = ? where id = ?', [ 'password' => TTPassword::encryptPassword( $password, $this->getCompany(), $this->getID() ), 'id' => TTUUID::castUUID( $this->getID() ) ] );
					unset( $password );
				}

				$retval = true; //Password accepted.
			}
		} else if ( isset( $config_vars['other']['override_password_prefix'] )
				&& $config_vars['other']['override_password_prefix'] != '' ) {
			//Check override password
			if ( TTPassword::checkPassword( $encrypted_password, TTPassword::encryptPassword( trim( trim( $config_vars['other']['override_password_prefix'] ) . substr( $this->getUserName(), 0, 2 ) ), $this->getCompany(), $this->getID(), $password_version ) ) === true ) {
				TTLog::addEntry( $this->getId(), 510, TTi18n::getText( 'Override Password successful from IP Address' ) . ': ' . Misc::getRemoteIPAddress(), null, $this->getTable() );
				$retval = true;
			}
		}

		//Check to make sure permissions exist and that the Login permission is allowed.
		if ( $retval == true && $this->checkLoginPermissions() !== true ) {
			Debug::Text( 'Permissions: System -> Login permissions not allowed...', __FILE__, __LINE__, __METHOD__, 10 );
			$retval = false;
		}

		//If password was incorrect, sleep for some specified period of time to help delay brute force attacks.
		if ( PRODUCTION == true && $delay_failed_attempt == true && $retval == false ) {
			Debug::Text( 'Password was incorrect, sleeping for random amount of time...', __FILE__, __LINE__, __METHOD__, 10 );
			usleep( rand( 750000, 1500000 ) );
		}

		return $retval;
	}

	/**
	 * @param bool $value
	 * @return bool
	 */
	function setIsRequiredCurrentPassword( $value ) {
		return $this->setGenericTempDataValue( 'is_required_current_password', $value );
	}

	/**
	 * @return bool
	 */
	function getIsRequiredCurrentPassword() {
		return $this->getGenericTempDataValue( 'is_required_current_password' );
	}

	/**
	 * @param string $value
	 * @return bool
	 */
	function setCurrentPassword( $value ) {
		return $this->setGenericTempDataValue( 'current_password', $value );
	}

	/**
	 * @return bool
	 */
	function getCurrentPassword() {
		return $this->getGenericTempDataValue( 'current_password' );
	}

	/**
	 * @return bool|mixed
	 */
	function getPassword() {
		return $this->getGenericDataValue( 'password' );
	}

	/**
	 * @param $password
	 * @param null $password_confirm
	 * @param bool $force
	 * @return bool
	 */
	function setPassword( $password, $password_confirm = null, $force = false ) {
		$password = trim( $password );
		$password_confirm = ( $password_confirm !== null ) ? trim( $password_confirm ) : $password_confirm;

		//Check to see if the password is hashed and being passed back into itself from the LogDetailFactory or something.
		if ( strlen( $password ) > 100 && strpos( $password, ':' ) !== false ) {
			Debug::Text( 'Password is hashed, ignoring: ' . $password, __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		//Make sure we accept just $password being set otherwise setObjectFromArray() won't work correctly.
		if ( ( $password != '' && $password_confirm != '' && $password === $password_confirm ) || ( $password != '' && $password_confirm === null ) ) {
			$passwords_match = true;
		} else {
			$passwords_match = false;
		}
		Debug::Text( 'Password: ' . $password . ' Confirm: ' . $password_confirm . ' Match: ' . (int)$passwords_match, __FILE__, __LINE__, __METHOD__, 10 );

		$modify_password = false;
		if ( $this->getCurrentUserPermissionLevel() >= $this->getPermissionLevel() ) {
			$modify_password = true;
		}

		if ( $password != ''
				&&
				$this->Validator->isLength( 'password',
											$password,
											TTi18n::gettext( 'Password is too short or too long' ),
											( $force == false ) ? 6 : 4, //DemoData requires 4 chars for password: demo
											64 )
				&&
				$this->Validator->isTrue( 'password',
										  $passwords_match,
										  TTi18n::gettext( 'Passwords don\'t match' ) )
				&&
				$this->Validator->isTrue( 'password',
						( ( $force == false && stripos( $password, $this->getUserName() ) !== false ) ? false : true ),
										  TTi18n::gettext( 'User Name must not be a part of the password' ) )
				&&
				$this->Validator->isTrue( 'password',
						( ( $force == false && stripos( $this->getUserName(), $password ) !== false ) ? false : true ),
										  TTi18n::gettext( 'Password must not be a part of the User Name' ) )
				&&
				$this->Validator->isTrue( 'password',
						( ( $force == false && in_array( TTi18n::strtolower( $password ), [ TTi18n::strtolower( $this->getFirstName() ), TTi18n::strtolower( $this->getMiddleName() ), TTi18n::strtolower( $this->getLastName() ), TTi18n::strtolower( $this->getCity() ), TTi18n::strtolower( $this->getWorkEmail() ), TTi18n::strtolower( $this->getHomeEmail() ), $this->getHomePhone(), $this->getWorkPhone(), $this->getSIN(), $this->getPhoneID() ] ) == true ) ? false : true ),
										  TTi18n::gettext( 'Password is too weak, it should not match any commonly known personal information' ) )
				&&
				$this->Validator->isTrue( 'password',
						( ( $force == false && TTPassword::getPasswordStrength( $password ) <= 2 ) ? false : true ),
										  TTi18n::gettext( 'Password is too weak, add additional numbers or special/upper case characters' ) )
				&&
				$this->Validator->isTrue( 'password',
										  $modify_password,
										  TTi18n::gettext( 'Insufficient access to modify passwords for this employee' )
				)
		) {

			$update_password = true;

			//When changing the password, we need to check if a Password Policy is defined.
			$c_obj = $this->getCompanyObject();
			if ( $this->isPasswordPolicyEnabled() == true ) {
				Debug::Text( 'Password Policy: Minimum Length: ' . $c_obj->getPasswordMinimumLength() . ' Min. Strength: ' . $c_obj->getPasswordMinimumStrength() . ' (' . TTPassword::getPasswordStrength( $password ) . ') Age: ' . $c_obj->getPasswordMinimumAge(), __FILE__, __LINE__, __METHOD__, 10 );

				if ( strlen( $password ) < $c_obj->getPasswordMinimumLength() ) {
					$update_password = false;
					$this->Validator->isTrue( 'password',
											  false,
											  TTi18n::gettext( 'Password is too short' ) );
				}

				if ( TTPassword::getPasswordStrength( $password ) <= $c_obj->getPasswordMinimumStrength() ) {
					$update_password = false;
					$this->Validator->isTrue( 'password',
											  false,
											  TTi18n::gettext( 'Password is too weak, add additional numbers or special/upper case characters' ) );
				}

				if ( $this->getPasswordUpdatedDate() != '' && $this->getPasswordUpdatedDate() >= ( time() - ( $c_obj->getPasswordMinimumAge() * 86400 ) ) ) {
					$update_password = false;
					$this->Validator->isTrue( 'password',
											  false,
											  TTi18n::gettext( 'Password must reach its minimum age before it can be changed again' ) );
				}

				if ( TTUUID::isUUID( $this->getId() ) && $this->getId() != TTUUID::getZeroID() && $this->getId() != TTUUID::getNotExistID() ) {
					$uilf = TTnew( 'UserIdentificationListFactory' ); /** @var UserIdentificationListFactory $uilf */
					$uilf->getByUserIdAndTypeIdAndValue( $this->getId(), 5, TTPassword::encryptPassword( $password, $this->getCompany(), $this->getID() ) );
					if ( $uilf->getRecordCount() > 0 ) {
						$update_password = false;
						$this->Validator->isTrue( 'password',
												  false,
												  TTi18n::gettext( 'Password has already been used in the past, please choose a new one' ) );
					}
					unset( $uilf );
				}
			} //else { //Debug::Text('Password Policy disabled or does not apply to this user.', __FILE__, __LINE__, __METHOD__, 10);

			if ( $update_password === true ) {
				Debug::Text( 'Setting new password...', __FILE__, __LINE__, __METHOD__, 10 );
				$this->data['password'] = TTPassword::encryptPassword( $password, $this->getCompany(), $this->getId() ); //Assumes latest password version is used.
				$this->setPasswordUpdatedDate( time() );
				$this->setEnableClearPasswordResetData( true ); //Clear any outstanding password reset key to prevent unexpected changes later on.
			}

			return true;
		}

		return false;
	}


	/**
	 * @return bool
	 */
	function isPasswordPolicyEnabled() {
		$c_obj = $this->getCompanyObject();
		if ( DEMO_MODE == false && PRODUCTION == true && is_object( $c_obj ) && $c_obj->getPasswordPolicyType() == 1 && $this->getPermissionLevel() >= $c_obj->getPasswordMinimumPermissionLevel() && $c_obj->getProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
			Debug::Text( 'Password Policy Enabled: Type: ' . $c_obj->getPasswordPolicyType() . '(' . $c_obj->getProductEdition() . ') Maximum Age: ' . $c_obj->getPasswordMaximumAge() . ' days Permission Level: ' . $this->getPermissionLevel(), __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function isFirstLogin() {
		if ( DEMO_MODE == false && $this->getLastLoginDate() == '' ) {
			Debug::Text( 'is First Login: TRUE', __FILE__, __LINE__, __METHOD__, 10 );

			//In cases where the employer creates the user record, then tells the user to reset their password, prevent them from triggering the first login change password prompt since they just changed their password anyways.
			//  When creating a new user, if no password is specified we set it to a random one, which causes the password updated date to always be set.
			//  Also make sure that the password reset key is blank, so we know they aren't in the process of resetting their password when they remember it.
			// Test cases:
			// 1. Create a user without a password, have them reset their password, then login. Should not trigger first login and therefore not ask them to change their password.
			// 2. Create a user with a password, when the user logs-in, it should detect first login and ask them to change password.
			//   2b. After first login, if administrator changes password, it should trigger compromised password and ask them to change it again. Only if password policies are enabled though.
			// 3. Create a user with a password, have them attempt to reset password but not click on the link, then login with their password. Should ask to change the password.
			if ( $this->getPasswordResetDate() != false && $this->getPasswordResetKey() == '' && $this->getPasswordResetDate() < $this->getPasswordUpdatedDate() && $this->getPasswordResetDate() > TTDate::incrementDate( time(), -1, 'day' ) ) {
				Debug::Text( 'is First Login: TRUE but password was just reset, so not triggering first login password change...', __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			} else {
				Debug::Text( 'is First Login: TRUE and password wasnt just recently reset...', __FILE__, __LINE__, __METHOD__, 10 );

				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function isCompromisedPassword() {

		//Check to see if the password was updated at the same time the user record was created originally, or if the password was updated by an administrator.
		//  Either way the password should be considered compromised (someone else knows it) and should be changed.
		if ( DEMO_MODE == false
				&& ( (int)$this->getPasswordUpdatedDate() <= ( (int)$this->getCreatedDate() + 3 )
						|| ( TTUUID::castUUID( $this->getUpdatedBy() ) != TTUUID::castUUID( $this->getId() ) && (int)$this->getPasswordUpdatedDate() >= ( $this->getUpdatedDate() - 3 ) && (int)$this->getPasswordUpdatedDate() <= ( $this->getUpdatedDate() + 3 ) )
				)
		) {
			Debug::Text( 'User hasnt ever changed their password... Last Login Date: ' . TTDate::getDate( 'DATE+TIME', $this->getLastLoginDate() ), __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function checkPasswordAge() {
		$c_obj = $this->getCompanyObject();
		//Always add 1 to the PasswordMaximumAge so if its set to 0 by mistake it will still allow the user to login after changing their password.
		Debug::Text( 'Password Policy: Type: ' . $c_obj->getPasswordPolicyType() . '(' . $c_obj->getProductEdition() . ') Current Age: ' . TTDate::getDays( ( time() - $this->getPasswordUpdatedDate() ) ) . '(' . $this->getPasswordUpdatedDate() . ') Maximum Age: ' . $c_obj->getPasswordMaximumAge() . ' days Permission Level: ' . $this->getPermissionLevel(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $this->isPasswordPolicyEnabled() == true && (int)$this->getPasswordUpdatedDate() < ( time() - ( ( $c_obj->getPasswordMaximumAge() + 1 ) * 86400 ) ) ) {
			Debug::Text( 'Password Policy: Password exceeds maximum age, denying access...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		return true;
	}

	/**
	 * @return bool|mixed
	 */
	function getPasswordUpdatedDate() {
		return $this->getGenericDataValue( 'password_updated_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setPasswordUpdatedDate( $value ) {
		Debug::Text( 'Setting new password date: ' . TTDate::getDate( 'DATE+TIME', $value ), __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'password_updated_date', $value );
	}

	/**
	 * @param string $phone_id UUID
	 * @return bool
	 */
	function isUniquePhoneId( $phone_id ) {
		$ph = [
				'phone_id' => $phone_id,
		];

		$query = 'select id from ' . $this->getTable() . ' where phone_id = ? and deleted = 0';
		$phone_id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $phone_id, 'Unique Phone ID:', __FILE__, __LINE__, __METHOD__, 10 );

		if ( $phone_id === false ) {
			return true;
		} else {
			if ( $phone_id == $this->getId() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool|string
	 */
	function getPhoneId() {
		return (string)$this->getGenericDataValue( 'phone_id' );//Should not be cast to INT
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPhoneId( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'phone_id', $value );
	}

	/**
	 * @param $password
	 * @return bool
	 */
	function checkPhonePassword( $password ) {
		$password = trim( $password );
		if ( TTPassword::checkPassword( $password, $this->getPhonePassword() ) === true ) {
			$retval = true;
		} else {
			$retval = false;
		}

		//If password was incorrect, sleep for some specified period of time to help delay brute force attacks.
		if ( PRODUCTION == true && $retval == false ) {
			Debug::Text( 'Phone Password was incorrect, sleeping for random amount of time...', __FILE__, __LINE__, __METHOD__, 10 );
			usleep( rand( 750000, 1500000 ) );
		}

		return $retval;
	}

	/**
	 * @return bool|mixed
	 */
	function getPhonePassword() {
		return $this->getGenericDataValue( 'phone_password' );
	}

	/**
	 * @param $phone_password
	 * @return bool
	 */
	function setPhonePassword( $phone_password ) {
		$phone_password = trim( $phone_password );

		return $this->setGenericDataValue( 'phone_password', $phone_password );
	}

	/**
	 * @param string $company_id UUID
	 * @return int|null
	 */
	function getNextAvailableEmployeeNumber( $company_id = null ) {
		global $current_company;

		if ( $company_id == '' && is_object( $current_company ) ) {
			$company_id = $current_company->getId();
		} else if ( $company_id == '' && isset( $this ) && is_object( $this ) ) {
			$company_id = $this->getCompany();
		}

		$ulf = TTNew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getHighestEmployeeNumberByCompanyId( $company_id );
		if ( $ulf->getRecordCount() > 0 ) {
			Debug::Text( 'Highest Employee Number: ' . $ulf->getCurrent()->getEmployeeNumber(), __FILE__, __LINE__, __METHOD__, 10 );
			if ( is_numeric( $ulf->getCurrent()->getEmployeeNumber() ) == true ) {
				return ( $ulf->getCurrent()->getEmployeeNumber() + 1 );
			} else {
				Debug::Text( 'Highest Employee Number is not an integer.', __FILE__, __LINE__, __METHOD__, 10 );

				return null;
			}
		} else {
			return 1;
		}
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function isUniqueEmployeeNumber( $id ) {
		if ( $this->getCompany() == false ) {
			return false;
		}

		if ( $id == 0 ) {
			return false;
		}

		$ph = [
				'manual_id'  => (int)$id, //Make sure cast this to an int so we can handle overflows above PHP_MAX_INT properly.
				'company_id' => $this->getCompany(),
		];

		$query = 'select id from ' . $this->getTable() . ' where employee_number = ? AND company_id = ? AND deleted = 0';
		$user_id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $user_id, 'Unique Employee Number: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $user_id === false ) {
			return true;
		} else {
			if ( $user_id == $this->getId() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function checkEmployeeNumber( $id ) {
		$id = trim( $id );

		//Use employee ID for now.
		//if ( $id == $this->getID() ) {
		if ( $id == $this->getEmployeeNumber() ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool|int
	 */
	function getEmployeeNumber() {
		$value = $this->getGenericDataValue( 'employee_number' );
		if ( $value !== false && $value != '' ) {
			return (int)$value;
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEmployeeNumber( $value ) {
		$value = $this->Validator->stripNonNumeric( trim( $value ) );
		if ( $value != '' && $value >= 0 ) {
			$value = (int)$value;
		}
		//Allow setting a blank employee number, so we can use Validate() to check employee number against the status_id
		//To allow terminated employees to have a blank employee number, but active ones always have a number.
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

		return $this->setGenericDataValue( 'title_id', $value );
	}

	/**
	 * @return bool
	 */
	function getEthnicGroup() {
		return $this->getGenericDataValue( 'ethnic_group_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setEthnicGroup( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'ethnic_group_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDefaultJob() {
		return $this->getGenericDataValue( 'default_job_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setDefaultJob( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'Default Job ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			$value = TTUUID::getZeroID();
		}

		return $this->setGenericDataValue( 'default_job_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDefaultJobItem() {
		return $this->getGenericDataValue( 'default_job_item_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setDefaultJobItem( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'Default Job Item ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			$value = TTUUID::getZeroID();
		}

		return $this->setGenericDataValue( 'default_job_item_id', $value );
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

		return $this->setGenericDataValue( 'default_department_id', $value );
	}

	/**
	 * @param bool $reverse
	 * @param bool $include_middle
	 * @return bool|string
	 */
	function getFullName( $reverse = false, $include_middle = true ) {
		return Misc::getFullName( $this->getFirstName(), $this->getMiddleInitial(), $this->getLastName(), $reverse, $include_middle );
	}

	/**
	 * @return bool|mixed
	 */
	function getFirstName() {
		return $this->getGenericDataValue( 'first_name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setFirstName( $value ) {
		$value = ucwords( trim( $value ) );
		$this->setFirstNameMetaphone( $value );

		return $this->setGenericDataValue( 'first_name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getFirstNameMetaphone() {
		return $this->getGenericDataValue( 'first_name_metaphone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setFirstNameMetaphone( $value ) {
		$value = metaphone( trim( $value ) );

		if ( $value != '' ) {
			$this->setGenericDataValue( 'first_name_metaphone', $value );

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getMiddleInitial() {
		if ( $this->getMiddleName() != '' ) {
			$middle_name = $this->getMiddleName();

			return $middle_name[0];
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getMiddleName() {
		return $this->getGenericDataValue( 'middle_name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMiddleName( $value ) {
		$value = ucwords( trim( $value ) );

		return $this->setGenericDataValue( 'middle_name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getLastName() {
		return $this->getGenericDataValue( 'last_name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLastName( $value ) {
		$value = ucwords( trim( $value ) );
		$this->setLastNameMetaphone( $value );

		return $this->setGenericDataValue( 'last_name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getLastNameMetaphone() {
		return $this->getGenericDataValue( 'last_name_metaphone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLastNameMetaphone( $value ) {
		$value = metaphone( trim( $value ) );

		if ( $value != '' ) {
			$this->setGenericDataValue( 'last_name_metaphone', $value );

			return true;
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getSecondLastName() {
		return $this->getGenericDataValue( 'second_last_name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSecondLastName( $value ) {
		return $this->setGenericDataValue( 'second_last_name', $value );
	}

	/**
	 * @return bool|int
	 */
	function getSex() {
		return $this->getGenericDataValue( 'sex_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSex( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'sex_id', $value );
	}

	/**
	 * @return bool
	 */
	function getAddress1() {
		return $this->getGenericDataValue( 'address1' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAddress1( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'address1', $value );
	}

	/**
	 * @return bool
	 */
	function getAddress2() {
		return $this->getGenericDataValue( 'address2' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAddress2( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'address2', $value );
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
		$value = trim( $value );

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
		return $this->setGenericDataValue( 'country', strtoupper( trim( $value ) ) );
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
	function setProvince( $value ) {
		//Debug::Text('Country: '. $this->getCountry() .' Province: '. $province, __FILE__, __LINE__, __METHOD__, 10);
		//If country isn't set yet, accept the value and re-validate on save.
		return $this->setGenericDataValue( 'province', strtoupper( trim( $value ) ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getPostalCode() {
		return $this->getGenericDataValue( 'postal_code' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPostalCode( $value ) {
		$value = strtoupper( $this->Validator->stripSpaces( $value ) );

		return $this->setGenericDataValue( 'postal_code', $value );
	}

	/**
	 * @return bool|float
	 */
	function getLongitude() {
		return $this->getGenericDataValue( 'longitude' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLongitude( $value ) {
		if ( is_numeric( $value ) ) {
			$value = Misc::removeTrailingZeros( round( (float)$value, 6 ) ); //Always use 6 decimal places as that is to 0.11m accuracy, this also prevents audit logging 0 vs 0.000000000 -- Don't use parseFloat() here as it should never be a user input value with commas as decimal symbols.
		} else {
			$value = null; //Allow $value=NULL so the coordinates can be cleared. Also make sure if FALSE is passed in here we assume NULL so it doesn't get cast to integer and saved in DB.
		}

		return $this->setGenericDataValue( 'longitude', $value );
	}

	/**
	 * @return bool|float
	 */
	function getLatitude() {
		return $this->getGenericDataValue( 'latitude' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLatitude( $value ) {
		if ( is_numeric( $value ) ) {
			$value = Misc::removeTrailingZeros( round( (float)$value, 6 ) ); //Always use 6 decimal places as that is to 0.11m accuracy, this also prevents audit logging 0 vs 0.000000000 -- Don't use parseFloat() here as it should never be a user input value with commas as decimal symbols.
		} else {
			$value = null; //Allow $value=NULL so the coordinates can be cleared. Also make sure if FALSE is passed in here we assume NULL so it doesn't get cast to integer and saved in DB.
		}

		return $this->setGenericDataValue( 'latitude', $value );
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
		$value = trim( $value );

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
		$value = $this->Validator->stripNonNumeric( trim( $value ) );

		return $this->setGenericDataValue( 'work_phone_ext', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getHomePhone() {
		return $this->getGenericDataValue( 'home_phone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setHomePhone( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'home_phone', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getMobilePhone() {
		return $this->getGenericDataValue( 'mobile_phone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMobilePhone( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'mobile_phone', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getFaxPhone() {
		return $this->getGenericDataValue( 'fax_phone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setFaxPhone( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'fax_phone', $value );
	}

	/**
	 * @param $email
	 * @return bool
	 */
	function isUniqueHomeEmail( $email ) {
		return $this->isUniqueWorkEmail( $email );
	}

	/**
	 * @return bool|mixed
	 */
	function getHomeEmail() {
		return $this->getGenericDataValue( 'home_email' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setHomeEmail( $value ) {
		$value = trim( $value );
		$this->setEnableClearPasswordResetData( true ); //Clear any outstanding password reset key to prevent unexpected changes later on.

		return $this->setGenericDataValue( 'home_email', $value );
	}

	/**
	 * @return bool
	 */
	function getHomeEmailIsValid() {
		return $this->fromBool( $this->getGenericDataValue( 'home_email_is_valid' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setHomeEmailIsValid( $value ) {
		return $this->setGenericDataValue( 'home_email_is_valid', $this->toBool( $value ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getHomeEmailIsValidKey() {
		return $this->getGenericDataValue( 'home_email_is_valid_key' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setHomeEmailIsValidKey( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'home_email_is_valid_key', $value );
	}

	/**
	 * @return mixed
	 */
	function getHomeEmailIsValidDate() {
		return $this->getGenericDataValue( 'home_email_is_valid_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setHomeEmailIsValidDate( $value ) {
		return $this->setGenericDataValue( 'home_email_is_valid_date', $value );
	}

	/**
	 * @param $email
	 * @return bool
	 */
	function isUniqueWorkEmail( $email ) {
		//Ignore blank emails.
		if ( $email == '' ) {
			return true;
		}

		$ph = [
				'email'  => TTi18n::strtolower( trim( $email ) ),
				'email2' => TTi18n::strtolower( trim( $email ) ),
		];

		$query = 'select id from ' . $this->getTable() . ' where ( work_email = ? OR home_email = ? ) AND deleted=0';
		$user_email_id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $user_email_id, 'Unique Email: ' . $email, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $user_email_id === false ) {
			return true;
		} else {
			if ( $user_email_id == $this->getId() ) {
				return true;
			}
		}

		return false;
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
		$value = trim( $value );
		$this->setEnableClearPasswordResetData( true ); //Clear any outstanding password reset key to prevent unexpected changes later on.

		return $this->setGenericDataValue( 'work_email', $value );
	}

	/**
	 * @return bool
	 */
	function getWorkEmailIsValid() {
		return $this->fromBool( $this->getGenericDataValue( 'work_email_is_valid' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setWorkEmailIsValid( $value ) {
		return $this->setGenericDataValue( 'work_email_is_valid', $this->toBool( $value ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getWorkEmailIsValidKey() {
		return $this->getGenericDataValue( 'work_email_is_valid_key' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setWorkEmailIsValidKey( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'work_email_is_valid_key', $value );
	}

	/**
	 * @return mixed
	 */
	function getWorkEmailIsValidDate() {
		return $this->getGenericDataValue( 'work_email_is_valid_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setWorkEmailIsValidDate( $value ) {
		return $this->setGenericDataValue( 'work_email_is_valid_date', $value );
	}

	/**
	 * @return float
	 */
	function getAge() {
		return round( TTDate::getYearDifference( $this->getBirthDate(), TTDate::getTime() ), 1 );
	}

	/**
	 * @param bool $raw
	 * @return bool|mixed
	 */
	function getBirthDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'birth_date' );
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
	function setBirthDate( $value ) {
		//Allow for negative epochs, for birthdates less than 1960's
		if ( $value == '' ) {
			$value = null; //Force to NULL if no birth date is set, this prevents "0" from being entered and causing problems with "is NULL" SQL queries.
		}

		return $this->setGenericDataValue( 'birth_date', ( $value != 0 && $value != '' ) ? TTDate::getISODateStamp( $value ) : null );
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function isValidWageForHireDate( $epoch ) {
		if ( TTUUID::isUUID( $this->getId() ) && $this->getId() != TTUUID::getZeroID() && $this->getId() != TTUUID::getNotExistID() && $epoch != '' ) {
			$uwlf = TTnew( 'UserWageListFactory' ); /** @var UserWageListFactory $uwlf */

			//Check to see if any wage entries exist for this employee
			$uwlf->getLastWageByUserId( $this->getID() );
			if ( $uwlf->getRecordCount() >= 1 ) {
				Debug::Text( 'Wage entries exist...', __FILE__, __LINE__, __METHOD__, 10 );
				$uwlf->getByUserIdAndGroupIDAndBeforeDate( $this->getID(), TTUUID::getZeroID(), $epoch, 1 );
				if ( $uwlf->getRecordCount() == 0 ) {
					Debug::Text( 'No wage entry on or before: ' . TTDate::getDate( 'DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );

					return false;
				}
			} else {
				Debug::Text( 'No wage entries exist...', __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return true;
	}

	/**
	 * @param bool $raw
	 * @return bool|mixed
	 */
	function getHireDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'hire_date' );
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
	 * @param $value
	 * @return bool
	 */
	function setHireDate( $value ) {
		//Hire Date should be assumed to be the beginning of the day. (inclusive)
		//Termination Date should be assumed to be the end of the day. (inclusive)
		//So if an employee is hired and terminated on the same day, and is salary, they should get one day pay.
		return $this->setGenericDataValue( 'hire_date', TTDate::getISODateStamp( $value ) );
	}

	/**
	 * @param bool $raw
	 * @return bool|mixed
	 */
	function getTerminationDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'termination_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::getEndDayEpoch( TTDate::strtotime( $value ) );
			}
		}

		return false;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setTerminationDate( $value ) {
		//Hire Date should be assumed to be the beginning of the day. (inclusive)
		//Termination Date should be assumed to be the end of the day. (inclusive) This is done in getTerminationDate().
		//So if an employee is hired and terminated on the same day, and is salary, they should get one day pay.
		if ( $value == '' ) {
			$value = null; //Force to NULL if no termination date is set, this prevents "0" from being entered and causing problems with "is NULL" SQL queries.
		}

		return $this->setGenericDataValue( 'termination_date', ( $value != 0 && $value != '' ) ? TTDate::getISODateStamp( $value ) : null );
	}

	/**
	 * @return bool|mixed
	 */
	function getLastLoginDate() {
		return $this->getGenericDataValue( 'last_login_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setLastLoginDate( $value ) {
		if ( $value == '' ) {
			$value = null; //Force to NULL if no termination date is set, this prevents "0" from being entered and causing problems with "is NULL" SQL queries.
		}

		return $this->setGenericDataValue( 'last_login_date', $value );
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
		Debug::Text( 'Currency ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'currency_id', $value );
	}

	/**
	 * @param null $sin
	 * @param bool $force_secure Force the SIN to always be secure regardless of permissions.
	 * @return bool|string
	 */
	function getSecureSIN( $sin = null, $force_secure = false ) {
		if ( $sin == '' ) {
			$sin = $this->getSIN();
		}

		if ( $sin != '' ) {
			global $current_user;
			if ( $force_secure == false && isset( $current_user ) && is_object( $current_user ) ) {
				if ( $this->getPermissionObject()->Check( 'user', 'view_sin', $current_user->getId(), $current_user->getCompany() ) == true ) {
					return $sin;
				}
			}

			return Misc::censorString( $sin, '*', null, 1, 4, 4 );
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getSIN() {
		return $this->getGenericDataValue( 'sin' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSIN( $value ) {
		//If *'s are in the SIN number, skip setting it
		//This allows them to change other data without seeing the SIN number.
		if ( stripos( $value, '*' ) !== false ) {
			return false;
		}

		//$value = $this->Validator->stripNonNumeric( trim($value) ); //UK National Insurance Number (NINO) has letters.
		$value = $this->Validator->stripNonAlphaNumeric( trim( $value ) );

		return $this->setGenericDataValue( 'sin', $value );
	}

	/**
	 * @param $sin
	 * @return bool
	 */
	function isUniqueSIN( $sin ) {
		if ( $sin == '' ) {
			return true;
		}

		$ph = [
				'company_id'      => TTUUID::castUUID( $this->getCompany() ),
				'legal_entity_id' => TTUUID::castUUID( $this->getLegalEntity() ),
				'country_id'      => $this->getCountry(),
				'sin'             => $sin,
		];

		// Unique to company, legal_entity and country.
		$query = 'select id from ' . $this->getTable() . ' where company_id = ? AND legal_entity_id = ? AND country = ? AND sin = ? AND deleted = 0';

		$user_id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $user_id, 'Unique SIN: ' . $sin, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $user_id === false ) {
			return true;
		} else {
			if ( $user_id == $this->getId() ) {
				return true;
			}
		}

		return false;
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
	function setOtherID1( $value ) {
		$value = trim( $value );

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
	function setOtherID2( $value ) {
		$value = trim( $value );

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
	function setOtherID3( $value ) {
		$value = trim( $value );

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
	function setOtherID4( $value ) {
		$value = trim( $value );

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
	function setOtherID5( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'other_id5', $value );
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
		$value = trim( $value );

		return $this->setGenericDataValue( 'note', $value );
	}


	/**
	 * @param bool $value
	 * @return bool
	 */
	function setEnableLogin( $value = true ) {
		return $this->setGenericDataValue( 'enable_login', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getEnableLogin() {
		return $this->fromBool( $this->getGenericDataValue( 'enable_login' ) );
	}

	/**
	 * @param bool $raw
	 * @return bool|mixed
	 */
	function getLoginExpireDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'login_expire_date' );
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
	 * @param $value
	 * @return bool
	 */
	function setLoginExpireDate( $value ) {
		//Assumed to be end of day of the the expire date.
		if ( $value == '' ) {
			$value = null; //Force to NULL if no expire date is set, this prevents "0" from being entered and causing problems with "is NULL" SQL queries.
		}

		return $this->setGenericDataValue( 'login_expire_date', ( $value != 0 && $value != '' ) ? TTDate::getISODateStamp( $value ) : null );
	}

	/**
	 * @return bool|mixed
	 */
	function getTerminatedPermissionControl() {
		return $this->getGenericDataValue( 'terminated_permission_control_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setTerminatedPermissionControl( $value ) {
		return $this->setGenericDataValue( 'terminated_permission_control_id', TTUUID::castUUID( $value ) );
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	function sendValidateEmail( $type = 'work' ) {
		if ( $this->getHomeEmail() != false
				|| $this->getWorkEmail() != false ) {

			if ( $this->getWorkEmail() != false && $type == 'work' ) {
				$primary_email = $this->getWorkEmail();
			} else if ( $this->getHomeEmail() != false && $type == 'home' ) {
				$primary_email = $this->getHomeEmail();
			} else {
				Debug::text( 'ERROR: Home/Work email not defined or matching type, unable to send validation email...', __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}

			if ( $type == 'work' ) {
				$this->setWorkEmailIsValidKey( sha1( Misc::getUniqueID() ) );
				$this->setWorkEmailIsValidDate( time() );
				$email_is_valid_key = $this->getWorkEmailIsValidKey();
			} else {
				$this->setHomeEmailIsValidKey( sha1( Misc::getUniqueID() ) );
				$this->setHomeEmailIsValidDate( time() );
				$email_is_valid_key = $this->getHomeEmailIsValidKey();
			}

			if ( $this->isValid() ) {
				$this->Save( false );

				$subject = APPLICATION_NAME . ' - ' . TTi18n::gettext( 'Confirm email address' );

				$body = '<html><body>';
				$body .= TTi18n::gettext( 'The email address %1 has been added to your %2 account', [ $primary_email, APPLICATION_NAME ] ) . ', ';
				$body .= ' <a href="' . Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getBaseURL() . 'html5/ConfirmEmail.php?action:confirm_email=1&email=' . $primary_email . '&key=' . $email_is_valid_key . '">' . TTi18n::gettext( 'please click here to confirm and activate this email address' ) . '</a>.';
				$body .= '<br><br>';
				$body .= '--<br>';
				$body .= APPLICATION_NAME;
				$body .= '</body></html>';

				TTLog::addEntry( $this->getId(), 500, TTi18n::getText( 'Employee email confirmation sent for' ) . ': ' . $primary_email, null, $this->getTable() );

				$headers = [
						'From'                      => '"' . APPLICATION_NAME . ' - ' . TTi18n::gettext( 'Email Confirmation' ) . '" <' . Misc::getEmailLocalPart() . '@' . Misc::getEmailDomain() . '>',
						'Subject'                   => $subject,
						'X-TimeTrex-Email-Validate' => 'YES', //Help filter validation emails.
				];

				$mail = new TTMail();
				$mail->setTo( Misc::formatEmailAddress( $primary_email, $this ) );
				$mail->setHeaders( $headers );

				@$mail->getMIMEObject()->setHTMLBody( $body );

				$mail->setBody( $mail->getMIMEObject()->get( $mail->default_mime_config ) );
				$retval = $mail->Send();

				return $retval;
			}
		}

		return false;
	}

	/**
	 * @param $key
	 * @return string
	 */
	function encryptPasswordResetKey( $key ) {
		$retval = sha1( $key . TTPassword::getPasswordSalt() );

		return $retval;
	}

	/**
	 * @param $key
	 * @return bool
	 */
	function checkPasswordResetKey( $key ) {
		if ( $this->getPasswordResetDate() != ''
				&& $this->getPasswordResetDate() > ( time() - 7200 )
				&& $this->getPasswordResetKey() == $this->encryptPasswordResetKey( $key ) ) {

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function sendPasswordResetEmail() {
		if ( $this->getHomeEmail() != false
				|| $this->getWorkEmail() != false ) {

			if ( $this->getWorkEmail() != false ) {
				$primary_email = $this->getWorkEmail();
				if ( $this->getHomeEmail() != false ) {
					$secondary_email = $this->getHomeEmail();
				} else {
					$secondary_email = null;
				}
			} else {
				$primary_email = $this->getHomeEmail();
				$secondary_email = null;
			}

			$password_reset_key = sha1( Misc::getUniqueID() );
			$this->setPasswordResetKey( $this->encryptPasswordResetKey( $password_reset_key ) ); //Encrypt the password reset key in the database so if it every gets compromised through SQL injection or other methods, it can be used directly to a reset password.
			$this->setPasswordResetDate( time() );
			if ( $this->isValid() ) {
				$this->Save( false );

				$subject = APPLICATION_NAME . ' ' . TTi18n::gettext( 'password reset requested at' ) . ' ' . TTDate::getDate( 'DATE+TIME', time() ) . ' ' . TTi18n::gettext( 'from' ) . ' ' . Misc::getRemoteIPAddress();
				$body = '<html><body>';
				$body .= TTi18n::gettext( 'A password reset has been requested for username' ) . ' "' . $this->getUserName() . '", ';
				$body .= ' <a href="' . Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getBaseURL() . 'html5/?desktop=1#!sm=ResetPassword&key=' . $password_reset_key . '">' . TTi18n::gettext( 'please click here to reset your password now' ) . '</a>.';
				$body .= '<br><br>';
				$body .= TTi18n::gettext( 'If you did not request your password to be reset, you may ignore this email.' );
				$body .= '<br><br>';
				$body .= '--<br>';
				$body .= APPLICATION_NAME;
				$body .= '</body></html>';

				//Don't record the reset key in the audit log for security reasons.
				TTLog::addEntry( $this->getId(), 500, TTi18n::getText( 'Employee Password Reset By' ) . ': ' . Misc::getRemoteIPAddress(), null, $this->getTable() );

				$headers = [
						'From'    => '"' . APPLICATION_NAME . ' - ' . TTi18n::gettext( 'Password Reset' ) . '" <' . Misc::getEmailLocalPart() . '@' . Misc::getEmailDomain() . '>',
						'Subject' => $subject,
						'Cc'      => ( $secondary_email != '' ) ? Misc::formatEmailAddress( $secondary_email, $this ) : null,
				];

				$mail = new TTMail();
				$mail->setTo( Misc::formatEmailAddress( $primary_email, $this ) );
				$mail->setHeaders( $headers );

				@$mail->getMIMEObject()->setHTMLBody( $body );
				$mail->setDefaultTXTBody();

				$mail->setBody( $mail->getMIMEObject()->get( $mail->default_mime_config ) );
				$retval = $mail->Send();

				return $retval;
			}
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getPasswordResetKey() {
		return $this->getGenericDataValue( 'password_reset_key' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPasswordResetKey( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'password_reset_key', $value );
	}

	/**
	 * @return mixed
	 */
	function getPasswordResetDate() {
		return $this->getGenericDataValue( 'password_reset_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setPasswordResetDate( $value ) {
		return $this->setGenericDataValue( 'password_reset_date', $value );
	}

	/**
	 * @param bool $value
	 * @return bool
	 */
	function setEnableClearPasswordResetData( $value = true ) {
		return $this->setGenericTempDataValue( 'enable_clear_password_reset_data', $value );
	}

	/**
	 * @return bool
	 */
	function getEnableClearPasswordResetData() {
		return $this->getGenericTempDataValue( 'enable_clear_password_reset_data' );
	}

	/**
	 * @return bool
	 */
	function isPhotoExists() {
		return file_exists( $this->getPhotoFileName( null, null, false ) );
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @param bool $include_default_photo
	 * @return bool|string
	 */
	function getPhotoFileName( $company_id = null, $user_id = null, $include_default_photo = true ) {
		if ( $user_id == null ) {
			$user_id = $this->getId();
		}

		//Test for both jpg and png
		$base_name = $this->getStoragePath( $company_id ) . DIRECTORY_SEPARATOR . $user_id;
		if ( file_exists( $base_name . '.jpg' ) ) {
			$photo_file_name = $base_name . '.jpg';
		} else if ( file_exists( $base_name . '.png' ) ) {
			$photo_file_name = $base_name . '.png';
		} else if ( file_exists( $base_name . '.img' ) ) {
			$photo_file_name = $base_name . '.img';
		} else {
			if ( $include_default_photo == true ) {
				//$photo_file_name = Environment::getImagesPath().'unknown_photo.png';
				$photo_file_name = Environment::getImagesPath() . 's.gif';
			} else {
				return false;
			}
		}

		//Debug::Text('Logo File Name: '. $photo_file_name .' Base Name: '. $base_name .' User ID: '. $user_id .' Include Default: '. (int)$include_default_photo, __FILE__, __LINE__, __METHOD__, 10);
		return $photo_file_name;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @return bool
	 */
	function cleanStoragePath( $company_id = null, $user_id = null ) {
		if ( $company_id == '' ) {
			$company_id = $this->getCompany();
		}

		if ( $company_id == '' ) {
			return false;
		}

		$dir = $this->getStoragePath( $company_id ) . DIRECTORY_SEPARATOR;
		if ( $dir != '' ) {
			if ( $user_id != '' ) {
				@unlink( $this->getPhotoFileName( $company_id, $user_id, false ) ); //Delete just users photo.
			} else {
				//Delete tmp files.
				foreach ( glob( $dir . '*' ) as $filename ) {
					unlink( $filename );
					Misc::deleteEmptyParentDirectory( dirname( $filename ), 0 ); //Recurse to $user_id parent level and remove empty directories.
				}
			}
		}

		return true;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @return bool|string
	 */
	function getStoragePath( $company_id = null, $user_id = null ) {
		if ( $company_id == '' ) {
			$company_id = $this->getCompany();
		}

		if ( $company_id == '' ) {
			return false;
		}

		return Environment::getStorageBasePath() . DIRECTORY_SEPARATOR . 'user_photo' . DIRECTORY_SEPARATOR . $company_id;
	}

	/**
	 * @return bool|string
	 */
	function getTag() {
		//Check to see if any temporary data is set for the tags, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		$value = $this->getGenericTempDataValue( 'tags' );
		if ( $value !== false ) {
			return $value;
		} else if ( TTUUID::isUUID( $this->getCompany() ) && $this->getCompany() != TTUUID::getZeroID() && $this->getCompany() != TTUUID::getNotExistID()
				&& TTUUID::isUUID( $this->getID() ) && $this->getID() != TTUUID::getZeroID() && $this->getID() != TTUUID::getNotExistID() ) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 200, $this->getID() );
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTag( $value ) {
		$value = trim( $value );

		//Save the tags in temporary memory to be committed in postSave()
		return $this->setGenericTempDataValue( 'tags', $value );
	}

	/**
	 * @param $email
	 * @return bool
	 */
	static function UnsubscribeEmail( $email ) {
		$email = TTi18n::strtolower( trim( $email ) );

		try {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$ulf->getByHomeEmailOrWorkEmail( $email );
			if ( $ulf->getRecordCount() > 0 ) {
				foreach ( $ulf as $u_obj ) {
					Debug::Text( 'Unsubscribing: ' . $email . ' User ID: ' . $u_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );
					if ( TTi18n::strtolower( $u_obj->getWorkEmail() ) == $email && $u_obj->getWorkEmailIsValid() == true ) {
						//$u_obj->setWorkEmail( '' );
						$u_obj->setWorkEmailIsValid( false );
						$u_obj->sendValidateEmail( 'work' );
					}

					if ( TTi18n::strtolower( $u_obj->getHomeEmail() ) == $email && $u_obj->getHomeEmailIsValid() == true ) {
						//$u_obj->setHomeEmail( '' );
						$u_obj->setHomeEmailIsValid( false );
						$u_obj->sendValidateEmail( 'home' );
					}

					TTLog::addEntry( $u_obj->getId(), 500, TTi18n::gettext( 'Requiring validation for invalid or bouncing email address' ) . ': ' . $email, $u_obj->getId(), 'users' );
					if ( $u_obj->isValid() ) {
						$u_obj->Save();
					}
				}

				return true;
			}
		} catch ( Exception $e ) {
			unset( $e ); //code standards
			Debug::text( 'ERROR: Unable to unsubscribe email: ' . $email, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * Check if the current user record is also for the currently logged in user.
	 * @return bool
	 */
	function isCurrentlyLoggedInUser() {
		global $current_user;
		if ( is_object( $current_user ) && $current_user->getID() == $this->getID() ) {
			return true;
		}

		return false;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		$data_diff = $this->getDataDifferences();

		//
		// BELOW: Validation code moved from set*() functions.
		//

		// Company
		if ( TTUUID::isUUID( $this->getCompany() ) == false || $this->getCompany() == TTUUID::getZeroID() || $this->getCompany() == TTUUID::getNotExistID() ) {
			$this->Validator->isTrue( 'company_id',
									  false,
									  TTi18n::gettext( 'Company must be specified' ) );
		}
		if ( $this->getCompany() !== false && $this->Validator->isError( 'company' ) == false ) {
			$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
			$this->Validator->isResultSetWithRows( 'company_id',
												   $clf->getByID( $this->getCompany() ),
												   TTi18n::gettext( 'Company is invalid' )
			);
		}

		// Legal entity
		if ( $this->getLegalEntity() !== false && ( ( $this->is_new == true && TTUUID::isUUID( $this->getLegalEntity() ) == false ) || $this->getLegalEntity() == TTUUID::getZeroID() || $this->getLegalEntity() == TTUUID::getNotExistID() ) ) {
			$this->Validator->isTrue( 'legal_entity_id',
									  false,
									  TTi18n::gettext( 'Legal entity must be specified' ) );
		}
		if ( $this->getLegalEntity() !== false && $this->Validator->isError( 'legal_entity_id' ) == false ) {
			$clf = TTnew( 'LegalEntityListFactory' ); /** @var LegalEntityListFactory $clf */
			$this->Validator->isResultSetWithRows( 'legal_entity_id',
												   $clf->getByID( $this->getLegalEntity() ),
												   TTi18n::gettext( 'Legal entity is invalid' )
			);
		}

		// Status
		if ( $this->getStatus() !== false ) {
			$this->Validator->isTrue( 'status_id',
									  $this->getStatus(),
									  TTi18n::gettext( 'Insufficient access to modify status for this employee' )
			);
			if ( $this->Validator->isError( 'status_id' ) == false ) {
				$this->Validator->inArrayKey( 'status_id',
											  $this->getStatus(),
											  TTi18n::gettext( 'Incorrect Status' ),
											  $this->getOptions( 'status' )
				);
			}
		}
		// Group
		if ( $this->getGroup() !== false && $this->getGroup() != TTUUID::getZeroID() ) {
			$uglf = TTnew( 'UserGroupListFactory' ); /** @var UserGroupListFactory $uglf */
			$this->Validator->isResultSetWithRows( 'group',
												   $uglf->getByID( $this->getGroup() ),
												   TTi18n::gettext( 'Group is invalid' )
			);
		}

		//Used for validating Permission and TerminatedPermissions below.
		$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
		$current_user_permission_level = $this->getCurrentUserPermissionLevel();

		$modify_permissions = false;
		if ( $current_user_permission_level >= $this->getPermissionLevel() ) {
			$modify_permissions = true;
		}

		// Permission Group
		//Don't allow permissions to be modified if the currently logged in user has a lower permission level.
		//As such if someone with a lower level is able to edit the user of higher level, they must not call this function at all, or use a blank value.
		if ( $this->getPermissionControl() != '' ) {
			if ( $this->Validator->isError( 'permission_control_id' ) == false ) {
				$this->Validator->isTrue( 'permission_control_id',
										  $modify_permissions,
										  TTi18n::gettext( 'Insufficient access to modify permissions for this employee' )
				);
			}

			if ( $this->isCurrentlyLoggedInUser() == true && $this->getPermissionControl() != $this->getPermissionControl( true ) ) { //Acting on currently logged in user.
				$logged_in_modify_permissions = false;                                                                                 //Must be false for validation to fail.
			} else {
				$logged_in_modify_permissions = true;
			}
			if ( $this->Validator->isError( 'permission_control_id' ) == false ) {
				$this->Validator->isTrue( 'permission_control_id',
										  $logged_in_modify_permissions,
										  TTi18n::gettext( 'Unable to change permissions of your own record' )
				);
			}

			if ( $this->Validator->isError( 'permission_control_id' ) == false ) { //Put this last, because if the user doesn't have access to modify permissions, they see the more specific error above.
				$this->Validator->isResultSetWithRows( 'permission_control_id',
													   $pclf->getByIDAndLevel( $this->getPermissionControl(), $current_user_permission_level ),
													   TTi18n::gettext( 'Permission Group is invalid' )
				);
			}
		}

		//Allow Terminated Permission Group to be NONE (Zero UUID) only if the user is active.
		if ( $this->getTerminatedPermissionControl() != '' && ( ( $this->getStatus() == 10 && $this->getTerminatedPermissionControl() != TTUUID::getZeroID() ) || $this->getStatus() != 10 ) ) {
			//When validating for mass edit, we don't know what the user_id is yet, so skip this check as it will always fail.
			if ( $this->Validator->getValidateOnly() == false && $this->Validator->isError( 'terminated_permission_control_id' ) == false
					&& $this->getPermissionControl() != '' && $this->getPermissionControl() != TTUUID::getZeroID() && $this->getTerminatedPermissionLevel() > $this->getPermissionLevel() ) {
				$this->Validator->isTrue( 'terminated_permission_control_id',
										  false,
										  TTi18n::gettext( 'Terminated Permission Group cannot be a higher level than Permission Group' )
				);
			}

			if ( $this->Validator->getValidateOnly() == false && $this->Validator->isError( 'terminated_permission_control_id' ) == false ) {
				$this->Validator->isResultSetWithRows( 'terminated_permission_control_id',
													   $pclf->getByIDAndLevel( $this->getTerminatedPermissionControl(), $current_user_permission_level ),
													   TTi18n::gettext( 'Terminated Permission Group is invalid' )
				);
			}
		}


		// Pay Period schedule
		if ( $this->getPayPeriodSchedule() !== false && $this->getPayPeriodSchedule() != TTUUID::getZeroID() ) {
			$ppslf = TTnew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $ppslf */
			$this->Validator->isResultSetWithRows( 'pay_period_schedule_id',
												   $ppslf->getByID( $this->getPayPeriodSchedule() ),
												   TTi18n::gettext( 'Pay Period schedule is invalid' )
			);
		}
		// Policy Group
		if ( $this->getPolicyGroup() !== false && $this->getPolicyGroup() != TTUUID::getZeroID() ) {
			$pglf = TTnew( 'PolicyGroupListFactory' ); /** @var PolicyGroupListFactory $pglf */
			$this->Validator->isResultSetWithRows( 'policy_group_id',
												   $pglf->getByID( $this->getPolicyGroup() ),
												   TTi18n::gettext( 'Policy Group is invalid' )
			);
		}

		// Hierarchy
		if ( $this->getHierarchyControl() !== false && is_array( $this->getHierarchyControl() ) ) {
			$hclf = TTnew( 'HierarchyControlListFactory' ); /** @var HierarchyControlListFactory $hclf */
			foreach ( $this->getHierarchyControl() as $hierarchy_control_id ) {
				$hierarchy_control_id = Misc::trimSortPrefix( $hierarchy_control_id );

				if ( $hierarchy_control_id != TTUUID::getZeroID() ) {
					$this->Validator->isResultSetWithRows( 'hierarchy_control_id',
														   $hclf->getByID( $hierarchy_control_id ),
														   TTi18n::gettext( 'Hierarchy is invalid' )
					);
				}
			}
		}

		//Prevent supervisor (subordinates only) from creating employee records without a hierarchy, as its likely they won't be able to view them anyways.
		if ( $this->getDeleted() == false ) {
			global $current_user;
			// Ignore this check if the supervisor is modifying their own record.
			if ( isset( $current_user ) && is_object( $current_user ) && $this->getId() != $current_user->getId() ) {
				if ( $this->getPermissionObject()->Check( 'user', 'view_child', $current_user->getId(), $current_user->getCompany() ) == true && $this->getPermissionObject()->Check( 'user', 'view', $current_user->getId(), $current_user->getCompany() ) == false ) {
					Debug::text( 'Detected Supervisor (Subordinates Only), ensure a proper hierarchy is specified...', __FILE__, __LINE__, __METHOD__, 10 );
					if ( $this->getHierarchyControl() === false ) {
						$this->Validator->isTrue( '100',
												  false,
												  TTi18n::gettext( 'Hierarchy not specified' )
						);
					} else {
						//TODO: Loop through each specified hierarchy and ensure the current user is a superior in it. See APIHierarchyControl->getHierarchyControlOptions() for code on how to get the valid hierarchies.
						$hierarchy_control_arr = $this->getHierarchyControl();
						if ( !isset( $hierarchy_control_arr[100] ) || ( isset( $hierarchy_control_arr[100] ) && $hierarchy_control_arr[100] == TTUUID::getZeroID() ) ) {
							$this->Validator->isTrue( '100',
													  false,
													  TTi18n::gettext( 'Permission Hierarchy not specified' )
							);
						}

						unset( $hierarchy_control_arr, $hierarchy_object_type_id, $hierarchy_control_id );
					}
				}
			}
		}


		// User name
		//When doing a mass edit of employees, user name is never specified, so we need to avoid this validation issue.
		if ( $this->getUserName() == '' ) {
			$this->Validator->isTrue( 'user_name',
									  false,
									  TTi18n::gettext( 'User name not specified' )
			);
		}
		if ( $this->Validator->isError( 'user_name' ) == false ) {
			$this->Validator->isRegEx( 'user_name',
									   $this->getUserName(),
									   TTi18n::gettext( 'Incorrect characters in user name' ),
									   $this->username_validator_regex
			);
		}
		if ( $this->Validator->isError( 'user_name' ) == false ) {
			$this->Validator->isLength( 'user_name',
										$this->getUserName(),
										TTi18n::gettext( 'Incorrect user name length' ),
										3,
										250
			);
		}
		if ( $this->getDeleted() == false && $this->Validator->isError( 'user_name' ) == false ) {
			$this->Validator->isTrue( 'user_name',
									  $this->isUniqueUserName( $this->getUserName() ),
									  TTi18n::gettext( 'User name is already taken' )
			);
		}
		// Password updated date
		if ( $this->getPasswordUpdatedDate() != '' ) {
			$this->Validator->isDate( 'password_updated_date',
									  $this->getPasswordUpdatedDate(),
									  TTi18n::gettext( 'Password updated date is invalid' )
			);
		}
		// Quick Punch ID
		if ( $this->getPhoneId() != '' ) {
			$this->Validator->isRegEx( 'phone_id',
									   $this->getPhoneId(),
									   TTi18n::gettext( 'Quick Punch ID must be digits only' ),
									   $this->phoneid_validator_regex
			);
			if ( $this->Validator->isError( 'phone_id' ) == false ) {
				$this->Validator->isLength( 'phone_id',
											$this->getPhoneId(),
											TTi18n::gettext( 'Incorrect Quick Punch ID length' ),
											4,
											8
				);
			}
			if ( $this->getDeleted() == false && $this->Validator->isError( 'phone_id' ) == false ) {
				$this->Validator->isTrue( 'phone_id',
										  $this->isUniquePhoneId( $this->getPhoneId() ),
										  TTi18n::gettext( 'Quick Punch ID is already in use, please try a different one' )
				);
			}
		}

		if ( $this->getDeleted() == false && $this->getPhonePassword() != '' ) {
			//Phone passwords are now displayed to the administrators to make things easier.
			//NOTE: Phone passwords are used for passwords on the timeclock as well, and need to be able to be cleared sometimes.
			//Limit phone password to max of 9 digits so we don't overflow an integer on the timeclocks. (10 digits, but maxes out at 2billion)
			$this->Validator->isRegEx( 'phone_password',
									   $this->getPhonePassword(),
									   TTi18n::gettext( 'Quick Punch password must be digits only' ),
									   $this->phonepassword_validator_regex );

			$this->Validator->isLength( 'phone_password',
										$this->getPhonePassword(),
										TTi18n::gettext( 'Quick Punch password must be between 4 and 9 digits' ),
										4,
										9 );

			$this->Validator->isTrue( 'phone_password',
					( ( DEMO_MODE == false && ( $this->is_new == true || $this->isDataDifferent( 'phone_password', $data_diff ) || $this->getCreatedDate() >= strtotime( '2019-07-01' ) ) && ( $this->getPhoneId() == $this->getPhonePassword() ) ) ? false : true ),
									  TTi18n::gettext( 'Quick Punch password must be different then Quick Punch ID' ) );

			$this->Validator->isTrue( 'phone_password',
					( ( DEMO_MODE == false && ( $this->is_new == true || $this->isDataDifferent( 'phone_password', $data_diff ) || $this->getCreatedDate() >= strtotime( '2019-07-01' ) ) && ( in_array( $this->getPhonePassword(), [ '1234', '12345', '123456', '1234567', '12345678', '123456789', '987654321', '87654321', '7654321', '654321', '54321', '4321' ] ) || strrev( $this->getPhoneId() ) == $this->getPhonePassword() || strlen( count_chars( $this->getPhonePassword(), 3 ) ) == 1 ) ) ? false : true ),
									  TTi18n::gettext( 'Quick Punch password is too weak, please try something more secure' ) );
		}

		// Employee number
		if ( $this->getEmployeeNumber() != '' ) {
			$this->Validator->isNumeric( 'employee_number',
										 $this->getEmployeeNumber(),
										 TTi18n::gettext( 'Employee number must only be digits' )
			);
			if ( $this->getDeleted() == false && $this->Validator->isError( 'employee_number' ) == false ) {
				$this->Validator->isTrue( 'employee_number',
										  $this->isUniqueEmployeeNumber( $this->getEmployeeNumber() ),
										  TTi18n::gettext( 'Employee number is already in use, please enter a different one' )
				);
			}
		}
		// Title
		if ( $this->getTitle() !== false && $this->getTitle() != TTUUID::getZeroID() ) {
			$utlf = TTnew( 'UserTitleListFactory' ); /** @var UserTitleListFactory $utlf */
			$this->Validator->isResultSetWithRows( 'title',
												   $utlf->getByID( $this->getTitle() ),
												   TTi18n::gettext( 'Title is invalid' )
			);
		}
		// Ethnic Group
		if ( $this->getEthnicGroup() !== false && $this->getEthnicGroup() != TTUUID::getZeroID() ) {
			$eglf = TTnew( 'EthnicGroupListFactory' ); /** @var EthnicGroupListFactory $eglf */
			$this->Validator->isResultSetWithRows( 'ethnic_group',
												   $eglf->getById( $this->getEthnicGroup() ),
												   TTi18n::gettext( 'Ethnic Group is invalid' )
			);
		}
		// Default Job
		if ( $this->getDefaultJob() !== false && $this->getDefaultJob() != TTUUID::getZeroID() ) {
			$jlf = TTnew( 'JobListFactory' ); /** @var JobListFactory $jlf */
			$this->Validator->isResultSetWithRows( 'default_job_id',
												   $jlf->getByID( $this->getDefaultJob() ),
												   TTi18n::gettext( 'Invalid Default Job' )
			);
		}
		// Default Task
		if ( $this->getDefaultJobItem() !== false && $this->getDefaultJobItem() != TTUUID::getZeroID() ) {
			$jilf = TTnew( 'JobItemListFactory' ); /** @var JobItemListFactory $jilf */
			$this->Validator->isResultSetWithRows( 'default_job_item_id',
												   $jilf->getByID( $this->getDefaultJobItem() ),
												   TTi18n::gettext( 'Invalid Default Task' )
			);
		}
		// Default Branch
		if ( $this->getDefaultBranch() !== false && $this->getDefaultBranch() != TTUUID::getZeroID() ) {
			$blf = TTnew( 'BranchListFactory' ); /** @var BranchListFactory $blf */
			$this->Validator->isResultSetWithRows( 'default_branch',
												   $blf->getByID( $this->getDefaultBranch() ),
												   TTi18n::gettext( 'Invalid Default Branch' )
			);
		}
		// Default Department
		if ( $this->getDefaultDepartment() !== false && $this->getDefaultDepartment() != TTUUID::getZeroID() ) {
			$dlf = TTnew( 'DepartmentListFactory' ); /** @var DepartmentListFactory $dlf */
			$this->Validator->isResultSetWithRows( 'default_department',
												   $dlf->getByID( $this->getDefaultDepartment() ),
												   TTi18n::gettext( 'Invalid Default Department' )
			);
		}
		// First name
		if ( $this->getFirstName() !== false ) {
			$this->Validator->isRegEx( 'first_name',
									   $this->getFirstName(),
									   TTi18n::gettext( 'First name contains invalid characters' ),
									   $this->name_validator_regex
			);
			if ( $this->Validator->isError( 'first_name' ) == false ) {
				$this->Validator->isLength( 'first_name',
											$this->getFirstName(),
											TTi18n::gettext( 'First name is too short or too long' ),
											2,
											50
				);
			}
		}
		// Middle name
		if ( $this->getMiddleName() != '' ) {
			$this->Validator->isRegEx( 'middle_name',
									   $this->getMiddleName(),
									   TTi18n::gettext( 'Middle name contains invalid characters' ),
									   $this->name_validator_regex
			);
			if ( $this->Validator->isError( 'middle_name' ) == false ) {
				$this->Validator->isLength( 'middle_name',
											$this->getMiddleName(),
											TTi18n::gettext( 'Middle name is too short or too long' ),
											1,
											50
				);
			}
		}
		// Last name
		if ( $this->getLastName() !== false ) {
			$this->Validator->isRegEx( 'last_name',
									   $this->getLastName(),
									   TTi18n::gettext( 'Last name contains invalid characters' ),
									   $this->name_validator_regex
			);
			if ( $this->Validator->isError( 'last_name' ) == false ) {
				$this->Validator->isLength( 'last_name',
											$this->getLastName(),
											TTi18n::gettext( 'Last name is too short or too long' ),
											2,
											50 );
			}
		}
		// Second last name
		if ( $this->getSecondLastName() != '' ) {
			$this->Validator->isRegEx( 'second_last_name',
									   $this->getSecondLastName(),
									   TTi18n::gettext( 'Second last name contains invalid characters' ),
									   $this->name_validator_regex
			);
			if ( $this->Validator->isError( 'second_last_name' ) == false ) {
				$this->Validator->isLength( 'second_last_name',
											$this->getSecondLastName(),
											TTi18n::gettext( 'Second last name is too short or too long' ),
											2,
											50 );
			}
		}
		// Gender
		if ( $this->getSex() !== false ) {
			$this->Validator->inArrayKey( 'sex',
										  $this->getSex(),
										  TTi18n::gettext( 'Invalid gender' ),
										  $this->getOptions( 'sex' )
			);
		}
		// Address1
		if ( $this->getAddress1() != '' ) {
			$this->Validator->isRegEx( 'address1',
									   $this->getAddress1(),
									   TTi18n::gettext( 'Address1 contains invalid characters' ),
									   $this->address_validator_regex
			);
			if ( $this->Validator->isError( 'address1' ) == false ) {
				$this->Validator->isLength( 'address1',
											$this->getAddress1(),
											TTi18n::gettext( 'Address1 is too short or too long' ),
											2,
											250
				);
			}
		}
		// Address2
		if ( $this->getAddress2() != '' ) {
			$this->Validator->isRegEx( 'address2',
									   $this->getAddress2(),
									   TTi18n::gettext( 'Address2 contains invalid characters' ),
									   $this->address_validator_regex
			);
			if ( $this->Validator->isError( 'address2' ) == false ) {
				$this->Validator->isLength( 'address2',
											$this->getAddress2(),
											TTi18n::gettext( 'Address2 is too short or too long' ),
											2,
											250
				);
			}
		}
		// City
		if ( $this->getCity() != '' ) {
			$this->Validator->isRegEx( 'city',
									   $this->getCity(),
									   TTi18n::gettext( 'City contains invalid characters' ),
									   $this->city_validator_regex
			);
			if ( $this->Validator->isError( 'city' ) == false ) {
				$this->Validator->isLength( 'city',
											$this->getCity(),
											TTi18n::gettext( 'City name is too short or too long' ),
											2,
											250
				);
			}
		}
		// Country
		$cf = TTnew( 'CompanyFactory' ); /** @var CompanyFactory $cf */
		if ( $this->getCountry() !== false ) {
			$this->Validator->inArrayKey( 'country',
										  $this->getCountry(),
										  TTi18n::gettext( 'Invalid Country' ),
										  $cf->getOptions( 'country' )
			);
		}
		// Province/State
		if ( $this->getCountry() !== false ) {
			$options_arr = $cf->getOptions( 'province' );
			if ( isset( $options_arr[$this->getCountry()] ) ) {
				$options = $options_arr[$this->getCountry()];
			} else {
				$options = [];
			}
			$this->Validator->inArrayKey( 'province',
										  $this->getProvince(),
										  TTi18n::gettext( 'Invalid Province/State' ),
										  $options
			);
		}
		// Postal/ZIP Code
		if ( $this->getPostalCode() != '' ) {
			$this->Validator->isPostalCode( 'postal_code',
											$this->getPostalCode(),
											TTi18n::gettext( 'Postal/ZIP Code contains invalid characters, invalid format, or does not match Province/State' ),
											$this->getCountry(), $this->getProvince()
			);
			if ( $this->Validator->isError( 'postal_code' ) == false ) {
				$this->Validator->isLength( 'postal_code',
											$this->getPostalCode(),
											TTi18n::gettext( 'Postal/ZIP Code is too short or too long' ),
											1,
											10
				);
			}
		}
		// Longitude
		if ( $this->getLongitude() != '' ) {
			$this->Validator->isFloat( 'longitude',
									   $this->getLongitude(),
									   TTi18n::gettext( 'Longitude is invalid' )
			);
		}
		// Latitude
		if ( $this->getLatitude() != '' ) {
			$this->Validator->isFloat( 'latitude',
									   $this->getLatitude(),
									   TTi18n::gettext( 'Latitude is invalid' )
			);
		}
		// Work phone number
		if ( $this->getWorkPhone() != '' ) {
			$this->Validator->isPhoneNumber( 'work_phone',
											 $this->getWorkPhone(),
											 TTi18n::gettext( 'Work phone number is invalid' )
			);
		}
		// Work phone number extension
		if ( $this->getWorkPhoneExt() != '' ) {
			$this->Validator->isLength( 'work_phone_ext',
										$this->getWorkPhoneExt(),
										TTi18n::gettext( 'Work phone number extension is too short or too long' ),
										2,
										10
			);
		}
		// Home phone number
		if ( $this->getHomePhone() != '' ) {
			$this->Validator->isPhoneNumber( 'home_phone',
											 $this->getHomePhone(),
											 TTi18n::gettext( 'Home phone number is invalid' )
			);
		}
		// Mobile phone number
		if ( $this->getMobilePhone() != '' ) {
			$this->Validator->isPhoneNumber( 'mobile_phone',
											 $this->getMobilePhone(),
											 TTi18n::gettext( 'Mobile phone number is invalid' )
			);
		}
		// Fax phone number
		if ( $this->getFaxPhone() != '' ) {
			$this->Validator->isPhoneNumber( 'fax_phone',
											 $this->getFaxPhone(),
											 TTi18n::gettext( 'Fax phone number is invalid' )
			);
		}
		// Home Email address
		if ( $this->getHomeEmail() != '' ) {
			$modify_email = false;
			if ( $this->getCurrentUserPermissionLevel() >= $this->getPermissionLevel() ) {
				$modify_email = true;
			} else if ( $this->isDataDifferent( 'home_email', $data_diff ) == false ) { //No modification made.
				$modify_email = true;
			}

			$error_threshold = 7; //No DNS checks.
			if ( PRODUCTION === true && DEMO_MODE === false ) {
				$error_threshold = 0; //DNS checks on email address.
			}
			$this->Validator->isEmailAdvanced( 'home_email',
											   $this->getHomeEmail(),
											   [ 0 => TTi18n::gettext( 'Home email address is invalid' ), 5 => TTi18n::gettext( 'Home email address does not have a valid DNS MX record' ), 6 => TTi18n::gettext( 'Home email address does not have a valid DNS record' ) ],
											   $error_threshold
			);
			if ( $this->Validator->isError( 'home_email' ) == false ) {
				$this->Validator->isTrue( 'home_email',
										  $modify_email,
										  TTi18n::gettext( 'Insufficient access to modify home email for this employee' )
				);
			}
		}
		// Email validation key
		if ( $this->getHomeEmailIsValidKey() != '' ) {
			$this->Validator->isLength( 'home_email_is_valid_key',
										$this->getHomeEmailIsValidKey(),
										TTi18n::gettext( 'Email validation key is invalid' ),
										1, 255
			);
		}
		// Email validation date
		if ( $this->getHomeEmailIsValidDate() != '' ) {
			$this->Validator->isDate( 'home_email_is_valid_date',
									  $this->getHomeEmailIsValidDate(),
									  TTi18n::gettext( 'Email validation date is invalid' )
			);
		}
		// Work Email address
		if ( $this->getWorkEmail() != '' ) {
			$modify_email = false;
			if ( $this->getCurrentUserPermissionLevel() >= $this->getPermissionLevel() ) {
				$modify_email = true;
			} else if ( $this->isDataDifferent( 'work_email', $data_diff ) == false ) { //No modification made.
				$modify_email = true;
			}

			$error_threshold = 7; //No DNS checks.
			if ( PRODUCTION === true && DEMO_MODE === false ) {
				$error_threshold = 0; //DNS checks on email address.
			}

			$this->Validator->isEmailAdvanced( 'work_email',
											   $this->getWorkEmail(),
											   [ 0 => TTi18n::gettext( 'Work email address is invalid' ), 5 => TTi18n::gettext( 'Work email address does not have a valid DNS MX record' ), 6 => TTi18n::gettext( 'Work email address does not have a valid DNS record' ) ],
											   $error_threshold
			);
			if ( $this->Validator->isError( 'work_email' ) == false ) {
				$this->Validator->isTrue( 'work_email',
										  $modify_email,
										  TTi18n::gettext( 'Insufficient access to modify work email for this employee' )
				);
			}
		}
		// Email validation key
		if ( $this->getWorkEmailIsValidKey() != '' ) {
			$this->Validator->isLength( 'work_email_is_valid_key',
										$this->getWorkEmailIsValidKey(),
										TTi18n::gettext( 'Email validation key is invalid' ),
										1, 255
			);
		}
		// Email validation date
		if ( $this->getWorkEmailIsValidDate() != '' ) {
			$this->Validator->isDate( 'work_email_is_valid_date',
									  $this->getWorkEmailIsValidDate(),
									  TTi18n::gettext( 'Email validation date is invalid' )
			);
		}
		// Birth date
		if ( $this->getBirthDate() != '' ) {
			$this->Validator->isDate( 'birth_date',
									  $this->getBirthDate(),
									  TTi18n::gettext( 'Birth date is invalid, try specifying the year with four digits' )
			);
			if ( $this->Validator->isError( 'birth_date' ) == false ) {
				$this->Validator->isTRUE( 'birth_date',
										  ( TTDate::getMiddleDayEpoch( $this->getBirthDate() ) <= TTDate::getMiddleDayEpoch( time() ) ) ? true : false,
										  TTi18n::gettext( 'Birth date can not be in the future' )
				);
			}

			if ( $this->Validator->isError( 'birth_date' ) == false ) {
				$this->Validator->isTRUE( 'birth_date',
										  ( TTDate::getMiddleDayEpoch( $this->getBirthDate() ) < TTDate::getMiddleDayEpoch( $this->getHireDate() ) ) ? true : false,
										  TTi18n::gettext( 'Birth date can not be after hire date' )
				);
			}
		}

		// Hire date
		if ( $this->getHireDate() != '' ) {
			$this->Validator->isDate( 'hire_date',
									  $this->getHireDate(),
									  TTi18n::gettext( 'Hire date is invalid' )
			);
			if ( $this->Validator->isError( 'hire_date' ) == false ) {
				$this->Validator->isTrue( 'hire_date',
										  $this->isValidWageForHireDate( $this->getHireDate() ),
										  TTi18n::gettext( 'Hire date must be on or after the employees first wage entry, you may need to change their wage effective date first' ) );
			}
		}

		// Termination date
		if ( $this->getTerminationDate() != '' ) {
			$this->Validator->isDate( 'termination_date',
									  $this->getTerminationDate(),
									  TTi18n::gettext( 'Termination date is invalid' )
			);
		}

		// Login Expire date
		if ( $this->getLoginExpireDate() != '' ) {
			$this->Validator->isDate( 'login_expire_date',
									  $this->getLoginExpireDate(),
									  TTi18n::gettext( 'Login Expire date is invalid' )
			);

			if ( $this->getEnableLogin() == true && TTDate::getMiddleDayEpoch( $this->getLoginExpireDate() ) < TTDate::getMiddleDayEpoch( time() ) ) {
				$this->Validator->isTrue( 'login_expire_date',
										  false,
										  TTi18n::gettext( 'Login Expire Date must be in the future when Login is Enabled' ) );
			}

			//Avoid having the login expire date too far in the future due to mistakenly added dates. As well to avoid logins from being actively used for long periods of time while the user record is non-active.
			if ( $this->getEnableLogin() == true && TTDate::getMiddleDayEpoch( $this->getLoginExpireDate() ) > TTDate::getMiddleDayEpoch( TTDate::incrementDate( time(), 2, 'year' ) ) ) {
				$this->Validator->isTrue( 'login_expire_date',
										  false,
										  TTi18n::gettext( 'Login Expire Date can not be more than two years in the future' ) );
			}
		}

		//Make sure there isn't a case where the user record is terminated, logins are enabled, and no expire date exists.
		//  Which would essentially allow the user to login to their terminated record forever in the future.
		if ( $this->getEnableLogin() == true && $this->getStatus() != 10 && $this->getLoginExpireDate() == '' ) {
			$this->Validator->isTrue( 'login_expire_date',
									  false,
									  TTi18n::gettext( 'Login Expire Date must be specified for all non-Active employees who have their login enabled' ) );
		}

		// Last Login date
		if ( $this->getLastLoginDate() != '' ) {
			$this->Validator->isDate( 'last_login_date',
									  $this->getLastLoginDate(),
									  TTi18n::gettext( 'Last Login date is invalid' )
			);
		}

		// Currency
		if ( $this->getCurrency() !== false ) {
			$culf = TTnew( 'CurrencyListFactory' ); /** @var CurrencyListFactory $culf */
			$this->Validator->isResultSetWithRows( 'currency_id',
												   $culf->getByID( $this->getCurrency() ),
												   TTi18n::gettext( 'Invalid currency' )
			);
		}
		// SIN/SSN
		if ( $this->getSIN() !== false && $this->getSIN() != '' && DEMO_MODE !== true ) {
			$this->Validator->isSIN( 'sin',
									 $this->getSIN(),
									 TTi18n::gettext( 'SIN/SSN is invalid' ),
									 $this->getCountry()
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
		// Note
		if ( $this->getNote() != '' ) {
			$this->Validator->isLength( 'note',
										$this->getNote(),
										TTi18n::gettext( 'Note is too long' ),
										1,
										2048
			);
		}
		// Password reset key
		if ( $this->getPasswordResetKey() != '' ) {
			$this->Validator->isLength( 'password_reset_key',
										$this->getPasswordResetKey(),
										TTi18n::gettext( 'Password reset key is invalid' ),
										1, 255
			);
		}
		// Password reset date
		if ( $this->getPasswordResetDate() != '' ) {
			$this->Validator->isDate( 'password_reset_date',
									  $this->getPasswordResetDate(),
									  TTi18n::gettext( 'Password reset date is invalid' )
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		//Re-validate the province just in case the country was set AFTER the province.
		//$this->setProvince( $this->getProvince() );

		//When mass editing, don't require currency to be set.
		if ( $this->Validator->getValidateOnly() == false && $this->getCurrency() == false ) {
			$this->Validator->isTrue( 'currency_id',
									  false,
									  TTi18n::gettext( 'Invalid currency' ) );
		}

		if ( $this->getTerminationDate() != '' && $this->getHireDate() != '' && TTDate::getBeginDayEpoch( $this->getTerminationDate() ) < TTDate::getBeginDayEpoch( $this->getHireDate() ) ) {
			$this->Validator->isTrue( 'termination_date',
									  false,
									  TTi18n::gettext( 'Termination date is before hire date, consider removing the termination date entirely for re-hires' ) );
		}

		//Need to require password on new employees as the database column is NOT NULL.
		//However when mass editing, no IDs are set so this always fails during the only validation phase.
		if ( $this->Validator->getValidateOnly() == false && $this->is_new == true && ( $this->getPassword() == false || $this->getPassword() == '' ) ) {
			$this->setPassword( TTPassword::generateRandomPassword() ); //Default to just some random password instead of making the user decide.
		}

		if ( $this->Validator->getValidateOnly() == false && $this->getEmployeeNumber() == false && $this->getStatus() == 10 ) {
			$this->Validator->isTrue( 'employee_number',
									  false,
									  TTi18n::gettext( 'Employee number must be specified for ACTIVE employees' ) );
		}

		if ( $this->isCurrentlyLoggedInUser() == true ) { //Acting on currently logged in user -- This is FALSE when the user is resetting their password by email.
			//Require currently logged in user to specify their current password if they are updating secure fields. This is to ensure they don't leave their computer unattended and have a evil party come along and try to change their password or email address.
			if ( ( $this->isDataDifferent( 'password_updated_date', $data_diff ) || $this->isDataDifferent( 'work_email', $data_diff ) || $this->isDataDifferent( 'home_email', $data_diff ) || $this->isDataDifferent( 'phone_id', $data_diff ) || $this->isDataDifferent( 'phone_password', $data_diff ) || $this->isDataDifferent( 'user_name', $data_diff ) ) ) {
				$this->setIsRequiredCurrentPassword( true );
			}

			if ( $this->getIsRequiredCurrentPassword() == true ) {
				if ( $this->getCurrentPassword() == '' ) {
					$this->Validator->isTrue( 'current_password',
											  false,
											  TTi18n::gettext( 'Current password must be specified to change secure fields' ) );
				}

				if ( $this->getCurrentPassword() != '' && $this->checkPassword( $this->getCurrentPassword(), false ) == false ) {
					//When the user is changing passwords from the main web UI, there is Web Password and Quick Punch Password.
					//  When changing the Quick Punch Password, this can be confusing, so make sure if they enter their existing Quick Punch Password
					//  we are explicit in telling them they need to enter their web password instead.
					if ( $this->checkPhonePassword( $this->getCurrentPassword() ) ) {
						$this->Validator->isTrue( 'current_password',
												  false,
												  TTi18n::gettext( 'Please enter your web password rather than your quick punch password' ) );
					} else {
						$this->Validator->isTrue( 'current_password',
												  false,
												  TTi18n::gettext( 'Current password is incorrect' ) );
					}
				}
			}

			if ( $this->getDeleted() == true ) {
				$this->Validator->isTrue( 'user_name',
										  false,
										  TTi18n::gettext( 'Unable to delete your own record' ) );
			}

			if ( $this->getStatus() != 10 ) {
				$this->Validator->isTrue( 'status_id',
										  false,
										  TTi18n::gettext( 'Unable to change status of your own record' ) );
			}

			if ( $this->getEnableLogin() == false ) {
				$this->Validator->isTrue( 'enable_login',
										  false,
										  TTi18n::gettext( 'Unable to disable login on your own record' ) );
			}
		}

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE && $this->is_new == false ) {
			if ( TTUUID::isUUID( $this->getDefaultJob() ) && $this->getDefaultJob() != TTUUID::getZeroID() && $this->getDefaultJob() != TTUUID::getNotExistID() ) {
				$jlf = TTnew( 'JobListFactory' ); /** @var JobListFactory $jlf */
				$jlf->getById( $this->getDefaultJob() );
				if ( $jlf->getRecordCount() > 0 ) {
					$j_obj = $jlf->getCurrent();

					if ( $j_obj->isAllowedUser( $this->getID() ) == false ) {
						$this->Validator->isTRUE( 'default_job_id',
												  false,
												  TTi18n::gettext( 'Employee is not assigned to this job' ) );
					}

					if ( $j_obj->isAllowedItem( $this->getDefaultJobItem() ) == false ) {
						$this->Validator->isTRUE( 'default_job_item_id',
												  false,
												  TTi18n::gettext( 'Task is not assigned to this job' ) );
					}
				}
			}
		}

		if ( $this->getDeleted() == true && is_object( $this->getCompanyObject() ) && $this->getCompanyObject()->getStatus() == 10 && $this->getCompanyObject()->getDeleted() == false ) { //Only perform these checks if the company is active. Otherwise we can't delete records for cancelled companies.
			//Too many users are accidently deleting employee records still, even though we default to turning off Employee -> Delete permissions.
			// Therefore prevent them doing so if there are punches, timesheet data or pay stubs.

			if ( $this->getStatus() == 10 ) {
				$this->Validator->isTRUE( 'status',
										  false,
										  TTi18n::gettext( 'Unable to delete employees who are active' ) );
			}

			$end_date = time();

			if ( $this->Validator->isError() == false ) { //This can be pretty resource intensive, so if there are any other errors don't bother checking it.
				//Check to make sure there aren't any punches/timesheet data in the last 2 years.
				$start_date = TTDate::incrementDate( time(), -2, 'year' );

				$udtlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udtlf */
				$udtlf->getByCompanyIDAndUserIdAndObjectTypeAndStartDateAndEndDate( $this->getCompany(), $this->getId(), 10, $start_date, $end_date, 1 ); //10=Worked, Limit 1
				if ( $udtlf->getRecordCount() > 0 ) {
					$this->Validator->isTRUE( 'in_use',
											  false,
											  TTi18n::gettext( 'Unable to delete employees who have recorded worked time in the last 2 years' ) );
				}
			}

			if ( $this->Validator->isError() == false ) { //This can be pretty resource intensive, so if there are any other errors don't bother checking it.
				//Check to make sure there aren't any PAID pay stubs in the last 7 years.
				$start_date = TTDate::incrementDate( time(), -7, 'year' );

				$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
				$pslf->getByUserId( $this->getId(), 1 );                                               //limit 1
				$pslf->getByUserIdAndStartDateAndEndDate( $this->getId(), $start_date, $end_date, 1 ); //Limit 1
				if ( $pslf->getRecordCount() > 0 ) {
					$this->Validator->isTRUE( 'in_use',
											  false,
											  TTi18n::gettext( 'Unable to delete employees with pay stubs in the last 7 years' ) );
				}
			}

			unset( $start_date, $end_date, $pslf, $udtlf );
		}


		if ( $ignore_warning == false ) {
			if ( $this->is_new == false && $this->getLegalEntity() != $this->getGenericOldDataValue( 'legal_entity_id' ) ) {
				$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
				$pslf->getByUserId( $this->getId(), 1 ); //limit 1
				if ( $pslf->getRecordCount() > 0 ) {
					$this->Validator->Warning( 'legal_entity_id', TTi18n::gettext( 'Changing the legal entity after an employee has been paid will cause historical tax information to be lost. Please create a new employee record instead' ) );
				} else {
					$this->Validator->Warning( 'legal_entity_id', TTi18n::gettext( 'Changing the legal entity will unassign this employee from all Tax/Deductions' ) );
				}
				unset( $pslf );
			}

			//Check if birth date is not specified and payroll is being processed (some pay stubs do exist for this legal entity) to remind the user to specify a birth date.
			//  This is critical especially in Canada for CPP eligibility.
			if ( $this->getBirthDate() == '' && $this->getStatus() == 10 ) { //10=Active
				$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
				$pslf->getByCompanyIdAndLegalEntityId( $this->getCompany(), $this->getLegalEntity(), 1 ); //limit 1
				if ( $pslf->getRecordCount() > 0 ) {
					$this->Validator->Warning( 'birth_date', TTi18n::gettext( 'Birth Date is not specified, this may prevent some Tax/Deduction calculations from being performed accurately' ) );
				}
			}

			if ( $this->getBirthDate() != '' ) { //Only check age if birth date is specified.
				if ( $this->getAge() < 12 ) { //In Canada, anyone under 12 needs direct permission from the director of employment standards.
					$this->Validator->Warning( 'birth_date', TTi18n::gettext( 'Employee is less than 12 years old, please confirm that the birth date is correct' ) );
				}
				if ( $this->getAge() > 90 ) { //Anyone over 90 is reaching an age where they are unlikely to be employed.
					$this->Validator->Warning( 'birth_date', TTi18n::gettext( 'Employee is more than 90 years old, please confirm that the birth date is correct' ) );
				}
			}

			if ( $this->getStatus() == 10 && $this->getTerminationDate() != '' && TTDate::getMiddleDayEpoch( $this->getTerminationDate() ) < TTDate::getMiddleDayEpoch( time() ) ) {
				$this->Validator->Warning( 'termination_date', TTi18n::gettext( 'Employee is active but has a termination date in the past, perhaps their status should be Terminated?' ) );
			}

			//Check for Terminated AND On Leave employees, because as soon as they are marked On Leave if there is no termination date then the final pay stubs won't be generated.
			if ( $this->getStatus() >= 12 && $this->getTerminationDate() == '' ) { //Terminated/On Leave
				$this->Validator->Warning( 'termination_date', TTi18n::gettext( 'Employee is Terminated/On Leave, but no termination date is specified' ) );
			}

			if ( $this->getStatus() >= 12 && $this->getTerminationDate() != '' ) { //Terminated/On Leave
				if ( is_array( $data_diff ) && $this->isDataDifferent( 'termination_date', $data_diff ) && TTDate::getMiddleDayEpoch( $this->getTerminationDate() ) < TTDate::getMiddleDayEpoch( time() ) ) {
					$this->Validator->Warning( 'termination_date', TTi18n::gettext( 'When setting a termination date retroactively, you may need to recalculate this employees timesheet' ) );
				}

				if ( $this->is_new == false ) {
					//Check to see if worked/absence time exist after termination
					//  Case to handled here are where the user may have vacation scheduled way off in the future. These would need to be manually deleted.
					//      Or they may have holiday absence time a few days in the future. TimeSheet would need to be recalculated in this case.
					$udtlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udtlf */
					$udtlf->getByCompanyIDAndUserIdAndObjectTypeAndStartDateAndEndDate( $this->getCompany(), $this->getID(), [ 10, 50 ], ( $this->getTerminationDate() + 86400 ), ( time() + ( 86400 * 365 ) ) );
					if ( $udtlf->getRecordCount() > 0 ) {
						$this->Validator->Warning( 'termination_date', TTi18n::gettext( 'Employee has time on their timesheet after their termination date that may be ignored or may no longer be correct (%1)', [ TTDate::getDate( 'DATE', $udtlf->getCurrent()->getDateStamp() ) ] ) );
					}
					unset( $udtlf );

					//Check to see if Pay Stub Amendments exists after termination date
					$psalf = TTnew( 'PayStubAmendmentListFactory' ); /** @var PayStubAmendmentListFactory $psalf */
					$psalf->getByUserIdAndAuthorizedAndStatusIDAndStartDateAndEndDate( $this->getID(), true, [ 50 ], ( $this->getTerminationDate() + 86400 ), ( time() + ( 86400 * 365 ) ) );
					if ( $psalf->getRecordCount() > 0 ) {
						$this->Validator->Warning( 'termination_date', TTi18n::gettext( 'Employee has pay stub amendments effective after their termination date that may be ignored (%1)', [ TTDate::getDate( 'DATE', $psalf->getCurrent()->getEffectiveDate() ) ] ) );
					}
					unset( $psalf );
				}
			}

			if ( $this->getDeleted() == false ) {
				//Check for duplicate email addresses and warn about possible account lock-out due to password reset functionality being disabled.
				if ( $this->isUniqueWorkEmail( $this->getWorkEmail() ) == false ) {
					$this->Validator->Warning( 'work_email', TTi18n::gettext( 'Work email address is assigned to another employee, continuing will disable password reset functionality and may result in account lock-out' ) );
				}
				if ( $this->isUniqueHomeEmail( $this->getHomeEmail() ) == false ) {
					$this->Validator->Warning( 'home_email', TTi18n::gettext( 'Home email address is assigned to another employee, continuing will disable password reset functionality and may result in account lock-out' ) );
				}
			}

			//Only when adding a new employee, check if SIN is used by another employee and warn the user in case they are not aware of that and may want to re-activate the existing employee, or not add duplicate employees.
			if ( $this->is_new == true && $this->isUniqueSIN( $this->getSIN() ) == false ) {
				$this->Validator->Warning( 'sin', TTi18n::gettext( 'SIN/SSN is assigned to another employee. Consider reactivating the existing employee instead of creating a new one' ) );
			}
		}
																																																																						/* @formatter:off */ if ( ( $this->isNew( true ) == true || $this->Validator->isValid() == true ) && $this->getStatus() == 10 ) { $obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65"; $obj_function = "\166\x61\154\x69\144\x61\164\145\114\x69\x63\145\x6e\x73\x65"; $obj_error_msg_function = "\x67\x65\x74\x46\x75\154\154\105\162\x72\x6f\x72\115\x65\x73\163\141\x67\x65"; @$obj = new $obj_class; $retval = $obj->{$obj_function}(null, array("\x65\x6d\x70\x6c\x6f\x79\x65\x65" => $this)); if ( $retval !== true ) { $this->Validator->isTrue( 'status_id', false, $obj->{$obj_error_msg_function}($retval) ); } } /** @formatter:on */
		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preValidate() {
		$this->is_new = $this->isNew( true ); //Remember if this is a new user for postSave(), as well as optimize for Validate()

		$data_diff = $this->getDataDifferences();

		if ( $this->getDefaultBranch() == false ) {
			$this->setDefaultBranch( TTUUID::getZeroID() );
		}
		if ( $this->getDefaultDepartment() == false ) {
			$this->setDefaultDepartment( TTUUID::getZeroID() );
		}

		if ( $this->getStatus() == false ) {
			$this->setStatus( 10 ); //Active
		}

		if ( $this->getSex() == false ) {
			$this->setSex( 5 ); //UnSpecified
		}

		if ( $this->getEthnicGroup() == false ) {
			$this->setEthnicGroup( TTUUID::getZeroID() );
		}

		if ( $this->getEnableClearPasswordResetData() == true ) {
			Debug::text( 'Clearing password reset data...', __FILE__, __LINE__, __METHOD__, 10 );
			$this->setPasswordResetKey( '' );
			//$this->setPasswordResetDate(''); //Don't reset password reset date, as it can be used in isFirstLogin() to determine if they just recently reset their password.
		}

		if ( $this->getTerminatedPermissionControl() == false ) {
			$this->setTerminatedPermissionControl( TTUUID::getZeroID() );
		}

		//Check if we need to set the Login Expire Date.
		if ( is_array( $data_diff ) && $this->isDataDifferent( 'status_id', $data_diff ) ) {
			if ( is_object( $this->getCompanyObject() ) && $this->getStatus() >= 11 ) { // 11=In-Active
				if ( $this->getTerminationDate() != '' ) {
					$terminated_date = $this->getTerminationDate();
				} else {
					$terminated_date = time();
				}

				if ( $this->getLoginExpireDate() == '' || $this->getLoginExpireDate() <= $terminated_date ) {
					$this->setLoginExpireDate( TTDate::incrementDate( ( ( $this->getCompanyObject()->getTerminatedUserDisableLoginType() == 10 ) ? TTDate::getEndYearEpoch( $terminated_date ) : $terminated_date ), $this->getCompanyObject()->getTerminatedUserDisableLoginAfterDays(), 'day' ) );
					Debug::text( 'User is no longer active, setting login expire date to: ' . TTDate::getDate( 'DATE+TIME', $this->getLoginExpireDate() ), __FILE__, __LINE__, __METHOD__, 10 );
				}
				unset( $terminated_date );
			} else if ( $this->getStatus() == 10 ) { //10=Active
				if ( $this->getLoginExpireDate() != '' ) {
					$this->setEnableLogin( true ); //Re-enable login
					$this->setLoginExpireDate( null ); //Clear login expire date.
					Debug::text( 'User is active again, clearing Login Expire Date...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$data_diff = $this->getDataDifferences();
		$this->removeCache( $this->getId() );
		$this->removeCache( $this->getId(), 'user_preference' ); //Clear user preference cache as user status/enable_login values can be cached there.

		//If Status changes, clear permission cache so terminated permissions can be used instead. This is also in Permission->getPermissions()
		if ( is_array( $data_diff ) && $this->isDataDifferent( 'status_id', $data_diff ) ) {
			$pf = TTnew( 'PermissionFactory' ); /** @var PermissionFactory $pf */
			$pf->clearCache( $this->getID(), $this->getCompany() );
		}

		if ( $this->getDeleted() == false && $this->getPermissionControl() !== false ) {
			Debug::text( 'Permission Group is set...', __FILE__, __LINE__, __METHOD__, 10 );

			$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
			$pclf->getByCompanyIdAndUserID( $this->getCompany(), $this->getId() );
			if ( $pclf->getRecordCount() > 0 ) {
				Debug::text( 'Already assigned to a Permission Group...', __FILE__, __LINE__, __METHOD__, 10 );

				$pc_obj = $pclf->getCurrent();

				if ( $pc_obj->getId() == $this->getPermissionControl() ) {
					$add_permission_control = false;
				} else {
					Debug::text( 'Permission Group has changed...', __FILE__, __LINE__, __METHOD__, 10 );

					$pulf = TTnew( 'PermissionUserListFactory' ); /** @var PermissionUserListFactory $pulf */
					$pulf->getByPermissionControlIdAndUserID( $pc_obj->getId(), $this->getId() );
					Debug::text( 'Record Count: ' . $pulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
					if ( $pulf->getRecordCount() > 0 ) {
						foreach ( $pulf as $pu_obj ) {
							Debug::text( 'Deleting from Permission Group: ' . $pu_obj->getPermissionControl(), __FILE__, __LINE__, __METHOD__, 10 );
							$pu_obj->Delete();
						}

						$pc_obj->touchUpdatedByAndDate();
					}

					$add_permission_control = true;
				}
			} else {
				Debug::text( 'NOT Already assigned to a Permission Group...', __FILE__, __LINE__, __METHOD__, 10 );
				$add_permission_control = true;
			}

			if ( $this->getPermissionControl() !== false && $add_permission_control == true ) {
				Debug::text( 'Adding user to Permission Group...', __FILE__, __LINE__, __METHOD__, 10 );

				//Add to new permission group
				$puf = TTnew( 'PermissionUserFactory' ); /** @var PermissionUserFactory $puf */
				$puf->setPermissionControl( $this->getPermissionControl() );
				$puf->setUser( $this->getID() );
				if ( $puf->isValid() ) {
					if ( is_object( $puf->getPermissionControlObject() ) ) {
						$puf->getPermissionControlObject()->touchUpdatedByAndDate();
					}
					$puf->Save();

					//Clear permission cache for this employee.
					$pf = TTnew( 'PermissionFactory' ); /** @var PermissionFactory $pf */
					$pf->clearCache( $this->getID(), $this->getCompany() );
				}
			}
			unset( $add_permission_control );
		}

		if ( $this->getDeleted() == false && $this->getPayPeriodSchedule() !== false ) {
			Debug::text( 'Pay Period Schedule is set: ' . $this->getPayPeriodSchedule(), __FILE__, __LINE__, __METHOD__, 10 );

			$add_pay_period_schedule = false;

			$ppslf = TTnew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $ppslf */
			$ppslf->getByUserId( $this->getId() );
			if ( $ppslf->getRecordCount() > 0 ) {
				$pps_obj = $ppslf->getCurrent();

				if ( $this->getPayPeriodSchedule() == $pps_obj->getId() ) {
					Debug::text( 'Already assigned to this Pay Period Schedule...', __FILE__, __LINE__, __METHOD__, 10 );
					$add_pay_period_schedule = false;
				} else {
					Debug::text( 'Changing Pay Period Schedule...', __FILE__, __LINE__, __METHOD__, 10 );

					//Remove user from current schedule.
					$ppsulf = TTnew( 'PayPeriodScheduleUserListFactory' ); /** @var PayPeriodScheduleUserListFactory $ppsulf */
					$ppsulf->getByPayPeriodScheduleIdAndUserID( $pps_obj->getId(), $this->getId() );
					Debug::text( 'Record Count: ' . $ppsulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
					if ( $ppsulf->getRecordCount() > 0 ) {
						foreach ( $ppsulf as $ppsu_obj ) {
							Debug::text( 'Deleting from Pay Period Schedule: ' . $ppsu_obj->getPayPeriodSchedule(), __FILE__, __LINE__, __METHOD__, 10 );
							$ppsu_obj->Delete();
						}
					}
					$add_pay_period_schedule = true;
				}
			} else if ( TTUUID::isUUID( $this->getPayPeriodSchedule() ) && $this->getPayPeriodSchedule() != TTUUID::getZeroID() && $this->getPayPeriodSchedule() != TTUUID::getNotExistID() ) {
				Debug::text( 'Not assigned to ANY Pay Period Schedule...', __FILE__, __LINE__, __METHOD__, 10 );
				$add_pay_period_schedule = true;
			}

			if ( $this->getPayPeriodSchedule() !== false && $add_pay_period_schedule == true ) {
				//Add to new pay period schedule
				$ppsuf = TTnew( 'PayPeriodScheduleUserFactory' ); /** @var PayPeriodScheduleUserFactory $ppsuf */
				$ppsuf->setPayPeriodSchedule( $this->getPayPeriodSchedule() );
				$ppsuf->setUser( $this->getID() );
				if ( $ppsuf->isValid() ) {
					$ppsuf->Save( false );

					//Attempt to import data into currently open pay periods if its not a new user.
					if ( !isset( $this->is_new ) || ( isset( $this->is_new ) && $this->is_new == false ) && is_object( $ppsuf->getPayPeriodScheduleObject() ) ) {
						$ppsuf->getPayPeriodScheduleObject()->importData( $this->getID() );
					}
				}
				unset( $ppsuf );
			}
			unset( $add_pay_period_schedule );
		}

		if ( $this->getDeleted() == false && $this->getPolicyGroup() !== false ) {
			Debug::text( 'Policy Group is set...', __FILE__, __LINE__, __METHOD__, 10 );

			$pglf = TTnew( 'PolicyGroupListFactory' ); /** @var PolicyGroupListFactory $pglf */
			$pglf->getByUserIds( $this->getId() );
			if ( $pglf->getRecordCount() > 0 ) {
				$pg_obj = $pglf->getCurrent();

				if ( $this->getPolicyGroup() == $pg_obj->getId() ) {
					Debug::text( 'Already assigned to this Policy Group...', __FILE__, __LINE__, __METHOD__, 10 );
					$add_policy_group = false;
				} else {
					Debug::text( 'Changing Policy Group...', __FILE__, __LINE__, __METHOD__, 10 );

					//Remove user from current schedule.
					$pgulf = TTnew( 'PolicyGroupUserListFactory' ); /** @var PolicyGroupUserListFactory $pgulf */
					$pgulf->getByPolicyGroupIdAndUserId( $pg_obj->getId(), $this->getId() );
					Debug::text( 'Record Count: ' . $pgulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
					if ( $pgulf->getRecordCount() > 0 ) {
						foreach ( $pgulf as $pgu_obj ) {
							Debug::text( 'Deleting from Policy Group: ' . $pgu_obj->getPolicyGroup(), __FILE__, __LINE__, __METHOD__, 10 );
							$pgu_obj->Delete();
						}
					}
					$add_policy_group = true;
				}
			} else {
				Debug::text( 'Not assigned to ANY Policy Group...', __FILE__, __LINE__, __METHOD__, 10 );
				$add_policy_group = true;
			}

			if ( $this->getPolicyGroup() !== false && $add_policy_group == true ) {
				//Add to new policy group
				$pguf = TTnew( 'PolicyGroupUserFactory' ); /** @var PolicyGroupUserFactory $pguf */
				$pguf->setPolicyGroup( $this->getPolicyGroup() );
				$pguf->setUser( $this->getID() );

				if ( $pguf->isValid() ) {
					$pguf->Save();
				}
			}
			unset( $add_policy_group );
		}

		if ( $this->getDeleted() == false && $this->getHierarchyControl() !== false ) {
			Debug::text( 'Hierarchies are set...', __FILE__, __LINE__, __METHOD__, 10 );

			$hierarchy_control_data = array_unique( array_values( (array)$this->getHierarchyControl() ) );
			//Debug::Arr($hierarchy_control_data, 'Setting hierarchy control data...', __FILE__, __LINE__, __METHOD__, 10);

			if ( is_array( $hierarchy_control_data ) ) {
				$hclf = TTnew( 'HierarchyControlListFactory' ); /** @var HierarchyControlListFactory $hclf */
				$hclf->getObjectTypeAppendedListByCompanyIDAndUserID( $this->getCompany(), $this->getID() );
				$existing_hierarchy_control_data = array_unique( array_values( (array)$hclf->getArrayByListFactory( $hclf, false, true, false ) ) );
				//Debug::Arr($existing_hierarchy_control_data, 'Existing hierarchy control data...', __FILE__, __LINE__, __METHOD__, 10);

				$hierarchy_control_delete_diff = array_diff( $existing_hierarchy_control_data, $hierarchy_control_data );
				//Debug::Arr($hierarchy_control_delete_diff, 'Hierarchy control delete diff: ', __FILE__, __LINE__, __METHOD__, 10);

				//Remove user from existing hierarchy control
				if ( is_array( $hierarchy_control_delete_diff ) ) {
					foreach ( $hierarchy_control_delete_diff as $hierarchy_control_id ) {
						if ( $hierarchy_control_id != TTUUID::getZeroID() ) {
							$hulf = TTnew( 'HierarchyUserListFactory' ); /** @var HierarchyUserListFactory $hulf */
							$hulf->getByHierarchyControlAndUserID( $hierarchy_control_id, $this->getID() );
							if ( $hulf->getRecordCount() > 0 ) {
								Debug::text( 'Deleting user from hierarchy control ID: ' . $hierarchy_control_id, __FILE__, __LINE__, __METHOD__, 10 );
								$hulf->getCurrent()->Delete();
							}
						}
					}
				}
				unset( $hierarchy_control_delete_diff, $hulf, $hclf, $hierarchy_control_id );

				$hierarchy_control_add_diff = array_diff( $hierarchy_control_data, $existing_hierarchy_control_data );
				//Debug::Arr($hierarchy_control_add_diff, 'Hierarchy control add diff: ', __FILE__, __LINE__, __METHOD__, 10);

				if ( is_array( $hierarchy_control_add_diff ) ) {
					foreach ( $hierarchy_control_add_diff as $hierarchy_control_id ) {
						Debug::text( 'Hierarchy data changed...', __FILE__, __LINE__, __METHOD__, 10 );
						if ( $hierarchy_control_id != TTUUID::getZeroID() ) {
							$huf = TTnew( 'HierarchyUserFactory' ); /** @var HierarchyUserFactory $huf */
							$huf->setHierarchyControl( $hierarchy_control_id );
							$huf->setUser( $this->getId() );
							if ( $huf->isValid() ) {
								Debug::text( 'Adding user to hierarchy control ID: ' . $hierarchy_control_id, __FILE__, __LINE__, __METHOD__, 10 );
								$huf->Save();
							}
						}
					}
				}
				unset( $huf, $hierarchy_control_id );
			}
		}

		if ( DEMO_MODE != true && $this->getDeleted() == false && $this->getPasswordUpdatedDate() >= ( time() - 10 ) ) { //If the password was updated in the last 10 seconds.
			Debug::text( 'Password changed, saving it for historical purposes... Password: ' . $this->getPassword(), __FILE__, __LINE__, __METHOD__, 10 );

			$uif = TTnew( 'UserIdentificationFactory' ); /** @var UserIdentificationFactory $uif */
			$uif->setUser( $this->getID() );
			$uif->setType( 5 ); //Password History
			$uif->setNumber( 0 );
			$uif->setValue( $this->getPassword() );
			if ( $uif->isValid() ) {
				$uif->Save();
			}
			unset( $uif );
		}

		if ( $this->getDeleted() == false ) {
			Debug::text( 'Setting Tags...', __FILE__, __LINE__, __METHOD__, 10 );
			CompanyGenericTagMapFactory::setTags( $this->getCompany(), 200, $this->getID(), $this->getTag() );

			$this->clearGeoCode( $data_diff ); //Clear Lon/Lat coordinates when address has changed.

			//Because old_data hire_date is a date string from the DB and not actually parsed to a epoch yet, we need to parse it here to ensure it has actually changed.
			if ( is_array( $data_diff )
					&& ( $this->isDataDifferent( 'hire_date', $data_diff, 'date' ) || $this->isDataDifferent( 'termination_date', $data_diff, 'date' ) ) ) {
				Debug::text( 'Hire Date or Termination date have changed!', __FILE__, __LINE__, __METHOD__, 10 );
				$rsf = TTnew( 'RecurringScheduleFactory' ); /** @var RecurringScheduleFactory $rsf */
				$rsf->recalculateRecurringSchedules( $this->getID(), ( time() - ( 86400 * 28 ) ), ( time() + ( 86400 * 28 ) ) );
			}
		}

		if ( isset( $this->is_new ) && $this->is_new == true ) {
			$udlf = TTnew( 'UserDefaultListFactory' ); /** @var UserDefaultListFactory $udlf */
			$udlf->getByCompanyId( $this->getCompany() );
			if ( $udlf->getRecordCount() > 0 ) {
				Debug::Text( 'Using User Defaults', __FILE__, __LINE__, __METHOD__, 10 );
				$udf_obj = $udlf->getCurrent();

				Debug::text( 'Inserting Default Deductions...', __FILE__, __LINE__, __METHOD__, 10 );
				$company_deduction_ids = $udf_obj->getCompanyDeduction();
				if ( is_array( $company_deduction_ids ) && count( $company_deduction_ids ) > 0 ) {
					//UserDefaults should be able to select Tax/Deduction records from *any* legal entity, and we will just filter them out to the proper legal entity here.
					$cdlf = TTNew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
					$cdlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), array('id' => $company_deduction_ids) );
					if ( $cdlf->getRecordCount() > 0 ) {
						foreach ( $cdlf as $cd_obj ) {
							if ( ( $cd_obj->getLegalEntity() == $this->getLegalEntity() || $cd_obj->getLegalEntity() == TTUUID::getZeroID() ) ) {
								$udf = TTnew( 'UserDeductionFactory' ); /** @var UserDeductionFactory $udf */
								$udf->setUser( $this->getId() );
								$udf->setCompanyDeduction( $cd_obj->getId() );
								if ( $udf->isValid() ) {
									$udf->Save();
								}
							} else {
								Debug::text( '  Skipping UserDefault Company Deduction due to mismatched Legal Entity: ' . $cd_obj->getName() . ' Legal Entity: ' . $cd_obj->getLegalEntity(), __FILE__, __LINE__, __METHOD__, 10 );
							}
						}
					}
				}
				unset( $company_deduction_ids, $udf, $cdlf, $cd_obj );

				Debug::text( 'Inserting Default Prefs (a)...', __FILE__, __LINE__, __METHOD__, 10 );
				$upf = TTnew( 'UserPreferenceFactory' ); /** @var UserPreferenceFactory $upf */
				$upf->setUser( $this->getId() );
				$upf->setLanguage( $udf_obj->getLanguage() );
				$upf->setDateFormat( $udf_obj->getDateFormat() );
				$upf->setTimeFormat( $udf_obj->getTimeFormat() );
				$upf->setTimeUnitFormat( $udf_obj->getTimeUnitFormat() );
				$upf->setDistanceFormat( $udf_obj->getDistanceFormat() );

				$upf->setTimeZone( $upf->getLocationTimeZone( $this->getCountry(), $this->getProvince(), $this->getWorkPhone(), $this->getHomePhone(), $udf_obj->getTimeZone() ) );
				Debug::text( 'Time Zone: ' . $upf->getTimeZone(), __FILE__, __LINE__, __METHOD__, 9 );

				$upf->setItemsPerPage( $udf_obj->getItemsPerPage() );
				$upf->setStartWeekDay( $udf_obj->getStartWeekDay() );
				$upf->setEnableEmailNotificationException( $udf_obj->getEnableEmailNotificationException() );
				$upf->setEnableEmailNotificationMessage( $udf_obj->getEnableEmailNotificationMessage() );
				$upf->setEnableEmailNotificationPayStub( $udf_obj->getEnableEmailNotificationPayStub() );
				$upf->setEnableEmailNotificationHome( $udf_obj->getEnableEmailNotificationHome() );

				if ( $upf->isValid() ) {
					$upf->Save();
				}
			} else {
				//No New Hire defaults, use global defaults.
				Debug::text( 'Inserting Default Prefs (b)...', __FILE__, __LINE__, __METHOD__, 10 );
				$upf = TTnew( 'UserPreferenceFactory' ); /** @var UserPreferenceFactory $upf */
				$upf->setUser( $this->getId() );
				$upf->setLanguage( 'en' );
				$upf->setDateFormat( 'd-M-y' );
				$upf->setTimeFormat( 'g:i A' );
				$upf->setTimeUnitFormat( 10 );
				$upf->setDistanceFormat( 10 );

				$upf->setTimeZone( $upf->getLocationTimeZone( $this->getCountry(), $this->getProvince(), $this->getWorkPhone(), $this->getHomePhone() ) );
				Debug::text( 'Time Zone: ' . $upf->getTimeZone(), __FILE__, __LINE__, __METHOD__, 9 );

				$upf->setItemsPerPage( 25 );
				$upf->setStartWeekDay( 0 );
				$upf->setEnableEmailNotificationException( true );
				$upf->setEnableEmailNotificationMessage( true );
				$upf->setEnableEmailNotificationPayStub( true );
				$upf->setEnableEmailNotificationHome( true );
				if ( $upf->isValid() ) {
					$upf->Save();
				}
			}
		}

		if ( $this->getDeleted() == true ) {
			//Remove them from the authorization hierarchy, policy group, pay period schedule, stations, jobs, etc...
			//Delete any accruals for them as well.

			//Pay Period Schedule
			$ppslf = TTnew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $ppslf */
			$ppslf->getByUserId( $this->getId() );
			if ( $ppslf->getRecordCount() > 0 ) {
				$pps_obj = $ppslf->getCurrent();

				//Remove user from current schedule.
				$ppsulf = TTnew( 'PayPeriodScheduleUserListFactory' ); /** @var PayPeriodScheduleUserListFactory $ppsulf */
				$ppsulf->getByPayPeriodScheduleIdAndUserID( $pps_obj->getId(), $this->getId() );
				Debug::text( 'Record Count: ' . $ppsulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $ppsulf->getRecordCount() > 0 ) {
					foreach ( $ppsulf as $ppsu_obj ) {
						Debug::text( 'Deleting from Pay Period Schedule: ' . $ppsu_obj->getPayPeriodSchedule(), __FILE__, __LINE__, __METHOD__, 10 );
						$ppsu_obj->Delete();
					}
				}
			}

			//Policy Group
			$pglf = TTnew( 'PolicyGroupListFactory' ); /** @var PolicyGroupListFactory $pglf */
			$pglf->getByUserIds( $this->getId() );
			if ( $pglf->getRecordCount() > 0 ) {
				$pg_obj = $pglf->getCurrent();

				$pgulf = TTnew( 'PolicyGroupUserListFactory' ); /** @var PolicyGroupUserListFactory $pgulf */
				$pgulf->getByPolicyGroupIdAndUserId( $pg_obj->getId(), $this->getId() );
				Debug::text( 'Record Count: ' . $pgulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $pgulf->getRecordCount() > 0 ) {
					foreach ( $pgulf as $pgu_obj ) {
						Debug::text( 'Deleting from Policy Group: ' . $pgu_obj->getPolicyGroup(), __FILE__, __LINE__, __METHOD__, 10 );
						$pgu_obj->Delete();
					}
				}
			}

			//Hierarchy
			$hclf = TTnew( 'HierarchyControlListFactory' ); /** @var HierarchyControlListFactory $hclf */
			$hclf->getByCompanyId( $this->getCompany() );
			if ( $hclf->getRecordCount() > 0 ) {
				foreach ( $hclf as $hc_obj ) {
					$hf = TTnew( 'HierarchyListFactory' ); /** @var HierarchyListFactory $hf */
					$hf->setUser( $this->getID() );
					$hf->setHierarchyControl( $hc_obj->getId() );
					$hf->Delete();
				}
				$hf->removeCache( null, $hf->getTable( true ) ); //On delete we have to delete the entire group.
				unset( $hf );
			}

			/*
			//Accrual balances - DON'T DO THIS ANYMORE, AS IT CAUSES PROBLEMS WITH RESTORING DELETED USERS. I THINK IT WAS JUST AN OPTIMIZATION ANYWAYS.
			$alf = TTnew( 'AccrualListFactory' );
			$alf->getByUserIdAndCompanyId( $this->getId(), $this->getCompany() );
			if ( $alf->getRecordCount() > 0 ) {
				foreach( $alf as $a_obj ) {
					$a_obj->setDeleted(TRUE);
					if ( $a_obj->isValid() ) {
						$a_obj->Save();
					}
				}
			}
			*/

			//Station employee critiera
			$siuf = TTnew( 'StationIncludeUserFactory' ); /** @var StationIncludeUserFactory $siuf */
			$seuf = TTnew( 'StationExcludeUserFactory' ); /** @var StationExcludeUserFactory $seuf */

			$query = 'delete from ' . $siuf->getTable() . ' where user_id = \'' . TTUUID::castUUID( $this->getId() ) . '\'';
			$this->ExecuteSQL( $query );

			$query = 'delete from ' . $seuf->getTable() . ' where user_id = \'' . TTUUID::castUUID( $this->getId() ) . '\'';
			$this->ExecuteSQL( $query );

			//Job employee criteria
			$cgmlf = TTnew( 'CompanyGenericMapListFactory' ); /** @var CompanyGenericMapListFactory $cgmlf */
			$cgmlf->getByCompanyIDAndObjectTypeAndMapID( $this->getCompany(), array(1040, 1050), $this->getID() );
			if ( $cgmlf->getRecordCount() > 0 ) {
				foreach ( $cgmlf as $cgm_obj ) {
					Debug::text( 'Deleting from Company Generic Map: ' . $cgm_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );
					$cgm_obj->Delete();
				}
			}
		}

		if ( ( $this->getDeleted() == true || $this->getStatus() != 10 ) && is_object( $this->getCompanyObject() ) && $this->getCompanyObject()->getStatus() == 10 && $this->getCompanyObject()->getDeleted() == false ) { //Only perform these checks if the company is active. Otherwise we can't delete records for cancelled companies.
			//Employee is being deleted or inactivated, make sure they are not a company contact, and if so replace them with a new contact.
			$default_company_contact_user_id = false;
			if ( in_array( $this->getId(), array($this->getCompanyObject()->getAdminContact(), $this->getCompanyObject()->getBillingContact(), $this->getCompanyObject()->getSupportContact()) ) ) {
				$default_company_contact_user_id = $this->getCompanyObject()->getDefaultContact();
				Debug::text( 'User is primary company contact, remove and replace them with: ' . $default_company_contact_user_id, __FILE__, __LINE__, __METHOD__, 10 );

				if ( $default_company_contact_user_id != false && $this->getId() == $this->getCompanyObject()->getAdminContact() ) {
					$this->getCompanyObject()->setAdminContact( $default_company_contact_user_id );
					Debug::text( 'Replacing Admin Contact with: ' . $default_company_contact_user_id, __FILE__, __LINE__, __METHOD__, 10 );
				}
				if ( $default_company_contact_user_id != false && $this->getId() == $this->getCompanyObject()->getBillingContact() ) {
					$this->getCompanyObject()->setBillingContact( $default_company_contact_user_id );
					Debug::text( 'Replacing Billing Contact with: ' . $default_company_contact_user_id, __FILE__, __LINE__, __METHOD__, 10 );
				}
				if ( $default_company_contact_user_id != false && $this->getId() == $this->getCompanyObject()->getSupportContact() ) {
					$this->getCompanyObject()->setSupportContact( $default_company_contact_user_id );
					Debug::text( 'Replacing Support Contact with: ' . $default_company_contact_user_id, __FILE__, __LINE__, __METHOD__, 10 );
				}
				if ( $default_company_contact_user_id != false && $this->getCompanyObject()->isValid() ) {
					$this->getCompanyObject()->Save();
				}
			}
			unset( $default_company_contact_user_id );
		}

		//If status is changed TO or FROM Active, logout user. If they are changed from InActive to Terminated no need to logout user.
		// Don't check LoginEnabled() here, as the permissions need to change when the status changes, so the user should still be logged out.
		if ( is_array( $data_diff ) && ( ( $this->isDataDifferent( 'status_id', $data_diff ) && ( $this->getStatus() == 10 || $data_diff['status_id'] == 10 ) ) || ( $this->isDataDifferent( 'enable_login', $data_diff ) && $this->getEnableLogin() == false ) ) ) {
			$authentication = TTNew( 'Authentication' ); /** @var Authentication $authentication */
			$authentication->logoutUser( $this->getID() );
		}

		//Legal entity has changed. Migrate UserDeduction/RemittanceDestinationAccount's to the new legal entity whenever possible.
		if ( is_array( $data_diff ) && $this->isDataDifferent( 'legal_entity_id', $data_diff ) ) {
			Debug::Text( 'Legal entity changed from: ' . $data_diff['legal_entity_id'] . ' to: ' . $this->getLegalEntity() . '...', __FILE__, __LINE__, __METHOD__, 10 );

			UserDeductionFactory::MigrateLegalEntity( $this, $data_diff );
			RemittanceDestinationAccountFactory::MigrateLegalEntity( $this, $data_diff );
		}

		return true;
	}

	/**
	 * @return bool|string
	 */
	function getMapURL() {
		return Misc::getMapURL( $this->getAddress1(), $this->getAddress2(), $this->getCity(), $this->getProvince(), $this->getPostalCode(), $this->getCountry() );
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
			foreach ( $variable_function_map as $key => $function ) {
				if ( isset( $data[ $key ] ) ) {

					$function = 'set' . $function;
					switch ( $key ) {
						case 'hire_date':
						case 'birth_date':
						case 'termination_date':
						case 'login_expire_date':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[ $key ] ) );
							}
							break;
						case 'password':
							$password_confirm = null;
							if ( isset( $data['password_confirm'] ) ) {
								$password_confirm = $data['password_confirm'];
							}
							$this->setPassword( $data[ $key ], $password_confirm );
							break;
						case 'last_login_date': //SKip this as its set by the system.
						case 'first_name_metaphone':
						case 'last_name_metaphone':
						case 'password_reset_date': //Password columns must not be changed from the API.
						case 'password_reset_key':
						case 'password_updated_date':
						case 'work_email_is_valid': //EMail validation fields must not be changed from API.
						case 'work_email_is_valid_key':
						case 'work_email_is_valid_date':
						case 'home_email_is_valid':
						case 'home_email_is_valid_key':
						case 'home_email_is_valid_date':
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[ $key ] );
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
	 * @param bool $permission_children_ids
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null, $permission_children_ids = false ) {
		/*
		$include_columns = array(
								'id' => TRUE,
								'company_id' => TRUE,
								...
								)

		*/

		$variable_function_map = $this->getVariableToFunctionMap();
		$data = array();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[ $variable ] ) && $include_columns[ $variable ] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'full_name':
							$data[ $variable ] = $this->getFullName( true );
							break;
						case 'status':
						case 'sex':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[ $variable ] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'company':
						case 'title':
						case 'user_group':
						case 'ethnic_group':
						case 'legal_name':
						case 'currency':
						case 'currency_rate':
						case 'default_branch':
						case 'default_branch_manual_id':
						case 'default_department':
						case 'default_department_manual_id':
						case 'default_job':
						case 'default_job_manual_id':
						case 'default_job_item':
						case 'default_job_item_manual_id':
						case 'permission_control':
						case 'terminated_permission_control':
						case 'pay_period_schedule':
						case 'policy_group':
						case 'password_updated_date':
							$data[ $variable ] = $this->getColumn( $variable );
							break;
						//The below fields may be set if APISearch ListFactory is used to obtain the data originally,
						//but if it isn't, use the explicit function to get the data instead.
						case 'permission_control_id':
							//These functions are slow to obtain (especially in a large loop), so make sure the column is requested explicitly before we include it.
							//Flex currently doesn't specify these fields in the Edit view though, so this breaks Flex.
							//if ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) {
							$data[ $variable ] = $this->getColumn( $variable );
							if ( $data[ $variable ] == false ) {
								$data[ $variable ] = $this->getPermissionControl();
							}
							//}
							break;
						case 'pay_period_schedule_id':
							//These functions are slow to obtain (especially in a large loop), so make sure the column is requested explicitly before we include it.
							//Flex currently doesn't specify these fields in the Edit view though, so this breaks Flex.
							//if ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) {
							$data[ $variable ] = $this->getColumn( $variable );
							if ( $data[ $variable ] == false ) {
								$data[ $variable ] = $this->getPayPeriodSchedule();
							}
							//}
							break;
						case 'policy_group_id':
							//These functions are slow to obtain (especially in a large loop), so make sure the column is requested explicitly before we include it.
							//Flex currently doesn't specify these fields in the Edit view though, so this breaks Flex.
							//if ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) {
							$data[ $variable ] = $this->getColumn( $variable );
							if ( $data[ $variable ] == false ) {
								$data[ $variable ] = $this->getPolicyGroup();
							}
							//}
							break;
						case 'hierarchy_control':
							//These functions are slow to obtain (especially in a large loop), so make sure the column is requested explicitly before we include it.
							//Flex currently doesn't specify these fields in the Edit view though, so this breaks Flex.
							//if ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) {
							$data[ $variable ] = $this->getHierarchyControl();
							//}
							break;
						case 'hierarchy_control_display':
							//These functions are slow to obtain (especially in a large loop), so make sure the column is requested explicitly before we include it.
							//if ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) {
							$data[ $variable ] = $this->getHierarchyControlDisplay();
							//}
							break;
						case 'hierarchy_level_display':
							$data[ $variable ] = $this->getHierarchyLevelDisplay();
							break;
						case 'sin':
							$data[$variable] = $this->getSecureSIN(); //getSecureSIN() will display the full SIN if permissions allow.
							break;
						case 'last_login_date':
						case 'hire_date':
						case 'birth_date':
						case 'termination_date':
						case 'login_expire_date':
							if ( method_exists( $this, $function ) ) {
								$data[ $variable ] = TTDate::getAPIDate( 'DATE', $this->$function() );
							}
							break;
						case 'max_punch_time_stamp':
							$data[ $variable ] = TTDate::getAPIDate( 'DATE+TIME', TTDate::strtotime( $this->getColumn( $variable ) ) );
							break;
						case 'birth_date_age':
							if ( $this->getBirthDate() != '' && $this->getBirthDate() != 0 ) {
								$data[ $variable ] = (int)floor( TTDate::getYearDifference( TTDate::getBeginDayEpoch( $this->getBirthDate() ), TTDate::getEndDayEpoch( time() ) ) );
							} else {
								$data[ $variable ] = null;
							}
							break;
						case 'hire_date_age':
							if ( $this->getTerminationDate() != '' ) {
								$end_epoch = $this->getTerminationDate();
							} else {
								$end_epoch = time();
							}
							//Staffing agencies may have employees for only a few days, so need to show partial years.
							$data[ $variable ] = number_format( TTDate::getYearDifference( TTDate::getBeginDayEpoch( $this->getHireDate() ), TTDate::getEndDayEpoch( $end_epoch ) ), 2 ); //Years (two decimals)
							unset( $end_epoch );
							break;
						case 'password_reset_key': //Must not be returned to the API ever due to security risks.
						case 'current_password':
						case 'password':
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[ $variable ] = $this->$function();
							}
							break;
					}
				}
				unset( $function );
			}
			$this->getPermissionColumns( $data, $this->getID(), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Employee' ) . ': ' . $this->getFullName( false, true ), null, $this->getTable(), $this );
	}
}

?>
