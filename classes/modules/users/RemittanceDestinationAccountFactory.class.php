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
					'-1140-display_amount' => TTi18n::gettext('Amount'), //Needs to be excluded from importing.

					//Added to allow importing these columns
					'-1140-amount' => TTi18n::gettext('Payment Amount'),
					'-1150-percent_amount' => TTi18n::gettext('Payment Percent Amount'),
					'-1160-remittance_source_account' => TTi18n::gettext('Remittance Source Account'),

					'-1500-value1' => TTi18n::gettext('Institution'),
					'-1510-value2' => TTi18n::gettext('Transit/Routing'),
					'-1520-value3' => TTi18n::gettext('Account'),
					'-1522-ach_transaction_type' => TTi18n::gettext('Account Type'),

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
			'ach_transaction_type' => FALSE, //Account Type (Checking/Savings)
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

			'in_use' => FALSE,
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
		$value = TTUUID::castUUID( $value );
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
		$value = TTUUID::castUUID( $value );
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
		$value = TTUUID::castUUID( $value );
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

		if ( is_object( $this->getUserObject() ) ) {
			$company_id = $this->getUserObject()->getCompany();
		} else {
			return FALSE;
		}

		if ( $name == '' ) {
			return FALSE;
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		$ph = array(
			'company_id' => TTUUID::castUUID($company_id),
			'user_id' => TTUUID::castUUID($this->getUser()),
			'name' => $name,
		);

		$uf = TTnew( 'UserFactory' );

		$query = 'SELECT a.id
					FROM '. $this->getTable() .' as a
					LEFT JOIN ' . $uf->getTable() .' as uf ON ( a.user_id = uf.id AND uf.deleted = 0 )
					WHERE uf.company_id = ?
						AND a.user_id = ?
						AND lower(a.name) = lower(?)
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
	function getValue3( $value = NULL ) {
		if ( $value == NULL ) {
			$value = $this->getGenericDataValue( 'value3' );
		}

		$value = Misc::decrypt($value);

		//We must check is_numeric to ensure that the value properly decrypted.
		if ( isset($value) AND is_numeric( $value ) == FALSE ) {
			Debug::Text( 'DECRYPTION FAILED: Your salt may have changed.', __FILE__, __LINE__, __METHOD__, 10 );
		} else {
			return $value;
		}

		return FALSE;
	}

	/**
	 * VALUE 3 is the account number. It needs to be encrypted.
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
		if ( $value != '' ) { //Make sure we can clear out the value if needed. Misc::encypt() will return FALSE on a blank value.
			$encrypted_value = Misc::encrypt( $value );
			if ( $encrypted_value === FALSE ) {
				return FALSE;
			}
		} else {
			$encrypted_value = $value;
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
	 * Migrates RemittanceDestinationAccount as best as it possibly can for an employee when switching legal entities.
	 * @param $user_obj object
	 * @param $data_diff array
	 */
	static function MigrateLegalEntity( $user_obj, $data_diff ) {
		//Get all RemittanceSourceAccounts assign to the new legal entity so we can quickly loop over them.
		$rsalf = TTnew('RemittanceSourceAccountListFactory');
		$rsalf->StartTransaction();
		$rsalf->getByCompanyId( $user_obj->getCompany() );


		$rdalf = TTnew('RemittanceDestinationAccountListFactory');
		$rdalf->getByUserIdAndCompany( $user_obj->getId(), $user_obj->getCompany() );
		if ( $rdalf->getRecordCount() > 0 ) {
			Debug::text('Legal Entity changed. Trying to match all RemittanceDestiationAccount data to new entity for user: '. $user_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);
			foreach( $rdalf as $rda_obj ) {
				if ( $rda_obj->getStatus() != 10 ) { //Skip disabled accounts.
					Debug::text('  Skipping due to disabled RDA: '. $rda_obj->getName() .'('. $rda_obj->getId() .')', __FILE__, __LINE__, __METHOD__, 10);
					continue;
				}

				if ( is_object( $rda_obj->getRemittanceSourceAccountObject() ) AND $rda_obj->getRemittanceSourceAccountObject()->getLegalEntity() == TTUUID::getNotExistID() ) {
					Debug::text('  Skipping due to source account assigned to ANY legal entity, no need to make a change: '. $rda_obj->getName() .'('. $rda_obj->getId() .')', __FILE__, __LINE__, __METHOD__, 10);
					continue;
				}

				$matched_remittance_source_account_id = array();

				if ( $rsalf->getRecordCount() > 0 ) {
					foreach( $rsalf as $rsa_obj ) {
						if ( ( $rsa_obj->getLegalEntity() == $user_obj->getLegalEntity() OR $rsa_obj->getLegalEntity() == TTUUID::getNotExistID() )
								AND $rda_obj->getType() == $rsa_obj->getType() ) {
							Debug::text('  Match Found! Remittance Source Account: '. $rsa_obj->getName() .'('. $rsa_obj->getId() .')', __FILE__, __LINE__, __METHOD__, 10);
							$matched_remittance_source_account_id[] = $rsa_obj->getId();
						} else {
							Debug::text('  NOT a Match... Remittance Source Account: '. $rsa_obj->getName() .'('. $rsa_obj->getId() .')', __FILE__, __LINE__, __METHOD__, 10);
						}
					}
				}

				if ( count( $matched_remittance_source_account_id ) == 1 ) {
					//Create new RDA record because if the old one is assigned to any transactions it will fail anyways.
					$tmp_rda_obj = clone $rda_obj;
					$tmp_rda_obj->setId( FALSE );
					$tmp_rda_obj->setName( Misc::generateCopyName( $tmp_rda_obj->getName() ) );
					$tmp_rda_obj->setRemittanceSourceAccount( $matched_remittance_source_account_id[0] );
					if ( $tmp_rda_obj->isValid() ) {
						$tmp_rda_obj->Save();
					}
					unset($tmp_rda_obj);
				} else {
					Debug::text('  No Match Found ('. count( $matched_remittance_source_account_id ) .')! Disabling RemittanceDestinationAccount: '. $rda_obj->getName() .'('. $rda_obj->getId() .')', __FILE__, __LINE__, __METHOD__, 10);
				}

				//Always disable the old RDA, as a new one is created above if necessary.
				$rda_obj->setStatus( 20 ); //Disabled
				if ( $rda_obj->isValid() ) {
					$rda_obj->Save();
				} else {
					Debug::text('  ERROR! Validation failed when reassigning RemittanceDestination records: '. $rda_obj->getName() .'('. $rda_obj->getId() .')', __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		}

		$rsalf->CommitTransaction();

		unset($rsalf, $rdalf, $rda_obj, $rsa_obj );

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

		$country = is_object( $this->getRemittanceSourceAccountObject() ) ? $this->getRemittanceSourceAccountObject()->getCountry() : FALSE;

		if ( $this->getDeleted() == FALSE ) {
			// Remittance source account
			if ( $this->getRemittanceSourceAccount() !== FALSE AND $this->getRemittanceSourceAccount() != TTUUID::getZeroID() AND !$this->getEnableBlankRemittanceSourceAccount() ) {
				$lf = TTnew( 'RemittanceSourceAccountListFactory' );
				$this->Validator->isResultSetWithRows( 'remittance_source_account_id',
													   $lf->getByID( $this->getRemittanceSourceAccount() ),
													   TTi18n::gettext( 'Remittance source account is invalid' )
				);
			}
			// User
			if ( $this->Validator->getValidateOnly() == FALSE ) {
				if ( $this->getUser() == FALSE ) {
					$this->Validator->isTrue( 'user_id',
											  FALSE,
											  TTi18n::gettext( 'Please specify employee' )
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

			//Make sure Source Account and Destination Account types match.
			if ( $this->getRemittanceSourceAccount() !== FALSE AND $this->getType() !== FALSE ) {
				if ( is_object( $this->getRemittanceSourceAccountObject() ) ) {
					if ( $this->getRemittanceSourceAccountObject()->getType() != $this->getType() ) {
						$this->Validator->isTrue( 'remittance_source_account_id',
												  FALSE,
												  TTi18n::gettext( 'Source Account is invalid, type mismatch' ) );

					}
				}
			}

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

			if ( $this->Validator->getValidateOnly() == FALSE ) { //Make sure we can mass edit type/source account, so validating these has to be delayed.
				if ( $this->getStatus() == 10 ) { //10=Enabled - Only validate when status is enabled, so records that are invalid but used in the past can always be disabled.
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
						if ( $this->getType() == 3000 AND $country == 'US' AND is_object( $this->getRemittanceSourceAccountObject() ) ) {
							if ( $this->getValue1() == FALSE ) {
								$this->Validator->isTrue( 'value1_2', //JS uses value1_2 to reference this field.
														  FALSE,
														  TTi18n::gettext( 'Account Type must be specified' ) );
							}

							if ( strlen( $this->getValue2() ) != 9 ) {
								$this->Validator->isTrue( 'value2',
														  FALSE,
														  TTi18n::gettext( 'Invalid routing number length' ) );
							} else {
								$this->Validator->isNumeric( 'value2',
															 $this->getValue2(),
															 TTi18n::gettext( 'Invalid routing number, must be digits only' ) );
							}

							if ( strlen( $this->getValue3() ) < 3 OR strlen( $this->getValue3() ) > 17 ) {
								$this->Validator->isTrue( 'value3',
														  FALSE,
														  TTi18n::gettext( 'Invalid account number length' ) );
							} else {
								$this->Validator->isNumeric( 'value3',
															 $this->getValue3(),
															 TTi18n::gettext( 'Invalid account number, must be digits only' ) );
							}
						} elseif ( $this->getType() == 3000 AND $country == 'CA' AND is_object( $this->getRemittanceSourceAccountObject() ) ) {
							if ( strlen( $this->getValue1() ) != 3 ) {
								$this->Validator->isTrue( 'value1',
														  FALSE,
														  TTi18n::gettext( 'Invalid institution number length' ) );
							}
							if ( strlen( $this->getValue2() ) != 5 ) {
								$this->Validator->isTrue( 'value2',
														  FALSE,
														  TTi18n::gettext( 'Invalid transit number length' ) );
							} else {
								$this->Validator->isNumeric( 'value2',
															 $this->getValue2(),
															 TTi18n::gettext( 'Invalid transit number, must be digits only' ) );
							}
							if ( strlen( $this->getValue3() ) < 3 OR strlen( $this->getValue3() ) > 12 ) {
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
				}
			}

			//Make sure the name does not contain the account number for security reasons.
			$this->Validator->isTrue( 'name',
					( ( stripos( $this->getName(), $this->getValue3() ) !== FALSE ) ? FALSE : TRUE ),
									  TTi18n::gettext( 'Account number must not be a part of the Name' ) );

			//Make sure the description does not contain the account number for security reasons.
			$this->Validator->isTrue( 'description',
					( ( stripos( $this->getDescription(), $this->getValue3() ) !== FALSE ) ? FALSE : TRUE ),
									  TTi18n::gettext( 'Account number must not be a part of the Description' ) );
		}

		$pstlf = TTnew( 'PayStubTransactionListFactory' );
		$pstlf->getByRemittanceDestinationAccountId($this->getId());
		if ( $pstlf->getRecordCount() > 0 ) {

			//Ensure that only account detail items trigger this validation
			$disallowed_edit_fields = array ( 'value2', 'value3', 'type_id', 'remittance_source_account_id' );
			if ( $country == 'CA' ) {
				$disallowed_edit_fields[] = 'value1'; //US must be able to change account type.
			}
			$changed_fields = array_keys( $this->getDataDifferences() );
			$edited_fields_valid = TRUE;
			$first_invalid_key = NULL;

			foreach( $changed_fields as $key ) {
				if ( in_array( $key, $disallowed_edit_fields ) ) {
					$first_invalid_key = $key;
					$edited_fields_valid = FALSE;
					break;
				}
			}

			//Don't allow editing payment methods and changing the type or bank account details if transactions exist for it
			$this->Validator->isTrue(		$first_invalid_key,
											 $edited_fields_valid,
											 TTi18n::gettext('Payment Method is currently in use by Transactions, may need to create a new Payment Method instead')
			);
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
			$pstlf->getByRemittanceDestinationAccountId($this->getId());
			if ( $pstlf->getRecordCount() > 0 ) {
				Debug::Text('Pay Stub Transactions exist for Remittance Destination Account ID: '. $this->getID(). ' disabled instead of deleted', __FILE__, __LINE__, __METHOD__, 10);
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
						case 'ach_transaction_type':
							$this->setValue1( $data[$key] );
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
	 * @return mixed
	 */
	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE ) {
		$data = array();
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'in_use':
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

							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $rsaf->getOptions( $variable ) );
							}
							break;
						case 'status':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'amount_type':
							$data[$variable] = Option::getByKey( $this->getAmountType(), $this->getOptions( $variable ) );
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
						case 'ach_transaction_type':
							if ( $this->getType() == 3000 AND $this->getValue3() != '' ) { //US ACH
								$data[$variable] = Option::getByKey( $this->getValue1(), $this->getOptions( $variable ) );
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

			$this->getPermissionColumns( $data, $this->getColumn( 'user_id' ), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Employee Payment Method') .': '. $this->getName(), NULL, $this->getTable(), $this );
	}

}
?>
