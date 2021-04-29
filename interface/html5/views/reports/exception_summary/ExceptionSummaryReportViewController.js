class ExceptionSummaryReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {

		} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'ExceptionReport';
		this.viewId = 'ExceptionSummaryReport';
		this.context_menu_name = $.i18n._( 'Exception Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'ExceptionSummaryReportView.html';
		this.api = TTAPI.APIExceptionReport;
	}

	getCustomContextMenuModel() {
		return { include: ['default'] };
	}

}