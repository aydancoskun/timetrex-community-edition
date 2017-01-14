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
 * @package Core
 */
class DBError extends Exception {
	function __construct($e, $code = 'DBError' ) {
		global $db, $skip_db_error_exception;

		if ( isset($skip_db_error_exception) AND $skip_db_error_exception === TRUE ) { //Used by system_check script.
			return TRUE;
		}

		//If we couldn't connect to the database, this method may not exist.
		if (  isset($db) AND is_object($db) AND method_exists( $db, 'FailTrans' ) ) {
			$db->FailTrans();
		}

		//print_r($e);
		//adodb_pr($e);

		Debug::Text('Begin Exception...', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr( Debug::backTrace(), ' BackTrace: ', __FILE__, __LINE__, __METHOD__, 10);

		//Log database error
		if ( $e->getMessage() != '' ) {
			if ( stristr( $e->getMessage(), 'statement timeout' ) !== FALSE ) {
				$code = 'DBTimeout';
			} elseif ( stristr( $e->getMessage(), 'unique constraint' ) !== FALSE) {
				$code = 'DBUniqueConstraint';
			} elseif ( stristr( $e->getMessage(), 'invalid byte sequence' ) !== FALSE) {
				$code = 'DBInvalidByteSequence';
			} elseif ( stristr( $e->getMessage(), 'could not serialize' ) !== FALSE) {
				$code = 'DBSerialize';
			} elseif ( stristr( $e->getMessage(), 'deadlock' ) !== FALSE OR stristr( $e->getMessage(), 'concurrent' ) !== FALSE ) {
				$code = 'DBDeadLock';
			} elseif ( stristr( $e->getMessage(), 'server has gone away' ) !== FALSE OR stristr( $e->getMessage(), 'closed the connection unexpectedly' ) !== FALSE ) {
				$code = 'DBConnectionLost';
			} elseif ( stristr( $e->getMessage(), 'No space left on device' ) !== FALSE) { //Unrecoverable error, set down_for_maintenance so server admin can investigate?
				$code = 'DBNoSpaceOnDevice';
			}
			Debug::Text( 'Code: '. $code .'('. $e->getCode() .') Message: '. $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10);
		}

		if ( $e->getTrace() != '' ) {
			ob_start(); //ADDBO_BACKTRACE() always insists on printing its output and returning it, so capture the output and drop it, so we can use the $e variable instead.
			$e = adodb_backtrace( $e->getTrace(), 9999, 0, FALSE );
			ob_end_clean();
			Debug::Arr( $e, 'Exception...', __FILE__, __LINE__, __METHOD__, 10);
		}

		Debug::Text('End Exception...', __FILE__, __LINE__, __METHOD__, 10);

		if ( !defined( 'UNIT_TEST_MODE' ) OR UNIT_TEST_MODE === FALSE ) { //When in unit test mode don't exit/redirect
			//Dump debug buffer.
			Debug::Display();
			Debug::writeToLog();
			Debug::emailLog();

			//Prevent PHP error by checking to make sure output buffer exists before clearing it.
			if ( ob_get_level() > 0 ) {
				ob_flush();
				ob_clean();
			}

			if ( defined('TIMETREX_JSON_API') ) {
				if ( DEPLOYMENT_ON_DEMAND == TRUE ) {
					switch ( strtolower($code) ) {
						case 'dbtimeout':
							$description = TTi18n::getText('%1 database query has timed-out, if you were trying to run a report it may be too large, please narrow your search criteria and try again.', array( APPLICATION_NAME ) );
							break;
						case 'dbuniqueconstraint':
						case 'dbdeadlock':
							$description = TTi18n::getText('%1 has detected a duplicate request, this may be due to double-clicking a button or a poor internet connection.', array( APPLICATION_NAME ) );
							break;
						case 'dbinvalidbytesequence':
							$description = TTi18n::getText('%1 has detected invalid UTF8 characters, if you are attempting to use non-english characters, they may be invalid.', array( APPLICATION_NAME ) );
							break;
						case 'dbserialize':
							$description = TTi18n::getText('%1 has detected a duplicate request running at the exact same time, please try your request again in a couple minutes.', array( APPLICATION_NAME ) );
							break;
						default:
							$description = TTi18n::getText('%1 is currently undergoing maintenance. We\'re sorry for any inconvenience this may cause. Please try again in 15 minutes.', array( APPLICATION_NAME ) );
							break;
					}
				} else {
					switch ( strtolower($code) ) {
						case 'dbtimeout':
							$description = TTi18n::getText('%1 database query has timed-out, if you were trying to run a report it may be too large, please narrow your search criteria and try again.', array( APPLICATION_NAME ) );
							break;
						case 'dbuniqueconstraint':
						case 'dbdeadlock':
							$description = TTi18n::getText('%1 has detected a duplicate request, this may be due to double-clicking a button or a poor internet connection.', array( APPLICATION_NAME ) );
							break;
						case 'dbinvalidbytesequence':
							$description = TTi18n::getText('%1 has detected invalid UTF8 characters, if you are attempting to use non-english characters, they may be invalid.', array( APPLICATION_NAME ) );
							break;
						case 'dbserialize':
							$description = TTi18n::getText('%1 has detected a duplicate request running at the exact same time, please try your request again in a couple minutes.', array( APPLICATION_NAME ) );
							break;
						case 'dbnospaceondevice':
							$description = TTi18n::getText('%1 has detected a database error, please contact technical support immediately.', array( APPLICATION_NAME ) );
							break;
						case 'dberror':
							$description = TTi18n::getText('%1 is unable to connect to its database, please make sure that the database service on your own local %1 server has been started and is running. If you are unsure, try rebooting your server.', array( APPLICATION_NAME ));
							break;
						case 'dbinitialize':
							$description = TTi18n::getText('%1 database has not been initialized yet, please run the installer again and follow the on screen instructions. <a href="%2">Click here to run the installer now.</a>', array( APPLICATION_NAME, Environment::getBaseURL().'/install/install.php?external_installer=1' ));
							break;
						default:
							$description = TTi18n::getText('%1 experienced a general error, please contact technical support.', array( APPLICATION_NAME ) );
							break;
					}
				}

				$obj = new APIAuthentication();
				echo json_encode( $obj->returnHandler( FALSE, 'EXCEPTION', $description ) );
				exit;
			} elseif ( PHP_SAPI == 'cli' ) {
				//Don't attempt to redirect
				echo "Fatal Exception: Code: ". $code ."... Exiting with error code 254!\n";
				exit(254);
			} else {
				global $config_vars;
				if ( DEPLOYMENT_ON_DEMAND == FALSE
						AND isset($config_vars['other']['installer_enabled']) AND $config_vars['other']['installer_enabled'] == 1
						AND in_array( strtolower($code), array('dberror', 'dbinitialize') ) ) {
					Redirect::Page( URLBuilder::getURL( array(), Environment::getBaseURL().'html5/index.php?installer=1&disable_db=1&external_installer=0#!m=Install&a=license&external_installer=0') );
				} else {
					Redirect::Page( URLBuilder::getURL( array('exception' => $code ), Environment::getBaseURL().'DownForMaintenance.php') );
				}
				exit;
			}
		}
	}
}

/**
 * @package Core
 */
class GeneralError extends Exception {
	function __construct($message) {
		global $db;

		//debug_print_backtrace();

		//If we couldn't connect to the database, this method may not exist.
		if ( isset($db) AND is_object($db) AND method_exists( $db, 'FailTrans' ) ) {
			$db->FailTrans();
		}

		/*
		echo "======================================================================<br>\n";
		echo "EXCEPTION!<br>\n";
		echo "======================================================================<br>\n";
		echo "<b>Error message: </b>".$message ."<br>\n";
		echo "<b>Error code: </b>".$this->getCode()."<br>\n";
		echo "<b>Script Name: </b>".$this->getFile()."<br>\n";
		echo "<b>Line Number: </b>".$this->getLine()."<br>\n";
		echo "======================================================================<br>\n";
		echo "EXCEPTION!<br>\n";
		echo "======================================================================<br>\n";
		*/

		Debug::Text('EXCEPTION: Code: '. $this->getCode() .' Message: '. $message .' File: '. $this->getFile() .' Line: '. $this->getLine(), __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr( Debug::backTrace(), ' BackTrace: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( !defined( 'UNIT_TEST_MODE' ) OR UNIT_TEST_MODE === FALSE ) { //When in unit test mode don't exit/redirect
			//Dump debug buffer.
			Debug::Display();
			Debug::writeToLog();
			Debug::emailLog();
			ob_flush();
			ob_clean();

			if ( defined('TIMETREX_JSON_API') ) {
				global $obj;
				echo json_encode( $obj->returnHandler( FALSE, 'EXCEPTION', TTi18n::getText('%1 experienced a general error, please contact technical support.', array( APPLICATION_NAME ) ) ) );
				exit;
			} else {
				Redirect::Page( URLBuilder::getURL( array('exception' => 'GeneralError'), Environment::getBaseURL().'DownForMaintenance.php') );
				exit;
			}
		}
	}
}
?>
