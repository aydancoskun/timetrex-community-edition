PayPeriodScheduleViewController = BaseViewController.extend( {
	el: '#pay_period_schedule_view_container', //Must set el here and can only set string, so events can work

	_required_files: ['APIPayPeriodSchedule', 'APIUserPreference'],

	user_preference_api: null,
	type_array: null,
	pay_period_starts_on_array: null,
	start_week_day_array: null,
	transaction_date_array: null,
	transaction_date_business_day_array: null,
	time_zone_array: null,
	shift_assigned_day_array: null,
	timesheet_verify_type_array: null,
	sub_pay_periods_view_controller: null,

	init: function( options ) {

		//this._super('initialize', options );
		this.edit_view_tpl = 'PayPeriodScheduleEditView.html';
		this.permission_id = 'pay_period_schedule';
		this.viewId = 'PayPeriodSchedule';
		this.script_name = 'PayPeriodScheduleView';
		this.table_name_key = 'pay_period_schedule';
		this.context_menu_name = $.i18n._( 'Pay Period Schedule' );
		this.navigation_label = $.i18n._( 'Pay Period Schedule' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIPayPeriodSchedule' ))();
		this.user_preference_api = new (APIFactory.getAPIClass( 'APIUserPreference' ))();

		this.render();
		this.buildContextMenu();
		this.initData();

		this.setSelectRibbonMenuIfNecessary( 'PayPeriodSchedule' );

	},

	getCustomContextMenuModel: function () {
		var context_menu_model = {
			exclude: [ContextMenuIconName.copy, ContextMenuIconName.mass_edit],
			include: []
		};

		return context_menu_model;
	},

	openEditView: function( id ) {
		Global.setUINotready();
		TTPromise.add( 'init', 'init' );
		TTPromise.wait();

		var $this = this;

		if ( $this.edit_only_mode ) {

			$this.initOptions( function( result ) {

				if ( !$this.edit_view ) {
					$this.initEditViewUI( $this.viewId, $this.edit_view_tpl );
				}

				$this.getPayPeriodScheduleData( id, function( result ) {
					// Waiting for the (APIFactory.getAPIClass( 'API' )) returns data to set the current edit record.
					$this.current_edit_record = result;

					$this.initEditView();

				} );

			} );

		} else {
			if ( !this.edit_view ) {
				this.initEditViewUI( $this.viewId, $this.edit_view_tpl );
			}


		}

	},

	getPayPeriodScheduleData: function( id, callBack ) {
		var filter = {};
		filter.filter_data = {};
		filter.filter_data.id = [id];

		this.api['get' + this.api.key_name]( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();

				if ( !result_data ) {
					result_data = [];
				}
				result_data = result_data[0];

				callBack( result_data );

			}
		} );
	},

	initOptions: function( callBack ) {
		var $this = this;

		var options = [
			{ option_name: 'type' },
			{ option_name: 'transaction_date' },
			{ option_name: 'transaction_date_business_day' },
			{ option_name: 'shift_assigned_day' },
			{ option_name: 'time_zone', field_name: '', api: this.user_preference_api },
			{ option_name: 'timesheet_verify_type' },
			{ option_name: 'start_week_day' }

		];

		this.initDropDownOptions( options, function( result ) {
			$this.transaction_date_array = Global.addLastItemToArray( $this.transaction_date_array, -1, $.i18n._( '- Last Day Of Month -' ) );

			$this.user_preference_api.getOptions( 'start_week_day', {
				onResult: function( res ) {
					var result = res.getResult();
					$this.pay_period_starts_on_array = Global.buildRecordArray( result );

					if ( callBack ) {
						callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
					}
				}
			} );

		} );

	},

	initSubPayPeriodsView: function() {
		var $this = this;

		if ( !this.current_edit_record.id ) {
			TTPromise.resolve( 'BaseViewController', 'onTabShow' ); //Since search() isn't called in this case, and we just display the "Please Save This Record ..." message, resolve the promise.
			return;
		}

		if ( this.sub_pay_periods_view_controller ) {
			this.sub_pay_periods_view_controller.buildContextMenu( true );
			this.sub_pay_periods_view_controller.setDefaultMenu();
			$this.sub_pay_periods_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_pay_periods_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_pay_periods_view_controller.initData(); //Init data in this parent view
			return;
		}

		Global.loadScript( 'views/payroll/pay_periods/PayPeriodsViewController.js', function() {

			var tab_pay_period_schedule = $this.edit_view_tab.find( '#tab_pay_period' );

			var firstColumn = tab_pay_period_schedule.find( '.first-column-sub-view' );

			Global.trackView( 'Sub' + 'PayPeriods' + 'View' );
			PayPeriodsViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {

			$this.sub_pay_periods_view_controller = subViewController;
			$this.sub_pay_periods_view_controller.parent_key = 'pay_period_schedule_id';
			$this.sub_pay_periods_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_pay_periods_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_pay_periods_view_controller.parent_view_controller = $this;
			$this.sub_pay_periods_view_controller.initData(); //Init data in this parent view
		}
	},

	onFormItemChange: function( target, doNotValidate ) {

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;

		if ( key == 'type_id' ) {
			this.onTypeChange();
		} else if ( key == 'start_day_of_week' && ( !this.current_edit_record || !this.current_edit_record.id) ) {
			this.edit_view_ui_dic.start_week_day_id.setValue( this.edit_view_ui_dic.start_day_of_week.getValue() );
			this.current_edit_record.start_week_day_id = this.edit_view_ui_dic.start_day_of_week.getValue();
		} else if ( key == 'timesheet_verify_type_id' ) {
			this.onVerifyTypeChange();
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	},

	removeEditView: function() {

		this._super( 'removeEditView' );
		this.sub_pay_periods_view_controller = null;

	},

	onVerifyTypeChange: function() {
		if ( this.current_edit_record.timesheet_verify_type_id == 10 ) {
			this.detachElement( 'timesheet_verify_before_end_date' );
			this.detachElement( 'timesheet_verify_before_transaction_date' );
		} else {
			this.attachElement( 'timesheet_verify_before_end_date' );
			this.attachElement( 'timesheet_verify_before_transaction_date' );
		}

		this.editFieldResize();
	},

	onTypeChange: function() {

		if ( this.current_edit_record.type_id == 5 ) {
			this.detachElement( 'anchor_date' );
		} else {

			if ( this.current_edit_record.id ) {
				this.detachElement( 'anchor_date' );
			} else {
				this.attachElement( 'anchor_date' );
			}

		}

		if ( this.current_edit_record.type_id == 5 ) {
			this.detachElement( 'primary' );
			this.detachElement( 'primary_day_of_month' );
			this.detachElement( 'primary_transaction_day_of_month' );
			this.detachElement( 'secondary' );
			this.detachElement( 'secondary_day_of_month' );
			this.detachElement( 'secondary_transaction_day_of_month' );
			this.attachElement( 'annual_pay_periods' );
			this.detachElement( 'start_day_of_week' );
			this.detachElement( 'transaction_date' );
			this.detachElement( 'transaction_date_bd' );

		} else if ( this.current_edit_record.type_id == 10 || this.current_edit_record.type_id == 20 || this.current_edit_record.type_id == 100 ) {
			this.detachElement( 'primary' );
			this.detachElement( 'primary_day_of_month' );
			this.detachElement( 'primary_transaction_day_of_month' );
			this.detachElement( 'secondary' );
			this.detachElement( 'secondary_day_of_month' );
			this.detachElement( 'secondary_transaction_day_of_month' );
			this.detachElement( 'annual_pay_periods' );
			this.attachElement( 'start_day_of_week' );
			this.attachElement( 'transaction_date' );
			this.attachElement( 'transaction_date_bd' );

		} else if ( this.current_edit_record.type_id == 30 ) {
			this.attachElement( 'primary' );
			this.attachElement( 'primary_day_of_month' );
			this.attachElement( 'primary_transaction_day_of_month' );
			this.attachElement( 'secondary' );
			this.attachElement( 'secondary_day_of_month' );
			this.attachElement( 'secondary_transaction_day_of_month' );
			this.detachElement( 'annual_pay_periods' );
			this.detachElement( 'start_day_of_week' );
			this.detachElement( 'transaction_date' );
			this.attachElement( 'transaction_date_bd' );
		} else if ( this.current_edit_record.type_id == 50 ) {
			this.attachElement( 'primary' );
			this.attachElement( 'primary_day_of_month' );
			this.attachElement( 'primary_transaction_day_of_month' );
			this.detachElement( 'secondary' );
			this.detachElement( 'secondary_day_of_month' );
			this.detachElement( 'secondary_transaction_day_of_month' );
			this.detachElement( 'annual_pay_periods' );
			this.detachElement( 'start_day_of_week' );
			this.detachElement( 'transaction_date' );
			this.attachElement( 'transaction_date_bd' );
		}

		this.editFieldResize();
	},

	setCurrentEditRecordData: function() {
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}
		this.onTypeChange();
		this.onVerifyTypeChange();
		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();
	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		var tab_model = {
			'tab_pay_period_schedule': { 'label': $.i18n._( 'Pay Period Schedule' ) },
			'tab_advanced': { 'label': $.i18n._( 'Advanced' ) },
			'tab_pay_period': {
				'label': $.i18n._( 'Pay Periods' ),
				'init_callback': 'initSubPayPeriodsView',
				'display_on_mass_edit': false
			},
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		if ( !this.edit_only_mode ) {
			this.navigation.AComboBox( {
				id: this.script_name + '_navigation',
				api_class: (APIFactory.getAPIClass( 'APIPayPeriodSchedule' )),
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.PAY_PERIOD_SCHEDULE,
				navigation_mode: true,
				show_search_inputs: true
			} );

			this.setNavigation();
		}

		//Tab 0 start

		var tab_pay_period_schedule = this.edit_view_tab.find( '#tab_pay_period_schedule' );

		var tab_pay_period_schedule_column1 = tab_pay_period_schedule.find( '.first-column' );
		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_pay_period_schedule_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_pay_period_schedule_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_pay_period_schedule_column1 );

		form_item_input.parent().width( '45%' );

		// Pay Period Dates
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Pay Period Dates' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_pay_period_schedule_column1 );

		// Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'type_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_pay_period_schedule_column1 );

		// Primary

		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Primary' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_pay_period_schedule_column1, '', null, true, false, 'primary' );

		// Pay Period Start Day of Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'primary_day_of_month' } );
		var widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		var label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'at 00:00' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.transaction_date_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Pay Period Start Day of Month' ), form_item_input, tab_pay_period_schedule_column1, '', widgetContainer, true );

		// Transaction Day Of Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'primary_transaction_day_of_month' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.transaction_date_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Transaction Day Of Month' ), form_item_input, tab_pay_period_schedule_column1, '', null, true );

		// Secondary
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Secondary' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_pay_period_schedule_column1, '', null, true, false, 'secondary' );

		// Pay Period Start Day Of Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'secondary_day_of_month' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'at 00:00' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.transaction_date_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Pay Period Start Day of Month' ), form_item_input, tab_pay_period_schedule_column1, '', widgetContainer, true );

		// Transaction Day Of Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'secondary_transaction_day_of_month' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.transaction_date_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Transaction Day Of Month' ), form_item_input, tab_pay_period_schedule_column1, '', null, true );

		// Annual Pay Periods
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'annual_pay_periods' } );
		this.addEditFieldToColumn( $.i18n._( 'Annual Pay Periods' ), form_item_input, tab_pay_period_schedule_column1, '', null, true );

		// Pay Period Starts On
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'start_day_of_week' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'at 00:00' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.pay_period_starts_on_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Pay Period Starts On' ), form_item_input, tab_pay_period_schedule_column1, '', widgetContainer, true );

		// Transaction Date
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'transaction_date' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> (' + $.i18n._( 'days after end of pay period' ) + ')</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.transaction_date_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Transaction Date' ), form_item_input, tab_pay_period_schedule_column1, '', widgetContainer, true );

		// Transaction Always on Business Day
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'transaction_date_bd' } );

		form_item_input.setSourceData( Global.addFirstItemToArray( $this.transaction_date_business_day_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Transaction Always on Business Day' ), form_item_input, tab_pay_period_schedule_column1, '', null, true );

		//Create Initial Pay Periods From
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'anchor_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Create Initial Pay Periods From' ), form_item_input, tab_pay_period_schedule_column1, '', null, true );

		// employees
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'user'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Employees' ), form_item_input, tab_pay_period_schedule_column1 );

		//Tab 0 start

		var tab_advanced = this.edit_view_tab.find( '#tab_advanced' );

		var tab_advanced_column1 = tab_advanced.find( '.first-column' );
		this.edit_view_tabs[1] = [];

		this.edit_view_tabs[1].push( tab_advanced_column1 );

		// Overtime Week
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'start_week_day_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.start_week_day_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Overtime Week' ), form_item_input, tab_advanced_column1, '' );

		// Time Zone
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'time_zone', set_empty: true } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.time_zone_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Time Zone' ), form_item_input, tab_advanced_column1 );

		// Minimum Time-Off Between Shifts
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'new_day_trigger_time', mode: 'time_unit', need_parser_sec: true } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>(' + $.i18n._( 'Only for shifts that span midnight' ) + ')</span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Minimum Time-Off Between Shifts' ), form_item_input, tab_advanced_column1, '', widgetContainer );

		// Maximum Shift Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'maximum_shift_time', mode: 'time_unit', need_parser_sec: true } );

		this.addEditFieldToColumn( $.i18n._( 'Maximum Shift Time' ), form_item_input, tab_advanced_column1, '', null );

		// Assign Shifts To
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'shift_assigned_day_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.shift_assigned_day_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Assign Shifts To' ), form_item_input, tab_advanced_column1 );

		// TimeSheet Verification
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'TimeSheet Verification' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_advanced_column1 );

		// Timesheet Verification
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'timesheet_verify_type_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.timesheet_verify_type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'TimeSheet Verification' ), form_item_input, tab_advanced_column1 );

		// Verification Window Starts
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'timesheet_verify_before_end_date', width: 30 } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>' + $.i18n._( 'Day(s)' ) + ' (' + $.i18n._( 'Before Pay Period End Date' ) + ' )' + '</span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Verification Window Starts' ), form_item_input, tab_advanced_column1, '', widgetContainer, true );

		// Verification Windows Ends
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'timesheet_verify_before_transaction_date', width: 30 } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>' + $.i18n._( 'Day(s)' ) + ' (' + $.i18n._( 'Before Pay Period Transaction Date' ) + ' )' + '</span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Verification Window Ends' ), form_item_input, tab_advanced_column1, '', widgetContainer, true );

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );

		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				basic_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Description' ),
				in_column: 1,
				field: 'description',
				basic_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} )

		];

	}

} );
