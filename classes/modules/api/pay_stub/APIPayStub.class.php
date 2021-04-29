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
 * @package API\PayStub
 */
class APIPayStub extends APIFactory {
	protected $main_class = 'PayStubFactory';

	/**
	 * APIPayStub constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * overridden to get different columns based on permissions.
	 * @param bool $name
	 * @param null $parent
	 * @return bool|object
	 */
	function getOptions( $name = false, $parent = null ) {
		if ( $name == 'columns'
				&& ( !$this->getPermissionObject()->Check( 'pay_stub', 'enabled' )
						|| !( $this->getPermissionObject()->Check( 'pay_stub', 'view' ) || $this->getPermissionObject()->Check( 'pay_stub', 'view_child' ) ) ) ) {
			$name = 'list_columns';
		}

		return parent::getOptions( $name, $parent );
	}

	/**
	 * Get default paystub_entry_account data for creating new paystub_entry_accountes.
	 * @return array
	 */
	function getPayStubDefaultData() {
		$company_obj = $this->getCurrentCompanyObject();
		$user_obj = $this->getCurrentUserObject();

		Debug::Text( 'Getting pay stub entry default data...', __FILE__, __LINE__, __METHOD__, 10 );

		//Get earliest OPEN pay period.
		$pplf = TTNew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
		$pplf->getLastPayPeriodByCompanyIdAndPayPeriodScheduleIdAndDate( $company_obj->getId(), null, time() );
		if ( $pplf->getRecordCount() > 0 ) {
			$pp_obj = $pplf->getCurrent();

			$pay_period_id = $pp_obj->getId();
			$start_date = TTDate::getDate( 'DATE', $pp_obj->getStartDate() );
			$end_date = TTDate::getDate( 'DATE', $pp_obj->getEndDate() );
			$transaction_date = TTDate::getDate( 'DATE', $pp_obj->getTransactionDate() );
		} else {
			$pay_period_id = TTUUID::getZeroID();
			$start_date = TTDate::getDate( 'DATE', time() );
			$end_date = TTDate::getDate( 'DATE', time() );
			$transaction_date = TTDate::getDate( 'DATE', time() );
		}

		$run_id = $this->stripReturnHandler( $this->getCurrentPayRun( $pay_period_id ) );

		$data = [
				'company_id'       => $company_obj->getId(),
				'user_id'          => $user_obj->getId(),
				'currency_id'      => $user_obj->getCurrency(),
				'pay_period_id'    => $pay_period_id,
				'run_id'           => $run_id,
				'start_date'       => $start_date,
				'end_date'         => $end_date,
				'transaction_date' => $transaction_date,
		];

		return $this->returnHandler( $data );
	}

	/**
	 * Get pay_stub data for one or more pay_stubes.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @param bool $format
	 * @param bool $hide_employer_rows
	 * @return array|bool
	 */
	function getPayStub( $data = null, $disable_paging = false, $format = false, $hide_employer_rows = true ) {
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'pay_stub', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'pay_stub', 'view' ) || $this->getPermissionObject()->Check( 'pay_stub', 'view_own' ) || $this->getPermissionObject()->Check( 'pay_stub', 'view_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		$format = Misc::trimSortPrefix( $format );
		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'pay_stub', 'view' );

		if ( $this->getPermissionObject()->Check( 'pay_stub', 'view' ) == false && $this->getPermissionObject()->Check( 'pay_stub', 'view_child' ) == false ) {
			//Only display PAID pay stubs.
			$data['filter_data']['status_id'] = [ 40 ];
		}

		//Always hide employer rows unless they have permissions to view all pay stubs.
		if ( $this->getPermissionObject()->Check( 'pay_stub', 'view' ) == false ) {
			$hide_employer_rows = true;
		}

