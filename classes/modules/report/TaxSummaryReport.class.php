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
class TaxSummaryReport extends Report {

	/**
	 * TaxSummaryReport constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText('Tax Summary Report');
		$this->file_name = 'tax_summary_report';

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
				AND $this->getPermissionObject()->Check('report', 'view_generic_tax_summary', $user_id, $company_id ) ) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param $name
	 * @param null $params
	 * @return array|bool|mixed|null
	 */
	protected function _getOptions( $name, $params = NULL ) {
		$retval = NULL;
		switch( $name ) {
			case 'output_format':
				$retval = parent::getOptions('default_output_format');
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
										'-1900-legal_entity_id' => TTi18n::gettext('Legal Entity'),
										'-2000-company_deduction_id' => TTi18n::gettext('Tax'),
										'-2005-payroll_remittance_agency_id' => TTi18n::gettext('Remittance Agency'),

										'-2010-user_status_id' => TTi18n::gettext('Employee Status'),
										'-2020-user_group_id' => TTi18n::gettext('Employee Group'),
										'-2030-user_title_id' => TTi18n::gettext('Employee Title'),
										'-2035-user_tag' => TTi18n::gettext('Employee Tags'),
										'-2040-include_user_id' => TTi18n::gettext('Employee Include'),
										'-2050-exclude_user_id' => TTi18n::gettext('Employee Exclude'),
										'-2060-default_branch_id' => TTi18n::gettext('Default Branch'),
										'-2070-default_department_id' => TTi18n::gettext('Default Department'),
										'-3000-custom_filter' => TTi18n::gettext('Custom Filter'),

										//'-4020-exclude_ytd_adjustment' => TTi18n::gettext('Exclude YTD Adjustments'),

										'-5000-columns' => TTi18n::gettext('Display Columns'),
										'-5010-group' => TTi18n::gettext('Group By'),
										'-5020-sub_total' => TTi18n::gettext('SubTotal By'),
										'-5030-sort' => TTi18n::gettext('Sort By'),
								);
				break;
			case 'time_period':
				$retval = TTDate::getTimePeriodOptions();
				break;
			case 'date_columns':
				$retval = array_merge(
						TTDate::getReportDateOptions( 'hire', TTi18n::getText('Hire Date'), 13, FALSE ),
						TTDate::getReportDateOptions( 'termination', TTi18n::getText('Termination Date'), 14, FALSE ),
						TTDate::getReportDateOptions( 'transaction', TTi18n::getText('Transaction Date'), 16, TRUE )
				);
				break;
			case 'custom_columns':
				//Get custom fields for report data.
				$oflf = TTnew( 'OtherFieldListFactory' );
				//User and Punch fields conflict as they are merged together in a secondary process.
				$other_field_names = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), array(10), array( 10 => '' ) );
				if ( is_array($other_field_names) ) {
					$retval = Misc::addSortPrefix( $other_field_names, 9000 );
				}
				break;
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), NULL, 'TaxSummaryReport', 'custom_column' );
					if ( is_array($custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('filter_column_type_ids'), NULL, 'TaxSummaryReport', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('dynamic_format_ids'), 'TaxSummaryReport', 'custom_column' );
					if ( is_array($report_dynamic_custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('static_format_ids'), 'TaxSummaryReport', 'custom_column' );
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
										'-1040-status' => TTi18n::gettext('Status'),
										'-1050-title' => TTi18n::gettext('Title'),
										'-1052-ethnic_group' => TTi18n::gettext('Ethnicity'),
										'-1053-sex' => TTi18n::gettext('Gender'),
										'-1054-address1' => TTi18n::gettext('Address 1'),
										'-1054-address2' => TTi18n::gettext('Address 2'),
										'-1055-city' => TTi18n::gettext('City'),
										'-1060-province' => TTi18n::gettext('Province/State'),
										'-1070-country' => TTi18n::gettext('Country'),
										'-1075-postal_code' => TTi18n::gettext('Postal Code'),
										'-1078-home_phone' => TTi18n::gettext('Home Phone'),
										'-1080-user_group' => TTi18n::gettext('Group'),
										'-1090-default_branch' => TTi18n::gettext('Default Branch'),
										'-1100-default_department' => TTi18n::gettext('Default Department'),
										'-1102-default_job' => TTi18n::gettext('Default Job'),
										'-1104-default_job_item' => TTi18n::gettext('Default Task'),
										'-1110-currency' => TTi18n::gettext('Currency'),
										'-1200-permission_control' => TTi18n::gettext('Permission Group'),
										'-1210-pay_period_schedule' => TTi18n::gettext('Pay Period Schedule'),
										'-1220-policy_group' => TTi18n::gettext('Policy Group'),

										//Handled in date_columns above.
										//'-1250-pay_period' => TTi18n::gettext('Pay Period'),

										'-1280-sin' => TTi18n::gettext('SIN/SSN'),
										'-1290-note' => TTi18n::gettext('Note'),
										'-1295-tag' => TTi18n::gettext('Tags'),

										'-1598-hire_date_age' => TTi18n::gettext('Length of Service'),
										'-1599-birth_date_age' => TTi18n::gettext('Age'),

										'-1700-payroll_remittance_agency_name' => TTi18n::gettext('Remittance Agency'),

										'-1810-company_deduction_name' => TTi18n::gettext('Tax/Deduction Name'),

								);

				$retval = array_merge( $retval, $this->getOptions('date_columns'), (array)$this->getOptions('custom_columns'), (array)$this->getOptions('report_static_custom_column') );
				ksort($retval);
				break;
			case 'dynamic_columns':
				$retval = array(
										//Dynamic - Aggregate functions can be used

										//Take into account wage groups. However hourly_rates for the same hour type, so we need to figure out an average hourly rate for each column?
										//'-2010-hourly_rate' => TTi18n::gettext('Hourly Rate'),
										'-2030-subject_wages' => TTi18n::gettext('Subject Wages'),
										'-2040-taxable_wages' => TTi18n::gettext('Taxable Wages'),
										'-2050-tax_withheld' => TTi18n::gettext('Tax Withheld'),

										'-2110-subject_wages_ytd' => TTi18n::gettext('Subject Wages YTD'),
										'-2120-subject_units' => TTi18n::gettext('Subject Units'),
										'-2130-subject_rate' => TTi18n::gettext('Subject Hourly Rate'),

										'-2150-taxable_wages_ytd' => TTi18n::gettext('Taxable Wages YTD'),

										'-2210-pay_period_taxable_wages_weeks' => TTi18n::gettext('Taxable Wages Weeks'),
										'-2220-pay_period_tax_withheld_weeks' => TTi18n::gettext('Tax Withheld Weeks'),
										'-2230-pay_period_weeks' => TTi18n::gettext('Pay Period Weeks'),

										'-2300-total_user' => TTi18n::gettext('Total Employees'), //Group counter...

										'-3010-company_deduction_rate' => TTi18n::gettext('Tax/Deduction Rate'),
							);

				$retval = array_merge( $retval, $this->getOptions('pay_stub_account_amount_columns') );
				ksort($retval);

				break;

			case 'pay_stub_account_amount_columns':
				//Get all pay stub accounts
				$retval = array();

				$psealf = TTnew( 'PayStubEntryAccountListFactory' );
				$psealf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), 10, array(10, 20, 30, 40, 50, 60, 65, 80) );
				if ( $psealf->getRecordCount() > 0 ) {
					$type_options  = $psealf->getOptions('type');
					foreach( $type_options as $key => $val ) {
						$type_options[$key] = str_replace( array('Employee', 'Employer', 'Deduction', 'Miscellaneous', 'Total'), array('EE', 'ER', 'Ded', 'Misc', ''), $val);
					}

					$i = 0;
					foreach( $psealf as $psea_obj ) {
						//Need to make the PSEA_ID a string so we can array_merge it properly later.
						if ( $psea_obj->getType() == 40 ) { //Total accounts.
							$prefix = NULL;
						} else {
							$prefix = $type_options[$psea_obj->getType()] .' - ';
						}

						$retval['-3'. str_pad( $i, 3, 0, STR_PAD_LEFT).'-PA:'.$psea_obj->getID()] = $prefix.$psea_obj->getName();

						if ( $psea_obj->getType() == 10 ) { //Earnings only can see units.
							$retval['-4'. str_pad( $i, 3, 0, STR_PAD_LEFT).'-PR:'.$psea_obj->getID()] = $prefix.$psea_obj->getName() .' ['. TTi18n::getText('Rate') .']';
							$retval['-5'. str_pad( $i, 3, 0, STR_PAD_LEFT).'-PU:'.$psea_obj->getID()] = $prefix.$psea_obj->getName() .' ['. TTi18n::getText('Units') .']';
						}

						if ( $psea_obj->getType() == 50 ) { //Accruals, display balance/YTD amount.
							$retval['-6'. str_pad( $i, 3, 0, STR_PAD_LEFT).'-PY:'.$psea_obj->getID()] = $prefix.$psea_obj->getName() .' ['. TTi18n::getText('Balance') .']';
						}

						$i++;
					}
				}
				break;
			case 'pay_stub_account_unit_columns':
				//Units are only good for earnings?
				break;
			case 'pay_stub_account_ytd_columns':
				break;
			case 'columns':
				$retval = array_merge( $this->getOptions('static_columns'), $this->getOptions('dynamic_columns'), (array)$this->getOptions('report_dynamic_custom_column') );
				ksort($retval);
				break;
			case 'column_format':
				//Define formatting function for each column.
				$columns = Misc::trimSortPrefix( array_merge($this->getOptions('dynamic_columns'), (array)$this->getOptions('report_custom_column')) );
				if ( is_array($columns) ) {
					foreach($columns as $column => $name ) {
						if ( $column == 'subject_units' OR strpos($column, '_weeks') !== FALSE OR substr( $column, 0, 2 ) == 'PU' OR strpos($column, 'total_user') !== FALSE ) {
							$retval[$column] = 'numeric';
						} elseif ( strpos($column, '_wage') !== FALSE OR strpos($column, '_hourly_rate') !== FALSE
							OR substr( $column, 0, 2 ) == 'PA' OR substr( $column, 0, 2 ) == 'PY' OR substr( $column, 0, 2 ) == 'PR'
							OR strpos($column, '_ytd') !== FALSE
							OR $column == 'tax_withheld' OR $column == 'subject_rate' ) {
							$retval[$column] = 'currency';
						} elseif ( strpos($column, '_time') OR strpos($column, '_policy') ) {
							$retval[$column] = 'time_unit';
						} elseif ( $column == 'company_deduction_rate' ) {
							$retval[ $column ] = 'percent';
						}
					}
				}
				$retval['verified_time_sheet_date'] = 'time_stamp';
				break;
			case 'grand_total_metadata':
				//Make sure all jobs are sum'd
				$retval['aggregate'] = array();
				$dynamic_columns = array_keys( Misc::trimSortPrefix( array_merge( $this->getOptions('dynamic_columns'), (array)$this->getOptions('report_dynamic_custom_column') ) ) );
				if ( is_array($dynamic_columns ) ) {
					foreach( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								if ( strpos($column, '_hourly_rate') !== FALSE OR strpos($column, '_rate') !== FALSE OR substr( $column, 0, 2 ) == 'PR' OR $column == 'company_deduction_rate' ) {
									$retval['aggregate'][$column] = 'avg';
								} elseif ( strpos($column, '_ytd') !== FALSE ) {
									$retval['aggregate'][$column] = 'sum';
								} else {
									$retval['aggregate'][$column] = 'sum';
								}
						}
					}
				}
				break;
			case 'sub_total_by_metadata':
				//Make sure task estimates are sum'd.
				$retval['aggregate'] = array();
				$dynamic_columns = array_keys( Misc::trimSortPrefix( array_merge( $this->getOptions('dynamic_columns'), (array)$this->getOptions('report_dynamic_custom_column') ) ) );
				if ( is_array($dynamic_columns ) ) {
					foreach( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								if ( strpos($column, '_hourly_rate') !== FALSE OR strpos($column, '_rate') !== FALSE OR substr( $column, 0, 2 ) == 'PR' OR $column == 'company_deduction_rate' ) {
									$retval['aggregate'][$column] = 'avg';
								} elseif ( strpos($column, '_ytd') !== FALSE ) {
									$retval['aggregate'][$column] = 'sum';
								} else {
									$retval['aggregate'][$column] = 'sum';
								}
						}
					}
				}
				break;
			case 'group_by_metadata':
				$retval['aggregate'] = array();
				$dynamic_columns = array_keys( Misc::trimSortPrefix( array_merge( $this->getOptions('dynamic_columns'), (array)$this->getOptions('report_dynamic_custom_column') ) ) );
				if ( is_array($dynamic_columns ) ) {
					foreach( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								if ( strpos($column, '_hourly_rate') !== FALSE OR strpos($column, '_rate') !== FALSE OR substr( $column, 0, 2 ) == 'PR' OR $column == 'company_deduction_rate' ) {
									$retval['aggregate'][$column] = 'avg';
								} elseif ( strpos($column, '_ytd') !== FALSE ) {
									$retval['aggregate'][$column] = 'max';
								} else {
									$retval['aggregate'][$column] = 'sum';
								}
						}
					}
				}

				$retval['verified_time_sheet'] = 'first';
				$retval['verified_time_sheet_date'] = 'first';
				break;
			case 'templates':
				$retval = array(
									'-1010-by_employee+taxes' => TTi18n::gettext('Tax by Employee'),
									'-1020-by_company_deduction_by_employee+taxes' => TTi18n::gettext('Tax by Tax/Employee'),
									'-1030-by_payroll_remittance_agency+taxes' => TTi18n::gettext('Tax by Remittance Agency'),
									'-1040-by_payroll_remittance_agency_by_company_deduction+taxes' => TTi18n::gettext('Tax by Remittance Agency/Tax'),
									'-1050-by_payroll_remittance_agency_by_employee+taxes' => TTi18n::gettext('Tax by Remittance Agency/Employee'),
									'-1060-by_payroll_remittance_agency_by_company_deduction_by_employee+taxes' => TTi18n::gettext('Tax by Remittance Agency/Tax/Employee'),
								);

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset($template) AND $template != '' ) {
					switch( $template ) {
						default:
							Debug::Text(' Parsing template name: '. $template, __FILE__, __LINE__, __METHOD__, 10);
							$retval['-1010-time_period']['time_period'] = 'last_quarter';

							//Parse template name, and use the keywords separated by '+' to determine settings.
							$template_keywords = explode('+', $template );
							if ( is_array($template_keywords) ) {
								foreach( $template_keywords as $template_keyword ) {
									Debug::Text(' Keyword: '. $template_keyword, __FILE__, __LINE__, __METHOD__, 10);

									switch( $template_keyword ) {
										//Columns
										case 'taxes':
											//$retval['columns'][] = 'PA'.$default_linked_columns[0];
											$retval['columns'][] = 'subject_wages'; //Basically Total Gross.
											$retval['columns'][] = 'taxable_wages';
											$retval['columns'][] = 'tax_withheld';
											break;
										//Filter
										//Group By
										//SubTotal
										//Sort
										case 'by_employee':
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';
											$retval['columns'][] = 'middle_name';
											$retval['columns'][] = 'sin';

											$retval['-2000-company_deduction_id'][] = TTUUID::getZeroID();

											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';
											$retval['group'][] = 'middle_name';
											$retval['group'][] = 'sin';

											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('middle_name' => 'asc');
											$retval['sort'][] = array('sin' => 'asc');
											break;
										case 'by_company_deduction_by_employee':
											$retval['columns'][] = 'company_deduction_name';
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';
											$retval['columns'][] = 'middle_name';
											$retval['columns'][] = 'sin';

											$retval['-2000-company_deduction_id'][] = TTUUID::getZeroID();

											$retval['group'][] = 'company_deduction_name';
											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';
											$retval['group'][] = 'middle_name';
											$retval['group'][] = 'sin';

											$retval['sub_total'][] = 'company_deduction_name';

											$retval['sort'][] = array('company_deduction_name' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('middle_name' => 'asc');
											$retval['sort'][] = array('sin' => 'asc');
											break;
										case 'by_payroll_remittance_agency':
											$retval['columns'][] = 'payroll_remittance_agency_name';

											$retval['-2000-company_deduction_id'][] = TTUUID::getZeroID();

											$retval['group'][] = 'payroll_remittance_agency_name';

											$retval['sub_total'][] = 'payroll_remittance_agency_name';

											$retval['sort'][] = array('payroll_remittance_agency_name' => 'asc');
											break;
										case 'by_payroll_remittance_agency_by_company_deduction':
											$retval['columns'][] = 'payroll_remittance_agency_name';
											$retval['columns'][] = 'company_deduction_name';

											$retval['-2000-company_deduction_id'][] = TTUUID::getZeroID();

											$retval['group'][] = 'payroll_remittance_agency_name';
											$retval['group'][] = 'company_deduction_name';

											$retval['sub_total'][] = 'payroll_remittance_agency_name';
											$retval['sub_total'][] = 'company_deduction_name';

											$retval['sort'][] = array('payroll_remittance_agency_name' => 'asc');
											$retval['sort'][] = array('company_deduction_name' => 'asc');
											break;
										case 'by_payroll_remittance_agency_by_employee':
											$retval['columns'][] = 'payroll_remittance_agency_name';
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';
											$retval['columns'][] = 'middle_name';
											$retval['columns'][] = 'sin';

											$retval['-2000-company_deduction_id'][] = TTUUID::getZeroID();

											$retval['group'][] = 'payroll_remittance_agency_name';
											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';
											$retval['group'][] = 'middle_name';
											$retval['group'][] = 'sin';

											$retval['sub_total'][] = 'payroll_remittance_agency_name';

											$retval['sort'][] = array('payroll_remittance_agency_name' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('middle_name' => 'asc');
											$retval['sort'][] = array('sin' => 'asc');
											break;
										case 'by_payroll_remittance_agency_by_company_deduction_by_employee':
											$retval['columns'][] = 'payroll_remittance_agency_name';
											$retval['columns'][] = 'company_deduction_name';
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';
											$retval['columns'][] = 'middle_name';
											$retval['columns'][] = 'sin';

											$retval['-2000-company_deduction_id'][] = TTUUID::getZeroID();

											$retval['group'][] = 'payroll_remittance_agency_name';
											$retval['group'][] = 'company_deduction_name';
											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';
											$retval['group'][] = 'middle_name';
											$retval['group'][] = 'sin';

											$retval['sub_total'][] = 'payroll_remittance_agency_name';
											$retval['sub_total'][] = 'company_deduction_name';

											$retval['sort'][] = array('payroll_remittance_agency_name' => 'asc');
											$retval['sort'][] = array('company_deduction_name' => 'asc');
											$retval['sort'][] = array('last_name' => 'asc');
											$retval['sort'][] = array('first_name' => 'asc');
											$retval['sort'][] = array('middle_name' => 'asc');
											$retval['sort'][] = array('sin' => 'asc');
											break;
									}
								}
							}
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

	function getCompanyDeductionData( $company_id, $filter_data, $columns ) {
		$company_deduction_data = array();

		$cdlf = TTnew( 'CompanyDeductionListFactory' );
		//Order by calculation_id DESC so Federal Taxes are always the parent of Fixed Amount records for example.
		$cdlf->getAPISearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, NULL, NULL, NULL, array('calculation_id' => 'desc') );
		Debug::Text('Company Deductions: '. $cdlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $cdlf->getRecordCount() > 0 ) {
			$duplicate_pay_stub_entry_account_map = array();

			//Splitting or combining data across agency/company deductions is important to prevent a report from duplicating subject wages.
			//  For example if two taxes are selected (ie: 2x Workers Comp) and the employee has both deducted, the subject wages might be doubled up.
			if ( isset($columns['payroll_remittance_agency_name']) ) {
				$enable_split_by_payroll_remittance_agency = TRUE;
			} else {
				$enable_split_by_payroll_remittance_agency = FALSE;
			}

			if ( isset($columns['company_deduction_name']) ) {
				$enable_split_by_company_deduction = TRUE;
			} else {
				$enable_split_by_company_deduction = FALSE;
			}

			$i = 0;
			foreach( $cdlf as $cd_obj ) {
				//Check to see if its a Tax/Deduction that can be combined.
				//Just State, District/Local taxes can't be combined, due to employees working in multiple jurisdictions and may or may not be taxable. So they could be assigned to multiple Tax/Deductions of the same State.
				if ( strtoupper( $cd_obj->getCountry() ) == 'US' AND in_array( $cd_obj->getCalculation(), array(200, 300) ) ) {
					$can_be_combined = FALSE;
				} else {
					$can_be_combined = TRUE;
				}

				if ( $can_be_combined == TRUE AND $enable_split_by_payroll_remittance_agency == FALSE ) {
					$tmp_payroll_remittance_agency_id = TTUUID::getZeroID();
				} else {
					$tmp_payroll_remittance_agency_id = $cd_obj->getPayrollRemittanceAgency();
				}

				Debug::Text('  Company Deduction: '. $cd_obj->getName() .' Can Be Combined: '. (int)$can_be_combined .' Split: Agency: '. (int)$enable_split_by_payroll_remittance_agency .' Deduction: '. (int)$enable_split_by_company_deduction, __FILE__, __LINE__, __METHOD__, 10);

				if ( $can_be_combined == TRUE AND $enable_split_by_company_deduction == FALSE ) {
					$tmp_company_deduction_id = TTUUID::getZeroID();
				} else {
					if ( $can_be_combined == TRUE AND isset( $duplicate_pay_stub_entry_account_map[$tmp_payroll_remittance_agency_id][$cd_obj->getPayStubEntryAccount()] ) ) {
						$tmp_company_deduction_id = $duplicate_pay_stub_entry_account_map[$tmp_payroll_remittance_agency_id][$cd_obj->getPayStubEntryAccount()];
						Debug::Text( 'Merging Company Deductions: Parent: ' . $company_deduction_data[$tmp_payroll_remittance_agency_id][ $tmp_company_deduction_id ]->getName() . ' (' . $tmp_company_deduction_id . ') Child: ' . $cd_obj->getName() . ' (' . $cd_obj->getId() . ')', __FILE__, __LINE__, __METHOD__, 10 );

						$company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->duplicate_of[] = $cd_obj;
					} else {
						$tmp_company_deduction_id = $cd_obj->getID();
					}
				}

				if ( !isset($company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id] ) ) { //Make sure when $enable_split_by_company_deduction == FALSE we don't overwrite the COMPOSITE object.
					$company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id] = $cd_obj;
				}

				if ( !isset($company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->include_psea_ids) ) {
					$company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->include_psea_ids = array();
				}
				$company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->include_psea_ids = array_merge( $company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->include_psea_ids, $cd_obj->getExpandedPayStubEntryAccountIDs( $cd_obj->getIncludePayStubEntryAccount() ) );

				if ( !isset($company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->exclude_psea_ids) ) {
					$company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->exclude_psea_ids = array();
				}
				$company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->exclude_psea_ids = array_merge( $company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->exclude_psea_ids, $cd_obj->getExpandedPayStubEntryAccountIDs( $cd_obj->getExcludePayStubEntryAccount() ) );

				if ( !isset($company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->tax_withheld_psea_ids) ) {
					$company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->tax_withheld_psea_ids = array();
				}
				$company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->tax_withheld_psea_ids = array_merge( $company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->tax_withheld_psea_ids, array( $cd_obj->getPayStubEntryAccount() ) );

				if ( !isset($company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->user_ids) ) {
					$company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->user_ids = array();
				}
				$company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->user_ids = array_merge( $company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->user_ids, (array)$cd_obj->getUser() );

				$duplicate_pay_stub_entry_account_map[$tmp_payroll_remittance_agency_id][$cd_obj->getPayStubEntryAccount()] = $tmp_company_deduction_id;

				$i++;
			}
		}

		//Debug::Arr( $company_deduction_data, 'Company Deductions Data: ', __FILE__, __LINE__, __METHOD__, 10);
		return $company_deduction_data;
	}

	function getUserDeductionData( $company_id, $filter_data ) {
		//To help determine MaximumTaxableWages, we need to get the UserDeduction records and call getMaximumPayStubEntryAccountAmount().
		$user_deduction_data = array();
		$udlf = TTnew( 'UserDeductionListFactory' );
		$udlf->getByCompanyIdAndCompanyDeductionId( $company_id, $filter_data );
		if ( $udlf->getRecordCount() > 0 ) {
			foreach( $udlf as $ud_obj ) {
				if ( $ud_obj->getMaximumPayStubEntryAccountAmount() != FALSE OR $ud_obj->getRate() != FALSE ) {
					$user_deduction_data[$ud_obj->getCompanyDeduction()][$ud_obj->getUser()] = array( 'maximum_pay_stub_entry_amount' => $ud_obj->getMaximumPayStubEntryAccountAmount(), 'rate' => $ud_obj->getRate() );
				}
			}
		}

		return $user_deduction_data;
	}

	function addPayStubEntry( $cd_obj, $pse_obj, $user_deduction_data ) {
		//If the deduction amount has no where to go, just exit early as its essentially disabled.
		if ( empty( $tax_withheld_psea_ids ) == FALSE ) {
			return TRUE;
		}

		$company_deduction_id = $cd_obj->getId();
		$remittance_agency_id = $cd_obj->getPayrollRemittanceAgency();

		$deduction_include_psea_ids = $cd_obj->include_psea_ids; //These should already be from getExpandedPayStubEntryAccountIDs()
		$deduction_exclude_psea_ids = $cd_obj->exclude_psea_ids; //These should already be from getExpandedPayStubEntryAccountIDs()
		$tax_withheld_psea_ids = $cd_obj->tax_withheld_psea_ids;

		$user_id = $pse_obj->getColumn( 'user_id' );
		$date_stamp = TTDate::strtotime( $pse_obj->getColumn( 'pay_stub_transaction_date' ) ); //Should match PayStubSummary, RemittanceSummary, TaxSummary, GeneralLedgerSummaryReport, etc... $date_stamp too.
		$run_id = $pse_obj->getColumn( 'pay_stub_run_id' );
		$pay_stub_entry_name_id = $pse_obj->getPayStubEntryNameId();


		//If the CompanyDeduction pay stub account does not match this current PSE PayStubEntryAccount, then check if the user is assigned to the CompanyDeduction, and if not we can return early as it doesn't apply to them.
		//  This helps in cases where employees work in multiple states and may have absence time in their resident state, preventing subject wages from being calculated on the absence earnings in foriegn states.
		//  Important: If an employee was taxed part of the year in one state and moved to a different state, they still need to be assigned to both Tax/Deductions, with just start/end dates specified instead. Otherwise subject wages will not be calculated, but tax withheld will still be.
		//             Also need to take into account Workers Comp and multiple rate groups, to ensure those
		if ( in_array( $pay_stub_entry_name_id, $tax_withheld_psea_ids ) == FALSE AND in_array( $user_id, (array)$cd_obj->user_ids ) == FALSE ) { //Use user_ids rather than getUser() as they could be merged in getCompanyDeductionData()
			//Debug::Text('    Skipping PSE record: Agency ID: '. $remittance_agency_id .' Deduction ID: '. $company_deduction_id .' PSE Name ID: '. $pay_stub_entry_name_id .' Amount: '. $pse_obj->getColumn('amount'), __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		//Debug::Text('    Processing PSE record: Agency ID: '. $remittance_agency_id .' Deduction ID: '. $company_deduction_id .' PSE Name ID: '. $pay_stub_entry_name_id .' Amount: '. $pse_obj->getColumn('amount'), __FILE__, __LINE__, __METHOD__, 10);
		if ( !isset( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ] ) ) {
			$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ] = array(
					'pay_period_start_date'       => strtotime( $pse_obj->getColumn( 'pay_period_start_date' ) ),
					'pay_period_end_date'         => strtotime( $pse_obj->getColumn( 'pay_period_end_date' ) ),
					'pay_period_transaction_date' => strtotime( $pse_obj->getColumn( 'pay_period_transaction_date' ) ),
					'pay_period'                  => strtotime( $pse_obj->getColumn( 'pay_period_transaction_date' ) ),
					'pay_period_id'               => $pse_obj->getColumn( 'pay_period_id' ),

					'pay_stub_start_date'       => strtotime( $pse_obj->getColumn( 'pay_stub_start_date' ) ),
					'pay_stub_end_date'         => strtotime( $pse_obj->getColumn( 'pay_stub_end_date' ) ),
					'pay_stub_transaction_date' => TTDate::getMiddleDayEpoch( strtotime( $pse_obj->getColumn( 'pay_stub_transaction_date' ) ) ), //Some transaction dates could be throughout the day for terminated employees being paid early, so always forward them to the middle of the day to keep group_by working correctly.
					'pay_stub_run_id'           => $run_id,
			);
		}

		if ( !isset( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ][ 'PA:' . $pay_stub_entry_name_id ] ) ) {
			$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ][ 'PA:' . $pay_stub_entry_name_id ] = 0;
		}
		$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ][ 'PA:' . $pay_stub_entry_name_id ] = bcadd( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ][ 'PA:' . $pay_stub_entry_name_id ], $pse_obj->getColumn( 'amount' ) );

		if ( !isset( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ][ 'PY:' . $pay_stub_entry_name_id ] ) ) {
			$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ][ 'PY:' . $pay_stub_entry_name_id ] = 0;
		}
		$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ][ 'PY:' . $pay_stub_entry_name_id ] = bcadd( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ][ 'PY:' . $pay_stub_entry_name_id ], $pse_obj->getColumn( 'ytd_amount' ) );


		if ( !isset( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['subject_wages'] ) ) {
			$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['subject_wages'] = 0;
		}
		$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['subject_wages'] = bcadd( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['subject_wages'], Misc::calculateIncludeExcludeAmount( $pse_obj->getColumn( 'amount' ), $pay_stub_entry_name_id, $deduction_include_psea_ids, $deduction_exclude_psea_ids ) );

		if ( !isset( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['subject_wages_ytd'] ) ) {
			$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['subject_wages_ytd'] = 0;
		}
		$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['subject_wages_ytd'] = bcadd( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['subject_wages_ytd'], Misc::calculateIncludeExcludeAmount( $pse_obj->getColumn( 'ytd_amount' ), $pay_stub_entry_name_id, $deduction_include_psea_ids, $deduction_exclude_psea_ids ) );

		if ( !isset( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['subject_units'] ) ) {
			$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['subject_units'] = 0;
		}
		$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['subject_units'] = bcadd( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['subject_units'], Misc::calculateIncludeExcludeAmount( $pse_obj->getColumn( 'units' ), $pay_stub_entry_name_id, $deduction_include_psea_ids, $deduction_exclude_psea_ids ) );

		if ( !isset( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['subject_rate'] ) ) {
			$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['subject_rate'] = 0;
		}
		$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['subject_rate'] = ( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['subject_units'] > 0 ) ? bcdiv( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['subject_wages'], $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['subject_units'] ) : 0;


		if ( !isset( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['taxable_wages'] ) ) {
			$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['taxable_wages'] = 0;
		}
		$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['taxable_wages'] = bcadd( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['taxable_wages'], Misc::calculateIncludeExcludeAmount( $pse_obj->getColumn( 'amount' ), $pay_stub_entry_name_id, $deduction_include_psea_ids, $deduction_exclude_psea_ids ) );

		if ( !isset( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['taxable_wages_ytd'] ) ) {
			$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['taxable_wages_ytd'] = 0;
		}
		$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['taxable_wages_ytd'] = bcadd( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['taxable_wages_ytd'], Misc::calculateIncludeExcludeAmount( $pse_obj->getColumn( 'ytd_amount' ), $pay_stub_entry_name_id, $deduction_include_psea_ids, $deduction_exclude_psea_ids ) );

		if ( empty( $tax_withheld_psea_ids ) == FALSE AND in_array( $pay_stub_entry_name_id, $tax_withheld_psea_ids ) ) {
			if ( !isset( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['tax_withheld'] ) ) {
				$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['tax_withheld'] = 0;
			}
			$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['tax_withheld'] = bcadd( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['tax_withheld'], $pse_obj->getColumn( 'amount' ) );
		}

		$pay_period_weeks = round( TTDate::getWeeks( ( TTDate::getEndDayEpoch( TTDate::strtotime( $pse_obj->getColumn( 'pay_stub_end_date' ) ) ) - TTDate::getBeginDayEpoch( TTDate::strtotime( $pse_obj->getColumn( 'pay_stub_start_date' ) ) ) ) ), 2 );

		//For unemployment reports, we need to know the weeks where renumeration was received, so count weeks between start/end date of pay period
		//Set pay period weeks once per transaction date (pay period)
		$first_run_id = min( array_keys( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ] ) );
		if ( !isset( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $first_run_id ][ $user_id ]['pay_period_weeks'] ) ) {
			$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['pay_period_weeks'] = $pay_period_weeks;
		}

		if ( !isset( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $first_run_id ][ $user_id ]['pay_period_taxable_wages_weeks'] )
				AND isset( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['taxable_wages'] )
				AND $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['taxable_wages'] > 0 ) {
			$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['pay_period_taxable_wages_weeks'] = $pay_period_weeks;
		}

		if ( !isset( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $first_run_id ][ $user_id ]['pay_period_tax_withheld_weeks'] )
				AND isset( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['tax_withheld'] )
				AND $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['tax_withheld'] > 0 ) {
			$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['pay_period_tax_withheld_weeks'] = $pay_period_weeks;
		}
		unset($first_run_id);

		if ( isset( $user_deduction_data[ $company_deduction_id ][ $user_id ] ) AND $user_deduction_data[ $company_deduction_id ][ $user_id ]['rate'] != FALSE ) {
			$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['company_deduction_rate'] = $user_deduction_data[ $company_deduction_id ][ $user_id ]['rate'];
		}

		return TRUE;
	}

	/**
	 * @param null $format
	 * @return bool
	 */
	function _getData( $format = NULL ) {
		$this->tmp_data = array('pay_stub_entry' => array(), 'user' => array() );

		$columns = $this->getColumnDataConfig();
		$filter_data = $this->getFilterConfig();
		$company_deduction_filter_data = array();

		if ( isset( $filter_data['company_deduction_id'] ) ) {
			$company_deduction_filter_data['id'] = $filter_data['company_deduction_id'];
		}

		if ( isset( $filter_data['payroll_remittance_agency_id'] ) ) {
			$company_deduction_filter_data['payroll_remittance_agency_id'] = $filter_data['payroll_remittance_agency_id'];
		}

		if ( isset( $filter_data['legal_entity_id'] ) ) {
			$company_deduction_filter_data['legal_entity_id'] = $filter_data['legal_entity_id'];
		}

		$filter_data['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'pay_stub', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		if ( $ulf->getRecordCount() > 0 ) {
			if ( isset( $filter_data['company_deduction_id'] ) == FALSE ) {
				$filter_data['company_deduction_id'] = '';
			}

			$company_deduction_data = $this->getCompanyDeductionData( $this->getUserObject()->getCompany(), $company_deduction_filter_data, $columns );
			$user_deduction_data = $this->getUserDeductionData( $this->getUserObject()->getCompany(), $filter_data['company_deduction_id'] );
			//Debug::Arr($user_deduction_data, 'User Deduction Maximum Amount Data: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( !isset($filter_data['exclude_ytd_adjustment']) ) {
				$filter_data['exclude_ytd_adjustment'] = FALSE;
			}

			//Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);

			//We have to use the same PSE records and combine them in different ways for different Tax/Deduction or Remittance Agencies.
			//  For example, the same earnings records are likely to count towards many different Tax/Deduction records.
			$pself = TTnew( 'PayStubEntryListFactory' );
			$pself->getAPIReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $pself->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
			if ( $pself->getRecordCount() > 0 ) {
				if ( is_array( $company_deduction_data ) AND count( $company_deduction_data ) > 0 ) {
					Debug::Text( 'Found Company Deductions...', __FILE__, __LINE__, __METHOD__, 10 );
					foreach ( $company_deduction_data as $payroll_remittance_agency_id => $payroll_remittance_agency_data ) {
						Debug::Text( '  Processing Remittance Agency: ' . $payroll_remittance_agency_id, __FILE__, __LINE__, __METHOD__, 10 );
						foreach ( $payroll_remittance_agency_data as $cd_obj ) {
							Debug::Text( '  Processing Company Deduction: ' . $cd_obj->getName(), __FILE__, __LINE__, __METHOD__, 10 );
							//Debug::Arr( $cd_obj->include_psea_ids, '    Include PSEA IDs: ', __FILE__, __LINE__, __METHOD__, 10 );
							//Debug::Arr( $cd_obj->exclude_psea_ids, '    Exclude PSEA IDs: ', __FILE__, __LINE__, __METHOD__, 10 );
							//Debug::Arr( $cd_obj->getPayStubEntryAccount(), '   Withheld PSEA IDs: ', __FILE__, __LINE__, __METHOD__, 10 );

							foreach ( $pself as $key => $pse_obj ) {
								$this->addPayStubEntry( $cd_obj, $pse_obj, $user_deduction_data );
								$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
							}
						}
					}
				}

				//Loop through all records and handle maximum amounts.
				//  Do this down here, since with include/exclude amounts, we could reach the maximum amount,
				//	then the next row has an excluded (negative) amount, so now we've capped the amount at a maximum, and its reduced by -XX.XX.
				//  Instead just handle all the include/exclude amount above first, then once that is done go through and cap any necessary amounts.
				if ( count($user_deduction_data) > 0 ) {
					foreach( $this->tmp_data['pay_stub_entry'] as $remittance_agency_id => $level1 ) {
						foreach ( $level1 as $company_deduction_id => $level2 ) {
							foreach ( $level2 as $date_stamp => $level3 ) {
								foreach ( $level3 as $run_id => $level4) {
									foreach ( $level4 as $user_id => $row ) {
										Debug::Text( 'Before Current Taxable Wages: ' . $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['taxable_wages'] . ' YTD: ' . $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['taxable_wages_ytd'], __FILE__, __LINE__, __METHOD__, 10 );
										if ( isset( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['taxable_wages'] )
												AND isset( $user_deduction_data[ $company_deduction_id ][ $user_id ] )
												AND $user_deduction_data[ $company_deduction_id ][ $user_id ]['maximum_pay_stub_entry_amount'] > 0
												AND $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['taxable_wages_ytd'] > $user_deduction_data[ $company_deduction_id ][ $user_id ]['maximum_pay_stub_entry_amount']
										) {
											//Make sure taxable wages abides by maximum amount properly.
											$tmp_taxable_wages_ytd_diff = ( $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['taxable_wages_ytd'] - $this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['taxable_wages'] );
											$tmp_taxable_wages_max_diff = ( $user_deduction_data[ $company_deduction_id ][ $user_id ]['maximum_pay_stub_entry_amount'] - $tmp_taxable_wages_ytd_diff );
											//Debug::Text('  Taxable Wages YTD Diff: '. $tmp_taxable_wages_ytd_diff .' Max Diff: '. $tmp_taxable_wages_max_diff, __FILE__, __LINE__, __METHOD__, 10);
											if ( $tmp_taxable_wages_ytd_diff < $user_deduction_data[ $company_deduction_id ][ $user_id ]['maximum_pay_stub_entry_amount'] ) {
												$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['taxable_wages'] = $tmp_taxable_wages_max_diff;
											} else {
												$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['taxable_wages'] = 0;
											}

											$this->tmp_data['pay_stub_entry'][ $remittance_agency_id ][ $company_deduction_id ][ $date_stamp ][ $run_id ][ $user_id ]['taxable_wages_ytd'] = $user_deduction_data[ $company_deduction_id ][ $user_id ]['maximum_pay_stub_entry_amount'];
											unset( $tmp_taxable_wages_ytd_diff, $tmp_taxable_wages_max_diff );
										}
										//Debug::Text('After Current Taxable Wages: '. $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$user_id]['taxable_wages'] .' YTD: '. $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$user_id]['taxable_wages_ytd'], __FILE__, __LINE__, __METHOD__, 10);
									}
								}
							}
						}
					}
					unset($level1, $level2, $level3, $remittance_agency_id, $date_stamp, $user_id, $row);
				}
			}
		}
		//Debug::Arr($this->tmp_data['pay_stub_entry'], 'Pay Stub Entry Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Total Rows: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( array_merge( (array)$this->getColumnDataConfig(), array( 'province' => TRUE, 'hire_date' => TRUE, 'termination_date' => TRUE , 'title_id' => TRUE ) ) );
			$this->tmp_data['user'][$u_obj->getId()]['total_user'] = 1;
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}

		//Get remittance agency data for joining.
		$pralf = TTnew( 'PayrollRemittanceAgencyListFactory' );
		$pralf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), array() );
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $pralf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $pralf as $key => $ra_obj ) {
			$this->tmp_data['payroll_remittance_agency'][$ra_obj->getId()] = Misc::addKeyPrefix( 'payroll_remittance_agency_', (array)$ra_obj->getObjectAsArray( array( 'id' => TRUE, 'name' => TRUE ) ) );
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}

		//Company Deduction data for joining...
		$cdlf = TTnew( 'CompanyDeductionListFactory' );
		$cdlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $company_deduction_filter_data );

		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $cdlf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		if ( $cdlf->getRecordCount() > 0 ) {
			foreach( $cdlf as $key => $cd_obj ) {
				$this->tmp_data['company_deduction'][$cd_obj->getId()] = Misc::addKeyPrefix( 'company_deduction_', (array)$cd_obj->getObjectAsArray( array('id' => TRUE, 'name' => TRUE, 'payroll_remittance_agency_id' => TRUE ) ) );

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}
		}

		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->tmp_data, 'TMP Data: ', __FILE__, __LINE__, __METHOD__, 10);
		return TRUE;
	}

	/**
	 * PreProcess data such as calculating additional columns from raw data etc...
	 * @return bool
	 */
	function _preProcess() {
		//Merge time data with user data
		$key = 0;
		if ( isset($this->tmp_data['pay_stub_entry']) ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['pay_stub_entry']), NULL, TTi18n::getText('Pre-Processing Data...') );

			//foreach( $this->tmp_data['pay_stub_entry'] as $date_stamp => $level_1 ) {
			foreach( $this->tmp_data['pay_stub_entry'] as $remittance_agency_id => $level_0 ) {
				foreach ( $this->tmp_data['pay_stub_entry'][$remittance_agency_id] as $company_deduction_id => $level_1 ) {
					foreach ( $level_1 as $date_stamp => $level_2 ) {
						foreach ( $level_2 as $run_id => $level_3 ) {
							foreach ( $level_3 as $user_id => $row ) {
								if ( isset( $this->tmp_data['user'][ $user_id ] ) ) {
									$date_columns = TTDate::getReportDates( 'transaction', $date_stamp, FALSE, $this->getUserObject(), array('pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date']) );

									if ( isset( $this->tmp_data['user'][ $user_id ]['hire_date'] ) ) {
										$hire_date_columns = TTDate::getReportDates( 'hire', TTDate::parseDateTime( $this->tmp_data['user'][ $user_id ]['hire_date'] ), FALSE, $this->getUserObject() );
									} else {
										$hire_date_columns = array();
									}

									if ( isset( $this->tmp_data['user'][ $user_id ]['termination_date'] ) ) {
										$termination_date_columns = TTDate::getReportDates( 'termination', TTDate::parseDateTime( $this->tmp_data['user'][ $user_id ]['termination_date'] ), FALSE, $this->getUserObject() );
									} else {
										$termination_date_columns = array();
									}

									$processed_data = array(
										//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
										//'pay_stub' => array('sort' => $row['pay_stub_transaction_date'], 'display' => TTDate::getDate('DATE', $row['pay_stub_transaction_date'] ) ),
									);
									//Need to make sure PSEA IDs are strings not numeric otherwise array_merge will re-key them.

									if ( isset( $this->tmp_data['company_deduction'][ $company_deduction_id ] ) ) {
										$tmp_company_deduction = $this->tmp_data['company_deduction'][ $company_deduction_id ];
									} else {
										$tmp_company_deduction = array();
									}

									if ( isset( $this->tmp_data['payroll_remittance_agency'][ $remittance_agency_id ] ) ) {
										$tmp_payroll_remittance_agency = $this->tmp_data['payroll_remittance_agency'][ $remittance_agency_id ];
									} else {
										$tmp_payroll_remittance_agency = array();
									}

									$this->data[] = array_merge( $this->tmp_data['user'][ $user_id ], $tmp_company_deduction, $tmp_payroll_remittance_agency, $row, $date_columns, $hire_date_columns, $termination_date_columns, $processed_data );

									$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
									$key++;
								}
							}
						}
					}
				}
			}

			unset($this->tmp_data, $row, $date_columns, $processed_data, $level_1 );
		}
		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	/**
	 * Formats report data for exporting to TimeTrex payment service.
	 * @return array
	 */
	function getPaymentServicesData( $prae_obj, $pra_obj, $rs_obj, $pra_user_obj ) {
		//Make sure we have the columns we need.
		$report_data['config'] = $this->getConfig();
		$report_data['config']['columns'] = array( 'payroll_remittance_agency_name', 'transaction-date_stamp', 'subject_wages', 'taxable_wages', 'tax_withheld', 'total_user' );
		$report_data['config']['group'] = array( 'payroll_remittance_agency_name', 'transaction-date_stamp' );
		$this->setConfig( (array)$report_data['config'] );

		$output_data = $this->getOutput( 'payment_services' );
		Debug::Arr( $output_data, 'Raw Report data!', __FILE__, __LINE__, __METHOD__, 10);

		if ( $this->hasData() ) {
			//Get last Grand Total row.
			$last_row = end( $output_data );

			if ( isset( $last_row['_total'] ) ) {
				$batch_id = date( 'M d', $prae_obj->getEndDate() );

				$amount_due = ( isset( $last_row['tax_withheld'] ) ) ? $last_row['tax_withheld'] : NULL;

				$retarr = array(
						'object'          => __CLASS__,
						'user_success_message' => TTi18n::gettext( 'Payment submitted successfully for $%1', array( Misc::MoneyFormat( $amount_due ) ) ),
						'agency_report_data' => array (
							'total_employees' => ( isset( $last_row['total_user'] ) ) ? (int)$last_row['total_user'] : NULL,
							'subject_wages'   => ( isset( $last_row['subject_wages'] ) ) ? $last_row['subject_wages'] : NULL,
							'taxable_wages'   => ( isset( $last_row['taxable_wages'] ) ) ? $last_row['taxable_wages'] : NULL,
							'amount_withheld' => ( isset( $last_row['tax_withheld'] ) ) ? $last_row['tax_withheld'] : NULL,
							'amount_due'      => $amount_due,

							'remote_batch_id' => $batch_id,

							//Generate a consistent remote_id based on the exact pay stubs that are selected, the remittance agency event, and batch ID.
							//This helps to prevent duplicate records from be created, as well as work across separate or split up batches that may be processed.
							'remote_id' => TTUUID::convertStringToUUID( md5( $prae_obj->getId() . $batch_id ) )
						),
				);

				return $retarr;
			}
		}

		Debug::Text('No report data!', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}
}
?>
