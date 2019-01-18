PayrollRemittanceAgencyEventViewController = BaseViewController.extend( {

	el: '#payroll_remittance_agency_event_view_container', //Must set el here and can only set string, so events can work

	user_api: null,
	status_array: null,
	action_array: null,
	agency_array: null,
	payment_frequency_array: null,
	report_frequency_array: null,
	country_array: null,
	province_array: null,
	district_array: null,

	month_of_year_array: null,
	month_of_quarter_array: null,
	week_interval_array: null,
	day_of_month_array: null,
	day_of_week_array: null,

	frequency_week_array: null,
	remittance_source_account_array: null,
	sub_event_view_controller: null,

	_required_files: ['APIPayrollRemittanceAgency', 'APIPayrollRemittanceAgencyEvent', 'APIUserGroup', 'APICompany', 'APIDate', 'APIPayPeriodSchedule', 'APIUserReportData'],

	init: function( options ) {

		//this._super('initialize', options );
		this.edit_view_tpl = 'PayrollRemittanceAgencyEventEditView.html';
		this.permission_id = 'payroll_remittance_agency';
		this.script_name = 'PayrollRemittanceAgencyEventView';
		this.table_name_key = 'payroll_remittance_agency_event';
		this.viewId = 'PayrollRemittanceAgencyEvent';
		this.context_menu_name = $.i18n._( 'Remittance Agency Event' );
		this.navigation_label = $.i18n._( 'Remittance Agency Event' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIPayrollRemittanceAgencyEvent' ))();
		this.user_group_api = new (APIFactory.getAPIClass( 'APIUserGroup' ))();
		this.company_api = new (APIFactory.getAPIClass( 'APICompany' ))();

		this.date_api = new (APIFactory.getAPIClass( 'APIDate' ))();
		this.api_user_report = new (APIFactory.getAPIClass( 'APIUserReportData' ))();
		this.month_of_quarter_array = Global.buildRecordArray( { 1: 1, 2: 2, 3: 3 } );

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

		this.setSelectRibbonMenuIfNecessary();

	},

	//override required because this is a subview in an edit view.
	_setGridSizeGridWidthOfSubViewMode: function() {
		this.grid.setGridWidth( $( this.el ).parents( '.edit-view-tab' ).parent().parent().width() - 10 );
	},

	//Don't initOptions if edit_only_mode. Do it in sub views
	initOptions: function() {
		var $this = this;

		this.initDropDownOption( 'status' );
		this.initDropDownOption( 'frequency' );
		this.initDropDownOption( 'payroll_remittance_agency', 'payroll_remittance_agency' );

		this.api.getOptions( 'week', {
			onResult: function( res ) {
				res = res.getResult();
				$this.frequency_week_array = res;
			}

		} );
		this.date_api.getMonthOfYearArray( {
			onResult: function( res ) {
				res = res.getResult();
				$this.month_of_year_array = res;
			}
		} );
		this.date_api.getDayOfMonthArray( {
			onResult: function( res ) {
				res = res.getResult();
				$this.day_of_month_array = Global.buildRecordArray( res );
			}
		} );
		this.date_api.getDayOfWeekArray( {
			onResult: function( res ) {
				res = res.getResult();
				$this.day_of_week_array = res;
			}
		} );
	},

	getTypeOptions: function() {
		var $this = this;
		var type_params = {
			'payroll_remittance_agency_id': this.edit_view_ui_dic.payroll_remittance_agency_id.getValue()
		};

		this.api.getOptions( 'type', type_params, {
			onResult: function( res ) {
				res = res.getResult();
				$this.edit_view_ui_dic.type_id.setSourceData( Global.buildRecordArray( res ) );

				$this.edit_view_ui_dic.type_id.setSourceData( Global.buildRecordArray( res ) );
				//must update current edit record in case the previous type has been removed from list
				$this.current_edit_record.type_id = $this.edit_view_ui_dic.type_id.getValue();

				TTPromise.resolve( 'PayrollRemittanceAgencyEvent', 'updateUI' );
			}
		} );
	},

	getReportOptions: function() {
		var $this = this;
		this.api_user_report.getUserReportData( { filter_data: { include_user_report_id: this.current_edit_record.user_report_data_id } }, {
			onResult: function( res ) {
				$this.edit_view_ui_dic.user_report_data_id.setSourceData( res.getResult() );
			}
		} );
	},

	setEditViewDataDone: function() {
		this._super( 'setEditViewDataDone' );
		this.onFrequencyChange();

		if ( typeof this.current_edit_record.id == 'undefined' ) {
			this.detachElement( 'enable_recalculate_dates' );
		}

		this.getTypeOptions();
		this.getReportOptions();
		this.confirm_on_exit = false;
	},

	onFormItemChange: function( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		Debug.Text( 'key: ' + key + ' value: ' + c_value, 'PayrollRemittanceAgencyEventViewController.js', 'PayrollRemittanceAgencyEventViewController', 'onFormItemChange', 10 );
		TTPromise.add( 'PayrollRemittanceAgencyEvent', 'updateUI' );
		switch ( key ) {
			case 'payroll_remittance_agency_id':
				this.getTypeOptions(); //must be dynamically connected every time stuff changes.
				break;
			case 'frequency_id':
				this.onFrequencyChange( c_value );
				this.current_edit_record[key] = c_value;
				this.validate();
				this.updateFutureDates();
				TTPromise.reject( 'PayrollRemittanceAgencyEvent', 'updateUI' );
				break;
			default:
				TTPromise.resolve( 'PayrollRemittanceAgencyEvent', 'updateUI' );
				break;
		}

		var $this = this;
		// Hit when all promises are done...
		TTPromise.wait( 'PayrollRemittanceAgencyEvent', 'updateUI', function() {
			$this.onFrequencyChange();
			$this.current_edit_record[key] = c_value;
			$this.validate();
			$this.updateFutureDates();
		} );
	},

	updateFutureDates: function() {
		Debug.Text( 'Updating remittance agency event dates.', null, null, null, 10 );
		var $this = this;
		this.api.calculateNextRunDate( this.current_edit_record, {
			onResult: function( result ) {
				result = result.getResult();
				$this.edit_view_ui_dic.start_date.setValue( result.start_date );
				$this.edit_view_ui_dic.end_date.setValue( result.end_date );
				$this.edit_view_ui_dic.due_date.setValue( result.due_date );
				$this.edit_view_ui_dic.next_reminder_date.setValue( result.next_reminder_date );
			}
		} );
	},

	onFrequencyChange: function( arg ) {
		if ( !Global.isSet( arg ) ) {

			if ( !Global.isSet( this.current_edit_record['frequency_id'] ) ) {
				this.current_edit_record['frequency_id'] = 10;
			}

			arg = this.current_edit_record['frequency_id'];
		}
		Debug.Text( 'Selected Frequency: ' + arg, null, null, null, 10 );
		this.detachElement( 'week' );
		this.detachElement( 'primary_month' );
		this.detachElement( 'primary_day_of_month' );
		this.detachElement( 'secondary_month' );
		this.detachElement( 'secondary_day_of_month' );
		this.detachElement( 'day_of_week' );
		this.detachElement( 'due_date_delay_days' );
		this.detachElement( 'quarter_month' );
		this.detachElement( 'pay_period_schedule_id' );

		if ( arg == 1000 ) { //each pay period
			this.attachElement( 'pay_period_schedule_id' );
			this.attachElement( 'due_date_delay_days' );
		} else if ( arg == 2000 ) { //annually
			this.attachElement( 'primary_month' );
			this.edit_view_ui_dic.primary_month.parents( '.edit-view-form-item-div' ).find( '.edit-view-form-item-label' ).html( 'Month' );
			this.attachElement( 'primary_day_of_month' );
			this.edit_view_ui_dic.primary_day_of_month.parents( '.edit-view-form-item-div' ).find( '.edit-view-form-item-label' ).html( 'Day Of Month' );
		} else if ( arg == 2100 ) { //Year-To-Date
			this.attachElement( 'primary_day_of_month' );
			this.edit_view_ui_dic.primary_day_of_month.parents( '.edit-view-form-item-div' ).find( '.edit-view-form-item-label' ).html( 'Day Of Month' );
			this.attachElement( 'primary_month' );
			this.edit_view_ui_dic.primary_month.parents( '.edit-view-form-item-div' ).find( '.edit-view-form-item-label' ).html( 'Month' );
			this.attachElement( 'due_date_delay_days' );
		} else if ( arg == 2200 ) { //Semi-Annually
			this.attachElement( 'primary_day_of_month' );
			this.edit_view_ui_dic.primary_day_of_month.parents( '.edit-view-form-item-div' ).find( '.edit-view-form-item-label' ).html( 'Primary Day Of Month' );
			this.attachElement( 'primary_month' );
			this.edit_view_ui_dic.primary_month.parents( '.edit-view-form-item-div' ).find( '.edit-view-form-item-label' ).html( 'Primary Month' );
			this.attachElement( 'secondary_month' );
			this.attachElement( 'secondary_day_of_month' );
			this.attachElement( 'due_date_delay_days' );
		} else if ( arg == 3000 ) {//Quarterly
			this.attachElement( 'primary_day_of_month' );
			this.edit_view_ui_dic.primary_day_of_month.parents( '.edit-view-form-item-div' ).find( '.edit-view-form-item-label' ).html( 'Day Of Month' );
			this.attachElement( 'quarter_month' );
		} else if ( arg == 4100 ) { //monthly
			this.attachElement( 'primary_day_of_month' );
			this.edit_view_ui_dic.primary_day_of_month.parents( '.edit-view-form-item-div' ).find( '.edit-view-form-item-label' ).html( 'Day Of Month' );
		} else if ( arg == 4200 ) { //semimonthly
			this.attachElement( 'primary_day_of_month' );
			this.edit_view_ui_dic.primary_day_of_month.parents( '.edit-view-form-item-div' ).find( '.edit-view-form-item-label' ).html( 'Primary Day Of Month' );
			this.attachElement( 'secondary_day_of_month' );
			this.attachElement( 'due_date_delay_days' );
		} else if ( arg == 5100 ) { //weekly
			this.attachElement( 'day_of_week' );
		} else if ( arg == 90100 || arg == 90200 ) { //On Hire/Termination
			this.attachElement( 'due_date_delay_days' );
		} else if ( arg == 90310 ) { //On Termination (Pay Period End Date)
			this.attachElement( 'pay_period_schedule_id' );
			this.attachElement( 'due_date_delay_days' );
		}

		this.editFieldResize();
	},

	setDefaultMenuMassEditIcon: function( context_btn, grid_selected_length ) {
		context_btn.addClass( 'invisible-image' );
	},
	setDefaultMenuSaveAndCopyIcon: function( context_btn, grid_selected_length ) {
		context_btn.addClass( 'invisible-image' );
	},

	/* jshint ignore:end */

	//Make sure this.current_edit_record is updated before validate
	// validate: function() {
	// 	var $this = this;
	// 	var record = {};
	// 	LocalCacheData.current_doing_context_action = 'validate';
	// 	if ( this.is_mass_editing ) {
	// 		for ( var key in this.edit_view_ui_dic ) {
	// 			var widget = this.edit_view_ui_dic[key];
	//
	// 			if ( Global.isSet( widget.isChecked ) ) {
	// 				if ( widget.isChecked() && widget.getEnabled() ) {
	// 					record[key] = widget.getValue();
	// 				}
	// 			}
	// 		}
	// 	} else {
	// 		if ( Global.isArray( this.current_edit_record.user_id ) && this.current_edit_record.user_id.length > 0 ) {
	// 			record = [];
	// 			$.each( this.current_edit_record.user_id, function( index, value ) {
	//
	// 				var commonRecord = Global.clone( $this.current_edit_record );
	// 				commonRecord.user_id = value;
	// 				record.push( commonRecord );
	//
	// 			} );
	// 		} else {
	// 			record = this.current_edit_record;
	// 		}
	// 	}
	// 	this.api['validate' + this.api.key_name]( record, {
	// 		onResult: function( result ) {
	// 			$this.validateResult( result );
	// 		}
	// 	} );
	// },

	setDefaultMenuImportIcon: function( context_btn, grid_selected_length, pId ) {

	},

	preCopyAsNew: function record( record ) {
		record['id'] = '';
		record['start_date'] = '';
		record['end_date'] = '';
		record['due_date'] = '';
		record['last_due_date'] = '';
		record['next_reminder_date'] = '';
		record['last_reminder_date'] = '';
		return record;
	},

	onCustomContextClick: function( id ) {
		switch ( id ) {
			case ContextMenuIconName.import_icon:
				this.onImportClick();
				break;
		}
	},

	onSaveClick: function( ignoreWarning ) {
		var $this = this;
		var record;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		LocalCacheData.current_doing_context_action = 'save';
		if ( this.is_mass_editing ) {

			var checkFields = {};
			for ( var key in this.edit_view_ui_dic ) {
				var widget = this.edit_view_ui_dic[key];

				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() ) {
						checkFields[key] = widget.getValue();
					}
				}
			}

			record = [];
			$.each( this.mass_edit_record_ids, function( index, value ) {
				var commonRecord = Global.clone( checkFields );
				commonRecord.id = value;
				record.push( commonRecord );

			} );
		} else {
			if ( Global.isArray( this.current_edit_record.user_id ) && this.current_edit_record.user_id.length > 0 ) {
				record = [];
				$.each( this.current_edit_record.user_id, function( index, value ) {

					var commonRecord = Global.clone( $this.current_edit_record );
					commonRecord.user_id = value;
					record.push( commonRecord );

				} );
			} else {
				record = this.current_edit_record;
			}

		}

		this.api['set' + this.api.key_name]( record, false, ignoreWarning, false, ignoreWarning, {
			onResult: function( result ) {

				if ( result.isValid() ) {
					var result_data = result.getResult();
					if ( result_data === true ) {
						$this.refresh_id = $this.current_edit_record.id;
					} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
						$this.refresh_id = result_data;
					}
					$this.search();

					$this.removeEditView();
				} else {
					$this.setErrorMenu();
					$this.setErrorTips( result );
				}
			}
		} );
	},

	setEditMenuSaveAndContinueIcon: function( context_btn, pId ) {
		this.saveAndContinueValidate( context_btn );

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuSaveAndAddIcon: function( context_btn, pId ) {
		this.saveAndNewValidate( context_btn );

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuSaveAndCopyIcon: function( context_btn, pId ) {
		this.saveAndContinueValidate( context_btn );

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	buildEditViewUI: function() {
		this._super( 'buildEditViewUI' );
		var $this = this;
		var form_item_input;

		var tab_model = {
			'tab_payroll_remittance_agency_event': { 'label': $.i18n._( 'Remittance Agency Event' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayrollRemittanceAgencyEvent' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAYROLL_REMITTANCE_AGENCY,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		var tab_payroll_remittance_agency_event = this.edit_view_tab.find( '#tab_payroll_remittance_agency_event' );
		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( tab_payroll_remittance_agency_event );
		var tab_payroll_remittance_agency_event_column_1 = tab_payroll_remittance_agency_event.find( '.first-column' );

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayrollRemittanceAgency' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PAYROLL_REMITTANCE_AGENCY,
			show_search_inputs: true,
			set_empty: false,
			field: 'payroll_remittance_agency_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Remittance Agency' ), form_item_input, tab_payroll_remittance_agency_event_column_1 );

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {
			field: 'status_id'
		} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_payroll_remittance_agency_event_column_1 );

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {
			field: 'type_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_payroll_remittance_agency_event_column_1 );

		// Payment Frequency
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {
			field: 'frequency_id'
		} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.frequency_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Frequency' ), form_item_input, tab_payroll_remittance_agency_event_column_1 );

		// Payment Frequency Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'primary_month' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( Global.buildRecordArray( $this.month_of_year_array ) ) );
		this.addEditFieldToColumn( $.i18n._( 'Primary Month' ), form_item_input, tab_payroll_remittance_agency_event_column_1, '', null, true );

		// Payment Frequency Day Of Month
		// Day of the Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'primary_day_of_month' } );
		var day_of_month_array = $this.day_of_month_array;
		day_of_month_array.push( {
					fullValue: -1,
					value: -1,
					label: $.i18n._( '- Last Day Of Month -' ),
					id: 2000
				}
		);
		form_item_input.setSourceData( Global.addFirstItemToArray( day_of_month_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Primary Day of Month' ), form_item_input, tab_payroll_remittance_agency_event_column_1, '', null, true );

		// Payment Frequency Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'secondary_month' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( Global.buildRecordArray( $this.month_of_year_array ) ) );
		this.addEditFieldToColumn( $.i18n._( 'Secondary Month' ), form_item_input, tab_payroll_remittance_agency_event_column_1, '', null, true );

		// Payment Frequency Day Of Month
		// Day of the Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'secondary_day_of_month' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( day_of_month_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Secondary Day of Month' ), form_item_input, tab_payroll_remittance_agency_event_column_1, '', null, true );

		// Payment Frequency Week
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'week' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( Global.buildRecordArray( $this.frequency_week_array ) ) );
		this.addEditFieldToColumn( $.i18n._( 'Week' ), form_item_input, tab_payroll_remittance_agency_event_column_1, '', null, true );

		// Payment Frequency quarter Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'quarter_month' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.month_of_quarter_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Month of Quarter' ), form_item_input, tab_payroll_remittance_agency_event_column_1, '', null, true );


		// Day of the week
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'day_of_week' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( Global.buildRecordArray( $this.day_of_week_array ) ) );
		this.addEditFieldToColumn( $.i18n._( 'Day of week' ), form_item_input, tab_payroll_remittance_agency_event_column_1, '', null, true );

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIPayPeriodSchedule' )),
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.PAY_PERIOD_SCHEDULE,
			show_search_inputs: true,
			set_special_empty: true,
			set_any: true,
			field: 'pay_period_schedule_id'
		} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.saved_report_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Pay Period Schedule' ), form_item_input, tab_payroll_remittance_agency_event_column_1, '', null, true );

		// Payment Frequency Days After Transaction Date

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'due_date_delay_days', width: 50 } );
		this.addEditFieldToColumn( $.i18n._( 'Due Date Delay Days' ), form_item_input, tab_payroll_remittance_agency_event_column_1, '', null, true );


		// Effective Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( {
			field: 'effective_date'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Effective Date' ), form_item_input, tab_payroll_remittance_agency_event_column_1 );

		//user to remind
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIUser' )),
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'reminder_user_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Send Reminder To' ), form_item_input, tab_payroll_remittance_agency_event_column_1 );

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.SAVED_REPORT,
			show_search_inputs: true,
			set_default: true,
			field: 'user_report_data_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Saved Report' ), form_item_input, tab_payroll_remittance_agency_event_column_1, '', null, true );

		// Payment Frequency reminder days
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'reminder_days', width: 50 } );
		this.addEditFieldToColumn( $.i18n._( 'Reminder Days' ), form_item_input, tab_payroll_remittance_agency_event_column_1, '' );

		//Note
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'note', width: '100%', rows: 5 } );
		this.addEditFieldToColumn( $.i18n._( 'Notes' ), form_item_input, tab_payroll_remittance_agency_event_column_1, '', null, true, true );
		form_item_input.parent().width( '50%' );

		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'enable_recalculate_dates' } );
		this.addEditFieldToColumn( $.i18n._( 'Recalculate Dates' ), form_item_input, tab_payroll_remittance_agency_event_column_1, '', null, true );


		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'start_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Start Date' ), form_item_input, tab_payroll_remittance_agency_event_column_1, '', null, true );

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'end_date' } );
		this.addEditFieldToColumn( $.i18n._( 'End Date' ), form_item_input, tab_payroll_remittance_agency_event_column_1, '', null, true );

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'due_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Due Date' ), form_item_input, tab_payroll_remittance_agency_event_column_1, '', null, true );

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'next_reminder_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Reminder Date' ), form_item_input, tab_payroll_remittance_agency_event_column_1, '', null, true );

	}


} );


PayrollRemittanceAgencyEventViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {
	Global.loadViewSource( 'PayrollRemittanceAgencyEvent', 'SubPayrollRemittanceAgencyEventView.html', function( result ) {
		var args = {};
		var template = _.template( result );
		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}

		if ( Global.isSet( container ) ) {
			container.html( template( args ) );

			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_payroll_remittance_agency_event_controller );
			}
		}
	} );
};