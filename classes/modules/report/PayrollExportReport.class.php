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
class PayrollExportReport extends TimesheetSummaryReport {

	/**
	 * PayrollExportReport constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText('Payroll Export Report');
		$this->file_name = 'payroll_export';

		//Don't call TimesheetSummaryReport __construct(), skip one level lower to the Report class instead.
		Report::__construct();

		return TRUE;
	}

	/**
	 * @param string $user_id UUID
	 * @param string $company_id UUID
	 * @return bool
	 */
	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check('report', 'enabled', $user_id, $company_id )
				AND $this->getPermissionObject()->Check('report', 'view_payroll_export', $user_id, $company_id ) ) {
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
	 * @return array|bool|mixed|null
	 */
	protected function _getOptions( $name, $params = NULL ) {
		$retval = NULL;
		switch( $name ) {
			case 'export_columns': //Must pass export_type.
				if ( $params == 'csv_advanced' OR $params = 'va_munis' OR $params = 'meditech' ) {
					if ( is_object( $this->getUserObject() ) AND is_object( $this->getUserObject()->getCompanyObject() ) AND $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
						$jar = TTNew('JobDetailReport');
					} else {
						$jar = TTNew('TimesheetDetailReport');
					}
					$jar->setUserObject( $this->getUserObject() );

					$retval = $jar->getOptions('static_columns');
				} else {
					$retval = parent::getOptions('static_columns');
				}
				break;
			case 'output_format':
				$retval = array_merge( parent::getOptions('default_output_format'), array('-0999-payroll_export' => TTi18n::gettext('Export') ) );
				break;
			case 'export_type':
				$retval = array(
								0 => TTi18n::gettext('-- Please Choose --'),
								'-0100-adp'				=> TTi18n::gettext('ADP'),
								'-0110-adp_advanced'		=> TTi18n::gettext('ADP (Advanced)'),
								'-0120-adp_resource'		=> TTi18n::gettext('ADP Resource/Pay eXpert'),
								'-0200-paychex_preview'	=> TTi18n::gettext('Paychex Preview'),
								'-0210-paychex_preview_advanced_job' => TTi18n::gettext('Paychex Preview (by Day/Job)'),
								'-0220-paychex_online'	=> TTi18n::gettext('Paychex Online Payroll'),
								'-0300-ceridian_insync'	=> TTi18n::gettext('Ceridian Insync'),
								'-0400-millenium'			=> TTi18n::gettext('Millennium'),
								'-0500-quickbooks'		=> TTi18n::gettext('QuickBooks Pro'),
								//'-0510-quickbooks_advanced' => TTi18n::gettext('QuickBooks Pro (Advanced)'), //Break time out by day?
								'-0600-surepayroll'		=> TTi18n::gettext('SurePayroll'),
								'-0700-chris21'			=> TTi18n::gettext('Chris21'),
								'-0800-va_munis'			=> TTi18n::gettext('MUNIS (VA)'),
								'-0900-accero'			=> TTi18n::gettext('Accero'),
								'-1000-compupay'			=> TTi18n::gettext('CompuPay'),
								'-1100-sage_50'			=> TTi18n::gettext('Sage 50'),
								'-1110-meditech'			=> TTi18n::gettext('Meditech'),
								'-9900-csv'				=> TTi18n::gettext('Generic Excel/CSV'),
								'-9900-csv_advanced'		=> TTi18n::gettext('Generic Excel/CSV (Advanced)'),
								//'other'			=> TTi18n::gettext('-- Other --'),
								);

				if ( $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$retval['-1200-cms_pbj'] = TTi18n::gettext('CMS Payroll Based Journal (PBJ)');
				}
				ksort($retval);
				$retval = Misc::trimSortPrefix($retval);
				break;
			case 'export_policy':
				$retval = array();
				$pclf = TTnew( 'PayCodeListFactory' );
				$pclf->getByCompanyId( $this->getUserObject()->getCompany() );
				if ( $pclf->getRecordCount() > 0 ) {
					foreach( $pclf as $pc_obj ) {
						//Collect PAID pay codes so we can create PAID TIME columns.
						$retval['-3190-pay_code:'.$pc_obj->getId()] = $pc_obj->getName();
					}
				}
				break;
			case 'default_hour_codes':
				$export_type = $this->getOptions('export_type');

				$retval = array();
				$pclf = TTnew( 'PayCodeListFactory' );
				$pclf->getByCompanyId( $this->getUserObject()->getCompany() );
				if ( $pclf->getRecordCount() > 0 ) {
					foreach( $pclf as $pc_obj ) {
						foreach( $export_type as $type => $name ) {
							if ( $type === 0 ) {
								continue;
							}

							$retval[$type]['columns']['pay_code:'.$pc_obj->getId()]['hour_code'] = $pc_obj->getCode();
						}
					}
				}
				break;
			case 'hour_column_name':
				$hour_column_name_map = array(
								'adp'				=> TTi18n::gettext('ADP Hours Code'),
								'adp_advanced'		=> TTi18n::gettext('ADP Hours Code'),
								'adp_resource'		=> TTi18n::gettext('ADP Hours Code'),
								'paychex_preview'	=> TTi18n::gettext('Paychex Hours Code'),
								'paychex_preview_advanced_job' => TTi18n::gettext('Paychex Hours Code'),
								'paychex_online'	=> TTi18n::gettext('Paychex Hours Code'),
								'ceridian_insync'	=> TTi18n::gettext('Ceridian Hours Code'),
								'millenium'			=> TTi18n::gettext('Millennium Hours Code'),
								'quickbooks'		=> TTi18n::gettext('Quickbooks Payroll Item Name'),
								'quickbooks_advanced' => TTi18n::gettext('Quickbooks Payroll Item Name'),
								'surepayroll'		=> TTi18n::gettext('Payroll Code'),
								'va_munis'			=> TTi18n::gettext('Hours Code'),
								'accero'			=> TTi18n::gettext('Hours Code'),
								'compupay'			=> TTi18n::gettext('Hours Code'),
								'sage_50'			=> TTi18n::gettext('Item Number'),
								'meditech'			=> TTi18n::gettext('Earning Number'),
								'cms_pbj'			=> TTi18n::gettext('Export'),
								'csv'				=> TTi18n::gettext('Hours Code'),
								'csv_advanced'		=> TTi18n::gettext('Hours Code'),
								);

				if (  isset($params['export_type']) AND isset($hour_column_name_map[$params['export_type']]) ) {
					$retval = $hour_column_name_map[$params['export_type']];
				} else {
					$retval = $hour_column_name_map['csv'];
				}
				break;
			case 'adp_hour_column_options':
			case 'adp_advanced_hour_column_options':
			case 'adp_resource_hour_column_options':
				$retval[$name][0] = TTi18n::gettext('-- DO NOT EXPORT --');
				$retval[$name]['-0010-regular_time'] = TTi18n::gettext('Regular Time');
				$retval[$name]['-0020-overtime'] = TTi18n::gettext('Overtime');
				if ( $name == 'adp_resource_hour_column_options' ) {
					$retval[$name]['-0040-other'] = TTi18n::gettext('Other Hours');
				} else {
					for ( $i = 3; $i <= 4; $i++ ) {
						$retval[$name]['-003'.$i.'-'.$i] = TTi18n::gettext('Hours') .' '. $i;
					}
				}
				break;
			case 'adp_company_code_options':
			case 'adp_batch_options':
			case 'adp_temp_dept_options':
			case 'adp_job_cost_options':
			case 'adp_work_class_options':
				$retval = array(
								0 => TTi18n::gettext('-- Custom --'),
								'-0010-default_branch_manual_id' => TTi18n::gettext('Default Branch: Code'),
								'-0020-default_department_manual_id' => TTi18n::gettext('Default Department: Code'),
								'-0030-branch_manual_id' => TTi18n::gettext('Branch: Code'),
								'-0040-department_manual_id' => TTi18n::gettext('Department: Code'),
								);

				if ( ( $name == 'adp_job_cost_options' OR $name == 'adp_work_class_options' ) AND $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$retval['-0200-job_name'] = TTi18n::gettext('Job: Name');
					$retval['-0210-job_manual_id'] = TTi18n::gettext('Job: Code');
					$retval['-0300-job_item_name'] = TTi18n::gettext('Task: Name');
					$retval['-0310-job_item_manual_id'] = TTi18n::gettext('Task: Code');
				}

				$oflf = TTnew( 'OtherFieldListFactory' );

				//Put a colon or underscore in the name, thats how we know it needs to be replaced.

				//Get Branch other fields.
				$default_branch_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 4, '-1000-default_branch_', TTi18n::getText('Default Branch').': ' );
				if (  !is_array($default_branch_options) ) {
					$default_branch_options = array();
				}
				$default_department_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 5, '-2000-default_department_', TTi18n::getText('Default Department').': ' );
				if (  !is_array($default_department_options) ) {
					$default_department_options = array();
				}

				$branch_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 4, '-3000-branch_', TTi18n::getText('Branch').': ' );
				if ( !is_array($branch_options) ) {
					$branch_options = array();
				}
				$department_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 5, '-4000-department_', TTi18n::getText('Department').': ' );
				if ( !is_array($department_options) ) {
					$department_options = array();
				}

				$retval = array_merge( $retval, (array)$default_branch_options, (array)$default_department_options, $branch_options, $department_options );
				break;
			case 'accero_hour_column_options':
				$retval['accero_hour_column_options'][0] = TTi18n::gettext('-- DO NOT EXPORT --');
				$retval['accero_hour_column_options']['-0010-regular_time'] = TTi18n::gettext('Regular Time');
				$retval['accero_hour_column_options']['-0020-overtime'] = TTi18n::gettext('Overtime');
				break;
			case 'accero_temp_dept_options':
				$retval = array(
								0 => TTi18n::gettext('-- Custom --'),
								'-0010-default_branch_manual_id' => TTi18n::gettext('Default Branch: Code'),
								'-0020-default_department_manual_id' => TTi18n::gettext('Default Department: Code'),
								'-0030-branch_manual_id' => TTi18n::gettext('Branch: Code'),
								'-0040-department_manual_id' => TTi18n::gettext('Department: Code'),
								);

				$oflf = TTnew( 'OtherFieldListFactory' );

				//Put a colon or underscore in the name, thats how we know it needs to be replaced.

				//Get Branch other fields.
				$default_branch_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 4, '-1000-default_branch_', TTi18n::getText('Default Branch').': ' );
				if (  !is_array($default_branch_options) ) {
					$default_branch_options = array();
				}
				$default_department_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 5, '-2000-default_department_', TTi18n::getText('Default Department').': ' );
				if (  !is_array($default_department_options) ) {
					$default_department_options = array();
				}

				$branch_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 4, '-3000-branch_', TTi18n::getText('Branch').': ' );
				if ( !is_array($branch_options) ) {
					$branch_options = array();
				}
				$department_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 5, '-4000-department_', TTi18n::getText('Department').': ' );
				if ( !is_array($department_options) ) {
					$department_options = array();
				}

				$retval = array_merge( $retval, (array)$default_branch_options, (array)$default_department_options, $branch_options, $department_options );
				break;
			case 'quickbooks_proj_options':
			case 'quickbooks_job_options':
			case 'quickbooks_item_options':
				$retval = array(
								0 => TTi18n::gettext('-- NONE --'),
								'default_branch' => TTi18n::gettext('Default Branch'),
								'default_department' => TTi18n::gettext('Default Department'),
								'group' => TTi18n::gettext('Group'),
								'title' => TTi18n::gettext('Title'),
								'branch_name' => TTi18n::gettext('Punch Branch'),
								'department_name' => TTi18n::gettext('Punch Department'),
								);
				break;
			case 'sage_50_customer_name_options':
				$retval = array(
								0 => TTi18n::gettext('-- Custom --'),
								'default_branch' => TTi18n::gettext('Default Branch'),
								'default_department' => TTi18n::gettext('Default Department'),
								'group' => TTi18n::gettext('Group'),
								'title' => TTi18n::gettext('Title'),
								'branch_name' => TTi18n::gettext('Punch Branch'),
								'department_name' => TTi18n::gettext('Punch Department'),
								);
				break;
			case 'cms_pbj_hour_column_options':
				$retval[$name] = array(
								0 => TTi18n::gettext('No'),
								1 => TTi18n::gettext('Yes'),
								);
				break;
			case 'cms_pbj_facility_code_options':
				$retval = array(
								0 => TTi18n::gettext('-- Custom --'),
								'-0010-default_branch_manual_id' => TTi18n::gettext('Default Branch: Code'),
								'-0020-default_department_manual_id' => TTi18n::gettext('Default Department: Code'),
								'-0030-branch_manual_id' => TTi18n::gettext('Branch: Code'),
								'-0040-department_manual_id' => TTi18n::gettext('Department: Code'),
								);

				if ( $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$retval['-0200-job_name'] = TTi18n::gettext('Job: Name');
					$retval['-0210-job_manual_id'] = TTi18n::gettext('Job: Code');
					$retval['-0300-job_item_name'] = TTi18n::gettext('Task: Name');
					$retval['-0310-job_item_manual_id'] = TTi18n::gettext('Task: Code');
				}

				$oflf = TTnew( 'OtherFieldListFactory' );

				//Put a colon or underscore in the name, thats how we know it needs to be replaced.

				//Get Branch other fields.
				$default_branch_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 4, '-1000-default_branch_', TTi18n::getText('Default Branch').': ' );
				if (  !is_array($default_branch_options) ) {
					$default_branch_options = array();
				}
				$default_department_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 5, '-2000-default_department_', TTi18n::getText('Default Department').': ' );
				if (  !is_array($default_department_options) ) {
					$default_department_options = array();
				}

				$branch_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 4, '-3000-branch_', TTi18n::getText('Branch').': ' );
				if ( !is_array($branch_options) ) {
					$branch_options = array();
				}
				$department_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 5, '-4000-department_', TTi18n::getText('Department').': ' );
				if ( !is_array($department_options) ) {
					$department_options = array();
				}

				$retval = array_merge( $retval, (array)$default_branch_options, (array)$default_department_options, (array)$branch_options, (array)$department_options );
				break;
			case 'cms_pbj_pay_type_code_options':
			case 'cms_pbj_job_title_code_options':
				$retval = array(
								0 => TTi18n::gettext('-- Custom --'),
								'-0010-default_branch_manual_id' => TTi18n::gettext('Default Branch: Code'),
								'-0020-default_department_manual_id' => TTi18n::gettext('Default Department: Code'),
								'-0030-branch_manual_id' => TTi18n::gettext('Branch: Code'),
								'-0040-department_manual_id' => TTi18n::gettext('Department: Code'),
								);

				if ( $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$retval['-0200-job_name'] = TTi18n::gettext('Job: Name');
					$retval['-0210-job_manual_id'] = TTi18n::gettext('Job: Code');
					$retval['-0300-job_item_name'] = TTi18n::gettext('Task: Name');
					$retval['-0310-job_item_manual_id'] = TTi18n::gettext('Task: Code');
				}

				$oflf = TTnew( 'OtherFieldListFactory' );

				//Put a colon or underscore in the name, thats how we know it needs to be replaced.

				//Get Branch other fields.
				$default_branch_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 4, '-1000-default_branch_', TTi18n::getText('Default Branch').': ' );
				if (  !is_array($default_branch_options) ) {
					$default_branch_options = array();
				}
				$default_department_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 5, '-2000-default_department_', TTi18n::getText('Default Department').': ' );
				if (  !is_array($default_department_options) ) {
					$default_department_options = array();
				}

				$branch_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 4, '-3000-branch_', TTi18n::getText('Branch').': ' );
				if ( !is_array($branch_options) ) {
					$branch_options = array();
				}
				$department_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 5, '-4000-department_', TTi18n::getText('Department').': ' );
				if ( !is_array($department_options) ) {
					$department_options = array();
				}

				$job_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 20, '-5000-job_', TTi18n::getText('Job').': ' );
				if ( !is_array($job_options) ) {
					$job_options = array();
				}
				$job_item_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 30, '-6000-job_item_', TTi18n::getText('Task').': ' );
				if ( !is_array($job_item_options) ) {
					$job_item_options = array();
				}

				$title_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 12, '-7000-title_', TTi18n::getText('Title').': ' );
				if ( !is_array($title_options) ) {
					$title_options = array();
				}

				$user_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 10, '-8000-', TTi18n::getText('Employee').': ' );
				if ( !is_array($user_options) ) {
					$user_options = array();
				}

				$retval = array_merge( $retval, (array)$default_branch_options, (array)$default_department_options, (array)$branch_options, (array)$department_options, (array)$job_options, (array)$job_item_options, (array)$title_options, (array)$user_options );
				break;
			case 'cms_obj_state_code_options':
				$retval = array(
								0 => TTi18n::gettext('-- Custom --'),
								'-0010-default_branch_province' => TTi18n::gettext('Default Branch: Province/State'),
								'-0020-branch_province' => TTi18n::gettext('Branch: Province/State'),
								'-0030-user_province' => TTi18n::gettext('Employee: Province/State'),
								);
				break;
			case 'meditech_employee_number_options':
			case 'meditech_department_options':
			case 'meditech_job_code_options':
				$retval = array(
						0 => TTi18n::gettext('-- Custom --'),
						'-0010-default_branch' => TTi18n::gettext('Default Branch'), //Include branch name, as it is alphanumeric value.
						'-0010-default_branch_manual_id' => TTi18n::gettext('Default Branch: Code'),
						'-0020-default_department' => TTi18n::gettext('Default Department'),
						'-0020-default_department_manual_id' => TTi18n::gettext('Default Department: Code'),
						'-0030-branch_name' => TTi18n::gettext('Branch'),
						'-0030-branch_manual_id' => TTi18n::gettext('Branch: Code'),
						'-0040-department_name' => TTi18n::gettext('Department'),
						'-0040-department_manual_id' => TTi18n::gettext('Department: Code'),
				);

				if ( $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$retval['-0200-job_name'] = TTi18n::gettext('Job: Name');
					$retval['-0210-job_manual_id'] = TTi18n::gettext('Job: Code');
					$retval['-0300-job_item_name'] = TTi18n::gettext('Task: Name');
					$retval['-0310-job_item_manual_id'] = TTi18n::gettext('Task: Code');
				}

				$oflf = TTnew( 'OtherFieldListFactory' );

				//Put a colon or underscore in the name, thats how we know it needs to be replaced.

				//Get Branch other fields.
				$default_branch_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 4, '-1000-default_branch_', TTi18n::getText('Default Branch').': ' );
				if (  !is_array($default_branch_options) ) {
					$default_branch_options = array();
				}
				$default_department_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 5, '-2000-default_department_', TTi18n::getText('Default Department').': ' );
				if (  !is_array($default_department_options) ) {
					$default_department_options = array();
				}

				$branch_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 4, '-3000-branch_', TTi18n::getText('Branch').': ' );
				if ( !is_array($branch_options) ) {
					$branch_options = array();
				}
				$department_options = $oflf->getByCompanyIdAndTypeIdArray( $this->getUserObject()->getCompany(), 5, '-4000-department_', TTi18n::getText('Department').': ' );
				if ( !is_array($department_options) ) {
					$department_options = array();
				}

				$retval = array_merge( $retval, (array)$default_branch_options, (array)$default_department_options, (array)$branch_options, (array)$department_options );
				break;
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), NULL, 'PayrollExportReport', 'custom_column' );
					if ( is_array($custom_column_labels) ) {
						$retval = Misc::prependArray( Misc::addSortPrefix( $custom_column_labels, 9500 ), parent::_getOptions( $name, $params ) );
					} else {
						$retval = parent::_getOptions( $name, $params );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$retval = Misc::prependArray( $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('filter_column_type_ids'), NULL, 'PayrollExportReport', 'custom_column' ), parent::_getOptions( $name, $params ) );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('dynamic_format_ids'), 'PayrollExportReport', 'custom_column' );
					if ( is_array($report_dynamic_custom_column_labels) ) {
						$retval = Misc::prependArray( Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 ), parent::_getOptions( $name, $params ) );
					} else {
						$retval = parent::_getOptions( $name, $params );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' );
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions('display_column_type_ids'), $rcclf->getOptions('static_format_ids'), 'PayrollExportReport', 'custom_column' );
					if ( is_array($report_static_custom_column_labels) ) {
						$retval = Misc::prependArray( Misc::addSortPrefix( $report_static_custom_column_labels, 9700 ), parent::_getOptions( $name, $params ) );
					} else {
						$retval = parent::_getOptions( $name, $params );
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
				return Misc::prependArray( array_merge( array( '-1480-sin' => TTi18n::gettext('SIN/SSN') ), (array)$this->getOptions('report_static_custom_column') ), parent::_getOptions( $name, $params ) );
				break;
			default:
				return parent::_getOptions( $name, $params );
				break;
		}

		return $retval;
	}

	/**
	 * @param $config
	 * @param $format
	 * @return array|mixed
	 */
	function getExportTypeTemplate( $config, $format ) {
		$config = Misc::trimSortPrefix( $config );

		if ( $format == 'payroll_export' ) {
			unset($config['columns'], $config['group'], $config['sort'], $config['sub_total']);
			$config['other']['disable_grand_total'] = TRUE; //Disable grand totals.

			if ( isset($config['form']['export_type']) ) {
				$export_type = $config['form']['export_type'];

				//In case the raw Export Format is passed from the ReportScheduleFactory, make sure we extract just the data for the specific $export_type and rearrange things slightly.
				// This is already done in JS before its passed to the API.
				if ( isset($config['form']['export_columns'][$export_type]) ) {
					$tmp_config[$export_type] = $config['form'];
					$tmp_config[$export_type]['columns'] = $config['form']['export_columns'][$export_type]['columns'];
					$tmp_config['export_type'] = $config['form']['export_type'];
					unset($tmp_config[$export_type]['export_columns']);
					$config['form'] = $tmp_config;
				}

				$setup_data = $config['form']; //get setup data to determine custom formats...

				switch( strtolower($export_type) ) {
					case 'adp':
						$config['columns'][] = 'default_branch_id';
						$config['columns'][] = 'default_department_id';

						if ( isset($setup_data['adp']['company_code']) AND strpos( $setup_data['adp']['company_code'], '_' ) !== FALSE ) {
							$config['columns'][] = Misc::trimSortPrefix( $setup_data['adp']['company_code'] );
						}
						if ( isset($setup_data['adp']['batch_id']) AND strpos( $setup_data['adp']['batch_id'], '_' ) !== FALSE ) {
							$config['columns'][] = Misc::trimSortPrefix( $setup_data['adp']['batch_id'] );
						}
						if ( isset($setup_data['adp']['temp_dept']) AND strpos( $setup_data['adp']['temp_dept'], '_' ) !== FALSE ) {
							$config['columns'][] = Misc::trimSortPrefix( $setup_data['adp']['temp_dept'] );
						}
						$config['columns'][] = 'employee_number';
						$config['columns'] += Misc::trimSortPrefix( $this->getOptions('dynamic_columns') );

						$config['group'][] = 'default_branch_id';
						$config['group'][] = 'default_department_id';
						if ( isset($setup_data['adp']['company_code']) AND strpos( $setup_data['adp']['company_code'], '_' ) !== FALSE ) {
							$config['group'][] = Misc::trimSortPrefix( $setup_data['adp']['company_code'] );
						}
						if ( isset($setup_data['adp']['batch_id']) AND strpos( $setup_data['adp']['batch_id'], '_' ) !== FALSE ) {
							$config['group'][] = Misc::trimSortPrefix( $setup_data['adp']['batch_id'] );
						}
						if ( isset($setup_data['adp']['temp_dept']) AND strpos( $setup_data['adp']['temp_dept'], '_' ) !== FALSE ) {
							$config['group'][] = Misc::trimSortPrefix( $setup_data['adp']['temp_dept'] );
						}
						$config['group'][] = 'employee_number';

						if ( isset($setup_data['adp']['company_code']) AND strpos( $setup_data['adp']['company_code'], '_' ) !== FALSE ) {
							$config['sort'][] = array( Misc::trimSortPrefix( $setup_data['adp']['company_code'] ) => 'asc' );
						}
						if ( isset($setup_data['adp']['batch_id']) AND strpos( $setup_data['adp']['batch_id'], '_' ) !== FALSE ) {
							$config['sort'][] = array( Misc::trimSortPrefix( $setup_data['adp']['batch_id'] ) => 'asc' );
						}
						if ( isset($setup_data['adp']['temp_dept']) AND strpos( $setup_data['adp']['temp_dept'], '_' ) !== FALSE ) {
							$config['sort'][] = array( Misc::trimSortPrefix( $setup_data['adp']['temp_dept'] ) => 'asc' );
						}
						$config['sort'][] = array('employee_number' => 'asc' );
						break;
					case 'adp_advanced':  //This uses the Job Analysis report instead, so handle the config later.
					case 'adp_resource':  //This uses the Job Analysis report instead, so handle the config later.
					case 'paychex_preview_advanced_job': //This uses the Job Analysis report instead, so handle the config later.
					case 'meditech': //This uses the Job Analysis report instead, so handle the config later.
						break;
					case 'paychex_preview':
					case 'paychex_online':
					case 'millenium':
					case 'ceridian_insync':
						$config['columns'][] = 'employee_number';
						$config['columns'] += Misc::trimSortPrefix( $this->getOptions('dynamic_columns') );

						$config['group'][] = 'employee_number';

						$config['sort'][] = array('employee_number' => 'asc');
						break;
					case 'quickbooks':
					case 'quickbooks_advanced':
						$config['columns'][] = 'pay_period_end_date';
						$config['columns'][] = 'employee_number';
						$config['columns'][] = 'last_name';
						$config['columns'][] = 'first_name';
						$config['columns'][] = 'middle_name';

						//Support custom group based on PROJ field
						if ( isset($setup_data['quickbooks']['proj']) AND !empty($setup_data['quickbooks']['proj']) ) {
							$config['columns'][] = $setup_data['quickbooks']['proj'];
						}
						if ( isset($setup_data['quickbooks']['item']) AND !empty($setup_data['quickbooks']['item']) ) {
							$config['columns'][] = $setup_data['quickbooks']['item'];
						}
						if ( isset($setup_data['quickbooks']['job']) AND !empty($setup_data['quickbooks']['job']) ) {
							$config['columns'][] = $setup_data['quickbooks']['job'];
						}

						$config['columns'] += array_keys(  Misc::trimSortPrefix( $this->getOptions('dynamic_columns') ) );

						$config['group'][] = 'pay_period_end_date';
						$config['group'][] = 'employee_number';
						$config['group'][] = 'last_name';
						$config['group'][] = 'first_name';
						$config['group'][] = 'middle_name';

						//Support custom group based on PROJ field
						if ( isset($setup_data['quickbooks']['proj']) AND !empty($setup_data['quickbooks']['proj']) ) {
							$config['group'][] = $setup_data['quickbooks']['proj'];
						}
						if ( isset($setup_data['quickbooks']['item']) AND !empty($setup_data['quickbooks']['item']) ) {
							$config['group'][] = $setup_data['quickbooks']['item'];
						}
						if ( isset($setup_data['quickbooks']['job']) AND !empty($setup_data['quickbooks']['job']) ) {
							$config['group'][] = $setup_data['quickbooks']['job'];
						}

						$config['sort'][] = array('pay_period_end_date' => 'asc', 'employee_number' => 'asc', 'last_name' => 'asc', 'first_name' => 'asc');

						break;
					case 'surepayroll':
						$config['columns'][] = 'pay_period_end_date';
						$config['columns'][] = 'employee_number';
						$config['columns'][] = 'last_name';
						$config['columns'][] = 'first_name';
						$config['columns'] += Misc::trimSortPrefix( $this->getOptions('dynamic_columns') );

						$config['group'][] = 'pay_period_end_date';
						$config['group'][] = 'employee_number';
						$config['group'][] = 'last_name';
						$config['group'][] = 'first_name';

						$config['sort'][] = array('pay_period_end_date' => 'asc', 'employee_number' => 'asc', 'last_name' => 'asc', 'first_name' => 'asc');
						break;
					case 'chris21':
						$config['columns'][] = 'pay_period_end_date';
						$config['columns'][] = 'employee_number';
						$config['columns'] += Misc::trimSortPrefix( $this->getOptions('dynamic_columns') );

						$config['group'][] = 'pay_period_end_date';
						$config['group'][] = 'employee_number';

						$config['sort'][] = array('pay_period_end_date' => 'asc', 'employee_number' => 'asc');
						break;
					case 'accero':
						$config['columns'][] = 'default_branch_id';
						$config['columns'][] = 'default_department_id';

						if ( isset($setup_data['accero']['temp_dept']) AND strpos( $setup_data['accero']['temp_dept'], '_' ) !== FALSE ) {
							$config['columns'][] = Misc::trimSortPrefix( $setup_data['accero']['temp_dept'] );
						}
						//$config['columns'][] = 'pay_period_end_date';
						$config['columns'][] = 'date_week_end';
						$config['columns'][] = 'employee_number';
						$config['columns'] += Misc::trimSortPrefix( $this->getOptions('dynamic_columns') );

						$config['group'][] = 'default_branch_id';
						$config['group'][] = 'default_department_id';
						if ( isset($setup_data['accero']['temp_dept']) AND strpos( $setup_data['accero']['temp_dept'], '_' ) !== FALSE ) {
							$config['group'][] = Misc::trimSortPrefix( $setup_data['accero']['temp_dept'] );
						}
						//$config['group'][] = 'pay_period_end_date';
						$config['group'][] = 'date_week_end';
						$config['group'][] = 'employee_number';

						if ( isset($setup_data['accero']['temp_dept']) AND strpos( $setup_data['accero']['temp_dept'], '_' ) !== FALSE ) {
							$config['sort'][] = array( Misc::trimSortPrefix( $setup_data['accero']['temp_dept'] ) => 'asc' );
						}
						//$config['sort'][] = array('pay_period_end_date' => 'asc', 'employee_number' => 'asc');
						$config['sort'][] = array('date_week_end' => 'asc', 'employee_number' => 'asc');
						break;
					case 'compupay':
						$config['columns'][] = 'pay_period_end_date';
						$config['columns'][] = 'employee_number';
						$config['columns'][] = 'last_name';
						$config['columns'][] = 'first_name';
						$config['columns'] += Misc::trimSortPrefix( $this->getOptions('dynamic_columns') );

						$config['group'][] = 'pay_period_end_date';
						$config['group'][] = 'employee_number';

						$config['sort'][] = array('pay_period_end_date' => 'asc', 'employee_number' => 'asc');
						break;
					case 'sage_50':
						$config['columns'][] = 'pay_period_end_date';
						$config['columns'][] = 'employee_number';
						$config['columns'][] = 'last_name';
						$config['columns'][] = 'first_name';

						//Support custom group based on CUSTOMER field
						if ( isset($setup_data['sage_50']['customer_name']) AND !empty($setup_data['sage_50']['customer_name']) ) {
							$config['columns'][] = $setup_data['sage_50']['customer_name'];
						}

						$config['columns'] += Misc::trimSortPrefix( $this->getOptions('dynamic_columns') );

						$config['group'][] = 'pay_period_end_date';
						$config['group'][] = 'employee_number';
						$config['group'][] = 'last_name';
						$config['group'][] = 'first_name';

						if ( isset($setup_data['sage_50']['customer_name']) AND !empty($setup_data['sage_50']['customer_name']) ) {
							$config['group'][] = $setup_data['sage_50']['customer_name'];
						}

						$config['sort'][] = array('pay_period_end_date' => 'asc', 'employee_number' => 'asc', 'last_name' => 'asc', 'first_name' => 'asc');
						break;
					case 'cms_pbj':  //This is XML.
						break;
					case 'csv':
						//If this needs to be customized, they can just export any regular report. This could probably be removed completely except for the Hour Code mapping...
						$config['columns'][] = 'full_name';
						$config['columns'][] = 'employee_number';
						$config['columns'][] = 'default_branch';
						$config['columns'][] = 'default_department';
						$config['columns'][] = 'pay_period';
						$config['columns'] += Misc::trimSortPrefix( $this->getOptions('dynamic_columns') );

						$config['group'][] = 'full_name';
						$config['group'][] = 'employee_number';
						$config['group'][] = 'default_branch';
						$config['group'][] = 'default_department';
						$config['group'][] = 'pay_period';

						$config['sort'][] = array('full_name' => 'asc', 'employee_number' => 'asc', 'default_branch' => 'asc', 'default_department' => 'asc', 'pay_period' => 'asc');
						break;
					case 'va_munis': //This uses the Job Analysis report instead, so handle the config later.
					case 'csv_advanced': //This uses the Job Analysis report instead, so handle the config later.
						break;
				}
				Debug::Arr($config, 'Export Type Template: '. $export_type, __FILE__, __LINE__, __METHOD__, 10);
			} else {
				Debug::Text('No Export Type defined, not modifying config...', __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		return $config;
	}

	//Short circuit this function, as no postprocessing is required for exporting the data.

	/**
	 * @param null $format
	 * @return bool
	 */
	function _postProcess( $format = NULL ) {
		if ( $format == 'payroll_export' ) {
			return TRUE;
		} else {
			return parent::_postProcess( $format );
		}
	}

	/**
	 * @param null $format
	 * @return array|bool|null|string
	 */
	function _outputPayrollExport( $format = NULL ) {
		$setup_data = $this->getFormConfig();

		Debug::Text('Generating Payroll Export... Format: '. $format, __FILE__, __LINE__, __METHOD__, 10);

		if ( isset($setup_data['export_type']) ) {
			Debug::Text('Export Type: '. $setup_data['export_type'], __FILE__, __LINE__, __METHOD__, 10);
		} else {
			Debug::Text('No Export Type defined!', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}
		Debug::Arr($setup_data, 'Setup Data: ', __FILE__, __LINE__, __METHOD__, 10);
		$rows = $this->data;
		//Debug::Arr($rows, 'PreData: ', __FILE__, __LINE__, __METHOD__, 10);

		$file_name = strtolower(trim($setup_data['export_type'])).'_'.date('Y_m_d').'.txt';
		$mime_type = 'application/text';
		$data = NULL;

		$setup_data['export_type'] = strtolower(trim($setup_data['export_type']));
		$export_data_map = array();
		$config = array();
		switch( $setup_data['export_type'] ) {
			case 'adp': //ADP export format.
				//File format supports multiple rows per employee (file #) all using the same columns. No need to jump through nasty hoops to fit everything on row.
				$export_column_map = array(
										'company_code' => 'Co Code',
										'batch_id' => 'Batch ID',
										'temp_dept' => 'Temp Dept',
										'employee_number' => 'File #',
										'regular_time' => 'Reg Hours',
										'overtime' => 'O/T Hours',
										'3_code' => 'Hours 3 Code',
										'3_amount' => 'Hours 3 Amount',
										'4_code' => 'Hours 4 Code',
										'4_amount' => 'Hours 4 Amount',
										//'other_code' => 'Other Hours Code',
										//'other_amount' => 'Other Hours',
										);

				ksort($setup_data['adp']['columns']);
				$setup_data['adp']['columns'] = Misc::trimSortPrefix( $setup_data['adp']['columns'] );

				foreach( $setup_data['adp']['columns'] as $column_id => $column_data ) {
					if ( $column_data['hour_column'] == 'regular_time' ) {
						$export_data_map[$column_id] = 'regular_time';
					} elseif ($column_data['hour_column'] == 'overtime' ) {
						$export_data_map[$column_id] = 'overtime';
					} elseif ( $column_data['hour_column'] == 'other' OR $column_data['hour_column'] >= 3 ) {
						$export_data_map[$column_id] = $column_data;
					}
				}

				if ( !isset($setup_data['adp']['company_code_value']) ) {
					$setup_data['adp']['company_code_value'] = NULL;
				}
				if ( !isset($setup_data['adp']['batch_id_value']) ) {
					$setup_data['adp']['batch_id_value'] = NULL;
				}
				if ( !isset($setup_data['adp']['temp_dept_value']) ) {
					$setup_data['adp']['temp_dept_value'] = NULL;
				}

				$company_code_column = Misc::trimSortPrefix( $setup_data['adp']['company_code'] );
				$batch_id_column = Misc::trimSortPrefix( $setup_data['adp']['batch_id'] );
				$temp_dept_column = Misc::trimSortPrefix( $setup_data['adp']['temp_dept'] );
				$tmp_rows = array();
				foreach($rows as $row) {
					$static_columns = array(
										'company_code' => ( isset($row[$company_code_column]) ) ? $row[$company_code_column] : $setup_data['adp']['company_code_value'],
										'batch_id' => ( isset($row[$batch_id_column]) ) ? $row[$batch_id_column] : $setup_data['adp']['batch_id_value'],
										'temp_dept' => ( isset($row[$temp_dept_column]) ) ? $row[$temp_dept_column] : $setup_data['adp']['temp_dept_value'],
										'employee_number' => str_pad( $row['employee_number'], 6, 0, STR_PAD_LEFT), //ADP employee numbers should always be 6 digits.
										);

					foreach( $setup_data['adp']['columns'] as $column_id => $column_data ) {
						$column_data = Misc::trimSortPrefix( $column_data, TRUE );
						if ( isset( $row[$column_id.'_time'] ) AND $column_data['hour_column'] != '0' ) {
							Debug::Text('ADP Column ID: '. $column_id .' Hour Column: '. $column_data['hour_column'] .' Code: '. $column_data['hour_code'], __FILE__, __LINE__, __METHOD__, 10);
							foreach( $export_column_map as $export_column_id => $export_column_name ) {
								Debug::Arr($row, 'Row: Column ID: '. $column_id .' Export Column ID: '. $export_column_id .' Name: '. $export_column_name, __FILE__, __LINE__, __METHOD__, 10);

								if ( ( $column_data['hour_column'] == $export_column_id OR $column_data['hour_column'].'_code' == $export_column_id )
										AND !in_array( $export_column_id, array('company_code', 'batch_id', 'temp_dept', 'employee_number')) ) {
									$tmp_row = array();
									//if ( (int)substr( $export_column_id, 0, 1 ) > 0 ) {
									if ( strpos( $export_column_id, 'other_' ) !== FALSE OR (int)substr( $export_column_id, 0, 1 ) > 0 ) {
										$tmp_row[$column_data['hour_column'].'_code'] = $column_data['hour_code'];
										$tmp_row[$column_data['hour_column'].'_amount'] = TTDate::getTimeUnit( $row[$column_id.'_time'], 20 );
									} else {
										$tmp_row[$export_column_id] = TTDate::getTimeUnit( $row[$column_id.'_time'], 20 );
									}

									//Break out every column onto its own row, that way its easier to handle multiple columns of the same type.
									$tmp_rows[] = array_merge( $static_columns, $tmp_row );
									unset($tmp_row);
								}
							}
						}
					}
				}

				$file_name = 'EPI000000.csv';
				if ( isset( $tmp_rows[0] ) ) {
					//File format supports multiple entries per employee (file #) all using the same columns. No need to jump through nasty hoops to fit everyone one row.
					$file_name = 'EPI'. $tmp_rows[0]['company_code'] . $tmp_rows[0]['batch_id'] .'.csv';

					$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE );
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);
				break;
			case 'adp_advanced':
			case 'adp_resource': //ADP Resource export format. Its similar to other ADP formats, except it uses "Other Hours" rather than Hours 3 and Hours 4.
				unset($rows); //Ignore any existing timesheet summary data, we will be using our own job data below.
				//Debug::Arr($setup_data, 'ADP Advanced Setup Data: ', __FILE__, __LINE__, __METHOD__, 10);

				$config['columns'][] = 'default_branch_id';
				$config['columns'][] = 'default_department_id';
				if ( isset($setup_data[$setup_data['export_type']]['company_code']) AND strpos( $setup_data[$setup_data['export_type']]['company_code'], '_' ) !== FALSE ) {
					$config['columns'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['company_code'] );
				}
				if ( isset($setup_data[$setup_data['export_type']]['batch_id']) AND strpos( $setup_data[$setup_data['export_type']]['batch_id'], '_' ) !== FALSE ) {
					$config['columns'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['batch_id'] );
				}
				if ( isset($setup_data[$setup_data['export_type']]['work_class']) AND strpos( $setup_data[$setup_data['export_type']]['work_class'], '_' ) !== FALSE ) {
					$config['columns'][] = Misc::trimSortPrefix( 'pay_period_start_date' );
					$config['columns'][] = Misc::trimSortPrefix( 'date_stamp' );
				}
				if ( isset($setup_data[$setup_data['export_type']]['temp_dept']) AND strpos( $setup_data[$setup_data['export_type']]['temp_dept'], '_' ) !== FALSE ) {
					$config['columns'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['temp_dept'] );
				}
				if ( isset($setup_data[$setup_data['export_type']]['job_cost']) AND strpos( $setup_data[$setup_data['export_type']]['job_cost'], '_' ) !== FALSE ) {
					$config['columns'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['job_cost'] );
				}
				if ( isset($setup_data[$setup_data['export_type']]['work_class']) AND strpos( $setup_data[$setup_data['export_type']]['work_class'], '_' ) !== FALSE ) {
					$config['columns'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['work_class'] );
				}
				if ( isset($setup_data[$setup_data['export_type']]['state_columns']) AND strpos( $setup_data[$setup_data['export_type']]['state_columns'], '_' ) !== FALSE ) {
					$config['columns'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['state_columns'] );
				}
				$config['columns'][] = 'employee_number';

				$config['group'][] = 'default_branch_id';
				$config['group'][] = 'default_department_id';
				if ( isset($setup_data[$setup_data['export_type']]['company_code']) AND strpos( $setup_data[$setup_data['export_type']]['company_code'], '_' ) !== FALSE ) {
					$config['group'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['company_code'] );
				}
				if ( isset($setup_data[$setup_data['export_type']]['batch_id']) AND strpos( $setup_data[$setup_data['export_type']]['batch_id'], '_' ) !== FALSE ) {
					$config['group'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['batch_id'] );
				}
				if ( isset($setup_data[$setup_data['export_type']]['work_class']) AND strpos( $setup_data[$setup_data['export_type']]['work_class'], '_' ) !== FALSE ) {
					$config['group'][] = Misc::trimSortPrefix( 'pay_period_start_date' );
					$config['group'][] = Misc::trimSortPrefix( 'date_stamp' );
				}
				if ( isset($setup_data[$setup_data['export_type']]['temp_dept']) AND strpos( $setup_data[$setup_data['export_type']]['temp_dept'], '_' ) !== FALSE ) {
					$config['group'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['temp_dept'] );
				}
				if ( isset($setup_data[$setup_data['export_type']]['job_cost']) AND strpos( $setup_data[$setup_data['export_type']]['job_cost'], '_' ) !== FALSE ) {
					$config['group'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['job_cost'] );
				}
				if ( isset($setup_data[$setup_data['export_type']]['work_class']) AND strpos( $setup_data[$setup_data['export_type']]['work_class'], '_' ) !== FALSE ) {
					$config['group'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['work_class'] );
				}
				if ( isset($setup_data[$setup_data['export_type']]['state_columns']) AND strpos( $setup_data[$setup_data['export_type']]['state_columns'], '_' ) !== FALSE ) {
					$config['group'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['state_columns'] );
				}
				$config['group'][] = 'employee_number';

				$dynamic_column_names = array_keys( Misc::trimSortPrefix( $this->getOptions('dynamic_columns') ) );
				//Force group by hourly_rates, so if there are multiple hourly rates with the same pay code its still split out.
				foreach( $dynamic_column_names as $dynamic_column_name ) {
					if ( substr( $dynamic_column_name, -12 ) == '_hourly_rate' ) { //Ends with
						$config['group'][] = $dynamic_column_name;
					}
				}
				unset($dynamic_column_names, $dynamic_column_name);

				$config['sort'][] = array( 'default_branch_id' => 'asc');
				$config['sort'][] = array( 'default_department_id' => 'asc');
				if ( isset($setup_data[$setup_data['export_type']]['company_code']) AND strpos( $setup_data[$setup_data['export_type']]['company_code'], '_' ) !== FALSE ) {
					$config['sort'][] = array( Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['company_code'] ) => 'asc' );
				}
				if ( isset($setup_data[$setup_data['export_type']]['batch_id']) AND strpos( $setup_data[$setup_data['export_type']]['batch_id'], '_' ) !== FALSE ) {
					$config['sort'][] = array( Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['batch_id'] ) => 'asc' );
				}
				if ( isset($setup_data[$setup_data['export_type']]['work_class']) AND strpos( $setup_data[$setup_data['export_type']]['work_class'], '_' ) !== FALSE ) {
					$config['sort'][] = Misc::trimSortPrefix( 'pay_period_start_date' );
					$config['sort'][] = Misc::trimSortPrefix( 'date_stamp' );
				}
				if ( isset($setup_data[$setup_data['export_type']]['temp_dept']) AND strpos( $setup_data[$setup_data['export_type']]['temp_dept'], '_' ) !== FALSE ) {
					$config['sort'][] = array( Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['temp_dept'] ) => 'asc' );
				}
				if ( isset($setup_data[$setup_data['export_type']]['job_cost']) AND strpos( $setup_data[$setup_data['export_type']]['job_cost'], '_' ) !== FALSE ) {
					$config['sort'][] = array( Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['job_cost'] ) => 'asc' );
				}
				if ( isset($setup_data[$setup_data['export_type']]['work_class']) AND strpos( $setup_data[$setup_data['export_type']]['work_class'], '_' ) !== FALSE ) {
					$config['sort'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['work_class'] );
				}
				if ( isset($setup_data[$setup_data['export_type']]['state_columns']) AND strpos( $setup_data[$setup_data['export_type']]['state_columns'], '_' ) !== FALSE ) {
					$config['sort'][] = array( Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['state_columns'] ) => 'asc' );
				}
				$config['sort'][] = array('employee_number' => 'asc' );
				Debug::Arr($config, 'ADP Advanced Config Data: ', __FILE__, __LINE__, __METHOD__, 10);

				if ( is_object( $this->getUserObject() ) AND is_object( $this->getUserObject()->getCompanyObject() ) AND $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
					Debug::Text('Using Job Detail Report...', __FILE__, __LINE__, __METHOD__, 10);
					$jar = TTNew('JobDetailReport');
				} else {
					Debug::Text('Using TimeSheet Detail Report...', __FILE__, __LINE__, __METHOD__, 10);
					$jar = TTNew('TimesheetDetailReport');
				}
				$jar->setAMFMessageID( $this->getAMFMessageID() );
				$jar->setUserObject( $this->getUserObject() );
				$jar->setPermissionObject( $this->getPermissionObject() );
				$jar->setConfig( $config );
				$jar->setFilterConfig( $this->getFilterConfig() );
				$jar->setCustomFilterConfig( $this->getCustomFilterConfig() );
				$jar->setCustomColumnConfig( array_merge( (array)$this->getCustomFilterConfig(), (array)$this->getCustomColumnConfig() ) );
				if ( isset($config['sort']) ) {
					$jar->setSortConfig( $config['sort'] );
				}
				$jar->_getData();
				$jar->_preProcess();
				$jar->currencyConvertToBase();
				//$jar->calculateCustomColumns( 10 ); //Selections (these are pre-group)
				//$jar->calculateCustomColumns( 20 ); //Pre-Group
				$jar->calculateCustomColumnFilters( 30 ); //Pre-Group
				$jar->group();
				//$jar->calculateCustomColumns( 21 ); //Post-Group: things like round() functions normally need to be done post-group, otherwise they are rounding already rounded values.
				$jar->calculateCustomColumnFilters( 31 ); //Pre-Group
				$jar->sort();
				$jar->_postProcess( 'csv' ); //Minor post-processing.
				$rows = $jar->data;
				//Debug::Arr($rows, 'Raw Rows: ', __FILE__, __LINE__, __METHOD__, 10);

				//File format supports multiple rows per employee (file #) all using the same columns. No need to jump through nasty hoops to fit everything on row.
				if ( $setup_data['export_type'] == 'adp_advanced' ) {
					$export_column_map = array(
											'company_code' => 'Co Code',
											'batch_id' => 'Batch ID',
											'employee_number' => 'File #',
											'temp_state' => 'Temp State Code',
											'temp_dept' => 'Temp Dept',
											'job_cost' => 'Temp Cost #',
											'temp_rate' => 'Temp Rate',
											'regular_time' => 'Reg Hours',
											'overtime' => 'O/T Hours',
											'3_code' => 'Hours 3 Code',
											'3_amount' => 'Hours 3 Amount',
											'4_code' => 'Hours 4 Code',
											'4_amount' => 'Hours 4 Amount',
											);
				} else { //ADP Resource
					$export_column_map = array(
											'company_code' => 'Co Code',
											'batch_id' => 'Batch ID',
											'temp_state' => 'Work State',
											'temp_dept' => 'Temp Dept',
											'job_cost' => 'Job Cost #',
											'employee_number' => 'File #',
											'temp_rate' => 'Temp Rate',
											'regular_time' => 'Reg Hours',
											'overtime' => 'O/T Hours',
											'other_code' => 'Other Hours Code',
											'other_amount' => 'Other Hours',
											);
				}

				if ( !( ( isset($setup_data[$setup_data['export_type']]['temp_dept']) AND $setup_data[$setup_data['export_type']]['temp_dept'] != '' ) OR ( isset($setup_data[$setup_data['export_type']]['temp_dept_value']) AND $setup_data[$setup_data['export_type']]['temp_dept_value'] != '' ) ) ) {
					unset($export_column_map['temp_dept']); //If the ADP company is not setup for Labor Distribution by Dept, then this column must be removed.
				}

				ksort($setup_data[$setup_data['export_type']]['columns']);
				$setup_data[$setup_data['export_type']]['columns'] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['columns'] );

				foreach( $setup_data[$setup_data['export_type']]['columns'] as $column_id => $column_data ) {
					if ( $column_data['hour_column'] == 'regular_time' ) {
						$export_data_map[$column_id] = 'regular_time';
					} elseif ($column_data['hour_column'] == 'overtime' ) {
						$export_data_map[$column_id] = 'overtime';
					} elseif ( $column_data['hour_column'] == 'other' OR $column_data['hour_column'] >= 3 ) {
						$export_data_map[$column_id] = $column_data;
					}
				}

				if ( !isset($setup_data[$setup_data['export_type']]['company_code_value']) ) {
					$setup_data[$setup_data['export_type']]['company_code_value'] = NULL;
				}
				if ( !isset($setup_data[$setup_data['export_type']]['batch_id_value']) ) {
					$setup_data[$setup_data['export_type']]['batch_id_value'] = NULL;
				}
				if ( !isset($setup_data[$setup_data['export_type']]['temp_dept_value']) ) {
					$setup_data[$setup_data['export_type']]['temp_dept_value'] = NULL;
				}
				if ( !isset($setup_data[$setup_data['export_type']]['job_cost_value']) ) {
					$setup_data[$setup_data['export_type']]['job_cost_value'] = NULL;
				}

				$company_code_column = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['company_code'] );
				$batch_id_column = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['batch_id'] );
				$temp_dept_column = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['temp_dept'] );
				$job_cost_column = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['job_cost'] );
				$state_column = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['state_columns'] );
				foreach($rows as $row) {
					$static_columns = array(
										'company_code' => ( isset($row[$company_code_column]) ) ? $row[$company_code_column] : $setup_data[$setup_data['export_type']]['company_code_value'],
										'batch_id' => ( isset($row[$batch_id_column]) ) ? $row[$batch_id_column] : $setup_data[$setup_data['export_type']]['batch_id_value'],
										'temp_dept' => ( isset($row[$temp_dept_column]) ) ? $row[$temp_dept_column] : $setup_data[$setup_data['export_type']]['temp_dept_value'],
										'job_cost' => ( isset($row[$job_cost_column]) ) ? $row[$job_cost_column] : $setup_data[$setup_data['export_type']]['job_cost_value'],
										'temp_state' => ( isset($row[$state_column]) ) ? $row[$state_column] : NULL,
										'employee_number' => str_pad( $row['employee_number'], 6, 0, STR_PAD_LEFT), //ADP employee numbers should always be 6 digits.
										);

					if ( isset($setup_data[$setup_data['export_type']]['work_class']) ) {
						if ( strpos( $setup_data[$setup_data['export_type']]['work_class'], '_' ) !== FALSE AND isset($row[$setup_data[$setup_data['export_type']]['work_class']]) ) {
							$work_class = $row[$setup_data[$setup_data['export_type']]['work_class']];
						} elseif ( isset($setup_data[$setup_data['export_type']]['work_class_value']) AND $setup_data[$setup_data['export_type']]['work_class_value'] != '' ) {
							$work_class = $setup_data[$setup_data['export_type']]['work_class_value'];
						}

						if ( isset($work_class) AND $work_class != '' ) {
							$static_columns['job_cost'] .= '-'. $work_class;

							if ( isset($row['date_stamp']) AND isset($row['pay_period_start_date']) ) {
								//Debug::Text('Pay Period Start Date: '. $row['pay_period_start_date'] .' Date: '. $row['date_stamp'], __FILE__, __LINE__, __METHOD__, 10);
								$day_of_pay_period = str_pad( ( round( TTDate::getDays( ( TTDate::parseDateTime( $row['date_stamp'] ) - TTDate::parseDateTime( $row['pay_period_start_date'] ) ) ) ) + 1 ), 2, 0, STR_PAD_LEFT);
								$static_columns['job_cost'] .= '-'.  $day_of_pay_period;
							}

							unset($work_class, $day_of_pay_period);
						}
					}

					foreach( $setup_data[$setup_data['export_type']]['columns'] as $column_id => $column_data ) {
						$column_data = Misc::trimSortPrefix( $column_data, TRUE );
						if ( isset( $row[$column_id.'_time'] ) AND $column_data['hour_column'] != '0' ) {
							//Debug::Text('ADP Column ID: '. $column_id .' Hour Column: '. $column_data['hour_column'] .' Code: '. $column_data['hour_code'], __FILE__, __LINE__, __METHOD__, 10);
							foreach( $export_column_map as $export_column_id => $export_column_name ) {
								//Debug::Arr($row, 'Row: Column ID: '. $column_id .' Export Column ID: '. $export_column_id .' Name: '. $export_column_name, __FILE__, __LINE__, __METHOD__, 10);
								if ( ( $column_data['hour_column'] == $export_column_id OR $column_data['hour_column'].'_code' == $export_column_id )
										AND !in_array( $export_column_id, array('company_code', 'batch_id', 'temp_dept', 'job_cost', 'temp_rate', 'state_columns', 'employee_number')) ) {

									if ( strpos( $export_column_id, 'other_' ) !== FALSE OR (int)substr( $export_column_id, 0, 1 ) > 0 ) {
										$tmp_row[$column_data['hour_column'].'_code'] = $column_data['hour_code'];
										$tmp_row[$column_data['hour_column'].'_amount'] = TTDate::getTimeUnit( $row[$column_id.'_time'], 20 );
									} else {
										$tmp_row[$export_column_id] = TTDate::getTimeUnit( $row[$column_id.'_time'], 20 );
									}
									$tmp_row['temp_rate'] = ( isset($row[$column_id.'_hourly_rate']) ) ? $row[$column_id.'_hourly_rate'] : NULL;

									//Break out every column onto its own row, that way its easier to handle multiple columns of the same type.
									$tmp_rows[] = array_merge( $static_columns, $tmp_row );

									unset($tmp_row);
								}
							}
						}
					}
				}
				//Debug::Arr($tmp_rows, 'TMP Rows: ', __FILE__, __LINE__, __METHOD__, 10);

				$file_name = 'EPI000000.csv';
				if ( isset( $tmp_rows) ) {
					//File format supports multiple entries per employee (file #) all using the same columns. No need to jump through nasty hoops to fit everyone one row.
					$file_name = 'EPI'. $tmp_rows[0]['company_code'] . $tmp_rows[0]['batch_id'] .'.csv';

					$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE );
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);

				break;
			case 'paychex_preview_advanced_job': //PayChex Preview with job information
				unset($rows); //Ignore any existing timesheet summary data, we will be using our own job data below.
				//Debug::Arr($setup_data, 'PayChex Advanced Job Setup Data: ', __FILE__, __LINE__, __METHOD__, 10);

				$config['columns'][] = 'employee_number';
				$config['columns'][] = 'date_stamp';
				$config['columns'] = array_merge( $config['columns'], (array)$setup_data['paychex_preview_advanced_job']['job_columns'] );
				$config['columns'][] = $setup_data['paychex_preview_advanced_job']['state_columns'];
				$config['columns'] += array_keys(  Misc::trimSortPrefix( $this->getOptions('dynamic_columns') ) );

				$config['group'][] = 'employee_number';
				$config['group'][] = 'date_stamp';
				$config['group'] = array_merge( $config['columns'], (array)$setup_data['paychex_preview_advanced_job']['job_columns']);
				$config['group'][] = $setup_data['paychex_preview_advanced_job']['state_columns'];

				$config['sort'][] = array('employee_number' => 'asc');
				$config['sort'][] = array('date_stamp' => 'asc');
				//Debug::Arr($config, 'Job Detail Report Config: ', __FILE__, __LINE__, __METHOD__, 10);

				//Get job data...
				$jar = TTNew('JobDetailReport');
				$jar->setAMFMessageID( $this->getAMFMessageID() );
				$jar->setUserObject( $this->getUserObject() );
				$jar->setPermissionObject( $this->getPermissionObject() );
				$jar->setConfig( $config );
				$jar->setFilterConfig( $this->getFilterConfig() );
				$jar->setCustomFilterConfig( $this->getCustomFilterConfig() );
				$jar->setCustomColumnConfig( array_merge( (array)$this->getCustomFilterConfig(), (array)$this->getCustomColumnConfig() ) );
				$jar->setSortConfig( $config['sort'] );
