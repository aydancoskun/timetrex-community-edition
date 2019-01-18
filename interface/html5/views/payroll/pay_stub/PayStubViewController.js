PayStubViewController = BaseViewController.extend( {
	el: '#pay_stub_view_container',

	_required_files: ['APIPayStub', 'APIPayStubEntry', 'APIPayStubEntryAccountLink', 'APIUserGroup', 'APIPayPeriod', 'APIPayStubTransaction', 'APIRemittanceDestinationAccount', 'APIUserTitle', 'APICurrency', 'APIBranch', 'APIDepartment', 'APIPayStubEntryAccount'],

	filtered_status_array: null,
	user_status_array: null,
	user_group_array: null,
	user_destination_account_array: null,
	currency_array: null,
	type_array: null,

	country_array: null,
	province_array: null,

	e_province_array: null,

	user_api: null,
	user_group_api: null,
	company_api: null,

	pay_stub_entry_api: null,

	include_pay_stub_accounts: true,
	transaction_status_array: false,

	net_pay_amount: false,

	pseal_link: false,

	original_status_id: 10,

	init: function( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'PayStubEditView.html';
		this.permission_id = 'pay_stub';
		this.viewId = 'PayStub';
		this.script_name = 'PayStubView';
		this.table_name_key = 'pay_stub';
		this.context_menu_name = $.i18n._( 'Pay Stub' );
		this.navigation_label = $.i18n._( 'Pay Stubs' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIPayStub' ))();
		this.user_api = new (APIFactory.getAPIClass( 'APIUser' ))();
		this.pay_stub_entry_api = new (APIFactory.getAPIClass( 'APIPayStubEntry' ))();
		this.pay_stub_entry_account_link_api = new (APIFactory.getAPIClass( 'APIPayStubEntryAccountLink' ))();
		this.user_group_api = new (APIFactory.getAPIClass( 'APIUserGroup' ))();
		this.company_api = new (APIFactory.getAPIClass( 'APICompany' ))();
		this.pay_period_api = new (APIFactory.getAPIClass( 'APIPayPeriod' ))();
		this.pay_stub_transaction_api = new (APIFactory.getAPIClass( 'APIPayStubTransaction' ))();
		this.remittance_destination_account_api = new (APIFactory.getAPIClass( 'APIRemittanceDestinationAccount' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true; //Hide some context menus
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_new] = true; //Hide some context menus
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_copy] = true; //Hide some context menus

	 	var $this = this;
		$.when(
			this.preloadTransactionOptions(new $.Deferred()),
			this.preloadPayStubAccountLinks(new $.Deferred())
		).done( function() {
			$this.completeInit();
		});

	},

	isEditMode: function() {
		if ( this.is_add || ( this.is_edit && this.original_status_id == 25 ) ) {
			return true;
		}
		return false;
	},

	preloadTransactionOptions: function(dfd) {
		var $this = this;
		this.pay_stub_transaction_api.getOptions( 'status', false, false, {onResult: function( result ) {
			$this.transaction_status_array = result.getResult();
			dfd.resolve(true);
		}});

		return dfd.promise();
	},

	preloadPayStubAccountLinks:function(dfd) {
		var $this = this;
		this.pay_stub_entry_account_link_api.getPayStubEntryAccountLink( '', false, false, {onResult: function( result ) {
			var data = result.getResult()[0];
			if ( data ) {
				$this.pseal_link = {
					total_gross_entry_account_id: false,
					total_deductions_entry_account_id: false,
					net_pay_entry_account_id: false,
					contributions_entry_account_id: false,
				};

				$this.pseal_link.total_gross_entry_account_id = data.total_gross;
				$this.pseal_link.total_deductions_entry_account_id = data.total_employee_deduction;
				$this.pseal_link.net_pay_entry_account_id = data.total_net_pay;
				$this.pseal_link.contributions_entry_account_id = data.total_employer_deduction;
			}
			dfd.resolve(true);
		}});

		return dfd.promise();
	},

	completeInit: function() {
		this.initPermission();
		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary('PayStub');
	},

	initPermission: function() {

		this._super( 'initPermission' );

		if ( PermissionManager.validate( this.permission_id, 'view' ) || PermissionManager.validate( this.permission_id, 'view_child' ) ) {
			this.show_search_tab = true;
		} else {
			this.show_search_tab = false;
		}
		return {};

	},

	initOptions: function() {
		var $this = this;

		this.initDropDownOption( 'filtered_status', 'status_id' );
		this.initDropDownOption( 'type', 'type_id' );
		this.initDropDownOption( 'status', 'user_status_id', this.user_api );
		this.initDropDownOption( 'country', 'country', this.company_api );
		var result = {};
		for ( var i = 1; i <= 128; i++ ) {
			result[i] = i;
		}
		$this.basic_search_field_ui_dic['run_id'].setSourceData( Global.buildRecordArray(result) );
		$this.adv_search_field_ui_dic['run_id'].setSourceData( Global.buildRecordArray(result) );
		this.user_group_api.getUserGroup( '', false, false, {onResult: function( res ) {
			res = res.getResult();

			res = Global.buildTreeRecord( res );
			$this.user_group_array = res;

			if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
				$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
				$this.adv_search_field_ui_dic['group_id'].setSourceData( res );
			}

		}} );

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

		var navigation_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Navigation' ),
			id: this.viewId + 'navigation',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var pay_stubs_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Pay Stubs' ),
			id: this.script_name + 'Pay Stubs',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var other_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Other' ),
			id: this.viewId + 'other',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var add = new RibbonSubMenu( {
			label: $.i18n._( 'New' ),
			id: ContextMenuIconName.add,
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

		var timesheet = new RibbonSubMenu( {
			label: $.i18n._( 'TimeSheet' ),
			id: ContextMenuIconName.timesheet,
			group: navigation_group,
			icon: Icons.timesheet,
			permission_result: true,
			permission: null
		} );

		var schedule = new RibbonSubMenu( {
			label: $.i18n._( 'Schedule' ),
			id: ContextMenuIconName.schedule,
			group: navigation_group,
			icon: Icons.schedule,
			permission_result: true,
			permission: null
		} );

		var pay_stub_amendments = new RibbonSubMenu( {
			label: $.i18n._( 'Pay Stub<br>Amendments' ),
			id: ContextMenuIconName.pay_stub_amendment,
			group: navigation_group,
			icon: Icons.pay_stub_amendment,
			permission_result: true,
			permission: null
		} );

		var pay_stub_transactions = new RibbonSubMenu( {
			label: $.i18n._( 'Pay Stub<br>Transactions' ),
			id: ContextMenuIconName.pay_stub_transaction,
			group: navigation_group,
			icon: Icons.pay_stub_transaction,
			items: [],
			permission_result: true,
			permission: true,
			sort_order: null,
		} );

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

		var employee_pay_stubs = new RibbonSubMenu( {
			label: $.i18n._( 'Employee Pay<br>Stubs' ),
			id: ContextMenuIconName.employee_pay_stubs,
			group: pay_stubs_group,
			icon: Icons.pay_stubs,
			permission_result: true,
			permission: null
		} );

		var employer_pay_stubs = new RibbonSubMenu( {
			label: $.i18n._( 'Employer Pay<br>Stubs' ),
			id: ContextMenuIconName.employer_pay_stubs,
			group: pay_stubs_group,
			icon: Icons.pay_stubs,
			permission_result: true,
			permission: null
		} );

		var generate_pay_stub = new RibbonSubMenu( {
			label: $.i18n._( 'Generate<br>Pay Stub' ),
			id: ContextMenuIconName.generate_pay_stub,
			group: other_group,
			icon: Icons.process_payroll,
			permission_result: true,
			permission: null
		} );


		var process_transactions = new RibbonSubMenu( {
			label: $.i18n._( 'Process<br>Transactions' ),
			id: ContextMenuIconName.direct_deposit,
			group: other_group,
			icon: 'direct_deposit-35x35.png',
			items: [],
			permission_result: true,
			permission: true
		} );


		var export_csv = new RibbonSubMenu( {
			label: $.i18n._( 'Export' ),
			id: ContextMenuIconName.export_excel,
			group: other_group,
			icon: Icons.export_excel,
			items: [],
			permission_result: true,
			permission: true,
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
			var context_btn = this.context_menu_array[i];
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			context_btn.removeClass( 'invisible-image' );
			context_btn.removeClass( 'disable-image' );

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setDefaultMenuAddIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.view:
					//View icon should be displayed separate from Employee Pay Stub/Employer Pay Stub icons.
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.edit:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.mass_edit:
					this.setDefaultMenuMassEditIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.delete_icon:
					this.setDefaultMenuDeleteIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setDefaultMenuDeleteAndNextIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save:
					this.setDefaultMenuSaveIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.copy_as_new:
					this.setDefaultMenuCopyAsNewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_next:
					this.setDefaultMenuSaveAndNextIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_continue:
					this.setDefaultMenuSaveAndContinueIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.cancel:
					this.setDefaultMenuCancelIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.timesheet:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'punch' );
					break;
				case ContextMenuIconName.schedule:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'schedule' );
					break;
				case ContextMenuIconName.pay_stub_amendment:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'pay_stub_amendment' );
					break;
				case ContextMenuIconName.pay_stub_transaction:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'pay_stub_transaction' );
					break;
				case ContextMenuIconName.edit_employee:
					this.setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length, 'user' );
					break;
				case ContextMenuIconName.edit_pay_period:
					this.setDefaultMenuEditPayPeriodIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.employee_pay_stubs:
				case ContextMenuIconName.employer_pay_stubs:
				case ContextMenuIconName.print_checks:
				case ContextMenuIconName.direct_deposit:
					this.setDefaultMenuReportRelatedIcons( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.generate_pay_stub:
					this.setDefaultMenuGeneratePayStubIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn, grid_selected_length );
					break;
			}
		}

		this.setContextMenuGroupVisibility();
	},

	setDefaultMenuEditPayPeriodIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !this.editPermissionValidate( 'pay_period_schedule' ) ) {
			context_btn.addClass( 'invisible-image' );
		}
		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuEditEmployeeIcon: function( context_btn, grid_selected_length ) {
		if ( !this.editChildPermissionValidate( 'user' ) ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuViewIcon: function( context_btn, grid_selected_length, pId ) {
		if ( pId === 'punch' || pId === 'schedule' || pId === 'pay_stub_amendment' ) {
			this._super('setDefaultMenuViewIcon', context_btn, grid_selected_length, pId);
		} else if ( pId === 'pay_stub_transaction' ) {
			if ( PermissionManager.validate( 'pay_stub', 'enabled' )
				&& ( PermissionManager.validate( 'pay_stub', 'view' ) || PermissionManager.validate( 'pay_stub', 'view_child' ) ) ) {
				context_btn.removeClass( 'invisible-image' );
			} else {
				context_btn.addClass( 'invisible-image' );
			}
		} else {
			if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
				context_btn.addClass( 'invisible-image' );
			}

			if ( grid_selected_length > 0 && this.viewOwnerOrChildPermissionValidate() ) {
				context_btn.removeClass( 'disable-image' );
			} else {
				context_btn.addClass( 'disable-image' );
			}
		}
	},

	setEditMenu: function() {
		this.selectContextMenu();
		var len = this.context_menu_array.length;
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

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setEditMenuAddIcon( context_btn );
					break;
				case ContextMenuIconName.edit:
					this.setEditMenuEditIcon( context_btn );
					break;
				case ContextMenuIconName.mass_edit:
					this.setEditMenuMassEditIcon( context_btn );
					break;
				case ContextMenuIconName.copy:
					this.setEditMenuCopyIcon( context_btn );
					break;
				case ContextMenuIconName.delete_icon:
					this.setEditMenuDeleteIcon( context_btn );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setEditMenuDeleteAndNextIcon( context_btn );
					break;
				case ContextMenuIconName.save:
					this.setEditMenuSaveIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_continue:
					this.setEditMenuSaveAndContinueIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_new:
					this.setEditMenuSaveAndAddIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_next:
					this.setEditMenuSaveAndNextIcon( context_btn );
					break;
				case ContextMenuIconName.save_and_copy:
					this.setEditMenuSaveAndCopyIcon( context_btn );
					break;
				case ContextMenuIconName.copy_as_new:
					this.setEditMenuCopyAndAddIcon( context_btn );
					break;
				case ContextMenuIconName.cancel:
					break;
				case ContextMenuIconName.import_icon:
					this.setEditMenuImportIcon( context_btn );
					break;
				case ContextMenuIconName.timesheet:
					this.setEditMenuViewIcon( context_btn, 'punch' );
					break;
				case ContextMenuIconName.schedule:
					this.setEditMenuViewIcon( context_btn, 'schedule' );
					break;
				case ContextMenuIconName.pay_stub_amendment:
					this.setEditMenuViewIcon( context_btn, 'pay_stub_amendment' );
					break;
				case ContextMenuIconName.edit_employee:
					this.setEditMenuViewIcon( context_btn, 'user' );
					break;
				case ContextMenuIconName.edit_pay_period:
					this.setEditMenuViewIcon( context_btn, 'pay_period_schedule' );
					break;
				case ContextMenuIconName.view:
				case ContextMenuIconName.employer_pay_stubs:
				case ContextMenuIconName.employee_pay_stubs:
				case ContextMenuIconName.print_checks:
				case ContextMenuIconName.direct_deposit:
					//this.setEditMenuReportRelatedIcons( context_btn );
					this.setEditMenuEditIcon( context_btn );
					break;
				case ContextMenuIconName.generate_pay_stub:
					this.setEditMenuGeneratePayStubIcon( context_btn );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn);
					break;
			}
		}

		this.setContextMenuGroupVisibility();

	},

	setEditMenuViewIcon: function( context_btn, pId ) {
		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	payStubReportIconsValidate: function() {
		if ( !PermissionManager.validate( 'pay_stub', 'enabled' ) ) {
			return false;
		}

		var selected_item = this.getSelectedItem();

		if ( PermissionManager.validate( 'pay_stub', 'view' ) || this.ownerOrChildPermissionValidate( 'pay_stub', 'view_child', selected_item ) ) {
			return true;
		}

		return false;
	},

	setDefaultMenuReportRelatedIcons: function( context_btn, grid_selected_length, pId ) {
		if ( !this.payStubReportIconsValidate() ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( grid_selected_length > 0 && this.viewOwnerOrChildPermissionValidate() ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuReportRelatedIcons: function( context_btn, grid_selected_length, pId ) {
		if ( !this.payStubReportIconsValidate() ) {
			context_btn.addClass( 'invisible-image' );
		}

		if ( this.current_edit_record.id && this.viewOwnerOrChildPermissionValidate() ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuGeneratePayStubIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !PermissionManager.checkTopLevelPermission('GeneratePayStubs') ) {
			context_btn.addClass( 'invisible-image' );
		}
	},

	endOfPayValidate: function() {
		if ( PermissionManager.validate( 'pay_period_schedule', 'enabled' ) && PermissionManager.validate( 'pay_period_schedule', 'view' ) ) {
			return true;
		}

		return false;
	},

	setDefaultMenuGeneratePayStubIcon: function( context_btn, grid_selected_length, pId ) {
		if ( !PermissionManager.checkTopLevelPermission('GeneratePayStubs') ) {
			context_btn.addClass( 'invisible-image' );
		}
	},

	removeEntryInsideEditorCover: function() {
		if ( this.cover && this.cover.length > 0 ) {
			this.cover.remove();
		}
		this.cover = null;

	},

	setCurrentEditRecordData: function() {
		this.include_pay_stub_accounts = true;
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'country':
						this.setCountryValue(widget, key);
						break;
					case 'status_id':
						if ( this.current_edit_record[key] == 40 || this.current_edit_record[key] == 100 ) {
							this.include_pay_stub_accounts = false;
						}
						widget.setValue( this.current_edit_record[key] );
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
		this._super( 'setEditViewDataDone' );

		if ( this.is_add ) {
			this.edit_view_ui_dic.user_id.setEnabled( true );
		} else {
			this.edit_view_ui_dic.user_id.setEnabled( false );
		}

		if ( !this.is_mass_editing ) {
			this.initInsideEntryEditorData();
		}

	},

	getPayStubTransactionDefaultData: function( callback, index ) {
		this.pay_stub_transaction_api['get' + this.pay_stub_transaction_api.key_name + 'DefaultData']( {onResult: function( result ) {
			var data = [];
			var result_data = result.getResult();
			result_data.id = false;

			data.push( result_data );
			callback( data, index );

		}} );
	},


	getPayStubTransaction: function(callback) {
		var $this = this;
		var args = {};
		args.filter_data = {};
		args.filter_data.pay_stub_id = TTUUID.isUUID(this.current_edit_record.id) ? this.current_edit_record.id : ( TTUUID.isUUID(this.copied_record_id) ? this.copied_record_id : '' );
		this.pay_stub_transaction_api['getPayStubTransaction']( args, true, {onResult: function( res ) {
			if ( !$this.edit_view ) {
				return;
			}
			var result_data = res.getResult();
			if (_.size( result_data ) == 0  ) {
				result_data = [];
			}
			callback( result_data );

		}} );
	},

	initInsideTransactionEditorData: function() {
		var $this = this;

		if ( ( !this.current_edit_record || TTUUID.isUUID( this.current_edit_record.id ) == false ) && !this.copied_record_id ) {
			this.getPayStubTransactionDefaultData(function (data) {
				if ( $this.isEditMode() == true || data.length > 0 ) {
					$this.editor.insideTransactionEditorSetValue(data);
				}
			});
		} else {
			this.getPayStubTransaction( function( data ) {
				if ( $this.isEditMode() == true || data.length > 0 ) {
					$this.editor.insideTransactionEditorSetValue(data);
				}
			} )
		}
	},

	initInsideEntryEditorData: function() {
		var $this = this;
		var args = {};
		args.filter_data = {};

		if ( this.copied_record_id || ( this.current_edit_record && this.current_edit_record.id ) ) {
			args.filter_data.pay_stub_id = TTUUID.isUUID(this.current_edit_record.id) ? this.current_edit_record.id : this.copied_record_id;
			this.pay_stub_entry_api['get' + this.pay_stub_entry_api.key_name](args, {
				onResult: function (res) {
					if (!$this.edit_view) {
						return;
					}
					var data = $this.handlePayStubEntryData(res.getResult());

					$this.editor.setValue(data);
				}
			});
		} else {
			var data = $this.handlePayStubEntryData();
			$this.editor.setValue(data);
		}
	},

	handlePayStubEntryData: function( data ) {

		var total_rows = {};
		var retval = {};
		if (data) {
			for (var n in data) {
				var type_id = data[n].type_id;
				if (type_id == 40) {
					if (data[n].pay_stub_entry_account_id) {
						var newrow = data[n];
						newrow.total_row = true;
						total_rows[data[n].pay_stub_entry_account_id] = newrow[n];
					}
				} else {
					if (typeof retval[type_id] == 'undefined') {
						retval[type_id] = [];
					}
					retval[type_id].push(data[n]);
				}
			}
		}

		//set blanks where there are no records in any given sections
		var type_ids = [10, 20, 30, 50, 80]; //no net pay default row
		for (var t = 0; t < type_ids.length; t++) {
			if (typeof retval[type_ids[t]] == 'undefined' || retval[type_ids[t]].length == 0) {
				retval[type_ids[t]] = [];
				retval[type_ids[t]].push({type_id: type_ids[t]});
			}
		}

		//Fill up the missing total rows.
		var gross_total = {};
		if ( total_rows[this.pseal_link.total_gross_entry_account_id] ) {
			gross_total = total_rows[this.pseal_link.total_gross_entry_account_id];
		}else{
			gross_total = {
				total_row: true,
				type_id:40,
				name: $.i18n._( 'Total Gross' ),
				pay_stub_entry_account_id: this.pseal_link.total_gross_entry_account_id,
			};
		}
		retval[10].push( gross_total );

		var employee_deduction_total = {};
		if ( total_rows[this.pseal_link.total_deductions_entry_account_id] ) {
			employee_deduction_total = total_rows[this.pseal_link.total_deductions_entry_account_id];
		}else{
			employee_deduction_total = {
				total_row: true,
				type_id:40,
				name: $.i18n._( 'Total Deductions' ),
				pay_stub_entry_account_id: this.pseal_link.total_deductions_entry_account_id,
			};
		}
		retval[20].push( employee_deduction_total );

		var net_pay_total = {};
		if ( total_rows[this.pseal_link.net_pay_entry_account_id] ) {
			net_pay_total = total_rows[this.pseal_link.net_pay_entry_account_id];
		}else{
			net_pay_total = {
				//total_row: true,
				type_id:40,
				name: $.i18n._( 'Net Pay' ),
				pay_stub_entry_account_id: this.pseal_link.net_pay_entry_account_id,
			};
		}
		//Because we don't add empty rows to retval[40], and there should only ever be one row in net pay, we will need to initialize retval[40] here.
		retval[40] = net_pay_total ;

		var employer_deduction_total = {};
		if ( total_rows[this.pseal_link.contributions_entry_account_id] ) {
			employer_deduction_total = total_rows[this.pseal_link.contributions_entry_account_id];
		}else{
			employer_deduction_total = {
				total_row: true,
				type_id:40,
				name: $.i18n._( 'Employer Total Contributions' ),
				pay_stub_entry_account_id: this.pseal_link.contributions_entry_account_id,
			};
		}
		retval[30].push( employer_deduction_total );

		return retval;
	},

	insideEntryEditorSetValue: function( val ) {
		var $this = this;
		this.removeAllRows( true );
		this.removeCover();
		function setEarnings( data ) {
			var render = $this.getRender(); //get render, should be a table
			var headerRow = Global.loadWidget( 'views/payroll/pay_stub/PayStubEntryViewInsideEditorFiveColumnHeader.html' );
			var args = {
				col1: $.i18n._( 'Earnings' ),
				col2: $.i18n._( 'Note' ),
				col3: $.i18n._( 'Rate' ),
				col4: $.i18n._( 'Hrs/Units' ),
				col5: $.i18n._( 'Amount' ),
				col6: $.i18n._( 'YTD Amount' )
			};

			var template = _.template(headerRow);
			$( render ).append( template(args) );

			$this.rows_widgets_array.push( true );
			for ( var i = 0; i < _.size( data ); i++ ) {
				if ( Global.isSet( data[i] ) ) {
					var row = data[i];
					row.type_id = 10;
					$this.addRow( row );
				}
			}
			$( render ).append( '<tr><td colspan="8"><br></td></tr>' );
			$this.rows_widgets_array.push( true );
		}

		function setDeductions( data ) {
			var render = $this.getRender(); //get render, should be a table
			var headerRow = Global.loadWidget( 'views/payroll/pay_stub/PayStubEntryViewInsideEditorThreeColumnHeader.html' );
			var args = {
				col1: $.i18n._( 'Deductions' ),
				col2: $.i18n._( 'Note' ),
				col3: $.i18n._( 'Amount' ),
				col4: $.i18n._( 'YTD Amount' )
			};

			var template = _.template(headerRow);
			$( render ).append( template(args) );

			$this.rows_widgets_array.push( true );
			for ( var i = 0; i < _.size( data ); i++ ) {
				if ( Global.isSet( data[i] ) ) {
					var row = data[i];
					row.type_id = 20;
					$this.addRow( row );
				}
			}

			$( render ).append( '<tr><td colspan="8"><br></td></tr>' );
			$this.rows_widgets_array.push( true );

		}

		function setNetPay( data ) {
			var render = $this.getRender(); //get render, should be a table
			if ( data ) {
				// data.type_id = 40;
				$this.addRow(data);
			}

			$( render ).append( '<tr><td colspan="8"><br></td></tr>' );
			$this.rows_widgets_array.push( true );

		}

		function setMiscellaneous( data ) {
			var render = $this.getRender(); //get render, should be a table
			var headerRow = Global.loadWidget( 'views/payroll/pay_stub/PayStubEntryViewInsideEditorThreeColumnHeader.html' );
			var args = {
				col1: $.i18n._( 'Miscellaneous' ),
				col2: $.i18n._( 'Note' ),
				col3: $.i18n._( 'Amount' ),
				col4: $.i18n._( 'YTD Amount' )
			};

			var template = _.template(headerRow);
			$( render ).append( template(args) );

			$this.rows_widgets_array.push( true );
			for ( var i in data ) {
				$this.addRow( data[i] );
			}
			$( render ).append( '<tr><td colspan="8"><br></td></tr>' );
			$this.rows_widgets_array.push( true );
		}

		function setEmployerContributions( data ) {
			var render = $this.getRender(); //get render, should be a table
			var headerRow = Global.loadWidget( 'views/payroll/pay_stub/PayStubEntryViewInsideEditorThreeColumnHeader.html' );
			var args = {
				col1: $.i18n._( 'Employer Contributions' ),
				col2: $.i18n._( 'Note' ),
				col3: $.i18n._( 'Amount' ),
				col4: $.i18n._( 'YTD Amount' )
			};

			var template = _.template(headerRow);
			$( render ).append( template(args) );

			$this.rows_widgets_array.push( true );
			for ( var i = 0; i < _.size( data ); i++ ) {
				if ( Global.isSet( data[i] ) ) {
					var row = data[i];
					row.type_id = 30;
					$this.addRow( row );
				}
			}
			$( render ).append( '<tr><td colspan="8"><br></td></tr>' );
			$this.rows_widgets_array.push( true );
		}

		function setAccrual( data ) {
			var render = $this.getRender(); //get render, should be a table
			var headerRow = Global.loadWidget( 'views/payroll/pay_stub/PayStubEntryViewInsideEditorThreeColumnHeader.html' );
			var args = {
				col1: $.i18n._( 'Accrual' ),
				col2: $.i18n._( 'Note' ),
				col3: $.i18n._( 'Amount' ),
				col4: $.i18n._( 'Balance' )
			};

			var template = _.template(headerRow);
			$( render ).append( template(args) );

			$this.rows_widgets_array.push( true );
			for ( var i = 0; i < _.size( data ); i++ ) {
				if ( Global.isSet( data[i] ) ) {
					var row = data[i];
					row.type_id = 50;
					$this.addRow( row );
				}
			}
			$( render ).append( '<tr><td colspan="8"><br></td></tr>' );
			$this.rows_widgets_array.push( true );
		}

		if ( this.parent_controller.isEditMode() == true || this.parent_controller.checkForNonHeaderData(val[10]) ) {
			setEarnings(val[10]);
		}

		if ( this.parent_controller.isEditMode() == true || this.parent_controller.checkForNonHeaderData(val[20]) ) {
			setDeductions(val[20]);
		}

		setNetPay(val[40]);

		if ( this.parent_controller.isEditMode() == true || this.parent_controller.checkForNonHeaderData(val[80])  ) {
			setMiscellaneous(val[80]);
		}

		if ( this.parent_controller.isEditMode() == true || this.parent_controller.checkForNonHeaderData(val[30])  ) {
			setEmployerContributions(val[30]);
		}

		if ( this.parent_controller.isEditMode() == true || this.parent_controller.checkForNonHeaderData(val[50])  ) {
			setAccrual(val[50]);
		}

		var $parent_controller = this.parent_controller
		TTPromise.wait(null,null,function(){
			// render inside pay stub transaction
			$parent_controller.initInsideTransactionEditorData();

			if(	$parent_controller.copied_record_id && TTUUID.isUUID($parent_controller.copied_record_id) ) {
				$parent_controller.copied_record_id = '';
			}
		});

		this.calcTotal();
	},

	checkForNonHeaderData: function(data){
		for ( var n in data ) {
			if( TTUUID.isUUID( data[n].id ) == true ){
				return true;
			}
		}

		return false;
	},

	insideEntryEditorAddRow: function( data, index ) {
		var $this = this;
		if ( !data ) {
			$this.addRow( {}, index );
		} else {
			if ( typeof index != 'undefined' && typeof this.rows_widgets_array[index].ytd_amount != 'undefined' && !data['type_id'] ) {
				data['type_id'] = this.rows_widgets_array[index].ytd_amount.attr( 'type_id' );
			}

			function renderColumns( data, type, index ) {
				var render = $this.getRender(); //get render, should be a table
				var widgets = {}; //Save each row's widgets
				var row; //Get Row render
				var widgetContainer = $( "<div class='widget-h-box'></div>" );
				var right_label;
				var args = { filter_data: {} };
				var pay_stub_amendment_id = 0, user_expense_id = 0;
				var pay_stub_status_id = $this.parent_controller['current_edit_record']['status_id'];

				var is_add = false;

				if ( ( !$this.parent_controller['current_edit_record']['id'] && !$this.parent_controller.copied_record_id ) || ( !data.id )  ) {
					is_add = true;
				}

				if ( pay_stub_status_id == 40 || pay_stub_status_id == 100 ) {
					is_add = false;
				}


				if ( !isNaN( parseFloat( data['pay_stub_amendment_id'] ) ) && parseFloat( data['pay_stub_amendment_id'] ) > 0 ) {
					pay_stub_amendment_id = data['pay_stub_amendment_id'];
				}
				if ( !isNaN( parseFloat( data['user_expense_id'] ) ) && parseFloat( data['user_expense_id'] ) > 0 ) {
					user_expense_id = data['user_expense_id'];
				}

				if ( $this.parent_controller.copied_record_id ) {
					pay_stub_amendment_id = 0;
					user_expense_id = 0;
				}

				var row_enabled = true;
				// if the pay_stub_amendment_id and user_expense_id all >0 how to display the right label?
				if ( TTUUID.isUUID( pay_stub_amendment_id ) && pay_stub_amendment_id != TTUUID.zero_id ) {
					right_label = $( "<span class='widget-right-label'> (" + $.i18n._( 'Amendment' ) + ")</span>" );
					row_enabled = false;
				} else if ( TTUUID.isUUID( user_expense_id ) && user_expense_id != TTUUID.zero_id ) {
					right_label = $( "<span class='widget-right-label'> (" + $.i18n._( 'Expense' ) + ")</span>" );
					row_enabled = false;
				}

				if ( type == 10 ) {
					row = $( Global.loadWidget( 'views/payroll/pay_stub/PayStubEntryViewInsideEditorFiveColumnRow.html' ) );
				} else {
					row = $( Global.loadWidget( 'views/payroll/pay_stub/PayStubEntryViewInsideEditorThreeColumnRow.html' ) );
				}

				// Pay Stub Account
				var form_item_name_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
				form_item_name_input.AComboBox( {
					api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
					width: 132,
					is_static_width: 132,
					allow_multiple_selection: false,
					layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
					show_search_inputs: true,
					set_empty: true,
					field: 'pay_stub_entry_name_id',
				} );
				form_item_name_input.setValue(data.pay_stub_entry_name_id)
				form_item_name_input.setEnabled( row_enabled )

				form_item_name_input.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
					$this.onFormItemChange( target );
				} );

				var form_item_name_text = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_name_text.TText( {field: 'name'} );
				form_item_name_text.setValue( data.name ? ( ( data['type_id'] != 40) ? "  " + data.name : data.name  ) : '' );

				// Note(description)
				var form_item_note_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_note_input.TTextInput( { field: 'description', width: 300, display_na:false } );
				form_item_note_input.setValue( data.description );
				form_item_note_input.attr( 'editable', true );

				var form_item_note_text = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_note_text.TText( {field: 'description', display_na:false} );
				form_item_note_text.setValue( data.description ? data.description : ' ' );

				// Rate
				var form_item_rate_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_rate_input.TTextInput( {field: 'rate', width: 60, hasKeyEvent: true} );
				form_item_rate_input.setValue( Global.removeTrailingZeros(data.rate) );
				form_item_rate_input.attr( 'editable', true );
				form_item_rate_input.unbind( 'formItemKeyUp' ).bind( 'formItemKeyUp', function( e, target ) {
					$this.onFormItemKeyUp( target );
				} );

				form_item_rate_input.unbind( 'formItemKeyDown' ).bind( 'formItemKeyDown', function( e, target ) {
					$this.onFormItemKeyDown( target );
				} );

				form_item_rate_input.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
					$this.onFormItemChange( target );
				} );

				var form_item_rate_text = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_rate_text.TText( {field: 'rate'} );
				form_item_rate_text.setValue( Global.removeTrailingZeros(data.rate) );


				// Hrs/Units
				var form_item_units_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_units_input.TTextInput( {field: 'units', width: 60, hasKeyEvent: true} );
				form_item_units_input.setValue( Global.removeTrailingZeros(data.units) );
				form_item_units_input.attr( 'editable', true );
				form_item_units_input.unbind( 'formItemKeyUp' ).bind( 'formItemKeyUp', function( e, target ) {
					$this.onFormItemKeyUp( target );
				} );

				form_item_units_input.unbind( 'formItemKeyDown' ).bind( 'formItemKeyDown', function( e, target ) {
					$this.onFormItemKeyDown( target );
				} );
				form_item_units_input.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
					$this.onFormItemChange( target );
				} );

				var form_item_units_text = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_units_text.TText( {field: 'units'} );
				form_item_units_text.setValue( Global.removeTrailingZeros(data.units) );


				// Amount
				var form_item_amount_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_amount_input.TTextInput( {field: 'amount', width: 60} );
				form_item_amount_input.setValue( Global.removeTrailingZeros(data.amount) );

				form_item_amount_input.unbind( 'formItemKeyUp' ).bind( 'formItemKeyUp', function( e, target ) {
					$this.onFormItemKeyUp( target );
				} );

				form_item_amount_input.unbind( 'formItemKeyDown' ).bind( 'formItemKeyDown', function( e, target ) {
					$this.onFormItemKeyDown( target );
				} );

				form_item_amount_input.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
					$this.onFormItemChange( target );
				} );

				var form_item_amount_text = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_amount_text.TText( {field: 'amount'} );
				form_item_amount_text.setValue( Global.removeTrailingZeros(data.amount) );

				// YTD Amount
				var form_item_ytd_amount_text = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_ytd_amount_text.TText( {field: 'ytd_amount'} );
				form_item_ytd_amount_text.setValue( Global.removeTrailingZeros(data.ytd_amount) );
				form_item_ytd_amount_text.attr( {
					'pay_stub_entry_id': (data.id && $this.parent_controller.current_edit_record.id) ? data.id : '',
					'type_id': data['type_id'],
					'original_amount': data['amount'] ? data['amount'] : '0.00',
					'original_ytd_amount': data['ytd_amount'] ? data['ytd_amount'] : '0.00',
					'pay_stub_entry_name_id': data['pay_stub_entry_name_id'] ? data['pay_stub_entry_name_id'] : null
				} );

