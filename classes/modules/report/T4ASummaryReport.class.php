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
class T4ASummaryReport extends Report {

	protected $user_ids = array();

	/**
	 * T4ASummaryReport constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText('T4A Summary Report');
		$this->file_name = 't4a_summary';

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
				AND $this->getPermissionObject()->Check('report', 'view_t4_summary', $user_id, $company_id ) ) {
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
										'-1100-pdf_form' => TTi18n::gettext('Employee (One Employee/Page)'),
										'-1110-pdf_form_government' => TTi18n::gettext('Government (Multiple Employees/Page)'),
										'-1120-efile_xml' => TTi18n::gettext('eFile'),
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
										'-3000-custom_filter' => TTi18n::gettext('Custom Filter'),

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
				//$retval = TTDate::getReportDateOptions( NULL, TTi18n::getText('Date'), 13, TRUE );
				$retval = array();
				break;
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), NULL, 'T4ASummaryReport', 'custom_column' );
					if ( is_array($custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('filter_column_type_ids'), NULL, 'T4ASummaryReport', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('dynamic_format_ids'), 'T4ASummaryReport', 'custom_column' );
					if ( is_array($report_dynamic_custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('static_format_ids'), 'T4ASummaryReport', 'custom_column' );
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
										'-1060-province' => TTi18n::gettext('Province/State'),
										'-1070-country' => TTi18n::gettext('Country'),
										'-1080-group' => TTi18n::gettext('Group'),
										'-1090-default_branch' => TTi18n::gettext('Default Branch'),
										'-1100-default_department' => TTi18n::gettext('Default Department'),
										'-1110-currency' => TTi18n::gettext('Currency'),
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
						'-2110-income_tax'               => TTi18n::gettext( 'Income Tax (22)' ),
						'-2115-pension'                  => TTi18n::gettext( 'Pension/Superannuation (16)' ),
						'-2120-lump_sum_payment'         => TTi18n::gettext( 'Lump Sum Payment (18)' ),
						'-2130-self_employed_commission' => TTi18n::gettext( 'Self-Employed Commission (20)' ),
						'-2135-annuities'                => TTi18n::gettext( 'Annuities (24)' ),
						'-2140-service_fees'             => TTi18n::gettext( 'Service Fees (24)' ),
						'-2200-other_box_0'              => TTi18n::gettext( 'Other Box 1' ),
						'-2210-other_box_1'              => TTi18n::gettext( 'Other Box 2' ),
						'-2220-other_box_2'              => TTi18n::gettext( 'Other Box 3' ),
						'-2220-other_box_3'              => TTi18n::gettext( 'Other Box 4' ),
						'-2220-other_box_4'              => TTi18n::gettext( 'Other Box 5' ),
						'-2220-other_box_5'              => TTi18n::gettext( 'Other Box 6' ),
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
			case 'type':
				$retval = array(
											'-1010-O' => TTi18n::getText('Original'),
											'-1020-A' => TTi18n::getText('Amended'),
											'-1030-C' => TTi18n::getText('Cancel'),
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
		unset($this->form_obj['gf']);

		return TRUE;
	}

	/**
	 * @return mixed
	 */
	function getT4AObject() {
		if ( !isset($this->form_obj['t4a']) OR !is_object($this->form_obj['t4a']) ) {
			$this->form_obj['t4a'] = $this->getFormObject()->getFormObject( 'T4A', 'CA' );
			return $this->form_obj['t4a'];
		}

		return $this->form_obj['t4a'];
	}

	/**
	 * @return bool
	 */
	function clearT4AObject() {
		unset($this->form_obj['t4a']);

		return TRUE;
	}

	/**
	 * @return mixed
	 */
	function getT4ASumObject() {
		if ( !isset($this->form_obj['t4asum']) OR !is_object($this->form_obj['t4asum']) ) {
			$this->form_obj['t4asum'] = $this->getFormObject()->getFormObject( 'T4ASum', 'CA' );
			return $this->form_obj['t4asum'];
		}

		return $this->form_obj['t4asum'];
	}

	/**
	 * @return bool
	 */
	function clearT4ASumObject() {
		unset($this->form_obj['t4asum']);

		return TRUE;
	}

