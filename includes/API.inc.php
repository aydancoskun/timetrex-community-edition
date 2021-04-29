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

define( 'TIMETREX_API', true );
forceNoCacheHeaders(); //Send headers to disable caching.

/**
 * Returns if the method should always be a unauthenticated API call.
 * @return bool
 */
function isUnauthenticatedMethod( $method ) {
	if ( in_array( strtolower( $method ), [ 'isloggedin', 'ping' ] ) ) {
		return true;
	}

	return false;
}

/**
 * Returns valid classes when unauthenticated.
 * @return array
 */
function getUnauthenticatedAPIClasses() {
	return [ 'APIAuthentication', 'APIRecruitmentAuthentication', 'APIJobApplicantPortal', 'APIJobVacancyPortal', 'APIDocumentPortal', 'APIClientStationUnAuthenticated', 'APIAuthenticationPlugin', 'APIClientStationUnAuthenticatedPlugin', 'APIDocumentPortal', 'APICompanyPortal', 'APIProgressBar', 'APIInstall' ];
}

/**
 * @return array
 */
function getAuthenticatedPortalAPIMethods() {
	return [
			'getJobApplicant', 'getJobApplicantEducation', 'setJobApplicantEducation', 'getJobApplicantEmployment', 'setJobApplicantEmployment', 'getJobApplicantLanguage', 'setJobApplicantLanguage', 'getJobApplicantLicense', 'setJobApplicantLicense', 'getJobApplicantLocation', 'setJobApplicantLocation', 'getJobApplicantMembership', 'setJobApplicantMembership',
			'getJobApplicantReference', 'setJobApplicantReference', 'getJobApplicantSkill', 'setJobApplicantSkill', 'getJobApplication', 'setJobApplication', 'getAttachment', 'addAttachment', 'uploadAttachment',
	];
}


/**
 * Get Cookie/Post/Get variable in that order.
 * @param $var_name
 * @param null $default_value
 * @return mixed|null
 */
function getCookiePostGetVariable( $var_name, $default_value = null ) {
	if ( isset( $_COOKIE[$var_name] ) && $_COOKIE[$var_name] != '' ) {
		$retval = $_COOKIE[$var_name];
	} else if ( isset( $_POST[$var_name] ) && $_POST[$var_name] != '' ) {
		$retval = $_POST[$var_name];
	} else if ( isset( $_GET[$var_name] ) && $_GET[$var_name] != '' ) {
		$retval = $_GET[$var_name];
	} else {
		$retval = $default_value;
	}

	return $retval;
}

/**
 * Returns session ID from _COOKIE, _POST, then _GET.
 * @param int $authentication_type_id
 * @return bool|string
 */
function getSessionID( $authentication_type_id = 800 ) {
	//FIXME: Work-around for bug in Mobile app v3.0.86 that uses old SessionIDs in the Cookie, but correct ones on the URL.
	if ( isset( $_COOKIE['SessionID'] ) && isset( $_GET['SessionID'] ) && $_COOKIE['SessionID'] != $_GET['SessionID'] ) {
		//Debug::Arr( array($_COOKIE, $_POST, $_GET), 'Input Data:', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Text( 'WARNING: Two different SessionIDs sent, COOKIE: ' . $_COOKIE['SessionID'] . ' GET: ' . $_GET['SessionID'], __FILE__, __LINE__, __METHOD__, 10 );
		if ( isset( $_SERVER['REQUEST_URI'] ) && stripos( $_SERVER['REQUEST_URI'], 'APIClientStationUnAuthenticated' ) !== false ) {
			Debug::Text( 'Using GET Session ID...', __FILE__, __LINE__, __METHOD__, 10 );
			unset( $_COOKIE['SessionID'] );
		}
	}

	$authentication = new Authentication();
	$session_name = $authentication->getName( $authentication_type_id );

	$session_id = getCookiePostGetVariable( $session_name, false );
	if ( is_string( $session_id ) == false ) {
		$session_id = false;
	}

	return $session_id;
}

/**
 * Returns Station ID from _COOKIE, _POST, then _GET.
 * @return bool|mixed
 */
function getStationID() {
	$station_id = getCookiePostGetVariable( 'StationID', false );

	//Check to see if there is a "sticky" user agent based Station ID defined.
	if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && $_SERVER['HTTP_USER_AGENT'] != '' && stripos( $_SERVER['HTTP_USER_AGENT'], 'StationID:' ) !== false ) {
		if ( preg_match( '/StationID:\s?([a-zA-Z0-9]{30,64})/i', $_SERVER['HTTP_USER_AGENT'], $matches ) > 0 ) {
			if ( isset( $matches[1] ) ) {
				Debug::Text( '  Found StationID in user agent, forcing to that instead!', __FILE__, __LINE__, __METHOD__, 10 );
				$station_id = $matches[1];
			}
		}
	}

	if ( is_string( $station_id ) == false ) {
		$station_id = false;
	}

	return $station_id;
}

