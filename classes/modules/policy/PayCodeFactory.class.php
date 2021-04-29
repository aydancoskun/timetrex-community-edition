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
class PayCodeFactory extends Factory {
	protected $table = 'pay_code';
	protected $pk_sequence_name = 'pay_code_id_seq'; //PK Sequence name

	protected $company_obj = null;
	protected $pay_stub_entry_account_obj = null;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {
		$retval = null;
		switch ( $name ) {
			case 'type': //Should this be status? This could be useful for overtime/premium and such as well, so it needs to stay here.
				$retval = [
						10 => TTi18n::gettext( 'Paid' ),
						12 => TTi18n::gettext( 'Paid (Above Salary)' ),
						20 => TTi18n::gettext( 'Unpaid' ),
						30 => TTi18n::gettext( 'Dock' ),
				];
				break;
			case 'paid_type': //Types that are considered paid.
				$retval = [ 10, 12 ];
				break;
			case 'columns':
				$retval = [
						'-1010-name'        => TTi18n::gettext( 'Name' ),
						'-1020-description' => TTi18n::gettext( 'Description' ),

						'-1030-code' => TTi18n::gettext( 'Code' ),

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
				'id'          => 'ID',
				'company_id'  => 'Company',
				'name'        => 'Name',
				'description' => 'Description',

				'code' => 'Code',

				'type_id' => 'Type',
				'type'    => false,

				'pay_formula_policy_id' => 'PayFormulaPolicy',
				'pay_formula_policy'    => false,

				'pay_stub_entry_account_id' => 'PayStubEntryAccountId',

				'in_use'  => false,
				'deleted' => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getPayStubEntryAccountObject() {
		return $this->getGenericObject( 'PayStubEntryAccountListFactory', $this->getPayStubEntryAccountID(), 'pay_stub_entry_account_obj' );
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
	 * @return bool|mixed
	 */
	function getCode() {
		return $this->getGenericDataValue( 'code' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCode( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'code', $value );
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
	 * @return bool
	 */
	function isPaid() {
		if ( $this->getType() == 10 || $this->getType() == 12 ) {
			return true;
		}

		return false;
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

	/**
	 * @return bool|mixed
	 */
	function getPayStubEntryAccountId() {
		return $this->getGenericDataValue( 'pay_stub_entry_account_id' );
	}

	/**
	 * Don't require a pay stub entry account to be defined, as there may be some cases
	 * in job costing situations where the rate of pay should be 1.0, but going to no pay stub account so their reports can reflect
	 * proper rates of pay but not have it actually appear on pay stubs.
	 * @param string $value UUID
	 * @return bool
	 */
	function setPayStubEntryAccountId( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::text( 'Entry Account ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'pay_stub_entry_account_id', $value );
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
										2, 70 ); //Needs to be long enough for upgrade procedure when converting from other policies.
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
		// Code
		if ( $this->getCode() !== false ) {
			$this->Validator->isLength( 'code',
										$this->getCode(),
										TTi18n::gettext( 'Code is too short or too long' ),
										1, 50
			);
		}
		// Type
		if ( $this->getType() !== false ) {
			$this->Validator->inArrayKey( 'type_id',
										  $this->getType(),
										  TTi18n::gettext( 'Incorrect Type' ),
										  $this->getOptions( 'type' )
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
		// Pay Stub Account
		if ( $this->getPayStubEntryAccountId() !== false && $this->getPayStubEntryAccountId() != TTUUID::getZeroID() ) {
			$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
			$this->Validator->isResultSetWithRows( 'pay_stub_entry_account_id',
												   $psealf->getById( $this->getPayStubEntryAccountId() ),
												   TTi18n::gettext( 'Invalid Pay Stub Account' )
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->getDeleted() == true ) {
			//Check to make sure there are no hours using this PayCode.
			$udtlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udtlf */
			$udtlf->getByPayCodeId( $this->getId(), 1 ); //Limit 1
			if ( $udtlf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  false,
										  TTi18n::gettext( 'This pay code is currently in use' ) . ' ' . TTi18n::gettext( 'by employee timesheets' ) );
			}

			$rtplf = TTNew( 'RegularTimePolicyListFactory' ); /** @var RegularTimePolicyListFactory $rtplf */
			$rtplf->getByCompanyIdAndPayCodeId( $this->getCompany(), $this->getId() );
			if ( $rtplf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  false,
										  TTi18n::gettext( 'This pay code is currently in use' ) . ' ' . TTi18n::gettext( 'by regular time policies' ) );
			}

			$otplf = TTNew( 'OverTimePolicyListFactory' ); /** @var OverTimePolicyListFactory $otplf */
			$otplf->getByCompanyIdAndPayCodeId( $this->getCompany(), $this->getId() );
			if ( $otplf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  false,
										  TTi18n::gettext( 'This pay code is currently in use' ) . ' ' . TTi18n::gettext( 'by overtime policies' ) );
			}

			$pplf = TTNew( 'PremiumPolicyListFactory' ); /** @var PremiumPolicyListFactory $pplf */
			$pplf->getByCompanyIdAndPayCodeId( $this->getCompany(), $this->getId() );
			if ( $pplf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  false,
										  TTi18n::gettext( 'This pay code is currently in use' ) . ' ' . TTi18n::gettext( 'by premium policies' ) );
			}

			$aplf = TTNew( 'AbsencePolicyListFactory' ); /** @var AbsencePolicyListFactory $aplf */
			$aplf->getByCompanyIdAndPayCodeId( $this->getCompany(), $this->getId() );
			if ( $aplf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  false,
										  TTi18n::gettext( 'This pay code is currently in use' ) . ' ' . TTi18n::gettext( 'by absence policies' ) );
			}

			$mplf = TTNew( 'MealPolicyListFactory' ); /** @var MealPolicyListFactory $mplf */
			$mplf->getByCompanyIdAndPayCodeId( $this->getCompany(), $this->getId() );
			if ( $mplf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  false,
										  TTi18n::gettext( 'This pay code is currently in use' ) . ' ' . TTi18n::gettext( 'by meal policies' ) );
			}

			$bplf = TTNew( 'BreakPolicyListFactory' ); /** @var BreakPolicyListFactory $bplf */
			$bplf->getByCompanyIdAndPayCodeId( $this->getCompany(), $this->getId() );
			if ( $bplf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  false,
										  TTi18n::gettext( 'This pay code is currently in use' ) . ' ' . TTi18n::gettext( 'by break policies' ) );
			}

			$csplf = TTNew( 'ContributingPayCodePolicyListFactory' ); /** @var ContributingPayCodePolicyListFactory $csplf */
			$csplf->getByCompanyIdAndPayCodeId( $this->getCompany(), $this->getId() );
			if ( $csplf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  false,
										  TTi18n::gettext( 'This pay code is currently in use' ) . ' ' . TTi18n::gettext( 'by contributing pay code policies' ) );
			}
		} else {
			if ( TTUUID::isUUID( $this->getId() ) && $this->getId() != TTUUID::getZeroID() && $this->getId() != TTUUID::getNotExistID()
					&& $this->getPayFormulaPolicy() == TTUUID::getZeroID() ) { //Defined by Policy
				//Check to make sure all policies associated with this pay code have a pay formula defined
				$rtplf = TTNew( 'RegularTimePolicyListFactory' ); /** @var RegularTimePolicyListFactory $rtplf */
				$rtplf->getByCompanyIdAndPayCodeIdAndPayFormulaPolicyId( $this->getCompany(), $this->getId(), TTUUID::getZeroID() );
				if ( $rtplf->getRecordCount() > 0 ) {
					$this->Validator->isTRUE( 'pay_formula_policy_id',
											  false,
											  TTi18n::gettext( 'Regular Time Policy: %1 requires this Pay Formula Policy to be defined', [ $rtplf->getCurrent()->getName() ] ) );
				}

				$otplf = TTNew( 'OverTimePolicyListFactory' ); /** @var OverTimePolicyListFactory $otplf */
				$otplf->getByCompanyIdAndPayCodeIdAndPayFormulaPolicyId( $this->getCompany(), $this->getId(), TTUUID::getZeroID() );
				if ( $otplf->getRecordCount() > 0 ) {
					$this->Validator->isTRUE( 'pay_formula_policy_id',
											  false,
											  TTi18n::gettext( 'Overtime Policy: %1 requires this Pay Formula Policy to be defined', [ $otplf->getCurrent()->getName() ] ) );
				}

				$pplf = TTNew( 'PremiumPolicyListFactory' ); /** @var PremiumPolicyListFactory $pplf */
				$pplf->getByCompanyIdAndPayCodeIdAndPayFormulaPolicyId( $this->getCompany(), $this->getId(), TTUUID::getZeroID() );
				if ( $pplf->getRecordCount() > 0 ) {
					$this->Validator->isTRUE( 'pay_formula_policy_id',
											  false,
											  TTi18n::gettext( 'Premium Policy: %1 requires this Pay Formula Policy to be defined', [ $pplf->getCurrent()->getName() ] ) );
				}

				$aplf = TTNew( 'AbsencePolicyListFactory' ); /** @var AbsencePolicyListFactory $aplf */
				$aplf->getByCompanyIdAndPayCodeIdAndPayFormulaPolicyId( $this->getCompany(), $this->getId(), TTUUID::getZeroID() );
				if ( $aplf->getRecordCount() > 0 ) {
					$this->Validator->isTRUE( 'pay_formula_policy_id',
											  false,
											  TTi18n::gettext( 'Absence Policy: %1 requires this Pay Formula Policy to be defined', [ $aplf->getCurrent()->getName() ] ) );
				}

				$mplf = TTNew( 'MealPolicyListFactory' ); /** @var MealPolicyListFactory $mplf */
				$mplf->getByCompanyIdAndPayCodeIdAndPayFormulaPolicyId( $this->getCompany(), $this->getId(), TTUUID::getZeroID() );
				if ( $mplf->getRecordCount() > 0 ) {
					$this->Validator->isTRUE( 'pay_formula_policy_id',
											  false,
											  TTi18n::gettext( 'Meal Policy: %1 requires this Pay Formula Policy to be defined', [ $mplf->getCurrent()->getName() ] ) );
				}

				$bplf = TTNew( 'BreakPolicyListFactory' ); /** @var BreakPolicyListFactory $bplf */
				$bplf->getByCompanyIdAndPayCodeIdAndPayFormulaPolicyId( $this->getCompany(), $this->getId(), TTUUID::getZeroID() );
				if ( $bplf->getRecordCount() > 0 ) {
					$this->Validator->isTRUE( 'pay_formula_policy_id',
											  false,
											  TTi18n::gettext( 'Break Policy: %1 requires this Pay Formula Policy to be defined', [ $bplf->getCurrent()->getName() ] ) );
				}
			}

			if ( $ignore_warning == false && $this->Validator->getValidateOnly() == false ) { //Don't check the below when mass editing, but must check when adding a new record.
				if ( $this->getType() != 20 && $this->getPayStubEntryAccountId() == TTUUID::getZeroID() ) {
					//This could be changed to a validation error instead, but then it may require customers who don't use TimeTrex for payroll to have to maintain Pay Stub Accounts.
					$this->Validator->Warning( 'pay_stub_entry_account_id', TTi18n::gettext( 'Pay Stub Account must be specified for amounts to appear on pay stubs' ) );
				}
			}
		}

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
			Debug::Text( 'UnAssign PayCode from ContributingShiftPolicies: ' . $this->getId(), __FILE__, __LINE__, __METHOD__, 10 );
			$cgmf = TTnew( 'CompanyGenericMapFactory' ); /** @var CompanyGenericMapFactory $cgmf */

			$query = 'delete from ' . $cgmf->getTable() . ' where company_id = \'' . TTUUID::castUUID( $this->getCompany() ) . '\' AND object_type_id = 90 AND map_id = \'' . TTUUID::castUUID( $this->getID() ) . '\'';
			$this->ExecuteSQL( $query );
		}

		$this->removeCache( $this->getId() );

		return true;
	}

	/**
	 * Migrate data from one pay code to another, without recalculating timesheets.
	 * @param string $company_id UUID
	 * @param string[] $src_ids  UUID
	 * @param string $dst_id     UUID
	 * @return bool
	 */
	function migrate( $company_id, $src_ids, $dst_id ) {
		$dst_id = TTUUID::castUUID( $dst_id );
		$src_ids = array_unique( (array)$src_ids );

		if ( empty( $dst_id ) && $dst_id != TTUUID::getZeroID() ) {
			return false;
		}

		$pclf = TTnew( 'PayCodeListFactory' ); /** @var PayCodeListFactory $pclf */
		$pclf->getByIdAndCompanyID( $dst_id, $company_id );
		if ( $pclf->getRecordCount() != 1 ) {
			Debug::Text( 'Destination PayCode not valid: ' . $dst_id, __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		if ( is_array( $src_ids ) && count( $src_ids ) > 0 ) {
			$pclf->getByIdAndCompanyID( $src_ids, $company_id );
			if ( $pclf->getRecordCount() != count( $src_ids ) ) {
				Debug::Arr( $src_ids, 'Source PayCode(s) not valid: ', __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}
		}

		$ph = [
				'dst_pay_code_id' => TTUUID::castUUID( $dst_id ),
		];

		$udtf = TTNew( 'UserDateTotalFactory' ); /** @var UserDateTotalFactory $udtf */

		$query = 'update ' . $udtf->getTable() . ' set pay_code_id = ? where pay_code_id in (' . $this->getListSQL( $src_ids, $ph, 'uuid' ) . ') AND deleted = 0';
		$this->ExecuteSQL( $query, $ph );

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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Pay Code' ), null, $this->getTable(), $this );
	}
}

?>
