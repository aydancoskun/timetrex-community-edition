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
class Debug {
	static protected $enable = FALSE;			//Enable/Disable debug printing.
	static protected $verbosity = 5;			//Display debug info with a verbosity level equal or lesser then this.
	static protected $buffer_output = TRUE;		//Enable/Disable output buffering.
	static protected $debug_buffer = NULL;		//Output buffer.
	static protected $enable_tidy = FALSE;		//Enable/Disable tidying of output
	static protected $enable_display = FALSE;	//Enable/Disable displaying of debug output
	static protected $enable_log = FALSE;		//Enable/Disable logging of debug output
	static protected $max_line_size = 200;		//Max line size in characters. This is used to break up long lines.
	static protected $max_buffer_size = 1000;	//Max buffer size in lines. **Syslog can't handle much more than 1000.
	static protected $buffer_id = NULL;			//Unique identifier for the debug buffer.
	static protected $php_errors = 0;			//Count number of PHP errors so we can automatically email the log.
	static protected $email_log = FALSE;		//Determine if log needs to be emailed on shutdown.

	static protected $buffer_size = 0;			//Current buffer size in lines.

	static $tidy_obj = NULL;

	/**
	 * @param $bool
	 */
	static function setEnable( $bool) {
		self::setBufferID();
		self::$enable = $bool;
	}

	/**
	 * @return bool
	 */
	static function getEnable() {
		return self::$enable;
	}

	/**
	 * @param $bool
	 */
	static function setBufferOutput( $bool) {
		self::$buffer_output = $bool;
	}

	/**
	 * @param $level
	 */
	static function setVerbosity( $level) {
		global $db;

		self::$verbosity = (int)$level;

		if (is_object($db) AND $level == 11) {
			$db->debug = TRUE;
		}
	}

	/**
	 * @return int
	 */
	static function getVerbosity() {
		return self::$verbosity;
	}

	/**
	 * @param $bool
	 */
	static function setEnableDisplay( $bool) {
		self::$enable_display = $bool;
	}

	/**
	 * @return bool
	 */
	static function getEnableDisplay() {
		return self::$enable_display;
	}

	/**
	 * @param $bool
	 */
	static function setEnableLog( $bool) {
		self::$enable_log = $bool;
	}

	/**
	 * @return bool
	 */
	static function getEnableLog() {
		return self::$enable_log;
	}

	static function setBufferID() {
		if ( self::$buffer_id == NULL ) {
			self::$buffer_id = uniqid();
		}
	}

	/**
	 * @param string $extra_ident UUID
	 * @param null $company_name
	 * @return mixed
	 */
	static function getSyslogIdent( $extra_ident = NULL, $company_name = NULL ) {
		global $config_vars, $current_company;

		$suffix = NULL;
		if ( $company_name != '' ) {
			$suffix = $company_name;
		} elseif ( isset($current_company) AND is_object( $current_company ) ) {
			$suffix = $current_company->getShortName();
		} else {
			$suffix = 'System';
		}

		if ( isset($config_vars['debug']['syslog_ident']) AND $config_vars['debug']['syslog_ident'] != '' ) {
			$retval = $config_vars['debug']['syslog_ident'].'-'.$suffix.$extra_ident;
		} else {
			$retval = APPLICATION_NAME.'-'.$suffix.$extra_ident;
		}

		return preg_replace('/[^a-zA-Z0-9-]/', '', escapeshellarg( $retval ) ); //This will remove spaces.
	}
	//	Three primary log types: $log_types = array( 0 => 'debug', 1 => 'client', 2 => 'timeclock' );

	/**
	 * @param int $log_type
	 * @return int|mixed
	 */
	static function getSyslogFacility( $log_type = 0 ) {
		global $config_vars;
		if ( isset($config_vars['debug']['syslog_facility']) AND $config_vars['debug']['syslog_facility'] != '' ) {
			$facility_arr = explode( ',', $config_vars['debug']['syslog_facility'] );
			if ( is_array($facility_arr) AND isset( $facility_arr[(int)$log_type] ) ) {
				return ( is_numeric( $facility_arr[(int)$log_type] ) ) ? $facility_arr[(int)$log_type] : constant( trim($facility_arr[(int)$log_type]) );
			}
		}

		return LOG_LOCAL7; //Default
	}

