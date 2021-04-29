UserSummaryReportViewController = ReportBaseViewController.extend( {

	_required_files: ['APIUserSummaryReport', 'APICurrency'],

	initReport: function( options ) {
		this.script_name = 'UserSummaryReport';
		this.viewId = 'UserSummaryReport';
		this.context_menu_name = $.i18n._( 'Employee Information' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'UserSummaryReportView.html';
		this.api = new ( APIFactory.getAPIClass( 'APIUserSummaryReport' ) )();
	},

	getCustomContextMenuModel: function() {
		return { include: ['default'] };
	}
	,
} );