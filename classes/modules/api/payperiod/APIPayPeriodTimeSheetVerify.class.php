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
 * @package API\PayPeriod
 */
class APIPayPeriodTimeSheetVerify extends APIFactory {
	protected $main_class = 'PayPeriodTimeSheetVerifyFactory';

	/**
	 * APIPayPeriodTimeSheetVerify constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	/**
	 * Get default pay_period_timesheet_verify data for creating new pay_period_timesheet_verifyes.
	 * @return array
	 */
	function getPayPeriodTimeSheetVerifyDefaultData() {
		Debug::Text('Getting pay_period_timesheet_verify default data...', __FILE__, __LINE__, __METHOD__, 10);

		$data = array(
					);

		return $this->returnHandler( $data );
	}

	/**
	 * Get hierarchy_level and hierarchy_control_ids for authorization list.
	 * @param int $type_id ID
	 * @return array
	 * @internal param int $object_type_id hierarchy object_type_id
	 */
	function getHierarchyLevelOptions( $type_id = NULL ) {
		//Ignore type_id argument, as there is only one for this.

		$hl = new APIHierarchyLevel();
		return $hl->getHierarchyLevelOptions( array(90) );
	}

	/**
	 * Get pay_period_timesheet_verify data for one or more pay_period_timesheet_verifyes.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function getPayPeriodTimeSheetVerify( $data = NULL, $disable_paging = FALSE ) {
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( ( !$this->getPermissionObject()->Check('punch', 'enabled')
				OR !( $this->getPermissionObject()->Check('punch', 'view') OR $this->getPermissionObject()->Check('punch', 'view_own') OR $this->getPermissionObject()->Check('punch', 'view_child') ) )
			 	OR !$this->getPermissionObject()->Check('punch', 'authorize') ) { //Check that punch,authorize is also allowed.
			return $this->getPermissionObject()->PermissionDenied();
		}

		//If type_id and hierarchy_level is passed, assume we are in the authorization view.
		if ( isset($data['filter_data']['hierarchy_level'])
				AND ( $this->getPermissionObject()->Check('authorization', 'enabled')
						AND $this->getPermissionObject()->Check('authorization', 'view')
						AND $this->getPermissionObject()->Check('punch', 'authorize') ) ) {

			//FIXME: If type_id = -1 (ANY) is used, it may show more requests then if type_id is specified to a specific ID.
			//This is because if the hierarchy objects are changed when pending requests exist, the ANY type_id will catch them and display them,
			//But if you filter on type_id = <specific value> as well a specific hierarchy level, it may exclude them.

			$hllf = TTnew( 'HierarchyLevelListFactory' );
			$hierarchy_level_arr = $hllf->getLevelsAndHierarchyControlIDsByUserIdAndObjectTypeID( $this->getCurrentUserObject()->getId(), 90 );
			//Debug::Arr( $hierarchy_level_arr, 'Hierarchy Levels: ', __FILE__, __LINE__, __METHOD__, 10);

			$data['filter_data']['hierarchy_level_map'] = FALSE;
			if ( isset($data['filter_data']['hierarchy_level']) AND isset($hierarchy_level_arr[$data['filter_data']['hierarchy_level']]) ) {
				$data['filter_data']['hierarchy_level_map'] = $hierarchy_level_arr[$data['filter_data']['hierarchy_level']];
			} elseif ( isset($hierarchy_level_arr[1]) ) {
				$data['filter_data']['hierarchy_level_map'] = $hierarchy_level_arr[1];
			}
			unset($hierarchy_level_arr);

			//Force other filter settings for authorization view.
			$data['filter_data']['authorized'] = array(0);
			//$data['filter_data']['status_id'] = array(30);
		} else {
			Debug::Text('Not using authorization criteria...', __FILE__, __LINE__, __METHOD__, 10);
		}

		//Is this to too restrictive when authorizing requests, as they have to be in the permission hierarchy as well as the request hierarchy?
		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'punch', 'view' );

		$blf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
		$blf->getAPIAuthorizationSearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
		Debug::Text('Record Count: '. $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $blf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $blf->getRecordCount() );

			$this->setPagerObject( $blf );

			$retarr = array();
			foreach( $blf as $b_obj ) {
				$retarr[] = $b_obj->getObjectAsArray( $data['filter_columns'] );

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
	function exportPayPeriodTimeSheetVerify( $format = 'csv', $data = NULL, $disable_paging = TRUE ) {
		$result = $this->stripReturnHandler( $this->getPayPeriodTimeSheetVerify( $data, $disable_paging ) );
		return $this->exportRecords( $format, 'export_timesheet_verify', $result, ( ( isset($data['filter_columns']) ) ? $data['filter_columns'] : NULL ) );
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonPayPeriodTimeSheetVerifyData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getPayPeriodTimeSheetVerify( $data, TRUE ) ) );
	}

	/**
	 * Validate pay_period_timesheet_verify data for one or more pay_period_timesheet_verifyes.
	 * @param array $data pay_period_timesheet_verify data
	 * @return array
	 */
	function validatePayPeriodTimeSheetVerify( $data ) {
		return $this->setPayPeriodTimeSheetVerify( $data, TRUE );
	}

