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
class PolicyGroupFactory extends Factory {
	protected $table = 'policy_group';
	protected $pk_sequence_name = 'policy_group_id_seq'; //PK Sequence name

	protected $company_obj = null;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {

		$retval = null;
		switch ( $name ) {
			case 'columns':
				$retval = [
						'-1000-name'        => TTi18n::gettext( 'Name' ),
						'-1010-description' => TTi18n::gettext( 'Description' ),
						'-1100-total_users' => TTi18n::gettext( 'Employees' ),

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
						'total_users',
						'updated_date',
						'updated_by',
				];
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [
						'name',
						'user',
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
				'id'                          => 'ID',
				'company_id'                  => 'Company',
				'name'                        => 'Name',
				'description'                 => 'Description',
				'user'                        => 'User',
				'total_users'                 => 'TotalUsers',
				'regular_time_policy'         => 'RegularTimePolicy',
				'over_time_policy'            => 'OverTimePolicy',
				'round_interval_policy'       => 'RoundIntervalPolicy',
				'premium_policy'              => 'PremiumPolicy',
				'meal_policy'                 => 'MealPolicy',
				'break_policy'                => 'BreakPolicy',
				'holiday_policy'              => 'HolidayPolicy',
				'accrual_policy'              => 'AccrualPolicy',
				'expense_policy'              => 'ExpensePolicy',
				'absence_policy'              => 'AbsencePolicy',
				'exception_policy_control_id' => 'ExceptionPolicyControlID',
				'deleted'                     => 'Deleted',
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
	 * @return array|bool
	 */
	function getUser() {
		$pgulf = TTnew( 'PolicyGroupUserListFactory' ); /** @var PolicyGroupUserListFactory $pgulf */
		$pgulf->getByPolicyGroupId( $this->getId() );

		$list = [];
		foreach ( $pgulf as $obj ) {
			$list[] = $obj->getUser();
		}

		if ( empty( $list ) == false ) {
			return $list;
		}

		return false;
	}

	/**
	 * @param string|string[] $ids UUID
	 * @return bool
	 */
	function setUser( $ids ) {
		if ( !is_array( $ids ) ) {
			$ids = [ $ids ];
		}

		if ( is_array( $ids ) ) {
			$tmp_ids = [];
			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$pgulf = TTnew( 'PolicyGroupUserListFactory' ); /** @var PolicyGroupUserListFactory $pgulf */
				$pgulf->getByPolicyGroupId( $this->getId() );
				foreach ( $pgulf as $obj ) {
					$id = $obj->getUser();
					Debug::text( 'Policy ID: ' . $obj->getPolicyGroup() . ' ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

					//Delete users that are not selected.
					if ( !in_array( $id, $ids ) ) {
						Debug::text( 'Deleting: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text( 'NOT Deleting : ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$tmp_ids[] = $id;
					}
				}
				unset( $id, $obj );
			}

			//Insert new mappings.
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			foreach ( $ids as $id ) {
				if ( isset( $ids ) && !in_array( $id, $tmp_ids ) ) {
					$pguf = TTnew( 'PolicyGroupUserFactory' ); /** @var PolicyGroupUserFactory $pguf */
					$pguf->setPolicyGroup( $this->getId() );
					$pguf->setUser( $id );

					$ulf->getById( $id );
					if ( $ulf->getRecordCount() > 0 ) {
						$obj = $ulf->getCurrent();

						if ( $this->Validator->isTrue( 'user',
													   $pguf->isValid(),
													   TTi18n::gettext( 'Selected employee is invalid or already assigned to another policy group' ) . ' (' . $obj->getFullName() . ')' ) ) {
							$pguf->save();
						}
					}
				}
			}

			return true;
		}

		Debug::text( 'No User IDs to set.', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return mixed
	 */
	function getTotalUsers() {
		$pgulf = TTnew( 'PolicyGroupUserListFactory' ); /** @var PolicyGroupUserListFactory $pgulf */
		return $pgulf->getTotalByPolicyGroupId( $this->getId() );
	}

	/**
	 * @return array|bool
	 */
	function getRegularTimePolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 100, $this->getID() );
	}

	/**
	 * @param string|string[] $ids UUID
	 * @return bool
	 */
	function setRegularTimePolicy( $ids ) {
		Debug::text( 'Setting Regular Time Policy IDs: ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 100, $this->getID(), $ids );
	}

	/**
	 * @return array|bool
	 */
	function getOverTimePolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 110, $this->getID() );
	}

	/**
	 * @param string|string[] $ids UUID
	 * @return bool
	 */
	function setOverTimePolicy( $ids ) {
		Debug::text( 'Setting OverTime Policy IDs: ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 110, $this->getID(), $ids );
	}

	/**
	 * @return array|bool
	 */
	function getPremiumPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 120, $this->getID() );
	}

	/**
	 * @param string|string[] $ids UUID
	 * @return bool
	 */
	function setPremiumPolicy( $ids ) {
		Debug::text( 'Setting Premium Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 120, $this->getID(), $ids );
	}

	/**
	 * @return array|bool
	 */
	function getRoundIntervalPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 130, $this->getID() );
	}

	/**
	 * @param string|string[] $ids UUID
	 * @return bool
	 */
	function setRoundIntervalPolicy( $ids ) {
		Debug::text( 'Setting Round Interval Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 130, $this->getID(), $ids );
	}

	/**
	 * @return array|bool
	 */
	function getAccrualPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 140, $this->getID() );
	}

