<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2016 TimeTrex Software Inc.
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


/**
 * @package API\UnAuthenticated
 */
class APIAuthentication extends APIFactory {
	protected $main_class = 'Authentication';

	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	//Default username=NULL to prevent argument warnings messages if its not passed from the API.
	function Login($user_name = NULL, $password = NULL, $type = 'USER_NAME') {
		global $config_vars;
		$authentication = new Authentication();

		Debug::text('User Name: '. $user_name .' Password Length: '. strlen($password) .' Type: '. $type, __FILE__, __LINE__, __METHOD__, 10);

		if ( ( isset($config_vars['other']['installer_enabled']) AND $config_vars['other']['installer_enabled'] == 1 ) OR ( isset($config_vars['other']['down_for_maintenance']) AND $config_vars['other']['down_for_maintenance'] == 1 ) ) {
			Debug::text('WARNING: Installer is enabled... Normal logins are disabled!', __FILE__, __LINE__, __METHOD__, 10);
			//When installer is enabled, just display down for maintenance message to user if they try to login.
			$error_message = TTi18n::gettext('%1 is currently undergoing maintenance. We apologize for any inconvenience this may cause, please try again later.', array(APPLICATION_NAME) );
			$validator_obj = new Validator();
			$validator_stats = array('total_records' => 1, 'valid_records' => 0 );
			$validator_obj->isTrue( 'user_name', FALSE, $error_message );
			$validator = array();
			$validator[0] = $validator_obj->getErrorsArray();
			return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), $validator, $validator_stats );
		}

		if ( isset($config_vars['other']['web_session_expire']) AND $config_vars['other']['web_session_expire'] != '' ) {
			$authentication->setEnableExpireSession( (int)$config_vars['other']['web_session_expire'] );
		}

		if ( $authentication->Login($user_name, $password, $type) === TRUE ) {
			$retval = $authentication->getSessionId();
			Debug::text('Success, Session ID: '. $retval, __FILE__, __LINE__, __METHOD__, 10);
			return $retval;
		} else {
			$validator_obj = new Validator();
			$validator_stats = array('total_records' => 1, 'valid_records' => 0 );

			$error_column = 'user_name';
			$error_message = TTi18n::gettext('User Name or Password is incorrect');

			//Get company status from user_name, so we can display messages for ONHOLD/Cancelled accounts.
			$clf = TTnew( 'CompanyListFactory' );
			$clf->getByUserName( $user_name );
			if ( $clf->getRecordCount() > 0 ) {
				$c_obj = $clf->getCurrent();
				if ( $c_obj->getStatus() == 20 ) {
					$error_message = TTi18n::gettext('Sorry, your company\'s account has been placed ON HOLD, please contact customer support immediately');
				} elseif ( $c_obj->getStatus() == 23 ) {
					$error_message = TTi18n::gettext('Sorry, your trial period has expired, please contact our sales department to reactivate your account');
				} elseif ( $c_obj->getStatus() == 28 ) {
					if ( $c_obj->getMigrateURL() != '' ) {
						$error_message = TTi18n::gettext('To better serve our customers your account has been migrated, please update your bookmarks to use the following URL from now on') . ': ' . 'http://'. $c_obj->getMigrateURL();
					} else {
						$error_message = TTi18n::gettext('To better serve our customers your account has been migrated, please contact customer support immediately.');
					}
				} elseif ( $c_obj->getStatus() == 30 ) {
					$error_message = TTi18n::gettext('Sorry, your company\'s account has been CANCELLED, please contact customer support if you believe this is an error');
				} else {
					$ulf = TTnew( 'UserListFactory' );
					$ulf->getByUserName( $user_name );
					if ( $ulf->getRecordCount() == 1 ) {
						$u_obj = $ulf->getCurrent();

						if ( $u_obj->checkPassword($password, FALSE) == TRUE ) {
							if ( $u_obj->isFirstLogin() == TRUE AND $u_obj->isCompromisedPassword() == TRUE ) {
								$error_message = TTi18n::gettext('Welcome to %1, since this is your first time logging in, we ask that you change your password to something more secure', array(APPLICATION_NAME) );
								$error_column = 'password';
							} elseif ( $u_obj->isPasswordPolicyEnabled() == TRUE ) {
								if ( $u_obj->isCompromisedPassword() == TRUE ) {
									$error_message = TTi18n::gettext('Due to your company\'s password policy, your password must be changed immediately');
									$error_column = 'password';
								} elseif(  $u_obj->checkPasswordAge() == FALSE ) {
									//Password policy is enabled, confirm users password has not exceeded maximum age.
									//Make sure we confirm that the password is in fact correct, but just expired.
									$error_message = TTi18n::gettext('Your password has exceeded its maximum age specified by your company\'s password policy and must be changed immediately');
									$error_column = 'password';
								}
							}
						}
					}
					unset($ulf, $u_obj);
				}
			}

			$validator_obj->isTrue( $error_column, FALSE, $error_message );

			$validator[0] = $validator_obj->getErrorsArray();

			return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), $validator, $validator_stats );
		}

		return $this->returnHandler( FALSE );
	}

	function newSession( $user_id, $client_id = NULL, $ip_address = NULL ) {
		global $authentication;

		if ( is_object($authentication) AND $authentication->getSessionID() != '' ) {
			Debug::text('Session ID: '. $authentication->getSessionID(), __FILE__, __LINE__, __METHOD__, 10);

			if ( $this->getPermissionObject()->Check('company', 'view') AND $this->getPermissionObject()->Check('company', 'login_other_user') ) {
				if ( !is_numeric( $user_id ) ) { //If username is used, lookup user_id
					Debug::Text('Lookup User ID by UserName: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
					$ulf = TTnew( 'UserListFactory' );
					$ulf->getByUserName( trim($user_id) );
					if ( $ulf->getRecordCount() == 1 ) {
						$user_id = $ulf->getCurrent()->getID();
					}
				}

				$ulf = TTnew( 'UserListFactory' );
				$ulf->getByIdAndStatus( (int)$user_id, 10 );  //Can only switch to Active employees
				if ( $ulf->getRecordCount() == 1 ) {
					$new_session_user_obj = $ulf->getCurrent();

					Debug::Text('Login as different user: '. $user_id .' IP Address: '. $ip_address, __FILE__, __LINE__, __METHOD__, 10);
					$new_session_id = $authentication->newSession( $user_id, $ip_address );

					$retarr = array(
									'session_id' => $new_session_id,
									'url' => Misc::getHostName(FALSE).Environment::getBaseURL(), //Don't include the port in the hostname, otherwise it can cause problems when forcing port 443 but not using 'https'.
									);

					//Add entry in source *AND* destination user log describing who logged in.
					//Source user log, showing that the source user logged in as someone else.
					TTLog::addEntry( $this->getCurrentUserObject()->getId(), 100, TTi18n::getText('Override Login').': '. TTi18n::getText('SourceIP').': '. $authentication->getIPAddress() .' '. TTi18n::getText('SessionID') .': '.$authentication->getSessionID() .' '.	TTi18n::getText('To Employee').': '. $new_session_user_obj->getFullName() .'('.$user_id.')', $this->getCurrentUserObject()->getId(), 'authentication');

					//Destination user log, showing the destination user was logged in *by* someone else.
					TTLog::addEntry( $user_id, 100, TTi18n::getText('Override Login').': '. TTi18n::getText('SourceIP').': '. $authentication->getIPAddress() .' '. TTi18n::getText('SessionID') .': '.$authentication->getSessionID() .' '.  TTi18n::getText('By Employee').': '. $this->getCurrentUserObject()->getFullName() .'('.$user_id.')', $user_id, 'authentication');

					return $this->returnHandler( $retarr );
				}
			}
		}

		return FALSE;
	}

	//Accepts user_id or user_name.
	function switchUser( $user_id ) {
		global $authentication;

		if ( is_object($authentication) AND $authentication->getSessionID() != '' ) {
			Debug::text('Session ID: '. $authentication->getSessionID(), __FILE__, __LINE__, __METHOD__, 10);

			if ( $this->getPermissionObject()->Check('company', 'view') AND $this->getPermissionObject()->Check('company', 'login_other_user') ) {
				if ( !is_numeric( $user_id ) ) { //If username is used, lookup user_id
					Debug::Text('Lookup User ID by UserName: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
					$ulf = TTnew( 'UserListFactory' );
					$ulf->getByUserName( trim($user_id) );
					if ( $ulf->getRecordCount() == 1 ) {
						$user_id = $ulf->getCurrent()->getID();
					}
				}

				$ulf = TTnew( 'UserListFactory' );
				$ulf->getByIdAndStatus( (int)$user_id, 10 );  //Can only switch to Active employees
				if ( $ulf->getRecordCount() == 1 ) {
					Debug::Text('Login as different user: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
					$authentication->changeObject( $user_id );

					//Add entry in source *AND* destination user log describing who logged in.
					//Source user log, showing that the source user logged in as someone else.
					TTLog::addEntry( $this->getCurrentUserObject()->getId(), 100, TTi18n::getText('Override Login').': '. TTi18n::getText('SourceIP').': '. $authentication->getIPAddress() .' '. TTi18n::getText('SessionID') .': '.$authentication->getSessionID() .' '.	TTi18n::getText('To Employee').': '. $authentication->getObject()->getFullName() .'('.$user_id.')', $this->getCurrentUserObject()->getId(), 'authentication');

					//Destination user log, showing the destination user was logged in *by* someone else.
					TTLog::addEntry( $user_id, 100, TTi18n::getText('Override Login').': '. TTi18n::getText('SourceIP').': '. $authentication->getIPAddress() .' '. TTi18n::getText('SessionID') .': '.$authentication->getSessionID() .' '.  TTi18n::getText('By Employee').': '. $this->getCurrentUserObject()->getFullName() .'('.$user_id.')', $user_id, 'authentication');

					return TRUE;
				} else {
					Debug::Text('User is likely not active: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
				}
			}
		}

		return FALSE;
	}

	function Logout() {
		global $authentication;

		if ( is_object($authentication) AND $authentication->getSessionID() != '' ) {
			Debug::text('Logging out session ID: '. $authentication->getSessionID(), __FILE__, __LINE__, __METHOD__, 10);

			return $authentication->Logout();
		}

		return FALSE;
	}

	function getSessionIdle() {
		global $config_vars;

		if ( isset($config_vars['other']['web_session_timeout']) AND $config_vars['other']['web_session_timeout'] != '' ) {
			return (int)$config_vars['other']['web_session_timeout'];
		} else {
			$authentication = new Authentication();
			return $authentication->getIdle();
		}
	}

	function isLoggedIn( $touch_updated_date = TRUE, $type = 'USER_NAME' ) {
		global $authentication, $config_vars;

		$session_id = getSessionID();

		if ( $session_id != '' ) {
			$authentication = new Authentication();

			Debug::text('AMF Session ID: '. $session_id .' Source IP: '. Misc::getRemoteIPAddress() .' Touch Updated Date: '. (int)$touch_updated_date, __FILE__, __LINE__, __METHOD__, 10);
			if ( isset($config_vars['other']['web_session_timeout']) AND $config_vars['other']['web_session_timeout'] != '' ) {
				$authentication->setIdle( (int)$config_vars['other']['web_session_timeout'] );
			}
			if ( $authentication->Check( $session_id, $type, $touch_updated_date ) === TRUE ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	function getCurrentUserName() {
		if ( is_object( $this->getCurrentUserObject() ) ) {
			return $this->returnHandler( $this->getCurrentUserObject()->getUserName() );
		}

		return $this->returnHandler( FALSE );
	}
	function getCurrentUser() {
		if ( is_object( $this->getCurrentUserObject() ) ) {
			return $this->returnHandler( $this->getCurrentUserObject()->getObjectAsArray( array( 'id' => TRUE, 'company_id' => TRUE, 'currency_id' => TRUE, 'permission_control_id' => TRUE, 'pay_period_schedule_id' => TRUE, 'policy_group_id' => TRUE, 'employee_number' => TRUE, 'user_name' => TRUE, 'phone_id' => TRUE, 'first_name' => TRUE, 'middle_name' => TRUE, 'last_name' => TRUE, 'full_name' => TRUE, 'city' => TRUE, 'province' => TRUE, 'country' => TRUE, 'longitude' => TRUE, 'latitude' => TRUE, 'work_phone' => TRUE, 'home_phone' => TRUE, 'work_email' => TRUE, 'home_email' => TRUE, 'feedback_rating' => TRUE, 'last_login_date' => TRUE, 'created_date' => TRUE ) ) );
		}

		return $this->returnHandler( FALSE );
	}

	function getCurrentCompany() {
		if ( is_object( $this->getCurrentCompanyObject() ) ) {
			return $this->returnHandler( $this->getCurrentCompanyObject()->getObjectAsArray( array('id' => TRUE, 'product_edition_id' => TRUE, 'name' => TRUE, 'industry' => TRUE, 'city' => TRUE, 'province' => TRUE, 'country' => TRUE, 'work_phone' => TRUE, 'application_build' => TRUE, 'is_setup_complete' => TRUE, 'total_active_days' => TRUE, 'created_date' => TRUE ) ) );
		}

		return $this->returnHandler( FALSE );
	}

	function getCurrentUserPreference() {
		if ( is_object( $this->getCurrentUserObject() ) AND is_object( $this->getCurrentUserObject()->getUserPreferenceObject() ) ) {
			return $this->returnHandler( $this->getCurrentUserObject()->getUserPreferenceObject()->getObjectAsArray() );
		}

		return $this->returnHandler( FALSE );
	}

	//Functions that can be called before the API client is logged in.
	//Mainly so the proper loading/login page can be displayed.
	function getProduction() {
		return PRODUCTION;
	}
	function getApplicationName() {
		return APPLICATION_NAME;
	}
	function getApplicationVersion() {
		return APPLICATION_VERSION;
	}
	function getApplicationVersionDate() {
		return APPLICATION_VERSION_DATE;
	}
	function getApplicationBuild() {
		return APPLICATION_BUILD;
	}
	function getOrganizationName() {
		return ORGANIZATION_NAME;
	}
	function getOrganizationURL() {
		return ORGANIZATION_URL;
	}
	function isApplicationBranded() {
		global $config_vars;

		if ( isset($config_vars['branding']['application_name']) ) {
			return TRUE;
		}

		return FALSE;
	}
	function isPoweredByLogoEnabled() {
		global $config_vars;

		if ( isset($config_vars['branding']['disable_powered_by_logo']) AND $config_vars['branding']['disable_powered_by_logo'] == TRUE ) {
			return FALSE;
		}

		return TRUE;
	}

	function isAnalyticsEnabled() {
		global $config_vars;

		if ( isset($config_vars['other']['disable_google_analytics']) AND $config_vars['other']['disable_google_analytics'] == TRUE ) {
			return FALSE;
		}

		return TRUE;
	}

	function getAnalyticsTrackingCode() {
		global $config_vars;

		if ( isset($config_vars['other']['analytics_tracking_code']) AND $config_vars['other']['analytics_tracking_code'] != '' ) {
			return $config_vars['other']['analytics_tracking_code'];
		}

		return 'UA-333702-3';
	}

	function getTTProductEdition( $name = FALSE ) {
		if ( $name == TRUE ) {
			$edition = getTTProductEditionName();
		} else {
			$edition = getTTProductEdition();
		}

		Debug::text('Edition: '. $edition, __FILE__, __LINE__, __METHOD__, 10);
		return $edition;
	}

	function getDeploymentOnDemand() {
		return DEPLOYMENT_ON_DEMAND;
	}

	function getRegistrationKey() {
		return SystemSettingFactory::getSystemSettingValueByKey( 'registration_key' );
	}

	function getLocale( $language = NULL, $country = NULL ) {
		$language = Misc::trimSortPrefix( $language );
		if ( $language == '' AND is_object( $this->getCurrentUserObject() ) AND is_object($this->getCurrentUserObject()->getUserPreferenceObject()) ) {
			$language = $this->getCurrentUserObject()->getUserPreferenceObject()->getLanguage();
		}
		if ( $country == '' AND is_object( $this->getCurrentUserObject() ) ) {
			$country = $this->getCurrentUserObject()->getCountry();
		}

		if ( $language != '' ) {
			TTi18n::setLanguage( $language );
		}
		if ( $country != '' ) {
			TTi18n::setCountry( $country );
		}
		TTi18n::setLocale(); //Sets master locale

		//$retval = str_replace('.UTF-8', '', TTi18n::getLocale() );
		$retval = TTi18n::getNormalizedLocale();

		Debug::text('Locale: '. $retval .' Language: '. $language, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	function getSystemLoad() {
		return Misc::getSystemLoad();
	}

	function getHTTPHost() {
		return $_SERVER['HTTP_HOST'];
	}

	function getCompanyName() {
		//Get primary company data needs to be used when user isn't logged in as well.
		$clf = TTnew( 'CompanyListFactory' );
		$clf->getByID( PRIMARY_COMPANY_ID );
		Debug::text('Primary Company ID: '. PRIMARY_COMPANY_ID, __FILE__, __LINE__, __METHOD__, 10);
		if ( $clf->getRecordCount() == 1 ) {
			return $clf->getCurrent()->getName();
		}

		Debug::text('  ERROR: Primary Company does not exist!', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}

	//Returns all login data required in a single call for optimization purposes.
	function getPreLoginData( $api = NULL ) {
		global $config_vars;

		if ( isset($_GET['disable_db']) OR ( isset($config_vars['other']['installer_enabled']) AND $config_vars['other']['installer_enabled'] == 1 ) ) {
			Debug::text('WARNING: Installer is enabled... Normal logins are disabled!', __FILE__, __LINE__, __METHOD__, 10);
			return array(
				'primary_company_id' => PRIMARY_COMPANY_ID, //Needed for some branded checks.
				'primary_company_name' => 'N/A',
				'analytics_enabled' => $this->isAnalyticsEnabled(),
				'application_version' => $this->getApplicationVersion(),
				'application_version_date' => $this->getApplicationVersionDate(),
				'application_build' => $this->getApplicationBuild(),
				'powered_by_logo_enabled' => $this->isPoweredByLogoEnabled(),
				'deployment_on_demand' => $this->getDeploymentOnDemand(),
				'analytics_enabled' => $this->isAnalyticsEnabled(),
				'analytics_tracking_code' => $this->getAnalyticsTrackingCode(),
				'product_edition' => $this->getTTProductEdition( FALSE ),
				'product_edition_name' => $this->getTTProductEdition( TRUE ),
				'registration_key' => 'INSTALLER',
				'http_host' => $this->getHTTPHost(),
				'production' => $this->getProduction(),
				'demo_mode' => DEMO_MODE,
				'base_url' => Environment::getBaseURL(),
				'cookie_base_url' => Environment::getCookieBaseURL(),
				'api_base_url' => Environment::getAPIBaseURL(),
				'language_options' => Misc::addSortPrefix( TTi18n::getLanguageArray() ),
				//Make sure locale is set properly before this function is called, either in api.php or APIGlobal.js.php for example.
				'enable_default_language_translation' => ( isset($config_vars['other']['enable_default_language_translation']) ) ? $config_vars['other']['enable_default_language_translation'] : FALSE,

				'language' => TTi18n::getLanguage(),
				'locale' => TTi18n::getNormalizedLocale(), //Needed for HTML5 interface to load proper translation file.
			);
		}

		$company_name = $this->getCompanyName();
		if ( $company_name == '' ) {
			$company_name = 'N/A';
		}

		return array(
				'primary_company_id' => PRIMARY_COMPANY_ID, //Needed for some branded checks.
				'primary_company_name' => $company_name,
				'base_url' => Environment::getBaseURL(),
				'cookie_base_url' => Environment::getCookieBaseURL(),
				'api_url' => Environment::getAPIURL( $api ),
				'api_base_url' => Environment::getAPIBaseURL( $api ),
				'api_json_url' => Environment::getAPIURL( 'json' ),
				'images_url' => Environment::getImagesURL(),
				'powered_by_logo_enabled' => $this->isPoweredByLogoEnabled(),
				'is_application_branded' => $this->isApplicationBranded(),
				'application_name' => $this->getApplicationName(),
				'organization_name' => $this->getOrganizationName(),
				'organization_url' => $this->getOrganizationURL(),
				'copyright_notice' => COPYRIGHT_NOTICE,
				'product_edition' => $this->getTTProductEdition( FALSE ),
				'product_edition_name' => $this->getTTProductEdition( TRUE ),
				'deployment_on_demand' => $this->getDeploymentOnDemand(),
				'web_session_expire' => ( isset($config_vars['other']['web_session_expire']) AND $config_vars['other']['web_session_expire'] != '' ) ? (bool)$config_vars['other']['web_session_expire'] : FALSE, //If TRUE then session expires when browser closes.
				'analytics_enabled' => $this->isAnalyticsEnabled(),
				'analytics_tracking_code' => $this->getAnalyticsTrackingCode(),
				'registration_key' => $this->getRegistrationKey(),
				'http_host' => $this->getHTTPHost(),
				'is_ssl' => Misc::isSSL(),
				'production' => $this->getProduction(),
				'demo_mode' => DEMO_MODE,
				'application_version' => $this->getApplicationVersion(),
				'application_version_date' => $this->getApplicationVersionDate(),
				'application_build' => $this->getApplicationBuild(),
				'is_logged_in' => $this->isLoggedIn(),
				'session_idle_timeout' => $this->getSessionIdle(),
				'footer_left_html' => ( isset($config_vars['other']['footer_left_html']) AND $config_vars['other']['footer_left_html'] != '' ) ? $config_vars['other']['footer_left_html'] : FALSE,
				'footer_right_html' => ( isset($config_vars['other']['footer_right_html']) AND $config_vars['other']['footer_right_html'] != '' ) ? $config_vars['other']['footer_right_html'] : FALSE,
				'language_options' => Misc::addSortPrefix( TTi18n::getLanguageArray() ),
				//Make sure locale is set properly before this function is called, either in api.php or APIGlobal.js.php for example.
				'enable_default_language_translation' => ( isset($config_vars['other']['enable_default_language_translation']) ) ? $config_vars['other']['enable_default_language_translation'] : FALSE,
				'language' => TTi18n::getLanguage(),
				'locale' => TTi18n::getNormalizedLocale(), //Needed for HTML5 interface to load proper translation file.

				'map_api_key' => ( isset($config_vars['map']['api_key']) AND $config_vars['map']['api_key'] != '' ) ? $config_vars['map']['map_api_key'] : '',
				'map_provider' => isset($config_vars['map']['provider'] ) ? $config_vars['map']['provider'] : 'timetrex',
				'map_tile_url' => isset( $config_vars['map']['tile_url'] ) ? rtrim($config_vars['map']['tile_url'], '/') : '//map-tiles.timetrex.com',
				'map_routing_url' => isset( $config_vars['map']['routing_url'] ) ? rtrim($config_vars['map']['routing_url'], '/') : '//map-routing.timetrex.com',
				'map_geocode_url' => isset( $config_vars['map']['geocode_url'] ) ? rtrim($config_vars['map']['geocode_url'], '/') : '//map-geocode.timetrex.com',
		);
	}


	//Function that HTML5 interface can call when an irrecoverable error or uncaught exception is triggered.
	function sendErrorReport( $data = NULL, $screenshot = NULL ) {
		$rl = TTNew('RateLimit');
		$rl->setID( 'error_report_'. Misc::getRemoteIPAddress() );
		$rl->setAllowedCalls( 20 );
		$rl->setTimeFrame( 900 ); //15 minutes
		if ( $rl->check() == FALSE ) {
			Debug::Text('Excessive error reports... Preventing error reports from: '. Misc::getRemoteIPAddress() .' for up to 15 minutes...', __FILE__, __LINE__, __METHOD__, 10);
			return APPLICATION_BUILD;
		}

		$attachments = NULL;
		if ( $screenshot != '' ) {
			$attachments[] = array( 'file_name' => 'screenshot.png', 'mime_type' => 'image/png', 'data' => base64_decode( $screenshot ) );
		}

		if ( defined( 'TIMETREX_JSON_API' ) == TRUE ) {
			$subject = TTi18n::gettext('HTML5 Error Report');
		} else {
			$subject = TTi18n::gettext('Flex Error Report');
		}

		$data = 'IP Address: '. Misc::getRemoteIPAddress() ."\nServer Version: ". APPLICATION_BUILD ."\n\n". $data;

		Misc::sendSystemMail( $subject, $data, $attachments ); //Do not send if PRODUCTION=FALSE.

		//return APPLICATION_BUILD so JS can check if its correct and notify the user to refresh/clear cache.
		return APPLICATION_BUILD;
	}

	/**
	 * Allows user who isn't logged in to change their password.
	 * @param string $user_name
	 * @param string $current_password
	 * @param string $new_password
	 * @param string $new_password2
	 * @param string $type
	 * @return bool
	 */
	function changePassword( $user_name, $current_password, $new_password, $new_password2 ) {
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getByUserName( $user_name );
		if ( $ulf->getRecordCount() > 0 ) {
			foreach( $ulf as $u_obj ) {
				if ( $u_obj->getCompanyObject()->getStatus() == 10 ) {
					Debug::text('Attempting to change password for: '. $user_name, __FILE__, __LINE__, __METHOD__, 10);

					if ( $current_password != '' ) {
						if ( $u_obj->checkPassword($current_password, FALSE) !== TRUE ) { //Disable password policy checking on current password.
							Debug::Text('Password check failed!', __FILE__, __LINE__, __METHOD__, 10);
							$u_obj->Validator->isTrue(	'current_password',
													FALSE,
													TTi18n::gettext('Current password is incorrect') );
						}
					} else {
						Debug::Text('Current password not specified', __FILE__, __LINE__, __METHOD__, 10);
						$u_obj->Validator->isTrue(	'current_password',
												FALSE,
												TTi18n::gettext('Current password is incorrect') );
					}

					if ( $current_password == $new_password ) {
						$u_obj->Validator->isTrue(	'password',
												FALSE,
												TTi18n::gettext('New password must be different than current password') );
					} else {
						if ( $new_password != '' OR $new_password2 != ''  ) {
							if ( $new_password == $new_password2 ) {
								$u_obj->setPassword($new_password);
							} else {
								$u_obj->Validator->isTrue(	'password',
														FALSE,
														TTi18n::gettext('Passwords don\'t match') );
							}
						} else {
							$u_obj->Validator->isTrue(	'password',
													FALSE,
													TTi18n::gettext('Passwords don\'t match') );
						}
					}

					if ( $u_obj->isValid() ) {
						if ( DEMO_MODE == TRUE ) {
							//Return TRUE even in demo mode, but nothing happens.
							return $this->returnHandler( TRUE );
						} else {
							//This should force the updated_by field to match the user changing their password,
							//  so we know now to ask the user to change their password again, since they were the last ones to do so.
							global $current_user;
							$current_user = $u_obj;

							TTLog::addEntry( $u_obj->getID(), 20, TTi18n::getText('Password - Web (Password Policy)'), NULL, $u_obj->getTable() );
							$retval = $u_obj->Save();

							unset($current_user);

							return $this->returnHandler( $retval ); //Single valid record
						}
					} else {
						return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), $u_obj->Validator->getErrorsArray(), array('total_records' => 1, 'valid_records' => 0) );
					}
				}
			}
		}

		return $this->returnHandler( FALSE );
	}

	//Ping function is also in APIMisc for when the session timesout is valid.
	//Ping no longer can tell if the session is timed-out, must use "isLoggedIn(FALSE)" instead.
	function Ping() {
		return TRUE;
	}
}
?>
