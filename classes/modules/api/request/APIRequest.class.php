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
 * @package API\Request
 */
class APIRequest extends APIFactory {
	protected $main_class = 'RequestFactory';

	/**
	 * APIRequest constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	/**
	 * Get options for dropdown boxes.
	 * @param bool|string $name Name of options to return, ie: 'columns', 'type', 'status'
	 * @param mixed $parent Parent name/ID of options to return if data is in hierarchical format. (ie: Province)
	 * @return bool|array
	 */
	function getOptions( $name = FALSE, $parent = NULL ) {
		if ( $name == 'columns'
				AND ( !$this->getPermissionObject()->Check('request', 'enabled')
					OR !( $this->getPermissionObject()->Check('request', 'view') OR $this->getPermissionObject()->Check('request', 'view_child') ) ) ) {
			$name = 'list_columns';
		}

		return parent::getOptions( $name, $parent );
	}

	/**
	 * Get default request data for creating new requestes.
	 * @return array
	 */
	function getRequestDefaultData() {
		Debug::Text('Getting request default data...', __FILE__, __LINE__, __METHOD__, 10);
		$data = array(
						'date_stamp' => TTDate::getAPIDate('DATE', TTDate::getTime() ),
					);

		return $this->returnHandler( $data );
	}

	/**
	 * Get hierarchy_level and hierarchy_control_ids for authorization list.
	 * @param int $type_id
	 * @return array
	 * @internal param int $object_type_id hierarchy object_type_id
	 */
	function getHierarchyLevelOptions( $type_id ) {
		$type_id = (array)$type_id;
		if ( is_array($type_id) AND count($type_id) > 0 ) {
			//If "ANY" is specified for the type_id, use all type_ids.
			if ( in_array(-1, $type_id) ) {
				$type_id = array_keys( $this->getOptions('type') );
			}
			Debug::Arr( $type_id, 'Type ID: ', __FILE__, __LINE__, __METHOD__, 10);

			$blf = TTnew( 'RequestListFactory' );
			$object_type_id = $blf->getHierarchyTypeId( $type_id );
			if ( isset($object_type_id) AND is_array($object_type_id) ) {
				$hl = new APIHierarchyLevel();
				return $hl->getHierarchyLevelOptions( $object_type_id );
			} else {
				Debug::Text( 'Invalid Request type ID!', __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * Get request data for one or more requestes.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function getRequest( $data = NULL, $disable_paging = FALSE ) {

		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( !$this->getPermissionObject()->Check('request', 'enabled')
				OR !( $this->getPermissionObject()->Check('request', 'view') OR $this->getPermissionObject()->Check('request', 'view_own') OR $this->getPermissionObject()->Check('request', 'view_child') ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		$blf = TTnew( 'RequestListFactory' );

		//If type_id and hierarchy_level is passed, assume we are in the authorization view.
		if ( isset($data['filter_data']['type_id']) AND is_array($data['filter_data']['type_id']) AND isset($data['filter_data']['hierarchy_level'])
				AND ( $this->getPermissionObject()->Check('authorization', 'enabled')
						AND $this->getPermissionObject()->Check('authorization', 'view')
						AND $this->getPermissionObject()->Check('request', 'authorize') ) ) {

			//FIXME: If type_id = -1 (ANY) is used, it may show more requests then if type_id is specified to a specific ID.
			//This is because if the hierarchy objects are changed when pending requests exist, the ANY type_id will catch them and display them,
			//But if you filter on type_id = <specific value> as well a specific hierarchy level, it may exclude them.

			//If "ANY" is selected, use all type_ids.
			if ( in_array(-1, $data['filter_data']['type_id']) ) {
				$data['filter_data']['type_id'] = array_keys( $this->getOptions('type') );
			}

			$hllf = TTnew( 'HierarchyLevelListFactory' );
			$hierarchy_level_arr = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $this->getCurrentUserObject()->getId(), $blf->getHierarchyTypeId( $data['filter_data']['type_id'] ) );
			Debug::Arr( $data['filter_data']['type_id'], 'Type ID: ', __FILE__, __LINE__, __METHOD__, 10);
			Debug::Arr( $blf->getHierarchyTypeId( $data['filter_data']['type_id'] ), 'Hierarchy Type ID: ', __FILE__, __LINE__, __METHOD__, 10);
			Debug::Arr( $hierarchy_level_arr, 'Hierarchy Levels: ', __FILE__, __LINE__, __METHOD__, 10);

			$data['filter_data']['hierarchy_level_map'] = FALSE;
			if ( isset($data['filter_data']['hierarchy_level']) AND isset($hierarchy_level_arr[$data['filter_data']['hierarchy_level']]) ) {
				$data['filter_data']['hierarchy_level_map'] = $hierarchy_level_arr[$data['filter_data']['hierarchy_level']];
			} elseif ( isset($hierarchy_level_arr[1]) ) {
				$data['filter_data']['hierarchy_level_map'] = $hierarchy_level_arr[1];
			}
			unset($hierarchy_level_arr);

			//Force other filter settings for authorization view.
			$data['filter_data']['authorized'] = array(0);
			$data['filter_data']['status_id'] = array(30);
			$data['filter_sort'] = array( 'status_id' => 'asc', 'date_stamp' => 'asc', 'type_id' => 'asc', 'last_name' => 'asc' );
		} else {
			Debug::Text('Not using authorization criteria...', __FILE__, __LINE__, __METHOD__, 10);
		}

		//Is this to too restrictive when authorizing requests, as they have to be in the permission hierarchy as well as the request hierarchy?
		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'request', 'view' );

		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
		Debug::Text('Record Count: '. $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $blf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $blf->getRecordCount() );

			$this->setPagerObject( $blf );

			$retarr = array();
			foreach( $blf as $b_obj ) {
				$retarr[] = $b_obj->getObjectAsArray( $data['filter_columns'], $data['filter_data']['permission_children_ids'] );

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $blf->getCurrentRow() );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( TRUE ); //No records returned.
	}

	/**
	 * @param string $format
	 * @param array $data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function exportRequest( $format = 'csv', $data = NULL, $disable_paging = TRUE ) {
		$result = $this->stripReturnHandler( $this->getRequest( $data, $disable_paging ) );
		return $this->exportRecords( $format, 'export_request', $result, ( ( isset($data['filter_columns']) ) ? $data['filter_columns'] : NULL ) );
	}
	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonRequestData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getRequest( $data, TRUE ) ) );
	}

	/**
	 * Validate request data for one or more requestes.
	 * @param array $data request data
	 * @return array
	 */
	function validateRequest( $data ) {
		return $this->setRequest( $data, TRUE );
	}

	/**
	 * Set request data for one or more requestes.
	 * @param array $data request data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setRequest( $data, $validate_only = FALSE, $ignore_warning = TRUE ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('request', 'enabled')
				OR !( $this->getPermissionObject()->Check('request', 'edit') OR $this->getPermissionObject()->Check('request', 'edit_own') OR $this->getPermissionObject()->Check('request', 'edit_child') OR $this->getPermissionObject()->Check('request', 'add') ) ) {
			return	$this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == TRUE ) {
			Debug::Text('Validating Only!', __FILE__, __LINE__, __METHOD__, 10);
			$permission_children_ids = FALSE;
		} else {
			//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
			$permission_children_ids = $this->getPermissionChildren();
		}

		list( $data, $total_records ) = $this->convertToMultipleRecords( $data );
		Debug::Text('Received data for: '. $total_records .' Requests', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		$validator = $save_result = $key = FALSE;
		if ( is_array( $data ) AND $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach ( $data as $key => $row ) {
				$primary_validator = $tertiary_validator = new Validator();
				$lf = TTnew( 'RequestListFactory' );
				$lf->StartTransaction();
				if ( isset( $row['id'] ) AND TTUUID::isUUID($row['id']) AND $row['id'] != TTUUID::getZeroID() AND $row['id'] != TTUUID::getNotExistID()) {
					//Modifying existing object.
					//Get request object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
								$validate_only == TRUE
								OR
								(
										$this->getPermissionObject()->Check( 'request', 'edit' )
										OR ( $this->getPermissionObject()->Check( 'request', 'edit_own' ) AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy() ) === TRUE )
										OR ( $this->getPermissionObject()->Check( 'request', 'edit_child' ) AND $this->getPermissionObject()->isChild( $lf->getCurrent()->getUser(), $permission_children_ids ) === TRUE )
								)
						) {
							Debug::Text('Row Exists, getting current data for ID: '. $row['id'], __FILE__, __LINE__, __METHOD__, 10);
							$lf = $lf->getCurrent();
							$row = array_merge( $lf->getObjectAsArray(), $row );
						} else {
							$primary_validator->isTrue( 'permission', FALSE, TTi18n::gettext( 'Edit permission denied' ) );
						}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext( 'Edit permission denied, record does not exist' ) );
					}
				} else {
					//Adding new object, check ADD permissions.
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check( 'request', 'add' ), TTi18n::gettext( 'Add permission denied' ) );

					//Because this class has sub-classes that depend on it, when adding a new record we need to make sure the ID is set first,
					//so the sub-classes can depend on it. We also need to call Save( TRUE, TRUE ) to force a lookup on isNew()
					$row['id'] = $lf->getNextInsertId();
				}
				Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

				if ( $validate_only == TRUE ) {
					$lf->Validator->setValidateOnly( $validate_only );
				}

				$is_valid = $primary_validator->isValid( $ignore_warning );
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Setting object data...', __FILE__, __LINE__, __METHOD__, 10 );

					$lf->setObjectFromArray( $row );
					//Save request_schedule here...
					if ( $this->getCurrentCompanyObject()->getProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
						if ( isset( $row['request_schedule'] ) AND is_array( $row['request_schedule'] ) AND count( $row['request_schedule'] ) > 0 ) {
							$rs_obj = TTnew( 'APIRequestSchedule' );
							foreach ( $row['request_schedule'] as $request_schedule_row ) {
								if ( is_array( $request_schedule_row ) ) {
									$request_schedule_row['request_id'] = $row['id'];
									$request_schedule_row['user_id'] = TTUUID::castUUID($row['user_id']);
									$tertiary_validator = $this->convertAPIreturnHandlerToValidatorObject( $rs_obj->setRequestSchedule( $request_schedule_row, $validate_only ), $tertiary_validator );
									$is_valid = $tertiary_validator->isValid( $ignore_warning );
								}
							}
							unset( $rs_obj );
						}
					}

					//Checking tertiary validity
					if ( $is_valid == TRUE ) {
						$is_valid = $lf->isValid( $ignore_warning );
						if ( $is_valid == TRUE ) {
							Debug::Text( 'Saving data...', __FILE__, __LINE__, __METHOD__, 10 );
							if ( $validate_only == TRUE ) {
								$save_result[$key] = TRUE;
							} else {
								$save_result[$key] = $lf->Save( TRUE, TRUE );
							}
							$validator_stats['valid_records']++;
						}
					}
				}

				if ( $is_valid == FALSE ) {
					Debug::Text( 'Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10 );

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					$validator[$key] = $this->setValidationArray( $primary_validator, $lf, $tertiary_validator );
				} elseif ( $validate_only == TRUE ) {
					$lf->FailTransaction();
				}

				$lf->CommitTransaction();

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * Delete one or more requests.
	 * @param array $data request data
	 * @return array|bool
	 */
	function deleteRequest( $data ) {
		if ( !is_array($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('request', 'enabled')
				OR !( $this->getPermissionObject()->Check('request', 'delete') OR $this->getPermissionObject()->Check('request', 'delete_own') OR $this->getPermissionObject()->Check('request', 'delete_child') ) ) {
			return	$this->getPermissionObject()->PermissionDenied();
		}

		Debug::Text('Received data for: '. count($data) .' Requests', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$total_records = count($data);
		$validator = $save_result = $key = FALSE;
		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) AND $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'RequestListFactory' );
				$lf->StartTransaction();
				if ( $id != '' ) {
					//Modifying existing object.
					//Get request object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check('request', 'delete')
								OR ( $this->getPermissionObject()->Check('request', 'delete_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE ) ) {
							Debug::Text('Record Exists, deleting record ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);
							$lf = $lf->getCurrent();
						} else {
							$primary_validator->isTrue( 'permission', FALSE, TTi18n::gettext('Delete permission denied') );
						}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Delete permission denied, record does not exist') );
					}
				} else {
					$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Delete permission denied, record does not exist') );
				}

				//Debug::Arr($lf, 'AData: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Attempting to delete record...', __FILE__, __LINE__, __METHOD__, 10);
					$lf->setDeleted(TRUE);

					$is_valid = $lf->isValid();
					if ( $is_valid == TRUE ) {
						Debug::Text('Record Deleted...', __FILE__, __LINE__, __METHOD__, 10);
						$save_result[$key] = $lf->Save();
						$validator_stats['valid_records']++;
					}
				}

				if ( $is_valid == FALSE ) {
					Debug::Text('Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10);

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					$validator[$key] = $this->setValidationArray( $primary_validator, $lf );
				}

				$lf->CommitTransaction();

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * Copy one or more requestes.
	 * @param array $data request IDs
	 * @return array
	 */
	function copyRequest( $data ) {
		if ( !is_array($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		Debug::Text('Received data for: '. count($data) .' Requests', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$src_rows = $this->stripReturnHandler( $this->getRequest( array('filter_data' => array('id' => $data) ), TRUE ) );
		if ( is_array( $src_rows ) AND count($src_rows) > 0 ) {
			Debug::Arr($src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);
			foreach( $src_rows as $key => $row ) {
				unset($src_rows[$key]['id'], $src_rows[$key]['manual_id'] ); //Clear fields that can't be copied
				$src_rows[$key]['name'] = Misc::generateCopyName( $row['name'] ); //Generate unique name
			}
			//Debug::Arr($src_rows, 'bSRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->setRequest( $src_rows ); //Save copied rows
		}

		return $this->returnHandler( FALSE );
	}
}
?>
