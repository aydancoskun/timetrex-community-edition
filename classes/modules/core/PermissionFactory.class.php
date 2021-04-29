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
 * @package Core
 */
class PermissionFactory extends Factory {
	protected $table = 'permission';
	protected $pk_sequence_name = 'permission_id_seq'; //PK Sequence name

	protected $permission_control_obj = null;
	protected $company_id = null;

	/**
	 * @param $name
	 * @param null $parent
	 * @return array|bool|null
	 */
	function _getFactoryOptions( $name, $parent = null ) {
		$retval = null;
		switch ( $name ) {
			case 'preset':
				$retval = [
					//-1 => TTi18n::gettext('--'),
					5  => TTi18n::gettext( 'Terminated Employee' ),
					10 => TTi18n::gettext( 'Regular Employee (Punch In/Out)' ),
					12 => TTi18n::gettext( 'Regular Employee (Manual Punch)' ), //Can manually Add/Edit own punches/absences.
					14 => TTi18n::gettext( 'Regular Employee (Manual TimeSheet)' ), //Can use manual timesheet and punches.
					18 => TTi18n::gettext( 'Supervisor (Subordinates Only)' ),
					20 => TTi18n::gettext( 'Supervisor (All Employees)' ),
					25 => TTi18n::gettext( 'HR Manager' ),
					30 => TTi18n::gettext( 'Payroll Administrator' ),
					40 => TTi18n::gettext( 'Administrator' ),
				];

				if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
					unset( $retval[14] );
				}
				break;
			case 'common_permissions':
				$retval = [
						'add'          => TTi18n::gettext( 'Add' ),
						'view'         => TTi18n::gettext( 'View' ),
						'view_own'     => TTi18n::gettext( 'View Own' ),
						'view_child'   => TTi18n::gettext( 'View Subordinate' ),
						'edit'         => TTi18n::gettext( 'Edit' ),
						'edit_own'     => TTi18n::gettext( 'Edit Own' ),
						'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
						'delete'       => TTi18n::gettext( 'Delete' ),
						'delete_own'   => TTi18n::gettext( 'Delete Own' ),
						'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
						'other'        => TTi18n::gettext( 'Other' ),
				];

				if ( defined( 'TIMETREX_API' ) == true && TIMETREX_API == true ) {
					$retval = Misc::addSortPrefix( $retval, 1000 );
				}
				break;
			case 'preset_flags':
				//Remove sections that don't apply to the current product edition.
				$product_edition = Misc::getCurrentCompanyProductEdition();

				if ( $product_edition >= TT_PRODUCT_COMMUNITY ) {
					$retval[10] = TTi18n::gettext( 'Scheduling' );
					$retval[20] = TTi18n::gettext( 'Time & Attendance' );
					$retval[30] = TTi18n::gettext( 'Payroll' );
					$retval[70] = TTi18n::gettext( 'Human Resources' );
				}

				if ( $product_edition >= TT_PRODUCT_CORPORATE ) {
					$retval[40] = TTi18n::gettext( 'Job Costing' );
					$retval[50] = TTi18n::gettext( 'Document Management' );
					$retval[60] = TTi18n::gettext( 'Invoicing' );
				}

				if ( $product_edition >= TT_PRODUCT_ENTERPRISE ) {
					$retval[75] = TTi18n::gettext( 'Recruitment' );
					$retval[80] = TTi18n::gettext( 'Expense Tracking' );
				}
				ksort( $retval );
				break;
			case 'preset_level':
				$retval = [
						5  => 5,
						10 => 10, //Was: 1
						12 => 20, //Was: 2
						14 => 30, //Was: 3
						18 => 40, //Was: 10
						20 => 50, //Was: 15
						25 => 70, //Was: 18
						30 => 80, //Was: 20
						40 => 100, //Was: 25
				];
				break;
			case 'section_group':
				$retval = [
						0             => TTi18n::gettext( '-- Please Choose --' ),
						'all'         => TTi18n::gettext( '-- All --' ),
						'company'     => TTi18n::gettext( 'Company' ),
						'user'        => TTi18n::gettext( 'Employee' ),
						'schedule'    => TTi18n::gettext( 'Schedule' ),
						'attendance'  => TTi18n::gettext( 'Attendance' ),
						'job'         => TTi18n::gettext( 'Job Tracking' ),
						'invoice'     => TTi18n::gettext( 'Invoicing' ),
						'payroll'     => TTi18n::gettext( 'Payroll' ),
						'policy'      => TTi18n::gettext( 'Policies' ),
						'report'      => TTi18n::gettext( 'Reports' ),
						'hr'          => TTi18n::gettext( 'Human Resources (HR)' ),
						'recruitment' => TTi18n::gettext( 'Recruitment' ),
				];

				//Remove sections that don't apply to the current product edition.
				$product_edition = Misc::getCurrentCompanyProductEdition();

				//if ( $product_edition == TT_PRODUCT_ENTERPRISE ) { //Enterprise
				// } elseif {
				if ( $product_edition == TT_PRODUCT_CORPORATE ) { //Corporate
					unset( $retval['recruitment'] );
				} else if ( $product_edition == TT_PRODUCT_COMMUNITY || $product_edition == TT_PRODUCT_PROFESSIONAL ) { //Community or Professional
					unset( $retval['job'], $retval['invoice'], $retval['recruitment'] );
				}

				if ( defined( 'TIMETREX_API' ) == true && TIMETREX_API == true ) {
					unset( $retval[0] );
					$retval = Misc::addSortPrefix( $retval, 1000 );
					ksort( $retval );
				}

				break;
			case 'section_group_map':
				$retval = [
						'company'     => [
								'system',
								'company',
								'legal_entity',
								'currency',
								'branch',
								'department',
								'geo_fence',
								'station',
								'hierarchy',
								'authorization',
								'message',
								'other_field',
								'document',
								'help',
								'permission',
								'pay_period_schedule',
						],
						'user'        => [
								'user',
								'user_preference',
								'user_tax_deduction',
								'user_contact',
								'remittance_destination_account',
						],
						'schedule'    => [
								'schedule',
								'recurring_schedule',
								'recurring_schedule_template',
						],
						'attendance'  => [
								'punch',
								'user_date_total',
								'absence',
								'accrual',
								'request',
						],
						'job'         => [
								'job',
								'job_item',
								'job_report',
						],
						'invoice'     => [
								'invoice_config',
								'client',
								'client_payment',
								'product',
								'tax_policy',
								'area_policy',
								'shipping_policy',
								'payment_gateway',
								'transaction',
								'invoice',
								'invoice_report',
						],
						'policy'      => [
								'policy_group',
								'pay_code',
								'pay_formula_policy',
								'contributing_pay_code_policy',
								'contributing_shift_policy',
								'schedule_policy',
								'meal_policy',
								'break_policy',
								'regular_time_policy',
								'over_time_policy',
								'premium_policy',
								'accrual_policy',
								'absence_policy',
								'round_policy',
								'exception_policy',
								'holiday_policy',
								'expense_policy',
						],
						'payroll'     => [
								'pay_stub_account',
								'pay_stub',
								'government_document',
								'pay_stub_amendment',
								'payroll_remittance_agency',
								'remittance_source_account',
								'wage',
								'roe',
								'company_tax_deduction',
								'user_expense',
						],
						'report'      => [
								'report',
								'report_custom_column',
						],
						'hr'          => [
								'qualification',
								'user_education',
								'user_license',
								'user_skill',
								'user_membership',
								'user_language',
								'kpi',
								'user_review',
								'job_vacancy',
								'job_applicant',
								'job_application',
								'hr_report',
						],
						'recruitment' => [
								'job_vacancy',
								'job_applicant',
								'job_application',
								'recruitment_report',
						],
				];

				//Remove sections that don't apply to the current product edition.
				$product_edition = Misc::getCurrentCompanyProductEdition();
				//if ( $product_edition == TT_PRODUCT_ENTERPRISE ) { //Enterprise
				//} else
				if ( $product_edition == TT_PRODUCT_CORPORATE ) { //Corporate
					unset( $retval['recruitment'] );
					unset( $retval['payroll'][array_search( 'user_expense', $retval['payroll'] )], $retval['policy'][array_search( 'expense_policy', $retval['policy'] )] );
				} else if ( $product_edition == TT_PRODUCT_COMMUNITY || $product_edition == TT_PRODUCT_PROFESSIONAL ) { //Community or Professional
					unset( $retval['recruitment'], $retval['invoice'], $retval['job'], $retval['geo_fence'], $retval['government_document'] );
					unset( $retval['payroll'][array_search( 'user_expense', $retval['payroll'] )], $retval['policy'][array_search( 'expense_policy', $retval['policy'] )] );
				}

				break;
			case 'section':
				$retval = [
						'system'        => TTi18n::gettext( 'System' ),
						'company'       => TTi18n::gettext( 'Company' ),
						'legal_entity'  => TTi18n::gettext( 'Legal Entity' ),
						'currency'      => TTi18n::gettext( 'Currency' ),
						'branch'        => TTi18n::gettext( 'Branch' ),
						'department'    => TTi18n::gettext( 'Department' ),
						'geo_fence'     => TTi18n::gettext( 'GEO Fence' ),
						'station'       => TTi18n::gettext( 'Station' ),
						'hierarchy'     => TTi18n::gettext( 'Hierarchy' ),
						'authorization' => TTi18n::gettext( 'Authorization' ),
						'other_field'   => TTi18n::gettext( 'Other Fields' ),
						'document'      => TTi18n::gettext( 'Documents' ),
						'message'       => TTi18n::gettext( 'Message' ),
						'help'          => TTi18n::gettext( 'Help' ),
						'permission'    => TTi18n::gettext( 'Permissions' ),

						'user'                           => TTi18n::gettext( 'Employees' ),
						'user_preference'                => TTi18n::gettext( 'Employee Preferences' ),
						'user_tax_deduction'             => TTi18n::gettext( 'Employee Tax / Deductions' ),
						'user_contact'                   => TTi18n::gettext( 'Employee Contact' ),
						'remittance_destination_account' => TTi18n::gettext( 'Employee Pay Methods' ),

						'schedule'                    => TTi18n::gettext( 'Schedule' ),
						'recurring_schedule'          => TTi18n::gettext( 'Recurring Schedule' ),
						'recurring_schedule_template' => TTi18n::gettext( 'Recurring Schedule Template' ),

						'request'         => TTi18n::gettext( 'Requests' ),
						'accrual'         => TTi18n::gettext( 'Accruals' ),
						'punch'           => TTi18n::gettext( 'Punch' ),
						'user_date_total' => TTi18n::gettext( 'TimeSheet Accumulated Time' ),
						'absence'         => TTi18n::gettext( 'Absence' ),

						'job'        => TTi18n::gettext( 'Jobs' ),
						'job_item'   => TTi18n::gettext( 'Job Tasks' ),
						'job_report' => TTi18n::gettext( 'Job Reports' ),

						'invoice_config'  => TTi18n::gettext( 'Invoice Settings' ),
						'client'          => TTi18n::gettext( 'Invoice Clients' ),
						'client_payment'  => TTi18n::gettext( 'Client Payment Methods' ),
						'product'         => TTi18n::gettext( 'Products' ),
						'tax_policy'      => TTi18n::gettext( 'Tax Policies' ),
						'shipping_policy' => TTi18n::gettext( 'Shipping Policies' ),
						'area_policy'     => TTi18n::gettext( 'Area Policies' ),
						'payment_gateway' => TTi18n::gettext( 'Payment Gateway' ),
						'transaction'     => TTi18n::gettext( 'Invoice Transactions' ),
						'invoice'         => TTi18n::gettext( 'Invoices' ),
						'invoice_report'  => TTi18n::gettext( 'Invoice Reports' ),

						'policy_group'                 => TTi18n::gettext( 'Policy Group' ),
						'pay_code'                     => TTi18n::gettext( 'Pay Codes' ),
						'pay_formula_policy'           => TTi18n::gettext( 'Pay Formulas' ),
						'contributing_pay_code_policy' => TTi18n::gettext( 'Contributing Pay Code Policies' ),
						'contributing_shift_policy'    => TTi18n::gettext( 'Contributing Shift Policies' ),
						'schedule_policy'              => TTi18n::gettext( 'Schedule Policies' ),
						'meal_policy'                  => TTi18n::gettext( 'Meal Policies' ),
						'break_policy'                 => TTi18n::gettext( 'Break Policies' ),
						'regular_time_policy'          => TTi18n::gettext( 'Regular Time Policies' ),
						'over_time_policy'             => TTi18n::gettext( 'Overtime Policies' ),
						'premium_policy'               => TTi18n::gettext( 'Premium Policies' ),
						'accrual_policy'               => TTi18n::gettext( 'Accrual Policies' ),
						'absence_policy'               => TTi18n::gettext( 'Absence Policies' ),
						'round_policy'                 => TTi18n::gettext( 'Rounding Policies' ),
						'exception_policy'             => TTi18n::gettext( 'Exception Policies' ),
						'holiday_policy'               => TTi18n::gettext( 'Holiday Policies' ),
						'expense_policy'               => TTi18n::gettext( 'Expense Policies' ),

						'pay_stub_account'          => TTi18n::gettext( 'Pay Stub Accounts' ),
						'payroll_remittance_agency' => TTi18n::gettext( 'Payroll Remittance Agency' ),
						'remittance_source_account' => TTi18n::gettext( 'Remittance Source Account' ),
						'pay_stub'                  => TTi18n::gettext( 'Employee Pay Stubs' ),
						'government_document'       => TTi18n::gettext( 'Government Documents' ),
						'pay_stub_amendment'        => TTi18n::gettext( 'Employee Pay Stub Amendments' ),
						'wage'                      => TTi18n::gettext( 'Wages' ),
						'pay_period_schedule'       => TTi18n::gettext( 'Pay Period Schedule' ),
						'roe'                       => TTi18n::gettext( 'Record of Employment' ),
						'company_tax_deduction'     => TTi18n::gettext( 'Company Tax / Deductions' ),
						'user_expense'              => TTi18n::gettext( 'Employee Expenses' ),

						'report'               => TTi18n::gettext( 'Reports' ),
						'report_custom_column' => TTi18n::gettext( 'Report Custom Column' ),

						'qualification'   => TTi18n::gettext( 'Qualifications' ),
						'user_education'  => TTi18n::gettext( 'Employee Education' ),
						'user_license'    => TTi18n::gettext( 'Employee Licenses' ),
						'user_skill'      => TTi18n::gettext( 'Employee Skills' ),
						'user_membership' => TTi18n::gettext( 'Employee Memberships' ),
						'user_language'   => TTi18n::gettext( 'Employee Language' ),

						'kpi'         => TTi18n::gettext( 'Key Performance Indicators' ),
						'user_review' => TTi18n::gettext( 'Employee Review' ),

						'job_vacancy'     => TTi18n::gettext( 'Job Vacancy' ),
						'job_applicant'   => TTi18n::gettext( 'Job Applicant' ),
						'job_application' => TTi18n::gettext( 'Job Application' ),

						'hr_report'          => TTi18n::gettext( 'HR Reports' ),
						'recruitment_report' => TTi18n::gettext( 'Recruitment Reports' ),
				];
				break;
			case 'name':
				$retval = [
						'system'                         => [
								'login' => TTi18n::gettext( 'Login Enabled' ),
						],
						'company'                        => [
								'enabled'          => TTi18n::gettext( 'Enabled' ),
								'view_own'         => TTi18n::gettext( 'View Own' ),
								'view'             => TTi18n::gettext( 'View' ),
								'add'              => TTi18n::gettext( 'Add' ),
								'edit_own'         => TTi18n::gettext( 'Edit Own' ),
								'edit'             => TTi18n::gettext( 'Edit' ),
								'delete_own'       => TTi18n::gettext( 'Delete Own' ),
								'delete'           => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete'),
								//'edit_own_bank' => TTi18n::gettext('Edit Own Banking Information'),
								'login_other_user' => TTi18n::gettext( 'Login as Other Employee' ),
						],
						'user'                           => [
								'enabled'                  => TTi18n::gettext( 'Enabled' ),
								'view_own'                 => TTi18n::gettext( 'View Own' ),
								'view_child'               => TTi18n::gettext( 'View Subordinate' ),
								'view'                     => TTi18n::gettext( 'View' ),
								'add'                      => TTi18n::gettext( 'Add' ),
								'edit_own'                 => TTi18n::gettext( 'Edit Own' ),
								'edit_child'               => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'                     => TTi18n::gettext( 'Edit' ),
								'edit_advanced'            => TTi18n::gettext( 'Edit Advanced' ),
								//'edit_own_bank' => TTi18n::gettext('Edit Own Bank Info'),
								//'edit_child_bank' => TTi18n::gettext('Edit Subordinate Bank Info'),
								//'edit_bank' => TTi18n::gettext('Edit Bank Info'),
								'edit_permission_group'    => TTi18n::gettext( 'Edit Permission Group' ),
								'edit_pay_period_schedule' => TTi18n::gettext( 'Edit Pay Period Schedule' ),
								'edit_policy_group'        => TTi18n::gettext( 'Edit Policy Group' ),
								'edit_hierarchy'           => TTi18n::gettext( 'Edit Hierarchy' ),
								'edit_own_password'        => TTi18n::gettext( 'Edit Own Password' ),
								'edit_own_phone_password'  => TTi18n::gettext( 'Edit Own Quick Punch Password' ),
								'enroll'                   => TTi18n::gettext( 'Enroll Employees' ),
								'enroll_child'             => TTi18n::gettext( 'Enroll Subordinate' ),
								'timeclock_admin'          => TTi18n::gettext( 'TimeClock Administrator' ),
								'delete_own'               => TTi18n::gettext( 'Delete Own' ),
								'delete_child'             => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'                   => TTi18n::gettext( 'Delete' ),
								'view_sin'                 => TTi18n::gettext( 'View SIN/SSN' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'user_contact'                   => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								//'view_sin' => TTi18n::gettext('View SIN/SSN'),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'user_preference'                => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'user_tax_deduction'             => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'roe'                            => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'company_tax_deduction'          => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'user_expense'                   => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'authorize'    => TTi18n::gettext( 'Authorize Expense' )
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'pay_stub_account'               => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'payroll_remittance_agency'      => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
						],
						'remittance_source_account'      => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
						],
						'pay_stub'                       => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'government_document'            => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
						],
						'pay_stub_amendment'             => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'wage'                           => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'currency'                       => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'branch'                         => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'legal_entity'                   => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
						],
						'remittance_destination_account' => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
						],
						'department'                     => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete'),
								'assign'     => TTi18n::gettext( 'Assign Employees' ),

						],
						'geo_fence'                      => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
						],
						'station'                        => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete'),
								'assign'     => TTi18n::gettext( 'Assign Employees' ),
						],
						'pay_period_schedule'            => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete'),
								'assign'     => TTi18n::gettext( 'Assign Employees' ),
						],
						'schedule'                       => [
								'enabled'         => TTi18n::gettext( 'Enabled' ),
								'view_own'        => TTi18n::gettext( 'View Own' ),
								'view_child'      => TTi18n::gettext( 'View Subordinate' ),
								'view'            => TTi18n::gettext( 'View' ),
								'view_open'       => TTi18n::gettext( 'View Open Shifts' ),
								'add'             => TTi18n::gettext( 'Add' ),
								'edit_own'        => TTi18n::gettext( 'Edit Own' ),
								'edit_child'      => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'            => TTi18n::gettext( 'Edit' ),
								'delete_own'      => TTi18n::gettext( 'Delete Own' ),
								'delete_child'    => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'          => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete'),
								'edit_branch'     => TTi18n::gettext( 'Edit Branch Field' ),
								'edit_department' => TTi18n::gettext( 'Edit Department Field' ),
								'edit_job'        => TTi18n::gettext( 'Edit Job Field' ),
								'edit_job_item'   => TTi18n::gettext( 'Edit Task Field' ),
						],
						'other_field'                    => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete'),
						],
						'document'                       => [
								'enabled'        => TTi18n::gettext( 'Enabled' ),
								'view_own'       => TTi18n::gettext( 'View Own' ),
								'view'           => TTi18n::gettext( 'View' ),
								'view_private'   => TTi18n::gettext( 'View Private' ),
								'add'            => TTi18n::gettext( 'Add' ),
								'edit_own'       => TTi18n::gettext( 'Edit Own' ),
								'edit'           => TTi18n::gettext( 'Edit' ),
								'edit_private'   => TTi18n::gettext( 'Edit Private' ),
								'delete_own'     => TTi18n::gettext( 'Delete Own' ),
								'delete'         => TTi18n::gettext( 'Delete' ),
								'delete_private' => TTi18n::gettext( 'Delete Private' ),
								//'undelete' => TTi18n::gettext('Un-Delete'),
						],
						'accrual'                        => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'pay_code'                       => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'pay_formula_policy'             => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'policy_group'                   => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'contributing_pay_code_policy'   => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'contributing_shift_policy'      => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'schedule_policy'                => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'meal_policy'                    => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'break_policy'                   => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'absence_policy'                 => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'accrual_policy'                 => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'regular_time_policy'            => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'over_time_policy'               => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'premium_policy'                 => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'round_policy'                   => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view'       => TTi18n::gettext( 'View' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'exception_policy'               => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'holiday_policy'                 => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'expense_policy'                 => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],

						'recurring_schedule_template' => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'recurring_schedule'          => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'request'                     => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'add_advanced' => TTi18n::gettext( 'Add Advanced' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete'),
								'authorize'    => TTi18n::gettext( 'Authorize' ),
						],
						'punch'                       => [
								'enabled'           => TTi18n::gettext( 'Enabled' ),
								'view_own'          => TTi18n::gettext( 'View Own' ),
								'view_child'        => TTi18n::gettext( 'View Subordinate' ),
								'view'              => TTi18n::gettext( 'View' ),
								'add'               => TTi18n::gettext( 'Add' ),
								'edit_own'          => TTi18n::gettext( 'Edit Own' ),
								'edit_child'        => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'              => TTi18n::gettext( 'Edit' ),
								'delete_own'        => TTi18n::gettext( 'Delete Own' ),
								'delete_child'      => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'            => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete'),
								'edit_transfer'     => TTi18n::gettext( 'Edit Transfer Field' ),
								'default_transfer'  => TTi18n::gettext( 'Default Transfer On' ),
								'edit_branch'       => TTi18n::gettext( 'Edit Branch Field' ),
								'edit_department'   => TTi18n::gettext( 'Edit Department Field' ),
								'edit_job'          => TTi18n::gettext( 'Edit Job Field' ),
								'edit_job_item'     => TTi18n::gettext( 'Edit Task Field' ),
								'edit_quantity'     => TTi18n::gettext( 'Edit Quantity Field' ),
								'edit_bad_quantity' => TTi18n::gettext( 'Edit Bad Quantity Field' ),
								'edit_note'         => TTi18n::gettext( 'Edit Note Field' ),
								'edit_location'     => TTi18n::gettext( 'Edit Location' ),
								'edit_other_id1'    => TTi18n::gettext( 'Edit Other ID1 Field' ),
								'edit_other_id2'    => TTi18n::gettext( 'Edit Other ID2 Field' ),
								'edit_other_id3'    => TTi18n::gettext( 'Edit Other ID3 Field' ),
								'edit_other_id4'    => TTi18n::gettext( 'Edit Other ID4 Field' ),
								'edit_other_id5'    => TTi18n::gettext( 'Edit Other ID5 Field' ),

								'verify_time_sheet' => TTi18n::gettext( 'Verify TimeSheet' ),
								'authorize'         => TTi18n::gettext( 'Authorize TimeSheet' ),

								'punch_in_out' => TTi18n::gettext( 'Punch In/Out' ),

								'punch_timesheet'  => TTi18n::gettext( 'Punch TimeSheet' ), //Enables Punch Timesheet button for viewing.
								'manual_timesheet' => TTi18n::gettext( 'Manual TimeSheet' ), //Enables Manual Timesheet button for viewing.
						],
						'user_date_total'             => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete'),
						],
						'absence'                     => [
								'enabled'         => TTi18n::gettext( 'Enabled' ),
								'view_own'        => TTi18n::gettext( 'View Own' ),
								'view_child'      => TTi18n::gettext( 'View Subordinate' ),
								'view'            => TTi18n::gettext( 'View' ),
								'add'             => TTi18n::gettext( 'Add' ),
								'edit_own'        => TTi18n::gettext( 'Edit Own' ),
								'edit_child'      => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'            => TTi18n::gettext( 'Edit' ),
								'delete_own'      => TTi18n::gettext( 'Delete Own' ),
								'delete_child'    => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'          => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete'),
								'edit_branch'     => TTi18n::gettext( 'Edit Branch Field' ),
								'edit_department' => TTi18n::gettext( 'Edit Department Field' ),
								'edit_job'        => TTi18n::gettext( 'Edit Job Field' ),
								'edit_job_item'   => TTi18n::gettext( 'Edit Task Field' ),
						],
						'hierarchy'                   => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'authorization'               => [
								'enabled' => TTi18n::gettext( 'Enabled' ),
								'view'    => TTi18n::gettext( 'View' ),
						],
						'message'                     => [
								'enabled'       => TTi18n::gettext( 'Enabled' ),
								'view_own'      => TTi18n::gettext( 'View Own' ),
								'view'          => TTi18n::gettext( 'View' ),
								'add'           => TTi18n::gettext( 'Add' ),
								'add_advanced'  => TTi18n::gettext( 'Add Advanced' ),
								'edit_own'      => TTi18n::gettext( 'Edit Own' ),
								'edit'          => TTi18n::gettext( 'Edit' ),
								'delete_own'    => TTi18n::gettext( 'Delete Own' ),
								'delete'        => TTi18n::gettext( 'Delete' ),
								'send_to_any'   => TTi18n::gettext( 'Send to Any Employee' ),
								'send_to_child' => TTi18n::gettext( 'Send to Subordinate' )
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'help'                        => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'report'                      => [
								'enabled'                      => TTi18n::gettext( 'Enabled' ),
								'view_active_shift'            => TTi18n::gettext( 'Whos In Summary' ),
								'view_user_information'        => TTi18n::gettext( 'Employee Information' ),
								//'view_user_detail' => TTi18n::gettext('Employee Detail'),
								'view_pay_stub_summary'        => TTi18n::gettext( 'Pay Stub Summary' ),
								'view_payroll_export'          => TTi18n::gettext( 'Payroll Export' ),
								//'view_wages_payable_summary' => TTi18n::gettext('Wages Payable Summary'),
								'view_system_log'              => TTi18n::gettext( 'Audit Trail' ),
								//'view_employee_pay_stub_summary' => TTi18n::gettext('Employee Pay Stub Summary'),
								'view_timesheet_summary'       => TTi18n::gettext( 'Timesheet Summary' ),
								'view_exception_summary'       => TTi18n::gettext( 'Exception Summary' ),
								'view_accrual_balance_summary' => TTi18n::gettext( 'Accrual Balance Summary' ),
								'view_schedule_summary'        => TTi18n::gettext( 'Schedule Summary' ),
								'view_punch_summary'           => TTi18n::gettext( 'Punch Summary' ),
								'view_remittance_summary'      => TTi18n::gettext( 'Remittance Summary' ),
								//'view_branch_summary' => TTi18n::gettext('Branch Summary'),
								'view_t4_summary'              => TTi18n::gettext( 'T4 Summary' ),
								'view_generic_tax_summary'     => TTi18n::gettext( 'Generic Tax Summary' ),
								'view_form941'                 => TTi18n::gettext( 'Form 941' ),
								'view_form940'                 => TTi18n::gettext( 'Form 940' ),
								'view_form940ez'               => TTi18n::gettext( 'Form 940-EZ' ),
								'view_form1099misc'            => TTi18n::gettext( 'Form 1099-Misc' ),
								'view_formW2'                  => TTi18n::gettext( 'Form W2 / W3' ),
								'view_affordable_care'         => TTi18n::gettext( 'Affordable Care' ),
								'view_user_barcode'            => TTi18n::gettext( 'Employee Barcodes' ),
								'view_general_ledger_summary'  => TTi18n::gettext( 'General Ledger Summary' ),
								//'view_roe' => TTi18n::gettext('Record of employment'), //Disable for now as its not needed, use 'roe', 'view' instead.
								'view_expense'                 => TTi18n::gettext( 'Expense Summary' ),
						],
						'report_custom_column'        => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'job'                         => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'job_item'                    => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'job_report'                  => [
								'enabled'                   => TTi18n::gettext( 'Enabled' ),
								'view_job_summary'          => TTi18n::gettext( 'Job Summary' ),
								'view_job_analysis'         => TTi18n::gettext( 'Job Analysis' ),
								'view_job_payroll_analysis' => TTi18n::gettext( 'Job Payroll Analysis' ),
								'view_job_barcode'          => TTi18n::gettext( 'Job Barcode' ),
						],
						'invoice_config'              => [
								'enabled' => TTi18n::gettext( 'Enabled' ),
								'add'     => TTi18n::gettext( 'Add' ),
								'edit'    => TTi18n::gettext( 'Edit' ),
								'delete'  => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'client'                      => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'client_payment'              => [
								'enabled'          => TTi18n::gettext( 'Enabled' ),
								'view_own'         => TTi18n::gettext( 'View Own' ),
								'view'             => TTi18n::gettext( 'View' ),
								'add'              => TTi18n::gettext( 'Add' ),
								'edit_own'         => TTi18n::gettext( 'Edit Own' ),
								'edit'             => TTi18n::gettext( 'Edit' ),
								'delete_own'       => TTi18n::gettext( 'Delete Own' ),
								'delete'           => TTi18n::gettext( 'Delete' ),
								'view_credit_card' => TTi18n::gettext( 'View Credit Card #' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'product'                     => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'tax_policy'                  => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'shipping_policy'             => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'area_policy'                 => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'payment_gateway'             => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'transaction'                 => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'invoice'                     => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'invoice_report'              => [
								'enabled'                  => TTi18n::gettext( 'Enabled' ),
								'view_transaction_summary' => TTi18n::gettext( 'View Transaction Summary' ),
						],
						'permission'                  => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'qualification'               => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'user_education'              => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'user_license'                => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'user_skill'                  => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'user_membership'             => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'user_language'               => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'kpi'                         => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'user_review'                 => [
								'enabled'      => TTi18n::gettext( 'Enabled' ),
								'view_own'     => TTi18n::gettext( 'View Own' ),
								'view_child'   => TTi18n::gettext( 'View Subordinate' ),
								'view'         => TTi18n::gettext( 'View' ),
								'add'          => TTi18n::gettext( 'Add' ),
								'edit_own'     => TTi18n::gettext( 'Edit Own' ),
								'edit_child'   => TTi18n::gettext( 'Edit Subordinate' ),
								'edit'         => TTi18n::gettext( 'Edit' ),
								'delete_own'   => TTi18n::gettext( 'Delete Own' ),
								'delete_child' => TTi18n::gettext( 'Delete Subordinate' ),
								'delete'       => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'job_vacancy'                 => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								//'view_child' => TTi18n::gettext('View Subordinate'),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								//'edit_child' => TTi18n::gettext('Edit Subordinate'),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								//'delete_child' => TTi18n::gettext('Delete Subordinate'),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')
						],
						'job_applicant'               => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								//'view_child' => TTi18n::gettext('View Subordinate'),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								//'edit_child' => TTi18n::gettext('Edit Subordinate'),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								//'delete_child' => TTi18n::gettext('Delete Subordinate'),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')

						],
						'job_application'             => [
								'enabled'    => TTi18n::gettext( 'Enabled' ),
								'view_own'   => TTi18n::gettext( 'View Own' ),
								//'view_child' => TTi18n::gettext('View Subordinate'),
								'view'       => TTi18n::gettext( 'View' ),
								'add'        => TTi18n::gettext( 'Add' ),
								'edit_own'   => TTi18n::gettext( 'Edit Own' ),
								//'edit_child' => TTi18n::gettext('Edit Subordinate'),
								'edit'       => TTi18n::gettext( 'Edit' ),
								'delete_own' => TTi18n::gettext( 'Delete Own' ),
								//'delete_child' => TTi18n::gettext('Delete Subordinate'),
								'delete'     => TTi18n::gettext( 'Delete' ),
								//'undelete' => TTi18n::gettext('Un-Delete')

						],

						'hr_report'          => [
								'enabled'            => TTi18n::gettext( 'Enabled' ),
								'user_qualification' => TTi18n::gettext( 'Employee Qualifications' ),
								'user_review'        => TTi18n::getText( 'Employee Review' ),
								'user_recruitment'   => TTi18n::gettext( 'Employee Recruitment' ),
						],
						'recruitment_report' => [
								'enabled'          => TTi18n::gettext( 'Enabled' ),
								'user_recruitment' => TTi18n::gettext( 'Employee Recruitment' ),
						],
				];
				break;
		}

		return $retval;
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setCompany( $value ) {
		$this->company_id = $value;

		return true;
	}

	/**
	 * @return null
	 */
	function getCompany() {
		if ( $this->company_id != '' ) {
			return $this->company_id;
		} else {
			$company_id = $this->getPermissionControlObject()->getCompany();

			return $company_id;
		}
	}

	/**
	 * @return bool|null
	 */
	function getPermissionControlObject() {
		if ( is_object( $this->permission_control_obj ) ) {
			return $this->permission_control_obj;
		} else {

			$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
			$pclf->getById( $this->getPermissionControl() );

			if ( $pclf->getRecordCount() == 1 ) {
				$this->permission_control_obj = $pclf->getCurrent();

				return $this->permission_control_obj;
			}

			return false;
		}
	}

	/**
	 * @return bool|int|string
	 */
	function getPermissionControl() {
		return TTUUID::castUUID( $this->getGenericDataValue( 'permission_control_id' ) );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPermissionControl( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'permission_control_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getSection() {
		return $this->getGenericDataValue( 'section' );
	}

	/**
	 * @param $section
	 * @param bool $disable_error_check
	 * @return bool
	 */
	function setSection( $section, $disable_error_check = false ) {
		$section = trim( $section );

		return $this->setGenericDataValue( 'section', $section );
	}

	/**
	 * @return bool|mixed
	 */
	function getName() {
		return $this->getGenericDataValue( 'name' );
	}

	/**
	 * @param $name
	 * @param bool $disable_error_check
	 * @return bool
	 */
	function setName( $name, $disable_error_check = false ) {
		$name = trim( $name );

		return $this->setGenericDataValue( 'name', $name );
	}

	/**
	 * @return bool
	 */
	function getValue() {
		$value = $this->getGenericDataValue( 'value' );
		if ( $value !== false && $value == 1 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setValue( $value ) {
		$value = (int)$value;

		//Debug::Arr($value, 'Value: ', __FILE__, __LINE__, __METHOD__, 10);
		return $this->setGenericDataValue( 'value', $value );
	}

	/**
	 * @param $preset
	 * @param bool $filter_sections
	 * @param bool $filter_permissions
	 * @return bool
	 */
	function filterPresetPermissions( $preset, $filter_sections = false, $filter_permissions = false ) {
		//Debug::Arr( array($filter_sections, $filter_permissions), 'Preset: '. $preset, __FILE__, __LINE__, __METHOD__, 10);
		if ( $preset == 0 ) {
			$preset = 40; //Administrator.
		}

		$filter_sections = Misc::trimSortPrefix( $filter_sections, true );
		if ( !is_array( $filter_sections ) ) {
			$filter_sections = false;
		}

		//Always add enabled, system to the filter_permissions.
		$filter_permissions[] = 'enabled';
		$filter_permissions[] = 'login';
		$filter_permissions = Misc::trimSortPrefix( $filter_permissions, true );
		if ( !is_array( $filter_permissions ) ) {
			$filter_permissions = false;
		}

		//Get presets based on all flags.
		$preset_permissions = $this->getPresetPermissions( $preset, array_keys( $this->getOptions( 'preset_flags' ) ) );
		//Debug::Arr($preset_permissions, 'Preset Permissions: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( is_array( $preset_permissions ) ) {
			foreach ( $preset_permissions as $section => $permissions ) {
				if ( $filter_sections === false || in_array( $section, $filter_sections ) ) {
					foreach ( $permissions as $name => $value ) {
						//Other permission basically matches anything that is not in filter list. Things like edit_own_password, etc...
						if ( $filter_permissions === false || in_array( $name, $filter_permissions ) || ( in_array( 'other', $filter_permissions ) && !in_array( $name, $filter_permissions ) ) ) {
							//Debug::Text('aSetting Permission - Section: '. $section .' Name: '. $name .' Value: '. (int)$value, __FILE__, __LINE__, __METHOD__, 10);
							$retarr[$section][$name] = $value;
						} //else { //Debug::Text('bNOT Setting Permission - Section: '. $section .' Name: '. $name .' Value: '. (int)$value, __FILE__, __LINE__, __METHOD__, 10);
					}
				}
			}
		}

		if ( isset( $retarr ) ) {
			Debug::Arr( $retarr, 'Filtered Permissions', __FILE__, __LINE__, __METHOD__, 10 );

			return $retarr;
		}

		return false;
	}

	/**
	 * @param $preset
	 * @param array $preset_flags
	 * @param bool $force_system_presets
	 * @return array|bool
	 */
	function getPresetPermissions( $preset, $preset_flags = [], $force_system_presets = true ) {
		$key = Option::getByValue( $preset, $this->getOptions( 'preset' ) );
		if ( $key !== false ) {
			$preset = $key;
		}

		//Always add system presets when using the Permission wizard, so employees can login and such.
		//However when upgrading this causes a problem as it resets custom permission groups.
		if ( $force_system_presets == true ) {
			$preset_flags[] = 0;
		}
		asort( $preset_flags );

		Debug::Text( 'Preset: ' . $preset, __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $preset_flags, 'Preset Flags... ', __FILE__, __LINE__, __METHOD__, 10 );

		if ( !isset( $preset ) || $preset == '' || $preset == -1 ) {
			Debug::Text( 'No Preset set... Skipping!', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$preset_permissions = [
				5  => //Role: Terminated Employee
						[
								0  => //Module: System
										[
												'system'          => [
														'login' => true,
												],
												'user'            => [
														'enabled'                 => true,
														'view_own'                => true,
														'edit_own'                => true,
														'edit_own_password'       => true,
														'edit_own_phone_password' => true,
												],
												'user_preference' => [
														'enabled'    => true,
														'view_own'   => true,
														'add'        => true,
														'edit_own'   => true,
														'delete_own' => true,
												],
												'request'         => [
														'enabled'      => true,
														'view_own'     => true,
														'add'          => true,
														'add_advanced' => true,
														'edit_own'     => false,
														'delete_own'   => false,
												],
												'message'         => [
														'enabled'    => true,
														'view_own'   => true,
														'add'        => true,
														'edit_own'   => true,
														'delete_own' => false,
												],
												'help'            => [
														'enabled' => true,
														'view'    => true,
												],

										],
								10 => //Module: Scheduling
										[
												'schedule' => [
														'enabled'  => true,
														'view_own' => true,
												],
												'accrual'  => [
														'enabled'  => true,
														'view_own' => true,
												],
												'absence'  => [
														'enabled'  => true,
														'view_own' => true,
												],
										],
								20 => //Module: Time & Attendance
										[
												'punch'   => [
														'enabled'           => true,
														'view_own'          => true,
														'add'               => false,
														'verify_time_sheet' => false,
														'punch_in_out'      => false,
														'punch_timesheet'   => true,
												],
												'accrual' => [
														'enabled'  => true,
														'view_own' => true,
												],
												'absence' => [
														'enabled'  => true,
														'view_own' => true,
												],

										],
								30 => //Module: Payroll
										[
												'user'                           => [
														'enabled' => true,
												],
												'pay_stub'                       => [
														'enabled'  => true,
														'view_own' => true,
												],
												'government_document'            => [
														'enabled'  => true,
														'view_own' => true,
												],
												'remittance_destination_account' => [
														'enabled'    => true,
														'view_own'   => true,
														'add'        => true,
														'edit_own'   => false,
														'delete_own' => false,
												],
										],
								40 => //Module: Job Costing
										[
												'schedule' => [
														'edit_job'      => true,
														'edit_job_item' => true,
												],
												'punch'    => [
														'edit_job'          => true,
														'edit_job_item'     => true,
														'edit_quantity'     => true,
														'edit_bad_quantity' => true,
												],
												'job'      => [
														'enabled' => true,
												],
										],
								50 => //Module: Document Management
										[
												'document' => [
														'enabled' => true,
														'view'    => true,
												],
										],
								60 => //Module: Invoicing
										[],
								70 => //Module: Human Resources
										[],
								75 => //Module: Recruitement
										[],
								80 => //Module: Expenses
										[
												'user_expense' => [
														'enabled'    => true,
														'view_own'   => true,
														'add'        => false,
														'edit_own'   => false,
														'delete_own' => false,
												],
										],
						],
				10 => //Role: Regular Employee
						[
								0  => //Module: System
										[
												'system'          => [
														'login' => true,
												],
												'user'            => [
														'enabled'                 => true,
														'view_own'                => true,
														'edit_own'                => true,
														'edit_own_password'       => true,
														'edit_own_phone_password' => true,
												],
												'user_preference' => [
														'enabled'    => true,
														'view_own'   => true,
														'add'        => true,
														'edit_own'   => true,
														'delete_own' => true,
												],
												'request'         => [
														'enabled'      => true,
														'view_own'     => true,
														'add'          => true,
														'add_advanced' => true,
														'edit_own'     => true,
														'delete_own'   => true,
												],
												'message'         => [
														'enabled'    => true,
														'view_own'   => true,
														'add'        => true,
														'edit_own'   => true,
														'delete_own' => true,
												],
												'help'            => [
														'enabled' => true,
														'view'    => true,
												],

										],
								10 => //Module: Scheduling
										[
												'schedule' => [
														'enabled'         => true,
														'view_own'        => true,
														'edit_branch'     => true, //Allows the user to see the branch column by default.
														'edit_department' => true, //Allows the user to see the department column by default.
												],
												'accrual'  => [
														'enabled'  => true,
														'view_own' => true,
												],
												'absence'  => [
														'enabled'  => true,
														'view_own' => true,
												],
										],
								20 => //Module: Time & Attendance
										[
												'punch'   => [
														'enabled'           => true,
														'view_own'          => true,
														'add'               => true,
														'verify_time_sheet' => true,
														'punch_in_out'      => true,
														'edit_transfer'     => true,
														'edit_branch'       => true,
														'edit_department'   => true,
														'edit_note'         => true,
														'edit_other_id1'    => true,
														'edit_other_id2'    => true,
														'edit_other_id3'    => true,
														'edit_other_id4'    => true,
														'edit_other_id5'    => true,
														'punch_timesheet'   => true,
												],
												'accrual' => [
														'enabled'  => true,
														'view_own' => true,
												],
												'absence' => [
														'enabled'  => true,
														'view_own' => true,
												],

										],
								30 => //Module: Payroll
										[
												'user'                           => [
														'enabled' => true,
														//'edit_own_bank' => TRUE,
												],
												'pay_stub'                       => [
														'enabled'  => true,
														'view_own' => true,
												],
												'government_document'            => [
														'enabled'  => true,
														'view_own' => true,
												],
												'remittance_destination_account' => [
														'enabled'    => true,
														'view_own'   => true,
														'add'        => true,
														'edit_own'   => true,
														'delete_own' => true,
												],
										],
								40 => //Module: Job Costing
										[
												'schedule' => [
														'edit_job'      => true,
														'edit_job_item' => true,
												],
												'punch'    => [
														'edit_job'          => true,
														'edit_job_item'     => true,
														'edit_quantity'     => true,
														'edit_bad_quantity' => true,
												],
												'job'      => [
														'enabled' => true,
												],
										],
								50 => //Module: Document Management
										[
												'document' => [
														'enabled' => true,
														'view'    => true,
												],
										],
								60 => //Module: Invoicing
										[],
								70 => //Module: Human Resources
										[],
								75 => //Module: Recruitement
										[],
								80 => //Module: Expenses
										[
												'user_expense' => [
														'enabled'    => true,
														'view_own'   => true,
														'add'        => true,
														'edit_own'   => true, //Allow editing expenses once they are submitted, but not once authorized/declined. This is required to add though.
														'delete_own' => true,
												],
										],
						],
				12 => //Role: Regular Employee (Manual Punch)
						[
								20 => //Module: Time & Attendance
										[
												'punch'   => [
														'edit_own'   => true,
														'delete_own' => true,
												],
												'absence' => [
														'add'        => true,
														'edit_own'   => true,
														'delete_own' => true,
												],
										],
						],
				14 => //Role: Regular Employee (Manual TimeSheet)
						[
								20 => //Module: Time & Attendance
										[
												'punch' => [
														'manual_timesheet' => true,
												],
										],
						],
				18 => //Role: Supervisor (Subordinates Only)
						[
								0  => //Module: System
										[
												'user'                 => [
														'add'                      => true, //Can only add user with permissions level equal or lower.
														'view_child'               => true,
														'edit_child'               => true,
														'edit_advanced'            => true,
														'enroll_child'             => true,
														//'delete_child' => TRUE, //Disable deleting of users by default as a precautionary measure.
														'edit_pay_period_schedule' => true,
														'edit_permission_group'    => true,
														'edit_policy_group'        => true,
														'edit_hierarchy'           => true,
												],
												'user_preference'      => [
														'view_child' => true,
														'edit_child' => true,
												],
												'request'              => [
														'view_child'   => true,
														'edit_child'   => true,
														'delete_child' => true,
														'authorize'    => true,
												],
												'authorization'        => [
														'enabled' => true,
														'view'    => true,
												],
												'message'              => [
														'add_advanced'  => true,
														'send_to_child' => true,
												],
												'report'               => [
														'enabled'               => true,
														'view_user_information' => true,
														//'view_user_detail' => TRUE,
														'view_user_barcode'     => true,
												],
												'report_custom_column' => [
														'enabled'    => true,
														'view_own'   => true,
														'add'        => true,
														'edit_own'   => true,
														'delete_own' => true,
												],
										],
								10 => //Module: Scheduling
										[
												'schedule'                    => [
														'add'             => true,
														'view_child'      => true,
														'view_open'       => true,
														'edit_child'      => true,
														'delete_child'    => true,
														'edit_branch'     => true,
														'edit_department' => true,
												],
												'recurring_schedule_template' => [
														'enabled'    => true,
														'view_own'   => true,
														'add'        => true,
														'edit_own'   => true,
														'delete_own' => true,
												],
												'recurring_schedule'          => [
														'enabled'      => true,
														'view_child'   => true,
														'add'          => true,
														'edit_child'   => true,
														'delete_child' => true,
												],
												'absence'                     => [
														'add'             => true,
														'view_child'      => true,
														'edit_child'      => true,
														'delete_child'    => true,
														'edit_branch'     => true,
														'edit_department' => true,
												],
												'accrual'                     => [
														'add'          => true,
														'view_child'   => true,
														'edit_own'     => false,
														'edit_child'   => true,
														'delete_own'   => false,
														'delete_child' => true,
												],
												'report'                      => [
														'view_schedule_summary'        => true,
														'view_accrual_balance_summary' => true,
												],

										],
								20 => //Module: Time & Attendance
										[
												'punch'   => [
														'view_child'   => true,
														'edit_child'   => true,
														'delete_child' => true,
														'authorize'    => true,
												],
												'absence' => [
														'add'             => true,
														'view_child'      => true,
														'edit_own'        => false,
														'edit_child'      => true,
														'edit_branch'     => true,
														'edit_department' => true,
														'delete_own'      => false,
														'delete_child'    => true,
												],
												'accrual' => [
														'view_child'   => true,
														'add'          => true,
														'edit_child'   => true,
														'delete_child' => true,
												],
												'report'  => [
														'view_active_shift'            => true,
														'view_timesheet_summary'       => true,
														'view_punch_summary'           => true,
														'view_exception_summary'       => true,
														'view_accrual_balance_summary' => true,
												],

										],
								30 => //Module: Payroll
										[],
								40 => //Module: Job Costing
										[
												'schedule'   => [
														'edit_job'      => true,
														'edit_job_item' => true,
												],
												'absence'    => [
														'edit_job'      => true,
														'edit_job_item' => true,
												],
												'job'        => [
														'add'        => true,
														'view'       => true, //Must be able to view all jobs so they can punch in/out.
														'view_own'   => true,
														'edit_own'   => true,
														'delete_own' => true,
												],
												'job_item'   => [
														'enabled'    => true,
														'view'       => true,
														'add'        => true,
														'edit_own'   => true,
														'delete_own' => true,
												],
												'job_report' => [
														'enabled'                   => true,
														'view_job_summary'          => true,
														'view_job_analysis'         => true,
														'view_job_payroll_analysis' => true,
														'view_job_barcode'          => true,
												],
												'geo_fence'  => [
														'enabled'    => true,
														'add'        => true,
														'view'       => true, //Must be able to view all fences so they can punch in/out.
														'view_own'   => true,
														'edit_own'   => true,
														'delete_own' => true,
												],
										],
								50 => //Module: Document Management
										[
												'document' => [
														'add'            => true,
														'view_private'   => true,
														'edit'           => true,
														'edit_private'   => true,
														'delete'         => true,
														'delete_private' => true,
												],

										],
								60 => //Module: Invoicing
										[
												'client'         => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'client_payment' => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'transaction'    => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'invoice'        => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
										],
								70 => //Module: Human Resources
										[
												'user_contact'    => [
														'enabled'      => true,
														'add'          => true, //Can only add user with permissions level equal or lower.
														'view_child'   => true,
														'edit_child'   => true,
														'delete_child' => true,
												],
												'qualification'   => [
														'enabled'      => true,
														'add'          => true,
														'view'         => true,
														'edit_own'     => true,
														'delete_own'   => true,
														'view_child'   => true,
														'edit_child'   => true,
														'delete_child' => true,
												],
												'user_education'  => [
														'enabled'      => true,
														'add'          => true,
														'view'         => true,
														'edit_own'     => true,
														'delete_own'   => true,
														'view_child'   => true,
														'edit_child'   => true,
														'delete_child' => true,
												],
												'user_license'    => [
														'enabled'      => true,
														'add'          => true,
														'view'         => true,
														'edit_own'     => true,
														'delete_own'   => true,
														'view_child'   => true,
														'edit_child'   => true,
														'delete_child' => true,
												],
												'user_skill'      => [
														'enabled'      => true,
														'add'          => true,
														'view'         => true,
														'edit_own'     => true,
														'delete_own'   => true,
														'view_child'   => true,
														'edit_child'   => true,
														'delete_child' => true,
												],
												'user_membership' => [
														'enabled'      => true,
														'add'          => true,
														'view'         => true,
														'edit_own'     => true,
														'delete_own'   => true,
														'view_child'   => true,
														'edit_child'   => true,
														'delete_child' => true,
												],
												'user_language'   => [
														'enabled'      => true,
														'add'          => true,
														'view'         => true,
														'edit_own'     => true,
														'delete_own'   => true,
														'view_child'   => true,
														'edit_child'   => true,
														'delete_child' => true,
												],
												'kpi'             => [
														'enabled'      => true,
														'add'          => true,
														'view'         => true,
														'edit_own'     => true,
														'delete_own'   => true,
														'view_child'   => true,
														'edit_child'   => true,
														'delete_child' => true,
												],
												'user_review'     => [
														'enabled'      => true,
														'add'          => true,
														'view_own'     => true,
														'view_child'   => true,
														'edit_child'   => true,
														'delete_child' => true,
												],
												'hr_report'       => [
														'enabled'            => true,
														'user_qualification' => true,
														'user_review'        => true,
												],

										],
								75 => //Module: Recruitement
										[
												'job_vacancy'        => [
														'enabled'      => true,
														'add'          => true,
														'view_child'   => true,
														'edit_child'   => true,
														'delete_child' => true,
												],
												'job_applicant'      => [
														'enabled'      => true,
														'add'          => true,
														'view_child'   => true,
														'edit_child'   => true,
														'delete_child' => true,
												],
												'job_application'    => [
														'enabled'      => true,
														'add'          => true,
														'view_child'   => true,
														'edit_child'   => true,
														'delete_child' => true,
												],
												'recruitment_report' => [
														'enabled'          => true,
														'user_recruitment' => true,
												],

										],
								80 => //Module: Expenses
										[
												'user_expense' => [
														'view_child'   => true,
														'add'          => true,
														'edit_child'   => true,
														'delete_child' => true,
														'authorize'    => true,
												],
										],
						],
				20 => //Role: Supervisor (All Employees)
						[
								0  => //Module: System
										[
												'user'            => [
														'view'   => true,
														'edit'   => true,
														'enroll' => true,
														//'delete' => TRUE, //Disable deleting of users by default as a precautionary measure.
												],
												'user_preference' => [
														'view' => true,
														'edit' => true,
												],
												'request'         => [
														'view'   => true,
														'edit'   => true,
														'delete' => true,
												],
												'message'         => [
														'send_to_any' => true,
												],
										],
								10 => //Module: Scheduling
										[
												'schedule'                    => [
														'view'   => true,
														'edit'   => true,
														'delete' => true,
												],
												'recurring_schedule_template' => [
														'view'   => true,
														'edit'   => true,
														'delete' => true,
												],
												'recurring_schedule'          => [
														'view'   => true,
														'edit'   => true,
														'delete' => true,
												],
												'absence'                     => [
														'view'   => true,
														'edit'   => true,
														'delete' => true,
												],
												'accrual'                     => [
														'view'   => true,
														'edit'   => true,
														'delete' => true,
												],

										],
								20 => //Module: Time & Attendance
										[
												'punch'   => [
														'view'   => true,
														'edit'   => true,
														'delete' => true,
														'edit_location'=> true,
												],
												'absence' => [
														'view'       => true,
														'edit'       => true,
														'delete'     => true,
														'edit_own'   => true,
														'delete_own' => true,
												],
												'accrual' => [
														'view'   => true,
														'edit'   => true,
														'delete' => true,
												],
										],
								30 => //Module: Payroll
										[],
								40 => //Module: Job Costing
										[
												'job'       => [
														'view'   => true,
														'edit'   => true,
														'delete' => true,
												],
												'job_item'  => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'geo_fence' => [
														'view'   => true,
														'edit'   => true,
														'delete' => true,
												],
										],
								50 => //Module: Document Management
										[],
								60 => //Module: Invoicing
										[],
								70 => //Module: Human Resources
										[
												'user_contact'    => [
														'add'    => true,
														'view'   => true,
														'edit'   => true,
														'delete' => true,
												],
												'qualification'   => [
														'edit'   => true,
														'delete' => true,
												],
												'user_education'  => [
														'edit'   => true,
														'delete' => true,
												],
												'user_license'    => [
														'edit'   => true,
														'delete' => true,
												],
												'user_skill'      => [
														'edit'   => true,
														'delete' => true,
												],
												'user_membership' => [
														'edit'   => true,
														'delete' => true,
												],
												'user_language'   => [
														'edit'   => true,
														'delete' => true,
												],
												'kpi'             => [
														'edit'   => true,
														'delete' => true,
												],
												'user_review'     => [
														'view'   => true,
														'edit'   => true,
														'delete' => true,
												],
										],
								75 => //Module: Recruitement
										[
												'job_vacancy'     => [
														'view'   => true,
														'edit'   => true,
														'delete' => true,
												],
												'job_applicant'   => [
														'view'   => true,
														'edit'   => true,
														'delete' => true,
												],
												'job_application' => [
														'view'   => true,
														'edit'   => true,
														'delete' => true,
												],
										],
								80 => //Module: Expenses
										[
												'user_expense' => [
														'view'   => true,
														'edit'   => true,
														'delete' => true,
												],
										],
						],
				25 => //Role: HR Manager
						[
								0  => //Module: System
										[],
								10 => //Module: Scheduling
										[],
								20 => //Module: Time & Attendance
										[],
								30 => //Module: Payroll
										[],
								40 => //Module: Job Costing
										[],
								50 => //Module: Document Management
										[],
								60 => //Module: Invoicing
										[],
								70 => //Module: Human Resources
										[
												'qualification' => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
										],
								75 => //Module: Recruitement
										[],
								80 => //Module: Expenses
										[],
						],
				30 => //Role: Payroll Administrator
						[
								0  => //Module: System
										[
												'company'             => [
														'enabled'  => true,
														'view_own' => true,
														'edit_own' => true,
														//'edit_own_bank' => TRUE
												],
												'legal_entity'        => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'user'                => [
														'add'      => true,
														//'edit_bank' => TRUE,
														'view_sin' => true,
												],
												'wage'                => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'pay_period_schedule' => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
														'assign'  => true,
												],
												'pay_code'            => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'pay_formula_policy'  => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'report'              => [
														'view_system_log' => true,
												],
										],
								10 => //Module: Scheduling
										[],
								20 => //Module: Time & Attendance
										[],
								30 => //Module: Payroll
										[
												'user_tax_deduction'             => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'roe'                            => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'company_tax_deduction'          => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'pay_stub_account'               => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'payroll_remittance_agency'      => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'remittance_source_account'      => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'remittance_destination_account' => [
													//'enabled' => TRUE,
													'view'   => true,
													'add'    => true,
													'edit'   => true,
													'delete' => true,
												],
												'pay_stub'                       => [
														'view'   => true,
														'add'    => true,
														'edit'   => true,
														'delete' => true,
												],
												'government_document'            => [
														'view'   => true,
														'add'    => true,
														'edit'   => true,
														'delete' => true,
												],
												'pay_stub_amendment'             => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'report'                         => [
														'view_pay_stub_summary'       => true,
														'view_payroll_export'         => true,
														//'view_employee_pay_stub_summary' => TRUE,
														'view_remittance_summary'     => true,
														'view_wages_payable_summary'  => true,
														'view_t4_summary'             => true,
														'view_generic_tax_summary'    => true,
														'view_form941'                => true,
														'view_form940'                => true,
														'view_form940ez'              => true,
														'view_form1099misc'           => true,
														'view_formW2'                 => true,
														'view_affordable_care'        => true,
														'view_general_ledger_summary' => true,
												],
										],
								40 => //Module: Job Costing
										[],
								50 => //Module: Document Management
										[],
								60 => //Module: Invoicing
										[
												'product'         => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'tax_policy'      => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'shipping_policy' => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'area_policy'     => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'payment_gateway' => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'invoice_report'  => [
														'enabled'                  => true,
														'view_transaction_summary' => true,
												],
										],
								70 => //Module: Human Resources
										[],
								75 => //Module: Recruitement
										[],
								80 => //Module: Expenses
										[
												'report' => [
														'view_expense' => true,
												],
										],
						],
				40 => //Role: Administrator
						[
								0  => //Module: System
										[
												'user'                         => [
														'timeclock_admin' => true,
												],
												'user_date_total'              => [
														'enabled' => true,
														'view'    => true,
														//By default allow them to view Accumulated Time, but not add/edit/delete because they likely don't understand the implications.
														//'add' => TRUE,
														//'edit' => TRUE,
														//'delete' => TRUE,
												],
												'policy_group'                 => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'contributing_pay_code_policy' => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'contributing_shift_policy'    => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'regular_time_policy'          => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'schedule_policy'              => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'meal_policy'                  => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'break_policy'                 => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'over_time_policy'             => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'premium_policy'               => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'accrual_policy'               => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'absence_policy'               => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'round_policy'                 => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'exception_policy'             => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'holiday_policy'               => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'currency'                     => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'branch'                       => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'department'                   => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
														'assign'  => true,
												],
												'station'                      => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
														'assign'  => true,
												],
												'report'                       => [//'view_shift_actual_time' => TRUE,
												],
												'hierarchy'                    => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'other_field'                  => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'permission'                   => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
												'report_custom_column'         => [
														'view'   => true,
														'edit'   => true,
														'delete' => true,
												],
										],
								10 => //Module: Scheduling
										[],
								20 => //Module: Time & Attendance
										[],
								30 => //Module: Payroll
										[],
								40 => //Module: Job Costing
										[],
								50 => //Module: Document Management
										[],
								60 => //Module: Invoicing
										[
												'invoice_config' => [
														'enabled' => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],

										],
								70 => //Module: Human Resources
										[],
								75 => //Module: Recruitement
										[],
								80 => //Module: Expenses
										[
												'expense_policy' => [
														'enabled' => true,
														'view'    => true,
														'add'     => true,
														'edit'    => true,
														'delete'  => true,
												],
										],
						],
		];

		$retarr = [];

		//Loop over each preset adding the permissions together for that preset and the role that is selected.
		$preset_options = array_keys( Misc::trimSortPrefix( $this->getOptions( 'preset' ) ) );
		if ( is_array( $preset_options ) ) {
			foreach ( $preset_options as $preset_option ) {
				if ( isset( $preset_permissions[$preset_option] ) && $preset_option <= $preset ) {
					foreach ( $preset_flags as $preset_flag ) {
						if ( isset( $preset_permissions[$preset_option][$preset_flag] ) ) {
							Debug::Text( 'Applying Preset: ' . $preset_option . ' Preset Flag: ' . $preset_flag, __FILE__, __LINE__, __METHOD__, 10 );
							$retarr = Misc::arrayMergeRecursive( $retarr, $preset_permissions[$preset_option][$preset_flag] );
						}
					}
				}
			}
		}

		return $retarr;
	}

	/**
	 * This is used by CompanyFactory to create the initial permissions when creating a new company.
	 * Also by the Quick Start wizard.
	 * @param string $permission_control_id UUID
	 * @param $preset
	 * @param $preset_flags
	 * @return bool
	 */
	function applyPreset( $permission_control_id, $preset, $preset_flags ) {
		$preset_permissions = $this->getPresetPermissions( $preset, $preset_flags );

		if ( !is_array( $preset_permissions ) ) {
			return false;
		}

		$this->setPermissionControl( $permission_control_id );

		$product_edition = $this->getPermissionControlObject()->getCompanyObject()->getProductEdition();
		//Debug::Arr($preset_flags, 'Preset: '. $preset .' Product Edition: '. $product_edition, __FILE__, __LINE__, __METHOD__, 10);

		$pf = TTnew( 'PermissionFactory' ); /** @var PermissionFactory $pf */
		$pf->StartTransaction();

		//Delete all previous permissions for this control record..
		$this->deletePermissions( $this->getCompany(), $permission_control_id );

		$created_date = time();
		foreach ( $preset_permissions as $section => $permissions ) {
			foreach ( $permissions as $name => $value ) {
				if ( $pf->isIgnore( $section, $name, $product_edition ) == false ) {
					//Put all inserts into a single query, this speeds things up greatly (9s to less than .5s),
					//but we are by-passing the audit log so make sure we add a new entry describing what took place.
					$ph[] = $pf->getNextInsertId(); //This needs work before UUID and after.
					$ph[] = $permission_control_id;
					$ph[] = $section;
					$ph[] = $name;
					$ph[] = (int)$value;
					$ph[] = $created_date;
					$data[] = '(?, ?, ?, ?, ?, ?)';
					/*
					//Debug::Text('Setting Permission - Section: '. $section .' Name: '. $name .' Value: '. (int)$value, __FILE__, __LINE__, __METHOD__, 10);
					$pf->setPermissionControl( $permission_control_id );
					$pf->setSection( $section );
					$pf->setName( $name );
					$pf->setValue( (int)$value );
					if ( $pf->isValid() ) {
						$pf->save();
					} else {
						Debug::Text('ERROR: Setting Permission - Section: '. $section .' Name: '. $name .' Value: '. (int)$value, __FILE__, __LINE__, __METHOD__, 10);
					}
					*/
				}
			}
		}

		$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
		if ( isset( $data ) ) {
			//Save data in a single SQL query.
			$query = 'INSERT INTO ' . $this->getTable() . '(ID, PERMISSION_CONTROL_ID, SECTION, NAME, VALUE, CREATED_DATE) VALUES' . implode( ',', $data );
			//Debug::Text('Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);
			$this->ExecuteSQL( $query, $ph );

			//Debug::Text('Logged detail records in: '. (microtime(TRUE) - $start_time), __FILE__, __LINE__, __METHOD__, 10);
			TTLog::addEntry( $permission_control_id, 20, TTi18n::getText( 'Applying Permission Preset' ) . ': ' . Option::getByKey( $preset, $this->getOptions( 'preset' ) ), null, $pclf->getTable(), $this );
		}
		unset( $ph, $data, $created_date, $preset_permissions, $permissions, $section, $name, $value );

		//Clear cache for all users assigned to this permission_control_id
		$pclf->getById( $permission_control_id );
		if ( $pclf->getRecordCount() > 0 ) {
			$pc_obj = $pclf->getCurrent();

			if ( is_array( $pc_obj->getUser() ) ) {
				foreach ( $pc_obj->getUser() as $user_id ) {
					$pf->clearCache( $user_id, $this->getCompany() );
				}
			}
		}
		unset( $pclf, $pc_obj, $user_id );

		//$pf->FailTransaction();
		$pf->CommitTransaction();

		return true;
	}

	/**
	 * @param string $company_id            UUID
	 * @param string $permission_control_id UUID
	 * @return bool
	 */
	function deletePermissions( $company_id, $permission_control_id ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $permission_control_id == '' ) {
			return false;
		}

		$plf = TTnew( 'PermissionListFactory' ); /** @var PermissionListFactory $plf */
		$plf->getByCompanyIDAndPermissionControlId( $company_id, $permission_control_id );
		foreach ( $plf as $permission_obj ) {
			$permission_obj->Delete();
			$this->removeCache( $this->getCacheID() );
		}

		return true;
	}

	/**
	 * @param $section
	 * @param null $name
	 * @param int $product_edition
	 * @return bool
	 */
	static function isIgnore( $section, $name = null, $product_edition = 10 ) {
		global $current_company;

		//Ignore by default
		if ( $section == '' ) {
			return true;
		}

		//Debug::Text(' Product Edition: '. $product_edition .' Primary Company ID: '. PRIMARY_COMPANY_ID, __FILE__, __LINE__, __METHOD__, 10);
		if ( $product_edition == TT_PRODUCT_ENTERPRISE ) { //Enterprise
			//Company ignore permissions must be enabled always, and unset below if this is the primary company
			$ignore_permissions = [
					'help'    => 'ALL',
					'company' => [ 'add', 'delete', 'delete_own', 'undelete', 'view', 'edit', 'login_other_user' ],
			];
		} else if ( $product_edition == TT_PRODUCT_CORPORATE ) { //Corporate
			//Company ignore permissions must be enabled always, and unset below if this is the primary company
			$ignore_permissions = [
					'help'               => 'ALL',
					'company'            => [ 'add', 'delete', 'delete_own', 'undelete', 'view', 'edit', 'login_other_user' ],
					'job_vacancy'        => 'ALL',
					'job_applicant'      => 'ALL',
					'job_application'    => 'ALL',
					'user_expense'       => 'ALL',
					'expense_policy'     => 'ALL',
					'report'             => [ 'view_expense' ],
					'recruitment_report' => 'ALL',
			];
		} else if ( $product_edition == TT_PRODUCT_PROFESSIONAL ) { //Professional
			$ignore_permissions = [
					'help'               => 'ALL',
					'company'            => [ 'add', 'delete', 'delete_own', 'undelete', 'view', 'edit', 'login_other_user' ],
					'schedule'           => [ 'edit_job', 'edit_job_item' ],
					'punch'              => [ 'edit_job', 'edit_job_item', 'edit_quantity', 'edit_bad_quantity' ],
					'absence'            => [ 'edit_job', 'edit_job_item' ],
					'job_item'           => 'ALL',
					'invoice_config'     => 'ALL',
					'client'             => 'ALL',
					'client_payment'     => 'ALL',
					'product'            => 'ALL',
					'tax_policy'         => 'ALL',
					'area_policy'        => 'ALL',
					'shipping_policy'    => 'ALL',
					'payment_gateway'    => 'ALL',
					'transaction'        => 'ALL',
					'job_report'         => 'ALL',
					'invoice_report'     => 'ALL',
					'invoice'            => 'ALL',
					'geo_fence'          => 'ALL',
					'job'                => 'ALL',
					'document'           => 'ALL',
					'job_vacancy'        => 'ALL',
					'job_applicant'      => 'ALL',
					'job_application'    => 'ALL',
					'user_expense'       => 'ALL',
					'expense_policy'     => 'ALL',
					'report'             => [ 'view_expense' ],
					'recruitment_report' => 'ALL',
			];
		} else if ( $product_edition == TT_PRODUCT_COMMUNITY ) { //Community
			//Company ignore permissions must be enabled always, and unset below if this is the primary company
			$ignore_permissions = [
					'help'                => 'ALL',
					'company'             => [ 'add', 'delete', 'delete_own', 'undelete', 'view', 'edit', 'login_other_user' ],
					'schedule'            => [ 'edit_job', 'edit_job_item' ],
					'punch'               => [ 'manual_timesheet', 'edit_job', 'edit_job_item', 'edit_quantity', 'edit_bad_quantity' ],
					'user_date_total'     => 'ALL',
					'absence'             => [ 'edit_job', 'edit_job_item' ],
					'job_item'            => 'ALL',
					'invoice_config'      => 'ALL',
					'client'              => 'ALL',
					'client_payment'      => 'ALL',
					'product'             => 'ALL',
					'tax_policy'          => 'ALL',
					'area_policy'         => 'ALL',
					'shipping_policy'     => 'ALL',
					'payment_gateway'     => 'ALL',
					'transaction'         => 'ALL',
					'job_report'          => 'ALL',
					'invoice_report'      => 'ALL',
					'invoice'             => 'ALL',
					'geo_fence'           => 'ALL',
					'job'                 => 'ALL',
					'document'            => 'ALL',
					'government_document' => 'ALL',
					'job_vacancy'         => 'ALL',
					'job_applicant'       => 'ALL',
					'job_application'     => 'ALL',
					'user_expense'        => 'ALL',
					'expense_policy'      => 'ALL',
					'report'              => [ 'view_expense' ],
					'recruitment_report'  => 'ALL',
			];
		}

		//If they are currently logged in as the primary company ID, allow multiple company permissions.
		if ( isset( $current_company ) && $current_company->getProductEdition() > TT_PRODUCT_COMMUNITY && $current_company->getId() == PRIMARY_COMPANY_ID ) {
			unset( $ignore_permissions['company'] );
		}

		if ( isset( $ignore_permissions[$section] )
				&&
				(
						(
								$name != ''
								&&
								( $ignore_permissions[$section] == 'ALL'
										|| ( is_array( $ignore_permissions[$section] ) && in_array( $name, $ignore_permissions[$section] ) ) )
						)
						||
						(
								$name == ''
								&&
								$ignore_permissions[$section] == 'ALL'
						)
				)

		) {
			//Debug::Text(' IGNORING... Section: '. $section .' Name: '. $name, __FILE__, __LINE__, __METHOD__, 10);
			return true;
		} else {
			//Debug::Text(' NOT IGNORING... Section: '. $section .' Name: '. $name, __FILE__, __LINE__, __METHOD__, 10);
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function preSave() {
		//Just update any existing permissions. It would probably be faster to delete them all and re-insert though.
		$plf = TTnew( 'PermissionListFactory' ); /** @var PermissionListFactory $plf */
		$obj = $plf->getByCompanyIdAndPermissionControlIdAndSectionAndName( $this->getCompany(), $this->getPermissionControl(), $this->getSection(), $this->getName() )->getCurrent();
		$this->setId( $obj->getId() );

		return true;
	}

	/**
	 * @return string
	 */
	function getCacheID() {
		$cache_id = 'permission_query_' . $this->getSection() . $this->getName() . $this->getPermissionControl() . $this->getCompany();

		return $cache_id;
	}

	/**
	 * @param string $user_id    UUID
	 * @param string $company_id UUID
	 * @return bool
	 */
	function clearCache( $user_id, $company_id ) {
		if ( $user_id == '' OR $company_id == '' ) {
			return false;
		}

		Debug::Text( ' Clearing Cache for User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );

		$cache_id = 'permission_level_' . $user_id . '_' . $company_id;
		$this->removeCache( $cache_id );

		$cache_id = 'permission_all_' . $user_id . '_' . $company_id;
		$retval = $this->removeCache( $cache_id );

		return $retval;
	}

	/**
	 * @return bool
	 */
	function getEnableSectionAndNameValidation() {
		if ( isset( $this->enable_section_and_name_validation ) ) {
			return $this->enable_section_and_name_validation;
		}

		return true; //Default to TRUE
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableSectionAndNameValidation( $bool ) {
		$this->enable_section_and_name_validation = $bool;

		return true;
	}


	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Permission Group
		if ( $this->getPermissionControl() == TTUUID::getZeroID() ) {
			$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
			$this->Validator->isResultSetWithRows( 'permission_control',
												   $pclf->getByID( $this->getPermissionControl() ),
												   TTi18n::gettext( 'Permission Group is invalid' )
			);
		}

		if ( $this->getEnableSectionAndNameValidation() == true ) {
			// Section
			if ( $this->getGenericTempDataValue( 'section' ) !== false ) {
				$this->Validator->inArrayKey( 'section',
											  $this->getGenericTempDataValue( 'section' ),
											  TTi18n::gettext( 'Incorrect section' ),
											  $this->getOptions( 'section' )
				);
			}
			// Permission Name
			if ( $this->getGenericTempDataValue( 'name' ) !== false ) {
				$this->Validator->inArrayKey( 'name',
											  $this->getGenericTempDataValue( 'name' ),
											  TTi18n::gettext( 'Incorrect permission name' ),
											  $this->getOptions( 'name', $this->getSection() )
				);
			}
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		//$cache_id = 'permission_query_'.$this->getSection().$this->getName().$this->getUser().$this->getCompany();
		//$this->removeCache( $this->getCacheID() );

		return true;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		if ( $this->getValue() == true ) {
			$value_display = TTi18n::getText( 'ALLOW' );
		} else {
			$value_display = TTi18n::getText( 'DENY' );
		}

		return TTLog::addEntry( $this->getPermissionControl(), $log_action, TTi18n::getText( 'Section' ) . ': ' . Option::getByKey( $this->getSection(), $this->getOptions( 'section' ) ) . ' Name: ' . Option::getByKey( $this->getName(), $this->getOptions( 'name', $this->getSection() ) ) . ' Value: ' . $value_display, null, $this->getTable() );
	}
}

?>