		if ( ( $format == 'export_transactions' ) && $this->getPermissionObject()->Check( 'pay_stub', 'view' ) == true ) {
			//Always enable debug logging during transaction export.
			Debug::setEnable( true );
			Debug::setBufferOutput( true );
			Debug::setEnableLog( true );
			Debug::setVerbosity( 10 );

			if ( isset( $data['filter_data']['time_period'] ) && is_array( $data['filter_data']['time_period'] ) ) {
				$report_obj = TTnew( 'Report' ); /** @var Report $report_obj */
				$report_obj->setUserObject( $this->getCurrentUserObject() );
				$report_obj->setPermissionObject( $this->getPermissionObject() );
				Debug::Text( 'Found TimePeriod...', __FILE__, __LINE__, __METHOD__, 10 );
				$data['filter_data'] = array_merge( $data['filter_data'], (array)$report_obj->convertTimePeriodToStartEndDate( $data['filter_data']['time_period'] ) );
				unset( $report_obj );
			}

			//These filters are also in APIPayStubTransaction->getPayPeriodTransactionSummary().
			$data['filter_data']['transaction_status_id'] = [ 10, 200 ]; //10=Pending, 200=ReIssue
			$data['filter_data']['transaction_type_id'] = 10;            //10=Valid (Enabled)
			if ( isset( $data['filter_data']['id'] ) ) {
				$data['filter_data']['pay_stub_id'] = $data['filter_data']['id'];
			}
			unset( $data['filter_data']['id'] );

			//Specific sort order to ensure consistent transaction order in the EFT files. Keep in mind exportPayStubTransaction() sorts the transactions again too, but this helps.
			$data['filter_sort'] = [ 'lef.id' => 'asc', 'rsaf.id' => 'asc', 'psf.transaction_date' => 'asc', 'destination_user_last_name' => 'asc', 'destination_user_first_name' => 'asc', 'rdaf.id' => 'asc' ];

			$pslf = TTnew( 'PayStubTransactionListFactory' ); /** @var PayStubTransactionListFactory $pslf */
			$pslf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentUserObject()->getCompany(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], null, $data['filter_sort'] );
			Debug::Text( 'PSTLF Record Count: ' . $pslf->getRecordCount() . ' Format: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );
		} else {
			$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
			$pslf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], null, $data['filter_sort'] );
			Debug::Text( 'PSLF Record Count: ' . $pslf->getRecordCount() . ' Format: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );
		}

		if ( $format == 'pdf' ) {
			if ( $pslf->getRecordCount() > 0 ) {
				$this->getProgressBarObject()->setDefaultKey( $this->getAPIMessageID() );
				$this->getProgressBarObject()->start( $this->getAPIMessageID(), $pslf->getRecordCount() );
				$pslf->setProgressBarObject( $this->getProgressBarObject() ); //Expose progress bar object to pay stub object.

				$output = $pslf->getPayStub( $pslf, (bool)$hide_employer_rows );

				$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

				if ( $output != '' ) {
					return Misc::APIFileDownload( 'pay_stub.pdf', 'application/pdf', $output );
				} else {
					return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'ERROR: No data to export...' ) );
				}
			}
		} else if ( ( $format == 'export_transactions' ) && $this->getPermissionObject()->Check( 'pay_stub', 'view' ) == true ) {
			if ( $pslf->getRecordCount() > 0 ) {
				$this->getProgressBarObject()->setDefaultKey( $this->getAPIMessageID() );
				$this->getProgressBarObject()->start( $this->getAPIMessageID(), $pslf->getRecordCount() );
				$pslf->setProgressBarObject( $this->getProgressBarObject() ); //Expose progress bar object to pay stub object.

				$output = $pslf->exportPayStubTransaction( $pslf, null, $data['setup_last_check_number'] );

				$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

				if ( is_array( $output ) && count( $output ) > 0 ) {
					//Transmit agency reports to TimeTrex Payment Services
					$pslf->exportPayStubRemittanceAgencyReports( $pslf );

					$filename = 'pay_stub_transactions_' . TTDate::getDate( 'DATE', time() ) . '.zip';
					$zip_file = Misc::zip( $output, $filename, true );
					if ( is_array( $zip_file ) && isset( $zip_file['file_name'] ) && isset( $zip_file['mime_type'] ) && isset( $zip_file['data'] ) ) { //Was just: $zip_file !== FALSE
						return Misc::APIFileDownload( $zip_file['file_name'], $zip_file['mime_type'], $zip_file['data'] );
					} else {
						//FIXME: Return UserGenericStatus ID instead? Or at least some message showing success.
						Debug::Arr( $output, 'No Zip file to download, perhaps transactions were processed with PaymentServices API?', __FILE__, __LINE__, __METHOD__, 10 );

						return true;
					}
				} else {
					return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'ERROR: No data to export...' ) );
				}
			} else {
				return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'All transactions have already been processed' ) );
			}
		} else {
			if ( $pslf->getRecordCount() > 0 ) {
				$this->getProgressBarObject()->start( $this->getAPIMessageID(), $pslf->getRecordCount() );

				$this->setPagerObject( $pslf );

				$retarr = [];
				foreach ( $pslf as $ps_obj ) {
					$retarr[] = $ps_obj->getObjectAsArray( $data['filter_columns'], $data['filter_data']['permission_children_ids'] );

					$this->getProgressBarObject()->set( $this->getAPIMessageID(), $pslf->getCurrentRow() );
				}

				$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

				return $this->returnHandler( $retarr );
			}

			return $this->returnHandler( true ); //No records returned.
		}

		return $this->returnHandler( false );
	}

	/**
	 * Export data to csv
	 * @param string $format file format (csv)
	 * @param array $data    filter data
	 * @param bool $disable_paging
	 * @return array
	 */
	function exportPayStub( $format = 'csv', $data = null, $disable_paging = true ) {
		$result = $this->stripReturnHandler( $this->getPayStub( $data, $disable_paging ) );

		return $this->exportRecords( $format, 'export_pay_stub', $result, ( ( isset( $data['filter_columns'] ) ) ? $data['filter_columns'] : null ) );
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonPayStubData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getPayStub( $data, true ) ) );
	}

	/**
	 * Validate pay_stub data for one or more pay_stubes.
	 * @param array $data pay_stub data
	 * @return array
	 */
	function validatePayStub( $data ) {
		return $this->setPayStub( $data, true );
	}

	/**
	 * Set pay_stub data for one or more pay_stubes.
	 * @param array $data pay_stub data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setPayStub( $data, $validate_only = false, $ignore_warning = true ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'pay_stub', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'pay_stub', 'edit' ) || $this->getPermissionObject()->Check( 'pay_stub', 'edit_own' ) || $this->getPermissionObject()->Check( 'pay_stub', 'edit_child' ) || $this->getPermissionObject()->Check( 'pay_stub', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == true ) {
			Debug::Text( 'Validating Only!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		list( $data, $total_records ) = $this->convertToMultipleRecords( $data );
		Debug::Text( 'Received data for: ' . $total_records . ' PayStubs', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			foreach ( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $lf */
				$lf->StartTransaction();
				if ( isset( $row['id'] ) && $row['id'] != '' ) {
					//Modifying existing object.
					//Get pay_stub object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
								$validate_only == true
								||
								(
										$this->getPermissionObject()->Check( 'pay_stub', 'edit' )
										|| ( $this->getPermissionObject()->Check( 'pay_stub', 'edit_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getUser() ) === true )
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
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check( 'pay_stub', 'add' ), TTi18n::gettext( 'Add permission denied' ) );

					//Because this class has sub-classes that depend on it, when adding a new record we need to make sure the ID is set first,
					//so the sub-classes can depend on it. We also need to call Save( TRUE, TRUE ) to force a lookup on isNew()
					$row['id'] = $lf->getNextInsertId();
				}
				Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

				$is_valid = $primary_validator->isValid( $ignore_warning );
				if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Setting object data...', __FILE__, __LINE__, __METHOD__, 10 );

					$lf->setObjectFromArray( $row );

					if ( ( isset( $row['entries'] ) && is_array( $row['entries'] ) && count( $row['entries'] ) > 0 ) ) {
						Debug::Text( ' Found modified entries!', __FILE__, __LINE__, __METHOD__, 10 );

						//Load previous pay stub
						$lf->loadPreviousPayStub();

						//Delete all entries, so they can be re-added.
						//$lf->deleteEntries( TRUE );

						//When editing pay stubs we can't re-process linked accruals.
						$lf->setEnableLinkedAccruals( false );

						$processed_entries = 0;
						foreach ( $row['entries'] as $pay_stub_entry ) {
							if ( (
											( isset( $pay_stub_entry['id'] ) && TTUUID::isUUID( $pay_stub_entry['id'] ) && $pay_stub_entry['id'] != TTUUID::getZeroID() && $pay_stub_entry['id'] != TTUUID::getNotExistID() )
											||
											( isset( $pay_stub_entry['pay_stub_entry_name_id'] ) && TTUUID::isUUID( $pay_stub_entry['pay_stub_entry_name_id'] ) )
									)
									&&
									(
											!isset( $pay_stub_entry['type'] )
											||
											( isset( $pay_stub_entry['type'] ) && $pay_stub_entry['type'] != 40 )
									)
									&& isset( $pay_stub_entry['amount'] )
							) {
								Debug::Text( 'Pay Stub Entry ID: ' . ( ( isset($pay_stub_entry['id']) ) ? $pay_stub_entry['id'] : 'N/A' ) . ' Amount: ' . $pay_stub_entry['amount'] . ' Pay Stub ID: ' . $row['id'], __FILE__, __LINE__, __METHOD__, 10 );

								//Populate $pay_stub_entry_obj so we can find validation errors before postSave() is called.
								if ( isset( $pay_stub_entry['id'] ) && $pay_stub_entry['id'] != '' && TTUUID::isUUID( $pay_stub_entry['id'] ) ) {
									$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */
									$pself->getById( $pay_stub_entry['id'] );
									if ( $pself->getRecordCount() > 0 ) {
										$pay_stub_entry_obj = $pself->getCurrent();
									} else {
										$pay_stub_entry_obj = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pay_stub_entry_obj */
									}
								} else {
									$pay_stub_entry_obj = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pay_stub_entry_obj */
									//$pay_stub_entry_obj->setPayStub( $lf->getId() ); //Don't set this here as it will cause validation failures. Its handled in addEntry instead.
								}

								if ( isset( $pay_stub_entry['deleted'] ) && $pay_stub_entry['deleted'] == 1 ) {
									// Deleted is set instead of populating the object to provide for the case where a
									// user enters invalid data then deletes the row, removing it from the UI
									$pay_stub_entry_obj->setDeleted( true );
								} else {
									if ( isset( $pay_stub_entry['pay_stub_entry_name_id'] ) && $pay_stub_entry['pay_stub_entry_name_id'] != '' ) {
										$pay_stub_entry_obj->setPayStubEntryNameId( $pay_stub_entry['pay_stub_entry_name_id'] );
									}

									if ( isset( $pay_stub_entry['pay_stub_amendment_id'] ) && $pay_stub_entry['pay_stub_amendment_id'] != '' ) {
										$pay_stub_entry_obj->setPayStubAmendment( $pay_stub_entry['pay_stub_amendment_id'], $lf->getStartDate(), $lf->getEndDate() );
									}

									if ( isset( $pay_stub_entry['rate'] ) && $pay_stub_entry['rate'] != '' ) {
										$pay_stub_entry_obj->setRate( $pay_stub_entry['rate'] );
									}
									if ( isset( $pay_stub_entry['units'] ) && $pay_stub_entry['units'] != '' ) {
										$pay_stub_entry_obj->setUnits( $pay_stub_entry['units'] );
									}

									if ( isset( $pay_stub_entry['amount'] ) && $pay_stub_entry['amount'] != '' ) {
										$pay_stub_entry_obj->setAmount( $pay_stub_entry['amount'] );
									}

									if ( !isset( $pay_stub_entry['units'] ) || $pay_stub_entry['units'] == '' ) {
										$pay_stub_entry['units'] = 0;
									}
									if ( !isset( $pay_stub_entry['rate'] ) || $pay_stub_entry['rate'] == '' ) {
										$pay_stub_entry['rate'] = 0;
									}
									if ( !isset( $pay_stub_entry['description'] ) || $pay_stub_entry['description'] == '' ) {
										$pay_stub_entry['description'] = null;
									}
									if ( !isset( $pay_stub_entry['pay_stub_amendment_id'] ) || $pay_stub_entry['pay_stub_amendment_id'] == '' ) {
										$pay_stub_entry['pay_stub_amendment_id'] = null;
									}
									if ( !isset( $pay_stub_entry['user_expense_id'] ) || $pay_stub_entry['user_expense_id'] == '' ) {
										$pay_stub_entry['user_expense_id'] = null;
									}

									$ytd_adjustment = false;
									if ( TTUUID::isUUID( $pay_stub_entry['pay_stub_amendment_id'] ) && $pay_stub_entry['pay_stub_amendment_id'] != TTUUID::getZeroID() && $pay_stub_entry['pay_stub_amendment_id'] != TTUUID::getNotExistID() ) {
										$psamlf = TTNew( 'PayStubAmendmentListFactory' ); /** @var PayStubAmendmentListFactory $psamlf */
										$psamlf->getByIdAndCompanyId( TTUUID::castUUID( $pay_stub_entry['pay_stub_amendment_id'] ), $this->getCurrentCompanyObject()->getId() );
										if ( $psamlf->getRecordCount() > 0 ) {
											$ytd_adjustment = $psamlf->getCurrent()->getYTDAdjustment();
										}
										Debug::Text( ' Pay Stub Amendment Id: ' . $pay_stub_entry['pay_stub_amendment_id'] . ' YTD Adjusment: ' . (int)$ytd_adjustment, __FILE__, __LINE__, __METHOD__, 10 );
									}
								}

								if ( $pay_stub_entry_obj->isValid() == true ) {
									if ( $pay_stub_entry_obj->getDeleted() == true ) {
										//Since addEntry() doesn't get passed an object, it can't delete entries, so we need to handle it outside of that function instead.
										$pay_stub_entry_obj->Save();
									} else {
										$lf->addEntry( $pay_stub_entry['pay_stub_entry_name_id'], $pay_stub_entry['amount'], $pay_stub_entry['units'], $pay_stub_entry['rate'], $pay_stub_entry['description'], $pay_stub_entry['pay_stub_amendment_id'], null, null, $ytd_adjustment, $pay_stub_entry['user_expense_id'] );
									}
									$processed_entries++;
								} else {
									Debug::Text( ' ERROR: Unable to save PayStubEntry... ', __FILE__, __LINE__, __METHOD__, 10 );
									$tmp_pay_stub_entry_account_name = TTi18n::getText( 'N/A' );
									if ( is_object( $pay_stub_entry_obj->getPayStubEntryAccountObject() ) ) {
										$tmp_pay_stub_entry_account_name = $pay_stub_entry_obj->getPayStubEntryAccountObject()->getName();
									}

									$lf->Validator->isTrue( 'pay_stub_entry', false, TTi18n::getText( '%1 entry for amount: %2 is invalid', [ $tmp_pay_stub_entry_account_name, Misc::MoneyFormat( $pay_stub_entry['amount'] ) ] ) );
								}
							} else {
								Debug::Text( ' Skipping Total Entry. ', __FILE__, __LINE__, __METHOD__, 10 );
							}
							unset( $pay_stub_entry_obj );
						}
						unset( $pay_stub_entry );

						if ( $processed_entries > 0 ) {
							$lf->setTainted( true ); //Make sure tainted flag is set when any entries are processed.
							$lf->setEnableCalcYTD( true );
							$lf->setEnableProcessEntries( true );
							$lf->processEntries();
						}
					} else {
						Debug::Text( ' Skipping ALL Entries... ', __FILE__, __LINE__, __METHOD__, 10 );
					}

					if ( ( isset( $row['transactions'] ) && is_array( $row['transactions'] ) && count( $row['transactions'] ) > 0 ) ) {
						Debug::Text( ' Found modified transactions!', __FILE__, __LINE__, __METHOD__, 10 );
						$processed_transactions = 0;
						if ( count( $row['transactions'] ) > 0 ) {
							foreach ( $row['transactions'] as $pay_stub_transaction ) {
								//Debug::Arr($pay_stub_transaction,'Paystub transaction row...', __FILE__, __LINE__, __METHOD__, 10);
								if ( $pay_stub_transaction['amount'] == 0 && $pay_stub_transaction['remittance_destination_account_id'] == TTUUID::getZeroID() ) { //Skip any transactions of $0.00
									continue;
								}

								if ( isset( $pay_stub_transaction['id'] )
										&& TTUUID::isUUID( $pay_stub_transaction['id'] ) && $pay_stub_transaction['id'] != TTUUID::getZeroID() && $pay_stub_transaction['id'] != TTUUID::getNotExistID() ) {
									$pstlf = TTnew( 'PayStubTransactionListFactory' ); /** @var PayStubTransactionListFactory $pstlf */
									$pstlf->getByIdAndCompanyId( $pay_stub_transaction['id'], $this->getCurrentCompanyObject()->getId() );
									if ( $pstlf->getRecordCount() > 0 ) {
										$pst_obj = $pstlf->getCurrent();
									}
									unset( $pstlf );
								} else {
									$pst_obj = TTnew( 'PayStubTransactionFactory' ); /** @var PayStubTransactionFactory $pst_obj */
									//$pst_obj->setPayStub( $lf->getId() ); //Don't set this here as it will cause validation failures. Its handled in addTransaction() instead.
								}

								$pst_obj->setType( 10 ); //10=Valid

								if ( isset( $pay_stub_transaction['status_id'] ) && $pay_stub_transaction['status_id'] != '' ) {
									$pst_obj->setStatus( $pay_stub_transaction['status_id'] );
								} else {
									$pst_obj->setStatus( 10 ); //10=Pending
								}

								if ( isset( $pay_stub_transaction['deleted'] ) && $pay_stub_transaction['deleted'] == 1 ) {
									// Deleted is set instead of populating the object to provide for the case where a
									// user enters invalid data then deletes the row, removing it from the UI
									$pst_obj->setDeleted( true );
								} else {
									if ( isset( $pay_stub_transaction['remittance_destination_account_id'] ) ) {
										$pst_obj->setRemittanceDestinationAccount( $pay_stub_transaction['remittance_destination_account_id'] );
									}

									//Make sure remittance source account and currency is set so we don't have to rely on preSave(), which causes issues with validation.
									if ( is_object( $pst_obj->getRemittanceDestinationAccountObject() ) ) {
										$pst_obj->setRemittanceSourceAccount( $pst_obj->getRemittanceDestinationAccountObject()->getRemittanceSourceAccount() );
									}

									if ( $pst_obj->getCurrency() == false ) {
										if ( is_object( $pst_obj->getRemittanceSourceAccountObject() ) ) {
											$pst_obj->setCurrency( $pst_obj->getRemittanceSourceAccountObject()->getCurrency() );
										} else if ( is_object( $pst_obj->getPayStubObject() ) ) {
											$pst_obj->setCurrency( $pst_obj->getPayStubObject()->getCurrency() );
										}
									}

									if ( isset( $pay_stub_transaction['transaction_date'] ) ) {
										$pst_obj->setTransactionDate( TTDate::parseDateTime( $pay_stub_transaction['transaction_date'] ) );
									}

									if ( isset( $pay_stub_transaction['amount'] ) ) {
										$pst_obj->setAmount( $pay_stub_transaction['amount'] );
									} else {
										$pst_obj->setAmount( 0 );
									}

									if ( isset( $pay_stub_transaction['note'] ) ) {
										$pst_obj->setNote( $pay_stub_transaction['note'] );
									}
								}

								if ( $pst_obj->isValid() ) {
									$lf->addTransaction( $pst_obj );
									$processed_transactions++;
								} else {
									if ( isset( $pay_stub_transaction['deleted'] ) == false || $pay_stub_transaction['deleted'] == 0 ) {
										$tmp_remittance_destination_account_name = TTi18n::getText( 'N/A' );
										if ( is_object( $pst_obj->getRemittanceDestinationAccountObject() ) ) {
											$tmp_remittance_destination_account_name = $pst_obj->getRemittanceDestinationAccountObject()->getName();
										}
										$lf->Validator->isTrue( 'pay_stub_transaction', false, TTi18n::getText( '%1 transaction for amount: %2 is invalid', [ $tmp_remittance_destination_account_name, Misc::MoneyFormat( $pst_obj->getAmount() ) ] ) );
										unset( $tmp_remittance_destination_account_name );
									}
								}
								unset( $pst_obj );
							}
						}

						if ( $processed_transactions > 0 ) {
							$lf->setTainted( true ); //Make sure tainted flag is set when any entries are processed.
							$lf->setEnableProcessTransactions( true );
							//$lf->processTransactions();
						}
					} else {
						Debug::Text( ' Skipping ALL transactions... ', __FILE__, __LINE__, __METHOD__, 10 );
					}

					$lf->Validator->setValidateOnly( $validate_only );

					$is_valid = $lf->isValid( $ignore_warning );
					if ( $is_valid == true ) {
						Debug::Text( 'Saving data...', __FILE__, __LINE__, __METHOD__, 10 );
						if ( $validate_only == true ) {
							$save_result[$key] = true;
						} else {
							$save_result[$key] = $lf->Save( true, true );
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

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}

	/**
	 * Delete one or more pay_stubs.
	 * @param array $data pay_stub data
	 * @return array|bool
	 */
	function deletePayStub( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'pay_stub', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'pay_stub', 'delete' ) || $this->getPermissionObject()->Check( 'pay_stub', 'delete_own' ) || $this->getPermissionObject()->Check( 'pay_stub', 'delete_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' PayStubs', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$total_records = count( $data );
		$validator = $save_result = $key = false;
		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			foreach ( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $lf */
				$lf->StartTransaction();
				if ( $id != '' ) {
					//Modifying existing object.
					//Get pay_stub object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check( 'pay_stub', 'delete' )
								|| ( $this->getPermissionObject()->Check( 'pay_stub', 'delete_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getUser() ) === true ) ) {
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

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}

	/**
	 * @param string $pay_period_ids UUID
	 * @param string $user_ids       UUID
	 * @param bool $enable_correction
	 * @param bool $run_id
	 * @param int $type_id
	 * @param int $transaction_date  EPOCH
	 * @return array|bool
	 */
	function generatePayStubs( $pay_period_ids, $user_ids = null, $enable_correction = false, $run_id = false, $type_id = 10, $transaction_date = null ) {
		global $profiler;
		Debug::Text( 'Generate Pay Stubs!', __FILE__, __LINE__, __METHOD__, 10 );

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( $this->getCurrentUserObject()->getStatus() != 10 ) { //10=Active -- Make sure user record is active as well.
			return $this->getPermissionObject()->PermissionDenied( false, TTi18n::getText( 'Employee status must be Active to Generate Pay Stubs' ) );
		}

		if ( !( $this->getPermissionObject()->Check( 'pay_period_schedule', 'enabled' )
				&& ( $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit_own' ) )
				&& ( $this->getPermissionObject()->Check( 'pay_stub', 'view' ) || $this->getPermissionObject()->Check( 'pay_stub', 'view_child' ) ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( !is_array( $pay_period_ids ) ) {
			$pay_period_ids = [ $pay_period_ids ];
		}
		$pay_period_ids = array_unique( $pay_period_ids );


		if ( $user_ids !== null && !is_array( $user_ids ) && $user_ids != '' ) {
			$user_ids = [ $user_ids ];
		} else if ( is_array( $user_ids ) && isset( $user_ids[0] ) && $user_ids[0] == TTUUID::getZeroID() ) {
			$user_ids = null;
		}

		if ( is_array( $user_ids ) ) {
			$user_ids = array_unique( $user_ids );
		}

		if ( $type_id == 5 ) { //Post-Adjustment Carry-Forward, enable correction and force type to Normal.
			$enable_correction = true;
			$type_id = 10;
		}

		$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
		$pplf->getByIdAndCompanyId( $pay_period_ids, $this->getCurrentCompanyObject()->getId(), null, [ 'start_date' => 'asc' ] ); //Make sure pay periods are ordered by start date asc so they are calculated in order if the user happens to calculate multiple pay periods over a long period of time.
		foreach ( $pplf as $pay_period_obj ) {
			/** @var PayPeriodFactory $pay_period_obj */
			$epoch = TTDate::getTime();

			Debug::text( 'Pay Period ID: ' . $pay_period_obj->getID() . ' Schedule ID: ' . $pay_period_obj->getPayPeriodSchedule() . ' Start Date: ' . TTDate::getDate( 'DATE', $pay_period_obj->getStartDate() ), __FILE__, __LINE__, __METHOD__, 10 );
			if ( PRODUCTION == FALSE || $pay_period_obj->isPreviousPayPeriodClosed() == true ) { //Allow generating pay stubs without closing each pay period when not in production.
				$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */

				if ( (int)$run_id == 0 ) {
					$run_id = PayStubListFactory::getCurrentPayRun( $this->getCurrentCompanyObject()->getId(), $pay_period_obj->getId() );
				}
				Debug::text( '  Using Run ID: ' . $run_id, __FILE__, __LINE__, __METHOD__, 10 );

				//Check to make sure pay stubs with a transaction date before today are not open, as that can cause the payroll run number to be incorrectly determined on its own.
				$open_pay_stub_transaction_date = ( TTDate::getMiddleDayEpoch( $epoch ) >= TTDate::getMiddleDayEpoch( $pay_period_obj->getTransactionDate() ) ) ? $pay_period_obj->getTransactionDate() : TTDate::getBeginDayEpoch( $epoch );
				$pslf->getByCompanyIdAndPayPeriodIdAndStatusIdAndTransactionDateBeforeDate( $this->getCurrentCompanyObject()->getId(), $pay_period_obj->getID(), [ 25 ], $open_pay_stub_transaction_date, 1 );
				if ( $pslf->getRecordCount() > 0 ) {
					UserGenericStatusFactory::queueGenericStatus( TTi18n::gettext( 'ERROR' ), 10, TTi18n::gettext( 'Pay Stubs with a transaction date before today are still OPEN, all pay stubs must be PAID on or before their transaction date' ), null );
					continue;
				}
				unset( $open_pay_stub_transaction_date );

				if ( $run_id > 1 ) { //Check to make sure prior payroll runs are marked as PAID.

					$pslf->getByCompanyIdAndPayPeriodIdAndStatusIdAndNotRun( $this->getCurrentCompanyObject()->getId(), $pay_period_obj->getId(), [ 10, 20, 25, 30 ], $run_id, 1 ); //Only need to return 1 record.
					if ( $pslf->getRecordCount() > 0 ) {
						$tmp_pay_stub_obj = $pslf->getCurrent();
						Debug::text( 'Pay Stub ID: ' . $tmp_pay_stub_obj->getID() . ' Run: ' . $tmp_pay_stub_obj->getRun() . ' Transaction Date: ' . TTDate::getDate( 'DATE', $tmp_pay_stub_obj->getTransactionDate() ), __FILE__, __LINE__, __METHOD__, 10 );
						UserGenericStatusFactory::queueGenericStatus( TTi18n::gettext( 'ERROR' ), 10, TTi18n::gettext( 'Payroll Run #%1 of Pay Period %2 is still OPEN, all pay stubs must be PAID before starting a new payroll run.', [ $tmp_pay_stub_obj->getRun(), TTDate::getDate( 'DATE', $pay_period_obj->getStartDate() ) . ' -> ' . TTDate::getDate( 'DATE', $pay_period_obj->getEndDate() ) ] ), null );
						unset( $tmp_pay_stub_obj );
						continue;
					}
				}
				unset( $pslf );

				//Grab all users for pay period
				$ppsulf = TTnew( 'PayPeriodScheduleUserListFactory' ); /** @var PayPeriodScheduleUserListFactory $ppsulf */
				if ( is_array( $user_ids ) && count( $user_ids ) > 0 && !in_array( TTUUID::getNotExistID(), $user_ids ) ) {
					Debug::text( 'Generating pay stubs for specific users...', __FILE__, __LINE__, __METHOD__, 10 );

					TTLog::addEntry( $this->getCurrentCompanyObject()->getId(), 500, TTi18n::gettext( 'Calculating Company Pay Stubs for Pay Period' ) . ': ' . TTDate::getDate( 'DATE', $pay_period_obj->getStartDate() ) . ' -> ' . TTDate::getDate( 'DATE', $pay_period_obj->getEndDate() ), $this->getCurrentUserObject()->getId(), 'pay_stub' ); //Notice
					$ppsulf->getByCompanyIDAndPayPeriodScheduleIdAndUserID( $this->getCurrentCompanyObject()->getId(), $pay_period_obj->getPayPeriodSchedule(), $user_ids );
				} else {
					Debug::text( 'Generating pay stubs for all users...', __FILE__, __LINE__, __METHOD__, 10 );
					TTLog::addEntry( $this->getCurrentCompanyObject()->getId(), 500, TTi18n::gettext( 'Calculating Employee Pay Stub for Pay Period' ) . ': ' . TTDate::getDate( 'DATE', $pay_period_obj->getStartDate() ) . ' -> ' . TTDate::getDate( 'DATE', $pay_period_obj->getEndDate() ), $this->getCurrentUserObject()->getId(), 'pay_stub' );
					$ppsulf->getByCompanyIDAndPayPeriodScheduleId( $this->getCurrentCompanyObject()->getId(), $pay_period_obj->getPayPeriodSchedule() );
				}
				$total_pay_stubs = $ppsulf->getRecordCount();

				if ( $total_pay_stubs > 0 ) {
					$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_pay_stubs, null, TTi18n::getText( 'Generating Paystubs...' ) );

					//FIXME: If a pay stub already exists, it is deleted first, but then if the new pay stub fails to generate, the original one is
					//  still deleted, so that can catch some people off guard if they don't fix the problem and re-generate the paystubs again.
					//  This can be useful in some cases though, as the opposite problem may arise.

					//Delete existing pay stub. Make sure we only
					//delete pay stubs that are the same as what we're creating.
					$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
					$pslf->getByCompanyIdAndPayPeriodIdAndRun( $this->getCurrentCompanyObject()->getId(), $pay_period_obj->getId(), $run_id );
					foreach ( $pslf as $pay_stub_obj ) {
						if ( is_array( $user_ids ) && count( $user_ids ) > 0 && !in_array( TTUUID::getNotExistID(), $user_ids ) && in_array( $pay_stub_obj->getUser(), $user_ids ) == false ) {
							continue; //Only generating pay stubs for individual employees, skip ones not in the list.
						}
						Debug::text( 'Existing Pay Stub: ' . $pay_stub_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

						//Check PS End Date to match with PP End Date
						//So if an ROE was generated, it won't get deleted when they generate all other Pay Stubs later on.
						//Unless the ROE used the exact same dates as the pay period? To avoid this, only delete pay stubs for employees with no termination date, or with a termination date after the pay period start date.
						if ( $pay_stub_obj->getStatus() <= 25
								&& $pay_stub_obj->getTainted() === false
								&& TTDate::getMiddleDayEpoch( $pay_stub_obj->getEndDate() ) == TTDate::getMiddleDayEpoch( $pay_period_obj->getEndDate() )
								&& ( is_object( $pay_stub_obj->getUserObject() ) && ( $pay_stub_obj->getUserObject()->getTerminationDate() == '' || TTDate::getMiddleDayEpoch( $pay_stub_obj->getUserObject()->getTerminationDate() ) >= TTDate::getMiddleDayEpoch( $pay_period_obj->getStartDate() ) ) ) ) {
							Debug::text( 'Deleting pay stub: ' . $pay_stub_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
							$pay_stub_obj->setDeleted( true );
							if ( $pay_stub_obj->isValid() == true ) { //Make sure we validate on delete, in case there are paid transactions.
								$pay_stub_obj->Save();
							} else {
								Debug::text( 'ERROR: Unable to delete old pay stub to regenerate it...', __FILE__, __LINE__, __METHOD__, 10 );
							}
						} else {
							Debug::text( 'Pay stub does not need regenerating, or it is LOCKED! ID: ' . $pay_stub_obj->getID() . ' Status: ' . $pay_stub_obj->getStatus() . ' Tainted: ' . (int)$pay_stub_obj->getTainted() . ' Pay Stub End Date: ' . $pay_stub_obj->getEndDate() . ' Pay Period End Date: ' . $pay_period_obj->getEndDate(), __FILE__, __LINE__, __METHOD__, 10 );
						}
					}

					$i = 1;
					foreach ( $ppsulf as $pay_period_schdule_user_obj ) {
						Debug::text( 'Pay Period User ID: ' . $pay_period_schdule_user_obj->getUser(), __FILE__, __LINE__, __METHOD__, 10 );
						Debug::text( 'Total Pay Stubs: ' . $total_pay_stubs . ' - ' . ceil( 1 / ( 100 / $total_pay_stubs ) ), __FILE__, __LINE__, __METHOD__, 10 );

						$profiler->startTimer( 'Calculating Pay Stub' );
						//Calc paystubs.
						$cps = new CalculatePayStub();
						$cps->setEnableCorrection( (bool)$enable_correction );
						$cps->setUser( $pay_period_schdule_user_obj->getUser() );
						$cps->setPayPeriod( $pay_period_obj->getId() );
						$cps->setType( $type_id );
						$cps->setRun( $run_id );
						if ( $transaction_date != '' ) {
							$cps->setTransactionDate( TTDate::parseDateTime( $transaction_date ) );
						}
						$cps->calculate();
						unset( $cps );
						$profiler->stopTimer( 'Calculating Pay Stub' );

						$this->getProgressBarObject()->set( $this->getAPIMessageID(), $i );

						//sleep(1); /////////////////////////////// FOR TESTING ONLY //////////////////

						$i++;
					}
					unset( $ppsulf );

					$this->getProgressBarObject()->stop( $this->getAPIMessageID() );
				} else {
					Debug::text( 'ERROR: User not assigned to pay period schedule...', __FILE__, __LINE__, __METHOD__, 10 );
					UserGenericStatusFactory::queueGenericStatus( TTi18n::gettext( 'ERROR' ), 10, TTi18n::gettext( 'Unable to generate pay stub(s), employee(s) may not be assigned to a pay period schedule.' ), null );
				}
			} else {
				UserGenericStatusFactory::queueGenericStatus( TTi18n::gettext( 'ERROR' ), 10, TTi18n::gettext( 'Pay period prior to %1 is not closed, please close all previous pay periods and try again...', [ TTDate::getDate( 'DATE', $pay_period_obj->getStartDate() ) . ' -> ' . TTDate::getDate( 'DATE', $pay_period_obj->getEndDate() ) ] ), null );
			}
		}

		if ( UserGenericStatusFactory::isStaticQueue() == true ) {
			$ugsf = TTnew( 'UserGenericStatusFactory' ); /** @var UserGenericStatusFactory $ugsf */
			$ugsf->setUser( $this->getCurrentUserObject()->getId() );
			$ugsf->setBatchID( $ugsf->getNextBatchId() );
			$ugsf->setQueue( UserGenericStatusFactory::getStaticQueue() );
			$ugsf->saveQueue();
			$user_generic_status_batch_id = $ugsf->getBatchID();
		} else {
			$user_generic_status_batch_id = false;
		}
		unset( $ugsf );

		return $this->returnHandler( true, true, false, false, false, $user_generic_status_batch_id );
	}

	/**
	 * @param string $pay_period_ids UUID
	 * @return int
	 */
	function getCurrentPayRun( $pay_period_ids ) {
		$retval = 1;
		if ( is_array( $pay_period_ids ) && count( $pay_period_ids ) > 0 ) {
			$retval = PayStubListFactory::getCurrentPayRun( $this->getCurrentCompanyObject()->getId(), $pay_period_ids );
		}

		Debug::Text( '  Current Run ID: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
