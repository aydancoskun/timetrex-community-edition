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
 * @package Modules\Import
 */
class ImportUser extends Import {

	public $class_name = 'APIUser';

	public $user_names = []; //Stored used usernames so we can find duplicates.

	public $title_options = false;
	public $user_group_options = false;
	public $ethnic_group_options = false;

	public $permission_control_options = false;
	public $policy_group_options = false;
	public $pay_period_schedule_options = false;
	public $hierarchy_control_options = false;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {

		$retval = null;
		switch ( $name ) {
			case 'columns':
				global $current_company;

				$uf = TTNew( 'UserFactory' ); /** @var UserFactory $uf */
				$retval = $uf->getOptions( 'columns' );

				$retval['-1025-password'] = TTi18n::getText( 'Password' );
				$retval['-1026-phone_password'] = TTi18n::getText( 'Quick Punch Password' );

				$retval['-1099-group'] = ( isset( $retval['-1099-user_group'] ) ) ? $retval['-1099-user_group'] : null;
				unset( $retval['-1099-user_group'], $retval['-1082-full_name'], $retval['-1401-hierarchy_level_display'], $retval['-1500-last_login_date'], $retval['-1510-max_punch_time_stamp'] );
				ksort( $retval );

				//Since getOptions() can be called without first setting a company, we don't always know the product edition for the currently
				//logged in employee.
				if ( ( is_object( $this->getCompanyObject() ) && $this->getCompanyObject()->getProductEdition() < TT_PRODUCT_CORPORATE )
						|| ( !is_object( $this->getCompanyObject() ) && getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) ) {
					unset( $retval['-1104-default_job'], $retval['-1105-default_job_item'] );
				}

				if ( is_object( $current_company ) ) {
					//Get custom fields for import data.
					$oflf = TTnew( 'OtherFieldListFactory' ); /** @var OtherFieldListFactory $oflf */
					$other_field_names = $oflf->getByCompanyIdAndTypeIdArray( $current_company->getID(), [ 10 ], [ 10 => '' ] );
					if ( is_array( $other_field_names ) ) {
						$retval = array_merge( (array)$retval, (array)$other_field_names );
					}
				}

				$retval = Misc::trimSortPrefix( $retval );

				Debug::Arr( $retval, 'ImportUserColumns: ', __FILE__, __LINE__, __METHOD__, 10 );

				break;
			case 'column_aliases':
				//Used for converting column names after they have been parsed.
				$retval = [
						'status'                    => 'status_id',
						'default_branch'            => 'default_branch_id',
						'default_department'        => 'default_department_id',
						'default_job'               => 'default_job_id',
						'default_job_item'          => 'default_job_item_id',
						'title'                     => 'title_id',
						'user_group'                => 'group_id',
						'group'                     => 'group_id',
						'ethnic_group'              => 'ethnic_group_id',
						'sex'                       => 'sex_id',
						'permission_control'        => 'permission_control_id',
						'pay_period_schedule'       => 'pay_period_schedule_id',
						'policy_group'              => 'policy_group_id',
						'hierarchy_control_display' => 'hierarchy_control',
				];
				break;
			case 'import_options':
				$retval = [
						'-1010-fuzzy_match'         => TTi18n::getText( 'Enable smart matching.' ),
						'-1015-update'              => TTi18n::getText( 'Update existing records based on UserName, Employee Number, or SIN/SSN.' ), //Need an array to pick the unique column to use as the identifier, or we can just detect this on our own?
						//Allow these to be imported separately instead.
						//'-1020-create_branch' => TTi18n::getText('Create branches that don\'t exist.'),
						//'-1030-create_department' => TTi18n::getText('Create departments that don\'t exist.'),
						'-1040-create_group'        => TTi18n::getText( 'Create groups that don\'t already exist.' ),
						'-1045-create_ethnic_group' => TTi18n::getText( 'Create ethnic groups that don\'t already exist.' ),
						'-1050-create_title'        => TTi18n::getText( 'Create titles that don\'t already exist.' ),
				];
				break;
			case 'parse_hint':
				$upf = TTnew( 'UserPreferenceFactory' ); /** @var UserPreferenceFactory $upf */

				$retval = [
						'default_branch'     => [
								'-1010-name'      => TTi18n::gettext( 'Name' ),
								'-1020-manual_id' => TTi18n::gettext( 'Code' ),
						],
						'default_department' => [
								'-1010-name'      => TTi18n::gettext( 'Name' ),
								'-1020-manual_id' => TTi18n::gettext( 'Code' ),
						],
						'default_job'        => [
								'-1010-name'      => TTi18n::gettext( 'Name' ),
								'-1020-manual_id' => TTi18n::gettext( 'Code' ),
						],
						'default_job_item'   => [
								'-1010-name'      => TTi18n::gettext( 'Name' ),
								'-1020-manual_id' => TTi18n::gettext( 'Code' ),
						],
						'first_name'         => [
								'-1010-first_name'             => TTi18n::gettext( 'First Name' ),
								'-1020-first_last_name'        => TTi18n::gettext( 'FirstName LastName' ),
								'-1030-last_first_name'        => TTi18n::gettext( 'LastName, FirstName' ),
								'-1040-last_first_middle_name' => TTi18n::gettext( 'LastName, FirstName MiddleInitial' ),
						],
						'last_name'          => [
								'-1010-last_name'              => TTi18n::gettext( 'Last Name' ),
								'-1020-first_last_name'        => TTi18n::gettext( 'FirstName LastName' ),
								'-1030-last_first_name'        => TTi18n::gettext( 'LastName, FirstName' ),
								'-1040-last_first_middle_name' => TTi18n::gettext( 'LastName, FirstName MiddleInitial' ),
						],
						'middle_name'        => [
								'-1010-middle_name'            => TTi18n::gettext( 'Middle Name' ),
								'-1040-last_first_middle_name' => TTi18n::gettext( 'LastName, FirstName MiddleInitial' ),
						],
						'hire_date'          => $upf->getOptions( 'date_format' ),
						'termination_date'   => $upf->getOptions( 'date_format' ),
						'birth_date'         => $upf->getOptions( 'date_format' ),
				];
				break;
		}

		return $retval;
	}


