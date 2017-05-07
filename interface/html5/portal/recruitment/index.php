<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2017 TimeTrex Software Inc.
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

require_once('../../../../includes/global.inc.php');
forceNoCacheHeaders(); //Send headers to disable caching.

//Break out of any domain masking that may exist for security reasons.
Misc::checkValidDomain();

//Skip this step if disable_database_connection is enabled or the user is going through the installer still
$system_settings = array();
$primary_company = FALSE;
$clf = new CompanyListFactory();
if ( ( !isset($disable_database_connection) OR ( isset($disable_database_connection) AND $disable_database_connection != TRUE ) )
		AND ( !isset($config_vars['other']['installer_enabled']) OR ( isset($config_vars['other']['installer_enabled']) AND $config_vars['other']['installer_enabled'] != TRUE ) )) {
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

//Some sites have problems with links with hash in them, so redirect by using "?" instead. ie: /interface/html5/portal/recruitment/?m=PortalJobVacancyDetail&id=403&company_id=ABC
if ( isset($_GET['company_id']) AND $_GET['company_id'] != '' ) {
	$url = Environment::getBaseURL().'html5/portal/recruitment/';
	if ( isset($_GET['m']) AND $_GET['m'] != '' ) {
		$url .= '#!m='. $_GET['m'];
	} else {
		$url .= '#!m=PortalJobVacancy';
	}

	if ( isset($_GET['id']) AND $_GET['id'] != '' ) {
		$url .= '&id='. (int)$_GET['id'];
	}

	$url .= '&company_id='. $_GET['company_id'];
	Redirect::Page( $url );
	exit;
}

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
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="Keywords" content="workforce management, time and attendance, payroll software, online timesheet software, open source payroll, online employee scheduling software, employee time clock software, online job costing software, workforce management, flexible scheduling solutions, easy scheduling solutions, track employee attendance, monitor employee attendance, employee time clock, employee scheduling, true real-time time sheets, accruals and time banks, payroll system, time management system"/>
	<meta name="Description" content="Workforce Management Software for tracking employee time and attendance, employee time clock software, employee scheduling software and payroll software all in a single package. Also calculate complex over time and premium time business policies and can identify labor costs attributed to branches and departments. Managers can now track and monitor their workforce easily."/>
	<title><?php echo APPLICATION_NAME .' '. TTi18n::getText('Workforce Management');?></title>
	<script async src="../../framework/stacktrace.js"></script>
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="../../framework/html5shiv.min.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="../../framework/respond.min.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<![endif]-->
	<link title="application css" rel="stylesheet" type="text/css" href="../../theme/default/css/application.css?v=<?php echo APPLICATION_BUILD?>">
	<link rel="stylesheet" type="text/css" href="../../theme/default/css/jquery-ui/jquery-ui.custom.css?v=<?php echo APPLICATION_BUILD?>">
	<link rel="stylesheet" type="text/css" href="../../theme/default/css/views/login/LoginView.css?v=<?php echo APPLICATION_BUILD?>">
	<script src="../../framework/jquery.min.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="../../framework/bootstrap/js/bootstrap.min.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="../../framework/bootstrap-select/dist/js/bootstrap-select.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="../../framework/bootstrap-toolkit.min.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="../../framework/jquery.form.min.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="../../framework/jquery.i18n.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="../../framework/backbone/underscore-min.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="../../framework/backbone/backbone-min.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="../../framework/tinymce/tinymce.min.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="../../framework/tinymce/jquery.tinymce.min.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="../../global/CookieSetting.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="../../global/APIGlobal.js.php?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="../../global/RateLimit.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="../../global/Global.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="../../global/Debug.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script async src="../../framework/rightclickmenu/rightclickmenu.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script async src="../../framework/rightclickmenu/jquery.ui.position.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script async src="../../services/APIFactory.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="../../global/LocalCacheData.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script>
		Global.url_offset = '../../';

		Global.addCss( "right_click_menu/rightclickmenu.css" );
		Global.addCss( "views/wizard/Wizard.css" );
		Global.addCss( "image_area_select/imgareaselect-default.css" );
	</script>
</head>

<!--z-index

Alert: 100
DatePicker:100
Awesomebox: 100
Progressbar: 100
ribbon sub menu: 100
right click menu: 100
validation: 6000 set by plugin


Wizard: 50
camera shooter in wizard 51


EditView : 40
Bottom minimize tab: 39

Login view:10

 -->
<body class="login-bg">
<div id="topContainer" class="top-container need-hidden-element"></div>

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
<div id="bottomContainer" class="bottom-container need-hidden-element" ondragstart="return false;"></div>

<div id="overlay" class=""></div>
<!-- Modal -->
<div class="modal fade bs-example-modal-sm" id="signinModal" style="display: none" tabindex="-1" role="dialog" aria-describedby="using as sign in dialog" aria-labelledby="signinModalLabel">
	<div class="modal-dialog modal-sm" aria-hidden="true" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
							aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="signinModalLabel"></h4>
			</div>
			<div id="signinModalBody" class="modal-body">
				...
			</div>
			<div id="signinModalFooter" class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary">Sign In</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade bs-example-modal-lg" id="formModal" style="display: none" tabindex="-1" role="dialog" aria-describedby="using as form dialog" aria-labelledby="formModalLabel">
	<div class="modal-dialog modal-lg" aria-hidden="true" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
						aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="formModalLabel"></h4>
			</div>
			<div id="formModalBody" class="modal-body">
				...
			</div>
			<div id="formModalFooter" class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary">Save changes</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade bs-example-modal-sm" id="tipModal" style="display: none" tabindex="-1" role="dialog" aria-describedby="using as tip dialog" aria-labelledby="tipModalLabel">
	<div class="modal-dialog" style="max-width: 400px; width: auto;" aria-hidden="true" role="document">
		<div class="modal-content">
			<div id="tipModalBody" class="modal-body">
				...
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="confirmModal" style="display: none" tabindex="-1" role="dialog" aria-describedby="using as confirm dialog" aria-labelledby="confirmModalLabel">
	<div class="modal-dialog" aria-hidden="true" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
						aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="confirmModalLabel"></h4>
			</div>
			<div id="confirmModalBody" class="modal-body">
				...
			</div>
			<div id="confirmModalFooter" class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary">Save changes</button>
			</div>
		</div>
	</div>
</div>

</body>
<script>
	//Hide elements that show hidden link for search friendly
	hideElements();
	//Don't not show loading bar if refresh
	if ( Global.isSet( LocalCacheData.getPortalLoginUser() ) ) {
		$( ".loading-view" ).hide();
	} else {
		setProgress()
	}

	function setProgress() {
		loading_bar_time = setInterval( function() {
			var progress_bar = $( ".progress-bar" )
			var c_value = progress_bar.attr( "value" );

			if ( c_value < 90 ) {
				progress_bar.attr( "value", c_value + 10 );
			}
		}, 1000 );
	}

	function cleanProgress() {
		if ( $( ".loading-view" ).is( ":visible" ) ) {

			var progress_bar = $( ".progress-bar" )
			progress_bar.attr( "value", 100 );
			clearInterval( loading_bar_time );

			loading_bar_time = setInterval( function() {
				$( ".progress-bar-div" ).hide();
				clearInterval( loading_bar_time );
			}, 50 );
		}
	}

	function hideElements(){
		var elements = document.getElementsByClassName( 'need-hidden-element' );

		for ( var i = 0; i < elements.length; i++ ) {
			elements[i].style.display = 'none';
		}
	}
</script>
<script src="../../views/portal/PortalBaseViewController.js?v=<?php echo APPLICATION_BUILD?>"></script>
<script src="../../views/portal/hr/my_profile/JobApplicantSubBaseViewController.js?v=<?php echo APPLICATION_BUILD?>"></script>
<script src="../../views/portal/header/HeaderViewController.js?v=<?php echo APPLICATION_BUILD?>"></script>
<script src="../../views/portal/sign_in/SignInController.js?v=<?php echo APPLICATION_BUILD?>"></script>
<script src="../../views/portal/sign_in/PortalForgotPasswordController.js?v=<?php echo APPLICATION_BUILD?>"></script>
<script src="../../views/portal/sign_in/PortalResetForgotPasswordController.js?v=<?php echo APPLICATION_BUILD?>"></script>
<script src="../../views/portal/header/HeaderUploadResumeWidget.js?v=<?php echo APPLICATION_BUILD?>"></script>
<script src="../../views/portal/hr/recruitment/PortalJobVacancyRowController.js?v=<?php echo APPLICATION_BUILD?>"></script>
<script src="../../views/portal/hr/recruitment/PortalJobVacancyDetailController.js?v=<?php echo APPLICATION_BUILD?>"></script>
<script src="../../views/portal/hr/my_profile/JobApplicantEmploymentSubViewController.js?v=<?php echo APPLICATION_BUILD?>"></script>
<script src="../../views/portal/hr/my_profile/JobApplicantReferenceSubViewController.js?v=<?php echo APPLICATION_BUILD?>"></script>
<script src="../../views/portal/hr/my_profile/JobApplicantLocationSubViewController.js?v=<?php echo APPLICATION_BUILD?>"></script>
<script src="../../views/portal/hr/my_profile/JobApplicantSkillSubViewController.js?v=<?php echo APPLICATION_BUILD?>"></script>
<script src="../../views/portal/hr/my_profile/JobApplicantEducationSubViewController.js?v=<?php echo APPLICATION_BUILD?>"></script>
<script src="../../views/portal/hr/my_profile/JobApplicantMembershipSubViewController.js?v=<?php echo APPLICATION_BUILD?>"></script>
<script src="../../views/portal/hr/my_profile/JobApplicantLicenseSubViewController.js?v=<?php echo APPLICATION_BUILD?>"></script>
<script src="../../views/portal/hr/my_profile/JobApplicantLanguageSubViewController.js?v=<?php echo APPLICATION_BUILD?>"></script>
<script src="../../views/portal/hr/my_profile/JobApplicationSubViewController.js?v=<?php echo APPLICATION_BUILD?>"></script>
<script src="../../views/portal/hr/my_profile/DocumentSubViewController.js?v=<?php echo APPLICATION_BUILD?>"></script>
<script src="../../framework/require.js" data-main="main.js?v=<?php echo APPLICATION_BUILD?>"></script>

<!-- <?php echo Misc::getInstanceIdentificationString( $primary_company, $system_settings );?>  -->
</html>
<?php
Debug::writeToLog();
?>
