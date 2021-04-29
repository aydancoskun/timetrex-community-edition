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
 * @package API\Company
 */
class APILegalEntity extends APIFactory {
	protected $main_class = 'LegalEntityFactory';

	/**
	 * APILegalEntity constructor.
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
				&& ( !$this->getPermissionObject()->Check( 'legal_entity', 'enabled' )
						|| !( $this->getPermissionObject()->Check( 'legal_entity', 'view' ) || $this->getPermissionObject()->Check( 'legal_entity', 'view_own' ) ) ) ) {
			$name = 'list_columns';
		}

		return parent::getOptions( $name, $parent );
	}

	/**
	 * Get default legal entity data for creating new legal entity.
	 * @return array
	 */
	function getLegalEntityDefaultData() {
		$company_obj = $this->getCurrentCompanyObject();

		Debug::Text( 'Getting legal entity default data...', __FILE__, __LINE__, __METHOD__, 10 );
		$data = [
				'company_id' => $company_obj->getId(),
				'status_id'  => 10,
				'city'       => $company_obj->getCity(),
				'country'    => $company_obj->getCountry(),
				'province'   => $company_obj->getProvince(),
				'work_phone' => $company_obj->getWorkPhone(),
				'fax_phone'  => $company_obj->getFaxPhone(),

				'payment_services_status_id' => 20, //Disabled
		];

		return $this->returnHandler( $data );
	}

