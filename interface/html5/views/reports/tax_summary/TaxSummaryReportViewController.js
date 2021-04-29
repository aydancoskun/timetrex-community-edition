export class TaxSummaryReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {

		} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'TaxSummaryReport';
		this.viewId = 'TaxSummaryReport';
		this.context_menu_name = $.i18n._( 'Tax Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'TaxSummaryReportView.html';
		this.api = TTAPI.APITaxSummaryReport;
	}

	getCustomContextMenuModel() {
		return { include: ['default'] };
	}

}