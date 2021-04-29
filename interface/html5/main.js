// General 3rd party vendor code
import * as StackTrace from 'stacktrace-js'
import 'expose-loader?exposes[]=$&exposes[]=jQuery!jquery'; // Needs be done with expose otherwise other imports error "jQuery/$ is not defined." e.g. plugins like i18n.
import 'expose-loader?exposes[]=_!underscore'; // Needs be done with expose otherwise other imports error "_ is not defined." e.g. like functions in Global.isSet
import 'backbone'; // does not seem to need expose loader.
import * as moment from 'moment';

import './framework/jquery.i18n.js';
import html2canvas from 'html2canvas';
import jQueryBaResize from 'raw-loader!./framework/jquery.ba-resize.js'; // see Function(jQueryBaResize) statement below too for more and reasoning.
// import './framework/jquery.ba-resize.js'; // wont work unless last line in plugin is changed from 'this' to 'window'. Hence the workaround, to avoid modifying plugin.

// JS
import 'expose-loader?exposes=Global|Global!@/global/Global';
import { LocalCacheData } from '@/global/LocalCacheData';
import { Base } from '@/model/Base';
import { BaseWindowController } from '@/views/BaseWindowController';
import { ServiceCaller } from '@/services/ServiceCaller';
import { BaseViewController } from '@/views/BaseViewController';
import { TTAPI } from '@/services/TimeTrexClientAPI';
import { IndexViewController } from '@/IndexController';
// import { ReportBaseViewController } from '@/views/reports/ReportBaseViewController'; // Moved to post-login-app-dependancies
import { TTUUID } from '@/global/TTUUID';
import { TTPromise } from '@/global/TTPromise';// Potentially the same issue as ProgressBar (multiple instances, no single state), so lets just make it global to be safe.
import { ProgressBar } from '@/global/ProgressBarManager'; // Must only be imported in one place, otherwise multiple instances could be loaded and clash by leaving open Progressbars. e.g. Seen in TimeSheet
import { BaseWizardController } from '@/views/wizard/BaseWizardController';
import { PermissionManager } from '@/global/PermissionManager';
import { TAlertManager } from '@/global/TAlertManager'
import { TopMenuManager } from 'exports-loader?exports=TopMenuManager!@/global/TopMenuManager';

import '@/global/widgets/text_input/TTextInput.js';
import '@/global/widgets/text_input/TPasswordInput.js';
import '@/global/widgets/combobox/TComboBox.js';
import { Global } from '@/global/Global';

// Main execution for imported code
window.html2canvas = html2canvas;
window.StackTrace = StackTrace;
window.moment = moment;
Function(jQueryBaResize)(); // Need this to execute in correct scope where this=window, otherwise the 'this' value in the jquery plugin results to {} instead of real window, as the 'this' param passed into the plugins IIFE is not window, but {}, which means `window` in plugin = {}. Results in error: "TypeError: window[str_setTimeout] is not a function"

// not needed if cookie script included in html <script>
// window.getCookie = getCookie;
// window.setCookie = setCookie;
// window.deleteCookie = deleteCookie;

// Main execution for imported code
// Define the globals for various plugins and views.
// window.TTBackboneView = TTBackboneView;
// window.Global = Global;
window.LocalCacheData = LocalCacheData;
window.Base = Base;
window.BaseWindowController = BaseWindowController;
window.ServiceCaller = ServiceCaller;
window.BaseViewController = BaseViewController;
window.TTAPI = TTAPI;
window.IndexViewController = IndexViewController;
// window.ReportBaseViewController = ReportBaseViewController;
window.TTUUID = TTUUID;
window.TTPromise = TTPromise;
window.ProgressBar = ProgressBar;
window.BaseWizardController = BaseWizardController;
window.PermissionManager = PermissionManager;
window.TAlertManager = TAlertManager;
window.TopMenuManager = TopMenuManager;


// TODO: At some point, the code in this file should be refactored into classes.

window.addEventListener('load', loadViewRequiredJS);

//Don't not show loading bar if refresh
if ( Global.isSet( LocalCacheData.getLoginUser() ) ) {
	$( '.loading-view' ).hide();
} else {
	setProgress();
}

