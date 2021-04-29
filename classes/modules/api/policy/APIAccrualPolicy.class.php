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
 * @package API\Policy
 */
class APIAccrualPolicy extends APIFactory {
	protected $main_class = 'AccrualPolicyFactory';

	/**
	 * APIAccrualPolicy constructor.
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
				&& ( !$this->getPermissionObject()->Check( 'accrual_policy', 'enabled' )
						|| !( $this->getPermissionObject()->Check( 'accrual_policy', 'view' ) || $this->getPermissionObject()->Check( 'accrual_policy', 'view_own' ) || $this->getPermissionObject()->Check( 'accrual_policy', 'view_child' ) ) ) ) {
			$name = 'list_columns';
		}

		return parent::getOptions( $name, $parent );
	}

	/**
	 * Get default accrual_policy data for creating new accrual_policyes.
	 * @return array
	 */
	function getAccrualPolicyDefaultData() {
		$company_obj = $this->getCurrentCompanyObject();

		Debug::Text( 'Getting accrual_policy default data...', __FILE__, __LINE__, __METHOD__, 10 );

		$data = [
				'company_id'                     => $company_obj->getId(),
				'type_id'                        => 20,
				'minimum_employed_days'          => 0,
				'milestone_rollover_hire_date'   => true,
				'enable_pro_rate_initial_period' => true,
		];

		return $this->returnHandler( $data );
	}

