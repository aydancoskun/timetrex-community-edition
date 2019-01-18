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
 * @package Core
 */
class Authentication {
	protected $name = 'SessionID';
	protected $idle = 14400; //Max IDLE time
	protected $expire_session; //When TRUE, cookie is expired when browser closes.
	protected $type_id = 800; //USER_NAME
	protected $object_id = NULL;
	protected $session_id = NULL;
	protected $ip_address = NULL;
	protected $created_date = NULL;
	protected $updated_date = NULL;

	protected $obj = NULL;

	/**
	 * Authentication constructor.
	 */
	function __construct() {
		global $db;

		$this->db = $db;

		$this->rl = TTNew('RateLimit');
		$this->rl->setID( 'authentication_'. Misc::getRemoteIPAddress() );
		$this->rl->setAllowedCalls( 20 );
		$this->rl->setTimeFrame( 900 ); //15 minutes

		return TRUE;
	}

	/**
	 * @param int $type_id
	 * @return bool|mixed
	 */
	function getNameByTypeId( $type_id ) {
		if ( !is_numeric( $type_id ) ) {
			$type_id = $this->getTypeIDByName( $type_id );
		}

		//Seperate session cookie names so if the user logs in with QuickPunch it doesn't log them out of the full interface for example.
		$map = array(
						100 => 'SessionID-JA', //Job Applicant
						110 => 'SessionID-CC', //Client Contact

						500 => 'SessionID-HW',
						510 => 'SessionID-HW',
						520 => 'SessionID-HW',

						600 => 'SessionID-QP', //QuickPunch
						610 => 'SessionID-PC', //ClientPC

						700 => 'SessionID',
						710 => 'SessionID',
						800 => 'SessionID',
						810 => 'SessionID',
					);

		if ( isset($map[$type_id]) ) {
			return $map[$type_id];
		}

		return FALSE;
	}

	/**
	 * @param bool $type_id
	 * @return bool|mixed
	 */
	function getName( $type_id = FALSE ) {
		if ( $type_id == '' ) {
			$type_id = $this->getType();
		}
		return $this->getNameByTypeId( $type_id );
		//return $this->name;
	}

	//Determine if the session type is for an actual user, so we know if we can create audit logs.

