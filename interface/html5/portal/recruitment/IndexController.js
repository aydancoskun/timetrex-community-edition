var ApplicationRouter = Backbone.Router.extend( {
	controller: null,
	headerView: null,
	routes: {
		'': 'onViewChange',
		'!:viewName': 'onViewChange',
		'*notFound': 'notFound'
	},

	buildArgDic: function( array ) {
		var len = array.length;
		var result = {};
		for ( var i = 0; i < len; i++ ) {
			var item = array[i];
			item = item.split( '=' );
			result[item[0]] = item[1];
		}

		return result;
	},

	reloadView: function( view_id ) {
		//error: Uncaught ReferenceError: XXXXViewController is not defined ininterface/html5/#!m=TimeSheet line 3
		// Happens when quickly click on context menu and network is slow.
		if ( window[view_id + 'ViewController'] ) {
			TopMenuManager.selected_sub_menu_id = ''; // clear select ribbon menu, set in view init;
			PortalBaseViewController.loadView( view_id );
		}
	},

	notFound: function( url ) {
		var new_url = Global.getBaseURL().split( '#' )[0];

		Global.setURLToBrowser( new_url + '#!m=PortalJobVacancy' );
	},

	/* jshint ignore:start */
	onViewChange: function( viewName ) {
		var $this = this;
		var args = {};
		var view_id;
		var edit_id;
		var action;

		if ( Global.needReloadBrowser ) {
			Global.needReloadBrowser = false;
			window.location.reload();
			return;
		}

		if ( viewName ) {
			args = this.buildArgDic( viewName.split( '&' ) );
		}
		if ( viewName && viewName.indexOf( 'm=' ) >= 0 ) {
			view_id = args.m;
		} else {
			view_id = 'PortalJobVacancy';
		}

		LocalCacheData.fullUrlParameterStr = viewName;

		LocalCacheData.all_url_args = args;

		if ( LocalCacheData.all_url_args ) {
			if ( !LocalCacheData.all_url_args.hasOwnProperty('company_id') ) {
				TTPromise.add('IndexController', 'onViewChange');
				TTPromise.wait( null, null, function() {
					if ( IndexViewController && IndexViewController.instance && IndexViewController.instance.router ) {
						IndexViewController.instance.router.showTipModal( $.i18n._('Invalid Company') );
					} else {
						TAlertManager.showAlert( $.i18n._('Invalid Company') );
					}
				});

				setTimeout( function() {
					//Ensure that the error is shown in a relatively timely fashion AFTER the framework needed to render properly is loaded.
					TTPromise.resolve('IndexController', 'onViewChange');
				},4000);
			}
		}
		var reg = new RegExp( '^[0-9]*$' );

		if ( reg.test( args.id ) ) {
			edit_id = TTUUID.castUUID( args.id );
		} else {
			edit_id = args.id; //Accrual balance go here, because it's id is combined. x_x
		}

		action = args.a;

		if ( LocalCacheData.current_open_view_id === view_id ) {

			if ( LocalCacheData.current_open_primary_controller ) {

				if ( action ) {
					switch ( action ) {
						case 'edit':

							//Error: Unable to get property 'id' of undefined or null reference in /interface/html5/IndexController.js?v=8.0.0-20141230-125406 line 87
							if ( !LocalCacheData.current_open_primary_controller.edit_view ||
								(LocalCacheData.current_open_primary_controller.current_edit_record &&
								LocalCacheData.current_open_primary_controller.current_edit_record.id != edit_id) ) {

								//Makes ure when doing copy_as_new, don't open this
								if ( LocalCacheData.current_doing_context_action === 'edit' ) {
									openEditView( edit_id );
								}

							}
							break;
						case 'new':
							if ( !LocalCacheData.current_open_primary_controller.edit_view ) {
								openEditView();
							}

							break;
						case 'view':

							switch ( view_id ) {
								case 'MessageControl':
									if ( args.t === 'message' ) {
										if ( !LocalCacheData.current_open_primary_controller.edit_view ||
											(!checkIds()) ) {
											openEditView( edit_id, true );
										}
									} else if ( args.t === 'request' ) {
										if ( !LocalCacheData.current_open_primary_controller.edit_view ||
											(LocalCacheData.current_open_primary_controller.current_select_message_control_data.id != edit_id) ) {
											openEditView( edit_id, true );
										}
									}
									break;
								default:
									if ( !LocalCacheData.current_open_primary_controller.edit_view ||
										(LocalCacheData.current_open_primary_controller.current_edit_record.id != edit_id) ) {
										openEditView( edit_id, true );
									}
									break;
							}

					}

					return;
				} else {
					if ( LocalCacheData.current_open_primary_controller.edit_view &&
						LocalCacheData.current_open_primary_controller.current_edit_record ) {

						if ( LocalCacheData.current_open_primary_controller.viewId === 'TimeSheet' ) {
							if ( LocalCacheData.current_open_primary_controller.is_mass_editing ) {
								return;
							}
						}

						LocalCacheData.current_open_primary_controller.buildContextMenu( true );
						LocalCacheData.current_open_primary_controller.removeEditView();
						this.cleanAnySubViewUI();

					}
				}

			}
			return;

		} else {
			LocalCacheData.edit_id_for_next_open_view = edit_id;

			if ( action ) {
				LocalCacheData.current_doing_context_action = action;
			}

			switch ( view_id ) {
				case 'TimeSheet':
				case 'Schedule':
					if ( args.date ) {
						LocalCacheData.current_selet_date = args.date;
					}
					break;

			}

		}

		var timeout_count;
		timeout_count = 0;
		if (LocalCacheData.loadViewRequiredJSReady) {
			showRibbonMenuAndLoadView();
		} else {
			var auto_login_timer = setInterval(function () {
				if (timeout_count == 100) {
					clearInterval(auto_login_timer);
				}
				timeout_count = timeout_count + 1;
				if (LocalCacheData.loadViewRequiredJSReady) {
					showRibbonMenuAndLoadView();
					clearInterval(auto_login_timer);
				}
			}, 600);
		}
		function showRibbonMenuAndLoadView() {
			//Show ribbon menu UI
			// if ( view_id ) {
			// 	$( 'body' ).removeClass( 'login-bg' );
			// 	Global.loadStyleSheet( '../../theme/default/css/portal.css' + '?v=' + APIGlobal.pre_login_data.application_build );
			// 	$this.headerView = new HeaderViewController();
			// 	$('#topContainer').html($this.headerView.el);
			// 	setTimeout(function(  ) {
			// 		Global.topContainer().css('display', 'block');
			// 		Global.bottomContainer().css( 'display', 'block' );
			// 	}, 50);
			// }
			$( 'body' ).removeClass( 'login-bg' );
			Global.loadStyleSheet( '../../theme/default/portal/css/portal.css' + '?v=' + APIGlobal.pre_login_data.application_build );
			$('link[title="application css"]').prop('disabled', true);
			if ( !$this.headerView ) {
				$this.headerView = new HeaderViewController();
				$('#topContainer').html($this.headerView.el);
				loadViewController();
			} else {
				if ( $this.headerView.profileView && $this.headerView.profileView.is_changed ) {
					$this.showConfirmModal( $.i18n._('You have modified data without saving, are you sure you want to continue and lose your changes'), {
						title: '',
						actions: [
							{label: "No", isClose: true, callBack: function ( e ) {
								$this.navigate('#!m=MyProfile&company_id=' + LocalCacheData.all_url_args.company_id, {trigger: false, replace: true} );
								return;
							}},
							{label: 'Yes', callBack: function( e ) {
								$this.hideConfirmModal();
								headerRender();
								loadViewController();
							}}
						]
					} );
					return;
				} else {
					headerRender();
					loadViewController();
				}
			}
		}

		function headerRender() {
			$this.headerView.jobVacancyViewController = null;
			$this.headerView.profileView = null;
			$this.headerView.render();
		}

		function loadViewController() {
			setTimeout(function(  ) {
				Global.topContainer().css('display', 'block');
				Global.bottomContainer().css( 'display', 'block' );
			}, 50);

			Global.loadViewSource( view_id, view_id + 'ViewController.js', function() {
				PortalBaseViewController.loadView( view_id );
			} );
		}
		function checkIds() {

			if ( Global.isArray( LocalCacheData.current_open_primary_controller.current_edit_record ) ) {
				for ( var i = 0; i < LocalCacheData.current_open_primary_controller.current_edit_record.length; i++ ) {
					var item = LocalCacheData.current_open_primary_controller.current_edit_record[i];

					if ( item.id === edit_id ) {
						return true;
					}
				}
			} else {
				item = LocalCacheData.current_open_primary_controller.current_edit_record;
				if ( item.id === edit_id ) {
					return true;
				}
			}

			return false;
		}

		function openEditView( edit_id, view_mode ) {
			var type;
			switch ( view_id ) {
				case 'MessageControl':
					type = args.t;
					var item = {};
					if ( type === 'message' ) {
						item.id = edit_id;
					} else {
						item.object_id = edit_id;
						item.object_type_id = 50;
					}
					LocalCacheData.current_open_primary_controller.onViewClick( item );
					break;

				case 'TimeSheet':
					type = args.t;

					if ( !view_mode ) {
						if ( edit_id ) {
							LocalCacheData.current_open_primary_controller.onEditClick( edit_id, type );
						} else {
							LocalCacheData.current_open_primary_controller.onAddClick();
						}
					} else {
						if ( edit_id ) {
							LocalCacheData.current_open_primary_controller.onViewClick( edit_id, type );
						}
					}

					break;
				default:

					if ( !view_mode ) {
						if ( edit_id ) {
							LocalCacheData.current_open_primary_controller.onEditClick( edit_id );
						} else {
							LocalCacheData.current_open_primary_controller.onAddClick();
						}
					} else {
						if ( edit_id ) {
							LocalCacheData.current_open_primary_controller.onViewClick( edit_id );
						}
					}

					break;
			}
		}

	},

	/* jshint ignore:end */

	cleanAnySubViewUI: function() {
		var children = Global.contentContainer().children();

		if ( children.length > 1 ) {
			for ( var i = 1; i < children.length; i++ ) {
				// Object doesn't support property or method 'remove', Not sure why, add try catch to ingore this error since this should no harm
				try {

					if ( $( children[i] ).attr( 'id' ) === LocalCacheData.current_open_primary_controller.ui_id ) {
						continue;
					} else {
						children[i].remove();
					}

				} catch ( e ) {
					//Do nothing
				}

			}
		}
	},

	setContentDivHeight: function() {
		Global.contentContainer().removeClass( 'content-container' );
		Global.contentContainer().addClass( 'content-container-after-login' );
		Global.topContainer().addClass( 'top-container-after-login' );

	},

	addTopMenu: function() {
		Global.loadScript( 'global/widgets/top_menu/TopMenuController.js' );
		if ( TopMenuController ) {
			TopMenuController.loadView();
		}

	},

	showFormModal: function (element, options) {
		var $this = this;
		if ($('#formModal').is(':visible')) {
			this.hideFormModal();
			setTimeout(function () {
				doNext()
			}, 500)
		} else {
			doNext();
		}
		function doNext() {
			!options && (options = {title: '...', actions: []});
			if ($('#formModal').length > 0) {
				$('#formModal').modal({
					backdrop: 'static',
					show: true
				});
			}
			$('#formModalLabel').text(options.title);
			$('#formModalBody').html(element);
			if (options.actions.length > 0) {
				$('#formModalFooter').empty();
				_.each(options.actions, function (item) {
					var button = $('<button type="button" class="btn"></button>');
					item.isClose && button.attr('data-dismiss', 'modal');
					button.text(item.label);
					item.class && (button.addClass(item.class));
					!item.class && button.addClass('btn-primary');
					if (item.callBack) {
						if ( item.isClose ) {
							$('#formModal').find('button.close').unbind('click').bind('click', item.callBack.bind(self));
						}
						button.unbind('click').bind('click', item.callBack.bind(self));
					}
					$('#formModalFooter').append(button);
				})
			}
		}

	},

	showTipModal: function (element, options) {
		var $this = this;
		if ($('#tipModal').is(':visible')) {
			this.hideTipModal();
			setTimeout(function () {
				doNext();
			}, 500)
		} else {
			doNext();
		}
		function doNext() {
			!options && (options = {title: '...', actions:[], style: {}});
			$('#tipModal').find('.modal-content').css( options.style );
 			if ($('#tipModal').length > 0) {
				$('#tipModal').modal('show');
			}
			$('#tipModalBody').html(element);
			$this.autoHideTipModal();
		}
	},

	showConfirmModal: function (element, options) {
		var $this = this;
		if ($('#confirmModal').is(':visible')) {
			this.hideConfirmModal();
			setTimeout(function () {
				doNext()
			}, 500)
		} else {
			doNext();
		}
		function doNext() {
			!options && (options = {title: '...', actions: []});
			if ($('#confirmModal').length > 0) {
				$('#confirmModal').modal({
					backdrop: 'static',
					show: true
				});
			}
			$('#confirmModalLabel').text(options.title);
			$('#confirmModalBody').html(element);
			if (options.actions.length > 0) {
				$('#confirmModalFooter').empty();
				_.each(options.actions, function (item) {
					var button = $('<button type="button" class="btn"></button>');
					item.isClose && button.attr('data-dismiss', 'modal');
					button.text(item.label);
					item.class && (button.addClass(item.class));
					!item.class && button.addClass('btn-primary');
					if (item.callBack) {
						button.unbind('click').bind('click', item.callBack.bind(self));
					}
					$('#confirmModalFooter').append(button);
				})
			}
		}

	},

	showSignInModal: function (element, options) {
		var $this = this;
		if ($('#signinModal').is(':visible')) {
			this.hideSignInModal();
			setTimeout(function () {
				doNext()
			}, 500)
		} else {
			doNext();
		}
		function doNext() {
			!options && (options = {title: '...', actions: []});
			if ($('#signinModal').length > 0) {
				$('#signinModal').modal({
					backdrop: 'static',
					show: true
				});
			}
			$('#signinModalLabel').text(options.title);
			$('#signinModalBody').html(element);
			if (options.actions.length > 0) {
				$('#signinModalFooter').empty();
				_.each(options.actions, function (item) {
					var button = $('<button type="button" class="btn"></button>');
					item.isClose && button.attr('data-dismiss', 'modal');
					button.text(item.label);
					item.class && (button.addClass(item.class));
					!item.class && button.addClass('btn-primary');
					if (item.callBack) {
						if ( item.isClose ) {
							$('#signinModal').find('button.close').unbind('click').bind('click', item.callBack.bind(self));
						}
						button.unbind('click').bind('click', item.callBack.bind(self));
					}
					$('#signinModalFooter').append(button);
				})
			}
		}

	},

	autoHideTipModal: function(  ) {
		var $this = this;
		setTimeout(  function(  ) {
			$this.hideTipModal();
		}, 5000);
	},

	hideTipModal: function(  ) {
		$('#tipModal').modal('hide');
	},

	hideFormModal: function(  ) {
		$('#formModal').modal('hide');
	},

	hideSignInModal: function() {
		$('#signinModal').modal('hide');
	},

	hideConfirmModal: function(  ) {
		$('#confirmModal').modal('hide');
	},

	removeCurrentView: function( callBack ) {
		if ( LocalCacheData.current_open_edit_only_controller ) {
			clean( LocalCacheData.current_open_edit_only_controller );
			LocalCacheData.current_open_edit_only_controller = null;
		}

		if ( LocalCacheData.current_open_primary_controller ) {
			if ( LocalCacheData.current_open_primary_controller.edit_view ) {
				clean( LocalCacheData.current_open_primary_controller );
			}
			Global.contentContainer().empty();
			LocalCacheData.current_open_primary_controller.cleanWhenUnloadView( callBack );
		} else {

			if ( Global.isSet( callBack ) ) {
				callBack();
			}
		}

		function clean( viewController ) {
			viewController.clearErrorTips();
			// Cannot read property 'remove' of null in interface/html5/IndexController.js?v=9.0.0-20151016-153057 line 439
			if ( viewController.edit_view ) {
				viewController.edit_view.remove();
			}
			viewController.sub_log_view_controller = null;
			viewController.edit_view_ui_dic = {};
			viewController.edit_view_ui_validation_field_dic = {};
			viewController.edit_view_form_item_dic = {};
			viewController.edit_view_error_ui_dic = {};
			LocalCacheData.current_doing_context_action = '';
		}
	}

} );

