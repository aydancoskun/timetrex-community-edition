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


/*

	This files only purpose is to confirm we are running PHP5, and that the
	templates_c directory is writable so we can forward the user to License.php

*/
echo "<html><body>";
echo "Checking pre-flight requirements... ";
echo " 1...";

ini_set( 'display_errors', 1 ); //Try to display any errors that may arise on this page.
ini_set( 'default_socket_timeout', 5 );
ini_set( 'allow_url_fopen', 1 );

echo " 2...";
if ( isset( $_GET['external_installer'] ) ) {
	$external_installer = (int)$_GET['external_installer'];
} else {
	$external_installer = 0;
}

echo " 3...";
//$templates_c_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'templates_c';

echo " 4...";
$redir = true;
if ( version_compare( PHP_VERSION, '7.0.0', '<' ) == 1 ) { //Also update CLI.inc.php for the minimum PHP version.
	echo "You are currently using PHP v<b>" . PHP_VERSION . "</b> TimeTrex requires PHP <b>v7.0</b> or greater!<br><br>\n";
	$redir = false;
}
if ( version_compare( PHP_VERSION, '7.4.99', '>' ) == 1 ) {
	echo "You are currently using PHP v<b>" . PHP_VERSION . "</b> TimeTrex requires PHP <b>v7.4.x</b> or earlier!<br><br>\n";
	$redir = false;
}

echo " 5...";
//if ( !is_writeable($templates_c_dir) ) {
//	echo "<b>". $templates_c_dir ."</b> is NOT writable by your web server! For help on this topic click <a href='https://forums.timetrex.com/viewtopic.php?t=66'>here</a>.<br><br>\n";
//	$redir = FALSE;
//}

echo " 6...";
//These are all extensions required to even initialize the HTML5 interface.
//if ( extension_loaded( 'intl' ) == FALSE ) {
//	echo "PHP INTL extension is not installed, TimeTrex requires INTL to be installed.<br><br>\n";
//	$redir = FALSE;
//}
if ( extension_loaded( 'gettext' ) == false ) {
	echo "PHP GetText extension is not installed, TimeTrex requires GetText to be installed.<br><br>\n";
	$redir = false;
}
if ( extension_loaded( 'mbstring' ) == false ) {
	echo "PHP MBSTRING extension is not installed, TimeTrex requires MBSTRING to be installed.<br><br>\n";
	$redir = false;
}
if ( extension_loaded( 'json' ) == false ) {
	echo "PHP JSON extension is not installed, TimeTrex requires JSON to be installed.<br><br>\n";
	$redir = false;
}

echo " 7...";
//$test_template_c_sub_dir = $templates_c_dir . DIRECTORY_SEPARATOR . uniqid();
//if ( @mkdir( $test_template_c_sub_dir ) !== TRUE ) {
//	//If SELinux is installed, could try: chcon -t httpd_sys_content_t storage
//	echo "Your web server is unable to create directories inside of: <b>". $templates_c_dir ."</b>, please give your webserver write permissions to this directory. For help on this topic click <a href='https://forums.timetrex.com/viewtopic.php?t=66'>here</a>.<br><br>\n";
//	$redir = FALSE;
//}
//echo " 8...";
//@rmdir( $test_template_c_sub_dir );
//unset($test_template_c_sub_dir);

echo " 9...";
$handle = @fopen( 'http://www.timetrex.com/pre_install.php?os=' . PHP_OS . '&php_version=' . PHP_VERSION . '&redir=' . (int)$redir . '&web_server=' . urlencode( substr( $_SERVER['SERVER_SOFTWARE'], 0, 20 ) ) . '&external_installer=' . $external_installer . '&url=' . urlencode( $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'] ), 'r' );
@fclose( $handle );

echo " 10...";
if ( $redir == true ) {
	echo " PASSED!<br><br>\n";
	echo "Please wait while we automatically redirect you to the <a href='License.php?external_installer=" . $external_installer . "'>installer</a>.";
	//echo "<meta http-equiv='refresh' content='0;url=License.php?external_installer=". $external_installer ."'>";
	echo "<meta http-equiv='refresh' content='0;url=../html5/index.php?installer=1&disable_db=1&external_installer=" . $external_installer . "#!m=Install&a=license&external_installer=" . $external_installer . "'>";
} else {
	echo " FAILED!<br><br>\n";
	echo "For installation support, please join our community <a href=\"https://forums.timetrex.com\" target=\"_blank\">forums</a> or
		contact a TimeTrex support expert for <a href=\"https://www.timetrex.com/setup_support.php\" target=\"_blank\">Implementation Support Services</a>.
		<br>\n";
}
echo "</body></html>";
?>
