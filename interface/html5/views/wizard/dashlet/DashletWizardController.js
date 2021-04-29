DashletWizardController = BaseWizardController.extend( {

	el: '.wizard-bg',
	user_generic_data_api: null,
	api_user_report: null,
	numArray: [
		{ label: $.i18n._( 'Default' ), value: 0 },
		{ label: 5, value: 5 },
		{ label: 10, value: 10 },
		{ label: 15, value: 15 },
		{ label: 20, value: 20 },
		{ label: 25, value: 25 },
		{ label: 50, value: 50 },
		{ label: 100, value: 100 },
		{ label: 250, value: 250 },
		{ label: 500, value: 500 },
		{ label: 1000, value: 1000 }
	],
	report_apis: {
		AccrualBalanceSummaryReport: 'APIAccrualBalanceSummaryReport',
		AuditTrailReport: 'APIAuditTrailReport',
		UserSummaryReport: 'APIUserSummaryReport',
		ExceptionReport: 'APIExceptionSummaryReport',
		UserExpenseReport: 'APIUserExpenseReport',
		PayStubSummaryReport: 'APIPayStubSummaryReport',
		PunchSummaryReport: 'APIPunchSummaryReport',
		UserQualificationReport: 'APIUserQualificationReport',
		UserRecruitmentDetailReport: 'APIUserRecruitmentDetailReport',
		UserRecruitmentSummaryReport: 'APIUserRecruitmentSummaryReport',
		KPIReport: 'APIKPIReport',
		ScheduleSummaryReport: 'APIScheduleSummaryReport',
		TimeSheetDetailReport: 'APITimesheetDetailReport',
		TimeSheetSummaryReport: 'APITimesheetSummaryReport',
		JobSummaryReport: 'APIJobSummaryReport',
		JobDetailReport: 'APIJobDetailReport',
		JobInformationReport: 'APIJobInformationReport',
		JobItemInformationReport: 'APIJobItemInformationReport',
		ActiveShiftReport: 'APIActiveShiftReport'
	},

	init: function( options ) {
		//this._super('initialize', options );
		this.title = $.i18n._( 'Dashlet Wizard' );
		this.steps = 2;
		this.current_step = 1;
		this.user_generic_data_api = new ( APIFactory.getAPIClass( 'APIUserGenericData' ) )();
		this.api_user_report = new ( APIFactory.getAPIClass( 'APIUserReportData' ) )();
		this.render();
	},

	render: function() {
		this._super( 'render' );
		this.initCurrentStep();
	},

	//Create each page UI
	buildCurrentStepUI: function() {
		var $this = this;
		this.stepsWidgetDic[this.current_step] = {};
		this.content_div.empty();
		var combobox;

		switch ( this.current_step ) {
			case 1:
				var label = this.getLabel();
				var $this = this;
				label.text( $.i18n._( 'Choose the type of dashlet' ) );
				this.content_div.append( label );
				combobox = this.getComboBox( 'dashlet_type', false );
				combobox.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
					$this.stepsDataDic[2] = null;
					$this.stepsWidgetDic[2] = null;
				} );
				combobox.off( 'change' ).on( 'change', function( e ) {
					$this.step1ComboboxChanged( $( e.target ).val() );
				} );
				this.content_div.append( combobox );
				this.stepsWidgetDic[this.current_step][combobox.getField()] = combobox;

				break;
			case 2:
				label = this.getLabel();
				var step_1_data = this.stepsDataDic[1];
				var form_item;
				var form_item_label;
				var form_item_input_div;
				var textInput;

				if ( step_1_data.dashlet_type == 'custom_list' ) {
					label.text( $.i18n._( 'Choose a list view and layout to display in the dashlet' ) );
					this.content_div.append( label );

					// Choose view
					form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
					form_item_label = form_item.find( '.form-item-label' );
					form_item_input_div = form_item.find( '.form-item-input-div' );
					combobox = this.getComboBox( 'script', false );
					combobox.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
						$this.setLayout( $this.getScriptNameByAPIViewKey( target.getValue() ) );
						$this.setDefaultName( step_1_data.dashlet_type );
					} );
					form_item_label.text( $.i18n._( 'List View' ) + ': ' );
					form_item_input_div.append( combobox );
					this.content_div.append( form_item );
					this.stepsWidgetDic[this.current_step][combobox.getField()] = combobox;

					//Dashlet Title
					form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
					form_item_label = form_item.find( '.form-item-label' );
					form_item_input_div = form_item.find( '.form-item-input-div' );
					textInput = this.getTextInput( 'name', false );
					form_item_label.text( $.i18n._( 'Dashlet Title' ) + ': ' );
					form_item_input_div.append( textInput );
					this.content_div.append( form_item );
					this.stepsWidgetDic[this.current_step][textInput.getField()] = textInput;

					//Choose layout
					form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
					form_item_label = form_item.find( '.form-item-label' );
					form_item_input_div = form_item.find( '.form-item-input-div' );
					combobox = this.getComboBox( 'layout', false );
					combobox.setValueKey( 'id' );
					combobox.setLabelKey( 'name' );
					form_item_label.text( $.i18n._( 'Saved Layout' ) + ': ' );
					form_item_input_div.append( combobox );
					this.content_div.append( form_item );
					this.stepsWidgetDic[this.current_step][combobox.getField()] = combobox;

					// Rows per page
					form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
					form_item_label = form_item.find( '.form-item-label' );
					form_item_input_div = form_item.find( '.form-item-input-div' );
					combobox = this.getComboBox( 'rows_per_page', false );
					combobox.setSourceData( this.numArray );
					form_item_label.text( $.i18n._( 'Rows per page' ) + ': ' );
					form_item_input_div.append( combobox );
					this.content_div.append( form_item );
					this.stepsWidgetDic[this.current_step][combobox.getField()] = combobox;

				} else if ( step_1_data.dashlet_type == 'custom_report' ) {
					label.text( $.i18n._( 'Choose a saved report to display in the dashlet' ) );
					this.content_div.append( label );

					// Choose a Report
					form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
					form_item_label = form_item.find( '.form-item-label' );
					form_item_input_div = form_item.find( '.form-item-input-div' );
					combobox = this.getComboBox( 'report', false );
					combobox.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
						$this.setTemplateSource();
						$this.stepsWidgetDic[2].template.setValue( 'saved_report' );
						$this.setSavedReport();
						$this.setDefaultName( step_1_data.dashlet_type );
					} );
					form_item_label.text( $.i18n._( 'Report' ) + ': ' );
					form_item_input_div.append( combobox );
					this.content_div.append( form_item );
					this.stepsWidgetDic[this.current_step][combobox.getField()] = combobox;

					//Template
					form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
					form_item_label = form_item.find( '.form-item-label' );
					form_item_input_div = form_item.find( '.form-item-input-div' );
					combobox = this.getComboBox( 'template', false );
					form_item_label.text( $.i18n._( 'Template' ) + ': ' );
					form_item_input_div.append( combobox );
					this.content_div.append( form_item );
					this.stepsWidgetDic[this.current_step][combobox.getField()] = combobox;

					combobox.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
						$this.setSavedReport();
					} );

					//Choose saved report
					form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
					form_item_label = form_item.find( '.form-item-label' );
					form_item_input_div = form_item.find( '.form-item-input-div' );
					combobox = this.getComboBox( 'saved_report', false );
					combobox.setValueKey( 'id' );
					combobox.setLabelKey( 'name' );
					form_item_label.text( $.i18n._( 'Saved Report' ) + ': ' );
					form_item_input_div.append( combobox );
					this.content_div.append( form_item );
					this.stepsWidgetDic[this.current_step][combobox.getField()] = combobox;

					//Dashlet Title
					form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
					form_item_label = form_item.find( '.form-item-label' );
					form_item_input_div = form_item.find( '.form-item-input-div' );
					textInput = this.getTextInput( 'name', false );
					form_item_label.text( $.i18n._( 'Dashlet Title' ) + ': ' );
					form_item_input_div.append( textInput );
					this.content_div.append( form_item );
					this.stepsWidgetDic[this.current_step][textInput.getField()] = textInput;

				} else {
					label.text( $.i18n._( 'Choose dashlet specific settings' ) );
					this.content_div.append( label );

					//Dashlet Title
					form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
					form_item_label = form_item.find( '.form-item-label' );
					form_item_input_div = form_item.find( '.form-item-input-div' );
					textInput = this.getTextInput( 'name', false );
					form_item_label.text( $.i18n._( 'Dashlet Title' ) + ': ' );
					form_item_input_div.append( textInput );
					this.content_div.append( form_item );
					this.stepsWidgetDic[this.current_step][textInput.getField()] = textInput;

					// Rows per page
					form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
					form_item_label = form_item.find( '.form-item-label' );
					form_item_input_div = form_item.find( '.form-item-input-div' );
					combobox = this.getComboBox( 'rows_per_page', false );
					combobox.setSourceData( this.numArray );
					form_item_label.text( $.i18n._( 'Rows per page' ) + ': ' );
					form_item_input_div.append( combobox );
					this.content_div.append( form_item );
					this.stepsWidgetDic[this.current_step][combobox.getField()] = combobox;

				}

				// Auto refresh
				form_item = $( Global.loadWidget( 'global/widgets/wizard_form_item/WizardFormItem.html' ) );
				form_item_label = form_item.find( '.form-item-label' );
				form_item_input_div = form_item.find( '.form-item-input-div' );
				combobox = this.getComboBox( 'auto_refresh', false );
				form_item_label.text( $.i18n._( 'Auto Refresh' ) + ': ' );
				form_item_input_div.append( combobox );
				this.content_div.append( form_item );
				this.stepsWidgetDic[this.current_step][combobox.getField()] = combobox;

				break;
		}
	},

	step1ComboboxChanged: function( value ) {
		if ( Global.getProductEdition() <= 10 && ( value == 'custom_list' || value == 'custom_report' ) ) {
			TAlertManager.showAlert( Global.getUpgradeMessage(), $.i18n._( 'Denied' ) );
			Global.setWidgetEnabled( this.next_btn, false );
		} else {
			Global.setWidgetEnabled( this.next_btn, true );
		}
	},

	setDefaultName: function( dashlet_type ) {
		var step_2_ui = this.stepsWidgetDic[2];
		var step_1_ui = this.stepsWidgetDic[1];
		if ( dashlet_type === 'custom_list' ) {
			step_2_ui.name.setValue( step_2_ui.script.getLabel() );
		} else if ( dashlet_type === 'custom_report' ) {
			step_2_ui.name.setValue( step_2_ui.report.getLabel() );
		} else {
			step_2_ui.name.setValue( step_1_ui.dashlet_type.getLabel() );
		}
	},

	buildCurrentStepData: function() {
		var $this = this;
		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		var api = new ( APIFactory.getAPIClass( 'APIDashboard' ) )();
		switch ( this.current_step ) {
			case 1:
				api.getOptions( 'dashlets', {
					onResult: function( result ) {
						var result_data = Global.buildRecordArray( result.getResult() );
						current_step_ui.dashlet_type.setSourceData( result_data );
						if ( current_step_data ) {
							if ( current_step_data.dashlet_type ) {
								current_step_ui.dashlet_type.setValue( current_step_data.dashlet_type );
							} else {
								if ( current_step_data.saved_dashlet_id ) {
									$this.user_generic_data_api.getUserGenericData( {
										filter_data: {
											id: current_step_data.saved_dashlet_id,
											deleted: false
										}
									}, {
										onResult: function( result ) {
											if ( Global.isArray( result.getResult() ) && result.getResult().length > 0 ) {
												current_step_data.saved_dashlet = result.getResult()[0];
												current_step_ui.dashlet_type.setValue( current_step_data.saved_dashlet.data.dashlet_type );
											}
										}
									} );
								}

							}
						}
					}
				} );

				break;
			case 2:
				if ( this.stepsDataDic[1].dashlet_type == 'custom_list' ) {
					api.getOptions( 'custom_list', {
						onResult: function( result ) {
							var array = Global.buildRecordArray( result.getResult() );
							current_step_ui.script.setSourceData( array );
							//If has saved steps data
							if ( current_step_data && current_step_data.script ) {
								current_step_ui.script.setValue( current_step_data.script );
								current_step_ui.name.setValue( current_step_data.name );
								current_step_ui.rows_per_page.setValue( current_step_data.rows_per_page );
							} else if ( $this.stepsDataDic[1].saved_dashlet ) {
								current_step_ui.script.setValue( $this.stepsDataDic[1].saved_dashlet.data.view_name );
								current_step_ui.name.setValue( $this.stepsDataDic[1].saved_dashlet.name );
								current_step_ui.rows_per_page.setValue( $this.stepsDataDic[1].saved_dashlet.data.rows_per_page );
							} else {
								current_step_ui.script.setValue( array[0] );
								$this.setDefaultName( $this.stepsDataDic[1].dashlet_type );
							}
							$this.setLayout( $this.getScriptNameByAPIViewKey( current_step_ui.script.getValue() ), current_step_ui.layout );
						}
					} );
				} else if ( this.stepsDataDic[1].dashlet_type == 'custom_report' ) {
					api.getOptions( 'custom_report', {
						onResult: function( result ) {
							var array = Global.buildRecordArray( result.getResult() );
							if ( array.length === 0 ) {
								Global.setWidgetEnabled( $this.done_btn, false );
								return;
							}
							current_step_ui.report.setSourceData( array );
							//If has saved steps data
							if ( current_step_data && current_step_data.report ) {
								current_step_ui.report.setValue( current_step_data.report );
								current_step_ui.name.setValue( current_step_data.name );
							} else if ( $this.stepsDataDic[1].saved_dashlet ) {
								current_step_ui.report.setValue( $this.stepsDataDic[1].saved_dashlet.data.report );
								current_step_ui.name.setValue( $this.stepsDataDic[1].saved_dashlet.name );
							} else {
								current_step_ui.report.setValue( array.value );
								$this.setDefaultName( $this.stepsDataDic[1].dashlet_type );
							}
							$this.setTemplateSource( current_step_ui.report.getValue() );
							if ( current_step_data && current_step_data.report ) {
								current_step_ui.template.setValue( current_step_data.template );
							} else if ( $this.stepsDataDic[1].saved_dashlet ) {
								current_step_ui.template.setValue( $this.stepsDataDic[1].saved_dashlet.data.template );
							} else {
								current_step_ui.template.setValue( 'saved_report' );
							}
							$this.setSavedReport( current_step_ui.report.getValue(), current_step_ui.template.getValue() );
						}
					} );
				} else {
					if ( current_step_data ) {
						current_step_ui.name.setValue( current_step_data.name );
						current_step_ui.rows_per_page.setValue( current_step_data.rows_per_page );
					} else if ( $this.stepsDataDic[1].saved_dashlet ) {
						current_step_ui.name.setValue( $this.stepsDataDic[1].saved_dashlet.name );
						current_step_ui.rows_per_page.setValue( $this.stepsDataDic[1].saved_dashlet.data.rows_per_page );
					} else {
						$this.setDefaultName( $this.stepsDataDic[1].dashlet_type );
					}
				}
				api.getOptions( 'auto_refresh', {
					onResult: function( result ) {
						var array = Global.buildRecordArray( result.getResult() );
						current_step_ui.auto_refresh.setSourceData( array );
						//If has saved steps data
						if ( current_step_data && current_step_data.auto_refresh ) {
							current_step_ui.auto_refresh.setValue( current_step_data.auto_refresh );
						} else if ( $this.stepsDataDic[1].saved_dashlet ) {
							current_step_ui.auto_refresh.setValue( $this.stepsDataDic[1].saved_dashlet.data.auto_refresh );
						}
					}
				} );
				break;
		}
	},

	setTemplateSource: function() {
		var step_2_widgets = this.stepsWidgetDic[2];
		var script = step_2_widgets.report.getValue();
		var template_combobox = step_2_widgets.template;
		var report_api = new ( APIFactory.getAPIClass( this.report_apis[script] ) )();
		var template_options_result = report_api.getOptions( 'templates', { async: false } );
		var templates = template_options_result.getResult();
		templates = Global.buildRecordArray( templates );
		templates.unshift( {
			fullvalue: 'saved_report',
			id: 'saved_report',
			label: '-- ' + $.i18n._( 'Saved Report' ) + ' --',
			orderValue: 0,
			value: 'saved_report'
		} );
		template_combobox.setSourceData( templates );
	},

	setSavedReport: function() {
		var step_2_data = this.stepsDataDic[2];
		var step_1_data = this.stepsDataDic[1];
		var step_2_widgets = this.stepsWidgetDic[2];
		var layout_combobox = step_2_widgets.saved_report;
		var script = step_2_widgets.report.getValue();
		var template = step_2_widgets.template.getValue();
		if ( template !== 'saved_report' ) {
			layout_combobox.hide();
			layout_combobox.parent().parent().hide();
			return;
		} else {
			layout_combobox.parent().parent().show();
			layout_combobox.show();
		}

		this.api_user_report.getUserReportData( {
			filter_data: {
				script: script
			}
		}, {
			onResult: function( result ) {
				var result_data = result.getResult();
				if ( result_data && result_data.length > 0 ) {
					result_data.sort( function( a, b ) {
							return Global.compare( a, b, 'name' );
						}
					);
				} else {
					result_data = [
						{
							fullValue: 0,
							id: 0,
							name: Global.empty_item,
							label: Global.empty_item,
							orderValue: 0,
							value: 0
						}
					];
				}
				layout_combobox.setSourceData( result_data );
				if ( step_2_data && step_2_data.layout ) {
					layout_combobox.setValue( step_2_data.layout );
				} else if ( step_1_data.saved_dashlet ) {
					layout_combobox.setValue( step_1_data.saved_dashlet.data.saved_report );
				}
			}
		} );
	},

	setLayout: function( script ) {
		var step_2_data = this.stepsDataDic[2];
		var step_1_data = this.stepsDataDic[1];
		var layout_combobox = this.stepsWidgetDic[2].layout;
		this.user_generic_data_api.getUserGenericData( {
			filter_data: {
				script: script,
				deleted: false
			}
		}, {
			onResult: function( result ) {
				var result_data = result.getResult();
				if ( result_data && result_data.length > 0 ) {
					result_data.sort( function( a, b ) {
							return Global.compare( a, b, 'name' );
						}
					);
				} else {
					result_data = [{ id: 0, name: '- ' + $.i18n._( 'Default' ) + ' -' }];
				}
				layout_combobox.setSourceData( result_data );
				if ( step_2_data && step_2_data.layout ) {
					layout_combobox.setValue( step_2_data.layout );
				} else if ( step_1_data.saved_dashlet ) {
					layout_combobox.setValue( step_1_data.saved_dashlet.data.layout_id );
				}

			}
		} );
	},

	getScriptNameByAPIViewKey: function( key ) {
		var result = '';
		switch ( key ) {
			case 'Exception':
				result = 'exceptionView';
				break;
			case 'Request':
				result = 'RequestView';
				break;
			case 'User':
				result = 'EmployeeView';
				break;
			case 'UserExpense':
				result = 'UserExpenseView';
				break;
			case 'Schedule':
				result = 'ScheduleShiftView';
				break;
			case 'Invoice':
				result = 'InvoiceView';
				break;
			case 'Request-Authorization':
				result = 'RequestAuthorizationView';
				break;
			case 'PayPeriodTimeSheetVerify':
				result = 'TimeSheetAuthorizationView';
				break;
			case 'UserExpense-Authorization':
				result = 'ExpenseAuthorizationView';
				break;
			case 'AccrualBalance':
				result = 'AccrualBalanceView';
				break;
			case 'Accrual':
				result = 'AccrualView';
				break;
			case 'RecurringScheduleControl':
				result = 'RecurringScheduleControlView';
				break;
			case 'RecurringScheduleTemplateControl':
				result = 'RecurringScheduleTemplateControlView';
				break;
			case 'Job':
				result = 'JobView';
				break;
			case 'JobItem':
				result = 'JobItemView';
				break;
			case 'UserContact':
				result = 'UserContactView';
				break;
			case 'UserWage':
				result = 'WageView';
				break;
			case 'PayStub':
				result = 'PayStubView';
				break;
			case 'PayStubTransaction':
				result = 'PayStubTransactionView';
				break;
			case 'PayPeriod':
				result = 'PayPeriodsView';
				break;
			case 'PayStubAmendment':
				result = 'PayStubAmendmentView';
				break;
			case 'Client':
				result = 'ClientView';
				break;
			case 'ClientContact':
				result = 'ClientContactView';
				break;
			case 'Transaction':
				result = 'InvoiceTransaction';
				break;
			case 'UserReviewControl':
				result = 'UserReviewControlView';
				break;
			case 'JobVacancy':
				result = 'JobVacancyView';
				break;
			case 'JobApplicant':
				result = 'JobApplicantView';
				break;
			case 'JobApplication':
				result = 'JobApplicationView';
				break;
		}

		return result;
	},

	onDoneClick: function() {
		var $this = this;
		this._super( 'onDoneClick' );
		this.saveCurrentStep();
		this.stepsWidgetDic[2].name.clearErrorStyle();
		var saved_dashlet = this.stepsDataDic[1].saved_dashlet;
		var dashlet_type = this.stepsDataDic[1].dashlet_type;
		var dasglet_name = this.stepsDataDic[2].name;
		var auto_refresh = this.stepsDataDic[2].auto_refresh;
		var rows_per_page = this.stepsDataDic[2].rows_per_page;
		var height = 200;
		var width = 33;
		if ( dashlet_type === 'custom_report' ) {
			width = 99;
		}
		var args = {};
		if ( !dasglet_name ) {
			this.stepsWidgetDic[2].name.setErrorStyle( 'Dashlet title can\'t be empty', true );
			return;
		}

		if ( dashlet_type == 'custom_list' ) {
			var view_name = this.stepsDataDic[2].script;
			var layout_id = this.stepsDataDic[2].layout;
			if ( !saved_dashlet ) {
				args.script = ALayoutIDs.DASHBOARD;
				args.name = dasglet_name;
				args.is_default = 'false';
				args.data = {
					dashlet_type: dashlet_type,
					view_name: view_name,
					layout_id: layout_id,
					auto_refresh: auto_refresh,
					height: height,
					width: width,
					rows_per_page: rows_per_page
				};
			} else {
				args = saved_dashlet;
				args.name = dasglet_name;
				args.is_default = 'false';
				args.data.dashlet_type = dashlet_type;
				args.data.view_name = view_name;
				args.data.layout_id = layout_id;
				args.data.auto_refresh = auto_refresh;
				args.data.rows_per_page = rows_per_page;
			}
		} else if ( dashlet_type == 'custom_report' ) {
			var report = this.stepsDataDic[2].report;
			var saved_report_id = ( this.stepsDataDic[2].saved_report && this.stepsDataDic[2].saved_report != 0 ) ? this.stepsDataDic[2].saved_report : false;
			var template = this.stepsDataDic[2].template;
			if ( template === 'saved_report' && !saved_report_id ) {
				TAlertManager.showAlert( $.i18n._( 'No saved report!' ) );
				return;
			}
			if ( !saved_dashlet ) {
				args.script = ALayoutIDs.DASHBOARD;
				args.name = dasglet_name;
				args.is_default = 'false';
				args.data = {
					dashlet_type: dashlet_type,
					report: report,
					template: template,
					saved_report_id: saved_report_id,
					auto_refresh: auto_refresh,
					height: height,
					width: width
				};
			} else {
				args = saved_dashlet;
				args.name = dasglet_name;
				args.is_default = 'false';
				args.data.dashlet_type = dashlet_type;
				args.data.report = report;
				args.data.template = template;
				args.data.saved_report_id = saved_report_id;
				args.data.auto_refresh = auto_refresh;
				args.data.rows_per_page = rows_per_page;
			}
		} else {
			if ( !saved_dashlet ) {
				args.script = ALayoutIDs.DASHBOARD;
				args.name = dasglet_name;
				args.is_default = 'false';
				args.data = {
					dashlet_type: dashlet_type,
					auto_refresh: auto_refresh,
					rows_per_page: rows_per_page,
					height: height,
					width: width
				};
			} else {
				args = saved_dashlet;
				args.name = dasglet_name;
				args.is_default = 'false';
				args.data.dashlet_type = dashlet_type;
				args.data.auto_refresh = auto_refresh;
				args.data.rows_per_page = rows_per_page;
			}
		}

		this.user_generic_data_api.setUserGenericData( args, {
			onResult: function( result ) {
				if ( result.isValid() ) {
					$this.onCloseClick();
					if ( $this.call_back ) {
						$this.call_back();
					}
				} else {
					TAlertManager.showErrorAlert( result );
				}

			}
		} );
	},

	saveCurrentStep: function() {
		if ( !this.stepsDataDic[this.current_step] ) {
			this.stepsDataDic[this.current_step] = {};
		}

		var current_step_data = this.stepsDataDic[this.current_step];
		var current_step_ui = this.stepsWidgetDic[this.current_step];
		switch ( this.current_step ) {
			case 1:
				current_step_data.dashlet_type = current_step_ui.dashlet_type.getValue();
				break;
			case 2:
				var step_1_data = this.stepsDataDic[1];
				if ( step_1_data.dashlet_type == 'custom_list' ) {
					current_step_data.script = current_step_ui.script.getValue();
					current_step_data.layout = current_step_ui.layout.getValue();
					current_step_data.rows_per_page = current_step_ui.rows_per_page.getValue();
					current_step_data.name = current_step_ui.name.getValue();
				} else if ( step_1_data.dashlet_type == 'custom_report' ) {
					current_step_data.report = current_step_ui.report.getValue();
					current_step_data.saved_report = current_step_ui.saved_report.getValue();
					current_step_data.name = current_step_ui.name.getValue();
					current_step_data.template = current_step_ui.template.getValue();
				} else {
					current_step_data.name = current_step_ui.name.getValue();
					current_step_data.rows_per_page = current_step_ui.rows_per_page.getValue();
				}
				current_step_data.auto_refresh = current_step_ui.auto_refresh.getValue();
				break;
		}

	},

	getProperDashletName: function( step_1_data ) {
		var key = step_1_data['dashlet_type'];
		var name = '';
		switch ( key ) {
			case 'request_summary':
				name = $.i18n._( 'Request Summary' );
				break;
			case 'expense_summary':
				name = $.i18n._( 'Expense Summary' );
				break;
			case 'message_summary':
				name = $.i18n._( 'Message Summary' );
				break;
			case 'exception_summary':
				name = $.i18n._( 'Exception Summary' );
				break;
		}
		return name;
	},

	setDefaultDataToSteps: function() {
		if ( !this.default_data ) {
			return null;
		}
		this.stepsDataDic[1] = {};
		if ( this.getDefaultData( 'saved_dashlet_id' ) ) {
			this.stepsDataDic[1].saved_dashlet_id = this.getDefaultData( 'saved_dashlet_id' );
		} else {
			this.stepsDataDic[1].saved_dashlet_id = false;
		}
	}

} );