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
 * @package API\Core
 */
class APIUserDateTotal extends APIFactory {
	protected $main_class = 'UserDateTotalFactory';

	/**
	 * APIUserDateTotal constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * Get default user_date_total data for creating new user_date_totales.
	 * @param string $user_id UUID
	 * @param int $date       EPOCH
	 * @return array
	 */
	function getUserDateTotalDefaultData( $user_id = null, $date = null ) {
		$company_obj = $this->getCurrentCompanyObject();

		Debug::Text( 'Getting user_date_total default data...', __FILE__, __LINE__, __METHOD__, 10 );

		$data = [
				'currency_id'      => $this->getCurrentUserObject()->getCurrency(),
				'branch_id'        => $this->getCurrentUserObject()->getDefaultBranch(),
				'department_id'    => $this->getCurrentUserObject()->getDefaultDepartment(),
				'total_time'       => 0,
				'base_hourly_rate' => 0,
				'hourly_rate'      => 0,
				'override'         => true,
		];

		//If user_id is specified, use their default branch/department.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getByIdAndCompanyId( $user_id, $company_obj->getID() );
		if ( $ulf->getRecordCount() == 1 ) {
			$user_obj = $ulf->getCurrent();

			$data['user_id'] = $user_obj->getID();
			$data['branch_id'] = $user_obj->getDefaultBranch();
			$data['department_id'] = $user_obj->getDefaultDepartment();
			$data['job_id'] = $user_obj->getDefaultJob();
			$data['job_item_id'] = $user_obj->getDefaultJobItem();

			$uwlf = TTnew( 'UserWageListFactory' ); /** @var UserWageListFactory $uwlf */
			$uwlf->getByUserIdAndGroupIDAndBeforeDate( $user_id, TTUUID::getZeroID(), TTDate::parseDateTime( $date ), 1 );
			if ( $uwlf->getRecordCount() > 0 ) {
				foreach ( $uwlf as $uw_obj ) {
					$data['base_hourly_rate'] = $data['hourly_rate'] = $uw_obj->getHourlyRate();
				}
			}
			unset( $uwlf, $uw_obj );
		}
		unset( $ulf, $user_obj );

		Debug::Arr( $data, 'Default data: ', __FILE__, __LINE__, __METHOD__, 10 );

		return $this->returnHandler( $data );
	}

	/**
	 * Get combined recurring user_date_total and committed user_date_total data for one or more user_date_totales.
	 * @param array $data filter data
	 * @return array|bool
	 */
	function getCombinedUserDateTotal( $data = null ) {
		if ( !$this->getPermissionObject()->Check( 'punch', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'punch', 'view' ) || $this->getPermissionObject()->Check( 'punch', 'view_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		$data = $this->initializeFilterAndPager( $data );

		$sf = TTnew( 'UserDateTotalFactory' ); /** @var UserDateTotalFactory $sf */
		$retarr = $sf->getUserDateTotalArray( $data['filter_data'] );

		Debug::Arr( $retarr, 'RetArr: ', __FILE__, __LINE__, __METHOD__, 10 );

		return $this->returnHandler( $retarr );
	}


	/**
	 * Get user_date_total data for one or more user_date_totales.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function getUserDateTotal( $data = null, $disable_paging = false ) {
		$data = $this->initializeFilterAndPager( $data );

		//Regular employees with permissions to edit their own absences need this.
		if ( !$this->getPermissionObject()->Check( 'punch', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'punch', 'view' ) || $this->getPermissionObject()->Check( 'punch', 'view_own' ) || $this->getPermissionObject()->Check( 'punch', 'view_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		//Parse date string sent by HTML5 interface for searching.
		if ( isset( $data['filter_data']['date_stamp'] ) ) {
			$data['filter_data']['date_stamp'] = TTDate::getMiddleDayEpoch( TTDate::parseDateTime( $data['filter_data']['date_stamp'] ) );
		}

		if ( isset( $data['filter_data']['start_date'] ) ) {
			$data['filter_data']['start_date'] = TTDate::parseDateTime( $data['filter_data']['start_date'] );
		}

		if ( isset( $data['filter_data']['end_date'] ) ) {
			$data['filter_data']['end_date'] = TTDate::parseDateTime( $data['filter_data']['end_date'] );
		}

		//This can be used to edit Absences as well, how do we differentiate between them?
		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'punch', 'view' );

		$blf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $blf */
		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], null, $data['filter_sort'] );
		Debug::Text( 'Record Count: ' . $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $blf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $blf->getRecordCount() );

