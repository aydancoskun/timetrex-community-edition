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
class UserTitleFactory extends Factory {
	protected $table = 'user_title';
	protected $pk_sequence_name = 'user_title_id_seq'; //PK Sequence name

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {

		$retval = null;
		switch ( $name ) {
			case 'columns':
				$retval = [
						'-1000-name' => TTi18n::gettext( 'Name' ),

						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'name',
						'created_by',
						'created_date',
						'updated_by',
						'updated_date',
				];
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [
						'name',
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
				'name'       => 'Name',
				'other_id1'  => 'OtherID1',
				'other_id2'  => 'OtherID2',
				'other_id3'  => 'OtherID3',
				'other_id4'  => 'OtherID4',
				'other_id5'  => 'OtherID5',
				'deleted'    => 'Deleted',
		];

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
	function setCompany( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name ) {
		$name = trim( $name );
		if ( $name == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $this->getCompany() ),
				'name'       => TTi18n::strtolower( $name ),
		];

		$query = 'select id from ' . $this->table . '
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
	function getOtherID1() {
		return $this->getGenericDataValue( 'other_id1' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOtherID1( $value ) {
		$value = trim( $value );

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
	function setOtherID2( $value ) {
		$value = trim( $value );

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
	function setOtherID3( $value ) {
		$value = trim( $value );

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
	function setOtherID4( $value ) {
		$value = trim( $value );

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
	function setOtherID5( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'other_id5', $value );
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//

		//Company
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$this->Validator->isResultSetWithRows( 'company',
											   $clf->getByID( $this->getCompany() ),
											   TTi18n::gettext( 'Company is invalid' ) );

		//Name
		if ( $this->getName() == '' ) {
			$this->Validator->isTRUE( 'name',
									  false,
									  TTi18n::gettext( 'Name not specified' )
			);
		}
		if ( $this->Validator->isError( 'name' ) == false ) {
			$this->Validator->isLength( 'name',
										$this->getName(),
										TTi18n::gettext( 'Name is too short or too long' ),
										2,
										100 );
		}
		if ( $this->Validator->isError( 'name' ) == false ) {
			$this->Validator->isTrue( 'name',
									  $this->isUniqueName( $this->getName() ),
									  TTi18n::gettext( 'Title already exists' ) );
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
		return true;
	}

	function postSave() {
		if ( $this->getDeleted() == true ) {
			Debug::Text( 'UnAssign title from employees: ' . $this->getId(), __FILE__, __LINE__, __METHOD__, 10 );

			$uf = TTnew( 'UserFactory' ); /** @var UserFactory $uf */
			$udf = TTnew( 'UserDefaultFactory' ); /** @var UserDefaultFactory $udf */

			$query = 'update ' . $uf->getTable() . ' set title_id = \'' . TTUUID::getZeroID() . '\' where company_id = \'' . TTUUID::castUUID( $this->getCompany() ) . '\' AND title_id = \'' . TTUUID::castUUID( $this->getId() ) . '\'';
			$this->ExecuteSQL( $query );

			$query = 'update ' . $udf->getTable() . ' set title_id = \'' . TTUUID::getZeroID() . '\' where company_id = \'' . TTUUID::castUUID( $this->getCompany() ) . '\' AND title_id = \'' . TTUUID::castUUID( $this->getId() ) . '\'';
			$this->ExecuteSQL( $query );
		}
	}

	/**
	 * Support setting created_by, updated_by especially for importing data.
	 * Make sure data is set based on the getVariableToFunctionMap order.
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Employee Title' ), null, $this->getTable(), $this );
	}
}

?>
