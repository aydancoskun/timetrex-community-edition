<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2020 TimeTrex Software Inc.
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
 * @package Core
 */
class CompanySettingFactory extends Factory {
	protected $table = 'company_setting';
	protected $pk_sequence_name = 'company_setting_id_seq'; //PK Sequence name

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {

		$retval = null;
		switch ( $name ) {
			case 'type':
				$retval = [
						10 => TTi18n::gettext( 'Public' ),
						20 => TTi18n::gettext( 'Private' ),
				];
				break;
		}

		return $retval;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = [
				'id'         => 'ID',
				'company_id' => 'Company',
				'type_id'    => 'Type',
				'type'       => false,
				'name'       => 'Name',
				'value'      => 'Value',
				'deleted'    => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name ) {
		Debug::Arr( $this->getCompany(), 'Company: ', __FILE__, __LINE__, __METHOD__, 10 );
		if ( $this->getCompany() == false ) {
			return false;
		}

		$name = trim( $name );
		if ( $name == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $this->getCompany() ),
				'name'       => TTi18n::strtolower( $name ),
		];

		$query = 'select id from ' . $this->getTable() . '
					where company_id = ?
						AND lower(name) = ?
						AND deleted = 0';
		$name_id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $name_id, 'Unique Name: ' . $name, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $name_id === false ) {
			return true;
		} else {
			if ( $name_id == $this->getId() ) {
				return true;
			}
		}

		return false;
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
	function setCompany( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'company_id', $value );
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
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'type_id', $value );
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
		$value = trim( $value );

		return $this->setGenericDataValue( 'name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getValue() {
		return $this->getGenericDataValue( 'value' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'value', $value );
	}


	/**
	 * @return bool
	 */
	function preSave() {
		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getCompany() . $this->getName() );

		return true;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach ( $variable_function_map as $key => $function ) {
				if ( isset( $data[$key] ) ) {

					$function = 'set' . $function;
					switch ( $key ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return true;
		}

		return false;
	}

	/**
	 * @param null $include_columns
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		$data = [];
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'type':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
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
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Company
		if ( $this->getCompany() != TTUUID::getZeroID() ) {
			$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
			$this->Validator->isResultSetWithRows( 'company',
												   $clf->getByID( $this->getCompany() ),
												   TTi18n::gettext( 'Company is invalid' )
			);
		}
		// Type
		$this->Validator->inArrayKey( 'type',
									  $this->getType(),
									  TTi18n::gettext( 'Incorrect Type' ),
									  $this->getOptions( 'type' )
		);
		// Name
		$this->Validator->isLength( 'name',
									$this->getName(),
									TTi18n::gettext( 'Name is too short or too long' ),
									1, 250
		);
		if ( $this->Validator->isError( 'name' ) == false ) {
			$this->Validator->isTrue( 'name',
									  $this->isUniqueName( $this->getName() ),
									  TTi18n::gettext( 'Name already exists' )
			);
		}
		// Value
		$this->Validator->isLength( 'value',
									$this->getValue(),
									TTi18n::gettext( 'Value is too short or too long' ),
									1, 4096
		);
		//
		// ABOVE: Validation code moved from set*() functions.
		//
		return true;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Company Setting - Name' ) . ': ' . $this->getName() . ' ' . TTi18n::getText( 'Value' ) . ': ' . $this->getValue(), null, $this->getTable() );
	}

	/**
	 * @param string $company_id UUID
	 * @param $name
	 * @return bool
	 */
	static function getCompanySettingObjectByName( $company_id, $name ) {
		$cslf = new CompanySettingListFactory();
		$cslf->getByCompanyIdAndName( $company_id, $name );
		if ( $cslf->getRecordCount() == 1 ) {
			$cs_obj = $cslf->getCurrent();

			return $cs_obj;
		}

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $name
	 * @return bool
	 */
	static function getCompanySettingArrayByName( $company_id, $name ) {
		$cs_obj = self::getCompanySettingObjectByName( $company_id, $name );
		if ( is_object( $cs_obj ) ) {
			return $cs_obj->getObjectAsArray();
		}

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $name
	 * @return null
	 */
	static function getCompanySettingValueByName( $company_id, $name ) {
		$cs_obj = self::getCompanySettingObjectByName( $company_id, $name );
		if ( is_object( $cs_obj ) ) {
			return $cs_obj->getValue();
		}

		return null;
	}

	/**
	 * @param string $company_id UUID
	 * @param $name
	 * @param $value
	 * @param int $type_id
	 * @return bool
	 */
	static function setCompanySetting( $company_id, $name, $value, $type_id = 10 ) {
		$row = [
				'company_id' => $company_id,
				'name'       => $name,
				'value'      => $value,
				'type_id'    => $type_id,
		];
		$cslf = new CompanySettingListFactory();
		$cslf->getByCompanyIdAndName( $company_id, $name );
		if ( $cslf->getRecordCount() == 1 ) {
			$csf = $cslf->getCurrent();
			$row = array_merge( $csf->getObjectAsArray(), $row );
		} else {
			$csf = new CompanySettingFactory();
		}

		Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );
		$csf->setObjectFromArray( $row );
		if ( $csf->isValid() ) {
			$csf->Save();
		}

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $name
	 * @return bool
	 */
	static function deleteCompanySetting( $company_id, $name ) {
		$cslf = new CompanySettingListFactory();
		$cslf->getByCompanyIdAndName( $company_id, $name );
		if ( $cslf->getRecordCount() == 1 ) {
			$csf = $cslf->getCurrent();
			$csf->setDeleted( true );
			if ( $csf->isValid() ) {
				$csf->Save();
			}
		}

		return false;
	}
}

?>
