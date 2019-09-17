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
class AccrualFactory extends Factory {
	protected $table = 'accrual';
	protected $pk_sequence_name = 'accrual_id_seq'; //PK Sequence name

	var $user_obj = NULL;

	protected $system_type_ids = array(10, 20, 75, 76); //These all special types reserved for system use only.

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|bool|null
	 */
	function _getFactoryOptions( $name, $parent = NULL ) {
		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
										10 => TTi18n::gettext('Banked'), //System: Can never be deleted/edited/added
										20 => TTi18n::gettext('Used'), //System: Can never be deleted/edited/added
										30 => TTi18n::gettext('Awarded'),
										40 => TTi18n::gettext('Un-Awarded'),
										50 => TTi18n::gettext('Gift'),
										55 => TTi18n::gettext('Paid Out'),
										60 => TTi18n::gettext('Rollover Adjustment'),
										70 => TTi18n::gettext('Initial Balance'),
										75 => TTi18n::gettext('Calendar-Based Accrual Policy'), //System: Can never be added or edited.
										76 => TTi18n::gettext('Hour-Based Accrual Policy'), //System: Can never be added or edited.
										80 => TTi18n::gettext('Other')
									);
				break;
			case 'system_type':
				$retval = array_intersect_key( $this->getOptions('type'), array_flip( $this->system_type_ids ) );
				break;
			case 'add_type':
			case 'edit_type':
			case 'user_type':
				$retval = array_diff_key( $this->getOptions('type'), array_flip( $this->system_type_ids ) );
				break;
			case 'delete_type': //Types that can be deleted
				$retval = $this->getOptions('type');
				unset($retval[10], $retval[20]); //Remove just Banked/Used as those can't be deleted.
				break;
			case 'accrual_policy_type':
				$apf = TTNew('AccrualPolicyFactory'); /** @var AccrualPolicyFactory $apf */
				$retval = $apf->getOptions('type');
				break;
			case 'columns':
				$retval = array(

										'-1010-first_name' => TTi18n::gettext('First Name'),
										'-1020-last_name' => TTi18n::gettext('Last Name'),

										'-1030-accrual_policy_account' => TTi18n::gettext('Accrual Account'),
										'-1040-type' => TTi18n::gettext('Type'),
										//'-1050-time_stamp' => TTi18n::gettext('Date'),
										'-1050-date_stamp' => TTi18n::gettext('Date'), //Date stamp is combination of time_stamp and user_date.date_stamp columns.
										'-1060-amount' => TTi18n::gettext('Amount'),
										'-1070-note' => TTi18n::gettext('Note'),

										'-1090-title' => TTi18n::gettext('Title'),
										'-1099-user_group' => TTi18n::gettext('Group'),
										'-1100-default_branch' => TTi18n::gettext('Branch'),
										'-1110-default_department' => TTi18n::gettext('Department'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( array('accrual_policy_account', 'type', 'date_stamp', 'amount'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'first_name',
								'last_name',
								'accrual_policy_account',
								'type',
								'amount',
								'date_stamp'
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
										'id' => 'ID',
										'user_id' => 'User',
										'first_name' => FALSE,
										'last_name' => FALSE,
										'default_branch' => FALSE,
										'default_department' => FALSE,
										'user_group' => FALSE,
										'title' => FALSE,
										'accrual_policy_account_id' => 'AccrualPolicyAccount',
										'accrual_policy_account' => FALSE,
										'accrual_policy_id' => 'AccrualPolicy',
										'accrual_policy' => FALSE,
										'accrual_policy_type' => FALSE,
										'type_id' => 'Type',
										'type' => FALSE,
										'user_date_total_id' => 'UserDateTotalID',
										'date_stamp' => FALSE,
										'time_stamp' => 'TimeStamp',
										'amount' => 'Amount',
										'note' => 'Note',
										'deleted' => 'Deleted',
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
		return $this->getGenericDataValue('user_id');
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUser( $value) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue('user_id', $value);
	}

	/**
	 * @return bool|mixed
	 */
	function getAccrualPolicyAccount() {
		return $this->getGenericDataValue('accrual_policy_account_id');
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAccrualPolicyAccount( $value) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue('accrual_policy_account_id', $value);
	}

	/**
	 * @return bool|mixed
	 */
	function getAccrualPolicy() {
		return $this->getGenericDataValue('accrual_policy_id');
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAccrualPolicy( $value) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue('accrual_policy_id', $value);
	}

	/**
	 * @return int
	 */
	function getType() {
		return (int)$this->getGenericDataValue('type_id');
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setType( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'type_id', $value );
	}

	/**
	 * @return bool
	 */
	function isSystemType() {
		if ( in_array( $this->getType(), $this->system_type_ids ) ) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool|mixed
	 */
	function getUserDateTotalID() {
		return $this->getGenericDataValue('user_date_total_id');
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserDateTotalID( $value) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue('user_date_total_id', $value);
	}

	/**
	 * @param bool $raw
	 * @return bool|int|mixed
	 */
	function getTimeStamp( $raw = FALSE ) {
		$value = $this->getGenericDataValue('time_stamp');
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
	 * @param $value
	 * @return bool
	 */
	function setTimeStamp( $value) {
		$value = ( !is_int($value) ) ? trim($value) : $value; //Dont trim integer values, as it changes them to strings.
		return $this->setGenericDataValue( 'time_stamp', $value );
	}

	/**
	 * @param $amount
	 * @return bool
	 */
	function isValidAmount( $amount) {
		Debug::text('Type: '. $this->getType() .' Amount: '. $amount, __FILE__, __LINE__, __METHOD__, 10);
		//Based on type, set Amount() pos/neg
		switch ( $this->getType() ) {
			case 10: // Banked
			case 30: // Awarded
			case 50: // Gifted
				if ( $amount >= 0 ) {
					return TRUE;
				}
				break;
			case 20: // Used
			case 55: // Paid Out
			case 40: // Un Awarded
				if ( $amount <= 0 ) {
					return TRUE;
				}
				break;
			default:
				return TRUE;
				break;
		}

		return FALSE;

	}

	/**
	 * @return bool|mixed
	 */
	function getAmount() {
		return $this->getGenericDataValue( 'amount' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAmount( $value) {
		$value = trim($value);
		if	( empty($value) ) {
			$value = 0;
		}
		return $this->setGenericDataValue( 'amount', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getNote() {
		return $this->getGenericDataValue('note');
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setNote( $value) {
		$value = trim($value);
		return $this->setGenericDataValue('note', $value);
	}

	/**
	 * @return bool
	 */
	function getEnableCalcBalance() {
		if ( isset($this->calc_balance) ) {
			return $this->calc_balance;
		}

		return FALSE;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableCalcBalance( $bool) {
		$this->calc_balance = $bool;

		return TRUE;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = TRUE ) {

		//
		// BELOW: Validation code moved from set*() functions.
		//

		//User
		if ( $this->Validator->getValidateOnly() == FALSE ) {
			if ( $this->getUser() == FALSE OR $this->getUser() == TTUUID::getZeroID() ) {
				$this->Validator->isTrue(		'user_id',
												FALSE,
												TTi18n::gettext('Please specify an employee'));
			}
		}
		if ( $this->getUser() != '' AND $this->Validator->isError('user_id') == FALSE ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$this->Validator->isResultSetWithRows(	'user_id',
															$ulf->getByID($this->getUser()),
															TTi18n::gettext('Invalid Employee')
														);
		}
		// Accrual Policy
		if ( $this->getAccrualPolicy() != '' AND $this->getAccrualPolicy() != TTUUID::getZeroID() ) {
			$aplf = TTnew( 'AccrualPolicyListFactory' ); /** @var AccrualPolicyListFactory $aplf */
			$this->Validator->isResultSetWithRows(	'accrual_policy_id',
													  $aplf->getByID($this->getAccrualPolicy()),
													  TTi18n::gettext('Accrual Policy is invalid')
			);
		}

		// Accrual Policy Account
		if ( $this->Validator->getValidateOnly() == FALSE ) {
			if ( $this->getAccrualPolicyAccount() == FALSE OR $this->getAccrualPolicyAccount() == TTUUID::getZeroID() ) {
				$this->Validator->isTrue(		'accrual_policy_account_id',
												FALSE,
												TTi18n::gettext('Please select an accrual account'));
			}
		}
		if ( $this->getAccrualPolicyAccount() != '' AND $this->Validator->isError('accrual_policy_account_id') == FALSE ) {
			$apalf = TTnew( 'AccrualPolicyAccountListFactory' ); /** @var AccrualPolicyAccountListFactory $apalf */
			$this->Validator->isResultSetWithRows(	'accrual_policy_account_id',
														$apalf->getByID($this->getAccrualPolicyAccount()),
														TTi18n::gettext('Accrual Account is invalid')
													);
		}
		// Type
		if ( $this->Validator->getValidateOnly() == FALSE ) { //Don't do the follow validation checks during Mass Edit.
			if ( $this->getType() == FALSE OR $this->getType() == 0 ) {
				$this->Validator->isTrue(		'type_id',
													FALSE,
													TTi18n::gettext('Please specify accrual type'));
			}
		}
		if ( $this->getType() != '' AND $this->Validator->isError('type_id') == FALSE ) {
			$this->Validator->inArrayKey(	'type_id',
													$this->getType(),
													TTi18n::gettext('Incorrect Type'),
													$this->getOptions('type')
												);
		}
		// UserDateTotal
		if ( $this->getUserDateTotalID() != '' AND $this->getUserDateTotalID() != TTUUID::getZeroID() ) {
			$udtlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udtlf */
			$this->Validator->isResultSetWithRows(	'user_date_total',
													  $udtlf->getByID($this->getUserDateTotalID()),
													  TTi18n::gettext('User Date Total ID is invalid')
			);
		}

		// Time stamp
		if ( $this->getTimeStamp() != '' ) {
			$this->Validator->isDate(		'times_tamp',
											$this->getTimeStamp(),
											TTi18n::gettext('Incorrect time stamp')
										);
		}


		// Amount
		if ( $this->getAmount() !== FALSE ) {
			$this->Validator->isNumeric(		'amount',
														$this->getAmount(),
														TTi18n::gettext('Incorrect Amount')
													);
			if ( $this->Validator->isError('amount') == FALSE ) {
				$this->Validator->isTrue(			'amount',
															$this->isValidAmount($this->getAmount()),
															TTi18n::gettext('Amounts of type "%1" must be a %2 value instead', array( Option::getByKey( $this->getType(), $this->getOptions('type') ), ( ( $this->getAmount() < 0 AND $this->isValidAmount($this->getAmount()) == FALSE ) ? TTi18n::getText('positive') : TTi18n::getText('negative') ) ) )
														);
			}
		}

		// Note
		if ( $this->getNote() != '' ) {
			$this->Validator->isLength(		'note',
											$this->getNote(),
											TTi18n::gettext('Note is too long'),
											0,
											1024
										);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		if ( $this->getDeleted() == TRUE ) {
			$this->Validator->inArrayKey(	'type_id',
											 $this->getType(),
											 TTi18n::gettext( 'Unable to delete system accrual records, modify the employees schedule/timesheet instead' ),
											 $this->getOptions( 'delete_type' )
			);
		} elseif ( $this->isNew(TRUE) == FALSE ) {
			$this->Validator->inArrayKey(	'type_id',
											 $this->getType(),
											 TTi18n::gettext( 'Unable to modify system accrual records' ),
											 $this->getOptions( 'user_type' )
			);
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getTimeStamp() == FALSE ) {
			$this->setTimeStamp( TTDate::getTime() );
		}

		//Delete duplicates before saving.
		//Or orphaned entries on Sum'ing?
		//Would have to do it on view as well though.
		if ( TTUUID::isUUID( $this->getUserDateTotalID() ) AND $this->getUserDateTotalID() != TTUUID::getZeroID() AND $this->getUserDateTotalID() != TTUUID::getNotExistID() ) {
			$alf = TTnew( 'AccrualListFactory' ); /** @var AccrualListFactory $alf */
			$alf->getByUserIdAndAccrualPolicyAccountAndAccrualPolicyAndUserDateTotalID( $this->getUser(), $this->getAccrualPolicyAccount(), $this->getAccrualPolicy(), $this->getUserDateTotalID() );
			Debug::text('Found Duplicate Records: '. (int)$alf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			if ( $alf->getRecordCount() > 0 ) {
				foreach($alf as $a_obj ) {
					if ( $a_obj->getId() != $this->getId() ) { //Make sure we don't delete the record we are currently editing.
						$a_obj->Delete( TRUE );
					}
				}
			}
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		//Calculate balance
		if ( $this->getEnableCalcBalance() == TRUE ) {
			Debug::text('Calculating Balance is enabled! ', __FILE__, __LINE__, __METHOD__, 10);

			//If the user and/or the accrual policy account was changed, recalculate the old and new values.
			$data_diff = $this->getDataDifferences();
			if ( isset($data_diff['user_id']) OR isset($data_diff['accrual_policy_account_id']) ) {
				AccrualBalanceFactory::calcBalance( ( ( isset($data_diff['user_id']) ) ? $data_diff['user_id'] : $this->getUser() ), ( ( isset($data_diff['accrual_policy_account_id']) ) ? $data_diff['accrual_policy_account_id'] : $this->getAccrualPolicyAccount() ) );
			}

			AccrualBalanceFactory::calcBalance( $this->getUser(), $this->getAccrualPolicyAccount() );
		}

		return TRUE;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $date_stamp EPOCH
	 * @return bool
	 */
	static function deleteOrphans( $user_id, $date_stamp ) {
		Debug::text('Attempting to delete Orphaned Records for User ID: '. $user_id .' Date: '. TTDate::getDate('DATE', $date_stamp), __FILE__, __LINE__, __METHOD__, 10);
		//Remove orphaned entries
		$alf = TTnew( 'AccrualListFactory' ); /** @var AccrualListFactory $alf */
		$alf->getOrphansByUserIdAndDate( $user_id, $date_stamp );
		Debug::text('Found Orphaned Records: '. $alf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $alf->getRecordCount() > 0 ) {
			$accrual_policy_ids = array();
			foreach( $alf as $a_obj ) {
				Debug::text('Orphan Record ID: '. $a_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
				$accrual_policy_ids[] = $a_obj->getAccrualPolicyAccount();
				$a_obj->Delete( TRUE );
			}

			//ReCalc balances
			if ( empty($accrual_policy_ids) === FALSE ) {
				foreach($accrual_policy_ids as $accrual_policy_id) {
					AccrualBalanceFactory::calcBalance( $user_id, $accrual_policy_id );
				}
			}
		}

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
						case 'user_date_total_id': //Skip this, as it should never be set from the API.
							break;
						case 'time_stamp':
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
	 * @param bool $permission_children_ids
	 * @return array
	 */
	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE  ) {
		$data = array();
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'accrual_policy_account':
						case 'accrual_policy':
						case 'first_name':
						case 'last_name':
						case 'title':
						case 'user_group':
						case 'default_branch':
						case 'default_department':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'accrual_policy_type':
							$data[$variable] = Option::getByKey( $this->getColumn( 'accrual_policy_type_id' ), $this->getOptions( $variable ) );
							break;
						case 'type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'time_stamp':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
							}
							break;
						case 'date_stamp': //This is a combination of the time_stamp and user_date.date_stamp columns.
							$data[$variable] = TTDate::getAPIDate( 'DATE', strtotime( $this->getColumn( $variable ) ) );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getPermissionColumns( $data, $this->getUser(), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		//Debug::Arr($data, 'Data Object: ', __FILE__, __LINE__, __METHOD__, 10);

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		$u_obj = $this->getUserObject();
		if ( is_object($u_obj) ) {
			return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Accrual') .' - '. TTi18n::getText('Employee').': '. $u_obj->getFullName( FALSE, TRUE ) .' '. TTi18n::getText('Type') .': '. Option::getByKey( $this->getType(), $this->getOptions('type') ) .' '. TTi18n::getText('Date') .': '.	TTDate::getDate('DATE', $this->getTimeStamp() ) .' '. TTi18n::getText('Total Time') .': '. TTDate::getTimeUnit( $this->getAmount() ), NULL, $this->getTable(), $this );
		}

		return FALSE;
	}

}
?>
