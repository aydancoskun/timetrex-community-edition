export class BranchViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#branch_view_container',

			status_array: null,
			country_array: null,
			province_array: null,

			e_province_array: null,

			company_api: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'BranchEditView.html';
		this.permission_id = 'branch';
		this.viewId = 'Branch';
		this.script_name = 'BranchView';
		this.table_name_key = 'branch';
		this.context_menu_name = $.i18n._( 'Branch' );
		this.navigation_label = $.i18n._( 'Branch' ) + ':';
		this.api = TTAPI.APIBranch;
		this.company_api = TTAPI.APICompany;

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary();
	}

	initOptions() {
		var $this = this;

		this.initDropDownOption( 'status' );
		this.initDropDownOption( 'country', 'country', this.company_api );
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
					permission_result: PermissionManager.checkTopLevelPermission( 'ImportCSVBranch' ),
					permission: null,
					sort_order: 8000
				}
			]
		};

		return context_menu_model;
	}

	onSetSearchFilterFinished() {
		var combo;
		var select_value;
		if ( this.search_panel.getSelectTabIndex() === 0 ) {
			combo = this.basic_search_field_ui_dic['country'];
			select_value = combo.getValue();
			this.setProvince( select_value );
		} else if ( this.search_panel.getSelectTabIndex() === 1 ) {
			combo = this.adv_search_field_ui_dic['country'];
			select_value = combo.getValue();
			this.setProvince( select_value );
		}
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
		IndexViewController.openWizard( 'ImportCSVWizard', 'Branch', function() {
			$this.search();
		} );
	}

	onBuildAdvUIFinished() {

		this.adv_search_field_ui_dic['country'].change( $.proxy( function() {
			var combo = this.adv_search_field_ui_dic['country'];
			var selectVal = combo.getValue();

			this.setProvince( selectVal );

			this.adv_search_field_ui_dic['province'].setValue( null );

		}, this ) );
	}

	onBuildBasicUIFinished() {
		this.basic_search_field_ui_dic['country'].change( $.proxy( function() {
			var combo = this.basic_search_field_ui_dic['country'];
			var selectVal = combo.getValue();

			this.setProvince( selectVal );

			this.basic_search_field_ui_dic['province'].setValue( null );

		}, this ) );
	}

	onFormItemChange( target, doNotValidate ) {

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();

		switch ( key ) {
			case 'country':
				var widget = this.edit_view_ui_dic['province'];
				widget.setValue( null );
				break;
		}

		this.current_edit_record[key] = target.getValue();

		if ( key === 'country' ) {
			this.onCountryChange();
			return;
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	buildEditViewUI() {

		super.buildEditViewUI();
		var $this = this;

		var tab_model = {
			'tab_branch': { 'label': $.i18n._( 'Branch' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		var form_item_input;

		this.navigation.AComboBox( {
			api_class: TTAPI.APIBranch,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_branch',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_branch = this.edit_view_tab.find( '#tab_branch' );

		var tab_branch_column1 = tab_branch.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_branch_column1 );

		//Status

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'status_id' } );
		form_item_input.setSourceData( $this.status_array );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_branch_column1, '' );

		// Name

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_branch_column1 );

		form_item_input.parent().width( '45%' );

		// Code

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'manual_id', width: 65 } );
		this.addEditFieldToColumn( $.i18n._( 'Code' ), form_item_input, tab_branch_column1 );

		// Address1

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'address1', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Address (Line 1)' ), form_item_input, tab_branch_column1 );

		form_item_input.parent().width( '45%' );

		// Address2

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'address2', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Address (Line 2)' ), form_item_input, tab_branch_column1 );

		form_item_input.parent().width( '45%' );

		// city

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'city', width: 149 } );
		this.addEditFieldToColumn( $.i18n._( 'City' ), form_item_input, tab_branch_column1 );

		//Country
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'country', set_empty: true } );
		form_item_input.setSourceData( $this.country_array );
		this.addEditFieldToColumn( $.i18n._( 'Country' ), form_item_input, tab_branch_column1 );

		//Province / State
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'province' } );
		form_item_input.setSourceData( [] );
		this.addEditFieldToColumn( $.i18n._( 'Province/State' ), form_item_input, tab_branch_column1 );

		//City
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'postal_code', width: 149 } );
		this.addEditFieldToColumn( $.i18n._( 'Postal/ZIP Code' ), form_item_input, tab_branch_column1 );

		// Phone

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'work_phone', width: 149 } );
		this.addEditFieldToColumn( $.i18n._( 'Phone' ), form_item_input, tab_branch_column1 );

		// Fax
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'fax_phone', width: 149 } );

		this.addEditFieldToColumn( $.i18n._( 'Fax' ), form_item_input, tab_branch_column1 );

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
			this.addEditFieldToColumn( $.i18n._( 'Allowed GEO Fences' ), form_item_input, tab_branch_column1 );
		}

		//Tags
		form_item_input = Global.loadWidgetByName( FormItemType.TAG_INPUT );

		form_item_input.TTagInput( { field: 'tag', object_type_id: 110 } );
		this.addEditFieldToColumn( $.i18n._( 'Tags' ), form_item_input, tab_branch_column1, '', null, null, true );
	}

	setProvince( val, m ) {
		var $this = this;

		if ( !val || val === '-1' || val === '0' ) {
			$this.province_array = [];
			this.adv_search_field_ui_dic['province'].setSourceData( [] );
			this.basic_search_field_ui_dic['province'].setSourceData( [] );

		} else {

			this.company_api.getOptions( 'province', val, {
				onResult: function( res ) {
					res = res.getResult();
					if ( !res ) {
						res = [];
					}

					$this.province_array = Global.buildRecordArray( res );
					$this.adv_search_field_ui_dic['province'].setSourceData( $this.province_array );
					$this.basic_search_field_ui_dic['province'].setSourceData( $this.province_array );

				}
			} );
		}
	}

	eSetProvince( val, refresh ) {
		var $this = this;
		var province_widget = $this.edit_view_ui_dic['province'];

		if ( !val || val === '-1' || val === '0' ) {
			$this.e_province_array = [];
			province_widget.setSourceData( [] );
		} else {
			this.company_api.getOptions( 'province', val, {
				onResult: function( res ) {
					res = res.getResult();
					if ( !res ) {
						res = [];
					}
					$this.e_province_array = Global.buildRecordArray( res );
					if ( refresh && $this.e_province_array.length > 0 ) {
						$this.current_edit_record.province = $this.e_province_array[0].value;
						province_widget.setValue( $this.current_edit_record.province );
					}
					province_widget.setSourceData( $this.e_province_array );

				}
			} );
		}
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
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Tags' ),
				field: 'tag',
				basic_search: true,
				adv_search: true,
				in_column: 1,
				form_item_type: FormItemType.TAG_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Phone' ),
				field: 'work_phone',
				basic_search: false,
				adv_search: true,
				in_column: 1,
				object_type_id: 110,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Fax' ),
				field: 'fax_phone',
				basic_search: false,
				adv_search: true,
				in_column: 1,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Address (Line1)' ),
				field: 'address1',
				basic_search: false,
				adv_search: true,
				in_column: 2,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Address (Line2)' ),
				field: 'address2',
				basic_search: false,
				adv_search: true,
				in_column: 2,
				form_item_type: FormItemType.TEXT_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Postal/ZIP Code' ),
				field: 'postal_code',
				basic_search: false,
				adv_search: true,
				in_column: 2,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Country' ),
				in_column: 2,
				field: 'country',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.COMBO_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Province/State' ),
				in_column: 2,
				field: 'province',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'City' ),
				field: 'city',
				basic_search: true,
				adv_search: true,
				in_column: 3,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Code' ),
				field: 'manual_id',
				basic_search: true,
				adv_search: true,
				in_column: 3,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 3,
				field: 'created_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: false,
				adv_search: true,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 3,
				field: 'updated_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: false,
				adv_search: true,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} )

		];
	}

}