/**
 * Handle temporarily overriding user preferences based on Cookie/Post/Get variables.
 * This is useful for ensuring there is always consistent date/time formats and timezones when accessing the API for multiple users.
 * @param $user_obj UserFactory
 * @return bool
 */
function handleOverridePreferences( $user_obj ) {
	$user_pref_obj = $user_obj->getUserPreferenceObject(); /** @var UserPreferenceFactory $user_pref_obj */

	$override_preferences = json_decode( getCookiePostGetVariable( 'OverrideUserPreference' ), true );
	if ( is_array( $override_preferences ) && count( $override_preferences ) > 0 ) {
		//If a user_id is specified, pull the timezone for that user and default to it, rather than the UI having to do a lookup itself and passing it through.
		if ( isset( $override_preferences['user_id'] ) && TTUUID::isUUID( $override_preferences['user_id'] ) && $user_obj->getId() != $override_preferences['user_id'] ) {
			$uplf = TTnew( 'UserPreferenceListFactory' ); /** @var UserPreferenceListFactory $uplf */
			$uplf->getByUserID( $override_preferences['user_id'] ); //Cached
			if ( $uplf->getRecordCount() > 0 ) {
				$override_preferences = array_merge( $uplf->getCurrent()->getObjectAsArray( [ 'time_zone' => true ] ), $override_preferences );
			}

			//If switching to another users timezone, default to appending the timezone on the end of each timestamp unless otherwise specified.
			if ( !isset( $override_preferences['time_format'] ) && strpos( $user_pref_obj->getTimeFormat(), 'T' ) === false ) {
				$override_preferences['time_format'] = $user_pref_obj->getTimeFormat() . ' T';
			}
		}

		Debug::Arr( $override_preferences, 'Overridden Preferences: ', __FILE__, __LINE__, __METHOD__, 10 );
		$user_pref_obj->setObjectFromArray( $override_preferences );
	}

	$user_pref_obj->setDateTimePreferences();

	Debug::text( 'Locale Cookie: ' . TTi18n::getLocaleCookie(), __FILE__, __LINE__, __METHOD__, 10 );

	//If override preferences specifies a language, do not save the users preferences, just use it dynamically instead.
	if ( !isset( $override_preferences['language'] ) && TTi18n::getLocaleCookie() != '' && $user_pref_obj->getLanguage() !== TTi18n::getLanguageFromLocale( TTi18n::getLocaleCookie() ) ) {
		Debug::text( 'Changing User Preference Language to match cookie...', __FILE__, __LINE__, __METHOD__, 10 );
		$user_pref_obj->setLanguage( TTi18n::getLanguageFromLocale( TTi18n::getLocaleCookie() ) );
		if ( $user_pref_obj->isValid() ) {
			$user_pref_obj->Save( false );
		}
	} else {
		Debug::text( 'User Preference Language matches cookie!', __FILE__, __LINE__, __METHOD__, 10 );
	}

	if ( isset( $_GET['language'] ) && $_GET['language'] != '' ) {
		TTi18n::setLocale( $_GET['language'] ); //Sets master locale
	} else {
		TTi18n::setLanguage( $user_pref_obj->getLanguage() );
		TTi18n::setCountry( $user_obj->getCountry() );
		TTi18n::setLocale(); //Sets master locale
	}

	TTi18n::setLocaleCookie(); //Make sure locale cookie is set so APIGlobal.js.php can read it.

	return $user_pref_obj;
}

/**
 * @return bool|string
 */
function getJSONError() {
	$retval = false;

	if ( function_exists( 'json_last_error' ) ) { //Handle PHP v5.3 and older.
		switch ( json_last_error() ) {
			case JSON_ERROR_NONE:
				break;
			case JSON_ERROR_DEPTH:
				$retval = 'Maximum stack depth exceeded';
				break;
			case JSON_ERROR_STATE_MISMATCH:
				$retval = 'Underflow or the modes mismatch';
				break;
			case JSON_ERROR_CTRL_CHAR:
				$retval = 'Unexpected control character found';
				break;
			case JSON_ERROR_SYNTAX:
				$retval = 'Syntax error, malformed JSON';
				break;
			case JSON_ERROR_UTF8:
				$retval = 'Malformed UTF-8 characters, possibly incorrectly encoded';
				break;
			default:
				$retval = 'Unknown error';
				break;
		}
	}

	return $retval;
}

//Make sure cron job information is always logged.
//Don't do this until log rotation is implemented.
/*
Debug::setEnable( TRUE );
Debug::setBufferOutput( TRUE );
Debug::setEnableLog( TRUE );
if ( Debug::getVerbosity() <= 1 ) {
	Debug::setVerbosity( 1 );
}
*/
?>