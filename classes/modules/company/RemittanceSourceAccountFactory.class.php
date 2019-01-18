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
class RemittanceSourceAccountFactory extends Factory {
	protected $table = 'remittance_source_account';
	protected $pk_sequence_name = 'remittance_source_account_id_seq'; //PK Sequence name

	protected $legal_entity_obj = NULL;
	protected $currency_obj = NULL;

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
			case 'country':
				/** @var CompanyFactory $cf */
				$cf = TTNew('CompanyFactory');
				$retval = $cf->getOptions('country');
				break;
			case 'type':
				$retval = array(
					//1000 => TTi18n::gettext('TimeTrex EFT'),
					//1010 => TTi18n::gettext('TimeTrex Check'),
					2000 => TTi18n::gettext('Check'),
					3000 => TTi18n::gettext('EFT/ACH'),
					//9000 => TTi18n::gettext('Bitcoin'),
				);
				break;
			case 'data_format':
				$retval = array(
					0 => TTi18n::gettext('-- None --'),
				);

				if ( isset($params['type_id'])
						AND isset($params['country'])
						AND $params['country'] != FALSE ) {
					$tmp_retval = array();
					$valid_keys = array();
					switch( $params['type_id'] ) {
						case 2000: //Check
							$tmp_retval = array(
								10 => TTi18n::gettext('NEBS #9085'), //cheque_9085 // SS9085 (still current for Sage 50 & Accpac)  https://www.nebs.ca/canEcat/products/product_detail.jsp?pc=SS9085
								20 => TTi18n::gettext('NEBS #9209P'), //cheque_9209p // SS9209 (still current for Quickbooks)  https://www.nebs.ca/canEcat/products/product_detail.jsp?pc=SS9209
								30 => TTi18n::gettext('NEBS #DLT103'), //cheque_dlt103 // DLT103 (fill-in lines on cheques)  https://www.deluxe.com/shopdeluxe/pd/laser-top-checks-lined/_/A-DLT103
								40 => TTi18n::gettext('NEBS #DLT104'), //cheque_dlt104 // DLT104 ("$" & "Dollar" on cheques) https://www.deluxe.com/shopdeluxe/pd/laser-top-checks-lined/_/A-DLT104
							);
							$valid_keys = array_keys($tmp_retval);
							break;
						case 3000: //EFT
							$tmp_retval = array(
								10 => TTi18n::gettext( 'United States - ACH (94-Byte)' ),
								20 => TTi18n::gettext( 'Canada - EFT (1464-Byte)' ),
								30 => TTi18n::gettext( 'Canada - EFT CIBC (1464-Byte)'),
								//40 => TTi18n::gettext('Canada - EFT RBC (1464-Byte)'),
								50 => TTi18n::gettext( 'Canada - EFT (105-Byte)' ),
								//60 => TTi18n::gettext('Canada - HSBC EFT-PC (CSV)'),
								70 => TTi18n::gettext( 'Bambora (CSV)' )
							);

							if ( $params['country'] == 'US' ) {
								$valid_keys = array(10);
							}elseif ( $params['country'] == 'CA' ) {
								$valid_keys = array(20, 30, 50, 70);
							}
							break;
					}

					if( count($valid_keys) > 0 ) {
						unset($retval[0]); //remove "-- None --"
						foreach ( $valid_keys as $key ) {
							$retval[$key] = $tmp_retval[$key];
						}
					}
				}
				break;
			case 'columns':
				$retval = array(
					'-1010-status' => TTi18n::gettext('Status'),
					'-1020-type' => TTi18n::gettext('Type'),
					'-1030-legal_name' => TTi18n::gettext('Legal Entity Name'),
					'-1040-name' => TTi18n::gettext('Name'),
					'-1050-description' => TTi18n::gettext('Description'),
					'-1150-data_format' => TTi18n::gettext('Data Format'),
					'-1160-last_transaction_number' => TTi18n::gettext('Last Transaction Number'),
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
					'status',
					'type',
					'legal_name',
					'name',
					'description',
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
			'legal_entity_id' => 'LegalEntity',
			'status_id' => 'Status',
			'status' => FALSE,
			'type_id' => 'Type',
			'type'	=> FALSE,
			'legal_name' => FALSE,
			'name' => 'Name',
			'description' => 'Description',
			'country' => 'Country',
			'currency_id' => 'Currency',
			'currency' => FALSE,
			'data_format_id' => 'DataFormat',
			'data_format' => FALSE,
			'last_transaction_number' => 'LastTransactionNumber',
			'value1' => 'Value1',
			'value2' => 'Value2',
			'value3' => 'Value3',
			'value4' => 'Value4',
			'value5' => 'Value5',
			'value6' => 'Value6',
			'value7' => 'Value7',
			'value8' => 'Value8',
			'value9' => 'Value9',
			'value10' => 'Value10',
			'value11' => 'Value11',
			'value12' => 'Value12',
			'value13' => 'Value13',
			'value14' => 'Value14',
			'value15' => 'Value15',
			'value16' => 'Value16',
			'value17' => 'Value17',
			'value18' => 'Value18',
			'value19' => 'Value19',
			'value20' => 'Value20',
			'value21' => 'Value21',
			'value22' => 'Value22',
			'value23' => 'Value23',
			'value24' => 'Value24',
			'value25' => 'Value25',
			'value26' => 'Value26',
			'value27' => 'Value27',
			'value28' => 'Value28',
			'value29' => 'Value29',
			'value30' => 'Value30',
			'in_use' => FALSE,
			'deleted' => 'Deleted',
		);
		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getCurrencyObject() {
		return $this->getGenericObject( 'CurrencyListFactory', $this->getCurrency(), 'currency_obj' );
	}

