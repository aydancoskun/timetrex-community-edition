TimeSheetAuthorizationViewController = BaseViewController.extend( {
	el: '#timesheet_authorization_view_container',

	_required_files: ['APIPayPeriodTimeSheetVerify', 'APIPayPeriod', 'APITimeSheet', 'APIException', 'APIRequest', 'APIMessageControl', 'APIAuthorization'],

	type_array: null,
	hierarchy_level_array: null,

	messages: null,

	message_control_api: null,

	authorization_api: null,

	request_api: null,
	init: function( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'TimeSheetAuthorizationEditView.html';
		this.permission_id = 'punch';
		this.viewId = 'TimeSheetAuthorization';
		this.script_name = 'TimeSheetAuthorizationView';
		this.table_name_key = 'pay_period_time_sheet_verify';
		this.context_menu_name = $.i18n._( 'TimeSheet (Authorizations)' );
		this.navigation_label = $.i18n._( 'TimeSheet' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIPayPeriodTimeSheetVerify' ))();
		this.request_api = new (APIFactory.getAPIClass( 'APIRequest' ))();
		this.message_control_api = new (APIFactory.getAPIClass( 'APIMessageControl' ))();
		this.authorization_api = new (APIFactory.getAPIClass( 'APIAuthorization' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.add] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.edit] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.mass_edit] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.delete_icon] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.delete_and_next] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.copy_as_new] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_continue] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_next] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_copy] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_new] = true;

		this.render();
		this.buildContextMenu( true );

		this.initData();
		this.setSelectRibbonMenuIfNecessary();

		this.hierarchy_type_id = 90;
	},

	initOptions: function() {
		var $this = this;
		var res = this.request_api.getHierarchyLevelOptions( [-1], { async: false } );
		var data = res.getResult();
		$this['hierarchy_level_array'] = Global.buildRecordArray( data );
		if ( Global.isSet( $this.basic_search_field_ui_dic['hierarchy_level'] ) ) {
			$this.basic_search_field_ui_dic['hierarchy_level'].setSourceData( Global.buildRecordArray( data ) );
		}

	},

	search: function( set_default_menu, page_action, page_number, callBack ) {
		this.refresh_id = null;
		this._super( 'search', set_default_menu, page_action, page_number, callBack );
	},

	processResultData: function( result_data ) {
		var len = result_data.length;
		for ( var i = 0; i < len; i++ ) {
			var item = result_data[i];
			if ( item.id == TTUUID.not_exist_id ) {
				item.id = item.user_id + '_' + item.pay_period_id;
			}
			//item.id = item.user_id + '_' + item.pay_period_id;
		}

		return result_data;
	},

	parseToRecordId: function( id, index ) {
		if ( !id ) {
			return false;
		}
		id = id.toString();
		if ( id.indexOf( '_' ) > 0 ) {
			if ( index >= 0 ) {
				return id.split( '_' )[index];
			}
			return TTUUID.not_exist_id;
		} else {
			return id;
		}
	},

	/* jshint ignore:end */
	buildContextMenuModels: function() {

		//Context Menu
		var menu = new RibbonMenu( {
			label: this.context_menu_name,
			id: this.viewId + 'ContextMenu',
			sub_menu_groups: []
		} );

		var editor_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Action' ),
			id: this.script_name + 'action',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var authorization_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Authorization' ),
			id: this.script_name + 'authorization',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var objects_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Objects' ),
			id: this.script_name + 'objects',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var navigation_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Navigation' ),
			id: this.viewId + 'navigation',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var other_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Other' ),
			id: this.viewId + 'other',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var view = new RibbonSubMenu( {
			label: $.i18n._( 'View' ),
			id: ContextMenuIconName.view,
			group: editor_group,
			icon: Icons.view,
			permission_result: true,
			permission: null
		} );

		var reply = new RibbonSubMenu( {
			label: $.i18n._( 'Reply' ),
			id: ContextMenuIconName.edit,
			group: editor_group,
			icon: Icons.edit,
			permission_result: true,
			permission: null
		} );

		// var send = new RibbonSubMenu( {
		// 	label: $.i18n._( 'Send' ),
		// 	id: ContextMenuIconName.send,
		// 	group: editor_group,
		// 	icon: Icons.send,
		// 	permission_result: true,
		// 	permission: null
		// } );

		var cancel = new RibbonSubMenu( {
			label: $.i18n._( 'Cancel' ),
			id: ContextMenuIconName.cancel,
			group: editor_group,
			icon: Icons.cancel,
			permission_result: true,
			permission: null
		} );

		var authorization = new RibbonSubMenu( {
			label: $.i18n._( 'Authorize' ),
			id: ContextMenuIconName.authorization,
			group: authorization_group,
			icon: Icons.authorization,
			permission_result: true,
			permission: null
		} );

		var pass = new RibbonSubMenu( {
			label: $.i18n._( 'Pass' ),
			id: ContextMenuIconName.pass,
			group: authorization_group,
			icon: Icons.pass,
			permission_result: true,
			permission: null
		} );

		var decline = new RibbonSubMenu( {
			label: $.i18n._( 'Decline' ),
			id: ContextMenuIconName.decline,
			group: authorization_group,
			icon: Icons.decline,
			permission_result: true,
			permission: null
		} );

		var authorization_request = new RibbonSubMenu( {
			label: $.i18n._( 'Request<br>Authorizations' ),
			id: ContextMenuIconName.authorization_request,
			group: objects_group,
			icon: Icons.authorization_request,
			permission_result: PermissionManager.checkTopLevelPermission( 'RequestAuthorization' ),
			permission: null
		} );

		var authorization_timesheet = new RibbonSubMenu( {
			label: $.i18n._( 'TimeSheet<br>Authorizations' ),
			id: ContextMenuIconName.authorization_timesheet,
			group: objects_group,
			icon: Icons.authorization_timesheet,
			selected: true,
			permission_result: PermissionManager.checkTopLevelPermission( 'TimeSheetAuthorization' ),
			permission: null
		} );

		var authorization_expense = new RibbonSubMenu( {
			label: $.i18n._( 'Expense<br>Authorizations' ),
			id: ContextMenuIconName.authorization_expense,
			group: objects_group,
			icon: Icons.authorization_expense,
			selected: false,
			permission_result: PermissionManager.checkTopLevelPermission( 'ExpenseAuthorization' ),
			permission: null
		} );

		var timesheet = new RibbonSubMenu( {
			label: $.i18n._( 'TimeSheet' ),
			id: ContextMenuIconName.timesheet,
			group: navigation_group,
			icon: Icons.timesheet,
			permission_result: true,
			permission: null
		} );

		var schedule_view = new RibbonSubMenu( {
			label: $.i18n._( 'Schedule' ),
			id: ContextMenuIconName.schedule,
			group: navigation_group,
			icon: Icons.schedule,
			permission_result: true,
			permission: null
		} );

		var employee = new RibbonSubMenu( {
			label: $.i18n._( 'Edit<br>Employee' ),
			id: ContextMenuIconName.edit_employee,
			group: navigation_group,
			icon: Icons.employee,
			permission_result: true,
			permission: null
		} );

		var export_csv = new RibbonSubMenu( {
			label: $.i18n._( 'Export' ),
			id: ContextMenuIconName.export_excel,
			group: other_group,
			icon: Icons.export_excel,
			permission_result: true,
			permission: null,
			sort_order: 9000
		} );

		return [menu];

	},

	setDefaultMenuViewIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuEditIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 && this.editOwnerOrChildPermissionValidate( pId ) && this.parseToRecordId( this.getGridSelectIdArray()[0] ) !== TTUUID.not_exist_id ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuEditIcon: function( context_btn, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}
		if ( !this.is_viewing || !this.editOwnerOrChildPermissionValidate( pId ) || this.parseToRecordId( this.current_edit_record.id ) === TTUUID.not_exist_id ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	getFilterColumnsFromDisplayColumns: function() {
		// Error: Unable to get property 'getGridParam' of undefined or null reference
		var display_columns = [];
		if ( this.grid ) {
			display_columns = this.grid.getGridParam( 'colModel' );
		}

		var column_filter = {};
		column_filter.is_owner = true;
		column_filter.id = true;
		column_filter.user_id = true;
		column_filter.is_child = true;
		column_filter.in_use = true;
		column_filter.first_name = true;
		column_filter.last_name = true;
		column_filter.start_date = true;
		column_filter.end_date = true;
		column_filter.pay_period_id = true;

		if ( display_columns ) {
			var len = display_columns.length;

			for ( var i = 0; i < len; i++ ) {
				var column_info = display_columns[i];
				column_filter[column_info.name] = true;
			}
		}

		return column_filter;
	},

	setDefaultMenuAuthorizationExpenseIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !( Global.getProductEdition() >= 25 ) ) {
			context_btn.addClass( 'invisible-image' );
		}
		context_btn.removeClass( 'disable-image' );
	},

	setDefaultMenu: function( doNotSetFocus ) {

		//Error: Uncaught TypeError: Cannot read property 'length' of undefined in /interface/html5/#!m=Employee&a=edit&id=42411&tab=Wage line 282
		if ( !this.context_menu_array ) {
			return;
		}

		if ( !Global.isSet( doNotSetFocus ) || !doNotSetFocus ) {
			this.selectContextMenu();
		}

		this.setTotalDisplaySpan();

		var len = this.context_menu_array.length;

		var grid_selected_id_array = this.getGridSelectIdArray();

		var grid_selected_length = grid_selected_id_array.length;

		for ( var i = 0; i < len; i++ ) {
			var context_btn = $( this.context_menu_array[i] );
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			context_btn.removeClass( 'invisible-image' );
			context_btn.removeClass( 'disable-image' );
			/* jshint ignore:start */
			switch ( id ) {
				case ContextMenuIconName.edit:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.view:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.send:
					this.setDefaultMenuSaveIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.cancel:
					this.setDefaultMenuCancelIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.authorization:
					this.setDefaultMenuAuthorizationIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.pass:
					this.setDefaultMenuPassIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.decline:
					this.setDefaultMenuDeclineIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.authorization_request:
					this.setDefaultMenuAuthorizationRequestIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.authorization_timesheet:
					this.setDefaultMenuAuthorizationTimesheetIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.timesheet:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'punch' );
					break;
				case ContextMenuIconName.schedule:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'schedule' );
					break;
				case ContextMenuIconName.edit_employee:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'user' );
					break;
				case ContextMenuIconName.authorization_expense:
					this.setDefaultMenuAuthorizationExpenseIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn, grid_selected_length );
					break;

			}

			/* jshint ignore:end */

		}

		this.setContextMenuGroupVisibility();

	},

	setEditMenu: function() {

		this.selectContextMenu();
		var len = this.context_menu_array.length;
		for ( var i = 0; i < len; i++ ) {
			var context_btn = $( this.context_menu_array[i] );
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			context_btn.removeClass( 'disable-image' );
			/* jshint ignore:start */
			switch ( id ) {
				case ContextMenuIconName.edit:
					this.setEditMenuEditIcon( context_btn );
					break;
				case ContextMenuIconName.view:
					this.setEditMenuViewIcon( context_btn );
					break;
				case ContextMenuIconName.send:
					this.setEditMenuSaveIcon( context_btn );
					break;
				case ContextMenuIconName.cancel:
					break;
				case ContextMenuIconName.authorization:
					this.setEditMenuAuthorizationIcon( context_btn );
					break;
				case ContextMenuIconName.pass:
					this.setEditMenuPassIcon( context_btn );
					break;
				case ContextMenuIconName.decline:
					this.setEditMenuDeclineIcon( context_btn );
					break;
				case ContextMenuIconName.authorization_request:
					this.setEditMenuAuthorizationRequestIcon( context_btn );
					break;
				case ContextMenuIconName.authorization_timesheet:
					this.setEditMenuAuthorizationTimesheetIcon( context_btn );
					break;
				case ContextMenuIconName.timesheet:
					this.setEditMenuNavViewIcon( context_btn, 'punch' );
					break;
				case ContextMenuIconName.schedule:
					this.setEditMenuNavViewIcon( context_btn, 'schedule' );
					break;
				case ContextMenuIconName.edit_employee:
					this.setEditMenuNavEditIcon( context_btn, 'user' );
					break;
				case ContextMenuIconName.authorization_expense:
					this.setEditMenuAuthorizationExpenseIcon( context_btn );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn );
					break;

			}

			/* jshint ignore:end */

		}

		this.setContextMenuGroupVisibility();

	},

	setEditMenuAuthorizationExpenseIcon: function( context_btn, pId ) {
		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		} else {
			context_btn.removeClass( 'disable-image' );
		}
	},

	onCustomContextClick: function( id ) {
		switch ( id ) {
			case ContextMenuIconName.authorization_timesheet:
				break;
			case ContextMenuIconName.send:
				this.onSaveClick();
				break;
			case ContextMenuIconName.authorization:
				this.onAuthorizationClick();
				break;
			case ContextMenuIconName.pass:
				this.onPassClick();
				break;
			case ContextMenuIconName.decline:
				this.onDeclineClick();
				break;
			case ContextMenuIconName.authorization_request:
				this.onAuthorizationRequestClick();
				break;
			case ContextMenuIconName.authorization_timesheet:
				this.onAuthorizationTimesheetClick();
				break;
			case ContextMenuIconName.authorization_expense:
				this.onAuthorizationExpenseClick();
				break;
			case ContextMenuIconName.timesheet:
			case ContextMenuIconName.schedule:
			case ContextMenuIconName.edit_employee:
			case ContextMenuIconName.export_excel:
				this.onNavigationClick( id );
				break;
		}
	},

	onAuthorizationExpenseClick: function() {
		IndexViewController.goToView( 'ExpenseAuthorization' );
	},

	onNavigationClick: function( iconName ) {

		var $this = this;

		var grid_selected_id_array;

		var filter = {};

		var ids = [];

		var user_ids = [];

		var base_date;

		if ( $this.edit_view && $this.current_edit_record.id ) {
			ids.push( $this.current_edit_record.id );
			user_ids.push( $this.current_edit_record.user_id );
			base_date = $this.current_edit_record.start_date;
		} else {
			grid_selected_id_array = this.getGridSelectIdArray();
			$.each( grid_selected_id_array, function( index, value ) {
				var grid_selected_row = $this.getRecordFromGridById( value );
				ids.push( grid_selected_row.id );
				user_ids.push( grid_selected_row.user_id );
				base_date = grid_selected_row.start_date;
			} );
		}

		//Error: TypeError: Global.strToDateTime(...) is null in interface/html5/framework/jquery.min.js?v=9.0.0-20151014-164655 line 2 > eval line 552
		base_date = base_date ? Global.strToDateTime( base_date ).format() : new Date().format();

		switch ( iconName ) {
			case ContextMenuIconName.edit_employee:
				if ( user_ids.length > 0 ) {
					IndexViewController.openEditView( this, 'Employee', user_ids[0] );
				}
				break;
			case ContextMenuIconName.timesheet:
				if ( user_ids.length > 0 ) {
					filter.user_id = user_ids[0];
					filter.base_date = base_date;
					Global.addViewTab( $this.viewId, $.i18n._( 'Authorization - TimeSheet' ), window.location.href );
					IndexViewController.goToView( 'TimeSheet', filter );
				}
				break;
			case ContextMenuIconName.schedule:
				filter.filter_data = {};
				var include_users = { value: user_ids };
				filter.filter_data.include_user_ids = include_users;
				filter.select_date = base_date;
				Global.addViewTab( this.viewId, $.i18n._( 'Authorization - TimeSheet' ), window.location.href );
				IndexViewController.goToView( 'Schedule', filter );
				break;

			case ContextMenuIconName.export_excel:
				this.onExportClick( 'export' + this.api.key_name );
				break;
		}

	},

	onSaveClick: function( ignoreWarning ) {
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		if ( this.is_edit ) {

			var $this = this;

			var record = {};

			this.is_add = false;

			record = this.uniformVariable( record );

			this.message_control_api['setMessageControl']( record, false, ignoreWarning, {
				onResult: function( result ) {

					$this.onSaveResult( result );

				}
			} );
		}

	},

	onSaveResult: function( result ) {
		var $this = this;
		var current_edit_record_id;

		if ( result.isValid() ) {

			current_edit_record_id = $this.current_edit_record.id;


			$this.onViewClick( current_edit_record_id, true );

		} else {
			$this.setErrorMenu();
			$this.setErrorTips( result );

		}
	},

	validate: function() {

		var $this = this;

		var record = this.current_edit_record;

		record = this.uniformVariable( record );

		this.message_control_api['validate' + this.message_control_api.key_name]( record, {
			onResult: function( result ) {
				$this.validateResult( result );

			}
		} );
	},

	onAuthorizationClick: function() {
		var $this = this;
		var filter = {};
		filter.authorized = true;
		if ( this.parseToRecordId( $this.current_edit_record.id ) == TTUUID.not_exist_id ) {
			filter.object_id = TTUUID.not_exist_id;
			filter.user_id = $this.current_edit_record.user_id;
			filter.pay_period_id = $this.current_edit_record.pay_period_id;
		} else {
			filter.object_id = $this.current_edit_record.id;
		}
		filter.object_type_id = 90;

		$this.authorization_api['setAuthorization']( [filter], {
			onResult: function( res ) {
				if ( res.isValid() ) {
					$this.onRightArrowClick( function() { $this.search(); } );
				} else {
					TAlertManager.showErrorAlert( res );
				}
			}
		} );

	},

	onPassClick: function() {
		var $this = this;
		this.onRightArrowClick( function() { $this.search(); } );
	},

	onAuthorizationRequestClick: function() {
		IndexViewController.goToView( 'RequestAuthorization' );
	},

	onCancelClick: function( force_no_confirm, cancel_all, callback ) {
		//Refresh grid on cancel as its not done during authorize/decline anymore.
		var $this = this;
		this._super( 'onCancelClick', force_no_confirm, cancel_all, function() { $this.search(); } );
	},

	onDeclineClick: function() {

		var $this = this;
		var filter = {};

		filter.authorized = false;
		if ( this.parseToRecordId( $this.current_edit_record.id ) == TTUUID.not_exist_id ) {
			filter.object_id = TTUUID.not_exist_id;
			filter.user_id = $this.current_edit_record.user_id;
			filter.pay_period_id = $this.current_edit_record.pay_period_id;
		} else {
			filter.object_id = $this.current_edit_record.id;
		}
		filter.object_type_id = 90;

		$this.authorization_api['setAuthorization']( [filter], {
			onResult: function( res ) {
				if ( res.isValid() ) {
					$this.onRightArrowClick( function() { $this.search(); } );
				} else {
					TAlertManager.showErrorAlert( res );
				}
			}
		} );
	},

	onAuthorizationTimesheetClick: function() {
		this.search( false );
	},

	uniformVariable: function( records ) {

		var msg = {};

		if ( this.is_edit && this.current_edit_record != undefined ) {
			msg.body = this.current_edit_record['body'];
			msg.from_user_id = this.current_edit_record['user_id'];
			msg.to_user_id = this.current_edit_record['user_id'];
			msg.object_id = this.current_edit_record['id'];
			msg.object_type_id = 90;
			if ( Global.isFalseOrNull( this.current_edit_record['subject'] ) ) {
				msg.subject = this.edit_view_ui_dic['subject'].getValue();
			} else {
				msg.subject = this.current_edit_record['subject'];
			}
			return msg;
		}
		records.id = this.parseToRecordId( records.id );
		return records;
	},

	onViewClick: function( editId, noRefreshUI ) {
		var $this = this;
		$this.is_viewing = true;
		$this.is_edit = false;
		$this.is_add = false;
		LocalCacheData.current_doing_context_action = 'view';
		$this.openEditView();

		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;

		if ( Global.isSet( editId ) ) {
			var selectedId = editId;
		} else {
			if ( grid_selected_length > 0 ) {
				selectedId = grid_selected_id_array[0];
			} else {
				return;
			}
		}

		filter.filter_data = {};
		if ( this.parseToRecordId( selectedId ) != TTUUID.not_exist_id ) {
			filter.filter_data.id = [selectedId];
		} else {
			filter.filter_data.user_id = this.parseToRecordId( selectedId, 0 );
			filter.filter_data.pay_period_id = this.parseToRecordId( selectedId, 1 );
		}

		this.api['get' + this.api.key_name]( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();
				result_data = $this.processResultData( result_data );
				if ( !result_data ) {
					result_data = [];
				}
				result_data = result_data[0];
				if ( !result_data ) {
					TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
					$this.onCancelClick();
					return;
				}
				$this.current_edit_record = result_data;
				$this.initEditView();

				AuthorizationHistory.init( $this );
			}
		} );

	},

	onGridDblClickRow: function() {

		ProgressBar.showOverlay();
		this.onViewClick();

	},

	setEditMenuViewIcon: function( context_btn, pId ) {
		context_btn.addClass( 'disable-image' );
	},

	setEditMenuAuthorizationIcon: function( context_btn, pId ) {
		if ( this.is_edit ) {
			context_btn.addClass( 'disable-image' );
		} else {
			context_btn.removeClass( 'disable-image' );
		}
	},

	setEditMenuPassIcon: function( context_btn, pId ) {
		if ( this.is_edit ) {
			context_btn.addClass( 'disable-image' );
		} else {
			context_btn.removeClass( 'disable-image' );
		}
	},

	setEditMenuDeclineIcon: function( context_btn, pId ) {
		if ( this.is_edit ) {
			context_btn.addClass( 'disable-image' );
		} else {
			context_btn.removeClass( 'disable-image' );
		}
	},

	setEditMenuAuthorizationRequestIcon: function( context_btn, pId ) {
		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		} else {
			context_btn.removeClass( 'disable-image' );
		}
	},

	setEditMenuAuthorizationTimesheetIcon: function( context_btn, pId ) {
		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		} else {
			context_btn.removeClass( 'disable-image' );
		}
	},

	setDefaultMenuSaveIcon: function( context_btn, grid_selected_length, pId ) {

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuAuthorizationIcon: function( context_btn, grid_selected_length, pId ) {
		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuPassIcon: function( context_btn, grid_selected_length, pId ) {
		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuDeclineIcon: function( context_btn, grid_selected_length, pId ) {
		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuAuthorizationRequestIcon: function( context_btn, grid_selected_length, pId ) {
		context_btn.removeClass( 'disable-image' );
	},

	setDefaultMenuAuthorizationTimesheetIcon: function( context_btn, grid_selected_length, pId ) {
		context_btn.removeClass( 'disable-image' );
	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );
		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Pay Period' ),
				in_column: 1,
				field: 'pay_period_id',
				layout_name: ALayoutIDs.PAY_PERIOD,
				api_class: (APIFactory.getAPIClass( 'APIPayPeriod' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Hierarchy Level' ),
				in_column: 1,
				multiple: false,
				set_any: false,
				field: 'hierarchy_level',
				basic_search: true,
				adv_search: false,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
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
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	},

	onEditClick: function( editId, noRefreshUI ) {

		var $this = this;
		this.is_viewing = false;
		this.is_edit = true;
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'edit';
		$this.openEditView();

		$this.initEditView();

	},

	getSubViewFilter: function( filter ) {

		if ( filter.length === 0 ) {
			filter = {};
		}

		if ( !Global.isSet( filter.hierarchy_level ) ) {
			filter['hierarchy_level'] = 1;
			this.filter_data['hierarchy_level'] = {
				field: 'hierarchy_level',
				id: '',
				value: this.basic_search_field_ui_dic['hierarchy_level'].getValue( true )
			};
		}

		return filter;
	},

	buildEditViewUI: function() {
		this._super( 'buildEditViewUI' );

		var tab_model = {
			'tab_timesheet_verification': { 'label': $.i18n._( 'Message' ) },
		};
		this.setTabModel( tab_model );

		this.navigation = null;

		//Tab 0 start

		var tab_timesheet_verification = this.edit_view_tab.find( '#tab_timesheet_verification' );
		var tab_timesheet_verification_column1 = tab_timesheet_verification.find( '.first-column' );
		var tab_timesheet_verification_column2 = tab_timesheet_verification.find( '.second-column' );
		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( tab_timesheet_verification_column1 );

		// Subject
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'subject', width: 359 } );
		this.addEditFieldToColumn( $.i18n._( 'Subject' ), form_item_input, tab_timesheet_verification_column1, '' );

		// Body
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'body', width: 600, height: 400 } );
		this.addEditFieldToColumn( $.i18n._( 'Body' ), form_item_input, tab_timesheet_verification_column1, '', null, null, true );
		tab_timesheet_verification_column2.css( 'display', 'none' );

	},

	needShowNavigation: function() {
		if ( this.is_viewing && this.current_edit_record && Global.isSet( this.current_edit_record.id ) && this.current_edit_record.id ) {
			return true;
		} else {
			return false;
		}
	},

	buildViewUI: function() {
		var pager_data = this.navigation && this.navigation.getPagerData && this.navigation.getPagerData();
		var source_data = this.navigation && this.navigation.getSourceData && this.navigation.getSourceData();
		this._super( 'buildEditViewUI' );

		var $this = this;

		var tab_model = {
			'tab_timesheet_verification': { 'label': $.i18n._( 'TimeSheet Verification' ) },
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayPeriodTimeSheetVerify' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_PERIOD,
			navigation_mode: true,
			show_search_inputs: true,
			extendDataProcessWhenSearch: this.processResultData
		} );

		this.setNavigation();

		if ( pager_data && source_data ) {
			this.navigation.setSourceData( source_data );
			this.navigation.setPagerData( pager_data );
		}

		//Tab 0 first column start

		var tab_timesheet_verification = this.edit_view_tab.find( '#tab_timesheet_verification' );
		var tab_timesheet_verification_column1 = tab_timesheet_verification.find( '.first-column' );

		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( tab_timesheet_verification_column1 );

		// Employee
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'full_name', selected_able: true } );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_timesheet_verification_column1, '' );

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'status', selected_able: true } );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_timesheet_verification_column1, '' );

		// Pay Period
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'pay_period', selected_able: true } );
		this.addEditFieldToColumn( $.i18n._( 'Pay Period' ), form_item_input, tab_timesheet_verification_column1 );

		// tab_timesheet_verification first column end

		var separate_box = tab_timesheet_verification.find( '.separate' );

		// Messages

		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Messages' ) } );
		this.addEditFieldToColumn( null, form_item_input, separate_box );

		separate_box.css( 'display', 'none' );

		// Tab 0 second column start

		var tab_timesheet_verification_column2 = tab_timesheet_verification.find( '.second-column' );

		this.edit_view_tabs[0].push( tab_timesheet_verification_column2 );

		// From
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'from', selected_able: true } );
		this.addEditFieldToColumn( $.i18n._( 'From' ), form_item_input, tab_timesheet_verification_column2, '' );

		// Subject
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'subject', selected_able: true } );
		this.addEditFieldToColumn( $.i18n._( 'Subject' ), form_item_input, tab_timesheet_verification_column2 );

		// Body
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'body', width: 600, height: 400, selected_able: true } );
		this.addEditFieldToColumn( $.i18n._( 'Body' ), form_item_input, tab_timesheet_verification_column2, '', null, true, true );

		// Tab 0 second column end

		tab_timesheet_verification_column2.css( 'display', 'none' );

	},

	initEditViewUI: function( view_id, edit_view_file_name ) {
		Global.setUINotready();
		TTPromise.add( 'init', 'init' );
		TTPromise.wait();
		var $this = this;

		if ( this.edit_view ) {
			this.edit_view.remove();
		}

		this.edit_view = $( Global.loadViewSource( view_id, edit_view_file_name, null, true ) );

		this.edit_view_tab = $( this.edit_view.find( '.edit-view-tab-bar' ) );

		//Give edt view tab a id, so we can load it when put right click menu on it
		this.edit_view_tab.attr( 'id', this.ui_id + '_edit_view_tab' );

		this.setTabOVisibility( false );

		this.edit_view_tab = this.edit_view_tab.tabs( {
			activate: function( e, ui ) {
				$this.onTabShow( e, ui );
			}
		} );

		this.edit_view_tab.bind( 'tabsselect', function( e, ui ) {
			$this.onTabIndexChange( e, ui );
		} );

		Global.contentContainer().append( this.edit_view );
		this.initRightClickMenu( RightClickMenuType.EDITVIEW );

		if ( this.is_viewing ) {
			LocalCacheData.current_doing_context_action = 'view';
			this.buildViewUI();
		} else if ( this.is_edit ) {
			LocalCacheData.current_doing_context_action = 'edit';
			this.buildEditViewUI();
		}

		$this.setEditViewTabHeight();
	},

	setCurrentEditRecordData: function() {

		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'full_name':
						if ( this.is_viewing ) {
							widget.setValue( this.current_edit_record['first_name'] + ' ' + this.current_edit_record['last_name'] );
						}
						break;
					case 'pay_period':
						widget.setValue( this.current_edit_record['start_date'] + ' ' + this.current_edit_record['end_date'] );
						break;
					case 'subject':
						if ( this.is_edit ) {
							if ( Global.isSet( this.messages ) ) {
								widget.setValue( 'Re: ' + this.messages[0].subject );
							}
						} else if ( this.is_viewing ) {
							widget.setValue( this.current_edit_record[key] );
						}
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();

	},

	setEditViewDataDone: function() {
		var $this = this;
		this._super( 'setEditViewDataDone' );

		if ( this.is_viewing ) {
			this.initTimeSheetSummary();
			this.initExceptionSummary();
			//this.initEmbeddedMessageData();
		} else {
			if ( Global.isSet( $this.messages ) ) {
				$this.messages = null;
			}
		}
	},

	buildExceptionDisplayColumns: function( apiDisplayColumnsArray ) {
		var len = this.all_exception_columns.length;
		var len1 = apiDisplayColumnsArray ? apiDisplayColumnsArray.length : 0;
		var display_columns = [];
		for ( var j = 0; j < len1; j++ ) {
			for ( var i = 0; i < len; i++ ) {
				if ( apiDisplayColumnsArray[j] === this.all_exception_columns[i].value ) {
					display_columns.push( this.all_exception_columns[i] );
				}
			}
		}
		return display_columns;
	},

	initExceptionSummary: function() {

		var $this = this;
		if ( !this.api_exception ) {
			this.api_exception = new (APIFactory.getAPIClass( 'APIException' ))();
		}

		$this.buildExceptionGrid();
		$this.setExceptionGridSize();
		this.api_exception.getOptions( 'columns', {
			onResult: function( columns_result ) {
				var columns_result_data = columns_result.getResult();
				if ( Global.isSet( $this.current_edit_record ) == false ) {
					return false;
				}
				var args = {
					filter_data: {
						user_id: $this.current_edit_record.user_id,
						pay_period_id: $this.current_edit_record.pay_period_id,
						type_id: [30, 40, 50, 55, 60]
					}
				};

				$this.api_exception.getException( args, {
					onResult: function( result ) {
						$this.all_exception_columns = Global.buildColumnArray( columns_result_data );
						var grid;
						if ( !Global.isSet( $this.exception_grid ) ) {
							grid = $( '#exception_grid' );
						}
						var display_columns = [
							'date_stamp',
							'severity',
							'exception_policy_type',
							'exception_policy_type_id',
							'exception_color',
							'exception_background_color'
						];
						display_columns = $this.buildExceptionDisplayColumns( display_columns );
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
						$this.buildExceptionGrid( column_info_array );

						var result_data = result.getResult();
						if ( !Global.isArray( result_data ) && TTUUID.isUUID( $this.refresh_id ) == false ) {
							$this.showExceptionGridNoResultCover();
						} else {
							$this.removeExceptionGridNoResultCover();
							$this.exception_grid.setData( Global.formatGridData( result.getResult() ) );
						}

						$this.setExceptionGridSize();

						$( '.exception-title' ).text( $.i18n._( 'Exceptions' ) );
						$( '.exception-title' ).css( 'width' ,($this.exception_grid.grid.width() - 1)+'px !important' );
						$this.setGridCellBackGround();
					}
				} );
			}
		} );
	},

	buildExceptionGrid: function( column_info_array ) {
		var $this = this;

		if ( typeof column_info_array == 'undefined' ) {
			column_info_array = [];
		}


		if ( this.exception_grid ) {
			this.exception_grid.grid.jqGrid( 'GridUnload' );
			this.exception_grid = null;
		}
		this.exception_grid = new TTGrid( 'exception_grid', {
			onResizeGrid: false,
			multiselect: false,
			winMultiSelect: false,
			gridComplete: function() {
				if ( $( this ).jqGrid( 'getGridParam', 'data' ).length > 0 ) {
					$this.exception_grid.setGridColumnsWidth();
				}
			},
			ondblClickRow: function( row_id ) {
				$this.onExceptionGridDblClickRow( row_id );
			},
			sortable: false,
			height: 160
		}, column_info_array );
	},

	showExceptionGridNoResultCover: function() {
		this.removeExceptionGridNoResultCover();
		this.exception_grid_no_result_box = Global.loadWidgetByName( WidgetNamesDic.NO_RESULT_BOX );
		this.exception_grid_no_result_box.NoResultBox( { related_view_controller: this, is_new: false } );
		var grid_div = $( '.exception-grid-div' );
		grid_div.css( 'position', 'relative' );
		this.exception_grid_no_result_box.attr( 'id', '#exception_grid_no_result_box' );
		this.exception_grid_no_result_box.css( 'width', parseInt( grid_div.width() ) + 'px' );
		this.exception_grid_no_result_box.css( 'height', parseInt( grid_div.height() ) + 'px' );

		grid_div.append( this.exception_grid_no_result_box );
	},

	removeExceptionGridNoResultCover: function() {
		if ( this.exception_grid_no_result_box && this.exception_grid_no_result_box.length > 0 ) {
			this.exception_grid_no_result_box.remove();
		}
		this.exception_grid_no_result_box = null;
	},

	setEditViewTabSize: function() {
		this._super( 'setEditViewTabSize' );
		this.setExceptionGridSize();
		this.setTimeSheetSummaryGridSize();
	},

	setGridCellBackGround: function() {
		var data;
		var len;
		var i;
		var item;
		if ( !this.exception_grid || !this.edit_view ) {
			return;
		}
		data = this.exception_grid.getGridParam( 'data' );
		//Error: TypeError: data is undefined in /interface/html5/framework/jquery.min.js?v=7.4.6-20141027-074127 line 2 > eval line 70
		if ( !data ) {
			return;
		}
		len = data.length;
		for ( i = 0; i < len; i++ ) {
			item = data[i];
			if ( item.exception_background_color ) {
				var severity = this.edit_view.find( 'tr[id=\'' + item.id + '\']' ).find( 'td[aria-describedby="exception_grid_severity"]' );
				severity.css( 'background-color', item.exception_background_color );
				severity.css( 'font-weight', 'bold' );
			}
			if ( item.exception_color ) {
				var code = this.edit_view.find( 'tr[id=\'' + item.id + '\']' ).find( 'td[aria-describedby="exception_grid_exception_policy_type_id"]' );
				code.css( 'color', item.exception_color );
				code.css( 'font-weight', 'bold' );
			}
		}

	},


	initTimeSheetSummary: function() {
		var $this = this;

		if ( Global.isSet( this.current_edit_record ) == false ) {
			return false;
		}

		this.accumulated_total_grid_source_map = {};
		if ( !this.api_timesheet ) {
			this.api_timesheet = new (APIFactory.getAPIClass( 'APITimeSheet' ))();
		}

		$this.buildAccumulatedTotalGrid();
		this.api_timesheet.getTimeSheetData( this.current_edit_record.user_id, this.current_edit_record.start_date, {
			onResult: function( result ) {

				if ( Global.isSet( $this.current_edit_record ) == false ) {
					return false;
				}

				$this.full_timesheet_data = result.getResult();
				$this.pay_period_data = $this.full_timesheet_data.pay_period_data;
				$this.timesheet_verify_data = $this.full_timesheet_data.timesheet_verify_data;
				$this.start_date = Global.strToDate( $this.full_timesheet_data.timesheet_dates.start_display_date );
				$this.end_date = Global.strToDate( $this.full_timesheet_data.timesheet_dates.end_display_date );
				var columns = [];
				var punch_in_out_column = {
					name: 'punch_info',
					index: 'punch_info',
					label: ' ',
					width: 200,
					fixed: true,
					sortable: false,
					title: false,
					formatter: $this.onCellFormat
				};
				columns.push( punch_in_out_column );
				var start_date_str = $this.current_edit_record.start_date;
				var end_date_str = $this.current_edit_record.end_date;
				$this.getAccumulatedTotalGridPayperiodHeader();
				var column_1 = {
					name: 'week',
					index: 'week',
					label: $.i18n._( 'Week' ) + '<br>' + start_date_str + ' to ' + end_date_str,
					width: 100,
					sortable: false,
					title: false,
					formatter: $this.onCellFormat
				};
				var column_2 = {
					name: 'pay_period',
					index: 'pay_period',
					label: $.i18n._( 'Pay Period' ) + '<br>' + $this.pay_period_header,
					width: 100,
					sortable: false,
					title: false,
					formatter: $this.onCellFormat
				};
				columns.push( column_2 );
				$this.buildAccumulatedTotalGrid( columns );
				$this.buildAccumulatedTotalData();
				$( '.button-rotate' ).removeClass( 'button-rotate' );
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

	//This function is copied from TimeSheetViewController.js
	buildSubGridsData: function( array, date_string, map, result_array, parent_key ) {
		var row;
		var marked_regular_row = false; //Only mark the first regular time row, as thats where the bold top-line is going to go.
		for ( var key  in array ) {
			if ( !map[key] ) {
				row = {};
				row.parent_key = parent_key;
				row.key = key;

				if ( parent_key === 'accumulated_time' ) {
					if ( key === 'total' || key === 'worked_time' ) {
						row.type = TimeSheetAuthorizationViewController.TOTAL_ROW;
					} else if ( marked_regular_row == false && key.indexOf( 'regular_time' ) === 0 ) {
						row.type = TimeSheetAuthorizationViewController.REGULAR_ROW;
						marked_regular_row = true;
					} else {
						row.type = TimeSheetAuthorizationViewController.ACCUMULATED_TIME_ROW;
					}

					if ( array[key].override ) {
						row.is_override_row = true;
					}
				} else if ( parent_key === 'premium_time' ) {
					row.type = TimeSheetAuthorizationViewController.PREMIUM_ROW;
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
				row[date_string] = Global.getTimeUnit( array[key].total_time );
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
					row[date_string] = Global.getTimeUnit( array[key].total_time );
					row[date_string + '_data'] = array[key];
					if ( row.parent_key === 'accumulated_time' ) {
						if ( array[key].override ) {
							row.is_override_row = true;
						}
					}
				} else {
					array[key].key = key;
					row[date_string] = Global.getTimeUnit( array[key].total_time );
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

	buildAccumulatedTotalData: function() {
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
			accumulated_time = { total: { label: 'Total Time', total_time: '0' } };
			this.buildSubGridsData( accumulated_time, 'pay_period', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'accumulated_time' );
		}
		if ( Global.isSet( premium_time ) ) {
			this.buildSubGridsData( premium_time, 'pay_period', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'premium_time' );
		}
		if ( Global.isSet( absence_time ) ) {
			this.buildSubGridsData( absence_time, 'pay_period', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'absence_time' );
		}
		accumulated_time = { total: { label: 'Total Time', total_time: '0' } };
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
		this.timesheet_authorization_summary_grid.setData( this.accumulated_total_grid_source );

		$( '.timesheet-authorization-summary-title' ).text( $.i18n._( 'TimeSheet Summary' ) )
	},

	sortAccumulatedTotalData: function() {
		var sort_fields = ['order', 'punch_info'];
		this.accumulated_total_grid_source.sort( Global.m_sort_by( sort_fields ) );
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

	buildAccumulatedTotalGrid: function( columns ) {
		var $this = this;
		var grid;
		if ( typeof columns == 'undefined' ) {
			columns = [];
		}
		if ( this.timesheet_authorization_summary_grid ) {
			this.timesheet_authorization_summary_grid.grid.jqGrid( 'GridUnload' );
			this.timesheet_authorization_summary_grid = null;
		}
		var $this = this;
		this.timesheet_authorization_summary_grid = new TTGrid( 'timesheet_authorization_summary_grid', {
			multiselect: false,
			winMultiSelect: false,
			sortable: false,
			onResizeGrid: false,
			gridComplete: function() {
				$this.setTimeSheetSummaryGridSize();
			},
			ondblClickRow: function() {
				$this.onTimeSheetGridDblClickRow();
			}
		}, columns );
		this.setTimeSheetSummaryGridSize();
	},

	onTimeSheetGridDblClickRow: function() {
		var filter = { filter_data: {} };
		filter.user_id = this.current_edit_record.user_id;
		filter.base_date = Global.strToDateTime( this.current_edit_record.start_date ).format();
		Global.addViewTab( this.viewId, $.i18n._( 'TimeSheet (Authorizations)' ), window.location.href );
		IndexViewController.goToView( 'TimeSheet', filter );
	},

	onExceptionGridDblClickRow: function( row_id ) {
		var date_stamp = this.exception_grid.grid.jqGrid( 'getCell', row_id, 'date_stamp' );

		if ( !date_stamp ) {
			date_stamp = Global.strToDateTime( this.current_edit_record.start_date ).format();
		}

		var filter = { filter_data: {} };
		filter.user_id = this.current_edit_record.user_id;
		filter.base_date = date_stamp;
		Global.addViewTab( this.viewId, $.i18n._( 'TimeSheet (Authorizations)' ), window.location.href );
		IndexViewController.goToView( 'TimeSheet', filter );
	},

	onCellFormat: function( cell_value, related_data, row ) {
		cell_value = Global.decodeCellValue( cell_value );
		var col_model = related_data.colModel;
		var row_id = related_data.rowid;
		var content_div = $( '<div class=\'punch-content-div\'></div>' );
		var punch_info;
		if ( related_data.pos === 0 ) {
			if ( row.type === TimeSheetAuthorizationViewController.TOTAL_ROW ) {
				punch_info = $( '<span class=\'total\' style=\'font-size: 11px\'></span>' );
				if ( Global.isSet( cell_value ) ) {
					punch_info.text( cell_value );
				} else {
					punch_info.text( '' );
				}
				return punch_info.get( 0 ).outerHTML;
			} else if ( row.type === TimeSheetAuthorizationViewController.REGULAR_ROW ) {
				punch_info = $( '<span class=\'top-line-span\' style=\'font-size: 11px\'></span>' );
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
		if ( row.type === TimeSheetAuthorizationViewController.TOTAL_ROW ) {
			data = row[col_model.name + '_data'];
			time_span = $( '<span class=\'total\'></span>' );
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
		} else if ( row.type === TimeSheetAuthorizationViewController.REGULAR_ROW ) {
			content_div.addClass( 'top-line' );
			data = row[col_model.name + '_data'];
			time_span = $( '<span ></span>' );
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
		} else if ( row.type === TimeSheetAuthorizationViewController.ACCUMULATED_TIME_ROW ) {
			data = row[col_model.name + '_data'];
			time_span = $( '<span></span>' );
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
			time_span = $( '<span class=\'punch-time\'></span>' );
			if ( Global.isSet( cell_value ) ) {
				time_span.text( cell_value );
			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );
		}
		return content_div.get( 0 ).outerHTML;
	},

	setExceptionGridSize: function() {
		if ( this.exception_grid ) {
			this.exception_grid.grid.setGridWidth( $( '.exception-grid-div' ).width() + 1 );

			var size = 2;

			var data_array = this.exception_grid.getData();
			if ( Global.isArray( data_array ) ) {
				size += ( 22 * data_array.length )
			}

			this.exception_grid.setGridHeight( size );
		}
	},

	setTimeSheetSummaryGridSize: function() {
		if ( this.timesheet_authorization_summary_grid ) {
			this.timesheet_authorization_summary_grid.grid.setGridWidth( $( '.timesheet-authorization-grid-div' ).width() );

			var size = 2;

			var data_array = this.timesheet_authorization_summary_grid.getData();
			if ( Global.isArray( data_array ) ) {
				size += ( 22 * data_array.length )
			}

			this.timesheet_authorization_summary_grid.setGridHeight( size );
		}
	}

} );

TimeSheetAuthorizationViewController.TOTAL_ROW = 4;
TimeSheetAuthorizationViewController.REGULAR_ROW = 5;
TimeSheetAuthorizationViewController.ABSENCE_ROW = 6;
TimeSheetAuthorizationViewController.ACCUMULATED_TIME_ROW = 7;
TimeSheetAuthorizationViewController.PREMIUM_ROW = 8;
