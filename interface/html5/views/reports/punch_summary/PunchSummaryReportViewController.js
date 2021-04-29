PunchSummaryReportViewController = ReportBaseViewController.extend( {

	_required_files: {
		10: ['APIPunchSummaryReport'],
		20: ['APIJob', 'APIJobItem', 'APIJobGroup', 'APIJobItemGroup']
	},

	initReport: function( options ) {
		this.script_name = 'PunchSummaryReport';
		this.viewId = 'PunchSummaryReport';
		this.context_menu_name = $.i18n._( 'Punch Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'PunchSummaryReportView.html';
		this.api = new ( APIFactory.getAPIClass( 'APIPunchSummaryReport' ) )();
	},

	getCustomContextMenuModel: function() {
		return { include: ['default'] };
	}

} );