	/**
	 * @param string|string[] $ids UUID
	 * @return bool
	 */
	function setAccrualPolicy( $ids ) {
		Debug::text( 'Setting Accrual Policy IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 140, $this->getID(), $ids );
	}

	/**
	 * @return array|bool
	 */
	function getMealPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 150, $this->getID() );
	}

	/**
	 * @param string|string[] $ids UUID
	 * @return bool
	 */
	function setMealPolicy( $ids ) {
		Debug::text( 'Setting Meal Policy IDs: ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 150, $this->getID(), $ids );
	}

	/**
	 * @return array|bool
	 */
	function getBreakPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 160, $this->getID() );
	}

	/**
	 * @param string|string[] $ids UUID
	 * @return bool
	 */
	function setBreakPolicy( $ids ) {
		Debug::text( 'Setting Break Policy IDs: ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 160, $this->getID(), $ids );
	}

	/**
	 * @return array|bool
	 */
	function getAbsencePolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 170, $this->getID() );
	}

	/**
	 * @param string|string[] $ids UUID
	 * @return bool
	 */
	function setAbsencePolicy( $ids ) {
		Debug::text( 'Setting Absence Policy IDs: ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 170, $this->getID(), (array)$ids );
	}

	/**
	 * @return array|bool
	 */
	function getHolidayPolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 180, $this->getID() );
	}

	/**
	 * @param string|string[] $ids UUID
	 * @return bool
	 */
	function setHolidayPolicy( $ids ) {
		Debug::text( 'Setting Holiday Policy IDs: ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 180, $this->getID(), (array)$ids );
	}

	/**
	 * @return array|bool
	 */
	function getExpensePolicy() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 200, $this->getID() );
	}

	/**
	 * @param string|string[] $ids UUID
	 * @return bool
	 */
	function setExpensePolicy( $ids ) {
		Debug::text( 'Setting Expense Policy IDs: ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 200, $this->getID(), (array)$ids );
	}


	/**
	 * @return bool|mixed
	 */
	function getExceptionPolicyControlID() {
		return $this->getGenericDataValue( 'exception_policy_control_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setExceptionPolicyControlID( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'exception_policy_control_id', $value );
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
		// Name
		if ( $this->Validator->getValidateOnly() == false ) { //Don't check the below when mass editing.
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
		// Exception Policy
		if ( $this->getExceptionPolicyControlID() !== false && $this->getExceptionPolicyControlID() != TTUUID::getZeroID() ) {
			$epclf = TTnew( 'ExceptionPolicyControlListFactory' ); /** @var ExceptionPolicyControlListFactory $epclf */
			$this->Validator->isResultSetWithRows( 'exception_policy',
												   $epclf->getByID( $this->getExceptionPolicyControlID() ),
												   TTi18n::gettext( 'Exception Policy is invalid' )
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		if ( $this->getDeleted() == true ) {
			Debug::Text( 'UnAssign Policy Group from User Defaults...' . $this->getId(), __FILE__, __LINE__, __METHOD__, 10 );
			$udf = TTnew( 'UserDefaultFactory' ); /** @var UserDefaultFactory $udf */

			$query = 'update ' . $udf->getTable() . ' set policy_group_id = \'' . TTUUID::getZeroID() . '\' where company_id = \'' . TTUUID::castUUID( $this->getCompany() ) . '\' AND policy_group_id = \'' . TTUUID::castUUID( $this->getId() ) . '\'';
			$this->ExecuteSQL( $query );
		}

		return true;
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
				if ( isset( $data[$key] ) ) {

					$function = 'set' . $function;
					switch ( $key ) {
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
						//case 'total_users':
						//	$data[$variable] = $this->getColumn( $variable );
						//	break;
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Policy Group' ), null, $this->getTable(), $this );
	}
}

?>
