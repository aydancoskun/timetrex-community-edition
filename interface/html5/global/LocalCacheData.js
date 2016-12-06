var LocalCacheData = function() {

};

LocalCacheData.view_layout_cache = {};

LocalCacheData.i18nDic = null;

LocalCacheData.notification_bar = null;

LocalCacheData.ui_click_stack = [];

LocalCacheData.api_stack = [];

LocalCacheData.last_timesheet_selected_date = null;

LocalCacheData.last_timesheet_selected_user = null;

LocalCacheData.last_schedule_selected_date = null;

LocalCacheData.current_open_wizard_controller = null; // cache opened wizard conroller, only one wizard open at a time

LocalCacheData.default_filter_for_next_open_view = null;

LocalCacheData.extra_filter_for_next_open_view = null;

LocalCacheData.default_edit_id_for_next_open_edit_view = null; //First use in save report jump to report

LocalCacheData.current_open_view_id = ''; // Save current open view's id. set in BaseViewController.loadView

LocalCacheData.login_error_string = ''; //Error message show on Login Screen

LocalCacheData.all_url_args = null; //All args from URL

LocalCacheData.current_open_primary_controller = null; // Save current open view's id. set in BaseViewController.loadView

LocalCacheData.current_open_sub_controller = null; // Save current open view's id. set in BaseViewController.loadView

LocalCacheData.current_open_edit_only_controller = null; // Save current open view's id. set in BaseViewController.loadView

LocalCacheData.current_open_report_controller = null; //save open report view controller

LocalCacheData.current_doing_context_action = ''; //Save what context action is doing right now

LocalCacheData.current_selet_date = ''; // Save

LocalCacheData.edit_id_for_next_open_view = '';

LocalCacheData.url_args = null;

LocalCacheData.result_cache = {};

LocalCacheData.paging_type = 10;  //0 is CLick to show more, 10 is normal paging

LocalCacheData.currentShownContextMenuName = '';

LocalCacheData.isSupportHTML5LocalCache = false;

//LocalCacheData.isApplicationBranded = null;

LocalCacheData.loginData = null;

LocalCacheData.currentLanguage = 'en_us';

LocalCacheData.currentLanguageDic = {};

LocalCacheData.enablePoweredByLogo = null;

LocalCacheData.appType = null;

LocalCacheData.productEditionId = null;

LocalCacheData.debuger = null;

LocalCacheData.applicationName = null;

LocalCacheData.loginUser = null;

LocalCacheData.loginUserPreference = null;

LocalCacheData.openAwesomeBox = null; //To help make sure only one Awesomebox is shown at one time. Do mouse click outside job

LocalCacheData.openAwesomeBoxColumnEditor = null; //To Make sure only one column editor of Awesomebox is shown at one time Do mouse click outside job

LocalCacheData.openRibbonNaviMenu = null;

LocalCacheData.loadedWidgetCache = {};

LocalCacheData.loadedScriptNames = {}; //Save load javascript, prevent multiple load

LocalCacheData.permissionData = null;

LocalCacheData.uniqueCountryArray = null;

LocalCacheData.currentSelectMenuId = null;

LocalCacheData.currentSelectSubMenuId = null;

LocalCacheData.timesheet_sub_grid_expended_dic = {};

LocalCacheData.view_min_map = {};

LocalCacheData.view_min_tab_bar = null;

LocalCacheData.cookie_path = APIGlobal.pre_login_data.cookie_base_url;

LocalCacheData.domain_name = '';

LocalCacheData.fullUrlParameterStr = '';

LocalCacheData.setLocalCache = function( key, val, format ) {
	if ( LocalCacheData.isSupportHTML5LocalCache ) {

		if ( format === 'JSON' ) {

			sessionStorage.setItem( key, JSON.stringify( val ) );
		} else {
			sessionStorage.setItem( key, val );
		}

	}

	LocalCacheData[key] = val;
};
/**
 * BUG#2066
 * JavaScript was reporting: TypeError: Cannot read property 'product_edition_id' of null
 *
 * This appears to be caused by a person closing the browser and reopening it with a "return to where I was" option active.
 * The browser is trying to load local cache data and it may be incomplete in this scenario, which generates the error. We could not reproduce this reliably.
 * To fix it, we created LocalCacheData.getRequiredLocalCache(), and called it for mission critical cache chunks instead of LocalCacheData.getLocalCache()
 */
