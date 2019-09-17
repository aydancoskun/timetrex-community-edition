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
class FormW2Report extends Report {
	/**
	 * FormW2Report constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText('Form W2 Report');
		$this->file_name = 'form_w2';

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
				AND $this->getPermissionObject()->Check('report', 'view_formW2', $user_id, $company_id ) ) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	protected function _validateConfig() {
		$config = $this->getConfig();

		//Make sure some time period is selected.
		if ( ( !isset($config['filter']['time_period']) AND !isset($config['filter']['pay_period_id']) ) OR ( isset($config['filter']['time_period']) AND isset($config['filter']['time_period']['time_period']) AND $config['filter']['time_period']['time_period'] == TTUUID::getZeroId() ) ) {
			$this->validator->isTrue( 'time_period', FALSE, TTi18n::gettext('No time period defined for this report') );
		}

		return TRUE;
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
										'-1100-pdf_form' => TTi18n::gettext('Employee (One Employee/Page)'),
										'-1110-pdf_form_government' => TTi18n::gettext('Government (Multiple Employees/Page)'),
										'-1120-efile' => TTi18n::gettext('eFile'),
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
										//'-2005-payroll_remittance_agency_id' => TTi18n::gettext('Remittance Agency'),
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
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), NULL, 'FormW2Report', 'custom_column' );
					if ( is_array($custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('filter_column_type_ids'), NULL, 'FormW2Report', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('dynamic_format_ids'), 'FormW2Report', 'custom_column' );
					if ( is_array($report_dynamic_custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('static_format_ids'), 'FormW2Report', 'custom_column' );
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
										'-0900-legal_entity_legal_name' => TTi18n::gettext('Legal Entity Name'),
										'-0910-legal_entity_trade_name' => TTi18n::gettext( 'Legal Entity Trade Name' ),

										'-1000-first_name' => TTi18n::gettext('First Name'),
										'-1001-middle_name' => TTi18n::gettext('Middle Name'),
										'-1002-last_name' => TTi18n::gettext('Last Name'),
										'-1005-full_name' => TTi18n::gettext('Full Name'),
										'-1030-employee_number' => TTi18n::gettext('Employee #'),
										'-1035-sin' => TTi18n::gettext('SIN/SSN'),
										'-1040-status' => TTi18n::gettext('Status'),
										'-1050-title' => TTi18n::gettext('Title'),
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

										'-1510-address1' => TTi18n::gettext('Address 1'),
										'-1512-address2' => TTi18n::gettext('Address 2'),
										'-1520-city' => TTi18n::gettext('City'),
										'-1522-province' => TTi18n::gettext('Province/State'),
										'-1524-country' => TTi18n::gettext('Country'),
										'-1526-postal_code' => TTi18n::gettext('Postal Code'),
										'-1530-work_phone' => TTi18n::gettext('Work Phone'),
										'-1540-work_phone_ext' => TTi18n::gettext('Work Phone Ext'),
										'-1550-home_phone' => TTi18n::gettext('Home Phone'),
										'-1560-home_email' => TTi18n::gettext('Home Email'),
										'-1590-note' => TTi18n::gettext('Note'),
										'-1595-tag' => TTi18n::gettext('Tags'),
								);

				$retval = array_merge( $retval, $this->getOptions('date_columns'), (array)$this->getOptions('report_static_custom_column') );
				ksort($retval);
				break;
			case 'dynamic_columns':
				$retval = array(
										//Dynamic - Aggregate functions can be used
										'-2010-l1' => TTi18n::gettext('Wages (1)'),
										'-2020-l2' => TTi18n::gettext('Federal Income Tax (2)'),
										'-2030-l3' => TTi18n::gettext('Social Security Wages (3)'),
										'-2040-l4' => TTi18n::gettext('Social Security Tax (4)'),
										'-2040-l7' => TTi18n::gettext('Social Security Tips (7)'),
										'-2050-l5' => TTi18n::gettext('Medicare Wages (5)'),
										'-2060-l6' => TTi18n::gettext('Medicare Tax (6)'),
										'-2070-l8' => TTi18n::gettext('Allocated Tips (8)'),
										'-2080-l10' => TTi18n::gettext('Dependent Care Benefits (10)'),
										'-2090-l11' => TTi18n::gettext('Nonqualified Plans (11)'),
										'-2100-l12a' => TTi18n::gettext('Box 12a'),
										'-2110-l12b' => TTi18n::gettext('Box 12b'),
										'-2120-l12c' => TTi18n::gettext('Box 12c'),
										'-2130-l12d' => TTi18n::gettext('Box 12d'),

										'-2200-l14a' => TTi18n::gettext('Box 14a'),
										'-2210-l14b' => TTi18n::gettext('Box 14b'),
										'-2220-l14c' => TTi18n::gettext('Box 14c'),
										'-2230-l14d' => TTi18n::gettext('Box 14d'),
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
			case 'kind_of_employer':
				$retval = array(
											'-1010-N' => TTi18n::getText('None Apply'),
											'-1020-T' => TTi18n::getText('501c Non-Gov\'t'),
											'-1030-S' => TTi18n::getText('State/Local Non-501c'),
											'-1040-Y' => TTi18n::getText('State/Local 501c'),
											'-1050-F' => TTi18n::getText('Federal Gov\'t'),
										);
				break;
			case 'templates':
				$retval = array(
										//'-1010-by_month' => TTi18n::gettext('by Month'),
										'-1020-by_employee' => TTi18n::gettext('by Employee'),
										'-1030-by_branch' => TTi18n::gettext('by Branch'),
										'-1040-by_department' => TTi18n::gettext('by Department'),
										'-1050-by_branch_by_department' => TTi18n::gettext('by Branch/Department'),

										//'-1060-by_month_by_employee' => TTi18n::gettext('by Month/Employee'),
										//'-1070-by_month_by_branch' => TTi18n::gettext('by Month/Branch'),
										//'-1080-by_month_by_department' => TTi18n::gettext('by Month/Department'),
										//'-1090-by_month_by_branch_by_department' => TTi18n::gettext('by Month/Branch/Department'),
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
	function getFW2Object() {
		if ( !isset($this->form_obj['fw2']) OR !is_object($this->form_obj['fw2']) ) {
			$this->form_obj['fw2'] = $this->getFormObject()->getFormObject( 'w2', 'US' );
			return $this->form_obj['fw2'];
		}

		return $this->form_obj['fw2'];
	}

	/**
	 * @return bool
	 */
	function clearFW2Object() {
		$this->form_obj['fw2'] = FALSE;

		return TRUE;
	}

	/**
	 * @return mixed
	 */
	function getFW3Object() {
		if ( !isset($this->form_obj['fw3']) OR !is_object($this->form_obj['fw3']) ) {
			$this->form_obj['fw3'] = $this->getFormObject()->getFormObject( 'w3', 'US' );
			return $this->form_obj['fw3'];
		}

		return $this->form_obj['fw3'];
	}

