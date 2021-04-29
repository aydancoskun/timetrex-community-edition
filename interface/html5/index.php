<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2021 TimeTrex Software Inc.
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
if ( isset( $_GET['disable_db'] ) && $_GET['disable_db'] == 1 ) {
	$disable_database_connection = true;
}

require_once( '../../includes/global.inc.php' );
if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], '//' ) !== false ) { //Always strip duplicate a slashes from URL whenever possible.
	Debug::text( 'Stripping duplicate slashes from URL: ' . $_SERVER['REQUEST_URI'], __FILE__, __LINE__, __METHOD__, 10 );
	Redirect::Page( Environment::stripDuplicateSlashes( $_SERVER['REQUEST_URI'] ) );
}

sendCSRFTokenCookie();
forceNoCacheHeaders(); //Send headers to disable caching.

//Break out of any domain masking that may exist for security reasons.
Misc::checkValidDomain();

//Skip this step if disable_database_connection is enabled or the user is going through the installer still
$system_settings = [];
$primary_company = false;
$clf = new CompanyListFactory();
if ( ( !isset( $disable_database_connection ) || ( isset( $disable_database_connection ) && $disable_database_connection != true ) )
		&& ( !isset( $config_vars['other']['installer_enabled'] ) || ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] != true ) )
		&& ( ( !isset( $config_vars['other']['down_for_maintenance'] ) || isset( $config_vars['other']['down_for_maintenance'] ) && $config_vars['other']['down_for_maintenance'] != true ) ) ) {
	//Get all system settings, so they can be used even if the user isn't logged in, such as the login page.
	try {
		$sslf = new SystemSettingListFactory();
		$system_settings = $sslf->getAllArray();
		unset( $sslf );

		//Get primary company data needs to be used when user isn't logged in as well.
		$clf->getByID( PRIMARY_COMPANY_ID );
		if ( $clf->getRecordCount() == 1 ) {
			$primary_company = $clf->getCurrent();
		}
	} catch ( Exception $e ) {
		//Database not initialized, or some error, redirect to Install page.
		throw new DBError( $e, 'DBInitialize' );
	}
}

if ( DEPLOYMENT_ON_DEMAND == false && isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] == true && !isset( $_GET['installer'] ) ) {
	//Installer is enabled, check to see if any companies have been created, if not redirect to installer automatically, as they skipped it somehow.
	//Check if Company table exists first, incase the installer hasn't run at all, this avoids a SQL error.
	$installer_url = 'index.php?installer=1&disable_db=1&external_installer=1#!m=Install&a=license&external_installer=0';
	if ( isset( $db ) ) {
		$install_obj = new Install();
		$install_obj->setDatabaseConnection( $db );
		if ( $install_obj->checkTableExists( 'company' ) == true ) {
			$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
			$clf->getAll();
			if ( $clf->getRecordCount() == 0 ) {
				Redirect::Page( URLBuilder::getURL( null, $installer_url ) );
			}
		} else {
			Redirect::Page( URLBuilder::getURL( null, $installer_url ) );
		}
	} else {
		Redirect::Page( URLBuilder::getURL( null, $installer_url ) );
	}
	unset( $install_obj, $clf, $installer_url );
}
Misc::redirectMobileBrowser(); //Redirect mobile browsers automatically.
Misc::redirectUnSupportedBrowser(); //Redirect unsupported web browsers automatically.

