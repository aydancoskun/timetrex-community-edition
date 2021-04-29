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
class LockFile {
	var $file_name = null;

	var $max_lock_file_age = 86400;
	var $use_pid = true;

	/**
	 * LockFile constructor.
	 * @param $file_name
	 */
	function __construct( $file_name ) {
		$this->file_name = $file_name;

		return true;
	}

	/**
	 * @return null
	 */
	function getFileName() {
		return $this->file_name;
	}

	/**
	 * @param $file_name
	 * @return bool
	 */
	function setFileName( $file_name ) {
		if ( $file_name != '' ) {
			$this->file_name = $file_name;

			return true;
		}

		return false;
	}

	/**
	 * @return bool|int
	 */
	function getCurrentPID() {
		if ( $this->use_pid == true && function_exists( 'getmypid' ) == true ) {
			$retval = getmypid();
			Debug::Text( 'Current PID: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}

		return false;
	}

	/**
	 * @param int $pid Process ID
	 * @return bool|null
	 */
	function isPIDRunning( $pid ) {
		if ( $this->use_pid == true && (int)$pid > 0 && function_exists( 'posix_getpgid' ) == true ) {
			Debug::Text( 'Checking if PID is running: ' . $pid, __FILE__, __LINE__, __METHOD__, 10 );
			if ( posix_getpgid( $pid ) === false ) {
				Debug::Text( '  PID is NOT running!', __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			} else {
				Debug::Text( '  PID IS running!', __FILE__, __LINE__, __METHOD__, 10 );

				return true;
			}
		} else {
			//Debug::Text( 'PID is invalid or POSIX functions dont exist: ' . $pid, __FILE__, __LINE__, __METHOD__, 10 );
			if ( OPERATING_SYSTEM == 'WIN' ) {
				Debug::Text( 'Checking if PID is running on Windows: ' . $pid, __FILE__, __LINE__, __METHOD__, 10 );
				$processes = explode( "\n", shell_exec( 'tasklist.exe' ) );
				if ( is_array( $processes ) ) {
					foreach ( $processes as $process ) {
						if ( trim( $process ) == '' || strpos( "Image Name", $process ) === 0 || strpos( "===", $process ) === 0 ) {
							continue;
						}

						$matches = false;
						preg_match( "/(.*?)\s+(\d+).*$/", $process, $matches );
						if ( isset( $matches[2] ) && $pid == trim( $matches[2] ) ) {
							Debug::Text( '  PID IS running!', __FILE__, __LINE__, __METHOD__, 10 );

							return true;
						}
					}

					Debug::Text( '  PID is NOT running!', __FILE__, __LINE__, __METHOD__, 10 );

					return false;
				}
			}
		}

		return null; //Assuming the process is still running if the file exists and PID is invalid.
	}

	/**
	 * @return bool|int
	 */
	function create() {
		//Attempt to create directory if it does not already exist.
		$dir = dirname( $this->getFileName() );
		if ( file_exists( $dir ) == false ) {
			$mkdir_result = @mkdir( $dir, 0777, true ); //ugo+rwx
			if ( $mkdir_result == false ) {
				Debug::Text( 'ERROR: Unable to create lock file directory: ' . $dir, __FILE__, __LINE__, __METHOD__, 10 );
			} else {
				Debug::Text( 'WARNING: Created lock file directory as it didnt exist: ' . $dir, __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		//Write current PID to file, so we can check if its still running later on.
		$retval = @file_put_contents( $this->getFileName(), $this->getCurrentPID() );
		@chmod( $this->getFileName(), 0660 ); //ug+rw

		return $retval;
	}

	/**
	 * @return bool
	 */
	function delete() {
		if ( file_exists( $this->getFileName() ) ) {
			return @unlink( $this->getFileName() );
		}

		Debug::text( ' Failed deleting lock file: ' . $this->file_name, __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return bool|null
	 */
	function exists() {
		//Ignore lock files older than max_lock_file_age, so if the server crashes or is rebooted during an operation, it will start again the next day.
		clearstatcache();
		//if ( file_exists( $this->getFileName() ) AND @filemtime( $this->getFileName() ) >= ( time() - $this->max_lock_file_age ) ) {
		if ( file_exists( $this->getFileName() ) ) {
			$lock_file_pid = (int)@file_get_contents( $this->getFileName() );
			Debug::text( ' Lock file exists with PID: ' . $lock_file_pid, __FILE__, __LINE__, __METHOD__, 10 );

			//Check to see if PID is still running or not.
			$pid_running = $this->isPIDRunning( $lock_file_pid );
			if ( $pid_running !== null ) {
				//PID result is reliable, use it.
				return $pid_running;
			} else if ( @filemtime( $this->getFileName() ) >= ( time() - $this->max_lock_file_age ) ) {
				//PID result may not be reliable, fall back to using file time instead.
				return true;
			}
		}

		return false;
	}
}

?>
