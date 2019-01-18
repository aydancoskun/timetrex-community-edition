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

require_once('../../../../includes/global.inc.php');
forceNoCacheHeaders(); //Send headers to disable caching.

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
	<meta name="Description" content="Search job postings by local and international companies."/>
	<title><?php echo TTi18n::getText('Job Search') . ' | '. APPLICATION_NAME;?></title>
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="../../framework/html5shiv.min.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="../../framework/respond.min.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<![endif]-->
    <link rel="stylesheet" type="text/css" href="../../theme/default/css/jquery-ui/jquery-ui.min.css?v=<?php echo APPLICATION_BUILD?>">
    <link rel="stylesheet" type="text/css" href="../../theme/default/css/global/widgets/datepicker/TDatePicker.css?v=<?php echo APPLICATION_BUILD?>">
	<link title="application css" rel="stylesheet" type="text/css" href="../../theme/default/css/application.css?v=<?php echo APPLICATION_BUILD?>">
<!--	<link rel="stylesheet" type="text/css" href="../../theme/default/css/jquery-ui/jquery-ui.custom.css?v=--><?php //echo APPLICATION_BUILD?><!--">-->
	<link rel="stylesheet" type="text/css" href="../../theme/default/css/views/login/LoginView.css?v=<?php echo APPLICATION_BUILD?>">

    <script>
		APPLICATION_BUILD = '<?php echo APPLICATION_BUILD; ?>';
		DISABLE_DB = <?php if ( isset($disable_database_connection) AND $disable_database_connection == TRUE ) { echo '1'; } else { echo '0'; }?>;
    </script>
    <script src="../../global/Debug.js?v=<?php echo APPLICATION_BUILD?>"></script>
    <link title="application css" rel="stylesheet" type="text/css" href="../../framework/bootstrap/css/glyphicons.css?v=<?php echo APPLICATION_BUILD?>">
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
<div id="bottomContainer" class="bottom-container need-hidden-element">
    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <p class="footer-copyright"><?php /*REMOVING OR CHANGING THIS COPYRIGHT NOTICE IS IN STRICT VIOLATION OF THE LICENSE AND COPYRIGHT AGREEMENT*/ echo COPYRIGHT_NOTICE;?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="overlay" class=""></div>
<!-- Modal -->
<div class="modal fade bs-example-modal-sm" id="signinModal" style="display: none" tabindex="-1" role="dialog" aria-describedby="using as Login dialog" aria-labelledby="signinModalLabel">
	<div class="modal-dialog modal-sm" aria-hidden="true" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="signinModalLabel"></h4>
				<div class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></div>
			</div>
			<div id="signinModalBody" class="modal-body">
				...
			</div>
			<div id="signinModalFooter" class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary">Login</button>
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
<script src="../../framework/require.js" data-main="main.js?v=<?php echo APPLICATION_BUILD?>"></script>
<!-- <?php echo Misc::getInstanceIdentificationString( $primary_company, $system_settings );?>  -->
</html>
<?php
Debug::writeToLog();
?>
