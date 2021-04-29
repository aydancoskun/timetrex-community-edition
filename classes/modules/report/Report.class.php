<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2021 TimeTrex Software Inc.
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
class Report {

	public $title = null;
	public $file_name = 'report';
	public $file_mime_type = 'application/pdf';

	protected $PDF_IMAGE_SCALE_RATIO = 1.25; //CONST from TCPDF, copy it here so we don't need to include TCPDF for HTML reports.

	protected $config = [
			'other' => [
					'report_name'         => '', //Name to be displayed on the report.
					'is_embedded'         => false, //Is it an embedded report that should hide things like the description header?
					'disable_grand_total' => false,
					//'output_format' => 'pdf', //PDF, PDF_PRINT, PDF_FORM(Tax Form), PDF_FORM_PRINT(Print Tax Form), HTML, EMAIL
					'page_orientation'    => 'P', //Portrait
					'page_format'         => 'LETTER', //Letter/Legal

					'default_font' => '', //Leave blank to default to locale specific font.
					//'default_font' => 'helvetica', //Core PDF font, works with setFontSubsetting(TRUE) and is fast with small PDF sizes.
					//'default_font' => 'freeserif', //Slow with setFontSubsetting(TRUE), produces PDFs at least 1mb.

					'maximum_page_limit'      => 100, //User configurable limit to prevent accidental large report generation. Don't allow it to be more than 1000.
					'maximum_row_limit'       => false, //postProcessing maximum number of rows.

					//Set limits high for On-Site installs, this is all configurable in the .ini file though.
					'query_statement_timeout' => 600000, //In milliseconds. Default to 10 minutes.
					'maximum_memory_limit'    => '3072M',
					'maximum_execution_limit' => 1800, //30 Minutes

					'font_size'                   => 0, //+5, +4, .., +1, 0, -1, ..., -4, -5 (adjusts relative font size)
					'table_header_font_size'      => 8,
					'table_row_font_size'         => 8,
					'table_header_word_wrap'      => 10, //Characters per word when wrapping.
					'table_data_word_wrap'        => 50, //Characters per word when wrapping data on each row of the report.
					'top_margin'                  => 5, //Allow the user to adjust the left/top margins for different printers.
					'bottom_margin'               => 5,
					'left_margin'                 => 5,
					'right_margin'                => 5,
					'adjust_horizontal_position'  => 0, //We may need these for government forms/check printing, for on-page adjustments.
					'adjust_vertical_position'    => 0,
					'show_blank_values'           => true, //Uses "- -" in place of a blank value.
					'blank_value_placeholder'     => '-', //Used to replace blank values with. Was '- -'
					'show_duplicate_values'       => false, //Hides duplicate values in the same columns.
					'duplicate_value_placeholder' => ' ', //Used to replace duplicate values with. Can't be '' as that represents a blank value.
					'auto_refresh'                => false,
			],
			'chart' => [
					'enable'            => false,
					'type'              => 10, //'horizontal_bar', // horizontal_bar, vertical_bar, pie
					//'type' => 'vertical_bar', // horizontal_bar, vertical_bar, pie
					'display_mode'      => 10, //Displays chart above/below table data
					'point_labels'      => true, //Show bar/point labels.
					'include_sub_total' => false, //Include sub_totals in chart.
					'axis_scale_min'    => false, //Set y_axis_minimum value, to rebase the axis scale on.
					'axis_scale_static' => false, //Keeps the same axis scale for all graphs in a group.
					'combine_columns'   => true, //Combine all columns into a single chart.
			],
	];

	//Columns that should never be considered "duplicate" and therefore have their data blanked out in subsequent rows.
	//  Don't include "full_name" here, as that makes certain reports like TimeSheet Detail, All Time By Employee/Date template to look bad.
	public $duplicate_value_ignored_columns = [ 'first_name' => true, 'last_name' => true, 'verified_time_sheet' => true, 'verified_time_sheet_date' => true, 'date_stamp' => true, 'start_date' => true, 'end_date' => true, 'start_time' => true, 'end_time' => true ];

	protected $maximum_memory_limit = false;

	protected $tmp_data = null;
	public $data = null;
	protected $total_row = null;
	protected $data_column_widths = null;
	public $pdf = null;

	public $html = null;

	private $option_cache = []; //Cache getOption() calls as some of them may involve SQL queries.

	protected $chart_images = [];

	protected $form_obj = null;  //Government forms
	protected $form_data = null; //Government forms

	protected $profiler = null;

	public $user_obj = null;
	public $permission_obj = null;
	public $currency_obj = null;
	public $validator = null;

	protected $progress_bar_obj = null;
	protected $api_message_id = null;

	protected $enable_system_columns = false;


	/**
	 * @param $name
	 * @param null $params
	 * @return array|null
	 */
	protected function __getOptions( $name, $params = null ) {
		$retval = null;
		switch ( $name ) {
			case 'page_orientation':
				$retval = [
						'P' => TTi18n::getText( 'Portrait' ),
						'L' => TTi18n::getText( 'Landscape' ),
				];
				break;
			case 'font_size':
				$retval = [
						0   => '-' . TTi18n::getText( 'Default' ) . '-',
						25  => ' 25%',
						50  => ' 50%',
						75  => ' 75%',
						80  => ' 80%',
						85  => ' 85%',
						90  => ' 90%',
						95  => ' 95%',
						100 => '100%',
						105 => '105%',
						110 => '110%',
						115 => '115%',
						120 => '120%',
						125 => '125%',
						150 => '150%',
						175 => '175%',
						200 => '200%',
						225 => '225%',
						250 => '250%',
						275 => '275%',
						300 => '300%',
						400 => '400%',
						500 => '500%',
				];
				break;
			case 'chart_type':
				$retval = [
						10 => TTi18n::getText( 'Bar - Horizontal' ), //'horizontal_bar'
						15 => TTi18n::getText( 'Bar - Vertical' ), //'vertical_bar'
						//20 => TTi18n::getText('Line'), //'line'
						//30 => TTi18n::getText('Pie'), //'pie'
				];
				break;
			case 'chart_display_mode':
				$retval = [
						10 => TTi18n::getText( 'Below Table' ), //'below_table'
						20 => TTi18n::getText( 'Above Table' ), //'above_table'
						30 => TTi18n::getText( 'Chart Only' ), //'chart_only'
				];
				break;
			case 'auto_refresh':
				$retval = [
						0     => TTi18n::getText( 'Disabled' ),
						300   => TTi18n::getText( '5 mins' ),
						600   => TTi18n::getText( '10 mins' ),
						900   => TTi18n::getText( '15 mins' ),
						1800  => TTi18n::getText( '30 mins' ),
						3600  => TTi18n::getText( '1 hour' ),
						7200  => TTi18n::getText( '2 hours' ),
						10800 => TTi18n::getText( '3 hours' ),
						21600 => TTi18n::getText( '6 hours' ),
				];

				if ( PRODUCTION == false ) { //Shorter time for testing.
					$retval[30] = TTi18n::getText( '30 seconds' );
				}

				break;

			//
			//Metadata options...
			//
			case 'metadata_columns':
				$options = [
						'columns'      => [ 'format' => TTi18n::getText( 'Format' ), 'format2' => TTi18n::getText( 'SubFormat' ) ],
						'group_by'     => [ 'aggregate' => TTi18n::getText( 'Aggregate' ) ],
						'sub_total_by' => [ 'aggregate' => TTi18n::getText( 'Aggregate' ) ],
						'sort_order'   => [ 'sort_order' => TTi18n::getText( 'Sort' ) ],
				];
				if ( isset( $params ) && $params != '' && isset( $options[$params] ) ) {
					$retval = $options[$params];
				} else {
					$retval = $options;
				}

				break;
			case 'metadata_column_options':
				$options = [
						'text'      => [],
						//'time_stamp' => array( 'HH:mm' => 'Hours:Minutes', 'HH' => 'Hours', 'HH:mm:ss' => 'Hours:Min:Sec' )
						//'date_stamp' => array( 'DD-MM-YY' => 'Day-Month-Year', 'MM-YY' => 'Month-Year' )
						//'currency' => array( 1 => 'Include dollar sign' 0 => 'Exclude Dollar Sign' )
						//'precision' => array( 1 => '1 Decimal Place', 1 => '2 Decimal Places', )
						//'numeric' => array( 0 => 'w/Seperator', 1 => 'w/o Seperator' )
						//'full_name' = array( 0 => 'First Name', 1 => 'Last Name', 2 => 'First & Last Name', 3 => 'Last & First Name' ),
						'aggregate' => [ false => TTi18n::getText( 'Group By' ), 'min' => TTi18n::getText( 'Min' ), 'avg' => TTi18n::getText( 'Avg' ), 'max' => TTi18n::getText( 'Max' ), 'sum' => TTi18n::getText( 'Sum' ), 'count' => TTi18n::getText( 'Count' ) ],
						'sort'      => [ 'ASC' => TTi18n::getText( 'ASC' ), 'DESC' => TTi18n::getText( 'DESC' ) ],
				];
				if ( isset( $params ) && $params != '' && isset( $options[$params] ) ) {
					$retval = $options[$params];
				} else {
					$retval = $options;
				}
				break;
			case 'column_format_map':
				$retval = [
						10  => 'numeric',
						20  => 'time_unit',
						30  => 'report_date',
						40  => 'currency',
						50  => 'percent',
						60  => 'date_stamp',
						70  => 'time',
						80  => 'time_stamp',
						90  => 'boolean',
						100 => 'time_since',
						110 => 'string',

				];
				break;
			case 'currency':
				if ( is_object( $this->getUserObject()->getCompanyObject() ) ) {
					$crlf = TTnew( 'CurrencyListFactory' ); /** @var CurrencyListFactory $crlf */
					$crlf->getByCompanyId( $this->getUserObject()->getCompanyObject()->getId() );
					$retval = $crlf->getArrayByListFactory( $crlf, false, true );
				} else {
					$retval = false;
				}
				break;
			case 'default_output_format':
				$retval = [
					//Static Columns - Aggregate functions can't be used on these.
					'-1000-pdf'  => TTi18n::gettext( 'PDF' ),
					'-1010-html' => TTi18n::gettext( 'HTML' ),
					'-1020-csv'  => TTi18n::gettext( 'Excel/CSV' ),
				];
				break;
			case 'paycode_columns':
				//Common code for getting report columns based on pay codes.
				$retval = [];

				if ( $params == '' ) {
					$params = 3;
				}

				//Collect absence policies so we know which pay codes need the 'absence_taken' prefix.
				$absence_pay_codes = [];
				$aplf = TTNew( 'AbsencePolicyListFactory' ); /** @var AbsencePolicyListFactory $aplf */
				$aplf->getByCompanyId( $this->getUserObject()->getCompany() );
				if ( $aplf->getRecordCount() > 0 ) {
					foreach ( $aplf as $ap_obj ) {
						$absence_pay_codes[$ap_obj->getPayCode()] = true;
					}
				}
				unset( $aplf, $ap_obj );

				$pclf = TTnew( 'PayCodeListFactory' ); /** @var PayCodeListFactory $pclf */
				$pclf->getByCompanyId( $this->getUserObject()->getCompany() );
				if ( $pclf->getRecordCount() > 0 ) {
					foreach ( $pclf as $pc_obj ) {
						$retval['-' . $params . '190-pay_code:' . $pc_obj->getId() . '_time'] = $pc_obj->getName();
						if ( isset( $absence_pay_codes[$pc_obj->getId()] ) ) {
							$retval['-' . $params . '195-absence_taken_pay_code:' . $pc_obj->getId() . '_time'] = $pc_obj->getName() . ' (' . TTi18n::getText( 'Taken' ) . ')';
						}
						$retval['-' . $params . '290-pay_code:' . $pc_obj->getId() . '_wage'] = $pc_obj->getName() . ' - ' . TTi18n::getText( 'Wage' );
						$retval['-' . $params . '390-pay_code:' . $pc_obj->getId() . '_hourly_rate'] = $pc_obj->getName() . ' - ' . TTi18n::getText( 'Hourly Rate' );
						$retval['-' . $params . '490-pay_code:' . $pc_obj->getId() . '_wage_with_burden'] = $pc_obj->getName() . ' - ' . TTi18n::getText( 'Wage w/Burden' );
						$retval['-' . $params . '590-pay_code:' . $pc_obj->getId() . '_hourly_rate_with_burden'] = $pc_obj->getName() . ' - ' . TTi18n::getText( 'Hourly Rate w/Burden' );
					}
				}
				unset( $pclf, $pc_obj );
				break;
		}

		return $retval;
	}

	/**
	 * Report constructor.
	 */
	function __construct() {
		global $profiler;

		$this->profiler = $profiler;

		//Debug::Text(' Memory Usage: Current: '. memory_get_usage() .' Peak: '. memory_get_peak_usage() .' Limits: Execution: '. $maximum_execution_limit .' Memory: '. $maximum_memory_limit, __FILE__, __LINE__, __METHOD__, 10);

		//Don't set Execution/Memory limits here, as that may affect schema upgrade scripts that need to read report data.
		//Only set them when actually executing the report data in Output();

		return true;
	}

