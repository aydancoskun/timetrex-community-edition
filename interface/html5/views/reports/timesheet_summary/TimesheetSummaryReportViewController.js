TimesheetSummaryReportViewController = ReportBaseViewController.extend( {

	_required_files: ['APITimesheetSummaryReport', 'APICurrency', 'APITimeSheetVerify'],

	initReport: function( options ) {
		this.script_name = 'TimesheetSummaryReport';
		this.viewId = 'TimesheetSummaryReport';
		this.context_menu_name = $.i18n._( 'TimeSheet Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'TimesheetSummaryReportView.html';
		this.api = new ( APIFactory.getAPIClass( 'APITimesheetSummaryReport' ) )();
	},

	getCustomContextMenuModel: function() {
		return { include: ['default'] };
	}
} );
