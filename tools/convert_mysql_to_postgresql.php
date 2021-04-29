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

/*
 Proceedure to Convert MySQL to PostgreSQL:

 For instructions, please see: https://forums.timetrex.com/viewtopic.php?f=6&t=7519&p=23173
*/


if ( $argc < 2 || in_array( $argv[1], [ '--help', '-help', '-h', '-?' ] ) ) {
	$help_output = "Usage: convert_mysql_to_postgresql.php [data]\n";
	$help_output .= " [data] = 'truncate'\n";
	echo $help_output;
} else {
	//Handle command line arguments
	$last_arg = count( $argv ) - 1;

	if ( isset( $db ) && is_object( $db ) && strncmp( $db->databaseType, 'mysql', 5 ) != 0 ) {
		echo "ERROR: This script must be run on MySQL only!";
		exit( 255 );
	}

	if ( isset( $argv[$last_arg] ) && $argv[$last_arg] != '' ) {
		$type = trim( strtolower( $argv[$last_arg] ) );

		$dict = NewDataDictionary( $db );
		$tables = $dict->MetaTables();

		$sequence_modifier = 1000;

		$out = null;
		foreach ( $tables as $table ) {
			if ( $type == 'truncate' ) {
				echo 'TRUNCATE ' . $table . ';' . "\n";
			}
		}
	}
}

//Debug::Display();
?>
