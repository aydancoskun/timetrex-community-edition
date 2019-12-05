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
 * Class PurgeDatabase
 */
class PurgeDatabase {
	static $parent_table_column_map = array(
											'users' => 'user_id',
											'report_schedule' => 'user_report_data_id',
											);

	static $parent_table_map_array = array(
										'absence_policy' => array(
														'company',
														),
										'accrual' => array(
														'users',
														),
										'accrual_balance' => array(
														'users',
														//'accrual_policy'
														'accrual_policy_account'
														),
										'accrual_policy' => array(
														'company'
														),
										'accrual_policy_account' => array(
														'company',
														),
										'accrual_policy_milestone' => array(
														'accrual_policy'
														),
										'area_policy' => array(
														'company'
														),
										'area_policy_location' => array(
														'area_policy'
														),
										//Can't automatically purge this table, as user_id is often NULL for company wide settings.
										//'bank_account' => array(
										//				'company',
										//				'users'
										//				),
										'branch' => array(
														'company'
														),
										'bread_crumb' => array(
														'users'
														),
										'break_policy' => array(
														'company'
														),
										'client' => array(
														'company',
														),
										'client_balance' => array(
														'client'
														),
										'client_contact' => array(
														'client',
														),
										'client_group' => array(
														'company'
														),
										'client_payment' => array(
														'client'
														),
										'legal_entity' => array(
														'company'
														),
										'remittance_source_account' => array(
														'legal_entity',
														),
										'remittance_destination_account' => array(
														'users',
														),
										'payroll_remittance_agency' => array(
														'legal_entity',
														),
										'payroll_remittance_agency_event' => array(
														'payroll_remittance_agency',
														),
										'company_deduction' => array(
														'company',
														),
										'company_deduction_pay_stub_entry_account' => array(
														'company_deduction',
														),
										'company_generic_map' => array(
														'company'
														),
										'company_user_count' => array(
														'company'
														),
										'contributing_pay_code_policy' => array(
														'company',
														),
										'contributing_shift_policy' => array(
														'company',
														),
										'currency' => array(
														'company'
														),
										'currency_rate' => array(
														'currency'
										),
										'department' => array(
														'company'
														),
										'department_branch' => array(
														'branch',
														'department'
														),
										'department_branch_user' => array(
														'users'
														),
										'government_document' => array(
														'users',
														),
										'document' => array(
														'company',
														),
										'document_attachment' => array(
														'document'
														),
										'document_group' => array(
														'company'
														),
										'document_revision' => array(
														'document'
														),
										'exception' => array(
														'exception_policy',
														'users',
														'punch',
														'punch_control'
														),
										'exception_policy' => array(
														'exception_policy_control'
														),
										'exception_policy_control' => array(
														'company'
														),
										'hierarchy_control' => array(
														'company'
														),
										'hierarchy_level' => array(
														'hierarchy_control',
														'users'
														),
										'hierarchy_object_type' => array(
														'hierarchy_control'
														),
										'hierarchy_user' => array(
														'hierarchy_control',
														'users'
														),
										'holiday_policy' => array(
														'company',
														),
										'holiday_policy_recurring_holiday' => array(
														'holiday_policy',
														'recurring_holiday'
														),
										'holidays' => array(
														'holiday_policy'
														),
										'invoice' => array(
														'client',
														),
										'invoice_config' => array(
														'company'
														),
										'invoice_district' => array(
														'company'
														),
										'invoice_transaction' => array(
														'client',
														'product', //Invoice payments are product_id = 0
														'invoice',
														),
										'job' => array(
														'company',
														),
										'job_group' => array(
														'company'
														),
										'job_item' => array(
														'company',
														),
										'job_item_amendment' => array(
														'job'
														),
										'job_item_group' => array(
														'company'
														),
										'geo_fence' => array(
														'company'
														),



										'qualification' => array(
														'company'
														),
										'qualification_group' => array(
														'company'
														),
										'user_education' => array(
														'users'
														),
										'user_license' => array(
														'users'
														),
										'user_skill' => array(
														'users'
														),
										'user_language' => array(
														'users'
														),
										'user_membership' => array(
														'users'
														),
										'user_review_control' => array(
														'users'
														),
										'user_review' => array(
														'user_review_control'
														),
										'kpi' => array(
														'company'
														),
										'kpi_group' => array(
														'company'
														),
										'ethnic_group' => array(
														'company'
														),
										'user_contact' => array(
														'users'
														),
										'user_setting' => array(
														'users'
														),
										'company_setting' => array(
														'company'
														),


										'job_vacancy' => array(
														'company',
														),
										'job_applicant' => array(
														'company',
														),
										'job_application' => array(
														'job_applicant',
														'job_vacancy',
														),
										'job_applicant_location' => array(
														'job_applicant',
														),
										'job_applicant_employment' => array(
														'job_applicant',
														),
										'job_applicant_reference' => array(
														'job_applicant',
														),
										'job_applicant_education' => array(
														'job_applicant',
														),
										'job_applicant_skill' => array(
														'job_applicant',
														),
										'job_applicant_language' => array(
														'job_applicant',
														),
										'job_applicant_membership' => array(
														'job_applicant',
														),
										'job_applicant_license' => array(
														'job_applicant',
														),
										'recruitment_portal_config' => array(
														'company',
														),
										'recruitment_portal_field_map' => array(
														'company',
														),

										'expense_policy' => array(
														'company'
														),
										'user_expense' => array(
														'users',
														'expense_policy',
														),

										'meal_policy' => array(
														'company'
														),
										'message_recipient' => array(
														'users',
														'message_sender'
														),
										'message_sender' => array(
														'users',
														'message_control'
														),
										'other_field' => array(
														'company'
														),
										'over_time_policy' => array(
														'company',
														),
										'pay_code' => array(
														'company',
														),
										'pay_formula_policy' => array(
														'company',
														),
										'pay_period' => array(
														'company',
														'pay_period_schedule'
														),
										'pay_period_schedule' => array(
														'company'
														),
										'pay_period_schedule_user' => array(
														'pay_period_schedule',
														'users'
														),
										'pay_period_time_sheet_verify' => array(
														'pay_period',
														'users'
														),
										'pay_stub' => array(
														'pay_period',
														'users',
														),
										'pay_stub_amendment' => array(
														'users',
														),
										'pay_stub_entry' => array(
														'pay_stub',
														),
										'pay_stub_entry_account' => array(
														'company'
														),
										'pay_stub_transaction' => array(
														'pay_stub',
														),
										'pay_stub_entry_account_link' => array(
														'company'
														),
										'payment_gateway' => array(
														'company'
														),
										'payment_gateway_credit_card_type' => array(
														'payment_gateway'
														),
										'payment_gateway_currency' => array(
														'payment_gateway',
														),
										'permission' => array(
														'permission_control'
														),
										'permission_control' => array(
														'company'
														),
										'permission_user' => array(
														'permission_control',
														'users'
														),
										'policy_group' => array(
														'company',
														),
										'policy_group_user' => array(
														'policy_group',
														'users'
														),
										'premium_policy' => array(
														'company',
														),
										'premium_policy_branch' => array(
														'premium_policy',
														'branch'
														),
										'premium_policy_department' => array(
														'premium_policy',
														'department'
														),
										'premium_policy_job' => array(
														'premium_policy',
														'job'
														),
										'premium_policy_job_group' => array(
														'premium_policy',
														'job_group'
														),
										'premium_policy_job_item' => array(
														'premium_policy',
														'job_item'
														),
										'premium_policy_job_item_group' => array(
														'premium_policy',
														'job_item_group'
														),
										'product' => array(
														'company',
														),
										'product_group' => array(
														'company'
														),
										'product_price' => array(
														'product'
														),
										'punch' => array(
														'punch_control',
														),
										'punch_control' => array(
														'users',
														),
										'recurring_holiday' => array(
														'company'
														),
										'recurring_ps_amendment' => array(
														'company'
														),
										'recurring_ps_amendment_user' => array(
														'recurring_ps_amendment',
														'users'
														),
										'recurring_schedule_control' => array(
														'company',
														),
										'recurring_schedule_template' => array(
														'recurring_schedule_template_control',
														),
										'recurring_schedule_template_control' => array(
														'company'
														),
										'recurring_schedule_user' => array(
														'recurring_schedule_control',
														'users'
														),
										'recurring_schedule' => array(
														'recurring_schedule_control',
														'users',
														'company'
														),
										'regular_time_policy' => array(
														'company',
														),
										'report_schedule' => array(
														'user_report_data'
														),
										'request' => array(
														'users',
														),
										'request_schedule' => array(
														'request',
														),

										'roe' => array(
														'users'
														),
										'round_interval_policy' => array(
														'company'
														),
										'schedule' => array(
														'users',
														'company',
														),
										'schedule_policy' => array(
														'company',
														),
										'shipping_policy' => array(
														'company',
														),
										'shipping_policy_object' => array(
														'shipping_policy'
														),
										'shipping_table_rate' => array(
														'shipping_policy'
														),
										'station' => array(
														'company',
														),
										'station_branch' => array(
														'station',
														'branch'
														),
										'station_department' => array(
														'station',
														'department'
														),
										'station_exclude_user' => array(
														'station',
														'users'
														),
										'station_include_user' => array(
														'station',
														'users'
														),
										'station_user' => array(
														'station',
														'users'
														),
										'station_user_group' => array(
														'station'
														),
										'system_log' => array(
														'users'
														),
										'system_log_detail' => array(
														'system_log'
														),
										'tax_policy' => array(
														'company',
														),
										'tax_policy_object' => array(
														'tax_policy'
														),
										'user_date_total' => array(
														'users',
														),
										'user_deduction' => array(
														'users',
														'company_deduction'
														),
										'user_default' => array(
														'company',
														),
										'user_default_company_deduction' => array(
														'user_default',
														'company_deduction'
														),
										//Can't automatically purge this table, as user_id is often NULL for company wide settings.
										//'user_generic_data' => array(
										//				'users',
										//				'company'
										//				),
										'user_generic_status' => array(
														'users'
														),
										'user_group' => array(
														'company'
														),
										'user_identification' => array(
														'users'
														),
										'user_preference' => array(
														'users'
														),
										//Can't automatically purge this table, as user_id is often NULL for company wide settings.
										//'user_report_data' => array(
										//				'company',
										//				'users'
										//				),
										'report_custom_column' => array(
														'company',
										),
										'user_title' => array(
														'company'
														),
										'user_wage' => array(
														'users',
														),
										'users' => array(
														'company',
														),
										'wage_group' => array(
														'company'
														)
										);

