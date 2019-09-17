PayrollRemittanceAgencyEventWizard = Wizard.extend( {
	current_step: false,
	wizard_name: $.i18n._( 'Tax Wizard' ),

	selected_remittance_agency_event: null,
	selected_remittance_agency_event_id: null,
	payroll_remittance_agency_event_block: null,

	wizard_id: 'PayrollRemittanceAgencyEventWizard',
	_step_map: {
		'home': {
			script_path: 'views/payroll/remittance_wizard/PayrollRemittanceAgencyEventWizardStepHome.js',
			object_name: 'PayrollRemittanceAgencyEventWizardStepHome'
		},
		'review': {
			script_path: 'views/payroll/remittance_wizard/PayrollRemittanceAgencyEventWizardStepReview.js',
			object_name: 'PayrollRemittanceAgencyEventWizardStepReview'
		},
		'submit': {
			script_path: 'views/payroll/remittance_wizard/PayrollRemittanceAgencyEventWizardStepSubmit.js',
			object_name: 'PayrollRemittanceAgencyEventWizardStepSubmit'
		},
		'publish': {
			script_path: 'views/payroll/remittance_wizard/PayrollRemittanceAgencyEventWizardStepPublish.js',
			object_name: 'PayrollRemittanceAgencyEventWizardStepPublish'
		}
	},

	init: function() {
		var $this = this;
	},

	render: function() {
		//do render stuff
	},

	/**
	 * builds the event data block used on several steps in this wizard.
	 * @param container_id
	 * @param data
	 */
	buildEventDataBlock: function( container_id, data ) {
		$( '#' + container_id ).remove(); //never allow this to duplicate on the wizard.
		var div = $( '<div id=\'' + container_id + '\' class=\'payroll_remittance_agency_event_wizard_event_details\'><table></table></div>' );
		var step_obj = this.getStepObject( this.getCurrentStepName() );
		step_obj.append( div );


		var even = false;
		var td_label, td_value;

		if ( this.payroll_remittance_agency_event_block == null ) {

			var column_one_keys = [
				{ key: 'legal_entity_legal_name', title: $.i18n._( 'Legal Entity' ) },
				{ key: 'payroll_remittance_agency_name', title: $.i18n._( 'Agency' ) },
				{ key: 'type', title: $.i18n._( 'Event' ) }
			];

			var column_two_keys = [
				{ key: 'frequency', title: $.i18n._( 'Frequency' ) },
				{ key: 'time_period', title: $.i18n._( 'Time Period' ) },
				{ key: 'due_date', title: $.i18n._( 'Due Date' ) }
			];

			var upper_bound = ( column_one_keys.length > column_two_keys.length ) ? column_one_keys.length : column_two_keys.length;

			for ( var i = 0; i < upper_bound; i++ ) {
				var tr = $( '<tr/>' );

				if ( column_one_keys.length > i ) {
					var label = $( '<td class="label col1" />' );
					label.html( column_one_keys[i].title + ':' );

					var value = $( '<td class="value" />' );
					value.html( data[column_one_keys[i].key] );

					tr.append( label );
					tr.append( value );
				} else {
					tr.append( $( '<td/>' ) );
					tr.append( $( '<td/>' ) );
				}

				if ( column_two_keys.length > i ) {
					var label = $( '<td class="label col2" />' );
					label.html( column_two_keys[i].title + ':' );

					var value = $( '<td class="value" />' );
					if ( column_two_keys[i].key == 'time_period' ) {
						value.html( data.start_date + ' - ' + data.end_date );
					} else {
						value.html( data[column_two_keys[i].key] );
					}

					tr.append( label );
					tr.append( value );
				} else {

					tr.append( $( '<td/>' ) );
					tr.append( $( '<td/>' ) );
				}

				$( '#' + container_id + ' table' ).append( tr );
			}

			this.payroll_remittance_agency_event_block = $( '#' + container_id + ' table' ).html();
		} else {
			$( '#' + container_id + ' table' ).html( this.payroll_remittance_agency_event_block );
		}

	},

	/**
	 * both args required.
	 * @param id
	 * @param callback
	 */
	getPayrollRemittanceAgencyEventById: function( id, columns, callback ) {
		//Stright to the callback if nothing has changed. if ( this.selected_remittance_agency_event.id != id) {
		if ( typeof callback == 'function' && this.selected_remittance_agency_event && this.selected_remittance_agency_event.id == id ) {
			callback( this.selected_remittance_agency_event );
		} else {
			var filter = {
				filter_data: {
					id: this.selected_remittance_agency_event_id
				}
			};

			if ( columns == null || typeof columns == 'undefined' ) {
				filter.filter_columns = {
					'payroll_remittance_agency_id': true,
					'legal_entity_legal_name': true,
					'payroll_remittance_agency_name': true,
					'user_report_data_id': true,
					'status': true,
					'status_id': true,
					'type': true,
					'type_id': true,
					'frequency': true,
					'start_date': true,
					'end_date': true,
					'due_date': true
				};
			} else {
				filter.columns = columns;
			}
			var $this = this;
			var api_payroll_remittance_agency_event = new (APIFactory.getAPIClass( 'APIPayrollRemittanceAgencyEvent' ))();
			api_payroll_remittance_agency_event.getPayrollRemittanceAgencyEvent( filter, {
				onResult: function( event_result ) {
					var event_result = event_result.getResult()[0];

					var api_payroll_remittance_agency = new (APIFactory.getAPIClass( 'APIPayrollRemittanceAgency' ))();
					api_payroll_remittance_agency.getPayrollRemittanceAgency( { filter_data: { id: event_result.payroll_remittance_agency_id } }, {
							onResult: function ( agency_result ) {
								event_result.payroll_remittance_agency_obj = agency_result.getResult()[0]; //Merge Event and Agency data together.

								if ( typeof callback == 'function' ) {
									callback( event_result );
								}

							}
						}
					);
				}
			} );
		}
	},

	/**
	 * Allows us to open reports to give us access to their context menu code from within wizards.
	 *
	 * @param report_type
	 * @param report_obj
	 * @param callback
	 */
	getReport: function( render_type, post_data ) {
		if ( !post_data ) {
			post_data = {
				0: this.selected_remittance_agency_event_id,
				1: render_type
			};
		}
		Global.APIFileDownload( 'APIPayrollRemittanceAgencyEvent', 'getReportData', post_data );
	},

	/**
	 * Displays html report. Does not close wizard, but leaves it up in the background.
	 * @param report_name
	 * @param post_data
	 */
	showHTMLReport: function( report_name, new_window ) {
		ProgressBar.showOverlay();
		var api = new (APIFactory.getAPIClass( 'APIPayrollRemittanceAgencyEvent' ))();
		api['getReportData']( this.selected_remittance_agency_event_id, 'html', {
			onResult: function( res ) {
				ProgressBar.closeOverlay();

				if ( res.isValid() ) {
					var result = res.getResult();
					if ( new_window ) {
						var w = window.open();
						w.document.writeln( result.api_retval );
						w.document.close();
					} else if ( result ) {
						IndexViewController.openWizard( 'ReportViewWizard', result.api_retval );
					}
				} else {
					TAlertManager.showErrorAlert( res );
				}
			}
		} );
	},

	/**
	 * @param e
	 */
	onDone: function( e ) {
		if ( e === true || $( e.target ).hasClass( 'disable-image' ) == false ) {
			var $this = this;
			this.getPayrollRemittanceAgencyEventById( this.selected_remittance_agency_event_id, {}, function( result ) {
				if ( result ) {
					result.enable_recalculate_dates = 1;
					result.last_due_date = result.due_date;

					var api_payroll_remittance_agency_event = new (APIFactory.getAPIClass( 'APIPayrollRemittanceAgencyEvent' ))();
					api_payroll_remittance_agency_event.setPayrollRemittanceAgencyEvent( result, false, true, {
						onResult: function( result ) {
							$this.cleanUp();
						}
					} );
				}
			} );
		}
	}

} );