LocalCacheData.getRequiredLocalCache = function( key, format ) {
	var result = LocalCacheData.getLocalCache( key, format );
	if ( result == null ) {
		//There are 2 cases where result can be null.
		//  First is the cache going dead.
		//  Second is that a required local cache item is not yet loaded because most of the required data isn't set yet.
		//  In the second case we need to fail gracefully to show the error and stack trace on the console.
		try {
			Global.sendErrorReport( 'ERROR: Unable to get required local cache data: '+ key, window.location, '', '', '' );
			TAlertManager.showConfirmAlert($.i18n._('Local cache has expired. Click Yes to reload.'), $.i18n._('ERROR'), function(choice) {
				if ( choice ) {
					window.location.reload();
				}
			} );
		} catch ( e ) {
			// Early page loads won't have Global or TAlertManager
			console.debug('ERROR: Unable to get required local cache data: '+ key);
			console.debug('ERROR: Unable to report error to server: '+ key);
			console.debug(e.stack);
			if ( confirm('Local cache has expired. Click OK to reload.') ) {
				window.location.reload();
			}
		}
	}
	return result;
};

LocalCacheData.getLocalCache = function( key, format ) {
	//BUG#2066 - For testing bad cache. See getrequiredlocalcache
	//if(key == 'current_company'){return null}
	if ( LocalCacheData[key] ) {
		return LocalCacheData[key];
	} else if ( !LocalCacheData[key] && sessionStorage[key] ) {
		var result = sessionStorage.getItem( key );

		if ( result !== 'undefined' && format === 'JSON' ) {
			result = JSON.parse( result )
		}

		if ( result === 'true' ) {
			result = true;
		} else if ( result === 'false' ) {
			result = false;
		}

		LocalCacheData[key] = result;

		return LocalCacheData[key];
	}

	return null;
};

LocalCacheData.getI18nDic = function() {
	return LocalCacheData.getLocalCache( 'i18nDic', 'JSON' );
};

LocalCacheData.setI18nDic = function( val ) {

	LocalCacheData.setLocalCache( 'i18nDic', val, 'JSON' );
};

LocalCacheData.getViewMinMap = function() {
	return LocalCacheData.getLocalCache( 'viewMinMap', 'JSON' );
};

LocalCacheData.setViewMinMap = function( val ) {

	LocalCacheData.setLocalCache( 'viewMinMap', val, 'JSON' );
};

//LocalCacheData.getIsApplicationBranded = function() {
//	return LocalCacheData.getLocalCache( 'isApplicationBranded' );
//};

//LocalCacheData.setIsApplicationBranded = function( val ) {
//
//	LocalCacheData.setLocalCache( 'isApplicationBranded', val );
//};

//LocalCacheData.getOrgUrl = function() {
//	return LocalCacheData.getLocalCache( 'OrgUrl' );
//};
//
//LocalCacheData.setOrgUrl = function( val ) {
//	LocalCacheData.setLocalCache( 'OrgUrl', val );
//};

LocalCacheData.getCopyRightInfo = function() {
	return LocalCacheData.getLocalCache( 'copyRightInfo' );
};

LocalCacheData.setCopyRightInfo = function( val ) {
	LocalCacheData.setLocalCache( 'copyRightInfo', val );
};

LocalCacheData.getApplicationName = function() {
	return LocalCacheData.getRequiredLocalCache( 'applicationName' );
};

LocalCacheData.setApplicationName = function( val ) {
	LocalCacheData.setLocalCache( 'applicationName', val );
};

LocalCacheData.getCurrentCompany = function() {
	return LocalCacheData.getRequiredLocalCache( 'current_company', 'JSON' );
};

