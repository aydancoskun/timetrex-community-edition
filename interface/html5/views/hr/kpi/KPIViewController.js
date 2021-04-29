class KPIViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#kpi_view_container',



			type_array: null,
			status_array: null,

			kpi_group_array: null,
			kpi_group_api: null,

			sub_document_view_controller: null,

			document_object_type_id: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'KPIEditView.html';
		this.permission_id = 'kpi';
		this.viewId = 'KPI';
		this.script_name = 'KPIView';
		this.table_name_key = 'kpi';
		this.context_menu_name = $.i18n._( 'KPI' );
		this.navigation_label = $.i18n._( 'KPI' ) + ':';
		this.api = TTAPI.APIKPI;
		this.kpi_group_api = TTAPI.APIKPIGroup;

		this.document_object_type_id = 210;
		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary();
	}

	initOptions() {
		var $this = this;

		this.initDropDownOption( 'type' );
		this.initDropDownOption( 'status' );

		this.kpi_group_api.getKPIGroup( '', false, false, {
			onResult: function( res ) {
				res = Global.clone( res.getResult() );
				var all = {};
				all.name = '-- ' + $.i18n._( 'All' ) + ' --';
				all.level = 1;
				all.id = TTUUID.not_exist_id;

				if ( res.hasOwnProperty( '0' ) && res[0].hasOwnProperty( 'children' ) ) {
					res[0].children.unshift( all );
				} else {
					res = [
						{ children: [all], id: 0, level: 0, name: 'Root' }
					];
				}

				res = Global.buildTreeRecord( res );
				$this.kpi_group_array = res;
				if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
					$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
				}

			}
		} );
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_key_performance_indicator': { 'label': $.i18n._( 'Key Performance Indicator' ) },
			'tab_attachment': true,
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIKPI,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.KPI,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_key_performance_indicator = this.edit_view_tab.find( '#tab_key_performance_indicator' );

		var tab_key_performance_indicator_column1 = tab_key_performance_indicator.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_key_performance_indicator_column1 );

		//Type
		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'type_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_key_performance_indicator_column1, '' );

		//Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'status_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_key_performance_indicator_column1 );

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_key_performance_indicator_column1 );
		form_item_input.parent().width( '45%' );

		//Group
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			tree_mode: true,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.TREE_COLUMN,
			set_empty: true,
			field: 'group_id'
		} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.kpi_group_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Group' ), form_item_input, tab_key_performance_indicator_column1 );

		//Minimum Rating
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'minimum_rate', width: 50 } );
		this.addEditFieldToColumn( $.i18n._( 'Minimum Rating' ), form_item_input, tab_key_performance_indicator_column1, '', null, true );

		//Maximum Rating
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'maximum_rate', width: 50 } );
		this.addEditFieldToColumn( $.i18n._( 'Maximum Rating' ), form_item_input, tab_key_performance_indicator_column1, '', null, true );

		//Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_key_performance_indicator_column1 );

		form_item_input.parent().width( '45%' );

		//Tags
		form_item_input = Global.loadWidgetByName( FormItemType.TAG_INPUT );

		form_item_input.TTagInput( { field: 'tag', object_type_id: 310 } );
		this.addEditFieldToColumn( $.i18n._( 'Tags' ), form_item_input, tab_key_performance_indicator_column1, '', null, null, true );
	}

	buildSearchFields() {

		super.buildSearchFields();
		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Tags' ),
				field: 'tag',
				basic_search: true,
				adv_search: false,
				in_column: 1,
				object_type_id: 310,
				form_item_type: FormItemType.TAG_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Group' ),
				in_column: 2,
				multiple: true,
				field: 'group_id',
				layout_name: ALayoutIDs.TREE_COLUMN,
				tree_mode: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Description' ),
				in_column: 2,
				field: 'description',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
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
				layout_name: ALayoutIDs.USER,
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	}

	setCurrentEditRecordData() {

		// When mass editing, these fields may not be the common data, so their value will be undefined, so this will cause their change event cannot work properly.
		this.setDefaultData( {
			'type_id': 10
		} );

		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		this.collectUIDataToCurrentEditRecord();

		this.setEditViewDataDone();
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		this.onTypeChange();
	}

	onFormItemChange( target, doNotValidate ) {

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		this.current_edit_record[key] = c_value;

		if ( key === 'type_id' ) {
			this.onTypeChange();
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	onTypeChange() {
		if ( this.current_edit_record.type_id == 10 ) {
			this.attachElement( 'minimum_rate' );
			this.attachElement( 'maximum_rate' );

		} else {
			this.detachElement( 'minimum_rate' );
			this.detachElement( 'maximum_rate' );
		}

		this.editFieldResize();
	}
}
