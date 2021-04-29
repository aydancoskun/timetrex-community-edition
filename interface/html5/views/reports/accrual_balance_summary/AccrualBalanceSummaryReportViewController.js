class AccrualBalanceSummaryReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {

		} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'AccrualBalanceSummaryReport';
		this.viewId = 'AccrualBalanceSummaryReport';
		this.context_menu_name = $.i18n._( 'Accrual Balance Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'AccrualBalanceSummaryReportView.html';
		this.api = TTAPI.APIAccrualBalanceSummaryReport;
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: [],
			include: ['default']
		};

		return context_menu_model;
	}

	getFormValues() {

		var other = {};

		other.page_orientation = this.current_edit_record.page_orientation;
		other.font_size = this.current_edit_record.font_size;
		other.auto_refresh = this.current_edit_record.auto_refresh;
		other.disable_grand_total = this.current_edit_record.disable_grand_total;
		other.maximum_page_limit = this.current_edit_record.maximum_page_limit;
		other.show_duplicate_values = this.current_edit_record.show_duplicate_values;
		other.accrual_policy_account_id = this.current_edit_record.accrual_policy_account_id;

		if ( this.current_saved_report && Global.isSet( this.current_saved_report.name ) ) {
			other.report_name = _.escape( this.current_saved_report.name );
			other.report_description = this.current_saved_report.description;
		}

		return other;
	}

}