IndexViewController = Backbone.View.extend( {
	el: 'body', //So we can add event listener for all elements
	router: null,

	initialize: function( options ) {
		this.router = new ApplicationRouter();

		//Set title in index.php instead.
		//$( 'title' ).html( '' );

		this.router.controller = this;
		//Error: Backbone.history has already been started in interface/html5/framework/backbone/backbone-min.js?v=9.0.1-20151022-162110 line 28
		if ( !Backbone.History.started ) {
			Backbone.history.start();
		}

		IndexViewController.instance = this;

	}

} );

IndexViewController.goToView = function( view_name, filter ) {
	if ( TopMenuManager.selected_sub_menu_id ) {
		$( '#' + TopMenuManager.selected_sub_menu_id ).removeClass( 'selected-menu' );
	}

	$( '#' + view_name ).addClass( 'selected-menu' );
	LocalCacheData.default_filter_for_next_open_view = filter;

	TopMenuManager.goToView( view_name, true );

};

IndexViewController.goToViewByViewLabel = function( view_label ) {
	var view_name;
	switch ( view_label ) {
		case 'Exceptions':
			view_name = 'Exception';
			break;
		case 'Messages':
			view_name = 'MessageControl';
			break;
		case 'Requests':
			view_name = 'Request';
			break;
		case 'Contact Information':
			IndexViewController.openEditView( LocalCacheData.current_open_primary_controller, 'LoginUserContact' );
			return;
			break;
		default:
			var reg = /\s/g;
			view_name = view_label.replace( reg, '' );
			break
	}

	if ( TopMenuManager.selected_sub_menu_id ) {
		$( '#' + TopMenuManager.selected_sub_menu_id ).removeClass( 'selected-menu' );
	}

	$( '#' + view_name ).addClass( 'selected-menu' );

	TopMenuManager.goToView( view_name, true );

};

