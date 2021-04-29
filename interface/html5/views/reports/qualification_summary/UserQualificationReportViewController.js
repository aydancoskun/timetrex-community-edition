class UserQualificationReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {

		} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'UserQualificationReport';
		this.viewId = 'UserQualificationReport';
		this.context_menu_name = $.i18n._( 'Qualification Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'UserQualificationReportView.html';
		this.api = TTAPI.APIUserQualificationReport;
	}

	getCustomContextMenuModel() {
		return { include: ['default'] };
	}

	/* jshint ignore:end */

}