	/**
	 * Get legal entity data for one or more legal entities.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function getLegalEntity( $data = null, $disable_paging = false ) {
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'legal_entity', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'legal_entity', 'view' ) || $this->getPermissionObject()->Check( 'legal_entity', 'view_own' ) ) ) {
			//return $this->getPermissionObject()->PermissionDenied();
			//Rather then permission denied, restrict to just 'list_view' columns.
			$data['filter_columns'] = $this->handlePermissionFilterColumns( ( isset( $data['filter_columns'] ) ) ? $data['filter_columns'] : null, Misc::trimSortPrefix( $this->getOptions( 'list_columns' ) ) );
		}

		if ( !( $this->getPermissionObject()->Check( 'company', 'view' ) && $this->getCurrentCompanyObject()->getId() == PRIMARY_COMPANY_ID ) ) {
			//Force ID to current company.
			$data['filter_data']['company_id'] = $this->getCurrentCompanyObject()->getId();
		}

		//If this is a master administrator logged into a primary company, and doesn't specify a company filter, force it to their own company. As they should never see legal entities for more than a single company at a time.
		if ( !isset($data['filter_data']['company_id']) || TTUUID::isUUID( $data['filter_data']['company_id'] ) == false ) {
			$data['filter_data']['company_id'] = $this->getCurrentCompanyObject()->getId();
		}

		$blf = TTnew( 'LegalEntityListFactory' ); /** @var LegalEntityListFactory $blf */
		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], null, $data['filter_sort'] );
		Debug::Text( 'Record Count: ' . $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $blf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $blf->getRecordCount() );

			$this->setPagerObject( $blf );

			$retarr = [];
			foreach ( $blf as $b_obj ) {
				$retarr[] = $b_obj->getObjectAsArray( $data['filter_columns'] );

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
	function getCommonLegalEntityData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getLegalEntity( $data, true ) ) );
	}

	/**
	 * Validate legal entity data for one or more legal entities.
	 * @param array $data legal entity data
	 * @return array
	 */
	function validateLegalEntity( $data ) {
		return $this->setLegalEntity( $data, true );
	}

	/**
	 * Set legal entity data for one or more legal entities.
	 * @param array $data legal entity data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @param bool $add_presets
	 * @return array|bool
	 * @throws DBError
	 * @throws GeneralError
	 */
	function setLegalEntity( $data, $validate_only = false, $ignore_warning = true, $add_presets = true ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'legal_entity', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'legal_entity', 'edit' ) || $this->getPermissionObject()->Check( 'legal_entity', 'edit_own' ) || $this->getPermissionObject()->Check( 'legal_entity', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == true ) {
			Debug::Text( 'Validating Only!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		list( $data, $total_records ) = $this->convertToMultipleRecords( $data );
		Debug::Text( 'Received data for: ' . $total_records . ' legal entities', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach ( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'LegalEntityListFactory' ); /** @var LegalEntityListFactory $lf */
				$lf->StartTransaction();
				if ( isset( $row['id'] ) && $row['id'] != '' ) {
					//Modifying existing object.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
								$validate_only == true
								||
								(
										$this->getPermissionObject()->Check( 'legal_entity', 'edit' )
										|| ( $this->getPermissionObject()->Check( 'legal_entity', 'edit_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true )
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
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check( 'legal_entity', 'add' ), TTi18n::gettext( 'Add permission denied' ) );
				}
				Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

				$is_valid = $primary_validator->isValid( $ignore_warning );
				if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Setting object data...', __FILE__, __LINE__, __METHOD__, 10 );

					//Force Company ID to current company.
					$row['company_id'] = $this->getCurrentCompanyObject()->getId();

					$lf->setObjectFromArray( $row );

					$lf->Validator->setValidateOnly( $validate_only );

					if ( $add_presets == true ) {
						$lf->setEnableAddRemittanceSource( true );
						$lf->setEnableAddPresets( true );
					}
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
	 * Delete one or more legal entities.
	 * @param array $data legal entity data
	 * @return array|bool
	 */
	function deleteLegalEntity( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'legal_entity', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'legal_entity', 'delete' ) || $this->getPermissionObject()->Check( 'legal_entity', 'delete_own' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' Legal entities', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$total_records = count( $data );
		$validator = $save_result = $key = false;
		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach ( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'LegalEntityListFactory' ); /** @var LegalEntityListFactory $lf */
				$lf->StartTransaction();
				if ( $id != '' ) {
					//Modifying existing object.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check( 'legal_entity', 'delete' )
								|| ( $this->getPermissionObject()->Check( 'legal_entity', 'delete_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true ) ) {
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

	/**
	 * Copy one or more legal entities.
	 * @param array $data legal entity IDs
	 * @return array
	 */
	function copyLegalEntity( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' Legal entities', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$src_rows = $this->stripReturnHandler( $this->getLegalEntity( [ 'filter_data' => [ 'id' => $data ] ], true ) );
		if ( is_array( $src_rows ) && count( $src_rows ) > 0 ) {
			Debug::Arr( $src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $src_rows as $key => $row ) {
				$lef = TTnew( 'LegalEntityFactory' ); /** @var LegalEntityFactory $lef */
				$lef->StartTransaction();

				$original_legal_entity_id = $src_rows[$key]['id'];
				unset( $src_rows[$key]['id'] );                                               //Clear fields that can't be copied
				$src_rows[$key]['legal_name'] = Misc::generateCopyName( $row['legal_name'] ); //Generate unique name

				//We need to copy legal entities one at a time rather than in a batch, since we need to do so much other work with them.
				$retval = $this->setLegalEntity( $src_rows[$key], false, true, false );       //Save copied rows -- $add_presets=FALSE though so we can copy them all from the source legal entity.

				$new_legal_entity_id = null;
				if ( is_array( $retval ) ) {
					if ( isset( $retval['api_retval'] )
							&& TTUUID::isUUID( $retval['api_retval'] ) && $retval['api_retval'] != TTUUID::getZeroID() && $retval['api_retval'] != TTUUID::getNotExistID() ) {
						$new_legal_entity_id = $retval['api_retval'];
					} else if ( isset( $retval['api_details']['details'][$key] ) ) {
						$new_legal_entity_id = $retval['api_details']['details'][$key];
					}
				} else if ( TTUUID::isUUID( $retval ) ) {
					$new_legal_entity_id = $retval;
				}
				Debug::Text( '  Legal Entity IDs: Original: ' . $original_legal_entity_id . ' New: ' . $new_legal_entity_id, __FILE__, __LINE__, __METHOD__, 10 );

				if ( TTUUID::isUUID( $new_legal_entity_id ) && $new_legal_entity_id != TTUUID::getNotExistID() ) {
					//Copy Remittance Source Accounts
					$rsalf = TTnew( 'RemittanceSourceAccountListFactory' ); /** @var RemittanceSourceAccountListFactory $rsalf */
					$rsalf->getByLegalEntityIdAndCompanyId( $original_legal_entity_id, $this->getCurrentCompanyObject()->getId() );
					if ( $rsalf->getRecordCount() > 0 ) {
						foreach ( $rsalf as $rsa_obj ) {
							$rsa_obj->setId( false );
							$rsa_obj->setLegalEntity( $new_legal_entity_id );
							$rsa_obj->setName( Misc::generateCopyName( $rsa_obj->getName() ) ); //Generate unique name
							if ( $rsa_obj->isValid() ) {
								$rsa_obj->Save();
							}
						}
					}
					unset( $rsalf, $rsa_obj );

					//Copy Remittance Agencies
					$pralf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $pralf */
					$pralf->getByLegalEntityIdAndCompanyId( $original_legal_entity_id, $this->getCurrentCompanyObject()->getId() );
					if ( $pralf->getRecordCount() > 0 ) {
						foreach ( $pralf as $pra_obj ) {
							$original_pra_id = $pra_obj->getId();
							Debug::Text( '    Copying Payroll Remittance Agency to new legal entity: ' . $pra_obj->getName() . ' Original ID: ' . $original_pra_id, __FILE__, __LINE__, __METHOD__, 10 );

							$pra_obj->setId( false );
							$pra_obj->setLegalEntity( $new_legal_entity_id );
							if ( $pra_obj->isValid() ) {
								$new_pra_id = $pra_obj->Save();

								$praelf = TTnew( 'PayrollRemittanceAgencyEventListFactory' ); /** @var PayrollRemittanceAgencyEventListFactory $praelf */
								$praelf->getByLegalEntityIdAndRemittanceAgencyId( $original_legal_entity_id, $original_pra_id );
								if ( $praelf->getRecordCount() > 0 ) {
									foreach ( $praelf as $prae_obj ) {
										Debug::Text( '      Copying Payroll Remittance Agency Event to new Remittance Agency: ' . $prae_obj->getType() . ' Original ID: ' . $prae_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
										$prae_obj->setId( false );
										$prae_obj->setPayrollRemittanceAgencyId( $new_pra_id );
										if ( $prae_obj->isValid() ) {
											$prae_obj->Save();
										}
									}
								}
							}
						}
					}
					unset( $pralf, $prae_obj );

					//Copy Tax/Deduction records (without users).
					$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
					$cdlf->getByCompanyIdAndLegalEntityId( $this->getCurrentCompanyObject()->getId(), $original_legal_entity_id );
					if ( $cdlf->getRecordCount() > 0 ) {
						foreach ( $cdlf as $cd_obj ) {
							//Copy all Tax/Deductions assigned to this legal entity, even if they aren't associated with a specific remittance agency.
							//  However, if they aren't assigned to any specific legal entity, then they won't be copied of course and thats how they can be shared.
//							if ( $cd_obj->getPayrollRemittanceAgency() != '' AND $cd_obj->getPayrollRemittanceAgency() != TTUUID::getZeroId() ) {
							Debug::Text( '    Copying Tax/Deduction to new legal entity: ' . $cd_obj->getName() . ' Original ID: ' . $cd_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
							$tmp_cd_obj_arr = $cd_obj->getObjectAsArray();
							$tmp_cd_obj_arr['id'] = $cd_obj->getNextInsertId(); //Since we are saving data to child tables, we need to define the ID first.
							$tmp_cd_obj_arr['legal_entity_id'] = $new_legal_entity_id;

							//Find new Payroll Remittance Agency to assign it to.
							if ( is_object( $cd_obj->getPayrollRemittanceAgencyObject() ) ) {
								$pralf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $pralf */
								$pralf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), [ 'legal_entity_id' => $new_legal_entity_id, 'agency_id' => $cd_obj->getPayrollRemittanceAgencyObject()->getAgency() ] );
								if ( $pralf->getRecordCount() == 1 ) {
									Debug::Text( '      Found new Remittance Agency to assign... ID: ' . $pralf->getCurrent()->getId(), __FILE__, __LINE__, __METHOD__, 10 );
									$tmp_cd_obj_arr['payroll_remittance_agency_id'] = $pralf->getCurrent()->getId();
								} else {
									Debug::Text( '      a. No Remittance Agency to assign... Remittance Agency ID: ' . $cd_obj->getPayrollRemittanceAgency(), __FILE__, __LINE__, __METHOD__, 10 );
									$tmp_cd_obj_arr['payroll_remittance_agency_id'] = TTUUID::getZeroID();
								}
							} else {
								Debug::Text( '      b. No Remittance Agency to assign... Remittance Agency ID: ' . $cd_obj->getPayrollRemittanceAgency(), __FILE__, __LINE__, __METHOD__, 10 );
								$tmp_cd_obj_arr['payroll_remittance_agency_id'] = TTUUID::getZeroID();

								//If no remittance agency is selected, we have to generate a random name to prevent unique name checks from failing.
								$tmp_cd_obj_arr['name'] = $cd_obj->getName() . ' [' . rand( 1, 99 ) . ']';
							}

							unset( $tmp_cd_obj_arr['user'] );

							$tmp_cd_obj = TTnew( 'CompanyDeductionFactory' ); /** @var CompanyDeductionFactory $tmp_cd_obj */
							$tmp_cd_obj->setObjectFromArray( $tmp_cd_obj_arr );
							if ( $tmp_cd_obj->isValid() ) {
								$tmp_cd_obj->Save( true, true );
								Debug::Text( '      New Tax/Deduction ID: ' . $tmp_cd_obj_arr['id'], __FILE__, __LINE__, __METHOD__, 10 );
							}

							unset( $tmp_cd_obj_arr, $tmp_cd_obj );
//							} else {
//								Debug::Text( '      Skip copying Tax/Deduction that is not assigned to any specific remittance agency: '. $cd_obj->getName() .' Original ID: '. $cd_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
//							}
						}
					}
					unset( $cdlf, $cd_obj );
				} else {
					//Likely some validation failure, return it back to the user.
					$lef->FailTransaction();
					$lef->CommitTransaction();

					return $this->returnHandler( $retval );
				}

				//$lef->FailTransaction(); //ZZZ REMOVE ME!
				$lef->CommitTransaction();
			}

			//Debug::Arr($src_rows, 'bSRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->returnHandler( true );
		}

		return $this->returnHandler( false );
	}

	/**
	 * @param $id
	 * @return bool
	 */
	function deleteImage( $id ) {
		//Permissions match setLegalEntity
		if ( !$this->getPermissionObject()->Check( 'legal_entity', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'legal_entity', 'edit' ) || $this->getPermissionObject()->Check( 'legal_entity', 'edit_own' ) || $this->getPermissionObject()->Check( 'legal_entity', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		$result = $this->stripReturnHandler( $this->getLegalEntity( [ 'filter_data' => [ 'id' => $id ] ] ) );
		if ( isset( $result[0] ) && count( $result[0] ) > 0 ) {
			$f = TTnew( 'LegalEntityFactory' ); /** @var LegalEntityFactory $f */
			$file_name = $f->getLogoFileName( $id, false );

			if ( file_exists( $file_name ) ) {
				unlink( $file_name );
			}
		}

		return $this->returnHandler( true );
	}

	/**
	 * @param $legal_entity_id
	 * @param null $start_date
	 * @param null $end_date
	 * @return array|bool
	 */
	function getPaymentServicesAccountStatementReport( $legal_entity_id, $start_date = null, $end_date = null ) {
		//Permissions match setLegalEntity
		if ( !$this->getPermissionObject()->Check( 'legal_entity', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'legal_entity', 'edit' ) || $this->getPermissionObject()->Check( 'legal_entity', 'edit_own' ) || $this->getPermissionObject()->Check( 'legal_entity', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		$data['filter_data']['company_id'] = $this->getCurrentCompanyObject()->getId();

		if ( TTUUID::isUUID( $legal_entity_id ) == true ) {
			$data['filter_data']['id'] = $legal_entity_id;
		}

		$lelf = TTnew( 'LegalEntityListFactory' ); /** @var LegalEntityListFactory $lelf */
		$lelf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'] );
		Debug::Text( 'Record Count: ' . $lelf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $lelf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $lelf->getRecordCount() );

			$output = [];
			foreach ( $lelf as $le_obj ) {
				if ( $le_obj->checkPaymentServicesCredentials() == true ) {
					$tt_ps_api = $le_obj->getPaymentServicesAPIObject();
					$output = $tt_ps_api->getAccountStatementReport( $start_date, $end_date );
				}

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $lelf->getCurrentRow() );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			if ( $output != '' ) {
				return Misc::APIFileDownload( 'payment_services_account_statement.txt', 'application/txt', $output );
			} else {
				return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'ERROR: No data for account statement...' ) );
			}
		}

		return $this->returnHandler( false );
	}
}

?>
