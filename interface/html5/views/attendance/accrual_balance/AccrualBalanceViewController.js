class AccrualBalanceViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#accrual_balance_view_container',

			user_group_api: null,
			user_group_array: null,

			sub_accrual_view_controller: null,

			log_object_ids: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'AccrualBalanceEditView.html';
		this.permission_id = 'accrual';
		this.viewId = 'AccrualBalance';
		this.script_name = 'AccrualBalanceView';
		this.table_name_key = 'accrual';
		this.context_menu_name = $.i18n._( 'Accrual Balances' );
		this.navigation_label = $.i18n._( 'Accrual Balance' ) + ':';
		this.api = TTAPI.APIAccrualBalance;
		this.accrual_api = TTAPI.APIAccrual;
		this.user_api = TTAPI.APIUser;
		this.user_group_api = TTAPI.APIUserGroup;

		this.initPermission();
		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary();
	}

	initPermission() {

		super.initPermission();

		if ( PermissionManager.validate( this.permission_id, 'view' ) || PermissionManager.validate( this.permission_id, 'view_child' ) ) {
			this.show_search_tab = true;
		} else {
			this.show_search_tab = false;
		}
	}

	initOptions() {
		var $this = this;

		this.initDropDownOption( 'status', 'user_status_id', this.user_api, null, 'user_status_array' );
		this.user_group_api.getUserGroup( '', false, false, {
			onResult: function( res ) {

				res = res.getResult();
				res = Global.buildTreeRecord( res );

				if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
					$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
				}

				$this.user_group_array = res;

			}
		} );
	}

	buildEditViewUI() {
		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_accrual': {
				'label': $.i18n._( 'Accrual' ),
				'init_callback': 'initSubAccrualView',
				'display_on_mass_edit': false
			},
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIAccrualBalance,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.ACCRUAL_BALANCE,
			navigation_mode: true,
			addition_source_function: function( target, data ) {
				return $this.__createRowId( data );
			},
			show_search_inputs: true
		} );

		this.setNavigation();
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['default'],
			include: [
				ContextMenuIconName.add,
				ContextMenuIconName.view,
				ContextMenuIconName.export_excel
			]
		};

		return context_menu_model;
	}

	buildSearchFields() {
		super.buildSearchFields();

		var default_args = {};
		default_args.permission_section = 'accrual';

		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				default_args: default_args,
				field: 'user_id',
				layout_name: ALayoutIDs.USER,
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Employee Status' ),
				in_column: 1,
				field: 'user_status_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Accrual Account' ),
				in_column: 1,
				field: 'accrual_policy_account_id',
				layout_name: ALayoutIDs.ACCRUAL_POLICY_ACCOUNT,
				api_class: TTAPI.APIAccrualPolicyAccount,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Default Branch' ),
				in_column: 2,
				field: 'default_branch_id',
				layout_name: ALayoutIDs.BRANCH,
				api_class: TTAPI.APIBranch,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Default Department' ),
				in_column: 2,
				field: 'default_department_id',
				layout_name: ALayoutIDs.DEPARTMENT,
				api_class: TTAPI.APIDepartment,
				multiple: true,
				basic_search: true,
				adv_search: false,
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
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	}

	getFilterColumnsFromDisplayColumns() {
		var column_filter = {};
		column_filter.user_id = true;
		column_filter.accrual_policy_account_id = true;
		return this._getFilterColumnsFromDisplayColumns( column_filter, true );
	}

	__createRowId( data ) {
		if ( Array.isArray( data ) ) {
			for ( var i = 0; i < data.length; i++ ) {
				data[i].id = data[i]['user_id'] + '_' + data[i]['accrual_policy_account_id'];
			}
		} else if ( data && data['user_id'] && data['accrual_policy_account_id'] ) {
			data.id = data['user_id'] + '_' + data['accrual_policy_account_id'];
		} else {
			Debug.Text( 'ERROR: Data format is invalid.', 'AccrualBalanceViewController.js', 'AccrualBalanceViewController', '__createRowId', 1 );
		}
		return data;
	}

