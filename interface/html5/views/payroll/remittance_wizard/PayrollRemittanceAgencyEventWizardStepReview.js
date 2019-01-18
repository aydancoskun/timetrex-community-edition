PayrollRemittanceAgencyEventWizardStepReview = WizardStep.extend({
	name: 'review',
	api: null,
	_required_files: ['APIPayrollRemittanceAgencyEvent'],
	el: $('.wizard.process_transactions_wizard'),

	init: function(){
		var $this = this;
		require( this._required_files, function(){
			$this.api = new (APIFactory.getAPIClass( 'APIPayrollRemittanceAgencyEvent' ))();
			$this.render();
		});
	},

	getPreviousStepName: function(){
		return 'home';
	},

	getNextStepName: function (){
		return 'submit';
	},

	_render: function(){
		this.setTitle( $.i18n._('Review and Verify Information') );
		this.setInstructions( $.i18n._('Review and verify all necessary information'  + ': ') );

		var $this = this;
		this.getWizardObject().getPayrollRemittanceAgencyEventById( this.getWizardObject().selected_remittance_agency_event_id, null, function(result) {
			$this.getWizardObject().selected_remittance_agency_event = result;

			$this.getWizardObject().buildEventDataBlock('payroll_remittance_agency_event_wizard-review-event_details', result);
			$this.getWizardObject().enableButtons();
			$this.initCardsBlock();

			Debug.Text('Selected tax report type: '+ $this.getWizardObject().selected_remittance_agency_event.type_id,null,null,null,10);
			var tax_button = false;

			switch ( $this.getWizardObject().selected_remittance_agency_event.type_id ) {
				//Canada
				case 'T4':
					tax_button = $this.addButton( ContextMenuIconName.tax_reports,
						Icons.tax_reports,
						$.i18n._('Summary Report'),
						$.i18n._('View a summary report to quickly review and verify information for each employee.')
					);

					$this.addButton( 'GovernmentT4',
						Icons.view_detail,
						$.i18n._('T4 Forms'),
						$.i18n._('Generate T4 forms to review and verify that all necessary boxes are properly filled out.')
					);

					$this.addButton( 'T4FormSetup',
						Icons.save_setup,
						$.i18n._('Form Setup') + ' (' + $.i18n._('Optional') +')',
						$.i18n._('In the event that the T4 report or forms are not showing the correct information, use this Form Setup icon to make adjustments and try again.')
					);
					break;
				case 'T4A':
					tax_button = $this.addButton( ContextMenuIconName.tax_reports,
						Icons.tax_reports,
						$.i18n._('Summary Report'),
						$.i18n._('View a summary report to quickly review and verify information for each employee.')
					);

					$this.addButton( 'GovernmentT4A',
						Icons.view_detail,
						$.i18n._('T4A Forms'),
						$.i18n._('Generate T4A forms to review and verify that all necessary boxes are properly filled out.')
					);

					$this.addButton( 'T4AFormSetup',
						Icons.save_setup,
						$.i18n._('Form Setup') + ' (' + $.i18n._('Optional') +')',
						$.i18n._('In the event that the T4A report or forms are not showing the correct information, use this Form Setup icon to make adjustments and try again.')
					);
					break;

				//US
				case 'FW2':
					tax_button = $this.addButton( ContextMenuIconName.tax_reports,
						Icons.tax_reports,
						$.i18n._('Summary Report'),
						$.i18n._('View a summary report to quickly review and verify information for each employee.')
					);

					$this.addButton( 'GovernmentW2',
						Icons.view_detail,
						$.i18n._('W2 Forms'),
						$.i18n._('Generate W2 forms to review and verify that all necessary boxes are properly filled out.')
					);

					$this.addButton( 'W2FormSetup',
						Icons.save_setup,
						$.i18n._('Form Setup') + ' (' + $.i18n._('Optional') +')',
						$.i18n._('In the event that the W2 report or forms are not showing the correct information, use this Form Setup icon to make adjustments and try again.')
					);
					break;
				case 'F1099MISC':
					tax_button = $this.addButton( ContextMenuIconName.tax_reports,
						Icons.tax_reports,
						$.i18n._('Summary Report'),
						$.i18n._('View a summary report to quickly review and verify information for each employee.')
					);

					$this.addButton( 'Government1099Misc',
						Icons.view_detail,
						$.i18n._('1099-MISC Forms'),
						$.i18n._('Generate 1099-MISC forms to review and verify that all necessary boxes are properly filled out.')
					);

					$this.addButton( '1099MiscFormSetup',
						Icons.save_setup,
						$.i18n._('Form Setup') + ' (' + $.i18n._('Optional') +')',
						$.i18n._('In the event that the 1099-MISC report or forms are not showing the correct information, use this Form Setup icon to make adjustments and try again.')
					);
					break;
				case 'F940':
					tax_button = $this.addButton( ContextMenuIconName.tax_reports,
						Icons.tax_reports,
						$.i18n._('Summary Report'),
						$.i18n._('View a summary report to quickly review and verify information for each employee.')
					);

					$this.addButton( 'Government940',
						Icons.view_detail,
						$.i18n._('940 Form'),
						$.i18n._('Generate the 940 form to review and verify that all necessary boxes are properly filled out.')
					);

					$this.addButton( '940FormSetup',
						Icons.save_setup,
						$.i18n._('Form Setup') + ' (' + $.i18n._('Optional') +')',
						$.i18n._('In the event that the 940 report or forms are not showing the correct information, use this Form Setup icon to make adjustments and try again.')
					);
					break;
				case 'F941':
					tax_button = $this.addButton( ContextMenuIconName.tax_reports,
						Icons.tax_reports,
						$.i18n._('Summary Report'),
						$.i18n._('View a summary report to quickly review and verify information for each employee.')
					);

					$this.addButton( 'Government941',
						Icons.view_detail,
						$.i18n._('941 Form'),
						$.i18n._('Generate the 941 form to review and verify that all necessary boxes are properly filled out.')
					);

					$this.addButton( '941FormSetup',
						Icons.save_setup,
						$.i18n._('Form Setup') + ' (' + $.i18n._('Optional') +')',
						$.i18n._('In the event that the 941 report or forms are not showing the correct information, use this Form Setup icon to make adjustments and try again.')
					);
					break;
				case 'PBJ':
					tax_button = $this.addButton( ContextMenuIconName.tax_reports,
						Icons.tax_reports,
						$.i18n._('Summary Report'),
						$.i18n._('View a summary report to quickly review and verify information for each employee.')
					);

					$this.addButton( 'PBJExportSetup',
					    Icons.save_setup,
					    $.i18n._('Export Setup'),
					    $.i18n._('In the event that the export format is not showing the correct information, use this icon to make adjustments and try again.')
					);
					break;
				case 'ROE':
					tax_button = $this.addButton( ContextMenuIconName.tax_reports,
						Icons.view,
						$.i18n._('ROE Forms'),
						$.i18n._('View ROE forms to review and verify that all necessary boxes are properly filled out.')
					);
					break;
				default:
					tax_button = $this.addButton( ContextMenuIconName.tax_reports,
						Icons.tax_reports,
						$.i18n._('Summary Report'),
						$.i18n._('View a summary report to quickly review and verify information for each employee.')
					);
			}

			if ( tax_button != false ) {
				$this.initRightClickMenuForTaxReportViewButton();
			}
		});

	},

	_onNavigationClick: function( icon ) {
		//When navigating away, link the wizard.
		var $this = this;
		switch ( $this.getWizardObject().selected_remittance_agency_event.type_id ) {
			//Canada
			case 'T4':
				switch (icon) {
					case 'GovernmentT4':
						Global.loadScript('views/reports/t4_summary/T4SummaryReportViewController', function () {
							$this.getWizardObject().getReport('pdf_form_government');
						});
						break;
					case 'taxReportsIcon_new_window':
						this.getWizardObject().showHTMLReport('T4Summary', true);
						break;
					case 'taxReportsIcon':
						this.getWizardObject().showHTMLReport('T4Summary');
						break;
					case 'T4FormSetup':
						this.getWizardObject().minimize();
						IndexViewController.openReport(LocalCacheData.current_open_primary_controller, 'T4SummaryReport', null, 'FormSetup');
						break;
				}
				break;
			case 'T4A':
				switch (icon) {
					case 'GovernmentT4A':
						Global.loadScript('views/reports/t4a_summary/T4ASummaryReportViewController', function () {
							$this.getWizardObject().getReport('pdf_form_government');
						});
						break;
					case 'taxReportsIcon_new_window':
						this.getWizardObject().showHTMLReport('T4ASummary', true);
						break;
					case 'taxReportsIcon':
						this.getWizardObject().showHTMLReport('T4ASummary');
						break;
					case 'T4AFormSetup':
						this.getWizardObject().minimize();
						IndexViewController.openReport(LocalCacheData.current_open_primary_controller, 'T4ASummaryReport', null, 'FormSetup');
						break;
				}
				break;

			//US
			case 'FW2':
				switch (icon) {
					case 'GovernmentW2':
						Global.loadScript('views/reports/formw2/FormW2ReportViewController', function () {
							$this.getWizardObject().getReport('pdf_form_government');
						});
						break;
					case 'taxReportsIcon_new_window':
						this.getWizardObject().showHTMLReport('FormW2Report', true);
						break;
					case 'taxReportsIcon':
						this.getWizardObject().showHTMLReport('FormW2Report');
						break;
					case 'W2FormSetup':
						this.getWizardObject().minimize();
						IndexViewController.openReport(LocalCacheData.current_open_primary_controller, 'FormW2Report', null, 'FormSetup');
						break;
				}
				break;
			case 'F1099MISC':
				switch (icon) {
					case 'Government1099Misc':
						Global.loadScript('views/reports/form1099/Form1099MiscReportViewController', function () {
							$this.getWizardObject().getReport('pdf_form_government');
						});
						break;
					case 'taxReportsIcon_new_window':
						this.getWizardObject().showHTMLReport('Form1099MiscReport', true);
						break;
					case 'taxReportsIcon':
						this.getWizardObject().showHTMLReport('Form1099MiscReport');
						break;
					case '1099MiscFormSetup':
						this.getWizardObject().minimize();
						IndexViewController.openReport(LocalCacheData.current_open_primary_controller, 'Form1099MiscReport', null, 'FormSetup');
						break;
				}
				break;
			case 'F940':
				switch (icon) {
					case 'Government940':
						Global.loadScript('views/reports/form940/Form940ReportViewController', function () {
							$this.getWizardObject().getReport('pdf_form');
						});
						break;
					case 'taxReportsIcon_new_window':
						this.getWizardObject().showHTMLReport('Form940', true);
						break;
					case 'taxReportsIcon':
						this.getWizardObject().showHTMLReport('Form940');
						break;
					case '940FormSetup':
						this.getWizardObject().minimize();
						IndexViewController.openReport(LocalCacheData.current_open_primary_controller, 'Form940Report', null, 'FormSetup');
						break;
				}
				break;
			case 'F941':
				switch (icon) {
					case 'Government941':
						Global.loadScript('views/reports/form941/Form941ReportViewController', function () {
							$this.getWizardObject().getReport('pdf_form');
						});
						break;
					case 'taxReportsIcon_new_window':
						this.getWizardObject().showHTMLReport('Form941', true);
						break;
					case 'taxReportsIcon':
						this.getWizardObject().showHTMLReport('Form941');
						break;
					case '941FormSetup':
						this.getWizardObject().minimize();
						IndexViewController.openReport(LocalCacheData.current_open_primary_controller, 'Form941Report', null, 'FormSetup');
						break;
				}
				break;
			case 'PBJ':
				switch (icon) {
					case 'taxReportsIcon_new_window':
						this.getWizardObject().showHTMLReport('PayrollExportReport', true);
						break;
					case 'taxReportsIcon':
						this.getWizardObject().showHTMLReport('PayrollExportReport');
						break;
					case 'PBJExportSetup':
					    this.getWizardObject().minimize();
					    IndexViewController.openReport(LocalCacheData.current_open_primary_controller, 'PayrollExportReport', null, 'ExportSetup');
					    break;
				}
				break;
			case 'ROE':
				switch (icon) {
					case 'taxReportsIcon':
						$this.getWizardObject().getReport('pdf_form');
						break;
				}
				break;
			case 'NEWHIRE':
				switch (icon) {
					case 'taxReportsIcon':
						this.getWizardObject().showHTMLReport('UserSummaryReport');
						break;
					case 'taxReportsIcon_new_window':
						this.getWizardObject().showHTMLReport('UserSummaryReport', true);
						break;
				}
				break;
			default:
				switch (icon) {
					case 'taxReportsIcon_new_window':
						this.getWizardObject().showHTMLReport('TaxSummary', true);
						break;
					case 'taxReportsIcon':
						this.getWizardObject().showHTMLReport('TaxSummary');
						break;
					case 'TaxSummaryFormSetup':
						this.getWizardObject().minimize();
						IndexViewController.openReport(LocalCacheData.current_open_primary_controller, 'TaxSummaryReport');
						break;
				}
		}
	},

	initRightClickMenuForTaxReportViewButton: function( ) {
		var $this = this;
		var selector = '#taxReportsIcon';
		if ( $( selector ).length == 0 ) {
			return;
		}
		var items = this.getViewButtonRightClickItems();

		if ( !items || $.isEmptyObject( items ) ) {
			return;
		}
		$.contextMenu( 'destroy', selector );
		$.contextMenu( {
			selector: selector,
			callback: function( key, options ) {
				$this.onNavigationClick( null, key );
			},

			onContextMenu: function() {
				return false;
			},
			items: items,
			zIndex: 50
		} );
	},

	getViewButtonRightClickItems: function() {
		var $this = this;
		var items = {};

		items['taxReportsIcon'] = {
			name: $.i18n._( 'View' ), icon: 'viewIcon', disabled: function() {
				return isDisabled();
			}
		};

		items['taxReportsIcon_new_window'] = {
			name: $.i18n._( 'View in New Window' ), icon: 'viewIcon', disabled: function() {
				return isDisabled();
			}
		};

		function isDisabled() {
			if ( $( '#taxReportsIcon' ).parent().hasClass( 'disable-image' ) ) {
				return true;
			} else {
				return false;
			}
		}

		return items;
	},
});