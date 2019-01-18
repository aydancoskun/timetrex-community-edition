(function( $ ) {

	$.fn.ADropDown = function( options ) {
		var opts = $.extend( {}, $.fn.ADropDown.defaults, options );

		var unselect_grid = null;

		var select_grid = null;

		var total_display_span = null;

		var static_source_data = null; //Always use this to help to set Select data

		var id = null;

		var key = 'id';

		var parent_a_combo_box = null;

		var a_dropdown_this = this;

		var unselect_grid_header_array = [];

		var select_grid_header_array = [];

		var show_search_inputs = false;

		var unselect_grid_search_map = null;

		var select_grid_search_map = null;

		var unselect_grid_sort_map = null;

		var select_grid_sort_map = null;

		var select_item = null;

		var tree_mode = false;

		var on_tree_grid_row_select = false; //#2566 - added select row callback so that trees can be used for edit-view navigation

		var unselect_grid_last_row = '';

		var select_grid_last_row = '';

		var allow_multiple_selection = true;

		var allow_drag_to_order = false;

		var pager_data = null;

		var paging_widget = null;

		var real_selected_items = null; //Set this after search in select grid;

		var start;

		var last;

		var next;

		var end;

		var paging_selector;

		var left_buttons_div;

		var right_buttons_div;

		var left_buttons_enable;

		var right_buttons_enable;

		var field;

		var error_tip_box;

		var error_string = '';

		var default_height = 150;

		var unselect_grid_no_result_box = null;

		var select_grid_no_result_box = null;

		var box_width;

		var focus_in_select_grid = false;

		var auto_sort = false;

		var isChanged = false;

		var api = null; //Pass from owner

		var column_editor = null;

		var column_option_key = null;

		var display_column_settings = true;

		var quick_search_timer;

		var quick_search_typed_keys = '';

		var quick_search_dic = {};

		var max_height = false;
		var static_height = false;

		var resize_grids = false;

		//Select all records in target grid
		var selectAllInGrid = function( target, deSelect ) {
			target.resetSelection();
			if ( !deSelect ) {
				var source_data = target.getGridParam( 'data' );
				var len = source_data.length;
				for ( var i = 0; i < len; i++ ) {
					var item = source_data[i];
					if ( Global.isSet( item.id ) ) {
						target.grid.jqGrid( 'setSelection', item.id, false );
					} else {
						target.grid.jqGrid( 'setSelection', i + 1, false );
					}

				}

				target.grid.parents( '.cbox-header' ).prop( 'checked', true );
			}
		};

		Global.addCss( 'global/widgets/awesomebox/ADropDown.css' );

		this.isChanged = function() {
			return isChanged;
		};

		this.setIsChanged = function( val ) {
			isChanged = val;
		};

		this.unSelectAll = function( target ) {
			selectAllInGrid( target, true );
		};

		this.getFocusInSeletGrid = function() {
			return focus_in_select_grid;
		};

		this.selectAll = function() {

			if ( focus_in_select_grid ) {
				selectAllInGrid( select_grid );
			} else {
				selectAllInGrid( unselect_grid );
			}

		};

		this.gridScrollTop = function() {

			unselect_grid.grid.parent().parent().scrollTop( 0 );
		};

		this.gridScrollDown = function() {

			unselect_grid.grid.parent().parent().scrollTop( 10000 );
		};

		this.getBoxWidth = function() {
			return box_width;
		};

		this.setErrorStyle = function( errStr, show, isWarning ) {
			if ( isWarning ) {
				$( this ).addClass( 'warning-tip' );
			} else {
				$( this ).addClass( 'error-tip' );
			}
			error_string = errStr;

			if ( show ) {
				this.showErrorTip();
			}
		};

		this.showErrorTip = function( sec ) {

			if ( !Global.isSet( sec ) ) {
				sec = 2;
			}

			if ( !error_tip_box ) {
				error_tip_box = Global.loadWidgetByName( WidgetNamesDic.ERROR_TOOLTIP );
				error_tip_box = error_tip_box.ErrorTipBox();
			}
			if ( $( this ).hasClass( 'warning-tip' ) ) {
				error_tip_box.show( this, error_string, sec, true );
			} else {
				error_tip_box.show( this, error_string, sec );
			}
		};

		this.hideErrorTip = function() {

			if ( Global.isSet( error_tip_box ) ) {
				error_tip_box.remove();
			}

		};

		this.clearErrorStyle = function() {
			$( this ).removeClass( 'error-tip' );
			$( this ).removeClass( 'warning-tip' );
			this.hideErrorTip();
			error_string = '';
		};

		this.getField = function() {
			return field;
		};

		// Must call after setUnSelectGridData
		this.setValue = function( val ) {
			this.setSelectGridData( val );
		};

		this.getValue = function() {
			return this.getSelectItems();
		};

		this.getSelectGridSortMap = function() {
			return select_grid_sort_map;
		};

		this.getUnSelectGridSortMap = function() {
			return unselect_grid_sort_map;
		};

		this.getUnSelectGridMap = function() {

			if ( !unselect_grid_search_map ) {
				unselect_grid_search_map = {};
			}

			return unselect_grid_search_map;
		};

		this.getUnSelectGrid = function() {
			return unselect_grid;
		};

		this.getSelectGrid = function() {
			return select_grid;
		};

		this.getSelectGridMap = function() {

			if ( !select_grid_search_map ) {
				select_grid_search_map = {};
			}

			var ids = [];
			if ( allow_multiple_selection ) {
				var select_items = a_dropdown_this.getSelectItems();
				for ( var i = 0; i < select_items.length; i++ ) {
					ids.push( select_items[i][key] );
				}
			}

			if ( ids.length > 0 ) {
				select_grid_search_map.id = ids;
			} else {
				return false;
			}

			return select_grid_search_map;
		};

		this.collectUnselectGridColumns = function() {
			var columns = unselect_grid.getGridParam( 'colModel' );

			var len = ( ( columns ) ? columns.length : 0 );

			unselect_grid_header_array = [];

			for ( var i = 0; i < len; i++ ) {
				var column_info = columns[i];
				var column_header = $( this ).find( 'div #jqgh_unselect_grid_' + id + '_' + column_info.name );

				unselect_grid_header_array.push( column_header.TGridHeader( { column_model: column_info } ) );

				column_header.bind( 'headerClick', onUnSelectColumnHeaderClick );
			}

			a_dropdown_this.setGridHeaderStyle( 'unselect_grid' );

			function onUnSelectColumnHeaderClick( e, headerE, column_model ) {

				//Error: Uncaught TypeError: Cannot read property 'setCachedSortFilter' of null in /interface/html5/global/widgets/awesomebox/ADropDown.js?v=7.4.6-20141027-072624 line 286
				if ( !parent_a_combo_box || !parent_a_combo_box.getAPI() ) {
					return;
				}

				var field = column_model.name;

				if ( field === 'cb' ) { //first column, check box column.
					return;
				}

				if ( headerE.metaKey || headerE.ctrlKey ) {
					a_dropdown_this.buildSortCondition( false, field, 'unselect_grid' );
				} else {
					a_dropdown_this.buildSortCondition( true, field, 'unselect_grid' );

				}

				parent_a_combo_box.setCachedSortFilter( unselect_grid_sort_map );
				a_dropdown_this.setGridHeaderStyle( 'unselect_grid' );
				parent_a_combo_box.onADropDownSearch( 'unselect_grid' );

			}
		};

		this.buildSortCondition = function( reset, field, targetName ) {

			var sort_map = null;
			var nextSort = 'desc';

			if ( targetName === 'unselect_grid' ) {
				sort_map = unselect_grid_sort_map;
			} else {
				sort_map = select_grid_sort_map;
			}

			if ( reset ) {

				if ( sort_map && sort_map.length > 0 ) {
					var len = sort_map.length;
					var found = false;
					for ( i = 0; i < len; i++ ) {
						var sortItem = sort_map[i];
						for ( var key in sortItem ) {
							if ( key === field ) {
								if ( sortItem[key] === 'asc' ) {
									nextSort = 'desc';
								} else {
									nextSort = 'asc';
								}

								found = true;
							}
						}

						if ( found ) {
							break;
						}

					}

				}

				sort_map = [
					{}
				];
				sort_map[0][field] = nextSort;

			} else {
				if ( !sort_map ) {
					sort_map = [
						{}
					];
					sort_map[0][field] = 'desc';
				} else {
					len = sort_map.length;
					found = false;
					for ( var i = 0; i < len; i++ ) {
						sortItem = sort_map[i];
						for ( var key in sortItem ) {
							if ( key === field ) {
								if ( sortItem[key] === 'asc' ) {
									sortItem[key] = 'desc';
								} else {
									sortItem[key] = 'asc';
								}

								found = true;
							}
						}

						if ( found ) {
							break;
						}

					}

					if ( !found ) {
						sort_map.push( {} );
						sort_map[len][field] = 'desc';
					}
				}

			}

			if ( targetName === 'unselect_grid' ) {
				unselect_grid_sort_map = sort_map;
			} else {
				select_grid_sort_map = sort_map;
			}

		};

		this.setGridHeaderStyle = function( targetName ) {

			var headerArray = [];
			var sort_map = [];

			if ( targetName === 'unselect_grid' ) {
				headerArray = unselect_grid_header_array;
				sort_map = unselect_grid_sort_map;
			} else {
				headerArray = select_grid_header_array;
				sort_map = select_grid_sort_map;
			}

			var len = headerArray.length;

			for ( var i = 0; i < len; i++ ) {
				var tGridHeader = headerArray[i];
				var field = tGridHeader.getColumnModel().name;

				tGridHeader.cleanSortStyle();

				if ( sort_map ) {
					var sortArrayLen = sort_map.length;

					for ( var j = 0; j < sortArrayLen; j++ ) {
						var sortItem = sort_map[j];
						var sortField = Global.getFirstKeyFromObject( sortItem );
						if ( sortField === field ) {

							if ( sortArrayLen > 1 ) {
								tGridHeader.setSortStyle( sortItem[sortField], j + 1 );
							} else {
								tGridHeader.setSortStyle( sortItem[sortField], 0 );
							}

						}

					}
				}

			}

		};

		this.collectSelectGridColumns = function() {
			var columns = select_grid.getGridParam( 'colModel' );

			var len = ( ( columns ) ? columns.length : 0 );

			select_grid_header_array = [];

			for ( var i = 0; i < len; i++ ) {
				var column_info = columns[i];
				var column_header = $( this ).find( 'div #jqgh_select_grid_' + id + '_' + column_info.name );

				select_grid_header_array.push( column_header.TGridHeader( { column_model: column_info } ) );

				column_header.bind( 'headerClick', onSelectColumnHeaderClick );

			}
			a_dropdown_this.setGridHeaderStyle( 'select_grid' );

			function onSelectColumnHeaderClick( e, headerE, column_model ) {

				if ( !parent_a_combo_box || !parent_a_combo_box.getAPI() ) {
					return;
				}

				var field = column_model.name;

				if ( field === 'cb' ) { //first column, check box column.
					return;
				}

				if ( headerE.metaKey || headerE.ctrlKey ) {
					a_dropdown_this.buildSortCondition( false, field, 'select_grid' );
				} else {
					a_dropdown_this.buildSortCondition( true, field, 'select_grid' );

				}
				parent_a_combo_box.setCachedSelectedGridSortFilter( select_grid_sort_map );
				a_dropdown_this.setGridHeaderStyle( 'select_grid' );
				parent_a_combo_box.onADropDownSearch( 'select_grid' );

			}

		};

		//HightLight select item in UnSelect grid when !allow_multiple_selection
		this.setSelectItem = function( val, target_grid ) {
			if ( !target_grid ) {
				target_grid = unselect_grid;
			}
			var source_data = target_grid.getGridParam( 'data' );
			val && (select_item = val);
			if ( source_data && source_data.length > 0 ) {
				for ( var i = 0; i < source_data.length; i++ ) {
					var content = source_data[i];
					var temp_val = val;
					!val && (temp_val = source_data[0]);

					var content_key = key;
					if ( tree_mode && key == 'id' ) {
						content_key = '_id_';
					}

					if ( content[content_key] == temp_val[key] ) {  //Some times 0, sometimes '0'


						var content_id_key = 'id';
						if ( tree_mode ) {
							content_id_key = '_id_';
						}


						//Always use id to set select row, all record array should have id
						target_grid.grid.find( 'tr[id="' + content[content_id_key] + '"]' ).focus();
						if ( target_grid.grid.hasClass( 'unselect-grid' ) ) {
							if ( unselect_grid_last_row ) {
								target_grid.grid.jqGrid( 'saveRow', unselect_grid_last_row );
							}
							unselect_grid_last_row = content[content_id_key];
						} else {
							if ( select_grid_last_row ) {
								target_grid.grid.jqGrid( 'saveRow', select_grid_last_row );
							}
							select_grid_last_row = content[content_id_key];
						}
						target_grid.grid.jqGrid( 'setSelection', content[content_id_key], false );
						target_grid.grid.jqGrid( 'editRow', content[content_id_key], true );
						val && (select_item = content);
						return false;
					}
				}
				this.setTotalDisplaySpan();
			}
		};

		this.getUnSelectGridData = function() {
			return unselect_grid.getGridParam( 'data' );
		};

		this.getSelectItem = function() {
			return select_item;
		};

		this.getSelectItems = function () {
			//Save last edit row if there is editable row in grid cell;
			if ( unselect_grid_last_row.length > 0 ) {
				unselect_grid.grid.jqGrid( 'saveRow', unselect_grid_last_row );
				unselect_grid_last_row = '';
			}

			if ( select_grid_last_row.length > 0 ) {
				select_grid.grid.jqGrid( 'saveRow', select_grid_last_row );
				select_grid_last_row = '';
			}

			var retval = null;
			if ( show_search_inputs && real_selected_items && real_selected_items.length > 0 ) {
				retval = real_selected_items;
			} else {
				retval = select_grid.getGridParam( 'data' ); //Set this when setSelectGridItems
			}

			//Make sure we never return null, and always at least an empty array. This helps prevent JS excptions when we run .length on the return value.
			if ( Global.isArray( retval ) == false ) {
				return Array();
			}

			return retval;
		};

		this.setRealSelectItems = function ( all_records, selected_ids ) {
			real_selected_items = [];
			if ( selected_ids != TTUUID.zero_id && selected_ids.length > 0 ) {
				for ( var n = 0; n < all_records.length; n++ ) {
					if ( selected_ids.indexOf( all_records[n].id ) != -1 ) {
						real_selected_items.push( all_records[n] );
					}
				}
			}

			return real_selected_items;
		};

		this.getAllowMultipleSelection = function() {
			return allow_multiple_selection;
		};

		this.getResizeGrids = function() {
			return resize_grids;
		};

		this.setResizeGrids = function( val ) {
			resize_grids = val;
			if ( val ) {
				this.setGridColumnsWidths();
			}
		};

		this.getTreeMode = function() {
			return tree_mode;
		};

		//Must Set this after set Columns
		this.setUnselectedGridData = function( val ) {
			static_source_data = val;

			if ( !tree_mode ) {
				unselect_grid.clearGridData();
				unselect_grid.setData( val );

				this.setTotalDisplaySpan();

			} else {
				this.reSetUnSelectGridTreeData( val );

			}

			this.setUnSelectGridDragAble();

			this.setGridsHeight();
		};

		/**
		 * This function calculates the optimal widths for awesomebox columns
		 * and whether they should overflow or shrink to fit
		 */
		this.setGridColumnsWidths = function() {
			var primary_grid = unselect_grid;
			var secondary_grid = select_grid;

			//don't swap the grids. always size on left grid.
			var p_data = primary_grid.getData();
			var s_data = secondary_grid.getData();
			if( s_data.length != 0 && p_data.length == 0 ){
				//swap grids if no data in primary.
				var temp = primary_grid;
				primary_grid = secondary_grid;
				secondary_grid = temp;
			}

			//Make sure what dropdown is expanded, we do not spill over the right edge of the browser causing scrollbars to appear.
			var max_grid_width = ( ( ( ( Global.bodyWidth() - $( this ).offset().left ) - 100 ) / 2 ) );

			var default_width = primary_grid.setGridColumnsWidth( null, { max_grid_width: max_grid_width } );
			var colModel = primary_grid.getColumnModel();

			secondary_grid.setGridColumnsWidth( colModel );

			var total_headers_width = 0;
			for ( var i = 0; i < colModel.length; i++ ) {
				total_headers_width += colModel[i].widthOrg + 1; //collect the (calculated) column widths (+1 for border
			}

			var width = 200
			var offset = 28;
			//awesomeboxes with more than 1 column need to adjust for td borders.

			if ( colModel[0].name == 'cb' ) {
				offset += 2;
			}

			if ( tree_mode ) {
				offset = 26;
			} else {
				offset = 22;
			}

			//only shrink smaller grids to fit container
			if ( total_headers_width <= ( width ) || colModel.length < 5 ) {
				//prevent resize on search.
				width = primary_grid.grid.parents( '.unselect-grid-div, .select-grid-div' ).width() - offset ;
			} else {
				width = total_headers_width; //primary_grid.grid.parents( '.unselect-grid-div' ).width() - offset;
				if ( default_width > width ) {
					width = default_width;
				}
			}

			primary_grid.setGridWidth( width );
			secondary_grid.setGridWidth( width );

			this.resizeUnSelectSearchInputs();
			this.resizeSelectSearchInputs();
		};

		this.getPagerData = function() {
			return pager_data;
		};

		//Always setPager data no matter static options or api.
		this.setPagerData = function( value ) {

			pager_data = value;

			if ( LocalCacheData.paging_type === 0 ) {
				if ( paging_widget.parent().length > 0 ) {
					paging_widget.remove();
				}

				paging_widget.css( 'width', unselect_grid.width() );
				unselect_grid.append( paging_widget );

				paging_widget.click(function(){
					$this.onPaging();
				});

				if ( !pager_data || pager_data.is_last_page || pager_data.last_page_number < 0 ) {
					paging_widget.css( 'display', 'none' );
				} else {
					paging_widget.css( 'display', 'inline-block' );
				}

			} else {

				if ( !pager_data || pager_data.last_page_number < 0 ) {
					left_buttons_div.css( 'display', 'none' );
					right_buttons_div.css( 'display', 'none' );

				} else {
					left_buttons_div.css( 'display', 'inline-block' );
					right_buttons_div.css( 'display', 'inline-block' );

					if ( pager_data.is_last_page === true ) {
						right_buttons_div.addClass( 'disabled' );
						right_buttons_div.addClass( 'disabled-image' );
						right_buttons_enable = false;
					} else {
						right_buttons_div.removeClass( 'disabled' );
						right_buttons_div.removeClass( 'disabled-image' );
						right_buttons_enable = true;
					}

					if ( pager_data.is_first_page ) {
						left_buttons_div.addClass( 'disabled' );
						left_buttons_div.addClass( 'disabled-image' );
						left_buttons_enable = false;

					} else {
						left_buttons_div.removeClass( 'disabled' );
						left_buttons_div.removeClass( 'disabled-image' );
						left_buttons_enable = true;
					}
				}

			}

			a_dropdown_this.setTotalDisplaySpan();
		};

		this.onPaging = function() {
			parent_a_combo_box.onADropDownSearch( 'unselect_grid', 'next' );
		};

		this.reSetUnSelectGridTreeData = function( val ) {
			var scroll_position = unselect_grid.grid.parent().parent().scrollTop();
			var grid_data = unselect_grid.getData();
			if ( grid_data.length > 0 && val.length > 0 ) {
				unselect_grid.setData( val );
			} else {
				var col_model = unselect_grid.getGridParam( 'colModel' );

				if ( !unselect_grid.grid.is( ':visible' ) ) {
					return;
				}
				unselect_grid.grid.jqGrid( 'GridUnload' );
				unselect_grid = $( this ).find( '.unselect-grid' );
				unselect_grid = unselect_grid.grid = new TTGrid( $( this ).find( '.unselect-grid' ).attr( 'id' ), {
					container_selector: '.unselect-grid-div',
					altRows: true,
					datastr: val,
					datatype: 'jsonstring',
					sortable: false,
					width: 440,
					onResizeGrid: resize_grids,
					//maxHeight: default_height,
					colNames: [],
					rowNum: 10000,
					colModel: col_model,
					ondblClickRow: a_dropdown_this.onUnSelectGridDoubleClick,
					gridview: true,
					treeGrid: true,
					treeGridModel: 'adjacency',
					treedatatype: 'local',
					ExpandColumn: 'name',
					multiselect: allow_multiple_selection,
					multiselectPosition: 'none',
					winMultiSelect: allow_multiple_selection,
					onCellSelect: function( id, k, el, e ) {
						if ( $( e.target ).prop( 'class' ).indexOf( 'ui-icon-triangle' ) != -1 ) {
							return false;
						}
						id = Global.convertToNumberIfPossible( id );

						if ( !allow_multiple_selection ) {
							var source_data = unselect_grid.getGridParam( 'data' );
							$.each( source_data, function( index, content ) {
								if ( tree_mode && key == 'id' ) {
									key = '_id_';
								}

								if ( content[key] == id ) {
									select_item = content;
									isChanged = true;
									if ( !LocalCacheData.currently_collapsing_navigation_tree_element ) { //#2583 - must allow null or false
										a_dropdown_this.trigger( 'close', [a_dropdown_this] );
									}
									return;
								}
							} );

						}

						if ( on_tree_grid_row_select ) {
							on_tree_grid_row_select( id, LocalCacheData.currently_collapsing_navigation_tree_element );
						}
					},
					beforeSelectRow: function( iRow, e ) {
						var $td = $( e.target ).closest( 'tr.jqgrow>td' ),
								iCol = $td.length > 0 ? $td[0].cellIndex : -1,
								p = $( this ).jqGrid( 'getGridParam' ),
								cm = iCol >= 0 ? p.colModel[iCol] : null;

						if ( cm != null && cm.name === p.ExpandColumn &&
								$( e.target ).closest( '.tree-wrap' ).length > 0 ) {

							return false; // prevent row selection
						}
						return true;
					},
					jsonReader: {
						repeatitems: false,
						root: function( obj ) {
							return obj;
						},
						page: function( obj ) {
							return 1;
						},
						total: function( obj ) {
							return 1;
						},
						records: function( obj ) {
							return obj.length;
						}
					},
					onResizeGrid: resize_grids
				}, col_model );
			}
			scroll_position > 0 && unselect_grid.grid.parent().parent().scrollTop( scroll_position );
			this.setGridsHeight();

			var select_items = this.getSelectItems();
			$( this ).find( 'tr' ).removeClass( 'selected-tree-cell' );
			for ( var i = (select_items.length - 1); i >= 0; i-- ) {
				$( this ).find( '.unselect-grid-div' ).find( 'tr#' + select_items[i].id ).addClass( 'selected-tree-cell' );
			}
		};

		this.setGridsHeight = function() {
			//Calculate the max possible size of awesomebox.

			if ( static_height ) {
				unselect_grid.grid.setGridHeight( static_height );
				if ( allow_multiple_selection ) {
					select_grid.grid.setGridHeight( static_height );
				}
				return;
			}

			if ( !parent_a_combo_box ) {
				return;
			}

			var top_offset = parent_a_combo_box.offset().top;
			var bottom_offset = Global.bodyHeight() - top_offset - 30;
			var new_height = top_offset > bottom_offset ? top_offset : bottom_offset;

			new_height = new_height - 130;

			var source_data = parent_a_combo_box.getStaticSourceData();

			if ( !source_data ) {
				new_height = default_height;
			} else {
				var source_height = source_data.length * 23;

				if ( source_height < default_height ) {
					new_height = default_height;
				} else if ( source_height > default_height && source_height < new_height ) {
					new_height = source_height;
				}
			}

			if ( max_height && max_height < new_height ) {
				new_height = max_height;
			}

			unselect_grid.grid.setGridHeight( new_height );
			if ( allow_multiple_selection ) {
				select_grid.grid.setGridHeight( new_height );
			}

			this.setPosition( top_offset, new_height );

		};

		this.setPosition = function( top_offset, new_height ) {

			if ( top_offset + new_height + 130 < Global.bodyHeight() ) {
				a_dropdown_this.parent().css( 'top', top_offset + 25 );
			} else {

				if ( new_height != default_height ) {
					a_dropdown_this.parent().css( 'top', (top_offset - new_height - 125) );
				} else {
					a_dropdown_this.parent().css( 'top', (top_offset - new_height - 125) );
				}

			}
		};

		this.onTreeCellFormat = function( cell_value, related_data, row ) {
			var selected_items = a_dropdown_this.getSelectItems();
			for ( var i = 0, m = selected_items.length; i < m; i++ ) {
				var item = selected_items[i];
				if ( item.name === cell_value ) {
					return '<span class="selected-tree-cell">' + cell_value + '</span>';
				}
			}
			return cell_value;
		};

		//Do this before set data
		this.setColumns = function( val ) {

			for ( var a = 0; a < val.length; a++ ) {
				val[a].resizable = false;
			}

			unselect_grid.grid.jqGrid( 'GridUnload' );
			unselect_grid = $( this ).find( '.unselect-grid' );
			unselect_grid.getGridParam();
			var unselect_grid_search_div = $( this ).find( '.unselect-grid-search-div' );
			var select_grid_search_div = $( this ).find( '.select-grid-search-div' );

			var gridWidth = $( '.unselect-grid-border-div' ).width() - 2; // single-column width
			if ( show_search_inputs ) {
				gridWidth -= 15;
			}

			gridWidth = val.length * 125;
			if ( gridWidth < 438 ) {
				box_width = gridWidth = 438;
			}
			if ( val.length > 1 ) {

				box_width = gridWidth;
				if ( allow_multiple_selection && gridWidth > (Global.bodyWidth() / 2 - 30 - 15) ) {
					box_width = (Global.bodyWidth() / 2 - 30 - 15);
				} else if ( !allow_multiple_selection && gridWidth > (Global.bodyWidth() - 30 - 15) ) {
					box_width = (Global.bodyWidth() - 30 - 15);
				}

				this.find( '.unselect-grid-div' ).width( null );
				this.find( '.unselect-grid-border-div' ).width( null );

				this.find( '.select-grid-div' ).width( null );
				this.find( '.select-grid-border-div' ).width( null );

				if ( show_search_inputs ) {
					unselect_grid_search_div.css( 'width', null );
					select_grid_search_div.css( 'width', null );
				}
			} else {
				box_width = gridWidth;
			}

			if ( !tree_mode ) {
				unselect_grid = new TTGrid( $( unselect_grid ).attr( 'id' ), {
					container_selector: '.unselect-grid-div',
					altRows: true,
					data: [],
					datatype: 'local',
					sortable: false,
					width: gridWidth,
					//maxHeight: default_height,
					colNames: [],
					rowNum: 10000,
					keep_scroll_place: true,
					ondblClickRow: a_dropdown_this.onUnSelectGridDoubleClick,
					colModel: val,
					multiselect: allow_multiple_selection,
					multiboxonly: allow_multiple_selection,
					viewrecords: true,
					editurl: 'clientArray',
					resizeStop: function() {
						a_dropdown_this.resizeUnSelectSearchInputs();
					},
					onCellSelect: function( id ) {
						id = Global.convertToNumberIfPossible( id );

						if ( !allow_multiple_selection ) {

							var source_data = unselect_grid.getGridParam( 'data' );

							$.each( source_data, function( index, content ) {

								if ( key !== 'id' ) {
									if ( content['id'] == id ) {
										select_item = content;
										isChanged = true;
										a_dropdown_this.trigger( 'close', [a_dropdown_this] );
										return false;
									}
								} else {
									if ( content[key] == id ) {
										select_item = content;
										isChanged = true;
										a_dropdown_this.trigger( 'close', [a_dropdown_this] );
										return false;
									}
								}

							} );

						}

						if ( unselect_grid_last_row ) {
							unselect_grid.grid.jqGrid( 'saveRow', unselect_grid_last_row );
						}
						unselect_grid.grid.jqGrid( 'editRow', id, true );
						unselect_grid_last_row = id;

						a_dropdown_this.setTotalDisplaySpan();

						function getSelectValue() {
							var len = source_data.length;

						}
					},
					onResizeGrid: resize_grids
				}, val );

			} else {
				if ( tree_mode ) {
					var tree_columns = _.map( val, _.clone );
					_.map( tree_columns, function( item ) {
						item.formatter = a_dropdown_this.onTreeCellFormat;
					} );
				}
				unselect_grid = new TTGrid( $( unselect_grid ).attr( 'id' ), {
					container_selector: '.unselect-grid-div',
					altRows: true,
					datastr: [],
					datatype: 'jsonstring',
					sortable: false,
					width: gridWidth,
					maxHeight: default_height,
					colNames: [],
					rowNum: 10000,
					colModel: tree_columns,
					ondblClickRow: a_dropdown_this.onUnSelectGridDoubleClick,
					onCellSelect: function() {
						a_dropdown_this.onUnSelectGridSelectRow();
					},
					gridview: true,
					treeGrid: true,
					treeGridModel: 'adjacency',
					treedatatype: 'local',
					ExpandColumn: 'name',
					multiselect: false, //allow_multiple_selection,

					jsonReader: {
						repeatitems: false,
						root: function( obj ) {
							return obj;
						},
						page: function( obj ) {
							return 1;
						},
						total: function( obj ) {
							return 1;
						},
						records: function( obj ) {
							return obj.length;
						}
					},


					onSelectRow: function( id ) {
						if ( on_tree_grid_row_select ) {
							on_tree_grid_row_select( id );
						}

						id = Global.convertToNumberIfPossible( id );

					},

					onResizeGrid: resize_grids
				}, tree_columns );
			}

			this.collectUnselectGridColumns(); //Make each column as THeader plugin and save them

			if ( show_search_inputs && parent_a_combo_box.getAPI() ) {
				this.buildUnSelectSearchInputs(); //Build search input above columns
			}

			select_grid.grid.jqGrid( 'GridUnload' );
			select_grid = $( this ).find( '.select-grid' );
			select_grid = new TTGrid( $( select_grid ).attr( 'id' ), {
				container_selector: '.select-grid-div',
				altRows: true,
				data: [],
				datatype: 'local',
				sortable: false,
				width: gridWidth,
				//maxHeight: default_height,
				colNames: [],
				rowNum: 10000,
				ondblClickRow: this.onSelectGridDoubleClick,
				colModel: val,
				multiselect: allow_multiple_selection,
				multiboxonly: allow_multiple_selection,
				viewrecords: true,
				keep_scroll_place: true,
				resizeStop: function() {
					a_dropdown_this.resizeSelectSearchInputs();
				},
				onCellSelect: function( id ) {
					if ( id ) {

						if ( select_grid_last_row ) {
							select_grid.grid.jqGrid( 'saveRow', select_grid_last_row );
						}
						select_grid.grid.jqGrid( 'editRow', id, true );
						select_grid_last_row = id;
					}

				},
				onResizeGrid: resize_grids
			}, val );

			this.collectSelectGridColumns(); //Make each column as THeader plugin and save them
			if ( show_search_inputs && parent_a_combo_box.getAPI() ) {
				this.buildSelectSearchInputs(); //Build search input above columns
			}

		};

		this.buildSelectSearchInputs = function() {
			var len = select_grid_header_array.length;

			var search_div = $( this ).find( '.select-grid-search-div' );
			var first_column_width = 0;
			var search_input_array = [];
			for ( var i = 0; i < len; i++ ) {
				var header = select_grid_header_array[i];

				if ( i === 0 ) {
					first_column_width = header.getWidth();
					continue;
				} else if ( allow_multiple_selection && i === 1 ) {
					var search_input = $( '<input type=\'text\' class=\'search-input\'>' );
					search_input.css( 'width', header.getWidth() + first_column_width );
				} else {
					search_input = $( '<input type=\'text\' class=\'search-input\'>' );
					search_input.css( 'width', header.getWidth() );
				}

				search_input.on('drop', function(e){
					e.preventDefault();
				});

				search_input.ASearchInput( { column_model: header.getColumnModel() } ); //Make it as ASearchInout Widget;

				search_div.append( search_input );
				search_input_array.push( search_input );
				//Set cached seach_input data back, usually in navigation_mode
				if ( select_grid_search_map ) {
					search_input.setFilter( select_grid_search_map );

				}

				search_input.bind( 'searchEnter', function( e, searchVal, field ) {

					if ( a_dropdown_this.getValue().length < 1 ) {
						return;
					}

					if ( !select_grid_search_map ) {
						select_grid_search_map = {};
					}

					delete select_grid_search_map.id;

					if ( !searchVal ) {
						delete select_grid_search_map[field];
					} else {
						select_grid_search_map[field] = searchVal;
					}

					parent_a_combo_box.setCachedSelectGridSearchInputsFilter( Global.clone( select_grid_search_map ) );

					parent_a_combo_box.onADropDownSearch();
				} );

			}

			var close_btn = $( '<button class="close-btn"><img src="'+ Global.getRealImagePath( 'images/close.png' ) +'"/></button>' );

			//close_btn.width( unselect_grid_header_array[0].getWidth() + 2 );
			//close_btn.width( 22 );
			search_div.prepend( close_btn );
			var $this = this;
			close_btn.click( function() { //clear search inputs for select box
				select_grid_search_map = {};
				parent_a_combo_box.setCachedSelectGridSearchInputsFilter( select_grid_search_map );
				parent_a_combo_box.onADropDownSearch();

				for ( var i = 0; i < search_input_array.length; i++ ) {
					var s_i = search_input_array[i];
					s_i.clearValue();
				}
			} );
		};

		this.resizeUnSelectSearchInputs = function() {
			var search_div = $( this ).find( '.unselect-grid-search-div' );

			var search_inputs = search_div.find( '.search-input' );
			var first_column_width;
			var unselect_grid_search_div = $( this ).find( '.unselect-grid-search-div' );

			var len = search_inputs.length;
			var header;
			var search_input;
			if ( allow_multiple_selection ) {
				first_column_width = unselect_grid_header_array[0].width();

				if ( len == 1 ) {
					header = unselect_grid_header_array[1];
					search_input = $( search_inputs[0] );
					search_input.css( 'width', header.getWidth() );
				} else {
					for ( var i = 0; i < len; i++ ) {
						header = unselect_grid_header_array[i + 1];
						search_input = $( search_inputs[i] );
						if ( i == (len - 1) ) {
							search_input.css( 'width', header.getWidth() );
						} else {
							search_input.css( 'width', header.getWidth() + 1 );
						}

					}
				}
			} else {
				for ( i = 0; i < len; i++ ) {
					header = unselect_grid_header_array[i];
					search_input = $( search_inputs[i] );
					if ( i === 0 ) {
						search_input.css( 'width', header.getWidth() - 22 );
					} else if ( i == (len - 1) ) {
						search_input.css( 'width', header.getWidth() + 1 );
					} else {
						search_input.css( 'width', header.getWidth() + 1 );
					}

				}
			}

			var unselect_grid_width = unselect_grid.grid.parents( '.ui-jqgrid-jquery-ui' ).width() ? unselect_grid.grid.parent().parent().width() : 100;
			unselect_grid.setGridWidth( unselect_grid_width );
			//var unselect_grid_width = unselect_grid.getWidth();
			var unselect_grid_search_div_width = unselect_grid_width;
			if ( tree_mode ) {
				unselect_grid_width = unselect_grid.getWidth();
			}

			var outer_box_width = unselect_grid_width + 22;
			var inner_box_width = unselect_grid_width + 4;
			var max_width = ( $( 'body' ).width() - 30 );
			if ( allow_multiple_selection ) {
				max_width = ( ( $( 'body' ).width() / 2 ) - 30 );
			}

			if ( outer_box_width > max_width ) {
				outer_box_width = max_width;
				inner_box_width = max_width - 17;
			}

			unselect_grid.grid.parents( '.unselect-grid-div' ).css( 'width', outer_box_width ); //outer blue box width
			unselect_grid.grid.parents( '.unselect-grid-border-div' ).css( 'width', inner_box_width ); //red border div.
			unselect_grid_search_div.css( 'width', unselect_grid_search_div_width );
		};

		this.resizeSelectSearchInputs = function() {
			var search_div = $( this ).find( '.select-grid-search-div' );

			var search_inputs = search_div.find( '.search-input' );
			var first_column_width;
			var select_grid_search_div = $( this ).find( '.select-grid-search-div' );

			var len = search_inputs.length;

			first_column_width = select_grid_header_array[0].width() + 5;
			if ( len == 1 ) {
				header = unselect_grid_header_array[1];
				search_input = $( search_inputs[0] );
				search_input.css( 'width', header.getWidth() );
			} else {
				for ( var i = 0; i < len; i++ ) {
					var header = select_grid_header_array[i + 1];
					var search_input = $( search_inputs[i] );
					if ( i == (len - 1) ) {
						search_input.css( 'width', header.getWidth() );
					} else {
						search_input.css( 'width', header.getWidth() + 1 );
					}
				}
			}

			var newWidth = select_grid.grid.parents( '.ui-jqgrid-jquery-ui' ).width() ? select_grid.grid.parent().parent().width() : 100;
			select_grid.setGridWidth( newWidth );

			var outer_box_width = newWidth + 22;
			var inner_box_width = newWidth + 4;

			var max_width = ( ( $( 'body' ).width() / 2 ) - 30 );

			if ( outer_box_width > max_width ) {
				outer_box_width = max_width;
				inner_box_width = max_width - 17;
			}

			select_grid.grid.parents( '.select-grid-div' ).css( 'width', outer_box_width ); //outer blue box width
			select_grid.grid.parents( '.select-grid-border-div' ).css( 'width', inner_box_width ); //red border div.
			select_grid_search_div.css( 'width', newWidth );
		};

		this.buildUnSelectSearchInputs = function() {
			var len = unselect_grid_header_array.length;

			var search_div = $( this ).find( '.unselect-grid-search-div' );
			var first_column_width = 0;
			var search_input_array = [];
			for ( var i = 0; i < len; i++ ) {
				var header = unselect_grid_header_array[i];

				if ( allow_multiple_selection && i === 0 ) {
					first_column_width = header.getWidth();
					continue;
				} else if ( allow_multiple_selection && i === 1 ) {
					var search_input = $( '<input type=\'text\' class=\'search-input unselect-grid-search-input\'>' );
					var width = header.getWidth() + first_column_width - 2;
					search_input.css( 'width', width );
				} else {
					search_input = $( '<input type=\'text\' class=\'search-input\'>' );
					if ( i == (len - 1) ) {
						search_input.css( 'width', header.getWidth() + 1 + 'px' );
					} else {
						search_input.css( 'width', header.getWidth() + 'px' );
					}
				}

				search_input.on('drop', function(e){
					e.preventDefault();
				});

				search_input.ASearchInput( { column_model: header.getColumnModel() } ); //Make it as ASearchInout Widget;

				search_div.append( search_input );
				search_input_array.push( search_input );

				//Set cached seach_input data back, unsualy in navigation_mode
				if ( unselect_grid_search_map ) {
					search_input.setFilter( unselect_grid_search_map );

				}

				//Do Column Search
				search_input.bind( 'searchEnter', function( e, searchVal, field ) {

					if ( !unselect_grid_search_map ) {
						unselect_grid_search_map = {};
					}

					if ( !searchVal ) {
						delete unselect_grid_search_map[field];
					} else {
						unselect_grid_search_map[field] = searchVal;
					}

					parent_a_combo_box.setCachedSearchInputsFilter( unselect_grid_search_map );

					parent_a_combo_box.onADropDownSearch( 'unselect_grid' );
				} );

			}

			var close_btn = $( '<button class="close-btn"><img src="'+ Global.getRealImagePath( 'images/close.png') +'"/></button>' );

			if ( allow_multiple_selection ) {
				//close_btn.width( 22 )
			} else {
				//close_btn.width( 14 );
			}

			search_div.prepend( close_btn );
			close_btn.click( function() {

				unselect_grid_search_map = {};
				parent_a_combo_box.setCachedSearchInputsFilter( unselect_grid_search_map );
				parent_a_combo_box.onADropDownSearch( 'unselect_grid' );

				for ( var i = 0; i < search_input_array.length; i++ ) {

					var s_i = search_input_array[i];
					s_i.clearValue();
				}

			} );

		};

		//Set select item when not allow multiple selection
		setSelectItem = function( val ) {
			select_item = val;
		};

		//Search Reesult in select grid. it's not effect the selectitems when getSelectItems
		this.setSelectGridSearchResult = function( val ) {

			if ( !real_selected_items || real_selected_items.length == 0 ) {
				//Clone the array with .slice(), this fixes the bug where you have an empty dropdown (no selected items)
				//You move 5 items to the right side, then search within those items to show only 1, then clear the search, and only 1 item would still be shown.
				real_selected_items = this.getSelectItems().slice();
			}
			select_grid.clearGridData();
			select_grid.setData( val );
		};

		//Must Set this after setUnselectedGridData for now
		//Remove select items form allColumn array
		this.setSelectGridData = function( val, searchResult ) {
			if ( parent_a_combo_box && parent_a_combo_box.getAPI() ) {
				val = Global.formatGridData( val, parent_a_combo_box.getAPI().key_name );
			}
			if ( Object.prototype.toString.call( static_source_data ) !== '[object Array]' || static_source_data.length < 1 ) {
				static_source_data = [];
			}
			//Uncaught TypeError: Cannot read property 'length' of undefined
			if ( !val ) {
				val = [];
			}
			var all_columns = static_source_data.slice(); //Copy from Static data
			var i;
			var j;
			var select_item;
			var tmp_select_items;
			var all_columns_len;
			if ( all_columns && all_columns.length > 0 ) {
				var selectItemLen = val.length;
				if ( !auto_sort ) {

					for ( i = 0; i < selectItemLen; i++ ) {
						select_item = val[i];
						if ( !Global.isSet( select_item[key] ) ) {
							select_item = [];
							select_item[key] = val[i];
							if ( !Global.isSet( tmp_select_items ) ) {
								tmp_select_items = [];
							}
						}
						all_columns_len = all_columns.length;
						for ( j = 0; j < all_columns_len; j++ ) {
							var fromAllColumn = all_columns[j];
							if ( fromAllColumn[key] == select_item[key] ) {
								//saved search select items may don't have ids if it's saved from flex, so set it back
								if ( !select_item.hasOwnProperty( 'id' ) && fromAllColumn.hasOwnProperty( 'id' ) ) {
									select_item.id = fromAllColumn.id;
								}
								if ( Global.isSet( tmp_select_items ) ) {
									tmp_select_items.push( fromAllColumn );
								}
								if ( !tree_mode ) {
									all_columns.splice( j, 1 );
								}
								break;
							}
						}
					}

				} else {
					all_columns_len = all_columns.length;
					for ( j = 0; j < all_columns_len; j++ ) {
						fromAllColumn = all_columns[j];
						for ( i = 0; i < selectItemLen; i++ ) {
							select_item = val[i];
							if ( !Global.isSet( select_item[key] ) ) {
								select_item = [];
								select_item[key] = val[i];
								if ( !Global.isSet( tmp_select_items ) ) {
									tmp_select_items = [];
								}
							}
							// we have both string case and number case. sometimes number will be 'xx'. So use == make sure all match
							if ( fromAllColumn[key] == select_item[key] ) {
								//saved search select items may don't have ids if it's saved from flex, so set it back
								if ( !select_item.hasOwnProperty( 'id' ) && fromAllColumn.hasOwnProperty( 'id' ) ) {
									select_item.id = fromAllColumn.id;
								}
								if ( Global.isSet( tmp_select_items ) ) {
									tmp_select_items.push( fromAllColumn );
								}
								if ( !tree_mode ) {
									all_columns.splice( j, 1 );
									all_columns_len = all_columns_len - 1;
									j = j - 1;
								}
								break;
							}
						}
					}
				}

			}
			// for all static options, that don't need get reald data, the length should always be match, use temp array because val don't
			//contains full info.
			if ( tmp_select_items && tmp_select_items.length === val.length ||
					(val.length > 0 && !val[0].hasOwnProperty( key )) ) {
				val = tmp_select_items;
			}
//			val = ( Global.isSet( tmp_select_items ) ) ? tmp_select_items : val;

			//don't refresh select grid if it's calling from onDropDownsearch whcih doing search in search input
			if ( !searchResult ) {
				//select_grid.clearGridData();
				//FIXES BUG #1998: The api call returns true when the data it's looking for is deleted. This causes the grid to add a blank row to the unselected side when clear is clicked.
				if ( typeof val === 'object' ) {
					select_grid.clearGridData();
					select_grid.setData( val );
				}
				select_grid.grid.trigger( 'reloadGrid' );
			}

			if ( !tree_mode ) {
				unselect_grid.clearGridData();
				unselect_grid.setData( all_columns );
				this.setTotalDisplaySpan();
			} else {
				a_dropdown_this.reSetUnSelectGridTreeData( all_columns );
			}
			a_dropdown_this.setSelectGridDragAble();
			a_dropdown_this.setUnSelectGridDragAble();

		};

		this.setUnSelectGridHighlight = function( array ) {
			unselect_grid.resetSelection();
			$.each( array, function( index, content ) {
				unselect_grid.grid.jqGrid( 'setSelection', content, false );
			} );

		};

		this.showNoResultCover = function( target_grid ) {

			this.removeNoResultCover( target_grid );

			var no_result_box = Global.loadWidgetByName( WidgetNamesDic.NO_RESULT_BOX );
			no_result_box.NoResultBox( { related_view_controller: this } );
			var grid_div;

			if ( target_grid === 'unselect_grid' ) {
				no_result_box.attr( 'id', id + target_grid + '_no_result_box' );

				grid_div = $( this ).find( '#gbox_unselect_grid_' + id );

				unselect_grid_no_result_box = no_result_box;

			} else {
				no_result_box.attr( 'id', id + target_grid + '_no_result_box' );

				grid_div = $( this ).find( '#gbox_select_grid_' + id );

				select_grid_no_result_box = no_result_box;

			}

			grid_div.append( no_result_box );

		};

		this.removeNoResultCover = function( target_grid ) {
			if ( target_grid === 'unselect_grid' ) {

				if ( unselect_grid_no_result_box ) {
					unselect_grid_no_result_box.remove();
					unselect_grid_no_result_box = null;
				}

			} else {

				if ( select_grid_no_result_box ) {
					select_grid_no_result_box.remove();
					select_grid_no_result_box = null;
				}

			}
		};

		this.setSelectGridHighlight = function( array ) {
			select_grid.resetSelection();
			$.each( array, function( index, content ) {
				select_grid.grid.jqGrid( 'setSelection', content, false );
			} );

		};

		this.setUnSelectGridDragAble = function() {

			var highlight_Rows = null;

			var trs = unselect_grid.grid.find( 'tr.ui-widget-content' ).attr( 'draggable', 'true' );

			trs.unbind( 'dragstart' ).bind( 'dragstart', function( event ) {
				var target = $( event.target );

				var container = $( '<table class=\'drag-holder-table\'></table>' );
				highlight_Rows = unselect_grid.grid.find( 'tr.ui-state-highlight' );
				var cloneRows = [];
				var len = highlight_Rows.length;
				if ( len === 0 ) {
					len = 1;
					unselect_grid.grid.jqGrid( 'setSelection', target.attr( 'id' ), false );
				} else if ( !target.hasClass( 'ui-state-highlight' ) ) {
					selectAllInGrid( unselect_grid, true );
					unselect_grid.grid.jqGrid( 'setSelection', target.attr( 'id' ), false );
					len = 1;
				}

				if ( len === 1 ) {
					highlight_Rows = unselect_grid.grid.find( 'tr.ui-state-highlight' );
					var clone_row = $( highlight_Rows[0] ).clone();

					clone_row.children().eq( 0 ).remove();
					clone_row.find( 'td' ).css( 'padding-right', 10 );
					clone_row.find( 'td' ).css( 'padding-left', 10 );

					container.append( clone_row );
				} else {
					container.append( len +' '+ $.i18n._('item(s) selected') );
				}

				$( 'body' ).find( '.drag-holder-table' ).remove();

				$( 'body' ).append( container );

				event.originalEvent.dataTransfer.setData( 'Text', 'un_select_grid' );//JUST ELEMENT references is ok here NO ID

				if ( event.originalEvent.dataTransfer.setDragImage ) {
					event.originalEvent.dataTransfer.setDragImage( container[0], -10, 0 );
				}

				return true;
			} );

			unselect_grid.grid.parent().parent().unbind( 'dragover' ).bind( 'dragover', function( event ) {
				event.preventDefault();
			} );

			unselect_grid.grid.parent().parent().unbind( 'drop' ).bind( 'drop', function( event ) {

				event.preventDefault();
				if ( event.stopPropagation ) {
					event.stopPropagation(); // stops the browser from redirecting.
				}
				$( '.drag-holder-table' ).remove();
				//drag from left to right
				if ( event.originalEvent.dataTransfer.getData( 'Text' ) === 'select_grid' ) {
					var grid_selected_id_array = select_grid.grid.jqGrid( 'getGridParam', 'selarrrow' );
					var grid_selected_length = grid_selected_id_array.length;

					if ( grid_selected_length > 0 ) {
						a_dropdown_this.moveItems( false, grid_selected_id_array );
					}
				}
			} );

			trs.unbind( 'dragend' ).bind( 'dragend', function( event ) {
				$( '.drag-holder-table' ).remove();
			} );

			unselect_grid.grid.parent().parent().unbind( 'dragend' ).bind( 'dragend', function( event ) {
				$( '.drag-holder-table' ).remove();
			} );

		};

		//Start Drag
		this.setSelectGridDragAble = function() {

			var highlight_Rows = null;
			var $$this = this;

			var trs = select_grid.grid.find( 'tr.ui-widget-content' ).attr( 'draggable', 'true' );

			trs.attr( 'draggable', true );

			trs.unbind( 'dragstart' ).bind( 'dragstart', function( event ) {
				var target = $( event.target );
				var container = $( '<table class=\'drag-holder-table\' from=\'select_grid\'></table>' );
				highlight_Rows = select_grid.grid.find( 'tr.ui-state-highlight' );
				var cloneRows = [];
				var len = highlight_Rows.length;

				if ( len === 0 ) {
					len = 1;
					select_grid.grid.jqGrid( 'setSelection', target.attr( 'id' ), false );
				} else if ( !target.hasClass( 'ui-state-highlight' ) ) {
					selectAllInGrid( select_grid, true );
					select_grid.grid.jqGrid( 'setSelection', target.attr( 'id' ), false );
					len = 1;
				}

				if ( len === 1 ) {
					highlight_Rows = select_grid.grid.find( 'tr.ui-state-highlight' );

					var clone_row = $( highlight_Rows[0] ).clone();

					clone_row.children().eq( 0 ).remove();
					clone_row.find( 'td' ).css( 'padding-right', 10 );
					clone_row.find( 'td' ).css( 'padding-left', 10 );

					container.append( clone_row );
				} else {
					container.append( len +' '+ $.i18n._('item(s) selected') );
				}

				$( '.drag-holder-table' ).remove();

				$( 'body' ).append( container );

				event.originalEvent.dataTransfer.setData( 'Text', 'select_grid' );//JUST ELEMENT references is ok here NO ID

				if ( event.originalEvent.dataTransfer.setDragImage ) {
					event.originalEvent.dataTransfer.setDragImage( container[0], -10, 0 );
				}
				return true;

			} );

			select_grid.grid.parent().parent().unbind( 'dragover' ).bind( 'dragover', function( event ) {
				event.preventDefault();
			} );

			select_grid.grid.parent().parent().unbind( 'drop' ).bind( 'drop', function( event ) {
				event.preventDefault();
				if ( event.stopPropagation ) {
					event.stopPropagation(); // stops the browser from redirecting.
				}
				$( '.drag-holder-table' ).remove();
				//drag from left to right
				if ( event.originalEvent.dataTransfer.getData( 'Text' ) === 'un_select_grid' ) {
					if ( !tree_mode ) {
						var grid_selected_id_array = unselect_grid.grid.jqGrid( 'getGridParam', 'selarrrow' );
						var grid_selected_length = grid_selected_id_array.length;
						if ( grid_selected_length > 0 ) {
							a_dropdown_this.moveItems( true, grid_selected_id_array );
						}
					} else {
						var selectRow = unselect_grid.grid.jqGrid( 'getGridParam', 'selrow' );
						a_dropdown_this.moveItems( true, [selectRow] );
					}

					return;
				}

			} );

			//when drag item to the header row, put them as first row
			var parent_grid_container = select_grid.grid.parent().parent().parent();
			parent_grid_container = parent_grid_container.find( '.ui-jqgrid-labels' );

			parent_grid_container.unbind( 'dragover' ).bind( 'dragover', function( event ) {
				event.preventDefault();
				$( this ).addClass( 'drag-over-bottom' );
			} );

			parent_grid_container.unbind( 'dragleave' ).bind( 'dragleave', function( event ) {
				$( this ).removeClass( 'drag-over-bottom' );

			} );

			trs.unbind( 'dragend' ).bind( 'dragend', function( event ) {
				$( '.drag-holder-table' ).remove();
			} );

			select_grid.grid.parent().parent().unbind( 'dragend' ).bind( 'dragend', function( event ) {
				$( '.drag-holder-table' ).remove();
			} );

			parent_grid_container.unbind( 'dragend' ).bind( 'dragend', function( event ) {
				$( '.drag-holder-table' ).remove();
			} );

			//when dropping on th/divs at top of grid.
			parent_grid_container.unbind( 'drop' ).bind( 'drop', function( event ) {
				event.preventDefault();
				if ( event.stopPropagation ) {
					event.stopPropagation(); // stops the browser from redirecting.
				}

				$( '.drag-holder-table' ).remove();
				$( this ).removeClass( 'drag-over-bottom' );
				if ( event.originalEvent.dataTransfer.getData( 'Text' ) === 'select_grid' ) {

					var firstTr = select_grid.grid.find( 'tr.ui-widget-content' )[0];
					if ( !firstTr ) {
						return;
					}
					var rows = select_grid.grid.find( 'tr.ui-state-highlight' );

					var len = rows.length;

					for ( var i = len - 1; i >= 0; i-- ) {

						var value = rows[i];
						var row = $( value );

						var target_row_index = 0;
						var select_items = a_dropdown_this.getSelectItems();
						var drag_item_index = value.rowIndex - 1;

						select_items.splice( target_row_index, 0, select_items.splice( drag_item_index, 1 )[0] );

						$( row ).insertAfter( firstTr ); // insert after sizerow.
					}

					var scroll_position = select_grid.grid.parents( '.ui-jqgrid-bdiv' ).scrollTop();
					isChanged = true;
					select_grid.grid.trigger( 'reloadGrid' );

					$$this.setSelectGridDragAble();

					rows = select_grid.grid.find( 'tr.ui-widget-content' );

					$.each( highlight_Rows, function( index, value ) {

						var item = value;
						var itemLabel = $( item ).find( 'td' )[1].innerHTML;

						$.each( rows, function( index1, value1 ) {
							var row = value1;

							var rowLabel = $( row ).find( 'td' )[1].innerHTML;
							if ( itemLabel === rowLabel ) {
								select_grid.grid.jqGrid( 'setSelection', $( row ).attr( 'id' ), false );
								return false;
							}
						} );

					} );

					select_grid.grid.parents( '.ui-jqgrid-bdiv' ).scrollTop( scroll_position );
				} else if ( event.originalEvent.dataTransfer.getData( 'Text' ) === 'un_select_grid' ) {
					target_row_index = -1;
					var grid_selected_id_array = unselect_grid.grid.jqGrid( 'getGridParam', 'selarrrow' );
					var grid_selected_length = grid_selected_id_array.length;
					if ( grid_selected_length > 0 ) {
						a_dropdown_this.moveItems( true, grid_selected_id_array, target_row_index );
					}
				}
				a_dropdown_this.trigger( 'formItemChange', [a_dropdown_this] );

			} );

			trs.unbind( 'dragover' ).bind( 'dragover', function( event ) {
				$( this ).addClass( 'drag-over-bottom' );
			} );

			trs.unbind( 'dragleave' ).bind( 'dragleave', function( event ) {
				$( this ).removeClass( 'drag-over-bottom' );
			} );

			trs.unbind( 'drop' ).bind( 'drop', function( event ) {

				event.preventDefault();
				if ( event.stopPropagation ) {
					event.stopPropagation(); // stops the browser from redirecting.
				}

				$( this ).removeClass( 'drag-over-bottom' );

				var $this = this;
				// Dont do drag to order
				if ( event.originalEvent.dataTransfer.getData( 'Text' ) === 'select_grid' ) {
					var rows = select_grid.grid.find( 'tr.ui-state-highlight' );

					var len = rows.length;

					for ( var i = len - 1; i >= 0; i-- ) {

						var value = rows[i];
						var row = $( value );

						if ( value === this ) {
							continue;
						}

						var target_row_index = $this.rowIndex - 1;
						var select_items = a_dropdown_this.getSelectItems();
						var drag_item_index = value.rowIndex - 1;

						if ( target_row_index >= drag_item_index ) {
							select_items.splice( target_row_index, 0, select_items.splice( drag_item_index, 1 )[0] );
						} else {
							select_items.splice( target_row_index + 1, 0, select_items.splice( drag_item_index, 1 )[0] );
						}

						$( row ).insertAfter( $this );
					}
					isChanged = true;
					var scroll_position = select_grid.grid.closest( '.ui-jqgrid-bdiv' ).scrollTop();
					select_grid.grid.trigger( 'reloadGrid' );

					$$this.setSelectGridDragAble();

					rows = select_grid.grid.find( 'tr.ui-widget-content' );

					$.each( highlight_Rows, function( index, value ) {

						var item = value;
						var itemLabel = $( item ).find( 'td' )[1].innerHTML;

						$.each( rows, function( index1, value1 ) {
							row = value1;

							var rowLabel = $( row ).find( 'td' )[1].innerHTML;
							if ( itemLabel === rowLabel ) {
								select_grid.grid.jqGrid( 'setSelection', $( row ).attr( 'id' ), false );
								return false;
							}
						} );

					} );

					select_grid.grid.closest( '.ui-jqgrid-bdiv' ).scrollTop( scroll_position );
				} else if ( event.originalEvent.dataTransfer.getData( 'Text' ) === 'un_select_grid' ) {

					if ( !tree_mode ) {
						target_row_index = $this.rowIndex - 1;
						var grid_selected_id_array = unselect_grid.grid.jqGrid( 'getGridParam', 'selarrrow' );
						var grid_selected_length = grid_selected_id_array.length;
						if ( grid_selected_length > 0 ) {
							a_dropdown_this.moveItems( true, grid_selected_id_array, target_row_index, $( $this ).attr( 'id' ) );
						}
					} else {
						var selectRow = unselect_grid.grid.jqGrid( 'getGridParam', 'selrow' );
						a_dropdown_this.moveItems( true, [selectRow] );
					}

				}

				//Need to dirty the form when changing order of selected items
				a_dropdown_this.trigger( 'formItemChange', [a_dropdown_this] );
			} );
		};

		this.setTotalDisplaySpan = function() {
			var grid_selected_id_array;

			if ( allow_multiple_selection ) {
				grid_selected_id_array = unselect_grid.grid.jqGrid( 'getGridParam', 'selarrrow' );
			} else {
				grid_selected_id_array = unselect_grid.grid.jqGrid( 'getGridParam', 'selrow' ) ? [unselect_grid.grid.jqGrid( 'getGridParam', 'selrow' )] : [];
			}

			var grid_selected_length = 0;
			//Uncaught TypeError: Cannot read property 'length' of undefined
			if ( grid_selected_id_array ) {
				grid_selected_length = grid_selected_id_array.length;
			}

			var totalRows = 0;
			var start = 0;
			var end = 0;
			var unselect_grid_length = ( ( unselect_grid && Global.isArray( unselect_grid.getGridParam( 'data' ) ) ) ? unselect_grid.getGridParam( 'data' ).length : 0 );

			// CLICK TO SHOW MORE MODE OR SHOW ALL
			if ( LocalCacheData.paging_type === 0 || !pager_data || (pager_data && pager_data.last_page_number < 0) ) {
				if ( pager_data ) {
					totalRows = pager_data.total_rows;
					start = 1;
					end = unselect_grid_length;
				} else {
					totalRows = unselect_grid_length;
					start = 1;
					end = unselect_grid_length;
				}
			} else {
				if ( pager_data ) {
					totalRows = pager_data.total_rows;
					start = 0;
					end = 0;

					if ( pager_data.last_page_number > 1 ) {
						if ( !pager_data.is_last_page ) {
							start = ( pager_data.current_page - 1 ) * pager_data.rows_per_page + 1;
							end = start + pager_data.rows_per_page - 1;
						} else {
							start = totalRows - unselect_grid_length + 1;
							end = totalRows;
						}

					} else {
						start = 1;
						end = totalRows;
					}

				} else {
					totalRows = 0;
					start = 0;
					end = 0;
				}
			}

			var totalInfo;
			if ( allow_multiple_selection ) {

				var selected_count = this.getSelectItems().length;

				var remain_count = unselect_grid_length;

				if ( remain_count === 0 ) {
					end = 0;
					start = 0;
				} else {
					end = (remain_count + start);
					if ( start === 1 ) {
						end = end - 1;

					}
				}

				if ( totalRows ) {

					//If there is manually added item
					if ( end > totalRows ) {
						totalRows = end;
					}

					totalInfo = start + ' - ' + end + ' ' + $.i18n._( 'of' ) + ' ' + totalRows + ' ' + $.i18n._( 'total' ) + '. ';

				} else {
					totalInfo = start + ' - ' + end + '.';
				}

				total_display_span.text( $.i18n._( 'Displaying' ) + ' ' + totalInfo + ' ' + $.i18n._( 'Selected' ) + ': ' + selected_count );

			}
			else {

				if ( end === 0 ) {
					start = 0;
				}
				if ( totalRows ) {

					//If there is manually added item
					if ( end > totalRows ) {
						totalRows = end;
					}

					totalInfo = start + ' - ' + end + ' ' + $.i18n._( 'of' ) + ' ' + totalRows + ' ' + $.i18n._( 'total' ) + '. ';
				} else {
					totalInfo = start + ' - ' + end + '.';
				}

				total_display_span.text( $.i18n._( 'Displaying' ) + ' ' + totalInfo );
			}

		};

		this.onUnSelectGridSelectRow = function() {
			this.setTotalDisplaySpan();
		};

		this.onUnSelectGridDoubleClick = function() {
			if ( allow_multiple_selection ) {
				if ( !tree_mode ) {
					var grid_selected_id_array = unselect_grid.grid.jqGrid( 'getGridParam', 'selarrrow' );
					var grid_selected_length = grid_selected_id_array.length;
					if ( grid_selected_length > 0 ) {
						a_dropdown_this.moveItems( true, grid_selected_id_array );
					}
				} else {
					var selectRow = unselect_grid.grid.jqGrid( 'getGridParam', 'selrow' );
					a_dropdown_this.moveItems( true, [selectRow] );
				}
			}
		};

		this.onSelectGridDoubleClick = function() {
			var grid_selected_id_array = select_grid.grid.jqGrid( 'getGridParam', 'selarrrow' );
			var grid_selected_length = grid_selected_id_array.length;

			if ( grid_selected_length > 0 ) {
				a_dropdown_this.moveItems( false, grid_selected_id_array );
			}

		};

		//Move items between 2 grids
		this.moveItems = function( left_to_right, array, index, target_row_id ) {
			this.removeNoResultCover('unselect_grid');
			this.removeNoResultCover('select_grid');
			var added_items = [];
			var removed_items = [];
			isChanged = true;

			var editable_unselect_items = $(unselect_grid.grid).find('.editable');
			for( var i = 0; i < editable_unselect_items.length; i++) {
				$el = $( editable_unselect_items[i] );
				var tr_id = $el.parents( 'tr' ).prop( 'id' );
				unselect_grid.grid.saveRow( tr_id );
			}

			var editable_select_items = $(select_grid.grid).find('.editable');
			for( var i = 0; i < editable_select_items.length; i++) {
				$el = $( editable_select_items[i] );
				var tr_id = $el.parents( 'tr' ).prop( 'id' );
				select_grid.grid.saveRow( tr_id );
			}

			var moved_items_array = array.slice();

			if ( left_to_right ) {
				var source_grid = unselect_grid;
				var target_grid = select_grid;
				var source_data = unselect_grid.getGridParam( 'data' );
				var target_data = select_grid.getGridParam( 'data' );
			} else {
				source_grid = select_grid;
				target_grid = unselect_grid;
				source_data = select_grid.getGridParam( 'data' );
				target_data = unselect_grid.getGridParam( 'data' );
			}

			if ( !Global.isArray( target_data ) ) {
				target_data = [];
			}

			if ( !Global.isSet( source_data[0].id ) ) {

			} else {
				var last_item_index = null; //for drag order fixing when drag to empty space

				if ( !Global.isSet( index ) ) {
					array = array.reverse();
				}

				for ( var i = array.length - 1; i >= 0; i-- ) {
					var selected_item_id = array[i];

					for ( var j = 0; j < source_data.length; j++ ) {
						var from_all_columns_item = source_data[j];
						if ( from_all_columns_item.id == selected_item_id ) {  //html number is string, compare string numbers with number number
							var select_item = from_all_columns_item;

							if ( !tree_mode || !left_to_right ) { //Don't remove item from list if tree mode
								source_grid.grid.jqGrid( 'delRowData', selected_item_id );
								i = i + 1;
							}

							if ( !tree_mode || left_to_right ) {
								if ( tree_mode ) {
									//Make sure only one item can be add to right when tree mode
									var target_data_len = target_data.length;
									var find = false;
									for ( var y = 0; y < target_data_len; y++ ) {
										var existed_item = target_data[y];
										if ( existed_item[key] === select_item[key] ) {
											find = true;
											break;
										}
									}

									if ( !find ) {

										if ( index >= 0 ) {
											target_grid.grid.addRowData( selected_item_id, select_item, 'after', target_row_id );
											target_data.splice( target_data.length - 1, 1 );
											target_data.splice( index + 1, 0, select_item );

										} else if ( index === -1 ) { // add to first row
											target_grid.grid.addRowData( selected_item_id, select_item, 'first' );
											target_data.splice( target_data.length - 1, 1 );
											target_data.unshift( select_item );
										} else {
											target_grid.grid.addRowData( selected_item_id, select_item );
											target_data[target_data.length - 1] = select_item; //need this since we need full data, addRowData only keep data which shown on UI
										}

									}
								} else {

									if ( index >= 0 ) {
										target_grid.grid.addRowData( selected_item_id, select_item, 'after', target_row_id );
										target_data.splice( target_data.length - 1, 1 );
										target_data.splice( index + 1, 0, select_item );

									} else if ( index === -1 ) { // add to first row
										target_grid.grid.addRowData( selected_item_id, select_item, 'first' );
										target_data.splice( target_data.length - 1, 1 );
										target_data.unshift( select_item );
									} else {

										target_grid.grid.addRowData( selected_item_id, select_item );
										target_data[target_data.length - 1] = select_item; //

									}

								}

							}
							break;
						}
					}
				}
			}

			if ( !tree_mode ) {
				target_grid.grid.trigger( 'reloadGrid' );
				// if ( left_to_right ) {
				// 	a_dropdown_this.resizeSelectSearchInputs();
				// }
			} else {
				if ( left_to_right ) {
					a_dropdown_this.reSetUnSelectGridTreeData( source_data );
				} else {
					a_dropdown_this.reSetUnSelectGridTreeData( target_data );
				}
			}

			a_dropdown_this.setSelectGridDragAble();
			a_dropdown_this.setUnSelectGridDragAble();
			a_dropdown_this.updateRealSelectItemsIfNecessary( left_to_right, moved_items_array );
			a_dropdown_this.setTotalDisplaySpan();

			if ( !parent_a_combo_box ) {
				a_dropdown_this.trigger( 'formItemChange', [a_dropdown_this] );
			}
		};

		this.updateRealSelectItemsIfNecessary = function( left_to_right, moved_items ) {
			if ( !real_selected_items || real_selected_items.length == 0 ) {
				return;
			}

			if ( left_to_right ) {
				var current_items_in_selected_grid = select_grid.getGridParam( 'data' );
				$.each( moved_items, function( index, value ) {
					$.each( current_items_in_selected_grid, function( index1, value1 ) {
						if ( value1[key] == value ) { //use == to match '' or number of id
							real_selected_items.push( value1 );
							return false;
						}
					} );
				} );
			} else {
				$.each( moved_items, function( index, value ) {
					$.each( real_selected_items, function( index1, value1 ) {
						if ( value1[key] == value ) { //use == to match '' or number of id
							real_selected_items.splice( index1, 1 );
							return false;
						}
					} );
				} );
			}

		};

		this.setHeight = function( height ) {
			if ( max_height && max_height < height ) {
				height = max_height;
			}
			unselect_grid.grid.setGridHeight( height );
			if ( allow_multiple_selection ) {
				select_grid.grid.setGridHeight( height );
			}
		};

		var initColumnSettingsBtn = function() {
			var edit_icon_div = $( a_dropdown_this ).find( '.edit-columnIcon-div' );
			var edit_icon = $( a_dropdown_this ).find( '.edit_column_icon' );
			if ( !display_column_settings || tree_mode ) {
				edit_icon_div.hide();
				return;
			} else {
				edit_icon_div.show();
			}

			edit_icon_div.css( 'display', 'inline-block' );
			edit_icon.attr( 'src', Global.getRealImagePath( 'images/edit.png' ) );

			//OPen Column editor
			edit_icon_div.click( function( e ) {
				e.preventDefault();
				e.stopPropagation();

				if ( !parent_a_combo_box.getEnabled() ) {
					return;
				}
				api.getOptions( column_option_key, {
					onResult: function( columns_result ) {

						column_editor = Global.loadWidget( 'global/widgets/column_editor/ColumnEditor.html' );
						column_editor = $( column_editor );
						column_editor.find( '#save_btn' ).text( $.i18n._( 'Save and Close' ) );
						column_editor.find( '#close_btn' ).text( $.i18n._( 'Close' ) );
						column_editor.find( '.rows-per-page' ).text( $.i18n._( 'Rows Per Page' ) + ':' );
						column_editor.find( '.choose-layout' ).text( $.i18n._( 'Choose Layout' ) + ':' );

						column_editor = column_editor.ColumnEditor( { parent_awesome_box: parent_a_combo_box } );

						var columns_result_data = columns_result.getResult();
						parent_a_combo_box.setAllColumns( Global.buildColumnArray( columns_result_data ) );
						parent_a_combo_box.setDisplayColumnsForEditor( parent_a_combo_box.buildDisplayColumnsForEditor() );

						//Open Column Editor;
						column_editor.show();

					}
				} );
			} );
		};

		var setLabels = function() {
			var unselected_items_label = a_dropdown_this.find( '#unSelectedItemsLabel' );
			var un_deselect_all_btn = a_dropdown_this.find( '#unDeselectAllBtn' );
			var unselect_all_btn = a_dropdown_this.find( '#unselect_all_btn' );
			var un_clear_btn = a_dropdown_this.find( '#un_clear_btn' );
			var show_all_check_box_label = a_dropdown_this.find( '#show_all_check_box_label' );

			if ( allow_multiple_selection ) {
				unselected_items_label.text( $.i18n._( 'UNSELECTED ITEMS' ) );
			} else {
				unselected_items_label.text( $.i18n._( 'PLEASE SELECT ITEM' ) );
			}
			un_deselect_all_btn.text( $.i18n._( 'Deselect All' ) );
			unselect_all_btn.text( $.i18n._( 'Select All' ) );
			un_clear_btn.text( $.i18n._( 'Move All' ) );
			show_all_check_box_label.text( $.i18n._( 'Show All' ) );

			var selectedItemsLabel = a_dropdown_this.find( '#selectedItemsLabel' );
			var delete_all_btn = a_dropdown_this.find( '#delete_all_btn' );
			var select_all_btn = a_dropdown_this.find( '#select_all_btn' );
			var clear_btn = a_dropdown_this.find( '#clear_btn' );

			selectedItemsLabel.text( $.i18n._( 'SELECTED ITEMS' ) );
			delete_all_btn.text( $.i18n._( 'Deselect All' ) );
			select_all_btn.text( $.i18n._( 'Select All' ) );
			clear_btn.text( $.i18n._( 'Move All' ) );

		};

		this.selectNextItem = function( e ) {
			var next_index;
			var target_grid;
			var next_select_item;
			if ( e.keyCode === 39 ) { //right
				if ( allow_multiple_selection && !$( e.target ).hasClass( 'search-input' ) ) {
					e.preventDefault();
					a_dropdown_this.onUnSelectGridDoubleClick();
				}
			} else if ( e.keyCode === 37 ) { //left
				if ( allow_multiple_selection && !$( e.target ).hasClass( 'search-input' ) ) {
					e.preventDefault();
					a_dropdown_this.onSelectGridDoubleClick();
				}
			} else {
				if ( quick_search_timer ) {
					clearTimeout( quick_search_timer );
				}
				var focus_target = $( ':focus' );
				if ( focus_target.length > 0 && $( focus_target[0] ).hasClass( 'search-input' ) ) {
					return;
				}
				quick_search_timer = setTimeout( function() {
					quick_search_typed_keys = '';
				}, 200 );
				e.preventDefault();
				quick_search_typed_keys = quick_search_typed_keys + Global.KEYCODES[e.which];
				if ( allow_multiple_selection || tree_mode ) {
					if ( quick_search_typed_keys ) {
						target_grid = a_dropdown_this.getFocusInSeletGrid() ? a_dropdown_this.getSelectGrid() : a_dropdown_this.getUnSelectGrid();
						var search_index = quick_search_dic[quick_search_typed_keys] ? quick_search_dic[quick_search_typed_keys] : 0;
						var tds = $( target_grid.grid.find( 'tr' ).find( 'td:eq(1)' ).filter( function() {
							return $.text( [this] ).toLowerCase().indexOf( quick_search_typed_keys ) == 0;
						} ) );
						var td;
						if ( search_index < 0 || search_index > tds.length ) {
							search_index = 0;
						}

						td = $( tds[search_index] );
						a_dropdown_this.unSelectAll( target_grid.grid, true );

						next_index = td.parent().index() - 1;

						var next_select_item = false;
						var grid_data = target_grid.getData();
						for ( var z = 0; z < grid_data.length; z++ ) {
							if ( grid_data[z].id == td.parents('tr').attr('id') ) {
								next_select_item = grid_data[z];
								break;
							}
						}

						select_item = next_select_item;
						a_dropdown_this.setSelectItem( next_select_item, target_grid );
						quick_search_dic = {};
						quick_search_dic[quick_search_typed_keys] = search_index + 1;
					}

				} else {
					if ( quick_search_typed_keys ) {
						search_index = quick_search_dic[quick_search_typed_keys] ? quick_search_dic[quick_search_typed_keys] : 0;
						tds = $( a_dropdown_this.getUnSelectGrid().find( 'tr' ).find( 'td:first' ).filter( function() {
							return $.text( [this] ).toLowerCase().indexOf( quick_search_typed_keys ) == 0;
						} ) );
						if ( search_index > 0 && search_index < tds.length ) {

						} else {
							search_index = 0;
						}

						td = $( tds[search_index] );

						next_index = td.parent().index() - 1;
						next_select_item = this.getItemByIndex( next_index );
						select_item = next_select_item;
						a_dropdown_this.setSelectItem( next_select_item );

						quick_search_dic = {};
						quick_search_dic[quick_search_typed_keys] = search_index + 1;
					}
				}

			}

		};

		//For multiple items like .xxx could contains a few widgets.
		//#2353 removed $.each because it's slower
		for ( var i = 0; i < this.length; i++ ) {
			var obj = $( this[i] ); //#2353 - caching the lookup to speed this part up.
			var o = $.meta ? $.extend( {}, opts, obj.data() ) : opts;

			if ( o.default_height > 150 ) {
				default_height = o.default_height;
			}

			field = o.field;

			if ( o.search_input_filter ) {
				unselect_grid_search_map = o.search_input_filter;
			}

			if ( o.select_grid_search_input_filter ) {
				select_grid_search_map = o.select_grid_search_input_filter;
			}

			if ( o.default_sort_filter ) {
				unselect_grid_sort_map = o.default_sort_filter;
			}

			if ( o.default_select_grid_sort_filter ) {
				select_grid_sort_map = o.default_select_grid_sort_filter;
			}

			if ( o.auto_sort ) {
				auto_sort = o.auto_sort;
			}

			if ( Global.isSet( o.allow_multiple_selection ) ) {
				allow_multiple_selection = o.allow_multiple_selection;
			}

			if ( o.column_option_key ) {
				column_option_key = o.column_option_key;
			}

			if ( o.api ) {
				api = o.api;
			}

			if ( o.hasOwnProperty( 'display_column_settings' ) ) {
				display_column_settings = o.display_column_settings;
			}

			if ( o.resize_grids ) {
				resize_grids = o.resize_grids;
			} else {
				resize_grids = false;
			}

			//Init paging widget

			left_buttons_div = obj.find( '.left-buttons-div' );
			right_buttons_div = obj.find( '.right-buttons-div' );

			start = obj.find( '.start' );
			last = obj.find( '.last' );
			next = obj.find( '.next' );
			end = obj.find( '.end' );

			start.text( $.i18n._( 'Start' ) );
			last.text( $.i18n._( 'Previous' ) );

			next.text( $.i18n._( 'Next' ) );
			end.text( $.i18n._( 'End' ) );

			start.click( function() {
				if ( left_buttons_enable ) {
					parent_a_combo_box.onADropDownSearch( 'unselect_grid', 'start' );
				}
			} );

			last.click( function() {
				if ( left_buttons_enable ) {
					parent_a_combo_box.onADropDownSearch( 'unselect_grid', 'last' );
				}
			} );

			next.click( function() {
				if ( right_buttons_enable ) {
					parent_a_combo_box.onADropDownSearch( 'unselect_grid', 'next' );
				}
			} );

			end.click( function() {
				if ( right_buttons_enable ) {
					parent_a_combo_box.onADropDownSearch( 'unselect_grid', 'end' );
				}
			} );

			left_buttons_div.css( 'display', 'none' );
			right_buttons_div.css( 'display', 'none' );

			if ( LocalCacheData.paging_type !== 10 ) {
				//Click to show more button below the last row
				paging_widget = Global.loadWidgetByName( WidgetNamesDic.PAGING );
			}

			//Display 'Displaying XX of xx, Selected: xxx'
			total_display_span = obj.find( '.total-number-span' );

			if ( allow_multiple_selection ) {
				total_display_span.text( $.i18n._( 'Displaying' ) + ' 0 ' + $.i18n._( 'of' ) + ' 0 ' + $.i18n._( 'total' ) );
			} else {
				total_display_span.text( $.i18n._( 'Displaying' ) + ' 0 - 0 ' + $.i18n._( 'of' ) + ' 0 ' + $.i18n._( 'total' ) + '. ' + $.i18n._( 'Selected' ) + ': 0' );
			}

			if ( !allow_multiple_selection ) {
				var unselect_grd_border_div = obj.find( '.unselect-grid-border-div' );
				unselect_grd_border_div.addClass( 'single-mode-border' );

			}

			var unselect_grd_div = obj.find( '.unselect-grid-div' );

			unselect_grd_div.bind( 'click', function() {
				focus_in_select_grid = false;

			} );

			var select_grid_div = obj.find( '.select-grid-div' );
			var left_and_right_div = obj.find( '.left-and-right-div' );
			var unselected_items_label = obj.find( '#unselected_items_label' );
			var selected_items_label = obj.find( '#selected_items_label' );

			select_grid_div.bind( 'click', function() {
				focus_in_select_grid = true;

			} );

			if ( !allow_multiple_selection ) {
				select_grid_div.css( 'display', 'none' );
				left_and_right_div.css( 'display', 'none' );
				unselected_items_label.text( $.i18n._( 'SELECT AN ITEM' ) );
			} else {
				select_grid_div.css( 'display', 'inline-block' );
				left_and_right_div.css( 'display', 'inline-block' );
				unselected_items_label.text( $.i18n._( 'UNSELECTED ITEMS' ) );
				selected_items_label.text( $.i18n._( 'SELECTED ITEMS' ) );
			}

			if ( Global.isSet( o.allow_drag_to_order ) ) {
				allow_drag_to_order = o.allow_drag_to_order;
			}

			//Set UI visibility
			if ( o.display_show_all === true ) {

				var show_all_check_box = obj.find( '#show_all_check_box' );

				show_all_check_box.css( 'display', 'normal' );
				obj.find( '#show_all_check_box_label' ).css( 'display', 'normal' );

				if ( o.show_all === true ) {
					show_all_check_box.prop( 'checked', 'true' );
				} else {
					show_all_check_box.prop( 'checked', undefined );
				}

				obj.find( '#show_all_check_box' ).click( function() {
					var show_all_checked = false;
					if ( show_all_check_box.prop( 'checked' ) === 'checked' || show_all_check_box[0].checked === true ) {
						show_all_checked = true;
					}

					parent_a_combo_box.onShowAll( show_all_checked );

				} );

			} else {
				obj.find( '#show_all_check_box' ).css( 'display', 'none' );
				obj.find( '#show_all_check_box_label' ).css( 'display', 'none' );

			}

			if ( o.comboBox ) {
				parent_a_combo_box = o.comboBox;

				if ( parent_a_combo_box.allow_multiple_selection ) {
					obj.css( 'min-Width', 958 );
				} else {

				}
			} else {
				obj.css( 'min-Width', 958 );
			}

			id = o.id;

			obj.attr( 'id', o.id + 'ADropDown' );

			if ( o.max_height ) {
				max_height = o.max_height;
			}

			if ( o.static_height ) {
				static_height = o.static_height;
				max_height = o.static_height;
			}

			if ( o.key ) {
				key = o.key;
			}

			if ( o.show_search_inputs ) {
				show_search_inputs = o.show_search_inputs;
			}

			if ( o.tree_mode ) {
				tree_mode = o.tree_mode;
			}

			if ( o.on_tree_grid_row_select ) {
				on_tree_grid_row_select = o.on_tree_grid_row_select;
			}

			//All options set, NOW we init the label and column settings button
			setLabels();
			initColumnSettingsBtn();

			unselect_grid = obj.find( '.unselect-grid' ); //Must add id for them

			unselect_grid.attr( 'id', 'unselect_grid' + '_' + id );
			var unselect_grid_data = {};
			if ( !tree_mode ) {
				unselect_grid_data = {
					width: 440,
					//maxHeight: default_height,
					onCellSelect: a_dropdown_this.onUnSelectGridSelectRow,
					ondblClickRow: a_dropdown_this.onUnSelectGridDoubleClick,
					multiselect: allow_multiple_selection,
					winMultiSelect: allow_multiple_selection,
					multiboxonly: allow_multiple_selection
				};
			} else {
				unselect_grid_data = {

					width: 440,
					//maxHeight: default_height,
					sortname: 'id',
					onCellSelect: a_dropdown_this.onUnSelectGridSelectRow,
					ondblClickRow: a_dropdown_this.onUnSelectGridDoubleClick,
					multiselect: allow_multiple_selection,
					winMultiSelect: allow_multiple_selection,
					gridview: true,
					treeGrid: true,
					treeGridModel: 'adjacency',
					treedatatype: 'local',
					ExpandColumn: 'name',
					jsonReader: {
						repeatitems: false,
						root: function( obj ) {
							return obj;
						},
						page: function( obj ) {
							return 1;
						},
						total: function( obj ) {
							return 1;
						},
						records: function( obj ) {
							return obj.length;
						}
					}
				};
			}
			if ( max_height ) {
				unselect_grid_data.maxHeight = max_height;
			}

			if ( static_height ) {
				unselect_grid_data.static_height = static_height;
			}

			unselect_grid = new TTGrid( 'unselect_grid' + '_' + id, unselect_grid_data, [] );

			select_grid = obj.find( '.select-grid' );

			select_grid.attr( 'id', 'select_grid' + '_' + id );

			select_grid = new TTGrid( 'select_grid' + '_' + id, {
				container_selector: '.select-grid-div',
				altRows: true,
				data: [],
				datatype: 'local',
				sortable: false,
				width: 440,
				//maxHeight: default_height,
				rowNum: 10000,
				colNames: [],
				colModel: [],
				ondblClickRow: a_dropdown_this.onSelectGridDoubleClick,
				multiselect: allow_multiple_selection,
				winMultiSelect: allow_multiple_selection,
				multiboxonly: true,
				viewrecords: true,
				onResizeGrid: resize_grids,
				maxHeight: max_height,
				static_height: static_height ? static_height : null

			}, [] );

			var right_arrow = obj.find( '.a-grid-right-arrow' );
			var left_arrow = obj.find( '.a-grid-left-arrow' );

			right_arrow.attr( 'src', Global.getRealImagePath( 'css/global/widgets/awesomebox/images/nav_right.png' ) );
			left_arrow.attr( 'src', Global.getRealImagePath( 'css/global/widgets/awesomebox/images/nav_left.png' ) );

			right_arrow.click( function() {

				if ( !tree_mode ) {
					var grid_selected_id_array = unselect_grid.grid.jqGrid( 'getGridParam', 'selarrrow' );
					var grid_selected_length = grid_selected_id_array.length;
					if ( grid_selected_length > 0 ) {
						a_dropdown_this.moveItems( true, grid_selected_id_array );
					}
				} else {
					var selectRow = unselect_grid.grid.jqGrid( 'getGridParam', 'selrow' );
					a_dropdown_this.moveItems( true, [selectRow] );
				}

			} );

			left_arrow.click( function() {
				var grid_selected_id_array = select_grid.grid.jqGrid( 'getGridParam', 'selarrrow' );
				var grid_selected_length = grid_selected_id_array.length;

				if ( grid_selected_length > 0 ) {
					a_dropdown_this.moveItems( false, grid_selected_id_array );
				}

			} );

			//Set Action  Buttons

			//UnSelect grid
			var unselect_all_btn = obj.find( '#unselect_all_btn' );

			unselect_all_btn.click( function() {
				selectAllInGrid( unselect_grid );
			} );

			var un_deselect_all_Btn = obj.find( '#unDeselectAllBtn' );

			un_deselect_all_Btn.click( function() {
				selectAllInGrid( unselect_grid, true );

			} );

			var un_clear_btn = obj.find( '#un_clear_btn' );

			un_clear_btn.click( function() {
				cleanAllInGrid( unselect_grid, true );

			} );

			if ( tree_mode || !allow_multiple_selection ) {
				unselect_all_btn.css( 'display', 'none' );
				un_deselect_all_Btn.css( 'display', 'none' );
				un_clear_btn.css( 'display', 'none' );
			} else {
				unselect_all_btn.css( 'display', 'inline-block' );
				un_deselect_all_Btn.css( 'display', 'inline-block' );
				un_clear_btn.css( 'display', 'inline-block' );
			}

			//Select Grid
			var select_all_btn = obj.find( '#select_all_btn' );

			select_all_btn.click( function() {
				selectAllInGrid( select_grid );

			} );

			var delete_all_btn = obj.find( '#delete_all_btn' );

			delete_all_btn.click( function() {
				selectAllInGrid( select_grid, true );

			} );

			var clear_btn = obj.find( '#clear_btn' );

			clear_btn.click( function() {
				cleanAllInGrid( select_grid );
			} );

			var select_grid_close_btn = obj.find( '#select_grid_close_btn' );
			var unselect_grid_close_btn = obj.find( '#unselect_grid_close_btn' );

			if ( allow_multiple_selection ) {
				unselect_grid_close_btn.css( 'display', 'none' );
			} else {
				unselect_grid_close_btn.css( 'display', 'inline-block' );
				unselect_grid_close_btn.click( function() {
					a_dropdown_this.trigger( 'close', [a_dropdown_this] );
				} );
			}
			if ( Global.isSet( o.display_close_btn ) && !o.display_close_btn ) {
				select_grid_close_btn.css( 'display', 'none' );
			} else {
				select_grid_close_btn.css( 'display', 'inline-block' );
				select_grid_close_btn.click( function() {
					a_dropdown_this.trigger( 'close', [a_dropdown_this] );
				} );
			}

			//Move all records from target grid to another
			function cleanAllInGrid( target, left_to_right ) {

				a_dropdown_this.removeNoResultCover('unselect_grid');
				a_dropdown_this.removeNoResultCover('select_grid');

				isChanged = true;
				var finalArray = [];
				if ( left_to_right ) {
					var source_grid = unselect_grid;
					var target_grid = select_grid;
					var source_data = unselect_grid.getData();
					var target_data = select_grid.getData();

					real_selected_items = source_data;
				} else {
					real_selected_items = [];
					source_grid = select_grid;
					target_grid = unselect_grid;
					source_data = select_grid.getData();
					target_data = unselect_grid.getData();
				}


				if ( tree_mode ) {
					source_grid.clearGridData();
					source_grid.grid.trigger( 'reloadGrid' );
					a_dropdown_this.setTotalDisplaySpan();
					a_dropdown_this.getUnSelectGrid().grid.find( 'tr' ).removeClass( 'selected-tree-cell' );
				} else {
					finalArray = target_data.concat( source_data );
					target_grid.setData( finalArray );
					source_grid.clearGridData();
					source_grid.grid.trigger( 'reloadGrid' );
					a_dropdown_this.setTotalDisplaySpan();
				}
				if ( !parent_a_combo_box ) {
					a_dropdown_this.trigger( 'formItemChange', [a_dropdown_this] );
				}

				a_dropdown_this.setSelectGridDragAble();
				a_dropdown_this.setUnSelectGridDragAble();
			}

		}

		return this;

	};

	$.fn.ADropDown.defaults = {};

})( jQuery );
