ActiveShiftReportViewController = ReportBaseViewController.extend( {

	initialize: function( options ) {
		this.__super( 'initialize', options );
		this.script_name = 'ActiveShiftReport';
		this.viewId = 'ActiveShiftReport';
		this.context_menu_name = $.i18n._( 'Whos In Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) +':';
		this.view_file = 'ActiveShiftReportView.html';
		this.api = new (APIFactory.getAPIClass( 'APIActiveShiftReport' ))();
		this.buildContextMenu();

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

		return [menu];

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
			case ContextMenuIconName.view:
				this.onViewClick();
				break;
			case ContextMenuIconName.view_html:

				this.onViewClick( 'html' );
				break;
			case ContextMenuIconName.view_html_new_window:
				this.onViewClick( 'html', true );
				break;
			case ContextMenuIconName.export_excel:
				this.onViewExcelClick();
				break;
			case ContextMenuIconName.cancel:
				this.onCancelClick();
				break;
			case ContextMenuIconName.save_existed_report: //All report view
				this.onSaveExistedReportClick();
				break;
			case ContextMenuIconName.save_new_report: //All report view
				this.onSaveNewReportClick();
				break;
			case ContextMenuIconName.save_setup: //All report view
				this.onSaveSetup();
				break;
		}
	},

	processFilterField: function() {
		for ( var i = 0; i < this.setup_fields_array.length; i++ ) {
			var item = this.setup_fields_array[i];
			if ( item.value === 'user_status_id' ) {
				item.value = 'filter';
			}
		}
	},

	setFilterValue: function( widget, value ) {
		widget.setValue( value.user_status_id );
	},

	onFormItemChangeProcessFilterField: function( target, key ) {
		var filter = target.getValue();
		this.visible_report_values[key] = {user_status_id: filter};
	}

} );