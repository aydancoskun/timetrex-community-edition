TaxSummaryReportViewController = ReportBaseViewController.extend( {

	_required_files: ['APITaxSummaryReport', 'APICompanyDeduction'],

	initReport: function( options ) {
		this.script_name = 'TaxSummaryReport';
		this.viewId = 'TaxSummaryReport';
		this.context_menu_name = $.i18n._( 'Tax Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'TaxSummaryReportView.html';
		this.api = new (APIFactory.getAPIClass( 'APITaxSummaryReport' ))();
	},

	getCustomContextMenuModel: function () {
		return { include: ['default'] };
	}
	
} );