KPIReportViewController = ReportBaseViewController.extend( {

	_required_files: ['APIKPIReport', 'APIUserReviewControl'],

	initReport: function( options ) {
		this.script_name = 'KPIReport';
		this.viewId = 'KPIReport';
		this.context_menu_name = $.i18n._( 'Review Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'KPIReportView.html';
		this.api = new (APIFactory.getAPIClass( 'APIKPIReport' ))();
	},

	getCustomContextMenuModel: function () {
		return { include: ['default'] };
	}

} );