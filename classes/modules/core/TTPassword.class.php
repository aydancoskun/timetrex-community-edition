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
class TTPassword {
	static protected $latest_password_version = 3;

	/**
	 * @return int
	 */
	static function getLatestVersion() {
		return self::$latest_password_version;
	}

	/**
	 * @return string
	 */
	static function getPasswordSalt() {
		global $config_vars;

		if ( isset($config_vars['other']['salt']) AND $config_vars['other']['salt'] != '' ) {
			$retval = $config_vars['other']['salt'];
		} else {
			$retval = 'ttsalt03198238';
		}

		return trim($retval);
	}

	static function generateRandomPassword() {
		$password = substr( sha1( uniqid( self::getPasswordSalt(), TRUE ) ), 0, 12 ); //12 digit random password.

		return $password;
	}

	/**
	 * @param bool $encrypted_password
	 * @return int|mixed
	 */
	static function getPasswordVersion( $encrypted_password ) {
		$split_password = explode(':', $encrypted_password );
		if ( is_array($split_password) AND count($split_password) == 2 ) {
			$version = $split_password[0];
		} else {
			$version = 1;
		}

		return $version;
	}

	/**
	 * @param $password
	 * @param int $version
	 * @return string
	 */
	static function encryptPassword( $password, $id1 = NULL, $id2 = NULL, $version = NULL ) {
		//Always default to latest password version.
		if ( $version == '' ) {
			$version = self::$latest_password_version;
		}

		$password = trim($password);

		//Handle password migration/versioning
		switch( (int)$version ) {
			case 2: //v2
				//Case sensitive, uses sha512 and company/user specific salt.
				//Prepend with password version.
				//
				//IMPORTANT: When creating a new user, the ID must be defined before this is called, otherwise the hash is incorrect.
				//           This manifests itself as an incorrect password when its first created, but can be changed and then starts working.
				//
				//NOTE: After upgrade to UUIDs, we must convert UUIDs to integers for v2 hashes to work. All new v3+ hashes will use UUIDs instead.
				$encrypted_password = '2:'. hash( 'sha512', self::getPasswordSalt() . TTUUID::convertUUIDtoInt( $id1 ) . TTUUID::convertUUIDToInt( $id2 ) . $password );
				break;
			case 3: //v3 that uses UUIDs
				$encrypted_password = '3:'. hash( 'sha512', self::getPasswordSalt() . TTUUID::castUUID( $id1 ) . TTUUID::castUUID( $id2 ) . $password );
				break;
			default: //v1
				//Case insensitive, uses sha1 and global salt.
				$encrypted_password = sha1( self::getPasswordSalt() . strtolower($password) );
				break;
		}
		unset($password);

		return $encrypted_password;
	}

	static function checkPassword( $user_entered_password, $database_password ) {
		if ( $user_entered_password === $database_password ) {
			return TRUE;
		}

		return FALSE;
	}
}
?>
