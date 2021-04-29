<?php /** @noinspection PhpUndefinedVariableInspection */
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

/*
 * Checks for any version updates...
 *
 */
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php' );

//
//Check system requirements.
//
if ( PRODUCTION == true && DEPLOYMENT_ON_DEMAND == false ) {
	Debug::Text( 'Checking system requirements... ' . TTDate::getDate( 'DATE+TIME', time() ), __FILE__, __LINE__, __METHOD__, 10 );
	$install_obj = new Install();
	$failed_requirment_requirements = $install_obj->getFailedRequirements( false, [ 'base_url', 'clean_cache', 'file_checksums' ] );

	if ( is_array( $failed_requirment_requirements ) && count( $failed_requirment_requirements ) > 1 ) {
		SystemSettingFactory::setSystemSetting( 'valid_install_requirements', 0 );
		Debug::Text( 'Failed system requirements: ' . implode( $failed_requirment_requirements ), __FILE__, __LINE__, __METHOD__, 10 );
		TTLog::addEntry( 0, 510, 'Failed system requirements: ' . implode( $failed_requirment_requirements ), 0, 'company' );
	} else {
		SystemSettingFactory::setSystemSetting( 'valid_install_requirements', 1 );
	}

	unset( $install_obj, $check_all_requirements );
	Debug::Text( 'Checking system requirements complete... ' . TTDate::getDate( 'DATE+TIME', time() ), __FILE__, __LINE__, __METHOD__, 10 );
}

//
// Purge database tables
//
if ( !isset( $config_vars['other']['disable_database_purging'] )
		|| isset( $config_vars['other']['disable_database_purging'] ) && $config_vars['other']['disable_database_purging'] != true ) {
	PurgeDatabase::Execute();
}

//
// Clean cache directories
// - Make sure cache directory is set, and log/storage directories are not contained within it.
//
if ( !isset( $config_vars['other']['disable_cache_purging'] )
		|| isset( $config_vars['other']['disable_cache_purging'] ) && $config_vars['other']['disable_cache_purging'] != true ) {

	if ( isset( $config_vars['cache']['dir'] )
			&& $config_vars['cache']['dir'] != ''
			&& strpos( $config_vars['path']['log'], $config_vars['cache']['dir'] ) === false
			&& strpos( $config_vars['path']['storage'], $config_vars['cache']['dir'] ) === false ) {

		Debug::Text( 'Purging Cache directory: ' . $config_vars['cache']['dir'] . ' - ' . TTDate::getDate( 'DATE+TIME', time() ), __FILE__, __LINE__, __METHOD__, 10 );
		$install_obj = new Install();
		$install_obj->cleanCacheDirectory( '' ); //Don't exclude .ZIP files, so if there is a corrupt one it will be redownloaded within a week.
		Debug::Text( 'Purging Cache directory complete: ' . TTDate::getDate( 'DATE+TIME', time() ), __FILE__, __LINE__, __METHOD__, 10 );
	} else {
		Debug::Text( 'Cache directory is invalid: ' . TTDate::getDate( 'DATE+TIME', time() ), __FILE__, __LINE__, __METHOD__, 10 );
	}
}

//
//Check for severely out of date versions and take out of production mode if necessary.
//
if ( PRODUCTION == true && DEPLOYMENT_ON_DEMAND == false && ( ( time() - (int)APPLICATION_VERSION_DATE ) > ( 86400 * 455 ) ) ) {
	Debug::Text( 'ERROR: Application version is severely out of date, changing production mode... ', __FILE__, __LINE__, __METHOD__, 10 );
	$install_obj = new Install();
	$tmp_config_vars['debug']['production'] = 'FALSE';
	$write_config_result = $install_obj->writeConfigFile( $tmp_config_vars );
	unset( $install_obj, $tmp_config_vars, $write_config_result );
}

Debug::writeToLog();
Debug::Display();
?>