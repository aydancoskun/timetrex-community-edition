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
 * @package API\TimeTrexClientAPI
 */
class TimeTrexClientAPI {
	protected $base_url = 'https://demo.timetrex.com/api/json/api.php';
	protected $session_id = null;
	protected $session_hash = null; //Used to determine if we need to login again because the URL or Session changed.
	protected $class_factory = null;
	protected $class_factory_method = null;

	protected $protocol = null;
	protected $protocol_version = 2;

	protected $namespace = 'urn:api';
	protected $soap_obj = null; //Persistent SOAP object.

	protected $curl_obj = null; //Persistent CURL object.

	/**
	 * TimeTrexClientAPI constructor.
	 * @param null $class
	 * @param null $url
	 * @param string $session_id UUID
	 */
	function __construct( $class = null, $url = null, $session_id = null ) {
		global $TIMETREX_URL;

		if ( class_exists( 'SoapClient' ) == false ) {
			echo "ERROR: PHP SOAP extension is not installed and enabled, please correct this then try again.";
			exit( 255 );
		}

		ini_set( 'default_socket_timeout', 3600 );

		if ( $url == '' ) {
			$url = $TIMETREX_URL;
		}

		if ( $session_id == '' ) {
			$session_id = $this->getSessionID();
		}

		$this->setURL( $url );
		$this->setSessionId( $session_id );
		$this->setClass( $class );

		return true;
	}

