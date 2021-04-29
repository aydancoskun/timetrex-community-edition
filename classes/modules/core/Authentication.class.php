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


/**
 * @package Core
 */
class Authentication {
	protected $name = 'SessionID';
	protected $idle_timeout = null; //Max IDLE time
	protected $expire_session;      //When TRUE, cookie is expired when browser closes.
	protected $type_id = 800;       //USER_NAME
	protected $end_point_id = null;
	protected $client_id = null;
	protected $object_id = null;
	protected $session_id = null;
	protected $ip_address = null;
	protected $user_agent = null;
	protected $flags = null;
	protected $created_date = null;
	protected $updated_date = null;

	protected $obj = null;

	/**
	 * Authentication constructor.
	 */
	function __construct() {
		global $db;

		$this->db = $db;

		$this->rl = TTNew( 'RateLimit' );
		$this->rl->setID( 'authentication_' . Misc::getRemoteIPAddress() );
		$this->rl->setAllowedCalls( 20 );
		$this->rl->setTimeFrame( 900 ); //15 minutes

		return true;
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
		$map = [
				100 => 'SessionID-JA', //Job Applicant
				110 => 'SessionID-CC', //Client Contact

				500 => 'SessionID-HW',
				510 => 'SessionID-HW',
				520 => 'SessionID-HW',

				600 => 'SessionID-QP', //QuickPunch - Web Browser
				605 => 'SessionID',    //QuickPunch - Phone ID (Mobile App expects SessionID)
				610 => 'SessionID-PC', //ClientPC

				700 => 'SessionID',
				705 => 'SessionID',
				710 => 'SessionID',
				800 => 'SessionID',
				810 => 'SessionID',
		];

		if ( isset( $map[$type_id] ) ) {
			return $map[$type_id];
		}

		return false;
	}

	/**
	 * @param bool $type_id
	 * @return bool|mixed
	 */
	function getName( $type_id = false ) {
		if ( $type_id == '' ) {
			$type_id = $this->getType();
		}

		return $this->getNameByTypeId( $type_id );
		//return $this->name;
	}

