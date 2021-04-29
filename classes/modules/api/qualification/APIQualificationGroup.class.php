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
 * @package API\Qualification
 */
class APIQualificationGroup extends APIFactory {
	protected $main_class = 'QualificationGroupFactory';

	/**
	 * APIQualificationGroup constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * Get options for dropdown boxes.
	 * @param bool|string $name Name of options to return, ie: 'columns', 'type', 'status'
	 * @param mixed $parent     Parent name/ID of options to return if data is in hierarchical format. (ie: Province)
	 * @return bool|array
	 */
	function getOptions( $name = false, $parent = null ) {
		if ( $name == 'columns'
				&& ( !$this->getPermissionObject()->Check( 'qualification', 'enabled' )
						|| !( $this->getPermissionObject()->Check( 'qualification', 'view' ) || $this->getPermissionObject()->Check( 'qualification', 'view_own' ) || $this->getPermissionObject()->Check( 'qualification', 'view_child' ) ) ) ) {
			$name = 'list_columns';
		}

		return parent::getOptions( $name, $parent );
	}

	/**
	 * Get default QualificationGroup data for creating new QualificationGroupes.
	 * @return array
	 */
	function getQualificationGroupDefaultData() {
		$company_obj = $this->getCurrentCompanyObject();

		Debug::Text( 'Getting QualificationGroup default data...', __FILE__, __LINE__, __METHOD__, 10 );

		$data = [
				'company_id' => $company_obj->getId(),
				'parent_id'  => TTUUID::getZeroID(),
				'name'       => null,
		];

		return $this->returnHandler( $data );
	}

	/**
	 * Get QualificationGroup data for one or more QualificationGroupes.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @param string $mode
	 * @return array|bool
	 */
	function getQualificationGroup( $data = null, $disable_paging = false, $mode = 'flat' ) {
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'qualification', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'qualification', 'view' ) || $this->getPermissionObject()->Check( 'qualification', 'view_own' ) || $this->getPermissionObject()->Check( 'qualification', 'view_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'qualification', 'view' );

		$qglf = TTnew( 'QualificationGroupListFactory' ); /** @var QualificationGroupListFactory $qglf */

		if ( $mode == 'flat' ) {

			$qglf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], null, $data['filter_sort'] );
			Debug::Text( 'Record Count: ' . $qglf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );

			if ( $qglf->getRecordCount() > 0 ) {
				$this->getProgressBarObject()->start( $this->getAMFMessageID(), $qglf->getRecordCount() );

				$this->setPagerObject( $qglf );

				$retarr = [];
				foreach ( $qglf as $ug_obj ) {
					$retarr[] = $ug_obj->getObjectAsArray( $data['filter_columns'], $data['filter_data']['permission_children_ids'] );

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $qglf->getCurrentRow() );
				}

				$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

