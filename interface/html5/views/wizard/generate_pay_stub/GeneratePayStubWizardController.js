GeneratePayStubWizardController = BaseWizardController.extend( {

	el: '.wizard',

	init: function( options ) {
		//this._super('initialize', options );

		this.title = $.i18n._( 'Generate Pay Stub Wizard' );
		this.steps = 3;
		this.current_step = 1;

		this.render();
	},

	render: function() {
		this._super( 'render' );

		this.initCurrentStep();

	},

	buildCurrentStepUI: function() {
		var $this = this;
		this.content_div.empty();

		this.stepsWidgetDic[this.current_step] = {};

		switch ( this.current_step ) {
			case 1:
				var label = this.getLabel()
				label.text( $.i18n._( "Generate pay stubs for individual employees when manual modifications or a termination occurs. Use Payroll -> Process Payroll if you wish to generate pay stubs for all employees instead." ) )

				this.content_div.append( label );
				break;
			case 2:

				label = this.getLabel();
				label.text( $.i18n._( 'Select one or more pay periods and choose a payroll run type' ) );
				this.content_div.append( label );
				// Pay period
				var form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				var form_item_label = form_item.find( '.form-item-label' );
				var form_item_input_div = form_item.find( '.form-item-input-div' );
				var a_combobox = this.getAComboBox( (APIFactory.getAPIClass( 'APIPayPeriod' )), true, ALayoutIDs.PAY_PERIOD, 'pay_period_id' );
				a_combobox.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
					$this.saveCurrentStep();
					$this.onPayPeriodChange( true );
					$this.setPayRun( target.getValue() );
				} );
				form_item_label.text( $.i18n._( 'Pay Period' ) + ': ' );
				form_item_input_div.append( a_combobox );
				this.content_div.append( form_item );
				this.stepsWidgetDic[this.current_step][a_combobox.getField()] = a_combobox;

				// Payroll Run Type
				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );
				var combobox = this.getComboBox( 'type_id', false );
				form_item_label.text( $.i18n._( 'Payroll Run Type' ) + ': ' );
				form_item_input_div.append( combobox );
				this.content_div.append( form_item );
				this.stepsWidgetDic[this.current_step][combobox.getField()] = combobox;

				combobox.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
					$this.saveCurrentStep();
					$this.onPayrollTypeChange( true );
				} );

				//Carry Forward to Date
				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );
				var date_picker = this.getDatePicker( 'carry_forward_to_date' );
				form_item_label.text( $.i18n._( 'Carry Forward Adjustments to' ) + ': ' );
				form_item_input_div.append( date_picker );
				this.content_div.append( form_item );
				this.stepsWidgetDic[this.current_step][date_picker.getField()] = date_picker;
				this.stepsWidgetDic[this.current_step][date_picker.getField() + '_row'] = form_item;

				form_item.hide();

				//Transaction Date
				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );
				date_picker = this.getDatePicker( 'transaction_date' );
				form_item_label.text( $.i18n._( 'Transaction Date' ) + ': ' );
				form_item_input_div.append( date_picker );
				this.content_div.append( form_item );
				this.stepsWidgetDic[this.current_step][date_picker.getField()] = date_picker;
				this.stepsWidgetDic[this.current_step][date_picker.getField() + '_row'] = form_item;

				form_item.hide();

				//Payroll Run #
				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );
				var textInput = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				textInput = textInput.TTextInput( {
					field: 'run_id',
					width: 20
				} );
				form_item_label.text( $.i18n._( 'Payroll Run' ) + ': ' );
				form_item_input_div.append( textInput );
				this.content_div.append( form_item );
				this.stepsWidgetDic[this.current_step][textInput.getField()] = textInput;
				this.stepsWidgetDic[this.current_step][textInput.getField() + '_row'] = form_item;

				form_item.hide();

				break;
			case 3:
				label = this.getLabel();
				label.text( $.i18n._( 'Select one or more employees' ) );

				a_combobox = this.getAComboBox( (APIFactory.getAPIClass( 'APIUser' )), true, ALayoutIDs.USER, 'user_id', true );
				var div = $( "<div class='wizard-acombobox-div'></div>" );
				div.append( a_combobox );

				this.stepsWidgetDic[this.current_step] = {};
				this.stepsWidgetDic[this.current_step][a_combobox.getField()] = a_combobox;

				this.content_div.append( label );
				this.content_div.append( div );
				break;
		}
	},

	setPayRun: function( pay_period_id ) {
		var api = new (APIFactory.getAPIClass( 'APIPayStub' ))();
		var step_2_ui = this.stepsWidgetDic[2];
		api.getCurrentPayRun( pay_period_id, {
			onResult: function( result ) {
				var data = result.getResult();
				step_2_ui.run_id.setValue( data );
			}
		} );
	},

	buildCurrentStepData: function() {
		var $this = this;
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		var api = new (APIFactory.getAPIClass( 'APIPayStub' ))();
		switch ( this.current_step ) {
			case 2:
				if ( current_step_data.pay_period_id ) {
					var pay_period_ids = current_step_data.pay_period_id
					pay_period_ids = Global.array_unique(pay_period_ids);

					if ( current_step_data ) {
						current_step_ui.pay_period_id.setValue( pay_period_ids );
					}
					$this.setPayRun( pay_period_ids );
				}
				this.onPayPeriodChange();
				break;
			case 3:
				if ( current_step_data.user_id ) {
					var user_ids = current_step_data.user_id;
					user_ids = Global.array_unique(user_ids);
					current_step_ui.user_id.setValue( user_ids );
				}
				break;
		}
	},

	onDoneClick: function() {
		var $this = this;
		this._super( 'onDoneClick' );
		this.saveCurrentStep();

		// Function called stacks: TypeError: Cannot read property 'pay_period_id' of undefined
		if ( !this.stepsDataDic || !this.stepsDataDic[2] || !this.stepsDataDic[3] ) {
			TAlertManager.showAlert( $.i18n._( 'Wizard data is not correct on step 2 or step 3, please open wizard and try again' ) );
			$this.onCloseClick();
		}

		var api = new (APIFactory.getAPIClass( 'APIPayStub' ))();
		var pay_period_ids = this.stepsDataDic[2].pay_period_id;
		var user_ids = this.stepsDataDic[3].user_id;
		var type_id = this.stepsDataDic[2].type_id;
		var run_id = this.stepsDataDic[2].run_id;
		var transaction_date = null;
		var cal_pay_stub_amendment = false;
		if ( type_id == 5 ) {
			transaction_date = this.stepsDataDic[2].carry_forward_to_date;
			cal_pay_stub_amendment = true;
		} else if ( type_id == 20 ) {
			transaction_date = this.stepsDataDic[2].transaction_date;
		}

		api.generatePayStubs( pay_period_ids, user_ids, cal_pay_stub_amendment, run_id, type_id, transaction_date, {onResult: onDoneResult} );
		function onDoneResult( result ) {
			if ( result.isValid() ) {
				var user_generic_status_batch_id = result.getAttributeInAPIDetails( 'user_generic_status_batch_id' );

				if ( user_generic_status_batch_id && TTUUID.isUUID( user_generic_status_batch_id ) && user_generic_status_batch_id != TTUUID.zero_id&& user_generic_status_batch_id != TTUUID.not_exist_id ) {
					UserGenericStatusWindowController.open( user_generic_status_batch_id, user_ids, function() {
						if ( cal_pay_stub_amendment ) {
							var filter = {filter_data: {}};
							var users = {value: user_ids};
							filter.filter_data.user_id = users;
							IndexViewController.goToView( 'PayStubAmendment', filter );
						}

					} );
				}
			}

			$this.onCloseClick();

			if ( $this.call_back ) {
				$this.call_back();
			}
		}

		$this.onCloseClick();
	},

	onPayrollTypeChange: function( refresh ) {
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		var current_step_data = this.stepsDataDic[this.current_step];
		//Error: Uncaught TypeError: Cannot read property 'hide' of undefined in /interface/html5/index.php#!m=PayStub line 221
		if ( !current_step_ui ||
			!current_step_ui['run_id_row'] ||
			!current_step_ui['carry_forward_to_date_row'] ||
			!current_step_ui['transaction_date_row']) {
			return;
		}
		current_step_ui['run_id_row'].hide();
		current_step_ui['carry_forward_to_date_row'].hide();
		current_step_ui['transaction_date_row'].hide();
		var newest_pay_period = this.getNewestPayPeriod( this.selected_pay_periods );
		if ( current_step_data.type_id == 20 ) {
			current_step_ui['run_id_row'].show();
			current_step_ui['transaction_date_row'].show();
			if ( !refresh ) {
				if ( current_step_data.transaction_date ) {
					current_step_ui['transaction_date'].setValue( Global.strToDateTime( current_step_data.transaction_date ).format() );
				} else {
					current_step_ui['transaction_date'].setValue( newest_pay_period ? Global.strToDateTime( newest_pay_period.transaction_date ).format() : null );
				}

			} else {
				current_step_ui['transaction_date'].setValue( newest_pay_period ? Global.strToDateTime( newest_pay_period.transaction_date ).format() : null );
			}
		}
		if ( current_step_data.type_id == 5 ) {
			current_step_ui['carry_forward_to_date_row'].show();
			if ( !refresh ) {
				current_step_ui['carry_forward_to_date'].setValue( current_step_data.carry_forward_to_date || new Date().format() );
			} else {
				current_step_ui['carry_forward_to_date'].setValue( new Date().format() );
			}
		}
	},

	buildPayPeriodStatusIdArray: function( pay_periods ) {
		var result = [];
		for ( var i = 0; i < pay_periods.length; i++ ) {
			var item = pay_periods[i];
			result.push( item.status_id );
		}
		return result;
	},

	getNewestPayPeriod: function( pay_periods ) {
		var result;
		for ( var i = 0; i < pay_periods.length; i++ ) {
			var item = pay_periods[i];
			var date = Global.strToDateTime( item.transaction_date ).getTime();
			if ( !result || date > result ) {
				result = item;
			}
		}
		return result;
	},

	onPayPeriodChange: function( refresh ) {
		var $this = this;
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		var current_step_data = this.stepsDataDic[this.current_step];
		var api = new (APIFactory.getAPIClass( 'APIPayStub' ))();
		var api_pay_period = new (APIFactory.getAPIClass( 'APIPayPeriod' ))();
		var args = {};
		args.filter_data = {};
		args.filter_data.id = current_step_data.pay_period_id;
		if ( !current_step_data.pay_period_id || current_step_data.pay_period_id.length === 0 ) {
			args.filter_data.id = [0];
		}
		api_pay_period.getPayPeriod( args, {
			onResult: function( result ) {
				$this.selected_pay_periods = result.getResult();
				var status_id_array = $this.buildPayPeriodStatusIdArray( $this.selected_pay_periods );
				api.getOptions( 'type', status_id_array, {
					onResult: function( result ) {
						var result_data = result.getResult();
						var type_array = Global.buildRecordArray( result_data );
						current_step_ui['type_id'].setSourceData( type_array );
						if ( !refresh ) {
							if ( !current_step_data.type_id ) {
								current_step_data.type_id = type_array && type_array[0].value;
							}
							current_step_ui['type_id'].setValue( current_step_data.type_id );
						} else {
							current_step_data.type_id = type_array && type_array[0].value;
							current_step_ui['type_id'].setValue( current_step_data.type_id );
						}
						$this.onPayrollTypeChange( refresh );
					}
				} )
			}
		} );

	},

	saveCurrentStep: function() {
		this.stepsDataDic[this.current_step] = {};
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		switch ( this.current_step ) {
			case 1:
				break;
			case 2:
				current_step_data.pay_period_id = current_step_ui.pay_period_id.getValue();
				current_step_data.transaction_date = current_step_ui.transaction_date.getValue();
				current_step_data.carry_forward_to_date = current_step_ui.carry_forward_to_date.getValue();
				current_step_data.type_id = current_step_ui.type_id.getValue();
				current_step_data.run_id = current_step_ui.run_id.getValue();
				break;
			case 3:
				current_step_data.user_id = current_step_ui.user_id.getValue();
				break;
		}

	},

	setDefaultDataToSteps: function() {

		if ( !this.default_data ) {
			return null;
		}

		this.stepsDataDic[2] = {};
		this.stepsDataDic[3] = {};

		if ( this.getDefaultData( 'user_id' ) ) {
			this.stepsDataDic[3].user_id = this.getDefaultData( 'user_id' );
		}

		if ( this.getDefaultData( 'pay_period_id' ) ) {
			this.stepsDataDic[2].pay_period_id = this.getDefaultData( 'pay_period_id' );
		}

	}

} );
