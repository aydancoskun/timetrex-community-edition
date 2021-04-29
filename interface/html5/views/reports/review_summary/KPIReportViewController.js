class KPIReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {

		} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'KPIReport';
		this.viewId = 'KPIReport';
		this.context_menu_name = $.i18n._( 'Review Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'KPIReportView.html';
		this.api = TTAPI.APIKPIReport;
	}

	getCustomContextMenuModel() {
		return { include: ['default'] };
	}

}