PayrollRemittanceAgencyEventWizardStepSubmit = WizardStep.extend( {
	name: 'summary',
	api: null,
	el: $( '.wizard.process_transactions_wizard' ),

	report_paths: {
		'FormW2ReportViewController': 'views/reports/formw2/FormW2ReportViewController'
	},

	init: function() {
		this.render();

		this.api = new (APIFactory.getAPIClass( 'APIPayrollRemittanceAgencyEvent' ))();

	},

	getPreviousStepName: function() {
		return 'review';
	},

	getNextStepName: function() {
		switch ( this.getWizardObject().selected_remittance_agency_event.type_id ) {
				//Canada
			case 'T4':
			case 'T4A':

				//US
			case 'FW2':
			case 'F1099MISC':
				return 'publish';
				break;

			default:
				return false;
				break;
		}
	},

	_render: function() {
		this.setTitle( $.i18n._( 'Submit Verified Information' ) );
		this.setInstructions( $.i18n._( 'Submit verified information to the agency' ) + ': ' );

		var $this = this;
		this.getWizardObject().getPayrollRemittanceAgencyEventById( this.getWizardObject().selected_remittance_agency_event_id, null, function( result ) {
			$this.getWizardObject().selected_remittance_agency_event = result;
			$this.getWizardObject().buildEventDataBlock( 'payroll_remittance_agency_event_wizard-review-event_details', result );
			$this.getWizardObject().enableButtons();
			$this.initCardsBlock();

			switch ( $this.getWizardObject().selected_remittance_agency_event.type_id ) {
					//Canada
				case 'T4':
				case 'T4A':
					$this.addButton( 'efileDownload',
							Icons.e_file,
							$.i18n._( 'Download eFile' ),
							$.i18n._( 'Download the file to your computer for eFiling in the below step.' )
					);
					$this.addButton( ContextMenuIconName.e_file,
							Icons.e_file,
							$.i18n._( 'Upload eFile' ),
							$.i18n._( 'Navigate to the agency\'s website and upload the eFile downloaded in the above step.' )
					);
					break;

					//US
				case 'FW2':
					$this.addButton( 'efileDownload',
							Icons.e_file,
							$.i18n._( 'Download eFile' ),
							$.i18n._( 'Download the file to your computer for eFiling in the below step.' )
					);
					$this.addButton( ContextMenuIconName.e_file,
							Icons.e_file,
							$.i18n._( 'Upload eFile' ),
							$.i18n._( 'Navigate to the agency\'s website and upload the eFile downloaded in the above step.' )
					);
					break;
				case 'F1099MISC':
					$this.addButton( 'Government1099Misc',
							Icons.print,
							$.i18n._( '1099-MISC Forms' ),
							$.i18n._( 'Generate the government 1099-MISC forms to print and file manually.' )
					);
					break;
				case 'F940':
					$this.addButton( 'Government940',
							Icons.view_detail,
							$.i18n._( '940 Form' ),
							$.i18n._( 'Generate the 940 form to print and file manually.' )
					);
					$this.addButton( ContextMenuIconName.payment_method,
							Icons.payment_method,
							$.i18n._( 'Make Payment' ),
							$.i18n._( 'Navigate to the agency\'s website to make a payment if necessary.' )
					);
					break;
				case 'F941':
					$this.addButton( 'Government941',
							Icons.view_detail,
							$.i18n._( '941 Form' ),
							$.i18n._( 'Generate the 941 form to print and file manually.' )
					);
					$this.addButton( ContextMenuIconName.payment_method,
							Icons.payment_method,
							$.i18n._( 'Make Payment' ),
							$.i18n._( 'Navigate to the agency\'s website to make a payment if necessary.' )
					);
					break;
				case 'PBJ':
					$this.addButton( 'efileDownload',
							Icons.export_export,
							$.i18n._( 'Download eFile' ),
							$.i18n._( 'Download the file to your computer for eFiling in the below step.' )
					);
					$this.addButton( ContextMenuIconName.e_file,
							Icons.e_file,
							$.i18n._( 'Upload eFile' ),
							$.i18n._( 'Navigate to the agency\'s website and upload the eFile downloaded in the above step.' )
					);
					break;
				case 'ROE':
					$this.addButton( 'efileDownload',
							Icons.export_export,
							$.i18n._( 'Download eFile' ),
							$.i18n._( 'Download the file to your computer for eFiling in the below step.' )
					);
					$this.addButton( ContextMenuIconName.e_file,
							Icons.e_file,
							$.i18n._( 'Upload eFile' ),
							$.i18n._( 'Navigate to the agency\'s website and upload the eFile downloaded in the above step.' )
					);
					break;
				case 'NEWHIRE':
					$this.addButton( ContextMenuIconName.tax_reports,
							Icons.tax_reports,
							$.i18n._( 'Report' ),
							$.i18n._( 'Generate the report to file manually.' )
					);
					$this.addButton( 'efileDownload',
							Icons.export_export,
							$.i18n._( 'Download Excel/CSV File (Optional)' ),
							$.i18n._( 'Download the Excel/CSV file to your computer for eFiling in the below step.' )
					);
					$this.addButton( ContextMenuIconName.e_file,
							Icons.e_file,
							$.i18n._( 'File with Agency' ),
							$.i18n._( 'Navigate to the agency\'s website to file the necessary information.' )
					);
					break;
				case 'AUDIT':
				case 'REPORT':
					$this.addButton( ContextMenuIconName.tax_reports,
							Icons.tax_reports,
							$.i18n._( 'Report' ),
							$.i18n._( 'Generate the report to file manually.' )
					);
					break;
				case 'PAYMENT':
				case 'REPORT+PAYMENT':
					$this.addButton( ContextMenuIconName.tax_reports,
							Icons.tax_reports,
							$.i18n._( 'Report' ),
							$.i18n._( 'Generate the report to file manually.' )
					);

					$this.addButton( ContextMenuIconName.payment_method,
							Icons.payment_method,
							$.i18n._( 'Make Payment' ),
							$.i18n._( 'Navigate to the agency\'s website to make a payment if necessary.' )
					);
					break;
				default:
					$this.addButton( ContextMenuIconName.tax_reports,
							Icons.tax_reports,
							$.i18n._( 'Report' ),
							$.i18n._( 'Generate the report to file manually.' )
					);

					$this.addButton( ContextMenuIconName.payment_method,
							Icons.payment_method,
							$.i18n._( 'Make Payment' ),
							$.i18n._( 'Navigate to the agency\'s website to make a payment if necessary.' )
					);
					break;
			}
		} );
	},

	_onNavigationClick: function( icon ) {
		//When navigating away, link the wizard.
		var $this = this;
		switch ( this.getWizardObject().selected_remittance_agency_event.type_id ) {
				//Canada
			case 'T4':
				switch ( icon ) {
					case 'efileDownload':
						Global.loadScript( 'views/reports/t4_summary/T4SummaryReportViewController', function() {
							$this.getWizardObject().getReport( 'efile_xml' );
						} );
						break;
					case 'eFileIcon':
						//show report.
						this.urlClick( 'file' );
						break;
				}
				break;
			case 'T4A':
				switch ( icon ) {
					case 'efileDownload':
						Global.loadScript( 'views/reports/t4a_summary/T4ASummaryReportViewController', function() {
							$this.getWizardObject().getReport( 'efile_xml' );
						} );
						break;
					case 'eFileIcon':
						//show report.
						this.urlClick( 'file' );
						break;
				}
				break;

				//US
			case 'FW2':
				switch ( icon ) {
					case 'efileDownload':
						Global.loadScript( 'views/reports/formw2/FormW2ReportViewController', function() {
							$this.getWizardObject().getReport( 'efile' );
						} );
						break;
					case 'eFileIcon':
						//show report.
						this.urlClick( 'file' );
						break;
				}
				break;
			case 'F1099MISC':
				switch ( icon ) {
					case 'Government1099Misc':
						Global.loadScript( 'views/reports/form1099/Form1099MiscReportViewController', function() {
							$this.getWizardObject().getReport( 'pdf_form_government' );
						} );
						break;
				}
				break;
			case 'F940':
				switch ( icon ) {
					case 'Government940':
						Global.loadScript( 'views/reports/form940/Form940ReportViewController', function() {
							$this.getWizardObject().getReport( 'pdf_form' );
						} );
						break;
					case  ContextMenuIconName.payment_method:
						//show report.
						this.urlClick( 'payment' );
						break;
				}
				break;
			case 'F941':
				switch ( icon ) {
					case 'Government941':
						Global.loadScript( 'views/reports/form941/Form941ReportViewController', function() {
							$this.getWizardObject().getReport( 'pdf_form' );
						} );
						break;
					case  ContextMenuIconName.payment_method:
						//show report.
						this.urlClick( 'payment' );
						break;
				}
				break;
			case 'PBJ':
				switch ( icon ) {
					case 'efileDownload':
						Global.loadScript( 'views/reports/payroll_export/PayrollExportReportViewController', function() {
							$this.getWizardObject().getReport( 'payroll_export' );
						} );
						break;
					case 'eFileIcon':
						//show report.
						this.urlClick( 'file' );
						break;
				}
				break;
			case 'ROE':
				switch ( icon ) {
					case 'efileDownload':
						$this.getWizardObject().getReport( 'efile_xml' );
						break;
					case 'eFileIcon':
						//show report.
						this.urlClick( 'file' );
						break;
				}
				break;
			case 'NEWHIRE':
				switch ( icon ) {
					case 'taxReportsIcon_new_window':
						this.getWizardObject().showHTMLReport( 'TaxSummary', true );
						break;
					case 'taxReportsIcon':
						this.getWizardObject().showHTMLReport( 'TaxSummary' );
						break;
					case 'efileDownload':
						$this.getWizardObject().getReport( 'csv' ); //Use CSV until we get full eFile support.
						break;
					case 'eFileIcon':
						//show report.
						this.urlClick( 'file' );
						break;
				}
				break;
			default:
				switch ( icon ) {
					case 'taxReportsIcon_new_window':
						this.getWizardObject().showHTMLReport( 'TaxSummary', true );
						break;
					case 'taxReportsIcon':
						this.getWizardObject().showHTMLReport( 'TaxSummary' );
						break;
					case 'efileDownload':
						Global.loadScript( 'views/reports/tax_summary/TaxSummaryReportViewController', function() {
							$this.getWizardObject().getReport( 'efile' );
						} );
						break;
					case 'eFileIcon':
						//show report.
						this.urlClick( 'file' );
						break;
					case ContextMenuIconName.payment_method:
						//show report.
						this.urlClick( 'payment' );
						break;
				}
				break;
		}
	}
} );