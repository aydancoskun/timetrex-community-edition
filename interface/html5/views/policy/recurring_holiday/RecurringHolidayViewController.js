export class RecurringHolidayViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#recurring_holiday_view_container',

			special_day_array: null,
			type_array: null,
			day_of_month_array: null,
			day_of_week_array: null,
			month_of_year_array: null,

			always_week_day_array: null,
			week_interval_array: null,
			pivot_day_direction_array: null,
			date_api: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'RecurringHolidayEditView.html';
		this.permission_id = 'holiday_policy';
		this.viewId = 'RecurringHoliday';
		this.script_name = 'RecurringHolidayView';
		this.table_name_key = 'recurring_holiday';
		this.context_menu_name = $.i18n._( 'Recurring Holiday' );
		this.navigation_label = $.i18n._( 'Recurring Holiday' ) + ':';
		this.api = TTAPI.APIRecurringHoliday;
		this.date_api = TTAPI.APITTDate;
		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'RecurringHoliday' );
	}

	initOptions() {
		var $this = this;
		this.initDropDownOption( 'special_day', 'special_day' );
		this.initDropDownOption( 'week_interval', 'week_interval' );
		this.initDropDownOption( 'type' );
		this.initDropDownOption( 'pivot_day_direction' );
		this.initDropDownOption( 'always_week_day', 'always_week_day' );

		this.date_api.getDayOfMonthArray( {
			onResult: function( res ) {
				res = res.getResult();
				$this.day_of_month_array = res;
			}
		} );
		this.date_api.getMonthOfYearArray( {
			onResult: function( res ) {
				res = res.getResult();
				$this.month_of_year_array = res;
			}
		} );
		this.date_api.getDayOfWeekArray( {
			onResult: function( res ) {
				res = res.getResult();
				$this.day_of_week_array = res;
			}
		} );
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_recurring_holiday': { 'label': $.i18n._( 'Recurring Holiday' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIRecurringHoliday,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_recurring_holiday',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_recurring_holiday = this.edit_view_tab.find( '#tab_recurring_holiday' );

		var tab_recurring_holiday_column1 = tab_recurring_holiday.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_recurring_holiday_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_recurring_holiday_column1, '' );

		form_item_input.parent().width( '45%' );

		// Special Day
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'special_day' } );
		form_item_input.setSourceData( $this.special_day_array );
		this.addEditFieldToColumn( $.i18n._( 'Special Day' ), form_item_input, tab_recurring_holiday_column1 );

		// Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'type_id', set_empty: false } );
		form_item_input.setSourceData( $this.type_array );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_recurring_holiday_column1, '', null, true );

		// Week Interval
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'week_interval' } );
		form_item_input.setSourceData( $this.week_interval_array );
		this.addEditFieldToColumn( $.i18n._( 'Week Interval' ), form_item_input, tab_recurring_holiday_column1, '', null, true );

		// Day of the week
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'day_of_week' } );
		form_item_input.setSourceData( $.extend( {}, $this.day_of_week_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Day of the week' ), form_item_input, tab_recurring_holiday_column1, '', null, true );

		// Pivot Day Direction
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'pivot_day_direction_id' } );
		form_item_input.setSourceData( $this.pivot_day_direction_array );
		this.addEditFieldToColumn( $.i18n._( 'Pivot Day Direction' ), form_item_input, tab_recurring_holiday_column1, '', null, true );

		// Day of the Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'day_of_month' } );
		form_item_input.setSourceData( $this.day_of_month_array );
		this.addEditFieldToColumn( $.i18n._( 'Day of the Month' ), form_item_input, tab_recurring_holiday_column1, '', null, true );

		// Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'month_int' } );
		form_item_input.setSourceData( $this.month_of_year_array );
		this.addEditFieldToColumn( $.i18n._( 'Month' ), form_item_input, tab_recurring_holiday_column1, '', null, true );

		// Always On Week Day
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'always_week_day_id' } );
		form_item_input.setSourceData( $this.always_week_day_array );
		this.addEditFieldToColumn( $.i18n._( 'Always On Week Day' ), form_item_input, tab_recurring_holiday_column1, '' );
	}

	onFormItemChange( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;

		if ( key === 'special_day' ) {
			this.onSpecialDayChange();
		}
		if ( key === 'type_id' ) {
			this.onTypeChange();
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		this.onSpecialDayChange();
		this.onTypeChange();
	}

	onSpecialDayChange() {

		this.detachElement( 'type_id' );
		this.detachElement( 'week_interval' );
		this.detachElement( 'day_of_week' );
		this.detachElement( 'pivot_day_direction_id' );
		this.detachElement( 'day_of_month' );
		this.detachElement( 'month_int' );

		if ( Global.isFalseOrNull( this.current_edit_record['special_day'] ) ) {
			this.current_edit_record['special_day'] = 0;
		}

		if ( parseInt( this.current_edit_record['special_day'] ) === 0 ) {

			this.attachElement( 'type_id' );
			this.detachElement( 'week_interval' );
			this.detachElement( 'day_of_week' );
			this.detachElement( 'pivot_day_direction_id' );
			this.attachElement( 'day_of_month' );
			this.attachElement( 'month_int' );

		}

		this.editFieldResize();
	}

	onTypeChange() {

		if ( parseInt( this.current_edit_record['special_day'] ) === 0 ) {

			if ( this.current_edit_record['type_id'] == 10 ) {
				this.detachElement( 'week_interval' );
				this.detachElement( 'day_of_week' );
				this.detachElement( 'pivot_day_direction_id' );
				this.attachElement( 'day_of_month' );
				this.attachElement( 'month_int' );

			} else if ( this.current_edit_record['type_id'] == 20 ) {
				this.attachElement( 'week_interval' );
				this.attachElement( 'day_of_week' );
				this.detachElement( 'pivot_day_direction_id' );
				this.detachElement( 'day_of_month' );
				this.attachElement( 'month_int' );
			} else if ( this.current_edit_record['type_id'] == 30 ) {
				this.detachElement( 'week_interval' );
				this.attachElement( 'day_of_week' );
				this.attachElement( 'pivot_day_direction_id' );
				this.attachElement( 'day_of_month' );
				this.attachElement( 'month_int' );
			}
		}

		this.editFieldResize();
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
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: 'global_user',
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
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	}

}
