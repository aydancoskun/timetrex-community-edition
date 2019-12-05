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
class ROEReport extends Report {

	protected $user_ids = array();

	/**
	 * ROEReport constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText('ROE Report');
		$this->file_name = 'roe';

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
				//AND $this->getPermissionObject()->Check('report', 'view_roe', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('roe', 'view', $user_id, $company_id )
			) {
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

										'-2080-code_id' => TTi18n::gettext('Reason'),
										'-2090-pay_period_type_id' => TTi18n::gettext('Pay Period Type'),

										'-3000-custom_filter' => TTi18n::gettext('Custom Filter'),

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
//				$retval = array_merge(
//										TTDate::getReportDateOptions( 'first', TTi18n::gettext('First Day Worked(Or first day since last ROE)'), 16, FALSE ),
//										TTDate::getReportDateOptions( 'last', TTi18n::gettext('Last Day For Which Paid'), 16, FALSE ),
//										TTDate::getReportDateOptions( 'pay_period_end', TTi18n::gettext('Final Pay Period Ending Date'), 17, FALSE ),
//										TTDate::getReportDateOptions( 'recall', TTi18n::gettext('Expected Date of Recall'), 17, FALSE )
//				);
				$retval = array();
				break;
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), NULL, 'ROEReport', 'custom_column' );
					if ( is_array($custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('filter_column_type_ids'), NULL, 'ROEReport', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('dynamic_format_ids'), 'ROEReport', 'custom_column' );
					if ( is_array($report_dynamic_custom_column_labels) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('static_format_ids'), 'ROEReport', 'custom_column' );
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

										'-1120-code' => TTi18n::gettext('Reason'),
										'-1130-pay_period_type' => TTi18n::gettext('Pay Period Type'),
										//'-1140-first_date' => TTi18n::gettext('First Day Worked(Or first day since last ROE)'),
										//'-1150-last_date' => TTi18n::gettext('Last Day For Which Paid'),
										//'-1160-pay_period_end_date' => TTi18n::gettext('Final Pay Period Ending Date'),
										//'-1170-recall_date' => TTi18n::gettext('Expected Date of Recall'),
										'-1180-serial' => TTi18n::gettext('Serial No'),
										'-1190-comments' => TTi18n::gettext('Comments'),

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
										'-2100-insurable_earnings' => TTi18n::gettext('Insurable Earnings (Box 15B)'),
										'-2110-vacation_pay' => TTi18n::gettext('Vacation Pay (Box 17A)'),

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
						$retval[$column] = 'numeric';
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
	function getROEObject() {
		if ( !isset($this->form_obj['roe']) OR !is_object($this->form_obj['roe']) ) {
			$this->form_obj['roe'] = $this->getFormObject()->getFormObject( 'ROE', 'CA' );
			return $this->form_obj['roe'];
		}

		return $this->form_obj['roe'];
	}

	/**
	 * @return array
	 */
	function formatFormConfig() {
		$retarr = (array)$this->getFormConfig();
		return $retarr;
	}

