export class UserLanguageViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#user_language_view_container',



			document_object_type_id: null,

			fluency_array: null,
			competency_array: null,
			source_type_array: null,
			qualification_group_array: null,
			qualification_array: null,

			qualification_group_api: null,
			qualification_api: null,

			sub_view_grid_autosize: true
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'UserLanguageEditView.html';
		this.permission_id = 'user_language';
		this.viewId = 'UserLanguage';
		this.script_name = 'UserLanguageView';
		this.table_name_key = 'user_language';
		this.context_menu_name = $.i18n._( 'Languages' );
		this.navigation_label = $.i18n._( 'Language' ) + ':';
		this.api = TTAPI.APIUserLanguage;
		this.qualification_api = TTAPI.APIQualification;
		this.qualification_group_api = TTAPI.APIQualificationGroup;
		this.document_object_type_id = 129;
		this.render();

		//call init data in parent view
		if ( !this.sub_view_mode ) {
			this.buildContextMenu();
			this.initData();
			this.setSelectRibbonMenuIfNecessary();
		}
	}

	showNoResultCover( show_new_btn ) {
		super.showNoResultCover( ( this.sub_view_mode ) ? true : false );
	}

	onGridSelectRow() {
		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
			this.cancelOtherSubViewSelectedStatus();
		} else {
			this.buildContextMenu();
		}
		this.setDefaultMenu();
	}

	onGridSelectAll() {
		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
			this.cancelOtherSubViewSelectedStatus();
		}
		this.setDefaultMenu();
	}

	cancelOtherSubViewSelectedStatus() {
		switch ( true ) {
			case typeof ( this.parent_view_controller.sub_user_skill_view_controller ) !== 'undefined':
				this.parent_view_controller.sub_user_skill_view_controller.unSelectAll();
			case typeof ( this.parent_view_controller.sub_user_membership_view_controller ) !== 'undefined':
				this.parent_view_controller.sub_user_membership_view_controller.unSelectAll();
			case typeof ( this.parent_view_controller.sub_user_education_view_controller ) !== 'undefined':
				this.parent_view_controller.sub_user_education_view_controller.unSelectAll();
			case typeof ( this.parent_view_controller.sub_user_license_view_controller ) !== 'undefined':
				this.parent_view_controller.sub_user_license_view_controller.unSelectAll();
				break;
		}
	}

	onAddClick() {

		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
		}

		super.onAddClick();
	}

	onMassEditClick() {

		var $this = this;
		$this.is_add = false;
		$this.is_viewing = false;
		$this.is_mass_editing = true;
		LocalCacheData.current_doing_context_action = 'mass_edit';
		$this.openEditView();
		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		this.mass_edit_record_ids = [];

		$.each( grid_selected_id_array, function( index, value ) {
			$this.mass_edit_record_ids.push( value );
		} );

		filter.filter_data = {};
		filter.filter_data.id = this.mass_edit_record_ids;

		this.api['getCommon' + this.api.key_name + 'Data']( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();

				$this.unique_columns = {};

				$this.linked_columns = {};

				if ( !result_data ) {
					result_data = [];
				}

				if ( $this.sub_view_mode && $this.parent_key ) {
					result_data[$this.parent_key] = $this.parent_value;
				}

				$this.current_edit_record = result_data;
				$this.initEditView();

			}
		} );
	}

	initOptions() {
		var $this = this;

		this.initDropDownOption( 'fluency' );
		this.initDropDownOption( 'competency' );
		this.initDropDownOption( 'source_type' );

		this.qualification_group_api.getQualificationGroup( '', false, false, {
			onResult: function( res ) {
				res = res.getResult();

				res = Global.buildTreeRecord( res );
				$this.qualification_group_array = res;

				if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
					$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
//				$this.adv_search_field_ui_dic['group_id'].setSourceData( res );
				}

			}
		} );

		var args = {};
		var filter_data = {};
		filter_data.type_id = [40];
		args.filter_data = filter_data;
		this.qualification_api.getQualification( args, {
			onResult: function( res ) {
				res = res.getResult();

				$this.qualification_array = res;
				if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['qualification_id'] ) {
					$this.basic_search_field_ui_dic['qualification_id'].setSourceData( res );
//				$this.adv_search_field_ui_dic['qualification_id'].setSourceData( res );
				}
			}
		} );
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_language': { 'label': $.i18n._( 'Language' ) },
			'tab_attachment': true,
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIUserLanguage,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_user_language',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_language = this.edit_view_tab.find( '#tab_language' );

		var tab_language_column1 = tab_language.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_language_column1 );

		// Employee
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: false,
			layout_name: 'global_user',
			field: 'user_id',
			set_empty: true,
			show_search_inputs: true
		} );
		var default_args = {};
		default_args.permission_section = 'user_language';
		form_item_input.setDefaultArgs( default_args );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_language_column1, '' );

		// Language
		var args = {};
		var filter_data = {};
		filter_data.type_id = [40];
		args.filter_data = filter_data;

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIQualification,
			allow_multiple_selection: false,
			layout_name: 'global_qualification',
			show_search_inputs: true,
			set_empty: true,
			field: 'qualification_id'
		} );

		form_item_input.setDefaultArgs( args );
		this.addEditFieldToColumn( $.i18n._( 'Language' ), form_item_input, tab_language_column1 );

		// Fluency
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'fluency_id' } );
		form_item_input.setSourceData( $this.fluency_array );
		this.addEditFieldToColumn( $.i18n._( 'Fluency' ), form_item_input, tab_language_column1 );

		// Competency
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'competency_id' } );
		form_item_input.setSourceData( $this.competency_array );
		this.addEditFieldToColumn( $.i18n._( 'Competency' ), form_item_input, tab_language_column1 );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_language_column1, '', null, null, true );
		form_item_input.parent().width( '45%' );

		//Tags
		form_item_input = Global.loadWidgetByName( FormItemType.TAG_INPUT );

		form_item_input.TTagInput( { field: 'tag', object_type_id: 254 } );
		this.addEditFieldToColumn( $.i18n._( 'Tags' ), form_item_input, tab_language_column1, '', null, null, true );
	}

	buildSearchFields() {

		super.buildSearchFields();

		var default_args = {};
		default_args.permission_section = 'user_language';

		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				default_args: default_args,
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Language' ),
				in_column: 1,
				field: 'qualification_id',
				layout_name: 'global_qualification',
				api_class: TTAPI.APIQualification,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Group' ),
				in_column: 1,
				multiple: true,
				field: 'group_id',
				layout_name: 'global_tree_column',
				tree_mode: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Source' ),
				in_column: 2,
				multiple: true,
				field: 'source_type_id',
				basic_search: true,
				adv_search: false,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Tags' ),
				field: 'tag',
				basic_search: true,
				adv_search: false,
				in_column: 1,
				object_type_id: 254,
				form_item_type: FormItemType.TAG_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Fluency' ),
				in_column: 2,
				field: 'fluency_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Competency' ),
				in_column: 2,
				field: 'competency_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
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

	searchDone() {
		super.searchDone();
		TTPromise.resolve( 'Employee_Qualifications_Tab', 'UserLanguageViewController' );
	}
}

UserLanguageViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {
	Global.loadViewSource( 'UserLanguage', 'SubUserLanguageView.html', function( result ) {
		var args = {};
		var template = _.template( result );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}
		if ( Global.isSet( container ) ) {
			container.html( template( args ) );
			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_user_language_view_controller );
			}
		}
	} );
};