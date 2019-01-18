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
 * @package Modules\Payroll Agency
 */
class RemittanceDestinationAccountFactory extends Factory {
	protected $table = 'remittance_destination_account';
	protected $pk_sequence_name = 'remittance_destination_account_id_seq'; //PK Sequence name

	protected $user_obj = NULL;
	protected $remittance_source_account_obj = NULL;

	/**
	 * @param bool $name
	 * @param null $params
	 * @return array|bool|null
	 */
	function _getFactoryOptions( $name = FALSE, $params = NULL ) {

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
					10 => TTi18n::gettext('Enabled'),
					20 => TTi18n::gettext('Disabled')
				);
				break;
			case 'ach_transaction_type': //ACH transactions require a transaction code that matches the bank account.
				$retval = array(
								22 => TTi18n::getText('Checking'),
								32 => TTi18n::getText('Savings'),
								);
				break;
			case 'type':
				if ( !isset($params['legal_entity_id']) ) {
					return FALSE;
				}
				$rsalf = TTnew( 'RemittanceSourceAccountListFactory' );
				$rsalf->getByLegalEntityId( $params['legal_entity_id'] );
				$type_options = $rsalf->getOptions('type');

				foreach( $rsalf as $obj ) {
					$retval[$obj->getType()] = $type_options[$obj->getType()];
				}

				if ( $retval == FALSE OR count($retval) == 0 ){
					$retval = array( 0 => '-- None --' );
				}
				ksort( $retval );
				Debug::Arr($retval, 'Available account types for Legal Entity: '. $params['legal_entity_id'], __FILE__, __LINE__, __METHOD__, 10);
				break;
			case 'amount_type':
				$retval = array(
						10 => TTi18n::gettext('Percent'),
						20 => TTi18n::gettext('Fixed Amount'),
				);
				break;
			case 'priority':
				$retval = array(
						1 => '1 ('. TTi18n::gettext('First') .')',
						2 => '2',
						3 => '3',
						4 => '4',
						5 => '5',
						6 => '6',
						7 => '7',
						8 => '8',
						9 => '9',
						10 => '10 ('. TTi18n::gettext('Last') .')',
				);
				break;
			case 'columns':
				$retval = array(
					'-1010-status' => TTi18n::gettext('Status'),
					'-1020-type' => TTi18n::gettext('Type'),
					'-1010-user_first_name' => TTi18n::gettext('First Name'),
					'-1020-user_last_name' => TTi18n::gettext('Last Name'),

					'-1020-amount_type' => TTi18n::gettext('Amount Type'),
					'-1021-name' => TTi18n::gettext('Name'),
					'-1030-priority' => TTi18n::gettext('Priority'),
					'-1140-display_amount' => TTi18n::gettext('Amount'),
					'-1140-amount' => TTi18n::gettext('Payment Amount'),

					//added to allow importing these columns
					'-1150-percent_amount' => TTi18n::gettext('Payment Percent Amount'),
					'-1160-remittance_source_account' => TTi18n::gettext('Remittance Source Account'),

					'-1500-value1' => TTi18n::gettext('Institution'),
					'-1510-value2' => TTi18n::gettext('Transit/Routing'),
					'-1520-value3' => TTi18n::gettext('Account'),

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
					'user_first_name',
					'user_last_name',
					'status',
					'type',
					'name',
					'description',
					'priority',
					'display_amount',
				);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
					'name',
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
			'remittance_source_account_id' => 'RemittanceSourceAccount',
			'remittance_source_account' => FALSE,
			'legal_entity_id' => FALSE,
			'user_id' => 'User',
			'user' => FALSE,
			'user_first_name' => FALSE,
			'user_last_name' => FALSE,
			'status_id' => 'Status',
			'status' => FALSE,
			'type_id' => 'Type',
			'type'	=> FALSE,
			'name' => 'Name',
			'description' => 'Description',
			'currency_id' => 'Currency',
			'currency' => FALSE,
			'priority' => 'Priority',
			'amount_type_id' => 'AmountType',
			'amount_type' => FALSE,
			'amount' => 'Amount',
			'percent_amount' => 'PercentAmount',
			'display_amount' => FALSE, //must come after amount and percent_amount
			'value1' => 'Value1',
			'value2' => 'Value2',
			'value3' => 'Value3', //encrypted account
			'value4' => 'Value4',
			'value5' => 'Value5',
			'value6' => 'Value6',
			'value7' => 'Value7',
			'value8' => 'Value8',
			'value9' => 'Value9',
			'value10' => 'Value10',

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
	 * @return bool
	 */
	function getRemittanceSourceAccountObject() {
		return $this->getGenericObject( 'RemittanceSourceAccountListFactory', $this->getRemittanceSourceAccount(), 'remittance_source_account_obj' );
	}

