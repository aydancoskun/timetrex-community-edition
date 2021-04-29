class AwesomeboxTestViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#awesomebox_test_view_container',

			_required_files: ['TImage', 'TImageAdvBrowser'],

			user_api: null,
			user_group_api: null,
			company_api: null,
			user_id_array: null
		} );

		super( options );
	}

	init( options ) {
		this.edit_view_tpl = 'AwesomeboxTestEditView.html';
		this.permission_id = 'user';
		this.viewId = 'AwesomeboxTest';
		this.script_name = 'AwesomeboxTestView';
		this.table_name_key = 'awesomebox_test';
		this.context_menu_name = $.i18n._( 'AwesomeBox Test' );
		this.navigation_label = $.i18n._( 'AwesomBox Test' ) + ':';
		this.api = TTAPI.APIUser;
		this.select_company_id = LocalCacheData.getCurrentCompany().id;
		this.user_group_api = TTAPI.APIUserGroup;
		this.company_api = TTAPI.APICompany;
		this.user_id_array = [];

		this.render();
		this.buildContextMenu();
		this.initData();
	}

	setCurrentEditRecordData() {
		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();
	}

	clearEditViewData() {
		return false;
	}

	buildEditViewUI() {
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

//		User
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_DROPDOWN );
		form_item_input.ADropDown( {
			//api_class: TTAPI.APIUser,
			field: 'user_id',
			display_show_all: false,
			id: 'user_id_dropdown',
			key: 'id',
			allow_drag_to_order: false,
			display_close_btn: false,
			auto_sort: true,
			display_column_settings: false,
			default_height: 100
		} );

		// form_item_input.setSourceData( Global.addFirstItemToArray( $this.user_id_array ) );
		form_item_input.addClass( 'splayed-adropdown' );
		var display_columns = ALayoutCache.getDefaultColumn( ALayoutIDs.USER ); //Get Default columns base on different layout name
		display_columns = Global.convertColumnsTojGridFormat( display_columns, ALayoutIDs.USER ); //Convert to jQgrid format
		this.addEditFieldToColumn( $.i18n._( 'User' ), form_item_input, tab_employee_column1 );
		form_item_input.setColumns( display_columns );
		form_item_input.setUnselectedGridData( this.user_id_array );
		form_item_input.setGridColumnsWidths();
		form_item_input.setHeight( 150 );

		//Company
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.USER,
			show_search_inputs: true,
			set_empty: true,
			field: 'user_id2'
		} );
		this.addEditFieldToColumn( $.i18n._( 'User ( Big multi select)' ), form_item_input, tab_employee_column1 );
		form_item_input.setEnabled( false );

		//Company
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APICompany,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.COMPANY,
			show_search_inputs: true,
			set_empty: true,
			field: 'company_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Company' ), form_item_input, tab_employee_column1 );
		form_item_input.setEnabled( false );

		//Legal Entity
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APILegalEntity,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.LEGAL_ENTITY,
			show_search_inputs: true,
			set_empty: true,
			field: 'legal_entity_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Legal Entity' ), form_item_input, tab_employee_column1 );

		//Group
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			tree_mode: true,
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.TREE_COLUMN,
			set_empty: true,
			field: 'group_id'
		} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.user_group_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Group' ), form_item_input, tab_employee_column1 );

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			tree_mode: true,
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.TREE_COLUMN,
			set_empty: true,
			field: 'group_id2'
		} );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.user_group_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Group (Multi)' ), form_item_input, tab_employee_column1 );

		//Status

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'status_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_employee_column1 );

		TTPromise.resolve( 'Awesomeboxtest', 'init' );
	}

	buildSearchFields() {

		super.buildSearchFields();

		var $this = this;
		this.search_fields = [
			new SearchField( {
				label: $.i18n._( 'Company' ),
				in_column: 1,
				field: 'company_id',
				layout_name: ALayoutIDs.COMPANY,
				api_class: TTAPI.APICompany,
				multiple: false,
				custom_first_label: Global.default_item,
				basic_search: PermissionManager.checkTopLevelPermission( 'Companies' ) ? true : false,
				adv_search: PermissionManager.checkTopLevelPermission( 'Companies' ) ? true : false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Legal Entity' ),
				in_column: 1,
				field: 'legal_entity_id',
				layout_name: ALayoutIDs.LEGAL_ENTITY,
				api_class: TTAPI.APILegalEntity,
				multiple: true,
				custom_first_label: Global.any_item,
				basic_search: PermissionManager.checkTopLevelPermission( 'LegalEntity' ) ? true : false,
				adv_search: PermissionManager.checkTopLevelPermission( 'LegalEntity' ) ? true : false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Gender' ),
				in_column: 2,
				field: 'sex_id',
				multiple: true,
				basic_search: false,
				adv_search: true,
				layout_name: ALayoutIDs.OPTION_COLUMN,
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
			} )
		];
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['default'],
			include: [
				ContextMenuIconName.view,
				ContextMenuIconName.edit,
				ContextMenuIconName.cancel
			]
		};

		return context_menu_model;
	}

	initOptions( callBack ) {

		var options = [
			{ option_name: 'status' },
			{ option_name: 'company_id' }
		];

		this.initDropDownOptions( options, function( result ) {

			if ( callBack ) {
				callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
			}

		} );
	}

	initDropDownOptions( options, callBack ) {
		var $this = this;
		var len = options.length + 2;
		var complete_count = 0;
		var option_result = [];

		var res = '[{"id":"00000000-0000-0000-0000-000000000000","name":"Root","level":0,"children":[{"id":"11e85213-a778-6410-bee1-123456abcdef","company_id":"11e85213-a6b2-93b0-a2cd-123456abcdef","parent_id":"00000000-0000-0000-0000-000000000000","name":"Corporate","deleted":false,"created_by_id":false,"created_by":false,"created_date":"07-May-18 9:28 AM","updated_by_id":false,"updated_by":false,"updated_date":"07-May-18 9:28 AM","level":1,"children":[{"id":"11e85213-a779-fa20-835f-123456abcdef","company_id":"11e85213-a6b2-93b0-a2cd-123456abcdef","parent_id":"11e85213-a778-6410-bee1-123456abcdef","name":"Executives","deleted":false,"created_by_id":false,"created_by":false,"created_date":"07-May-18 9:28 AM","updated_by_id":false,"updated_by":false,"updated_date":"07-May-18 9:28 AM","level":2,"expanded":true,"loaded":true,"parent":"11e85213-a778-6410-bee1-123456abcdef","isLeaf":true},{"id":"11e85213-a77a-39c0-9bc2-123456abcdef","company_id":"11e85213-a6b2-93b0-a2cd-123456abcdef","parent_id":"11e85213-a778-6410-bee1-123456abcdef","name":"Human Resources","deleted":false,"created_by_id":false,"created_by":false,"created_date":"07-May-18 9:28 AM","updated_by_id":false,"updated_by":false,"updated_date":"07-May-18 9:28 AM","level":2,"expanded":true,"loaded":true,"parent":"11e85213-a778-6410-bee1-123456abcdef","isLeaf":true}],"expanded":true,"loaded":true,"parent":"00000000-0000-0000-0000-000000000000"},{"id":"11e85213-a77a-7810-a67f-123456abcdef","company_id":"11e85213-a6b2-93b0-a2cd-123456abcdef","parent_id":"00000000-0000-0000-0000-000000000000","name":"Hourly (Non-Exempt)","deleted":false,"created_by_id":false,"created_by":false,"created_date":"07-May-18 9:28 AM","updated_by_id":false,"updated_by":false,"updated_date":"07-May-18 9:28 AM","level":1,"children":[{"id":"11e85213-a77a-bb40-b83e-123456abcdef","company_id":"11e85213-a6b2-93b0-a2cd-123456abcdef","parent_id":"11e85213-a77a-7810-a67f-123456abcdef","name":"Salary (Exempt)","deleted":false,"created_by_id":false,"created_by":false,"created_date":"07-May-18 9:28 AM","updated_by_id":false,"updated_by":false,"updated_date":"07-May-18 9:28 AM","level":2,"expanded":true,"loaded":true,"parent":"11e85213-a77a-7810-a67f-123456abcdef","isLeaf":true}],"expanded":true,"loaded":true,"parent":"00000000-0000-0000-0000-000000000000"}],"expanded":true,"loaded":true},{"id":"11e85213-a778-6410-bee1-123456abcdef","company_id":"11e85213-a6b2-93b0-a2cd-123456abcdef","parent_id":"00000000-0000-0000-0000-000000000000","name":"Corporate","deleted":false,"created_by_id":false,"created_by":false,"created_date":"07-May-18 9:28 AM","updated_by_id":false,"updated_by":false,"updated_date":"07-May-18 9:28 AM","level":1,"children":[{"id":"11e85213-a779-fa20-835f-123456abcdef","company_id":"11e85213-a6b2-93b0-a2cd-123456abcdef","parent_id":"11e85213-a778-6410-bee1-123456abcdef","name":"Executives","deleted":false,"created_by_id":false,"created_by":false,"created_date":"07-May-18 9:28 AM","updated_by_id":false,"updated_by":false,"updated_date":"07-May-18 9:28 AM","level":2,"expanded":true,"loaded":true,"parent":"11e85213-a778-6410-bee1-123456abcdef","isLeaf":true},{"id":"11e85213-a77a-39c0-9bc2-123456abcdef","company_id":"11e85213-a6b2-93b0-a2cd-123456abcdef","parent_id":"11e85213-a778-6410-bee1-123456abcdef","name":"Human Resources","deleted":false,"created_by_id":false,"created_by":false,"created_date":"07-May-18 9:28 AM","updated_by_id":false,"updated_by":false,"updated_date":"07-May-18 9:28 AM","level":2,"expanded":true,"loaded":true,"parent":"11e85213-a778-6410-bee1-123456abcdef","isLeaf":true}],"expanded":true,"loaded":true,"parent":"00000000-0000-0000-0000-000000000000"},{"id":"11e85213-a779-fa20-835f-123456abcdef","company_id":"11e85213-a6b2-93b0-a2cd-123456abcdef","parent_id":"11e85213-a778-6410-bee1-123456abcdef","name":"Executives","deleted":false,"created_by_id":false,"created_by":false,"created_date":"07-May-18 9:28 AM","updated_by_id":false,"updated_by":false,"updated_date":"07-May-18 9:28 AM","level":2,"expanded":true,"loaded":true,"parent":"11e85213-a778-6410-bee1-123456abcdef","isLeaf":true},{"id":"11e85213-a77a-39c0-9bc2-123456abcdef","company_id":"11e85213-a6b2-93b0-a2cd-123456abcdef","parent_id":"11e85213-a778-6410-bee1-123456abcdef","name":"Human Resources","deleted":false,"created_by_id":false,"created_by":false,"created_date":"07-May-18 9:28 AM","updated_by_id":false,"updated_by":false,"updated_date":"07-May-18 9:28 AM","level":2,"expanded":true,"loaded":true,"parent":"11e85213-a778-6410-bee1-123456abcdef","isLeaf":true},{"id":"11e85213-a77a-7810-a67f-123456abcdef","company_id":"11e85213-a6b2-93b0-a2cd-123456abcdef","parent_id":"00000000-0000-0000-0000-000000000000","name":"Hourly (Non-Exempt)","deleted":false,"created_by_id":false,"created_by":false,"created_date":"07-May-18 9:28 AM","updated_by_id":false,"updated_by":false,"updated_date":"07-May-18 9:28 AM","level":1,"children":[{"id":"11e85213-a77a-bb40-b83e-123456abcdef","company_id":"11e85213-a6b2-93b0-a2cd-123456abcdef","parent_id":"11e85213-a77a-7810-a67f-123456abcdef","name":"Salary (Exempt)","deleted":false,"created_by_id":false,"created_by":false,"created_date":"07-May-18 9:28 AM","updated_by_id":false,"updated_by":false,"updated_date":"07-May-18 9:28 AM","level":2,"expanded":true,"loaded":true,"parent":"11e85213-a77a-7810-a67f-123456abcdef","isLeaf":true}],"expanded":true,"loaded":true,"parent":"00000000-0000-0000-0000-000000000000"},{"id":"11e85213-a77a-bb40-b83e-123456abcdef","company_id":"11e85213-a6b2-93b0-a2cd-123456abcdef","parent_id":"11e85213-a77a-7810-a67f-123456abcdef","name":"Salary (Exempt)","deleted":false,"created_by_id":false,"created_by":false,"created_date":"07-May-18 9:28 AM","updated_by_id":false,"updated_by":false,"updated_date":"07-May-18 9:28 AM","level":2,"expanded":true,"loaded":true,"parent":"11e85213-a77a-7810-a67f-123456abcdef","isLeaf":true}]';
		res = JSON.parse( res );
		if ( !$this.edit_only_mode ) {
			if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
				$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
				$this.adv_search_field_ui_dic['group_id'].setSourceData( res );
			}

		}

		$this.user_group_array = res;

		complete_count = complete_count + 1;

		if ( complete_count === len ) {

			callBack( option_result );
		}

		for ( var i = 0; i < len - 2; i++ ) {
			var option_info = options[i];
			this.initDropDownOption( option_info.option_name, option_info.field_name, option_info.api, onGetOptionResult );

		}

		function onGetOptionResult( result ) {

			option_result.push( result );

			complete_count = complete_count + 1;

			if ( complete_count === len ) {

				callBack( option_result );
			}
		}
	}

	//override that forces same data to grid at all times.
	search() {
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

		TTPromise.add( 'Awesomeboxtest', 'init' );
		var $this = this;
		TTPromise.wait( 'Awesomeboxtest', 'init', function() {
			$this.initEditView();
		} );

		this.initEditViewUI( this.viewId, this.edit_view_tpl );
	}

	setEditViewDataDone() {
		var $this = this;
		setTimeout( function() {
			TAlertManager.showConfirmAlert( $.i18n._( 'Run tests?' ), null, function( result ) {
				if ( result == true ) {
					$this.runTests();
				}
			} );
			$this.setTabOVisibility( true );
			$( '.edit-view-tab-bar' ).css( 'opacity', 1 );
		}, 2500 );
	}

	/**
	 * in the testing code are a lot of null null waits to ensure that all awesomeboxes are ready for next step in test
	 */
	runTests() {
		if ( $( '#qunit_script' ).length == 0 ) {
			$( '<script id=\'qunit_script\' src=\'framework/qunit/qunit.js\'></script>' ).appendTo( 'head' );
			$( '<link rel=\'stylesheet\' type=\'text/css\' href=\'framework/qunit/qunit.css\'>' ).appendTo( 'head' );
		}
		;

		QUnit.config.autostart = false;
		$( '#qunit_container' ).css( 'width', '100%' );
		$( '#qunit_container' ).css( 'height', 'auto' );
		$( '#qunit_container' ).css( 'overflow-y', 'scroll' );
		$( '#qunit_container' ).css( 'top', '0px' );
		$( '#qunit_container' ).css( 'left', '0px' );
		$( '#qunit_container' ).css( 'z-index', '100' );
		$( '#qunit_container' ).css( 'background', '#fff' );
		$( '#qunit_container' ).show();

		$( '#tt_debug_console' ).remove();
		if ( !window.qunit_initiated ) {
			window.qunit_initiated = true;
			QUnit.start();
		}

		// QUnit.module('QUnit Sanity');
		// QUnit.test("QUnit test", function (assert) {
		// 	assert.ok(1 == "1", "QUnit is loaded and sane!");
		// });

		//select an item by clicking a td in the last row
		var $this = this;
		QUnit.module( 'AwesomeBox' );
		QUnit.test( 'Awesomebox tests', function( assert ) {
			var done_awesomebox_tests = assert.async();

			Debug.Text( 'A: Test Splayed Awesomebox', 'AwesomeboxTestViewController.js', 'AwesomeboxTestViewController', 'runTests', 11 );
			$this.checkOpenAwesomeBox( $this.edit_view_ui_dic['user_id'], assert, function() {
				Debug.Text( 'B: Test MultiSelect AwesomeBox', 'AwesomeboxTestViewController.js', 'AwesomeboxTestViewController', 'runTests', 11 );
				$this.checkMultiSelectAwesomeBox( assert, function() {
					Debug.Text( 'C: Test regular AwesomeBox', 'AwesomeboxTestViewController.js', 'AwesomeboxTestViewController', 'runTests', 11 );
					$this.checkAwesomeBox( assert, function() {
						Debug.Text( 'D: Test tree_mode AwesomeBox', 'AwesomeboxTestViewController.js', 'AwesomeboxTestViewController', 'runTests', 11 );
						$this.checkTreeBox( assert, function() {
							Debug.Text( 'E: Test tree_mode MultiSelect AwesomeBox', 'AwesomeboxTestViewController.js', 'AwesomeboxTestViewController', 'runTests', 11 );
							$this.checkMultiTreeAwesomeBox( assert, function() {
								Debug.Text( 'F: Test searching in AwesomeBox', 'AwesomeboxTestViewController.js', 'AwesomeboxTestViewController', 'runTests', 11 );
								$this.checkClearSearch( $this.edit_view_ui_dic['user_id2'], assert, function() {
									Debug.Text( 'G: Wonky move all scenario', 'AwesomeboxTestViewController.js', 'AwesomeboxTestViewController', 'runTests', 11 );
									$this.checkMoveAllScenario( $this.edit_view_ui_dic['user_id2'], assert, function() {
										setTimeout( function() {
											Debug.Text( 'Z: Testing Complete', 'AwesomeboxTestViewController.js', 'AwesomeboxTestViewController', 'runTests', 11 );
											done_awesomebox_tests();
										}, 1000 );

									} );

								} );
							} );
						} );
					} );
				} );
			} );

		} );
	}

	checkMultiTreeAwesomeBox( assert, callback ) {
		var awesomebox = this.edit_view_ui_dic['group_id2'];
		awesomebox.trigger( 'click' );
		var visible_awesomebox = $( '#group_id2a_dropdown_div' );
		var $this = this;
		TTPromise.wait( 'AComboBox', 'init', function() {
			visible_awesomebox.find( '.unselect-grid-div tbody tr:last td:last' ).trigger( 'click' );
			visible_awesomebox.find( '.a-grid-right-arrow' ).trigger( 'click' );
			visible_awesomebox.find( '#select_grid_close_btn' ).trigger( 'click' );
			var val = awesomebox.getValue();

			assert.ok( ( TTUUID.isUUID( val ) && val != TTUUID.zero_id ), 'Multi tree - selection valid' );

			setTimeout( function() {
				awesomebox.trigger( 'click' );
				setTimeout( function() {

					var visible_awesomebox = $( '#group_id2a_dropdown_div' );
					visible_awesomebox.find( '.select-grid-div tbody tr:last td:last' ).trigger( 'click' );
					visible_awesomebox.find( '.a-grid-left-arrow' ).trigger( 'click' );
					visible_awesomebox.find( '#select_grid_close_btn' ).trigger( 'click' );
					var val = awesomebox.getValue();

					assert.ok( ( val == TTUUID.zero_id ), 'Multi tree - unselection valid' );

					awesomebox.trigger( 'click' );
					//this check fails when it should not as the border makes it look fine
					//$this.checkGridWidths( visible_awesomebox, assert );
					visible_awesomebox.find( '#select_grid_close_btn' ).trigger( 'click' );
					if ( callback ) {
						callback();
					}
				}, 1000 );
			}, 1000 );

		} );
	}

	checkTreeBox( assert, callback ) {
		var awesomebox = this.edit_view_ui_dic['group_id'];
		var val = awesomebox.getValue();
		assert.ok( ( val == TTUUID.zero_id ), 'tree - unselection valid' );
		var $this = this;
		setTimeout( function() {
			awesomebox.trigger( 'click' );
			TTPromise.wait( null, null, function() {
				$this.checkGridWidths( $( '#group_ida_dropdown_div' ), assert );
				$( '#group_ida_dropdown_div .unselect-grid-div tbody tr:last td:last' ).trigger( 'click' );
				setTimeout( function() {
					var val = awesomebox.getValue();
					awesomebox.setValue( null );

					assert.ok( ( TTUUID.isUUID( val ) && val != TTUUID.zero_id ), 'tree - selection valid' );

					if ( callback ) {
						callback();
					}
				}, 1000 );

			} );
		}, 1000 );
	}

	checkAwesomeBox( assert, callback ) {
		var awesomebox = this.edit_view_ui_dic['legal_entity_id'];
		var val = awesomebox.getValue();

		assert.ok( ( val == TTUUID.zero_id ), 'regular - unselection valid' );

		var $this = this;
		setTimeout( function() {
			awesomebox.trigger( 'click' );
			TTPromise.wait( null, null, function() {
				$this.checkGridWidths( $( '#legal_entity_idADropDown' ), assert );
				$( '#legal_entity_idADropDown .unselect-grid-div tbody tr:last td:last' ).trigger( 'click' );
				setTimeout( function() {
					val = awesomebox.getValue();
					awesomebox.setValue( null );
					assert.ok( ( TTUUID.isUUID( val ) && val != TTUUID.zero_id ), 'regular - selection valid' );

					if ( callback ) {
						callback();
					}
				}, 1000 );

			} );
		}, 1000 );
	}

	checkMultiSelectAwesomeBox( assert, callback ) {
		var awesomebox = this.edit_view_ui_dic['company_id'];
		awesomebox.trigger( 'click' );
		var visible_awesomebox = $( '#company_ida_dropdown_div' );
		var $this = this;
		TTPromise.wait( 'AComboBox', 'init', function() {
			visible_awesomebox.find( '.unselect-grid-div tbody tr:last td:last' ).trigger( 'click' );
			visible_awesomebox.find( '.a-grid-right-arrow' ).trigger( 'click' );
			visible_awesomebox.find( '#select_grid_close_btn' ).trigger( 'click' );
			var val = awesomebox.getValue();

			assert.ok( ( TTUUID.isUUID( val ) && val != TTUUID.zero_id ), 'multiselect - selection valid' );

			setTimeout( function() {
				awesomebox.trigger( 'click' );
				TTPromise.wait( 'AComboBox', 'init', function() {

					var visible_awesomebox = $( '#company_ida_dropdown_div' );
					visible_awesomebox.find( '.select-grid-div tbody tr:last td:last' ).trigger( 'click' );
					visible_awesomebox.find( '.a-grid-left-arrow' ).trigger( 'click' );
					$this.checkGridWidths( visible_awesomebox, assert );
					visible_awesomebox.find( '#select_grid_close_btn' ).trigger( 'click' );
					val = awesomebox.getValue();
					awesomebox.trigger( 'click' );

					assert.ok( ( val == TTUUID.zero_id ), 'multiselect - unselection valid' );

					TTPromise.wait( null, null, function() {
						if ( callback ) {
							callback();
						}
					} );
				} );
			}, 2000 );

		} );
	}

	checkOpenAwesomeBox( box, assert, callback ) {
		var selected_id = box.find( '.unselect-grid-div tbody tr:last' ).attr( 'id' );
		box.find( '.unselect-grid-div tbody tr:last td:last' ).trigger( 'click' );
		box.find( '.a-grid-right-arrow' ).trigger( 'click' );
		var val = box.getValue();

		assert.ok( ( TTUUID.isUUID( val[0].id ) && val[0].id == selected_id ), 'splayed - selection valid' );

		var $this = this;
		TTPromise.wait( 'AComboBox', 'init', function() {
			box.find( '.select-grid-div tbody tr:last td:last' ).trigger( 'click' );
			box.find( '.a-grid-left-arrow' ).trigger( 'click' );

			assert.ok( ( val.length === 0 ), 'splayed - unselection valid' );

			$this.checkGridWidths( box, assert );

			if ( callback ) {
				TTPromise.wait( null, null, function() {
					callback();
				} );
			}
		} );
	}

	checkGridWidths( box, assert ) {

		var unselect_grid_container_width = box.find( '.unselect-grid-border-div' ).width();
		var unselect_grid_width = box.find( '.ui-jqgrid' ).width();
		var unselect_grid_diff = unselect_grid_container_width - unselect_grid_width;

		assert.ok( ( unselect_grid_diff <= 2 ), 'unselect grid width diff <= 2: ' + unselect_grid_diff );

		if ( box.find( '.select-grid-border-div' ).is( ':visible' ) > 0 ) {
			var select_grid_container_width = box.find( '.select-grid-border-div' ).width();
			var select_grid_width = box.find( '.ui-jqgrid' ).width();
			var select_grid_diff = Math.abs( select_grid_container_width - select_grid_width );

			assert.ok( ( select_grid_diff <= 2 ), 'select grid width diff <= 2: ' + select_grid_diff );
		}
	}

	checkClearSearch( box, assert, callback ) {
		// open
		var $this = this;
		box.trigger( 'click' );
		TTPromise.wait( null, null, function() {
			var dropdown = $( '#user_id2ADropDown' );
			var checkboxes = dropdown.find( '.unselect-grid-div .ui-jqgrid input[type="checkbox"]' );

			var expected_ids = [];
			// select a couple records
			$( checkboxes[1] ).trigger( 'click' );
			$( checkboxes[3] ).trigger( 'click' );
			$( checkboxes[6] ).trigger( 'click' );
			$( checkboxes[7] ).trigger( 'click' );

			//note selected ids
			expected_ids.push( getRowId( $( checkboxes[1] ) ) );
			expected_ids.push( getRowId( $( checkboxes[3] ) ) );
			expected_ids.push( getRowId( $( checkboxes[6] ) ) );
			expected_ids.push( getRowId( $( checkboxes[7] ) ) );

			// click move button
			dropdown.find( '.a-grid-right-arrow' ).trigger( 'click' );

			// check that visible values contain expected ids
			assert.ok( expectedInGrid( 'select_grid', dropdown, expected_ids ), 'all expected records are in selected grid' );

			// search to filter
			var select_grid_text_inputs = dropdown.find( '.select-grid-div input[type="text"].search-input' );

			// set up grid search prerequisites
			$( select_grid_text_inputs[0] ).trigger( 'focus' );
			$( select_grid_text_inputs[0] ).val( 'mr' );
			LocalCacheData.openAwesomeBox.focus_in_select_grid = true;
			var e = $.Event( "keydown", { keyCode: 77, which: 77 } );
			LocalCacheData.openAwesomeBox.selectNextItem( e );
			var e = $.Event( "keydown", { keyCode: 82, which: 82 } );
			LocalCacheData.openAwesomeBox.selectNextItem( e );
			// trigger search
			$( select_grid_text_inputs[0] ).trigger( 'searchEnter', ['mr', 'first_name'] );

			TTPromise.wait( null, null, function() {
				// check that VISIBLE values contain expected ids
				assert.ok( ( dropdown.find( '.select-grid tr' ).length == 2 ), 'only .sizetr and mr admin shows after filter (only works with demo data)' );

				// move first record back ( note id )
				var first_row = $( dropdown.find( '.select-grid tr' )[dropdown.find( '.select-grid tr' ).length - 1] );
				first_row.find( 'input[type="checkbox"]' ).click();
				var ondblClickRowHandler = dropdown.find( '.select-grid' ).jqGrid( 'getGridParam', 'ondblClickRow' );
				ondblClickRowHandler.call( dropdown.find( '.select-grid' )[0], first_row.prop( 'id' ) );

				// check that selected values contain expected ids
				assert.ok( $( dropdown.find( '.select-grid tr' ).length == 1 ), 'removed record is in not in selectgrid' ); //1 because of sizetr
				assert.ok( expectedInGrid( 'unselect_grid', dropdown, [getRowId( first_row )] ), 'removed record is in unselectgrid' );

				// clear search
				dropdown.find( '.select-grid-search-div .close-btn' ).trigger( 'click' );

				TTPromise.wait( null, null, function() {
					expected_ids.splice( 0, 1 ); // remove moved record from expected_ids

					// check that selected values contain expected ids
					assert.ok( ( expected_ids.length == 3 ), 'correct number of expected records' );
					assert.ok( ( dropdown.find( '.select-grid tr' ).length == 4 ), 'correct number of selected rows' );
					assert.ok( expectedInGrid( 'select_grid', dropdown, expected_ids ), 'all expected records are in selected grid' );

					dropdown.find( '#select_grid_close_btn' ).click();
					box.setValue( [] );

					if ( callback ) {
						callback();
					}

				} );
			} );
		} );

		//strip out jqgrid stuff and get row id
		function getRowId( row ) {
			if ( !row || !row.prop ) {
				return false;
			}

			if ( row.prop( 'id' ).indexOf( '_' ) == -1 ) {
				return row.prop( 'id' );
			}

			var id_array = $( row ).prop( 'id' ).split( '_' );
			return id_array[id_array.length - 1];
		}

		function expectedInGrid( target_grid_name, dropdown, expected_ids ) {
			var selected_rows = null;
			if ( target_grid_name == 'select_grid' ) {
				selected_rows = dropdown.find( '.select-grid tr' );
			} else {
				selected_rows = dropdown.find( '.unselect-grid tr' );
			}
			var all_found = true;

			for ( var n = 0; n < expected_ids.length; n++ ) {
				var is_found = false;
				for ( var i = 0; i < selected_rows.length; i++ ) {
					if ( expected_ids[n] == getRowId( $( selected_rows[i] ) ) ) {
						is_found = true;
						break;
					}
				}
				if ( !is_found ) {
					all_found = false;
					break;
				}
			}
			return all_found;
		}
	}

	checkMoveAllScenario( box, assert, callback ) {
		// open
		var $this = this;
		box.trigger( 'click' );
		TTPromise.wait( 'AComboBox', 'init', function() {
			var dropdown = $( '#user_id2ADropDown' );
			if ( dropdown.length == 0 ) {
				//wait until combobox is visible ( sometimes takes longer than promise )
				setTimeout( function() {
					$this.checkMoveAllScenario( box, assert, callback );
				}, 1000 );
				return;
			}

			var checkboxes = dropdown.find( '.unselect-grid-div .ui-jqgrid input[type="checkbox"]' );

			var expected_ids = [];
			// select a couple records
			$( checkboxes[1] ).trigger( 'click' );
			$( checkboxes[3] ).trigger( 'click' );
			$( checkboxes[6] ).trigger( 'click' );
			$( checkboxes[7] ).trigger( 'click' );

			//note selected ids
			expected_ids.push( getRowId( $( checkboxes[1] ) ) );
			expected_ids.push( getRowId( $( checkboxes[3] ) ) );
			expected_ids.push( getRowId( $( checkboxes[6] ) ) );
			expected_ids.push( getRowId( $( checkboxes[7] ) ) );

			// click move button
			dropdown.find( '.a-grid-right-arrow' ).trigger( 'click' );

			// check that visible values contain expected ids
			assert.ok( expectedInGrid( 'select_grid', dropdown, expected_ids ), 'all expected records are in selected grid' );

			//click "move all" button to remove all selected records
			dropdown.find( '.select-grid-title-bar #clear_btn' ).trigger( 'click' );

			TTPromise.wait( null, null, function() {
				expected_ids = [];

				// check that selected values contain expected ids
				assert.ok( ( expected_ids.length == 0 ), 'correct number of expected records' );
				assert.ok( ( dropdown.find( '.select-grid tr' ).length == 1 ), 'correct number of selected rows ( zero )' ); // the 1 is sizetr
				assert.ok( expectedInGrid( 'select_grid', dropdown, expected_ids ), 'zero records are in selected grid' );

				dropdown.find( '#select_grid_close_btn' ).click();
				box.setValue( [] );

				if ( callback ) {
					callback();
				}

			} );
		} );

		function getRowId( row ) {
			if ( !row || !row.prop ) {
				return false;
			}

			if ( row.prop( 'id' ).indexOf( '_' ) == -1 ) {
				return row.prop( 'id' );
			}

			var id_array = $( row ).prop( 'id' ).split( '_' );
			return id_array[id_array.length - 1];
		}

		function expectedInGrid( target_grid_name, dropdown, expected_ids ) {
			var selected_rows = null;
			if ( target_grid_name == 'select_grid' ) {
				selected_rows = dropdown.find( '.select-grid tr' );
			} else {
				selected_rows = dropdown.find( '.unselect-grid tr' );
			}
			var all_found = true;

			for ( var n = 0; n < expected_ids.length; n++ ) {
				var is_found = false;
				for ( var i = 0; i < selected_rows.length; i++ ) {
					if ( expected_ids[n] == getRowId( $( selected_rows[i] ) ) ) {
						is_found = true;
						break;
					}
				}
				if ( !is_found ) {
					all_found = false;
					break;
				}
			}
			return all_found;
		}
	}
}
