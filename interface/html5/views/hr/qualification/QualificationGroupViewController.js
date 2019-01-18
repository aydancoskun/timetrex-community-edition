QualificationGroupViewController = BaseTreeViewController.extend( {
	el: '#qualification_group_view_container',

	_required_files: ['APIQualificationGroup'],

	tree_mode: null,
	grid_table_name: null,
	grid_select_id_array: null,

	init: function( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'QualificationGroupEditView.html';
		this.permission_id = 'qualification';
		this.viewId = 'QualificationGroup';
		this.script_name = 'QualificationGroupView';
		this.table_name_key = 'qualification_group';
		this.context_menu_name = $.i18n._( 'Qualification Groups' );
		this.grid_table_name = $.i18n._( 'Qualification Groups' );
		this.navigation_label = $.i18n._( 'Qualification Group' ) + ':';

		this.tree_mode = true;
		this.primary_tab_label = $.i18n._( 'Qualification Group' );
		this.primary_tab_key = 'tab_qualification_group';

		this.api = new (APIFactory.getAPIClass( 'APIQualificationGroup' ))();
		this.invisible_context_menu_dic[ContextMenuIconName.copy] = true; //Hide some context menus
		this.invisible_context_menu_dic[ContextMenuIconName.mass_edit] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.delete_and_next] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_continue] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.save_and_next] = true;
		this.invisible_context_menu_dic[ContextMenuIconName.export_excel] = true;
		this.grid_select_id_array = [];

		this.render();
		this.buildContextMenu();
		this.initData();
		this.setSelectRibbonMenuIfNecessary();
	}
} );