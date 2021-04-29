class PunchSummaryReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'PunchSummaryReport';
		this.viewId = 'PunchSummaryReport';
		this.context_menu_name = $.i18n._( 'Punch Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'PunchSummaryReportView.html';
		this.api = TTAPI.APIPunchSummaryReport;
	}

	getCustomContextMenuModel() {
		return { include: ['default'] };
	}

}