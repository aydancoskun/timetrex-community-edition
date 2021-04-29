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
 * @package API\Core
 */
class APIAuthorization extends APIFactory {
	protected $main_class = 'AuthorizationFactory';

	/**
	 * APIAuthorization constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * Get default authorization data for creating new authorizations.
	 * @return array
	 */
	function getAuthorizationDefaultData() {
		Debug::Text( 'Getting authorization default data...', __FILE__, __LINE__, __METHOD__, 10 );

		$data = [];

		return $this->returnHandler( $data );
	}

	/**
	 * Get authorization data for one or more authorizations.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function getAuthorization( $data = null, $disable_paging = false ) {
		Debug::Arr( $data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		//Keep in mind administrators doing authorization often have access to ALL requests, or ALL users, so permission_children won't come into play.
		//Users should be able to see authorizations for their own requests.
		if ( isset( $data['filter_data']['object_type_id'] ) && in_array( $data['filter_data']['object_type_id'], [ 1010, 1020, 1030, 1040, 1100 ] ) ) { //Requests
			Debug::Text( 'Request object_type_id: ' . $data['filter_data']['object_type_id'], __FILE__, __LINE__, __METHOD__, 10 );

			if ( !$this->getPermissionObject()->Check( 'request', 'enabled' )
					|| !( $this->getPermissionObject()->Check( 'request', 'view' ) || $this->getPermissionObject()->Check( 'request', 'view_own' ) || $this->getPermissionObject()->Check( 'request', 'view_child' ) ) ) {
				return $this->getPermissionObject()->PermissionDenied();
			}

			$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'request', 'view' );
		} else if ( isset( $data['filter_data']['object_type_id'] ) && in_array( $data['filter_data']['object_type_id'], [ 90 ] ) ) { //Timesheets
			Debug::Text( 'TimeSheet object_type_id: ' . $data['filter_data']['object_type_id'], __FILE__, __LINE__, __METHOD__, 10 );

			if ( !$this->getPermissionObject()->Check( 'punch', 'enabled' )
					|| !( $this->getPermissionObject()->Check( 'punch', 'view' ) || $this->getPermissionObject()->Check( 'punch', 'view_own' ) || $this->getPermissionObject()->Check( 'punch', 'view_child' ) ) ) {
				return $this->getPermissionObject()->PermissionDenied();
			}

			$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'punch', 'view' );
		} else if ( isset( $data['filter_data']['object_type_id'] ) && in_array( $data['filter_data']['object_type_id'], [ 200 ] ) ) { // Expense
			Debug::Text( 'Expense object_type_id: ' . $data['filter_data']['object_type_id'], __FILE__, __LINE__, __METHOD__, 10 );

			if ( !$this->getPermissionObject()->Check( 'user_expense', 'enabled' )
					|| !( $this->getPermissionObject()->Check( 'user_expense', 'view' ) || $this->getPermissionObject()->Check( 'user_expense', 'view_own' ) || $this->getPermissionObject()->Check( 'user_expense', 'view_child' ) ) ) {
				return $this->getPermissionObject()->PermissionDenied();
			}

			$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'user_expense', 'view' );
		} else {
			//Invalid or not specified object_type_id
			Debug::Text( 'No valid object_type_id specified...', __FILE__, __LINE__, __METHOD__, 10 );

			return $this->getPermissionObject()->PermissionDenied();
		}

		//Make sure there is always a object_type_id/object_id to prevent the SQL call from skipping these filters and returning more data than it should.
		if ( !isset( $data['filter_data']['object_type_id'] ) ) {
			$data['filter_data']['object_type_id'] = TTUUID::notExistID();
		}

		if ( !isset( $data['filter_data']['object_id'] ) ) {
			$data['filter_data']['object_id'] = TTUUID::notExistID();
		}

		//Debug::Arr($data['filter_data']['permission_children_ids'], 'Permission Children: ', __FILE__, __LINE__, __METHOD__, 10);

		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		$blf = TTnew( 'AuthorizationListFactory' ); /** @var AuthorizationListFactory $blf */
		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], null, $data['filter_sort'] );
		Debug::Text( 'Record Count: ' . $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $blf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $blf->getRecordCount() );

			$this->setPagerObject( $blf );

			$retarr = [];
			foreach ( $blf as $b_obj ) {
				$retarr[] = $b_obj->getObjectAsArray( $data['filter_columns'], $data['filter_data']['permission_children_ids'] );

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $blf->getCurrentRow() );
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( true ); //No records returned.
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonAuthorizationData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getAuthorization( $data, true ) ) );
	}

	/**
	 * Validate authorization data for one or more authorizations.
	 * @param array $data authorization data
	 * @return array
	 */
	function validateAuthorization( $data ) {
		return $this->setAuthorization( $data, true );
	}

	/**
	 * Set authorization data for one or more authorizations.
	 * @param array $data authorization data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setAuthorization( $data, $validate_only = false, $ignore_warning = true ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( $this->getCurrentUserObject()->getStatus() != 10 ) { //10=Active -- Make sure user record is active as well.
			return $this->getPermissionObject()->PermissionDenied( false, TTi18n::getText( 'Employee status must be Active to Authorize/Decline Requests' ) );
		}

		if ( !( $this->getPermissionObject()->Check( 'request', 'authorize' ) || $this->getPermissionObject()->Check( 'punch', 'authorize' ) || $this->getPermissionObject()->Check( 'user_expense', 'authorize' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == true ) {
			Debug::Text( 'Validating Only!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		list( $data, $total_records ) = $this->convertToMultipleRecords( $data );
		Debug::Text( 'Received data for: ' . $total_records . ' Authorizations', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			foreach ( $data as $key => $row ) {
				$transaction_function = function () use ( $row, $validate_only, $ignore_warning, $validator_stats, $validator, $save_result, $key ) {
					$primary_validator = $tertiary_validator = new Validator();

					$lf = TTnew( 'AuthorizationListFactory' ); /** @var AuthorizationListFactory $lf */
					if ( $validate_only == false ) {                  //Only switch into serializable mode when actually saving the record.
						$lf->setTransactionMode( 'REPEATABLE READ' ); //Required to help prevent duplicate simulataneous HTTP requests from causing duplicate user records or duplicate employee number/user_names.
					}
					$lf->StartTransaction();

					if ( isset( $row['id'] ) && $row['id'] != '' ) {
						//Modifying existing object.
						//Get authorization object, so we can only modify just changed data for specific records if needed.
						$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
						if ( $lf->getRecordCount() == 1 ) {
							//Object exists, check edit permissions
							if (
									$validate_only == true
									||
									(
											$this->getPermissionObject()->Check( 'request', 'authorize' )
											||
											$this->getPermissionObject()->Check( 'punch', 'authorize' )
											||
											$this->getPermissionObject()->Check( 'user_expense', 'authorize' )
									)
							) {

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
					} //else {
					//Adding new object, check ADD permissions.
					//$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check('authorization', 'add'), TTi18n::gettext('Add permission denied') );
					//}
					Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

					$is_valid = $primary_validator->isValid();
					if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
						//Handle authorizing timesheets that have no PPTSVF records yet.
						if ( isset( $row['object_type_id'] ) && $row['object_type_id'] == 90
								&& isset( $row['object_id'] ) && $row['object_id'] == TTUUID::getNotExistID()
								&& isset( $row['user_id'] ) && isset( $row['pay_period_id'] ) ) {

							//Since we are most likely inside a RetryTransaction block already, and verifyTimeSheet() itself is inside one too,
							// we need rethrow any exceptions to cause the outer nested transaction block to fail and retry as a whole. Since we can't retry just part of the transaction.
							try {
								$api_ts = new APITimeSheet();
								$api_raw_retval = $api_ts->verifyTimeSheet( $row['user_id'], $row['pay_period_id'], false ); //Don't enable setEnableAuthorize() as that duplicates the authorize records and causes validation errors.
								Debug::Arr( $api_raw_retval, 'API Retval: ', __FILE__, __LINE__, __METHOD__, 10 );

								$api_retval = $this->stripReturnHandler( $api_raw_retval );
								if ( TTUUID::isUUID( $api_retval ) && $api_retval != TTUUID::getZeroID() && $api_retval != TTUUID::getNotExistID() ) {
									$row['object_id'] = $api_retval;
								} else {
									$tertiary_validator = $this->convertAPIreturnHandlerToValidatorObject( $api_raw_retval, $tertiary_validator );
									$is_valid = $tertiary_validator->isValid();
								}
							} catch ( NestedRetryTransaction $e ) {
								throw new NestedRetryTransaction( $e ); //This should trigger the outer nested transaction block to fail and retry.
							}
						}

						if ( $is_valid == true ) {
							Debug::Text( 'Setting object data...', __FILE__, __LINE__, __METHOD__, 10 );
							$lf->setObjectFromArray( $row );

							//Set the current user so we know who is doing the authorization.
							$lf->setCurrentUser( $this->getCurrentUserObject()->getId() );

							$is_valid = $lf->isValid( $ignore_warning );
							if ( $is_valid == true ) {
								Debug::Text( 'Saving data...', __FILE__, __LINE__, __METHOD__, 10 );
								if ( $validate_only == true ) {
									$save_result[$key] = true;
									$validator_stats['valid_records']++;
								} else {
									$save_result[$key] = $lf->Save( false );

									//Make sure we test for validation failures after Save() is called, especially in cases of advanced requests, as the addRelatedSchedules() could fail.
									if ( $lf->isValid( $ignore_warning ) == false ) {
										Debug::Arr( $lf->Validator->getErrors(), 'PostSave() returned a validation error!', __FILE__, __LINE__, __METHOD__, 10 );
										$is_valid = false;
									} else {
										$validator_stats['valid_records']++;
									}
								}
							}
						}
					}

					if ( $is_valid == false ) {
						Debug::Text( 'Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10 );

						$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

						$validator[$key] = $this->setValidationArray( [ $primary_validator, $lf, $tertiary_validator ] );
					} else if ( $validate_only == true ) {
						$lf->FailTransaction();
					}

					$lf->CommitTransaction();
					$lf->setTransactionMode(); //Back to default isolation level.

					return [ $validator, $validator_stats, $key, $save_result ];
				};

				list( $validator, $validator_stats, $key, $save_result ) = $this->getMainClassObject()->RetryTransaction( $transaction_function );

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}

	/**
	 * Delete one or more authorizations.
	 * @param array $data authorization data
	 * @return array|bool
	 */
	function deleteAuthorization( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !( $this->getPermissionObject()->Check( 'request', 'authorize' ) || $this->getPermissionObject()->Check( 'punch', 'authorize' ) || $this->getPermissionObject()->Check( 'user_expense', 'authorize' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' Authorizations', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$total_records = count( $data );
		$validator = $save_result = $key = false;
		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			foreach ( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'AuthorizationListFactory' ); /** @var AuthorizationListFactory $lf */
				$lf->StartTransaction();
				if ( $id != '' ) {
					//Modifying existing object.
					//Get authorization object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
								$this->getPermissionObject()->Check( 'request', 'authorize' )
								||
								$this->getPermissionObject()->Check( 'punch', 'authorize' )
								||
								$this->getPermissionObject()->Check( 'user_expense', 'authorize' )
						) {
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

					$validator[$key] = $this->setValidationArray( [ $primary_validator, $lf ] );
				}

				$lf->CommitTransaction();

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}
}

?>
