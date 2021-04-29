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
class KPIReport extends Report {

	/**
	 * KPIReport constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText( 'Review Summary Report' );
		$this->file_name = 'review_summary_report';

		parent::__construct();

		return true;
	}

	/**
	 * @param string $user_id    UUID
	 * @param string $company_id UUID
	 * @return bool
	 */
	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check( 'hr_report', 'enabled', $user_id, $company_id )
				&& $this->getPermissionObject()->Check( 'hr_report', 'user_review', $user_id, $company_id ) ) {
			return true;
		}

		//Debug::Text('Regular employee viewing their own review...', __FILE__, __LINE__, __METHOD__, 10);
		//Regular employee printing review for themselves. Force specific config options.
		$filter_config = $this->getFilterConfig();
		if ( isset( $filter_config['user_review_control_id'] ) ) {
			$user_review_control_id = $filter_config['user_review_control_id'];
		} else {
			$user_review_control_id = TTUUID::getZeroID();
		}
		$this->setFilterConfig( [ 'include_user_id' => [ $user_id ], 'user_review_control_id' => $user_review_control_id ] );

		return true;
	}

	/**
	 * @param $name
	 * @param null $params
	 * @return array|bool|mixed|null
	 */
	protected function _getOptions( $name, $params = null ) {
		$retval = null;
		switch ( $name ) {
			case 'output_format':
				$retval = parent::getOptions( 'default_output_format' );
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
					'-1000-template'                      => TTi18n::gettext( 'Template' ),
					'-1010-time_period'                   => TTi18n::gettext( 'Time Period' ),
					'-2000-legal_entity_id'               => TTi18n::gettext( 'Legal Entity' ),
					'-2010-user_status_id'                => TTi18n::gettext( 'Employee Status' ),
					'-2020-user_group_id'                 => TTi18n::gettext( 'Employee Group' ),
					'-2030-user_title_id'                 => TTi18n::gettext( 'Employee Title' ),
					'-2035-user_tag'                      => TTi18n::gettext( 'Employee Tags' ),
					'-2040-include_user_id'               => TTi18n::gettext( 'Employee Include' ),
					'-2050-exclude_user_id'               => TTi18n::gettext( 'Employee Exclude' ),
					'-2060-include_reviewer_user_id'      => TTi18n::gettext( 'Reviewer Include' ),
					'-2070-exclude_reviewer_user_id'      => TTi18n::gettext( 'Reviewer Exclude' ),
					'-2080-default_branch_id'             => TTi18n::gettext( 'Default Branch' ),
					'-2090-default_department_id'         => TTi18n::gettext( 'Default Department' ),
					//'-2080-punch_branch_id' => TTi18n::gettext('Punch Branch'),
					//'-2090-punch_department_id' => TTi18n::gettext('Punch Department'),
					'-2100-kpi_id'                        => TTi18n::gettext( 'Key Performance Indicators' ),
					//'-2090-user_id' => TTi18n::gettext('Employee'),
					//'-2110-reviewer_user_id' => TTi18n::gettext('Reviewer'),
					'-2120-kpi_group_id'                  => TTi18n::gettext( 'KPI Group' ),
					'-2130-kpi_status_id'                 => TTi18n::gettext( 'KPI Status' ),
					'-2140-kpi_type_id'                   => TTi18n::gettext( 'KPI Type' ),
					'-2150-user_review_control_status_id' => TTi18n::gettext( 'Review Status' ),
					'-2160-user_review_control_type_id'   => TTi18n::gettext( 'Review Type' ),

					'-2170-term_id'     => TTi18n::gettext( 'Review Terms' ),
					'-2180-severity_id' => TTi18n::gettext( 'Review Severity/Importance' ),

					'-2188-review_tag' => TTi18n::gettext( 'Review Tags' ),

					'-3000-custom_filter' => TTi18n::gettext( 'Custom Filter' ),

					'-5000-columns'    => TTi18n::gettext( 'Display Columns' ),
					'-5010-group'      => TTi18n::gettext( 'Group By' ),
					'-5020-sub_total'  => TTi18n::gettext( 'SubTotal By' ),
					'-5030-sort'       => TTi18n::gettext( 'Sort By' ),
					'-5040-page_break' => TTi18n::gettext( 'Page Break On' ),
				];
				break;
			case 'time_period':
				$retval = TTDate::getTimePeriodOptions();
				break;
			case 'date_columns':
				$retval = array_merge(
						TTDate::getReportDateOptions( 'user.hire', TTi18n::getText( 'Hire Date' ), 15, false ),
						TTDate::getReportDateOptions( 'user_review_control.start', TTi18n::getText( 'Start Date' ), 16, false ),
						TTDate::getReportDateOptions( 'user_review_control.end', TTi18n::getText( 'End Date' ), 17, false ),
						TTDate::getReportDateOptions( 'user_review_control.due', TTi18n::getText( 'Due Date' ), 18, false )
				);
				break;
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), null, 'KPIReport', 'custom_column' );
					if ( is_array( $custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'filter_column_type_ids' ), null, 'KPIReport', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'dynamic_format_ids' ), 'KPIReport', 'custom_column' );
					if ( is_array( $report_dynamic_custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'static_format_ids' ), 'KPIReport', 'custom_column' );
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
					'-1000-kpi.name' => TTi18n::gettext( 'Key Performance Indicators' ),

					'-1005-kpi.type'   => TTi18n::getText( 'KPI Type' ),
					'-1000-kpi.status' => TTi18n::gettext( 'KPI Status' ),

					'-1010-kpi.description' => TTi18n::gettext( 'Description' ),

					'-1020-user.first_name'         => TTi18n::gettext( 'First Name' ),
					'-1030-user.middle_name'        => TTi18n::gettext( 'Middle Name' ),
					'-1040-user.last_name'          => TTi18n::gettext( 'Last Name' ),
					'-1050-user.full_name'          => TTi18n::gettext( 'Full Name' ),
					'-1060-user.status'             => TTi18n::gettext( 'Employee Status' ),
					'-1070-user.sex'                => TTi18n::gettext( 'Gender' ),
					'-1080-user.user_group'         => TTi18n::gettext( 'Employee Group' ),
					'-1090-user.title'              => TTi18n::gettext( 'Employee Title' ),
					'-1100-user.default_branch'     => TTi18n::gettext( 'Branch' ), //abbreviate for space
					'-1110-user.default_department' => TTi18n::gettext( 'Department' ), //abbreviate for space
					'-1120-user.city'               => TTi18n::gettext( 'City' ),
					'-1130-user.province'           => TTi18n::gettext( 'Province/State' ),
					'-1140-user.country'            => TTi18n::gettext( 'Country' ),
					'-1150-user.postal_code'        => TTi18n::gettext( 'Postal Code' ),
					'-1160-user.work_phone'         => TTi18n::gettext( 'Work Phone' ),
					'-1170-user.work_phone_ext'     => TTi18n::gettext( 'Work Phone Ext' ),
					'-1180-user.home_phone'         => TTi18n::gettext( 'Home Phone' ),
					'-1190-user.mobile_phone'       => TTi18n::gettext( 'Mobile Phone' ),
					'-1200-user.fax_phone'          => TTi18n::gettext( 'Fax Phone' ),
					'-1210-user.home_email'         => TTi18n::gettext( 'Home Email' ),
					'-1220-user.work_email'         => TTi18n::gettext( 'Work Email' ),
					'-1230-user.address1'           => TTi18n::gettext( 'Address 1' ),
					'-1240-user.address2'           => TTi18n::gettext( 'Address 2' ),
					'-1244-user.tag'                => TTi18n::getText( 'Employee Tags' ),

					'-1250-user_review_control.reviewer_user' => TTi18n::gettext( 'Reviewer Name' ),
					'-1260-user_review_control.status'        => TTi18n::gettext( 'Review Status' ),
					'-1270-user_review_control.type'          => TTi18n::gettext( 'Review Type' ),
					'-1280-user_review_control.term'          => TTi18n::gettext( 'Review Terms' ),
					'-1290-user_review_control.severity'      => TTi18n::gettext( 'Review Severity/Importance' ),
					'-1292-user_review_control.tag'           => TTi18n::gettext( 'Review Tags' ),

					'-1300-user_review.note'         => TTi18n::gettext( 'KPI Notes' ),
					'-1350-user.note'                => TTi18n::gettext( 'Employee Notes' ),
					'-1400-user_review_control.note' => TTi18n::gettext( 'Review Notes' ),


				];

				$retval = array_merge( $retval, $this->getOptions( 'date_columns' ), (array)$this->getOptions( 'report_static_custom_column' ) );
				ksort( $retval );
				break;
			case 'dynamic_columns':
				$retval = [
					//Dynamic - Aggregate functions can be used
					'-2000-kpi.minimum_rate'   => TTi18n::gettext( 'Minimum Rating' ),
					'-2010-kpi.maximum_rate'   => TTi18n::gettext( 'Maximum Rating' ),
					'-2020-user_review.rating' => TTi18n::gettext( 'Rating' ),

					'-2000-total_review' => TTi18n::gettext( 'Total Reviews' ), //Group counter...
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
						if ( strpos( $column, 'rating' ) !== false ) {
							$retval[$column] = 'numeric';
						} else if ( strpos( $column, 'total_review' ) !== false ) {
							$retval[$column] = 'numeric';
						}
					}
				}
				break;
			case 'aggregates':
				$retval = [];
				$dynamic_columns = array_keys( Misc::trimSortPrefix( array_merge( $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_dynamic_custom_column' ) ) ) );
				if ( is_array( $dynamic_columns ) ) {
					foreach ( $dynamic_columns as $column ) {
						switch ( $column ) {
							case 'kpi.minimum_rate':
								$retval[$column] = 'min';
								break;
							case 'kpi.maximum_rate':
								$retval[$column] = 'max';
								break;
							case 'user_review.rating':
							case 'total_review':
								$retval[$column] = 'sum';
								break;
						}
					}
				}
				break;
			case 'templates':
				$retval = [
						'-1010-by_employee_by_kpi+kpi+rating' => TTi18n::gettext( 'Review Information By Employee/KPI' ),
						'-1020-by_kpi_by_employee+kpi+rating' => TTi18n::gettext( 'Review Information By KPI/Employee' ),

						'-1040-by_type_by_terms_by_severity+kpi+rating'             => TTi18n::gettext( 'Review Summary By Type/Terms/Severity' ),
						'-1050-by_employee_by_type_by_terms_by_severity+kpi+rating' => TTi18n::gettext( 'Review Summary By Employee/Type/Terms/Severity' ),
						'-1060-by_type_by_terms_by_severity_by_employee+kpi+rating' => TTi18n::gettext( 'Review Summary By Type/Terms/Severity/Employee' ),

						'-1070-by_employee+due_date' => TTi18n::gettext( 'Pending Reviews By Employee' ),
				];

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset( $template ) && $template != '' ) {
					switch ( $template ) {
						case 'by_employee_by_kpi+kpi+rating':
							$retval['columns'][] = 'user.full_name';
							$retval['columns'][] = 'user_review_control.type';
							$retval['columns'][] = 'user_review_control.due-date_stamp';
							$retval['columns'][] = 'kpi.name';
							$retval['columns'][] = 'user_review.rating';
							$retval['columns'][] = 'user_review.note';

							$retval['-2150-user_review_control_status_id'] = [ 30 ];
							$retval['-2160-user_review_control_type_id'] = []; //Allow use to easily filter based on review type

							$retval['sub_total'][] = 'user.full_name';
							$retval['sub_total'][] = 'user_review_control.type';
							$retval['sub_total'][] = 'user_review_control.due-date_stamp';

							$retval['sort'][] = [ 'user.full_name' => 'asc' ];
							$retval['sort'][] = [ 'user_review_control.type' => 'asc' ];
							$retval['sort'][] = [ 'user_review_control.due-date_stamp' => 'desc' ];
							$retval['sort'][] = [ 'user_review.rating' => 'desc' ];
							$retval['sort'][] = [ 'kpi.name' => 'asc' ];
							break;
						case 'by_kpi_by_employee+kpi+rating':
							$retval['columns'][] = 'user_review_control.type';
							$retval['columns'][] = 'kpi.name';
							$retval['columns'][] = 'user.full_name';
							$retval['columns'][] = 'user_review.rating';
							$retval['columns'][] = 'user_review.note';

							$retval['-2150-user_review_control_status_id'] = [ 30 ];
							$retval['-2160-user_review_control_type_id'] = []; //Allow use to easily filter based on review type

							$retval['sub_total'][] = 'user_review_control.type';
							$retval['sub_total'][] = 'kpi.name';

							$retval['sort'][] = [ 'user_review_control.type' => 'asc' ];
							$retval['sort'][] = [ 'kpi.name' => 'asc' ];
							$retval['sort'][] = [ 'user_review.rating' => 'desc' ];
							$retval['sort'][] = [ 'user.full_name' => 'asc' ];
							break;
						case 'by_type_by_terms_by_severity+kpi+rating':
							$retval['columns'][] = 'user_review_control.type';
							$retval['columns'][] = 'user_review_control.term';
							$retval['columns'][] = 'user_review_control.severity';
							$retval['columns'][] = 'total_review';

							$retval['-2150-user_review_control_status_id'] = [ 30 ];

							$retval['group'][] = 'user_review_control.type';
							$retval['group'][] = 'user_review_control.term';
							$retval['group'][] = 'user_review_control.severity';

							$retval['sub_total'][] = 'user_review_control.type';
							$retval['sub_total'][] = 'user_review_control.term';
							$retval['sub_total'][] = 'user_review_control.severity';

							$retval['sort'][] = [ 'user_review_control.type' => 'asc' ];
							$retval['sort'][] = [ 'user_review_control.term' => 'asc' ];
							$retval['sort'][] = [ 'user_review_control.severity' => 'desc' ];
							break;
						case 'by_employee_by_type_by_terms_by_severity+kpi+rating':
							$retval['columns'][] = 'user.full_name';
							$retval['columns'][] = 'user_review_control.type';
							$retval['columns'][] = 'user_review_control.term';
							$retval['columns'][] = 'user_review_control.severity';
							$retval['columns'][] = 'total_review';
							$retval['columns'][] = 'user_review.rating';

							$retval['-2150-user_review_control_status_id'] = [ 30 ];

							$retval['group'][] = 'user.full_name';
							$retval['group'][] = 'user_review_control.type';
							$retval['group'][] = 'user_review_control.term';
							$retval['group'][] = 'user_review_control.severity';

							$retval['sub_total'][] = 'user.full_name';
							$retval['sub_total'][] = 'user_review_control.type';
							$retval['sub_total'][] = 'user_review_control.term';
							$retval['sub_total'][] = 'user_review_control.severity';

							$retval['sort'][] = [ 'user.full_name' => 'asc' ];
							$retval['sort'][] = [ 'user_review_control.type' => 'asc' ];
							$retval['sort'][] = [ 'user_review_control.term' => 'asc' ];
							$retval['sort'][] = [ 'user_review_control.severity' => 'desc' ];
							break;
						case 'by_type_by_terms_by_severity_by_employee+kpi+rating':
							$retval['columns'][] = 'user_review_control.type';
							$retval['columns'][] = 'user_review_control.term';
							$retval['columns'][] = 'user_review_control.severity';
							$retval['columns'][] = 'user.full_name';
							$retval['columns'][] = 'total_review';
							$retval['columns'][] = 'user_review.rating';

							$retval['-2150-user_review_control_status_id'] = [ 30 ];

							$retval['group'][] = 'user_review_control.type';
							$retval['group'][] = 'user_review_control.term';
							$retval['group'][] = 'user_review_control.severity';
							$retval['group'][] = 'user.full_name';

							$retval['sub_total'][] = 'user_review_control.type';
							$retval['sub_total'][] = 'user_review_control.term';
							$retval['sub_total'][] = 'user_review_control.severity';

							$retval['sort'][] = [ 'user_review_control.type' => 'asc' ];
							$retval['sort'][] = [ 'user_review_control.term' => 'asc' ];
							$retval['sort'][] = [ 'user_review_control.severity' => 'desc' ];
							$retval['sort'][] = [ 'total_review' => 'desc' ];
							$retval['sort'][] = [ 'user_review.rating' => 'desc' ];
							break;
						case 'by_employee+due_date':
							$retval['columns'][] = 'user.full_name';
							$retval['columns'][] = 'user_review_control.type';
							$retval['columns'][] = 'user_review_control.reviewer_user';
							$retval['columns'][] = 'user_review_control.due-date_stamp';

							$retval['-1010-time_period']['time_period'] = 'this_month';
							$retval['-2150-user_review_control_status_id'] = [ 10 ];
							$retval['-2160-user_review_control_type_id'] = []; //Allow use to easily filter based on review type

							$retval['group'][] = 'user.full_name';
							$retval['group'][] = 'user_review_control.type';
							$retval['group'][] = 'user_review_control.reviewer_user';
							$retval['group'][] = 'user_review_control.due-date_stamp';

							$retval['sort'][] = [ 'user_review_control.due-date_stamp' => 'asc' ];
							$retval['sort'][] = [ 'user_review_control.type_id' => 'asc' ];
							$retval['sort'][] = [ 'user.full_name' => 'asc' ];
							break;
						default:
							Debug::Text( ' Parsing template name: ' . $template, __FILE__, __LINE__, __METHOD__, 10 );
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
	 * Get raw data for report
	 * @param null $format
	 * @return bool
	 */
	function _getData( $format = null ) {
		$this->tmp_data = [ 'user' => [], 'kpi' => [], 'user_review_control' => [], 'user_review' => [] ];

		$columns = $this->getColumnDataConfig();
		$filter_data = $this->getFilterConfig();
		$columns['hire_date'] = $columns['start_date'] = $columns['end_date'] = $columns['due_date'] = true;

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Rows: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = Misc::addKeyPrefix( 'user.', (array)$u_obj->getObjectAsArray( Misc::removeKeyPrefix( 'user.', $columns ) ) );

			if ( strpos( $format, 'pdf_' ) !== false ) {
				if ( !isset( $this->form_data['user'][$u_obj->getId()] ) ) {
					$this->form_data['user'][$u_obj->getId()] = [];
				}
				//Make sure we merge this array with existing data and include all required fields for generating timesheets. This prevents slow columns from being returned.
				$this->form_data['user'][$u_obj->getId()] += (array)$u_obj->getObjectAsArray( [ 'first_name' => true, 'last_name' => true, 'employee_number' => true, 'hire_date' => true, 'hire_date_age' => true, 'title' => true, 'user_group' => true, 'default_branch' => true, 'default_department' => true ] );
			}

			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		//Get KPI data for joining.
		$klf = TTnew( 'KPIListFactory' ); /** @var KPIListFactory $klf */
		$klf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' KPI Rows: ' . $klf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $klf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $klf as $key => $k_obj ) {
			$this->tmp_data['kpi'][$k_obj->getId()] = Misc::addKeyPrefix( 'kpi.', (array)$k_obj->getObjectAsArray( Misc::removeKeyPrefix( 'kpi.', $columns ) ) );

			if ( strpos( $format, 'pdf_' ) !== false ) {
				if ( !isset( $this->form_data['kpi'][$k_obj->getId()] ) ) {
					$this->form_data['kpi'][$k_obj->getId()] = [];
				}
				//Make sure we merge this array with existing data and include all required fields for generating timesheets. This prevents slow columns from being returned.
				$this->form_data['kpi'][$k_obj->getId()] += (array)$k_obj->getObjectAsArray( [ 'name' => true, 'type' => true, 'type_id' => true, 'description' => true ] );
			}

			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		//Get user review control data for joining.
		$urclf = TTnew( 'UserReviewControlListFactory' ); /** @var UserReviewControlListFactory $urclf */
		$urclf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Review Control Rows: ' . $urclf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $urclf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $urclf as $key => $urc_obj ) {
			$this->tmp_data['user_review_control'][$urc_obj->getId()][$urc_obj->getUser()] = Misc::addKeyPrefix( 'user_review_control.', (array)$urc_obj->getObjectAsArray( Misc::removeKeyPrefix( 'user_review_control.', $columns ) ) );
			$this->tmp_data['user_review_control'][$urc_obj->getId()][$urc_obj->getUser()]['total_review'] = 1;

			if ( strpos( $format, 'pdf_' ) !== false ) {
				if ( !isset( $this->form_data['user_review_control'][$urc_obj->getId()][$urc_obj->getUser()] ) ) {
					$this->form_data['user_review_control'][$urc_obj->getId()][$urc_obj->getUser()] = [];
				}
				//Make sure we merge this array with existing data and include all required fields for generating timesheets. This prevents slow columns from being returned.
				$this->form_data['user_review_control'][$urc_obj->getId()][$urc_obj->getUser()] += (array)$urc_obj->getObjectAsArray( [ 'status' => true, 'type' => true, 'term' => true, 'severity' => true, 'start_date' => true, 'end_date' => true, 'due_date' => true, 'rating' => true, 'note' => true, 'reviewer_user' => true ] );
			}

			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		$urlf = TTnew( 'UserReviewListFactory' ); /** @var UserReviewListFactory $urlf */
		$urlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Review Rows: ' . $urlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $urlf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $urlf as $key => $ur_obj ) {
			$this->tmp_data['user_review'][$ur_obj->getUserReviewControl()][$ur_obj->getKPI()] = Misc::addKeyPrefix( 'user_review.', (array)$ur_obj->getObjectAsArray( Misc::removeKeyPrefix( 'user_review.', $columns ) ) );

			if ( strpos( $format, 'pdf_' ) !== false ) {
				if ( !isset( $this->form_data['user_review'][$ur_obj->getUserReviewControl()][$ur_obj->getKPI()] ) ) {
					$this->form_data['user_review'][$ur_obj->getUserReviewControl()][$ur_obj->getKPI()] = [];
				}
				//Make sure we merge this array with existing data and include all required fields for generating timesheets. This prevents slow columns from being returned.
				$this->form_data['user_review'][$ur_obj->getUserReviewControl()][$ur_obj->getKPI()] += (array)$this->form_data['kpi'][$ur_obj->getKPI()];
				$this->form_data['user_review'][$ur_obj->getUserReviewControl()][$ur_obj->getKPI()] += (array)$ur_obj->getObjectAsArray( [ 'rating' => true, 'note' => true ] );
			}

			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		//Debug::Arr($this->tmp_data, ' TMP Rows: ', __FILE__, __LINE__, __METHOD__, 10);

		return true;
	}

	/**
	 * PreProcess data such as calculating additional columns from raw data etc...
	 * @param null $format
	 * @return bool
	 */
	function _preProcess( $format = null ) {
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $this->tmp_data['user_review_control'] ), null, TTi18n::getText( 'Pre-Processing Data...' ) );

		$key = 0;
		if ( isset( $this->tmp_data['user_review_control'] ) ) {
			foreach ( $this->tmp_data['user_review_control'] as $user_review_control_id => $level_1 ) {

				foreach ( $level_1 as $user_id => $user_review_control ) {
					$processed_data = [];
					if ( isset( $this->tmp_data['user'][$user_id]['user.hire_date'] ) ) {
						$hire_date_columns = TTDate::getReportDates( 'user.hire', TTDate::parseDateTime( $this->tmp_data['user'][$user_id]['user.hire_date'] ), false, $this->getUserObject() );
					} else {
						$hire_date_columns = [];
					}

					if ( isset( $user_review_control['user_review_control.start_date'] ) ) {
						$start_date_columns = TTDate::getReportDates( 'user_review_control.start', TTDate::parseDateTime( $user_review_control['user_review_control.start_date'] ), false, $this->getUserObject() );
					} else {
						$start_date_columns = [];
					}
					if ( isset( $user_review_control['user_review_control.end_date'] ) ) {
						$end_date_columns = TTDate::getReportDates( 'user_review_control.end', TTDate::parseDateTime( $user_review_control['user_review_control.end_date'] ), false, $this->getUserObject() );
					} else {
						$end_date_columns = [];
					}
					if ( isset( $user_review_control['user_review_control.due_date'] ) ) {
						$due_date_columns = TTDate::getReportDates( 'user_review_control.due', TTDate::parseDateTime( $user_review_control['user_review_control.due_date'] ), false, $this->getUserObject() );
					} else {
						$due_date_columns = [];
					}

					if ( isset( $this->tmp_data['user'][$user_id] ) && is_array( $this->tmp_data['user'][$user_id] ) ) {
						$processed_data = array_merge( $processed_data, $this->tmp_data['user'][$user_id] );
					}

					if ( isset( $this->tmp_data['user_review'][$user_review_control_id] ) ) {
						foreach ( $this->tmp_data['user_review'][$user_review_control_id] as $kpi_id => $kpi ) {
							if ( is_array( $kpi ) ) {
								$processed_data = array_merge( $processed_data, $kpi );
							}
							if ( is_array( $user_review_control ) ) {
								$processed_data = array_merge( $processed_data, $user_review_control );
							}
							if ( isset( $this->tmp_data['kpi'][$kpi_id] ) && is_array( $this->tmp_data['kpi'][$kpi_id] ) ) {
								$processed_data = array_merge( $processed_data, $this->tmp_data['kpi'][$kpi_id] );
							}

							if ( strpos( $format, 'pdf_' ) === false ) {
								$this->data[] = array_merge( $hire_date_columns, $start_date_columns, $end_date_columns, $due_date_columns, $processed_data );
							} else {
								$this->form_data['review'][$user_id][$user_review_control_id][] = array_merge( [ 'kpi.id' => $kpi_id ], $hire_date_columns, $start_date_columns, $end_date_columns, $due_date_columns, $processed_data );
							}
						}
					} else {
						//If no KPIs are assigned to a review, still display as much data as we can.
						//As reviews can be scheduled in the future and aren't likely to have KPIs.
						if ( is_array( $user_review_control ) ) {
							$processed_data = array_merge( $processed_data, $user_review_control );
						}

						if ( strpos( $format, 'pdf_' ) === false ) {
							$this->data[] = array_merge( $hire_date_columns, $start_date_columns, $end_date_columns, $due_date_columns, $processed_data );
						} else {
							$this->form_data['review'][$user_id][$user_review_control_id][] = array_merge( [ 'kpi.id' => null ], $hire_date_columns, $start_date_columns, $end_date_columns, $due_date_columns, $processed_data );
						}
					}
				}
				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
				$key++;
			}
			unset( $this->tmp_data, $kpi, $user_review_control, $hire_date_columns, $start_date_columns, $end_date_columns, $due_date_columns, $processed_data );
		}

		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);
		return true;
	}

	/**
	 * @param $user_data
	 * @param $review_data
	 * @return bool
	 */
	function reviewHeader( $user_data, $review_data ) {
		$margins = $this->pdf->getMargins();
		$current_company = $this->getUserObject()->getCompanyObject();

		$border = 0;

		$total_width = ( $this->pdf->getPageWidth() - $margins['left'] - $margins['right'] );

		//Logo
		$this->pdf->Image( $current_company->getLogoFileName( null, true, false, 'large' ), Misc::AdjustXY( 0, $margins['left'] ), Misc::AdjustXY( 1, $margins['top'] ), $this->pdf->pixelsToUnits( 167 ), $this->pdf->pixelsToUnits( 42 ), '', '', '', false, 300, '', false, false, 0, true );
		$this->pdf->Ln( $this->_pdf_scaleSize( 2 ) );

		$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 24 ) );
		$this->pdf->Cell( $total_width, $this->_pdf_scaleSize( 10 ), TTi18n::gettext( 'Employee Review' ), $border, 0, 'C' );
		$this->pdf->Ln( $this->_pdf_scaleSize( 10 ) );
		$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 12 ) );
		$this->pdf->Cell( $total_width, $this->_pdf_scaleSize( 5 ), $current_company->getName(), $border, 0, 'C' );
		$this->pdf->Ln( $this->_pdf_scaleSize( 5 ) + $this->_pdf_scaleSize( 2 ) );

		//Generated Date/User top right.
		$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( 6 ) );
		$this->pdf->setY( ( $this->pdf->getY() - $this->_pdf_fontSize( 6 ) ) );
		$this->pdf->setX( ( $this->pdf->getPageWidth() - $margins['right'] - 50 ) );
		$this->pdf->Cell( 50, $this->_pdf_scaleSize( 2 ), TTi18n::getText( 'Generated' ) . ': ' . TTDate::getDate( 'DATE+TIME', time() ), $border, 0, 'R', 0, '', 1 );
		$this->pdf->Ln( $this->_pdf_scaleSize( 2 ) );
		$this->pdf->setX( ( $this->pdf->getPageWidth() - $margins['right'] - 50 ) );
		$this->pdf->Cell( 50, $this->_pdf_scaleSize( 2 ), TTi18n::getText( 'Generated For' ) . ': ' . $this->getUserObject()->getFullName(), $border, 0, 'R', 0, '', 1 );
		$this->pdf->Ln( $this->_pdf_scaleSize( 5 ) );

		$this->pdf->Rect( $this->pdf->getX(), ( $this->pdf->getY() - $this->_pdf_scaleSize( 2 ) ), $total_width, $this->_pdf_scaleSize( 37 ) );

		$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( 12 ) );
		$this->pdf->Cell( 30, $this->_pdf_scaleSize( 5 ), TTi18n::gettext( 'Employee' ) . ':', $border, 0, 'R' );
		$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 12 ) );
		$this->pdf->Cell( ( 70 + ( ( $total_width - 200 ) / 2 ) ), $this->_pdf_scaleSize( 5 ), $user_data['first_name'] . ' ' . $user_data['last_name'] . ' (#' . $user_data['employee_number'] . ')', $border, 0, 'L', 0, '', 1 );

		$this->pdf->SetFont( '', '', $this->_pdf_fontSize( 12 ) );
		$this->pdf->Cell( 40, $this->_pdf_scaleSize( 5 ), TTi18n::gettext( 'Title' ) . ':', $border, 0, 'R' );
		$this->pdf->Cell( ( 60 + ( ( $total_width - 200 ) / 2 ) ), $this->_pdf_scaleSize( 5 ), ( $user_data['title'] != '' ) ? $user_data['title'] : '--', $border, 0, 'L', 0, '', 1 );
		$this->pdf->Ln( $this->_pdf_scaleSize( 5 ) );

		$this->pdf->SetFont( '', '', $this->_pdf_fontSize( 12 ) );
		$this->pdf->Cell( 30, $this->_pdf_scaleSize( 5 ), TTi18n::gettext( 'Hire Date' ) . ':', $border, 0, 'R' );
		$this->pdf->Cell( ( 70 + ( ( $total_width - 200 ) / 2 ) ), $this->_pdf_scaleSize( 5 ), $user_data['hire_date'] .' ( '. $user_data['hire_date_age'] .' Years Ago )', $border, 0, 'L', 0, '', 1 );
		$this->pdf->Cell( 40, $this->_pdf_scaleSize( 5 ), TTi18n::gettext( 'Group' ) . ':', $border, 0, 'R' );
		$this->pdf->Cell( ( 60 + ( ( $total_width - 200 ) / 2 ) ), $this->_pdf_scaleSize( 5 ), ( $user_data['user_group'] != '' ) ? $user_data['user_group'] : '--', $border, 0, 'L', 0, '', 1 );
		$this->pdf->Ln( $this->_pdf_scaleSize( 5 ) );

		$this->pdf->Ln( $this->_pdf_scaleSize( 2 ) );
		$this->pdf->Line( ( $this->pdf->getX() + $this->_pdf_scaleSize( 2 ) ), $this->pdf->getY(), ( $this->pdf->getX() + $total_width - $this->_pdf_scaleSize( 2 ) ), $this->pdf->getY() );
		$this->pdf->Ln( $this->_pdf_scaleSize( 2 ) );


		$this->pdf->SetFont( '', '', $this->_pdf_fontSize( 12 ) );
		$this->pdf->Cell( 30, $this->_pdf_scaleSize( 5 ), TTi18n::gettext( 'Type' ) . ':', $border, 0, 'R' );
		$this->pdf->Cell( ( 70 + ( ( $total_width - 200 ) / 2 ) ), $this->_pdf_scaleSize( 5 ), $review_data['type'], $border, 0, 'L', 0, '', 1 );
		$this->pdf->Cell( 40, $this->_pdf_scaleSize( 5 ), TTi18n::gettext( 'Status' ) . ':', $border, 0, 'R' );
		$this->pdf->Cell( ( 60 + ( ( $total_width - 200 ) / 2 ) ), $this->_pdf_scaleSize( 5 ), $review_data['status'], $border, 0, 'L', 0, '', 1 );
		$this->pdf->Ln( $this->_pdf_scaleSize( 5 ) );

		$this->pdf->SetFont( '', '', $this->_pdf_fontSize( 12 ) );
		$this->pdf->Cell( 30, $this->_pdf_scaleSize( 5 ), TTi18n::gettext( 'Severity' ) . ':', $border, 0, 'R' );
		$this->pdf->Cell( ( 70 + ( ( $total_width - 200 ) / 2 ) ), $this->_pdf_scaleSize( 5 ), $review_data['severity'], $border, 0, 'L', 0, '', 1 );
		$this->pdf->Cell( 40, $this->_pdf_scaleSize( 5 ), TTi18n::gettext( 'Terms' ) . ':', $border, 0, 'R' );
		$this->pdf->Cell( ( 60 + ( ( $total_width - 200 ) / 2 ) ), $this->_pdf_scaleSize( 5 ), $review_data['term'], $border, 0, 'L', 0, '', 1 );
		$this->pdf->Ln( $this->_pdf_scaleSize( 5 ) );

		$this->pdf->SetFont( '', '', $this->_pdf_fontSize( 12 ) );
		$this->pdf->Cell( 30, $this->_pdf_scaleSize( 5 ), TTi18n::gettext( 'Rating' ) . ':', $border, 0, 'R' );
		$this->pdf->Cell( ( 70 + ( ( $total_width - 200 ) / 2 ) ), $this->_pdf_scaleSize( 5 ), ( $review_data['rating'] != '' ) ? $review_data['rating'] : '--', $border, 0, 'L', 0, '', 1 );
		$this->pdf->Cell( 40, $this->_pdf_scaleSize( 5 ), TTi18n::gettext( 'Reviewer' ) . ':', $border, 0, 'R' );
		$this->pdf->Cell( ( 60 + ( ( $total_width - 200 ) / 2 ) ), $this->_pdf_scaleSize( 5 ), $review_data['reviewer_user'], $border, 0, 'L', 0, '', 1 );
		$this->pdf->Ln( $this->_pdf_scaleSize( 5 ) );

		$this->pdf->SetFont( '', '', $this->_pdf_fontSize( 12 ) );
		$this->pdf->Cell( 30, $this->_pdf_scaleSize( 5 ), TTi18n::gettext( 'Time Period' ) . ':', $border, 0, 'R' );
		$this->pdf->Cell( ( 70 + ( ( $total_width - 200 ) / 2 ) ), $this->_pdf_scaleSize( 5 ), $review_data['start_date'] .' - '. $review_data['end_date'], $border, 0, 'L', 0, '', 1 );
		$this->pdf->Cell( 40, $this->_pdf_scaleSize( 5 ), TTi18n::gettext( 'Due Date' ) . ':', $border, 0, 'R' );
		$this->pdf->Cell( ( 60 + ( ( $total_width - 200 ) / 2 ) ), $this->_pdf_scaleSize( 5 ), $review_data['due_date'], $border, 0, 'L', 0, '', 1 );
		$this->pdf->Ln( $this->_pdf_scaleSize( 5 ) );

		$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( 10 ) );
		$this->pdf->Ln( $this->_pdf_scaleSize( 5 ) );

		return true;
	}

	/**
	 * @param $columns
	 * @param $column_widths
	 * @return bool
	 */
	function reviewKPIHeader( $columns, $column_widths ) {
		$line_h = $this->_pdf_scaleSize( 5 );

		$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 10 ) );
		$this->pdf->setFillColor( 220, 220, 220 );

		$this->pdf->Cell( $column_widths['line'], $line_h, '#', 1, 0, 'C', 1, '', 1 );
		$this->pdf->Cell( $column_widths['kpi.name'], $line_h, $columns['kpi.name'], 1, 0, 'C', 1, '', 1 );
		$this->pdf->Cell( $column_widths['user_review.rating'], $line_h, $columns['user_review.rating'], 1, 0, 'C', 1, '', 1 );
		$this->pdf->Cell( $column_widths['user_review.note'], $line_h, $columns['user_review.note'], 1, 0, 'C', 1, '', 1 );
		$this->pdf->Ln();

		$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( 10 ) );

		return true;
	}

	/**
	 * @return bool
	 */
	function reviewAddPage() {
		$this->reviewFooter();
		$this->pdf->AddPage();

		return true;
	}

	/**
	 * @return bool
	 */
	function reviewFooter() {
		$margins = $this->pdf->getMargins();

		$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( 8 ) );

		//Save x, y and restore after footer is set.
		$x = $this->pdf->getX();
		$y = $this->pdf->getY();

		//Jump to end of page.
		$this->pdf->setY( ( $this->pdf->getPageHeight() - $margins['bottom'] - $margins['top'] - 10 ) );

		$this->pdf->Cell( ( $this->pdf->getPageWidth() - $margins['right'] ), $this->_pdf_fontSize( 5 ), TTi18n::getText( 'Page' ) . ' ' . $this->pdf->PageNo() . ' of ' . $this->pdf->getAliasNbPages(), 0, 0, 'C', 0 );
		$this->pdf->Ln();

		$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( 6 ) );
		$this->pdf->Cell( ( $this->pdf->getPageWidth() - $margins['right'] ), $this->_pdf_fontSize( 5 ), TTi18n::gettext( 'Generated By' ) . ' ' . APPLICATION_NAME . ' v' . APPLICATION_VERSION, 0, 0, 'C', 0 );

		$this->pdf->setX( $x );
		$this->pdf->setY( $y );

		$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( 10 ) );

		return true;
	}

	/**
	 * @param $user_data
	 * @param $review_control_data
	 * @return bool
	 */
	function reviewSignature( $user_data, $review_control_data ) {
		$border = 0;

		$this->reviewCheckPageBreak( 25, true );

		$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( 10 ) );
		$this->pdf->setFillColor( 255, 255, 255 );
		$this->pdf->Ln( 1 );

		$line_h = $this->_pdf_scaleSize( 6 );

		$this->pdf->Ln( $line_h );

		$this->pdf->Cell( 40, $line_h, TTi18n::gettext( 'Employee Signature' ) . ':', $border, 0, 'L' );
		$this->pdf->Cell( 60, $line_h, '_____________________________', $border, 0, 'C' );
		$this->pdf->Cell( 40, $line_h, TTi18n::gettext( 'Reviewer Signature' ) . ':', $border, 0, 'R' );
		$this->pdf->Cell( 60, $line_h, '_____________________________', $border, 0, 'C' );

		$this->pdf->Ln( $line_h );
		$this->pdf->Cell( 40, $line_h, '', $border, 0, 'R' );
		$this->pdf->Cell( 60, $line_h, $user_data['first_name'] . ' ' . $user_data['last_name'], $border, 0, 'C' );
		$this->pdf->Cell( 40, $line_h, '', $border, 0, 'R' );
		$this->pdf->Cell( 60, $line_h, $review_control_data['reviewer_user'], $border, 0, 'C' );

		return true;
	}

	/**
	 * @param $height
	 * @param bool $add_page
	 * @return bool
	 */
	function reviewCheckPageBreak( $height, $add_page = true ) {
		$margins = $this->pdf->getMargins();

		if ( ( $this->pdf->getY() + $height ) > ( $this->pdf->getPageHeight() - $margins['bottom'] - $margins['top'] - 10 ) ) {
			//Debug::Text('Detected Page Break needed...', __FILE__, __LINE__, __METHOD__, 10);
			$this->reviewAddPage();

			return true;
		}

		return false;
	}

	/**
	 * @param $format
	 * @return bool|string
	 */
	function _outputPDFReview( $format ) {
		Debug::Text( ' Format: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );

		if ( isset( $this->form_data ) && count( $this->form_data ) > 0 ) {
			$this->pdf = new TTPDF( 'P', 'mm', 'LETTER', $this->getUserObject()->getCompanyObject()->getEncoding() );

			$this->pdf->SetAuthor( APPLICATION_NAME );
			$this->pdf->SetTitle( $this->title );
			$this->pdf->SetSubject( APPLICATION_NAME . ' ' . TTi18n::getText( 'Report' ) );

			$this->pdf->setMargins( $this->config['other']['left_margin'], $this->config['other']['top_margin'], $this->config['other']['right_margin'] ); //Margins are ignored because we use setXY() to force the coordinates before each drawing and therefore ignores margins.

			$this->pdf->SetAutoPageBreak( false );

			$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( 10 ) );

			$margins = $this->pdf->getMargins();
			$total_width = ( $this->pdf->getPageWidth() - $margins['left'] - $margins['right'] );
			$total_height = ( $this->pdf->getPageHeight() - $margins['bottom'] - $margins['top'] - 15 );

			//Use percentages so it properly scales to landscape mode.
			$column_widths = [
					'line'               => ( $total_width * 0.04 ),
					'kpi.name'           => ( $total_width * 0.50 ),
					'user_review.rating' => ( $total_width * 0.07 ),
					'user_review.note'   => ( $total_width * 0.39 ),
			];

			$row_layout = [
					'max_width'    => 50,
					'cell_padding' => 2,
					'height'       => 5,
					'align'        => 'L',
					'border'       => 0,
					'fill'         => 1,
					'stretch'      => 1,
			];

			$columns = [
					'line' => '#',
					'kpi.name'           => TTi18n::gettext( 'Key Performance Indicator' ),
					'user_review.rating' => TTi18n::gettext( 'Result' ),
					'user_review.note'   => TTi18n::gettext( 'Note' ),
			];

			$border = 0;

			foreach( $this->form_data['review'] as $user_id => $user_rows ) {
				foreach( $user_rows as $user_review_control_id => $kpi_rows ) {
					$i = 0;
					$this->pdf->AddPage( 'P', 'LETTER' );

					foreach( $kpi_rows as $key => $row ) {
						$is_review_first_page = false;
						$row['line'] = ($i + 1);

						if ( $this->_pdf_checkMaximumPageLimit() == false ) {
							Debug::Text( 'Exceeded maximum page count...', __FILE__, __LINE__, __METHOD__, 10 );
							//Exceeded maximum pages, stop processing.
							$this->_pdf_displayMaximumPageLimitError();
							break;
						}

						if ( $i == 0 ) {
							$is_review_first_page = true;
							$this->reviewHeader( $this->form_data['user'][$user_id], $this->form_data['user_review_control'][$user_review_control_id][$user_id] );
							if ( isset($this->form_data['user_review'][$user_review_control_id]) && count($this->form_data['user_review'][$user_review_control_id]) > 0 ) { //Only show header if KPIs actually exist. The review might just be a general note.
								$this->reviewKPIHeader( $columns, $column_widths );
							}
						}

						if ( isset($this->form_data['user_review'][$user_review_control_id][$row['kpi.id']]) ) {
							$description = ( $this->form_data['user_review'][$user_review_control_id][$row['kpi.id']]['description'] != '' ) ? "\n(" . $this->form_data['user_review'][$user_review_control_id][$row['kpi.id']]['description'] . ')' : '';
							$row['kpi.name'] = $this->form_data['user_review'][$user_review_control_id][$row['kpi.id']]['name'] . $description;
							$row['user_review.rating'] = $this->form_data['user_review'][$user_review_control_id][$row['kpi.id']]['rating'];
							$row['user_review.note'] = $this->form_data['user_review'][$user_review_control_id][$row['kpi.id']]['note'];

							$row_cell_height = $this->_pdf_getMaximumHeightFromArray( $columns, $row, $column_widths, 65, $this->_pdf_fontSize( $row_layout['height'] ), 0.75 ); //Seems to estimate a little too high, so shrink it down a bit. We force the text to always fit in the cell, so it will never be too small.
							//Debug::Text( 'Max Row Height ('. $i .'): '. $row_cell_height, __FILE__, __LINE__, __METHOD__, 10 );
							if ( $is_review_first_page == true && $row_cell_height > ( $total_height - 50 ) || $row_cell_height > $total_height ) { //Different max height on first page vs. 2nd page.
								$row_cell_height = $total_height;
							}
							$this->reviewCheckPageBreak( $row_cell_height, true );

							$this->pdf->MultiCell( $column_widths['line'], $row_cell_height, $row['line'], 1, 'C', 0, 0 );

							$this->pdf->MultiCell( $column_widths['kpi.name'], $row_cell_height, $row['kpi.name'], 1, 'L', 0, 0, '', '', true, 0, false, true, 0, 'T', true );

							if ( $this->form_data['user_review'][$user_review_control_id][$row['kpi.id']]['type_id'] == 10  ) { //10=Scale
								$this->pdf->MultiCell( $column_widths['user_review.rating'], $row_cell_height, ( $row['user_review.rating'] != '' ) ? Misc::removeTrailingZeros( $row['user_review.rating'], 0) : '--', 1, 'C', 0, 0, '', '', true, 0 );
							} elseif ( $this->form_data['user_review'][$user_review_control_id][$row['kpi.id']]['type_id'] == 20 ) { //20=Yes/No
								$this->pdf->MultiCell( $column_widths['user_review.rating'], $row_cell_height, Misc::humanBoolean( $row['user_review.rating'] ), 1, 'C', 0, 0, '', '', true, 0 );
							} else {
								$this->pdf->MultiCell( $column_widths['user_review.rating'], $row_cell_height, ( $row['user_review.rating'] != '' ) ? $row['user_review.rating'] : TTi18n::getText('N/A'), 1, 'C', 0, 0, '', '', true, 0 );
							}

							$this->pdf->MultiCell( $column_widths['user_review.note'], $row_cell_height, ( $row['user_review.note'] != '' ) ? $row['user_review.note'] : '--', 1, 'L', 0, 0, '', '', true, 0, false, true, 0, 'T', true );
							$this->pdf->Ln( $row_cell_height );
						}

						$i++;
					}

					if ( $this->form_data['user_review_control'][$user_review_control_id][$user_id]['note'] != '' ) {
						$row_cell_height = $this->pdf->getStringHeight( $this->form_data['user_review_control'][$user_review_control_id][$user_id]['note'], wordwrap( $this->form_data['user_review_control'][$user_review_control_id][$user_id]['note'], $total_width ) );

						$this->reviewCheckPageBreak( $row_cell_height, true );

						$this->pdf->Ln( 5 );
						$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 12 ) );
						$this->pdf->Cell( 15, $this->_pdf_scaleSize( 5 ), TTi18n::gettext( 'Note' ) . ':', $border, 0, 'L' );
						$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( 10 ) );
						$this->pdf->MultiCell( ( $total_width - 15 ), $row_cell_height, $this->form_data['user_review_control'][$user_review_control_id][$user_id]['note'], $border, 'L', 0, 0, '', '', true, 0 );
						$this->pdf->Ln( $row_cell_height );
					}

					$this->reviewSignature( $this->form_data['user'][$user_id], $this->form_data['user_review_control'][$user_review_control_id][$user_id] );

					$this->reviewFooter();
				}
			}

			$output = $this->pdf->Output( '', 'S' );

			return $output;

		}

		Debug::Text( 'No data to return...', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param null $format
	 * @return array|bool|string
	 */
	function _output( $format = null ) {
		if ( $format == 'pdf_review_print' ) {
			return $this->_outputPDFReview( $format );
		} else {
			return parent::_output( $format );
		}
	}
}

?>
