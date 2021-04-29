RibbonViewController = Backbone.View.extend( {

	el: '#ribbon_view_container', //Must set el here and can only set string, so events can work
	user_api: null,

	subMenuNavMap: null,

	initialize: function( options ) {
		// TopMenuManager should be initialized before render to avoid possible race condition.
		// Error: TypeError: TopMenuManager.ribbon_view_controller is null in /interface/html5/framework/jquery.min.js?v=10.5.0-20170331-081453 line 2 > eval line 218

		TopMenuManager.ribbon_view_controller = this;
		var $this = this;

		$this.render();
	},

	onMenuSelect: function( e, ui ) {
		return;
		// No longer used because we only support a single context menu now.
		// if ( TopMenuManager.selected_menu_id && TopMenuManager.selected_menu_id.indexOf( 'ContextMenu' ) >= 0 ) {
		// 	$( '.context-menu-active' ).removeClass( 'context-menu-active' );
		// }
		// TopMenuManager.selected_menu_id = $( e.currentTarget ).attr( 'ref' );
		// if ( TopMenuManager.selected_menu_id && TopMenuManager.selected_menu_id.indexOf( 'ContextMenu' ) >= 0 ) {
		// 	$( e.target ).parent().addClass( 'context-menu-active' );
		// }
	},

	onSubMenuNavClick: function( target, id ) {
		var $this = this;
		var sub_menu = this.subMenuNavMap[id];
		if ( LocalCacheData.openRibbonNaviMenu ) {

			if ( LocalCacheData.openRibbonNaviMenu.attr( 'id' ) === 'sub_nav' + id ) {
				LocalCacheData.openRibbonNaviMenu.close();
				return;
			} else {
				LocalCacheData.openRibbonNaviMenu.close();
			}
		}
		showNavItems();

		function showNavItems() {
			var items = sub_menu.get( 'items' );
			var box = $( '<ul id=\'sub_nav' + id + '\' class=\'ribbon-sub-menu-nav\'> </ul>' );
			for ( var i = 0; i < items.length; i++ ) {
				var item = items[i];
				var item_node = $( '<li class=\'ribbon-sub-menu-nav-item\' id=\'' + item.get( 'id' ) + '\'><span class=\'label\'>' + item.get( 'label' ) + '</span></li>' );
				box.append( item_node );

				item_node.unbind( 'click' ).click( function() {

					var id = $( this ).attr( 'id' );
					$this.onReportMenuClick( id );
				} );
			}
			box = box.RibbonSubMenuNavWidget();
			LocalCacheData.openRibbonNaviMenu = box;
			$( target ).append( box );
		}

	},

	onReportMenuClick: function( id ) {
		Global.closeEditViews( function() {
			if ( id === 'AffordableCareReport' && !( Global.getProductEdition() >= 15 ) ) {
				TAlertManager.showAlert( Global.getUpgradeMessage() );
			} else {
				var parent_view = LocalCacheData.current_open_edit_only_controller ? LocalCacheData.current_open_edit_only_controller : LocalCacheData.current_open_primary_controller;
				IndexViewController.openReport( parent_view, id );
			}
		} );

	},

	//FIXME: Stops punch inout from being able to exit via the menu system except on items with dropdowns
	//Does not trigger on Report menu items with dropdowns (see the right event)
	onSubMenuClick: function( id ) {
		var $this = this;
		//#2342 see onCancelClick in BaseViewController and Gloabl.closeEditViews.

		Global.closeEditViews( function() {
			$this.setSelectSubMenu( id );
			$this.openSelectView( id );
		} );
	},

	buildRibbonMenus: function() {
		var $this = this;
		this.subMenuNavMap = {};
		var ribbon_menu_array = TopMenuManager.ribbon_menus;
		var ribbon_menu_label_node = $( '.ribbonTabLabel' );
		var ribbon_menu_root_node = $( '.ribbon' );

		var len = ribbon_menu_array.length;

		for ( var i = 0; i < len; i++ ) {

			var ribbon_menu = ribbon_menu_array[i];

			if ( ribbon_menu.get( 'permission_result' ) === false ) {
				continue;
			}

			var ribbon_menu_group_array = ribbon_menu.get( 'sub_menu_groups' );
			var ribbon_menu_ui = $( '<div id="' + ribbon_menu.get( 'id' ) + '" class="ribbon-tab-out-side"><div class="ribbon-tab"><div class="ribbon-sub-menu"></div></div></div>' );

			var len1 = ribbon_menu_group_array.length;
			for ( var x = 0; x < len1; x++ ) {
				var ribbon_menu_group = ribbon_menu_group_array[x];
				var ribbon_sub_menu_array = ribbon_menu_group.get( 'sub_menus' );
				var sub_menu_ui_nodes = $( '<ul></ul>' );
				var ribbon_menu_group_ui = $( '<div class="menu top-ribbon-menu"></div>' );

				var len2 = ribbon_sub_menu_array.length;
				for ( var y = 0; y < len2; y++ ) {

					var ribbon_sub_menu = ribbon_sub_menu_array[y];

					var sub_menu_ui_node = $( '<li><div class="ribbon-sub-menu-icon" id="' + ribbon_sub_menu.get( 'id' ) + '"><img src="' + ribbon_sub_menu.get( 'icon' ) + '" ><span class="ribbon-label">' + ribbon_sub_menu.get( 'label' ) + '</span></div></li>' );

					if ( ribbon_sub_menu.get( 'type' ) === RibbonSubMenuType.NAVIGATION ) {

						if ( ribbon_sub_menu.get( 'items' ).length > 0 ) {
							sub_menu_ui_nodes.append( sub_menu_ui_node );
							sub_menu_ui_node.children().eq( 0 ).addClass( 'ribbon-sub-menu-nav-icon' );
							$this.subMenuNavMap[ribbon_sub_menu.get( 'id' )] = ribbon_sub_menu;

							sub_menu_ui_node.click( function( e ) {
								var id = $( $( this ).find( '.ribbon-sub-menu-icon' ) ).attr( 'id' );
								$this.onSubMenuNavClick( this, id );
							} );
						}

					} else {
						sub_menu_ui_nodes.append( sub_menu_ui_node );

						//Debounce to help prevent double clicks.
						sub_menu_ui_node.click( Global.debounce( function RibbonMenuSubMenuClickEvent( e ) {
							var id = $( this ).find( '.ribbon-sub-menu-icon' ).attr( 'id' );
							$this.onSubMenuClick( id );
						}, 500, true ) );
					}
				}

				//If there is any menu
				if ( sub_menu_ui_nodes.children().length > 0 ) {
					ribbon_menu_group_ui.append( sub_menu_ui_nodes );
					ribbon_menu_group_ui.append( $( '<div class="menu-bottom"><span class="menu-bottom-span">' + ribbon_menu_group.get( 'label' ) + '</span></div>' ) );
					ribbon_menu_ui.find( '.ribbon-sub-menu' ).append( ribbon_menu_group_ui );
				}

			}

			if ( ribbon_menu_ui.find( '.ribbon-sub-menu' ).children().length > 0 ) {
				ribbon_menu_label_node.append( $( '<li><a id="menu:' + ribbon_menu.get( 'id' ) + '"  ref="' + ribbon_menu.get( 'id' ) + '" href="#' + ribbon_menu.get( 'id' ) + '">' + ribbon_menu.get( 'label' ) + '</a></li>' ) );
				ribbon_menu_root_node.append( ribbon_menu_ui );
			}

		}

		this.setRibbonMenuVisibility();

	},

	setRibbonMenuVisibility: function() {
		// Set Employee tab visibility

		var tab_array = ['companyMenu', 'employeeMenu', 'payrollMenu'];

		var len = tab_array.length;

		for ( var i = 0; i < len; i++ ) {
			var menu_id = tab_array[i];

			var tab_content = Global.topContainer().find( '#' + menu_id ).find( 'li' );
			if ( tab_content.length < 1 ) {
				var tab = Global.topContainer().find( 'a[ref=\'' + menu_id + '\']' );
				tab.parent().hide();
			}
		}

//		  // Set COmpany tab visibility
//		  var employee_tab_content = Global.topContainer().find('#employeeMenu ' ).find('li');
//		  if(employee_tab_content.length < 1){
//			  var employee_tab = Global.topContainer().find("a[ref='employeeMenu']" );
//			  employee_tab.parent().hide();
//		  }

	},

	render: function() {
		// Error: TypeError: $(...).tabs is not a function in /interface/html5/framework/jquery.min.js?v=8.0.6-20150417-104146 line 2 > eval line 205
		if ( !this.el ) {
			return;
		}

		this.buildRibbonMenus();

		var $this = this;
		$( this.el ).tabs( {
			// No longer used because we only support a single context menu now.
			// activate: function( e, ui ) {
			// 	$this.onMenuSelect( e, ui );
			// }
		} );

		this.setSelectMenu( TopMenuManager.selected_menu_id );

		this.setSelectSubMenu( TopMenuManager.selected_sub_menu_id );

		$( '#leftLogo' ).attr( 'src', Global.getRealImagePath( 'css/global/widgets/ribbon/images/logo.png' ) );
		$( '#rightLogo' ).attr( 'src', ServiceCaller.companyLogo + '&t=' + new Date().getTime() );

		if ( LocalCacheData.getLoginUserPreference() ) {
			$( '#leftLogo' ).unbind( 'click' ).bind( 'click', function() {
				Global.closeEditViews( function() {
					TopMenuManager.goToView( 'Home' );
				} );

			} );
		}
	},

	setSelectMenu: function( name ) {
		// if ( name ) {
		// 	var index = $(this.el).find('#ribbon a[ref=' + name + ']').parent().index();
		// 	//$(this.el).tabs({'selected': index});
		// 	$(this.el).tabs('option','active',index)
		// 	TopMenuManager.selected_menu_id = name;
		// }
		//#2353 - exception: can't find #ribbon a[ref=]
		if ( !name ) {
			name = 'Home';
		}

		var index = $( this.el ).find( '#ribbon a[ref=' + name + ']' ).parent().index();
		if ( index >= 0 ) { //Without this check, the last tab is always selected first and causes more obvious "flashing" when refreshing the browser. This just changes it to the first tab instead is all though.
			$( this.el ).tabs( 'option', 'active', index );
		}
		TopMenuManager.selected_menu_id = name;
	},

	openSelectView: function( name ) {
		Global.setUINotready();
		switch ( name ) {
			case 'ImportCSV':
				IndexViewController.openWizard( 'ImportCSVWizard', null, function() {
					//Error: TypeError: LocalCacheData.current_open_primary_controller.search is not a function in interface/html5/framework/jquery.min.js?v=9.0.0-20151016-110437 line 2 > eval line 248
					if ( LocalCacheData.current_open_primary_controller && typeof LocalCacheData.current_open_primary_controller.search === 'function' ) {
						LocalCacheData.current_open_primary_controller.search();
					}
				} );
				break;
			case 'QuickStartWizard':
				if ( PermissionManager.checkTopLevelPermission( 'QuickStartWizard' ) ) {
					IndexViewController.openWizard( 'QuickStartWizard' );
				}
				break;
			case 'InOut':
			case 'UserDefault':
			case 'Company':
			case 'LoginUserContact':
			case 'LoginUserPreference':
			case 'ChangePassword':
			case 'InvoiceConfig':
			case 'RecruitmentPortalConfig':
			case 'About':
				if ( LocalCacheData.current_open_edit_only_controller && LocalCacheData.current_open_edit_only_controller.viewId == name ) { //#2557 - A - Ensure that opening edit only views on top of same edit only view just resets the edit menu
					LocalCacheData.current_open_edit_only_controller.setEditMenu();
					$( '#ribbon_view_container .context-menu:visible a' ).click();
				} else if ( LocalCacheData.current_open_edit_only_controller ) { //#2557 - B - Ensure that opening edit only views on top of different edit only  view sets the parent to the existing edit only view
					IndexViewController.openEditView( LocalCacheData.current_open_edit_only_controller, name );
				} else {
					IndexViewController.openEditView( LocalCacheData.current_open_primary_controller, name ); //#2557 - C - Ensure that opening edit views as normal works as before
				}
				break;
			case 'Logout':
				this.doLogout();
				break;
			case 'PortalLogout':
				this.doPortalLogout();
				break;
			case 'AdminGuide':
				var url = 'https://www.timetrex.com/h?id=admin_guide&v=' + LocalCacheData.getLoginData().application_version + '&e=' + Global.getProductEdition();
				window.open( url, '_blank' );
				break;
			case 'FAQS':
				url = 'https://www.timetrex.com/h?id=faq&v=' + LocalCacheData.getLoginData().application_version + '&e=' + Global.getProductEdition();
				window.open( url, '_blank' );
				break;
			case 'WhatsNew':
				url = 'https://www.timetrex.com/h?id=changelog&v=' + LocalCacheData.getLoginData().application_version + '&e=' + Global.getProductEdition();
				window.open( url, '_blank' );
				break;
			case 'EmailHelp':
				if ( Global.getProductEdition() >= 15 ) {
					location.href = 'mailto:' + APIGlobal.pre_login_data.support_email + '?subject=Company: ' + LocalCacheData.getCurrentCompany().name + '&body=Company: ' + LocalCacheData.getCurrentCompany().name + '  ' + 'Registration Key: ' + LocalCacheData.getLoginData().registration_key;
				} else {
					url = 'https://www.timetrex.com/r?id=29';
					window.open( url, '_blank' );
				}
				break;
			case 'Sandbox':
				if ( APIGlobal.pre_login_data['sandbox_url'] && APIGlobal.pre_login_data['sandbox_url'].length > 0 ) {
					var user = LocalCacheData.getLoginUser();
					Global.NewSession( user.user_name, 'SANDBOX', true );
				}
				break;
			case 'ProcessPayrollWizard':
				IndexViewController.openWizard( 'ProcessPayrollWizard', null, function() {
					//Error: TypeError: LocalCacheData.current_open_primary_controller.search is not a function in interface/html5/framework/jquery.min.js?v=9.0.0-20151016-110437 line 2 > eval line 248
					if ( LocalCacheData.current_open_primary_controller && typeof LocalCacheData.current_open_primary_controller.search === 'function' ) {
						LocalCacheData.current_open_primary_controller.search();
					}
				} );
				break;
			case 'PayrollRemittanceAgencyEventWizard':
				IndexViewController.openWizardController( 'PayrollRemittanceAgencyEventWizardController', null, function() {
					//Error: TypeError: LocalCacheData.current_open_primary_controller.search is not a function in interface/html5/framework/jquery.min.js?v=9.0.0-20151016-110437 line 2 > eval line 248
					if ( LocalCacheData.current_open_primary_controller && typeof LocalCacheData.current_open_primary_controller.search === 'function' ) {
						LocalCacheData.current_open_primary_controller.search();
					}
				} );
				break;
			case 'ProcessTransactionsWizard':
				IndexViewController.openWizardController( 'ProcessTransactionsWizard', null, function() {
					//Error: TypeError: LocalCacheData.current_open_primary_controller.search is not a function in interface/html5/framework/jquery.min.js?v=9.0.0-20151016-110437 line 2 > eval line 248
					if ( LocalCacheData.current_open_primary_controller && typeof LocalCacheData.current_open_primary_controller.search === 'function' ) {
						LocalCacheData.current_open_primary_controller.search();
					}
				} );
				break;
			case 'LegalEntity':
				if ( Global.getProductEdition() >= 15 ) {
					TopMenuManager.goToView( TopMenuManager.selected_sub_menu_id );
				} else {
					IndexViewController.openEditView( LocalCacheData.current_open_primary_controller, name, false );
				}
				break;
			default:
				//#2557 - When opening a view from the submenus, ensure that similarily named edit only views are cancelled (with confirm) first.
				if ( LocalCacheData.current_open_edit_only_controller && LocalCacheData.current_open_edit_only_controller.viewId == name ) {
					LocalCacheData.current_open_edit_only_controller.onCancelClick();
					TTPromise.wait( 'base', 'onCancelClick', function() {
						TopMenuManager.goToView( TopMenuManager.selected_sub_menu_id );
					} );
				} else {
					TopMenuManager.goToView( TopMenuManager.selected_sub_menu_id );
				}
		}
	},

	setSelectSubMenu: function( name ) {
		switch ( name ) {
			case 'InOut':
			case 'UserDefault':
			case 'Company':
			case 'LoginUserContact':
			case 'ImportCSV':
			case 'QuickStartWizard':
			case 'InvoiceConfig':
			case 'LoginUserPreference':
			case 'RecruitmentPortalConfig':
				break;
			case 'Logout':
				break;
			case 'AdminGuide':
				break;
			case 'FAQS':
				break;
			case 'WhatsNew':
				break;
			case 'EmailHelp':
				break;
			case 'ProcessPayrollWizard':
			case 'PayrollRemittanceAgencyWizard':
				break;
			default:
				if ( TopMenuManager.selected_sub_menu_id ) {

					try {
						$( '#' + TopMenuManager.selected_sub_menu_id ).removeClass( 'selected-menu' );
					} catch ( e ) {
						TopMenuManager.selected_sub_menu_id = '';
						TopMenuManager.selected_menu_id = '';
						TAlertManager.showAlert( $.i18n._( 'Invalid view name' ) );
						return;
					}

				}

				if ( !name ) {
					name = 'Home';
				}

				$( '#' + name ).addClass( 'selected-menu' );
				TopMenuManager.selected_sub_menu_id = name;

		}

	},

	doLogout: function() {
		//Don't wait for result of logout in case of slow or disconnected internet. Just clear local cookies and move on.
		var current_user_api = TTAPI.APIAuthentication;
		if ( typeof current_user_api.Logout !== 'undefined' ) { //Fix JS exception: Uncaught TypeError: current_user_api.Logout is not a function -- Which can occur when offline and clicking Logout.
			current_user_api.Logout( {
				onResult: function() {
				}
			} );
		}

		Global.setAnalyticDimensions();
		if ( typeof ( ga ) != 'undefined' && APIGlobal.pre_login_data.analytics_enabled === true ) {
			try {
				ga( 'send', 'pageview', { 'sessionControl': 'end' } );
			} catch ( e ) {
				throw e;
			}
		}

		//A bare "if" wrapped around lh_inst doesn't work here for some reason.
		if ( typeof ( lh_inst ) != 'undefined' ) {
			//stop the update loop for live chat with support
			clearTimeout( lh_inst.timeoutStatuscheck );
		}

		Global.Logout();
		TopMenuManager.goToView( 'Login' );

		TAlertManager.showBrowserTopBanner();

		return;
	}
} );

RibbonViewController.loadView = function() {
	Global.topContainer().css( 'display', 'block' );
	var result = Global.loadPageSync( 'global/widgets/ribbon/RibbonView.html' );
	var template = _.template( result );
	Global.topContainer().html( template );

};
