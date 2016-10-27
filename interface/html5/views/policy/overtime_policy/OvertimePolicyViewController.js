OvertimePolicyViewController = BaseViewController.extend( {
	el: '#overtime_policy_view_container',
	type_array: null,
	initialize: function() {
		this._super( 'initialize' );
		this.edit_view_tpl = 'OvertimePolicyEditView.html';
		this.permission_id = 'over_time_policy';
		this.viewId = 'OvertimePolicy';
		this.script_name = 'OvertimePolicyView';
		this.table_name_key = 'over_time_policy';
		this.context_menu_name = $.i18n._( 'Overtime Policy' );
		this.navigation_label = $.i18n._( 'Overtime Policy' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIOvertimePolicy' ))();

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'OvertimePolicy' );

	},

	initOptions: function() {
		var $this = this;
		this.initDropDownOption( 'type' );
	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		var tab_0_label = this.edit_view.find( 'a[ref=tab0]' );
		var tab_1_label = this.edit_view.find( 'a[ref=tab1]' );
		tab_0_label.text( $.i18n._( 'OverTime Policy' ) );
		tab_1_label.text( $.i18n._( 'Audit' ) );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIOvertimePolicy' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.OVER_TIME_POLICY,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();


		//Tab 0 start

		var tab0 = this.edit_view_tab.find( '#tab0' );

		var tab0_column1 = tab0.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab0_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'name', width: 359} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab0_column1, '' );

		// Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {field: 'type_id', set_empty: false} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab0_column1 );

		// Active After

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {field: 'trigger_time', width: 149, need_parser_sec: true} );

		var widgetContainer = $( "<div class='widget-h-box'></div>" );
		var label = $( "<span class='widget-right-label'> " + $.i18n._('ie') + ' : '+ LocalCacheData.getLoginUserPreference().time_unit_format_display + "</span>" );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Active After' ), form_item_input, tab0_column1, '', widgetContainer );

		// Rate
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'rate', width: 359} );
		this.addEditFieldToColumn( $.i18n._( 'Rate' ), form_item_input, tab0_column1 );

		// Wage Group

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIWageGroup' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.WAGE_GROUP,
			show_search_inputs: true,
			set_default: true,
			field: 'wage_group_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Wage Group' ), form_item_input, tab0_column1 );

		// Pay Stub Account

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_stub_entry_account_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Stub Account' ), form_item_input, tab0_column1 );

		// Deposit Accrual Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIAccrualPolicy' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.ACCRUAL_POLICY,
			show_search_inputs: true,
			set_empty: true,
			field: 'accrual_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Deposit Accrual Policy' ), form_item_input, tab0_column1, '' );

		// Accrual Rate
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'accrual_rate'} );
		this.addEditFieldToColumn( $.i18n._( 'Accrual Rate' ), form_item_input, tab0_column1, '', null, true );

	},

	onFormItemChange: function( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;

		switch ( key ) {
			case 'accrual_policy_id':
				this.onTypeChange();
				break;
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	},
	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );

		this.onTypeChange();
	},

	onTypeChange: function( getRate ) {

		if ( this.current_edit_record.accrual_policy_id ) {
			this.edit_view_form_item_dic['accrual_rate'].css( 'display', 'block' );

		} else {
			this.edit_view_form_item_dic['accrual_rate'].css( 'display', 'none' );
		}

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );
		this.search_fields = [

			new SearchField( {label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT} ),

			new SearchField( {label: $.i18n._( 'Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Deposit to Accrual Policy' ),
				in_column: 1,
				field: 'accrual_policy_id',
				layout_name: ALayoutIDs.ACCRUAL_POLICY,
				api_class: (APIFactory.getAPIClass( 'APIAccrualPolicy' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Pay Stub Account' ),
				in_column: 1,
				field: 'pay_stub_entry_account_id',
				layout_name: ALayoutIDs.PAY_STUB_ACCOUNT,
				api_class: (APIFactory.getAPIClass( 'APIPayStubEntryAccount' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} )
		];
	}


} );

OvertimePolicyViewController.loadView = function() {

	Global.loadViewSource( 'OvertimePolicy', 'OvertimePolicyView.html', function( result ) {

		var args = {};
		var template = _.template( result, args );

		Global.contentContainer().html( template );
	} );

};