<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2018 TimeTrex Software Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by
 * the Free Software Foundation with the addition of the following permission
 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
 * WORK IN WHICH THE COPYRIGHT IS OWNED BY TIMETREX, TIMETREX DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact TimeTrex headquarters at Unit 22 - 2475 Dobbin Rd. Suite
 * #292 West Kelowna, BC V4T 2E9, Canada or at email address info@timetrex.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Powered by TimeTrex" logo. If the display of the logo is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Powered by TimeTrex".
 ********************************************************************************/
if ( isset($_GET['disable_db']) AND $_GET['disable_db'] == 1 ) {
	$disable_database_connection = TRUE;
}

require_once('../../includes/global.inc.php');
if ( isset( $_SERVER['REQUEST_URI'] ) AND strpos( $_SERVER['REQUEST_URI'], '//' ) !== FALSE ) { //Always strip duplicate a slashes from URL whenever possible.
	Debug::text('Stripping duplicate slashes from URL: '. $_SERVER['REQUEST_URI'], __FILE__, __LINE__, __METHOD__, 10);
	Redirect::Page( Environment::stripDuplicateSlashes( $_SERVER['REQUEST_URI'] ) );
}

forceNoCacheHeaders(); //Send headers to disable caching.

//PreLoad CSS
header('Link: <theme/default/css/application.css?v='. APPLICATION_BUILD .'>; rel=preload; as=style', FALSE);
header('Link: <theme/default/css/views/login/LoginView.css?v='. APPLICATION_BUILD .'>; rel=preload; as=style', FALSE);
header('Link: <theme/default/css/global/widgets/loading_bar/LoadingBox.css?v='. APPLICATION_BUILD .'>; rel=preload; as=style', FALSE);
header('Link: <framework/bootstrap/css/bootstrap.min.css?v='. APPLICATION_BUILD .'>; rel=preload; as=style', FALSE);
header('Link: <framework/bootstrap/css/bootstrap-reboot.min.css?v='. APPLICATION_BUILD .'>; rel=preload; as=style', FALSE);
header('Link: <theme/default/css/text_layer_builder.css?v='. APPLICATION_BUILD .'>; rel=preload; as=style', FALSE);
header('Link: <theme/default/css/right_click_menu/rightclickmenu.css?v='. APPLICATION_BUILD .'>; rel=preload; as=style', FALSE);
header('Link: <theme/default/css/global/widgets/timepicker/TTimePicker.css?v='. APPLICATION_BUILD .'>; rel=preload; as=style', FALSE);
header('Link: <theme/default/css/global/widgets/datepicker/TDatePicker.css?v='. APPLICATION_BUILD .'>; rel=preload; as=style', FALSE);
header('Link: <theme/default/css/global/widgets/ribbon/RibbonView.css?v='. APPLICATION_BUILD .'>; rel=preload; as=style', FALSE);
header('Link: <theme/default/css/global/widgets/search_panel/SearchPanel.css?v='. APPLICATION_BUILD .'>; rel=preload; as=style', FALSE);
header('Link: <theme/default/css/views/attendance/timesheet/TimeSheetView.css?v='. APPLICATION_BUILD .'>; rel=preload; as=style', FALSE);
header('Link: <theme/default/css/views/attendance/schedule/ScheduleView.css?v='. APPLICATION_BUILD .'>; rel=preload; as=style', FALSE);
header('Link: <theme/default/css/jquery-ui/jquery-ui.min.css?v='. APPLICATION_BUILD .'>; rel=preload; as=style', FALSE);
header('Link: <framework/widgets/jquery.qtip/jquery.qtip.min.css?v='. APPLICATION_BUILD .'>; rel=preload; as=style', FALSE);
header('Link: <theme/default/css/image_area_select/imgareaselect-default.css?v='. APPLICATION_BUILD .'>; rel=preload; as=style', FALSE);
header('Link: <theme/default/css/views/wizard/Wizard.css?v='. APPLICATION_BUILD .'>; rel=preload; as=style', FALSE);
header('Link: <theme/default/css/ui.jqgrid.css?v='. APPLICATION_BUILD .'>; rel=preload; as=style', FALSE);
if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
	header('Link: <framework/leaflet/leaflet.css?v='. APPLICATION_BUILD .'>; rel=preload; as=style', FALSE);
	header('Link: <framework/leaflet/leaflet-draw/leaflet.draw.css?v='. APPLICATION_BUILD .'>; rel=preload; as=style', FALSE);
	header('Link: <framework/leaflet/leaflet-routing-machine/leaflet-routing-machine.css?v='. APPLICATION_BUILD .'>; rel=preload; as=style', FALSE);
}

