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


/**
 * @package API\UnAuthenticated
 */
class APIAuthentication extends APIFactory {
	protected $main_class = 'Authentication';

	/**
	 * APIAuthentication constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	/**
	 * @param null $user_name
	 * @param null $password
	 * @return array
	 */
	function PunchLogin( $user_name = NULL, $password = NULL ) {
		global $config_vars;
		Debug::Text('Quick Punch ID: '. $user_name, __FILE__, __LINE__, __METHOD__, 10);
		$authentication = new Authentication();
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
		$clf = TTnew( 'CompanyListFactory' );
		$clf->getByPhoneID( $user_name );
		if ( $clf->getRecordCount() == 1 ) {
			$c_obj = $clf->getCurrent();
		} else {
			$c_obj = FALSE;
		}
		//Checks user_name/password
		$password_result = FALSE;
		$user_name = trim($user_name);
		if ( $user_name != '' AND $password != '' AND ( is_object($c_obj) AND $c_obj->getStatus() == 10 AND $c_obj->getProductEdition() >= TT_PRODUCT_PROFESSIONAL ) ) {
			$password_result = $authentication->Login($user_name, $password, 'QUICK_PUNCH_ID');
		}
		if ( $password_result === TRUE ) {
			$clf = TTnew( 'CompanyListFactory' );
			$clf->getByID( $authentication->getObject()->getCompany() );
			$current_company = $clf->getCurrent();
			unset($clf);
			$create_new_station = FALSE;
			//If this is a new station, insert it now.
			if ( isset( $_COOKIE['StationID'] ) ) {
				Debug::text('Station ID Cookie found! '. $_COOKIE['StationID'], __FILE__, __LINE__, __METHOD__, 10);

				$slf = TTnew( 'StationListFactory' );
				$slf->getByStationIdandCompanyId( $_COOKIE['StationID'], $current_company->getId() );
				$current_station = $slf->getCurrent();
				unset($slf);

				if ( $current_station->isNew() ) {
					Debug::text('Station ID is NOT IN DB!! '. $_COOKIE['StationID'], __FILE__, __LINE__, __METHOD__, 10);
					$create_new_station = TRUE;
				}
			} else {
				$create_new_station = TRUE;
			}

			if ( $create_new_station == TRUE ) {
				//Insert new station
				$sf = TTnew( 'StationFactory' );

				$sf->setCompany( $current_company->getId() );
				$sf->setStatus( 20 ); //Enabled
				if ( Misc::detectMobileBrowser() == FALSE ) {
					Debug::text('PC Station device...', __FILE__, __LINE__, __METHOD__, 10);
					$sf->setType( 10 ); //PC
				} else {
					$sf->setType( 26 ); //Mobile device web browser
					Debug::text('Mobile Station device...', __FILE__, __LINE__, __METHOD__, 10);
				}
				$sf->setSource( Misc::getRemoteIPAddress() );
				$sf->setStation();
				$sf->setDescription( substr( $_SERVER['HTTP_USER_AGENT'], 0, 250) );
				if ( $sf->isValid() ) { //Standard Edition can't save mobile stations.
					if ( $sf->Save(FALSE) ) {
						$sf->setCookie();
					}
				}
			}
			return array('SessionID' => $authentication->getSessionId());
		} else {
			$validator_obj = new Validator();
			$validator_stats = array('total_records' => 1, 'valid_records' => 0 );

			$error_column = 'quick_punch_id'; // match the correct input field in the html
			$error_message = TTi18n::gettext('Quick Punch ID or Password is incorrect');
			//Get company status from user_name, so we can display messages for ONHOLD/Cancelled accounts.
			if ( is_object( $c_obj ) ) {
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
				}
			}
			$validator_obj->isTrue( $error_column, FALSE, $error_message );
			$validator = array();
			$validator[0] = $validator_obj->getErrorsArray();
			return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), $validator, $validator_stats );
		}
	}

	/**
	 * Default username=NULL to prevent argument warnings messages if its not passed from the API.
	 * @param null $user_name
	 * @param null $password
	 * @param string $type
	 * @return array|null
	 */
	function Login( $user_name = NULL, $password = NULL, $type = 'USER_NAME') {
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
						$migrate_url = ( Misc::isSSL() == TRUE ) ? 'https://'. $c_obj->getMigrateURL() : 'http://'. $c_obj->getMigrateURL();
						$error_message = TTi18n::gettext('To better serve our customers your account has been migrated, please update your bookmarks to use the following URL from now on') . ': ' . '<a href="'. $migrate_url .'">'. $migrate_url .'</a>';
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
	}

	/**
	 * @param string $user_id UUID
	 * @param string $client_id UUID
	 * @param null $ip_address
	 * @return array|bool
	 */
	function newSession( $user_id, $client_id = NULL, $ip_address = NULL ) {
		global $authentication;

		if ( is_object($authentication) AND $authentication->getSessionID() != '' ) {
			Debug::text('Session ID: '. $authentication->getSessionID(), __FILE__, __LINE__, __METHOD__, 10);

			if ( $this->getPermissionObject()->Check('company', 'view') AND $this->getPermissionObject()->Check('company', 'login_other_user') ) {
				if ( TTUUID::isUUID( $user_id ) == FALSE ) { //If username is used, lookup user_id
					Debug::Text('Lookup User ID by UserName: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
					$ulf = TTnew( 'UserListFactory' );
					$ulf->getByUserName( trim($user_id) );
					if ( $ulf->getRecordCount() == 1 ) {
						$user_id = $ulf->getCurrent()->getID();
					}
				}

				$ulf = TTnew( 'UserListFactory' );
				$ulf->getByIdAndStatus( TTUUID::castUUID($user_id), 10 );  //Can only switch to Active employees
				if ( $ulf->getRecordCount() == 1 ) {
					$new_session_user_obj = $ulf->getCurrent();

					Debug::Text('Login as different user: '. $user_id .' IP Address: '. $ip_address, __FILE__, __LINE__, __METHOD__, 10);
					$new_session_id = $authentication->newSession( $user_id, $ip_address );

					$retarr = array(
									'session_id' => $new_session_id,
									'url' => Misc::getHostName(FALSE).Environment::getBaseURL(), //Don't include the port in the hostname, otherwise it can cause problems when forcing port 443 but not using 'https'.
									'cookie_base_url' => Environment::getCookieBaseURL(),
									);

					//Add entry in source *AND* destination user log describing who logged in.
					//Source user log, showing that the source user logged in as someone else.
					TTLog::addEntry( $this->getCurrentUserObject()->getId(), 100, TTi18n::getText('Override Login').': '. TTi18n::getText('SourceIP').': '. $authentication->getIPAddress() .' '. TTi18n::getText('SessionID') .': '.$authentication->getSecureSessionID() .' '.	TTi18n::getText('To Employee').': '. $new_session_user_obj->getFullName() .' ('.$user_id.')', $this->getCurrentUserObject()->getId(), 'authentication');

					//Destination user log, showing the destination user was logged in *by* someone else.
					TTLog::addEntry( $user_id, 100, TTi18n::getText('Override Login').': '. TTi18n::getText('SourceIP').': '. $authentication->getIPAddress() .' '. TTi18n::getText('SessionID') .': '.$authentication->getSecureSessionID() .' '.  TTi18n::getText('By Employee').': '. $this->getCurrentUserObject()->getFullName() .' ('.$user_id.')', $user_id, 'authentication');

					return $this->returnHandler( $retarr );
				}
			} else {
				Debug::text('  ERROR: Permission check failed for logging in as another user...', __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		return FALSE;
	}

	/**
	 * Accepts user_id or user_name.
	 * @param string $user_id UUID
	 * @return bool
	 */
	function switchUser( $user_id ) {
		global $authentication;

		if ( is_object($authentication) AND $authentication->getSessionID() != '' ) {
			Debug::text('Session ID: '. $authentication->getSessionID(), __FILE__, __LINE__, __METHOD__, 10);

			if ( $this->getPermissionObject()->Check('company', 'view') AND $this->getPermissionObject()->Check('company', 'login_other_user') ) {
				if ( TTUUID::isUUID( $user_id ) == FALSE ) { //If username is used, lookup user_id
					Debug::Text('Lookup User ID by UserName: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
					$ulf = TTnew( 'UserListFactory' );
					$ulf->getByUserName( trim($user_id) );
					if ( $ulf->getRecordCount() == 1 ) {
						$user_id = $ulf->getCurrent()->getID();
					}
				}

				$ulf = TTnew( 'UserListFactory' );
				$ulf->getByIdAndStatus( TTUUID::castUUID($user_id), 10 );  //Can only switch to Active employees
				if ( $ulf->getRecordCount() == 1 ) {
					Debug::Text('Login as different user: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
					$authentication->changeObject( $user_id );

					//Add entry in source *AND* destination user log describing who logged in.
					//Source user log, showing that the source user logged in as someone else.
					TTLog::addEntry( $this->getCurrentUserObject()->getId(), 100, TTi18n::getText('Override Login').': '. TTi18n::getText('SourceIP').': '. $authentication->getIPAddress() .' '. TTi18n::getText('SessionID') .': '.$authentication->getSecureSessionID() .' '.	TTi18n::getText('To Employee').': '. $authentication->getObject()->getFullName() .' ('.$user_id.')', $this->getCurrentUserObject()->getId(), 'authentication');

					//Destination user log, showing the destination user was logged in *by* someone else.
					TTLog::addEntry( $user_id, 100, TTi18n::getText('Override Login').': '. TTi18n::getText('SourceIP').': '. $authentication->getIPAddress() .' '. TTi18n::getText('SessionID') .': '.$authentication->getSecureSessionID() .' '.  TTi18n::getText('By Employee').': '. $this->getCurrentUserObject()->getFullName() .' ('.$user_id.')', $user_id, 'authentication');

					return TRUE;
				} else {
					Debug::Text('User is likely not active: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
				}
			}  else {
				Debug::text('  ERROR: Permission check failed for switching users...', __FILE__, __LINE__, __METHOD__, 10);
			}
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function Logout() {
		global $authentication;

		if ( is_object($authentication) AND $authentication->getSessionID() != '' ) {
			Debug::text('Logging out session ID: '. $authentication->getSessionID(), __FILE__, __LINE__, __METHOD__, 10);

			return $authentication->Logout();
		}

		return FALSE;
	}

	/**
	 * @return int
	 */
	function getSessionIdle() {
		global $config_vars;

		if ( isset($config_vars['other']['web_session_timeout']) AND $config_vars['other']['web_session_timeout'] != '' ) {
			return (int)$config_vars['other']['web_session_timeout'];
		} else {
			$authentication = new Authentication();
			return $authentication->getIdle();
		}
	}

	/**
	 * @param bool $touch_updated_date
	 * @param string $type
	 * @return bool
	 */
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

	/**
	 * @return array
	 */
	function getCurrentUserName() {
		if ( is_object( $this->getCurrentUserObject() ) ) {
			return $this->returnHandler( $this->getCurrentUserObject()->getUserName() );
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * @return array
	 */
	function getCurrentUser() {
		if ( is_object( $this->getCurrentUserObject() ) ) {
			return $this->returnHandler( $this->getCurrentUserObject()->getObjectAsArray( array( 'id' => TRUE, 'company_id' => TRUE, 'currency_id' => TRUE, 'permission_control_id' => TRUE, 'pay_period_schedule_id' => TRUE, 'policy_group_id' => TRUE, 'employee_number' => TRUE, 'user_name' => TRUE, 'phone_id' => TRUE, 'first_name' => TRUE, 'middle_name' => TRUE, 'last_name' => TRUE, 'full_name' => TRUE, 'city' => TRUE, 'province' => TRUE, 'country' => TRUE, 'longitude' => TRUE, 'latitude' => TRUE, 'work_phone' => TRUE, 'home_phone' => TRUE, 'work_email' => TRUE, 'home_email' => TRUE, 'feedback_rating' => TRUE, 'last_login_date' => TRUE, 'created_date' => TRUE, 'is_owner' => TRUE, 'is_child' => TRUE ) ) );
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * @return array
	 */
	function getCurrentCompany() {
		if ( is_object( $this->getCurrentCompanyObject() ) ) {
			return $this->returnHandler( $this->getCurrentCompanyObject()->getObjectAsArray( array('id' => TRUE, 'product_edition_id' => TRUE, 'name' => TRUE, 'short_name' => TRUE, 'industry' => TRUE, 'city' => TRUE, 'province' => TRUE, 'country' => TRUE, 'work_phone' => TRUE, 'application_build' => TRUE, 'is_setup_complete' => TRUE, 'total_active_days' => TRUE, 'created_date' => TRUE, 'latitude' => TRUE, 'longitude' => TRUE ) ) );
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * @return array
	 */
	function getCurrentUserPreference() {
		if ( is_object( $this->getCurrentUserObject() ) AND is_object( $this->getCurrentUserObject()->getUserPreferenceObject() ) ) {
			return $this->returnHandler( $this->getCurrentUserObject()->getUserPreferenceObject()->getObjectAsArray() );
		}

		return $this->returnHandler( FALSE );
	}

	//Functions that can be called before the API client is logged in.
	//Mainly so the proper loading/login page can be displayed.
	/**
	 * @return bool
	 */
	function getProduction() {
		return PRODUCTION;
	}

	/**
	 * @return string
	 */
	function getApplicationName() {
		return APPLICATION_NAME;
	}

	/**
	 * @return string
	 */
	function getApplicationVersion() {
		return APPLICATION_VERSION;
	}

	/**
	 * @return int
	 */
	function getApplicationVersionDate() {
		return APPLICATION_VERSION_DATE;
	}

	/**
	 * @return string
	 */
	function getApplicationBuild() {
		return APPLICATION_BUILD;
	}

	/**
	 * @return string
	 */
	function getOrganizationName() {
		return ORGANIZATION_NAME;
	}

	/**
	 * @return string
	 */
	function getOrganizationURL() {
		return ORGANIZATION_URL;
	}

	/**
	 * @return bool
	 */
	function isApplicationBranded() {
		global $config_vars;

		if ( isset($config_vars['branding']['application_name']) ) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function isPoweredByLogoEnabled() {
		global $config_vars;

		if ( isset($config_vars['branding']['disable_powered_by_logo']) AND $config_vars['branding']['disable_powered_by_logo'] == TRUE ) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function isAnalyticsEnabled() {
		global $config_vars;

		if ( isset($config_vars['other']['disable_google_analytics']) AND $config_vars['other']['disable_google_analytics'] == TRUE ) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * @return string
	 */
	function getAnalyticsTrackingCode() {
		global $config_vars;

		if ( isset($config_vars['other']['analytics_tracking_code']) AND $config_vars['other']['analytics_tracking_code'] != '' ) {
			return $config_vars['other']['analytics_tracking_code'];
		}

		return 'UA-333702-3';
	}

	/**
	 * @param bool $name
	 * @return int|string
	 */
	function getTTProductEdition( $name = FALSE ) {
		if ( $name == TRUE ) {
			$edition = getTTProductEditionName();
		} else {
			$edition = getTTProductEdition();
		}

		Debug::text('Edition: '. $edition, __FILE__, __LINE__, __METHOD__, 10);
		return $edition;
	}

	/**
	 * @return bool
	 */
	function getDeploymentOnDemand() {
		return DEPLOYMENT_ON_DEMAND;
	}

	/**
	 * @return bool
	 */
	function getRegistrationKey() {
		return SystemSettingFactory::getSystemSettingValueByKey( 'registration_key' );
	}

	/**
	 * @param null $language
	 * @param null $country
	 * @return null
	 */
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

	/**
	 * @return int|mixed
	 */
	function getSystemLoad() {
		return Misc::getSystemLoad();
	}

	/**
	 * @return mixed
	 */
	function getHTTPHost() {
		return $_SERVER['HTTP_HOST'];
	}

	/**
	 * @return bool
	 */
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

	/**
	 * @param null $api
	 * @return array
	 */
	function getPreLoginData( $api = NULL ) {
		global $config_vars;

		//Basic settings that *do not* require a DB connection.
		$retarr = array(
				'primary_company_id' => PRIMARY_COMPANY_ID, //Needed for some branded checks.
				'primary_company_name' => NULL, //Requires DB connection.
				'base_url' => Environment::getBaseURL(),
				'cookie_base_url' => Environment::getCookieBaseURL(),
				'api_url' => Environment::getAPIURL( $api ),
				'api_base_url' => Environment::getAPIBaseURL( $api ),
				'api_json_url' => Environment::getAPIURL( 'json' ),
				'images_url' => Environment::getImagesURL(),
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
				'registration_key' => NULL, //Requires DB connection.
				'http_host' => $this->getHTTPHost(),
				'is_ssl' => Misc::isSSL(),
				'production' => $this->getProduction(),
				'demo_mode' => DEMO_MODE,
				'application_version' => $this->getApplicationVersion(),
				'application_version_date' => $this->getApplicationVersionDate(),
				'application_build' => $this->getApplicationBuild(),
				'is_logged_in' => FALSE, //Requires DB connection.
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

				//registration key for the map servers must be added in JS because of the url formats
				'map_tile_url' => isset( $config_vars['map']['tile_url'] ) ? rtrim($config_vars['map']['tile_url'], '/') : '//map-tiles.timetrex.com',
				'map_routing_url' => isset( $config_vars['map']['routing_url'] ) ? rtrim($config_vars['map']['routing_url'], '/') : '//map-routing.timetrex.com',
				'map_geocode_url' => isset( $config_vars['map']['geocode_url'] ) ? rtrim($config_vars['map']['geocode_url'], '/') : '//map-geocode.timetrex.com',

				'sandbox_url' => isset($config_vars['other']['sandbox_url']) ? $config_vars['other']['sandbox_url'] : FALSE,
				'sandbox' => isset($config_vars['other']['sandbox']) ? $config_vars['other']['sandbox'] : FALSE,
				'uuid_seed' => TTUUID::getSeed(TRUE),
		);

		if ( (isset($_GET['disable_db']) AND $_GET['disable_db'] == 1 )
				OR ( isset($config_vars['other']['installer_enabled']) AND $config_vars['other']['installer_enabled'] == 1 )
				OR ( isset($config_vars['other']['down_for_maintenance']) AND $config_vars['other']['down_for_maintenance'] == TRUE ) ) {
			Debug::text( 'WARNING: Installer/Down For Maintenance is enabled... Normal logins are disabled!', __FILE__, __LINE__, __METHOD__, 10 );
		} else {
			//Only data that requires a DB connection to obtain here.
			$retarr['company_name'] = $this->getCompanyName();
			if ( $retarr['company_name'] == '' ) {
				$retarr['company_name'] == 'N/A';
			}

			$retarr['registration_key'] = $this->getRegistrationKey();
			$retarr['is_logged_in'] = $this->isLoggedIn();
		}

		return $retarr;
	}

	/**
	 * Function that HTML5 interface can call when an irrecoverable error or uncaught exception is triggered.
	 * @param null $data
	 * @param null $screenshot
	 * @return string
	 */
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

		$subject = 'HTML5 Error Report'; //Don't translate this, as it breaks filters.

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
	 * @return array|bool
	 * @internal param string $type
	 */
	function changePassword( $user_name, $current_password = NULL, $new_password = NULL, $new_password2 = NULL ) {
		$rl = TTNew( 'RateLimit' );
		$rl->setID( 'authentication_' . Misc::getRemoteIPAddress() );
		$rl->setAllowedCalls( 20 );
		$rl->setTimeFrame( 900 ); //15 minutes

		if ( $rl->check() == FALSE ) {
			Debug::Text( 'Excessive failed password attempts... Preventing password change from: ' . Misc::getRemoteIPAddress() . ' for up to 15 minutes...', __FILE__, __LINE__, __METHOD__, 10 );
			sleep( 5 ); //Excessive password attempts, sleep longer.
			$u_obj = TTnew( 'UserListFactory' );
			$u_obj->Validator->isTrue( 'current_password', FALSE, TTi18n::gettext( 'Current User Name or Password is incorrect' ) );

			return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), $u_obj->Validator->getErrorsArray(), array('total_records' => 1, 'valid_records' => 0) );
		}

		$ulf = TTnew( 'UserListFactory' );
		$ulf->getByUserName( $user_name );
		if ( $ulf->getRecordCount() == 1 ) {
			$u_obj = $ulf->getCurrent();
			if ( $u_obj->getCompanyObject()->getStatus() == 10 ) {
				Debug::text( 'Attempting to change password for: ' . $user_name, __FILE__, __LINE__, __METHOD__, 10 );

				if ( $current_password != '' ) {
					if ( $u_obj->checkPassword( $current_password, FALSE ) !== TRUE ) { //Disable password policy checking on current password.
						Debug::text( 'Password check failed! Attempt: ' . $rl->getAttempts(), __FILE__, __LINE__, __METHOD__, 10 );
						//sleep( ( $rl->getAttempts() * 0.5 ) ); //If password is incorrect, sleep for some time to slow down brute force attacks.
						$u_obj->Validator->isTrue( 'current_password',
												   FALSE,
												   TTi18n::gettext( 'Current User Name or Password is incorrect' ) );
					}
				} else {
					Debug::Text( 'Current password not specified', __FILE__, __LINE__, __METHOD__, 10 );
					$u_obj->Validator->isTrue( 'current_password',
											   FALSE,
											   TTi18n::gettext( 'Current User Name or Password is incorrect' ) );
				}

				if ( $current_password == $new_password ) {
					$u_obj->Validator->isTrue( 'password',
											   FALSE,
											   TTi18n::gettext( 'New password must be different than current password' ) );
				} else {
					if ( $new_password != '' OR $new_password2 != '' ) {
						if ( $new_password == $new_password2 ) {
							$u_obj->setPassword( $new_password );
						} else {
							$u_obj->Validator->isTrue( 'password',
													   FALSE,
													   TTi18n::gettext( 'Passwords don\'t match' ) );
						}
					} else {
						$u_obj->Validator->isTrue( 'password',
												   FALSE,
												   TTi18n::gettext( 'Passwords don\'t match' ) );
					}
				}
			}

			//This should force the updated_by field to match the user changing their password,
			//  so we know not to ask the user to change their password again, since they were the last ones to do so.
			//$current_user must be set above $u_obj->isValid() so it can properly validate things like hierarchy and such in UserFactory.
			global $current_user;
			$current_user = $u_obj;

			if ( $u_obj->isValid() ) {
				if ( DEMO_MODE == TRUE ) {
					//Return TRUE even in demo mode, but nothing happens.
					return $this->returnHandler( TRUE );
				} else {
					TTLog::addEntry( $u_obj->getID(), 20, TTi18n::getText( 'Password - Web (Password Policy)' ), NULL, $u_obj->getTable() );
					$rl->delete(); //Clear failed password rate limit upon successful login.

					$retval = $u_obj->Save();

					unset( $current_user );

					return $this->returnHandler( $retval ); //Single valid record
				}
			}
		} else {
			//Issue #2225 - Be sure to return the same error message even if username is not valid to avoid user enumeration attacks.
			$u_obj = TTnew( 'UserListFactory' );
			$u_obj->Validator->isTrue( 'current_password', FALSE, TTi18n::gettext( 'Current User Name or Password is incorrect' ) );
		}

		sleep( ( $rl->getAttempts() * 0.5 ) ); //If password is incorrect, sleep for some time to slow down brute force attacks.
		Debug::Text( 'Failed username/password... Attempt: '. $rl->getAttempts() .' Sleeping...', __FILE__, __LINE__, __METHOD__, 10 );

		return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), $u_obj->Validator->getErrorsArray(), array('total_records' => 1, 'valid_records' => 0) );
	}

	/**
	 * @param $email
	 * @return array
	 */
	function resetPassword( $email ) {
		//Debug::setVerbosity( 11 );
		$rl = TTNew('RateLimit');
		$rl->setID( 'password_reset_'. Misc::getRemoteIPAddress() );
		$rl->setAllowedCalls( 10 );
		$rl->setTimeFrame( 900 ); //15 minutes

		$validator = new Validator();

		Debug::Text('Email: '. $email, __FILE__, __LINE__, __METHOD__, 10);
		if ( $rl->check() == FALSE ) {
			Debug::Text('Excessive reset password attempts... Preventing resets from: '. Misc::getRemoteIPAddress() .' for up to 15 minutes...', __FILE__, __LINE__, __METHOD__, 10);
			sleep(5); //Excessive password attempts, sleep longer.
			$validator->isTrue('email', FALSE, TTi18n::getText('Email address was not found in our database (z)') );
		} else {
			$ulf = TTnew( 'UserListFactory' );
			$ulf->getByHomeEmailOrWorkEmail( $email );
			if ( $ulf->getRecordCount() == 1 ) {
				$user_obj = $ulf->getCurrent();
				if ( $user_obj->getStatus() == 10 ) { //Only allow password resets on active employees.
					//Check if company is using LDAP authentication, if so deny password reset.
					if ( $user_obj->getCompanyObject()->getLDAPAuthenticationType() == 0 ) {
						if ( $user_obj->sendPasswordResetEmail() == TRUE ) {
							Debug::Text( 'Found USER! ', __FILE__, __LINE__, __METHOD__, 10 );
							$rl->delete(); //Clear password reset rate limit upon successful login.

							return $this->returnHandler( array('email_sent' => 1, 'email' => $email) );
						} else {
							Debug::Text('ERROR: Unable to send password reset email, perhaps user record is invalid?', __FILE__, __LINE__, __METHOD__, 10);
							$validator->isTrue('email', FALSE, TTi18n::getText('Unable to reset password, please contact your administrator for more information') );
						}
					} else {
						Debug::Text('LDAP Authentication is enabled, password reset is disabled! ', __FILE__, __LINE__, __METHOD__, 10);
						$validator->isTrue('email', FALSE, TTi18n::getText('Please contact your administrator for instructions on changing your password'). ' (LDAP)' );
					}
				} else {
					$validator->isTrue('email', FALSE, TTi18n::getText('Email address was not found in our database (b)') );
				}
			} else {
				//Error
				Debug::Text('DID NOT FIND USER! Returned: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
				$validator->isTrue('email', FALSE, TTi18n::getText('Email address was not found in our database (a)') );
			}

			Debug::text('Reset Password Failed! Attempt: '. $rl->getAttempts(), __FILE__, __LINE__, __METHOD__, 10);
			sleep( ($rl->getAttempts() * 0.5) ); //If email is incorrect, sleep for some time to slow down brute force attacks.
		}

		return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), array('error' => $validator->getErrorsArray()), array('total_records' => 1, 'valid_records' => 0) );
	}

	/**
	 * Reset the password if users forgotten their password
	 * @param $key
	 * @param $password
	 * @param $password2
	 * @return array
	 */
	function passwordReset( $key, $password, $password2 ) {
		$rl = TTNew('RateLimit');
		$rl->setID( 'password_reset_'. Misc::getRemoteIPAddress() );
		$rl->setAllowedCalls( 10 );
		$rl->setTimeFrame( 900 ); //15 minutes

		$validator = new Validator();
		if ( $rl->check() == FALSE ) {
			Debug::Text('Excessive password reset attempts... Preventing resets from: '. Misc::getRemoteIPAddress() .' for up to 15 minutes...', __FILE__, __LINE__, __METHOD__, 10);
			sleep(5); //Excessive password attempts, sleep longer.
		} else {
			$ulf = TTnew( 'UserListFactory' );
			Debug::Text('Key: '. $key, __FILE__, __LINE__, __METHOD__, 10);
			$ulf->getByPasswordResetKey( $key );
			if ( $ulf->getRecordCount() == 1 ) {
				Debug::Text('FOUND Password reset key! ', __FILE__, __LINE__, __METHOD__, 10);
				$user_obj = $ulf->getCurrent();
				if ( $user_obj->checkPasswordResetKey( $key ) == TRUE ) {
					//Make sure passwords match
					Debug::Text('Change Password Key: '. $key, __FILE__, __LINE__, __METHOD__, 10);
					if ( $password != '' AND trim($password) === trim($password2) ) {
						//Change password
						$user_obj->setPassword( $password ); //Password reset key is cleared when password is changed.
						if ( $user_obj->isValid() ) {
							$user_obj->Save(FALSE);
							Debug::Text('Password Change succesful!', __FILE__, __LINE__, __METHOD__, 10);

							//Logout all sessions for this user when password is successfully reset.
							$authentication = TTNew('Authentication');
							$authentication->logoutUser( $user_obj->getId() );
							unset($user_obj);

							return $this->returnHandler( TRUE );
						} else {
							$validator->merge( $user_obj->Validator ); //Make sure we display any validation errors like password too weak.
						}
					} else {
						$validator->isTrue('password', FALSE, TTi18n::getText('Passwords do not match') );
					}
					//Do this once a successful key is found, so the user can get as many password change attempts as needed.
					$rl->delete(); //Clear password reset rate limit upon successful reset.
				} else {
					Debug::Text('DID NOT FIND Valid Password reset key!', __FILE__, __LINE__, __METHOD__, 10);
					$validator->isTrue('password', FALSE, TTi18n::getText('Password reset key is invalid, please try resetting your password again.') );
				}
			} else {
				Debug::Text('DID NOT FIND Valid Password reset key! (b)', __FILE__, __LINE__, __METHOD__, 10);
				$validator->isTrue('password', FALSE, TTi18n::getText('Password reset key is invalid, please try resetting your password again.') .' (b)' );
			}

			Debug::text('Password Reset Failed! Attempt: '. $rl->getAttempts(), __FILE__, __LINE__, __METHOD__, 10);
			sleep( ($rl->getAttempts() * 0.5) ); //If email is incorrect, sleep for some time to slow down brute force attacks.
		}

		return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), array('error' => $validator->getErrorsArray()), array('total_records' => 1, 'valid_records' => 0) );
	}

	/**
	 * Ping function is also in APIMisc for when the session timesout is valid.
	 * Ping no longer can tell if the session is timed-out, must use "isLoggedIn(FALSE)" instead.
	 * @return bool
	 */
	function Ping() {
		return TRUE;
	}
}
?>
