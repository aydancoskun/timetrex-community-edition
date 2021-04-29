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
 * @package API\Users
 */
class APIBankAccount extends APIFactory {
	protected $main_class = 'BankAccountFactory';

	/**
	 * APIBankAccount constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * Get default bank account data for creating new bank accountes.
	 * @param string $user_id UUID
	 * @return array
	 */
	function getBankAccountDefaultData( $user_id = null ) {
		$company_obj = $this->getCurrentCompanyObject();

		Debug::Text( 'Getting wage default data...', __FILE__, __LINE__, __METHOD__, 10 );

		//If user_id is passed, check for other wage entries, if none, default to the employees hire date.

		$data = [
				'company_id' => $company_obj->getId(),
		];

		return $this->returnHandler( $data );
	}

	/**
	 * Get bank account data for one or more bank accountes.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function getBankAccount( $data = null, $disable_paging = false ) {
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( !$this->getPermissionObject()->Check( 'user', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'user', 'edit_bank' ) || $this->getPermissionObject()->Check( 'user', 'edit_own_bank' ) || $this->getPermissionObject()->Check( 'user', 'edit_child_bank' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		if ( $this->getPermissionObject()->Check( 'user', 'edit_bank' ) == true ) {
			//Don't set permission_children_ids.
			$data['filter_data']['permission_children_ids'] = null;
		} else {
			$data['filter_data']['permission_children_ids'] = []; //#2243 - Make sure permission_children_ids is overwritten entirely to avoid security issues of appending to an array partially passed by user and partially set here.
			if ( $this->getPermissionObject()->Check( 'user', 'edit_child_bank' ) == true ) {
				//Manually handle the permission checks here because edit_child_bank doesn't fit with getPermissionChildren() appending "_own" or "_child" on the end.
				$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionHierarchyChildren( $this->getCurrentCompanyObject()->getId(), $this->getCurrentUserObject()->getId() );
			}

			if ( $this->getPermissionObject()->Check( 'user', 'edit_own_bank' ) == true ) {
				$data['filter_data']['permission_children_ids'][] = $this->getCurrentUserObject()->getId();
			}

			Debug::Arr( $data['filter_data']['permission_children_ids'], 'Permission Children: ', __FILE__, __LINE__, __METHOD__, 10 );
		}

		$blf = TTnew( 'BankAccountListFactory' ); /** @var BankAccountListFactory $blf */
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

			Debug::Arr( $retarr, 'Record Count: ' . $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( true ); //No records returned.
	}