//				if ( !$this.parent_controller.copied_record_id ) {
					form_item_ytd_amount_text.attr( 'pay_stub_amendment_id', pay_stub_amendment_id );
					form_item_ytd_amount_text.attr( 'user_expense_id', user_expense_id );
//				}

				if ( parseInt( data['ytd_amount'] ) > 0 ) {

				} else if ( pay_stub_status_id == 40 || pay_stub_status_id == 100 || data.total_row === true ) {
					form_item_ytd_amount_text.text( '-' );
				}

				if ( parseInt( data.rate ) > 0 && !$this.parent_controller.copied_record_id ) {
					form_item_amount_input.setReadOnly( true );
				} else if ( pay_stub_status_id == 40 || pay_stub_status_id == 100 || data.total_row === true  ) {
					form_item_rate_text.text( '-' );
				}

				if (  parseInt( data.units ) > 0 && !$this.parent_controller.copied_record_id ) {
					form_item_amount_input.setReadOnly( true );
				} else if ( pay_stub_status_id == 40 || pay_stub_status_id == 100 || data.total_row === true  ) {
					form_item_units_text.text( '-' );
				}

				// name
				if ( type == 40 || data.total_row === true  ) {
					if ( data['type_id'] == 40 || data.total_row === true ) {
						form_item_name_text.css( 'font-weight', 'bold' );
					}
					widgets[form_item_name_text.getField()] = form_item_name_text;
					widgetContainer.append( form_item_name_text );
					widgetContainer.append( right_label );
					row.children().eq( 0 ).append( widgetContainer );

				} else {
					if ( $this.parent_controller.isEditMode() == true || index ) {

						if (  data['type_id'] == 40 || data.total_row === true  ) {
							form_item_name_text.css( 'font-weight', 'bold' );
							widgets[form_item_name_text.getField()] = form_item_name_text;
							widgetContainer.append( form_item_name_text );
							widgetContainer.append( right_label );
							row.children().eq( 0 ).append( widgetContainer );

						} else {
							args['filter_data']['type_id'] = [type];
							form_item_name_input.setDefaultArgs( args );
							widgets[form_item_name_input.getField()] = form_item_name_input;
							widgetContainer.append( form_item_name_input );
							widgetContainer.append( right_label );
							row.children().eq( 0 ).append( widgetContainer );
						}

					} else {
						if ( data['type_id'] == 40 || data.total_row === true ) {
							form_item_name_text.css( 'font-weight', 'bold' );
						}
						widgets[form_item_name_text.getField()] = form_item_name_text;
						widgetContainer.append( form_item_name_text );
						widgetContainer.append( right_label );
						row.children().eq( 0 ).append( widgetContainer );
					}
				}

				// Note
				if ( ( data['type_id'] == type.toString() || data['type_id'] === type) && type != 40 && !data.total_row ) {
					if ( $this.parent_controller.isEditMode() == true ) {
						if ( ( ( TTUUID.isUUID( pay_stub_amendment_id ) && pay_stub_amendment_id != TTUUID.zero_id ) || ( TTUUID.isUUID( user_expense_id ) && user_expense_id != TTUUID.zero_id ) ) ) {
							form_item_note_input.setReadOnly( true );
						}
						widgets[form_item_note_input.getField()] = form_item_note_input;
						row.children().eq( 1 ).append( form_item_note_input );

					} else {
						widgets[form_item_note_text.getField()] = form_item_note_text;
						row.children().eq( 1 ).append( form_item_note_text );
					}
				} else {
					if ( Global.isSet( index ) || is_add || ( _.size( data) === 1 && ( $this.parent_controller.isEditMode() == true ) ) ) {
//						if ( (data['type_id'] === '40' || data['type_id'] == 40) && ( type == 20 || type == 30 || type == 50 || type == 80 ) ) {
//							widgets[form_item_note_text.getField()] = form_item_note_text;
//							row.children().eq( 1 ).append( form_item_note_text );
//						} else if ( type != 40 ) {
//							widgets[form_item_note_input.getField()] = form_item_note_input;
//							row.children().eq( 1 ).append( form_item_note_input );
//						}
						if ( (data['type_id'] == 40 || data.total_row === true) ) {

						} else {
							widgets[form_item_note_input.getField()] = form_item_note_input;
							row.children().eq( 1 ).append( form_item_note_input );
						}

					} else if ( type == 20 || type == 30 || type == 50 || type == 80 ) {
						widgets[form_item_note_text.getField()] = form_item_note_text;
						row.children().eq( 1 ).append( form_item_note_text );
					}
				}

				// amount
				if ( ( data['type_id'] === type.toString() || data['type_id'] === type ) && type != 40 && !data.total_row) {
					if ( $this.parent_controller.isEditMode() == true ) {
						if ( ( TTUUID.isUUID( pay_stub_amendment_id ) && pay_stub_amendment_id != TTUUID.zero_id ) || ( TTUUID.isUUID( user_expense_id ) && user_expense_id != TTUUID.zero_id ) ) {
							form_item_amount_input.setReadOnly( true );
						}
						widgets[form_item_amount_input.getField()] = form_item_amount_input;
						if ( type == 10 ) {
							row.children().eq( 4 ).append( form_item_amount_input );
						} else {
							row.children().eq( 2 ).append( form_item_amount_input );
						}
					} else {
						widgets[form_item_amount_text.getField()] = form_item_amount_text;
						if ( type == 10 ) {
							row.children().eq( 4 ).append( form_item_amount_text );
						} else {
							row.children().eq( 2 ).append( form_item_amount_text );
						}

					}
				} else {
					if ( (Global.isSet( index ) || is_add || ( _.size( data) === 1 && ( $this.parent_controller.isEditMode() == true ) ) ) && type != 40 && !data.total_row ) {

						if ( data['type_id'] == 40 ) {
							form_item_amount_text.css( 'font-weight', 'bold' );
							widgets[form_item_amount_text.getField()] = form_item_amount_text;
							if ( type == 10 ) {
								row.children().eq( 4 ).append( form_item_amount_text );
							} else {
								row.children().eq( 2 ).append( form_item_amount_text );
							}
						} else {
							widgets[form_item_amount_input.getField()] = form_item_amount_input;
							if ( type == 10 ) {
								row.children().eq( 4 ).append( form_item_amount_input );
							} else {
								row.children().eq( 2 ).append( form_item_amount_input );
							}
						}

					} else {
						if ( (data['type_id'] == 40 || data.total_row) && type == 30 ) {
							form_item_amount_text.css( 'font-weight', 'bold' );
						} else {
							form_item_amount_text.css( 'font-weight', 'bold' );
						}
						widgets[form_item_amount_text.getField()] = form_item_amount_text;
						if ( type == 10 ) {
							row.children().eq( 4 ).append( form_item_amount_text );
						} else {
							row.children().eq( 2 ).append( form_item_amount_text );
						}
					}
				}

				// Ytd amount
				if ( data['type_id'] == 40 || data.total_row) {
					form_item_ytd_amount_text.css( 'font-weight', 'bold' );
				}
				if ( ( Global.isSet( index ) || is_add || _.size( data) === 1 ) && type != 40 ) {
					form_item_ytd_amount_text.text( '-' );
				}
				widgets[form_item_ytd_amount_text.getField()] = form_item_ytd_amount_text;
				if ( type == 10 ) {
					row.children().eq( 5 ).append( form_item_ytd_amount_text );
				} else {
					row.children().eq( 3 ).append( form_item_ytd_amount_text );
				}

				if ( type == 10 ) { // && !data.total_row ) {

					// rate
					if ( data['type_id'] == 10 && !data.total_row) {
						if ( $this.parent_controller.isEditMode() == true ) {
							if ( ( TTUUID.isUUID( pay_stub_amendment_id ) && pay_stub_amendment_id != TTUUID.zero_id ) || ( TTUUID.isUUID( user_expense_id ) && user_expense_id != TTUUID.zero_id ) ) {
								form_item_rate_input.setReadOnly( true );
							}
							widgets[form_item_rate_input.getField()] = form_item_rate_input;
							row.children().eq( 2 ).append( form_item_rate_input );

						} else {
							widgets[form_item_rate_text.getField()] = form_item_rate_text;
							row.children().eq( 2 ).append( form_item_rate_text );
						}
					} else {
						if ( Global.isSet( index ) || is_add ) {
							if ( data['type_id'] == 40 || data.total_row ) {

							} else {
								widgets[form_item_rate_input.getField()] = form_item_rate_input;
								row.children().eq( 2 ).append( form_item_rate_input );
							}
						}
					}

					// units
					if ( data['type_id'] == 10 && !data.total_row ) {
						if ( $this.parent_controller.isEditMode() == true ) {
							if ( ( TTUUID.isUUID( pay_stub_amendment_id ) && pay_stub_amendment_id != TTUUID.zero_id ) || ( TTUUID.isUUID( user_expense_id ) && user_expense_id != TTUUID.zero_id ) ) {
								form_item_units_input.setReadOnly( true );
							}
							widgets[form_item_units_input.getField()] = form_item_units_input;
							row.children().eq( 3 ).append( form_item_units_input );
						} else {
							widgets[form_item_units_text.getField()] = form_item_units_text;
							row.children().eq( 3 ).append( form_item_units_text );
						}
					} else {
						if ( Global.isSet( index ) || is_add ) {

							if ( data['type_id'] == 40 || data.total_row ) {
								form_item_units_text.css( 'font-weight', 'bold' );
								widgets[form_item_units_text.getField()] = form_item_units_text;
								row.children().eq( 3 ).append( form_item_units_text );
							} else {
								widgets[form_item_units_input.getField()] = form_item_units_input;
								row.children().eq( 3 ).append( form_item_units_input );
							}
						} else {
							form_item_units_text.css( 'font-weight', 'bold' );
							widgets[form_item_units_text.getField()] = form_item_units_text;
							row.children().eq( 3 ).append( form_item_units_text );
						}
					}

				}

				//Build row widgets
				if ( ( TTUUID.isUUID( pay_stub_amendment_id ) && pay_stub_amendment_id != TTUUID.zero_id ) || ( TTUUID.isUUID( user_expense_id ) && user_expense_id != TTUUID.zero_id ) ) {
					row.children().last().find( '.minus-icon ' ).hide();
				}

				if ( data['total_row'] == true ) {
					widgets['total_row'] = true;
				}

				if ( typeof data['type_id'] != 'undefined' ) {
					widgets['type_id'] = data['type_id'];
				}

				if ( data['pay_stub_entry_account_id'] == $this.parent_controller.pseal_link.net_pay_entry_account_id ) {
					widgets['pay_stub_entry_account_id'] =  $this.parent_controller.pseal_link.net_pay_entry_account_id;
				}

				if ( typeof index !== 'undefined' ) {
					row.insertAfter( $( render ).find( 'tr' ).eq( index ) );
					$this.rows_widgets_array.splice( (index + 1 ), 0, widgets );

				} else {
					$( render ).append( row );
					$this.rows_widgets_array.push( widgets );
				}

				if ( $this.parent_controller.isEditMode() == true ) {
					$this.addIconsEvent( row ); //Bind event to add and minus icon
				} else {
					row.children().last().empty();
				}

				if ( data.total_row || data.type_id == 40 ) {
					row.find( '.plus-icon' ).remove()
					row.find( '.minus-icon' ).remove()
				}

			}

			if ( data['type_id'] == 10 ) {
				renderColumns( data, 10, index );
			} else if ( data['type_id'] == 20 ) {
				renderColumns( data, 20, index );
			} else if ( data['type_id'] == 30 ) {
				renderColumns( data, 30, index );
			} else if ( data['type_id'] == 40 ) {
				renderColumns( data, 40, index );
			} else if ( data['type_id'] == 50 ) {
				renderColumns( data, 50, index );
			} else if ( data['type_id'] == 80  ) {
				renderColumns( data, 80, index );
			}

		}

	},

	insideEntryEditorRemoveRow: function( row ) {
		var index = row[0].rowIndex;
		if( this.rows_widgets_array[index].ytd_amount ) {
			var remove_id = this.rows_widgets_array[index].ytd_amount.attr('pay_stub_entry_id');
			var type_id = this.rows_widgets_array[index].ytd_amount.attr('type_id');
			if (TTUUID.isUUID(remove_id)) {
				this.delete_ids.push(remove_id);
			}
			row.remove();
			if (this.rows_widgets_array[index - 1] === true && ( this.rows_widgets_array[index + 1]['total_row'] === true || this.rows_widgets_array[index + 1] === true )) {
				this.addRow({id: '', type_id: type_id}, index - 1);
				this.rows_widgets_array.splice(index + 1, 1); //Remove from the array used in calcTotal()
			} else {
				this.rows_widgets_array.splice(index, 1); //Remove from the array used in calcTotal()
			}

		}
		this.calcTotal();
	},

	savePayStub: function(record, callbackFunction) {
		// when the user create a new pay stub record have them can send entries to api.
		if ( this.include_pay_stub_accounts ) {
			var entries = this.saveInsideEntryEditorData();
			var transactions = this.saveInsideTransactionEditorData();
			if ( entries.length > 0 ) {
				record['entries'] = entries;
			}
			if ( transactions.length > 0 ) {
				record['transactions'] = transactions;
			}
		}
		callbackFunction();
	},

	onSaveClick: function( ignoreWarning ) {
		var $this = this;
		var record;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		LocalCacheData.current_doing_context_action = 'save';
		if ( this.is_mass_editing ) {
			this.include_pay_stub_accounts = false;
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
				common_record = $this.uniformVariable( common_record );
				record.push( common_record );

			} );
		} else {
			record = this.current_edit_record;
			record = this.uniformVariable( record );
		}
		this.savePayStub(record, function() {
			$this.api['set' + $this.api.key_name]( record, false, true, {onResult: function( result ) {
				$this.onSaveResult( result );
			}} );
		});
	},

	onSaveResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			$this.is_add = false;
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;
			} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
				$this.refresh_id = result_data
			}

			if ( !$this.edit_only_mode ) {
				$this.search();
			}
			$this.onSaveDone( result );
			$this.current_edit_record = null;
			$this.removeEditView();

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},


	saveInsideTransactionEditorData: function( callBack ) {
		//called by validation function
		var $this = this;
		var data = this.editor.insideTransactionEditorGetValue($this.current_edit_record.id ? $this.current_edit_record.id : '');

		if ( this.editor.delete_transaction_ids.length > 0 ) {
			for( var i = 0 ; i < this.editor.delete_transaction_ids.length; i++ ){
				for ( var n = 0 ; n < data.length; n++) {
					if( this.editor.delete_transaction_ids[i] == data[n].id ){
						data[n].deleted = 1;
					}
				}
			}
		}
		return data;
	},

	onCopyAsNewClick: function() {
		var $this = this;
		var reload_entries = false;
		this.is_add = true;

		LocalCacheData.current_doing_context_action = 'copy_as_new';
		if ( Global.isSet( this.edit_view ) ) {
//			for ( var i = 0; i < this.editor.rows_widgets_array.length; i++ ) {
//				if ( this.editor.rows_widgets_array[i] === true ) {
//					continue;
//				}
//				this.editor.rows_widgets_array[i].ytd_amount.attr( 'pay_stub_entry_id', '' );
//				this.editor.rows_widgets_array[i].ytd_amount.removeAttr( 'pay_stub_amendment_id' );
//				this.editor.rows_widgets_array[i].ytd_amount.removeAttr( 'user_expense_id' );
//			}
			this.copied_record_id = this.current_edit_record.id;
			this.current_edit_record.id = '';

			this.edit_view_ui_dic.user_id.setEnabled( true );
			if ( !this.current_edit_record.status_id != 25 ) {
				this.current_edit_record.status_id = 25;
				this.edit_view_ui_dic.status_id.setValue( 25 );
//				this.editor.show_cover = false;
//				this.include_pay_stub_accounts = true;
//				reload_entries = true;
			}
			this.editor.show_cover = false;
//			this.editor.removeCover();

			var navigation_div = this.edit_view.find( '.navigation-div' );
			navigation_div.css( 'display', 'none' );
			this.setEditMenu();
			this.setTabStatus();
			this.is_changed = false;
			// reset the entries data.
//			if ( reload_entries ) {
				this.editor.removeAllRows( true );
				this.initInsideEntryEditorData();
//			}

			ProgressBar.closeOverlay();

		} else {

			var filter = {};
			var grid_selected_id_array = this.getGridSelectIdArray();
			var grid_selected_length = grid_selected_id_array.length;
			var selectedId;

			if ( grid_selected_length > 0 ) {
				selectedId = grid_selected_id_array[0];

				filter.filter_data = {};
				filter.filter_data.id = [selectedId];

				this.api['get' + this.api.key_name]( filter, {
					onResult: function( result ) {
						$this.onCopyAsNewResult( result );

					}
				} );
			} else {
				TAlertManager.showAlert( $.i18n._( 'No selected record' ) );
			}


		}

	},

	initEditView: function(){
		this.original_status_id = this.current_edit_record.status_id;
		this._super('initEditView');
	},

	onSubViewRemoved: function() {
		this.search();

		if ( !this.edit_view ) {
			this.setDefaultMenu();
		} else {
			this.setEditMenu();
		}

	},

	onCopyAsNewResult: function( result ) {
		var $this = this;
		var result_data = result.getResult();

		if ( !result_data ) {
			TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
			$this.onCancelClick();
			return;
		}

		$this.openEditView(); // Put it here is to avoid if the selected one is not existed in data or have deleted by other pragram. in this case, the edit view should not be opend.

		result_data = result_data[0];
		this.copied_record_id = result_data.id;
		result_data.id = '';
		if ( $this.sub_view_mode && $this.parent_key ) {
			result_data[$this.parent_key] = $this.parent_value;
		}
		if ( result_data.status_id != 25 ) {
			result_data.status_id = 25; // If its status is not open then set it to open status.
		}

		$this.current_edit_record = result_data;
		$this.editor.show_cover = false;
		$this.initEditView();
	},

	onSaveAndContinue: function( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_changed = false;
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'save_and_continue';
		var record = this.current_edit_record;
		this.original_status_id = record.status_id;

		record = this.uniformVariable( record );

		this.savePayStub(record,function() {
			$this.api['set' + $this.api.key_name]( record, false, ignoreWarning, {onResult: function( result ) {
				$this.onSaveAndContinueResult( result );

			}} );
		});

	},

	onMassEditClick: function() {
		var $this = this;
		$this.is_add = false;
		$this.is_viewing = false;
		$this.is_mass_editing = true;
		LocalCacheData.current_doing_context_action = 'mass_edit';
		$this.openEditView();
		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		this.mass_edit_record_ids = [];

		$.each( grid_selected_id_array, function( index, value ) {
			$this.mass_edit_record_ids.push( value )
		} );

		filter.filter_data = {};
		filter.filter_data.id = this.mass_edit_record_ids;

		this.api['getCommon' + this.api.key_name + 'Data']( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();

				if ( !result_data ) {
					result_data = [];
				}

				$this.api['getOptions']( 'unique_columns', {
					onResult: function( result ) {
						$this.unique_columns = result.getResult();
						$this.api['getOptions']( 'linked_columns', {
							onResult: function( result1 ) {
								$this.linked_columns = result1.getResult();
								if ( $this.sub_view_mode && $this.parent_key ) {
									result_data[$this.parent_key] = $this.parent_value;
								}

								$this.current_edit_record = result_data;
								$this.initEditView();
							}
						} );

					}
				} );

			}
		} );

	},

	onSaveAndContinueResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
				$this.refresh_id = result_data;
			}
			$this.search( false );
			$this.editor.show_cover = false;
			$this.onEditClick( $this.refresh_id, true );

			$this.onSaveAndContinueDone( result );
		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	onSaveAndNextResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;
			} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
				$this.refresh_id = result_data;
			}
			$this.editor.show_cover = true;
			$this.onRightArrowClick();
			$this.search( false );
			$this.onSaveAndNextDone( result );

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	},

	saveInsideEntryEditorData: function( callBack ) {
		//called by validation function
		var $this = this;
		var data = this.editor.getValue( $this.current_edit_record.id ? $this.current_edit_record.id : '' );

		if ( this.editor.delete_ids.length > 0 ) {
			for( var i = 0 ; i < this.editor.delete_ids.length; i++ ){
				for ( var n = 0 ; n < data.length; n++) {
					if( this.editor.delete_ids[i] == data[n].id ){
						data[n].deleted = 1;
					}
				}
			}
		}
		if ( callBack && typeof callBack == 'function' ) {
			callBack();
		}
		return data;
	},

	insideEntryEditorGetValue: function( current_edit_item_id ) {
		var len = this.rows_widgets_array.length;
		var result = [];

		if ( this.cover && this.cover.length > 0 ) {
			return result;
		}

		for ( var i = 0; i < len; i++ ) {
			var row = this.rows_widgets_array[i];
			var data = {};

			if ( row === true || _.isArray(row) ) {
				continue;
			}

//			var pay_stub_amendment_id = row['ytd_amount'].attr( 'pay_stub_amendment_id' );
//			var user_expense_id = row['ytd_amount'].attr( 'user_expense_id' );
//
//			if (  pay_stub_amendment_id > 0 || user_expense_id > 0  ) {
//				continue;
//			}

			data['id'] = row['ytd_amount'].attr( 'pay_stub_entry_id' );

			if ( row['ytd_amount'].attr( 'type_id' ) ) {
				data['type'] = row['ytd_amount'].attr( 'type_id' );
			}

			data['rate'] =  row['rate'] ? row['rate'].getValue() : '';
			data['units'] =  row['units'] ? row['units'].getValue() : '';
			data['amount'] = row['amount'] ? row['amount'].getValue() : '';

			if ( Global.isSet( row['ytd_amount'] ) ) {
				data['ytd_amount'] = row['ytd_amount'].getValue();
			}

			data['description'] = row['description'] ? row['description'].getValue() : '';

			if ( Global.isSet( row['pay_stub_entry_name_id'] ) ) {
				data['pay_stub_entry_name_id'] = row['pay_stub_entry_name_id'].getValue();
			} else {
				data['pay_stub_entry_name_id'] = row['ytd_amount'].attr( 'pay_stub_entry_name_id' );
			}


			// return back to the server with the same data get from API.
			data['pay_stub_amendment_id'] = row['ytd_amount'].attr( 'pay_stub_amendment_id' );
			data['user_expense_id'] = row['ytd_amount'].attr( 'user_expense_id' );

			data['pay_stub_id'] = current_edit_item_id;
			if ( row['total_row'] != true && TTUUID.isUUID( data['pay_stub_entry_name_id'] )
				&& ( data['pay_stub_entry_name_id'] != TTUUID.zero_id
					|| ( data['description'] != undefined && data['description'].length > 0 )
					|| ( data['rate'] != undefined && data['rate'].length > 0 && parseFloat( data['rate'] ) != 0 )
					|| ( data['units'] != undefined && data['units'].length > 0 && parseFloat( data['units'] ) != 0 )
					|| ( data['amount'] != undefined && data['amount'].length > 0 && parseFloat( data['amount'] ) != 0 )
				) ) {
				result.push(data);
			}
		}

		return result;
	},

	getFilterColumnsFromDisplayColumns: function() {

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

	onFormItemChange: function( target, doNotValidate ) {
		var $this = this;
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;
		switch ( key ) {
//			case 'status_id':
//				if ( c_value == 40 || c_value == 100 ) {
//					this.include_pay_stub_accounts = false;
//				}
//				break;
			case 'user_id':
				if ( this.is_add ) {
					var transaction_rows = $this.editor.rows_widgets_array[$this.editor.rows_widgets_array.length - 2];
					var user_id =$this.edit_view_ui_dic.user_id.getValue()
					for ( var t in transaction_rows ) {
						if ( Global.isArray(transaction_rows) && transaction_rows[t].remittance_destination_account_id ) {
							transaction_rows[t].remittance_destination_account_id.setDefaultArgs(  {filter_data: { user_id: user_id } } );
							transaction_rows[t].remittance_destination_account_id.setValue( TTUUID.zero_id );
						}
					}
				}
				break;
			case 'country':
				var widget = this.edit_view_ui_dic['province'];
				widget.setValue( null );
				break;
			case 'pay_period_id':
				var filter = {};
				filter.filter_data = {};
				filter.filter_data.id = c_value;
				this.pay_period_api['get' + this.pay_period_api.key_name]( filter, {onResult: function( res ) {
					//Error: Uncaught TypeError: Cannot read property 'start_date' of undefined in interface/html5/#!m=PayStub&a=new&tab=PayStub line 1836
					if ( res.isValid() && res.getResult()[0] ) {
						var result = res.getResult()[0];
						var start_date = Global.strToDate( result.start_date ).format();
						var end_date = Global.strToDate( result.end_date ).format();
						var transaction_date = Global.strToDate( result.transaction_date ).format();

						$this.current_edit_record['start_date'] = start_date;
						$this.current_edit_record['end_date'] = end_date;
						$this.current_edit_record['transaction_date'] = transaction_date;

						$this.edit_view_ui_dic['start_date'].setValue( start_date );
						$this.edit_view_ui_dic['end_date'].setValue( end_date );
						$this.edit_view_ui_dic['transaction_date'].setValue( transaction_date );

						if ( !doNotValidate ) {
							$this.validate();
						}
					}
				}} );
				break;
			default:
				if ( !doNotValidate ) {
					$this.validate();
				}
				break;
		}


		if ( key === 'country' || key === 'pay_period_id' ) {
			return;
		}

		if ( !doNotValidate ) {
			this.validate();

		}

	},

	onSetSearchFilterFinished: function() {

		if ( this.search_panel.getSelectTabIndex() === 1 ) {
			var combo = this.adv_search_field_ui_dic['country'];
			var select_value = combo.getValue();
			this.setProvince( select_value );
		}

	},
	onBuildAdvUIFinished: function() {

		this.adv_search_field_ui_dic['country'].change( $.proxy( function() {
			var combo = this.adv_search_field_ui_dic['country'];
			var selectVal = combo.getValue();

			this.setProvince( selectVal );

			this.adv_search_field_ui_dic['province'].setValue( null );

		}, this ) );
	},

	setProvince: function( val, m ) {
		var $this = this;

		if ( !val || val === '-1' || val === '0' ) {
			$this.province_array = [];
			this.adv_search_field_ui_dic['province'].setSourceData( [] );
		} else {

			this.company_api.getOptions( 'province', val, {onResult: function( res ) {
				res = res.getResult();
				if ( !res ) {
					res = [];
				}

				$this.province_array = Global.buildRecordArray( res );
				$this.adv_search_field_ui_dic['province'].setSourceData( $this.province_array );

			}} );
		}
	},
	eSetProvince: function( val, refresh ) {
		var $this = this;
		var province_widget = $this.edit_view_ui_dic['province'];

		if ( !val || val === '-1' || val === '0' ) {
			$this.e_province_array = [];
			province_widget.setSourceData( [] );
		} else {
			this.company_api.getOptions( 'province', val, {onResult: function( res ) {
				res = res.getResult();
				if ( !res ) {
					res = [];
				}

				$this.e_province_array = Global.buildRecordArray( res );
				if ( refresh && $this.e_province_array.length > 0 ) {
					$this.current_edit_record.province = $this.e_province_array[0].value;
					province_widget.setValue( $this.current_edit_record.province );
				}
				province_widget.setSourceData( $this.e_province_array );

			}} );
		}
	},


	validate: function() {
		var $this = this;

		var record = {};
		var transaction_record = {};

		if ( this.is_mass_editing ) {
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
			record = this.current_edit_record;
		}

		record = this.uniformVariable( record );

		if ( this.include_pay_stub_accounts ) {
			var entries = $this.saveInsideEntryEditorData();
			var transactions = $this.saveInsideTransactionEditorData();
			if ( typeof entries == 'object' ) {
				record['entries'] = entries;
			}
			if ( typeof transactions == 'object' ) {
				record['transactions'] = transactions;
			}
		}

		this.api['validate' + this.api.key_name]( record, {onResult: function( result ) {
			$this.validateResult( result );
		}} );
	},

	buildEditViewUI: function() {
		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_pay_stub': $.i18n._( 'Pay Stub' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );

//		var tab_0_label = this.edit_view.find( 'a[ref=tab_pay_stub]' );
//		var tab_1_label = this.edit_view.find( 'a[ref=tab_audit]' );
//		tab_0_label.text( $.i18n._( 'Pay Stub' ) );
//		tab_1_label.text( $.i18n._( 'Audit' ) );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStub' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_STUB,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_pay_stub = this.edit_view_tab.find( '#tab_pay_stub' );

		var tab_pay_stub_column1 = tab_pay_stub.find( '.first-column' );
		var tab_pay_stub_column2 = tab_pay_stub.find( '.second-column' );
//		var tab_pay_stub_column3 = tab_pay_stub.find( '.third-column' );

		var form_item_input;

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_pay_stub_column1 );

		// Employee
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: false,
			field: 'user_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_pay_stub_column1 );

		// Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'status_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.filtered_status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_pay_stub_column1 );

		// Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {field: 'type_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_pay_stub_column1 );

		// Currency
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APICurrency' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.CURRENCY,
			show_search_inputs: true,
			set_empty: true,
			field: 'currency_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Currency' ), form_item_input, tab_pay_stub_column1 );

		// Pay Period
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayPeriod' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_PERIOD,
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_period_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Period' ), form_item_input, tab_pay_stub_column2 );

		// Payroll Run
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'run_id', width: 20} );
		this.addEditFieldToColumn( $.i18n._( 'Payroll Run' ), form_item_input, tab_pay_stub_column2 );

		// Pay Start Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'start_date'} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Start Date' ), form_item_input, tab_pay_stub_column2 );

		// Pay End Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'end_date'} );
		this.addEditFieldToColumn( $.i18n._( 'Pay End Date' ), form_item_input, tab_pay_stub_column2 );

		// Payment Date

		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( {field: 'transaction_date'} );
		this.addEditFieldToColumn( $.i18n._( 'Payment Date' ), form_item_input, tab_pay_stub_column2, '' );

		//Inside pay stub entries editor

		var inside_pay_stub_entry_editor_div = tab_pay_stub.find( '.inside-pay-stub-entry-editor-div' );

		this.editor = Global.loadWidgetByName( FormItemType.INSIDE_EDITOR );

		this.editor.InsideEditor( {
			addRow: this.insideEntryEditorAddRow,
			removeRow: this.insideEntryEditorRemoveRow,
			getValue: this.insideEntryEditorGetValue,
			setValue: this.insideEntryEditorSetValue,
			parent_controller: this,
			api: this.pay_stub_entry_api,
			render: 'views/payroll/pay_stub/PayStubEntryViewInsideEditorRender.html',
			render_args: {},
			row_render: 'views/payroll/pay_stub/PayStubEntryViewInsideEditorRow.html'

		} );

		this.editor.show_cover = true;
		this.editor.delete_transaction_ids = [];
		this.editor.removeCover = this.removeEntryInsideEditorCover;
		this.editor.onEditClick = this.removeEntryInsideEditorCover;
		this.editor.onFormItemKeyUp = function( target ) {
			var index = target.parent().parent().index();
			var $this = this;
			var widget_rate = $this.rows_widgets_array[index]['rate'];
			var widget_units = $this.rows_widgets_array[index]['units'];
			var widget_amount = $this.rows_widgets_array[index]['amount'];

			if ( target.getValue().length === 0 ) {
				widget_amount.setReadOnly( false );
			}
			if ( widget_rate.getValue().length > 0 || widget_units.getValue().length > 0 ) {
				widget_amount.setReadOnly( true );
			}

			if ( widget_rate.getValue().length > 0 && widget_units.getValue().length > 0 ) {
				//widget_amount.setValue( ( parseFloat( widget_rate.getValue() ) * parseFloat( widget_units.getValue() ) ).toFixed( 2 ) );
				var amount_value = Global.MoneyRound( parseFloat( widget_rate.getValue() ) * parseFloat( widget_units.getValue() ) );
				if ( amount_value == 'NaN' || amount_value == 0 || amount_value == '' ) {
					amount_value = '0.00';
				}
				widget_amount.setValue( amount_value );
				this.onFormItemChange( widget_amount, true );
			} else {
				widget_amount.setValue( '0.00' );
				this.onFormItemChange( widget_amount, true );
			}
		};
		this.editor.onFormItemKeyDown = function( target ) {
			var index = target.parent().parent().index();
			var $this = this;
			var widget = $this.rows_widgets_array[index]['amount'];
			var widget_rate = $this.rows_widgets_array[index]['rate'];
			var widget_units = $this.rows_widgets_array[index]['units'];
			if ( widget_rate.getValue().length > 0 && widget_units.getValue().length > 0 ) {

			} else {
				widget.setValue( '0.00' );
				this.onFormItemChange( widget, true );
			}

			widget.setReadOnly( true );
		};
		this.editor.onFormItemChange = function( target, doNotValidate ) {
			var key = target.getField();
			var c_value = parseFloat( target.getValue() ? target.getValue() : 0 ); // new value
			var index = target.parent().parent().index();

			if ( key === 'amount' ) {
				var original_ytd_amount = parseFloat( this.rows_widgets_array[index]['ytd_amount'].attr( 'original_ytd_amount' ) );
				var original_amount = parseFloat( this.rows_widgets_array[index]['ytd_amount'].attr( 'original_amount' ) );
//				var new_ytd_amount = (original_ytd_amount - original_amount + c_value).toFixed( 4 );
				var new_ytd_amount = Global.removeTrailingZeros( (original_ytd_amount - original_amount + c_value) );
				this.rows_widgets_array[index]['ytd_amount'].setValue( new_ytd_amount != 0 ? new_ytd_amount : '-' );
				this.rows_widgets_array[index]['ytd_amount'].attr( 'original_ytd_amount', new_ytd_amount );
				this.rows_widgets_array[index]['ytd_amount'].attr( 'original_amount', c_value );
			}

			if ( doNotValidate ) {

			} else {
				this.parent_controller.validate();
			}

			this.calcTotal();
		};

		this.editor.calcTotal = function() {
			var total_units = 0;
			var total_amount = 0;
			var total_ytd_amount = 0;
			var net_pay_amount = 0;
			var net_pay_ytd_amount = 0;
			var total_units_blank = true;

			for ( var i = 0; i < this.rows_widgets_array.length; i++ ) {
				var row = this.rows_widgets_array[i];
				if ( row === true || _.isArray(row) ) {
					total_units = 0;
					total_amount = 0;
					total_ytd_amount = 0;
					continue;
				}


				if ( row['total_row'] === true ) {
					if( !Global.isNumeric(total_amount) ) {
						total_amount = 0;
					}

					if ( Global.isSet( row['units'] ) ) {
						if (total_units_blank == false) {
							row['units'].setValue(Global.MoneyRound(total_units));
						} else if (total_units_blank) {
							row['units'].setValue('-');
						}
					}
					row['amount'].setValue( Global.MoneyRound( parseFloat(total_amount) ) );
					row['ytd_amount'].setValue( Global.MoneyRound( parseFloat(total_ytd_amount) ) );

					if ( row.type_id == 10 ) { // Start with total gross value
						net_pay_amount = total_amount;
						net_pay_ytd_amount = total_ytd_amount;
					} else if ( row.type_id == 20 ) { // Subtract deductions (only)
						net_pay_amount = net_pay_amount - total_amount;
						net_pay_ytd_amount = net_pay_ytd_amount - total_ytd_amount;
					}

					this.parent_controller.net_pay_amount = net_pay_amount;
					continue;
				}

				 if ( row['pay_stub_entry_account_id'] && row['pay_stub_entry_account_id'] == this.parent_controller.pseal_link.net_pay_entry_account_id ) {
					row['amount'].setValue( Global.MoneyRound(net_pay_amount) );
					row['ytd_amount'].setValue( Global.MoneyRound(net_pay_ytd_amount) );
					continue;
				}

				var current_units = 0;
				if ( Global.isSet( row['units']) && Global.isNumeric( row['units'].getValue() ) ){
					current_units = Global.MoneyRound(parseFloat(row['units'].getValue()))
					total_units_blank = false;
				}

				var current_total_amount = 0;
				if ( Global.isSet( row['amount']) && Global.isNumeric(row['amount'].getValue()) ){
					current_total_amount = parseFloat(row['amount'].getValue());
					total_units_blank = false;
				}

				var current_ytd_total = 0;
				if ( Global.isSet( row['ytd_amount']) && Global.isNumeric(row['ytd_amount'].getValue()) ){
					current_ytd_total = parseFloat(row['ytd_amount'].getValue())
				}

				if ( total_units_blank == false ) {
					total_units = parseFloat(total_units) + parseFloat(current_units);
				} else {
					total_units = '';
				}

				if ( Global.isNumeric( current_total_amount ) ) {
					total_amount = total_amount + current_total_amount;
				}

				if (  Global.isNumeric( current_ytd_total ) ) {
					total_ytd_amount = total_ytd_amount + current_ytd_total;
				}

			}

			this.calcTransactionTotals();

		};

		this.editor.insideTransactionEditorSetValue = function( data ) {
			$this = this;
			if ( !this.parent_controller.current_edit_record ) {
				return false;
			}
			var pay_stub_status_id = this.parent_controller['current_edit_record']['status_id'];
			var is_add = false;
			if ( !this.parent_controller['current_edit_record']['id'] && !this.parent_controller.copied_record_id  ) {
				is_add = true;
			}
			if ( !is_add && ( pay_stub_status_id == 25 ) && this.show_cover ) {
				this.cover = Global.loadWidgetByName( WidgetNamesDic.NO_RESULT_BOX );
				this.cover.NoResultBox( {
					related_view_controller: this,
					message: $.i18n._( 'Click the Edit icon below to override pay stub amounts' ),
					is_edit: true
				} );
			}
			var render = this.getRender(); //get render, should be a table
			var headerRow = Global.loadWidget( 'views/payroll/pay_stub/PayStubTransactionViewInsideEditorColumnHeader.html' );
			var args = {
				col1: $.i18n._( 'Payment Method' ),
				col2: $.i18n._( 'Note' ),
				col3: $.i18n._( 'Status' ),
				col4: $.i18n._( 'Payment Date' ),
				col5: $.i18n._( 'Amount' ),
			};
			$( render ).append( '<tr class="tblSepHeader"><td colspan="8">' + $.i18n._( 'Transactions' ) + '</td></tr>' );

			var template = _.template(headerRow);
			$( render ).append( template(args) );

			this.rows_widgets_array.push(true);
			if(_.size( data ) > 0 ) {
				for ( var i = 0; i < _.size( data ); i++ ) {
					if ( Global.isSet( data[i] ) ) {
						var row = data[i];
						this.insideTransactionEditorAddRow( row );
					}
				}
				//$( render ).append( '<tr><td colspan="8"><br></td></tr>' );
				this.rows_widgets_array.push( true );
				if ( this.cover && this.cover.length > 0 ) {
					this.cover.css( {width: this.width(), height: this.height()+30} );
					this.parent().append( this.cover );
				}
			} else {
				this.parent_controller.getPayStubTransactionDefaultData( function( data ) {
					$this.insideTransactionEditorAddRow( data );
					//$( render ).append( '<tr><td colspan="8"><br></td></tr>' );
					$this.rows_widgets_array.push( true );
					if ( $this.cover && $this.cover.length > 0 ) {
						$this.cover.css( {width: $this.width(), height: $this.height()} );
						$this.parent().append( $this.cover );
					}
				} );
			}


			this.calcTransactionTotals();
		};

		this.editor.insideTransactionEditorGetValue = function( parent_id ) {
			var len = this.rows_widgets_array.length;
			var result = [];

			if ( this.cover && this.cover.length > 0 ) {
				return result;
			}

			for ( var i = 0; i < len; i++ ) {
				var data = {};
				if ( _.isArray(this.rows_widgets_array[i]) ) {
					var row = this.rows_widgets_array[i][0];

					if ( !Global.isSet(row['remittance_destination_account_id']) || !row['remittance_destination_account_id'].getValue() ) {
						continue; //row is not editable but is among those that are.
					}
					data['id'] = row['form_item_record']['id'];
					data['remittance_destination_account_id'] = row['remittance_destination_account_id'] ? row['remittance_destination_account_id'].getValue() : TTUUID.zero_id;

					if ( Global.isSet(row['status_id']) ) {
						data['status_id'] = row['status_id'].getValue();
					}

					data['transaction_date'] = row['transaction_date'] ? row['transaction_date'].getValue() : '';

					if ( Global.isSet(row['currency_id']) ) {
						data['currency_id'] = row['currency_id'].getValue();
					}

					data['note'] = row['note'] ? row['note'].getValue() : '';

					data['amount'] = row['amount'] ? row['amount'].getValue() : '';

					if ( Global.isSet(row['deleted']) && row['deleted'] == 1 ) {
						data['deleted'] = 1;
					} else {
						data['deleted'] = 0;
					}

					data['pay_stub_id'] = parent_id;

					if ( row['total_row'] != true && TTUUID.isUUID( data['remittance_destination_account_id'] )
						&& ( data['remittance_destination_account_id'] != TTUUID.zero_id
							|| ( data['note'] != undefined && data['note'].length > 0 )
							|| ( data['amount'] != undefined && data['amount'].length > 0 && parseFloat( data['amount'] ) != 0 )
						) ) {
						result.push(data);
					}
				}
			}

			return result;
		};

		this.editor.insideTransactionEditorRemoveRow = function( row ) {
			var index = row[0].rowIndex -1;

			if ( this.rows_widgets_array[index][0] ) {
				this.rows_widgets_array[index][0]['form_item_record']['deleted'] = 1;
				var remove_id = this.rows_widgets_array[index][0]['form_item_record']['id'];
			}

			if ( TTUUID.isUUID(remove_id) ) {
				this.delete_transaction_ids.push( remove_id );
			}

			row.hide();
			//count transaction rows.
			var trows = $('.paystub_transaction_row:visible').length;

			this.rows_widgets_array.splice( index, 1 );
			if ( trows == 0 ) {
				this.insideTransactionEditorAddRow( {}, index );
			}

			this.parent_controller.validate();
			$this.calcTransactionTotals();


		};

		this.editor.calcTransactionTotals = function() {
			var total_amount = 0;

			for ( var i = 0; i < this.rows_widgets_array.length; i++ ) {
				var row = this.rows_widgets_array[i];
				//use transaction_date column existence as is transaction flag.
				if ( _.isObject(row) //row is object
					&& Global.isSet(row[0])	//row's object is set.
					&& Global.isSet(row[0].remittance_destination_account_id) //row is a pay stub transaction
					&& Global.isSet(row[0].form_item_record.deleted) == false //row is not removed
					&& Global.isSet(row[0].status_id) //row is not removed
					&& ( row[0].status_id.getValue() == 10 || row[0].status_id.getValue() == 20 ) //status is valid
				) {
					var current = parseFloat(row[0].amount.getValue())
					if ( isNaN(current) ){
						current = 0;
					}
					total_amount += current;
				}
			}

			total_amount = Global.MoneyRound(total_amount)

			//total_amount = Global.removeTrailingZeros(total_amount);
			var render = this.getRender();
			$('.transaction_total_rows').remove();


			if ( total_amount > 0 ) {
				var difference = Global.removeTrailingZeros( parseFloat(total_amount) - parseFloat(this.parent_controller.net_pay_amount) );
				if ( isNaN(difference) ) {
					difference = 0;
				}

				var color = 'green';
				if (difference != 0) {
					color = 'red';
				}

				if (difference != 0) {
					difference = Global.MoneyRound(difference);
					$(render).append('<tr class="tblDataWhite transaction_total_rows"><td colspan="2" style="text-align:left"><b>Transaction Total</b></td><td colspan="3"><br></td><td style="text-align:right;"><span>Difference: <i  style="color:red;">' + difference  + '</i></b></td><td><b style="color:' + color + '">' + Global.MoneyRound(total_amount) + '</b></td><td></td></td></tr>');
				} else {
					$(render).append('<tr class="tblDataWhite transaction_total_rows"><td colspan="2" style="text-align:left"><b>Transaction Total</b></td><td colspan="4"><br></td><td><b style="color:' + color + '">' +  total_amount + '</b></td><td></td></td></tr>');
				}

				this.rows_widgets_array.push(true);
			}
		};

		this.editor.insideTransactionEditorAddRow = function( data, index ) {
			var $this = this;
			if (_.size( data ) == 0 ) {
				this.parent_controller.getPayStubTransactionDefaultData( function( data ) {
					$this.insideTransactionEditorAddRow( data, index );
				}, index );
			} else {
				var render = $this.getRender();
				var widgets = [];
				var transaction = {};
				transaction.form_item_record = {};
				var row = $( Global.loadWidget( 'views/payroll/pay_stub/PayStubTransactionViewInsideEditorColumnRow.html' ) );
				data = _.isArray(data) ? data[0] : data;
				transaction.form_item_record.id = (data.id && $this.parent_controller.current_edit_record.id) ? data.id : '';
				var pay_stub_status_id = $this.parent_controller.current_edit_record.status_id;
				var is_add = false;
				if ( !$this.parent_controller.current_edit_record.id && !$this.parent_controller.copied_record_id ) {
					is_add = true;
				}

				// Destination Account
				// writable
				var allowed_statuses = [10];
				if ( $this.parent_controller.is_add == false ) {
					allowed_statuses.push( 20 );
				}
				var form_item_remittance_destination_account_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
				form_item_remittance_destination_account_input.AComboBox( {
					api_class: (APIFactory.getAPIClass( 'APIRemittanceDestinationAccount' )),
					allow_multiple_selection: false,
					layout_name: ALayoutIDs.REMITTANCE_DESTINATION_ACCOUNT,
					show_search_inputs: true,
					set_empty: true,
					field: 'remittance_destination_account_id',
					default_args: {filter_data: { user_id: $this.parent_controller.current_edit_record.user_id, status_id: allowed_statuses } }
				} );
				form_item_remittance_destination_account_input.setValue( data.remittance_destination_account_id ? data.remittance_destination_account_id : '' );
				form_item_remittance_destination_account_input.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
					$this.parent_controller.validate();
				} );

				// readable
				var form_item_remittance_destination_account_text = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_remittance_destination_account_text.TText( {field: 'remittance_destination_account'} );
				form_item_remittance_destination_account_text.setValue( data.remittance_destination_account ? data.remittance_destination_account : '' );

				// Note
				// writable
				var form_item_note_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_note_input.TTextInput( {field: 'note', width: 300,  display_na: false} );
				form_item_note_input.setValue( data.note );
				form_item_note_input.attr( 'editable', true );
				//readable
				var form_item_note_text = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_note_text.TText( {field: 'description', display_na:false} );
				form_item_note_text.setValue( data.description );

				// Transaction Status
				// writable
				var form_item_status_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_status_input.TComboBox( {field: 'status_id', set_empty: false} );
				form_item_status_input.setSourceData( Global.addFirstItemToArray( $this.parent_controller.transaction_status_array ) );
				form_item_status_input.setValue( data.status_id ? data.status_id : '' );
				form_item_status_input.setEnabled( (LocalCacheData.current_doing_context_action == 'copy_as_new') );
				form_item_status_input.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
					$this.parent_controller.validate();
				} );
				// readable
				var form_item_status_text = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_status_text.TText( {field: 'status'} );
				form_item_status_text.setValue( data.status ? data.status : '' );

				// Transaction Date
				// writable
				var form_item_transaction_date_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
				form_item_transaction_date_input.TDatePicker( {field: 'transaction_date'} );
				form_item_transaction_date_input.setValue( data.transaction_date ? data.transaction_date : '' );
				form_item_transaction_date_input.attr( 'editable', true );

				if(!data['transaction_date']) {
					form_item_transaction_date_input.setValue($this.parent_controller.edit_view_ui_dic.transaction_date.getValue())
				}

				form_item_transaction_date_input.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
					$this.parent_controller.validate();
				} );
				// readable
				var form_item_confirmation_number_text = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_confirmation_number_text.TText( {field: 'transaction_date'} );
				form_item_confirmation_number_text.setValue( data.transaction_date ? data.transaction_date : '' );

				// Amount
				// writable
				var form_item_amount_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_amount_input.TTextInput( {field: 'amount', width: 60} );
				form_item_amount_input.setValue( data.amount ? Global.removeTrailingZeros(data.amount) : '' );
				form_item_amount_input.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
					$this.parent_controller.validate();
					$this.calcTransactionTotals();
				} );
				form_item_amount_input.attr( 'editable', true );
				// readable
				var form_item_amount_text = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_amount_text.TText( {field: 'amount'} );
				form_item_amount_text.setValue( data.amount ? Global.removeTrailingZeros(data.amount) : '' );


				if ( !data.status_id ) {
					data.status_id = 10;
				}

				if ( $this.parent_controller.isEditMode() ) {
					form_item_remittance_destination_account_input.setEnabled( data.status_id == 10 );
					form_item_transaction_date_input.setEnabled( data.status_id == 10 );
					form_item_amount_input.setReadOnly( data.status_id != 10 );
					form_item_note_input.setReadOnly( data.status_id != 10 );
					//form_item_status_input.setValue( 10 ); //set to pending
					form_item_status_input.setEnabled( false );
				} else {
					//status is not pending. disable editing the row.
					form_item_remittance_destination_account_input.setEnabled(false);
					form_item_transaction_date_input.setEnabled(false);
					form_item_amount_input.setEnabled(false);
					form_item_note_input.setEnabled(false);
					form_item_status_input.setEnabled( false );
					form_item_input = form_item_note_text; //only way to hide the N/A is to swap in a Text Field in view mode.
				}

				//actually append the row to the DOM
				transaction[form_item_remittance_destination_account_input.getField()] = form_item_remittance_destination_account_input;
				row.children().eq( 0 ).append( form_item_remittance_destination_account_input );

				transaction[form_item_note_input.getField()] = form_item_note_input;
				row.children().eq( 1 ).append( form_item_note_input );

				transaction[form_item_status_input.getField()] = form_item_status_input;
				row.children().eq( 2 ).append( form_item_status_input );

				transaction[form_item_transaction_date_input.getField()] = form_item_transaction_date_input;
				row.children().eq( 3 ).append( form_item_transaction_date_input );


				transaction[form_item_amount_input.getField()] = form_item_amount_input;
				row.children().eq( 4 ).append( form_item_amount_input );

				widgets.push(transaction);
				if ( typeof index !== 'undefined' ) {
					row.insertAfter( $( render ).find( 'tr' ).eq( index ) );
					$this.rows_widgets_array.splice( (index ), 0, widgets );
				} else {
					$( render ).append( row );
					$this.rows_widgets_array.push( widgets );
				}

				if (  $this.parent_controller.isEditMode() == true  ) {
					var minus_icon = row.find('.minus-icon');
					if ( data.status_id != 10 ) {
						minus_icon.remove();
					}else{
						minus_icon.click(function () {
							$this.insideTransactionEditorRemoveRow(row);
						});
					}
					var plus_icon = row.find( '.plus-icon' );
					plus_icon.click( function() {
						$this.insideTransactionEditorAddRow( {}, $( this ).parents('tr').index() );
					} );
				} else {
					//#2548 - Do not show plus button in view mode (when paystub is marked paid)
					row.children().last().find( '.minus-icon' ).remove();
					//if ( data.status_id != 20 ) {
						row.children().last().find('.plus-icon').remove();
					//}
				}

			}
		};
		inside_pay_stub_entry_editor_div.append( this.editor );
	},

	buildSearchFields: function() {
		this._super( 'buildSearchFields' );
		this.search_fields = [
			new SearchField( {label: $.i18n._( 'Pay Stub Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Pay Stub Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Employee Status' ),
				in_column: 1,
				field: 'user_status_id',
				multiple: true,
				basic_search: false,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Pay Period' ),
				in_column: 1,
				field: 'pay_period_id',
				layout_name: ALayoutIDs.PAY_PERIOD,
				api_class: (APIFactory.getAPIClass( 'APIPayPeriod' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Payroll Run' ),
				in_column: 1,
				field: 'run_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Title' ),
				field: 'title_id',
				in_column: 1,
				layout_name: ALayoutIDs.JOB_TITLE,
				api_class: (APIFactory.getAPIClass( 'APIUserTitle' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Currency' ),
				field: 'currency_id',
				in_column: 1,
				layout_name: ALayoutIDs.CURRENCY,
				api_class: (APIFactory.getAPIClass( 'APICurrency' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Group' ),
				in_column: 2,
				multiple: true,
				field: 'group_id',
				layout_name: ALayoutIDs.TREE_COLUMN,
				tree_mode: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Default Branch' ),
				in_column: 2,
				field: 'default_branch_id',
				layout_name: ALayoutIDs.BRANCH,
				api_class: (APIFactory.getAPIClass( 'APIBranch' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Default Department' ),
				field: 'default_department_id',
				in_column: 2,
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Country' ),
				in_column: 2,
				field: 'country',
				multiple: true,
				basic_search: false,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.COMBO_BOX} ),
			new SearchField( {label: $.i18n._( 'Province/State' ),
				in_column: 2,
				field: 'province',
				multiple: true,
				basic_search: false,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'City' ),
				field: 'city',
				basic_search: false,
				adv_search: true,
				in_column: 2,
				form_item_type: FormItemType.TEXT_INPUT} ),

			new SearchField( {label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX} )
		];
	},

	onContextMenuClick: function( context_btn, menu_name ) {
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
				ProgressBar.showOverlay();
				this.onAddClick();
				break;
			case ContextMenuIconName.save:
				ProgressBar.showOverlay();
				this.onSaveClick();
				break;
			case ContextMenuIconName.save_and_continue:
				ProgressBar.showOverlay();
				this.onSaveAndContinue();
				break;
			case ContextMenuIconName.save_and_next:
				ProgressBar.showOverlay();
				this.onSaveAndNextClick();
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
			case ContextMenuIconName.copy_as_new:
				ProgressBar.showOverlay();
				this.onCopyAsNewClick();
				break;
			case ContextMenuIconName.cancel:
				this.onCancelClick();
				break;
			case ContextMenuIconName.view:
				this.onViewClick( id );
				break;
			case ContextMenuIconName.timesheet:
			case ContextMenuIconName.schedule:
			case ContextMenuIconName.pay_stub_amendment:
			case ContextMenuIconName.edit_employee:
			case ContextMenuIconName.employee_pay_stubs:
			case ContextMenuIconName.employer_pay_stubs:
			case ContextMenuIconName.generate_pay_stub:
			case ContextMenuIconName.edit_pay_period:
			case ContextMenuIconName.export_excel:
			case ContextMenuIconName.pay_stub_transaction:
			default:
				this.onNavigationClick( id );
				break;

		}

	},

	onViewClick: function( editId, noRefreshUI ) {
		this.onNavigationClick( ContextMenuIconName.view );
	},

	onReportPrintClick: function( key ) {
		var $this = this;

		var grid_selected_id_array;

		var filter = {};

		var ids = [];

		var user_ids = [];

		var base_date;

		var pay_period_ids = [];

		if ( $this.edit_view && $this.current_edit_record.id ) {
			ids.push( $this.current_edit_record.id );
			user_ids.push( $this.current_edit_record.user_id );
			pay_period_ids.push( $this.current_edit_record.pay_period_id );
			base_date = $this.current_edit_record.start_date;
		} else {
			grid_selected_id_array = this.getGridSelectIdArray();
			$.each( grid_selected_id_array, function( index, value ) {
				var grid_selected_row = $this.getRecordFromGridById( value );
				ids.push( grid_selected_row.id );
				user_ids.push( grid_selected_row.user_id );
				pay_period_ids.push( grid_selected_row.pay_period_id );
				base_date = grid_selected_row.start_date;
			} );
		}

		var args = {filter_data: {id: ids}};
		var post_data = {0: args, 1: true, 2: key};

		this.doFormIFrameCall( post_data );

	},

	onNavigationClick: function( iconName ) {

		var $this = this;

		var grid_selected_id_array;

		var filter = {};

		var ids = [];

		var user_ids = [];

		var base_date;

		var pay_period_ids = [];

		if ( $this.edit_view && $this.current_edit_record.id ) {
			ids.push( $this.current_edit_record.id );
			user_ids.push( $this.current_edit_record.user_id );
			pay_period_ids.push( $this.current_edit_record.pay_period_id );
			base_date = $this.current_edit_record.start_date;
		} else {
			grid_selected_id_array = this.getGridSelectIdArray();
			$.each( grid_selected_id_array, function( index, value ) {
				var grid_selected_row = $this.getRecordFromGridById( value );
				ids.push( grid_selected_row.id );
				user_ids.push( grid_selected_row.user_id );
				pay_period_ids.push( grid_selected_row.pay_period_id );
				base_date = grid_selected_row.start_date;
			} );
		}

		var args = {filter_data: {id: ids}};

		var post_data;

		switch ( iconName ) {
			case ContextMenuIconName.edit_employee:
				if ( user_ids.length > 0 ) {
					IndexViewController.openEditView( this, 'Employee', user_ids[0] );
				}
				break;
			case ContextMenuIconName.edit_pay_period:
				if ( pay_period_ids.length > 0 ) {
					IndexViewController.openEditView( this, 'PayPeriods', pay_period_ids[0] )
				}
				break;
			case ContextMenuIconName.timesheet:
				if ( user_ids.length > 0 ) {
					filter.user_id = user_ids[0];
					filter.base_date = base_date;
					Global.addViewTab( $this.viewId, 'Pay Stubs', window.location.href );
					IndexViewController.goToView( 'TimeSheet', filter );
				}
				break;
			case ContextMenuIconName.schedule:
				filter.filter_data = {};
				var include_users = {value: user_ids };
				filter.filter_data.include_user_ids = include_users;
				filter.select_date = base_date;
				Global.addViewTab( this.viewId, 'Pay Stubs', window.location.href );
				IndexViewController.goToView( 'Schedule', filter );
				break;
			case ContextMenuIconName.pay_stub_amendment:
				filter.filter_data = {};
				filter.filter_data.user_id = user_ids[0];
				filter.filter_data.pay_period_id = pay_period_ids[0];
				Global.addViewTab( this.viewId, 'Pay Stubs', window.location.href );
				IndexViewController.goToView( 'PayStubAmendment', filter );
				break;
			case ContextMenuIconName.generate_pay_stub:

				if ( user_ids.length === 1 ) {
					filter.user_id = user_ids[0];
				} else if ( user_ids.length > 1 ) {
					filter.user_id = user_ids;
				}

				if ( pay_period_ids.length === 1 ) {
					filter.pay_period_id = [pay_period_ids[0]];
				} else if ( pay_period_ids.length > 1 ) {
					filter.pay_period_id = pay_period_ids;
				}else{
					filter.pay_period_id = [];
				}

				IndexViewController.openWizard( 'GeneratePayStubWizard', filter, function() {
					$this.search();
				} );
				break;
			case ContextMenuIconName.view:
				post_data = {0: args, 1: false, 2: 'pdf', 3: true};
				this.doFormIFrameCall( post_data );
				break;
			case ContextMenuIconName.employee_pay_stubs:
				post_data = {0: args, 1: false, 2: 'pdf', 3: true};
				this.doFormIFrameCall( post_data );
				break;
			case ContextMenuIconName.employer_pay_stubs:
				post_data = {0: args, 1: false, 2: 'pdf', 3: false};
				this.doFormIFrameCall( post_data );
				break;
			case ContextMenuIconName.pay_stub_transaction:
				filter = {};
				filter.filter_data = {};

				filter.filter_data.user_id = {value: user_ids };
				filter.filter_data.pay_period_id = {value: pay_period_ids };

				Global.addViewTab( this.viewId, 'Pay Stubs', window.location.href );
				IndexViewController.goToView( 'PayStubTransaction', filter );
				break;
			case ContextMenuIconName.export_excel:
				this.onExportClick('export' + this.api.key_name )
				break;
			case ContextMenuIconName.direct_deposit:
				var data = {
					filter_data:{
						pay_stub_id: this.getGridSelectIdArray()
					}
				}
				IndexViewController.openWizardController('ProcessTransactionsWizardController', data );
				break;
		}

	},

	onReportMenuClick: function( id ) {
		this.onReportPrintClick( id );
	},

	doFormIFrameCall: function( postData ) {
		Global.APIFileDownload( this.api.className, 'get' + this.api.key_name , postData );
	}


} );
