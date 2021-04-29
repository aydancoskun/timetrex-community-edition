class CurrencyViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#currency_view_container',

			status_array: null,
			iso_codes_array: null,
			round_decimal_places_array: null,
			sub_currency_rate_view_controller: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'CurrencyEditView.html';
		this.permission_id = 'currency';
		this.viewId = 'Currency';
		this.script_name = 'CurrencyView';
		this.table_name_key = 'currency';
		this.context_menu_name = $.i18n._( 'Currencies' );
		this.navigation_label = $.i18n._( 'Currency' ) + ':';
		this.api = TTAPI.APICurrency;

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'Currency' );
	}

	initOptions() {
		var $this = this;

		this.initDropDownOption( 'status' );
		this.initDropDownOption( 'round_decimal_places', 'round_decimal_places' );
		this.api.getISOCodesArray( '', false, false, {
			onResult: function( res ) {
				res = res.getResult();
				res = Global.buildRecordArray( res );
				$this.basic_search_field_ui_dic['iso_code'].setSourceData( res );
				$this.iso_codes_array = res;

			}
		} );
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_currency': { 'label': $.i18n._( 'Currency' ) },
			'tab_rates': {
				'label': $.i18n._( 'Rates' ),
				'init_callback': 'initSubCurrencyRateView',
				'display_on_mass_edit': false
			},
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APICurrency,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.CURRENCY,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_currency = this.edit_view_tab.find( '#tab_currency' );

		var tab_currency_column1 = tab_currency.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_currency_column1 );

		var form_item_input;
		var widgetContainer;

		//Status

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'status_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_currency_column1, '' );

		// ISO Currency

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'iso_code' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.iso_codes_array ) );
		this.addEditFieldToColumn( $.i18n._( 'ISO Currency' ), form_item_input, tab_currency_column1 );

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_currency_column1 );

		form_item_input.parent().width( '45%' );

		// Base Currency
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'is_base' } );
		this.addEditFieldToColumn( $.i18n._( 'Base Currency' ), form_item_input, tab_currency_column1 );

		// Conversion Rate
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'conversion_rate', width: 114 } );

		widgetContainer = $( '<div class=\'\'></div>' );
		var conversion_rate_clarification_box = $( '<span id=\'conversion_rate_clarification_box\'></span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( conversion_rate_clarification_box );
		this.addEditFieldToColumn( $.i18n._( 'Conversion Rate' ), [form_item_input], tab_currency_column1, '', widgetContainer, false, true );

		// Default Currency
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'is_default' } );
		this.addEditFieldToColumn( $.i18n._( 'Default Currency' ), form_item_input, tab_currency_column1 );

		// Auto Update
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'auto_update' } );
		this.addEditFieldToColumn( $.i18n._( 'Auto Update' ), form_item_input, tab_currency_column1 );

		// Decimal Places
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'round_decimal_places' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.round_decimal_places_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Decimal Places' ), form_item_input, tab_currency_column1, '' );
	}

	removeEditView() {
		super.removeEditView();

		this.sub_currency_rate_view_controller = null;
	}

	initSubCurrencyRateView() {
		var $this = this;

		if ( !this.current_edit_record.id ) {
			TTPromise.resolve( 'BaseViewController', 'onTabShow' ); //Since search() isn't called in this case, and we just display the "Please Save This Record ..." message, resolve the promise.
			return;
		}

		if ( this.sub_currency_rate_view_controller ) {
			this.sub_currency_rate_view_controller.buildContextMenu( true );
			this.sub_currency_rate_view_controller.setDefaultMenu();
			$this.sub_currency_rate_view_controller.parent_key = 'currency_id';
			$this.sub_currency_rate_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_currency_rate_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_currency_rate_view_controller.initData();
			return;
		}

		Global.loadScript( 'views/company/currency/CurrencyRateViewController.js', function() {
			var tab = $this.edit_view_tab.find( '#tab_rates' );
			var firstColumn = tab.find( '.first-column-sub-view' );
			Global.trackView( 'Sub' + 'CurrencyRate' + 'View', LocalCacheData.current_doing_context_action );
			CurrencyRateViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );
		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_currency_rate_view_controller = subViewController;
			$this.sub_currency_rate_view_controller.parent_key = 'currency_id';
			$this.sub_currency_rate_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_currency_rate_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_currency_rate_view_controller.parent_view_controller = $this;
			$this.sub_currency_rate_view_controller.initData();

		}
	}

	buildSearchFields() {

		super.buildSearchFields();
		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
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
				label: $.i18n._( 'ISO Currency' ),
				in_column: 1,
				field: 'iso_code',
				multiple: true,
				basic_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
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
		if ( target.getField() == 'conversion_rate' ) {
			this.setConversionRateExampleText( target.getValue(), this.edit_view_ui_dic.iso_code.getValue() );
		}
		super.onFormItemChange( target, doNotValidate );
	}

	initEditView( editId, noRefreshUI ) {
		super.initEditView();
		this.setConversionRateExampleText( this.edit_view_ui_dic.conversion_rate.getValue(), this.edit_view_ui_dic.iso_code.getValue() );
	}
}