	/**
	 * @param int $log_type
	 * @return int|mixed
	 */
	static function getSyslogPriority( $log_type = 0 ) {
		global $config_vars;

		if ( isset($config_vars['debug']['syslog_priority']) AND $config_vars['debug']['syslog_priority'] != '' ) {
			$priority_arr = explode( ',', $config_vars['debug']['syslog_priority'] );
			if ( is_array($priority_arr) AND isset( $priority_arr[(int)$log_type] ) ) {
				return ( is_numeric( $priority_arr[(int)$log_type] ) ) ? $priority_arr[(int)$log_type] : constant( trim($priority_arr[(int)$log_type]) );
			}
		}

		return LOG_DEBUG; //Default
	}

	//Used to add timing to each debug call.

	/**
	 * @return float
	 */
	static function getExecutionTime() {
		return ceil( ( (microtime( TRUE ) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000 ) );
	}

	//Splits long debug lines or array dumps to prevent syslog overflows.

	/**
	 * @param $text
	 * @param null $prefix
	 * @param null $suffix
	 * @return array
	 */
	static function splitInput( $text, $prefix = NULL, $suffix = NULL ) {
		if ( strlen( $text ) > self::$max_line_size ) {
			$retarr = array();

			$lines = explode( PHP_EOL, $text ); //Split on newlines first.
			foreach( $lines as $line ) {
				$split_lines = str_split( $line, self::$max_line_size ); //Split on long lines next.
				foreach( $split_lines as $split_line ) {
					$retarr[] = $prefix.$split_line.$suffix;
				}
			}
			unset($lines, $line, $split_lines, $split_line);
		} else {
			$retarr = array( $prefix.$text.$suffix ); //Always returns an array.
		}

		return $retarr;
	}

	/**
	 * @param null $text
	 * @param string $file
	 * @param int $line
	 * @param string $method
	 * @param int $verbosity
	 * @return bool
	 */
	static function Text( $text = NULL, $file = __FILE__, $line = __LINE__, $method = __METHOD__, $verbosity = 9) {
		if ( $verbosity > self::getVerbosity() OR self::$enable == FALSE ) {
			return FALSE;
		}

		if ( empty($method) ) {
			$method = 'GLOBAL: '; //Was: [Function]
		} else {
			$method = $method .'(): ';
		}

		//If text is too long, split it into an array.
		$text_arr = self::splitInput( $text, 'DEBUG [L'. str_pad( $line, 4, 0, STR_PAD_LEFT) .'] ['. str_pad( self::getExecutionTime(), 5, 0, STR_PAD_LEFT) .'ms]: '. $method, PHP_EOL );

		if ( self::$buffer_output == TRUE ) {
			foreach( $text_arr as $text_line ) {
				self::$debug_buffer[] = array($verbosity, $text_line);
				self::$buffer_size++;
				self::handleBufferSize( $line, $method );
			}
		} else {
			if ( self::$enable_display == TRUE ) {
				foreach( $text_arr as $text_line ) {
					echo $text_line;
				}
			} elseif ( OPERATING_SYSTEM != 'WIN' AND self::$enable_log == TRUE ) {
				foreach( $text_arr as $text_line ) {
					syslog(LOG_DEBUG, $text_line );
				}
			}
		}

		return TRUE;
	}

	/**
	 * @param object $profile_obj
	 * @return bool|string
	 */
	static function profileTimers( $profile_obj ) {
		if ( !is_object($profile_obj) ) {
			return FALSE;
		}

		ob_start();
		$profile_obj->printTimers();
		$ob_contents = ob_get_contents();
		ob_end_clean();

		return $ob_contents;
	}

	/**
	 * @return string
	 */
	static function backTrace() {
		//ob_start();
		//debug_print_backtrace();
		//$ob_contents = ob_get_contents();
		//ob_end_clean();
		//return $ob_contents;

		$retval = '';
		$trace_arr = debug_backtrace();
		if ( is_array($trace_arr) ) {
			$i = 1;
			foreach( $trace_arr as $trace_line ) {
				if ( isset($trace_line['class']) AND isset($trace_line['type'])	 ) {
					$class = $trace_line['class'].$trace_line['type'];
				} else {
					$class = NULL;
				}

				if ( !isset($trace_line['file']) ) {
					$trace_line['file'] = 'N/A';
				}

				if ( !isset($trace_line['line']) ) {
					$trace_line['line'] = 'N/A';
				}

				if ( isset($trace_line['args']) AND is_array($trace_line['args']) ) {
					$args = array();
					foreach( $trace_line['args'] as $arg ) {
						if ( is_array($arg) ) {
							if ( self::getVerbosity() == 11 ) {
								$args[] = self::varDump( $arg ); //NOTE: If this contains an exception object from ADODB and is triggered from a SQL error, it could cause a circular reference and exhaust all memory.
							} else {
								//Don't display the entire array is it polutes the log and is too large for syslog anyways.
								$args[] = 'Array('. count($arg) .')';
							}
						} elseif ( is_object($arg) ) {
							if ( self::getVerbosity() == 11 ) {
								$args[] = self::varDump( $arg ); //NOTE: If this contains an exception object from ADODB and is triggered from a SQL error, it could cause a circular reference and exhaust all memory.
							} else {
								//Don't display the entire array is it polutes the log and is too large for syslog anyways.
								$args[] = 'Object('. get_class( $arg ) .')';
							}

						} else {
							$args[] = $arg;
						}
					}
				}
				$retval .= '#'.$i.'.'. $class.$trace_line['function'].'('. implode(', ', $args) .') '. $trace_line['file'] .':'. $trace_line['line'] . PHP_EOL;
				$i++;
			}
		}
		unset($trace_arr, $trace_line, $args);

		return $retval;
	}

	/**
	 * @param $array
	 * @return string
	 */
	static function varDump( $array ) {
		ob_start();
		var_dump($array); //Xdebug may interfere with this and cause it to not display all the data...
		//print_r($array);
		$ob_contents = ob_get_contents();
		ob_end_clean();

		return $ob_contents;
	}

	/**
	 * @param $array
	 * @param null $text
	 * @param string $file
	 * @param int $line
	 * @param string $method
	 * @param int $verbosity
	 * @return bool
	 */
	static function Arr( $array, $text = NULL, $file = __FILE__, $line = __LINE__, $method = __METHOD__, $verbosity = 9) {
		if ( $verbosity > self::getVerbosity() OR self::$enable == FALSE ) {
			return FALSE;
		}

		if ( empty($method) ) {
			$method = '[Function]';
		}

		$text_arr = array();
		$text_arr[] = 'DEBUG [L'. str_pad( $line, 4, 0, STR_PAD_LEFT) .'] ['. str_pad( self::getExecutionTime(), 5, 0, STR_PAD_LEFT) .'ms] Array: '. $method .'(): '. $text . PHP_EOL;
		$text_arr = array_merge( $text_arr, self::splitInput( self::varDump($array), NULL, PHP_EOL ) );
		$text_arr[] = PHP_EOL;

		if (self::$buffer_output == TRUE) {
			foreach( $text_arr as $text_line ) {
				self::$debug_buffer[] = array($verbosity, $text_line);
				self::$buffer_size++;
				self::handleBufferSize( $line, $method );
			}
		} else {
			if ( self::$enable_display == TRUE ) {
				foreach( $text_arr as $text_line ) {
					echo $text_line;
				}
			} elseif ( OPERATING_SYSTEM != 'WIN' AND self::$enable_log == TRUE ) {
				foreach( $text_arr as $text_line ) {
					syslog(LOG_DEBUG, $text_line );
				}
			}
		}

		return TRUE;
	}

	/**
	 * Output SQL query with place holders inserted into the query.
	 * @param $query
	 * @param $ph
	 * @param string $file
	 * @param int $line
	 * @param string $method
	 * @param int $verbosity
	 * @return bool
	 */
	static function Query( $query, $ph, $file = __FILE__, $line = __LINE__, $method = __METHOD__, $verbosity = 9 ) {
		$output_query = PHP_EOL; //Start with newline so its easier to copy&paste.

		$split_query = explode( '?', $query );
		foreach( $split_query as $query_chunk ) {
			$ph_value = ( !empty($ph) ) ? array_shift( $ph ) : FALSE; //array_shift() returns NULL if no elements are left, but the first value can also be NULL in some cases too.
			if ( is_string( $ph_value ) ) {
				$ph_value = '\''. $ph_value .'\'';
			} elseif ( $ph_value === NULL ) {
				$ph_value = 'NULL';
			}
			$output_query .= $query_chunk . $ph_value;
		}

		$output_query = str_replace( "\t", ' ', $output_query );

		$output_query .= ';'. PHP_EOL; //End with newline so its easier to copy&paste.

		self::Arr( $output_query, 'SQL Query: ', $file, $line, $method, $verbosity );

		return TRUE;
	}

	/**
	 * @param $error_number
	 * @param $error_str
	 * @param $error_file
	 * @param $error_line
	 * @return bool
	 *
	 * Replacement for apache_request_headers() as it wasn't reliably available and would sometimes cause PHP fatal errors due to it being undefined.
	 */
	static function RequestHeaders() {
		$arh = array();
		$rx_http = '/\AHTTP_/';
		foreach ( $_SERVER as $key => $val ) {
			if ( preg_match( $rx_http, $key ) ) {
				$arh_key = preg_replace( $rx_http, '', $key );
				$rx_matches = array();
				// do some nasty string manipulations to restore the original letter case
				// this should work in most cases
				$rx_matches = explode( '_', strtolower( $arh_key ) );
				if ( count( $rx_matches ) > 0 and strlen( $arh_key ) > 2 ) {
					foreach ( $rx_matches as $ak_key => $ak_val ) {
						$rx_matches[ $ak_key ] = ucfirst( $ak_val );
					}
					$arh_key = implode( '-', $rx_matches );
				}
				$arh[ $arh_key ] = $val;
			}
		}

		if ( isset( $_SERVER['CONTENT_TYPE'] ) ) {
			$arh['Content-Type'] = $_SERVER['CONTENT_TYPE'];
		}
		if ( isset( $_SERVER['CONTENT_LENGTH'] ) ) {
			$arh['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
		}

		return $arh;
	}

	static function ErrorHandler( $error_number, $error_str, $error_file, $error_line ) {
		//Only handle errors included in the error_reporting()
		if ( ( error_reporting() & $error_number ) ) { //Bitwise operator.
			// This error code is not included in error_reporting
			switch ( $error_number ) {
				case E_USER_ERROR:
					$error_name = 'FATAL';
					break;
				case E_USER_WARNING:
				case E_WARNING:
					$error_name = 'WARNING';
					break;
				case E_USER_NOTICE:
				case E_NOTICE:
					$error_name = 'NOTICE';
					break;
				case E_STRICT:
					$error_name = 'STRICT';
					break;
				case E_DEPRECATED:
					$error_name = 'DEPRECATED';
					break;
				default:
					$error_name = 'UNKNOWN';
			}

			$error_name .= '('. $error_number .')';

			$text = 'PHP ERROR - '. $error_name .': '. $error_str .' File: '. $error_file .' Line: '. $error_line;

			//If this is the first PHP error, make sure debugging is enabled so it and any others can be captured.
			if ( self::$php_errors == 0 ) {
				self::setEnable(TRUE);
				self::setBufferOutput(TRUE);
			}

			self::$php_errors++;

			//Display these errors in the log, but don't cause them to trigger PHP errors that forces the log to be emailed.
			if ( $error_number == E_USER_ERROR
					OR ( DEPLOYMENT_ON_DEMAND == TRUE
							OR ( DEPLOYMENT_ON_DEMAND == FALSE
									AND (
											//Database
											stristr( $error_str, 'unable to connect' ) === FALSE
											AND stristr( $error_str, 'statement timeout' ) === FALSE
											AND stristr( $error_str, 'unique constraint' ) === FALSE
											AND stristr( $error_str, 'deadlock' ) === FALSE
											AND stristr( $error_str, 'server has gone away' ) === FALSE
											AND stristr( $error_str, 'closed the connection unexpectedly' ) === FALSE
											AND stristr( $error_str, 'execution was interrupted' ) === FALSE
											AND stristr( $error_str, 'No space left on device' ) === FALSE
											AND stristr( $error_str, 'unserialize' ) === FALSE
											AND stristr( $error_str, 'headers already sent by' ) === FALSE

											//SOAP
											AND stristr( $error_str, 'An existing connection was forcibly closed by the remote host' ) === FALSE

											//MISC
											AND stristr( $error_str, 'Unable to fork' ) === FALSE
									)
							)
					)
			) {
				self::$email_log = TRUE;
			}

			if ( self::$php_errors == 1 ) { //Only trigger this on the first error, so its not repeated over and over again.
				if ( PHP_SAPI != 'cli' ) { //Used to use apache_request_headers() here, but it would often fail as undefined, even though we would check function_exists() on it.
					self::Arr( self::RequestHeaders(), 'Raw Request Headers: ', $error_file, $error_line, __METHOD__, 0 );
				}

				global $HTTP_RAW_POST_DATA;
				if ( $HTTP_RAW_POST_DATA != '' ) {
					self::Arr( urldecode( $HTTP_RAW_POST_DATA ), 'Raw POST Request: ', $error_file, $error_line, __METHOD__, 0 );
				}
			}

			self::Text( '(E'. self::$php_errors .') '. $text, $error_file, $error_line, __METHOD__, 0 );
			self::Text( self::backTrace(), $error_file, $error_line, __METHOD__, 0 );
		}

		return FALSE; //Let the standard PHP error handler work as well.
	}

	/**
	 * @return bool
	 */
	static function Shutdown() {
		$error = error_get_last();
		if ( $error !== NULL AND isset($error['type']) AND $error['type'] == 1 ) { //Only trigger fatal errors on shutdown.
			self::$php_errors++;
			self::Text('PHP ERROR - FATAL('. $error['type'] .'): '. $error['message'] .' File: '. $error['file'] .' Line: '. $error['line'], $error['file'], $error['line'], __METHOD__, 0 );

			if ( defined('TIMETREX_API') AND TIMETREX_API == TRUE ) { //Only when a fatal error occurs.
				global $amf_message_id;
				if ( $amf_message_id != '' ) {
					$progress_bar = new ProgressBar();
					$progress_bar->error( $amf_message_id, TTi18n::getText('ERROR: Operation cannot be completed.') );
					unset($progress_bar);
				}
			}
		}

		if ( self::$email_log == TRUE ) {
			//If the error log is too long, make sure we add important data to help trace it are included at the end of the log.
			global $config_vars, $current_user, $current_company;
			Debug::Text('URI: '. ( isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'N/A' ) .' IP Address: '. Misc::getRemoteIPAddress(), __FILE__, __LINE__, __METHOD__, 10);
			Debug::Text('USER-AGENT: '. ( isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'N/A' ), __FILE__, __LINE__, __METHOD__, 10);
			Debug::Text('Version: '. APPLICATION_VERSION .' (PHP: v'. phpversion() .') Edition: '. getTTProductEdition() .' Production: '. (int)PRODUCTION .' Server: '. ( isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'N/A' ) .' OS: '. OPERATING_SYSTEM .' Database: Type: '. ( isset($config_vars['database']['type']) ? $config_vars['database']['type'] : 'N/A' ) .' Name: '. ( isset($config_vars['database']['database_name']) ? $config_vars['database']['database_name'] : 'N/A' ) .' Config: '. CONFIG_FILE .' Demo Mode: '. (int)DEMO_MODE, __FILE__, __LINE__, __METHOD__, 10);
			Debug::text('Current User: '. ( ( isset($current_user) AND is_object($current_user) ) ? $current_user->getUserName() : 'N/A' ) .' (User ID: '. ( ( isset($current_user) AND is_object($current_user) ) ? $current_user->getID() : 'N/A' ) .') Company: '. ( ( isset($current_company) AND is_object($current_company) ) ? $current_company->getName() : 'N/A' ) .' (Company ID: '. ( ( isset($current_company) AND is_object($current_company) ) ? $current_company->getId() : 'N/A' ) .')', __FILE__, __LINE__, __METHOD__, 10);

			self::Text('Detected PHP errors ('. self::$php_errors .'), emailing log...', __FILE__, __LINE__, __METHOD__, 0);
			self::Text('---------------[ '. @date('d-M-Y G:i:s O') .' ['. microtime(TRUE) .'] (PID: '. getmypid() .') ]---------------', __FILE__, __LINE__, __METHOD__, 0);

			self::emailLog();
			if ( $error !== NULL ) { //Fatal error, write to log once more as this won't be called automatically.
				self::writeToLog();
			}
		} else {
			//Check to see if a transaction was held open, as it could be a potential problem as it was never committed.
			// Essentially, a CommitTrasnaction() should be called after every FailTransaction() before the script exits. Otherwise in things like loops the entire outer transaction would be rolled back unintentionally.
			global $db;
			if ( is_object( $db ) ) {
				$transaction_error = FALSE;
				if ( $db->transOff > 0 ) {
					self::Text('ERROR: Detected UNCOMMITTED transaction: Count: '. $db->transCnt .' Off: '. $db->transOff .' OK: '. (int)$db->_transOK .', emailing log...', __FILE__, __LINE__, __METHOD__, 0);
					$transaction_error = TRUE;
				} elseif( $db->transCnt < 0 ) {
					self::Text('ERROR: Detected DOUBLE COMMITTED transaction: Count: '. $db->transCnt .' Off: '. $db->transOff .' OK: '. (int)$db->_transOK .', emailing log...', __FILE__, __LINE__, __METHOD__, 0);
					$transaction_error = TRUE;
				}

				if ( $transaction_error == TRUE ) {
					self::Text( '---------------[ ' . @date( 'd-M-Y G:i:s O' ) . ' [' . microtime( TRUE ) . '] (PID: ' . getmypid() . ') ]---------------', __FILE__, __LINE__, __METHOD__, 0 );
					self::emailLog();
					self::writeToLog(); //write to log once more as this won't be called automatically.
				}
			}
		}


		return TRUE;
	}

	/**
	 * @return bool|null|string
	 */
	static function getOutput() {
		$output = NULL;
		if ( count(self::$debug_buffer) > 0 ) {
			foreach (self::$debug_buffer as $arr) {
				$verbosity = $arr[0];
				$text = $arr[1];

				if ($verbosity <= self::getVerbosity() ) {
					$output .= $text;
				}
			}

			return $output;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	static function emailLog() {
		if ( PRODUCTION === TRUE ) {
			$output = self::getOutput();

			if ( strlen($output) > 0 ) {
				global $TT_DISABLE_EMAIL_LOG;

				if ( isset($TT_DISABLE_EMAIL_LOG) == FALSE OR $TT_DISABLE_EMAIL_LOG !== TRUE ) { //Prevent emailLog() from triggering more errors and a emailLog infinite loop.
					$TT_DISABLE_EMAIL_LOG = TRUE;

					Misc::sendSystemMail( APPLICATION_NAME . ' - Error!', $output );

					$TT_DISABLE_EMAIL_LOG = FALSE;
				} else {
					self::Text('WARNING: Skipping sendSystemMail() to avoid nested calls...', __FILE__, __LINE__, __METHOD__, 0);
				}
			}
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	static function writeToLog() {
		if (self::$enable_log == TRUE AND self::$buffer_output == TRUE) {
			global $config_vars;

			$eol = PHP_EOL;

			if ( PRODUCTION == FALSE AND function_exists('xdebug_get_gc_run_count') == TRUE AND xdebug_get_gc_run_count() > 0 ) {
				self::Text( 'Garbage Collector Runs: ' . xdebug_get_gc_run_count() .' Collected Roots: '. xdebug_get_gc_total_collected_roots(), __FILE__, __LINE__, __METHOD__, 0 );
				if ( file_exists( xdebug_get_gcstats_filename() ) ) {
					self::Arr( file_get_contents( xdebug_get_gcstats_filename() ), 'Garbage Collection Report:', __FILE__, __LINE__, __METHOD__, 0 );
				}
			}

			if ( is_array( self::$debug_buffer ) ) {
				$output = $eol.'---------------[ '. @date('d-M-Y G:i:s O') .' ['. $_SERVER['REQUEST_TIME_FLOAT'] .'] (PID: '. getmypid() .') ]---------------'.$eol;

				foreach (self::$debug_buffer as $arr) {
					if ( $arr[0] <= self::getVerbosity() ) {
						$output .= $arr[1];
					}
				}

				$output .= '---------------[ '. @date('d-M-Y G:i:s O') .' ['. microtime(TRUE) .'] (PID: '. getmypid() .') ]---------------'.$eol;

				if ( isset($config_vars['debug']['enable_syslog']) AND $config_vars['debug']['enable_syslog'] == TRUE AND OPERATING_SYSTEM != 'WIN' ) {
					//If using rsyslog, need to set:
					//$MaxMessageSize 256000 #Above ModuleLoad imtcp
					openlog( self::getSyslogIdent(), 11, self::getSyslogFacility( 0 ) ); //11 = LOG_PID | LOG_NDELAY | LOG_CONS
					syslog( self::getSyslogPriority( 0 ), $output ); //Used to strip_tags output, but that was likely causing problems with SQL queries with >= and <= in them.
					closelog();
				} else {
					if ( isset($config_vars['path']['log']) AND is_writable( $config_vars['path']['log'] ) ) {
						$file_name = $config_vars['path']['log'] . DIRECTORY_SEPARATOR .'timetrex.log';
						$fp = @fopen( $file_name, 'a' );
						@fwrite($fp, $output ); //Used to strip_tags output, but that was likely causing problems with SQL queries with >= and <= in them.
						@fclose($fp);
						unset($output);
					} else {
						echo "ERROR: Unable to write to log file in directory: ". ( isset($config_vars['path']['log']) ? $config_vars['path']['log'] .'/' : 'N/A' ) . PHP_EOL;
					}
				}

				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	static function Display() {
		if (self::$enable_display == TRUE AND self::$buffer_output == TRUE) {

			$output = self::getOutput();

			if ( function_exists('memory_get_usage') ) {
				$memory_usage = memory_get_usage();
			} else {
				$memory_usage = 'N/A';
			}

			if (strlen($output) > 0) {
				echo PHP_EOL . 'Debug Buffer' . PHP_EOL;
				echo '============================================================================' . PHP_EOL;
				echo 'Memory Usage: '. $memory_usage .' Buffer Size: '. self::$buffer_size . PHP_EOL;
				echo '----------------------------------------------------------------------------' . PHP_EOL;
				echo $output;
				echo '============================================================================' . PHP_EOL;
			}

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param null $line
	 * @param null $method
	 * @return bool
	 */
	static function handleBufferSize( $line = NULL, $method = NULL) {
		//When buffer exceeds maximum size, write it to the log and clear it.
		//This will affect displaying large buffers though, but otherwise we may run out of memory.
		//If we detect PHP errors, buffer up to 10x the maximum size to try and capture those errors.
		if ( ( self::$php_errors == 0 AND self::$buffer_size >= self::$max_buffer_size ) OR ( self::$php_errors > 0 AND self::$buffer_size >= ( self::$max_buffer_size * 100 ) ) ) {
			self::$debug_buffer[] = array(1, 'DEBUG [L'. str_pad( $line, 4, 0, STR_PAD_LEFT) .'] ['. str_pad( self::getExecutionTime(), 5, 0, STR_PAD_LEFT) .'ms]: '. $method .'(): Maximum debug buffer size of: '. self::$max_buffer_size .' reached. Writing out buffer before continuing... Buffer ID: '. self::$buffer_id . PHP_EOL );
			self::writeToLog();
			self::clearBuffer();
			self::$debug_buffer[] = array(1, 'DEBUG [L'. str_pad( $line, 4, 0, STR_PAD_LEFT) .'] ['. str_pad( self::getExecutionTime(), 5, 0, STR_PAD_LEFT) .'ms]: '. $method .'(): Continuing debug output from Buffer ID: '. self::$buffer_id . PHP_EOL );

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	static function clearBuffer() {
		self::$debug_buffer = NULL;
		self::$buffer_size = 0;
		return TRUE;
	}
}
?>
