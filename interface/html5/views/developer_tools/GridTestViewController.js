GridTestViewController = BaseViewController.extend( {
	el: '#grid_test_view_container',

	_required_files: {
		10: [
			'APICompany', 'APIUserGroup', 'APIHierarchyControl', 'APIBranch', 'APIDepartment', 'APIUserTitle', 'APICompanyGenericTag', 'TImage', 'APILegalEntity',
			'APIPayPeriodSchedule', 'APIPolicyGroup', 'APICurrency', 'APIEthnicGroup', 'APIUserSkill', 'APIQualification', 'APIQualificationGroup',
			'APIUserEducation', 'APIUserMembership', 'APIUserLicense', 'APIUserLanguage', 'APIRemittanceDestinationAccount', 'APIUserDefault', 'APIRemittanceSourceAccount', 'APIAccrualPolicyUserModifier',
			'APIUserReviewControl', 'APIKPIGroup', 'APIUserReview', 'APIKPI', 'TImageAdvBrowser'
		],
		20: ['APIJob', 'APIJobItem']
	},

	user_api: null,
	user_group_api: null,
	company_api: null,
	user_id_array: null,
	grid_container_id: null,

	init: function( options ) {
		this.edit_view_tpl = 'GridTestEditView.html';
		this.permission_id = 'user';
		this.viewId = 'GridTest';
		this.script_name = 'GridTestView';
		this.table_name_key = 'grid_test';
		this.context_menu_name = $.i18n._( 'Grid Test' );
		this.navigation_label = $.i18n._( 'AwesomBox Test' ) + ':';
		this.api = new ( APIFactory.getAPIClass( 'APIUser' ) )();
		this.select_company_id = LocalCacheData.getCurrentCompany().id;
		this.user_group_api = new ( APIFactory.getAPIClass( 'APIUserGroup' ) )();
		this.company_api = new ( APIFactory.getAPIClass( 'APICompany' ) )();
		this.user_id_array = [];

		this.edit_view_grid_array = [];

		this.grid_container_id = TTUUID.generateUUID();
		this.render();
		this.buildContextMenu();
		this.initData();
	},

	setCurrentEditRecordData: function() {
		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();
	},

	clearEditViewData: function() {
		return false;
	},

	buildEditViewUI: function() {
		var $this = this;

		var tab_model = {
			'tab_employee': { 'label': $.i18n._( 'AWESOMEBOX TESTING VIEW' ) },
		};
		this.setTabModel( tab_model );

		//Tab 0 start

		var tab_employee = this.edit_view_tab.find( '#tab_employee' );

		var tab_employee_column1 = tab_employee.find( '.first-column' );

		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( tab_employee_column1 );

		var $div_grid_container = $( '<div id="' + this.grid_container_id + '">' );
		tab_employee_column1.append( $div_grid_container );

		/** build the test grids **/
		var $grid = this.addGrid( 1 );
		$div_grid_container.append( $grid );
		$grid.setData( this.getFakeData( 1 ) );

		var column_header_text_array = [
			'column name',
			'column name2',
			'column name3',
			'column name4',
			'column name5',
			'column name6'
		];
		var $grid = this.addGrid( 6, column_header_text_array );
		$div_grid_container.append( $grid );
		$grid.setData( this.getFakeData( 6, true ) );

		var $grid = this.addGrid( 12 );
		$div_grid_container.append( $grid );
		$grid.setData( this.getFakeData( 12 ) );

		var $grid = this.addGrid( 40 );
		$div_grid_container.append( $grid );
		$grid.setData( this.getFakeData( 40 ) );

		for ( var i = 0; i < this.edit_view_grid_array.length; i++ ) {
			this.edit_view_grid_array[i].setGridColumnsWidth();
		}

		TTPromise.resolve( 'Gridtest', 'init' );
	},

	getGridSetup: function() {
		var setup = {
			container_selector: '.edit-view-tab',
			sub_grid_mode: true,
			onResizeGrid: true,
			onSelectRow: function() {
			},
			onCellSelect: function() {
			},
			onSelectAll: function() {
			},
			ondblClickRow: function( e ) {
			},
			onRightClickRow: function( rowId ) {
			},
			height: 200
		};
		return setup;
	},

	addGrid: function( column_count, column_header_text_array ) {
		var container_selector = 'test_grid_' + column_count;
		$( '#' + this.grid_container_id ).append( $( '<table id="' + container_selector + '" >' ) );

		var column_info_array = [];
		for ( var n = 0; n < column_count; n++ ) {
			var letter = ( n + 10 ).toString( 36 );
			var text = 'Column ' + letter;
			if ( column_header_text_array ) {
				text = column_header_text_array[n];
			}
			column_info_array.push( {
				name: letter,
				index: letter,
				label: text,
				width: 100,
				sortable: false,
				title: false
			} );
		}

		var $grid = new TTGrid( container_selector, this.getGridSetup(), column_info_array );
		this.edit_view_grid_array.push( $grid );
		return $grid;
	},

	getFakeData: function( column_count, randomize ) {
		var data = [];
		for ( var n = 0; n < 4; n++ ) {
			var data_element = {};
			for ( var m = 0; m < column_count; m++ ) {
				var letter = ( m + 10 ).toString( 36 );
				if ( randomize ) {
					data_element[letter] = TTUUID.generateUUID();
				} else {
					data_element[letter] = 'content';
				}

			}
			data.push( data_element );
		}
		return data;
	},

	buildSearchFields: function() {
		this._super( 'buildSearchFields' );
		this.search_fields = [];
	},

	getCustomContextMenuModel: function() {
		var context_menu_model = {
			exclude: ['default'],
			include: [
				ContextMenuIconName.edit,
				ContextMenuIconName.cancel
			]
		};

		return context_menu_model;
	},

	//override that forces same data to grid at all times.
	search: function() {
		var result_data = JSON.parse( '[{"id":"11e85213-a799-d200-b041-123456abcdef","status":"Active","employee_number":100,"first_name":"Mr.","last_name":"FAKE","full_name":"Administrator, Mr.","home_phone":"471-438-3900","is_owner":true,"is_child":false},' +
			'{"id":"11e85213-ad34-e0e0-8541-123456abcdef","status":"Active","employee_number":13,"first_name":"Tristen","last_name":"Braun","full_name":"FAKE Braun, Tristen","home_phone":"527-500-4852","is_owner":false,"is_child":true},' +
			'{"id":"11e85213-af64-d0e0-9b00-123456abcdef","status":"Active","employee_number":20,"first_name":"Jane","last_name":"Doe","full_name":"FAKE Doe, Jane","home_phone":"477-443-9650","is_owner":false,"is_child":true},' +
			'{"id":"11e85213-ac44-1830-9908-123456abcdef","status":"Active","employee_number":10,"first_name":"John","last_name":"Doe","full_name":"FAKE Doe, John","home_phone":"464-547-9452","is_owner":false,"is_child":true}]' );
		this.user_id_array = result_data;
		result_data = this.processResultData( result_data );
		this.grid.setData( result_data );
		this.grid.setGridColumnsWidth();
		this.current_edit_record = result_data[0];
		this.setCurrentEditViewState( 'edit' );

		TTPromise.add( 'Gridtest', 'init' );
		var $this = this;
		TTPromise.wait( 'Gridtest', 'init', function() {
			$this.initEditView();
		} );

		this.initEditViewUI( this.viewId, this.edit_view_tpl );
	},

	setEditViewDataDone: function() {
		var $this = this;
		setTimeout( function() {
			$this.setTabOVisibility( true );
			$( '.edit-view-tab-bar' ).css( 'opacity', 1 );
		}, 2500 );
	},

} );
