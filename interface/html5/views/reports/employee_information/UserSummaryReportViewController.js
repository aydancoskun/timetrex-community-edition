class UserSummaryReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {

		} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'UserSummaryReport';
		this.viewId = 'UserSummaryReport';
		this.context_menu_name = $.i18n._( 'Employee Information' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'UserSummaryReportView.html';
		this.api = TTAPI.APIUserSummaryReport;
	}

	getCustomContextMenuModel() {
		return { include: ['default'] };
	}
}