IndexViewController.openWizard = function( wizardName, defaultData, callBack ) {
	BaseWizardController.default_data = defaultData;
	BaseWizardController.call_back = callBack;
	switch ( wizardName ) {
		default:
			// track edit view only view
			Global.trackView( wizardName );
			Global.loadViewSource( wizardName, wizardName + 'Controller.js', function() {
				BaseWizardController.openWizard( wizardName, wizardName + '.html' );
			} );
			break;
	}

};

IndexViewController.openReport = function( parent_view_controller, view_name, id ) {
	var view_controller = null;

	if ( LocalCacheData.current_open_report_controller ) {
		LocalCacheData.current_open_report_controller.removeEditView();
	}

	ProgressBar.showOverlay();

	switch ( view_name ) {
		default:
			Global.loadViewSource( view_name, view_name + 'ViewController.js', function() {
				/* jshint ignore:start */
				view_controller = eval( 'new ' + view_name + 'ViewController( {edit_only_mode: true} ); ' );
				/* jshint ignore:end */
				view_controller.parent_view_controller = parent_view_controller;
				view_controller.openEditView();

				var current_url = window.location.href;
				if ( current_url.indexOf( '&sm' ) > 0 ) {
					current_url = current_url.substring( 0, current_url.indexOf( '&sm' ) );
				}
				current_url = current_url + '&sm=' + view_name;

				if ( LocalCacheData.default_edit_id_for_next_open_edit_view ) {
					current_url = current_url + '&sid=' + LocalCacheData.default_edit_id_for_next_open_edit_view;
				}
				Global.setURLToBrowser( current_url );

			} );
			break;
	}

};

