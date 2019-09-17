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
class APIPayStubTransaction extends APIFactory {
	protected $main_class = 'PayStubTransactionFactory';

	/**
	 * APIPayStubTransaction constructor.
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
			AND ( !$this->getPermissionObject()->Check('pay_stub', 'enabled')
				OR !( $this->getPermissionObject()->Check('pay_stub', 'view') OR $this->getPermissionObject()->Check('pay_stub', 'view_own') OR $this->getPermissionObject()->Check('pay_stub', 'view_child') ) ) ) {
			$name = 'list_columns';
		}

		return parent::getOptions( $name, $parent );
	}

	/**
	 * Get default pay stub transaction data for creating new pay stub transactions.
	 * @return array
	 */
	function getPayStubTransactionDefaultData() {
		$company_obj = $this->getCurrentCompanyObject();

		Debug::Text('Getting pay stub transaction default data...', __FILE__, __LINE__, __METHOD__, 10);

		$data = array(
			'company_id' => $company_obj->getId(),
			'amount' => '0.00',
		);

		return $this->returnHandler( $data );
	}

	/**
	 * Get pay stub transaction data for one or more pay stub transactions.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function getPayStubTransaction( $data = NULL, $disable_paging = FALSE ) {
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( !$this->getPermissionObject()->Check('pay_stub', 'enabled')
			OR !( $this->getPermissionObject()->Check('pay_stub', 'view') OR $this->getPermissionObject()->Check('pay_stub', 'view_child')	) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'pay_stub', 'view' );

		$pstlf = TTnew( 'PayStubTransactionListFactory' ); /** @var PayStubTransactionListFactory $pstlf */
		$pstlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
		Debug::Text('Record Count: '. $pstlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $pstlf->getRecordCount() > 0 ) {
			$this->setPagerObject( $pstlf );

			$retarr = array();
			foreach( $pstlf as $pst_obj ) {
				$retarr[] = $pst_obj->getObjectAsArray( $data['filter_columns'] );
			}

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( TRUE ); //No records returned.
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonPayStubTransactionData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getPayStubTransaction( $data, TRUE ) ) );
	}

	/**
	 * Validate pay stub transaction data for one or more pay stub transactions.
	 * @param array $data pay stub transaction data
	 * @return array
	 */
	function validatePayStubTransaction( $data ) {
		return $this->setPayStubTransaction( $data, TRUE );
	}

	/**
	 * Set pay stub transaction data for one or more pay stub transactions.
	 * @param array $data pay stub transaction data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setPayStubTransaction( $data, $validate_only = FALSE, $ignore_warning = TRUE ) {
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
		Debug::Text('Received data for: '. $total_records .' PayStubTransactions', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		$validator = $save_result = $key = FALSE;
		if ( is_array($data) AND $total_records > 0 ) {
			foreach( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'PayStubTransactionListFactory' ); /** @var PayStubTransactionListFactory $lf */
				$lf->StartTransaction();
				if ( isset($row['id']) AND $row['id'] != '' ) {
					//Modifying existing object.
					//Get pay stub transaction object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
							$validate_only == TRUE
							OR
							(
								$this->getPermissionObject()->Check('pay_stub', 'edit')
								OR ( $this->getPermissionObject()->Check('pay_stub', 'edit_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE )
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
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check('pay_stub', 'add'), TTi18n::gettext('Add permission denied') );
				}
				Debug::Arr($row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid( $ignore_warning );
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Setting object data...', __FILE__, __LINE__, __METHOD__, 10);

					$lf->setObjectFromArray( $row );

					$lf->Validator->setValidateOnly( $validate_only );

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
			}

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * Delete one or more pay stub transactions.
	 * @param array $data pay stub transaction data
	 * @return array|bool
	 */
	function deletePayStubTransaction( $data ) {
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

		Debug::Text('Received data for: '. count($data) .' PayStubTransactions', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$total_records = count($data);
		$validator = $save_result = $key = FALSE;
		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) AND $total_records > 0 ) {
			foreach( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'PayStubTransactionListFactory' ); /** @var PayStubTransactionListFactory $lf */
				$lf->StartTransaction();
				if ( $id != '' ) {
					//Modifying existing object.
					//Get pay stub transaction object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check('pay_stub', 'delete')
							OR ( $this->getPermissionObject()->Check('pay_stub', 'delete_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE ) ) {
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
			}

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * Copy one or more pay stub transactions.
	 * @param array $data pay stub transaction IDs
	 * @return array
	 */
	function copyPayStubTransaction( $data ) {
		if ( !is_array($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		Debug::Text('Received data for: '. count($data) .' PayStubTransactions', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$src_rows = $this->stripReturnHandler( $this->getPayStubTransaction( array('filter_data' => array('id' => $data) ), TRUE ) );
		if ( is_array( $src_rows ) AND count($src_rows) > 0 ) {
			Debug::Arr($src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);
			foreach( $src_rows as $key => $row ) {
				unset($src_rows[$key]['id'] ); //Clear fields that can't be copied
			}
			//Debug::Arr($src_rows, 'bSRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->setPayStubTransaction( $src_rows ); //Save copied rows
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * Allow filters from Pay Stub Summary and Pay Stub Transaction Summary reports to return the transaction summary
	 *
	 * @param $filter_data
	 * @return array|bool
	 */
	function getPayPeriodTransactionSummary( $filter_data ) {
		if ( !$this->getPermissionObject()->Check('pay_stub', 'enabled')
				OR !( $this->getPermissionObject()->Check('pay_stub', 'view') OR $this->getPermissionObject()->Check('pay_stub', 'view_child')	) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		$pstlf = TTnew( 'PayStubTransactionListFactory' ); /** @var PayStubTransactionListFactory $pstlf */
		$company_id = $this->getCurrentCompanyObject()->getId();

		if ( isset($filter_data['time_period']) AND is_array($filter_data['time_period']) ) {
			$report_obj = TTnew('Report'); /** @var Report $report_obj */
			$report_obj->setUserObject( $this->getCurrentUserObject() );
			$report_obj->setPermissionObject( $this->getPermissionObject() );
			Debug::Text('Found TimePeriod...', __FILE__, __LINE__, __METHOD__, 10);
			$filter_data = array_merge( $filter_data, (array)$report_obj->convertTimePeriodToStartEndDate( $filter_data['time_period'] ) );
			unset($report_obj);
		}

		//These filters are also in APIPayStub->getPayStub().
		$filter_data['transaction_status_id'] = array( 10, 200 ); //10=Pending, 200=Stop Payment (ReIssue) -- Make sure we don't show stop payment or paid transactions in the list.
		$filter_data['transaction_type_id'] = 10; //10=Valid

		//SECURITY: Keep this line right before the API execution so nothing overwrites this filter by accident
		$filter_data['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'pay_stub', 'view' );
		$pstlf->getAPISummaryByCompanyIdAndArrayCriteria( $company_id, $filter_data );

		$rsa_obj = TTnew( 'RemittanceSourceAccountFactory' ); /** @var RemittanceSourceAccountFactory $rsa_obj */

		$retarr = array();
		foreach($pstlf as $pst_obj) {
			$currency = $pst_obj->getCurrencyObject()->getName();
			$retarr[] = array( 'pay_period_id' => $pst_obj->getPayPeriodID(),
							   'currency' => $currency,
							   'remittance_source_account_id' => $pst_obj->getRemittanceSourceAccount(),
							   'remittance_source_account_last_transaction_number' => $pst_obj->getRemittanceSourceAccountObject()->getLastTransactionNumber(),
							   'remittance_source_account_type' => Option::getByKey( $pst_obj->getRemittanceSourceAccountType(), $rsa_obj->getOptions('type') ),
							   'remittance_source_account' => $pst_obj->getRemittanceSourceAccountName(),
							   'total_amount' => Misc::removeTrailingZeros( $pst_obj->getColumn('total_amount') ),
							   'total_transactions' => $pst_obj->getColumn('total_transactions')
			);
		}

		//Debug::Arr($retarr, 'Retarr: ', __FILE__, __LINE__, __METHOD__, 10);
		return $this->returnHandler( $retarr );
	}

}
?>
