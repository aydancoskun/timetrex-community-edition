BaseTreeViewController = BaseViewController.extend( {
	setSelectLayout: function( column_start_from ) {
		var $this = this;

		var grid;
		if ( !Global.isSet( this.grid ) ) {
			grid = $( this.el ).find( '#grid' );

			grid.attr( 'id', this.ui_id + '_grid' );  //Grid's id is ScriptName + _grid

			grid = $( this.el ).find( '#' + this.ui_id + '_grid' );
		}

		var column_info_array = [];

		if ( !this.select_layout ) { //Set to defalt layout if no layout at all
			this.select_layout = { id: '' };
			this.select_layout.data = { filter_data: {}, filter_sort: {} };
			this.select_layout.data.display_columns = this.default_display_columns;
		}
		var layout_data = this.select_layout.data;

		if ( layout_data.display_columns.length < 1 ) {
			layout_data.display_columns = this.default_display_columns;
		}

		var display_columns = this.buildDisplayColumns( layout_data.display_columns );
		//Set Data Grid on List view
		var len = display_columns.length;

		var start_from = 0;

		if ( Global.isSet( column_start_from ) && column_start_from > 0 ) {
			start_from = column_start_from;
		}

		var view_column_data = display_columns[0];

		var column_info = {
			name: view_column_data.value,
			index: view_column_data.value,
			label: $this.grid_table_name,
			width: 100,
			sortable: false,
			title: false
		};

		column_info_array.push( column_info );

		if ( this.grid ) {
			this.grid.jqGrid( 'GridUnload' );
			this.grid = null;
		}


		this.grid = new TTGrid( this.ui_id + '_grid', {
			multiselect: false,
			winMultiSelect: false,
			tree_mode: true,
			onSelectRow: $.proxy( this.onGridSelectRow, this )
		}, column_info_array );

		this.bindGridColumnEvents();

		this.setGridHeaderStyle(); //Set Sort Style

		this.filter_data = this.select_layout.data.filter_data;

		this.showGridBorders();

		$this.setGridSize();
	},

	search: function( set_default_menu, page_action, page_number ) {
		var $this = this;

		if ( !Global.isSet( set_default_menu ) ) {
			set_default_menu = true;
		}

		var filter = {};
		filter.filter_data = {};
		filter.filter_sort = {};
		filter.filter_columns = this.getFilterColumnsFromDisplayColumns();
		filter.filter_items_per_page = 0; // Default to 0 to load user preference defined

		if ( this.sub_view_mode && this.parent_key ) {
			this.select_layout.data.filter_data[this.parent_key] = this.parent_value;

			//If sub view controller set custom filters, get it
			if ( Global.isSet( this.getSubViewFilter ) ) {

				this.select_layout.data.filter_data = this.getSubViewFilter( this.select_layout.data.filter_data );

			}
		}

		this.last_select_ids = this.getGridSelectIdArray();
		//select_layout will not be null, it's set in setSelectLayout function
		filter.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
		filter.filter_sort = this.select_layout.data.filter_sort;

		this.api['get' + this.api.key_name]( filter, false, false, {
			onResult: function( result ) {

				var result_data = result.getResult();

				result_data = Global.buildTreeRecord( result_data );

				$this.grid_current_page_items = result_data; // For tree mode only

				if ( !Global.isArray( result_data ) ) {
					$this.showNoResultCover();
				} else {
					$this.removeNoResultCover();
				}

				$this.reSetGridTreeData( result_data );

				$this.setGridSize();

				ProgressBar.closeOverlay(); //Add this in initData
				if ( set_default_menu ) {
					$this.setDefaultMenu();
				}

				if ( LocalCacheData.paging_type === 0 ) {
					if ( !$this.pager_data || $this.pager_data.is_last_page ) {
						$this.paging_widget.css( 'display', 'none' );
					} else {
						$this.paging_widget.css( 'display', 'block' );
					}
				}

				$this.reSelectLastSelectItems();
				$this.autoOpenEditViewIfNecessary();
				$this.searchDone();
			}
		} );
	},

	reSetGridTreeData: function( val ) {
		var $this = this;

		var col_model = this.grid.getGridParam( 'colModel' );
		this.grid.grid.jqGrid( 'GridUnload' );
		this.grid = null;

		this.grid = new TTGrid( this.ui_id + '_grid', {
			multiselect: false,
			winMultiSelect: false,
			datastr: val,
			datatype: 'jsonstring',
			sortable: false,
			onSelectRow: function( id ) {
				$( '#ribbon_view_container .context-menu:visible a' ).click();
				$this.grid_select_id_array = [id];
				$this.setDefaultMenu();

			},
			ondblClickRow: function() {
				$this.onGridDblClickRow();
			},
			gridview: true,
			treeGrid: true,
			treeGridModel: 'adjacency',
			treedatatype: 'local',
			ExpandColumn: 'name',
		}, col_model );
	},

	getGridSelectIdArray: function() {
		var result = [];
		result = this.grid_select_id_array;

		return result;
	},

	initLayout: function() {
		var $this = this;

		$this.getDefaultDisplayColumns( function() {
			$this.setSelectLayout();
			$this.search();
		} );
	},

	getAllColumns: function( callBack ) {
		var $this = this;

		this.api.getOptions( 'columns', {
			onResult: function( columns_result ) {

				var columns_result_data = columns_result.getResult();
				$this.all_columns = Global.buildColumnArray( columns_result_data );

				if ( callBack ) {
					callBack();
				}

			}
		} );
	},

	setCurrentEditRecordData: function() {
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'parent_id':
						widget.setSourceData( Global.addFirstItemToArray( this.grid_current_page_items ) );
						widget.setValue( this.current_edit_record[key] );
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}
		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();
	},

	setEditViewData: function() {
		var $this = this;

		this.is_changed = false;

		if ( !this.edit_only_mode ) {

			var navigation_div = this.edit_view.find( '.navigation-div' );

			if ( Global.isSet( this.current_edit_record.id ) && this.current_edit_record.id ) {
				navigation_div.css( 'display', 'block' );
				//Set Navigation Awesomebox

				//init navigation only when open edit view
				if ( !this.navigation.getSourceData() ) {
					this.navigation.setSourceData( Global.addFirstItemToArray( this.grid_current_page_items ) );

					var default_args = {};
					default_args.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
					default_args.filter_sort = this.select_layout.data.filter_sort;
					this.navigation.setDefaultArgs( default_args );
				}

				this.navigation.setValue( this.current_edit_record );

			} else {
				navigation_div.css( 'display', 'none' );
			}
		}

		for ( var key in this.edit_view_ui_dic ) {

			//Set all UI field to current edit reocrd, we need validate all UI fielld when save and validate
			if ( !Global.isSet( $this.current_edit_record[key] ) && !this.is_mass_editing ) {
				$this.current_edit_record[key] = false;
			}

		}

		this.setNavigationArrowsStatus();

		// Create this function alone because of the column value of view is different from each other, some columns need to be handle specially. and easily to rewrite this function in sub-class.

		this.setCurrentEditRecordData();

		//Init *Please save this record before modifying any related data* box
		this.edit_view.find( '.save-and-continue-div' ).SaveAndContinueBox( { related_view_controller: this } );
		this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'none' );

		if ( this.edit_view_tab.tabs( 'option', 'active' ) === 1 ) {
			if ( this.current_edit_record.id ) {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'block' );
				this.initSubLogView( 'tab_audit' );
			} else {
				this.edit_view_tab.find( '#tab_audit' ).find( '.first-column-sub-view' ).css( 'display', 'none' );
				this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'block' );
			}
		}
		this.switchToProperTab();
	},

	addIdFieldToNavigation: function( array ) {
		$.each( array, function( key, item ) {
			$( item ).each( function( i_key, i_item ) {
				i_item.id = i_item._id_;
			} );
		} );

		return array;
	},

	onViewClick: function( editId, noRefreshUI ) {
		var $this = this;

		$this.is_viewing = true;
		LocalCacheData.current_doing_context_action = 'view';
		$this.openEditView();

		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		var selectedId;

		if ( Global.isSet( editId ) ) {
			selectedId = editId;
		} else {
			if ( grid_selected_length > 0 ) {
				selectedId = grid_selected_id_array[0];
			} else {
				return;
			}
		}

		filter.filter_data = {};

		this.api['get' + this.api.key_name]( filter, false, false, {
			onResult: function( result ) {
				var result_data = result.getResult();

				result_data = Global.buildTreeRecord( result_data );

				result_data = Global.getParentIdByTreeRecord( result_data, selectedId );

				if ( !result_data ) {
					result_data = [];
				}

				result_data = result_data[0];

				if ( !result_data ) {
					TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
					$this.onCancelClick();
					return;
				}

				$this.current_edit_record = result_data;
				$this.current_edit_record.id = selectedId;

				$this.initEditView();
			}
		} );
	},

	onDeleteDone: function( result ) {
		this.grid_select_id_array = [];
		this.setDefaultMenu();
		this.removeDeletedRows();
	},

	onSaveDone: function( result ) {
		this.grid_select_id_array = [];
	},

	onEditClick: function( editId, noRefreshUI ) {
		var $this = this;

		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		var selectedId;

		if ( Global.isSet( editId ) ) {
			selectedId = editId;
		} else {

			if ( this.is_viewing ) {
				selectedId = this.current_edit_record.id;
			} else if ( grid_selected_length > 0 ) {
				selectedId = grid_selected_id_array[0];
			} else {
				return;
			}
		}

		this.is_viewing = false;
		LocalCacheData.current_doing_context_action = 'edit';
		$this.openEditView();

		var filter = {};

		filter.filter_data = {};

		this.api['get' + this.api.key_name]( filter, false, false, {
			onResult: function( result ) {
				var result_data = result.getResult();

				result_data = Global.buildTreeRecord( result_data );

				result_data = Global.getParentIdByTreeRecord( result_data, selectedId );

				if ( !result_data ) {
					result_data = [];
				}

				result_data = result_data[0];

				if ( !result_data ) {
					TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
					$this.onCancelClick();
					return;
				}

				if ( $this.sub_view_mode && $this.parent_key ) {
					result_data[$this.parent_key] = $this.parent_value;
				}

				$this.current_edit_record = result_data;
				$this.current_edit_record.id = selectedId;

				$this.initEditView();

			}
		} );
	},

	_continueDoCopyAsNew: function() {
		var $this = this;

		LocalCacheData.current_doing_context_action = 'copy_as_new';

		if ( Global.isSet( this.edit_view ) ) {

			this.current_edit_record.id = '';
			var navigation_div = this.edit_view.find( '.navigation-div' );
			navigation_div.css( 'display', 'none' );
			this.setEditMenu();
			this.setTabStatus();
			this.is_changed = false;
			ProgressBar.closeOverlay();

		} else {
			var filter = {};
			var grid_selected_id_array = this.getGridSelectIdArray();
			var grid_selected_length = grid_selected_id_array.length;

			if ( grid_selected_length > 0 ) {
				var selectedId = grid_selected_id_array[0];
			} else {
				TAlertManager.showAlert( $.i18n._( 'No selected record' ) );
				return;
			}

			filter.filter_data = {};

			this.api['get' + this.api.key_name]( filter, false, false, {
				onResult: function( result ) {
					var result_data = result.getResult();
					result_data = Global.buildTreeRecord( result_data );
					result_data = Global.getParentIdByTreeRecord( result_data, selectedId );

					if ( !result_data ) {
						TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
						$this.onCancelClick();
						return;
					}

					$this.openEditView(); // Put it here is to avoid if the selected one is not existed in data or have deleted by other pragram. in this case, the edit view should not be opend.

					result_data = result_data[0];
					if ( $this.sub_view_mode && $this.parent_key ) {
						result_data[$this.parent_key] = $this.parent_value;
					}

					$this.current_edit_record = result_data;
					$this.current_edit_record.id = '';

					$this.initEditView();

				}
			} );
		}
	},

	buildEditViewUI: function() {
		var $this = this;

		//No navigation when edit only mode
		if ( !this.edit_only_mode ) {
			var navigation_div = this.edit_view.find( '.navigation-div' );
			var label = navigation_div.find( '.navigation-label' );

			var navigation_widget_div = navigation_div.find( '.navigation-widget-div' );

			this.navigation = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			label.text( this.navigation_label );

			navigation_widget_div.append( this.navigation );
		}

		this.edit_view_close_icon = this.edit_view.find( '.close-icon' );
		this.edit_view_close_icon.hide();
		this.edit_view_close_icon.click( function() {
			$this.onCloseIconClick();
		} );

		var tab_model = Array();
		tab_model[this.primary_tab_key] = { 'label': this.primary_tab_label },
		tab_model['tab_audit'] = {
				'label': $.i18n._( 'Audit' ),
				'init_callback': 'initSubLogView',
				'display_on_mass_edit': false,
				'display_on_add': false
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			id: this.script_name + '_navigation',
			tree_mode: true,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.TREE_COLUMN,
			navigation_mode: true,
			show_search_inputs: false,
			on_tree_grid_row_select: function( id, tree_mode_collapse ) {
				$this.onTreeGridNavigationRowSelect( id, tree_mode_collapse );
			}
		} );

		var left_click = navigation_div.find( '.left-click' );
		var right_click = navigation_div.find( '.right-click' );
		left_click.attr( 'src', Global.getRealImagePath( 'images/left_arrow.png' ) );
		right_click.attr( 'src', Global.getRealImagePath( 'images/right_arrow.png' ) );

		this.setNavigation();

		//Tab 0 start
		var tab_group = this.edit_view_tab.find( '#'+ this.primary_tab_key );

		var tab_group_column1 = tab_group.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_group_column1 );

		//Parent
		//Group
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			tree_mode: true,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.TREE_COLUMN,
			set_empty: true,
			field: 'parent_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Parent' ), form_item_input, tab_group_column1, '' );

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_group_column1, '' );

		form_item_input.parent().width( '45%' );
	}
} );
