<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2020 TimeTrex Software Inc.
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
//Add timetrex.ini.php setting to enable/disable the API. Make an entire [API] section.
require_once( '../../../includes/global.inc.php' );
require_once( '../../../includes/API.inc.php' );
Header( 'Content-Type: application/json; charset=UTF-8' ); //Make sure content type is not text/HTML to help avoid XSS.

function unauthenticatedInvokeService( $class_name, $method, $arguments, $message_id, $api_auth ) {
	global $config_vars;
	TTi18n::chooseBestLocale(); //Make sure we set the locale as best we can when not logged in

	Debug::text( 'Handling UNAUTHENTICATED JSON Call To API Factory: ' . $class_name . ' Method: ' . $method . ' Message ID: ' . $message_id, __FILE__, __LINE__, __METHOD__, 10 );

	if ( !isset( $config_vars['other']['down_for_maintenance'] ) || isset( $config_vars['other']['down_for_maintenance'] ) && $config_vars['other']['down_for_maintenance'] == '' ) {
		$valid_unauthenticated_classes = getUnauthenticatedAPIClasses();
		if ( $class_name != '' && in_array( $class_name, $valid_unauthenticated_classes ) && class_exists( $class_name ) ) {
			$obj = new $class_name;
			if ( method_exists( $obj, 'setAPIMessageID' ) ) {
				$obj->setAPIMessageID( $message_id ); //Sets API message ID so progress bar continues to work.
			}
			if ( $method != '' && method_exists( $obj, $method ) ) {
				$retval = call_user_func_array( [ $obj, $method ], (array)$arguments );
				//If the function returns anything else, encode into JSON and return it.
				//Debug::Arr($retval, 'Retval: ', __FILE__, __LINE__, __METHOD__, 10);
				echo json_encode( $retval );
				$json_error = getJSONError();
				if ( $json_error !== false ) {
					Debug::Arr( $retval, 'ERROR: JSON: ' . $json_error, __FILE__, __LINE__, __METHOD__, 10 );
					echo json_encode( $api_auth->returnHandler( false, 'EXCEPTION', 'ERROR: JSON: ' . $json_error ) );
				}
			} else {
				$validator = TTnew( 'Validator' ); /** @var Validator $validator */
				Debug::text( 'Method: ' . $method . ' does not exist!', __FILE__, __LINE__, __METHOD__, 10 );
				echo json_encode( $api_auth->returnHandler( false, 'SESSION', TTi18n::getText( 'Method %1 does not exist.', [ $validator->escapeHTML( $method ) ] ) ) );
			}
		} else {
			$validator = TTnew( 'Validator' ); /** @var Validator $validator */
			Debug::text( 'Class: ' . $class_name . ' does not exist! (unauth)', __FILE__, __LINE__, __METHOD__, 10 );
			echo json_encode( $api_auth->returnHandler( false, 'SESSION', TTi18n::getText( 'Class %1 does not exist, or unauthenticated.', [ $validator->escapeHTML( $class_name ) ] ) ) );
		}
	} else {
		Debug::text( 'WARNING: Installer/Down For Maintenance is enabled... Service is disabled!', __FILE__, __LINE__, __METHOD__, 10 );
		echo json_encode( $api_auth->returnHandler( false, 'DOWN_FOR_MAINTENANCE', TTi18n::gettext( '%1 is currently undergoing maintenance. We apologize for any inconvenience this may cause, please try again later.', [ APPLICATION_NAME ] ) ) );
	}

	return true;
}