	/**
	 * @return mixed
	 */
	function getT619Object() {
		if ( !isset($this->form_obj['t619']) OR !is_object($this->form_obj['t619']) ) {
			$this->form_obj['t619'] = $this->getFormObject()->getFormObject( 'T619', 'CA' );
			return $this->form_obj['t619'];
		}

		return $this->form_obj['t619'];
	}

	/**
	 * @return bool
	 */
	function clearT619SumObject() {
		unset($this->form_obj['t619']);

		return TRUE;
	}

	/**
	 * @return array
	 */
	function formatFormConfig() {
		$default_include_exclude_arr = array( 'include_pay_stub_entry_account' => array(), 'exclude_pay_stub_entry_account' => array() );

		$default_arr = array(
				'pension' => $default_include_exclude_arr,
				'lump_sum_payment' => $default_include_exclude_arr,
				'income_tax' => $default_include_exclude_arr,
				'annuities' => $default_include_exclude_arr,
				'self_employed_commission' => $default_include_exclude_arr,
				'service_fees' => $default_include_exclude_arr,
				'other_box' => array(
									0 => $default_include_exclude_arr,
									1 => $default_include_exclude_arr,
									2 => $default_include_exclude_arr,
									3 => $default_include_exclude_arr,
									4 => $default_include_exclude_arr,
									5 => $default_include_exclude_arr,
									6 => $default_include_exclude_arr,
									),
			);

		$retarr = array_merge( $default_arr, (array)$this->getFormConfig() );
		return $retarr;
	}

	//Get raw data for report

