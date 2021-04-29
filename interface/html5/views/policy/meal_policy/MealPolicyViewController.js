class MealPolicyViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#meal_policy_view_container',

			type_array: null,
			auto_detect_type_array: null,

			date_api: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'MealPolicyEditView.html';
		this.permission_id = 'meal_policy';
		this.viewId = 'MealPolicy';
		this.script_name = 'MealPolicyView';
		this.table_name_key = 'meal_policy';
		this.context_menu_name = $.i18n._( 'Meal Policy' );
		this.navigation_label = $.i18n._( 'Meal Policy' ) + ':';
		this.api = TTAPI.APIMealPolicy;
		this.date_api = TTAPI.APITTDate;
		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'MealPolicy' );
	}

	initOptions() {
		var $this = this;
		this.initDropDownOption( 'type' );
		this.initDropDownOption( 'auto_detect_type' );
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_meal_policy': { 'label': $.i18n._( 'Meal Policy' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIMealPolicy,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.MEAL_POLICY,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_meal_policy = this.edit_view_tab.find( '#tab_meal_policy' );

		var tab_meal_policy_column1 = tab_meal_policy.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_meal_policy_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_meal_policy_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_meal_policy_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		// Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'type_id', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_meal_policy_column1 );

		//Active After

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'trigger_time', mode: 'time_unit', need_parser_sec: true } );

		this.addEditFieldToColumn( $.i18n._( 'Active After' ), form_item_input, tab_meal_policy_column1, '', null );

		// Meal Time
		// Deduction/Addition Time

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'amount', mode: 'time_unit', need_parser_sec: true } );

		this.addEditFieldToColumn( $.i18n._( 'Deduction/Addition Time' ), form_item_input, tab_meal_policy_column1, '', null, true );

		// Auto-Detect Meals By

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'auto_detect_type_id', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.auto_detect_type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Auto-Detect Meals By' ), form_item_input, tab_meal_policy_column1 );

		// Minimum Punch Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'minimum_punch_time', mode: 'time_unit', need_parser_sec: true } );

		this.addEditFieldToColumn( $.i18n._( 'Minimum Punch Time' ), form_item_input, tab_meal_policy_column1, '', null, true );

		// Maximum Punch Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'maximum_punch_time', mode: 'time_unit', need_parser_sec: true } );

		this.addEditFieldToColumn( $.i18n._( 'Maximum Punch Time' ), form_item_input, tab_meal_policy_column1, '', null, true );

		// Start Window
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'start_window', mode: 'time_unit', need_parser_sec: true } );

		this.addEditFieldToColumn( $.i18n._( 'Start Window' ), form_item_input, tab_meal_policy_column1, '', null, true );

		// Window Length

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'window_length', mode: 'time_unit', need_parser_sec: true } );

		this.addEditFieldToColumn( $.i18n._( 'Window Length' ), form_item_input, tab_meal_policy_column1, '', null, true );

		// Include Any Punched Time for Meal
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'include_lunch_punch_time' } );
		this.addEditFieldToColumn( $.i18n._( 'Include Any Punched Time for Meal' ), form_item_input, tab_meal_policy_column1, '', null, true );

		//Pay Code
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayCode,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_CODE,
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_code_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Code' ), form_item_input, tab_meal_policy_column1 );

		//Pay Formula Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayFormulaPolicy,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_FORMULA_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_formula_policy_id',
			custom_first_label: $.i18n._( '-- Defined By Pay Code --' ),
			added_items: [
				{ value: TTUUID.zero_id, label: $.i18n._( '-- Defined By Pay Code --' ) }
			]
		} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Formula Policy' ), form_item_input, tab_meal_policy_column1 );
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
				label: $.i18n._( 'Pay Code' ),
				in_column: 1,
				field: 'pay_code_id',
				layout_name: ALayoutIDs.PAY_CODE,
				api_class: TTAPI.APIPayCode,
				multiple: true,
				basic_search: true,
				adv_search: false,
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

	onFormItemChange( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

//		switch ( key ) {
//			case 'trigger_time':
//			case 'amount':
//			case 'minimum_punch_time':
//			case 'maximum_punch_time':
//			case 'window_length':
//			case 'start_window':
//				c_value = this.date_api.parseTimeUnit( target.getValue(), {async: false} ).getResult();
//				break;
//		}

		this.current_edit_record[key] = c_value;

		if ( key === 'type_id' ) {
			this.onTypeChange();
		} else if ( key === 'auto_detect_type_id' ) {
			this.onAutoDetectTypeChange();
		}

		this.editFieldResize( 0 );

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	setEditViewDataDone() {

		super.setEditViewDataDone();
		this.onTypeChange();

		this.onAutoDetectTypeChange();

		this.editFieldResize( 0 );
	}

	onTypeChange() {

		if ( this.current_edit_record['type_id'] == 10 || this.current_edit_record['type_id'] == 15 ) {

			this.edit_view_form_item_dic['amount'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Deduction/Addition Time' ) + ': ' );
			this.attachElement( 'include_lunch_punch_time' );

		} else if ( this.current_edit_record['type_id'] == 20 ) {
			this.edit_view_form_item_dic['amount'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Meal Time' ) + ': ' );
			this.detachElement( 'include_lunch_punch_time' );
		} else {
			this.edit_view_form_item_dic['amount'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Deduction/Addition Time' ) + ': ' );
			this.attachElement( 'include_lunch_punch_time' );
		}

		this.editFieldResize();
	}

	onAutoDetectTypeChange() {

		if ( this.current_edit_record['auto_detect_type_id'] == 10 ) {
			this.attachElement( 'start_window' );
			this.attachElement( 'window_length' );
			this.detachElement( 'minimum_punch_time' );
			this.detachElement( 'maximum_punch_time' );

		} else if ( this.current_edit_record['auto_detect_type_id'] == 20 ) {
			this.detachElement( 'start_window' );
			this.detachElement( 'window_length' );
			this.attachElement( 'minimum_punch_time' );
			this.attachElement( 'maximum_punch_time' );

		} else {
			this.attachElement( 'start_window' );
			this.attachElement( 'window_length' );
			this.detachElement( 'minimum_punch_time' );
			this.detachElement( 'maximum_punch_time' );

		}

		this.editFieldResize();
	}

}
