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

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php' );

if ( $argc < 2 || in_array( $argv[1], [ '--help', '-help', '-h', '-?' ] ) ) {
	$help_output = "Usage: register_api_key.php [OPTIONS]\n";
	$help_output .= "\n";
	$help_output .= "  Options:\n";
	$help_output .= "    -server <URL>				URL to API server\n";
	$help_output .= "    -username <username>		API username\n";
	$help_output .= "    -password <password>		API password\n";
	$help_output .= "    -protocol <protocol>		(Optional) API Protocol (JSON/SOAP/REPORT)\n";

	echo $help_output;
} else {
	//Handle command line arguments
	$last_arg = count( $argv ) - 1;

	if ( in_array( '-server', $argv ) ) {
		$api_url = trim( $argv[( array_search( '-server', $argv ) + 1 )] );
	} else {
		$api_url = false;
	}

	if ( in_array( '-username', $argv ) ) {
		$username = trim( $argv[( array_search( '-username', $argv ) + 1 )] );
	} else {
		$username = false;
	}

	if ( in_array( '-password', $argv ) ) {
		$password = trim( $argv[( array_search( '-password', $argv ) + 1 )] );
	} else {
		$password = false;
	}

	//Protocol is based on the Server URL. However we need a way to force it to something else in the case of the REPORT API, as it doesn't allow authentication in the same way.
	if ( in_array( '-protocol', $argv ) ) {
		$protocol = strtoupper( trim( $argv[( array_search( '-protocol', $argv ) + 1 )] ) );
	} else {
		$protocol = 'JSON';
	}

	switch ( strtolower( $protocol ) ) {
		case 'json':
			$end_point = 'json/api';
			break;
		case 'soap':
			$end_point = 'soap/api';
			break;
		case 'report':
			$end_point = 'report/api';
			break;
		default:
			$end_point = 'json/api';
			break;
	}

	$TIMETREX_URL = $api_url;

	$api_session = new TimeTrexClientAPI();
	$api_key = $api_session->registerAPIKey( $username, $password, $end_point );
	if ( is_string( $api_key ) && strlen( $api_key ) > 40 ) {
		echo "Successfully Registered Permanent API Key: SessionID=" . $api_key . " to Username: " . $username . "\n";
		$api_session->Logout();
	} else {
		echo "ERROR: Unable to register API key, please check the Server URL, Username and Password!\n";
		exit( 255 );
	}
}
echo "\n";

//Debug::Display();
Debug::writeToLog();
?>
