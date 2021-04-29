<?php /** @noinspection PhpUndefinedVariableInspection */
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

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php' );

//
// Update PRIMARY_COMPANY_ID if its invalid or does not exist anymore.
//
$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
$clf->getByID( PRIMARY_COMPANY_ID );
Debug::text( 'Primary Company ID: ' . PRIMARY_COMPANY_ID, __FILE__, __LINE__, __METHOD__, 10 );
if ( $clf->getRecordCount() != 1 ) {
	//Get all companies and try to determine which one should be the primary, based on created date and status.
	$clf->getAll( 1, null, [ 'status_id' => '= 10' ], [ 'created_date' => 'asc' ] );
	Debug::text( '  Total Companies: ' . $clf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
	if ( $clf->getRecordCount() > 0 ) {
		foreach ( $clf as $c_obj ) {
			if ( $c_obj->getDeleted() == false && $c_obj->getStatus() == 10 ) { //10=Active
				Debug::text( '  Setting PRIMARY_COMPANY_ID to: ' . $c_obj->getId() . ' Name: ' . $c_obj->getName(), __FILE__, __LINE__, __METHOD__, 10 );
				$install_obj = new Install();
				$tmp_config_vars['other']['primary_company_id'] = (string)$c_obj->getId();
				$write_config_result = $install_obj->writeConfigFile( $tmp_config_vars );
				unset( $install_obj, $tmp_config_vars, $write_config_result );

				break;
			}
		}
	}
} else {
	Debug::text( '  Valid PRIMARY_COMPANY_ID, not modifying...', __FILE__, __LINE__, __METHOD__, 10 );
}
unset( $clf, $c_obj );


//
// Set system_timezone .ini setting to the most commonly used value if it hasn't been changed from the default of GMT.
//
if ( TTDate::getTimeZone() == 'GMT' ) {
	$uplf = TTNew( 'UserPreferenceListFactory' ); /** @var UserPreferenceListFactory $uplf */
	$most_common_time_zone = $uplf->getMostCommonTimeZone();
	Debug::text( 'Most Common TimeZone: ' . $most_common_time_zone, __FILE__, __LINE__, __METHOD__, 10 );

	if ( $most_common_time_zone != '' && $most_common_time_zone != 'GMT' ) {
		$install_obj = new Install();
		$tmp_config_vars['other']['system_timezone'] = (string)$most_common_time_zone;
		$write_config_result = $install_obj->writeConfigFile( $tmp_config_vars );
		Debug::text( ' Setting System TimeZone to: ' . $most_common_time_zone, __FILE__, __LINE__, __METHOD__, 10 );
	}

	unset( $uplf, $most_common_time_zone, $install_obj, $tmp_config_vars, $write_config_result );
}


//
// Backup database if script exists.
// Always backup the database first before doing anything else like purging tables.
//
if ( !isset( $config_vars['other']['disable_backup'] )
		|| isset( $config_vars['other']['disable_backup'] ) && $config_vars['other']['disable_backup'] != true ) {
	if ( PHP_OS == 'WINNT' ) {
		$backup_script = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'backup_database.bat';
	} else {
		$backup_script = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'backup_database';
	}
	Debug::Text( 'Backup Database Command: ' . $backup_script, __FILE__, __LINE__, __METHOD__, 10 );
	if ( file_exists( $backup_script ) ) {
		Debug::Text( 'Running Backup: ' . TTDate::getDate( 'DATE+TIME', time() ), __FILE__, __LINE__, __METHOD__, 10 );
		exec( '"' . $backup_script . '"', $output, $retcode );
		Debug::Text( 'Backup Completed: ' . TTDate::getDate( 'DATE+TIME', time() ) . ' RetCode: ' . $retcode, __FILE__, __LINE__, __METHOD__, 10 );

		$backup_history_files = [];

		$backup_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..';
		if ( is_dir( $backup_dir ) && is_readable( $backup_dir ) ) {
			$fh = opendir( $backup_dir );
			while ( ( $file = readdir( $fh ) ) !== false ) {
				# loop through the files, skipping . and .., and recursing if necessary
				if ( strcmp( $file, '.' ) == 0 || strcmp( $file, '..' ) == 0 ) {
					continue;
				}

				$filepath = $backup_dir . DIRECTORY_SEPARATOR . $file;
				if ( !is_dir( $filepath ) ) {
					//Be more strict with regex to avoid: PHP ERROR - WARNING(2): filemtime(): stat failed for C:\TimeTrex\timetrex\maint\..\..\timetrex_database_???.sql File: C:\TimeTrex\timetrex\maint\MiscDaily.php Line: 74
					if ( preg_match( '/timetrex_database_[A-Za-z0-9\-]+\.sql/i', $file ) == 1 ) {
						$file_mtime = @filemtime( $filepath );
						if ( $file_mtime !== false ) {
							$backup_history_files[$file_mtime] = $filepath;
						} else {
							Debug::Text( 'ERROR: Unable to get filemtime on: ' . $filepath, __FILE__, __LINE__, __METHOD__, 10 );
						}
					}
				}
			}
		}
		ksort( $backup_history_files );

		if ( is_array( $backup_history_files ) && count( $backup_history_files ) > 7 ) {
			reset( $backup_history_files );
			$delete_backup_file = current( $backup_history_files );
			Debug::Text( 'Deleting oldest backup: ' . $delete_backup_file . ' Of Total: ' . count( $backup_history_files ), __FILE__, __LINE__, __METHOD__, 10 );
			if ( @unlink( $delete_backup_file ) == false ) { //PHP ERROR - WARNING(2): unlink(C:\TimeTrex\timetrex\maint\..\..\timetrex_database_20160322.sql): Permission denied File: C:\TimeTrex\timetrex\maint\MiscDaily.php Line: 85
				Debug::Text( 'ERROR: Unable to delete backup file, possible permission denied error?', __FILE__, __LINE__, __METHOD__, 10 );
			}
			unset( $delete_backup_file );
		}
	}
	unset( $backup_script, $output, $retcode, $backup_dir, $fh, $file, $filepath, $backup_history_files );
}

//
// Rotate log files
//
if ( !isset( $config_vars['other']['disable_log_rotate'] )
		|| isset( $config_vars['other']['disable_log_rotate'] ) && $config_vars['other']['disable_log_rotate'] != true ) {
	$log_rotate_config[] = [
			'directory' => $config_vars['path']['log'],
			'recurse'   => false,
			'file'      => 'timetrex.log',
			'frequency' => 'DAILY',
			'history'   => 10,
	]; //Keep more than a weeks worth, so we can better diagnose maintenance jobs that just run once per week.

	$log_rotate_config[] = [
			'directory' => $config_vars['path']['log'] . DIRECTORY_SEPARATOR . 'client',
			'recurse'   => true,
			'file'      => '*',
			'frequency' => 'DAILY',
			'history'   => 10,
	];

	$log_rotate_config[] = [
			'directory' => $config_vars['path']['log'] . DIRECTORY_SEPARATOR . 'time_clock',
			'recurse'   => true,
			'file'      => '*',
			'frequency' => 'DAILY',
			'history'   => 10,
	];

	$lr = new LogRotate( $log_rotate_config );
	$lr->Rotate();
}

//
// Check cache file directories and permissions.
//
if ( !isset( $config_vars['other']['disable_cache_permission_check'] )
		|| isset( $config_vars['other']['disable_cache_permission_check'] ) && $config_vars['other']['disable_cache_permission_check'] != true ) {
	if ( isset( $config_vars['cache']['enable'] ) && $config_vars['cache']['enable'] == true && isset( $config_vars['cache']['dir'] ) && $config_vars['cache']['dir'] != '' ) {
		Debug::Text( 'Validating Cache Files/Directory: ' . $config_vars['cache']['dir'], __FILE__, __LINE__, __METHOD__, 10 );

		//Just as a precaution, confirm that cache directory exists, if not try to create it.
		//If the cache directory doesnt exist, then LockFile class can't create lock files, and therefore no cron jobs will run.
		//So also have LockFile class try to create the directory so we can at least get to this point.
		if ( file_exists( $config_vars['cache']['dir'] ) == false ) {
			//Try to create cache directory
			Debug::Text( 'Cache directory does not exist, attempting to create it: ' . $config_vars['cache']['dir'], __FILE__, __LINE__, __METHOD__, 10 );
			$mkdir_result = @mkdir( $config_vars['cache']['dir'], 0777, true );
			if ( $mkdir_result == false ) {
				Debug::Text( 'ERROR: Unable to create cache directory: ' . $config_vars['cache']['dir'], __FILE__, __LINE__, __METHOD__, 10 );
				Misc::disableCaching();
			} else {
				Debug::Text( 'Cache directory created successfully: ' . $config_vars['cache']['dir'], __FILE__, __LINE__, __METHOD__, 10 );
			}
			unset( $mkdir_result );
		}

		//Check all cache files and make sure they are owned by the same users.
		try {
			$prev_file_owner = null;
			$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $config_vars['cache']['dir'], FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
			foreach ( $files as $file_obj ) {
				$cache_file = $file_obj->getRealPath(); //Check both files and directories.

				$file_owner = @fileowner( $cache_file );
				if ( $prev_file_owner !== null && $file_owner != $prev_file_owner ) {
					Debug::Text( 'ERROR: Cache directory contains files from several different owners. Its likely that their permission conflict.', __FILE__, __LINE__, __METHOD__, 10 );
					Debug::Text( 'Cache File Owner UIDs: ' . $prev_file_owner . ', ' . $file_owner, __FILE__, __LINE__, __METHOD__, 10 );
					Misc::disableCaching();

					break; //Stop loop as soon as more than one owner is detected.
				}

				$prev_file_owner = $file_owner;
			}
			unset( $prev_file_owner, $files, $cache_file, $file_owner );
		} catch ( Exception $e ) {
			Debug::Text( 'Failed opening/reading file or directory: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
			Misc::disableCaching();
		}
	}
}


//
// Update Company contacts so they are always valid.
//
$clf = TTNew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
$clf->getAllByInValidContacts();
if ( $clf->getRecordCount() > 0 ) {
	foreach ( $clf as $c_obj ) {
		Debug::Text( 'Attempting to update Company Contacts for Company: ' . $c_obj->getName() . '(' . $c_obj->getID() . ')', __FILE__, __LINE__, __METHOD__, 10 );
		$default_company_contact_user_id = $c_obj->getDefaultContact();
		if ( $default_company_contact_user_id > 0 ) {
			Debug::text( 'Found alternative contact: ' . $default_company_contact_user_id, __FILE__, __LINE__, __METHOD__, 10 );

			$user_obj = $c_obj->getUserObject( $c_obj->getAdminContact() );
			if ( !is_object( $user_obj ) || ( is_object( $user_obj ) && $user_obj->getStatus() == 10 && $user_obj->getId() != $default_company_contact_user_id ) ) {
				$c_obj->setAdminContact( $default_company_contact_user_id );
				Debug::text( 'Replacing Admin Contact with: ' . $default_company_contact_user_id, __FILE__, __LINE__, __METHOD__, 10 );
			}

			$user_obj = $c_obj->getUserObject( $c_obj->getBillingContact() );
			if ( !is_object( $user_obj ) || ( is_object( $user_obj ) && $user_obj->getStatus() == 10 && $user_obj->getId() != $default_company_contact_user_id ) ) {
				$c_obj->setBillingContact( $default_company_contact_user_id );
				Debug::text( 'Replacing Billing Contact with: ' . $default_company_contact_user_id, __FILE__, __LINE__, __METHOD__, 10 );
			}

			$user_obj = $c_obj->getUserObject( $c_obj->getSupportContact() );
			if ( !is_object( $user_obj ) || ( is_object( $user_obj ) && $user_obj->getStatus() == 10 && $user_obj->getId() != $default_company_contact_user_id ) ) {
				$c_obj->setSupportContact( $default_company_contact_user_id );
				Debug::text( 'Replacing Support Contact with: ' . $default_company_contact_user_id, __FILE__, __LINE__, __METHOD__, 10 );
			}

			if ( $c_obj->isValid() ) {
				Debug::Text( 'Saving company record...', __FILE__, __LINE__, __METHOD__, 10 );
				$c_obj->Save();
			}
		} else {
			Debug::Text( 'Unable to find default contact!', __FILE__, __LINE__, __METHOD__, 10 );
		}
	}
}
unset( $clf, $c_obj, $default_company_contact_user_id, $user_obj );

//
// Expire Logins
//
$ulf = TTNew( 'UserListFactory' ); /** @var UserListFactory $ulf */
$ulf->disableExpiredLogins( time() );

Debug::writeToLog();
Debug::Display();
?>