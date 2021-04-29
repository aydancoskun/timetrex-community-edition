export class RemittanceSummaryReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {

		} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'RemittanceSummaryReport';
		this.viewId = 'RemittanceSummaryReport';
		this.context_menu_name = $.i18n._( 'Remittance Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'RemittanceSummaryReportView.html';
		this.api = TTAPI.APIRemittanceSummaryReport;
		this.include_form_setup = true;
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			groups: {
				form: {
					label: $.i18n._( 'Form' ),
					id: this.viewId + 'Form'
				}
			},
			exclude: [],
			include: [
				{
					label: $.i18n._( 'Save Setup' ),
					id: ContextMenuIconName.save_setup,
					group: 'form',
					icon: Icons.save_setup
				}
			]
		};

		return context_menu_model;
	}

	buildFormSetupUI() {

		var $this = this;

		var tab3 = this.edit_view_tab.find( '#tab_form_setup' );

		var tab3_column1 = tab3.find( '.first-column' );

		this.edit_view_tabs[3] = [];

		this.edit_view_tabs[3].push( tab3_column1 );

		var form_item_input;
		var widgetContainer;
		var label;

		//This Payment (Override)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'this_payment' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>' + $.i18n._( '(Leave blank to not override)' ) + '</span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'This Payment (Override)' ), form_item_input, tab3_column1, '', widgetContainer );

		//Gross Payroll
		var v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'gross_payroll_include_pay_stub_entry_account'
		} );

		var form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		var form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'gross_payroll_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Gross Payroll' ) + '\n*' + $.i18n._( 'Must Match T4 Box 14' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Employee/Employer EI Accounts
		v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'ei_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'ei_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Employee/Employer EI' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Employee/Employer CPP Accounts
		v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'cpp_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'cpp_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Employee/Employer CPP' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Income Tax Accounts
		v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'tax_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'tax_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Federal/Provincial Income Tax' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );
	}

	getFormSetupData() {
		var other = {};

		other.this_payment = this.current_edit_record.this_payment;

		other.gross_payroll = {
			include_pay_stub_entry_account: this.current_edit_record.gross_payroll_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.gross_payroll_exclude_pay_stub_entry_account
		};
		other.cpp = {
			include_pay_stub_entry_account: this.current_edit_record.cpp_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.cpp_exclude_pay_stub_entry_account
		};
		other.ei = {
			include_pay_stub_entry_account: this.current_edit_record.ei_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.ei_exclude_pay_stub_entry_account
		};
		other.tax = {
			include_pay_stub_entry_account: this.current_edit_record.tax_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.tax_exclude_pay_stub_entry_account
		};

		return other;
	}

	setFormSetupData( res_data ) {

		if ( !res_data ) {
			this.show_empty_message = true;
		}

		if ( res_data ) {

			if ( res_data.this_payment ) {
				this.edit_view_ui_dic.this_payment.setValue( res_data.this_payment );
				this.current_edit_record.this_payment = res_data.this_payment;
			}

			if ( res_data.gross_payroll ) {
				this.edit_view_ui_dic.gross_payroll_exclude_pay_stub_entry_account.setValue( res_data.gross_payroll.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.gross_payroll_include_pay_stub_entry_account.setValue( res_data.gross_payroll.include_pay_stub_entry_account );

				this.current_edit_record.gross_payroll_include_pay_stub_entry_account = res_data.gross_payroll.include_pay_stub_entry_account;
				this.current_edit_record.gross_payroll_exclude_pay_stub_entry_account = res_data.gross_payroll.exclude_pay_stub_entry_account;

			}

			if ( res_data.cpp ) {
				this.edit_view_ui_dic.cpp_exclude_pay_stub_entry_account.setValue( res_data.cpp.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.cpp_include_pay_stub_entry_account.setValue( res_data.cpp.include_pay_stub_entry_account );

				this.current_edit_record.cpp_include_pay_stub_entry_account = res_data.cpp.include_pay_stub_entry_account;
				this.current_edit_record.cpp_exclude_pay_stub_entry_account = res_data.cpp.exclude_pay_stub_entry_account;

			}

			if ( res_data.ei ) {
				this.edit_view_ui_dic.ei_exclude_pay_stub_entry_account.setValue( res_data.ei.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.ei_include_pay_stub_entry_account.setValue( res_data.ei.include_pay_stub_entry_account );

				this.current_edit_record.ei_include_pay_stub_entry_account = res_data.ei.include_pay_stub_entry_account;
				this.current_edit_record.ei_exclude_pay_stub_entry_account = res_data.ei.exclude_pay_stub_entry_account;
			}

			if ( res_data.tax ) {
				this.edit_view_ui_dic.tax_exclude_pay_stub_entry_account.setValue( res_data.tax.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.tax_include_pay_stub_entry_account.setValue( res_data.tax.include_pay_stub_entry_account );

				this.current_edit_record.tax_include_pay_stub_entry_account = res_data.tax.include_pay_stub_entry_account;
				this.current_edit_record.tax_exclude_pay_stub_entry_account = res_data.tax.exclude_pay_stub_entry_account;
			}
		}
	}

}