//	saveLogIds( data ) {
//		this.parent_view_controller.log_object_ids = [];
//		for ( var i = 0; i < data.length; i++ ) {
//			this.parent_view_controller.log_object_ids.push( data[i]['id'] );
//		}
//
//		return data;
//	},

	setDefaultMenuAddIcon( context_btn, grid_selected_length, pId ) {
		this.setDefaultMenuEditIcon( context_btn, grid_selected_length, pId );
	}

	onAddClick() {
		var $this = this;
		this.setCurrentEditViewState( 'view' );
		this.add_accrual = true;
		$this.openEditView();

		var selected_item = null;
		var grid_selected_id_array = this.getGridSelectIdArray();

		var grid_selected_length = grid_selected_id_array.length;

		if ( grid_selected_length > 0 ) {
			selected_item = this.getRecordFromGridById( grid_selected_id_array[0] );
		} else {
			var grid_source_data = $this.grid.getGridParam( 'data' );
			selected_item = grid_source_data[0];
		}

		var filter = {};
		filter.filter_data = {};
		if ( selected_item ) {
			filter.filter_data.user_id = selected_item.user_id;
			filter.filter_data.accrual_policy_account_id = selected_item.accrual_policy_account_id;
		}

		this.api['get' + this.api.key_name]( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();

				if ( !result_data ) {
					result_data = [];
				}

				result_data = $this.__createRowId( result_data );

				result_data = result_data[0];

				if ( !result_data ) {
					result_data = {};
				}

				$this.current_edit_record = result_data;

				if ( $this.current_edit_record && $this.current_edit_record.user_id && $this.current_edit_record.accrual_policy_account_id ) {
					filter.filter_data.user_id = $this.current_edit_record.user_id;
					filter.filter_data.accrual_policy_account_id = $this.current_edit_record.accrual_policy_account_id;
				}

				// get the accrual data with the same filter data in order to be used for the audit tab.
				$this.accrual_api['get' + $this.accrual_api.key_name]( filter, {
					onResult: function( res ) {
						var result = res.getResult();
						$this.log_object_ids = [];
						for ( var i = 0; i < result.length; i++ ) {
							$this.log_object_ids.push( result[i]['id'] );
						}

						$this.initEditView();

					}
				} );

			}
		} );
	}

	parseToUserId( id ) {
		if ( !id ) {
			return false;
		}

		id = id.toString();

		if ( id.indexOf( '_' ) > 0 ) {
			return id.split( '_' )[0];
		}

		return id;
	}

	parseToAccrualPolicyAccountId( id ) {
		if ( !id ) {
			return false;
		}

		id = id.toString();

		if ( id.indexOf( '_' ) > 0 ) {
			return id.split( '_' )[1];
		}

		return id;
	}

	getAPIFilters() {
		var composite_id = this.getCurrentSelectedRecord();

		var filter = {};

		filter.filter_data = {};
		filter.filter_data.user_id = this.parseToUserId( composite_id );
		filter.filter_data.accrual_policy_account_id = this.parseToAccrualPolicyAccountId( composite_id );

		return filter;
	}

	doViewClickResult( result_data ) {
		var $this = this;
		var filter = {};
		filter.filter_data = {};

		result_data = $this.__createRowId( result_data );
		$this.current_edit_record = result_data;
		if ( $this.current_edit_record && $this.current_edit_record.user_id && $this.current_edit_record.accrual_policy_account_id ) {
			filter.filter_data.user_id = $this.current_edit_record.user_id;
			filter.filter_data.accrual_policy_account_id = $this.current_edit_record.accrual_policy_account_id;
		}

		// get the accrual data with the same filter data in order to be used for the audit tab.
		return $this.accrual_api['get' + $this.accrual_api.key_name]( filter, {
			onResult: function( res ) {
				var result = res.getResult();
				$this.log_object_ids = [];
				for ( var i = 0; i < result.length; i++ ) {
					$this.log_object_ids.push( result[i]['id'] );
				}

				return $this.initEditView();

			}
		} );
	}

	setEditViewData() {
		this.is_changed = false;
		this.initEditViewData();
		this.switchToProperTab();
		this.initTabData();
	}

	initEditViewData() {
		var $this = this;
		if ( !this.edit_only_mode && this.navigation ) {
			var grid_current_page_items = this.grid.getGridParam( 'data' );

			var navigation_div = this.edit_view.find( '.navigation-div' );

			navigation_div.css( 'display', 'block' );
			//Set Navigation Awesomebox

			//init navigation only when open edit view

			if ( !this.navigation.getSourceData() ) {

				this.navigation.setSourceData( grid_current_page_items );
				this.navigation.setRowPerPage( LocalCacheData.getLoginUserPreference().items_per_page );
				this.navigation.setPagerData( this.pager_data );

//				this.navigation.setDisPlayColumns( this.buildDisplayColumnsByColumnModel( this.grid.getGridParam( 'colModel' ) ) );
				var default_args = {};
				default_args.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
				default_args.filter_sort = this.select_layout.data.filter_sort;
				this.navigation.setDefaultArgs( default_args );
			}

			this.navigation.setValue( this.current_edit_record );
		}

		this.setNavigationArrowsStatus();

		// Create this function alone because of the column value of view is different from each other, some columns need to be handle specially. and easily to rewrite this function in sub-class.

		this.setCurrentEditRecordData();
		//Init *Please save this record before modifying any related data* box
		this.edit_view.find( '.save-and-continue-div' ).SaveAndContinueBox( { related_view_controller: this } );
		this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'none' );
	}

	initSubAccrualView() {
		var $this = this;

		if ( !this.current_edit_record.id ) {
			TTPromise.resolve( 'BaseViewController', 'onTabShow' ); //Since search() isn't called in this case, and we just display the "Please Save This Record ..." message, resolve the promise.
			return;
		}

		if ( this.sub_accrual_view_controller ) {
			this.sub_accrual_view_controller.buildContextMenu( true );
			this.sub_accrual_view_controller.setDefaultMenu();
			$this.sub_accrual_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_accrual_view_controller.initData();
			return;
		}

		Global.loadScript( 'views/attendance/accrual/AccrualViewController.js', function() {
			var tab_accrual = $this.edit_view_tab.find( '#tab_accrual' );

			var firstColumn = tab_accrual.find( '.first-column-sub-view' );

			TTPromise.add( 'initSubAccrualView', 'init' );
			TTPromise.wait( 'initSubAccrualView', 'init', function() {
				firstColumn.css( 'opacity', '1' );
			} );

			firstColumn.css( 'opacity', '0' ); //Hide the grid while its loading/sizing.

			Global.trackView( 'Sub' + 'Accrual' + 'View' );
			AccrualViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );
		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_accrual_view_controller = subViewController;
			$this.sub_accrual_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_accrual_view_controller.parent_view_controller = $this;
			$this.sub_accrual_view_controller.is_trigger_add = $this.add_accrual ? true : false;
			$this.sub_accrual_view_controller.initData();
			$this.add_accrual = false;
		}
	}

	setSubLogViewFilter() {
		if ( !this.sub_log_view_controller ) {
			return false;
		}

		this.sub_log_view_controller.parent_key = 'object_id';
		this.sub_log_view_controller.parent_value = this.log_object_ids;
		this.sub_log_view_controller.table_name_key = this.table_name_key;

		return true;
	}

	removeEditView() {
		super.removeEditView();
		this.sub_accrual_view_controller = null;
	}

	setNavigation() {

		var $this = this;
		this.navigation.setPossibleDisplayColumns( this.buildDisplayColumnsByColumnModel( this.grid.getGridParam( 'colModel' ) ),
			this.buildDisplayColumns( this.default_display_columns ) );

		this.navigation.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {

			var key = target.getField();
			var next_select_item_id = target.getValue();

			if ( !next_select_item_id ) {
				return;
			}

			if ( next_select_item_id !== $this.current_edit_record.id ) {
				ProgressBar.showOverlay();

				$this.onViewClick( next_select_item_id ); //Dont refresh UI

			}

		} );

	}

}