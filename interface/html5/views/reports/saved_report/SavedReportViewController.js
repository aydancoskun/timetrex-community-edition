SavedReportViewController = BaseViewController.extend( {
	el: '#saved_report_view_container',

	//Issue #1187 - Many of these variables are needed when this view is rendered as a sub view
	//in that case, init() will not be run so those variables need to be defined at load time instead.

	_required_files: ['APIUserReportData', 'APIUser'],
	sub_report_schedule_view_controller: null,
	edit_view_tpl: 'SavedReportEditView.html',
	permission_id: 'report',
	viewId: 'SavedReport',
	script_name: 'UserReportDataView',
	table_name_key: 'user_report_data',
	context_menu_name: $.i18n._( 'Reports' ),
	navigation_label: $.i18n._( 'Saved Report' ) + ':',

	api: new (APIFactory.getAPIClass( 'APIUserReportData' ))(),

	init: function( options ) {

		this.render();
		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );

			//call init data in parent view, don't call initData
		} else {
			this.buildContextMenu();
			if ( !this.sub_view_mode ) {
				this.initData();
			}
			this.setSelectRibbonMenuIfNecessary();
		}

	},

	onDeleteResult: function( result, remove_ids ) {
		var $this = this;
		ProgressBar.closeOverlay();

		if ( result.isValid() ) {
			$this.search();
			$this.onDeleteDone( result );

			if ( $this.edit_view ) {
				$this.removeEditView();
			}

			if ( this.sub_view_mode && this.parent_view_controller ) {
				this.parent_view_controller.onSavedReportDelete();
			}

		} else {
			TAlertManager.showErrorAlert( result );
		}
	},

	onCustomContextClick: function( id ) {
		switch ( id ) {
			case ContextMenuIconName.share_report:

				if ( Global.getProductEdition() >= 15 ) {
					var default_data = [];
					if ( this.edit_view && this.current_edit_record.id ) {
						default_data.push( this.current_edit_record.id );
					} else if ( !this.edit_view ) {
						default_data = this.getGridSelectIdArray();
					}
					IndexViewController.openWizard( 'ShareReportWizard', default_data );
				} else {
					TAlertManager.showAlert( Global.getUpgradeMessage() );
				}

				break;
		}
	},

	onGridDblClickRow: function() {
		ProgressBar.showOverlay();

		if ( this.sub_view_mode ) {
			this.onEditClick();
		} else {
			this.onViewClick();
		}

	},

	getCustomContextMenuModel: function () {
		var context_menu_model = {
			groups: {
				share: {
					label: $.i18n._( 'Share' ),
					id: this.viewId + 'Share'
				}
			},
			exclude: ['default'],
			include: [
				ContextMenuIconName.edit,
				ContextMenuIconName.delete_icon,
				ContextMenuIconName.delete_and_next,
				ContextMenuIconName.save,
				ContextMenuIconName.save_and_continue,
				ContextMenuIconName.save_and_next,
				ContextMenuIconName.cancel,
				{
					label: $.i18n._( 'Share<br>Report' ),
					id: ContextMenuIconName.share_report,
					group: 'share',
					icon: Icons.copy_as_new
				}
			]
		};

		if ( !this.sub_view_mode ) {
			context_menu_model.include.unshift({
				label: $.i18n._( 'Report' ),
				id: ContextMenuIconName.view,
				group: 'editor',
				icon: Icons.hr_reports
			});
		}

		return context_menu_model;
	},

	removeEditView: function() {

		this._super( 'removeEditView' );
		this.sub_report_schedule_view_controller = null;

	},

	getGridSetup: function() {
		var $this = this;

		var grid_setup = {
			container_selector: this.sub_view_mode ? '#tab_saved_reports' : '.view', //tab4 = Saved Report tab.
			sub_grid_mode: this.sub_view_mode,
			onSelectRow: function() {
				$this.onGridSelectRow();
			},
			onCellSelect: function() {
				$this.onGridSelectRow();
			},
			onSelectAll: function() {
				$this.onGridSelectAll();
			},
			ondblClickRow: function( e ) {
				$this.onGridDblClickRow( e );
			},
			onRightClickRow: function( rowId ) {
				var id_array = $this.getGridSelectIdArray();
				if ( id_array.indexOf( rowId ) < 0 ) {
					$this.grid.grid.resetSelection();
					$this.grid.grid.setSelection( rowId );
					$this.onGridSelectRow();
				}
			},
		};

		//Only use custom grid sizing when in sub_view_mode, since we need to use the BaseViewController grid sizing otherwise.
		if ( this.sub_view_mode ) {
			grid_setup.setGridSize = function() {
				if ( $this.sub_view_mode ) {
					$this.baseViewSubTabGridResize( '#tab_saved_reports' );
				}
			};

			grid_setup.onResizeGrid = function() {
				if ( $this.sub_view_mode ) {
					$this.baseViewSubTabGridResize( '#tab_saved_reports' );
				}
			};
		}

		return grid_setup;

	},

	onViewClick: function( editId, noRefreshUI ) {
		var grid_selected_id_array = this.getGridSelectIdArray();
		var id = grid_selected_id_array[0];

		var record = this.getRecordFromGridById( id );
		if ( record && record.script ) {
			var report_name = record.script; //Must use 'script' instead of 'script_name' so it doesn't change with different languages.

			LocalCacheData.current_doing_context_action = 'view';
			LocalCacheData.default_edit_id_for_next_open_edit_view = id;

			switch ( report_name ) {
				case 'AccrualBalanceSummaryReport':
				case 'ActiveShiftReport':
				case 'AuditTrailReport':
				case 'Form1099MiscReport':
				case 'Form940Report':
				case 'Form941Report':
				case 'FormW2Report':
				case 'GeneralLedgerSummaryReport':
				case 'InvoiceTransactionSummaryReport':
				case 'JobInformationReport':
				case 'JobItemInformationReport':
				case 'JobSummaryReport':
				case 'KPIReport':
				case 'PayrollExportReport':
				case 'PayStubTransactionSummaryReport':
				case 'PayStubSummaryReport':
				case 'PunchSummaryReport':
				case 'RemittanceSummaryReport':
				case 'ScheduleSummaryReport':
				case 'T4ASummaryReport':
				case 'T4SummaryReport':
				case 'TaxSummaryReport':
				case 'TimesheetDetailReport':
				case 'TimesheetSummaryReport':
				case 'UserQualificationReport':
				case 'UserRecruitmentDetailReport':
				case 'UserRecruitmentSummaryReport':
				case 'UserSummaryReport':
					//Leave report_name alone, as it should match exactly.
					break;
				case 'UserExpenseReport':
					report_name = 'ExpenseSummaryReport';
					break;
				case 'ExceptionReport':
					report_name = 'ExceptionSummaryReport';
					break;
				case 'JobDetailReport':
					report_name = 'JobAnalysisReport';
					break;
				case 'AffordableCareReport':
					if ( Global.getProductEdition() == 10 ) {
						TAlertManager.showAlert( Global.getUpgradeMessage() );
						report_name = null;
					}
					break;
				default:
					ProgressBar.closeOverlay();
					Debug.Text( 'ERROR: Saved Report name not defined: ' + report_name, 'SavedReportViewController.js', '', 'onViewClick', 10 );
					report_name = null;
					break;
			}

			if ( Global.isSet( report_name ) && report_name ) {
				IndexViewController.openReport( this, report_name );
			}
		}
	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );
		var $this = this;

		var tab_model = {
			'tab_report': { 'label': $.i18n._( 'Report' ) },
			'tab_schedule': { 'label': $.i18n._( 'Schedule' ), 'init_callback': 'initSubReportScheduleView' },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		if ( !this.edit_only_mode ) {
			this.navigation.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIUserReportData' )),
				id: this.script_name + '_navigation',
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.SAVED_REPORT,
				navigation_mode: true,
				show_search_inputs: true
			} );

			this.setNavigation();

		}

		//Tab 0 start

		var tab_report = this.edit_view_tab.find( '#tab_report' );

		var tab_report_column1 = tab_report.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_report_column1 );

		// Name

		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_report_column1, '' );
		form_item_input.parent().width( '45%' );

		// Default

		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );

		form_item_input.TCheckbox( { field: 'is_default' } );
		this.addEditFieldToColumn( $.i18n._( 'Default' ), form_item_input, tab_report_column1 );

		// Description

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );

		form_item_input.TTextInput( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_report_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );


	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );
		this.search_fields = [
			new SearchField( {
				label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Description' ),
				field: 'description',
				basic_search: true,
				adv_search: false,
				in_column: 1,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} )

		];
	},

	initSubReportScheduleView: function() {
		var $this = this;

		if ( !this.current_edit_record.id ) {
			TTPromise.resolve( 'BaseViewController', 'onTabShow' ); //Since search() isn't called in this case, and we just display the "Please Save This Record ..." message, resolve the promise.
			return;
		}

		$this.sub_view_mode = true;

		if ( Global.getProductEdition() >= 15 ) {
			if ( this.sub_report_schedule_view_controller ) {
				this.sub_report_schedule_view_controller.buildContextMenu( true );
				this.sub_report_schedule_view_controller.setDefaultMenu();
				$this.sub_report_schedule_view_controller.parent_value = $this.current_edit_record.id;
				$this.sub_report_schedule_view_controller.parent_edit_record = $this.current_edit_record;
				$this.sub_report_schedule_view_controller.initData(); //Init data in this parent view
				return;
			}

			Global.loadViewSource( 'ReportSchedule', 'ReportScheduleViewController.js', function() {
				var tab = $this.edit_view_tab.find( '#tab_schedule' );

				var firstColumn = tab.find( '.first-column-sub-view' );

				TTPromise.add( 'initSubReportScheduleView', 'init' );
				TTPromise.wait( 'initSubReportScheduleView', 'init', function() {
					firstColumn.css('opacity', '1');
				} );

				firstColumn.css('opacity', '0'); //Hide the grid while its loading/sizing.

				Global.trackView( 'Sub' + 'ReportSchedule' + 'View' );
				ReportScheduleViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );
			} );
		} else {
			this.edit_view_tab.find( '#tab_schedule' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );
			this.edit_view.find( '.save-and-continue-button-div' ).css( 'display', 'none' );
		}

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_report_schedule_view_controller = subViewController;
			$this.sub_report_schedule_view_controller.parent_key = 'user_report_data_id';
			$this.sub_report_schedule_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_report_schedule_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_report_schedule_view_controller.parent_view_controller = $this;
			$this.sub_report_schedule_view_controller.sub_view_mode = true;

			$this.sub_report_schedule_view_controller.initData(); //Init data in this parent view
		}
	},

	uniformVariable: function( records ) {

		// Remove in next commit, related to #2698 fix, no longer needed but semi-big refactor to remove
		// if ( records.hasOwnProperty( 'data' ) && records.data.hasOwnProperty( 'config' ) && records.data.config.hasOwnProperty( 'filter' ) ) {
		// 	records.data.config.filter_ = records.data.config.filter;
		// }

		return records;
	},

	onSaveDone: function( result ) {
		//onSaveDoneCallback is set in Report controller
		if ( this.parent_view_controller && this.parent_view_controller.onSaveDoneCallback ) {
			this.parent_view_controller.onSaveDoneCallback( result, this.current_edit_record );
		}
	},

	onSaveAndContinueDone: function( result ) {
		this.onSaveDone( result );
	},

	onSaveAndNextDone: function( result ) {
		this.onSaveDone( result );
	},

	getFilterColumnsFromDisplayColumns: function() {
		var column_filter = {};
		column_filter.id = true;
		column_filter.is_owner = true;
		column_filter.is_child = true;
		column_filter.script = true; //Include script column so onViewClick() knows which view to open for saved reports.

		// Error: Unable to get property 'getGridParam' of undefined or null reference
		var display_columns = [];
		if ( this.grid ) {
			display_columns = this.grid.getGridParam( 'colModel' );
		}

		if ( display_columns ) {
			var len = display_columns.length;

			for ( var i = 0; i < len; i++ ) {
				var column_info = display_columns[i];
				column_filter[column_info.name] = true;
			}
		}

		return column_filter;
	},

	onAddClick: function( reportData ) {

		ProgressBar.closeOverlay();
		var $this = this;
		this.setCurrentEditViewState('new');
		$this.openEditView();
		$this.current_edit_record = reportData;
		$this.initEditView();

	},

	searchDone: function() {
		$('window').trigger('resize');
		if ( this.sub_view_mode ) {
			TTPromise.resolve( 'SubSavedReportView', 'init' );
		}
		this._super('searchDone');
	}
} );

SavedReportViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'SavedReport', 'SubSavedReportView.html', function( result ) {

		var args = {};
		var template = _.template( result );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}

		if ( Global.isSet( container ) ) {
			container.html( template( args ) );
			if ( Global.isSet( afterViewLoadedFun ) ) {
				TTPromise.wait( 'BaseViewController', 'initialize', function() {
					afterViewLoadedFun( sub_saved_report_controller );
				} );
			}

		}

	} );

};
