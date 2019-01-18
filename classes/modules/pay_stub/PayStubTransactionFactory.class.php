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
class PayStubTransactionFactory extends Factory {
	protected $table = 'pay_stub_transaction';
	protected $pk_sequence_name = 'pay_stub_transaction_id_seq'; //PK Sequence name

	protected $remittance_source_account_obj = NULL;
	protected $remittance_destination_account_obj = NULL;
	protected $pay_stub_obj = NULL;
	protected $currency_obj = NULL;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = NULL ) {

		$retval = NULL;
		switch( $name ) {
			case 'remittance_source_account_type_id':
				$pstf = TTnew('RemittanceSourceAccountFactory');
				$retval = $pstf->getOptions('type');
				break;
			case 'transaction_status_id':
			case 'status':
				$retval = array(
						10 => TTi18n::gettext('Pending'),
						20 => TTi18n::gettext('Paid'),
						100 => TTi18n::gettext('Stop Payment'), //Stop Payment and don't re-issue.
						200 => TTi18n::gettext('Stop Payment - ReIssue'), //Use this for checks and EFT to simplify things.
				);
				break;
			case 'transaction_type_id':
			case 'type':
				$retval = array(
						10 => TTi18n::gettext('Valid'), //was: Enabled
						20 => TTi18n::gettext('InValid'), //was: Disabled
				);
				break;
			case 'columns':
				$retval = array(
					'-1000-status' => TTi18n::gettext('Status'),
					'-1010-destination_user_first_name' => TTi18n::gettext('First Name'),
					'-1020-destination_user_last_name' => TTi18n::gettext('Last Name'),
					'-1030-remittance_source_account' => TTi18n::gettext('Source Account'),
					'-1040-remittance_destination_account' => TTi18n::gettext('Destination Account'),
					'-1050-currency' => TTi18n::gettext('Currency'),
					'-1060-remittance_source_account_type' => TTi18n::gettext('Source Account Type'),
					'-1070-amount' => TTi18n::gettext('Amount'),
					'-1075-currency_rate' => TTi18n::gettext('Currency Rate'),
					'-1080-transaction_date' => TTi18n::gettext('Transaction Date'),
					'-1090-confirmation_number' => TTi18n::gettext('Confirmation Number'),

					'-1200-pay_stub_start_date' => TTi18n::gettext('Pay Stub Start Date'),
					'-1205-pay_stub_end_date' => TTi18n::gettext('Pay Stub End Date'),
					'-1210-pay_stub_transaction_date' => TTi18n::gettext('Pay Stub Transaction Date'),
					'-1220-pay_stub_run_id' => TTi18n::gettext('Payroll Run'),

					'-1300-pay_period_start_date' => TTi18n::gettext('Pay Period Start Date'),
					'-1305-pay_period_end_date' => TTi18n::gettext('Pay Period End Date'),
					'-1310-pay_period_transaction_date' => TTi18n::gettext('Pay Period Transaction Date'),


					'-2000-created_by' => TTi18n::gettext('Created By'),
					'-2010-created_date' => TTi18n::gettext('Created Date'),
					'-2020-updated_by' => TTi18n::gettext('Updated By'),
					'-2030-updated_date' => TTi18n::gettext('Updated Date'),
				);
				if ( isset($parent['payroll_wizard']) ) {
					$retval['-1400-total_amount'] = TTi18n::gettext('Total Amount');
					$retval['-1410-total_transactions'] = TTi18n::gettext('Total Transactions');
				}
				break;

			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
					'status',
					'destination_user_first_name',
					'destination_user_last_name',
					'remittance_source_account',
					'remittance_destination_account',
					'amount',
					'transaction_date',
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
			'id' => 'Id',
			'parent_id' => 'Parent',
			'pay_stub_id' => 'PayStub',
			'type_id' => 'Type',
			'type' => FALSE,
			'status_id' => 'Status',
			'status' => FALSE,
			'transaction_date' => 'TransactionDate',
			'remittance_source_account_id' => 'RemittanceSourceAccount',
			'remittance_source_account' => FALSE,
			'remittance_source_account_type' => FALSE,
			'remittance_destination_account_id' => 'RemittanceDestinationAccount',
			'remittance_destination_account' => FALSE,
			'currency_id' => FALSE, //Always forced to pay stub currency in presave. Should never be set from UI.
			'currency' => FALSE,
			'currency_rate' => 'CurrencyRate',
			'amount' => 'Amount',
			'confirmation_number' => 'ConfirmationNumber',
			'note' => 'Note',

			'user_id' => FALSE,
			'destination_user_first_name' => FALSE,
			'destination_user_last_name' => FALSE,
			'pay_period_id' => FALSE,
			'pay_period_start_date' => FALSE,
			'pay_period_end_date' => FALSE,
			'pay_period_transaction_date' => FALSE,
			'pay_stub_run_id' => FALSE,
			'pay_stub_status_id' => FALSE,
			'pay_stub_start_date' => FALSE,
			'pay_stub_end_date' => FALSE,
			'pay_stub_transaction_date' => FALSE,
			'legal_entity_legal_name' => FALSE,
			'legal_entity_trade_name' => FALSE,

			'deleted' => 'Deleted',
		);
		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getPayStubObject() {
		return $this->getGenericObject( 'PayStubListFactory', $this->getPayStub(), 'pay_stub_obj' );
	}

	/**
	 * @return bool
	 */
	function getRemittanceSourceAccountObject() {
		return $this->getGenericObject( 'RemittanceSourceAccountListFactory', $this->getRemittanceSourceAccount(), 'remittance_source_account_obj' );
	}

	/**
	 * @return bool
	 */
	function getRemittanceDestinationAccountObject() {
		return $this->getGenericObject( 'RemittanceDestinationAccountListFactory', $this->getRemittanceDestinationAccount(), 'remittance_destination_account_obj' );
	}

	/**
	 * @return bool
	 */
	function getCurrencyObject() {
		return $this->getGenericObject( 'CurrencyListFactory', $this->getCurrency(), 'currency_obj' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setParent( $value) {
		$value = TTUUID::castUUID(trim($value));
		$this->setGenericDataValue( 'parent_id', $value );
		return TRUE;
	}

	/**
	 * @return bool|mixed
	 */
	function getParent() {
		return $this->getGenericDataValue( 'parent_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setType( $value) {
		$value = (int)$value;
		return $this->setGenericDataValue( 'type_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getType() {
		return $this->getGenericDataValue( 'type_id' );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getTransactionDate( $raw = FALSE ) {
		//Debug::Text('Transaction Date: '. $this->data['transaction_date'] .' - '. TTDate::getDate('DATE+TIME', $this->data['transaction_date']), __FILE__, __LINE__, __METHOD__, 10);
		$value = $this->getGenericDataValue( 'transaction_date' );
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
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setTransactionDate( $value) {
		$value = ( !is_int($value) ) ? trim($value) : $value; //Dont trim integer values, as it changes them to strings.

		if ( $value != '' ) {
			//Make sure all pay periods transact at noon.
			$value = TTDate::getTimeLockedDate( strtotime('12:00:00', $value), $value);
		}

		return $this->setGenericDataValue( 'transaction_date', TTDate::getDBTimeStamp($value, FALSE) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStatus( $value) {
		$value = (int)$value;
		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getStatus() {
		return $this->getGenericDataValue( 'status_id' );
	}

	/**
	 * @return bool|mixed
	 */
	function getPayStub() {
		return $this->getGenericDataValue( 'pay_stub_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPayStub( $value) {
		$value = trim($value);
		$value = TTUUID::castUUID( $value );
		if ( $value == '' ) {
			$value = TTUUID::getZeroID();
		}
		return $this->setGenericDataValue( 'pay_stub_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getRemittanceSourceAccountName() {
		return $this->getGenericDataValue( 'remittance_source_account' );
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setRemittanceSourceAccountName( $value) {
		return $this->setGenericDataValue( 'remittance_source_account', $value );
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
	function setRemittanceSourceAccount( $value) {
		$value = trim($value);
		$value = TTUUID::castUUID( $value );
		if ( $value == '' ) {
			$value = TTUUID::getZeroID();
		}
		return $this->setGenericDataValue( 'remittance_source_account_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getRemittanceDestinationAccount() {
		return $this->getGenericDataValue( 'remittance_destination_account_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setRemittanceDestinationAccount( $value) {
		$value = trim($value);
		$value = TTUUID::castUUID( $value );
		if ( $value == '' ) {
			$value = TTUUID::getZeroID();
		}
		return $this->setGenericDataValue( 'remittance_destination_account_id', $value );
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
	function setCurrency( $value) {
		$value = trim($value);
		$value = TTUUID::castUUID( $value );
		if ( $value == '' ) {
			$value = TTUUID::getZeroID();
		}
		Debug::Text('Currency ID: '. $value, __FILE__, __LINE__, __METHOD__, 10);

		$culf = TTnew( 'CurrencyListFactory' );
		$old_currency_id = $this->getCurrency();

		if ( $culf->getRecordCount() == 1
			AND ( $this->isNew() OR $old_currency_id != $value ) ) {
			$this->setCurrencyRate( $culf->getCurrent()->getReverseConversionRate() );
		}

		return $this->setGenericDataValue( 'currency_id', $value );
	}

	/**
	 * @return bool|string
	 */
	function getAmount() {
		$value = $this->getGenericDataValue( 'amount' );
		if ( $value !== FALSE ) {
			return Misc::removeTrailingZeros( $value, 2);
		}

		return FALSE;
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

		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat($value);
		return $this->setGenericDataValue( 'amount', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCurrencyRate() {
		return $this->getGenericDataValue( 'currency_rate' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCurrencyRate( $value ) {
		$value = trim($value);
		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat($value);
		if ( $value == 0 ) {
			$value = 1;
		}
		return $this->setGenericDataValue( 'currency_rate', $value );
	}

	/**
	 * @return mixed
	 */
	function getConfirmationNumber() {
		return $this->getGenericDataValue( 'confirmation_number' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setConfirmationNumber( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'confirmation_number', $value );
	}
	/**
	 * @return mixed
	 */
	function getNote() {
		return $this->getGenericDataValue( 'note' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setNote( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'note', $value );
	}

	/**
	 * @return mixed
	 */
	function getPayPeriodID() {
		return $this->getGenericDataValue( 'pay_period_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPayPeriodID( $value) {
		$value = trim($value);

		if	(	$value == '') {

			$this->setGenericDataValue( 'pay_period_id', $value );

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return mixed
	 */
	function getRemittanceSourceAccountType() {
		return $this->getGenericDataValue( 'remittance_source_account_type' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRemittanceSourceAccountType( $value) {
		$value = trim($value);

		if	(	$value == '') {

			$this->setGenericDataValue( 'remittance_source_account_type', $value );

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = TRUE ) {
		Debug::Text('Validating PayStubTransaction...', __FILE__, __LINE__, __METHOD__, 10);

		//
		// BELOW: Validation code moved from set*() functions.
		//

		// Pay Stub
		if ( $this->getPayStub() !== FALSE ) {
			$pslf = TTnew( 'PayStubListFactory' );
			$this->Validator->isResultSetWithRows(	'pay_stub_id',
													  $pslf->getByID($this->getPayStub()),
													  TTi18n::gettext('Invalid Pay Stub')
			);
		}
		// Remittance source account
		if ( $this->getRemittanceSourceAccount() !== FALSE ) {
			$lf = TTnew( 'RemittanceSourceAccountListFactory' );
			$this->Validator->isResultSetWithRows(	'remittance_source_account_id',
													  $lf->getByID($this->getRemittanceSourceAccount()),
													  TTi18n::gettext('Remittance source account is invalid')
			);
		}
		// Remittance destination account
		if ( $this->getRemittanceDestinationAccount() !== FALSE ) {
			$lf = TTnew( 'RemittanceDestinationAccountListFactory' );
			$this->Validator->isResultSetWithRows(	'remittance_destination_account_id',
													  $lf->getByID($this->getRemittanceDestinationAccount()),
													  TTi18n::gettext('Remittance destination account is invalid')
			);
			if ( $this->Validator->isError('remittance_destination_account_id') == FALSE ) {
				$rdalf = TTnew( 'RemittanceDestinationAccountListFactory' );
				$rdalf->getById( $this->getRemittanceDestinationAccount() );
				if ( $rdalf->getRecordCount() > 0 ) {
					$rsalf = TTnew( 'RemittanceSourceAccountListFactory' );
					$rsalf->getById( $rdalf->getCurrent()->getRemittanceSourceAccount() );
					if ( $rsalf->getRecordCount() > 0 ) {
						$this->setCurrency( $rsalf->getCurrent()->getCurrency() );
					}
				}
			}
		}

		// Currency
		if ( $this->getCurrency() !== FALSE ) {
			$culf = TTnew( 'CurrencyListFactory' );
			$this->Validator->isResultSetWithRows(	'currency_id',
													  $culf->getByID($this->getCurrency()),
													  TTi18n::gettext('Invalid Currency')
			);
		}

		// Type
		if ( $this->getType() !== FALSE ) {
			$this->Validator->inArrayKey( 'type_id',
										  $this->getType(),
										  TTi18n::gettext( 'Incorrect Type' ),
										  $this->getOptions( 'type' )
			);
		}

		// Status
		if ( $this->getStatus() !== FALSE ) {
			$this->Validator->inArrayKey(	'status_id',
													$this->getStatus(),
													TTi18n::gettext('Incorrect Status'),
													$this->getOptions('status')
												);
		}
		// Transaction date
		if ( $this->getTransactionDate() !== FALSE ) {
			$this->Validator->isDate(		'transaction_date',
											 $this->getTransactionDate(),
											 TTi18n::gettext('Incorrect transaction date')
			);
		}

		// Amount
		if ( $this->getAmount() !== FALSE ) {
			$this->Validator->isNumeric(		'amount',
				$this->getAmount(),
				TTi18n::gettext('Incorrect Amount')
			);
		}

		// Currency Rate
		if ( $this->getCurrencyRate() !== FALSE ) {
			$this->Validator->isFloat(	'currency_rate',
												$this->getCurrencyRate(),
												TTi18n::gettext('Incorrect Currency Rate')
											);
			// Confirmation number
			if ( $this->getConfirmationNumber() != '' ) {
				$this->Validator->isLength(		'confirmation_number',
														$this->getConfirmationNumber(),
														TTi18n::gettext('Confirmation number is too short or too long'),
														1,
														50
													);
			}
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		// Status
		if ( $this->getStatus() !== FALSE ) {
			$status_options = $this->getOptions('status');
			$validate_msg = TTi18n::gettext('Invalid Status');

			$old_status_id = $this->getGenericOldDataValue('status_id');
			switch ( $old_status_id ) {
				case 100: //Stop Payment
				case 200: //Stop Payment - ReIssue
					$valid_statuses = array( 100, 200 );
					$status_options = Misc::arrayIntersectByKey( $valid_statuses, $status_options );
					$validate_msg = TTi18n::gettext('Status can only be changed to another Stop Payment');
					break;
				case 20: //Paid
					$valid_statuses = array( 20, 100, 200 );
					$status_options = Misc::arrayIntersectByKey( $valid_statuses, $status_options );
					$validate_msg = TTi18n::gettext('Status can only be changed from Paid to Stop Payment');
					break;
				case 10: //Pending
					$valid_statuses = array( 10, 20 );
					$status_options = Misc::arrayIntersectByKey( $valid_statuses, $status_options );
					$validate_msg = TTi18n::gettext('Status can only be changed from Pending to Paid');
					break;
				default:
					break;
			}
			Debug::Text( '  Old Status ID: '. $old_status_id .' Status ID: '. $this->getStatus(), __FILE__, __LINE__, __METHOD__, 10 );
			$this->Validator->inArrayKey(	'status_id',
											 $this->getStatus(),
											 $validate_msg,
											 $status_options
			);
		}

		if ( $this->Validator->getValidateOnly() == FALSE ) {
			if ( $this->getTransactionDate() == FALSE ) {
				$this->Validator->isDate( 'transaction_date',
										  $this->getTransactionDate(),
										  TTi18n::gettext( 'Incorrect transaction date' ) );
			}

			// Presave is called after validate so we can't assume source account is set.
//			if ( $this->getRemittanceSourceAccount() == FALSE ) {
//				$this->Validator->isTrue( 'remittance_source_account_id',
//										  FALSE,
//										  TTi18n::gettext( 'Source account not specified' ) );
//			}

			if ( $this->getCurrency() == FALSE ) {
				$this->Validator->isTrue( 'currency_id',
										  FALSE,
										  TTi18n::gettext( 'Currency not specified' ) );
			}

			//Make sure the pay stub is OPEN.
			if ( is_object( $this->getPayStubObject() ) AND $this->getPayStubObject()->getStatus() > 25 ) {
				$this->Validator->isTrue( 'pay_stub',
										  FALSE,
										  TTi18n::gettext( 'Pay Stub must be OPEN to modify transactions' ) );

			}

		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getType() == '' ) {
			$this->setType( 10 ); //Valid
		}

		if ( $this->getStatus() == '' ) {
			$this->setStatus( 10 ); //Pending
		}

		if ( $this->getRemittanceSourceAccount() == FALSE AND is_object($this->getRemittanceDestinationAccountObject()) ) {
			$this->setRemittanceSourceAccount( $this->getRemittanceDestinationAccountObject()->getRemittanceSourceAccount() );
		}
		if ( $this->getCurrency() == FALSE AND is_object( $this->getPayStubObject()) ) {
			$this->setCurrency( $this->getPayStubObject()->getCurrency() );
		}
		if ( $this->getCurrencyRate() == FALSE AND is_object( $this->getPayStubObject()) ) {
			$this->setCurrencyRate( $this->getPayStubObject()->getCurrencyRate() );
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
	 * starts EFT file
	 *
	 * @param object $rs_obj
	 * @return EFT
	 */
	function startEFTFile($rs_obj) {
		$data_format_type_id = $rs_obj->getDataFormat();
		$data_format_types = array(
				10 => 'ACH',
				20 => '1464',
				30 => '1464', //CIBC
				50 => '105',
				70 => 'BEANSTREAM'
		);

		$eft = new EFT();
		$eft->setFileFormat( $data_format_types[$data_format_type_id] );
		$eft->setBusinessNumber( $rs_obj->getValue4() ); //ACH
		$eft->setOriginatorID( $rs_obj->getValue5() );
		$eft->setFileCreationNumber( $rs_obj->getNextTransactionNumber() );
		$eft->setInitialEntryNumber( ( ( $rs_obj->getValue9() != '' ) ? $rs_obj->getValue9() : substr( $rs_obj->getValue5(), 0, 8 ) ) ); //ACH
		$eft->setDataCenter( $rs_obj->getValue7() );
		$eft->setDataCenterName( $rs_obj->getValue8() ); //ACH

		$eft->setOtherData('originator_long_name', $rs_obj->getLegalEntityObject()->getTradeName() ); //Originator Long name based on legal entity name. It will be trimmed automatically in EFT class.
		if ( $rs_obj->getValue6() != '' ) {
			$eft->setOriginatorShortName( substr( $rs_obj->getValue6(), 0, 26 ) );
		} else {
			$eft->setOriginatorShortName( substr( $eft->getOtherData('originator_long_name'), 0, 26 ) ); //Base the short name off the long name if it isn't otherwise specified.
		}

		if ( is_object( $rs_obj->getCurrencyObject() ) ) {
			$eft->setCurrencyISOCode( $rs_obj->getCurrencyObject()->getISOCode() );
		}

		$eft->setOtherData('sub_file_format', $data_format_type_id );

		//So far only used for CIBC file format
		$eft->setOtherData('settlement_institution', $rs_obj->getValue26() );
		$eft->setOtherData('settlement_transit', $rs_obj->getValue27() );
		$eft->setOtherData('settlement_account', $rs_obj->getValue28() );

		//File header line, some RBC services require a "routing" line at the top of the file.
		if ( trim( $rs_obj->getValue29() ) != '' ) {
			$eft->setFilePrefixData( $rs_obj->getValue29() );
		}

		return $eft;
	}

	/**
	 * Completes the eft file.
	 *
	 * @param $eft
	 * @param object $rs_obj
	 * @param object $uf_obj
	 * @param object $ps_obj
	 * @param $current_company
	 * @param $total_credit_amount
	 * @param $next_transaction_number
	 * @param $output
	 * @return mixed
	 */
	function endEFTFile( $eft, $rs_obj, $uf_obj, $ps_obj, $current_company, $total_credit_amount, $next_transaction_number, $output ) {
		$is_balanced = $rs_obj->getValue24();
		if ( $total_credit_amount > 0 AND (bool)$is_balanced == TRUE ) {
			Debug::Text( '  Balancing ACH... ', __FILE__, __LINE__, __METHOD__, 10 );
			$record = new EFT_Record();
			$record->setType( 'D' );
			$record->setCPACode( 200 );
			$record->setAmount( $total_credit_amount );

			$record->setDueDate( TTDate::getBeginDayEpoch( $ps_obj->getTransactionDate() ) );

			if ( $rs_obj->getValue28() != '' ) { //If specific OFFSET bank account is specified, use it here. Otherwise default to the source account.
				$record->setInstitution( $rs_obj->getValue26() );
				$record->setTransit( $rs_obj->getValue27() );
				$record->setAccount( $rs_obj->getValue28() );

			} else {
				$record->setInstitution( $rs_obj->getValue1() );
				$record->setTransit( $rs_obj->getValue2() );
				$record->setAccount( $rs_obj->getValue3() );
			}

			$record->setName( substr( $eft->getOtherData('originator_long_name'), 0, 30 ) );

			$record->setOriginatorShortName( $eft->getOriginatorShortName() );
			$record->setOriginatorLongName( $eft->getOtherData('originator_long_name') );

			$offset = $rs_obj->getValue25();
			if ( strlen( trim( $offset ) ) === 0 ) {
				$offset = 'OFFSET';
			}
			$record->setOriginatorReferenceNumber( $offset );

			//Don't need return accounts for ACH transactions.
			$eft->setRecord( $record );
		} else {
			Debug::Text( '  NOT Balancing ACH... ', __FILE__, __LINE__, __METHOD__, 10 );
		}
		unset( $is_balanced );

		//File trailer line.
		if ( trim( $rs_obj->getValue30() ) != '' ) { //Make sure we don't put blank lines at the end of file, as that can break some systems like TelPay.
			$eft->setFilePostfixData( $rs_obj->getValue30() );
		}

		$eft->compile();
		$file_name = $this->formatFileName( $rs_obj, $next_transaction_number, 'EFT', 'txt' );

		Debug::Text( 'EFT File name : ' . $file_name, __FILE__, __LINE__, __METHOD__, 10 );
		$output[$rs_obj->getId()] = array('file_name' => $file_name, 'mime_type' => 'Application/Text', 'data' => $eft->getCompiledData());

		//rs_obj cleared on save unless passed false
		$rs_obj->setLastTransactionNumber( $next_transaction_number );
		if ( $rs_obj->isValid() ) {
			$rs_obj->Save( FALSE );
		}
		unset( $eft );

		return $output;
	}

	/**
	 * @param object $rs_obj
	 * @param $transaction_number
	 * @param $prefix
	 * @param $extension
	 * @return string
	 */
	function formatFileName( $rs_obj, $transaction_number, $prefix, $extension ) {
		$file_name = $prefix . '_' . substr( $this->Validator->stripNonAlphaNumeric( $rs_obj->getName() ), 0, 20 ) . '_' . (int)$transaction_number . '_' . TTDate::getDate( 'DATE', time() ) . '.' . $extension;

		return $file_name;
	}

	/**
	 * Complete the cheque pdf and assign to output array.
	 *
	 * @param object $rs_obj
	 * @param object $ps_obj
	 * @param $transaction_number
	 * @param $output
	 * @return array
	 */
	function endChequeFile( $rs_obj, $ps_obj, $transaction_number, $output, $cheque_object ) {
		$file_name = $this->formatFileName( $rs_obj, $rs_obj->getNextTransactionNumber(), 'CHK', 'pdf' ); //transaction number for filename should be first cheque # in this file
		Debug::Text( 'Cheque File name : ' . $file_name . ' Source Account Id: ' . $rs_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
		$output[] = array('file_name' => $file_name, 'mime_type' => 'application/pdf', 'data' => $cheque_object->output( 'PDF' ));

		$rs_obj->setLastTransactionNumber( $transaction_number );
		//rs_obj cleared on save unless passed false
		if ( $rs_obj->isValid() ) {
			$rs_obj->Save( FALSE );
		}

		return $output;
	}

	/**
	 * Compiles the cheque data
	 *
	 * @param object $ps_obj
	 * @param object $pst_obj
	 * @param object $uf_obj
	 * @return array
	 */
	function getChequeData( $ps_obj, $pst_obj, $uf_obj, $transaction_number ) {
		return array(
				'date'             => $ps_obj->getTransactionDate(),
				'amount'           => $pst_obj->getAmount(),
				'stub_left_column' => $uf_obj->getFullName() . "\n" .
						TTi18n::gettext( 'Identification #' ) . ': ' . $ps_obj->getDisplayID() . "\n" .
						TTi18n::gettext( 'Check #' ) . ': ' . $transaction_number . "\n" .
						TTi18n::gettext( 'Net Pay' ) . ': ' . $ps_obj->getCurrencyObject()->getSymbol() .
						$pst_obj->getAmount(), TRUE, $ps_obj->getCurrencyObject()->getRoundDecimalPlaces(),

				'stub_right_column' => TTi18n::gettext( 'Pay Start Date' ) . ': ' . TTDate::getDate( 'DATE', $ps_obj->getStartDate() ) . "\n" .
						TTi18n::gettext( 'Pay End Date' ) . ': ' . TTDate::getDate( 'DATE', $ps_obj->getEndDate() ) . "\n" .
						TTi18n::gettext( 'Payment Date' ) . ': ' . TTDate::getDate( 'DATE', $ps_obj->getTransactionDate() ),
				'start_date'        => $ps_obj->getStartDate(),
				'end_date'          => $ps_obj->getEndDate(),
				'full_name'         => $uf_obj->getFullName(),
				'address1'          => $uf_obj->getAddress1(),
				'address2'          => $uf_obj->getAddress2(),
				'city'              => $uf_obj->getCity(),
				'province'          => $uf_obj->getProvince(),
				'postal_code'       => $uf_obj->getPostalCode(),
				'country'           => $uf_obj->getCountry(),

				'company_name' => $uf_obj->getCompanyObject()->getName(),

				'symbol' => $ps_obj->getCurrencyObject()->getSymbol(),
		);
	}

	/**
	 * Compiles EFT record data.
	 * @param object $pst_obj
	 * @param object $ps_obj
	 * @param object $rs_obj
	 * @param object $uf_obj
	 * @return EFT_Record
	 */
	function getEFTRecord( $eft, $pst_obj, $ps_obj, $rs_obj, $uf_obj, $originator_reference_number ) {
		$record = new EFT_Record();
		$record->setType( 'C' );
		$record->setCPACode( 200 );
		$record->setAmount( $pst_obj->getAmount() );

		$record->setDueDate( TTDate::getBeginDayEpoch( $ps_obj->getTransactionDate() ) );

		//Destination Account
		if ( is_object( $pst_obj->getRemittanceDestinationAccountObject() ) ) {
			$record->setInstitution( $pst_obj->getRemittanceDestinationAccountObject()->getValue1() );
			$record->setTransit( $pst_obj->getRemittanceDestinationAccountObject()->getValue2() );
			$record->setAccount( $pst_obj->getRemittanceDestinationAccountObject()->getValue3() );
		}

		$record->setName( $uf_obj->getFullName( TRUE ) ); //Last name first with middle initial, so it can be properly sorted.

		$record->setOriginatorShortName( $eft->getOriginatorShortName()  );
		$record->setOriginatorLongName( $eft->getOtherData('originator_long_name') );
		$record->setOriginatorReferenceNumber( $originator_reference_number ); //19 or less chars.

		if ( $rs_obj->getValue28() != '' ) { //If specific return bank account is specified, use it here. Otherwise default to the source account.
			$record->setReturnInstitution( $rs_obj->getValue26() );
			$record->setReturnTransit( $rs_obj->getValue27() );
			$record->setReturnAccount( $rs_obj->getValue28() );
		} else {
			$record->setReturnInstitution( $rs_obj->getValue1() );
			$record->setReturnTransit( $rs_obj->getValue2() );
			$record->setReturnAccount( $rs_obj->getValue3() );
		}

		return $record;
	}

	/**
	 * @param null $pstlf
	 * @param null $export_type
	 * @param object $company_obj
	 * @return bool
	 * @throws DBError
	 * @throws GeneralError
	 */
	function exportPayStubTransaction( $pstlf = NULL, $export_type = NULL, $company_obj = NULL ) {

		require_once( Environment::getBasePath() . '/classes/ChequeForms/ChequeForms.class.php' );
		$output = array();

		if ( is_object( $company_obj ) ) {
			$current_company = $company_obj;
		} else {
			global $current_company;
		}

		/** @var PayStubTransactionListFactory $pstlf */
		if ( get_class( $pstlf ) !== 'PayStubTransactionListFactory' ) {
			return FALSE;
		}

		if ( $export_type == '' ) {
			return FALSE;
		}

		$pstlf->StartTransaction();
		if ( $pstlf->getRecordCount() > 0 ) {
			//start with getting paystub transactions sorted by legal entity id, source acocunt
			Debug::Text( 'Getting paystub transactions. Count: ' . $pstlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
			$pstlf_sorted_array = array();
			foreach ( $pstlf as $tmp_pst_obj ) {
				/** @var PayStubTransactionFactory $tmp_pst_obj */
				$pstlf_sorted_array[TTUUID::castUUID($tmp_pst_obj->getRemittanceSourceAccount())][] = $tmp_pst_obj;
			}
			unset( $tmp_pst_obj );

			$data_format_types = array(
					10 => '9085', //cheque_9085
					20 => '9209P', //cheque_9209p
					30 => 'DLT103', //cheque_dlt103
					40 => 'DLT104', //cheque_dlt104
			);

			$i = 0;

			//EACH SOURCE
			foreach ( $pstlf_sorted_array as $pstlf_sub_sorted_array ) {
				$total_credit_amount = 0;
				$transaction_number = 1;
				$n = 0;
				$n_max = ( count( $pstlf_sub_sorted_array ) - 1 );
				//EACH BATCH
				foreach ( $pstlf_sub_sorted_array as $pst_obj ) {
					Debug::Text( '---------------------------------------------------------------------', __FILE__, __LINE__, __METHOD__, 10 );
					Debug::Text( 'PS Transaction ID: ' . $pst_obj->getId() . ' Amount: ' . $pst_obj->getAmount() .' Type: '. $pst_obj->getType() .' Status: '. $pst_obj->getStatus(), __FILE__, __LINE__, __METHOD__, 10 );

					/** @var PayStubTransactionFactory $pst_obj */
					//If the status is a Stop Payment - ReIssue (200), and still status=10 (Valid)
					//clone the object and create a new one to provide history
					if ( $pst_obj->getStatus() == 200 ) {
						if ( $pst_obj->getType() == 10 AND ( $this->getParent() == FALSE OR $this->getParent() == TTUUID::getZeroID() ) ) {
							Debug::Text( '  Found stop payment, re-issing...', __FILE__, __LINE__, __METHOD__, 10 );
							//Stop payment. Mark this record disabled and add a new transaction to the parent chain.
							$old_obj = clone $pst_obj; //clone old object
							$old_obj->clearOldData(); //Clear out old data so its like starting from scratch. This prevents some validation failures on setStatus() changes.
							$old_obj->setType( 20 ); //set old object to InValid
							if ( $old_obj->isValid() ) {
								$old_obj->Save();
							}
							unset( $old_obj ); //get the old object out of memory

							//Since the object has been cloned, the old object id needs to be set as the parent id.
							$pst_obj->clearOldData();
							$pst_obj->setParent( $pst_obj->getId() );
							$pst_obj->setId( FALSE ); //Now that parent id is set, clear the id to make this a new record.
							$pst_obj->setStatus( 10 ); //Pending
						}
					}

					if ( $pst_obj->getStatus() == 10 ) {
						/** @var UserFactory $uf_obj */
						$uf_obj = $pst_obj->getRemittanceDestinationAccountObject()->getUserObject();
						Debug::Text( 'USER: name: [' . $uf_obj->getFullName() . '] ID: ' . $uf_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

						/** @var RemittanceDestinationAccountFactory $rd_obj */
						$rd_obj = $pst_obj->getRemittanceDestinationAccountObject();
						Debug::Text( 'RDA: name: [' . $rd_obj->getName() . '] ID: ' . $rd_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

						/** @var PayStubFactory $ps_obj */
						$ps_obj = $pst_obj->getPayStubObject();
						Debug::Text( 'PayStub: TransactionDate: [' . TTDate::getDate( 'DATE', $ps_obj->getTransactionDate() ) . '] ID: ' . $ps_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

						//Get first rs_obj
						if ( $n == 0 ) {
							/** @var RemittanceSourceAccountFactory $rs_obj */
							$rs_obj = $pst_obj->getRemittanceSourceAccountObject();
							Debug::Text( 'Starting New Batch! Name: [' . $rs_obj->getName() . '] ID: ' . $rs_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
						}
						Debug::Text( 'RSA: name: [' . $rs_obj->getName() . '] Type: '. $rs_obj->getType() .' ID: ' . $rs_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

						//EFT loop
						if ( ( $export_type == 10 OR $export_type == 20 ) AND $rs_obj->getType() == 3000 ) {
							//START BATCH
							if ( $n == 0 ) {
								$next_transaction_number = $rs_obj->getNextTransactionNumber();
								Debug::Text( 'EFT RemittanceSourceAccount: name: [' . $rs_obj->getName() . '] ID: ' . $rs_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
								$eft = $this->startEFTFile( $rs_obj );
							}

							if ( $pst_obj->getAmount() > 0 ) {
								$confirmation_number = strtoupper( substr( sha1( TTUUID::generateUUID() ), -8 ) ); //Generate random string from UUIDs... Keep it around 6 chars to so its easier to exchange over phone or something, as well as to display on pay stubs.
								$record = $this->getEFTRecord( $eft, $pst_obj, $ps_obj, $rs_obj, $uf_obj, $confirmation_number );
								$eft->setRecord( $record );
							}

							$total_credit_amount += $pst_obj->getAmount();

							//END BATCH
							if ( $n == $n_max ) {
								Debug::Text( 'Ending EFT Batch! Source name: [' . $rs_obj->getName() . '] ID: ' . $rs_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
								$output = $this->endEFTFile( $eft, $rs_obj, $uf_obj, $ps_obj, $current_company, $total_credit_amount, $next_transaction_number, $output );
							}
						} //end EFT loop

						//CHECK loop
						if ( ( $export_type == 10 OR $export_type == 30 ) AND $rs_obj->getType() == 2000 ) {
							//START BATCH
							if ( $n == 0 ) {
								$data_format_type_id = $rs_obj->getDataFormat();
								$check_file_obj = new ChequeForms();
								$check_obj = $check_file_obj->getFormObject( strtoupper( $data_format_types[$data_format_type_id] ) );
								$transaction_number = $rs_obj->getNextTransactionNumber();
								Debug::Text( 'New Cheque of type: ' . $data_format_types[$data_format_type_id], __FILE__, __LINE__, __METHOD__, 10 );
							}

							$ps_data = $this->getChequeData( $ps_obj, $pst_obj, $uf_obj, $transaction_number );
							$check_obj->addRecord( $ps_data );
							Debug::Text( 'Row added to cheque' . $ps_obj->getId() . ' Transaction Number: ' . $transaction_number, __FILE__, __LINE__, __METHOD__, 10 );

							$check_file_obj->addForm( $check_obj );
							$confirmation_number = $transaction_number;
							$transaction_number++;

							//end this file and start another file.
							if ( $n == $n_max ) {
								$output = $this->endChequeFile( $rs_obj, $ps_obj, $transaction_number, $output, $check_file_obj );
								$transaction_number = 1;
								//Debug::Arr($output,'NEW File Added To CHQ Output',__FILE__,__LINE__,__METHOD__,10);
							}
						} //end CHECK loop

						if ( isset($confirmation_number) ) { //If no confirmation is set, it likely didn't get paid since it wasn't with check or direct deposit.
							$pst_obj->setConfirmationNumber( $confirmation_number );
							$pst_obj->setStatus( 20 ); //20=Paid
							if ( $pst_obj->isValid() ) {
								$pst_obj->Save();
							} else {
								$pstlf->FailTransaction();
								Debug::Text( '  Validation failed, not sending any output...', __FILE__, __LINE__, __METHOD__, 10 );
								$output = array();
							}
						} else {
							$pstlf->FailTransaction();
							Debug::Text( '  Payment failed, not sending any output...', __FILE__, __LINE__, __METHOD__, 10 );
							$output = array();
						}
					} else {
						Debug::Text( '  Found transaction that is not pending payment, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
					}

					$this->getProgressBarObject()->set( NULL, $i );
					$i++;
					$n++;
				}//foreach
			}

		}//if recordcount

		//$pstlf->FailTransaction(); //Uncomment for easier testing.
		$pstlf->CommitTransaction();

		if ( isset( $output ) ) {
			return $output;
		}

		return FALSE;
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
						case 'currency_id':
							//should never set currency id manually as it comes from the source account.
							//currency is automatically set from setRemittanceDestination()
							break;
						case 'transaction_date':
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
	 * @return mixed
	 */
	function getObjectAsArray( $include_columns = NULL ) {
		$data = array();

		$variable_function_map = $this->getVariableToFunctionMap();
		$rsaf = TTnew( 'RemittanceSourceAccountFactory' );
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'remittance_source_account_type':
							$data[$variable] = Option::getByKey( $this->getColumn( $variable ), $rsaf->getOptions( 'type' ) );
							break;
						case 'status':
						case 'type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'transaction_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
							}
							break;
						case 'currency_id':
						case 'currency':
						case 'currency_rate':
						case 'user_id':
						case 'pay_period_id':
						case 'destination_user_first_name':
						case 'destination_user_last_name':
						case 'remittance_destination_account':
						case 'remittance_source_account':
						case 'pay_stub_run_id':

						case 'transaction_number':

						case 'pay_period_end_date':
						case 'pay_period_start_date':
						case 'pay_period_transaction_date':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'pay_stub_end_date':
						case 'pay_stub_start_date':
						case 'pay_stub_transaction_date':
							//strtotime is needed as the dates are stored as timestamps not epochs.
							$data[$variable] = TTDate::getAPIDate( 'DATE', TTDate::strtotime($this->getColumn( $variable )) );
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Pay Stub Transaction'), NULL, $this->getTable(), $this );
	}

}
?>