	/**
	 * Get raw data for report
	 * @param null $format
	 * @return bool
	 */
	function _getData( $format = NULL ) {
		$this->tmp_data = array( 'user' => array(), 'roe' => array() );

		$filter_data = $this->getFilterConfig();

		//Debug::Arr($this->form_data, 'Form Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->tmp_data, 'Tmp Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Get ROE data for joining
		$rlf = TTnew( 'ROEListFactory' ); /** @var ROEListFactory $rlf */
		$rlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' ROE Total Rows: '. $rlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $rlf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach( $rlf as $key => $r_obj ) {
			$this->tmp_data['roe'][$r_obj->getUser()] = (array)$r_obj->getObjectAsArray(); //Don't pass $this->getColumnDataConfig() here as no columns are sent from Flex so it breaks the report.
			if ( $r_obj->isPayPeriodWithNoEarnings() == TRUE ) {
				$this->tmp_data['roe'][$r_obj->getUser()]['pay_period_earnings'] = $r_obj->combinePostTerminationPayPeriods( $r_obj->getInsurableEarningsByPayPeriod( '15c' ) );
			}
			//Box 17A, Vacation pay in last pay period
			$vacation_pay = $r_obj->getLastPayPeriodVacationEarnings();
			if ( $vacation_pay > 0 ) {
				$this->tmp_data['roe'][$r_obj->getUser()]['vacation_pay'] = $vacation_pay;
			}

			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['roe'], 'ROE Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Filter the below user list based on the users that actually have ROEs above.
		$filter_data['id'] = array_keys($this->tmp_data['roe']);

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text(' User Total Rows: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $ulf->getRecordCount(), NULL, TTi18n::getText('Retrieving Data...') );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray(); //Don't pass $this->getColumnDataConfig() here as no columns are sent from Flex so it breaks the report.
			$this->tmp_data['user'][$u_obj->getId()]['user_id'] = $u_obj->getId();
			$this->tmp_data['user'][$u_obj->getId()]['legal_entity_id'] = $u_obj->getLegalEntity();
			$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);


		unset($filter_data['id'], $filter_data['roe_id']); //Remove this filter so we don't cause problems with below queries.

		//Get legal entity data for joining.
		$lelf = TTnew( 'LegalEntityListFactory' ); /** @var LegalEntityListFactory $lelf */
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

		$this->tmp_data['remittance_agency'] = array();

		$filter_data['agency_id'] = array('10:CA:00:00:0010',  '10:CA:00:00:0020'); //CA Service Canada (ROE)
		$ralf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $ralf */
		$ralf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		//Debug::Text( ' Remittance Agency Total Rows: ' . $ralf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), $lelf->getRecordCount(), NULL, TTi18n::getText( 'Retrieving Remittance Agency Data...' ) );
		if ( $ralf->getRecordCount() > 0 ) {
			foreach ( $ralf as $key => $ra_obj ) {
				$this->form_data['remittance_agency'][$ra_obj->getLegalEntity()][$ra_obj->parseAgencyID( NULL, 'id')] = $ra_obj;
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
		$this->getProgressBarObject()->start( $this->getAMFMessageID(), count($this->tmp_data['roe']), NULL, TTi18n::getText('Pre-Processing Data...') );

		//Merge time data with user data
		$key = 0;
		if ( isset($this->tmp_data['roe']) ) {
			foreach( $this->tmp_data['roe'] as $user_id => $row ) {

				$process_data = array();
				if ( isset($row['first_date']) ) {
					$first_date_columns = TTDate::getReportDates( 'first', TTDate::parseDateTime( $row['first_date'] ), FALSE, $this->getUserObject() );
				} else {
					$first_date_columns = array();
				}

				if ( isset($row['last_date']) ) {
					$last_date_columns = TTDate::getReportDates( 'last', TTDate::parseDateTime( $row['last_date'] ), FALSE, $this->getUserObject() );
				} else {
					$last_date_columns = array();
				}

				if ( isset($row['pay_period_end_date']) ) {
					$pay_period_end_date_columns = TTDate::getReportDates( 'pay_period_end', TTDate::parseDateTime( $row['pay_period_end_date'] ), FALSE, $this->getUserObject() );
				} else {
					$pay_period_end_date_columns = array();
				}

				if ( isset($row['recall_date']) ) {
					$recall_date_columns = TTDate::getReportDates( 'recall', TTDate::parseDateTime( $row['recall_date'] ), FALSE, $this->getUserObject() );
				} else {
					$recall_date_columns = array();
				}

				if ( isset($this->tmp_data['user'][$user_id]) ) {
					$tmp_legal_array = array();
					if ( isset($this->tmp_data['legal_entity'][$this->tmp_data['user'][$user_id]['legal_entity_id']]) ) {
						$tmp_legal_array = $this->tmp_data['legal_entity'][$this->tmp_data['user'][$user_id]['legal_entity_id']];
					}

					$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $first_date_columns, $last_date_columns, $pay_period_end_date_columns, $recall_date_columns, $tmp_legal_array );

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
					$key++;
				}
			}
			unset($this->tmp_data, $row, $first_date_columns, $last_date_columns, $pay_period_end_date_columns, $recall_date_columns, $process_data, $tmp_legal_array);

			//Calculate Form Data
			if ( is_array($this->data) ) {
				foreach( $this->data as $row ) {
					if ( !isset($this->form_data['user'][$row['legal_entity_id']][$row['user_id']]) ) {
						$this->form_data['roe'][$row['legal_entity_id']][$row['user_id']] = $row;
					}

				}
			}
		}
		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	/**
	 * @param null $format
	 * @return array|bool
	 */
	function _outputPDFForm( $format = NULL ) {
		// Always display the background.
		$show_background = TRUE;
		Debug::Text('Generating Form... Format: '. $format, __FILE__, __LINE__, __METHOD__, 10);

		//Debug::Arr($setup_data, 'Setup Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//$last_row = count($this->form_data)-1;
		//$total_row = $last_row+1;

		$file_arr = array();

		$current_company = $this->getUserObject()->getCompanyObject();
		if ( !is_object($current_company) ) {
			Debug::Text('Invalid company object...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$roef = TTnew('ROEFactory'); /** @var ROEFactory $roef */

		$i = 0;
		if ( isset( $this->form_data['roe'] ) AND is_array( $this->form_data['roe'] ) ) {
			$file_name = 'roe.ext';
			foreach ( $this->form_data['roe'] as $legal_entity_id => $user_rows ) {
				if ( isset( $this->form_data['legal_entity'][ $legal_entity_id ] ) == FALSE ) {
					Debug::Text( 'Missing Legal Entity: ' . $legal_entity_id, __FILE__, __LINE__, __METHOD__, 10 );
					continue;
				}

				if ( isset( $this->form_data['remittance_agency'][ $legal_entity_id ][10] ) == FALSE ) {
					Debug::Text( 'Missing Remittance Agency: ' . $legal_entity_id, __FILE__, __LINE__, __METHOD__, 10 );
					continue;
				}

				$legal_entity_obj = $this->form_data['legal_entity'][ $legal_entity_id ];

				$cra_pra_obj = $this->form_data['remittance_agency'][ $legal_entity_id ][10];
				$pra_obj = $this->form_data['remittance_agency'][ $legal_entity_id ][20];

				$roe = $this->getROEObject();
				$roe->setShowBackground( $show_background );
				//$roe->setDebug( TRUE );
				//$roe->setType( $form_type );
				$roe->business_number = $cra_pra_obj->getPrimaryIdentification();
				$roe->company_name = $legal_entity_obj->getLegalName();
				$roe->company_address1 = $legal_entity_obj->getAddress1();
				$roe->company_address2 = $legal_entity_obj->getAddress2();
				$roe->company_city = $legal_entity_obj->getCity();
				$roe->company_province = $legal_entity_obj->getProvince();
				$roe->company_postal_code = $legal_entity_obj->getPostalCode();
				$roe->company_work_phone = $legal_entity_obj->getWorkPhone();
				$roe->english = TRUE;

				$batch_id = '';
				$report_data = array();
				foreach ( $user_rows as $row ) {
					$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
					$ulf->getById( TTUUID::castUUID( $row['user_id'] ) );
					if ( $ulf->getRecordCount() == 1 ) {
						$user_obj = $ulf->getCurrent();

						$title_obj = $user_obj->getTitleObject();

						$roef->setPayPeriodType( $row['pay_period_type_id'] );

						$ee_data = array(
								'first_name'           => $user_obj->getFirstName(),
								'middle_name'          => $user_obj->getMiddleName(),
								'last_name'            => $user_obj->getLastName(),
								'employee_full_name'   => $user_obj->getFullName( FALSE ),
								'employee_address1'    => $user_obj->getAddress1(),
								'employee_address2'    => $user_obj->getAddress2(),
								'employee_city'        => $user_obj->getCity(),
								'employee_province'    => $user_obj->getProvince(),
								'employee_postal_code' => $user_obj->getPostalCode(),
								'title'                => ( is_object( $title_obj ) ) ? $title_obj->getName() : NULL,
								'sin'                  => $user_obj->getSIN(),

								'pay_period_type'                => $row['pay_period_type'],
								'pay_period_type_id'             => $row['pay_period_type_id'],
								'insurable_earnings_pay_periods' => $roef->getInsurableEarningsReportPayPeriods( '15b' ),

								'code_id'             => $row['code_id'],
								'first_date'          => TTDate::parseDateTime( $row['first_date'] ),
								'last_date'           => TTDate::parseDateTime( $row['last_date'] ),
								'pay_period_end_date' => TTDate::parseDateTime( $row['pay_period_end_date'] ),
								'recall_date'         => TTDate::parseDateTime( $row['recall_date'] ),
								'insurable_hours'     => $row['insurable_hours'],
								'insurable_earnings'  => $row['insurable_earnings'],
								'vacation_pay'        => $row['vacation_pay'],
								'serial'              => $row['serial'],
								'comments'            => $row['comments'],
								'created_date'        => TTDate::parseDateTime( $row['created_date'] ),
						);
					}

					if ( is_object( $pra_obj ) AND is_object( $pra_obj->getContactUserObject() ) ) {
						$contact_user_obj = $pra_obj->getContactUserObject();
					} else {
						$ulf->getById( TTUUID::castUUID( $row['created_by_id'] ) );
						if ( $ulf->getRecordCount() == 1 ) {
							$contact_user_obj = $ulf->getCurrent();
						}
					}

					if ( is_object( $contact_user_obj ) ) {
						$ee_data['created_user_first_name'] = $contact_user_obj->getFirstName();
						$ee_data['created_user_middle_name'] = $contact_user_obj->getMiddleName();
						$ee_data['created_user_last_name'] = $contact_user_obj->getLastName();
						$ee_data['created_user_full_name'] = $contact_user_obj->getFullName( FALSE );
						$ee_data['created_user_work_phone'] = $contact_user_obj->getWorkPhone();
					}

					if ( isset( $row['pay_period_earnings'] ) AND is_array( $row['pay_period_earnings'] ) ) {
						foreach ( $row['pay_period_earnings'] as $pay_period_earning ) {
							$ee_data['pay_period_earnings'][] = Misc::MoneyFormat( $pay_period_earning['amount'], FALSE );
						}
					}

					//remote_id=Should be the ROE record ID, rather than the user_id so we can better differentiate between multiple ROEs for the same employee.
					$report_data[] = array( 'remote_id' => $row['id'], 'first_name' => $user_obj->getFirstName(), 'last_name' => $user_obj->getLastName(), 'sin' => $user_obj->getSIN(), 'first_date' => TTDate::parseDateTime( $row['first_date'] ), 'last_date' => TTDate::parseDateTime( $row['last_date'] ), 'pay_period_end_date' => TTDate::parseDateTime( $row['pay_period_end_date'] ) );
					$batch_id .= substr( $user_obj->getLastName(), 0, 9 ) . date('Ym', TTDate::parseDateTime( $row['pay_period_end_date'] ) );

					$roe->addRecord( $ee_data );

					if ( $format == 'pdf_form_publish_employee' ) {
						// generate PDF for every employee and assign to each government document records
						$this->getFormObject()->addForm( $roe );
						GovernmentDocumentFactory::addDocument( $user_obj->getId(), 20, 190, TTDate::parseDateTime( $row['pay_period_end_date'] ), $this->getFormObject()->output( 'PDF' ) );
						$this->getFormObject()->clearForms();
					}


					unset( $ee_data );

					$i++;
				}
				unset( $user_rows, $rows, $ulf, $user_obj, $title_obj, $contact_user_obj );

				$this->getFormObject()->addForm( $roe );

				if ( $format == 'pdf_form_publish_employee' ) {
					$user_generic_status_batch_id = GovernmentDocumentFactory::saveUserGenericStatus( $this->getUserObject()->getId() );
					return $user_generic_status_batch_id;
				}

				$full_service_efile = FALSE;
				if ( $format == 'efile_xml' ) {
					$praelf = TTNew( 'PayrollRemittanceAgencyEventListFactory' ); /** @var PayrollRemittanceAgencyEventListFactory $praelf */
					$praelf->getAPISearchByCompanyIdAndArrayCriteria( $legal_entity_obj->getCompany(), array('payroll_remittance_agency_id' => $pra_obj->getId(), 'type_id' => 'ROE', 'status_id' => 15 ) ); //15=Full Service
					if ( $praelf->getRecordCount() > 0 ) {
						$prae_obj = $praelf->getCurrent();
						$full_service_efile = TRUE;
						Debug::Text(' Full Service eFile: Yes', __FILE__, __LINE__, __METHOD__, 10);
					}
				}

				if ( PRODUCTION == TRUE AND $full_service_efile == TRUE AND is_object( $legal_entity_obj ) AND $legal_entity_obj->getPaymentServicesStatus() == 10 AND $legal_entity_obj->getPaymentServicesUserName() != '' AND $legal_entity_obj->getPaymentServicesAPIKey() != '' ) { //10=Enabled
					if ( $roe->countRecords() > 0 ) {
						try {
							$tt_ps_api = $legal_entity_obj->getPaymentServicesAPIObject();

							if ( strlen( $batch_id ) > 15 ) {
								$batch_id = TTUUID::truncateUUID( TTUUID::convertStringToUUID( md5( $batch_id ) ), 15 );
							}

							$remote_id = TTUUID::convertStringToUUID( md5( $prae_obj->getId() . $batch_id . $roe->countRecords() ) );

							$agency_report_arr = $tt_ps_api->convertROEToAgencyReportArray( $this->getFormObject(), $report_data, $prae_obj, $pra_obj, $remote_id, $batch_id );
							$retval = $tt_ps_api->setAgencyReport( $agency_report_arr ); //P=Payment
							Debug::Arr( $retval, 'TimeTrexPaymentServices Retval: ', __FILE__, __LINE__, __METHOD__, 10 );
							if ( $retval->isValid() == TRUE ) {
								$retval = array(
										'api_retval'  => FALSE, //Needs to be FALSE to show a popup to the user, even though its a success message.
										'api_details' => array(
												'code'        => 'SUCCESS',
												'description' => TTi18n::gettext( 'ROE(s) submitted successfully!' ),
										),
								);
							} else {
								$retval = array(
										'api_retval'  => FALSE,
										'api_details' => array(
												'code'        => 'ERROR',
												'description' => TTi18n::gettext( 'ERROR! Unable to submit ROE(s)!' ),
										),
								);
							}
						} catch ( Exception $e ) {
							Debug::Text( 'ERROR! Unable to upload agency report data... (b) Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
							$retval = array(
									'api_retval'  => FALSE,
									'api_details' => array(
											'code'        => 'ERROR',
											'description' => TTi18n::gettext('Payment Services ERROR: %1', array( $e->getMessage() ) ),
									),
							);
						}
					} else {
						Debug::Text(' ERROR! No ROE records to eFile!', __FILE__, __LINE__, __METHOD__, 10);
						$retval = array(
								'api_retval'  => FALSE,
								'api_details' => array(
										'code'        => 'ERROR',
										'description' => TTi18n::gettext( 'ERROR! No ROE(s) to submit!' ),
								),
						);
					}

					Debug::Arr( $retval, ' Full Service eFile Retval: ', __FILE__, __LINE__, __METHOD__, 10);
					return $retval;
				} else {
					if ( $format == 'efile_xml' ) {
						$output_format = 'XML';
						$file_name = 'roe_efile_' . date( 'Y_m_d' ) . '_' . Misc::sanitizeFileName( $this->form_data['legal_entity'][ $legal_entity_id ]->getTradeName() ) . '.xml';
						$mime_type = 'applications/octet-stream'; //Force file to download.
					} else {
						$output_format = 'PDF';
						$file_name = 'roe_' . Misc::sanitizeFileName( $this->form_data['legal_entity'][ $legal_entity_id ]->getTradeName() ) . '.pdf';
						$mime_type = $this->file_mime_type;
					}

					$file_output = $this->getFormObject()->output( $output_format );
					if ( !is_array( $file_output ) ) {
						$file_arr[] = array('file_name' => $file_name, 'mime_type' => $mime_type, 'data' => $file_output);
					} else {
						return $file_output;
					}
				}

				//Reset the file objects.
				$this->clearFormObject();
				unset( $file_output );
			}
		}

		$zip_filename = explode( '.', $file_name );
		if ( isset( $zip_filename[ ( count( $zip_filename ) - 1 ) ] ) ) {
			$zip_filename = str_replace( '.', '', str_replace( $zip_filename[ ( count( $zip_filename ) - 1 ) ], '', $file_name ) ) . '.zip';
		} else {
			$zip_filename = str_replace( '.', '', $file_name ) . '.zip';
		}

		return Misc::zip( $file_arr, $zip_filename, TRUE );
	}

	/**
	 * Short circuit this function, as no postprocessing is required for exporting the data.
	 * @param null $format
	 * @return bool
	 */
	function _postProcess( $format = NULL ) {
		if ( ( $format == 'pdf_form' OR $format == 'pdf_form_government' ) OR ( $format == 'pdf_form_print' OR $format == 'pdf_form_print_government' ) OR $format == 'efile_xml'  OR $format == 'pdf_form_publish_employee' ) {
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
		if ( ( $format == 'pdf_form' OR $format == 'pdf_form_government' ) OR ( $format == 'pdf_form_print' OR $format == 'pdf_form_print_government' ) OR $format == 'efile_xml' OR $format == 'pdf_form_publish_employee' ) {
			return $this->_outputPDFForm( $format );
		} else {
			return parent::_output( $format );
		}
	}
}
?>