	/**
	 * @return bool
	 */
	static function Execute() {
		global $db;

		//MySQL fails with many of these queries due to recently changed syntax in a point release, disable purging when using MySQL for now.
		//http://bugs.mysql.com/bug.php?id=27525
		if ( strncmp($db->databaseType, 'mysql', 5) == 0 ) {
			return FALSE;
		}

		//Make array of tables to purge, and the timeperiod to purge them at.
		Debug::Text('Purging database tables: '. TTDate::getDate('DATE+TIME', time() ), __FILE__, __LINE__, __METHOD__, 10);
		$purge_tables = array(
								'user_generic_status' => 2,
								'punch' => 60, //Punch must come before punch_control
								'punch_control' => 60, //punch_control must come before user_date
								'user_date_total' => 60, //user_date_total must come before user_date
								'schedule' => 60, //schedule must come before user_date
								'company' => 120,
								'company_deduction' => 120,
								'company_deduction_pay_stub_entry_account' => 120,
								'company_generic_map' => 120,
								'company_user_count' => 120,
								'legal_entity' => 120,
								'remittance_source_account' => 120,
								'remittance_destination_account' => 120,
								'payroll_remittance_agency' => 120,
								'payroll_remittance_agency_event' => 120,
								'authentication' => 2, //Sessions.
								'hierarchy_user' => 45,
								'hierarchy_object_type' => 45,
								'hierarchy_level' => 45,
								'absence_policy' => 45,
								'accrual' => 60,
								'accrual_balance' => 60, //Doesnt have updated_date column
								'accrual_policy' => 45,
								'accrual_policy_milestone' => 60,
								'accrual_policy_account' => 120,
								'authorizations' => 45, //Must go before requests.
								'bank_account' => 45,
								'branch' => 45,
								'break_policy' => 45,
								'wage_group' => 45,
								'cron' => 45,
								'currency' => 120,
								'currency_rate' => 120,
								'contributing_pay_code_policy' => 45,
								'contributing_shift_policy' => 45,
								'department' => 45,
								'department_branch' => 45,
								'department_branch_user' => 45,
								'exception' => 45,
								'exception_policy' => 45,
								'exception_policy_control' => 45,
								'hierarchy_control' => 45,
								'hierarchy_tree' => 45,
								'hierarchy_share' => 45,
								'holiday_policy' => 45,
								'holiday_policy_recurring_holiday' => 45,
								'holidays' => 45,
								'meal_policy' => 45,
								'message' => 45,
								'message_sender' => 45,
								'message_recipient' => 45,
								'message_control' => 45,
								'other_field' => 45,
								'over_time_policy' => 45,
								'pay_code' => 90,
								'pay_formula_policy' => 90,
								'pay_period' => 45,
								'pay_period_schedule' => 45,
								'pay_period_schedule_user' => 45,
								'pay_period_time_sheet_verify' => 45,
								'pay_stub' => 120,
								'pay_stub_amendment' => 120,
								'pay_stub_entry' => 120,
								'pay_stub_entry_account' => 120,
								'pay_stub_entry_account_link' => 120,
								'pay_stub_transaction' => 120,
								'permission' => 45,
								'permission_control' => 45,
								'permission_user' => 45,
								'policy_group' => 45,
								'policy_group_user' => 45,
								'premium_policy' => 45,
								'premium_policy_branch' => 45,
								'premium_policy_department' => 45,
								'recurring_holiday' => 45,
								'recurring_ps_amendment' => 45,
								'recurring_ps_amendment_user' => 45,
								'recurring_schedule_control' => 45,
								'recurring_schedule_template' => 45,
								'recurring_schedule_template_control' => 45,
								'recurring_schedule_user' => 45,
								'regular_time_policy' => 45,
								'request' => 45,
								'roe' => 45,
								'round_interval_policy' => 45,
								'schedule_policy' => 45,
								'station' => 45,
								'station_user' => 45,
								'station_branch' => 45,
								'station_department' => 45,
								'station_user_group' => 45,
								'station_include_user' => 45,
								'station_exclude_user' => 45,
								'user_deduction' => 120, //This is related to payroll, so keep as long as pay stubs exist.
								'user_default' => 45,
								'user_default_company_deduction' => 45,
								'user_generic_data' => 45,
								'user_group' => 45,
								'user_group_tree' => 45,
								'user_identification' => 45,
								'user_preference' => 45,
								'user_title' => 45,
								'user_wage' => 120,
								'user_report_data' => 45,
								'user_setting' => 120,
								'company_setting' => 45,
								'users' => 120,
								'recurring_schedule' => 0, //Delete these immediately.
								'bread_crumb' => 45,
								'system_log' => 45,
								'system_log_detail' => 45,
								'company_generic_tag_map' => 45,
								'company_generic_tag' => 45,
								'qualification' => 45,
								'user_education' => 45,
								'user_license' => 45,
								'user_skill' => 45,
								'user_language' => 45,
								'user_membership' => 45,
								'qualification_group' => 45,
								'qualification_group_tree' => 45,
								'kpi' => 45,
								'kpi_group' => 45,
								'kpi_group_tree' => 45,
								'user_review' => 45,
								'user_review_control' => 45,
								'user_contact' => 45,
								'ethnic_group' => 45,
							);

		if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
			$purge_professional_tables = array(
								'request_schedule' => 45,
								'report_schedule' => 45,
								'report_custom_column' => 45,
								);

			$purge_tables = array_merge( $purge_tables, $purge_professional_tables );
		}

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$purge_corporate_tables = array(
								'client' => 45,
								'client_contact' => 45,
								'client_group' => 45,
								'client_group_tree' => 45,
								'client_payment' => 45,
								'client_balance' => 45,
								'premium_policy_job' => 45,
								'premium_policy_job_group' => 45,
								'premium_policy_job_item' => 45,
								'premium_policy_job_item_group' => 45,
								'area_policy' => 45,
								'area_policy_location' => 45,
								'government_document' => 120,
								'document' => 45,
								'document_attachment' => 45,
								'document_group' => 45,
								'document_group_tree' => 45,
								'document_revision' => 45,
								'invoice' => 45,
								'invoice_config' => 45,
								'invoice_district' => 45,
								'invoice_transaction' => 90,
								'job' => 45,
								'job_user_allow' => 45,
								'job_group' => 45,
								'job_group_tree' => 45,
								'job_item' => 45,
								'job_item_allow' => 45,
								'job_item_amendment' => 45,
								'job_item_group' => 45,
								'job_item_group_tree' => 45,
								'geo_fence' => 45,
								'payment_gateway' => 45,
								'payment_gateway_currency' => 45,
								'payment_gateway_credit_card_type' => 45,
								'product' => 45,
								'product_group' => 45,
								'product_group_tree' => 45,
								'product_price' => 45,
								'shipping_policy' => 45,
								'shipping_table_rate' => 45,
								'shipping_policy_object' => 45,
								'tax_policy' => 45,
								'tax_policy_object' => 45,
								);

