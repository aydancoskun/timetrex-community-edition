export class Form940ReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {

			return_type_array: null,
			exempt_payment_array: null,
			state_array: null,
			province_array: null
		} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'Form940Report';
		this.viewId = 'Form940Report';
		this.context_menu_name = $.i18n._( 'Form 940' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'Form940ReportView.html';
		this.api = TTAPI.APIForm940Report;
		this.include_form_setup = true;
	}

	initOptions( callBack ) {
		var $this = this;
		var options = [
			{ option_name: 'page_orientation' },
			{ option_name: 'font_size' },
			{ option_name: 'chart_display_mode' },
			{ option_name: 'chart_type' },
			{ option_name: 'templates' },
			{ option_name: 'setup_fields' },
			{ option_name: 'return_type' },
			{ option_name: 'exempt_payment' },
			{ option_name: 'state' },
			{ option_name: 'auto_refresh' }
		];

		this.initDropDownOptions( options, function( result ) {
			TTAPI.APICompany.getOptions( 'province', 'US', {
				onResult: function( provinceResult ) {
					$this.province_array = Global.buildRecordArray( provinceResult.getResult() );

					callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
				}
			} );

		} );
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
					label: $.i18n._( 'View' ),
					id: ContextMenuIconName.view_form,
					group: 'form',
					icon: Icons.view
				},
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
		var form_item;

		//Type of Return
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input = form_item_input.AComboBox( {
			field: 'return_type',
			set_empty: true,
			allow_multiple_selection: true,
			layout_name: 'global_option_column',
			key: 'value'
		} );

		form_item_input.setSourceData( $this.return_type_array );
		this.addEditFieldToColumn( $.i18n._( 'Type of Return' ), form_item_input, tab3_column1, '' );

		//Exempt Payments
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input = form_item_input.AComboBox( {
			field: 'exempt_payment',
			set_empty: true,
			allow_multiple_selection: true,
			layout_name: 'global_option_column',
			key: 'value'
		} );

		form_item_input.setSourceData( $this.exempt_payment_array );
		this.addEditFieldToColumn( $.i18n._( 'Exempt Payment Types' ), form_item_input, tab3_column1 );

		//Total Payments (Line 3)
		// var v_box = $( "<div class='v-box'></div>" );
		//
		// //Selection Type
		// form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		// form_item_input.AComboBox( {
		// 	api_class: TTAPI.APIPayStubEntryAccount,
		// 	allow_multiple_selection: true,
		// 	layout_name: 'global_PayStubAccount',
		// 	show_search_inputs: true,
		// 	set_empty: true,
		// 	field: 'total_payments_include_pay_stub_entry_account'
		// } );
		//
		// var form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );
		//
		// v_box.append( form_item );
		// v_box.append( "<div class='clear-both-div'></div>" );
		//
		// //Selection
		// var form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		//
		// form_item_input_1.AComboBox( {
		// 	api_class: TTAPI.APIPayStubEntryAccount,
		// 	allow_multiple_selection: true,
		// 	layout_name: 'global_PayStubAccount',
		// 	show_search_inputs: true,
		// 	set_empty: true,
		// 	field: 'total_payments_exclude_pay_stub_entry_account'
		// } );
		//
		// form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );
		//
		// v_box.append( form_item );
		//
		// this.addEditFieldToColumn( $.i18n._( 'Total Payments (Line 3)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Exempt Payments (Line 4)
		var v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'exempt_payments_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

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
			field: 'exempt_payments_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Exempt Payments (Line 4)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Credit Reduction (Line 9)
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );

		form_item_input.TCheckbox( { field: 'line_9' } );
		this.addEditFieldToColumn( $.i18n._( 'Were ALL taxable FUTA wages excluded from State UI? (Line 9)' ), form_item_input, tab3_column1 );

		//Wages Excluded From State Unemployement Tax (Line 10)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'line_10' } );
		this.addEditFieldToColumn( $.i18n._( 'Wages Excluded From State Unemployement Tax (Line 10)' ), form_item_input, tab3_column1 );

		// //Credit Reduction (Line 11)
		// form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		//
		// form_item_input.TTextInput( {field: 'line_11'} );
		// this.addEditFieldToColumn( $.i18n._( 'Credit Reduction (Line 11)' ), form_item_input, tab3_column1 );

		//FUTA Tax Deposited For The Year (Line 13)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'tax_deposited' } );
		this.addEditFieldToColumn( $.i18n._( 'FUTA Tax Deposited For The Year (Line 13)' ), form_item_input, tab3_column1 );
	}

	getFormSetupData() {
		var other = {};

		//other.total_payments = {include_pay_stub_entry_account: this.current_edit_record.total_payments_include_pay_stub_entry_account, exclude_pay_stub_entry_account: this.current_edit_record.total_payments_exclude_pay_stub_entry_account};
		other.exempt_payments = {
			include_pay_stub_entry_account: this.current_edit_record.exempt_payments_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.exempt_payments_exclude_pay_stub_entry_account
		};

		other.return_type = this.current_edit_record.return_type;
		other.exempt_payment = this.current_edit_record.exempt_payment;
		other.line_9 = this.current_edit_record.line_9;
		other.line_10 = this.current_edit_record.line_10;
		//other.line_11 = this.current_edit_record.line_11;
		other.tax_deposited = this.current_edit_record.tax_deposited;

		return other;
	}

	/* jshint ignore:start */
	setFormSetupData( res_Data ) {

		if ( !res_Data ) {
			this.show_empty_message = true;
		}

		if ( res_Data ) {
			// if ( res_Data.total_payments ) {
			//     this.edit_view_ui_dic.total_payments_exclude_pay_stub_entry_account.setValue( res_Data.total_payments.exclude_pay_stub_entry_account );
			//     this.edit_view_ui_dic.total_payments_include_pay_stub_entry_account.setValue( res_Data.total_payments.include_pay_stub_entry_account );
			//
			//     this.current_edit_record.total_payments_include_pay_stub_entry_account = res_Data.total_payments.include_pay_stub_entry_account;
			//     this.current_edit_record.total_payments_exclude_pay_stub_entry_account = res_Data.total_payments.exclude_pay_stub_entry_account;
			// }

			if ( res_Data.exempt_payments ) {
				this.edit_view_ui_dic.exempt_payments_exclude_pay_stub_entry_account.setValue( res_Data.exempt_payments.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.exempt_payments_include_pay_stub_entry_account.setValue( res_Data.exempt_payments.include_pay_stub_entry_account );

				this.current_edit_record.exempt_payments_include_pay_stub_entry_account = res_Data.exempt_payments.include_pay_stub_entry_account;
				this.current_edit_record.exempt_payments_exclude_pay_stub_entry_account = res_Data.exempt_payments.exclude_pay_stub_entry_account;
			}

			if ( res_Data.return_type ) {
				this.edit_view_ui_dic.return_type.setValue( res_Data.return_type );

				this.current_edit_record.return_type = res_Data.return_type;
			}

			if ( res_Data.exempt_payment ) {
				this.edit_view_ui_dic.exempt_payment.setValue( res_Data.exempt_payment );

				this.current_edit_record.exempt_payment = res_Data.exempt_payment;
			}

			if ( res_Data.line_9 ) {
				this.edit_view_ui_dic.line_9.setValue( res_Data.line_9 );

				this.current_edit_record.line_9 = res_Data.line_9;
			}

			if ( res_Data.line_10 ) {
				this.edit_view_ui_dic.line_10.setValue( res_Data.line_10 );

				this.current_edit_record.line_10 = res_Data.line_10;
			}

			if ( res_Data.line_11 ) {
				this.edit_view_ui_dic.line_11.setValue( res_Data.line_11 );

				this.current_edit_record.line_11 = res_Data.line_11;
			}

			if ( res_Data.tax_deposited ) {
				this.edit_view_ui_dic.tax_deposited.setValue( res_Data.tax_deposited );

				this.current_edit_record.tax_deposited = res_Data.tax_deposited;
			}
		}
	}

	/* jshint ignore:end */
}