	/**
	 * @return bool|mixed
	 */
	function getRemittanceSourceAccount() {
		return $this->getGenericDataValue( 'remittance_source_account_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setRemittanceSourceAccount( $value ) {
		$value = TTUUID::castUUID($value);
		if ( $value == '' ) {
			$value = TTUUID::getZeroID();
		}
		return $this->setGenericDataValue( 'remittance_source_account_id', $value );
	}

	/**
	 * @return mixed
	 */
	function getUser() {
		return $this->getGenericDataValue( 'user_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setUser( $value ) {
		$value = trim($value);
		$value = TTUUID::castUUID($value);
		if ( $value == '' ) {
			$value = TTUUID::getZeroID();
		}
		return $this->setGenericDataValue( 'user_id', $value );
	}

	/**
	 * @return bool
	 */
	function getLegalEntity() {
		if ( is_object( $this->getRemittanceSourceAccountObject() ) ) {
			return $this->getRemittanceSourceAccountObject()->getLegalEntity();
		}

		return FALSE;
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
		$value = TTUUID::castUUID($value);
		if ( $value == '' ) {
			$value = TTUUID::getZeroID();
		}
		Debug::Text('Currency ID: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		return $this->setGenericDataValue( 'currency_id', $value );
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
		$value = (int)$value;
		return $this->setGenericDataValue( 'status_id', $value );
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
		$value = (int)$value;
		return $this->setGenericDataValue( 'type_id', $value );
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name) {
		$name = trim($name);

		$company_id = $this->getUserObject()->getCompany();

		if ( $name == '' ) {
			return FALSE;
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		$ph = array(
			'name' => $name,
			'user_id' => TTUUID::castUUID($this->getUser()),
			'company_id' => TTUUID::castUUID($company_id),
		);

		$uf = TTnew( 'UserFactory' );

		$query = 'SELECT a.id
					FROM '. $this->getTable() .' as a
					LEFT JOIN ' . $uf->getTable() .' as uf ON ( a.user_id = uf.id AND uf.deleted = 0 )
					WHERE lower(a.name) = lower(?)
						AND a.user_id = ?
						AND uf.company_id = ?
						AND a.deleted = 0';

		$name_id = $this->db->GetOne($query, $ph);
		Debug::Arr($name_id, 'Unique Name: '. $name, __FILE__, __LINE__, __METHOD__, 10);

		if ( $name_id === FALSE ) {
			return TRUE;
		} else {
			if ($name_id == $this->getId() ) {
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
	function setName( $value ) {
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
	function setDescription( $value ) {
		$value = trim($value);
		return $this->setGenericDataValue( 'description', $value );
	}

	/**
	 * @return bool|int
	 */
	function getAmountType() {
		return $this->getGenericDataValue( 'amount_type_id' );
	}

	/**
	 * @param int $value
	 * @return bool
	 */
	function setAmountType( $value ) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'amount_type_id', $value );
	}

	/**
	 * @return bool|string
	 */
	function getAmount() {
		return Misc::removeTrailingZeros( $this->getGenericDataValue( 'amount' ), 2);
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAmount( $value ) {
		$value = trim($value);
		if	( empty($value) ) {
			$value = 0;
		}
		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat($value);
		return $this->setGenericDataValue( 'amount', $value );
	}

	/**
	 * @return string
	 */
	function getDisplayPercentAmount() {
		return Misc::removeTrailingZeros( $this->getPercentAmount(), 0 ) .'%';
	}

	/**
	 * @return null
	 */
	function getPercentAmount() {
		return $this->getGenericDataValue( 'percent_amount' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPercentAmount( $value) {
		$value = (float)$value;
		//$this->data['amount'] = number_format( $value, 2, '.', '');
		return $this->setGenericDataValue( 'percent_amount', round( $value, 2) );
	}

	/**
	 * @return bool|mixed
	 */
	function getPriority() {
		return $this->getGenericDataValue( 'priority' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPriority( $value) {
		$value = trim($value);
		//Pull out only digits and periods.
		$value = $this->Validator->stripNonNumeric($value);
		return $this->setGenericDataValue( 'priority', $value );
	}

	/**
	 * @return bool
	 */
	function getValue1() {
		return $this->getGenericDataValue( 'value1' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue1( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value1', $value );
	}

	/**
	 * @return bool
	 */
	function getValue2() {
		return $this->getGenericDataValue( 'value2' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue2( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value2', $value );
	}

	/**
	 * VALUE 3 is the account number. It must be stored encrypted.
	 * @return bool|string
	 */
	function getSecureValue3( $account = NULL ) {
		if ( $account == NULL ) {
			$account = $this->getValue3();
		}

		return Misc::censorString( $account, 'X', 1, 2, 1, 4 );
	}

	/**
	 * VALUE 3 is the account number. It must be stored encrypted. Use getSecureAccountNumber()
	 * @return bool
	 */
	function getValue3() {
		$value = $this->getGenericDataValue( 'value3' );
		//We must check is_numeric to ensure that the value properly decrypted.

		$value = Misc::decrypt($value);
		if ( isset($value) AND is_numeric( $value ) == FALSE ) {
			Debug::Text( 'DECRYPTION FAILED: Your salt may have changed.', __FILE__, __LINE__, __METHOD__, 10 );
		} else {
			return $value;
		}

		return FALSE;
	}

	/**
	 * VALUE 3 is the account number. It must be stored encrypted.
	 * @param $value
	 * @return bool
	 */
	function setValue3($value) {
		//If X's are in the account number, skip setting it
		// Also if a colon is in the account number, its likely an encrypted string, also skip.
		//This allows them to change other data without seeing the account number.
		if ( stripos( $value, 'X') !== FALSE OR stripos( $value, ':') !== FALSE ) {
			return FALSE;
		}
		$value = trim($value);
		$encrypted_value = Misc::encrypt($value);
		if ( $encrypted_value === FALSE ) {
			return FALSE;
		}
		return $this->setGenericDataValue( 'value3', $encrypted_value );
	}

	/**
	 * @return bool
	 */
	function getValue4() {
		return $this->getGenericDataValue( 'value4' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue4( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value4', $value );
	}

	/**
	 * @return bool
	 */
	function getValue5() {
		return $this->getGenericDataValue( 'value5' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue5( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value5', $value );
	}


	/**
	 * @return bool
	 */
	function getValue6() {
		return $this->getGenericDataValue( 'value6' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue6( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value6', $value );
	}

	/**
	 * @return bool
	 */
	function getValue7() {
		return $this->getGenericDataValue( 'value7' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue7( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value7', $value );
	}


	/**
	 * @return bool
	 */
	function getValue8() {
		return $this->getGenericDataValue( 'value8' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue8( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value8', $value );
	}

	/**
	 * @return bool
	 */
	function getValue9() {
		return $this->getGenericDataValue( 'value9' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue9( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value9', $value );
	}

	/**
	 * @return bool
	 */
	function getValue10() {
		return $this->getGenericDataValue( 'value10' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue10( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value10', $value );
	}

	/**
	 * @return bool
	 */
	function getEnableBlankRemittanceSourceAccount() {
		if ( isset($this->enable_blank_remittance_source_account) ) {
			return $this->enable_blank_remittance_source_account;
		}

		return FALSE;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableBlankRemittanceSourceAccount( $bool) {
		$this->enable_blank_remittance_source_account = $bool;
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
		// Remittance source account
		if ( $this->getRemittanceSourceAccount() !== FALSE AND $this->getRemittanceSourceAccount() != TTUUID::getZeroID() AND !$this->getEnableBlankRemittanceSourceAccount() ) {
			$lf = TTnew( 'RemittanceSourceAccountListFactory' );
			$this->Validator->isResultSetWithRows(	'remittance_source_account_id',
															$lf->getByID($this->getRemittanceSourceAccount()),
															TTi18n::gettext('Remittance source account is invalid')
														);
		}
		// User
		if ( $this->Validator->getValidateOnly() == FALSE ) {
			if ( $this->getUser() == FALSE ) {
				$this->Validator->isTrue(		'user_id',
												FALSE,
												TTi18n::gettext('Please specify employee')
											);
			}
		}

		if ( ( $this->getUser() != FALSE AND $this->Validator->isError( 'user_id' ) == FALSE ) ) {

			$ulf = TTnew( 'UserListFactory' );
			$this->Validator->isResultSetWithRows( 'user_id',
												   $ulf->getByID( $this->getUser() ),
												   TTi18n::gettext( 'Invalid Employee' )
			);
		}

		// Currency
		if ( $this->getCurrency() !== FALSE AND $this->getCurrency() != TTUUID::getZeroID() ) {
			$culf = TTnew( 'CurrencyListFactory' );
			$this->Validator->isResultSetWithRows( 'currency_id',
												   $culf->getByID( $this->getCurrency() ),
												   TTi18n::gettext( 'Invalid Currency' )
			);
		}
		// Status
		if ( $this->Validator->getValidateOnly() == FALSE ) {
			if ( $this->getStatus() == FALSE ) {
				$this->Validator->isTrue( 'status_id',
										  FALSE,
										  TTi18n::gettext( 'Please specify status' ) );
			}
		}
		if ( $this->getStatus() !== FALSE AND $this->Validator->isError( 'status_id' ) == FALSE ) {
			$this->Validator->inArrayKey( 'status',
										  $this->getStatus(),
										  TTi18n::gettext( 'Incorrect Status' ),
										  $this->getOptions( 'status' )
			);
		}
		// Type
		if ( $this->Validator->getValidateOnly() == FALSE ) { //Don't check the below when mass editing.
			if ( $this->getType() == FALSE ) {
				$this->Validator->isTrue( 'type_id',
										  FALSE,
										  TTi18n::gettext( 'Please specify type' )
				);
			}
		}
		if ( $this->getType() !== FALSE AND $this->Validator->isError( 'type_id' ) == FALSE AND $this->getRemittanceSourceAccount() != TTUUID::getZeroID() ) {
			$this->Validator->inArrayKey( 'type_id',
										  $this->getType(),
										  TTi18n::gettext( 'Incorrect Type' ),
										  $this->getOptions( 'type', array('legal_entity_id' => $this->getLegalEntity()) )
			);
		}
		// Name
		if ( $this->Validator->getValidateOnly() == FALSE ) {
			if ( $this->getName() == '' ) {
				$this->Validator->isTRUE( 'name',
										  FALSE,
										  TTi18n::gettext( 'Please specify a name' ) );
			}
		}
		if ( $this->getName() != '' AND $this->Validator->isError( 'name' ) == FALSE ) {
			$this->Validator->isLength( 'name',
										$this->getName(),
										TTi18n::gettext( 'Name is too short or too long' ),
										2, 100
			);
		}
		if ( $this->getName() != '' AND $this->Validator->isError( 'name' ) == FALSE AND $this->getUser() != TTUUID::getZeroID() ) {
			$this->Validator->isTrue( 'name',
									  $this->isUniqueName( $this->getName() ),
									  TTi18n::gettext( 'Name already exists' )
			);
		}
		// Description
		$this->Validator->isLength( 'description',
									$this->getDescription(),
									TTi18n::gettext( 'Description is invalid' ),
									0, 255
		);
		// Amount type
		if ( $this->Validator->getValidateOnly() == FALSE ) {
			if ( $this->getAmountType() == FALSE ) {
				$this->Validator->isTrue( 'amount_type_id',
										  FALSE,
										  TTi18n::gettext( 'Please specify amount type' )
				);
			}
		}
		if ( $this->getAmountType() !== FALSE AND $this->Validator->isError( 'amount_type_id' ) == FALSE ) {
			$this->Validator->inArrayKey( 'amount_type_id',
										  $this->getAmountType(),
										  TTi18n::gettext( 'Incorrect amount type' ),
										  $this->getOptions( 'amount_type' )
			);
		}
		// Amount
		if ( $this->getAmount() != '' ) {
			$this->Validator->isNumeric( 'amount',
										 $this->getAmount(),
										 TTi18n::gettext( 'Incorrect Amount' )
			);
		}

		// Percent
		if ( $this->getAmountType() == 10 AND $this->getPercentAmount() != '' ) {
			$this->Validator->isFloat( 'percent_amount',
									   $this->getPercentAmount(),
									   TTi18n::gettext( 'Invalid Percent' )
			);
			if ( $this->Validator->isError( 'percent_amount' ) == FALSE ) {
				$this->Validator->isLessThan( 'percent_amount',
											  $this->getPercentAmount(),
											  TTi18n::gettext( 'Percent must be less than 100%' ),
											  100
				);
			}
			if ( $this->Validator->isError( 'percent_amount' ) == FALSE ) {
				$this->Validator->isGreaterThan( 'percent_amount',
												 $this->getPercentAmount(),
												 TTi18n::gettext( 'Percent must be more than 1%' ),
												 1
				);
			}
		}

		// Priority
		if ( $this->Validator->getValidateOnly() == FALSE ) {
			if ( $this->getPriority() == FALSE ) {
				$this->Validator->isTrue( 'priority',
										  FALSE,
										  TTi18n::gettext( 'Please specify priority' )
				);
			}
		}
		if ( $this->getPriority() !== FALSE AND $this->Validator->isError( 'priority' ) == FALSE ) {
			$this->Validator->isNumeric( 'priority',
										 $this->getPriority(),
										 TTi18n::gettext( 'Priority is invalid' )
			);
		}
		// Value 4
		if ( $this->getValue4() != '' ) {
			$this->Validator->isLength( 'value4',
										$this->getValue4(),
										TTi18n::gettext( 'Value 4 is invalid' ),
										1, 255
			);
		}
		// Value 5
		if ( $this->getValue5() != '' ) {
			$this->Validator->isLength( 'value5',
										$this->getValue5(),
										TTi18n::gettext( 'Value 5 is invalid' ),
										1, 255
			);
		}
		// Value 6
		if ( $this->getValue6() != '' ) {
			$this->Validator->isLength( 'value6',
										$this->getValue6(),
										TTi18n::gettext( 'Value 6 is invalid' ),
										1, 255
			);
		}
		// Value 7
		if ( $this->getValue7() != '' ) {
			$this->Validator->isLength( 'value7',
										$this->getValue7(),
										TTi18n::gettext( 'Value 7 is invalid' ),
										1, 255
			);
		}
		// Value 8
		if ( $this->getValue8() != '' ) {
			$this->Validator->isLength( 'value8',
										$this->getValue8(),
										TTi18n::gettext( 'Value 8 is invalid' ),
										1, 255
			);
		}
		// Value 9
		if ( $this->getValue9() != '' ) {
			$this->Validator->isLength( 'value9',
										$this->getValue9(),
										TTi18n::gettext( 'Value 9 is invalid' ),
										1, 255
			);
		}
		// Value 10
		if ( $this->getValue10() != '' ) {
			$this->Validator->isLength( 'value10',
										$this->getValue10(),
										TTi18n::gettext( 'Value 10 is invalid' ),
										1, 255
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		$country = is_object( $this->getRemittanceSourceAccountObject() ) ? $this->getRemittanceSourceAccountObject()->getCountry() : FALSE;
		if ( $this->getAmountType() == 20 ) {
			if ( (int)$this->getAmount() == 0 ) {
				$this->Validator->isTrue( 'amount',
										  FALSE,
										  TTi18n::gettext( 'Amount is 0 or not specified' )
				);
			}
		} else {
			if ( $this->getAmountType() == 10 ) {
				if ( $this->getPercentAmount() == 0 OR $this->getPercentAmount() == 0.00 ) {
					$this->Validator->isTrue( 'percent_amount',
											  FALSE,
											  TTi18n::gettext( 'Percent is 0 or not specified' )
					);
				} else {
					if ( $this->getPercentAmount() < 0 OR (int)$this->getPercentAmount() > 100 ) {
						$this->Validator->isTrue( 'percent_amount',
												  FALSE,
												  TTi18n::gettext( 'Percent is less than 0 or more than 100' )
						);
					}
				}
			}
		}

		if ( $this->getType() == 2000 ) {
			/**
			 * Currently hiding these options from the UI because we aren't printing MICR codes yet so we don't want to validate them.
			 */

			//			if ( strlen( $this->getValue2() ) < 2 OR strlen( $this->getValue2() ) > 15 ) {
			//				$this->Validator->isTrue(		'value2',
			//												FALSE,
			//												TTi18n::gettext('Invalid routing number length'));
			//			} else {
			//				$this->Validator->isNumeric(	'value2',
			//												$this->getValue2(),
			//												TTi18n::gettext('Invalid routing number, must be digits only'));
			//			}
			//			if ( strlen( $this->getValue3() ) < 3 OR strlen( $this->getValue3() ) > 20 ) {
			//				$this->Validator->isTrue(		'value3',
			//												FALSE,
			//												TTi18n::gettext('Invalid account number length'));
			//			} else {
			//				$this->Validator->isNumeric(	'value3',
			//												$this->getValue3(),
			//												TTi18n::gettext('Invalid account number, must be digits only'));
			//			}
		} else {
			if ( $this->getType() == 3000 AND $country == 'US' AND is_object($this->getRemittanceSourceAccountObject()) ) {
				// value2
				if ( strlen( $this->getValue2() ) < 2 OR strlen( $this->getValue2() ) > 15 ) {
					$this->Validator->isTrue( 'value2',
											  FALSE,
											  TTi18n::gettext( 'Invalid routing number length' ) );
				} else {
					$this->Validator->isNumeric( 'value2',
												 $this->getValue2(),
												 TTi18n::gettext( 'Invalid routing number, must be digits only' ) );
				}

				if ( strlen( $this->getValue3() ) < 3 OR strlen( $this->getValue3() ) > 20 ) {
					$this->Validator->isTrue( 'value3',
											  FALSE,
											  TTi18n::gettext( 'Invalid account number length' ) );
				} else {
					$this->Validator->isNumeric( 'value3',
												 $this->getValue3(),
												 TTi18n::gettext( 'Invalid account number, must be digits only' ) );
				}
			} elseif ( $this->getType() == 3000 AND $country == 'CA' AND is_object($this->getRemittanceSourceAccountObject()) ) {
				if ( strlen( $this->getValue1() ) < 2 OR strlen( $this->getValue1() ) > 3 ) {
					$this->Validator->isTrue( 'value1',
											  FALSE,
											  TTi18n::gettext( 'Invalid institution number length' ) );
				}
				if ( strlen( $this->getValue2() ) < 2 OR strlen( $this->getValue2() ) > 15 ) {
					$this->Validator->isTrue( 'value2',
											  FALSE,
											  TTi18n::gettext( 'Invalid transit number length' ) );
				} else {
					$this->Validator->isNumeric( 'value2',
												 $this->getValue2(),
												 TTi18n::gettext( 'Invalid transit number, must be digits only' ) );
				}
				if ( strlen( $this->getValue3() ) < 3 OR strlen( $this->getValue3() ) > 20 ) {
					$this->Validator->isTrue( 'value3',
											  FALSE,
											  TTi18n::gettext( 'Invalid account number length' ) );
				} else {
					$this->Validator->isNumeric( 'value3',
												 $this->getValue3(),
												 TTi18n::gettext( 'Invalid account number, must be digits only' ) );
				}
			}
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		$this->setCurrency( TTUUID::getZeroID() );
		if ( $this->getRemittanceSourceAccount() == TTUUID::getZeroID() ) {
			Debug::Text('WARNING: Remittance Source Account is blank, disabling destination account...', __FILE__, __LINE__, __METHOD__, 10);
			$this->setStatus(20);
		} else {
			//FIXME: Remove this if we enable the currency field in the UI.
			//This was done because we don't need to be able to edit the destination account currency via the UI at this time, and destinations currencies will match source currencies now.
			$this->setCurrency( $this->getRemittanceSourceAccountObject()->getCurrency() );
		}

		if ( $this->getDeleted() == TRUE ) {
			$pstlf = TTnew( 'PayStubTransactionListFactory' );
			$pstlf->getByRemittanceSourceAccountId($this->getId());
			if ( $pstlf->getRecordCount() > 0 ) {
				Debug::Text('Pay Stub Transactions exist for Remittance Source Account ID: '. $this->getID(). ' disabled instead of deleted', __FILE__, __LINE__, __METHOD__, 10);
				$this->setDeleted( FALSE );
				$this->setStatus( 20 );
			}
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
	 * @return mixed
	 */
	function getObjectAsArray( $include_columns = NULL ) {
		$data = array();
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'user_first_name':
						case 'user_last_name':
						case 'remittance_source_account':
						case 'currency_id':
						case 'currency':
						case 'legal_entity_id':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'type':
							$rsaf = TTnew( 'RemittanceSourceAccountFactory' );
							$type_options = $rsaf->getOptions('type');
							$data[$variable] = $type_options[$this->getType()];
							break;
						case 'amount_type':
						case 'status':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'percent_amount':
							$data[$variable] = $this->getDisplayPercentAmount();
							break;
						case 'display_amount':
							if ( $this->getAmountType() == 10 ) { //Show percent sign at end, so the user can tell the difference.
								$data[$variable] = $this->getDisplayPercentAmount();
							} else {
								$data[$variable] = $this->getAmount();
							}
							break;
						case 'value3': //account number
							$data[$variable] = $this->getSecureValue3();
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Remittance destination account') .': '. $this->getName(), NULL, $this->getTable(), $this );
	}

}
?>
