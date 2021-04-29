require.config( {

	waitSeconds: 500,
	urlArgs: 'v=' + APPLICATION_BUILD,

	paths: {
		'polyfill': 'framework/polyfill',
		'CookieSetting': 'global/CookieSetting',
		'APIGlobal': 'global/APIGlobal.js.php?disable_db=' + DISABLE_DB,
		'async': 'framework/require_async_plugin',
		'jquery_json': 'framework/jquery.json',
		'jquery_tablednd': 'framework/jquery.tablednd',
		'jquery_ba_resize': 'framework/jquery.ba-resize',
		'html2canvas': 'framework/html2canvas',
		'moment': 'framework/moment',
		'timepicker_addon': 'framework/widgets/datepicker/jquery-ui-timepicker-addon',
		'jqGrid': 'framework/widgets/jqgrid/jquery.jqgrid.min',
		'jqgrid.winmultiselect': 'framework/widgets/jqgrid/jquery.jqgrid.winmultiselect',
		'ImageAreaSelect': 'framework/jquery.imgareaselect',
		'live-chat': 'global/widgets/live-chat/live-chat',
		'SearchPanel': 'global/widgets/search_panel/SearchPanel',
		'FormItemType': 'global/widgets/search_panel/FormItemType',
		'TGridHeader': 'global/widgets/jqgrid/TGridHeader',
		'ADropDown': 'global/widgets/awesomebox/ADropDown',
		'AComboBox': 'global/widgets/awesomebox/AComboBox',
		'ASearchInput': 'global/widgets/awesomebox/ASearchInput',
		'ALayoutCache': 'global/widgets/awesomebox/ALayoutCache',
		'ALayoutIDs': 'global/widgets/awesomebox/ALayoutIDs',
		'ColumnEditor': 'global/widgets/column_editor/ColumnEditor',
		'SaveAndContinueBox': 'global/widgets/message_box/SaveAndContinueBox',
		'NoHierarchyBox': 'global/widgets/message_box/NoHierarchyBox',
		'NoResultBox': 'global/widgets/message_box/NoResultBox',
		'SeparatedBox': 'global/widgets/separated_box/SeparatedBox',
		'TTextInput': 'global/widgets/text_input/TTextInput',
		'TPasswordInput': 'global/widgets/text_input/TPasswordInput',
		'TText': 'global/widgets/text/TText',
		'TList': 'global/widgets/list/TList',
		'TToggleButton': 'global/widgets/toggle_button/TToggleButton',
		'SwitchButton': 'global/widgets/switch_button/SwitchButton',
		'TCheckbox': 'global/widgets/checkbox/TCheckbox',
		'TComboBox': 'global/widgets/combobox/TComboBox',
		'TTagInput': 'global/widgets/tag_input/TTagInput',
		'TRangePicker': 'global/widgets/datepicker/TRangePicker',
		'TDatePicker': 'global/widgets/datepicker/TDatePicker',
		'TTimePicker': 'global/widgets/timepicker/TTimePicker',
		'TTextArea': 'global/widgets/textarea/TTextArea',
		'TImageBrowser': 'global/widgets/filebrowser/TImageBrowser',
		'TImageAdvBrowser': 'global/widgets/filebrowser/TImageAdvBrowser',
		'TImage': 'global/widgets/filebrowser/TImage',
		'TImageCutArea': 'global/widgets/filebrowser/TImageCutArea',
		'colors': 'framework/widgets/color-picker/colors',
		'colorpicker': 'framework/widgets/color-picker/color-picker',
		'TColorPicker': 'global/widgets/color-picker/TColorPicker',
		'CameraBrowser': 'global/widgets/filebrowser/CameraBrowser',
		'InsideEditor': 'global/widgets/inside_editor/InsideEditor',
		'ErrorTipBox': 'global/widgets/error_tip/ErrorTipBox',
		'TFeedback': 'global/widgets/feedback/TFeedback',
		'Paging2': 'global/widgets/paging/Paging2',
		'ViewMinTabBar': 'global/widgets/view_min_tab/ViewMinTabBar',
		'RibbonSubMenuNavWidget': 'global/widgets/ribbon/RibbonSubMenuNavWidget',
		'TopNotification': 'global/widgets/top_alert/TopNotification',

		'ContextMenuConstant': 'global/ContextMenuConstant',
		'ProgressBarManager': 'global/ProgressBarManager',
		'TAlertManager': 'global/TAlertManager',
		'PermissionManager': 'global/PermissionManager',
		'TopMenuManager': 'global/TopMenuManager',
		'IndexController': 'IndexController',

		'Base': 'model/Base',
		'SearchField': 'model/SearchField',
		'ResponseObject': 'model/ResponseObject',
		'RibbonMenu': 'model/RibbonMenu',
		'RibbonSubMenu': 'model/RibbonSubMenu',
		'RibbonSubMenuGroup': 'model/RibbonSubMenuGroup',
		'RibbonSubMenuNavItem': 'model/RibbonSubMenuNavItem',
		'ServiceCaller': 'services/ServiceCaller',
		'TimeTrexClientAPI': 'services/TimeTrexClientAPI',
		'APIReturnHandler': 'model/APIReturnHandler',
		'TTBackboneView': 'views/TTBackboneView',
		'BaseViewController': 'views/BaseViewController',
		'BaseWindowController': 'views/BaseWindowController',
		'BaseWizardController': 'views/wizard/BaseWizardController',
		'UserGenericStatusWindowController': 'views/wizard/user_generic_data_status/UserGenericStatusWindowController',
		'ReportBaseViewController': 'views/reports/ReportBaseViewController',
		'qtip': 'framework/widgets/jquery.qtip/jquery.qtip.min',
		'rightclickmenu': 'framework/rightclickmenu/rightclickmenu',

		'jquery': 'framework/jquery.min',
		'jquery.form': 'framework/jquery.form.min',
		'jquery-ui': 'framework/jqueryui/jquery-ui.min',
		'jquery.i18n': 'framework/jquery.i18n',
		'underscore': 'framework/backbone/underscore-min',
		'backbone': 'framework/backbone/backbone-min',
		'jquery.masonry': 'framework/masonry.min',
		'interact': 'framework/interact.min',
		'tinymce': 'framework/tinymce/tinymce.min',
		'Global': 'global/Global',
		'LocalCacheData': 'global/LocalCacheData',
		'nanobar': 'framework/nanobar.min',
		'decimal': 'framework/decimal.min',

		'leaflet': 'framework/leaflet/leaflet',
		'leaflet-timetrex': 'framework/leaflet/leaflet-timetrex',
		'leaflet-providers': 'framework/leaflet/leaflet-providers/leaflet-providers',
		'leaflet-routing': 'framework/leaflet/leaflet-routing-machine/leaflet-routing-machine',
		'leaflet-draw': 'framework/leaflet/leaflet-draw/leaflet.draw',
		'leaflet-markercluster': 'framework/leaflet/leaflet-markercluster/leaflet.markercluster',
		'pdfjs': 'framework/pdfjs', //Need to use pdfjs-dist/build below instead, as thats what defined in the pdf.js file.
		'pdfjs-dist/build/pdf': 'framework/pdfjs/pdf',
		'pdfjs-dist/build/pdf.worker': 'framework/pdfjs/pdf.worker',
		'autolinker': 'framework/autolinker',
		'measurement': 'framework/measurement',
		'TTPromise': 'global/TTPromise',
		'TTUUID': 'global/TTUUID',

		'Wizard': 'global/widgets/wizard/Wizard',
		'WizardStep': 'global/widgets/wizard/WizardStep',

		'RateLimit': 'global/RateLimit',
		'jquery-bridget': 'framework/jquery.bridget',

		'TTGrid': 'global/widgets/ttgrid/TTGrid',

		'RequestViewCommonController': 'views/common/RequestViewCommonController',
		'EmbeddedMessageCommon': 'views/common/EmbeddedMessageCommon',
		'AuthorizationHistoryCommon': 'views/common/AuthorizationHistoryCommon',
		'BaseTreeViewController': 'views/common/BaseTreeViewController',
	},
	shim: {
		'TTGrid': {
			deps: ['jqGrid']
		},
		'jqGrid': {
			deps: ['jquery']
		},
		'APIGlobal': {
			deps: ['CookieSetting']
		},
		'LocalCacheData': {
			deps: ['APIGlobal'],
			exports: 'LocalCacheData'
		},
		'Global': {
			exports: 'Global',
			deps: [
				'polyfill',
				'backbone',
				'TTBackboneView',
				'LocalCacheData',
				'jquery.masonry',
				'APIGlobal'
			]
		},
		'underscore': {
			deps: ['polyfill'], // polyfill added in several key deps to ensure polyfills get loaded as early as possible.
			exports: '_'
		},
		'interact': {
			exports: 'interact'
		},
		'backbone': {
			deps: [
				'underscore',
				'jquery'
			],
			exports: 'Backbone'
		},
		'Base': {
			deps: ['backbone']
		},
		'TTBackboneView': {
			deps: ['backbone']
		},
		'BaseViewController': {
			deps: [
				'TTBackboneView',
				'ContextMenuConstant',
				'ServiceCaller',
				'APIGlobal'
			]
		},
		'jquery': {
			deps: ['polyfill'] // polyfill added in several key deps to ensure polyfills get loaded as early as possible.
		},
		'jquery.i18n': {
			deps: ['jquery']
		},
		'jquery-ui': {
			deps: ['jquery']
		},
		'jquery_ba_resize': {
			deps: ['jquery']
		},
		'jquery_tablednd': {
			deps: ['jquery']
		},
		'jquery_json': {
			deps: ['jquery']
		},
		'jquery.form': {
			deps: ['jquery']
		},
		'jquery.masonry': {
			deps: ['jquery', 'jquery-ui', 'jquery-bridget'],
			exports: 'jQuery.masonry'
		},
		//long dep chain
		'colors': {
			deps: ['jquery']
		},
		'colorpicker': {
			deps: ['colors']
		},
		'TColorPicker': {
			deps: ['colorpicker'],
			exports: 'jQuery.fn.TColorPicker'
		},

		'TImageCutArea': {
			deps: ['jquery']
		},
		'TImage': {
			deps: ['jquery']
		},
		'TImageAdvBrowser': {
			deps: ['jquery']
		},
		'TImageBrowser': {
			deps: ['jquery']
		},
		'TRangePicker': {
			deps: ['jquery']
		},
		'TDatePicker': {
			deps: ['jquery']
		},
		'TTimePicker': {
			deps: ['jquery']
		},
		'TTextArea': {
			deps: ['jquery']
		},
		'TText': {
			deps: ['jquery']
		},
		'TList': {
			deps: ['jquery']
		},
		'TCheckbox': {
			deps: ['jquery']
		},
		'TTagInput': {
			deps: ['jquery']
		},
		'TTextInput': {
			deps: ['jquery']
		},
		'TPasswordInput': {
			deps: ['jquery']
		},
		'TComboBox': {
			deps: ['jquery']
		},
		'jqgrid.winmultiselect': {
			deps: ['jqGrid']
		},
		'APIReturnHandler': {
			deps: ['Base']
		},
		'ResponseObject': {
			deps: ['Base']
		},
		'ServiceCaller': {
			deps: ['APIReturnHandler', 'Base', 'ResponseObject']
		},
		'BaseWindowController': {
			deps: ['TTBackboneView']
		},
		'BaseWizardController': {
			deps: ['BaseWindowController']
		},
		'IndexController': {
			deps: ['BaseWizardController']
		},
		'leaflet-providers': {
			deps: ['leaflet']
		},
		'leaflet-routing': {
			deps: ['leaflet']
		},
		'leaflet-draw': {
			deps: ['leaflet']
		},
		'leaflet-markercluster': {
			deps: ['leaflet']
		},
		'leaflet-timetrex': {
			deps: ['polyfill', 'leaflet', 'leaflet-draw', 'leaflet-routing', 'leaflet-providers', 'leaflet-markercluster', 'measurement'],
			exports: 'exportFunctions',
			init: function() {
				window.TTMapLib = exportFunctions.TTMapLib;
				window.L = exportFunctions.L;
			}
		},
		'tinymce': {
			exports: 'tinyMCE',
			init: function() {
				this.tinyMCE.DOM.events.domLoaded = true;
				return this.tinyMCE;
			}
		},

		'TimeTrexClientAPI': {
			deps: ['ServiceCaller', 'Global']
		},

		'WizardStep': {
			deps: ['Wizard', 'TTBackboneView']
		},

		'Wizard': {
			deps: ['TTBackboneView']
		}
	}
} );

