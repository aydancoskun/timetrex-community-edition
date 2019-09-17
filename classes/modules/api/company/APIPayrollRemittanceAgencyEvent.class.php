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
class APIPayrollRemittanceAgencyEvent extends APIFactory {
	protected $main_class = 'PayrollRemittanceAgencyEventFactory';

	/**
	 * APIPayrollRemittanceAgencyEvent constructor.
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
				AND ( !$this->getPermissionObject()->Check( 'payroll_remittance_agency', 'enabled' )
						OR !( $this->getPermissionObject()->Check( 'payroll_remittance_agency', 'view' ) OR $this->getPermissionObject()->Check( 'payroll_remittance_agency', 'view_own' ) ) )
		) {
			$name = 'list_columns';
		}

		return parent::getOptions( $name, $parent );
	}

	/**
	 * Get default payroll remittance agency data for creating new payroll remittance agency.
	 * @return array
	 */
	function getPayrollRemittanceAgencyEventDefaultData() {
		$company_obj = $this->getCurrentCompanyObject();

		Debug::Text( 'Getting payroll remittance agency default data...', __FILE__, __LINE__, __METHOD__, 10 );
		$data = array(
			//FIXME: need better default data.
			'status_id'                   => 10,
			'type_id'                     => 10,
			'reminder_user_id'            => $this->getCurrentUserObject()->getId(),
			'reminder_days'               => 2,
			'due_date_delay_days' 		  => 0,
		);

		return $this->returnHandler( $data );
	}

