class TimesheetDetailReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'TimesheetDetailReport';
		this.viewId = 'TimesheetDetailReport';
		this.context_menu_name = $.i18n._( 'TimeSheet Detail' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'TimesheetDetailReportView.html';
		this.api = TTAPI.APITimesheetDetailReport;
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			groups: {
				timesheet: {
					label: $.i18n._( 'TimeSheet' ),
					id: this.viewId + 'TimeSheet'
				}
			},
			exclude: [],
			include: [
				{
					label: $.i18n._( 'Print TimeSheet' ),
					id: ContextMenuIconName.print_timesheet,
					group: 'timesheet',
					icon: Icons.print,
					type: RibbonSubMenuType.NAVIGATION,
					items: [
						{
							label: $.i18n._( 'Summary' ),
							id: 'pdf_timesheet'
						},
						{
							label: $.i18n._( 'Detailed' ),
							id: 'pdf_timesheet_detail'
						}
					],
					permission_result: true,
					permission: true
				}
			]
		};

		return context_menu_model;
	}

	onReportMenuClick( id ) {

		this.onViewClick( id );
	}

}