	/**
	 * Export data to csv
	 * @param string $format file format (csv)
	 * @param array $data    filter data
	 * @param bool $disable_paging
	 * @return array
	 */
	function exportBankAccount( $format = 'csv', $data = null, $disable_paging = true ) {
		$result = $this->stripReturnHandler( $this->getBankAccount( $data, $disable_paging ) );

		return $this->exportRecords( $format, 'export_bank_account', $result, ( ( isset( $data['filter_columns'] ) ) ? $data['filter_columns'] : null ) );
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonBankAccountData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getBankAccount( $data, true ) ) );
	}

	/**
	 * Validate bank account information.
	 * @param array $data bank account data
	 * @return array
	 */
	function validateBankAccount( $data ) {
		return $this->setBankAccount( $data, true );
	}

	/**
	 * Set bank account data for one or more bank accountes.
	 * @param array $data bank account data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setBankAccount( $data, $validate_only = false, $ignore_warning = true ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( !$this->getPermissionObject()->Check( 'user', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'user', 'edit_bank' ) || $this->getPermissionObject()->Check( 'user', 'edit_own_bank' ) || $this->getPermissionObject()->Check( 'user', 'edit_child_bank' ) ) ) {
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
		Debug::Text( 'Received data for: ' . $total_records . ' BankAccounts', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach ( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'BankAccountListFactory' ); /** @var BankAccountListFactory $lf */
				$lf->StartTransaction();
				if ( isset( $row['id'] ) && $row['id'] != '' ) {
					//Modifying existing object.
					//Get wage object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
								$validate_only == true
								||
								(
										$this->getPermissionObject()->Check( 'user', 'edit_bank' )
										|| ( $this->getPermissionObject()->Check( 'user', 'edit_own_bank' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getUser() ) === true )
										|| ( $this->getPermissionObject()->Check( 'user', 'edit_child_bank' ) && $this->getPermissionObject()->isChild( $lf->getCurrent()->getUser(), $permission_children_ids ) === true )
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
				} //else {
				//Adding new object, check ADD permissions.
				//$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check('user', 'add'), TTi18n::gettext('Add permission denied') );
				//}
				//Debug::Arr($row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid( $ignore_warning );
				if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Setting object data...', __FILE__, __LINE__, __METHOD__, 10 );

					if ( ( $row['user_id'] != '' && $row['user_id'] == $this->getCurrentUserObject()->getId() ) && $row['company_id'] == '' && $this->getPermissionObject()->Check( 'user', 'edit_own_bank' ) ) {
						Debug::Text( 'Current User/Company', __FILE__, __LINE__, __METHOD__, 10 );
						//Current user
						$row['company_id'] = $this->getCurrentCompanyObject()->getId();
						$row['user_id'] = $this->getCurrentUserObject()->getId();
					} else if ( $row['user_id'] != '' && ( $row['company_id'] == '' || $row['company_id'] == $this->getCurrentCompanyObject()->getId() ) && $this->getPermissionObject()->Check( 'user', 'edit_child_bank' ) ) {
						Debug::Text( 'Specified Child User', __FILE__, __LINE__, __METHOD__, 10 );
						//Specified User
						$row['company_id'] = $this->getCurrentCompanyObject()->getId();
					} else if ( $row['user_id'] != '' && ( $row['company_id'] == '' || $row['company_id'] == $this->getCurrentCompanyObject()->getId() ) && $this->getPermissionObject()->Check( 'user', 'edit_bank' ) ) {
						Debug::Text( 'Specified User', __FILE__, __LINE__, __METHOD__, 10 );
						//Specified User
						$row['company_id'] = $this->getCurrentCompanyObject()->getId();
					} else if ( $row['company_id'] != '' && $row['user_id'] == '' && $this->getPermissionObject()->Check( 'company', 'edit_own_bank' ) ) {
						Debug::Text( 'Specified Company', __FILE__, __LINE__, __METHOD__, 10 );
						//Company bank.
						$row['company_id'] = $this->getCurrentCompanyObject()->getId();
					} else {
						Debug::Text( 'No Company or User ID specified...', __FILE__, __LINE__, __METHOD__, 10 );
						//Assume its always the currently logged in users account.
						$row['company_id'] = $this->getCurrentCompanyObject()->getId();

						if ( !isset( $row['user_id'] ) || $this->getPermissionObject()->Check( 'company', 'edit_bank' ) == false ) {
							$row['user_id'] = $this->getCurrentUserObject()->getId();
						}
					}

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

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}

	/**
	 * Delete one or more bank accounts.
	 * @param array $data bank account data
	 * @return array|bool
	 */
	function deleteBankAccount( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( !$this->getPermissionObject()->Check( 'user', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'user', 'edit_bank' ) || $this->getPermissionObject()->Check( 'user', 'edit_own_bank' ) || $this->getPermissionObject()->Check( 'user', 'edit_child_bank' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$permission_children_ids = $this->getPermissionChildren();

		Debug::Text( 'Received data for: ' . count( $data ) . ' BankAccounts', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$total_records = count( $data );
		$validator = $save_result = $key = false;
		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach ( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'BankAccountListFactory' ); /** @var BankAccountListFactory $lf */
				$lf->StartTransaction();
				if ( $id != '' ) {
					//Modifying existing object.
					//Get bank account object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check( 'user', 'edit_bank' )
								|| ( $this->getPermissionObject()->Check( 'user', 'edit_own_bank' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getUser() ) === true )
								|| ( $this->getPermissionObject()->Check( 'user', 'edit_child_bank' ) && $this->getPermissionObject()->isChild( $lf->getCurrent()->getId(), $permission_children_ids ) === true )
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

					$validator[$key] = $this->setValidationArray( $primary_validator, $lf );
				}

				$lf->CommitTransaction();

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}
}

?>
