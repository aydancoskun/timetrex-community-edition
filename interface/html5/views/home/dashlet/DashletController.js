DashletController = Backbone.View.extend( {

	data: null,
	api_dashboard: null,
	api_user_report: null,
	user_generic_data_api: null,
	all_columns: null,
	grid: null,
	dashboard_data: null,
	refresh_timer: null,
	iframe_data: null,
	initComplete: false,
	iframe: null,
	homeViewController: null,
	initTimesheetGridComplete: null,
	accumulated_total_grid_source_map: null,
	accmulated_order_map: null,
	report_apis: {
		AccrualBalanceSummaryReport: 'APIAccrualBalanceSummaryReport',
		AuditTrailReport: 'APIAuditTrailReport',
		UserSummaryReport: 'APIUserSummaryReport',
		ExceptionReport: 'APIExceptionSummaryReport',
		UserExpenseReport: 'APIUserExpenseReport',
		PayStubSummaryReport: 'APIPayStubSummaryReport',
		PunchSummaryReport: 'APIPunchSummaryReport',
		UserQualificationReport: 'APIUserQualificationReport',
		UserRecruitmentDetailReport: 'APIUserRecruitmentDetailReport',
		UserRecruitmentSummaryReport: 'APIUserRecruitmentSummaryReport',
		KPIReport: 'APIKPIReport',
		ScheduleSummaryReport: 'APIScheduleSummaryReport',
		TimeSheetDetailReport: 'APITimesheetDetailReport',
		TimeSheetSummaryReport: 'APITimesheetSummaryReport',
		ActiveShiftReport: 'APIActiveShiftReport'
	},

	initialize: function() {
		this.api_dashboard = new (APIFactory.getAPIClass( 'APIDashboard' ))();
		this.user_generic_data_api = new (APIFactory.getAPIClass( 'APIUserGenericData' ))();
	},

	refreshIfNecessary: function() {
		if ( this.data && this.data.data.dashlet_type == 'custom_report' || this.data.data.dashlet_type === 'news' ) {
			if ( this.iframe_data ) {
				this.addIframeBack();
				this.setIframeData();
			}
		}
	},

	initContent: function() {
		var $this = this;
		this.setTitle();
		this.initComplete = false;
		if ( Global.isScrolledIntoView( $( $this.el ) ) ) {
			doInit();
		}
		// remove first
		interact( '#' + $( this.el ).attr( 'id' ) ).unset();

		//BUG#2070 - Break resizable for mobile because it negatively impacts usability
		if ( Global.detectMobileBrowser() == false ) {
			interact('#' + $(this.el).attr('id'))
				.resizable({
					edges: {left: true, right: true, bottom: true, top: true}
				})
				.on('resizestart', function (event) {

				})
				.on('resizemove', function (event) {

					var target = event.target;
					var xUnit = parseInt(event.rect.width / 20);
					var yUnit = parseInt(event.rect.height / 20);
					var left = parseFloat($(target).css('left'));
					var top = parseFloat($(target).css('top'));
					if (xUnit > 1) {
						var percentage_width = ((xUnit * 20) / $this.homeViewController.dashboard_container.width() * 100);
						if (Math.abs(percentage_width - 33) < 2) {
							percentage_width = 33;
						}
						var currentWidth = $(target).width();
						target.style.width = percentage_width + '%';
						var newWidth = $(target).width();
						var widthChanged = newWidth - currentWidth;
						if (event.edges.left) {
							$(target).css('left', left - widthChanged);
						}
					}
					if (yUnit > 1) {
						var height = (yUnit * 20);
						var currentHeight = $(target).height();
						target.style.height = height + 'px';
						var newHeight = $(target).height();
						var heightChanged = newHeight - currentHeight;
						if (event.edges.top) {
							$(target).css('top', top - heightChanged);
						}
					}
					$this.setGridSize();
				})
				.on('resizeend', function (event) {
					$this.addIframeBack();
					$this.homeViewController.updateLayout();
					$this.saveSize();
				});
		}

		this.homeViewController.dashboard_container.parent().scroll( function() {
			if ( Global.isScrolledIntoView( $( $this.el ) ) && !$this.initComplete ) {
				doInit();
			}
		} );

		function doInit() {
			if ( $this.data.data.dashlet_type == 'custom_list' ) {
				$( $this.el ).addClass( 'custom_list' );
				$this.initCustomViewContent();
			} else if ( $this.data.data.dashlet_type == 'custom_report' ) {
				$( $this.el ).addClass( 'custom-report' );
				$this.initReportContent();
				$( $this.el ).unbind( 'mousedown' ).bind( 'mousedown', function() {
					$this.removeIframe();
				} );
				$( $this.el ).unbind( 'mouseup' ).bind( 'mouseup', function() {
					$this.addIframeBack();

				} );
			} else if ( $this.data.data.dashlet_type == 'request_summary' ) {
				$( $this.el ).addClass( 'request-summary' );
				$this.initDefaultDashletContent( 'request_summary' );
			} else if ( $this.data.data.dashlet_type == 'request_authorize_summary' ) {
				$( $this.el ).addClass( 'request-authorize-summary' );
				$this.initDefaultDashletContent( 'request_authorize_summary' );
			} else if ( $this.data.data.dashlet_type == 'accrual_balance_summary' ) {
				$( $this.el ).addClass( 'accrual-balance-summary' );
				$this.initDefaultDashletContent( 'accrual_balance_summary' );
			} else if ( $this.data.data.dashlet_type == 'exception_summary' ) {
				$( $this.el ).addClass( 'exception-summary' );
				$this.initDefaultDashletContent( 'exception_summary' );
			} else if ( $this.data.data.dashlet_type == 'exception_summary_child' ) {
				$( $this.el ).addClass( 'exception-summary-child' );
				$this.initDefaultDashletContent( 'exception_summary_child' );
			} else if ( $this.data.data.dashlet_type == 'message_summary' ) {
				$( $this.el ).addClass( 'message-summary' );
				$this.initDefaultDashletContent( 'message_summary' );
			} else if ( $this.data.data.dashlet_type == 'user_active_shift_summary' ) {
				$( $this.el ).addClass( 'user-active-shift-summary' );
				$this.initDefaultDashletContent( 'user_active_shift_summary' );
			} else if ( $this.data.data.dashlet_type == 'timesheet_verification_summary' ) {
				$( $this.el ).addClass( 'timesheet-verification-summary' );
				$this.initDefaultDashletContent( 'timesheet_verification_summary' );
			} else if ( $this.data.data.dashlet_type == 'timesheet_verification_summary_child' ) {
				$( $this.el ).addClass( 'timesheet-verification_summary-child' );
				$this.initDefaultDashletContent( 'timesheet_verification_summary_child' );
			} else if ( $this.data.data.dashlet_type == 'timesheet_summary' ) {
				$( $this.el ).addClass( 'timesheet-summary' );
				$this.initTimesheetContent( 'timesheet_summary' );
			} else if ( $this.data.data.dashlet_type == 'schedule_summary' ) {
				$( $this.el ).addClass( 'schedule-summary' );
				$this.initDefaultDashletContent( 'schedule_summary' );
			} else if ( $this.data.data.dashlet_type == 'schedule_summary_child' ) {
				$( $this.el ).addClass( 'schedule-summary-child' );
				$this.initDefaultDashletContent( 'schedule_summary_child' );
			} else if ( $this.data.data.dashlet_type == 'news' ) {
				$( $this.el ).addClass( 'news' );
				$this.initNewsContent();
				$( $this.el ).unbind( 'mousedown' ).bind( 'mousedown', function() {
					$this.removeIframe();
				} );
				$( $this.el ).unbind( 'mouseup' ).bind( 'mouseup', function() {
					$this.addIframeBack();

				} );
			}
			$this.initComplete = true;
		}//if not android
	},

	onViewClick: function() {
		var target_view = '';
		var open_reoprt = false;
		if ( this.data.data.dashlet_type == 'custom_list' ) {
			target_view = this.data.data.view_name;
			if ( target_view === 'Request-Authorization' ) {
				target_view = 'RequestAuthorization'
			} else if ( target_view === 'PayPeriodTimeSheetVerify' ) {
				target_view = 'TimeSheetAuthorization'
			} else if ( target_view === 'UserExpense-Authorization' ) {
				target_view = 'ExpenseAuthorization'
			} else if ( target_view === 'User' ) {
				target_view = 'Employee'
			} else if ( target_view === 'Transaction' ) {
				target_view = 'InvoiceTransaction'
			} else if ( target_view === 'UserWage' ) {
				target_view = 'Wage'
			}
		} else {
			switch ( this.data.data.dashlet_type ) {
				case 'message_summary':
					target_view = 'MessageControl';
					break;
				case 'exception_summary_child':
				case 'exception_summary':
					target_view = 'Exception';
					break;
				case 'schedule_summary':
				case 'schedule_summary_child':
					target_view = 'Schedule';
					break;
				case 'request_summary':
					target_view = 'Request';
					break;
				case 'timesheet_verification_summary':
					open_reoprt = true;
					target_view = 'TimesheetSummaryReport';
					break;
				case 'timesheet_verification_summary_child':
					open_reoprt = true;
					target_view = 'TimesheetSummaryReport';
					break;
				case 'timesheet_summary':
					target_view = 'TimeSheet';
					break;
				case 'user_active_shift_summary':
					open_reoprt = true;
					target_view = 'ActiveShiftReport';
					break;
				case 'request_authorize_summary':
					target_view = 'RequestAuthorization';
					break;
				case 'accrual_balance_summary':
					target_view = 'AccrualBalance';
					break;
			}
		}
		if ( open_reoprt ) {
			IndexViewController.openReport( this.homeViewController, target_view );
		} else {
			IndexViewController.goToView( target_view );
		}

	},

	onCellFormat: function( cell_value, related_data, row ) {
		cell_value = Global.decodeCellValue( cell_value );
		var col_model = related_data.colModel;
		var row_id = related_data.rowid;
		var content_div = $( "<div class='punch-content-div'></div>" );
		var punch_info;
		if ( related_data.pos === 0 ) {
			if ( row.type === DashletController.TOTAL_ROW ) {
				punch_info = $( "<span class='total' style='font-size: 11px'></span>" );
				if ( Global.isSet( cell_value ) ) {
					punch_info.text( cell_value );
				} else {
					punch_info.text( '' );
				}
				return punch_info.get( 0 ).outerHTML;
			} else if ( row.type === DashletController.REGULAR_ROW ) {

				punch_info = $( "<span class='top-line-span' style='font-size: 11px'></span>" );
				if ( Global.isSet( cell_value ) ) {
					punch_info.text( cell_value );
				} else {
					punch_info.text( '' );
				}
				return punch_info.get( 0 ).outerHTML;
			}
			return cell_value;
		}
		var ex_span;
		var i;
		var time_span;
		var punch;
		var break_span;
		var related_punch;
		var exception;
		var len;
		var text;
		var ex;
		var data;
		if ( row.type === DashletController.TOTAL_ROW ) {
			data = row[col_model.name + '_data'];
			time_span = $( "<span class='total'></span>" );
			if ( Global.isSet( cell_value ) ) {
				if ( data ) {
					if ( data.hasOwnProperty( 'override' ) && data.override === true ) {
						time_span.addClass( 'absence-override' );
					}
					if ( data.hasOwnProperty( 'note' ) && data.note ) {
						cell_value = '*' + cell_value;
					}
				}
				time_span.text( cell_value );

			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );
		} else if ( row.type === DashletController.REGULAR_ROW ) {
			content_div.addClass( 'top-line' );
			data = row[col_model.name + '_data'];
			time_span = $( "<span ></span>" );
			if ( Global.isSet( cell_value ) ) {
				if ( data ) {
					if ( data.hasOwnProperty( 'override' ) && data.override === true ) {
						time_span.addClass( 'absence-override' );
					}
					if ( data.hasOwnProperty( 'note' ) && data.note ) {
						cell_value = '*' + cell_value;
					}
				}
				time_span.text( cell_value );
			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );
		} else if ( row.type === DashletController.ACCUMULATED_TIME_ROW ) {
			data = row[col_model.name + '_data'];
			time_span = $( "<span></span>" );
			if ( Global.isSet( cell_value ) ) {
				if ( data ) {
					if ( data.hasOwnProperty( 'override' ) && data.override === true ) {
						time_span.addClass( 'absence-override' );
					}
					if ( data.hasOwnProperty( 'note' ) && data.note ) {
						cell_value = '*' + cell_value;
					}
				}
				time_span.text( cell_value );
			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );
		} else {
			time_span = $( "<span class='punch-time'></span>" );
			if ( Global.isSet( cell_value ) ) {
				time_span.text( cell_value );
			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );
		}
		return content_div.get( 0 ).outerHTML;
	},

	initNewsContent: function() {
		var $this = this;
		$( this.el ).find( '#grid' ).remove();
		$( this.el ).find( '.view-btn' ).remove();
		$( this.el ).find( '.refresh-btn' ).unbind( 'click' ).bind( 'click', function( e ) {
			$( e.target ).addClass( 'button-rotate' );
			$this.initNewsData();
		} );
		this.initNewsData();
		this.startRefresh();
	},

	initNewsData: function() {
		var $this = this;
		this.api_dashboard.getDashletData( this.data.data.dashlet_type, {}, {
			onResult: function( result ) {
				var result = result.getResult();
				$this.iframe_data = result;
				if ( result ) {
					$this.removeNoResultCover();

					$this.setIframeData();
				} else {
					$this.showNoResultCover();
				}
				$( '.button-rotate' ).removeClass( 'button-rotate' );
			}
		} );
	},

	//Error: Uncaught TypeError: Cannot read property 'contentDocument' of undefined in/interface/html5/#!m=MessageControl line 359
	setIframeData: function( iframe_data ) {
		if ( $( this.el ).find( '#iframe' ).length > 0 ) {
			$( this.el ).find( '#iframe' )[0].contentDocument.open();
			$( this.el ).find( '#iframe' )[0].contentDocument.writeln( this.iframe_data );
			$( this.el ).find( '#iframe' )[0].contentDocument.close();
		}
	},

	initTimesheetContent: function( view_name ) {
		$( this.el ).find( '#iframe' ).remove();
		var $this = this;
		this.api = this.getAPIByViewName( view_name );
		// set grid id
		if ( !Global.isSet( this.grid ) ) {
			var grid = $( this.el ).find( '#grid' );
			grid.attr( 'id', 'dashlet_' + this.data.id + '_grid' );  //Grid's id is ScriptName + _grid
		}
		// refresh event
		$( this.el ).find( '.refresh-btn' ).unbind( 'click' ).bind( 'click', function( e ) {
			$( e.target ).addClass( 'button-rotate' );
			$this.initDefaultTimesheetData();
		} );
		// view event
		$( this.el ).find( '.view-btn' ).unbind( 'click' ).bind( 'click', function( e ) {
			$this.onViewClick();
		} );

		// start load grid data
		this.initDefaultTimesheetData();
		this.startRefresh();

	},

	initDefaultTimesheetData: function() {
		var $this = this;
		this.accumulated_total_grid_source_map = {};
		this.api.getTimeSheetData( LocalCacheData.getLoginUser().id, new Date().format(), {
			onResult: function( result ) {
				//Error: Uncaught TypeError: Cannot read property 'start_display_date' of undefined in /interface/html5/#!m=Home line 409
				if ( result.isValid() ) {
					$this.full_timesheet_data = result.getResult();
					$this.pay_period_data = $this.full_timesheet_data.pay_period_data;
					$this.timesheet_verify_data = $this.full_timesheet_data.timesheet_verify_data;
					if ( $this.full_timesheet_data.timesheet_dates ) {
						$this.start_date = Global.strToDate( $this.full_timesheet_data.timesheet_dates.start_display_date );
						$this.end_date = Global.strToDate( $this.full_timesheet_data.timesheet_dates.end_display_date );
						// Error: Uncaught TypeError: Cannot read property 'format' of null in interface/html5/#!m=Home line 607
						if ( !$this.initTimesheetGridComplete && $this.start_date && $this.end_date ) {
							$this.buildAccumulatedTotalGrid();
							$this.initTimesheetGridComplete = true;
						}
						$this.buildAccumulatedTotalData();
					}
					$( '.button-rotate' ).removeClass( 'button-rotate' );
				} else {
					$( '.button-rotate' ).removeClass( 'button-rotate' );
				}

			}
		} );
	},

	buildAccmulatedOrderMap: function( total ) {
		if ( !total ) {
			return;
		}
		for ( var key in total ) {
			for ( var key1 in total[key] ) {
				this.accmulated_order_map[key1] = total[key][key1].order;
			}
		}
	},

	buildAccumulatedTotalData: function() {
		// There will be no grid when no start date and end date when calling getTimeSheetData
		if(!this.grid) return;
		this.accmulated_order_map = {};
		this.accumulated_total_grid_source = [];
		var accumulated_user_date_total_data = this.full_timesheet_data.accumulated_user_date_total_data;
		var pay_period_accumulated_user_date_total_data = this.full_timesheet_data.pay_period_accumulated_user_date_total_data;
		var accumulated_time = pay_period_accumulated_user_date_total_data.accumulated_time;
		var premium_time = pay_period_accumulated_user_date_total_data.premium_time;
		var absence_time = pay_period_accumulated_user_date_total_data.absence_time_taken;
		// Save the order, will do sort after all data prepared.
		if ( accumulated_user_date_total_data.total ) {
			this.buildAccmulatedOrderMap( accumulated_user_date_total_data.total );
		}
		if ( pay_period_accumulated_user_date_total_data ) {
			this.buildAccmulatedOrderMap( pay_period_accumulated_user_date_total_data );
		}
		if ( Global.isSet( accumulated_time ) ) {
			this.buildSubGridsData( accumulated_time, 'pay_period', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'accumulated_time' );
		} else {
			accumulated_time = {total: {label: 'Total Time', total_time: '0'}};
			this.buildSubGridsData( accumulated_time, 'pay_period', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'accumulated_time' );
		}
		if ( Global.isSet( premium_time ) ) {
			this.buildSubGridsData( premium_time, 'pay_period', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'premium_time' );
		}
		if ( Global.isSet( absence_time ) ) {
			this.buildSubGridsData( absence_time, 'pay_period', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'absence_time' );
		}
		accumulated_time = {total: {label: 'Total Time', total_time: '0'}};
		this.buildSubGridsData( accumulated_time, 'week', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'accumulated_time' );
		for ( var key in accumulated_user_date_total_data ) {
			//Build Accumulated Total Grid week column data
			if ( key === 'total' ) {
				var total_result = accumulated_user_date_total_data.total;
				accumulated_time = total_result.accumulated_time;
				premium_time = total_result.premium_time;
				absence_time = total_result.absence_time_taken;
				if ( Global.isSet( accumulated_time ) ) {
					this.buildSubGridsData( accumulated_time, 'week', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'accumulated_time' );
				}
				if ( Global.isSet( premium_time ) ) {
					this.buildSubGridsData( premium_time, 'week', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'premium_time' );
				}
				if ( Global.isSet( absence_time ) ) {
					this.buildSubGridsData( absence_time, 'week', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'absence_time' );
				}
				continue;
			}
		}
		this.sortAccumulatedTotalData();
		this.markRegularRow( this.accumulated_total_grid_source );
		this.grid.clearGridData();
		this.grid.setGridParam( {data: this.accumulated_total_grid_source} );
		this.grid.trigger( 'reloadGrid' );
	},

	sortAccumulatedTotalData: function() {
		var sort_fields = ['order', 'punch_info'];
		this.accumulated_total_grid_source.sort( Global.m_sort_by( sort_fields ) );
	},

	markRegularRow: function( source ) {
		var len = source.length;
		for ( var i = 0; i < source.length; i++ ) {
			var row = source[i];
			if ( row.key && row.key.indexOf( 'regular_time' ) === 0 ) {
				row.type = DashletController.REGULAR_ROW;
				return;
			}
		}
	},

	buildSubGridsData: function( array, date_string, map, result_array, parent_key ) {
		var row;
		for ( var key  in array ) {
			if ( !map[key] ) {
				row = {};
				row.parent_key = parent_key;
				row.key = key;
				if ( parent_key === 'accumulated_time' ) {
					row.type = DashletController.TOTAL_ROW;
					if ( array[key].override ) {
						row.is_override_row = true;
					}
				}
				if ( this.accmulated_order_map[key] ) {
					row.order = this.accmulated_order_map[key];
				}
				row.punch_info = array[key].label;
				var key_array = key.split( '_' );
				var no_id = false;
				if ( key_array.length > 1 && key_array[1] == '0' ) {
					no_id = true;
				}
				array[key].key = key;
				row[date_string] = Global.secondToHHMMSS( array[key].total_time );
				row[date_string + '_data'] = array[key];
				//if id == 0, put the row as first row.
				if ( no_id ) {
					result_array.unshift( row );
				} else {
					result_array.push( row );
				}
				map[key] = row;
			} else {
				row = map[key];
				if ( row[date_string] && key === 'total' ) { //Override total cell data since we set all to 00:00 at beginning
					array[key].key = key;
					row[date_string] = Global.secondToHHMMSS( array[key].total_time );
					row[date_string + '_data'] = array[key];
					if ( row.parent_key === 'accumulated_time' ) {
						if ( array[key].override ) {
							row.is_override_row = true;
						}
					}
				} else {
					array[key].key = key;
					row[date_string] = Global.secondToHHMMSS( array[key].total_time );
					row[date_string + '_data'] = array[key];

					if ( row.parent_key === 'accumulated_time' ) {
						if ( array[key].override ) {
							row.is_override_row = true;
						}
					}
				}
			}
		}
	},

	getAccumulatedTotalGridPayperiodHeader: function() {
		this.pay_period_header = $.i18n._( 'No Pay Period' );
		var pay_period_id = this.timesheet_verify_data.pay_period_id;
		if ( pay_period_id && this.pay_period_data ) {
			for ( var key in this.pay_period_data ) {
				var pay_period = this.pay_period_data[key];
				if ( pay_period.id === pay_period_id ) {
					var start_date = Global.strToDate( pay_period.start_date ).format();
					var end_date = Global.strToDate( pay_period.end_date ).format();
					this.pay_period_header = start_date + ' to ' + end_date;
					break;
				}
			}
		}
	},

	buildAccumulatedTotalGrid: function() {
		var $this = this;
		var columns = [];
		var grid;
		if ( !Global.isSet( this.grid ) ) {
			grid = $( this.el ).find( '#' + 'dashlet_' + this.data.id + '_grid' );
		}
		var punch_in_out_column = {
			name: 'punch_info',
			index: 'punch_info',
			label: ' ',
			width: 200,
			sortable: false,
			title: false,
			formatter: this.onCellFormat
		};
		columns.push( punch_in_out_column );
		var start_date_str = this.start_date.format( Global.getLoginUserDateFormat() );
		var end_date_str = this.end_date.format( Global.getLoginUserDateFormat() );
		this.getAccumulatedTotalGridPayperiodHeader();
		var column_1 = {
			name: 'week',
			index: 'week',
			label: $.i18n._( 'Week' ) + '<br>' + start_date_str + ' to ' + end_date_str,
			width: 100,
			sortable: false,
			title: false,
			formatter: this.onCellFormat
		};
		var column_2 = {
			name: 'pay_period',
			index: 'pay_period',
			label: $.i18n._( 'Pay Period' ) + '<br>' + this.pay_period_header,
			width: 100,
			sortable: false,
			title: false,
			formatter: this.onCellFormat
		};
		columns.push( column_1 );
		columns.push( column_2 );
		if ( !this.grid ) {
			this.grid = grid;
			this.grid.jqGrid( {
				altRows: true,
				data: [],
				datatype: 'local',
				sortable: false,
				scrollOffset: 0,
				width: Global.bodyWidth() - 14,
				gridComplete: function() {
					if ( $( this ).jqGrid( 'getGridParam', 'data' ).length > 0 ) {
						$this.setGridColumnsWidth();
					}
				},
				rowNum: 10000,
				colNames: [],
				colModel: columns,
				viewrecords: true

			} );
		} else {
			this.grid.jqGrid( 'GridUnload' );
			this.grid = null;
			grid = $( this.el ).find( '#' + 'dashlet_' + this.data.id + '_grid' );
			this.grid = $( grid );
			this.grid.jqGrid( {
				altRows: true,
				data: [],
				rowNum: 10000,
				sortable: false,
				scrollOffset: 0,
				datatype: 'local',
				width: Global.bodyWidth() - 14,
				colNames: [],
				colModel: columns,
				viewrecords: true
			} );
		}
		$this.setGridSize();
	},

	initDefaultDashletContent: function( view_name ) {
		$( this.el ).find( '#iframe' ).remove();
		var $this = this;
		this.api = this.getAPIByViewName( view_name );
		// when auto resize
		$( window ).resize( function() {
			if ( $this.grid ) {
				$this.setGridSize();
			}
		} );
		// set grid id
		if ( !Global.isSet( this.grid ) ) {
			var grid = $( this.el ).find( '#grid' );
			grid.attr( 'id', 'dashlet_' + this.data.id + '_grid' );  //Grid's id is ScriptName + _grid
		}
		// refresh event
		$( this.el ).find( '.refresh-btn' ).unbind( 'click' ).bind( 'click', function( e ) {
			$( e.target ).addClass( 'button-rotate' );
			$this.initDefaultDashletData();
		} );
		// view event
		$( this.el ).find( '.view-btn' ).unbind( 'click' ).bind( 'click', function( e ) {
			$this.onViewClick();
		} );

		// start load grid data
		this.initDefaultDashletData();
		this.startRefresh();
	},

	initDefaultDashletData: function() {
		var $this = this;
		$this.getDefaultDashletData( function() {
			$this.getAllColumns( function() {
				$this.setSelectLayout();
				$this.grid.clearGridData();
				var data = Global.formatGridData( $this.dashboard_data.data, $this.data.data.dashlet_type );
				data = $this.processId( data );
				$this.grid.setGridParam( {data: data} );
				$this.grid.trigger( 'reloadGrid' );
				$( '.button-rotate' ).removeClass( 'button-rotate' );
				if ( !Global.isArray( $this.dashboard_data.data ) || $this.dashboard_data.data.length < 1 ) {
					$this.showNoResultCover();
				} else {
					$this.removeNoResultCover();
				}
				$this.setGridCellBackGround();
			} );
		} );
	},

	processId: function( data ) {
		var start_id = -2;
		// Add a random id to make sure each row has different id when the item don't have id itself (Scheudle summary)
		data = _.map( data, function( item ) {
			if ( item.hasOwnProperty( 'id' ) && !item.id ) {
				item.id = start_id;
			}
			start_id--;
			return item;
		} );

		return data;
	},

	addIframeBack: function() {
		var $this = this;
		if ( $this.iframe ) {
			$( $this.el ).find( '.content' ).append( $this.iframe );
			$this.setIframeData();
			$this.iframe = null;
		}
	},

	removeIframe: function() {
		var $this = this;
		$this.iframe = $( $this.el ).find( '#iframe' );
		$( $this.el ).find( '#iframe' ).remove();
	},

	saveSize: function() {
		var $his = this;
        //Error: Uncaught TypeError: Cannot read property 'style' of undefined in /html5/?desktop=1#!m=Exception line 755
		if(!$( this.el )[0]) return;
		var height = $( this.el )[0].style.height;
		var width = $( this.el )[0].style.width;
		this.data.data.width = width.replace( '%', '' );
		this.data.data.height = height.replace( 'px', '' );
		this.user_generic_data_api.setUserGenericData( this.data, {
			onResult: function( result ) {
			}
		} );

	},

	setGridSize: function() {
		if ( (!this.grid || !this.grid.is( ':visible' )) ) {
			return;
		}
		this.grid.setGridWidth( $( this.el ).width() - 6 );
		var height_offset = $( this.el ).find( '.ui-jqgrid-sortable' ).height() + 43;
		this.grid.setGridHeight( $( this.el ).height() - height_offset );
	},

	initReportContent: function() {
		var $this = this;
		$( this.el ).find( '#grid' ).remove();
		$( this.el ).find( '.view-btn' ).remove();
		$( this.el ).find( '.refresh-btn' ).unbind( 'click' ).bind( 'click', function( e ) {
			$( e.target ).addClass( 'button-rotate' );
			$this.initReportData();
		} );
		this.api_user_report = new (APIFactory.getAPIClass( 'APIUserReportData' ))();
		this.api = this.getAPIByViewName( this.data.data.report );
		this.initReportData();
		this.startRefresh();
	},

	cleanWhenUnloadView: function() {
		if ( this.refresh_timer ) {
			clearInterval( this.refresh_timer );
		}
	},

	initReportData: function() {
		var $this = this;
		if ( $this.data.data.template !== 'saved_report' ) {
			var report_api = new (APIFactory.getAPIClass( $this.report_apis[$this.data.data.report] ))();
			report_api.getTemplate( $this.data.data.template, {
				onResult: function( result ) {
					var config = result.getResult();
					config.other = {
						"page_orientation": "P",
						"font_size": 0,
						"auto_refresh": false,
						"disable_grand_total": false,
						"maximum_page_limit": 100,
						"show_duplicate_values": false,
						is_embedded: true
					};
					$this.api['get' + $this.api.key_name]( config, 'html', {
						onResult: function( res ) {
							var result = res.getResult();
							$this.iframe_data = result;
							if ( result ) {
								$this.setIframeData();
							}
							$( '.button-rotate' ).removeClass( 'button-rotate' );
						}
					} );
				}
			} );
		} else {
			$this.api_user_report.getUserReportData( {
				filter_data: {
					id: $this.data.data.saved_report_id
				}
			}, {
				onResult: function( result ) {
					var result_data = result.getResult();
					if ( result_data && result_data.length == 1 ) {
						if ( result_data[0].data.config.other ) {
							result_data[0].data.config.other.is_embedded = true;
						} else {
							result_data[0].data.config.other = {is_embedded: true};
						}
						$this.api['get' + $this.api.key_name]( result_data[0].data.config, 'html', {
							onResult: function( res ) {
								var result = res.getResult();
								$this.iframe_data = result;
								if ( result ) {
									$this.setIframeData();
								}
								$( '.button-rotate' ).removeClass( 'button-rotate' );
							}
						} );
					}

				}
			} );
		}

	},

	startRefresh: function() {
		var $this = this;
		var auto_refresh = this.data.data.auto_refresh;
		if ( auto_refresh > 0 ) {
			this.refresh_timer = setInterval( function() {
				if ( LocalCacheData.current_open_primary_controller.viewId !== 'Home' ) {
					clearInterval( $this.refresh_timer );
				}
				if ( $this.data.data.dashlet_type == 'custom_list' ) {
					$this.initCustomViewData();
				} else if ( $this.data.data.dashlet_type == 'custom_report' ) {
					$this.initReportData();
				} else if ( $this.data.data.dashlet_type == 'exception_summary' ||
					$this.data.data.dashlet_type == 'request_summary' ||
					$this.data.data.dashlet_type == 'message_summary' ||
					$this.data.data.dashlet_type == 'exception_summary_child' ||
					$this.data.data.dashlet_type == 'request_authorize_summary' ||
					$this.data.data.dashlet_type == 'accrual_balance_summary' ||
					$this.data.data.dashlet_type == 'user_active_shift_summary' ||
					$this.data.data.dashlet_type == 'timesheet_verification_summary' ||
					$this.data.data.dashlet_type == 'timesheet_verification_summary_child' ||
					$this.data.data.dashlet_type == 'schedule_summary' ||
					$this.data.data.dashlet_type == 'schedule_summary_child' ) {
					$this.initDefaultDashletData();
				} else if ( $this.data.data.dashlet_type == 'timesheet_summary' ) {
					$this.initDefaultTimesheetData();
				} else if ( $this.data.data.dashlet_type == 'news' ) {
					$this.initNewsData();
				}

			}, (auto_refresh * 1000) );
		}
	},

	initCustomViewContent: function() {
		$( this.el ).find( '#iframe' ).remove();
		var $this = this;
		this.api = this.getAPIByViewName( this.data.data.view_name );
		// when auto resize
		$( window ).resize( function() {
			if ( $this.grid ) {
				$this.setGridSize();
			}
		} );
		// set grid id
		if ( !Global.isSet( this.grid ) ) {
			var grid = $( this.el ).find( '#grid' );
			grid.attr( 'id', 'dashlet_' + this.data.id + '_grid' );  //Grid's id is ScriptName + _grid
		}
		// refresh event
		$( this.el ).find( '.refresh-btn' ).unbind( 'click' ).bind( 'click', function( e ) {
			$( e.target ).addClass( 'button-rotate' );
			$this.initCustomViewData();
		} );
		// view event
		$( this.el ).find( '.view-btn' ).unbind( 'click' ).bind( 'click', function( e ) {
			$this.onViewClick();
		} );

		// start load grid data
		this.initCustomViewData();
		this.startRefresh();

	},

	setGridCellBackGround: function() {
		var data;
		var len;
		var i;
		var item;
		if ( this.data.data.dashlet_type === 'exception_summary' ||
			this.data.data.dashlet_type === 'exception_summary_child' ) {
			data = this.grid.getGridParam( 'data' );
			//Error: TypeError: data is undefined in /interface/html5/framework/jquery.min.js?v=7.4.6-20141027-074127 line 2 > eval line 70
			if ( !data ) {
				return;
			}
			len = data.length;
			for ( i = 0; i < len; i++ ) {
				item = data[i];
				if ( item.exception_background_color ) {
					var severity = $( this.el ).find( "tr[id='" + item.id + "']" ).find( 'td[aria-describedby="dashlet_' + this.data.id + '_grid_severity"]' );
					severity.css( 'background-color', item.exception_background_color );
					severity.css( 'font-weight', 'bold' );
				}
				if ( item.exception_color ) {
					var code = $( this.el ).find( "tr[id='" + item.id + "']" ).find( 'td[aria-describedby="dashlet_' + this.data.id + '_grid_exception_policy_type_id"]' );
					code.css( 'color', item.exception_color );
					code.css( 'font-weight', 'bold' );
				}
			}
		} else if ( this.data.data.dashlet_type === 'message_summary' ) {
			data = this.grid.getGridParam( 'data' );
			//Error: TypeError: data is undefined in /interface/html5/framework/jquery.min.js?v=7.4.6-20141027-074127 line 2 > eval line 70
			if ( !data ) {
				return;
			}
			len = data.length;
			for ( i = 0; i < len; i++ ) {
				item = data[i];
				if ( item.status_id === 10 ) {
					$( this.el ).find( "tr[id='" + item.id + "'] td" ).css( 'font-weight', 'bold' );
				}
			}
		} else if ( this.data.data.dashlet_type === 'request_summary' ) {
			data = this.grid.getGridParam( 'data' );
			//Error: TypeError: data is undefined in /interface/html5/framework/jquery.min.js?v=7.4.6-20141027-074127 line 2 > eval line 70
			if ( !data ) {
				return;
			}
			len = data.length;
			for ( i = 0; i < len; i++ ) {
				item = data[i];
				if ( item.status_id === 30 ) {
					$( this.el ).find( "tr[id='" + item.id + "']" ).addClass( 'bolder-request' );
				}
			}
		} else if ( this.data.data.dashlet_type === 'user_active_shift_summary' ) {
			data = this.grid.getGridParam( 'data' );
			//Error: TypeError: data is undefined in /interface/html5/framework/jquery.min.js?v=7.4.6-20141027-074127 line 2 > eval line 70
			if ( !data ) {
				return;
			}
			len = data.length;
			for ( i = 0; i < len; i++ ) {
				item = data[i];
				if ( item.status === 'In' ) {
					$( this.el ).find( "tr[id='" + (i + 1) + "']" ).addClass( 'light-green' );
				} else if ( item.status === 'Out' ) {
					$( this.el ).find( "tr[id='" + (i + 1) + "']" ).addClass( 'light-red' );
				}
			}
		} else if ( this.data.data.dashlet_type === 'schedule_summary' ||
			this.data.data.dashlet_type === 'schedule_summary_child' ) {
			data = this.grid.getGridParam( 'data' );
			//Error: TypeError: data is undefined in /interface/html5/framework/jquery.min.js?v=7.4.6-20141027-074127 line 2 > eval line 70
			if ( !data ) {
				return;
			}
			len = data.length;
			for ( i = 0; i < len; i++ ) {
				item = data[i];
				if ( item.status_id === 20 ) {
					$( this.el ).find( "tr[id='" + item.id + "']" ).addClass( 'red-absence' );
				}
			}
		}

	},

	startCustomViewAutoRefresh: function() {
		var $this = this;
		var auto_refresh = this.data.data.auto_refresh;
		if ( auto_refresh > 0 ) {
			this.refresh_timer = setInterval( function() {
				if ( LocalCacheData.current_open_primary_controller.viewId !== 'Home' ) {
					clearInterval( $this.refresh_timer );
				}
				$this.initCustomViewData();
			}, (auto_refresh * 1000) );
		}
	},

	initCustomViewData: function() {
		var $this = this;
		$this.getCustomListDashboardData( function() {
			$this.getAllColumns( function() {
				$this.setSelectLayout();
				$this.grid.clearGridData();
				$this.grid.setGridParam( {data: Global.formatGridData( $this.dashboard_data.data )} );
				$this.grid.trigger( 'reloadGrid' );
				$( '.button-rotate' ).removeClass( 'button-rotate' );
				if ( !Global.isArray( $this.dashboard_data.data ) ) {
					$this.showNoResultCover();
				} else {
					$this.removeNoResultCover();
				}
				$this.setGridCellBackGround();
			} );
		} );
	},

	getCustomListDashboardData: function( callback ) {
		var $this = this;
		if ( !this.data.data.rows_per_page ) {
			this.data.data.rows_per_page = 0;
		}
		this.api_dashboard.getDashletData( this.data.data.dashlet_type, {
			'class': this.data.data.view_name,
			'user_generic_data_id': this.data.data.layout_id.toString(),
			'rows_per_page': this.data.data.rows_per_page.toString()
		}, {
			onResult: function( result ) {
				var result_data = result.getResult();
				$this.dashboard_data = result_data;
				callback();
			}
		} );
	},

	getDefaultDashletData: function( callback ) {
		var $this = this;
		if ( !this.data.data.rows_per_page ) {
			this.data.data.rows_per_page = 0;
		}
		this.api_dashboard.getDashletData( this.data.data.dashlet_type, {
			'rows_per_page': this.data.data.rows_per_page.toString()
		}, {
			onResult: function( result ) {
				var result_data = result.getResult();
				$this.dashboard_data = result_data;
				callback();
			}
		} );
	},

	buildDisplayColumns: function( apiDisplayColumnsArray ) {
		var len = this.all_columns.length;
		var len1 = apiDisplayColumnsArray ? apiDisplayColumnsArray.length : 0;
		var display_columns = [];
		for ( var j = 0; j < len1; j++ ) {
			for ( var i = 0; i < len; i++ ) {
				if ( apiDisplayColumnsArray[j] === this.all_columns[i].value ) {
					display_columns.push( this.all_columns[i] );
				}
			}
		}
		return display_columns;

	},

	onGridDblClickRow: function() {
		this.onViewClick();
	},

	setSelectLayout: function() {
		var $this = this;
		var grid;
		if ( !Global.isSet( this.grid ) ) {
			grid = $( this.el ).find( '#' + 'dashlet_' + this.data.id + '_grid' );
		}
		var display_columns = this.buildDisplayColumns( this.dashboard_data.display_columns );
		//Set Data Grid on List view
		var column_info_array = [];
		var len = display_columns.length;
		var start_from = 0;
		for ( var i = start_from; i < len; i++ ) {
			var view_column_data = display_columns[i];
			var column_info = {
				name: view_column_data.value,
				index: view_column_data.value,
				label: view_column_data.label,
				width: 100,
				sortable: false,
				title: false
			};
			column_info_array.push( column_info );
		}

		if ( !this.grid ) {
			this.grid = grid;
			this.grid = this.grid.jqGrid( {
				altRows: true,
				data: [],
				datatype: 'local',
				sortable: false,
				width: (Global.bodyWidth() - 14),
				rowNum: 10000,
				colNames: [],
				ondblClickRow: function() {
					$this.onGridDblClickRow();
				},
				gridComplete: function() {
					if ( $( this ).jqGrid( 'getGridParam', 'data' ).length > 0 ) {
						$this.setGridColumnsWidth();
					}
				},
				onSelectRow: $.proxy( this.onGridSelectRow, this ),
				colModel: column_info_array,
				viewrecords: true

			} );
		} else {
			this.grid.jqGrid( 'GridUnload' );
			this.grid = null;
			grid = $( this.el ).find( '#dashlet_' + this.data.id + '_grid' );
			this.grid = $( grid );
			this.grid = this.grid.jqGrid( {
				altRows: true,
				onSelectRow: $.proxy( this.onGridSelectRow, this ),
				data: [],
				rowNum: 10000,
				ondblClickRow: function() {
					$this.onGridDblClickRow();
				},
				gridComplete: function() {
					if ( $( this ).jqGrid( 'getGridParam', 'data' ).length > 0 ) {

						$this.setGridColumnsWidth();
					}
				},
				sortable: false,
				datatype: 'local',
				width: (Global.bodyWidth() - 14),
				colNames: [],
				colModel: column_info_array,
				viewrecords: true
			} );

		}
		$this.setGridSize();
	},

	showNoResultCover: function() {
		this.removeNoResultCover();
		this.no_result_box = Global.loadWidgetByName( WidgetNamesDic.NO_RESULT_BOX );
		if ( this.no_result_box ) {
			this.no_result_box.NoResultBox( {
				related_view_controller: this,
				is_new: false,
				message: this.getNoResultMessage()
			} );
			this.no_result_box.attr( 'id', '#dashlet_' + this.data.id + '_no_result_box' );
			var grid_div = $( this.el ).find( '.content' );
			grid_div.append( this.no_result_box );
		}

	},
	getNoResultMessage: function() {
		//Show result message base on different dashlet type
		var result = $.i18n._( 'No Results Found' );
		switch ( this.data.data.dashlet_type ) {
			case 'schedule_summary':
				result = $.i18n._( 'Perhaps if you ask nicely, your supervisor will add a schedule for you?' );
				break;
			case 'exception_summary':
				result = $.i18n._( 'No exceptions to correct, great job!' );
				break;
			case 'message_summary':
				result = $.i18n._( 'All messages are read, nicely done!' );
				break;
			case 'request_summary':
				result = $.i18n._( 'Send a request to your supervisor by clicking MyAccount -> Requests.' );
				break;
			case 'accrual_balance_summary':
				result = $.i18n._( 'No accrual balances at this time.' );
				break;
			case 'timesheet_verification_summary':
			case 'timesheet_verification_summary_child':
				result = $.i18n._( 'No timesheets to verify yet.' );
				break;
			case 'schedule_summary_child':
				result = $.i18n._( 'Schedules can be added by clicking Attendance -> Schedules.' );
				break;
			case 'exception_summary_child':
				result = $.i18n._( 'All exceptions are corrected... You can relax now!' );
				break;
			case 'user_active_shift_summary':
				result = $.i18n._( 'No active shifts at this moment.' );
				break;
			case 'request_authorize_summary':
				result = $.i18n._( 'All requests are authorized, excellent work!' );
				break;
			case 'news':
				result = $.i18n._( 'Slow news day, nothing to see here yet...' );
				break;
		}

		return result;
	},

	removeNoResultCover: function() {
		if ( this.no_result_box && this.no_result_box.length > 0 ) {
			this.no_result_box.remove();
		}
		this.no_result_box = null;
	},

	getAllColumns: function( callBack ) {
		var $this = this;
		this.api.getOptions( 'columns', {
			onResult: function( columns_result ) {
				var columns_result_data = columns_result.getResult();
				$this.all_columns = Global.buildColumnArray( columns_result_data );
				if ( callBack ) {
					callBack();
				}

			}
		} );

	},

	getDefaultDisplayColumns: function( callBack ) {
		var $this = this;
		this.api.getOptions( 'default_display_columns', {
			onResult: function( columns_result ) {
				var columns_result_data = columns_result.getResult();
				$this.default_display_columns = columns_result_data;
				if ( callBack ) {
					callBack();
				}

			}
		} );
	},

	setTitle: function() {
		$( this.el ).find( '.title' ).text( this.data.name );
	},

	getAPIByViewName: function( view_name ) {
		var api = null;
		switch ( view_name ) {
			case 'message_summary':
				api = new (APIFactory.getAPIClass( 'APIMessageControl' ))();
				break;
			case 'schedule_summary':
			case 'schedule_summary_child':
			case 'Schedule':
				api = new (APIFactory.getAPIClass( 'APISchedule' ))();
				break;
			case 'Exception':
			case 'exception_summary':
			case 'exception_summary_child':
				api = new (APIFactory.getAPIClass( 'APIException' ))();
				break;
			case 'Invoice':
				api = new (APIFactory.getAPIClass( 'APIInvoice' ))();
				break;
			case 'User':
				api = new (APIFactory.getAPIClass( 'APIUser' ))();
				break;
			case 'request_summary':
			case 'Request':
			case 'Request-Authorization':
			case 'request_authorize_summary':
				api = new (APIFactory.getAPIClass( 'APIRequest' ))();
				break;
			case 'accrual_balance_summary':
				api = new (APIFactory.getAPIClass( 'APIAccrualBalance' ))();
				break;
			case 'timesheet_verification_summary':
			case 'timesheet_verification_summary_child':
				api = new (APIFactory.getAPIClass( 'APITimesheetSummaryReport' ))();
				break;
			case 'timesheet_summary':
				api = new (APIFactory.getAPIClass( 'APITimeSheet' ))();
				break;
			case 'user_active_shift_summary':
				api = new (APIFactory.getAPIClass( 'APIActiveShiftReport' ))();
				break;
			case 'PayPeriodTimeSheetVerify':
				api = new (APIFactory.getAPIClass( 'APIPayPeriodTimeSheetVerify' ))();
				break;
			case 'UserExpense':
			case 'UserExpense-Authorization':
				api = new (APIFactory.getAPIClass( 'APIUserExpense' ))();
				break;
			case 'TimeSheetSummaryReport':
				api = new (APIFactory.getAPIClass( 'APITimesheetSummaryReport' ))();
				break;
			case 'TimeSheetDetailReport':
				api = new (APIFactory.getAPIClass( 'APITimesheetDetailReport' ))();
				break;
			case 'AccrualBalance':
				api = new (APIFactory.getAPIClass( 'APIAccrualBalance' ))();
				break;
			case 'Accrual':
				api = new (APIFactory.getAPIClass( 'APIAccrual' ))();
				break;
			case 'RecurringScheduleControl':
				api = new (APIFactory.getAPIClass( 'APIRecurringScheduleControl' ))();
				break;
			case 'RecurringScheduleTemplateControl':
				api = new (APIFactory.getAPIClass( 'APIRecurringScheduleTemplateControl' ))();
				break;
			case 'Job':
				api = new (APIFactory.getAPIClass( 'APIJob' ))();
				break;
			case 'JobItem':
				api = new (APIFactory.getAPIClass( 'APIJobItem' ))();
				break;
			case 'UserContact':
				api = new (APIFactory.getAPIClass( 'APIUserContact' ))();
				break;
			case 'UserWage':
				api = new (APIFactory.getAPIClass( 'APIUserWage' ))();
				break;
			case 'PayStub':
				api = new (APIFactory.getAPIClass( 'APIPayStub' ))();
				break;
			case 'PayPeriod':
				api = new (APIFactory.getAPIClass( 'APIPayPeriod' ))();
				break;
			case 'PayStubAmendment':
				api = new (APIFactory.getAPIClass( 'APIPayStubAmendment' ))();
				break;
			case 'Client':
				api = new (APIFactory.getAPIClass( 'APIClient' ))();
				break;
			case 'ClientContact':
				api = new (APIFactory.getAPIClass( 'APIClientContact' ))();
				break;
			case 'Transaction':
				api = new (APIFactory.getAPIClass( 'APITransaction' ))();
				break;
			case 'UserReviewControl':
				api = new (APIFactory.getAPIClass( 'APIUserReviewControl' ))();
				break;
			case 'JobVacancy':
				api = new (APIFactory.getAPIClass( 'APIJobVacancy' ))();
				break;
			case 'JobApplicant':
				api = new (APIFactory.getAPIClass( 'APIJobApplicant' ))();
				break;
			case 'JobApplication':
				api = new (APIFactory.getAPIClass( 'APIJobApplication' ))();
				break;
			case 'AccrualBalanceSummaryReport':
				api = new (APIFactory.getAPIClass( 'APIAccrualBalanceSummaryReport' ))();
				break;
			case 'UserSummaryReport':
				api = new (APIFactory.getAPIClass( 'APIUserSummaryReport' ))();
				break;
			case 'ActiveShiftReport':
				api = new (APIFactory.getAPIClass( 'APIActiveShiftReport' ))();
				break;
			case 'AuditTrailReport':
				api = new (APIFactory.getAPIClass( 'APIAuditTrailReport' ))();
				break;
			case 'ScheduleSummaryReport':
				api = new (APIFactory.getAPIClass( 'APIScheduleSummaryReport' ))();
				break;
			case 'PunchSummaryReport':
				api = new (APIFactory.getAPIClass( 'APIPunchSummaryReport' ))();
				break;
			case 'ExceptionReport':
				api = new (APIFactory.getAPIClass( 'APIExceptionSummaryReport' ))();
				break;
			case 'PayStubSummaryReport':
				api = new (APIFactory.getAPIClass( 'APIPayStubSummaryReport' ))();
				break;
			case 'UserExpenseReport':
				api = new (APIFactory.getAPIClass( 'APIUserExpenseReport' ))();
				break;
			case 'JobSummaryReport':
				api = new (APIFactory.getAPIClass( 'APIJobSummaryReport' ))();
				break;
			case 'JobDetailReport':
				api = new (APIFactory.getAPIClass( 'APIJobDetailReport' ))();
				break;
			case 'JobInformationReport':
				api = new (APIFactory.getAPIClass( 'APIJobInformationReport' ))();
				break;
			case 'JobItemInformationReport':
				api = new (APIFactory.getAPIClass( 'APIJobItemInformationReport' ))();
				break;
			case 'InvoiceTransactionSummaryReport':
				api = new (APIFactory.getAPIClass( 'APIInvoiceTransactionSummaryReport' ))();
				break;
			case 'UserQualificationReport':
				api = new (APIFactory.getAPIClass( 'APIUserQualificationReport' ))();
				break;
			case 'KPIReport':
				api = new (APIFactory.getAPIClass( 'APIKPIReport' ))();
				break;
			case 'UserRecruitmentSummaryReport':
				api = new (APIFactory.getAPIClass( 'APIUserRecruitmentSummaryReport' ))();
				break;
			case 'UserRecruitmentDetailReport':
				api = new (APIFactory.getAPIClass( 'APIUserRecruitmentDetailReport' ))();
				break;
		}

		return api;
	},

	setGridColumnsWidth: function() {
		var col_model = this.grid.getGridParam( 'colModel' );
		var grid_data = this.grid.getGridParam( 'data' );
		this.grid_total_width = 0;
		//Possible exception
		//Error: Uncaught TypeError: Cannot read property 'length' of undefined in /interface/html5/#!m=TimeSheet&date=20141102&user_id=53130 line 4288
		if ( !col_model ) {
			return;
		}
		for ( var i = 0; i < col_model.length; i++ ) {
			var col = col_model[i];
			var field = col.name;
			var longest_words = '';
			for ( var j = 0; j < grid_data.length; j++ ) {
				var row_data = grid_data[j];
				if ( !row_data.hasOwnProperty( field ) ) {
					break;
				}
				var current_words = row_data[field];
				if ( !current_words ) {
					current_words = '';
				}
				if ( !longest_words ) {
					longest_words = current_words.toString();
				} else {
					if ( current_words && current_words.toString().length > longest_words.length ) {
						longest_words = current_words.toString();
					}
				}

			}
			if ( longest_words ) {
				var width_test = $( '<span id="width_test" />' );
				width_test.css( 'font-size', '11' );
				width_test.css( 'font-weight', 'normal' );
				$( 'body' ).append( width_test );
				width_test.text( longest_words );
				var width = width_test.width();
				width_test.text( col.label );
				var header_width = width_test.width();
				if ( header_width > width ) {
					width = header_width + 20;
				}
				this.grid_total_width += width + 5;
				this.grid.setColProp( field, {widthOrg: width + 5} );
				width_test.remove();
			}
		}
		var gw = this.grid.getGridParam( 'width' );
		this.grid.setGridWidth( gw );

	}

} );

DashletController.TOTAL_ROW = 4;
DashletController.REGULAR_ROW = 5;
