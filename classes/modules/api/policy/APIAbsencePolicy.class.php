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
 * @package API\Policy
 */
class APIAbsencePolicy extends APIFactory {
	protected $main_class = 'AbsencePolicyFactory';

	/**
	 * APIAbsencePolicy constructor.
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
				&& ( !$this->getPermissionObject()->Check( 'absence_policy', 'enabled' )
						|| !( $this->getPermissionObject()->Check( 'absence_policy', 'view' ) || $this->getPermissionObject()->Check( 'absence_policy', 'view_own' ) || $this->getPermissionObject()->Check( 'absence_policy', 'view_child' ) ) ) ) {
			$name = 'list_columns';
		}

		return parent::getOptions( $name, $parent );
	}

	/**
	 * Get default absence policy data for creating new absence policyes.
	 * @return array
	 */
	function getAbsencePolicyDefaultData() {
		$company_obj = $this->getCurrentCompanyObject();

		Debug::Text( 'Getting absence policy default data...', __FILE__, __LINE__, __METHOD__, 10 );

		$data = [
				'company_id'   => $company_obj->getId(),
				'rate'         => '1.00',
				'accrual_rate' => '1.00',
		];

		return $this->returnHandler( $data );
	}

	/**
	 * Get absence policy data for one or more absence policyes.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array
	 */
	function getAbsencePolicy( $data = null, $disable_paging = false ) {
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( !$this->getPermissionObject()->Check( 'absence_policy', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'absence_policy', 'view' ) || $this->getPermissionObject()->Check( 'absence_policy', 'view_own' ) || $this->getPermissionObject()->Check( 'absence_policy', 'view_child' ) ) ) {
			//return $this->getPermissionObject()->PermissionDenied();
			$data['filter_columns'] = $this->handlePermissionFilterColumns( ( isset( $data['filter_columns'] ) ) ? $data['filter_columns'] : null, Misc::trimSortPrefix( $this->getOptions( 'list_columns' ) ) );
		}
		/*
		//Handle this in the SQL query directly with the user_id filter.
		//Make sure we filter absence policies to just those assigned to the policy group when user_id filter is passed.
		if ( isset( $data['filter_data']['user_id'] ) ) {
			$user_ids = (array)$data['filter_data']['user_id'];

			$pgulf = new PolicyGroupUserListFactory();
			$pgulf->getByUserId( $user_ids );
			if ( $pgulf->getRecordCount() > 0 ) {
				$pguf_obj = $pgulf->getCurrent();
				$policy_group_id = $pguf_obj->getPolicyGroup();
			}
			if ( isset($policy_group_id) ) {
				$cgmlf = new CompanyGenericMapListFactory();
				$cgmlf->getByObjectTypeAndObjectID( 170, $policy_group_id );
				if ( $cgmlf->getRecordCount() > 0 ) {
					foreach( $cgmlf as $cgm_obj ) {
						$absence_policy_ids[] = $cgm_obj->getMapID();
					}
				}
			}

			if ( isset( $absence_policy_ids ) ) {
				$data['filter_data']['id'] = $absence_policy_ids;
			} else {
				//Make sure that is no absence policies are assigned to the policy group, we don't display any.
				$data['filter_data']['id'] = array(0);
			}
			unset( $data['filter_data']['user_id'] );
		}
		*/

		if ( isset( $data['filter_data']['user_id'] ) && !is_array( $data['filter_data']['user_id'] ) ) {
			$data['filter_data']['user_id'] = (array)$data['filter_data']['user_id'];
		}