require( [
	'RateLimit',
	'LocalCacheData',
	'backbone',
	'Global',
	'html2canvas',
	'moment',
	'async',
	'jquery-ui',
	'jquery.i18n',
	'jquery_ba_resize',
	'IndexController',
	'TTBackboneView',
	'BaseViewController',
	'ServiceCaller',
	'TimeTrexClientAPI',
	'TTextInput',
	'TPasswordInput',
	'FormItemType',
	'TComboBox',
	'ProgressBarManager',
	'TAlertManager',
	'TTPromise',
	'TTUUID',
	'Wizard',
	'WizardStep'
], function( r, lcd, bb, G, html2canvas, moment ) {
	window.moment = moment;
	window.html2canvas = html2canvas;

	//Don't not show loading bar if refresh
	if ( Global.isSet( LocalCacheData.getLoginUser() ) ) {
		$( '.loading-view' ).hide();
	} else {
		setProgress();
	}

	function setProgress() {
		loading_bar_time = setInterval( function() {
			var progress_bar = $( '.progress-bar' );
			var c_value = progress_bar.prop( 'value' );

			if ( c_value < 90 ) {
				progress_bar.prop( 'value', c_value + 10 );
			}
		}, 1000 );
	}

	function cleanProgress() {
		if ( $( '.loading-view' ).is( ':visible' ) ) {

			var progress_bar = $( '.progress-bar' );
			progress_bar.prop( 'value', 100 );
			clearInterval( loading_bar_time );

			loading_bar_time = setInterval( function() {
				$( '.progress-bar-div' ).hide();
				clearInterval( loading_bar_time );
			}, 50 );
		}
	}

	is_browser_iOS = ( navigator.userAgent.match( /(iPad|iPhone|iPod)/g ) ? true : false );
	ie = ( function() {
		var ua = window.navigator.userAgent;

		var trident = ua.indexOf( 'Trident/' );
		if ( trident > 0 ) {
			// IE 11 => return version number
			var rv = ua.indexOf( 'rv:' );
			return parseInt( ua.substring( rv + 3, ua.indexOf( '.', rv ) ), 10 );
		}

		return null;
	}() );

	$( function() {
		Global.styleSandbox();

		$.support.cors = true; // For IE
		cleanProgress();

		var api_authentication = TTAPI.APIAuthentication;

		if ( Error ) {
			Error.stackTraceLimit = 50; //Increase JS exception stack trace limit.
		}

		//BUG-2065 see also: require.onError()
		window.onerror = function() {
			if ( !arguments || arguments.length < 1 ) {
				Global.sendErrorReport( 'No error parameters when window.onerror', ServiceCaller.rootURL, '', '', '' );
			} else {
				Global.sendErrorReport( arguments[0], arguments[1], arguments[2], arguments[3], arguments[4] );
			}

		};

		window.addEventListener( 'beforeunload', function( e ) {
			// Note that Google recommends against the following, as it affects page caching, but we dont want caching anyway: https://developers.google.com/web/updates/2018/07/page-lifecycle-api#the-beforeunload-event
			Global.sendAnalyticsEvent( 'browser', 'browser:beforeunload', 'browser:beforeunload' );
			if ( ( LocalCacheData.current_open_primary_controller && LocalCacheData.current_open_primary_controller.edit_view && LocalCacheData.current_open_primary_controller.is_changed == true )
				|| ( LocalCacheData.current_open_report_controller && LocalCacheData.current_open_report_controller.is_changed == true )
				|| ( LocalCacheData.current_open_edit_only_controller && LocalCacheData.current_open_edit_only_controller.is_changed == true )
				|| ( LocalCacheData.current_open_sub_controller && LocalCacheData.current_open_sub_controller.edit_view && LocalCacheData.current_open_sub_controller.is_changed == true )
			) {
				e.preventDefault(); // Cancel the unload event
				e.returnValue = ''; // Chrome requires returnValue to be set
			}
		} );

		$( 'body' ).addClass( 'login-bg' );

		//Load need API class

		$( document ).on( 'keydown', function( e ) {
			if ( e.which === 8 && !$( e.target ).is( 'input, textarea' ) ) {
				e.preventDefault();
			}
		} );

		$( 'body' ).unbind( 'keydown' ).bind( 'keydown', function( e ) {

			//Tab key must go to next search text field if a search text field is selected, otherwise, tab closes awesomebox.
			//This allows consistent ui experience between awesomebox and default form input controls.
			if ( e.keyCode === 27 || e.keyCode === 13 || ( e.keyCode === 9 && e.target.type != 'text' ) ) {
				if ( LocalCacheData.openAwesomeBox ) {
					LocalCacheData.openAwesomeBox.onClose();
				}

				if ( LocalCacheData.openAwesomeBoxColumnEditor ) {
					LocalCacheData.openAwesomeBoxColumnEditor.onClose();
				}
			}

			if ( LocalCacheData.openAwesomeBox ) {
				if ( Global.isValidInputCodes( e.keyCode ) ) {
					LocalCacheData.openAwesomeBox.selectNextItem( e );
				}
			} else if ( LocalCacheData.current_open_primary_controller &&
				LocalCacheData.current_open_primary_controller.column_selector &&
				LocalCacheData.current_open_primary_controller.column_selector.is( ':visible' ) &&
				LocalCacheData.current_open_primary_controller.column_selector.has( $( ':focus' ) ).length > 0 ) {
				if ( Global.isValidInputCodes( e.keyCode ) ) {
					LocalCacheData.current_open_primary_controller.column_selector.selectNextItem( e );
				}
			}

			if ( LocalCacheData.current_open_wizard_controller && e.keyCode === 13 ) {
				if ( LocalCacheData.current_open_wizard_controller.$el.hasClass( 'change-password-wizard' ) ) {
					!LocalCacheData.current_open_wizard_controller.done_btn.attr( 'disabled' ) &&
					e.target &&
					e.target.type !== 'textarea' && LocalCacheData.current_open_wizard_controller.onDoneClick();
				}
			}

			if ( ( e.keyCode === 65 && e.metaKey === true ) || ( e.keyCode === 65 && e.ctrlKey === true ) ) {
				e.preventDefault();
				selectAll();
			}

			if ( e.keyCode === 36 ) {
				gridScrollTop();
			}

			if ( e.keyCode === 35 ) {
				gridScrollDown();
			}

			// keyboard event to quick search permission adropdown
			if ( LocalCacheData.current_open_primary_controller &&
				LocalCacheData.current_open_primary_controller.viewId === 'PermissionControl' &&
				LocalCacheData.current_open_primary_controller.edit_view ) {
				LocalCacheData.current_open_primary_controller.onKeyDown( e );
			}

		} );

		if ( window._addToDebugClickStack === undefined ) {
			window._addToDebugClickStack = function( e ) {
				// Must collect click data on 'event capture phase' vs bubbling phase, so the click is recorded as soon as possible, before any potential errors prevent the recording of last click.
				// Function added to window, to prevent duplicate click listeners (JS wont add duplicate listeners referencing the same function). More context at https://stackoverflow.com/questions/38939937/when-are-duplicate-event-listeners-discarded-and-when-are-they-not
				var ui_clicked_date = new Date();
				var ui_stack = {
					target_class: $( e.target ).attr( 'class' ) ? $( e.target ).attr( 'class' ) : '',
					target_id: $( e.target ).attr( 'id' ) ? $( e.target ).attr( 'id' ) : '',
					html: e.target.outerHTML,
					ui_clicked_date: ui_clicked_date.toISOString()
				};
				if ( LocalCacheData.ui_click_stack.length === 16 ) {
					LocalCacheData.ui_click_stack.pop();
				}

				LocalCacheData.ui_click_stack.unshift( ui_stack );

			};
			window.addEventListener( 'click', window._addToDebugClickStack, true ); // true is to set listener on 'event capture phase', so the click is recorded as soon as possible, before any potential errors prevent the recording of last click.
		}

		$( 'body' ).unbind( 'mousedown' ).bind( 'mousedown', function( e ) {
			// MUST COLLECT DATA WHEN MOUSE down, otherwise when do save in edit view when awesomebox open, the data can't be saved.
			// Mouse down to collect data so for some actions like search can read select data in its click event
			if ( LocalCacheData.openAwesomeBox && LocalCacheData.openAwesomeBox.getADropDown() && LocalCacheData.openAwesomeBox.getADropDown().has( e.target ).length < 1 ) {
				if ( $( e.target ).hasClass( 'a-combobox' ) ) {
					var target = LocalCacheData.openAwesomeBox;
					$( e.target ).unbind( 'mouseup' ).bind( 'mouseup', function( e ) {
						target.find( '.focus-input' ).focus();
						$( e.target ).unbind( 'mouseup' );
					} );
				}
				LocalCacheData.openAwesomeBox.onClose();
			}

			//This closes pickers and dropdown boxes when clicking off them.
			if ( LocalCacheData.openRangerPicker && !LocalCacheData.openRangerPicker.getIsMouseOver() ) {
				LocalCacheData.openRangerPicker.close();
			}

			if ( LocalCacheData.openAwesomeBoxColumnEditor && !LocalCacheData.openAwesomeBoxColumnEditor.getIsMouseOver() ) {
				LocalCacheData.openAwesomeBoxColumnEditor.onClose();
			}

			if ( LocalCacheData.openRibbonNaviMenu && !LocalCacheData.openRibbonNaviMenu.getIsMouseOver() ) {
				LocalCacheData.openRibbonNaviMenu.close();
			}

		} );

		var cUrl = window.location.href;

		if ( getCookie( 'js_debug' ) ) {
			var script = Global.loadScript( 'local_testing/LocalURL.js' );
			if ( script === true ) {
				cUrl = LocalURL.url();
			}

			need_load_pre_login_data = true;
		}

		cUrl = getRelatedURL( cUrl );

		ServiceCaller.baseUrl = cUrl + 'api/json/api.php';
		ServiceCaller.staticURL = ServiceCaller.baseUrl;
		ServiceCaller.orginalUrl = cUrl;
		ServiceCaller.rootURL = getRootURL( cUrl );

		var loginData = {};
		//Set in APIGlobal.php
		if ( !need_load_pre_login_data ) {
			loginData = APIGlobal.pre_login_data;
		} else {
			need_load_pre_login_data = false;
		}
		if ( !loginData.hasOwnProperty( 'api_base_url' ) ) {
			api_authentication.getPreLoginData( null, {
				onResult: function( e ) {

					var result = e.getResult();

					LocalCacheData.setLoginData( result );
					APIGlobal.pre_login_data = result;

					loginData = LocalCacheData.getLoginData();
					initApps();

				}
			} );
		} else {
			LocalCacheData.setLoginData( loginData ); //set here because the loginData is set from php
			initApps();
		}
		initAnalytics();

		function initAnalytics() {
			/* jshint ignore:start */
			if ( APIGlobal.pre_login_data.analytics_enabled === true
				&& ServiceCaller && ServiceCaller.rootURL
				&& loginData && loginData.base_url ) {
				try {
					( function( i, s, o, g, r, a, m ) {
						i['GoogleAnalyticsObject'] = r;
						i[r] = i[r] || function() {
							( i[r].q = i[r].q || [] ).push( arguments );
						}, i[r].l = 1 * new Date();
						a = s.createElement( o ),
							m = s.getElementsByTagName( o )[0];
						a.async = 1;
						a.src = g;
						m.parentNode.insertBefore( a, m );
					} )( window, document, 'script', ServiceCaller.rootURL + loginData.base_url + 'html5/framework/google/analytics/analytics.js', 'ga' );
					//ga('set', 'sendHitTask', null); //disables sending hit data to Google. uncoment when debugging GA.

					ga( 'create', APIGlobal.pre_login_data.analytics_tracking_code, 'auto' );

					//Do not check exitstance of LocalCacheData with if(LocalCacheData) or JS will execute the unnamed function it uses as a constructor
					if ( LocalCacheData.loginUser ) {
						var current_company = LocalCacheData.getCurrentCompany();
						Global.setAnalyticDimensions( LocalCacheData.getLoginUser().first_name + ' (' + LocalCacheData.getLoginUser().id + ')', current_company.name );
					} else {
						Global.setAnalyticDimensions();
					}
					ga( 'send', 'pageview', { 'sessionControl': 'start' } );
				} catch ( e ) {
					throw e; //Attempt to catch any errors thrown by Google Analytics.
				}
			}
			/* jshint ignore:end */
		}

		function initApps() {
			TAlertManager.showBrowserTopBanner(); //Checks for IE version itself.

			loadViewRequiredJS();

			//Optimization: Only change locale if its *not* en_US or enable_default_language_translation = TRUE
			if ( loginData.locale !== 'en_US' || loginData.enable_default_language_translation == true ) {
				Global.loadLanguage( loginData.locale );
				Debug.Text( 'Using Locale: ' + loginData.locale, 'main.js', '', 'initApps', 1 );
			} else {
				LocalCacheData.setI18nDic( {} );
			}

			$.i18n.load( LocalCacheData.getI18nDic() );
			Global.initStaticStrings();

			ServiceCaller.import_csv_emample = ServiceCaller.rootURL + loginData.base_url + 'html5/views/wizard/import_csv/';
			ServiceCaller.fileDownloadURL = ServiceCaller.rootURL + loginData.base_url + 'send_file.php?api=1&X-CSRF-Token=' + getCookie( 'CSRF-Token' );
			ServiceCaller.uploadURL = ServiceCaller.rootURL + loginData.base_url + 'upload_file.php';
			ServiceCaller.companyLogo = ServiceCaller.rootURL + loginData.base_url + 'send_file.php?api=1&object_type=company_logo&X-CSRF-Token=' + getCookie( 'CSRF-Token' );
			ServiceCaller.invoiceLogo = ServiceCaller.rootURL + loginData.base_url + 'send_file.php?api=1&object_type=invoice_config&X-CSRF-Token=' + getCookie( 'CSRF-Token' );
			ServiceCaller.userPhoto = ServiceCaller.rootURL + loginData.base_url + 'send_file.php?api=1&object_type=user_photo&X-CSRF-Token=' + getCookie( 'CSRF-Token' );
			ServiceCaller.mainCompanyLogo = ServiceCaller.rootURL + loginData.base_url + 'send_file.php?api=1&object_type=primary_company_logo';
			ServiceCaller.poweredByLogo = ServiceCaller.rootURL + loginData.base_url + 'send_file.php?api=1&object_type=smcopyright';
			ServiceCaller.login_page_powered_by_logo = ServiceCaller.rootURL + loginData.base_url + 'send_file.php?api=1&object_type=copyright';
			LocalCacheData.appType = loginData.deployment_on_demand;
			LocalCacheData.productEditionId = loginData.product_edition;
			var controller = new IndexViewController();

			var alternate_session_data = getCookie( 'AlternateSessionData' );
			if ( alternate_session_data ) {
				try { //Prevent JS exception if we can't parse alternate_session_data for some reason.
					alternate_session_data = JSON.parse( alternate_session_data );
					if ( alternate_session_data && alternate_session_data.previous_session_id ) {
						TAlertManager.showPreSessionAlert();
					}
				} catch ( e ) {
					Debug.Text( e.message, 'main.js', 'require', 'initApps', 10 );
				}
			}

			//When the user switched away from, then back again to our tab, make sure the session is still the same so they don't get two different login sessions confused.
			function handleVisibilityChange() {
				//No need to handle anything if installer is enabled.
				if ( APIGlobal.pre_login_data && APIGlobal.pre_login_data.installer_enabled && APIGlobal.pre_login_data.installer_enabled == true ) {
					return null;
				}

				var is_hidden = document.hidden;
				var cookie_session_id = getCookie( Global.getSessionIDKey() );

				Debug.Text( 'Tab Visibility Change: ' + is_hidden + ' Session ID: ' + LocalCacheData.getSessionID(), 'main.js', '', 'handleVisibilityChange', 10 );

				if ( is_hidden == false ) {
					//Check to make sure our session_id matches what the server returns.
					if ( cookie_session_id != LocalCacheData.getSessionID() ) {
						Debug.Text( 'Session ID has changed out from out underneath us! Session ID: Memory: ' + LocalCacheData.getSessionID() + ' Cookie: ' + cookie_session_id, 'main.js', '', 'handleVisibilityChange', 1 );

						var api = TTAPI.APIAuthentication;
						api.isLoggedIn( false, {
							onResult: function( result ) {
								var result_data = result.getResult();

								if ( result_data === true ) {
									TAlertManager.showAlert( $.i18n._( 'It appears that you have logged in from another web browser window or tab.<br><br>Please be patient while the session is resumed here...' ), 'Session Changed', function() {
										Global.sendAnalyticsEvent( 'session', 'session:changed', 'session:changed' );
										window.location.reload( true );
									} );
								} else {
									//Don't do Logout here, as we need to display a "Session Expired" message to the user, which is triggered from the ServiceCaller.
									//  In order to trigger that though, we need to make an *Authenticated* API call to APIMisc.Ping(), rather than UnAuthenticated call to APIAuthentication.Ping()
									var api = TTAPI.APIMisc;
									api.ping( {
										onResult: function() {
										}
									} );
								}
							}
						} );
					}
				}
			}

			window.addEventListener( 'visibilitychange', handleVisibilityChange );
		}

		function gridScrollDown() {
			if ( LocalCacheData.openAwesomeBox &&
				_.isFunction( LocalCacheData.openAwesomeBox.gridScrollDown ) ) {
				LocalCacheData.openAwesomeBox.gridScrollDown();
				return;
			}

			if ( LocalCacheData.current_open_sub_controller ) {
				if ( !LocalCacheData.current_open_sub_controller.edit_view &&
					_.isFunction( LocalCacheData.current_open_sub_controller.gridScrollDown ) ) {
					LocalCacheData.current_open_sub_controller.gridScrollDown();
				}
				return;
			}
			if ( LocalCacheData.current_open_primary_controller ) {
				if ( !LocalCacheData.current_open_primary_controller.edit_view &&
					_.isFunction( LocalCacheData.current_open_primary_controller.gridScrollDown ) ) {
					LocalCacheData.current_open_primary_controller.gridScrollDown();
				}
				return;
			}
		}

		function gridScrollTop() {
			if ( LocalCacheData.openAwesomeBox &&
				_.isFunction( LocalCacheData.openAwesomeBox.gridScrollTop ) ) {
				LocalCacheData.openAwesomeBox.gridScrollTop();
				return;
			}
			if ( LocalCacheData.current_open_sub_controller ) {
				if ( !LocalCacheData.current_open_sub_controller.edit_view &&
					_.isFunction( LocalCacheData.current_open_sub_controller.gridScrollTop ) ) {
					LocalCacheData.current_open_sub_controller.gridScrollTop();
				}
				return;
			}
			//Error: Uncaught TypeError: LocalCacheData.current_open_primary_controller.gridScrollTop is not a function in interface/html5/main.js?v=9.0.2-20151106-092147 line 434
			if ( LocalCacheData.current_open_primary_controller ) {
				if ( !LocalCacheData.current_open_primary_controller.edit_view &&
					_.isFunction( LocalCacheData.current_open_primary_controller.gridScrollTop ) ) {
					LocalCacheData.current_open_primary_controller.gridScrollTop();
				}
				return;
			}
		}

		function selectAll() {
			//Error: Uncaught TypeError: LocalCacheData.current_open_primary_controller.selectAll is not a function in interface/html5/main.js?v=9.0.4-20151123-121757 line 457
			if ( LocalCacheData.openAwesomeBox &&
				_.isFunction( LocalCacheData.openAwesomeBox.selectAll ) ) {
				LocalCacheData.openAwesomeBox.selectAll();
				return;
			}

			if ( LocalCacheData.current_open_sub_controller ) {

				if ( !LocalCacheData.current_open_sub_controller.edit_view &&
					_.isFunction( LocalCacheData.current_open_sub_controller.selectAll ) ) {
					LocalCacheData.current_open_sub_controller.selectAll();
				}

				return;
			}

			if ( LocalCacheData.current_open_primary_controller ) {
				if ( !LocalCacheData.current_open_primary_controller.edit_view &&
					_.isFunction( LocalCacheData.current_open_primary_controller.selectAll ) ) {
					LocalCacheData.current_open_primary_controller.selectAll();
				}
				return;
			}
		};

	} );

	function loadViewRequiredJS() {
		LocalCacheData.loadViewRequiredJSReady = false;

		require( [
			//NOTE: If the order is changed here, you must update the below require() line as well. ie: require( require_array, function( Backbone, Global, BaseViewController, Nanobar, Decimal )
			'backbone',
			'Global',
			'nanobar', //only in timesheet
			'decimal', //only in Pay Stub and Pay Stub Amendments currently.
			'jquery_json',
			'rightclickmenu',
			'jquery_tablednd',
			'TTagInput',
			'timepicker_addon',
			'TDatePicker',
			'TTimePicker',
			'TRangePicker',
			'TTextArea',
			'InsideEditor',
			'ErrorTipBox',
			'TFeedback',
			'TText',
			'TList',
			'TToggleButton',
			'SwitchButton',
			'TCheckbox',
			'ViewMinTabBar',
			'TopNotification',
			'RibbonMenu',
			'RibbonSubMenu',
			'RibbonSubMenuGroup',
			'RibbonSubMenuNavItem',
			'RibbonSubMenuNavWidget',
			'SearchPanel',
			'jqGrid',
			'jqgrid.winmultiselect',
			'ImageAreaSelect',
			'qtip',
			'SearchField',
			'PermissionManager',
			'TopMenuManager',
			'TGridHeader',
			'ADropDown',
			'AComboBox',
			'ASearchInput',
			'ALayoutCache',
			'ALayoutIDs',
			'ColumnEditor',
			'SaveAndContinueBox',
			'NoHierarchyBox',
			'NoResultBox',
			'SeparatedBox',
			'BaseWizardController',
			'UserGenericStatusWindowController',
			'ReportBaseViewController',
			'Paging2',
			'jquery-ui',
			'TTGrid',
			'AuthorizationHistoryCommon',
			'RequestViewCommonController',
			'EmbeddedMessageCommon',
			'BaseTreeViewController'
		], function( Backbone, Global, Nanobar, Decimal ) {
			window.Nanobar = Nanobar;
			window.Decimal = Decimal;

			//Revert jQuery preFilter behavior in v3.5.0 back to pre-v3.5.0 so it doesn't break jqGrid getGridParam( 'colModel' ).
			// Attendance -> TimeSheet, expand employee dropdown, click icon to customize columns, triggers JS exception: Uncaught TypeError: Cannot read property 'width' of undefined
			var rxhtmlTag = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([a-z][^\/\0>\x20\t\r\n\f]*)[^>]*)\/>/gi;
			jQuery.htmlPrefilter = function( html ) {
				return html.replace( rxhtmlTag, "<$1></$2>" );
			};

			LocalCacheData.loadViewRequiredJSReady = true;
		} );
	}

	function stripDuplicateSlashes( url ) {
		return url.replace( /([^:]\/)\/+/g, '$1' );
	}

	function getRelatedURL( url ) {
		url = stripDuplicateSlashes( url );
		var a = url.split( '/' ); //Strip duplicate slashes

		var targetIndex = ( a.length - 3 );
		var newUrl = '';
		for ( var i = 0; i < targetIndex; i++ ) {
			if ( i !== 1 ) {
				newUrl = newUrl + a[i] + '/';
			} else {
				newUrl = newUrl + '/';
			}

		}

		return newUrl;
	}

	function getRootURL( url ) {
		url = stripDuplicateSlashes( url );
		var a = url.split( '/' );
		var targetIndex = 3;
		var newUrl = '';
		for ( var i = 0; i < targetIndex; i++ ) {
			if ( i !== 1 && i < 2 ) {
				newUrl = newUrl + a[i] + '/';
			} else if ( i === 1 ) {
				newUrl = newUrl + '/';
			} else if ( i === 2 ) {
				newUrl = newUrl + a[i];
			}

		}

		return newUrl;
	}

} );