				return $this->returnHandler( $retarr );
			}
		} else {
			$nodes = $qglf->getByCompanyIdArray( $this->getCurrentCompanyObject()->getId() );
			//Debug::Arr($nodes, ' Nodes: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Text('Record Count: '. count($nodes), __FILE__, __LINE__, __METHOD__, 10);
			if ( isset( $nodes ) ) {
				$retarr = TTTree::FormatArray( $nodes );

				//Debug::Arr($retarr, ' Data: ', __FILE__, __LINE__, __METHOD__, 10);

				return $this->returnHandler( $retarr );
			}
		}

		return $this->returnHandler( true ); //No records returned.
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonQualificationGroupData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getQualificationGroup( $data, true ) ) );
	}

	/**
	 * Validate QualificationGroup data for one or more QualificationGroupes.
	 * @param array $data QualificationGroup data
	 * @return array
	 */
	function validateQualificationGroup( $data ) {
		return $this->setQualificationGroup( $data, true );
	}

	/**
	 * Set QualificationGroup data for one or more QualificationGroupes.
	 * @param array $data QualificationGroup data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setQualificationGroup( $data, $validate_only = false, $ignore_warning = true ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'qualification', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'qualification', 'edit' ) || $this->getPermissionObject()->Check( 'qualification', 'edit_own' ) || $this->getPermissionObject()->Check( 'qualification', 'edit_child' ) || $this->getPermissionObject()->Check( 'qualification', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == true ) {
			Debug::Text( 'Validating Only!', __FILE__, __LINE__, __METHOD__, 10 );
			$permission_children_ids = false;
		} else {
			//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
			$permission_children_ids = $this->getPermissionChildren();
		}

		list( $data, $total_records ) = $this->convertToMultipleRecords( $data );

		Debug::Text( 'Received data for: ' . $total_records . ' QualificationGroups', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			foreach ( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'QualificationGroupListFactory' ); /** @var QualificationGroupListFactory $lf */
				$lf->StartTransaction();

				if ( isset( $row['id'] ) && $row['id'] != '' ) {
					//Modifying existing object.
					//Get QualificationGroup object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );

					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
								$validate_only == true
								||
								(
										$this->getPermissionObject()->Check( 'qualification', 'edit' )
										|| ( $this->getPermissionObject()->Check( 'qualification', 'edit_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy() ) === true )
										|| ( $this->getPermissionObject()->Check( 'qualification', 'edit_child' ) && $this->getPermissionObject()->isChild( $lf->getCurrent()->getCreatedBy(), $permission_children_ids ) === true )
								) ) {

							Debug::Text( 'Row Exists, getting current data for ID: ' . $row['id'], __FILE__, __LINE__, __METHOD__, 10 );
							$lf = $lf->getCurrent();
							$row = array_merge( $lf->getObjectAsArray(), $row );
						} else {
							$primary_validator->isTrue( 'permission', false, TTi18n::gettext( 'Edit permission denied' ) );
						}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', false, TTi18n::gettext( 'Edit permission denied, record does not exist' ) );
					}
				} else {
					//Adding new object, check ADD permissions.
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check( 'qualification', 'add' ), TTi18n::gettext( 'Add permission denied' ) );
				}
				Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

				$is_valid = $primary_validator->isValid( $ignore_warning );
				if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Setting object data...', __FILE__, __LINE__, __METHOD__, 10 );
					//Force Company ID to current company.
					$row['company_id'] = $this->getCurrentCompanyObject()->getId();

					$lf->setObjectFromArray( $row );

					$is_valid = $lf->isValid( $ignore_warning );
					if ( $is_valid == true ) {
						Debug::Text( 'Saving data...', __FILE__, __LINE__, __METHOD__, 10 );
						if ( $validate_only == true ) {
							$save_result[$key] = true;
						} else {
							$save_result[$key] = $lf->Save();
						}
						$validator_stats['valid_records']++;
					}
				}

				if ( $is_valid == false ) {
					Debug::Text( 'Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10 );

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					$validator[$key] = $this->setValidationArray( $primary_validator, $lf );
				} else if ( $validate_only == true ) {
					$lf->FailTransaction();
				}


				$lf->CommitTransaction();
			}

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}

	/**
	 * Delete one or more QualificationGroups.
	 * @param array $data QualificationGroup data
	 * @return array|bool
	 */
	function deleteQualificationGroup( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'qualification', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'qualification', 'delete' ) || $this->getPermissionObject()->Check( 'qualification', 'delete_own' ) || $this->getPermissionObject()->Check( 'qualification', 'delete_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		$permission_children_ids = $this->getPermissionChildren();

		Debug::Text( 'Received data for: ' . count( $data ) . ' QualificationGroups', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$total_records = count( $data );
		$validator = $save_result = $key = false;
		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		if ( is_array( $data ) && $total_records > 0 ) {
			foreach ( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'QualificationGroupListFactory' ); /** @var QualificationGroupListFactory $lf */
				$lf->StartTransaction();
				if ( $id != '' ) {
					//Modifying existing object.
					//Get QualificationGroup object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check( 'qualification', 'delete' )
								|| ( $this->getPermissionObject()->Check( 'qualification', 'delete_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy() ) === true )
								|| ( $this->getPermissionObject()->Check( 'qualification', 'delete_child' ) && $this->getPermissionObject()->isChild( $lf->getCurrent()->getCreatedBy(), $permission_children_ids ) === true ) ) {
							Debug::Text( 'Record Exists, deleting record ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
							$lf = $lf->getCurrent();
						} else {
							$primary_validator->isTrue( 'permission', false, TTi18n::gettext( 'Delete permission denied' ) );
						}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', false, TTi18n::gettext( 'Delete permission denied, record does not exist' ) );
					}
				} else {
					$primary_validator->isTrue( 'id', false, TTi18n::gettext( 'Delete permission denied, record does not exist' ) );
				}

				//Debug::Arr($lf, 'AData: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Attempting to delete record...', __FILE__, __LINE__, __METHOD__, 10 );
					$lf->setDeleted( true );

					$is_valid = $lf->isValid();
					if ( $is_valid == true ) {
						Debug::Text( 'Record Deleted...', __FILE__, __LINE__, __METHOD__, 10 );
						$save_result[$key] = $lf->Save();
						$validator_stats['valid_records']++;
					}
				}

				if ( $is_valid == false ) {
					Debug::Text( 'Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10 );

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					$validator[$key] = $this->setValidationArray( $primary_validator, $lf );
				}

				$lf->CommitTransaction();
			}

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}

	/**
	 * Copy one or more QualificationGroupes.
	 * @param array $data QualificationGroup IDs
	 * @return array
	 */
	function copyQualificationGroup( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' QualificationGroups', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$src_rows = $this->stripReturnHandler( $this->getQualificationGroup( [ 'filter_data' => [ 'id' => $data ] ], true ) );
		if ( is_array( $src_rows ) && count( $src_rows ) > 0 ) {
			Debug::Arr( $src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $src_rows as $key => $row ) {
				unset( $src_rows[$key]['id'], $src_rows[$key]['manual_id'] );     //Clear fields that can't be copied
				$src_rows[$key]['name'] = Misc::generateCopyName( $row['name'] ); //Generate unique name
			}

			//Debug::Arr($src_rows, 'bSRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->setQualificationGroup( $src_rows ); //Save copied rows
		}

		return $this->returnHandler( false );
	}

	/**
	 * Change parent of one or more groups to another group.
	 * @param array $src_id source Group ID
	 * @param int $dst_id   destination Group ID
	 * @return array
	 */
	function dragNdropQualificationGroup( $src_id, $dst_id ) {
		if ( !is_array( $src_id ) ) {
			$src_id = [ $src_id ];
		}

		if ( is_array( $dst_id ) ) {
			return $this->returnHandler( false );
		}

		Debug::Arr( $src_id, 'Src ID: Data: ', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $dst_id, 'Dst ID: Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$src_rows = $this->stripReturnHandler( $this->getQualificationGroup( [ 'filter_data' => [ 'id' => $src_id ] ], true, 'flat' ) );
		if ( is_array( $src_rows ) && count( $src_rows ) > 0 ) {
			Debug::Arr( $src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $src_rows as $key => $row ) {
				$src_rows[$key]['parent_id'] = $dst_id;
			}
			unset( $row ); //code standards
			Debug::Arr( $src_rows, 'bSRC Rows: ', __FILE__, __LINE__, __METHOD__, 10 );

			return $this->setQualificationGroup( $src_rows ); //Save copied rows
		}

		return $this->returnHandler( false );
	}
}

?>
