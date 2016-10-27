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

require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'global.inc.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'CLI.inc.php');

//Debug::setBufferOutput(FALSE);
//Debug::setEnable(FALSE);
//Debug::setVerbosity(0);

/*
Debug::setBufferOutput(TRUE);
Debug::setEnable(TRUE);
Debug::setEnableDisplay(TRUE);
//Debug::setVerbosity(11);
Debug::setVerbosity(10);
*/

//Allow CLI scripts to run much longer.
ini_set( 'max_execution_time', 86400 );
ini_set( 'memory_limit', '1024M' );

if ( $argc < 2 OR in_array ($argv[1], array('--help', '-help', '-h', '-?') ) ) {
	$help_output = "Usage: create_demo_data.php [OPTIONS]\n";
	$help_output .= "  Options:\n";
	$help_output .= "    -f (Force creating data even if DEMO_MODE is not enabled. *NOT RECOMMENDED*)\n";
	$help_output .= "    -s [Numeric USER NAME suffix, ie: '100' to create user names like: 'demoadmin100']\n";
	$help_output .= "    -n [Number of random users to create above 25]\n";

	echo $help_output;
} else {
	if ( in_array('-s', $argv) ) {
		$data['suffix'] = trim($argv[(array_search('-s', $argv) + 1)]);
	} else {
		$data['suffix'] = '1';
	}

	if ( in_array('-n', $argv) ) {
		$data['random_users'] = (int)trim($argv[(array_search('-n', $argv) + 1)]);
	} else {
		$data['random_users'] = 0;
	}

	if ( in_array('-f', $argv) ) {
		$data['force'] = TRUE;
	} else {
		$data['force'] = FALSE;
	}

	$config_vars['other']['demo_mode'] = TRUE;
	$config_vars['other']['enable_plugins'] = FALSE; //Disable plugins as they shouldn't be needed and likely just cause problems.

	if ( DEMO_MODE == TRUE OR $data['force'] === TRUE ) {
		SystemSettingListFactory::setSystemSetting( 'system_version', APPLICATION_VERSION );
		SystemSettingListFactory::setSystemSetting( 'tax_engine_version', '1.1.0' );
		SystemSettingListFactory::setSystemSetting( 'tax_data_version', date('Ymd') );
		
		Debug::Text('Generating DEMO Data...', __FILE__, __LINE__, __METHOD__, 10);
		echo "UserName suffix: ". $data['suffix'] ." Max Random Users: ". $data['random_users'] ."<br>\n";
		sleep(1);

		$dd = new DemoData();
		$dd->setMaxRandomUsers( $data['random_users'] );
		$dd->setUserNamePostFix( $data['suffix'] );
		$dd->generateData();
		Debug::Text('Done Generating DEMO Data!', __FILE__, __LINE__, __METHOD__, 10);
	} else {
		echo "DEMO MODE IS NOT ENABLED!<br>\n";
		exit(1);
	}
}
Debug::WriteToLog();
//Debug::Display();
?>