			$this->setPagerObject( $blf );

			$retarr = [];
			foreach ( $blf as $b_obj ) {
				$retarr[] = $b_obj->getObjectAsArray( $data['filter_columns'], $data['filter_data']['permission_children_ids'] );

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $blf->getCurrentRow() );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( true ); //No records returned.
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonUserDateTotalData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getUserDateTotal( $data, true ) ) );
	}

	/**
	 * Validate user_date_total data for one or more user_date_totales.
	 * @param array $data user_date_total data
	 * @return array
	 */
	function validateUserDateTotal( $data ) {
		return $this->setUserDateTotal( $data, true );
	}

	/**
	 * Set user_date_total data for one or more user_date_totales.
	 * @param array $data user_date_total data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setUserDateTotal( $data, $validate_only = false, $ignore_warning = true ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( !( $this->getPermissionObject()->Check( 'punch', 'enabled' ) || $this->getPermissionObject()->Check( 'absence', 'enabled' ) )
				|| !( $this->getPermissionObject()->Check( 'punch', 'edit' ) || $this->getPermissionObject()->Check( 'punch', 'edit_own' ) || $this->getPermissionObject()->Check( 'punch', 'edit_child' ) || $this->getPermissionObject()->Check( 'punch', 'add' ) )
				|| !( $this->getPermissionObject()->Check( 'absence', 'edit' ) || $this->getPermissionObject()->Check( 'absence', 'edit_own' ) || $this->getPermissionObject()->Check( 'absence', 'edit_child' ) || $this->getPermissionObject()->Check( 'absence', 'add' ) )
		) {
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
		Debug::Text( 'Received data for: ' . $total_records . ' UserDateTotals', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			$transaction_function = function () use ( $data, $validate_only, $ignore_warning, $validator_stats, $validator, $save_result, $key, $permission_children_ids ) {
				$lf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $lf */
				if ( $validate_only == false ) {                  //Only switch into serializable mode when actually saving the record.
					$lf->setTransactionMode( 'REPEATABLE READ' ); //Required to help prevent duplicate simulataneous HTTP requests from causing incorrect calculations in user_date_total table.
				}
				$lf->StartTransaction(); //Wrap entire batch in the transaction.

				$recalculate_user_date_stamp = false;
				foreach ( $data as $key => $row ) {
					$primary_validator = new Validator();
					$lf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $lf */
					//$lf->StartTransaction();
					if ( isset( $row['id'] ) && $row['id'] != '' ) {
						//Modifying existing object.
						//Get user_date_total object, so we can only modify just changed data for specific records if needed.
						$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
						if ( $lf->getRecordCount() == 1 ) {
							//Object exists, check edit permissions
							if (
									$validate_only == true
									||
									(
											$this->getPermissionObject()->Check( 'punch', 'edit' )
											|| ( $this->getPermissionObject()->Check( 'punch', 'edit_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getUser() ) === true )
											|| ( $this->getPermissionObject()->Check( 'punch', 'edit_child' ) && $this->getPermissionObject()->isChild( $lf->getCurrent()->getUser(), $permission_children_ids ) === true )
									)
									||
									(
											$this->getPermissionObject()->Check( 'absence', 'edit' )
											|| ( $this->getPermissionObject()->Check( 'absence', 'edit_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getUser() ) === true )
											|| ( $this->getPermissionObject()->Check( 'absence', 'edit_child' ) && $this->getPermissionObject()->isChild( $lf->getCurrent()->getUser(), $permission_children_ids ) === true )
									)
							) {

								Debug::Text( 'Row Exists, getting current data for ID: ' . $row['id'], __FILE__, __LINE__, __METHOD__, 10 );
								$lf = $lf->getCurrent();

								//When editing a record if the date changes, we need to recalculate the old date.
								//This must occur before we merge the data together.
								if ( ( isset( $row['user_id'] )
												&& $lf->getUser() != $row['user_id'] )
										||
										( isset( $row['date_stamp'] )
												&& $lf->getDateStamp()
												&& TTDate::parseDateTime( $row['date_stamp'] ) != $lf->getDateStamp() )
								) {
									Debug::Text( 'Date has changed, recalculate old date... New: [ Date: ' . $row['date_stamp'] . ' ] UserID: ' . $lf->getUser(), __FILE__, __LINE__, __METHOD__, 10 );
									$recalculate_user_date_stamp[$lf->getUser()][] = TTDate::getMiddleDayEpoch( $lf->getDateStamp() ); //Help avoid confusion with different timezones/DST.
								}

								//Since switching to batch calculation mode, need to store every possible date to recalculate.
								if ( isset( $row['user_id'] ) && $row['user_id'] != '' && isset( $row['date_stamp'] ) && $row['date_stamp'] != '' ) {
									//Since switching to batch calculation mode, need to store every possible date to recalculate.
									$recalculate_user_date_stamp[TTUUID::castUUID( $row['user_id'] )][] = TTDate::getMiddleDayEpoch( TTDate::parseDateTime( $row['date_stamp'] ) ); //Help avoid confusion with different timezones/DST.
								}
								$recalculate_user_date_stamp[$lf->getUser()][] = TTDate::getMiddleDayEpoch( $lf->getDateStamp() ); //Help avoid confusion with different timezones/DST.

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
						if ( !( $validate_only == true
								||
								( $this->getPermissionObject()->Check( 'punch', 'add' )
										&&
										(
												$this->getPermissionObject()->Check( 'punch', 'edit' )
												|| ( isset( $row['user_id'] ) && $this->getPermissionObject()->Check( 'punch', 'edit_own' ) && $this->getPermissionObject()->isOwner( false, $row['user_id'] ) === true ) //We don't know the created_by of the user at this point, but only check if the user is assigned to the logged in person.
												|| ( isset( $row['user_id'] ) && $this->getPermissionObject()->Check( 'punch', 'edit_child' ) && $this->getPermissionObject()->isChild( $row['user_id'], $permission_children_ids ) === true )
										)
								)
								||
								( $this->getPermissionObject()->Check( 'absence', 'add' )
										&&
										(
												$this->getPermissionObject()->Check( 'absence', 'edit' )
												|| ( isset( $row['user_id'] ) && $this->getPermissionObject()->Check( 'absence', 'edit_own' ) && $this->getPermissionObject()->isOwner( false, $row['user_id'] ) === true ) //We don't know the created_by of the user at this point, but only check if the user is assigned to the logged in person.
												|| ( isset( $row['user_id'] ) && $this->getPermissionObject()->Check( 'absence', 'edit_child' ) && $this->getPermissionObject()->isChild( $row['user_id'], $permission_children_ids ) === true )
										)
								)
						) ) {
							$primary_validator->isTrue( 'permission', false, TTi18n::gettext( 'Add permission denied' ) );
						} else {
							if ( isset( $row['user_id'] ) && $row['user_id'] != '' && isset( $row['date_stamp'] ) && $row['date_stamp'] != '' ) {
								//Since switching to batch calculation mode, need to store every possible date to recalculate.
								$recalculate_user_date_stamp[TTUUID::castUUID( $row['user_id'] )][] = TTDate::getMiddleDayEpoch( TTDate::parseDateTime( $row['date_stamp'] ) ); //Help avoid confusion with different timezones/DST.
							}
						}
					}
					Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

					$is_valid = $primary_validator->isValid( $ignore_warning );
					if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
						Debug::Text( 'Setting object data...', __FILE__, __LINE__, __METHOD__, 10 );

						//If the currently logged in user is timezone GMT, and he edits an absence for a user in timezone PST
						//it can cause confusion as to which date needs to be recalculated, the GMT or PST date?
						//Try to avoid this by using getMiddleDayEpoch() as much as possible.
						$lf->setObjectFromArray( $row );
						$lf->Validator->setValidateOnly( $validate_only );

						$is_valid = $lf->isValid( $ignore_warning );
						if ( $is_valid == true ) {
							Debug::Text( 'Saving data...', __FILE__, __LINE__, __METHOD__, 10 );
							if ( $validate_only == true ) {
								$save_result[$key] = true;
							} else {
								$lf->setEnableTimeSheetVerificationCheck( true ); //Unverify timesheet if its already verified.

								//Before batch calculation mode was enabled...
								//$lf->setEnableCalcSystemTotalTime( TRUE );
								//$lf->setEnableCalcWeeklySystemTotalTime( TRUE );
								//$lf->setEnableCalcException( TRUE );
								$lf->setEnableCalcSystemTotalTime( false );
								$lf->setEnableCalcWeeklySystemTotalTime( false );
								$lf->setEnableCalcException( false );

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
					//$lf->CommitTransaction();

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
				}

				if ( $is_valid == true && $validate_only == false ) {
					if ( is_array( $recalculate_user_date_stamp ) && count( $recalculate_user_date_stamp ) > 0 ) {
						Debug::Arr( $recalculate_user_date_stamp, 'Recalculating other dates...', __FILE__, __LINE__, __METHOD__, 10 );
						foreach ( $recalculate_user_date_stamp as $user_id => $date_arr ) {
							$ulf = TTNew( 'UserListFactory' ); /** @var UserListFactory $ulf */
							$ulf->getByIdAndCompanyId( $user_id, $this->getCurrentCompanyObject()->getId() );
							if ( $ulf->getRecordCount() > 0 ) {
								$cp = TTNew( 'CalculatePolicy' ); /** @var CalculatePolicy $cp */
								$cp->setUserObject( $ulf->getCurrent() );
								$cp->addPendingCalculationDate( $date_arr );
								$cp->calculate(); //This sets timezone itself.
								$cp->Save();
							}
						}
					} else {
						Debug::Text( 'aNot recalculating batch...', __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::Text( 'bNot recalculating batch...', __FILE__, __LINE__, __METHOD__, 10 );
				}

				$lf->CommitTransaction();
				$lf->setTransactionMode(); //Back to default isolation level.

				return [ $validator, $validator_stats, $key, $save_result ];
			};

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			if ( $total_records > 100 ) { //When importing, or mass adding punches, don't retry as the transaction will be much too large.
				$retry_max_attempts = 1;
			} else if ( $total_records > 20 ) { //When importing, or mass adding punches, don't retry as the transaction will be much too large.
				$retry_max_attempts = 2;
			} else {
				$retry_max_attempts = 3;
			}

			list( $validator, $validator_stats, $key, $save_result ) = $this->RetryTransaction( $transaction_function, $retry_max_attempts );

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}

	/**
	 * Delete one or more user_date_totals.
	 * @param array $data user_date_total data
	 * @return array|bool
	 */
	function deleteUserDateTotal( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( !( $this->getPermissionObject()->Check( 'punch', 'enabled' ) || $this->getPermissionObject()->Check( 'absence', 'enabled' ) )
				|| !( $this->getPermissionObject()->Check( 'punch', 'edit' ) || $this->getPermissionObject()->Check( 'punch', 'edit_own' ) || $this->getPermissionObject()->Check( 'punch', 'edit_child' ) || $this->getPermissionObject()->Check( 'punch', 'add' ) )
				|| !( $this->getPermissionObject()->Check( 'absence', 'edit' ) || $this->getPermissionObject()->Check( 'absence', 'edit_own' ) || $this->getPermissionObject()->Check( 'absence', 'edit_child' ) || $this->getPermissionObject()->Check( 'absence', 'add' ) )
		) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$permission_children_ids = $this->getPermissionChildren();

		Debug::Text( 'Received data for: ' . count( $data ) . ' UserDateTotals', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$total_records = count( $data );
		$validator = $save_result = $key = false;
		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			$transaction_function = function () use ( $data, $validator_stats, $validator, $save_result, $permission_children_ids ) {
				$lf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $lf */
				$lf->setTransactionMode( 'REPEATABLE READ' ); //Required to help prevent duplicate simulataneous HTTP requests from causing incorrect calculations in user_date_total table.
				$lf->StartTransaction();

				$recalculate_user_date_stamp = false;
				foreach ( $data as $key => $id ) {
					$primary_validator = new Validator();
					$lf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $lf */
					//$lf->StartTransaction();
					if ( $id != '' ) {
						//Modifying existing object.
						//Get user_date_total object, so we can only modify just changed data for specific records if needed.
						$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
						if ( $lf->getRecordCount() == 1 ) {
							//Object exists, check edit permissions
							if ( (
											$this->getPermissionObject()->Check( 'punch', 'delete' )
											|| ( $this->getPermissionObject()->Check( 'punch', 'delete_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getUser() ) === true )
											|| ( $this->getPermissionObject()->Check( 'punch', 'delete_child' ) && $this->getPermissionObject()->isChild( $lf->getCurrent()->getUser(), $permission_children_ids ) === true )
									)
									||
									(
											$this->getPermissionObject()->Check( 'absence', 'delete' )
											|| ( $this->getPermissionObject()->Check( 'absence', 'delete_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getUser() ) === true )
											|| ( $this->getPermissionObject()->Check( 'absence', 'delete_child' ) && $this->getPermissionObject()->isChild( $lf->getCurrent()->getUser(), $permission_children_ids ) === true )
									)
							) {
								Debug::Text( 'Record Exists, deleting record: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
								$lf = $lf->getCurrent();

								$recalculate_user_date_stamp[$lf->getUser()][] = TTDate::getMiddleDayEpoch( $lf->getDateStamp() ); //Help avoid confusion with different timezones/DST.
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

					//Prevent user from deleting records that haven't been overridden already. For example records created by punches and not the manual timesheet.
					//  Just in case the manual timesheet UI for some reason shows them the delete (minus) icon on the wrong row when it shouldn't.
					if ( $lf->getOverride() == false ) {
						Debug::Text( 'Skip deleting UDT record that isnt already overridden. Object Type: ' . $lf->getObjectType() . ' ID: ' . $lf->getId(), __FILE__, __LINE__, __METHOD__, 10 );
						if ( $lf->getObjectType() == 50 ) {
							$primary_validator->isTrue( 'override', false, TTi18n::gettext( 'Unable to delete absences that originated from the schedule. Instead delete scheduled shift or edit this absence and set the total time to 0' ) );
						} else {
							$primary_validator->isTrue( 'override', false, TTi18n::gettext( 'Unable to delete system records. Instead edit the record and set the total time to 0' ) );
						}
					}

					$is_valid = $primary_validator->isValid();
					if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
						Debug::Text( 'Attempting to delete record...', __FILE__, __LINE__, __METHOD__, 10 );
						$lf->setDeleted( true );

						$is_valid = $lf->isValid();
						if ( $is_valid == true ) {
							Debug::Text( 'Record Deleted...', __FILE__, __LINE__, __METHOD__, 10 );
							$lf->setEnableTimeSheetVerificationCheck( true ); //Unverify timesheet if its already verified.

							//Before batch calculation mode was enabled...
							//$lf->setEnableCalcSystemTotalTime( TRUE );
							//$lf->setEnableCalcWeeklySystemTotalTime( TRUE );
							//$lf->setEnableCalcException( TRUE );
							$lf->setEnableCalcSystemTotalTime( false );
							$lf->setEnableCalcWeeklySystemTotalTime( false );
							$lf->setEnableCalcException( false );

							$save_result[$key] = $lf->Save();
							$validator_stats['valid_records']++;
						}
					}

					if ( $is_valid == false ) {
						Debug::Text( 'Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10 );

						$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

						$validator[$key] = $this->setValidationArray( $primary_validator, $lf );
					}

					//$lf->CommitTransaction();

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
				}

				if ( $is_valid == true ) {
					if ( is_array( $recalculate_user_date_stamp ) && count( $recalculate_user_date_stamp ) > 0 ) {
						Debug::Arr( $recalculate_user_date_stamp, 'Recalculating other dates...', __FILE__, __LINE__, __METHOD__, 10 );
						foreach ( $recalculate_user_date_stamp as $user_id => $date_arr ) {
							$ulf = TTNew( 'UserListFactory' ); /** @var UserListFactory $ulf */
							$ulf->getByIdAndCompanyId( $user_id, $this->getCurrentCompanyObject()->getId() );
							if ( $ulf->getRecordCount() > 0 ) {
								$cp = TTNew( 'CalculatePolicy' ); /** @var CalculatePolicy $cp */
								$cp->setUserObject( $ulf->getCurrent() );
								$cp->addPendingCalculationDate( $date_arr );
								$cp->calculate(); //This sets timezone itself.
								$cp->Save();
							}
						}
					} else {
						Debug::Text( 'aNot recalculating batch...', __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::Text( 'bNot recalculating batch...', __FILE__, __LINE__, __METHOD__, 10 );
				}

				$lf->CommitTransaction();
				$lf->setTransactionMode(); //Back to default isolation level.

				return [ $validator, $validator_stats, $key, $save_result ];
			};

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			list( $validator, $validator_stats, $key, $save_result ) = $this->RetryTransaction( $transaction_function );

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}

	/**
	 * Copy one or more user_date_totales.
	 * @param array $data user_date_total IDs
	 * @return array
	 */
	function copyUserDateTotal( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' UserDateTotals', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$src_rows = $this->stripReturnHandler( $this->getUserDateTotal( [ 'filter_data' => [ 'id' => $data ] ], true ) );
		if ( is_array( $src_rows ) && count( $src_rows ) > 0 ) {
			Debug::Arr( $src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $src_rows as $key => $row ) {
				unset( $src_rows[$key]['id'], $src_rows[$key]['manual_id'] );     //Clear fields that can't be copied
				$src_rows[$key]['name'] = Misc::generateCopyName( $row['name'] ); //Generate unique name
			}

			//Debug::Arr($src_rows, 'bSRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->setUserDateTotal( $src_rows ); //Save copied rows
		}

		return $this->returnHandler( false );
	}

	/**
	 * @param $data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function getAccumulatedUserDateTotal( $data, $disable_paging = false ) {
		return UserDateTotalFactory::calcAccumulatedTime( $this->getUserDateTotal( $data, true ) );
	}

	/**
	 * @param $data
	 * @param bool $disable_paging
	 * @return bool
	 */
	function getTotalAccumulatedUserDateTotal( $data, $disable_paging = false ) {
		$retarr = UserDateTotalFactory::calcAccumulatedTime( $this->getUserDateTotal( $data, true ) );
		if ( isset( $retarr['total'] ) ) {
			return $retarr['total'];
		}

		return false;
	}

}

?>