	/**
	 * When this is enabled, it ignores checking that the columns returned are actually in the list of available columns.
	 * Useful for when we need reports to return IDs and such, like when sending AgencyReprots to TimeTrex PaymentServices.
	 * @param $bool
	 * @return bool
	 */
	function setEnableSystemColumns( $bool ) {
		$this->enable_system_columns = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableSystemColumns() {
		return $this->enable_system_columns;
	}

	/**
	 * Defines the max execution timelimit for PHP
	 * @param bool $int
	 * @return bool
	 */
	function setExecutionTimeLimit( $int = false ) {
		if ( $int === false ) {
			$int = $this->config['other']['maximum_execution_limit'];

			global $config_vars;
			if ( isset( $config_vars['other']['report_maximum_execution_limit'] ) && $config_vars['other']['report_maximum_execution_limit'] != '' ) {
				$int = $config_vars['other']['report_maximum_execution_limit'];
			}
		}

		ini_set( 'max_execution_time', $int );

		return true;
	}

	/**
	 * Defines the max execution memory limit for PHP
	 * @param bool $str
	 * @return bool
	 */
	function setExecutionMemoryLimit( $str = false ) {
		if ( $str === false ) {
			$str = $this->config['other']['maximum_memory_limit'];

			global $config_vars;
			if ( isset( $config_vars['other']['report_maximum_memory_limit'] ) && $config_vars['other']['report_maximum_memory_limit'] != '' ) {
				$str = $config_vars['other']['report_maximum_memory_limit'];
			}
		}

		$memory_limit = Misc::getBytesFromSize( $str );
		$available_memory = Misc::getSystemMemoryInfo();
		if ( $available_memory < $memory_limit ) {
			Debug::Text( 'Available memory is less than maximum, reducing to: ' . $available_memory . ' Max Memory: ' . $memory_limit, __FILE__, __LINE__, __METHOD__, 10 );
			$memory_limit = $available_memory;
		}
		$this->maximum_memory_limit = $memory_limit;
		Debug::Text( 'Setting hard memory limit to: ' . $available_memory . ' Soft Limit: ' . $memory_limit . ' Based on: ' . $str, __FILE__, __LINE__, __METHOD__, 10 );

		ini_set( 'memory_limit', $available_memory );

		return true;
	}

	/**
	 * @return bool
	 */
	function isSystemLoadValid() {
		//Check system load and memory usage.
		if ( $this->maximum_memory_limit > 0 && memory_get_usage() > $this->maximum_memory_limit ) {
			Debug::Text( 'Exceeded memory limit: ' . $this->maximum_memory_limit . ' Current Usage: ' . memory_get_usage(), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		if ( Misc::isSystemLoadValid() == false ) {
			return false;
		}

		return true;
	}

	/**
	 * Object of the user generating the report, we use this to base permission checks on, etc...
	 * @param object $obj
	 * @return bool
	 */
	function setUserObject( $obj ) {
		if ( is_object( $obj ) ) {
			$this->user_obj = $obj;

			return true;
		}

		return false;
	}

	/**
	 * @return UserFactory|null
	 */
	function getUserObject() {
		return $this->user_obj;
	}

	/**
	 * @param object $obj
	 * @return bool
	 */
	function setPermissionObject( $obj ) {
		if ( is_object( $obj ) ) {
			$this->permission_obj = $obj;

			return true;
		}

		return false;
	}

	/**
	 * @return null
	 */
	function getPermissionObject() {
		return $this->permission_obj;
	}

	/**
	 * Object of the currency used in the report, we use this to base currency column formats on.
	 * @return bool
	 */
	function getCurrencyConvertToBase() {
		$filter_data = $this->getFilterConfig();

		$currency_convert_to_base = false;
		if ( isset( $filter_data['currency_id'] ) == false ) {
			//Check to see if there are more than one possible currency records.
			if ( is_object( $this->getUserObject()->getCompanyObject()->getBaseCurrencyObject() )
					&& $this->getUserObject()->getCompanyObject()->getTotalCurrencies() > 1 ) {
				Debug::Text( 'Converting currency to base... (a)', __FILE__, __LINE__, __METHOD__, 10 );
				$currency_convert_to_base = true;
			}
		} else if ( count( $filter_data['currency_id'] ) > 1 ) {
			Debug::Text( 'Converting currency to base... (b)', __FILE__, __LINE__, __METHOD__, 10 );
			$currency_convert_to_base = true;
		}

		return $currency_convert_to_base;
	}

	/**
	 * @return bool|object
	 */
	function getBaseCurrencyObject() {
		$base_currency_obj = false;
		if ( is_object( $this->getUserObject()->getCompanyObject()->getBaseCurrencyObject() ) ) {
			$base_currency_obj = $this->getUserObject()->getCompanyObject()->getBaseCurrencyObject();
		}

		return $base_currency_obj;
	}

	/**
	 * @param $currency_convert_to_base
	 * @param object $base_currency_obj
	 * @param $filter_data
	 * @return bool
	 */
	function handleReportCurrency( $currency_convert_to_base, $base_currency_obj, $filter_data ) {
		//$currency_convert_to_base = $this->getCurrencyConvertToBase(); //This ignores the 1st argument to this function, which defeats its purpose. Make sure we respect it now.
		$base_currency_obj = $this->getBaseCurrencyObject();
		$filter_data = $this->getFilterConfig();

		$crlf = TTnew( 'CurrencyListFactory' ); /** @var CurrencyListFactory $crlf */
		if ( $currency_convert_to_base == true && is_object( $base_currency_obj ) ) {
			$this->setCurrencyObject( $base_currency_obj );
		} else {
			if ( ( isset( $filter_data['currency_id'][0] ) && is_array( $filter_data['currency_id'] ) && TTUUID::isUUID( $filter_data['currency_id'][0] ) && $filter_data['currency_id'][0] != TTUUID::getZeroID() && $filter_data['currency_id'][0] != TTUUID::getNotExistID() )
					|| ( isset( $filter_data['currency_id'] ) && !is_array( $filter_data['currency_id'] )
							&& TTUUID::isUUID( $filter_data['currency_id'] ) && $filter_data['currency_id'] != TTUUID::getZeroID() && $filter_data['currency_id'] != TTUUID::getNotExistID() ) ) {
				$crlf->getByIdAndCompanyId( ( isset( $filter_data['currency_id'][0] ) ) ? $filter_data['currency_id'][0] : $filter_data['currency_id'], $this->getUserObject()->getCompany() );
				if ( $crlf->getRecordCount() == 1 ) {
					$this->setCurrencyObject( $crlf->getCurrent() );
				}
			} else if ( is_object( $base_currency_obj ) ) {
				$this->setCurrencyObject( $base_currency_obj );
			}
		}

		return true;
	}

	/**
	 * @param object $obj
	 * @return bool
	 */
	function setCurrencyObject( $obj ) {
		if ( is_object( $obj ) ) {
			Debug::Text( 'Setting Report Currency to: ' . $obj->getISOCode(), __FILE__, __LINE__, __METHOD__, 10 );
			$this->currency_obj = $obj;

			return true;
		}

		return false;
	}

	/**
	 * @return null
	 */
	function getCurrencyObject() {
		return $this->currency_obj;
	}

	/**
	 * Used for TTLog::addEntry.
	 * @return string
	 */
	function getTable() {
		return 'report';
	}

	/**
	 * @return null|ProgressBar
	 */
	function getProgressBarObject() {
		if ( !is_object( $this->progress_bar_obj ) ) {
			$this->progress_bar_obj = new ProgressBar();
		}

		return $this->progress_bar_obj;
	}

	/**
	 * Returns the API messageID for each individual call.
	 * @return bool|null
	 */
	function getAPIMessageID() {
		if ( $this->api_message_id != null ) {
			return $this->api_message_id;
		}

		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setAPIMessageID( $id ) {
		Debug::Text(  'API Message ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $id != '' ) {
			$this->api_message_id = $id;

			return true;
		}

		return false;
	}

	/**
	 * Set all options at once.
	 * @param $data
	 * @return bool
	 */
	function setConfig( $data ) {
		if ( is_array( $data ) ) {
			$data = Misc::trimSortPrefix( $data );

			Debug::Arr( $data, 'setConfig(): ', __FILE__, __LINE__, __METHOD__, 10 );
			// Initialize the custom columns array
			$custom_columns = [];

			//Handle merging in each set*Config() function instead.
			if ( isset( $data['columns'] ) ) {
				$this->setColumnConfig( (array)$data['columns'] );
				$custom_columns = array_merge( $custom_columns, (array)$data['columns'] );
			}


			// Set the user defined filters.
			if ( isset( $data['custom_filter'] ) ) {
				$this->setCustomFilterConfig( (array)$data['custom_filter'] );
				$custom_columns = array_merge( $custom_columns, (array)$data['custom_filter'] );
			}

			if ( isset( $data['group'] ) ) {
				$this->setGroupConfig( (array)$data['group'] );
			}

			if ( isset( $data['sub_total'] ) ) {
				$this->setSubTotalConfig( (array)$data['sub_total'] );
			}

			if ( isset( $data['page_break'] ) ) {
				$this->setPageBreakConfig( (array)$data['page_break'] );
			}

			//Work around bug in Flex that sends config sort data as "sort_" array element.
			if ( isset( $data['sort_'] ) && !isset( $data['sort'] ) ) {
				$data['sort'] = $data['sort_'];
				unset( $data['sort_'] );
			}
			//This must come after sub_total, as sort needs to adjust itself automatically based on sub_total.
			$this->setSortConfig( ( isset( $data['sort'] ) ? (array)$data['sort'] : [] ) ); //If no sorting is specified at all, we still need to call this function so we can fallback to some sane defaults determined within it.

			if ( isset( $data['chart'] ) ) {
				$this->setChartConfig( (array)$data['chart'] );
			}
			if ( isset( $data['form'] ) ) {
				$this->setFormConfig( (array)$data['form'] );
			}
			if ( isset( $data['other'] ) ) {
				$this->setOtherConfig( (array)$data['other'] );
			}
			// Set the user defined columns(including the defined filters).
			$this->setCustomColumnConfig( $custom_columns );

			//Remove special data, then the remaining is all filter data.
			unset( $data['columns'], $data['group'], $data['sub_total'], $data['sort'], $data['page_break'], $data['other'], $data['chart'], $data['form'], $data['custom_filter'] );
			if ( isset( $data['filter'] ) ) {
				$data = array_merge( $data, (array)$data['filter'] );
				unset( $data['filter'] );
			}

			$this->setFilterConfig( $data );

			return true;
		}

		return false;
	}

	/**
	 * Get all options
	 * @return array
	 */
	function getConfig() {
		return $this->config;
	}

	/**
	 * @param $name
	 * @return bool|mixed
	 */
	function getTemplate( $name ) {
		if ( !empty( $name ) ) {
			$config = $this->getOptions( 'template_config', [ 'template' => $name ] );
			if ( is_array( $config ) ) {
				return $config;
			}
		}

		return false;
	}

	/**
	 * Loads a template config.
	 * @param $name
	 * @return bool
	 */
	function loadTemplate( $name ) {
		$config = $this->getTemplate( $name );
		if ( is_array( $config ) ) {
			//Merge template with existing config data, so we can keep any default settings.
			$this->setConfig( Misc::trimSortPrefix( array_merge( $this->config, $config ) ) );

			//Debug::Arr($this->config, '  bConfig:', __FILE__, __LINE__, __METHOD__, 10);

			return true;
		}

		return false;
	}

	/**
	 * Store column options - This must be in the format of 'column' => TRUE, ie: 'regular_time => TRUE
	 * @param $data
	 * @return bool
	 */
	function setColumnConfig( $data ) {
		if ( isset( $data[0] ) ) {
			//array of format: array('col1', 'col2', 'col3') was passed, flip it first before saving it. so Flex can use the array key to maintain order
			$formatted_data = array_fill_keys( array_unique( $data ), true ); //Fill all array keys with TRUE.
		} else {
			$formatted_data = $data;
		}

		//Remove any columns that aren't valid anymore. For example if a Pay Stub Account was added to a report, then later deleted from the system completely.
		$this->config['columns'] = array_intersect_key( Misc::trimSortPrefix( $formatted_data ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );

		return true;
	}

	/**
	 * @return bool|mixed
	 */
	function getColumnConfig() {
		if ( isset( $this->config['columns'] ) ) {
			return $this->config['columns'];
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function setColumnDataConfig( $data ) {
		if ( is_array( $data ) ) {
			//getColumnConfig() can return FALSE, make sure we don't merge an array where 0 => FALSE, as that will prevent us from including *all* columns in the report.
			// and instead no columns will be included.
			$data = array_merge( (array)$data, ( is_array( $this->getColumnConfig() ) ) ? $this->getColumnConfig() : [] );
			$this->config['columns_data'] = $data;
		}

		return true;
	}

	/**
	 * @return bool|mixed
	 */
	function getColumnDataConfig() {
		if ( isset( $this->config['columns_data'] ) ) {
			return $this->config['columns_data'];
		}

		return $this->getColumnConfig();
	}

	/**
	 * @param $time_period_arr
	 * @param null $prefix
	 * @param bool $force_dates_for_pay_periods
	 * @return array|bool
	 */
	function convertTimePeriodToStartEndDate( $time_period_arr, $prefix = null, $force_dates_for_pay_periods = false ) {
		Debug::Arr( $time_period_arr, 'Input: Time Period Array: ', __FILE__, __LINE__, __METHOD__, 10 );

		$retarr = [];
		//Convert time_period into start/end date, with pay_period_schedule_ids if necessary.
		if ( isset( $time_period_arr['time_period'] )
				&& ( $time_period_arr['time_period'] == 'custom_date' || $time_period_arr['time_period'] == 'custom_time' ) ) {
			Debug::Text( 'Found Custom dates...', __FILE__, __LINE__, __METHOD__, 10 );
			$retarr[$prefix . 'time_period']['time_period'] = $time_period_arr['time_period'];
			if ( isset( $time_period_arr['start_date'] ) ) {
				$retarr[$prefix . 'start_date'] = TTDate::getBeginDayEpoch( TTDate::parseDateTime( $time_period_arr['start_date'] ) );
			}
			if ( isset( $time_period_arr['end_date'] ) ) {
				$retarr[$prefix . 'end_date'] = TTDate::getEndDayEpoch( TTDate::parseDateTime( $time_period_arr['end_date'] ) );
			}
		} else if ( isset( $time_period_arr['time_period'] ) ) {
			$params = [];
			if ( isset( $time_period_arr['pay_period_schedule_id'] ) ) {
				$params = [ 'pay_period_schedule_id' => $time_period_arr['pay_period_schedule_id'] ];
				//Make sure we keep the original array intact so we if this function is run more than once it will work each time.
				$retarr[$prefix . 'time_period']['pay_period_schedule_id'] = $time_period_arr['pay_period_schedule_id'];
			} else if ( isset( $time_period_arr['pay_period_id'] ) ) {
				$params = [ 'pay_period_id' => $time_period_arr['pay_period_id'] ];
				//Make sure we keep the original array intact so we if this function is run more than once it will work each time.
				$retarr[$prefix . 'time_period']['pay_period_id'] = $time_period_arr['pay_period_id'];
			}

			if ( !isset( $time_period_arr['time_period'] ) ) {
				Debug::Text( 'ERROR: Time Period idenfier not specified!', __FILE__, __LINE__, __METHOD__, 10 );
				$retarr[$prefix . 'time_period'] = null;
			} else {
				$retarr[$prefix . 'time_period']['time_period'] = $time_period_arr['time_period'];
			}

			//Debug::Arr($params, 'Time Period: '.$time_period_arr['time_period'] .' Params: ', __FILE__, __LINE__, __METHOD__, 10);
			$time_period_dates = TTDate::getTimePeriodDates( $time_period_arr['time_period'], null, $this->getUserObject(), $params );
			if ( $time_period_dates != false ) {
				if ( isset( $time_period_dates['start_date'] ) ) {
					$retarr[$prefix . 'start_date'] = $time_period_dates['start_date'];
				}
				if ( isset( $time_period_dates['end_date'] ) ) {
					$retarr[$prefix . 'end_date'] = $time_period_dates['end_date'];
				}
				if ( isset( $time_period_dates['pay_period_id'] ) ) {
					$retarr[$prefix . 'pay_period_id'] = $time_period_dates['pay_period_id'];
				}
			} else {
				//No pay period find default to no time period, otherwise the report can take forever to finish.
				Debug::Text( 'No pay periods found, defaulting to none (0)...', __FILE__, __LINE__, __METHOD__, 10 );
				$retarr[$prefix . 'pay_period_id'] = TTUUID::getZeroID(); //This can actually find data not assigned to a pay period.
			}

			if ( $force_dates_for_pay_periods == true && isset( $retarr[$prefix . 'pay_period_id'] ) ) {
				Debug::Text( 'Attempting to convert pay periods to start/end dates...', __FILE__, __LINE__, __METHOD__, 10 );
				$pplf = TTNew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
				$pplf->getByIdList( $retarr[$prefix . 'pay_period_id'], null, [ 'start_date' => 'asc' ] );
				if ( $pplf->getRecordCount() > 0 ) {
					foreach ( $pplf as $pp_obj ) {
						if ( !isset( $retarr[$prefix . 'start_date'] ) || $pp_obj->getStartDate() < $retarr[$prefix . 'start_date'] ) {
							$retarr[$prefix . 'start_date'] = $pp_obj->getStartDate();
						}
						if ( !isset( $retarr[$prefix . 'end_date'] ) || $pp_obj->getEndDate() > $retarr[$prefix . 'start_date'] ) {
							$retarr[$prefix . 'end_date'] = $pp_obj->getEndDate();
						}
					}
				}
				unset( $pplf );
			}
		} else {
			Debug::Text( 'Invalid TimePeriod filter...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::Arr( $retarr, 'Output: Time Period Array: ', __FILE__, __LINE__, __METHOD__, 10 );

		return $retarr;
	}

	/**
	 * Store filter options
	 * @param $data
	 * @return bool
	 */
	function setFilterConfig( $data ) {
		$data = Misc::trimSortPrefix( $data );

		//Allow report sub-class to parse custom time periods for filtering on things like hire dates, termination, expiry, etc...
		if ( method_exists( $this, '_setFilterConfig' ) ) {
			$data = $this->_setFilterConfig( $data );
		}

		if ( isset( $data['time_period'] ) && is_array( $data['time_period'] ) ) {
			Debug::Text( 'Found TimePeriod...', __FILE__, __LINE__, __METHOD__, 10 );
			$data = array_merge( $data, (array)$this->convertTimePeriodToStartEndDate( $data['time_period'] ) );
		}

		//Check for other time_period arrays.
		if ( is_array( $data ) ) {
			foreach ( $data as $column => $column_data ) {
				if ( strpos( $column, '_time_period' ) !== false ) {
					Debug::Text( 'Found Custom TimePeriod... Column: ' . $column, __FILE__, __LINE__, __METHOD__, 10 );
					$data = array_merge( $data, (array)$this->convertTimePeriodToStartEndDate( $data[$column], str_replace( '_time_period', '', $column . '_' ) ) );
				}
			}
			unset( $column_data ); //code standards
		}

		Debug::Arr( $data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10 );
		$this->config['filter'] = $data;

		return true;
	}

	/**
	 * @return bool|mixed
	 */
	function getFilterConfig() {
		if ( isset( $this->config['filter'] ) ) {
			return $this->config['filter'];
		}

		return false;
	}

	/**
	 * Used for converting $test[] = blah, or $test[] = array( 'col' => blah ) to $test['col'] => $blah.
	 * Mainly for multi-dimension awesomebox group by, sub_total by, sorting...
	 * @param $arr
	 * @return array|bool
	 */
	function convertArrayNumericKeysToString( $arr ) {
		$retarr = [];
		foreach ( $arr as $key => $value ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $key2 => $value2 ) {
					$retarr[$key2] = $value2;
				}
			} else {
				$retarr[$key] = $value;
			}
		}

		if ( empty( $retarr ) == false ) {
			return $retarr;
		}

		return false;
	}

	/**
	 * @return array|bool
	 */
	function formatGroupConfig() {
		$group_config = $this->getGroupConfig();
		if ( is_array( $group_config ) ) {
			$metadata = $this->getOptions( 'group_by_metadata' );
			if ( is_array( $metadata ) ) {
				$aggregates = $metadata['aggregate'];
			} else {
				$aggregates = $this->getOptions( 'aggregates' );
			}
			//Debug::Arr($aggregates, 'Aggregates: ', __FILE__, __LINE__, __METHOD__, 10);
			if ( isset( $group_config[0] ) && is_array( $group_config[0] ) ) {
				$group_data = array_merge( $aggregates, $this->convertArrayNumericKeysToString( $group_config ) );
			} else if ( isset( $group_config[0] ) && $group_config[0] !== false ) {
				//Merge passed group array with default aggregates from sub-class
				//Make sure group columns passed in from user take precendent, as we will want to be able to group by any column like hourly_rate eventually,
				//This is needed for PayrollExportReport to break rows out by different hourly rates.
				//$group_data = array_merge( array_flip( $group_config ), (array)$aggregates );
				$group_data = array_merge( (array)$aggregates, array_flip( $group_config ) );
				//Debug::Arr($group_data, 'Final Group Data: ', __FILE__, __LINE__, __METHOD__, 10);
			} else {
				Debug::Text( 'ERROR: Group data cannot be determined!', __FILE__, __LINE__, __METHOD__, 10 );
				$group_data = false;
			}

			return $group_data;
		}

		return false;
	}

	/**
	 * Grouping options - Use a single re-orderable dropdown for grouping options?
	 * Add function like: getGroupOptions( $columns ), that only shows the possible group_by columns based on the displayed columns?
	 * @param $data
	 * @return bool
	 */
	function setGroupConfig( $data ) {
		if ( !is_array( $data ) || ( is_array( $data ) && ( count( $data ) == 0 || $data[0] === false || $data[0] === TTUUID::getZeroID() ) ) ) {
			return false;
		}

		//$data should be a basic array of: 0 => 'first_name', 1 => 'last_name', etc... Convert to this to a
		$this->config['group'] = $data;

		return true;
	}

	/**
	 * @return bool|mixed
	 */
	function getGroupConfig() {
		if ( isset( $this->config['group'] ) ) {
			return $this->config['group'];
		}

		return false;
	}

	/**
	 * Page Break On options - Use a single re-orderable dropdown for page break options.
	 * @param $data
	 * @return bool
	 */
	function setPageBreakConfig( $data ) {
		if ( !is_array( $data ) || ( is_array( $data ) && ( count( $data ) == 0 || $data[0] === false || $data[0] === TTUUID::getZeroID() ) ) ) {
			return false;
		}

		//$data should be a basic array of: 0 => 'first_name', 1 => 'last_name', etc... Convert to this to a
		$this->config['page_break'] = $data;

		return true;
	}

	/**
	 * @return bool|mixed
	 */
	function getPageBreakConfig() {
		if ( isset( $this->config['page_break'] ) ) {
			return $this->config['page_break'];
		}

		return false;
	}

	/**
	 * When using grouping, we have to be able to get a list of just the columns that will be displayed for reporting purposes.
	 * @param bool $num
	 * @return bool|mixed
	 */
	function getReportColumns( $num = false ) {
		$columns = $this->getColumnConfig();

		$format_group_config = $this->formatGroupConfig();
		if ( is_array( $format_group_config ) ) {
			$group_data = array_keys( $format_group_config );
			//Debug::Arr($group_data, 'testGroup Data: ', __FILE__, __LINE__, __METHOD__, 10);

			$static_columns = array_keys( Misc::trimSortPrefix( $this->getOptions( 'static_columns' ) ) );

			$invalid_columns = array_diff( $static_columns, $group_data );
			//Debug::Arr($invalid_columns, 'Invalid Columns due to grouping... Removing from column list: ', __FILE__, __LINE__, __METHOD__, 10);
			if ( is_array( $invalid_columns ) ) {
				foreach ( $invalid_columns as $invalid_column ) {
					unset( $columns[$invalid_column] );
				}
			}
			//Debug::Arr($columns, 'Remaining Column Config: ', __FILE__, __LINE__, __METHOD__, 10);
		}

		if ( $num !== false ) {
			$column_keys = array_keys( (array)$columns );
			if ( isset( $column_keys[$num] ) ) {
				return $column_keys[$num];
			} else {
				return false;
			}
		}

		return $columns;
	}

	/**
	 * When multiple columns are selected for sub-totaling, we need to multiply the sub-total passes,
	 * ie: pay_period, branch, department would need to sub-total on pay_period.branch.department, pay_period.branch, pay_period
	 * @return array|bool
	 */
	function formatSubTotalConfig() {
		$sub_total_config = $this->getSubTotalConfig();

		if ( is_array( $sub_total_config ) && count( $sub_total_config ) > 0 ) {
			$metadata = $this->getOptions( 'sub_total_by_metadata' );
			if ( is_array( $metadata ) ) {
				$aggregates = $metadata['aggregate'];
			} else {
				$aggregates = (array)$this->getOptions( 'aggregates' );
			}
			//Debug::Arr($aggregates, 'Aggregates: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( isset( $sub_total_config[0] ) ) {
				$sub_total_config = $this->convertArrayNumericKeysToString( $sub_total_config );
			}

			//Multiple sub-total config into each iteration. Order of the columns matters.
			//Reverse the array then
			$sub_total_data = [];
			for ( $i = 0; $i < count( $sub_total_config ); $i++ ) {
				$n = ( count( $sub_total_config ) - 1 );
				foreach ( $sub_total_config as $column ) {
					if ( $n >= $i ) {
						$sub_total_data[$i][] = $column;
					}
					$n--;
				}
				$sub_total_data[$i] = array_merge( array_flip( $sub_total_data[$i] ), $aggregates );
			}

			//Debug::Arr($sub_total_data, 'Final SubTotal Data: ', __FILE__, __LINE__, __METHOD__, 10);

			return $sub_total_data;
		}

		return false;
	}

	/**
	 * Sub-Total options - If grouping is being used, we can only sub-total based on grouped columns.
	 * In any case we can't sub-total by the last column, as that wouldn't make any sense anyways.
	 * @param $data
	 * @return bool
	 */
	function setSubTotalConfig( $data ) {
		if ( !is_array( $data ) || ( is_array( $data ) && ( count( $data ) == 0 || $data[0] === false || $data[0] === TTUUID::getZeroID() ) ) ) {
			return false;
		}
		//$data should be a basic array of: 0 => 'first_name', 1 => 'last_name', etc... It will be converted later.

		//Make sure sub_total doesn't contain the last (or only) group by column, as it will sub-total every row.
		if ( is_array( $this->getGroupConfig() ) && count( $this->getGroupConfig() ) > 0 ) {
			$group_config = array_reverse( $this->getGroupConfig() );
			$bad_key = array_search( $group_config[0], $data );
			if ( $bad_key !== false ) {
				Debug::Text( 'Removing bad sub-total column: ' . $data[$bad_key], __FILE__, __LINE__, __METHOD__, 10 );
				unset( $data[$bad_key] );
			}
		}

		$this->config['sub_total'] = $data;

		return true;
	}

	/**
	 * @return bool|mixed
	 */
	function getSubTotalConfig() {
		if ( isset( $this->config['sub_total'] ) ) {
			return $this->config['sub_total'];
		}

		return false;
	}

	/**
	 * Sorting options
	 * When sub-totaling, we must sort by the sub-total columns *first*, otherwise the sub-totals won't be in the right place.
	 * @param $data
	 * @return bool
	 */
	function setSortConfig( $data ) {
		//If no sorting is specified at all (blank or NONE), fallback to the first group by column, sub-total column, then display columns.
		//   This ensures there is always some level of sorting, and the below code to always sort by sub-total columns also gets triggered.
		if ( empty( $data ) ) {
			$group_config = $this->getGroupConfig();
			if ( is_array( $group_config ) && isset( $group_config[0] ) ) {
				$data[0] = [ $group_config[0] => 'asc' ];
			} else {
				$sub_total_config = $this->getSubTotalConfig();
				if ( is_array( $sub_total_config ) && isset( $sub_total_config[0] ) ) {
					$data[0] = [ $sub_total_config[0] => 'asc' ];
				} else {
					$columns = $this->getColumnConfig();
					if ( is_array( $columns ) && count( $columns ) > 0 ) {
						reset( $columns );
						$data[0] = [ key( $columns ) => 'asc' ];
					}
				}
			}
		}

		$formatted_data = [];

		//Get any sub_total columns, and use them to sort by first.
		$sub_total_config = $this->getSubTotalConfig();
		if ( is_array( $sub_total_config ) ) {
			foreach ( $sub_total_config as $sub_total_col ) {
				$formatted_data[$sub_total_col] = 'asc';
			}
		}

		if ( isset( $data[0] ) ) {
			//Allow alternative format of: array( 0 => array('col1' => 'asc'), 1 => array('col2' => 'desc') ) so Flex can use the array key to maintain order
			foreach ( $data as $sort_arr ) {
				if ( is_array( $sort_arr ) ) {
					foreach ( $sort_arr as $sort_col => $sort_dir ) {
						$formatted_data[$sort_col] = $sort_dir;
					}
				}
			}
		} else {
			$formatted_data = $data;
		}


		$this->config['sort'] = $formatted_data;

		return true;
	}

	/**
	 * @return bool|mixed
	 */
	function getSortConfig() {
		if ( isset( $this->config['sort'] ) ) {
			return $this->config['sort'];
		}

		return false;
	}

	/**
	 * Uses UserReportData class to save the form config for the entire company.
	 * NOTE: This is duplicated in SetupPresets class. If you change it here, change it there too.
	 * @param null $data
	 * @return bool
	 */
	function setCompanyFormConfig( $data = null ) {
		if ( $this->checkPermissions() == false ) {
			Debug::Text( 'Invalid permissions!', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		if ( $data == '' || !is_array( $data ) ) {
			$data = $this->getFormConfig();
		}

		if ( is_object( $this->getUserObject() ) ) {
			$urdf = TTnew( 'UserReportDataFactory' ); /** @var UserReportDataFactory $urdf */

			$urdlf = TTnew( 'UserReportDataListFactory' ); /** @var UserReportDataListFactory $urdlf */
			$urdlf->getByCompanyIdAndScriptAndDefault( $this->getUserObject()->getCompany(), get_class( $this ) );
			if ( $urdlf->getRecordCount() > 0 ) {
				$urdf->setID( $urdlf->getCurrent()->getID() );
			}
			$urdf->setCompany( $this->getUserObject()->getCompany() );
			$urdf->setScript( get_class( $this ) );
			$urdf->setName( $this->title );
			$urdf->setData( $data );
			$urdf->setDefault( true );
			if ( $urdf->isValid() ) {
				$urdf->Save();
			}

			return true;
		}

		return false;
	}

	/**
	 * @return bool|array
	 */
	function getCompanyFormConfig() {
		if ( $this->checkPermissions() == false ) {
			Debug::Text( 'Invalid permissions!', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$urdlf = TTnew( 'UserReportDataListFactory' ); /** @var UserReportDataListFactory $urdlf */
		$urdlf->getByCompanyIdAndScriptAndDefault( $this->getUserObject()->getCompany(), get_class( $this ) );
		if ( $urdlf->getRecordCount() > 0 ) {
			$urd_obj = $urdlf->getCurrent();
			Debug::Text( 'Found Company Report Setup! ID: ' . $urd_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
			$data = $urd_obj->getData();

			//Debug::Arr( $data, '  Company Report Setup!', __FILE__, __LINE__, __METHOD__, 10);
			return $data;
		}
		unset( $urdlf );

		return false;
	}

	/**
	 * Used for government form config.
	 * @param $data
	 * @return bool
	 */
	function setFormConfig( $data ) {
		//Check to see if existing data for the form has already been saved.
		if ( $this->getCompanyFormConfig() === false ) {
			$this->setCompanyFormConfig( $data ); //If no form config has been saved yet, do so on the first report generation only.
		}

		$this->config['form'] = $data;

		return true;
	}

	/**
	 * @return bool|mixed
	 */
	function getFormConfig() {
		if ( isset( $this->config['form'] ) ) {
			return $this->config['form'];
		}

		return false;
	}

	/**
	 * Misc. options
	 * @param $data
	 * @return bool
	 */
	function setOtherConfig( $data ) {
		if ( is_array( $data ) ) {
			if ( !isset( $data['default_font'] ) || ( isset( $data['default_font'] ) && $data['default_font'] == '' ) ) {
				$data['default_font'] = TTi18n::getPDFDefaultFont( null, $this->getUserObject()->getCompanyObject()->getEncoding() );
			}
			Debug::Text( 'Report default font: ' . $data['default_font'], __FILE__, __LINE__, __METHOD__, 10 );

			if ( isset( $data['maximum_page_limit'] ) && (int)$data['maximum_page_limit'] != 0 ) {
				if ( $data['maximum_page_limit'] > 10000 ) {
					$data['maximum_page_limit'] = 10000;
				} else if ( $data['maximum_page_limit'] < 2 ) {
					$data['maximum_page_limit'] = 2;
				}
			} else {
				unset( $data['maximum_page_limit'] ); //Use default.
			}
			$this->config['other'] = array_merge( $this->config['other'], $data ); //Merge data as to keep default settings whenever possible.

			return true;
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getOtherConfig() {
		if ( isset( $this->config['other'] ) ) {
			return $this->config['other'];
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function isEnabledChart() {
		$config = $this->getChartConfig();
		if ( isset( $config['enable'] ) && $config['enable'] == true ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function setChartConfig( $data ) {
		$this->config['chart'] = $data;

		return true;
	}

	/**
	 * @return bool|mixed
	 */
	function getChartConfig() {
		if ( isset( $this->config['chart'] ) ) {
			return $this->config['chart'];
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function setCustomFilterConfig( $data ) {
		$this->config['custom_filter'] = $data;

		return true;
	}

	/**
	 * @return bool|mixed
	 */
	function getCustomFilterConfig() {
		if ( isset( $this->config['custom_filter'] ) ) {
			return $this->config['custom_filter'];
		}

		return false;
	}

	/**
	 * Validates report config, mainly so users aren't surprised when they set group by options that aren't doing anything.
	 * @param bool $format
	 * @return null|Validator
	 */
	function validateConfig( $format = false ) {
		$this->validator = new Validator();

		if ( method_exists( $this, '_validateConfig' ) ) {
			$this->_validateConfig();
		}

		$column_options = Misc::trimSortPrefix( $this->getOptions( 'columns' ) );
		$config = $this->getConfig();

		//Reports with other formats (Tax reports, printable timesheets), don't specify columns.
		if ( !isset( $config['columns'] ) && in_array( $format, [ 'pdf', 'csv', 'html' ] ) ) {
			$this->validator->isTrue( 'columns', false, TTi18n::gettext( 'No columns specified to display on report' ) );
			$config['columns'] = [];
		}

		if ( isset( $config['filter']['time_period'] )
				&& isset( $config['filter']['time_period']['time_period'] )
				&& $config['filter']['time_period']['time_period'] == 'custom_pay_period'
				&& ( !isset( $config['filter']['pay_period_id'] )
						|| $config['filter']['pay_period_id'] == TTUUID::getZeroID()
						|| count( $config['filter']['pay_period_id'] ) == 0
						|| ( isset( $config['filter']['pay_period_id'][0] ) && $config['filter']['pay_period_id'][0] == false )
				)
		) {
			$this->validator->isTrue( 'time_period', false, TTi18n::gettext( 'Time Period is set to Custom Pay Period, but no pay period is selected' ) );
		}

		if ( isset( $config['filter']['time_period'] )
				&& isset( $config['filter']['time_period']['time_period'] )
				&& $config['filter']['time_period']['time_period'] == 'custom_date'
				&& ( !isset( $config['filter']['start_date'] )
						|| !isset( $config['filter']['end_date'] )
						|| ( isset( $config['filter']['start_date'] ) && $config['filter']['start_date'] == '' )
						|| ( isset( $config['filter']['end_date'] ) && $config['filter']['end_date'] == '' )
				)
		) {
			$this->validator->isTrue( 'time_period', false, TTi18n::gettext( 'Time Period is set to Custom Dates, but dates are not specified' ) );
		}

		//Make sure any group/sub_total columns are also being displayed.
		if ( isset( $config['group'] ) && is_array( $config['group'] ) && isset( $config['columns'] ) && is_array( $config['columns'] ) ) {
			$group_diff = array_diff( $config['group'], array_keys( $config['columns'] ) );
			if ( is_array( $group_diff ) && count( $group_diff ) > 0 ) {
				foreach ( $group_diff as $group_bad_column ) {
					if ( !isset( $column_options[$group_bad_column] ) ) {       //Prevent PHP warning.
						$column_options[$group_bad_column] = $group_bad_column; //If column doesn't exist, at least show the column key rather than NULL so we have something to go on.
					}

					$this->validator->isTrue( 'group', false, TTi18n::gettext( 'Group by defines column that is not being displayed on the report' ) . ': ' . $column_options[$group_bad_column] );
				}
			}
		}

		if ( isset( $config['sub_total'] ) && is_array( $config['sub_total'] ) && isset( $config['columns'] ) && is_array( $config['columns'] ) ) {
			$sub_total_diff = array_diff( $config['sub_total'], array_keys( $config['columns'] ) );
			if ( is_array( $sub_total_diff ) && count( $sub_total_diff ) > 0 ) {
				foreach ( $sub_total_diff as $sub_total_bad_column ) {
					if ( !isset( $column_options[$sub_total_bad_column] ) ) {           //Prevent PHP warning.
						$column_options[$sub_total_bad_column] = $sub_total_bad_column; //If column doesn't exist, at least show the column key rather than NULL so we have something to go on.
					}

					$this->validator->isTrue( 'sub_total', false, TTi18n::gettext( 'Sub Total defines column that is not being displayed on the report' ) . ': ' . $column_options[$sub_total_bad_column] );
				}
			}
		}

		if ( isset( $config['page_break'] ) && is_array( $config['page_break'] ) && isset( $config['columns'] ) && is_array( $config['columns'] ) ) {
			$page_break_diff = array_diff( $config['page_break'], array_keys( $config['columns'] ) );
			if ( is_array( $page_break_diff ) && count( $page_break_diff ) > 0 ) {
				foreach ( $page_break_diff as $page_break_bad_column ) {
					if ( !isset( $column_options[$page_break_bad_column] ) ) {            //Prevent PHP warning.
						$column_options[$page_break_bad_column] = $page_break_bad_column; //If column doesn't exist, at least show the column key rather than NULL so we have something to go on.
					}

					$this->validator->isTrue( 'page_break', false, TTi18n::gettext( 'Page Break On defines column that is not being displayed on the report' ) . ': ' . $column_options[$page_break_bad_column] );
				}
			}
		}

		//Debug::Arr( $config, 'Config: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr( $this->validator, 'Validate Report Config: ', __FILE__, __LINE__, __METHOD__, 10);
		return $this->validator;
	}

	/**
	 * Returns the default file name if none is specified.
	 * @return string
	 */
	function getFileName() {
		return $this->file_name . '_' . date( 'Y_m_d' ) . '.pdf';
	}

	/**
	 * Returns the default file mime type if none is specified.
	 * @return string
	 */
	function getFileMimeType() {
		return $this->file_mime_type;
	}

	/**
	 * Return options from sub-class for things like columns, sorting columns, grouping columns, sub-total columns, etc...
	 * @param $name
	 * @param null $params
	 * @return bool|mixed
	 */
	function getOptions( $name, $params = null ) {
		//Cache getOption() calls as it could require several SQL queries.
		$id = $name . serialize( $params );

		if ( $params == null || $params == '' ) {
			if ( !isset( $this->option_cache[$id] ) ) {
				$this->option_cache[$id] = $this->_getOptions( $name );
			}

			return $this->option_cache[$id];
		}

		if ( !isset( $this->option_cache[$id] ) ) {
			$this->option_cache[$id] = $this->_getOptions( $name, $params );
		}

		return $this->option_cache[$id];
	}

	/**
	 * @param $name
	 * @param null $params
	 * @return bool
	 */
	protected function _getOptions( $name, $params = null ) {
		return false;
	}

	/**
	 * Get raw data for report
	 * @param $format
	 * @return bool
	 */
	function getData( $format ) {
		Debug::Arr( $this->config, 'Final Report Config: ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->profiler->startTimer( 'getData' );
		$this->_getData( $format );
		$this->profiler->stopTimer( 'getData' );
		Debug::Text( ' Memory Usage: Current: ' . memory_get_usage() . ' Peak: ' . memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/**
	 * PreProcess data such as calculating additional columns (Day of Week, Year Quarter, combined multiple columns together, etc...) from raw data etc...
	 * @param null $format
	 * @return bool
	 */
	function preProcess( $format = null ) {
		$this->profiler->startTimer( 'preProcess' );
		$this->_preProcess( $format );
		$this->profiler->stopTimer( 'preProcess' );
		Debug::Text( ' Memory Usage: Current: ' . memory_get_usage() . ' Peak: ' . memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/**
	 * Group Data - Automatically include all static columns that are also selected to be viewed, so the user doesn't have to re-select all columns twice.
	 *                Its actually the opposite, select on NON-static columns, and ignore all static columns except the grouped columns.
	 * @return bool
	 */
	function group() {
		$this->profiler->startTimer( 'group' );

		$format_group_config = $this->formatGroupConfig();
		if ( is_array( $format_group_config ) && count( $format_group_config ) > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), ( is_array( $this->data ) ) ? count( $this->data ) : 0, null, TTi18n::getText( 'Grouping Data...' ) );

			$this->data = Group::GroupBy( $this->data, $format_group_config );

			$this->getProgressBarObject()->set( $this->getAPIMessageID(), ( is_array( $this->data ) ) ? count( $this->data ) : 0 );
			//Debug::Arr($format_group_config, 'Group Config: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($this->data, 'Group Data: ', __FILE__, __LINE__, __METHOD__, 10);
		}

		$this->profiler->stopTimer( 'group' );
		Debug::Text( ' Memory Usage: Current: ' . memory_get_usage() . ' Peak: ' . memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/**
	 * Sort data
	 * @return bool
	 */
	function sort() {
		if ( is_array( $this->data ) == false ) {
			return true;
		}

		$this->profiler->startTimer( 'sort' );
		if ( is_array( $this->getSortConfig() ) && count( $this->getSortConfig() ) > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), ( is_array( $this->data ) ) ? count( $this->data ) : 0, null, TTi18n::getText( 'Sorting Data...' ) );

			Debug::Arr( $this->getSortConfig(), 'Sort Config: ', __FILE__, __LINE__, __METHOD__, 10 );

			$this->data = Sort::arrayMultiSort( $this->data, $this->getSortConfig() );

			$this->getProgressBarObject()->set( $this->getAPIMessageID(), ( is_array( $this->data ) ) ? count( $this->data ) : 0 );
		}

		$this->profiler->stopTimer( 'sort' );

		//Debug::Arr($this->data, 'Sort Data: ', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Text( ' Memory Usage: Current: ' . memory_get_usage() . ' Peak: ' . memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/**
	 * @return bool
	 */
	function sortFormData() {
		$this->profiler->startTimer( 'sort' );
		if ( is_array( $this->getSortConfig() ) && count( $this->getSortConfig() ) > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $this->form_data ), null, TTi18n::getText( 'Sorting Form Data...' ) );

			Debug::Arr( $this->getSortConfig(), 'Sort Config: ', __FILE__, __LINE__, __METHOD__, 10 );
			if ( isset( $this->form_data['user'] ) ) {
				foreach ( $this->form_data['user'] as $legal_entity_id => $legal_entity_users ) {
					$this->form_data['user'][$legal_entity_id] = Sort::arrayMultiSort( $legal_entity_users, $this->getSortConfig() );
				}
			} else {
				Debug::Text( ' WARNING: Unable to sort form data due to user array element does not exist.', __FILE__, __LINE__, __METHOD__, 10 );
			}

			$this->getProgressBarObject()->set( $this->getAPIMessageID(), count( $this->form_data ) );
		}
		$this->profiler->stopTimer( 'sort' );

		//Debug::Arr($this->form_data['user'], 'Sort Data: ', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Text( ' Memory Usage: Current: ' . memory_get_usage() . ' Peak: ' . memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/**
	 * Calculate overall total in memory before we do any sub-totaling, then append *after* subtotaling is complete.
	 * @return bool
	 */
	function Total() {
		$this->profiler->startTimer( 'Total' );

		$other_config = $this->getOtherConfig();
		if ( !isset( $other_config['disable_grand_total'] ) || $other_config['disable_grand_total'] == false ) {

			$metadata = $this->getOptions( 'grand_total_metadata' );
			if ( is_array( $metadata ) ) {
				$aggregates = $metadata['aggregate'];
			} else {
				$aggregates = $this->getOptions( 'aggregates' );
			}
			//Debug::Arr($aggregates, 'Aggregates: ', __FILE__, __LINE__, __METHOD__, 10);

			//Use Group By, so we utilize the proper aggregates when totalling the entire report.
			//Add '_total' = TRUE as metadata.
			$total = Group::GroupBy( $this->data, $aggregates, 2 ); //2 = Total

			//Determine where we need to place "Grand Total" label.
			$static_column_options = (array)Misc::trimSortPrefix( $this->getOptions( 'static_columns' ) );
			$columns = $this->getReportColumns();
			$selected_static_columns = count( array_intersect( array_keys( $static_column_options ), array_keys( (array)$columns ) ) );
			$sub_total_columns = (array)$this->getSubTotalConfig();
			$sub_total_columns_count = ( $selected_static_columns > 1 ) ? count( $sub_total_columns ) : 0; //If there is only one static column, we can't indent the "Grand Total" label.

			//Only display "Grand Total" label when at least one static column is to be displayed on the report, otherwise just display the totals without the label.
			//This also prevents PHP errors caused by sending "Grand Total" to a currency formater or something.
			if ( $selected_static_columns > 0 ) {
				$grand_total_column = $this->getReportColumns( $sub_total_columns_count );
				if ( isset( $static_column_options[$grand_total_column] ) ) {
					$total[0][$grand_total_column] = [ 'display' => TTi18n::getText( 'Grand Total' ) . '[' . ( ( is_array( $this->data ) ) ? count( $this->data ) : 0 ) . ']:' ]; //Use 'display' array so column formatter isn't run on this.
				} else {
					Debug::Text( 'Skipping Grand Total label due to not being static...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				Debug::Text( 'Skipping Grand Total label...', __FILE__, __LINE__, __METHOD__, 10 );
			}

			$total[0]['_total'] = true;
			$this->total_row = $total[0];
			//Debug::Arr($this->total_row, ' Total Row: ', __FILE__, __LINE__, __METHOD__, 10);
		}

		$this->profiler->stopTimer( 'Total' );
		Debug::Text( ' Memory Usage: Current: ' . memory_get_usage() . ' Peak: ' . memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/**
	 * Calculate subtotals - This must be done *after* sorting, as the data may need to be re-sorted to properly merge sub-totals back into main array.
	 * @return bool
	 */
	function subTotal() {
		$this->profiler->startTimer( 'subTotal' );
		if ( is_array( $this->getSubTotalConfig() ) && count( $this->getSubTotalConfig() ) > 0 ) {

			$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $this->formatSubTotalConfig() ), null, TTi18n::getText( 'Totaling Data...' ) );

			$sub_total_data = [];
			$i = 0;
			foreach ( $this->formatSubTotalConfig() as $k => $iteration_config ) {
				Debug::Text( ' SubTotal iteration: ' . $i, __FILE__, __LINE__, __METHOD__, 10 );

				$tmp_sub_total_data = Group::GroupBy( $this->data, $iteration_config, 1 );
				if ( $i == 0 ) {
					$sub_total_data = $tmp_sub_total_data;
				} else {
					//Merge sub_total data arrays, if two keys match, increment by one, so the consecutive sub-totals rows come after one another.
					foreach ( $tmp_sub_total_data as $key => $data ) {
						if ( isset( $sub_total_data[$key] ) ) {
							//Find non-conflicting key that preserves ordering.
							$new_key = $key;
							$sub_total_data_count = count( $sub_total_data );
							for ( $i = 0; $i <= $sub_total_data_count; $i++ ) {
								$new_key .= '_';
								//Stop the loop if the new key isn't also a duplicate.
								if ( !isset( $sub_total_data[$new_key] ) ) {
									break;
								}
							}
							//Debug::Text(' Conflicting key found: '. $key .', finding next available one: '. $new_key, __FILE__, __LINE__, __METHOD__, 10);

							$sub_total_data[$new_key] = $data;
						} else {
							$sub_total_data[$key] = $data;
						}
					}
				}

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $k );

				$i++;
			}

			$this->data = array_merge( (array)$this->data, $sub_total_data );
			unset( $sub_total_data, $k, $key, $data, $tmp_sub_total_data );

			uksort( $this->data, 'strnatcasecmp' );
			//Debug::Arr($this->data, ' SubTotal Data: ', __FILE__, __LINE__, __METHOD__, 10);
		}

		$this->profiler->stopTimer( 'subTotal' );
		Debug::Text( ' Memory Usage: Current: ' . memory_get_usage() . ' Peak: ' . memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/**
	 * Calculate page break points - This must be done *after* sub-totaling and sorting, as the data may need to be re-sorted to properly merge page break points back into main array.
	 * @return bool
	 */
	function pageBreak() {
		$this->profiler->startTimer( 'pageBreak' );
		if ( is_array( $this->data ) && is_array( $this->getPageBreakConfig() ) && count( $this->getPageBreakConfig() ) > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), ( is_array( $this->data ) ) ? count( $this->data ) : 0, null, TTi18n::getText( 'Inserting Page Breaks...' ) );

			$this->data = array_values( $this->data );
			$page_break_data = Group::PageBreakBy( $this->data, $this->getPageBreakConfig() );
			if ( is_array( $page_break_data ) ) {
				foreach ( $page_break_data as $key => $value ) {
					$this->data[$key]['_page_break'] = true;
				}
			}
			unset( $page_break_data );

			$this->getProgressBarObject()->set( $this->getAPIMessageID(), ( is_array( $this->data ) ) ? count( $this->data ) : 0 );
		}

		$this->profiler->stopTimer( 'pageBreak' );
		Debug::Text( ' Memory Usage: Current: ' . memory_get_usage() . ' Peak: ' . memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/**
	 * @param $format
	 * @return bool
	 */
	function chart( $format ) {
		$this->profiler->startTimer( 'chart' );

		if ( $this->isEnabledChart() == true ) {
			$rc = new ReportChart( $this );

			if ( $format == 'html' ) {
				$page_width = 280;
				$page_height = 362;
			} else {
				if ( $this->config['other']['page_orientation'] == 'P' ) {
					$page_width = 216;
					$page_height = 279;
				} else {
					$page_width = 279;
					$page_height = 216;
				}
			}

			//Always put charts on a regular size paper,
			//that way we don't have to initialize the PDF page to pass into the chart, then do it all over again for the table.
			$properties = [
					'page_orientation' => $this->config['other']['page_orientation'],
					'left'             => $this->config['other']['left_margin'],
					'right'            => $this->config['other']['right_margin'],
					'top'              => $this->config['other']['top_margin'],
					'bottom'           => 0,
					'page_width'       => $page_width,
					'page_height'      => $page_height,
			];
			$rc->setDocumentProperties( $properties );

			$this->chart_images = $rc->Output();
		} else {
			Debug::Text( ' Charting not enabled...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		$this->profiler->stopTimer( 'chart' );
		Debug::Text( ' Memory Usage: Current: ' . memory_get_usage() . ' Peak: ' . memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/**
	 * Last operation before displaying data to the user, format data within locale, add dollar signs, thousand separators etc...
	 * This increases performance when heavy grouping is used, as there is less data to process.
	 * As well it reduces memory usage as we can overwrite columns with nicely displaying columns instead.
	 * Unfortunatley the above performance optimizations dont' work, so we need to postProcess immediately after preProcess as
	 * sometimes there are columns postProcess needs that grouping will drop. For example when two or three columns are required to postProcess into a single column.
	 * If group_by on that single column happens before postProcess, all the necessary data will be lost.
	 * This will have to be one of the restrictions, that postProcess can only use a *SINGLE* column at a time, as its not guaranteed to have more than that due to grouping.
	 * @param null $format
	 * @return bool
	 */
	function postProcess( $format = null ) {
		//Append Total record to the end.
		if ( isset( $this->total_row ) && is_array( $this->total_row ) ) {
			$this->data[] = $this->total_row;
		}

		$this->profiler->startTimer( 'postProcess' );
		$this->_postProcess( $format );
		$this->profiler->stopTimer( 'postProcess' );
		Debug::Text( ' Memory Usage: Current: ' . memory_get_usage() . ' Peak: ' . memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/**
	 * @param $type
	 * @param $column
	 * @param mixed $value
	 * @param null $format
	 * @return array|bool|false|mixed|null|string
	 */
	function columnFormatter( $type, $column, $value, $format = null ) {
		if ( is_array( $value ) && isset( $value['display'] ) ) { //Found sorting array, use display column.
			return $value['display'];
		} else {
			$retval = $value;
			if ( $format == 'csv' || $format == 'json' || $format == 'raw' || $format == 'payment_services' ) { //Force specific field formats for exporting to CSV format.
				switch ( $type ) {
					case 'report_date':
						$column = ( strpos( $column, 'custom_column' ) === false ) ? $column : $column . '-' . 'date_stamp';
						$retval = TTDate::getReportDates( $column, $value, true, $this->getUserObject() );
						break;
					case 'currency':
						$retval = Misc::MoneyRound( $value, 2, $this->getCurrencyObject() ); //Set MIN decimals to 2 and max to the currency rounding.
						break;
					case 'percent':
					case 'numeric':
						//Don't format above types.
						break;
					case 'time_unit':
						$retval = TTDate::getHours( $value ); //Force to hours always.
						break;
					case 'date_stamp':
						$retval = TTDate::getDate( 'DATE', $value );
						break;
					case 'time':
						$retval = TTDate::getDate( 'TIME', $value );
						break;
					case 'time_stamp':
						$retval = TTDate::getDate( 'DATE+TIME', $value );
						break;
					case 'boolean':
						if ( $value == true ) {
							$retval = TTi18n::getText( 'Yes' );
						} else {
							$retval = TTi18n::getText( 'No' );
						}
						break;
					default:
						break;
				}
			} else if ( $format == 'xml' ) {
				//Use standard XML formats whenever possible.
				switch ( $type ) {
					case 'report_date':
						$column = ( strpos( $column, 'custom_column' ) === false ) ? $column : $column . '-' . 'date_stamp';
						$retval = TTDate::getReportDates( $column, $value, true, $this->getUserObject() );
						break;
					case 'currency':
						$retval = Misc::MoneyRound( $value, 2, $this->getCurrencyObject() ); //Set MIN decimals to 2 and max to the currency rounding.
						break;
					case 'percent':
					case 'numeric':
						//Don't format above types.
						break;
					case 'time_unit':
						$retval = TTDate::getHours( $value ); //Force to hours always.
						break;
					case 'date_stamp':
						$retval = date( 'Y-m-d', $value ); //type="xs:date"
						break;
					case 'time':
						$retval = date( 'H:i:s', $value ); //type="xs:time"
						break;
					case 'time_stamp':
						$retval = date( 'c', $value ); //type="xs:dateTime"
						break;
					case 'boolean':
						if ( $value == true ) {
							$retval = TTi18n::getText( 'Yes' );
						} else {
							$retval = TTi18n::getText( 'No' );
						}
						break;
					default:
						break;
				}
			} else {
				switch ( $type ) {
					case 'report_date':
						$column = ( strpos( $column, 'custom_column' ) === false ) ? $column : $column . '-' . 'date_stamp';
						$retval = TTDate::getReportDates( $column, $value, true, $this->getUserObject() );
						break;
					case 'currency':
						if ( is_object( $this->getCurrencyObject() ) ) {
							//Set MIN decimals to 2 and max to the currency rounding.
							$retval = $this->getCurrencyObject()->getSymbol() . TTi18n::formatNumber( $value, true, 2, $this->getCurrencyObject()->getRoundDecimalPlaces() );
						} else {
							$retval = TTi18n::formatCurrency( $value );
						}
						break;
					case 'percent':
						$retval = TTi18n::formatNumber( $value, true ) . '%';
						break;
					case 'numeric':
						$retval = TTi18n::formatNumber( $value, true );
						break;
					case 'time_unit':
						$retval = TTDate::getTimeUnit( $value );
						break;
					case 'date_stamp':
						$retval = TTDate::getDate( 'DATE', $value );
						break;
					case 'time':
						$retval = TTDate::getDate( 'TIME', $value );
						break;
					case 'time_stamp':
						$retval = TTDate::getDate( 'DATE+TIME', $value );
						break;
					case 'boolean':
						if ( $value == true ) {
							$retval = TTi18n::getText( 'Yes' );
						} else {
							$retval = TTi18n::getText( 'No' );
						}
						break;
					case 'time_since':
						$retval = TTDate::getHumanTimeSince( $value );
						break;
					default:
						break;
				}
			}

			//Debug::Text('Column: '. $column .' Value: '. $value .' Type: '. $type .' Retval: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

			return $retval;
		}
	}

	/**
	 * @param array $format_options
	 * @return array
	 */
	function getTimePeriodFormatOptions( $format_options = [] ) {
		$report_date_columns = Misc::trimSortPrefix( $this->getOptions( 'date_columns' ) );
		if ( is_array( $report_date_columns ) ) {
			foreach ( $report_date_columns as $column => $name ) {
				$format_options[$column] = 'report_date';
			}
			unset( $name );//code standards
		} else {
			Debug::Text( 'No Report Date columns...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $format_options;
	}

	/**
	 * @param array $format_options
	 * @return array
	 */
	function getCustomColumnFormatOptions( $format_options = [] ) {
		$custom_columns = $this->getCustomColumnConfig();
		$report_format_options = $this->getOptions( 'column_format_map' );
		if ( is_array( $custom_columns ) ) {
			foreach ( $custom_columns as $custom_column ) {
				$format_options[$custom_column['variable_name']] = $report_format_options[$custom_column['format']];
			}
		} else {
			Debug::Text( 'No Custom Columns...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $format_options;
	}

	/**
	 * @return bool
	 */
	function currencyConvertToBase() {
		$all_currency_format_columns = array_keys( array_merge( (array)Misc::trimSortPrefix( $this->getOptions( 'column_format' ) ), $this->getCustomColumnFormatOptions() ), 'currency' );
		$currency_convert_to_base = $this->getCurrencyConvertToBase();
		$base_currency_obj = $this->getBaseCurrencyObject();

		//Determine if any currency columns are actually being displayed, no point in converting if they don't.
		$currency_format_columns = array_intersect_key( array_flip( $all_currency_format_columns ), (array)$this->getReportColumns() );
		if ( empty( $currency_format_columns ) ) {
			return true;
		}
		if ( is_object( $base_currency_obj ) == false ) {
			return true;
		}
		if ( $currency_convert_to_base == false ) {
			return true;
		}
		if ( is_array( $this->data ) == false ) {
			return true;
		}

		$this->profiler->startTimer( 'currencyConvertToBase' ); //Must go below the above "return" calls, otherwise stopTimer() never gets called.

		// Loop over the all currency columns to match with the report data to convert the currency columns in data to base currency in company if they do exist.
		foreach ( $this->data as $key => $row ) {
			foreach ( $currency_format_columns as $currency_column => $currency_column_value ) {
				//We must have the currency_rate here to do the proper conversions.
				//For reports that don't use currency_rate columns (like timesheet summary/detail) they need to create the currency_rate to always be the same as the employees default currency.
				if ( isset( $row[$currency_column] ) && isset( $row['currency_rate'] ) && $row['currency_rate'] !== 1 && $row['currency_rate'] != 0 ) {
					$this->data[$key][$currency_column] = $base_currency_obj->getBaseCurrencyAmount( $row[$currency_column], $row['currency_rate'], $currency_convert_to_base );
				}
			}
			unset( $currency_column_value ); //code standards
		}

		Debug::Text( ' Memory Usage: Current: ' . memory_get_usage() . ' Peak: ' . memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__, 10 );

		$this->profiler->stopTimer( 'currencyConvertToBase' );

		return true;
	}

	/**
	 * @return array
	 */
	function getColumnFormatConfig() {
		return $this->getTimePeriodFormatOptions( array_merge( (array)Misc::trimSortPrefix( $this->getOptions( 'column_format' ) ), $this->getCustomColumnFormatOptions() ) );
	}

	/**
	 * @param null $format
	 * @return bool
	 */
	function _postProcess( $format = null ) {
		if ( is_array( $this->data ) && count( $this->data ) > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $this->data ), null, TTi18n::getText( 'Post-Processing Data...' ) );

			$columns = $this->getReportColumns();

			//Get column formatting data.
			$column_format_config = $this->getColumnFormatConfig();

			//Debug::Arr($column_format_config, 'Column Format Config: ', __FILE__, __LINE__, __METHOD__, 10);

			foreach ( $this->data as $key => $row ) {
				if ( is_array( $row ) ) {
					foreach ( $row as $column => $value ) {
						if ( !isset( $columns[$column] ) ) { //Dont bother trying to format data that isn't in the list of displayed columns.
							continue;
						}

						if ( is_array( $row[$column] ) && isset( $row[$column]['display'] ) ) { //Found sorting array, use display column.
							$this->data[$key][$column] = $row[$column]['display'];
						} else {
							if ( isset( $column_format_config[$column] ) ) {
								//Optimization to lower memory usage when the column formatter doesn't do anything, prevent overwriting the data in the array.
								//$this->profiler->startTimer( 'columnFormatter' );
								$formatted_value = $this->columnFormatter( $column_format_config[$column], $column, $value, $format );
								if ( $formatted_value !== $value ) { //Use !== for exact match, otherwise '100.00' is matched as int(100)
									$this->data[$key][$column] = $formatted_value;
								}
								//$this->profiler->stopTimer( 'columnFormatter' );
							} //else { //Don't modify any data.
						}
					}
				}
				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );

				if ( $this->config['other']['maximum_row_limit'] > 0 && $key >= $this->config['other']['maximum_row_limit'] ) {
					Debug::Text( '  Reached maximum row limit (' . $this->config['other']['maximum_row_limit'] . '), stop processing...', __FILE__, __LINE__, __METHOD__, 10 );
					array_splice( $this->data, ( $key + 1 ) );
					break;
				}
			}
		} else {
			Debug::Text( 'No data to postProcess...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		//Debug::Arr($this->data, 'postProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return true;
	}

	/**
	 * Returns the full description block of text.
	 * @param bool $html
	 * @param bool $relative_time_period
	 * @return string
	 */
	function getDescriptionBlock( $html = false, $relative_time_period = false ) {
		//Don't include the report name.
		//$body = TTI18n::getText('Report').': '. $this->title."\n\n";
		$body = '';

		//Report Name
		$report_name = $this->getDescription( 'report_name' );
		if ( $report_name != '' ) {
			$body .= TTi18n::getText( 'Name' ) . ': ' . $report_name . "\n";
		}

		//Report Description
		$report_description = $this->getDescription( 'report_description' );
		if ( $report_name != '' ) {
			$body .= TTi18n::getText( 'Description' ) . ': ' . $report_description . "\n";
		}

		//Time Period: start/end date, or pay period.
		$description = $this->getDescription( 'time_period', [ 'relative_time_period' => $relative_time_period ] );
		if ( $description != '' ) {
			$body .= TTi18n::getText( 'Time Period' ) . ': ' . $description . "\n";
		}

		//Filter:
		$description = $this->getDescription( 'filter' );
		if ( $description != '' ) {
			$body .= TTi18n::getText( 'Filter' ) . ': ' . $description . "\n";
		}

		//Group:
		$description = $this->getDescription( 'group' );
		if ( $description != '' ) {
			$body .= TTi18n::getText( 'Group' ) . ': ' . $description . "\n";
		}

		//SubTotal:
		$description = $this->getDescription( 'sub_total' );
		if ( $description != '' ) {
			$body .= TTi18n::getText( 'SubTotal' ) . ': ' . $description . "\n";
		}

		//Sort:
		$description = $this->getDescription( 'sort' );
		if ( $description != '' ) {
			$body .= TTi18n::getText( 'Sort' ) . ': ' . $description . "\n";
		}

		//Custom Filter:
		$description = $this->getDescription( 'custom_filter' );
		if ( $description != '' ) {
			$body .= TTi18n::getText( 'Custom Filter' ) . ': ' . $description . "\n";
		}

		if ( $html == true ) {
			$body = nl2br( $body );
		}

		return $body;
	}

	/**
	 * @param $label
	 * @param null $params
	 * @return bool|string
	 */
	function getDescription( $label, $params = null ) {
		$retval = false;

		$label = strtolower( trim( $label ) );
		switch ( $label ) {
			case 'time_period':
				//Debug::Text('Valid Label: '. $label, __FILE__, __LINE__, __METHOD__, 10);

				$config = $this->getFilterConfig();
				if ( isset( $config['pay_period_id'] ) && is_array( $config['pay_period_id'] ) ) {
					//Pay Period based
					$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
					$pplf->getByCompanyId( $this->getUserObject()->getCompany() );
					$pay_period_options = Misc::trimSortPrefix( $pplf->getArrayByListFactory( $pplf, false, true ) );

					$pay_period_names = [];
					foreach ( $config['pay_period_id'] as $pay_period_id ) {
						$pay_period_names[] = Option::getByKey( $pay_period_id, $pay_period_options );
					}

					if ( isset( $pay_period_names ) ) {
						$retval = TTi18n::getText( 'Pay Periods' ) . ': ' . implode( ', ', $pay_period_names );
					} else {
						$retval = TTi18n::getText( 'Pay Periods' ) . ': ' . TTi18n::getText( 'N/A' );
					}
					unset( $pplf, $pay_period_options, $pay_period_id, $pay_period_names );
				} else if ( isset( $config['time_period'] ) ) {
					if ( isset( $params['relative_time_period'] ) && $params['relative_time_period'] == true
							&& isset( $config['time_period'] ) && isset( $config['time_period']['time_period'] ) ) {
						//Show just the relative time period for displaying in a Saved Report datagrid, where exact dates may not be necessary.
						$retval = Option::getByKey( $config['time_period']['time_period'], Misc::trimSortPrefix( $this->getOptions( 'time_period' ) ) );
					} else {
						if ( isset( $config['time_period']['time_period'] ) ) {
							$retval = Option::getByKey( $config['time_period']['time_period'], Misc::trimSortPrefix( $this->getOptions( 'time_period' ) ) ) . ' [ ';
						}

						//Date based
						if ( isset( $config['start_date'] ) && $config['start_date'] != '' ) {
							$retval .= TTDate::getDate( 'DATE', $config['start_date'] );
						} else {
							$retval .= TTi18n::getText( 'N/A' );
						}

						$retval .= ' ' . TTi18n::getText( 'to' ) . ' ';

						if ( isset( $config['end_date'] ) && $config['end_date'] != '' ) {
							$retval .= TTDate::getDate( 'DATE', $config['end_date'] );
						} else {
							$retval .= TTi18n::getText( 'N/A' );
						}

						$retval .= ' ]';
					}
				}
				break;
			case 'report_name':
				$config = $this->getOtherConfig();
				if ( isset( $config['report_name'] ) && $config['report_name'] != '' ) {
					$retval = $config['report_name'];
				}
				break;
			case 'report_description':
				$config = $this->getOtherConfig();
				if ( isset( $config['report_description'] ) && $config['report_description'] != '' ) {
					$retval = $config['report_description'];
				}
				break;
			case 'filter':
			case 'group':
			case 'sub_total':
			case 'sort':
			case 'custom_filter':
				switch ( $label ) {
					case 'filter':
						$config = (array)$this->getFilterConfig();
						unset( $config['template'] ); //Ignore template when displaying this
						$filter_columns = array_keys( $config );
						$columns = Misc::trimSortPrefix( $this->getOptions( 'setup_fields' ) );
						break;
					case 'group':
						$config = (array)$this->formatGroupConfig();
						$filter_columns = [];
						foreach ( $config as $key => $val ) {
							if ( $val == '' || is_int( $val ) ) {
								$filter_columns[] = $key;
							}
						}
						unset( $key, $val );
						$columns = Misc::trimSortPrefix( $this->getOptions( 'columns' ) );
						break;
					case 'sub_total':
						$config = (array)$this->formatSubTotalConfig();
						$filter_columns = [];

						if ( is_array( $config ) && isset( $config[0] ) && is_array( $config[0] ) ) {
							$config = $config[0];
							foreach ( $config as $key => $val ) {
								if ( $val == '' || is_int( $val ) ) {
									$filter_columns[] = $key;
								}
							}
						}
						unset( $key, $val );
						$columns = Misc::trimSortPrefix( $this->getOptions( 'columns' ) );
						break;
					case 'sort':
						$config = (array)$this->getSortConfig();
						$filter_columns = array_keys( $config );
						$columns = Misc::trimSortPrefix( $this->getOptions( 'columns' ) );
						break;
					case 'custom_filter':
						//$config = (array)$this->getCustomFilterConfig();
						$filter_columns = (array)$this->getCustomFilterConfig();
						$columns = Misc::trimSortPrefix( $this->getOptions( 'report_custom_filters' ) );
						break;
				}
				//Debug::Arr($config, ' Config: ', __FILE__, __LINE__, __METHOD__, 10);

				if ( is_array( $filter_columns ) && count( $filter_columns ) > 0 ) {
					foreach ( $filter_columns as $column ) {
						if ( isset( $columns[$column] ) ) {
							$retval[] = trim( Option::getByKey( $column, $columns ) );
						}
					}

					if ( is_array( $retval ) ) {
						$retval = implode( ', ', $retval );
					}
				}

				break;
			default:
				Debug::Text( 'Invalid label!', __FILE__, __LINE__, __METHOD__, 10 );
				break;
		}

		//Debug::Text('Getting description for label: '. $label .' Description: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	/**
	 * @return bool
	 */
	function checkPermissions() {
		if ( is_object( $this->getPermissionObject() ) == true ) {

			$retval = $this->_checkPermissions( $this->getUserObject()->getId(), $this->getUserObject()->getCompany() );
			Debug::Text( 'Permission Check Retval: ' . (int)$retval, __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}

		Debug::Text( 'Permission Object not set!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param null $milliseconds
	 * @return bool
	 */
	function setQueryStatementTimeout( $milliseconds = null ) {
		global $db;

		if ( $milliseconds == '' ) {
			global $config_vars;
			$milliseconds = $this->config['other']['query_statement_timeout'];
			if ( isset( $config_vars['other']['report_query_statement_timeout'] ) && $config_vars['other']['report_query_statement_timeout'] != '' ) {
				$milliseconds = $config_vars['other']['report_query_statement_timeout'];
			}
		}

		Debug::Text( 'Setting Report DB query statement timeout to: ' . $milliseconds, __FILE__, __LINE__, __METHOD__, 10 );
		if ( strncmp( $db->databaseType, 'postgres', 8 ) == 0 ) {
			$db->Execute( 'SET statement_timeout = ' . (int)$milliseconds );
		}

		return true;
	}

	/**
	 * @param null $format
	 * @return array|bool
	 */
	function getOutput( $format = null ) {
		//Get format from getMiscOptions().
		//Formats: RAW (PHP ARRAY), CSV, HTML, PDF

		if ( $this->checkPermissions() == false ) {
			Debug::Text( 'Invalid permissions!', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		//Set these only when calling getOutput() rather than in the constructor so schema upgrades that need to read report settings but not actually
		//execute the report are not affected.
		$this->setExecutionTimeLimit();
		$this->setExecutionMemoryLimit();

		$this->start_time = microtime( true );

		$this->getProgressBarObject()->start( $this->getAPIMessageID(), 2, null, TTi18n::getText( 'Querying Database...' ) ); //Iterations need to be 2, otherwise progress bar is not created.
		$this->getProgressBarObject()->set( $this->getAPIMessageID(), 2 );

		$this->_preOutput( $format );

		$this->setQueryStatementTimeout(); //Use default.
		$this->getData( $format );
		$this->setQueryStatementTimeout( 0 );

		//Check after data is received to make sure we are still below our load threshold.
		if ( $this->isSystemLoadValid() == true ) {
			$this->preProcess( $format );
		} else {
			return false;
		}

		if ( $this->isSystemLoadValid() == true ) {
			$this->currencyConvertToBase();
			$this->calculateCustomColumns( 10 ); //Selections (these are pre-group)
		} else {
			return false;
		}

		if ( $this->isSystemLoadValid() == true ) {
			$this->calculateCustomColumns( 20 ); //Pre-Group
		} else {
			return false;
		}

		if ( $this->isSystemLoadValid() == true ) {
			$this->calculateCustomColumnFilters( 30 ); //Pre-Group
		} else {
			return false;
		}

		if ( $this->isSystemLoadValid() == true ) {
			$this->group();
		} else {
			return false;
		}
		if ( $this->isSystemLoadValid() == true ) {
			$this->calculateCustomColumns( 21 ); //Post-Group: things like round() functions normally need to be done post-group, otherwise they are rounding already rounded values.
		} else {
			return false;
		}

		if ( $this->isSystemLoadValid() == true ) {
			$this->calculateCustomColumnFilters( 31 ); //Post-Group //Put after grouping is handled, otherwise the user might get unexpected results based on the data they actually see.
		} else {
			return false;
		}

		if ( $this->isSystemLoadValid() == true ) {
			$this->sort(); //Sort needs to come before subTotal, as subTotal will need to re-sort the data in order to fit the sub-totals in.
		} else {
			return false;
		}


		//if ( $format != 'csv' AND $format != 'xml' ) { //Exclude total/sub-totals for CSV/XML format
		if ( $format == 'pdf' || $format == 'html' || $format == 'raw' || $format == 'payment_services' || stripos( $format, 'pdf_' ) !== false ) {  //Only total/subtotal for PDF/RAW formats.
			if ( $this->isSystemLoadValid() == true ) {
				$this->Total();
				$this->subTotal();
			} else {
				return false;
			}
		}

		if ( $format == 'pdf' || $format == 'html' ) {  //Only page break for PDF/HTML formats.
			if ( $this->isSystemLoadValid() == true ) {
				$this->PageBreak();
			} else {
				return false;
			}
		}

		//Rekey data array starting at 0 sequentially.
		//This prevents the progress bar from jumping all over or moving in reverse.
		if ( is_array( $this->data ) ) { //Don't do this if no data exists, as it will preven the "NO DATA MATCHES" message from appearing.
			$this->data = array_values( $this->data );
		}

		if ( $this->isEnabledChart() == true ) {
			if ( $this->isSystemLoadValid() == true ) {
				//We need to generate the charts before postProcess runs.
				//But we need to size the PDF *after* postProcess runs.
				$this->chart( $format );
			} else {
				return false;
			}
		}


		if ( $this->isSystemLoadValid() == true ) {
			$this->postProcess( $format );
		} else {
			return false;
		}

		//PDFs have multiple format names, so just check if HTML or PDF is in the name at all.
		if ( strpos( $format, 'html' ) !== false ) {
			$this->_html_Initialize(); // initialize the html tag, including the head tag, set author, title, description informations.
		} else if ( strpos( $format, 'pdf' ) !== false ) {
			$this->_pdf_Initialize(); //Size page after postProcess() is done. This will resize the page if its already been initialized for charting purposes.
		}

		//Check after data is postProcessed to make sure we are still below our load threshold.
		if ( $this->isSystemLoadValid() == false ) {
			return false;
		}

		$retval = $this->_output( $format );

		$this->_postOutput( $format );

		$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

		Debug::Text( ' Format: ' . $format . ' Total Time: ' . ( microtime( true ) - $this->start_time ) . ' Memory Usage: Current: ' . memory_get_usage() . ' Peak: ' . memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( Debug::profileTimers( $this->profiler ), ' Profile Timers: ', __FILE__, __LINE__, __METHOD__, 10 );
		//Debug::Arr( $retval, ' Report Data...', __FILE__, __LINE__, __METHOD__, 10);
		if ( $format != 'raw' && $format != 'payment_services' && $format != 'pdf_form_publish_employee' && $format != 'efile_xml' && $format != 'html' && ( !is_array( $retval ) || !isset( $retval['file_name'] ) || !isset( $retval['mime_type'] ) ) ) {
			if ( is_array( $retval ) && isset( $retval['api_retval'] ) ) {
				//Passthru validation errors.
				return $retval;
			} else {
				return [
						'data'      => $retval,
						'file_name' => $this->getFileName(),
						'mime_type' => $this->getFileMimeType(),
				];
			}
		} else {
			return $retval; //Array with file_name and mime_types
		}
	}

	/**
	 * @param null $format
	 * @return bool
	 */
	function _preOutput( $format = null ) {
		return true;
	}

	/**
	 * @param null $format
	 * @return array|bool
	 */
	function _output( $format = null ) {
		Debug::Text( 'Format: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );

		//Make sure we use the full readable column name when exporting to CSV.
		$column_options = (array)Misc::trimSortPrefix( $this->getOptions( 'columns' ) );
		$column_config = (array)$this->getReportColumns();
		$columns = [];
		foreach ( $column_config as $column => $tmp ) {
			if ( isset( $column_options[$column] ) ) {
				$columns[$column] = $column_options[$column];
			} else if ( $this->getEnableSystemColumns() == true ) {
				$columns[$column] = $column;
			}
		}
		unset( $tmp );//code standards
		//Debug::Arr($columns, 'Columns:  '. $format, __FILE__, __LINE__, __METHOD__, 10);

		if ( $format == 'raw' || $format == 'payment_services' ) {
			//Since only columns that are displayed are formatted, this can cause confusion if we return some formatted and some unformatted columns.
			//Therefore make sure we still format the data somewhat, by ordering and removing columns that aren't actually displayed (with the exception of _total/_subtotal columns)
			//return $this->data;

			if ( is_array( $this->data ) ) {
				$columns['_subtotal'] = $columns['_total'] = ''; //Always include these columns so they can be easily determined programmatically.
				$data = [];
				$i = 0;
				foreach ( $this->data as $key => $row ) {
					//Always include columns that start with '_'. As they are special columns that are helpful in many cases. For example the Whos In/Out dashlet needs _status_id to base the bgcolor logic on. This is important for translations, as we can't base any logic on english names (ie: status_id='In')
					if ( $i == 0 ) {
						foreach ( $row as $row_column_name => $row_column_value ) {
							if ( $row_column_name[0] == '_' ) {
								$columns[$row_column_name] = null;
							}
						}
					}

					if ( is_array( $row ) ) {
						foreach ( $columns as $column_key => $tmp ) {
							if ( isset( $row[$column_key] ) ) {
								$data[$key][$column_key] = $row[$column_key];
							}
//							else {
//								//$data[$key][$column_key] = NULL; //Don't bother with NULLs as it just eats up memory.
//							}
						}
					}
					$i++;
				}

				return $data;
			} else {
				return $this->data;
			}
		} else if ( $format == 'csv' || $format == 'xml' || $format == 'json' ) {
			if ( $format == 'csv' ) {
				$data = Misc::Array2CSV( $this->data, $columns, false, true );
				$file_extension = 'csv';
			} else if ( $format == 'xml' ) {
				//Include report name with non-alphanumerics stripped out.
				$data = Misc::Array2XML( $this->data, $columns, $this->getColumnFormatConfig(), false, false, preg_replace( '/[^A-Za-z0-9]/', '', $this->config['other']['report_name'] ), 'row' );
				$file_extension = 'xml';
			} else if ( $format == 'json' ) {
				//Include report name with non-alphanumerics stripped out.
				$data = Misc::Array2JSON( $this->data, $columns );
				$file_extension = 'json';
			}

			return [
					'data'      => $data,
					'file_name' => $this->file_name . '_' . date( 'Y_m_d' ) . '.' . $file_extension,
					'mime_type' => 'text/' . $file_extension,
			];
		} else if ( $format == 'html' ) {
			Debug::Text( 'Exporting HTML format: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );
			$data = $this->_html();

			return [
					'data'      => $data,
					'file_name' => $this->file_name . '_' . date( 'Y_m_d' ) . '.html',
					'mime_type' => 'text/html',
			];
		} else if ( strpos( $format, 'pdf' ) !== false ) {
			Debug::Text( 'Exporting PDF format: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );

			return $this->_pdf();
		} else {
			Debug::Text( 'ERROR: Unknown report format: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * @param null $format
	 * @return bool
	 */
	function _postOutput( $format = null ) {
		return true;
	}

	/**
	 * @return bool
	 */
	function hasData() {
		$total_rows = count( (array)$this->data );
		$total_form_rows = count( (array)$this->form_data );

		if ( ( is_array( $this->data ) && $total_rows > 0 ) || ( is_array( $this->form_data ) && $total_form_rows > 0 ) ) {
			//Check if the only row is the grand total.
			//Debug::Arr($this->data, ' Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($this->form_data, ' Raw Form Data: ', __FILE__, __LINE__, __METHOD__, 10);
			if ( $total_rows == 1 && isset( $this->data[0]['_total'] ) ) {
				$retval = false;
			} else {
				$retval = true;
			}
		} else {
			$retval = false;
		}

		Debug::text( 'Total Rows: ' . $total_rows . ' Form Rows: ' . $total_form_rows . ' Result: ' . (int)$retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	/**
	 * @param $output
	 * @param object $report_schedule_obj
	 * @return bool
	 */
	function email( $output, $report_schedule_obj = null ) {
		Debug::Text( 'Emailing report...', __FILE__, __LINE__, __METHOD__, 10 );

		if ( is_array( $output ) && isset( $output['data'] ) && $output['data'] != ''
				&& is_object( $this->getUserObject() )
				&& (
						( $this->getUserObject()->getWorkEmail() != '' && $this->getUserObject()->getWorkEmailIsValid() == true )
						||
						( $this->getUserObject()->getHomeEmail() != '' && $this->getUserObject()->getHomeEmailIsValid() == true )
				)
		) {

			if ( $this->getUserObject()->getWorkEmail() != '' && $this->getUserObject()->getWorkEmailIsValid() == true ) {
				$primary_email = Misc::formatEmailAddress( $this->getUserObject()->getWorkEmail(), $this->getUserObject() );

				$secondary_email = null;
				if ( is_object( $report_schedule_obj ) && $report_schedule_obj->getEnableHomeEmail() == true && $this->getUserObject()->getHomeEmail() != '' && $this->getUserObject()->getHomeEmailIsValid() == true ) {
					$secondary_email .= Misc::formatEmailAddress( $this->getUserObject()->getHomeEmail(), $this->getUserObject() );
				}

				if ( is_object( $report_schedule_obj ) && $report_schedule_obj->getOtherEmail() != '' ) {
					if ( $secondary_email != '' ) {
						$secondary_email .= ', ';
					}
					$secondary_email .= $report_schedule_obj->getOtherEmail();
				}
			} else {
				$primary_email = Misc::formatEmailAddress( $this->getUserObject()->getHomeEmail(), $this->getUserObject() );
				$secondary_email = null;
			}

			Debug::Text( 'Emailing report to: ' . $primary_email . ' CC: ' . $secondary_email, __FILE__, __LINE__, __METHOD__, 10 );

			$subject = APPLICATION_NAME . ' ';
			$other_config = $this->getOtherConfig();
			if ( isset( $other_config['report_name'] ) && $other_config['report_name'] != '' ) {
				$subject .= $other_config['report_name'] . ' (';
			}
			$subject .= $this->title;
			if ( isset( $other_config['report_name'] ) && $other_config['report_name'] != '' ) {
				$subject .= ')';
			}

			$body = '<html><body>';
			$body .= TTI18n::getText( 'Report' ) . ': ' . $this->title . '<br><br>';
			$body .= $this->getDescriptionBlock( true );
			$body .= '</body></html>';
			//Debug::Text('Email Subject: '. $subject, __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Text('Email Body: '. $body, __FILE__, __LINE__, __METHOD__, 10);

			//Use $report_schedule_obj->getUserReportData() for audit logging success/failures, so they all appear in the audit tab of the *saved* report rather than the scheduled report or employee record
			TTLog::addEntry( $report_schedule_obj->getUserReportData(), 500, TTi18n::getText( 'Emailed Report' ) . ': ' . $this->title . ' ' . TTi18n::getText( 'To' ) . ': ' . $primary_email . ' ' . TTi18n::getText( 'CC' ) . ': ' . $secondary_email, null, 'user_report_data' );

			$headers = [
					'From'    => '"' . APPLICATION_NAME . '-' . TTi18n::gettext( 'Reports' ) . '" <' . Misc::getEmailLocalPart() . '@' . Misc::getEmailDomain() . '>',
					'Subject' => $subject,
					'Cc'      => $secondary_email,
			];

			$mail = new TTMail();
			$mail->setTo( $primary_email );
			$mail->setHeaders( $headers );

			@$mail->getMIMEObject()->setHTMLBody( $body );
			//$mail->getMIMEObject()->addAttachment($output, 'application/pdf', $this->file_name.'.pdf', FALSE, 'base64');
			$mail->getMIMEObject()->addAttachment( $output['data'], $output['mime_type'], $output['file_name'], false, 'base64' );

			$mail->setBody( $mail->getMIMEObject()->get( $mail->default_mime_config ) );

			return $mail->Send();
		} else {
			if ( is_object( $this->getUserObject() ) ) {
				if ( !( is_array( $output ) && isset( $output['data'] ) && $output['data'] != '' ) ) {
					TTLog::addEntry( $report_schedule_obj->getUserReportData(), 500, TTi18n::getText( 'Not emailing report' ) . ': ' . $this->title . ' - ' . TTi18n::getText( 'Report is blank' ), null, 'user_report_data' );
				} else {
					TTLog::addEntry( $report_schedule_obj->getUserReportData(), 500, TTi18n::getText( 'Not emailing report' ) . ': ' . $this->title . ' - ' . TTi18n::getText( 'Work or Home email address is blank or invalid' ), null, 'user_report_data' );
				}
			}
		}

		Debug::Text( 'No report data to email, or no email address to send them to!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param $column_options
	 * @param $columns
	 * @return array
	 */
	function _pdf_detectPageSize( $column_options, $columns ) {
		$min_dimensions = [ 216, 279 ]; //Letter size, in mm. Exact size is: 215.9x279.4

		//Compare size of table header with larger bold font compared to table data with smaller font.
		$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( $this->config['other']['table_header_font_size'] ) );
		$table_column_name_widths = $this->_pdf_getTableColumnWidths( array_intersect_key( $column_options, (array)$columns ), $this->config['other']['layout']['header'], true, $this->config['other']['table_header_word_wrap'] ); //Table header column names

		//Only fill page with column headers, not table data, otherwise the minimum page size will almost always be larger than the default setting.
		$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( $this->config['other']['table_row_font_size'] ) );
		$table_data_column_widths = $this->_pdf_getTableColumnWidths( $this->getLargestColumnData( array_intersect_key( $column_options, (array)$columns ), false ), $this->config['other']['layout']['header'], false, $this->config['other']['table_data_word_wrap'] ); //Table largest column data

		$width = 0;
		foreach ( $table_column_name_widths as $column => $column_width ) {
			if ( $column_width > $table_data_column_widths[$column] ) {
				$tmp_width = $column_width;
			} else {
				$tmp_width = $table_data_column_widths[$column];
			}

			$this->data_column_widths[$column] = $tmp_width;
			$width += $tmp_width;
		}

		$margins = $this->pdf->getMargins();
		$width += ( $margins['left'] + $margins['right'] );

		if ( $width < $min_dimensions[0] ) {
			$width = $min_dimensions[0];
		}

		Debug::Text( ' Detected Page Width including Margins: ' . $width, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->_pdf_getPageSizeDimensionsFromWidth( $width );
	}

	/**
	 * Return the string from each column that is the largest, so we can base the column widths on these.
	 * @param $columns
	 * @param bool $include_headers
	 * @return array
	 */
	function getLargestColumnData( $columns, $include_headers = true ) {
		//Cache the widths so all data doesn't need to be searched each time.
		$this->profiler->startTimer( 'getLargestColumnData' );

		$retarr = [];
		$widths = [];
		foreach ( $columns as $key => $text ) {
			//Make sure we include the length of the column header in this as well.
			//Except now that we use wordwrapping in column headers we don't want to use the column header text.
			if ( $include_headers === true ) {
				$retarr[$key] = $text;
				$widths[$key] = strlen( $text );
			} else {
				$retarr[$key] = null;
				$widths[$key] = 0;
			}
			if ( is_array( $this->data ) ) {
				foreach ( $this->data as $data_arr ) {
					if ( isset( $data_arr[$key] ) ) {
						if ( is_array( $data_arr[$key] ) && isset( $data_arr[$key]['display'] ) ) {
							$tmp_len = strlen( $data_arr[$key]['display'] );
							$data_arr[$key] = $data_arr[$key]['display'];
						} else if ( is_object( $data_arr[$key] ) ) {
							$tmp_len = $data_arr[$key]->getColumnWidth();
						} else {
							$tmp_len = strlen( $data_arr[$key] );
						}
						if ( $tmp_len > $widths[$key] ) {
							$retarr[$key] = $data_arr[$key];
							$widths[$key] = $tmp_len;
						}
					}
				}
			}
		}

		$this->profiler->stopTimer( 'getLargestColumnData' );

		return $retarr;
	}

	/**
	 * @param $text
	 * @param $layout
	 * @param bool $wrap_width
	 * @return float|int
	 */
	function _pdf_getColumnWidth( $text, $layout, $wrap_width = false ) {
		$this->profiler->startTimer( 'Column Width' );
		$max_width = 0;
		$cell_padding = 0;
		if ( isset( $layout['max_width'] ) ) {
			$max_width = $layout['max_width'];
		}
		if ( isset( $layout['cell_padding'] ) ) {
			$cell_padding = $layout['cell_padding'];
		}
		$cell_padding += 2; //The column width is not always exact, so we need a little extra padding in most cases.

		if ( is_object( $text ) ) {
			$string_width = $text->getColumnWidth();
		} else {
			if ( $wrap_width != '' ) {
				$text = $this->_pdf_getLargestWrappedWord( $text, $wrap_width, $layout );
			}

			//Force sizing with bold fonts, so Grand Total/SubTotal labels are always sized properly.
			$string_width = ceil( $this->pdf->getStringWidth( $text, '', 'B' ) + $cell_padding );
		}

		if ( $max_width > 0 && $string_width > $max_width ) {
			$string_width = $max_width;
		}
		$string_width += 2; //Grand total label needs some extra space.
		//Debug::Text(' Sizing Text: '. $text .' Width: '. $string_width .' Max: '. $max_width .' Padding: '. $cell_padding, __FILE__, __LINE__, __METHOD__, 10);

		$this->profiler->stopTimer( 'Column Width' );

		return $string_width;
	}

	/**
	 * @param $text
	 * @param $layout
	 * @param bool $wrap_width
	 * @return float|int
	 */
	function _pdf_getColumnHeight( $text, $layout, $wrap_width = false ) {
		$this->profiler->startTimer( 'Column Height' );
		$max_height = 0;
		$cell_padding = 0;
		if ( isset( $layout['max_height'] ) ) {
			$max_height = $layout['max_height'];
		}
		if ( isset( $layout['cell_padding'] ) ) {
			$cell_padding = $layout['cell_padding'];
		}

		if ( $wrap_width != '' ) {
			$text = $this->_pdf_getLargestWrappedWord( $text, $wrap_width, $layout );
		}

		$string_height = ceil( $this->pdf->getStringHeight( $text ) + $cell_padding );

		if ( $max_height > 0 && $string_height > $max_height ) {
			$string_height = $max_height;
		}
		//Debug::Text(' Sizing Text: '. $text .' Height: '. $string_height .' Max: '. $max_height .' Padding: '. $cell_padding, __FILE__, __LINE__, __METHOD__, 10);

		$this->profiler->stopTimer( 'Column Height' );

		return $string_height;
	}

	/**
	 * @param $string
	 * @param string $width UUID
	 * @param $layout
	 * @return null
	 */
	function _pdf_getLargestWrappedWord( $string, $width, $layout ) {
		if ( strlen( $string ) > $width ) {
			$split_string = explode( "\n", wordwrap( $string, $width ) );
			$max_size = 0;
			$word = null;
			foreach ( $split_string as $tmp_string ) {
				$tmp_size = $this->_pdf_getColumnWidth( $tmp_string, $layout, false );
				if ( $tmp_size > $max_size ) {
					//Debug::Text(' Largest Wrapped Word: '. $tmp_size .' Word: '. $tmp_string, __FILE__, __LINE__, __METHOD__, 10);
					$max_size = $tmp_size;
					$word = $tmp_string;
				} //else { //Debug::Text(' Other Wrapped Word: '. $tmp_size .' Word: '. $tmp_string, __FILE__, __LINE__, __METHOD__, 10);
			}
		} else {
			$word = $string;
		}

		return $word;
	}

	/**
	 * @param $columns
	 * @param $layout
	 * @param bool $fill_page
	 * @param bool $wrap_width
	 * @return array|bool
	 */
	function _pdf_getTableColumnWidths( $columns, $layout, $fill_page = true, $wrap_width = false ) {
		if ( !is_array( $columns ) ) {
			return false;
		}

		$widths = [];
		foreach ( $columns as $key => $text ) {
			$widths[$key] = $this->_pdf_getColumnWidth( $text, $layout, $wrap_width );
		}
		//Debug::Arr($widths, ' aColumn widths: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( $fill_page == true && count( $widths ) > 0 ) {
			$margins = $this->pdf->getMargins();

			//Margins are already accounted for in newer TCPDF versions. However the recent TCPDF upgrade seems to have removed that.
			$page_width = ( $this->pdf->getPageWidth() - $margins['right'] - $margins['left'] );

			$total_width = array_sum( $widths );
			if ( $total_width < $page_width ) {
				$empty_space = ( $page_width - $total_width );
				$empty_space_per_column = ( $empty_space / count( $widths ) );

				//Try to make all column widths even numbers, than take any fractions and add them to the first column.
				$remainder = ( ( $empty_space_per_column - floor( $empty_space_per_column ) ) * count( $widths ) );
				//Debug::Text(' Column widths are smaller than page size, resizing each column by: '. $empty_space_per_column .' Total Width: '. $total_width .' Page Width: '. $page_width .' Remainder: '. $remainder, __FILE__, __LINE__, __METHOD__, 10);

				$i = 0;
				foreach ( $widths as $key => $width ) {
					if ( $i == 0 ) {
						$widths[$key] += $remainder;
					}
					$widths[$key] += floor( $empty_space_per_column );
					$i++;
				}
				unset( $width ); //code standards
			}
		}

		//Debug::Arr($widths, ' Total Width: '. array_sum($widths) .' bColumn widths: ', __FILE__, __LINE__, __METHOD__, 10);

		return $widths;
	}

	/**
	 * @param $size
	 * @return mixed
	 */
	function _pdf_unitsToPixels( $size ) {
		return ( $size * ( $this->PDF_IMAGE_SCALE_RATIO * 2 ) );
	}

	/**
	 * @param $size
	 * @return float
	 */
	function _pdf_scaleSize( $size ) {
		//The config font_size variable should be a scale, not a direct font size.
		$multiplier = $this->config['other']['font_size'];
		if ( $multiplier <= 0 ) {
			$multiplier = 100;
		}
		$retval = round( ( $size * ( $multiplier / 100 ) ), 3 );

		//Debug::Text(' Requested Font Size: '. $size .' Relative Size: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	/**
	 * @param $size
	 * @return float
	 */
	function _pdf_fontSize( $size ) {
		//The config font_size variable should be a scale, not a direct font size.
		$multiplier = $this->config['other']['font_size'];
		if ( $multiplier <= 0 ) {
			$multiplier = 100;
		}
		$retval = ceil( $size * ( $multiplier / 100 ) );

		//Debug::Text(' Requested Font Size: '. $size .' Relative Size: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	/**
	 * @param $size
	 * @return float
	 */
	function _html_fontSize( $size ) {
		//The config font_size variable should be a scale, not a direct font size.
		$multiplier = $this->config['other']['font_size'];
		if ( $multiplier <= 0 ) {
			$multiplier = 100;
		}
		$retval = ceil( $size * ( $multiplier / 100 ) );

		//Debug::Text(' Requested Font Size: '. $size .' Relative Size: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}


	/**
	 * @param string $min_width UUID
	 * @return array
	 */
	function _pdf_getPageSizeDimensionsFromWidth( $min_width ) {
		//Handle portrait/landscape modes properly
		if ( $this->config['other']['page_orientation'] == 'L' ) {
			//Landscape
			$width = $min_width;
			$height = ( $min_width * 0.774193548 );
		} else {
			//Portrait
			$width = $min_width;
			$height = ( $min_width * 1.291666667 );
		}
		Debug::Text( ' Orientation: ' . $this->config['other']['page_orientation'] . ' Width: ' . $width . ' Height: ' . $height, __FILE__, __LINE__, __METHOD__, 10 );

		//return array( $width, $width*1.2739726027397260274 );
		return [ $width, $height ];
	}

	/**
	 * @param int $width
	 * @return bool
	 */
	function _pdf_drawLine( $width = 3 ) {
		$margins = $this->pdf->getMargins();

		$prev_width = $this->pdf->getLineWidth();
		$this->pdf->setLineWidth( $width );
		$this->pdf->setDrawColor( 0 ); //Black
		$this->pdf->setFillColor( 0 ); //Black

		$this->pdf->Line( $this->pdf->getX(), $this->pdf->getY(), ( $this->pdf->getPageWidth() - $margins['right'] ), $this->pdf->getY() );

		$this->pdf->setLineWidth( $prev_width );

		$this->pdf->Ln( 0.75 );

		return true;
	}

	/**
	 * @param $height
	 * @param bool $add_page
	 * @return bool
	 */
	function _pdf_checkPageBreak( $height, $add_page = true ) {
		$margins = $this->pdf->getMargins();

		if ( ( $this->pdf->getY() + $height ) > ( $this->pdf->getPageHeight() - $margins['bottom'] - $margins['top'] - 10 ) ) {
			//Debug::Text('Detected Page Break needed...', __FILE__, __LINE__, __METHOD__, 10);
			$this->_pdf_AddPage();

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function _pdf_displayMaximumPageLimitError() {
		$this->pdf->AddPage();
		$this->pdf->Ln( $this->pdf->getPageHeight() / 2 );
		$this->pdf->setTextColor( 255, 0, 0 );
		$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 18 ) );
		$this->pdf->Cell( $this->pdf->getPageWidth(), $this->_pdf_fontSize( 10 ), TTi18n::getText( 'Exceeded the maximum number of allowed pages.' ), 0, 0, 'C', 0, '', 1 );
		$this->pdf->Ln();
		$this->pdf->setTextColor( 0, 0, 0 );
		$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 8 ) );
		$this->pdf->Cell( $this->pdf->getPageWidth(), $this->_pdf_fontSize( 6 ), TTi18n::getText( 'If you wish to see more pages, please go to the report "Setup" tab to increase this setting and run the report again.' ), 0, 0, 'C', 0, '', 1 );
		$this->pdf->Ln( 100 );

		return true;
	}

	/**
	 * @return bool
	 */
	function _pdf_checkMaximumPageLimit() {
		$total_pages = $this->pdf->getNumPages(); //Get total pages in PDF so far.
		if ( $total_pages <= $this->config['other']['maximum_page_limit'] ) {
			return true;
		}

		Debug::Text( ' Exceeded maximum page limit... Total Pages: ' . $total_pages . ' Limit: ' . $this->config['other']['maximum_page_limit'], __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return bool
	 */
	function _pdf_AddPage() {
		$this->_pdf_Footer();

		$this->pdf->AddPage();
		$this->_pdf_Header();

		return true;
	}

	/**
	 * @return bool
	 */
	function _pdf_TopSummary() {
		$margins = $this->pdf->getMargins();

		//Draw report information
		if ( $this->pdf->getPage() == 1 ) {
			//Logo - top right
			$image_width = $this->pdf->pixelsToUnits( $this->_pdf_scaleSize( 167 ) );
			$image_height = $this->pdf->pixelsToUnits( $this->_pdf_scaleSize( 42 ) );
			$this->pdf->Image( $this->getUserObject()->getCompanyObject()->getLogoFileName( null, true, false, 'large' ), ( $this->pdf->getPageWidth() - $margins['right'] - $image_width + $this->_pdf_scaleSize( 3 ) ), $margins['top'], $image_width, $image_height, '', '', '', false, 300, '', false, false, 0, true );
			$this->pdf->Ln( 1 );
			$logo_image_y = ( $margins['top'] + $image_height );
			//$this->pdf->setY( $this->pdf->getY()+5 ); //Place Abscissa below image.

			//Report Name
			$report_name = $this->getDescription( 'report_name' );
			$report_description = $this->getDescription( 'report_description' );
			if ( $report_name != '' ) {
				//When a report name is specified, make that the large bold font, and just add in smaller font the report name itself.
				$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 18 ) );
				$this->pdf->Cell( $this->_pdf_scaleSize( 160 ), $this->_pdf_fontSize( 10 ), $report_name, 0, 0, 'L', 0, '', 1 );
				$this->pdf->Ln();

				$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( 6 ) );
				$this->pdf->Cell( $this->_pdf_scaleSize( 15 ), $this->_pdf_fontSize( 3 ), TTi18n::getText( 'Report' ) . ':', 0, 0, 'L', 0, '', 0 );
				$this->pdf->Cell( $this->_pdf_scaleSize( 150 ), $this->_pdf_fontSize( 3 ), $this->title, 0, 0, 'L', 0, '', 1 );
				$this->pdf->Ln();

				$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( 6 ) );
				$this->pdf->Cell( $this->_pdf_scaleSize( 15 ), $this->_pdf_fontSize( 3 ), TTi18n::getText( 'Description' ) . ':', 0, 0, 'L', 0, '', 0 );

				$report_description_array = explode( "\n", $report_description );
				if ( count( $report_description_array ) != 1 ) {
					$first = true;
					foreach ( $report_description_array as $report_description ) {
						if ( $first == true ) {
							$first = false;
						} else {
							$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( 6 ) );
						}
						$this->pdf->Cell( $this->_pdf_scaleSize( 150 ), $this->_pdf_fontSize( 3 ), $report_description, 0, 0, 'L', 0, '', 1 );
						$this->pdf->Ln();
					}
				} else {
					$this->pdf->Cell( $this->_pdf_scaleSize( 150 ), $this->_pdf_fontSize( 3 ), $report_description, 0, 0, 'L', 0, '', 1 );
				}
				$this->pdf->Ln();
			} else {
				//Report Title top left.
				$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 18 ) );
				$this->pdf->Cell( 160, $this->_pdf_fontSize( 10 ), $this->title, 0, 0, 'L', 0, '', 1 );
				$this->pdf->Ln();
			}

			//Set font to small for report filter description
			$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( 6 ) );

			//Time Period: start/end date, or pay period.
			$description = $this->getDescription( 'time_period' );
			if ( $description != '' ) {
				$this->pdf->Cell( $this->_pdf_scaleSize( 15 ), $this->_pdf_fontSize( 3 ), TTi18n::getText( 'Time Period' ) . ':', 0, 0, 'L', 0, '', 0 );
				$this->pdf->Cell( $this->_pdf_scaleSize( 190 ), $this->_pdf_fontSize( 3 ), $description, 0, 0, 'L', 0, '', 1 );
				$this->pdf->Ln();
			}

			//Filter:
			$description = $this->getDescription( 'filter' );
			if ( $description != '' ) {
				$this->pdf->Cell( $this->_pdf_scaleSize( 15 ), $this->_pdf_fontSize( 3 ), TTi18n::getText( 'Filter' ) . ':', 0, 0, 'L', 0, '', 0 );
				$this->pdf->Cell( $this->_pdf_scaleSize( 190 ), $this->_pdf_fontSize( 3 ), $description, 0, 0, 'L', 0, '', 1 );
				$this->pdf->Ln();
			}

			//Group:
			$description = $this->getDescription( 'group' );
			if ( $description != '' ) {
				$this->pdf->Cell( $this->_pdf_scaleSize( 15 ), $this->_pdf_fontSize( 3 ), TTi18n::getText( 'Group' ) . ':', 0, 0, 'L', 0, '', 0 );
				$this->pdf->Cell( $this->_pdf_scaleSize( 170 ), $this->_pdf_fontSize( 3 ), $description, 0, 0, 'L', 0, '', 1 );
				$this->pdf->Ln();
			}

			//SubTotal:
			$description = $this->getDescription( 'sub_total' );
			if ( $description != '' ) {
				$this->pdf->Cell( $this->_pdf_scaleSize( 15 ), $this->_pdf_fontSize( 3 ), TTi18n::getText( 'SubTotal' ) . ':', 0, 0, 'L', 0, '', 0 );
				$this->pdf->Cell( $this->_pdf_scaleSize( 170 ), $this->_pdf_fontSize( 3 ), $description, 0, 0, 'L', 0, '', 1 );
				$this->pdf->Ln();
			}

			//Sort:
			$description = $this->getDescription( 'sort' );
			if ( $description != '' ) {
				$this->pdf->Cell( $this->_pdf_scaleSize( 15 ), $this->_pdf_fontSize( 3 ), TTi18n::getText( 'Sort' ) . ':', 0, 0, 'L', 0, '', 0 );
				$this->pdf->Cell( $this->_pdf_scaleSize( 170 ), $this->_pdf_fontSize( 3 ), $description, 0, 0, 'L', 0, '', 1 );
				$this->pdf->Ln();
			}

			//Custom Filter:
			$description = $this->getDescription( 'custom_filter' );
			if ( $description != '' ) {
				$this->pdf->Cell( $this->_pdf_scaleSize( 15 ), $this->_pdf_fontSize( 3 ), TTi18n::getText( 'Custom Filter' ) . ':', 0, 0, 'L', 0, '', 0 );
				$this->pdf->Cell( $this->_pdf_scaleSize( 170 ), $this->_pdf_fontSize( 3 ), $description, 0, 0, 'L', 0, '', 1 );
				$this->pdf->Ln();
			}

			//Generated Date/User top right.
			$this->pdf->setY( ( ( $this->pdf->getY() - 6 ) < $logo_image_y ) ? $logo_image_y : ( $this->pdf->getY() - 6 ) );
			$this->pdf->setX( ( $this->pdf->getPageWidth() - $margins['right'] - $this->_pdf_scaleSize( 15 ) ) );
			$this->pdf->Cell( $this->_pdf_scaleSize( 15 ), $this->_pdf_fontSize( 3 ), TTi18n::getText( 'Generated' ) . ': ' . TTDate::getDate( 'DATE+TIME', $this->start_time ), 0, 0, 'R', 0, '', 0 );
			$this->pdf->Ln();
			$this->pdf->setX( ( $this->pdf->getPageWidth() - $margins['right'] - $this->_pdf_scaleSize( 15 ) ) );
			$this->pdf->Cell( $this->_pdf_scaleSize( 15 ), $this->_pdf_fontSize( 3 ), TTi18n::getText( 'Generated For' ) . ': ' . $this->getUserObject()->getFullName(), 0, 0, 'R', 0, '', 0 );
			$this->pdf->Ln( $this->_pdf_fontSize( 4 ) );

			$this->_pdf_drawLine( 1 );

			return true;
		}

		return false;
	}

	/**
	 * @param $cur
	 * @return bool
	 */
	function _html_checkMaximumPageLimit( $cur ) {
		$total_rows = ( $this->config['other']['maximum_page_limit'] * 25 ); // 25 rows per page default,

		if ( $cur <= $total_rows ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function _html_Table() {
		$this->profiler->startTimer( 'HTML Table' );

		$this->getProgressBarObject()->start( $this->getAPIMessageID(), ( is_array( $this->data ) ? count( $this->data ) : 0 ), null, TTi18n::getText( 'Generating HTML...' ) );

		$static_column_options = (array)Misc::trimSortPrefix( $this->getOptions( 'static_columns' ) );
		$column_format_config = $this->getColumnFormatConfig();

		$sort_by_columns = $this->getSortConfig();
		$group_by_columns = $this->getGroupConfig();

		//Make sure we ignore a group_by_columns that is an array( 0 => FALSE )
		if ( is_array( $group_by_columns ) && count( $group_by_columns ) > 0 && $group_by_columns[0] !== false ) {
			$group_by_columns = array_flip( $group_by_columns );
		}
		$columns = $this->getReportColumns();
		$sub_total_columns = (array)$this->getSubTotalConfig();

		$sub_total_columns_count = ( count( array_intersect( array_keys( $static_column_options ), array_keys( (array)$columns ) ) ) > 1 ) ? count( $sub_total_columns ) : 0; //If there is only one static column, we can't indent the "Grand Total" label.
		$sub_total_rows = [];                                                                                                                                                 //Count all rows included in sub_total
		for ( $n = 0; $n <= $sub_total_columns_count; $n++ ) {
			$sub_total_rows[$n] = 0;
		}

		$prev_row = [];
		$r = 0;
		$total_rows = 0; //Count all rows included in grand total
		$columns_count = count( $columns );

		$this->html .= '<tbody>';

		if ( is_array( $columns ) && count( $columns ) > 0 && is_array( $this->data ) && count( $this->data ) > 0 ) {
			foreach ( $this->data as $key => $row ) {

				//If the next row is a subtotal or total row, do a page break early, so we don't show just a subtotal/total by itself.
				$cur = ( $r + 1 );
				if ( $this->_html_checkMaximumPageLimit( $cur ) == false ) {
					//Exceeded maximum pages, stop processing.
					$this->html .= '<tr class="blank-row"><td colspan="' . $columns_count . '"></td></tr>';

					$this->html .= '<tr>';
					$this->html .= '<td class="exceeded-error" colspan="' . $columns_count . '">';
					$this->html .= TTi18n::getText( 'Exceeded the maximum number of allowed pages.' );
					$this->html .= '</td>';
					$this->html .= '</tr>';

					$this->html .= '<tr>';
					$this->html .= '<td class="exceeded-warning" colspan="' . $columns_count . '">';
					$this->html .= TTi18n::getText( 'If you wish to see more pages, please go to the report "Setup" tab to increase this setting and run the report again.' );
					$this->html .= '</td>';
					$this->html .= '</tr>';

					$this->html .= '<tr class="blank-row"><td colspan="' . $columns_count . '"></td></tr>';
					break;
				}

				$c = 0;
				$total_row_sub_total_columns = 0;
				$blank_row = true;
				if ( $r == 0 && ( isset( $row['_total'] ) && $row['_total'] == true ) ) {
					Debug::Text( 'Last row is grand total, no actual data to display...', __FILE__, __LINE__, __METHOD__, 10 );
					$error_msg = TTi18n::getText( 'NO DATA MATCHES CRITERIA' );
					$this->html .= '<tr>';
					$this->html .= '<td class="no-result" colspan="' . $columns_count . '">' . '[' . $error_msg . ']' . '</td>';
					$this->html .= '</tr>';
				} else {
					$is_white_background_color = false;
					$is_gray_background_color = false;
					// Start the tr and set the tr background color.
					$this->html .= '<tr'; // wait for setting the background color.
					if ( ( $r % 2 ) == 0 ) {
						$is_white_background_color = true;
					} else {
						$is_gray_background_color = true;
					}

					$tr_class = [];
					if ( isset( $row['_total'] ) && $row['_total'] == true ) {
						$tr_class = [ 'content-tbody', 'grand-border', 'font-weight-td' ];
					} else if ( isset( $row['_subtotal'] ) && $row['_subtotal'] == true ) {
						$tr_class = [ 'content-tbody', 'top-border-thin', 'font-weight-td' ];
					}

					if ( $is_white_background_color ) {
						$tr_class[] = 'bg-white';
					} else {
						if ( $is_gray_background_color ) {
							$tr_class[] = 'bg-gray';
						}
					}

					$this->html .= ' class="' . implode( ' ', $tr_class ) . '">';

					if ( ( isset( $row['_subtotal'] ) && $row['_subtotal'] == true ) ) {
						//Figure out how many subtotal columns are set, so we can merge the cells
						foreach ( $sub_total_columns as $sub_total_column ) {
							if ( isset( $row[$sub_total_column] ) ) {
								$total_row_sub_total_columns++;
							}
						}

						//Make sure we only run this once per sub_total row.
						$sub_total_column_label_position = $this->getSubTotalColumnLabelPosition( $row, $columns, $sub_total_columns );
					}

					foreach ( $columns as $column => $tmp ) {
						if ( isset( $row[$column] ) ) {
							if ( isset( $column_format_config[$column] ) && $column_format_config[$column] == 'html' ) {
								$value = $row[$column]; //Allow raw HTML for displaying images and such.
							} else {
								$value = htmlentities( $row[$column], ENT_QUOTES ); //avoid xss attacks in reports.
							}
						} else {
							$value = ''; //This needs to be a space, otherwise cells won't be drawn and background colors won't be shown either.
						}

						//Bold total and sub-total rows, add lines above each cell.
						if ( ( isset( $row['_subtotal'] ) && $row['_subtotal'] == true ) || ( isset( $row['_total'] ) && $row['_total'] == true ) ) {

							if ( ( isset( $row['_subtotal'] ) && $row['_subtotal'] == true ) ) {
								//Debug::Text(' SubTotal Row... SI: '. $sub_total_columns_count .' Pos: '. $sub_total_column_label_position .' C: '. $c .' Row SI: '. $total_row_sub_total_columns, __FILE__, __LINE__, __METHOD__, 10);
								//Need to display "SubTotal" before the column that is being sub-totaled.
								if ( $sub_total_column_label_position !== false && $c == $sub_total_column_label_position && isset( $sub_total_rows[$total_row_sub_total_columns] ) ) {
									$value = TTi18n::getText( 'SubTotal' ) . '[' . $sub_total_rows[$total_row_sub_total_columns] . ']:';
								} else if ( $c < ( $total_row_sub_total_columns - 1 ) ) {
									$value = '';
								} else if ( $c == 0 && $sub_total_column_label_position === false && isset( $sub_total_rows[$total_row_sub_total_columns] ) ) {
									$value = '[' . $sub_total_rows[$total_row_sub_total_columns] . '] ' . $value;
								}
							}
						} else {
							//Don't show duplicate data in cells that are next to one another. But always show data after a sub-total.
							//Only do this for static columns that are also in group, subtotal or sort lists.
							//Make sure we don't remove duplicate values in pay stub reports, so if the value is a FLOAT then never replace it. (What static column would also be a float though?)
							//Make sure we don't replace duplicate values if the duplicates are blank value placeholders.
							if ( $this->config['other']['show_duplicate_values'] == false /*AND $new_page == FALSE*/ && !isset( $prev_row['_subtotal'] )
									&& isset( $prev_row[$column] ) && isset( $row[$column] ) && !is_float( $row[$column] ) && $prev_row[$column] === $row[$column]
									&& $prev_row[$column] !== $this->config['other']['blank_value_placeholder']
									&& ( isset( $static_column_options[$column] ) && ( ( isset( $sort_by_columns[$column] ) && !isset( $this->duplicate_value_ignored_columns[$column] ) ) || isset( $group_by_columns[$column] ) ) ) ) {
								//This needs to be a space otherwise cell background colors won't be shown.
								$value = ( $this->config['other']['duplicate_value_placeholder'] != '' ) ? $this->config['other']['duplicate_value_placeholder'] : ' ';
							}
						}

						if ( $this->config['other']['show_blank_values'] == true && !is_object($value) && $value == '' ) {
							//Update $row[$column] so the blank value gets put into the prev_row variable so we can check for it in the next loop.
							$value = $row[$column] = $this->config['other']['blank_value_placeholder'];
						}

						if ( !isset( $row['_total'] ) && $blank_row == true && $value == '' ) {
							//$this->html .= '<td>';
							//$this->html .= '&nbsp;';
							//$this->html .= '</td>';
							$n = '';
							unset( $n ); //code standards
						} else {
							$blank_row = false;

							$this->html .= '<td'; // wait for setting the css style in below.

							//Row formatting...
							$row_style = '';
							if ( isset( $row['_fontcolor'] ) && is_array( $row['_fontcolor'] ) ) {
								$row_style .= 'color: rgb( ' . $row['_fontcolor'][0] . ',' . $row['_fontcolor'][1] . ',' . $row['_fontcolor'][2] . ' );';
							}
							if ( isset( $row['_bgcolor'] ) && is_array( $row['_bgcolor'] ) ) {
								$row_style .= 'background-color: rgb( ' . $row['_bgcolor'][0] . ',' . $row['_bgcolor'][1] . ',' . $row['_bgcolor'][2] . ' );';
							}

							if ( $row_style != '' ) {
								$this->html .= ' style="' . $row_style . '"';
							}

							$this->html .= '>';

//							if ( isset($row['_page_break']) AND $row['_page_break'] == TRUE AND $c <= ( $columns_count - 1 ) ) { //This isn't quite working correctly yet.
//								$this->html .= '<div class="page-break"></div>';
//							}

							if ( is_object( $value ) ) {
								$this->html .= $value->display( 'html', 52, 25 );
								$this->html .= '</td>';
							} else {
								$this->html .= $value;
								$this->html .= '</td>';
							}
						}

						$c++;
					}
					unset( $tmp ); //code standards

					$this->html .= '</tr>';
				}

				//UnBold after sub-total rows, but NOT grand total row.
				if ( ( isset( $row['_subtotal'] ) && $row['_subtotal'] == true ) ) {
					$this->html .= '<tr><td colspan="' . $columns_count . '">&nbsp;</td></tr>';
				}

				if ( $blank_row != true ) {
					$r++;
				}

				if ( !isset( $row['_total'] ) && !isset( $row['_subtotal'] ) ) {
					$total_rows++;
					//Increment all sub_total rows for each group_by column.
					for ( $n = 0; $n <= $sub_total_columns_count; $n++ ) {
						$sub_total_rows[$n]++;
					}
				} else if ( isset( $row['_subtotal'] ) ) {
					//Clear only the sub_total row counter that we are displaying currently.
					$sub_total_rows[$total_row_sub_total_columns] = 0;
				}

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );

				$prev_row = $row;
			}

//			if ( $this->_html_checkMaximumPageLimit() == TRUE ) {
////				$this->_pdf_drawLine(1);
//			}

		} else {
			Debug::Text( 'No data or columns to display...', __FILE__, __LINE__, __METHOD__, 10 );
			if ( !is_array( $columns ) || count( $columns ) == 0 ) {
				$error_msg = TTi18n::getText( 'NO DISPLAY COLUMNS SELECTED' );
			} else if ( !is_array( $this->data ) || count( $this->data ) == 0 ) {
				$error_msg = TTi18n::getText( 'NO DATA MATCHES CRITERIA' );
			} else {
				$error_msg = TTi18n::getText( 'UNABLE TO DISPLAY REPORT' );
			}

			$this->html .= '<tr>';
			$this->html .= '<td class="no-result" colspan="' . $columns_count . '">' . '[' . $error_msg . ']' . '</td>';
			$this->html .= '</tr>';

			unset( $error_msg );
		}
		$this->html .= '</tbody>';
		$this->profiler->stopTimer( 'HTML Table' );

		return true;
	}

	/**
	 * @param $columns
	 * @param $column_options
	 * @param array $column_widths
	 * @param string $wrap_width UUID
	 * @param int $min_height
	 * @param int $multiplier Multiplier on the detected height in case its off slightly.
	 * @param int $multiplier_threshold Threshold value as to when the multiplier should be used.
	 * @return int
	 */
	function _pdf_getMaximumHeightFromArray( $columns, $column_options, $column_widths, $wrap_width, $min_height = 0, $multiplier = 1, $multiplier_threshold = null ) {
		$this->profiler->startTimer( 'Maximum Height' );
		$max_height = $min_height;
		$multiplier_threshold = ( $multiplier_threshold == '' ) ? $min_height * 5 : $multiplier_threshold;
		foreach ( $columns as $column => $tmp ) {
			$height = ( $min_height + ( $min_height * substr_count( ( isset( $column_options[$column] ) ? $column_options[$column] : '' ), "\n" ) ) ); //If there are pre-mature line endings, this increases the height for at least each one of them.
			if ( isset( $column_options[$column] ) && is_object( $column_options[$column] ) ) {
				$height = $column_options[$column]->getColumnHeight();
			} else if ( isset( $column_options[$column] ) && isset( $column_widths[$column] ) && $column_options[$column] != ''
					&& strlen( $column_options[$column] ) > $wrap_width ) { //Make sure we only calculate stringHeight if we exceed the wrap_width, as its a slow operation.
				$height = $this->pdf->getStringHeight( $column_widths[$column], wordwrap( $column_options[$column], $wrap_width ) );
				if ( $height > $multiplier_threshold )  { //Only apply the multiplier if height is above threshold
					$height = $height * $multiplier;
				}
				Debug::Text('Cell Height: '. $height .' Width: '. $column_widths[$column] .' Text: '. $column_options[$column], __FILE__, __LINE__, __METHOD__, 10);
			}
			if ( $height > $max_height ) {
				$max_height = $height;
			}
		}
		unset( $tmp ); //code standards
		$this->profiler->stopTimer( 'Maximum Height' );

		return $max_height;
	}

	/**
	 * @return bool
	 */
	function _pdf_Header() {
		$column_options = Misc::trimSortPrefix( $this->getOptions( 'columns' ) );
		$columns = $this->getReportColumns();
		$header_layout = $this->config['other']['layout']['header'];

		//Draw report information
		if ( $this->pdf->getPage() > 1 ) {
			$this->_pdf_drawLine( 0.75 ); //Slightly smaller than first/last lines.
		}

		if ( is_array( $columns ) && count( $columns ) > 0 ) {
			$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( $this->config['other']['table_header_font_size'] ) );
			$this->pdf->setTextColor( 0 );
			$this->pdf->setDrawColor( 0 );
			$this->pdf->setFillColor( 240 ); //Grayscale only.

			$column_widths = $this->data_column_widths;
			//$cell_height = $this->_pdf_getMaximumNumLinesFromArray( $columns, $column_options, $column_widths ) * $this->_pdf_fontSize( $header_layout['height'] );
			$cell_height = $this->_pdf_getMaximumHeightFromArray( $columns, $column_options, $column_widths, $this->config['other']['table_header_word_wrap'], $this->_pdf_fontSize( $header_layout['height'] ) );
			foreach ( $columns as $column => $tmp ) {
				if ( isset( $column_options[$column] ) && isset( $column_widths[$column] ) ) {
					$cell_width = $column_widths[$column];
					if ( ( $this->pdf->getX() + $cell_width ) > $this->pdf->getPageWidth() ) {
						Debug::Text( ' Page not wide enough, it should be at least: ' . ( $this->pdf->getX() + $cell_width ) . ' Page Width: ' . $this->pdf->getPageWidth(), __FILE__, __LINE__, __METHOD__, 10 );
						$this->pdf->Ln();
					}
					//$this->pdf->Cell( $cell_width, $this->_pdf_fontSize( $header_layout['height'] ), $column_options[$column], $header_layout['border'], 0, $header_layout['align'], $header_layout['fill'], '', $header_layout['stretch'] );
					//Wrapping shouldn't be needed as the cell widths should expand to at least fit the header. Wrapping may be needed on regular rows though.
					$this->pdf->MultiCell( $cell_width, $cell_height, wordwrap( $column_options[$column], $this->config['other']['table_header_word_wrap'] ), 0, $header_layout['align'], $header_layout['fill'], 0 );
				} else {
					Debug::Text( ' Invalid Column: ' . $column, __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
			unset( $tmp ); //code standards
			$this->pdf->Ln();
			//$this->pdf->Ln( $cell_height ); //Used for multi-cell wrapping

			$this->_pdf_drawLine( 0.75 ); //Slightly smaller than first/last lines.
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function _pdf_Footer() {
		$margins = $this->pdf->getMargins();

		//Don't scale these lines as they aren't that important anyways.
		$this->pdf->SetFont( $this->config['other']['default_font'], '', 8 );
		$this->pdf->setTextColor( 0 );
		$this->pdf->setDrawColor( 0 );

		//Save x, y and restore after footer is set.
		$x = $this->pdf->getX();
		$y = $this->pdf->getY();

		//Jump to end of page.
		$this->pdf->setY( ( $this->pdf->getPageHeight() - $margins['bottom'] - $margins['top'] - 5 ) );

		$this->pdf->Cell( ( $this->pdf->getPageWidth() - $margins['right'] ), 5, TTi18n::getText( 'Page' ) . ' ' . $this->pdf->PageNo() . ' of ' . $this->pdf->getAliasNbPages(), 0, 0, 'C', 0 );
		$this->pdf->Ln( 3 );

		$this->pdf->SetFont( $this->config['other']['default_font'], '', 6 );
		$this->pdf->Cell( ( $this->pdf->getPageWidth() - $margins['right'] ), 5, TTi18n::gettext( 'Report Generated By' ) . ' ' . APPLICATION_NAME . ' v' . APPLICATION_VERSION . ' @ ' . TTDate::getDate( 'DATE+TIME', $this->start_time ), 0, 0, 'C', 0 );

		$this->pdf->setX( $x );
		$this->pdf->setY( $y );

		return true;
	}

	/**
	 * @return bool
	 */
	function _pdf_Chart() {
		if ( $this->isEnabledChart() == true ) {
			$chart_config = $this->getChartConfig();

			Debug::Text( ' Adding charts to PDF...', __FILE__, __LINE__, __METHOD__, 10 );

			$total_images = ( is_array( $this->chart_images ) ) ? count( $this->chart_images ) : 0;
			if ( is_array( $this->chart_images ) && $total_images > 0 ) {
				$margins = $this->pdf->getMargins();

				$x = 1;
				foreach ( $this->chart_images as $chart_image ) {
					if ( $x == 1 && isset( $chart_config['display_mode'] ) && $chart_config['display_mode'] == 10 ) {
						//In case the table is displayed above the chart, insert a small space.
						$this->pdf->setY( ( $this->pdf->getY() + 5 ) );
					}

					if ( isset( $chart_image['file'] ) && file_exists( $chart_image['file'] ) ) {
						$remaining_page_height = ( $this->pdf->getPageHeight() - $this->pdf->getY() );

						Debug::Text( ' Adding chart: ' . $chart_image['file'] . ' Page: ' . $this->pdf->getPage() . ' Width: ' . $chart_image['width'] . ' Height: ' . $chart_image['height'] . ' Page Width: ' . $this->pdf->getPageWidth() . ' Page Height: ' . $this->pdf->getPageHeight(), __FILE__, __LINE__, __METHOD__, 10 );

						if ( $x == 1 && $this->pdf->getPage() == 1 && isset( $chart_config['display_mode'] ) && ( $chart_config['display_mode'] == 20 || $chart_config['display_mode'] == 30 ) ) {
							//Resize the first chart to fit on the page with the report summary.
							//Resizing the chart causes the fonts to become blocky and hard to read. Instead make the original chart image smaller.
							//Always check the chart at 100% zoom.
							//$this->pdf->Image( $chart_image['file'], '', '', '', ($this->pdf->getPageHeight()-($this->pdf->getY()+$margins['bottom']+20)), '', '', '', FALSE, 300, 'C', FALSE, FALSE, 0, TRUE, FALSE, FALSE );
							$this->pdf->Image( $chart_image['file'], '', '', '', '', '', '', '', false, 300, 'C', false, false, 0, true, false, false );
						} else {
							if ( $remaining_page_height < $chart_image['height'] ) {
								$this->_pdf_Footer();
								$this->pdf->AddPage();
								$this->pdf->setY( ( $this->pdf->getY() + $margins['top'] ) );
							}

							$this->pdf->Image( $chart_image['file'], '', '', '', '', '', '', '', false, 300, 'C', false, false, 0, false, false, false );
						}

						$this->pdf->setY( ( $this->pdf->getY() + $chart_image['height'] ) );
						$this->_pdf_Footer();
					}

					if ( $x == $total_images && isset( $chart_config['display_mode'] ) && $chart_config['display_mode'] == 20 ) {
						//In case the table is displayed below the chart, insert a small space.
						$this->pdf->setY( ( $this->pdf->getY() + 25 ) );
					}

					@unlink( $chart_image['file'] );

					$x++;
				}
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function _html_Chart() {
		if ( $this->isEnabledChart() == true ) {
			$chart_config = $this->getChartConfig();

			Debug::Text( ' Adding charts to PDF...', __FILE__, __LINE__, __METHOD__, 10 );

			$total_images = ( is_array( $this->chart_images ) ) ? count( $this->chart_images ) : 0;
			if ( is_array( $this->chart_images ) && $total_images > 0 ) {

				$this->html .= "<tr><td>&nbsp;</td></tr>";

				$x = 1;
				foreach ( $this->chart_images as $chart_image ) {
					//if ( $x == 1 AND isset($chart_config['display_mode']) AND $chart_config['display_mode'] == 10 ) {
					//In case the table is displayed above the chart, insert a small space.
					//}

					if ( isset( $chart_image['file'] ) && file_exists( $chart_image['file'] ) ) {

						$this->html .= "<tr>";

						if ( $x == 1 && isset( $chart_config['display_mode'] ) && ( $chart_config['display_mode'] == 20 || $chart_config['display_mode'] == 30 ) ) {
							//Resize the first chart to fit on the page with the report summary.
							//Resizing the chart causes the fonts to become blocky and hard to read. Instead make the original chart image smaller.
							//Always check the chart at 100% zoom.
							$this->html .= "<td>";
							$this->html .= '<img src="data:image/x-icon;base64,' . base64_encode( file_get_contents( $chart_image['file'] ) ) . '">';
							$this->html .= "</td>";
						} else {

							$this->html .= "<td>";
							$this->html .= '<img src="data:image/x-icon;base64,' . base64_encode( file_get_contents( $chart_image['file'] ) ) . '">';
							$this->html .= "</td>";
						}
						$this->html .= "</tr>";
						$this->html .= "<tr><td>&nbsp;</td></tr>";
					}

					@unlink( $chart_image['file'] );

					$x++;
				}
			}
		}

		return true;
	}

	/**
	 * @return string
	 */
	function _html_CSS() {
		$css = '* { margin:0; padding:0;}';
		$css .= 'body{ font-size: 100%, font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;padding: 15px;}';
		$css .= '.report table{ border-collapse: collapse; border-spacing:0;}';
		$css .= '.report th, .report td{ padding: 2px 4px 2px 4px;}';
		$css .= 'div.page-break{ page-break-after: always; page-break-inside: avoid; }'; //Handle page breaks when <div class="page-break"></div> is specified.
		$css .= '.header{ width: 100%}';
		$css .= '.full-width{ width: 100%}';
//		$css .= '.header tbody{font-size: 14px;}';
		$css .= '.col-header{ font-size: ' . $this->_html_fontSize( 200 ) . '%; font-weight: bold;}';
		$css .= '.col-header tbody{ font-size: ' . $this->_html_fontSize( 400 ) . '%; font-weight: bold;}';
		$css .= '.logo{ text-align: right; }';
		$css .= '.print-icon{ text-align: center; vertical-align:top; cursor: pointer;}';
		$css .= '.col-description tbody{ font-size: ' . $this->_html_fontSize( 75 ) . '%;}';
		$css .= '.col-description .desc-table{ width: 100%}';
		$css .= '.col-description .desc-left{ width: 45%}';
		$css .= '.generated{ vertical-align: bottom; width:45%; }';
		$css .= '.generated table{ float: right; text-align: right;}';
		$css .= '.timeperiod{ width: 100px; }';
		$css .= '.content{ text-align: right; border-collapse: collapse; width: 100%; }';
		$css .= '.content thead{ top:0; text-align: right; vertical-align: text-top }';
		$css .= '.content-thead td{ padding: 0; }';
		$css .= '.content-thead tbody{ font-size: ' . $this->_html_fontSize( 100 ) . '%;}';
		$css .= '.content-tbody{ white-space: nowrap; font-size: ' . $this->_html_fontSize( 100 ) . '%;}';
		$css .= '.content-tbody td{ padding: 2px 4px 2px 4px; width: 1%;}';
		$css .= '.exceeded-error{ text-align:center; font-size: ' . $this->_html_fontSize( 250 ) . '%; font-weight: bold; color: #FF0000}';
		$css .= '.exceeded-warning{ text-align:center; font-size: ' . $this->_html_fontSize( 200 ) . '%; font-weight: bold;}';
		$css .= '.blank-row{ height: 10px}';
		$css .= '.content-footer td{ width: 1%;}';
		$css .= '.content-header th{ width: 1%; white-space: nowrap; font-size: ' . $this->_html_fontSize( 100 ) . '%; border-bottom: 5px solid #000000; border-top: 5px solid #000000; background-color: #e5e5e5; text-align: right; }';
		$css .= '.footer{ margin:0 auto; text-align: center; vertical-align: middle; font-size: ' . $this->_html_fontSize( 75 ) . '%; }';
		$css .= '.bg-white{ background-color: #FFFFFF; }';
		$css .= '.bg-gray{ background-color: #FAFAFA; }';
//		$css .= '.no-top-border{ border: none; }';
		$css .= '.top-border-thin{ border-top-color: #000000; border-top-style: solid; border-top-width: 1px; }';
		$css .= '.top-border-bold{ border-top-color: #000000; border-top-style: solid; border-top-width: 2px; }';
		$css .= '.grand-border{ border-bottom-color: #000000; border-bottom-style: solid; border-bottom-width: 2px; border-top-color: #000000; border-top-style: double; }';
		$css .= '.font-weight-td{ font-weight: bold; }';
		$css .= '.col-header-left table{ table-layout: fixed; width:100%; }';
		$css .= '.col-header-left td{ word-wrap: break-word; overflow-wrap: break-word; padding: 0; }';
//		$css .= '.col-header-right{ vertical-align:top; }';
		$css .= '.chart{ margin: 0 auto; margin-bottom: 50px; }';
		$css .= '.no-result{ text-align: center; font-weight: bold; font-size: ' . $this->_html_fontSize( 200 ) . '%; }';
		$css .= $this->_html_setPageOrientationCSS();

		return $css;
	}

	/**
	 * @return string
	 */
	function _html_setPageOrientationCSS() {
		$css = '@page {size: ';
		if ( $this->config['other']['page_orientation'] == 'L' ) {
			$css .= 'landscape'; // print landscape model.
		} else {
			$css .= 'portrait'; // print portrait model.
		}

		$css .= ';}';

		return $css;
	}

	/**
	 * @return bool
	 */
	function _html_Initialize() {

		$this->profiler->startTimer( 'HTML' );

		if ( !$this->html ) {
			//Page width: 205mm
			$this->html = '<html>';
			$this->html .= '<head>';
			$this->html .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
			$this->html .= '<title>' . htmlentities( $this->getDescription( 'report_name' ), ENT_QUOTES ) . '</title>';
			$this->html .= '<meta name="author" content="' . htmlentities( $this->getUserObject()->getFullName(), ENT_QUOTES ) . '">';
			$this->html .= '<meta name="description" content="' . APPLICATION_NAME . ' ' . TTi18n::getText( 'Report' ) . '">';
			$this->html .= '<style type="text/css">';
			$this->html .= $this->_html_CSS();
			$this->html .= '</style>';
			$this->html .= '<script type="text/javascript">' . file_get_contents( Environment::getBasePath() . '/interface/html5/framework/jquery.min.js' ) . '</script>';
			$this->html .= '<script type="text/javascript">' . file_get_contents( Environment::getBasePath() . '/interface/html5/framework/jquery.stickytableheaders.min.js' ) . '</script>';

			$this->html .= '</head>';
		}

		$this->profiler->stopTimer( 'HTML' );

		return true;
	}

	/**
	 * @return bool
	 */
	function _pdf_Initialize() {
		$this->profiler->startTimer( 'PDF' );

		if ( !is_object( $this->pdf ) ) {
			//Page width: 205mm
			$this->pdf = new TTPDF( $this->config['other']['page_orientation'], 'mm', $this->config['other']['page_format'], $this->getUserObject()->getCompanyObject()->getEncoding() );

			$this->pdf->setImageScale( $this->PDF_IMAGE_SCALE_RATIO );

			$this->pdf->SetAuthor( $this->getUserObject()->getFullName() );
			//$this->pdf->SetTitle( $this->title );
			$this->pdf->SetTitle( $this->getDescription( 'report_name' ) );
			$this->pdf->SetSubject( APPLICATION_NAME . ' ' . TTi18n::getText( 'Report' ) );

			$this->pdf->setMargins( $this->config['other']['left_margin'], $this->config['other']['top_margin'], $this->config['other']['right_margin'] ); //Margins are ignored because we use setXY() to force the coordinates before each drawing and therefore ignores margins.
			$this->pdf->SetAutoPageBreak( false );

			$column_options = (array)Misc::trimSortPrefix( $this->getOptions( 'columns' ) );
			$columns = $this->getReportColumns();

			//Debug::Arr($columns, ' Report Columns: ', __FILE__, __LINE__, __METHOD__, 10);

			//
			//Table Header - Start
			//
			$this->config['other']['layout']['header'] = [
					'max_width'    => 500, //Double the word wrap length?
					'cell_padding' => 2,
					'height'       => 8,
					'align'        => 'R',
					'border'       => 0,
					'fill'         => 1,
					'stretch'      => 1,
			];

			//Determine how large the page needs to be, and change its format as necessary.
			$page_size = $this->_pdf_detectPageSize( $column_options, $columns );
			$this->pdf->AddPage( $this->config['other']['page_orientation'], $page_size );
		}

		$this->profiler->stopTimer( 'PDF' );

		return true;
	}

	/**
	 * @return bool|null|string
	 */
	function _html() {
		$chart_config = $this->getChartConfig();

		$this->_html_TopSummary(); // initialize the top filter, sort etc informations.

		if ( $this->isEnabledChart() == true && isset( $chart_config['display_mode'] ) && $chart_config['display_mode'] == 30 ) {
			$this->html .= '<table class="chart">';
			$this->_html_Chart(); // do the same thing with the pdf does.
			$this->html .= '</table>';
		} else {

			if ( $this->isEnabledChart() == true && isset( $chart_config['display_mode'] ) && $chart_config['display_mode'] == 20 ) {
				$this->html .= '<table class="chart">';
				$this->_html_Chart();
				$this->html .= '</table>';
			}
//			$this->html .= '<table class="content">';

			$this->_html_Header(); // do the table header.
			$this->_html_Table();  // do the table content.

			$this->html .= '</table>';

			if ( $this->isEnabledChart() == true && isset( $chart_config['display_mode'] ) && $chart_config['display_mode'] == 10 ) {
				$this->html .= '<table class="chart">';
				$this->_html_Chart();
				$this->html .= '</table>';
			}
		}

		$this->_html_Footer(); //  do the footer of the page.

		if ( $this->html !== false ) {
			return $this->html;
		}

		return false;
	}

	function _html_Footer() {
		$this->html .= '<table class="footer">';
		$this->html .= '<tr><td>&nbsp;</td></tr>';
		$this->html .= '<tr><td>&nbsp;</td></tr>';
		$this->html .= '<tr><td>&nbsp;</td></tr>';
		$this->html .= '<tr>';
		$this->html .= '<td>';
		$this->html .= TTi18n::gettext( 'Report Generated By' ) . ' ' . APPLICATION_NAME . ' v' . APPLICATION_VERSION . ' @ ' . TTDate::getDate( 'DATE+TIME', $this->start_time );
		$this->html .= '</td>';
		$this->html .= '</tr>';
		$this->html .= '</table>';
		if ( isset( $this->config['other']['auto_refresh'] ) && $this->config['other']['auto_refresh'] > 0 ) {
			$this->html .= '<script>auto_refresh_time = ' . $this->config['other']['auto_refresh'] . ';</script>'; //Must be global variable
		} else {
			$this->html .= '<script>auto_refresh_time = -1;</script>'; //Must be global variable
		}

		$this->html .= '<script>$("table.content").stickyTableHeaders();</script>';
	}

	/**
	 * @return bool
	 */
	function _html_Header() {

		$this->html .= '<table class="content">';
		$column_options = Misc::trimSortPrefix( $this->getOptions( 'columns' ) );
		$columns = $this->getReportColumns();
		$this->html .= '<thead>';

		$this->html .= '<tr class="content-thead content-header">';

		if ( is_array( $columns ) && count( $columns ) > 0 ) {
			foreach ( $columns as $column => $tmp ) {
				if ( isset( $column_options[$column] ) ) {
					//$cell_width = $column_widths[$column];
					$this->html .= '<th>' . wordwrap( $column_options[$column], $this->config['other']['table_header_word_wrap'], '<br>' ) . '</th>';
				} else {
					$this->html .= '<th>&nbsp;</th>';
					Debug::Text( ' Invalid Column: ' . $column, __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
			unset( $tmp );//code standards
			$this->html .= '</tr>';
			$this->html .= '</thead>';
		} else {
			$this->html .= '<th>&nbsp;</th>';
			$this->html .= '</tr>';
			$this->html .= '</thead>';
		}

		return true;
	}


	/**
	 * @return bool
	 */
	function _html_TopSummary() {
		//global $config_vars;

		if ( is_object( $this->getUserObject()->getCompanyObject() ) ) {
			$file_name = $this->getUserObject()->getCompanyObject()->getLogoFileName( $this->getUserObject()->getCompanyObject()->getId() );
		}

		$other_config = $this->getOtherConfig();

		//Draw report information
		$this->html .= '<body class="report">';

		if ( !isset( $other_config['is_embedded'] ) || $other_config['is_embedded'] == false ) { //Reports embedded in dashlets shouldn't display the description block.
			$this->html .= '<table class="header">';
			$this->html .= '<tr class="col-header">';

			$this->html .= '<td class="col-header-left">';
			//Report Name
			$report_name = $this->getDescription( 'report_name' );
			if ( $report_name != '' ) {
				$this->html .= '<table>';
				$this->html .= '<tr>';
				$this->html .= '<td>' . htmlspecialchars( $report_name, ENT_QUOTES ) . '</td>';
				$this->html .= '</tr>';
				$this->html .= '</table>';
				$this->html .= '</td>';
			} else {
				// title
				$this->html .= '<table>';
				$this->html .= '<tr>';
				$this->html .= '<td>' . htmlspecialchars( $this->title, ENT_QUOTES ) . '</td>';
				$this->html .= '</tr>';
				$this->html .= '</table>';
				$this->html .= '</td>';
			}

//			$this->html .= '<td class="col-header-right">';
			$this->html .= '<td>';
			$this->html .= '<table class="full-width">';
			$this->html .= '<tr>';
			$this->html .= '<td class="logo"><img width="163" height="45" alt="image" src="data:image/x-icon;base64,' . base64_encode( file_get_contents( $file_name ) ) . '"></td>';
			$this->html .= '</tr>';
			$this->html .= '</table>';
			$this->html .= '</td>';

			$this->html .= '</tr>';


			$this->html .= '<tr class="col-description">';

			$this->html .= '<td colspan="2">';

			$this->html .= '<table class="desc-table">';

			$this->html .= '<tr>';

			// left
			$this->html .= '<td class="desc-left">';
			$this->html .= '<table>';
			// title
			if ( $report_name != '' ) {
				$this->html .= '<tr>';
				$this->html .= '<td>' . TTi18n::getText( 'Report' ) . ':' . '</td>';
				$this->html .= '<td>' . htmlspecialchars( $this->title, ENT_QUOTES ) . '</td>';
				$this->html .= '</tr>';
			}

			$report_description = $this->getDescription( 'report_description' );
			if ( $report_description != '' ) {
				$this->html .= '<tr>';
				$this->html .= '<td style="vertical-align:top">' . TTi18n::getText( 'Description' ) . ':' . '</td>';
				$this->html .= '<td>' . nl2br( htmlspecialchars( $report_description, ENT_QUOTES ) ) . '</td>';
				$this->html .= '</tr>';
			}

			//Time Period: start/end date, or pay period.
			$description = $this->getDescription( 'time_period' );
			if ( $description != '' ) {
				$this->html .= '<tr>';
				$this->html .= '<td class="timeperiod">' . TTi18n::getText( 'Time Period' ) . ':' . '</td>';
				$this->html .= '<td>' . htmlspecialchars( $description, ENT_QUOTES ) . '</td>';
				$this->html .= '</tr>';
			}
			//Filter:
			$description = $this->getDescription( 'filter' );
			if ( $description != '' ) {
				$this->html .= '<tr>';
				$this->html .= '<td>' . TTi18n::getText( 'Filter' ) . ':' . '</td>';
				$this->html .= '<td>' . htmlspecialchars( $description, ENT_QUOTES ) . '</td>';
				$this->html .= '</tr>';
			}
			//Group:
			$description = $this->getDescription( 'group' );
			if ( $description != '' ) {
				$this->html .= '<tr>';
				$this->html .= '<td>' . TTi18n::getText( 'Group' ) . ':' . '</td>';
				$this->html .= '<td>' . htmlspecialchars( $description, ENT_QUOTES ) . '</td>';
				$this->html .= '</tr>';
			}
			//SubTotal:
			$description = $this->getDescription( 'sub_total' );
			if ( $description != '' ) {
				$this->html .= '<tr>';
				$this->html .= '<td>' . TTi18n::getText( 'SubTotal' ) . ':' . '</td>';
				$this->html .= '<td>' . htmlspecialchars( $description, ENT_QUOTES ) . '</td>';
				$this->html .= '</tr>';
			}
			//Sort:
			$description = $this->getDescription( 'sort' );
			if ( $description != '' ) {
				$this->html .= '<tr>';
				$this->html .= '<td>' . TTi18n::getText( 'Sort' ) . ':' . '</td>';
				$this->html .= '<td>' . htmlspecialchars( $description, ENT_QUOTES ) . '</td>';
				$this->html .= '</tr>';
			}

			//Custom Filter:
			$description = $this->getDescription( 'custom_filter' );
			if ( $description != '' ) {
				$this->html .= '<tr>';
				$this->html .= '<td>' . TTi18n::getText( 'Custom Filter' ) . ':' . '</td>';
				$this->html .= '<td>' . htmlspecialchars( $description, ENT_QUOTES ) . '</td>';
				$this->html .= '</tr>';
			}
			$this->html .= '</table>';
			$this->html .= '</td>';

			// center
			$this->html .= '<td class="print-icon"><img onclick="' . "this.style.display='none';" . 'window.print();' . "this.style.display='inline-block';" . '" src="data:image/x-icon;base64,' . base64_encode( file_get_contents( Environment::getBasePath() . '/interface/html5/theme/default/css/global/widgets/ribbon/icons/print-35x35.png' ) ) . '"></td>';

			// right
			$this->html .= '<td class="generated">';
			$this->html .= '<table>';
			$this->html .= '<tr><td>' . TTi18n::getText( 'Generated' ) . ': ' . TTDate::getDate( 'DATE+TIME', $this->start_time ) . '</td></tr>';
			$this->html .= '<tr><td>' . TTi18n::getText( 'Generated For' ) . ': ' . htmlspecialchars( $this->getUserObject()->getFullName(), ENT_QUOTES ) . '</td></tr>';
			$this->html .= '</table>';
			$this->html .= '</td>';

			$this->html .= '</tr>';

			$this->html .= '</table>';

			$this->html .= '</td>';


			$this->html .= '</tr>';

			$this->html .= '</table>';
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function _pdf() {
		$chart_config = $this->getChartConfig();

		//$this->_pdf_Initialize(); //This is called in Output() function, as it needs to happen before the charts are generated.
		$this->_pdf_TopSummary();

		if ( $this->isEnabledChart() == true && isset( $chart_config['display_mode'] ) && $chart_config['display_mode'] == 30 ) {
			$this->_pdf_Chart();
		} else {
			if ( $this->isEnabledChart() == true && isset( $chart_config['display_mode'] ) && $chart_config['display_mode'] == 20 ) {
				$this->_pdf_Chart();
			}

			$this->_pdf_Header();
			$this->_pdf_Table();

			if ( $this->isEnabledChart() == true && isset( $chart_config['display_mode'] ) && $chart_config['display_mode'] == 10 ) {
				$this->_pdf_Chart();
			}
		}

		$this->_pdf_Footer();
		$output = $this->pdf->Output( '', 'S' );
		if ( $output !== false ) {
			return $output;
		}

		return false;
	}

	/**
	 * @param $row
	 * @param $columns
	 * @param $sub_total_columns
	 * @return bool|mixed
	 */
	function getSubTotalColumnLabelPosition( $row, $columns, $sub_total_columns ) {
		$sub_total_column_position = false;
		if ( count( $sub_total_columns ) > 0 ) {
			$tmp_columns = array_keys( $columns );
			$tmp_sub_total_columns = array_reverse( $sub_total_columns );
			foreach ( $tmp_sub_total_columns as $sub_total_column ) {
				if ( isset( $row[$sub_total_column] ) ) {
					//Find which position this sub_total column is in.
					$sub_total_column_position = ( array_search( $sub_total_column, $tmp_columns ) - 1 );
					break;
				}
			}
		}

		if ( $sub_total_column_position < 0 ) {
			$sub_total_column_position = false;
		}

		return $sub_total_column_position;
	}

	/**
	 * Generate PDF.
	 * @return bool
	 * @noinspection PhpUnusedLocalVariableInspection
	 */
	function _pdf_Table() {
		$this->profiler->startTimer( 'PDF Table' );

		$this->getProgressBarObject()->start( $this->getAPIMessageID(), ( is_array( $this->data ) ? count( $this->data ) : 0 ), null, TTi18n::getText( 'Generating PDF...' ) );

		$border = 0;

		//Remove some columns from sort by that may be common but we don't want duplicate values to be removed. This could be moved to each report if the list gets too large.
		$sort_by_columns = $this->getSortConfig();
		$group_by_columns = $this->getGroupConfig();

		//Make sure we ignore a group_by_columns that is an array( 0 => FALSE )
		if ( is_array( $group_by_columns ) && count( $group_by_columns ) > 0 && $group_by_columns[0] !== false ) {
			$group_by_columns = array_flip( $group_by_columns );
		}
		$columns = $this->getReportColumns();

		$sub_total_columns = (array)$this->getSubTotalConfig();
		$static_column_options = (array)Misc::trimSortPrefix( $this->getOptions( 'static_columns' ) );
		$sub_total_columns_count = ( count( array_intersect( array_keys( $static_column_options ), array_keys( (array)$columns ) ) ) > 1 ) ? count( $sub_total_columns ) : 0; //If there is only one static column, we can't indent the "Grand Total" label.
		$sub_total_rows = [];                                                                                                                                                 //Count all rows included in sub_total
		for ( $n = 0; $n <= $sub_total_columns_count; $n++ ) {
			$sub_total_rows[$n] = 0;
		}
		//Debug::Arr($sort_by_columns, ' Sort Columns: ', __FILE__, __LINE__, __METHOD__, 10);

		$row_layout = [
				'max_width'    => 30,
				'cell_padding' => 2,
				'height'       => 5,
				'align'        => 'R',
				'border'       => 0,
				'fill'         => 1,
				'stretch'      => 1,
		];

		$column_widths = $this->data_column_widths;

		$force_page_break = false;
		$prev_row = [];
		$r = 0;
		$total_rows = 0; //Count all rows included in grand total
		if ( is_array( $columns ) && count( $columns ) > 0 && is_array( $this->data ) && count( $this->data ) > 0 ) {
			foreach ( $this->data as $key => $row ) {
				$row_cell_height = $this->_pdf_getMaximumHeightFromArray( $columns, $row, $column_widths, $this->config['other']['table_data_word_wrap'], $this->_pdf_fontSize( $row_layout['height'] ) );

				//If the next row is a subtotal or total row, do a page break early, so we don't show just a subtotal/total by itself.
				if ( isset( $this->data[( $key + 1 )] )
						&& ( ( isset( $this->data[( $key + 1 )]['_subtotal'] ) && $this->data[( $key + 1 )]['_subtotal'] == true )
								|| ( isset( $this->data[( $key + 1 )]['_total'] ) && $this->data[( $key + 1 )]['_total'] == true )
								|| ( isset( $this->data[( $key + 1 )]['_page_break]'] ) && $this->data[( $key + 1 )]['_page_break'] == true ) ) ) {
					$page_break_row_height = ( $row_cell_height * 2.5 );
				} else {
					$page_break_row_height = $row_cell_height;
				}

				if ( $this->_pdf_checkMaximumPageLimit() == false ) {
					//Exceeded maximum pages, stop processing.
					$this->_pdf_displayMaximumPageLimitError();
					break;
				}

				if ( $force_page_break == true ) {
					$new_page = $this->_pdf_AddPage();
					$force_page_break = false;
				}

				if ( isset( $row['_page_break'] ) && $row['_page_break'] == true ) {
					$force_page_break = true;
				} else {
					$new_page = $this->_pdf_checkPageBreak( $page_break_row_height, true );
				}

				//Reset all styles/fills after page break.
				$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( $this->config['other']['table_row_font_size'] ) );
				$this->pdf->SetTextColor( 0 );
				$this->pdf->SetDrawColor( 0 );
				if ( ( $r % 2 ) == 0 ) {
					$this->pdf->setFillColor( 255 );
				} else {
					$this->pdf->setFillColor( 250 );
				}

				//Add a little extra space before the Grand Total line, so we can insert a double line.
				if ( isset( $row['_total'] ) && $row['_total'] == true ) {
					$this->pdf->Ln( 1 );
				}

				$c = 0;
				$total_row_sub_total_columns = 0;
				$blank_row = true;
				if ( $r == 0 && ( isset( $row['_total'] ) && $row['_total'] == true ) ) {
					Debug::Text( 'Last row is grand total, no actual data to display...', __FILE__, __LINE__, __METHOD__, 10 );
					$error_msg = TTi18n::getText( 'NO DATA MATCHES CRITERIA' );
					$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 16 ) );
					$this->pdf->Cell( $this->pdf->getPageWidth(), 20, '[' . $error_msg . ']', 0, 0, 'C', 0, '', 0 );
				} else {
					if ( ( isset( $row['_subtotal'] ) && $row['_subtotal'] == true ) ) {
						//Figure out how many subtotal columns are set, so we can merge the cells
						foreach ( $sub_total_columns as $sub_total_column ) {
							if ( isset( $row[$sub_total_column] ) ) {
								$total_row_sub_total_columns++;
							}
						}

						//Make sure we only run this once per sub_total row.
						$sub_total_column_label_position = $this->getSubTotalColumnLabelPosition( $row, $columns, $sub_total_columns );
					}

					foreach ( $columns as $column => $tmp ) {
						if ( isset( $row[$column] ) ) {
							$value = $row[$column];
						} else {
							$value = ''; //This needs to be a space, otherwise cells won't be drawn and background colors won't be shown either.
						}

						//Debug::Text(' Row: '. $key .' Column: '. $column .'('.$c.') Value: '. $value .' Count Cols: '. count($row) .' Sub Total Columns: '. $total_row_sub_total_columns, __FILE__, __LINE__, __METHOD__, 10);
						//Debug::Text(' Row: '. $key .' Column: '. $column .'('.$c.') Value: '. $value .' Count Cols: '. count($row), __FILE__, __LINE__, __METHOD__, 10);
						$cell_width = ( isset( $column_widths[$column] ) ) ? $column_widths[$column] : 30;

						//Bold total and sub-total rows, add lines above each cell.
						if ( ( isset( $row['_subtotal'] ) && $row['_subtotal'] == true ) || ( isset( $row['_total'] ) && $row['_total'] == true ) ) {
							$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( $this->config['other']['table_row_font_size'] ) );

							if ( ( isset( $row['_subtotal'] ) && $row['_subtotal'] == true ) ) {
								//Debug::Text(' SubTotal Row... SI: '. $sub_total_columns_count .' Pos: '. $sub_total_column_label_position .' C: '. $c .' Row SI: '. $total_row_sub_total_columns, __FILE__, __LINE__, __METHOD__, 10);
								//Need to display "SubTotal" before the column that is being sub-totaled.
								if ( $sub_total_column_label_position !== false && $c == $sub_total_column_label_position && isset( $sub_total_rows[$total_row_sub_total_columns] ) ) {
									$value = TTi18n::getText( 'SubTotal' ) . '[' . $sub_total_rows[$total_row_sub_total_columns] . ']:';
								} else if ( $c < ( $total_row_sub_total_columns - 1 ) ) {
									$value = '';
								} else if ( $c == 0 && $sub_total_column_label_position === false && isset( $sub_total_rows[$total_row_sub_total_columns] ) ) {
									$value = '[' . $sub_total_rows[$total_row_sub_total_columns] . '] ' . $value;
								}
							}    //else {
							//Debug::Text(' C: '. $c .' Row SI: '. $sub_total_columns_count, __FILE__, __LINE__, __METHOD__, 10);
							//Display "Grand Total" immediately before all the columns that are totaled, or on the last static column.

							//This is handled in the Total() function now so we can properly deterine the column widths earlier on.
							//if ( $c == $sub_total_columns_count ) {
							//	$value = TTi18n::getText('Grand Total').'['. $total_rows .']:';
							//}
							//}

							//Put a line above the sub-total cell, and a double line above grand total cell
							if ( isset( $row['_total'] ) && $row['_total'] == true ) {
								$this->pdf->setFillColor( 245 );
								$this->pdf->setLineWidth( 0.25 );
								$this->pdf->Line( ( $this->pdf->getX() + 1 ), ( $this->pdf->getY() - 1 ), ( $this->pdf->getX() + ( $cell_width - 1 ) ), ( $this->pdf->getY() - 1 ) );
								$this->pdf->Line( ( $this->pdf->getX() + 1 ), ( $this->pdf->getY() - 0.5 ), ( $this->pdf->getX() + ( $cell_width - 1 ) ), ( $this->pdf->getY() - 0.5 ) );
							} else if ( $c >= ( $total_row_sub_total_columns - 1 ) ) {
								$this->pdf->setLineWidth( 0.5 );
								$this->pdf->Line( ( $this->pdf->getX() + 1 ), $this->pdf->getY(), ( $this->pdf->getX() + ( $cell_width - 1 ) ), $this->pdf->getY() );
							}
						} else {
							//Don't show duplicate data in cells that are next to one another. But always show data after a sub-total.
							//Only do this for static columns that are also in group, subtotal or sort lists.
							//Make sure we don't remove duplicate values in pay stub reports, so if the value is a FLOAT then never replace it. (What static column would also be a float though?)
							//Make sure we don't replace duplicate values if the duplicates are blank value placeholders.
							if ( $this->config['other']['show_duplicate_values'] == false && $new_page == false && !isset( $prev_row['_subtotal'] )
									&& isset( $prev_row[$column] ) && isset( $row[$column] ) && !is_float( $row[$column] ) && $prev_row[$column] === $row[$column]
									&& $prev_row[$column] !== $this->config['other']['blank_value_placeholder']
									&& ( isset( $static_column_options[$column] ) && ( ( isset( $sort_by_columns[$column] ) && !isset( $this->duplicate_value_ignored_columns[$column] ) ) || isset( $group_by_columns[$column] ) ) ) ) {
								//This needs to be a space otherwise cell background colors won't be shown.
								$value = ( $this->config['other']['duplicate_value_placeholder'] != '' ) ? $this->config['other']['duplicate_value_placeholder'] : ' ';
							}
						}

						if ( $this->config['other']['show_blank_values'] == true && $value == '' ) {
							//Update $row[$column] so the blank value gets put into the prev_row variable so we can check for it in the next loop.
							$value = $row[$column] = $this->config['other']['blank_value_placeholder'];
						}

						if ( !isset( $row['_total'] ) && $blank_row == true && $value == '' ) {
							$this->pdf->setX( ( $this->pdf->getX() + $cell_width ) );
						} else {
							$blank_row = false;

							//Row formatting...
							if ( isset( $row['_fontcolor'] ) && is_array( $row['_fontcolor'] ) ) {
								$this->pdf->setTextColor( $row['_fontcolor'][0], $row['_fontcolor'][1], $row['_fontcolor'][2] );
							} else {
								$this->pdf->setTextColor( 0 );
							}
							if ( isset( $row['_drawcolor'] ) && is_array( $row['_drawcolor'] ) ) {
								$this->pdf->setDrawColor( $row['_drawcolor'][0], $row['_drawcolor'][1], $row['_drawcolor'][2] );
							} else {
								$this->pdf->setDrawColor( 0 );
							}
							if ( isset( $row['_bgcolor'] ) && is_array( $row['_bgcolor'] ) ) {
								$this->pdf->setFillColor( $row['_bgcolor'][0], $row['_bgcolor'][1], $row['_bgcolor'][2] );
							}
							if ( isset( $row['_border'] ) ) {
								$border = $row['_border'];
							} else {
								$border = $row_layout['border'];
							}


							if ( is_object( $value ) ) {
								$this->profiler->startTimer( 'Draw Cell Object' );
								$cell_obj_start_x = $this->pdf->getX();
								$value->display( 'pdf', $cell_width, $row_cell_height, $r );
								$this->pdf->setX( ( $cell_obj_start_x + $cell_width ) ); //Make sure we always make the cell the proper width.
								unset( $cell_obj_start_x );
								$this->profiler->stopTimer( 'Draw Cell Object' );
							} else {
								$this->profiler->startTimer( 'Draw Cell' );
								//MultiCell() is significantly slower than Cell(), so only use MultiCell when the height is more than one row.
								if ( $row_cell_height > $this->_pdf_fontSize( $row_layout['height'] ) ) {
									//MultiCell should wrap the text automatically, so no need to call wordwrap() directly.
									//$this->pdf->MultiCell( $cell_width, $row_cell_height, wordwrap('This is some really long text to test wrapping. '.$value, $this->config['other']['table_data_word_wrap']), $border, $row_layout['align'], $row_layout['fill'], 0, '', '', TRUE, $row_layout['stretch'], FALSE, TRUE, 0, 'T', TRUE );
									$this->pdf->MultiCell( $cell_width, $row_cell_height, $value, $border, $row_layout['align'], $row_layout['fill'], 0, '', '', true, $row_layout['stretch'], false, true, 0, 'T', false );
								} else {
									$this->pdf->Cell( $cell_width, $this->_pdf_fontSize( $row_layout['height'] ), $value, $border, 0, $row_layout['align'], $row_layout['fill'], '', $row_layout['stretch'] );
								}
								$this->profiler->stopTimer( 'Draw Cell' );
							}
						}

						$c++;
					}
					unset( $tmp ); //code standards

				}

				//UnBold after sub-total rows, but NOT grand total row.
				if ( ( isset( $row['_subtotal'] ) && $row['_subtotal'] == true ) ) {
					$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( $this->config['other']['table_row_font_size'] ) );
					$this->pdf->Ln( 8 );
				}

				if ( $blank_row == true ) {
					$this->pdf->Ln( 0 );
				} else {
					$this->pdf->Ln();
					$r++;
				}

				if ( !isset( $row['_total'] ) && !isset( $row['_subtotal'] ) ) {
					$total_rows++;
					//Increment all sub_total rows for each group_by column.
					for ( $n = 0; $n <= $sub_total_columns_count; $n++ ) {
						$sub_total_rows[$n]++;
					}
				} else if ( isset( $row['_subtotal'] ) ) {
					//Clear only the sub_total row counter that we are displaying currently.
					$sub_total_rows[$total_row_sub_total_columns] = 0;
				}

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );

				$prev_row = $row;
			}

			if ( $this->_pdf_checkMaximumPageLimit() == true ) {
				$this->_pdf_drawLine( 1 );
			}
		} else {
			Debug::Text( 'No data or columns to display...', __FILE__, __LINE__, __METHOD__, 10 );
			if ( !is_array( $columns ) || count( $columns ) == 0 ) {
				$error_msg = TTi18n::getText( 'NO DISPLAY COLUMNS SELECTED' );
			} else if ( !is_array( $this->data ) || count( $this->data ) == 0 ) {
				$error_msg = TTi18n::getText( 'NO DATA MATCHES CRITERIA' );
			} else {
				$error_msg = TTi18n::getText( 'UNABLE TO DISPLAY REPORT' );
			}

			$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 16 ) );
			$this->pdf->Cell( $this->pdf->getPageWidth(), 20, '[' . $error_msg . ']', 0, 0, 'C', 0, '', 0 );
			unset( $error_msg );
		}

		$this->profiler->stopTimer( 'PDF Table' );

		return true;
	}

	/**
	 * @return bool
	 */
	function downloadOutput() {
		/*
		Misc::FileDownloadHeader('report.pdf', 'application/pdf', strlen($output));
		echo $output;
		Debug::writeToLog();
		exit;
		*/
		return true;
	}

	/**
	 * @return bool
	 */
	function emailOutput() {
		return true;
	}

	/**
	 * @param $columns
	 * @return bool
	 */
	function setCustomColumnConfig( $columns ) {
		if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
			$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
			$rcclf->getByCompanyId( $this->getUserObject()->getCompany() );
			$columns_data = [];
			if ( $rcclf->getRecordCount() > 0 ) {
				$row = [];
				foreach ( $rcclf as $rccf ) {
					$column = 'custom_column:' . $rccf->getId();
					if ( in_array( $column, $columns ) ) {
						$row['variable_name'] = $column;
						$row['label'] = $rccf->getName();
						$row['type'] = $rccf->getType();
						$row['format'] = $rccf->getFormat();
						switch ( $row['type'] ) {
							case 10:
								$row['definition'] = [
										'include_columns' => $rccf->getIncludeColumns(),
										'exclude_columns' => $rccf->getExcludeColumns(),
								];
								break;
							case 20:
							case 21:
							case 30:
							case 31:
								$row['definition'] = $rccf->getFormula();
								$columns_data = array_merge( $columns_data, (array)TTMath::parseColumnsFromFormula( $row['definition'] ) );
								break;
						}

						$this->config['custom_column'][] = $row;
					}
				}
			}

			$this->setColumnDataConfig( $columns_data );
		}

		return true;
	}

	/**
	 * @return bool|mixed
	 */
	function getCustomColumnConfig() {
		if ( isset( $this->config['custom_column'] ) ) {
			return $this->config['custom_column'];
		}

		return false;
	}

	/**
	 * @param int $type_id
	 * @return bool
	 */
	function calculateCustomColumns( $type_id ) {
		if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
			$this->profiler->startTimer( 'calculateCustomColumns' );
			ReportCustomColumnFactory::calculateCustomColumns( $this, $type_id );
			$this->profiler->stopTimer( 'calculateCustomColumns' );
		}

		return true;
	}

	/**
	 * @param int $type_id
	 * @return bool
	 */
	function calculateCustomColumnFilters( $type_id ) {
		if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
			$this->profiler->startTimer( 'calculateCustomColumnFilters' );
			ReportCustomColumnFactory::calculateCustomColumnFilters( $this, $type_id );
			$this->profiler->stopTimer( 'calculateCustomColumnFilters' );
		}

		return true;
	}
}

/**
 * Class ReportPDF
 */
class ReportPDF extends Report {

	/**
	 * @return bool
	 */
	function header() {
		return true;
	}

	/**
	 * @return bool
	 */
	function footer() {
		return true;
	}

}

/**
 * Class ReportCell
 */
class ReportCell {
	public $report_obj = null;

	public $value = null;

	/**
	 * @return string
	 */
	function __toString() {
		return (string)$this->value;
	}

	/**
	 * @return int
	 */
	function getColumnWidth() {
		return 0;
	}

	/**
	 * @return int
	 */
	function getColumnHeight() {
		return 0;
	}
}

/**
 * Class ReportCellImage
 */
class ReportCellImage extends ReportCell {
	public $style = null;

	/**
	 * ReportCellImage constructor.
	 * @param object $report_obj
	 * @param string|array $image_file_name
	 * @param bool $style
	 */
	function __construct( $report_obj, $image_file_name, $style = false ) {
		$this->report_obj = $report_obj;
		$this->image_file_name = $image_file_name;
	}

	/**
	 * @return mixed
	 */
	function getColumnHeight() {
		return $this->report_obj->_pdf_scaleSize( 10 );
	}

	/**
	 * @return mixed
	 */
	function getColumnWidth() {
		return $this->report_obj->_pdf_scaleSize( 50 );
	}

	/**
	 * @return string
	 */
	function __toString() {
		return '<PHOTO>';
	}

	/**
	 * @param $format
	 * @param string $max_width UUID
	 * @param $max_height
	 * @param int $row_i
	 * @return string
	 */
	function display( $format, $max_width, $max_height, $row_i = 0 ) {
		$width = $max_width;
		$height = $max_height; //Make height the same as width.

		//Make sure we don't stretch the image as it makes it difficult to read.
		if ( $width > $height ) {
			$width = $height;
		}
		if ( $height > $width ) {
			$height = $width;
		}

		//Images must be passed in as an array containing both a local file and URL, so they work with PDFs and HTML reports.
		if ( is_array( $this->image_file_name ) ) {
			if ( $format == 'pdf' ) {
				$this->image_file_name = $this->image_file_name['local_file'];
			} else if ( $format == 'html' ) {
				$this->image_file_name = $this->image_file_name['url'];
			}
		}

		if ( $format == 'pdf' ) {
			return $this->report_obj->pdf->Image( $this->image_file_name, ( $this->report_obj->pdf->getX() + $max_width - $width ), '', $width, $height, '', '', 'T', false, 300, '', false, false, 0, true );
		} else {
			if ( $format == 'html' ) {
				if ( strpos( $this->image_file_name, 'http') !== false || strpos( $this->image_file_name, '?') !== false ) { //URL passed in.
					$image_html = '<img style="max-width: 150px; max-height: 150px;" src="' . $this->image_file_name . '&X-CSRF-Token=' . ( ( isset( $_COOKIE['CSRF-Token'] ) ) ? $_COOKIE['CSRF-Token'] : null ) . '">';
				} else if ( $this->image_file_name != '' && file_exists( $this->image_file_name ) ) { //Local file name passed in.
					$image_html = '<img style="width: 100%; max-width: ' . $this->report_obj->_pdf_unitsToPixels( $width ) . ';" src="data:image/x-icon;base64,' . base64_encode( file_get_contents( $this->image_file_name ) ) . '">';
				}


				return $image_html;
			}
		}

		return null;
	}
}

/**
 * Class ReportCellBarcode
 */
class ReportCellBarcode extends ReportCell {
	public $style = null;

	/**
	 * ReportCellBarcode constructor.
	 * @param object $report_obj
	 * @param $value
	 * @param bool $style
	 */
	function __construct( $report_obj, $value, $style = false ) {
		$this->report_obj = $report_obj;
		$this->value = $value;
		//$this->style = $style;
	}

	/**
	 * @return mixed
	 */
	function getColumnHeight() {
		return $this->report_obj->_pdf_scaleSize( 10 );
	}

	/**
	 * @return mixed
	 */
	function getColumnWidth() {
		return $this->report_obj->_pdf_scaleSize( 50 );
	}

	/**
	 * @param $format
	 * @param string $max_width UUID
	 * @param $max_height
	 * @param int $row_i
	 * @return string
	 */
	function display( $format, $max_width, $max_height, $row_i = 0 ) {
		if ( $format == 'pdf' ) {
			$style = [
				//'position' => '',
				//'align' => 'R', //This is based on the entire page.
				'stretch'  => true,
				//'fitwidth' => FALSE,
				//'cellfitalign' => '',
				//'border' => TRUE,
				'hpadding' => 2,
				'vpadding' => 2,
				//'fgcolor' => array(0, 0, 0),
				//'bgcolor' => FALSE, //array(255, 255, 255),
				//'text' => TRUE, //Text below the barcode.
				//'font' => 'helvetica',
				//'fontsize' => 8,
				//'stretchtext' => 4
			];

			return $this->report_obj->pdf->write1DBarcode( $this->value, 'C128A', $this->report_obj->pdf->getX(), '', $max_width, $max_height, '', $style, 'T' );
		} else {
			if ( $format == 'html' ) {
				require_once( Environment::getBasePath() . 'classes/tcpdf' . '/tcpdf_barcodes_1d.php' );
				$barcode_obj = new TCPDFBarcode( $this->value, 'C128A' );
				$barcode_html = '<img width=' . $this->report_obj->_pdf_unitsToPixels( $max_width ) . ' height=' . $this->report_obj->_pdf_unitsToPixels( $max_height ) . ' src="data:image/x-icon;base64,' . base64_encode( $barcode_obj->getBarcodePngData( $max_width, $max_height ) ) . '">';

				return $barcode_html;
			}
		}

		return null;
	}
}

/**
 * Class ReportCellQRcode
 */
class ReportCellQRcode extends ReportCell {
	public $report_obj = null;

	public $value = null;
	public $style = null;

	/**
	 * ReportCellQRcode constructor.
	 * @param object $report_obj
	 * @param $value
	 * @param bool $style
	 */
	function __construct( $report_obj, $value, $style = false ) {
		$this->report_obj = $report_obj;
		$this->value = $value;
		//$this->style = $style;
	}

	/**
	 * @return mixed
	 */
	function getColumnHeight() {
		return $this->report_obj->_pdf_scaleSize( 25 );
	}

	/**
	 * @return mixed
	 */
	function getColumnWidth() {
		return $this->report_obj->_pdf_scaleSize( 25 );
	}

	/**
	 * @param $format
	 * @param string $max_width UUID
	 * @param $max_height
	 * @param int $row_i
	 * @return string
	 */
	function display( $format, $max_width, $max_height, $row_i = 0 ) {
		$width = $max_width;
		$height = $max_height;

		//Make sure we don't stretch the QRcode as it makes it difficult to read.
		if ( $width > $height ) {
			$width = $height;
		}
		if ( $height > $width ) {
			$height = $width;
		}

		if ( $format == 'pdf' ) {
			if ( ( $row_i % 2 ) == 0 ) {
				$bgcolor = 255;
			} else {
				$bgcolor = 250;
			}

			$style = [
					'vpadding' => 3,
					'hpadding' => ( ( ( $max_width - $width ) / 2 ) - 3 ),
					//'position' => 'R', //This is based on the entire page.
					'bgcolor'  => $bgcolor,
			];

			//Debug::Arr($style, 'X: '. $this->report_obj->pdf->getX() .' Width: '. $width .' Height: '. $height .' Max Width: '. $max_width, __FILE__, __LINE__, __METHOD__, 10);
			return $this->report_obj->pdf->write2DBarcode( $this->value, 'QRCODE, H', $this->report_obj->pdf->getX(), '', $max_width, $max_height, $style, 'T', true );
		} else {
			if ( $format == 'html' ) {
				require_once( Environment::getBasePath() . 'classes/tcpdf' . '/tcpdf_barcodes_2d.php' );
				$barcode_obj = new TCPDF2DBarcode( $this->value, 'QRCODE' );
				$barcode_html = '<img width=' . $this->report_obj->_pdf_unitsToPixels( $width ) . ' height=' . $this->report_obj->_pdf_unitsToPixels( $height ) . ' src="data:image/x-icon;base64,' . base64_encode( $barcode_obj->getBarcodePngData( $width, $height ) ) . '">';

				return $barcode_html;
			}
		}

		return null;
	}
}

//For advanced reports that require cell background colors, borders, formatting etc...
//Use objects for the cell data, which can then be checked and handled separately.
//Make them all static objects for faster access?
//Need to be able to overload specific formatting classes that will only ever be used by one report.
/*
ReportFormatter
	ReportTable (ReportObjectTableFormatter)
		ReportTableHeader
		ReportTableFooter
	ReportColumn (ReportObjectColumnFormatter)
	ReportRow (ReportObjectRowFormatter)
	ReportCell (ReportObjectCellFormatter)
		ReportCell<Type> (ie: ReportCellCurrency, ReportCellPercent, ReportCellNumeric, ReportCellMyCustomType)


//I think all the objects need to be seperate from one another, so we can pass objects around efficiently, then the main processor can handle inheritance that way.
//This might still be too heavy weight for what we need.
$report = new ReportFormatter();
$table = $report->addTable();
	$table->addColumn();
	$table->addColumn();
$row = $table->addRow();
	$row->addCell()
	$row->addCell()
	$row->addCell()

*/
?>
