class Form941ReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {

			return_type_array: null,
			exempt_payment_array: null,
			state_array: null,
			province_array: null,
			schedule_deposit_array: null
		} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'Form941Report';
		this.viewId = 'Form941Report';
		this.context_menu_name = $.i18n._( 'Form 941' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'Form941ReportView.html';
		this.api = TTAPI.APIForm941Report;
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
			{ option_name: 'schedule_deposit' },
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
		var widgetContainer;
		var label;

		//Schedule Depositor
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input = form_item_input.AComboBox( {
			field: 'deposit_schedule',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.OPTION_COLUMN,
			key: 'value'
		} );

		form_item_input.setSourceData( Global.addFirstItemToArray( $this.schedule_deposit_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Schedule Depositor' ), form_item_input, tab3_column1, '' );

		//Total Deposits For This Quarter
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'quarter_deposit' } );
		this.addEditFieldToColumn( $.i18n._( 'Total Deposits For This Quarter' ), form_item_input, tab3_column1 );

		//Wages, tips and other compensation (Line 2)
		var v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'wages_include_pay_stub_entry_account'
		} );

		var form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		var form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'wages_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Wages, tips and other compensation (Line 2)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Income Tax (Line 3)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'income_tax_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'income_tax_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Income Tax (Line 3)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Taxable Social Security Wages (Line 5a)
		v_box = $( '<div class=\'v-box\'></div>' );
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX ); //Selection Type
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'social_security_wages_include_pay_stub_entry_account'
		} );
		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );
		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX ); //Selection
		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'social_security_wages_exclude_pay_stub_entry_account'
		} );
		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );
		v_box.append( form_item );
		this.addEditFieldToColumn( $.i18n._( 'Taxable Social Security Wages (Line 5a)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );


		//Social Security Taxes Withheld
		v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'social_security_tax_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'social_security_tax_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Social Security Taxes Withheld' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Social Security Taxes - Employer
		v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'social_security_tax_employer_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'social_security_tax_employer_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Social Security Employer' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Taxable Social Security Tips (Line 5b)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'social_security_tips_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'social_security_tips_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Taxable Social Security Tips (Line 5b)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Taxable Medicare Wages (Line 5c)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'medicare_wages_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'medicare_wages_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Taxable Medicare Wages (Line 5c)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Medicare Taxes Withheld
		v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'medicare_tax_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'medicare_tax_exclude_pay_stub_entry_account'

		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Medicare Taxes Withheld' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Medicare Taxes - Employer
		v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'medicare_tax_employer_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'medicare_tax_employer_exclude_pay_stub_entry_account'

		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Medicare Employer' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );


		//Credit from Form 5884-C, line 11, for this quarter
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'form_5884c_credit' } );
		this.addEditFieldToColumn( $.i18n._( 'Credit from Form 5884-C on Line 11 (Line 23)' ), form_item_input, tab3_column1 );


		//COVID-19 related settings.
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'COVID-19 Settings' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab3_column1, '', null, true, false, 'separated_2' );


		//Qualified Sick Leave Wages (Line 5a.i.)
		v_box = $( '<div class=\'v-box\'></div>' );
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX ); //Selection Type
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'qualified_sick_leave_wages_include_pay_stub_entry_account'
		} );
		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );
		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX ); //Selection
		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'qualified_sick_leave_wages_exclude_pay_stub_entry_account'
		} );
		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );
		v_box.append( form_item );
		this.addEditFieldToColumn( $.i18n._( 'Qualified Sick Leave Wages (Line 5a.i)\n(Must not be included in Line 5a)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Qualified Family Leave Wages (Line 5a.ii.)
		v_box = $( '<div class=\'v-box\'></div>' );
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX ); //Selection Type
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'qualified_family_leave_wages_include_pay_stub_entry_account'
		} );
		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );
		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX ); //Selection
		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'qualified_family_leave_wages_exclude_pay_stub_entry_account'
		} );
		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );
		v_box.append( form_item );
		this.addEditFieldToColumn( $.i18n._( 'Qualified Family Leave Wages (Line 5a.ii)\n(Must not be included in Line 5a)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );


		//Qualified Employee Retention Credit Wages (Line 11c)
		v_box = $( '<div class=\'v-box\'></div>' );
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX ); //Selection Type
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'qualified_retention_credit_wages_include_pay_stub_entry_account'
		} );
		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );
		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX ); //Selection
		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'qualified_retention_credit_wages_exclude_pay_stub_entry_account'
		} );
		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );
		v_box.append( form_item );
		this.addEditFieldToColumn( $.i18n._( 'Qualified Employee Retention Credit Wages\n(If not received PPP loan)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Qualified Health Plan Expenses Allocable to Sick/Family Leave Wages (For Lines 19/20)
		v_box = $( '<div class=\'v-box\'></div>' );
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX ); //Selection Type
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'qualified_health_plan_expenses_include_pay_stub_entry_account'
		} );
		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );
		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX ); //Selection
		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'qualified_health_plan_expenses_exclude_pay_stub_entry_account'
		} );
		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );
		v_box.append( form_item );
		this.addEditFieldToColumn( $.i18n._( 'Qualified Health Plan Expenses' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Deferred amount of employer share of social security tax
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'deferred_social_security_tax_employer' } );
		this.addEditFieldToColumn( $.i18n._( 'Deferred Employer Social Security Tax (Line 13b)' ), form_item_input, tab3_column1 );

		//Advances from filing Forms 7200 for the quarter.
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'form_7200_advances' } );
		this.addEditFieldToColumn( $.i18n._( 'Total Advances Received from Form(s) 7200 for the Quarter (Line 13f)' ), form_item_input, tab3_column1 );
	}

	getFormSetupData() {
		var other = {};
		other.wages = {
			include_pay_stub_entry_account: this.current_edit_record.wages_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.wages_exclude_pay_stub_entry_account
		};

		other.income_tax = {
			include_pay_stub_entry_account: this.current_edit_record.income_tax_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.income_tax_exclude_pay_stub_entry_account
		};

		other.social_security_wages = {
			include_pay_stub_entry_account: this.current_edit_record.social_security_wages_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.social_security_wages_exclude_pay_stub_entry_account
		};

		other.social_security_tax = {
			include_pay_stub_entry_account: this.current_edit_record.social_security_tax_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.social_security_tax_exclude_pay_stub_entry_account
		};

		other.social_security_tax_employer = {
			include_pay_stub_entry_account: this.current_edit_record.social_security_tax_employer_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.social_security_tax_employer_exclude_pay_stub_entry_account
		};

		other.social_security_tips = {
			include_pay_stub_entry_account: this.current_edit_record.social_security_tips_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.social_security_tips_exclude_pay_stub_entry_account
		};

		other.medicare_wages = {
			include_pay_stub_entry_account: this.current_edit_record.medicare_wages_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.medicare_wages_exclude_pay_stub_entry_account
		};

		other.medicare_tax = {
			include_pay_stub_entry_account: this.current_edit_record.medicare_tax_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.medicare_tax_exclude_pay_stub_entry_account
		};

		other.medicare_tax_employer = {
			include_pay_stub_entry_account: this.current_edit_record.medicare_tax_employer_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.medicare_tax_employer_exclude_pay_stub_entry_account
		};

		other.qualified_sick_leave_wages = {
			include_pay_stub_entry_account: this.current_edit_record.qualified_sick_leave_wages_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.qualified_sick_leave_wages_exclude_pay_stub_entry_account
		};

		other.qualified_family_leave_wages = {
			include_pay_stub_entry_account: this.current_edit_record.qualified_family_leave_wages_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.qualified_family_leave_wages_exclude_pay_stub_entry_account
		};

		other.qualified_retention_credit_wages = {
			include_pay_stub_entry_account: this.current_edit_record.qualified_retention_credit_wages_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.qualified_retention_credit_wages_exclude_pay_stub_entry_account
		};

		other.qualified_health_plan_expenses = {
			include_pay_stub_entry_account: this.current_edit_record.qualified_health_plan_expenses_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.qualified_health_plan_expenses_exclude_pay_stub_entry_account
		};

		other.deposit_schedule = this.current_edit_record.deposit_schedule;
		other.quarter_deposit = this.current_edit_record.quarter_deposit;

		other.deferred_social_security_tax_employer = this.current_edit_record.deferred_social_security_tax_employer;
		other.form_7200_advances = this.current_edit_record.form_7200_advances;
		other.form_5884c_credit = this.current_edit_record.form_5884c_credit;

		return other;
	}

	/* jshint ignore:start */
	setFormSetupData( res_Data ) {

		if ( !res_Data ) {
			this.show_empty_message = true;
		}

		if ( res_Data ) {
			if ( res_Data.wages ) {
				this.edit_view_ui_dic.wages_exclude_pay_stub_entry_account.setValue( res_Data.wages.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.wages_include_pay_stub_entry_account.setValue( res_Data.wages.include_pay_stub_entry_account );

				this.current_edit_record.wages_include_pay_stub_entry_account = res_Data.wages.include_pay_stub_entry_account;
				this.current_edit_record.wages_exclude_pay_stub_entry_account = res_Data.wages.exclude_pay_stub_entry_account;

			}

			if ( res_Data.income_tax ) {
				this.edit_view_ui_dic.income_tax_exclude_pay_stub_entry_account.setValue( res_Data.income_tax.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.income_tax_include_pay_stub_entry_account.setValue( res_Data.income_tax.include_pay_stub_entry_account );

				this.current_edit_record.income_tax_include_pay_stub_entry_account = res_Data.income_tax.include_pay_stub_entry_account;
				this.current_edit_record.income_tax_exclude_pay_stub_entry_account = res_Data.income_tax.exclude_pay_stub_entry_account;
			}

			if ( res_Data.social_security_wages ) {
				this.edit_view_ui_dic.social_security_wages_exclude_pay_stub_entry_account.setValue( res_Data.social_security_wages.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.social_security_wages_include_pay_stub_entry_account.setValue( res_Data.social_security_wages.include_pay_stub_entry_account );

				this.current_edit_record.social_security_wages_include_pay_stub_entry_account = res_Data.social_security_wages.include_pay_stub_entry_account;
				this.current_edit_record.social_security_wages_exclude_pay_stub_entry_account = res_Data.social_security_wages.exclude_pay_stub_entry_account;
			}

			if ( res_Data.social_security_tax ) {
				this.edit_view_ui_dic.social_security_tax_exclude_pay_stub_entry_account.setValue( res_Data.social_security_tax.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.social_security_tax_include_pay_stub_entry_account.setValue( res_Data.social_security_tax.include_pay_stub_entry_account );

				this.current_edit_record.social_security_tax_include_pay_stub_entry_account = res_Data.social_security_tax.include_pay_stub_entry_account;
				this.current_edit_record.social_security_tax_exclude_pay_stub_entry_account = res_Data.social_security_tax.exclude_pay_stub_entry_account;
			}

			if ( res_Data.social_security_tax_employer ) {
				this.edit_view_ui_dic.social_security_tax_employer_exclude_pay_stub_entry_account.setValue( res_Data.social_security_tax_employer.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.social_security_tax_employer_include_pay_stub_entry_account.setValue( res_Data.social_security_tax_employer.include_pay_stub_entry_account );

				this.current_edit_record.social_security_tax_employer_include_pay_stub_entry_account = res_Data.social_security_tax_employer.include_pay_stub_entry_account;
				this.current_edit_record.social_security_tax_employer_exclude_pay_stub_entry_account = res_Data.social_security_tax_employer.exclude_pay_stub_entry_account;
			}

			if ( res_Data.social_security_tips ) {
				this.edit_view_ui_dic.social_security_tips_exclude_pay_stub_entry_account.setValue( res_Data.social_security_tips.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.social_security_tips_include_pay_stub_entry_account.setValue( res_Data.social_security_tips.include_pay_stub_entry_account );

				this.current_edit_record.social_security_tips_include_pay_stub_entry_account = res_Data.social_security_tips.include_pay_stub_entry_account;
				this.current_edit_record.social_security_tips_exclude_pay_stub_entry_account = res_Data.social_security_tips.exclude_pay_stub_entry_account;
			}

			if ( res_Data.medicare_wages ) {
				this.edit_view_ui_dic.medicare_wages_exclude_pay_stub_entry_account.setValue( res_Data.medicare_wages.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.medicare_wages_include_pay_stub_entry_account.setValue( res_Data.medicare_wages.include_pay_stub_entry_account );

				this.current_edit_record.medicare_wages_include_pay_stub_entry_account = res_Data.medicare_wages.include_pay_stub_entry_account;
				this.current_edit_record.medicare_wages_exclude_pay_stub_entry_account = res_Data.medicare_wages.exclude_pay_stub_entry_account;
			}

			if ( res_Data.medicare_tax ) {
				this.edit_view_ui_dic.medicare_tax_exclude_pay_stub_entry_account.setValue( res_Data.medicare_tax.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.medicare_tax_include_pay_stub_entry_account.setValue( res_Data.medicare_tax.include_pay_stub_entry_account );

				this.current_edit_record.medicare_tax_include_pay_stub_entry_account = res_Data.medicare_tax.include_pay_stub_entry_account;
				this.current_edit_record.medicare_tax_exclude_pay_stub_entry_account = res_Data.medicare_tax.exclude_pay_stub_entry_account;
			}

			if ( res_Data.medicare_tax_employer ) {
				this.edit_view_ui_dic.medicare_tax_employer_exclude_pay_stub_entry_account.setValue( res_Data.medicare_tax_employer.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.medicare_tax_employer_include_pay_stub_entry_account.setValue( res_Data.medicare_tax_employer.include_pay_stub_entry_account );

				this.current_edit_record.medicare_tax_employer_include_pay_stub_entry_account = res_Data.medicare_tax_employer.include_pay_stub_entry_account;
				this.current_edit_record.medicare_tax_employer_exclude_pay_stub_entry_account = res_Data.medicare_tax_employer.exclude_pay_stub_entry_account;
			}

			if ( res_Data.qualified_sick_leave_wages ) {
				this.edit_view_ui_dic.qualified_sick_leave_wages_exclude_pay_stub_entry_account.setValue( res_Data.qualified_sick_leave_wages.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.qualified_sick_leave_wages_include_pay_stub_entry_account.setValue( res_Data.qualified_sick_leave_wages.include_pay_stub_entry_account );

				this.current_edit_record.qualified_sick_leave_wages_include_pay_stub_entry_account = res_Data.qualified_sick_leave_wages.include_pay_stub_entry_account;
				this.current_edit_record.qualified_sick_leave_wages_exclude_pay_stub_entry_account = res_Data.qualified_sick_leave_wages.exclude_pay_stub_entry_account;
			}

			if ( res_Data.qualified_family_leave_wages ) {
				this.edit_view_ui_dic.qualified_family_leave_wages_exclude_pay_stub_entry_account.setValue( res_Data.qualified_family_leave_wages.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.qualified_family_leave_wages_include_pay_stub_entry_account.setValue( res_Data.qualified_family_leave_wages.include_pay_stub_entry_account );

				this.current_edit_record.qualified_family_leave_wages_include_pay_stub_entry_account = res_Data.qualified_family_leave_wages.include_pay_stub_entry_account;
				this.current_edit_record.qualified_family_leave_wages_exclude_pay_stub_entry_account = res_Data.qualified_family_leave_wages.exclude_pay_stub_entry_account;
			}

			if ( res_Data.qualified_retention_credit_wages ) {
				this.edit_view_ui_dic.qualified_retention_credit_wages_exclude_pay_stub_entry_account.setValue( res_Data.qualified_retention_credit_wages.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.qualified_retention_credit_wages_include_pay_stub_entry_account.setValue( res_Data.qualified_retention_credit_wages.include_pay_stub_entry_account );

				this.current_edit_record.qualified_retention_credit_wages_include_pay_stub_entry_account = res_Data.qualified_retention_credit_wages.include_pay_stub_entry_account;
				this.current_edit_record.qualified_retention_credit_wages_exclude_pay_stub_entry_account = res_Data.qualified_retention_credit_wages.exclude_pay_stub_entry_account;
			}

			if ( res_Data.qualified_health_plan_expenses ) {
				this.edit_view_ui_dic.qualified_health_plan_expenses_exclude_pay_stub_entry_account.setValue( res_Data.qualified_health_plan_expenses.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.qualified_health_plan_expenses_include_pay_stub_entry_account.setValue( res_Data.qualified_health_plan_expenses.include_pay_stub_entry_account );

				this.current_edit_record.qualified_health_plan_expenses_include_pay_stub_entry_account = res_Data.qualified_health_plan_expenses.include_pay_stub_entry_account;
				this.current_edit_record.qualified_health_plan_expenses_exclude_pay_stub_entry_account = res_Data.qualified_health_plan_expenses.exclude_pay_stub_entry_account;
			}

			if ( res_Data.quarter_deposit ) {
				this.edit_view_ui_dic.quarter_deposit.setValue( res_Data.quarter_deposit );
				this.current_edit_record.quarter_deposit = res_Data.quarter_deposit;
			}

			if ( res_Data.deposit_schedule ) {
				this.edit_view_ui_dic.deposit_schedule.setValue( res_Data.deposit_schedule );
				this.current_edit_record.deposit_schedule = res_Data.deposit_schedule;
			}

			if ( res_Data.deferred_social_security_tax_employer ) {
				this.edit_view_ui_dic.deferred_social_security_tax_employer.setValue( res_Data.deferred_social_security_tax_employer );
				this.current_edit_record.deferred_social_security_tax_employer = res_Data.deferred_social_security_tax_employer;
			}

			if ( res_Data.form_7200_advances ) {
				this.edit_view_ui_dic.form_7200_advances.setValue( res_Data.form_7200_advances );
				this.current_edit_record.form_7200_advances = res_Data.form_7200_advances;
			}

			if ( res_Data.form_5884c_credit ) {
				this.edit_view_ui_dic.form_5884c_credit.setValue( res_Data.form_5884c_credit );
				this.current_edit_record.form_5884c_credit = res_Data.form_5884c_credit;
			}
		}
	}

	/* jshint ignore:end */

}
