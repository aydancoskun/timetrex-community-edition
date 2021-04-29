<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2021 TimeTrex Software Inc.
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
class TTUUID {
	protected static $uuid_counter = 1;

	/**
	 * @return int|string
	 */
	static function getZeroID() {
		global $PRIMARY_KEY_IS_UUID;
		if ( $PRIMARY_KEY_IS_UUID == false ) {
			return (int)0;
		}

		return '00000000-0000-0000-0000-000000000000';
	}

	/**
	 * @param null $int
	 * @return int|string
	 */
	static function getNotExistID( $int = null ) {
		global $PRIMARY_KEY_IS_UUID;
		if ( $PRIMARY_KEY_IS_UUID == false ) {
			return (int)-1;
		}

		if ( is_numeric( $int ) ) {

			return 'ffffffff-ffff-ffff-ffff-' . str_pad( substr( abs( $int ), 0, 12 ), 12, 0, STR_PAD_LEFT );
		} else {
			return 'ffffffff-ffff-ffff-ffff-ffffffffffff';
		}
	}

	/**
	 * Create an ORDERED UUID https://www.percona.com/blog/2014/12/19/store-uuid-optimized-way/
	 *   This is about 20-30% faster than generateUUIDOld() and should contain more random data, reducing chance of collisions when running in parallel (ie: unit tests)
	 * @param null $seed
	 * @return string
	 */
	static function generateUUID( $seed = null ) {
		if ( $seed == null || strlen( $seed ) !== 12 ) {
			$seed = self::getSeed( true );
		}

		// 7 bit micro-time using real microsecond precision, as both microtime(1) and array_sum(explode(' ', microtime())) are limited by php.ini precision
		$split_time = explode( ' ', microtime( false ) );

		//On 32bit PHP installs (especially Windows), the microtime() resolution isn't high enough (only 1/64 of a second) and can cause many UUID duplicates in tight loops. Supplement the timer with a counter instead.
		//  Need to increment the "sec" portion of microtime( false ) before combining it with the msec portion below so it stays within a 32-bit integer.
		if ( PHP_INT_SIZE === 4 ) { //32bit
			$split_time[1] += self::$uuid_counter;
			self::$uuid_counter++;
		}

		$time_micro_second = $split_time[1] . substr( $split_time[0], 2, 6 );                                                                    //Remove precision on the microsecond portion.

		// Convert to 56-bit integer (7 bytes), enough to store micro time is enough up to 4253-05-31 22:20:37
		$time = base_convert( $time_micro_second, 10, 16 );

		// Left pad the eventual gap and to make sure its always an even number of characters, append 3 random bytes (2^24 = 16 777 216 combinations), then finally convert to hex. This should be 20 characters in total, as the seed appended below is 12 = 32.
		$uuid = str_pad( $time, 14, '0', STR_PAD_LEFT ) . bin2hex( openssl_random_pseudo_bytes( 3 ) );

		//Add separators so its human readable.
		$uuid = substr( $uuid, 0, 8 ) . '-' . substr( $uuid, 8, 4 ) . '-' . substr( $uuid, 12, 4 ) . '-' . substr( $uuid, 16, 4 ) . '-' . $seed; //$seed could be replaced by: substr( $uuid, 20, 12 )

		//For testing, the two strings should match exactly.
		//var_dump($uuid.$seed, str_replace( '-', '', $retval) );

		return $uuid;
	}

	/**
	 * @param string $uuid UUID
	 * @param bool $allow_null
	 * @return int|string
	 */
	static function castUUID( $uuid, $allow_null = false ) {
		//@see comment in isUUID

		//During upgrade from V10.x (pre-UUID) to v11 (post-UUID), we need numeric IDs to be left as integers to avoid SQL errors.
		global $PRIMARY_KEY_IS_UUID;
		if ( $PRIMARY_KEY_IS_UUID == false ) {
			return (int)$uuid;
		}

		//Allow NULLs for cases where the column allows it.
		$uuid = ( is_string( $uuid ) ) ? trim( $uuid ) : $uuid;
		if ( ( $uuid === null && $allow_null == true ) || self::isUUID( $uuid ) == true ) {
			return $uuid;
		}

		return self::getZeroID();
	}

	/**
	 * @param bool $exact_string
	 * @return string
	 */
	static function getRegex( $exact_string = true ) {
		$regex = '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}';
		if ( $exact_string === true ) {
			return '/^' . $regex . '$/';
		} else {
			return '/' . $regex . '/';
		}
	}

