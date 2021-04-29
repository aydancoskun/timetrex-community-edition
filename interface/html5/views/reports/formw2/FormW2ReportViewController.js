FormW2ReportViewController = ReportBaseViewController.extend( {

	_required_files: ['APIFormW2Report', 'APIPayStubEntryAccount', 'APICompanyDeduction', 'APICurrency'],

	kind_of_employer_array: null,

	initReport: function( options ) {
		this.script_name = 'FormW2Report';
		this.viewId = 'FormW2Report';
		this.context_menu_name = $.i18n._( 'Form W2/W3' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'FormW2ReportView.html';
		this.api = new ( APIFactory.getAPIClass( 'APIFormW2Report' ) )();
		this.include_form_setup = true;
	},

	getCustomContextMenuModel: function() {
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
					label: $.i18n._( 'eFile' ),
					id: ContextMenuIconName.e_file,
					group: 'form',
					icon: Icons.e_file
				},
				{
					label: $.i18n._( 'Save Setup' ),
					id: ContextMenuIconName.save_setup,
					group: 'form',
					icon: Icons.save_setup
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
			],
			permission_result: true,
			permission: true
		};

		if ( ( Global.getProductEdition() >= 15 ) ) {
			view_print.items.push( {
				label: $.i18n._( 'Publish Employee Forms' ),
				id: 'pdf_form_publish_employee'
			} );
		}

		context_menu_model.include.unshift( view_print );

		return context_menu_model;
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
			{ option_name: 'kind_of_employer' },
			{ option_name: 'auto_refresh' }
		];

		this.initDropDownOptions( options, function( result ) {
			callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
		} );
	},

	onReportMenuClick: function( id ) {
		this.onViewClick( id );
	},

	buildFormSetupUI: function() {

		var $this = this;

		var tab3 = this.edit_view_tab.find( '#tab_form_setup' );

		var tab3_column1 = tab3.find( '.first-column' );

		this.edit_view_tabs[3] = [];

		this.edit_view_tabs[3].push( tab3_column1 );

		//Kind of Employer
		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'kind_of_employer', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.kind_of_employer_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Kind of Employer' ), form_item_input, tab3_column1 );

		//Wages, Tips, Other Compensation (Box 1)
		var v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l1_include_pay_stub_entry_account'
		} );

		var form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		var form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l1_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Wages, Tips, Other Compensation (Box 1)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Federal Income Tax Withheld (Box 2)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l2_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l2_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Federal Income Tax Withheld (Box 2)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Social Security Wages (Box 3)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l3_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l3_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Social Security Wages (Box 3)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Social Security Tax Withheld (Box 4)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l4_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l4_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Social Security Tax Withheld (Box 4)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Medicare Wages and Tips (Box 5)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l5_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l5_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Medicare Wages and Tips (Box 5)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Medicare Tax Withheld (Box 6)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l6_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l6_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Medicare Tax Withheld (Box 6)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Social Security Tips (Box 7)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l7_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l7_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Social Security Tips (Box 7)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Allocated Tips (Box 8)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l8_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l8_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Allocated Tips (Box 8)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Dependent Care Benefits (Box 10)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l10_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l10_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Dependent Care Benefits (Box 10)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Nonqualified Plans (Box 11)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l11_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l11_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Nonqualified Plans (Box 11)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Box 12a:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l12a_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l12a_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		var custom_label_widget = $( '<div class=\'h-box\'></div>' );
		var label = $( '<span class="edit-view-form-item-label"></span>' );
		var box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l12a_code', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target );
		} );

		label.text( $.i18n._( 'Box 12a: Code' ) + ': ' );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box 12b:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l12b_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l12b_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l12b_code', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target );
		} );

		label.text( $.i18n._( 'Box 12b: Code' ) + ': ' );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box 12c:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l12c_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l12c_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l12c_code', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target );
		} );

		label.text( $.i18n._( 'Box 12c: Code' ) + ': ' );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box 12d:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l12d_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l12d_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l12d_code', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target );
		} );

		label.text( $.i18n._( 'Box 12d: Code' ) + ': ' );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box 13 (Retirement Plan)
		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APICompanyDeduction' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.COMPANY_DEDUCTION,
			show_search_inputs: true,
			set_empty: true,
			field: 'l13b_company_deduction'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Retirement Plans (Box 13)' ), form_item_input, tab3_column1 );

		//Box 14a:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l14a_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l14a_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l14a_name', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target );
		} );

		label.text( $.i18n._( 'Box 14 (Other): Name' ) + ': ' );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box 14b:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l14b_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l14b_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l14b_name', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target );
		} );

		label.text( $.i18n._( 'Box 14 (Other): Name' ) + ': ' );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box 14c:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l14c_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l14c_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l14c_name', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target );
		} );

		label.text( $.i18n._( 'Box 14 (Other): Name' ) + ': ' );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box 14c:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l14d_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIPayStubEntryAccount' ) ),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'l14d_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l14d_name', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target );
		} );

		label.text( $.i18n._( 'Box 14 (Other): Name' ) + ': ' );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );
	},

	getFormSetupData: function() {
		var other = {};

		other.kind_of_employer = this.current_edit_record.kind_of_employer;

		other.l1 = {
			include_pay_stub_entry_account: this.current_edit_record.l1_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l1_exclude_pay_stub_entry_account
		};

		other.l2 = {
			include_pay_stub_entry_account: this.current_edit_record.l2_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l2_exclude_pay_stub_entry_account
		};

		other.l3 = {
			include_pay_stub_entry_account: this.current_edit_record.l3_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l3_exclude_pay_stub_entry_account
		};

		other.l4 = {
			include_pay_stub_entry_account: this.current_edit_record.l4_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l4_exclude_pay_stub_entry_account
		};

		other.l5 = {
			include_pay_stub_entry_account: this.current_edit_record.l5_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l5_exclude_pay_stub_entry_account
		};

		other.l6 = {
			include_pay_stub_entry_account: this.current_edit_record.l6_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l6_exclude_pay_stub_entry_account
		};

		other.l7 = {
			include_pay_stub_entry_account: this.current_edit_record.l7_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l7_exclude_pay_stub_entry_account
		};

		other.l8 = {
			include_pay_stub_entry_account: this.current_edit_record.l8_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l8_exclude_pay_stub_entry_account
		};

		other.l10 = {
			include_pay_stub_entry_account: this.current_edit_record.l10_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l10_exclude_pay_stub_entry_account
		};

		other.l11 = {
			include_pay_stub_entry_account: this.current_edit_record.l11_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l11_exclude_pay_stub_entry_account
		};

		other.l12a = {
			include_pay_stub_entry_account: this.current_edit_record.l12a_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l12a_exclude_pay_stub_entry_account
		};

		other.l12b = {
			include_pay_stub_entry_account: this.current_edit_record.l12b_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l12b_exclude_pay_stub_entry_account
		};

		other.l12c = {
			include_pay_stub_entry_account: this.current_edit_record.l12c_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l12c_exclude_pay_stub_entry_account
		};

		other.l12d = {
			include_pay_stub_entry_account: this.current_edit_record.l12d_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l12d_exclude_pay_stub_entry_account
		};

		other.l13b = {
			company_deduction: this.current_edit_record.l13b_company_deduction
		};

		other.l14a = {
			include_pay_stub_entry_account: this.current_edit_record.l14a_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l14a_exclude_pay_stub_entry_account
		};

		other.l14b = {
			include_pay_stub_entry_account: this.current_edit_record.l14b_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l14b_exclude_pay_stub_entry_account
		};

		other.l14c = {
			include_pay_stub_entry_account: this.current_edit_record.l14c_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l14c_exclude_pay_stub_entry_account
		};

		other.l14d = {
			include_pay_stub_entry_account: this.current_edit_record.l14d_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l14d_exclude_pay_stub_entry_account
		};

		other.l12a_code = this.current_edit_record.l12a_code;
		other.l12b_code = this.current_edit_record.l12b_code;
		other.l12c_code = this.current_edit_record.l12c_code;
		other.l12d_code = this.current_edit_record.l12d_code;
		other.l14a_name = this.current_edit_record.l14a_name;
		other.l14b_name = this.current_edit_record.l14b_name;
		other.l14c_name = this.current_edit_record.l14c_name;
		other.l14d_name = this.current_edit_record.l14d_name;

		return other;
	},
	/* jshint ignore:start */
	setFormSetupData: function( res_Data ) {

		if ( !res_Data ) {
			this.show_empty_message = true;
		}

		if ( res_Data ) {
			if ( res_Data.l1 ) {
				this.edit_view_ui_dic.l1_exclude_pay_stub_entry_account.setValue( res_Data.l1.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.l1_include_pay_stub_entry_account.setValue( res_Data.l1.include_pay_stub_entry_account );
				this.current_edit_record.l1_include_pay_stub_entry_account = res_Data.l1.include_pay_stub_entry_account;
				this.current_edit_record.l1_exclude_pay_stub_entry_account = res_Data.l1.exclude_pay_stub_entry_account;
			}

			if ( res_Data.l2 ) {
				this.edit_view_ui_dic.l2_exclude_pay_stub_entry_account.setValue( res_Data.l2.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.l2_include_pay_stub_entry_account.setValue( res_Data.l2.include_pay_stub_entry_account );
				this.current_edit_record.l2_include_pay_stub_entry_account = res_Data.l2.include_pay_stub_entry_account;
				this.current_edit_record.l2_exclude_pay_stub_entry_account = res_Data.l2.exclude_pay_stub_entry_account;
			}

			if ( res_Data.l3 ) {
				this.edit_view_ui_dic.l3_exclude_pay_stub_entry_account.setValue( res_Data.l3.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.l3_include_pay_stub_entry_account.setValue( res_Data.l3.include_pay_stub_entry_account );
				this.current_edit_record.l3_include_pay_stub_entry_account = res_Data.l3.include_pay_stub_entry_account;
				this.current_edit_record.l3_exclude_pay_stub_entry_account = res_Data.l3.exclude_pay_stub_entry_account;
			}

			if ( res_Data.l4 ) {
				this.edit_view_ui_dic.l4_exclude_pay_stub_entry_account.setValue( res_Data.l4.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.l4_include_pay_stub_entry_account.setValue( res_Data.l4.include_pay_stub_entry_account );
				this.current_edit_record.l4_include_pay_stub_entry_account = res_Data.l4.include_pay_stub_entry_account;
				this.current_edit_record.l4_exclude_pay_stub_entry_account = res_Data.l4.exclude_pay_stub_entry_account;
			}

			if ( res_Data.l5 ) {
				this.edit_view_ui_dic.l5_exclude_pay_stub_entry_account.setValue( res_Data.l5.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.l5_include_pay_stub_entry_account.setValue( res_Data.l5.include_pay_stub_entry_account );
				this.current_edit_record.l5_include_pay_stub_entry_account = res_Data.l5.include_pay_stub_entry_account;
				this.current_edit_record.l5_exclude_pay_stub_entry_account = res_Data.l5.exclude_pay_stub_entry_account;
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

			if ( res_Data.l8 ) {
				this.edit_view_ui_dic.l8_exclude_pay_stub_entry_account.setValue( res_Data.l8.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.l8_include_pay_stub_entry_account.setValue( res_Data.l8.include_pay_stub_entry_account );
				this.current_edit_record.l8_include_pay_stub_entry_account = res_Data.l8.include_pay_stub_entry_account;
				this.current_edit_record.l8_exclude_pay_stub_entry_account = res_Data.l8.exclude_pay_stub_entry_account;
			}

			if ( res_Data.l10 ) {
				this.edit_view_ui_dic.l10_exclude_pay_stub_entry_account.setValue( res_Data.l10.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.l10_include_pay_stub_entry_account.setValue( res_Data.l10.include_pay_stub_entry_account );
				this.current_edit_record.l10_include_pay_stub_entry_account = res_Data.l10.include_pay_stub_entry_account;
				this.current_edit_record.l10_exclude_pay_stub_entry_account = res_Data.l10.exclude_pay_stub_entry_account;
			}

			if ( res_Data.l11 ) {
				this.edit_view_ui_dic.l11_exclude_pay_stub_entry_account.setValue( res_Data.l11.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.l11_include_pay_stub_entry_account.setValue( res_Data.l11.include_pay_stub_entry_account );
				this.current_edit_record.l11_include_pay_stub_entry_account = res_Data.l11.include_pay_stub_entry_account;
				this.current_edit_record.l11_exclude_pay_stub_entry_account = res_Data.l11.exclude_pay_stub_entry_account;
			}

			if ( res_Data.l12a ) {
				this.edit_view_ui_dic.l12a_exclude_pay_stub_entry_account.setValue( res_Data.l12a.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.l12a_include_pay_stub_entry_account.setValue( res_Data.l12a.include_pay_stub_entry_account );
				this.current_edit_record.l12a_include_pay_stub_entry_account = res_Data.l12a.include_pay_stub_entry_account;
				this.current_edit_record.l12a_exclude_pay_stub_entry_account = res_Data.l12a.exclude_pay_stub_entry_account;
			}

			if ( res_Data.l12b ) {
				this.edit_view_ui_dic.l12b_exclude_pay_stub_entry_account.setValue( res_Data.l12b.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.l12b_include_pay_stub_entry_account.setValue( res_Data.l12b.include_pay_stub_entry_account );
				this.current_edit_record.l12b_include_pay_stub_entry_account = res_Data.l12b.include_pay_stub_entry_account;
				this.current_edit_record.l12b_exclude_pay_stub_entry_account = res_Data.l12b.exclude_pay_stub_entry_account;
			}

			if ( res_Data.l12c ) {
				this.edit_view_ui_dic.l12c_exclude_pay_stub_entry_account.setValue( res_Data.l12c.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.l12c_include_pay_stub_entry_account.setValue( res_Data.l12c.include_pay_stub_entry_account );
				this.current_edit_record.l12c_include_pay_stub_entry_account = res_Data.l12c.include_pay_stub_entry_account;
				this.current_edit_record.l12c_exclude_pay_stub_entry_account = res_Data.l12c.exclude_pay_stub_entry_account;
			}

			if ( res_Data.l12d ) {
				this.edit_view_ui_dic.l12d_exclude_pay_stub_entry_account.setValue( res_Data.l12d.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.l12d_include_pay_stub_entry_account.setValue( res_Data.l12d.include_pay_stub_entry_account );
				this.current_edit_record.l12d_include_pay_stub_entry_account = res_Data.l12d.include_pay_stub_entry_account;
				this.current_edit_record.l12d_exclude_pay_stub_entry_account = res_Data.l12d.exclude_pay_stub_entry_account;
			}

			if ( res_Data.l13b ) {
				this.edit_view_ui_dic.l13b_company_deduction.setValue( res_Data.l13b.company_deduction );
				this.current_edit_record.l13b_company_deduction = res_Data.l13b.company_deduction;
			}

			if ( res_Data.l14a ) {
				this.edit_view_ui_dic.l14a_exclude_pay_stub_entry_account.setValue( res_Data.l14a.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.l14a_include_pay_stub_entry_account.setValue( res_Data.l14a.include_pay_stub_entry_account );
				this.current_edit_record.l14a_include_pay_stub_entry_account = res_Data.l14a.include_pay_stub_entry_account;
				this.current_edit_record.l14a_exclude_pay_stub_entry_account = res_Data.l14a.exclude_pay_stub_entry_account;
			}

			if ( res_Data.l14b ) {
				this.edit_view_ui_dic.l14b_exclude_pay_stub_entry_account.setValue( res_Data.l14b.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.l14b_include_pay_stub_entry_account.setValue( res_Data.l14b.include_pay_stub_entry_account );
				this.current_edit_record.l14b_include_pay_stub_entry_account = res_Data.l14b.include_pay_stub_entry_account;
				this.current_edit_record.l14b_exclude_pay_stub_entry_account = res_Data.l14b.exclude_pay_stub_entry_account;
			}

			if ( res_Data.l14c ) {
				this.edit_view_ui_dic.l14c_exclude_pay_stub_entry_account.setValue( res_Data.l14c.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.l14c_include_pay_stub_entry_account.setValue( res_Data.l14c.include_pay_stub_entry_account );
				this.current_edit_record.l14c_include_pay_stub_entry_account = res_Data.l14c.include_pay_stub_entry_account;
				this.current_edit_record.l14c_exclude_pay_stub_entry_account = res_Data.l14c.exclude_pay_stub_entry_account;
			}

			if ( res_Data.l14d ) {
				this.edit_view_ui_dic.l14d_exclude_pay_stub_entry_account.setValue( res_Data.l14d.exclude_pay_stub_entry_account );
				this.edit_view_ui_dic.l14d_include_pay_stub_entry_account.setValue( res_Data.l14d.include_pay_stub_entry_account );
				this.current_edit_record.l14d_include_pay_stub_entry_account = res_Data.l14d.include_pay_stub_entry_account;
				this.current_edit_record.l14d_exclude_pay_stub_entry_account = res_Data.l14d.exclude_pay_stub_entry_account;
			}

			if ( res_Data.l12a_code ) {
				this.edit_view_ui_dic.l12a_code.setValue( res_Data.l12a_code );
				this.current_edit_record.l12a_code = res_Data.l12a_code;
			}

			if ( res_Data.l12a_code ) {
				this.edit_view_ui_dic.l12b_code.setValue( res_Data.l12b_code );
				this.current_edit_record.l12b_code = res_Data.l12b_code;
			}

			if ( res_Data.l12c_code ) {
				this.edit_view_ui_dic.l12c_code.setValue( res_Data.l12c_code );
				this.current_edit_record.l12c_code = res_Data.l12c_code;
			}

			if ( res_Data.l12d_code ) {
				this.edit_view_ui_dic.l12d_code.setValue( res_Data.l12d_code );
				this.current_edit_record.l12d_code = res_Data.l12d_code;
			}

			if ( res_Data.l14a_name ) {
				this.edit_view_ui_dic.l14a_name.setValue( res_Data.l14a_name );
				this.current_edit_record.l14a_name = res_Data.l14a_name;
			}

			if ( res_Data.l14b_name ) {
				this.edit_view_ui_dic.l14b_name.setValue( res_Data.l14b_name );
				this.current_edit_record.l14b_name = res_Data.l14b_name;
			}

			if ( res_Data.l14c_name ) {
				this.edit_view_ui_dic.l14c_name.setValue( res_Data.l14c_name );
				this.current_edit_record.l14c_name = res_Data.l14c_name;
			}

			if ( res_Data.l14d_name ) {
				this.edit_view_ui_dic.l14d_name.setValue( res_Data.l14d_name );
				this.current_edit_record.l14d_name = res_Data.l14d_name;
			}

		}
	}
	/* jshint ignore:end */
} );