	/**
	 * @param $row_number
	 * @param $raw_row
	 * @return array
	 */
	function _preParseRow( $row_number, $raw_row ) {
		//Only set defaults for columns already specified, or absolutely necessary ones.
		//That way if the user wants to just update one or two columns for existing employees, the default values aren't all used too.
		$column_map = $this->getColumnMap(); //Include columns that should always be there.
		$default_data = $this->getObject()->getUserDefaultData();

		$retval = [];
		foreach ( $column_map as $key => $map_data ) {
			if ( isset( $default_data[$key] ) ) {
				$retval[$key] = $default_data[$key];
			}
		}
		unset( $map_data ); //code standards

		//Debug::Arr($retval, 'preParse Row: ', __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	/**
	 * @param $row_number
	 * @param $raw_row
	 * @return mixed
	 */
	function _postParseRow( $row_number, $raw_row ) {
		if ( $this->getImportOptions( 'update' ) == true ) {
			Debug::Text( 'Updating existing records, try to find record... ', __FILE__, __LINE__, __METHOD__, 10 );
			$raw_row['id'] = $this->getUserIdByRowData( $raw_row );
			if ( $raw_row['id'] == false ) {
				unset( $raw_row['id'] );
			}
		} else {
			Debug::Text( 'NOT updating existing records... ', __FILE__, __LINE__, __METHOD__, 10 );
		}

		//Check to see if this particular record is new or modifying an existing one.
		if ( !isset( $raw_row['id'] ) || ( isset( $raw_row['id'] ) && $raw_row['id'] == false ) ) {
			Debug::Text( 'Unable to find existing employee... Creating a new one...', __FILE__, __LINE__, __METHOD__, 10 );

			$default_data = $this->getObject()->stripReturnHandler( $this->getObject()->getUserDefaultData() );
			//Debug::Arr($default_data, 'Default Data: ', __FILE__, __LINE__, __METHOD__, 10);

			$uf = TTnew( 'UserFactory' ); /** @var UserFactory $uf */

			if ( !is_array( $default_data ) ) {
				$default_data['status_id'] = 10; //Active
				$default_data['employee_number'] = 1;
				$default_data['currency_id'] = 1;
			}

			if ( !isset( $raw_row['status'] ) || ( isset( $raw_row['status'] ) && $raw_row['status'] == 0 ) ) {
				$raw_row['status'] = $default_data['status_id'];
			}

			if ( !isset( $raw_row['employee_number'] ) ) {
				$raw_row['employee_number'] = ( $default_data['employee_number'] + $row_number ); //Auto increment manual_id automatically.
			}
			if ( !isset( $raw_row['password'] ) ) {
				$raw_row['password'] = TTPassword::generateRandomPassword( 50 ); //Default to a unique password, make it really long so it always passes the password strength checker.
			}

			if ( !isset( $raw_row['user_name'] ) || ( isset( $raw_row['user_name'] ) && $raw_row['user_name'] == '' ) ) {
				if ( isset( $raw_row['first_name'] ) && isset( $raw_row['last_name'] ) ) {
					$tmp_first_name = $uf->Validator->stripNonAlphaNumeric( $raw_row['first_name'] );
					$tmp_last_name = $uf->Validator->stripNonAlphaNumeric( $raw_row['last_name'] );

					$tmp_user_name = strtolower( $tmp_first_name . '.' . $tmp_last_name );
					if ( $uf->isUniqueUserName( $tmp_user_name ) == false || in_array( $tmp_user_name, $this->user_names ) ) { //Check against existing users and those in the current import batch.
						Debug::Text( 'Autogenerated user name already exists, trying random one: ' . $tmp_user_name, __FILE__, __LINE__, __METHOD__, 10 );
						$tmp_user_name = strtolower( $tmp_first_name . '.' . $tmp_last_name . rand( 10, 9999 ) );
					}

					Debug::Text( 'Autogenerating user name: ' . $tmp_user_name, __FILE__, __LINE__, __METHOD__, 10 );

					$raw_row['user_name'] = $tmp_user_name;
				} else {
					Debug::Text( 'Not autogenerating user name...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			}

			if ( isset( $raw_row['user_name'] ) && $raw_row['user_name'] != '' ) {
				$this->user_names[] = $raw_row['user_name']; //Need to store usernames from import batch so we can detect duplicates within it.
			}

			if ( !isset( $raw_row['currency_id'] ) || ( isset( $raw_row['currency_id'] ) && $raw_row['currency_id'] == '' ) ) {
				$raw_row['currency_id'] = $default_data['currency_id'];
			}

			//Merge the default data with row data.
			//This must go at the end so it doesn't overwrite imported data.
			$raw_row = array_merge( (array)$default_data, $raw_row );
			//Debug::Arr($raw_row, 'Row+Default data: ', __FILE__, __LINE__, __METHOD__, 10);
		}

		//Debug::Arr($raw_row, 'postParse Row: ', __FILE__, __LINE__, __METHOD__, 10);
		return $raw_row;
	}

	/**
	 * @param int $validate_only EPOCH
	 * @return mixed
	 */
	function _import( $validate_only ) {
		return $this->getObject()->setUser( $this->getParsedData(), $validate_only );
	}

	//
	// Generic parser functions.
	//

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return int
	 */
	function parse_status( $input, $default_value = null, $parse_hint = null ) {

		if ( strtolower( $input ) == 'a'
				|| strtolower( $input ) == 'active' ) {
			$retval = 10;
		} else if ( strtolower( $input ) == 'disabled'
				|| strtolower( $input ) == 'inactive' ) {
			$retval = 11;
		} else if ( strtolower( $input ) == 't'
				|| strtolower( $input ) == 'terminated' ) {
			$retval = 20;
		} else if ( strtolower( $input ) == 'l'
				|| strtolower( $input ) == 'leave' ) {
			$retval = 16; //Leave - Other
		} else if ( strtolower( $input ) == 'i'
				|| strtolower( $input ) == 'injury' || strtolower( $input ) == 'illness' ) {
			$retval = 12; //Leave - Injury
		} else {
			$retval = (int)$input;
		}

		return $retval;
	}

	/**
	 * @return bool
	 */
	function getPermissionControlOptions() {
		//Get job titles
		$pglf = TTNew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pglf */
		$pglf->getByCompanyId( $this->company_id );
		$this->permission_control_options = (array)$pglf->getArrayByListFactory( $pglf, false, false ); //Include include in the name level, as it causes problems with exact matching.
		unset( $pglf );

		return true;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|int|mixed
	 */
	function parse_permission_control( $input, $default_value = null, $parse_hint = null ) {
		if ( trim( $input ) == '' ) {
			return TTUUID::getZeroID(); //No Permission Group
		}

		if ( !is_array( $this->permission_control_options ) ) {
			$this->getPermissionControlOptions();
		}

		$retval = $this->findClosestMatch( $input, $this->permission_control_options );
		if ( $retval === false ) {
			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}

	/**
	 * @return bool
	 */
	function getPolicyGroupOptions() {
		//Get job titles
		$pglf = TTNew( 'PolicyGroupListFactory' ); /** @var PolicyGroupListFactory $pglf */
		$pglf->getByCompanyId( $this->company_id );
		$this->policy_group_options = (array)$pglf->getArrayByListFactory( $pglf, false );
		unset( $pglf );

		return true;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|int|mixed
	 */
	function parse_policy_group( $input, $default_value = null, $parse_hint = null ) {
		if ( trim( $input ) == '' ) {
			return TTUUID::getZeroID(); //No Permission Group
		}

		if ( !is_array( $this->policy_group_options ) ) {
			$this->getPolicyGroupOptions();
		}

		$retval = $this->findClosestMatch( $input, $this->policy_group_options );
		if ( $retval === false ) {
			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}

	/**
	 * @return bool
	 */
	function getPayPeriodScheduleOptions() {
		//Get job titles
		$pglf = TTNew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $pglf */
		$pglf->getByCompanyId( $this->company_id );
		$this->pay_period_schedule_options = (array)$pglf->getArrayByListFactory( $pglf, false );
		unset( $pglf );

		return true;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|int|mixed
	 */
	function parse_pay_period_schedule( $input, $default_value = null, $parse_hint = null ) {
		if ( trim( $input ) == '' ) {
			return TTUUID::getZeroID(); //No Permission Group
		}

		if ( !is_array( $this->pay_period_schedule_options ) ) {
			$this->getPayPeriodScheduleOptions();
		}

		$retval = $this->findClosestMatch( $input, $this->pay_period_schedule_options );
		if ( $retval === false ) {
			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}

	/**
	 * @return bool
	 */
	function getUserTitleOptions() {
		//Get job titles
		$utlf = TTNew( 'UserTitleListFactory' ); /** @var UserTitleListFactory $utlf */
		$utlf->getByCompanyId( $this->company_id );
		$this->title_options = (array)$utlf->getArrayByListFactory( $utlf, false );
		unset( $utlf );

		return true;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|int|mixed
	 */
	function parse_title( $input, $default_value = null, $parse_hint = null ) {
		if ( trim( $input ) == '' ) {
			return TTUUID::getZeroID(); //No title
		}

		if ( !is_array( $this->title_options ) ) {
			$this->getUserTitleOptions();
		}

		$retval = $this->findClosestMatch( $input, $this->title_options );
		if ( $retval === false ) {
			if ( $this->getImportOptions( 'create_title' ) == true ) {
				$utf = TTnew( 'UserTitleFactory' ); /** @var UserTitleFactory $utf */
				$utf->setCompany( $this->company_id );
				$utf->setName( $input );

				if ( $utf->isValid() ) {
					$new_title_id = $utf->Save();
					$this->getUserTitleOptions(); //Update group records after we've added a new one.
					Debug::Text( 'Created new title name: ' . $input . ' ID: ' . $new_title_id, __FILE__, __LINE__, __METHOD__, 10 );

					return $new_title_id;
				}
				unset( $utf, $new_title_id );
			}

			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|int|mixed
	 */
	function parse_default_branch( $input, $default_value = null, $parse_hint = null ) {
		return $this->parse_branch( $input, $default_value, $parse_hint );
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|int|mixed
	 */
	function parse_default_department( $input, $default_value = null, $parse_hint = null ) {
		return $this->parse_department( $input, $default_value, $parse_hint );
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|int|mixed
	 */
	function parse_default_job( $input, $default_value = null, $parse_hint = null ) {
		return $this->parse_job( $input, $default_value, $parse_hint );
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|int|mixed
	 */
	function parse_default_job_item( $input, $default_value = null, $parse_hint = null ) {
		return $this->parse_job_item( $input, $default_value, $parse_hint );
	}

	/**
	 * @return bool
	 */
	function getUserGroupOptions() {
		//Get groups
		$uglf = TTNew( 'UserGroupListFactory' ); /** @var UserGroupListFactory $uglf */
		$uglf->getByCompanyId( $this->company_id );
		$this->user_group_options = (array)$uglf->getArrayByListFactory( $uglf, false );
		unset( $uglf );

		return true;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|int|mixed
	 */
	function parse_group( $input, $default_value = null, $parse_hint = null ) {
		return $this->parse_user_group( $input, $default_value, $parse_hint );
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|int|mixed
	 */
	function parse_user_group( $input, $default_value = null, $parse_hint = null ) {
		if ( trim( $input ) == '' ) {
			return TTUUID::getZeroID(); //No group
		}

		if ( !is_array( $this->user_group_options ) ) {
			$this->getUserGroupOptions();
		}

		$retval = $this->findClosestMatch( $input, $this->user_group_options );

		if ( $retval === false ) {
			if ( $this->getImportOptions( 'create_group' ) == true ) {
				$ugf = TTnew( 'UserGroupFactory' ); /** @var UserGroupFactory $ugf */
				$ugf->setCompany( $this->company_id );
				$ugf->setParent( TTUUID::getZeroID() );
				$ugf->setName( $input );

				if ( $ugf->isValid() ) {
					$new_group_id = $ugf->Save();
					$this->getUserGroupOptions(); //Update group records after we've added a new one.
					Debug::Text( 'Created new group name: ' . $input . ' ID: ' . $new_group_id, __FILE__, __LINE__, __METHOD__, 10 );

					return $new_group_id;
				}
				unset( $ugf, $new_group_id );
			}

			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}

	/**
	 * @return bool
	 */
	function getEthnicGroupOptions() {
		//Get groups
		$uglf = TTNew( 'EthnicGroupListFactory' ); /** @var EthnicGroupListFactory $uglf */
		$uglf->getByCompanyId( $this->company_id );
		$this->ethnic_group_options = (array)$uglf->getArrayByListFactory( $uglf, false );
		unset( $uglf );

		return true;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return array|bool|int|mixed
	 */
	function parse_ethnic_group( $input, $default_value = null, $parse_hint = null ) {
		if ( trim( $input ) == '' ) {
			return TTUUID::getZeroID(); //No group
		}

		if ( !is_array( $this->user_group_options ) ) {
			$this->getEthnicGroupOptions();
		}

		$retval = $this->findClosestMatch( $input, $this->ethnic_group_options );

		if ( $retval === false ) {
			if ( $this->getImportOptions( 'create_ethnic_group' ) == true ) {
				$egf = TTnew( 'EthnicGroupFactory' ); /** @var EthnicGroupFactory $egf */
				$egf->setCompany( $this->company_id );
				$egf->setName( $input );

				if ( $egf->isValid() ) {
					$new_group_id = $egf->Save();
					$this->getEthnicGroupOptions(); //Update group records after we've added a new one.
					Debug::Text( 'Created new ethnic group name: ' . $input . ' ID: ' . $new_group_id, __FILE__, __LINE__, __METHOD__, 10 );

					return $new_group_id;
				}
				unset( $egf, $new_group_id );
			}

			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}

	/**
	 * @return bool
	 */
	function getHierarchyControlOptions() {
		//Get job titles
		$hclf = TTNew( 'HierarchyControlListFactory' ); /** @var HierarchyControlListFactory $hclf */
		$hclf->getObjectTypeAppendedListByCompanyID( $this->company_id );
		$this->hierarchy_control_options = (array)$hclf->getArrayByListFactory( $hclf, true, false, true );
		unset( $hclf );

		return true;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return int
	 */
	function parse_hierarchy_control_display( $input, $default_value = null, $parse_hint = null ) {
		if ( trim( $input ) == '' ) {
			return TTUUID::getZeroID(); //No Hierarchy
		}

		if ( !is_array( $this->hierarchy_control_options ) ) {
			$this->getHierarchyControlOptions();
		}

		Debug::Text( 'Finding hierarchy for: ' . $input, __FILE__, __LINE__, __METHOD__, 10 );

		$retval = $this->findClosestMatch( $input, $this->hierarchy_control_options );
		if ( $retval === false ) {
			$retarr = -1; //Make sure this fails.
		} else {
			//Use only the permission object_type_id, if the hierarchies use all objects this will work fine as well.
			$retarr[100] = $retval;
		}

		return $retarr;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return string
	 */
	function parse_phone_id( $input, $default_value = null, $parse_hint = null ) {
		if ( strlen( $input ) < 4 ) {
			$retval = str_pad( $input, 4, 0, STR_PAD_LEFT );
		} else {
			$retval = $input;
		}

		return $retval;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return string
	 */
	function parse_phone_password( $input, $default_value = null, $parse_hint = null ) {
		if ( strlen( $input ) < 4 ) {
			$retval = str_pad( $input, 4, 0, STR_PAD_LEFT );
		} else {
			$retval = $input;
		}

		return $retval;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return false|int
	 */
	function parse_birth_date( $input, $default_value = null, $parse_hint = null ) {
		return $this->parse_date( $input, $default_value, $parse_hint );
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return false|int
	 */
	function parse_hire_date( $input, $default_value = null, $parse_hint = null ) {
		return $this->parse_date( $input, $default_value, $parse_hint );
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return false|int
	 */
	function parse_termination_date( $input, $default_value = null, $parse_hint = null ) {
		return $this->parse_date( $input, $default_value, $parse_hint );
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return false|int
	 */
	function parse_wage_effective_date( $input, $default_value = null, $parse_hint = null ) {
		return $this->parse_date( $input, $default_value, $parse_hint );
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return int
	 */
	function parse_wage_type( $input, $default_value = null, $parse_hint = null ) {
		if ( strtolower( $input ) == 'salary' || strtolower( $input ) == 'salaried' || strtolower( $input ) == 's' || strtolower( $input ) == 'annual' ) {
			$retval = 20;
		} else if ( strtolower( $input ) == 'month' || strtolower( $input ) == 'monthly' ) {
			$retval = 15;
		} else if ( strtolower( $input ) == 'biweekly' || strtolower( $input ) == 'bi-weekly' ) {
			$retval = 13;
		} else if ( strtolower( $input ) == 'week' || strtolower( $input ) == 'weekly' ) {
			$retval = 12;
		} else {
			$retval = 10;
		}

		return $retval;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return bool|float|int|number|string
	 */
	function parse_wage_weekly_time( $input, $default_value = null, $parse_hint = null ) {
		if ( isset( $parse_hint ) && $parse_hint != '' ) {
			TTDate::setTimeUnitFormat( $parse_hint );
		}

		$retval = TTDate::parseTimeUnit( $input );

		return $retval;
	}

	/**
	 * @param $input
	 * @param null $default_value
	 * @param null $parse_hint
	 * @return mixed
	 */
	function parse_wage( $input, $default_value = null, $parse_hint = null ) {
		$val = new Validator();
		$retval = $val->stripNonFloat( $input );

		return $retval;
	}
}

?>
