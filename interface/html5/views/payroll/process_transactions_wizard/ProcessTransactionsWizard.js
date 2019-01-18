ProcessTransactionsWizard = Wizard.extend({
	el: $('.process_transactions_wizard'),
	current_step: false,
	wizard_name: $.i18n._('Process Transactions'),

	selected_transaction_ids: [],

	wizard_id: 'ProcessTransactionsWizard',
	_step_map: {
		'home': {
			script_path: 'views/payroll/process_transactions_wizard/ProcessTransactionsWizardStepHome.js',
			object_name: 'ProcessTransactionsWizardStepHome'
		}

	},
	api: null,
	_required_files: ['APIPayrollRemittanceAgencyEvent'],

	setTransactionIds: function ( data ) {
		this.selected_transaction_ids = data;
		if ( this.selected_transaction_ids.length > 0 ) {
			$('.process_transactions_wizard .done-btn').removeClass('disable-image');
		} else {
			$('.process_transactions_wizard .done-btn').addClass('disable-image');
		}
	},

	/**
	 * @param e
	 */
	onDone: function ( e ) {
		if ( e && $(e.target).hasClass('disable-image') == false ) {
			var $this = LocalCacheData.current_open_wizard_controller; //required to bring the object back into scope from the event.

			var data = { filter_data: {} };
			var external_data = $this.getExternalData();
			if ( external_data ) {
				if ( !external_data.filter_data ) {
					data.filter_data = external_data;
				} else {
					data.filter_data = external_data.filter_data;
				}
			}

			if ( !data || !data.filter_data){
				data.filter_data = {};
			}

			if ( !data.filter_data.remittance_source_account_id ) {
				data.filter_data.remittance_source_account_id = [];
			}

			if ( !data.filter_data.setup_last_check_number ) {
				data.setup_last_check_number = {}
			}

			var table_rows = $('#process_transactions_wizard_source_account_table tr');
			if (table_rows.length > 0) {
				for (var x = 0; x < table_rows.length; x++) {
					var row = $(table_rows[x]);
					if (row.find('[type="checkbox"]').is(':checked')) {
						data.filter_data.remittance_source_account_id.push(row.find('[type="checkbox"]').val());
						data.setup_last_check_number[row.find('[type="checkbox"]').val()] = row.find('input.last_transaction_number').val();
					}
				}
			}

			if ( data.filter_data.remittance_source_account_id.length > 0 ) {
				$(e.target).addClass('disable-image')
				var post_data = {0: data, 1: true, 2: 'export_transactions'};
				var api = new (APIFactory.getAPIClass('APIPayStub'))();
				Global.APIFileDownload(api.className, 'getPayStub', post_data);
			} else {
				Debug.Text('No source accounts selected', 'ProcessTransactionsWizard.js', 'ProcessTransactionsWizard', 'onDone',10)
			}
			$this.onCloseClick(true);
		}
	},

	onCloseClick: function(e){
		if ( e === true || ( e && $(e.target).hasClass('disable-image') == false ) ) {
			//if process payroll wizard is minimized, click it.
			if ( $('#min_tab_ProcessPayrollWizard')[0] ){
				$( $('#min_tab_ProcessPayrollWizard')[0] ).click();
			}
			this.cleanUp();
		}
	}
});