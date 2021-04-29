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

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php' );

if ( $argc < 2 || in_array( $argv[1], [ '--help', '-help', '-h', '-?' ] ) ) {
	$help_output = "Usage: uuid.php [integer_to_convert]\n";
	echo $help_output;
} else {
	//Handle command line arguments
	$last_arg = count( $argv ) - 1;

	if ( in_array( '--random_seed', $argv ) ) {
		echo "Random Seed: uuid_seed = " . TTUUID::generateRandomSeed() . "\n";
	} else if ( in_array( '--primary_company', $argv ) ) {
		echo "Primary Company ID: " . PRIMARY_COMPANY_ID . " UUID: " . TTUUID::convertIntToUUID( PRIMARY_COMPANY_ID ) . "\n";
	} else if ( in_array( '--benchmark', $argv ) ) {
		$start_time = microtime( true );
		$max = 1000000;
		for ( $i = 0; $i < $max; $i++ ) {
			$uuid_arr[] = TTUUID::generateUUID();
		}
		$end_time = microtime( true );

		$unique_uuid_arr = array_unique( $uuid_arr );
		echo "  Raw UUIDs: " . count( $uuid_arr ) . " Unique UUIDs: " . count( $unique_uuid_arr ) . "\n\n";
		flush();
		ob_flush();

		//Test large timestamps.
		$start_time = microtime( true );
		for ( $i = 0; $i < $max; $i++ ) {
			$timestamps_arr[] = microtime( true ) * 10000000 + 0x01b21dd213814000;
		}
		$end_time = microtime( true );
		$unique_timestamps_arr = array_unique( $timestamps_arr );
		echo "  Raw Large TimeStamps: " . count( $timestamps_arr ) . " Unique Large TimeStamps: " . count( $unique_timestamps_arr ) . " Time: " . ( $end_time - $start_time ) . "s\n";
		unset( $timestamps_arr, $unique_timestamps_arr );

		//Test large timestamps with counter
		for ( $i = 0; $i < $max; $i++ ) {
			$timestamps_arr[] = microtime( true ) * 10000000 + 0x01b21dd213814000 + $i;
		}
		$unique_timestamps_arr = array_unique( $timestamps_arr );
		echo "  Raw Counter TimeStamps: " . count( $timestamps_arr ) . " Unique Counter TimeStamps: " . count( $unique_timestamps_arr ) . "\n";
		unset( $timestamps_arr, $unique_timestamps_arr );

		//Test regular timestamps.
		for ( $i = 0; $i < $max; $i++ ) {
			$timestamps_arr[] = microtime( true );
		}
		$unique_timestamps_arr = array_unique( $timestamps_arr );
		echo "  Raw TimeStamps: " . count( $timestamps_arr ) . " Unique TimeStamps: " . count( $unique_timestamps_arr ) . "\n";
		unset( $timestamps_arr, $unique_timestamps_arr );

		//Test random bytes.
		$strong_crypto = false;
		for ( $i = 0; $i < $max; $i++ ) {
			$random_bytes_arr[] = bin2hex( openssl_random_pseudo_bytes( 3, $strong_crypto ) );
			if ( $strong_crypto == false ) {
				echo "ERROR: openssl not using strong crypto!\n";
				exit;
			}
		}
		$unique_random_bytes_arr = array_unique( $random_bytes_arr );
		echo "  Raw Random Bytes: " . count( $random_bytes_arr ) . " Unique Random Bytes: " . count( $unique_random_bytes_arr ) . "\n";
		unset( $random_bytes_arr, $unique_random_bytes_arr );

		//Test timestamps + random bytes.
		for ( $i = 0; $i < $max; $i++ ) {
			$pseudo_uuid_arr[] = microtime( true ) * 10000000 + 0x01b21dd213814000 . bin2hex( openssl_random_pseudo_bytes( 3 ) );
		}
		$unique_pseudo_uuid_arr = array_unique( $pseudo_uuid_arr );
		echo "  Raw Psuedo UUIDs: " . count( $pseudo_uuid_arr ) . " Unique Psuedo UUID: " . count( $unique_pseudo_uuid_arr ) . "\n";
		unset( $pseudo_uuid_arr, $unique_pseudo_uuid_arr );


		$strong_crypto = false;
		$random_bytes = bin2hex( openssl_random_pseudo_bytes( 3, $strong_crypto ) );
		if ( $strong_crypto == true ) {
			echo "  SUCCESS: Strong crypto algorithm is being used!\n";
		} else {
			echo "  WARNING: Weak crypto algorithm is being used!\n";
		}
		unset( $strong_crypto, $random_bytes );

		echo "Total Time for " . $max . " UUIDs: " . ( $end_time - $start_time ) . "s\n";

		if ( count( $uuid_arr ) == count( $unique_uuid_arr ) ) {
			echo "SUCCESS: No Duplicate UUIDs detected!\n";
		} else {
			echo "ERROR: Duplicate UUID generation detected!\n";
		}
		unset( $uuid_arr, $unique_uuid_arr );
	} else {
		if ( isset( $argv[$last_arg] ) && $argv[$last_arg] != '' ) {
			$integer = $argv[$last_arg];
		} else {
			$integer = 0;
		}

		$uuid = TTUUID::convertIntToUUID( $integer );
		echo "Integer: " . $integer . " converts to UUID: " . $uuid . "\n";

		//PGSQL:
		echo "\n";
		echo "To convert integer ID columns to UUID in PostgreSQL use the following example query: \n";
		echo "ALTER TABLE <table> ALTER COLUMN <column> DROP DEFAULT, ALTER COLUMN <column> SET DATA TYPE UUID USING uuid( concat( '" . TTUUID::getConversionPrefix() . "-', lpad( text( <column> ), 12, '0' ) ) );\n";
	}
}
echo "\n";

//Debug::Display();
Debug::writeToLog();
?>
