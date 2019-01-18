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
			'in_use' => FALSE,
//			'tag' => 'Tag',
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
		$value = TTUUID::castUUID($value);
		if ( $value == '' ) {
			$value = TTUUID::getZeroID();
		}
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
		$value = trim($value);
		Debug::Text('Country: '. $this->getCountry() .' Province: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		return $this->setGenericDataValue( 'province', $value );
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
		$value = trim($value);
		return $this->setGenericDataValue( 'country', $value );
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
	 * @return bool
	 */
	function isLogoExists() {
		return file_exists( $this->getLogoFileName() );
	}

	/**
	 * @param null $legal_entity_id
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
		// BELOW: Validation code moved from set*() functions.
		//
		// Company
		$clf = TTnew( 'CompanyListFactory' );
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
		// Address1
		if ( $this->getAddress1() !== FALSE AND $this->getAddress1() != '' ) {
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
		if ( $this->getAddress2() !== FALSE AND $this->getAddress2() != '' ) {
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
		// City
		if ( $this->getCity() !== FALSE ) {
			$this->Validator->isRegEx(		'city',
											  $this->getCity(),
											  TTi18n::gettext('City contains invalid characters'),
											  $this->city_validator_regex
			);
			if ( $this->Validator->isError('city') == FALSE ) {
				$this->Validator->isLength(		'city',
												   $this->getCity(),
												   TTi18n::gettext('City name is too short or too long'),
												   2,
												   250
				);
			}
		}
		// Province
		$cf = TTnew( 'CompanyFactory' );
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
		// Country
		if ( $this->getCountry() !== FALSE ) {
			$this->Validator->inArrayKey(		'country',
												 $this->getCountry(),
												 TTi18n::gettext('Invalid Country'),
												 $cf->getOptions('country')
			);
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

		if ( $this->getDeleted() == TRUE ) {
			$ulf = TTnew( 'UserListFactory' );
			$ulf->getByLegalEntityIdAndCompanyId( $this->getId(), $this->getCompany() );
			if ( $ulf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
					FALSE,
					TTi18n::gettext('This legal entity is currently in use by employees') );
			}

			$praf = TTnew( 'PayrollRemittanceAgencyListFactory' );
			$praf->getByLegalEntityIdAndCompanyId( $this->getId(), $this->getCompany() );
			if ( $praf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
					FALSE,
					TTi18n::gettext('This legal entity is currently in use by payroll remittance agency') );
			}

			$rsaf = TTnew( 'RemittanceSourceAccountListFactory' );
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
	function postSave() {
		$this->removeCache( $this->getId() );
		//Create default RemittanceAgencies for new legal entities.
		if ( isset($this->is_new) AND $this->is_new == TRUE ) {

			//Only create the presets when the legal entity is created by a user logged into the UI, not during upgrade or cron.
			//if ( TTUUID::isUUID($this->getCreatedBy()) AND $this->getCreatedBy() != TTUUID::getZeroID() AND $this->getCreatedBy() != TTUUID::getNotExistID() ) {
			if ( $this->getEnableAddRemittanceSource() == TRUE ) {
				Debug::Text('Adding default source account...', __FILE__, __LINE__, __METHOD__, 10);
				//Create a Check Remittance Source account for each legal entity by default.
				$rsaf = TTnew( 'RemittanceSourceAccountFactory' );
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
				}
			}

			if ( $this->getEnableAddPresets() == TRUE ) {
				Debug::Text('Adding presets...', __FILE__, __LINE__, __METHOD__, 10);

				$sp = TTNew( 'SetupPresets' );
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
