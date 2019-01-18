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
 * @package Modules\Install
 */
class InstallSchema_1100A extends InstallSchema_Base {

	//THIS IS THE SCHEMA VERSION THAT SWITCHES TO UUIDs.

	/**
	 * @return bool
	 */
	function preInstall() {
		Debug::text( 'preInstall: ' . $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9 );

		//No need to manually generate the UUID SEED as its done and written to the config file automatically as part of InstallSchema_Base->replaceSQLVariables()

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postInstall() {
		Debug::text( 'postInstall: ' . $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9 );

		//Make sure we clean all cache once we switch to UUID mode, otherwise integer IDs may be cached when UUIDs should be.
		$this->install_obj->cleanCacheDirectory();
		global $cache;
		$cache->clean();

		global $PRIMARY_KEY_IS_UUID;
		$PRIMARY_KEY_IS_UUID = TRUE;

		//Migrate UserGenericData (ie: Saved Search & Layout) to UUIDs
		$ugdlf = new UserGenericDataListFactory();
		$ugdlf->getAll( NULL, NULL, NULL, array( 'created_date' => 'asc', 'id' => 'asc') ); //Order by ID, in cases where their might be conflicting names or invalid records, the last record should be preserved.
		if ( $ugdlf->getRecordCount() > 0 ) {
			foreach ( $ugdlf as $ugdf ) {
				Debug::text( '   UserGenericData: ID: ' . $ugdf->getId() .' Name: '. $ugdf->getName() .' Script: '. $ugdf->getScript(), __FILE__, __LINE__, __METHOD__, 9 );
				if ( strpos( $ugdf->getScript(), '/interface/' ) !== FALSE ) { //Skip all reports and legacy data from TT v5 or lower.
					Debug::text( '   Deleting legacy interface record...', __FILE__, __LINE__, __METHOD__, 9 );
					$ugdf->setDeleted(TRUE);
				} else {
					Debug::text( '   Updating to UUIDs...', __FILE__, __LINE__, __METHOD__, 9 );

					//Debug::Arr( $ugdf->getData(), '  +++ Pre-Conversion data: ', __FILE__, __LINE__, __METHOD__, 9 );
					$data = $this->convertUserGenericData( $ugdf->getData(), $ugdf->getScript() );
					//Debug::Arr( $data, '  +++ Post-conversion data: ', __FILE__, __LINE__, __METHOD__, 9 );

					$ugdf->setData( $data );
				}

				if ( $ugdf->isValid() ) {
					$ugdf->save();
				} else {
					Debug::Arr( $ugdf->Validator->getTextErrors(), '   Deleting legacy interface record due to being invalid...', __FILE__, __LINE__, __METHOD__, 9 );
					$ugdf->setDeleted(TRUE);
					if ( $ugdf->isValid() ) {
						$ugdf->save();
					}
				}
			}
		}
		unset($ugdlf, $ugdf);


		//Migrate ReportCustomData to UUIDs
		$rcdlf = new UserReportDataListFactory();
		$rcdlf->getAll( NULL, NULL, NULL, array( 'created_date' => 'asc', 'id' => 'asc') ); //Order by ID, in cases where their might be conflicting names or invalid records, the last record should be preserved.
		if ( $rcdlf->getRecordCount() > 0 ) {
			foreach ( $rcdlf as $rcdf ) {
				$rcdf->setData( $this->convertUserReportData( $rcdf->getData(), $rcdf->getScript() ) );
				if ( $rcdf->isValid() ) {
					$rcdf->save();
				} else {
					Debug::Arr( $rcdf->Validator->getTextErrors(), '   Deleting invalid saved report data due to being invalid...', __FILE__, __LINE__, __METHOD__, 9 );
					$rcdf->setDeleted(TRUE);
					if ( $rcdf->isValid() ) {
						$rcdf->save();
					}
				}
			}
		}
		unset($rcdlf, $rcdf);

		return TRUE;
	}

	function convertUserGenericData( $data, $script ) {
		if ( !is_array( $data ) ) {
			return $data;
		}

		$exclude_keys = array(
			'filter_sort',
			'display_columns',

			'type_id',
			'status_id',

			'other_id1',
			'other_id2',
			'other_id3',
			'other_id4',
			'other_id5',

			'mx_internal_uid',
		);

		foreach ( $data as $key => $value ) {
			if ( is_array($value) ) {
				$data[$key] = $this->convertUserGenericData( $value, $script );
			} else {
				if ( ( is_numeric($key) OR $key == 'id' OR strpos( $key, '_id' ) !== FALSE ) AND !in_array( $key, $exclude_keys, TRUE ) ) {
					$data[$key] = TTUUID::convertIntToUUID( $value );
				}
			}
		}

		//Debug::Arr( $data,'   Post-Conversion data: ', __FILE__, __LINE__, __METHOD__, 9 );
		return $data;
	}

	function convertUserReportData( $data, $script ) {
		//Debug::Arr( $data,'+++ pre conversion data (possibly recursive) ', __FILE__, __LINE__, __METHOD__, 9 );
		$config_fields_to_convert = array(
				'company_deduction_id',
				'legal_entity_id',
				'user_title_id',
				'include_user_id',
				'exclude_user_id',
				'default_branch_id',
				'default_department_id',
				'accrual_policy_account_id',
				'punch_branch_id',
				'punch_department_id',
				'include_job_id',
				'exclude_job_id',
				'include_job_item_id',
				'exclude_job_item_id',
				'currency_id',
				'client_id',
				'include_job_client_id',
				'exclude_job_client_id',
				'include_job_product_id',
				'exclude_job_product_id',
				'client_sales_contact_id',
				'created_by_id',
				'updated_by_id',
				'job_vacancy_id',
				'job_application_interviewer_user_id',
				'schedule_branch_id',
				'schedule_department_id',
				'absence_policy_id',
				'job_id',
				'job_item_id',
				'default_job_id',
				'default_job_item_id',
				'punch_job_id',
				'punch_job_item_id',
				'pay_stub_entry_account_id',
				'product_id',
				'expense_policy_id',
				'kpi_id',
				'include_reviewer_user_id',
				'exclude_reviewer_user_id',
				'include_client_id',
				'exclude_client_id',
				'include_product_id',
				'exclude_product_id',
				'job_client_id',
				'job_applicant_id',
				'qualification_id',
				'eligible_time_contributing_pay_code',
				'include_pay_stub_entry_account',
				'exclude_pay_stub_entry_account',
				'user_group_id',
				'client_group_id',
				'wage_group_id',
				'policy_group_id',
				'job_group_id',
				'job_item_group_id',
				'ethnic_group_id',
		);

		foreach( $data as $key => $setup_data ) {
			if ( in_array( $key, $config_fields_to_convert ) AND is_numeric($data[$key]) ) {
				$data[$key] = TTUUID::convertIntToUUID($data[$key]);
			} else {
				switch ( $key ) {
					case 'time_period':
						if ( isset( $data['time_period']['pay_period_id'] ) ) {
							$data['time_period']['pay_period_id'] = $this->install_obj->convertArrayElementsToUUID( $data['time_period']['pay_period_id'] );
						}
						if ( isset( $data['time_period']['pay_period_schedule_id'] ) ) {
							$data['time_period']['pay_period_schedule_id'] = $this->install_obj->convertArrayElementsToUUID( $data['time_period']['pay_period_schedule_id'] );
						}
						break;
					case 'config':
						$data[$key] = $this->convertUserReportData( $data[$key], $script );
						break;
					case 'columns':
					case 'custom_filter':
					case 'subtotal':
					case 'sort':
					case 'group':
						$data[$key] = $this->install_obj->processColumns( $data[$key] );
						break;
					default:
						if ( in_array( $key, $config_fields_to_convert ) ) {
							$data[$key] = $this->install_obj->convertArrayElementsToUUID( $data[$key] );
						}
						break;

				}
			}
		}

		if ( isset( $data['config'] ) ) {
			foreach( $data['config'] as $key => $row ) {
				if ( in_array( $key, $config_fields_to_convert ) ) {
					$data['config'][$key] = $this->install_obj->convertArrayElementsToUUID( $data['config'][$key] );
				}
				if ( isset( $row['include_pay_stub_entry_account'] ) OR isset( $row['include_pay_stub_entry_account'] ) ) {
					$data['config'][$key]['include_pay_stub_entry_account'] = $this->install_obj->convertArrayElementsToUUID( $data['config'][$key]['include_pay_stub_entry_account'] );
					$data['config'][$key]['exclude_pay_stub_entry_account'] = $this->install_obj->convertArrayElementsToUUID( $data['config'][$key]['exclude_pay_stub_entry_account'] );
				}
			}
		} else {
			foreach( $data as $key => $row ) {
				if ( isset( $row['include_pay_stub_entry_account'] ) OR isset( $row['include_pay_stub_entry_account'] ) ) {
					$data[$key]['include_pay_stub_entry_account'] = $this->install_obj->convertArrayElementsToUUID( $data[$key]['include_pay_stub_entry_account'] );
					$data[$key]['exclude_pay_stub_entry_account'] = $this->install_obj->convertArrayElementsToUUID( $data[$key]['exclude_pay_stub_entry_account'] );
				}

			}
		}

		if ( isset( $data['other_box'] ) ) {
			foreach( $data['other_box'] as $index=>$box ) {
				foreach( $box as $key=>$row ) {
					if ( in_array( $key, $config_fields_to_convert ) ) {
						$data['other_box'][$index][$key] = $this->install_obj->convertArrayElementsToUUID( $data['other_box'][$index][$key] );
					}
					if ( isset( $row['include_pay_stub_entry_account'] ) OR isset( $row['include_pay_stub_entry_account'] ) ) {
						$data['other_box'][$index][$key]['include_pay_stub_entry_account'] = $this->install_obj->convertArrayElementsToUUID( $data['other_box'][$index][$key]['include_pay_stub_entry_account'] );
						$data['other_box'][$index][$key]['exclude_pay_stub_entry_account'] = $this->install_obj->convertArrayElementsToUUID( $data['other_box'][$index][$key]['exclude_pay_stub_entry_account'] );
					}
				}
			}
		}

		//PayrollExportReport needs its own section because of the custom setup data.
		if ( $script == 'PayrollExportReport' AND isset($data['export_type']) ) {
			$export_types = array('adp', 'adp_advanced', 'adp_resource', 'paychex_preview', 'paychex_preview_advanced_job', 'paychex_online','ceridian_insync', 'millenium','quickbooks', 'quickbooks_advanced', 'surepayroll', 'chris21', 'va_munis', 'accero', 'compupay', 'sage_50', 'meditech', 'csv', 'csv_advanced', 'other', 'cms_pbj');

			if ( isset($data['export_columns']) AND in_array( $data['export_type'], $export_types ) ) {
				//Legacy Export Setup data format (1 export type) - Upgrade to new format while we UUIDify it.
				foreach( $data['export_columns'] as $key => $setup_data ) {
					if ( in_array( $key, $export_types ) ) {
						$data[$key]['columns'] = $data[$key][$key]['columns'] = $this->install_obj->processColumns( $data['export_columns'][$key]['columns'] );
					}
				}
				unset($data['export_columns']);

				//Move any remaining array elements inside export_type specific key.
				foreach( $data as $key => $setup_data ) {
					if ( $key != 'export_type' AND $key != $data['export_type'] ) {
						$data[$data['export_type']][$key] = $setup_data;
						unset( $data[$key] );
					}
				}
			} else {
				//New Export Setup data format (multiple export types)
				foreach( $data as $key => $setup_data ) {
					if ( in_array( $key, $export_types ) ) {
						$data[$key]['columns'] = $this->install_obj->processColumns( $data[$key]['columns'] );
						if ( isset( $data[$key][$key] ) AND isset( $data[$key][$key]['columns'] ) ) {
							$data[$key][$key]['columns'] = $this->install_obj->processColumns( $data[$key][$key]['columns'] );
						}
					}
				}
			}
		}

		//Debug::Arr( $data,'+++ post conversion data: ', __FILE__, __LINE__, __METHOD__, 9 );

		return $data;
	}
}
?>
