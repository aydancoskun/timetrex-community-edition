DepartmentViewController = BaseViewController.extend( {
	el: '#department_view_container',
	status_array: null,

	initialize: function( options ) {
		this._super( 'initialize', options );
		this.edit_view_tpl = 'DepartmentEditView.html';
		this.permission_id = 'department';
		this.viewId = 'Department';
		this.script_name = 'DepartmentView';
		this.table_name_key = 'department';
		this.context_menu_name = $.i18n._( 'Departments' );
		this.navigation_label = $.i18n._( 'Department' ) + ':';
		this.api = new (APIFactory.getAPIClass( 'APIDepartment' ))();

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'Department' );

	},

	initOptions: function() {
		var $this = this;
		this.initDropDownOption( 'status' );

	},

	buildEditViewUI: function() {

		this._super( 'buildEditViewUI' );

		var $this = this;

		this.setTabLabels( {
			'tab_department': $.i18n._( 'Department' ),
			'tab_audit': $.i18n._( 'Audit' )
		} );


		this.navigation.AComboBox( {
			api_class: (APIFactory.getAPIClass( 'APIDepartment' )),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.DEPARTMENT,
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
		form_item_input.TComboBox( {field: 'status_id'} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_department_column1, '' );

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'name', width: '100%'} );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_department_column1 );
		form_item_input.parent().width( '45%' );


		// Code

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( {field: 'manual_id', width: 65} );
		this.addEditFieldToColumn( $.i18n._( 'Code' ), form_item_input, tab_department_column1 );

		//Allowed GEO Fences
		if (  LocalCacheData.getCurrentCompany().product_edition_id >= 20 ) {
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: (APIFactory.getAPIClass( 'APIGEOFence' )),
				allow_multiple_selection: true,
				layout_name: ALayoutIDs.GEO_FENCE,
				show_search_inputs: true,
				set_empty: true,
				field: 'geo_fence_ids'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Allowed GEO Fences' ), form_item_input, tab_department_column1 );
		}

		//Tags
		form_item_input = Global.loadWidgetByName( FormItemType.TAG_INPUT );

		form_item_input.TTagInput( {field: 'tag', object_type_id: 120} );
		this.addEditFieldToColumn( $.i18n._( 'Tags' ), form_item_input, tab_department_column1, '' );

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );
		this.search_fields = [

			new SearchField( {label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX} ),
			new SearchField( {label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				multiple: true,
				basic_search: true,
				form_item_type: FormItemType.TEXT_INPUT} ),

			new SearchField( {label: $.i18n._( 'Tags' ),
				field: 'tag',
				basic_search: true,
				in_column: 1,
				form_item_type: FormItemType.TAG_INPUT} ),
			new SearchField( {label: $.i18n._( 'Code' ),
				field: 'manual_id',
				basic_search: true,
				in_column: 2,
				object_type_id: 120,
				form_item_type: FormItemType.TEXT_INPUT} ),
			new SearchField( {label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} ),

			new SearchField( {label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: (APIFactory.getAPIClass( 'APIUser' )),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX} )
		];
	},

	buildContextMenuModels: function() {
		var menu = this._super('buildContextMenuModels')[0];

		var import_csv = new RibbonSubMenu( {
			label: $.i18n._( 'Import' ),
			id: ContextMenuIconName.import_icon,
			group: this.getContextMenuGroupByName(menu,'other'),
			icon: Icons.import_icon,
			permission_result: PermissionManager.checkTopLevelPermission( 'ImportCSVDepartment' ),
			permission: null
		} );

		return [menu];
	},

	onContextMenuClick: function( context_btn, menu_name ) {

		this._super( 'onContextMenuClick', context_btn, menu_name );

		var id;

		if ( Global.isSet( menu_name ) ) {
			id = menu_name;
		} else {
			context_btn = $( context_btn );

			id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			if ( context_btn.hasClass( 'disable-image' ) ) {
				return;
			}
		}

		switch ( id ) {
			case ContextMenuIconName.import_icon:
				ProgressBar.showOverlay();
				this.onImportClick();
				break;

		}
	},

	onImportClick: function() {
		var $this = this;

		IndexViewController.openWizard( 'ImportCSVWizard', 'department', function(){
			$this.search();
		} );
	}


} );
