ScheduleSummaryReportViewController = ReportBaseViewController.extend( {

	_required_files: {
		10: ['APIScheduleSummaryReport', 'APISchedule', 'APIAbsencePolicy'],
		20: ['APIJob', 'APIJobGroup', 'APIJobItemGroup', 'APIJobItem']
	},

	initReport: function( options ) {
		this.script_name = 'ScheduleSummaryReport';
		this.viewId = 'ScheduleSummaryReport';
		this.context_menu_name = $.i18n._( 'Schedule Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'ScheduleSummaryReportView.html';
		this.api = new (APIFactory.getAPIClass( 'APIScheduleSummaryReport' ))();
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
		var schedule_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Schedule' ),
			id: this.script_name + 'Schedule',
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

		var print = new RibbonSubMenu( {
			label: $.i18n._( 'Print' ),
			id: ContextMenuIconName.print,
			group: schedule_group,
			icon: 'print-35x35.png',
			type: RibbonSubMenuType.NAVIGATION,
			items: [],
			permission_result: true,
			permission: true
		} );

		var pdf_schedule = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Individual Schedules' ),
			id: 'pdf_schedule',
			nav: print
		} );

		var pdf_schedule_group_combined = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Group - Combined' ),
			id: 'pdf_schedule_group_combined',
			nav: print
		} );

		var pdf_schedule_group = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Group - Separated' ),
			id: 'pdf_schedule_group',
			nav: print
		} );

		var pdf_schedule_group_pagebreak = new RibbonSubMenuNavItem( {
			label: $.i18n._( 'Group - Separated (Page Breaks)' ),
			id: 'pdf_schedule_group_pagebreak',
			nav: print
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

	onReportMenuClick: function( id ) {
		this.onViewClick( id );
	},

	setFilterValue: function( widget, value ) {
		widget.setValue( value.status_id );
	},

	onFormItemChangeProcessFilterField: function( target, key ) {
		var filter = target.getValue();
		this.visible_report_values[key] = { status_id: filter };
	}

} );