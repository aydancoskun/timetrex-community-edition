class KPIGroupViewController extends BaseTreeViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#kpi_group_view_container',



			tree_mode: null,
			grid_table_name: null,
			grid_select_id_array: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'KPIGroupEditView.html';
		this.permission_id = 'kpi';
		this.viewId = 'KPIGroup';
		this.script_name = 'KPIGroupView';
		this.table_name_key = 'kpi_group';
		this.context_menu_name = $.i18n._( 'KPI Groups' );
		this.grid_table_name = $.i18n._( 'KPI Groups' );
		this.navigation_label = $.i18n._( 'KPI Group' ) + ':';

		this.tree_mode = true;
		this.primary_tab_label = $.i18n._( 'KPI Group' );
		this.primary_tab_key = 'tab_kpi_group';

		this.api = TTAPI.APIKPIGroup;
		this.grid_select_id_array = [];

		this.render();
		this.buildContextMenu();
		this.initData();
		this.setSelectRibbonMenuIfNecessary();
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: [
				ContextMenuIconName.copy,
				ContextMenuIconName.mass_edit,
				ContextMenuIconName.delete_and_next,
				ContextMenuIconName.save_and_continue,
				ContextMenuIconName.save_and_next,
				ContextMenuIconName.export_excel
			],
			include: []
		};

		return context_menu_model;
	}

}