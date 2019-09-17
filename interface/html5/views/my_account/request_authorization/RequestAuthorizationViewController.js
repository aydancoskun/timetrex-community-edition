RequestAuthorizationViewController = RequestViewCommonController.extend( {
	el: '#request_authorization_view_container',

	_required_files: {
		10: ['APIRequest', 'APIAuthorization', 'APIAbsencePolicy', 'APIMessageControl', 'APISchedulePolicy', 'APISchedule', 'APIBranch', 'APIDepartment'],
		20: ['APIJob', 'APIJobItem']
	},

	type_array: null,
	hierarchy_level_array: null,

	messages: null,

	authorization_api: null,
	api_request: null,
	api_absence_policy: null,
	message_control_api: null,

	authorization_history_columns: [],

	authorization_history_default_display_columns: [],

	authorization_history_grid: null,
	pre_request_schedule: true,

	init: function( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'RequestAuthorizationEditView.html';
		this.permission_id = 'request';
		this.viewId = 'RequestAuthorization';
		this.script_name = 'RequestAuthorizationView';
		this.table_name_key = 'request';
		this.context_menu_name = $.i18n._( 'Request (Authorizations)' );
		this.navigation_label = $.i18n._( 'Requests' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIRequest' ))();
		this.authorization_api = new (APIFactory.getAPIClass( 'APIAuthorization' ))();
		this.api_request = new (APIFactory.getAPIClass( 'APIRequest' ))();
		this.api_absence_policy = new (APIFactory.getAPIClass( 'APIAbsencePolicy' ))();
		this.message_control_api = new (APIFactory.getAPIClass( 'APIMessageControl' ))();

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
			this.job_api = new (APIFactory.getAPIClass( 'APIJob' ))();
			this.job_item_api = new (APIFactory.getAPIClass( 'APIJobItem' ))();
		}
		this.message_control_api = new (APIFactory.getAPIClass( 'APIMessageControl' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.add] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.mass_edit] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.delete_icon] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.delete_and_next] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.copy_as_new] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_continue] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_next] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_copy] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_new] = true;

		this.initPermission();
		this.render();

		this.buildContextMenu( true );

		this.initData();
		this.setSelectRibbonMenuIfNecessary();
	},

	initOptions: function() {
		var $this = this;

		this.initDropDownOption( 'type' );
		var res = this.api.getHierarchyLevelOptions( [-1], { async: false } );
		var data = res.getResult();
		$this['hierarchy_level_array'] = Global.buildRecordArray( data );
		if ( Global.isSet( $this.basic_search_field_ui_dic['hierarchy_level'] ) ) {
			$this.basic_search_field_ui_dic['hierarchy_level'].setSourceData( Global.buildRecordArray( data ) );
		}
	},

	initPermission: function() {
		this._super( 'initPermission' );

		if ( this.jobUIValidate() ) {
			this.show_job_ui = true;
		} else {
			this.show_job_ui = false;
		}

		if ( this.jobItemUIValidate() ) {
			this.show_job_item_ui = true;
		} else {
			this.show_job_item_ui = false;
		}

		if ( this.branchUIValidate() ) {
			this.show_branch_ui = true;
		} else {
			this.show_branch_ui = false;
		}

		if ( this.departmentUIValidate() ) {
			this.show_department_ui = true;
		} else {
			this.show_department_ui = false;
		}

		// Error: Uncaught TypeError: (intermediate value).isBranchAndDepartmentAndJobAndJobItemEnabled is not a function on line 207
		var company_api = new (APIFactory.getAPIClass( 'APICompany' ))();
		if ( company_api && _.isFunction( company_api.isBranchAndDepartmentAndJobAndJobItemEnabled ) ) {
			result = company_api.isBranchAndDepartmentAndJobAndJobItemEnabled( { async: false } ).getResult();
		}

		if ( !result ) {
			this.show_branch_ui = false;
			this.show_department_ui = false;
			this.show_job_ui = false;
			this.show_job_item_ui = false;
		} else {
			if ( !result.branch ) {
				this.show_branch_ui = false;
			}

			if ( !result.department ) {
				this.show_department_ui = false;
			}

			if ( !result.job ) {
				this.show_job_ui = false;
			}

			if ( !result.job_item ) {
				this.show_job_item_ui = false;
			}
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

		var send = new RibbonSubMenu( {
			label: $.i18n._( 'Send' ),
			id: ContextMenuIconName.send,
			group: editor_group,
			icon: Icons.send,
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
			selected: true,
			permission_result: PermissionManager.checkTopLevelPermission( 'RequestAuthorization' ),
			permission: null
		} );

		var authorization_timesheet = new RibbonSubMenu( {
			label: $.i18n._( 'TimeSheet<br>Authorizations' ),
			id: ContextMenuIconName.authorization_timesheet,
			group: objects_group,
			icon: Icons.authorization_timesheet,
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
					this.setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.authorization_expense:
					this.setDefaultMenuAuthorizationExpenseIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn );
					break;

			}

			/* jshint ignore:end */

		}

		this.setContextMenuGroupVisibility();

	},

	setDefaultMenuAuthorizationExpenseIcon: function( context_btn, grid_selected_length, pId ) {

		if ( !( LocalCacheData.getCurrentCompany().product_edition_id >= 25 ) ) {
			context_btn.addClass( 'invisible-image' );
		}

		context_btn.removeClass( 'disable-image' );
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
					this.setEditMenuEditIcon( context_btn, 'request' );
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
				this.onNavigationClick( id );
				break;
		}
	},

	onViewclick: function() {
		this._super( 'onViewclick' );
		AuthorizationHistory.init( this );
	},

	onAuthorizationExpenseClick: function() {
		IndexViewController.goToView( 'ExpenseAuthorization' );
	},

	onSaveClick: function( ignoreWarning ) {
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		if ( this.is_edit ) {

			var $this = this;
			var record;
			this.is_add = false;

			record = this.current_edit_record;

			record = this.uniformVariable( record );

			EmbeddedMessage.reply( record, ignoreWarning, function( result ) {
				if ( result.isValid() ) {
					var id = $this.current_edit_record.id;

					//see #2224 - Unable to get property 'find' of undefined
					$this.removeEditView();
					$this.onViewClick( id );
				} else {
					$this.setErrorTips( result );
					$this.setErrorMenu();
				}
			} );
		}
	},

	onAuthorizationClick: function() {
		var $this = this;

		//Error: TypeError: $this.current_edit_record is null in /interface/html5/framework/jquery.min.js?v=7.4.6-20141027-074127 line 2 > eval line 629
		if ( !$this.current_edit_record ) {
			return;
		}

		var request_data;
		if ( LocalCacheData.getCurrentCompany().product_edition_id >= 15 && ( this.current_edit_record.type_id == 30 || this.current_edit_record.type_id == 40 ) ) {
			request_data = $this.buildDataForAPI( this.current_edit_record );
		} else {
			request_data = this.getSelectedItem();
		}

		//Check if Edit permissions exist, if not, only authorize the request to avoid a API permission error.
		if ( this.enable_edit_view_ui == true ) {
			$this.api_request['setRequest'](request_data, {
				onResult: function (res) {
					if (res.getResult() != false) {
						authorizeRequest();
					} else {
						$this.setErrorMenu();
						$this.setErrorTips(res, true);
					}
				},
			});
		} else {
			authorizeRequest();
		}

		function authorizeRequest() {
			if ( $this.current_edit_record ) {
				var filter = {};
				filter.authorized = true;
				filter.object_id = $this.current_edit_record.id;
				filter.object_type_id = $this.current_edit_record.hierarchy_type_id;

				$this.authorization_api['setAuthorization']( [filter], {
					onResult: function ( result ) {
						var retval = result.getResult();
						if ( retval != false ) {
							$this.is_changed = false;
							$this.onRightArrowClick( $this.search() );
						} else {
							$this.setErrorMenu();
							$this.setErrorTips( result, true );
						}
					}
				} );
			}
		}
	},

	onPassClick: function() {
		var $this = this;

		function doNext() {
			$this.onRightArrowClick( function() { $this.search(); } );
		}

		if ( this.is_changed ) {
			TAlertManager.showConfirmAlert( Global.modify_alert_message, null, function( flag ) {
				if ( flag === true ) {
					doNext();
				}
			} );
		} else {
			doNext();
		}
	},

	onAuthorizationRequestClick: function() {
		this.search( false );
	},

	onDeclineClick: function() {
		var $this = this;

		function doNext() {
			var filter = {};

			filter.authorized = false;
			filter.object_id = $this.current_edit_record.id;
			filter.object_type_id = $this.current_edit_record.hierarchy_type_id;

			$this.authorization_api['setAuthorization']( [filter], {
				onResult: function( res ) {
					$this.onRightArrowClick( function() { $this.search(); } );
				}
			} );
		}

		if ( this.is_changed ) {
			TAlertManager.showConfirmAlert( Global.modify_alert_message, null, function( flag ) {
				if ( flag === true ) {
					doNext();
				}
			} );
		} else {
			doNext();
		}
	},

	onAuthorizationTimesheetClick: function() {
		IndexViewController.goToView( 'TimeSheetAuthorization' );
	},

	uniformVariable: function( records ) {
		if ( this.is_edit ) {
			return this.uniformMessageVariable( records );
		}
		return records;
	},

	onGridDblClickRow: function() {

		ProgressBar.showOverlay();
		this.onViewClick();

	},

	setEditMenuViewIcon: function( context_btn, pId ) {
		context_btn.addClass( 'disable-image' );
	},

	onSaveResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {

			$this.is_add = false;
			var result_data = result.getResult();
			if ( !this.edit_only_mode ) {
				if ( result_data === true ) {
					$this.refresh_id = $this.current_edit_record.id;
				} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
					$this.refresh_id = result_data;
				}

				$this.search();
			}

			$this.onSaveDone( result );

			if ( $this.is_edit ) {
				$this.onViewClick( $this.current_edit_record.id );
			} else {

				$this.removeEditView();
			}

		} else {
			$this.setErrorMenu();
			$this.setErrorTips( result );

		}
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

	setDefaultMenuEditIcon: function( context_btn, grid_selected_length, pId ) {
		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuEditEmployeeIcon: function( context_btn, grid_selected_length ) {
		if ( !this.editPermissionValidate( 'user' ) ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
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
				label: $.i18n._( 'Type' ),
				in_column: 1,
				multiple: true,
				field: 'type_id',
				basic_search: true,
				adv_search: false,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Start Date' ),
				in_column: 1,
				field: 'start_date',
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.DATE_PICKER
			} ),

			new SearchField( {
				label: $.i18n._( 'End Date' ),
				in_column: 1,
				field: 'end_date',
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.DATE_PICKER
			} ),

			new SearchField( {
				label: $.i18n._( 'Hierarchy Level' ),
				in_column: 2,
				multiple: false,
				field: 'hierarchy_level',
				basic_search: true,
				adv_search: false,
				set_any: false,
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

	onFormItemChange: function( target, doNotValidate ) {
		var $this = this;
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		this.current_edit_record[key] = c_value;
		switch ( key ) {
			case 'job_id':
				if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
					this.edit_view_ui_dic['job_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
					this.setJobItemValueWhenJobChanged( target.getValue( true ), 'job_item_id' );
					this.edit_view_ui_dic['job_quick_search'].setCheckBox( true );
				}
				break;
			case 'job_item_id':
				if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
					this.edit_view_ui_dic['job_item_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
					this.edit_view_ui_dic['job_item_quick_search'].setCheckBox( true );
				}
				break;
			case 'job_quick_search':
			case 'job_item_quick_search':
				if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
					this.onJobQuickSearch( key, c_value, 'job_id', 'job_item_id' );
				}
				break;
			case 'type_id':
				this.onTypeChanged();
				break;

			case 'date_stamp':
				this.onDateStampChanged();
				break;
			case 'request_schedule_status_id':
				this.onWorkingStatusChanged();
				break;
			case 'start_date':
				$this.getScheduleTotalTime();
				$this.onStartDateChanged();
				$this.current_edit_record.start_date = $this.edit_view_ui_dic.start_date.getValue();
				$this.current_edit_record.date_stamp = $this.edit_view_ui_dic.start_date.getValue();
				if ( $this.edit_view_ui_dic.date_stamp ) {
					$this.edit_view_ui_dic.date_stamp.setValue( $this.edit_view_ui_dic.start_date.getValue() );
				}
				break;
			case 'end_date':
				$this.getScheduleTotalTime();
				$this.current_edit_record.end_date = $this.edit_view_ui_dic.end_date.getValue();
				break;
			case 'sun':
			case 'tue':
			case 'wed':
			case 'thu':
			case 'fri':
			case 'sat':
			case 'start_time':
			case 'end_time':
			case 'schedule_policy_id':
				$this.getScheduleTotalTime();
				break;
			case'absence_policy_id':
				this.selected_absence_policy_record = this.edit_view_ui_dic.absence_policy_id.getValue();
				this.getAvailableBalance();
				break;
		}

		if ( key === 'date_stamp' ||
				key === 'start_date_stamps' ||
				key === 'start_date_stamp' ||
				key === 'start_time' ||
				key === 'end_time' ||
				key === 'schedule_policy_id' ||
				key === 'absence_policy_id' ) {

			if ( this.current_edit_record['date_stamp'] !== '' &&
					this.current_edit_record['start_time'] !== '' &&
					this.current_edit_record['end_time'] !== '' ) {

				var startTime = this.current_edit_record['date_stamp'] + ' ' + this.current_edit_record['start_time'];
				var endTime = this.current_edit_record['date_stamp'] + ' ' + this.current_edit_record['end_time'];
				var schedulePolicyId = this.current_edit_record['schedule_policy_id'];
				var user_id = this.current_edit_record.user_id;

				this.getScheduleTotalTime();

			} else {
				this.onAvailableBalanceChange();
			}

		}

		if ( !doNotValidate ) {
			this.validate();
		}

	},

	//set widget disablebility if view mode or edit mode
	setEditViewWidgetsMode: function() {
		var did_clean_dic = {};
		for ( var key in this.edit_view_ui_dic ) {
			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}
			var widget = this.edit_view_ui_dic[key];
			widget.css( 'opacity', 1 );
			var column = widget.parent().parent().parent();
			var tab_id = column.parent().attr( 'id' );
			if ( !column.hasClass( 'v-box' ) ) {
				if ( !did_clean_dic[tab_id] ) {
					did_clean_dic[tab_id] = true;
				}
			}
			if ( Global.isSet( widget.setEnabled ) ) {
				widget.setEnabled( this.enable_edit_view_ui );
			}
		}
	},

	onAvailableBalanceChange: function() {
		this.getAvailableBalance();
	},

	setURL: function() {

		if ( LocalCacheData.current_doing_context_action === 'edit' ) {
			LocalCacheData.current_doing_context_action = '';
			return;
		}

		this._super( 'setURL' );
	},

	getSubViewFilter: function( filter ) {
		if ( filter.length === 0 ) {
			filter = {};
		}

		if ( !Global.isSet( filter.type_id ) ) {
			filter['type_id'] = [-1];
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
			'tab_request': { 'label': $.i18n._( 'Message' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		//This hides the audit tab as this view is always used for creating/replying to an existing request.
		//For some reason removing 'tab_audit' from the model above results in a blank tab appearing.
		var tab_audit_label = this.edit_view.find( 'a[ref=tab_audit]' );
		tab_audit_label.css( 'display', 'none' );

		//Tab 0 start

		var tab_request = this.edit_view_tab.find( '#tab_request' );

		var tab_request_column1 = tab_request.find( '.first-column' );

		var tab_request_column2 = tab_request.find( '.second-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_request_column1 );

		// Subject
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'subject', width: 359 } );
		this.addEditFieldToColumn( $.i18n._( 'Subject' ), form_item_input, tab_request_column1, '' );

		// Body
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );

		form_item_input.TTextArea( { field: 'body', width: 600, height: 400 } );

		this.addEditFieldToColumn( $.i18n._( 'Body' ), form_item_input, tab_request_column1, '', null, null, true );

		tab_request_column2.css( 'display', 'none' );

	},

	search: function( set_default_menu, page_action, page_number, callBack ) {
		this.refresh_id = null;
		this._super( 'search', set_default_menu, page_action, page_number, callBack );
	},

	setCurrentEditRecordData: function() {
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'full_name':
						widget.setValue( this.current_edit_record['first_name'] + ' ' + this.current_edit_record['last_name'] );
						break;
					case 'subject':
						widget.setValue( this.current_edit_record[key] );
						if ( this.is_edit ) {
							widget.setValue( 'Re: ' + this.messages[0].subject );
						}

						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}
		this.setEditViewDataDone();
	},

	setEditViewDataDone: function() {
		var $this = this;
		this._super( 'setEditViewDataDone' );
		if ( !this.is_viewing ) {
			if ( Global.isSet( $this.messages ) ) {
				$this.messages = null;
			}
		}

	},

	//Make sure this.current_edit_record is updated before validate
	validate: function() {
		var $this = this;

		var record = {};

		if ( this.is_edit ) {
			for ( var key in this.edit_view_ui_dic ) {

				if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
					continue;
				}

				var widget = this.edit_view_ui_dic[key];

				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() && widget.getEnabled() ) {
						record[key] = widget.getValue();
					}

				}
			}

		} else {
			record = this.buildDataForAPI( this.current_edit_record );
		}

		record = this.uniformVariable( record );
		if ( this.is_edit ) {
			this.message_control_api['validate' + this.message_control_api.key_name]( record, {
				onResult: function( result ) {
					$this.validateResult( result );
				}
			} );
		} else if ( this.is_viewing ) {
			this.api_request['validate' + this.api.key_name]( record, {
				onResult: function( result ) {
					$this.validateResult( result );
				}
			} );
		}
	},


	openAuthorizationView: function() {
		if ( !this.edit_view ) {
			this.initEditViewUI( this.viewId, this.edit_view_tpl );
		}
	}

} );