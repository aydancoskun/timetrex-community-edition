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
 * @package Modules\Report
 */
class GeneralLedgerSummaryReport extends Report {

	/**
	 * GeneralLedgerSummaryReport constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText( 'General Ledger Summary Report' );
		$this->file_name = 'generalledger_summary_report';

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
				&& $this->getPermissionObject()->Check( 'report', 'view_general_ledger_summary', $user_id, $company_id ) ) {
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
	 * @return array|bool|mixed|null
	 */
	protected function _getOptions( $name, $params = null ) {
		$retval = null;
		switch ( $name ) {
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
					//Static Columns - Aggregate functions can't be used on these.
					'-1000-template'              => TTi18n::gettext( 'Template' ),
					'-1010-time_period'           => TTi18n::gettext( 'Time Period' ),
					'-2000-legal_entity_id'       => TTi18n::gettext( 'Legal Entity' ),
					'-2010-user_status_id'        => TTi18n::gettext( 'Employee Status' ),
					'-2020-user_group_id'         => TTi18n::gettext( 'Employee Group' ),
					'-2030-user_title_id'         => TTi18n::gettext( 'Employee Title' ),
					'-2040-include_user_id'       => TTi18n::gettext( 'Employee Include' ),
					'-2050-exclude_user_id'       => TTi18n::gettext( 'Employee Exclude' ),
					'-2060-default_branch_id'     => TTi18n::gettext( 'Default Branch' ),
					'-2070-default_department_id' => TTi18n::gettext( 'Default Department' ),
					'-2080-currency_id'           => TTi18n::gettext( 'Currency' ),
					'-2100-custom_filter'         => TTi18n::gettext( 'Custom Filter' ),

					'-2205-pay_stub_type_id' => TTi18n::gettext( 'Pay Stub Type' ),
					'-2210-pay_stub_run_id'  => TTi18n::gettext( 'Payroll Run' ),

					//'-4020-exclude_ytd_adjustment' => TTi18n::gettext('Exclude YTD Adjustments'), //No longer supported.

					'-5000-columns'    => TTi18n::gettext( 'Display Columns' ), //No Columns for this report.
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
				$retval = TTDate::getReportDateOptions( 'transaction', TTi18n::getText( 'Transaction Date' ), 13, true );
				break;
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), null, 'GeneralLedgerSummaryReport', 'custom_column' );
					if ( is_array( $custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'filter_column_type_ids' ), null, 'GeneralLedgerSummaryReport', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'dynamic_format_ids' ), 'GeneralLedgerSummaryReport', 'custom_column' );
					if ( is_array( $report_dynamic_custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'static_format_ids' ), 'GeneralLedgerSummaryReport', 'custom_column' );
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
					'-1000-first_name'          => TTi18n::gettext( 'First Name' ),
					'-1001-middle_name'         => TTi18n::gettext( 'Middle Name' ),
					'-1002-last_name'           => TTi18n::gettext( 'Last Name' ),
					'-1005-full_name'           => TTi18n::gettext( 'Full Name' ),
					'-1030-employee_number'     => TTi18n::gettext( 'Employee #' ),
					'-1040-status'              => TTi18n::gettext( 'Status' ),
					'-1050-title'               => TTi18n::gettext( 'Title' ),
					'-1060-province'            => TTi18n::gettext( 'Province/State' ),
					'-1070-country'             => TTi18n::gettext( 'Country' ),
					'-1080-user_group'          => TTi18n::gettext( 'Group' ),
					'-1090-default_branch'      => TTi18n::gettext( 'Default Branch' ),
					'-1100-default_department'  => TTi18n::gettext( 'Default Department' ),
					'-1110-currency'            => TTi18n::gettext( 'Currency' ),
					'-1200-permission_control'  => TTi18n::gettext( 'Permission Group' ),
					'-1210-pay_period_schedule' => TTi18n::gettext( 'Pay Period Schedule' ),
					'-1220-policy_group'        => TTi18n::gettext( 'Policy Group' ),
					//Handled in date_columns above.
					//'-1250-pay_period' => TTi18n::gettext('Pay Period'),

					'-1800-pay_stub_status' => TTi18n::gettext( 'Pay Stub Status' ),
					'-1810-pay_stub_type'   => TTi18n::gettext( 'Pay Stub Type' ),
					'-1820-pay_stub_run_id' => TTi18n::gettext( 'Payroll Run' ),

					'-2010-account'          => TTi18n::gettext( 'Account' ),
					'-3000-pay_stub_account' => TTi18n::gettext( 'Pay Stub Account' ),

					'-9100-debit_account'  => TTi18n::gettext( 'Debit Account' ), //Mainly used for worksheet
					'-9110-credit_account' => TTi18n::gettext( 'Credit Account' ), //Mainly used for worksheet
				];

				$retval = array_merge( $retval, $this->getOptions( 'date_columns' ), (array)$this->getOptions( 'report_static_custom_column' ) );
				ksort( $retval );
				break;
			case 'dynamic_columns':
				$retval = [
					//Dynamic - Aggregate functions can be used
					'-2100-debit_amount'  => TTi18n::gettext( 'Debit Amount' ),
					'-2110-credit_amount' => TTi18n::gettext( 'Credit Amount' ),

					'-3100-amount' => TTi18n::gettext( 'Amount' ),
				];

				break;
			case 'pay_stub_account_amount_columns':
				//Get all pay stub accounts
				$retval = [];

				$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
				$psealf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), 10, [ 10, 20, 30, 40, 50, 60, 65, 80 ] );
				if ( $psealf->getRecordCount() > 0 ) {
					$type_options = $psealf->getOptions( 'type' );
					foreach ( $type_options as $key => $val ) {
						$type_options[$key] = str_replace( [ 'Employee', 'Employer', 'Deduction', 'Miscellaneous', 'Total' ], [ 'EE', 'ER', 'Ded', 'Misc', '' ], $val );
					}

					$i = 0;
					foreach ( $psealf as $psea_obj ) {
						//Need to make the PSEA_ID a string so we can array_merge it properly later.
						if ( $psea_obj->getType() == 40 ) { //Total accounts.
							$prefix = null;
						} else {
							$prefix = $type_options[$psea_obj->getType()] . ' - ';
						}

						//$retval['-3'. str_pad( $i, 3, 0, STR_PAD_LEFT).'-PA:'.$psea_obj->getID()] = $prefix.$psea_obj->getName();
						$retval[$psea_obj->getID()] = $prefix . $psea_obj->getName();

						$i++;
					}
				}
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
						if ( strpos( $column, '_amount' ) !== false || $column == 'amount' ) {
							$retval[$column] = 'currency';
						}
					}
				}
				$retval['verified_time_sheet_date'] = 'time_stamp';
				break;
			case 'aggregates':
				$retval = [];
				$dynamic_columns = array_keys( Misc::trimSortPrefix( array_merge( $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_dynamic_custom_column' ) ) ) );
				if ( is_array( $dynamic_columns ) ) {
					foreach ( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								if ( strpos( $column, '_hourly_rate' ) !== false || substr( $column, 0, 2 ) == 'PR' ) {
									$retval[$column] = 'avg';
								} else {
									$retval[$column] = 'sum';
								}
						}
					}
				}
				$retval['verified_time_sheet'] = 'first';
				$retval['verified_time_sheet_date'] = 'first';
				break;
			case 'templates':
				$retval = [

						'-1010-by_employee' => TTi18n::gettext( 'by Employee' ),

						'-1110-by_title'                => TTi18n::gettext( 'by Title' ),
						'-1120-by_group'                => TTi18n::gettext( 'by Group' ),
						'-1130-by_branch'               => TTi18n::gettext( 'by Branch' ),
						'-1140-by_department'           => TTi18n::gettext( 'by Department' ),
						'-1150-by_branch_by_department' => TTi18n::gettext( 'by Branch/Department' ),
						'-1160-by_pay_period'           => TTi18n::gettext( 'by Pay Period' ),

						'-3000-by_employee+pay_stub_account' => TTi18n::gettext( 'Worksheet for General Ledger Mapping' ),
				];

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset( $template ) && $template != '' ) {
					switch ( $template ) {
						default:
							Debug::Text( ' Parsing template name: ' . $template, __FILE__, __LINE__, __METHOD__, 10 );
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';

							//Parse template name, and use the keywords separated by '+' to determine settings.

							switch ( $template ) {
								//Columns
								//Filter
								//Group By
								//SubTotal
								//Sort
								case 'by_employee+pay_stub_account':
									$retval['columns'][] = 'full_name';
									$retval['columns'][] = 'pay_stub_account';
									$retval['columns'][] = 'amount';
									$retval['columns'][] = 'debit_account';
									$retval['columns'][] = 'debit_amount';
									$retval['columns'][] = 'credit_account';
									$retval['columns'][] = 'credit_amount';

									$retval['include_user_id'] = []; //They will likely want to run this for just a few employees.
									break;
								case 'by_employee':
									$retval['columns'][] = 'full_name';
									$retval['columns'][] = 'account';
									$retval['columns'][] = 'debit_amount';
									$retval['columns'][] = 'credit_amount';

									$retval['group'][] = 'full_name';
									$retval['group'][] = 'account';

									$retval['sub_total'][] = 'full_name';

									$retval['sort'][] = [ 'full_name' => 'asc' ];
									$retval['sort'][] = [ 'account' => 'asc' ];
									break;

								case 'by_title':
									$retval['columns'][] = 'title';
									$retval['columns'][] = 'account';
									$retval['columns'][] = 'debit_amount';
									$retval['columns'][] = 'credit_amount';

									$retval['group'][] = 'title';
									$retval['group'][] = 'account';

									$retval['sub_total'][] = 'title';

									$retval['sort'][] = [ 'title' => 'asc' ];
									$retval['sort'][] = [ 'account' => 'asc' ];
									break;
								case 'by_group':
									$retval['columns'][] = 'user_group';
									$retval['columns'][] = 'account';
									$retval['columns'][] = 'debit_amount';
									$retval['columns'][] = 'credit_amount';

									$retval['group'][] = 'user_group';
									$retval['group'][] = 'account';

									$retval['sub_total'][] = 'user_group';

									$retval['sort'][] = [ 'user_group' => 'asc' ];
									$retval['sort'][] = [ 'account' => 'asc' ];
									break;
								case 'by_branch':
									$retval['columns'][] = 'default_branch';
									$retval['columns'][] = 'account';
									$retval['columns'][] = 'debit_amount';
									$retval['columns'][] = 'credit_amount';

									$retval['group'][] = 'default_branch';
									$retval['group'][] = 'account';

									$retval['sub_total'][] = 'default_branch';

									$retval['sort'][] = [ 'default_branch' => 'asc' ];
									$retval['sort'][] = [ 'account' => 'asc' ];
									break;
								case 'by_department':
									$retval['columns'][] = 'default_department';
									$retval['columns'][] = 'account';
									$retval['columns'][] = 'debit_amount';
									$retval['columns'][] = 'credit_amount';

									$retval['group'][] = 'default_department';
									$retval['group'][] = 'account';

									$retval['sub_total'][] = 'default_department';

									$retval['sort'][] = [ 'default_department' => 'asc' ];
									$retval['sort'][] = [ 'account' => 'asc' ];
									break;
								case 'by_branch_by_department':
									$retval['columns'][] = 'default_branch';
									$retval['columns'][] = 'default_department';
									$retval['columns'][] = 'account';
									$retval['columns'][] = 'debit_amount';
									$retval['columns'][] = 'credit_amount';

									$retval['group'][] = 'default_branch';
									$retval['group'][] = 'default_department';
									$retval['group'][] = 'account';

									$retval['sub_total'][] = 'default_branch';
									$retval['sub_total'][] = 'default_department';

									$retval['sort'][] = [ 'default_branch' => 'asc' ];
									$retval['sort'][] = [ 'default_department' => 'asc' ];
									$retval['sort'][] = [ 'account' => 'asc' ];
									break;
								case 'by_pay_period':
									$retval['columns'][] = 'transaction-pay_period';
									$retval['columns'][] = 'account';
									$retval['columns'][] = 'debit_amount';
									$retval['columns'][] = 'credit_amount';

									$retval['group'][] = 'transaction-pay_period';
									$retval['group'][] = 'account';

									$retval['sub_total'][] = 'transaction-pay_period';

									$retval['sort'][] = [ 'transaction-pay_period' => 'asc' ];
									$retval['sort'][] = [ 'account' => 'asc' ];
									break;
							}
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
	 * @param array $user_date_total_arr
	 * @param array $pay_period_total_arr
	 * @return array|bool
	 */
	function calculatePercentDistribution( $user_date_total_arr, $pay_period_total_arr ) {
		//Debug::Arr($user_date_total_arr, 'User Date Total Arr: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($pay_period_total_arr, 'Total Time Arr: ', __FILE__, __LINE__, __METHOD__, 10);

		//Flatten array to one dimension and calculate percents.
		if ( is_array( $pay_period_total_arr ) ) {

			$retarr = [];
			foreach ( $pay_period_total_arr as $user_id => $level_1 ) {
				foreach ( $level_1 as $pay_period_id => $pay_period_total_time ) {
					if ( isset( $user_date_total_arr[$user_id][$pay_period_id] ) ) {
						foreach ( $user_date_total_arr[$user_id][$pay_period_id] as $branch_id => $level_10 ) {
							foreach ( $level_10 as $department_id => $level_11 ) {
								foreach ( $level_11 as $job_id => $level_12 ) {
									foreach ( $level_12 as $job_item_id => $total_time ) {
										//Debug::Text('Pay Period Total Time: '. $pay_period_total_time .' Total Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);
										$key = $branch_id . ':' . $department_id . ':' . $job_id . ':' . $job_item_id;
										$retarr[$user_id][$pay_period_id][$key] = ( $total_time / $pay_period_total_time );
									}
								}
							}
						}
					}
				}
				//Keep consistent order of the keys, this may help reduce variances or bugs later on.
				ksort( $retarr[$user_id][$pay_period_id] );
			}

			if ( empty( $retarr ) == false ) {
				//Debug::Arr($retarr, 'RetArr: ', __FILE__, __LINE__, __METHOD__, 10);
				return $retarr;
			} else {
				Debug::Text( '  No distribution data...', __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return false;
	}

	/**
	 * Get raw data for report
	 * @param null $format
	 * @return bool
	 */
	function _getData( $format = null ) {
		$this->tmp_data = [ 'pay_stub_entry' => [], 'user_date_total' => [], 'pay_period_total' => [], 'pay_period_distribution' => [], 'user' => [] ];

		$psf = TTnew( 'PayStubFactory' ); /** @var PayStubFactory $psf */

		$columns = $this->getColumnDataConfig();
		$filter_data = $this->getFilterConfig();

		//$currency_convert_to_base = $this->getCurrencyConvertToBase();
		$currency_convert_to_base = false; //Never convert currencies, as the accounting software needs to handle that on its side based on the chart of accounts, and its unlikely the amounts will balance anyways.
		$base_currency_obj = $this->getBaseCurrencyObject();
		$this->handleReportCurrency( $currency_convert_to_base, $base_currency_obj, $filter_data );
		//$currency_options = $this->getOptions( 'currency' );

		$filter_data['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'pay_stub', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );

		$this->enable_time_based_distribution = false;
		$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
		$psealf->getByCompanyId( $this->getUserObject()->getCompany() );
		$psea_arr = [];
		if ( $psealf->getRecordCount() > 0 ) {
			foreach ( $psealf as $psea_obj ) {
				if ( $this->enable_time_based_distribution == false && ( strpos( $psea_obj->getDebitAccount(), 'punch' ) !== false || strpos( $psea_obj->getCreditAccount(), 'punch' ) !== false ) ) {
					$this->enable_time_based_distribution = true;
				}
				$psea_arr[$psea_obj->getId()] = [
						'name'           => $psea_obj->getName(),
						'debit_account'  => $psea_obj->getDebitAccount(),
						'credit_account' => $psea_obj->getCreditAccount(),
				];
			}
		}
		Debug::Text( ' Time Based Distribution: ' . (int)$this->enable_time_based_distribution, __FILE__, __LINE__, __METHOD__, 10 );

		$crlf = TTnew( 'CurrencyListFactory' ); /** @var CurrencyListFactory $crlf */
		$crlf->getByCompanyId( $this->getUserObject()->getCompany() );

		//Debug::Text(' Permission Children: '. count($permission_children_ids) .' Wage Children: '. count($wage_permission_children_ids), __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($permission_children_ids, 'Permission Children: '. count($permission_children_ids), __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($wage_permission_children_ids, 'Wage Children: '. count($wage_permission_children_ids), __FILE__, __LINE__, __METHOD__, 10);

		//Get total time for each filtered employee in each filtered pay period. DO NOT filter by anything else, as we need the overall total time worked always.
		if ( $this->enable_time_based_distribution == true ) {
			$udtlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udtlf */
			$udtlf->getGeneralLedgerReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
			Debug::Text( ' User Date Total Rows: ' . $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $udtlf->getRecordCount(), null, TTi18n::getText( 'Retrieving Punch Data...' ) );
			$pay_period_ids = [];
			if ( $udtlf->getRecordCount() > 0 ) {
				foreach ( $udtlf as $key => $udt_obj ) {
					$pay_period_ids[$udt_obj->getColumn( 'pay_period_id' )] = true;

					$user_id = $udt_obj->getColumn( 'user_id' );
					//$date_stamp = TTDate::strtotime( $udt_obj->getColumn('date_stamp') );
					$pay_period_id = $udt_obj->getColumn( 'pay_period_id' );
					$branch_id = $udt_obj->getColumn( 'branch_id' );
					$department_id = $udt_obj->getColumn( 'department_id' );
					$job_id = $udt_obj->getColumn( 'job_id' );
					$job_item_id = $udt_obj->getColumn( 'job_item_id' );

					$time_columns = $udt_obj->getTimeCategory( false, $columns ); //Exclude 'total' as its not used in reports anyways, and causes problems when grouping by branch/default branch.
					foreach ( $time_columns as $column ) {
						//Debug::Text('bColumn: '. $column .' Total Time: '. $udt_obj->getColumn('total_time') .' Object Type ID: '. $udt_obj->getColumn('object_type_id') .' Rate: '. $udt_obj->getColumn( 'hourly_rate' ), __FILE__, __LINE__, __METHOD__, 10);

						if ( ( $column == 'worked' || $column == 'absence' ) && $udt_obj->getColumn( 'total_time' ) != 0 ) {
							if ( isset( $this->tmp_data['user_date_total'][$user_id][$pay_period_id][$branch_id][$department_id][$job_id][$job_item_id] ) ) {
								$this->tmp_data['user_date_total'][$user_id][$pay_period_id][$branch_id][$department_id][$job_id][$job_item_id] = bcadd( $this->tmp_data['user_date_total'][$user_id][$pay_period_id][$branch_id][$department_id][$job_id][$job_item_id], $udt_obj->getColumn( 'total_time' ) );
							} else {
								$this->tmp_data['user_date_total'][$user_id][$pay_period_id][$branch_id][$department_id][$job_id][$job_item_id] = $udt_obj->getColumn( 'total_time' );
							}

							if ( isset( $this->tmp_data['pay_period_total'][$user_id][$pay_period_id] ) ) {
								$this->tmp_data['pay_period_total'][$user_id][$pay_period_id] = bcadd( $this->tmp_data['pay_period_total'][$user_id][$pay_period_id], $udt_obj->getColumn( 'total_time' ) );
							} else {
								$this->tmp_data['pay_period_total'][$user_id][$pay_period_id] = $udt_obj->getColumn( 'total_time' );
							}
						}
					}

					$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
				}
			}
			$this->tmp_data['pay_period_distribution'] = $this->calculatePercentDistribution( $this->tmp_data['user_date_total'], $this->tmp_data['pay_period_total'] );
		}

		$pay_stub_account_columns = Misc::trimSortPrefix( $this->getOptions( 'pay_stub_account_amount_columns' ) );

		$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */
		$pself->getAPIGeneralLedgerReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $pself->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		Debug::Text( ' PSE Total Rows: ' . $pself->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $pself->getRecordCount() > 0 ) {
			foreach ( $pself as $key => $pse_obj ) {
				$user_id = $pse_obj->getColumn( 'user_id' );
				$date_stamp = TTDate::strtotime( $pse_obj->getColumn( 'pay_stub_transaction_date' ) ); //Should match PayStubSummary, RemittanceSummary, TaxSummary, GeneralLedgerSummaryReport, etc... $date_stamp too.
				$run_id = $pse_obj->getColumn( 'pay_stub_run_id' );

				if ( !isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id] ) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id] = [
							'pay_stub_status' => Option::getByKey( $pse_obj->getColumn( 'pay_stub_status_id' ), $psf->getOptions( 'status' ) ),
							'pay_stub_type'   => Option::getByKey( $pse_obj->getColumn( 'pay_stub_type_id' ), $psf->getOptions( 'type' ) ),

							'pay_period_start_date'       => strtotime( $pse_obj->getColumn( 'pay_period_start_date' ) ),
							'pay_period_end_date'         => strtotime( $pse_obj->getColumn( 'pay_period_end_date' ) ),
							'pay_period_transaction_date' => strtotime( $pse_obj->getColumn( 'pay_period_transaction_date' ) ),
							'pay_period'                  => strtotime( $pse_obj->getColumn( 'pay_period_transaction_date' ) ),
							'pay_period_id'               => $pse_obj->getColumn( 'pay_period_id' ),

							'pay_stub_start_date'       => strtotime( $pse_obj->getColumn( 'pay_stub_start_date' ) ),
							'pay_stub_end_date'         => strtotime( $pse_obj->getColumn( 'pay_stub_end_date' ) ),
							'pay_stub_transaction_date' => TTDate::getMiddleDayEpoch( strtotime( $pse_obj->getColumn( 'pay_stub_transaction_date' ) ) ), //Some transaction dates could be throughout the day for terminated employees being paid early, so always forward them to the middle of the day to keep group_by working correctly.
							'pay_stub_run_id'           => $run_id,
					];
				}

				//If the account name has punch_branch, punch_department, punch_job, punch_job_item variables specified, loop through
				//those duplicating the row using only a time based distribution percentage for the amount.

				if ( isset( $psea_arr[$pse_obj->getPayStubEntryNameId()] ) ) {
					//Debug::Text('Pay Stub ID: '. $pse_obj->getPayStub() .' PSE ID: '. $pse_obj->getPayStubEntryNameId() .' Amount: '. $pse_obj->getAmount(), __FILE__, __LINE__, __METHOD__, 10);

					//Since Pay Stub Account/Amount don't play nice with GL Account/Debit/Credit columns, if both are displayed, only attach data to them, leave everything else blank.
					if ( isset( $columns['pay_stub_account'] ) && $columns['pay_stub_account'] == true && isset( $columns['amount'] ) && $columns['amount'] == true ) {
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['psen_ids'][] = [
								'pay_stub_account' => ( isset( $pay_stub_account_columns[$pse_obj->getPayStubEntryNameId()] ) ) ? $pay_stub_account_columns[$pse_obj->getPayStubEntryNameId()] : '',
								'amount'           => Misc::MoneyFormat( $base_currency_obj->getBaseCurrencyAmount( $pse_obj->getAmount(), $pse_obj->getColumn( 'currency_rate' ), $currency_convert_to_base ), false ),
						];
					} else {

						if ( isset( $psea_arr[$pse_obj->getPayStubEntryNameId()]['debit_account'] )
								&& $psea_arr[$pse_obj->getPayStubEntryNameId()]['debit_account'] != '' ) {

							$debit_accounts = explode( ',', $psea_arr[$pse_obj->getPayStubEntryNameId()]['debit_account'] );
							foreach ( $debit_accounts as $debit_account ) {
								//Debug::Text('Debit Entry: Account: '. $debit_account .' Amount: '. $pse_obj->getAmount(), __FILE__, __LINE__, __METHOD__, 10);
								//Negative amounts should be switched to the opposite side of the ledger.
								//We can't ignore them, and we can't include them as absolute (always positive) values, and we can't
								//Allow negative amounts as not all accounting systems accept them, but skip any $0 entries
								//This is especially important for handling vacation accruals.
								if ( $pse_obj->getAmount() > 0 ) {
									$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['psen_ids'][] = [
											'account'        => trim( $debit_account ),
											'debit_account'  => trim( $debit_account ),
											'credit_account' => null,
											'debit_amount'   => Misc::MoneyFormat( $base_currency_obj->getBaseCurrencyAmount( $pse_obj->getAmount(), $pse_obj->getColumn( 'currency_rate' ), $currency_convert_to_base ), false ),
											'credit_amount'  => null,
									];
								} else if ( $pse_obj->getAmount() < 0 ) {
									Debug::Text( 'Negative debit amount, switching to credit: ' . $pse_obj->getAmount() . ' Debit Account: ' . $debit_account . ' User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );
									$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['psen_ids'][] = [
											'account'        => trim( $debit_account ),
											'debit_account'  => null,
											'credit_account' => trim( $debit_account ),
											'debit_amount'   => null,
											'credit_amount'  => Misc::MoneyFormat( $base_currency_obj->getBaseCurrencyAmount( abs( $pse_obj->getAmount() ), $pse_obj->getColumn( 'currency_rate' ), $currency_convert_to_base ), false ),
									];
								}
							}
							unset( $debit_accounts, $debit_account );
						}

						if ( isset( $psea_arr[$pse_obj->getPayStubEntryNameId()]['credit_account'] )
								&& $psea_arr[$pse_obj->getPayStubEntryNameId()]['credit_account'] != '' ) {

							//Debug::Text('Combined Credit Accounts: '. count($credit_accounts), __FILE__, __LINE__, __METHOD__, 10);
							$credit_accounts = explode( ',', $psea_arr[$pse_obj->getPayStubEntryNameId()]['credit_account'] );
							foreach ( $credit_accounts as $credit_account ) {
								//Allow negative amounts, but skip any $0 entries
								if ( $pse_obj->getAmount() > 0 ) {
									$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['psen_ids'][] = [
											'account'        => trim( $credit_account ),
											'debit_account'  => null,
											'credit_account' => trim( $credit_account ),
											'debit_amount'   => null,
											'credit_amount'  => Misc::MoneyFormat( $base_currency_obj->getBaseCurrencyAmount( $pse_obj->getAmount(), $pse_obj->getColumn( 'currency_rate' ), $currency_convert_to_base ), false ),
									];
								} else if ( $pse_obj->getAmount() < 0 ) {
									Debug::Text( 'Negative credit amount, switching to debit: ' . $pse_obj->getAmount() . ' Credit Account: ' . $credit_account . ' User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );
									$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['psen_ids'][] = [
											'account'        => trim( $credit_account ),
											'debit_account'  => trim( $credit_account ),
											'credit_account' => null,
											'debit_amount'   => Misc::MoneyFormat( $base_currency_obj->getBaseCurrencyAmount( abs( $pse_obj->getAmount() ), $pse_obj->getColumn( 'currency_rate' ), $currency_convert_to_base ), false ),
											'credit_amount'  => null,
									];
								}
							}
							unset( $credit_accounts, $credit_account );
						}
					}
				} else {
					Debug::Text( 'No Pay Stub Entry Account Matches!', __FILE__, __LINE__, __METHOD__, 10 );
				}
				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}
		}

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Total Rows: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( array_merge( [
																											 'default_branch_id'     => true,
																											 'default_department_id' => true,
																											 'default_job_id'        => true,
																											 'default_job_item_id'   => true,
																											 'title_id'              => true,
																											 'employee_number'       => true,
																											 'other_id1'             => true,
																											 'other_id2'             => true,
																											 'other_id3'             => true,
																											 'other_id4'             => true,
																											 'other_id5'             => true,
																									 ],
																									 (array)$this->getColumnDataConfig() ) );
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->tmp_data, 'TMP Data: ', __FILE__, __LINE__, __METHOD__, 10);
		return true;
	}

	/**
	 * PreProcess data such as calculating additional columns from raw data etc...
	 * @return bool
	 */
	function _preProcess() {
		if ( isset( $this->tmp_data['pay_stub_entry'] ) == false ) {
			return true;
		}

		$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $this->tmp_data['pay_stub_entry'] ), null, TTi18n::getText( 'Pre-Processing Data...' ) );

		$blf = TTnew( 'BranchListFactory' ); /** @var BranchListFactory $blf */
		//Get Branch ID to Branch Code mapping
		$branch_code_map = [ TTUUID::getZeroID() => TTUUID::getZeroID() ];
		$blf->getByCompanyId( $this->getUserObject()->getCompany() );
		if ( $blf->getRecordCount() > 0 ) {
			foreach ( $blf as $b_obj ) {
				$branch_code_map[$b_obj->getId()] = $b_obj;
			}
		}

		$dlf = TTnew( 'DepartmentListFactory' ); /** @var DepartmentListFactory $dlf */
		//Get Department ID to Branch Code mapping
		$department_code_map = [ TTUUID::getZeroID() => TTUUID::getZeroID() ];
		$dlf->getByCompanyId( $this->getUserObject()->getCompany() );
		if ( $dlf->getRecordCount() > 0 ) {
			foreach ( $dlf as $d_obj ) {
				$department_code_map[$d_obj->getId()] = $d_obj;
			}
		}

		$utlf = TTnew( 'UserTitleListFactory' ); /** @var UserTitleListFactory $utlf */
		//Get Title mapping
		$utlf->getByCompanyId( $this->getUserObject()->getCompany() );
		$title_code_map = [ TTUUID::getZeroID() => TTUUID::getZeroID() ];
		if ( $utlf->getRecordCount() > 0 ) {
			foreach ( $utlf as $ut_obj ) {
				$title_code_map[$ut_obj->getId()] = $ut_obj;
			}
		}

		$job_code_map = [ TTUUID::getZeroID() => TTUUID::getZeroID() ];      //Make sure this always exists to prevent PHP warnings.
		$job_item_code_map = [ TTUUID::getZeroID() => TTUUID::getZeroID() ]; //Make sure this always exists to prevent PHP warnings.
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jlf = TTnew( 'JobListFactory' ); /** @var JobListFactory $jlf */
			//Get Job ID to Job Code mapping
			$jlf->getByCompanyId( $this->getUserObject()->getCompany() );
			if ( $jlf->getRecordCount() > 0 ) {
				foreach ( $jlf as $j_obj ) {
					$job_code_map[$j_obj->getId()] = $j_obj;
				}
			}

			$jilf = TTnew( 'JobItemListFactory' ); /** @var JobItemListFactory $jilf */
			//Get Job ID to Job Code mapping
			$jilf->getByCompanyId( $this->getUserObject()->getCompany() );
			if ( $jilf->getRecordCount() > 0 ) {
				foreach ( $jilf as $ji_obj ) {
					$job_item_code_map[$ji_obj->getId()] = $ji_obj;
				}
			}
		}

		//Merge time data with user data
		$key = 0;
		if ( isset( $this->tmp_data['pay_stub_entry'] ) ) {
			foreach ( $this->tmp_data['pay_stub_entry'] as $user_id => $level_1 ) {
				if ( isset( $this->tmp_data['user'][$user_id] ) ) {
					foreach ( $level_1 as $date_stamp => $level_2 ) {
						foreach ( $level_2 as $row ) {
							$replace_arr = [
								//*NOTE*: If this changes you must change numeric indexes in calcPercentDistribution().
								( is_object( $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']] ) ) ? $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']]->getManualID() : null,
								( is_object( $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']] ) ) ? $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']]->getOtherID1() : null,
								( is_object( $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']] ) ) ? $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']]->getOtherID2() : null,
								( is_object( $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']] ) ) ? $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']]->getOtherID3() : null,
								( is_object( $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']] ) ) ? $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']]->getOtherID4() : null,
								( is_object( $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']] ) ) ? $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']]->getOtherID5() : null,

								( is_object( $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']] ) ) ? $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']]->getManualID() : null,
								( is_object( $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']] ) ) ? $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']]->getOtherID1() : null,
								( is_object( $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']] ) ) ? $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']]->getOtherID2() : null,
								( is_object( $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']] ) ) ? $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']]->getOtherID3() : null,
								( is_object( $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']] ) ) ? $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']]->getOtherID4() : null,
								( is_object( $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']] ) ) ? $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']]->getOtherID5() : null,

								( is_object( $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']] ) ) ? $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']]->getManualID() : null, //'#default_job#',
								( is_object( $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']] ) ) ? $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']]->getOtherID1() : null, //'#default_job_other_id1#',
								( is_object( $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']] ) ) ? $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']]->getOtherID2() : null, //'#default_job_other_id2#',
								( is_object( $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']] ) ) ? $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']]->getOtherID3() : null, //'#default_job_other_id3#',
								( is_object( $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']] ) ) ? $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']]->getOtherID4() : null, //'#default_job_other_id4#',
								( is_object( $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']] ) ) ? $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']]->getOtherID5() : null, //'#default_job_other_id5#',

								( is_object( $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']] ) ) ? $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']]->getManualID() : null, //'#default_job_item#',
								( is_object( $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']] ) ) ? $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']]->getOtherID1() : null, //'#default_job_item_other_id1#',
								( is_object( $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']] ) ) ? $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']]->getOtherID2() : null, //'#default_job_item_other_id2#',
								( is_object( $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']] ) ) ? $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']]->getOtherID3() : null, //'#default_job_item_other_id3#',
								( is_object( $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']] ) ) ? $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']]->getOtherID4() : null, //'#default_job_item_other_id4#',
								( is_object( $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']] ) ) ? $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']]->getOtherID5() : null, //'#default_job_item_other_id5#',

								( is_object( $title_code_map[$this->tmp_data['user'][$user_id]['title_id']] ) ) ? $title_code_map[$this->tmp_data['user'][$user_id]['title_id']]->getOtherID1() : null,
								( is_object( $title_code_map[$this->tmp_data['user'][$user_id]['title_id']] ) ) ? $title_code_map[$this->tmp_data['user'][$user_id]['title_id']]->getOtherID2() : null,
								( is_object( $title_code_map[$this->tmp_data['user'][$user_id]['title_id']] ) ) ? $title_code_map[$this->tmp_data['user'][$user_id]['title_id']]->getOtherID3() : null,
								( is_object( $title_code_map[$this->tmp_data['user'][$user_id]['title_id']] ) ) ? $title_code_map[$this->tmp_data['user'][$user_id]['title_id']]->getOtherID4() : null,
								( is_object( $title_code_map[$this->tmp_data['user'][$user_id]['title_id']] ) ) ? $title_code_map[$this->tmp_data['user'][$user_id]['title_id']]->getOtherID5() : null,

								//Use default branch as punch branch in case the employee does not punch in/out at all during the pay period.
								//This allows a fallback to default branch if its set.
								//29
								( is_object( $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']] ) ) ? $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']]->getManualID() : null, //'#punch_branch#',
								( is_object( $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']] ) ) ? $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']]->getOtherID1() : null, //'#punch_branch_other_id1#',
								( is_object( $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']] ) ) ? $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']]->getOtherID2() : null, //'#punch_branch_other_id2#',
								( is_object( $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']] ) ) ? $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']]->getOtherID3() : null, //'#punch_branch_other_id3#',
								( is_object( $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']] ) ) ? $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']]->getOtherID4() : null, //'#punch_branch_other_id4#',
								( is_object( $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']] ) ) ? $branch_code_map[$this->tmp_data['user'][$user_id]['default_branch_id']]->getOtherID5() : null, //'#punch_branch_other_id5#',

								//35
								( is_object( $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']] ) ) ? $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']]->getManualID() : null, //'#punch_department#',
								( is_object( $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']] ) ) ? $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']]->getOtherID1() : null, //'#punch_department_other_id1#',
								( is_object( $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']] ) ) ? $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']]->getOtherID2() : null, //'#punch_department_other_id2#',
								( is_object( $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']] ) ) ? $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']]->getOtherID3() : null, //'#punch_department_other_id3#',
								( is_object( $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']] ) ) ? $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']]->getOtherID4() : null, //'#punch_department_other_id4#',
								( is_object( $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']] ) ) ? $department_code_map[$this->tmp_data['user'][$user_id]['default_department_id']]->getOtherID5() : null, //'#punch_department_other_id5#',

								//41
								( is_object( $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']] ) ) ? $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']]->getManualID() : null, //'#punch_job#',
								( is_object( $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']] ) ) ? $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']]->getOtherID1() : null, //'#punch_job_other_id1#',
								( is_object( $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']] ) ) ? $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']]->getOtherID2() : null, //'#punch_job_other_id2#',
								( is_object( $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']] ) ) ? $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']]->getOtherID3() : null, //'#punch_job_other_id3#',
								( is_object( $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']] ) ) ? $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']]->getOtherID4() : null, //'#punch_job_other_id4#',
								( is_object( $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']] ) ) ? $job_code_map[$this->tmp_data['user'][$user_id]['default_job_id']]->getOtherID5() : null, //'#punch_job_other_id5#',

								//47
								( is_object( $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']] ) ) ? $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']]->getManualID() : null, //'#punch_job_item#',
								( is_object( $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']] ) ) ? $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']]->getOtherID1() : null, //'#punch_job_item_other_id1#',
								( is_object( $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']] ) ) ? $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']]->getOtherID2() : null, //'#punch_job_item_other_id2#',
								( is_object( $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']] ) ) ? $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']]->getOtherID3() : null, //'#punch_job_item_other_id3#',
								( is_object( $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']] ) ) ? $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']]->getOtherID4() : null, //'#punch_job_item_other_id4#',
								( is_object( $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']] ) ) ? $job_item_code_map[$this->tmp_data['user'][$user_id]['default_job_item_id']]->getOtherID5() : null, //'#punch_job_item_other_id5#',

								( isset( $this->tmp_data['user'][$user_id]['employee_number'] ) ) ? $this->tmp_data['user'][$user_id]['employee_number'] : null,
								( isset( $this->tmp_data['user'][$user_id]['other_id1'] ) ) ? $this->tmp_data['user'][$user_id]['other_id1'] : null,
								( isset( $this->tmp_data['user'][$user_id]['other_id2'] ) ) ? $this->tmp_data['user'][$user_id]['other_id2'] : null,
								( isset( $this->tmp_data['user'][$user_id]['other_id3'] ) ) ? $this->tmp_data['user'][$user_id]['other_id3'] : null,
								( isset( $this->tmp_data['user'][$user_id]['other_id4'] ) ) ? $this->tmp_data['user'][$user_id]['other_id4'] : null,
								( isset( $this->tmp_data['user'][$user_id]['other_id5'] ) ) ? $this->tmp_data['user'][$user_id]['other_id5'] : null,
							];

							$date_columns = TTDate::getReportDates( 'transaction', $date_stamp, false, $this->getUserObject(), [ 'pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date'] ] );
							$processed_data = [
								//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
								//'pay_stub' => array('sort' => $row['pay_stub_transaction_date'], 'display' => TTDate::getDate('DATE', $row['pay_stub_transaction_date'] ) ),
							];

							if ( isset( $row['psen_ids'] ) && is_array( $row['psen_ids'] ) ) {
								$psen_ids = $row['psen_ids'];
								unset( $row['psen_ids'] );
								foreach ( $psen_ids as $psen_data ) {
									if ( $this->enable_time_based_distribution == true ) {
										//Debug::Text('     TimeBased Distribution...', __FILE__, __LINE__, __METHOD__, 10);
										if ( !isset( $this->tmp_data['pay_period_distribution'][$user_id][$row['pay_period_id']] ) ) {
											$this->tmp_data['pay_period_distribution'][$user_id][$row['pay_period_id']] = [];
										}
										$expanded_gl_rows = $this->expandGLAccountRows( $psen_data, $this->tmp_data['pay_period_distribution'][$user_id][$row['pay_period_id']], $branch_code_map, $department_code_map, $job_code_map, $job_item_code_map, $replace_arr );
										if ( is_array( $expanded_gl_rows ) && count( $expanded_gl_rows ) > 0 ) {
											//Debug::Arr($expanded_gl_rows, '       Expanded GL Rows...', __FILE__, __LINE__, __METHOD__, 10);
											foreach ( $expanded_gl_rows as $gl_row ) {
												$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data, $gl_row );
											}
										} else {
											Debug::Text( '       NO Expanded GL Rows...', __FILE__, __LINE__, __METHOD__, 10 );
										}
										unset( $expanded_gl_rows, $psen_data );
									} else {
										//Debug::Text('     NO TimeBased Distribution...', __FILE__, __LINE__, __METHOD__, 10);
										if ( isset( $psen_data['account'] ) ) {
											$psen_data['account'] = $this->replaceGLAccountVariables( $psen_data['account'], $replace_arr );
										}
										//Need to make sure PSEA IDs are strings not numeric otherwise array_merge will re-key them.
										$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data, $psen_data );
									}
								}
								unset( $psen_ids );
							}

							$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
							$key++;
						}
					}
				}
			}
			unset( $this->tmp_data, $row, $date_columns, $processed_data, $level_1 );
		}

		$this->form_data = $this->data; //Used for exporting.

		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return true;
	}

	/**
	 * @param $type
	 * @param $psen_data
	 * @param $distribution_arr
	 * @param $branch_code_map
	 * @param $department_code_map
	 * @param $job_code_map
	 * @param $job_item_code_map
	 * @param $tmp_replace_arr
	 * @return array|bool
	 */
	function calcPercentDistribution( $type, $psen_data, $distribution_arr, $branch_code_map, $department_code_map, $job_code_map, $job_item_code_map, $tmp_replace_arr ) {
		$amount_distribution_arr = Misc::PercentDistribution( $psen_data[$type . '_amount'], $distribution_arr );
		//Debug::Arr($amount_distribution_arr, $type .' PSEN Distribution Arr: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array( $amount_distribution_arr ) ) {
			$retarr = [];

			foreach ( $amount_distribution_arr as $key => $amount ) {
				if ( $amount == 0 ) {
					continue;
				}

				//If key=0-0-0-0 comes after any non-zero key, it will use the account code from the previous key, as its not ID=0 is not replaced.
				//Therefore deep copy replace_arr each iteration, so it starts fresh.
				$replace_arr = $tmp_replace_arr;

				$account_arr = explode( ':', $key );

				if ( isset( $account_arr[0] ) ) { //Branch
					//Was 17
					$replace_arr[29] = ( is_object( $branch_code_map[$account_arr[0]] ) ) ? $branch_code_map[$account_arr[0]]->getManualID() : null;
					$replace_arr[30] = ( is_object( $branch_code_map[$account_arr[0]] ) ) ? $branch_code_map[$account_arr[0]]->getOtherID1() : null;
					$replace_arr[31] = ( is_object( $branch_code_map[$account_arr[0]] ) ) ? $branch_code_map[$account_arr[0]]->getOtherID2() : null;
					$replace_arr[32] = ( is_object( $branch_code_map[$account_arr[0]] ) ) ? $branch_code_map[$account_arr[0]]->getOtherID3() : null;
					$replace_arr[33] = ( is_object( $branch_code_map[$account_arr[0]] ) ) ? $branch_code_map[$account_arr[0]]->getOtherID4() : null;
					$replace_arr[34] = ( is_object( $branch_code_map[$account_arr[0]] ) ) ? $branch_code_map[$account_arr[0]]->getOtherID5() : null;
				}

				if ( isset( $account_arr[1] ) && $account_arr[1] != TTUUID::getZeroID() ) { //Department
					//Was 23
					$replace_arr[35] = ( is_object( $department_code_map[$account_arr[1]] ) ) ? $department_code_map[$account_arr[1]]->getManualID() : null;
					$replace_arr[36] = ( is_object( $department_code_map[$account_arr[1]] ) ) ? $department_code_map[$account_arr[1]]->getOtherID1() : null;
					$replace_arr[37] = ( is_object( $department_code_map[$account_arr[1]] ) ) ? $department_code_map[$account_arr[1]]->getOtherID2() : null;
					$replace_arr[38] = ( is_object( $department_code_map[$account_arr[1]] ) ) ? $department_code_map[$account_arr[1]]->getOtherID3() : null;
					$replace_arr[39] = ( is_object( $department_code_map[$account_arr[1]] ) ) ? $department_code_map[$account_arr[1]]->getOtherID4() : null;
					$replace_arr[40] = ( is_object( $department_code_map[$account_arr[1]] ) ) ? $department_code_map[$account_arr[1]]->getOtherID5() : null;
				}

				if ( isset( $account_arr[2] ) && $account_arr[2] != TTUUID::getZeroID() ) { //Job
					//Was 29
					$replace_arr[41] = ( is_object( $job_code_map[$account_arr[2]] ) ) ? $job_code_map[$account_arr[2]]->getManualID() : null;
					$replace_arr[42] = ( is_object( $job_code_map[$account_arr[2]] ) ) ? $job_code_map[$account_arr[2]]->getOtherID1() : null;
					$replace_arr[43] = ( is_object( $job_code_map[$account_arr[2]] ) ) ? $job_code_map[$account_arr[2]]->getOtherID2() : null;
					$replace_arr[44] = ( is_object( $job_code_map[$account_arr[2]] ) ) ? $job_code_map[$account_arr[2]]->getOtherID3() : null;
					$replace_arr[45] = ( is_object( $job_code_map[$account_arr[2]] ) ) ? $job_code_map[$account_arr[2]]->getOtherID4() : null;
					$replace_arr[46] = ( is_object( $job_code_map[$account_arr[2]] ) ) ? $job_code_map[$account_arr[2]]->getOtherID5() : null;
				}

				if ( isset( $account_arr[3] ) && $account_arr[3] != TTUUID::getZeroID() ) { //Job Item
					//Was 35
					$replace_arr[47] = ( is_object( $job_item_code_map[$account_arr[3]] ) ) ? $job_item_code_map[$account_arr[3]]->getManualID() : null;
					$replace_arr[48] = ( is_object( $job_item_code_map[$account_arr[3]] ) ) ? $job_item_code_map[$account_arr[3]]->getOtherID1() : null;
					$replace_arr[49] = ( is_object( $job_item_code_map[$account_arr[3]] ) ) ? $job_item_code_map[$account_arr[3]]->getOtherID2() : null;
					$replace_arr[50] = ( is_object( $job_item_code_map[$account_arr[3]] ) ) ? $job_item_code_map[$account_arr[3]]->getOtherID3() : null;
					$replace_arr[51] = ( is_object( $job_item_code_map[$account_arr[3]] ) ) ? $job_item_code_map[$account_arr[3]]->getOtherID4() : null;
					$replace_arr[52] = ( is_object( $job_item_code_map[$account_arr[3]] ) ) ? $job_item_code_map[$account_arr[3]]->getOtherID5() : null;
				}

				$retarr[] = [
						'account'       => $this->replaceGLAccountVariables( $psen_data['account'], $replace_arr ),
						'debit_amount'  => ( $type == 'debit' ) ? $amount : null,
						'credit_amount' => ( $type == 'credit' ) ? $amount : null,
				];
			}

			//Debug::Arr($retarr, $type .' PSEN Distribution Retarr: ', __FILE__, __LINE__, __METHOD__, 10);
			return $retarr;
		}

		return false;
	}

	/**
	 * Checks to see if a GL Account contains any "Punch" variables, and expands based on them.
	 * @param $psen_data
	 * @param $distribution_arr
	 * @param $branch_code_map
	 * @param $department_code_map
	 * @param $job_code_map
	 * @param $job_item_code_map
	 * @param $replace_arr
	 * @return array
	 */
	function expandGLAccountRows( $psen_data, $distribution_arr, $branch_code_map, $department_code_map, $job_code_map, $job_item_code_map, $replace_arr ) {
		//Debug::Arr($psen_data, 'PSEN Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($distribution_arr, 'Distribution Arr ', __FILE__, __LINE__, __METHOD__, 10);

		if ( isset( $psen_data['account'] ) ) {
			if ( strpos( $psen_data['account'], 'punch' ) !== false ) {
				//Expand account based on percent distribution.
				Debug::Text( 'Found punch distribution variables...', __FILE__, __LINE__, __METHOD__, 10 );

				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL && is_array( $distribution_arr ) && count( $distribution_arr ) > 0 ) {
					$retarr = array_merge(
							$this->calcPercentDistribution( 'credit', $psen_data, $distribution_arr, $branch_code_map, $department_code_map, $job_code_map, $job_item_code_map, $replace_arr ),
							$this->calcPercentDistribution( 'debit', $psen_data, $distribution_arr, $branch_code_map, $department_code_map, $job_code_map, $job_item_code_map, $replace_arr )
					);
				} else {
					Debug::Text( '  No distribution data, available...', __FILE__, __LINE__, __METHOD__, 10 );
					//Still need to replace the variables.
					$psen_data['account'] = $this->replaceGLAccountVariables( $psen_data['account'], $replace_arr );
					$retarr = [ 0 => $psen_data ];
				}

				//Debug::Arr($retarr, 'Expanded GL Rows RetArr: ', __FILE__, __LINE__, __METHOD__, 10);
				return $retarr;
			} else {
				//Still need to replace the variables.
				$psen_data['account'] = $this->replaceGLAccountVariables( $psen_data['account'], $replace_arr );
			}
		}

		return [ 0 => $psen_data ];
	}

	/**
	 * @param $subject
	 * @param null $replace_arr
	 * @return bool|mixed|string
	 */
	function replaceGLAccountVariables( $subject, $replace_arr = null ) {
		$search_arr = [
			//*NOTE*: If this changes you must change numeric indexes in calcPercentDistribution().
			'#default_branch#',
			'#default_branch_other_id1#',
			'#default_branch_other_id2#',
			'#default_branch_other_id3#',
			'#default_branch_other_id4#',
			'#default_branch_other_id5#',

			'#default_department#',
			'#default_department_other_id1#',
			'#default_department_other_id2#',
			'#default_department_other_id3#',
			'#default_department_other_id4#',
			'#default_department_other_id5#',

			'#default_job#',
			'#default_job_other_id1#',
			'#default_job_other_id2#',
			'#default_job_other_id3#',
			'#default_job_other_id4#',
			'#default_job_other_id5#',
			'#default_job_item#',
			'#default_job_item_other_id1#',
			'#default_job_item_other_id2#',
			'#default_job_item_other_id3#',
			'#default_job_item_other_id4#',
			'#default_job_item_other_id5#',

			'#title_other_id1#',
			'#title_other_id2#',
			'#title_other_id3#',
			'#title_other_id4#',
			'#title_other_id5#',

			'#punch_branch#',
			'#punch_branch_other_id1#',
			'#punch_branch_other_id2#',
			'#punch_branch_other_id3#',
			'#punch_branch_other_id4#',
			'#punch_branch_other_id5#',
			'#punch_department#',
			'#punch_department_other_id1#',
			'#punch_department_other_id2#',
			'#punch_department_other_id3#',
			'#punch_department_other_id4#',
			'#punch_department_other_id5#',

			'#punch_job#',
			'#punch_job_other_id1#',
			'#punch_job_other_id2#',
			'#punch_job_other_id3#',
			'#punch_job_other_id4#',
			'#punch_job_other_id5#',
			'#punch_job_item#',
			'#punch_job_item_other_id1#',
			'#punch_job_item_other_id2#',
			'#punch_job_item_other_id3#',
			'#punch_job_item_other_id4#',
			'#punch_job_item_other_id5#',

			'#employee_number#',
			'#employee_other_id1#',
			'#employee_other_id2#',
			'#employee_other_id3#',
			'#employee_other_id4#',
			'#employee_other_id5#',
		];

		if ( $subject != '' && is_array( $replace_arr ) ) {
			$subject = str_replace( $search_arr, $replace_arr, $subject );
		}

		//Handle cases where variables are replaced with nothing or invalid values.
		//5010--99
		$subject = str_replace( '--', '-', $subject );

		//-5010-99
		//5010-99-
		//-5010-99-
		if ( substr( $subject, 0, 1 ) == '-' ) {
			$subject = substr( $subject, 1 );
		}
		if ( substr( $subject, -1 ) == '-' ) {
			$subject = substr( $subject, 0, -1 );
		}

		return $subject;
	}

	/**
	 * @param $format
	 * @return array
	 * @noinspection PhpUnusedLocalVariableInspection
	 * @noinspection PhpUndefinedVariableInspection
	 */
	function _outputExportGeneralLedger( $format ) {
		Debug::Text( 'Generating GL export for Format: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );

		//Calculate sub-total so we know where the journal entries start/stop.

		$enable_grouping = false;
		if ( is_array( $this->formatGroupConfig() ) && count( $this->formatGroupConfig() ) > 0 ) {
			Debug::Arr( $this->formatGroupConfig(), 'Group Config: ', __FILE__, __LINE__, __METHOD__, 10 );
			$enable_grouping = true;
		}

		$file_name = 'no_data.txt';
		$data = null;

		if ( is_array( $this->form_data ) ) {
			//Need to group the exported data so the number of journal entries can be reduced.
			$this->form_data = Group::GroupBy( $this->form_data, $this->formatGroupConfig() );
			$this->form_data = Sort::arrayMultiSort( $this->form_data, $this->getSortConfig() );

			$gle = new GeneralLedgerExport();
			$gle->setFileFormat( $format );
			if ( strtolower( $format ) == 'csv' || strtolower( $format ) == 'export_csv' ) {
				$ignore_balance_check = true; //Dont be so strict on the balance, there may be cases where they just want the data out, even if it doesn't balance for other purposes.
			} else {
				$ignore_balance_check = false; //Dont be so strict on the balance, there may be cases where they just want the data out, even if it doesn't balance for other purposes.
			}

			$prev_group_key = null;
			$i = 0;
			foreach ( $this->form_data as $row ) {
				if ( !isset( $row['account'] ) ) { //If the user didn't include the Account column, skip that row completely.
					continue;
				}

				$group_key = 0;
				if ( $enable_grouping == true ) {
					$comment = [];
					foreach ( $this->formatGroupConfig() as $group_column => $group_agg ) {
						if ( is_int( $group_agg ) && isset( $row[$group_column] ) && $group_column != 'account' ) {
							if ( is_array( $row[$group_column] ) && isset( $row[$group_column]['display'] ) ) {
								$comment[] = $row[$group_column]['display'];
								$group_key .= crc32( $row[$group_column]['display'] );
							} else if ( $row[$group_column] != '' ) {
								$comment[] = $row[$group_column];
								$group_key .= $row[$group_column];
							}
						} else {
							$group_key .= 0;
						}
					}
					unset( $group_column, $group_agg );
				}
				//Debug::Arr($row, 'GL Export Row: Group Key: "'. $group_key .'" Prev Group Key: "'. $prev_group_key .'"', __FILE__, __LINE__, __METHOD__, 10);

				if ( $prev_group_key === null || $prev_group_key != $group_key ) {
					if ( $i > 0 && is_object( $je ) ) {
						Debug::Text( 'Ending previous JE: Group Key: ' . $group_key, __FILE__, __LINE__, __METHOD__, 10 );
						$gle->setJournalEntry( $je ); //Add previous JE before starting a new one.
					}

					Debug::Text( 'Starting new JE: Group Key: ' . $group_key, __FILE__, __LINE__, __METHOD__, 10 );

					$je = new GeneralLedgerExport_JournalEntry( $ignore_balance_check );
					if ( isset( $row['pay_stub_transaction_date'] ) ) {
						$je->setDate( $row['pay_stub_transaction_date'] );
					} else if ( isset( $row['transaction-date_stamp'] ) ) {
						$je->setDate( TTDate::parseDateTime( $row['transaction-date_stamp'] ) );
					} else {
						$je->setDate( time() );
					}

					$je->setSource( APPLICATION_NAME );

					if ( isset( $comment ) && is_array( $comment ) && count( $comment ) > 0 ) {
						$je->setComment( implode( ' ', $comment ) );
					} else {
						$je->setComment( TTi18n::getText( 'Payroll' ) );
					}
				}

				if ( isset( $row['debit_amount'] ) && $row['debit_amount'] > 0 ) {
					Debug::Text( 'Adding Debit Record for: ' . $row['debit_amount'], __FILE__, __LINE__, __METHOD__, 10 );
					$record = new GeneralLedgerExport_Record( $ignore_balance_check );
					$record->setAccount( $row['account'] );
					$record->setType( 'debit' );
					$record->setAmount( $row['debit_amount'] );
					$je->setRecord( $record );
				}
				if ( isset( $row['credit_amount'] ) && $row['credit_amount'] > 0 ) {
					Debug::Text( 'Adding Credit Record for: ' . $row['credit_amount'], __FILE__, __LINE__, __METHOD__, 10 );
					$record = new GeneralLedgerExport_Record( $ignore_balance_check );
					$record->setAccount( $row['account'] );
					$record->setType( 'credit' );
					$record->setAmount( $row['credit_amount'] );
					$je->setRecord( $record );
				}
				unset( $record );

				$prev_group_key = $group_key;
				$i++;
			}
			if ( isset( $je ) && is_object( $je ) ) {
				$gle->setJournalEntry( $je ); //Handle last JE here
			}

			if ( $gle->compile() == true ) {
				$data = $gle->getCompiledData();
				Debug::Text( 'Exporting as: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );

				if ( $format == 'simply' ) {
					$file_name = 'general_ledger_' . str_replace( [ '/', ',', ' ' ], '_', TTDate::getDate( 'DATE', time() ) ) . '.txt';
				} else if ( $format == 'quickbooks' ) {
					$file_name = 'general_ledger_' . str_replace( [ '/', ',', ' ' ], '_', TTDate::getDate( 'DATE', time() ) ) . '.iif';
				} else {
					$file_name = 'general_ledger_' . str_replace( [ '/', ',', ' ' ], '_', TTDate::getDate( 'DATE', time() ) ) . '.csv';
				}

				return [ 'file_name' => $file_name, 'mime_type' => 'application/text', 'data' => $data ];
			} else {
				return [
						'api_retval'  => false,
						'api_details' => [
								'code'        => 'VALIDATION',
								'description' => TTi18n::getText( 'ERROR: Journal entries do not balance' ) . ":<br><br>" . implode( "<br>\n", $gle->journal_entry_error_msgs ),
						],
				];
			}
		}

		return [
				'api_retval'  => false,
				'api_details' => [
						'code'        => 'VALIDATION',
						'description' => TTi18n::getText( 'ERROR: No data matches criteria.' ),
				],
		];
	}

	/**
	 * @param null $format
	 * @return array|bool
	 */
	function _output( $format = null ) {
		$psf = TTnew( 'PayStubFactory' ); /** @var PayStubFactory $psf */
		$export_type_options = Misc::trimSortPrefix( $psf->getOptions( 'export_general_ledger' ) );
		Debug::Arr( $export_type_options, 'Format: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );
		if ( isset( $export_type_options[$format] ) ) {
			return $this->_outputExportGeneralLedger( $format );
		} else {
			return parent::_output( $format );
		}
	}
}

?>
