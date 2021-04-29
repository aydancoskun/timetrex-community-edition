GeneralLedgerSummaryReportViewController = ReportBaseViewController.extend( {

	_required_files: ['APIGeneralLedgerSummaryReport', 'APIPayStub'],

	initReport: function( options ) {
		this.script_name = 'GeneralLedgerSummaryReport';
		this.viewId = 'GeneralLedgerSummaryReport';
		this.context_menu_name = $.i18n._( 'General Ledger Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'GeneralLedgerSummaryReportView.html';
		this.api = new ( APIFactory.getAPIClass( 'APIGeneralLedgerSummaryReport' ) )();
	},

	onReportMenuClick: function( id ) {
		this.onViewClick( id );
	},

	getCustomContextMenuModel: function() {
		var context_menu_model = {
			groups: {
				export: {
					label: $.i18n._( 'Export' ),
					id: this.viewId + 'Export'
				}
			},
			exclude: [],
			include: []
		};

		var export_icon = {
			label: $.i18n._( 'Export' ),
			id: ContextMenuIconName.print_checks,
			group: 'export',
			icon: 'export-35x35.png',
			type: RibbonSubMenuType.NAVIGATION,
			items: [],
			permission_result: true,
			permission: true
		};

		var export_general_ledger_result = new ( APIFactory.getAPIClass( 'APIPayStub' ) )().getOptions( 'export_general_ledger', { async: false } ).getResult();

		export_general_ledger_result = Global.buildRecordArray( export_general_ledger_result );

		for ( var i = 0; i < export_general_ledger_result.length; i++ ) {
			var item = export_general_ledger_result[i];
			export_icon.items.push( {
				label: item.label,
				id: item.value
			} );
		}

		context_menu_model.include.push( export_icon );

		return context_menu_model;
	}

} );
