class ScheduleSummaryReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'ScheduleSummaryReport';
		this.viewId = 'ScheduleSummaryReport';
		this.context_menu_name = $.i18n._( 'Schedule Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'ScheduleSummaryReportView.html';
		this.api = TTAPI.APIScheduleSummaryReport;
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			groups: {
				schedule: {
					label: $.i18n._( 'Schedule' ),
					id: this.script_name + 'Schedule'
				}
			},
			exclude: [],
			include: [
				{
					label: $.i18n._( 'Print' ),
					id: ContextMenuIconName.print,
					group: 'schedule',
					icon: 'print-35x35.png',
					type: RibbonSubMenuType.NAVIGATION,
					items: [
						{
							label: $.i18n._( 'Individual Schedules' ),
							id: 'pdf_schedule'
						},
						{
							label: $.i18n._( 'Group - Combined' ),
							id: 'pdf_schedule_group_combined'
						},
						{
							label: $.i18n._( 'Group - Separated' ),
							id: 'pdf_schedule_group'
						},
						{
							label: $.i18n._( 'Group - Separated (Page Breaks)' ),
							id: 'pdf_schedule_group_pagebreak'
						}
					],
					permission_result: true,
					permission: true
				}
			]
		};

		return context_menu_model;
	}

	// Overriding empty ReportBaseViewController.processFilterField() called from base.openEditView to provide view specific logic.
	processFilterField() {
		for ( var i = 0; i < this.setup_fields_array.length; i++ ) {
			var item = this.setup_fields_array[i];
			if ( item.value === 'status_id' ) {
				item.value = 'filter';
			}
		}
	}

	onReportMenuClick( id ) {
		this.onViewClick( id );
	}

	setFilterValue( widget, value ) {
		widget.setValue( value.status_id );
	}

	onFormItemChangeProcessFilterField( target, key ) {
		var filter = target.getValue();
		this.visible_report_values[key] = { status_id: filter };
	}

}