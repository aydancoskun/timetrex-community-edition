class UserSkillViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#user_skill_view_container',



			proficiency_array: null,
			document_object_type_id: null,
			qualification_group_api: null,
			qualification_api: null,
			qualification_group_array: null,
			source_type_array: null,
			qualification_array: null,

			sub_view_grid_autosize: true
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'UserSkillEditView.html';
		this.permission_id = 'user_skill';
		this.viewId = 'UserSkill';
		this.script_name = 'UserSkillView';
		this.table_name_key = 'user_skill';
		this.context_menu_name = $.i18n._( 'Skills' );
		this.navigation_label = $.i18n._( 'Skill' ) + ':';
		this.api = TTAPI.APIUserSkill;
		this.qualification_api = TTAPI.APIQualification;
		this.qualification_group_api = TTAPI.APIQualificationGroup;
		this.document_object_type_id = 125;
		this.render();

		//call init data in parent view
		if ( !this.sub_view_mode ) {
			this.buildContextMenu();
			this.initData();
			this.setSelectRibbonMenuIfNecessary( 'UserSkill' );
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
			case typeof ( this.parent_view_controller.sub_user_education_view_controller ) !== 'undefined':
				this.parent_view_controller.sub_user_education_view_controller.unSelectAll();
			case typeof ( this.parent_view_controller.sub_user_license_view_controller ) !== 'undefined':
				this.parent_view_controller.sub_user_license_view_controller.unSelectAll();
			case typeof ( this.parent_view_controller.sub_user_membership_view_controller ) !== 'undefined':
				this.parent_view_controller.sub_user_membership_view_controller.unSelectAll();
			case typeof ( this.parent_view_controller.sub_user_language_view_controller ) !== 'undefined':
				this.parent_view_controller.sub_user_language_view_controller.unSelectAll();
				break;
		}
	}

	onAddClick() {

		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
		}

		super.onAddClick();
	}

	initOptions() {
		var $this = this;

		this.initDropDownOption( 'proficiency' );
		this.initDropDownOption( 'source_type' );

		this.qualification_group_api.getQualificationGroup( '', false, false, {
			onResult: function( res ) {
				res = res.getResult();

				res = Global.buildTreeRecord( res );
				$this.qualification_group_array = res;

				if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
					$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
					$this.adv_search_field_ui_dic['group_id'].setSourceData( res );
				}

			}
		} );

		var args = {};
		var filter_data = {};
		filter_data.type_id = [10];
		args.filter_data = filter_data;
		this.qualification_api.getQualification( args, {
			onResult: function( res ) {
				res = res.getResult();

				$this.qualification_array = res;
				if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['qualification_id'] ) {
					$this.basic_search_field_ui_dic['qualification_id'].setSourceData( res );
					$this.adv_search_field_ui_dic['qualification_id'].setSourceData( res );
				}
			}
		} );
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

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_skill': { 'label': $.i18n._( 'Skill' ) },
			'tab_attachment': true,
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIUserSkill,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER_SKILL,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_skill = this.edit_view_tab.find( '#tab_skill' );

		var tab_skill_column1 = tab_skill.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_skill_column1 );

		var form_item_input;
		var widgetContainer;
		var label;

		// Employee
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.USER,
			field: 'user_id',
			set_empty: true,
			show_search_inputs: true
		} );
		var default_args = {};
		default_args.permission_section = 'user_skill';
		form_item_input.setDefaultArgs( default_args );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_skill_column1, '' );

		// Skill
		var args = {};
		var filter_data = {};
		filter_data.type_id = [10];
		args.filter_data = filter_data;

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIQualification,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.QUALIFICATION,
			show_search_inputs: true,
			set_empty: true,
			field: 'qualification_id'
		} );

		form_item_input.setDefaultArgs( args );
		this.addEditFieldToColumn( $.i18n._( 'Skill' ), form_item_input, tab_skill_column1 );

		// Proficiency
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'proficiency_id', set_empty: true } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.proficiency_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Proficiency' ), form_item_input, tab_skill_column1 );

		// First Used Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'first_used_date' } );
		this.addEditFieldToColumn( $.i18n._( 'First Used Date' ), form_item_input, tab_skill_column1 );

		// Last Used Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'last_used_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Last Used Date' ), form_item_input, tab_skill_column1 );

		// Years Experience
		var widgets = [];

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'experience', width: 50 } );

		widgets.push( form_item_input );

		widgetContainer = $( '<div class="widget-h-box"></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'Automatic' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'enable_calc_experience' } );

		widgets.push( form_item_input );

		widgetContainer.append( form_item_input );
		this.addEditFieldToColumn( $.i18n._( 'Years Experience' ), widgets, tab_skill_column1, '', widgetContainer );

		// Expiry Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'expiry_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Expiry Date' ), form_item_input, tab_skill_column1, '', null );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_skill_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		//Tags
		form_item_input = Global.loadWidgetByName( FormItemType.TAG_INPUT );

		form_item_input.TTagInput( { field: 'tag', object_type_id: 251 } );
		this.addEditFieldToColumn( $.i18n._( 'Tags' ), form_item_input, tab_skill_column1, '', null, null, true );
	}

	buildSearchFields() {

		super.buildSearchFields();

		var default_args = {};
		default_args.permission_section = 'user_skill';

		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				default_args: default_args,
				layout_name: ALayoutIDs.USER,
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Proficiency' ),
				in_column: 1,
				field: 'proficiency_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Skill' ),
				in_column: 1,
				field: 'qualification_id',
				layout_name: ALayoutIDs.QUALIFICATION,
				api_class: TTAPI.APIQualification,
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Group' ),
				in_column: 2,
				multiple: true,
				field: 'group_id',
				layout_name: ALayoutIDs.TREE_COLUMN,
				tree_mode: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Source' ),
				in_column: 2,
				multiple: true,
				field: 'source_type_id',
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Expiry Date' ),
				in_column: 1,
				field: 'expiry_date',
				tree_mode: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.DATE_PICKER
			} ),

			new SearchField( {
				label: $.i18n._( 'Tags' ),
				field: 'tag',
				basic_search: true,
				adv_search: true,
				in_column: 1,
				object_type_id: 251,
				form_item_type: FormItemType.TAG_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'First Used Date' ),
				in_column: 2,
				field: 'first_used_date',
				tree_mode: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.DATE_PICKER
			} ),

			new SearchField( {
				label: $.i18n._( 'Last Used Date' ),
				in_column: 2,
				field: 'last_used_date',
				tree_mode: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.DATE_PICKER
			} ),

			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: ALayoutIDs.USER,
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		if ( this.current_edit_record['enable_calc_experience'] ) {
			this.edit_view_ui_dic['experience'].setEnabled( false );
		} else {
			this.edit_view_ui_dic['experience'].setEnabled( true );
		}
	}

	onFormItemChange( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();

		switch ( key ) {
			case 'enable_calc_experience':
				if ( c_value ) {
					this.calcExperience();
				} else {
					this.edit_view_ui_dic['experience'].setEnabled( true );
				}
				break;
			case 'last_used_date':
			case 'first_used_date':
				if ( this.current_edit_record['enable_calc_experience'] ) {
					this.calcExperience();
				}
				break;
		}

		this.current_edit_record[key] = c_value;

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	calcExperience() {
		this.edit_view_ui_dic['experience'].setEnabled( false );
		var first_used_date = this.edit_view_ui_dic['first_used_date'].getValue();
		var last_used_date = this.edit_view_ui_dic['last_used_date'].getValue();

		last_used_date = last_used_date ? last_used_date : new Date().format();

		if ( first_used_date !== '' && last_used_date !== '' ) {
			var result = this.api.calcExperience( first_used_date, last_used_date, { async: false } );
			if ( result ) {
				var experience = result.getResult();
				this.edit_view_ui_dic['experience'].setValue( experience );
			}
		} else {
			this.edit_view_ui_dic['experience'].setValue( 0 );
		}
	}

	searchDone() {
		super.searchDone();
		TTPromise.resolve( 'Employee_Qualifications_Tab', 'UserSkillViewController' );
	}
}

UserSkillViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {
	Global.loadViewSource( 'UserSkill', 'SubUserSkillView.html', function( result ) {
		var args = {};
		var template = _.template( result );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}
		if ( Global.isSet( container ) ) {
			container.html( template( args ) );
			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_user_skill_view_controller );
			}
		}
	} );
};