	/**
	 * @param bool $type_id
	 * @return bool
	 */
	function isUser( $type_id = FALSE ) {
		if ( $type_id == '' ) {
			$type_id = $this->getType();
		}

		//If this is updated, modify PurgeDatabase.class.php for authentication table as well.
		if ( in_array( $type_id, array( 100, 110 ) ) ) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * @param $type
	 * @return bool|int
	 */
	function getTypeIDByName( $type ) {
		$type = strtolower( $type );

		//SmallINT datatype, max of 32767
		$map = array(
					//
					//Non-Users.
					//
					'job_applicant' => 100,
					'client_contact' => 110,

					//
					//Users
					//

					//Other hardware.
					'ibutton' => 500,
					'barcode' => 510,
					'finger_print' => 520,

					//QuickPunch
					//'phone_id' => 600,
					'phone_id' => 800, //Make Phone_ID same as user_name for now, as that is how it used to work and this causes problems with the Mobile App.
					'quick_punch_id' => 600,
					'client_pc' => 610,

					//SSO or alternitive methods
					'http_auth' => 700,
					'sso' => 710,

					//Username/Passwords including two factor.
					'user_name' => 800,
					'user_name_two_factor' => 810,
					);

		if ( isset($map[$type]) ) {
			return (int)$map[$type];
		}

		return FALSE;
	}

	/**
	 * @return int
	 */
	function getType() {
		return $this->type_id;
	}

	/**
	 * @param int $type_id
	 * @return bool
	 */
	function setType( $type_id) {
		if ( !is_numeric( $type_id ) ) {
			$type_id = $this->getTypeIDByName( $type_id );
		}

		if ( is_int($type_id) ) {
			$this->type_id = $type_id;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return null
	 */
	function getIPAddress() {
		return $this->ip_address;
	}

	/**
	 * @param null $ip_address
	 * @return bool
	 */
	function setIPAddress( $ip_address = NULL) {
		if (empty( $ip_address ) ) {
			$ip_address = Misc::getRemoteIPAddress();
		}

		if ( !empty($ip_address) ) {
			$this->ip_address = $ip_address;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return int
	 */
	function getIdle() {
		//Debug::text('Idle Seconds Allowed: '. $this->idle, __FILE__, __LINE__, __METHOD__, 10);
		return $this->idle;
	}

	/**
	 * @param $secs
	 * @return bool
	 */
	function setIdle( $secs) {
		if ( is_int($secs) ) {
			$this->idle = $secs;

			return TRUE;
		}

		return FALSE;
	}

	//Expire Session when browser is closed?
	function getEnableExpireSession() {
		return $this->expire_session;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableExpireSession( $bool) {
		$this->expire_session = (bool)$bool;
		return TRUE;
	}

	/**
	 * @return null
	 */
	function getCreatedDate() {
		return $this->created_date;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setCreatedDate( $epoch = NULL) {
		if ( $epoch == '' ) {
			$epoch = TTDate::getTime();
		}

		if ( is_numeric($epoch) ) {
			$this->created_date = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return null
	 */
	function getUpdatedDate() {
		return $this->updated_date;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setUpdatedDate( $epoch = NULL) {
		if ( $epoch == '' ) {
			$epoch = TTDate::getTime();
		}

		if ( is_numeric($epoch) ) {
			$this->updated_date = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Duplicates existing session with a new SessionID. Useful for multiple logins with the same or different users.
	 * @param string $object_id UUID
	 * @param null $ip_address
	 * @return null
	 */
	function newSession( $object_id = NULL, $ip_address = NULL ) {
		if ( $object_id == '' AND $this->getObjectID() != '' ) {
			$object_id = $this->getObjectID();
		}

		$new_session_id = $this->genSessionID();
		Debug::text('Duplicating session to User ID: '. $object_id .' Original SessionID: '. $this->getSessionID() .' New Session ID: '. $new_session_id .' IP Address: '. $ip_address, __FILE__, __LINE__, __METHOD__, 10);

		$authentication = new Authentication();
		$authentication->setType( $this->getType() );
		$authentication->setSessionID( $new_session_id );
		$authentication->setIPAddress( $ip_address );
		$authentication->setCreatedDate();
		$authentication->setUpdatedDate();
		$authentication->setObjectID( $object_id );

		//Sets session cookie.
		//$authentication->setCookie();

		//Write data to db.
		$authentication->Write();

		//$authentication->UpdateLastLoginDate(); //Don't do this when switching users.

		return $authentication->getSessionID();
	}

	/**
	 * @param string $object_id UUID
	 * @return bool
	 * @throws DBError
	 */
	function changeObject( $object_id) {
		$this->getObjectById( $object_id );

		$ph = array(
					'object_id' => TTUUID::castUUID($object_id),
					'session_id' => $this->encryptSessionID( $this->getSessionID() ),
					);

		$query = 'UPDATE authentication SET object_id = ? WHERE session_id = ?';

		try {
			$this->db->Execute($query, $ph);
		} catch (Exception $e) {
			throw new DBError($e);
		}

		return TRUE;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function getObjectByID( $id ) {
		if ( empty($id) ) {
			return FALSE;
		}

		if ( $this->isUser() ) {
			$ulf = TTnew( 'UserListFactory' );
			$ulf->getByID( $id );
			if ( $ulf->getRecordCount() == 1 ) {
				$retval = $ulf->getCurrent();
			}
		}

		if ( $this->getType() === 100 ) {
			$jalf = TTnew( 'JobApplicantListFactory' );
			$jalf->getByID( $id );
			if ( $jalf->getRecordCount() == 1 ) {
				$retval = $jalf->getCurrent();
			}
		}

		if ( isset($retval) AND is_object($retval) ) {
			return $retval;
		}

		return FALSE;
	}

	/**
	 * @return bool|null
	 */
	function getObject() {
		if ( is_object($this->obj) ) {
			return $this->obj;
		}

		return FALSE;
	}

	/**
	 * @param $object
	 * @return bool
	 */
	function setObject( $object) {
		if ( is_object( $object ) ) {
			$this->obj = $object;
			return TRUE;
		}

		return FALSE;
		/*
		if ( !empty($object_id) ) {
			$ulf = TTnew( 'UserListFactory' );
			$ulf->getByID($object_id);
			if ( $ulf->getRecordCount() == 1 ) {
				foreach ($ulf as $user) {
					$this->obj = $user;

					return TRUE;
				}
			}
		}

		return FALSE;
		*/
	}

	/**
	 * @return null
	 */
	function getObjectID() {
		return $this->object_id;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setObjectID( $id) {
		$id = TTUUID::castUUID($id);
		if ( $id != '' ) {
			$this->object_id = $id;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return mixed
	 */
	function getSecureSessionID() {
		return substr_replace( $this->getSessionID(), '...', (int)( strlen( $this->getSessionID() ) / 3 ), (int)( strlen( $this->getSessionID() ) / 3 ) );
	}

	//#2238 - Encrypt SessionID with private SALT before writing/reading SessionID in database.
	// This adds an additional protection layer against session stealing if a SQL injection attack is ever discovered.
	// It prevents someone from being able to enumerate over the SessionIDs in the table and use them for nafarious purposes.
	/**
	 * @param string $session_id UUID
	 * @return string
	 */
	function encryptSessionID( $session_id ) {
		$retval = sha1( $session_id . TTPassword::getPasswordSalt() );

		return $retval;
	}

	/**
	 * @return null
	 */
	function getSessionID() {
		return $this->session_id;
	}

	/**
	 * @param string $session_id UUID
	 * @return bool
	 */
	function setSessionID( $session_id) {
		$validator = new Validator;
		$session_id = $validator->stripNonAlphaNumeric( $session_id );

		if (!empty( $session_id ) ) {
			$this->session_id = $session_id;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return string
	 */
	private function genSessionID() {
		//return sha1( uniqid( dechex( mt_rand() ), TRUE ) );
		return sha1( Misc::getUniqueID() );
	}

	/**
	 * @param bool $type_id
	 * @return bool
	 */
	private function setCookie( $type_id = FALSE ) {
		if ( $this->getSessionID() != '' ) {
			$cookie_expires = ( time() + 7776000 ); //90 Days
			if ( $this->getEnableExpireSession() === TRUE ) {
				$cookie_expires = 0; //Expire when browser closes.
			}
			Debug::text('Cookie Expires: '. $cookie_expires .' Path: '. Environment::getCookieBaseURL(), __FILE__, __LINE__, __METHOD__, 10);

			//15-Jun-2016: This should be not be needed anymore as it has been around for several years now.
			//setcookie( $this->getName(), NULL, ( time() + 9999999 ), Environment::getBaseURL(), NULL, Misc::isSSL( TRUE ) ); //Delete old directory cookie as it can cause a conflict if it stills exists.

			//Upon successful login to a cloud hosted server, set the URL to a cookie that can be read from the upper domain to help get the user back to the proper login URL later.
			if ( DEPLOYMENT_ON_DEMAND == TRUE AND DEMO_MODE == FALSE ) {
				setcookie( 'LoginURL', Misc::getURLProtocol() .'://'.Misc::getHostName().Environment::getBaseURL(), ( time() + 9999999 ), '/', '.'.Misc::getHostNameWithoutSubDomain( Misc::getHostName( FALSE ) ), FALSE ); //Delete old directory cookie as it can cause a conflict if it stills exists.
			}

			//Set cookie in root directory so other interfaces can access it.
			setcookie( $this->getName(), $this->getSessionID(), $cookie_expires, Environment::getCookieBaseURL(), NULL, Misc::isSSL( TRUE ) );

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	private function destroyCookie() {
		setcookie( $this->getName(), NULL, ( time() + 9999999 ), Environment::getCookieBaseURL(), NULL, Misc::isSSL( TRUE ) );

		return TRUE;
	}

	/**
	 * @return bool
	 * @throws DBError
	 */
	private function UpdateLastLoginDate() {
		$ph = array(
					'last_login_date' => TTDate::getTime(),
					'object_id' => TTUUID::castUUID($this->getObjectID()),
					);

		$query = 'UPDATE users SET last_login_date = ? WHERE id = ?';

		try {
			$this->db->Execute($query, $ph);
		} catch (Exception $e) {
			throw new DBError($e);
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	private function Update() {
		$ph = array(
					'updated_date' => TTDate::getTime(),
					'session_id' => $this->encryptSessionID( $this->getSessionID() ),
					);

		$query = 'UPDATE authentication SET updated_date = ? WHERE session_id = ?';

		try {
			$this->db->Execute($query, $ph); //This can cause SQL error: "could not serialize access due to concurrent update" when in READ COMMITTED mode.
		} catch (Exception $e) {
			//Ignore any serialization errors, as its not a big deal anyways.
			Debug::text('WARNING: SQL query failed, likely due to transaction isolotion: '. $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10);
			//throw new DBError($e);
		}

		return TRUE;
	}

	/**
	 * @return bool
	 * @throws DBError
	 */
	private function Delete() {
		$ph = array(
					'session_id' => $this->encryptSessionID( $this->getSessionID() ),
					);

		//Can't use IdleTime here, as some users have different idle times.
		//Assume none are longer then one day though.
		$query = 'DELETE FROM authentication WHERE session_id = ? OR (updated_date - created_date) > '. (86400 * 2) .' OR ('. TTDate::getTime() .' - updated_date) > 86400';

		try {
			$this->db->Execute($query, $ph);
		} catch (Exception $e) {
			throw new DBError($e);
		}

		return TRUE;
	}

	/**
	 * @return bool
	 * @throws DBError
	 */
	private function Write() {
		$ph = array(
					'session_id' => $this->encryptSessionID( $this->getSessionID() ),
					'type_id' => (int)$this->getType(),
					'object_id' => TTUUID::castUUID($this->getObjectID()),
					'ip_address' => $this->getIPAddress(),
					'created_date' => $this->getCreatedDate(),
					'updated_date' => $this->getUpdatedDate()
					);

		$query = 'INSERT INTO authentication (session_id, type_id, object_id, ip_address, created_date, updated_date) VALUES( ?, ?, ?, ?, ?, ? )';
		try {
			$this->db->Execute($query, $ph);
		} catch (Exception $e) {
			throw new DBError($e);
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	private function Read() {
		$ph = array(
					'session_id' => $this->encryptSessionID( $this->getSessionID() ),
					//'ip_address' => $this->getIPAddress(),
					'type_id' => (int)$this->getType(),
					'updated_date' => ( TTDate::getTime() - $this->getIdle() ),
					);

		//Need to handle IP addresses changing during the session.
		//When using SSL, don't check for IP address changing at all as we use secure cookies.
		//When *not* using SSL, always require the same IP address for the session.
		//However we need to still allow multiple sessions for the same user, using different IPs.
		$query = 'SELECT type_id, session_id, object_id, ip_address, created_date, updated_date FROM authentication WHERE session_id = ? AND type_id = ? AND updated_date >= ?';

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);
		$result = $this->db->GetRow($query, $ph);

		if ( count($result) > 0) {
			if ( PRODUCTION == TRUE AND $result['ip_address'] != $this->getIPAddress() ) {
				Debug::text('WARNING: IP Address has changed for existing session... Original IP: '. $result['ip_address'] .' Current IP: '. $this->getIPAddress() .' isSSL: '. (int)Misc::isSSL( TRUE ), __FILE__, __LINE__, __METHOD__, 10);
				//When using SSL, we don't care if the IP address has changed, as the session should still be secure.
				//This allows sessions to work across load balancing routers, or between mobile/wifi connections, which can change 100% of the IP address (so close matches are useless anyways)
				if ( Misc::isSSL( TRUE ) != TRUE ) {
					//When not using SSL there is no 100% method of preventing session hijacking, so just insist that IP addresses match exactly as its as close as we can get.
					Debug::text('Not using SSL, IP addresses must match exactly...', __FILE__, __LINE__, __METHOD__, 10);
					return FALSE;
				}
			}
			$this->setType($result['type_id']);
			$this->setSessionID( $this->getSessionID() ); //Make sure this is *not* the encrypted session_id
			$this->setIPAddress($result['ip_address']);
			$this->setCreatedDate($result['created_date']);
			$this->setUpdatedDate($result['updated_date']);
			$this->setObjectID($result['object_id']);

			if ( $this->setObject( $this->getObjectById( $this->getObjectID() ) ) ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getHTTPAuthenticationUsername() {
		$user_name = FALSE;
		if ( isset($_SERVER['PHP_AUTH_USER']) AND $_SERVER['PHP_AUTH_USER'] != '' ) {
			$user_name = $_SERVER['PHP_AUTH_USER'];
		} elseif ( isset($_SERVER['REMOTE_USER']) AND $_SERVER['REMOTE_USER'] != '' ) {
			$user_name = $_SERVER['REMOTE_USER'];
		}

		return $user_name;
	}

	function HTTPAuthenticationHeader() {
		global $config_vars;
		if ( isset($config_vars['other']['enable_http_authentication']) AND $config_vars['other']['enable_http_authentication'] == 1
				AND isset($config_vars['other']['enable_http_authentication_prompt']) AND $config_vars['other']['enable_http_authentication_prompt'] == 1 ) {
			header('WWW-Authenticate: Basic realm="'. APPLICATION_NAME .'"');
			header('HTTP/1.0 401 Unauthorized');
			echo TTi18n::getText('ERROR: A valid username/password is required to access this application. Press refresh in your web browser to try again.');
			Debug::writeToLog();
			exit;
		}
	}

	//Allow web server to handle authentication with Basic Auth/LDAP/SSO/AD, etc...

	/**
	 * @return bool
	 */
	function loginHTTPAuthentication() {
		$user_name = self::getHTTPAuthenticationUsername();

		global $config_vars;
		if ( isset($config_vars['other']['enable_http_authentication']) AND $config_vars['other']['enable_http_authentication'] == 1 AND $user_name != '' ) {
			//Debug::Arr($_SERVER, 'Server vars: ', __FILE__, __LINE__, __METHOD__, 10);
			if ( isset($_SERVER['PHP_AUTH_PW']) AND $_SERVER['PHP_AUTH_PW'] != '' ) {
				Debug::Text('Handling HTTPAuthentication with password.', __FILE__, __LINE__, __METHOD__, 10);
				return $this->Login( $user_name, $_SERVER['PHP_AUTH_PW'], 'USER_NAME' );
			} else {
				Debug::Text('Handling HTTPAuthentication without password.', __FILE__, __LINE__, __METHOD__, 10);
				return $this->Login( $user_name, 'HTTP_AUTH', 'HTTP_AUTH' );
			}
		} elseif( $user_name != '' )  {
			Debug::Text('HTTPAuthentication is passing username: '. $user_name .' however enable_http_authentication is not enabled.', __FILE__, __LINE__, __METHOD__, 10);
		}

		return FALSE;
	}

	/**
	 * @param $user_name
	 * @param $password
	 * @param string $type
	 * @return bool
	 * @throws DBError
	 */
	function Login( $user_name, $password, $type = 'USER_NAME' ) {
		//DO NOT lowercase username, because iButton values are case sensitive.
		$user_name = html_entity_decode( trim($user_name) );
		$password = html_entity_decode( $password );

		//Checks user_name/password.. However password is blank for iButton/Fingerprints often so we can't check that.
		if ( $user_name == '' ) {
			return FALSE;
		}

		$type = strtolower($type);
		Debug::text('Login Type: '. $type, __FILE__, __LINE__, __METHOD__, 10);
		try {
			//Prevent brute force attacks by IP address.
			//Allowed up to 20 attempts in a 30 min period.
			if ( $this->rl->check() == FALSE ) {
				Debug::Text('Excessive failed password attempts... Preventing login from: '. Misc::getRemoteIPAddress() .' for up to 15 minutes...', __FILE__, __LINE__, __METHOD__, 10);
				sleep(5); //Excessive password attempts, sleep longer.
				return FALSE;
			}

			$uf = new UserFactory();
			if ( preg_match( $uf->username_validator_regex, $user_name ) === 0 ) { //This helps prevent invalid byte sequences on unicode strings.
				Debug::Text('Username doesnt match regex: '. $user_name, __FILE__, __LINE__, __METHOD__, 10);
				return FALSE; //No company by that user name.
			}
			unset($uf);

			switch ( $type ) {
				case 'user_name':
					if ( $password == '' ) {
						return FALSE;
					}

					if ( $this->checkCompanyStatus( $user_name ) == 10 ) { //Active
						//Lowercase regular user_names here only.
						$password_result = $this->checkPassword( $user_name, $password);
					} else {
						$password_result = FALSE; //No company by that user name.
					}
					break;
				case 'phone_id': //QuickPunch ID/Password
				case 'quick_punch_id':
					$password_result = $this->checkPhonePassword($user_name, $password);
					break;
				case 'ibutton':
					$password_result = $this->checkIButton($user_name);
					break;
				case 'barcode':
					$password_result = $this->checkBarcode($user_name, $password);
					break;
				case 'finger_print':
					$password_result = $this->checkFingerPrint( $user_name );
					break;
				case 'client_pc':
					//This is for client application persistent connections, use:
					//Login Type: client_pc
					//Station Type: PC

					$password_result = FALSE;

					//StationID must be set on the URL
					if ( isset($_GET['StationID']) AND $_GET['StationID'] != '' ) {
						$slf = new StationListFactory();
						$slf->getByStationID( $_GET['StationID'] );
						if ( $slf->getRecordCount() == 1 ) {
							$station_obj = $slf->getCurrent();
							if ( $station_obj->getStatus() == 20 ) { //Enabled
								$uilf = new UserIdentificationListFactory();
								$uilf->getByCompanyIdAndTypeId( $station_obj->getCompany(), array( 1 ) ); //1=Employee Sequence number.
								if ( $uilf->getRecordCount() > 0 ) {
									foreach( $uilf as $ui_obj ) {
										if ( (int)$ui_obj->getValue() == (int)$user_name ) {
											//$password_result = $this->checkClientPC( $user_name );
											$password_result = $this->checkBarcode( $ui_obj->getUser(), $password);
										}
									}
								} else {
									Debug::text('UserIdentification match failed: '. $user_name, __FILE__, __LINE__, __METHOD__, 10);
								}
							} else {
								Debug::text('Station is DISABLED: '. $station_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);
							}
						} else {
							Debug::text('StationID not specifed on URL or not found...', __FILE__, __LINE__, __METHOD__, 10);
						}
					}
					break;
				case 'http_auth':
					if ( $this->checkCompanyStatus( $user_name ) == 10 ) { //Active
						//Lowercase regular user_names here only.
						$password_result = $this->checkUsername( $user_name );
					} else {
						$password_result = FALSE; //No company by that user name.
					}
					break;
				case 'job_applicant':
					$password_result = $this->checkApplicantPassword($user_name, $password);
					break;
				default:
					return FALSE;
			}

			if ( $password_result === TRUE ) {
				$this->setType( $type );
				$this->setSessionID( $this->genSessionID() );
				$this->setIPAddress();
				$this->setCreatedDate();
				$this->setUpdatedDate();

				//Sets session cookie.
				$this->setCookie();

				//Write data to db.
				$this->Write();

				Debug::text('Login Succesful for User Name: '. $user_name .' Session ID: Cookie: '. $this->getSessionID() .' DB: '. $this->encryptSessionID( $this->getSessionID() ), __FILE__, __LINE__, __METHOD__, 10);

				//Only update last_login_date when using user_name to login to the web interface.
				if ( $type == 'user_name' ) {
					$this->UpdateLastLoginDate();
				}

				//Truncate SessionID for security reasons, so someone with access to the audit log can't steal sessions.
				if ( $this->isUser() == TRUE ) {
					TTLog::addEntry( $this->getObjectID(), 100, TTi18n::getText('SourceIP').': '. $this->getIPAddress() .' '. TTi18n::getText('Type').': '. $type .' '.	TTi18n::getText('SessionID') .': '. $this->getSecureSessionID() .' '.	TTi18n::getText('ObjectID').': '. $this->getObjectID(), $this->getObjectID(), 'authentication'); //Login
				}

				$this->rl->delete(); //Clear failed password rate limit upon successful login.

				return TRUE;
			}

			Debug::text('Login Failed! Attempt: '. $this->rl->getAttempts(), __FILE__, __LINE__, __METHOD__, 10);

			sleep( ($this->rl->getAttempts() * 0.5) ); //If password is incorrect, sleep for some time to slow down brute force attacks.
		} catch (Exception $e) {
			//Database not initialized, or some error, redirect to Install page.
			throw new DBError($e, 'DBInitialize');
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function Logout() {
		$this->destroyCookie();
		$this->Delete();

		if ( $this->isUser() == TRUE ) {
			TTLog::addEntry( $this->getObjectID(), 110, TTi18n::getText('SourceIP').': '. $this->getIPAddress() .' '.  TTi18n::getText('SessionID').': '. $this->getSecureSessionID() .' '. TTi18n::getText('ObjectID').': '. $this->getObjectID(), $this->getObjectID(), 'authentication');
		}

		return TRUE;
	}

	/**
	 * @param string $session_id UUID
	 * @param string $type
	 * @param bool $touch_updated_date
	 * @return bool
	 * @throws DBError
	 */
	function Check( $session_id = NULL, $type = 'USER_NAME', $touch_updated_date = TRUE ) {
		global $profiler;
		$profiler->startTimer( "Authentication::Check()");

		//Debug::text('Session Name: '. $this->getName(), __FILE__, __LINE__, __METHOD__, 10);

		//Support session_ids passed by cookie, post, and get.
		if ( $session_id == '' ) {
			$session_name = $this->getName( $type );

			//There appears to be a bug with Flex when uploading files (upload_file.php) that sometimes the browser sends an out-dated sessionID in the cookie
			//that differs from the sessionID sent in the POST variable. This causes a Flex I/O error because TimeTrex thinks the user isn't authenticated.
			//To fix this check to see if BOTH a COOKIE and POST variable contain SessionIDs, and if so use the POST one.
			if ( ( isset($_COOKIE[$session_name]) AND $_COOKIE[$session_name] != '' ) AND ( isset($_POST[$session_name]) AND $_POST[$session_name] != '' ) ) {
				$session_id = $_POST[$session_name];
			} elseif ( isset($_COOKIE[$session_name]) AND $_COOKIE[$session_name] != '' ) {
				$session_id = $_COOKIE[$session_name];
			} elseif ( isset($_POST[$session_name]) AND $_POST[$session_name] != '' ) {
				$session_id = $_POST[$session_name];
			} elseif ( isset($_GET[$session_name]) AND $_GET[$session_name] != '' ) {
				$session_id = $_GET[$session_name];
			} else {
				$session_id = FALSE;
			}
		}

		Debug::text('Session ID: '. $session_id .' IP Address: '. Misc::getRemoteIPAddress() .' URL: '. $_SERVER['REQUEST_URI'] .' Touch Updated Date: '. (int)$touch_updated_date, __FILE__, __LINE__, __METHOD__, 10);
		//Checks session cookie, returns object_id;
		if ( isset( $session_id ) ) {
			/*
				Bind session ID to IP address to aid in preventing session ID theft,
				if this starts to cause problems
				for users behind load balancing proxies, allow them to choose to
				bind session IDs to just the first 1-3 quads of their IP address
				as well as the SHA1 of their user-agent string.
				Could also use "behind proxy IP address" if one is supplied.
			*/
			try {
				$this->setType( $type );
				$this->setSessionID( $session_id );
				$this->setIPAddress();

				if ( $this->Read() == TRUE ) {
					//touch UpdatedDate in most cases, however when calling PING() we don't want to do this.
					if ( $touch_updated_date !== FALSE ) {
						//Reduce contention and traffic on the session table by only touching the updated_date every 60 +/- rand() seconds.
						//Especially helpful for things like the dashboard that trigger many async calls.
						if ( ( time() - $this->getUpdatedDate() ) > ( 60 + rand( 0, 60 ) ) ) {
							Debug::text('  Touching updated date due to more than 60s...', __FILE__, __LINE__, __METHOD__, 10);
							$this->Update();
						}
					}

					$profiler->stopTimer( "Authentication::Check()");
					return TRUE;
				}
			} catch (Exception $e) {
				//Database not initialized, or some error, redirect to Install page.
				throw new DBError($e, 'DBInitialize');
			}
		}

		$profiler->stopTimer( "Authentication::Check()");

		return FALSE;
	}

	//When company status changes, logout all users for the company.

	/**
	 * @param string $company_id UUID
	 * @return bool
	 * @throws DBError
	 */
	function logoutCompany( $company_id ) {
		//MySQL fails with many of these queries due to recently changed syntax in a point release, disable purging when using MySQL for now.
		//http://bugs.mysql.com/bug.php?id=27525
		if ( strncmp($this->db->databaseType, 'mysql', 5) == 0 ) {
			return FALSE;
		}

		$ph = array(
					'company_id' => TTUUID::castUUID($company_id),
					'type_id' => (int)$this->getTypeIDByName( 'USER_NAME' ),
					);

		$query = 'DELETE FROM authentication as a USING users as b WHERE a.object_id = b.id AND b.company_id = ? AND a.type_id = ?';

		try {
			Debug::text('Logging out entire company ID: '. $company_id, __FILE__, __LINE__, __METHOD__, 10);
			$this->db->Execute($query, $ph);
		} catch (Exception $e) {
			throw new DBError($e);
		}

		return TRUE;
	}

	//When user resets password, logout all sessions for that user.

	/**
	 * @param string $object_id UUID
	 * @return bool
	 * @throws DBError
	 */
	function logoutUser( $object_id ) {
		$ph = array(
					'object_id' => TTUUID::castUUID($object_id),
					'type_id' => (int)$this->getTypeIDByName( 'USER_NAME' ),
					);

		$query = 'DELETE FROM authentication WHERE object_id = ? AND type_id = ?';

		try {
			Debug::text('Logging out all user sessions: '. $object_id, __FILE__, __LINE__, __METHOD__, 10);
			$this->db->Execute($query, $ph);
		} catch (Exception $e) {
			throw new DBError($e);
		}

		return TRUE;
	}

	//
	//Functions to help check crendentials.
	//
	/**
	 * @param $user_name
	 * @return bool
	 */
	function checkCompanyStatus( $user_name ) {
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getByUserName( strtolower($user_name) );
		if ( $ulf->getRecordCount() == 1 ) {
			$u_obj = $ulf->getCurrent();
			if ( is_object($u_obj) ) {
				$clf = TTnew( 'CompanyListFactory' );
				$clf->getById( $u_obj->getCompany() );
				if ( $clf->getRecordCount() == 1 ) {
					//Return the actual status so we can do multiple checks.
					Debug::text('Company Status: '. $clf->getCurrent()->getStatus(), __FILE__, __LINE__, __METHOD__, 10);
					return $clf->getCurrent()->getStatus();
				}

			}
		}

		return FALSE;
	}

	//Checks just the username, used in conjunction with HTTP Authentication/SSO.

	/**
	 * @param $user_name
	 * @return bool
	 */
	function checkUsername( $user_name ) {
		//Use UserFactory to set name.
		$ulf = TTnew( 'UserListFactory' );

		$ulf->getByUserNameAndStatus( $user_name, 10 ); //Active
		foreach ($ulf as $user) {
			if ( strtolower( $user->getUsername() ) == strtolower(trim($user_name)) ) {
				$this->setObjectID( $user->getID() );
				$this->setObject( $user );

				return TRUE;
			} else {
				return FALSE;
			}
		}

		return FALSE;
	}

	/**
	 * @param $user_name
	 * @param $password
	 * @return bool
	 */
	function checkPassword( $user_name, $password) {
		//Use UserFactory to set name.
		$ulf = TTnew( 'UserListFactory' );

		$ulf->getByUserNameAndStatus( $user_name, 10 ); //Active

		foreach ($ulf as $user) {
			if ( $user->checkPassword($password) ) {
				$this->setObjectID( $user->getID() );
				$this->setObject( $user );

				return TRUE;
			} else {
				return FALSE;
			}
		}

		return FALSE;
	}

	/**
	 * @param int $phone_id
	 * @param $password
	 * @return bool
	 */
	function checkPhonePassword( $phone_id, $password) {
		//Use UserFactory to set name.
		$ulf = TTnew( 'UserListFactory' );

		$ulf->getByPhoneIdAndStatus($phone_id, 10 );

		foreach ($ulf as $user) {
			if ( $user->checkPhonePassword($password) ) {
				$this->setObjectID( $user->getID() );
				$this->setObject( $user );

				return TRUE;
			} else {
				return FALSE;
			}
		}

		return FALSE;
	}

	/**
	 * @param $user_name
	 * @param $password
	 * @return bool
	 */
	function checkApplicantPassword( $user_name, $password) {
		$ulf = TTnew( 'JobApplicantListFactory' );

		$ulf->getByUserName( $user_name );

		foreach ($ulf as $user) {
			if ( $user->checkPassword( $password ) ) {
				$this->setObjectID( $user->getID() );
				$this->setObject( $user );

				return TRUE;
			} else {
				return FALSE;
			}
		}

		return FALSE;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function checkIButton( $id) {
		$uilf = TTnew( 'UserIdentificationListFactory' );
		$uilf->getByTypeIdAndValue(10, $id);
		if ( $uilf->getRecordCount() > 0 ) {
			foreach( $uilf as $ui_obj ) {
				if ( is_object( $ui_obj->getUserObject() ) AND $ui_obj->getUserObject()->getStatus() == 10 ) {
					$this->setObjectID( $ui_obj->getUser() );
					$this->setObject( $ui_obj->getUserObject() );
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	/**
	 * @param string $object_id UUID
	 * @param $employee_number
	 * @return bool
	 */
	function checkBarcode( $object_id, $employee_number) {
		//Use UserFactory to set name.
		$ulf = TTnew( 'UserListFactory' );

		$ulf->getByIdAndStatus($object_id, 10 );

		foreach ($ulf as $user) {
			if ( $user->checkEmployeeNumber($employee_number) ) {
				$this->setObjectID( $user->getID() );
				$this->setObject( $user );

				return TRUE;
			} else {
				return FALSE;
			}
		}

		return FALSE;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function checkFingerPrint( $id) {
		$ulf = TTnew( 'UserListFactory' );

		$ulf->getByIdAndStatus($id, 10 );

		foreach ($ulf as $user) {
			if ( $user->getId() == $id ) {
				$this->setObjectID( $user->getID() );
				$this->setObject( $user );

				return TRUE;
			} else {
				return FALSE;
			}
		}

		return FALSE;
	}

	/**
	 * @param $user_name
	 * @return bool
	 */
	function checkClientPC( $user_name) {
		//Use UserFactory to set name.
		$ulf = TTnew( 'UserListFactory' );

		$ulf->getByUserNameAndStatus(strtolower($user_name), 10 );

		foreach ($ulf as $user) {
			if ( $user->getUserName() == $user_name ) {
				$this->setObjectID( $user->getID() );
				$this->setObject( $user );

				return TRUE;
			} else {
				return FALSE;
			}
		}

		return FALSE;
	}

}
?>