	/**
	 * @return bool
	 */
	function clearFW3Object() {
		$this->form_obj['fw3'] = FALSE;

		return TRUE;
	}

	/**
	 * @return mixed
	 */
	function getRETURN1040Object() {
		if ( !isset($this->form_obj['return1040']) OR !is_object($this->form_obj['return1040']) ) {
			$this->form_obj['return1040'] = $this->getFormObject()->getFormObject( 'RETURN1040', 'US' );
			return $this->form_obj['return1040'];
		}

		return $this->form_obj['return1040'];
	}

	/**
	 * @return bool
	 */
	function clearRETURN1040Object() {
		$this->form_obj['return1040'] = FALSE;

		return TRUE;
	}


	/**
	 * @return array
	 */
	function formatFormConfig() {
		$default_include_exclude_arr = array( 'include_pay_stub_entry_account' => array(), 'exclude_pay_stub_entry_account' => array() );

		$default_arr = array(
				'l1' => $default_include_exclude_arr,
				'l2' => $default_include_exclude_arr,
				'l3' => $default_include_exclude_arr,
				'l4' => $default_include_exclude_arr,
				'l5' => $default_include_exclude_arr,
				'l6' => $default_include_exclude_arr,
				'l7' => $default_include_exclude_arr,
				'l8' => $default_include_exclude_arr,
				'l9' => $default_include_exclude_arr,
				'l10' => $default_include_exclude_arr,
				'l11' => $default_include_exclude_arr,
				'l12a' => $default_include_exclude_arr,
				'l12b' => $default_include_exclude_arr,
				'l12c' => $default_include_exclude_arr,
				'l12d' => $default_include_exclude_arr,
				'l13b' => array('company_deduction'),
				'l14' => $default_include_exclude_arr,
				'l14a' => $default_include_exclude_arr,
				'l14b' => $default_include_exclude_arr,
				'l14c' => $default_include_exclude_arr,
				'l14d' => $default_include_exclude_arr,
				'l15' => $default_include_exclude_arr,
				'l16' => $default_include_exclude_arr,
				'l17' => $default_include_exclude_arr,
				'l18' => $default_include_exclude_arr,
				'l19' => $default_include_exclude_arr,
				'l20' => $default_include_exclude_arr,
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
		$this->tmp_data = array( 'pay_stub_entry' => array(), 'remittance_agency' => array() );

		$filter_data = $this->getFilterConfig();
		$form_data = $this->formatFormConfig();
		$tax_deductions = array();
		$user_deduction_data = array();

		//
		//Figure out state/locality wages/taxes.
		//  Make sure state tax/deduction records come before district so they can be matched.
		//
		$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
		$cdlf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), array(10, 20), 10, NULL, array( 'calculation_id' => 'asc', 'calculation_order' => 'asc' ) );
		if ( $cdlf->getRecordCount() > 0 ) {
			foreach( $cdlf as $cd_obj ) {
				if ( in_array( $cd_obj->getCalculation(), array(200, 300) ) ) { //Only consider State/District records.
					//Debug::Text('Company Deduction: ID: '. $cd_obj->getID() .' Name: '. $cd_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);
					$tax_deductions[$cd_obj->getId()] = $cd_obj;

					//Need to determine start/end dates for each CompanyDeduction/User pair, so we can break down total wages earned in the date ranges.
					$udlf = TTnew( 'UserDeductionListFactory' ); /** @var UserDeductionListFactory $udlf */
					$udlf->getByCompanyIdAndCompanyDeductionId( $cd_obj->getCompany(), $cd_obj->getId() );
					if ( $udlf->getRecordCount() > 0 ) {
						foreach( $udlf as $ud_obj ) {
							if ( $ud_obj->getStartDate() != '' OR $ud_obj->getEndDate() != '' ) {
								//Debug::Text('  User Deduction: ID: '. $ud_obj->getID() .' User ID: '. $ud_obj->getUser(), __FILE__, __LINE__, __METHOD__, 10);
								$user_deduction_data[$ud_obj->getCompanyDeduction()][$ud_obj->getUser()] = $ud_obj;
							}
						}
					}
				}
			}
			//Debug::Arr($tax_deductions, 'Tax Deductions: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($user_deduction_data, 'User Deductions: ', __FILE__, __LINE__, __METHOD__, 10);
		}
		unset( $cd_obj);

		//Get users assigned to Box 13b (Retirement Plan) tax/deductions.
		if ( isset($form_data['l13b']['company_deduction']) ) {
			$udlf = TTnew( 'UserDeductionListFactory' ); /** @var UserDeductionListFactory $udlf */
			$udlf->getByCompanyIdAndCompanyDeductionId( $this->getUserObject()->getCompany(), $form_data['l13b']['company_deduction'] );
			if ($udlf->getRecordCount() > 0 ) {
				foreach( $udlf as $ud_obj ) {
					if ( ( $ud_obj->getStartDate() == '' OR $ud_obj->getStartDate() <= $filter_data['end_date'] )
							AND ( $ud_obj->getEndDate() == '' OR $ud_obj->getEndDate() >= $filter_data['start_date'] ) ) {
						$this->form_data['l13_user_deduction_data'][ $ud_obj->getUser() ] = TRUE;
					}
				}
			}
		}
		unset( $udlf, $ud_obj);