//				$jar->_getData();
//				$jar->_preProcess();
//				$jar->sort();
				$jar->_getData();
				$jar->_preProcess();
				$jar->currencyConvertToBase();
				//$jar->calculateCustomColumns( 10 ); //Selections (these are pre-group)
				//$jar->calculateCustomColumns( 20 ); //Pre-Group
				$jar->calculateCustomColumnFilters( 21 ); //Pre-Group
				//$jar->group();
				//$jar->calculateCustomColumns( 21 ); //Post-Group: things like round() functions normally need to be done post-group, otherwise they are rounding already rounded values.
				$jar->calculateCustomColumnFilters( 31 ); //Since group() is not called above, this is unlikely to function as expected, but leave it here so it functions essentially like a pre-group
				$jar->sort();

				$rows = $jar->data;
				//Debug::Arr($rows, 'Raw Rows: ', __FILE__, __LINE__, __METHOD__, 10);

				//Need to get job data from job report instead of TimeSheet Summary report.
				if ( !isset($setup_data['paychex_preview_advanced_job']['client_number']) ) {
					$setup_data['paychex_preview_advanced_job']['client_number'] = '0000';
				}

				$file_name = $setup_data['paychex_preview_advanced_job']['client_number'] .'_TA.txt';

				ksort($setup_data['paychex_preview_advanced_job']['columns']);
				$setup_data['paychex_preview_advanced_job']['columns'] = Misc::trimSortPrefix( $setup_data['paychex_preview_advanced_job']['columns'] );

				$data = NULL;
				foreach($rows as $row) {
					foreach( $setup_data['paychex_preview_advanced_job']['columns'] as $column_id => $column_data ) {
						if ( isset( $row[$column_id.'_time'] ) AND trim($column_data['hour_code']) != '' ) {
							$data .= str_pad($row['employee_number'], 6, ' ', STR_PAD_LEFT);
							$data .= str_pad('', 31, ' ', STR_PAD_LEFT); //Blank space.
							if ( isset($setup_data['paychex_preview_advanced_job']['job_columns']) AND is_array($setup_data['paychex_preview_advanced_job']['job_columns']) ) {
								$job_column = array();
								foreach( $setup_data['paychex_preview_advanced_job']['job_columns'] as $tmp_job_column ) {
									$job_column[] = ( isset($row[$tmp_job_column]) ) ? $row[$tmp_job_column] : NULL ;
								}
								$data .= str_pad( substr( implode('-', $job_column), 0, 12 ), 12, ' ', STR_PAD_LEFT);
								unset($job_column);
							} else {
								$data .= str_pad( '', 12, ' ', STR_PAD_LEFT);
							}
							$data .= str_pad('', 1, ' ', STR_PAD_LEFT); //Shift identifier.

							//Allow user to specify three digit hour codes to specify their own E/D codes. If codes are two digit, always use E.
							if ( strlen( trim($column_data['hour_code']) ) < 3 ) {
								$column_data['hour_code'] = 'E'.trim($column_data['hour_code']);
							}
							//Should start at col51
							$data .= str_pad( substr( trim($column_data['hour_code']), 0, 3), 3, ' ', STR_PAD_RIGHT);
							if ( isset($setup_data['paychex_preview_advanced_job']['include_hourly_rate']) AND $setup_data['paychex_preview_advanced_job']['include_hourly_rate'] == TRUE ) {
								$data .= str_pad( ( isset($row[$column_id.'_hourly_rate']) ? number_format( $row[$column_id.'_hourly_rate'], 4, '.', '') : NULL ), 9, 0, STR_PAD_LEFT); //Override rate
							} else {
								$data .= str_pad( '', 9, 0, STR_PAD_LEFT); //Override rate
							}
							$data .= str_pad( TTDate::getTimeUnit( $row[$column_id.'_time'], 20 ), 8, 0, STR_PAD_LEFT);

							//Break out time by day.
							$data .= str_pad( TTDate::getYear($row['time_stamp']), 4, 0, STR_PAD_LEFT); //Year, based on time_stamp epoch column
							$data .= str_pad( TTDate::getMonth($row['time_stamp']), 2, 0, STR_PAD_LEFT); //Month, based on time_stamp epoch column. Can be space padded.
							$data .= str_pad( TTDate::getDayOfMonth($row['time_stamp']), 2, 0, STR_PAD_LEFT); //Day, based on time_stamp epoch column. Can be space padded.

							$data .= str_pad('', 4, ' ', STR_PAD_LEFT); //Filler
							$data .= str_pad( '', 9, ' ', STR_PAD_LEFT); //Amount. This can always be calculated from hours and hourly rate above though.
							$data .= str_pad( '', 13, ' ', STR_PAD_LEFT); //Blank space
							if ( isset($setup_data['paychex_preview_advanced_job']['state_columns']) ) {
								$data .= str_pad( ( isset($row[$setup_data['paychex_preview_advanced_job']['state_columns']]) ) ? $row[$setup_data['paychex_preview_advanced_job']['state_columns']] : NULL, 2, ' ', STR_PAD_LEFT); //Override State
							}
							$data .= str_pad( '', 10, ' ', STR_PAD_LEFT); //Override Local
							if ( isset($setup_data['paychex_preview_advanced_job']['state_columns'])
									AND isset($row[$setup_data['paychex_preview_advanced_job']['state_columns']])
									AND $row[$setup_data['paychex_preview_advanced_job']['state_columns']] != '' ) {
								$data .= 'S'; //State/Local Misc Field, needs 'S' to trigger override state column above.
							}

							$data .= "\n";
						}
					}
				}

				break;
			case 'paychex_preview': //Paychex Preview export format.
				//Add an advanced PayChex Preview format that supports rates perhaps?
				//http://kb.idb-sys.com/KnowledgebaseArticle10013.aspx
				if ( !isset($setup_data['paychex_preview']['client_number']) ) {
					$setup_data['paychex_preview']['client_number'] = '0000';
				}

				$file_name = $setup_data['paychex_preview']['client_number'] .'_TA.txt';

				ksort($setup_data['paychex_preview']['columns']);
				$setup_data['paychex_preview']['columns'] = Misc::trimSortPrefix( $setup_data['paychex_preview']['columns'] );

				$data = NULL;
				foreach($rows as $row) {
					foreach( $setup_data['paychex_preview']['columns'] as $column_id => $column_data ) {
						if ( isset( $row[$column_id.'_time'] ) AND trim($column_data['hour_code']) != '' ) {
							$data .= str_pad($row['employee_number'], 6, ' ', STR_PAD_LEFT);
							$data .= str_pad('E'. str_pad( trim($column_data['hour_code']), 2, ' ', STR_PAD_RIGHT), 47, ' ', STR_PAD_LEFT);
							$data .= str_pad( str_pad( TTDate::getTimeUnit( $row[$column_id.'_time'], 20 ), 8, 0, STR_PAD_LEFT), 17, ' ', STR_PAD_LEFT)."\n";
						}
					}
				}
				break;
			case 'paychex_online': //Paychex Online Payroll CSV
				ksort($setup_data['paychex_online']['columns']);
				$setup_data['paychex_online']['columns'] = Misc::trimSortPrefix( $setup_data['paychex_online']['columns'] );

				$earnings = array();
				//Find all the hours codes
				foreach( $setup_data['paychex_online']['columns'] as $column_id => $column_data ) {
					$hour_code = $column_data['hour_code'];
					$earnings[] = $hour_code;
				}

				$export_column_map['employee_number'] = '';
				foreach($earnings as $value) {
					$export_column_map[$value] = '';
				}

				$i = 0;
				$tmp_hour_codes = array();
				foreach($rows as $row) {
					if ( $i == 0 ) {
						//Include header.
						$tmp_row['employee_number'] = 'Employee Number';
						foreach($earnings as $value) {
							$tmp_row[$value] = $value . ' Hours';
						}
						$tmp_rows[] = $tmp_row;
						unset($tmp_row);
					}

					//Combine all hours from the same code together.
					foreach( $setup_data['paychex_online']['columns'] as $column_id => $column_data ) {
						$hour_code = trim($column_data['hour_code']);
						if ( isset( $row[$column_id.'_time'] ) AND $hour_code != '' ) {
							if ( !isset($tmp_hour_codes[$hour_code]) ) {
								$tmp_hour_codes[$hour_code] = 0;
							}
							$tmp_hour_codes[$hour_code] = bcadd( $tmp_hour_codes[$column_data['hour_code']], $row[$column_id.'_time'] ); //Use seconds for math here.
						}
					}

					if ( isset($tmp_hour_codes) ) {
						$tmp_row['employee_number'] = $row['employee_number'];
						foreach($tmp_hour_codes as $hour_code => $hours ) {
							$tmp_row[$hour_code] = TTDate::getTimeUnit($hours, 20);
						}
						$tmp_rows[] = $tmp_row;
						unset($tmp_hour_codes, $hour_code, $hours, $tmp_row);
					}

					$i++;
				}

				if ( isset( $tmp_rows) ) {
					$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE );
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);
				break;
			case 'millenium': //Millenium export format. Also used by Qqest.
				ksort($setup_data['millenium']['columns']);
				$setup_data['millenium']['columns'] = Misc::trimSortPrefix( $setup_data['millenium']['columns'] );

				$export_column_map = array('employee_number' => '', 'transaction_code' => '', 'hour_code' => '', 'hours' => '');
				foreach($rows as $row) {
					foreach( $setup_data['millenium']['columns'] as $column_id => $column_data ) {
						if ( isset( $row[$column_id.'_time'] ) AND trim($column_data['hour_code']) != '' ) {
							$tmp_rows[] = array(
												'employee_number' => $row['employee_number'],
												'transaction_code' => 'E',
												'hour_code' => trim($column_data['hour_code']),
												'hours' => TTDate::getTimeUnit( $row[$column_id.'_time'], 20 )
												);
						}
					}
				}

				if ( isset( $tmp_rows) ) {
					$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE );
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);
				break;

			case 'ceridian_insync': //Ceridian InSync export format. Needs to be .IMP to import? DOS line endings?
				if ( !isset($setup_data['ceridian_insync']['employer_number']) OR $setup_data['ceridian_insync']['employer_number'] == '' ) {
					$setup_data['ceridian_insync']['employer_number'] = '0001';
				}

				$file_name = strtolower(trim($setup_data['export_type'])).'_'. $setup_data['ceridian_insync']['employer_number'] .'_'. date('Y_m_d').'.imp';

				ksort($setup_data['ceridian_insync']['columns']);
				$setup_data['ceridian_insync']['columns'] = Misc::trimSortPrefix( $setup_data['ceridian_insync']['columns'] );

				$export_column_map = array(	'employer_number' => '', 'import_type_id' => '', 'employee_number' => '', 'check_type' => '',
											'hour_code' => '', 'value' => '', 'distribution' => '', 'rate' => '', 'premium' => '', 'day' => '', 'pay_period' => '');
				foreach($rows as $row) {
					foreach( $setup_data['ceridian_insync']['columns'] as $column_id => $column_data ) {
						if ( isset( $row[$column_id.'_time'] ) AND trim($column_data['hour_code']) != '' ) {
							$tmp_rows[] = array(
												'employer_number' => $setup_data['ceridian_insync']['employer_number'], //Employer No./Payroll Number
												'import_type_id' => 'COSTING', //This can change, must be configurable.
												'employee_number' => str_pad( $row['employee_number'], 9, '0', STR_PAD_LEFT),
												'check_type' => 'REG',
												'hour_code' => trim($column_data['hour_code']),
												'value' => TTDate::getTimeUnit( $row[$column_id.'_time'], 20 ),
												'distribution' => NULL,
												'rate' => NULL, //This overrides whats in ceridian and seems to cause problems.
												//'rate' => ( isset($row[$column_id.'_hourly_rate']) ) ? $row[$column_id.'_hourly_rate'] : NULL,
												'premium' => NULL,
												'day' => NULL,
												'pay_period' => NULL,
												);
						}
					}
				}

				if ( isset( $tmp_rows) ) {
					$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE, "\r\n" ); //Use DOS line endings only.
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);

				break;
			case 'quickbooks': //Quickbooks Pro export format.
			case 'quickbooks_advanced': //Quickbooks Pro export format.
				$file_name = 'payroll_export.iif';

				ksort($setup_data['quickbooks']['columns']);
				$setup_data['quickbooks']['columns'] = Misc::trimSortPrefix( $setup_data['quickbooks']['columns'] );

				//
				// Quickbooks header
				//
				/*
					Company Create Time can be found by first running an Timer Activity export in QuickBooks and viewing the output.

					PITEM field needs to be populated, as that is the PAYROLL ITEM in quickbooks. It can be the same as the ITEM field.
					ITEM is the service item, can be mapped to department/task?
					PROJ could be mapped to the default department/branch?
				*/
				$data = "!TIMERHDR\tVER\tREL\tCOMPANYNAME\tIMPORTEDBEFORE\tFROMTIMER\tCOMPANYCREATETIME\n";
				$data .= "TIMERHDR\t8\t0\t". trim($setup_data['quickbooks']['company_name']) ."\tN\tY\t". trim($setup_data['quickbooks']['company_created_date']) ."\n";
				$data .= "!TIMEACT\tDATE\tJOB\tEMP\tITEM\tPITEM\tDURATION\tPROJ\tNOTE\tXFERTOPAYROLL\tBILLINGSTATUS\n";

				foreach($rows as $row) {
					foreach( $setup_data['quickbooks']['columns'] as $column_id => $column_data ) {
						if ( isset( $row[$column_id.'_time'] ) AND trim($column_data['hour_code']) != '' ) {
							//Make sure employee name is in format: LastName, FirstName MiddleInitial
							$tmp_employee_name = $row['last_name'].', '. $row['first_name'];
							if ( isset($row['middle_name']) AND strlen($row['middle_name']) > 0 ) {
								$tmp_employee_name .= ' '.substr(trim($row['middle_name']), 0, 1);
							}

							$proj = NULL;
							if ( isset($row[$setup_data['quickbooks']['proj']]) ) {
								$proj = $row[$setup_data['quickbooks']['proj']];
							}
							$item = NULL;
							if ( isset($row[$setup_data['quickbooks']['item']]) ) {
								$item = $row[$setup_data['quickbooks']['item']];
							}
							$job = NULL;
							if ( isset($row[$setup_data['quickbooks']['job']]) ) {
								$job = $row[$setup_data['quickbooks']['job']];
							}

							$data .= "TIMEACT\t". date('n/j/y', $row['pay_period_end_date'])."\t". $job ."\t". $tmp_employee_name ."\t". $item ."\t". trim($column_data['hour_code']) ."\t".  TTDate::getTimeUnit( $row[$column_id.'_time'], 10 ) ."\t". $proj ."\t\tY\t0\n";
							unset($tmp_employee_name);
						}
					}
				}

				break;
			case 'surepayroll': //SurePayroll Export format.
				$file_name = strtolower(trim($setup_data['export_type'])).'_'.date('Y_m_d').'.csv';

				ksort($setup_data['surepayroll']['columns']);
				$setup_data['surepayroll']['columns'] = Misc::trimSortPrefix( $setup_data['surepayroll']['columns'] );

				//
				//header
				//
				$data = 'TC'."\n";
				$data .= '00001'."\n";

				$export_column_map = array(	'pay_period_end_date' => 'Entry Date',
											'employee_number' => 'Employee Number',
											'last_name' => 'Last Name',
											'first_name' => 'First Name',
											'hour_code' => 'Payroll Code',
											'value' => 'Hours' );

				foreach($rows as $row) {
					foreach( $setup_data['surepayroll']['columns'] as $column_id => $column_data ) {

						if ( isset( $row[$column_id.'_time'] ) AND trim($column_data['hour_code']) != '' ) {
							//Debug::Arr($column_data, 'Output2', __FILE__, __LINE__, __METHOD__, 10);
							$tmp_rows[] = array(
												'pay_period_end_date' => date('m/d/Y', $row['pay_period_end_date']),
												'employee_number' => $row['employee_number'],
												'last_name' => $row['last_name'],
												'first_name' => $row['first_name'],
												'hour_code' => trim($column_data['hour_code']),
												'value' => TTDate::getTimeUnit( $row[$column_id.'_time'], 20 ),
												);
						}
					}
				}

				if ( isset( $tmp_rows) ) {
					$data .= Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE );
					$data = str_replace('"', '', $data);
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);
				break;
			case 'chris21': //Chris21 Export format.
				//Get all Absence Policies so we can determine which pay codes are absences.
				$absence_policy_data = array();
				$aplf = TTnew( 'AbsencePolicyListFactory' );
				$aplf->getByCompanyId( $this->getUserObject()->getCompany() );
				if ( $aplf->getRecordCount() > 0 ) {
					foreach( $aplf as $ap_obj ) {
						$pay_code_obj = $ap_obj->getPayCodeObject();
						if ( is_object( $pay_code_obj ) ) {
							$absence_policy_data['pay_code:'.$pay_code_obj->getId()] = $pay_code_obj;
						}
					}
				}
				unset($aplf, $ap_obj, $pay_code_obj);

				//Columns required: Employee_number (2), Date (10), ADJUSTMENT_CODE (12), HOURS (13), SIGNED_HOURS(15)[?]
				//Use SIGNED_HOURS only, as it provides more space?
				//When using absences a leave start/end date must be specified other it won't be imported.
				ksort($setup_data['chris21']['columns']);
				$setup_data['chris21']['columns'] = Misc::trimSortPrefix( $setup_data['chris21']['columns'] );

				$data = '';
				foreach($rows as $row) {
					foreach( $setup_data['chris21']['columns'] as $column_id => $column_data ) {

						if ( isset( $row[$column_id.'_time'] ) AND trim($column_data['hour_code']) != '' ) {
							//Debug::Arr($column_data, 'Output2: ID: '. $column_id, __FILE__, __LINE__, __METHOD__, 10);
							$data .= str_repeat(' ', 8);															//8 digits Blank
							$data .= str_pad( substr( $row['employee_number'], 0, 7), 7, ' ', STR_PAD_RIGHT);		//7 digits
							$data .= str_repeat(' ', 11);															//14 digits Blank
							$data .= date('dmy', $row['pay_period_end_date']);										//4 digits Date
							$data .= str_repeat(' ', 4);															//4 digits Blank
							$data .= str_pad( substr( trim($column_data['hour_code']), 0, 4), 4, ' ', STR_PAD_RIGHT);//4 digits
							$data .= '0000';																		//4 digits HOURS field, always be 0, use SIGNED_HOURS instead.
							$data .= str_repeat(' ', 4);															//CC_CODE: 4 digits Blank
							$data .= str_pad( str_replace('.', '', TTDate::getTimeUnit( $row[$column_id.'_time'], 20 ) ), 6, 0, STR_PAD_LEFT).'+'; //SIGNED_HOURS: Hours without decimal padded to 6 digits, with '+' on the end.
							//$data .= '+000000000';																	//Filler: Redefintion of SIGNED_HOURS.
							$data .= '000000000';																	//RATE: 9 chars
							$data .= str_repeat(' ', 20);															//ACCT_NO: 20 chars
							$data .= str_repeat(' ', 16);
							if ( isset($absence_policy_data[$column_id]) ) {
								$data .= date('dmy', $row['pay_period_end_date']);										//LEAVE Start Date: 6 digits
								$data .= date('dmy', $row['pay_period_end_date']);										//LEAVE End Date: 6 digits
							}
							$data .= "\n";
						}
					}
				}
				unset($tmp_rows, $column_id, $column_data, $rows, $row, $absence_policy_data);
				break;
			case 'va_munis':
				//MAP specific fields in Export Setup tab, ie:
				//Department: <export column dropdown box>
				//Long GL Account: <export column dropdown box>
				//Pay Code: <export column dropdown box>
				unset($rows); //Ignore any existing timesheet summary data, we will be using our own job data below.

				//Get all Absence Policies so we can determine which ones are paid/unpaid.
				$absence_policy_data = array();
				$aplf = TTnew( 'AbsencePolicyListFactory' );
				$aplf->getByCompanyId( $this->getUserObject()->getCompany() );
				if ( $aplf->getRecordCount() > 0 ) {
					foreach( $aplf as $ap_obj ) {
						$pay_code_obj = $ap_obj->getPayCodeObject();
						if ( is_object( $pay_code_obj ) AND in_array( $pay_code_obj->getType(), array(10,12) ) ) {
							$absence_policy_data['pay_code:'.$pay_code_obj->getId()] = $pay_code_obj;
						}
					}
				}
				unset($aplf, $ap_obj, $pay_code_obj);

				$export_column_map = array(
										'department' => NULL,
										'employee_number' => NULL,
										'from_date' => 'date_time_stamp',
										'to_date' => 'date_time_stamp',
										'gl_account' => NULL,
										//'absence_flag' => NULL,
										'pay_code' => 'hour_code',
										'quantity' => 'hours',
										//'unit_type' => NULL,
										//'note' => NULL,
									);

				if ( isset($setup_data['va_munis']['department']) AND !isset($setup_data['va_munis']['department_value']) ) {
					//$config['columns'][] = $setup_data['va_munis']['export_columns'][] = $setup_data['va_munis']['department'];
					$config['columns'][] = $config['group'][] = $export_column_map['department'] = $setup_data['va_munis']['department'];
					$config['sort'][] = array( $setup_data['va_munis']['department'] => 'asc' );
				}
				if ( isset($setup_data['va_munis']['gl_account']) AND !isset($setup_data['va_munis']['gl_account_value']) ) {
					//$config['columns'][] = $setup_data['va_munis']['export_columns'][] = $setup_data['va_munis']['gl_account'];
					$config['columns'][] = $config['group'][] = $export_column_map['gl_account'] = $setup_data['va_munis']['gl_account'];
					$config['sort'][] = array( $setup_data['va_munis']['gl_account'] => 'asc' );
				}
				if ( isset($setup_data['va_munis']['employee_number']) AND !isset($setup_data['va_munis']['employee_number_value']) ) {
					//$config['columns'][] = $setup_data['va_munis']['export_columns'][] = $setup_data['va_munis']['employee_number'];
					$config['columns'][] = $config['group'][] = $export_column_map['employee_number'] = $setup_data['va_munis']['employee_number'];
					$config['sort'][] = array( $setup_data['va_munis']['employee_number'] => 'asc' );
				}
				//Loop through columns for each time category so we can use those columns to
				foreach( $setup_data['va_munis']['columns'] as $column_id => $column_data ) {
					if ( $column_data['hour_column'] != 0 ) {
						if ( array_search( Misc::trimSortPrefix($column_data['hour_column']), $config['columns'] ) === FALSE ) {
							$config['columns'][] = $config['group'][] = Misc::trimSortPrefix($column_data['hour_column']);
							//$config['sort'][] = array( Misc::trimSortPrefix($column_data['hour_column']) => 'asc' );
						}
					}
				}

				$config['columns'][] = 'date_time_stamp';
				$config['sort'][] = array( 'date_time_stamp' => 'asc');
				$config['group'] = $config['columns'];
				$config['columns'] = array_merge( $config['columns'], array_keys($setup_data['va_munis']['columns']) );

				Debug::Arr($config, 'Job Detail Report Config: ', __FILE__, __LINE__, __METHOD__, 10);

				//Get job data...
				if ( is_object( $this->getUserObject() ) AND is_object( $this->getUserObject()->getCompanyObject() ) AND $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
					Debug::Text('Using Job Detail Report...', __FILE__, __LINE__, __METHOD__, 10);
					$jar = TTNew('JobDetailReport');
				} else {
					Debug::Text('Using TimeSheet Detail Report...', __FILE__, __LINE__, __METHOD__, 10);
					$jar = TTNew('TimesheetDetailReport');
				}
				$jar->setAMFMessageID( $this->getAMFMessageID() );
				$jar->setUserObject( $this->getUserObject() );
				$jar->setPermissionObject( $this->getPermissionObject() );
				$jar->setConfig( $config );
				$jar->setFilterConfig( $this->getFilterConfig() );
				$jar->setCustomFilterConfig( $this->getCustomFilterConfig() );
				$jar->setCustomColumnConfig( array_merge( (array)$this->getCustomFilterConfig(), (array)$this->getCustomColumnConfig() ) );
				$jar->setSortConfig( $config['sort'] );
