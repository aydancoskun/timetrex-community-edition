TimeSheetViewController = BaseViewController.extend( {

	el: '#timesheet_view_container', //Must set el here and can only set string, so events can work
	status_array: null,
	type_array: null,
	employee_nav: null,
	start_date_picker: null,
	full_timesheet_data: null, //full timesheet data
	full_format: 'ddd-MMM-DD-YYYY',
	weekly_format: 'ddd, MMM DD',
	day_format: 'ddd',
	date_format: 'MMM DD',
	start_date: null,
	end_date: null,
	select_cells_Array: [], //Timesheet grid
	select_punches_array: [], //Timesheet grid.
	absence_select_cells_Array: [], //Absence grid
	accumulated_time_cells_array: [],
	premium_cells_array: [],
	timesheet_data_source: null,
	accumulated_time_source: null,
	accumulated_time_grid: null,
	accumulated_time_source_map: null,
	branch_grid: null,
	branch_source_map: null,
	branch_source: null,
	department_grid: null,
	department_source_map: null,
	department_source: null,
	job_grid: null,
	job_source_map: null,
	job_source: null,
	job_item_grid: null,
	job_item_source_map: null,
	job_item_source: null,
	premium_grid: null,
	premium_source_map: null,
	premium_source: null,
	absence_grid: null,
	absence_source: null,
	absence_original_source: null,
	accumulated_total_grid: null,
	accumulated_total_grid_source_map: null,
	accumulated_total_grid_source: null,
	punch_note_grid: null,
	punch_note_grid_source: null,
	verification_grid: null,
	verification_grid_source: null,
	grid_dic: null,
	pay_period_map: null,
	pay_period_data: null,
	timesheet_verify_data: null,
	api_timesheet: null,
	api_user_date_total: null,
	api_date: null,
	api_station: null,
	api_punch: null,
	absence_model: false,
	select_drag_menu_id: '', //Do drag move or copy
	is_mass_adding: false,
	department_cell_count: 0,
	branch_cell_count: 0,
	premium_cell_count: 0,
	job_cell_count: 0,
	task_cell_count: 0,
	absence_cell_count: 0,
	punch_note_account: 0,
	show_navigation_box: true,
	station: null,
	scroll_position: 0,
	job_api: null,
	job_item_api: null,
	api_absence_policy: null,
	pre_total_time: null,
	absence_available_balance_dataList: {},
	available_balance_info: null,
	show_job_ui: false,
	show_job_item_ui: false,
	show_branch_ui: false,
	show_department_ui: false,
	show_good_quantity_ui: false,
	show_bad_quantity_ui: false,
	show_note_ui: false,
	show_station_ui: false,
	show_absence_job_ui: false,
	show_absence_job_item_ui: false,
	show_absence_branch_ui: false,
	show_absence_department_ui: false,
	holiday_data_dic: {},
	grid_div: null,
	actual_time_label: null,
	column_maps: null,
	accmulated_order_map: {},
	url_args_before_set_date_url: {},
	allow_auto_switch: true,

	previous_absence_policy_id: false,

	initialize: function( options ) {

		this._super( 'initialize', options );
		this.permission_id = 'punch';
		this.viewId = 'TimeSheet';
		this.script_name = 'TimeSheetView';
		this.context_menu_name = $.i18n._( 'TimeSheet' );
		this.navigation_label = $.i18n._( 'TimeSheet' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIPunch' ))();
		this.api_timesheet = new (APIFactory.getAPIClass( 'APITimeSheet' ))();
		this.api_user_date_total = new (APIFactory.getAPIClass( 'APIUserDateTotal' ))();
		this.api_date = new (APIFactory.getAPIClass( 'APIDate' ))();
		this.api_station = new (APIFactory.getAPIClass( 'APIStation' ))();
		this.api_punch = new (APIFactory.getAPIClass( 'APIPunch' ))();
		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
			this.job_api = new (APIFactory.getAPIClass( 'APIJob' ))();
			this.job_item_api = new (APIFactory.getAPIClass( 'APIJobItem' ))();
		}
		this.api_absence_policy = new (APIFactory.getAPIClass( 'APIAbsencePolicy' ))();
		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.copy_as_new] = true;
		this.scroll_position = 0;
		this.grid_dic = {};
		this.initPermission();

		this.render();
		this.buildContextMenu();
		this.initData();
		this.setSelectRibbonMenuIfNecessary();
	},

	onSubViewRemoved: function() {
		this.search();

		if ( !this.edit_view ) {
			this.setDefaultMenu();
		} else {
			this.setEditMenu();
		}

	},

	setScrollPosition: function() {
		if ( this.scroll_position > 0 ) {
			this.grid_div.scrollTop( this.scroll_position );
		}
	},

	punchModeValidate: function( p_id ) {
		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'punch_timesheet' ) &&
			PermissionManager.validate( p_id, 'manual_timesheet' ) ) {
			return true;
		}
		return false;
	},

	jobUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( "job", 'enabled' ) &&
			PermissionManager.validate( p_id, 'edit_job' ) ) {
			return true;
		}
		return false;
	},

	jobItemUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_job_item' ) ) {
			return true;
		}
		return false;
	},

	//Refresh to clear warnning messages after saving from employee edit view
	updateSelectUserAndRefresh: function( new_item ) {

		this.employee_nav.updateSelectItem( new_item );

		this.search();
	},

	branchUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_branch' ) ) {
			return true;
		}
		return false;
	},

	departmentUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_department' ) ) {
			return true;
		}
		return false;
	},

	goodQuantityUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_quantity' ) ) {
			return true;
		}
		return false;
	},

	badQuantityUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_quantity' ) &&
			PermissionManager.validate( p_id, 'edit_bad_quantity' ) ) {
			return true;
		}
		return false;
	},

	noteUIValidate: function( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_note' ) ) {
			return true;
		}
		return false;
	},

	stationValidate: function() {
		if ( PermissionManager.validate( 'station', 'enabled' ) ) {
			return true;
		}
		return false;
	},

	/* jshint ignore:start */
	//Special permission check for views, need override
	initPermission: function() {
		this._super( 'initPermission' );

		if ( !PermissionManager.validate( 'punch', 'view' ) && !PermissionManager.validate( 'punch', 'view_child' ) ) {
			this.show_navigation_box = false;
			this.show_search_tab = false;
		} else {
			this.show_navigation_box = true;
			this.show_search_tab = true;
		}

		if ( this.punchModeValidate() ) {
			this.show_punch_mode_ui = true;
		} else {
			this.show_punch_mode_ui = false;
		}

		this.allow_auto_switch && this.show_punch_mode_ui && (this.is_auto_switch = true);

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

		if ( this.goodQuantityUIValidate() ) {
			this.show_good_quantity_ui = true;
		} else {
			this.show_good_quantity_ui = false;
		}

		if ( this.badQuantityUIValidate() ) {
			this.show_bad_quantity_ui = true;
		} else {
			this.show_bad_quantity_ui = false;
		}

		if ( this.noteUIValidate() ) {
			this.show_note_ui = true;
		} else {
			this.show_note_ui = false;
		}

		if ( this.stationValidate() ) {
			this.show_station_ui = true;
		} else {
			this.show_station_ui = false;
		}

		if ( this.jobUIValidate( 'absence' ) ) {
			this.show_absence_job_ui = true;
		} else {
			this.show_absence_job_ui = false;
		}

		if ( this.jobItemUIValidate( 'absence' ) ) {
			this.show_absence_job_item_ui = true;
		} else {
			this.show_absence_job_item_ui = false;
		}

		if ( this.branchUIValidate( 'absence' ) ) {
			this.show_absence_branch_ui = true;
		} else {
			this.show_absence_branch_ui = false;
		}

		if ( this.departmentUIValidate( 'absence' ) ) {
			this.show_absence_department_ui = true;
		} else {
			this.show_absence_department_ui = false;
		}

	},
	/* jshint ignore:end */

	ownerOrChildPermissionValidate: function( p_id, permission_name, selected_item ) {
		var field;
		if ( permission_name && permission_name.indexOf( 'child' ) > -1 ) {
			field = 'is_child';
		} else {
			field = 'is_owner';
		}

		var user = this.getSelectEmployee( true );

		if ( PermissionManager.validate( p_id, permission_name ) && (!user || !Global.isSet( user[field] ) || ( user && user[field] ) ) ) {
			return true;
		}

		return false;
	},

	initOptions: function() {
		var $this = this;
		this.initDropDownOption( 'type' );
		this.initDropDownOption( 'status' );

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

		//menu group
		var drag_and_drop_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Drag & Drop' ),
			id: this.viewId + 'drag_and_drop',
			ribbon_menu: menu,
			sub_menus: []
		} );

		//navigation group
		var navigation_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Navigation' ),
			id: this.viewId + 'navigation',
			ribbon_menu: menu,
			sub_menus: []
		} );

		//navigation group
		var other_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Other' ),
			id: this.viewId + 'other',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var add = new RibbonSubMenu( {
			label: $.i18n._( 'New<br>Punch' ),
			id: ContextMenuIconName.add,
			group: editor_group,
			icon: Icons.new_add,
			permission_result: true,
			permission: null
		} );

		var add_absence = new RibbonSubMenu( {
			label: $.i18n._( 'New<br>Absence' ),
			id: ContextMenuIconName.add_absence,
			group: editor_group,
			icon: Icons.new_add,
			permission_result: true,
			permission: null
		} );

		var view = new RibbonSubMenu( {
			label: $.i18n._( 'View' ),
			id: ContextMenuIconName.view,
			group: editor_group,
			icon: Icons.view,
			permission_result: true,
			permission: null
		} );

		var edit = new RibbonSubMenu( {
			label: $.i18n._( 'Edit' ),
			id: ContextMenuIconName.edit,
			group: editor_group,
			icon: Icons.edit,
			permission_result: true,
			permission: null
		} );

		var mass_edit = new RibbonSubMenu( {
			label: $.i18n._( 'Mass<br>Edit' ),
			id: ContextMenuIconName.mass_edit,
			group: editor_group,
			icon: Icons.mass_edit,
			permission_result: true,
			permission: null
		} );

		var del = new RibbonSubMenu( {
			label: $.i18n._( 'Delete' ),
			id: ContextMenuIconName.delete_icon,
			group: editor_group,
			icon: Icons.delete_icon,
			permission_result: true,
			permission: null
		} );

		var delAndNext = new RibbonSubMenu( {
			label: $.i18n._( 'Delete<br>& Next' ),
			id: ContextMenuIconName.delete_and_next,
			group: editor_group,
			icon: Icons.delete_and_next,
			permission_result: true,
			permission: null
		} );

		var copy = new RibbonSubMenu( {
			label: $.i18n._( 'Copy' ),
			id: ContextMenuIconName.copy,
			group: editor_group,
			icon: Icons.copy_as_new,
			permission_result: true,
			permission: null
		} );

		var copy_as_new = new RibbonSubMenu( {
			label: $.i18n._( 'Copy<br>as New' ),
			id: ContextMenuIconName.copy_as_new,
			group: editor_group,
			icon: Icons.copy,
			permission_result: true,
			permission: null
		} );

		var save = new RibbonSubMenu( {
			label: $.i18n._( 'Save' ),
			id: ContextMenuIconName.save,
			group: editor_group,
			icon: Icons.save,
			permission_result: true,
			permission: null
		} );

		var save_and_continue = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& Continue' ),
			id: ContextMenuIconName.save_and_continue,
			group: editor_group,
			icon: Icons.save_and_continue,
			permission_result: true,
			permission: null
		} );

		var save_and_next = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& Next' ),
			id: ContextMenuIconName.save_and_next,
			group: editor_group,
			icon: Icons.save_and_next,
			permission_result: true,
			permission: null
		} );

		var save_and_copy = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& Copy' ),
			id: ContextMenuIconName.save_and_copy,
			group: editor_group,
			icon: Icons.save_and_copy,
			permission_result: true,
			permission: null
		} );

		var save_and_new = new RibbonSubMenu( {
			label: $.i18n._( 'Save<br>& New' ),
			id: ContextMenuIconName.save_and_new,
			group: editor_group,
			icon: Icons.save_and_new,
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

		var move = new RibbonSubMenu( {
			label: $.i18n._( 'Move' ),
			id: ContextMenuIconName.move,
			group: drag_and_drop_group,
			icon: Icons.move,
			permission_result: true,
			permission: null
		} );

		var drag_copy = new RibbonSubMenu( {
			label: $.i18n._( 'Copy' ),
			id: ContextMenuIconName.drag_copy,
			group: drag_and_drop_group,
			icon: Icons.copy,
			permission_result: true,
			permission: null
		} );

		var in_out = new RibbonSubMenu( {
			label: $.i18n._( 'In/Out' ),
			id: ContextMenuIconName.in_out,
			group: navigation_group,
			icon: Icons.in_out,
			permission_result: PermissionManager.checkTopLevelPermission( 'InOut' ),
			permission: null
		} );



		var schedule_view = new RibbonSubMenu( {
			label: $.i18n._( 'Schedules' ),
			id: ContextMenuIconName.schedule,
			group: navigation_group,
			icon: Icons.schedule,
			permission_result: true,
			permission: null
		} );

		var pay_stub_view = new RibbonSubMenu( {
			label: $.i18n._( 'Pay<br>Stubs' ),
			id: ContextMenuIconName.pay_stub,
			group: navigation_group,
			icon: Icons.pay_stubs,
			permission_result: true,
			permission: null
		} );

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) ) {
			var map = new RibbonSubMenu( {
				label: $.i18n._( 'Map' ),
				id: ContextMenuIconName.map,
				group: navigation_group,
				icon: Icons.map,
				permission_result: true,
				permission: null
			} );
		}

		var edit_employee = new RibbonSubMenu( {
			label: $.i18n._( 'Edit<br>Employee' ),
			id: ContextMenuIconName.edit_employee,
			group: navigation_group,
			icon: Icons.employee,
			permission_result: true,
			permission: null
		} );

		var edit_pay_period = new RibbonSubMenu( {
			label: $.i18n._( 'Edit Pay<br>Period' ),
			id: ContextMenuIconName.edit_pay_period,
			group: navigation_group,
			icon: Icons.pay_period,
			permission_result: true,
			permission: null
		} );

		var edit_accumulated_time = new RibbonSubMenu( {
			label: $.i18n._( 'Accumulated<br>Time' ),
			id: ContextMenuIconName.accumulated_time,
			group: navigation_group,
			icon: Icons.timesheet,
			permission_result: true,
			permission: null
		} );

		if ( PermissionManager.validate('request', 'add') ) {
			var auto_request = new RibbonSubMenu({
				label: $.i18n._('Add<br>Request'),
				id: 'AddRequest',
				group: navigation_group,
				icon: Icons.request,
				permission_result: true,
				permission: true
			});
		}

		var re_cal_timesheet = new RibbonSubMenu( {
			label: $.i18n._( 'ReCalculate<br>TimeSheet' ),
			id: ContextMenuIconName.re_calculate_timesheet,
			group: other_group,
			icon: Icons.re_cal_timesheet,
			permission_result: true,
			permission: null
		} );

		var generate_pay_stub = new RibbonSubMenu( {
			label: $.i18n._( 'Generate<br>Pay Stub' ),
			id: ContextMenuIconName.generate_pay_stub,
			group: other_group,
			icon: Icons.re_cal_pay_stub,
			permission_result: true,
			permission: null
		} );

		var print = new RibbonSubMenu( {
			label: $.i18n._( 'Print' ),
			id: ContextMenuIconName.print,
			group: other_group,
			icon: Icons.print,
			type: RibbonSubMenuType.NAVIGATION,
			items: [],
			permission_result: true,
			permission: true
		} );

		var summary = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Summary' ),
			id: 'print_summary',
			nav: print
		} );

		var detail = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Detailed' ),
			id: 'print_detailed',
			nav: print
		} );

		return [menu];

	},

	openEditView: function() {
		if ( !this.edit_view ) {
			this.is_edit = true;
			this.initEditViewUI( 'TimeSheet', 'TimeSheetEditView.html' );
		}

	},

	/* jshint ignore:start */
	//set widget disablebility if view mode or edit mode
	setEditViewWidgetsMode: function() {
		var did_clean = false;
		for ( var key in this.edit_view_ui_dic ) {
			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}
			var widget = this.edit_view_ui_dic[key];
			var widgetContainer = this.edit_view_form_item_dic[key];
			var column = widget.parent().parent().parent();
			if ( !column.hasClass( 'v-box' ) ) {
				if ( !did_clean ) {
					did_clean = true;
				}
			}
			if ( this.absence_model ) {
				switch ( key ) {
					case 'punch_date':
					case 'punch_time':
					case 'status_id':
					case 'type_id':
					case 'quantity':
					case 'station_id':
					case 'has_image':
					case 'latitude':
						this.detachElement( key );
						widget.css( 'opacity', 0 );
						break;

					case 'punch_dates':
						if ( this.is_mass_adding ) {
							this.attachElement( key );
							widget.css( 'opacity', 1 );
							break;
						} else {
							this.detachElement( key );
							widget.css( 'opacity', 0 );
							break;
						}
						break;
					case 'date_stamp':
						if ( this.is_mass_adding ) {
							this.detachElement( key );
							widget.css( 'opacity', 0 );
							break;
						} else {
							this.attachElement( key );
							widget.css( 'opacity', 1 );
							break;
						}
						break;
					case 'total_time':
					case 'src_object_id':
					case 'override':
						this.attachElement( key );
						widget.css( 'opacity', 1 );
						break;
					default:
						widget.css( 'opacity', 1 );
						break;

				}

			} else {
				switch ( key ) {
					case 'punch_dates':
						if ( this.is_mass_adding ) {
							this.attachElement( key );
							widget.css( 'opacity', 1 );
							break;
						} else {
							this.detachElement( key );
							widget.css( 'opacity', 0 );
							break;
						}
						break;
					case 'punch_date':
						if ( this.is_mass_adding ) {
							this.detachElement( key );
							widget.css( 'opacity', 0 );
							break;
						} else {
							this.attachElement( key );
							widget.css( 'opacity', 1 );
							break;
						}
						break;
					case 'quantity':

						if ( this.show_good_quantity_ui && this.show_bad_quantity_ui ) {
							this.attachElement( key );
							widget.css( 'opacity', 1 );
						}
						break;
					case 'station':
						this.attachElement( key );
						widget.css( 'opacity', 1 );
						break;
					case 'punch_time':
					case 'status_id':
					case 'type_id':
					case 'has_image':
					case 'latitude':
						this.attachElement( key );
						widget.css( 'opacity', 1 );
						break;
					case 'date_stamp':
					case 'total_time':
					case 'src_object_id':
					case 'override':
						this.detachElement( key );
						widget.css( 'opacity', 0 );
						break;
					default:
						widget.css( 'opacity', 1 );
						break;

				}
			}
			if ( this.is_viewing ) {
				if ( Global.isSet( widget.setEnabled ) ) {
					widget.setEnabled( false );
				}
			} else {
				if ( Global.isSet( widget.setEnabled ) ) {
					widget.setEnabled( true );
				}
			}
		}

	},

	buildEditViewUI: function() {
		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_punch': this.absence_model ? $.i18n._( 'Absence' ) : $.i18n._( 'Punch' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

		var form_item_input;
		var widgetContainer;

		//Tab 0 start

		var tab_punch = this.edit_view_tab.find( '#tab_punch' );

		var tab_punch_column1 = tab_punch.find( '.first-column' );

		//Employee

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'first_last_name'} );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_punch_column1, '' );

		//Time
		form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );
		form_item_input.TTimePicker( {field: 'punch_time', validation_field: 'time_stamp'} );
		widgetContainer = $( "<div class='widget-h-box'></div>" );
		this.actual_time_label = $( "<span class='widget-right-label'></span>" );
		widgetContainer.append( form_item_input );
		widgetContainer.append( this.actual_time_label );
		this.addEditFieldToColumn( $.i18n._( 'Time' ), form_item_input, tab_punch_column1, '', widgetContainer, true );

		//Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( {field: 'punch_date', validation_field: 'date_stamp'} );

		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_punch_column1, '', null, true );

		//Mass Add Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TRangePicker( {field: 'punch_dates', validation_field: 'date_stamp'} );

		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_punch_column1, '', null, true );

		//Absence Model
		//Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( {field: 'date_stamp'} );

		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_punch_column1, '', null, true );

		//Absence Model
		//Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'total_time', mode: 'time_unit'} );

		this.addEditFieldToColumn( $.i18n._( 'Time' ), form_item_input, tab_punch_column1, '', null, true );

		//Absence Model
		//Absence Policy TYpe
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIAbsencePolicy' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.ABSENCES_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'src_object_id',
			validation_field: 'absence_policy_id'
		} );

		form_item_input.customSearchFilter = function( filter ) {
			return $this.setAbsencePolicyFilter( filter );
		};

		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_punch_column1, '', null, true );

		//Available Balance
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'available_balance'} );

		widgetContainer = $( "<div class='widget-h-box'></div>" );
		this.available_balance_info = $( '<img class="available-balance-info" src="' + Global.getRealImagePath( 'images/infox16x16.png' ) + '">' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( this.available_balance_info );

		this.addEditFieldToColumn( $.i18n._( 'Available Balance' ), [form_item_input], tab_punch_column1, '', widgetContainer, true );

		//Punch Type

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'type_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );

		widgetContainer = $( "<div class='widget-h-box'></div>" );

		var check_box = Global.loadWidgetByName( FormItemType.CHECKBOX );
		check_box.TCheckbox( {field: 'disable_rounding'} );

		var label = $( "<span class='widget-right-label'>" + $.i18n._( 'Disable Rounding' ) + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		widgetContainer.append( check_box );

		this.addEditFieldToColumn( $.i18n._( 'Punch Type' ), [form_item_input, check_box], tab_punch_column1, '', widgetContainer, true );

		//In Out (Status)
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'status_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'In/Out' ), form_item_input, tab_punch_column1, '', null, true );

		//Default Branch
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIBranch' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.BRANCH,
			show_search_inputs: true,
			set_empty: true,
			field: 'branch_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Branch' ), form_item_input, tab_punch_column1, '', null, true );

		if ( !this.absence_model ) {
			if ( !this.show_branch_ui ) {
				this.detachElement( 'branch_id' );
			}
		} else {
			if ( !this.show_absence_branch_ui ) {
				this.detachElement( 'branch_id' );
			}
		}

		//Department
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.DEPARTMENT,
			show_search_inputs: true,
			set_empty: true,
			field: 'department_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Department' ), form_item_input, tab_punch_column1, '', null, true );

		if ( !this.absence_model ) {
			if ( !this.show_department_ui ) {
				this.detachElement( 'department_id' );
			}
		} else {
			if ( !this.show_absence_department_ui ) {
				this.detachElement( 'department_id' );
			}
		}

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {

			//Job
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIJob' )),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.JOB,
				show_search_inputs: true,
				set_empty: true,
				setRealValueCallBack: (function( val ) {

					if ( val ) {
						job_coder.setValue( val.manual_id );
					}
				}),
				field: 'job_id'
			} );

			widgetContainer = $( "<div class='widget-h-box'></div>" );

			var job_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_coder.TTextInput( {field: 'job_quick_search', disable_keyup_event: true} );
			job_coder.addClass( 'job-coder' );

			widgetContainer.append( job_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Job' ), [form_item_input, job_coder], tab_punch_column1, '', widgetContainer, true );

			if ( !this.absence_model ) {
				if ( !this.show_job_ui ) {
					this.detachElement( 'job_id' );
				}
			} else {
				if ( !this.show_absence_job_ui ) {
					this.detachElement( 'job_id' );
				}
			}

			//Job Item
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIJobItem' )),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.JOB_ITEM,
				show_search_inputs: true,
				set_empty: true,
				setRealValueCallBack: (function( val ) {

					if ( val ) {
						job_item_coder.setValue( val.manual_id );
					}
				}),
				field: 'job_item_id'
			} );

			widgetContainer = $( "<div class='widget-h-box'></div>" );

			var job_item_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_item_coder.TTextInput( {field: 'job_item_quick_search', disable_keyup_event: true} );
			job_item_coder.addClass( 'job-coder' );

			widgetContainer.append( job_item_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Task' ), [form_item_input, job_item_coder], tab_punch_column1, '', widgetContainer, true );

			if ( !this.absence_model ) {
				if ( !this.show_job_item_ui ) {
					this.detachElement( 'job_item_id' );
				}
			} else {
				if ( !this.show_absence_job_item_ui ) {
					this.detachElement( 'job_item_id' );
				}
			}

		}

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {

			//Quanitity

			var good = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			good.TTextInput( {field: 'quantity'} );
			good.addClass( 'quantity-input' );

			var good_label = $( "<span class='widget-right-label'>" + $.i18n._( 'Good' ) + ": </span>" );

			var bad = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			bad.TTextInput( {field: 'bad_quantity'} );
			bad.addClass( 'quantity-input' );

			var bad_label = $( "<span class='widget-right-label'>/ " + $.i18n._( 'Bad' ) + ": </span>" );

			widgetContainer = $( "<div class='widget-h-box'></div>" );

			widgetContainer.append( good_label );
			widgetContainer.append( good );
			widgetContainer.append( bad_label );
			widgetContainer.append( bad );

			this.addEditFieldToColumn( $.i18n._( 'Quantity' ), [good, bad], tab_punch_column1, '', widgetContainer, true );

			if ( !this.show_bad_quantity_ui && !this.show_good_quantity_ui ) {
				this.detachElement( 'quantity' );
			} else {
				if ( !this.show_bad_quantity_ui ) {
					bad_label.hide();
					bad.hide();
				}

				if ( !this.show_good_quantity_ui ) {
					good_label.hide();
					good.hide();
				}
			}
		}

		//Note
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( {field: 'note', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Note' ), form_item_input, tab_punch_column1, '', null, true, true );
		form_item_input.parent().width( '45%' );

		if ( !this.show_note_ui ) {
			this.detachElement( 'note' );
		}

		//Absence Mode
		//Override
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( {field: 'override'} );
		this.addEditFieldToColumn( $.i18n._( 'Override' ), form_item_input, tab_punch_column1, '', null, true, true );

		//Location
		if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {

			var latitude = Global.loadWidgetByName(FormItemType.TEXT);
			latitude.TText({field: 'latitude'});
			var longitude = Global.loadWidgetByName(FormItemType.TEXT);
			longitude.TText({field: 'longitude'});
			widgetContainer = $("<div class='widget-h-box link-widget-box'></div>");
			var accuracy = Global.loadWidgetByName(FormItemType.TEXT);
			accuracy.TText({field: 'position_accuracy'});
			label = $("<span class='widget-right-label'>" + $.i18n._('Accuracy') + ":</span>");

			var map_icon = $('<img class="widget-h-box-mapIcon" src="framework/leaflet/images/marker-icon-red.png" >')

			this.location_wrapper = $('<div class="widget-h-box-mapLocationWrapper"></div>')
			widgetContainer.append(map_icon);
			widgetContainer.append(this.location_wrapper);
			this.location_wrapper.append(latitude);
			this.location_wrapper.append($('<span>, </span>'));
			this.location_wrapper.append(longitude);
			this.location_wrapper.append(label);
			this.location_wrapper.append(accuracy);
			this.location_wrapper.append($('<span>m</span>'));
			this.addEditFieldToColumn($.i18n._('Location'), [latitude, longitude, accuracy], tab_punch_column1, '', widgetContainer, true);
			widgetContainer.click(function () {
				$this.onMapClick();
			});
		}

		//Station
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( {field: 'station_id'} );

		this.addEditFieldToColumn( $.i18n._( 'Station' ), form_item_input, tab_punch_column1, '', null, true, true );

		form_item_input.click( function() {
			if ( $this.current_edit_record.station_id && $this.show_station_ui ) {
				IndexViewController.openEditView( $this, 'Station', $this.current_edit_record.station_id );
			}

		} );

		//Punch Image
		form_item_input = Global.loadWidgetByName( FormItemType.IMAGE );
		form_item_input.TImage( {field: 'punch_image'} );
		this.addEditFieldToColumn( $.i18n._( 'Image' ), form_item_input, tab_punch_column1, '', null, true, true );

	},

	/* jshint ignore:end */


	onEditStationDone: function() {
		this.setStation();
	},

	setAbsencePolicyFilter: function( filter ) {
		if ( !filter.filter_data ) {
			filter.filter_data = {};
		}

		filter.filter_data.user_id = this.current_edit_record.user_id;

		if ( filter.filter_columns ) {
			filter.filter_columns.absence_policy = true;
		}

		return filter;
	},

	onSetSearchFilterFinished: function() {

	},

	onBuildBasicUIFinished: function() {
	},

	onBuildAdvUIFinished: function() {

	},

	events: {},

	parserDatesRange: function( date ) {
		var dates = date.split( " - " );
		var resultArray = [];
		var beginDate = Global.strToDate( dates[0] );
		var endDate = Global.strToDate( dates[1] );

		var nextDate = beginDate;

		while ( nextDate.getTime() < endDate.getTime() ) {
			resultArray.push( nextDate.format() );
			nextDate = new Date( new Date( nextDate.getTime() ).setDate( nextDate.getDate() + 1 ) );
		}

		resultArray.push( dates[1] );

		return resultArray;
	},

	validate: function() {
		var $this = this;
		var record = this.current_edit_record;
		var i;
		if ( this.is_mass_editing ) {
			record = [];
			var len = this.mass_edit_record_ids.length;
			for ( i = 0; i < len; i++ ) {
				var temp_item = Global.clone( this.current_edit_record );
				temp_item.id = this.mass_edit_record_ids[i];
				record.push( temp_item );
			}
		}

		if ( this.is_mass_adding ) {

			record = [];
			var dates_array = this.current_edit_record.punch_dates;

			if ( dates_array && dates_array.indexOf( ' - ' ) > 0 ) {
				dates_array = this.parserDatesRange( dates_array );
			}

			for ( i = 0; i < dates_array.length; i++ ) {
				var common_record = Global.clone( this.current_edit_record );
				delete common_record.punch_dates;
				if ( this.absence_model ) {
					common_record.date_stamp = dates_array[i];
				} else {
					common_record.punch_date = dates_array[i];
				}

				record.push( common_record );
			}
		}

		if ( !this.absence_model ) {

			this.api['validate' + this.api.key_name]( record, {
				onResult: function( result ) {
					$this.validateResult( result );
				}
			} );

		} else {

			this.api_user_date_total['validate' + this.api_user_date_total.key_name]( record, {
				onResult: function( result ) {
					$this.clearErrorTips(); //Always clear error

					if ( result.isValid() ) {
						$this.setEditMenu();
					} else {
						$this.setErrorMenu();
						$this.setErrorTips( result );
					}

				}
			} );

		}

	},
	/* jshint ignore:start */
	onFormItemChange: function( target, doNotValidate ) {

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		// Error: TypeError: this.current_edit_record is null in interface/html5/framework/jquery.min.js?v=9.0.5-20151222-094938 line 2 > eval line 1409
		if ( !this.current_edit_record ) {
			return;
		}
		this.current_edit_record[key] = c_value;
		switch ( key ) {
			case 'job_id':
				if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
					this.edit_view_ui_dic['job_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
					this.setJobItemValueWhenJobChanged( target.getValue( true ), 'job_item_id', {status_id: 10, job_id: this.current_edit_record.job_id} );
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
					this.onJobQuickSearch( key, c_value );
				}
				break;
			case 'punch_dates':
				this.setEditMenu();
				break;

		}

		if ( this.absence_model ) {
			if ( key === 'total_time' ) {
				c_value = this.api_date.parseTimeUnit( c_value, {async: false} ).getResult();
				this.current_edit_record[key] = c_value;
			} else {
				this.current_edit_record[key] = c_value;
			}
			if ( key !== 'override' ) {
				this.edit_view_ui_dic.override.setValue( true );
				this.current_edit_record.override = true;
			}
		} else {
			this.current_edit_record[key] = c_value;
		}

		if ( !doNotValidate ) {
			if ( this.absence_model ) {
				if ( key === 'total_time' ||
					key === 'date_stamp' ||
					key === 'punch_dates' ||
					key === 'src_object_id' ) {
					this.onAvailableBalanceChange();
				}
			}
			this.validate();
		}

	},
	/* jshint ignore:end */

	buildSearchAndLayoutUI: function() {
		var layout_div = this.search_panel.find( 'div #saved_layout_content_div' );

		var form_item = $( Global.loadWidget( 'global/widgets/search_panel/FormItem.html' ) );
		var form_item_label = form_item.find( '.form-item-label' );
		var form_item_input_div = form_item.find( '.form-item-input-div' );

		form_item_label.text( $.i18n._( 'Save Search As' ) + ': ' );

		this.save_search_as_input = Global.loadWidget( 'global/widgets/text_input/TTextInput.html' );
		this.save_search_as_input = $( this.save_search_as_input );
		this.save_search_as_input.TTextInput();

		var save_btn = $( "<input class='t-button' style='margin-left: 5px' type='button' value='" + $.i18n._( 'Save' ) + "' />" );

		form_item_input_div.append( this.save_search_as_input );
		form_item_input_div.append( save_btn );

		var $this = this;
		save_btn.click( function() {
			$this.onSaveNewLayout();
		} );

		//Previous Saved Layout

		this.previous_saved_layout_div = $( "<div class='previous-saved-layout-div'></div>" );

		form_item_input_div.append( this.previous_saved_layout_div );

		form_item_label = $( "<span style='margin-left: 5px' >" + $.i18n._( 'Previous Saved Searches' ) + ":</span>" );
		this.previous_saved_layout_div.append( form_item_label );

		this.previous_saved_layout_selector = $( "<select style='margin-left: 5px' class='t-select'>" );
		var update_btn = $( "<input class='t-button' style='margin-left: 5px' type='button' value='" + $.i18n._( 'Update' ) + "' />" );
		var del_btn = $( "<input class='t-button' style='margin-left: 5px' type='button' value='" + $.i18n._( 'Delete' ) + "' />" );

		update_btn.click( function() {
			$this.onUpdateLayout();
		} );

		del_btn.click( function() {
			$this.onDeleteLayout();
		} );

		this.previous_saved_layout_div.append( this.previous_saved_layout_selector );
		this.previous_saved_layout_div.append( update_btn );
		this.previous_saved_layout_div.append( del_btn );

		layout_div.append( form_item );

		this.previous_saved_layout_div.css( 'display', 'none' );

	},

	checkTimesheetData: function() {
		if ( this.full_timesheet_data === true ) {
			return false;
		}

		return true;
	},

	render: function() {

		var $this = this;
		this._super( 'render' );

		var control_bar = $( this.el ).find( '.control-bar' );
		var date_chooser_div = control_bar.find( '.date-chooser-div' );
		var employee_nav_div = control_bar.find( '.employee-nav-div' );
		var action_chooser_div = control_bar.find( '.action-chooser-div' );

		if ( !this.show_navigation_box ) {
			employee_nav_div.css( 'display', 'none' );
		} else {
			employee_nav_div.css( 'display', 'block' );
		}
		this.wage_btn = action_chooser_div.find( '#wages' );
		this.wage_btn = this.wage_btn.SwitchButton( {
			icon: SwitchButtonIcon.wages,
			tooltip: $.i18n._( 'Show Wages' )
		} );
		this.wage_btn.click( function() {
			$this.onWageOrModeChange( 'wage' );
		} );

		if ( !PermissionManager.checkTopLevelPermission( 'Wage' ) ) {
			this.wage_btn.parent().hide();
		} else {
			this.wage_btn.parent().show();
		}

		//Create Start Date Picker
		this.start_date_picker = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		this.start_date_picker.TDatePicker( {field: 'start_date'} );
		var date_chooser = $( "<span class='label'>" + $.i18n._( 'Date' ) + ":</span>" +
		"<img class='left-arrow arrow' src=" + Global.getRealImagePath( 'images/left_arrow.png' ) + ">" +
		"<div class='date-picker-div'></div>" +
		"<img class='right-arrow arrow' src=" + Global.getRealImagePath( 'images/right_arrow.png' ) + ">" );

		date_chooser_div.append( date_chooser );
		date_chooser_div.find( '.date-picker-div' ).append( this.start_date_picker );

		var date_left_arrow = date_chooser_div.find( '.left-arrow' );
		var date_right_arrow = date_chooser_div.find( '.right-arrow' );

		date_left_arrow.bind( 'click', function() {
			//Error: TypeError: $this.timesheet_columns is undefined in /interface/html5/framework/jquery.min.js?v=8.0.0-20141230-125919 line 2 > eval line 1569
			if ( !$this.checkTimesheetData() || !$this.timesheet_columns ) {
				return;
			}
			var start = $this.getPunchMode() === 'punch' ? 1 : $this.timesheet_columns.length - 7;
			var select_date = Global.strToDate( $this.timesheet_columns[start].index, $this.full_format );
			var new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() - 6 ) ).format();
			continueChangeDate( new_date );

			//see #2224 Cannot read property 'date' of undefined
			$this.setDefaultMenu();
		} );

		date_right_arrow.bind( 'click', function() {
			//Error: TypeError: $this.timesheet_columns is undefined in /interface/html5/framework/jquery.min.js?v=8.0.0-20141230-125919 line 2 > eval line 1569
			if ( !$this.checkTimesheetData() || !$this.timesheet_columns ) {
				return;
			}
			var start = $this.getPunchMode() === 'punch' ? 7 : $this.timesheet_columns.length - 1;
			var select_date = Global.strToDate( $this.timesheet_columns[start].index, $this.full_format );
			var new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() + 1 ) ).format();

			continueChangeDate( new_date );

			//see #2224 Cannot read property 'date' of undefined
			$this.setDefaultMenu();

		} );

		this.start_date_picker.bind( 'formItemChange', function() {
			var select_date = $this.getSelectDate() ? $this.getSelectDate() : new Date().format();
			continueChangeDate( select_date )
		} );

		function continueChangeDate( new_date ) {
			$this.doNextIfNoValueChangeInManualGrid( doNext, reset );
			function reset() {
				$this.setDatePickerValue( LocalCacheData.last_timesheet_selected_date );
			}

			function doNext() {
				$this.setDatePickerValue( new_date );
				$this.search();
			}
		}

		//Create Employee Navigation

		var label = employee_nav_div.find( '.navigation-label' );
		var left_click = employee_nav_div.find( '.left-click' );
		var right_click = employee_nav_div.find( '.right-click' );
		var navigation_widget_div = employee_nav_div.find( '.navigation-widget-div' );

		this.employee_nav = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		var default_args = {permission_section: 'punch'};
		this.employee_nav = this.employee_nav.AComboBox( {
			id: 'employee_navigation',
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER,
			init_data_immediately: true,
			default_args: default_args,
			show_search_inputs: true,
			always_include_columns: ['default_branch_id', 'default_department_id', 'default_job_id', 'default_job_item_id']
		} );

		navigation_widget_div.append( this.employee_nav );
		this.employee_nav.bind( 'formItemChange', function() {
			$this.doNextIfNoValueChangeInManualGrid( doNext, reset );
			function doNext() {
				var selected_user_id = $this.getSelectEmployee();
				if ( !$this.edit_view ) {
					$this.reSetURL();
				}
				$this.allow_auto_switch && ($this.is_auto_switch = true);
				/* jshint ignore:start */
				if ( LocalCacheData.last_timesheet_selected_user != selected_user_id ) {
					$this.search();
				}
				/* jshint ignore:end */
				$this.setEmployeeNavArrowsStatus();
				$this.absence_model = false;
				$this.setDefaultMenu();
			}

			function reset() {
				$this.employee_nav.setValue( LocalCacheData.last_timesheet_selected_user );
			}
		} );

		this.employee_nav.bind( 'initSourceComplete', function() {
			$this.setEmployeeNavArrowsStatus();
		} );

		left_click.attr( 'src', Global.getRealImagePath( 'images/left_arrow.png' ) );
		right_click.attr( 'src', Global.getRealImagePath( 'images/right_arrow.png' ) );
		right_click.click( function() {
			if ( right_click.hasClass( 'disabled' ) ) {
				return;
			}
			$this.doNextIfNoValueChangeInManualGrid( doNext );
			function doNext() {
				var selected_index = $this.employee_nav.getSelectIndex();
				var source_data = $this.employee_nav.getSourceData();
				var current_open_page = $this.employee_nav.getCurrentOpenPage();
				var next_select_item;
				if ( source_data && selected_index < source_data.length - 1 ) {
					next_select_item = $this.employee_nav.getItemByIndex( selected_index + 1 );
					$this.employee_nav.setValue( next_select_item );
				} else if ( source_data && selected_index === source_data.length - 1 ) {
					$this.employee_nav.onADropDownSearch( 'unselect_grid', current_open_page + 1, 'first' );
				} else {
					next_select_item = $this.employee_nav.getItemByIndex( 0 );
					$this.employee_nav.setValue( next_select_item );
				}
				if ( !$this.edit_view ) {
					$this.reSetURL();
				}
				$this.allow_auto_switch && ($this.is_auto_switch = true);
				$this.search();
				$this.setEmployeeNavArrowsStatus();
			}

		} );

		left_click.click( function() {
			if ( left_click.hasClass( 'disabled' ) ) {
				return;
			}
			$this.doNextIfNoValueChangeInManualGrid( doNext );
			function doNext() {
				var selected_index = $this.employee_nav.getSelectIndex();
				var source_data = $this.employee_nav.getSourceData();
				var current_open_page = $this.employee_nav.getCurrentOpenPage();
				var next_select_item;
				if ( selected_index > 0 ) {
					next_select_item = $this.employee_nav.getItemByIndex( selected_index - 1 );
					$this.employee_nav.setValue( next_select_item );
				} else if ( current_open_page > 1 ) {
					$this.employee_nav.onADropDownSearch( 'unselect_grid', current_open_page - 1, 'last' );
				} else {
					// Error: TypeError: source_data is null in /interface/html5/framework/jquery.min.js?v=8.0.6-20150417-084000 line 2 > eval line 1691
					next_select_item = $this.employee_nav.getItemByIndex( 0 );
					$this.employee_nav.setValue( next_select_item );
				}
				if ( !$this.edit_view ) {
					$this.reSetURL();
				}
				$this.allow_auto_switch && ($this.is_auto_switch = true);
				$this.search();
				$this.setEmployeeNavArrowsStatus();
			}

		} );

		label.text( $.i18n._( 'Employee' ) + ':' );
		//Create timesheet mode toggle buttons
		this.toggle_button = $( this.el ).find( '.toggle-button-div' );
		var data_provider = [
			{label: $.i18n._( 'Punch' ), value: 'punch'},
			{label: $.i18n._( 'Manual' ), value: 'manual'}
		];
		this.toggle_button = this.toggle_button.TToggleButton( {data_provider: data_provider} );
		if ( !this.show_punch_mode_ui ) {
			this.toggle_button.remove();
		} else {
			this.toggle_button.bind( 'change', function( e, result ) {
				$this.onWageOrModeChange( 'manual' );
			} );
		}

	},

	doNextIfNoValueChangeInManualGrid: function( doNext, reset, mode ) {
		!mode && (mode = 'manual');
		var $this = this;
		if ( this.getPunchMode() === mode && this.editor ) {
			var records = this.editor.getValue();
			if ( records.length > 0 ) {
				TAlertManager.showConfirmAlert( Global.modify_alert_message, "", function( flag ) {
					if ( flag ) {
						$this.wait_auto_save && clearTimeout( $this.wait_auto_save );
						doNext();
					} else {
						reset && reset();
					}
				} );
			} else {
				doNext();
			}
		} else {
			doNext();
		}
	},

	getPunchMode: function() {
		return this.toggle_button.getValue()
	},

	onWageOrModeChange: function( id ) {
		var $this = this;
		if ( id === 'wage' ) {
			this.doNextIfNoValueChangeInManualGrid( doNext, resetWage );
		} else if ( id === 'manual' ) {
			this.doNextIfNoValueChangeInManualGrid( doNext, resetManual, 'punch' );
		}

		function resetWage() {
			$this.wage_btn.setValue( !$this.wage_btn.getValue( true ) );
		}

		function resetManual() {
			$this.toggle_button.setValue( 'manual' );
		}

		function doNext() {
			if ( !$this.edit_view ) {
				$this.reSetURL();
			}
			$this.search();
			$this.setDefaultMenu();
		}
	},

	setEmployeeNavArrowsStatus: function() {
		var $this = this;
		var employee_nav_div = $( this.el ).find( '.employee-nav-div' );
		var left_click = employee_nav_div.find( '.left-click' );
		var right_click = employee_nav_div.find( '.right-click' );
		var selected_index = $this.employee_nav.getSelectIndex();
		var source_data = $this.employee_nav.getSourceData();

		right_click.removeClass( 'disabled' );
		left_click.removeClass( 'disabled' );

		var pager_data = $this.employee_nav.getPagerData();
		var current_open_page = $this.employee_nav.getCurrentOpenPage();

		//Error: Uncaught TypeError: Cannot read property 'length' of null in /interface/html5/#!m=TimeSheet&date=20150102&user_id=null line 1698
		if ( !source_data || (selected_index === source_data.length - 1 && current_open_page === pager_data.last_page_number) ) {
			right_click.addClass( 'disabled' );
		}

		if ( !source_data || (selected_index === 0 && current_open_page === 1) ) {
			left_click.addClass( 'disabled' );
		}
	},

	onClearSearch: function() {
		var do_update = false;
		var default_layout_id;
		if ( this.search_panel.getLayoutsArray() && this.search_panel.getLayoutsArray().length > 0 ) {
			default_layout_id = $( this.previous_saved_layout_selector ).children( 'option:contains("' + BaseViewController.default_layout_name + '")' ).attr( 'value' );
			var layout_name = BaseViewController.default_layout_name;
			this.clearSearchPanel();
			this.filter_data = null;
			this.temp_adv_filter_data = null;
			this.temp_basic_filter_data = null;
			do_update = true;

		} else {

			this.clearSearchPanel();
			this.filter_data = null;
			this.temp_adv_filter_data = null;
			this.temp_basic_filter_data = null;

			//Error: Uncaught TypeError: Cannot read property 'setSelectGridData' of null in /interface/html5/#!m=TimeSheet&date=20141213&user_id=29715 line 1738
			if ( this.column_selector ) {
				this.column_selector.setSelectGridData( this.default_display_columns );
			}

			//Error: Uncaught TypeError: Cannot read property 'setValue' of null in /interface/html5/#!m=TimeSheet&date=20150125&user_id=53288 line 1742
			if ( this.sort_by_selector ) {
				this.sort_by_selector.setValue( null );
			}

			this.onSaveNewLayout( BaseViewController.default_layout_name );
			return;

		}

		var filter_data = this.getValidSearchFilter();

		var args;
		if ( do_update ) {
			args = {};
			args.id = default_layout_id;
			args.data = {};
			args.data.filter_data = filter_data;

		}

		var $this = this;
		this.user_generic_data_api.setUserGenericData( args, {
			onResult: function( res ) {

				if ( res.isValid() ) {
					$this.clearViewLayoutCache();
					$this.need_select_layout_name = layout_name;
					$this.initLayout();
				}

			}
		} );

	},

	onUpdateLayout: function() {

		var selectId = $( this.previous_saved_layout_selector ).children( 'option:selected' ).attr( 'value' );
		var layout_name = $( this.previous_saved_layout_selector ).children( 'option:selected' ).text();

		var filter_data = this.getValidSearchFilter();

		var args = {};
		args.id = selectId;
		args.data = {};
		args.data.filter_data = filter_data;

		var $this = this;
		this.user_generic_data_api.setUserGenericData( args, {
			onResult: function( res ) {
				if ( res.isValid() ) {
					$this.clearViewLayoutCache();
					$this.need_select_layout_name = layout_name;
					$this.initLayout();
				}

			}
		} );

	},

	onSaveNewLayout: function( default_layout_name ) {
		var layout_name;
		if ( Global.isSet( default_layout_name ) ) {
			layout_name = default_layout_name;
		} else {
			layout_name = this.save_search_as_input.getValue();
		}

		if ( !layout_name || layout_name.length < 1 ) {
			return;
		}

		var filter_data = this.getValidSearchFilter();

		var args = {};
		args.script = this.script_name;
		args.name = layout_name;
		args.is_default = false;
		args.data = {};
		args.data.filter_data = filter_data;

		var $this = this;
		this.user_generic_data_api.setUserGenericData( args, {
			onResult: function( res ) {

				if ( res.isValid() ) {
					$this.clearViewLayoutCache();
					$this.need_select_layout_name = layout_name;
					$this.initLayout();
				}

			}
		} );

	},

	onSearch: function() {

		this.temp_adv_filter_data = null;
		this.temp_basic_filter_data = null;

		this.getSearchPanelFilter();
		var default_layout_id;
		var layout_name;
		if ( this.search_panel.getLayoutsArray() && this.search_panel.getLayoutsArray().length > 0 ) {
			default_layout_id = $( this.previous_saved_layout_selector ).children( 'option:contains("' + BaseViewController.default_layout_name + '")' ).attr( 'value' );
			layout_name = BaseViewController.default_layout_name;

		} else {
			this.onSaveNewLayout( BaseViewController.default_layout_name );
			return;
		}

		var filter_data = this.getValidSearchFilter();

		var args = {};
		args.id = default_layout_id;
		args.data = {};
		args.data.filter_data = filter_data;

		ProgressBar.showOverlay();
		var $this = this;
		this.user_generic_data_api.setUserGenericData( args, {
			onResult: function( res ) {

				if ( res.isValid() ) {
					$this.clearViewLayoutCache();
					$this.need_select_layout_name = layout_name;

					$this.initLayout();
				}

			}
		} );

	},

	updateManualGrid: function() {
		var $this = this;
		var start_date_string = this.start_date_picker.getValue();
		var user_id = this.getSelectEmployee();
		ProgressBar.noProgressForNextCall();
		$this.api_timesheet.getTimeSheetData( user_id, start_date_string, args, {
			onResult: function( result ) {
				ProgressBar.removeNanobar();
				$this.full_timesheet_data = result.getResult();
				if ( $this.full_timesheet_data === true || !$this.full_timesheet_data.hasOwnProperty( 'timesheet_dates' ) ) {
					return;
				}
				$this.start_date = Global.strToDate( $this.full_timesheet_data.timesheet_dates.start_display_date );
				$this.end_date = Global.strToDate( $this.full_timesheet_data.timesheet_dates.end_display_date );
				$this.setDefaultMenu();
				$this.initInsideEditorData( true );
				$this.accumulated_time_source_map = {};
				$this.branch_source_map = {};
				$this.department_source_map = {};
				$this.job_source_map = {};
				$this.job_item_source_map = {};
				$this.premium_source_map = {};
				$this.accumulated_total_grid_source_map = {};
				$this.accumulated_time_source = [];
				$this.branch_source = [];
				$this.department_source = [];
				$this.job_source = [];
				$this.job_item_source = [];
				$this.premium_source = [];
				$this.accumulated_total_grid_source = [];
				$this.verification_grid_source = [];
				$this.onReloadSubGridResult( result );
			}
		} );
	},

	search: function( setDefaultMenu, force ) {

		this.accumulated_time_cells_array = []; //reset array since the select cell is clean
		this.premium_cells_array = []; //reset array since the select cell is clean
		this.accumulated_time_source_map = {};
		this.branch_source_map = {};
		this.department_source_map = {};
		this.job_source_map = {};
		this.job_item_source_map = {};
		this.premium_source_map = {};
		this.accumulated_total_grid_source_map = {};
		this.accumulated_time_source = [];
		this.branch_source = [];
		this.department_source = [];
		this.job_source = [];
		this.job_item_source = [];
		this.premium_source = [];
		this.absence_source = [];
		this.accumulated_total_grid_source = [];
		this.punch_note_grid_source = [];
		this.verification_grid_source = [];
		this.select_cells_Array = [];
		this.select_punches_array = [];
		this.branch_cell_count = 0;
		this.department_cell_count = 0;
		this.premium_cell_count = 0;
		this.job_cell_count = 0;
		this.task_cell_count = 0;
		this.absence_cell_count = 0;
		this.punch_note_account = 0;
		this.select_punches_array = [];

		var $this = this;
		var filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
		var start_date_string = this.start_date_picker.getValue();
		var user_id = this.getSelectEmployee();
		if ( !force ) {
			this.doNextIfNoValueChangeInManualGrid( doNext, reset );
		} else {
			doNext();
		}

		function reset() {
			$( '.button-rotate' ).removeClass( 'button-rotate' );
		}

		function doNext() {
			LocalCacheData.last_timesheet_selected_date = start_date_string;
			LocalCacheData.last_timesheet_selected_user = $this.getSelectEmployee( true );
			LocalCacheData.last_timesheet_selected_show_wage = $this.wage_btn.getValue( true );
			LocalCacheData.last_timesheet_selected_punch_mode = $this.toggle_button.getValue();
			var args = {filter_data: filter_data};
			ProgressBar.showOverlay();
			//Error: TypeError: this.api_timesheet.getTimeSheetData is not a function in /interface/html5/framework/jquery.min.js?v=8.0.0-20141117-155153 line 2 > eval line 1885
			if ( !$this.api_timesheet || !$this.api_timesheet || typeof($this.api_timesheet.getTimeSheetData) !== 'function' ) {
				return;
			}
			$this.api_timesheet.getTimeSheetData( user_id, start_date_string, args, {
				onResult: function( result ) {

					$this.full_timesheet_data = result.getResult();

					if ( $this.full_timesheet_data === true || !$this.full_timesheet_data.hasOwnProperty( 'timesheet_dates' ) ) {
						return;
					}
					$this.start_date = Global.strToDate( $this.full_timesheet_data.timesheet_dates.start_display_date );
					$this.end_date = Global.strToDate( $this.full_timesheet_data.timesheet_dates.end_display_date );
					$this.buildCalendars();
					if ( setDefaultMenu ) {
						$this.setDefaultMenu( true );
					}
					$this.searchDone();
				}
			} );
		}
	},

	buildVerificationGrid: function() {
		var $this = this;

		var columns = [];
		var grid;
		if ( !Global.isSet( this.verification_grid ) ) {
			grid = $( this.el ).find( '#verification_grid' );

			grid.attr( 'id', this.ui_id + '_verification_grid' );  //Grid's id is ScriptName + _grid

			grid = $( this.el ).find( '#' + this.ui_id + '_verification_grid' );
		}

		var column = {
			name: 'pay_period',
			index: 'pay_period',
			label: $.i18n._( 'Pay Period' ),
			width: 100,
			sortable: false,
			title: false
		};
		columns.push( column );

		column = {
			name: 'verification',
			index: 'verification',
			label: $.i18n._( 'Window' ),
			width: 100,
			sortable: false,
			title: false
		};
		columns.push( column );

		if ( !this.verification_grid ) {

			this.verification_grid = grid;

			this.verification_grid.jqGrid( {
				altRows: true,
				data: [],
				datatype: 'local',
				sortable: false,
				scrollOffset: 0,
				rowNum: 10000,
				colNames: [],
				colModel: columns,
				viewrecords: true

			} );

		} else {

			this.verification_grid.jqGrid( 'GridUnload' );
			this.verification_grid = null;

			grid = $( this.el ).find( '#' + this.ui_id + '_verification_grid' );
			this.verification_grid = $( grid );
			this.verification_grid.jqGrid( {
				altRows: true,
				data: [],
				rowNum: 10000,
				sortable: false,
				scrollOffset: 0,
				datatype: 'local',
				colNames: [],
				colModel: columns,
				viewrecords: true
			} );
		}

		this.grid_dic.verification_grid = this.verification_grid;
	},

	buildPunchNoteGrid: function() {
		var $this = this;

		var columns = [];
		var grid;
		if ( !Global.isSet( this.punch_note_grid ) ) {
			grid = $( this.el ).find( '#punch_note_grid' );

			//Grid's id is ScriptName + _grid
			grid.attr( 'id', this.ui_id + '_punch_note_grid' );

			grid = $( this.el ).find( '#' + this.ui_id + '_punch_note_grid' );
		}

		//if only put one column in grid. There is a UI bug
		var punch_in_out_column = {
			name: '',
			index: '',
			label: ' ',
			width: 0,
			sortable: false,
			title: false,
			hidden: true
		};
		columns.push( punch_in_out_column );

		punch_in_out_column = {
			name: 'note',
			index: 'note',
			label: ' ',
			width: 100,
			sortable: false,
			title: false,
			cellattr: function( index, value ) {
				return 'title="' + value + '"';
			}
		};
		columns.push( punch_in_out_column );

		if ( !this.punch_note_grid ) {

			this.punch_note_grid = grid;

			this.punch_note_grid.jqGrid( {
				altRows: true,
				data: [],
				datatype: 'local',
				sortable: false,
				scrollOffset: 0,
				rowNum: 10000,
				colNames: [],
				colModel: columns,
				viewrecords: true

			} );

		} else {

			this.punch_note_grid.jqGrid( 'GridUnload' );
			this.punch_note_grid = null;

			grid = $( this.el ).find( '#' + this.ui_id + '_punch_note_grid' );
			this.punch_note_grid = $( grid );
			this.punch_note_grid.jqGrid( {
				altRows: true,
				data: [],
				rowNum: 10000,
				sortable: false,
				scrollOffset: 0,
				datatype: 'local',
				colNames: [],
				colModel: columns,
				viewrecords: true
			} );

		}

		this.grid_dic.punch_note_grid = this.punch_note_grid;
		this.setGridHeaderBar( 'punch_note_grid', 'Punch Notes' );
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
					this.pay_period_header = start_date + ' ' + $.i18n._( 'to' ) + ' ' + end_date;
					break;
				}
			}
		}
	},

	buildAccumulatedTotalGrid: function() {
		var $this = this;

		var columns = [];

		var grid;
		if ( !Global.isSet( this.accumulated_total_grid ) ) {
			grid = $( this.el ).find( '#accumulated_total_grid' );

			grid.attr( 'id', this.ui_id + '_accumulated_total_grid' );	//Grid's id is ScriptName + _grid

			grid = $( this.el ).find( '#' + this.ui_id + '_accumulated_total_grid' );
		}

		var punch_in_out_column = {
			name: 'punch_info',
			index: 'punch_info',
			label: ' ',
			width: 100,
			sortable: false,
			title: false,
			formatter: this.onCellFormat
		};
		columns.push( punch_in_out_column );

		var start_date_str = this.start_date.format( Global.getLoginUserDateFormat() );
		var end_date_str = this.end_date.format( Global.getLoginUserDateFormat() );

		this.getAccumulatedTotalGridPayperiodHeader();

		var column_width = 100;
		if ( this.wage_btn.getValue( true ) ) {
			column_width = 150;
		}
		var column_1 = {
			name: 'week',
			index: 'week',
			label: start_date_str + ' ' + $.i18n._( 'to' ) + ' ' + end_date_str,
			width: column_width,
			sortable: false,
			title: false,
			formatter: this.onCellFormat
		};
		var column_2 = {
			name: 'pay_period',
			index: 'pay_period',
			label: this.pay_period_header,
			width: column_width,
			sortable: false,
			title: false,
			formatter: this.onCellFormat
		};

		columns.push( column_1 );
		columns.push( column_2 );

		if ( Global.isSet(this.accumulated_total_grid) == false ) {
			this.accumulated_total_grid = grid;
		} else {
			this.accumulated_total_grid.jqGrid( 'GridUnload' );
			this.accumulated_total_grid = null;
			grid = $( this.el ).find( '#' + this.ui_id + '_accumulated_total_grid' );
			this.accumulated_total_grid = $( grid );
		}

		this.accumulated_total_grid.jqGrid( {
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

		this.grid_dic.accumulated_total_grid = this.accumulated_total_grid;

		var accumulated_total_grid_title = $( this.el ).find( '.accumulated-total-grid-title' );
		accumulated_total_grid_title.css( 'display', 'block' );
		this.setAccumulatedTotalGridPayPeriodHeaders();

	},

	//Bind column click event to change sort type and save columns to t_grid_header_array to use to set column style (asc or desc)
	bindGridColumnEvents: function() {
		var display_columns = this.grid.getGridParam( 'colModel' );

		//Exception taht display column not existed, not sure when this will happen, but may there will be a second time load if this happen
		if ( !display_columns ) {
			return;
		}

		var len = display_columns.length;

		this.t_grid_header_array = [];

		for ( var i = 0; i < len; i++ ) {
			var column_info = display_columns[i];
			var column_header = $( $( this.el ).find( '#gbox_' + this.ui_id + '_grid' ).find( 'div #jqgh_' + this.ui_id + '_grid_' + column_info.name ) );

			this.t_grid_header_array.push( column_header.TGridHeader() );
			column_header.bind( 'click', onColumnHeaderClick );
		}

		var $this = this;

		function onColumnHeaderClick( e ) {
			var field = $( this ).attr( 'id' );
			field = field.substring( 10 + $this.ui_id.length + 1, field.length );

			if ( field === 'cb' || field === 'punch_info' ) { //first column, check box column.
				return;
			}

			var date = Global.strToDate( field, $this.full_format );

			if ( date && date.getYear() > 0 ) {
				$this.setDatePickerValue( date.format( Global.getLoginUserDateFormat() ) );

				$this.highLightSelectDay();
				$this.reLoadSubGridsSource();
			}

		}

	},

	checkIsSelectedAbsenceCell: function( row_id, cell_index ) {
		for ( var i = 0, m = this.absence_select_cells_Array.length; i < m; i++ ) {
			var cell = this.absence_select_cells_Array[i];
			if ( cell.row_id.toString() === row_id.toString() && cell.cell_index.toString() === cell_index.toString() ) {
				return true;
			}
		}

		return false;
	},

	buildAbsenceGrid: function() {
		var $this = this;
		var grid;
		if ( !Global.isSet( this.absence_grid ) ) {
			grid = $( this.el ).find( '#absence_grid' );

			grid.attr( 'id', this.ui_id + '_absence_grid' );  //Grid's id is ScriptName + _grid

			grid = $( this.el ).find( '#' + this.ui_id + '_absence_grid' );
		}

		if ( !this.absence_grid ) {

			this.absence_grid = grid;

			this.absence_grid.jqGrid( {
				altRows: true,
				data: [],
				datatype: 'local',
				sortable: false,
				scrollOffset: 0,
				hoverrows: false,
				width: Global.bodyWidth() - 14,
				rowNum: 10000,
				ondblClickRow: function() {
					$this.onGridDblClickRow( 'absence' );
				},
				onSelectRow: function( row_id, flag, e ) {
					$this.onSelectRow( 'absence_grid', row_id, this );
				},
				onRightClickRow: function( row_id, iRow, cell_index, e ) {
					if ( !$this.checkIsSelectedAbsenceCell( row_id, cell_index ) ) {
						var cell_val = $( e.target ).closest( "td,th" ).html();
						$this.onCellSelect( 'absence_grid', row_id, cell_index, cell_val, this, e );
						$this.onSelectRow( 'absence_grid', row_id, this );
					}
				},
				onCellSelect: function( row_id, cell_index, cell_val, e ) {
					$this.onCellSelect( 'absence_grid', row_id, cell_index, cell_val, this, e );
				},
				colNames: [],
				colModel: this.timesheet_columns,
				viewrecords: true

			} );

		} else {

			this.absence_grid.jqGrid( 'GridUnload' );
			this.absence_grid = null;

			grid = $( this.el ).find( '#' + this.ui_id + '_absence_grid' );
			this.absence_grid = $( grid );
			this.absence_grid.jqGrid( {
				altRows: true,
				onSelectRow: function( row_id, flag, e ) {
					$this.onSelectRow( 'absence_grid', row_id, this );
				},
				onRightClickRow: function( row_id, iRow, cell_index, e ) {
					if ( !$this.checkIsSelectedAbsenceCell( row_id, cell_index ) ) {
						var cell_val = $( e.target ).closest( "td,th" ).html();
						$this.onCellSelect( 'absence_grid', row_id, cell_index, cell_val, this, e );
						$this.onSelectRow( 'absence_grid', row_id, this );
					}
				},
				onCellSelect: function( row_id, cell_index, cell_val, e ) {
					$this.onCellSelect( 'absence_grid', row_id, cell_index, cell_val, this, e );
				},
				ondblClickRow: function() {
					$this.onGridDblClickRow('absence');
				},
				data: [],
				rowNum: 10000,
				sortable: false,
				scrollOffset: 0,
				hoverrows: false,
				datatype: 'local',
				width: Global.bodyWidth() - 14,
				colNames: [],
				colModel: this.timesheet_columns,
				viewrecords: true
			} );

		}

		this.grid_dic.absence_grid = this.absence_grid;

		this.setGridHeaderBar( 'absence_grid', 'Absence' );

		this.bindGridColumnEvents();
	},

	checkIsSelectedPunchCell: function( row_id, cell_index ) {
		for ( var i = 0, m = this.select_cells_Array.length; i < m; i++ ) {
			var cell = this.select_cells_Array[i];
			if ( cell.row_id === row_id && cell.cell_index === cell_index ) {
				return true;
			}
		}

		return false;
	},

	buildTimeSheetGrid: function() {

		var $this = this;
		var grid;
		if ( !Global.isSet( this.grid ) ) {
			grid = $( this.el ).find( '#grid' );

			grid.attr( 'id', this.ui_id + '_grid' );  //Grid's id is ScriptName + _grid

			grid = $( this.el ).find( '#' + this.ui_id + '_grid' );
		}

		if ( !this.grid ) {

			this.grid = grid;

			this.grid.jqGrid( {
				altRows: true,
				alt2Rows: true,
				data: [],
				datatype: 'local',
				sortable: false,
				scrollOffset: 0,
				rowNum: 10000,
				hoverrows: false,
				ondblClickRow: function() {
					$this.onGridDblClickRow();
				},
				onSelectRow: function( row_id, flag, e ) {
					$this.onSelectRow( 'timesheet_grid', row_id, this );
				},
				onRightClickRow: function( row_id, iRow, cell_index, e ) {
					if ( !$this.checkIsSelectedPunchCell( row_id, cell_index ) ) {
						var cell_val = $( e.target ).closest( "td,th" ).html();
						$this.onCellSelect( 'timesheet_grid', row_id, cell_index, cell_val, this, e );
						$this.onSelectRow( 'timesheet_grid', row_id, this );
					}
				},
				onCellSelect: function( row_id, cell_index, cell_val, e ) {
					$this.onCellSelect( 'timesheet_grid', row_id, cell_index, cell_val, this, e );
				},
				colNames: [],
				colModel: this.timesheet_columns,
				viewrecords: true

			} );

		} else {

			this.grid.jqGrid( 'GridUnload' );
			this.grid = null;

			grid = $( this.el ).find( '#' + this.ui_id + '_grid' );
			this.grid = $( grid );
			this.grid.jqGrid( {
				altRows: true,
				alt2Rows: true,
				onSelectRow: function( row_id, flag, e ) {
					$this.onSelectRow( 'timesheet_grid', row_id, this );

				},
				onRightClickRow: function( row_id, iRow, cell_index, e ) {
					if ( !$this.checkIsSelectedPunchCell( row_id, cell_index ) ) {
						var cell_val = $( e.target ).closest( "td,th" ).html();
						$this.onCellSelect( 'timesheet_grid', row_id, cell_index, cell_val, this, e );
						$this.onSelectRow( 'timesheet_grid', row_id, this );
					}
				},
				onCellSelect: function( row_id, cell_index, cell_val, e ) {
					$this.onCellSelect( 'timesheet_grid', row_id, cell_index, cell_val, this, e );
				},
				ondblClickRow: function() {
					$this.onGridDblClickRow();
				},
				data: [],
				rowNum: 10000,
				sortable: false,
				hoverrows: false,
				scrollOffset: 0,
				datatype: 'local',
				colNames: [],
				colModel: this.timesheet_columns,
				viewrecords: true
			} );

		}

		this.grid_dic.timesheet_grid = this.grid;

		this.grid_div.scroll( function( e ) {
			$this.scroll_position = $this.grid_div.scrollTop();
		} );

	},

	onGridDblClickRow: function(name) {
		var len = this.context_menu_array.length;
		var need_break = false;
		for ( var i = 0; i < len; i++ ) {
			if ( need_break ) {
				break;
			}
			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			switch ( id ) {
				case ContextMenuIconName.edit:
					if ( context_btn.is( ':visible' ) && !context_btn.hasClass( 'disable-image' ) ) {
						ProgressBar.showOverlay();
						this.onEditClick();
						return;
					}
					break;
			}
		}
		for ( i = 0; i < len; i++ ) {
			if ( need_break ) {
				break;
			}
			context_btn = this.context_menu_array[i];
			id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			switch ( id ) {
				case ContextMenuIconName.view:
					need_break = true;
					if ( context_btn.is( ':visible' ) && !context_btn.hasClass( 'disable-image' ) ) {
						ProgressBar.showOverlay();
						this.onViewClick();
						return;
					}
					break;
			}
		}
		for ( i = 0; i < len; i++ ) {
			context_btn = this.context_menu_array[i];
			id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			switch ( id ) {
				case 'addAbsenceIcon':
				case ContextMenuIconName.add:
					// There are 2 add icons, one for punch and one for absence.
					// We must ensure to check the right one to provide permissions for the add click or absence will be allowed based on the punch permissions
					if ( name == 'absence' && id != 'addAbsenceIcon' ) {
						continue;
					}

					if ( context_btn.is(':visible') && !context_btn.hasClass('disable-image') ) {
						if ( this.isPunchCells() ) {
							ProgressBar.showOverlay();
							this.onAddClick();
						}
						return;
					}
					break;
			}
		}
	},

	isPunchCells: function() {
		var result = false;
		var cell = this.select_cells_Array && this.select_cells_Array.length > 0 && this.select_cells_Array[0];
		var row = cell && this.timesheet_data_source[parseInt( cell.row_id ) - 1];
		if ( row && row.type === TimeSheetViewController.PUNCH_ROW ) {
			result = true;
		} else if ( this.absence_select_cells_Array && this.absence_select_cells_Array.length > 0 ) {
			result = true;
		}
		return result;
	},

	buildAccumulatedGrid: function() {

		var grid_id = 'accumulated_time_grid';
		var title = $.i18n._( 'Accumulated Time' );

		var $this = this;
		var grid;
		if ( !Global.isSet( this[grid_id] ) ) {
			grid = $( this.el ).find( '#' + grid_id );

			grid.attr( 'id', this.ui_id + '_' + grid_id );	//Grid's id is ScriptName + _grid

			grid = $( this.el ).find( '#' + this.ui_id + '_' + grid_id );
		}

		if ( !this[grid_id] ) {

			this[grid_id] = grid;

			this[grid_id].jqGrid( {
				data: [],
				datatype: 'local',
				scrollOffset: 0,
				hoverrows: false,
				sortable: false,
				altRows: true,
				onSelectRow: function( row_id, flag, e ) {
					$this.onSelectRow( 'accumulated_grid', row_id, this );
				},
				onRightClickRow: function( row_id, iRow, cell_index, e ) {
					var cell_val = $( e.target ).closest( "td,th" ).html();
					$this.onCellSelect( 'accumulated_grid', row_id, cell_index, cell_val, this, e );
					$this.onSelectRow( 'accumulated_grid', row_id, this );
				},
				onCellSelect: function( row_id, cell_index, cell_val, e ) {
					$this.onCellSelect( 'accumulated_grid', row_id, cell_index, cell_val, this, e );
				},
				ondblClickRow: function() {
					$this.onAccumulatedTimeClick();
				},
				width: Global.bodyWidth() - 14,
				rowNum: 10000,
				colNames: [],
				colModel: this.timesheet_columns,
				viewrecords: true

			} );

		} else {

			this[grid_id].jqGrid( 'GridUnload' );
			this[grid_id] = null;

			grid = $( this.el ).find( '#' + this.ui_id + '_' + grid_id );
			this[grid_id] = $( grid );
			this[grid_id].jqGrid( {
				altRows: true,
				onSelectRow: function( row_id, flag, e ) {
					$this.onSelectRow( 'accumulated_grid', row_id, this );
				},
				onRightClickRow: function( row_id, iRow, cell_index, e ) {
					var cell_val = $( e.target ).closest( "td,th" ).html();
					$this.onCellSelect( 'accumulated_grid', row_id, cell_index, cell_val, this, e );
					$this.onSelectRow( 'accumulated_grid', row_id, this );
				},
				onCellSelect: function( row_id, cell_index, cell_val, e ) {
					$this.onCellSelect( 'accumulated_grid', row_id, cell_index, cell_val, this, e );
				},
				ondblClickRow: function() {
					$this.onAccumulatedTimeClick();
				},
				data: [],
				rowNum: 10000,
				sortable: false,
				hoverrows: false,
				scrollOffset: 0,
				datatype: 'local',
				width: Global.bodyWidth() - 14,
				colNames: [],
				colModel: this.timesheet_columns,
				viewrecords: true
			} );

		}
		this.grid_dic[grid_id] = this[grid_id];

		this.setGridHeaderBar( grid_id, title );
	},

	buildSubGrid: function( grid_id, title ) {

		var $this = this;
		var grid;
		if ( !Global.isSet( this[grid_id] ) ) {
			grid = $( this.el ).find( '#' + grid_id );

			grid.attr( 'id', this.ui_id + '_' + grid_id );	//Grid's id is ScriptName + _grid

			grid = $( this.el ).find( '#' + this.ui_id + '_' + grid_id );
		}

		if ( grid_id === 'premium_grid' ) {
			if ( !this[grid_id] ) {
				this[grid_id] = grid;
				grid.addClass( 'premium-grid' );
				this[grid_id].jqGrid( {
					data: [],
					datatype: 'local',
					scrollOffset: 0,
					hoverrows: false,
					sortable: false,
					altRows: true,
					width: Global.bodyWidth() - 14,
					rowNum: 10000,
					colNames: [],
					colModel: this.timesheet_columns,
					viewrecords: true,
					onSelectRow: function( row_id, flag, e ) {
						$this.onSelectRow( 'premium_grid', row_id, this );
					},
					onRightClickRow: function( row_id, iRow, cell_index, e ) {
						var cell_val = $( e.target ).closest( "td,th" ).html();
						$this.onCellSelect( 'premium_grid', row_id, cell_index, cell_val, this, e );
						$this.onSelectRow( 'premium_grid', row_id, this );
					},
					onCellSelect: function( row_id, cell_index, cell_val, e ) {
						$this.onCellSelect( 'premium_grid', row_id, cell_index, cell_val, this, e );
					},
					ondblClickRow: function() {
						if ( grid_id === 'premium_grid' ) {
							$this.onAccumulatedTimeClick();
						}
					}
				} );

			} else {
				this[grid_id].jqGrid( 'GridUnload' );
				this[grid_id] = null;
				grid = $( this.el ).find( '#' + this.ui_id + '_' + grid_id );
				grid.addClass( 'premium-grid' );
				this[grid_id] = $( grid );
				this[grid_id].jqGrid( {
					altRows: true,
					data: [],
					rowNum: 10000,
					sortable: false,
					hoverrows: false,
					scrollOffset: 0,
					datatype: 'local',
					width: Global.bodyWidth() - 14,
					colNames: [],
					colModel: this.timesheet_columns,
					viewrecords: true,
					onSelectRow: function( row_id, flag, e ) {
						$this.onSelectRow( 'premium_grid', row_id, this );
					},
					onRightClickRow: function( row_id, iRow, cell_index, e ) {
						var cell_val = $( e.target ).closest( "td,th" ).html();
						$this.onCellSelect( 'premium_grid', row_id, cell_index, cell_val, this, e );
						$this.onSelectRow( 'premium_grid', row_id, this );
					},
					onCellSelect: function( row_id, cell_index, cell_val, e ) {
						$this.onCellSelect( 'premium_grid', row_id, cell_index, cell_val, this, e );
					},
					ondblClickRow: function() {
						if ( grid_id === 'premium_grid' ) {
							$this.onAccumulatedTimeClick();
						}
					}
				} );

			}
		} else {
			if ( !this[grid_id] ) {

				this[grid_id] = grid;

				this[grid_id].jqGrid( {
					data: [],
					datatype: 'local',
					scrollOffset: 0,
					hoverrows: false,
					sortable: false,
					altRows: true,
					width: Global.bodyWidth() - 14,
					rowNum: 10000,
					colNames: [],
					colModel: this.timesheet_columns,
					viewrecords: true,

					//FIXME: ucomment when fixing the selection clearing code
					onCellSelect: function( row_id, cell_index, cell_val, e ) {
						$this.onCellSelect( grid_id, row_id, cell_index, cell_val, this, e );
					},
				} );

			} else {

				this[grid_id].jqGrid( 'GridUnload' );
				this[grid_id] = null;

				grid = $( this.el ).find( '#' + this.ui_id + '_' + grid_id );
				this[grid_id] = $( grid );
				this[grid_id].jqGrid( {
					altRows: true,
					data: [],
					rowNum: 10000,
					sortable: false,
					hoverrows: false,
					scrollOffset: 0,
					datatype: 'local',
					width: Global.bodyWidth() - 14,
					colNames: [],
					colModel: this.timesheet_columns,
					viewrecords: true,

					//FIXME: ucomment when fixing the selection clearing code
					onCellSelect: function( row_id, cell_index, cell_val, e ) {
						$this.onCellSelect( grid_id, row_id, cell_index, cell_val, this, e );
					},
				} );

			}
		}

		this.grid_dic[grid_id] = this[grid_id];

		this.setGridHeaderBar( grid_id, title );
	},

	setGridSExpendOrCollapseStatus: function( grid_id, title ) {
		var grid = this.grid_dic[grid_id];
		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_' + grid_id + ']' )[0] );
		var title_bar = table.find( '.title-bar' );
		this.setGridHeight( grid_id );

		if ( LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] !== true ) {
			grid.setGridHeight( 0 );
		}

		this.updateGridHeaderBar( grid_id, title );

	},

	//Show expend and collapse button in grid title bar
	setGridExpendButton: function( grid_id, title ) {
		var $this = this;
		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_' + grid_id + ']' )[0] );
		var title_bar = table.find( '.title-bar' );

		var img = $( "<img>" );
		img.addClass( 'grid-expend-btn' );

		if ( !Global.isSet( LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] ) ||
			LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] === true ) {

			img.attr( 'src', Global.getRealImagePath( 'images/big_collapse.png' ) );
			LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] = true;

		} else {
			img.attr( 'src', Global.getRealImagePath( 'images/big_expand.png' ) );
			LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] = false;
		}

		title_bar.append( img );

		this.setGridSExpendOrCollapseStatus( grid_id, title );

		img.click( function( e ) {

			if ( LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] === true ) {
				$( this ).attr( 'src', Global.getRealImagePath( 'images/big_expand.png' ) );
				LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] = false;
				$this.setGridSExpendOrCollapseStatus( grid_id, title );
			} else {
				$( this ).attr( 'src', Global.getRealImagePath( 'images/big_collapse.png' ) );
				LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] = true;
				$this.setGridSExpendOrCollapseStatus( grid_id, title );

			}
		} );

	},

	updateGridHeaderBar: function( grid_id, description ) {
		var label = description;
		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_' + grid_id + ']' )[0] );
		var title_span = table.find( '.title-span' );
		var count = 0;

		if ( LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] !== true ) {
			switch ( grid_id ) {
				case 'branch_grid':
					label = label + ' (' + (this.branch_cell_count) + ')';
					break;
				case 'department_grid':
					label = label + ' (' + (this.department_cell_count) + ')';
					break;
				case 'job_item_grid':
					label = label + ' (' + (this.task_cell_count) + ')';
					break;
				case 'job_grid':
					label = label + ' (' + (this.job_cell_count) + ')';
					break;
				case 'premium_grid':
					label = label + ' (' + (this.premium_cell_count) + ')';
					break;
				case 'absence_grid':
					label = label + ' (' + (this.absence_cell_count) + ')';
					break;
				case 'punch_note_grid':
					label = label + ' (' + (this.punch_note_account) + ')';
					break;
			}
		}

		title_span.text( label );
	},

	setGridHeaderBar: function( grid_id, description ) {
		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_' + grid_id + ']' )[0] );
		table.empty();

		var label = $.i18n._( description );

		var title_bar = $( "<div class='title-bar'><span class='title-span'>" + label + "</span></div>" );
		table.append( title_bar );
	},

	buildManualTimeSheetsColumns: function() {
		this.day_dic = {};
		for ( var i = 0; i < 7; i++ ) {
			var current_date = new Date( new Date( this.start_date.getTime() ).setDate( this.start_date.getDate() + i ) );
			var day_text = current_date.format( this.day_format );
			var date_text = current_date.format( this.date_format );
			this.day_dic['day_' + i] = {value: day_text + '<br>' + date_text, field: current_date.format()};
		}
	},

	getManualTimeSheetData: function( callBack ) {
		var $this = this;
		var args = {};
		args.filter_data = {
			user_id: this.getSelectEmployee(),
			object_type_id: 10,
			start_date: this.start_date.format(),
			end_date: this.end_date.format()
		};
		args.filter_columns = {
			"id": true,
			"date_stamp": true,
			"total_time": true,
			"object_type": true,
			"name": true,
			"branch_id": true,
			"department_id": true,
			"branch": true,
			"department": true,
			"job": true,
			"job_item": true,
			"job_id": true,
			"job_item_id": true,
			"note": true,
			"override": true
		};
		this.api_user_date_total.getUserDateTotal( args, true, {
			onResult: function( result ) {
				$this.manual_timesheet_data = result.getResult();
				callBack();
			}
		} );
	},

	buildManualTimeSheetData: function() {
		this.time_sheet_data_overrode_true_map = {};
		this.time_sheet_data_overrode_false_map = {};
		var sort_by_fields = ['branch_id', 'department_id', 'job_id', 'job_item_id'],
			manual_timesheet_data_group_array = [],
			override_true_array = [],
			override_false_array = [];
		this.manual_timesheet_data.sort( Global.m_sort_by( sort_by_fields ) );
		manual_timesheet_data_group_array = _.groupBy( this.manual_timesheet_data, 'override' );
		override_true_array = manual_timesheet_data_group_array[true];
		override_false_array = manual_timesheet_data_group_array[false];
		doNext( override_true_array, this.time_sheet_data_overrode_true_map );
		doNext( override_false_array, this.time_sheet_data_overrode_false_map );

		function doNext( manual_timesheet_data, target_map ) {
			!manual_timesheet_data && (manual_timesheet_data = []);
			for ( var i = 0, m = manual_timesheet_data.length; i < m; i++ ) {
				var data = manual_timesheet_data[i];
				var key = data.branch_id + '-' + data.department_id + '-' + data.job_id + '-' + data.job_item_id;
				if ( !target_map[key] ) {
					target_map[key] = {};
					target_map[key].branch_id = data.branch_id;
					target_map[key].department_id = data.department_id;
					target_map[key].job_id = data.job_id;
					target_map[key].job_item_id = data.job_item_id;
					target_map[key].branch = data.branch;
					target_map[key].department = data.department;
					target_map[key].job = data.job;
					target_map[key].job_item = data.job_item;
					target_map[key].override = data.override;
					target_map[key][data.date_stamp] = data;
				} else if ( target_map[key][data.date_stamp] ) {
					// If already has data in this day, create next row.
					var j = 1;
					while ( true ) {
						if ( !target_map[key + '-' + j] ) {
							target_map[key + '-' + j] = {};
							target_map[key + '-' + j].branch_id = data.branch_id;
							target_map[key + '-' + j].department_id = data.department_id;
							target_map[key + '-' + j].job_id = data.job_id;
							target_map[key + '-' + j].job_item_id = data.job_item_id;
							target_map[key + '-' + j].branch = data.branch;
							target_map[key + '-' + j].department = data.department;
							target_map[key + '-' + j].job = data.job;
							target_map[key + '-' + j].job_item = data.job_item;
							target_map[key + '-' + j].override = data.override;
							target_map[key + '-' + j][data.date_stamp] = data;
							break;
						} else if ( !target_map[key + '-' + j][data.date_stamp] ) {
							target_map[key + '-' + j][data.date_stamp] = data;
							break;
						} else {
							j = j + 1;
						}
					}
				} else {
					target_map[key][data.date_stamp] = data;
				}
			}
		}

	},

	initInsideEditorData: function( updateExistedCell ) {
		var $this = this;
		for ( var key in this.day_dic ) {
			this.$( '#' + key + '_date' ).html( this.day_dic[key].value );
			this.$( '#' + key + '_date' ).addClass( 'manual_grid_day_' + Global.strToDate( this.day_dic[key].field ).format( this.full_format ) );
			this.$( '#' + key + '_date' ).attr( 'current_date', 'manual_grid_day_' + Global.strToDate( this.day_dic[key].field ).format( this.full_format ) );
			this.$( '#' + key + '_date' ).unbind( 'click' ).bind( 'click', function( e ) {
				var target = e.currentTarget;
				var field = $( target ).attr( 'current_date' );
				field = field.substring( 16, field.length );
				var date = Global.strToDate( field, $this.full_format );
				if ( date && date.getYear() > 0 ) {
					$this.setDatePickerValue( date.format( Global.getLoginUserDateFormat() ) );
					$this.highLightSelectDay();
					$this.reLoadSubGridsSource();
				}
			} );
			this.$( '.is-saving-manual-grid' ).removeClass( 'is-saving-manual-grid' );
		}
		if ( this.is_auto_switch ) {
			this.is_auto_switch = false;
			doNext();
		} else {
			this.getManualTimeSheetData( function() {
				doNext();
			} );
		}

		function doNext() {
			$this.is_saving_manual_grid = false;
			$this.setDefaultMenu();
			if ( !updateExistedCell ) {
				$this.editor.removeAllRows();
				if ( $this.manual_timesheet_data.length > 0 ) {
					$this.buildManualTimeSheetData();
					_.map( $this.time_sheet_data_overrode_false_map, function( data ) {
						$this.editor.addRow( data );
					} );
					_.map( $this.time_sheet_data_overrode_true_map, function( data ) {
						$this.editor.addRow( data );
					} );
					if ( _.isEmpty( $this.time_sheet_data_overrode_true_map ) ) {
						$this.editor.addRow();
					}
				} else {
					$this.editor.addRow();
				}
			} else {
				if ( $this.manual_timesheet_data.length > 0 ) {
					$this.buildManualTimeSheetData();
					for ( var map_key in $this.time_sheet_data_overrode_true_map ) {
						var data = $this.time_sheet_data_overrode_true_map[map_key];
						for ( var map_key_2 in data ) {
							var item = data[map_key_2];
							if ( !_.isObject( item ) ) {
								continue
							}
							var key = item.date_stamp + '-' + (($this.show_branch_ui && item.branch_id) ? item.branch_id : 0) +
								'-' + (($this.show_department_ui && item.department_id) ? item.department_id : 0)
								+ '-' + (($this.show_job_ui && item.job_id && LocalCacheData.getCurrentCompany().product_edition_id >= 20) ? item.job_id : 0) +
								'-' + (($this.show_job_item_ui && item.job_item_id && LocalCacheData.getCurrentCompany().product_edition_id >= 20) ? item.job_item_id : 0) +
								'-' + item.total_time;
							if ( $this.manual_grid_records_map[item.id + '-' + key] ) {
								$this.manual_grid_records_map[item.id + '-' + key][item.date_stamp].setValue( item.total_time );
							} else if ( $this.manual_grid_records_map[key] ) {
								$this.manual_grid_records_map[key].current_edit_item[item.date_stamp] = item;
								$this.manual_grid_records_map[key][item.date_stamp].setValue( item.total_time );
							}
						}
					}
				}
			}
			if ( $this.save_manual_grid_after_save ) {
				$this.autoSaveManualPunch();
				$this.save_manual_grid_after_save = false;
				return;
			}
		}
	},

	insideEditorAddRow: function( data, index ) {
		var $this = this;
		if ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) {
			var job_item_api = new (APIFactory.getAPIClass( 'APIJobItem' ))();
			var job_api = new (APIFactory.getAPIClass( 'APIJob' ))();
		}
		var args;
		if ( !data ) {
			data = {};
			if ( index >= 0 ) {
				var widget_row = this.rows_widgets_array[index - 3];
				if ( this.parent_controller.show_branch_ui ) {
					if ( widget_row.branch_id ) {
						data.branch_id = widget_row.branch_id.getValue();
					} else if ( widget_row.current_edit_item && widget_row.current_edit_item.branch_id ) {
						data.branch_id = widget_row.current_edit_item.branch_id;
					} else {
						data.branch_id = false;
					}
				}
				if ( this.parent_controller.show_department_ui ) {
					if ( widget_row.department_id ) {
						data.department_id = widget_row.department_id.getValue();
					} else if ( widget_row.current_edit_item && widget_row.current_edit_item.department_id ) {
						data.department_id = widget_row.current_edit_item.department_id;
					} else {
						data.department_id = false;
					}
				}
				if ( this.parent_controller.show_job_ui && LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) {
					if ( widget_row.job_id ) {
						data.job_id = widget_row.job_id.getValue();
					} else if ( widget_row.current_edit_item && widget_row.current_edit_item.job_id ) {
						data.job_id = widget_row.current_edit_item.job_id;
					} else {
						data.job_id = false;
					}
				}
				if ( this.parent_controller.show_job_item_ui && LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) {
					if ( widget_row.job_item_id ) {
						data.job_item_id = widget_row.job_item_id.getValue();
					} else if ( widget_row.current_edit_item && widget_row.current_edit_item.job_item_id ) {
						data.job_item_id = widget_row.current_edit_item.job_item_id;
					} else {
						data.job_item_id = false;
					}
				}
			}

		}
		var row = this.getRowRender(); //Get Row render
		var render = this.getRender(); //get render, should be a table
		var widgets = {}; //Save each row's widgets
		var form_item_input;
		//Build row widgets
		//Branch
		if ( this.parent_controller.show_branch_ui ) {
			if ( data.hasOwnProperty( 'override' ) && !data.override ) {
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_input.TText( {field: 'branch'} );
				form_item_input.setValue( data.branch );
				row.children().eq( 2 ).append( form_item_input );
			} else {
				form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
				form_item_input.AComboBox( {
					api_class: (APIFactory.getAPIClass( 'APIBranch' )),
					width: 90,
					layout_name: ALayoutIDs.BRANCH,
					show_search_inputs: true,
					set_empty: true,
					field: 'branch_id',
					is_static_width: true
				} );
				widgets[form_item_input.getField()] = form_item_input;
				form_item_input.setValue( data.hasOwnProperty( 'branch_id' ) ? data.branch_id : this.parent_controller.getSelectEmployee( true ).default_branch_id );
				row.children().eq( 2 ).append( form_item_input );
			}
		} else {
			row.children().eq( 2 ).hide();
		}
		//Department
		if ( this.parent_controller.show_department_ui ) {
			if ( data.hasOwnProperty( 'override' ) && !data.override ) {
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_input.TText( {field: 'department'} );
				form_item_input.setValue( data.department );
				row.children().eq( 3 ).append( form_item_input );
			} else {
				form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
				form_item_input.AComboBox( {
					api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
					width: 90,
					layout_name: ALayoutIDs.DEPARTMENT,
					show_search_inputs: true,
					set_empty: true,
					field: 'department_id',
					is_static_width: true
				} );
				widgets[form_item_input.getField()] = form_item_input;
				form_item_input.setValue( data.hasOwnProperty( 'department_id' ) ? data.department_id : this.parent_controller.getSelectEmployee( true ).default_department_id );
				row.children().eq( 3 ).append( form_item_input );
			}
		} else {
			row.children().eq( 3 ).hide();
		}
		//Job
		if ( this.parent_controller.show_job_ui && LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) {
			if ( data.hasOwnProperty( 'override' ) && !data.override ) {
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_input.TText( {field: 'job'} );
				form_item_input.setValue( data.job );
				row.children().eq( 4 ).append( form_item_input );
			} else {
				var job_form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
				job_form_item_input.AComboBox( {
					api_class: (APIFactory.getAPIClass( 'APIJob' )),
					width: 90,
					layout_name: ALayoutIDs.JOB,
					show_search_inputs: true,
					set_empty: true,
					field: 'job_id',
					is_static_width: true,
					setRealValueCallBack: (function( val ) {
						if ( val ) {
							job_coder.setValue( val.manual_id );
						}
					})
				} );
				widgets[job_form_item_input.getField()] = job_form_item_input;
				// Set default args
				args = {};
				args.filter_data = {status_id: 10, user_id: this.parent_controller.getSelectEmployee()};
				job_form_item_input.setDefaultArgs( args );
				var job_id = data.hasOwnProperty( 'job_id' ) ? data.job_id : this.parent_controller.getSelectEmployee( true ).default_job_id;
				job_form_item_input.setValue( job_id );
				var job_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				job_coder.TTextInput( {field: 'job_quick_search', disable_keyup_event: true, width: 30} );
				job_coder.css( 'display', 'inline-block' );
				job_form_item_input.css( 'display', 'inline-block' );
				row.children().eq( 4 ).append( job_coder );
				row.children().eq( 4 ).append( job_form_item_input );
				job_coder.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
					onJobQuickSearch( target.getField(), target.getValue() );
				} );
			}
		} else {
			row.children().eq( 4 ).hide();
		}

		//Task
		if ( this.parent_controller.show_job_item_ui && LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) {
			if ( data.hasOwnProperty( 'override' ) && !data.override ) {
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_input.TText( {field: 'job_item'} );
				form_item_input.setValue( data.job_item );
				row.children().eq( 5 ).append( form_item_input );
			} else {
				form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
				form_item_input.AComboBox( {
					api_class: (APIFactory.getAPIClass( 'APIJobItem' )),
					width: 90,
					layout_name: ALayoutIDs.JOB_ITEM,
					show_search_inputs: true,
					set_empty: true,
					field: 'job_item_id',
					is_static_width: true,
					setRealValueCallBack: (function( val ) {
						if ( val ) {
							job_item_coder.setValue( val.manual_id );
						}
					}),
				} );
				args = {};
				args.filter_data = {status_id: 10, job_id: job_id};
				form_item_input.setDefaultArgs( args );
				form_item_input.setValue( data.hasOwnProperty( 'job_item_id' ) ? data.job_item_id : this.parent_controller.getSelectEmployee( true ).default_job_item_id );
				var job_item_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				job_item_coder.TTextInput( {field: 'job_item_quick_search', disable_keyup_event: true, width: 30} );
				widgets[form_item_input.getField()] = form_item_input;
				job_item_coder.css( 'display', 'inline-block' );
				form_item_input.css( 'display', 'inline-block' );
				row.children().eq( 5 ).append( job_item_coder );
				row.children().eq( 5 ).append( form_item_input );
				job_item_coder.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
					onJobQuickSearch( target.getField(), target.getValue() );
				} );
			}
		} else {
			row.children().eq( 5 ).hide();
		}
		//day 0
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: this.parent_controller.day_dic['day_0'].field,
			width: 41,
			mode: 'time_unit',
			display_na: false,
			need_parser_sec: true,
			do_validate: false
		} );
		form_item_input.setValue( data[form_item_input.getField()] ? data[form_item_input.getField()].total_time : 0 );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 6 ).append( form_item_input );

		//day 1
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: this.parent_controller.day_dic['day_1'].field,
			width: 41,
			mode: 'time_unit',
			display_na: false,
			need_parser_sec: true,
			do_validate: false
		} );
		form_item_input.setValue( data[form_item_input.getField()] ? data[form_item_input.getField()].total_time : 0 );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 7 ).append( form_item_input );

		//day 2
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: this.parent_controller.day_dic['day_2'].field,
			width: 41,
			mode: 'time_unit',
			display_na: false,
			need_parser_sec: true,
			do_validate: false
		} );
		form_item_input.setValue( data[form_item_input.getField()] ? data[form_item_input.getField()].total_time : 0 );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 8 ).append( form_item_input );

		//day 3
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: this.parent_controller.day_dic['day_3'].field,
			width: 41,
			mode: 'time_unit',
			display_na: false,
			need_parser_sec: true,
			do_validate: false
		} );
		form_item_input.setValue( data[form_item_input.getField()] ? data[form_item_input.getField()].total_time : 0 );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 9 ).append( form_item_input );

		//day 4
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: this.parent_controller.day_dic['day_4'].field,
			width: 41,
			mode: 'time_unit',
			display_na: false,
			need_parser_sec: true,
			do_validate: false
		} );
		form_item_input.setValue( data[form_item_input.getField()] ? data[form_item_input.getField()].total_time : 0 );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 10 ).append( form_item_input );

		//day 5
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: this.parent_controller.day_dic['day_5'].field,
			width: 41,
			mode: 'time_unit',
			display_na: false,
			need_parser_sec: true,
			do_validate: false
		} );
		form_item_input.setValue( data[form_item_input.getField()] ? data[form_item_input.getField()].total_time : 0 );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 11 ).append( form_item_input );

		//day 6
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: this.parent_controller.day_dic['day_6'].field,
			width: 41,
			mode: 'time_unit',
			display_na: false,
			need_parser_sec: true,
			do_validate: false
		} );
		form_item_input.setValue( data[form_item_input.getField()] ? data[form_item_input.getField()].total_time : 0 );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 12 ).append( form_item_input );
		if ( data.hasOwnProperty( 'override' ) && !data.override ) {
			row.children().eq( 1 ).children().hide();
		}
		for ( var key in widgets ) {
			var item = widgets[key];
			if ( data.hasOwnProperty( 'override' ) && !data.override ) {
				item.setEnabled && item.setEnabled( false );
				item.getValue() > 0 && item.hasClass( 't-text-input' ) >= 0 && item.css( 'color', 'red' );
			}
			item.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				target.is_changed = true;
				if ( target.getField() === 'job_id' ) {
					widgets.is_changed = true;
					job_coder.setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
					setJobItemValueWhenJobChanged( target.getValue( true ) );
				} else if ( target.getField() === 'job_item_id' ) {
					widgets.is_changed = true;
					job_item_coder.setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
				} else if ( target.getField() === 'branch_id' || target.getField() === 'department_id' ) {
					widgets.is_changed = true;
				}
				$this.parent_controller.autoSaveManualPunch();
			} )
		}

		if ( typeof index != 'undefined' ) {
			row.insertAfter( $( render ).find( 'tr' ).eq( index ) );
			this.rows_widgets_array.splice( (index), 0, widgets );

		} else {
			$( render ).append( row );
			this.rows_widgets_array.push( widgets );
		}
		//Save current set item
		widgets.current_edit_item = data;
		this.addIconsEvent( row ); //Bind event to add and minus icon

		function onJobQuickSearch( key, value ) {
			var args = {};
			if ( key === 'job_quick_search' ) {
				args.filter_data = {manual_id: value, user_id: $this.parent_controller.getSelectEmployee(), status_id: "10"};
				job_api.getJob( args, {
					onResult: function( result ) {
						var result_data = result.getResult();
						widgets.is_changed = true;
						$this.parent_controller.autoSaveManualPunch();
						if ( result_data.length > 0 ) {
							widgets['job_id'].setValue( result_data[0].id );
							setJobItemValueWhenJobChanged( result_data[0] );
						} else {
							widgets['job_id'].setValue( '' );
							setJobItemValueWhenJobChanged( false );
						}

					}
				} );
			} else if ( key === 'job_item_quick_search' ) {
				args.filter_data = {manual_id: value, job_id: widgets['job_id'].getValue(), status_id: '10'};
				job_item_api.getJobItem( args, {
					onResult: function( result ) {
						var result_data = result.getResult();
						widgets.is_changed = true;
						$this.parent_controller.autoSaveManualPunch();
						if ( result_data.length > 0 ) {
							widgets['job_item_id'].setValue( result_data[0].id );

						} else {
							widgets['job_item_id'].setValue( '' );
						}

					}
				} );
			}
		}

		function setJobItemValueWhenJobChanged( job ) {
			var job_item_widget = widgets['job_item_id'];
			if ( !job_item_widget ) return;
			var current_job_item_id = job_item_widget.getValue();
			job_item_widget.setSourceData( null );
			var args = {};
			args.filter_data = {status_id: 10, job_id: widgets['job_id'].getValue()};
			job_item_widget.setDefaultArgs( args );
			if ( current_job_item_id ) {
				var new_arg = Global.clone( args );
				new_arg.filter_data.id = current_job_item_id;
				new_arg.filter_columns = job_item_widget.getColumnFilter();
				job_item_api.getJobItem( new_arg, {
					onResult: function( task_result ) {
						var data = task_result.getResult();
						if ( data.length > 0 ) {
							job_item_widget.setValue( current_job_item_id );
						} else {
							setDefaultData();
						}
					}
				} )

			} else {
				setDefaultData();
			}

			function setDefaultData() {
				if ( widgets['job_id'].getValue() ) {
					job_item_widget.setValue( job.default_item_id );
					if ( job.default_item_id === false || job.default_item_id === 0 ) {
						job_item_coder.setValue( '' );
					}

				} else {
					job_item_widget.setValue( '' );
                    job_item_coder.setValue( '' );
				}
			}
		}

	},

	autoSaveManualPunch: function() {
		var $this = this;
		if ( this.is_saving_manual_grid ) {
			this.save_manual_grid_after_save = true;
			return;
		}
		this.wait_auto_save && clearTimeout( this.wait_auto_save );
		this.wait_auto_save = setTimeout( function() {
			if ( $this.getPunchMode() === 'manual' ) {
				ProgressBar.showOverlay();
				$this.onSaveClick();
			}
		}, 2000 );
	},

	insideEditorGetValue: function( isSave ) {
		var len = this.rows_widgets_array.length;
		var result = [];
		for ( var i = 0; i < len; i++ ) {
			var row = this.rows_widgets_array[i];
			for ( var j = 0; j < 7; j++ ) {
				var current_date = new Date( new Date( this.parent_controller.start_date.getTime() ).setDate( this.parent_controller.start_date.getDate() + j ) );
				var field = current_date.format();
				var common_record = {};
				if ( !row[field] )
					continue;
				common_record.total_time = row[field].getValue();
				if ( row[field].is_changed || (row.is_changed && row.current_edit_item[field]) ) {
					row.branch_id && (common_record.branch_id = row.branch_id.getValue());
					row.department_id && (common_record.department_id = row.department_id.getValue());
					row.job_id && (common_record.job_id = row.job_id.getValue());
					row.job_item_id && (common_record.job_item_id = row.job_item_id.getValue());
					common_record.date_stamp = field;
					common_record.user_id = this.parent_controller.getSelectEmployee();
					common_record.object_type_id = 10;
					common_record.override = true;
					common_record.row = row;
					row.current_edit_item[field] && (common_record.id = row.current_edit_item[field].id);
					result.push( common_record );
					if ( isSave ) {
						row[field].is_changed = false;
						row[field].addClass( 'is-saving-manual-grid' );
					}
				}
			}
			if ( isSave ) {
				row.is_changed = false;
			}
		}

		return result;
	},

	insideEditorRemoveRow: function( row ) {
		var $this = this;
		var index = row[0].rowIndex - 3;
		var widget_row = this.rows_widgets_array[index];
		var has_value = false;
		for ( var j = 0; j < 7; j++ ) {
			var current_date = new Date( new Date( this.parent_controller.start_date.getTime() ).setDate( this.parent_controller.start_date.getDate() + j ) );
			var field = current_date.format();
			if ( widget_row[field].getValue() > 0 || widget_row.current_edit_item[field] ) {
				has_value = true;
				TAlertManager.showConfirmAlert( $.i18n._( 'You are about to delete the entire week worth of time for this row. Are you sure you wish to continue?' ), "", doNext );
				break;
			}
		}
		!has_value && doNext( true );
		function doNext( flag ) {
			if ( flag ) {
				var remove_ids = [];
				for ( var j = 0; j < 7; j++ ) {
					var current_date = new Date( new Date( $this.parent_controller.start_date.getTime() ).setDate( $this.parent_controller.start_date.getDate() + j ) );
					var field = current_date.format();
					widget_row.current_edit_item[field] && (remove_ids.push( widget_row.current_edit_item[field].id ));
				}
				if ( remove_ids.length > 0 ) {
					ProgressBar.noProgressForNextCall();
					ProgressBar.showNanobar();
					$this.is_saving_manual_grid = true;
					$this.parent_controller.setDefaultMenu();
					$this.api.deleteUserDateTotal( remove_ids, {
						onResult: function() {
							$this.parent_controller.reLoadSubGridsSource( true );
							ProgressBar.removeNanobar();
							$this.parent_controller.setDefaultMenu();
						}
					} );
				}
				row.remove();
				$this.rows_widgets_array.splice( index, 1 );
				if ( $this.rows_widgets_array.length === 0 ) {
					$this.addRow();
				}
			}
		}

	},

	buildManualTimeSheetGrid: function() {
		var args = {
			branch: $.i18n._( 'Branch' ),
			department: $.i18n._( 'Department' ),
			job: $.i18n._( 'Job' ),
			task: $.i18n._( 'Task' )
		};

		this.editor = Global.loadWidgetByName( FormItemType.INSIDE_EDITOR );
		this.editor.InsideEditor( {
			title: '',
			addRow: this.insideEditorAddRow,
			getValue: this.insideEditorGetValue,
			setValue: this.insideEditorSetValue,
			removeRow: this.insideEditorRemoveRow,
			parent_controller: this,
			render: 'views/attendance/timesheet/ManualTimeSheetInsideEditorRender.html',
			render_args: args,
			api: this.api_user_date_total,
			row_render: 'views/attendance/timesheet/ManualTimeSheetInsideEditorRow.html'
		} );
		var inside_editor_div = this.$( '.manual-timesheet-inside-editor-div' );
		inside_editor_div.append( this.editor );
	},

	buildCalendars: function() {
		var $this = this;
		if ( this.is_auto_switch ) {
			this.getManualTimeSheetData( function() {
				if ( $this.manual_timesheet_data.length > 0 ) {
					$this.buildManualTimeSheetData();
					var is_no_manual = _.isEmpty( $this.time_sheet_data_overrode_true_map );
					var is_no_punch = _.isEmpty( $this.time_sheet_data_overrode_false_map );
					if ( is_no_manual && !is_no_punch ) {
						$this.toggle_button.setValue( 'punch' );
						$this.is_auto_switch = false
					} else if ( !is_no_manual && is_no_punch ) {
						$this.toggle_button.setValue( 'manual' );
					}
					$this.reSetURL();
					doNext();
				} else {
					$this.getPunchMode() === 'punch' && ($this.is_auto_switch = false);
					doNext();
				}
			} );
		} else {
			doNext();
		}

		function doNext() {
			$this.pay_period_data = $this.full_timesheet_data.pay_period_data;
			$this.pay_period_map = $this.full_timesheet_data.timesheet_dates.pay_period_date_map;
			$this.timesheet_verify_data = $this.full_timesheet_data.timesheet_verify_data;
			$this.grid_div = $( $this.el ).find( '.timesheet-grid-div' );
			// Punch grid
			$this.buildTimeSheetsColumns();
			$this.buildTimeSheetGrid();
			if ( $this.getPunchMode() === 'manual' ) {
				$this.$( '.timesheet-punch-grid-wrapper' ).hide();
				$this.$( '.manual-timesheet-inside-editor-div' ).show();
				if ( !$this.editor ) {
					$this.buildManualTimeSheetsColumns();
					$this.buildManualTimeSheetGrid();
					$this.initInsideEditorData();
				} else {
					$this.buildManualTimeSheetsColumns();
					$this.initInsideEditorData();
				}
				if ( !$this.show_job_ui || LocalCacheData.getCurrentCompany().product_edition_id < 20 ) {
					$this.$( '.job-header' ).hide();
				}
				if ( !$this.show_job_item_ui || LocalCacheData.getCurrentCompany().product_edition_id < 20 ) {
					$this.$( '.job-item-header' ).hide();
				}
				if ( !$this.show_branch_ui ) {
					$this.$( '.branch-header' ).hide();
				}
				if ( !$this.show_department_ui ) {
					$this.$( '.department-header' ).hide();
				}
			} else {
				$this.$( '.timesheet-punch-grid-wrapper' ).show();
				$this.$( '.manual-timesheet-inside-editor-div' ).hide();
			}

			$this.buildAccumulatedGrid();

			$this.buildSubGrid( 'branch_grid', 'Branch' );
			$this.buildSubGrid( 'department_grid', 'Department' );
			$this.buildSubGrid( 'job_grid', 'Job' );
			$this.buildSubGrid( 'job_item_grid', 'Task' );
			$this.buildSubGrid( 'premium_grid', 'Premium' );
			$this.buildAbsenceGrid();
			$this.showGridBorders();
			$this.buildAccumulatedTotalGrid();
			$this.buildPunchNoteGrid();
			$this.buildVerificationGrid();
			//TimeSheet grid
			$this.buildTimeSheetSource(); //Create punch data
			$this.buildTimeSheetRequests();
			//Accumulated Time, Branch, Department, Job, Task, Pre
			$this.buildSubGridsSource();
			//Make sure exception rows goes after Lanuch and break create from buildSubGridsSource
			$this.buildTimeSheetExceptions();
			//Absence Grid source
			$this.buildAbsenceSource(); //Create punch data
			//Show punch notes in a grid
			$this.buildPunchNoteGridSource();
			//buildVerificationGridSource
			$this.buildVerificationGridSource();
			$this.setGridExpendButton( 'accumulated_time_grid', $.i18n._( 'Accumulated Time' ) );
			$this.setGridExpendButton( 'branch_grid', $.i18n._( 'Branch' ) );
			$this.setGridExpendButton( 'department_grid', $.i18n._( 'Department' ) );
			$this.setGridExpendButton( 'job_grid', $.i18n._( 'Job' ) );
			$this.setGridExpendButton( 'job_item_grid', $.i18n._( 'Task' ) );
			$this.setGridExpendButton( 'premium_grid', $.i18n._( 'Premium' ) );
			$this.setGridExpendButton( 'absence_grid', $.i18n._( 'Absence' ) );
			$this.setGridExpendButton( 'punch_note_grid', $.i18n._( 'Punch Notes' ) );
			if ( $this.getPunchMode() === 'punch' ) {
				$this.grid.clearGridData();
				$this.grid.setGridParam( {data: $this.timesheet_data_source} );
				$this.grid.trigger( 'reloadGrid' );
			}
			$this.markRegularRow( $this.accumulated_time_source );
			$this.accumulated_time_grid.clearGridData();
			$this.accumulated_time_grid.setGridParam( {data: $this.accumulated_time_source} );
			$this.accumulated_time_grid.trigger( 'reloadGrid' );
			$this.branch_grid.clearGridData();
			$this.branch_grid.setGridParam( {data: $this.branch_source} );
			$this.branch_grid.trigger( 'reloadGrid' );
			$this.department_grid.clearGridData();
			$this.department_grid.setGridParam( {data: $this.department_source} );
			$this.department_grid.trigger( 'reloadGrid' );
			$this.job_grid.clearGridData();
			$this.job_grid.setGridParam( {data: $this.job_source} );
			$this.job_grid.trigger( 'reloadGrid' );
			$this.job_item_grid.clearGridData();
			$this.job_item_grid.setGridParam( {data: $this.job_item_source} );
			$this.job_item_grid.trigger( 'reloadGrid' );
			$this.premium_grid.clearGridData();
			$this.premium_grid.setGridParam( {data: $this.premium_source} );
			$this.premium_grid.trigger( 'reloadGrid' );
			$this.absence_grid.clearGridData();
			$this.absence_grid.setGridParam( {data: $this.absence_source} );
			$this.absence_grid.trigger( 'reloadGrid' );
			if ( $this.accumulated_total_grid_source.length === 0 ) {
				$this.accumulated_total_grid_source.push();
			}
			$this.markRegularRow( $this.accumulated_total_grid_source );
			$this.accumulated_total_grid.clearGridData();
			$this.accumulated_total_grid.setGridParam( {data: $this.accumulated_total_grid_source} );
			$this.accumulated_total_grid.trigger( 'reloadGrid' );
			$this.punch_note_grid.clearGridData();
			$this.punch_note_grid.setGridParam( {data: $this.punch_note_grid_source} );
			$this.punch_note_grid.trigger( 'reloadGrid' );
			$this.verification_grid.clearGridData();
			$this.verification_grid.setGridParam( {data: $this.verification_grid_source} );
			$this.verification_grid.trigger( 'reloadGrid' );
			$this.setGridSize();
			$this.setTimeSheetGridPayPeriodHeaders();
			$this.setTimeSheetGridHolidayHeaders();
			$this.highLightSelectDay();
			$this.autoOpenEditViewIfNecessary();
			$this.setScrollPosition();
			$this.initRightClickMenu();
			$this.initRightClickMenu( RightClickMenuType.ABSENCE_GRID );
			$this.showWarningMessageIfAny();
			$this.setPunchModeClass();

			if ( $this.getPunchMode() != 'punch' ) {
				var cols = $this.getManualPayPeriodDefaultTrColspan()
				for ( var i = 1; i < cols; i++ ) {
					$('.sub-grid td:nth-child(' + i + ')').css('border-right', 'none');
				}
			}
		}

		//Manual punch grid

	},

	searchDone: function() {
		//the rotate icon from search panel
		var $this = this;
		$( '.button-rotate' ).removeClass( 'button-rotate' );
		$( this.el ).attr( 'init_complete', true );
		setTimeout( function() {
			if ( $this.search_panel && typeof $this.search_panel.attr( 'search_complete' ) !== 'undefined' ) {
				$this.search_panel.attr( 'search_complete', true );
			}
		}, 4000 );
	},

	showWarningMessageIfAny: function() {
		var $this = this;
		var timesheet_grid_div;
		var warning_bar = $( this.el ).find( '.timesheet-warning-title-bar' );
		warning_bar.length > 0 && warning_bar.remove() && (warning_bar = $( this.el ).find( '.timesheet-warning-title-bar' ));
		if ( this.getPunchMode() === 'punch' ) {
			timesheet_grid_div = $( this.el ).find( '#gbox_' + this.ui_id + '_grid' );
		} else {
			timesheet_grid_div = $( this.el ).find( '.manual-timesheet-inside-editor-div' );
		}
		var user = this.getSelectEmployee( true );
		if ( !user.pay_period_schedule_id || !user.policy_group_id || !payPeriodCheck() ) {
			warning_bar = $( "<div class='timesheet-warning-title-bar'><span class='p-message'></span><span class='g-message'></span><span class='pp-message'></span></div>" );
			warning_bar.insertBefore( timesheet_grid_div );
			if ( !user.pay_period_schedule_id ) {
				warning_bar.children().eq( 0 ).html( $.i18n._( 'WARNING: Employee is not assigned to a pay period schedule.' ) );
			} else {
				warning_bar.children().eq( 0 ).html( '' );
			}
			if ( !user.policy_group_id ) {
				warning_bar.children().eq( 1 ).html( $.i18n._( 'WARNING: Employee is not assigned to a policy group.' ) );
			} else {
				warning_bar.children().eq( 1 ).html( '' );
			}

			if ( !payPeriodCheck() ) {
				warning_bar.children().eq( 2 ).html( $.i18n._( 'WARNING: Employee has day(s) not assigned to a pay period. Please perform a pay period import to correct.' ) );
			} else {
				warning_bar.children().eq( 2 ).html( '' );
			}

		} else {
			if ( warning_bar.length > 0 ) {
				warning_bar.remove();
			}
		}

		function payPeriodCheck() {
			if ( $this.start_date ) {
				for ( var i = 0; i < 7; i++ ) {
					var select_date = new Date( new Date( $this.start_date.getTime() ).setDate( $this.start_date.getDate() + i ) );
					var select_date_str = select_date.format();
					var hire_date = $this.getSelectEmployee( true ).hire_date;
					var termination_date = $this.getSelectEmployee( true ).termination_date;
					//Error: Uncaught TypeError: Cannot read property 'getTime' of null in interface/html5/index.php?user_name=dustin#!m=TimeSheet&date=20151214&user_id=38599&show_wage=0 line 2947
					if ( !select_date ) continue;
					if ( select_date.getTime() < new Date().getTime() && !$this.getPayPeriod( select_date_str ) &&
						(!hire_date || select_date.getTime() >= Global.strToDate( hire_date ).getTime()) &&
						(!termination_date || select_date.getTime() <= Global.strToDate( termination_date ).getTime()) ) {
						return false;
					}

				}
			}

			return true;
		}

	},

	autoOpenEditViewIfNecessary: function() {
		//Auto open edit view. Should set in IndexController

		switch ( LocalCacheData.current_doing_context_action ) {
			case 'edit':
				if ( LocalCacheData.edit_id_for_next_open_view ) {
					this.onEditClick( LocalCacheData.edit_id_for_next_open_view, LocalCacheData.all_url_args.t );
					LocalCacheData.edit_id_for_next_open_view = null;
				}

				break;
			case 'view':
				if ( LocalCacheData.edit_id_for_next_open_view ) {
					this.onViewClick( LocalCacheData.edit_id_for_next_open_view, LocalCacheData.all_url_args.t );
					LocalCacheData.edit_id_for_next_open_view = null;
				}
				break;
			case 'new':
				if ( !this.edit_view ) {
					if ( LocalCacheData.all_url_args.t === 'absence' ) {
						this.absence_model = true;
					} else {
						this.absence_model = false;
					}
					this.onAddClick();
				}
				break;
		}

		this.autoOpenEditOnlyViewIfNecessary();

	},

	getWeekDayIndexFromADate: function( date_string ) {

		var len = this.timesheet_columns.length;

		for ( var i = 1; i < len; i++ ) {
			var column = this.timesheet_columns[i];
			var column_date_string = Global.strToDate( column.index, this.full_format ).format();
			if ( date_string === column_date_string ) {
				return i;
			}
		}

		return 7;
	},

	setAccumulatedTotalGridPayPeriodHeaders: function() {
		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_accumulated_total_grid]' )[0] );

		var new_tr = $( '<tr class="group-column-tr"  role="rowheader"  >' +
		'</tr>' );

		var new_th = $( '<th class="group-column-th" >' +
		'<span class="group-column-label"></span>' +
		'</th>' );

		var default_th = new_th.clone();

		var week_th = new_th.clone();

		var pay_period_th = new_th.clone();

		week_th.children( 0 ).text( $.i18n._( 'Week' ) );
		pay_period_th.children( 0 ).text( $.i18n._( 'Pay Period' ) );

		new_tr.append( default_th );
		new_tr.append( week_th );
		new_tr.append( pay_period_th );

		//causes a render bug. table cells do not need to have static widths.
		// var ths = table.children( 0 ).find( 'th' );
		// for ( var i = 0; i < ths.length; i++ ) {
		// 	var th_width = $( ths[i] ).width();
		// 	new_tr.children().eq( i ).width( th_width );
		// }
		table.children( 0 ).prepend( new_tr );
	},

	setTimeSheetGridHolidayHeaders: function() {
		var holiday_name_map = {};

		if ( this.full_timesheet_data.holiday_data ) {
			for ( var i = 0; i < this.full_timesheet_data.holiday_data.length; i++ ) {
				var item = this.full_timesheet_data.holiday_data[i];
				var standard_date = Global.strToDate( item.date_stamp ).format( this.full_format );

				var cell = $( 'div[id="jqgh_' + this.ui_id + '_grid_' + standard_date + '"]' );
				if ( cell && !holiday_name_map[item.name] ) {
					cell.html( cell.html() + '<br>' + item.name );
					holiday_name_map[item.name] = true;
				}

			}
		}
	},

	getManualPayPeriodDefaultTrColspan: function() {
		var colspan = 2;
		if (this.show_branch_ui) {
			colspan++;
		}

		if (this.show_department_ui) {
			colspan++;
		}

		if (this.show_job_ui) {
			colspan++;
		}

		if (this.show_job_item_ui) {
			colspan++;
		}

		return colspan;
	},

	setTimeSheetGridPayPeriodHeaders: function() {
		var $this = this;
		var table,
			size_tr;
		if ( this.getPunchMode() === 'punch' ) {
			table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_grid]' )[0] );
			size_tr = $( '<tr class="size-tr" role="row" style="height: 0;" >' + '</tr>' );
		} else {
			table = this.$( '.grid-inside-editor-render' );
			table.find( '.group-column-tr' ).remove();
			size_tr = $( this.$( '.grid-inside-editor-render' ).find( 'tr' )[0] );
		}
		var new_tr = $( '<tr class="group-column-tr"></tr>' );
		var new_th = $( '<th class="group-column-th"><span class="group-column-label"></span></th>' );
		var current_trs = table.find( '.ui-jqgrid-labels' );

		// createSizeColumns was added in 2014. When manual timesheet mode was added in 2016, things were refactored and this should have been pulled out.
		// Leaving it in causes  header row solumns to be out of alignment with the timesheet punch grid by a few pixels.
		if ( this.getPunchMode() === 'punch' ) {
			createSizeColumns();
			table.children( 0 ).prepend( size_tr );
		}

		var default_th;
		if ( this.pay_period_data.length === 0 ) {
			default_th = new_th.clone();
			new_tr.append( default_th );
			if ( this.getPunchMode() === 'manual' ) {
				default_th.attr( 'colspan', this.getManualPayPeriodDefaultTrColspan() );
			}
			createNoPayPeriodColumns( 7 );
			new_tr.insertAfter( size_tr );
			return;
		}
		var current_end_index = 0;
		var last_pay_period_id;
		var column_number = 0;
		var pay_period;
		var map_array = [];
		for ( var y = 0; y < this.column_maps.length; y++ ) {
			var p_key = this.column_maps[y];
			var pay_period_id = this.pay_period_map[p_key];
			if ( !pay_period_id ) {
				pay_period_id = -1;
			}
			map_array.push( {date: p_key, time_stamp: Global.strToDate( p_key ).getTime(), id: pay_period_id} );
		}

		default_th = new_th.clone();
		new_tr.append( default_th );
		if ( this.getPunchMode() === 'manual' ) {
			default_th.attr( 'colspan', this.getManualPayPeriodDefaultTrColspan() );
		}
		for ( var j = 0; j < map_array.length; j++ ) {
			if ( !last_pay_period_id ) {
				last_pay_period_id = map_array[j].id;
				pay_period = getPayPeriod( map_array[j].id );
				column_number = column_number + 1;
			} else if ( last_pay_period_id !== map_array[j].id ) {
				if ( pay_period ) {
					createTh();
				} else {
					createNoPayPeriodColumns( column_number );
				}
				last_pay_period_id = map_array[j].id;
				pay_period = getPayPeriod( map_array[j].id );
				column_number = 1;

			} else {
				column_number = column_number + 1;
			}
			if ( j === map_array.length - 1 && column_number > 0 ) {
				if ( pay_period ) {
					createTh();
				} else {
					createNoPayPeriodColumns( column_number );
				}
			}
		}
		new_tr.insertAfter(size_tr);
		function createTh() {
			var start_date = Global.strToDate( pay_period.start_date ).format();
			var end_date = Global.strToDate( pay_period.end_date ).format();
			var colspan = column_number;
			var pay_period_th = new_th.clone();
			pay_period_th.children( 0 ).text( start_date + ' ' + $.i18n._( 'to' ) + ' ' + end_date );
			pay_period_th.attr( 'colspan', colspan );
			/* jshint ignore:start */
			if ( pay_period.status_id == 12 || pay_period.status_id == 20 ) {
				pay_period_th.css( 'background', '#EC0000' );
			} else if ( pay_period.status_id == 30 ) {
				pay_period_th.css( 'background', '#EED614' );
			}
			/* jshint ignore:end */
			new_tr.append( pay_period_th );
		}

		function getPayPeriod( id ) {
			for ( var key in $this.pay_period_data ) {
				var pay_period = $this.pay_period_data[key];
				if ( pay_period.id === id ) {
					return pay_period;
				}
			}
		}

		function createNoPayPeriodColumns( end_index ) {
			var pay_period_th = new_th.clone();
			pay_period_th.children( 0 ).text( $.i18n._( 'No Pay Period' ) );
			pay_period_th.attr( 'colspan', end_index );
			new_tr.append( pay_period_th );
		}

		function createSizeColumns() {
			var len = current_trs.children().length;
			for ( var i = 0; i < len; i++ ) {
				var th = $( '<td class=""  role="gridcell">' + '</td>' );
				var item = current_trs.children().eq( i );
				th.width( item.width() );
				th.height( 0 );
				size_tr.append( th );
			}

		}

	},

	setPayPeriodHeaderSize: function() {
		var size_tr = $( '.size-tr' );

		if ( size_tr.length === 0 ) {
			return;
		}

		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_grid]' )[0] );
		var current_trs = table.find( '.ui-jqgrid-labels' );
		var len = current_trs.children().length;

		for ( var i = 0; i < len; i++ ) {
			var item = current_trs.children().eq( i );
			size_tr.children().eq( i ).width( parseInt( item[0].style.width ) );
		}

	},

	highLightSelectDay: function() {

		if ( this.highlight_header ) {
			this.highlight_header.removeClass( 'highlight-header' );
		}

		//Error: TypeError: select_date is null in interface/html5/framework/jquery.min.js?v=9.0.1-20151022-081724 line 2 > eval line 3214
		var select_date = Global.strToDate( this.start_date_picker.getValue() );
		!select_date && (select_date = new Date());

		if ( this.getPunchMode() === 'punch' ) {
			select_date = select_date.format( this.full_format );
			this.highlight_header = $( '#' + this.ui_id + '_grid_' + select_date );
		} else {
			select_date = select_date.format( this.full_format );
			this.highlight_header = $( '.manual_grid_day_' + select_date );
		}

		this.highlight_header.addClass( 'highlight-header' );

	},

	/* jshint ignore:start */
	setGridHeight: function( grid_id ) {
		var grid = this.grid_dic[grid_id];
		var len = 0;

		switch ( grid_id ) {
			case 'timesheet_grid':
				len = this.timesheet_data_source.length;
				break;
			case 'accumulated_time_grid':
				len = this.accumulated_time_source.length;
				break;
			case 'branch_grid':
				len = this.branch_source.length;
				break;
			case 'department_grid':
				len = this.department_source.length;
				break;
			case 'job_grid':
				len = this.job_source.length;
				break;
			case 'job_item_grid':
				len = this.job_item_source.length;
				break;
			case 'premium_grid':
				len = this.premium_source.length;
				break;
			case 'absence_grid':
				len = this.absence_source.length;
				break;
			case 'accumulated_total_grid':
				len = this.accumulated_total_grid_source.length;
				if ( this.wage_btn.getValue( true ) ) {
					grid.setGridWidth( 600 );
				} else {
					grid.setGridWidth( 500 );
				}
				break;
			case 'punch_note_grid':
				len = this.punch_note_grid_source.length;
				var grid_width = grid.width();
				var accumulated_grid_width = this.accumulated_total_grid.width();
				var verification_grid_width = this.verification_grid.width();

				if ( this.verification_grid_source.length !== 0 ) {
					grid_width = Math.floor( Global.bodyWidth() - (accumulated_grid_width + verification_grid_width + 25) );
				} else {
					grid_width = Math.floor( Global.bodyWidth() - (25 + accumulated_grid_width) ) ;
				}
				grid_width = Math.abs(grid_width);

				if ( grid_width != grid.width() ) {
					//Debug.Text("Setting punch note grid width to " + grid_width, 'TimesheetViewConroller.js', 'TimesheetViewConroller', 'setGridHeight', 10);
					grid.setGridWidth( grid_width );
					$('td.notes_grid_td_container').css('width', (grid_width+2)+'px');
				}

				break;
			case 'verification_grid':
				len = this.verification_grid_source.length;
				grid.setGridWidth( 400 );
		}

		if ( LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] === true ||
			grid_id === 'timesheet_grid' ||
			grid_id === 'accumulated_total_grid' ||
			grid_id === 'punch_note_grid' ||
			grid_id === 'verification_grid' ) {
			grid.setGridHeight( len * 23 );
		} else {
			grid.setGridHeight( 0 );

		}

		//dont't show scroll bar of grid
		grid.parent().parent().css( 'overflow', 'hidden' );

		//Do not show grid if no data in it
		if ( len === 0 && grid_id !== 'accumulated_total_grid' && grid_id !== 'verification_grid' ) {
			grid.parent().parent().parent().parent().hide();
		} else {
			grid.parent().parent().parent().parent().show();
		}
	},
	/* jshint ignore:end */

	setGridSize: function() {
		var $this = this;
		if ( (!this.grid || !this.grid.is( ':visible' )) && (!this.editor || !this.editor.is( ':visible' )) ) {
			return;
		}

		for ( var key in this.grid_dic ) {
			var grid = this.grid_dic[key];
			if( key != 'punch_note_grid' ) {
				if (Global.bodyWidth() > Global.app_min_width) {
					grid.setGridWidth(Global.bodyWidth() - 14);
				}
			}
			this.setGridHeight(key);

		}
		//force punch note grid to be last, so that it is drawn after the grids it is dependant on to infer its size.
		this.setGridHeight('punch_note_grid');

		if ( this.search_panel.is( ':visible' ) ) {
			this.grid_div.height( $( this.el ).height() - this.search_panel.height() - 72 );
		} else {
			this.grid_div.height( $( this.el ).height() - 72 );
		}

		this.setPayPeriodHeaderSize();

		if ( this.getPunchMode() === 'manual' ) {
			$this.setManualTimeSheetGridSize();
		}
	},

	setManualTimeSheetGridSize: function() {
		var tr = $( this.accumulated_time_grid.find( 'tr:first-child' )[0] );
		var manual_grid_tr = $( this.editor.find( "table" ).find( 'tr:first-child' )[0] );
		var index = 0;
		for ( var i = 0, m = manual_grid_tr.children().length; i < m; i++ ) {
			var td = $( manual_grid_tr.children()[i] );
			if ( !td.is( ':visible' ) ) {
				continue;
			}
			$( td ).css( 'width', $( tr.children()[index] ).css( "width" ) );
			index++;
		}

		this.editor.width( this.accumulated_time_grid.width() );
	},

	onCellFormat: function( cell_value, related_data, row ) {
		var col_model = related_data.colModel;
		var row_id = related_data.rowid;
		var content_div = $( "<div class='punch-content-div'></div>" );
		var punch_info;
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
		var currency = LocalCacheData.getCurrentCurrencySymbol();
		cell_value = Global.decodeCellValue( cell_value );
		if ( related_data.pos === 0 ) {
			if ( row.type === TimeSheetViewController.TOTAL_ROW ) {
				punch_info = $( "<span class='total' style='font-size: 11px'></span>" );
				if ( Global.isSet( cell_value ) ) {
					punch_info.text( cell_value );
				} else {
					punch_info.text( '' );
				}

				return punch_info.get( 0 ).outerHTML;
			} else if ( row.type === TimeSheetViewController.REGULAR_ROW ) {
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

		if ( row.type === TimeSheetViewController.PUNCH_ROW ) {
			punch = row[col_model.name + '_data'];
			related_punch = row[col_model.name + '_related_data'];
			time_span = $( "<span class='punch-time'></span>" );
			break_span = $( "<span class='punch-break'></span>" );

			if ( punch ) {
				exception = punch.exception;

				if ( punch.type_id === 20 ) {

					break_span.text( 'L' );
				} else if ( punch.type_id === 30 ) {

					break_span.text( 'B' );
				}

				if ( punch.note ) {
					cell_value = '*' + cell_value;
				}

				var label_suffix = '';

				if ( punch.latitude && punch.longitude ) {
					label_suffix = 'G';
				}

				if ( punch.has_image ) {
					label_suffix = label_suffix + 'F';

				}

				if ( label_suffix ) {
					cell_value = cell_value + ' ' + label_suffix;
				}

				if ( punch.tainted ) {
					time_span.css( 'color', '#ff0000' );
				}

				content_div.append( break_span );

			} else if ( related_punch ) {
				exception = related_punch.exception;
			}

			if ( Global.isSet( cell_value ) ) {

				time_span.text( cell_value );
			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );

			if ( exception ) {
				len = exception.length;
				text = '';
				for ( i = 0; i < len; i++ ) {
					ex = exception[i];
					ex_span = $( "<span class='punch-exceptions'></span>" );
					ex_span.css( 'color', ex.exception_color );
					ex_span.text( ex.exception_policy_type_id );
					ex_span.attr( 'title', ex.exception_policy_type_id + ': ' + ex.exception_policy_type );
					content_div.prepend( ex_span );
				}
			} else {
				ex_span = $( "<span class='punch-exceptions'></span>" );
				ex_span.text( ' ' );
				content_div.prepend( ex_span );
			}

		} else if ( row.type === TimeSheetViewController.EXCEPTION_ROW ) {
			exception = row[col_model.name + '_exceptions'];

			if ( Global.isSet( exception ) ) {
				len = exception.length;
				text = '';
				for ( i = 0; i < len; i++ ) {
					ex = exception[i];
					ex_span = $( "<span class='punch-exceptions-center'></span>" );
					ex_span.css( 'color', ex.exception_color );
					ex_span.text( ex.exception_policy_type_id );
					ex_span.attr( 'title', ex.exception_policy_type_id + ': ' + ex.exception_policy_type );

					content_div.append( ex_span );
				}
			}

		} else if ( row.type === TimeSheetViewController.REQUEST_ROW ) {
			time_span = $( "<span class='request'></span>" );
			if ( Global.isSet( cell_value ) ) {
				time_span.text( cell_value );
				time_span.attr( 'title', createRequestToolTip( row[col_model.name + '_request'] ) );
			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );

		} else if ( row.type === TimeSheetViewController.TOTAL_ROW ) {

			data = row[col_model.name + '_data'];
			time_span = $( "<span class='total'></span>" );

			if ( Global.isSet( cell_value ) ) {

				if ( data ) {

					if ( data.hasOwnProperty( 'note' ) && data.note ) {
						cell_value = '*' + cell_value;
					}

					if ( time_sheet_view_controller.wage_btn.getValue( true ) && data.hasOwnProperty( 'total_time_amount' ) && data.total_time_amount && data.hasOwnProperty( 'hourly_rate' ) && data.hourly_rate ) {
						time_span = $( "<div class='total--bold time-sheet-view-wage-container'></div>" );
						cell_value = '<span class="time-sheet-view-wage-hour-rate">' + currency +
						data.hourly_rate.toFixed( 2 ) + '/hr @</span>' +
						'<span class="time-sheet-view-wage-value">' + cell_value +
						'</span ><span class="time-sheet-view-wage-amount" >= ' + currency + data.total_time_amount.toFixed( 2 ) +
						'</span>';
					}

					if ( time_sheet_view_controller.getPunchMode() === 'punch' ) {
						if ( data.hasOwnProperty( 'override' ) && data.override === true ) {
							time_span.addClass( 'absence-override' );
						}
					} else {
						if ( !data.override && row.key === 'worked_time' ) {
							time_span.addClass( 'absence-override' );
						}
					}
				}

				time_span.html( cell_value );

			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );

		} else if ( row.type === TimeSheetViewController.REGULAR_ROW ) {

			content_div.addClass( 'top-line' );

			data = row[col_model.name + '_data'];

			time_span = $( "<span></span>" );
			if ( Global.isSet( cell_value ) ) {

				if ( data ) {

					if ( data.hasOwnProperty( 'note' ) && data.note ) {
						cell_value = '*' + cell_value;
					}

					if ( time_sheet_view_controller.wage_btn.getValue( true ) && data.hasOwnProperty( 'total_time_amount' ) && data.total_time_amount && data.hasOwnProperty( 'hourly_rate' ) && data.hourly_rate ) {
						time_span = $( "<div class='time-sheet-view-wage-container'></div>" );
						cell_value = '<span class="time-sheet-view-wage-hour-rate">' + currency +
						data.hourly_rate.toFixed( 2 ) + '/hr @</span>' +
						'<span class="time-sheet-view-wage-value">' + cell_value +
						'</span ><span class="time-sheet-view-wage-amount" >= ' + currency + data.total_time_amount.toFixed( 2 ) +
						'</span>';
					}

					if ( data.hasOwnProperty( 'override' ) && data.override === true ) {
						time_span.addClass( 'absence-override' );
					}
				}

				time_span.html( cell_value );
			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );

		} else if ( row.type === TimeSheetViewController.ABSENCE_ROW ) {

			var absence = row[col_model.name + '_data'];
			time_span = $( "<span></span>" );

			if ( Global.isSet( cell_value ) ) {

				if ( absence ) {

					if ( absence.override === true ) {
						time_span.addClass( 'absence-override' );
					}

					if ( absence.note ) {
						cell_value = '*' + cell_value;
					}
				}

				time_span.text( cell_value );

			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );

		} else if ( row.type === TimeSheetViewController.ACCUMULATED_TIME_ROW ||
			row.type === TimeSheetViewController.PREMIUM_ROW ) {
			data = row[col_model.name + '_data'];
			time_span = $( "<span  style='width: 100%'></span>" );

			if ( Global.isSet( cell_value ) ) {

				if ( data ) {

					if ( data.hasOwnProperty( 'note' ) && data.note ) {
						cell_value = '*' + cell_value;
					}

					if ( time_sheet_view_controller.wage_btn.getValue( true ) && data.hasOwnProperty( 'total_time_amount' ) && data.total_time_amount && data.hasOwnProperty( 'hourly_rate' ) && data.hourly_rate ) {
						time_span = $( "<div class='time-sheet-view-wage-container'></div>" );
						cell_value = '<span class="time-sheet-view-wage-hour-rate">' + currency +
						data.hourly_rate.toFixed( 2 ) + '/hr @</span>' +
						'<span class="time-sheet-view-wage-value">' + cell_value +
						'</span ><span class="time-sheet-view-wage-amount" >= ' + currency + data.total_time_amount.toFixed( 2 ) +
						'</span>';
					}
					if ( data.hasOwnProperty( 'override' ) && data.override === true ) {
						time_span.addClass( 'absence-override' );
					}
				}

				time_span.html( cell_value );

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

		function createRequestToolTip( value ) {
			var label;
			if ( _.isArray( value ) ) {
				label = calDAndA( value );
			} else {
				// Already translate when back from API
				label = value.status;
			}
			function calDAndA( array ) {
				var len = array.length;
				var a = 0;
				var d = 0;
				var p = 0;
				var label = '';
				for ( var i = 0; i < len; i++ ) {
					var item = array[i];
					if ( item.status_id === 50 ) {
						a = a + 1;
					} else if ( item.status_id === 55 ) {
						d = d + 1;
					} else if ( item.status_id === 30 ) {
						p = p + 1;
					}
				}
				if ( a > 0 ) {
					label += ' ' + $.i18n._( 'Authorized' ) + ': ' + a;
				}
				if ( p > 0 ) {
					label += ' ' + $.i18n._( 'Pending' ) + ': ' + p;
				}
				if ( d > 0 ) {
					label += ' ' + $.i18n._( 'Declined' ) + ': ' + d;
				}
				return label;
			}

			return label;
		}

		return content_div.get( 0 ).outerHTML;
	},

	onSelectRow: function( grid_id, row_id, target ) {
		var $this = this;
		var row_tr = $( target ).find( '#' + row_id );
		row_tr.removeClass( 'ui-state-highlight' ).attr( 'aria-selected', true );
		var cells_array = [];
		var len = 0;
		if ( grid_id === 'timesheet_grid' ) {
			cells_array = $this.select_cells_Array;
			len = $this.select_cells_Array.length;
			$this.absence_select_cells_Array = [];
		} else if ( grid_id === 'absence_grid' ) {
			cells_array = $this.absence_select_cells_Array;
			len = $this.absence_select_cells_Array.length;
			$this.select_cells_Array = [];
		} else if ( grid_id === 'accumulated_grid' ) {
			cells_array = $this.accumulated_time_cells_array;
			len = $this.accumulated_time_cells_array.length;
		} else if ( grid_id === 'premium_grid' ) {
			cells_array = $this.premium_cells_array;
			len = $this.premium_cells_array.length;
		}
		this.select_punches_array = [];
		/* jshint ignore:start */
		for ( var i = 0; i < len; i++ ) {
			var info = cells_array[i];
			row_tr = $( target ).find( '#' + info.row_id );
			var cell_td = $( row_tr.find( 'td' )[info.cell_index] );
			cell_td.addClass( 'ui-state-highlight' ).attr( 'aria-selected', true );

			if ( info.punch && info.punch.id ) {

				if ( Global.isSet( info.punch.time_stamp ) ) { //date + time number
					var date = Global.strToDate( info.punch.punch_date ).format( 'MM-DD-YYYY' );
					var date_time = date + ' ' + info.punch.punch_time;
					info.punch.time_stamp_num = Global.strToDateTime( date_time ).getTime();
				} else {
					info.punch.time_stamp_num = info.time_stamp_num; //Uer time_stamp_num from cell select setting, a date number
				}
				this.select_punches_array.push( info.punch );
				this.select_punches_array.sort( function( a, b ) {
					return Global.compare( a, b, 'time_stamp_num' );
				} );
			}
		}
		/* jshint ignore:end */
		this.setDefaultMenu();
	},

	unsetSelectedCells: function( grid_id ) {
		if( grid_id == 'accumulated_grid' ) {
			grid_id = 'accumulated_time_grid';
		}

		if ( this.last_clicked_grid_id && grid_id != this.last_clicked_grid_id ) {
			//Use window setTimeout to make this code asyncchronous for speed, it's the fastest way.
			//web worker : 850ms
			//inline code : 700ms
			//setTimeout: 400ms
			window.setTimeout( function ( t, n ) {
				t.grid_dic[n].trigger('reloadGrid');
			}, 0, this, this.last_clicked_grid_id);
		}

		this.setDefaultMenu();
		this.last_clicked_grid_id = grid_id;
	},

	onCellSelect: function( grid_id, row_id, cell_index, cell_val, target, e ) {
		if ( cell_index < 0 ) {

			this.unsetSelectedCells(grid_id);
			return;
		}

		// BUG#2149 row_id is stored within jqgrid as a string.
		// We need an int here for numeric comparisons to figure out the range of selected cells.
		row_id = parseInt(row_id);
		cell_index = parseInt(cell_index);

		var $this = this;
		var len = 0;
		var row;
		var colModel;
		var data_field;
		var punch;
		var related_punch;
		var cells_array = [];
		$this.absence_model = false;
		var date;
		if ( grid_id === 'timesheet_grid' ) {

			cells_array = $this.select_cells_Array;
			len = $this.select_cells_Array.length;
			row = $this.timesheet_data_source[row_id - 1];
			colModel = $this.grid.getGridParam( 'colModel' );
			data_field = colModel[cell_index].name;

			if ( row.type === TimeSheetViewController.REQUEST_ROW ) {
				var filter = {filter_data: {}};
				filter.filter_data.user_id = this.getSelectEmployee();
				filter.filter_data.start_date = $this.full_timesheet_data.timesheet_dates.start_display_date;
				filter.filter_data.end_date = $this.full_timesheet_data.timesheet_dates.end_display_date;
				filter.filter_data.id = [];

				$this.unsetSelectedCells(grid_id);
				var pending_requests = 0;
				var total_requests = 0;
				if ( Global.isArray(row[data_field+'_request']) ) {
					for ( var n in row[data_field + '_request'] ) {
						var obj = row[data_field + '_request'][n];
						filter.filter_data.id.push( obj.id );
						if ( obj.status == 'PENDING' ) {
							pending_requests += 1;
						}
						total_requests += 1;
					}
				} else if ( row[data_field + '_request'] ) {
					//is object;
					filter.filter_data.id.push( row[data_field + '_request'].id );
					if ( row[data_field + '_request'].status == 'PENDING' ) {
						pending_requests = 1;
					}
					total_requests = 1;
				} else {
					return;
				}

				Global.addViewTab( this.viewId, 'TimeSheet', window.location.href );

				if ( total_requests > 0 ) {
					if (this.getSelectEmployee() != LocalCacheData.getLoginUser().id && pending_requests > 0) {
						if (this.ownerOrChildPermissionValidate('request', 'view_child', filter.filter_data.id)) {
							IndexViewController.goToView('RequestAuthorization', filter);
						}
					} else {
						if (this.viewPermissionValidate('request', filter.filter_data.id)) {
							IndexViewController.goToView('Request', filter);
						}
					}
				}
				return;
			}

			if ( row ) {
				punch = row[data_field + '_data'];
			} else {
				punch = null;
			}

			related_punch = row[data_field + '_related_data'];

			date = Global.strToDate( data_field, this.full_format );

			$this.setTimesheetGridDragAble();

		} else if ( grid_id === 'absence_grid' ) {
			cells_array = $this.absence_select_cells_Array;

			len = $this.absence_select_cells_Array.length;

			row = $this.absence_source[row_id - 1];

			colModel = $this.absence_grid.getGridParam( 'colModel' );

			data_field = colModel[cell_index].name;

			// Error: Uncaught TypeError: Cannot read property 'punch_info_data' of undefined in interface/html5/#!m=TimeSheet&date=20151220&user_id=null&show_wage=0 line 3761
			if ( row ) {
				punch = row[data_field + '_data'];
			} else {
				punch = null;
			}

			date = Global.strToDate( data_field, this.full_format );

			$this.absence_model = true;

		} else if ( grid_id === 'accumulated_grid' ) {

			cells_array = $this.accumulated_time_cells_array;

			len = $this.accumulated_time_cells_array.length;

			row = $this.accumulated_time_source[row_id - 1];

			colModel = $this.accumulated_time_grid.getGridParam( 'colModel' );

			data_field = colModel[cell_index].name;

			if ( row ) {
				punch = row[data_field + '_data'];
			} else {
				punch = null;
			}

			date = Global.strToDate( data_field, this.full_format );
		} else if ( grid_id === 'premium_grid' ) {

			cells_array = $this.premium_cells_array;

			len = $this.premium_cells_array.length;

			row = $this.premium_source[row_id - 1];

			colModel = $this.premium_grid.getGridParam( 'colModel' );

			data_field = colModel[cell_index].name;

			if ( row ) {
				punch = row[data_field + '_data'];
			} else {
				punch = null;
			}

			date = Global.strToDate( data_field, this.full_format );

		}


		if ( date == null ) {
			$this.unsetSelectedCells(grid_id);
			return false;
		}
		var info;
		var row_tr;
		var cell_td;
		//Clean all select cells first
		for ( var i = 0; i < len; i++ ) {
			info = cells_array[i];
			row_tr = $( target ).find( '#' + info.row_id );
			cell_td = $( row_tr.find( 'td' )[info.cell_index] );
			cell_td.removeClass( 'ui-state-highlight' ).attr( 'aria-selected', false );
		}

		var date_str;
		var time_stamp_num;
		// Add multiple selectiend_display_date if click cell and hold ctrl or command
		if ( e.ctrlKey || e.metaKey ) {
			var found = false;
			for ( i = 0; i < len; i++ ) {
				info = cells_array[i];
				if ( row_id === info.row_id && cell_index === info.cell_index ) {
					cells_array.splice( i, 1 );
					found = true;
					break;
				}
			}

			date_str = date.format();
			time_stamp_num = date.getTime();

			if ( !found ) {

				if ( grid_id === 'timesheet_grid' ) {

					cells_array.push( {
						row_id: row_id,
						cell_index: cell_index,
						cell_val: cell_val,
						punch: punch,
						related_punch: related_punch,
						date: date_str,
						time_stamp_num: time_stamp_num
					} );

					$this.select_cells_Array = cells_array;
					$this.select_cells_Array.sort( Global.m_sort_by( ['time_stamp_num', 'row_id'] ) );

				} else if ( grid_id === 'absence_grid' ) {
					cells_array.push( {
						row_id: row_id,
						cell_index: cell_index,
						cell_val: cell_val,
						punch: punch,
						date: date_str,
						time_stamp_num: time_stamp_num,
						src_object_id: row.punch_info_id
					} );
					$this.absence_select_cells_Array = cells_array;
					$this.absence_select_cells_Array.sort( Global.m_sort_by( ['time_stamp_num', 'row_id'] ) );
				} else if ( grid_id === 'premium_grid' ) {
					cells_array = [
						{
							row_id: row_id,
							cell_index: cell_index,
							cell_val: cell_val,
							date: date_str,
							time_stamp_num: time_stamp_num
						}
					];
					$this.premium_cells_array = cells_array;
				}

			}
		} else if ( e.shiftKey ) {

			var start_row_index = row_id;
			var start_cell_index = cell_index;

			var end_row_index = row_id;
			var end_cell_index = cell_index;

			for ( i = 0; i < len; i++ ) {
				info = cells_array[i];

				if ( info.row_id < start_row_index ) {
					start_row_index = info.row_id;
				} else if ( info.row_id > end_row_index ) {
					end_row_index = info.row_id;
				}

				if ( info.cell_index < start_cell_index ) {
					start_cell_index = info.cell_index;
				} else if ( info.cell_index > end_cell_index ) {
					end_cell_index = info.cell_index;
				}
			}

			//If the click is inside the existing selection, truncate the existing selection to the click.
			//Check in ScheduleViewController.js for related change
			//Make sure to check for cells_array and cells_array.length before the other checks or when the user clicks into another grid while holding shift, it throws the following error:
			//Cannot read property 'cell_index' of undefined
			if ( cells_array && cells_array.length > 0 && cells_array[cells_array.length - 1].cell_index && cells_array[0].cell_index && cells_array[cells_array.length - 1].cell_index >= cell_index && cells_array[0].cell_index <= cell_index &&  cells_array[cells_array.length - 1].row_id >= row_id && cells_array[0].row_id <= row_id ) {
				end_row_index = row_id;
				end_cell_index = cell_index;
			}

			cells_array = [];
			for ( var i = start_row_index; i <= end_row_index; i++ ) {
				var r_index = i;
				for ( var j = start_cell_index; j <= end_cell_index; j++ ) {
					var c_index = j;

					row_tr = $( target ).find( '#' + r_index );

					cell_td = $( row_tr.find( 'td' )[c_index] );

					cell_val = cell_td[0].outerHTML;

					if ( grid_id === 'timesheet_grid' ) {

						row = $this.timesheet_data_source[r_index - 1];

						colModel = $this.grid.getGridParam( 'colModel' );

						data_field = colModel[c_index].name;

						punch = row[data_field + '_data'];

						related_punch = row[data_field + '_related_data'];

						date = Global.strToDate( data_field, this.full_format );

						date_str = date.format();
						time_stamp_num = date.getTime();

						cells_array.push( {
							row_id: r_index, //see bug #2149
							//row_id: r_index.toString(),
							cell_index: c_index,
							cell_val: cell_val,
							punch: punch,
							related_punch: related_punch,
							date: date_str,
							time_stamp_num: time_stamp_num
						} );

					} else if ( grid_id === 'absence_grid' ) {

						row = $this.absence_source[row_id - 1];

						colModel = $this.absence_grid.getGridParam( 'colModel' );

						data_field = colModel[c_index].name;

						punch = row[data_field + '_data'];

						date = Global.strToDate( data_field, this.full_format );

						date_str = date.format();
						time_stamp_num = date.getTime();

						cells_array.push( {
							row_id: r_index, //see bug #2149
							//row_id: r_index.toString(),
							cell_index: c_index,
							cell_val: cell_val,
							punch: punch,
							date: date_str,
							time_stamp_num: time_stamp_num,
							src_object_id: row.punch_info_id
						} );
					} else if ( grid_id === 'accumulated_grid' ) {
						cells_array = [
							{
								row_id: row_id,
								cell_index: cell_index,
								cell_val: cell_val,
								date: date_str,
								time_stamp_num: time_stamp_num
							}
						];
						$this.accumulated_time_cells_array = cells_array;
					} else if ( grid_id === 'premium_grid' ) {
						cells_array = [
							{
								row_id: row_id,
								cell_index: cell_index,
								cell_val: cell_val,
								date: date_str,
								time_stamp_num: time_stamp_num
							}
						];
						$this.premium_cells_array = cells_array;
					}

				}
			}

			if ( grid_id === 'timesheet_grid' ) {
				$this.select_cells_Array = cells_array;
				$this.select_cells_Array.sort( Global.m_sort_by( ['time_stamp_num', 'row_id'] ) );
				//$this.select_cells_Array = _.sortBy(( _.sortBy($this.select_cells_Array, 'time_stamp_num')), 'row_id');
				//this.select_cells_Array.sort( function( a, b ) {
				//
				//	return Global.compare( a, b, 'time_stamp_num' );
				//
				//} );
			} else if ( grid_id === 'absence_grid' ) {
				$this.absence_select_cells_Array = cells_array;
				$this.absence_select_cells_Array.sort( Global.m_sort_by( ['time_stamp_num', 'row_id'] ) );
				//this.absence_select_cells_Array.sort( function( a, b ) {
				//
				//	return Global.compare( a, b, 'time_stamp_num' );
				//
				//} );
			} else if ( grid_id === 'accumulated_grid' ) {
				$this.accumulated_time_cells_array = cells_array;
				$this.accumulated_time_cells_array.sort( Global.m_sort_by( ['time_stamp_num', 'row_id'] ) );
				//this.accumulated_time_cells_array.sort( function( a, b ) {
				//
				//	return Global.compare( a, b, 'time_stamp_num' );
				//
				//} );
			} else if ( grid_id === 'premium_grid' ) {
				$this.premium_cells_array = cells_array;
				$this.premium_cells_array.sort( Global.m_sort_by( ['time_stamp_num', 'row_id'] ) );
				//this.premium_cells_array.sort( function( a, b ) {
				//
				//	return Global.compare( a, b, 'time_stamp_num' );
				//
				//} );
			}

		} else {
			date_str = date ? date.format() : '';
			time_stamp_num = date ? date.getTime() : 0;
			if ( grid_id === 'timesheet_grid' ) {

				cells_array = [
					{
						row_id: row_id,
						cell_index: cell_index,
						cell_val: cell_val,
						punch: punch,
						related_punch: related_punch,
						date: date_str,
						time_stamp_num: time_stamp_num
					}
				];

				$this.select_cells_Array = cells_array;

			} else if ( grid_id === 'absence_grid' ) {

				cells_array = [
					{
						row_id: row_id,
						cell_index: cell_index,
						cell_val: cell_val,
						punch: punch,
						date: date_str,
						time_stamp_num: time_stamp_num,
						src_object_id: row.punch_info_id
					}
				];
				$this.absence_select_cells_Array = cells_array;
			} else if ( grid_id === 'accumulated_grid' ) {

				cells_array = [
					{
						row_id: row_id,
						cell_index: cell_index,
						cell_val: cell_val,
						date: date_str,
						time_stamp_num: time_stamp_num
					}
				];
				$this.accumulated_time_cells_array = cells_array;
			} else if ( grid_id === 'premium_grid' ) {

				cells_array = [
					{
						row_id: row_id,
						cell_index: cell_index,
						cell_val: cell_val,
						date: date_str,
						time_stamp_num: time_stamp_num
					}
				];
				$this.premium_cells_array = cells_array;
			}

			if ( date && date.getYear() > 0 ) {
				this.setDatePickerValue( date.format( Global.getLoginUserDateFormat() ) );
				this.highLightSelectDay();
				this.reLoadSubGridsSource();
			}
			$this.unsetSelectedCells(grid_id);

		}

	},

	get_selected_punch_array: function() {

	},

	buildTimeSheetRequests: function() {
		var request_array = this.full_timesheet_data.request_data;
		var len = request_array.length;
		var request_row_index = null;

		for ( var i = 0; i < len; i++ ) {
			var request = request_array[i];

			var date_string = Global.strToDate( request.date_stamp ).format( this.full_format );

			var row;
			//Build Exception row at bottom
			if ( !request_row_index ) {
				row = {};
				row.punch_info = $.i18n._( 'Requests' );
				row.user_id = request.user_id;
				row[date_string] = request.status;
				row[date_string + '_request'] = request;

				row.type = TimeSheetViewController.REQUEST_ROW;
				this.timesheet_data_source.push( row );
				request_row_index = this.timesheet_data_source.length - 1;
			} else {
				row = this.timesheet_data_source[request_row_index];
				if ( !Global.isSet( row[date_string + '_request'] ) ) {
					row[date_string] = request.status;
					row[date_string + '_request'] = request;
				} else {

					if ( $.type( row[date_string + '_request'] ) === 'array' ) {
						row[date_string + '_request'].push( request );

					} else {
						row[date_string + '_request'] = [row[date_string + '_request']];
						row[date_string + '_request'].push( request );
					}

					row[date_string] = calDAndA( row[date_string + '_request'] );
				}
			}

		}

		function calDAndA( array ) {
			var len = array.length;
			var a = 0;
			var d = 0;
			var p = 0;
			var label = '';
			for ( var i = 0; i < len; i++ ) {
				var item = array[i];
				if ( item.status_id === 50 ) {
					a = a + 1;
				} else if ( item.status_id === 55 ) {
					d = d + 1;
				} else if ( item.status_id === 30 ) {
					p = p + 1;
				}
			}
			if ( a > 0 ) {
				label += ' A: ' + a;
			}
			if ( p > 0 ) {
				label += ' P: ' + p;
			}
			if ( d > 0 ) {
				label += ' D: ' + d;
			}
			return label;
		}
	},

	buildTimeSheetExceptions: function() {
		var exception_array = this.full_timesheet_data.exception_data;

		var len = exception_array.length;
		var timesheet_data_source_len = this.timesheet_data_source.length;
		var exception_row_index = null;
		for ( var i = 0; i < len; i++ ) {
			var ex = exception_array[i];
			var date_string = Global.strToDate( ex.date_stamp ).format( this.full_format );
			var row;
			//Build Exception row at bottom
			if ( !exception_row_index ) {
				row = {};
				row.punch_info = $.i18n._( 'Exceptions' );
				row.user_id = ex.user_id;
				row[date_string] = '';
				row[date_string + '_exceptions'] = [ex];

				row.type = TimeSheetViewController.EXCEPTION_ROW;
				this.timesheet_data_source.push( row );
				exception_row_index = this.timesheet_data_source.length - 1;
			} else {
				row = this.timesheet_data_source[exception_row_index];
				if ( !Global.isSet( row[date_string + '_exceptions'] ) ) {
					row[date_string + '_exceptions'] = [ex];
				} else {
					row[date_string + '_exceptions'].push( ex );
				}
			}

			var punch;
			var j;
			if ( !Global.isFalseOrNull( ex.punch_id ) ) {

				for ( j = 0; j < timesheet_data_source_len; j++ ) {
					row = this.timesheet_data_source[j];

					if ( !row[date_string] ) {
						continue;
					}

					if ( row[date_string + '_data'] ) {
						punch = row[date_string + '_data'];
					} else if ( row[date_string + '_related__data'] ) {
						punch = row[date_string + '_related_data'];
					}

					if ( punch.id === ex.punch_id && !punch.exception ) {
						punch.exception = [ex];
					}

				}

			} else if ( !Global.isFalseOrNull( ex.punch_control_id ) ) {
				for ( j = 0; j < timesheet_data_source_len; j++ ) {
					row = this.timesheet_data_source[j];

					if ( !row[date_string] ) {
						continue;
					}

					if ( row[date_string + '_data'] ) {
						punch = row[date_string + '_data'];
					} else if ( row[date_string + '_related__data'] ) {
						punch = row[date_string + '_related_data'];
					}

					if ( punch.punch_control_id === ex.punch_control_id && !punch.exception ) {
						punch.exception = [ex];
					}

				}
			}
		}
	},

	// Make sure Totle_time go to last item
	sortAccumulatedTotalData: function() {

		var sort_fields = ['order', 'punch_info'];
		this.accumulated_total_grid_source.sort( Global.m_sort_by( sort_fields ) );

	},

	// Make sure total time go to last item
	sortAccumulatedTimeData: function() {

		var sort_fields = ['order', 'punch_info'];
		this.accumulated_time_source.sort( Global.m_sort_by( sort_fields ) );

	},

	reLoadSubGridsSource: function( force ) {
		// Error: Uncaught TypeError: Cannot read property 'pay_period_id' of undefined in interface/html5/#!m=TimeSheet&date=20151214&user_id=null&show_wage=0 line 4290
		if ( !this.full_timesheet_data || !this.full_timesheet_data.timesheet_verify_data ) {
			return;
		}

		if ( !force ) {
			if ( this.full_timesheet_data.timesheet_verify_data.pay_period_id === this.pay_period_map[this.getSelectDate()] ||
				( !Global.isSet( this.full_timesheet_data.timesheet_verify_data.pay_period_id ) && !this.pay_period_map[this.getSelectDate()])
			) {
				return;
			}
		}

		this.accumulated_time_source_map = {};
		this.branch_source_map = {};
		this.department_source_map = {};
		this.job_source_map = {};
		this.job_item_source_map = {};
		this.premium_source_map = {};
		this.accumulated_total_grid_source_map = {};
		this.accumulated_time_source = [];
		this.branch_source = [];
		this.department_source = [];
		this.job_source = [];
		this.job_item_source = [];
		this.premium_source = [];
		this.accumulated_total_grid_source = [];
		this.verification_grid_source = [];
		var $this = this;
		var start_date_string = this.start_date_picker.getValue();
		var user_id = this.getSelectEmployee();
		this.api_timesheet.getTimeSheetTotalData( user_id, start_date_string, {
			onResult: function( result ) {
				$this.onReloadSubGridResult( result );

			}
		} );
	},

	onReloadSubGridResult: function( result ) {
		var $this = this;
		result = result.getResult();
		$this.full_timesheet_data.accumulated_user_date_total_data = result.accumulated_user_date_total_data;
		$this.full_timesheet_data.meal_and_break_total_data = result.meal_and_break_total_data;
		$this.full_timesheet_data.pay_period_accumulated_user_date_total_data = result.pay_period_accumulated_user_date_total_data;
		$this.full_timesheet_data.timesheet_verify_data = result.timesheet_verify_data;
		$this.full_timesheet_data.pay_period_data = result.pay_period_data;
		$this.timesheet_verify_data = $this.full_timesheet_data.timesheet_verify_data;

		$this.buildSubGridsSource();

		$this.buildAccumulatedTotalGrid();
		$this.buildVerificationGridSource();

		$this.accumulated_time_grid.clearGridData();
		$this.accumulated_time_grid.setGridParam( {data: $this.accumulated_time_source} );
		$this.accumulated_time_grid.trigger( 'reloadGrid' );

		$this.branch_grid.clearGridData();
		$this.branch_grid.setGridParam( {data: $this.branch_source} );
		$this.branch_grid.trigger( 'reloadGrid' );

		$this.department_grid.clearGridData();
		$this.department_grid.setGridParam( {data: $this.department_source} );
		$this.department_grid.trigger( 'reloadGrid' );

		$this.job_grid.clearGridData();
		$this.job_grid.setGridParam( {data: $this.job_source} );
		$this.job_grid.trigger( 'reloadGrid' );

		$this.job_item_grid.clearGridData();
		$this.job_item_grid.setGridParam( {data: $this.job_item_source} );
		$this.job_item_grid.trigger( 'reloadGrid' );

		$this.premium_grid.clearGridData();
		$this.premium_grid.setGridParam( {data: $this.premium_source} );
		$this.premium_grid.trigger( 'reloadGrid' );

		if ( $this.accumulated_total_grid_source.length === 0 ) {
			$this.accumulated_total_grid_source.push();
		}

		$this.accumulated_total_grid.clearGridData();
		$this.accumulated_total_grid.setGridParam( {data: $this.accumulated_total_grid_source} );
		$this.accumulated_total_grid.trigger( 'reloadGrid' );

		$this.punch_note_grid.clearGridData();
		$this.punch_note_grid.setGridParam( {data: $this.punch_note_grid_source} );
		$this.punch_note_grid.trigger( 'reloadGrid' );

		$this.verification_grid.clearGridData();
		$this.verification_grid.setGridParam( {data: $this.verification_grid_source} );
		$this.verification_grid.trigger( 'reloadGrid' );

		$this.setGridSize();

	},

	setDefaultMenuEditIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 && this.editOwnerOrChildPermissionValidate( pId ) && (this.getPunchMode() === 'punch' || pId === 'absence') ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuDeleteIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.deletePermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length >= 1 && this.deleteOwnerOrChildPermissionValidate( pId ) && (this.getPunchMode() === 'punch' || pId === 'absence') ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuViewIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 && this.viewOwnerOrChildPermissionValidate() && (this.getPunchMode() === 'punch' || pId === 'absence') ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuAddIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.addPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( this.getPunchMode() === 'manual' && pId !== 'absence' ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuSaveIcon: function( context_btn, grid_selected_length, pId ) {
		if ( (!this.addPermissionValidate( pId ) && !this.editPermissionValidate( pId )) ) {
			context_btn.addClass( 'invisible-image' );
		}
		if ( this.getPunchMode() === 'manual' ) {
			if ( (!this.addPermissionValidate( pId ) && !this.editPermissionValidate( pId )) ) {
				context_btn.addClass( 'invisible-image' );
			}
			if ( this.is_saving_manual_grid ) {
				context_btn.addClass( 'disable-image' );
			}
		} else {
			context_btn.addClass( 'disable-image' );
		}

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

	buildSubGridsSource: function() {

		var accumulated_user_date_total_data = this.full_timesheet_data.accumulated_user_date_total_data;
		var meal_and_break_total_data = this.full_timesheet_data.meal_and_break_total_data;
		var pay_period_accumulated_user_date_total_data = this.full_timesheet_data.pay_period_accumulated_user_date_total_data;

		this.accmulated_order_map = {};

		// Save the order, will do sort after all data prepared.
		if ( accumulated_user_date_total_data.total ) {
			this.buildAccmulatedOrderMap( accumulated_user_date_total_data.total );
		}

		if ( pay_period_accumulated_user_date_total_data ) {
			this.buildAccmulatedOrderMap( pay_period_accumulated_user_date_total_data );
		}

		//Build Accumulated Total Grid Pay_period column data
		var accumulated_time = pay_period_accumulated_user_date_total_data.accumulated_time;
		var premium_time = pay_period_accumulated_user_date_total_data.premium_time;
		var absence_time = pay_period_accumulated_user_date_total_data.absence_time_taken;

		if ( Global.isSet( accumulated_time ) ) {
			this.buildSubGridsData( accumulated_time, 'pay_period', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'accumulated_time' );
		} else {
			accumulated_time = {total: {label: $.i18n._( 'Total Time' ), total_time: '0'}};
			this.buildSubGridsData( accumulated_time, 'pay_period', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'accumulated_time' );
		}

		if ( Global.isSet( premium_time ) ) {
			this.buildSubGridsData( premium_time, 'pay_period', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'premium_time' );
		}

		if ( Global.isSet( absence_time ) ) {
			this.buildSubGridsData( absence_time, 'pay_period', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'absence_time' );
		}

		//Build Accumulated Total Grid Pay_period column data end

		var column_len = this.timesheet_columns.length;
		accumulated_time = {total: {label: $.i18n._( 'Total Time' ), total_time: '0'}};
		var date_string;
		var date;

		var start = 1;
		//#2160 - subgrid column offset must be dynamic to parallel existence of dynamic columns (job, task, branch, department)
		if ( this.getPunchMode() != 'punch' ) {
			var start = this.getManualPayPeriodDefaultTrColspan();
		}

		for ( var i = start; i < column_len; i++ ) {
			date_string = this.timesheet_columns[i].name;
			if ( date_string.indexOf( 'empty_cell' ) >= 0 ) continue;
			this.buildSubGridsData( accumulated_time, date_string, this.accumulated_time_source_map, this.accumulated_time_source, 'accumulated_time' );
		}
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

			//Build Accumulated Total Grid week column data end
			//Build all sub grids data
			//Error: Uncaught TypeError: Cannot read property 'format' of null in interface/html5/#!m=TimeSheet&date=20151117&user_id=35367&show_wage=0 line 4478
			date = Global.strToDate( key );
			if ( !date ) continue;
			date_string = date.format( this.full_format );

			accumulated_time = accumulated_user_date_total_data[key].accumulated_time;
			var branch_time = accumulated_user_date_total_data[key].branch_time;
			var department_time = accumulated_user_date_total_data[key].department_time;
			var job_time = accumulated_user_date_total_data[key].job_time;
			var job_item_time = accumulated_user_date_total_data[key].job_item_time;
			premium_time = accumulated_user_date_total_data[key].premium_time;

			if ( Global.isSet( accumulated_time ) ) {
				this.buildSubGridsData( accumulated_time, date_string, this.accumulated_time_source_map, this.accumulated_time_source, 'accumulated_time' );
			}

			if ( Global.isSet( branch_time ) ) {

				this.buildSubGridsData( branch_time, date_string, this.branch_source_map, this.branch_source, 'branch_time' );
			}

			if ( Global.isSet( department_time ) ) {

				this.buildSubGridsData( department_time, date_string, this.department_source_map, this.department_source, 'department_time' );
			}

			if ( Global.isSet( job_time ) ) {

				this.buildSubGridsData( job_time, date_string, this.job_source_map, this.job_source, 'job_time' );
			}

			if ( Global.isSet( job_item_time ) ) {

				this.buildSubGridsData( job_item_time, date_string, this.job_item_source_map, this.job_item_source, 'job_item_time' );
			}

			if ( Global.isSet( premium_time ) ) {

				this.buildSubGridsData( premium_time, date_string, this.premium_source_map, this.premium_source, 'premium_time' );
			}

		}

		this.sortAccumulatedTotalData();
		this.sortAccumulatedTimeData();

		if ( Global.isSet( meal_and_break_total_data ) ) {

			for ( key  in meal_and_break_total_data ) {
				// Error: Uncaught TypeError: Cannot read property 'format' of null in interface/html5/#!m=TimeSheet&date=20151119&user_id=55338&show_wage=0 line 4527
				date = Global.strToDate( key );
				if ( !date ) continue;
				date_string = date.format( this.full_format );

				this.buildBreakAndLunchData( meal_and_break_total_data[key], date_string );

			}

		}
	},

	buildBreakAndLunchData: function( array, date_string ) {
		var row;
		for ( var key in array ) {
			if ( !this.accumulated_time_source_map[key] ) {
				row = {};
				row.punch_info = array[key].break_name;
				array[key].key = key;
				row[date_string] = Global.secondToHHMMSS( array[key].total_time ) + ' (' + array[key].total_breaks + ')';
				row[date_string + '_data'] = array[key];
				this.timesheet_data_source.push( row );
				this.accumulated_time_source_map[key] = row;
			} else {
				row = this.accumulated_time_source_map[key];
				if ( !row[date_string] ) {
					array[key].key = key;
					row[date_string] = Global.secondToHHMMSS( array[key].total_time ) + ' (' + array[key].total_breaks + ')';

					row[date_string + '_data'] = array[key];
				}

			}
		}

	},

	addCellCount: function( key ) {
		switch ( key ) {
			case 'branch_time':
				this.branch_cell_count = this.branch_cell_count + 1;
				break;
			case 'department_time':
				this.department_cell_count = this.department_cell_count + 1;
				break;

			case 'premium_time':
				this.premium_cell_count = this.premium_cell_count + 1;
				break;
			case 'job_time':
				this.job_cell_count = this.job_cell_count + 1;
				break;
			case 'job_item_time':
				this.task_cell_count = this.task_cell_count + 1;
				break;

		}
	},

	markRegularRow: function( source ) {

		var len = source.length;

		for ( var i = 0; i < source.length; i++ ) {
			var row = source[i];

			if ( row.key && row.key.indexOf( 'regular_time' ) === 0 ) {
				row.type = TimeSheetViewController.REGULAR_ROW;
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

					if ( key === 'total' || key === 'worked_time' ) {
						row.type = TimeSheetViewController.TOTAL_ROW;
					} else {
						row.type = TimeSheetViewController.ACCUMULATED_TIME_ROW;
					}

					if ( array[key].override ) {
						row.is_override_row = true;
					}

				} else if ( parent_key === 'premium_time' ) {
					row.type = TimeSheetViewController.PREMIUM_ROW;
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

			this.addCellCount( parent_key )
		}

	},

	timeSheetVerifyPermissionValidate: function() {
		if ( PermissionManager.validate( 'punch', 'verify_time_sheet' ) &&
			this.timesheet_verify_data.hasOwnProperty( 'pay_period_verify_type_id' ) &&
			this.timesheet_verify_data.pay_period_verify_type_id !== 10 ) {
			return true;
		}

		return false;
	},

	buildVerificationGridSource: function() {

		var $this = this;
		var verify_action_bar = $( this.el ).find( '.verification-action-bar' );
		var verify_grid_div = $( this.el ).find( '.verification-grid-div' );
		var verify_btn = $( this.el ).find( '.verify-button' );
		var verify_title = $( this.el ).find( '.verification-grid-title' );
		var verify_des = $( this.el ).find( '.verify-description' );

		if ( this.timeSheetVerifyPermissionValidate() &&
			Global.isSet( this.timesheet_verify_data.pay_period_id ) &&
			Global.isSet( this.timesheet_verify_data.pay_period_verify_type_id ) &&
			this.timesheet_verify_data.pay_period_verify_type_id !== '10' ) {

			if ( !this.timesheet_verify_data.display_verify_button ) {
				verify_btn.css( 'display', 'none' );
				verify_title.css( 'display', 'none' );
			} else {
				verify_btn.css( 'display', 'inline-block' );
				verify_title.css( 'display', 'block' );
			}

			verify_grid_div.css( 'display', 'block' );
			verify_des.text( this.timesheet_verify_data.verification_status_display );

			if ( this.timesheet_verify_data.verification_box_color ) {
				verify_action_bar.css( 'background', this.timesheet_verify_data.verification_box_color );
			} else {
				verify_action_bar.css( 'background', '#ffffff' );
			}

			verify_btn.unbind( 'click' ).bind( 'click', function() {
				TAlertManager.showConfirmAlert( $this.timesheet_verify_data.verification_confirmation_message, '', function( flag ) {

					if ( flag ) {
						$this.api_timesheet.verifyTimeSheet( $this.getSelectEmployee(), $this.timesheet_verify_data.pay_period_id,

							{
								onResult: function( result ) {

									if ( result.isValid() ) {
										$this.search()
									} else {
										TAlertManager.showErrorAlert( result )
									}

								}

							} );
					}

				} );
			} );

		} else {

			verify_btn.css( 'display', 'none' );
			verify_grid_div.css( 'display', 'none' );
			return;

		}

		var verification_data = this.timesheet_verify_data.verification_window_dates.start + ' ' + $.i18n._( 'to' ) + ' ' + this.timesheet_verify_data.verification_window_dates.end

		var pay_period_data = this.pay_period_header;

		this.verification_grid_source.push( {pay_period: pay_period_data, verification: verification_data} );

	},

	buildPunchNoteGridSource: function() {
		this.punch_note_grid_source = [];
		var punch_array = this.full_timesheet_data.punch_data;
		var absence_array = this.full_timesheet_data.user_date_total_data;
		var len = punch_array.length;
		var len1 = absence_array.length;
		var last_control_id = '';
		var date;
		var date_string;
		for ( var i = 0; i < len; i++ ) {
			var punch = punch_array[i];
			date = Global.strToDate( punch.date_stamp );
			date_string = date.format();
			if ( punch.note && punch.punch_control_id !== last_control_id ) {
				this.punch_note_account = this.punch_note_account + 1;
				this.punch_note_grid_source.push( {note: date_string + ' @ ' + punch.punch_time + ': ' + punch.note.replace( /\n/g, ' ' )} );
				last_control_id = punch.punch_control_id;
			}
		}
		for ( var x = 0; x < len1; x++ ) {
			var absence = absence_array[x];
			date = Global.strToDate( absence.date_stamp );
			date_string = date.format();
			if ( absence.note ) {
				this.punch_note_account = this.punch_note_account + 1;
				this.punch_note_grid_source.push( {note: date_string + ' @ ' + Global.secondToHHMMSS( absence.total_time ) + ': ' + absence.note.replace( /\n/g, ' ' )} );
			}
		}
	},

	buildAbsenceSource: function() {

		var map = {};
		this.absence_source = [];
		this.absence_original_source = [];
		var absence_array = this.full_timesheet_data.user_date_total_data;
		var len = absence_array.length;
		var row;

		for ( var i = 0; i < len; i++ ) {

			var absence = absence_array[i];

			if ( absence.object_type_id !== 50 ) {
				continue;
			}
			this.absence_original_source.push( absence );
			var date = Global.strToDate( absence.date_stamp );
			var date_string = date.format( this.full_format );
			var key = absence.src_object_id + '-' + absence.pay_code_id;

			if ( !map[key] ) {
				row = {};
				row.type = TimeSheetViewController.ABSENCE_ROW;
				row.punch_info = absence.name; //Was: absence.absence_policy
				row.punch_info_id = absence.src_object_id;
				row.user_id = absence.user_id;
				row[date_string] = Global.secondToHHMMSS( absence.total_time );
				row[date_string + '_data'] = absence;
				this.absence_source.push( row );
				map[key] = row
			} else {
				row = map[key];
				if ( row[date_string] ) {
					row = {};
					row.type = TimeSheetViewController.ABSENCE_ROW;
					row.punch_info = absence.name; //Was: absence.absence_policy
					row.punch_info_id = absence.src_object_id;
					row.user_id = absence.user_id;
					row[date_string] = Global.secondToHHMMSS( absence.total_time );

					row[date_string + '_data'] = absence;
					this.absence_source.push( row );
					map[key] = row;

				} else {

					this.lastDayIsOverride( date, row, absence );
					row[date_string] = Global.secondToHHMMSS( absence.total_time );
					row[date_string + '_data'] = absence;
				}

			}

			this.absence_cell_count = this.absence_cell_count + 1;

		}

		if ( this.absence_source.length === 0 ) {
			row = {};
			row.punch_info = '';
			row.user_id = this.getSelectEmployee();
			this.absence_source.push( row );
		}

	},

	lastDayIsOverride: function( current_date, row, current_data ) {

		var last_date = new Date( new Date( current_date.getTime() ).setDate( current_date.getDate() - 1 ) );

		var date_str = last_date.format( this.full_format );

		var data = row[date_str + '_data'];

		if ( data && data.override && current_data.src_object_id === data.src_object_id ) {
			return true;
		}

		return false;
	},

	buildTimeSheetSource: function() {
		this.timesheet_data_source = [];
		var punch_array = this.full_timesheet_data.punch_data;
		var len = punch_array.length;
		var row;
		var new_row;
		for ( var i = 0; i < len; i++ ) {
			var punch = punch_array[i];
			// Error: TypeError: Global.strToDate(...) is null in interface/html5/framework/jquery.min.js?v=9.0.1-20151022-081724 line 2 > eval line 4869
			// Punch must have a date
			if ( !punch.date_stamp ) continue;
			var date = Global.strToDate( punch.date_stamp );
			var date_string = date.format( this.full_format );

			var punch_status_id = punch.status_id;

			if ( i === 0 ) {
				row = {};
				row.punch_info = punch.status;
				row.user_id = punch.user_id;
				row[date_string] = punch.punch_time;
				row[date_string + '_data'] = punch;
				row[date_string + '_related_data'] = null;
				row.status_id = punch_status_id;
				row.type = TimeSheetViewController.PUNCH_ROW;
				this.timesheet_data_source.push( row );

				if ( punch_status_id === 10 ) {

					var our_row = {};
					our_row.punch_info = $.i18n._( 'Out' );
					our_row.user_id = punch.user_id;
					our_row[date_string] = '';
					our_row[date_string + '_data'] = null;
					our_row[date_string + '_related_data'] = punch;
					our_row.status_id = 20;
					our_row.type = TimeSheetViewController.PUNCH_ROW;
					this.timesheet_data_source.push( our_row );

				} else {
					new_row = {};
					new_row.punch_info = $.i18n._( 'In' );
					new_row.user_id = punch.user_id;
					new_row[date_string] = '';
					new_row[date_string + '_data'] = null;
					new_row[date_string + '_related_data'] = punch;
					new_row.status_id = 10;
					new_row.type = TimeSheetViewController.PUNCH_ROW;
					this.timesheet_data_source.splice( this.timesheet_data_source.length - 1, 0, new_row );
				}

			} else {

				var find_position = false;
				var timesheet_data_source_len = this.timesheet_data_source.length;
				for ( var j = 0; j < timesheet_data_source_len; j++ ) {
					row = this.timesheet_data_source[j];
					if ( row[date_string] ) {
						continue;
					} else if ( !row[date_string] && row[date_string + '_related_data'] ) {
						var related_punch = row[date_string + '_related_data'];

						if ( related_punch.punch_control_id === punch.punch_control_id ) {
							row[date_string] = punch.punch_time;
							row[date_string + '_data'] = punch;
							find_position = true;
							break;
						}
					} else if ( !row[date_string] && !row[date_string + '_related_data'] && punch.status_id === row.status_id ) {
						row[date_string] = punch.punch_time;
						row[date_string + '_data'] = punch;
						row[date_string + '_related_data'] = null;
						find_position = true;

						if ( punch.status_id === 10 ) {
							new_row = this.timesheet_data_source[j + 1];
							new_row[date_string] = '';
							new_row[date_string + '_data'] = null;
							new_row[date_string + '_related_data'] = punch;
						} else {
							new_row = this.timesheet_data_source[j - 1];
							new_row[date_string] = '';
							new_row[date_string + '_data'] = null;
							new_row[date_string + '_related_data'] = punch;
						}

						break;
					}
				}

				//Need add a new row
				if ( !find_position ) {
					row = {};
					row.punch_info = punch.status;
					row.user_id = punch.user_id;
					row[date_string] = punch.punch_time;
					row[date_string + '_data'] = punch;
					row[date_string + '_related_data'] = null;
					row.status_id = punch_status_id;
					row.type = TimeSheetViewController.PUNCH_ROW;
					this.timesheet_data_source.push( row );

					if ( punch_status_id === 10 ) {

						new_row = {};
						new_row.punch_info = $.i18n._( 'Out' );
						new_row.user_id = punch.user_id;
						new_row[date_string] = '';
						new_row[date_string + '_data'] = null;
						new_row[date_string + '_related_data'] = punch;
						new_row.status_id = 20;
						new_row.type = TimeSheetViewController.PUNCH_ROW;
						this.timesheet_data_source.push( new_row );

					} else {
						new_row = {};
						new_row.punch_info = $.i18n._( 'In' );
						new_row.user_id = punch.user_id;
						new_row[date_string] = '';
						new_row[date_string + '_data'] = null;
						new_row[date_string + '_related_data'] = punch;
						new_row.status_id = 10;
						new_row.type = TimeSheetViewController.PUNCH_ROW;
						this.timesheet_data_source.splice( this.timesheet_data_source.length - 1, 0, new_row );
					}
				}
			}
		}

		row = {};
		row.punch_info = $.i18n._( 'In' );
		row.user_id = this.getSelectEmployee();
		row.status_id = 10;
		row.type = TimeSheetViewController.PUNCH_ROW;
		this.timesheet_data_source.push( row );

		row = {};
		row.punch_info = $.i18n._( 'Out' );
		row.user_id = this.getSelectEmployee();
		row.status_id = 20;
		row.type = TimeSheetViewController.PUNCH_ROW;
		this.timesheet_data_source.push( row );

	},

	buildTimeSheetsColumns: function() {
		this.timesheet_columns = [];
		if ( this.getPunchMode() === 'manual' ) {
			for ( var i = 0; i < 5; i++ ) {
				var column_width = i > 1 ? 123 : 25;
				if ( i === 4 ) {
					column_width = 160;
				}
				if ( i === 0 && !this.show_branch_ui ) {
					continue;
				}
				if ( i === 1 && !this.show_department_ui ) {
					continue;
				}
				if ( i === 2 && (!this.show_job_ui || LocalCacheData.getCurrentCompany().product_edition_id < 20) ) {
					continue;
				}
				if ( i === 3 && (!this.show_job_item_ui || LocalCacheData.getCurrentCompany().product_edition_id < 20) ) {
					continue;
				}
				var column = {
					name: 'empty_cell_' + i,
					index: 'empty_cell_' + i,
					label: ' ',
					width: column_width,
					sortable: false,
					title: false,
					fixed: true
				};
				this.timesheet_columns.push( column );
			}
		}

		var punch_in_out_column = {
			name: 'punch_info',
			index: 'punch_info',
			label: ' ',
			//if not set to 0 in punch timesheet mode, the date column headers are a few px out of alignment and look bad.
			//see #2091 notes for link to the percent-based js fiddle
			width: this.getPunchMode() === 'manual' ? 160 : 100,
			sortable: false,
			title: false,
			formatter: this.onCellFormat,
			fixed: this.getPunchMode() === 'manual' ? true : false
		};
		this.timesheet_columns.push( punch_in_out_column );

		//save full week columns map use to build no pey period column
		this.column_maps = [];
		for ( i = 0; i < 7; i++ ) {

			var current_date = new Date( new Date( this.start_date.getTime() ).setDate( this.start_date.getDate() + i ) );
			var header_text = current_date.format( this.weekly_format );

			var header_text_array = header_text.split( ',' );
			var header_text_array_2 = header_text_array[1].split( ' ' );

			header_text = $.i18n._( header_text_array[0] ) + ', ' + $.i18n._( header_text_array_2[1] ) + ' ' + header_text_array_2[2];

			var data_field = current_date.format( this.full_format );

			this.column_maps.push( current_date.format() );

			var column_info = {
				resizable: false,
				name: data_field,
				index: data_field,
				label: header_text,
				width: 100,
				sortable: false,
				title: false,
				formatter: this.onCellFormat
			};
			this.timesheet_columns.push( column_info );
		}

		return this.timesheet_columns;

	},

	initLayout: function() {

		var $this = this;
		$this.getAllLayouts( function() {
			$this.setSelectLayout();
			//set right click menu to list view grid

		} );
	},

	setSelectLayout: function() {
		var $this = this;

		if ( !Global.isSet( this.grid ) ) {
			var grid = $( this.el ).find( '#grid' );

			grid.attr( 'id', this.ui_id + '_grid' );  //Grid's id is ScriptName + _grid

			grid = $( this.el ).find( '#' + this.ui_id + '_grid' );
		}

		if ( !this.select_layout ) { //Set to defalt layout if no layout at all
			this.select_layout = {id: ''};
			this.select_layout.data = {filter_data: {}, filter_sort: {}};
		}

		//Set Previoous Saved layout combobox in layout panel
		var layouts_array = this.search_panel.getLayoutsArray();

		this.previous_saved_layout_selector.empty();
		if ( layouts_array && layouts_array.length > 0 ) {
			this.previous_saved_layout_div.css( 'display', 'inline' );

			var len = layouts_array.length;
			for ( var i = 0; i < len; i++ ) {
				var item = layouts_array[i];
				this.previous_saved_layout_selector.append( '<option value="' + item.id + '">' + item.name + '</option>' )
			}

			$( this.previous_saved_layout_selector.find( 'option' ) ).filter( function() {
				return $( this ).attr( 'value' ) === $this.select_layout.id;
			} ).prop( 'selected', true ).attr( 'selected', true );

		} else {
			this.previous_saved_layout_div.css( 'display', 'none' );
		}

		//replace select layout filter_data to filter set in onNavigation function when goto view from navigation context group
		if ( LocalCacheData.default_filter_for_next_open_view ) {
			this.select_layout.data.filter_data = LocalCacheData.default_filter_for_next_open_view.filter_data;
			LocalCacheData.default_filter_for_next_open_view = null;
		}

		this.filter_data = this.select_layout.data.filter_data;

		this.setSearchPanelFilter( true ); //Auto change to property tab when set value to search fields.

		this.search( true ); // get punches base on userid, data and filter

	},

	//Start Drag
	setTimesheetGridDragAble: function() {

		var $this = this;

		var position = 0;

		var cells = this.grid.find( "td[role='gridcell']" );
//
		cells.attr( 'draggable', true );

		if ( ie <= 9 ) {
			cells.bind( 'selectstart', function( event ) {
				this.dragDrop();
				return false;
			} );
		}

		cells.unbind( 'dragstart' ).bind( 'dragstart', function( event ) {

			var td = event.target;

			if ( $this.select_punches_array.length < 1 || !$( td ).hasClass( "ui-state-highlight" ) || !$this.select_drag_menu_id ) {
				return false;
			}

			var container = $( "<div class='drag-holder-div'></div>" );

			var len = $this.select_punches_array.length;

			for ( var i = 0; i < len; i++ ) {
				var punch = $this.select_punches_array[i];

				var span = $( "<span class='drag-span'></span>" );
				span.text( punch.status + ': ' + punch.time_stamp );
				container.append( span );
			}

			$( 'body' ).find( '.drag-holder-div' ).remove();

			$( 'body' ).append( container );

			event.originalEvent.dataTransfer.setData( 'Text', 'timesheet' );//JUST ELEMENT references is ok here NO ID

			if ( event.originalEvent.dataTransfer.setDragImage ) {
				event.originalEvent.dataTransfer.setDragImage( container[0], 0, 0 );
			}

			return true;

		} );

		cells.unbind( 'dragover' ).bind( 'dragover', function( e ) {

			var event = e.originalEvent;

			event.preventDefault();
			var $this = this;
			var target = $( this );

			$( '.timesheet-drag-over' ).removeClass( 'timesheet-drag-over' );
			$( '.drag-over-top' ).removeClass( 'drag-over-top' );
			$( '.drag-over-center' ).removeClass( 'drag-over-center' );
			$( '.drag-over-bottom' ).removeClass( ' drag-over-bottom' );

			$( $this ).addClass( 'timesheet-drag-over' );

			//judge which area mouse on in the target cell and set proper style, Keep checking this in drag event.
			if ( event.pageY - target.offset().top <= 8 ) {
				position = -1;
				target.removeClass( 'drag-over-top drag-over-center drag-over-bottom' ).addClass( 'drag-over-top' );
			} else if ( event.pageY - target.offset().top >= target.height() - 5 ) {
				position = 1;
				target.removeClass( 'drag-over-top drag-over-center drag-over-bottom' ).addClass( 'drag-over-bottom' );
			} else {
				position = 0;
				target.removeClass( 'drag-over-top drag-over-center drag-over-bottom' ).addClass( 'drag-over-center' );
			}

		} );

		cells.unbind( 'dragend' ).bind( 'dragend', function( event ) {

			$( '.timesheet-drag-over' ).removeClass( 'timesheet-drag-over' );
			$( '.drag-over-top' ).removeClass( 'drag-over-top' );
			$( '.drag-over-center' ).removeClass( 'drag-over-center' );
			$( '.drag-over-bottom' ).removeClass( ' drag-over-bottom' );
			$( 'body' ).find( '.drag-holder-div' ).remove();

		} );

		cells.unbind( 'drop' ).bind( 'drop', function( event ) {

			event.preventDefault();
			if ( event.stopPropagation ) {
				event.stopPropagation(); // stops the browser from redirecting.
			}

			$( this ).removeClass( 'drag-over-top drag-over-center drag-over-bottom timesheet-drag-over' );
			var target_cell = event.currentTarget;
			var i = 0; //start index;

			if ( $( target_cell ).index() === 0 ) {
				return;
			}

			//Error: Uncaught TypeError: Cannot read property 'punch_date' of undefined in /interface/html5/#!m=TimeSheet&date=20141118&user_id=32916 line 4563
			if ( !$this.select_punches_array || !$this.select_punches_array[i] ) {
				return;
			}

			var punch = $this.select_punches_array[i];

			var punch_date = Global.strToDate( punch.punch_date );

			var row = $this.timesheet_data_source[target_cell.parentNode.rowIndex - 1];

			//Error: Uncaught TypeError: Cannot read property 'status_id' of undefined in /interface/html5/#!m=TimeSheet&date=20150108&user_id=1068 line 5174
			if ( !row ) {
				return;
			}

			var colModel = $this.grid.getGridParam( 'colModel' );

			var data_field = colModel[target_cell.cellIndex].name;

			var target_punch = row[data_field + '_data'];

			var target_related_punch = row[data_field + '_related_data'];

			var target_column_date = Global.strToDate( data_field, $this.full_format )

			var first_select_date = punch_date;

			var time_offset = target_column_date.getTime() - punch_date.getTime();

			var target_column_date_str = target_column_date.format();

			savePunch();

			function savePunch() {

				//Error: Uncaught TypeError: Cannot read property 'date_stamp' of undefined in /interface/html5/#!m=TimeSheet&date=20141229&user_id=39555 line 5207
				if ( !$this.select_punches_array ) {
					return;
				}

				$this.api_date.parseDateTime( target_column_date_str, {
					onResult: function( date_num_result ) {
						var date_num = date_num_result.getResult();

						var new_punch_id = punch.id;
						var target_id = false;
						var target_status_id = row.status_id;
						var action_type = $this.select_drag_menu_id === ContextMenuIconName.move ? 1 : 0;

						//Issue #2008 - All in-punches need target_id to be false to ensure that each pair retains its punch_control settings.
						//Most out-punches need their target id to be the related in-punch.
						//If these conditions are not met, copying groups of punches with different punch_control data will result in all copied punches having the same punch_control data as the first punch pair.
						if ( target_punch && punch.status_id === 20 ) {
							target_id = target_punch.id;
							target_status_id = false;
						} else if ( target_related_punch ) {
							target_id = target_related_punch.id;
							if ( target_related_punch.status_id === 10 ) {
								position = 1;
							} else {
								position = -1;
							}
							target_status_id = false;
						}

						var api_punch_control = new (APIFactory.getAPIClass( 'APIPunchControl' ))();

						api_punch_control.dragNdropPunch( new_punch_id, target_id, target_status_id, position, action_type, date_num, {
							onResult: function( result ) {
								var result_data = result.getResult();
								//Error: Uncaught TypeError: Cannot read property 'date_stamp' of undefined in interface/html5/#!m=TimeSheet&date=20150831&user_id=129895&show_wage=0 line 5286
								if ( result.isValid() && $this.select_punches_array && $this.select_punches_array.length > 0 ) {
									i = i + 1;
									if ( i > $this.select_cells_Array.length - 1 ) {
										$this.search( true );
										return;
									}
									//Error: Uncaught TypeError: Cannot read property 'date_stamp' of undefined in interface/html5/#!m=TimeSheet&date=20150831&user_id=129895&show_wage=0 line 5286
									if ( !$this.select_punches_array[i] ) {
										$this.search( true );
										return;
									}
									while ( !$this.select_punches_array[i].date_stamp ) {
										i = i + 1;
										if ( i > $this.select_cells_Array.length - 1 ) {
											$this.search( true );
											return;
										}
									}
									position = 1; //put next punch below last one
									var last_date_string = target_column_date_str;
									punch = $this.select_punches_array[i];
									punch_date = Global.strToDate( punch.punch_date );
									row = $this.timesheet_data_source[target_cell.parentNode.rowIndex - 1];
									colModel = $this.grid.getGridParam( 'colModel' );
									data_field = colModel[target_cell.cellIndex].name;
									time_offset = punch_date.getTime() - first_select_date.getTime();
									//drop column date
									target_column_date = Global.strToDate( data_field, $this.full_format );
									//Real target column date str
									target_column_date_str = new Date( target_column_date.getTime() + time_offset ).format();
									target_punch = {id: result_data};
									target_related_punch = null;
									if ( target_column_date_str !== last_date_string ) {
										position = 0;
										target_punch = null;
									}
									savePunch();
								} else {
									TAlertManager.showAlert( $.i18n._( 'Unable to drag and drop punch to the specified location' ) );
									if ( i > 0 ) {
										$this.search( true );
									}
								}

							}
						} )

					}
				} );

			}
		} );

	},

	setPunchModeClass: function() {
		this.$el.removeClass( 'timesheet-punch-mode' );
		this.$el.removeClass( 'timesheet-manual-mode' );
		this.getPunchMode() === 'punch' ? this.$el.addClass( 'timesheet-punch-mode' ) : this.$el.addClass( 'timesheet-manual-mode' )
	},

	initData: function() {
		var $this = this;
		Global.removeViewTab( this.viewId );
		var loginUser = LocalCacheData.getLoginUser();
		this.initOptions();
		ProgressBar.showOverlay();
		// Set Wage
		if ( !LocalCacheData.last_timesheet_selected_show_wage ) {
			this.wage_btn.setValue( false );
		} else {
			this.wage_btn.setValue( LocalCacheData.last_timesheet_selected_show_wage );
		}

		//Error: TypeError: Cannot read property 'show_wage' of null
		//just need to check that the variable exists before checking properties for the case of the LocalCacheData being empty
		if ( Global.isSet(LocalCacheData.all_url_args) && LocalCacheData.all_url_args.show_wage ) {
			this.wage_btn.setValue( LocalCacheData.all_url_args.show_wage === '1' ? true : false );
		}

		// Set punch mode
		if ( !this.show_punch_mode_ui ) {
			if ( !PermissionManager.validate( this.permission_id, 'punch_timesheet' ) && !PermissionManager.validate( this.permission_id, 'manual_timesheet' ) ) {
				this.toggle_button.setValue( 'punch' )
			} else {
				if ( PermissionManager.validate( this.permission_id, 'punch_timesheet' ) ) {
					this.toggle_button.setValue('punch');
				}
				if ( LocalCacheData.getCurrentCompany().product_edition_id >= 15 && PermissionManager.validate( this.permission_id, 'manual_timesheet' ) ) {
					this.toggle_button.setValue('manual');
				}
			}
		} else {
			if ( !LocalCacheData.last_timesheet_selected_punch_mode ) {
				this.toggle_button.setValue( 'punch' );

			} else {
				this.toggle_button.setValue( LocalCacheData.last_timesheet_selected_punch_mode );
			}
			if ( LocalCacheData.all_url_args.mode ) {
				// Fix wrong value from url
				this.toggle_button.setValue( LocalCacheData.all_url_args.mode === 'manual' ? 'manual' : 'punch' );
			}
		}
		//replace select layout filter_data to filter set in onNavigation function when goto view from navigation context group
		if ( LocalCacheData.default_filter_for_next_open_view ) {
			this.employee_nav.setValue( LocalCacheData.default_filter_for_next_open_view.user_id );
			this.setDatePickerValue( LocalCacheData.default_filter_for_next_open_view.base_date );
		} else {
			if ( !LocalCacheData.last_timesheet_selected_user ) {
				//Default set current login user as select Employee
				this.employee_nav.setValue( loginUser );
			} else {
				this.employee_nav.setValue( LocalCacheData.last_timesheet_selected_user );
			}
			if ( LocalCacheData.all_url_args.user_id ) {
				this.employee_nav.setValue( LocalCacheData.all_url_args.user_id );
			}
			if ( !LocalCacheData.last_timesheet_selected_date ) { //Saved current select date in cache. so still select last select date when go to other view and back
				if ( LocalCacheData.current_selet_date && Global.strToDate( LocalCacheData.current_selet_date, 'YYYYMMDD' ) ) { //Select date get from URL.
					this.setDatePickerValue( Global.strToDate( LocalCacheData.current_selet_date, 'YYYYMMDD' ).format() );
					LocalCacheData.current_selet_date = '';
				} else {
					var date = new Date();
					var format = Global.getLoginUserDateFormat();
					var dateStr = date.format( format );
					this.setDatePickerValue( dateStr );
				}

			} else {
				this.setDatePickerValue( LocalCacheData.last_timesheet_selected_date );
			}
		}

		$this.initLayout();
		this.setMoveOrDropMode( ContextMenuIconName.move );

	},

	setDatePickerValue: function( val ) {
		this.start_date_picker.setValue( val );

		var default_date = this.start_date_picker.getDefaultFormatValue();

		if ( !this.edit_view &&
			(window.location.href.indexOf( 'date=' + default_date ) === -1 || window.location.href.indexOf( 'user_id=' + this.getSelectEmployee() === -1 )) ) {

			var location = Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + default_date + '&user_id=' + this.getSelectEmployee() + '&show_wage=' + this.wage_btn.getValue( true ) + '&mode=' + this.toggle_button.getValue();

			if ( LocalCacheData.all_url_args ) {
				for ( var key in LocalCacheData.all_url_args ) {
					if ( key === 'm' || key === 'date' || key === 'user_id' || key === 'show_wage' || key === 'mode' ) {
						continue;
					}
					location = location + '&' + key + '=' + LocalCacheData.all_url_args[key];

				}
			}

			Global.setURLToBrowser( location );

		}

		LocalCacheData.last_timesheet_selected_date = val;

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );
		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Punch Branch' ),
				in_column: 1,
				field: 'branch_id',
				layout_name: ALayoutIDs.BRANCH,
				api_class: (APIFactory.getAPIClass( 'APIBranch' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Punch Department' ),
				field: 'department_id',
				in_column: 1,
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Job' ),
				in_column: 2,
				field: 'job_id',
				layout_name: ALayoutIDs.JOB,
				api_class: ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ? (APIFactory.getAPIClass( 'APIJob' )) : null,
				multiple: true,
				basic_search: (this.show_job_item_ui && ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 )),
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Task' ),
				in_column: 2,
				field: 'job_item_id',
				layout_name: ALayoutIDs.JOB_ITEM,
				api_class: ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ? (APIFactory.getAPIClass( 'APIJobItem' )) : null,
				multiple: true,
				basic_search: (this.show_job_item_ui && ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 )),
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	},

	getSelectEmployee: function( full_item ) {
		var user;
		if ( this.show_navigation_box ) {
			user = this.employee_nav.getValue( full_item );
		} else {
			if ( full_item ) {
				user = LocalCacheData.getLoginUser();
			} else {
				user = LocalCacheData.getLoginUser().id;
			}
		}

		return user;
	},

	getSelectDate: function() {
		return this.start_date_picker.getValue();
	},

	onDeleteAndNextClick: function() {
		var $this = this;

		var current_api = this.getCurrentAPI();

		TAlertManager.showConfirmAlert( $.i18n._( 'You are about to delete data, once data is deleted it can not be recovered ' +
		'Are you sure you wish to continue?' ), null, function( result ) {

			var remove_ids = [];
			if ( $this.edit_view ) {
				remove_ids.push( $this.current_edit_record.id );
			}

			if ( result ) {

				ProgressBar.showOverlay();
				current_api['delete' + current_api.key_name]( remove_ids, {
					onResult: function( result ) {
						ProgressBar.closeOverlay();
						if ( result.isValid() ) {
							$this.onRightArrowClick();
						}
						$this.search();
					}
				} );

			} else {
				ProgressBar.closeOverlay();
			}
		} );
	},

	onDeleteClick: function() {
		var $this = this;

		var current_api = this.getCurrentAPI();
		LocalCacheData.current_doing_context_action = 'delete';
		TAlertManager.showConfirmAlert( $.i18n._( 'You are about to delete data, once data is deleted it can not be recovered ' +
		'Are you sure you wish to continue?' ), null, function( result ) {

			var remove_ids = [];
			if ( $this.edit_view ) {
				remove_ids.push( $this.current_edit_record.id );
			} else {
				var len = $this.select_punches_array.length;
				for ( var i = 0; i < len; i++ ) {
					var item = $this.select_punches_array[i];
					remove_ids.push( item.id );
				}
			}
			if ( result ) {
				ProgressBar.showOverlay();
				current_api['delete' + current_api.key_name]( remove_ids, {
					onResult: function( result ) {
						ProgressBar.closeOverlay();
						if ( result.isValid() ) {
							if ( $this.edit_view ) {
								$this.removeEditView();
							}
						}
						$this.search();

					}
				} );

			} else {
				ProgressBar.closeOverlay();
			}
		} );

	},

	reSetURL: function() {
		var args = '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&user_id=' + this.getSelectEmployee() + '&show_wage=' + this.wage_btn.getValue( true ) + '&mode=' + this.toggle_button.getValue();
		Global.setURLToBrowser( Global.getBaseURL() + args );
		LocalCacheData.all_url_args = IndexViewController.instance.router.buildArgDic( args.split( '&' ) );
	},

	onSaveAndContinue: function( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_changed = false;
		LocalCacheData.current_doing_context_action = 'save_and_continue';
		var current_api = this.getCurrentAPI();

		if ( this.is_mass_adding && this.current_edit_record.punch_dates && this.current_edit_record.punch_dates.length === 1 ) {
			this.current_edit_record.punch_date = this.current_edit_record.punch_dates[0];
		}

		current_api['set' + current_api.key_name]( this.current_edit_record, false, ignoreWarning, {
			onResult: function( result ) {
				if ( result.isValid() ) {
					var result_data = result.getResult();
					var refresh_id;
					if ( result_data === true ) {
						refresh_id = $this.current_edit_record.id;

					} else if ( result_data > 0 ) {
						refresh_id = result_data
					}
					$this.search();
					$this.onEditClick( refresh_id );

					$this.onSaveAndContinueDone( result );
				} else {
					$this.setErrorMenu();
					$this.setErrorTips( result );

				}

			}
		} );
	},

	onSaveAndNextClick: function( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_add = false;
		this.is_changed = false;

		var current_api = this.getCurrentAPI();
		LocalCacheData.current_doing_context_action = 'save_and_next';
		current_api['set' + current_api.key_name]( this.current_edit_record, false, ignoreWarning, {
			onResult: function( result ) {
				if ( result.isValid() ) {
					var result_data = result.getResult();
					if ( result_data === true ) {
						$this.refresh_id = $this.current_edit_record.id;
					} else if ( result_data > 0 ) {
						$this.refresh_id = result_data
					}
					$this.onRightArrowClick();
					$this.search( false );
					$this.onSaveAndNextDone( result );

				} else {
					$this.setErrorMenu();
					$this.setErrorTips( result );
				}

			}
		} );
	},

	onViewClick: function( editId, type ) {
		var $this = this;
		$this.is_viewing = true;
		LocalCacheData.current_doing_context_action = 'view';
		if ( type ) {
			if ( type === 'absence' ) {
				this.absence_model = true;
			} else {
				this.absence_model = false;
			}
		}

		$this.openEditView();

		var current_api = this.getCurrentAPI();

		var filter = {};
		var selected_id;
		if ( Global.isSet( editId ) ) {
			selected_id = editId;
		} else {

			if ( this.select_punches_array.length > 0 ) {
				selected_id = this.select_punches_array[0].id;
			} else {
				return;
			}
		}

		filter.filter_data = {};
		filter.filter_data.id = [selected_id];

		current_api['get' + current_api.key_name]( filter, {
			onResult: function( result ) {

				var result_data = result.getResult();

				result_data = result_data[0];

				if ( !result_data ) {
					TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
					$this.onCancelClick();
					return;
				}

				$this.current_edit_record = result_data;

				$this.initEditView();

			}
		} );

	},

	buildOtherFieldUI: function( field, label ) {

		if ( !this.edit_view_tab ) {
			return;
		}

		var form_item_input;
		var $this = this;
		var tab_punch = this.edit_view_tab.find( '#tab_punch' );
		var tab_punch_column1 = tab_punch.find( '.first-column' );

		if ( $this.edit_view_ui_dic[field] ) {
			form_item_input = $this.edit_view_ui_dic[field];
			form_item_input.setValue( $this.current_edit_record[field] );
			form_item_input.css( 'opacity', 1 );
		} else {
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( {field: field} );
			var input_div = $this.addEditFieldToColumn( label, form_item_input, tab_punch_column1 );

			input_div.insertBefore( this.edit_view_form_item_dic['note'] );

			form_item_input.setValue( $this.current_edit_record[field] );
			form_item_input.css( 'opacity', 1 );
		}

		if ( $this.is_viewing ) {
			form_item_input.setEnabled( false );
		} else {
			form_item_input.setEnabled( true );
		}

	},

	onMassEditClick: function() {
		var $this = this;
		LocalCacheData.current_doing_context_action = 'mass_edit';
		$this.openEditView();
		this.is_mass_adding = false;
		this.is_viewing = false;

		var current_api = this.getCurrentAPI();

		var filter = {};
		this.mass_edit_record_ids = [];

		$.each( this.select_punches_array, function( index, value ) {
			$this.mass_edit_record_ids.push( value.id )
		} );

		filter.filter_data = {};
		filter.filter_data.id = this.mass_edit_record_ids;

		current_api['getCommon' + current_api.key_name + 'Data']( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();
				current_api['getOptions']( 'unique_columns', {
					onResult: function( result ) {
						$this.unique_columns = result.getResult();
						current_api['getOptions']( 'linked_columns', {
							onResult: function( result1 ) {

								$this.linked_columns = result1.getResult();

								if ( $this.sub_view_mode && $this.parent_key ) {
									result_data[$this.parent_key] = $this.parent_value;
								}

								if ( !Global.isSet( result_data.time_stamp ) ) {
									result_data.time_stamp = false;
								}

								$this.current_edit_record = result_data;
								$this.is_mass_editing = true;
								$this.initEditView();

							}
						} );

					}
				} );

			}
		} );

	},

	initSubLogView: function( tab_id ) {

		var $this = this;
		if ( this.sub_log_view_controller ) {
			this.sub_log_view_controller.buildContextMenu( true );
			this.sub_log_view_controller.setDefaultMenu();
			$this.sub_log_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_log_view_controller.getSubViewFilter = function( filter ) {

				if ( !$this.absence_model ) {
					filter['table_name_object_id'] = {
						'punch': [this.parent_edit_record.id],
						'punch_control': [this.parent_edit_record.punch_control_id]
					};
				} else {
					filter['table_name'] = 'user_date_total';
					filter['object_id'] = this.parent_edit_record.id;

				}

				return filter;
			};
			$this.sub_log_view_controller.initData();
			return;
		}

		Global.loadScript( 'views/core/log/LogViewController.js', function() {
			var tab = $this.edit_view_tab.find( '#' + tab_id );
			var firstColumn = tab.find( '.first-column-sub-view' );
			Global.trackView( 'Sub' + 'Log' + 'View' );
			LogViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );
		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_log_view_controller = subViewController;
			$this.sub_log_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_log_view_controller.getSubViewFilter = function( filter ) {
				if ( !$this.absence_model ) {
					filter['table_name_object_id'] = {
						'punch': [this.parent_edit_record.id],
						'punch_control': [this.parent_edit_record.punch_control_id]
					};
				} else {
					filter['table_name'] = 'user_date_total';
					filter['object_id'] = this.parent_edit_record.id;

				}

				return filter;
			};
			$this.sub_log_view_controller.parent_view_controller = $this;
			$this.sub_log_view_controller.initData();

		}
	},

	onEditClick: function( editId, type ) {

		var $this = this;
		var selected_id;
		if ( Global.isSet( editId ) ) {
			selected_id = editId
		} else {
			if ( this.is_viewing ) {
				selected_id = this.current_edit_record.id;
			} else if ( this.select_punches_array.length > 0 ) {
				selected_id = this.select_punches_array[0].id;
			} else {
				return;
			}

		}
		this.is_mass_adding = false;
		this.is_viewing = false;
		LocalCacheData.current_doing_context_action = 'edit';
		if ( type ) {
			if ( type === 'absence' ) {
				this.absence_model = true;
			} else {
				this.absence_model = false;
			}
		}

		$this.openEditView();

		var current_api = this.getCurrentAPI();

		var filter = {};

		filter.filter_data = {};
		filter.filter_data.id = [selected_id];

		current_api['get' + current_api.key_name]( filter, {
			onResult: function( result ) {

				var result_data = result.getResult();
				result_data = result_data[0];

				if ( !result_data ) {
					TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
					$this.onCancelClick();
					return;
				}

				$this.current_edit_record = result_data;

				$this.initEditView();

			}
		} );

	},

	setURL: function() {
		var t = this.absence_model ? 'absence' : 'punch';
		var a = '';
		switch ( LocalCacheData.current_doing_context_action ) {
			case 'new':
			case 'edit':
			case 'view':
				a = LocalCacheData.current_doing_context_action;
				break;
			case 'copy_as_new':
				a = 'new';
				break;
		}

		var tab_name = this.edit_view_tab ? this.edit_view_tab.find( '.edit-view-tab-bar-label' ).children().eq( this.edit_view_tab_selected_index ).text() : '';
		tab_name = tab_name.replace( /\/|\s+/g, '' );

		//Error: Unable to get property 'id' of undefined or null reference in /interface/html5/views/BaseViewController.js?v=8.0.0-20141117-132941 line 2234
		if ( this.current_edit_record && this.current_edit_record.id ) {

			if ( a ) {
				Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&user_id=' + this.getSelectEmployee() + '&show_wage=' + this.wage_btn.getValue( true ) + '&mode=' + this.toggle_button.getValue() + '&a=' + a + '&id=' + this.current_edit_record.id + '&t=' + t +
				'&tab=' + tab_name );

			} else {
				Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&user_id=' + this.getSelectEmployee() + '&show_wage=' + this.wage_btn.getValue( true ) + '&mode=' + this.toggle_button.getValue() + '&id=' + this.current_edit_record.id + '&t=' + t );
			}

			Global.trackView();

		} else {

			if ( a ) {
				Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&user_id=' + this.getSelectEmployee() + '&show_wage=' + this.wage_btn.getValue( true ) + '&mode=' + this.toggle_button.getValue() + '&a=' + a + '&t=' + t +
				'&tab=' + tab_name );
			} else {
				Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&user_id=' + this.getSelectEmployee() + '&show_wage=' + this.wage_btn.getValue( true ) + '&mode=' + this.toggle_button.getValue() + '&t=' + t );
			}

		}
	},

	onContextMenuClick: function( context_btn, menu_name ) {

		if ( !this.checkTimesheetData() ) {
			return;
		}
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
			case ContextMenuIconName.add:
				this.absence_model = false;
				ProgressBar.showOverlay();
				this.onAddClick();
				break;
			case ContextMenuIconName.add_absence:
				this.absence_model = true;
				ProgressBar.showOverlay();
				this.onAddClick();
				break;
			case ContextMenuIconName.view:
				ProgressBar.showOverlay();
				this.onViewClick();
				break;
			case ContextMenuIconName.save:
				ProgressBar.showOverlay();
				this.onSaveClick();
				break;
			case ContextMenuIconName.save_and_next:
				ProgressBar.showOverlay();
				this.onSaveAndNextClick();
				break;
			case ContextMenuIconName.save_and_continue:
				ProgressBar.showOverlay();
				this.onSaveAndContinue();
				break;
			case ContextMenuIconName.save_and_new:
				ProgressBar.showOverlay();
				this.onSaveAndNewClick();
				break;
			case ContextMenuIconName.save_and_copy:
				ProgressBar.showOverlay();
				this.onSaveAndCopy();
				break;
			case ContextMenuIconName.edit:
				ProgressBar.showOverlay();
				this.onEditClick();
				break;
			case ContextMenuIconName.mass_edit:
				ProgressBar.showOverlay();
				this.onMassEditClick();
				break;
			case ContextMenuIconName.delete_icon:
				ProgressBar.showOverlay();
				this.onDeleteClick();
				break;
			case ContextMenuIconName.delete_and_next:
				ProgressBar.showOverlay();
				this.onDeleteAndNextClick();
				break;
			case ContextMenuIconName.copy:
				ProgressBar.showOverlay();
				this.onCopyClick();
				break;
			case ContextMenuIconName.copy_as_new:
				ProgressBar.showOverlay();
				this.onCopyAsNewClick();
				break;
			case ContextMenuIconName.cancel:
				this.onCancelClick();
				break;
			case ContextMenuIconName.move:
			case ContextMenuIconName.drag_copy:
				this.setMoveOrDropMode( id );
				break;
			case ContextMenuIconName.in_out:
			case ContextMenuIconName.schedule:
			case ContextMenuIconName.pay_stub:
			case ContextMenuIconName.edit_employee:
			case ContextMenuIconName.edit_pay_period:
				this.onNavigationClick( id );
				break;
			case ContextMenuIconName.re_calculate_timesheet:
			case ContextMenuIconName.generate_pay_stub:
				this.onWizardClick( id );
				break;
			case ContextMenuIconName.map:
				this.onMapClick( id );
				break;
			case ContextMenuIconName.accumulated_time:
				this.onAccumulatedTimeClick( id );
				break;
			case 'AddRequest':
				this.addRequestFromTimesheetCell( id );
				break;
		}
	},

	addRequestFromTimesheetCell: function(id) {
		if ( LocalCacheData.getCurrentCompany().product_edition_id < 15 ) {
			TAlertManager.showAlert( Global.getUpgradeMessage() );
			return false;
		}

		var current_column_field = Global.strToDate( this.select_cells_Array[0].date ? this.select_cells_Array[0].date : this.start_date_picker.getValue() ).format( this.full_format );

        if (this.select_cells_Array[0].punch) {
            var punch_control_id = this.select_cells_Array[0].punch.punch_control_id;
            var current_punch_id = this.select_cells_Array[0].punch.id;
            var current_punch_status_id = this.select_cells_Array[0].punch.status_id;
            var type_id = this.select_cells_Array[0].punch.type_id;
            var user_id = this.select_cells_Array[0].punch.user_id
        } else {
            var user_id = this.getSelectEmployee();
            var punch_control_id = null;
            var current_punch_id = null;
            var current_punch_status_id = 10;
            var type_id = 10;
        }

		var previous_punch_id = null;
		if( !current_punch_id ) {
			if (this.select_cells_Array[0].row_id > 1 && this.timesheet_data_source[this.select_cells_Array[0].row_id - 2][current_column_field + '_data']) {
				previous_punch_id = this.timesheet_data_source[this.select_cells_Array[0].row_id - 2][current_column_field + '_data'].id;
				type_id = this.timesheet_data_source[this.select_cells_Array[0].row_id - 2][current_column_field + '_data'].type_id;
			}
			//blank and has no previous punch so we need to infer status_id from the selected row's status
			current_punch_status_id = this.timesheet_data_source[this.select_cells_Array[0].row_id - 1].status_id;
		}

		var date = this.select_cells_Array[0].time_stamp_num/1000;
		var $this = this;
		this.api_punch.getRequestDefaultData(
		    user_id,
			date,
			punch_control_id,
			previous_punch_id,
			current_punch_status_id,
			type_id,
			current_punch_id, {
			onResult: function( result ) {
				var request = result.getResult();
				IndexViewController.openEditView( $this, 'Request', request );
			}}
		);
	},
	
	getPayPeriod: function( date ) {

		var current_date = this.getSelectDate();

		//if pass a date in, use the date
		if ( date ) {
			current_date = date;
		}

		if ( this.pay_period_map && this.pay_period_map[current_date] && parseInt( this.pay_period_map[current_date] ) > 0 ) {
			return this.pay_period_map[current_date];
		} else {
			return null;
		}
	},

	onNavigationClick: function( iconName ) {

		if ( !this.checkTimesheetData() ) {
			return;
		}

		var post_data;

		switch ( iconName ) {
			case ContextMenuIconName.in_out:
				IndexViewController.openEditView( LocalCacheData.current_open_primary_controller, 'InOut' );
				break;
			case ContextMenuIconName.edit_employee:
				IndexViewController.openEditView( this, 'Employee', this.getSelectEmployee() );
				break;
			case ContextMenuIconName.edit_pay_period:
				var pay_period_id = this.getPayPeriod();
				if ( pay_period_id ) {
					IndexViewController.openEditView( this, 'PayPeriods', pay_period_id )
				}
				break;
			case ContextMenuIconName.schedule:
				var filter = {filter_data: {}};
				var include_users = {value: [this.getSelectEmployee()]};
				filter.filter_data.include_user_ids = include_users;
				filter.select_date = this.getSelectDate();

				Global.addViewTab( this.viewId, 'TimeSheet', window.location.href );
				IndexViewController.goToView( 'Schedule', filter );

				break;
			case ContextMenuIconName.pay_stub:
				filter = {filter_data: {}};
				var users = {value: [this.getSelectEmployee()]};
				filter.filter_data.user_id = users;

				Global.addViewTab( this.viewId, 'TimeSheet', window.location.href );
				IndexViewController.goToView( 'PayStub', filter );

				break;
			case 'print_summary':

				filter = {time_period: {}};
				filter.time_period.time_period = 'custom_pay_period';
				filter.time_period.pay_period_id = this.timesheet_verify_data.pay_period_id;
				filter.include_user_id = [this.getSelectEmployee()];
				post_data = {0: filter, 1: 'pdf_timesheet'};
				this.doFormIFrameCall( post_data );
				break;
			case 'print_detailed':
				filter = {time_period: {}};
				filter.time_period.time_period = 'custom_pay_period';
				filter.time_period.pay_period_id = this.timesheet_verify_data.pay_period_id;
				filter.include_user_id = [this.getSelectEmployee()];
				post_data = {0: filter, 1: 'pdf_timesheet_detail'};
				this.doFormIFrameCall( post_data );
				break;
		}
	},

	doFormIFrameCall: function( postData ) {
		this.sendIframeCall('APITimesheetDetailReport','getTimesheetDetailReport', postData);
	},

	onAccumulatedTimeClick: function() {
		if ( PermissionManager.checkTopLevelPermission( 'AccumulatedTime' ) ) {
			var select_date = Global.strToDate( this.getSelectDate() ).format( 'YYYYMMDD' );
			IndexViewController.openEditView( this, 'UserDateTotalParent', select_date );
		}
	},

	onMapClick: function() {
		var punches;
		if ( this.edit_view ) {
			punches = [this.current_edit_record];
		} else if ( this.select_punches_array && this.select_punches_array.length > 0 ) {
			punches = this.select_punches_array;
		} else {
			punches = this.full_timesheet_data.punch_data;
		}
		if ( !this.is_mass_editing ) {
			IndexViewController.openEditView( this, "Map", punches );
		}
		//window.open( url, '_blank' );
	},

	onWizardClick: function( iconName ) {

		var $this = this;
		switch ( iconName ) {
			case ContextMenuIconName.re_calculate_timesheet:
				var default_data = {};
				default_data.user_id = this.getSelectEmployee();

				var pay_period_id = this.getPayPeriod();
				if ( pay_period_id ) {
					default_data.pay_period_id = pay_period_id;
				}
				IndexViewController.openWizard( 'ReCalculateTimeSheetWizard', default_data, function() {

					$this.onReCalTimeSheetDone();
				} );
				break;
			case ContextMenuIconName.generate_pay_stub:

				default_data = {};
				default_data.user_id = this.getSelectEmployee();

				pay_period_id = this.getPayPeriod();
				if ( pay_period_id ) {
					default_data.pay_period_id = [pay_period_id];
				} else {
					default_data.pay_period_id = [];
				}
				IndexViewController.openWizard( 'GeneratePayStubWizard', default_data, function() {
					$this.search();
				} );
				break;
		}

	},

	onReCalTimeSheetDone: function() {
		this.initData();
	},

	setMoveOrDropMode: function( id ) {

		var drag_copy_icon = $( '#' + ContextMenuIconName.drag_copy );
		var move_icon = $( '#' + ContextMenuIconName.move );
		drag_copy_icon.removeClass( 'selected-menu' );
		move_icon.removeClass( 'selected-menu' );

		var drag_invisible = false;
		var move_invisible = false;

		if ( !this.copyPermissionValidate() ) {
			drag_invisible = true;
		}

		if ( !this.movePermissionValidate() ) {
			move_invisible = true;
		}

		if ( move_invisible && id === ContextMenuIconName.move ) {
			drag_copy_icon.addClass( 'selected-menu' );
		} else {
			$( '#' + id ).addClass( 'selected-menu' );
		}

		if ( drag_invisible && move_invisible ) {
			this.select_drag_menu_id = null;
		} else {
			this.select_drag_menu_id = id;
		}

	},

	getSelectDateArray: function() {

		var result = [];

		var cells_array = this.absence_model ? this.absence_select_cells_Array : this.select_cells_Array;

		var len = cells_array.length;

		var date_dic = {};
		for ( var i = 0; i < len; i++ ) {
			var item = cells_array[i];
			date_dic[item.date] = true;
		}

		for ( var key in date_dic ) {
			result.push( key )
		}

		if ( result.length === 0 ) {
			result = [this.getSelectDate()];
		}

		return result;

	},

	onAddClick: function( doing_save_and_new ) {

		var $this = this;
		this.is_viewing = false;
		this.is_mass_adding = true;
		LocalCacheData.current_doing_context_action = 'new';
		var punch_control_id = null;
		var prev_punch_id = null;
		var related_punch = null;
		var date = this.getSelectDate();
		var status_id = 10, type_id = 10, select_cell;

		if ( !this.absence_model ) {
			if ( this.select_cells_Array.length === 1 ) {
				var select_item = this.select_cells_Array[0];
				if ( select_item.related_punch ) {
					related_punch = select_item.related_punch;
					punch_control_id = select_item.related_punch.punch_control_id;
					prev_punch_id = select_item.related_punch.id;
				} else {
					//Error: Uncaught TypeError: Cannot read property 'format' of null in interface/html5/#!m=TimeSheet&date=20151006&user_id=51085&show_wage=0 line 6292
					var current_column_field = Global.strToDate( select_item.date ? select_item.date : this.start_date_picker.getValue() ).format( this.full_format );

					if ( this.timesheet_data_source && this.timesheet_data_source[select_item.row_id - 2] ) {
						var pre_punch = this.timesheet_data_source[select_item.row_id - 2][current_column_field + '_data'];
					}

					if ( pre_punch ) {
						prev_punch_id = pre_punch.id;
					}

				}

			}
			// To use proper context menu for each punch or abseonce mode.
			this.setDefaultMenu();
			$this.openEditView();

			if ( doing_save_and_new ) {
				date = this.current_edit_record.punch_date;
				related_punch = null;
				if ( this.current_edit_record.status_id === 10 ) {
					punch_control_id = this.current_edit_record.punch_control_id;
				} else {
					punch_control_id = null;
				}

			}

			if ( this.select_cells_Array.length === 1 ) {
				select_cell = this.select_cells_Array[0];
				if ( select_cell.row_id % 2 !== 0 ) {
					status_id = 10;
				} else {
					status_id = 20;
				}

				var select_date = Global.strToDate( this.start_date_picker.getValue() );
				var new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() - 1 ) );
				if ( new_date.getTime() < this.start_date.getTime() ) {
					type_id = 10;
				} else {
					var row_data = this.timesheet_data_source[select_cell.row_id - 1];
					//Error: Unable to get property 'Sun-Dec-13-2015_data' of undefined or null reference in interface/html5/ line 6362
					var left_side_punch = row_data && row_data[new_date.format( this.full_format ) + '_data'];
					if ( left_side_punch ) {
						type_id = left_side_punch.type_id;
					} else {
						type_id = 10;
					}
				}
			}
			this.api['get' + this.api.key_name + 'DefaultData']( this.getSelectEmployee(),
				date,
				punch_control_id,
				prev_punch_id,
				status_id,
				type_id,
				{
					onResult: function( result ) {

						var result_data = result.getResult();

						if ( !Global.isSet( result_data.time_stamp ) ) {
							result_data.time_stamp = false;
						}

						if ( !$this.is_mass_adding && related_punch ) {
							result_data.punch_date = related_punch.punch_date;
							result_data.punch_time = related_punch.punch_time;

							if ( related_punch.status_id === 10 ) {
								result_data.status_id = 20;
							} else {
								result_data.status_id = 10;
							}
						} else {
							result_data.punch_date = $this.getSelectDate();
							var select_cell_item = $this.select_cells_Array[0];
							if ( select_cell_item ) {
								if ( select_cell_item.row_id % 2 !== 0 ) {
									result_data.status_id = 10;
								} else {
									result_data.status_id = 20;
								}
							}

						}

						// Set in or out base on first item select row
						if ( $this.is_mass_adding ) {
							var first_item = $this.select_cells_Array[0];

							if ( !first_item || first_item.row_id % 2 !== 0 ) {
								result_data.status_id = 10;
							} else {
								result_data.status_id = 20;
							}
						}

						if ( doing_save_and_new ) {
							result_data.punch_date = $this.current_edit_record.punch_date;

							if ( $this.current_edit_record.status_id === 10 ) {
								result_data.status_id = 20;
							} else {
								result_data.status_id = 10;
							}

						}

						$this.current_edit_record = result_data;
						$this.initEditView();

					}
				} );

		} else { //Absence model branch

			if ( doing_save_and_new ) {
				date = this.current_edit_record.date_stamp;
			}
			// To use proper context menu for each punch or abseonce mode.
			$this.setDefaultMenu();
			$this.openEditView();
			this.api_user_date_total['get' + this.api_user_date_total.key_name + 'DefaultData']( this.getSelectEmployee(),
				date,
				{
					onResult: function( result ) {

						var result_data = result.getResult();

						if ( !Global.isSet( result_data.time_stamp ) ) {
							result_data.time_stamp = false;
						}

						if ( Global.isSet( $this.absence_select_cells_Array[0] ) ) {
							result_data.src_object_id = $this.absence_select_cells_Array[0].src_object_id;
						}

						result_data.object_type_id = 50;

						result_data.date_stamp = $this.getSelectDate();
						$this.current_edit_record = result_data;
						$this.initEditView();

					}
				} );

		}

	},

	removeEditView: function() {
		this._super( 'removeEditView' );
		if ( this.absence_select_cells_Array.length > 0 ) {
			this.absence_model = true;
		} else {
			this.absence_model = false;
		}
		this.setDefaultMenu();
	},

	isMassDate: function() {
		//Error: Unable to get property 'punch_dates' of undefined or null reference in /interface/html5/ line 6300
		if ( this.is_mass_adding && this.current_edit_record && this.current_edit_record.punch_dates && this.current_edit_record.punch_dates.length > 1 ) {
			return true;
		}

		return false;
	},

	setEditMenuSaveAndContinueIcon: function( context_btn, pId ) {
		this.saveAndContinueValidate( context_btn, pId );

		if ( this.is_mass_editing || this.is_viewing || this.isMassDate() ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	onSaveAndNewClick: function( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		var current_api = this.getCurrentAPI();
		LocalCacheData.current_doing_context_action = 'new';

		var record = this.current_edit_record;

		if ( this.is_mass_adding ) {

			record = [];
			var dates_array = this.current_edit_record.punch_dates;

			if ( dates_array && dates_array.indexOf( ' - ' ) > 0 ) {
				dates_array = this.parserDatesRange( dates_array );
			}

			for ( var i = 0; i < dates_array.length; i++ ) {
				var common_record = Global.clone( this.current_edit_record );
				delete common_record.punch_dates;
				if ( this.absence_model ) {
					common_record.date_stamp = dates_array[i];
				} else {
					common_record.punch_date = dates_array[i];
				}

				record.push( common_record );
			}
		}

		current_api['set' + current_api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				if ( result.isValid() ) {
					$this.search( false );
					$this.onAddClick( true );
				} else {
					$this.setErrorMenu();
					$this.setErrorTips( result );
				}

			}
		} );
	},

	_continueDoCopyAsNew: function() {
		var $this = this;
		LocalCacheData.current_doing_context_action = 'copy_as_new';
		this.is_mass_adding = true;

		if ( Global.isSet( this.edit_view ) ) {
			this.current_edit_record.id = '';

			if ( !this.absence_model ) {

				this.current_edit_record.punch_control_id = '';

				if ( this.current_edit_record.status_id === 10 ) {
					this.current_edit_record.status_id = 20;

				} else {
					this.current_edit_record.status_id = 10;
				}

				this.edit_view_ui_dic['status_id'].setValue( this.current_edit_record.status_id );
			}

			var navigation_div = this.edit_view.find( '.navigation-div' );
			navigation_div.css( 'display', 'none' );
			this.openEditView();
			this.initEditView();
			this.setEditMenu();
			this.setTabStatus();
			this.is_changed = false;
			ProgressBar.closeOverlay();
		}

	},

	onSaveAndCopy: function( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		var current_api = this.getCurrentAPI();
		this.is_changed = false;
		LocalCacheData.current_doing_context_action = 'save_and_copy';
		var record = this.current_edit_record;
		if ( this.is_mass_adding ) {

			record = [];
			var dates_array = this.current_edit_record.punch_dates;

			if ( dates_array && dates_array.indexOf( ' - ' ) > 0 ) {
				dates_array = this.parserDatesRange( dates_array );
			}

			for ( var i = 0; i < dates_array.length; i++ ) {
				var common_record = Global.clone( this.current_edit_record );
				delete common_record.punch_dates;
				if ( this.absence_model ) {
					common_record.date_stamp = dates_array[i];
				} else {
					common_record.punch_date = dates_array[i];
				}

				record.push( common_record );
			}
		}

		this.clearNavigationData();
		current_api['set' + current_api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				if ( result.isValid() ) {
					var result_data = result.getResult();
					$this.search( false );
					$this.onCopyAsNewClick();
				} else {
					$this.setErrorMenu();
					$this.setErrorTips( result );
				}

			}
		} );
	},

	getCurrentAPI: function() {
		var current_api = this.api;

		if ( this.absence_model ) {
			current_api = this.api_user_date_total;
		}

		return current_api;
	},

	createCurrentManualGridRecordsMap: function( records ) {
		var $this = this;
		this.manual_grid_records_map = {};
		for ( var i = 0, m = records.length; i < m; i++ ) {
			var item = records[i];
			var key = item.date_stamp + '-' + ((this.show_branch_ui && item.branch_id) ? item.branch_id : 0) +
				'-' + ((this.show_department_ui && item.department_id) ? item.department_id : 0)
				+ '-' + ((this.show_job_ui && item.job_id && LocalCacheData.getCurrentCompany().product_edition_id >= 20) ? item.job_id : 0) +
				'-' + ((this.show_job_item_ui && item.job_item_id && LocalCacheData.getCurrentCompany().product_edition_id >= 20) ? item.job_item_id : 0) +
				'-' + item.total_time;
			item.id && (key = item.id + '-' + key);
			this.manual_grid_records_map[key] = item.row;
			delete item.row;
		}

	},

	onSaveClick: function( ignoreWarning ) {
		var $this = this;
		var record;
		// Save manual punch
		if ( this.getPunchMode() === 'manual' && !this.edit_view ) {
			var records = this.editor.getValue( true ); // reset is_changed
			if ( records.length > 0 ) {
				this.wait_auto_save && clearTimeout( this.wait_auto_save );
				this.createCurrentManualGridRecordsMap( records );
				ProgressBar.noProgressForNextCall();
				this.is_saving_manual_grid = true;
				this.setDefaultMenu();
				this.api_user_date_total['set' + this.api_user_date_total.key_name]( records, {
					onResult: function( result ) {
						$this.updateManualGrid();
					}
				} );
				ProgressBar.showNanobar();
				ProgressBar.closeOverlay();
			} else {
				ProgressBar.closeOverlay();
			}
			return;
		}

		//Save normal punch
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		LocalCacheData.current_doing_context_action = 'save';
		var current_api = this.getCurrentAPI();

		if ( this.is_mass_editing ) {

			var check_fields = {};
			for ( var key in this.edit_view_ui_dic ) {
				var widget = this.edit_view_ui_dic[key];

				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() ) {
						check_fields[key] = this.current_edit_record[key];
					}
				}
			}

			record = [];
			$.each( this.mass_edit_record_ids, function( index, value ) {
				var common_record = Global.clone( check_fields );
				common_record.id = value;
				record.push( common_record );

			} );
		} else {
			record = this.current_edit_record;
		}

		// Error: Uncaught TypeError: Cannot read property 'punch_dates' of null in /interface/html5/#!m=TimeSheet&date=20150323&user_id=69543 line 6448
		if ( this.is_mass_adding && this.current_edit_record ) {

			record = [];
			var dates_array = this.current_edit_record.punch_dates;

			if ( dates_array && dates_array.indexOf( ' - ' ) > 0 ) {
				dates_array = this.parserDatesRange( dates_array );
			}

			for ( var i = 0; i < dates_array.length; i++ ) {
				var common_record = Global.clone( this.current_edit_record );
				delete common_record.punch_dates;
				if ( this.absence_model ) {
					common_record.date_stamp = dates_array[i];
				} else {
					common_record.punch_date = dates_array[i];
				}

				record.push( common_record );
			}
		}

		current_api['set' + current_api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {

				if ( result.isValid() ) {
					$this.search();

					$this.removeEditView();

				} else {
					$this.setErrorMenu();
					$this.setErrorTips( result );
				}

			}
		} );
	},

	getOtherFieldTypeId: function() {
		var res = 15;

		if ( this.absence_model ) {
			res = 0;
		}

		return res;
	},

	/**
	 * This function is special as it handles an edit view that deals with both absences and punches.
	 * This is the only place where 2 different data layouts need to be handled by the same navigation without a change of view.
	 */
	setEditViewData: function() {
		var $this = this;
		var navigation_div = this.edit_view.find('.navigation-div');
		var navigation_widget_div = navigation_div.find('.navigation-widget-div');

		this.is_changed = false;

		if ( Global.isSet( this.current_edit_record.id ) && this.current_edit_record.id ) {
			//fixing both #2171 and #2227
			//preventing unclickable navigation and "cannot find property or function has of undefined."
			navigation_div.css('display', 'block');
			this.navigation = Global.loadWidgetByName(FormItemType.AWESOME_BOX);

			//Set Navigation Awesomebox
			if ( !this.absence_model ) {
				this.navigation.AComboBox({
					navigation_mode: true,
					id: this.script_name + '_navigation',
					layout_name: ALayoutIDs.TIMESHEET
				});
				this.navigation.setSourceData(this.full_timesheet_data.punch_data);
				this.navigation.is_punch_nav = true;
			} else {
				this.navigation.AComboBox({
					navigation_mode: true,
					id: this.script_name + '_navigation',
					layout_name: ALayoutIDs.ABSENCE
				});
				this.navigation.setSourceData(this.absence_original_source);
				this.navigation.is_punch_nav = false;
			}

			this.navigation.setValue( this.current_edit_record );

			navigation_widget_div.html( this.navigation );
			// #2122 - Fixes navigation errors including: "Cannot read property 'current_page' of null" & "Cannot read property 'has' of null"
			// Prevents user clicking on drop-down to navigate to the first record then immediately clicking the left arrow which triggers the errors.
			this.setNavigation();

		} else {
			navigation_div.css( 'display', 'none' );
		}

		for ( var key in this.edit_view_ui_dic ) {

			//Set all UI field to current edit record, we need validate all UI fielld when save and validate
			if ( !Global.isSet( $this.current_edit_record[key] ) && !this.is_mass_editing ) {
				$this.current_edit_record[key] = false;
			}
		}

		if ( this.is_mass_editing ) {
			for ( key in this.edit_view_ui_dic ) {
				var widget = this.edit_view_ui_dic[key];
				if ( Global.isSet( widget.setMassEditMode ) ) {
					widget.setMassEditMode( true );
				}

			}

			$.each( this.unique_columns, function( index, value ) {

				if ( Global.isSet( $this.edit_view_ui_dic[value] ) && Global.isSet( $this.edit_view_ui_dic[value].setEnabled ) ) {
					$this.edit_view_ui_dic[value].setEnabled( false );
				}

			} );

		}

		this.setNavigationArrowsStatus();

		// Create this function alone because of the column value of view is different from each other, some columns need to be handle specially. and easily to rewrite this function in sub-class.
		this.setCurrentEditRecordData();

		//Init *Please save this record before modifying any related data* box
		this.edit_view.find( '.save-and-continue-div' ).SaveAndContinueBox( {related_view_controller: this} );
		this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'none' );

		if ( this.edit_view_tab.tabs( 'option', 'selected' ) === 1 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubLogView( 'tab_audit' );
			} else {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}
		}

		this.switchToProperTab();
	},

	setCurrentEditRecordData: function() {
		//Set current edit record data to all widgets

		var tab_0_label = this.edit_view.find( 'a[ref=tab_punch]' );

		if ( this.absence_model ) {
			tab_0_label.text( $.i18n._( 'Absence' ) );
		} else {
			tab_0_label.text( $.i18n._( 'Punch' ) );
		}

		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}
			var widget = this.edit_view_ui_dic[key];
			var args;
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'punch_dates':
						var date_array;
						if ( !this.current_edit_record.punch_dates ) {
							date_array = this.getSelectDateArray();
							this.current_edit_record.punch_dates = date_array;
						} else {
							date_array = this.current_edit_record.punch_dates;
						}
						widget.setValue( date_array );
						break;
					case 'first_last_name':
						var select_employee = this.getSelectEmployee( true ); //Get full item
						//Error: Uncaught TypeError: Cannot read property 'first_name' of null in interface/html5/#!m=TimeSheet&date=null&user_id=null&show_wage=0&a=new&t=punch&tab=Punch line 6810
						if ( select_employee ) {
							widget.setValue( select_employee['first_name'] + ' ' + select_employee['last_name'] );
						}
						break;
					case 'total_time':
						if ( this.absence_model ) {
							var result = Global.secondToHHMMSS( this.current_edit_record[key] );
							widget.setValue( result );
						}
						break;
					case 'station_id':
						if ( this.current_edit_record[key] ) {
							this.setStation();
						} else {
							widget.setValue( 'N/A' );
							widget.css( 'cursor', 'default' );
						}
						break;
					case 'punch_image':
						var station_form_item = this.edit_view_form_item_dic['station_id'];
						if ( this.current_edit_record['has_image'] ) {
							this.attachElement( 'punch_image' );
							widget.setValue( ServiceCaller.fileDownloadURL + '?object_type=punch_image&parent_id=' + this.current_edit_record.user_id + '&object_id=' + this.current_edit_record.id );

						} else {
							this.detachElement( 'punch_image' );
						}
						break;
					case 'job_id':
						if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
							args = {};
							args.filter_data = {status_id: 10, user_id: this.current_edit_record.user_id};
							widget.setDefaultArgs( args );
							widget.setValue( this.current_edit_record[key] );
						}
						break;
					case 'job_item_id':
						if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) ) {
							args = {};
							args.filter_data = {status_id: 10, job_id: this.current_edit_record.job_id};
							widget.setDefaultArgs( args );
							widget.setValue( this.current_edit_record[key] );
						}
						break;
					case 'job_quick_search':
						break;
					case 'job_item_quick_search':
						break;
					case 'latitude':
					case 'longitude':
					case 'position_accuracy':
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		if ( this.absence_model ) {

			if ( this.current_edit_record.id ) {
				this.pre_total_time = this.current_edit_record.total_time;
			} else {
				this.pre_total_time = 0;
			}
		} else {
			this.pre_total_time = 0;
		}

		var actual_time_value;
		if ( this.current_edit_record.id ) {

			if ( this.current_edit_record.actual_time_stamp ) {
				actual_time_value = $.i18n._( 'Actual Time' ) + ': ' + this.current_edit_record.actual_time_stamp;
			} else {
				actual_time_value = 'N/A';
			}

		}

		this.setLocationValue();

		this.actual_time_label.text( actual_time_value );

		this.onAvailableBalanceChange();

		this.setEditMenu(); //To make sure save & continue icon disabled correct when multi dates

		this.setEditViewDataDone();
	},

	setLocationValue: function() {
		if ( LocalCacheData.getCurrentCompany().product_edition_id > 10 ) {
			this.edit_view_ui_dic['latitude'].setValue(this.current_edit_record.latitude);
			this.edit_view_ui_dic['longitude'].setValue(this.current_edit_record.longitude);
			this.edit_view_ui_dic['position_accuracy'].setValue(this.current_edit_record.position_accuracy ? this.current_edit_record.position_accuracy : 0);

			if (!this.current_edit_record.latitude && !this.is_mass_editing) {
				this.location_wrapper.hide();
			} else {
				this.location_wrapper.show();
			}
		}

	},

	onAvailableBalanceChange: function() {
		if ( this.current_edit_record.hasOwnProperty( 'src_object_id' ) &&
			this.current_edit_record.src_object_id && !this.is_mass_editing ) {
			this.getAvailableBalance();
		} else {
			this.detachElement( 'available_balance' );
		}
		this.editFieldResize();
	},

	getAvailableBalance: function() {

		var $this = this;
		var result_data;

		//On first run, set previous_absence_policy_id.
		if ( this.previous_absence_policy_id == false ) {
			this.previous_absence_policy_id = this.current_edit_record.src_object_id;
		}

		if ( this.absence_model ) {

			var last_date_stamp = this.current_edit_record.date_stamp;
			var total_time = this.current_edit_record.total_time;

			if ( this.is_mass_adding ) {

				last_date_stamp = this.current_edit_record.punch_dates;
				//get dates from date ranger
				if ( last_date_stamp && last_date_stamp.indexOf( ' - ' ) > 0 ||
					$.type( last_date_stamp ) === 'array' ) {

					if ( last_date_stamp.indexOf( ' - ' ) > 0 ) {
						last_date_stamp = this.parserDatesRange( last_date_stamp );
					}

					if ( last_date_stamp.length > 0 ) {
						total_time = total_time * last_date_stamp.length;
						last_date_stamp = last_date_stamp[last_date_stamp.length - 1];
					}

				}
			}

			this.api_absence_policy.getProjectedAbsencePolicyBalance(
				this.current_edit_record.src_object_id,
				this.getSelectEmployee(),
				last_date_stamp,
				total_time,
				this.pre_total_time,
				this.previous_absence_policy_id,
				{
					onResult: function( result ) {
						$this.getBalanceHandler( result, last_date_stamp );
					}
				}
			);

		}
	},

	/*
	 1. Job is switched.
	 2. If a Task is already selected (and its not Task=0), keep it selected *if its available* in the newly populated Task list.
	 3. If the task selected is *not* available in the Task list, or the selected Task=0, then check the default_item_id field from the Job and if its *not* 0 also, select that Task by default.
	 */
	setJobItemValueWhenJobChanged: function( job ) {
		//Error: Uncaught TypeError: Cannot set property 'job_item_id' of null in /interface/html5/#!m=TimeSheet&date=20150126&user_id=54286 line 6785
		if ( !this.current_edit_record ) {
			return;
		}
		var $this = this;
		var job_item_widget = $this.edit_view_ui_dic['job_item_id'];
		var current_job_item_id = job_item_widget.getValue();
		job_item_widget.setSourceData( null );
		job_item_widget.setCheckBox( true );
		this.edit_view_ui_dic['job_item_quick_search'].setCheckBox( true );
		var args = {};
		args.filter_data = {status_id: 10, job_id: $this.current_edit_record.job_id};
		$this.edit_view_ui_dic['job_item_id'].setDefaultArgs( args );

		if ( current_job_item_id ) {

			var new_arg = Global.clone( args );

			new_arg.filter_data.id = current_job_item_id;
			new_arg.filter_columns = $this.edit_view_ui_dic['job_item_id'].getColumnFilter();
			$this.job_item_api.getJobItem( new_arg, {
				onResult: function( task_result ) {
					//Error: Uncaught TypeError: Cannot set property 'job_item_id' of null in /interface/html5/#!m=TimeSheet&date=20150126&user_id=54286 line 6785
					if ( !$this.current_edit_record ) {
						return;
					}
					var data = task_result.getResult();

					if ( data.length > 0 ) {
						job_item_widget.setValue( current_job_item_id );
						$this.current_edit_record.job_item_id = current_job_item_id;
					} else {
						setDefaultData();
					}

				}
			} )

		} else {
			setDefaultData();
		}

		function setDefaultData() {
			if ( $this.current_edit_record.job_id ) {
				job_item_widget.setValue( job.default_item_id );
				$this.current_edit_record.job_item_id = job.default_item_id;

				if ( job.default_item_id === false || job.default_item_id === 0 ) {
					$this.edit_view_ui_dic.job_item_quick_search.setValue( '' );
				}

			} else {
				job_item_widget.setValue( '' );
				$this.current_edit_record.job_item_id = false;
				$this.edit_view_ui_dic.job_item_quick_search.setValue( '' );

			}
		}
	},

	onJobQuickSearch: function( key, value ) {
		var args = {};
		var $this = this;

		//Error: Uncaught TypeError: Cannot read property 'setValue' of undefined in /interface/html5/#!m=TimeSheet&date=20141222&user_id=13566 line 6686
		if ( !$this.edit_view_ui_dic || !$this.edit_view_ui_dic['job_id'] ) {
			return;
		}

		if ( key === 'job_quick_search' ) {

			args.filter_data = {manual_id: value, user_id: this.current_edit_record.user_id, status_id: "10"};

			this.job_api.getJob( args, {
				onResult: function( result ) {

					//Error: Uncaught TypeError: Cannot read property 'setValue' of undefined in /interface/html5/#!m=TimeSheet&date=20141222&user_id=13566 line 6686
					if ( !$this.edit_view_ui_dic || !$this.edit_view_ui_dic['job_id'] ) {
						return;
					}

					var result_data = result.getResult();

					if ( result_data.length > 0 ) {
						$this.edit_view_ui_dic['job_id'].setValue( result_data[0].id );
						$this.current_edit_record.job_id = result_data[0].id;
						$this.setJobItemValueWhenJobChanged( result_data[0], 'job_item_id', {status_id: 10, job_id: $this.current_edit_record.job_id} );
					} else {
						$this.edit_view_ui_dic['job_id'].setValue( '' );
						$this.current_edit_record.job_id = false;
						$this.setJobItemValueWhenJobChanged( false, 'job_item_id', {status_id: 10, job_id: $this.current_edit_record.job_id} );
					}

				}
			} );
			$this.edit_view_ui_dic['job_quick_search'].setCheckBox( true );
			$this.edit_view_ui_dic['job_id'].setCheckBox( true );
		} else if ( key === 'job_item_quick_search' ) {

			args.filter_data = {manual_id: value, job_id: this.current_edit_record.job_id, status_id: "10"};

			this.job_item_api.getJobItem( args, {
				onResult: function( result ) {

					//Error: Uncaught TypeError: Cannot read property 'setValue' of undefined in /interface/html5/#!m=TimeSheet&date=20141222&user_id=13566 line 6686
					if ( !$this.edit_view_ui_dic || !$this.edit_view_ui_dic['job_item_id'] ) {
						return;
					}

					var result_data = result.getResult();
					if ( result_data.length > 0 ) {
						$this.edit_view_ui_dic['job_item_id'].setValue( result_data[0].id );
						$this.current_edit_record.job_item_id = result_data[0].id;

					} else {
						$this.edit_view_ui_dic['job_item_id'].setValue( '' );
						$this.current_edit_record.job_item_id = false;
					}

				}
			} );
			this.edit_view_ui_dic['job_item_quick_search'].setCheckBox( true );
			this.edit_view_ui_dic['job_item_id'].setCheckBox( true );
		}

	},

	setStation: function() {

		var $this = this;
		var arg = {filter_data: {id: this.current_edit_record.station_id}};

		this.api_station.getStation( arg, {
			onResult: function( result ) {

				$this.station = result.getResult()[0];

				var widget = $this.edit_view_ui_dic['station_id'];
				if ( $this.station ) {
					//Error: Uncaught TypeError: Cannot read property 'setValue' of undefined in /interface/html5/#!m=TimeSheet&date=20140925 line 6017
					if ( widget ) {
						widget.setValue( $this.station.type + '-' + $this.station.description );
					}

				} else {
					if ( widget ) {
						widget.setValue( 'N/A' );
					}

					return;
				}

				if ( PermissionManager.validate( 'station', 'view' ) ||
					(PermissionManager.validate( 'station', 'view_child' ) && $this.station.is_child ) ||
					(PermissionManager.validate( 'station', 'view_own' ) && $this.station.is_owner ) ) {
					$this.show_station_ui = true;
				} else {
					$this.show_station_ui = false;
				}

				// Error: TypeError: form_item_input is undefined in interface/html5/framework/jquery.min.js?v=9.0.1-20151022-091549 line 2 > eval line 7119
				if ( $this.show_station_ui && widget ) {
					widget.css( 'cursor', 'pointer' );
				}

			}
		} );
	},

	getSelectedItems: function() {
		var selected_item = null;
		if ( this.edit_view ) {
			return [this.current_edit_record];
		} else {

			if ( this.select_punches_array.length > 0 ) {
				return this.select_punches_array;
			}
		}

		return [];
	},

	getSelectedItem: function() {

		var selected_item = null;
		if ( this.edit_view ) {
			selected_item = this.current_edit_record;
		} else {

			if ( this.select_punches_array.length > 0 ) {
				selected_item = this.select_punches_array[0];
			} else {
				selected_item = null;
			}
		}

		return Global.clone( selected_item );
	},

	addPermissionValidate: function( p_id ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( PermissionManager.validate( p_id, 'add' ) && this.editPermissionValidate( p_id ) ) {
			return true;
		}

		return false;

	},

	setDefaultMenu: function( doNotSetFocus ) {
		//Error: Uncaught TypeError: Cannot read property 'length' of undefined in /interface/html5/#!m=Employee&a=edit&id=42411&tab=Wage line 282
		if ( !this.context_menu_array ) {
			return;
		}

		if ( !Global.isSet( doNotSetFocus ) || !doNotSetFocus ) {
			this.selectContextMenu();
		}

		var len = this.context_menu_array.length;

		var grid_selected_length = this.select_punches_array.length;

		var p_id = this.absence_model ? 'absence' : 'punch';

		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			context_btn.removeClass( 'disable-image' );
			context_btn.removeClass( 'invisible-image' );

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setDefaultMenuAddIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.add_absence:
					this.setDefaultMenuAddIcon( context_btn, grid_selected_length, 'absence' );
					break;
				case ContextMenuIconName.edit:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.view:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.mass_edit:
					this.setDefaultMenuMassEditIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.copy:
					this.setDefaultMenuCopyIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.drag_copy:
					this.setDefaultMenuDragCopyIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.delete_icon:
					this.setDefaultMenuDeleteIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setDefaultMenuDeleteAndNextIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.save:
					this.setDefaultMenuSaveIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.save_and_next:
					this.setDefaultMenuSaveAndNextIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.save_and_continue:
					this.setDefaultMenuSaveAndContinueIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.save_and_new:
					this.setDefaultMenuSaveAndAddIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.save_and_copy:
					this.setDefaultMenuSaveAndCopyIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.copy_as_new:
					this.setDefaultMenuCopyAsNewIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.move:
					if ( !this.movePermissionValidate( p_id ) ) {
						context_btn.addClass( 'invisible-image' );
					}
					if (this.getPunchMode() == 'manual') {
						context_btn.addClass('disable-image');
					} else {
						context_btn.removeClass('disable-image');
					}
					break;
				case ContextMenuIconName.cancel:
					this.setDefaultMenuCancelIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.edit_pay_period:
					this.setDefaultMenuEditPayPeriodIcon( context_btn, grid_selected_length, p_id );
					break;
				case ContextMenuIconName.map:
					this.setDefaultMenuMapIcon( context_btn, grid_selected_length, p_id );

					break;
				case ContextMenuIconName.print:
					this.setDefaultMenuPrintIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.edit_employee:
					this.setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.pay_stub:
					this.setDefaultMenuPayStubIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.re_calculate_timesheet:
					this.setDefaultMenuReCalculateTimesheet( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.generate_pay_stub:
					this.setDefaultMenuGeneratePayStubIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.schedule:
					this.setDefaultMenuScheduleIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.accumulated_time:
					this.setDefaultMenuAccumulatedTimeIcon( context_btn );
					break;
				case 'AddRequest':
					this.setAddRequestIcon(context_btn);
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},

	setEditMenuDragCopyIcon: function( context_btn, grid_selected_length, pId ) {
		if (!this.copyPermissionValidate(pId) || this.edit_only_mode) {
			context_btn.addClass('invisible-image');
		}

		if (this.getPunchMode() == 'manual') {
			context_btn.addClass('disable-image');
		} else {
			context_btn.removeClass('disable-image');
		}

	},

	setDefaultMenuDragCopyIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.copyPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if (this.getPunchMode() == 'manual') {
			context_btn.addClass('disable-image');
		} else {
			context_btn.removeClass('disable-image');
		}
	},

	setDefaultMenuScheduleIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !PermissionManager.checkTopLevelPermission( 'Schedule' ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

	},

	reCalculateEditPermissionValidate: function() {

		var p_id = this.permission_id;

		if ( PermissionManager.validate( p_id, 'edit' ) || this.ownerOrChildPermissionValidate( p_id, 'edit_child' ) ) {

			return true;
		}
	},

	setDefaultMenuReCalculateTimesheet: function( context_btn, grid_selected_length, pId ) {

		if ( !this.reCalculateEditPermissionValidate() ) {
			context_btn.addClass( 'invisible-image' );
		}

	},

	setDefaultMenuGeneratePayStubIcon: function( context_btn, grid_selected_length, pId ) {

		if ( !PermissionManager.checkTopLevelPermission( 'PayPeriodSchedule' ) ) {
			context_btn.addClass( 'invisible-image' );
		}

	},

	setDefaultMenuPayStubIcon: function( context_btn, grid_selected_length, pId ) {

		if ( !PermissionManager.checkTopLevelPermission( 'PayStub' ) ) {
			context_btn.addClass( 'invisible-image' );
		}

	},

	setDefaultMenuEditPayPeriodIcon: function( context_btn, grid_selected_length, pId ) {

		if ( !this.editPermissionValidate( 'pay_period_schedule' ) ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( !this.getPayPeriod() ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuAccumulatedTimeIcon: function( context_btn, pId ) {

		if ( !PermissionManager.checkTopLevelPermission( 'AccumulatedTime' ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}
	},

	setDefaultMenuEditEmployeeIcon: function( context_btn, grid_selected_length, pId ) {

		if ( !this.editChildPermissionValidate( 'user' ) ) {
			context_btn.addClass( 'invisible-image' );
		}

	},

	editOwnerOrChildPermissionValidate: function( p_id, selected_item ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( !selected_item ) {
			selected_item = this.getSelectEmployee( true );
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if (
			PermissionManager.validate( p_id, 'edit' ) ||
			(selected_item && selected_item.is_owner && PermissionManager.validate( p_id, 'edit_own' )) ||
			(selected_item && selected_item.is_child && PermissionManager.validate( p_id, 'edit_child' )) ) {

			return true;

		}

		return false;

	},

	viewOwnerOrChildPermissionValidate: function( p_id, selected_item ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( !selected_item ) {
			selected_item = this.getSelectEmployee( true );
		}

		if (
			PermissionManager.validate( p_id, 'view' ) ||
			(selected_item && selected_item.is_owner && PermissionManager.validate( p_id, 'view_own' )) ||
			(selected_item && selected_item.is_child && PermissionManager.validate( p_id, 'view_child' )) ) {

			return true;

		}

		return false;

	},

	deleteOwnerOrChildPermissionValidate: function( p_id, selected_item ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( !selected_item ) {
			selected_item = this.getSelectEmployee( true );
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if (
			PermissionManager.validate( p_id, 'delete' ) ||
			(selected_item && selected_item.is_owner && PermissionManager.validate( p_id, 'delete_own' )) ||
			(selected_item && selected_item.is_child && PermissionManager.validate( p_id, 'delete_child' )) ) {

			return true;

		}

		return false;

	},

	editChildPermissionValidate: function( p_id, selected_item ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( !Global.isSet( selected_item ) ) {
			selected_item = this.getSelectEmployee( true );
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( !PermissionManager.validate( p_id, 'enabled' ) ) {
			return false;
		}

		if ( PermissionManager.validate( p_id, 'edit' ) ||
			this.ownerOrChildPermissionValidate( p_id, 'edit_child', selected_item ) ) {

			return true;
		}

		return false;

	},

	onReportMenuClick: function( id ) {
		this.onNavigationClick( id );
	},

	setDefaultMenuPrintIcon: function( context_btn, grid_selected_length, pId ) {

		context_btn.removeClass( 'disable-image' );
	},

	setEditMenuMapIcon: function( context_btn, pId ) {
		if ( this.absence_model ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuSaveAndAddIcon: function( context_btn, pId ) {
		this.saveAndNewValidate( context_btn, pId );

		if ( this.is_viewing || this.is_mass_editing ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuMapIcon: function( context_btn, grid_selected_length, pId ) {
		this._super("setDefaultMenuMapIcon", context_btn);

		if (context_btn.hasClass('disable-image') == false) {
			if (this.absence_model || this.getPunchMode() == 'manual') {
				context_btn.addClass('disable-image');
			}
		}
	},

	setEditMenuSaveAndNextIcon: function( context_btn, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) || this.is_viewing || this.is_mass_adding ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuSaveAndCopyIcon: function( context_btn, pId ) {
		this.saveAndNewValidate( context_btn, pId );

		if ( this.is_viewing || this.is_mass_editing ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuCopyAndAddIcon: function( context_btn, pId ) {
		if ( !this.addPermissionValidate( pId ) ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) || this.is_viewing || this.is_mass_adding ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenu: function() {
		this.selectContextMenu();
		var len = this.context_menu_array.length;

		var p_id = this.absence_model ? 'absence' : 'punch';

		for ( var i = 0; i < len; i++ ) {
			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			context_btn.removeClass( 'disable-image' );

			if ( this.is_mass_editing ) {
				switch ( id ) {
					case ContextMenuIconName.save:
						this.setEditMenuSaveIcon( context_btn );
						break;
					case ContextMenuIconName.cancel:
						break;
					default:
						context_btn.addClass( 'disable-image' );
						break;
				}

				continue;
			}

			//no need reset invisible-image, inhert from default menu
//			context_btn.removeClass( 'invisible-image' );

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setEditMenuAddIcon( context_btn );
					break;
				case ContextMenuIconName.add_absence:
					this.setEditMenuAddIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.edit:
					this.setEditMenuEditIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.view:
					this.setEditMenuViewIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.mass_edit:
					this.setEditMenuMassEditIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.copy:
					this.setEditMenuCopyIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.drag_copy:
					this.setEditMenuDragCopyIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.move:
					if ( !this.movePermissionValidate( p_id ) ) {
						context_btn.addClass( 'invisible-image' );
					}
					break;
				case ContextMenuIconName.delete_icon:
					this.setEditMenuDeleteIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setEditMenuDeleteAndNextIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.save:
					this.setEditMenuSaveIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.save_and_continue:
					this.setEditMenuSaveAndContinueIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.save_and_new:
					this.setEditMenuSaveAndAddIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.save_and_next:
					this.setEditMenuSaveAndNextIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.save_and_copy:
					this.setEditMenuSaveAndCopyIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.copy_as_new:
					this.setEditMenuCopyAndAddIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.map:
					this.setEditMenuMapIcon( context_btn, p_id );
					break;
				case ContextMenuIconName.cancel:
					break;
				case ContextMenuIconName.accumulated_time:
					this.setDefaultMenuAccumulatedTimeIcon( context_btn );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn);
					break;
				case 'AddRequest':
					this.setAddRequestIcon(context_btn);
					break;
			}

		}

		this.setContextMenuGroupVisibility();

	},


	enableAddRequestButton: function() {
		var grid_selected_id_array = this.select_cells_Array;
		var grid_selected_length = grid_selected_id_array.length;

		if (grid_selected_length == 1) {
			return true;
		}
		return false;
	},

	cleanWhenUnloadView: function( callBack ) {

		$( '#timesheet_view_container' ).remove();
		this._super( 'cleanWhenUnloadView', callBack );

	},

	setAddRequestIcon: function( context_btn, grid_selected_length, pId ) {
		if ( LocalCacheData.getCurrentCompany().product_edition_id <= 10 || !this.addPermissionValidate( 'request' ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( this.enableAddRequestButton() === true ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

} );

TimeSheetViewController.PUNCH_ROW = 1;
TimeSheetViewController.EXCEPTION_ROW = 2;
TimeSheetViewController.REQUEST_ROW = 3;
TimeSheetViewController.TOTAL_ROW = 4;
TimeSheetViewController.REGULAR_ROW = 5;
TimeSheetViewController.ABSENCE_ROW = 6;
TimeSheetViewController.ACCUMULATED_TIME_ROW = 7;
TimeSheetViewController.PREMIUM_ROW = 8;
