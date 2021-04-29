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
define( 'TIMETREX_JSON_API', true );
if ( isset( $_GET['disable_db'] ) && $_GET['disable_db'] == 1 ) {
	$disable_database_connection = true;
}
require_once( '../../../includes/global.inc.php' );
require_once( '../../../includes/API.inc.php' );
forceNoCacheHeaders(); //Send headers to disable caching.
header( 'Content-Type: application/javascript; charset=UTF-8' );

TTi18n::chooseBestLocale(); //Make sure we set the locale as best we can when not logged in, this is needed for getPreLoginData as well.
$auth = TTNew( 'APIAuthentication' ); /** @var APIAuthentication $auth */
?>
export var APIGlobal = function() {
};
APIGlobal.pre_login_data = <?php echo json_encode( $auth->getPreLoginData() );?>; //Convert getPreLoginData() array to JS.

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
	}
}
//delete alternate_session_data; // Invalid in strict mode. Should not be needed anyway as they are not global now?
//delete host; // Invalid in strict mode. Should not be needed anyway as they are not global now?
alternate_session_data = null;
host = null;
<?php
Debug::writeToLog();
?>
