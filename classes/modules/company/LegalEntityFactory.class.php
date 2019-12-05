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
 * @package Modules\Company
 */
class LegalEntityFactory extends Factory {
	protected $table = 'legal_entity';
	protected $pk_sequence_name = 'legal_entity_id_seq'; //PK Sequence name

	protected $address_validator_regex = '/^[a-zA-Z0-9-,_\/\.\'#\ |\x{0080}-\x{FFFF}]{1,250}$/iu';
	protected $city_validator_regex = '/^[a-zA-Z0-9-,_\.\'#\ |\x{0080}-\x{FFFF}]{1,250}$/iu';
	protected $short_name_validator_regex = '/^[a-zA-Z0-9-]{1,15}$/iu'; //Short name must only allow characters available for EFT/ACH banking systems.

	protected $company_obj = NULL;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|bool|null
	 */
	function _getFactoryOptions( $name, $parent = NULL ) {

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
					10 => TTi18n::gettext('ACTIVE'),
					20 => TTi18n::gettext('CLOSED')
				);
				break;
			case 'payment_services_status':
				$retval = array(
						10 => TTi18n::gettext('Enabled'),
						20 => TTi18n::gettext('Disabled')
				);
				break;

			case 'type':
				$retval = array(
					0  => TTi18n::getText('- Please Choose -'),
					10 => TTi18n::gettext('Corporation'),
					20 => TTi18n::gettext('LLC'),
					30 => TTi18n::gettext('Partnership'),
					40 => TTi18n::gettext('S-Corporation'),
					50 => TTi18n::gettext('Sole Proprietorship'),
					60 => TTi18n::gettext('LLP'),
					70 => TTi18n::gettext('Non-Profit Corporation'),
				);
				break;
			case 'classification_code':
				$file_name = __DIR__ . DIRECTORY_SEPARATOR . 'naics.csv';
				if ( file_exists($file_name) ) {
					$retval[0] = TTi18n::getText('- Please Choose -');
					$codes = Misc::parseCSV($file_name, TRUE);
					foreach( $codes as $row ) {
						if ( isset($row['code']) AND $row['code'] != '' ) {
							$name = str_repeat('&nbsp;', ( ( strlen( $row['code'] ) - 2 ) * 3 ) );
							if ( strlen($row['code']) == 6 ) {
							   $name .= ''. $row['code'] .' - '. $row['title'];
							} else {
								$name .= '['. $row['title'] .']';
							}

							$retval[$row['code']] = $name;
						}
					}

					//Must keep exact order from file.
					$retval = Misc::addSortPrefix( $retval );
				} else {
					$retval = array();
				}
				break;
			case 'columns':
				$retval = array(
					'-1010-legal_name' => TTi18n::gettext('Legal Name'),
					'-1020-trade_name' => TTi18n::gettext('Trade Name'),
					'-1021-short_name' => TTi18n::gettext('Short Name'),
					'-1022-type' => TTi18n::gettext('Type'),
					'-1025-classification_code_name' => TTi18n::gettext('Classification Code'),
					'-1030-status' => TTi18n::gettext('Status'),
					'-1140-address1' => TTi18n::gettext('Address 1'),
					'-1150-address2' => TTi18n::gettext('Address 2'),
					'-1160-city' => TTi18n::gettext('City'),
					'-1170-province' => TTi18n::gettext('Province/State'),
					'-1180-country' => TTi18n::gettext('Country'),
					'-1190-postal_code' => TTi18n::gettext('Postal Code'),
					'-1200-work_phone' => TTi18n::gettext('Work Phone'),
					'-1210-fax_phone' => TTi18n::gettext('Fax Phone'),

					'-1250-start_date' => TTi18n::gettext('Start Date'),
					'-1260-end_date' => TTi18n::gettext('End Date'),

//					'-1300-tag' => TTi18n::gettext('Tags'),
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
					'legal_name',
					'trade_name',
					'city',
					'province',
				);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
					'legal_name',
				);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
					'country',
					'province',
					'postal_code'
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
			'company_id' => 'Company',
			'status_id' => 'Status',
			'status' => FALSE,
			'type_id' => 'Type',
			'type' => FALSE,
			'classification_code' => 'ClassificationCode',
			'classification_code_name' => FALSE,
			'legal_name' => 'LegalName',
			'trade_name' => 'TradeName',
			'short_name' => 'ShortName',
			'address1' => 'Address1',
			'address2' => 'Address2',
			'city' => 'City',
			'country' => 'Country',
			'province' => 'Province',
			'postal_code' => 'PostalCode',
			'work_phone' => 'WorkPhone',
			'fax_phone' => 'FaxPhone',
			'start_date' => 'StartDate',
			'end_date' => 'EndDate',

			'payment_services_status_id' => 'PaymentServicesStatus',
			'payment_services_user_name' => 'PaymentServicesUserName',
			'payment_services_api_key' => 'PaymentServicesAPIKey',

			'in_use' => FALSE,
			'deleted' => 'Deleted',
		);
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
	 * @param $value
	 * @return bool
	 */
	function setCompany( $value) {
		$value = TTUUID::castUUID( $value );
		return $this->setGenericDataValue( 'company_id', $value );
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
	 * @return int
	 */
	function getClassificationCode() {
		return $this->getGenericDataValue( 'classification_code' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setClassificationCode( $value ) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'classification_code', $value );
	}


	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name) {
		Debug::Arr($this->getCompany(), 'Company: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( $this->getCompany() == FALSE ) {
			return FALSE;
		}

		$name = trim($name);
		if ( $name == '' ) {
			return FALSE;
		}

		$ph = array(
			'company_id' => $this->getCompany(),
			'legal_name' => TTi18n::strtolower($name),
		);

		$query = 'select id from '. $this->getTable() .'
					where company_id = ?
						AND lower(legal_name) = ?
						AND deleted = 0';
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
	function getLegalName() {
		return $this->getGenericDataValue( 'legal_name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLegalName( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'legal_name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getTradeName() {
		return $this->getGenericDataValue( 'trade_name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTradeName( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'trade_name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getShortName() {
		return $this->getGenericDataValue( 'short_name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setShortName( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'short_name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getAddress1() {
		return $this->getGenericDataValue( 'address1' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAddress1( $value ) {
		$value = trim($value);
		return $this->setGenericDataValue( 'address1', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getAddress2() {
		return $this->getGenericDataValue( 'address2' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAddress2( $value) {
		$value = trim($value);
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
	function setCity( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'city', $value );
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
	function setProvince( $value) {
		Debug::Text('Country: '. $this->getCountry() .' Province: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		return $this->setGenericDataValue( 'province', strtoupper( trim($value) ) );
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
	function setCountry( $value) {
		return $this->setGenericDataValue( 'country', strtoupper( trim($value) ) );
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
	function setPostalCode( $value) {
		$value = strtoupper( $this->Validator->stripSpaces($value) );
		return $this->setGenericDataValue( 'postal_code', $value );
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
	function setWorkPhone( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'work_phone', $value );
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
	function setFaxPhone( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'fax_phone', $value );
	}

	/**
	 * @return int
	 */
	function getStartDate() {
		return (int)$this->getGenericDataValue( 'start_date' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStartDate( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'start_date', $value );
	}

	/**
	 * @return int
	 */
	function getEndDate() {
		return (int)$this->getGenericDataValue( 'end_date' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEndDate( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'end_date', $value );
	}

	/**
	 * @return int
	 */
	function getPaymentServicesStatus() {
		return $this->getGenericDataValue( 'payment_services_status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPaymentServicesStatus( $value) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'payment_services_status_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPaymentServicesUserName() {
		return $this->getGenericDataValue( 'payment_services_user_name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPaymentServicesUserName( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'payment_services_user_name', $value );
	}

	/**
	 * Get a secure version of the PaymentServicesAPIKey
	 * @param null $value
	 * @return bool|string
	 */
	function getSecurePaymentServicesAPIKey( $value = NULL ) {
		if ( $value == NULL ) {
			$value = $this->getPaymentServicesAPIKey();
		}

		return Misc::censorString( $value, 'X', 1, 6, 1, 6 );
	}

	/**
	 * @return bool|mixed
	 */
	function getPaymentServicesAPIKey() {
		$value = $this->getGenericDataValue( 'payment_services_api_key' );

		if ( $value !== FALSE ) {
			$retval = Misc::decrypt( $value );
			if ( is_string( $retval ) ) {
				return $retval;
			}
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPaymentServicesAPIKey( $value) {
		//If X's are in the key, skip setting it
		// Also if a colon is in the key, its likely an encrypted string, also skip.
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

		return $this->setGenericDataValue( 'payment_services_api_key', $encrypted_value );
	}

	/**
	 * @return bool
	 */
	function isLogoExists() {
		return file_exists( $this->getLogoFileName() );
	}

	/**
	 * @param null $legal_entity_id
	 * @param bool $include_default_logo
	 * @return string
	 */
	function getLogoFileName( $legal_entity_id = NULL, $include_default_logo = TRUE ) {
		$logo_file_name = $this->getStoragePath( $legal_entity_id ) . DIRECTORY_SEPARATOR .'logo.img';
		if ( file_exists( $logo_file_name ) == FALSE ) {
			if ( $include_default_logo == TRUE ) {
				$c_obj = $this->getCompanyObject();
				if ( is_object($c_obj) ) {
					$logo_file_name = $c_obj->getLogoFileName( NULL, TRUE, FALSE );
				}
				unset($c_obj);
			} else {
				return FALSE;
			}
		}

		//Debug::Text('Logo File Name: '. $logo_file_name .' Legal Entity Id: '. $legal_entity_id, __FILE__, __LINE__, __METHOD__, 10);
		return $logo_file_name;
	}

	/**
	 * @param null $legal_entity_id
	 * @return bool
	 */
	function cleanStoragePath( $legal_entity_id = NULL ) {
		if ( $legal_entity_id == '' ) {
			$legal_entity_id = $this->getCompany();
		}

		if ( $legal_entity_id == '' ) {
			return FALSE;
		}

		$dir = $this->getStoragePath( $legal_entity_id ) . DIRECTORY_SEPARATOR;

		if ( $dir != '' ) {
			//Delete tmp files.
			foreach(glob($dir.'*') as $filename) {
				unlink($filename);
				Misc::deleteEmptyDirectory( dirname( $filename ), 0 ); //Recurse to $user_id parent level and remove empty directories.
			}
		}

		return TRUE;
	}

	/**
	 * @param null $legal_entity_id
	 * @return bool|string
	 */
	function getStoragePath( $legal_entity_id = NULL ) {
		if ( $legal_entity_id == '' ) {
			$legal_entity_id = $this->getID();
		}

		if ( $legal_entity_id == '' ) {
			return FALSE;
		}

		return Environment::getStorageBasePath() . DIRECTORY_SEPARATOR .'legal_entity_logo'. DIRECTORY_SEPARATOR . $legal_entity_id;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = TRUE ) {
		//
		//NOTE: CompanyFactory->Validate and LegalEntityFactory->Validate() need to be identical on the fields they share, since legal entities are automatically created from companies.
		//

		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Company
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$this->Validator->isResultSetWithRows(	'company_id',
												  $clf->getByID($this->getCompany()),
												  TTi18n::gettext('Company is invalid')
		);

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

		// Classification Code
		$this->Validator->inArrayKey(	'classification_code',
										 $this->getClassificationCode(),
										 TTi18n::gettext('Incorrect Classification Code'),
										 Misc::trimSortPrefix( $this->getOptions('classification_code') )
		);

		// Legal name
		if ( $this->getLegalName() !== FALSE ) {
			$this->Validator->isLength(		'legal_name',
											   $this->getLegalName(),
											   TTi18n::gettext('Legal name is too short or too long'),
											   2,
											   100
			);
			if ( $this->Validator->isError('legal_name') == FALSE ) {
				$this->Validator->isTrue(		'legal_name',
												 $this->isUniqueName( $this->getLegalName() ),
												 TTi18n::gettext('Legal name already exists')
				);
			}
		}

		// Trade name
		if ( $this->getTradeName() !== FALSE ) {
			$this->Validator->isLength(		'trade_name',
											   $this->getTradeName(),
											   TTi18n::gettext('Trade name is too short or too long'),
											   2,
											   100
			);
		}

		// Short name
		if ( $this->getShortName() != '' ) {
			//Short name must only allow characters available in domain names.
			$this->Validator->isLength(		'short_name',
											   $this->getShortName(),
											   TTi18n::gettext('Short name is too short or too long'),
											   2,
											   15
			);
			if ( $this->Validator->isError('short_name') == FALSE ) {
				$this->Validator->isRegEx(		'short_name',
												  $this->getShortName(),
												  TTi18n::gettext('Short name must not contain any special characters'),
												  $this->short_name_validator_regex
				);
			}
		}

		// Address1
		if ( $this->getAddress1() != '' ) {
			$this->Validator->isLength(		'address1',
											   $this->getAddress1(),
											   TTi18n::gettext('Address1 is too short or too long'),
											   2,
											   250
			);

			if ( $this->Validator->isError('address1') == FALSE ) {
				$this->Validator->isRegEx(		'address1',
												  $this->getAddress1(),
												  TTi18n::gettext('Address1 contains invalid characters'),
												  $this->address_validator_regex
				);
			}
		}
		// Address2
		if ( $this->getAddress2() != '' ) {
			$this->Validator->isLength(		'address2',
											   $this->getAddress2(),
											   TTi18n::gettext('Address2 is too short or too long'),
											   2,
											   250
			);

			if ( $this->Validator->isError('address2') == FALSE ) {
				$this->Validator->isRegEx(		'address2',
												  $this->getAddress2(),
												  TTi18n::gettext('Address2 contains invalid characters'),
												  $this->address_validator_regex
				);
			}
		}

		// City -- Allow it to be blank as Company records can have it blank as well, and legal entities often get automatically created from them.
		if ( $this->getCity() != '' ) {
			$this->Validator->isLength( 'city',
										$this->getCity(),
										TTi18n::gettext( 'City name is too short or too long' ),
										2,
										250
			);
		}

		if ( $this->getCity() != '' AND $this->Validator->isError('city') == FALSE ) {
			$this->Validator->isRegEx( 'city',
									   $this->getCity(),
									   TTi18n::gettext( 'City contains invalid characters' ),
									   $this->city_validator_regex
			);
		}

		//Needed for country/province validation.
		$cf = TTnew( 'CompanyFactory' ); /** @var CompanyFactory $cf */

		// Country
		if ( $this->getCountry() !== FALSE ) {
			$this->Validator->inArrayKey(		'country',
												 $this->getCountry(),
												 TTi18n::gettext('Invalid Country'),
												 $cf->getOptions('country')
			);
		}

		// Province
		if ( $this->getCountry() != FALSE  ) {
			$options_arr = $cf->getOptions('province');
			if ( isset($options_arr[$this->getCountry()]) ) {
				$options = $options_arr[$this->getCountry()];
			} else {
				$options = array();
			}
			$this->Validator->inArrayKey(	'province',
											 $this->getProvince(),
											 TTi18n::gettext('Invalid Province/State'),
											 $options
			);
			unset($options_arr, $options);
		}

		// Postal/ZIP Code
		if ( $this->getPostalCode() != '' ) {
			$this->Validator->isPostalCode(		'postal_code',
												   $this->getPostalCode(),
												   TTi18n::gettext('Postal/ZIP Code contains invalid characters, invalid format, or does not match Province/State'),
												   $this->getCountry(), $this->getProvince()
			);
			if ( $this->Validator->isError('postal_code') == FALSE ) {
				$this->Validator->isLength(		'postal_code',
												   $this->getPostalCode(),
												   TTi18n::gettext('Postal/ZIP Code is too short or too long'),
												   1,
												   10
				);
			}
		}

		// Work phone number
		if ( $this->getWorkPhone() !== FALSE ) {
			$this->Validator->isPhoneNumber(	'work_phone',
												$this->getWorkPhone(),
												TTi18n::gettext('Work phone number is invalid')
			);
		}

		// Fax phone number
		if ( $this->getFaxPhone() != '' ) {
			$this->Validator->isPhoneNumber(	'fax_phone',
												$this->getFaxPhone(),
												TTi18n::gettext('Fax phone number is invalid')
			);
		}

		// Start date
		if ( $this->getStartDate() != '' ) {
			$this->Validator->isDate(		'start_date',
											 $this->getStartDate(),
											 TTi18n::gettext('Incorrect start date')
			);
		}

		// End date
		if ( $this->getEndDate() != '' ) {
			$this->Validator->isDate(		'end_date',
											 $this->getEndDate(),
											 TTi18n::gettext('Incorrect end date')
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		//Make sure these fields are always specified, but don't break mass edit.
		if ( $this->Validator->getValidateOnly() == FALSE ) {
			if ( $this->getLegalName() == FALSE AND $this->Validator->hasError( 'legal_name' ) == FALSE ) {
				$this->Validator->isTRUE( 'legal_name',
										  FALSE,
										  TTi18n::gettext( 'Please specify a legal name' ) );
			}

			if ( $this->getTradeName() == FALSE AND $this->Validator->hasError( 'trade_name' ) == FALSE ) {
				$this->Validator->isTRUE( 'trade_name',
										  FALSE,
										  TTi18n::gettext( 'Please specify a trade name' ) );
			}

			if ( $this->getCompany() == FALSE AND $this->Validator->hasError( 'company' ) == FALSE ) {
				$this->Validator->isTrue( 'company',
										  FALSE,
										  TTi18n::gettext( 'Please specify a company' ) );
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

		// Payment Services Status
		if ( $this->getPaymentServicesStatus() !== FALSE ) {
			$this->Validator->inArrayKey( 'payment_services_status_id',
										  $this->getPaymentServicesStatus(),
										  TTi18n::gettext( 'Incorrect Payment Services Status' ),
										  $this->getOptions( 'payment_services_status' )
			);
		}

		//Make sure if payment services is enabled, that the country is a valid one that is supported.
		if ( $this->getPaymentServicesStatus() == 10 AND !in_array( $this->getCountry(), array( 'CA', 'US') ) ) { //10=Enabled.
			$this->Validator->isTrue( 'payment_services_status_id',
									  FALSE,
									  TTi18n::gettext( 'Payment Services is only available in Canada or the United States' ) );

		}

		if ( $this->getPaymentServicesStatus() == 10 AND $this->getPaymentServicesUserName() != '' AND $this->getPaymentServicesAPIKey() != '' ) { //10=Enabled
			$this->Validator->isTrue( 'payment_services_user_name',
									  $this->checkPaymentServicesCredentials(),
									  TTi18n::gettext( 'Payment Services User Name or API Key is incorrect, or service not activated' ) );
		}

		if ( $this->getDeleted() == TRUE ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$ulf->getByLegalEntityIdAndCompanyId( $this->getId(), $this->getCompany() );
			if ( $ulf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
					FALSE,
					TTi18n::gettext('This legal entity is currently in use by employees') );
			}

			$praf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $praf */
			$praf->getByLegalEntityIdAndCompanyId( $this->getId(), $this->getCompany() );
			if ( $praf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
					FALSE,
					TTi18n::gettext('This legal entity is currently in use by payroll remittance agency') );
			}

			$rsaf = TTnew( 'RemittanceSourceAccountListFactory' ); /** @var RemittanceSourceAccountListFactory $rsaf */
			$rsaf->getByLegalEntityIdAndCompanyId( $this->getId(), $this->getCompany() );
			if ( $rsaf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
					FALSE,
					TTi18n::gettext('This legal entity is currently in use by remittance source account') );
			}
		}

		if ( $this->getDeleted() != TRUE AND $this->Validator->getValidateOnly() == FALSE ) { //Don't check the below when mass editing.
			if ( $this->getLegalName() == FALSE AND $this->Validator->hasError('legal_name') == FALSE  ) {
				$this->Validator->isTRUE(	'legal_name',
											FALSE,
											TTi18n::gettext('Please specify a legal name') );
			}

		}

		return TRUE;
	}

	/**
	 * @return TimeTrexPaymentServices
	 */
	function getPaymentServicesAPIObject() {
		require_once( Environment::getBasePath() . DIRECTORY_SEPARATOR .'classes'. DIRECTORY_SEPARATOR .'modules'. DIRECTORY_SEPARATOR . 'other' . DIRECTORY_SEPARATOR . 'TimeTrexPaymentServices.class.php' );
		$tt_ps_api = new TimeTrexPaymentServices( $this->getPaymentServicesUserName(), $this->getPaymentServicesAPIKey() ); //Username and API Key

		return $tt_ps_api;
	}

	/**
	 * @return bool
	 */
	function checkPaymentServicesCredentials() {
		if ( PRODUCTION == FALSE ) {
			return TRUE;
		}

		if ( $this->getPaymentServicesStatus() == 10 AND $this->getPaymentServicesUserName() != '' AND $this->getPaymentServicesAPIKey() != '' ) {
			require_once( Environment::getBasePath() . DIRECTORY_SEPARATOR .'classes'. DIRECTORY_SEPARATOR .'modules'. DIRECTORY_SEPARATOR . 'other' . DIRECTORY_SEPARATOR . 'TimeTrexPaymentServices.class.php' );
			try {
				$tt_ps_api = $this->getPaymentServicesAPIObject();
				return $tt_ps_api->ping();
			} catch ( Exception $e ) {
				Debug::Text( 'ERROR! Unable to login to payment services... Username: '. $this->getPaymentServicesUserName() .' Exception: '. $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );

				return FALSE;
			}
		}

		Debug::Text( 'ERROR! Unable to login to payment services, either disabled, or username/API key not defined...', __FILE__, __LINE__, __METHOD__, 10 );
		return FALSE;
	}


	/**
	 * @return bool
	 */
	function getEnableAddRemittanceSource() {
		if ( isset($this->add_remittiance_source) ) {
			return $this->add_remittiance_source;
		}

		return FALSE;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableAddRemittanceSource( $bool) {
		$this->add_remittiance_source = $bool;

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function getEnableAddPresets() {
		if ( isset($this->add_presets) ) {
			return $this->add_presets;
		}

		return FALSE;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableAddPresets( $bool) {
		$this->add_presets = $bool;

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		//Remember if this is a new user for postSave()
		if ( $this->isNew( TRUE ) ) {
			$this->is_new = TRUE;
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preValidate() {
		//If they are outside US/Canada, automatically disable payment services, as its not supported anyways.
		if ( !in_array( $this->getCountry(), array( 'CA', 'US') ) ) {
			$this->setPaymentServicesStatus( 20 ); //20=Disabled
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );
		//Create default RemittanceAgencies for new legal entities.
		if ( isset($this->is_new) AND $this->is_new == TRUE ) {

			//Only create the presets when the legal entity is created by a user logged into the UI, not during upgrade or cron.
			//if ( TTUUID::isUUID($this->getCreatedBy()) AND $this->getCreatedBy() != TTUUID::getZeroID() AND $this->getCreatedBy() != TTUUID::getNotExistID() ) {
			if ( $this->getEnableAddRemittanceSource() == TRUE ) {
				Debug::Text('Adding default source account...', __FILE__, __LINE__, __METHOD__, 10);
				//Create a Check Remittance Source account for each legal entity by default.
				$rsaf = TTnew( 'RemittanceSourceAccountFactory' ); /** @var RemittanceSourceAccountFactory $rsaf */
				$rsaf->setCompany( $this->getCompany() );
				$rsaf->setLegalEntity( $this->getId() );
				$rsaf->setStatus( 10 ); //Enabled
				$rsaf->setCurrency( $this->getCompanyObject()->getDefaultCurrency() );
				$rsaf->setLastTransactionNumber( 0 );
				$rsaf->setType( 2000 ); //Check
				$rsaf->setCountry( $this->getCountry() );
				$rsaf->setName( TTi18n::getText( 'Checking' ) );
				$rsaf->setDescription( '' );
				$rsaf->setDataFormat( 10 );
				if ( $rsaf->isValid() ) {
					$rsaf->Save();
				} else {
					Debug::Text('ERROR: Unable to create default remittance source account!', __FILE__, __LINE__, __METHOD__, 10);
				}
			}

			if ( $this->getEnableAddPresets() == TRUE ) {
				Debug::Text('Adding presets...', __FILE__, __LINE__, __METHOD__, 10);

				$sp = TTNew( 'SetupPresets' ); /** @var SetupPresets $sp */
				$sp->setCompany( $this->getCompany() );
				$sp->setUser( $this->getCreatedBy() );

				$sp->RecurringHolidays( $this->getCountry(), NULL );
				$sp->RecurringHolidays( $this->getCountry(), $this->getProvince() );

				//$retval = $sp->PayrollRemittanceAgencys();
				$sp->PayrollRemittanceAgencys( $this->getCountry(), NULL, NULL, NULL, $this->getId() );
				$sp->PayrollRemittanceAgencys( $this->getCountry(), $this->getProvince(), NULL, NULL, $this->getId() );

				$sp->CompanyDeductions( NULL, NULL, NULL, NULL, $this->getId() );
				$sp->CompanyDeductions( $this->getCountry(), NULL, NULL, NULL, $this->getId() );
				$sp->CompanyDeductions( $this->getCountry(), $this->getProvince(), NULL, NULL, $this->getId() );
			}
		}


		if ( PRODUCTION == TRUE AND $this->getPaymentServicesStatus() == 10 ) { //10=Enabled
			//Send data to TimeTrex Payment Services.
			require_once( Environment::getBasePath() . DIRECTORY_SEPARATOR .'classes'. DIRECTORY_SEPARATOR .'modules'. DIRECTORY_SEPARATOR . 'other' . DIRECTORY_SEPARATOR . 'TimeTrexPaymentServices.class.php' );

			if ( $this->getPaymentServicesUserName() == '' OR $this->getPaymentServicesAPIKey() == '' ) {
				global $current_user;
				if ( isset( $current_user ) AND is_object( $current_user ) ) {
					try {
						//Setting up a new remittance source account, so we need to create the necessary data remotely.
						$tt_ps_api = new TimeTrexPaymentServices(); //No API key yet, as we need to create one still.
						$organization_retval = $tt_ps_api->createNewOrganization( $tt_ps_api->convertLegalEntityObjectToOrganizationArray( $this ) );
						Debug::Arr( $organization_retval, 'createNewOrganization(): ', __FILE__, __LINE__, __METHOD__, 10 );
						if ( TTUUID::isUUID( $organization_retval ) ) {
							$user_retval = $tt_ps_api->createNewUser( $tt_ps_api->convertUserObjectToUserArray( $current_user, $organization_retval ) );
							Debug::Arr( $user_retval, 'createNewUser(): ', __FILE__, __LINE__, __METHOD__, 10 );
							if ( is_array( $user_retval ) AND isset( $user_retval['user_name'] ) AND isset( $user_retval['api_key'] ) ) {
								Debug::Text( 'Creating new user success! Username: ' . $user_retval['user_name'] . ' API Key: ' . $user_retval['api_key'], __FILE__, __LINE__, __METHOD__, 10 );

								//Update UserName/API Key
								$lelf = TTNew( 'LegalEntityListFactory' ); /** @var LegalEntityListFactory $lelf */
								$lelf->getByIdAndCompanyId( $this->getId(), $this->getCompany() );
								if ( $lelf->getRecordCount() == 1 ) {
									$lef = $lelf->getCurrent();
									$lef->setPaymentServicesUserName( $user_retval['user_name'] );
									$lef->setPaymentServicesAPIKey( $user_retval['api_key'] );
									if ( $lef->isValid() ) {
										$lef->Save( FALSE );
									} else {
										Debug::Text( 'ERROR! Saving user_name/API Key failed!', __FILE__, __LINE__, __METHOD__, 10 );

										return FALSE;
									}
								}
							} else {
								Debug::Text( 'ERROR! Creating new user failed!', __FILE__, __LINE__, __METHOD__, 10 );

								return FALSE;
							}
						} else {
							Debug::Text( 'ERROR! Creating new organization failed!', __FILE__, __LINE__, __METHOD__, 10 );

							return FALSE;
						}
					} catch ( Exception $e ) {
						Debug::Text( 'ERROR! Unable to create new organization/user... Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );

						return FALSE;
					}
				} else {
					Debug::Text( 'No user currently logged in, skipping creating remote Remittance records...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				//Update any legal entity information.
				try {
					$tt_ps_api = $this->getPaymentServicesAPIObject();
					$retval = $tt_ps_api->setOrganization( $tt_ps_api->convertLegalEntityObjectToOrganizationArray( $this ) );
					if ( $retval === FALSE ) {
						Debug::Text( 'ERROR! Unable to upload organization data... (a)', __FILE__, __LINE__, __METHOD__, 10 );

						return FALSE;
					}
				} catch (Exception $e) {
					Debug::Text( 'ERROR! Unable to upload organization data... (b) Exception: '. $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
				}

				//Update any contact information for the user.
				global $current_user;
				if ( isset( $current_user ) AND is_object( $current_user ) ) {
					if ( $current_user->getWorkEmail() == $this->getPaymentServicesUserName() ) {
						try {
							$tt_ps_api = $this->getPaymentServicesAPIObject();
							$retval = $tt_ps_api->setUser( $tt_ps_api->convertUserObjectToUserArray( $current_user, $this->getId() ) );
							if ( $retval === FALSE ) {
								Debug::Text( 'ERROR! Unable to upload user data... (a)', __FILE__, __LINE__, __METHOD__, 10 );

								return FALSE;
							}
						} catch ( Exception $e ) {
							Debug::Text( 'ERROR! Unable to upload user data... (b) Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
						}
					} else {
						Debug::Text( 'WARNING: Current user is not the PaymentServices user, skipping updating information... Work Email: '. $current_user->getWorkEmail() .' PaymentServices User Name: '. $this->getPaymentServicesUserName(), __FILE__, __LINE__, __METHOD__, 10 );
					}
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
						case 'start_date':
							$this->setStartDate( TTDate::parseDateTime( $data['start_date'] ) );
							break;
						case 'end_date':
							$this->setEndDate( TTDate::parseDateTime( $data['end_date'] ) );
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
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'type':
						case 'status':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'classification_code_name':
							$data[$variable] = Option::getByKey( $this->getClassificationCode(), Misc::trimSortPrefix( $this->getOptions('classification_code') ) );
							$data[$variable] = str_replace('&nbsp;', '', $data[$variable]);
							break;
						case 'in_use':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'start_date':
							$data['start_date'] = TTDate::getAPIDate( 'DATE', $this->getStartDate() );
							break;
						case 'end_date':
							$data['end_date'] = TTDate::getAPIDate( 'DATE', $this->getEndDate() );
							break;
						case 'payment_services_api_key':
							$data[$variable] = $this->getSecurePaymentServicesAPIKey();
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Legal Entity') .': '. $this->getLegalName(), NULL, $this->getTable(), $this );
	}

}
?>