		//Remove any user_id=0 as its for an OPEN shift and no absence policy is ever assigned to this user in the policy groups.
		if ( isset( $data['filter_data']['user_id'] ) && in_array( TTUUID::getZeroId(), $data['filter_data']['user_id'] ) ) {
			$open_user_id_key = array_search( TTUUID::getZeroID(), $data['filter_data']['user_id'] );
			if ( $open_user_id_key !== false ) {
				Debug::Text( 'Removing user_id=0 from filter...', __FILE__, __LINE__, __METHOD__, 10 );
				unset( $data['filter_data']['user_id'][$open_user_id_key] );
			}
		}

		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'absence_policy', 'view' );

		$blf = TTnew( 'AbsencePolicyListFactory' ); /** @var AbsencePolicyListFactory $blf */
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
	function exportAbsencePolicy( $format = 'csv', $data = null, $disable_paging = true ) {
		$result = $this->stripReturnHandler( $this->getAbsencePolicy( $data, $disable_paging ) );

		return $this->exportRecords( $format, 'export_absence_policy', $result, ( ( isset( $data['filter_columns'] ) ) ? $data['filter_columns'] : null ) );
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonAbsencePolicyData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getAbsencePolicy( $data, true ) ) );
	}

	/**
	 * Validate absence policy data for one or more absence policyes.
	 * @param array $data absence policy data
	 * @return array
	 */
	function validateAbsencePolicy( $data ) {
		return $this->setAbsencePolicy( $data, true );
	}

	/**
	 * Set absence policy data for one or more absence policyes.
	 * @param array $data absence policy data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setAbsencePolicy( $data, $validate_only = false, $ignore_warning = true ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( !$this->getPermissionObject()->Check( 'absence_policy', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'absence_policy', 'edit' ) || $this->getPermissionObject()->Check( 'absence_policy', 'edit_own' ) || $this->getPermissionObject()->Check( 'absence_policy', 'edit_child' ) || $this->getPermissionObject()->Check( 'absence_policy', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == true ) {
			Debug::Text( 'Validating Only!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		list( $data, $total_records ) = $this->convertToMultipleRecords( $data );
		Debug::Text( 'Received data for: ' . $total_records . ' AbsencePolicys', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			foreach ( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'AbsencePolicyListFactory' ); /** @var AbsencePolicyListFactory $lf */
				$lf->StartTransaction();
				if ( isset( $row['id'] ) && $row['id'] != '' ) {
					//Modifying existing object.
					//Get absence policy object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
								$validate_only == true
								||
								(
										$this->getPermissionObject()->Check( 'absence_policy', 'edit' )
										|| ( $this->getPermissionObject()->Check( 'absence_policy', 'edit_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true )
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
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check( 'absence_policy', 'add' ), TTi18n::gettext( 'Add permission denied' ) );
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
	 * Delete one or more absence policys.
	 * @param array $data absence policy data
	 * @return array|bool
	 */
	function deleteAbsencePolicy( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( !$this->getPermissionObject()->Check( 'absence_policy', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'absence_policy', 'delete' ) || $this->getPermissionObject()->Check( 'absence_policy', 'delete_own' ) || $this->getPermissionObject()->Check( 'absence_policy', 'delete_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' AbsencePolicys', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$total_records = count( $data );
		$validator = $save_result = $key = false;
		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		if ( is_array( $data ) && $total_records > 0 ) {
			foreach ( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'AbsencePolicyListFactory' ); /** @var AbsencePolicyListFactory $lf */
				$lf->StartTransaction();
				if ( $id != '' ) {
					//Modifying existing object.
					//Get absence policy object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check( 'absence_policy', 'delete' )
								|| ( $this->getPermissionObject()->Check( 'absence_policy', 'delete_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true ) ) {
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
	 * Copy one or more absence policyes.
	 * @param array $data absence policy IDs
	 * @return array
	 */
	function copyAbsencePolicy( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' AbsencePolicys', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$src_rows = $this->stripReturnHandler( $this->getAbsencePolicy( [ 'filter_data' => [ 'id' => $data ] ], true ) );
		if ( is_array( $src_rows ) && count( $src_rows ) > 0 ) {
			Debug::Arr( $src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $src_rows as $key => $row ) {
				unset( $src_rows[$key]['id'], $src_rows[$key]['manual_id'] );     //Clear fields that can't be copied
				$src_rows[$key]['name'] = Misc::generateCopyName( $row['name'] ); //Generate unique name
			}

			//Debug::Arr($src_rows, 'bSRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->setAbsencePolicy( $src_rows ); //Save copied rows
		}

		return $this->returnHandler( false );
	}

	/**
	 * @param string $accrual_policy_id UUID
	 * @param string $user_id           UUID
	 * @return array|bool
	 */
	function getAccrualBalance( $accrual_policy_id, $user_id ) {
		if ( $accrual_policy_id == '' ) {
			return false;
		}
		if ( $user_id == '' ) {
			return false;
		}

		$ablf = TTnew( 'AccrualBalanceListFactory' ); /** @var AccrualBalanceListFactory $ablf */
		$ablf->getByUserIdAndAccrualPolicyId( TTUUID::castUUID( $user_id ), TTUUID::castUUID( $accrual_policy_id ) );
		if ( $ablf->getRecordCount() > 0 ) {
			$accrual_balance = $ablf->getCurrent()->getBalance();
		} else {
			$accrual_balance = 0;
		}

		return $this->returnHandler( TTDate::getTimeUnit( $accrual_balance ) );
	}

	/**
	 * @param string $absence_policy_id UUID
	 * @param string $user_id           UUID
	 * @return array
	 */
	function getAbsencePolicyBalance( $absence_policy_id, $user_id ) {
		if ( $absence_policy_id == '' ) {
			return $this->returnHandler( false );
		}

		if ( $user_id == '' ) {
			return $this->returnHandler( false );
		}

		$aplf = TTnew( 'AbsencePolicyListFactory' ); /** @var AbsencePolicyListFactory $aplf */
		$aplf->getByIdAndCompanyId( $absence_policy_id, $this->getCurrentCompanyObject()->getId() );
		if ( $aplf->getRecordCount() > 0 ) {
			$ap_obj = $aplf->getCurrent();
			if ( $ap_obj->getAccrualPolicyID() != '' ) {
				return $this->returnHandler( $this->getAccrualBalance( $ap_obj->getAccrualPolicyID(), $user_id ) );
			}
		}

		return $this->returnHandler( false );
	}

	/**
	 * @param string $absence_policy_id UUID
	 * @param string $user_id           UUID
	 * @param int $epoch                EPOCH
	 * @param $amount
	 * @param int $previous_amount
	 * @param bool $previous_absence_policy_id
	 * @return array
	 */
	function getProjectedAbsencePolicyBalance( $absence_policy_id, $user_id, $epoch, $amount, $previous_amount = 0, $previous_absence_policy_id = false ) {
		if ( $absence_policy_id == '' || $absence_policy_id == TTUUID::getZeroID() ) {
			return $this->returnHandler( false );
		}

		if ( $user_id == '' || $absence_policy_id == TTUUID::getZeroID() ) {
			return $this->returnHandler( false );
		}

		$user_id = TTUUID::castUUID( $user_id );

		$epoch = TTDate::parseDateTime( $epoch );

		$aplf = TTnew( 'AbsencePolicyListFactory' ); /** @var AbsencePolicyListFactory $aplf */
		$aplf->getByIdAndCompanyId( $absence_policy_id, $this->getCurrentCompanyObject()->getId() );
		if ( $aplf->getRecordCount() > 0 ) {
			$ap_obj = $aplf->getCurrent();
			$pfp_obj = $ap_obj->getPayFormulaPolicyObject();

			$prev_aplf = TTnew( 'AbsencePolicyListFactory' ); /** @var AbsencePolicyListFactory $prev_aplf */
			$prev_aplf->getByIdAndCompanyId( $previous_absence_policy_id, $this->getCurrentCompanyObject()->getId() );

			$accrual_rate = ( is_object( $pfp_obj ) ) ? $pfp_obj->getAccrualRate() : ( -1 );

			Debug::Text( 'Before Accrual Rate: Amount: ' . $amount . ' Prev Amount: ' . $previous_amount, __FILE__, __LINE__, __METHOD__, 10 );
			$amount = ( $amount * $accrual_rate );
			$previous_amount = ( $previous_amount * $accrual_rate );
			Debug::Text( 'After Accrual Rate: Amount: ' . $amount . ' Prev Amount: ' . $previous_amount, __FILE__, __LINE__, __METHOD__, 10 );

			//Get Wage Permission Hierarchy Children first, as this can be used for viewing, or editing.
			$wage_permission_children_ids = $this->getPermissionObject()->getPermissionChildren( 'wage', 'view' );
			if ( $this->getPermissionObject()->isPermissionChild( $user_id, $wage_permission_children_ids ) ) {
				//Check for links to Pay Stub Account accruals, to get dollar amounts too.
				if ( is_object( $ap_obj->getPayCodeObject() )
						&& is_object( $ap_obj->getPayCodeObject()->getPayStubEntryAccountObject() )
						&& is_object( $pfp_obj ) ) {
					$pay_stub_entry_account_accrual_id = $ap_obj->getPayCodeObject()->getPayStubEntryAccountObject()->getAccrual();

					$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */
					$pay_stub_entry_account_data = $pself->getLastSumByUserIdAndEntryNameIdAndDate( $user_id, $pay_stub_entry_account_accrual_id, $epoch );
					if ( isset( $pay_stub_entry_account_data['ytd_amount'] ) && $pay_stub_entry_account_data['ytd_amount'] !== null ) {
						$pclf = TTnew( 'PayCodeListFactory' ); /** @var PayCodeListFactory $pclf */
						$accrual_account_pay_code_ids = (array)$pclf->getIDSByListFactory( $pclf->getByCompanyIdAndAccrualPayStubEntryAccountID( $this->getCurrentCompanyObject()->getId(), $pay_stub_entry_account_accrual_id ) );

						//Get all UserDateTotal records after the last pay stub date, so we can include dollar amounts that havne't appeared on pay stubs yet.
						$udtlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udtlf */
						$udt_sum_arr = $udtlf->getSumByUserIDAndObjectTypeIDAndPayCodeIDAndStartDateAndEndDate( $user_id, 25, $accrual_account_pay_code_ids, TTDate::getBeginDayEpoch( strtotime( $pay_stub_entry_account_data['end_date'] ) + 7200 ), ( time() + ( 86400 * 365 ) ) );
						Debug::Arr( [ $udt_sum_arr, $accrual_account_pay_code_ids ], 'UDT Sum array.  SRC Object ID: ' . $ap_obj->getID() . ' Start Date: ' . TTDate::getDate( 'DATE+TIME', TTDate::getBeginDayEpoch( strtotime( $pay_stub_entry_account_data['end_date'] ) + 7200 ) ), __FILE__, __LINE__, __METHOD__, 10 );

						$uwlf = TTnew( 'UserWageListFactory' ); /** @var UserWageListFactory $uwlf */
						$uwlf->getByUserIdAndGroupIDAndBeforeDate( $user_id, $pfp_obj->getWageGroup(), $epoch, 1 );

						$dollar_previous_amount = 0;

						if ( $uwlf->getRecordCount() > 0 ) {
							$dollar_amount = ( TTDate::getHours( $amount ) * $pfp_obj->getHourlyRate( $uwlf->getCurrent()->getHourlyRate() ) );
							if ( $prev_aplf->getRecordCount() > 0 ) {
								$prev_pc_obj = $prev_aplf->getCurrent()->getPayCodeObject();

								if ( is_object( $prev_pc_obj )
										&& ( is_object( $prev_pc_obj->getPayStubEntryAccountObject() )
												&&
												( is_object( $prev_pc_obj->getPayStubEntryAccountObject() )
														&& $pay_stub_entry_account_accrual_id == $prev_pc_obj->getPayStubEntryAccountObject()->getAccrual()
												)
										)
								) {
									$dollar_previous_amount = ( TTDate::getHours( $previous_amount ) * $pfp_obj->getHourlyRate( $uwlf->getCurrent()->getHourlyRate() ) );
									Debug::Text( 'Pay stub entry account accrual has not changed, setting previous amount.', __FILE__, __LINE__, __METHOD__, 10 );
								}
							}
						}

						$available_dollar_balance = ( ( $pay_stub_entry_account_data['ytd_amount'] - $dollar_previous_amount ) - $udt_sum_arr['total_time_amount'] );

						$dollar_retarr = [
								'available_dollar_balance' => Misc::MoneyFormat( $available_dollar_balance, false ),
								'current_dollar_amount'    => Misc::MoneyFormat( $dollar_amount, false ),
								'remaining_dollar_balance' => Misc::MoneyFormat( ( $available_dollar_balance + $dollar_amount ), false ),
						];
						Debug::Arr( $dollar_retarr, 'Dollar Amount: ' . $dollar_amount . ' Previous: ' . $dollar_previous_amount . ' Dollar Accrual: ', __FILE__, __LINE__, __METHOD__, 10 );
					}
				}
			}

			if ( is_object( $pfp_obj ) && $pfp_obj->getAccrualPolicyAccount() != '' ) {
				//The previous amount is cleared when the accrual policy (by way of absence policy) is changed to prevent miscalculation of remaining accrued time.
				if ( $prev_aplf->getRecordCount() > 0 ) {
					$prev_pfp_obj = $prev_aplf->getCurrent()->getPayFormulaPolicyObject();
					if ( is_object( $prev_pfp_obj ) && TTUUID::castUUID( $pfp_obj->getAccrualPolicyAccount() ) != TTUUID::castUUID( $prev_pfp_obj->getAccrualPolicyAccount() ) ) {
						Debug::Text( 'Accrual policy has been changed clearing previous amount.', __FILE__, __LINE__, __METHOD__, 10 );
						$previous_amount = 0;
					}
				}

				$aplf = new AccrualPolicyListFactory();
				$aplf->getByPolicyGroupUserIdAndAccrualPolicyAccount( $user_id, TTUUID::castUUID( $pfp_obj->getAccrualPolicyAccount() ) );
				Debug::Text( 'Accrual Policy Records: ' . $aplf->getRecordCount() . ' User ID: ' . $user_id . ' Accrual Policy Account: ' . $pfp_obj->getAccrualPolicyAccount(), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $aplf->getRecordCount() > 0 ) {
					$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
					$ulf->getByIDAndCompanyID( $user_id, $this->getCurrentCompanyObject()->getId() );
					if ( $ulf->getRecordCount() == 1 ) {
						$u_obj = $ulf->getCurrent();

						$retarr = [];
						foreach ( $aplf as $acp_obj ) {
							Debug::Text( '  Accrual Policy ID: ' . $acp_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );
							//Pass $retval back into itself so additional balance can be calculated when accrual policy accounts are used in multiple policies.
							$retarr = $acp_obj->getAccrualBalanceWithProjection( $u_obj, $epoch, $amount, $previous_amount, $retarr );
						}

						if ( isset( $dollar_retarr ) ) {
							$retarr = array_merge( $retarr, $dollar_retarr );
						}

						Debug::Arr( $retarr, '  Projected Accrual Arr: ', __FILE__, __LINE__, __METHOD__, 10 );

						return $this->returnHandler( $retarr );
					}
				} else if ( is_object( $pfp_obj->getAccrualPolicyAccountObject() ) ) {
					Debug::Text( 'No Accrual Policies to return projection for, just get current balance then...', __FILE__, __LINE__, __METHOD__, 10 );
					$available_balance = ( $pfp_obj->getAccrualPolicyAccountObject()->getCurrentAccrualBalance( $user_id ) - $previous_amount );


					$retarr = [
							'available_balance'           => $available_balance,
							'current_time'                => $amount,
							'remaining_balance'           => ( $available_balance + $amount ),
							'projected_balance'           => $available_balance,
							'projected_remaining_balance' => ( $available_balance + $amount ),
					];

					if ( isset( $dollar_retarr ) ) {
						$retarr = array_merge( $retarr, $dollar_retarr );
					}

					Debug::Arr( $retarr, '  Current Accrual Arr: ', __FILE__, __LINE__, __METHOD__, 10 );

					return $this->returnHandler( $retarr );
				} else {
					return $this->returnHandler( false );
				}
			}
		}

		Debug::Text( 'No projections to return...', __FILE__, __LINE__, __METHOD__, 10 );

		return $this->returnHandler( false );
	}
}

?>
