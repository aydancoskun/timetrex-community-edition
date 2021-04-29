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
 * @package Modules\Report
 */
class PayStubTransactionSummaryReport extends Report {

	/**
	 * PayStubTransactionSummaryReport constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText( 'Pay Stub Transaction Summary Report' );
		$this->file_name = 'paystub_transaction_summary_report';

		parent::__construct();

		return true;
	}

	/**
	 * @param string $user_id    UUID
	 * @param string $company_id UUID
	 * @return bool
	 */
	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check( 'report', 'enabled', $user_id, $company_id )
				&& $this->getPermissionObject()->Check( 'report', 'view_pay_stub_summary', $user_id, $company_id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	protected function _validateConfig() {
		$config = $this->getConfig();

		//Make sure some time period is selected.
		if ( ( !isset( $config['filter']['time_period'] ) && !isset( $config['filter']['pay_period_id'] ) ) || ( isset( $config['filter']['time_period'] ) && isset( $config['filter']['time_period']['time_period'] ) && $config['filter']['time_period']['time_period'] == TTUUID::getZeroId() ) ) {
			$this->validator->isTrue( 'time_period', false, TTi18n::gettext( 'No time period defined for this report' ) );
		}

		return true;
	}

	/**
	 * @param $name
	 * @param null $params
	 * @return array|null
	 */
	protected function _getOptions( $name, $params = null ) {
		$retval = null;
		switch ( $name ) {
			case 'status':
			case 'type':
			case 'transaction_status_id':
			case 'transaction_type_id':
			case 'remittance_source_account_type_id':
				$pstf = TTnew( 'PayStubTransactionFactory' ); /** @var PayStubTransactionFactory $pstf */
				$retval = $pstf->getOptions( $name );
				break;
			case 'output_format':
				$retval = parent::getOptions( 'default_output_format' );
				break;
			case 'default_setup_fields':
				$retval = [
						'template',
						'time_period',
						'columns',
				];
				break;
			case 'setup_fields':
				$retval = [
						'-1000-template'        => TTi18n::gettext( 'Template' ),
						'-1010-time_period'     => TTi18n::gettext( 'Time Period' ),
						'-1500-legal_entity_id' => TTi18n::gettext( 'Legal Entity' ),

						'-1510-transaction_transaction_date-date_stamp' => TTi18n::gettext( 'Transaction Date' ),
						//'-1510-transaction_type_id' => TTi18n::gettext('Transaction Type'),
						'-1520-transaction_status_id'                   => TTi18n::gettext( 'Transaction Status' ),
						'-1520-is_reprint'                              => TTi18n::gettext( 'Reprint' ),

						'-1620-remittance_source_account_type_id' => TTi18n::gettext( 'Source Account Type' ),
						'-1650-confirmation_number'               => TTi18n::gettext( 'Confirmation Number' ),

						'-2010-user_status_id'          => TTi18n::gettext( 'Employee Status' ),
						'-2020-user_group_id'           => TTi18n::gettext( 'Employee Group' ),
						'-2030-user_title_id'           => TTi18n::gettext( 'Employee Title' ),
						'-2035-user_tag'                => TTi18n::gettext( 'Employee Tags' ),
						'-2040-include_user_id'         => TTi18n::gettext( 'Employee Include' ),
						'-2050-exclude_user_id'         => TTi18n::gettext( 'Employee Exclude' ),
						'-2060-default_branch_id'       => TTi18n::gettext( 'Default Branch' ),
						'-2070-default_department_id'   => TTi18n::gettext( 'Default Department' ),
						'-2080-currency_id'             => TTi18n::gettext( 'Currency' ),
						'-2100-custom_filter'           => TTi18n::gettext( 'Custom Filter' ),

						'-2200-pay_stub_status_id' => TTi18n::gettext( 'Pay Stub Status' ),
						'-2205-pay_stub_type_id'   => TTi18n::gettext( 'Pay Stub Type' ),
						'-2210-pay_stub_run_id'    => TTi18n::gettext( 'Payroll Run' ),

						//'-4020-exclude_ytd_adjustment' => TTi18n::gettext('Exclude YTD Adjustments'),

						'-5000-columns'    => TTi18n::gettext( 'Display Columns' ),
						'-5010-group'      => TTi18n::gettext( 'Group By' ),
						'-5020-sub_total'  => TTi18n::gettext( 'SubTotal By' ),
						'-5030-sort'       => TTi18n::gettext( 'Sort By' ),
						'-5040-page_break' => TTi18n::gettext( 'Page Break On' ),
				];
				break;
			case 'time_period':
				$retval = TTDate::getTimePeriodOptions();
				break;
			case 'date_columns':
				$retval = array_merge(
//								TTDate::getReportDateOptions( 'hire', TTi18n::getText( 'Hire Date' ), 13, FALSE ),
//								TTDate::getReportDateOptions( 'termination', TTi18n::getText( 'Termination Date' ), 14, FALSE ),
						TTDate::getReportDateOptions( 'pay_stub_transaction', TTi18n::getText( 'Pay Stub Transaction Date' ), 26, true ),
						TTDate::getReportDateOptions( 'transaction', TTi18n::getText( 'Transaction Date' ), 27, true )
				);
				break;
			case 'custom_columns':
				//Get custom fields for report data.
				$oflf = TTnew( 'OtherFieldListFactory' ); /** @var OtherFieldListFactory $oflf */
				//User and Punch fields conflict as they are merged together in a secondary process.
				$other_field_names = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), [ 10 ], [ 10 => '' ] );
				if ( is_array( $other_field_names ) ) {
					$retval = Misc::addSortPrefix( $other_field_names, 9000 );
				}
				break;
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), null, 'PayStubTransactionSummaryReport', 'custom_column' );
					if ( is_array( $custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'filter_column_type_ids' ), null, 'PayStubTransactionSummaryReport', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'dynamic_format_ids' ), 'PayStubTransactionSummaryReport', 'custom_column' );
					if ( is_array( $report_dynamic_custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'static_format_ids' ), 'PayStubTransactionummaryReport', 'custom_column' );
					if ( is_array( $report_static_custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $report_static_custom_column_labels, 9700 );
					}
				}
				break;
			case 'formula_columns':
				$retval = TTMath::formatFormulaColumns( array_merge( array_diff( $this->getOptions( 'static_columns' ), (array)$this->getOptions( 'report_static_custom_column' ) ), $this->getOptions( 'dynamic_columns' ) ) );
				break;
			case 'filter_columns':
				$retval = TTMath::formatFormulaColumns( array_merge( $this->getOptions( 'static_columns' ), $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_dynamic_custom_column' ) ) );
				break;
			case 'static_columns':
				$retval = [
					//Static Columns - Aggregate functions can't be used on these.
					'-1000-destination_user_first_name' => TTi18n::gettext( 'First Name' ),
					'-1002-destination_user_last_name'  => TTi18n::gettext( 'Last Name' ),

					'-1004-transaction_status'  => TTi18n::gettext( 'Status' ),
					'-1008-transaction_type' => TTi18n::gettext( 'Type' ),

					'-1010-remittance_source_account'      => TTi18n::gettext( 'Source Account' ),
					'-1012-remittance_destination_account' => TTi18n::gettext( 'Destination Account' ),

					'-1020-pay_period_start_date' => TTi18n::gettext( 'Pay Period Start Date' ),
					'-1022-pay_period_end_date'   => TTi18n::gettext( 'Pay Period End Date' ),

					'-1025-pay_stub_run_id'     => TTi18n::gettext( 'Pay Stub Run' ),
					'-1025-confirmation_number' => TTi18n::gettext( 'Confirmation Number' ),

					'-1040-pay_stub_status_id'  => TTi18n::gettext( 'Pay Stub Status' ),
					'-1041-pay_stub_start_date' => TTi18n::gettext( 'Pay Stub Start Date' ),
					'-1042-pay_stub_end_date'   => TTi18n::gettext( 'Pay Stub End Date' ),

					'-1110-currency'			=> TTi18n::gettext( 'Currency' ),
					'-1131-current_currency'    => TTi18n::gettext( 'Current Currency' ),
				];

				$retval = array_merge( $retval, (array)$this->getOptions( 'date_columns' ), (array)$this->getOptions( 'custom_columns' ), (array)$this->getOptions( 'report_static_custom_column' ) );
				ksort( $retval );
				break;
			case 'dynamic_columns':
				$retval = [
					//Dynamic - Aggregate functions can be used
					'-2010-amount' => TTi18n::gettext( 'Amount' ),
				];
				ksort( $retval );
				break;

			case 'columns':
				$retval = array_merge( $this->getOptions( 'static_columns' ), $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_dynamic_custom_column' ) );
				ksort( $retval );
				break;
			case 'column_format':
				//Define formatting function for each column.
				$columns = Misc::trimSortPrefix( array_merge( $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_custom_column' ) ) );
				if ( is_array( $columns ) ) {
					foreach ( $columns as $column => $name ) {
						if ( strpos( $column, 'amount' ) !== false ) {
							$retval[$column] = 'currency';
						} else if ( strpos( $column, 'total_pay_stub_transaction' ) !== false ) {
							$retval[$column] = 'numeric';
						}
					}
				}
				break;
			case 'aggregates':
				$retval = [];
				$dynamic_columns = array_keys( Misc::trimSortPrefix( array_merge( $this->getOptions( 'dynamic_columns' ) ) ) );
				if ( is_array( $dynamic_columns ) ) {
					foreach ( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								$retval[$column] = 'sum';
						}
					}
				}
				break;
			case 'templates':
				$retval = [
						'-4000-pending_transactions'       => TTi18n::gettext( 'Pending Transactions' ),
						'-5000-pending_check_transactions' => TTi18n::gettext( 'Pending Check Transactions' ),
						'-6000-pending_eft_transactions'   => TTi18n::gettext( 'Pending EFT/ACH Transactions' ),
						'-7000-stop_payment_transactions'  => TTi18n::gettext( 'Stopped Transactions' ),
						'-8000-paid_transactions'          => TTi18n::gettext( 'Paid Transactions' ),
				];

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset( $template ) && $template != '' ) {
					switch ( $template ) {
						case 'pending_transactions':
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';
							$retval['-1520-transaction_status_id'] = [ 10 ];

							$retval['columns'][] = 'transaction_type';
							$retval['columns'][] = 'remittance_source_account';
							$retval['columns'][] = 'destination_user_first_name';
							$retval['columns'][] = 'destination_user_last_name';
							$retval['columns'][] = 'remittance_destination_account';
							$retval['columns'][] = 'amount';
							$retval['columns'][] = 'pay_stub_transaction-date_stamp';
							$retval['columns'][] = 'transaction-date_stamp';

							$retval['sub_total'][] = 'transaction_type';
							$retval['sub_total'][] = 'remittance_source_account';

							$retval['sort'][] = [ 'transaction_type' => 'asc' ];
							$retval['sort'][] = [ 'remittance_source_account' => 'asc' ];
							$retval['sort'][] = [ 'destination_user_last_name' => 'asc' ];
							$retval['sort'][] = [ 'destination_user_first_name' => 'asc' ];
							$retval['sort'][] = [ 'remittance_destination_account' => 'asc' ];
							break;
						case 'pending_check_transactions':
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';
							$retval['-1520-transaction_status_id'] = [ 10 ];
							$retval['-1620-remittance_source_account_type_id'] = 2000;

							$retval['columns'][] = 'remittance_source_account';
							$retval['columns'][] = 'destination_user_first_name';
							$retval['columns'][] = 'destination_user_last_name';
							$retval['columns'][] = 'remittance_destination_account';
							$retval['columns'][] = 'amount';
							$retval['columns'][] = 'pay_stub_transaction-date_stamp';
							$retval['columns'][] = 'transaction-date_stamp';

							$retval['sub_total'][] = 'remittance_source_account';

							$retval['sort'][] = [ 'transaction_type' => 'asc' ];
							$retval['sort'][] = [ 'remittance_source_account' => 'asc' ];
							$retval['sort'][] = [ 'destination_user_last_name' => 'asc' ];
							$retval['sort'][] = [ 'destination_user_first_name' => 'asc' ];
							$retval['sort'][] = [ 'remittance_destination_account' => 'asc' ];
							break;
						case 'pending_eft_transactions':
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';
							$retval['-1520-transaction_status_id'] = [ 10 ];
							$retval['-1620-remittance_source_account_type_id'] = 3000;

							$retval['columns'][] = 'remittance_source_account';
							$retval['columns'][] = 'destination_user_first_name';
							$retval['columns'][] = 'destination_user_last_name';
							$retval['columns'][] = 'remittance_destination_account';
							$retval['columns'][] = 'amount';
							$retval['columns'][] = 'pay_stub_transaction-date_stamp';
							$retval['columns'][] = 'transaction-date_stamp';

							$retval['sub_total'][] = 'remittance_source_account';

							$retval['sort'][] = [ 'remittance_source_account' => 'asc' ];
							$retval['sort'][] = [ 'destination_user_last_name' => 'asc' ];
							$retval['sort'][] = [ 'destination_user_first_name' => 'asc' ];
							$retval['sort'][] = [ 'remittance_destination_account' => 'asc' ];
							break;
						case 'stop_payment_transactions':
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';
							$retval['-1520-transaction_status_id'] = [ 100, 110 ];

							$retval['columns'][] = 'transaction_type';
							$retval['columns'][] = 'remittance_source_account';
							$retval['columns'][] = 'destination_user_first_name';
							$retval['columns'][] = 'destination_user_last_name';
							$retval['columns'][] = 'remittance_destination_account';
							$retval['columns'][] = 'amount';
							$retval['columns'][] = 'confirmation_number';
							$retval['columns'][] = 'pay_stub_transaction-date_stamp';
							$retval['columns'][] = 'transaction-date_stamp';

							$retval['sub_total'][] = 'transaction_type';
							$retval['sub_total'][] = 'remittance_source_account';

							$retval['sort'][] = [ 'remittance_source_account' => 'asc' ];
							$retval['sort'][] = [ 'destination_user_last_name' => 'asc' ];
							$retval['sort'][] = [ 'destination_user_first_name' => 'asc' ];
							$retval['sort'][] = [ 'remittance_destination_account' => 'asc' ];
							break;
						case 'paid_transactions':
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';
							$retval['-1520-transaction_status_id'] = [ 20 ];

							$retval['columns'][] = 'transaction_type';
							$retval['columns'][] = 'remittance_source_account';
							$retval['columns'][] = 'destination_user_first_name';
							$retval['columns'][] = 'destination_user_last_name';
							$retval['columns'][] = 'remittance_destination_account';
							$retval['columns'][] = 'amount';
							$retval['columns'][] = 'confirmation_number';
							$retval['columns'][] = 'pay_stub_transaction-date_stamp';
							$retval['columns'][] = 'transaction-date_stamp';

							$retval['sub_total'][] = 'remittance_source_account';

							$retval['sort'][] = [ 'transaction_type' => 'asc' ];
							$retval['sort'][] = [ 'remittance_source_account' => 'asc' ];
							$retval['sort'][] = [ 'destination_user_last_name' => 'asc' ];
							$retval['sort'][] = [ 'destination_user_first_name' => 'asc' ];
							$retval['sort'][] = [ 'remittance_destination_account' => 'asc' ];
							break;
					}
				}

				//Set the template dropdown as well.
				$retval['-1000-template'] = $template;

				//Add sort prefixes so Flex can maintain order.
				if ( isset( $retval['filter'] ) ) {
					$retval['-5000-filter'] = $retval['filter'];
					unset( $retval['filter'] );
				}
				if ( isset( $retval['columns'] ) ) {
					$retval['-5010-columns'] = $retval['columns'];
					unset( $retval['columns'] );
				}
				if ( isset( $retval['group'] ) ) {
					$retval['-5020-group'] = $retval['group'];
					unset( $retval['group'] );
				}
				if ( isset( $retval['sub_total'] ) ) {
					$retval['-5030-sub_total'] = $retval['sub_total'];
					unset( $retval['sub_total'] );
				}
				if ( isset( $retval['sort'] ) ) {
					$retval['-5040-sort'] = $retval['sort'];
					unset( $retval['sort'] );
				}
				Debug::Arr( $retval, ' Template Config for: ' . $template, __FILE__, __LINE__, __METHOD__, 10 );

				break;
			default:
				//Call report parent class options function for options valid for all reports.
				$retval = $this->__getOptions( $name );
				break;
		}

		return $retval;
	}

	/**
	 * Get raw data for report
	 * @param null $format
	 * @return bool
	 */
	function _getData( $format = null ) {
		$this->tmp_data = [ 'pay_stub_transaction' => [], 'user' => [] ];
		$filter_data = $this->getFilterConfig();

		$currency_convert_to_base = $this->getCurrencyConvertToBase();
		$base_currency_obj = $this->getBaseCurrencyObject();
		$this->handleReportCurrency( $currency_convert_to_base, $base_currency_obj, $filter_data );
		$currency_options = $this->getOptions( 'currency' );

		$filter_data['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'pay_stub', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );

		$rsaf = TTnew( 'RemittanceSourceAccountFactory' ); /** @var RemittanceSourceAccountFactory $rsaf */ //For getOptions() below.
		$psf = TTnew( 'PayStubFactory' ); /** @var PayStubFactory $psf */ //For getOptions() below.

		$pstlf = TTnew( 'PayStubTransactionListFactory' ); /** @var PayStubTransactionListFactory $pstlf */
		$pstlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $pstlf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		Debug::Text( 'PayStubTransaction report records: ' . $pstlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $pstlf->getRecordCount() > 0 ) {
			foreach ( $pstlf as $key => $pst_obj ) {
				$user_id = $pst_obj->getColumn( 'user_id' );
				$tmp_row = [
						'legal_entity_id'                   => $pst_obj->getColumn( 'legal_entity_id' ),
						'legal_entity_legal_name'           => $pst_obj->getColumn( 'legal_entity_legal_name' ),
						'legal_entity_trade_name'           => $pst_obj->getColumn( 'legal_entity_trade_name' ),
						'remittance_source_account'         => $pst_obj->getColumn( 'remittance_source_account' ),
						'remittance_source_account_id'      => $pst_obj->getRemittanceSourceAccount(),
						'remittance_destination_account'    => $pst_obj->getColumn( 'remittance_destination_account' ),
						'remittance_destination_account_id' => $pst_obj->getRemittanceDestinationAccount(),
						'user_id'                           => $pst_obj->getColumn( 'user_id' ),
						'destination_user_first_name'       => $pst_obj->getColumn( 'destination_user_first_name' ),
						'destination_user_last_name'        => $pst_obj->getColumn( 'destination_user_last_name' ),
						'remittance_source_account_type_id' => $pst_obj->getColumn( 'remittance_source_account_type_id' ),
						'transaction_type'                  => Option::getByKey( $pst_obj->getColumn( 'remittance_source_account_type_id' ), $rsaf->getOptions( 'type' ) ),
						'transaction_type_id'               => $pst_obj->getType(),
						'transaction_status'                => Option::getByKey( $pst_obj->getColumn( 'status_id' ), $pstlf->getOptions( 'status' ) ),
						'transaction_status_id'             => $pst_obj->getStatus(),
						'transaction_date'                  => $pst_obj->getTransactionDate(),

						'amount'              => $pst_obj->getAmount(),
						'confirmation_number' => $pst_obj->getConfirmationNumber(),

						'pay_period_id'               => $pst_obj->getColumn( 'pay_period_id' ),
						'pay_period_start_date'       => TTDate::strtotime( $pst_obj->getColumn( 'pay_period_start_date' ) ),
						'pay_period_end_date'         => TTDate::strtotime( $pst_obj->getColumn( 'pay_period_end_date' ) ),
						'pay_period_transaction_date' => TTDate::strtotime( $pst_obj->getColumn( 'pay_period_transaction_date' ) ),
						'pay_stub_run_id'             => $pst_obj->getColumn( 'pay_stub_run_id' ),

						'pay_stub_id'               => $pst_obj->getColumn( 'pay_stub_id' ),
						'pay_stub_status_id'        => Option::getByKey( $pst_obj->getColumn( 'pay_stub_status_id' ), $psf->getOptions( 'status' ) ),
						'pay_stub_start_date'       => TTDate::strtotime( $pst_obj->getColumn( 'pay_stub_start_date' ) ),
						'pay_stub_end_date'         => TTDate::strtotime( $pst_obj->getColumn( 'pay_stub_end_date' ) ),
						'pay_stub_transaction_date' => TTDate::strtotime( $pst_obj->getColumn( 'pay_stub_transaction_date' ) ),

						'currency_rate'           => $pst_obj->getColumn( 'currency_rate' ),
				];

				$tmp_row['currency'] = $tmp_row['current_currency'] = Option::getByKey( $pst_obj->getColumn( 'currency_id' ), $currency_options );

				if ( $currency_convert_to_base == true && is_object( $base_currency_obj ) ) {
					$tmp_row['current_currency'] = Option::getByKey( $base_currency_obj->getId(), $currency_options );
				}

				$this->tmp_data['pay_stub_transaction'][$user_id][] = $tmp_row;
				unset( $tmp_row );

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}
		}

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		unset( $filter_data['currency_id'] ); //Don't filter users based on currency_id, since the user currency_id may not match the transaction currency id, then nothing will be displayed.
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Total Rows: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( array_merge( (array)$this->getColumnDataConfig(), [ 'hire_date' => true, 'termination_date' => true, 'birth_date' => true ] ) );
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);foo
		//Debug::Arr($this->tmp_data, 'TMP Data: ', __FILE__, __LINE__, __METHOD__, 10);
		return true;
	}

	/**
	 * PreProcess data such as calculating additional columns from raw data etc...
	 * @return bool
	 */
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $this->tmp_data['pay_stub_transaction'] ), null, TTi18n::getText( 'Pre-Processing Data...' ) );

		//Merge time data with user data
		$key = 0;
		if ( isset( $this->tmp_data['pay_stub_transaction'] ) ) {
			foreach ( $this->tmp_data['pay_stub_transaction'] as $user_id => $rows ) {
				if ( isset( $this->tmp_data['user'][$user_id] ) ) {
					foreach ( $rows as $row ) {
						if ( is_array( $row ) ) {
							$date_columns = TTDate::getReportDates( 'pay_stub_transaction', $row['pay_stub_transaction_date'], false, $this->getUserObject(), [ 'pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date'] ] );
							$transaction_date_columns = TTDate::getReportDates( 'transaction', $row['transaction_date'], false, $this->getUserObject(), [ 'pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date'] ] );

							$processed_data = [
								//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
								'pay_period_start_date'       => [ 'sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate( 'DATE', $row['pay_period_start_date'] ) ],
								'pay_period_end_date'         => [ 'sort' => $row['pay_period_end_date'], 'display' => TTDate::getDate( 'DATE', $row['pay_period_end_date'] ) ],
								'pay_period_transaction_date' => [ 'sort' => $row['pay_period_transaction_date'], 'display' => TTDate::getDate( 'DATE', $row['pay_period_transaction_date'] ) ],
								'pay_stub_start_date'         => [ 'sort' => $row['pay_stub_start_date'], 'display' => TTDate::getDate( 'DATE', $row['pay_stub_start_date'] ) ],
								'pay_stub_end_date'           => [ 'sort' => $row['pay_stub_end_date'], 'display' => TTDate::getDate( 'DATE', $row['pay_stub_end_date'] ) ],
							];

							//Need to make sure PSEA IDs are strings not numeric otherwise array_merge will re-key them.
							//$hire_date_columns, $termination_date_columns, $birth_date_columns
							$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $transaction_date_columns, $processed_data );

							$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
							$key++;
						}
					}
				}
			}
			unset( $this->tmp_data, $row, $date_columns, $transaction_date_columns, $hire_date_columns, $termination_date_columns, $birth_date_columns, $processed_data, $level_1 );
		}

		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return true;
	}
}

?>