function setProgress() {
	window.loading_bar_time = setInterval( function() {
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

window.is_browser_iOS = ( navigator.userAgent.match( /(iPad|iPhone|iPod)/g ) ? true : false );

$( function() {
	Global.styleSandbox();

	cleanProgress();

	var api_authentication = TTAPI.APIAuthentication;

	if ( Error ) {
		Error.stackTraceLimit = 50; //Increase JS exception stack trace limit.
	}

	//BUG-2065 see also: require.onError()
	window.onerror = function( error_msg, file, line, col, error_obj ) {
		if ( !arguments || arguments.length < 1 ) {
			Global.sendErrorReport( 'No error parameters when window.onerror', ServiceCaller.root_url, '', '', '' );
		} else {
			Global.sendErrorReport( error_msg, file, line, col, error_obj );
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

	ServiceCaller.base_url = Global.getBaseURL( '../../', false );
	ServiceCaller.base_api_url = 'api/json/api.php';
	ServiceCaller.root_url = Global.getRootURL();

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
			&& ServiceCaller && ServiceCaller.root_url
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
				} )( window, document, 'script', ServiceCaller.root_url + loginData.base_url + 'html5/framework/google/analytics/analytics.js', 'ga' );
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
		TAlertManager.showBrowserTopBanner(); //Checks for supported browser versions and display banner if not.

		// 	loadViewRequiredJS(); // Moved to top of document, waiting on 'load' completion, to load after the 'red line'

		//Optimization: Only change locale if its *not* en_US or enable_default_language_translation = TRUE
		if ( loginData.locale !== 'en_US' || loginData.enable_default_language_translation == true ) {
			Global.loadLanguage( loginData.locale );
			Debug.Text( 'Using Locale: ' + loginData.locale, 'main.js', '', 'initApps', 1 );
		} else {
			LocalCacheData.setI18nDic( {} );
		}

		$.i18n.load( LocalCacheData.getI18nDic() );
		Global.initStaticStrings();

		LocalCacheData.appType = loginData.deployment_on_demand;
		LocalCacheData.productEditionId = loginData.product_edition;
		var controller = new IndexViewController(); //Even though controller variable is not used, this must be called.

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

	//Revert jQuery preFilter behavior in v3.5.0 back to pre-v3.5.0 so it doesn't break jqGrid getGridParam( 'colModel' ).
	// Attendance -> TimeSheet, expand employee dropdown, click icon to customize columns, triggers JS exception: Uncaught TypeError: Cannot read property 'width' of undefined
	var rxhtmlTag = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([a-z][^\/\0>\x20\t\r\n\f]*)[^>]*)\/>/gi;
	jQuery.htmlPrefilter = function( html ) {
		return html.replace( rxhtmlTag, "<$1></$2>" );
	};

	import(
		/* webpackChunkName: "post-login-main_ui-vendor-dependancies" */
		'@/post-login-main_ui-vendor-dependancies'
	).catch( Global.importErrorHandler );
	// .then(function() {
	// 	LocalCacheData.loadViewRequiredJSReady = true; // #2848 Moved to post-login-main_ui-dependancies.js to ensure all dependancies fully loaded.
	// });
}

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
// require.onError = function( err ) {
// 	console.error( err );
// 	if ( err.message.indexOf( 'Script error for' ) != -1 ) {
// 		if ( require.script_error_shown == undefined ) {
// 			require.script_error_shown = 1;
// 			//There is no pretty errorbox at this time. You may only have basic javascript.
// 			if ( confirm( 'Unable to download required data. Your internet connection may have failed press Ok to reload.' ) ) {
// 				//For testing, so that there's time to turn internet back on after confirm is clicked.
// 				//window.setTimeout(function() {window.location.reload()},5000);
//
// 				//This can also happen if the user manually modifies the URL to be a bogus ViewId (ie: #!m=homeABC)
// 				//So try to redirect back to the home page first, otherwise try to do a browser reload.
// 				if ( ServiceCaller.root_url && APIGlobal.pre_login_data.base_url ) {
// 					Global.setURLToBrowser( ServiceCaller.root_url + APIGlobal.pre_login_data.base_url );
// 				} else {
// 					window.location.reload();
// 				}
//
// 			}
// 		}
// 		console.debug( err.message );
// 		//Stop error from bubbling up.
// 		delete err;
// 	}
// };