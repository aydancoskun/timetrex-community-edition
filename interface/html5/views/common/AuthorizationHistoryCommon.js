export var AuthorizationHistory = {

	/**
	 * There's only 4 steps for ading auth history to a view file:
	 * 1. Copy the authorization-grid-div from RequestEditView.html
	 * 2. Paste that div into the editview html of the new view
	 * 3. Add AuthorizationHistory.init(this) to setEditViewDataDone() or to the end of onViewClick() if you experience screen flashing with it in setEditViewDataDone()
	 * 4a.If the view only has one hierarchytype id, add this.hierarchy_type_id = [**the correct id**]; to the init function of the view
	 * 4b.Else, ensure that hierarch_type_id is set in the view's current_edit_record
	 *
	 */

	authorization_api: null,
	authorization_history_columns: [],
	authorization_history_default_display_columns: [],

	host_view_controller: null,

	/**
	 * call this to render the auth grid.
	 * assumes this.edit_view exists.
	 * @param $this
	 * @returns {AuthorizationHistory}
	 */
	init: function( host ) {
		$( '.authorization-grid-div' ).hide();
		if ( host.is_add ) {
			return;
		}

		var separate_box = $( '.authorization-grid-div .grid-title' );
		separate_box.html( '' );

		var form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Authorization History' ) } );
		form_item_input.attr( 'id', 'authorization_history' );
		host.addEditFieldToColumn( null, form_item_input, separate_box );

		this.host_view_controller = host;
		this.authorization_api = TTAPI.APIAuthorization;

		var $this = this;
		this.getAuthorizationHistoryColumns( function() {
			$this.initAuthorizationHistoryLayout( function() {
				$this.setAuthorizationGridSize();
			} );
		} );

		return $this;
	},

	initAuthorizationHistoryLayout: function( callback ) {
		var $this = this;
		this.getAuthorizationHistoryDefaultDisplayColumns( function() {
			if ( !$this.host_view_controller.edit_view ) {
				return;
			}
			$this.setAuthorizationHistorySelectLayout();
			$this.initAuthorizationHistoryData();
			if ( callback ) {
				callback();
			}
		} );
	},

	/**
	 * Gets data from the API and puts it into the authorization history grid.
	 *
	 * @param callback
	 */
	initAuthorizationHistoryData: function( callback ) {
		var filter = {};
		filter.filter_data = {};

		filter.filter_columns = { 'created_by': true, 'created_date': true, 'authorized': true };
		filter.filter_data.object_id = [this.host_view_controller.current_edit_record.id];
		filter.filter_data.object_type_id = this.host_view_controller.hierarchy_type_id ? this.host_view_controller.hierarchy_type_id : this.host_view_controller.current_edit_record.hierarchy_type_id;

		var $this = this;
		this.authorization_api['get' + this.authorization_api.key_name]( filter, {
			onResult: function( result ) {

				if ( !$this.host_view_controller.edit_view ) {
					return;
				}

				var result_data = result.getResult();
				if ( result.isValid() && Global.isArray( result_data ) && result_data.length >= 1 ) {
					result_data = Global.formatGridData( result_data, $this.authorization_api.key_name );

					$this.authorization_history_grid.setData( result_data );

					$( $this.host_view_controller.edit_view.find( '.authorization-grid-div' ) ).show();
					$this.showAuthorizationHistoryGridBorders();
					$this.setAuthorizationGridSize();
				} else {
					$( $this.host_view_controller.edit_view.find( '.authorization-grid-div' ) ).hide();
				}

			}
		} );
	},

	setAuthorizationGridSize: function() {
		var history_height_unit;
		if ( ( !this.authorization_history_grid || !this.authorization_history_grid.grid.is( ':visible' ) ) ) {
			return;
		}
		history_height_unit = this.authorization_history_grid.getData().length;
		history_height_unit > 5 && ( history_height_unit = 5 );
		this.authorization_history_grid.grid.setGridWidth( $( this.host_view_controller.edit_view.find( '#authorization_history' ) ).width() );
		this.authorization_history_grid.grid.setGridHeight( history_height_unit * 25 );
	},

	buildAuthorizationDisplayColumns: function( apiDisplayColumnsArray ) {
		var len = this.authorization_history_columns.length;
		var len1 = apiDisplayColumnsArray.length;
		var display_columns = [];

		for ( var j = 0; j < len1; j++ ) {
			for ( var i = 0; i < len; i++ ) {
				if ( apiDisplayColumnsArray[j] === this.authorization_history_columns[i].value ) {
					display_columns.push( this.authorization_history_columns[i] );
				}
			}
		}
		return display_columns;
	},

	showAuthorizationHistoryGridBorders: function() {
		var top_border = this.host_view_controller.edit_view.find( '.grid-top-border' );
		var bottom_border = this.host_view_controller.edit_view.find( '.grid-bottom-border' );

		top_border.css( 'display', 'block' );
		bottom_border.css( 'display', 'block' );
	},

	getAuthorizationHistoryDefaultDisplayColumns: function( callBack ) {
		var $this = this;
		this.authorization_api.getOptions( 'default_display_columns', {
			onResult: function( columns_result ) {
				var columns_result_data = columns_result.getResult();

				$this.authorization_history_default_display_columns = columns_result_data;

				if ( callBack ) {
					callBack();
				}

			}
		} );
	},

	getAuthorizationHistoryColumns: function( callBack ) {
		var $this = this;
		this.authorization_api.getOptions( 'columns', {
			onResult: function( columns_result ) {
				var columns_result_data = columns_result.getResult();
				$this.authorization_history_columns = Global.buildColumnArray( columns_result_data );

				if ( callBack ) {
					callBack();
				}
			}
		} );
	},

	setAuthorizationHistorySelectLayout: function( column_start_from ) {
		var $this = this;
		var grid = this.host_view_controller.edit_view.find( '#grid' );
		if ( grid ) {
			grid.attr( 'id', 'authorization_history_grid' );  //Grid's id is ScriptName + _grid
		}
		var column_info_array = [];
		var display_columns = this.buildAuthorizationDisplayColumns( this.authorization_history_default_display_columns );

		//Set Data Grid on List view
		var len = display_columns.length;

		for ( var i = 0; i < len; i++ ) {
			var view_column_data = display_columns[i];

			var column_info = {
				name: view_column_data.value,
				index: view_column_data.value,
				label: view_column_data.label,
				width: 100,
				sortable: false,
				title: false
			};
			column_info_array.push( column_info );
		}

		if ( this.authorization_history_grid ) {

			this.authorization_history_grid.grid.jqGrid( 'GridUnload' );
			this.authorization_history_grid = null;
		}

		this.authorization_history_grid = new TTGrid( 'authorization_history_grid', {
			onResizeGrid: false,
			winMultiselect: false,
			multiselect: false,
			width: this.host_view_controller.edit_view.find( '.edit-view-tab' ).width()
		}, column_info_array );
	}

};