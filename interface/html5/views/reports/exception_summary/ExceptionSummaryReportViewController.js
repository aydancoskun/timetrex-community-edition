ExceptionSummaryReportViewController = ReportBaseViewController.extend( {

	_required_files: ['APIExceptionSummaryReport', 'APIExceptionPolicy', 'APIPayPeriod'],

	initReport: function( options ) {
		this.script_name = 'ExceptionReport';
		this.viewId = 'ExceptionSummaryReport';
		this.context_menu_name = $.i18n._( 'Exception Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'ExceptionSummaryReportView.html';
		this.api = new ( APIFactory.getAPIClass( 'APIExceptionSummaryReport' ) )();
	},

	getCustomContextMenuModel: function() {
		return { include: ['default'] };
	}

} );