	/**
	 * Get payroll remittance agency data for one or more agencies.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array
	 */
	function getPayrollRemittanceAgencyEvent( $data = NULL, $disable_paging = FALSE ) {
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( !$this->getPermissionObject()->Check( 'payroll_remittance_agency', 'enabled' )
				OR !( $this->getPermissionObject()->Check( 'payroll_remittance_agency', 'view' ) OR $this->getPermissionObject()->Check( 'payroll_remittance_agency', 'view_own' ) ) ) {
			//return $this->getPermissionObject()->PermissionDenied();
			//Rather then permission denied, restrict to just 'list_view' columns.
			$data['filter_columns'] = $this->handlePermissionFilterColumns( ( isset( $data['filter_columns'] ) ) ? $data['filter_columns'] : NULL, Misc::trimSortPrefix( $this->getOptions( 'list_columns' ) ) );
		}

		$blf = TTnew( 'PayrollRemittanceAgencyEventListFactory' ); /** @var PayrollRemittanceAgencyEventListFactory $blf */
		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
		Debug::Text( 'Record Count: ' . $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $blf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $blf->getRecordCount() );

			$this->setPagerObject( $blf );

			$retarr = array();
			foreach ( $blf as $b_obj ) {
				$retarr[] = $b_obj->getObjectAsArray( $data['filter_columns'] );

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $blf->getCurrentRow() );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( TRUE ); //No records returned.
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonPayrollRemittanceAgencyEventData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getPayrollRemittanceAgencyEvent( $data, TRUE ) ) );
	}

	/**
	 * Validate payroll remittance agency data for one or more agencies.
	 * @param array $data payroll remittance agency data
	 * @return array
	 */
	function validatePayrollRemittanceAgencyEvent( $data ) {
		return $this->setPayrollRemittanceAgencyEvent( $data, TRUE );
	}

	/**
	 * Set payroll remittance agency data for one or more agencies.
	 * @param array $data payroll remittance agency
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setPayrollRemittanceAgencyEvent( $data, $validate_only = FALSE, $ignore_warning = TRUE ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check( 'payroll_remittance_agency', 'enabled' )
				OR !( $this->getPermissionObject()->Check( 'payroll_remittance_agency', 'edit' ) OR $this->getPermissionObject()->Check( 'payroll_remittance_agency', 'edit_own' ) OR $this->getPermissionObject()->Check( 'payroll_remittance_agency', 'add' ) )
		) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == TRUE ) {
			Debug::Text( 'Validating Only!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		list( $data, $total_records ) = $this->convertToMultipleRecords( $data );
		Debug::Text( 'Received data for: ' . $total_records . ' payroll remittance agencies', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0);
		$validator = $save_result = $key = FALSE;
		if ( is_array( $data ) AND $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach ( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'PayrollRemittanceAgencyEventListFactory' ); /** @var PayrollRemittanceAgencyEventListFactory $lf */
				$lf->StartTransaction();
				if ( isset( $row['id'] ) AND $row['id'] != '' ) {
					//Modifying existing object.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
								$validate_only == TRUE
								OR
								(
										$this->getPermissionObject()->Check( 'payroll_remittance_agency', 'edit' )
										OR ( $this->getPermissionObject()->Check( 'payroll_remittance_agency', 'edit_own' ) AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE )
								)
						) {

							Debug::Text( 'Row Exists, getting current data for ID: ' . $row['id'], __FILE__, __LINE__, __METHOD__, 10 );
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
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check( 'payroll_remittance_agency', 'add' ), TTi18n::gettext( 'Add permission denied' ) );
					//Because this class has sub-classes that depend on it, when adding a new record we need to make sure the ID is set first,
					//so the sub-classes can depend on it. We also need to call Save( TRUE, TRUE ) to force a lookup on isNew()
					$row['id'] = $lf->getNextInsertId();
				}
				Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

				$is_valid = $primary_validator->isValid( $ignore_warning );
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Setting object data...', __FILE__, __LINE__, __METHOD__, 10 );

					$lf->setObjectFromArray( $row );

					$lf->Validator->setValidateOnly( $validate_only );

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

				if ( $is_valid == FALSE ) {
					Debug::Text( 'Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10 );

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
	 * Delete one or more payroll remittance agencies.
	 * @param array $data payroll remittance agency data
	 * @return array|bool
	 */
	function deletePayrollRemittanceAgencyEvent( $data ) {
		if ( !is_array( $data ) ) {
			$data = array($data);
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check( 'payroll_remittance_agency', 'enabled' )
				OR !( $this->getPermissionObject()->Check( 'payroll_remittance_agency', 'delete' ) OR $this->getPermissionObject()->Check( 'payroll_remittance_agency', 'delete_own' ) )
		) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' payroll remittance agencies', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$total_records = count( $data );
		$validator = $save_result = $key = FALSE;
		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0);
		if ( is_array( $data ) AND $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach ( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'PayrollRemittanceAgencyEventListFactory' ); /** @var PayrollRemittanceAgencyEventListFactory $lf */
				$lf->StartTransaction();
				if ( $id != '' ) {
					//Modifying existing object.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check( 'payroll_remittance_agency', 'delete' )
								OR ( $this->getPermissionObject()->Check( 'payroll_remittance_agency', 'delete_own' ) AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE )
						) {
							Debug::Text( 'Record Exists, deleting record ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
							$lf = $lf->getCurrent();
						} else {
							$primary_validator->isTrue( 'permission', FALSE, TTi18n::gettext( 'Delete permission denied' ) );
						}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext( 'Delete permission denied, record does not exist' ) );
					}
				} else {
					$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext( 'Delete permission denied, record does not exist' ) );
				}

				//Debug::Arr($lf, 'AData: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Attempting to delete record...', __FILE__, __LINE__, __METHOD__, 10 );
					$lf->setDeleted( TRUE );

					$is_valid = $lf->isValid();
					if ( $is_valid == TRUE ) {
						Debug::Text( 'Record Deleted...', __FILE__, __LINE__, __METHOD__, 10 );
						$save_result[$key] = $lf->Save();
						$validator_stats['valid_records']++;
					}
				}

				if ( $is_valid == FALSE ) {
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

		return $this->returnHandler( FALSE );
	}

	/**
	 * Copy one or more payroll remittance agencies.
	 * @param array $data payroll remittance agency IDs
	 * @return array
	 */
	function copyPayrollRemittanceAgencyEvent( $data ) {
		if ( !is_array( $data ) ) {
			$data = array($data);
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( FALSE );
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' Payroll remittance agencies', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$src_rows = $this->stripReturnHandler( $this->getPayrollRemittanceAgencyEvent( array('filter_data' => array('id' => $data)), TRUE ) );
		if ( is_array( $src_rows ) AND count( $src_rows ) > 0 ) {
			Debug::Arr( $src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $src_rows as $key => $row ) {
				unset( $src_rows[$key]['id'] ); //Clear fields that can't be copied
				unset( $row ); //code standards
			}

			//Debug::Arr($src_rows, 'bSRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->setPayrollRemittanceAgencyEvent( $src_rows ); //Save copied rows
		}

		return $this->returnHandler( FALSE );
	}


	/**
	 * Returns report data intended for the tax wizard
	 * @param $prae_id
	 * @param $report_id
	 * @param null $data
	 * @return array|bool
	 */
	function getReportData( $prae_id, $report_id, $data = NULL ) {
		if ( !$this->getPermissionObject()->Check( 'payroll_remittance_agency', 'enabled' )
				OR !( $this->getPermissionObject()->Check( 'payroll_remittance_agency', 'edit' ) OR $this->getPermissionObject()->Check( 'payroll_remittance_agency', 'edit_own' ) )
		) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		$praelf = TTnew('PayrollRemittanceAgencyEventListFactory'); /** @var PayrollRemittanceAgencyEventListFactory $praelf */
		$praelf->getByIdAndCompanyId( $prae_id, $this->getCurrentCompanyObject()->getId() );
		if ( $praelf->getRecordCount() > 0 ) {
			$user_obj = $this->getCurrentUserObject();
			$permission_obj = $this->getPermissionObject();

			$report_obj = $praelf->getCurrent()->getReport( $report_id, $data, $user_obj, $permission_obj );
			if ( is_object( $report_obj ) ) {
				$report_obj->setAMFMessageId( $this->getAMFMessageId() ); //Be sure to pass along the AMFMessageId so progress bars work properly, specifically from the Tax Wizard.

				$output_arr = $report_obj->getOutput( $report_id );
				if ( isset( $output_arr['file_name'] ) AND isset( $output_arr['mime_type'] ) AND isset( $output_arr['data'] ) ) {
					//If using the SOAP API, return data base64 encoded so it can be decoded on the client side.
					if ( defined( 'TIMETREX_SOAP_API' ) AND TIMETREX_SOAP_API == TRUE ) {
						$output_arr['data'] = base64_encode( $output_arr['data'] );

						$retval = $this->returnHandler( $output_arr );
					} else {
						if ( $output_arr['mime_type'] === 'text/html' ) {
							$retval = $this->returnHandler( $output_arr['data'] );
						} else {
							Misc::APIFileDownload( $output_arr['file_name'], $output_arr['mime_type'], $output_arr['data'] );

							return NULL; //Don't send any additional data, so JSON encoding doesn't corrupt the download.
						}
					}
				} elseif ( isset( $output_arr['api_retval'] ) ) { //Pass through validation errors.
					Debug::Text( 'Report returned VALIDATION error, passing through...', __FILE__, __LINE__, __METHOD__, 10 );

					$retval = $this->returnHandler( $output_arr['api_retval'], $output_arr['api_details']['code'], $output_arr['api_details']['description'] );
				} elseif ( $output_arr !== FALSE ) {
					//Likely RAW data, return untouched.
					$retval = $this->returnHandler( $output_arr );
				} else {
					//getOutput() returned FALSE, some error occurred. Likely load too high though.
					$retval = $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText( 'ERROR: Report is too large, please try again later or narrow your search criteria to decrease the size of your report' ) . '...' );
				}

				return $this->returnHandler( $retval ); //This is double wrapped in returnHandler() for some reason
			} else {
				Debug::Text( 'Report likely has VALIDATION error, show to user...', __FILE__, __LINE__, __METHOD__, 10 );
				return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText( 'ERROR: Unable to generate report, likely due to missing or invalid Form Setup' ) . '...' );
			}
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * Returns make payment data intended for the tax wizard
	 * @param $prae_id
	 * @param $action_id
	 * @param null $data
	 * @return array|bool
	 */
	function getMakePaymentData( $prae_id, $action_id, $data = NULL ) {
		if ( !$this->getPermissionObject()->Check( 'payroll_remittance_agency', 'enabled' )
				OR !( $this->getPermissionObject()->Check( 'payroll_remittance_agency', 'edit' ) OR $this->getPermissionObject()->Check( 'payroll_remittance_agency', 'edit_own' ) )
		) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		$praelf = TTnew('PayrollRemittanceAgencyEventListFactory'); /** @var PayrollRemittanceAgencyEventListFactory $praelf */
		$praelf->getByIdAndCompanyId( $prae_id, $this->getCurrentCompanyObject()->getId() );
		if ( $praelf->getRecordCount() == 1 ) {
			$prae_obj = $praelf->getCurrent();

			$retval = $prae_obj->getURL( $action_id );

			return $this->returnHandler( $retval );
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * Submits data to Payment Services for filing and payment.
	 * @param $prae_id
	 * @param $action_id
	 * @param null $data
	 * @return array|bool
	 */
	function getFileAndPayWithPaymentServicesData( $prae_id, $action_id, $data = NULL ) {
		if ( !$this->getPermissionObject()->Check( 'payroll_remittance_agency', 'enabled' )
				OR !( $this->getPermissionObject()->Check( 'payroll_remittance_agency', 'edit' ) OR $this->getPermissionObject()->Check( 'payroll_remittance_agency', 'edit_own' ) )
		) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		$praelf = TTnew('PayrollRemittanceAgencyEventListFactory'); /** @var PayrollRemittanceAgencyEventListFactory $praelf */
		$praelf->getByIdAndCompanyId( $prae_id, $this->getCurrentCompanyObject()->getId() );
		if ( $praelf->getRecordCount() == 1 ) {
			$prae_obj = $praelf->getCurrent();

			if ( $prae_obj->getStatus() == 15 ) { //15=Full Service
				$retval = array(
						'result' => FALSE,
						'user_message' => TTi18n::gettext('ERROR: General error occurred, please contact customer service immediately.'),
				);

				if ( is_object( $prae_obj->getPayrollRemittanceAgencyObject() ) ) {
					$pra_obj = $prae_obj->getPayrollRemittanceAgencyObject();

					$rs_obj = $pra_obj->getRemittanceSourceAccountObject();

					$le_obj = $rs_obj->getLegalEntityObject();

					if ( $rs_obj->getType() == 3000 AND $rs_obj->getDataFormat() == 5 ) { //3000=EFT/ACH, 5=TimeTrex EFT

						if ( is_object( $pra_obj->getContactUserObject() ) ) {
							Debug::Text( '  Agency Event: Agency: ' . $prae_obj->getPayrollRemittanceAgencyObject()->getName() . ' Legal Entity: ' . $prae_obj->getPayrollRemittanceAgencyObject()->getLegalEntity() . ' Type: ' . $prae_obj->getType() . ' Due Date: ' . TTDate::getDate( 'DATE', $prae_obj->getDueDate() ) . ' ID: ' . $prae_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
							Debug::Text( '  Remittance Source: Name: ' . $rs_obj->getName() . ' API Username: ' . $rs_obj->getValue5(), __FILE__, __LINE__, __METHOD__, 10 );

							$pra_user_obj = $pra_obj->getContactUserObject();

							$report_obj = $prae_obj->getReport( 'raw', NULL, $pra_user_obj, new Permission() );
							//$report_obj = $prae_obj->getReport( '123456', NULL, $pra_user_obj, new Permission() ); //Test with generic TaxSummaryReport

							$output_data = $report_obj->getPaymentServicesData( $prae_obj, $pra_obj, $rs_obj, $pra_user_obj );

							Debug::Arr( $output_data, 'Report Payment Services Data: ', __FILE__, __LINE__, __METHOD__, 10 );
							if ( is_array( $output_data ) ) {
								if ( !isset($output_data['user_success_message']) ) {
									$output_data['user_success_message'] = TTi18n::gettext('Data submitted successfully.' );
								}

								if ( PRODUCTION == TRUE AND is_object( $le_obj ) AND $le_obj->getPaymentServicesStatus() == 10 AND $le_obj->getPaymentServicesUserName() != '' AND $le_obj->getPaymentServicesAPIKey() != '' ) { //10=Enabled
									try {
										$tt_ps_api = $le_obj->getPaymentServicesAPIObject();

										$agency_report_arr = $tt_ps_api->convertReportPaymentServicesDataToAgencyReportArray( $output_data, $prae_obj, $pra_obj, $rs_obj, $pra_user_obj );

										$retval = $tt_ps_api->setAgencyReport( $agency_report_arr ); //P=Payment
										Debug::Arr( $retval, 'TimeTrexPaymentServices Retval: ', __FILE__, __LINE__, __METHOD__, 10 );
										if ( $retval->isValid() == TRUE ) {
											$retval = array(
													'result'       => TRUE,
													'user_message' => $output_data['user_success_message'],
											);
										} else {
											$retval = array(
													'result' => FALSE,
													'user_message' => TTi18n::gettext('Payment Services Validation ERROR: %1', array( $retval->getDescription() ) ),
											);
										}
										unset( $agency_report_arr );

									} catch ( Exception $e ) {
										Debug::Text( 'ERROR! Unable to upload agency report data... (b) Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
										$retval = array(
												'result' => FALSE,
												'user_message' => TTi18n::gettext('Payment Services ERROR: %1', array( $e->getMessage() ) ),
										);
									}
									unset( $tt_ps_api, $agency_report_arr, $batch_id, $remote_id );
								} else {
									Debug::Text( 'WARNING: Production is off, not calling payment services API...', __FILE__, __LINE__, __METHOD__, 10 );
									$retval = array(
											'result' => FALSE,
											'user_message' => TTi18n::gettext('WARNING: Not in PRODUCTION mode, unable to send data to Payment Services.'),
									);

									//$retval = TRUE;
								}
							} else {
								Debug::Arr( $output_data, 'Report returned unexpected number of rows, not transmitting...', __FILE__, __LINE__, __METHOD__, 10 );
								$retval = array(
										'result' => FALSE,
										'user_message' => TTi18n::gettext('ERROR: Unable to generate report to for transmission to Payment Services.'),
								);

							}
						} else {
							Debug::Text( '  ERROR! Contact user assign to agency is invalid!', __FILE__, __LINE__, __METHOD__, 10 );
						}
					} else {
						Debug::Text( '  ERROR! Remittance Source Account is not EFT or TimeTrex Payment Services!', __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::Text( '  ERROR! Remittance Agency Object is invalid!', __FILE__, __LINE__, __METHOD__, 10 );
				}


			} else {
				$retval = array(
						'result' => FALSE,
						'user_message' => TTi18n::gettext('ERROR: Agency event is not configured for full service processing.'),
				);
			}

			return $this->returnHandler( $retval );
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function calculateNextRunDate( $data ) {
		$praef = new PayrollRemittanceAgencyEventFactory();
		$praef->setObjectFromArray( $data );
		$due_date_array = $praef->calculateNextDate();
		$due_date = $due_date_array['due_date'];
		if ( $due_date_array != FALSE ) {
			$next_reminder_date = $praef->calculateNextReminderDate( $due_date );

			return array(
					'start_date'         => TTDate::getDate( 'DATE', $due_date_array['start_date'] ),
					'end_date'           => TTDate::getDate( 'DATE', $due_date_array['end_date'] ),
					'due_date'           => TTDate::getDate( 'DATE', $due_date ),
					'next_reminder_date' => TTDate::getDate( 'DATE+TIME', $next_reminder_date ),
			);
		}

		return FALSE;
	}
}

?>
