class ProcessTransactionsWizardController extends BaseWindowController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '.process_transactions_wizard',

			wizard_obj: null
		} );

		super( options );
	}

	getRequiredFiles() {
		return [

			'Wizard',
			'WizardStep',
			'views/payroll/process_transactions_wizard/ProcessTransactionsWizard'
		];
	}

	init( external_data ) {
		var $this = this;

		//Delayed require
		require( this.getRequiredFiles(), function() {
			var wizard_id = 'ProcessTransactionsWizard';
			//LocalCacheData[this.wizard_id] is set when the wizard is minimized due to external navigation
			if ( !$this.wizard_obj && LocalCacheData[wizard_id] ) {
				$this.wizard_obj = LocalCacheData[wizard_id];
				delete LocalCacheData[wizard_id];
			} else {
				$this.wizard_obj = new ProcessTransactionsWizard( {
					el: $( '.process_transactions_wizard' ),
					external_data: external_data
				} );
			}
		} );
	}
}
