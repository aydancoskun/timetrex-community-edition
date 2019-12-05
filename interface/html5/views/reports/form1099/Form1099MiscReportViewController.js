Form1099MiscReportViewController = ReportBaseViewController.extend( {

	_required_files: ['APIForm1099MiscReport', 'APIPayStubEntryAccount'],

	province_array: null,

	state_field_array: null,

	schedule_deposit_array: null,

	initReport: function( options ) {
		this.script_name = 'Form1099MiscReport';
		this.viewId = 'Form1099MiscReport';
		this.context_menu_name = $.i18n._( 'Form 1099-Misc' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'Form1099MiscReportView.html';
		this.api = new (APIFactory.getAPIClass( 'APIForm1099MiscReport' ))();
		this.include_form_setup = true;
	},

	initOptions: function( callBack ) {
		var $this = this;
		var options = [
			{ option_name: 'page_orientation' },
			{ option_name: 'font_size' },
			{ option_name: 'chart_display_mode' },
			{ option_name: 'chart_type' },
			{ option_name: 'templates' },
			{ option_name: 'setup_fields' },
			{ option_name: 'auto_refresh' }
		];

		this.initDropDownOptions( options, function( result ) {

			new (APIFactory.getAPIClass( 'APICompany' ))().getOptions( 'province', 'US', {
				onResult: function( provinceResult ) {

					$this.province_array = Global.buildRecordArray( provinceResult.getResult() );

					callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
				}
			} );

		} );

	},

	onReportMenuClick: function( id ) {
		this.onViewClick( id );
	},

	getCustomContextMenuModel: function () {
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
					icon: Icons.save_setup,
					permission_result: true
				}
			]
		};

		var view_print = {
			label: $.i18n._( 'View' ),
			id: ContextMenuIconName.view_print,
			group: 'form',
			icon: 'view-35x35.png',
			type: RibbonSubMenuType.NAVIGATION,
			items: [
				{
					label: $.i18n._( 'Government (Multiple Employees/Page)' ),
					id: 'pdf_form_government'
				},
				{
					label: $.i18n._( 'Employee (One Employee/Page)' ),
					id: 'pdf_form'
				}
			]
		};

		if ( ( Global.getProductEdition() >= 15 ) ) {
			view_print.items.push({
				label: $.i18n._( 'Publish Employee Forms' ),
				id: 'pdf_form_publish_employee'
			});
		}

		context_menu_model['include'].unshift( view_print );

		return context_menu_model;
	},

	buildFormSetupUI: function() {

		var $this = this;

		var tab3 = this.edit_view_tab.find( '#tab_form_setup' );

		var tab3_column1 = tab3.find( '.first-column' );

		this.edit_view_tabs[3] = [];

		this.edit_view_tabs[3].push( tab3_column1 );

		//Federal Income Tax Withheld (Box 4)
		var v_box = $( '<div class=\'v-box\'></div>' );

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
		v_box.append( '<div class=\'clear-both-div\'></div>' );

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
		v_box = $( '<div class=\'v-box\'></div>' );

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
		v_box.append( '<div class=\'clear-both-div\'></div>' );

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
		v_box = $( '<div class=\'v-box\'></div>' );

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
		v_box.append( '<div class=\'clear-both-div\'></div>' );

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
		other.l4 = {
			include_pay_stub_entry_account: this.current_edit_record.l4_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l4_exclude_pay_stub_entry_account
		};

		other.l6 = {
			include_pay_stub_entry_account: this.current_edit_record.l6_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l6_exclude_pay_stub_entry_account
		};

		other.l7 = {
			include_pay_stub_entry_account: this.current_edit_record.l7_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l7_exclude_pay_stub_entry_account
		};

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
