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
 * @package Modules\Accrual
 */
class AccrualBalanceFactory extends Factory {
	protected $table = 'accrual_balance';
	protected $pk_sequence_name = 'accrual_balance_id_seq'; //PK Sequence name

	var $user_obj = NULL;

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

										'-1010-first_name' => TTi18n::gettext('First Name'),
										'-1020-last_name' => TTi18n::gettext('Last Name'),

										'-1030-accrual_policy_account' => TTi18n::gettext('Accrual Account'),
										//'-1040-accrual_policy_type' => TTi18n::gettext('Accrual Policy Type'),
										'-1050-balance' => TTi18n::gettext('Balance'),

										'-1090-title' => TTi18n::gettext('Title'),
										'-1099-group' => TTi18n::gettext('Group'),
										'-1100-default_branch' => TTi18n::gettext('Branch'),
										'-1110-default_department' => TTi18n::gettext('Department'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( array('accrual_policy_account', 'balance'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'first_name',
								'last_name',
								'accrual_policy_account',
								//'accrual_policy_type',
								'balance'
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
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
											'user_id' => 'User',
											'first_name' => FALSE,
											'last_name' => FALSE,
											'accrual_policy_account_id' => 'AccrualPolicyAccount',
											'accrual_policy_account' => FALSE,
											//'accrual_policy_type_id' => FALSE,
											//'accrual_policy_type' => FALSE,
											'default_branch' => FALSE,
											'default_department' => FALSE,
											'group' => FALSE,
											'title' => FALSE,
											'balance' => 'Balance',
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
	 * @return bool|mixed
	 */
	function getUser() {
		return $this->getGenericDataValue( 'user_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUser( $value) {
		$value = trim($value);
		$value = TTUUID::castUUID( $value);
		if ( $value == '' ) {
			$value = TTUUID::getZeroID();
		}

		return $this->setGenericDataValue( 'user_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getAccrualPolicyAccount() {
		return $this->getGenericDataValue( 'accrual_policy_account_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAccrualPolicyAccount( $value) {
		$value = trim($value);
		$value = TTUUID::castUUID( $value);
		if ( $value == '' ) {
			$value = TTUUID::getZeroID();
		}
		return $this->setGenericDataValue( 'accrual_policy_account_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getBalance() {
		return $this->getGenericDataValue( 'balance' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setBalance( $value) {
		$value = trim($value);
		if ( empty($value) ) {
			$value = 0;
		}
		return $this->setGenericDataValue('balance', $value);
	}

	/**
	 * @return bool
	 */
	function getCreatedBy() {
		return FALSE;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setCreatedBy( $id = NULL) {
		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getUpdatedDate() {
		return FALSE;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setUpdatedDate( $epoch = NULL) {
		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getUpdatedBy() {
		return FALSE;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setUpdatedBy( $id = NULL) {
		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getDeletedDate() {
		return FALSE;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setDeletedDate( $epoch = NULL) {
		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getDeletedBy() {
		return FALSE;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setDeletedBy( $id = NULL) {
		return FALSE;
	}

	/**
	 * @param string $user_id UUID
	 * @param string $accrual_policy_account_id UUID
	 * @return bool
	 */
	static function calcBalance( $user_id, $accrual_policy_account_id = NULL ) {
		global $profiler;

		$profiler->startTimer( "AccrualBalanceFactory::calcBalance()");

		$retval = FALSE;
		$update_balance = TRUE;

		$alf = TTnew( 'AccrualListFactory' );

		$alf->StartTransaction();
		//$alf->db->SetTransactionMode( 'SERIALIZABLE' ); //Serialize balance transactions so concurrency issues don't corrupt the balance.

		$balance = $alf->getSumByUserIdAndAccrualPolicyAccount($user_id, $accrual_policy_account_id);
		Debug::text('Balance for User ID: '. $user_id .' Accrual Account ID: '. $accrual_policy_account_id .' Balance: '. $balance, __FILE__, __LINE__, __METHOD__, 10);

		$ablf = TTnew( 'AccrualBalanceListFactory' );
		$ablf->getByUserIdAndAccrualPolicyAccount( $user_id, $accrual_policy_account_id);
		Debug::text('Found balance records: '. $ablf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $ablf->getRecordCount() > 1 ) { //In case multiple records exist, delete them all and re-insert.
			foreach($ablf as $ab_obj) {
				$ab_obj->Delete();
			}
			$ab_obj = TTnew( 'AccrualBalanceFactory' );
		} elseif( $ablf->getRecordCount() == 1 ) {
			$ab_obj = $ablf->getCurrent();
			if ( $balance == $ab_obj->getBalance() ) {
				Debug::text('Balance has not changed, not updating: '. $balance, __FILE__, __LINE__, __METHOD__, 10);
				$update_balance = FALSE;
			}
		} else { //No balance record exists yet.
			$ab_obj = TTnew( 'AccrualBalanceFactory' );
		}

		if ( $update_balance == TRUE ) {
			Debug::text('Setting new balance to: '. $balance, __FILE__, __LINE__, __METHOD__, 10);
			$ab_obj->setUser( $user_id );
			$ab_obj->setAccrualPolicyAccount( $accrual_policy_account_id );
			$ab_obj->setBalance( $balance );
			if ( $ab_obj->isValid() ) {
				$retval = $ab_obj->Save();
			} else {
				$alf->FailTransaction();
				Debug::text('Setting new balance failed for User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		$alf->CommitTransaction();
		//$alf->db->SetTransactionMode(''); //Restore default transaction mode.

		$profiler->stopTimer( "AccrualBalanceFactory::calcBalance()");

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


		// User
		$ulf = TTnew( 'UserListFactory' );
		$this->Validator->isResultSetWithRows(	'user_id',
													$ulf->getByID( $this->getUser() ),
													TTi18n::gettext('Invalid Employee')
												);

		// Accrual Policy Account
		if ( $this->getAccrualPolicyAccount() != TTUUID::getZeroID() ) {
			$apalf = TTnew( 'AccrualPolicyAccountListFactory' );
			$this->Validator->isResultSetWithRows(	'accrual_policy_account_id',
													$apalf->getByID($this->getAccrualPolicyAccount()),
													TTi18n::gettext('Accrual Account is invalid')
												);
		}
		// Balance
		if ( $this->getBalance() != 0 ) {
			$this->Validator->isNumeric(		'balance',
													$this->getBalance(),
													TTi18n::gettext('Incorrect Balance')
												);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//

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
	 * @param null $include_columns
	 * @param bool $permission_children_ids
	 * @return array
	 */
	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE  ) {
		$data = array();
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			//$apf = TTnew( 'AccrualPolicyFactory' );

			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'accrual_policy_account':
						//case 'accrual_policy_type_id':
						case 'first_name':
						case 'last_name':
						case 'title':
						case 'group':
						case 'default_branch':
						case 'default_department':
							$data[$variable] = $this->getColumn( $variable );
							break;
						//case 'accrual_policy_type':
						//	$data[$variable] = Option::getByKey( $this->getColumn( 'accrual_policy_type_id' ), $apf->getOptions( 'type' ) );
						//	break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getPermissionColumns( $data, $this->getUser(), FALSE, $permission_children_ids, $include_columns );
			//Accrual Balances are only created/modified by the system.
			//$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

}
?>