	/**
	 * Determine if the session type is for an actual user, so we know if we can create audit logs.
	 * @param bool $type_id
	 * @return bool
	 */
	function isUser( $type_id = false ) {
		if ( $type_id == '' ) {
			$type_id = $this->getType();
		}

		//If this is updated, modify PurgeDatabase.class.php for authentication table as well.
		if ( in_array( $type_id, [ 100, 110 ] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @param $type
	 * @return bool|int
	 */
	function getTypeIDByName( $type ) {
		$type = strtolower( $type );

		//SmallINT datatype, max of 32767
		$map = [
			//
			//Non-Users.
			//
			'job_applicant'        => 100,
			'client_contact'       => 110,

			//
			//Users
			//

			//Other hardware.
			'ibutton'              => 500,
			'barcode'              => 510,
			'finger_print'         => 520,

			//QuickPunch
			'quick_punch_id'       => 600,
			'phone_id'             => 605, //This used to have to be 800 otherwise the Desktop PC app and touch-tone AGI scripts would fail, however that should be resolved now with changes to soap/server.php
			'client_pc'            => 610,

			'api_key'              => 700, //API key created after user_name/password authentication. This should be below any methods that use user_name/password to authenticate each time they login.

			//SSO or alternative methods
			'http_auth'            => 705,
			'sso'                  => 710,

			//Username/Passwords including two factor.
			'user_name'            => 800,
			'user_name_two_factor' => 810,
		];

		if ( isset( $map[$type] ) ) {
			return (int)$map[$type];
		}

		return false;
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
	function setType( $type_id ) {
		if ( !is_numeric( $type_id ) ) {
			$type_id = $this->getTypeIDByName( $type_id );
		}

		if ( is_int( $type_id ) ) {
			$this->type_id = $type_id;

			return true;
		}

		return false;
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
	function setIPAddress( $ip_address = null ) {
		if ( empty( $ip_address ) ) {
			$ip_address = Misc::getRemoteIPAddress();
		}

		if ( !empty( $ip_address ) ) {
			$this->ip_address = $ip_address;

			return true;
		}

		return false;
	}

	/**
	 * @return int
	 */
	function getIdleTimeout() {
		if ( $this->idle_timeout == null ) {
			global $config_vars;
			if ( isset( $config_vars['other']['web_session_timeout'] ) && $config_vars['other']['web_session_timeout'] != '' ) {
				$this->idle_timeout = (int)$config_vars['other']['web_session_timeout'];
			} else {
				$this->idle_timeout = 14400; //Default to 4-hours.
			}
		}

		Debug::text( 'Idle Seconds Allowed: ' . $this->idle_timeout, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->idle_timeout;
	}

	/**
	 * @param $secs
	 * @return bool
	 */
	function setIdleTimeout( $secs ) {
		if ( $secs != '' && is_int( $secs ) ) {
			$this->idle_timeout = $secs;

			return true;
		}

		return false;
	}

	/**
	 * @param null $end_point_id
	 * @return mixed|string
	 */
	function parseEndPointID( $end_point_id = null ) {
		if ( $end_point_id == null && isset( $_SERVER['SCRIPT_NAME'] ) && $_SERVER['SCRIPT_NAME'] != '' ) {
			$end_point_id = $_SERVER['SCRIPT_NAME'];
		}

		$end_point_id = Environment::stripDuplicateSlashes( $end_point_id );

		//If the SCRIPT_NAME is something like upload_file.php, or APIGlobal.js.php, assume its the JSON API
		// soap/server.php is a SOAP end-point.
		//   This is also set in parseEndPointID() and getClientIDHeader()
		//   /api/json/api.php should be: json/api
		//   /api/soap/api.php should be: soap/api
		//   /api/report/api.php should be: report/api
		//   /soap/server.php should be: soap/server
		//   See MiscTest::testAuthenticationParseEndPoint() for unit tests.
		if ( $end_point_id == '' || ( strpos( $end_point_id, 'api' ) === false && strpos( $end_point_id, 'soap/server.php' ) === false ) ) {
			$retval = 'json/api';
		} else {
			$retval = Environment::stripDuplicateSlashes( str_replace( [ dirname( Environment::getAPIBaseURL() ) . '/', '.php' ], '', $end_point_id ) );
		}

		$retval = strtolower( trim( $retval, '/' ) ); //Strip leading and trailing slashes.
		//Debug::text('End Point: '. $retval .' Input: '. $value .' API Base URL: '. Environment::getAPIBaseURL(), __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	/**
	 * @return string
	 */
	function getEndPointID() {
		if ( $this->end_point_id == null ) {
			$this->end_point_id = $this->parseEndPointID();
		}

		return $this->end_point_id;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEndPointID( $value ) {
		if ( $value != '' ) {
			$this->end_point_id = substr( $value, 0, 30 );

			return true;
		}

		return false;
	}

	/**
	 * @return string
	 */
	function getClientID() {
		if ( $this->client_id == null ) {
			$this->client_id = strtolower( $this->getClientIDHeader() );
		}

		return $this->client_id;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setClientID( $value ) {
		if ( $value != '' ) {
			$this->client_id = strtolower( substr( $value, 0, 30 ) );

			return true;
		}

		return false;
	}

	/**
	 * @return string
	 */
	function getUserAgent() {
		if ( $this->user_agent == null ) {
			$this->user_agent = sha1( ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : null ) . TTPassword::getPasswordSalt() ); //Hash the user agent so its not as long.
		}

		return $this->user_agent;
	}

	/**
	 * @param $value
	 * @param bool $hash
	 * @return bool
	 */
	function setUserAgent( $value, $hash = false ) {
		if ( $value != '' ) {
			if ( $hash == true ) {
				$value = sha1( $value . TTPassword::getPasswordSalt() ); //Hash the user agent so its not as long.
			}

			$this->user_agent = substr( $value, 0, 40 );

			return true;
		}

		return false;
	}

	//Expire Session when browser is closed?
	function getEnableExpireSession() {
		return $this->expire_session;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableExpireSession( $bool ) {
		$this->expire_session = (bool)$bool;

		return true;
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
	function setCreatedDate( $epoch = null ) {
		if ( $epoch == '' ) {
			$epoch = TTDate::getTime();
		}

		if ( is_numeric( $epoch ) ) {
			$this->created_date = $epoch;

			return true;
		}

		return false;
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
	function setUpdatedDate( $epoch = null ) {
		if ( $epoch == '' ) {
			$epoch = TTDate::getTime();
		}

		if ( is_numeric( $epoch ) ) {
			$this->updated_date = $epoch;

			return true;
		}

		return false;
	}

	/**
	 * Register permanent API key Session ID to be used for all subsequent API calls without needing a username/password.
	 * @param string $user_name
	 * @param string $password
	 * @return bool|string
	 * @throws DBError
	 */
	function registerAPIKey( $user_name, $password ) {
		$login_result = $this->Login( $user_name, $password, 'USER_NAME' ); //Make sure login succeeds before generating API key.
		if ( $login_result === true ) {
			Debug::text( 'Creating API Key session for User ID: ' . $this->getObjectID() . ' Original SessionID: ' . $this->getSessionID(), __FILE__, __LINE__, __METHOD__, 10 );
			$authentication = new Authentication();
			$authentication->setType( 700 ); //API Key
			$authentication->setSessionID( 'API'. $this->genSessionID() );
			$authentication->setIPAddress();

			if ( $this->getEndPointID() == 'json/api' || $this->getEndPointID() == 'soap/api' ) {
				$authentication->setEndPointID( $this->getEndPointID() ); //json/api, soap/api
			}
			$authentication->setClientID( 'api' );
			$authentication->setUserAgent( 'API KEY' ); //Force the same user agent for all API keys, as its very likely could change across time as these are long-lived keys.
			$authentication->setIdleTimeout( ( 90 * 86400 ) ); //90 Days of inactivity.
			$authentication->setCreatedDate();
			$authentication->setUpdatedDate();
			$authentication->setObjectID( $this->getObjectID() );

			//Write data to db.
			$authentication->Write();

			TTLog::addEntry( $this->getObjectID(), 10, TTi18n::getText( 'Registered API Key' ) . ': ' .  $authentication->getSecureSessionID() . ' ' . TTi18n::getText( 'End Point' ) . ': ' . $authentication->getEndPointID(), $this->getObjectID(), 'authentication' ); //Add

			return $authentication->getSessionID();
		}

		Debug::text( 'Password match failed, unable to create API Key session for User ID: ' . $this->getObjectID() . ' Original SessionID: ' . $this->getSessionID(), __FILE__, __LINE__, __METHOD__, 10 );
		return false;
	}

	/**
	 * Duplicates existing session with a new SessionID. Useful for multiple logins with the same or different users.
	 * @param string $object_id UUID
	 * @param string $ip_address
	 * @param string $user_agent
	 * @param string $client_id UUID
	 * @param string $end_point_id
	 * @param null $type_id
	 * @return null
	 * @throws DBError
	 */
	function newSession( $object_id = null, $ip_address = null, $user_agent = null, $client_id = null, $end_point_id = null, $type_id = null ) {
		if ( $object_id == '' && $this->getObjectID() != '' ) {
			$object_id = $this->getObjectID();
		}

		if ( $type_id == null ) {
			$type_id = $this->getType();
		}

		//Allow switching from type_id=700 (API Key) to 800 (username/password) so we can impersonate across API key to browser.
		if ( !( ( $this->getType() == 700 || $this->getType() == 800 ) && ( $type_id == 700 || $type_id == 800 ) ) ) {
			Debug::text( ' ERROR: Invalid from/to Type IDs! From Type: ' . $this->getType() . ' To Type: '. $type_id, __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		}

		$new_session_id = $this->genSessionID();
		Debug::text( 'Duplicating session to User ID: ' . $object_id . ' Original SessionID: ' . $this->getSessionID() . ' New Session ID: ' . $new_session_id . ' IP Address: ' . $ip_address . ' Type: ' . $type_id . ' End Point: ' . $end_point_id . ' Client ID: ' . $client_id . ' DB: ' . $this->encryptSessionID( $new_session_id ), __FILE__, __LINE__, __METHOD__, 10 );

		$authentication = new Authentication();
		$authentication->setType( $type_id );
		$authentication->setSessionID( $new_session_id );
		$authentication->setIPAddress( $ip_address );
		$authentication->setEndPointID( $end_point_id );
		$authentication->setClientID( $client_id );
		$authentication->setUserAgent( $user_agent, true ); //Force hash the user agent.
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
	function changeObject( $object_id ) {
		$this->getObjectById( $object_id );

		$ph = [
				'object_id'  => TTUUID::castUUID( $object_id ),
				'session_id' => $this->encryptSessionID( $this->getSessionID() ),
		];

		$query = 'UPDATE authentication SET object_id = ? WHERE session_id = ?';

		try {
			$this->db->Execute( $query, $ph );
		} catch ( Exception $e ) {
			throw new DBError( $e );
		}

		return true;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function getObjectByID( $id ) {
		if ( empty( $id ) ) {
			return false;
		}

		if ( $this->isUser() ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$ulf->getByID( $id );
			if ( $ulf->getRecordCount() == 1 ) {
				$retval = $ulf->getCurrent();
			}
		}

		if ( $this->getType() === 100 ) {
			$jalf = TTnew( 'JobApplicantListFactory' ); /** @var JobApplicantListFactory $jalf */
			$jalf->getByID( $id );
			if ( $jalf->getRecordCount() == 1 ) {
				$retval = $jalf->getCurrent();
			}
		}

		if ( isset( $retval ) && is_object( $retval ) ) {
			return $retval;
		}

		return false;
	}

	/**
	 * @return bool|null
	 */
	function getObject() {
		if ( is_object( $this->obj ) ) {
			return $this->obj;
		}

		return false;
	}

	/**
	 * @param $object
	 * @return bool
	 */
	function setObject( $object ) {
		if ( is_object( $object ) ) {
			$this->obj = $object;

			return true;
		}

		return false;
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
	function setObjectID( $id ) {
		$id = TTUUID::castUUID( $id );
		if ( $id != '' ) {
			$this->object_id = $id;

			return true;
		}

		return false;
	}

	/**
	 * @return mixed
	 */
	function getSecureSessionID() {
		return substr_replace( $this->getSessionID(), '...', 7, (int)( strlen( $this->getSessionID() ) - 11 ) );
	}

	/**
	 * #2238 - Encrypt SessionID with private SALT before writing/reading SessionID in database.
	 * This adds an additional protection layer against session stealing if a SQL injection attack is ever discovered.
	 * It prevents someone from being able to enumerate over the SessionIDs in the table and use them for nafarious purposes.
	 * @param string $session_id UUID
	 * @return string
	 */
	function encryptSessionID( $session_id ) {
		$retval = sha1( $session_id . TTPassword::getPasswordSalt() );

		return $retval;
	}

	/**
	 * @return string|null
	 */
	function getSessionID() {
		return $this->session_id;
	}

	/**
	 * @param string $session_id UUID
	 * @return bool
	 */
	function setSessionID( $session_id ) {
		$validator = new Validator;
		$session_id = $validator->stripNonAlphaNumeric( $session_id );

		if ( !empty( $session_id ) ) {
			$this->session_id = $session_id;

			return true;
		}

		return false;
	}

	/**
	 * @return string
	 */
	private function genSessionID() {
		return sha1( Misc::getUniqueID() );
	}

	/**
	 * @param bool $type_id
	 * @return bool
	 */
	private function setCookie( $type_id = false ) {
		if ( $this->getSessionID() != '' ) {
			$cookie_expires = ( time() + 7776000 ); //90 Days
			if ( $this->getEnableExpireSession() === true ) {
				$cookie_expires = 0; //Expire when browser closes.
			}
			Debug::text( 'Cookie Expires: ' . $cookie_expires . ' Path: ' . Environment::getCookieBaseURL(), __FILE__, __LINE__, __METHOD__, 10 );

			//15-Jun-2016: This should be not be needed anymore as it has been around for several years now.
			//setcookie( $this->getName(), NULL, ( time() + 9999999 ), Environment::getBaseURL(), NULL, Misc::isSSL( TRUE ) ); //Delete old directory cookie as it can cause a conflict if it stills exists.

			//Upon successful login to a cloud hosted server, set the URL to a cookie that can be read from the upper domain to help get the user back to the proper login URL later.
			if ( DEPLOYMENT_ON_DEMAND == true && DEMO_MODE == false ) {
				setcookie( 'LoginURL', Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getBaseURL(), ( time() + 9999999 ), '/', '.' . Misc::getHostNameWithoutSubDomain( Misc::getHostName( false ) ), false ); //Delete old directory cookie as it can cause a conflict if it stills exists.
			}

			//Set cookie in root directory so other interfaces can access it.
			setcookie( $this->getName(), $this->getSessionID(), $cookie_expires, Environment::getCookieBaseURL(), null, Misc::isSSL( true ) );

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	private function destroyCookie() {
		setcookie( $this->getName(), null, ( time() + 9999999 ), Environment::getCookieBaseURL(), null, Misc::isSSL( true ) );

		return true;
	}

	/**
	 * @return bool
	 * @throws DBError
	 */
	private function UpdateLastLoginDate() {
		$ph = [
				'last_login_date' => TTDate::getTime(),
				'object_id'       => TTUUID::castUUID( $this->getObjectID() ),
		];

		$query = 'UPDATE users SET last_login_date = ? WHERE id = ?';

		try {
			$this->db->Execute( $query, $ph );
		} catch ( Exception $e ) {
			throw new DBError( $e );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	private function Update() {
		$ph = [
				'updated_date' => TTDate::getTime(),
				'session_id'   => $this->encryptSessionID( $this->getSessionID() ),
		];

		$query = 'UPDATE authentication SET updated_date = ? WHERE session_id = ?';

		try {
			$this->db->Execute( $query, $ph ); //This can cause SQL error: "could not serialize access due to concurrent update" when in READ COMMITTED mode.
		} catch ( Exception $e ) {
			//Ignore any serialization errors, as its not a big deal anyways.
			Debug::text( 'WARNING: SQL query failed, likely due to transaction isolotion: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
			//throw new DBError($e);
		}

		return true;
	}

	/**
	 * @return bool
	 * @throws DBError
	 */
	private function Delete() {
		$ph = [
				'session_id' => $this->encryptSessionID( $this->getSessionID() ),
		];

		$query = 'DELETE FROM authentication WHERE session_id = ? OR (' . TTDate::getTime() . ' - updated_date) > idle_timeout';

		try {
			$this->db->Execute( $query, $ph );
		} catch ( Exception $e ) {
			throw new DBError( $e );
		}

		return true;
	}

	/**
	 * @return bool
	 * @throws DBError
	 */
	private function Write() {
		$ph = [
				'session_id'   => $this->encryptSessionID( $this->getSessionID() ),
				'type_id'      => (int)$this->getType(),
				'object_id'    => TTUUID::castUUID( $this->getObjectID() ),
				'ip_address'   => $this->getIPAddress(),
				'idle_timeout' => $this->getIdleTimeout(),
				'end_point_id' => $this->getEndPointID(),
				'client_id'    => $this->getClientID(),
				'user_agent'   => $this->getUserAgent(),
				'created_date' => $this->getCreatedDate(),
				'updated_date' => $this->getUpdatedDate(),
		];

		$query = 'INSERT INTO authentication (session_id, type_id, object_id, ip_address, idle_timeout, end_point_id, client_id, user_agent, created_date, updated_date) VALUES( ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )';
		try {
			$this->db->Execute( $query, $ph );
		} catch ( Exception $e ) {
			throw new DBError( $e );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	private function Read() {
		$ph = [
				'session_id'   => $this->encryptSessionID( $this->getSessionID() ),
				'type_id'      => (int)$this->getType(),
				'end_point_id' => $this->getEndPointID(),
				'client_id'    => $this->getClientID(),
				'updated_date' => TTDate::getTime(),
		];

		//Need to handle IP addresses changing during the session.
		//When using SSL, don't check for IP address changing at all as we use secure cookies.
		//When *not* using SSL, always require the same IP address for the session.
		//However we need to still allow multiple sessions for the same user, using different IPs.
		$query = 'SELECT type_id, session_id, object_id, ip_address, idle_timeout, end_point_id, client_id, user_agent, created_date, updated_date FROM authentication WHERE session_id = ? AND type_id = ? AND end_point_id = ? AND client_id = ? AND updated_date >= ( ? - idle_timeout )';
		$result = $this->db->GetRow( $query, $ph );
		//Debug::Query($query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		if ( count( $result ) > 0 ) {
			if ( PRODUCTION == true && $result['ip_address'] != $this->getIPAddress() ) {
				Debug::text( 'WARNING: IP Address has changed for existing session... Original IP: ' . $result['ip_address'] . ' Current IP: ' . $this->getIPAddress() . ' isSSL: ' . (int)Misc::isSSL( true ), __FILE__, __LINE__, __METHOD__, 10 );
				//When using SSL, we don't care if the IP address has changed, as the session should still be secure.
				//This allows sessions to work across load balancing routers, or between mobile/wifi connections, which can change 100% of the IP address (so close matches are useless anyways)
				if ( Misc::isSSL( true ) != true ) {
					//When not using SSL there is no 100% method of preventing session hijacking, so just insist that IP addresses match exactly as its as close as we can get.
					Debug::text( 'Not using SSL, IP addresses must match exactly...', __FILE__, __LINE__, __METHOD__, 10 );

					return false;
				}
			}

			//Only check user agent if we know its a web-browser, and definitely not when its an API or Mobile App, as the user agent may change between SOAP/REST libraries or App versions.
			if ( $result['client_id'] == 'browser-timetrex' && $result['user_agent'] != $this->getUserAgent() ) {
				Debug::text( 'WARNING: User Agent changed! Original: ' . $result['user_agent'] . ' Current: ' . $this->getUserAgent(), __FILE__, __LINE__, __METHOD__, 10 );
				return FALSE; //Disable USER AGENT checking until v12 is fully released, and end-user have a chance to update their APIs to handle passing the user agent if using switchUser() or newSession()
			}

			$this->setType( $result['type_id'] );
			$this->setIdleTimeout( $result['idle_timeout'] );
			$this->setEndPointID( $result['end_point_id'] );
			$this->setClientID( $result['client_id'] );
			$this->setUserAgent( $result['user_agent'] );
			$this->setSessionID( $this->getSessionID() ); //Make sure this is *not* the encrypted session_id
			$this->setIPAddress( $result['ip_address'] );
			$this->setCreatedDate( $result['created_date'] );
			$this->setUpdatedDate( $result['updated_date'] );
			$this->setObjectID( $result['object_id'] );

			if ( $this->setObject( $this->getObjectById( $this->getObjectID() ) ) ) {
				return true;
			}
		} else {
			Debug::text( 'Session ID not found in the DB... End Point: ' . $this->getEndPointID() . ' Client ID: ' . $this->getClientID() . ' Type: ' . $this->getType(), __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getHTTPAuthenticationUsername() {
		$user_name = false;
		if ( isset( $_SERVER['PHP_AUTH_USER'] ) && $_SERVER['PHP_AUTH_USER'] != '' ) {
			$user_name = $_SERVER['PHP_AUTH_USER'];
		} else if ( isset( $_SERVER['REMOTE_USER'] ) && $_SERVER['REMOTE_USER'] != '' ) {
			$user_name = $_SERVER['REMOTE_USER'];
		}

		return $user_name;
	}

	function HTTPAuthenticationHeader() {
		global $config_vars;
		if ( isset( $config_vars['other']['enable_http_authentication'] ) && $config_vars['other']['enable_http_authentication'] == 1
				&& isset( $config_vars['other']['enable_http_authentication_prompt'] ) && $config_vars['other']['enable_http_authentication_prompt'] == 1 ) {
			header( 'WWW-Authenticate: Basic realm="' . APPLICATION_NAME . '"' );
			header( 'HTTP/1.0 401 Unauthorized' );
			echo TTi18n::getText( 'ERROR: A valid username/password is required to access this application. Press refresh in your web browser to try again.' );
			Debug::writeToLog();
			exit;
		}
	}

	/**
	 * Allow web server to handle authentication with Basic Auth/LDAP/SSO/AD, etc...
	 * @return bool
	 */
	function loginHTTPAuthentication() {
		$user_name = self::getHTTPAuthenticationUsername();

		global $config_vars;
		if ( isset( $config_vars['other']['enable_http_authentication'] ) && $config_vars['other']['enable_http_authentication'] == 1 && $user_name != '' ) {
			//Debug::Arr($_SERVER, 'Server vars: ', __FILE__, __LINE__, __METHOD__, 10);
			if ( isset( $_SERVER['PHP_AUTH_PW'] ) && $_SERVER['PHP_AUTH_PW'] != '' ) {
				Debug::Text( 'Handling HTTPAuthentication with password.', __FILE__, __LINE__, __METHOD__, 10 );

				return $this->Login( $user_name, $_SERVER['PHP_AUTH_PW'], 'USER_NAME' );
			} else {
				Debug::Text( 'Handling HTTPAuthentication without password.', __FILE__, __LINE__, __METHOD__, 10 );

				return $this->Login( $user_name, 'HTTP_AUTH', 'HTTP_AUTH' );
			}
		} else if ( $user_name != '' ) {
			Debug::Text( 'HTTPAuthentication is passing username: ' . $user_name . ' however enable_http_authentication is not enabled.', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
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
		$user_name = html_entity_decode( trim( $user_name ) );
		$password = html_entity_decode( trim( $password ) );

		//Checks user_name/password.. However password is blank for iButton/Fingerprints often so we can't check that.
		if ( $user_name == '' ) {
			return false;
		}

		$type = strtolower( $type );
		Debug::text( 'Login Type: ' . $type, __FILE__, __LINE__, __METHOD__, 10 );
		try {
			//Prevent brute force attacks by IP address.
			//Allowed up to 20 attempts in a 30 min period.
			if ( $this->rl->check() == false ) {
				Debug::Text( 'Excessive failed password attempts... Preventing login from: ' . Misc::getRemoteIPAddress() . ' for up to 15 minutes...', __FILE__, __LINE__, __METHOD__, 10 );
				sleep( 5 ); //Excessive password attempts, sleep longer.

				return false;
			}

			$uf = new UserFactory();
			if ( preg_match( $uf->username_validator_regex, $user_name ) === 0 ) { //This helps prevent invalid byte sequences on unicode strings.
				Debug::Text( 'Username doesnt match regex: ' . $user_name, __FILE__, __LINE__, __METHOD__, 10 );

				return false; //No company by that user name.
			}
			unset( $uf );

			switch ( $type ) {
				case 'user_name':
					if ( $password == '' ) {
						return false;
					}

					if ( $this->checkCompanyStatus( $user_name ) == 10 ) { //Active
						//Lowercase regular user_names here only.
						$password_result = $this->checkPassword( $user_name, $password );
					} else {
						$password_result = false; //No company by that user name.
					}
					break;
				case 'phone_id': //QuickPunch ID/Password
				case 'quick_punch_id':
					$password_result = $this->checkPhonePassword( $user_name, $password );
					break;
				case 'ibutton':
					$password_result = $this->checkIButton( $user_name );
					break;
				case 'barcode':
					$password_result = $this->checkBarcode( $user_name, $password );
					break;
				case 'finger_print':
					$password_result = $this->checkFingerPrint( $user_name );
					break;
				case 'client_pc':
					//This is for client application persistent connections, use:
					//Login Type: client_pc
					//Station Type: PC

					$password_result = false;

					//StationID must be set on the URL
					if ( isset( $_GET['StationID'] ) && $_GET['StationID'] != '' ) {
						$slf = new StationListFactory();
						$slf->getByStationID( $_GET['StationID'] );
						if ( $slf->getRecordCount() == 1 ) {
							$station_obj = $slf->getCurrent();
							if ( $station_obj->getStatus() == 20 ) { //Enabled
								$uilf = new UserIdentificationListFactory();
								$uilf->getByCompanyIdAndTypeId( $station_obj->getCompany(), [ 1 ] ); //1=Employee Sequence number.
								if ( $uilf->getRecordCount() > 0 ) {
									foreach ( $uilf as $ui_obj ) {
										if ( (int)$ui_obj->getValue() == (int)$user_name ) {
											//$password_result = $this->checkClientPC( $user_name );
											$password_result = $this->checkBarcode( $ui_obj->getUser(), $password );
										}
									}
								} else {
									Debug::text( 'UserIdentification match failed: ' . $user_name, __FILE__, __LINE__, __METHOD__, 10 );
								}
							} else {
								Debug::text( 'Station is DISABLED... UUID: ' . $station_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
							}
						} else {
							Debug::text( 'StationID not specifed on URL or not found...', __FILE__, __LINE__, __METHOD__, 10 );
						}
					}
					break;
				case 'http_auth':
					if ( $this->checkCompanyStatus( $user_name ) == 10 ) { //Active
						//Lowercase regular user_names here only.
						$password_result = $this->checkUsername( $user_name );
					} else {
						$password_result = false; //No company by that user name.
					}
					break;
				case 'job_applicant':
					$company_obj = $this->getCompanyObject( $user_name, 'JOB_APPLICANT' );
					if ( is_object( $company_obj ) && $company_obj->getProductEdition() == 25 && $company_obj->getStatus() == 10 ) { //Active
						$password_result = $this->checkApplicantPassword( $user_name, $password );
					} else {
						Debug::text( 'ERROR: Company is not active or incorrect product edition...', __FILE__, __LINE__, __METHOD__, 10 );
						$password_result = false; //No company by that user name.
					}
					unset( $company_obj );
					break;
				default:
					return false;
			}

			if ( $password_result === true ) {
				$this->setType( $type );
				$this->setSessionID( $this->genSessionID() );
				$this->setIPAddress();
				$this->setCreatedDate();
				$this->setUpdatedDate();

				//Sets session cookie.
				$this->setCookie();

				//Write data to db.
				$this->Write();

				Debug::text( 'Login Succesful for User Name: ' . $user_name . ' End Point ID: ' . $this->getEndPointID() . ' Client ID: ' . $this->getClientID() . ' Type: ' . $type . ' Session ID: Cookie: ' . $this->getSessionID() . ' DB: ' . $this->encryptSessionID( $this->getSessionID() ), __FILE__, __LINE__, __METHOD__, 10 );

				//Only update last_login_date when using user_name to login to the web interface.
				if ( $type == 'user_name' ) {
					$this->UpdateLastLoginDate();
				}

				//Truncate SessionID for security reasons, so someone with access to the audit log can't steal sessions.
				if ( $this->isUser() == true ) {
					TTLog::addEntry( $this->getObjectID(), 100, TTi18n::getText( 'SourceIP' ) . ': ' . $this->getIPAddress() . ' ' . TTi18n::getText( 'Type' ) . ': ' . $type . ' ' . TTi18n::getText( 'SessionID' ) . ': ' . $this->getSecureSessionID() . ' ' . TTi18n::getText( 'Client' ) . ': ' . $this->getClientID() . ' ' . TTi18n::getText( 'End Point' ) . ': ' . $this->getEndPointID() . ' ' . TTi18n::getText( 'ObjectID' ) . ': ' . $this->getObjectID(), $this->getObjectID(), 'authentication' ); //Login
				}

				$this->rl->delete(); //Clear failed password rate limit upon successful login.

				return true;
			}

			Debug::text( 'Login Failed! Attempt: ' . $this->rl->getAttempts(), __FILE__, __LINE__, __METHOD__, 10 );

			sleep( ( $this->rl->getAttempts() * 0.5 ) ); //If password is incorrect, sleep for some time to slow down brute force attacks.
		} catch ( Exception $e ) {
			//Database not initialized, or some error, redirect to Install page.
			throw new DBError( $e, 'DBInitialize' );
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function Logout() {
		$this->destroyCookie();
		$this->Delete();

		if ( $this->isUser() == true ) {
			TTLog::addEntry( $this->getObjectID(), 110, TTi18n::getText( 'SourceIP' ) . ': ' . $this->getIPAddress() . ' ' . TTi18n::getText( 'SessionID' ) . ': ' . $this->getSecureSessionID() . ' ' . TTi18n::getText( 'ObjectID' ) . ': ' . $this->getObjectID(), $this->getObjectID(), 'authentication' );
		}

		return true;
	}

	/**
	 * Gets the current session ID from the COOKIE, POST or GET variables.
	 * @param string $type
	 * @return string|bool
	 */
	function getCurrentSessionID( $type ) {
		$session_name = $this->getName( $type );

		if ( isset( $_COOKIE[$session_name] ) && $_COOKIE[$session_name] != '' ) {
			$session_id = (string)$_COOKIE[$session_name];
		} else if ( isset( $_SERVER[$session_name] ) && $_SERVER[$session_name] != '' ) {
			$session_id = (string)$_SERVER[$session_name];
		} else if ( isset( $_POST[$session_name] ) && $_POST[$session_name] != '' ) {
			$session_id = (string)$_POST[$session_name];
		} else if ( isset( $_GET[$session_name] ) && $_GET[$session_name] != '' ) {
			$session_id = (string)$_GET[$session_name];
		} else {
			$session_id = false;
		}

		Debug::text( 'Session ID: ' . $session_id . ' IP Address: ' . Misc::getRemoteIPAddress() . ' URL: ' . $_SERVER['REQUEST_URI'], __FILE__, __LINE__, __METHOD__, 10 );

		return $session_id;
	}

	/**
	 * @param $session_id
	 * @return bool
	 */
	function isSessionIDAPIKey( $session_id ) {
		if ( $session_id != '' && substr( $session_id, 0, 3 ) == 'API' ) {
			return true;
		}

		return false;
	}

	/**
	 * @param string $session_id UUID
	 * @param string $type
	 * @param bool $touch_updated_date
	 * @return bool
	 * @throws DBError
	 */
	function Check( $session_id = null, $type = null, $touch_updated_date = true ) {
		global $profiler;
		$profiler->startTimer( "Authentication::Check()" );

		if ( $type == '' ) {
			$type = 'USER_NAME';
		}

		//Support session_ids passed by cookie, post, and get.
		if ( $session_id == '' ) {
			$session_id = $this->getCurrentSessionID( $type );
		}

		Debug::text( 'Session ID: ' . $session_id . ' Type: ' . $type . ' IP Address: ' . Misc::getRemoteIPAddress() . ' URL: ' . $_SERVER['REQUEST_URI'] . ' Touch Updated Date: ' . (int)$touch_updated_date, __FILE__, __LINE__, __METHOD__, 10 );
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

				if ( $this->Read() == true ) {
					//touch UpdatedDate in most cases, however when calling PING() we don't want to do this.
					if ( $touch_updated_date !== false ) {
						//Reduce contention and traffic on the session table by only touching the updated_date every 60 +/- rand() seconds.
						//Especially helpful for things like the dashboard that trigger many async calls.
						if ( ( time() - $this->getUpdatedDate() ) > ( 60 + rand( 0, 60 ) ) ) {
							Debug::text( '  Touching updated date due to more than 60s...', __FILE__, __LINE__, __METHOD__, 10 );
							$this->Update();
						}
					}

					$profiler->stopTimer( "Authentication::Check()" );

					return true;
				}
			} catch ( Exception $e ) {
				//Database not initialized, or some error, redirect to Install page.
				throw new DBError( $e, 'DBInitialize' );
			}
		}

		$profiler->stopTimer( "Authentication::Check()" );

		return false;
	}

	/**
	 * When company status changes, logout all users for the company.
	 * @param string $company_id UUID
	 * @return bool
	 * @throws DBError
	 */
	function logoutCompany( $company_id ) {
		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'type_id'    => (int)$this->getTypeIDByName( 'USER_NAME' ),
		];

		$query = 'DELETE FROM authentication as a USING users as b WHERE a.object_id = b.id AND b.company_id = ? AND a.type_id = ?';

		try {
			Debug::text( 'Logging out entire company ID: ' . $company_id, __FILE__, __LINE__, __METHOD__, 10 );
			$this->db->Execute( $query, $ph );
		} catch ( Exception $e ) {
			throw new DBError( $e );
		}

		return true;
	}

	/**
	 * When user resets or changes their password, logout all sessions for that user.
	 * @param string $object_id            UUID
	 * @param string $type_id
	 * @param bool $ignore_current_session Avoid logging out existing session, for example when the user is changing their own password.
	 * @return bool
	 * @throws DBError
	 */
	function logoutUser( $object_id, $type_id = 'USER_NAME', $ignore_current_session = true ) {
		if ( $ignore_current_session == true ) {
			$session_id = $this->encryptSessionID( $this->getCurrentSessionId( $type_id ) );
		} else {
			$session_id = null;
		}

		$ph = [
				'object_id'  => TTUUID::castUUID( $object_id ),
				'type_id'    => (int)$this->getTypeIDByName( $type_id ),
				'session_id' => $session_id,
		];

		$query = 'DELETE FROM authentication WHERE object_id = ? AND type_id = ? AND session_id != ?';

		try {
			$this->db->Execute( $query, $ph );
			//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);
			Debug::text( 'Logging out all sessions for User ID: ' . $object_id . ' Affected Rows: ' . $this->db->Affected_Rows(), __FILE__, __LINE__, __METHOD__, 10 );
		} catch ( Exception $e ) {
			throw new DBError( $e );
		}

		return true;
	}

	//
	//Functions to help check crendentials.
	//

	/**
	 * @param $user_name
	 * @param string $type
	 * @return bool|mixed
	 */
	function getCompanyObject( $user_name, $type = 'USER' ) {
		$type = strtoupper( $type );
		if ( $type == 'USER' ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$ulf->getByUserName( TTi18n::strtolower( $user_name ) );
		} else if ( $type == 'JOB_APPLICANT' ) {
			$ulf = TTnew( 'JobApplicantListFactory' ); /** @var JobApplicantListFactory $ulf */
			$ulf->getByUserName( TTi18n::strtolower( $user_name ) );
		}

		if ( $ulf->getRecordCount() == 1 ) {
			$u_obj = $ulf->getCurrent();
			if ( is_object( $u_obj ) ) {
				$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
				$clf->getById( $u_obj->getCompany() );
				if ( $clf->getRecordCount() == 1 ) {
					return $clf->getCurrent();
				}
			}
		}

		return false;
	}

	/**
	 * @param $user_name
	 * @return bool
	 */
	function checkCompanyStatus( $user_name ) {
		$company_obj = $this->getCompanyObject( $user_name, 'USER' );
		if ( is_object( $company_obj ) ) {
			//Return the actual status so we can do multiple checks.
			Debug::text( 'Company Status: ' . $company_obj->getStatus(), __FILE__, __LINE__, __METHOD__, 10 );

			return $company_obj->getStatus();
		}

		return false;
	}

	/**
	 * Checks just the username, used in conjunction with HTTP Authentication/SSO.
	 * @param $user_name
	 * @return bool
	 */
	function checkUsername( $user_name ) {
		//Use UserFactory to set name.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getByUserNameAndEnableLogin( $user_name, true ); //Login Enabled
		foreach ( $ulf as $user ) {
			if ( TTi18n::strtolower( $user->getUsername() ) == TTi18n::strtolower( trim( $user_name ) ) ) {
				$this->setObjectID( $user->getID() );
				$this->setObject( $user );

				return true;
			} else {
				return false;
			}
		}

		return false;
	}

	/**
	 * @param $user_name
	 * @param $password
	 * @return bool
	 */
	function checkPassword( $user_name, $password ) {
		//Use UserFactory to set name.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getByUserNameAndEnableLogin( $user_name, true ); //Login Enabled
		foreach ( $ulf as $user ) {
			/** @var UserFactory $user */
			if ( $user->checkPassword( $password ) ) {
				$this->setObjectID( $user->getID() );
				$this->setObject( $user );

				return true;
			} else {
				return false;
			}
		}

		return false;
	}

	/**
	 * @param int $phone_id
	 * @param $password
	 * @return bool
	 */
	function checkPhonePassword( $phone_id, $password ) {
		//Use UserFactory to set name.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */

		$ulf->getByPhoneIdAndStatus( $phone_id, 10 );

		foreach ( $ulf as $user ) {
			if ( $user->checkPhonePassword( $password ) ) {
				$this->setObjectID( $user->getID() );
				$this->setObject( $user );

				return true;
			} else {
				return false;
			}
		}

		return false;
	}

	/**
	 * @param $user_name
	 * @param $password
	 * @return bool
	 */
	function checkApplicantPassword( $user_name, $password ) {
		$ulf = TTnew( 'JobApplicantListFactory' ); /** @var JobApplicantListFactory $ulf */

		$ulf->getByUserName( $user_name );

		foreach ( $ulf as $user ) {
			if ( $user->checkPassword( $password ) ) {
				$this->setObjectID( $user->getID() );
				$this->setObject( $user );

				return true;
			} else {
				return false;
			}
		}

		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function checkIButton( $id ) {
		$uilf = TTnew( 'UserIdentificationListFactory' ); /** @var UserIdentificationListFactory $uilf */
		$uilf->getByTypeIdAndValue( 10, $id );
		if ( $uilf->getRecordCount() > 0 ) {
			foreach ( $uilf as $ui_obj ) {
				if ( is_object( $ui_obj->getUserObject() ) && $ui_obj->getUserObject()->getStatus() == 10 ) {
					$this->setObjectID( $ui_obj->getUser() );
					$this->setObject( $ui_obj->getUserObject() );

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @param string $object_id UUID
	 * @param $employee_number
	 * @return bool
	 */
	function checkBarcode( $object_id, $employee_number ) {
		//Use UserFactory to set name.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */

		$ulf->getByIdAndStatus( $object_id, 10 );

		foreach ( $ulf as $user ) {
			if ( $user->checkEmployeeNumber( $employee_number ) ) {
				$this->setObjectID( $user->getID() );
				$this->setObject( $user );

				return true;
			} else {
				return false;
			}
		}

		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function checkFingerPrint( $id ) {
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */

		$ulf->getByIdAndStatus( $id, 10 );

		foreach ( $ulf as $user ) {
			if ( $user->getId() == $id ) {
				$this->setObjectID( $user->getID() );
				$this->setObject( $user );

				return true;
			} else {
				return false;
			}
		}

		return false;
	}

	/**
	 * @param $user_name
	 * @return bool
	 */
	function checkClientPC( $user_name ) {
		//Use UserFactory to set name.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */

		$ulf->getByUserNameAndStatus( TTi18n::strtolower( $user_name ), 10 );

		foreach ( $ulf as $user ) {
			if ( $user->getUserName() == $user_name ) {
				$this->setObjectID( $user->getID() );
				$this->setObject( $user );

				return true;
			} else {
				return false;
			}
		}

		return false;
	}

	/**
	 * Returns the value of the X-Client-ID HTTP header so we can determine what type of front-end we are using and if CSRF checks should be enabled or not.
	 * @return bool|string
	 */
	function getClientIDHeader() {
		if ( isset( $_SERVER['HTTP_X_CLIENT_ID'] ) && $_SERVER['HTTP_X_CLIENT_ID'] != '' ) {
			return trim( $_SERVER['HTTP_X_CLIENT_ID'] );
		} else if ( isset( $_POST['X-Client-ID'] ) && $_POST['X-Client-ID'] != '' ) { //Need to read X-Client-ID from POST variables so Global.APIFileDownload() works.
			return trim( $_POST['X-Client-ID'] );
		} else if ( Misc::isMobileAppUserAgent() == true ) {
			return 'App-TimeTrex';
		} else {
			if ( isset( $_SERVER['SCRIPT_NAME'] ) && $_SERVER['SCRIPT_NAME'] != '' ) {
				$script_name = $_SERVER['SCRIPT_NAME'];

				//If the SCRIPT_NAME is something like upload_file.php, or APIGlobal.js.php, assume its the JSON API
				//   This is also set in parseEndPointID() and getClientIDHeader()
				if ( $script_name == '' || ( strpos( $script_name, 'api' ) === false && strpos( $script_name, 'soap/server.php' ) === false ) ) {
					return 'Browser-TimeTrex';
				}
			}
		}

		return 'API'; //Default to API Client-ID
	}

	/**
	 * Checks that the CSRF token header matches the CSRF token cookie that was originally sent.
	 *   This uses the Cookie-To-Header method explained here: https://en.wikipedia.org/w/index.php?title=Cross-site_request_forgery#Cookie-to-header_token
	 *   Also explained further here: https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html -- "Double Submit Cookie" method.
	 * @return bool
	 */
	function checkValidCSRFToken() {
		global $config_vars;

		$client_id_header = $this->getClientIDHeader();

		if ( $client_id_header != 'API' && $client_id_header != 'App-TimeTrex' && $client_id_header != 'App-TimeTrex-AGI'
				&& ( !isset( $config_vars['other']['enable_csrf_validation'] ) || ( isset( $config_vars['other']['enable_csrf_validation'] ) && $config_vars['other']['enable_csrf_validation'] == true ) )
				&& ( !isset( $config_vars['other']['installer_enabled'] ) || ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] != true ) ) //Disable CSRF if installer is enabled, because TTPassword::getPasswordSalt() has the potential to change at anytime.
		) {
			if ( isset( $_SERVER['HTTP_X_CSRF_TOKEN'] ) && $_SERVER['HTTP_X_CSRF_TOKEN'] != '' ) {
				$csrf_token_header = trim( $_SERVER['HTTP_X_CSRF_TOKEN'] );
			} else {
				if ( isset( $_POST['X-CSRF-Token'] ) && $_POST['X-CSRF-Token'] != '' ) { //Global.APIFileDownload() needs to be able to send the token by POST or GET.
					$csrf_token_header = trim( $_POST['X-CSRF-Token'] );
				} else if ( isset( $_GET['X-CSRF-Token'] ) && $_GET['X-CSRF-Token'] != '' ) { //Some send_file.php calls need to be able to send the token by GET.
					$csrf_token_header = trim( $_GET['X-CSRF-Token'] );
				} else {
					$csrf_token_header = false;
				}
			}

			if ( isset( $_COOKIE['CSRF-Token'] ) && $_COOKIE['CSRF-Token'] != '' ) {
				$csrf_token_cookie = trim( $_COOKIE['CSRF-Token'] );
			} else {
				$csrf_token_cookie = false;
			}

			if ( $csrf_token_header != '' && $csrf_token_header == $csrf_token_cookie ) {
				//CSRF token is hashed with a secret key, so full token is: <TOKEN>-<HASHED WITH SECRET KEY TOKEN> -- Therefore make sure that the hashed token matches with our secret key.
				$split_csrf_token = explode( '-', $csrf_token_header ); //0=Token value, 1=Salted token value.
				if ( is_array( $split_csrf_token ) && count( $split_csrf_token ) == 2 && $split_csrf_token[1] == sha1( $split_csrf_token[0] . TTPassword::getPasswordSalt() ) ) {
					return true;
				} else {
					Debug::Text( ' CSRF token value does not match hashed value! Client-ID: ' . $client_id_header . ' CSRF Token: Header: ' . $csrf_token_header . ' Cookie: ' . $csrf_token_cookie, __FILE__, __LINE__, __METHOD__, 10 );

					return false;
				}
			} else {
				Debug::Text( ' CSRF token does not match! Client-ID: ' . $client_id_header . ' CSRF Token: Header: ' . $csrf_token_header . ' Cookie: ' . $csrf_token_cookie, __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}
		} else {
			return true; //Not a CSRF vulnerable end-point
		}
	}

	/**
	 * Checks refer to help mitigate CSRF attacks.
	 * @param bool $referer
	 * @return bool
	 */
//	static function checkValidReferer( $referer = FALSE ) {
//		global $config_vars;
//
//		if ( PRODUCTION == TRUE AND isset($config_vars['other']['enable_csrf_validation']) AND $config_vars['other']['enable_csrf_validation'] == TRUE ) {
//			if ( $referer == FALSE ) {
//				if ( isset($_SERVER['HTTP_ORIGIN']) AND $_SERVER['HTTP_ORIGIN'] != '' ) {
//					//IE9 doesn't send this, but if it exists use it instead as its likely more trustworthy.
//					//Debug::Text( 'Using Referer from Origin header...', __FILE__, __LINE__, __METHOD__, 10);
//					$referer = $_SERVER['HTTP_ORIGIN'];
//					if ( $referer == 'file://' ) { //Mobile App and some browsers can send the origin as: file://
//						return TRUE;
//					}
//				} elseif ( isset($_SERVER['HTTP_REFERER']) AND $_SERVER['HTTP_REFERER'] != '' ) {
//					Debug::Text( 'WARNING: CSRF check falling back for legacy browser... Referer: '. $_SERVER['HTTP_REFERER'], __FILE__, __LINE__, __METHOD__, 10);
//					$referer = $_SERVER['HTTP_REFERER'];
//				} else {
//					Debug::Text( 'WARNING: No HTTP_ORIGIN or HTTP_REFERER headers specified...', __FILE__, __LINE__, __METHOD__, 10);
//					$referer = '';
//				}
//			}
//
//			//Debug::Text( 'Raw Referer: '. $referer, __FILE__, __LINE__, __METHOD__, 10);
//			$referer = strtolower( parse_url( $referer, PHP_URL_HOST ) ); //Make sure we lowercase it, so case doesn't prevent a match.
//
//			//Use HTTP_HOST rather than getHostName() as the same site can be referenced with multiple different host names
//			//Especially considering on-site installs that default to 'localhost'
//			//If deployment ondemand is set, then we assume SERVER_NAME is correct and revert to using that instead of HTTP_HOST which has potential to be forged.
//			//Apache's UseCanonicalName On configuration directive can help ensure the SERVER_NAME is always correct and not masked.
//			if ( DEPLOYMENT_ON_DEMAND == FALSE AND isset( $_SERVER['HTTP_HOST'] ) ) {
//				$host_name = $_SERVER['HTTP_HOST'];
//			} elseif ( isset( $_SERVER['SERVER_NAME'] ) ) {
//				$host_name = $_SERVER['SERVER_NAME'];
//			} elseif ( isset( $_SERVER['HOSTNAME'] ) ) {
//				$host_name = $_SERVER['HOSTNAME'];
//			} else {
//				$host_name = '';
//			}
//			$host_name = ( $host_name != '' ) ? strtolower( parse_url( 'http://'.$host_name, PHP_URL_HOST ) ) : ''; //Need to add 'http://' so parse_url() can strip it off again. Also lowercase it so case differences don't prevent a match.
//			//Debug::Text( 'Parsed Referer: '. $referer .' Hostname: '. $host_name, __FILE__, __LINE__, __METHOD__, 10);
//
//			if ( $referer == $host_name OR $host_name == '' ) {
//				return TRUE;
//			}
//
//			Debug::Text( 'CSRF check failed... Parsed Referer: '. $referer .' Hostname: '. $host_name, __FILE__, __LINE__, __METHOD__, 10);
//			return FALSE;
//		}
//
//		return TRUE;
//	}
}

?>
