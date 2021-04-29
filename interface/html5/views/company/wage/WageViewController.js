class WageViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#wage_view_container', //Must set el here and can only set string, so events can work



			user_api: null,
			user_group_api: null,
			company_api: null,
			currency_api: null,

			type_array: null,
			status_array: null,
			sex_array: null,
			user_group_array: null,
			country_array: null,
			province_array: null,

			e_province_array: null,

			currency: '',
			code: '',

			is_mass_adding: false,
			events: {}
		} );

		super( options );
	}

	init( options ) {

		//this._super('initialize', options );
		this.edit_view_tpl = 'WageEditView.html';
		this.permission_id = 'wage';
		this.script_name = 'WageView';
		this.viewId = 'Wage';
		this.table_name_key = 'user_wage';
		this.context_menu_name = $.i18n._( 'Wage' );
		this.navigation_label = $.i18n._( 'Wage' ) + ':';
		this.document_object_type_id = 110;
		this.api = TTAPI.APIUserWage;
		this.user_group_api = TTAPI.APIUserGroup;
		this.company_api = TTAPI.APICompany;
		this.user_api = TTAPI.APIUser;
		this.currency_api = TTAPI.APICurrency;
		this.render();

		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
		} else {
			this.buildContextMenu();
		}

		//call init data in parent view
		if ( !this.sub_view_mode ) {
			this.initData();
		}

		this.setSelectRibbonMenuIfNecessary();
	}

	initOptions() {
		var $this = this;

		this.initDropDownOption( 'type' );
		this.initDropDownOption( 'status', '', this.user_api );
		this.initDropDownOption( 'country', 'country', this.company_api );

		this.user_group_api.getUserGroup( '', false, false, {
			onResult: function( res ) {
				res = res.getResult();

				res = Global.buildTreeRecord( res );
				$this.user_group_array = res;

				if ( !$this.sub_view_mode ) {
					$this.adv_search_field_ui_dic['group_id'].setSourceData( res );
				}

			}
		} );
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: [ContextMenuIconName.copy],
			include: [{
				label: $.i18n._( 'Import' ),
				id: ContextMenuIconName.import_icon,
				group: 'other',
				icon: Icons.import_icon,
				permission_result: PermissionManager.checkTopLevelPermission( 'ImportCSVWage' ),
				permission: null,
				sort_order: 8000
			}]
		};

		return context_menu_model;
	}

	onFormItemChange( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		this.current_edit_record[key] = c_value;

		switch ( key ) {
			case 'type_id':
				this.onTypeChange( true );
				break;
			case 'user_id':
				if ( $.isArray( this.current_edit_record.user_id ) && this.current_edit_record.user_id.length > 1 ) {
					this.is_mass_adding = true;
				} else {
					this.is_mass_adding = false;
				}
				this.setCurrency();
				break;
			case 'wage':
			case 'weekly_time':
				this.getHourlyRate();
				break;
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	/* jshint ignore:start */
	setDefaultMenu( doNotSetFocus ) {

		//Error: Uncaught TypeError: Cannot read property 'length' of undefined in /interface/html5/#!m=Employee&a=edit&id=42411&tab=Wage line 282
		if ( !this.context_menu_array ) {
			return;
		}

		if ( !Global.isSet( doNotSetFocus ) || !doNotSetFocus ) {
			this.selectContextMenu();
		}

		this.setTotalDisplaySpan();

		var len = this.context_menu_array.length;

		var grid_selected_id_array = this.getGridSelectIdArray();

		var grid_selected_length = grid_selected_id_array.length;

		for ( var i = 0; i < len; i++ ) {
			var context_btn = $( this.context_menu_array[i] );
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			context_btn.removeClass( 'invisible-image' );
			context_btn.removeClass( 'disable-image' );

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setDefaultMenuAddIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.edit:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.view:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.mass_edit:
					this.setDefaultMenuMassEditIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.copy:
					this.setDefaultMenuCopyIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.delete_icon:
					this.setDefaultMenuDeleteIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setDefaultMenuDeleteAndNextIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save:
					this.setDefaultMenuSaveIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_next:
					this.setDefaultMenuSaveAndNextIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_continue:
					this.setDefaultMenuSaveAndContinueIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_new:
					this.setDefaultMenuSaveAndAddIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.save_and_copy:
					this.setDefaultMenuSaveAndCopyIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.copy_as_new:
					this.setDefaultMenuCopyAsNewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.login:
					this.setDefaultMenuLoginIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.cancel:
					this.setDefaultMenuCancelIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.import_icon:
					this.setDefaultMenuImportIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn, grid_selected_length );
					break;
			}

		}

		this.setContextMenuGroupVisibility();
	}

	/* jshint ignore:end */

	//Make sure this.current_edit_record is updated before validate
	validate() {
		var $this = this;
		var record = {};
		LocalCacheData.current_doing_context_action = 'validate';
		if ( this.is_mass_editing ) {
			for ( var key in this.edit_view_ui_dic ) {
				var widget = this.edit_view_ui_dic[key];

				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() && widget.getEnabled() ) {
						record[key] = widget.getValue();
					}
				}
			}
		} else {
			if ( Global.isArray( this.current_edit_record.user_id ) && this.current_edit_record.user_id.length > 0 ) {
				record = [];
				$.each( this.current_edit_record.user_id, function( index, value ) {

					var commonRecord = Global.clone( $this.current_edit_record );
					commonRecord.user_id = value;
					record.push( commonRecord );

				} );
			} else {
				record = this.current_edit_record;
			}
		}
		this.api['validate' + this.api.key_name]( record, {
			onResult: function( result ) {
				$this.validateResult( result );
			}
		} );
	}

	setDefaultMenuImportIcon( context_btn, grid_selected_length, pId ) {
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

		IndexViewController.openWizard( 'ImportCSVWizard', 'UserWage', function() {
			$this.search();
		} );
	}

	doSaveAPICall( record, ignoreWarning, callback ) {
		// #2644: We have to handle the record as though its a mass_add, as the awesomebox will always return an array of user_id's. See 733f709e287626070b84e39ca67f78e69a3fcee6.
		// Normal handling results in 'Invalid Employee' errors for single records. Cannot force is_mass_adding flag, as this affects the save&continue button disabling.
		record = this.buildMassAddRecord( record );
		super.doSaveAPICall( record, ignoreWarning, callback );
	}

	setEditMenuSaveAndContinueIcon( context_btn, pId ) {
		this.saveAndContinueValidate( context_btn );

		if ( this.is_mass_adding || !this.current_edit_record ) {
			context_btn.addClass( 'disable-image' );
		}
	}

	setEditMenuSaveAndAddIcon( context_btn, pId ) {
		this.saveAndNewValidate( context_btn );

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}
	}

	setEditMenuSaveAndCopyIcon( context_btn, pId ) {
		this.saveAndContinueValidate( context_btn );

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			context_btn.addClass( 'disable-image' );
		}
	}

	setEditViewData() {
		var $this = this;
		super.setEditViewData(); //Set Navigation

		this.setCurrency();

		if ( !this.sub_view_mode ) {
			var widget = $this.edit_view_ui_dic['user_id'];
			if ( widget ) {
				if ( ( !this.current_edit_record || !this.current_edit_record.id ) && !this.is_mass_editing ) {
					widget.setAllowMultipleSelection( true );
				} else {
					widget.setAllowMultipleSelection( false );
				}
			}
		}

		$this.onTypeChange( false );
	}

	setCurrency() {

		var $this = this;
		if ( Global.isSet( this.current_edit_record.user_id ) ) {
			var filter = {};
			filter.filter_data = { user_id: this.current_edit_record.user_id };

			this.currency_api.getCurrency( filter, false, false, {
				onResult: function( res ) {
					res = res.getResult();
					if ( Global.isArray( res ) ) {
						$this.currency.text( res[0].symbol );
						$this.code.text( res[0].iso_code );
					} else {
						$this.currency.text( '' );
						$this.code.text( '' );
					}

				}
			} );
		}
	}

	getHourlyRate() {

		var $this = this;
		if ( this.current_edit_record.wage &&
			this.current_edit_record.weekly_time &&
			this.current_edit_record.type_id &&
			this.current_edit_record.type_id != 10 ) {

			//wwkly_time need value before pasrse to seconds.
			this.api.getHourlyRate( this.current_edit_record.wage, $this.edit_view_ui_dic['weekly_time'].getInputValue(), this.current_edit_record.type_id, {
				onResult: function( result ) {
					var res = result.getResult();

					if ( result.isValid() ) {
						$this.current_edit_record.hourly_rate = res;
						$this.edit_view_ui_dic['hourly_rate'].val( res );
					}

				}
			} );
		}
	}

	onTypeChange( getRate ) {

		if ( parseInt( this.current_edit_record.type_id ) != 10 ) {
			this.attachElement( 'weekly_time' );
			this.attachElement( 'hourly_rate' );

			if ( getRate ) {
				this.getHourlyRate();
			}

		} else {
			this.detachElement( 'weekly_time' );
			this.detachElement( 'hourly_rate' );
		}
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_wage': { 'label': $.i18n._( 'Wage' ) },
			'tab_attachment': true,
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIUserWage,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.WAGE,
			show_search_inputs: true,
			navigation_mode: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_wage = this.edit_view_tab.find( '#tab_wage' );

		var tab_wage_column1 = tab_wage.find( '.first-column' );

		var form_item_input;
		var widgetContainer;
		var label;

		if ( !this.sub_view_mode ) {
			//Employee

			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: TTAPI.APIUser,
				allow_multiple_selection: false,
				layout_name: ALayoutIDs.USER,
				show_search_inputs: true,
				set_empty: true,
				field: 'user_id'

			} );

			var default_args = {};
			default_args.permission_section = 'wage';
			form_item_input.setDefaultArgs( default_args );
			this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_wage_column1, '' );
		}

		//Wage Group

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIWageGroup,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.WAGE_GROUP,
			show_search_inputs: true,
			set_default: true,
			field: 'wage_group_id'
		} );

		if ( this.sub_view_mode ) {
			this.addEditFieldToColumn( $.i18n._( 'Wage Group' ), form_item_input, tab_wage_column1, '' );
		} else {
			this.addEditFieldToColumn( $.i18n._( 'Wage Group' ), form_item_input, tab_wage_column1 );
		}

		//Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'type_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.type_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_wage_column1 );

		//Wage

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'wage', width: 90 } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		this.currency = $( '<span class=\'widget-left-label\'></span>' );
		this.code = $( '<span class=\'widget-right-label\'></span>' );

		widgetContainer.append( this.currency );
		widgetContainer.append( form_item_input );
		widgetContainer.append( this.code );

		this.addEditFieldToColumn( $.i18n._( 'Wage' ), form_item_input, tab_wage_column1, '', widgetContainer );

		//Average Time / Week

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'weekly_time', need_parser_sec: true } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>( ' + $.i18n._( 'ie' ) + ': ' + $.i18n._( '40 hours / week' ) + ' )</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Average Time / Week' ), form_item_input, tab_wage_column1, '', widgetContainer, true );

		//Annual Hourly Rate

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'hourly_rate' } );

		this.addEditFieldToColumn( $.i18n._( 'Annual Hourly Rate' ), form_item_input, tab_wage_column1, '', null, true );

		//Labor Burden Percent

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'labor_burden_percent', width: 50 } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>% ( ' + $.i18n._( 'ie' ) + ': ' + $.i18n._( '25% burden' ) + ' )</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Labor Burden Percent' ), form_item_input, tab_wage_column1, '', widgetContainer );

		//Effective Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'effective_date', width: 120 } );
		this.addEditFieldToColumn( $.i18n._( 'Effective Date' ), form_item_input, tab_wage_column1 );

		//Note
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'note', width: 389, height: 117 } );
		this.addEditFieldToColumn( $.i18n._( 'Note' ), form_item_input, tab_wage_column1, '', null, null, true );
	}

	//Override for: Do not show first 2 columns in sub wage view
	setSelectLayout() {
		if ( this.sub_view_mode ) {
			super.setSelectLayout( ['first_name', 'last_name'] );
		} else {
			super.setSelectLayout();
		}
	}

	setProvince( val, m ) {
		var $this = this;

		if ( !val || val === '-1' || val === '0' ) {
			$this.province_array = [];
			this.adv_search_field_ui_dic['province'].setSourceData( [] );
		} else {
			this.company_api.getOptions( 'province', val, {
				onResult: function( res ) {
					res = res.getResult();
					if ( !res ) {
						res = [];
					}

					$this.province_array = Global.buildRecordArray( res );
					$this.adv_search_field_ui_dic['province'].setSourceData( $this.province_array );

				}
			} );
		}
	}

	eSetProvince( val ) {
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
					province_widget.setSourceData( $this.e_province_array );

				}
			} );
		}
	}

	onSetSearchFilterFinished() {

		if ( this.search_panel.getSelectTabIndex() === 1 ) {
			var combo = this.adv_search_field_ui_dic['country'];
			var select_value = combo.getValue();
			this.setProvince( select_value );
		}
	}

	onBuildBasicUIFinished() {
		var basicSearchTabPanel = this.search_panel.find( 'div #basic_search' );
	}

	onBuildAdvUIFinished() {

		this.adv_search_field_ui_dic['country'].change( $.proxy( function() {
			var combo = this.adv_search_field_ui_dic['country'];
			var selectVal = combo.getValue();

			this.setProvince( selectVal );

			this.adv_search_field_ui_dic['province'].setValue( null );

		}, this ) );
	}

	buildSearchFields() {

		super.buildSearchFields();

		var default_args = { permission_section: 'wage' };
		this.search_fields = [
			new SearchField( {
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				layout_name: ALayoutIDs.USER,
				default_args: default_args,
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Wage Group' ),
				in_column: 1,
				field: 'wage_group_id',
				layout_name: ALayoutIDs.WAGE_GROUP,
				api_class: TTAPI.APIWageGroup,
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: false,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Group' ),
				in_column: 1,
				multiple: true,
				field: 'group_id',
				layout_name: ALayoutIDs.TREE_COLUMN,
				tree_mode: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: true,
				script_name: 'EmployeeView',
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
				adv_search: true,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Default Branch' ),
				in_column: 2,
				field: 'default_branch_id',
				layout_name: ALayoutIDs.BRANCH,
				api_class: TTAPI.APIBranch,
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Default Department' ),
				field: 'default_department_id',
				in_column: 2,
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: TTAPI.APIDepartment,
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Title' ),
				field: 'title_id',
				in_column: 2,
				layout_name: ALayoutIDs.JOB_TITLE,
				api_class: TTAPI.APIUserTitle,
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Country' ),
				in_column: 3,
				field: 'country',
				multiple: true,
				basic_search: false,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.COMBO_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Province/State' ),
				in_column: 3,
				field: 'province',
				multiple: true,
				basic_search: false,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} )

		];
	}

	cleanWhenUnloadView( callBack ) {

		$( '#wage_view_container' ).remove();
		super.cleanWhenUnloadView( callBack );
	}

	searchDone() {
		TTPromise.resolve( 'WageView', 'init' );
		super.searchDone();
	}

}

WageViewController.loadView = function( container ) {
	Global.loadViewSource( 'Wage', 'WageView.html', function( result ) {
		var args = {};
		var template = _.template( result );
		if ( Global.isSet( container ) ) {
			container.html( template( args ) );
		} else {
			Global.contentContainer().html( template( args ) );
		}
	} );
};

WageViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {
	Global.loadViewSource( 'Wage', 'SubWageView.html', function( result ) {
		var args = {};
		var template = _.template( result );
		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}

		if ( Global.isSet( container ) ) {
			container.html( template( args ) );

			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_wage_view_controller );
			}
		}
	} );
};
