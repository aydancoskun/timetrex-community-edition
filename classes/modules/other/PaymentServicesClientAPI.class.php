<?php

/**
 * Class PaymentServicesClientAPI
 */
class PaymentServicesClientAPI {
	protected $url = 'https://paymentservices.timetrex.com/api/soap/api.php';

	protected $user_name = NULL;
	protected $password = NULL;

	protected $cookies = array();

	//protected $session_id = NULL;
	//protected $session_hash = NULL; //Used to determine if we need to login again because the URL or Session changed.
	protected $class_factory = NULL;
	protected $namespace = 'urn:api';
	protected $protocol_version = 1;

	protected $soap_obj = NULL; //Persistent SOAP object.

	/**
	 * PaymentServicesClientAPI constructor.
	 * @param null $class
	 * @param null $user_name
	 * @param null $password
	 * @param null $url
	 * @param null $cookies
	 */
	function __construct( $class = NULL, $user_name = NULL, $password = NULL, $url = NULL, $cookies = NULL ) {
		global $PAYMENTSERVICES_URL, $PAYMENTSERVICES_COOKIES, $PAYMENTSERVICES_USER, $PAYMENTSERVICES_PASSWORD;

		ini_set( 'default_socket_timeout', 3600 );

		if ( $url == '' ) {
			$url = $PAYMENTSERVICES_URL;
		}

		if ( $cookies == '' AND ( isset($PAYMENTSERVICES_COOKIES) AND is_array( $PAYMENTSERVICES_COOKIES ) ) ) {
			$cookies = $PAYMENTSERVICES_COOKIES;
		}

		if ( $url == '' ) {
			$url = $PAYMENTSERVICES_URL;
		}

		if ( $user_name == '' ) {
			$user_name = $PAYMENTSERVICES_USER;
		}

		if ( $password == '' ) {
			$password = $PAYMENTSERVICES_PASSWORD;
		}

		$this->setCookies( $cookies );
		$this->setUsername( $user_name );
		$this->setPassword( $password );
		$this->setURL( $url );
		$this->setClass( $class );

		return TRUE;
	}

	/**
	 * @return SoapClient
	 */
	function getSoapClientObject() {
		$url_pieces[] = 'Class=' . $this->class_factory;

		if ( strpos( $this->url, '?' ) === FALSE ) {
			$url_separator = '?';
		} else {
			$url_separator = '&';
		}

		$url = $this->url . $url_separator . 'v=' . $this->protocol_version . '&' . implode( '&', $url_pieces );

		//Try to maintain existing SOAP object as there could be cookies associated with it.
		if ( !is_object( $this->soap_obj ) ) {
			if ( PRODUCTION == FALSE ) {
				//Allow self-signed certificates to be accepted when not in production mode.
				$stream_context_options = array(
						'ssl' => array(
							// set some SSL/TLS specific options
							'verify_peer'       => FALSE,
							'verify_peer_name'  => FALSE,
							'allow_self_signed' => TRUE,
						),
				);
			} else {
				$stream_context_options = array();
			}
			$steam_context = stream_context_create( $stream_context_options );

			$retval = new SoapClient( NULL, array(
												  'stream_context'     => $steam_context,
												  'location'           => $url,
												  'uri'                => $this->namespace,
												  'encoding'           => 'UTF-8',
												  'style'              => SOAP_RPC,
												  'use'                => SOAP_ENCODED,
												  'login'              => $this->user_name, //Username
												  'password'           => $this->password, //API Key
												  'connection_timeout' => 120,
												  'request_timeout'    => 7200,
												  'compression'        => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
												  'trace'              => 1,
												  'exceptions'         => 0,
										  )
			);

			if ( is_array( $this->cookies ) ) {
				foreach ( $this->cookies as $key => $value ) {
					$retval->__setCookie( $key, $value );
				}
			}
		} else {
			$retval = $this->soap_obj;
			$retval->__setLocation( $url );
		}

		return $retval;
	}

