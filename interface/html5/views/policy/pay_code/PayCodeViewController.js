class PayCodeViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#pay_code_view_container',

			type_array: null,
			//pay_type_array: null,
			//wage_source_type_array: null,
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'PayCodeEditView.html';
		this.permission_id = 'pay_code';
		this.viewId = 'PayCode';
		this.script_name = 'PayCodeView';
		this.table_name_key = 'pay_code';
		this.context_menu_name = $.i18n._( 'Pay Code' );
		this.navigation_label = $.i18n._( 'Pay Code' ) + ':';
		this.api = TTAPI.APIPayCode;

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'PayCode' );
	}

	initOptions() {
		var $this = this;
		this.initDropDownOption( 'type' );
		//this.initDropDownOption( 'pay_type' );
		//this.initDropDownOption( 'wage_source_type' );
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: [],
			include: [
				{
					label: $.i18n._( 'Migrate<br>Pay Codes' ),
					id: ContextMenuIconName.migrate_pay_codes,
					group: 'other',
					icon: Icons.wizard
				}
			]
		};

		return context_menu_model;
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_pay_code': { 'label': $.i18n._( 'Pay Code' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIPayCode,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_CODE,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_pay_code = this.edit_view_tab.find( '#tab_pay_code' );

		var tab_pay_code_column1 = tab_pay_code.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_pay_code_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_pay_code_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_pay_code_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		//Code
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'code', width: 150 } );
		this.addEditFieldToColumn( $.i18n._( 'Code' ), form_item_input, tab_pay_code_column1, '' );

		// Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'type_id', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_pay_code_column1 );
		/*
		 // Pay Type
		 form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		 form_item_input.TComboBox( {field: 'pay_type_id', set_empty: false} );
		 form_item_input.setSourceData( Global.addFirstItemToArray( $this.pay_type_array ) );
		 this.addEditFieldToColumn( $.i18n._( 'Pay Type' ), form_item_input, tab_pay_code_column1 );

		 // Wage Source
		 form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		 form_item_input.TComboBox( {field: 'wage_source_type_id', set_empty: false} );
		 form_item_input.setSourceData( Global.addFirstItemToArray( $this.wage_source_type_array ) );
		 this.addEditFieldToColumn( $.i18n._( 'Wage Source' ), form_item_input, tab_pay_code_column1 );

		 //Wage Source Contributing Shift
		 form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		 form_item_input.AComboBox( {
		 api_class: TTAPI.APIContributingShiftPolicy,
		 allow_multiple_selection: false,
		 layout_name: ALayoutIDs.CONTRIBUTING_SHIFT_POLICY,
		 show_search_inputs: true,
		 set_empty: true,
		 field: 'wage_source_contributing_shift_policy_id'} );
		 this.addEditFieldToColumn( $.i18n._( 'Wage Source Contributing Shift Policy' ), form_item_input, tab_pay_code_column1 );

		 //Time Source Contributing Shift
		 form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		 form_item_input.AComboBox( {
		 api_class: TTAPI.APIContributingShiftPolicy,
		 allow_multiple_selection: false,
		 layout_name: ALayoutIDs.CONTRIBUTING_SHIFT_POLICY,
		 show_search_inputs: true,
		 set_empty: true,
		 field: 'time_source_contributing_shift_policy_id'} );
		 this.addEditFieldToColumn( $.i18n._( 'Time Source Contributing Shift Policy' ), form_item_input, tab_pay_code_column1 );

		 // Wage Group
		 form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		 form_item_input.AComboBox( {
		 api_class: TTAPI.APIWageGroup,
		 allow_multiple_selection: false,
		 layout_name: ALayoutIDs.WAGE_GROUP,
		 show_search_inputs: true,
		 set_empty: true,
		 field: 'wage_group_id'} );
		 this.addEditFieldToColumn( $.i18n._( 'Wage Group' ), form_item_input, tab_pay_code_column1 );

		 // Rate
		 form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		 form_item_input.TTextInput( {field: 'rate', width: 100} );
		 this.addEditFieldToColumn( $.i18n._( 'Rate' ), form_item_input, tab_pay_code_column1 );

		 // Deposit Accrual Policy
		 form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		 form_item_input.AComboBox( {
		 api_class: TTAPI.APIAccrualPolicy,
		 allow_multiple_selection: false,
		 layout_name: ALayoutIDs.ACCRUAL_POLICY,
		 show_search_inputs: true,
		 set_empty: true,
		 field: 'accrual_policy_id'} );
		 this.addEditFieldToColumn( $.i18n._( 'Deposit Accrual Policy' ), form_item_input, tab_pay_code_column1, '' );
		 */
		//Pay Formula Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayFormulaPolicy,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_FORMULA_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_formula_policy_id',
			custom_first_label: $.i18n._( '-- Defined By Policy --' ),
			added_items: [
				{ value: TTUUID.zero_id, label: $.i18n._( '-- Defined By Policy --' ) }
			]
		} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Formula Policy' ), form_item_input, tab_pay_code_column1 );

		// Pay Stub Account
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_stub_entry_account_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Stub Account' ), form_item_input, tab_pay_code_column1 );
	}

	buildSearchFields() {

		super.buildSearchFields();
		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Pay Formula Policy' ),
				in_column: 1,
				field: 'pay_formula_policy_id',
				layout_name: ALayoutIDs.PAY_FORMULA_POLICY,
				api_class: TTAPI.APIPayFormulaPolicy,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Pay Stub Account' ),
				in_column: 2,
				field: 'pay_stub_entry_account_id',
				layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
				api_class: TTAPI.APIPayStubEntryAccount,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case ContextMenuIconName.migrate_pay_codes:
				ProgressBar.showOverlay();
				this.onWizardClick();
				break;

		}
	}

	onSaveClick( ignoreWarning ) {
		super.onSaveClick( ignoreWarning );
		Global.clearCache( 'getOptions_columns' ); //Needs to clear cache so if they add a pay code it will immediately appear on all reports in the Display Columns.
	}

	onWizardClick() {
		var $this = this;
		IndexViewController.openWizard( 'PayCodeWizard', null, function() {
//			$this.search();
		} );
	}

}
