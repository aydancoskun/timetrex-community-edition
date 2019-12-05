PayrollRemittanceAgencyEventWizardController = BaseWindowController.extend( {

	el: '.wizard-bg',

	wizard_obj: null,


	init: function() {
		var wizard_id = 'PayrollRemittanceAgencyEventWizard';
		//LocalCacheData[this.wizard_id] is set when the wizard is minimized due to external navigation
		if ( !this.wizard_obj && LocalCacheData[wizard_id] ) {
			this.wizard_obj = LocalCacheData[wizard_id];
			this.wizard_obj.initialize();
			this.wizard_obj.setElement( $( '.tax_wizard' ) );
			delete LocalCacheData[wizard_id];
		} else {
			this.wizard_obj = new ( window[wizard_id] )( { el: $( '.tax_wizard' ) } );
		}
	},

	getRequiredFiles: function() {
		return ['APIPayrollRemittanceAgencyEvent', 'Wizard', 'WizardStep', 'views/payroll/remittance_wizard/PayrollRemittanceAgencyEventWizard'];
	}


} );
