PayStubSummaryReportViewController = ReportBaseViewController.extend( {

	_required_files: ['APIPayStubSummaryReport', 'APIPayStub', 'APIPayrollRemittanceAgency', 'APICurrency'],

	initReport: function( options ) {
		this.script_name = 'PayStubSummaryReport';
		this.viewId = 'PayStubSummaryReport';
		this.context_menu_name = $.i18n._( 'Pay Stub Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'PayStubSummaryReportView.html';
		this.api = new (APIFactory.getAPIClass( 'APIPayStubSummaryReport' ))();
	},

	onReportMenuClick: function( id ) {
		this.processTransactions( id );
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
		var saved_report_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Saved Report' ),
			id: this.viewId + 'SavedReport',
			ribbon_menu: menu,
			sub_menus: []
		} );

		//menu group
		var pay_stub_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Pay Stub' ),
			id: this.script_name + 'PayStub',
			ribbon_menu: menu,
			sub_menus: []
		} );

		//menu group
		var export_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Export' ),
			id: this.viewId + 'Export',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var view_html = new RibbonSubMenu( {
			label: $.i18n._( 'View' ),
			id: ContextMenuIconName.view_html,
			group: editor_group,
			icon: Icons.view,
			permission_result: true,
			permission: null
		} );

		var view_pdf = new RibbonSubMenu( {
			label: $.i18n._( 'PDF' ),
			id: ContextMenuIconName.view,
			group: editor_group,
			icon: Icons.print,
			permission_result: true,
			permission: null
		} );

		var excel = new RibbonSubMenu( {
			label: $.i18n._( 'Excel' ),
			id: ContextMenuIconName.export_excel,
			group: editor_group,
			icon: Icons.export_excel,
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

		var save_existed_report = new RibbonSubMenu( {
			label: $.i18n._( 'Save' ),
			id: ContextMenuIconName.save_existed_report,
			group: saved_report_group,
			icon: Icons.save,
			permission_result: true,
			permission: null
		} );

		var save_new_report = new RibbonSubMenu( {
			label: $.i18n._( 'Save as New' ),
			id: ContextMenuIconName.save_new_report,
			group: saved_report_group,
			icon: Icons.save_and_new,
			permission_result: true,
			permission: null
		} );

		var employee_pay_stubs = new RibbonSubMenu( {
			label: $.i18n._( 'Employee<br>Pay Stubs' ),
			id: ContextMenuIconName.employee_pay_stubs,
			group: pay_stub_group,
			icon: Icons.pay_stubs,
			permission_result: true,
			permission: null
		} );

		var employer_pay_stubs = new RibbonSubMenu( {
			label: $.i18n._( 'Employer<br>Pay Stubs' ),
			id: ContextMenuIconName.employer_pay_stubs,
			group: pay_stub_group,
			icon: Icons.pay_stubs,
			permission_result: true,
			permission: null
		} );

		var direct_deposit = new RibbonSubMenu( {
			label: $.i18n._( 'Process<br>Transactions' ),
			id: ContextMenuIconName.direct_deposit,
			group: export_group,
			icon: 'direct_deposit-35x35.png',
			items: [],
			permission_result: true,
			permission: true
		} );

		return [menu];

	},

	// Overriding empty ReportBaseViewController.processFilterField() called from base.openEditView to provide view specific logic.
	processFilterField: function() {
		for ( var i = 0; i < this.setup_fields_array.length; i++ ) {
			var item = this.setup_fields_array[i];
			if ( item.value === 'status_id' ) {
				item.value = 'filter';
			}
		}
	},

	onFormItemChangeProcessFilterField: function( target, key ) {
		var filter = target.getValue();
		this.visible_report_values[key] = { status_id: filter };
	},

	setFilterValue: function( widget, value ) {
		widget.setValue( value.status_id );
	},

	onCustomContextClick: function( id ) {
		switch ( id ) {
			case ContextMenuIconName.employee_pay_stubs: //All report view
				this.onViewClick( 'pdf_employee_pay_stub' );
				break;
			case ContextMenuIconName.employer_pay_stubs: //All report view
				this.onViewClick( 'pdf_employer_pay_stub' );
				break;
			case ContextMenuIconName.direct_deposit:
				if ( !this.validate( true ) ) {
					return;
				}

				IndexViewController.openWizardController( 'ProcessTransactionsWizardController', { filter_data: this.visible_report_values } );
				break;
		}
	}
} );