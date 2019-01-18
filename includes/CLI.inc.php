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

//Allow only CLI PHP binaries to call maint scripts. To avoid a remote party from running them from hitting a URL.
if ( PHP_SAPI != 'cli' ) {
	echo "This script can only be called from the Command Line. (". PHP_SAPI .")\n";
	exit(1);
}

//There appears to be cases where ARGC/ARGV may not be set, so check those too. Fixes: PHP ERROR - NOTICE(8): Undefined variable: argc File: C:\TimeTrex\timetrex\tools\unattended_install.php Line: 31
if ( !isset($argc) OR !isset($argv) ) {
	echo "This script can only be called from the Command Line. (args)\n";
	exit(1);
}

if ( version_compare( PHP_VERSION, 5, '<') == 1 ) {
	echo "You are currently using PHP v". PHP_VERSION ." TimeTrex requires PHP v5 or greater!\n";
	exit(1);
}

//Allow CLI scripts to run much longer. ie: Purging database could takes hours.
ini_set( 'max_execution_time', 86000 ); //Just less than 24hrs, so scripts that run daily can't build up.

$install_obj = new Install();

//Make sure CLI tools are not being run as root, otherwise show error message and attempt to down-grade users.
if ( Misc::isCurrentOSUserRoot() == TRUE ) {
	fwrite(STDERR, 'ERROR: Running as \'root\' forbidden! To avoid permission conflicts, must run as the web-server user instead.'."\n" );
	fwrite(STDERR, '       Example: su www-data -c "'. ( ( isset($config_vars['path']['php_cli']) ) ? $config_vars['path']['php_cli'] : 'php' ) .' '. implode(' ', ( ( isset($argv) ) ? $argv : array() ) ) .'"'."\n" );
	Debug::Text('WARNING: Running as OS user \'root\' forbidden!', __FILE__, __LINE__, __METHOD__, 10);

	//Before we down-grade user privileges, check to make sure we can read/write all necessary files.
	$install_obj->checkFilePermissions();
	if ( Misc::setProcessUID( Misc::findWebServerOSUser() ) != TRUE ) {
		Debug::Display();
		Debug::writeToLog();
		exit(1);
	}
}

//Check post install requirements, because PHP CLI usually uses a different php.ini file.
if ( $install_obj->checkAllRequirements( TRUE ) == 1 ) {
	$failed_requirements = $install_obj->getFailedRequirements( TRUE );
	unset($failed_requirements[0]);
	echo "----WARNING----WARNING----WARNING-----\n";
	echo "--------------------------------------\n";
	echo "Minimum PHP Requirements are NOT met!!\n";
	echo "--------------------------------------\n";
	echo "Failed Requirements: ".implode(',', (array)$failed_requirements )." \n";
	echo "--------------------------------------\n";
	echo "PHP INI: ". $install_obj->getPHPConfigFile() ." \n";
	echo "Process Owner: ". $install_obj->getWebServerUser() ." \n";
	echo "--------------------------------------\n\n\n";
}
unset($install_obj);

TTi18n::chooseBestLocale(); //Make sure a locale is set, specifically when generating PDFs.

//Uncomment the below block to force debug logging with maintenance jobs.
/*
Debug::setEnable( TRUE );
Debug::setBufferOutput( TRUE );
Debug::setEnableLog( TRUE );
if ( Debug::getVerbosity() <= 1 ) {
	Debug::setVerbosity( 1 );
}
*/
?>