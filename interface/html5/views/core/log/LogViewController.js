LogViewController = BaseViewController.extend( {
	el: '#log_view_container',
	tables: {
		'product': ['product', 'product_price'],
		'user_contact': ['user_contact'],
		'users': ['users', 'user_preference', 'user_wage', 'authentication'],
		'user_wage': ['user_wage'],
		'user_title': ['user_title'],
		'user_preference': ['user_preference'],
		'bank_account': ['bank_account'],
		'user_default': ['user_default'],
		'user_group': ['user_group'],
		'company': ['company'],
		'pay_period_schedule': ['pay_period_schedule', 'pay_period', 'pay_period_schedule_user'],
		'pay_period': ['pay_period'],
		'branch': ['branch'],
		'department': ['department'],
		'hierarchy_control': ['hierarchy_control', 'hierarchy_object_type', 'hierarchy_user', 'hierarchy_level'],
		'wage_group': ['wage_group'],
		'ethnic_group': ['ethnic_group'],
		'currency': ['currency'],
		'permission_control': ['permission_control', 'permission_user'],
		'other_field': ['other_field'],
		'station': ['station', 'station_user_group', 'station_branch', 'station_department', 'station_include_user', 'station_exclude_user'],
		'pay_stub_amendment': ['pay_stub_amendment'],
		'recurring_ps_amendment': ['recurring_ps_amendment', 'recurring_ps_amendment_user'],
		'pay_stub_entry_account': ['pay_stub_entry_account'],
		'company_deduction': ['company_deduction', 'user_deduction', 'company_deduction_pay_stub_entry_account'],
		'user_expense': ['user_expense'],
		'round_interval_policy': ['round_interval_policy'],
		'meal_policy': ['meal_policy'],
		'break_policy': ['break_policy'],
		'over_time_policy': ['over_time_policy'],
		'absence_policy': ['absence_policy'],
		'recurring_holiday': ['recurring_holiday'],
		'holiday_policy': ['holiday_policy', 'holiday_policy_recurring_holiday'],
		'holidays': ['holidays'],
		'premium_policy': ['premium_policy'],
		'policy_group': ['policy_group', 'policy_group_user'],
		'document': ['document', 'document_revision'],
		'document_group': ['document_group'],
		'document_revision': ['document_revision'],
		'schedule_policy': ['schedule_policy'],
		'accrual_policy': ['accrual_policy', 'accrual_policy_milestone'],
		'client': ['client', 'client_contact', 'client_payment'],
		'report_custom_column': ['report_custom_column'],
		'client_contact': ['client_contact'],
		'client_payment': ['client_payment'],
		'invoice_transaction': ['invoice_transaction'],
		'invoice': ['invoice'],
		'job': ['job', 'job_exclude_job_item', 'job_exclude_user', 'job_include_job_item', 'job_include_user', 'job_job_item_group', 'job_user_branch', 'job_user_group', 'job_user_department'],
		'client_group': ['client_group'],
		'job_item': ['job_item'],
		'job_group': ['job_group'],
		'job_item_group': ['job_item_group'],
		'report_schedule': ['report_schedule'],
		'accrual': ['accrual'],
		'accrual_balance': ['accrual_balance'],
		'schedule': ['schedule'],
		'recurring_schedule_control': ['recurring_schedule_control', 'recurring_schedule_user'],
		'recurring_schedule_template_control': ['recurring_schedule_template_control', 'recurring_schedule_template'],
		'punch': ['punch', 'punch_control'],
		'kpi': ['kpi'],
		'kpi_group': ['kpi_group'],
		'qualification': ['qualification'],
		'qualification_group': ['qualification_group'],
		'user_skill': ['user_skill'],
		'user_education': ['user_education'],
		'user_membership': ['user_membership'],
		'user_license': ['user_license'],
		'user_language': ['user_language'],
		'job_vacancy': ['job_vacancy'],
		'job_application': ['job_application'],
		'job_applicant': ['job_applicant'],
		'invoice_district': ['invoice_district'],
		'job_applicant_employment': ['job_applicant_employment'],
		'job_applicant_reference': ['job_applicant_reference'],
		'job_applicant_location': ['job_applicant_location'],
		'job_applicant_skill': ['job_applicant_skill'],
		'job_applicant_education': ['job_applicant_education'],
		'job_applicant_license': ['job_applicant_license'],
		'job_applicant_membership': ['job_applicant_membership'],
		'job_applicant_language': ['job_applicant_language'],
		'tax_policy': ['tax_policy'],
		'area_policy': ['area_policy'],
		'shipping_policy': ['shipping_policy'],
		'payment_gateway': ['payment_gateway'],
		'request': ['request'],
		'exception_policy_control': ['exception_policy_control', 'exception_policy'],
		'user_review_control': ['user_review_control', 'user_review'],
		'roe': ['roe'],
		'expense_policy': ['expense_policy'],
		'user_report_data': ['user_report_data']
	},
	log_detail_grid: null,
	log_detail_script_name: null,

	initialize: function() {

		if ( Global.isSet( this.options.sub_view_mode ) ) {

			this.sub_view_mode = this.options.sub_view_mode;
		}

		this._super( 'initialize' );
		this.edit_view_tpl = 'LogEditView.html';
		this.context_menu_name = $.i18n._( 'Audit' );
		this.navigation_label = $.i18n._( 'Audit' ) + ':';
		this.viewId = 'Log';
		this.script_name = 'LogView';
		this.log_detail_script_name = 'LogDetailView';
		this.api = new (APIFactory.getAPIClass( 'APILog' ))();

		this.render();

		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
		} else {
			this.buildContextMenu();
		}

	},

	buildEditViewUI: function() {
		this._super( 'buildEditViewUI' );
		var tab_0_label = this.edit_view.find( 'a[ref=tab0]' );
		tab_0_label.text( $.i18n._( 'Audit Details' ) );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APILog' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.LOG,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		var tab0 = this.edit_view_tab.find( '#tab0' );
		var tab0_column1 = tab0.find( '.first-row' );
		// tab0 column1

		// Date
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {
			field: 'date'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab0_column1, '' );

		// Action
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {
			field: 'action'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Action' ), form_item_input, tab0_column1 );

		// Employee
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );

		form_item_input.TText( {
			field: 'user_name'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab0_column1 );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {
			field: 'description'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab0_column1, '' );

		// set the log details information.
		this.initLogDetailsView();

	},

	initLogDetailsView: function( column_start_from ) {

		var grid = this.edit_view.find( '#grid' );

		grid.attr( 'id', this.log_detail_script_name + '_grid' );  //Grid's id is ScriptName + _grid

		grid = this.edit_view.find( '#' + this.log_detail_script_name + '_grid' );

		var column_info_array = [];
		var display_columns = [
			{'label': 'Field', 'value': 'display_field'},
			{'label': 'Before', 'value': 'old_value'},
			{'label': 'After', 'value': 'new_value'}
		];

		//Set Data Grid on List view
		var len = display_columns.length;

		var start_from = 0;

		if ( Global.isSet( column_start_from ) && column_start_from > 0 ) {
			start_from = column_start_from;
		}

		for ( var i = start_from; i < len; i++ ) {
			var view_column_data = display_columns[i];

			var column_info = {name: view_column_data.value, index: view_column_data.value, label: view_column_data.label, width: 100, sortable: false, title: false};
			column_info_array.push( column_info );
		}

		this.log_detail_grid = grid;

		this.log_detail_grid.jqGrid( {
			altRows: true,
			data: [],
			datatype: 'local',
			sortable: false,
			width: Global.bodyWidth() - 14,
			rowNum: 10000,
			colNames: [],
			colModel: column_info_array,
			viewrecords: true

		} );

	},

	initEditViewData: function() {
		this._super( 'initEditViewData' );
		if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {
			this.edit_view_tab.find( '#tab0' ).find( '.detail-grid-row' ).css( 'display', 'block' );
		} else {
			this.edit_view_tab.find( '#tab0' ).find( '.detail-grid-row' ).css( 'display', 'none' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );
		}
	},

	onGridDblClickRow: function() {
		this.onViewDetailClick();
	},

	setCurrentEditRecordData: function() {
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'user_name':
						widget.setValue( this.current_edit_record['first_name'] + ' ' + this.current_edit_record['last_name'] );
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			} else {
				switch ( key ) {
					case 'details':
						this.setLogDetailsViewData( this.current_edit_record[key] );
						break;
					default:
						break;
				}
			}
		}
		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();
	},

	setLogDetailsViewData: function( log_detail_data ) {

		var $this = this;

		if ( !Global.isArray( log_detail_data ) ) {
			$this.showDetailNoResultCover();
		} else {
			$this.removeNoResultCover();
		}

		log_detail_data = Global.formatGridData( log_detail_data );

		$this.log_detail_grid.clearGridData();
		$this.log_detail_grid.setGridParam( {data: log_detail_data} );
		$this.log_detail_grid.trigger( 'reloadGrid' );

		$this.setLogDetailGridSize();

	},

	setLogDetailGridSize: function() {

		if ( !this.log_detail_grid || !this.log_detail_grid.is( ':visible' ) ) {
			return;
		}

		var tab0 = this.edit_view.find( '#tab0_content_div' );
		var first_row = this.edit_view.find( '.first-row' );
		this.log_detail_grid.setGridWidth( tab0.width() );
		this.log_detail_grid.setGridHeight( tab0.height() - first_row.height() );

	},

	showDetailNoResultCover: function() {
		this.removeNoResultCover();
		this.no_result_box = Global.loadWidgetByName( WidgetNamesDic.NO_RESULT_BOX );
		this.no_result_box.NoResultBox( {related_view_controller: this, is_new: false} );

		var grid_div = this.edit_view.find( '.grid-div' );

		grid_div.append( this.no_result_box );
	},

	showNoResultCover: function() {

		this._super( 'showNoResultCover', false );
	},

	onEditClick: function( editId, noRefreshUI ) {

		this.onViewDetailClick( editId, noRefreshUI );
	},

	onViewDetailClick: function( editId ) {

		var $this = this;
		this.is_viewing = false;
		LocalCacheData.current_doing_context_action = 'view_detail';
		$this.openEditView();

		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		var selectedId;
		if ( Global.isSet( editId ) ) {
			selectedId = editId;
		} else {

			if ( this.is_viewing ) {
				selectedId = this.current_edit_record.id;
			} else if ( grid_selected_length > 0 ) {
				selectedId = grid_selected_id_array[0];
			} else {
				return;
			}
		}

		filter.filter_data = {};
		filter.filter_data.id = [selectedId];
		//If sub view controller set custom filters, get it
		if ( Global.isSet( this.getSubViewFilter ) ) {

			filter.filter_data = this.getSubViewFilter( filter.filter_data );

		}
		filter.filter_columns = this.getFilterColumnsForViewDetails();

		this.api['get' + this.api.key_name]( filter, {onResult: function( result ) {
			var result_data = result.getResult();

			if ( !result_data ) {
				result_data = [];
			}

			result_data = result_data[0];

			if ( $this.sub_view_mode && $this.parent_key ) {
				result_data[$this.parent_key] = $this.parent_value;
			}

			$this.current_edit_record = result_data;

			$this.initEditView();

		}} );
	},

	getFilterColumnsFromDisplayColumns: function() {
		var display_columns = this.grid.getGridParam( 'colModel' );

		var column_filter = {};
		column_filter.id = true;
		column_filter.table_name = true;

		if ( display_columns ) {
			var len = display_columns.length;

			for ( var i = 0; i < len; i++ ) {
				var column_info = display_columns[i];
				column_filter[column_info.name] = true;
			}
		}

		return column_filter;
	},

	getFilterColumnsForViewDetails: function() {
		var display_columns = this.grid.getGridParam( 'colModel' );

		var column_filter = {};
		column_filter.id = true;
		column_filter.table_name = true;
		column_filter.details = true;

		var len = display_columns.length;

		for ( var i = 0; i < len; i++ ) {
			var column_info = display_columns[i];
			column_filter[column_info.name] = true;
		}

		return column_filter;
	},

	setDefaultMenu: function() {

		this.selectContextMenu();
		this.setTotalDisplaySpan();

		var len = this.context_menu_array.length;

		var grid_selected_id_array = this.getGridSelectIdArray();

		var grid_selected_length = grid_selected_id_array.length;

		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			context_btn.removeClass( 'invisible-image' );
			context_btn.removeClass( 'disable-image' );

			switch ( id ) {
				case ContextMenuIconName.view_detail:
					if ( grid_selected_length === 1 ) {
						context_btn.removeClass( 'disable-image' );
					} else {
						context_btn.addClass( 'disable-image' );
					}
					break;
			}

		}

	},

	onContentMenuClick: function( context_btn, menu_name ) {

		var id;
		if ( Global.isSet( menu_name ) ) {
			id = menu_name;
		} else {
			context_btn = $( context_btn );

			id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			if ( context_btn.hasClass( 'disable-image' ) ) {
				return;
			}
		}

		switch ( id ) {
			case ContextMenuIconName.view_detail:
				this.onViewDetailClick();
				break;
			case ContextMenuIconName.cancel:
				this.onCancelClick();
				break;
		}
	},

	setEditMenu: function() {

		this.selectContextMenu();
		var len = this.context_menu_array.length;

		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			context_btn.removeClass( 'disable-image' );

			switch ( id ) {
				case ContextMenuIconName.view_detail:
					context_btn.addClass( 'disable-image' );
					break;
			}

		}

	},

	buildContextMenuModels: function() {
		//Context Menu
		var menu = new RibbonMenu( {
			label: this.context_menu_name,
			id: this.viewId + 'ContextMenu',
			sub_menu_groups: []
		} );

		//menu group
		var editor_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Editor' ),
			id: this.viewId + 'Editor',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var view_detail = new RibbonSubMenu( {
			label: $.i18n._( 'View Details' ),
			id: ContextMenuIconName.view_detail,
			group: editor_group,
			icon: Icons.view_detail,
			permission_result: true,
			permission: null
		} );

		var cancel = new RibbonSubMenu( {
			label: $.i18n._( 'Cancel' ),
			id: ContextMenuIconName.cancel,
			group: editor_group,
			icon: Icons.cancel,
			permission_result: true,
			permission: null
		} );

		return [menu];
	},

	getSubViewFilter: function( filter ) {
		if ( Global.isSet( this.table_name_key ) ) {
			filter['table_name'] = this.tables[this.table_name_key];
		}

		return filter;
	}

} );

LogViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'Log', 'SubLogView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}
		if ( Global.isSet( container ) ) {
			container.html( template );
			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_log_view_controller );
			}
		}
	} );
};
