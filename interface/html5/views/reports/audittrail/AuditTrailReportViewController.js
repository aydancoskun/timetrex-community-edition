class AuditTrailReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'AuditTrailReport';
		this.viewId = 'AuditTrailReport';
		this.context_menu_name = $.i18n._( 'Audit Trail' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'AuditTrailReportView.html';
		this.api = TTAPI.APIAuditTrailReport;
	}

	getCustomContextMenuModel() {
		return {
			exclude: [],
			include: ['default']
		};
	}

}