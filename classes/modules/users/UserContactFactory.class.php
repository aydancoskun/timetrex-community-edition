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
 * @package Modules\Users
*/

class UserContactFactory extends Factory {
	protected $table = 'user_contact';
	protected $pk_sequence_name = 'user_contact_id_seq'; //PK Sequence name

	protected $tmp_data = NULL;
	protected $user_obj = NULL;
	protected $name_validator_regex = '/^[a-zA-Z- \.\'|\x{0080}-\x{FFFF}]{1,250}$/iu';
	protected $address_validator_regex = '/^[a-zA-Z0-9-,_\/\.\'#\ |\x{0080}-\x{FFFF}]{1,250}$/iu';
	protected $city_validator_regex = '/^[a-zA-Z0-9-,_\.\'#\ |\x{0080}-\x{FFFF}]{1,250}$/iu';

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = NULL ) {

		$retval = NULL;
		switch( $name ) {
			case 'status':
				$retval = array(
										10 => TTi18n::gettext('ENABLED'),
										20 => TTi18n::gettext('DISABLED'),
									);
				break;
			case 'type':
				$retval = array(
										10 => TTi18n::gettext('Spouse/Partner'),
										20 => TTi18n::gettext('Parent/Guardian'),
										30 => TTi18n::gettext('Sibling'),
										40 => TTi18n::gettext('Child'),
										50 => TTi18n::gettext('Relative'),
										60 => TTi18n::gettext('Dependant'),
										70 => TTi18n::gettext('Emergency Contact'),
									);
				break;
			case 'sex':
				$retval = array(
										5 => TTi18n::gettext('Unspecified'),
										10 => TTi18n::gettext('Male'),
										20 => TTi18n::gettext('Female'),
									);
				break;
			case 'columns':
				$retval = array(
										'-1090-employee_first_name' => TTi18n::gettext('Employee First Name'),
										//'-1100-employee_middle_name' => TTi18n::gettext('Employee Middle Name'),
										'-1110-employee_last_name' => TTi18n::gettext('Employee Last Name'),

										'-1010-title' => TTi18n::gettext('Employee Title'),
										'-1099-user_group' => TTi18n::gettext('Employee Group'),
										'-1100-default_branch' => TTi18n::gettext('Employee Branch'),
										'-1030-default_department' => TTi18n::gettext('Employee Department'),

										'-1060-first_name' => TTi18n::gettext('First Name'),
										'-1070-middle_name' => TTi18n::gettext('Middle Name'),
										'-1080-last_name' => TTi18n::gettext('Last Name'),
										'-1020-status' => TTi18n::gettext('Status'),
										'-1050-type' => TTi18n::getText('Type'),

										'-1120-sex' => TTi18n::gettext('Gender'),
										'-1125-ethnic_group' => TTi18n::gettext('Ethnic Group'),

										'-1130-address1' => TTi18n::gettext('Address 1'),
										'-1140-address2' => TTi18n::gettext('Address 2'),

										'-1150-city' => TTi18n::gettext('City'),
										'-1160-province' => TTi18n::gettext('Province/State'),
										'-1170-country' => TTi18n::gettext('Country'),
										'-1180-postal_code' => TTi18n::gettext('Postal Code'),
										'-1190-work_phone' => TTi18n::gettext('Work Phone'),
										'-1191-work_phone_ext' => TTi18n::gettext('Work Phone Ext'),
										'-1200-home_phone' => TTi18n::gettext('Home Phone'),
										'-1210-mobile_phone' => TTi18n::gettext('Mobile Phone'),
										'-1220-fax_phone' => TTi18n::gettext('Fax Phone'),
										'-1230-home_email' => TTi18n::gettext('Home Email'),
										'-1240-work_email' => TTi18n::gettext('Work Email'),
										'-1250-birth_date' => TTi18n::gettext('Birth Date'),
										'-1280-sin' => TTi18n::gettext('SIN/SSN'),
										'-1290-note' => TTi18n::gettext('Note'),
										'-1300-tag' => TTi18n::gettext('Tags'),
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
								//'status',
								'employee_first_name',
								'employee_last_name',
								'title',
								'user_group',
								'default_branch',
								'default_department',
								'type',
								'first_name',
								'last_name',
								'home_phone',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'sin'
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
										'user_id' => 'User',
										'status_id' => 'Status',
										'status' => FALSE,
										'type_id' => 'Type',
										'type' => FALSE,
										'employee_first_name' => FALSE,
										'employee_last_name' => FALSE,
										'default_branch' => FALSE,
										'default_department' => FALSE,
										'user_group' => FALSE,
										'title' => FALSE,
										'first_name' => 'FirstName',
										'first_name_metaphone' => 'FirstNameMetaphone',
										'middle_name' => 'MiddleName',
										'last_name' => 'LastName',
										'last_name_metaphone' => 'LastNameMetaphone',
										'sex_id' => 'Sex',
										'sex' => FALSE,
										'ethnic_group_id' => 'EthnicGroup',
										'ethnic_group' => FALSE,
										'address1' => 'Address1',
										'address2' => 'Address2',
										'city' => 'City',
										'country' => 'Country',
										'province' => 'Province',
										'postal_code' => 'PostalCode',
										'work_phone' => 'WorkPhone',
										'work_phone_ext' => 'WorkPhoneExt',
										'home_phone' => 'HomePhone',
										'mobile_phone' => 'MobilePhone',
										'fax_phone' => 'FaxPhone',
										'home_email' => 'HomeEmail',
										'work_email' => 'WorkEmail',
										'birth_date' => 'BirthDate',
										'sin' => 'SIN',
										'note' => 'Note',
										'tag' => 'Tag',
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
		return $this->getGenericDataValue( 'user_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setUser( $value ) {
		$value = trim($value);
		$value = TTUUID::castUUID( $value );
		if ( $value == '' ) {
			$value = TTUUID::getZeroID();
		}
		return $this->setGenericDataValue( 'user_id', $value );
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
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getType() {
		if ( isset($this->data['type_id']) ) {
			return $this->data['type_id'];
		}

		return FALSE;
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
	function getFirstName() {
		return $this->getGenericDataValue( 'first_name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setFirstName( $value ) {
		$value = trim($value);
		$this->setFirstNameMetaphone( $value );
		return $this->setGenericDataValue( 'first_name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getFirstNameMetaphone() {
		return $this->getGenericDataValue( 'first_name_metaphone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setFirstNameMetaphone( $value ) {
		$value = metaphone( trim($value) );

		if ( $value != '' ) {
			$this->setGenericDataValue( 'first_name_metaphone', $value );

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool|mixed
	 */
	function getMiddleName() {
		return $this->getGenericDataValue( 'middle_name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMiddleName( $value ) {
		$value = trim($value);
		return $this->setGenericDataValue( 'middle_name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getLastName() {
		return $this->getGenericDataValue( 'last_name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLastName( $value ) {
		$value = trim($value);
		$this->setLastNameMetaphone( $value );
		return $this->setGenericDataValue( 'last_name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getLastNameMetaphone() {
		return $this->getGenericDataValue( 'last_name_metaphone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLastNameMetaphone( $value ) {
		$value = metaphone( trim($value) );

		if ( $value != '' ) {
			$this->setGenericDataValue( 'last_name_metaphone', $value );

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getMiddleInitial() {
		if ( $this->getMiddleName() != '' ) {
			$middle_name = $this->getMiddleName();
			return $middle_name[0];
		}

		return FALSE;
	}

	/**
	 * @param bool $reverse
	 * @param bool $include_middle
	 * @return bool|string
	 */
	function getFullName( $reverse = FALSE, $include_middle = TRUE ) {
		return Misc::getFullName($this->getFirstName(), $this->getMiddleInitial(), $this->getLastName(), $reverse, $include_middle);
	}

	/**
	 * @return bool|int
	 */
	function getSex() {
		return $this->getGenericDataValue( 'sex_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSex( $value ) {
		$value = (int)trim($value);
		return $this->setGenericDataValue( 'sex_id', $value );
	}

	/**
	 * @return bool
	 */
	function getEthnicGroup() {
		return $this->getGenericDataValue( 'ethnic_group_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setEthnicGroup( $value ) {
		$value = TTUUID::castUUID($value);
		if ( $value == '' ) {
			$value = TTUUID::getZeroID();
		}
		return $this->setGenericDataValue( 'ethnic_group_id', $value );
	}

	/**
	 * @return bool
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
	 * @return bool
	 */
	function getAddress2() {
		return $this->getGenericDataValue( 'address2' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAddress2( $value ) {
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
	function setCity( $value ) {
		$value = trim($value);
		return $this->setGenericDataValue( 'city', $value );
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
	 * @return bool|mixed
	 */
	function getProvince() {
		return $this->getGenericDataValue( 'province' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setProvince( $value ) {
		$value = trim($value);
		//Debug::Text('Country: '. $this->getCountry() .' Province: '. $province, __FILE__, __LINE__, __METHOD__, 10);
		//If country isn't set yet, accept the value and re-validate on save.
		return $this->setGenericDataValue( 'province', $value );
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
	function setPostalCode( $value ) {
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
	function setWorkPhone( $value ) {
		$value = trim($value);
		return $this->setGenericDataValue( 'work_phone', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getWorkPhoneExt() {
		return $this->getGenericDataValue( 'work_phone_ext' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setWorkPhoneExt( $value ) {
		$value = $this->Validator->stripNonNumeric( trim($value) );
		return $this->setGenericDataValue( 'work_phone_ext', $value );

	}

	/**
	 * @return bool|mixed
	 */
	function getHomePhone() {
		return $this->getGenericDataValue( 'home_phone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setHomePhone( $value ) {
		$value = trim($value);
		return $this->setGenericDataValue( 'home_phone', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getMobilePhone() {
		return $this->getGenericDataValue( 'mobile_phone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMobilePhone( $value ) {
		$value = trim($value);
		return $this->setGenericDataValue( 'mobile_phone', $value );
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
	function setFaxPhone( $value ) {
		$value = trim($value);
		return $this->setGenericDataValue( 'fax_phone', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getHomeEmail() {
		return $this->getGenericDataValue( 'home_email' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setHomeEmail( $value ) {
		$value = trim($value);
		return $this->setGenericDataValue( 'home_email', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getWorkEmail() {
		return $this->getGenericDataValue( 'work_email' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setWorkEmail( $value ) {
		$value = trim($value);
		return $this->setGenericDataValue( 'work_email', $value );
	}


	/**
	 * @return bool|mixed
	 */
	function getBirthDate() {
		return $this->getGenericDataValue( 'birth_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setBirthDate( $value ) {
		//Allow for negative epochs, for birthdates less than 1960's
		return $this->setGenericDataValue( 'birth_date', ( $value != 0 AND $value != '' ) ? TTDate::getMiddleDayEpoch( $value ) : '' ); //Allow blank birthdate.
	}

	/**
	 * @param null $sin
	 * @return bool|string
	 */
	function getSecureSIN( $sin = NULL ) {
		if ( $sin == '' ) {
			$sin = $this->getSIN();
		}
		if ( $sin != '' ) {
			return Misc::censorString( $sin, 'X', NULL, 1, 4, 4 );
		}

		return FALSE;
	}

	/**
	 * @return bool|mixed
	 */
	function getSIN() {
		return $this->getGenericDataValue( 'sin' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSIN( $value ) {
		//If *'s are in the SIN number, skip setting it
		//This allows them to change other data without seeing the SIN number.
		if ( stripos( $value, 'X') !== FALSE ) {
			return FALSE;
		}

		$value = $this->Validator->stripNonNumeric( trim($value) );
		if ( $value != '' ) {
			return $this->setGenericDataValue( 'sin', $value );
		}
	}

	/**
	 * @return bool|mixed
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
	 * @return bool|string
	 */
	function getTag() {
		//Check to see if any temporary data is set for the tags, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		$value = $this->getGenericTempDataValue( 'tags' );
		if ( $value !== FALSE ) {
			return $value;
		} elseif ( is_object($this->getUserObject())
				AND TTUUID::isUUID( $this->getUserObject()->getCompany() ) AND $this->getUserObject()->getCompany() != TTUUID::getZeroID() AND $this->getUserObject()->getCompany() != TTUUID::getNotExistID()
				AND	TTUUID::isUUID( $this->getID() ) AND $this->getID() != TTUUID::getZeroID() AND $this->getID() != TTUUID::getNotExistID() ) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getUserObject()->getCompany(), 230, $this->getID() );
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTag( $value ) {
		$value = trim($value);
		//Save the tags in temporary memory to be committed in postSave()
		return $this->setGenericTempDataValue( 'tags', $value );
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = TRUE ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Employee
		if ( $this->getUser() !== FALSE ) {
			$ulf = TTnew( 'UserListFactory' );
			$this->Validator->isResultSetWithRows(	'user_id',
															$ulf->getByID($this->getUser()),
															TTi18n::gettext('Invalid Employee')
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
		// Type
		if ( $this->getType() !== FALSE ) {
			$this->Validator->inArrayKey( 'type_id',
												$this->getType(),
												TTi18n::gettext('Incorrect Type'),
												$this->getOptions('type')
											);
		}
		// First name
		if ( $this->getFirstName() !== FALSE ) {
			$this->Validator->isRegEx(		'first_name',
													$this->getFirstName(),
													TTi18n::gettext('First name contains invalid characters'),
													$this->name_validator_regex
												);
			if ( $this->Validator->isError('first_name') == FALSE ) {
				$this->Validator->isLength(		'first_name',
														$this->getFirstName(),
														TTi18n::gettext('First name is too short or too long'),
														2,
														50
													);
			}
		}
		// Middle name
		if ( $this->getMiddleName() != '' ) {
			$this->Validator->isRegEx(		'middle_name',
													$this->getMiddleName(),
													TTi18n::gettext('Middle name contains invalid characters'),
													$this->name_validator_regex
												);
			if ( $this->Validator->isError('middle_name') == FALSE ) {
				$this->Validator->isLength(		'middle_name',
														$this->getMiddleName(),
														TTi18n::gettext('Middle name is too short or too long'),
														1,
														50
													);
			}
		}
		// Last name
		if ( $this->getLastName() !== FALSE ) {
			$this->Validator->isRegEx(		'last_name',
													$this->getLastName(),
													TTi18n::gettext('Last name contains invalid characters'),
													$this->name_validator_regex
												);
			if ( $this->Validator->isError('last_name') == FALSE ) {
				$this->Validator->isLength(		'last_name',
														$this->getLastName(),
														TTi18n::gettext('Last name is too short or too long'),
														2,
														50
													);
			}
		}
		// gender
		if ( $this->getSex() !== FALSE ) {
			$this->Validator->inArrayKey(	'sex_id',
													$this->getSex(),
													TTi18n::gettext('Invalid gender'),
													$this->getOptions('sex')
												);
		}
		// Ethnic Group
		if ( $this->getEthnicGroup() !== FALSE AND $this->getEthnicGroup() != TTUUID::getZeroID() ) {
			$eglf = TTnew( 'EthnicGroupListFactory' );
			$this->Validator->isResultSetWithRows( 'ethnic_group',
														$eglf->getById($this->getEthnicGroup()),
														TTi18n::gettext('Ethnic Group is invalid')
													);
		}
		// Address1
		if ( $this->getAddress1() != '' ) {
			$this->Validator->isRegEx(		'address1',
													$this->getAddress1(),
													TTi18n::gettext('Address1 contains invalid characters'),
													$this->address_validator_regex
												);
			if ( $this->Validator->isError('address1') == FALSE ) {
				$this->Validator->isLength(		'address1',
														$this->getAddress1(),
														TTi18n::gettext('Address1 is too short or too long'),
														2,
														250
													);
			}
		}
		// Address2
		if ( $this->getAddress2() != '' ) {
			$this->Validator->isRegEx(		'address2',
													$this->getAddress2(),
													TTi18n::gettext('Address2 contains invalid characters'),
													$this->address_validator_regex
												);
			if ( $this->Validator->isError('address2') == FALSE ) {
				$this->Validator->isLength(		'address2',
													$this->getAddress2(),
													TTi18n::gettext('Address2 is too short or too long'),
													2,
													250
												);
			}
		}
		// City
		if ( $this->getCity() != '' ) {
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
		// Country
		if ( $this->getCountry() !== FALSE ) {
			$cf = TTnew( 'CompanyFactory' );
			$this->Validator->inArrayKey(		'country',
														$this->getCountry(),
														TTi18n::gettext('Invalid Country'),
														$cf->getOptions('country')
													);
			// Province/State
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
			unset( $options, $options_arr );
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
		if ( $this->getWorkPhone() != '' ) {
			$this->Validator->isPhoneNumber(		'work_phone',
															$this->getWorkPhone(),
															TTi18n::gettext('Work phone number is invalid')
														);
		}
		// Work phone number extension
		if ( $this->getWorkPhoneExt() != '' ) {
			$this->Validator->isLength(		'work_phone_ext',
													$this->getWorkPhoneExt(),
													TTi18n::gettext('Work phone number extension is too short or too long'),
													2,
													10
												);
		}
		// Home phone number
		if ( $this->getHomePhone() != '' ) {
			$this->Validator->isPhoneNumber(		'home_phone',
															$this->getHomePhone(),
															TTi18n::gettext('Home phone number is invalid')
														);
		}
		// Mobile phone number
		if ( $this->getMobilePhone() != '' ) {
			$this->Validator->isPhoneNumber(	'mobile_phone',
														$this->getMobilePhone(),
														TTi18n::gettext('Mobile phone number is invalid')
													);
		}
		// Fax phone number
		if ( $this->getFaxPhone() != '' ) {
			$this->Validator->isPhoneNumber(	'fax_phone',
														$this->getFaxPhone(),
														TTi18n::gettext('Fax phone number is invalid')
													);
		}
		// Home Email address
		if ( $this->getHomeEmail() != '' ) {
			$error_threshold = 7; //No DNS checks.
			if ( DEPLOYMENT_ON_DEMAND === TRUE ) {
				$error_threshold = 0; //DNS checks on email address.
			}
			$this->Validator->isEmailAdvanced(	'home_email',
														$this->getHomeEmail(),
														TTi18n::gettext('Home Email address is invalid'),
														$error_threshold
													);
		}
		// Work Email address
		if ( $this->getWorkEmail() != '' ) {
			$error_threshold = 7; //No DNS checks.
			if ( DEPLOYMENT_ON_DEMAND === TRUE ) {
				$error_threshold = 0; //DNS checks on email address.
			}
			$this->Validator->isEmailAdvanced(	'work_email',
														$this->getWorkEmail(),
														TTi18n::gettext('Work Email address is invalid'),
														$error_threshold
													);
		}
		// Birth date
		if ( $this->getBirthDate() != '' ) {
			$this->Validator->isDate(	'birth_date',
												$this->getBirthDate(),
												TTi18n::gettext('Birth date is invalid, try specifying the year with four digits')
											);
			if ( $this->Validator->isError('birth_date') == FALSE ) {
				$this->Validator->isTRUE(	'birth_date',
					( TTDate::getMiddleDayEpoch( $this->getBirthDate() ) <= TTDate::getMiddleDayEpoch( ( time() + (365 * 86400) ) ) ) ? TRUE : FALSE,
					TTi18n::gettext('Birth date can not be more than one year in the future')
				);
			}
		}
		// SIN
		if ( $this->getSIN() != '' ) {
			$this->Validator->isLength(		'sin',
													$this->getSIN(),
													TTi18n::gettext('SIN is invalid'),
													6,
													20
												);
		}
		// Note
		if ( $this->getNote() != '' ) {
			$this->Validator->isLength(		'note',
													$this->getNote(),
													TTi18n::gettext('Note is too long'),
													1,
													2048
												);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		//When doing a mass edit of employees, user name is never specified, so we need to avoid this validation issue.

		//Re-validate the province just in case the country was set AFTER the province.
		//$this->setProvince( $this->getProvince() );
		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preSave() {

		if ( $this->getStatus() == FALSE ) {
			$this->setStatus( 10 ); //ENABLE
		}

		if ( $this->getSex() == FALSE ) {
			$this->setSex( 5 ); //UnSpecified
		}

		if ( $this->getEthnicGroup() == FALSE ) {
			$this->setEthnicGroup( TTUUID::getZeroID() );
		}

		//Remember if this is a new user for postSave()
		if ( $this->isNew() ) {
			$this->is_new = TRUE;
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postSave( ) {
		$this->removeCache( $this->getId() );

		if ( $this->getDeleted() == FALSE ) {
			Debug::text('Setting Tags...', __FILE__, __LINE__, __METHOD__, 10);
			CompanyGenericTagMapFactory::setTags( $this->getUserObject()->getCompany(), 230, $this->getID(), $this->getTag() );
		}

		return TRUE;
	}

	/**
	 * @return bool|string
	 */
	function getMapURL() {
		return Misc::getMapURL( $this->getAddress1(), $this->getAddress2(), $this->getCity(), $this->getProvince(), $this->getPostalCode(), $this->getCountry() );
	}

	//Support setting created_by, updated_by especially for importing data.
	//Make sure data is set based on the getVariableToFunctionMap order.
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
						case 'birth_date':
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
	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE ) {
		$data = array();
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'employee_first_name':
						case 'employee_last_name':
						case 'title':
						case 'user_group':
						case 'ethnic_group':
						case 'default_branch':
						case 'default_department':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'full_name':
							$data[$variable] = $this->getFullName(TRUE);
							break;
						case 'status':
						case 'type':
						case 'sex':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'sin':
							$data[$variable] = $this->getSecureSIN();
							break;
						case 'birth_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
							}
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
				unset($function);
			}
			$this->getPermissionColumns( $data, $this->getUser(), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Employee Contact').': '. $this->getFullName( FALSE, TRUE ), NULL, $this->getTable(), $this );
	}
}
?>
