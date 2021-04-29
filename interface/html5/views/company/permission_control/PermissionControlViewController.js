class PermissionControlViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#permission_control_view_container',



			level_array: null,
			user_api: null,
			permission_array: null,

			quick_search_dic: {},

			//Save multi key typed when quick search
			quick_search_typed_keys: '',

			//use to juedge if need to clear quick_search_this.quick_search_typed_keyss
			quick_search_timer: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'PermissionControlEditView.html';
		this.permission_id = 'permission';
		this.viewId = 'PermissionControl';
		this.script_name = 'PermissionControlView';
		this.table_name_key = 'permission_control';
		this.context_menu_name = $.i18n._( 'Permission Group' );
		this.navigation_label = $.i18n._( 'Permission Group' ) + ':';
		this.api = TTAPI.APIPermissionControl;
		this.user_api = TTAPI.APIUser;

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary();
	}

	onKeyDown( e ) {
		var focus = $( ':focus' );
		var $this = this;

		if ( this.edit_view_tab_selected_index === 0 && !LocalCacheData.openAwesomeBox &&
			( focus.length < 1 || focus[0].localName !== 'input' ) ) {
			var a_dropdown = this.edit_view_ui_dic.permission;

			if ( e.keyCode === 39 ) { //right

				e.preventDefault();

				if ( a_dropdown.getAllowMultipleSelection() ) {

					a_dropdown.onUnSelectGridDoubleClick();
				}
			} else if ( e.keyCode === 37 ) { //left

				e.preventDefault();

				if ( a_dropdown.getAllowMultipleSelection() ) {

					a_dropdown.onSelectGridDoubleClick();
				}
			} else {

				if ( e.keyCode === 16 ||
					e.keyCode === 17 ||
					e.keyCode === 91 ) {
					return;
				}

				if ( this.quick_search_timer ) {
					clearTimeout( this.quick_search_timer );
				}

				this.quick_search_timer = setTimeout( function() {
					$this.quick_search_typed_keys = '';
				}, 200 );

				e.preventDefault();
				var target_grid;
				var next_index;
				var next_select_item;
				this.quick_search_typed_keys = this.quick_search_typed_keys + Global.KEYCODES[e.which];

				if ( a_dropdown.getAllowMultipleSelection() || a_dropdown.getTreeMode() ) {
					if ( this.quick_search_typed_keys ) {
						target_grid = a_dropdown.getFocusInSeletGrid() ? a_dropdown.getSelectGrid() : a_dropdown.getUnSelectGrid();
						var search_index = this.quick_search_dic[this.quick_search_typed_keys] ? this.quick_search_dic[this.quick_search_typed_keys] : 0;
						var tds = $( target_grid.grid.find( 'tr' ).find( 'td:eq(1)' ).filter( function() {
							return $.text( [this] ).toLowerCase().indexOf( $this.quick_search_typed_keys ) == 0;
						} ) );

						var td;
						if ( search_index > 0 && search_index < tds.length ) {

						} else {
							search_index = 0;
						}

						td = $( tds[search_index] );

						a_dropdown.unSelectAll( target_grid, true );

						next_index = td.parent().index() - 1;
						next_select_item = target_grid.grid.jqGrid( 'getGridParam', 'data' )[next_index];
						a_dropdown.setSelectItem( next_select_item, target_grid );
						this.quick_search_dic = {};
						this.quick_search_dic[this.quick_search_typed_keys] = search_index + 1;
					}

				} else {
					if ( this.quick_search_typed_keys ) {
						search_index = this.quick_search_dic[this.quick_search_typed_keys] ? this.quick_search_dic[this.quick_search_typed_keys] : 0;
						tds = $( a_dropdown.getUnSelectGrid().find( 'tr' ).find( 'td:first' ).filter( function() {
							return $.text( [this] ).toLowerCase().indexOf( $this.quick_search_typed_keys ) == 0;
						} ) );
						if ( search_index > 0 && search_index < tds.length ) {

						} else {
							search_index = 0;
						}

						td = $( tds[search_index] );

						next_index = td.parent().index() - 1;
						next_select_item = this.getItemByIndex( next_index );
						a_dropdown.setSelectItem( next_select_item );

						this.quick_search_dic = {};
						this.quick_search_dic[this.quick_search_typed_keys] = search_index + 1;
					}
				}
			}

		}
	}

	setSubLogViewFilter() {
		if ( !this.sub_log_view_controller ) {
			return false;
		}

		this.sub_log_view_controller.getSubViewFilter = function( filter ) {
			filter['table_name_object_id'] = {
				'permission_user': [this.parent_edit_record.id],
				'permission': [this.parent_edit_record.id],
				'permission_control': [this.parent_edit_record.id]
			};

			return filter;
		};

		return true;
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: [ContextMenuIconName.mass_edit],
			include: [{
				label: $.i18n._( 'Permission<br>Wizard' ),
				id: ContextMenuIconName.permission_wizard,
				group: 'other',
				icon: Icons.permission_wizard
			}]
		};

		return context_menu_model;
	}

	setEditViewTabHeight() {
		super.setEditViewTabHeight();

		var permission_grid = this.edit_view_ui_dic.permission;

		permission_grid.setHeight( ( this.edit_view_tab.height() - 325 ) );
	}

	onCustomContextClick( context_menu_id ) {

		var $this = this;

		$this.current_edit_record.permission = this.buildAPIFormPermissionResult();

		if ( $.type( $this.current_edit_record.permission ) !== 'array' ) {
			$this.current_edit_record.permission = [];
		}

		switch ( context_menu_id ) {
			case ContextMenuIconName.permission_wizard:
				IndexViewController.openWizard( 'PermissionWizard', null, function( result, action ) {
					if ( result ) {

						switch ( action ) {
							case 'allow':
								var new_permission_array = $this.convertPermissionData( result );
								$this.current_edit_record.permission = $this.current_edit_record.permission.concat( new_permission_array );
								$this.removeDuplicatePermission();
								$this.edit_view_ui_dic.permission.setValue( $this.current_edit_record.permission );
								$this.edit_view_ui_dic.permission.setSelectGridHighlight( new_permission_array );
								break;
							case 'highlight':
								new_permission_array = $this.convertPermissionData( result );
								$this.edit_view_ui_dic.permission.setSelectGridHighlight( new_permission_array );
								$this.edit_view_ui_dic.permission.setUnSelectGridHighlight( new_permission_array );

								break;
							case 'deny':
								new_permission_array = $this.convertPermissionData( result );
								$this.edit_view_ui_dic.permission.setSelectGridHighlight( new_permission_array );
								$this.edit_view_ui_dic.permission.moveItems( false, new_permission_array );

								$this.edit_view_ui_dic.permission.setUnSelectGridHighlight( new_permission_array );

								$this.current_edit_record.permission = $this.edit_view_ui_dic.permission.getValue();

								break;
						}

					}

				} );
		}
	}

	removeDuplicatePermission() {
		var new_array = [];
		$.each( this.current_edit_record.permission, function( i, el ) {
			if ( $.inArray( el, new_array ) === -1 ) {
				new_array.push( el );
			}
		} );

		this.current_edit_record.permission = new_array;
	}

	buildPermissionArray( result, valueOnly ) {

		var arr = [];
		var val_array = [];
		var id = 1000;
		for ( var key in result ) {
			var sArr = [];
			for ( var cKey in result[key] ) {
				var item = result[key];
				var resItem = {};
				resItem.value = key + '->' + cKey;
				val_array.push( resItem.value );
				resItem.sortKey = key;
				resItem.label = item[cKey];
				resItem.id = resItem.value;
				sArr.push( resItem );
				id++;
			}

			arr = arr.concat( sArr );
		}

		arr.sort( function( a, b ) {
			return Global.compare( a, b, 'label' );
		} );

		if ( !valueOnly ) {
			return arr;
		} else {
			return val_array;
		}
	}

	initOptions() {
		var $this = this;

		this.initDropDownOption( 'level', 'level' );
		this.api.getPermissionOptions( {
			onResult: function( res ) {
				res = res.getResult();
				res = $this.buildPermissionArray( res );
				$this.permission_array = res;

			}
		} );
	}

	setCurrentEditRecordData() {
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'permission':
						if ( this.current_edit_record.permission ) {
							this.current_edit_record.permission = this.convertPermissionData( this.current_edit_record.permission );
							widget.setValue( this.current_edit_record.permission );
						}
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}
		this.collectUIDataToCurrentEditRecord();
		this.edit_view_ui_dic.permission.setGridColumnsWidths();
		this.setEditViewDataDone();
	}

	convertPermissionData( permission ) {

		var result = [];
		for ( var key in permission ) {
			var ar = [];
			for ( var cKey in permission[key] ) {
				if ( permission[key][cKey] === true ) {
					ar.push( key + '->' + cKey );
				}
			}
			result = result.concat( ar );
		}

		return result;
	}

	buildSelectItems() {

		var items = [];
		var len = this.permission_array.length;

		for ( var key in this.current_edit_record.permission ) {
			var select_value = this.current_edit_record.permission[key];
			for ( var i = 0; i < len; i++ ) {
				var item = this.permission_array[i];
				if ( select_value === item.value ) {
					items.push( item );
					break;
				}
			}
		}

		return items;
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		this.edit_view.children().eq( 0 ).css( 'min-width', 1170 );

		var tab_model = {
			'tab_permission_group': { 'label': $.i18n._( 'Permission Group' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIPermissionControl,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.PERMISSION_CONTROL,
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_permission_group = this.edit_view_tab.find( '#tab_permission_group' );

		var tab_permission_group_column1 = tab_permission_group.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_permission_group_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_permission_group_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_permission_group_column1 );

		form_item_input.parent().width( '45%' );

		// Level
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'level' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.level_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Level' ), form_item_input, tab_permission_group_column1 );

		//Employee

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'user'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Employees' ), form_item_input, tab_permission_group_column1, '' );

		//Permissions

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_DROPDOWN );

		var display_columns = ALayoutCache.getDefaultColumn( ALayoutIDs.OPTION_COLUMN ); //Get Default columns base on different layout name
		display_columns = Global.convertColumnsTojGridFormat( display_columns, ALayoutIDs.OPTION_COLUMN ); //Convert to jQgrid format

		form_item_input.ADropDown( {
			field: 'permission',
			display_show_all: false,
			id: 'permission_dropdown',
			key: 'value',
			allow_drag_to_order: false,
			display_close_btn: false,
			auto_sort: true,
			display_column_settings: false,
			default_height: ( this.edit_view_tab.height() - 325 ),
		} );
		form_item_input.addClass( 'splayed-adropdown' );
		this.addEditFieldToColumn( $.i18n._( 'Permissions' ), form_item_input, tab_permission_group_column1, '', null, true, true );

		form_item_input.setColumns( display_columns );
		form_item_input.setUnselectedGridData( this.permission_array );
	}

	uniformVariable( records ) {

		records.permission = this.buildAPIFormPermissionResult();
		return records;
	}

	buildAPIFormPermissionResult() {

		var val = this.edit_view_ui_dic.permission.getValue();
		var permission = {};

		var key = '';
		for ( var i = 0; i < val.length; i++ ) {
			var item = val[i].value;
			key = item.split( '->' )[0];

			if ( !permission[key] ) {
				permission[key] = {};
			}

			permission[key][item.split( '->' )[1]] = true;

		}

		return permission;
	}

	buildSearchFields() {

		super.buildSearchFields();
		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Description' ),
				in_column: 1,
				field: 'description',
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

}
