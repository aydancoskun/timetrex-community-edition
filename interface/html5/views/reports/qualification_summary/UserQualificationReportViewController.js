UserQualificationReportViewController = ReportBaseViewController.extend( {

	_required_files: ['APIUserQualificationReport', 'APIQualification'],

	initReport: function( options ) {
		this.script_name = 'UserQualificationReport';
		this.viewId = 'UserQualificationReport';
		this.context_menu_name = $.i18n._( 'Qualification Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'UserQualificationReportView.html';
		this.api = new (APIFactory.getAPIClass( 'APIUserQualificationReport' ))();
	},

	getCustomContextMenuModel: function () {
		return { include: ['default'] };
	}


	/* jshint ignore:end */


} );