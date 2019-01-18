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
 * @package API\PayStub
 */

class APIPayStub extends APIFactory {
	protected $main_class = 'PayStubFactory';

	/**
	 * APIPayStub constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	/**
	 * overridden to get different columns based on permissions.
	 * @param bool $name
	 * @param null $parent
	 * @return bool|object
	 */
	function getOptions( $name = FALSE, $parent = NULL ) {
		if ( $name == 'columns'
				AND ( !$this->getPermissionObject()->Check('pay_stub', 'enabled')
						OR !( $this->getPermissionObject()->Check('pay_stub', 'view') OR $this->getPermissionObject()->Check('pay_stub', 'view_child') ) ) ) {
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

		Debug::Text('Getting pay stub entry default data...', __FILE__, __LINE__, __METHOD__, 10);

		//Get earliest OPEN pay period.
		$pplf = TTNew('PayPeriodListFactory');
		$pplf->getLastPayPeriodByCompanyIdAndPayPeriodScheduleIdAndDate( $company_obj->getId(), NULL, time() );
		if ( $pplf->getRecordCount() > 0 ) {
			$pp_obj = $pplf->getCurrent();

			$pay_period_id = $pp_obj->getId();
			$start_date = TTDate::getDate('DATE', $pp_obj->getStartDate() );
			$end_date = TTDate::getDate('DATE', $pp_obj->getEndDate() );
			$transaction_date = TTDate::getDate('DATE', $pp_obj->getTransactionDate() );
		} else {
			$pay_period_id = TTUUID::getZeroID();
			$start_date = TTDate::getDate('DATE', time() );
			$end_date = TTDate::getDate('DATE', time() );
			$transaction_date = TTDate::getDate('DATE', time() );
		}

		$run_id = $this->stripReturnHandler( $this->getCurrentPayRun( $pay_period_id ) );

		$data = array(
			'company_id' => $company_obj->getId(),
			'user_id' => $user_obj->getId(),
			'currency_id' => $user_obj->getCurrency(),
			'pay_period_id' => $pay_period_id,
			'run_id' => $run_id,
			'start_date' => $start_date,
			'end_date' => $end_date,
			'transaction_date' => $transaction_date,
		);

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
	function getPayStub( $data = NULL, $disable_paging = FALSE, $format = FALSE, $hide_employer_rows = TRUE ) {
		if ( !$this->getPermissionObject()->Check( 'pay_stub', 'enabled' )
				OR !( $this->getPermissionObject()->Check( 'pay_stub', 'view' ) OR $this->getPermissionObject()->Check( 'pay_stub', 'view_own' ) OR $this->getPermissionObject()->Check( 'pay_stub', 'view_child' ) )
		) {
			return $this->getPermissionObject()->PermissionDenied();
		}
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		$format = Misc::trimSortPrefix( $format );
		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'pay_stub', 'view' );

		if ( $this->getPermissionObject()->Check( 'pay_stub', 'view' ) == FALSE AND $this->getPermissionObject()->Check( 'pay_stub', 'view_child' ) == FALSE ) {
			//Only display PAID pay stubs.
			$data['filter_data']['status_id'] = array(40);
		}

		//Always hide employer rows unless they have permissions to view all pay stubs.
		if ( $this->getPermissionObject()->Check( 'pay_stub', 'view' ) == FALSE ) {
			$hide_employer_rows = TRUE;
		}

		if ( ($format == 10 OR $format == 20 OR $format == 30) AND $this->getPermissionObject()->Check( 'pay_stub', 'view' ) == TRUE ) {
			$data['filter_data']['transaction_status_id'] = array(10, 200); //10=Pending, 200=ReIssue
			$data['filter_data']['transaction_type_id'] = 10; //10=Valid (Enabled)
			if ( isset($data['filter_data']['id']) ) {
				$data['filter_data']['pay_stub_id'] = $data['filter_data']['id'];
			}
			unset($data['filter_data']['id']);

			if ( $format == 20 ) {
				$data['filter_data']['remittance_destination_account_type_id'] = array(3000);
			}

			if ( $format == 30 ) {
				$data['filter_data']['remittance_destination_account_type_id'] = array(2000);
			}

			//Specific sort order to ensure consistent transaction order in the EFT files. Keep in mind exportPayStubTransaction() sorts the transactions again too, but this helps.
			$data['filter_sort'] = array( 'lef.id' => 'asc', 'rsaf.id' => 'asc', 'psf.transaction_date' => 'asc', 'destination_user_last_name' => 'asc', 'destination_first_last_name' => 'asc', 'rdaf.id' => 'asc' );

			/** @var PayStubTransactionListFactory $pslf */
			$pslf = TTnew( 'PayStubTransactionListFactory' );
			$pslf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
			Debug::Text( 'PSTLF Record Count: ' . $pslf->getRecordCount() . ' Format: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );
		} else {
			/** @var PayStubListFactory $pslf */
			$pslf = TTnew( 'PayStubListFactory' );
			$pslf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
			Debug::Text( 'PSLF Record Count: ' . $pslf->getRecordCount() . ' Format: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );
		}

		if ( $format == 'pdf' ) {
			if ( $pslf->getRecordCount() > 0 ) {
				$this->getProgressBarObject()->setDefaultKey( $this->getAMFMessageID() );
				$this->getProgressBarObject()->start( $this->getAMFMessageID(), $pslf->getRecordCount() );
				$pslf->setProgressBarObject( $this->getProgressBarObject() ); //Expose progress bar object to pay stub object.

				$output = $pslf->getPayStub( $pslf, (bool)$hide_employer_rows );

				$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

				if ( $output != '' ) {
					return Misc::APIFileDownload( 'pay_stub.pdf', 'application/pdf', $output );
				} else {
					return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('ERROR: No data to export...') );
				}
			}
		} elseif ( ($format == 10 OR $format == 20 OR $format == 30) AND $this->getPermissionObject()->Check('pay_stub', 'view') == TRUE ) {
			if ( $pslf->getRecordCount() > 0 ) {
				$this->getProgressBarObject()->setDefaultKey( $this->getAMFMessageID() );
				$this->getProgressBarObject()->start( $this->getAMFMessageID(), $pslf->getRecordCount() );
				$pslf->setProgressBarObject( $this->getProgressBarObject() ); //Expose progress bar object to pay stub object.

				$output = $pslf->exportPayStubTransaction( $pslf, $format);

				$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

				if ( is_array($output) AND count($output) > 0 ) {
					$filename = FALSE;
					if ( $format == 10 ) {
						$filename = 'pay_stub_transactions_'.TTDate::getDate( 'DATE', time() ).'.zip';
					}
					$zip_file = Misc::zip($output, $filename, TRUE);
					return Misc::APIFileDownload($zip_file['file_name'], $zip_file['mime_type'], $zip_file['data'] );
				} else {
					return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('ERROR: No data to export...') );
				}
			} else {
				return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('All transactions have already been processed') );
			}
		} else {
			if ( $pslf->getRecordCount() > 0 ) {
				$this->getProgressBarObject()->start( $this->getAMFMessageID(), $pslf->getRecordCount() );

				$this->setPagerObject( $pslf );

				$retarr = array();
				foreach( $pslf as $ps_obj ) {
					$retarr[] = $ps_obj->getObjectAsArray( $data['filter_columns'], $data['filter_data']['permission_children_ids'] );

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $pslf->getCurrentRow() );
				}

				$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

				return $this->returnHandler( $retarr );
			}

			return $this->returnHandler( TRUE ); //No records returned.
		}
	}

	/**
	 * Export data to csv
	 * @param string $format file format (csv)
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array
	 */
	function exportPayStub( $format = 'csv', $data = NULL, $disable_paging = TRUE) {
		$result = $this->stripReturnHandler( $this->getPayStub( $data, $disable_paging ) );
		return $this->exportRecords( $format, 'export_pay_stub', $result, ( ( isset($data['filter_columns']) ) ? $data['filter_columns'] : NULL ) );
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonPayStubData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getPayStub( $data, TRUE ) ) );
	}

	/**
	 * Validate pay_stub data for one or more pay_stubes.
	 * @param array $data pay_stub data
	 * @return array
	 */
	function validatePayStub( $data ) {
		return $this->setPayStub( $data, TRUE );
	}

	/**
	 * Set pay_stub data for one or more pay_stubes.
	 * @param array $data pay_stub data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setPayStub( $data, $validate_only = FALSE, $ignore_warning = TRUE ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('pay_stub', 'enabled')
				OR !( $this->getPermissionObject()->Check('pay_stub', 'edit') OR $this->getPermissionObject()->Check('pay_stub', 'edit_own') OR $this->getPermissionObject()->Check('pay_stub', 'edit_child') OR $this->getPermissionObject()->Check('pay_stub', 'add') ) ) {
			return	$this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == TRUE ) {
			Debug::Text('Validating Only!', __FILE__, __LINE__, __METHOD__, 10);
		}

		list( $data, $total_records ) = $this->convertToMultipleRecords( $data );
		Debug::Text('Received data for: '. $total_records .' PayStubs', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		$validator = $save_result = $key = FALSE;
		if ( is_array($data) AND $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach( $data as $key => $row ) {
				$primary_validator = new Validator();
				/** @var PayStubListFactory $lf */
				$lf = TTnew( 'PayStubListFactory' );
				$lf->StartTransaction();
				if ( isset($row['id']) AND $row['id'] != '' ) {
					//Modifying existing object.
					//Get pay_stub object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
							$validate_only == TRUE
							OR
								(
								$this->getPermissionObject()->Check('pay_stub', 'edit')
									OR ( $this->getPermissionObject()->Check('pay_stub', 'edit_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getUser() ) === TRUE )
								) ) {

							Debug::Text('Row Exists, getting current data for ID: '. $row['id'], __FILE__, __LINE__, __METHOD__, 10);
							$lf = $lf->getCurrent();

							//Check to see if the transaction date changed, so we can trigger setEnableCalcYTD().
							$lf_arr = $lf->getObjectAsArray();
							if ( isset($lf_arr['transaction_date']) AND isset($row['transaction_date'])
									AND $lf_arr['transaction_date'] != $row['transaction_date']
									AND TTDate::getYear( TTDate::parseDateTime( $lf_arr['transaction_date'] ) ) != TTDate::getYear( TTDate::parseDateTime( $row['transaction_date'] ) ) ) {
								Debug::Text( 'Transaction date changed to a different year, recalculate YTD amounts... Prev: '.$lf_arr['transaction_date'] .' New: '. $row['transaction_date'], __FILE__, __LINE__, __METHOD__, 10);
								$set_enable_calc_ytd = TRUE;
							}
							$row = array_merge( $lf_arr, $row );
							unset($lf_arr);
						} else {
							$primary_validator->isTrue( 'permission', FALSE, TTi18n::gettext('Edit permission denied') );
						}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Edit permission denied, record does not exist') );
					}
				} else {
					//Adding new object, check ADD permissions.
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check('pay_stub', 'add'), TTi18n::gettext('Add permission denied') );

					//Because this class has sub-classes that depend on it, when adding a new record we need to make sure the ID is set first,
					//so the sub-classes can depend on it. We also need to call Save( TRUE, TRUE ) to force a lookup on isNew()
					$row['id'] = $lf->getNextInsertId();
				}
				Debug::Arr($row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid( $ignore_warning );
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Setting object data...', __FILE__, __LINE__, __METHOD__, 10 );

					$lf->setObjectFromArray( $row );

					//If the user is changing the Transaction Date between years, make sure we always recalc the current pay stub YTD amount.
					// ie: Changing it from Dec 31st to January 1st, or vice versa makes the YTD amount reset.
					// This must go above processEntries() so it can be disabled by it if needed.
					if ( isset($set_enable_calc_ytd) AND $set_enable_calc_ytd == TRUE ) {
						$lf->setEnableCalcCurrentYTD( TRUE );
						$lf->setEnableCalcYTD( TRUE );
					}

					if ( ( isset( $row['entries'] ) AND is_array( $row['entries'] ) AND count( $row['entries'] ) > 0 ) ) {
						Debug::Text( ' Found modified entries!', __FILE__, __LINE__, __METHOD__, 10 );

						//Load previous pay stub
						$lf->loadPreviousPayStub();

						//Delete all entries, so they can be re-added.
						//$lf->deleteEntries( TRUE );

						//When editing pay stubs we can't re-process linked accruals.
						$lf->setEnableLinkedAccruals( FALSE );

						$processed_entries = 0;
						foreach ( $row['entries'] as $pay_stub_entry ) {
							if ( 	(
											( isset( $pay_stub_entry['id'] ) AND TTUUID::isUUID( $pay_stub_entry['id'] ) AND $pay_stub_entry['id'] != TTUUID::getZeroID() AND $pay_stub_entry['id'] != TTUUID::getNotExistID() )
										OR
											( isset( $pay_stub_entry['pay_stub_entry_name_id'] )
													AND TTUUID::isUUID( $pay_stub_entry['pay_stub_entry_name_id'] ) AND $pay_stub_entry['pay_stub_entry_name_id'] != TTUUID::getZeroID() AND $pay_stub_entry['pay_stub_entry_name_id'] != TTUUID::getNotExistID()
											)
									)
									AND
									(
											!isset( $pay_stub_entry['type'] )
										OR
											( isset( $pay_stub_entry['type'] ) AND $pay_stub_entry['type'] != 40 )
									)
									AND isset( $pay_stub_entry['amount'] )
								) {
								Debug::Text( 'Pay Stub Entry ID: ' . $pay_stub_entry['id'] . ' Amount: ' . $pay_stub_entry['amount'] . ' Pay Stub ID: ' . $row['id'], __FILE__, __LINE__, __METHOD__, 10 );

								//Populate $pay_stub_entry_obj so we can find validation errors before postSave() is called.
								if ( isset($pay_stub_entry['id']) AND $pay_stub_entry['id'] != '' AND TTUUID::isUUID( $pay_stub_entry['id'] ) ) {
									$pself = TTnew( 'PayStubEntryListFactory' );
									$pself->getById( $pay_stub_entry['id'] );
									if ( $pself->getRecordCount() > 0 ) {
										$pay_stub_entry_obj = $pself->getCurrent();
									} else {
										$pay_stub_entry_obj = TTnew( 'PayStubEntryListFactory' );
									}
								} else {
									$pay_stub_entry_obj = TTnew( 'PayStubEntryListFactory' );
									//$pay_stub_entry_obj->setPayStub( $lf->getId() ); //Don't set this here as it will cause validation failures. Its handled in addEntry instead.
								}

								if ( isset($pay_stub_entry['pay_stub_entry_name_id']) AND $pay_stub_entry['pay_stub_entry_name_id'] != '' ) {
									$pay_stub_entry_obj->setPayStubEntryNameId( $pay_stub_entry['pay_stub_entry_name_id'] );
								}

								if ( isset( $pay_stub_entry['pay_stub_amendment_id'] ) AND $pay_stub_entry['pay_stub_amendment_id'] != '' ) {
									$pay_stub_entry_obj->setPayStubAmendment( $pay_stub_entry['pay_stub_amendment_id'], $lf->getStartDate(), $lf->getEndDate() );
								}

								if ( isset( $pay_stub_entry['rate'] ) AND $pay_stub_entry['rate'] != '' ) {
									$pay_stub_entry_obj->setRate( $pay_stub_entry['rate'] );
								}
								if ( isset( $pay_stub_entry['units'] ) AND $pay_stub_entry['units'] != '' ) {
									$pay_stub_entry_obj->setUnits( $pay_stub_entry['units'] );
								}

								if ( isset( $pay_stub_entry['amount'] ) AND $pay_stub_entry['amount'] != '' ) {
									$pay_stub_entry_obj->setAmount( $pay_stub_entry['amount'] );
								}

								if ( !isset( $pay_stub_entry['units'] ) OR $pay_stub_entry['units'] == '' ) {
									$pay_stub_entry['units'] = 0;
								}
								if ( !isset( $pay_stub_entry['rate'] ) OR $pay_stub_entry['rate'] == '' ) {
									$pay_stub_entry['rate'] = 0;
								}
								if ( !isset( $pay_stub_entry['description'] ) OR $pay_stub_entry['description'] == '' ) {
									$pay_stub_entry['description'] = NULL;
								}
								if ( !isset( $pay_stub_entry['pay_stub_amendment_id'] ) OR $pay_stub_entry['pay_stub_amendment_id'] == '' ) {
									$pay_stub_entry['pay_stub_amendment_id'] = NULL;
								}
								if ( !isset( $pay_stub_entry['user_expense_id'] ) OR $pay_stub_entry['user_expense_id'] == '' ) {
									$pay_stub_entry['user_expense_id'] = NULL;
								}

								$ytd_adjustment = FALSE;
								if ( TTUUID::isUUID( $pay_stub_entry['pay_stub_amendment_id'] ) AND $pay_stub_entry['pay_stub_amendment_id'] != TTUUID::getZeroID() AND $pay_stub_entry['pay_stub_amendment_id'] != TTUUID::getNotExistID() ) {
									$psamlf = TTNew( 'PayStubAmendmentListFactory' );
									$psamlf->getByIdAndCompanyId( TTUUID::castUUID($pay_stub_entry['pay_stub_amendment_id']), $this->getCurrentCompanyObject()->getId() );
									if ( $psamlf->getRecordCount() > 0 ) {
										$ytd_adjustment = $psamlf->getCurrent()->getYTDAdjustment();
									}
									Debug::Text( ' Pay Stub Amendment Id: ' . $pay_stub_entry['pay_stub_amendment_id'] . ' YTD Adjusment: ' . (int)$ytd_adjustment, __FILE__, __LINE__, __METHOD__, 10 );
								}

								if ( $pay_stub_entry_obj->isValid() == TRUE ) {
									$lf->addEntry( $pay_stub_entry['pay_stub_entry_name_id'], $pay_stub_entry['amount'], $pay_stub_entry['units'], $pay_stub_entry['rate'], $pay_stub_entry['description'], $pay_stub_entry['pay_stub_amendment_id'], NULL, NULL, $ytd_adjustment, $pay_stub_entry['user_expense_id'] );
									$processed_entries++;
								} else {
									Debug::Text( ' ERROR: Unable to save PayStubEntry... ', __FILE__, __LINE__, __METHOD__, 10 );
									$tmp_pay_stub_entry_account_name = TTi18n::getText('N/A');
									if ( is_object( $pay_stub_entry_obj->getPayStubEntryAccountObject() ) ) {
										$tmp_pay_stub_entry_account_name = $pay_stub_entry_obj->getPayStubEntryAccountObject()->getName();
									}

									$lf->Validator->isTrue( 'pay_stub_entry', FALSE, TTi18n::getText( '%1 entry for amount: %2 is invalid', array($tmp_pay_stub_entry_account_name, Misc::MoneyFormat( $pay_stub_entry['amount'] )) ) );
								}
							} else {
								Debug::Text( ' Skipping Total Entry. ', __FILE__, __LINE__, __METHOD__, 10 );
							}
							unset( $pay_stub_entry_obj );
						}
						unset( $pay_stub_entry );

						if ( $processed_entries > 0 ) {
							$lf->setTainted( TRUE ); //Make sure tainted flag is set when any entries are processed.
							$lf->setEnableCalcYTD( TRUE );
							$lf->setEnableProcessEntries( TRUE );
							$lf->processEntries();
						}
					} else {
						Debug::Text( ' Skipping ALL Entries... ', __FILE__, __LINE__, __METHOD__, 10 );
					}

					if ( ( isset($row['transactions']) AND is_array($row['transactions']) AND count($row['transactions']) > 0 ) ) {
						Debug::Text( ' Found modified transactions!', __FILE__, __LINE__, __METHOD__, 10 );
						$processed_transactions = 0;
						if ( count($row['transactions']) > 0 ) {
							foreach ( $row['transactions'] as $pay_stub_transaction ) {
								//Debug::Arr($pay_stub_transaction,'Paystub transaction row...', __FILE__, __LINE__, __METHOD__, 10);
								if ( $pay_stub_transaction['amount'] == 0 ) { //Skip any transactions of $0.00
									continue;
								}

								if ( isset( $pay_stub_transaction['id'] )
										AND TTUUID::isUUID( $pay_stub_transaction['id'] ) AND $pay_stub_transaction['id'] != TTUUID::getZeroID() AND $pay_stub_transaction['id'] != TTUUID::getNotExistID() ) {
									/** @var PayStubTransactionListFactory $pstlf */
									$pstlf = TTnew( 'PayStubTransactionListFactory' );
									$pstlf->getByIdAndCompanyId( $pay_stub_transaction['id'], $this->getCurrentCompanyObject()->getId() );
									if ( $pstlf->getRecordCount() > 0 ) {
										$pst_obj = $pstlf->getCurrent();
									}
									unset( $pstlf );
								} else {
									/** @var PayStubTransactionFactory $pst_obj */
									$pst_obj = TTnew('PayStubTransactionFactory');
									//$pst_obj->setPayStub( $lf->getId() ); //Don't set this here as it will cause validation failures. Its handled in addTransaction() instead.
								}

								$pst_obj->setType( 10 ); //Enabled

								if ( isset($pay_stub_transaction['status_id']) AND $pay_stub_transaction['status_id'] != '' ) {
									$pst_obj->setStatus( $pay_stub_transaction['status_id'] );
								} else {
									$pst_obj->setStatus( 10 ); //Pending
								}

								if ( isset( $pay_stub_transaction['deleted']) AND $pay_stub_transaction['deleted'] == 1 ) {
									// Deleted is set instead of populating the object to provide for the case where a
									// user enters invalid data then deletes the row, removing it from the UI
									$pst_obj->setDeleted(TRUE);
								} else{
									if ( isset( $pay_stub_transaction['remittance_destination_account_id'] ) ) {
										$pst_obj->setRemittanceDestinationAccount($pay_stub_transaction['remittance_destination_account_id']);
									}

									if ( isset( $pay_stub_transaction['transaction_date'] ) ) {
										$pst_obj->setTransactionDate( TTDate::parseDateTime($pay_stub_transaction['transaction_date']) );
									}

									if ( isset( $pay_stub_transaction['amount'] ) ) {
										$pst_obj->setAmount($pay_stub_transaction['amount']);
									}

									if ( isset( $pay_stub_transaction['note'] ) ) {
										$pst_obj->setNote($pay_stub_transaction['note']);
									}
								}

								if ( $pst_obj->isValid() ) {
									$lf->addTransaction( $pst_obj );
									$processed_transactions++;
								} else {
									if ( isset( $pay_stub_transaction['deleted']) == FALSE OR $pay_stub_transaction['deleted'] == 0 ) {
										$tmp_remittance_destination_account_name = TTi18n::getText('N/A');
										if ( is_object( $pst_obj->getRemittanceDestinationAccountObject() ) ) {
											$tmp_remittance_destination_account_name = $pst_obj->getRemittanceDestinationAccountObject()->getName();
										}
										$lf->Validator->isTrue( 'pay_stub_transaction', FALSE, TTi18n::getText( '%1 transaction for amount: %2 is invalid', array($tmp_remittance_destination_account_name, Misc::MoneyFormat( $pst_obj->getAmount() )) ) );
										unset($tmp_remittance_destination_account_name);
									}
								}
								unset($pst_obj);
							}

						}

						if ( $processed_transactions > 0 ) {
							$lf->setTainted( TRUE ); //Make sure tainted flag is set when any entries are processed.
							$lf->setEnableProcessTransactions( TRUE );
							//$lf->processTransactions();
						}
					} else {
						Debug::Text(' Skipping ALL transactions... ', __FILE__, __LINE__, __METHOD__, 10);
					}

					$lf->Validator->setValidateOnly( $validate_only );

					$is_valid = $lf->isValid( $ignore_warning );
					if ( $is_valid == TRUE ) {
						Debug::Text('Saving data...', __FILE__, __LINE__, __METHOD__, 10);
						if ( $validate_only == TRUE ) {
							$save_result[$key] = TRUE;
						} else {
							$save_result[$key] = $lf->Save( TRUE, TRUE );
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
	 * Delete one or more pay_stubs.
	 * @param array $data pay_stub data
	 * @return array|bool
	 */
	function deletePayStub( $data ) {
		if ( !is_array($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('pay_stub', 'enabled')
				OR !( $this->getPermissionObject()->Check('pay_stub', 'delete') OR $this->getPermissionObject()->Check('pay_stub', 'delete_own') OR $this->getPermissionObject()->Check('pay_stub', 'delete_child') ) ) {
			return	$this->getPermissionObject()->PermissionDenied();
		}

		Debug::Text('Received data for: '. count($data) .' PayStubs', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$total_records = count($data);
		$validator = $save_result = $key = FALSE;
		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) AND $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'PayStubListFactory' );
				$lf->StartTransaction();
				if ( $id != '' ) {
					//Modifying existing object.
					//Get pay_stub object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check('pay_stub', 'delete')
								OR ( $this->getPermissionObject()->Check('pay_stub', 'delete_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getUser() ) === TRUE ) ) {
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
	 * @param string $pay_period_ids UUID
	 * @param string $user_ids UUID
	 * @param bool $enable_correction
	 * @param bool $run_id
	 * @param int $type_id
	 * @param int $transaction_date EPOCH
	 * @return array|bool
	 */
	function generatePayStubs( $pay_period_ids, $user_ids = NULL, $enable_correction = FALSE, $run_id = FALSE, $type_id = 10, $transaction_date = NULL ) {
		global $profiler;
		Debug::Text('Generate Pay Stubs!', __FILE__, __LINE__, __METHOD__, 10);

		if ( !( $this->getPermissionObject()->Check('pay_period_schedule', 'enabled')
				AND ( $this->getPermissionObject()->Check('pay_period_schedule', 'edit') OR $this->getPermissionObject()->Check('pay_period_schedule', 'edit_own') )
				AND ( $this->getPermissionObject()->Check('pay_stub', 'view') OR $this->getPermissionObject()->Check('pay_stub', 'view_child') ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( !is_array($pay_period_ids) ) {
			$pay_period_ids = array($pay_period_ids);
		}
		$pay_period_ids = array_unique( $pay_period_ids );


		if ( $user_ids !== NULL AND !is_array($user_ids) AND $user_ids != '' ) {
			$user_ids = array($user_ids);
		} elseif ( is_array($user_ids) AND isset($user_ids[0]) AND $user_ids[0] == TTUUID::getZeroID() ) {
			$user_ids = NULL;
		}

		if ( is_array($user_ids) ) {
			$user_ids = array_unique( $user_ids );
		}

		if ( $type_id == 5 ) { //Post-Adjustment Carry-Forward, enable correction and force type to Normal.
			$enable_correction = TRUE;
			$type_id = 10;
		}

		foreach($pay_period_ids as $pay_period_id) {
			Debug::text('Pay Period ID: '. $pay_period_id, __FILE__, __LINE__, __METHOD__, 10);

			$epoch = TTDate::getTime();

			$pplf = TTnew( 'PayPeriodListFactory' );
			$pplf->getByIdAndCompanyId($pay_period_id, $this->getCurrentCompanyObject()->getId() );
			foreach ($pplf as $pay_period_obj) {
				Debug::text('Pay Period Schedule ID: '. $pay_period_obj->getPayPeriodSchedule(), __FILE__, __LINE__, __METHOD__, 10);
				if ( $pay_period_obj->isPreviousPayPeriodClosed() == TRUE ) {
					$pslf = TTnew( 'PayStubListFactory' );

					if ( (int)$run_id == 0 ) {
						$run_id = PayStubListFactory::getCurrentPayRun( $this->getCurrentCompanyObject()->getId(), $pay_period_obj->getId() );
					}
					Debug::text('  Using Run ID: '. $run_id, __FILE__, __LINE__, __METHOD__, 10);

					//Check to make sure pay stubs with a transaction date before today are not open, as that can cause the payroll run number to be incorrectly determined on its own.
					$open_pay_stub_transaction_date = ( TTDate::getMiddleDayEpoch( $epoch ) >= TTDate::getMiddleDayEpoch( $pay_period_obj->getTransactionDate() ) ) ? $pay_period_obj->getTransactionDate() : TTDate::getBeginDayEpoch( $epoch );
					$pslf->getByCompanyIdAndPayPeriodIdAndStatusIdAndTransactionDateBeforeDate( $this->getCurrentCompanyObject()->getId(), $pay_period_id, array(25), $open_pay_stub_transaction_date, 1 );
					if ( $pslf->getRecordCount() > 0 ) {
						UserGenericStatusFactory::queueGenericStatus( TTi18n::gettext('ERROR'), 10, TTi18n::gettext('Pay Stubs with a transaction date before today are still OPEN, all pay stubs must be PAID on or before their transaction date'), NULL );
						continue;
					}
					unset($open_pay_stub_transaction_date);

					if ( $run_id > 1 ) { //Check to make sure prior payroll runs are marked as PAID.
						$pslf->getByCompanyIdAndPayPeriodIdAndStatusIdAndNotRun( $this->getCurrentCompanyObject()->getId(), $pay_period_obj->getId(), array(10, 20, 25, 30), $run_id, 1 ); //Only need to return 1 record.
						if ( $pslf->getRecordCount() > 0 ) {
							$tmp_pay_stub_obj = $pslf->getCurrent();
							Debug::text('Pay Stub ID: '. $tmp_pay_stub_obj->getID() .' Run: '. $tmp_pay_stub_obj->getRun() .' Transaction Date: '. TTDate::getDate('DATE', $tmp_pay_stub_obj->getTransactionDate() ), __FILE__, __LINE__, __METHOD__, 10);
							UserGenericStatusFactory::queueGenericStatus( TTi18n::gettext('ERROR'), 10, TTi18n::gettext('Payroll Run #%1 of Pay Period %2 is still OPEN, all pay stubs must be PAID before starting a new payroll run.', array( $tmp_pay_stub_obj->getRun(), TTDate::getDate('DATE', $pay_period_obj->getStartDate() ).' -> '. TTDate::getDate('DATE', $pay_period_obj->getEndDate() ) ) ), NULL );
							unset($tmp_pay_stub_obj);
							continue;
						}
					}
					unset($pslf);

					//Grab all users for pay period
					$ppsulf = TTnew( 'PayPeriodScheduleUserListFactory' );
					if ( is_array($user_ids) AND count($user_ids) > 0 AND !in_array( TTUUID::getNotExistID(), $user_ids ) ) {
						Debug::text('Generating pay stubs for specific users...', __FILE__, __LINE__, __METHOD__, 10);
						TTLog::addEntry( $this->getCurrentCompanyObject()->getId(), 500, TTi18n::gettext('Calculating Company Pay Stubs for Pay Period').': '. $pay_period_id, $this->getCurrentUserObject()->getId(), 'pay_stub' ); //Notice
						$ppsulf->getByCompanyIDAndPayPeriodScheduleIdAndUserID( $this->getCurrentCompanyObject()->getId(), $pay_period_obj->getPayPeriodSchedule(), $user_ids );
					} else {
						Debug::text('Generating pay stubs for all users...', __FILE__, __LINE__, __METHOD__, 10);
						TTLog::addEntry( $this->getCurrentCompanyObject()->getId(), 500, TTi18n::gettext('Calculating Employee Pay Stub for Pay Period').': '. $pay_period_id, $this->getCurrentUserObject()->getId(), 'pay_stub' );
						$ppsulf->getByCompanyIDAndPayPeriodScheduleId( $this->getCurrentCompanyObject()->getId(), $pay_period_obj->getPayPeriodSchedule() );
					}
					$total_pay_stubs = $ppsulf->getRecordCount();

					if ( $total_pay_stubs > 0 ) {
						$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_pay_stubs, NULL, TTi18n::getText('Generating Paystubs...') );

						//FIXME: If a pay stub already exists, it is deleted first, but then if the new pay stub fails to generate, the original one is
						//  still deleted, so that can cause some people off guard if they don't fix the problem and re-generate the paystubs again.
						//  This can be useful in some cases though, as the opposite problem may arise.

						//Delete existing pay stub. Make sure we only
						//delete pay stubs that are the same as what we're creating.
						$pslf = TTnew( 'PayStubListFactory' );
						$pslf->getByCompanyIdAndPayPeriodIdAndRun( $this->getCurrentCompanyObject()->getId(), $pay_period_obj->getId(), $run_id );
						foreach ( $pslf as $pay_stub_obj ) {
							if ( is_array($user_ids) AND count($user_ids) > 0 AND !in_array( TTUUID::getNotExistID(), $user_ids ) AND in_array( $pay_stub_obj->getUser(), $user_ids ) == FALSE ) {
								continue; //Only generating pay stubs for individual employees, skip ones not in the list.
							}
							Debug::text('Existing Pay Stub: '. $pay_stub_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);

							//Check PS End Date to match with PP End Date
							//So if an ROE was generated, it won't get deleted when they generate all other Pay Stubs later on.
							//Unless the ROE used the exact same dates as the pay period? To avoid this, only delete pay stubs for employees with no termination date, or with a termination date after the pay period start date.
							if ( $pay_stub_obj->getStatus() <= 25
									AND $pay_stub_obj->getTainted() === FALSE
									AND TTDate::getMiddleDayEpoch( $pay_stub_obj->getEndDate() ) == TTDate::getMiddleDayEpoch( $pay_period_obj->getEndDate() )
									AND ( is_object( $pay_stub_obj->getUserObject() ) AND ( $pay_stub_obj->getUserObject()->getTerminationDate() == '' OR TTDate::getMiddleDayEpoch( $pay_stub_obj->getUserObject()->getTerminationDate() ) >= TTDate::getMiddleDayEpoch( $pay_period_obj->getStartDate() ) ) ) ) {
								Debug::text('Deleting pay stub: '. $pay_stub_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);
								$pay_stub_obj->setDeleted( TRUE );
								if ( $pay_stub_obj->isValid() == TRUE ) { //Make sure we validate on delete, in case there are paid transactions.
									$pay_stub_obj->Save();
								} else {
									Debug::text('ERROR: Unable to delete old pay stub to regenerate it...', __FILE__, __LINE__, __METHOD__, 10);
								}
							} else {
								Debug::text('Pay stub does not need regenerating, or it is LOCKED! ID: '. $pay_stub_obj->getID() .' Status: '. $pay_stub_obj->getStatus() .' Tainted: '. (int)$pay_stub_obj->getTainted() .' Pay Stub End Date: '. $pay_stub_obj->getEndDate() .' Pay Period End Date: '. $pay_period_obj->getEndDate(), __FILE__, __LINE__, __METHOD__, 10);
							}
						}

						$i = 1;
						foreach ($ppsulf as $pay_period_schdule_user_obj) {
							Debug::text('Pay Period User ID: '. $pay_period_schdule_user_obj->getUser(), __FILE__, __LINE__, __METHOD__, 10);
							Debug::text('Total Pay Stubs: '. $total_pay_stubs .' - '. ceil( 1 / (100 / $total_pay_stubs) ), __FILE__, __LINE__, __METHOD__, 10);

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
							unset($cps);
							$profiler->stopTimer( 'Calculating Pay Stub' );

							$this->getProgressBarObject()->set( $this->getAMFMessageID(), $i );

							//sleep(1); /////////////////////////////// FOR TESTING ONLY //////////////////

							$i++;
						}
						unset($ppsulf);

						$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

					} else {
						Debug::text('ERROR: User not assigned to pay period schedule...', __FILE__, __LINE__, __METHOD__, 10);
						UserGenericStatusFactory::queueGenericStatus( TTi18n::gettext('ERROR'), 10, TTi18n::gettext('Unable to generate pay stub(s), employee(s) may not be assigned to a pay period schedule.' ), NULL );
					}
				} else {
					UserGenericStatusFactory::queueGenericStatus( TTi18n::gettext('ERROR'), 10, TTi18n::gettext('Pay period prior to %1 is not closed, please close all previous pay periods and try again...', array( TTDate::getDate('DATE', $pay_period_obj->getStartDate() ).' -> '. TTDate::getDate('DATE', $pay_period_obj->getEndDate() ) ) ), NULL );
				}
			}
		}

		if ( UserGenericStatusFactory::isStaticQueue() == TRUE ) {
			$ugsf = TTnew( 'UserGenericStatusFactory' );
			$ugsf->setUser( $this->getCurrentUserObject()->getId() );
			$ugsf->setBatchID( $ugsf->getNextBatchId() );
			$ugsf->setQueue( UserGenericStatusFactory::getStaticQueue() );
			$ugsf->saveQueue();
			$user_generic_status_batch_id = $ugsf->getBatchID();
		} else {
			$user_generic_status_batch_id = FALSE;
		}
		unset($ugsf);

		return $this->returnHandler( TRUE, TRUE, FALSE, FALSE, FALSE, $user_generic_status_batch_id );
	}

	/**
	 * @param string $pay_period_ids UUID
	 * @return int
	 */
	function getCurrentPayRun( $pay_period_ids ) {
		$retval = 1;
		if ( is_array($pay_period_ids) AND count($pay_period_ids) > 0 ) {
			$retval = PayStubListFactory::getCurrentPayRun( $this->getCurrentCompanyObject()->getId(), $pay_period_ids );
		}

		Debug::Text('  Current Run ID: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}
}
?>