LocalCacheData.setCurrentCompany = function( val ) {
	LocalCacheData.setLocalCache( 'current_company', val, 'JSON' );
};

LocalCacheData.getLoginUser = function() {
	//Can't be set to required as the data is chekced for null to trigger cache load.
	//See loginViewController.onLoginSuccess()
	return LocalCacheData.getLocalCache( 'loginUser', 'JSON' );
};

LocalCacheData.setLoginUser = function( val ) {
	LocalCacheData.setLocalCache( 'loginUser', val, 'JSON' );
};

LocalCacheData.getCurrentCurrencySymbol = function() {
	return LocalCacheData.getLocalCache( 'currentCurrencySymbol' );
};

LocalCacheData.setCurrentCurrencySymbol = function( val ) {
	LocalCacheData.setLocalCache( 'currentCurrencySymbol', val );
};

LocalCacheData.getLoginUserPreference = function() {
	return LocalCacheData.getRequiredLocalCache( 'loginUserPreference', 'JSON' );
};

LocalCacheData.setLoginUserPreference = function( val ) {
	LocalCacheData.setLocalCache( 'loginUserPreference', val, 'JSON' );
};

LocalCacheData.getPermissionData = function() {
	return LocalCacheData.getRequiredLocalCache( 'permissionData', 'JSON' );
};

LocalCacheData.setPermissionData = function( val ) {
	LocalCacheData.setLocalCache( 'permissionData', val, 'JSON' );
};

LocalCacheData.getUniqueCountryArray = function() {
	return LocalCacheData.getRequiredLocalCache( 'uniqueCountryArray', 'JSON' );
};

LocalCacheData.setUniqueCountryArray = function( val ) {
	LocalCacheData.setLocalCache( 'uniqueCountryArray', val, 'JSON' );
};

LocalCacheData.getStationID = function() {

	var result = LocalCacheData.getLocalCache( 'StationID' );
	if ( !result ) {
		result = ''
	}

	return result;
};

LocalCacheData.setStationID = function( val ) {
	LocalCacheData.setLocalCache( 'StationID', val );
};

LocalCacheData.getSessionID = function() {

	var result = LocalCacheData.getLocalCache( 'SessionID' );
	if ( !result ) {
		result = ''
	}

	return result;
};

LocalCacheData.setSessionID = function( val ) {

	LocalCacheData.setLocalCache( 'SessionID', val );
};

LocalCacheData.getLoginData = function() {
	return LocalCacheData.getRequiredLocalCache( 'loginData', 'JSON' );
};

LocalCacheData.setLoginData = function( val ) {

	LocalCacheData.setLocalCache( 'loginData', val, 'JSON' );
};

LocalCacheData.getCurrentSelectMenuId = function() {
	return LocalCacheData.getLocalCache( 'currentSelectMenuId' );
};

LocalCacheData.setCurrentSelectMenuId = function( val ) {

	LocalCacheData.setLocalCache( 'currentSelectMenuId', val );
};

LocalCacheData.getCurrentSelectSubMenuId = function() {
	return LocalCacheData.getLocalCache( 'currentSelectSubMenuId' );
};

LocalCacheData.setCurrentSelectSubMenuId = function( val ) {

	LocalCacheData.setLocalCache( 'currentSelectSubMenuId', val );
};

LocalCacheData.cleanNecessaryCache =  function() {
	Debug.Text('Clearing Cache', 'LoginViewController.js', 'LoginViewController', 'cleanNecessaryCache', 10)
	LocalCacheData.last_timesheet_selected_user = null;
	LocalCacheData.last_timesheet_selected_date = null;
	//JS load Optimize
	if ( LocalCacheData.loadViewRequiredJSReady ) {
		ALayoutCache.layout_dic = {};
	}
	LocalCacheData.view_layout_cache = {};
	LocalCacheData.result_cache = {};
	if ( LocalCacheData.current_open_wizard_controller ) {
		LocalCacheData.current_open_wizard_controller.onCloseClick();
		LocalCacheData.current_open_wizard_controller = null;
	}
	Global.cleanViewTab();
};