//				$jar->_getData();
//				$jar->_preProcess();
//				$jar->group();
//				$jar->sort();
				$jar->_getData();
				$jar->_preProcess();
				$jar->currencyConvertToBase();
				//$jar->calculateCustomColumns( 10 ); //Selections (these are pre-group)
				//$jar->calculateCustomColumns( 20 ); //Pre-Group
				$jar->calculateCustomColumnFilters( 21 ); //Pre-Group
				//$jar->group();
				//$jar->calculateCustomColumns( 21 ); //Post-Group: things like round() functions normally need to be done post-group, otherwise they are rounding already rounded values.
				$jar->calculateCustomColumnFilters( 31 );
				$jar->sort();

				//$columns = Misc::trimSortPrefix( $jar->getOptions('columns') );

				$rows = $jar->data;
				//Debug::Arr($rows, 'Raw Rows: ', __FILE__, __LINE__, __METHOD__, 10);

				$file_name = strtolower(trim($setup_data['export_type'])).'_'.date('Y_m_d').'.prn'; //Change .prn once done.

				//If this needs to be customized, they can just export any regular report. This could probably be removed completely except for the Hour Code mapping...
				$setup_data['va_munis']['columns'] = Misc::trimSortPrefix( $setup_data['va_munis']['columns'] );

				$i = 0;
				$hour_code_map = array();
				foreach($rows as $row) {
					//Combine all hours from the same code together.
					foreach( $setup_data['va_munis']['columns'] as $column_id => $column_data ) {
						if ( $column_data['hour_column'] != 0 ) {
							$hour_code = $row[Misc::trimSortPrefix($column_data['hour_column'])];
						} else {
							$hour_code = trim($column_data['hour_code']);
						}

						$hour_code_map[$hour_code][$column_id] = NULL;
						if ( isset( $row[$column_id.'_time'] ) AND $hour_code != '' ) {
							if ( !isset($tmp_hour_codes[$hour_code]) ) {
								$tmp_hour_codes[$hour_code] = 0;
							}
							$tmp_hour_codes[$hour_code] = bcadd( $tmp_hour_codes[$hour_code], $row[$column_id.'_time'] ); //Use seconds for math here.
						}
					}

					if ( isset($tmp_hour_codes) ) {
						foreach($tmp_hour_codes as $hour_code => $hours ) {
							foreach( $export_column_map as $export_column ) {
								if ( $export_column != '' ) {
									//Due to a bug in PHP v5.3, isset($row[$export_column]['display']) always returns TRUE, so we need to add array_key_exists() check as well.
									$tmp_rows[$i][$export_column] = ( isset($row[$export_column]) ) ? ( is_array($row[$export_column]) AND array_key_exists('display', $row[$export_column]) ) ? $row[$export_column]['display'] : $row[$export_column] : NULL;
								}
								$tmp_rows[$i]['hour_code'] = $hour_code;
								$tmp_rows[$i]['hours'] = TTDate::getTimeUnit( $hours, 20 );
							}
							$i++;
						}
						unset($tmp_hour_codes, $hour_code, $hours);
					}
				}
				//Debug::Arr($tmp_rows, 'Tmp Rows: ', __FILE__, __LINE__, __METHOD__, 10);

				if ( isset( $tmp_rows) ) {

					$data = '';
					foreach( $tmp_rows as $tmp_row ) {
						$data .= str_pad( ( isset($tmp_row[$export_column_map['department']]) ) ? $tmp_row[$export_column_map['department']] : $setup_data['va_munis']['department_value'], 5, 0, STR_PAD_LEFT);		//5 digits left padded
						//$data .= ', ';
						$data .= str_pad( ( isset($tmp_row[$export_column_map['employee_number']]) ) ? $tmp_row[$export_column_map['employee_number']] : '', 9, '0', STR_PAD_LEFT);		//9 digits left padded
						//$data .= ', ';
						$data .= str_pad( date('mdY', $tmp_row[$export_column_map['from_date']] ), 10, ' ', STR_PAD_LEFT);		//10 digits right space padded
						//$data .= ', ';
						$data .= str_pad( date('mdY', $tmp_row[$export_column_map['to_date']] ), 10, ' ', STR_PAD_LEFT);		//10 digits right space padded
						//$data .= ', ';
						$data .= str_pad( ( isset($tmp_row[$export_column_map['gl_account']]) ) ? $tmp_row[$export_column_map['gl_account']] : '', 55, ' ', STR_PAD_RIGHT);		//55 digits right space padded
						//$data .= ', ';

						//Check to see if the these hours were made up any absence time.
						$is_absence = FALSE;
						foreach( $hour_code_map[$tmp_row['hour_code']] as $original_column => $tmp ) {
							//Only mark paid absences as is_absence=TRUE
							if ( isset($absence_policy_data[$original_column]) AND in_array( $absence_policy_data[$original_column]->getType(), array(10, 12) ) ) {
								$is_absence = TRUE;
								break;
							}
						}
						unset($tmp); //code standards
						$data .= ( $is_absence == TRUE ) ? 'Y' : 'N';

						//$data .= ', ';
						$data .= str_pad( $tmp_row['hour_code'], 3, '0', STR_PAD_LEFT);		//5 digits left padded
						//$data .= ', ';
						$data .= str_pad( $tmp_row['hours'], 9, '0', STR_PAD_LEFT);		//9 digits left padded
						//$data .= ', ';
						$data .= 'H';
						//$data .= ', ';
						$data .= str_pad( '', 20, ' ', STR_PAD_RIGHT);		//20 digits right space padded
						$data .= "\r\n";
					}
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row, $absence_policy_data);
				break;
			case 'accero': //Accero export format.
				/*
				 	Field #	Field Name	No Of Positions	Start	End	Picture	Field Definition
				  	1	T1 Card Code	2	1	2	X(2)	Code that defines to the pay calculation program how to process the time card - "12" if only hours are going to be included on the record, the system will calc the pay;"18" if hours and an amount or just an amount (earning that does not have hours associated with it like a bonus) are going to be included on the record, system will not calc pay but pay whats reported; "16" to dock pay and only hours are to be included on the record, system will calc the negative pay; "17" to doc pay and hours and an amount or just an amount are to be included in the record, system will not calc the negative pay.
				 	2	Employee Number	10	3	12	X(10)	Employee Number
				 	3	Regular Hours	6	13	18	9999V99	Regular (HED 001) Hours
					4	Regular Amount or Rate Of Pay	7	19	25	99999V99 (amt) 999V9999(rate)	Can be calculated amount or can be used as an override rate.  If only passing hours, and this is left blank, then current rate of pay in Cyborg will be used to calculate pay.
					5	Overtime Code	1	26	26	X(1)	OT calculation Method See OT Codes For Definitions
					6	Overtime Hours	4	27	30	99V99	OT (HED 003) Hours
					7	OT Amount or Rate Of Pay	6	31	36	9999V99 (amt) 99V9999(rate)	Can be calculated OT amount or can be used as an OT override rate.  If only passing hours, and this is left blank, then current rate of pay in Cyborg will be used to calculate pay using the OT Code in the calculation.
					8	HED Override No	3	37	39	999	Earnings Code (HED) if hours or amount reported are not to be charged to Regular Pay (HED 001) or Overtime Pay (HED 003).  Can be used with either the regular hours/amount fields or OT but not both.
					9	Period Date	4	40	43	MMDD	Period End MM and DD
					10	Tax Type	1	44	44	N/A
					11	Local Code	6	45	50	N/A
					12	State Code	2	51	52	N/A
					13	Division Override Code	4	53	56	Division Charge Out	If pay is to be charged to another Department
					14	Department Override Code	4	57	60	Department Charge Out	If pay is to be charged to another Location
					15	Control 5 Code	4	61	64	N/A
					16	Control 6 Code	4	65	68	N/A
					17	GL Account Override Code	10	69	78	GL Account Charge Out	If pay is to be charged to another GL Account
					18	Shift Override	1	79	79	Shirt Override
					19	Deduction Cycle Override	1	80	80	Deduction Override
				 */

				$export_column_map = array(
										't1_card_code' => 'T1 Card Code',
										'employee_number' => 'Employee Number',
										'regular_time' => 'Reg Hours',
										'regular_amount' => 'Reg Amount',
										'overtime_code' => 'OT Code',
										'overtime' => 'OT Hours',
										'overtime_amount' => 'OT Amount',
										'hour_code' => 'HED Override No',
										'date_week_end' => 'Week End Date',
										'tax_type' => 'Tax Type',
										'local_code' => 'Local Code',
										'state_code' => 'State Code',
										'division_code' => 'Division Code',
										'temp_dept' => 'Department Override',
										);


				ksort($setup_data['accero']['columns']);
				$setup_data['accero']['columns'] = Misc::trimSortPrefix( $setup_data['accero']['columns'] );

				if ( !isset($setup_data['accero']['temp_dept_value']) ) {
					$setup_data['accero']['temp_dept_value'] = NULL;
				}

				$temp_dept_column = Misc::trimSortPrefix( $setup_data['accero']['temp_dept'] );

				$data = '';
				foreach($rows as $row) {
					$static_columns = array(
										't1_card_code' => 12,
										'employee_number' => str_pad( $row['employee_number'], 10, ' ', STR_PAD_RIGHT), //accero employee numbers should always be 6 digits.
										'regular_time' => str_pad( NULL, 6, ' ', STR_PAD_RIGHT),
										'regular_amount' => str_pad( NULL, 7, ' ', STR_PAD_RIGHT),
										'overtime_code' => str_pad( NULL, 1, ' ', STR_PAD_RIGHT),
										'overtime' => str_pad( NULL, 4, ' ', STR_PAD_RIGHT),
										'overtime_amount' => str_pad( NULL, 6, ' ', STR_PAD_RIGHT),
										'hour_code' => str_pad( NULL, 3, ' ', STR_PAD_RIGHT),
										'date_week_end' => str_pad( NULL, 4, ' ', STR_PAD_RIGHT),
										'tax_type' => str_pad( NULL, 1, ' ', STR_PAD_RIGHT),
										'local_code' => str_pad( NULL, 6, ' ', STR_PAD_RIGHT),
										'state_code' => str_pad( NULL, 2, ' ', STR_PAD_RIGHT),
										'division_code' => str_pad( NULL, 4, ' ', STR_PAD_RIGHT),
										'temp_dept' => str_pad( NULL, 4, ' ', STR_PAD_RIGHT),
										);

					foreach( $setup_data['accero']['columns'] as $column_id => $column_data ) {
						$column_data = Misc::trimSortPrefix( $column_data, TRUE );
						if ( isset( $row[$column_id.'_time'] ) AND $column_data['hour_column'] != '0' ) {
							//Debug::Text('Accero Column ID: '. $column_id .' Hour Column: '. $column_data['hour_column'] .' Code: '. $column_data['hour_code'], __FILE__, __LINE__, __METHOD__, 10);
							foreach( $export_column_map as $export_column_id => $export_column_name ) {
								//Debug::Arr($row, 'Row: Column ID: '. $column_id .' Export Column ID: '. $export_column_id .' Name: '. $export_column_name, __FILE__, __LINE__, __METHOD__, 10);
								if ( ( $column_data['hour_column'] == $export_column_id OR $column_data['hour_column'].'_code' == $export_column_id )
										AND !in_array( $export_column_id, array('t1_card_code', 'employee_number', 'regular_amount', 'overtime_code', 'tax_type', 'local_code', 'state_code', 'division_code')) ) {
									$tmp_row['date_week_end'] = date('md', strtotime( $row['date_week_end'] ) );
									$tmp_row['hour_code'] = str_pad( $column_data['hour_code'], 3, '0', STR_PAD_LEFT);
									$tmp_row['temp_dept'] = str_pad( ( isset($row[$temp_dept_column]) ) ? $row[$temp_dept_column] : $setup_data['accero']['temp_dept_value'], 4, ' ', STR_PAD_RIGHT);
									if ( $export_column_id == 'regular_time' ) {
										$tmp_row[$export_column_id] = str_pad( str_replace('.', '', TTDate::getTimeUnit( $row[$column_id.'_time'], 20 ) ), 6, 0, STR_PAD_LEFT);
									} else {
										$tmp_row[$export_column_id] = str_pad( str_replace('.', '', TTDate::getTimeUnit( $row[$column_id.'_time'], 20 ) ), 4, 0, STR_PAD_LEFT);
									}

									//Break out every column onto its own row, that way its easier to handle multiple columns of the same type.
									$tmp_rows[] = array_merge( $static_columns, $tmp_row );
									unset($tmp_row);
								}
							}
						}
					}
				}

				//$file_name = 'accero_payroll_export.csv';
				$file_name = 'accero_payroll_export.txt';
				if ( isset( $tmp_rows ) ) {
					//$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE );

					$data = '';
					foreach( $tmp_rows as $tmp_row ) {
						$data .= implode($tmp_row, '')."\r\n";
					}
				}
				unset($tmp_rows, $tmp_row, $export_column_map, $column_id, $column_data, $rows, $row);
				break;
			case 'compupay': //Compupay Export format.
				$file_name = strtolower(trim($setup_data['export_type'])).'_'.date('Y_m_d').'.csv';

				ksort($setup_data['compupay']['columns']);
				$setup_data['compupay']['columns'] = Misc::trimSortPrefix( $setup_data['compupay']['columns'] );

				$export_column_map = array(
											'employee_number' => 'Employee Number',
											'hour_code_type' => 'DET',
											'hour_code' => 'DET Code',
											'value' => 'Hours' );

				foreach($rows as $row) {
					foreach( $setup_data['compupay']['columns'] as $column_id => $column_data ) {

						if ( isset( $row[$column_id.'_time'] ) AND trim($column_data['hour_code']) != '' ) {
							//Debug::Arr($column_data, 'Output2', __FILE__, __LINE__, __METHOD__, 10);
							$tmp_rows[] = array(
												//'pay_period_end_date' => date('m/d/Y', $row['pay_period_end_date']),
												'employee_number' => $row['employee_number'],
												//'last_name' => $row['last_name'],
												//'first_name' => $row['first_name'],
												'hour_code_type' => 'E',
												'hour_code' => trim($column_data['hour_code']),
												'value' => TTDate::getTimeUnit( $row[$column_id.'_time'], 20 ),
												);
						}
					}
				}

				if ( isset( $tmp_rows) ) {
					$data = str_replace('"', '', Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE ) );
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);
				break;
			case 'sage_50': //sage_50 Export format.
				//It appears that Sage 50 can't import overtime, unless its handled through item numbers?
				//https://support.na.sage.com/selfservice/viewContent.do?externalId=41370&sliceId=1

				$file_name = strtolower(trim($setup_data['export_type'])).'_'.date('Y_m_d').'.imp';

				ksort($setup_data['sage_50']['columns']);
				$setup_data['sage_50']['columns'] = Misc::trimSortPrefix( $setup_data['sage_50']['columns'] );

				if ( !isset($setup_data['sage_50']['customer_name']) ) {
					$setup_data['sage_50']['customer_name'] = NULL;
				}

				if ( !isset($setup_data['sage_50']['customer_name_value']) ) {
					$setup_data['sage_50']['customer_name_value'] = NULL;
				}

				foreach($rows as $row) {
					foreach( $setup_data['sage_50']['columns'] as $column_id => $column_data ) {

						if ( isset( $row[$column_id.'_time'] ) AND trim($column_data['hour_code']) != '' ) {
							//Debug::Arr($column_data, 'Output2', __FILE__, __LINE__, __METHOD__, 10);

							$tmp_rows[$row['employee_number']][] = array(
												'pay_period_end_date' => date('m/d/Y', $row['pay_period_end_date']),
												'employee_number' => $row['employee_number'],
												'last_name' => $row['last_name'],
												'first_name' => $row['first_name'],
												'customer_name' => ( isset($row[$setup_data['sage_50']['customer_name']]) ) ? $row[$setup_data['sage_50']['customer_name']] : $setup_data['sage_50']['customer_name_value'],
												'hour_code' => trim($column_data['hour_code']),
												'value' => $row[$column_id.'_time'],
												);
						}
					}
				}

				$data = '';
				if ( isset( $tmp_rows ) ) {
					foreach( $tmp_rows as $detail_records ) {
						$data .= '<Timeslip>'."\r\n";
						$total_detail_records = count($detail_records);
						if ( $total_detail_records > 0 ) {
							$data .= '"'. substr( $row['last_name'].', '. $row['first_name'], 0, 52) .'"'."\r\n";
							$data .= '"'. $total_detail_records .'","","'. date('m-d-Y', $row['pay_period_end_date']).'"'."\r\n";
							foreach( $detail_records as $detail_record ) {
								$data .= '"'. $detail_record['customer_name'] .'","'. trim($column_data['hour_code']) .'","'. TTDate::getTimeUnit( $detail_record['value'], 12 ) .'"'."\r\n";
							}
						}
						$data .= '</Timeslip>'."\r\n";
					}
				}

				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row, $total_detail_records);
				break;
			case 'cms_pbj': //CMS PBJ export format: http://www.cms.gov/Medicare/Quality-Initiatives-Patient-Assessment-Instruments/NursingHomeQualityInits/Staffing-Data-Submission-PBJ.html
				unset($rows); //Ignore any existing timesheet summary data, we will be using our own job data below.
				Debug::Arr($setup_data, 'CMS PBJ Setup Data: ', __FILE__, __LINE__, __METHOD__, 10);

				if ( isset($setup_data[$setup_data['export_type']]['facility_code']) AND strpos( $setup_data[$setup_data['export_type']]['facility_code'], '_' ) !== FALSE ) {
					$config['columns'][] = $facility_code_column = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['facility_code'] );
				}
				if ( isset($setup_data[$setup_data['export_type']]['state_code']) AND strpos( $setup_data[$setup_data['export_type']]['state_code'], '_' ) !== FALSE ) {
					$config['columns'][] = $state_code_column = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['state_code'] );
				}
				if ( isset($setup_data[$setup_data['export_type']]['pay_type_code']) AND strpos( $setup_data[$setup_data['export_type']]['pay_type_code'], '_' ) !== FALSE ) {
					$config['columns'][] = $pay_type_code_column = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['pay_type_code'] );
				}
				if ( isset($setup_data[$setup_data['export_type']]['job_title_code']) AND strpos( $setup_data[$setup_data['export_type']]['job_title_code'], '_' ) !== FALSE ) {
					$config['columns'][] = $job_title_code_column = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['job_title_code'] );
				}
				$config['columns'][] = 'employee_number';
				$config['columns'][] = 'hire-date_stamp';
				$config['columns'][] = 'termination-date_stamp';
				$config['columns'][] = 'date_stamp';

				if ( isset($setup_data[$setup_data['export_type']]['facility_code']) AND strpos( $setup_data[$setup_data['export_type']]['facility_code'], '_' ) !== FALSE ) {
					$config['group'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['facility_code'] );
				}
				if ( isset($setup_data[$setup_data['export_type']]['state_code']) AND strpos( $setup_data[$setup_data['export_type']]['state_code'], '_' ) !== FALSE ) {
					$config['group'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['state_code'] );
				}
				if ( isset($setup_data[$setup_data['export_type']]['pay_type_code']) AND strpos( $setup_data[$setup_data['export_type']]['pay_type_code'], '_' ) !== FALSE ) {
					$config['group'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['pay_type_code'] );
				}
				if ( isset($setup_data[$setup_data['export_type']]['job_title_code']) AND strpos( $setup_data[$setup_data['export_type']]['job_title_code'], '_' ) !== FALSE ) {
					$config['group'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['job_title_code'] );
				}
				$config['group'][] = 'employee_number';
				$config['group'][] = 'hire-date_stamp';
				$config['group'][] = 'termination-date_stamp';
				$config['group'][] = 'date_stamp';


				if ( isset($setup_data[$setup_data['export_type']]['facility_code']) AND strpos( $setup_data[$setup_data['export_type']]['facility_code'], '_' ) !== FALSE ) {
					$config['sort'][] = array( Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['facility_code'] ) => 'asc' );
				}
				if ( isset($setup_data[$setup_data['export_type']]['state_code']) AND strpos( $setup_data[$setup_data['export_type']]['state_code'], '_' ) !== FALSE ) {
					$config['sort'][] = array( Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['state_code'] ) => 'asc' );
				}
				if ( isset($setup_data[$setup_data['export_type']]['pay_type_code']) AND strpos( $setup_data[$setup_data['export_type']]['pay_type_code'], '_' ) !== FALSE ) {
					$config['sort'][] = array( Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['pay_type_code'] ) => 'asc' );
				}
				if ( isset($setup_data[$setup_data['export_type']]['job_title_code']) AND strpos( $setup_data[$setup_data['export_type']]['job_title_code'], '_' ) !== FALSE ) {
					$config['sort'][] = array( Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['job_title_code'] ) => 'asc' );
				}
				$config['sort'][] = array('employee_number' => 'asc' );
				$config['sort'][] = array('hire-date_stamp' => 'asc' );
				$config['sort'][] = array('termination-date_stamp' => 'asc' );
				$config['sort'][] = array('date_stamp' => 'asc' );
				Debug::Arr($config, 'CMS PBJ Config Data: ', __FILE__, __LINE__, __METHOD__, 10);

				if ( is_object( $this->getUserObject() ) AND is_object( $this->getUserObject()->getCompanyObject() ) AND $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
					Debug::Text('Using Job Detail Report...', __FILE__, __LINE__, __METHOD__, 10);
					$jar = TTNew('JobDetailReport');
				} else {
					Debug::Text('Using TimeSheet Detail Report...', __FILE__, __LINE__, __METHOD__, 10);
					$jar = TTNew('TimesheetDetailReport');
				}
				$jar->setAMFMessageID( $this->getAMFMessageID() );
				$jar->setUserObject( $this->getUserObject() );
				$jar->setPermissionObject( $this->getPermissionObject() );
				$jar->setConfig( $config );
				$jar->setFilterConfig( $this->getFilterConfig() );
				$jar->setCustomFilterConfig( $this->getCustomFilterConfig() );
				$jar->setCustomColumnConfig( array_merge( (array)$this->getCustomFilterConfig(), (array)$this->getCustomColumnConfig() ) );
				if ( isset($config['sort']) ) {
					$jar->setSortConfig( $config['sort'] );
				}
				$jar->_getData();
				$jar->_preProcess();
				$jar->currencyConvertToBase();
				//$jar->calculateCustomColumns( 10 ); //Selections (these are pre-group)
				//$jar->calculateCustomColumns( 20 ); //Pre-Group
				$jar->calculateCustomColumnFilters( 21 ); //Pre-Group
				$jar->group();
				//$jar->calculateCustomColumns( 21 ); //Post-Group: things like round() functions normally need to be done post-group, otherwise they are rounding already rounded values.
				$jar->calculateCustomColumnFilters( 31 );
				$jar->sort();
				//$jar->_postProcess( 'csv' ); //Minor post-processing.
				$rows = $jar->data;
				//Debug::Arr($rows, 'Raw Rows: ', __FILE__, __LINE__, __METHOD__, 10);

				if ( !isset($setup_data[$setup_data['export_type']]['facility_code_value']) ) {
					$setup_data[$setup_data['export_type']]['facility_code_value'] = NULL;
				}
				if ( !isset($setup_data[$setup_data['export_type']]['state_code_value']) ) {
					$setup_data[$setup_data['export_type']]['state_code_value'] = NULL;
				}
				if ( !isset($setup_data[$setup_data['export_type']]['pay_type_code_value']) ) {
					$setup_data[$setup_data['export_type']]['pay_type_code_value'] = NULL;
				}
				if ( !isset($setup_data[$setup_data['export_type']]['job_title_code_value']) ) {
					$setup_data[$setup_data['export_type']]['job_title_code_value'] = NULL;
				}

				foreach($rows as $key => $row) {
					//Combine all hours from the same code together.
					foreach( $setup_data[$setup_data['export_type']]['columns'] as $column_id => $column_data ) {
						if ( isset( $row[$column_id.'_time'] ) AND $column_data['hour_column'] == 1 ) {
							if ( !isset($tmp_hour_codes['pbj_hours']) ) {
								$tmp_hour_codes['pbj_hours'] = 0;
							}
							$tmp_hour_codes['pbj_hours'] = bcadd( $tmp_hour_codes['pbj_hours'], $row[$column_id.'_time'] ); //Use seconds for math here.
						}
					}

					if ( isset($tmp_hour_codes) ) {
						$rows[$key]['pbj_hours'] = $tmp_hour_codes['pbj_hours'];
						unset($tmp_hour_codes);
					} else {
						$rows[$key]['pbj_hours'] = FALSE;
					}

					$rows[$key]['pbj_job_title_code'] = ( isset($job_title_code_column) AND isset($row[$job_title_code_column]) ) ? $row[$job_title_code_column] : $setup_data[$setup_data['export_type']]['job_title_code_value'];
					$rows[$key]['pbj_pay_type_code'] = ( isset($pay_type_code_column) AND isset($row[$pay_type_code_column]) ) ? $row[$pay_type_code_column] : $setup_data[$setup_data['export_type']]['pay_type_code_value'];
				}
				//Debug::Arr($rows, 'Calculated Rows: ', __FILE__, __LINE__, __METHOD__, 10);

				require_once( Environment::getBasePath() .'/classes/GovernmentForms/GovernmentForms.class.php');
				$gf = new GovernmentForms();
				$pbj = $gf->getFormObject('CMS_PBJ', 'US');

				foreach($rows as $key => $row) {
					if ( $row['pbj_hours'] > 0 ) {
						$facility_code = ( isset( $facility_code_column ) AND isset( $row[$facility_code_column] ) ) ? $row[$facility_code_column] : $setup_data[$setup_data['export_type']]['facility_code_value'];
						$state_code = ( isset( $state_code_column ) AND isset( $row[$state_code_column] ) ) ? $row[$state_code_column] : $setup_data[$setup_data['export_type']]['state_code_value'];

						//Debug::Text('Add Record: '. $row['employee_number'] .' Date: '. $row['date_stamp'], __FILE__, __LINE__, __METHOD__, 10);

						if ( !isset( $pbj->date ) ) {
							//Debug::Text('Facility Code: '. $facility_code .' State: '. $state_code, __FILE__, __LINE__, __METHOD__, 10);
							$pbj->facility_code = $facility_code;
							$pbj->state_code = $state_code;
							$pbj->date = strtotime( $row['date_stamp'] ); //This is ISO Date format.
						}

						//Make sure these are always set, so GovernmentForms->addRecords() overwrites them for every row.
						$row['date_stamp'] = ( isset( $row['date_stamp'] ) ) ? strtotime( $row['date_stamp'] ) : FALSE;
						$row['hire-date_stamp'] = ( isset( $row['hire-date_stamp'] ) ) ? strtotime( $row['hire-date_stamp'] ) : FALSE;
						$row['termination-date_stamp'] = ( isset( $row['termination-date_stamp'] ) ) ? strtotime( $row['termination-date_stamp'] ) : FALSE;

						$pbj->addRecord( $row );
					}
					//else {
					//	Debug::Text('  Skipping row with 0 hours: Key: '. $key .' Employee Number: '. $row['employee_number'] .' Date: '. $row['date_stamp'], __FILE__, __LINE__, __METHOD__, 10);
					//}
				}
				$gf->addForm( $pbj );

				$file_name = 'pbj_'.date('Y_m_d').'.xml';
				$mime_type = 'applications/octet-stream'; //Force file to download.
				$data = $gf->output( 'XML' );
				break;
			case 'meditech': //Meditech
				unset($rows); //Ignore any existing timesheet summary data, we will be using our own job data below.
				//Debug::Arr($setup_data, 'Meditech Setup Data: ', __FILE__, __LINE__, __METHOD__, 10);

				/**
				 * @param $value
				 * @param int $pad
				 * @return string
				 */
				function meditechNumericFormat( $value, $pad = 5 ) {
					$negative = FALSE;
					if ( $value < 0 ) {
						$negative = TRUE;
						$pad--;
					}

					//Replace decimal and negative ('-') with nothing. Can't use abs() here as it strips trailing zeros.
					$retval = str_pad( str_replace(array('.','-'), '', $value ), $pad, 0, STR_PAD_LEFT);
					if ( $negative == TRUE ) {
						$retval = '-'. substr($retval, 1);
					}

					//Debug::Text('Input: \''. $value .'\' Retval: \''. $retval .'\'', __FILE__, __LINE__, __METHOD__, 10);
					return $retval;
				}

				/**
				 * @param int $date_stamp EPOCH
				 * @param int $first_pay_period_start_date EPOCH
				 * @param int $start_week_day_id
				 * @return bool
				 */
				function isSecondBiWeeklyWeek( $date_stamp, $first_pay_period_start_date, $start_week_day_id = 0 ) {
					//This must be based on an "anchor" date, or first_pay_period_start_date, otherwise when there are 53 weeks in a year
					//it will throw off the odd/even calculation.
					//FIXME: What happens if they transition from one pay period schedule to another, and the first pay period is a shorter pay period.
					//  For example from Semi-Monthly to Bi-Weekly, and the first BiWeekly PP is 01-Jan-2014 to 05-Jan-215, then it continues as regular biweekly PPs after that.
					//  I think for now they will just need to modify their pay period dates so the pay period always starts on the 1st week, not the 2nd week.

					if ( $first_pay_period_start_date == '' ) {
						$first_pay_period_start_date = 788947200; //Sun, 01-Jan-1995... Used to calculate weeks from.
						//Debug::text('ERROR: First PP Start Date is invalid, use reference date instead!', __FILE__, __LINE__, __METHOD__, 10);
					}

					//Based on first pay period start date, get beginning week epoch.
					$reference_date = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( $first_pay_period_start_date, $start_week_day_id ) );

					//Figure out how many days we are from the first pay period start date, then figure out the number of weeks, to determine odd/even essentially.
					$days_diff = round( TTDate::getDays( ( TTDate::getMiddleDayEpoch( $date_stamp ) - $reference_date ) ) );
					$weekly_period_diff = ( $days_diff / 7 );

					$retval = ( $weekly_period_diff % 2 );

					//Debug::text(' Date: '. TTDate::getDate('DATE', $date_stamp ) .' First PP Start Date: '. TTDate::getDate('DATE+TIME', $first_pay_period_start_date ).' Days: '. $days_diff .' Weeks: '. $weekly_period_diff .' Retval: '. (int)$retval, __FILE__, __LINE__, __METHOD__, 10);

					if ( $retval == 1 ) {
						return TRUE;
					}

					return FALSE;
				}

				if ( isset($setup_data[$setup_data['export_type']]['employee_number']) ) {
					$config['columns'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['employee_number'] );
				} else {
					$config['columns'][] = 'employee_number';
				}
				if ( isset($setup_data[$setup_data['export_type']]['department']) AND strpos( $setup_data[$setup_data['export_type']]['department'], '_' ) !== FALSE ) {
					$config['columns'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['department'] );
				}
				if ( isset($setup_data[$setup_data['export_type']]['job_code']) AND strpos( $setup_data[$setup_data['export_type']]['job_code'], '_' ) !== FALSE ) {
					$config['columns'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['job_code'] );
				}
				$config['columns'][] = 'pay_period_end_date';
				$config['columns'][] = 'date_week_start';


				if ( isset($setup_data[$setup_data['export_type']]['employee_number']) ) {
					$config['group'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['employee_number'] );
				} else {
					$config['group'][] = 'employee_number';
				}
				if ( isset($setup_data[$setup_data['export_type']]['department']) AND strpos( $setup_data[$setup_data['export_type']]['department'], '_' ) !== FALSE ) {
					$config['group'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['department'] );
				}
				if ( isset($setup_data[$setup_data['export_type']]['job_code']) AND strpos( $setup_data[$setup_data['export_type']]['job_code'], '_' ) !== FALSE ) {
					$config['group'][] = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['job_code'] );
				}
				$config['group'][] = 'pay_period_end_date';
				$config['group'][] = 'date_week_start';

				$dynamic_column_names = array_keys( Misc::trimSortPrefix( $this->getOptions('dynamic_columns') ) );
				//Force group by hourly_rates, so if there are multiple hourly rates with the same pay code its still split out.
				foreach( $dynamic_column_names as $dynamic_column_name ) {
					if ( substr( $dynamic_column_name, -12 ) == '_hourly_rate' ) { //Ends with
						$config['group'][] = $dynamic_column_name;
					}
				}
				unset($dynamic_column_names, $dynamic_column_name);


				if ( isset($setup_data[$setup_data['export_type']]['employee_number']) ) {
					$config['sort'][] = array( Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['employee_number'] ) => 'asc' );
				} else {
					$config['sort'][] = array( 'employee_number' => 'asc' );
				}
				if ( isset($setup_data[$setup_data['export_type']]['department']) AND strpos( $setup_data[$setup_data['export_type']]['department'], '_' ) !== FALSE ) {
					$config['sort'][] = array( Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['department'] ) => 'asc' );
				}
				if ( isset($setup_data[$setup_data['export_type']]['job_code']) AND strpos( $setup_data[$setup_data['export_type']]['job_code'], '_' ) !== FALSE ) {
					$config['sort'][] = array( Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['job_code'] ) => 'asc' );
				}
				$config['sort'][] = array( 'pay_period_end_date' => 'asc' );
				$config['sort'][] = array( 'date_week_start' => 'asc' );

				Debug::Arr($config, 'MediTech Config Data: ', __FILE__, __LINE__, __METHOD__, 10);

				if ( is_object( $this->getUserObject() ) AND is_object( $this->getUserObject()->getCompanyObject() ) AND $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
					Debug::Text('Using Job Detail Report...', __FILE__, __LINE__, __METHOD__, 10);
					$jar = TTNew('JobDetailReport');
				} else {
					Debug::Text('Using TimeSheet Detail Report...', __FILE__, __LINE__, __METHOD__, 10);
					$jar = TTNew('TimesheetDetailReport');
				}
				$jar->setAMFMessageID( $this->getAMFMessageID() );
				$jar->setUserObject( $this->getUserObject() );
				$jar->setPermissionObject( $this->getPermissionObject() );
				$jar->setConfig( $config );
				$jar->setFilterConfig( $this->getFilterConfig() );
				$jar->setCustomFilterConfig( $this->getCustomFilterConfig() );
				$jar->setCustomColumnConfig( array_merge( (array)$this->getCustomFilterConfig(), (array)$this->getCustomColumnConfig() ) );
				if ( isset($config['sort']) ) {
					$jar->setSortConfig( $config['sort'] );
				}
				$jar->_getData();
				$jar->_preProcess();
				$jar->currencyConvertToBase();
				//$jar->calculateCustomColumns( 10 ); //Selections (these are pre-group)
				//$jar->calculateCustomColumns( 20 ); //Pre-Group
				$jar->calculateCustomColumnFilters( 30 ); //Pre-Group
				$jar->group();
				//$jar->calculateCustomColumns( 21 ); //Post-Group: things like round() functions normally need to be done post-group, otherwise they are rounding already rounded values.
				$jar->calculateCustomColumnFilters( 31 );
				$jar->sort();
				$jar->_postProcess( 'csv' ); //Minor post-processing.
				$rows = $jar->data;
				//Debug::Arr($rows, 'Raw Rows: ', __FILE__, __LINE__, __METHOD__, 10);

				$export_column_map = array(
						'record_type' => 'Record Type',
						'space_filler' => 'Filler',
						'employee_number' => 'Employee #',
						'earning_number' => 'Earning Number',
						'hours' => 'Hours',
						'shift' => '1',
						'department' => 'Department',
						'job_code' => 'Job Code',
						'base_rate' => 'Base Rate',
						'flat_amount' => 'Flat Amount',
						'week' => 'Week',
				);

				$employee_number_column = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['employee_number'] );
				$department_column = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['department'] );
				$job_code_column = Misc::trimSortPrefix( $setup_data[$setup_data['export_type']]['job_code'] );

				ksort($setup_data['meditech']['columns']);
				$setup_data['meditech']['columns'] = Misc::trimSortPrefix( $setup_data['meditech']['columns'] );

				$total_records = 0;
				$total_hours = 0;
				$pay_period_end_date = 0;
				foreach($rows as $row) {
					$pay_period_end_date = TTDate::parseDateTime( $row['pay_period_end_date'] );

					$static_columns = array(
							'record_type' => 2,
							'space_filler' => ' ',
							'employee_number' => str_pad( NULL, 14, ' ', STR_PAD_RIGHT),
							'earning_number' => str_pad( NULL, 8, ' ', STR_PAD_RIGHT),
							'hours' => str_pad( NULL, 7, 0, STR_PAD_LEFT),
							'shift' => '1',
							'department' => str_pad( NULL, 15, ' ', STR_PAD_RIGHT),
							'job_code' => str_pad( NULL, 10, ' ', STR_PAD_RIGHT),
							'base_rate' => str_pad( NULL, 5, 0, STR_PAD_LEFT),
							'flat_amount' => str_pad( NULL, 7, 0, STR_PAD_LEFT),
							'week' => '1',
					);

					foreach( $setup_data['meditech']['columns'] as $column_id => $column_data ) {

						if ( isset( $row[$column_id.'_time'] ) AND trim($column_data['hour_code']) != '' ) {
							$tmp_row['employee_number'] = str_pad( ( isset($row[$employee_number_column]) ) ? substr( $row[$employee_number_column], 0, 14) : NULL, 14, ' ', STR_PAD_RIGHT);

							$tmp_row['earning_number'] = str_pad( substr( $column_data['hour_code'], 0, 8 ), 8, ' ', STR_PAD_RIGHT);
							$tmp_row['hours'] = ( isset($row[$column_id.'_time']) ) ? meditechNumericFormat( number_format( TTDate::getHours( $row[$column_id.'_time'] ), 3, '.', ''), 7 ) : '0000000'; //Hours are to three decimal places.

							$tmp_row['department'] = str_pad( ( isset($row[$department_column]) ) ? substr( $row[$department_column], 0, 15) : ( ( isset($setup_data['meditech']['department_value']) ) ? $setup_data['meditech']['department_value'] : NULL ), 15, ' ', STR_PAD_RIGHT);
							$tmp_row['job_code'] = str_pad( ( isset($row[$job_code_column]) ) ? substr( $row[$job_code_column], 0, 10) : ( ( isset($setup_data['meditech']['job_code_value']) ) ? $setup_data['meditech']['job_code_value'] : NULL ), 10, ' ', STR_PAD_RIGHT);

							$tmp_row['base_rate'] = ( isset( $row[$column_id.'_hourly_rate'] ) ) ? meditechNumericFormat( number_format( $row[$column_id.'_hourly_rate'], 2, '.', ''), 5 ) : '00000'; //Rate is two decimal places.

							if ( isSecondBiWeeklyWeek( TTDate::parseDateTime( $row['date_week_start'] ), '', 0 ) == TRUE ) {
								$tmp_row['week'] = 2;
							} else {
								$tmp_row['week'] = 1;
							}

							//Break out every column onto its own row, that way its easier to handle multiple columns of the same type.
							$tmp_rows[] = array_merge( $static_columns, $tmp_row );

							$total_hours += isset($row[$column_id.'_time']) ? number_format( TTDate::getHours( $row[$column_id.'_time'] ), 3, '.', '' ) : 0; //Make sure we add up just to 3 decimal places.
							$total_records++;

							unset($tmp_row);
						}
					}
				}
				Debug::Text('Total Records: '. $total_records .' Hours: '. $total_hours, __FILE__, __LINE__, __METHOD__, 10);

				//$file_name = 'meditech_payroll_export.csv';
				$file_name = 'meditech_payroll_export.txt';
				if ( isset( $tmp_rows ) ) {
//					$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE );

					$data = '1 '. date('Ymd', time() ) . date('His', time() ) . date('Ymd', $pay_period_end_date ) . str_pad( substr( trim($setup_data[$setup_data['export_type']]['payroll']), 0, 8), 8, ' ', STR_PAD_RIGHT) ."\r\n";
					foreach( $tmp_rows as $tmp_row ) {
						$data .= implode($tmp_row, '')."\r\n";
					}
					$data .= '9 '. str_pad( $total_records, 6, 0, STR_PAD_LEFT ) . meditechNumericFormat( $total_hours, 11 ) . '0000000000' . '0000000000'; //Transmission Trailer
				}
				unset($tmp_rows, $tmp_row, $export_column_map, $column_id, $column_data, $rows, $row);
				break;
			case 'csv': //Generic CSV.
				$file_name = strtolower(trim($setup_data['export_type'])).'_'.date('Y_m_d').'.csv';

				//If this needs to be customized, they can just export any regular report. This could probably be removed completely except for the Hour Code mapping...
				ksort($setup_data['csv']['columns']);
				$setup_data['csv']['columns'] = Misc::trimSortPrefix( $setup_data['csv']['columns'] );

				$export_column_map = array('employee' => '', 'employee_number' => '', 'default_branch' => '', 'default_department' => '', 'pay_period' => '', 'branch_name' => '', 'department_name' => '', 'hour_code' => '', 'hours' => '');

				$i = 0;
				foreach($rows as $row) {
					if ( $i == 0 ) {
						//Include header.
						$tmp_rows[] = array(
											'employee' => 'Employee',
											'employee_number' => 'Employee Number',
											'default_branch' => 'Default Branch',
											'default_department' => 'Default Department',
											'pay_period' => 'Pay Period',
											'branch_name' => 'Branch',
											'department_name' => 'Department',
											'hour_code' => 'Hours Code',
											'hours' => 'Hours',
											);
					}

					//Combine all hours from the same code together.
					foreach( $setup_data['csv']['columns'] as $column_id => $column_data ) {
						$hour_code = ( isset($column_data['hour_code']) ) ? trim($column_data['hour_code']) : '';
						if ( isset( $row[$column_id.'_time'] ) AND $hour_code != '' ) {
							if ( !isset($tmp_hour_codes[$hour_code]) ) {
								$tmp_hour_codes[$hour_code] = 0;
							}
							$tmp_hour_codes[$hour_code] = bcadd( $tmp_hour_codes[$column_data['hour_code']], $row[$column_id.'_time'] ); //Use seconds for math here.
						}
					}

					if ( isset($tmp_hour_codes) ) {
						foreach($tmp_hour_codes as $hour_code => $hours ) {
							$tmp_rows[] = array(
												'employee' => ( isset($row['full_name']) ) ? $row['full_name'] : NULL,
												'employee_number' => ( isset($row['employee_number']) ) ? $row['employee_number'] : NULL,
												'default_branch' => ( isset($row['default_branch']) ) ? $row['default_branch'] : NULL,
												'default_department' => ( isset($row['default_department']) ) ? $row['default_department'] : NULL,
												'pay_period' => ( isset($row['pay_period']['display']) ) ? $row['pay_period']['display'] : NULL,
												'branch_name' => ( isset($row['branch_name']) ) ? $row['branch_name'] : NULL,
												'department_name' => ( isset($row['department_name']) ) ? $row['department_name'] : NULL,
												'hour_code' => $hour_code,
												'hours' => TTDate::getTimeUnit($hours, 20 ),
												);
						}
						unset($tmp_hour_codes, $hour_code, $hours);
					}

					$i++;
				}

				if ( isset( $tmp_rows) ) {
					$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE );
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);
				break;
			case 'csv_advanced': //Generic CSV.
				unset($rows); //Ignore any existing timesheet summary data, we will be using our own job data below.

				//If this needs to be customized, they can just export any regular report. This could probably be removed completely except for the Hour Code mapping...
				if ( !isset($setup_data['csv_advanced']['export_columns']) OR ( isset($setup_data['csv_advanced']['export_columns']) AND !is_array($setup_data['csv_advanced']['export_columns']) ) ) {
					$setup_data['csv_advanced']['export_columns'] = array(
																			'full_name',
																			'employee_number',
																			'default_branch',
																			'default_department',
																			'pay_period',
																			'date_stamp',
																			);
				}

				if ( isset($setup_data['csv_advanced']['export_columns']) AND is_array($setup_data['csv_advanced']['export_columns']) ) {
					//Debug::Arr($setup_data['csv_advanced']['export_columns'], 'Custom Columns defined: ', __FILE__, __LINE__, __METHOD__, 10);
					$config['columns'] = $config['group'] = $setup_data['csv_advanced']['export_columns'];

					//Force sorting...
					foreach( $setup_data['csv_advanced']['export_columns'] as $export_column ) {
						$config['sort'][] = array( $export_column => 'asc' );
					}

					$dynamic_column_names = array_keys( Misc::trimSortPrefix( $this->getOptions('dynamic_columns') ) );
					$config['columns'] += $dynamic_column_names;

					//Force group by hourly_rates, so if there are multiple hourly rates with the same pay code its still split out.
					foreach( $dynamic_column_names as $dynamic_column_name ) {
						if ( substr( $dynamic_column_name, -12 ) == '_hourly_rate' ) { //Ends with
							$config['group'][] = $dynamic_column_name;
						}
					}
					unset($dynamic_column_names, $dynamic_column_name);
				}
				//Debug::Arr($config, 'Job Detail Report Config: ', __FILE__, __LINE__, __METHOD__, 10);

				//Get job data...
				if ( is_object( $this->getUserObject() ) AND is_object( $this->getUserObject()->getCompanyObject() ) AND $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
					Debug::Text('Using Job Detail Report...', __FILE__, __LINE__, __METHOD__, 10);
					$jar = TTNew('JobDetailReport');
				} else {
					Debug::Text('Using TimeSheet Detail Report...', __FILE__, __LINE__, __METHOD__, 10);
					$jar = TTNew('TimesheetDetailReport');
				}
				$jar->setAMFMessageID( $this->getAMFMessageID() );
				$jar->setUserObject( $this->getUserObject() );
				$jar->setPermissionObject( $this->getPermissionObject() );
				$jar->setConfig( $config );
				$jar->setFilterConfig( $this->getFilterConfig() );
				$jar->setCustomFilterConfig( $this->getCustomFilterConfig() );
				$jar->setCustomColumnConfig( array_merge( (array)$this->getCustomFilterConfig(), (array)$this->getCustomColumnConfig() ) );
				if ( isset($config['sort']) ) {
					$jar->setSortConfig( $config['sort'] );
				}
				$jar->_getData();
				$jar->_preProcess();
				$jar->currencyConvertToBase();
				$jar->calculateCustomColumns( 10 ); //Selections (these are pre-group)
				$jar->calculateCustomColumns( 20 ); //Pre-Group
				$jar->calculateCustomColumnFilters( 30 ); //Pre-Group
				$jar->group();
				$jar->calculateCustomColumns( 21 ); //Post-Group: things like round() functions normally need to be done post-group, otherwise they are rounding already rounded values.
				$jar->calculateCustomColumnFilters( 31 );
				$jar->sort();
				$jar->_postProcess( 'csv' ); //Minor post-processing.

				$columns = Misc::trimSortPrefix( $jar->getOptions('columns') );

				$rows = $jar->data;
				//Debug::Arr($rows, 'Raw Rows: ', __FILE__, __LINE__, __METHOD__, 10);

				$file_name = strtolower(trim($setup_data['export_type'])).'_'.date('Y_m_d').'.csv';

				//If this needs to be customized, they can just export any regular report. This could probably be removed completely except for the Hour Code mapping...
				ksort($setup_data['csv_advanced']['columns']);
				$setup_data['csv_advanced']['columns'] = Misc::trimSortPrefix( $setup_data['csv_advanced']['columns'] );

				foreach( $setup_data['csv_advanced']['export_columns'] as $export_column ) {
					$export_column_map[$export_column] = '';
				}
				$export_column_map['hour_code'] = '';
				$export_column_map['hours'] = '';
				$export_column_map['hourly_rate'] = '';

				$i = 0;
				foreach($rows as $row) {
					if ( $i == 0 ) {
						//Include header.
						foreach( $setup_data['csv_advanced']['export_columns'] as $export_column ) {
							Debug::Text('Header Row: '. $export_column, __FILE__, __LINE__, __METHOD__, 10);
							$tmp_rows[$i][$export_column] = ( isset($columns[$export_column]) ) ? $columns[$export_column] : NULL;
						}
						$tmp_rows[$i]['hour_code'] = 'Hours Code';
						$tmp_rows[$i]['hours'] = 'Hours';
						$tmp_rows[$i]['hourly_rate'] = 'Hourly Rate';

						$i++;
					}

					//Combine all hours from the same code together.
					foreach( $setup_data['csv_advanced']['columns'] as $column_id => $column_data ) {
						$hour_code = trim($column_data['hour_code']);
						$hourly_rate = ( isset($row[$column_id.'_hourly_rate']) ) ? $row[$column_id.'_hourly_rate'] : NULL;
						$hour_code_key = $hour_code .':'. $hourly_rate; //Support for multiple rates of pay with the same hour code.
						if ( isset( $row[$column_id.'_time'] ) AND $hour_code != '' ) {
							if ( !isset($tmp_hour_codes[$hour_code_key]) ) {
								$tmp_hour_codes[$hour_code_key]['hour_code'] = $hour_code;
								$tmp_hour_codes[$hour_code_key]['hours'] = 0;
							}

							$tmp_hour_codes[$hour_code_key]['hour_code'] = $hour_code;
							$tmp_hour_codes[$hour_code_key]['hours'] = bcadd( $tmp_hour_codes[$hour_code_key]['hours'], $row[$column_id.'_time'] ); //Use seconds for math here.
							$tmp_hour_codes[$hour_code_key]['rate'] = $hourly_rate;
						}
					}
					unset($hour_code, $hourly_rate, $hour_code_key);

					if ( isset($tmp_hour_codes) ) {
						foreach($tmp_hour_codes as $hour_code => $hour_code_arr ) {
							foreach( $setup_data['csv_advanced']['export_columns'] as $export_column ) {
								$tmp_rows[$i][$export_column] = ( isset($row[$export_column]) ) ? ( is_array($row[$export_column]) AND isset($row[$export_column]['display']) ) ? $row[$export_column]['display'] : $row[$export_column] : NULL;
								$tmp_rows[$i]['hour_code'] = $hour_code_arr['hour_code'];
								$tmp_rows[$i]['hours'] = $hour_code_arr['hours']; //_postProcess() already converts this.
								$tmp_rows[$i]['hourly_rate'] = $hour_code_arr['rate'];
							}
							$i++;
						}
						unset($tmp_hour_codes, $hour_code, $hours);
					}
				}
				//Debug::Arr($tmp_rows, 'Tmp Rows: ', __FILE__, __LINE__, __METHOD__, 10);

				if ( isset( $tmp_rows) ) {
					$data = Misc::Array2CSV( $tmp_rows, $export_column_map, FALSE, FALSE );
				}
				unset($tmp_rows, $export_column_map, $column_id, $column_data, $rows, $row);
				break;
			default: //Send raw data so plugin can capture it and change it if needed.
				$data = $this->data;
				break;
		}

		//Debug::Arr($data, 'Export Data: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array($data) ) { //If there is a XML or some validation error, return that to the user.
			return $data;
		}

		return array( 'file_name' => $file_name, 'mime_type' => $mime_type, 'data' => $data );
	}

	/**
	 * @param null $format
	 * @return array|bool|null|string
	 */
	function _output( $format = NULL ) {
		//Get Form Config data, which can use for the export config.
		if ( $format == 'payroll_export' ) {
			return $this->_outputPayrollExport( $format );
		} else {
			return parent::_output( $format );
		}
	}
}
?>