	/**
	 * @param $user_name
	 * @return bool
	 */
	function setUserName( $user_name ) {
		if ( $user_name != '' ) {
			$this->user_name = $user_name;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param $password
	 * @return bool
	 */
	function setPassword( $password ) {
		if ( $password != '' ) {
			$this->password = $password;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param $cookies
	 * @return bool
	 */
	function setCookies( $cookies ) {
		if ( is_array( $cookies ) ) {
			$this->cookies = $cookies;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param $url
	 * @return bool
	 */
	function setURL( $url ) {
		if ( $url != '' ) {
			$this->url = $url;

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setClass( $value ) {
		$this->class_factory = trim( $value );

		return TRUE;
	}

	/**
	 * @param $result
	 * @return mixed
	 */
	function isFault( $result ) {
		return $this->getSoapClientObject()->is_soap_fault( $result );
	}

	/**
	 * @param $function_name
	 * @param array $args
	 * @return bool|PaymentServicesClientAPIReturnHandler
	 */
	function __call( $function_name, $args = array() ) {
		if ( is_object( $this->getSoapClientObject() ) ) {
			$retval = call_user_func_array( array($this->getSoapClientObject(), $function_name), $args );

			if ( is_soap_fault( $retval ) ) {
				//trigger_error( 'SOAP Fault: (Code: ' . $retval->faultcode . ', String: ' . $retval->faultstring . ') - Request: ' . $this->getSoapClientObject()->__getLastRequest() . ' Response: ' . $this->getSoapClientObject()->__getLastResponse(), E_USER_NOTICE );
				//Debug::Arr( array('last_request' => $this->getSoapClientObject()->__getLastRequest(), 'last_response' => $this->getSoapClientObject()->__getLastResponse()), 'SOAP Fault: '. $retval->faultstring .' Code: '. $retval->faultcode, __FILE__, __LINE__, __METHOD__, 10);
				throw new Exception('SOAP Fault: '. $retval->faultstring .' (Code: '. $retval->faultcode .')', (int)$retval->faultcode );

				return FALSE;
			}

			return new PaymentServicesClientAPIReturnHandler( $function_name, $args, $retval );
		}

		return FALSE;
	}
}

/**
 * @package API\PaymentServicesClientAPI
 */
class PaymentServicesClientAPIReturnHandler {
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
	protected $function_name = NULL;
	protected $args = NULL;
	protected $result_data = FALSE;

	/**
	 * PaymentServicesClientAPIReturnHandler constructor.
	 * @param $function_name
	 * @param $args
	 * @param $result_data
	 */
	function __construct( $function_name, $args, $result_data ) {
		$this->function_name = $function_name;
		$this->args = $args;
		$this->result_data = $result_data;

		return TRUE;
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

		$output = array();
		$output[] = '=====================================';
		$output[] = 'Function: ' . $this->getFunction() . '()';
		if ( is_object( $this->getArgs() ) OR is_array( $this->getArgs() ) ) {
			$output[] = 'Args: ' . count( $this->getArgs() );
		} else {
			$output[] = 'Args: ' . $this->getArgs();
		}
		$output[] = '-------------------------------------';
		$output[] = 'Returned:';
		$output[] = ( $this->isValid() === TRUE ) ? 'IsValid: YES' : 'IsValid: NO';
		if ( $this->isValid() === TRUE ) {
			$output[] = 'Return Value: ' . $this->getResult();
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
		if ( isset( $this->result_data['api_retval'] ) ) {
			return (bool)$this->result_data['api_retval'];
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function isError() { //Opposite of isValid()
		if ( isset( $this->result_data['api_retval'] ) ) {
			if ( $this->result_data['api_retval'] === FALSE ) {
				return TRUE;
			} else {
				return FALSE;
			}
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getCode() {
		if ( isset( $this->result_data['api_details'] ) AND isset( $this->result_data['api_details']['code'] ) ) {
			return $this->result_data['api_details']['code'];
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getDescription() {
		if ( isset( $this->result_data['api_details'] ) AND isset( $this->result_data['api_details']['description'] ) ) {
			return $this->result_data['api_details']['description'];
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getDetails() {
		if ( isset( $this->result_data['api_details'] ) AND isset( $this->result_data['api_details']['details'] ) ) {
			return $this->result_data['api_details']['details'];
		}

		return FALSE;
	}

	/**
	 * @return bool|string
	 */
	function getDetailsDescription() {
		$details = $this->getDetails();
		if ( is_array( $details ) ) {
			$retval = array();

			foreach( $details as $key => $row_details ) {
				foreach ( $row_details as $field => $field_details ) {
					foreach( $field_details as $detail ) {
						$retval[] = '['. $field .'] '. $detail;
					}
				}
			}

			return implode( ' ', $retval );
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getRecordDetails() {
		if ( isset( $this->result_data['api_details'] ) AND isset( $this->result_data['api_details']['record_details'] ) ) {
			return $this->result_data['api_details']['record_details'];
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getTotalRecords() {
		if ( isset( $this->result_data['api_details'] ) AND isset( $this->result_data['api_details']['record_details'] ) AND isset( $this->result_data['api_details']['record_details']['total_records'] ) ) {
			return $this->result_data['api_details']['record_details']['total_records'];
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getValidRecords() {
		if ( isset( $this->result_data['api_details'] ) AND isset( $this->result_data['api_details']['record_details'] ) AND isset( $this->result_data['api_details']['record_details']['valid_records'] ) ) {
			return $this->result_data['api_details']['record_details']['valid_records'];
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	function getInValidRecords() {
		if ( isset( $this->result_data['api_details'] ) AND isset( $this->result_data['api_details']['record_details'] ) AND isset( $this->result_data['api_details']['record_details']['invalid_records'] ) ) {
			return $this->result_data['api_details']['record_details']['invalid_records'];
		}

		return FALSE;
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