/**
 * FIXES: BUG-2065 "Uncaught Error: Script error for ..."
 *
 * REASON FOR BUG: The internet going out momentarily
 *    after the initial scripts have loaded and have begun
 *    loading the chain of javascript-loaded scripts causes
 *    requirejs to throw the Script Errors because it's unable
 *    to reach the files it's being told to load.
 *
 * TO REPRODUCE: in chrome, throttle the internet down then
 *    turn off the internet once the reload button shows on
 *    the login screen (and the stop button hides). This ensures
 *    that requirejs has been loaded and is loading other scripts.
 */
require.onError = function( err ) {
	console.error( err );
	if ( err.message.indexOf( 'Script error for' ) != -1 ) {
		if ( require.script_error_shown == undefined ) {
			require.script_error_shown = 1;
			//There is no pretty errorbox at this time. You may only have basic javascript.
			if ( confirm( 'Unable to download required data. Your internet connection may have failed press Ok to reload.' ) ) {
				//For testing, so that there's time to turn internet back on after confirm is clicked.
				//window.setTimeout(function() {window.location.reload()},5000);

				//This can also happen if the user manually modifies the URL to be a bogus ViewId (ie: #!m=homeABC)
				//So try to redirect back to the home page first, otherwise try to do a browser reload.
				if ( ServiceCaller.rootURL && APIGlobal.pre_login_data.base_url ) {
					Global.setURLToBrowser( ServiceCaller.rootURL + APIGlobal.pre_login_data.base_url );
				} else {
					window.location.reload();
				}

			}
		}
		console.debug( err.message );
		//Stop error from bubbling up.
		delete err;
	}
};