	/**
	 * Get accrual_policy data for one or more accrual_policyes.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array
	 */
	function getAccrualPolicy( $data = null, $disable_paging = false ) {
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( !$this->getPermissionObject()->Check( 'accrual_policy', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'accrual_policy', 'view' ) || $this->getPermissionObject()->Check( 'accrual_policy', 'view_own' ) || $this->getPermissionObject()->Check( 'accrual_policy', 'view_child' ) ) ) {
			//return $this->getPermissionObject()->PermissionDenied();
			$data['filter_columns'] = $this->handlePermissionFilterColumns( ( isset( $data['filter_columns'] ) ) ? $data['filter_columns'] : null, Misc::trimSortPrefix( $this->getOptions( 'list_columns' ) ) );
		}

		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'accrual_policy', 'view' );

		$blf = TTnew( 'AccrualPolicyListFactory' ); /** @var AccrualPolicyListFactory $blf */
		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], null, $data['filter_sort'] );
		Debug::Text( 'Record Count: ' . $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $blf->getRecordCount() > 0 ) {
			$this->setPagerObject( $blf );

			$retarr = [];
			foreach ( $blf as $b_obj ) {
				$retarr[] = $b_obj->getObjectAsArray( $data['filter_columns'] );
			}

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( true ); //No records returned.
	}

	/**
	 * @param string $format
	 * @param array $data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function exportAccrualPolicy( $format = 'csv', $data = null, $disable_paging = true ) {
		$result = $this->stripReturnHandler( $this->getAccrualPolicy( $data, $disable_paging ) );

		return $this->exportRecords( $format, 'export_accrual_policy', $result, ( ( isset( $data['filter_columns'] ) ) ? $data['filter_columns'] : null ) );
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonAccrualPolicyData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getAccrualPolicy( $data, true ) ) );
	}

	/**
	 * Validate accrual_policy data for one or more accrual_policyes.
	 * @param array $data accrual_policy data
	 * @return array
	 */
	function validateAccrualPolicy( $data ) {
		return $this->setAccrualPolicy( $data, true );
	}

	/**
	 * Set accrual_policy data for one or more accrual_policyes.
	 * @param array $data accrual_policy data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setAccrualPolicy( $data, $validate_only = false, $ignore_warning = true ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( !$this->getPermissionObject()->Check( 'accrual_policy', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'accrual_policy', 'edit' ) || $this->getPermissionObject()->Check( 'accrual_policy', 'edit_own' ) || $this->getPermissionObject()->Check( 'accrual_policy', 'edit_child' ) || $this->getPermissionObject()->Check( 'accrual_policy', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == true ) {
			Debug::Text( 'Validating Only!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		list( $data, $total_records ) = $this->convertToMultipleRecords( $data );
		Debug::Text( 'Received data for: ' . $total_records . ' AccrualPolicys', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			foreach ( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'AccrualPolicyListFactory' ); /** @var AccrualPolicyListFactory $lf */
				$lf->StartTransaction();
				if ( isset( $row['id'] ) && $row['id'] != '' ) {
					//Modifying existing object.
					//Get accrual_policy object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
								$validate_only == true
								||
								(
										$this->getPermissionObject()->Check( 'accrual_policy', 'edit' )
										|| ( $this->getPermissionObject()->Check( 'accrual_policy', 'edit_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true )
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
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check( 'accrual_policy', 'add' ), TTi18n::gettext( 'Add permission denied' ) );
				}
				Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

				$is_valid = $primary_validator->isValid( $ignore_warning );
				if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Setting object data...', __FILE__, __LINE__, __METHOD__, 10 );

					//Force Company ID to current company.
					$row['company_id'] = $this->getCurrentCompanyObject()->getId();

					$lf->setObjectFromArray( $row );
					$lf->Validator->setValidateOnly( $validate_only );

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
	 * Delete one or more accrual_policys.
	 * @param array $data accrual_policy data
	 * @return array|bool
	 */
	function deleteAccrualPolicy( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( !$this->getPermissionObject()->Check( 'accrual_policy', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'accrual_policy', 'delete' ) || $this->getPermissionObject()->Check( 'accrual_policy', 'delete_own' ) || $this->getPermissionObject()->Check( 'accrual_policy', 'delete_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' AccrualPolicys', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$total_records = count( $data );
		$validator = $save_result = $key = false;
		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		if ( is_array( $data ) && $total_records > 0 ) {
			foreach ( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'AccrualPolicyListFactory' ); /** @var AccrualPolicyListFactory $lf */
				$lf->StartTransaction();
				if ( $id != '' ) {
					//Modifying existing object.
					//Get accrual_policy object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check( 'accrual_policy', 'delete' )
								|| ( $this->getPermissionObject()->Check( 'accrual_policy', 'delete_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true ) ) {
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
	 * Copy one or more accrual_policyes.
	 * @param array $data accrual_policy IDs
	 * @return array
	 */
	function copyAccrualPolicy( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' AccrualPolicys', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$src_rows = $this->stripReturnHandler( $this->getAccrualPolicy( [ 'filter_data' => [ 'id' => $data ] ], true ) );
		if ( is_array( $src_rows ) && count( $src_rows ) > 0 ) {
			$original_ids = [];
			Debug::Arr( $src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $src_rows as $key => $row ) {
				$original_ids[$key] = $src_rows[$key]['id'];
				unset( $src_rows[$key]['id'] );                                   //Clear fields that can't be copied
				$src_rows[$key]['name'] = Misc::generateCopyName( $row['name'] ); //Generate unique name
			}
			//Debug::Arr($src_rows, 'bSRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);

			$retval = $this->setAccrualPolicy( $src_rows ); //Save copied rows

			//Now we need to loop through the result set, and copy the milestones as well.
			if ( empty( $original_ids ) == false ) {
				Debug::Arr( $original_ids, ' Original IDs: ', __FILE__, __LINE__, __METHOD__, 10 );
				Debug::Arr( $retval, ' New IDs: ', __FILE__, __LINE__, __METHOD__, 10 );

				foreach ( $original_ids as $key => $original_id ) {
					$new_id = null;
					if ( is_array( $retval ) ) {
						if ( isset( $retval['api_retval'] )
								&& TTUUID::isUUID( $retval['api_retval'] ) && $retval['api_retval'] != TTUUID::getZeroID() && $retval['api_retval'] != TTUUID::getNotExistID() ) {
							$new_id = $retval['api_retval'];
						} else if ( isset( $retval['api_details']['details'][$key] ) ) {
							$new_id = $retval['api_details']['details'][$key];
						}
					} else if ( TTUUID::isUUID( $retval ) ) {
						$new_id = $retval;
					}

					if ( TTUUID::isUUID( $new_id ) ) {
						//Get milestones by original_id.
						$apmlf = TTnew( 'AccrualPolicyMilestoneListFactory' ); /** @var AccrualPolicyMilestoneListFactory $apmlf */
						$apmlf->getByAccrualPolicyID( $original_id );
						if ( $apmlf->getRecordCount() > 0 ) {
							foreach ( $apmlf as $apm_obj ) {
								Debug::Text( 'Copying Milestone ID: ' . $apm_obj->getID() . ' To Accrual Policy: ' . $new_id, __FILE__, __LINE__, __METHOD__, 10 );

								//Copy milestone to new_id
								$apm_obj->setId( false );
								$apm_obj->setAccrualPolicy( $new_id );
								if ( $apm_obj->isValid() ) {
									$apm_obj->Save();
								}
							}
						}
					}
				}
			}

			return $retval;
		}

		return $this->returnHandler( false );
	}

	/**
	 * ReCalculate accrual policies
	 * @param string $accrual_policy_ids UUID
	 * @param $time_period_arr
	 * @param string $user_ids           UUID
	 * @return array|bool
	 */
	function reCalculateAccrual( $accrual_policy_ids, $time_period_arr, $user_ids = null ) {
		//Debug::text('Recalculating Employee Timesheet: User ID: '. $user_ids .' Pay Period ID: '. $pay_period_ids, __FILE__, __LINE__, __METHOD__, 10);
		//Debug::setVerbosity(11);

		if ( !$this->getPermissionObject()->Check( 'accrual_policy', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'accrual_policy', 'edit' ) || $this->getPermissionObject()->Check( 'accrual_policy', 'edit_child' ) || $this->getPermissionObject()->Check( 'accrual_policy', 'edit_own' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( Misc::isSystemLoadValid() == false ) { //Check system load before anything starts.
			Debug::Text( 'ERROR: System load exceeded, preventing new recalculation processes from starting...', __FILE__, __LINE__, __METHOD__, 10 );

			return $this->returnHandler( false );
		}

		$report_obj = TTNew( 'Report' ); /** @var Report $report_obj */
		$report_obj->setUserObject( $this->getCurrentUserObject() );
		$date_arr = $report_obj->convertTimePeriodToStartEndDate( $time_period_arr, null, true ); //Force start/end dates even if pay periods selected.
		Debug::Arr( $date_arr, 'Date Arr', __FILE__, __LINE__, __METHOD__, 10 );

		if ( isset( $date_arr['start_date'] ) && isset( $date_arr['end_date'] ) ) {
			$total_days = TTDate::getDays( ( $date_arr['end_date'] - $date_arr['start_date'] ) );

			$aplf = TTnew( 'AccrualPolicyListFactory' ); /** @var AccrualPolicyListFactory $aplf */
			$aplf->getByIdAndCompanyId( (array)$accrual_policy_ids, $this->getCurrentCompanyObject()->getId() );
			if ( $aplf->getRecordCount() > 0 ) {
				$this->getProgressBarObject()->start( $this->getAPIMessageID(), $aplf->getRecordCount(), null, TTi18n::getText( 'ReCalculating...' ) );

				foreach ( $aplf as $ap_obj ) {
					if ( Misc::isSystemLoadValid() == false ) { //Check system load as the user could ask to calculate decades worth at a time.
						Debug::Text( 'ERROR: System load exceeded, stopping recalculation... (a)', __FILE__, __LINE__, __METHOD__, 10 );
						break;
					}

					$aplf->StartTransaction();

					TTLog::addEntry( $this->getCurrentUserObject()->getId(), 500, 'Recalculate Accrual Policy: ' . $ap_obj->getName() . ' Start Date: ' . TTDate::getDate( 'DATE', $date_arr['start_date'] ) . ' End Date: ' . TTDate::getDate( 'DATE', $date_arr['end_date'] ) . ' Total Days: ' . round( $total_days ), $this->getCurrentUserObject()->getId(), $ap_obj->getTable() );

					$x = 0;
					for ( $i = $date_arr['start_date']; $i < $date_arr['end_date']; $i += ( 86400 ) ) {
						if ( ( $x % 100 ) == 0 && Misc::isSystemLoadValid() == false ) { //Check system load as the user could ask to calculate decades worth at a time.
							Debug::Text( 'ERROR: System load exceeded, stopping recalculation... (b)', __FILE__, __LINE__, __METHOD__, 10 );
							break;
						}

						//$i = TTDate::getBeginDayEpoch( $i ); //This causes infinite loops during DST transitions.
						Debug::Text( 'Recalculating Accruals for Date: ' . TTDate::getDate( 'DATE+TIME', TTDate::getBeginDayEpoch( $i ) ), __FILE__, __LINE__, __METHOD__, 10 );
						$ap_obj->addAccrualPolicyTime( TTDate::getBeginDayEpoch( $i ), 79200, $user_ids ); //Use default offset.

						$this->getProgressBarObject()->set( $this->getAPIMessageID(), $x );

						$x++;
					}

					//$aplf->FailTransaction();
					$aplf->CommitTransaction();
				}

				$this->getProgressBarObject()->stop( $this->getAPIMessageID() );
			} else {
				Debug::Text( 'No accrual policies to recalculate...', __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			Debug::Text( 'No dates to calculate accrual policies for...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $this->returnHandler( true );
	}

}

?>
