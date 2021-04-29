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
 * @package Core
 */
class UserSettingFactory extends Factory {
	protected $table = 'user_setting';
	protected $pk_sequence_name = 'user_setting_id_seq'; //PK Sequence name

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
				'user_id'    => 'User',
				'first_name' => false,
				'last_name'  => false,
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
		if ( $this->getUser() == false ) {
			return false;
		}

		$name = trim( $name );
		if ( $name == '' ) {
			return false;
		}

		$ph = [
				'user_id' => TTUUID::castUUID( $this->getUser() ),
				'name'    => TTi18n::strtolower( $name ),
		];

		$query = 'select id from ' . $this->getTable() . '
					where user_id = ?
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
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// User
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$this->Validator->isResultSetWithRows( 'user_id',
											   $ulf->getByID( $this->getUser() ),
											   TTi18n::gettext( 'Invalid Employee' )
		);
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
	 * @return bool
	 */
	function preSave() {
		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getUser() . $this->getName() );

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
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'first_name':
						case 'last_name':
							$data[$variable] = $this->getColumn( $variable );
							break;
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
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'User Setting - Name' ) . ': ' . $this->getName() . ' ' . TTi18n::getText( 'Value' ) . ': ' . $this->getValue(), null, $this->getTable() );
	}

	/**
	 * @param string $user_id UUID
	 * @param $name
	 * @return bool
	 */
	static function getUserSetting( $user_id, $name ) {
		$uslf = new UserSettingListFactory();
		$uslf->getByUserIdAndName( $user_id, $name );
		if ( $uslf->getRecordCount() == 1 ) {
			$us_obj = $uslf->getCurrent();
			$retarr = $us_obj->getObjectAsArray();

			return $retarr;
		}

		return false;
	}

	/**
	 * @param string $user_id UUID
	 * @param $name
	 * @param $value
	 * @param int $type_id
	 * @return bool
	 */
	static function setUserSetting( $user_id, $name, $value, $type_id = 10 ) {
		$row = [
				'user_id' => $user_id,
				'name'    => $name,
				'value'   => $value,
				'type_id' => $type_id,
		];
		$uslf = new UserSettingListFactory();
		$uslf->getByUserIdAndName( $user_id, $name );
		if ( $uslf->getRecordCount() == 1 ) {
			$usf = $uslf->getCurrent();
			$row = array_merge( $usf->getObjectAsArray(), $row );
		} else {
			$usf = new UserSettingFactory();
		}

		Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );
		$usf->setObjectFromArray( $row );
		if ( $usf->isValid() ) {
			return $usf->Save();
		}

		return false;
	}

	/**
	 * @param string $user_id UUID
	 * @param $name
	 * @return bool
	 */
	static function deleteUserSetting( $user_id, $name ) {
		$uslf = new UserSettingListFactory();
		$uslf->getByUserIdAndName( $user_id, $name );
		if ( $uslf->getRecordCount() == 1 ) {
			$usf = $uslf->getCurrent();
			$usf->setDeleted( true );
			if ( $usf->isValid() ) {
				$usf->Save();
			}
		}

		return false;
	}
}

?>