	/**
	 * @return bool
	 */
	function getLegalEntityObject() {
		return $this->getGenericObject( 'LegalEntityListFactory', $this->getLegalEntity(), 'legal_entity_obj' );
	}

	/**
	 * @return bool|mixed
	 */
	function getLegalEntity() {
		return $this->getGenericDataValue( 'legal_entity_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLegalEntity( $value) {
		$value = trim($value);
		$value = TTUUID::castUUID( $value );
		if ( $value == '' ) {
			$value = TTUUID::getZeroID();
		}
		return $this->setGenericDataValue( 'legal_entity_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCurrency() {
		return $this->getGenericDataValue( 'currency_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCurrency( $value) {
		$value = trim($value);
		$value = TTUUID::castUUID( $value );
		if ( $value == '' ) {
			$value = TTUUID::getZeroID();
		}
		Debug::Text('Currency ID: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		return $this->setGenericDataValue( 'currency_id', $value );
	}

	/**
	 * @return int
	 */
	function getStatus() {
		return $this->getGenericDataValue( 'status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStatus( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @return int
	 */
	function getType() {
		return $this->getGenericDataValue( 'type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setType( $value ) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'type_id', $value );
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
	 * @return int
	 */
	function getDataFormat() {
		return $this->getGenericDataValue( 'data_format_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDataFormat( $value ) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'data_format_id', $value );
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name) {
		$name = trim($name);

		$company_id = FALSE;
		if ( is_object( $this->getLegalEntityObject() ) ) {
			$company_id = $this->getLegalEntityObject()->getCompany();
		}

		if ( $name == '' ) {
			return FALSE;
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		$ph = array(
			'company_id' => TTUUID::castUUID($company_id),
			'legal_entity_id' => TTUUID::castUUID($this->getLegalEntity()),
			'type_id' => (int)$this->getType(),
			'name' => $name,
		);

		$lef = TTnew( 'LegalEntityFactory' );

		$query = 'SELECT a.id
					FROM '. $this->getTable() .' as a
					LEFT JOIN ' . $lef->getTable() . ' as lef ON ( a.legal_entity_id = lef.id AND lef.deleted = 0  )
					WHERE lef.company_id = ?
					    AND lef.id = ?
					    AND a.type_id = ?
					    AND LOWER(a.name) = LOWER(?)
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
	function setName( $value) {
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
	function setDescription( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'description', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getLastTransactionNumber() {
		return $this->getGenericDataValue( 'last_transaction_number' );
	}

	/**
	 * @return int
	 */
	function getNextTransactionNumber() {
		return ( $this->getLastTransactionNumber() + 1 );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLastTransactionNumber( $value) {
		$value = trim($value);

		//Pull out only digits
//		$value = $this->Validator->stripNonNumeric($value);
//
//		if (	$this->Validator->isFloat(	'last_transaction_number',
//											$value,
//											TTi18n::gettext('Incorrect transaction number')) ) {
//
//			$this->setGenericDataValue( 'last_transaction_number', $value );
//
//			return TRUE;
//		}
//
//		return FALSE;

		$this->setGenericDataValue( 'last_transaction_number', $value );

		return TRUE;
	}

	/**
	 * @return bool|mixed
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
	 * @return bool|mixed
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
	 * VALUE 3 is the account number. It needs to be encrypted.
	 * @return bool|string
	 */
	function getSecureValue3( $account = NULL ) {
		if ( $account == NULL ) {
			$account = $this->getValue3();
		}

		return Misc::censorString( $account, 'X', 1, 2, 1, 4 );
	}

	/**
	 * VALUE 3 is the account number. It needs to be encrypted.
	 * @return bool|string
	 */
	function getValue3() {
		$value = $this->getGenericDataValue( 'value3' );
		if ( $value !== FALSE ) {
			$retval = Misc::decrypt( $value );
			if ( is_numeric( $retval ) ) {
				return $retval;
			}
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
	 * @return bool|mixed
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
	 * @return bool|mixed
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
	 * @return bool|mixed
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
	 * @return bool|mixed
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
	 * @return bool|mixed
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
	 * @return bool|mixed
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
	 * @return bool|mixed
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
	 * @return bool|mixed
	 */
	function getValue11() {
		return $this->getGenericDataValue( 'value11' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue11( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value11', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue12() {
		return $this->getGenericDataValue( 'value12' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue12( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value12', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue13() {
		return $this->getGenericDataValue( 'value13' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue13( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value13', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue14() {
		return $this->getGenericDataValue( 'value14' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue14( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value14', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue15() {
		return $this->getGenericDataValue( 'value15' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue15( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value15', $value );
	}


	/**
	 * @return bool|mixed
	 */
	function getValue16() {
		return $this->getGenericDataValue( 'value16' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue16( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value16', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue17() {
		return $this->getGenericDataValue( 'value17' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue17( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value17', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue18() {
		return $this->getGenericDataValue( 'value18' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue18( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value18', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue19() {
		return $this->getGenericDataValue( 'value19' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue19( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value19', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue20() {
		return $this->getGenericDataValue( 'value20' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue20( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value20', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue21() {
		return $this->getGenericDataValue( 'value21' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue21( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value21', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue22() {
		return $this->getGenericDataValue( 'value22' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue22( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value22', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue23() {
		return $this->getGenericDataValue( 'value23' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue23( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value23', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue24() {
		return $this->getGenericDataValue( 'value24' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue24( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value24', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue25() {
		return $this->getGenericDataValue( 'value25' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue25( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value25', $value );
	}


	/**
	 * @return bool|mixed
	 */
	function getValue26() {
		return $this->getGenericDataValue( 'value26' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue26( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value26', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue27() {
		return $this->getGenericDataValue( 'value27' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue27( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value27', $value );
	}

	/**
	 * VALUE 28 is the return account number. It needs to be encrypted.
	 * @return bool|string
	 */
	function getSecureValue28( $account = NULL ) {
		if ( $account == NULL ) {
			$account = $this->getValue28();
		}

		return Misc::censorString( $account, 'X', 1, 2, 1, 4 );
	}

	/**
	 * VALUE 28 is the return account number. It needs to be encrypted.
	 * @return bool|string
	 */
	function getValue28() {
		$value = $this->getGenericDataValue( 'value28' );
		if ( $value !== FALSE ) {
			$retval = Misc::decrypt( $value );
			if ( is_numeric( $retval ) ) {
				return $retval;
			}
		}
		return FALSE;
	}

	/**
	 * VALUE 28 is the return account number. It needs to be encrypted.
	 * @param $value
	 * @return bool
	 */
	function setValue28($value) {
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

		return $this->setGenericDataValue( 'value28', $encrypted_value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue29() {
		return $this->getGenericDataValue( 'value29' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue29( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value29', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue30() {
		return $this->getGenericDataValue( 'value30' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue30( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'value30', $value );
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = TRUE ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Legal entity
		if ( $this->getLegalEntity() !== FALSE ) {
			$llf = TTnew( 'LegalEntityListFactory' );
			$this->Validator->isResultSetWithRows(	'legal_entity_id',
															$llf->getByID($this->getLegalEntity()),
															TTi18n::gettext('Legal entity is invalid')
														);
		}
		// Currency
		if ( $this->getCurrency() !== FALSE ) {
			$culf = TTnew( 'CurrencyListFactory' );
			$this->Validator->isResultSetWithRows(	'currency_id',
															$culf->getByID($this->getCurrency()),
															TTi18n::gettext('Invalid Currency')
														);
		}
		// Status
		if ( $this->getStatus() !== FALSE ) {
			$this->Validator->inArrayKey( 'status_id',
										  $this->getStatus(),
										  TTi18n::gettext( 'Incorrect Status' ),
										  $this->getOptions( 'status' )
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
		// Country
		if ( $this->getCountry() !== FALSE ) {
			$this->Validator->inArrayKey(	'country',
													$this->getCountry(),
													TTi18n::gettext('Incorrect Country'),
													$this->getOptions('country')
												);
		}
		// Data format
		if ( $this->getDataFormat() !== FALSE ) {
			$this->Validator->inArrayKey(	'data_format_id',
													$this->getDataFormat(),
													TTi18n::gettext('Incorrect data format'),
													$this->getOptions('data_format', array( 'type_id' => $this->getType(), 'country' => $this->getCountry() ) )
												);
		}
		// Name
		if ( $this->getName() !== FALSE AND $this->getName() != '' ) {
			$this->Validator->isLength(	'name',
												$this->getName(),
												TTi18n::gettext('Name is too short or too long'),
												2,
												100
											);
			if ( $this->Validator->isError('name') == FALSE ) {
				$this->Validator->isTrue(		'name',
														$this->isUniqueName( $this->getName() ),
														TTi18n::gettext('Name already exists')
													);
			}
		}
		// Description
		$this->Validator->isLength(	'description',
											$this->getDescription(),
											TTi18n::gettext('Description is invalid'),
											0, 255
										);
		// Value 1
		if ( $this->getValue1() != '' ) {
			$this->Validator->isLength(	'value1',
											$this->getValue1(),
											TTi18n::gettext('Value 1 is invalid'),
											1, 255
										);
		}
		// Value 2
		if ( $this->getValue2() != '' ) {
			$this->Validator->isLength(	'value2',
											$this->getValue2(),
											TTi18n::gettext('Value 2 is invalid'),
											1, 255
										);
		}
		// Value 4
		if ( $this->getValue4() != '' ) {
			$this->Validator->isLength(	'value4',
											$this->getValue4(),
											TTi18n::gettext('Value 4 is invalid'),
											1, 255
										);
		}
		// Value 5
		if ( $this->getValue5() != '' ) {
			$this->Validator->isLength(	'value5',
											$this->getValue5(),
											TTi18n::gettext('Value 5 is invalid'),
											1, 255
										);
		}
		// Value 6
		if ( $this->getValue6() != '' ) {
			$this->Validator->isLength(	'value6',
											$this->getValue6(),
											TTi18n::gettext('Value 6 is invalid'),
											1, 255
										);
		}
		// Value 7
		if ( $this->getValue7() != '' ) {
			$this->Validator->isLength(	'value7',
											$this->getValue7(),
											TTi18n::gettext('Value 7 is invalid'),
											1, 255
										);
		}
		// Value 8
		if ( $this->getValue8() != '' ) {
			$this->Validator->isLength(	'value8',
											$this->getValue8(),
											TTi18n::gettext('Value 8 is invalid'),
											1, 255
										);
		}
		// Value 9
		if ( $this->getValue9() != '' ) {
			$this->Validator->isLength(	'value9',
											$this->getValue9(),
											TTi18n::gettext('Value 9 is invalid'),
											1, 255
										);
		}
		// Value 10
		if ( $this->getValue10() != '' ) {
			$this->Validator->isLength(	'value10',
											$this->getValue10(),
											TTi18n::gettext('Value 10 is invalid'),
											1, 255
										);
		}
		// Value 11
		if ( $this->getValue11() != '' ) {
			$this->Validator->isLength(	'value11',
											$this->getValue11(),
											TTi18n::gettext('Value 11 is invalid'),
											1, 255
										);
		}
		// Value 12
		if ( $this->getValue12() != '' ) {
			$this->Validator->isLength(	'value12',
											$this->getValue12(),
											TTi18n::gettext('Value 12 is invalid'),
											1, 255
										);
		}
		// Value 13
		if ( $this->getValue13() != '' ) {
			$this->Validator->isLength(	'value13',
											$this->getValue13(),
											TTi18n::gettext('Value 13 is invalid'),
											1, 255
										);
		}
		// Value 14
		if ( $this->getValue14() != '' ) {
			$this->Validator->isLength(	'value14',
											$this->getValue14(),
											TTi18n::gettext('Value 14 is invalid'),
											1, 255
										);
		}
		// Value 15
		if ( $this->getValue15() != '' ) {
			$this->Validator->isLength(	'value15',
											$this->getValue15(),
											TTi18n::gettext('Value 15 is invalid'),
											1, 255
										);
		}
		// Value 16
		if ( $this->getValue16() != '' ) {
			$this->Validator->isLength(	'value16',
											$this->getValue16(),
											TTi18n::gettext('Value 16 is invalid'),
											1, 255
										);
		}
		// Value 17
		if ( $this->getValue17() != '' ) {
			$this->Validator->isLength(	'value17',
											$this->getValue17(),
											TTi18n::gettext('Value 17 is invalid'),
											1, 255
										);
		}
		// Value 18
		if ( $this->getValue18() != '' ) {
			$this->Validator->isLength(	'value18',
											$this->getValue18(),
											TTi18n::gettext('Value 18 is invalid'),
											1, 255
										);
		}
		// Value 19
		if ( $this->getValue19() != '' ) {
			$this->Validator->isLength(	'value19',
											$this->getValue19(),
											TTi18n::gettext('Value 19 is invalid'),
											1, 255
										);
		}
		// Value 20
		if ( $this->getValue20() != '' ) {
			$this->Validator->isLength(	'value20',
											$this->getValue20(),
											TTi18n::gettext('Value 20 is invalid'),
											1, 255
										);
		}
		// Value 21
		if ( $this->getValue21() != '' ) {
			$this->Validator->isLength(	'value21',
											$this->getValue21(),
											TTi18n::gettext('Value 21 is invalid'),
											1, 255
										);
		}
		// Value 22
		if ( $this->getValue22() != '' ) {
			$this->Validator->isLength(	'value22',
											$this->getValue22(),
											TTi18n::gettext('Value 22 is invalid'),
											1, 255
										);
		}
		// Value 23
		if ( $this->getValue23() != '' ) {
			$this->Validator->isLength(	'value23',
											$this->getValue23(),
											TTi18n::gettext('Value 23 is invalid'),
											1, 255
										);
		}
		// Value 24
		if ( $this->getValue24() != '' ) {
			$this->Validator->isLength(	'value24',
											$this->getValue24(),
											TTi18n::gettext('Value 24 is invalid'),
											1, 255
										);
		}
		// Value 25
		if ( $this->getValue25() != '' ) {
			$this->Validator->isLength(	'value25',
											$this->getValue25(),
											TTi18n::gettext('Value 25 is invalid'),
											1, 255
										);
		}
		// Value 26
		if ( $this->getValue26() != '' ) {
			$this->Validator->isLength(	'value26',
											$this->getValue26(),
											TTi18n::gettext('Value 26 is invalid'),
											1, 255
										);
		}
		// Value 27
		if ( $this->getValue27() != '' ) {
			$this->Validator->isLength(	'value27',
											$this->getValue27(),
											TTi18n::gettext('Value 27 is invalid'),
											1, 255
										);
		}
		// Value 28
		if ( $this->getValue28() != '' ) {
			$this->Validator->isLength(	'value28',
											$this->getValue28(),
											TTi18n::gettext('Value 28 is invalid'),
											1, 255
										);
		}
		// Value 29
		if ( $this->getValue29() != '' ) {
			$this->Validator->isLength(	'value29',
											$this->getValue29(),
											TTi18n::gettext('Value 29 is invalid'),
											1, 255
										);
		}
		// Value 30
		if ( $this->getValue30() != '' ) {
			$this->Validator->isLength(	'value30',
											$this->getValue30(),
											TTi18n::gettext('Value 30 is invalid'),
											1, 255
										);
		}
		//
		// ABOVE: Validation code moved from set*() functions.
		//
		$data_diff = $this->getDataDifferences();
		//$this->setProvince( $this->getProvince() ); //Not sure why this was there, but it causes duplicate errors if the province is incorrect.
		if ( $this->getDeleted() == TRUE ) {
			$rdalf = TTnew( 'RemittanceDestinationAccountListFactory');
			$rdalf->getByRemittanceSourceAccountId( $this->getId(), 1 ); //limit 1.
			if ( $rdalf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
											FALSE,
											TTi18n::gettext('This remittance source account is currently in use') .' '. TTi18n::gettext('by remittance destination accounts') );
			}

			$pralf = TTnew( 'PayrollRemittanceAgencyListFactory' );
			$pralf->getByRemittanceSourceAccountId( $this->getId(), 1 ); //limit 1.
			if ( $pralf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
											FALSE,
											TTi18n::gettext('This remittance source account is currently in use') .' '. TTi18n::gettext('by remittance agencies') );
			}

			$pstlf = TTnew( 'PayStubTransactionListFactory' );
			$pstlf->getByRemittanceSourceAccountId( $this->getId(), 1 ); //limit 1.
			if ( $pstlf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
											FALSE,
											TTi18n::gettext('This remittance source account is currently in use') .' '. TTi18n::gettext('by pay stub transactions') );
			}
		}

		if ( $this->getType() == 2000 ) {
			// when type is CHECK
			if ( $this->getLastTransactionNumber() !== FALSE ) {
				$value = $this->Validator->stripNonNumeric( $this->getLastTransactionNumber() );
				$this->Validator->isFloat(
										'last_transaction_number',
												$value,
												TTi18n::gettext('Incorrect last check number'));
			}

		} elseif ( $this->getType() == 3000 AND $this->getCountry() == 'US' ) {
			// when type is ACH
			if ( $this->getLastTransactionNumber() !== FALSE ) {
				$value = $this->Validator->stripNonNumeric( $this->getLastTransactionNumber() );
				$this->Validator->isFloat(
										'last_transaction_number',
												$value,
											TTi18n::gettext('Incorrect last batch number'));
			}
			// Routing number
			if ( $this->getValue2() !== FALSE ) {
				if ( strlen( $this->getValue2() ) < 2 OR strlen( $this->getValue2() ) > 15 ) {
					$this->Validator->isTrue(		'value2',
													FALSE,
													TTi18n::gettext('Invalid routing number length'));
				} else {
					$this->Validator->isNumeric(	'value2',
															$this->getValue2(),
														TTi18n::gettext('Invalid routing number, must be digits only'));
				}
			}
			// Account number
			if ( $this->getValue3() !== FALSE ) {
				if ( strlen( $this->getValue3() ) < 3 OR strlen( $this->getValue3() ) > 20 ) {
					$this->Validator->isTrue(		'value3',
													FALSE,
													TTi18n::gettext('Invalid account number length'));
				} else {
					$this->Validator->isNumeric(	'value3',
															$this->getValue3(),
															TTi18n::gettext('Invalid account number, must be digits only'));
				}
			}

			//Not all companies have this specified and it causes problems during upgrade.
//			if ( $this->getValue4() == '' ) {
//				$this->Validator->isTrue(		'value4',
//												FALSE,
//												TTi18n::gettext('Business Number not specified'));
//			}
//
//			if ( $this->getValue5() == '' ) {
//				$this->Validator->isTrue(		'value5',
//												FALSE,
//												TTi18n::gettext('Immediate origin not specified'));
//			}
//
//			if ( $this->getValue7() == '' ) {
//				$this->Validator->isTrue(		'value7',
//												FALSE,
//												TTi18n::gettext('Immediate destination not specified'));
//			}
		} elseif ( $this->getType() == 3000 AND $this->getCountry() == 'CA') {
			// when type is EFT
			if ( $this->getLastTransactionNumber() !== FALSE ) {
				$this->Validator->isFloat(
										'last_transaction_number',
										$this->Validator->stripNonNumeric( $this->getLastTransactionNumber() ),
										TTi18n::gettext('Incorrect last batch number'));
			}
			// Institution number
			if ( $this->getValue1() !== FALSE ) {
				if ( strlen( $this->getValue1() ) < 2 OR strlen( $this->getValue1() ) > 3 ) {
					$this->Validator->isTrue(		'value1',
													FALSE,
													TTi18n::gettext('Invalid institution number length'));
				}
			}
			// Transit number
			if ( $this->getValue2() !== FALSE ) {
				if ( strlen( $this->getValue2() ) < 2 OR strlen( $this->getValue2() ) > 15 ) {
					$this->Validator->isTrue(		'value2',
													FALSE,
													TTi18n::gettext('Invalid transit number length'));
				} else {
					$this->Validator->isNumeric(	'value2',
															$this->getValue2(),
															TTi18n::gettext('Invalid transit number, must be digits only'));
				}
			}
			// Account number
			if ( $this->getValue3() !== FALSE ) {
				if ( strlen( $this->getValue3() ) < 3 OR strlen( $this->getValue3() ) > 20 ) {
					$this->Validator->isTrue(		'value3',
														FALSE,
														TTi18n::gettext('Invalid account number length'));
				} else {
					$this->Validator->isNumeric(	'value3',
															$this->getValue3(),
															TTi18n::gettext('Invalid account number, must be digits only'));
				}
			}

			//Not all companies have this specified and it causes problems during upgrade.
//			if ( $this->getValue5() == '' ) {
//				$this->Validator->isTrue(		'value5',
//												FALSE,
//												TTi18n::gettext('Originator ID not specified'));
//			}
//
//			if ( $this->getValue7() == '' ) {
//				$this->Validator->isTrue(		'value7',
//												FALSE,
//												TTi18n::gettext('Data center not specified'));
//			}
		}

		if ( is_array($data_diff) AND isset($data_diff['legal_entity_id']) ) {
			$rdalf = TTnew( 'RemittanceDestinationAccountListFactory');
			$rdalf->getByRemittanceSourceAccountId( $this->getId(), 1 ); //limit 1.
			if ( $rdalf->getRecordCount() > 0 ) {
				$this->Validator->isTrue(		'legal_entity_id',
												 FALSE,
												 TTi18n::gettext('This remittance source account is currently in use employee by payment methods'));
			}
			unset($rdalf);
		}

		//Make sure these fields are always specified, but don't break mass edit.
		if ( $this->Validator->getValidateOnly() == FALSE ) {
			if ( $this->getLegalEntity() == FALSE AND $this->Validator->hasError('legal_entity_id') == FALSE ) {
				$this->Validator->isTrue(		'legal_entity_id',
												FALSE,
												TTi18n::gettext('Please specify a legal entity'));
			}

			if ( $this->getCurrency() == FALSE AND $this->Validator->hasError('currency_id') == FALSE ) {
				$this->Validator->isTrue(		'currency_id',
												FALSE,
												TTi18n::gettext('Please specify a currency'));
			}

			if ( $this->getStatus() == FALSE AND $this->Validator->hasError('status_id') == FALSE ) {
				$this->Validator->isTrue(		'status_id',
												FALSE,
												TTi18n::gettext('Please specify status'));
			}

			if ( $this->getType() == FALSE AND $this->Validator->hasError('type_id') == FALSE ) {
				$this->Validator->isTrue(		'type_id',
												FALSE,
												TTi18n::gettext('Please specify type'));
			}
		}

		if ( $this->getDeleted() != TRUE AND $this->Validator->getValidateOnly() == FALSE ) { //Don't check the below when mass editing.
			if ( $this->getName() == FALSE AND $this->Validator->hasError('name') == FALSE ) {
				$this->Validator->isTrue(		'name',
												FALSE,
												TTi18n::gettext('Please specify a name'));
			}

			if ( $this->getDataFormat() == FALSE ) {
				$this->Validator->isTrue(		'data_format_id',
												FALSE,
												TTi18n::gettext('Please specify data format'));
			}
		}

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
	 * @return array
	 */
	function getObjectAsArray( $include_columns = NULL ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		$data = array();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'legal_name':
						case 'currency':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'type':
						case 'status':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'data_format':
							$data[$variable] = Option::getByKey( $this->getDataFormat(), $this->getOptions( $variable, array( 'type_id' => $this->getType() ) ) );
							break;
						case 'in_use':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'value3': //Account Number
							$data[$variable] = $this->getSecureValue3();
							break;
						case 'value28': //Return Account Number
							$data[$variable] = $this->getSecureValue28();
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
	 * @param $lf
	 * @param bool $include_blank
	 * @return array|bool
	 */
	function getArrayByListFactory( $lf, $include_blank = TRUE ) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		$list = array();
		if ( $include_blank == TRUE ) {
			$list[TTUUID::getZeroID()] = '--';
		}

		foreach ($lf as $obj) {
			$list[$obj->getID()] = $obj->getName();
		}

		if ( empty($list) == FALSE ) {
			return $list;
		}

		return FALSE;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Remittance source account') .': '. $this->getName(), NULL, $this->getTable(), $this );
	}

}
?>