//Handle HTTPAuthentication after all redirects may have finished.
$authentication = new Authentication();
if ( $authentication->getHTTPAuthenticationUsername() == false ) {
	$authentication->HTTPAuthenticationHeader();
} else {
	if ( $authentication->loginHTTPAuthentication() == false ) {
		$authentication->HTTPAuthenticationHeader();
	}
}
unset( $authentication );
?><!DOCTYPE html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="Description" content="Employee Login | TimeTrex Workforce Management Software"/>
	<meta name="google" content="notranslate">
	<title><?php echo 'Workforce Management Software | ' . APPLICATION_NAME; ?></title>
	<link rel="shortcut icon" type="image/ico" href="<?php echo Environment::getBaseURL(); ?>../favicon.ico">

	<?php require_once( 'dist/_css.main_ui.template.html' ); ?>

	<script src="global/Debug.js?v=<?php echo APPLICATION_BUILD ?>"></script>
	<script src="global/CookieSetting.js?v=<?php echo APPLICATION_BUILD ?>"></script>
	<script>
		APPLICATION_BUILD = '<?php echo APPLICATION_BUILD; ?>';
		DISABLE_DB = <?php if ( isset( $disable_database_connection ) && $disable_database_connection == true ) {
			echo '1';
		} else {
			echo '0';
		}?>;

	<?php
	require_once( '../../includes/API.inc.php' );
	TTi18n::chooseBestLocale();
	$api_auth = TTNew( 'APIAuthentication' ); /** @var APIAuthentication $api_auth */
	?>
	var APIGlobal = function() {};
	APIGlobal.pre_login_data = <?php echo json_encode( $api_auth->getPreLoginData() );?>; //Convert getPreLoginData() array to JS.

	window.need_load_pre_login_data = false;

	var alternate_session_data = decodeURIComponent( getCookie( 'AlternateSessionData' ) );
	if ( alternate_session_data ) {
		alternate_session_data = JSON.parse( alternate_session_data );
		if ( alternate_session_data && alternate_session_data.new_session_id ) {
			setCookie( 'SessionID', alternate_session_data.new_session_id, 30, APIGlobal.pre_login_data.cookie_base_url );

			alternate_session_data.new_session_id = null;

			//Allow NewSessionID cookie to be accessible from one level higher subdomain.
			var host = window.location.hostname;
			host = host.substring( ( host.indexOf( '.' ) + 1 ) );

			setCookie( 'AlternateSessionData', JSON.stringify( alternate_session_data ), 1, APIGlobal.pre_login_data.cookie_base_url, host ); //was NewSessionID

			need_load_pre_login_data = true; // need load it again since APIGlobal.pre_login_data.is_logged_in will be false when first load
			host = null;
		}
	}
	alternate_session_data = null;

	<?php unset($api_auth);?>
	</script>
	<style>
		/* This code is related to the office animal background image on login. */
		/* Note: This CSS is here to ensure the background image on login loads immediately vs putting it in the LoginView.css view. Even cached it was delayed while in css file. */
		.login-bg {
			background: url('theme/default/images/login_background_base.png');
			position: fixed;
			background-size: cover;
			background-position: center;
			width: 100vw; /** hack to allow not half a screen width in safari... **/
		}
	</style>
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

// Detect if mobile, and add class on body for CSS to use.
// Reason for doing this instead of using css media queries is so we dont have to guess suitable mobile device breakpoints. May change to breakpoints if this does not work out though.
if ( Misc::detectMobileBrowser() == true ) { ?>
<body class="login-bg mobile-device-mode" oncontextmenu="return true;">
<?php } else { ?>
<body class="login-bg" oncontextmenu="return true;">
<?php } ?>
<div id="login-bg_animal">
	<div id="login-bg_opacity_filter">
		<?php
		if ( Misc::isSearchEngineBrowser() == true ) { ?>
			<div class="site-description"><h1><a href="https://www.timetrex.com/time-and-attendance">TimeTrex Time and Attendance Software</a>
				</h1>
				<h3>Web-based Time And Attendance software which offers employee timeclock, timesheets and payroll all in single integrated package. With the ability to interface with biometric facial recognition tablets and smart phones employees are able to efficiently track their time at the office or in the field. Automatically calculate complex over time and premium time business policies and immediately be able to identify labor costs attributed to branches, and departments. TimeTrex can process your payroll by calculating withholding taxes, generate electronic pay stubs and direct deposit funds.</h3>
			</div>
		<?php } ?>
		<div id="app-wrapper">
<!--			<div id="tt-top"></div>-->
<!--			<div id="tt-left"></div>-->
			<div id="tt-content">
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
						<li class="signal-strength-very-weak">
							<div></div>
						</li>
						<li class="signal-strength-weak">
							<div></div>
						</li>
						<li class="signal-strength-strong">
							<div></div>
						</li>
						<li class="signal-strength-pretty-strong">
							<div></div>
						</li>
					</ul>
					<div class="copyright-container">
						<a id="copy_right_logo_link" class="copy-right-logo-link" target="_blank"><img id="copy_right_logo" class="copy-right-logo"></a>
						<a id="copy_right_info" class="copy-right-info" target="_blank" style="display: none"></a>
						<span id="copy_right_info_1" class="copy-right-info" style="display: none"><?php /*REMOVING OR CHANGING THIS COPYRIGHT NOTICE IS IN STRICT VIOLATION OF THE LICENSE AND COPYRIGHT AGREEMENT*/
							echo COPYRIGHT_NOTICE; ?></span>
					</div>
					<?php
					if ( !isset( $config_vars['other']['disable_feedback'] ) || $config_vars['other']['disable_feedback'] == false ) {
						?>
						<div id="feedbackLinkContainer" class="feedback-link-container">
							<span id="feedback-link">Send feedback to <?php echo APPLICATION_NAME; ?></span>
						</div>
						<?php
					}
					?>
				</div>
			</div>
<!--			<div id="tt-right"></div>-->
		</div>
	</div>
</div>
<div id="overlay" class=""></div>
<iframe style="display: none" id="hideReportIFrame" name="hideReportIFrame"></iframe>

<?php require_once( 'dist/_js.main_ui.template.html' ); ?>

<!-- <?php echo Misc::getInstanceIdentificationString( $primary_company, $system_settings ); ?>  -->
</body>
</html>
<?php
Debug::writeToLog();
?>