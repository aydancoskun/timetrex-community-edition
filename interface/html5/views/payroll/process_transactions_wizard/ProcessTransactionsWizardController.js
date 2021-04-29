import { ProcessTransactionsWizard } from '@/views/payroll/process_transactions_wizard/ProcessTransactionsWizard';

export class ProcessTransactionsWizardController extends BaseWindowController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '.process_transactions_wizard',

			wizard_obj: null
		} );

		super( options );
	}

	init( external_data ) {
		var $this = this;

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
	}
}
