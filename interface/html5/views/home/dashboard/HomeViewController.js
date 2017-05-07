HomeViewController = Backbone.View.extend( {

	el: '.home-view',
	user_generic_data_api: null,
	context_menu_array: null,
	viewId: null,
	dashletControllerArray: null,
	initMasonryDone: false,
	dashboard_container: false,
	order_data: false,
	current_scroll_position: false,

	initialize: function( options ) {
		Global.setUINotready();
		TTPromise.add('init','init');
		TTPromise.wait();

		this.viewId = 'Home';
		LocalCacheData.current_open_primary_controller = this;
		this.user_generic_data_api = new (APIFactory.getAPIClass( 'APIUserGenericData' ))();
		this.api_dashboard = new (APIFactory.getAPIClass( 'APIDashboard' ))();
		this.dashboard_container = $( this.el ).find( '.dashboard-container' );
		this.initMasonryDone = false;
		this.initContextMenu();
		this.initDashBoard();
		this.setViewHeight();
		this.autoOpenEditOnlyViewIfNecessary();
	},

	autoOpenEditOnlyViewIfNecessary: function() {
		if ( LocalCacheData.all_url_args && LocalCacheData.all_url_args.sm && !LocalCacheData.current_open_edit_only_controller ) {
			if ( LocalCacheData.all_url_args.sm.indexOf( 'Report' ) < 0 ) {
				IndexViewController.openEditView( this, LocalCacheData.all_url_args.sm, LocalCacheData.all_url_args.sid );
			} else {
				IndexViewController.openReport( this, LocalCacheData.all_url_args.sm );
				if ( LocalCacheData.all_url_args.sid ) {
					LocalCacheData.default_edit_id_for_next_open_edit_view = LocalCacheData.all_url_args.sid;
				}
			}
		}
	},

	initContextMenu: function() {
		var $this = this;
		this.buildContextMenu();
		this.setDefaultMenu();
		$( this.el ).unbind( 'click' ).bind( 'click', function() {
			$this.setDefaultMenu();
		} );
	},

	buildContextMenu: function() {
		var $this = this;
		LocalCacheData.current_open_sub_controller = null;
		this.context_menu_array = [];
		var ribbon_menu_array = this.buildContextMenuModels();
		var ribbon_menu_label_node = $( '.ribbonTabLabel' );
		var ribbon_menu_root_node = $( '.ribbon' );
		var len = ribbon_menu_array.length;
		var ribbon_menu;
		for ( var i = 0; i < len; i++ ) {
			ribbon_menu = ribbon_menu_array[i];
			var ribbon_menu_group_array = ribbon_menu.get( 'sub_menu_groups' );
			var ribbon_menu_ui = $( '<div id="' + ribbon_menu.get( 'id' ) + '" class="ribbon-tab-out-side ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide"><div class="context-ribbon-tab"><div class="ribbon-sub-menu"></div></div></div>' );
			//make sure only one context menu shown at a time
			if ( Global.isSet( LocalCacheData.currentShownContextMenuName ) && LocalCacheData.currentShownContextMenuName !== ribbon_menu.get( 'id' ) ) {
				this.removeContentMenuByName( LocalCacheData.currentShownContextMenuName );

			} else if ( Global.isSet( LocalCacheData.currentShownContextMenuName ) && LocalCacheData.currentShownContextMenuName === ribbon_menu.get( 'id' ) ) {
				return;
			}
			this.subMenuNavMap = {};
			LocalCacheData.currentShownContextMenuName = ribbon_menu.get( 'id' );
			var len1 = ribbon_menu_group_array.length;
			for ( var x = 0; x < len1; x++ ) {
				var ribbon_menu_group = ribbon_menu_group_array[x];
				var ribbon_sub_menu_array = ribbon_menu_group.get( 'sub_menus' );
				var sub_menu_ui_nodes = $( "<ul></ul>" );
				var ribbon_menu_group_ui = $( '<div class="menu top-ribbon-menu"  ondragstart="return false;" />' );
				var len2 = ribbon_sub_menu_array.length;
				for ( var y = 0; y < len2; y++ ) {
					var ribbon_sub_menu = ribbon_sub_menu_array[y];
					if ( ribbon_sub_menu.get( 'selected' ) ) {
						var sub_menu_ui_node = $( '<li ><div class="ribbon-sub-menu-icon selected-menu" id="' + ribbon_sub_menu.get( 'id' ) + '"><img src="' + ribbon_sub_menu.get( 'icon' ) + '" ><span class="ribbon-label">' + ribbon_sub_menu.get( 'label' ) + '</span></div></li>' );
					} else {
						sub_menu_ui_node = $( '<li ><div class="ribbon-sub-menu-icon" id="' + ribbon_sub_menu.get( 'id' ) + '"><img src="' + ribbon_sub_menu.get( 'icon' ) + '" ><span class="ribbon-label">' + ribbon_sub_menu.get( 'label' ) + '</span></div></li>' );
					}
					this.context_menu_array.push( sub_menu_ui_node );
					if ( ribbon_sub_menu.get( 'type' ) === RibbonSubMenuType.NAVIGATION ) {
						sub_menu_ui_node.children().eq( 0 ).addClass( 'ribbon-sub-menu-nav-icon' );
						$this.subMenuNavMap[ribbon_sub_menu.get( 'id' )] = ribbon_sub_menu;
						sub_menu_ui_node.click( function( e ) {
							var id = $( $( this ).find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
							$this.onSubMenuNavClick( this, id );
						} );
					} else {
						//defend empty block error when comments following codes
						sub_menu_ui_node.click( function( e ) {
							var id = $( $( this ).find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
							$this.onContextMenuClick( this );
						} );
					}
					sub_menu_ui_nodes.append( sub_menu_ui_node );
				}
				if ( sub_menu_ui_nodes.children().length > 0 ) {
					ribbon_menu_group_ui.append( sub_menu_ui_nodes );
					ribbon_menu_group_ui.append( $( '<div class="menu-bottom"><span>' + ribbon_menu_group.get( 'label' ) + '</span></div>' ) );
					ribbon_menu_ui.find( '.ribbon-sub-menu' ).append( ribbon_menu_group_ui );
				}
			}
			ribbon_menu_label_node.append( $( '<li class="context-menu ui-state-default ui-corner-top"><a ref="' + ribbon_menu.get( 'id' ) + '" href="#' + ribbon_menu.get( 'id' ) + '">' + ribbon_menu.get( 'label' ) + '</a></li>' ) );
			ribbon_menu_root_node.append( ribbon_menu_ui );
		}
		//Register ribbon menu to tab widget
		$( '#ribbon_view_container' ).tabs( 'add', '#' + ribbon_menu.get( 'id' ) );
		$( '#ribbon_view_container' ).tabs( 'remove', ($( '#ribbon_view_container' ).tabs( 'length' ) - 1) );

	},

	onContextMenuClick: function( context_btn ) {
		var $this = this;
		context_btn = $( context_btn );
		var id = $( context_btn.find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
		if ( context_btn.hasClass( 'disable-image' ) ) {
			return;
		}
		switch ( id ) {
			case ContextMenuIconName.add:
				ProgressBar.showOverlay();
				this.onAddClick();
				break;
			case ContextMenuIconName.refresh_all:
				ProgressBar.showOverlay();
				for ( var i = 0; i < $this.dashletControllerArray.length; i++ ) {
					$( $this.dashletControllerArray[i].el ).find( '.refresh-btn' ).trigger( 'click' );
				}
				break;
			case ContextMenuIconName.auto_arrange:
				TAlertManager.showConfirmAlert( Global.auto_arrange_dashlet_confirm_message, null, function( result ) {
					if ( result ) {
						ProgressBar.showOverlay();
						$this.initDashBoard( true );
					} else {
						ProgressBar.closeOverlay();
					}
				} );
				break;
			case ContextMenuIconName.reset_all:
				TAlertManager.showConfirmAlert( Global.rese_all_dashlet_confirm_message, null, function( result ) {
					if ( result ) {
						ProgressBar.showOverlay();
						var ids = [];
						for ( var i = 0; i < $this.dashlet_list.length; i++ ) {
							ids.push( $this.dashlet_list[i].id );
						}
						if ( ids.length > 0 ) {
							$this.user_generic_data_api.deleteUserGenericData( ids, {
								onResult: function( result ) {
									if ( result.isValid() ) {
										doResetAllNext();
									} else {
										TAlertManager.showErrorAlert( result );
									}
								}
							} )
						} else {
							doResetAllNext();
						}

						function doResetAllNext() {
							if ( $this.order_data ) {
								$this.user_generic_data_api.deleteUserGenericData( $this.order_data.id, {
									onResult: function() {
										$this.initDashBoard();
									}
								} );
							} else {
								$this.initDashBoard();
							}
						}

					} else {
						ProgressBar.closeOverlay();
					}
				} );
				break;
			case ContextMenuIconName.in_out:
			case ContextMenuIconName.timesheet:
			case ContextMenuIconName.schedule:
			case ContextMenuIconName.request:
			case ContextMenuIconName.pay_stub:
				this.onNavigationClick( id );
				break;
		}
	},

	onNavigationClick: function( iconName ) {
		switch ( iconName ) {
			case ContextMenuIconName.in_out:
				IndexViewController.openEditView( LocalCacheData.current_open_primary_controller, 'InOut' );
				break;
			case ContextMenuIconName.timesheet:
				IndexViewController.goToView( 'TimeSheet' );
				break;
			case ContextMenuIconName.schedule:
				IndexViewController.goToView( 'Schedule' );
				break;
			case ContextMenuIconName.request:
				IndexViewController.goToView( 'Request' );
				break;
			case ContextMenuIconName.pay_stub:
				IndexViewController.goToView( 'PayStub' );
				break;
		}
	},

	selectContextMenu: function() {
		var ribbon = $( TopMenuManager.ribbon_view_controller.el );
		ribbon.tabs( {selected: this.viewId + 'ContextMenu'} );
	},

	//Call this when select grid row
	//Call this when setLayout
	setDefaultMenu: function() {
		//Error: Uncaught TypeError: Cannot read property 'length' of undefined in /interface/html5/#!m=Client line 308
		if ( !this.context_menu_array ) {
			return;
		}
		this.selectContextMenu();

	},

	onAddClick: function() {
		var $this = this;
		IndexViewController.openWizard( 'DashletWizard', null, function() {
			$this.initDashBoard();
		} );
	},

	removeContentMenuByName: function( name ) {
		if ( !LocalCacheData.current_open_primary_controller ) {
			return;
		}
		var primary_view_id = LocalCacheData.current_open_primary_controller.viewId;
		var select_menu_id = TopMenuManager.menus_quick_map[primary_view_id];
		if ( TopMenuManager.ribbon_view_controller && TopMenuManager.selected_menu_id && TopMenuManager.selected_menu_id.indexOf( 'ContextMenu' ) !== -1 ) {
			TopMenuManager.ribbon_view_controller.setSelectMenu( select_menu_id );
		}
		if ( !Global.isSet( name ) ) {
			name = this.context_menu_name;
		}
		var tab = $( '#ribbon ul a' ).filter( function() {
			return $( this ).attr( 'ref' ) === name;
		} ).parent();

		var index = $( 'li', $( '#ribbon' ) ).index( tab );
		if ( index >= 0 ) {
			$( '#ribbon_view_container' ).tabs( 'remove', index );
		}
	},

	buildContextMenuModels: function() {

		//Context Menu
		var menu = new RibbonMenu( {
			label: 'Dashboard',
			id: this.viewId + 'ContextMenu',
			sub_menu_groups: []
		} );

		//menu group
		var editor_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Editor' ),
			id: this.viewId + 'Editor',
			ribbon_menu: menu,
			sub_menus: []
		} );

		//navigation group
		var navigation_group = new RibbonSubMenuGroup( {
			label: $.i18n._( 'Navigation' ),
			id: this.viewId + 'navigation',
			ribbon_menu: menu,
			sub_menus: []
		} );

		var add = new RibbonSubMenu( {
			label: $.i18n._( 'Add Dashlet' ),
			id: ContextMenuIconName.add,
			group: editor_group,
			icon: Icons.new_add,
			permission_result: true,
			permission: null
		} );

		var auto_arrange = new RibbonSubMenu( {
			label: $.i18n._( 'Auto Arrange' ),
			id: ContextMenuIconName.auto_arrange,
			group: editor_group,
			icon: Icons.branches,
			permission_result: true,
			permission: null
		} );

		var refresh_all = new RibbonSubMenu( {
			label: $.i18n._( 'Refresh All<br>Dashlets' ),
			id: ContextMenuIconName.refresh_all,
			group: editor_group,
			icon: Icons.restart,
			permission_result: true,
			permission: null
		} );

		var reset_all = new RibbonSubMenu( {
			label: $.i18n._( 'Restore<br>Default Dashlets' ),
			id: ContextMenuIconName.reset_all,
			group: editor_group,
			icon: Icons.save_and_continue,
			permission_result: true,
			permission: null
		} );

		var in_out = new RibbonSubMenu( {
			label: $.i18n._( 'In/Out' ),
			id: ContextMenuIconName.in_out,
			group: navigation_group,
			icon: Icons.in_out,
			permission_result: PermissionManager.checkTopLevelPermission( 'InOut' ),
			permission: null
		} );

		var timesheet_view = new RibbonSubMenu( {
			label: $.i18n._( 'TimeSheet' ),
			id: ContextMenuIconName.timesheet,
			group: navigation_group,
			icon: Icons.timesheet,
			permission_result: PermissionManager.checkTopLevelPermission( 'TimeSheet' ),
			permission: null
		} );

		var schedule_view = new RibbonSubMenu( {
			label: $.i18n._( 'Schedules' ),
			id: ContextMenuIconName.schedule,
			group: navigation_group,
			icon: Icons.schedule,
			permission_result: PermissionManager.checkTopLevelPermission( 'Schedule' ),
			permission: null
		} );

		var request_view = new RibbonSubMenu( {
			label: $.i18n._( 'Requests' ),
			id: ContextMenuIconName.request,
			group: navigation_group,
			icon: Icons.request,
			permission_result: PermissionManager.checkTopLevelPermission( 'Request' ),
			permission: null
		} );

		var pay_stub_view = new RibbonSubMenu( {
			label: $.i18n._( 'Pay<br>Stubs' ),
			id: ContextMenuIconName.pay_stub,
			group: navigation_group,
			icon: Icons.pay_stubs,
			permission_result: PermissionManager.checkTopLevelPermission( 'PayStub' ),
			permission: null
		} );

		return [menu];

	},

	unLoadCurrentDashlets: function() {
		//Error: TypeError: this.dashletControllerArray is null in interface/html5/framework/jquery.min.js?v=9.0.2-20151106-092147 line 2 > eval line 368
		if(this.dashletControllerArray){
			for ( var i = 0; i < this.dashletControllerArray.length; i++ ) {
				var dashletController = this.dashletControllerArray[i];
				dashletController.cleanWhenUnloadView();
			}
		}
		this.dashletControllerArray = [];
	},

	initDashBoard: function( auto_arrange ) {
		var $this = this;
		var i = 0;
		if ( !this.dashletControllerArray ) {
			this.dashletControllerArray = [];
		} else {
			this.unLoadCurrentDashlets();
		}
		if ( this.initMasonryDone ) {
			this.dashboard_container.masonry( 'destroy' );
			this.dashboard_container.sortable( 'destroy' );
			this.dashboard_container.empty();
			this.initMasonryDone = false;
		}
		$this.dashlet_controller_dic = {};
		this.user_generic_data_api.getUserGenericData( {filter_data: {script: ALayoutIDs.DASHBOARD, deleted: false}}, {
			onResult: function( result ) {
				var dashlet_list = result.getResult();
				if ( !Global.isArray( dashlet_list ) || dashlet_list.length < 1 ) {
					$this.api_dashboard.getDefaultDashlets( {
						onResult: function( result ) {
							dashlet_list = result.getResult();
							$this.is_getting_default_dashlet = true;
							doOrder( dashlet_list );
						}
					} );
				} else {
					doOrder( dashlet_list );
				}
			}
		} );

		function doOrder( dashlet_list ) {
			$this.removeNoResultCover();
			$this.user_generic_data_api.getUserGenericData( {
				filter_data: {
					script: ALayoutIDs.DASHBOARD_ORDER,
					name: 'order_data',
					deleted: false
				}
			}, {
				onResult: function( order_result ) {
					order_result = order_result.getResult();
					if ( Global.isArray( order_result ) && order_result.length == 1 ) {
						$this.order_data = order_result[0];
						if ( $this.is_getting_default_dashlet ) {
							$this.order_data.data = [];
							$this.addMissedDashLetToOrder( dashlet_list );
							$this.is_getting_default_dashlet = false;
						} else {
							//Error: Uncaught TypeError: $this.order_data.data.push is not a function in interface/html5/#!m=Home line 550
							if ( !$this.order_data.data || !Global.isArray( $this.order_data.data ) ) {
								$this.order_data.data = [];
							}
							$this.addMissedDashLetToOrder( dashlet_list );
						}
						$this.dashlet_list = [];
						for ( var y = 0, yy = $this.order_data.data.length; y < yy; y++ ) {
							var order_id = $this.order_data.data[y];
							var found = false;
							for ( var j = 0, jj = dashlet_list.length; j < jj; j++ ) {
								var dashlet = dashlet_list[j];
								if ( dashlet.id.toString() === order_id.toString() ) {
									$this.dashlet_list.push( dashlet );
									found = true;
									break;
								}
							}
						}
					} else {
						$this.dashlet_list = dashlet_list;
					}
					if ( $this.dashlet_list.length > 0 ) {
						loadPage( $this.dashlet_list[i] );
					}
				}
			} );
		}

		function loadPage( dashlet_data ) {
			Global.loadScript( 'views/home/dashlet/DashletController.js', function() {
				var id = 'dashlet_' + dashlet_data.id;
				var dash_let = $( '<div class="dashlet-container" id="' + id + '">' +
				'<div class="dashlet">' +
				'<button class="refresh-btn"></button>' +
				'<span class="title"></span>' +
				'<div class="button-bar">' +
				'<button class="view-btn button">View</button>' +
				'<button class="modify-btn button">Edit</button>' +
				'<button class="delete-btn button">Delete</button>' +
				'</div>' +
				'<div class="content">' +
				'<table id="grid"></table>' +
				'<iframe class="report-iframe" id="iframe"></iframe>' +
				'</div>' +
				'</div>' +
				'<div class="dashlet-left-cover" ></div>' +
				'<div class="dashlet-right-cover" ></div>' +
				'</div>' );
				if ( !dashlet_data.data.height || auto_arrange ) {
					dashlet_data.data.height = 200;
				}
				if ( !dashlet_data.data.width || auto_arrange ) {
					if ( dashlet_data.data.dashlet_type === 'custom_report' ) {
						dashlet_data.data.width = 99;
					} else {
						dashlet_data.data.width = 33;
					}
				}
				dash_let.css( 'height', dashlet_data.data.height + 'px' );
				dash_let.css( 'width', dashlet_data.data.width + '%' );
				$this.dashboard_container.append( dash_let );
				dash_let.find( '.button' ).unbind( 'click' ).bind( 'click', function( e ) {
					var target = e.target;
					var container = $( target ).parent().parent().parent();
					// Error: Uncaught TypeError: Cannot read property 'split' of undefined in interface/html5/#!m=Home line 490
					if ( !container.attr( 'id' ) ) {
						return;
					}
					var dashlet_id = container.attr( 'id' ).split( '_' )[1];

					if ( $( target ).hasClass( 'delete-btn' ) ) {
						$this.deleteDashlet( dashlet_id, $( container ) );
					}

					if ( $( target ).hasClass( 'modify-btn' ) ) {
						$this.modifyDashlet( dashlet_id );
					}

				} );
				var dashlet_controller = new DashletController();
				$this.dashletControllerArray.push( dashlet_controller );
				dashlet_controller.el = '#' + id;
				dashlet_controller.data = dashlet_data;
				dashlet_controller.homeViewController = $this;
				dashlet_controller.initContent();
				// Update width and height to default one if doing auto arrange
				if ( auto_arrange ) {
					$this.user_generic_data_api.setUserGenericData( dashlet_data, {
						onResult: function( result ) {
						}
					} );
				}
				i = i + 1;
				if ( i < $this.dashlet_list.length ) {
					loadPage( $this.dashlet_list[i] );
				} else {
					$this.updateLayout();
				}
			} );
		}

		//BUG#2070 - Break sortable for mobile because it negatively impacts usability
		if ( Global.detectMobileBrowser() ) {
			this.dashboard_container.sortable({disabled:true}) ;
		}
	},

	showNoResultCover: function() {
		this.removeNoResultCover();
		this.no_result_box = Global.loadWidgetByName( WidgetNamesDic.NO_RESULT_BOX );
		this.no_result_box.NoResultBox( {
			related_view_controller: this,
			is_new: true,
			message: $.i18n._( 'No Dashlets Found' ),
			iconLabel: $.i18n._( 'Add' )
		} );
		this.no_result_box.attr( 'id', '#dashboard_' + this.viewId + '_no_result_box' );
		$( this.el ).find( '.container' ).append( this.no_result_box );
	},

	removeNoResultCover: function() {
		if ( this.no_result_box && this.no_result_box.length > 0 ) {
			this.no_result_box.remove();
		}
		this.no_result_box = null;
	},

	addMissedDashLetToOrder: function( dashlet_list ) {
		var $this = this;
		//Error: Uncaught TypeError: $this.order_data.data.push is not a function in interface/html5/#!m=Home line 546
		if ( !$this.order_data || !$this.order_data.data ) {
			return;
		}
		for ( var j = 0, jj = dashlet_list.length; j < jj; j++ ) {
			var dashlet = dashlet_list[j];
			var found = false;
			for ( var y = 0, yy = $this.order_data.data.length; y < yy; y++ ) {
				var order_id = $this.order_data.data[y];
				if ( dashlet.id.toString() === order_id.toString() ) {
					found = true;
					break;
				}
			}
			if ( !found ) {
				$this.order_data.data.push( dashlet.id.toString() );
			}
			if ( this.is_getting_default_dashlet ) {
				$this.order_data.data.sort();
			}

		}
	},

	updateLayout: function() {
		var $this = this;
		this.saveScrollPosition();
		if ( this.initMasonryDone ) {
			this.dashboard_container.masonry( 'destroy' );
			this.dashboard_container.sortable( 'destroy' );
		} else {
			this.initMasonryDone = true;
		}
		this.dashboard_container.masonry( {
			"columnWidth": 1,
			itemSelector: '.dashlet-container'
		} );
		this.dashboard_container.sortable().unbind( 'sortupdate' ).bind( 'sortupdate', function( e, draggingTarget ) {
			$this.saveNewOrder();
			$this.updateLayout();
			var draggingTargetId = draggingTarget.item.attr( 'id' ).split( '_' )[1];
			for ( var j = 0, jj = $this.dashletControllerArray.length; j < jj; j++ ) {
				var dashlet = $this.dashletControllerArray[j];
				if ( draggingTargetId == dashlet.data.id ) {
					dashlet.refreshIfNecessary();
				}
			}
		} );

		this.recoverCurrentScrollPosition()

		TTPromise.resolve('init','init');
	},

	saveNewOrder: function( callBack ) {
		var $this = this;
		var dashlets = $( this.el ).find( '.dashlet-container' );
		var new_order = [];
		for ( var i = 0, ii = dashlets.length; i < ii; i++ ) {
			var dashlet = $( dashlets[i] );
			var id = dashlet.attr( 'id' ).split( '_' )[1];
			new_order.push( id );
		}
		var arg = {};
		if ( this.order_data ) {
			this.order_data.data = new_order;
			arg = this.order_data;
		} else {
			arg.name = 'order_data';
			arg.script = ALayoutIDs.DASHBOARD_ORDER;
			arg.is_default = true;
			arg.data = new_order;
		}

		this.user_generic_data_api.setUserGenericData( arg, {
			onResult: function( result ) {
				var result_data = result.getResult();
				if ( result_data != true && result_data > 0 ) {
					$this.order_data = {id: result_data};
					$this.order_data.data = new_order;
					if ( callBack ) {
						callBack();
					}
				} else if ( result_data === true ) {
					if ( callBack ) {
						callBack();
					}
				}
			}
		} );
	},

	cleanWhenUnloadView: function() {
		this.unLoadCurrentDashlets();
	},

	modifyDashlet: function( id ) {
		var $this = this;
		IndexViewController.openWizard( 'DashletWizard', {saved_dashlet_id: id}, function() {
			$this.initDashBoard();
		} );
	},

	deleteDashlet: function( id, target ) {
		var $this = this;
		TAlertManager.showConfirmAlert( Global.delete_dashlet_confirm_message, null, function( result ) {
			if ( result ) {
				ProgressBar.showOverlay();
				$this.user_generic_data_api.deleteUserGenericData( id, {
					onResult: function( result ) {
						target.remove();
						$this.removeDeletedDashletsData( id );
						if ( $( $this.el ).find( '.dashboard-container' ).children().length < 1 ) {
							$this.showNoResultCover();
						} else {
							$this.saveNewOrder( function() {
								$this.updateLayout();
							} );
						}
					}
				} );
			} else {
				ProgressBar.closeOverlay();
			}
		} );
	},

	removeDeletedDashletsData: function( id ) {
		for ( var i = 0, ii = this.dashlet_list.length; i < ii; i++ ) {
			if ( this.dashlet_list[i].id.toString() === id ) {
				this.dashlet_list.splice( i, 1 );
				break;
			}
		}
	},

	setViewHeight: function() {
		var $this = this;
		$( this.el ).find( '.container' ).height( $( this.el ).height() - 35 );
		$( window ).resize( function() {
			$( $this.el ).find( '.container' ).height( $( $this.el ).height() - 35 );
		} );
	},

	saveScrollPosition: function() {
		this.current_scroll_position = this.dashboard_container.parent().scrollTop()
	},

	recoverCurrentScrollPosition: function() {
		if ( this.current_scroll_position > 0 ) {
			this.dashboard_container.parent().scrollTop( this.current_scroll_position );
		}
	}

} );
