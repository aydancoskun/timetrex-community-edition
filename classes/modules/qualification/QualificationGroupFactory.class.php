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
 * @package Modules\Qualification
 */
class QualificationGroupFactory extends Factory {
	protected $table = 'qualification_group';
	protected $pk_sequence_name = 'qualification_group_id_seq'; //PK Sequence name

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
						'-1030-name' => TTi18n::gettext( 'Name' ),

						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];
				break;
			case 'unique_columns': //Columns that are displayed by default.
				$retval = [
						'name',
				];
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
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
	 * @return bool
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
	 * @return bool|mixed
	 */
	function getParent() {
		return $this->getGenericDataValue( 'parent_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setParent( $value ) {
		return $this->setGenericDataValue( 'parent_id', TTUUID::castUUID( $value ) );
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
	 * @return bool
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
		if ( $this->getCompany() != TTUUID::getZeroID() ) {
			$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
			$this->Validator->isResultSetWithRows( 'company_id',
												   $clf->getByID( $this->getCompany() ),
												   TTi18n::gettext( 'Company is invalid' )
			);
		}
		// Name
		$this->Validator->isLength( 'name',
									$this->getName(),
									TTi18n::gettext( 'Name is too short or too long' ),
									2,
									100
		);
		if ( $this->Validator->isError( 'name' ) == false ) {
			$this->Validator->isTrue( 'name',
									  $this->isUniqueName( $this->getName() ),
									  TTi18n::gettext( 'Group already exists' )
			);
		}

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
				$qglf = TTnew( 'QualificationGroupListFactory' ); /** @var QualificationGroupListFactory $qglf */
				$nodes = $qglf->getByCompanyIdArray( $this->getCompany() );
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
			$qglf = TTnew( 'QualificationGroupListFactory' ); /** @var QualificationGroupListFactory $qglf */
			$qglf->getByCompanyIdAndParentId( $this->getCompany(), $this->getId() );
			if ( $qglf->getRecordCount() > 0 ) {
				foreach ( $qglf as $qg_obj ) {
					Debug::Text( ' Re-Parenting ID: ' . $qg_obj->getId() . ' To: ' . $this->getParent(), __FILE__, __LINE__, __METHOD__, 10 );
					$qg_obj->setParent( $this->getParent() );
					if ( $qg_obj->isValid() ) {
						$qg_obj->Save();
					}
				}
			}

			//Get items by group id.
			$qlf = TTnew( 'QualificationListFactory' ); /** @var QualificationListFactory $qlf */
			$qlf->getByCompanyIdAndGroupId( $this->getCompany(), $this->getId() );
			if ( $qlf->getRecordCount() > 0 ) {
				foreach ( $qlf as $obj ) {
					Debug::Text( ' Re-Grouping Item: ' . $obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
					$obj->setGroup( $this->getParent() );
					$obj->Save();
				}
			}

			//Company generic mapping
			$cgmlf = TTnew( 'CompanyGenericMapListFactory' ); /** @var CompanyGenericMapListFactory $cgmlf */
			$cgmlf->getByCompanyIDAndObjectTypeAndMapID( $this->getCompany(), 1090, $this->getID() );
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
	 * @param bool $permission_children_ids
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null, $permission_children_ids = false ) {
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
			$this->getPermissionColumns( $data, $this->getCreatedBy(), false, $permission_children_ids, $include_columns );

			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Qualification Group' ), null, $this->getTable(), $this );
	}
}

?>
