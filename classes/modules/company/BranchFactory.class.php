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
class BranchFactory extends Factory {
	protected $table = 'branch';
	protected $pk_sequence_name = 'branch_id_seq'; //PK Sequence name

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
										20 => TTi18n::gettext('DISABLED')
									);
				break;
			case 'columns':
				$retval = array(
										'-1010-status' => TTi18n::gettext('Status'),
										'-1020-manual_id' => TTi18n::gettext('Code'),
										'-1030-name' => TTi18n::gettext('Name'),

										'-1140-address1' => TTi18n::gettext('Address 1'),
										'-1150-address2' => TTi18n::gettext('Address 2'),
										'-1160-city' => TTi18n::gettext('City'),
										'-1170-province' => TTi18n::gettext('Province/State'),
										'-1180-country' => TTi18n::gettext('Country'),
										'-1190-postal_code' => TTi18n::gettext('Postal Code'),
										'-1200-work_phone' => TTi18n::gettext('Work Phone'),
										'-1210-fax_phone' => TTi18n::gettext('Fax Phone'),

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
								'manual_id',
								'name',
								'city',
								'province',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name',
								'manual_id'
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
										'manual_id' => 'ManualID',
										'name' => 'Name',
										'name_metaphone' => 'NameMetaphone',
										'address1' => 'Address1',
										'address2' => 'Address2',
										'city' => 'City',
										'country' => 'Country',
										'province' => 'Province',
										'postal_code' => 'PostalCode',
										'work_phone' => 'WorkPhone',
										'fax_phone' => 'FaxPhone',
										'other_id1' => 'OtherID1',
										'other_id2' => 'OtherID2',
										'other_id3' => 'OtherID3',
										'other_id4' => 'OtherID4',
										'other_id5' => 'OtherID5',
										'longitude' => 'Longitude',
										'latitude' => 'Latitude',
										'geo_fence_ids' => 'GEOFenceIds',
										'tag' => 'Tag',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
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
	 * @return bool|mixed
	 */
	function getStatus() {
		return $this->getGenericDataValue( 'status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStatus( $value) {
		$value = (int)trim( $value);
		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function isUniqueManualID( $id) {
		if ( $this->getCompany() == FALSE ) {
			return FALSE;
		}

		$ph = array(
					'manual_id' => (int)$id,
					'company_id' =>	TTUUID::castUUID( $this->getCompany() ),
					);

		$query = 'select id from '. $this->getTable() .' where manual_id = ? AND company_id = ? AND deleted=0';
		$id = $this->db->GetOne($query, $ph);
		Debug::Arr($id, 'Unique Code: '. $id, __FILE__, __LINE__, __METHOD__, 10);

		if ( $id === FALSE ) {
			return TRUE;
		} else {
			if ($id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @param string $company_id UUID
	 * @return int
	 */
	function getNextAvailableManualId( $company_id = NULL ) {
		global $current_company;

		if ( $company_id == '' AND is_object($current_company) ) {
			$company_id = $current_company->getId();
		} elseif ( $company_id == '' AND isset($this) AND is_object($this) ) {
			$company_id = $this->getCompany();
		}

		$blf = TTnew( 'BranchListFactory' ); /** @var BranchListFactory $blf */
		$blf->getHighestManualIDByCompanyId( $company_id );
		if ( $blf->getRecordCount() > 0 ) {
			$next_available_manual_id = ($blf->getCurrent()->getManualId() + 1);
		} else {
			$next_available_manual_id = 1;
		}

		return $next_available_manual_id;
	}

	/**
	 * @return bool|mixed
	 */
	function getManualID() {
		return $this->getGenericDataValue( 'manual_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setManualID( $value) {
		$value = $this->Validator->stripNonNumeric( trim($value) );
		return $this->setGenericDataValue( 'manual_id', $value );
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
					'company_id' => TTUUID::castUUID($this->getCompany()),
					'name' => TTi18n::strtolower($name),
					);

		$query = 'select id from '. $this->getTable() .'
					where company_id = ?
						AND lower(name) = ?
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
	function getName() {
		return $this->getGenericDataValue( 'name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setName( $value) {
		$value = trim( $value);
		$this->setNameMetaphone( $value );
		return $this->setGenericDataValue( 'name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getNameMetaphone() {
		return $this->getGenericDataValue( 'name_metaphone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setNameMetaphone( $value) {
		$value = metaphone( trim($value) );
		return $this->setGenericDataValue( 'name_metaphone', $value );
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
	function setAddress1( $value) {
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
		$value = strtoupper( $this->Validator->stripSpaces( $value ) );
		return $this->setGenericDataValue( 'postal_code', $value );
	}

	/**
	 * @return bool|float
	 */
	function getLongitude() {
		return $this->getGenericDataValue( 'longitude' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLongitude( $value ) {
		if ( is_numeric($value) ) {
			$value = Misc::removeTrailingZeros( round( (float)$value, 6 ) ); //Always use 6 decimal places as that is to 0.11m accuracy, this also prevents audit logging 0 vs 0.000000000 -- Don't use parseFloat() here as it should never be a user input value with commas as decimal symbols.
		} else {
			$value = NULL; //Allow $value=NULL so the coordinates can be cleared. Also make sure if FALSE is passed in here we assume NULL so it doesn't get cast to integer and saved in DB.
		}

		return $this->setGenericDataValue( 'longitude', $value );
	}

	/**
	 * @return bool|float
	 */
	function getLatitude() {
		return $this->getGenericDataValue( 'latitude' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLatitude( $value ) {
		if ( is_numeric($value) ) {
			$value = Misc::removeTrailingZeros( round( (float)$value, 6 ) ); //Always use 6 decimal places as that is to 0.11m accuracy, this also prevents audit logging 0 vs 0.000000000 -- Don't use parseFloat() here as it should never be a user input value with commas as decimal symbols.
		} else {
			$value = NULL; //Allow $value=NULL so the coordinates can be cleared. Also make sure if FALSE is passed in here we assume NULL so it doesn't get cast to integer and saved in DB.
		}

		return $this->setGenericDataValue( 'latitude', $value );
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
	 * @return bool|mixed
	 */
	function getOtherID1() {
		return $this->getGenericDataValue( 'other_id1' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOtherID1( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'other_id1', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getOtherID2() {
		return $this->getGenericDataValue( 'other_id2' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOtherID2( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'other_id2', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getOtherID3() {
		return $this->getGenericDataValue( 'other_id3' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOtherID3( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'other_id3', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getOtherID4() {
		return $this->getGenericDataValue( 'other_id4' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOtherID4( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'other_id4', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getOtherID5() {
		return $this->getGenericDataValue( 'other_id5' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOtherID5( $value) {
		$value = trim($value);
		return $this->setGenericDataValue( 'other_id5', $value );
	}

	/**
	 * @return array|bool
	 */
	function getGEOFenceIds() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 4000, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setGEOFenceIds( $ids) {
		Debug::text('Setting GEO Fence IDs...', __FILE__, __LINE__, __METHOD__, 10);
		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 4000, $this->getID(), (array)$ids );
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
		} elseif ( TTUUID::isUUID( $this->getCompany() ) AND $this->getCompany() != TTUUID::getZeroID() AND $this->getCompany() != TTUUID::getNotExistID()
				AND TTUUID::isUUID($this->getID()) AND $this->getID() != TTUUID::getZeroID() AND $this->getID() != TTUUID::getNotExistID() ) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 110, $this->getID() );
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

		//Status
		if ( $this->getStatus() !== FALSE ) {
			$this->Validator->inArrayKey( 'status',
													$this->getStatus(),
													TTi18n::gettext( 'Incorrect Status' ),
													$this->getOptions( 'status' )
												);
		}

		//Manual ID
		if ( $this->getManualID() !== FALSE ) {
			$this->Validator->isNumeric( 'manual_id',
												$this->getManualID(),
												TTi18n::gettext( 'Code is invalid' )
											);
			if ( $this->Validator->isError('manual_id') == FALSE ) {
				$this->Validator->isLength( 'manual_id',
													$this->getManualID(),
													TTi18n::gettext( 'Code has too many digits' ),
													0,
													10 );
			}
			if ( $this->Validator->isError('manual_id') == FALSE ) {
				$this->Validator->isTrue( 'manual_id',
											( $this->Validator->stripNon32bitInteger( $this->getManualID() ) === 0 ) ? FALSE : TRUE,
											TTi18n::gettext( 'Code is invalid, maximum value exceeded' ) );
			}
			if ( $this->Validator->isError('manual_id') == FALSE ) {
				$this->Validator->isTrue( 'manual_id',
											$this->isUniqueManualID( $this->getManualID() ),
											TTi18n::gettext( 'Code is already in use, please enter a different one' ) );
			}
		}

		//Name
		if ( $this->getName() !== FALSE ) {
			$this->Validator->isLength( 'name',
											$this->getName(),
											TTi18n::gettext( 'Name is too short or too long' ),
											2,
											100 );
			if ( $this->Validator->isError('name') == FALSE ) {
				$this->Validator->isTrue( 'name',
												$this->isUniqueName( $this->getName() ),
												TTi18n::gettext( 'Branch name already exists' ) );
			}
		}

		//Address1
		if ( $this->getAddress1() != '' ) {
			$this->Validator->isRegEx( 'address1',
									   $this->getAddress1(),
									   TTi18n::gettext( 'Address1 contains invalid characters' ),
									   $this->address_validator_regex );
			if ( $this->Validator->isError('address1') == FALSE ) {
				$this->Validator->isLength( 'address1',
											$this->getAddress1(),
											TTi18n::gettext( 'Address1 is too short or too long' ),
											2,
											250 );
			}
		}

		//Address2
		if ( $this->getAddress2() != '' ) {
			$this->Validator->isRegEx( 'address2',
									   $this->getAddress2(),
									   TTi18n::gettext( 'Address2 contains invalid characters' ),
									   $this->address_validator_regex );
			if ( $this->Validator->isError('address2') == FALSE ) {
				$this->Validator->isLength( 'address2',
											$this->getAddress2(),
											TTi18n::gettext( 'Address2 is too short or too long' ),
											2,
											250 );
			}
		}

		//City
		if ( $this->getCity() !== FALSE ) {
			$this->Validator->isRegEx( 'city',
											$this->getCity(),
											TTi18n::gettext( 'City contains invalid characters' ),
											$this->city_validator_regex );
			if ( $this->Validator->isError('city') == FALSE ) {
				$this->Validator->isLength( 'city',
												$this->getCity(),
												TTi18n::gettext( 'City name is too short or too long' ),
												2,
												250 );
			}
		}

		//Province
		if ( $this->getProvince() !== FALSE ) {
			$cf = TTnew( 'CompanyFactory' ); /** @var CompanyFactory $cf */
			$options_arr = $cf->getOptions( 'province' );
			if ( isset( $options_arr[$this->getCountry()] ) ) {
				$options = $options_arr[$this->getCountry()];
			} else {
				$options = array();
			}
			$this->Validator->inArrayKey( 'province',
												$this->getProvince(),
												TTi18n::gettext( 'Invalid Province/State' ),
												$options );
			unset( $options, $options_arr );
		}

		//Country
		if ( $this->getCountry() !== FALSE ) {
			$this->Validator->inArrayKey( 'country',
											$this->getCountry(),
											TTi18n::gettext( 'Invalid Country' ),
											$cf->getOptions( 'country' ) );
		}

		//Postal Code
		if ( $this->getPostalCode() != '' ) {
			$this->Validator->isPostalCode( 'postal_code',
											$this->getPostalCode(),
											TTi18n::gettext( 'Postal/ZIP Code contains invalid characters, invalid format, or does not match Province/State' ),
											$this->getCountry(), $this->getProvince() );
			if ( $this->Validator->isError('postal_code') == FALSE ) {
				$this->Validator->isLength( 'postal_code',
											$this->getPostalCode(),
											TTi18n::gettext( 'Postal/ZIP Code is too short or too long' ),
											1,
											10 );
			}
		}

		//Longitude
		if ( $this->getLongitude() != 0 ) {
			$this->Validator->isFloat( 'longitude',
									   $this->getLongitude(),
									   TTi18n::gettext( 'Longitude is invalid' ) );
		}

		//Latitude
		if ( $this->getLongitude() != 0 ) {
			$this->Validator->isFloat( 'latitude',
									   $this->getLatitude(),
									   TTi18n::gettext( 'Latitude is invalid' ) );
		}

		//Work Phone
		if ( $this->getWorkPhone() != '' ) {
			$this->Validator->isPhoneNumber( 'work_phone',
											 $this->getWorkPhone(),
											 TTi18n::gettext( 'Work phone number is invalid' ) );
		}

		//Fax Phone
		if ( $this->getFaxPhone() != '' ) {
			$this->Validator->isPhoneNumber( 'fax_phone',
											 $this->getFaxPhone(),
											 TTi18n::gettext( 'Fax phone number is invalid' ) );
		}

		//OtherIDs 1-5
		if ( $this->getOtherID1() != '' ) {
			$this->Validator->isLength( 'other_id1',
										$this->getOtherID1(),
										TTi18n::gettext( 'Other ID 1 is invalid' ),
										1, 255 );
		}
		if ( $this->getOtherID2() != '' ) {
			$this->Validator->isLength( 'other_id2',
										$this->getOtherID2(),
										TTi18n::gettext( 'Other ID 2 is invalid' ),
										1, 255 );
		}
		if ( $this->getOtherID3() != '' ) {
			$this->Validator->isLength( 'other_id3',
										$this->getOtherID3(),
										TTi18n::gettext( 'Other ID 3 is invalid' ),
										1, 255 );
		}
		if ( $this->getOtherID4() != '' ) {
			$this->Validator->isLength( 'other_id4',
										$this->getOtherID4(),
										TTi18n::gettext( 'Other ID 4 is invalid' ),
										1, 255 );
		}
		if ( $this->getOtherID5() != '' ) {
			$this->Validator->isLength( 'other_id5',
										$this->getOtherID5(),
										TTi18n::gettext( 'Other ID 5 is invalid' ),
										1, 255 );
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
		if ( $this->getStatus() == FALSE ) {
			$this->setStatus(10);
		}

		if ( $this->getManualID() == FALSE ) {
			$this->setManualID( $this->getNextAvailableManualId( $this->getCompany() ) );
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$data_diff = $this->getDataDifferences();
		$this->removeCache( $this->getId() );

		if ( $this->getDeleted() == FALSE ) {
			CompanyGenericTagMapFactory::setTags( $this->getCompany(), 110, $this->getID(), $this->getTag() );

			$this->clearGeoCode( $data_diff ); //Clear Lon/Lat coordinates when address has changed.
		}

		if ( $this->getDeleted() == TRUE ) {
			Debug::Text('UnAssign Hours from Branch: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
			//Unassign hours from this branch.
			$pcf = TTnew( 'PunchControlFactory' ); /** @var PunchControlFactory $pcf */
			$udtf = TTnew( 'UserDateTotalFactory' ); /** @var UserDateTotalFactory $udtf */
			$uf = TTnew( 'UserFactory' ); /** @var UserFactory $uf */
			$sf = TTnew( 'StationFactory' ); /** @var StationFactory $sf */
			$sbf = TTnew( 'StationBranchFactory' ); /** @var StationBranchFactory $sbf */
			$sf_b = TTnew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf_b */
			$udf = TTnew( 'UserDefaultFactory' ); /** @var UserDefaultFactory $udf */
			$rstf = TTnew( 'RecurringScheduleTemplateFactory' ); /** @var RecurringScheduleTemplateFactory $rstf */
			$rsf = TTnew( 'RecurringScheduleFactory' ); /** @var RecurringScheduleFactory $rsf */

			$query = 'update '. $pcf->getTable() .' set branch_id = \''. TTUUID::getZeroID() .'\' where branch_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->ExecuteSQL($query);

			$query = 'update '. $udtf->getTable() .' set branch_id = \''. TTUUID::getZeroID() .'\' where branch_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->ExecuteSQL($query);

			$query = 'update '. $sf_b->getTable() .' set branch_id = \''. TTUUID::getZeroID() .'\' where branch_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->ExecuteSQL($query);

			$query = 'update '. $uf->getTable() .' set default_branch_id = \''. TTUUID::getZeroID() .'\' where company_id = \''. TTUUID::castUUID($this->getCompany()) .'\' AND default_branch_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->ExecuteSQL($query);

			$query = 'update '. $udf->getTable() .' set default_branch_id = \''. TTUUID::getZeroID() .'\' where company_id = \''. TTUUID::castUUID($this->getCompany()) .'\' AND default_branch_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->ExecuteSQL($query);

			$query = 'update '. $sf->getTable() .' set branch_id = \''. TTUUID::getZeroID() .'\' where company_id = \''. TTUUID::castUUID($this->getCompany()) .'\' AND branch_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->ExecuteSQL($query);

			$query = 'delete from '. $sbf->getTable() .' where branch_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->ExecuteSQL($query);

			$query = 'update '. $rstf->getTable() .' set branch_id = \''. TTUUID::getZeroID() .'\' where branch_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->ExecuteSQL($query);

			$query = 'update '. $rsf->getTable() .' set branch_id = \''. TTUUID::getZeroID() .'\' where branch_id = \''. TTUUID::castUUID($this->getId()) .'\'';
			$this->ExecuteSQL($query);

			if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
				$jf = TTNew( 'JobFactory' ); /** @var JobFactory $jf */
				$query = 'update '. $jf->getTable() .' set branch_id = \''. TTUUID::getZeroID() .'\' where branch_id = \''. TTUUID::castUUID($this->getId()) .'\'';
				$this->ExecuteSQL($query);

				//Job employee criteria
				$cgmlf = TTnew( 'CompanyGenericMapListFactory' ); /** @var CompanyGenericMapListFactory $cgmlf */
				$cgmlf->getByCompanyIDAndObjectTypeAndMapID( $this->getCompany(), 1010, $this->getID() );
				if ( $cgmlf->getRecordCount() > 0 ) {
					foreach ( $cgmlf as $cgm_obj ) {
						Debug::text( 'Deleting from Company Generic Map: ' . $cgm_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );
						$cgm_obj->Delete();
					}
				}
			}

		}

		return TRUE;
	}

	/**
	 * @return bool|string
	 */
	function getMapURL() {
		return Misc::getMapURL( $this->getAddress1(), $this->getAddress2(), $this->getCity(), $this->getProvince(), $this->getPostalCode(), $this->getCountry() );
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
		$data = array();
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'status':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'name_metaphone':
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Branch') .': '. $this->getName(), NULL, $this->getTable(), $this );
	}

}
?>
