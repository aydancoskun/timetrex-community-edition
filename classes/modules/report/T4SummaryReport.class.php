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
class T4SummaryReport extends Report {

	protected $user_ids = [];

	/**
	 * T4SummaryReport constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText( 'T4 Summary Report' );
		$this->file_name = 't4_summary';

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
				&& $this->getPermissionObject()->Check( 'report', 'view_t4_summary', $user_id, $company_id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $name
	 * @param null $params
	 * @return array|bool|null
	 */
	protected function _getOptions( $name, $params = null ) {
		$retval = null;
		switch ( $name ) {
			case 'output_format':
				$retval = array_merge( parent::getOptions( 'default_output_format' ),
									   [
											   '-1100-pdf_form'            => TTi18n::gettext( 'Employee (One Employee/Page)' ),
											   '-1110-pdf_form_government' => TTi18n::gettext( 'Government (Multiple Employees/Page)' ),
											   '-1120-efile_xml'           => TTi18n::gettext( 'eFile' ),
									   ]
				);
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
					'-2100-custom_filter'         => TTi18n::gettext( 'Custom Filter' ),

					'-5000-columns'   => TTi18n::gettext( 'Display Columns' ),
					'-5010-group'     => TTi18n::gettext( 'Group By' ),
					'-5020-sub_total' => TTi18n::gettext( 'SubTotal By' ),
					'-5030-sort'      => TTi18n::gettext( 'Sort By' ),
				];
				break;
			case 'time_period':
				$retval = TTDate::getTimePeriodOptions( false ); //Exclude Pay Period options.
				break;
			case 'date_columns':
				//$retval = TTDate::getReportDateOptions( NULL, TTi18n::getText('Date'), 13, TRUE );
				$retval = [];
				break;
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), null, 'T4SummaryReport', 'custom_column' );
					if ( is_array( $custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'filter_column_type_ids' ), null, 'T4SummaryReport', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'dynamic_format_ids' ), 'T4SummaryReport', 'custom_column' );
					if ( is_array( $report_dynamic_custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'static_format_ids' ), 'T4SummaryReport', 'custom_column' );
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
					'-0900-legal_entity_legal_name' => TTi18n::gettext( 'Legal Entity Name' ),
					'-0910-legal_entity_trade_name' => TTi18n::gettext( 'Legal Entity Trade Name' ),

					'-1000-first_name'         => TTi18n::gettext( 'First Name' ),
					'-1001-middle_name'        => TTi18n::gettext( 'Middle Name' ),
					'-1002-last_name'          => TTi18n::gettext( 'Last Name' ),
					'-1005-full_name'          => TTi18n::gettext( 'Full Name' ),
					'-1030-employee_number'    => TTi18n::gettext( 'Employee #' ),
					'-1035-sin'                => TTi18n::gettext( 'SIN/SSN' ),
					'-1040-status'             => TTi18n::gettext( 'Status' ),
					'-1050-title'              => TTi18n::gettext( 'Title' ),
					'-1060-province'           => TTi18n::gettext( 'Province/State' ),
					'-1070-country'            => TTi18n::gettext( 'Country' ),
					'-1080-group'              => TTi18n::gettext( 'Group' ),
					'-1090-default_branch'     => TTi18n::gettext( 'Default Branch' ),
					'-1100-default_department' => TTi18n::gettext( 'Default Department' ),
					'-1110-currency'           => TTi18n::gettext( 'Currency' ),
					//'-1111-current_currency' => TTi18n::gettext('Current Currency'),

					//'-1110-verified_time_sheet' => TTi18n::gettext('Verified TimeSheet'),
					//'-1120-pending_request' => TTi18n::gettext('Pending Requests'),

					//Handled in date_columns above.
					//'-1450-pay_period' => TTi18n::gettext('Pay Period'),

					'-1400-permission_control'  => TTi18n::gettext( 'Permission Group' ),
					'-1410-pay_period_schedule' => TTi18n::gettext( 'Pay Period Schedule' ),
					'-1420-policy_group'        => TTi18n::gettext( 'Policy Group' ),
				];

				$retval = array_merge( $retval, $this->getOptions( 'date_columns' ), (array)$this->getOptions( 'report_static_custom_column' ) );
				ksort( $retval );
				break;
			case 'dynamic_columns':
				$retval = [
					//Dynamic - Aggregate functions can be used
					'-2100-income'             => TTi18n::gettext( 'Income (14)' ),
					'-2110-tax'                => TTi18n::gettext( 'Income Tax (22)' ),
					'-2120-employee_cpp'       => TTi18n::gettext( 'Employee CPP (16)' ),
					'-2125-ei_earnings'        => TTi18n::gettext( 'EI Insurable Earnings (24)' ),
					'-2126-cpp_earnings'       => TTi18n::gettext( 'CPP Pensionable Earnings (26)' ),
					'-2130-employee_ei'        => TTi18n::gettext( 'Employee EI (18)' ),
					'-2140-union_dues'         => TTi18n::gettext( 'Union Dues (44)' ),
					'-2150-employer_cpp'       => TTi18n::gettext( 'Employer CPP' ),
					'-2160-employer_ei'        => TTi18n::gettext( 'Employer EI' ),
					'-2170-rpp'                => TTi18n::gettext( 'RPP Contributions (20)' ),
					'-2180-charity'            => TTi18n::gettext( 'Charity Donations (46)' ),
					'-2190-pension_adjustment' => TTi18n::gettext( 'Pension Adjustment (52)' ),
					'-2200-other_box_0'        => TTi18n::gettext( 'Other Box 1' ),
					'-2210-other_box_1'        => TTi18n::gettext( 'Other Box 2' ),
					'-2220-other_box_2'        => TTi18n::gettext( 'Other Box 3' ),
					'-2220-other_box_3'        => TTi18n::gettext( 'Other Box 4' ),
					'-2220-other_box_4'        => TTi18n::gettext( 'Other Box 5' ),
					'-2220-other_box_5'        => TTi18n::gettext( 'Other Box 6' ),
				];
				break;
			case 'columns':
				$retval = array_merge( $this->getOptions( 'static_columns' ), $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_dynamic_custom_column' ) );
				ksort( $retval );
				break;
			case 'column_format':
				//Define formatting function for each column.
				$columns = array_merge( $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_custom_column' ) );
				if ( is_array( $columns ) ) {
					foreach ( $columns as $column => $name ) {
						$retval[$column] = 'currency';
					}
				}
				break;
			case 'aggregates':
				$retval = [];
				$dynamic_columns = array_keys( Misc::trimSortPrefix( array_merge( $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_dynamic_custom_column' ) ) ) );
				if ( is_array( $dynamic_columns ) ) {
					foreach ( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								$retval[$column] = 'sum';
						}
					}
				}

				break;
			case 'type':
				$retval = [
						'-1010-O' => TTi18n::getText( 'Original' ),
						'-1020-A' => TTi18n::getText( 'Amended' ),
						'-1030-C' => TTi18n::getText( 'Cancel' ),
				];
				break;
			case 'templates':
				$retval = [
					//'-1010-by_month' => TTi18n::gettext('by Month'),
					'-1020-by_employee'             => TTi18n::gettext( 'by Employee' ),
					'-1030-by_branch'               => TTi18n::gettext( 'by Branch' ),
					'-1040-by_department'           => TTi18n::gettext( 'by Department' ),
					'-1050-by_branch_by_department' => TTi18n::gettext( 'by Branch/Department' ),

					//'-1060-by_month_by_employee' => TTi18n::gettext('by Month/Employee'),
					//'-1070-by_month_by_branch' => TTi18n::gettext('by Month/Branch'),
					//'-1080-by_month_by_department' => TTi18n::gettext('by Month/Department'),
					//'-1090-by_month_by_branch_by_department' => TTi18n::gettext('by Month/Branch/Department'),
				];

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset( $template ) && $template != '' ) {
					switch ( $template ) {
						case 'default':
							//Proper settings to generate the form.
							//$retval['-1010-time_period']['time_period'] = 'last_quarter';

							$retval['columns'] = $this->getOptions( 'columns' );

							$retval['group'][] = 'date_quarter_month';

							$retval['sort'][] = [ 'date_quarter_month' => 'asc' ];

							$retval['other']['grand_total'] = true;

							break;
						default:
							Debug::Text( ' Parsing template name: ' . $template, __FILE__, __LINE__, __METHOD__, 10 );
							$retval['columns'] = [];
							$retval['-1010-time_period']['time_period'] = 'last_year';

							//Parse template name, and use the keywords separated by '+' to determine settings.
							$template_keywords = explode( '+', $template );
							if ( is_array( $template_keywords ) ) {
								foreach ( $template_keywords as $template_keyword ) {
									Debug::Text( ' Keyword: ' . $template_keyword, __FILE__, __LINE__, __METHOD__, 10 );

									switch ( $template_keyword ) {
										//Columns

										//Filter
										//Group By
										//SubTotal
										//Sort
										case 'by_month':
											$retval['columns'][] = 'date_month';

											$retval['group'][] = 'date_month';

											$retval['sort'][] = [ 'date_month' => 'asc' ];
											break;
										case 'by_employee':
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';

											$retval['sort'][] = [ 'last_name' => 'asc' ];
											$retval['sort'][] = [ 'first_name' => 'asc' ];
											break;
										case 'by_branch':
											$retval['columns'][] = 'default_branch';

											$retval['group'][] = 'default_branch';

											$retval['sort'][] = [ 'default_branch' => 'asc' ];
											break;
										case 'by_department':
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'default_department';

											$retval['sort'][] = [ 'default_department' => 'asc' ];
											break;
										case 'by_branch_by_department':
											$retval['columns'][] = 'default_branch';
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'default_branch';
											$retval['group'][] = 'default_department';

											$retval['sub_total'][] = 'default_branch';

											$retval['sort'][] = [ 'default_branch' => 'asc' ];
											$retval['sort'][] = [ 'default_department' => 'asc' ];
											break;
										case 'by_month_by_employee':
											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';

											$retval['sub_total'][] = 'date_month';

											$retval['sort'][] = [ 'date_month' => 'asc' ];
											$retval['sort'][] = [ 'last_name' => 'asc' ];
											$retval['sort'][] = [ 'first_name' => 'asc' ];
											break;
										case 'by_month_by_branch':
											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'default_branch';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'default_branch';

											$retval['sub_total'][] = 'date_month';

											$retval['sort'][] = [ 'date_month' => 'asc' ];
											$retval['sort'][] = [ 'default_branch' => 'asc' ];
											break;
										case 'by_month_by_department':
											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'default_department';

											$retval['sub_total'][] = 'date_month';

											$retval['sort'][] = [ 'date_month' => 'asc' ];
											$retval['sort'][] = [ 'default_department' => 'asc' ];
											break;
										case 'by_month_by_branch_by_department':
											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'default_branch';
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'default_branch';
											$retval['group'][] = 'default_department';

											$retval['sub_total'][] = 'date_month';
											$retval['sub_total'][] = 'default_branch';

											$retval['sort'][] = [ 'date_month' => 'asc' ];
											$retval['sort'][] = [ 'default_branch' => 'asc' ];
											$retval['sort'][] = [ 'default_department' => 'asc' ];
											break;
									}
								}
							}

							$retval['columns'] = array_merge( $retval['columns'], array_keys( Misc::trimSortPrefix( $this->getOptions( 'dynamic_columns' ) ) ) );

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
	 * @return mixed
	 */
	function getFormObject() {
		if ( !isset( $this->form_obj['gf'] ) || !is_object( $this->form_obj['gf'] ) ) {
			//
			//Get all data for the form.
			//
			require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

			$gf = new GovernmentForms();

			$this->form_obj['gf'] = $gf;

			return $this->form_obj['gf'];
		}

		return $this->form_obj['gf'];
	}

	/**
	 * @return bool
	 */
	function clearFormObject() {
		$this->form_obj['gf'] = false;

		return true;
	}

	/**
	 * @return mixed
	 */
	function getT4Object() {
		if ( !isset( $this->form_obj['t4'] ) || !is_object( $this->form_obj['t4'] ) ) {
			$this->form_obj['t4'] = $this->getFormObject()->getFormObject( 'T4', 'CA' );

			return $this->form_obj['t4'];
		}

		return $this->form_obj['t4'];
	}

	/**
	 * @return bool
	 */
	function clearT4Object() {
		$this->form_obj['t4'] = false;

		return true;
	}

	/**
	 * @return mixed
	 */
	function getT4SumObject() {
		if ( !isset( $this->form_obj['t4sum'] ) || !is_object( $this->form_obj['t4sum'] ) ) {
			$this->form_obj['t4sum'] = $this->getFormObject()->getFormObject( 'T4Sum', 'CA' );

			return $this->form_obj['t4sum'];
		}

		return $this->form_obj['t4sum'];
	}

	/**
	 * @return bool
	 */
	function clearT4SumObject() {
		$this->form_obj['t4sum'] = false;

		return true;
	}

	/**
	 * @return mixed
	 */
	function getT619Object() {
		if ( !isset( $this->form_obj['t619'] ) || !is_object( $this->form_obj['t619'] ) ) {
			$this->form_obj['t619'] = $this->getFormObject()->getFormObject( 'T619', 'CA' );

			return $this->form_obj['t619'];
		}

		return $this->form_obj['t619'];
	}

	/**
	 * @return bool
	 */
	function clearT619Object() {
		$this->form_obj['t619'] = false;

		return true;
	}

	/**
	 * @return array
	 */
	function formatFormConfig() {
		$default_include_exclude_arr = [ 'include_pay_stub_entry_account' => [], 'exclude_pay_stub_entry_account' => [] ];

		$default_arr = [
				'income'             => $default_include_exclude_arr,
				'tax'                => $default_include_exclude_arr,
				'employee_cpp'       => $default_include_exclude_arr,
				'employer_cpp'       => $default_include_exclude_arr,
				'employee_ei'        => $default_include_exclude_arr,
				'employer_ei'        => $default_include_exclude_arr,
				'ei_earnings'        => $default_include_exclude_arr,
				'cpp_earnings'       => $default_include_exclude_arr,
				'union_dues'         => $default_include_exclude_arr,
				'rpp'                => $default_include_exclude_arr,
				'charity'            => $default_include_exclude_arr,
				'pension_adjustment' => $default_include_exclude_arr,
				'other_box'          => [],
		];

		$retarr = array_merge( $default_arr, (array)$this->getFormConfig() );

		return $retarr;
	}

	/**
	 * Get raw data for report
	 * @param null $format
	 * @return bool
	 */
	function _getData( $format = null ) {
		$this->tmp_data = [ 'pay_stub_entry' => [], 'user' => [], 'legal_entity' => [] ];

		$filter_data = $this->getFilterConfig();
		$form_data = $this->formatFormConfig();

		//List of COVID employment income periods and the other boxes they populate. This is based on the pay period transaciton date.
		//https://www.canada.ca/en/revenue-agency/campaigns/covid-19-update/support-employers-cra-covid-19.html
		$covid_employment_income_periods = [
				[ 57, TTDate::getBeginDayEpoch( strtotime('15-Mar-2020') ), TTDate::getEndDayEpoch( strtotime('09-May-2020') ) ],
				[ 58, TTDate::getBeginDayEpoch( strtotime('10-May-2020') ), TTDate::getEndDayEpoch( strtotime('04-Jul-2020') ) ],
				[ 59, TTDate::getBeginDayEpoch( strtotime('05-Jul-2020') ), TTDate::getEndDayEpoch( strtotime('29-Aug-2020') ) ],
				[ 60, TTDate::getBeginDayEpoch( strtotime('30-Aug-2020') ), TTDate::getEndDayEpoch( strtotime('26-Sep-2020') ) ],
		];

		foreach ( range( 0, 23 ) as $n ) {
			if ( !isset($form_data['other_box'][$n]) || ( isset($form_data['other_box'][$n]['box']) && trim($form_data['other_box'][$n]['box']) == '' ) ) {
				//Found non-existant or empty other box, start inserting COVID boxes.
				$tmp_covid_period_data = array_shift( $covid_employment_income_periods );

				if ( is_array($tmp_covid_period_data) ) {
					$form_data['other_box'][$n] = $form_data['income']; //Copy Income (Box 14) include/exclude settings.

					$form_data['other_box'][$n]['box'] = $tmp_covid_period_data[0]; //Force box number.
					$form_data['other_box'][$n]['start_date'] = $tmp_covid_period_data[1];
					$form_data['other_box'][$n]['end_date'] = $tmp_covid_period_data[2];
				}
			}
		}
		unset($tmp_covid_period_data);

		//
		//Figure out province/CPP/EI wages/taxes.
		//
		$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
		$cdlf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), [ 10, 20 ], 10 );
		$tax_deductions = [];
		$tax_deduction_users = [];
		$user_deduction_data = [];
		if ( $cdlf->getRecordCount() > 0 ) {
			foreach ( $cdlf as $cd_obj ) {
				if ( in_array( $cd_obj->getCalculation(), [ 200, 90, 91 ] ) ) { //Only consider Province, CPP, EI records.
					Debug::Text( 'Company Deduction: ID: ' . $cd_obj->getID() . ' Name: ' . $cd_obj->getName(), __FILE__, __LINE__, __METHOD__, 10 );
					$tax_deductions[$cd_obj->getId()] = $cd_obj;
					$tax_deduction_users[$cd_obj->getId()] = $cd_obj->getUser(); //Optimization so we don't have to get assigned users more than once per obj, as its used lower down in a tighter loop.

					//Need to determine start/end dates for each CompanyDeduction/User pair, so we can break down total wages earned in the date ranges.
					$udlf = TTnew( 'UserDeductionListFactory' ); /** @var UserDeductionListFactory $udlf */
					$udlf->getByCompanyIdAndCompanyDeductionId( $cd_obj->getCompany(), $cd_obj->getId() );
					if ( $udlf->getRecordCount() > 0 ) {
						foreach ( $udlf as $ud_obj ) {
							//Debug::Text('  User Deduction: ID: '. $ud_obj->getID() .' User ID: '. $ud_obj->getUser(), __FILE__, __LINE__, __METHOD__, 10);
							$user_deduction_data[$ud_obj->getCompanyDeduction()][$ud_obj->getUser()] = $ud_obj;
						}
					}
				}
			}
			//Debug::Arr($tax_deductions, 'Tax Deductions: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($user_deduction_data, 'User Deductions: ', __FILE__, __LINE__, __METHOD__, 10);
		}
		unset( $cdlf, $cd_obj, $udlf, $ud_obj );

		$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */
		$pself->getAPIReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $pself->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		if ( $pself->getRecordCount() > 0 ) {
			foreach ( $pself as $key => $pse_obj ) {
				//NOTE: This does not need to be broken out by run_id, as we add all amounts by date_stamp in this loop.
				$user_id = $this->user_ids[] = $pse_obj->getColumn( 'user_id' );
				$date_stamp = TTDate::strtotime( $pse_obj->getColumn( 'pay_stub_transaction_date' ) ); //Must be transaction date for COVID employment periods.
				$pay_stub_entry_name_id = $pse_obj->getPayStubEntryNameId();

				if ( !isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp] ) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp] = [
							'birth_date'                  => strtotime( $pse_obj->getColumn( 'birth_date' ) ),
							'pay_period_start_date'       => strtotime( $pse_obj->getColumn( 'pay_stub_start_date' ) ),
							'pay_period_end_date'         => strtotime( $pse_obj->getColumn( 'pay_stub_end_date' ) ),
							'pay_period_transaction_date' => strtotime( $pse_obj->getColumn( 'pay_stub_transaction_date' ) ),
							'pay_period'                  => strtotime( $pse_obj->getColumn( 'pay_stub_transaction_date' ) ),
					];
				}

				if ( isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id] ) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id] = bcadd( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id], $pse_obj->getColumn( 'amount' ) );
				} else {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id] = $pse_obj->getColumn( 'amount' );
				}

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}

			if ( isset( $this->tmp_data['pay_stub_entry'] ) && is_array( $this->tmp_data['pay_stub_entry'] ) ) {
				foreach ( $this->tmp_data['pay_stub_entry'] as $user_id => $data_a ) {
					foreach ( $data_a as $date_stamp => $data_b ) {
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['income'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['income']['include_pay_stub_entry_account'], $form_data['income']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['tax'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['tax']['include_pay_stub_entry_account'], $form_data['tax']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['union_dues'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['union_dues']['include_pay_stub_entry_account'], $form_data['union_dues']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['rpp'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['rpp']['include_pay_stub_entry_account'], $form_data['rpp']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['charity'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['charity']['include_pay_stub_entry_account'], $form_data['charity']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pension_adjustment'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['pension_adjustment']['include_pay_stub_entry_account'], $form_data['pension_adjustment']['exclude_pay_stub_entry_account'] );

						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['employee_ei'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['employee_ei']['include_pay_stub_entry_account'], $form_data['employee_ei']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['employer_ei'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['employer_ei']['include_pay_stub_entry_account'], $form_data['employer_ei']['exclude_pay_stub_entry_account'] );

						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['employee_cpp'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['employee_cpp']['include_pay_stub_entry_account'], $form_data['employee_cpp']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['employer_cpp'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['employer_cpp']['include_pay_stub_entry_account'], $form_data['employer_cpp']['exclude_pay_stub_entry_account'] );

						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['cpp_earnings'] = 0;
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['ei_earnings'] = 0;

						if ( is_array( $data_b['psen_ids'] ) && isset( $tax_deductions ) && isset( $user_deduction_data ) ) {
							//Support multiple tax/deductions that deposit to the same pay stub account.
							//Also make sure we handle tax/deductions that may not have anything deducted/withheld, but do have wages to be displayed.
							//  For example an employee not earning enough to have tax taken off yet.
							//Now that user_deduction supports start/end dates per employee, we could use that to better handle employees switching between Tax/Deduction records mid-year
							//  while still accounting for cases where nothing is deducted/withheld but still needs to be displayed.
							foreach ( $tax_deductions as $tax_deduction_id => $tax_deduction_obj ) {
								//Found Tax/Deduction associated with this pay stub account.
								if ( in_array( $user_id, (array)$tax_deduction_users[$tax_deduction_id] ) && isset( $user_deduction_data[$tax_deduction_id][$user_id] ) ) {
									//Debug::Text('Found User ID: '. $user_id .' in Tax Deduction Name: '. $tax_deduction_obj->getName() .'('.$tax_deduction_obj->getID().') Calculation ID: '. $tax_deduction_obj->getCalculation(), __FILE__, __LINE__, __METHOD__, 10);

									if ( $tax_deduction_obj->isActiveDate( $user_deduction_data[$tax_deduction_id][$user_id], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_period_end_date'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_period_transaction_date'] ) == true
											&& $tax_deduction_obj->isActiveLengthOfService( $user_deduction_data[$tax_deduction_id][$user_id], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_period_end_date'] ) == true
											&& $tax_deduction_obj->isActiveUserAge( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['birth_date'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_period_end_date'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_period_transaction_date'] ) == true
									) {
										//Debug::Text('  Is Eligible... Date: '. TTDate::getDate('DATE', $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_period_end_date'] ), __FILE__, __LINE__, __METHOD__, 10);

										if ( $tax_deduction_obj->getCalculation() == 90 ) {
											$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['cpp_earnings'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['cpp_earnings']['include_pay_stub_entry_account'], $form_data['cpp_earnings']['exclude_pay_stub_entry_account'] );
										}

										if ( $tax_deduction_obj->getCalculation() == 91 ) {
											$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['ei_earnings'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['ei_earnings']['include_pay_stub_entry_account'], $form_data['ei_earnings']['exclude_pay_stub_entry_account'] );
										}
									} else {
										Debug::Text( '  NOT Eligible... Date: ' . TTDate::getDate( 'DATE', $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_period_end_date'] ), __FILE__, __LINE__, __METHOD__, 10 );
									}
								}
							}
						}

						foreach ( range( 0, 23 ) as $n ) {
							if ( isset( $form_data['other_box'][$n] ) && isset( $form_data['other_box'][$n]['box'] ) && $form_data['other_box'][$n]['box'] != '' ) {
								$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['other_box_' . $n . '_code'] = $form_data['other_box'][$n]['box'];

								//Check if a start/end date filter is specified such as for COVID employment periods.
								if ( isset($form_data['other_box'][$n]['start_date']) && $form_data['other_box'][$n]['start_date'] != '' ) {
									if ( $date_stamp >= $form_data['other_box'][$n]['start_date'] && $date_stamp <= $form_data['other_box'][$n]['end_date']) {
										$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['other_box_' . $n] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['other_box'][$n]['include_pay_stub_entry_account'], $form_data['other_box'][$n]['exclude_pay_stub_entry_account'] );
									}
								} else {
									$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['other_box_' . $n] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['other_box'][$n]['include_pay_stub_entry_account'], $form_data['other_box'][$n]['exclude_pay_stub_entry_account'] );
								}
							}
						}
					}
				}
			}
			unset( $tax_deductions, $tax_deduction_users, $tax_deduction_id, $tax_deduction_obj, $user_deduction_data, $user_id, $data_a, $data_b );
		}

		$this->user_ids = array_unique( $this->user_ids ); //Used to get the total number of employees.

		//Debug::Arr($this->user_ids, 'User IDs: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->form_data, 'Form Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->tmp_data, 'Tmp Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' Employee Total Rows: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'Retrieving Employee Data...' ) );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( $this->getColumnDataConfig() );
			$this->tmp_data['user'][$u_obj->getId()]['user_id'] = $u_obj->getId();
			$this->tmp_data['user'][$u_obj->getId()]['legal_entity_id'] = $u_obj->getLegalEntity();
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Get legal entity data for joining.
		$lelf = TTnew( 'LegalEntityListFactory' ); /** @var LegalEntityListFactory $lelf */
		$lelf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		//Debug::Text( ' User Total Rows: ' . $lelf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $lelf->getRecordCount(), null, TTi18n::getText( 'Retrieving Legal Entity Data...' ) );
		if ( $lelf->getRecordCount() > 0 ) {
			foreach ( $lelf as $key => $le_obj ) {
				if ( $format == 'html' || $format == 'pdf' ) {
					$this->tmp_data['legal_entity'][$le_obj->getId()] = Misc::addKeyPrefix( 'legal_entity_', (array)$le_obj->getObjectAsArray( Misc::removeKeyPrefix( 'legal_entity_', $this->getColumnDataConfig() ) ) );
					$this->tmp_data['legal_entity'][$le_obj->getId()]['legal_entity_id'] = $le_obj->getId();
				} else {
					$this->form_data['legal_entity'][$le_obj->getId()] = $le_obj;
				}
				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}
		}

		$this->tmp_data['remittance_agency'] = [];

		$filter_data['agency_id'] = [ '10:CA:00:00:0010' ]; //CA federal
		$ralf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $ralf */
		$ralf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		//Debug::Text( ' Remittance Agency Total Rows: ' . $ralf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $lelf->getRecordCount(), null, TTi18n::getText( 'Retrieving Remittance Agency Data...' ) );
		if ( $ralf->getRecordCount() > 0 ) {
			foreach ( $ralf as $key => $ra_obj ) {
				$this->form_data['remittance_agency'][$ra_obj->getLegalEntity()] = $ra_obj;
				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}
		}

		return true;
	}

	/**
	 * PreProcess data such as calculating additional columns from raw data etc...
	 * @param null $format
	 * @return bool
	 */
	function _preProcess( $format = null ) {
		//Merge time data with user data
		$key = 0;
		if ( isset( $this->tmp_data['pay_stub_entry'] ) && is_array( $this->tmp_data['pay_stub_entry'] ) ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $this->tmp_data['pay_stub_entry'] ), null, TTi18n::getText( 'Pre-Processing Data...' ) );

			//$sort_columns = $this->getSortConfig();

			foreach ( $this->tmp_data['pay_stub_entry'] as $user_id => $level_1 ) {
				foreach ( $level_1 as $date_stamp => $row ) {
					$date_columns = TTDate::getReportDates( null, $date_stamp, false, $this->getUserObject(), [ 'pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date'] ] );
					$processed_data = [//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
					];

					$tmp_legal_array = [];
					if ( isset( $this->tmp_data['legal_entity'][$this->tmp_data['user'][$user_id]['legal_entity_id']] ) ) {
						$tmp_legal_array = $this->tmp_data['legal_entity'][$this->tmp_data['user'][$user_id]['legal_entity_id']];
					}
					$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data, $tmp_legal_array );

					$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
					$key++;
				}
			}
			unset( $this->tmp_data, $row, $date_columns, $processed_data, $tmp_legal_array );

			//Total data per employee for the T4 forms. Just include the columns that are necessary for the form.
			if ( is_array( $this->data ) && !( $format == 'html' || $format == 'pdf' ) ) {
				$t4_dollar_columns = [ 'income', 'tax', 'employee_cpp', 'ei_earnings', 'cpp_earnings', 'employee_ei', 'union_dues', 'rpp', 'charity', 'pension_adjustment', 'employer_ei', 'employer_cpp', 'other_box_0', 'other_box_1', 'other_box_2', 'other_box_3', 'other_box_4', 'other_box_5', 'other_box_6', 'other_box_7', 'other_box_8', 'other_box_9', 'other_box_10', 'other_box_11', 'other_box_12', 'other_box_13', 'other_box_14', 'other_box_15', 'other_box_16', 'other_box_17', 'other_box_18', 'other_box_19', 'other_box_20', 'other_box_21', 'other_box_22', 'other_box_23', 'other_box_24' ];

				Debug::Text( 'Calculating Form Data...', __FILE__, __LINE__, __METHOD__, 10 );
				foreach ( $this->data as $row ) {
					if ( !isset( $this->form_data['user'][$row['legal_entity_id']][$row['user_id']] ) ) {
						$this->form_data['user'][$row['legal_entity_id']][$row['user_id']] = [ 'user_id' => $row['user_id'] ];
					}

					foreach ( $row as $key => $value ) {
						if ( in_array( $key, $t4_dollar_columns ) ) {
							if ( !isset( $this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key] ) ) {
								$this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key] = 0;
							}
							$this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key] = bcadd( $this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key], $value );
						} else {
							$this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key] = $value;
						}
					}
				}
			}
		}
		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->form_data, 'Form Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return true;
	}

	/**
	 * @param null $format
	 * @return array|bool
	 */
	function _outputPDFForm( $format = null ) {
		Debug::Text( 'Generating Form... Format: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );

		$setup_data = $this->getFormConfig();
		$filter_data = $this->getFilterConfig();
		//Debug::Arr($setup_data, 'Setup Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$current_company = $this->getUserObject()->getCompanyObject();
		if ( is_object( $current_company ) == false ) {
			Debug::Text( 'Invalid company object...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		if ( !isset( $setup_data['status_id'] ) || ( is_numeric( $setup_data['status_id'] ) && $setup_data['status_id'] == 0 ) ) {
			$setup_data['status_id'] = 'O'; //Original
		}

		if ( stristr( $format, 'government' ) ) {
			$form_type = 'government';
		} else {
			$form_type = 'employee';
		}
		Debug::Text( 'Form Type: ' . $form_type, __FILE__, __LINE__, __METHOD__, 10 );
		$file_arr = [];
		$file_name = null;
		if ( isset( $this->form_data['user'] ) && is_array( $this->form_data['user'] ) ) {
			$file_name = 't4_summary.ext';

			$this->sortFormData(); //Make sure forms are sorted.

			foreach ( $this->form_data['user'] as $legal_entity_id => $user_rows ) {
				if ( isset( $this->form_data['legal_entity'][$legal_entity_id] ) == false ) {
					Debug::Text( 'Missing Legal Entity: ' . $legal_entity_id, __FILE__, __LINE__, __METHOD__, 10 );
					continue;
				}

				if ( isset( $this->form_data['remittance_agency'][$legal_entity_id] ) == false ) {
					Debug::Text( 'Missing Remittance Agency: ' . $legal_entity_id, __FILE__, __LINE__, __METHOD__, 10 );
					continue;
				}

				$x = 0; //Progress bar only.
				$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $user_rows ), null, TTi18n::getText( 'Generating Forms...' ) );

				$legal_entity_obj = $this->form_data['legal_entity'][$legal_entity_id];
				$company_name = $legal_entity_obj->getTradeName(); //T4s show Operating/Trade name on the forms.

				if ( is_object( $this->form_data['remittance_agency'][$legal_entity_id] ) ) {
					$contact_user_obj = $this->form_data['remittance_agency'][$legal_entity_id]->getContactUserObject();
				}
				if ( !isset( $contact_user_obj ) || !is_object( $contact_user_obj ) ) {
					$contact_user_obj = $this->getUserObject();
				}

				if ( $format == 'efile_xml' || $format == 'payment_services' ) {
					$t619 = $this->getT619Object();
					$t619->setStatus( $setup_data['status_id'] );
					$t619->transmitter_number = $this->form_data['remittance_agency'][$legal_entity_id]->getSecondaryIdentification();

					$t619->transmitter_name = $legal_entity_obj->getTradeName();
					$t619->transmitter_address1 = $legal_entity_obj->getAddress1();
					$t619->transmitter_address2 = $legal_entity_obj->getAddress2();
					$t619->transmitter_city = $legal_entity_obj->getCity();
					$t619->transmitter_province = $legal_entity_obj->getProvince();
					$t619->transmitter_postal_code = $legal_entity_obj->getPostalCode();

					$t619->contact_name = $contact_user_obj->getFullName();
					$t619->contact_phone = $contact_user_obj->getWorkPhone();
					$t619->contact_email = ( $contact_user_obj->getWorkEmail() != '' ) ? $contact_user_obj->getWorkEmail() : ( ( $contact_user_obj->getHomeEmail() != '' ) ? $contact_user_obj->getHomeEmail() : null );
					$t619->company_name = $company_name;
					$this->getFormObject()->addForm( $t619 );
				}

				$t4 = $this->getT4Object();
				if ( isset( $setup_data['include_t4_back'] ) && $setup_data['include_t4_back'] == 1 ) {
					$t4->setShowInstructionPage( true );
				}
				$t4->setType( $form_type );
				$t4->setStatus( $setup_data['status_id'] );
				$t4->year = TTDate::getYear( $filter_data['start_date'] );

				if ( isset( $this->form_data['remittance_agency'][$legal_entity_id] ) ) {
					$t4->payroll_account_number = $this->form_data['remittance_agency'][$legal_entity_id]->getPrimaryIdentification();//( isset( $setup_data['payroll_account_number'] ) AND $setup_data['payroll_account_number'] != '' ) ? $setup_data['payroll_account_number'] : $current_company->getBusinessNumber();
					$t4->company_name = $company_name;
				}

				$report_meta_data = [];
				foreach ( $user_rows as $user_row_key => $row ) {
					if ( !isset( $row['user_id'] ) ) {
						Debug::Text( 'User ID not set!', __FILE__, __LINE__, __METHOD__, 10 );
						continue;
					}

					$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
					$ulf->getById( TTUUID::castUUID( $row['user_id'] ) );
					if ( $ulf->getRecordCount() == 1 ) {
						$user_obj = $ulf->getCurrent();

						$employment_province = $user_obj->getProvince();
						//If employees address is out of the country, use the company province instead.
						if ( strtolower( $user_obj->getCountry() ) != 'ca' ) {
							$employment_province = $legal_entity_obj->getProvince();
							Debug::Text( '  Using Company Province of Employment: ' . $employment_province, __FILE__, __LINE__, __METHOD__, 10 );
						}

						//Determine the province of employment...
						$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
						if ( isset( $setup_data['tax']['include_pay_stub_entry_account'] ) ) {
							$cdlf->getByCompanyIDAndUserIdAndCalculationIdAndPayStubEntryAccountID( $current_company->getId(), $user_obj->getId(), 200, $setup_data['tax']['include_pay_stub_entry_account'] );
							if ( $setup_data['tax']['include_pay_stub_entry_account'] != 0
									&& $cdlf->getRecordCount() > 0
							) {
								//Loop through all Tax/Deduction records to find one
								foreach ( $cdlf as $cd_obj ) {
									if ( $cd_obj->getStatus() == 10 && strtolower( $cd_obj->getCountry() ) == 'ca' ) {
										$employment_province = $cd_obj->getProvince();
										Debug::Text( '  Deduction Province of Employment: ' . $employment_province, __FILE__, __LINE__, __METHOD__, 10 );
									}
								}
							}
						}
						unset( $cdlf, $cd_obj );
						Debug::Text( '  Final Province of Employment: ' . $employment_province, __FILE__, __LINE__, __METHOD__, 10 );

						$ee_data = [
								'id'                  => (string)TTUUID::convertStringToUUID( md5( $row['user_id'] . $t4->year . microtime( true ) ) ),
								'user_id'             => (string)$row['user_id'],
								'first_name'          => $user_obj->getFirstName(),
								'middle_name'         => $user_obj->getMiddleName(),
								'last_name'           => $user_obj->getLastName(),
								'address1'            => $user_obj->getAddress1(),
								'address2'            => $user_obj->getAddress2(),
								'city'                => $user_obj->getCity(),
								'province'            => ( ( $user_obj->getCountry() == 'CA' || $user_obj->getCountry() == 'US' ) ? ( ( $user_obj->getProvince() != '00' ) ? $user_obj->getProvince() : null ) : 'ZZ' ),
								'country'             => Option::getByKey( $user_obj->getCountry(), $current_company->getOptions( 'country' ) ),
								'country_code'        => $user_obj->getCountry(),
								'employment_province' => ( $employment_province != '00' ) ? $employment_province : null,
								'postal_code'         => $user_obj->getPostalCode(),
								'sin'                 => $user_obj->getSIN(),
								'employee_number'     => $user_obj->getEmployeeNumber(),

								//If lines with dollar amounts change, update $amount_boxes below.
								'l14'                 => $row['income'],
								'l22'                 => $row['tax'],
								'l16'                 => $row['employee_cpp'],
								'l17'                 => null, //QPP
								'l24'                 => $row['ei_earnings'],
								'l26'                 => $row['cpp_earnings'],
								'l18'                 => $row['employee_ei'],
								'l44'                 => $row['union_dues'],
								'l20'                 => $row['rpp'],
								'l46'                 => $row['charity'],
								'l52'                 => $row['pension_adjustment'],
								'l50'                 => isset( $setup_data['rpp_number'] ) ? $setup_data['rpp_number'] : null,

								//Employer data, needed for totals.
								'l19'                 => $row['employer_ei'],
								'l27'                 => $row['employer_cpp'],
								//If lines with dollar amounts change, update $amount_boxes below.

								'cpp_exempt'       => false,
								'ei_exempt'        => false,
						];

						//Need to reset all other boxes so data doesn't get carried over from one record to another.
						// This must come directly below the above $ee_data array being initialized.
						foreach ( range( 0, 23 ) as $z ) {
							$ee_data['other_box_'. $z .'_code'] = null;
							$ee_data['other_box_'. $z] = null;
						}

						//Boxes that contain dollar amounts, so we can determine if the T4 is "blank" or not.
						$amount_boxes = [ 'l14', 'l22', 'l16', 'l17', 'l24', 'l26', 'l18', 'l44', 'l20', 'l46', 'l52', 'l19', 'l27', 'other_box_0', 'other_box_1', 'other_box_2', 'other_box_3', 'other_box_4', 'other_box_5' ];


						//CPP Exempt:
						// Leave this box blank if you:
						//  reported a retiring allowance and no other type of income
						//  reported an amount greater than "0" in boxes 16, 17, or 26
						//  reported "0" in box 26 and the employee gave you a copy of a completed Form CPT30, Election to Stop Contributing to the Canada Pension Plan, or Revocation of a Prior Election
						//  reported "0" in box 26 and the employee worked in one of the employment types listed as letters "C" to "O" on the back of Form CPT20, Election to Pay Canada Pension Plan Contributions
						//  Otherwise, enter an "X" or a check mark if you did not have to withhold CPP contributions from the earnings for the entire reporting period.
						//
						//  Cases in TimeTrex are as follows:
						//    Not assigned to the CPP Tax/Deduction record, and no CPP earnings/deductions = CPP Exempt
						//    Assigned to CPP Tax/Deduction Record, but too young, or too old and no CPP earnings/deductions = CPP Exempt
						if ( $ee_data['l16'] == 0 && $ee_data['l17'] == 0 && $ee_data['l26'] == 0 ) {
							//Debug::Text('CPP Exempt!', __FILE__, __LINE__, __METHOD__, 10);
							$ee_data['cpp_exempt'] = true;
						}

						//Check to see if the user has a end date set on their deduction record, signifying that they elected to stop CPP contributions.
						if ( $ee_data['l26'] == 0 && isset( $setup_data['employee_cpp']['include_pay_stub_entry_account'] ) && $setup_data['employee_cpp']['include_pay_stub_entry_account'] != TTUUID::getZeroID() ) {
							//Get User Tax / Deductions by Pay Stub Account.
							$udlf = TTnew( 'UserDeductionListFactory' ); /** @var UserDeductionListFactory $udlf */
							$udlf->getByUserIdAndPayStubEntryAccountID( $user_obj->getId(), $setup_data['employee_cpp']['include_pay_stub_entry_account'] );
							if ( $udlf->getRecordCount() > 0 ) {
								$ud_obj = $udlf->getCurrent();
								if ( $ud_obj->getEndDate() != '' && $ud_obj->getEndDate() < $filter_data['end_date'] ) {
									$ee_data['cpp_exempt'] = false;
									Debug::Text( 'CPP NOT Exempt!', __FILE__, __LINE__, __METHOD__, 10 );
								}
							}
							unset( $udlf, $ud_obj ); //Don't unset $udlf as its used below.
						}

						//EI Exempt:
						//Leave this box blank if you reported an amount greater than "0" in box 18 or 24.
						// Enter an "X" or a check mark in the "EI" box only if you did not have to withhold EI premiums from the earnings for the entire reporting period.
						if ( $ee_data['l18'] == 0 && $ee_data['l24'] == 0 ) {
							$ee_data['ei_exempt'] = true;
						}

						//Re-number other boxes so there aren't any gaps between them. Otherwise this could result in multiple pages being
						$tmp_other_box_destination = 0;
						foreach ( range( 0, 23 ) as $z ) {
							//Make sure if the amount is a negative value, that it gets skipped completely. Otherwise it will fail the eFile schema checks.
							if ( isset( $row['other_box_' . $z] ) && $row['other_box_' . $z] > 0 && isset( $row['other_box_' . $z .'_code'] ) && $row['other_box_' . $z .'_code'] != '' ) {
								$ee_data['other_box_'. $tmp_other_box_destination .'_code'] = $row['other_box_' . $z .'_code'];
								$ee_data['other_box_'. $tmp_other_box_destination] = $row['other_box_'. $z];
								$tmp_other_box_destination++;
							}
						}

						//Make sure there is actually data on the T4, otherwise skip the employee.
						$tmp_total_amount_boxes = 0;
						foreach ( $amount_boxes as $amount_box ) {
							if ( isset( $ee_data[$amount_box] ) && is_numeric( $ee_data[$amount_box] ) ) {
								$tmp_total_amount_boxes += $ee_data[$amount_box];
							}
						}

						if ( $tmp_total_amount_boxes != 0 ) {
							if ( $format == 'payment_services' ) {
								$report_meta_data['t4'][] = [ 'remote_id' => $row['user_id'], 'first_name' => $user_obj->getFirstName(), 'last_name' => $user_obj->getLastName(), 'sin' => $user_obj->getSIN(), 'l14' => $ee_data['l14'], 'l22' => $ee_data['l22'] ];
							}

							$t4->addRecord( $ee_data );

							if ( $format == 'pdf_form_publish_employee' ) {
								// generate PDF for every employee and assign to each government document records
								$this->getFormObject()->addForm( $t4 );
								GovernmentDocumentFactory::addDocument( $user_obj->getId(), 20, 100, TTDate::getEndYearEpoch( $filter_data['start_date'] ), ( ( $t4->countRecords() == 1 ) ? $this->getFormObject()->output( 'PDF', false ) : null ), ( ( $t4->countRecords() == 1 ) ? $this->getFormObject()->serialize() : null ) );
								$this->getFormObject()->clearForms();
							}
						} else {
							Debug::Text( '  No amounts on T4 skipping: ' . $user_obj->getFullName(), __FILE__, __LINE__, __METHOD__, 10 );
							unset( $user_rows[$user_row_key] ); //Remove user data from arrow does it doesn't get added to totals or counted as an employee.
						}
						unset( $ee_data );
					}

					$this->getProgressBarObject()->set( $this->getAPIMessageID(), $x );
					$x++;
				}

				if ( $format != 'pdf_form_publish_employee' ) {
					$this->getFormObject()->addForm( $t4 );

					if ( $t4->countRecords() > 0 && ( $form_type == 'government' || $format == 'efile_xml' || $format == 'payment_services' ) ) {
						//Handle T4Summary
						$t4s = $this->getT4SumObject();
						$t4s->setStatus( $setup_data['status_id'] );
						$t4s->year = $t4->year;
						$t4s->payroll_account_number = $t4->payroll_account_number;

						$t4s->company_name = $legal_entity_obj->getTradeName(); //T4s show Operating/Trade name on the forms.
						$t4s->company_address1 = $legal_entity_obj->getAddress1();
						$t4s->company_address2 = $legal_entity_obj->getAddress2();
						$t4s->company_city = $legal_entity_obj->getCity();
						$t4s->company_province = $legal_entity_obj->getProvince();
						$t4s->company_postal_code = $legal_entity_obj->getPostalCode();

						$t4s->l76 = $contact_user_obj->getFullName(); //Contact name.
						$t4s->l78 = $contact_user_obj->getWorkPhone();

						$t4->sumRecords();
						$total_row = $t4->getRecordsTotal();

						$t4s->l88 = $t4->countRecords();
						$t4s->l14 = ( isset( $total_row['l14'] ) ) ? $total_row['l14'] : null;
						$t4s->l22 = ( isset( $total_row['l22'] ) ) ? $total_row['l22'] : null;
						$t4s->l16 = ( isset( $total_row['l16'] ) ) ? $total_row['l16'] : null;
						$t4s->l18 = ( isset( $total_row['l18'] ) ) ? $total_row['l18'] : null;
						$t4s->l27 = ( isset( $total_row['l27'] ) ) ? $total_row['l27'] : null;
						$t4s->l19 = ( isset( $total_row['l19'] ) ) ? $total_row['l19'] : null;
						$t4s->l20 = ( isset( $total_row['l20'] ) ) ? $total_row['l20'] : null;
						$t4s->l52 = ( isset( $total_row['l52'] ) ) ? $total_row['l52'] : null;

						$total_deductions = Misc::MoneyRound( Misc::sumMultipleColumns( $total_row, [ 'l16', 'l27', 'l18', 'l19', 'l22' ] ) );

						if ( isset( $setup_data['remittances_paid'] ) && $setup_data['remittances_paid'] != '' ) {
							$t4s->l82 = (float)$setup_data['remittances_paid'];
						} else {
							$t4s->l82 = $total_deductions;
						}

						if ( $format == 'payment_services' ) {
							$report_meta_data['t4s'] = [ 'total_employees' => $t4->countRecords(), 'l14' => $t4s->l14, 'l22' => $t4s->l22, 'l80' => $total_deductions, 'l82' => $t4s->l82, 'balance' => bcsub( $total_deductions, $t4s->l82 ) ];
						}

						$this->getFormObject()->addForm( $t4s );
					}

					if ( $t4->countRecords() > 0 ) {
						if ( $format == 'efile_xml' || $format == 'payment_services' ) {
							$output_format = 'XML';
							$file_name = 't4_efile_' . date( 'Y_m_d' ) . '_' . Misc::sanitizeFileName( $this->form_data['legal_entity'][$legal_entity_id]->getTradeName() ) . '.xml';
							$mime_type = 'applications/octet-stream'; //Force file to download.
						} else {
							$output_format = 'PDF';
							$file_name = 't4_' . Misc::sanitizeFileName( $this->form_data['legal_entity'][$legal_entity_id]->getTradeName() ) . '.pdf';
							$mime_type = $this->file_mime_type;
						}
						$file_output = $this->getFormObject()->output( $output_format );

						//reset the file objects.
						$this->clearFormObject();
						$this->clearT4Object();
						$this->clearT619Object();
						$this->clearT4SumObject();

						if ( !is_array( $file_output ) ) {
							if ( $format == 'payment_services' ) {
								$tmp_output['xml'] = $file_output;
								$tmp_output['metadata'] = $report_meta_data;
								$file_output = $tmp_output;
							}
							$file_arr[] = [ 'file_name' => $file_name, 'mime_type' => $mime_type, 'data' => $file_output ];
							unset( $file_output );
						} else {
							return $file_output;
						}
					} else {
						return false; //No records.
					}
				}
			}
		}

		if ( $format == 'pdf_form_publish_employee' ) {
			$user_generic_status_batch_id = GovernmentDocumentFactory::saveUserGenericStatus( $this->getUserObject()->getId() );
			return $user_generic_status_batch_id;
		} else {
			if ( isset( $file_name ) && $file_name != '' ) {
				$zip_filename = explode( '.', $file_name );
				if ( isset( $zip_filename[( count( $zip_filename ) - 1 )] ) ) {
					$zip_filename = str_replace( '.', '', str_replace( $zip_filename[( count( $zip_filename ) - 1 )], '', $file_name ) ) . '.zip';
				} else {
					$zip_filename = str_replace( '.', '', $file_name ) . '.zip';
				}

				return Misc::zip( $file_arr, $zip_filename, true );
			}

			Debug::Text( ' Returning FALSE!', __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		}
	}

	/**
	 * Short circuit this function, as no postprocessing is required for exporting the data.
	 * @param null $format
	 * @return bool
	 */
	function _postProcess( $format = null ) {
		if ( ( $format == 'pdf_form' || $format == 'pdf_form_government' ) || ( $format == 'pdf_form_print' || $format == 'pdf_form_print_government' ) || $format == 'efile_xml' || $format == 'payment_services' || $format == 'pdf_form_publish_employee' ) {
			Debug::Text( 'Skipping postProcess! Format: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			return parent::_postProcess( $format );
		}
	}

	/**
	 * @param null $format
	 * @return array|bool
	 */
	function _output( $format = null ) {
		if ( ( $format == 'pdf_form' || $format == 'pdf_form_government' ) || ( $format == 'pdf_form_print' || $format == 'pdf_form_print_government' ) || $format == 'efile_xml' || $format == 'payment_services' || $format == 'pdf_form_publish_employee' ) {
			return $this->_outputPDFForm( $format );
		} else {
			return parent::_output( $format );
		}
	}

	/**
	 * Formats report data for exporting to TimeTrex payment service.
	 * @param $prae_obj
	 * @param $pra_obj
	 * @param $rs_obj
	 * @param $pra_user_obj
	 * @return array|bool
	 */
	function getPaymentServicesData( $prae_obj, $pra_obj, $rs_obj, $pra_user_obj ) {
		$output_data = $this->getOutput( 'payment_services' );
		Debug::Arr( $output_data, 'Raw Report data!', __FILE__, __LINE__, __METHOD__, 10 );

		if ( $this->hasData() && isset( $output_data['data'] ) && isset( $output_data['data']['metadata']['t4s']['total_employees'] ) && $output_data['data']['metadata']['t4s']['total_employees'] > 0 ) {
			$batch_id = date( 'M d', $prae_obj->getEndDate() );

			$retarr = [
					'object'               => __CLASS__,
					'user_success_message' => TTi18n::gettext( 'T4s submitted successfully' ),

					'agency_report_data' => [
							'type_id'         => 'R', //R=Report
							'total_employees' => (int)$output_data['data']['metadata']['t4s']['total_employees'],
							'subject_wages'   => $output_data['data']['metadata']['t4s']['l14'],
							'taxable_wages'   => $output_data['data']['metadata']['t4s']['l14'],
							'amount_withheld' => $output_data['data']['metadata']['t4s']['l80'], //Total Deductions
							'amount_due'      => $output_data['data']['metadata']['t4s']['balance'],
							'due_date'        => $prae_obj->getDueDate(),
							'extra_data'      => $output_data['data']['metadata'],
							'xml_data'        => $output_data['data']['xml'],

							'remote_batch_id' => $batch_id,

							//Generate a consistent remote_id based on the exact time period, the remittance agency event, and batch ID.
							//This helps to prevent duplicate records from be created, as well as work across separate or split up batches that may be processed.
							//  This needs to take into account different start/end date periods, so we don't try to overwrite records from last year.
							'remote_id'       => TTUUID::convertStringToUUID( md5( $prae_obj->getId() . $prae_obj->getStartDate() . $prae_obj->getEndDate() ) ),

					],
			];

			return $retarr;
		}

		Debug::Text( 'No report data!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}
}

?>
