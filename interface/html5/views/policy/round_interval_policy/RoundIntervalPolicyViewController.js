RoundIntervalPolicyViewController = BaseViewController.extend( {
	el: '#round_interval_policy_view_container',

	_required_files: ['APIRoundIntervalPolicy'],

	punch_type_array: null,
	round_type_array: null,
	condition_type_array: null,
	date_api: null,
	init: function( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'RoundIntervalPolicyEditView.html';
		this.permission_id = 'round_policy';
		this.viewId = 'RoundIntervalPolicy';
		this.script_name = 'RoundIntervalPolicyView';
		this.table_name_key = 'round_interval_policy';
		this.context_menu_name = $.i18n._( 'Rounding Policy' );
		this.navigation_label = $.i18n._( 'Rounding Policy' ) + ':';
		this.api = new ( APIFactory.getAPIClass( 'APIRoundIntervalPolicy' ) )();
		this.date_api = new ( APIFactory.getAPIClass( 'APIDate' ) )();
		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'RoundIntervalPolicy' );

	},

	initOptions: function() {
		this.initDropDownOption( 'punch_type' );
		this.initDropDownOption( 'round_type' );
		this.initDropDownOption( 'condition_type' );
	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		var tab_model = {
			'tab_rounding_policy': { 'label': $.i18n._( 'Rounding Policy' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIRoundIntervalPolicy' ) ),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.ROUND_INTERVAL_POLICY,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_rounding_policy = this.edit_view_tab.find( '#tab_rounding_policy' );

		var tab_rounding_policy_column1 = tab_rounding_policy.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_rounding_policy_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_rounding_policy_column1, '' );
		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_rounding_policy_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		//Punch Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'punch_type_id', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.punch_type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Punch Type' ), form_item_input, tab_rounding_policy_column1 );

		//Round Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'round_type_id', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.round_type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Round Type' ), form_item_input, tab_rounding_policy_column1 );

		// Interval
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'round_interval', mode: 'time_unit', need_parser_sec: true } );

		this.addEditFieldToColumn( $.i18n._( 'Interval' ), form_item_input, tab_rounding_policy_column1, '', null );

		//Grace Period
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'grace', mode: 'time_unit', need_parser_sec: true } );

		this.addEditFieldToColumn( $.i18n._( 'Grace Period' ), form_item_input, tab_rounding_policy_column1, '', null );

		// Strict Schedule
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'strict' } );
		this.addEditFieldToColumn( $.i18n._( 'Strict Schedule' ), form_item_input, tab_rounding_policy_column1, '' );

		//SEPARATED
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Only Round Punches Within The Following Window' ) } );
		this.addEditFieldToColumn( '', form_item_input, tab_rounding_policy_column1, '', null, true, false, 'sp_box' );

		//Window Based On
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'condition_type_id', set_empty: false } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.condition_type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Window Based On' ), form_item_input, tab_rounding_policy_column1, '', null, true );

		// Static Time
		form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );
		form_item_input.TTimePicker( { field: 'condition_static_time' } );

		this.addEditFieldToColumn( $.i18n._( 'Static Time' ), form_item_input, tab_rounding_policy_column1, '', null, true );

		// Static Total Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: 'condition_static_total_time',
			mode: 'time_unit',
			need_parser_sec: true
		} );

		this.addEditFieldToColumn( $.i18n._( 'Static Total Time' ), form_item_input, tab_rounding_policy_column1, '', null, true );

		// Start Window
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'condition_start_window', mode: 'time_unit', need_parser_sec: true } );

		this.addEditFieldToColumn( $.i18n._( 'Start Window' ), form_item_input, tab_rounding_policy_column1, '', null, true );

		// Stop Window
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'condition_stop_window', mode: 'time_unit', need_parser_sec: true } );

		this.addEditFieldToColumn( $.i18n._( 'Stop Window' ), form_item_input, tab_rounding_policy_column1, '', null, true );

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );
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
				label: $.i18n._( 'Punch Type' ),
				in_column: 1,
				field: 'punch_type_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Round Type' ),
				in_column: 1,
				field: 'round_type_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: ( APIFactory.getAPIClass( 'APIUser' ) ),
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
				api_class: ( APIFactory.getAPIClass( 'APIUser' ) ),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	},

	onFormItemChange: function( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

//		switch ( key ) {
//			case 'round_interval':
//			case 'grace':
//			case 'condition_static_total_time':
//			case 'condition_start_window':
//			case 'condition_stop_window':
//				c_value = this.date_api.parseTimeUnit( target.getValue(), {async: false} ).getResult();
//				break;
//		}

		this.current_edit_record[key] = c_value;

		if ( key === 'condition_type_id' ) {
			this.onConditionTypeChange();
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	},

	onConditionTypeChange: function() {

		var condition_type_id = parseInt( this.current_edit_record.condition_type_id );

		var sp_box = this.edit_view_form_item_dic.sp_box;
		var condition_type = this.edit_view_form_item_dic.condition_type_id;

		var static_time = this.edit_view_form_item_dic.condition_static_time;
		var condition_static_total_time = this.edit_view_form_item_dic.condition_static_total_time;
		var condition_start_window = this.edit_view_form_item_dic.condition_start_window;
		var condition_stop_window = this.edit_view_form_item_dic.condition_stop_window;

		if ( Global.getProductEdition() <= 10 ) {
			this.detachElement( 'condition_type_id' );
			this.detachElement( 'sp_box' );
			this.detachElement( 'condition_static_total_time' );
			this.detachElement( 'condition_start_window' );
			this.detachElement( 'condition_stop_window' );
			this.detachElement( 'condition_static_time' );
			return;
		}

		this.detachElement( 'condition_static_total_time' );
		this.detachElement( 'condition_start_window' );
		this.detachElement( 'condition_stop_window' );
		this.detachElement( 'condition_static_time' );

		if ( condition_type_id == 10 || condition_type_id == 20 ) {
			this.attachElement( 'condition_start_window' );
			this.attachElement( 'condition_stop_window' );

		} else if ( condition_type_id == 30 ) {
			this.attachElement( 'condition_static_time' );
			this.attachElement( 'condition_stop_window' );
			this.attachElement( 'condition_start_window' );

		} else if ( condition_type_id == 40 ) {

			this.attachElement( 'condition_static_total_time' );
			this.attachElement( 'condition_stop_window' );
			this.attachElement( 'condition_start_window' );
		}

		this.editFieldResize();

	},

	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );
		this.onConditionTypeChange();
	}

} );
