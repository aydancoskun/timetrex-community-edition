Form1099MiscReportViewController = ReportBaseViewController.extend( {

	_required_files: [ 'APIForm1099MiscReport', 'APIPayStubEntryAccount' ],

	province_array: null,

	state_field_array: null,

	initReport: function( options ) {
		this.script_name = 'Form1099MiscReport';
		this.viewId = 'Form1099MiscReport';
		this.context_menu_name = $.i18n._( 'Form 1099-Misc' );
		this.navigation_label = $.i18n._( 'Saved Report' ) +':';
		this.view_file = 'Form1099MiscReportView.html';
		this.api = new (APIFactory.getAPIClass( 'APIForm1099MiscReport' ))();
		this.include_form_setup = true;
	},

	initOptions: function( callBack ) {
		var $this = this;
		var options = [
			{option_name: 'page_orientation'},
			{option_name: 'font_size'},
			{option_name: 'chart_display_mode'},
			{option_name: 'chart_type'},
			{option_name: 'templates'},
			{option_name: 'setup_fields'},
			{option_name: 'auto_refresh'}
		];

		this.initDropDownOptions( options, function( result ) {

			new (APIFactory.getAPIClass( 'APICompany' ))().getOptions( 'province', 'US', {onResult: function( provinceResult ) {

				$this.province_array = Global.buildRecordArray( provinceResult.getResult() );

				callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
			}} );

		} );

	},

	onReportMenuClick: function( id ) {
		this.onViewClick( id );
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
		var form_setup_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Form' ),
			id: this.viewId + 'Form',
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

		var view_print = new RibbonSubMenu( {label: $.i18n._( 'View' ),
			id: ContextMenuIconName.view_print,
			group: form_setup_group,
			icon: 'view-35x35.png',
			type: RibbonSubMenuType.NAVIGATION,
			items: [],
			permission_result: true,
			permission: true} );

		var pdf_form_government = new RibbonSubMenuNavItem( {label: $.i18n._( 'Government (Multiple Employees/Page)' ),
			id: 'pdf_form_government',
			nav: view_print
		} );

		var pdf_form = new RibbonSubMenuNavItem( {label: $.i18n._( 'Employee (One Employee/Page)' ),
			id: 'pdf_form',
			nav: view_print
		} );

		if ( ( LocalCacheData.getCurrentCompany().product_edition_id >= 15 ) ) {
			var pdf_form_publish_employee = new RibbonSubMenuNavItem({
				label: $.i18n._('Publish Employee Forms'),
				id: 'pdf_form_publish_employee',
				nav: view_print
			});
		}

		// var print_print = new RibbonSubMenu( {label: $.i18n._( 'Print' ),
		// 	id: ContextMenuIconName.print,
		// 	group: form_setup_group,
		// 	icon: 'print-35x35.png',
		// 	type: RibbonSubMenuType.NAVIGATION,
		// 	items: [],
		// 	permission_result: true,
		// 	permission: true} );
		//
		// var pdf_form_print_government = new RibbonSubMenuNavItem( {label: $.i18n._( 'Government (Multiple Employees/Page)' ),
		// 	id: 'pdf_form_print_government',
		// 	nav: print_print
		// } );
		//
		// var pdf_form_print = new RibbonSubMenuNavItem( {label: $.i18n._( 'Employee (One Employee/Page)' ),
		// 	id: 'pdf_form_print',
		// 	nav: print_print
		// } );

		var save_setup = new RibbonSubMenu( {
			label: $.i18n._( 'Save Setup' ),
			id: ContextMenuIconName.save_setup,
			group: form_setup_group,
			icon: Icons.save_setup,
			permission_result: true,
			permission: null
		} );

		return [menu];

	},
	/* jshint ignore:start */
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
				ProgressBar.showOverlay();
				this.onViewClick();
				break;
			case ContextMenuIconName.view_html:
				ProgressBar.showOverlay();
				this.onViewClick('html');
				break;
			case ContextMenuIconName.view_html_new_window:
				ProgressBar.showOverlay();
				this.onViewClick('html', true);
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
			case ContextMenuIconName.timesheet_view: //All report view
				this.onViewClick( 'pdf_timesheet' );
				break;
			case ContextMenuIconName.timesheet_view_detail: //All report view
				this.onViewClick( 'pdf_timesheet_detail' );
				break;
			case ContextMenuIconName.save_setup: //All report view
				this.onSaveSetup();
				break;
		}
	},
	/* jshint ignore:end */
	schedule_deposit_array: null,

	buildFormSetupUI: function() {

		var $this = this;

		var tab3 = this.edit_view_tab.find( '#tab3' );

		var tab3_column1 = tab3.find( '.first-column' );

		this.edit_view_tabs[3] = [];

		this.edit_view_tabs[3].push( tab3_column1 );

		//Federal Income Tax Withheld (Box 4)
		var v_box = $( "<div class='v-box'></div>" );

		//Selection Type
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l4_include_pay_stub_entry_account'
		} );

		var form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( "<div class='clear-both-div'></div>" );

		//Selection
		var form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l4_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Federal Income Tax Withheld (Box 4)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Medical and Health Care Payments (Box 6)
		v_box = $( "<div class='v-box'></div>" );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l6_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( "<div class='clear-both-div'></div>" );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l6_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Medical and Health Care Payments (Box 6)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Nonemployee compensation (Box 7)
		v_box = $( "<div class='v-box'></div>" );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l7_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( "<div class='clear-both-div'></div>" );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l7_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Nonemployee compensation (Box 7)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );
	},


	getFormSetupData: function() {
		var other = {};
		other.l4 = {include_pay_stub_entry_account: this.current_edit_record.l4_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l4_exclude_pay_stub_entry_account};

		other.l6 = {include_pay_stub_entry_account: this.current_edit_record.l6_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l6_exclude_pay_stub_entry_account};

		other.l7 = {include_pay_stub_entry_account: this.current_edit_record.l7_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l7_exclude_pay_stub_entry_account};

		return other;
	},
	/* jshint ignore:start */
	setFormSetupData: function( res_Data ) {

		if ( !res_Data ) {
			this.show_empty_message = true;
		}

		if ( res_Data ) {
			if ( res_Data.l4 ) {
				this.edit_view_ui_dic.l4_exclude_pay_stub_entry_account.setValue( res_Data.l4.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.l4_include_pay_stub_entry_account.setValue( res_Data.l4.include_pay_stub_entry_account );

				this.current_edit_record.l4_include_pay_stub_entry_account = res_Data.l4.include_pay_stub_entry_account;
				this.current_edit_record.l4_exclude_pay_stub_entry_account = res_Data.l4.exclude_pay_stub_entry_account;

			}

			if ( res_Data.l6 ) {
				this.edit_view_ui_dic.l6_exclude_pay_stub_entry_account.setValue( res_Data.l6.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.l6_include_pay_stub_entry_account.setValue( res_Data.l6.include_pay_stub_entry_account );

				this.current_edit_record.l6_include_pay_stub_entry_account = res_Data.l6.include_pay_stub_entry_account;
				this.current_edit_record.l6_exclude_pay_stub_entry_account = res_Data.l6.exclude_pay_stub_entry_account;
			}

			if ( res_Data.l7 ) {
				this.edit_view_ui_dic.l7_exclude_pay_stub_entry_account.setValue( res_Data.l7.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.l7_include_pay_stub_entry_account.setValue( res_Data.l7.include_pay_stub_entry_account );

				this.current_edit_record.l7_include_pay_stub_entry_account = res_Data.l7.include_pay_stub_entry_account;
				this.current_edit_record.l7_exclude_pay_stub_entry_account = res_Data.l7.exclude_pay_stub_entry_account;
			}
		}
	}
	/* jshint ignore:end */
} );
