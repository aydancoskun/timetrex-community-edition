export class DepartmentViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#department_view_container',



			status_array: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'DepartmentEditView.html';
		this.permission_id = 'department';
		this.viewId = 'Department';
		this.script_name = 'DepartmentView';
		this.table_name_key = 'department';
		this.context_menu_name = $.i18n._( 'Departments' );
		this.navigation_label = $.i18n._( 'Department' ) + ':';
		this.api = TTAPI.APIDepartment;

		this.render();

		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'Department' );
	}

	initOptions() {
		var $this = this;
		this.initDropDownOption( 'status' );
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_department': { 'label': $.i18n._( 'Department' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIDepartment,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_department',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_department = this.edit_view_tab.find( '#tab_department' );

		var tab_department_column1 = tab_department.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_department_column1 );

		//Status

		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'status_id' } );
		form_item_input.setSourceData( $this.status_array );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_department_column1, '' );

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_department_column1 );
		form_item_input.parent().width( '45%' );

		// Code

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'manual_id', width: 65 } );
		this.addEditFieldToColumn( $.i18n._( 'Code' ), form_item_input, tab_department_column1 );

		//Allowed GEO Fences
		if ( Global.getProductEdition() >= 20 ) {
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: TTAPI.APIGEOFence,
				allow_multiple_selection: true,
				layout_name: 'global_geo_fence',
				show_search_inputs: true,
				set_empty: true,
				field: 'geo_fence_ids'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Allowed GEO Fences' ), form_item_input, tab_department_column1 );
		}

		//Tags
		form_item_input = Global.loadWidgetByName( FormItemType.TAG_INPUT );

		form_item_input.TTagInput( { field: 'tag', object_type_id: 120 } );
		this.addEditFieldToColumn( $.i18n._( 'Tags' ), form_item_input, tab_department_column1, '', null, null, true );
	}

	buildSearchFields() {

		super.buildSearchFields();
		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				multiple: true,
				basic_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Tags' ),
				field: 'tag',
				basic_search: true,
				in_column: 1,
				form_item_type: FormItemType.TAG_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Code' ),
				field: 'manual_id',
				basic_search: true,
				in_column: 2,
				object_type_id: 120,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: [],
			include: [
				{
					label: $.i18n._( 'Import' ),
					id: ContextMenuIconName.import_icon,
					group: 'other',
					icon: Icons.import_icon,
					permission_result: PermissionManager.checkTopLevelPermission( 'ImportCSVDepartment' ),
					sort_order: 8000
				}
			]
		};

		return context_menu_model;
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case ContextMenuIconName.import_icon:
				this.onImportClick();
				break;
		}
	}

	onImportClick() {
		var $this = this;

		IndexViewController.openWizard( 'ImportCSVWizard', 'Department', function() {
			$this.search();
		} );
	}
}
