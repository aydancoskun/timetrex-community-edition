MessageControlViewController = BaseViewController.extend( {
	el: '#message_control_view_container',

	_required_files: ['APIMessageControl', 'APIRequest'],

	object_type_array: null,

	is_request: false,
	is_message: false,

	messages: null,
	request_api: null,

	folder_id: null,

	navigation_source_data: null,

	isReloadViewUI: false,

	current_select_message_control_data: null, //current select message control data, set in onViewClick

	init: function( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'MessageControlEditView.html';
		this.permission_id = 'message';
		this.viewId = 'MessageControl';
		this.script_name = 'MessageControlView';
		this.table_name_key = 'message_control';
		this.context_menu_name = $.i18n._( 'Message' );
		this.api = new ( APIFactory.getAPIClass( 'APIMessageControl' ) )();
		this.request_api = new ( APIFactory.getAPIClass( 'APIRequest' ) )();
		this.folder_id = 10;

		this.render();
		this.buildContextMenu();

		this.initData();
		this.setSelectRibbonMenuIfNecessary( 'MessageControl' );

	},

	initOptions: function() {
		var $this = this;

		this.initDropDownOption( 'object_type' );

	},

	buildSearchFields: function() {

		this._super( 'buildSearchFields' );

		var default_args = {};
		default_args.permission_section = 'message';
		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				default_args: default_args,
				layout_name: ALayoutIDs.USER,
				api_class: ( APIFactory.getAPIClass( 'APIUser' ) ),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Type' ),
				in_column: 1,
				multiple: true,
				field: 'object_type_id',
				basic_search: true,
				adv_search: false,
				layout_name: ALayoutIDs.OPTION_COLUMN,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Subject' ),
				in_column: 1,
				field: 'subject',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: ALayoutIDs.USER,
				api_class: ( APIFactory.getAPIClass( 'APIUser' ) ),
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
				api_class: ( APIFactory.getAPIClass( 'APIUser' ) ),
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	},

	getSubViewFilter: function( filter ) {

		if ( filter.length === 0 ) {
			filter = {};
		}

		filter['folder_id'] = this.folder_id;

		return filter;
	},

	getCustomContextMenuModel: function() {
		var context_menu_model = {
			groups: {
				view: {
					label: $.i18n._( 'View' ),
					id: this.script_name + 'View',
					sort_order: 900
				},
				editor: {
					label: $.i18n._( 'Editor' ),
					id: this.viewId + 'Editor',
					sort_order: 1000
				},
				folder: {
					label: $.i18n._( 'Folder' ),
					id: this.script_name + 'Folder',
					sort_order: 2000
				},
				other: {
					label: $.i18n._( 'Other' ),
					id: this.script_name + 'other',
					sort_order: 9000

				}
			},
			exclude: ['default'],
			include: [
				{
					label: $.i18n._( 'New' ),
					id: ContextMenuIconName.add,
					group: 'view',
					icon: Icons.new_add,
					permission_result: true,
					permission: null
				},
				{
					label: $.i18n._( 'View' ),
					id: ContextMenuIconName.view,
					group: 'view',
					icon: Icons.view,
					permission_result: true,
					permission: null
				},
				{
					label: $.i18n._( 'Reply' ),
					id: ContextMenuIconName.edit,
					group: 'view',
					icon: Icons.edit,
					permission_result: true,
					permission: null
				},
				{
					label: $.i18n._( 'Delete' ),
					id: ContextMenuIconName.delete_icon,
					group: 'view',
					icon: Icons.delete_icon,
					permission_result: true,
					permission: null
				},
				{
					label: $.i18n._( 'Delete<br>& Next' ),
					id: ContextMenuIconName.delete_and_next,
					group: 'view',
					icon: Icons.delete_and_next,
					permission_result: true,
					permission: null
				},
				{
					label: $.i18n._( 'Close' ),
					id: ContextMenuIconName.close_misc,
					group: 'view',
					icon: Icons.close_misc,
					permission_result: true,
					permission: null
				},
				{
					label: $.i18n._( 'Send' ),
					id: ContextMenuIconName.send,
					group: 'editor',
					icon: Icons.send,
					permission_result: true,
					permission: null
				},
				{
					label: $.i18n._( 'Cancel' ),
					id: ContextMenuIconName.cancel,
					group: 'editor',
					icon: Icons.cancel,
					permission_result: true,
					permission: null,
					sort_order: 1990
				},
				{
					label: $.i18n._( 'Inbox' ),
					id: ContextMenuIconName.inbox,
					group: 'folder',
					icon: Icons.inbox,
					selected: true,
					permission_result: true,
					permission: null
				},
				{
					label: $.i18n._( 'Sent' ),
					id: ContextMenuIconName.sent,
					group: 'folder',
					icon: Icons.sent,
					permission_result: true,
					permission: null
				},
				{
					label: $.i18n._( 'Export' ),
					id: ContextMenuIconName.export_excel,
					group: 'other',
					icon: Icons.export_excel,
					permission_result: true,
					permission: null,
					sort_order: 9000
				}
			]
		};

		return context_menu_model;
	},

	setDefaultMenu: function( doNotSetFocus ) {

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
			/* jshint ignore:start */

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setDefaultMenuAddIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.view:
					this.setDefaultMenuViewIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.edit:
					this.setDefaultMenuEditIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.delete_icon:
					this.setDefaultMenuDeleteIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setDefaultMenuDeleteAndNextIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.close_misc:
					this.setDefaultMenuCloseMiscIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.send:
					this.setDefaultMenuSendIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.cancel:
					this.setDefaultMenuCancelIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.inbox:
					this.setDefaultMenuInboxIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.sent:
					this.setDefaultMenuSentIcon( context_btn, grid_selected_length );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn, grid_selected_length );
					break;

			}
			/* jshint ignore:end */

		}

		this.setContextMenuGroupVisibility();

	},

	onGridDblClickRow: function() {

		var len = this.context_menu_array.length;

		var need_break = false;

		for ( var i = 0; i < len; i++ ) {

			if ( need_break ) {
				break;
			}

			var context_btn = $( this.context_menu_array[i] );
			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );

			switch ( id ) {
				case ContextMenuIconName.view:
					need_break = true;
					if ( context_btn.is( ':visible' ) && !context_btn.hasClass( 'disable-image' ) ) {
						ProgressBar.showOverlay();
						this.onViewClick();
					}
					break;
			}
		}

	},

	onCustomContextClick: function( id, context_btn ) {
		switch ( id ) {
			case ContextMenuIconName.send:
				this.onSaveClick();
				break;
			case ContextMenuIconName.close_misc:
			case ContextMenuIconName.cancel:
				this.onCancelClick( id );
				break;
			case ContextMenuIconName.inbox:
				this.setCurrentSelectedIcon( context_btn );
				this.onInboxClick();
				break;
			case ContextMenuIconName.sent:
				this.setCurrentSelectedIcon( context_btn );
				this.onSentClick();
				break;
		}
	},

	onCancelClick: function( iconName ) {
		var $this = this;
		$this.is_add = false;
		LocalCacheData.current_doing_context_action = 'cancel';

		if ( this.is_changed ) {
			TAlertManager.showConfirmAlert( Global.modify_alert_message, null, function( flag ) {

				if ( flag === true ) {
					doNext();
				}

			} );
		} else {
			doNext();
		}

		function doNext() {

			if ( iconName === ContextMenuIconName.cancel && $this.isReloadViewUI ) {
				$this.isReloadViewUI = false;
//				 //set to fix that IndexViewConroler force ui back to view when open view again
				$this.onViewClick( $this.current_select_message_control_data );
			} else {
				$this.removeEditView();
				$this.isReloadViewUI = false;
			}

			Global.setUIInitComplete();
			ProgressBar.closeOverlay();

			TTPromise.resolve( 'base', 'onCancelClick' );

		}

	},

	onSaveResult: function( result ) {
		var $this = this;
		if ( result.isValid() ) {
			var result_data = result.getResult();
			if ( !this.edit_only_mode ) {
				if ( result_data === true ) {
					$this.refresh_id = $this.current_edit_record.id;
				} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
					$this.refresh_id = result_data;
				}

				$this.search();
			}

			$this.onSaveDone( result );

			if ( $this.isReloadViewUI ) {
				$this.isReloadViewUI = false;
				$this.removeEditView();
				$this.onViewClick( $this.current_select_message_control_data );

			} else {
				$this.removeEditView();
			}

			$().TFeedback( {
				source: 'Save'
			} );

		} else {
			$this.setErrorMenu();
			$this.setErrorTips( result );

		}
	},

	onInboxClick: function() {
		this.folder_id = 10;
		this.search();
	},

	onSentClick: function() {
		this.folder_id = 20;
		this.search();
	},

	setEditMenu: function() {
		this.selectContextMenu();
		var len = this.context_menu_array.length;
		var pId = null;
		if ( this.is_message ) {
			pId = 'message';
		} else if ( this.is_request ) {
			pId = 'request';
		}
		for ( var i = 0; i < len; i++ ) {
			var context_btn = $( this.context_menu_array[i] );

			var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
			context_btn.removeClass( 'disable-image' );
			/* jshint ignore:start */

			switch ( id ) {
				case ContextMenuIconName.add:
					this.setEditMenuAddIcon( context_btn, pId );
					break;
				case ContextMenuIconName.view:
					this.setEditMenuViewIcon( context_btn, pId );
					break;
				case ContextMenuIconName.edit:
					this.setEditMenuEditIcon( context_btn, pId );
					break;
				case ContextMenuIconName.delete_icon:
					this.setEditMenuDeleteIcon( context_btn, pId );
					break;
				case ContextMenuIconName.delete_and_next:
					this.setEditMenuDeleteAndNextIcon( context_btn, pId );
					break;
				case ContextMenuIconName.close_misc:
					this.setEditMenuCloseMiscIcon( context_btn, pId );
					break;
				case ContextMenuIconName.send:
					this.setEditMenuSendIcon( context_btn, pId );
					break;
				case ContextMenuIconName.cancel:
					this.setEditMenuCancelIcon( context_btn, pId );
					break;
				case ContextMenuIconName.inbox:
//					this.setCurrentSelectedIcon( context_btn, pId );
					this.setEditMenuInboxIcon( context_btn, pId );
					break;
				case ContextMenuIconName.sent:
//					this.setCurrentSelectedIcon( context_btn, pId );
					this.setEditMenuSentIcon( context_btn, pId );
					break;
				case ContextMenuIconName.export_excel:
					this.setDefaultMenuExportIcon( context_btn );
					break;
			}
			/* jshint ignore:end */

		}

		this.setContextMenuGroupVisibility();

	},

	setCurrentSelectedIcon: function( icon ) {

		//Error: Uncaught TypeError: Cannot read property 'find' of null in /interface/html5/#!m=MessageControl line 543
		if ( !icon ) {
			return;
		}

		var len = this.context_menu_array.length;
		for ( var i = 0; i < len; i++ ) {
			var context_btn = $( this.context_menu_array[i] );
			$( context_btn.find( '.ribbon-sub-menu-icon' ) ).removeClass( 'selected-menu' );
		}
		$( icon.find( '.ribbon-sub-menu-icon' ) ).addClass( 'selected-menu' );
	},

	setDefaultMenuDeleteAndNextIcon: function( context_btn, grid_selected_length, pId ) {

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuDeleteIcon: function( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length >= 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuEditIcon: function( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuViewIcon: function( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length === 1 ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuAddIcon: function( context_btn, grid_selected_length, pId ) {

	},

	setEditMenuCloseMiscIcon: function( context_btn, pId ) {

	},

	setEditMenuSendIcon: function( context_btn, pId ) {

		if ( this.is_edit || this.is_add ) {
			context_btn.removeClass( 'disable-image' );
		} else {
			context_btn.addClass( 'disable-image' );
		}

	},

	setEditMenuInboxIcon: function( context_btn, pId ) {
		context_btn.addClass( 'disable-image' );
	},

	setEditMenuSentIcon: function( context_btn, pId ) {

		context_btn.addClass( 'disable-image' );
	},

	setEditMenuCancelIcon: function( context_btn, pId ) {

		if ( this.is_viewing ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setDefaultMenuCloseMiscIcon: function( context_btn, grid_selected_length, pId ) {

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuSendIcon: function( context_btn, grid_selected_length, pId ) {

		context_btn.addClass( 'disable-image' );
	},

	setDefaultMenuInboxIcon: function( context_btn, grid_selected_length, pId ) {

	},

	setDefaultMenuSentIcon: function( context_btn, grid_selected_length, pId ) {

	},

	setGridCellBackGround: function() {
		var data = this.grid.getGridParam( 'data' );

		//Error: TypeError: data is undefined in /interface/html5/framework/jquery.min.js?v=7.4.6-20141027-074127 line 2 > eval line 70
		if ( !data ) {
			return;
		}

		var len = data.length;

		for ( var i = 0; i < len; i++ ) {
			var item = data[i];

			if ( item.status_id == 10 ) {
				$( 'tr[id=\'' + item.id + '\'] td' ).css( 'font-weight', 'bold' );
			}
		}
	},

	getFilterColumnsFromDisplayColumns: function() {
		var column_filter = {};

		column_filter.id = true;
		column_filter.is_child = true;
		column_filter.is_owner = true;
		column_filter.object_type_id = true;
		column_filter.object_id = true;
		column_filter.status_id = true;
		column_filter.from_user_id = true;
		column_filter.to_user_id = true;

		// Error: Unable to get property 'getGridParam' of undefined or null reference
		var display_columns = [];
		if ( this.grid ) {
			display_columns = this.grid.getGridParam( 'colModel' );
		}

		if ( display_columns ) {
			var len = display_columns.length;

			for ( var i = 0; i < len; i++ ) {
				var column_info = display_columns[i];
				column_filter[column_info.name] = true;
			}
		}

		return column_filter;
	},

	initEditViewUI: function( view_id, edit_view_file_name ) {

		var $this = this;

		if ( this.edit_view ) {
			this.edit_view.remove();
		}
		this.edit_view = $( Global.loadViewSource( view_id, edit_view_file_name, null, true ) );
		this.edit_view_tab = $( this.edit_view.find( '.edit-view-tab-bar' ) );
		//Give edt view tab a id, so we can load it when put right click menu on it
		this.edit_view_tab.attr( 'id', this.ui_id + '_edit_view_tab' );
		this.setTabOVisibility( false );
		this.edit_view_tab = this.edit_view_tab.tabs( {
			activate: function( e, ui ) {
				$this.onTabShow( e, ui );
			}
		} );

		this.edit_view_tab.bind( 'tabsselect', function( e, ui ) {
			$this.onTabIndexChange( e, ui );
		} );

		if ( this.folder_id == 10 ) {
			this.navigation_label = $.i18n._( 'From' ) + ':';
		} else if ( this.folder_id == 20 ) {
			this.navigation_label = $.i18n._( 'To' ) + ':';
		}

		Global.contentContainer().append( this.edit_view );

		this.initRightClickMenu( RightClickMenuType.EDITVIEW );

		this.buildEditViewUI();
		this.setEditViewTabHeight();
	},

	getViewSelectedRecordId: function( record ) {
		// overriden from BaseVC due to the this.getRecordFromGridById call
		var selected_item;
		var selected_id;
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;

		if ( Global.isSet( record ) ) {
			selected_item = record; // If the next_selected_item is defined, first to use this variable.

		} else if ( grid_selected_length > 0 ) {
			selected_item = this.getRecordFromGridById( grid_selected_id_array[0] );
		} else {
			TTPromise.reject( 'MessageControllViewController', 'onViewClick' );
			return null;
		}

		if ( selected_item.object_type_id == 50 ) {
			selected_id = selected_item.object_id;
			this.is_request = true;
			this.is_message = false;
		} else {
			selected_id = selected_item.id;
			this.is_request = false;
			this.is_message = true;
		}

		return selected_item;
	},

	getCurrentSelectedRecord: function( return_object ) {
		var selected_item = this.current_selected_record;
		if ( !selected_item ) {
			return false;
		}

		var selected_id;
		if ( selected_item.object_type_id && selected_item.object_type_id == 50 ) {
			selected_id = selected_item.object_id;
		} else {
			selected_id = selected_item.id;
		}

		// current_selected_record normally handles ID's, but for MessageControlVC we will be using the record object as this is needed in various places.
		if ( return_object === true ) {
			return selected_item;
		} else {
			return selected_id;
		}
	},

	handleViewAPICallbackResult: function( result ) {
		var result_data;
		if ( result && result.getResult ) {
			result_data = result.getResult();
			if ( this.is_request ) {
				result_data = result_data[0];
			} else {
				// Note that we dont want to take just the first record if its not a request. Requests (and most other onView pages, only have one record. But messages can have multiple records in the results data.
				result_data = result_data.length > 1 ? result_data.reverse() : result_data[0];
			}
		} else {
			result_data = result;
		}

		return this._super( 'handleViewAPICallbackResult', result_data );
	},

	doViewAPICall: function( filter ) {
		var callback = { onResult: this.handleViewAPICallbackResult.bind( this ) };

		if ( this.is_request ) {
			return this.request_api.getRequest( filter, callback );
		} else {
			return this.api.getMessage( filter, callback );
		}
	},

	doViewClickResult: function( result_data ) {
		// save current select grid data. Not this not work when access from url action. See autoOpenEditView function for why
		this.current_select_message_control_data = this.getCurrentSelectedRecord( true );

		//if access from url, current_select_message_control_data need be get again
		if ( !this.current_select_message_control_data.hasOwnProperty( 'to_user_id' ) ) {
			var filter = { filter_data: { id: this.current_select_message_control_data.id } };
			var message_control_data = this.api.getMessageControl( filter, { async: false } ).getResult()[0];

			if ( message_control_data ) {
				this.current_select_message_control_data = message_control_data;
			}

		}
		var retval = this._super( 'doViewClickResult', result_data );
		TTPromise.resolve( 'MessageControllViewController', 'onViewClick' );
		// The promise must be resolved last, after everthing else, hence the specific order here with retval and the super.
		return retval;
	},

	getAPIFilters: function() {
		var record_id = this.getCurrentSelectedRecord();
		var filter = {};

		filter.filter_data = {};
		filter.filter_data.id = record_id;

		return filter;
	},

	onViewClick: function( next_selected_item, noRefreshUI ) {
		TTPromise.add( 'MessageControllViewController', 'onViewClick' );
		TTPromise.wait();
		var $this = this;

		this.setCurrentEditViewState( 'view' );

		$this.isReloadViewUI = true;

		var selected_item = this.getViewSelectedRecordId( next_selected_item );
		if ( Global.isFalseOrNull( selected_item ) ) {
			return;
		}
		this.setCurrentSelectedRecord( selected_item );

		var filter = this.getAPIFilters();
		this.openEditView();

		return this.doViewAPICall( filter );
	},

	/* jshint ignore:start */
	setURL: function() {

		if ( LocalCacheData.current_doing_context_action === 'edit' ) {
			LocalCacheData.current_doing_context_action = '';
			return;
		}

		var a = '';
		switch ( LocalCacheData.current_doing_context_action ) {
			case 'new':
			case 'edit':
			case 'view':
				a = LocalCacheData.current_doing_context_action;
				break;
			case 'copy_as_new':
				a = 'new';
				break;
		}

		if ( this.canSetURL() ) {
			var tab_name = this.edit_view_tab ? this.edit_view_tab.find( '.edit-view-tab-bar-label' ).children().eq( this.getEditViewTabIndex() ).text() : '';
			tab_name = tab_name.replace( /\/|\s+/g, '' );
			if ( this.current_select_message_control_data && this.current_select_message_control_data.id ) {
				if ( a ) {

					if ( this.is_request ) {
						Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId +
							'&a=' + a + '&id=' + this.current_select_message_control_data.id +
							'&t=request&object_id=' + this.current_select_message_control_data.object_id +
							'&tab=' + tab_name );
					} else {
						Global.setURLToBrowser( Global.getBaseURL() +
							'#!m=' + this.viewId + '&a=' +
							a + '&id=' + this.current_select_message_control_data.id + '&t=message' +
							'&tab=' + tab_name );
					}

				}

				Global.trackView();

			} else {
				if ( a ) {
					//Edit a record which don't have id, schedule view Recurring Scedule
					if ( a === 'edit' ) {
						Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&a=new&t=' + ( this.is_request ? 'request' : 'message' ) +
							'&tab=' + tab_name );
					} else {
						Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&a=' + a + '&t=' + ( this.is_request ? 'request' : 'message' ) +
							'&tab=' + tab_name );
					}

				} else {
					Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId );
				}
			}

		}
	},
	/* jshint ignore:end */
	initEditViewData: function() {
		var $this = this;
		if ( !this.edit_only_mode && this.navigation ) {

			var grid_current_page_items = this.grid.getGridParam( 'data' );

			var navigation_div = this.edit_view.find( '.navigation-div' );
			var navigation_source_data;

			//because I will always get this in onViewClick, so else branch should never be in
			if ( this.current_select_message_control_data && this.current_select_message_control_data.hasOwnProperty( 'id' ) &&
				this.current_select_message_control_data.hasOwnProperty( 'subject' ) ) {
				navigation_source_data = this.current_select_message_control_data;
			} else {
				navigation_source_data = Global.isArray( this.current_edit_record ) ? this.current_edit_record[0] : this.current_edit_record;
			}

			this.navigation_source_data = navigation_source_data;

			if ( this.is_viewing && Global.isSet( navigation_source_data.id ) && navigation_source_data.id ) {
				navigation_div.css( 'display', 'block' );
				//Set Navigation Awesomebox

				//init navigation only when open edit view
				if ( !this.navigation.getSourceData() ) {
					this.navigation.setSourceData( grid_current_page_items );
					this.navigation.setRowPerPage( LocalCacheData.getLoginUserPreference().items_per_page );
					this.navigation.setPagerData( this.pager_data );

//					this.navigation.setDisPlayColumns( this.buildDisplayColumnsByColumnModel( this.grid.getGridParam( 'colModel' ) ) );

					var default_args = {};
					default_args.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
					default_args.filter_sort = this.select_layout.data.filter_sort;
					this.navigation.setDefaultArgs( default_args );
				}

				this.navigation.setValue( navigation_source_data );

			} else {
				navigation_div.css( 'display', 'none' );
			}
		}

		this.setUIWidgetFieldsToCurrentEditRecord();

		this.setNavigationArrowsStatus();

		// Create this function alone because of the column value of view is different from each other, some columns need to be handle specially. and easily to rewrite this function in sub-class.

		this.setCurrentEditRecordData();

		//Init *Please save this record before modifying any related data* box
		this.edit_view.find( '.save-and-continue-div' ).SaveAndContinueBox( { related_view_controller: this } );
		this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'none' );
	},

	setNavigation: function() {

		var $this = this;

		this.navigation.setPossibleDisplayColumns( this.buildDisplayColumnsByColumnModel( this.grid.getGridParam( 'colModel' ) ),
			this.buildDisplayColumns( this.default_display_columns ) );

		this.navigation.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {

			var key = target.getField();
			var next_select_item = target.getValue( true );

			if ( !next_select_item ) {
				return;
			}

			if ( next_select_item.id !== $this.navigation_source_data.id ) {
				ProgressBar.showOverlay();

				if ( $this.is_viewing ) {
					$this.onViewClick( next_select_item ); //Dont refresh UI
				} else {
					$this.onEditClick( next_select_item ); //Dont refresh UI
				}

			}

			$this.setNavigationArrowsEnabled();

		} );

	},

	onEditClick: function( editId, noRefreshUI ) {
		// edit click is clicking on Reply
		this.setCurrentEditViewState( 'edit' );
		this.is_request = false;
		this.is_message = false;

		var grid_selected_id_array = this.getGridSelectIdArray();
		var selected_item = {};

		if ( this.edit_view ) {
			selected_item = this.current_select_message_control_data;
		} else { // click Reply on list view.
			selected_item = this.getRecordFromGridById( grid_selected_id_array[0] );
		}

		this.current_edit_record = selected_item;
		this.initEditViewUI( this.viewId, this.edit_view_tpl );
		this.initEditView();

	},

	buildEditViewUI: function() {
		// Builds the fields for Add and Edit, and partially for Requests. But fields for Messages and some of requests are done dynamically in setMessages (Both) and initEmbeddedMessageData (Request only)

		var pager_data = this.navigation && this.navigation.getPagerData && this.navigation.getPagerData();
		var source_data = this.navigation && this.navigation.getSourceData && this.navigation.getSourceData();
		this._super( 'buildEditViewUI' );
		var $this = this;

		// This is actually updated in switchMessageOrRequestWidgets depending on view type
		var tab_model = {
			'tab_message': { 'label': $.i18n._( 'Message' ) }
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIMessageControl' ) ),
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: ALayoutIDs.MESSAGE_USER,
			navigation_mode: true,
			show_search_inputs: true
		} );
		this.setNavigation();

		if ( pager_data && source_data ) {
			this.navigation.setSourceData( source_data );
			this.navigation.setPagerData( pager_data );
		}

		//Tab 0 start

		var form_item_input;

		var tab_message = this.edit_view_tab.find( '#tab_message' );

		var tab_message_column1 = tab_message.find( '.first-column' );

		var tab_message_column2 = tab_message.find( '.second-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_message_column1 );
		this.edit_view_tabs[0].push( tab_message_column2 );
		tab_message_column2.css( 'display', 'none' );

		// Now set the fields up

		// 'Message' fields
		// #2775 'Message' message threads fields now dynamically built on the fly in setMessages()

		// 'Request' fields

		// Employee
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'full_name', selected_able: true } );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_message_column1, '', null, true );

		// Date
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'date_stamp', selected_able: true } );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_message_column1, '', null, true );

		// Type
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'type', selected_able: true } );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_message_column1, '', null, true );

		// tab_message first column end

		// 'Request' Separated Box for 'Messages' Header

		var separated_box = tab_message.find( '.separate' );

		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Messages' ) } );
		this.addEditFieldToColumn( null, form_item_input, separated_box, '', null, true, null, 'separated_box' );

		// #2775 Request message thread fields now generated by initEmbeddedMessageData() and setMessages()

		// Tab 0 second column end

		// 'New (add)' and 'Reply (edit)' fields

		// Employee - 'New' view
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: ( APIFactory.getAPIClass( 'APIMessageControl' ) ),
			column_option_key: 'user_columns',
			allow_multiple_selection: true,
			layout_name: ALayoutIDs.MESSAGE_USER,
			show_search_inputs: true,
			set_empty: true,
			custom_key_name: 'User',
			field: 'to_user_id'
		} );
		var default_args = {};
		default_args.permission_section = 'message';
		form_item_input.setDefaultArgs( default_args );
		this.addEditFieldToColumn( $.i18n._( 'Employee(s)' ), form_item_input, tab_message_column1, '', null, true );

		// Employee(s) - 'Reply' view
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'from_full_name' } );
		this.addEditFieldToColumn( $.i18n._( 'Employee(s)' ), form_item_input, tab_message_column1, '', null, true );

		// Subject - shared with the new/add & reply/edit view
		// Dev Note, in old reply view code, the width was passed as 359. Should that be incorporated here?
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'subject' } );
		this.addEditFieldToColumn( $.i18n._( 'Subject' ), form_item_input, tab_message_column1, '', null, true );

		// Body  - shared with the new/add & reply/edit view
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'body', width: 600, height: 400 } );
		this.addEditFieldToColumn( $.i18n._( 'Body' ), form_item_input, tab_message_column1, '', null, true, true );
	},

	setEditViewWidgetsMode: function() {
		this.switchMessageOrRequestWidgets();
		this._super( 'setEditViewWidgetsMode' );
	},
	switchMessageOrRequestWidgets: function() {
		// UI field building is done from buildEditViewUI(), and setMessages() for Messages and Requests (Also initEmbeddedMessageData).
		// This function shows/hides various fields depending on whether the view is displaying a message or request, to reduce re-building the form elements, instead simply hiding and showing the right ones.
		var tab_label;

		// Detach all fields, further down we just attach the ones we need for the view
		for ( var key in this.edit_view_ui_dic ) {
			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}
			this.detachElement( key );
		}

		// Detach the Request Messages header label box and hide parent container
		this.detachElement( 'separated_box' );

		// Remove message list for both Message type and Request type - Message UI fields do not detach, we just remove them, as they are dynamically built on the fly.
		this.edit_view_tab.find( '#tab_message' ).find( '.edit-view-tab .second-column.message-container' ).remove();

		if ( this.is_request ) {
			tab_label = 'Request';
			this.attachElement( 'full_name' );
			this.attachElement( 'date_stamp' );
			this.attachElement( 'type' );
			this.attachElement( 'separated_box' );

			// In show the main container which holds the fields.
			this.edit_view_tab.find( '#tab_message' ).find( '.edit-view-tab .first-column' ).show();

		} else if ( this.is_message ) {
			tab_label = 'Message';
			// #2775 No longer attaching elements here, as the Messages fields are dynamically built in setMessages

			// Hide the first-column field, as this is not used by messages, but causes a border to be shown at the top. Hiding only here rather than at the top, to reduce flashing (if any)
			this.edit_view_tab.find( '#tab_message' ).find( '.edit-view-tab .first-column' ).hide();

		} else if ( this.is_add ) {
			tab_label = 'New Message';
			this.attachElement( 'to_user_id' );
			this.attachElement( 'subject' );
			this.attachElement( 'body' );

			// Show the main container which holds the fields.
			this.edit_view_tab.find( '#tab_message' ).find( '.edit-view-tab .first-column' ).show();

		} else if ( this.is_edit ) {
			tab_label = 'Reply';
			this.attachElement( 'from_full_name' );
			this.attachElement( 'subject' );
			this.attachElement( 'body' );

			// Show the main container which holds the fields.
			this.edit_view_tab.find( '#tab_message' ).find( '.edit-view-tab .first-column' ).show();
		}

		var tab_model = {
			'tab_message': { 'label': $.i18n._( tab_label ) }
		};
		this.setTabModel( tab_model );

	},

	refreshCurrentRecord: function() {
		var next_select_item = this.navigation.getItemByIndex( this.navigation.getSelectIndex() );
		ProgressBar.showOverlay();
		this.onViewClick( next_select_item ); //Dont refresh UI
		this.setNavigationArrowsEnabled();
	},

	onRightOrLeftArrowClickCallBack: function( next_select_item ) {
		ProgressBar.showOverlay();
		this.onViewClick( next_select_item ); //Dont refresh UI
		this.setNavigationArrowsEnabled();
	},

	onAddClick: function() {

		TTPromise.add( 'Message', 'add' );
		TTPromise.wait();
		var $this = this;
		this.is_viewing = false;
		this.is_edit = false;
		this.is_add = true;
		this.isReloadViewUI = false;
		LocalCacheData.current_doing_context_action = 'new';
		this.is_request = false;
		this.is_message = false;
		$this.openEditView();

		var result_data = {};

		$this.current_edit_record = result_data;
		$this.initEditView();
	},

	initEditView: function() {
		this._super( 'initEditView' );
		TTPromise.resolve( 'Message', 'add' );
	},

	setEditMenuAddIcon: function( context_btn, pId ) {

		if ( this.is_add || this.is_changed ) {
			context_btn.addClass( 'disable-image' );
		} else {
			context_btn.removeClass( 'disable-image' );
		}

	},

	setEditMenuViewIcon: function( context_btn, pId ) {
		context_btn.addClass( 'disable-image' );
	},

	setEditMenuEditIcon: function( context_btn, pId ) {

		if ( !this.is_viewing ) {
			context_btn.addClass( 'disable-image' );
		}
	},

	setEditMenuDeleteIcon: function( context_btn, pId ) {
		if ( !this.current_select_message_control_data ||
			this.is_edit ||
			this.is_add ) {

			context_btn.addClass( 'disable-image' );
		}

	},

	setEditMenuDeleteAndNextIcon: function( context_btn, pId ) {

		if ( !this.current_select_message_control_data ||
			this.is_edit ||
			this.is_add ) {

			context_btn.addClass( 'disable-image' );
		}
	},

	validate: function() {

		var $this = this;

		var record = this.current_edit_record;

		if ( Global.isSet( this.edit_view_ui_dic['subject'] ) ) {
			record.subject = this.edit_view_ui_dic['subject'].getValue();
		} else if ( Global.isSet( this.edit_view_ui_dic['message_subject'] ) ) {
			record.subject = this.edit_view_ui_dic['message_subject'].getValue();
		} else if ( Global.isSet( this.edit_view_ui_dic['request_subject'] ) ) {
			record.subject = this.edit_view_ui_dic['request_subject'].getValue();
		}
		record = this.uniformVariable( record );

		this.api['validate' + this.api.key_name]( record, {
			onResult: function( result ) {
				$this.validateResult( result );

			}
		} );
	},

	onSaveClick: function( ignoreWarning ) {
		LocalCacheData.current_doing_context_action = 'save';
		this.collectUIDataToCurrentEditRecord();
		var record = this.current_edit_record;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		if ( Global.isSet( this.edit_view_ui_dic['subject'] ) ) {
			record.subject = this.edit_view_ui_dic['subject'].getValue();
		} else if ( Global.isSet( this.edit_view_ui_dic['message_subject'] ) ) {
			record.subject = this.edit_view_ui_dic['message_subject'].getValue();
		} else if ( Global.isSet( this.edit_view_ui_dic['request_subject'] ) ) {
			record.subject = this.edit_view_ui_dic['request_subject'].getValue();
		}
		record = this.uniformVariable( record );

		this.doSaveAPICall( record, ignoreWarning );
	},

	uniformVariable: function( records ) {
		var reply_data = {};

		if ( this.is_edit ) {

			reply_data.subject = records.subject;
			reply_data.body = records.body;

			// message
			if ( records.object_type_id != 50 ) {
				reply_data.to_user_id = records.from_user_id;
				reply_data.object_type_id = 5;
				reply_data.object_id = LocalCacheData.loginUser.id;
				reply_data.parent_id = records.id;

			} else {
				// request

				reply_data.object_id = records.object_id;

				reply_data.to_user_id = LocalCacheData.loginUser.id;
				reply_data.object_type_id = 50;

				reply_data.parent_id = 1;
			}

			return reply_data;

		}

		if ( this.is_add ) {
			records.object_type_id = 5;
			records.object_id = LocalCacheData.loginUser.id;
			records.parent_id = 0;
		}

		return records;
	},
	/* jshint ignore:start */
	setCurrentEditRecordData: function() {
		var $this = this;
		// If the current_edit_record is an array, then handle them in setEditViewDataDone function.
		// if ( Global.isArray( this.current_edit_record ) ) { // Commenting out to trial whether single messages can go through this too.
		if ( this.is_message ) {
			this.setMessages();
		} else {
			// TODO: Figure out where to trigger the uniformVariable work on splitting out the subject and body for msg/req stuff. Here or in above section, as multiple messages go elsewhere???

			//Set current edit record data to all widgets
			for ( var key in this.current_edit_record ) {
				if ( !this.current_edit_record.hasOwnProperty( key ) ) {
					continue;
				}

				var widget = this.edit_view_ui_dic[key];
				if ( Global.isSet( widget ) ) {
					// Now that all messages go through setMessages() and no longer through the below, we can remove some of the fields below. Not done yet as theres Add/Edit to consider and test first.
					switch ( key ) {
						case 'from_full_name':
							widget.setValue( this.current_edit_record['from_first_name'] + ' ' + this.current_edit_record['from_last_name'] );
							break;
						case 'to_full_name':
							widget.setValue( this.current_edit_record['to_first_name'] + ' ' + this.current_edit_record['to_last_name'] );
							break;
						case 'full_name':
							widget.setValue( this.current_edit_record['first_name'] + ' ' + this.current_edit_record['last_name'] );
							break;
						case 'subject':
							if ( this.is_edit ) {
								if ( Global.isArray( this.messages ) ) {
									widget.setValue( 'Re: ' + this.messages[0].subject );
								} else {
									widget.setValue( 'Re: ' + this.current_edit_record[key] );
								}

							} else if ( this.is_viewing ) {
								widget.setValue( this.current_edit_record[key] );
							}
							break;
						case 'message_body':
						case 'request_body':
							widget.setValue( this.current_edit_record[key] );
							break;
						default:
							widget.setValue( this.current_edit_record[key] );
							break;
					}
				}
			}
			//request will do this when initEmbeddedMessage
			if ( this.is_message && this.current_edit_record && this.current_edit_record.status_id == 10 ) {
				this.api['markRecipientMessageAsRead']( [this.current_edit_record.id], {
					onResult: function( res ) {
						$this.search( false );
					}
				} );
			}

			this.collectUIDataToCurrentEditRecord(); // #2775 If Messages then, we do not want to store any ui fields to current_edit_record. Its view only, and we dont have references to each generated message anyway, as they generate on the fly.
		}
		this.setEditViewDataDone(); // 2775 notes: also trigger more data/widget handling for request (SINGLE+MULTIPLE)
	},
	/* jshint ignore:end */
	autoOpenEditViewIfNecessary: function() {
		//Auto open edit view. Should set in IndexController

		switch ( LocalCacheData.current_doing_context_action ) {
			case 'view':
				if ( LocalCacheData.edit_id_for_next_open_view ) {
					var item = {};
					item.id = LocalCacheData.edit_id_for_next_open_view;
					if ( LocalCacheData.all_url_args.t === 'request' ) {
						item.object_id = LocalCacheData.all_url_args.object_id;
						item.object_type_id = 50;
					}
					this.onViewClick( item );
					LocalCacheData.edit_id_for_next_open_view = null;
				}
				break;
			case 'new':
				this.onAddClick();
				break;
		}

		this.autoOpenEditOnlyViewIfNecessary();

	},

	getDeleteSelectedRecordId: function() {
		var retval = [];
		if ( this.edit_view ) {
			if ( !this.current_select_message_control_data ) {
				TAlertManager.showAlert( $.i18n._( 'Invalid Message id' ) );
				return;
			}
			retval.push( this.current_select_message_control_data.id );
		} else {
			retval = this._super( 'getDeleteSelectedRecordId' );
		}
		return retval;
	},

	doDeleteAPICall: function( remove_ids, _callback ) {
		var callback = _callback || {
			onResult: function( result ) {
				this.isReloadViewUI = false;
				this.onDeleteResult( result, remove_ids );
			}.bind( this )
		};
		return this.api['delete' + this.api.key_name]( remove_ids, this.folder_id, callback );
	},

	setEditViewDataDone: function() {
		// TODO: Refactor this to move into setCurrentEditRecordData, as this is not code that is classed as Data Load Done, its still data loading.
		var $this = this;
		this._super( 'setEditViewDataDone' );

		if ( this.is_viewing ) {

			if ( this.is_request ) {
				this.initEmbeddedMessageData();
			}

		} else {

			if ( Global.isSet( $this.messages ) ) {
				$this.messages = null;
			}
		}

	},

	setMessages: function( message_data ) {
		// This function handles message thread generation for both the message and request types.
		var read_ids = [];

		if ( message_data ) {
			this.messages = message_data;
		} else {
			this.messages = this.current_edit_record;
		}

		if ( !Global.isArray( this.messages ) ) {
			// This function works on an array of messages. If there is only one message, then provide an array of one message and process the same way.
			this.messages = [this.messages];
		}

		// Remove all old messages first.
		this.edit_view_tab.find( '#tab_message' ).find( '.edit-view-tab .second-column.message-container' ).remove();

		/*
		 * Loop through and create the message fields
		 */

		// Collection container for the messages to be held in, until they are added in one go to the page.
		var container = $( '<div></div>' );

		for ( var key = 0; key < this.messages.length; key++ ) {

			var current_item = this.messages[key];
			if ( !current_item.hasOwnProperty( 'id' ) ) {
				continue;
			}

			if ( current_item.status_id == 10 ) {
				read_ids.push( current_item.id );
			}

			var message_container = $( '<div></div>', { class: "second-column full-width-column message-container" } );

			if ( this.is_message ) {
				this.addMessageRow( message_container, 'From', 'msg_from_full_name', current_item['from_first_name'] + ' ' + current_item['from_last_name'] );
				this.addMessageRow( message_container, 'To', 'msg_to_full_name', current_item['to_first_name'] + ' ' + current_item['to_last_name'] );
				this.addMessageRow( message_container, 'Date', 'msg_updated_date', current_item['updated_date'] );
				this.addMessageRow( message_container, 'Subject', 'msg_subject', current_item['subject'] );
				this.addMessageRow( message_container, 'Body', 'msg_body', current_item['body'], true );

			} else if ( this.is_request ) {
				this.addMessageRow( message_container, 'From', 'req_from_full_name', current_item['from_first_name'] + ' ' + current_item['from_last_name'] + '@' + current_item['updated_date'] );
				this.addMessageRow( message_container, 'Subject', 'req_subject', current_item['subject'] );
				this.addMessageRow( message_container, 'Body', 'req_body', current_item['body'], true );

			} else {
				// Error: Message type not supported. Exit. Currently only messages and request types supported.
				return;
			}

			container.append( message_container );
		}

		// Add the new message to the page
		this.edit_view_tab.find( '#tab_message' ).find( '.edit-view-tab' ).append( container.html() );

		if ( read_ids.length > 0 ) {
			var $this = this;
			this.api['markRecipientMessageAsRead']( read_ids, {
				onResult: function( res ) {
					$this.search( false );
				}
			} );
		}
	},

	addMessageRow: function( message_container, label, field, value, set_resize_event ) {
		// Note: Take extra care with this function, as we are building widgets outside of the normal init flow, so compare to the standard flow of buildEditViewUI if anything odd happens.

		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: field, selected_able: true } );
		this.addEditFieldToColumn( $.i18n._( label ), form_item_input, message_container, '', null, null, set_resize_event );

		// #2775 You must set the value after its added to column, not before, otherwise the field label will not resize after a large value is set.
		form_item_input.setValue( value );

		// #2775 You must set the opacity to 1 after adding to column, as the addEditFieldToColumn sets opacity to 0 during loading, and normally set back to 1 at the bottom of BaseVC.initEditViewData but here we are building widgets outside of the normal init flow.
		form_item_input.css( 'opacity', '1' );

		// remove the field reference from this.edit_view_ui_dic as we wont track the on-the-fly built fields.
		delete this.edit_view_ui_dic[field];
	},

	initEmbeddedMessageData: function() {
		// Used to generate the message threads for a Request type
		var $this = this;
		var args = {};
		args.filter_data = {};
		args.filter_data.object_type_id = 50;
		args.filter_data.object_id = this.current_edit_record.id;

		$this.api['getEmbeddedMessage']( args, {
			onResult: function( res ) {

				if ( !$this.edit_view ) {
					return;
				}

				var data = res.getResult();
				$this.setMessages( data );
			}
		} );
	},
	// #2775 Commenting out to fix an issue where Delete&Next does not go to the next record. Not 100% certain why this is here, but annotations show something to do with flashing, which does not seem an issue atm.
	// /* jshint ignore:start */
	// search: function( set_default_menu, page_action, page_number, callBack ) {
	// 	this.refresh_id = null;
	// 	this._super( 'search', set_default_menu, page_action, page_number, callBack );
	// }
	//
	// /* jshint ignore:end */

} );
