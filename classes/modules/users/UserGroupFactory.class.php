<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2021 TimeTrex Software Inc.
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
class UserGroupFactory extends Factory {
	protected $table = 'user_group';
	protected $pk_sequence_name = 'user_group_id_seq'; //PK Sequence name

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
				'parent_id'  => 'Parent',
				'name'       => 'Name',
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
	 * @param string $value UUID
	 * @return bool
	 */
	function setCompany( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'Company ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getParent() {
		return $this->getGenericDataValue( 'parent_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setParent( $value ) {
		return $this->setGenericDataValue( 'parent_id', TTUUID::castUUID( $value ) );
	}

	/**
	 * @return mixed
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
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Company
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$this->Validator->isResultSetWithRows( 'company',
											   $clf->getByID( $this->getCompany() ),
											   TTi18n::gettext( 'Company is invalid' )
		);
		// Name
		$this->Validator->isLength( 'name',
									$this->getName(),
									TTi18n::gettext( 'Name is invalid' ),
									2, 50
		);

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->isNew() == false
				&& $this->getId() == $this->getParent() ) {
			$this->Validator->isTrue( 'parent',
									  false,
									  TTi18n::gettext( 'Cannot re-parent group to itself' )
			);
		} else {
			if ( $this->isNew() == false ) {
				$uglf = TTnew( 'UserGroupListFactory' ); /** @var UserGroupListFactory $uglf */
				$nodes = $uglf->getByCompanyIdArray( $this->getCompany() );
				$children_ids = TTTree::getElementFromNodes( TTTree::flattenArray( TTTree::createNestedArrayWithDepth( $nodes, $this->getId() ) ), 'id' );
				if ( is_array( $children_ids ) && in_array( $this->getParent(), $children_ids ) == true ) {
					Debug::Text( ' Objects cant be re-parented to their own children...', __FILE__, __LINE__, __METHOD__, 10 );
					$this->Validator->isTrue( 'parent',
											  false,
											  TTi18n::gettext( 'Unable to change parent to a child of itself' )
					);
				}
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getParent() == '' ) {
			$this->setParent( TTUUID::getZeroID() );
		}

		return true;
	}

	/**
	 * Must be postSave because we need the ID of the object.
	 * @return bool
	 */
	function postSave() {
		$this->StartTransaction();

		if ( $this->getDeleted() == true ) {
			//Get parent of this object, and re-parent all groups to it.
			$uglf = TTnew( 'UserGroupListFactory' ); /** @var UserGroupListFactory $uglf */
			$uglf->getByCompanyIdAndParentId( $this->getCompany(), $this->getId() );
			if ( $uglf->getRecordCount() > 0 ) {
				foreach ( $uglf as $ug_obj ) {
					Debug::Text( ' Re-Parenting ID: ' . $ug_obj->getId() . ' To: ' . $this->getParent(), __FILE__, __LINE__, __METHOD__, 10 );
					$ug_obj->setParent( $this->getParent() );
					if ( $ug_obj->isValid() ) {
						$ug_obj->Save();
					}
				}
			}

			//Get items by group id.
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$ulf->getByCompanyIdAndGroupId( $this->getCompany(), $this->getId() );
			if ( $ulf->getRecordCount() > 0 ) {
				foreach ( $ulf as $obj ) {
					Debug::Text( ' Re-Grouping Item: ' . $obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
					$obj->setGroup( $this->getParent() );
					$obj->Save();
				}
			}

			//Delete this group from station/job criteria
			$sugf = TTnew( 'StationUserGroupFactory' ); /** @var StationUserGroupFactory $sugf */

			$query = 'DELETE FROM ' . $sugf->getTable() . ' WHERE group_id = \'' . TTUUID::castUUID( $this->getId() ) . '\'';
			$this->ExecuteSQL( $query );

			//Job employee criteria
			$cgmlf = TTnew( 'CompanyGenericMapListFactory' ); /** @var CompanyGenericMapListFactory $cgmlf */
			$cgmlf->getByCompanyIDAndObjectTypeAndMapID( $this->getCompany(), 1030, $this->getID() );
			if ( $cgmlf->getRecordCount() > 0 ) {
				foreach ( $cgmlf as $cgm_obj ) {
					Debug::text( 'Deleting from Company Generic Map: ' . $cgm_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );
					$cgm_obj->Delete();
				}
			}
		}

		$this->CommitTransaction();

		return true;
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Employee Group' ), null, $this->getTable(), $this );
	}
}

?>
