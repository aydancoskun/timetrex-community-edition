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
 * @package Modules\Report
 */
class Form940Report extends Report {

	/**
	 * Form940Report constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText('Form 940 Report');
		$this->file_name = 'form_940';

		parent::__construct();

		return TRUE;
	}

	/**
	 * @param string $user_id UUID
	 * @param string $company_id UUID
	 * @return bool
	 */
	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('report', 'enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('report', 'view_form940', $user_id, $company_id ) ) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param $name
	 * @param null $params
	 * @return array|bool|null
	 */
	protected function _getOptions( $name, $params = NULL ) {
		$retval = NULL;
		switch( $name ) {
			case 'output_format':
				$retval = array_merge( parent::getOptions('default_output_format'),
									array(
										'-1100-pdf_form' => TTi18n::gettext('Form'),
										//'-1120-efile' => TTi18n::gettext('eFile'),
										)
									);
				break;
			case 'default_setup_fields':
				$retval = array(
										'template',
										'time_period',
										'columns',
								);

				break;
			case 'setup_fields':
				$retval = array(
										//Static Columns - Aggregate functions can't be used on these.
										'-1000-template' => TTi18n::gettext('Template'),
										'-1010-time_period' => TTi18n::gettext('Time Period'),
										'-2000-legal_entity_id' => TTi18n::gettext('Legal Entity'),
										'-2010-user_status_id' => TTi18n::gettext('Employee Status'),
										'-2020-user_group_id' => TTi18n::gettext('Employee Group'),
										'-2030-user_title_id' => TTi18n::gettext('Employee Title'),
										'-2040-include_user_id' => TTi18n::gettext('Employee Include'),
										'-2050-exclude_user_id' => TTi18n::gettext('Employee Exclude'),
										'-2060-default_branch_id' => TTi18n::gettext('Default Branch'),
										'-2070-default_department_id' => TTi18n::gettext('Default Department'),
										'-2100-custom_filter' => TTi18n::gettext('Custom Filter'),

										//'-4020-exclude_ytd_adjustment' => TTi18n::gettext('Exclude YTD Adjustments'),

										'-5000-columns' => TTi18n::gettext('Display Columns'),
										'-5010-group' => TTi18n::gettext('Group By'),
										'-5020-sub_total' => TTi18n::gettext('SubTotal By'),
										'-5030-sort' => TTi18n::gettext('Sort By'),
								);
				break;
			case 'time_period':
				$retval = TTDate::getTimePeriodOptions( FALSE ); //Exclude Pay Period options.
				break;
			case 'date_columns':
				$retval = TTDate::getReportDateOptions( NULL, TTi18n::getText('Date'), 13, TRUE );
				break;
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), NULL, 'Form940Report', 'custom_column' );
					if ( is_array($custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('filter_column_type_ids'), NULL, 'Form940Report', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('dynamic_format_ids'), 'Form940Report', 'custom_column' );
					if ( is_array($report_dynamic_custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('static_format_ids'), 'Form940Report', 'custom_column' );
					if ( is_array($report_static_custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $report_static_custom_column_labels, 9700 );
					}
				}
				break;
			case 'formula_columns':
				$retval = TTMath::formatFormulaColumns( array_merge( array_diff( $this->getOptions('static_columns'), (array)$this->getOptions('report_static_custom_column') ), $this->getOptions('dynamic_columns') ) );
				break;
			case 'filter_columns':
				$retval = TTMath::formatFormulaColumns( array_merge( $this->getOptions('static_columns'), $this->getOptions('dynamic_columns'), (array)$this->getOptions('report_dynamic_custom_column') ) );
				break;
			case 'static_columns':
				$retval = array(
										//Static Columns - Aggregate functions can't be used on these.
										'-1000-first_name' => TTi18n::gettext('First Name'),
										'-1001-middle_name' => TTi18n::gettext('Middle Name'),
										'-1002-last_name' => TTi18n::gettext('Last Name'),
										'-1005-full_name' => TTi18n::gettext('Full Name'),
										'-1030-employee_number' => TTi18n::gettext('Employee #'),
										'-1035-sin' => TTi18n::gettext('SIN/SSN'),
										'-1040-status' => TTi18n::gettext('Status'),
										'-1050-title' => TTi18n::gettext('Title'),
										'-1060-province' => TTi18n::gettext('Province/State'),
										'-1070-country' => TTi18n::gettext('Country'),
										'-1080-group' => TTi18n::gettext('Group'),
										'-1090-default_branch' => TTi18n::gettext('Default Branch'),
										'-1100-default_department' => TTi18n::gettext('Default Department'),
										'-1110-currency' => TTi18n::gettext('Currency'),
										//'-1111-current_currency' => TTi18n::gettext('Current Currency'),

										//'-1110-verified_time_sheet' => TTi18n::gettext('Verified TimeSheet'),
										//'-1120-pending_request' => TTi18n::gettext('Pending Requests'),

										//Handled in date_columns above.
										//'-1450-pay_period' => TTi18n::gettext('Pay Period'),

										'-1400-permission_control' => TTi18n::gettext('Permission Group'),
										'-1410-pay_period_schedule' => TTi18n::gettext('Pay Period Schedule'),
										'-1420-policy_group' => TTi18n::gettext('Policy Group'),
								);

				$retval = array_merge( $retval, $this->getOptions('date_columns'), (array)$this->getOptions('report_static_custom_column') );
				ksort($retval);
				break;
			case 'dynamic_columns':
				$retval = array(
										//Dynamic - Aggregate functions can be used
										'-2010-total_payments' => TTi18n::gettext('Total Payments'), //Line 3
										'-2020-exempt_payments' => TTi18n::gettext('Exempt Payments'), //Line 4
										'-2030-excess_payments' => TTi18n::gettext('Excess Payments'), //Line 5
										'-2040-taxable_wages' => TTi18n::gettext('Taxable Wages'), //Line 7
										'-2050-before_adjustment_tax' => TTi18n::gettext('Tax Before Adjustments'), //Line 8
										'-2052-adjustment_tax' => TTi18n::gettext('Tax Adjustments'), //Line 9
										'-2054-after_adjustment_tax' => TTi18n::gettext('Tax After Adjustments'), //Line 12
							);
				break;
			case 'columns':
				$retval = array_merge( $this->getOptions('static_columns'), $this->getOptions('dynamic_columns'), (array)$this->getOptions('report_dynamic_custom_column') );
				ksort($retval);
				break;
			case 'column_format':
				//Define formatting function for each column.
				$columns = array_merge( $this->getOptions('dynamic_columns'), (array)$this->getOptions('report_custom_column') );
				if ( is_array($columns) ) {
					foreach($columns as $column => $name ) {
						$retval[$column] = 'currency';
					}
				}
				break;
			case 'aggregates':
				$retval = array();
				$dynamic_columns = array_keys( Misc::trimSortPrefix( array_merge( $this->getOptions('dynamic_columns'), (array)$this->getOptions('report_dynamic_custom_column') ) ) );
				if ( is_array($dynamic_columns ) ) {
					foreach( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								$retval[$column] = 'sum';
						}
					}
				}

				break;
			case 'state':
				$retval = Misc::prependArray( array( 0 => TTi18n::getText('- Multi-state Employer -') ), $this->getUserObject()->getCompanyObject()->getOptions('province', 'US' ) );
				break;
			case 'return_type':
				$retval = array(
											//0 => TTi18n::getText('--'),
											10 => TTi18n::getText('Amended'),
											20 => TTi18n::getText('Successor Employer'),
											30 => TTi18n::getText('No Payments to Employees'),
											40 => TTi18n::getText('Final: Business closed or stopped paying wages'),
										);
				break;
			case 'exempt_payment':
				$retval = array(
											//0 => TTi18n::getText('--'),
											10 => TTi18n::getText('4a. Fringe benefits'),
											20 => TTi18n::getText('4b. Group term life insurance'),
											30 => TTi18n::getText('4c. Retirement/Pension'),
											40 => TTi18n::getText('4d. Dependant care'),
											50 => TTi18n::getText('4e. Other'),
										);
				break;
			case 'templates':
				$retval = array(
										'-1010-by_quarter' => TTi18n::gettext('by Quarter'),
										'-1010-by_month' => TTi18n::gettext('by Month'),
										'-1020-by_employee' => TTi18n::gettext('by Employee'),
										'-1030-by_branch' => TTi18n::gettext('by Branch'),
										'-1040-by_department' => TTi18n::gettext('by Department'),
										'-1050-by_branch_by_department' => TTi18n::gettext('by Branch/Department'),

										'-1060-by_month_by_employee' => TTi18n::gettext('by Month/Employee'),
										'-1070-by_month_by_branch' => TTi18n::gettext('by Month/Branch'),
										'-1080-by_month_by_department' => TTi18n::gettext('by Month/Department'),
										'-1090-by_month_by_branch_by_department' => TTi18n::gettext('by Month/Branch/Department'),
								);

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset($template) AND $template != '' ) {
					switch( $template ) {
						case 'default':
							//Proper settings to generate the form.
							//$retval['-1010-time_period']['time_period'] = 'last_quarter';

							$retval['columns'] = $this->getOptions('columns');

							$retval['group'][] = 'date_quarter_month';

							$retval['sort'][] = array('date_quarter_month' => 'asc');

							$retval['other']['grand_total'] = TRUE;

							break;
						default:
							Debug::Text(' Parsing template name: '. $template, __FILE__, __LINE__, __METHOD__, 10);
							$retval['columns'] = array();
							$retval['-1010-time_period']['time_period'] = 'last_year';

							//Parse template name, and use the keywords separated by '+' to determine settings.
							$template_keywords = explode('+', $template );
							if ( is_array($template_keywords) ) {
								foreach( $template_keywords as $template_keyword ) {
									Debug::Text(' Keyword: '. $template_keyword, __FILE__, __LINE__, __METHOD__, 10);

									switch( $template_keyword ) {
										//Columns

										//Filter
										//Group By
										//SubTotal
										//Sort
										case 'by_quarter':
											$retval['columns'][] = 'date_quarter';

											$retval['group'][] = 'date_quarter';

											$retval['sort'][] = array('date_quarter' => 'asc');
											break;
										case 'by_month':
											$retval['columns'][] = 'date_month';

											$retval['group'][] = 'date_month';

											$retval['sort'][] = array('date_month' => 'asc');
											break;
										case 'by_employee':
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';

											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											break;
										case 'by_branch':
											$retval['columns'][] = 'default_branch';

											$retval['group'][] = 'default_branch';

											$retval['sort'][] = array('default_branch' => 'asc');
											break;
										case 'by_department':
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'default_department';

											$retval['sort'][] = array('default_department' => 'asc');
											break;
										case 'by_branch_by_department':
											$retval['columns'][] = 'default_branch';
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'default_branch';
											$retval['group'][] = 'default_department';

											$retval['sub_total'][] = 'default_branch';

											$retval['sort'][] = array('default_branch' => 'asc');
											$retval['sort'][] = array('default_department' => 'asc');
											break;
										case 'by_month_by_employee':
											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';

											$retval['sub_total'][] = 'date_month';

											$retval['sort'][] = array('date_month' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											break;
										case 'by_month_by_branch':
											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'default_branch';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'default_branch';

											$retval['sub_total'][] = 'date_month';

											$retval['sort'][] = array('date_month' => 'asc');
											$retval['sort'][] = array('default_branch' => 'asc');
											break;
										case 'by_month_by_department':
											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'default_department';

											$retval['sub_total'][] = 'date_month';

											$retval['sort'][] = array('date_month' => 'asc');
											$retval['sort'][] = array('default_department' => 'asc');
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

											$retval['sort'][] = array('date_month' => 'asc');
											$retval['sort'][] = array('default_branch' => 'asc');
											$retval['sort'][] = array('default_department' => 'asc');
											break;

									}
								}
							}

							$retval['columns'] = array_merge( $retval['columns'], array_keys( Misc::trimSortPrefix( $this->getOptions('dynamic_columns') ) ) );

							break;
					}
				}

				//Set the template dropdown as well.
				$retval['-1000-template'] = $template;

				//Add sort prefixes so Flex can maintain order.
				if ( isset($retval['filter']) ) {
					$retval['-5000-filter'] = $retval['filter'];
					unset($retval['filter']);
				}
				if ( isset($retval['columns']) ) {
					$retval['-5010-columns'] = $retval['columns'];
					unset($retval['columns']);
				}
				if ( isset($retval['group']) ) {
					$retval['-5020-group'] = $retval['group'];
					unset($retval['group']);
				}
				if ( isset($retval['sub_total']) ) {
					$retval['-5030-sub_total'] = $retval['sub_total'];
					unset($retval['sub_total']);
				}
				if ( isset($retval['sort']) ) {
					$retval['-5040-sort'] = $retval['sort'];
					unset($retval['sort']);
				}
				Debug::Arr($retval, ' Template Config for: '. $template, __FILE__, __LINE__, __METHOD__, 10);

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
		if ( !isset($this->form_obj['gf']) OR !is_object($this->form_obj['gf']) ) {
			//
			//Get all data for the form.
			//
			require_once( Environment::getBasePath() .'/classes/GovernmentForms/GovernmentForms.class.php');

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
		$this->form_obj['gf'] = FALSE;

		return TRUE;
	}

	/**
	 * @return mixed
	 */
	function getF940Object() {
		if ( !isset($this->form_obj['f940']) OR !is_object($this->form_obj['f940']) ) {
			$this->form_obj['f940'] = $this->getFormObject()->getFormObject( '940', 'US' );
			return $this->form_obj['f940'];
		}

		return $this->form_obj['f940'];
	}

	/**
	 * @return bool
	 */
	function clearF940Object() {
		$this->form_obj['f940'] = FALSE;

		return TRUE;
	}

	/**
	 * @return mixed
	 */
	function getRETURN940Object() {
		if ( !isset($this->form_obj['return940']) OR !is_object($this->form_obj['return940']) ) {
			$this->form_obj['return940'] = $this->getFormObject()->getFormObject( 'RETURN940', 'US' );
			return $this->form_obj['return940'];
		}

		return $this->form_obj['return940'];
	}

	/**
	 * @return bool
	 */
	function clearRETURN940Object() {
		$this->form_obj['return940'] = FALSE;

		return TRUE;
	}

	/**
	 * @return array
	 */
	function formatFormConfig() {
		$default_include_exclude_arr = array( 'include_pay_stub_entry_account' => array(), 'exclude_pay_stub_entry_account' => array() );

		$default_arr = array(
				'total_payments' => $default_include_exclude_arr,
				'exempt_payments' => $default_include_exclude_arr,
			);

		$retarr = array_merge( $default_arr, (array)$this->getFormConfig() );
		return $retarr;
	}

	/**
	 * Get raw data for report
	 * @param null $format
	 * @return bool
	 */
	function _getData( $format = NULL ) {
		$this->tmp_data = array( 'pay_stub_entry' => array(), 'user_total' => array() );

		$filter_data = $this->getFilterConfig();
		$form_data = $this->formatFormConfig();
		$setup_data = $this->getFormConfig();

		if ( isset($setup_data['total_payments']) ) {
			unset($setup_data['total_payments'], $form_data['total_payments']); //Ignore any total_payment include/exclude coming from the UI. As its determined from remittance agency data below.
		}

		if ( !isset($setup_data['line_10']) ) {
			$setup_data['line_10'] = NULL;
		}
		if ( !isset($setup_data['line_11']) ) {
			$setup_data['line_11'] = NULL;
		}

		//Get remittance agency for joining. Also use this to find the Tax/Deduction records to determine the include/exclude pay stub accounts.
		$filter_data['type_id'] = array(10, 20); //Federal/State (Need State here to determine if they are a multi-state employer or not.
		$filter_data['country'] = array('US'); //US Federal
		$ralf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $ralf */
		$ralf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' Remittance Agency Total Rows: ' . $ralf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ralf->getRecordCount(), NULL, TTi18n::getText( 'Retrieving Remittance Agency Data...' ) );
		if ( $ralf->getRecordCount() > 0 ) {
			//Initialize array for federal payments above the per state loop below.
			$form_data['total_payments']['include_pay_stub_entry_account'] = array();
			$form_data['total_payments']['exclude_pay_stub_entry_account'] = array();
			$form_data['total_payments']['pay_stub_entry_account'] = array();
			foreach( $ralf as $key => $ra_obj ) {
				if ( $ra_obj->getStatus() == 10 AND
						( ( $ra_obj->getType() == 10 AND $ra_obj->parseAgencyID( NULL, 'id') == 10 ) //IRS
								OR ( $ra_obj->getType() == 20 AND $ra_obj->parseAgencyID( NULL, 'id') == 20 ) //State Unemployment Agency.
								OR ( $ra_obj->parseAgencyID( NULL, 'id') == 10 AND in_array( $ra_obj->getProvince(), array('NY', 'CA', 'NM', 'OR' ) ) ) ) ) { //States that combine UI with Income Tax.
					$province_id = ( $ra_obj->getType() == 20 ) ? $ra_obj->getProvince() : '00';
					$this->form_data['remittance_agency'][$ra_obj->getLegalEntity()][$province_id] = $ra_obj;

					if ( $province_id != '00' ) {
						$this->form_data['remittance_agency_states'][$ra_obj->getLegalEntity()][$province_id] = TRUE; //Track which states have remittance agencies to determine multi-state employer or not.
					}

					//Get associated CompanyDeduction record to determine include/exclude PSE accounts for Total payments to all employees.
					$cdlf = $ra_obj->getCompanyDeductionListFactory();
					if ( $cdlf->getRecordCount() > 0 ) {
						foreach( $cdlf as $cd_obj ) {
							if ( $cd_obj->getCalculation() == 15 AND stripos( $cd_obj->getName(), 'unemployment' ) !== FALSE ) { //15=Advanced Percent
								Debug::Text( ' Found Company Deduction record: '. $cd_obj->getName() .' linked to Agency: ' . $ra_obj->getName(), __FILE__, __LINE__, __METHOD__, 10 );

								if ( $province_id == '00' ) {
									$form_data['total_payments']['include_pay_stub_entry_account'] = array_unique( array_merge( $form_data['total_payments']['include_pay_stub_entry_account'], (array)$cd_obj->getIncludePayStubEntryAccount() ) );
									$form_data['total_payments']['exclude_pay_stub_entry_account'] = array_unique( array_merge( $form_data['total_payments']['exclude_pay_stub_entry_account'], (array)$cd_obj->getExcludePayStubEntryAccount() ) );
									$form_data['total_payments']['pay_stub_entry_account'] = array_unique( array_merge( $form_data['total_payments']['pay_stub_entry_account'], (array)$cd_obj->getPayStubEntryAccount() ) );
								} else {
									$form_data['state_total_payments'][$province_id]['include_pay_stub_entry_account'] = array_unique( array_merge( $form_data['total_payments']['include_pay_stub_entry_account'], (array)$cd_obj->getIncludePayStubEntryAccount() ) );
									$form_data['state_total_payments'][$province_id]['exclude_pay_stub_entry_account'] = array_unique( array_merge( $form_data['total_payments']['exclude_pay_stub_entry_account'], (array)$cd_obj->getExcludePayStubEntryAccount() ) );
									$form_data['state_total_payments'][$province_id]['pay_stub_entry_account'] = array_unique( array_merge( $form_data['total_payments']['pay_stub_entry_account'], (array)$cd_obj->getPayStubEntryAccount() ) );
								}
							}
						}
					}

				}
				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}
			unset($province_id);

			Debug::Arr( $form_data['total_payments'], ' PSE Accounts for Federal Total Payments: ', __FILE__, __LINE__, __METHOD__, 10 );
			Debug::Arr( $form_data['state_total_payments'], ' PSE Accounts for State Total Payments: ', __FILE__, __LINE__, __METHOD__, 10 );
		}

		$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */
		$pself->getAPIReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data, NULL, NULL, NULL, array( 'user_id' => 'asc', 'pay_stub_transaction_date' => 'asc' ) );
		if ( $pself->getRecordCount() > 0 ) {
			foreach( $pself as $pse_obj ) {
				$legal_entity_id = $pse_obj->getColumn('legal_entity_id');
				$user_id = $pse_obj->getColumn('user_id');
				$date_stamp = $this->date_stamps[] = TTDate::strtotime( $pse_obj->getColumn('pay_stub_transaction_date') );
				$pay_stub_entry_name_id = $pse_obj->getPayStubEntryNameId();

				if ( !isset($this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp] = array(
																'legal_entity_id' => $legal_entity_id,
																'pay_period_start_date' => strtotime( $pse_obj->getColumn('pay_stub_start_date') ),
																'pay_period_end_date' => strtotime( $pse_obj->getColumn('pay_stub_end_date') ),
																'pay_period_transaction_date' => strtotime( $pse_obj->getColumn('pay_stub_transaction_date') ),
																'pay_period' => strtotime( $pse_obj->getColumn('pay_stub_transaction_date') ),
															);
				}


				if ( isset($this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id]) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id] = bcadd( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id], $pse_obj->getColumn('amount') );
				} else {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id] = $pse_obj->getColumn('amount');
				}
			}
			unset( $legal_entity_id, $user_id, $date_stamp, $pay_stub_entry_name_id, $pse_obj );

			if ( isset($this->tmp_data['pay_stub_entry']) AND is_array($this->tmp_data['pay_stub_entry']) ) {
				$payments_over_cutoff = $this->getF940Object()->payment_cutoff_amount; //Need to get this from the government form.
				$before_adjustment_tax_rate = $this->getF940Object()->futa_tax_before_adjustment_rate;
				$tax_rate = $this->getF940Object()->futa_tax_rate;
				Debug::Text(' Cutoff: '. $payments_over_cutoff .' Before Adjustment Rate: '. $before_adjustment_tax_rate .' Rate: '. $tax_rate .' Line 10: '. $setup_data['line_10'], __FILE__, __LINE__, __METHOD__, 10);
				if ( $setup_data['line_10'] > 0 ) {
					//Because they had to fill out a separate worksheet which we don't deal with, just average the excluded wages over each loop iteration.
					$excluded_wage_divisor = 0;
					foreach($this->tmp_data['pay_stub_entry'] as $user_id => $data_a) {
						foreach($data_a as $date_stamp => $data_b) {
							$excluded_wage_divisor++;
						}
					}
					$excluded_wage_avg = bcdiv( $setup_data['line_10'], $excluded_wage_divisor );
					Debug::Text(' Excluded Wage Avg: '. $excluded_wage_avg .' Divisor: '. $excluded_wage_divisor, __FILE__, __LINE__, __METHOD__, 10);
					unset($user_id, $data_a, $data_b, $date_stamp);
				}
				if ( $setup_data['line_11'] > 0 ) {
					//Because they had to fill out a separate worksheet which we don't deal with, just average the excluded wages over each loop iteration.
					$credit_reduction_wage_divisor = 0;
					foreach($this->tmp_data['pay_stub_entry'] as $user_id => $data_a) {
						foreach($data_a as $date_stamp => $data_b) {
							$credit_reduction_wage_divisor++;
						}
					}
					$credit_reduction_wage_avg = bcdiv( $setup_data['line_11'], $credit_reduction_wage_divisor );
					Debug::Text(' Excluded Wage Avg: '. $credit_reduction_wage_avg .' Divisor: '. $credit_reduction_wage_divisor, __FILE__, __LINE__, __METHOD__, 10);
					unset($user_id, $data_a, $data_b, $date_stamp);
				}

				foreach($this->tmp_data['pay_stub_entry'] as $user_id => $data_a) {
					foreach($data_a as $date_stamp => $data_b) {
						$legal_entity_id = $data_b['legal_entity_id'];
						$quarter_month = TTDate::getYearQuarterMonth( $date_stamp );
						//Debug::Text(' Quarter Month: '. $quarter_month .' Date: '. TTDate::getDate('DATE+TIME', $date_stamp ), __FILE__, __LINE__, __METHOD__, 10);

						if ( !isset($this->tmp_data['user_total'][$user_id]) ) {
							$this->tmp_data['user_total'][$user_id]['net_payments'] = 0;
							$this->tmp_data['user_total'][$user_id]['excess_payments'] = 0;
						}

						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['exempt_payments'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['exempt_payments']['include_pay_stub_entry_account'],	$form_data['exempt_payments']['exclude_pay_stub_entry_account'] );

						//Net Payments are includes/excludes as they already are excluding exempt payments.
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['net_payments'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['total_payments']['include_pay_stub_entry_account'], $form_data['total_payments']['exclude_pay_stub_entry_account'] );

						//Total Payments must include net payments plus all exempt payments, as its later subtracted out on Line 7.
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['total_payments'] = bcadd( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['exempt_payments'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['net_payments'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['excess_payments']	= $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['adjustment_tax'] = 0;

						//Need to total up payments for each employee so we know when we exceed the limit.
						$this->tmp_data['user_total'][$user_id]['net_payments'] = bcadd( $this->tmp_data['user_total'][$user_id]['net_payments'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['net_payments'] );

						if ( $this->tmp_data['user_total'][$user_id]['excess_payments'] == 0  ) {
							if ( $this->tmp_data['user_total'][$user_id]['net_payments'] > $payments_over_cutoff ) {
								//Debug::Text(' First time over cutoff for User: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
								$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['excess_payments'] = ( $this->tmp_data['user_total'][$user_id]['net_payments'] - $payments_over_cutoff );
								$this->tmp_data['user_total'][$user_id]['excess_payments'] = bcadd( $this->tmp_data['user_total'][$user_id]['excess_payments'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['excess_payments'] );
							}
						} else {
							//Debug::Text(' Next time over cutoff for User: '. $user_id .' Date Stamp: '. $date_stamp, __FILE__, __LINE__, __METHOD__, 10);
							$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['excess_payments'] = bcadd( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['excess_payments'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['net_payments'] );
						}
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['taxable_wages'] = bcsub( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['total_payments'], bcadd( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['exempt_payments'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['excess_payments'] ) );


						//State UI taxable wages - Just need taxable wages up to the federal maximum amount for each state.
						if ( isset($form_data['state_total_payments']) ) {
							foreach( $form_data['state_total_payments'] as $state => $state_psen_ids ) {

								//Make sure some state UI was deducted to include the amounts in the stat calculation.
								$state_deducted_amount = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['state_total_payments'][$state]['pay_stub_entry_account'] );
								if ( $state_deducted_amount > 0 ) {
									if ( !isset($this->tmp_data['user_total'][$user_id]['state'][$state]) ) {
										$this->tmp_data['user_total'][$user_id]['state'][$state]['net_payments'] = 0;
										$this->tmp_data['user_total'][$user_id]['state'][$state]['excess_payments'] = 0;
									}

									$this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ]['state'][ $state ]['total_payments'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['state_total_payments'][ $state ]['include_pay_stub_entry_account'], $form_data['state_total_payments'][ $state ]['exclude_pay_stub_entry_account'] );
									$this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ]['state'][ $state ]['net_payments'] = bcsub( $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ]['state'][ $state ]['total_payments'], $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ]['exempt_payments'] );
									$this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ]['state'][ $state ]['excess_payments'] = $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ]['state'][ $state ]['adjustment_tax'] = 0;

									//Need to total up payments for each employee so we know when we exceed the limit.
									$this->tmp_data['user_total'][ $user_id ]['state'][ $state ]['net_payments'] = bcadd( $this->tmp_data['user_total'][ $user_id ]['state'][ $state ]['net_payments'], $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ]['state'][ $state ]['net_payments'] );

									if ( $this->tmp_data['user_total'][ $user_id ]['state'][ $state ]['excess_payments'] == 0 ) {
										if ( $this->tmp_data['user_total'][ $user_id ]['state'][ $state ]['net_payments'] > $payments_over_cutoff ) {
											//Debug::Text(' First time over cutoff for User: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
											$this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ]['state'][ $state ]['excess_payments'] = ( $this->tmp_data['user_total'][ $user_id ]['state'][ $state ]['net_payments'] - $payments_over_cutoff );
											$this->tmp_data['user_total'][ $user_id ]['state'][ $state ]['excess_payments'] = bcadd( $this->tmp_data['user_total'][ $user_id ]['state'][ $state ]['excess_payments'], $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ]['state'][ $state ]['excess_payments'] );
										}
									} else {
										//Debug::Text(' Next time over cutoff for User: '. $user_id .' Date Stamp: '. $date_stamp, __FILE__, __LINE__, __METHOD__, 10);
										$this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ]['state'][ $state ]['excess_payments'] = bcadd( $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ]['state'][ $state ]['excess_payments'], $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ]['state'][ $state ]['net_payments'] );
									}

									$this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ]['state'][ $state ]['taxable_wages'] = bcsub( $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ]['state'][ $state ]['total_payments'], bcadd( $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ]['exempt_payments'], $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ]['state'][ $state ]['excess_payments'] ) );
								}
							}
							unset( $state, $state_psen_ids );
						}

						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['before_adjustment_tax'] = bcmul( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['taxable_wages'], $before_adjustment_tax_rate );
						if (  isset($setup_data['line_10']) AND $setup_data['line_10'] > 0 ) {
							$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['adjustment_tax'] = bcadd( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['adjustment_tax'], $excluded_wage_avg );
							//Debug::Text('   Line 10: Adjustment Tax: '. $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['adjustment_tax'], __FILE__, __LINE__, __METHOD__, 10);
						}
						if ( isset($setup_data['line_11']) AND $setup_data['line_11'] > 0 ) {
							$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['adjustment_tax'] = bcadd( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['adjustment_tax'], $credit_reduction_wage_avg );
							//Debug::Text('   Line 11: Adjustment Tax: '. $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['adjustment_tax'], __FILE__, __LINE__, __METHOD__, 10);
						}
						//Debug::Text(' Total Adjustment Tax: '. $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['adjustment_tax'], __FILE__, __LINE__, __METHOD__, 10);
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['after_adjustment_tax'] = bcadd( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['before_adjustment_tax'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['adjustment_tax'] );

						//Separate data used for reporting, grouping, sorting, from data specific used for the Form.
						if ( !isset($this->form_data['pay_period'][$legal_entity_id][$quarter_month][$date_stamp]) ) {
							$this->form_data['pay_period'][$legal_entity_id][$quarter_month][$date_stamp] = Misc::preSetArrayValues( array(), array('total_payments', 'exempt_payments', 'excess_payments', 'taxable_wages', 'before_adjustment_tax', 'adjustment_tax', 'after_adjustment_tax' ), 0 );
						}
						$this->form_data['pay_period'][$legal_entity_id][$quarter_month][$date_stamp]['total_payments'] = bcadd( $this->form_data['pay_period'][$legal_entity_id][$quarter_month][$date_stamp]['total_payments'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['total_payments'] );
						$this->form_data['pay_period'][$legal_entity_id][$quarter_month][$date_stamp]['exempt_payments'] = bcadd( $this->form_data['pay_period'][$legal_entity_id][$quarter_month][$date_stamp]['exempt_payments'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['exempt_payments'] );
						$this->form_data['pay_period'][$legal_entity_id][$quarter_month][$date_stamp]['excess_payments'] = bcadd( $this->form_data['pay_period'][$legal_entity_id][$quarter_month][$date_stamp]['excess_payments'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['excess_payments'] );
						$this->form_data['pay_period'][$legal_entity_id][$quarter_month][$date_stamp]['taxable_wages'] = bcadd( $this->form_data['pay_period'][$legal_entity_id][$quarter_month][$date_stamp]['taxable_wages'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['taxable_wages'] );
						$this->form_data['pay_period'][$legal_entity_id][$quarter_month][$date_stamp]['before_adjustment_tax'] = bcadd( $this->form_data['pay_period'][$legal_entity_id][$quarter_month][$date_stamp]['before_adjustment_tax'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['before_adjustment_tax'] );
						$this->form_data['pay_period'][$legal_entity_id][$quarter_month][$date_stamp]['adjustment_tax'] = bcadd( $this->form_data['pay_period'][$legal_entity_id][$quarter_month][$date_stamp]['adjustment_tax'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['adjustment_tax'] );
						$this->form_data['pay_period'][$legal_entity_id][$quarter_month][$date_stamp]['after_adjustment_tax'] = bcadd( $this->form_data['pay_period'][$legal_entity_id][$quarter_month][$date_stamp]['after_adjustment_tax'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['after_adjustment_tax'] );
					}
				}
				unset( $legal_entity_id, $quarter_month, $date_stamp, $user_id, $data_a, $data_b );

				//Total all state amounts
				if ( isset($this->tmp_data['user_total']) ) {
					foreach( $this->tmp_data['user_total'] as $user_id => $tmp_state_data ) {
						if ( isset($tmp_state_data['state']) AND is_array( $tmp_state_data['state'] ) ) {
							foreach( $tmp_state_data['state'] as $state => $state_data ) {
								if (  !isset($this->form_data['state'][$state]['excess_payments']) ) {
									$this->form_data['state'][$state]['excess_payments'] = 0;
								}

								if (  !isset($this->form_data['state'][$state]['net_payments']) ) {
									$this->form_data['state'][$state]['net_payments'] = 0;
								}

								$this->form_data['state'][$state]['excess_payments'] = bcadd( $this->form_data['state'][$state]['excess_payments'], $state_data['excess_payments'] );
								$this->form_data['state'][$state]['net_payments'] = bcadd( $this->form_data['state'][$state]['net_payments'], $state_data['net_payments'] );
							}
						}
					}
					unset( $user_id, $tmp_state_data, $state, $state_data );
				}

				//Total all pay periods by quarter
				if ( isset($this->form_data['pay_period']) ) {
					foreach( $this->form_data['pay_period'] as $legal_entity_id => $legal_entity_data ) {
						foreach( $this->form_data['pay_period'][$legal_entity_id] as $month_id => $pp_data ) {
							$this->form_data['quarter'][$legal_entity_id][$month_id] = Misc::ArrayAssocSum($pp_data, NULL, 8);
						}

						//Total all quarters.
						if ( isset($this->form_data['quarter'][$legal_entity_id]) ) {
							$this->form_data['total'][$legal_entity_id] = Misc::ArrayAssocSum( $this->form_data['quarter'][$legal_entity_id], NULL, 6);
						}
					}
					unset( $legal_entity_id, $legal_entity_data );
				}
			}
		}

		//Debug::Arr($this->form_data, 'Form Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->tmp_data['user_total'], 'Tmp User Total Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->tmp_data, 'Tmp Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Total Rows: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( $this->getColumnDataConfig() );
			$this->tmp_data['user'][$u_obj->getId()]['user_id'] = $u_obj->getId();
			$this->tmp_data['user'][$u_obj->getId()]['legal_entity_id'] = $u_obj->getLegalEntity();
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Get legal entity data for joining.
		$lelf = TTnew( 'LegalEntityListFactory' ); /** @var LegalEntityListFactory $lelf */
		$lelf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' Legal Entity Total Rows: ' . $lelf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $lelf->getRecordCount(), NULL, TTi18n::getText( 'Retrieving Legal Entity Data...' ) );
		if ( $lelf->getRecordCount() > 0 ) {
			foreach( $lelf as $key => $le_obj ) {
				if ( $format == 'html' OR $format == 'pdf' ) {
					$this->tmp_data['legal_entity'][$le_obj->getId()] = Misc::addKeyPrefix( 'legal_entity_', (array)$le_obj->getObjectAsArray( Misc::removeKeyPrefix( 'legal_entity_', $this->getColumnDataConfig() ) ) );
					$this->tmp_data['legal_entity'][$le_obj->getId()]['legal_entity_id'] = $le_obj->getId();
				} else {
					$this->form_data['legal_entity'][$le_obj->getId()] = $le_obj;
				}
				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}
		}

		return TRUE;
	}

	/**
	 * PreProcess data such as calculating additional columns from raw data etc...
	 * @return bool
	 */
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['pay_stub_entry']), NULL, TTi18n::getText('Pre-Processing Data...') );

		//Merge time data with user data
		$key = 0;
		if ( isset($this->tmp_data['pay_stub_entry']) ) {
			foreach( $this->tmp_data['pay_stub_entry'] as $user_id => $level_1 ) {
				foreach( $level_1 as $date_stamp => $row ) {
					if ( isset($this->tmp_data['user'][$user_id]) ) {
						$date_columns = TTDate::getReportDates( NULL, $date_stamp, FALSE, $this->getUserObject(), array('pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date']) );
						$processed_data	 = array(
												//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
												);

						$tmp_legal_array = array();
						if ( isset($this->tmp_data['legal_entity'][$this->tmp_data['user'][$user_id]['legal_entity_id']]) ) {
							$tmp_legal_array = $this->tmp_data['legal_entity'][$this->tmp_data['user'][$user_id]['legal_entity_id']];
						}
						$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data, $tmp_legal_array );
					}

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
					$key++;
				}
			}
			unset($this->tmp_data, $row, $date_columns, $processed_data, $level_1);
		}
		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	/**
	 * @param null $format
	 * @return mixed
	 */
	function _outputPDFForm( $format = NULL ) {
		$show_background = TRUE;
		if ( $format == 'pdf_form_print' ) {
			$show_background = FALSE;
		}
		Debug::Text('Generating Form... Format: '. $format, __FILE__, __LINE__, __METHOD__, 10);

		$setup_data = $this->getFormConfig();
		$filter_data = $this->getFilterConfig();
		//Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);
		$filter_data['end_date'] = isset( $filter_data['end_date'] ) ? $filter_data['end_date'] : NULL;
		$current_company = $this->getUserObject()->getCompanyObject();
		if ( !is_object($current_company) ) {
			Debug::Text('Invalid company object...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ( isset($this->form_data['total']) ) {
			foreach($this->form_data['total'] as $legal_entity_id => $legal_entity_data ) {
				if ( isset( $this->form_data['legal_entity'][$legal_entity_id] ) == FALSE ) {
					Debug::Text( 'Missing Legal Entity: ' . $legal_entity_id, __FILE__, __LINE__, __METHOD__, 10 );
					continue;
				}

				if ( isset( $this->form_data['remittance_agency'][$legal_entity_id] ) == FALSE ) {
					Debug::Text( 'Missing Remittance Agency: ' . $legal_entity_id, __FILE__, __LINE__, __METHOD__, 10 );
					continue;
				}

				$legal_entity_obj = $this->form_data['legal_entity'][$legal_entity_id];

				if ( $format == 'efile_xml' ) {
					$return940 = $this->getRETURN940Object();

					$return940->TaxPeriodEndDate = TTDate::getDate('Y-m-d', TTDate::getEndDayEpoch( $filter_data['end_date'] ));
					$return940->ReturnType = '';
					$return940->ein = $this->form_data['remittance_agency'][$legal_entity_id]['00']->getPrimaryIdentification(); //Always use EIN from Federal Agency.
					$return940->BusinessName1 = '';
					$return940->BusinessNameControl = '';

					$return940->AddressLine = $legal_entity_obj->getAddress1() . ' ' . $legal_entity_obj->getAddress2();
					$return940->City = $legal_entity_obj->getCity();
					$return940->State = $legal_entity_obj->getProvince();
					$return940->ZIPCode = $legal_entity_obj->getPostalCode();

					$this->getFormObject()->addForm( $return940 );
				}

				$f940 = $this->getF940Object();
				$f940->setDebug(FALSE);
				$f940->setShowBackground( $show_background );

				$f940->year = TTDate::getYear( $filter_data['end_date'] );

				$f940->ein = ( isset( $this->form_data['remittance_agency'][$legal_entity_id]['00'] ) ? $this->form_data['remittance_agency'][$legal_entity_id]['00']->getPrimaryIdentification() : NULL ); //Always use EIN from Federal Agency.
				$f940->name = $legal_entity_obj->getLegalName();
				$f940->trade_name = $legal_entity_obj->getTradeName();
				$f940->address = $legal_entity_obj->getAddress1() . ' ' . $legal_entity_obj->getAddress2();
				$f940->city = $legal_entity_obj->getCity();
				$f940->state = $legal_entity_obj->getProvince();
				$f940->zip_code = $legal_entity_obj->getPostalCode();

				if ( isset($setup_data['return_type']) AND is_array($setup_data['return_type']) ) {
					$return_type_arr = array();
					foreach( $setup_data['return_type'] as $return_type ) {
						switch ( $return_type ) {
							case 10: //Amended
								$return_type_arr[] = 'a';
								break;
							case 20: //Successor
								$return_type_arr[] = 'b';
								break;
							case 30: //No Payments
								$return_type_arr[] = 'c';
								break;
							case 40: //Final
								$return_type_arr[] = 'd';
								break;
						}
					}

					$f940->return_type = $return_type_arr;
				}

				if ( isset( $this->form_data['remittance_agency_states'][$legal_entity_id] ) AND count( $this->form_data['remittance_agency_states'][$legal_entity_id] ) > 1 ) {
					$f940->l1b = TRUE; //Let them set this manually.
				} else {
					$f940->l1a = key($this->form_data['remittance_agency_states'][$legal_entity_id]);
				}

				if ( isset( $this->form_data['remittance_agency_states'][$legal_entity_id] ) ) {
					//Determine which states have FUTA withholdings.
					if ( isset($this->form_data['state']) AND count($this->form_data['state']) > 1) {
						foreach( $this->form_data['state'] as $state => $tmp_state_data ) {
							$state_amounts[$state] = bcsub( $tmp_state_data['net_payments'], $tmp_state_data['excess_payments'] );
						}

						if ( isset($state_amounts) ) {
							$f940sa = $this->getFormObject()->getFormObject( '940sa', 'US' );
							$f940sa->setDebug(FALSE);
							$f940sa->setShowBackground( $show_background );

							$f940sa->year = $f940->year;
							$f940sa->ein = $f940->ein;
							$f940sa->name = $f940->name;

							Debug::Arr($state_amounts, 'State amounts: ', __FILE__, __LINE__, __METHOD__, 10);
							$f940sa->state_amounts = $state_amounts;

							$f940->l11 = $f940sa->calcTotal(); //Pass the Schedule A total back to the Form 940 on line 11.
						}
					}
					unset( $state, $tmp_state_data );

//					//Test Amounts for each state.
//					$f940sa = $this->getFormObject()->getFormObject( '940sa', 'US' );
//					$f940sa->setDebug(FALSE);
//					$f940sa->setShowBackground( $show_background );
//					$states = $this->getUserObject()->getCompanyObject()->getOptions('province', 'US' );
//					foreach( $states as $state_code => $name ) {
//						if ( !isset($state_amounts[$state_code]) ) {
//							$state_amounts[$state_code] = rand(100,1000);
//						}
//					}
//					$state_amounts['VI'] = rand(100,1000);
//					$state_amounts['PR'] = rand(100,1000);
//					Debug::Arr($state_amounts, 'State amounts: ', __FILE__, __LINE__, __METHOD__, 10);
//					$f940sa->state_amounts = $state_amounts;
//					$f940->l11 = $f940sa->calcTotal();
				}

				//Exempt payment check boxes
				if ( isset( $setup_data['exempt_payment'] ) AND is_array( $setup_data['exempt_payment'] ) ) {
					foreach( $setup_data['exempt_payment'] as $return_type ) {
						switch ( $return_type ) {
							case 10: //Fringe
								$f940->l4a = TRUE;
								break;
							case 20: //Group life insurance
								$f940->l4b = TRUE;
								break;
							case 30: //Retirement/Pension
								$f940->l4c = TRUE;
								break;
							case 40: //Dependant care
								$f940->l4d = TRUE;
								break;
							case 50: //Other
								$f940->l4e = TRUE;
								break;
						}
					}
				}

				//Debug::Arr($this->form_data['quarter'], 'Final Data for Form: ', __FILE__, __LINE__, __METHOD__, 10);
				if ( isset($this->form_data) AND count($this->form_data) >= 6 ) {
					$f940->l3 = $this->form_data['total'][$legal_entity_id]['total_payments'];
					$f940->l4 = $this->form_data['total'][$legal_entity_id]['exempt_payments'];
					$f940->l5 = $this->form_data['total'][$legal_entity_id]['excess_payments'];

					$f940->l9 = ( isset( $setup_data['line_9'] ) AND $setup_data['line_9'] == TRUE ) ? TRUE : FALSE;
					$f940->l10 = ( isset( $setup_data['line_10'] ) ) ? $setup_data['line_10'] : NULL;
					//$f940->l11 = ( isset( $setup_data['line_11'] ) ) ? $setup_data['line_11'] : NULL; //Calculated from Schedule A now.

					$f940->l13 = ( isset( $setup_data['tax_deposited'] ) AND $setup_data['tax_deposited'] != '' ) ? $setup_data['tax_deposited'] : NULL;

					$f940->l15b = TRUE;

					if ( isset($this->form_data['quarter'][$legal_entity_id][1]['after_adjustment_tax']) ) {
						$f940->l16a = $this->form_data['quarter'][$legal_entity_id][1]['after_adjustment_tax'];
					}
					if ( isset($this->form_data['quarter'][$legal_entity_id][2]['after_adjustment_tax']) ) {
						$f940->l16b = $this->form_data['quarter'][$legal_entity_id][2]['after_adjustment_tax'];
					}
					if ( isset($this->form_data['quarter'][$legal_entity_id][3]['after_adjustment_tax']) ) {
						$f940->l16c = $this->form_data['quarter'][$legal_entity_id][3]['after_adjustment_tax'];
					}
					if ( isset($this->form_data['quarter'][$legal_entity_id][4]['after_adjustment_tax']) ) {
						$f940->l16d = $this->form_data['quarter'][$legal_entity_id][4]['after_adjustment_tax'];
					}
				} else {
					Debug::Arr($this->data, 'Invalid Form Data: ', __FILE__, __LINE__, __METHOD__, 10);
				}

				$this->getFormObject()->addForm( $f940 );

				if ( isset($f940sa) AND is_object( $f940sa ) ) {
					$this->getFormObject()->addForm( $f940sa );
				}

				if ( $format == 'efile_xml' ) {
					$output_format = 'XML';
					$file_name = '940_efile_' . date( 'Y_m_d' ) . '_' . Misc::sanitizeFileName( $this->form_data['legal_entity'][ $legal_entity_id ]->getTradeName() ) . '.xml';
					$mime_type = 'applications/octet-stream'; //Force file to download.
				} else {
					$output_format = 'PDF';
					$file_name = $this->file_name . '_' . Misc::sanitizeFileName( $this->form_data['legal_entity'][ $legal_entity_id ]->getTradeName() ) . '.pdf';
					$mime_type = $this->file_mime_type;
				}

				$output = $this->getFormObject()->output( $output_format );
				$file_arr[] = array('file_name' => $file_name, 'mime_type' => $mime_type, 'data' => $output);

				$this->clearFormObject();
				$this->clearF940Object();
				$this->clearRETURN940Object();
			}
		}

		if ( isset($file_name) AND $file_name != '' ) {
			$zip_filename = explode( '.', $file_name );
			if ( isset( $zip_filename[ ( count( $zip_filename ) - 1 ) ] ) ) {
				$zip_filename = str_replace( '.', '', str_replace( $zip_filename[ ( count( $zip_filename ) - 1 ) ], '', $file_name ) ) . '.zip';
			} else {
				$zip_filename = str_replace( '.', '', $file_name ) . '.zip';
			}

			return Misc::zip( $file_arr, $zip_filename, TRUE );
		}

		Debug::Text(' Returning FALSE!', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	/**
	 * @param null $format
	 * @return array|bool
	 */
	function _output( $format = NULL ) {
		if ( $format == 'pdf_form' OR $format == 'pdf_form_print' OR $format == 'efile_xml' ) {
			return $this->_outputPDFForm( $format );
		} else {
			return parent::_output( $format );
		}

	}
}
?>
