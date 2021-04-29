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

//This must go above include for global.inc.php
if ( isset( $argv ) AND in_array( '--config', $argv ) ) {
	$_SERVER['TT_CONFIG_FILE'] = trim( $argv[ ( array_search( '--config', $argv ) + 1 ) ] );
}

//If requirements only check is enabled, do not connect to the database just in case the database isnt setup yet or setup incorrectly.
if ( isset( $argv ) AND in_array( '--requirements_only', $argv ) ) {
	$disable_database_connection = true;
}

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php' );

//Always enable debug logging during upgrade.
Debug::setEnable( true );
Debug::setBufferOutput( true );
Debug::setEnableLog( true );
Debug::setVerbosity( 10 );
ignore_user_abort( true );
ini_set( 'default_socket_timeout', 5 );
ini_set( 'allow_url_fopen', 1 );
ini_set( 'max_execution_time', 0 );
ini_set( 'memory_limit', '2048M' ); //Just in case.

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php' );

//Since we aren't including database.inc.php, force the timezone to be set to avoid   WARNING(2): getdate(): It is not safe to rely on the system's timezone settings. You are *required* to use the date.timezone setting or the date_default_timezone_set()
//Sometimes scripts won't make a database connection
if ( !isset( $config_vars['other']['system_timezone'] ) OR ( isset( $config_vars['other']['system_timezone'] ) AND $config_vars['other']['system_timezone'] == '' ) ) {
	$config_vars['other']['system_timezone'] = @date( 'e' );
}
if ( $config_vars['other']['system_timezone'] == '' ) {
	$config_vars['other']['system_timezone'] = 'GMT';
}
TTDate::setTimeZone( $config_vars['other']['system_timezone'], false, false ); //Don't force SQL to be executed here, as an optimization to avoid DB connections when calling things like getProgressBar()

//Re-initialize install object with new config file.
$install_obj = new Install();

if ( isset( $config_vars['other']['primary_company_id'] ) ) {
	$company_id = $config_vars['other']['primary_company_id'];
} else {
	$company_id = null;
}

//The installer already checks the cache directory to make sure its writable, so use that as the upgrade staging directory.
//The cache dir does get cleaned once per week though, but if an upgrade failed that may be helpful.
if ( !isset( $config_vars['cache']['dir'] ) ) { //Just in case the cache directory is not set.
	$config_vars['cache']['dir'] = Environment::getBasePath();
}
$upgrade_staging_dir = $config_vars['cache']['dir'] . DIRECTORY_SEPARATOR . 'upgrade_staging' . DIRECTORY_SEPARATOR;
$upgrade_staging_latest_dir = $upgrade_staging_dir . DIRECTORY_SEPARATOR . 'latest_version';
$upgrade_file_name = $config_vars['cache']['dir'] . DIRECTORY_SEPARATOR . 'UPGRADE.ZIP';
$php_cli = $config_vars['path']['php_cli'];