	/**
	 * @param string $uuid UUID
	 * @return bool
	 */
	static function isUUID( $uuid ) {
		//Must be strict enough to enfore PostgreSQL UUID storage standard (all lower, no '{}' must have dashes)
		//  enforce this here so we can be sure that '$a->getID() == $b->getID()' comparisons always work.

		//During upgrade from V10.x (pre-UUID) to v11 (post-UUID), we need numeric IDs to be left as integers to avoid SQL errors.
		global $PRIMARY_KEY_IS_UUID;
		if ( $PRIMARY_KEY_IS_UUID == false && is_numeric( $uuid ) ) {
			return $uuid;
		}

		if ( is_string( $uuid ) && $uuid != '' && preg_match( self::getRegex(), $uuid ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $uuid
	 * @param int $group
	 * @return bool
	 */
	static function getUUIDGroup( $uuid, $group = 4 ) {
		$bits = explode( '-', $uuid );
		if ( isset( $bits[$group] ) ) {
			return $bits[$group];
		}

		return false;
	}

	/**
	 * @param string $uuid UUID
	 * @return int
	 */
	static function convertUUIDtoInt( $uuid ) {
		$bits = explode( '-', $uuid );

		return (int)$bits[( count( $bits ) - 1 )];
	}

	/**
	 * @param $int
	 * @return int|string
	 */
	static function convertIntToUUID( $int ) {
		if ( is_numeric( $int ) ) {
			if ( $int === 0 ) {
				return self::getZeroID();
			} else if ( $int === -1 ) {
				return self::getNotExistID();
			}

			return self::getConversionPrefix() . '-' . str_pad( $int, 12, '0', STR_PAD_LEFT );
		} else {
			return $int;
		}
	}

	/**
	 * @param $str string
	 * @return string
	 */
	static function convertStringToUUID( $str ) {
		$str = str_pad( str_replace( '-', '', $str ),  32, 'f', STR_PAD_LEFT ); //Make sure there is at least enough data to make a full 32 char UUID.

		$retval = substr( $str, 0, 8 ) . '-' . substr( $str, 8, 4 ) . '-' . substr( $str, 12, 4 ) . '-' . substr( $str, 16, 4 ) . '-' . substr( $str, 20, 12 );

		return $retval;
	}

	/**
	 * @param bool $fail_to_random
	 * @return bool|string
	 */
	static function getSeed( $fail_to_random = false ) {
		global $config_vars;
		if ( isset( $config_vars['other']['uuid_seed'] ) && strlen( $config_vars['other']['uuid_seed'] ) == 12 && preg_match( '/^[a-z0-9]{12}$/', $config_vars['other']['uuid_seed'] ) ) {
			return strtolower( trim( $config_vars['other']['uuid_seed'] ) );
		}

		if ( $fail_to_random == true ) {
			Debug::text( '  WARNING: Generating random seed!', __FILE__, __LINE__, __METHOD__, 9 );

			return self::generateRandomSeed();
		}

		return false;
	}

	/**
	 * @return string
	 */
	static function generateRandomSeed() {
		return bin2hex( openssl_random_pseudo_bytes( 6 ) );
	}

	/**
	 * @return bool|string
	 */
	static function generateSeed() {
		//Once the seed is generated, it must not be ever generated to something different. Especially if the upgrade failed half way through and is run later on, or even on a different server.
		global $config_vars;
		if ( isset( $config_vars['other']['uuid_seed'] ) && strlen( $config_vars['other']['uuid_seed'] ) == 12 && preg_match( '/^[a-z0-9]{12}$/', $config_vars['other']['uuid_seed'] ) ) {
			return strtolower( trim( $config_vars['other']['uuid_seed'] ) );
		}

		global $db;

		//Make sure we check that the database/system_setting table exists before we attempt to use it. Otherwise it may fail on initial installation.
		$install_obj = new Install();
		$install_obj->setDatabaseConnection( $db ); //Default connection
		if ( $install_obj->checkSystemSettingTableExists() == true ) {
			$registration_key = SystemSettingFactory::getSystemSettingValueByKey( 'registration_key' );
		} else {
			Debug::text( 'Database or system_setting table does not exist yet, generating temporary registration key...', __FILE__, __LINE__, __METHOD__, 9 );
			$registration_key = md5( uniqid( null, true ) );
		}

		$license = new TTLicense();

		//Make sure the UUID key used for upgrading is as unique as possible, so we can avoid the chance of conflicts as best as possible.
		//  Include the database type and database name to further help make this unique in the event that a database was copied on the same server (hardware_id), it should at least have a different name.
		//  Be sure to use CONFIG_FILE file creation time rather than mtime as the config file gets changed during upgrade/installs and can cause the seed to then change.
		//  Seed should only be exactly 12 characters
		$uuid_seed = substr( sha1( $registration_key . $license->getHardwareID() . $db->databaseType . $db->database . filectime( CONFIG_FILE ) ), 0, 12 );
		$config_vars['other']['uuid_seed'] = $uuid_seed; //Save UUID_SEED to any in memory $config_vars to its able to be used immediately.

		Debug::text( '  Generated Seed: ' . $uuid_seed . ' From Registration Key: ' . $registration_key . ' Hardware ID: ' . $license->getHardwareID() . ' Database Type: ' . $db->databaseType . ' DB Name: ' . $db->database . ' Config File: ' . CONFIG_FILE . ' ctime: ' . filectime( CONFIG_FILE ), __FILE__, __LINE__, __METHOD__, 9 );

		$tmp_config_data = [];
		$tmp_config_data['other']['uuid_seed'] = $uuid_seed;
		if ( isset( $config_vars['other']['primary_company_id'] ) && is_numeric( $config_vars['other']['primary_company_id'] ) ) { //Convert to UUID while we are at it.
			$uuid_primary_company_id = TTUUID::convertIntToUUID( $config_vars['other']['primary_company_id'] );
			$config_vars['other']['primary_company_id'] = $uuid_primary_company_id; //Save UUID primary_company_id to any in memory $config_vars to its able to be used immediately.

			$tmp_config_data['other']['primary_company_id'] = $uuid_primary_company_id;
		}

		if ( $install_obj->writeConfigFile( $tmp_config_data ) !== true ) {
			return false;
		}

		return $uuid_seed;
	}

	/**
	 * @return bool|string
	 */
	static function getConversionPrefix() {
		$uuid_seed = self::generateSeed();
		if ( $uuid_seed !== false ) {
			$uuid_key = $uuid_seed . substr( sha1( $uuid_seed ), 12 ); //Make sure we sha1() the seed just to pad out to at least 24 characters. Make the first 12 characters the original seed for consistency though.

			$uuid_prefix = substr( $uuid_key, 0, 8 ) . '-' . substr( $uuid_key, 8, 2 ) . substr( substr( $uuid_key, -10 ), 0, 2 ) . '-' . substr( substr( $uuid_key, -8 ), 0, 4 ) . '-' . substr( $uuid_key, -4 );

			//Debug::text( 'UUID Key: ' . $uuid_key . ' UUID PREFIX: ' . $uuid_prefix, __FILE__, __LINE__, __METHOD__, 9 );

			return $uuid_prefix;
		}

		return false;
	}

	/**
	 * @param $uuid
	 * @param $length
	 * @param bool $include_dashes
	 * @return string
	 */
	static function truncateUUID( $uuid, $length, $include_dashes = true ) {
		//Re-arrange UUID so most unique data is at the beginning.
		if ( is_numeric( self::getUUIDGroup( $uuid, 4 ) ) && stripos( self::getSeed( false ), self::getUUIDGroup( $uuid, 0 ) ) !== false ) {
			//If its a legacy UUID converted from an INT, the only unique part is group 4, so it needs to be at the begining.
			//  However in cases where the SEED changes in the .ini file for some reason, this won't work anymore. Alternatively we could maybe just check that the first two digits of group 4 are '00' as well as being numeric. The chances of that happening are quite rare, but still possible.
			$tmp_uuid = self::getUUIDGroup( $uuid, 4 ) . '-' . self::getUUIDGroup( $uuid, 1 ) . '-' . self::getUUIDGroup( $uuid, 2 ) . '-' . self::getUUIDGroup( $uuid, 3 ) . '-' . self::getUUIDGroup( $uuid, 0 );
		} else {
			$tmp_uuid = self::getUUIDGroup( $uuid, 1 ) . '-' . self::getUUIDGroup( $uuid, 2 ) . '-' . self::getUUIDGroup( $uuid, 3 ) . '-' . self::getUUIDGroup( $uuid, 0 ) . '-' . self::getUUIDGroup( $uuid, 4 );
		}

		if ( $include_dashes == false ) {
			$tmp_uuid = str_replace( '-', '', $tmp_uuid );
		}

		return trim( substr( $tmp_uuid, 0, $length ), '-' );
	}
}

?>
