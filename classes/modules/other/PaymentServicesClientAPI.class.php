<?php

class PaymentServicesClientAPI {
	protected $url = 'https://paymentservices.timetrex.com/api/soap/api.php';

	protected $user_name = NULL;
	protected $password = NULL;

	//protected $session_id = NULL;
	//protected $session_hash = NULL; //Used to determine if we need to login again because the URL or Session changed.
	protected $class_factory = NULL;
	protected $namespace = 'urn:api';
	protected $protocol_version = 1;

	/**
	 * PaymentServicesClientAPI constructor.
	 * @param null $class
	 * @param null $url
	 * @param string $session_id UUID
	 */
	function __construct( $class = NULL, $url = NULL ) {
		global $PAYMENTSERVICES_URL;

		ini_set( 'default_socket_timeout', 3600 );

		if ( $url == '' ) {
			$url = $PAYMENTSERVICES_URL;
		}

		$this->setURL( $url );
		$this->setClass( $class );

		return TRUE;
	}

	/**
	 * @return SoapClient
	 */
	function getSoapClientObject() {
		global $PAYMENTSERVICES_USER, $PAYMENTSERVICES_PASSWORD;

		$url_pieces[] = 'Class=' . $this->class_factory;

		if ( strpos( $this->url, '?' ) === FALSE ) {
			$url_separator = '?';
		} else {
			$url_separator = '&';
		}

		$url = $this->url . $url_separator . 'v=' . $this->protocol_version . '&' . implode( '&', $url_pieces );

		if ( PRODUCTION == FALSE ) {
			//Allow self-signed certificates to be accepted when not in production mode.
			$steam_context = stream_context_create( array(
															'ssl' => array(
																// set some SSL/TLS specific options
																'verify_peer'       => FALSE,
																'verify_peer_name'  => FALSE,
																'allow_self_signed' => TRUE
															)
													) );
		} else {
			$steam_context = stream_context_create();
		}

		$retval = new SoapClient( NULL, array(
											  'stream_context' => $steam_context,
											  'location'    => $url,
											  'uri'         => $this->namespace,
											  'encoding'    => 'UTF-8',
											  'style'       => SOAP_RPC,
											  'use'         => SOAP_ENCODED,
											  'login'       => $PAYMENTSERVICES_USER, //Username
											  'password'    => $PAYMENTSERVICES_PASSWORD, //API Key
											  'connection_timeout' => 120,
											  'request_timeout' => 7200,
											  'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
											  'trace'       => 1,
											  'exceptions'  => 0,
									  )
		);

		return $retval;
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