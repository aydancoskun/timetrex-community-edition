/*
 * $License$
 */

PayStubTransactionSummaryReportViewController = ReportBaseViewController.extend( {

	_required_files: ['APIPayStubTransactionSummaryReport', 'APIPayStub'],

	initReport: function( options ) {
		this.script_name = 'PayStubTransactionSummaryReport';
		this.viewId = 'PayStubTransactionSummaryReport';
		this.context_menu_name = $.i18n._( 'Pay Stub Transaction Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' ) + ':';
		this.view_file = 'PayStubTransactionSummaryReportView.html';
		this.api = new (APIFactory.getAPIClass( 'APIPayStubTransactionSummaryReport' ))();
	},

	onReportMenuClick: function( id ) {
		this.processTransactions( id );
	},

	buildContextMenuModels: function() {

		//Context Menu
		var menu = new RibbonMenu( {
			label: this.context_menu_name,
			id: this.viewId + 'ContextMenu',
			sub_menu_groups: []
		} );

		//menu group
		var editor_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Editor' ),
			id: this.viewId + 'Editor',
			ribbon_menu: menu,
			sub_menus: []
		} );

		//menu group
		var saved_report_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Saved Report' ),
			id: this.viewId + 'SavedReport',
			ribbon_menu: menu,
			sub_menus: []
		} );

		//menu group
		var pay_stub_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Pay Stub' ),
			id: this.script_name + 'PayStub',
			ribbon_menu: menu,
			sub_menus: []
		} );

		//menu group
		var export_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Export' ),
			id: this.viewId + 'Export',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var view_html = new RibbonSubMenu( {
			label: $.i18n._( 'View' ),
			id: ContextMenuIconName.view_html,
			group: editor_group,
			icon: Icons.view,
			permission_result: true,
			permission: null
		} );

		var view_pdf = new RibbonSubMenu( {
			label: $.i18n._( 'PDF' ),
			id: ContextMenuIconName.view,
			group: editor_group,
			icon: Icons.print,
			permission_result: true,
			permission: null
		} );

		var excel = new RibbonSubMenu( {
			label: $.i18n._( 'Excel' ),
			id: ContextMenuIconName.export_excel,
			group: editor_group,
			icon: Icons.export_excel,
			permission_result: true,
			permission: null
		} );

		var cancel = new RibbonSubMenu( {
			label: $.i18n._( 'Cancel' ),
			id: ContextMenuIconName.cancel,
			group: editor_group,
			icon: Icons.cancel,
			permission_result: true,
			permission: null
		} );

		var save_existed_report = new RibbonSubMenu( {
			label: $.i18n._( 'Save' ),
			id: ContextMenuIconName.save_existed_report,
			group: saved_report_group,
			icon: Icons.save,
			permission_result: true,
			permission: null
		} );

		var save_new_report = new RibbonSubMenu( {
			label: $.i18n._( 'Save as New' ),
			id: ContextMenuIconName.save_new_report,
			group: saved_report_group,
			icon: Icons.save_and_new,
			permission_result: true,
			permission: null
		} );

		/**
		 var employee_pay_stubs = new RibbonSubMenu( {
			label: $.i18n._( 'Employee<br>Pay Stubs' ),
			id: ContextMenuIconName.employee_pay_stubs,
			group: pay_stub_group,
			icon: Icons.pay_stubs,
			permission_result: true,
			permission: null
		} );

		 var employer_pay_stubs = new RibbonSubMenu( {
			label: $.i18n._( 'Employer<br>Pay Stubs' ),
			id: ContextMenuIconName.employer_pay_stubs,
			group: pay_stub_group,
			icon: Icons.pay_stubs,
			permission_result: true,
			permission: null
		} );
		 **/

		var direct_deposit = new RibbonSubMenu( {
			label: $.i18n._( 'Process<br>Transactions' ),
			id: ContextMenuIconName.direct_deposit,
			group: export_group,
			icon: 'direct_deposit-35x35.png',
			items: [],
			permission_result: true,
			permission: true
		} );

		return [menu];

	},

	openEditView: function() {

		var $this = this;
		$this.initOptions( function() {

			if ( !$this.edit_view ) {
				$this.initEditViewUI( $this.viewId, $this.view_file );
			}

			$this.do_validate_after_create_ui = true;

			$this.getReportData( function( result ) {
				// Waiting for the (APIFactory.getAPIClass( 'API' )) returns data to set the current edit record.

				var edit_item;
				if ( LocalCacheData.default_edit_id_for_next_open_edit_view ) {
					for ( var i = 0; i < result.length; i++ ) {
						if ( result[i].id == LocalCacheData.default_edit_id_for_next_open_edit_view ) {
							edit_item = result[i];
						}
					}
					LocalCacheData.default_edit_id_for_next_open_edit_view = null;
				} else {
					edit_item = $this.getDefaultReport( result );
				}

				if ( result && result.length > 0 ) {
					$this.current_saved_report = edit_item;
					$this.saved_report_array = result;
				} else {
					$this.current_saved_report = {};
					$this.saved_report_array = [];
				}

				$this.current_edit_record = {};
				$this.visible_report_values = {};

				$this.initEditView();

			} );

		} );

	},

	onCustomContextClick: function( id ) {
		switch ( id ) {
			case ContextMenuIconName.direct_deposit:
				if ( !this.validate( true ) ) {
					return;
				}

				IndexViewController.openWizardController( 'ProcessTransactionsWizardController', { filter_data: this.visible_report_values } );
				break;
		}
	}
} );