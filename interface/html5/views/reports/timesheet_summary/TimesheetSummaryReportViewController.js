export class TimesheetSummaryReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {

		} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'TimesheetSummaryReport';
		this.viewId = 'TimesheetSummaryReport';
		this.context_menu_name = $.i18n._( 'TimeSheet Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'TimesheetSummaryReportView.html';
		this.api = TTAPI.APITimesheetSummaryReport;
	}

	getCustomContextMenuModel() {
		return { include: ['default'] };
	}
}
