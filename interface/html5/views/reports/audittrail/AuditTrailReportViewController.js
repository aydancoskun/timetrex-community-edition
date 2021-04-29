AuditTrailReportViewController = ReportBaseViewController.extend( {

	_required_files: ['APIAuditTrailReport'],

	initReport: function( options ) {
		this.script_name = 'AuditTrailReport';
		this.viewId = 'AuditTrailReport';
		this.context_menu_name = $.i18n._( 'Audit Trail' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'AuditTrailReportView.html';
		this.api = new ( APIFactory.getAPIClass( 'APIAuditTrailReport' ) )();
	},

	getCustomContextMenuModel: function() {
		return {
			exclude: [],
			include: ['default']
		};
	}

} );