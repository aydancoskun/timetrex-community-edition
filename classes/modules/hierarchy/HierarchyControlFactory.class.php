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
 * @package Modules\Hierarchy
 */
class HierarchyControlFactory extends Factory {
	protected $table = 'hierarchy_control';
	protected $pk_sequence_name = 'hierarchy_control_id_seq'; //PK Sequence name

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {

		$retval = null;
		switch ( $name ) {
			case 'object_type':
				$hotlf = TTnew( 'HierarchyObjectTypeListFactory' ); /** @var HierarchyObjectTypeListFactory $hotlf */
				$retval = $hotlf->getOptions( 'object_type' ); //Must contain sort prefixes, otherwise Edit Employee -> Hierarchy tab will be in the wrong order.
				break;
			case 'short_object_type':
				$hotlf = TTnew( 'HierarchyObjectTypeListFactory' ); /** @var HierarchyObjectTypeListFactory $hotlf */
				$retval = $hotlf->getOptions( 'short_object_type' );
				break;
			case 'columns':
				$retval = [
						'-1010-name'                => TTi18n::gettext( 'Name' ),
						'-1020-description'         => TTi18n::gettext( 'Description' ),
						'-1030-superiors'           => TTi18n::gettext( 'Superiors' ),
						'-1030-subordinates'        => TTi18n::gettext( 'Subordinates' ),
						'-1050-object_type_display' => TTi18n::gettext( 'Objects' ),

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
						'description',
						'superiors',
						'subordinates',
						'object_type_display',
				];
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [];
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = [];
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
				'id'                  => 'ID',
				'company_id'          => 'Company',
				'name'                => 'Name',
				'description'         => 'Description',
				'superiors'           => 'TotalSuperiors',
				'subordinates'        => 'TotalSubordinates',
				'object_type'         => 'ObjectType',
				'object_type_display' => false,
				'user'                => 'User',
				'deleted'             => 'Deleted',
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

		$query = 'select id from ' . $this->getTable() . ' where company_id = ? AND lower(name) = ? AND deleted = 0';
		$hierarchy_control_id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $hierarchy_control_id, 'Unique Hierarchy Control ID: ' . $hierarchy_control_id, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $hierarchy_control_id === false ) {
			return true;
		} else {
			if ( $hierarchy_control_id == $this->getId() ) {
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
	function getDescription() {
		return $this->getGenericDataValue( 'description' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDescription( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'description', $value );
	}

	/**
	 * @return null|string
	 */
	function getObjectTypeDisplay() {
		$object_type_ids = $this->getObjectType();
		$object_types = Misc::trimSortPrefix( $this->getOptions( 'short_object_type' ) );

		$retval = [];
		if ( is_array( $object_type_ids ) ) {
			foreach ( $object_type_ids as $object_type_id ) {
				$retval[] = Option::getByKey( $object_type_id, $object_types );
			}
			sort( $retval ); //Maintain consistent order.

			return implode( ',', $retval );
		}

		return null;
	}

	/**
	 * @return array|bool
	 */
	function getObjectType() {
		$valid_object_type_ids = Misc::trimSortPrefix( $this->getOptions( 'object_type' ) );

		$hotlf = TTnew( 'HierarchyObjectTypeListFactory' ); /** @var HierarchyObjectTypeListFactory $hotlf */
		$hotlf->getByHierarchyControlId( $this->getId() );
		if ( $hotlf->getRecordCount() > 0 ) {
			foreach ( $hotlf as $object_type ) {
				if ( isset( $valid_object_type_ids[$object_type->getObjectType()] ) ) {
					$object_type_list[] = $object_type->getObjectType();
				}
			}

			if ( isset( $object_type_list ) ) {
				return $object_type_list;
			}
		}

		return false;
	}

	/**
	 * @param string|string[] $ids UUID
	 * @return bool
	 */
	function setObjectType( $ids ) {
		if ( is_array( $ids ) && count( $ids ) > 0 ) {
			$tmp_ids = [];
			Debug::Arr( $ids, 'IDs: ', __FILE__, __LINE__, __METHOD__, 10 );

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = TTnew( 'HierarchyObjectTypeListFactory' ); /** @var HierarchyObjectTypeListFactory $lf_a */
				$lf_a->getByHierarchyControlId( $this->getId() );
				Debug::text( 'Existing Object Type Rows: ' . $lf_a->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );

				foreach ( $lf_a as $obj ) {
					//$id = $obj->getId();
					$id = $obj->getObjectType(); //Need to use object_types rather than row IDs.
					Debug::text( 'Hierarchy Object Type ID: ' . $obj->getId() . ' ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

					//Delete users that are not selected.
					if ( !in_array( $id, $ids ) ) {
						Debug::text( 'Deleting: Object Type: ' . $id . ' ID: ' . $obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );
						$obj->Delete();
						$obj->postSave(); //Clear cache.
					} else {
						//Save ID's that need to be updated.
						Debug::text( 'NOT Deleting: Object Type: ' . $id . ' ID: ' . $obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );
						$tmp_ids[] = $id;
					}
				}
				unset( $id, $obj );
			}

			foreach ( $ids as $id ) {
				if ( isset( $ids ) && !in_array( $id, $tmp_ids ) ) {
					$hotf = TTnew( 'HierarchyObjectTypeFactory' ); /** @var HierarchyObjectTypeFactory $hotf */
					$hotf->setHierarchyControl( $this->getId() );
					$hotf->setObjectType( $id );

					if ( $this->Validator->isTrue( 'object_type',
												   $hotf->isValid(),
												   TTi18n::gettext( 'Object type is already assigned to another hierarchy' ) ) ) {
						$hotf->save();
					}
				}
			}

			return true;
		}

		$this->Validator->isTrue( 'object_type',
								  false,
								  TTi18n::gettext( 'At least one object must be selected' ) );

		return false;
	}

	/**
	 * @return array|bool
	 */
	function getUser() {
		$hulf = TTnew( 'HierarchyUserListFactory' ); /** @var HierarchyUserListFactory $hulf */
		$hulf->getByHierarchyControlID( $this->getId() );
		foreach ( $hulf as $obj ) {
			$list[] = $obj->getUser();
		}

		if ( isset( $list ) ) {
			return $list;
		}

		return false;
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setUser( $ids ) {
		if ( !is_array( $ids ) ) {
			$ids = [ $ids ];
		}

		Debug::text( 'Setting User IDs : ', __FILE__, __LINE__, __METHOD__, 10 );
		if ( is_array( $ids ) ) {
			$tmp_ids = [];

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$hulf = TTnew( 'HierarchyUserListFactory' ); /** @var HierarchyUserListFactory $hulf */
				$hulf->getByHierarchyControlID( $this->getId() );

				foreach ( $hulf as $obj ) {
					$id = $obj->getUser();
					Debug::text( 'HierarchyControl ID: ' . $obj->getHierarchyControl() . ' ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

					//Delete users that are not selected.
					if ( !in_array( $id, $ids ) ) {
						Debug::text( 'Deleting: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text( 'NOT Deleting : ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$tmp_ids[] = $id;
					}
				}
				unset( $id, $obj );
			}

			//Insert new mappings.
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */

			foreach ( $ids as $id ) {
				if ( isset( $ids ) && !in_array( $id, $tmp_ids ) ) {
					$huf = TTnew( 'HierarchyUserFactory' ); /** @var HierarchyUserFactory $huf */
					$huf->setHierarchyControl( $this->getId() );
					$huf->setUser( $id );

					$ulf->getById( $id );
					if ( $ulf->getRecordCount() > 0 ) {
						$obj = $ulf->getCurrent();

						if ( $this->Validator->isTrue( 'user',
													   $huf->isValid(),
													   TTi18n::gettext( 'Selected subordinate is invalid or already assigned to another hierarchy with the same objects' ) . ' (' . $obj->getFullName() . ')' )
						) {
							$huf->save();
						}
					}
				}
			}

			return true;
		}

		Debug::text( 'No User IDs to set.', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return mixed
	 */
	function getTotalSubordinates() {
		$hulf = TTnew( 'HierarchyUserListFactory' ); /** @var HierarchyUserListFactory $hulf */
		$hulf->getByHierarchyControlID( $this->getId() );

		return $hulf->getRecordCount();
	}

	/**
	 * @return mixed
	 */
	function getTotalSuperiors() {
		$hllf = TTnew( 'HierarchyLevelListFactory' ); /** @var HierarchyLevelListFactory $hllf */
		$hllf->getByHierarchyControlId( $this->getID() );

		return $hllf->getRecordCount();
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		//
		// Company
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$this->Validator->isResultSetWithRows( 'company',
											   $clf->getByID( $this->getCompany() ),
											   TTi18n::gettext( 'Invalid Company' )
		);
		// Name
		$this->Validator->isLength( 'name',
									$this->getName(),
									TTi18n::gettext( 'Name is invalid' ),
									2, 250
		);
		if ( $this->Validator->isError( 'name' ) == false ) {
			$this->Validator->isTrue( 'name',
									  $this->isUniqueName( $this->getName() ),
									  TTi18n::gettext( 'Name is already in use' )
			);
		}
		// Description
		if ( $this->getDescription() != '' ) {
			$this->Validator->isLength( 'description',
										$this->getDescription(),
										TTi18n::gettext( 'Description is invalid' ),
										1, 250
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->getName() == false && $this->Validator->hasError( 'name' ) == false ) {
			$this->Validator->isTrue( 'name',
									  false,
									  TTi18n::gettext( 'Name is not specified' ) );
		}

		//When the user changes just the hierarchy objects, we need to loop through ALL users and confirm no conflicting hierarchies exist.
		//Only do this for existing hierarchies and ones that are already valid up to this point.
		if ( !$this->isNew() && $this->Validator->isValid() == true ) {

			$user_ids = $this->getUser();
			if ( is_array( $user_ids ) ) {
				$huf = TTNew( 'HierarchyUserFactory' ); /** @var HierarchyUserFactory $huf */
				$huf->setHierarchyControl( $this->getID() );

				foreach ( $user_ids as $user_id ) {
					if ( $huf->isUniqueUser( $user_id ) == false ) {
						$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
						$ulf->getById( $user_id );
						if ( $ulf->getRecordCount() > 0 ) {
							$obj = $ulf->getCurrent();
							$this->Validator->isTrue( 'user',
													  $huf->isUniqueUser( $user_id, $this->getID() ),
													  TTi18n::gettext( 'Selected subordinate is invalid or already assigned to another hierarchy with the same objects' ) . ' (' . $obj->getFullName() . ')' );
						} else {
							TTi18n::gettext( 'Selected subordinate is invalid or already assigned to another hierarchy with the same object. User ID: %1', [ $user_id ] );
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
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
	 * @return mixed
	 */
	function getObjectAsArray( $include_columns = null ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						//case 'superiors':
						//case 'subordinates':
						//	$data[$variable] = $this->getColumn($variable);
						//	break;
						case 'object_type_display':
							$data[$variable] = $this->getObjectTypeDisplay();
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Hierarchy' ), null, $this->getTable(), $this );
	}
}

?>
