PayrollRemittanceAgencyEventWizardController = BaseWindowController.extend({

	el: '.wizard',

	wizard_obj: null,


	init: function(  ) {
		var wizard_id = 'PayrollRemittanceAgencyEventWizard';
		//LocalCacheData[this.wizard_id] is set when the wizard is minimized due to external navigation
		if ( !this.wizard_obj && LocalCacheData[wizard_id] ) {
			this.wizard_obj = LocalCacheData[wizard_id];
			this.wizard_obj.initialize();
			this.wizard_obj.setElement( $('.wizard') );
			delete LocalCacheData[wizard_id];
		} else {
			this.wizard_obj = new ( window[wizard_id] )({ el:$(".wizard") });
		}
	},

	getRequiredFiles: function() {
		return ['APIPayrollRemittanceAgencyEvent', 'Wizard', 'WizardStep', 'views/payroll/remittance_wizard/PayrollRemittanceAgencyEventWizard'];
	},


});