function moveUpgradeFiles( $upgrade_staging_latest_dir ) {
	$latest_file_list = Misc::getFileList( $upgrade_staging_latest_dir, null, true );
	if ( is_array( $latest_file_list ) AND count( $latest_file_list ) > 0 ) {
		foreach ( $latest_file_list as $latest_file ) {
			$new_file = str_replace( $upgrade_staging_latest_dir, Environment::getBasePath(), $latest_file );

			//Check if directory exists.
			if ( !is_dir( dirname( $new_file ) ) ) {
				Debug::Text( 'Creating new directory: ' . dirname( $new_file ), __FILE__, __LINE__, __METHOD__, 10 );
				if ( @mkdir( dirname( $new_file ), 0755, true ) == false ) { //Read+Write+Execute for owner, Read/Execute for all others.
					Debug::Text( 'ERROR: FAILED TO CREATE DIRECTORY: ' . $new_file, __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
			Debug::Text( 'Moving: ' . $latest_file . ' To: ' . $new_file, __FILE__, __LINE__, __METHOD__, 10 );
			//if ( @rename( $latest_file, $new_file ) == FALSE ) {
			if ( Misc::rename( $latest_file, $new_file ) == false ) {
				Debug::Text( 'ERROR: FAILED TO MOVE: ' . $latest_file . ' To: ' . $new_file, __FILE__, __LINE__, __METHOD__, 10 );
			}
		}
	} else {
		Debug::Text( 'No files to move... Are we running --stage2 perhaps?', __FILE__, __LINE__, __METHOD__, 10 );
	}

	clearstatcache();

	return true;
}

function setAutoUpgradeFailed( $value = 1 ) {
	//When upgrading from pre-UUID versions, its possible if a failure occurs before the schema version upgrade has fully completed,
	// setSystemSetting() will then fail with a PHP fatal error saying it can't find class TTUUID, preventing the error log from being captured.
	if ( class_exists( 'TTUUID' ) == true ) {
		SystemSettingFactory::setSystemSetting( 'auto_upgrade_failed', $value );
		if ( $value == 1 ) {
			Debug::Text( 'ERROR: AutoUpgrade Failed, setting failed flag...', __FILE__, __LINE__, __METHOD__, 10 );
		} else {
			Debug::Text( 'AutoUpgrade Success, clearing failed flag...', __FILE__, __LINE__, __METHOD__, 10 );

			//Clear other messages that likely aren't valid anymore.
			SystemSettingFactory::setSystemSetting( 'valid_install_requirements', 1 );
			SystemSettingFactory::setSystemSetting( 'new_version', 0 );
		}
	}

	return true;
}

function CLIExit( $code = 0, $delete_lock_file = true ) {
	Debug::Display();
	Debug::writeToLog();

	if ( $delete_lock_file == true ) {
		global $lock_file;
		if ( is_object( $lock_file ) ) {
			$lock_file->delete();
		}
	}

	exit( $code );
}

Debug::Text( 'Version: ' . APPLICATION_VERSION . ' (PHP: v' . phpversion() . ') Edition: ' . getTTProductEdition() . ' Production: ' . (int)PRODUCTION . ' Server: ' . ( isset( $_SERVER['SERVER_ADDR'] ) ? $_SERVER['SERVER_ADDR'] : 'N/A' ) . ' Database: Type: ' . ( isset( $config_vars['database']['type'] ) ? $config_vars['database']['type'] : 'N/A' ) . ' Name: ' . ( isset( $config_vars['database']['database_name'] ) ? $config_vars['database']['database_name'] : 'N/A' ) . ' Config: ' . CONFIG_FILE . ' Demo Mode: ' . (int)DEMO_MODE, __FILE__, __LINE__, __METHOD__, 10 );

//Force flush after each output line.
ob_implicit_flush( true );
ob_end_flush();

if ( isset( $argv[1] ) AND in_array( $argv[1], array('--help', '-help', '-h', '-?') ) ) {
	$help_output = "Usage: unattended_upgrade.php\n";
	$help_output .= " [--config] = Config file to use.\n";
	$help_output .= " [--schema_only] = Run a schema upgrade only.\n";
	$help_output .= " [--pre_requirements_update] = Run a pre system requirements update.\n";
	$help_output .= " [--requirements_only] = Run a system requirements check only.\n";
	$help_output .= " [-f] = Force upgrade even if INSTALL mode is disabled.\n";
	echo $help_output;
} else {
	//Create lock file so the same clock isn't being synchronized more then once at a time.
	//  Use arguments in lock file name, so each argument or separate run has a separte lock file.
	$lock_file_name = $config_vars['cache']['dir'] . DIRECTORY_SEPARATOR . 'UnAttended_Upgrade_' . crc32( serialize( $argv ) ); //hash the arguments so we always use a different lock file name when using different arguments.
	$lock_file = new LockFile( $lock_file_name . '.lock' );
	$lock_file->max_lock_file_age = ( 3600 * 3 ); //3 hrs.
	Debug::text( 'Lock File: ' . $lock_file->getFileName(), __FILE__, __LINE__, __METHOD__, 10 );
	if ( $lock_file->exists() == true ) {
		Debug::text( 'Lock File already exists, exiting...', __FILE__, __LINE__, __METHOD__, 10 );
		echo 'Upgrade is already running, please wait for it to finish...' . "\n";
		CLIExit( 253, false ); //Don't delete lock file.
	} else {
		if ( $lock_file->create() == false ) {
			Debug::text( 'Unable to create lock file, likely already exists, exiting...', __FILE__, __LINE__, __METHOD__, 10 );
		}
		//Continue trying to run even if we can't create the lock file.
	}
	unset( $lock_file_name );

	$last_arg = ( count( $argv ) - 1 );

	if ( in_array( '-f', $argv ) ) {
		$force = true;
	} else {
		$force = false;
	}

	//Full force mode, forces upgrade even if the file downloaded is the same version.
	//Primarily should be used when UPGRADE.ZIP already exists.
	if ( in_array( '-ff', $argv ) ) {
		$force = true;
		$full_force = true;
	} else {
		$full_force = false;
	}

	if ( isset( $argv ) AND in_array( '--upgrade_file', $argv ) ) { //Allow forcing a specific file to use for upgrading instead of downloading one.
		$manual_upgrade_file_name = trim( $argv[ ( array_search( '--upgrade_file', $argv ) + 1 ) ] );
	}

	if ( in_array( '--pre_requirements_update', $argv ) ) {
		Debug::Text( 'Running pre-requirements update only...', __FILE__, __LINE__, __METHOD__, 10 );
		if ( $force == true OR version_compare( APPLICATION_VERSION, '10.0.0', '>=' ) == true ) {
			//Cant enable INTL/ZIP extensions, as they won't load on some stack installs...
//			Debug::Text('  Running: v10.0.0 Pre-Requirements Update...', __FILE__, __LINE__, __METHOD__, 10);
//
//			//Check if stack installer was used, if so, attempt to enable INTL extension.
//			if ( PHP_OS == 'WINNT' ) {
//				$full_stack_file = dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'start.bat';
//				Debug::Text('    Checking if this is a full stack install or not: '. $full_stack_file, __FILE__, __LINE__, __METHOD__, 10);
//				if ( !file_exists( $full_stack_file ) ) {
//					$full_stack_file = dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'start.bat';
//					Debug::Text('    bChecking if this is a full stack install or not: '. $full_stack_file, __FILE__, __LINE__, __METHOD__, 10);
//					if ( !file_exists( $full_stack_file ) ) {
//						Debug::Text('    This is NOT a full stack install... Exiting...', __FILE__, __LINE__, __METHOD__, 10);
//						CLIExit(0); //Exit success as it may not be a stack install.
//					}
//				}
//
//				$stack_install_dir = realpath( dirname( $full_stack_file ) );
//				Debug::Text('    Checking full stack install directory: '. $stack_install_dir, __FILE__, __LINE__, __METHOD__, 10);
//				if ( file_exists( $stack_install_dir ) ) {
//					Debug::Text('    Found full stack install directory: '. $stack_install_dir, __FILE__, __LINE__, __METHOD__, 10);
//					$php_ini_file = $stack_install_dir. DIRECTORY_SEPARATOR .'php'. DIRECTORY_SEPARATOR .'php.ini';
//					if ( file_exists( $php_ini_file ) ) {
//						Debug::Text( '      PHP INI file found: '. $php_ini_file, __FILE__, __LINE__, __METHOD__, 10 );
//						$php_ini_contents = file_get_contents( $php_ini_file );
//
//						//Enable PHP MCRYPT extension. Seems like it was compiled under a wrong version of PHP though, so don't do this.
//						//$php_ini_contents = preg_replace('/^;extension=php_mcrypt\.dll/mi', 'extension=php_mcrypt.dll', $php_ini_contents, -1, $replacement_count );
//						//Debug::Text( '      PHP.INI replacements: MCRYPT: '. $replacement_count, __FILE__, __LINE__, __METHOD__, 10 );
//
//						//Enable PHP OPENSSL extension.
//						$php_ini_contents = preg_replace('/^;extension=php_openssl\.dll/mi', 'extension=php_openssl.dll', $php_ini_contents, -1, $replacement_count );
//						Debug::Text( '      PHP.INI replacements: OPENSSL: '. $replacement_count, __FILE__, __LINE__, __METHOD__, 10 );
//
//						//Enable PHP ZIP and INTL extensions. Seems like ZIP was compiled under a different version of PHP, so don't enable it.
//						//$php_ini_contents = preg_replace('/^;extension=php_zip\.dll/mi', 'extension=php_zip.dll'. PHP_EOL .'extension=php_intl.dll', $php_ini_contents, -1, $replacement_count );
//						if ( preg_match( '/^extension=php_intl\.dll/mi', $php_ini_contents ) === 0 ) { //Don't replace it if it already is enabled.
//							$php_ini_contents = preg_replace( '/^;extension=php_zip\.dll/mi', ';extension=php_zip.dll' . PHP_EOL . 'extension=php_intl.dll', $php_ini_contents, -1, $replacement_count );
//							Debug::Text( '      PHP.INI replacements: ZIP/INTL: ' . $replacement_count, __FILE__, __LINE__, __METHOD__, 10 );
//						}
//
//						if ( $php_ini_contents !== NULL ) {
//							Debug::Arr( $php_ini_contents, '      Writing out new PHP.INI contents: ', __FILE__, __LINE__, __METHOD__, 10 );
//							file_put_contents( $php_ini_file, $php_ini_contents );
//
//							system( $stack_install_dir . DIRECTORY_SEPARATOR .'restart.bat', $exit_code );
//							Debug::Text( '      Restarting TimeTrex services... Exit Code: '. $exit_code, __FILE__, __LINE__, __METHOD__, 10 );
//							CLIExit(0);
//						} else {
//							Debug::Text( '      PHP INI modification failed, or none to make!', __FILE__, __LINE__, __METHOD__, 10 );
//							CLIExit(0);
//						}
//					}
//				}
//			}
		}
		CLIExit( 0 );
	}

	if ( in_array( '--requirements_only', $argv ) ) {
		Debug::Text( 'Checking requirements only...', __FILE__, __LINE__, __METHOD__, 10 );
		$exclude_requirements = array('php_cli_requirements', 'base_url', 'clean_cache');
		if ( in_array( '--web_installer', $argv ) ) {
			Debug::Text( '  Launched from web installer...', __FILE__, __LINE__, __METHOD__, 10 );
			//When run from the web_installer most requirements are already checked, so exclude the slow ones.
			$exclude_requirements[] = 'disk_space';
			$exclude_requirements[] = 'file_checksums';
			$exclude_requirements[] = 'file_permissions';
			$exclude_requirements[] = 'clean_cache';
		}

		$install_obj->cleanCacheDirectory();
		if ( $install_obj->checkAllRequirements( false, $exclude_requirements ) == 0 ) {
			echo 'Requirements all pass successfully!' . "\n";
			CLIExit( 0 );
			//CLIExit(1); //Test failed system requirement check...
		} else {
			echo 'Failed Requirements: ' . implode( ',', $install_obj->getFailedRequirements( false, $exclude_requirements ) ) . "\n";
			CLIExit( 1 );
		}
		unset( $exclude_requirements );
	}

	if ( $force == true ) {
		echo "Force Mode enabled...\n";
		//Force installer_enabled to TRUE so we don't have to manually modify the config file with scripts.
		$config_vars['other']['installer_enabled'] = true;
	}
	$install_obj = new Install(); //Re-initialize install object with new config options set above. (force)


	if ( in_array( '--schema_only', $argv ) ) {
		if ( $install_obj->isInstallMode() == false ) {
			echo "ERROR: Install mode is not enabled in the timetrex.ini.php file!\n";
			CLIExit( 1 );
		} else {
			$install_obj->cleanCacheDirectory();
			if ( $install_obj->checkAllRequirements( true ) == 0 ) {
				//Check to see if the DB schema is before the UUID upgrade (schema 1070 or older) and set the $PRIMARY_KEY_IS_UUID accordingly.
				//  THIS IS in tools/unattended_install.php, tools/unattended_upgrade.php, includes/database.inc.php  as well.
				if ( (int)SystemSettingFactory::getSystemSettingValueByKey( 'schema_version_group_A' ) < 1100 ) {
					Debug::Text( 'Setting PRIMARY_KEY_IS_UUID to FALSE due to pre-UUID schema version: ' . SystemSettingFactory::getSystemSettingValueByKey( 'schema_version_group_A' ), __FILE__, __LINE__, __METHOD__, 1 );
					$PRIMARY_KEY_IS_UUID = false;
				}

				$install_obj->setDatabaseConnection( $db ); //Default connection

				//Make sure at least one company exists in the database, this only works for upgrades, not initial installs.
				if ( $install_obj->checkDatabaseExists( $config_vars['database']['database_name'] ) == true ) {
					if ( $install_obj->checkTableExists( 'company' ) == true ) {
						//Table could be created, but check to make sure a company actually exists too.
						$clf = TTnew( 'CompanyListFactory' );
						$clf->getAll();
						if ( $clf->getRecordCount() >= 1 ) {
							$install_obj->setIsUpgrade( true );
						} else {
							//No company exists, send them to the create company page.
							$install_obj->setIsUpgrade( false );
						}
					} else {
						$install_obj->setIsUpgrade( false );
					}
				}

				if ( $install_obj->getIsUpgrade() == true ) {
					if ( $install_obj->checkDatabaseExists( $config_vars['database']['database_name'] ) == true ) {
						//Create SQL, always try to install every schema version, as
						//installSchema() will check if its already been installed or not.
						$install_obj->setDatabaseDriver( $config_vars['database']['type'] );
						$install_obj->createSchemaRange( null, null ); //All schema versions
						$install_obj->setVersions();

						//Clear all cache.
						$install_obj->cleanCacheDirectory();
						$cache->clean();

						echo "Upgrade successfull!\n";
						CLIExit( 0 );
					} else {
						Debug::Text( 'ERROR: Database does not exist.', __FILE__, __LINE__, __METHOD__, 10 );
						echo "ERROR: Database does not exists!\n";
					}
				} else {
					echo "ERROR: No company exists for upgrading!\n";
				}
			} else {
				echo "ERROR: System requirements are not satisfied, or a new version exists!\n";
			}
		}
		CLIExit( 1 );
	}

	//Upgrade Stage2
	if ( in_array( '--stage2', $argv ) ) {
		/*
		 Steps to do full upgrade:
		- Check if new version is available, send FORCE flag to help update some clients sooner if required.
		- If new version exists:
			- Enable logging (in memory), don't modify config file.
			- Check existing system requirements/checksums to make sure no files have been changed and system requirements are still met.
				This should also check permissions to make sure the files are all writable by the user who is running the script.
			- Download new version .ZIP file, extract to 'upgrade_staging' directory.
			- Run system requirement check for new version in staging directory, to make sure we can upgrade to that version.
			- (?)Force a database backup if possible.
			- Copy main directory to 'upgrade_rollback' directory.
			- Move staging directory over top of main directory
			- Run schema upgrade.
			- Done.
		*/

		Debug::Text( 'AutoUpgrade Stage2... Version: ' . APPLICATION_VERSION, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $force == false AND ( PRODUCTION == false OR DEPLOYMENT_ON_DEMAND == true ) ) { //Allow FORCE=TRUE to override this.
			echo "ERROR: Not doing full upgrade when PRODUCTION mode is disabled, or in ONDEMAND mode... Use FORCE argument to override.\n";
			CLIExit( 1 );
		}

		$config_vars['other']['installer_enabled'] = true;

		echo "Performing any necessary corrections from previous version...\n";
		//From v7.3.1 to 7.3.2 some files weren't getting copied if they were new in this version and created a new directory.
		//So do the copy again in stage2 just in case.
		moveUpgradeFiles( $upgrade_staging_latest_dir );

		echo "Upgrading database schema...\n";
		//Don't check file_checksums, as the script is run from the old version and therefore the checksum version match will fail everytime.
		//They should have been checked above anyways, so in theory this shouldn't matter.

		$handle = @fopen( 'http://www.timetrex.com/' . URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'unattended_upgrade_stage2_requirements'), 'pre_install.php' ), 'r' );
		@fclose( $handle );
		if ( $install_obj->checkAllRequirements( false, array('file_checksums', 'php_cli_requirements', 'base_url', 'clean_cache') ) == 0 ) {
			$install_obj->setDatabaseConnection( $db ); //Default connection

			//Make sure at least one company exists in the database, this only works for upgrades, not initial installs.
			if ( $install_obj->checkDatabaseExists( $config_vars['database']['database_name'] ) == true ) {
				if ( $install_obj->checkTableExists( 'company' ) == true ) {
					//Table could be created, but check to make sure a company actually exists too.
					$clf = TTnew( 'CompanyListFactory' );
					$clf->getAll();
					if ( $clf->getRecordCount() >= 1 ) {
						$install_obj->setIsUpgrade( true );
					} else {
						//No company exists, send them to the create company page.
						$install_obj->setIsUpgrade( false );
					}
				} else {
					$install_obj->setIsUpgrade( false );
				}
			}

			if ( $install_obj->getIsUpgrade() == true ) {
				if ( $install_obj->checkDatabaseExists( $config_vars['database']['database_name'] ) == true ) {
					Debug::Text( 'Upgrading schema now...', __FILE__, __LINE__, __METHOD__, 10 );
					//Create SQL, always try to install every schema version, as
					//installSchema() will check if its already been installed or not.
					$install_obj->setDatabaseDriver( $config_vars['database']['type'] );
					$install_obj->createSchemaRange( null, null ); //All schema versions
					$install_obj->setVersions();

					Debug::Text( 'Upgrading database schema successful!', __FILE__, __LINE__, __METHOD__, 10 );
					echo "Upgrading database schema successful!\n";
					$handle = @fopen( 'http://www.timetrex.com/' . URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'unattended_upgrade_new_schema'), 'pre_install.php' ), 'r' );
					@fclose( $handle );

					Debug::Text( 'Cleaning up temporary files...', __FILE__, __LINE__, __METHOD__, 10 );
					echo "Cleaning up temporary files...\n";
					//Send version data before and after upgrade.
					$ttsc = new TimeTrexSoapClient();
					$ttsc->sendCompanyData( $company_id, true );
					$ttsc->sendCompanyVersionData( $company_id );

					//Attempt to update license file if necessary.
					$license = new TTLicense();
					$license->getLicenseFile( false );

					//Clear all cache.
					$install_obj->cleanCacheDirectory();
					$cache->clean();

					Misc::cleanDir( $upgrade_staging_dir, true, true, true );
					@unlink( $upgrade_file_name );

					Debug::Text( 'Stage 2 Successfull!', __FILE__, __LINE__, __METHOD__, 10 );
					echo "Stage 2 Successfull!\n";
					$handle = @fopen( 'http://www.timetrex.com/' . URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'unattended_upgrade_done'), 'pre_install.php' ), 'r' );
					@fclose( $handle );

					//Make sure we disable the installer even if an error has occurred.
					//Since v7.3.0 had a bug where the installer never disabled, force it disabled here for at least one version just in case.
					//Even though we have switched to using the variable only, and this isn't needed anymore.
					$data['other']['installer_enabled'] = 'FALSE';
					$data['other']['default_interface'] = 'html5';
					$install_obj->writeConfigFile( $data );

					CLIExit( 0 );
				} else {
					Debug::Text( 'ERROR: Database does not exist.', __FILE__, __LINE__, __METHOD__, 10 );
					echo "ERROR: Database does not exists!\n";
				}
			} else {
				Debug::Text( 'ERROR: No company exists for upgrading', __FILE__, __LINE__, __METHOD__, 10 );
				echo "ERROR: No company exists for upgrading!\n";
			}
		} else {
			Debug::Text( 'ERROR: New system requirements are not satisfied!', __FILE__, __LINE__, __METHOD__, 10 );
			echo "ERROR: New system requirements are not satisfied!\n";
		}

		CLIExit( 1 );
	}

	//Stage 1, Full upgrade, including downloading the file.
	if ( in_array( '--schema_only', $argv ) == false AND in_array( '--stage2', $argv ) == false ) {
		if ( $force == false AND ( PRODUCTION == false OR DEPLOYMENT_ON_DEMAND == true ) ) { //Allow FORCE=TRUE to override this.
			echo "ERROR: Not doing full upgrade when PRODUCTION mode is disabled, or in ONDEMAND mode... Use FORCE argument to override.\n";
			CLIExit( 1 );
		}

		Debug::Text( 'New version available, check current system requirements...', __FILE__, __LINE__, __METHOD__, 10 );
		if ( disk_free_space( Environment::getBasePath() ) < ( 1000 * 1024000 ) ) {  //1000MB
			Debug::Text( 'Disk space available: ' . disk_free_space( Environment::getBasePath() ), __FILE__, __LINE__, __METHOD__, 10 );
			echo "Less than 1000MB of disk space available, unable to perform upgrade...\n";
			CLIExit( 1 );
		}

		//No need to write install file, as it just adds potential for problems if it doesn't get disabled again.
		$config_vars['other']['installer_enabled'] = true;

		Debug::Text( 'Checking if new version is available, current version: ' . APPLICATION_VERSION . ' Force: ' . (int)$full_force, __FILE__, __LINE__, __METHOD__, 10 );

		$ttsc = new TimeTrexSoapClient();
		if ( $full_force === true OR $ttsc->isNewVersionReadyForUpgrade( $force ) === true ) {
			Debug::Text( 'New version available, or force used...', __FILE__, __LINE__, __METHOD__, 10 );

			$handle = @fopen( 'http://www.timetrex.com/pre_install.php?v=' . $install_obj->getFullApplicationVersion() . '&os=' . PHP_OS . '&php_version=' . PHP_VERSION . '&web_server=' . urlencode( substr( $_SERVER['SERVER_SOFTWARE'], 0, 20 ) ) . '&page=unattended_upgrade', 'r' );
			@fclose( $handle );

			$install_obj->cleanCacheDirectory();
			if ( $install_obj->checkAllRequirements( false, array('file_checksums', 'php_cli_requirements', 'base_url', 'clean_cache') ) == 0 ) {
				Debug::Text( 'New version available, collecting data to download...', __FILE__, __LINE__, __METHOD__, 10 );
				echo "New version available, collecting data to download...\n";

				//Send version data before and after upgrade.
				$ttsc->sendCompanyData( $company_id, true );
				$ttsc->sendCompanyUserLocationData( $company_id );
				$ttsc->sendCompanyUserCountData( $company_id );
				$ttsc->sendCompanyVersionData( $company_id );

				for ( $i = 0; $i < 3; $i++ ) {
					$file_url = $ttsc->getUpgradeFileURL( $force );
					Debug::Arr( $file_url, 'File Upgrade URL: ', __FILE__, __LINE__, __METHOD__, 10 );
					if ( !is_soap_fault( $file_url ) AND $file_url === false ) {
						//Skip retries in case the .ZIP file is already downloaded for testing.
						Debug::Text( 'Upgrade URL not available from server, either already running latest version or not ready to upgrade yet, skip retries: ' . $i, __FILE__, __LINE__, __METHOD__, 10 );
						break;
					}

					if ( !is_soap_fault( $file_url ) AND $file_url !== false AND $file_url != '' ) {
						$file_url_size = Misc::getRemoteHTTPFileSize( $file_url );
						if ( $file_url_size > 0 ) {
							Debug::Text( 'Got File Upgrade URL and size, breaking retry loop...' . $i, __FILE__, __LINE__, __METHOD__, 10 );
							break;
						} else {
							Debug::Text( 'Unable to get remote File Upgrade URL size, retrying: ' . $i, __FILE__, __LINE__, __METHOD__, 10 );
						}
					}

					echo "  Unable to obtain File Upgrade URL, retrying in 2 minutes: " . $i . "\n";
					Debug::Text( 'Unable to obtain File Upgrade URL, retrying: ' . $i, __FILE__, __LINE__, __METHOD__, 10 );
					sleep( 120 );
				}

				if ( file_exists( $upgrade_file_name ) OR ( !is_soap_fault( $file_url ) AND $file_url !== false AND $file_url != '' ) ) {
					$handle = @fopen( 'http://www.timetrex.com/' . URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'unattended_upgrade_download'), 'pre_install.php' ), 'r' );
					@fclose( $handle );

					if ( isset( $manual_upgrade_file_name ) AND $manual_upgrade_file_name != '' AND file_exists( $manual_upgrade_file_name ) AND filesize( $manual_upgrade_file_name ) > 0 ) {
						Debug::Text( 'Using manual upgrade file: ' . $manual_upgrade_file_name . ' Current Size: ' . filesize( $manual_upgrade_file_name ), __FILE__, __LINE__, __METHOD__, 10 );
						echo 'Using Manual Upgrade File: ' . $manual_upgrade_file_name . "...\n";
						$upgrade_file_name = $manual_upgrade_file_name;
					} else {
						if ( file_exists( $upgrade_file_name ) == false OR ( isset( $file_url_size ) AND filesize( $upgrade_file_name ) != $file_url_size ) ) {
							Debug::Text( 'Attempting to download latest version...', __FILE__, __LINE__, __METHOD__, 10 );
							echo "Attempting to download latest version...\n";
							sleep( 5 ); //Sleep for 5 seconds so it can be cancelled easy if needed.

							//$bytes_downloaded = @file_put_contents( $upgrade_file_name, fopen( $file_url, 'r') );
							$bytes_downloaded = Misc::downloadHTTPFile( $file_url, $upgrade_file_name );
							Debug::Text( 'Downloaded file: ' . $upgrade_file_name . ' Size: ' . @filesize( $upgrade_file_name ) . ' Bytes downloaded: ' . $bytes_downloaded . ' Remote Size: ' . $file_url_size, __FILE__, __LINE__, __METHOD__, 10 );
							if ( $bytes_downloaded != $file_url_size OR @filesize( $upgrade_file_name ) <= 0 ) {
								Debug::Text( 'ERROR: File did not download correctly...', __FILE__, __LINE__, __METHOD__, 10 );
								echo 'ERROR: File did not download correctly...' . "\n";
								setAutoUpgradeFailed();
								CLIExit( 1 );
							} else {
								echo 'Downloaded file: ' . $upgrade_file_name . ' Size: ' . filesize( $upgrade_file_name ) . "\n";
							}
						} else {
							Debug::Text( 'Upgrade file already exists... Current Size: ' . filesize( $upgrade_file_name ) . ' Remote Size: ' . ( isset( $file_url_size ) ? $file_url_size : 'N/A' ), __FILE__, __LINE__, __METHOD__, 10 );
							echo "Upgrade file already exists...\n";
						}
					}

					if ( file_exists( $upgrade_file_name ) AND filesize( $upgrade_file_name ) > 0 ) {
						Debug::Text( 'Cleaning staging directory: ' . $upgrade_staging_dir, __FILE__, __LINE__, __METHOD__, 10 );
						echo 'Cleaning staging directory: ' . $upgrade_staging_dir . "\n";
						Misc::cleanDir( $upgrade_staging_dir, true, true, true );
						sleep( 15 ); //Apparently unlink() is async on windows, so wait some random time to hopefully let the operations complete.

						Debug::Text( 'Unzipping UPGRADE.ZIP', __FILE__, __LINE__, __METHOD__, 10 );
						echo "Unzipping UPGRADE.ZIP\n";
						$zip = new ZipArchive;
						$zip_result = $zip->open( $upgrade_file_name );
						if ( $zip_result === true ) {
							$handle = @fopen( 'http://www.timetrex.com/' . URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'unattended_upgrade_unzip'), 'pre_install.php' ), 'r' );
							@fclose( $handle );

							//Hide errors from this, like failed streams, or file already exists warnings and such. Don't think there is anything we can do about them anyways.
							//ie: PHP ERROR - WARNING(2): ZipArchive::extractTo(): File exists
							//    PHP ERROR - WARNING(2): ZipArchive::extractTo(): Unable to open stream
							@$zip->extractTo( $upgrade_staging_dir );
							$zip->close();
							sleep( 15 ); //Maybe this will help prevent access denied (code: 5) errors on windows?
							clearstatcache();

							Debug::Text( 'Unzipping UPGRADE.ZIP done...', __FILE__, __LINE__, __METHOD__, 10 );
							echo "Unzipping UPGRADE.ZIP done...\n";
						} else {
							Debug::Text( 'ERROR: Unzipping UPGRADE.ZIP failed...', __FILE__, __LINE__, __METHOD__, 10 );
							echo "ERROR: Unzipping UPGRADE.ZIP failed...\n";
						}
						unset( $zip_result, $zip );

						//Rename whatever directory that is in the staging dir to
						if ( file_exists( $upgrade_staging_dir ) ) {
							if ( $handle = opendir( $upgrade_staging_dir ) ) {
								while ( ( $entry = readdir( $handle ) ) !== false ) {
									if ( $entry != '.' AND $entry != '..' AND $entry != 'latest_version' ) { //In case the rename occurred and for some reason those files can't be cleared/deleted, ignore it.
										$upgrade_staging_extract_dir = $upgrade_staging_dir . DIRECTORY_SEPARATOR . $entry;
										break;
									}
								}
								closedir( $handle );
							}

							if ( isset( $upgrade_staging_extract_dir ) ) {
								$handle = @fopen( 'http://www.timetrex.com/' . URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'unattended_upgrade_rename_dir'), 'pre_install.php' ), 'r' );
								@fclose( $handle );

								//Make sure the latest directory does not exist before renaming the unzipped directory to it. This may help with some Access Denied errors on Windows.
								Misc::cleanDir( $upgrade_staging_latest_dir, true, true, true );

								Debug::Text( 'Upgrade Staging Extract Dir: ' . $upgrade_staging_extract_dir . ' Renaming to: ' . $upgrade_staging_latest_dir, __FILE__, __LINE__, __METHOD__, 10 );
								if ( Misc::rename( $upgrade_staging_extract_dir, $upgrade_staging_latest_dir ) == false ) {
									Debug::Text( 'ERROR: Unable to rename: ' . $upgrade_staging_extract_dir . ' to: ' . $upgrade_staging_latest_dir, __FILE__, __LINE__, __METHOD__, 10 );
									echo 'ERROR: Unable to rename: ' . $upgrade_staging_extract_dir . ' to: ' . $upgrade_staging_latest_dir . "\n";
								}
								clearstatcache();
							} else {
								Debug::Text( 'ERROR: UPGRADE.ZIP extract directory does not exist...', __FILE__, __LINE__, __METHOD__, 10 );
							}
							unset( $handle, $entry, $upgrade_staging_extract_dir );
						} else {
							Debug::Text( 'ERROR: Upgrade staging directory does not exist, cannot continue...', __FILE__, __LINE__, __METHOD__, 10 );
							echo "ERROR: Upgrade staging directory does not exist, cannot continue...\n";
						}

						if ( isset( $upgrade_staging_latest_dir ) ) {
							//Check system requirements of new version.
							$latest_unattended_upgrade_tool = $upgrade_staging_latest_dir . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'unattended_upgrade.php';
							if ( file_exists( $latest_unattended_upgrade_tool ) ) {
								if ( is_executable( $php_cli ) ) {
									$command = '"' . $php_cli . '" -d opcache.enable_cli=0 "' . $latest_unattended_upgrade_tool . '" --config "' . CONFIG_FILE . '" --pre_requirements_update'; //Make each part is quoted in case there are spaces in the paths.
									system( $command, $exit_code );
									Debug::Text( 'Running pre-requirements update... Command: ' . $command . ' Exit Code: ' . $exit_code, __FILE__, __LINE__, __METHOD__, 10 );
									if ( $exit_code == 0 ) {
										Debug::Text( 'New version pre-requirements met...', __FILE__, __LINE__, __METHOD__, 10 );
										$handle = @fopen( 'http://www.timetrex.com/' . URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'unattended_upgrade_pre_requirements'), 'pre_install.php' ), 'r' );
										@fclose( $handle );

										$command = '"' . $php_cli . '" -d opcache.enable_cli=0 "' . $latest_unattended_upgrade_tool . '" --config "' . CONFIG_FILE . '" --requirements_only'; //Make each part is quoted in case there are spaces in the paths.
										system( $command, $exit_code );
										Debug::Text( 'Checking new version system requirements... Command: ' . $command . ' Exit Code: ' . $exit_code, __FILE__, __LINE__, __METHOD__, 10 );
										if ( $exit_code == 0 ) {
											Debug::Text( 'New version system requirements met...', __FILE__, __LINE__, __METHOD__, 10 );
											$handle = @fopen( 'http://www.timetrex.com/' . URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'unattended_upgrade_new_requirements'), 'pre_install.php' ), 'r' );
											@fclose( $handle );

											moveUpgradeFiles( $upgrade_staging_latest_dir );

											$handle = @fopen( 'http://www.timetrex.com/' . URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'unattended_upgrade_launch_stage2'), 'pre_install.php' ), 'r' );
											@fclose( $handle );

											$global_class_map['TTUUID'] = 'core/TTUUID.class.php'; //Need to manually map the TTUUID class as it may be required by autoloaded classes in this process.

											//Clear OPCACHE to help try to avoid calling ourself with opcached files from the old version.
											if ( function_exists( 'opcache_reset' ) ) {
												opcache_reset();
											}

											//Run separate process to finish stage2 of installer so it can be run with the new scripts.
											//This allows us more flexibility if an error occurs to finish the install or have the latest version correct problems.
											echo "Launching Stage 2...\n";
											sleep( 5 );
											$command = '"' . $php_cli . '" -d opcache.enable_cli=0 "' . __FILE__ . '" --config "' . CONFIG_FILE . '" --stage2'; //Disable opcache on CLI

											//Pass along force argument if it was originally supplied.
											if ( $full_force == true ) {
												$command .= ' -ff';
											} else if ( $force == true ) {
												$command .= ' -f';
											}

											Debug::Text( 'Stage2 Command: ' . $command, __FILE__, __LINE__, __METHOD__, 10 );
											system( $command, $exit_code );
											if ( $exit_code == 0 ) {
												Debug::Text( 'Stage2 success!', __FILE__, __LINE__, __METHOD__, 10 );

												echo "Upgrade successfull!\n";

												//Since the --stage2 SQL upgrade is performed in a different process, we have to manually turn UUIDs on for this process before calling any other SQL query like setAutoUpgradeFailed( 0 )
												global $PRIMARY_KEY_IS_UUID;
												$PRIMARY_KEY_IS_UUID = true;

												setAutoUpgradeFailed( 0 ); //Clear auto_upgrade_failed setting if it isn't already.
												CLIExit( 0 );
											} else {
												Debug::Text( 'Stage2 failed... Exit Code: ' . $exit_code, __FILE__, __LINE__, __METHOD__, 10 );
												setAutoUpgradeFailed();
											}
										} else {
											Debug::Text( 'ERROR: New version system requirements not met...', __FILE__, __LINE__, __METHOD__, 10 );
											echo "ERROR: New version system requirements not met...\n";
											setAutoUpgradeFailed();
										}
									} else {
										Debug::Text( 'ERROR: Pre-Requirements Update failed...', __FILE__, __LINE__, __METHOD__, 10 );
										echo "ERROR: Pre-Requirements Update failed...\n";
										setAutoUpgradeFailed();
									}
								} else {
									Debug::text( 'ERROR: PHP CLI is not executable: ' . $php_cli, __FILE__, __LINE__, __METHOD__, 10 );
									echo "ERROR: PHP CLI is not executable: " . $php_cli . "\n";
									setAutoUpgradeFailed();
								}
							} else {
								Debug::Text( 'ERROR: UNATTENDED UPGRADE tool in new version does not exist: ' . $latest_unattended_upgrade_tool, __FILE__, __LINE__, __METHOD__, 10 );
								echo "ERROR: UNATTENDED UPGRADE tool in new version does not exist: " . $latest_unattended_upgrade_tool . "\n";
								setAutoUpgradeFailed();
							}
						} else {
							Debug::Text( 'ERROR: Upgrade staging latest directory does not exist, cannot continue...', __FILE__, __LINE__, __METHOD__, 10 );
							echo "ERROR: Upgrade staging latest directory does not exist, cannot continue...\n";
							setAutoUpgradeFailed();
						}
					} else {
						Debug::Text( 'ERROR: UPGRADE.ZIP does not exist or is 0 bytes...', __FILE__, __LINE__, __METHOD__, 10 );
						echo "ERROR: UPGRADE.ZIP does not exist or is 0 bytes...\n";
						setAutoUpgradeFailed();
					}
				} else {
					Debug::Text( 'Upgrade File URL not available...', __FILE__, __LINE__, __METHOD__, 10 );
					echo "ERROR: Unable to download upgrade file at this time, please try again later...\n";
					setAutoUpgradeFailed();
				}
			} else {
				Debug::Text( 'ERROR: Current system requirements check failed...', __FILE__, __LINE__, __METHOD__, 10 );
				echo "ERROR: Current system requirements check failed...\n";
				echo '  Failed Requirements: ' . implode( ',', $install_obj->getFailedRequirements( false, array('file_checksums', 'php_cli_requirements', 'base_url', 'clean_cache') ) ) . "\n";
				setAutoUpgradeFailed();
			}
		} else {
			Debug::Text( 'Already running latest version: ' . APPLICATION_VERSION, __FILE__, __LINE__, __METHOD__, 10 );
			echo "Already running latest version: " . APPLICATION_VERSION . "\n";
			setAutoUpgradeFailed( 0 ); //Clear auto_upgrade_failed setting if it isn't already.
		}
		CLIExit( 1 );
	}
}
CLIExit( 1 );
?>