			$purge_tables = array_merge( $purge_tables, $purge_corporate_tables );
		}

		if ( getTTProductEdition() >= TT_PRODUCT_ENTERPRISE ) {
			$purge_enterprise_tables = array(
								'job_vacancy' => 45,
								'job_applicant_location' => 45,
								'job_applicant_employment' => 45,
								'job_applicant_reference' => 45,
								'job_applicant_education' => 45,
								'job_applicant_skill' => 45,
								'job_applicant_language' => 45,
								'job_applicant_membership' => 45,
								'job_applicant_license' => 45,
								'job_applicant' => 45,
								'job_application' => 45,
								'recruitment_portal_config' => 45,
								'recruitment_portal_field_map' => 45,
								'expense_policy' => 45,
								'user_expense' => 45,
								);

			$purge_tables = array_merge( $purge_tables, $purge_enterprise_tables );
		}

		$current_tables = $db->MetaTables();

		if ( is_array( $purge_tables ) AND is_array( $current_tables ) ) {
			foreach( $purge_tables as $table => $expire_days ) {
//				if ( PRODUCTION == FALSE ) {
//					$expire_days = 0;
//				}

				$db->StartTrans(); //Try to keep the transactions as short lived as possible to avoid replication delays on the database.
				if ( in_array($table, $current_tables) ) {
					$query = array();
					switch ( $table ) {
						case 'user_generic_status':
							//Treat the user_generic_status table differently, as rows are never marked as deleted in it.
							$query[] = 'DELETE FROM '. $table .' WHERE updated_date <= '. (time() - (86400 * ($expire_days)));
							break;
						case 'system_log':
							//Only delete system_log rows from deleted users, or deleted/cancelled companies
							$query[] = 'DELETE FROM '. $table .' as a USING users as b, company as c WHERE a.user_id = b.id AND b.company_id = c.id AND ( b.deleted = 1 OR c.deleted = 1 OR c.status_id = 30 ) AND ( a.date <= '. (time() - (86400 * ($expire_days))) .' AND b.updated_date <= '. (time() - (86400 * ($expire_days))) .' AND c.updated_date <= '. (time() - (86400 * ($expire_days))) .')';

							//Quite a few system_log rows are created by user_id=0 (system), they aren't shown on the audit log anyways and don't need to be kept around for long.
							//This also deletes logs that can't be matches to any user, or those that have already been deleted.
							//This includes log entries for the cron system.
							//$query[] = 'DELETE FROM '. $table .' WHERE id in ( select a.id from '. $table .' as a LEFT JOIN users as b ON a.user_id = b.id WHERE b.id is NULL AND ( a.date <= '. (time()-(86400*($expire_days))) .' ) )';
							//NOTE: Make sure NOT EXISTS is a strict join query without any other where clauses, as that can cause unintended results.
							$query[] = 'DELETE FROM '. $table .' as a WHERE a.date <= '. (time() - (86400 * (($expire_days * 2)))) .' AND NOT EXISTS ( select 1 from users as b WHERE a.user_id = b.id )';

							//Delete non-critical audit log entries that bloat the database
							// 45 Days
							//  - Station ( Allowed )

							// 180 Days
							//  - Authentication
							//  - UserDateTotal ( Notice - Recalculating )
							//  - Exception ( Notice - Emails )
							//  - ReportSchedule ( Notice - Emailing )
							//  - Punch ( Telephone Start records )

							// 1.5 Years
							//  - PayStubAmendment ( Edit )
							//  - PunchControl ( Add )
							//  - Punch ( Add )
							//  - Schedule ( Add )
							$query[] = 'DELETE FROM '. $table .' as a WHERE
								(
									a.date <= '. (time() - (86400 * (($expire_days * 1)))) .'
									AND (
											( table_name = \'station\' AND action_id = 200 )
										)
								)
								OR
								(
									a.date <= '. (time() - (86400 * (($expire_days * 4)))) .'
									AND (
											( table_name = \'authentication\' )
											OR
											( table_name = \'user_date_total\' AND action_id = 500 )
											OR
											( table_name = \'exception\' AND action_id = 500 )
											OR
											( table_name = \'report_schedule\' AND action_id = 500 )
											OR
											( table_name = \'punch\' AND ( action_id = 500 AND description LIKE \'Telephone Punch Start%\' ) )
										)
								)
								OR
								(
									a.date <= '. (time() - (86400 * (($expire_days * 12)))) .'
									AND (
											( table_name = \'pay_stub_amendment\' AND action_id = 20 )
											OR
											( table_name = \'punch_control\' AND action_id = 10 )
											OR
											( table_name = \'punch\' AND action_id = 10 )
											OR
											( table_name = \'schedule\' AND action_id = 10 )
										)
								)
								';
							break;
						case 'system_log_detail':
							//Only delete system_log_detail rows when the corresponding system_log rows are already deleted
							//$query[] = 'DELETE FROM '. $table .' WHERE id in ( select a.id from '. $table .' as a LEFT JOIN system_log as b ON a.system_log_id = b.id WHERE b.id is NULL )';
							$query[] = 'DELETE FROM '. $table .' as a WHERE NOT EXISTS ( select 1 from system_log as b WHERE a.system_log_id = b.id )';
							break;
						case 'punch':
							//Delete punch rows from deleted users, or deleted companies
							$query[] = 'DELETE FROM '. $table .' as a USING punch_control as b, users as d, company as e WHERE a.punch_control_id = b.id AND b.user_id = d.id AND d.company_id = e.id AND ( a.deleted = 1 OR b.deleted = 1 OR d.deleted = 1 OR e.deleted = 1 ) AND ( a.updated_date <= '. (time() - (86400 * ($expire_days))) .' AND d.updated_date <= '. (time() - (86400 * ($expire_days))) .' AND e.updated_date <= '. (time() - (86400 * ($expire_days))) .')';
							break;
						case 'punch_control':
						case 'user_date_total':
						case 'schedule':
						case 'request':
							//Delete punch_control/user_date rows from deleted users, or deleted companies
							$query[] = 'DELETE FROM '. $table .' as a USING users as d, company as e WHERE a.user_id = d.id AND d.company_id = e.id AND ( a.deleted = 1 OR d.deleted = 1 OR e.deleted = 1 ) AND ( a.updated_date <= '. (time() - (86400 * ($expire_days))) .' AND d.updated_date <= '. (time() - (86400 * ($expire_days))) .' AND e.updated_date <= '. (time() - (86400 * ($expire_days))) .')';
							break;
						case 'exception':
							//Delete exception rows from deleted users, or deleted companies
							$query[] = 'DELETE FROM '. $table .' as a USING users as d, company as e WHERE a.user_id = d.id AND d.company_id = e.id AND ( a.deleted = 1 OR d.deleted = 1 OR e.deleted = 1 ) AND ( a.updated_date <= '. (time() - (86400 * ($expire_days))) .' AND d.updated_date <= '. (time() - (86400 * ($expire_days))) .' AND e.updated_date <= '. (time() - (86400 * ($expire_days))) .')';

							//Delete exception rows from terminated users after they have been terminated for 3x the regular expire length.
							$query[] = 'DELETE FROM '. $table .' as a USING users as d, company as e WHERE a.user_id = d.id AND d.company_id = e.id AND ( d.status_id = 20 ) AND ( a.updated_date <= '. (time() - (86400 * ($expire_days * 3))) .' AND d.updated_date <= '. (time() - (86400 * ($expire_days * 3))) .' AND e.updated_date <= '. (time() - (86400 * ($expire_days * 3))) .')';
							break;
						case 'user_identification':
							//Purge biometric data from terminated employees within 45 days (expire time) of their termination date.
							$query[] =  'UPDATE '. $table .' as a SET deleted = 1, updated_date = '. time() .', deleted_date = '. time() .', deleted_by = '. $db->qstr( TTUUID::getZeroID() ) .' FROM users as b WHERE a.user_id = b.id AND a.type_id in (20, 70, 71, 75, 76, 77, 78, 79, 80, 100, 101) AND b.status_id = 20 AND b.termination_date <= '. $db->qstr( TTDate::getDBTimeStamp( (time() - (86400 * ($expire_days))) ) ) .' AND a.deleted = 0 AND b.deleted = 0';
							//No break here, as it needs to continue through to message_recipient.
						case 'pay_period_time_sheet_verify':
						case 'message_sender':
						case 'message_recipient':
							//Delete rows from deleted users, or deleted companies
							$query[] = 'DELETE FROM '. $table .' as a USING users as d, company as e WHERE a.user_id = d.id AND d.company_id = e.id AND ( a.deleted = 1 OR d.deleted = 1 OR e.deleted = 1 ) AND ( a.updated_date <= '. (time() - (86400 * ($expire_days))) .' AND d.updated_date <= '. (time() - (86400 * ($expire_days))) .' AND e.updated_date <= '. (time() - (86400 * ($expire_days))) .')';
							break;
						case 'accrual_balance':
							//Delete rows from deleted users, or deleted companies. Accrual Balance table does not have updated_date column.
							$query[] = 'DELETE FROM '. $table .' as a USING users as d, company as e WHERE a.user_id = d.id AND d.company_id = e.id AND ( a.deleted = 1 OR d.deleted = 1 OR e.deleted = 1 ) AND ( d.updated_date <= '. (time() - (86400 * ($expire_days))) .' AND e.updated_date <= '. (time() - (86400 * ($expire_days))) .')';
							break;
						case 'pay_stub_entry':
							//Only delete pay_stub_entry rows from deleted users, or deleted companies
							$query[] = 'DELETE FROM '. $table .' as a USING pay_stub as b WHERE a.pay_stub_id = b.id AND ( a.deleted = 1 OR b.deleted = 1 ) AND a.updated_date <= '. (time() - (86400 * ($expire_days)));
							break;
						case 'authorizations':
							//Only delete authorization rows from deleted requests.
							//  Make sure we try still try to purge old authorization type_ids (10,20,30), as they could be left laying around from long-time customers.
							$query[] = 'DELETE FROM '. $table .' as a USING request as b WHERE a.object_type_id in (10, 20, 30, 50, 1010, 1020, 1030, 1040, 1100) AND a.object_id = b.id AND ( b.deleted = 1 ) AND ( b.updated_date <= '. (time() - (86400 * ($expire_days))) .' AND a.updated_date <= '. (time() - (86400 * ($expire_days))) .' AND b.updated_date <= '. (time() - (86400 * ($expire_days))) .')';

							$query[] = 'DELETE FROM '. $table .' as a WHERE a.object_type_id in (10, 20, 30, 50, 1010, 1020, 1030, 1040, 1100) AND NOT EXISTS ( select 1 from request as b WHERE a.object_id = b.id)';
							$query[] = 'DELETE FROM '. $table .' as a WHERE a.object_type_id in (90) AND NOT EXISTS ( select 1 from pay_period_time_sheet_verify as b WHERE a.object_id = b.id)';

							if ( getTTProductEdition() >= TT_PRODUCT_ENTERPRISE ) {
								$query[] = 'DELETE FROM ' . $table . ' as a WHERE a.object_type_id in (200) AND NOT EXISTS ( select 1 from user_expense as b WHERE a.object_id = b.id)';
							}
							break;
						case 'station':
							//Delete stations that haven't been used (allowed_date) or updated in over two years. Only consider PC/WirelessWeb stations types though.
							//Problem is when a station is created, a punch may be assigned to it, but the allowed_date is update on the wildcard entry instesd.
							//$query[] = 'DELETE FROM '. $table .' as a WHERE a.type_id in (10, 25) AND a.deleted = 0 AND ( lower(a.station_id) != \'any\' AND lower(a.source) != \'any\' ) AND ( a.allowed_date is NULL OR a.allowed_date <= '. (time()-(86400*(730))) .') AND ( a.updated_by is NULL AND a.updated_date <= '. (time()-(86400*($expire_days))) .')'; //This will delete active stations. DO NOT USE.
							$query[] = 'DELETE FROM '. $table .' as a WHERE a.type_id in (10, 25) AND ( lower(a.station_id) != \'any\' AND lower(a.source) != \'any\' ) AND NOT EXISTS ( select 1 from punch as b WHERE a.id = b.station_id ) AND ( a.updated_by is NULL AND a.updated_date <= '. (time() - (86400 * ($expire_days))) .' ) AND a.deleted = 0';

							//Delete station rows from deleted/cancelled companies
							$query[] = 'DELETE FROM '. $table .' as a USING company as e WHERE a.company_id = e.id AND ( a.deleted = 1 OR e.deleted = 1 OR e.status_id = 30 ) AND ( a.updated_date <= '. (time() - (86400 * ($expire_days))) .' AND e.updated_date <= '. (time() - (86400 * ($expire_days))) .')';

							//Disable iButton/Fingerprint/Barcode stations that have never been used.
							$query[] = 'UPDATE '. $table .' SET status_id = 10 WHERE type_id in (30, 40, 50) AND status_id = 20 AND allowed_date is NULL AND updated_date <= '. (time() - (86400 * (120))) .' AND ( deleted = 0 )';
							break;
						case 'permission_control':
							$query[] = 'DELETE FROM '. $table .' as a USING company as c WHERE a.company_id = c.id AND ( c.deleted = 1 ) AND ( a.updated_date <= '. (time() - (86400 * ($expire_days))) .' AND c.updated_date <= '. (time() - (86400 * ($expire_days))) .')';
							break;
						case 'permission':
							$query[] = 'DELETE FROM '. $table .' WHERE id in ( select a.id from '. $table .' as a LEFT JOIN permission_control as b ON a.permission_control_id = b.id WHERE b.id is NULL )';
							break;
						case 'message_control':
							$query[] = 'DELETE FROM '. $table .' WHERE id in ( select a.id from '. $table .' as a LEFT JOIN message_sender as b ON a.id = b.message_control_id WHERE b.id is NULL )';
							break;
						case 'bank_account':
						case 'user_generic_data':
						case 'user_report_data':
							//Delete all rows that are already deleted, since this isn't done in the DEFAULT case statement below
							$query[] = 'DELETE FROM '. $table .' WHERE deleted = 1 AND updated_date <= '. (time() - (86400 * ($expire_days)));

							//user_id column can be NULL for company wide data, make sure we leave that alone.
							$query[] = 'DELETE FROM '. $table .' as a USING company as b WHERE a.company_id = b.id AND ( b.deleted = 1 AND b.updated_date <= '. (time() - (86400 * ($expire_days))) .')';
							$query[] = 'DELETE FROM '. $table .' as a USING users as b WHERE a.user_id = b.id AND ( b.deleted = 1 AND b.updated_date <= '. (time() - (86400 * ($expire_days))) .')';

							//Delete rows where the parent table rows are already deleted.
							$query[] = 'DELETE FROM '. $table .' as a WHERE NOT EXISTS ( select 1 from company as b WHERE a.company_id = b.id )';

							//bank_account can have user_id is NULL or user_id = 0, we don't want to purge those records in either case.
							$query[] = 'DELETE FROM '. $table .' as a WHERE ( a.user_id is NOT NULL AND a.user_id != \''. TTUUID::getZeroID() .'\' ) AND NOT EXISTS ( select 1 from users as b WHERE a.user_id = b.id )';
							break;
						case 'user_group_tree':
						case 'document_group_tree':
						case 'client_group_tree':
						case 'job_group_tree':
						case 'job_item_group_tree':
						case 'product_group_tree':
						case 'qualification_group_tree':
						case 'kpi_group_tree':
							$parent_table = str_replace('_tree', '', $table);
							$query[] = 'DELETE FROM '. $table .' as a WHERE NOT EXISTS ( select 1 from '. $parent_table .' as b WHERE a.object_id = b.id)';
							break;
						case 'authentication':
							$query[] = 'DELETE FROM '. $table .' as a USING users as b WHERE a.object_id = b.id AND a.type_id NOT IN (100,110) AND ( b.deleted = 1 AND b.updated_date <= '. (time() - (86400 * ($expire_days))) .')';
							break;
						case 'company_generic_tag_map':
							//Tag mapping is specific to almost every other major table, so need to handle it one at a time.
							$table_arr = $db->MetaTables(); //Get list of tables, so we don't cause SQL errors if the table doesn't exist.

							$cgtf = TTnew('CompanyGenericTagFactory'); /** @var CompanyGenericTagFactory $cgtf */
							$object_type_arr = $cgtf->getOptions('object_type');
							foreach( $object_type_arr as $object_type_id => $object_type ) {
								if ( in_array( $object_type, $table_arr ) ) {
									$query[] = 'DELETE FROM '. $table .' as a WHERE a.object_type_id = '. $object_type_id .' AND NOT EXISTS ( select 1 from '. $object_type .' as b WHERE a.object_id = b.id)';
								}
							}
							unset($cgtf, $object_type_arr,  $object_type, $table_arr);
							break;
						case 'company_generic_tag':
							$query[] = 'DELETE FROM '. $table .' as a WHERE NOT EXISTS ( select 1 from company_generic_tag_map as b WHERE a.id = b.tag_id)';
							break;
						//Tables that don't require custom queries, but don't have a deleted/updated_date column.
						case 'company_user_count':
						case 'company_generic_map':
						case 'permission_user':
						case 'user_default_company_deduction':
						case 'recurring_schedule_user':
						case 'recurring_ps_amendment_user':
						case 'hierarchy_user':
						case 'hierarchy_object_type':
						case 'company_deduction_pay_stub_entry_account':
						case 'department_branch':
						case 'department_branch_user':
						case 'pay_period_schedule_user':
						case 'bread_crumb':
						case 'holiday_policy_recurring_holiday':
						case 'station_branch':
						case 'station_department':
						case 'station_user_group':
						case 'station_include_user':
						case 'station_exclude_user':
						case 'policy_group_user':
						case 'premium_policy_branch':
						case 'premium_policy_department':
						case 'premium_policy_job':
						case 'premium_policy_job_group':
						case 'premium_policy_job_item':
						case 'premium_policy_job_item_group':
						case 'client_balance':
						case 'tax_policy_object':
						case 'area_policy_location':
						case 'shipping_policy_object':
						case 'payment_gateway_currency':
						case 'payment_gateway_credit_card_type':
						case 'recruitment_portal_field_map':
							break;
						//Purge old tables from previous versions.
						case 'message':
						case 'hierarchy_tree':
						case 'hierarchy_share':
						case 'station_user':
						case 'job_user_allow':
						case 'job_item_allow':
							if ( version_compare(APPLICATION_VERSION, '3.5.0', '>=') ) {
								$query[] = 'DELETE FROM '. $table;
							}
							break;
						default:
							//
							//
							// FIXME: 120 days after 01-Jan-14 change "updated_date" to "deleted_date" for all queries.
							// All deleted records by that time should have the deleted_date set properly.
							//
							//
							Debug::Text('Default Query... Table: '. $table, __FILE__, __LINE__, __METHOD__, 10);
							$query[] = 'DELETE FROM '. $table .' WHERE deleted = 1 AND updated_date <= '. (time() - (86400 * ($expire_days)));
							break;
					}

					//Handle orphaned data as well, based on the parent table.
					if ( isset(self::$parent_table_map_array[$table]) ) {
						foreach( self::$parent_table_map_array[$table] as $parent_table ) {
							if ( isset(self::$parent_table_column_map[$parent_table]) ) {
								$parent_table_column = self::$parent_table_column_map[$parent_table];
							} else {
								$parent_table_column = $parent_table.'_id';
							}
							Debug::Text('Parent Table: '. $parent_table .' Parent Table Column: '. $parent_table_column, __FILE__, __LINE__, __METHOD__, 10);

							//Skip some tables without deleted columns.
							if ( !in_array($table, array( 'bank_account', 'user_generic_data', 'user_report_data', 'system_log', 'system_log_detail', 'authorizations' ) ) ) {
								//Delete rows where the parent table rows are already marked as deleted.
								//MySQL gets a syntax error on these types of queries, need to rewrite them using the IN clause without referencing the table we are deleting from.
								//Due to this we need to rewrite almost all queries here, which requires extensive testing as well.
								//$query[] = 'DELETE FROM '. $table .' WHERE '. $parent_table_column .' in ( select id from '. $parent_table .' as b WHERE b.deleted = 1 AND b.updated_date <= '. (time()-(86400*($expire_days))) .')';
								$query[] = 'DELETE FROM '. $table .' as a USING '. $parent_table .' as b WHERE a.'. $parent_table_column .' = b.id AND ( b.deleted = 1 AND b.updated_date <= '. (time() - (86400 * ($expire_days))) .')';

								//Delete rows where the parent table rows are already deleted.
								//Keep records where ID = 0 or NULL as those can still be valid in some cases.
								$query[] = 'DELETE FROM '. $table .' as a WHERE a.'. $parent_table_column .' != \''. TTUUID::getZeroID() .'\' AND a.'. $parent_table_column .' is NOT NULL AND NOT EXISTS ( SELECT 1 FROM '. $parent_table .' as b WHERE a.'. $parent_table_column .' = b.id )';
							}

							unset($parent_table_column, $parent_table);
						}
					}

					//FIXME: With new punch method in v3.0 add query to make sure orphaned punches without punch_control rows are cleaned out
					//select a.id, a.deleted, b.id, b.deleted from punch as a LEFT JOIN punch_control as b ON (a.punch_control_id = b.id) WHERE b.id is NULL AND a.deleted = 0;
					if ( isset($query) AND is_array($query) ) {
						$i = 0;
						foreach( $query as $q ) {
							//echo "Query: ". $q ."\n"; //Help with debugging SQL syntax errors.
							$db->Execute( $q );
							Debug::Text('Query: '. $q, __FILE__, __LINE__, __METHOD__, 10);
							Debug::Text('Table found for purging: '. $table .'('.$i.') Expire Days: '. $expire_days .' Purged Rows: '. $db->Affected_Rows(), __FILE__, __LINE__, __METHOD__, 10);
							$i++;
						}
					}
					unset($query);
				} else {
					Debug::Text('Table not found for purging: '. $table, __FILE__, __LINE__, __METHOD__, 10);
				}

				//$db->FailTrans();
				$db->CompleteTrans();
			}

			//
			//Purge saved punch images.
			//
			if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
				//This doesn't need to be wrapped in a transaction, and can take quite a while due to disk I/O. Doing so can harm the database performance, especially with replication.
				Debug::Text('Purging Punch Images...', __FILE__, __LINE__, __METHOD__, 10);

				$plf = new PunchListFactory();
				$total_records = -1;
				$x = 0;
				$i = 0;
				while ( $total_records == -1 OR ( $total_records > 0 AND $x <= 250 ) ) { //Limit to 10 batches in total.
					$db->StartTrans(); //Try to keep the transactions as short lived as possible to avoid replication delays on the database.

					$plf->getByHasImageAndCreatedDate( TRUE, ( time() - ( 86400 * 120 ) ), 1000 ); //Expire Days: 120 -- Limit to 500 at a time, as its mostly the file I/O that takes the time here.
					$total_records = $plf->getRecordCount();
					Debug::text('Batch: '. $x .' Punches with images older than X days: '. $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					if ( $plf->getRecordCount() > 0 ) {
						foreach( $plf as $p_obj ) {
							Debug::text('  Punch ID: '. $p_obj->getID() .' Date: '. TTDate::getDate('DATE+TIME', $p_obj->getTimeStamp() ) .' Image File Name: '. $p_obj->getImageFileName(), __FILE__, __LINE__, __METHOD__, 10);
							$query = 'UPDATE '. $plf->getTable() .' SET has_image = 0 WHERE id = \''. TTUUID::castUUID( $p_obj->getID() ) .'\'';
							if ( $plf->ExecuteSQL( $query ) !== FALSE ) {
								$p_obj->cleanStoragePath();
							} else {
								Debug::text('  ERROR: Update query to purge has_image failed... ID: '. $p_obj->getID(), __FILE__, __LINE__, __METHOD__, 10);
							}
							$i++;
						}
					}

					//$db->FailTrans();
					$db->CompleteTrans();

					$x++;
				}
				Debug::text('  Deleted Punch Images: '. $i, __FILE__, __LINE__, __METHOD__, 10);
				unset($plf, $p_obj, $total_records, $x, $i, $query);
			}

			//Since deleting a lot of records especially in system_log can cause large planning times of queries that select from that table. This manifested itself in the Audit tab taking 2-3 seconds to load.
			// Therefore force a regular vacuum to run on the entire database once we are done. We could also tune the autovacuum parameters more for these tables to be extra sure.
			Debug::text('  Start VACUUM...', __FILE__, __LINE__, __METHOD__, 10);
			$db->Execute( 'VACUUM ANALYZE' );
			Debug::text('  Done VACUUM...', __FILE__, __LINE__, __METHOD__, 10);
		}
		unset($purge_tables, $current_tables, $query);
		Debug::Text('Purging database tables complete: '. TTDate::getDate('DATE+TIME', time() ), __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	static function getParentTableMap() {
		global $db;

		//Get all tables in a database and their ID columns to aid in creating export/import mapping
		$exclude_columns = array('id', 'type_id', 'status_id', 'length_of_service_unit_id', 'apply_frequency_id', 'category_id', 'other_id1', 'other_id2', 'other_id3', 'other_id4', 'other_id5', 'ibutton_id', 'manual_id', 'exclusive_id', 'session_id', 'cc_type_id', 'originator_id', 'data_center_id', 'product_edition_id', 'calculation_id', 'severity_id', 'email_notification_id', 'default_schedule_status_id', 'phone_id', 'sex_id' );

		$table_name_map = array();
		$table_name_map['user'] = 'users';

		$dict = NewDataDictionary($db);
		$tables = $dict->MetaTables();
		sort($tables);
		$map = array();
		foreach( $tables as $table ) {
			$columns = $dict->MetaColumns( $table );

			foreach ( $columns as $column_table ) {
				$column_name = $column_table->name;
				if ( !in_array($column_name, $exclude_columns) AND stristr( $column_name, '_id') ) {
					//Find out where the column maps too.
					$tmp_table_name = str_replace('_id', '', $column_name);
					if ( isset($table_name_map[$tmp_table_name] ) ) {
						$tmp_table_name = $table_name_map[$tmp_table_name];
					}

					if ( in_array( $tmp_table_name, $tables ) ) {
						//Found destination table.
						//$out .= $table . ', '. $column_name .', '. $tmp_table_name .', id'."\n";
						//$test[$tmp_table_name][] = $table;
						$map[$table][] = $tmp_table_name;

					} else {
						echo "UNABLE TO FIND DESTINATION TABLE FOR: Table: ". $table ." Column: ". $column_name ."<br>\n";
					}
				}
			}

		}
		//echo $out;
		//var_dump($test);
		//asort($map);
		foreach( $map as $tmp_key => $tmp_val ) {
			echo "'$tmp_key' => array(\n\t\t\t\t'". implode("',\n\t\t\t\t'", $tmp_val) ."'\n\t\t\t\t), \n";
			//echo "'$tmp_key' => '$tmp_val', \n";
		}

	}
}


?>