	/**
	 * Set pay_period_timesheet_verify data for one or more pay_period_timesheet_verifyes.
	 * @param array $data pay_period_timesheet_verify data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setPayPeriodTimeSheetVerify( $data, $validate_only = FALSE, $ignore_warning = TRUE ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('punch', 'enabled')
				OR !( $this->getPermissionObject()->Check('punch', 'edit') OR $this->getPermissionObject()->Check('punch', 'edit_own') OR $this->getPermissionObject()->Check('punch', 'edit_child') OR $this->getPermissionObject()->Check('punch', 'add') ) ) {
			return	$this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == TRUE ) {
			Debug::Text('Validating Only!', __FILE__, __LINE__, __METHOD__, 10);
		}

		list( $data, $total_records ) = $this->convertToMultipleRecords( $data );
		Debug::Text('Received data for: '. $total_records .' PayPeriodTimeSheetVerifys', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		$validator = $save_result = $key = FALSE;
		if ( is_array($data) AND $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
				$lf->StartTransaction();
				if ( isset($row['id']) AND $row['id'] != '' ) {
					//Modifying existing object.
					//Get pay_period_timesheet_verify object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
							$validate_only == TRUE
							OR
								(
								$this->getPermissionObject()->Check('punch', 'edit')
									OR ( $this->getPermissionObject()->Check('punch', 'edit_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE )
								) ) {

							Debug::Text('Row Exists, getting current data for ID: '. $row['id'], __FILE__, __LINE__, __METHOD__, 10);
							$lf = $lf->getCurrent();
							$row = array_merge( $lf->getObjectAsArray(), $row );
						} else {
							$primary_validator->isTrue( 'permission', FALSE, TTi18n::gettext('Edit permission denied') );
						}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Edit permission denied, record does not exist') );
					}
				} else {
					//Adding new object, check ADD permissions.
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check('punch', 'add'), TTi18n::gettext('Add permission denied') );
				}
				Debug::Arr($row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid( $ignore_warning );
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Setting object data...', __FILE__, __LINE__, __METHOD__, 10);

					$lf->setObjectFromArray( $row );

					//Force current user.
					$lf->setCurrentUser( $this->getCurrentUserObject()->getID() );

					$is_valid = $lf->isValid( $ignore_warning );
					if ( $is_valid == TRUE ) {
						Debug::Text('Saving data...', __FILE__, __LINE__, __METHOD__, 10);
						if ( $validate_only == TRUE ) {
							$save_result[$key] = TRUE;
						} else {
							$save_result[$key] = $lf->Save();
						}
						$validator_stats['valid_records']++;
					}
				}

				if ( $is_valid == FALSE ) {
					Debug::Text('Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10);

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					$validator[$key] = $this->setValidationArray( $primary_validator, $lf );
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
	 * Delete one or more pay_period_timesheet_verifys.
	 * @param array $data pay_period_timesheet_verify data
	 * @return array|bool
	 */
	function deletePayPeriodTimeSheetVerify( $data ) {
		if ( !is_array($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('punch', 'enabled')
				OR !( $this->getPermissionObject()->Check('punch', 'delete') OR $this->getPermissionObject()->Check('punch', 'delete_own') OR $this->getPermissionObject()->Check('punch', 'delete_child') ) ) {
			return	$this->getPermissionObject()->PermissionDenied();
		}

		Debug::Text('Received data for: '. count($data) .' PayPeriodTimeSheetVerifys', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$total_records = count($data);
		$validator = $save_result = $key = FALSE;
		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) AND $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
				$lf->StartTransaction();
				if ( $id != '' ) {
					//Modifying existing object.
					//Get pay_period_timesheet_verify object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check('punch', 'delete')
								OR ( $this->getPermissionObject()->Check('punch', 'delete_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE ) ) {
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
}
?>