	/**
	 * @param null $format
	 * @return bool
	 */
	function _getData( $format = NULL ) {
		$this->tmp_data = array( 'pay_stub_entry' => array(), 'user' => array(), 'legal_entity' => array() );

		$filter_data = $this->getFilterConfig();
		$form_data = $this->formatFormConfig();

		$pself = TTnew( 'PayStubEntryListFactory' );
		$pself->getAPIReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $pself->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		if ( $pself->getRecordCount() > 0 ) {
			foreach( $pself as $key => $pse_obj ) {
				$user_id = $this->user_ids[] = $pse_obj->getColumn('user_id');
				$pay_stub_entry_name_id = $pse_obj->getPayStubEntryNameId();

				if ( !isset($this->tmp_data['pay_stub_entry'][$user_id]) ) {
					$this->tmp_data['pay_stub_entry'][$user_id] = array(
																'date_stamp' => strtotime( $pse_obj->getColumn('pay_stub_transaction_date') ),
																'pay_period_start_date' => strtotime( $pse_obj->getColumn('pay_stub_start_date') ),
																'pay_period_end_date' => strtotime( $pse_obj->getColumn('pay_stub_end_date') ),
																'pay_period_transaction_date' => strtotime( $pse_obj->getColumn('pay_stub_transaction_date') ),
																'pay_period' => strtotime( $pse_obj->getColumn('pay_stub_transaction_date') ),
															);
				}


				if ( isset($this->tmp_data['pay_stub_entry'][$user_id]['psen_ids'][$pay_stub_entry_name_id]) ) {
					$this->tmp_data['pay_stub_entry'][$user_id]['psen_ids'][$pay_stub_entry_name_id] = bcadd( $this->tmp_data['pay_stub_entry'][$user_id]['psen_ids'][$pay_stub_entry_name_id], $pse_obj->getColumn('amount') );
				} else {
					$this->tmp_data['pay_stub_entry'][$user_id]['psen_ids'][$pay_stub_entry_name_id] = $pse_obj->getColumn('amount');
				}

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}

			if ( isset($this->tmp_data['pay_stub_entry']) AND is_array($this->tmp_data['pay_stub_entry']) ) {
				foreach($this->tmp_data['pay_stub_entry'] as $user_id => $data_b) {

					$this->tmp_data['pay_stub_entry'][$user_id]['pension'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['pension']['include_pay_stub_entry_account'], $form_data['pension']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['lump_sum_payment'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['lump_sum_payment']['include_pay_stub_entry_account'], $form_data['lump_sum_payment']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['income_tax'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['income_tax']['include_pay_stub_entry_account'], $form_data['income_tax']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['annuities'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['annuities']['include_pay_stub_entry_account'], $form_data['annuities']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['self_employed_commission'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['self_employed_commission']['include_pay_stub_entry_account'], $form_data['self_employed_commission']['exclude_pay_stub_entry_account'] );
					$this->tmp_data['pay_stub_entry'][$user_id]['service_fees'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['service_fees']['include_pay_stub_entry_account'], $form_data['service_fees']['exclude_pay_stub_entry_account'] );

					for( $n = 0; $n <= 4; $n++) {
						if ( isset($form_data['other_box'][$n]) ) {
							$this->tmp_data['pay_stub_entry'][$user_id]['other_box_' . $n] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['other_box'][$n]['include_pay_stub_entry_account'], $form_data['other_box'][$n]['exclude_pay_stub_entry_account'] );
						}
					}
				}
			}
		}

		$this->user_ids = array_unique( $this->user_ids ); //Used to get the total number of employees.

		//Debug::Arr($this->user_ids, 'User IDs: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->form_data, 'Form Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->tmp_data, 'Tmp Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		//Debug::Text(' User Total Rows: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( $this->getColumnDataConfig() );
			$this->tmp_data['user'][$u_obj->getId()]['user_id'] = $u_obj->getId();
			$this->tmp_data['user'][$u_obj->getId()]['legal_entity_id'] = $u_obj->getLegalEntity();
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}

		//Get legal entity data for joining.
		/** @var LegalEntityListFactory $lelf */
		$lelf = TTnew( 'LegalEntityListFactory' );
		$lelf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		//Debug::Text( ' User Total Rows: ' . $lelf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
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
		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$this->tmp_data['remittance_agency'] = array();

		$filter_data['agency_id'] = array('10:CA:00:00:0010'); //CA federal
		/** @var PayrollRemittanceAgencyListFactory $ralf */
		$ralf = TTnew( 'PayrollRemittanceAgencyListFactory' );
		$ralf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		//Debug::Text( ' Remittance Agency Total Rows: ' . $ralf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $lelf->getRecordCount(), NULL, TTi18n::getText( 'Retrieving Remittance Agency Data...' ) );
		if ( $ralf->getRecordCount() > 0 ) {
			foreach ( $ralf as $key => $ra_obj ) {
				$this->form_data['remittance_agency'][$ra_obj->getLegalEntity()] = $ra_obj;
				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}
		}

		return TRUE;
	}

	//PreProcess data such as calculating additional columns from raw data etc...

	/**
	 * @param null $format
	 * @return bool
	 */
	function _preProcess( $format = NULL ) {
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['pay_stub_entry']), NULL, TTi18n::getText('Pre-Processing Data...') );

		//Merge time data with user data
		$key = 0;
		if ( isset($this->tmp_data['pay_stub_entry']) ) {
			$sort_columns = $this->getSortConfig();

			foreach( $this->tmp_data['pay_stub_entry'] as $user_id => $row ) {
				if ( isset( $this->tmp_data['user'][$user_id] ) ) {
					$date_columns = TTDate::getReportDates( NULL, $row['date_stamp'], FALSE, $this->getUserObject(), array('pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date']) );
					$processed_data = array( 'user_id' => $user_id );

					$tmp_legal_array = array();
					if ( isset($this->tmp_data['legal_entity'][$this->tmp_data['user'][$user_id]['legal_entity_id']]) ) {
						$tmp_legal_array = $this->tmp_data['legal_entity'][$this->tmp_data['user'][$user_id]['legal_entity_id']];
					}

					$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data, $tmp_legal_array );

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
					$key++;
				}

			}
			unset( $row, $date_columns, $processed_data, $tmp_legal_array);
		}

		//Total data per employee for the T4 forms. Just include the columns that are necessary for the form.
		if ( is_array($this->data) AND !($format == 'html' OR $format == 'pdf') ) {
			$t4_dollar_columns = array('income', 'tax', 'employee_cpp', 'ei_earnings', 'cpp_earnings', 'employee_ei', 'union_dues', 'rpp', 'charity', 'pension_adjustment', 'employer_ei', 'employer_cpp', 'other_box_0', 'other_box_1', 'other_box_2', 'other_box_3', 'other_box_4' );

			Debug::Text('Calculating Form Data...', __FILE__, __LINE__, __METHOD__, 10);
			foreach( $this->data as $row ) {
				if ( !isset($this->form_data['user'][$row['legal_entity_id']][$row['user_id']]) ) {
					$this->form_data['user'][$row['legal_entity_id']][$row['user_id']] = array( 'user_id' => $row['user_id'] );
				}

				foreach( $row as $key => $value ) {
					if ( in_array( $key, $t4_dollar_columns) ) {
						if ( !isset($this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key]) ) {
							$this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key] = 0;
						}
						$this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key] = bcadd( $this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key], $value );
					} else {
						$this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key] = $value;
					}
				}
			}
		}

		return TRUE;
	}

	/**
	 * @param null $format
	 * @return array|bool
	 */
	function _outputPDFForm( $format = NULL ) {
		Debug::Text('Generating Form... Format: '. $format, __FILE__, __LINE__, __METHOD__, 10);

		$setup_data = $this->getFormConfig();
		$filter_data = $this->getFilterConfig();
//		Debug::Arr($setup_data, 'Setup Data: ', __FILE__, __LINE__, __METHOD__, 10);
//		Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);
//		Debug::Arr($this->data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$current_company = $this->getUserObject()->getCompanyObject();
		if ( !is_object($current_company) ) {
			Debug::Text('Invalid company object...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ( !isset($setup_data['status_id']) OR $setup_data['status_id'] == 0 ) {
			$setup_data['status_id'] = 'O'; //Original
		}

		if ( stristr( $format, 'government' ) ) {
			$form_type = 'government';
		} else {
			$form_type = 'employee';
		}
		$file_arr = array();
		$file_name = NULL;
		if ( isset($this->form_data['user']) AND is_array($this->form_data['user']) ) {
			$file_name = 't4a_summary.ext';

			$this->sortFormData(); //Make sure forms are sorted.

			foreach($this->form_data['user'] as $legal_entity_id => $user_rows) {
				if ( isset($this->form_data['legal_entity'][$legal_entity_id]) == FALSE ) {
					Debug::Text('Missing Legal Entity: '.$legal_entity_id, __FILE__, __LINE__, __METHOD__, 10);
					continue;
				}

				if ( isset($this->form_data['remittance_agency'][$legal_entity_id]) == FALSE ) {
					Debug::Text('Missing Remittance Agency: '.$legal_entity_id, __FILE__, __LINE__, __METHOD__, 10);
					continue;
				}

				$x = 0; //Progress bar only.
				$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($user_rows), NULL, TTi18n::getText('Generating Forms...') );

				/** @var LegalEntityFactory $legal_entity_obj */
				$legal_entity_obj = $this->form_data['legal_entity'][$legal_entity_id];
				$company_name = $legal_entity_obj->getTradeName(); //T4As show Operating/Trade name on the forms.

				if ( is_object( $this->form_data['remittance_agency'][ $legal_entity_id ] ) ) {
					$contact_user_obj = $this->form_data['remittance_agency'][ $legal_entity_id ]->getContactUserObject();
				}
				if ( !isset( $contact_user_obj ) OR !is_object( $contact_user_obj ) ) {
					$contact_user_obj = $this->getUserObject();
				}

				if ( $format == 'efile_xml' OR $format == 'payment_services' ) {
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
					$t619->contact_email = ( $contact_user_obj->getWorkEmail() != '' ) ? $contact_user_obj->getWorkEmail() : ( ( $contact_user_obj->getHomeEmail() != '' ) ? $contact_user_obj->getHomeEmail() : NULL );
					$t619->company_name = $company_name;
					$this->getFormObject()->addForm( $t619 );
				}

				$t4a = $this->getT4AObject();
				if ( isset( $setup_data['include_t4a_back'] ) AND $setup_data['include_t4a_back'] == 1 ) {
					$t4a->setShowInstructionPage( TRUE );
				}
				Debug::Text( 'Form Type: ' . $form_type, __FILE__, __LINE__, __METHOD__, 10 );

				$t4a->setType( $form_type );
				$t4a->setStatus( $setup_data['status_id'] );
				$t4a->year = TTDate::getYear( $filter_data['start_date'] );
				if ( isset($this->form_data['remittance_agency'][$legal_entity_id]) ) {
					$t4a->payroll_account_number = $this->form_data['remittance_agency'][$legal_entity_id]->getPrimaryIdentification();//( isset( $setup_data['payroll_account_number'] ) AND $setup_data['payroll_account_number'] != '' ) ? $setup_data['payroll_account_number'] : $current_company->getBusinessNumber();
					$t4a->company_name = $company_name;
				}

				$report_meta_data = array();
				foreach ( $user_rows as $user_row_key => $row ) {
					if ( !isset( $row['user_id'] ) ) {
						Debug::Text( 'User ID not set!', __FILE__, __LINE__, __METHOD__, 10 );
						continue;
					}

					$ulf = TTnew( 'UserListFactory' );
					$ulf->getById( TTUUID::castUUID($row['user_id']) );
					if ( $ulf->getRecordCount() == 1 ) {
						$user_obj = $ulf->getCurrent();

						$employment_province = $user_obj->getProvince();
						//If employees address is out of the country, use the company province instead.
						if ( strtolower( $user_obj->getCountry() ) != 'ca' ) {
							$employment_province = $legal_entity_obj->getProvince();
							Debug::Text( '  Using Company Province of Employment: ' . $employment_province, __FILE__, __LINE__, __METHOD__, 10 );
						}

						//Determine the province of employment...
						$cdlf = TTnew( 'CompanyDeductionListFactory' );
						if ( isset( $setup_data['tax']['include_pay_stub_entry_account'] ) ) {
							$cdlf->getByCompanyIDAndUserIdAndCalculationIdAndPayStubEntryAccountID( $current_company->getId(), $user_obj->getId(), 200, $setup_data['tax']['include_pay_stub_entry_account'] );
							if ( $setup_data['tax']['include_pay_stub_entry_account'] != 0
									AND $cdlf->getRecordCount() > 0
							) {
								//Loop through all Tax/Deduction records to find one
								foreach ( $cdlf as $cd_obj ) {
									if ( $cd_obj->getStatus() == 10 AND strtolower( $cd_obj->getCountry() ) == 'ca' ) {
										$employment_province = $cd_obj->getProvince();
										Debug::Text( '  Deduction Province of Employment: ' . $employment_province, __FILE__, __LINE__, __METHOD__, 10 );
									}

								}
							}
						}
						unset( $cdlf, $cd_obj );
						Debug::Text( '  Final Province of Employment: ' . $employment_province, __FILE__, __LINE__, __METHOD__, 10 );

						$ee_data = array(
								'first_name'          => $user_obj->getFirstName(),
								'middle_name'         => $user_obj->getMiddleName(),
								'last_name'           => $user_obj->getLastName(),
								'address1'            => $user_obj->getAddress1(),
								'address2'            => $user_obj->getAddress2(),
								'city'                => $user_obj->getCity(),
								'province'            => ( ( $user_obj->getCountry() == 'CA' OR $user_obj->getCountry() == 'US' ) ? ( ( $user_obj->getProvince() != '00' ) ? $user_obj->getProvince() : NULL ) : 'ZZ' ),
								'country'             => Option::getByKey( $user_obj->getCountry(), $current_company->getOptions( 'country' ) ),
								'country_code'        => $user_obj->getCountry(),
								'employment_province' => ( $employment_province != '00' ) ? $employment_province : NULL,
								'postal_code'         => $user_obj->getPostalCode(),
								'sin'                 => $user_obj->getSIN(),
								'employee_number'     => $user_obj->getEmployeeNumber(),

								//If lines with dollar amounts change, update $amount_boxes below.
								'l16'                 => $row['pension'],
								'l18'                 => $row['lump_sum_payment'],
								'l22'                 => $row['income_tax'],
								'l20'                 => $row['self_employed_commission'],
								'l24'                 => $row['annuities'],
								'l48'                 => $row['service_fees'],
								'other_box_0_code'    => NULL,
								'other_box_0'         => NULL,
								'other_box_1_code'    => NULL,
								'other_box_1'         => NULL,
								'other_box_2_code'    => NULL,
								'other_box_2'         => NULL,
								'other_box_3_code'    => NULL,
								'other_box_3'         => NULL,
								'other_box_4_code'    => NULL,
								'other_box_4'         => NULL,
								'other_box_5_code'    => NULL,
								'other_box_5'         => NULL,
						);

						//Boxes that contain dollar amounts, so we can determine if the T4a is "blank" or not. **This should exclude L22 as in theory they will always have that**
						$amount_boxes = array( 'l16', 'l18', 'l20', 'l24', 'l48', 'other_box_0', 'other_box_1', 'other_box_2', 'other_box_3', 'other_box_4', 'other_box_5' );


						if ( isset( $row['other_box_0'] ) AND $row['other_box_0'] > 0 AND isset( $setup_data['other_box'][0]['box'] ) AND $setup_data['other_box'][0]['box'] != '' ) {
							$ee_data['other_box_0_code'] = $setup_data['other_box'][0]['box'];
							$ee_data['other_box_0'] = $row['other_box_0'];
						}

						if ( isset( $row['other_box_1'] ) AND $row['other_box_1'] > 0 AND isset( $setup_data['other_box'][1]['box'] ) AND $setup_data['other_box'][1]['box'] != '' ) {
							$ee_data['other_box_1_code'] = $setup_data['other_box'][1]['box'];
							$ee_data['other_box_1'] = $row['other_box_1'];
						}

						if ( isset( $row['other_box_2'] ) AND $row['other_box_2'] > 0 AND isset( $setup_data['other_box'][2]['box'] ) AND $setup_data['other_box'][2]['box'] != '' ) {
							$ee_data['other_box_2_code'] = $setup_data['other_box'][2]['box'];
							$ee_data['other_box_2'] = $row['other_box_2'];
						}

						if ( isset( $row['other_box_3'] ) AND $row['other_box_3'] > 0 AND isset( $setup_data['other_box'][3]['box'] ) AND $setup_data['other_box'][3]['box'] != '' ) {
							$ee_data['other_box_3_code'] = $setup_data['other_box'][3]['box'];
							$ee_data['other_box_3'] = $row['other_box_3'];
						}

						if ( isset( $row['other_box_4'] ) AND $row['other_box_4'] > 0 AND isset($setup_data['other_box'][4]['box']) AND $setup_data['other_box'][4]['box'] != '') {
							$ee_data['other_box_4_code'] = $setup_data['other_box'][4]['box'];
							$ee_data['other_box_4'] = $row['other_box_4'];
						}

						//Make sure there is actually data on the T4a, otherwise skip the employee.
						$tmp_total_amount_boxes = 0;
						foreach( $amount_boxes as $amount_box ) {
							if ( isset($ee_data[$amount_box]) AND is_numeric($ee_data[$amount_box]) ) {
								$tmp_total_amount_boxes += $ee_data[$amount_box];
							}
						}

						if ( $tmp_total_amount_boxes != 0 ) {
							if ( $format == 'payment_services' ) {
								$report_meta_data['t4a'][] = array('remote_id' => $row['user_id'], 'first_name' => $user_obj->getFirstName(), 'last_name' => $user_obj->getLastName(), 'sin' => $user_obj->getSIN(), 'l22' => $ee_data['l22']);
							}

							$t4a->addRecord( $ee_data );

							if ( $format == 'pdf_form_publish_employee' ) {
								// generate PDF for every employee and assign to each government document records
								$this->getFormObject()->addForm( $t4a );
								GovernmentDocumentFactory::addDocument( $user_obj->getId(), 20, 102, TTDate::getEndYearEpoch( $filter_data['end_date'] ), $this->getFormObject()->output( 'PDF' ) );
								$this->getFormObject()->clearForms();
							}
						} else {
							Debug::Text('  No amounts on T4A skipping: '. $user_obj->getFullName(), __FILE__, __LINE__, __METHOD__, 10);
							unset($user_rows[$user_row_key]); //Remove user data from array so it doesn't get added to totals or counted as an employee.
						}
						unset($ee_data);
					}

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $x );
					$x++;
				}

				$this->getFormObject()->addForm( $t4a );

				if ( $format == 'pdf_form_publish_employee' ) {
					$user_generic_status_batch_id = GovernmentDocumentFactory::saveUserGenericStatus( $this->getUserObject()->getId() );
					return $user_generic_status_batch_id;
				}

				if ( $t4a->countRecords() > 0 AND ( $form_type == 'government' OR $format == 'efile_xml' OR $format == 'payment_services' ) ) {
					//Handle T4ASummary
					$t4as = $this->getT4ASumObject();
					$t4as->setStatus( $setup_data['status_id'] );
					$t4as->year = $t4a->year;
					$t4as->payroll_account_number = $t4a->payroll_account_number;

					$t4as->company_name = $legal_entity_obj->getTradeName(); //T4As show Operating/Trade name on the forms.
					$t4as->company_address1 = $legal_entity_obj->getAddress1();
					$t4as->company_address2 = $legal_entity_obj->getAddress2();
					$t4as->company_city = $legal_entity_obj->getCity();
					$t4as->company_province = $legal_entity_obj->getProvince();
					$t4as->company_postal_code = $legal_entity_obj->getPostalCode();

					$t4as->l76 = $contact_user_obj->getFullName(); //Contact name.
					$t4as->l78 = $contact_user_obj->getWorkPhone();

					$total_row = Misc::ArrayAssocSum( $this->form_data['user'][ $legal_entity_id ] );

					$t4as->l88 = $t4a->countRecords();
					$t4as->l16 = ( isset( $total_row['pension'] ) ) ? $total_row['pension'] : NULL;
					$t4as->l22 = ( isset( $total_row['income_tax'] ) ) ? $total_row['income_tax'] : NULL;
					$t4as->l18 = ( isset( $total_row['lump_sum_payment'] ) ) ? $total_row['lump_sum_payment'] : NULL;
					$t4as->l20 = ( isset( $total_row['self_employed_commission'] ) ) ? $total_row['self_employed_commission'] : NULL;
					$t4as->l24 = ( isset( $total_row['annuities'] ) ) ? $total_row['annuities'] : NULL;
					$t4as->l48 = ( isset( $total_row['service_fees'] ) ) ? $total_row['service_fees'] : NULL;

					if ( isset( $setup_data['other_box'] ) ) {
						foreach ( $setup_data['other_box'] as $key => $other_box_data ) {
							//Debug::Text('zFound other box total for T4A Sum: '. $key .' Code: '. $other_box_data['box'], __FILE__, __LINE__, __METHOD__, 10);
							if ( in_array( (int)$other_box_data['box'], array(28, 30, 32, 34, 40, 42) ) ) {
								//Debug::Text('Found other box total for T4A Sum: '. $key .' Code: '. $other_box_data['box'], __FILE__, __LINE__, __METHOD__, 10);
								$object_var = 'l' . (int)$other_box_data['box'];
								$t4as->$object_var = $total_row[ 'other_box_' . $key ];
								unset( $object_var );
							}
						}
					}
					unset( $other_box_data, $key );

					$total_other_deductions = Misc::MoneyFormat( Misc::sumMultipleColumns( $total_row, array('other_box_0', 'other_box_1', 'other_box_2', 'other_box_3', 'other_box_4') ), FALSE );
					$t4as->l101 = $total_other_deductions;

					if ( isset( $setup_data['remittances_paid'] ) AND $setup_data['remittances_paid'] != '' ) {
						$t4as->l82 = (float)$setup_data['remittances_paid'];
					} else {
						$t4as->l82 = $total_row['income_tax'];
					}

					if ( $format == 'payment_services' ) {
						$report_meta_data['t4as'] = array('total_employees' => $t4a->countRecords(), 'l22' => $t4as->l22);
					}

					$this->getFormObject()->addForm( $t4as );
				}

				if ( $t4a->countRecords() > 0 ) {
					if ( $format == 'efile_xml' OR $format == 'payment_services' ) {
						$output_format = 'XML';
						$file_name = 't4a_efile_' . date( 'Y_m_d' ) . '_' . Misc::sanitizeFileName( $this->form_data['legal_entity'][ $legal_entity_id ]->getTradeName() ) . '.xml';
						$mime_type = 'applications/octet-stream'; //Force file to download.
					} else {
						$output_format = 'PDF';
						$file_name = 't4a_' . Misc::sanitizeFileName( $this->form_data['legal_entity'][ $legal_entity_id ]->getTradeName() ) . '.pdf';
						$mime_type = $this->file_mime_type;
					}

					$file_output = $this->getFormObject()->output( $output_format );

					//reset the file objects.
					$this->clearFormObject();
					$this->clearT4AObject();
					$this->clearT619SumObject();
					$this->clearT4ASumObject();

					if ( !is_array($file_output) ) {
						if ( $format == 'payment_services' ) {
							$tmp_output['xml'] = $file_output;
							$tmp_output['metadata'] = $report_meta_data;
							$file_output = $tmp_output;
						}

						$file_arr[] = array( 'file_name' => $file_name, 'mime_type' => $mime_type, 'data' => $file_output );
						unset($file_output);
					} else {
						return $file_output;
					}
				} else {
					return FALSE; //No records.
				}
			}
		}

		$zip_filename = explode('.', $file_name);
		if ( isset($zip_filename[(count($zip_filename) - 1)]) ) {
			$zip_filename = str_replace( '.', '', str_replace( $zip_filename[(count( $zip_filename ) - 1)], '', $file_name ) ) . '.zip';
		} else {
			$zip_filename = str_replace( '.', '', $file_name ) . '.zip';
		}

		return Misc::zip($file_arr, $zip_filename, TRUE);
	}

	/**
	 * Short circuit this function, as no postprocessing is required for exporting the data.
	 * @param null $format
	 * @return bool
	 */
	function _postProcess( $format = NULL ) {
		if ( ( $format == 'pdf_form' OR $format == 'pdf_form_government' ) OR ( $format == 'pdf_form_print' OR $format == 'pdf_form_print_government' ) OR $format == 'efile_xml' OR $format == 'payment_services' OR $format == 'pdf_form_publish_employee' ) {
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
		if ( ( $format == 'pdf_form' OR $format == 'pdf_form_government' ) OR ( $format == 'pdf_form_print' OR $format == 'pdf_form_print_government' ) OR $format == 'efile_xml' OR $format == 'payment_services' OR $format == 'pdf_form_publish_employee' ) {
			return $this->_outputPDFForm( $format );
		} else {
			return parent::_output( $format );
		}
	}

	/**
	 * Formats report data for exporting to TimeTrex payment service.
	 * @return array
	 */
	function getPaymentServicesData( $prae_obj, $pra_obj, $rs_obj, $pra_user_obj ) {
		$output_data = $this->getOutput( 'payment_services' );
		Debug::Arr( $output_data, 'Raw Report data!', __FILE__, __LINE__, __METHOD__, 10);

		if ( $this->hasData() AND isset( $output_data['data'] ) AND isset($output_data['data']['metadata']['t4as']['total_employees']) AND $output_data['data']['metadata']['t4as']['total_employees'] > 0 ) {
			$batch_id = date( 'M d', $prae_obj->getEndDate() );

			$retarr = array(
					'object'               => __CLASS__,
					'user_success_message' => TTi18n::gettext( 'T4As submitted successfully' ),

					'agency_report_data'   => array(
							'type_id'         => 'R', //R=Report
							'total_employees' => (int)$output_data['data']['metadata']['t4as']['total_employees'],
							'subject_wages'   => NULL,
							'taxable_wages'   => NULL,
							'amount_withheld' => $output_data['data']['metadata']['t4as']['l22'],
							'amount_due'      => 0,
							'due_date'        => $prae_obj->getDueDate(),
							'extra_data'      => $output_data['data']['metadata'],
							'xml_data'		  => $output_data['data']['xml'],

							'remote_batch_id'  => $batch_id,

							//Generate a consistent remote_id based on the exact time period, the remittance agency event, and batch ID.
							//This helps to prevent duplicate records from be created, as well as work across separate or split up batches that may be processed.
							'remote_id' => TTUUID::convertStringToUUID( md5( $prae_obj->getId() . $batch_id ) ),
					),
			);

			return $retarr;
		}

		Debug::Text('No report data!', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}
}
?>