		$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */
		$pself->getAPIReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $pself->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		if ( $pself->getRecordCount() > 0 ) {
			foreach( $pself as $key => $pse_obj ) {
				$legal_entity_id = $pse_obj->getColumn('legal_entity_id');
				$user_id = $pse_obj->getColumn('user_id');
				$date_stamp = TTDate::strtotime( $pse_obj->getColumn('pay_stub_end_date') );
				$pay_stub_entry_name_id = $pse_obj->getPayStubEntryNameId();

				if ( !isset($this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp] = array(
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

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}

			if ( isset( $this->tmp_data['pay_stub_entry'] ) AND is_array( $this->tmp_data['pay_stub_entry'] ) ) {
				foreach ( $this->tmp_data['pay_stub_entry'] as $user_id => $data_a ) {
					foreach ( $data_a as $date_stamp => $data_b ) {
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l1'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l1']['include_pay_stub_entry_account'], $form_data['l1']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l2'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l2']['include_pay_stub_entry_account'], $form_data['l2']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l3'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l3']['include_pay_stub_entry_account'], $form_data['l3']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l4'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l4']['include_pay_stub_entry_account'], $form_data['l4']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l5'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l5']['include_pay_stub_entry_account'], $form_data['l5']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l6'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l6']['include_pay_stub_entry_account'], $form_data['l6']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l7'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l7']['include_pay_stub_entry_account'], $form_data['l7']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l8'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l8']['include_pay_stub_entry_account'], $form_data['l8']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l10'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l10']['include_pay_stub_entry_account'], $form_data['l10']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l11'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l11']['include_pay_stub_entry_account'], $form_data['l11']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l12a'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l12a']['include_pay_stub_entry_account'], $form_data['l12a']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l12b'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l12b']['include_pay_stub_entry_account'], $form_data['l12b']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l12c'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l12c']['include_pay_stub_entry_account'], $form_data['l12c']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l12d'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l12d']['include_pay_stub_entry_account'], $form_data['l12d']['exclude_pay_stub_entry_account'] );

						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l14a'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l14a']['include_pay_stub_entry_account'], $form_data['l14a']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l14b'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l14b']['include_pay_stub_entry_account'], $form_data['l14b']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l14c'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l14c']['include_pay_stub_entry_account'], $form_data['l14c']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l14d'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l14d']['include_pay_stub_entry_account'], $form_data['l14d']['exclude_pay_stub_entry_account'] );

						if ( is_array($data_b['psen_ids']) AND empty($tax_deductions) == FALSE ) {
							//Support multiple tax/deductions that deposit to the same pay stub account.
							//Also make sure we handle tax/deductions that may not have anything deducted/withheld, but do have wages to be displayed.
							//  For example an employee not earning enough to have State income tax taken off yet.
							//Now that user_deduction supports start/end dates per employee, we could use that to better handle employees switching between Tax/Deduction records mid-year
							//  while still accounting for cases where nothing is deducted/withheld but still needs to be displayed.
							foreach ( $tax_deductions as $tax_deduction_id => $cd_obj ) {
								if ( $legal_entity_id == $cd_obj->getLegalEntity() OR $legal_entity_id == TTUUID::getZeroID() ) {
									//Found Tax/Deduction associated with this pay stub account.
									$tax_withheld_amount = Misc::calculateMultipleColumns( $data_b['psen_ids'], array($cd_obj->getPayStubEntryAccount()) );
									if ( $tax_withheld_amount > 0 OR in_array( $user_id, (array)$cd_obj->getUser() ) ) {
										Debug::Text( 'Found User ID: ' . $user_id . ' in Tax Deduction Name: ' . $cd_obj->getName() . '(' . $cd_obj->getID() . ') Calculation ID: ' . $cd_obj->getCalculation() . ' Withheld Amount: ' . $tax_withheld_amount, __FILE__, __LINE__, __METHOD__, 10 );

										$is_active_date = TRUE;
										if ( isset( $user_deduction_data ) AND isset( $user_deduction_data[ $tax_deduction_id ] ) AND isset( $user_deduction_data[ $tax_deduction_id ][ $user_id ] ) ) {
											$is_active_date = $cdlf->isActiveDate( $user_deduction_data[ $tax_deduction_id ][ $user_id ], $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ]['pay_period_end_date'] );
											Debug::Text( '  Date Restrictions Found... Is Active: ' . (int)$is_active_date . ' Date: ' . TTDate::getDate( 'DATE', $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ]['pay_period_end_date'] ), __FILE__, __LINE__, __METHOD__, 10 );
										}

										//State records must come before district, so they can be matched up.
										if ( $cd_obj->getCalculation() == 200 AND $cd_obj->getProvince() != '' ) {
											//determine how many district/states currently exist for this employee.
											foreach ( range( 'a', 'z' ) as $z ) {
												//Make sure we are able to combine multiple state Tax/Deduction amounts together in the case
												//where they are using different Pay Stub Accounts for the State Income Tax and State Addl. Income Tax PSA's.
												//Need to have per user state detection vs per user/date, so we can make sure the state_id is unique across all possible data.
												if ( !( isset( $this->tmp_data['state_ids'][ $user_id ][ 'l17' . $z ] ) AND isset( $this->tmp_data['state_ids'][ $user_id ][ 'l15' . $z . '_state' ] ) AND $this->tmp_data['state_ids'][ $user_id ][ 'l15' . $z . '_state' ] != $cd_obj->getProvince() ) ) {
													$state_id = $z;
													break;
												}
											}

											//State Wages/Taxes
											$this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l15' . $state_id . '_state' ] = $this->tmp_data['state_ids'][ $user_id ][ 'l15' . $state_id . '_state' ] = $cd_obj->getProvince();

											if ( $is_active_date == TRUE ) {
												if ( !isset( $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l16' . $state_id ] ) OR ( isset( $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l16' . $state_id ] ) AND $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l16' . $state_id ] == 0 ) ) {
													$this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l16' . $state_id ] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $cd_obj->getIncludePayStubEntryAccount(), $cd_obj->getExcludePayStubEntryAccount() );
												}
											}
											if ( !isset( $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l17' . $state_id ] ) ) {
												$this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l17' . $state_id ] = $this->tmp_data['state_ids'][ $user_id ][ 'l17' . $state_id ] = 0;
											}
											//Just combine the tax withheld part, not the wages/earnings, as we don't want to double up on that.
											$this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l17' . $state_id ] = bcadd( $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l17' . $state_id ], Misc::calculateMultipleColumns( $data_b['psen_ids'], array($cd_obj->getPayStubEntryAccount()) ) );
											$this->tmp_data['state_ids'][ $user_id ][ 'l17' . $state_id ] = bcadd( $this->tmp_data['state_ids'][ $user_id ][ 'l17' . $state_id ], $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l17' . $state_id ] );

											//Debug::Text('State ID: '. $state_id .' Withheld: '. $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l17'. $state_id], __FILE__, __LINE__, __METHOD__, 10);
										} elseif ( $cd_obj->getCalculation() == 300 AND ( $cd_obj->getDistrictName() != '' OR $cd_obj->getCompanyValue1() != '' ) ) {
											if ( $cd_obj->getDistrictName() == '' AND $cd_obj->getCompanyValue1() != '' ) {
												$district_name = $cd_obj->getCompanyValue1();
											} else {
												$district_name = $cd_obj->getDistrictName();
											}

											foreach ( range( 'a', 'z' ) as $z ) {
												//Make sure we are able to combine multiple district Tax/Deduction amounts together in the case
												//where they are using different Pay Stub Accounts for the District Income Tax and District Addl. Income Tax PSA's.
												//Need to have per user district detection vs per user/date, so we can make sure the district_id is unique across all possible data.
												//  Make sure we link the district to the state.
												if ( !isset( $this->tmp_data['state_ids'][ $user_id ][ 'l15' . $z . '_state' ] ) OR ( isset( $this->tmp_data['state_ids'][ $user_id ][ 'l15' . $z . '_state' ] ) AND $this->tmp_data['state_ids'][ $user_id ][ 'l15' . $z . '_state' ] == $cd_obj->getProvince() ) ) {
													if ( !( isset( $this->tmp_data['district_ids'][ $user_id ][ 'l19' . $z ] ) AND isset( $this->tmp_data['district_ids'][ $user_id ][ 'l20' . $z . '_district' ] ) AND $this->tmp_data['district_ids'][ $user_id ][ 'l20' . $z . '_district' ] != $district_name ) ) {
														$district_id = $z;
														break;
													}
												} else {
													Debug::Text( '  Multi-State employee, skipping mismatched StateID for District: ' . $z . ' Tax State: ' . $cd_obj->getProvince(), __FILE__, __LINE__, __METHOD__, 10 );
												}
											}

											if ( !isset( $district_id ) ) {
												Debug::Text( '  District ID not set, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
												continue;
											}

											//State
											if ( !isset( $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l15' . $district_id . '_state' ] ) ) {
												$this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l15' . $district_id . '_state' ] = $cd_obj->getProvince();
											}

											//District Wages/Taxes
											$this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l20' . $district_id . '_district' ] = $this->tmp_data['district_ids'][ $user_id ][ 'l20' . $district_id . '_district' ] = $district_name;

											if ( $is_active_date == TRUE ) {
												if ( !isset( $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l18' . $district_id ] ) OR ( isset( $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l18' . $district_id ] ) AND $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l18' . $district_id ] == 0 ) ) {
													$this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l18' . $district_id ] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $cd_obj->getIncludePayStubEntryAccount(), $cd_obj->getExcludePayStubEntryAccount() );
												}
											}
											if ( !isset( $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l19' . $district_id ] ) ) {
												$this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l19' . $district_id ] = $this->tmp_data['district_ids'][ $user_id ][ 'l19' . $district_id ] = 0;
											}
											//Just combine the tax withheld part, not the wages/earnings, as we don't want to double up on that.
											$this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l19' . $district_id ] = bcadd( $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l19' . $district_id ], Misc::calculateMultipleColumns( $data_b['psen_ids'], array($cd_obj->getPayStubEntryAccount()) ) );
											$this->tmp_data['district_ids'][ $user_id ][ 'l19' . $district_id ] = bcadd( $this->tmp_data['district_ids'][ $user_id ][ 'l19' . $district_id ], $this->tmp_data['pay_stub_entry'][ $user_id ][ $date_stamp ][ 'l19' . $district_id ] );

											//Debug::Text('District Name: '. $district_name .' ID: '. $district_id .' Withheld: '. $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l19'. $district_id], __FILE__, __LINE__, __METHOD__, 10);
										} else {
											Debug::Text( 'Not State or Local income tax: ' . $cd_obj->getId() . ' Calculation: ' . $cd_obj->getCalculation() . ' District: ' . $cd_obj->getDistrictName() . ' UserValue5: ' . $cd_obj->getUserValue5() . ' CompanyValue1: ' . $cd_obj->getCompanyValue1(), __FILE__, __LINE__, __METHOD__, 10 );
										}
									} else {
										Debug::Text( 'User is either not assigned to Tax/Deduction, or they do not have any calculated amounts...', __FILE__, __LINE__, __METHOD__, 10 );
									}
									unset( $tax_withheld_amount );
								} else {
									Debug::Text( 'User not assigned to Legal Entity for this CompanyDeduction record, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
								}
							}
							unset( $state_id, $district_id, $district_name, $tax_deduction_id, $cd_obj );
						}
					}
				}
			}
		}

		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->tmp_data, 'Tmp Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Total Rows: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, TTi18n::getText( 'Retrieving Employee Data...' ) );
		foreach ( $ulf as $key => $u_obj ) {
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
			foreach ( $lelf as $key => $le_obj ) {
				if ( $format == 'html' OR $format == 'pdf' ) {
					$this->tmp_data['legal_entity'][$le_obj->getId()] = Misc::addKeyPrefix( 'legal_entity_', (array)$le_obj->getObjectAsArray( Misc::removeKeyPrefix( 'legal_entity_', $this->getColumnDataConfig() ) ) );
					$this->tmp_data['legal_entity'][$le_obj->getId()]['legal_entity_id'] = $le_obj->getId();
				} else {
					$this->form_data['legal_entity'][$le_obj->getId()] = $le_obj;
				}
				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}
		}

		//Get remittance agency for joining.
		$filter_data['type_id'] = array(10, 20, 30); //federal, state and local/city.
		$filter_data['country'] = array('US'); //US federal
		$ralf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $ralf */
		$ralf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' Remittance Agency Total Rows: ' . $ralf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $lelf->getRecordCount(), NULL, TTi18n::getText( 'Retrieving Remittance Agency Data...' ) );
		if ( $ralf->getRecordCount() > 0 ) {
			foreach ( $ralf as $key => $ra_obj ) {
				if ( $ra_obj->parseAgencyID( NULL, 'id') == 10 ) {
					if ( in_array( $ra_obj->getType(), array(10, 20) ) ) {
						$province_id = ( $ra_obj->getType() == 10 ) ? '00' : $ra_obj->getProvince();
						$this->form_data['remittance_agency'][ $ra_obj->getLegalEntity() ][ $province_id ] = $ra_obj->getId(); //Map province to a specific remittance object below.
					}

					$this->form_data['remittance_agency_obj'][ $ra_obj->getId() ] = $ra_obj;
				}
				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}
			unset($province_id);
		}

		return TRUE;
	}

	/**
	 * PreProcess data such as calculating additional columns from raw data etc...
	 * @param null $format
	 * @return bool
	 */
	function _preProcess( $format = NULL ) {
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['pay_stub_entry']), NULL, TTi18n::getText('Pre-Processing Data...') );

		//Merge time data with user data
		$key = 0;
		if ( isset($this->tmp_data['pay_stub_entry']) AND isset($this->tmp_data['user']) ) {
			$sort_columns = $this->getSortConfig();

			foreach( $this->tmp_data['pay_stub_entry'] as $user_id => $level_1 ) {
				if ( isset($this->tmp_data['user'][$user_id]) ) {
					foreach ( $level_1 as $date_stamp => $row ) {
						$date_columns = TTDate::getReportDates( NULL, $date_stamp, FALSE, $this->getUserObject(), array('pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date']) );
						$processed_data = array();

						$tmp_legal_array = array();
						if ( isset($this->tmp_data['legal_entity'][$this->tmp_data['user'][$user_id]['legal_entity_id']]) ) {
							$tmp_legal_array = $this->tmp_data['legal_entity'][$this->tmp_data['user'][$user_id]['legal_entity_id']];
						}
						$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data, $tmp_legal_array );

						$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
						$key++;
					}
				}
			}
			unset($this->tmp_data, $row, $date_columns, $processed_data, $tmp_legal_array);

			//Total data per employee for the W2 forms. Just include the columns that are necessary for the form.
			if ( is_array($this->data) AND !($format == 'html' OR $format == 'pdf') ) {
				Debug::Text('Calculating Form Data...', __FILE__, __LINE__, __METHOD__, 10);
				foreach( $this->data as $row ) {
					if ( !isset($this->form_data['user'][$row['legal_entity_id']][$row['user_id']]) ) {
						$this->form_data['user'][$row['legal_entity_id']][$row['user_id']] = array( 'user_id' => $row['user_id'] );
					}

					foreach( $row as $key => $value ) {
						if ( preg_match( '/^l[0-9]{1,2}[a-z]?_(state|district)$/i', $key ) == TRUE ) { //Static keys
							$this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key] = $value;
						} elseif( is_numeric($value) AND preg_match( '/^l[0-9]{1,2}[a-z]?$/i', $key ) == TRUE ) { //Dynamic keys.
							if ( !isset($this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key]) ) {
								$this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key] = 0;
							}
							$this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key] = bcadd( $this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key], $value );
						} elseif ( isset( $sort_columns[$key] ) ) { //Sort columns only, to help sortFormData() later on.
							$this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key] = $value;
						}
					}
				}
			}
		}
		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->form_data, 'Form Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	/**
	 * @param null $format
	 * @return array|bool
	 */
	function _outputPDFForm( $format = NULL ) {
		$file_arr = array();
		$show_background = TRUE;
		if ( $format == 'pdf_form_print' OR $format == 'pdf_form_print_government' OR $format == 'efile' ) {
			$show_background = FALSE;
		}
		Debug::Text('Generating Form... Format: '. $format, __FILE__, __LINE__, __METHOD__, 10);

		$current_user = $this->getUserObject();
		$setup_data = $this->getFormConfig();
		$filter_data = $this->getFilterConfig();
		//Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( stristr( $format, 'government' ) ) {
			$form_type = 'government';
		} else {
			$form_type = 'employee';
		}

		if ( isset( $this->form_data['user'] ) AND is_array( $this->form_data['user'] ) ) {
			$this->sortFormData(); //Make sure forms are sorted.

			foreach ( $this->form_data['user'] as $legal_entity_id => $user_rows ) {
				if ( isset( $this->form_data['legal_entity'][$legal_entity_id] ) == FALSE ) {
					Debug::Text( 'Missing Legal Entity: ' . $legal_entity_id, __FILE__, __LINE__, __METHOD__, 10 );
					continue;
				}

				if ( isset( $this->form_data['remittance_agency'][$legal_entity_id] ) == FALSE ) {
					Debug::Text( 'Missing Remittance Agency: ' . $legal_entity_id, __FILE__, __LINE__, __METHOD__, 10 );
					continue;
				}

				if ( isset( $this->form_data['remittance_agency'][$legal_entity_id]['00'] ) == FALSE ) {
					Debug::Text( 'Missing Federal Remittance Agency: ' . $legal_entity_id, __FILE__, __LINE__, __METHOD__, 10 );
					continue;
				}

				$x = 0; //Progress bar only.
				$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($user_rows), NULL, TTi18n::getText('Generating Forms...') );

				$legal_entity_obj = $this->form_data['legal_entity'][$legal_entity_id];

				if ( is_object( $this->form_data['remittance_agency_obj'][ $this->form_data['remittance_agency'][ $legal_entity_id ]['00'] ]) ) {
					$contact_user_obj = $this->form_data['remittance_agency_obj'][ $this->form_data['remittance_agency'][ $legal_entity_id ]['00'] ]->getContactUserObject();
				}
				if ( !isset( $contact_user_obj ) OR !is_object( $contact_user_obj ) ) {
					$contact_user_obj = $this->getUserObject();
				}

				if ( $format == 'efile_xml' ) {
					$return1040 = $this->getRETURN1040Object();
					// Ceate the all needed data for Return1040.xsd at here.
					$return1040->return_created_timestamp = TTDate::getDBTimeStamp( TTDate::getTime(), FALSE );
					$return1040->year = TTDate::getYear( $filter_data['end_date'] );
					$return1040->tax_period_begin_date = TTDate::getDate( 'Y-m-d', TTDate::getBeginDayEpoch( $filter_data['start_date'] ) );
					$return1040->tax_period_end__date = TTDate::getDate( 'Y-m-d', TTDate::getEndDayEpoch( $filter_data['end_date'] ) );
					$return1040->software_id = '';
					$return1040->originator_efin = '';
					$return1040->originator_type_code = '';
					$return1040->pin_type_code = '';
					$return1040->jurat_disclosure_code = '';
					$return1040->pin_entered_by = '';
					$return1040->signature_date = TTDate::getDate( 'Y-m-d', TTDate::getTime() );
					$return1040->return_type = '';
					$return1040->ssn = '';
					$return1040->name = $legal_entity_obj->getLegalName();
					$return1040->address1 = $legal_entity_obj->getAddress1() . ' ' . $legal_entity_obj->getAddress2();
					$return1040->city = $legal_entity_obj->getCity();
					$return1040->state = $legal_entity_obj->getProvince();
					$return1040->zip_code = $legal_entity_obj->getPostalCode();
					$return1040->ip_address = '';
					$return1040->ip_date = TTDate::getDate( 'Y-m-d', TTDate::getTime() );
					$return1040->ip_time = TTDate::getDate( 'H:i:s', TTDate::getTime() );
					$return1040->timezone = TTDate::getTimeZone();

					$this->getFormObject()->addForm( $return1040 );
				}

				$fw2 = $this->getFW2Object();

				$fw2->setDebug( FALSE );
				//if ( $format == 'efile' ) {
				//	$fw2->setDebug(TRUE);
				//}
				$fw2->setShowBackground( $show_background );
				$fw2->setType( $form_type );
				$fw2->setShowInstructionPage( TRUE );
				$fw2->year = TTDate::getYear( $filter_data['end_date'] );
				$fw2->kind_of_employer = ( isset( $setup_data['kind_of_employer'] ) AND $setup_data['kind_of_employer'] != '' ) ? Misc::trimSortPrefix( $setup_data['kind_of_employer'] ) : 'N';

				$fw2->name = $legal_entity_obj->getLegalName();
				$fw2->trade_name = $legal_entity_obj->getTradeName();
				$fw2->company_address1 = $legal_entity_obj->getAddress1() . ' ' . $legal_entity_obj->getAddress2();
				$fw2->company_city = $legal_entity_obj->getCity();
				$fw2->company_state = $legal_entity_obj->getProvince();
				$fw2->company_zip_code = $legal_entity_obj->getPostalCode();

				$fw2->ein = $this->form_data['remittance_agency_obj'][ $this->form_data['remittance_agency'][ $legal_entity_id ]['00'] ]->getPrimaryIdentification(); //Always use EIN from Federal Agency.

				//Only use the state specific format if its the only agency that is being returned (ie: they are filtering to a specific agency).
				// $setup_data['efile_state'] is set from PayrollRemittanceAgencyEvent->getReport().
				if ( isset($setup_data['efile_state']) AND $setup_data['efile_state'] != '' ) {
					$fw2->efile_state = strtoupper( $setup_data['efile_state'] );
					Debug::Text( '    Using State eFile Format: '. $fw2->efile_state, __FILE__, __LINE__, __METHOD__, 10 );
				} else {
					Debug::Text( '    Using Federal eFile Format...', __FILE__, __LINE__, __METHOD__, 10 );
				}


				if ( isset($this->form_data['remittance_agency'][$legal_entity_id][$fw2->efile_state])
						AND isset($setup_data['efile_district']) AND $setup_data['efile_district'] == TRUE
						AND isset($setup_data['payroll_remittance_agency_id']) AND isset( $this->form_data['remittance_agency_obj'][ $setup_data['payroll_remittance_agency_id'] ]) ) {
					$fw2->efile_user_id = $this->form_data['remittance_agency_obj'][ $setup_data['payroll_remittance_agency_id'] ]->getTertiaryIdentification();
					$fw2->efile_agency_id = $this->form_data['remittance_agency_obj'][ $setup_data['payroll_remittance_agency_id'] ]->getAgency();
					Debug::Text( '    Using City eFile Format: '. $fw2->efile_agency_id, __FILE__, __LINE__, __METHOD__, 10 );
				} elseif ( isset($this->form_data['remittance_agency'][$legal_entity_id][$fw2->efile_state]) ) {
					$fw2->efile_user_id = $this->form_data['remittance_agency_obj'][ $this->form_data['remittance_agency'][$legal_entity_id][$fw2->efile_state] ]->getTertiaryIdentification();
					$fw2->efile_agency_id = $this->form_data['remittance_agency_obj'][ $this->form_data['remittance_agency'][$legal_entity_id][$fw2->efile_state] ]->getAgency();
				} elseif ( isset($this->form_data['remittance_agency'][$legal_entity_id]['00']) ) {
					$fw2->efile_user_id = $this->form_data['remittance_agency_obj'][ $this->form_data['remittance_agency'][$legal_entity_id]['00'] ]->getTertiaryIdentification();
					$fw2->efile_agency_id = $this->form_data['remittance_agency_obj'][ $this->form_data['remittance_agency'][$legal_entity_id]['00'] ]->getAgency();
				} else {
					Debug::Text( '    WARNING: Unable to determine remittance agency to obtain efile_user_id from...', __FILE__, __LINE__, __METHOD__, 10 );
				}

				$fw2->contact_name = $contact_user_obj->getFullName();
				$fw2->contact_phone = $contact_user_obj->getWorkPhone();
				$fw2->contact_phone_ext = $contact_user_obj->getWorkPhoneExt();
				$fw2->contact_email = ( $contact_user_obj->getWorkEmail() != '' ) ? $contact_user_obj->getWorkEmail() : ( ( $contact_user_obj->getHomeEmail() != '' ) ? $contact_user_obj->getHomeEmail() : NULL );

				if ( isset( $this->form_data ) AND count( $this->form_data ) > 0 ) {
					$i = 0;
					foreach ( $user_rows as $user_id => $row ) {
						if ( !isset( $user_id ) OR TTUUID::isUUID( $user_id ) == FALSE ) {
							Debug::Text( 'User ID not set!', __FILE__, __LINE__, __METHOD__, 10 );
							continue;
						}

						$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
						$ulf->getById( TTUUID::castUUID( $user_id ) );
						if ( $ulf->getRecordCount() == 1 ) {
							$user_obj = $ulf->getCurrent();

							$ee_data = array(
									'control_number'      => $i + 1,
									'first_name'          => $user_obj->getFirstName(),
									'middle_name'         => $user_obj->getMiddleName(),
									'last_name'           => $user_obj->getLastName(),
									'address1'            => $user_obj->getAddress1(),
									'address2'            => $user_obj->getAddress2(),
									'city'                => $user_obj->getCity(),
									'state'               => $user_obj->getProvince(),
									'employment_province' => $user_obj->getProvince(),
									'zip_code'            => $user_obj->getPostalCode(),
									'ssn'                 => $user_obj->getSIN(),
									'employee_number'     => $user_obj->getEmployeeNumber(),
									'l1'                  => ( $row['l1'] != 0 ) ? $row['l1'] : NULL,
									'l2'                  => ( $row['l2'] != 0 ) ? $row['l2'] : NULL,
									'l3'                  => ( $row['l3'] != 0 ) ? $row['l3'] : NULL,
									'l4'                  => ( $row['l4'] != 0 ) ? $row['l4'] : NULL,
									'l5'                  => ( $row['l5'] != 0 ) ? $row['l5'] : NULL,
									'l6'                  => ( $row['l6'] != 0 ) ? $row['l6'] : NULL,
									'l7'                  => ( $row['l7'] != 0 ) ? $row['l7'] : NULL,
									'l8'                  => ( $row['l8'] != 0 ) ? $row['l8'] : NULL,
									'l10'                 => ( $row['l10'] != 0 ) ? $row['l10'] : NULL,
									'l11'                 => ( $row['l11'] != 0 ) ? $row['l11'] : NULL,
									'l12a_code'           => NULL,
									'l12a'                => NULL,
									'l12b_code'           => NULL,
									'l12b'                => NULL,
									'l12c_code'           => NULL,
									'l12c'                => NULL,
									'l12d_code'           => NULL,
									'l12d'                => NULL,
									'l13b'                => FALSE,
									'l14a_name'           => NULL,
									'l14a'                => NULL,
									'l14b_name'           => NULL,
									'l14b'                => NULL,
									'l14c_name'           => NULL,
									'l14c'                => NULL,
									'l14d_name'           => NULL,
									'l14d'                => NULL,
									'states'              => array(), //State codes with wages.
							);

							if ( $row['l12a'] > 0 AND isset( $setup_data['l12a_code'] ) AND $setup_data['l12a_code'] != '' ) {
								$ee_data['l12a_code'] = $setup_data['l12a_code'];
								$ee_data['l12a'] = $row['l12a'];
							}
							if ( $row['l12b'] > 0 AND isset( $setup_data['l12b_code'] ) AND $setup_data['l12b_code'] != '' ) {
								$ee_data['l12b_code'] = $setup_data['l12b_code'];
								$ee_data['l12b'] = $row['l12b'];
							}
							if ( $row['l12c'] > 0 AND isset( $setup_data['l12c_code'] ) AND $setup_data['l12c_code'] != '' ) {
								$ee_data['l12c_code'] = $setup_data['l12c_code'];
								$ee_data['l12c'] = $row['l12c'];
							}
							if ( $row['l12d'] > 0 AND isset( $setup_data['l12d_code'] ) AND $setup_data['l12d_code'] != '' ) {
								$ee_data['l12d_code'] = $setup_data['l12d_code'];
								$ee_data['l12d'] = $row['l12d'];
							}

							if ( isset($this->form_data['l13_user_deduction_data']) AND isset($this->form_data['l13_user_deduction_data'][$user_id]) ) {
								$ee_data['l13b'] = TRUE;
							}

							if ( $row['l14a'] > 0 AND isset( $setup_data['l14a_name'] ) AND $setup_data['l14a_name'] != '' ) {
								$ee_data['l14a_name'] = $setup_data['l14a_name'];
								$ee_data['l14a'] = $row['l14a'];
							}
							if ( $row['l14b'] > 0 AND isset( $setup_data['l14b_name'] ) AND $setup_data['l14b_name'] != '' ) {
								$ee_data['l14b_name'] = $setup_data['l14b_name'];
								$ee_data['l14b'] = $row['l14b'];
							}
							if ( $row['l14c'] > 0 AND isset( $setup_data['l14c_name'] ) AND $setup_data['l14c_name'] != '' ) {
								$ee_data['l14c_name'] = $setup_data['l14c_name'];
								$ee_data['l14c'] = $row['l14c'];
							}
							if ( $row['l14d'] > 0 AND isset( $setup_data['l14d_name'] ) AND $setup_data['l14d_name'] != '' ) {
								$ee_data['l14d_name'] = $setup_data['l14d_name'];
								$ee_data['l14d'] = $row['l14d'];
							}

							foreach ( range( 'a', 'z' ) as $z ) {
								//Make sure state information is included if its just local income taxes.
								if ( ( isset( $row[ 'l16' . $z ] ) OR isset( $row[ 'l18' . $z ] ) )
										AND ( isset( $row[ 'l15' . $z . '_state' ] )
												AND isset( $this->form_data['remittance_agency'][ $legal_entity_id ][ $row[ 'l15' . $z . '_state' ] ] )
												AND isset( $this->form_data['remittance_agency_obj'][ $this->form_data['remittance_agency'][ $legal_entity_id ][ $row[ 'l15' . $z . '_state' ] ] ] )
												AND $this->form_data['remittance_agency_obj'][ $this->form_data['remittance_agency'][ $legal_entity_id ][ $row[ 'l15' . $z . '_state' ] ] ]->getType() == 20 ) ) {
									$ee_data[ 'l15' . $z . '_state_id' ] = $this->form_data['remittance_agency_obj'][ $this->form_data['remittance_agency'][ $legal_entity_id ][ $row[ 'l15' . $z . '_state' ] ] ]->getPrimaryIdentification();
									$ee_data[ 'l15' . $z . '_state' ] = $row[ 'l15' . $z . '_state' ];
								} else {
									$ee_data[ 'l15' . $z . '_state_id' ] = NULL;
									$ee_data[ 'l15' . $z . '_state' ] = NULL;
								}

								//State income tax
								if ( isset( $row[ 'l16' . $z ] ) ) {
									$ee_data[ 'l16' . $z ] = $row[ 'l16' . $z ];
									$ee_data[ 'l17' . $z ] = $row[ 'l17' . $z ];
								} else {
									$ee_data[ 'l16' . $z ] = NULL;
									$ee_data[ 'l17' . $z ] = NULL;
								}

								//District income tax
								if ( isset( $row[ 'l18' . $z ] ) ) {
									$ee_data[ 'l20' . $z . '_district' ] = $row[ 'l20' . $z . '_district' ];
									$ee_data[ 'l18' . $z ] = $row[ 'l18' . $z ];
									$ee_data[ 'l19' . $z ] = $row[ 'l19' . $z ];
								} else {
									$ee_data[ 'l20' . $z . '_district' ] = NULL;
									$ee_data[ 'l18' . $z ] = NULL;
									$ee_data[ 'l19' . $z ] = NULL;
								}

								//Save each state that wages were earned, so we can determine which states need W2/eFiling.
								if ( isset($row[ 'l15' . $z . '_state' ])
										AND ( ( isset( $row[ 'l16' . $z ] ) AND $row[ 'l16' . $z ] != 0 )
										OR ( isset( $row[ 'l18' . $z ] ) AND $row[ 'l18' . $z ] != 0 ) ) ) {
									Debug::Text( ' Wages earned in State: '. $row[ 'l15' . $z . '_state' ], __FILE__, __LINE__, __METHOD__, 10 );
									$ee_data['states'][ $row[ 'l15' . $z . '_state' ] ] = TRUE;
								}

							}

							//If we are doing State/Local W2s, skip employees who do not have wages or deductions in that state.
							if ( isset($fw2->efile_state) AND $fw2->efile_state != '' ) {
								if ( !isset( $ee_data['states'][$fw2->efile_state] ) ) {
									Debug::Text( '  No wages in eFile State: '. $fw2->efile_state .' Skipping...', __FILE__, __LINE__, __METHOD__, 10 );
									continue;
								}

							}

							$fw2->addRecord( $ee_data );
							unset( $ee_data );

							if ( $format == 'pdf_form_publish_employee' ) {
								// generate PDF for every employee and assign to each government document records
								$this->getFormObject()->addForm( $fw2 );
								GovernmentDocumentFactory::addDocument( $user_obj->getId(), 20, 200, TTDate::getEndYearEpoch( $filter_data['end_date'] ), $this->getFormObject()->output( 'PDF' ) );
								$this->getFormObject()->clearForms();
							}

							$i++;
						}

						$this->getProgressBarObject()->set( $this->getAMFMessageID(), $x );
						$x++;
					}
				}

				if ( $format == 'pdf_form_publish_employee' ) {
					$user_generic_status_batch_id = GovernmentDocumentFactory::saveUserGenericStatus( $current_user->getId() );

					return $user_generic_status_batch_id;
				}

				$this->getFormObject()->addForm( $fw2 );

				if ( $form_type == 'government' ) {
					//Handle W3
					$fw3 = $this->getFW3Object();
					$fw3->setShowBackground( $show_background );
					$fw3->year = $fw2->year;
					$fw3->ein = $fw2->ein;
					$fw3->name = $fw2->name;
					$fw3->trade_name = $fw2->trade_name;
					$fw3->company_address1 = $fw2->company_address1;
					$fw3->company_address2 = $fw2->company_address2;
					$fw3->company_city = $fw2->company_city;
					$fw3->company_state = $fw2->company_state;
					$fw3->company_zip_code = $fw2->company_zip_code;

					$fw3->contact_name = $contact_user_obj->getFullName();
					$fw3->contact_phone = ( $contact_user_obj->getWorkPhoneExt() != '' ) ? $contact_user_obj->getWorkPhone() . ' x' . $contact_user_obj->getWorkPhoneExt() : $contact_user_obj->getWorkPhone();
					$fw3->contact_email = $contact_user_obj->getWorkEmail();

					$fw3->kind_of_payer = '941';
					$fw3->kind_of_employer = $fw2->kind_of_employer;
					//$fw3->third_party_sick_pay = TRUE;

					//Use the home state ID if possible.
					if ( isset($this->form_data['remittance_agency'][$legal_entity_id][$fw2->company_state])
							AND isset($this->form_data['remittance_agency_obj'][ $this->form_data['remittance_agency'][$legal_entity_id][$fw2->company_state] ]) AND is_object( $this->form_data['remittance_agency_obj'][ $this->form_data['remittance_agency'][$legal_entity_id][$fw2->company_state] ]) ) {
						$fw3->state_id1 = $this->form_data['remittance_agency_obj'][ $this->form_data['remittance_agency'][$legal_entity_id][$fw2->company_state] ]->getPrimaryIdentification();
					}

					$fw3->lc = $fw2->countRecords();
					$fw3->control_number = ( $fw3->lc + 1 );

					//Use sumRecords()/getRecordsTotal() so all amounts are capped properly.
					$fw2->sumRecords();
					$total_row = $fw2->getRecordsTotal();

					//Debug::Arr($total_row, 'Total Row Data: ', __FILE__, __LINE__, __METHOD__, 10);
					if ( is_array( $total_row ) ) {
						$fw3->l1 = ( isset( $total_row['l1'] ) AND $total_row['l1'] != 0 ) ? $total_row['l1'] : NULL;
						$fw3->l2 = ( isset( $total_row['l2'] ) AND $total_row['l2'] != 0 ) ? $total_row['l2'] : NULL;
						$fw3->l3 = ( isset( $total_row['l3'] ) AND $total_row['l3'] != 0 ) ? $total_row['l3'] : NULL;
						$fw3->l4 = ( isset( $total_row['l4'] ) AND $total_row['l4'] != 0 ) ? $total_row['l4'] : NULL;
						$fw3->l5 = ( isset( $total_row['l5'] ) AND $total_row['l5'] != 0 ) ? $total_row['l5'] : NULL;
						$fw3->l6 = ( isset( $total_row['l6'] ) AND $total_row['l6'] != 0 ) ? $total_row['l6'] : NULL;
						$fw3->l7 = ( isset( $total_row['l7'] ) AND $total_row['l7'] != 0 ) ? $total_row['l7'] : NULL;
						$fw3->l8 = ( isset( $total_row['l8'] ) AND $total_row['l8'] != 0 ) ? $total_row['l8'] : NULL;
						$fw3->l10 = ( isset( $total_row['l10'] ) AND $total_row['l10'] != 0 ) ? $total_row['l10'] : NULL;
						$fw3->l11 = ( isset( $total_row['l11'] ) AND $total_row['l11'] != 0 ) ? $total_row['l11'] : NULL;

						$l12a_letters = array('d', 'e', 'f', 'g', 'h', 's', 'y', 'aa', 'bb', 'ee');
						$fw3->l12a = NULL;
						if ( isset( $total_row['l12a_code'] ) AND in_array( strtolower( $total_row['l12a_code'] ), $l12a_letters ) ) {
							$fw3->l12a = bcadd( $fw3->l12a, $total_row['l12a'] );
						}
						if ( isset( $total_row['l12b_code'] ) AND in_array( strtolower( $total_row['l12b_code'] ), $l12a_letters ) ) {
							$fw3->l12a = bcadd( $fw3->l12a, $total_row['l12b'] );
						}
						if ( isset( $total_row['l12c_code'] ) AND in_array( strtolower( $total_row['l12c_code'] ), $l12a_letters ) ) {
							$fw3->l12a = bcadd( $fw3->l12a, $total_row['l12c'] );
						}
						if ( isset( $total_row['l12d_code'] ) AND in_array( strtolower( $total_row['l12d_code'] ), $l12a_letters ) ) {
							$fw3->l12a = bcadd( $fw3->l12a, $total_row['l12d'] );
						}

						foreach ( range( 'a', 'z' ) as $z ) {
							//State income tax
							if ( isset( $total_row[ 'l16' . $z ] ) ) {
								$fw3->l16 = bcadd( $fw3->l16, $total_row[ 'l16' . $z ] );
								$fw3->l17 = bcadd( $fw3->l17, $total_row[ 'l17' . $z ] );
							}
							//District income tax
							if ( isset( $total_row[ 'l18' . $z ] ) ) {
								$fw3->l18 = bcadd( $fw3->l18, $total_row[ 'l18' . $z ] );
								$fw3->l19 = bcadd( $fw3->l19, $total_row[ 'l19' . $z ] );
							}
						}
					}

					$this->getFormObject()->addForm( $fw3 );
				}

				if ( $format == 'efile' ) {
					$output_format = 'EFILE';
					if ( $fw2->getDebug() == TRUE ) {
						$file_name = 'w2_efile_' . date( 'Y_m_d' ) . '_' . Misc::sanitizeFileName( $this->form_data['legal_entity'][ $legal_entity_id ]->getTradeName() ) . '.csv';
					} else {
						$file_name = 'w2_efile_' . date( 'Y_m_d' ) . '_' . Misc::sanitizeFileName( $this->form_data['legal_entity'][ $legal_entity_id ]->getTradeName() ) . '.txt';
					}
					$mime_type = 'applications/octet-stream'; //Force file to download.
				} elseif ( $format == 'efile_xml' ) {
					$output_format = 'XML';
					$file_name = 'w2_efile_' . date( 'Y_m_d' ) . '_' . Misc::sanitizeFileName( $this->form_data['legal_entity'][ $legal_entity_id ]->getTradeName() ) . '.xml';
					$mime_type = 'applications/octet-stream'; //Force file to download.
				} else {
					$output_format = 'PDF';
					$file_name = $this->file_name . '_' . Misc::sanitizeFileName( $this->form_data['legal_entity'][ $legal_entity_id ]->getTradeName() ) . '.pdf';
					$mime_type = $this->file_mime_type;
				}

				$output = $this->getFormObject()->output( $output_format );

				$file_arr[] = array('file_name' => $file_name, 'mime_type' => $mime_type, 'data' => $output);

				$this->clearFormObject();
				$this->clearFW2Object();
				$this->clearFW3Object();
				$this->clearRETURN1040Object();
			} //outer foreach
		} //if

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
	 * Short circuit this function, as no postprocessing is required for exporting the data.
	 * @param null $format
	 * @return bool
	 */
	function _postProcess( $format = NULL ) {
		if ( ( $format == 'pdf_form' OR $format == 'pdf_form_government' ) OR ( $format == 'pdf_form_print' OR $format == 'pdf_form_print_government' ) OR $format == 'efile' OR $format == 'efile_xml' OR $format == 'pdf_form_publish_employee' ) {
			Debug::Text('Skipping postProcess! Format: '. $format, __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		} else {
			return parent::_postProcess( $format );
		}
	}

	/**
	 * @param null $format
	 * @return array|bool
	 */
	function _output( $format = NULL ) {
		if ( ( $format == 'pdf_form' OR $format == 'pdf_form_government' ) OR ( $format == 'pdf_form_print' OR $format == 'pdf_form_print_government' ) OR $format == 'efile' OR $format == 'efile_xml' OR $format == 'pdf_form_publish_employee' ) {
			//return $this->_outputPDFForm( 'efile' );
			return $this->_outputPDFForm( $format );
		} else {
			return parent::_output( $format );
		}
	}
}
?>
