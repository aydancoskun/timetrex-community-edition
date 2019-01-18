CurrencyRateViewController = BaseViewController.extend( {

	_required_files: ['APICurrencyRate'],
	el: '#currency_rate_view_container', //Must set el here and can only set string, so events can work

	init: function( options ) {

		//this._super('initialize', options );
		this.edit_view_tpl = 'CurrencyRateEditView.html';
		this.permission_id = 'currency';
		this.script_name = 'CurrencyRateView';
		this.viewId = 'CurrencyRate';
		this.table_name_key = 'currency_rate';
		this.context_menu_name = $.i18n._( 'Rates' );
		this.navigation_label = $.i18n._( 'Rate' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APICurrencyRate' ))();

		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true; //Hide some context menus

		this.render();

		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
		} else {
			this.buildContextMenu();
		}

		//call init data in parent view
		if ( !this.sub_view_mode ) {
			this.initData();
		}

		this.setSelectRibbonMenuIfNecessary( 'CurrencyRate' );

	},

	//Override for: Do not show first 2 columns in sub wage view
//	setSelectLayout: function() {
//
//		if ( this.sub_view_mode ) {
//			this._super( 'setSelectLayout', 2 );
//		} else {
//			this._super( 'setSelectLayout' );
//		}
//
//	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );
		var $this = this;

		var tab_model = {
			'tab_currency_rate': { 'label': $.i18n._( 'Currency Rate' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		var form_item_input;

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APICurrencyRate' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.CURRENCY_RATE,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_currency_rate = this.edit_view_tab.find( '#tab_currency_rate' );

		var tab_currency_rate_column1 = tab_currency_rate.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_currency_rate_column1 );

		// Currency
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APICurrency' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.CURRENCY,
			field: 'currency_id',
			set_empty: false,
			show_search_inputs: true
		} );

		this.addEditFieldToColumn( $.i18n._( 'Currency' ), form_item_input, tab_currency_rate_column1 );

		// Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'date_stamp' } );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_currency_rate_column1 );

		// Conversion Rate
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'conversion_rate', width: 114 } );
		var widgetContainer = $( '<div class=\'\'></div>' );
		var conversion_rate_clarification_box = $( '<span id=\'rate_conversion_rate_clarification_box\'></span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( conversion_rate_clarification_box );
		this.addEditFieldToColumn( $.i18n._( 'Conversion Rate' ), [form_item_input], tab_currency_rate_column1, '', widgetContainer, false, true );


	},

	onMassEditClick: function() {

		var $this = this;
		$this.is_add = false;
		$this.is_viewing = false;
		$this.is_mass_editing = true;
		LocalCacheData.current_doing_context_action = 'mass_edit';
		$this.openEditView();
		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		this.mass_edit_record_ids = [];

		$.each( grid_selected_id_array, function( index, value ) {
			$this.mass_edit_record_ids.push( value );
		} );

		filter.filter_data = {};
		filter.filter_data.id = this.mass_edit_record_ids;

		this.api['getCommon' + this.api.key_name + 'Data']( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();

				if ( !result_data ) {
					result_data = [];
				}

				$this.api['getOptions']( 'unique_columns', {
					onResult: function( result ) {
						$this.unique_columns = result.getResult();

						$this.linked_columns = {};
						if ( $this.sub_view_mode && $this.parent_key ) {
							result_data[$this.parent_key] = $this.parent_value;
						}

						$this.current_edit_record = result_data;
						$this.initEditView();

					}
				} );

			}
		} );

	},

	onFormItemChange: function( target, doNotValidate ) {
		if ( target.getField() == 'conversion_rate' ) {
			this.setConversionRateExampleText( target.getValue(), null, this.edit_view_ui_dic.currency_id.getValue() );
		}
		this._super( 'onFormItemChange', target, doNotValidate );
	},


	initEditView: function( editId, noRefreshUI ) {
		this._super( 'initEditView' );
		this.setConversionRateExampleText( this.edit_view_ui_dic.conversion_rate.getValue(), null, this.edit_view_ui_dic.currency_id.getValue() );
	}


} );

CurrencyRateViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'CurrencyRate', 'SubCurrencyRateView.html', function( result ) {

		var args = {};
		var template = _.template( result );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}

		if ( Global.isSet( container ) ) {
			container.html( template( args ) );

			if ( Global.isSet( afterViewLoadedFun ) ) {
				TTPromise.wait( 'BaseViewController', 'initialize', function() {
					afterViewLoadedFun( sub_currency_rate_view_controller );
				} );
			}

		}

	} );

};