export class ActiveShiftReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'ActiveShiftReport';
		this.viewId = 'ActiveShiftReport';
		this.context_menu_name = $.i18n._( 'Whos In Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'ActiveShiftReportView.html';
		this.api = TTAPI.APIActiveShiftReport;
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: [],
			include: ['default']
		};

		return context_menu_model;
	}

	processFilterField() {
		for ( var i = 0; i < this.setup_fields_array.length; i++ ) {
			var item = this.setup_fields_array[i];
			if ( item.value === 'user_status_id' ) {
				item.value = 'filter';
			}
		}
	}

	setFilterValue( widget, value ) {
		widget.setValue( value.user_status_id );
	}

	onFormItemChangeProcessFilterField( target, key ) {
		var filter = target.getValue();
		this.visible_report_values[key] = { user_status_id: filter };
	}

}