ActiveShiftReportViewController = ReportBaseViewController.extend( {

	_required_files: {
		10: ['APIActiveShiftReport'],
		20: ['APIJob', 'APIJobGroup', 'APIJobItemGroup', 'APIJobItem']
	},

	initReport: function( options ) {
		this.script_name = 'ActiveShiftReport';
		this.viewId = 'ActiveShiftReport';
		this.context_menu_name = $.i18n._( 'Whos In Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'ActiveShiftReportView.html';
		this.api = new (APIFactory.getAPIClass( 'APIActiveShiftReport' ))();
	},

	getCustomContextMenuModel: function() {
		var context_menu_model = {
			exclude: [],
			include: ['default']
		};

		return context_menu_model;
	},

	processFilterField: function() {
		for ( var i = 0; i < this.setup_fields_array.length; i++ ) {
			var item = this.setup_fields_array[i];
			if ( item.value === 'user_status_id' ) {
				item.value = 'filter';
			}
		}
	},

	setFilterValue: function( widget, value ) {
		widget.setValue( value.user_status_id );
	},

	onFormItemChangeProcessFilterField: function( target, key ) {
		var filter = target.getValue();
		this.visible_report_values[key] = { user_status_id: filter };
	}

} );