//PreLoad JS
header('Link: <global/APIGlobal.js.php?disable_db='. ( isset($disable_database_connection) ? (int)$disable_database_connection : 0 ) .'&v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
header('Link: <global/CookieSetting.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);

header('Link: <framework/html2canvas.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
header('Link: <global/Debug.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
header('Link: <framework/require_async_plugin.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
header('Link: <framework/require.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
header('Link: <global/RateLimit.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
header('Link: <global/LocalCacheData.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
header('Link: <global/Global.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
header('Link: <main.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
header('Link: <IndexController.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <model/Base.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <model/APIReturnHandler.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <model/ResponseObject.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <views/BaseWindowController.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <views/BaseViewController.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <views/wizard/BaseWizardController.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/TAlertManager.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/ProgressBarManager.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/text_input/TTextInput.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/text_input/TPasswordInput.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/combobox/TComboBox.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/loading_bar/LoadingBox.html?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <views/login/LoginView.html?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
header('Link: <views/login/LoginViewController.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);

header('Link: <global/TTPromise.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/TTUUID.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <services/APIFactory.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/ContextMenuConstant.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <framework/fastclick.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <framework/sonic.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <framework/backbone/underscore-min.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <framework/backbone/backbone-min.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <framework/jquery.min.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <framework/jquery.json.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <framework/jquery.i18n.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <framework/jqueryui/jquery-ui.min.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <framework/jquery.bridget.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <framework/jquery.ba-resize.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <framework/jquery.tablednd.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <theme/default/images/login_view_bg.jpg?v='. APPLICATION_BUILD .'>; rel=preload; as=image', FALSE);
//header('Link: <../send_file.php?api=1&object_type=primary_company_logo&v='. APPLICATION_BUILD .'>; rel=preload; as=image', FALSE);
//header('Link: <../send_file.php?api=1&object_type=copyright&v='. APPLICATION_BUILD .'>; rel=preload; as=image', FALSE);
//header('Link: <theme/default/images/50_bg.png?v='. APPLICATION_BUILD .'>; rel=preload; as=image', FALSE);

//header('Link: <framework/google/analytics/analytics.js>; rel=preload; as=script', FALSE); //Skip APPLICATION_BUILD on this one.
//header('Link: <framework/jquery.imgareaselect.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <framework/moment.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <framework/widgets/jquery.qtip/jquery.qtip.min.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <framework/masonry.min.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <framework/nanobar.min.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <framework/rightclickmenu/rightclickmenu.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <framework/widgets/datepicker/jquery-ui-timepicker-addon.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <framework/widgets/jqgrid/jquery.jqgrid.min.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <framework/widgets/jqgrid/jquery.jqgrid.winmultiselect.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/ttgrid/TTGrid.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <model/SearchField.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/search_panel/FormItemType.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/search_panel/SearchPanel.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/wizard/Wizard.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/wizard/WizardStep.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/inside_editor/InsideEditor.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/error_tip/ErrorTipBox.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/feedback/TFeedback.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/toggle_button/TToggleButton.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/switch_button/SwitchButton.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/view_min_tab/ViewMinTabBar.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/top_alert/TopNotification.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/ribbon/RibbonSubMenuNavWidget.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/PermissionManager.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/TopMenuManager.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/jqgrid/TGridHeader.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/awesomebox/ADropDown.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/awesomebox/AComboBox.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/awesomebox/ASearchInput.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/awesomebox/ALayoutCache.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/awesomebox/ALayoutIDs.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/column_editor/ColumnEditor.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/message_box/SaveAndContinueBox.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/message_box/NoHierarchyBox.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/message_box/NoResultBox.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/separated_box/SeparatedBox.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/tag_input/TTagInput.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/datepicker/TDatePicker.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/timepicker/TTimePicker.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/datepicker/TRangePicker.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/textarea/TTextArea.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/text/TText.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/list/TList.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/checkbox/TCheckbox.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <global/widgets/paging/Paging2.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <model/RibbonMenu.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <model/RibbonSubMenu.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <model/RibbonSubMenuGroup.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <model/RibbonSubMenuNavItem.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <views/wizard/user_generic_data_status/UserGenericStatusWindowController.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <views/reports/ReportBaseViewController.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <views/common/AuthorizationHistoryCommon.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <views/common/RequestViewCommonController.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <views/common/EmbeddedMessageCommon.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <views/common/BaseTreeViewController.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <services/ServiceCaller.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <services/unauthenticated/APIAuthentication.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <services/APIDate.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <services/APICurrentUser.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <services/core/APIProgressBar.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <services/core/APILog.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <services/core/APINotification.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <services/core/APICurrency.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <services/core/APIMisc.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <services/core/APIPermission.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <services/core/APIPermissionControl.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <services/core/APIOtherField.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <services/users/APIUser.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <services/users/APIUserGenericData.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <services/users/APIUserPreference.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);
//header('Link: <services/company/APICompany.js?v='. APPLICATION_BUILD .'>; rel=preload; as=script', FALSE);

//Break out of any domain masking that may exist for security reasons.
Misc::checkValidDomain();

//Skip this step if disable_database_connection is enabled or the user is going through the installer still
$system_settings = array();
$primary_company = FALSE;
$clf = new CompanyListFactory();
if ( ( !isset($disable_database_connection) OR ( isset($disable_database_connection) AND $disable_database_connection != TRUE ) )
	AND ( !isset($config_vars['other']['installer_enabled']) OR ( isset($config_vars['other']['installer_enabled']) AND $config_vars['other']['installer_enabled'] != TRUE ) )
	AND ( ( !isset($config_vars['other']['down_for_maintenance']) OR isset($config_vars['other']['down_for_maintenance']) AND $config_vars['other']['down_for_maintenance'] != TRUE ) ) ) {
	//Get all system settings, so they can be used even if the user isn't logged in, such as the login page.
	try {
		$sslf = new SystemSettingListFactory();
		$system_settings = $sslf->getAllArray();
		unset($sslf);

		//Get primary company data needs to be used when user isn't logged in as well.
		$clf->getByID( PRIMARY_COMPANY_ID );
		if ( $clf->getRecordCount() == 1 ) {
			$primary_company = $clf->getCurrent();
		}
	} catch (Exception $e) {
		//Database not initialized, or some error, redirect to Install page.
		throw new DBError($e, 'DBInitialize');
	}
}

if ( DEPLOYMENT_ON_DEMAND == FALSE AND isset($config_vars['other']['installer_enabled']) AND $config_vars['other']['installer_enabled'] == TRUE AND !isset($_GET['installer']) ) {
	//Installer is enabled, check to see if any companies have been created, if not redirect to installer automatically, as they skipped it somehow.
	//Check if Company table exists first, incase the installer hasn't run at all, this avoids a SQL error.
	$installer_url = 'index.php?installer=1&disable_db=1&external_installer=1#!m=Install&a=license&external_installer=0';
	if ( isset($db) ) {
		$install_obj = new Install();
		$install_obj->setDatabaseConnection( $db );
		if ( $install_obj->checkTableExists('company') == TRUE ) {
			$clf = TTnew( 'CompanyListFactory' );
			$clf->getAll();
			if ( $clf->getRecordCount() == 0 ) {
				Redirect::Page( URLBuilder::getURL( NULL, $installer_url ) );
			}
		} else {
			Redirect::Page( URLBuilder::getURL( NULL, $installer_url ) );
		}
	} else {
		Redirect::Page( URLBuilder::getURL( NULL, $installer_url ) );
	}
	unset($install_obj, $clf, $installer_url);
}
Misc::redirectMobileBrowser(); //Redirect mobile browsers automatically.
Misc::redirectUnSupportedBrowser(); //Redirect unsupported web browsers automatically.

//Handle HTTPAuthentication after all redirects may have finished.
$authentication = new Authentication();
if ( $authentication->getHTTPAuthenticationUsername() == FALSE ) {
	$authentication->HTTPAuthenticationHeader();
} else {
	if ( $authentication->loginHTTPAuthentication() == FALSE ) {
		$authentication->HTTPAuthenticationHeader();
	}
}
unset($authentication);
?><!DOCTYPE html>
	<html>
		<head>
			<meta http-equiv="X-UA-Compatible" content="IE=edge" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<meta name="Description" content="Employee Login | TimeTrex Workforce Management Software"/>
			<meta name="google" content="notranslate">
			<title><?php echo APPLICATION_NAME .' Workforce Management';?></title>
			<link rel="shortcut icon" type="image/ico" href="<?php echo Environment::getBaseURL();?>../favicon.ico">
			<?php if ( file_exists('theme/default/css/login.composite.css') ) { //See tools/compile/Gruntfile.js to configure which files are included in the composites... ?>
				<link rel="stylesheet" type="text/css" href="theme/default/css/login.composite.css?v=<?php echo APPLICATION_BUILD?>">
				<script>
					use_composite_css_files = true;
				</script>
			<?php } else { ?>
				<link rel="stylesheet" type="text/css" href="framework/bootstrap/css/bootstrap.min.css?v=<?php echo APPLICATION_BUILD?>">
				<link rel="stylesheet" type="text/css" href="framework/bootstrap/css/bootstrap-reboot.min.css?v=<?php echo APPLICATION_BUILD?>">
				<link rel="stylesheet" type="text/css" href="theme/default/css/jquery-ui/jquery-ui.min.css?v=<?php echo APPLICATION_BUILD?>">
				<link rel="stylesheet" type="text/css" href="framework/widgets/jquery.qtip/jquery.qtip.min.css?v=<?php echo APPLICATION_BUILD?>">
				<link rel="stylesheet" type="text/css" href="theme/default/css/ui.jqgrid.css?v=<?php echo APPLICATION_BUILD?>">
				<link rel="stylesheet" type="text/css" href="theme/default/css/views/login/LoginView.css?v=<?php echo APPLICATION_BUILD?>">
				<link rel="stylesheet" type="text/css" href="theme/default/css/global/widgets/ribbon/RibbonView.css?v=<?php echo APPLICATION_BUILD?>">
				<link rel="stylesheet" type="text/css" href="theme/default/css/global/widgets/search_panel/SearchPanel.css?v=<?php echo APPLICATION_BUILD?>">
				<link rel="stylesheet" type="text/css" href="theme/default/css/views/attendance/timesheet/TimeSheetView.css?v=<?php echo APPLICATION_BUILD?>">
				<link rel="stylesheet" type="text/css" href="theme/default/css/views/attendance/schedule/ScheduleView.css?v=<?php echo APPLICATION_BUILD?>">
				<link rel="stylesheet" type="text/css" href="theme/default/css/global/widgets/timepicker/TTimePicker.css?v=<?php echo APPLICATION_BUILD?>">
				<link rel="stylesheet" type="text/css" href="theme/default/css/global/widgets/datepicker/TDatePicker.css?v=<?php echo APPLICATION_BUILD?>">
				<link rel="stylesheet" type="text/css" href="theme/default/css/right_click_menu/rightclickmenu.css?v=<?php echo APPLICATION_BUILD?>">
				<link rel="stylesheet" type="text/css" href="theme/default/css/views/wizard/Wizard.css?v=<?php echo APPLICATION_BUILD?>">
				<link rel="stylesheet" type="text/css" href="theme/default/css/image_area_select/imgareaselect-default.css?v=<?php echo APPLICATION_BUILD?>">
				<?php if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) { ?>
					<link rel="stylesheet" type="text/css" href="framework/leaflet/leaflet.css?v=<?php echo APPLICATION_BUILD?>">
					<link rel="stylesheet" type="text/css" href="framework/leaflet/leaflet-draw/leaflet.draw.css?v=<?php echo APPLICATION_BUILD?>">
					<link rel="stylesheet" type="text/css" href="framework/leaflet/leaflet-routing-machine/leaflet-routing-machine.css?v=<?php echo APPLICATION_BUILD?>">
				<?php } ?>
				<link rel="stylesheet" type="text/css" href="theme/default/css/text_layer_builder.css?v=<?php echo APPLICATION_BUILD?>">
				<link rel="stylesheet" type="text/css" href="theme/default/css/application.css?v=<?php echo APPLICATION_BUILD?>">

				<script>
					use_composite_css_files = false;
				</script>
			<?php } ?>

			<script>
				APPLICATION_BUILD = '<?php echo APPLICATION_BUILD; ?>';
				DISABLE_DB = <?php if ( isset($disable_database_connection) AND $disable_database_connection == TRUE ) { echo '1'; } else { echo '0'; }?>;

				//polyfill to fix IE<=9 console is undefined.
				//added here because we need it before ANYTHING is loaded.
				if(!(window.console && console.log)) {
					console = {
						log: function(){},
						debug: function(){},
						info: function(){},
						warn: function(){},
						error: function(){}
					};
				}
			</script>
			<script src="global/Debug.js?v=<?php echo APPLICATION_BUILD?>"></script>
		</head>
	<?php
	/*
	<!--z-index
	Alert: 6001 need larger than validation
	DatePicker:100
	Awesomebox: 100
	Progressbar: 100
	ribbon sub menu: 100
	right click menu: 100
	validation: 6000 set by plugin
	color-picker: 999

	Wizard: 50
	camera shooter in wizard 51

	EditView : 40
	Bottom minimize tab: 39

	Login view:10
	-->
	*/
	?>
	<body class="login-bg" oncontextmenu="return true;">
	<div class="need-hidden-element"><h1><a href="https://www.timetrex.com/time-and-attendance">TimeTrex Time and Attendance</a> Software</h1> - Web-based Time And Attendance suite which offers Employee Time and Attendance (timeclock, timecard, timesheet) and Payroll all in single tightly integrated package. With the ability to interface with biometric facial recognition tablets and cell phones employees are able to efficiently track their time at the office or on the road. Automatically calculate complex over time and premium time business policies and immediately be able to identify labor costs attributed to branches, and departments. Finally TimeTrex can process your payroll by calculating withholding taxes, generate detailed electronic pay stubs and even print paychecks or direct deposit funds.</div>
	<div id="topContainer" class="top-container"></div>
	<div id="contentContainer" class="content-container">
		<div class="loading-view">
			<!--[if (gt IE 8)|!(IE)]><!-->
			<div class="progress-bar-div">
				<progress class="progress-bar" max="100" value="10">
					<strong>Progress: 100% Complete.</strong>
				</progress>
				<span class="progress-label">Initializing...</span>
			</div>
			<!--<![endif]-->
		</div>
	</div>
	<div id="bottomContainer" class="bottom-container">
		<ul class="signal-strength">
			<li class="signal-strength-very-weak"><div></div></li>
			<li class="signal-strength-weak"><div></div></li>
			<li class="signal-strength-strong"><div></div></li>
			<li class="signal-strength-pretty-strong"><div></div></li>
		</ul>
		<div class="copyright-container">
			<a id="copy_right_logo_link" class="copy-right-logo-link" target="_blank"><img id="copy_right_logo" class="copy-right-logo"></a>
			<a id="copy_right_info" class="copy-right-info" target="_blank" style="display: none"></a>
			<span id="copy_right_info_1" class="copy-right-info" style="display: none"><?php /*REMOVING OR CHANGING THIS COPYRIGHT NOTICE IS IN STRICT VIOLATION OF THE LICENSE AND COPYRIGHT AGREEMENT*/ echo COPYRIGHT_NOTICE;?></span>
		</div>
		<div id="feedbackContainer" class="feedback-container">
			<span>Overall, how are you feeling about <?php echo APPLICATION_NAME; ?>?</span>
			<img class="filter yay-filter" title="Yay!" data-feedback = 1 alt="happy"  >
			<img class="filter meh-filter" title="Meh." data-feedback = 0 alt="neutral" >
			<img class="filter grr-filter" title="Grr!" data-feedback = -1 alt="sad" >
		</div>
	</div>
	<div id="overlay" class=""></div>

	<iframe style="display: none" id="hideReportIFrame" name="hideReportIFrame"></iframe>

	<script src="framework/require.js?v=<?php echo APPLICATION_BUILD?>" data-main="main.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<!-- <?php echo Misc::getInstanceIdentificationString( $primary_company, $system_settings );?>  -->
	</body>
	</html>
<?php
Debug::writeToLog();
?>