function authenticatedInvokeService( $class_name, $method, $arguments, $message_id, $authentication, $api_auth ) {
	global $current_user, $current_user_prefs, $current_company, $obj;

	$current_user = $authentication->getObject();

	if ( is_object( $current_user ) ) {
		$current_user->getUserPreferenceObject()->setDateTimePreferences();
		$current_user_prefs = $current_user->getUserPreferenceObject();

		Debug::text( 'Locale Cookie: ' . TTi18n::getLocaleCookie(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( TTi18n::getLocaleCookie() != '' && $current_user_prefs->getLanguage() !== TTi18n::getLanguageFromLocale( TTi18n::getLocaleCookie() ) ) {
			Debug::text( 'Changing User Preference Language to match cookie...', __FILE__, __LINE__, __METHOD__, 10 );
			$current_user_prefs->setLanguage( TTi18n::getLanguageFromLocale( TTi18n::getLocaleCookie() ) );
			if ( $current_user_prefs->isValid() ) {
				$current_user_prefs->Save( false );
			}
		} else {
			Debug::text( 'User Preference Language matches cookie!', __FILE__, __LINE__, __METHOD__, 10 );
		}
		if ( isset( $_GET['language'] ) && $_GET['language'] != '' ) {
			TTi18n::setLocale( $_GET['language'] ); //Sets master locale
		} else {
			TTi18n::setLanguage( $current_user_prefs->getLanguage() );
			TTi18n::setCountry( $current_user->getCountry() );
			TTi18n::setLocale(); //Sets master locale
		}
		TTi18n::setLocaleCookie(); //Make sure locale cookie is set so APIGlobal.js.php can read it.

		$clf = new CompanyListFactory();
		$current_company = $clf->getByID( $current_user->getCompany() )->getCurrent();

		if ( is_object( $current_company ) ) {
			Debug::text( 'Current User: ' . $current_user->getUserName() . ' (User ID: ' . $current_user->getID() . ') Company: ' . $current_company->getName() . ' (Company ID: ' . $current_company->getId() . ')', __FILE__, __LINE__, __METHOD__, 10 );

			//Debug::text('Handling JSON Call To API Factory: '.  $class_name .' Method: '. $method .' Message ID: '. $message_id .' UserName: '. $current_user->getUserName(), __FILE__, __LINE__, __METHOD__, 10);
			if ( $class_name != '' && class_exists( $class_name ) ) {
				$obj = new $class_name;
				if ( method_exists( $obj, 'setAPIMessageID' ) ) {
					$obj->setAPIMessageID( $message_id ); //Sets API message ID so progress bar continues to work.
				}

				if ( $method != '' && method_exists( $obj, $method ) ) {
					$retval = call_user_func_array( [ $obj, $method ], (array)$arguments );
					if ( $retval !== null ) {
						if ( !is_object( $retval ) ) { //Make sure we never return a raw object to end-user, as too much information could be included in it.
							echo json_encode( $retval );
							$json_error = getJSONError();
							if ( $json_error !== false ) {
								Debug::Arr( $retval, 'ERROR: JSON: ' . $json_error, __FILE__, __LINE__, __METHOD__, 10 );
								echo json_encode( $api_auth->returnHandler( false, 'EXCEPTION', 'ERROR: JSON: ' . $json_error ) );
							}
						} else {
							Debug::text( 'OBJECT return value, not JSON encoding any additional data.', __FILE__, __LINE__, __METHOD__, 10 );
						}
					} else {
						Debug::text( 'NULL return value, not JSON encoding any additional data.', __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::text( 'Method: ' . $method . ' does not exist!', __FILE__, __LINE__, __METHOD__, 10 );
					echo json_encode( $api_auth->returnHandler( false, 'EXCEPTION', TTi18n::getText( 'Method %1 does not exist.', [ $current_company->Validator->escapeHTML( $method ) ] ) ) );
				}
			} else {
				Debug::text( 'Class: ' . $class_name . ' does not exist!', __FILE__, __LINE__, __METHOD__, 10 );
				echo json_encode( $api_auth->returnHandler( false, 'EXCEPTION', TTi18n::getText( 'Class %1 does not exist.', [ $current_company->Validator->escapeHTML( $class_name ) ] ) ) );
			}
		} else {
			Debug::text( 'Failed to get Company Object!', __FILE__, __LINE__, __METHOD__, 10 );
			echo json_encode( $api_auth->returnHandler( false, 'SESSION', TTi18n::getText( 'Company does not exist.' ) ) );
		}
	} else {
		Debug::text( 'Failed to get User Object!', __FILE__, __LINE__, __METHOD__, 10 );
		echo json_encode( $api_auth->returnHandler( false, 'SESSION', TTi18n::getText( 'User does not exist.' ) ) );
	}

	return true;
}

/*
 Arguments:
	GET: SessionID
	GET: Class
	GET: Method
	POST: Arguments for method.
*/
$class_prefix = 'API';
$class_name = false;
$method = false;

if ( isset( $_GET['Class'] ) && $_GET['Class'] != '' ) {
	$class_name = $_GET['Class'];

	//If API wasn't already put on the class, add it manually.
	if ( strtolower( substr( $class_name, 0, 3 ) ) != 'api' ) {
		$class_name = $class_prefix . $class_name;
	}

	$class_name = TTgetPluginClassName( $class_name );
} else {
	$class_name = TTgetPluginClassName( $class_prefix . 'Authentication' );
}

if ( isset( $_GET['Method'] ) && $_GET['Method'] != '' ) {
	$method = $_GET['Method'];
}

if ( isset( $_GET['MessageID'] ) && $_GET['MessageID'] != '' ) {
	$message_id = $_GET['MessageID'];
} else {
	$message_id = md5( uniqid() ); //Random message_id
}

Debug::text( 'Handling JSON Call To API Factory: ' . $class_name . ' Method: ' . $method . ' Message ID: ' . $message_id, __FILE__, __LINE__, __METHOD__, 10 );

//URL: api.php?SessionID=fc914bf32711bff031a6c80295bbff86&Class=APIPayStub&Method=getPayStub
/*
 RAW POST: data[filter_data][id][0]=101561&paging=TRUE&format=pdf
 JSON (URL encoded): %7B%22data%22%3A%7B%22filter_data%22%3A%7B%22id%22%3A%5B101561%5D%7D%7D%2C%22paging%22%3Atrue%2C%22format%22%3A%22pdf%22%7D

 FULL URL: SessionID=fc914bf32711bff031a6c80295bbff86&Class=APIPayStub&Method=test&json={"data":{"filter_data":{"id":[101561]}},"paging":true,"format":"pdf"}
*/
/*
$_POST = array( 'data' => array('filter_data' => array('id' => array(101561) ) ),
				'paging' => TRUE,
				'format' => 'pdf',
				);
*/
//Debug::Arr(file_get_contents('php://input'), 'POST: ', __FILE__, __LINE__, __METHOD__, 10);
//Debug::Arr($_POST, 'POST: ', __FILE__, __LINE__, __METHOD__, 10);

$arguments = $_POST;
if ( isset( $_POST['json'] ) || isset( $_GET['json'] ) ) {
	if ( isset( $_GET['json'] ) && $_GET['json'] != '' ) {
		$arguments = json_decode( $_GET['json'], true );
	} else if ( isset( $_POST['json'] ) && $_POST['json'] != '' ) {
		$arguments = json_decode( $_POST['json'], true );
	}
}

//Make sure we sanitize all user inputs from XSS vulnerabilities. Where HTML should be allowed we can reverse this process on a case-by-case basis.
//This causes data to be modified when stored in the database though, we have since enabled escaping on output in jqGrid instead.
//FormVariables::RecurseFilterArray( $arguments );

//$argument_size = strlen( serialize($arguments) );
$argument_size = strlen( $HTTP_RAW_POST_DATA ); //Just strlen this variable rather than serialize all the data as it should be much faster.
if ( PRODUCTION == true && $argument_size > ( 1024 * 12 ) ) {
	Debug::Text( 'Arguments too large to display... Size: ' . $argument_size, __FILE__, __LINE__, __METHOD__, 10 );
} else {
	if ( strtolower( $method ) == 'login' && isset( $arguments[0] ) ) { //Make sure passwords arent displayed if logging is enabled.
		Debug::Arr( $arguments[0], '*Censored* Arguments: (Size: ' . $argument_size . ')', __FILE__, __LINE__, __METHOD__, 10 );
	} else {
		Debug::Arr( $arguments, 'Arguments: (Size: ' . $argument_size . ')', __FILE__, __LINE__, __METHOD__, 10 );
	}
}
unset( $argument_size );

$api_auth = TTNew( 'APIAuthentication' );
/** @var APIAuthentication $api_auth */ //Used to handle error cases and display error messages.
$session_id = getSessionID( 600 );
$authentication = new Authentication();
$authentication->setIdleTimeout( 900 ); //Force 15 minute timeout.
if ( $authentication->checkValidCSRFToken() == true ) { //Help prevent CSRF attacks with this, run this check during and before the user is logged in.
	if ( ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] == false ) && ( !isset( $config_vars['other']['down_for_maintenance'] ) || isset( $config_vars['other']['down_for_maintenance'] ) && $config_vars['other']['down_for_maintenance'] == '' ) && $session_id != '' && !isset( $_GET['disable_db'] ) && isUnauthenticatedMethod( $method ) == false ) { //When interface calls PING() on a regular basis we need to skip this check and pass it to APIAuthentication immediately to avoid updating the session time.
		Debug::text( 'Session ID: ' . $session_id . ' Source IP: ' . Misc::getRemoteIPAddress(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $class_name != 'APIProgressBar' && $authentication->Check( $session_id, 'QUICK_PUNCH_ID' ) === true ) { //Always treat APIProgressBar as unauthenticated as an optimization to avoid causing uncessary SQL queries.
			authenticatedInvokeService( $class_name, $method, $arguments, $message_id, $authentication, $api_auth );
		} else {
			Debug::text( 'SessionID set but user not authenticated!', __FILE__, __LINE__, __METHOD__, 10 );
			//echo json_encode( $api_auth->returnHandler( FALSE, 'SESSION', TTi18n::getText('User is not authenticated.' ) ) );

			//Rather than fail with session error, switch over to using unauthenticated calls, which if its calling to authenticated method will cause a SESSION error at that time.
			unauthenticatedInvokeService( $class_name, $method, $arguments, $message_id, $api_auth );
		}
	} else {
		Debug::text( 'No SessionID or calling non-authenticated function...', __FILE__, __LINE__, __METHOD__, 10 );
		unauthenticatedInvokeService( $class_name, $method, $arguments, $message_id, $api_auth );
	}
} else {
	echo json_encode( $api_auth->returnHandler( false, 'EXCEPTION', TTi18n::getText( 'Invalid CSRF token, please refresh your browser and try again!' ) ) ); //Could potentially use a SESSION error so the front-end logs the user out so they can login again with a fresh CSRF token.
}

Debug::text( 'Server Response Time: ' . ( (float)microtime( true ) - $_SERVER['REQUEST_TIME_FLOAT'] ), __FILE__, __LINE__, __METHOD__, 10 );
Debug::writeToLog();
?>
