class PayStubTransactionSummaryReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {

		} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'PayStubTransactionSummaryReport';
		this.viewId = 'PayStubTransactionSummaryReport';
		this.context_menu_name = $.i18n._( 'Pay Stub Transaction Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'PayStubTransactionSummaryReportView.html';
		this.api = TTAPI.APIPayStubTransactionSummaryReport;
	}

	onReportMenuClick( id ) {
		this.processTransactions( id );
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			groups: {
				export: {
					label: $.i18n._( 'Export' ),
					id: this.viewId + 'Export'
				}
			},
			exclude: [],
			include: [
				{
					label: $.i18n._( 'Process<br>Transactions' ),
					id: ContextMenuIconName.direct_deposit,
					group: 'export',
					icon: 'direct_deposit-35x35.png',
					items: []
				}
			]
		};

		return context_menu_model;
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case ContextMenuIconName.direct_deposit:
				if ( !this.validate( true ) ) {
					return;
				}

				IndexViewController.openWizardController( 'ProcessTransactionsWizardController', { filter_data: this.visible_report_values } );
				break;
		}
	}
}