//Open edit view
IndexViewController.openEditView = function( parent_view_controller, view_name, id ) {
	var view_controller = null;

	if ( !PermissionManager.checkTopLevelPermission( view_name ) && view_name !== 'map' ) {
		TAlertManager.showAlert('Permission denied');
		return;
	}
	
	//Merge conflict from RecruitmentPortal. This seemed to have removed the permission check, but that would break the application UI?
	//if ( LocalCacheData.current_open_edit_only_controller ) {
	//	LocalCacheData.current_open_edit_only_controller.onCancelClick();
	//}

	// track edit view only view
	Global.trackView( view_name );

	switch ( view_name ) {

		default:
			Global.loadViewSource( view_name, view_name + 'ViewController.js', function() {
				/* jshint ignore:start */
				view_controller = eval( 'new ' + view_name + 'ViewController( {edit_only_mode: true} ); ' );
				/* jshint ignore:end */
				view_controller.parent_view_controller = parent_view_controller;
				view_controller.openEditView( id );

				var current_url = window.location.href;
				if ( current_url.indexOf( '&sm' ) > 0 ) {
					current_url = current_url.substring( 0, current_url.indexOf( '&sm' ) );
				}
				if ( id ) {
					current_url = current_url + '&sm=' + view_name + '&sid=' + id;
				} else {
					current_url = current_url + '&sm=' + view_name;
				}

				Global.setURLToBrowser( current_url );

				LocalCacheData.current_open_edit_only_controller = view_controller;

			} );
			break;

	}

};

IndexViewController.setNotificationBar = function( target ) {

	var api = new (APIFactory.getAPIClass( 'APINotification' ))();

	//Error: TypeError: api.getNotification is not a function in /interface/html5/IndexController.js?v=8.0.0-20141117-095711 line 529
	if ( !api || !api.getNotification || typeof(api.getNotification) !== 'function' ) {
		return;
	}

	api.getNotification( target, {
		onResult: function( result ) {
			var result_data = result.getResult();

			if ( !LocalCacheData.notification_bar ) {
				var notification_box_tpl = $( Global.loadWidgetByName( WidgetNamesDic.NOTIFICATION_BAR ) );
				LocalCacheData.notification_bar = notification_box_tpl.TopNotification();
			}

			LocalCacheData.notification_bar.show( result_data );

		}
	} );

};

IndexViewController.instance = null;