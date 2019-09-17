/**
 * @author: joshr@timetrex.com
 * @description wrapper class for jqgrid in timetrex.
 * Requires free-jqgrid 4.15.4+ and jquery 3.3.1+
 *
 * @param setup
 * @constructor
 */
TTGrid = function( table_id, setup, column_info_array ) {

	if ( !table_id || !setup || !column_info_array ) {
		Debug.Text( 'ERROR: constructor requires all 3 arguments', 'TTGrid.js', 'TTGrid', 'constructor', 10 );
		return;
	}

	if ( $( '#' + table_id )[0] == false ) {
		Debug.Text( 'ERROR: table_id not found in DOM', 'TTGrid.js', 'TTGrid', 'constructor', 10 );
		return;
	}

	var $this = this;
	var max_height = null;
	this.ui_id = table_id;


	this.grid = null;

	//Default grid settings.
	var default_setup = {
		container_selector: 'body',
		sub_grid_mode: false,
		altRows: true,
		data: [],
		datatype: 'local',
		sortable: false,
		height: $( '#' + table_id ).parents( '.view' ).height(),
		width: $( '#' + table_id ).parent().width(),
		rowNum: 10000,
		colNames: [],
		colModel: column_info_array,
		multiselect: true,
		multiselectWidth: 22,
		multiboxonly: true,
		viewrecords: true,
		autoencode: true,
		scrollOffset: 0,
		verticalResize: true, //when the grid resizes do we reisize it vertically? needed for timesheet and subgrid views.
		resizeGrid: true,
		winMultiSelect: true
		// resizeStop: function(width, index){
		// 	//$this.setGridColumnsWidth(width, index);
		// }
	};


	if ( setup.max_height === true ) {
		setup.max_height = max_height;
	}

	if ( setup ) {
		setup = $.extend( {}, default_setup, setup );
	} else {
		setup = default_setup;
	}

	this.setup = setup;

	$( '#' + table_id ).empty(); //should unbind all events bound to the grid.
	this.grid = $( '#' + table_id ).jqGrid( setup );

	LocalCacheData.resizeable_grids.push(this);

	if ( setup.winMultiSelect === true ) {
		this.grid.winMultiSelect();
	}

	this.getGridId = function() {
		return this.ui_id;
	};

	//turn off grid resize event (for schedule grids that need to be rebuilt on every resize)
	this.noResize = function(){
		this.setup.onResizeGrid = false;
	};

	/**
	 *
	 * @param data
	 */
	this.setData = function( data, clear_grid ) {
		if ( this.grid ) {
			//Clear grid by default.
			clear_grid = ( clear_grid == null ) ? true : clear_grid;
			if ( clear_grid == true ) {
				this.grid.clearGridData();
			}

			this.grid.setGridParam( { 'data': data } ).trigger( 'reloadGrid' );
			//this.setGridColumnsWidth();
		} else {
			throw('ERROR: Grid is not ready yet.');
		}
	};
	this.getData = function() {
		return this.getGridParam( 'data' );
	};

	this.getSetup = function() {
		return this.getGridParam( 'data' );
	};


	this.getHeight = function() {
		return $( this.grid ).height();
	};
	this.getWidth = function() {
		return $( this.grid ).width();
	};

	/**
	 *
	 * @param value
	 */
	this.deleteRow = function( value ) {
		this.grid.jqGrid( 'delRowData', value );
	};

	this.resetSelection = function() {
		this.grid.resetSelection();
	};

	this.setSelection = function( x1, y1, x2, y2, noScale ) {
		this.grid.setSelection( x1, y1, x2, y2, noScale );
	};

	this.getGridParam = function( parameter_name ) {
		return this.grid.getGridParam( parameter_name );
	};

	this.setGridParam = function( parameter_name, value ) {
		return this.grid.setGridParam( parameter_name, value );
	};

	this.clearGridData = function() {
		this.grid.jqGrid( 'clearGridData', true );
	};

	this.reloadGrid = function() {
		this.grid.trigger( 'reloadGrid' );
	};

	this.getRecordFromGridById = function( id ) {
		var data = this.grid.getGridParam( 'data' );
		var result = null;
		/* jshint ignore:start */
		//id could be string or number.
		$.each( data, function( index, value ) {

			if ( value['_id_'] == id ) {
				result = Global.clone( value );
				return false;
			}

		} );
		/* jshint ignore:end */

		if ( result ) {
			result.id = result['_id_'];
		}
		return result;

	};

	this.getGridWidth = function() {
		if ( this.grid ) {
			return this.grid.width();
		}
		return 0;
	};

	this.setGridWidth = function( w, inner_width ) {
		if ( this.grid ) {
			if ( inner_width > w ) {
				w = inner_width;
			}
			this.grid.setGridWidth( w );
		}
	};

	this.getGridHeight = function() {
		if ( this.grid ) {
			return this.grid.height();
		}
		return 0;
	};
	this.setGridHeight = function( h ) {
		if ( this.setup.static_height ) {
			h = this.setup.static_height;
		}

		if ( this.grid ) {
			this.grid.setGridHeight( h );
		}
	};

	this.setRowData = function( id, new_record ) {
		this.grid.setRowData( id, new_record );
	};

	this.getColumnModel = function() {
		return this.getGridParam( 'colModel' );
	};

	this.setColumnModel = function( val ) {
		this.getGridParam( 'colModel', val );
	};

	this.getSelection = function() {
		var tds = this.grid.find('td.ui-state-highlight');

		var selection = []
		for ( var x = 0; x < tds.length; x++ ){
			selection.push( {
				tr: $(tds[x]).parent('tr').index(),
				td: $(tds[x]).index()
			} );
		}
		return selection;
	},

	this.setTimesheetSelection = function( selection_obj ) {
		//This is currently broken, it highlights the cells, but they aren't actually considered "selected".
		// So if you go to Attendance -> TimeSheet, select two cells, Mass Edit them, click Save, the "Mass Edit" is no longer available to be clicked again.
		// As well if you hold in SHIFT to try and expand the selection (select more cells), that doesn't work either.
		var trs = this.grid.find('tr');
		for ( var i = 0; i < selection_obj.length; i++ ) {
			for ( var x = 0; x < trs.length; x++ ){
				if ( $(trs[x]).index() == selection_obj[i].tr ) {
					var tds = $(trs[x]).find('td');
					for ( var w = 0; w < tds.length; w++ ){
						if ( $(tds[w]).index() == selection_obj[i].td ) {
							$(tds[w]).addClass('ui-state-highlight');
							break;
						}
					}
					break;
				}
			}
		}
	},

	this.setGridColumnsWidth = function( column_model, options ) {
		// this.grid.autoResizeAllColumns();
		// return;
		if ( this.setup.treeGrid || this.setup.tree_mode ) {
			this.setGridWidth( $(this.setup.container_selector).width() - 12 );
			return;
		}


		if ( typeof options === 'undefined' ) {
			options = {};
		}

		if ( this.setup.container_selector && (!options.min_grid_width || options.min_grid_width == 0 ) ) {
			if ( this.grid.parents( this.setup.container_selector ).find('.edit-view-tab').length > 0 ) {
				//Sub-View grid.
				options.min_grid_width = this.grid.parents( this.setup.container_selector ).find('.edit-view-tab').innerWidth();
			} else {
				//Main grid
				options.min_grid_width = this.grid.parents( this.setup.container_selector ).innerWidth();
			}
		}

		if ( options.min_grid_width == 0 || options.min_grid_width == 'undefined' ) { //fallback width so it's never sized to zero when render timing collides
			options.min_grid_width = $( 'body' ).innerWidth();
		}

		//Adjust for the vertical scrollbar offset that can occur when the items per page always exceeds the screen height.
		var grid_width_scrollbar_offset = ( this.grid.parents('.ui-jqgrid-bdiv').length > 0 && Math.ceil( this.grid.parents('.ui-jqgrid-bdiv').height() ) < Math.ceil( this.grid.parents('.ui-jqgrid-bdiv')[0].scrollHeight ) ) ? 15 : 0;
		options.min_grid_width = options.min_grid_width - grid_width_scrollbar_offset;

		if ( !options.max_grid_width ) {
			options.max_grid_width = null; //No maximum.
		}

		if ( options.max_grid_width && options.max_grid_width < options.min_grid_width ) {
			options.min_grid_width = options.max_grid_width;
		}

		Debug.Text( 'Target Grid: '+ this.setup.container_selector +' Target Div: '+ this.grid.parents( this.setup.container_selector ).id +' Width: Min: '+ options.min_grid_width +' Max: '+ options.max_grid_width +' Scrollbar Offset: '+ grid_width_scrollbar_offset +' Parent Width: '+ $(this.grid.parents('.edit-view-tab, body')[0]).width(), 'TTGrid.js', 'TTGrid', 'setGridColumnsWidth', 10 );

		if ( column_model ) {
			var total_column_width = 0;
			for( var i = 0; i < column_model.length; i++ ) {
				var field = column_model[i].name;
				var width = column_model[i].width ? column_model[i].width : column_model.widthOrg;
				total_column_width += width;

				//Don't change the width of columns if they are already the same size. This may help avoid minor changes in the table caused by simple redraws.
				if ( this.grid.getColProp( field ).width != width ) {
					this.grid.setColWidth( field, width );
				}
			}

			return total_column_width;
		}

		var column_model = this.getColumnModel();

		//Possible exception
		//Error: Uncaught TypeError: Cannot read property 'length' of undefined in /interface/html5/#!m=TimeSheet&date=20141102&user_id=53130 line 4288
		if ( !column_model ) {
			Debug.Text( 'ERROR: column_model is null or undefined', 'TTGrid.js', 'TTGrid', 'setGridColumnsWidth', 10 );
			return;
		}

		function longer( champ, contender ) {
			return (contender.length > champ.length) ? contender : champ;
		}

		function longestWord( str ) {
			if ( str ) {
				var words = str.split( ' ' );
				return words.reduce( longer );
			} else {
				return '';
			}
		}

		var grid_data = this.getData();
		this.grid_total_width = 0;

		var cb_column_width = 0; //checkbox column width, usually 22, but only set once we know there is a checkbox column.
		var cb_column_count_adjustment = 0; //If the checkbox column exists or not.

		var longest_field_width = 0;
		var last_column = null;
		var column_widths = {};

		for ( var i = 0; i < column_model.length; i++ ) {
			var col = column_model[i];
			last_column = col;
			var field = col.name;
			var longest_cell_content = longestWord( col.label ); // allow extra space for sort orer ui hint
			var header_is_longest = true;

			if ( field == 'cb' ) { //hard coded override on CB column, so we don't try to check the data in each row for it.
				cb_column_count_adjustment = 1;
				cb_column_width = 22;
				width = cb_column_width;
			} else {
				if ( options.column_width_override && options.column_width_override[field] ) {
					width = options.column_width_override[field];
				} else {

					for (var j = 0; j < grid_data.length; j++) {
						var row_data = grid_data[j];
						if (!row_data.hasOwnProperty(field)) {
							break;
						}

						var current_cell_content = row_data[field];
						if (!current_cell_content) {
							current_cell_content = '';
						}

						if (!longest_cell_content) {
							longest_cell_content = current_cell_content.toString();
							header_is_longest = false;
						} else {
							if (current_cell_content && current_cell_content.toString().length > longest_cell_content.length) {
								longest_cell_content = current_cell_content.toString();
								header_is_longest = false;
							}
						}
					}


					if ( longest_cell_content == field ) {
						calculate_text_width_options = {fontSize: '11px', fontWeight: 'bolder'};
					} else {
						calculate_text_width_options = {fontSize: '11px', fontWeight: 'normal'};
					}

					var width = Global.calculateTextWidth( longest_cell_content, calculate_text_width_options ); // + 40; // 8 is drag handle width +2 for borders +20 for sort order ui hint (17 for actual hint,13 for header padding on Firefox under windows

					if ( header_is_longest === true ) {
						width += 40;
					} else {
						width += 12;
					}
				}
			}

			if ( width > longest_field_width ) {
				longest_field_width = width;
			}

			//Debug.Text( '    Column: '+ field +' Width: '+ width +' Content: \''+ longest_cell_content +'\' Longest Column Width: '+ longest_field_width +' Header is Longest: '+ header_is_longest, 'TTGrid.js', 'TTGrid', 'setGridColumnsWidth', 10 );

			column_widths[field] = width;
			this.grid_total_width += width;
		}

		//If the longest column width can be used for all columns, size them all equally so they don't change sizes between pages.
		if ( ( longest_field_width * column_model.length ) <= options.min_grid_width ) {
			var equal_column_width = Math.floor( ( options.min_grid_width - cb_column_width ) / ( column_model.length - cb_column_count_adjustment ) );
			var equal_column_width_remainder = ( ( options.min_grid_width - cb_column_width ) % ( column_model.length - cb_column_count_adjustment ) ); //Eliminate partial pixel adjustments.
			Debug.Text( ' Grid columns CAN fit with equal sizes... Grid Width: '+ options.min_grid_width +' Optimal Grid Width: '+ this.grid_total_width +' Equal Size Width: '+ equal_column_width +' Remainder: '+ equal_column_width_remainder, 'TTGrid.js', 'TTGrid', 'setGridColumnsWidth', 10 );

			var adjusted_grid_width = 0;

			var x = 0;
			for ( var tmp_column_name in column_widths ) {
				if ( tmp_column_name == 'cb' ) {
					this.grid.setColWidth( 'cb', cb_column_width );
					adjusted_grid_width += cb_column_width;
				} else {
					tmp_column_width = equal_column_width;

					if ( x == 1 ) {
						tmp_column_width += equal_column_width_remainder;
					}

					//Debug.Text( '    Adjusted Column: '+ tmp_column_name +' Width: Old: '+ column_widths[tmp_column_name] +' New: '+ tmp_column_width, 'TTGrid.js', 'TTGrid', 'setGridColumnsWidth', 10 );
					if ( this.grid.getColProp( tmp_column_name ).width != tmp_column_width ) {
						this.grid.setColWidth( tmp_column_name, tmp_column_width );
					}
					adjusted_grid_width += tmp_column_width;
				}

				this.grid.setColProp( tmp_column_name, 'fixed', true );

				x++;
			}
			Debug.Text( ' Adjusted Grid Width: '+ adjusted_grid_width +' Adjusted Column Remainder: '+ equal_column_width_remainder, 'TTGrid.js', 'TTGrid', 'setGridColumnsWidth', 10 );

			this.grid_total_width = options.min_grid_width;
		} else {
			Debug.Text( ' Optimal Grid Width: '+ this.grid_total_width +' Equal Size Width: '+ ( longest_field_width * column_model.length ), 'TTGrid.js', 'TTGrid', 'setGridColumnsWidth', 10 );

			var body_width_difference = 0;
			var column_width_adjustment = 0;
			var columns_to_adjust_width = 0;
			var column_width_adjustment_remainder = 0;

			//If the optimal column widths are wider than the max grid width, shrink them to fit.
			if ( ( options.max_grid_width > 0 && this.grid_total_width > options.max_grid_width ) || ( options.min_grid_width > 0 && this.grid_total_width < options.min_grid_width ) ) {
				//When columns are too small to fit on the screen and need to be stretched, ignore the overridden column.
				if ( ( options.max_grid_width > 0 && this.grid_total_width > options.max_grid_width ) ) {
					body_width_difference = this.grid_total_width - options.max_grid_width; //Should be a negative difference to shrink columns to fit max width.
					this.grid_total_width = options.max_grid_width;
				} else {
					body_width_difference = options.min_grid_width - this.grid_total_width; //Should be a positive difference to grow columns to fit min width.
					this.grid_total_width = options.min_grid_width;
				}

				if ( body_width_difference != 0 ) {
					if ( options.column_width_override ) {
						columns_to_adjust_width = ( column_model.length - options.column_width_override.length() );
					} else {
						columns_to_adjust_width = ( column_model.length - cb_column_count_adjustment );
					}
					column_width_adjustment = Math.floor( body_width_difference / columns_to_adjust_width );
					column_width_adjustment_remainder = ( body_width_difference % columns_to_adjust_width ); //Eliminate partial pixel adjustments.
				}
			}

			var adjusted_grid_width = 0;

			var x = 0;
			for ( var tmp_column_name in column_widths ) {
				if ( tmp_column_name == 'cb' ) {
					this.grid.setColWidth( 'cb', cb_column_width );
					adjusted_grid_width += cb_column_width;
				} else {
					if ( options.column_width_override && options.column_width_override[n] ) {
						tmp_column_width = column_widths[tmp_column_name];
					} else {
						tmp_column_width = column_widths[tmp_column_name] + column_width_adjustment;
					}

					if ( x == 1 ) { //First column after the CB.
						tmp_column_width += column_width_adjustment_remainder;
					}

					Debug.Text( '    Adjusted Column: '+ tmp_column_name +' Width: Old: '+ column_widths[tmp_column_name] +' New: '+ tmp_column_width +' Adjustment: '+ column_width_adjustment +' Body Difference: '+ body_width_difference +' Columns Adjusted: '+ columns_to_adjust_width, 'TTGrid.js', 'TTGrid', 'setGridColumnsWidth', 10 );
					if ( this.grid.getColProp( tmp_column_name ).width != tmp_column_width ) {
						this.grid.setColWidth( tmp_column_name, tmp_column_width );
					}
					adjusted_grid_width += tmp_column_width;
				}
				this.grid.setColProp( tmp_column_name, 'fixed', true );

				x++;
			}
			//Debug.Text( ' Adjusted Grid Width: '+ adjusted_grid_width +' Adjusted Column Remainder: '+ column_width_adjustment_remainder, 'TTGrid.js', 'TTGrid', 'setGridColumnsWidth', 10 );
		}

		//this.setGridWidth( this.grid_total_width, this.grid_total_width ); //Causes columns with to flucuate, specifically in schedule day mode.
		Debug.Text( ' FINAL Grid width: '+ this.grid_total_width +' Body Width: '+ Global.bodyWidth() +' Total Rows: '+ grid_data.length, 'TTGrid.js', 'TTGrid', 'setGridColumnsWidth', 10 );
		return this.grid_total_width;
	};

	//resize event.
	$( '#contentContainer' ).off( 'resize' ).on( 'resize', Global.debounce( function(e) {
		e.stopPropagation();
		Debug.Text( ' Window resize event hit by TTGrid. Target: '+ e.target, 'TTGrid.js', 'TTGrid', 'setGridColumnsWidth', 10 );

		if ( LocalCacheData.resizeable_grids.length > 0 ) {

			//remove the nulls
			var grids = LocalCacheData.resizeable_grids.filter( function(t) {
				return t != null;
			});
			LocalCacheData.resizeable_grids = grids;

			for ( var i in LocalCacheData.resizeable_grids ) {
				var ttgrid = LocalCacheData.resizeable_grids[i];

				if ( !ttgrid || $('#'+ttgrid.ui_id).length === 0 || !ttgrid.grid || ttgrid.setup.onResizeGrid === false ) {
					Debug.Arr( LocalCacheData.resizeable_grids, ' Grid ignored ' + i, 'TTGrid.js', 'TTGrid', 'setGridColumnsWidth', 10 );
					LocalCacheData.resizeable_grids[i] = null;
					continue;
				}

				Debug.Text( ' Processing: ' + ttgrid.ui_id, 'TTGrid.js', 'TTGrid', 'setGridColumnsWidth', 10 );
				if ( ttgrid.setup.onResizeGrid && typeof ttgrid.setup.onResizeGrid == 'function' ) {
					Debug.Text( ' TTGrid invoked setup defined onResizeGrid() for ' + ttgrid.ui_id, 'TTGrid.js', 'TTGrid', 'setGridColumnsWidth', 10 );
					ttgrid.setup.onResizeGrid();
				} else {
					if ( ttgrid.grid.length == 1 ) {
						Debug.Text( ' TTGrid invoked TTgrid::setGridColumnsWidth() for ' + ttgrid.ui_id, 'TTGrid.js', 'TTGrid', 'setGridColumnsWidth', 10 );
						ttgrid.setGridColumnsWidth();

					}
				}
			}

			//usually we will want to make doublesure that visible search grids resize.
			//have to check for grid though because dashboard has no "search grid"
			if ( LocalCacheData.current_open_primary_controller.grid ) {
				//LocalCacheData.current_open_primary_controller.grid.setGridColumnsWidth();
				LocalCacheData.current_open_primary_controller.setGridColumnsWidth(); //Be sure to call the setGridColumnsWidth() from current_open_primary_controller in case its overridden.
				LocalCacheData.current_open_primary_controller.setGridSize();
			}
		}
	}, 500 ) );
};