	/**
	 * @return bool
	 */
	function __destruct() {
		return true;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setProtocol( $value ) {
		$this->protocol = strtolower( $value );

		return true;
	}

	/**
	 * @return string
	 */
	function getProtocol() {
		return $this->protocol;
	}

	/**
	 * @return SoapClient|Resource
	 */
	function getClientConnectionObject() {
		global $TIMETREX_API_COOKIES;

		$url = $this->buildURL();

		//Default to standard user preferences so its consistent across all logins.
		global $TIMETREX_API_STANDARD_PREFERENCES;
		if ( $TIMETREX_API_STANDARD_PREFERENCES == true ) {
			$TIMETREX_API_COOKIES['OverrideUserPreference'] = json_encode(
					[
							'date_format'      => 'Y-m-d',
							'time_format'      => 'G:i:s T',
							'distance_format'  => 30, //Meters
							'time_zone'        => 'GMT',
							'time_unit_format' => 40, //Seconds
					]
			);
		}

		if ( $this->getProtocol() == 'soap' ) {
			//Try to maintain existing SOAP object as there could be cookies associated with it.
			if ( !is_object( $this->soap_obj ) ) {
				global $TIMETREX_BASIC_AUTH_USER, $TIMETREX_BASIC_AUTH_PASSWORD;

				$retval = new SoapClient( null, [
													  'location'    => $url,
													  'uri'         => $this->namespace,
													  'encoding'    => 'UTF-8',
													  'style'       => SOAP_RPC,
													  'use'         => SOAP_ENCODED,
													  'login'       => $TIMETREX_BASIC_AUTH_USER,
													  'password'    => $TIMETREX_BASIC_AUTH_PASSWORD,
													  //'connection_timeout' => 120,
													  //'request_timeout' => 3600,
													  'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
													  'trace'       => 1,
													  'exceptions'  => 0,
											  ]
				);

				if ( isset( $TIMETREX_API_COOKIES ) && is_array( $TIMETREX_API_COOKIES ) && count( $TIMETREX_API_COOKIES ) > 0 ) {
					foreach ( $TIMETREX_API_COOKIES as $name => $data ) {
						$retval->__setCookie( $name, $data );
					}
					unset( $name, $data );
				}

				//Send SessionID as a cookie rather than on the URL to increase safety just a little bit more.
				//  Overwrite above set SessionID in case we need to force it to something else.
				if ( $this->session_id != '' ) {
					$retval->__setCookie( 'SessionID', $this->session_id );
				}

				$this->soap_obj = $retval;
			} else {
				$retval = $this->soap_obj;
				$retval->__setLocation( $url );
			}
		} else {
			if ( !is_resource( $this->curl_obj ) ) {
				$retval = curl_init();
				//curl_setopt( $retval, CURLOPT_VERBOSE, true );
				curl_setopt( $retval, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
				curl_setopt( $retval, CURLOPT_URL, $url );
				curl_setopt( $retval, CURLOPT_REFERER, $url ); //**IMPORTANT: Referer should always be sent to avoid requests being rejected due to CSRF security checks.
				curl_setopt( $retval, CURLOPT_CONNECTTIMEOUT, 600 );
				curl_setopt( $retval, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $retval, CURLOPT_SSL_VERIFYPEER, false );
				curl_setopt( $retval, CURLOPT_FOLLOWLOCATION, 1 );
				curl_setopt( $retval, CURLOPT_COOKIELIST, null ); //Enables curl_getinfo() to get a list of cookies
				//curl_setopt( $retval, CURLINFO_HEADER_OUT, true ); //Enables "curl_getinfo( $this->curl_obj, CURLINFO_HEADER_OUT )" to show the raw HTTP request for debugging.


				//Send SessionID as a cookie rather than on the URL to increase safety just a little bit more.
				//  Overwrite above set SessionID in case we need to force it to something else.
				if ( $this->session_id != '' ) {
					$TIMETREX_API_COOKIES['SessionID'] = $this->session_id;
				}

				if ( isset( $TIMETREX_API_COOKIES ) && is_array( $TIMETREX_API_COOKIES ) && count( $TIMETREX_API_COOKIES ) > 0 ) {
					foreach ( $TIMETREX_API_COOKIES as $name => $data ) {
						$cookies[] = $name . '=' . $data;
					}
					unset( $name, $data );

					//curl_setopt( $retval, CURLOPT_HTTPHEADER, [ 'Cookie: ' . implode( ';', $cookies ) ] ); //Send API Key as a cookie.
					curl_setopt( $retval, CURLOPT_COOKIE, implode( ';', $cookies ) ); //Send API Key as a cookie. Do not URL encode this, as it breaks it.
					unset( $cookies );
				}

				$this->curl_obj = $retval;
			} else {
				$retval = $this->curl_obj;
				curl_setopt( $retval, CURLOPT_URL, $url );
			}
		}

		return $retval;
	}

	/**
	 * @param $url
	 * @return bool
	 */
	function setURL( $url ) {
		if ( $url != '' ) {
			$this->base_url = $url;

			if ( strpos( $url, '/api/soap/' ) !== false ) {
				$this->setProtocol( 'soap' );
			} else {
				$this->setProtocol( 'json' );
			}

			return true;
		}

		return false;
	}

	/**
	 * @return string
	 */
	function buildURL() {
		$url_pieces[] = 'Class=' . $this->class_factory;
		if ( $this->class_factory_method != '' ) {
			$url_pieces[] = 'Method=' . $this->class_factory_method;
		}

		//TimeTrex v12.1.x and older requires SessionID to still be on the URL for just SOAP API calls, so keep this here for backwards compatibility.
		if ( $this->getProtocol() == 'soap' && $this->session_id != '' ) {
			$url_pieces[] = 'SessionID=' . $this->session_id;
		}

		if ( strpos( $this->base_url, '?' ) === false ) {
			$url_separator = '?';
		} else {
			$url_separator = '&';
		}

		$url = $this->base_url . $url_separator . 'v=' . $this->protocol_version . '&' . implode( '&', $url_pieces );

		return $url;
	}


	/**
	 * @return bool|string
	 */
	function getSessionID() {
		global $TIMETREX_SESSION_ID, $TIMETREX_API_KEY;

		if ( $TIMETREX_API_KEY != '' ) {
			$retval = $TIMETREX_API_KEY;
		} else if ( $TIMETREX_SESSION_ID != '' ) {
			$retval = $TIMETREX_SESSION_ID;
		} else if ( $this->session_id != '' ) {
			$retval = $this->session_id;
		} else {
			$retval = false;
		}

		return $retval;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSessionID( $value ) {
		if ( $value != '' ) {
			global $TIMETREX_SESSION_ID, $TIMETREX_API_KEY;
			$this->session_id = $TIMETREX_SESSION_ID = $TIMETREX_API_KEY = $value;

			return true;
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setClass( $value ) {
		$this->class_factory = trim( $value );

		return true;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMethod( $value ) {
		$this->class_factory_method = trim( $value );

		return true;
	}

	/**
	 * Use the SessionHash to ensure the URL for the session doesn't get changed out from under us without re-logging in.
	 * @param $url
	 * @param string $session_id UUID
	 * @return string
	 */
	function calcSessionHash( $url, $session_id ) {
		return md5( trim( $url ) . trim( $session_id ) );
	}

	/**
	 * @return null
	 */
	function getSessionHash() {
		return $this->session_hash;
	}

	/**
	 * @return bool
	 */
	private function setSessionHash() {
		global $TIMETREX_SESSION_HASH;
		$this->session_hash = $TIMETREX_SESSION_HASH = $this->calcSessionHash( $this->base_url, $this->session_id );

		return true;
	}

	/**
	 * Persist cookies in memory across all HTTP calls.
	 * @return bool
	 */
	private function handleServerCookies() {
		$connection_obj = $this->getClientConnectionObject();
		if ( is_object( $connection_obj ) || is_resource( $connection_obj ) ) {
			global $TIMETREX_API_COOKIES;

			$TIMETREX_API_COOKIES = false; //Clear any existing cookies so they are reset.
			if ( $this->getProtocol() == 'soap' ) {
				if ( isset( $this->getClientConnectionObject()->_cookies ) ) {
					$tmp_cookies = $this->getClientConnectionObject()->_cookies;

					//Format SOAP cookies in an name=>value so its consistent with between SOAP and JSON.
					if ( is_array( $tmp_cookies ) ) {
						foreach ( $tmp_cookies as $name => $data ) {
							$TIMETREX_API_COOKIES[$name] = $data[0];
						}
					}
					unset( $tmp_cookies, $name, $data );
				}
			} else {
				$tmp_cookies = curl_getinfo( $connection_obj, CURLINFO_COOKIELIST );

				//Format JSON cookies in an name=>value so its consistent with between SOAP and JSON.
				if ( is_array( $tmp_cookies ) ) {
					foreach ( $tmp_cookies as $data ) {
						$tmp_cookie = explode( "\t", $data ); //5=Name, 6=Value
						$TIMETREX_API_COOKIES[$tmp_cookie[5]] = $tmp_cookie[6];
					}
				}
				unset( $tmp_cookies, $data, $tmp_cookie );
			}
		}

		return true;
	}

	/**
	 * @param $result
	 * @return mixed
	 */
	function isFault( $result ) {
		return $this->getClientConnectionObject()->is_soap_fault( $result );
	}

	/**
	 * @param $user_name
	 * @param null $password
	 * @param string $type
	 * @return bool
	 */
	function Login( $user_name, $password = null, $type = 'USER_NAME' ) {
		//Check to see if we are currently logged in as the same user already?
		global $TIMETREX_SESSION_ID, $TIMETREX_SESSION_HASH;
		if ( $TIMETREX_SESSION_ID != '' && $TIMETREX_SESSION_HASH == $this->calcSessionHash( $this->base_url, $TIMETREX_SESSION_ID ) ) { //AND $this->isLoggedIn() == TRUE
			//Already logged in, skipping unnecessary new login procedure.
			return true;
		}

		$this->session_id = $this->session_hash = null; //Don't set old session ID on URL.

		$this->setClass( 'Authentication' );
		$retval = $this->call( 'Login', [ $user_name, $password, $type ] );
		if ( is_object( $retval ) && $retval->isValid() ) {
			$retval = $retval->getResult();
			if ( !is_array( $retval ) && $retval != false ) {
				$this->handleServerCookies();

				$this->setSessionID( $retval );
				$this->setSessionHash();

				return true;
			}
		}

		return false;
	}

	/**
	 * @return mixed
	 */
	function isLoggedIn() {
		$old_class = $this->class_factory;
		$this->setClass( 'Authentication' );
		$retval = $this->call( 'isLoggedIn' );

		$this->setClass( $old_class );
		unset( $old_class );

		if ( is_object( $retval ) && $retval->isValid() ) {
			$retval = $retval->getResult();

			return $retval;
		}

		return $retval;
	}

	/**
	 * @param $user_name
	 * @param $password
	 * @return bool
	 */
	function registerAPIKey( $user_name, $password ) {
		$this->session_id = $this->session_hash = null; //Don't set old session ID on URL.

		$this->setClass( 'Authentication' );
		$retval = $this->call( 'registerAPIKey', [ $user_name, $password ] );
		if ( is_object( $retval ) && $retval->isValid() ) {
			$retval = $retval->getResult();
			if ( !is_array( $retval ) && $retval != false ) {
				$this->handleServerCookies();

				$this->setSessionID( $retval );
				$this->setSessionHash();

				return $retval;
			}
		}

		return false;
	}

	/**
	 * @return mixed
	 */
	function Logout() {
		$this->setClass( 'Authentication' );
		$retval = $this->call( 'Logout' );
		if ( is_object( $retval ) && $retval->isValid() ) {
			$retval = $retval->getResult();

			global $TIMETREX_SESSION_ID, $TIMETREX_API_KEY, $TIMETREX_SESSION_HASH, $TIMETREX_API_COOKIES;
			$TIMETREX_SESSION_ID = $TIMETREX_API_KEY = $TIMETREX_SESSION_HASH = $TIMETREX_API_COOKIES = false;
			$this->session_id = $this->session_hash = null;
		}

		return $retval;
	}

	/**
	 * @param $function_name
	 * @param array $args
	 * @return bool|TimeTrexClientAPIReturnHandler
	 */
	function call( $function_name, $args = [] ) {
		$this->setMethod( $function_name );
		$connection_obj = $this->getClientConnectionObject();

		if ( is_object( $connection_obj ) || is_resource( $connection_obj ) ) {
			if ( $this->getProtocol() == 'soap' ) {
				$retval = call_user_func_array( [ $connection_obj, $function_name ], $args );

				if ( is_soap_fault( $retval ) ) {
					trigger_error( 'SOAP Fault: (Code: ' . $retval->faultcode . ', String: ' . $retval->faultstring . ') - Request: ' . $this->getClientConnectionObject()->__getLastRequest() . ' Response: ' . $this->getClientConnectionObject()->__getLastResponse(), E_USER_NOTICE );

					return false;
				}
			} else {
				if ( $args !== null ) {
					$post_data = 'json=' . urlencode( json_encode( $args ) );
					curl_setopt( $connection_obj, CURLOPT_POSTFIELDS, $post_data );
				}

				$retval = json_decode( curl_exec( $connection_obj ), true );
				//curl_close( $connection_obj ); //If the connection is closed, we can't get the cookie information from it.
			}

			return new TimeTrexClientAPIReturnHandler( $function_name, $args, $retval );
		}

		return false;
	}

	/**
	 * @param $function_name
	 * @param array $args
	 * @return bool|TimeTrexClientAPIReturnHandler
	 */
	function __call( $function_name, $args = [] ) {
		return $this->call( $function_name, $args );
	}
}

/**
 * @package API\TimeTrexClientAPI
 */
class TimeTrexClientAPIReturnHandler {
	/*
	'api_retval' => $retval,
	'api_details' => array(
					'code' => $code,
					'description' => $description,
					'record_details' => array(
											'total' => $validator_stats['total_records'],
											'valid' => $validator_stats['valid_records'],
											'invalid' => ($validator_stats['total_records']-$validator_stats['valid_records'])
											),
					'details' =>  $details,
					)
	*/
	protected $function_name = null;
	protected $args = null;
	protected $result_data = false;

	/**
	 * TimeTrexClientAPIReturnHandler constructor.
	 * @param $function_name
	 * @param $args
	 * @param $result_data
	 */
	function __construct( $function_name, $args, $result_data ) {
		$this->function_name = $function_name;
		$this->args = $args;
		$this->result_data = $result_data;

		return true;
	}

	/**
	 * @return bool
	 */
	function getResultData() {
		return $this->result_data;
	}

	/**
	 * @return null
	 */
	function getFunction() {
		return $this->function_name;
	}

	/**
	 * @return null
	 */
	function getArgs() {
		return $this->args;
	}

	/**
	 * @return string
	 */
	function __toString() {
		$eol = "<br>\n";

		$output = [];
		$output[] = '=====================================';
		$output[] = 'Function: ' . $this->getFunction() . '()';
		if ( is_object( $this->getArgs() ) || is_array( $this->getArgs() ) ) {
			$output[] = 'Args: ' . count( $this->getArgs() );
		} else {
			$output[] = 'Args: ' . $this->getArgs();
		}
		$output[] = '-------------------------------------';
		$output[] = 'Returned:';
		$output[] = ( $this->isValid() === true ) ? 'IsValid: YES' : 'IsValid: NO';
		if ( $this->isValid() === true ) {
			if ( is_string( $this->getResult() ) ) {
				$output[] = 'Return Value: ' . $this->getResult();
			} else {
				$output[] = 'Return Value (JSON): ' . json_encode( $this->getResult(), JSON_PRETTY_PRINT );
			}
		} else {
			$output[] = 'Code: ' . $this->getCode();
			$output[] = 'Description: ' . $this->getDescription();
			$output[] = 'Details: ';

			$details = $this->getDetails();
			if ( is_array( $details ) ) {
				foreach ( $details as $row => $detail ) {
					$output[] = 'Row: ' . $row;
					foreach ( $detail as $field => $msgs ) {
						$output[] = '--Field: ' . $field;
						foreach ( $msgs as $key => $msg ) {
							$output[] = '----Message: ' . $msg;
						}
					}
				}
			}
		}
		$output[] = '=====================================';
		$output[] = '';

		return implode( $eol, $output );
	}

	/**
	 * @return bool
	 */
	function isValid() {
		if ( isset( $this->result_data['api_retval'] ) && $this->result_data['api_retval'] == false ) {
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function isError() { //Opposite of isValid()
		if ( isset( $this->result_data['api_retval'] ) ) {
			if ( $this->result_data['api_retval'] === false ) {
				return true;
			} else {
				return false;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getCode() {
		if ( isset( $this->result_data['api_details'] ) && isset( $this->result_data['api_details']['code'] ) ) {
			return $this->result_data['api_details']['code'];
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getDescription() {
		if ( isset( $this->result_data['api_details'] ) && isset( $this->result_data['api_details']['description'] ) ) {
			return $this->result_data['api_details']['description'];
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getDetails() {
		if ( isset( $this->result_data['api_details'] ) && isset( $this->result_data['api_details']['details'] ) ) {
			return $this->result_data['api_details']['details'];
		}

		return false;
	}

	/**
	 * @return bool|string
	 */
	function getDetailsDescription() {
		$details = $this->getDetails();
		if ( is_array( $details ) ) {
			$retval = [];

			foreach ( $details as $key => $row_details ) {
				foreach ( $row_details as $field => $field_details ) {
					foreach ( $field_details as $detail ) {
						$retval[] = '[' . $field . '] ' . $detail;
					}
				}
			}

			return implode( ' ', $retval );
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getRecordDetails() {
		if ( isset( $this->result_data['api_details'] ) && isset( $this->result_data['api_details']['record_details'] ) ) {
			return $this->result_data['api_details']['record_details'];
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getTotalRecords() {
		if ( isset( $this->result_data['api_details'] ) && isset( $this->result_data['api_details']['record_details'] ) && isset( $this->result_data['api_details']['record_details']['total_records'] ) ) {
			return $this->result_data['api_details']['record_details']['total_records'];
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getValidRecords() {
		if ( isset( $this->result_data['api_details'] ) && isset( $this->result_data['api_details']['record_details'] ) && isset( $this->result_data['api_details']['record_details']['valid_records'] ) ) {
			return $this->result_data['api_details']['record_details']['valid_records'];
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getInValidRecords() {
		if ( isset( $this->result_data['api_details'] ) && isset( $this->result_data['api_details']['record_details'] ) && isset( $this->result_data['api_details']['record_details']['invalid_records'] ) ) {
			return $this->result_data['api_details']['record_details']['invalid_records'];
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getResult() {
		if ( isset( $this->result_data['api_retval'] ) ) {
			return $this->result_data['api_retval'];
		} else {
			return $this->result_data;
		}
	}
}

?>