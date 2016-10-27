<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2013 TimeTrex Software Inc.
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
 * #292 Westbank, BC V4T 2E9, Canada or at email address info@timetrex.com.
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
 * $Revision: 1246 $
 * $Id: fix_client_balance.php 1246 2007-09-14 23:47:42Z ipso $
 * $Date: 2007-09-14 16:47:42 -0700 (Fri, 14 Sep 2007) $
 */

//This must go above include for global.inc.php
if ( in_array('--config', $argv) ) {
	$_SERVER['TT_CONFIG_FILE'] = strtolower( trim($argv[array_search('--config', $argv)+1]) );
}
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'global.inc.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'CLI.inc.php');

//Always enable debug logging during upgrade.
Debug::setEnable(TRUE);
Debug::setBufferOutput(TRUE);
Debug::setEnableLog(TRUE);
Debug::setVerbosity(10);

ignore_user_abort(TRUE);
ini_set( 'max_execution_time', 0 );
ini_set( 'memory_limit', '1024M' ); //Just in case.

//Force flush after each output line.
ob_implicit_flush( TRUE );
ob_end_flush();

if ( isset($argv[1]) AND in_array($argv[1], array('--help', '-help', '-h', '-?') ) ) {
	$help_output = "Usage: unattended_upgrade.php\n";
	$help_output .= " [--config] = Config file to use.\n";
	$help_output .= " [--schema_only] = Run a schema upgrade only.\n";
	$help_output .= " [--requirements_only] = Run a system requirements check only.\n";
	$help_output .= " [-f] = Force upgrade even if INSTALL mode is disabled.\n";
	echo $help_output;
} else {
	$last_arg = count($argv)-1;

	if ( in_array('--requirements_only', $argv) ) {
		$install_obj->cleanCacheDirectory();
		if ( $install_obj->checkAllRequirements( FALSE, array('base_url', 'clean_cache') ) == 0 ) {
			exit(0);
			//exit(1); //Test failed system requirement check...
		} else {
			echo 'Failed Requirements: '. implode(',', $install_obj->getFailedRequirements( FALSE, array('base_url') ) )."\n";
			Debug::Display();
			Debug::writeToLog();
			exit(1);
		}
	}

	if ( in_array('-f', $argv) ) {
		$force = TRUE;
	} else {
		$force = FALSE;
	}
	if ( $force == TRUE ) {
		echo "Force Mode enabled...\n";
		//Force installer_enabled to TRUE so we don't have to manually modify the config file with scripts.
		$config_vars['other']['installer_enabled'] = TRUE;
	}

	//Re-initialize install object with new config file.
	$install_obj = new Install();
	if ( in_array('--schema_only', $argv) ) {
		if ( $install_obj->isInstallMode() == FALSE ) {
			echo "ERROR: Install mode is not enabled in the timetrex.ini.php file!\n";
			exit(1);
		} else {
			$install_obj->cleanCacheDirectory();
			if ( $install_obj->checkAllRequirements( TRUE ) == 0 ) {
				$install_obj->setDatabaseConnection( $db ); //Default connection

				//Make sure at least one company exists in the database, this only works for upgrades, not initial installs.
				if ( $install_obj->checkDatabaseExists( $config_vars['database']['database_name'] ) == TRUE ) {
					if ( $install_obj->checkTableExists( 'company' ) == TRUE ) {
						//Table could be created, but check to make sure a company actually exists too.
						$clf = TTnew( 'CompanyListFactory' );
						$clf->getAll();
						if ( $clf->getRecordCount() >= 1 ) {
							$install_obj->setIsUpgrade( TRUE );
						} else {
							//No company exists, send them to the create company page.
							$install_obj->setIsUpgrade( FALSE );
						}
					} else {
						$install_obj->setIsUpgrade( FALSE );
					}
				}

				if ( $install_obj->getIsUpgrade() == TRUE ) {
					if ( $install_obj->checkDatabaseExists( $config_vars['database']['database_name'] ) == TRUE ) {
						//Create SQL, always try to install every schema version, as
						//installSchema() will check if its already been installed or not.
						$install_obj->setDatabaseDriver( $config_vars['database']['type'] );
						$install_obj->createSchemaRange( NULL, NULL ); //All schema versions
						$install_obj->setVersions();

						//Clear all cache.
						$install_obj->cleanCacheDirectory();
						$cache->clean();

						$data['installer_enabled'] = 'FALSE';
						$install_obj->writeConfigFile( $data );

						echo "Upgrade successfull!\n";
					} else {
						Debug::Text('ERROR: Database does not exist.', __FILE__, __LINE__, __METHOD__,10);
						echo "ERROR: Database does not exists!\n";
					}
				} else {
					echo "ERROR: No company exists for upgrading!\n";
				}
			} else {
				echo "ERROR: System requirements are not satisfied, or a new version exists!\n";
			}
		}
		Debug::Display();
		Debug::writeToLog();
		exit;
	}

	//Full upgrade, including downloading the file.
	if ( in_array('--schema_only', $argv) == FALSE ) {
		if ( PRODUCTION == FALSE OR DEPLOYMENT_ON_DEMAND == TRUE ) {
			echo "ERROR: Not doing full upgrade when PRODUCTION or ONDEMAND mode is disabled...\n";
			exit(1);
		}

		Debug::Text('New version available, check current system requirements...', __FILE__, __LINE__, __METHOD__,10);
		if ( disk_free_space( Environment::getBasePath() ) < (500*1024000) ) {  //500MB
			Debug::Text('Disk space available: '. disk_free_space( Environment::getBasePath() ), __FILE__, __LINE__, __METHOD__,10);
			echo "Less than 500MB of disk space available, unable to perform upgrade...\n";
			exit(1);
		}

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
		$handle = @fopen('http://www.timetrex.com/pre_install.php?v='. $install_obj->getFullApplicationVersion() .'&os='. PHP_OS .'&php_version='. PHP_VERSION .'&web_server='. urlencode( substr( $_SERVER['SERVER_SOFTWARE'], 0, 20 ) ) .'&page=unattended_upgrade', "r");@fclose($handle);

		$data['installer_enabled'] = 'TRUE';
		$install_obj->writeConfigFile( $data );

		if ( isset( $config_vars['other']['primary_company_id'] ) ) {
			$company_id = $config_vars['other']['primary_company_id'];
		} else {
			$company_id = 1;
		}
		
		$ttsc = new TimeTrexSoapClient();
		if ( $ttsc->isLatestVersion( $company_id ) == FALSE ) {
			$install_obj->cleanCacheDirectory();
			if ( $install_obj->checkAllRequirements( FALSE, array('base_url', 'clean_cache') ) == 0 ) {
				Debug::Text('New version available, attempting to download...', __FILE__, __LINE__, __METHOD__,10);
				echo "New version available, attempting to download...\n";
				sleep(5); //Sleep for 5 seconds so it can be cancelled easy if needed.

				//Send version data before and after upgrade.
				$ttsc->sendCompanyData( $company_id, TRUE );
				$ttsc->sendCompanyUserLocationData( $company_id );
				$ttsc->sendCompanyUserCountData( $company_id );
				$ttsc->sendCompanyVersionData( $company_id );

				$file_url = $ttsc->getUpgradeFileURL( $force );
				Debug::Arr($file_url, 'File Upgrade URL: ', __FILE__, __LINE__, __METHOD__, 10);
				if ( !is_soap_fault($file_url) AND $file_url !== FALSE AND $file_url != '' ) {

					$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion() , 'page' => 'unattended_upgrade_download' ), 'pre_install.php'), "r");@fclose($handle);
					$upgrade_file_name = Environment::getBasePath() . DIRECTORY_SEPARATOR . 'UPGRADE.ZIP';
					if ( !file_exists( $upgrade_file_name ) ) {
						file_put_contents( $upgrade_file_name, fopen( $file_url , 'r'));
						Debug::Text('Downloaded file: '. $upgrade_file_name .' Size: '. filesize( $upgrade_file_name ), __FILE__, __LINE__, __METHOD__, 10);
						echo 'Downloaded file: '. $upgrade_file_name .' Size: '. filesize( $upgrade_file_name ) ."\n";
					} else {
						Debug::Text('Upgrade file already exists...', __FILE__, __LINE__, __METHOD__, 10);
						echo "Upgrade file already exists...\n";
					}

					$upgrade_staging_dir = Environment::getBasePath() . DIRECTORY_SEPARATOR . 'upgrade_staging' . DIRECTORY_SEPARATOR;
					Debug::Text('Cleaning staging directory: '. $upgrade_staging_dir, __FILE__, __LINE__, __METHOD__, 10);
					echo 'Cleaning staging directory: '. $upgrade_staging_dir ."\n";
					Misc::cleanDir( $upgrade_staging_dir, TRUE, TRUE, TRUE );

					Debug::Text('Unzipping UPGRADE.ZIP', __FILE__, __LINE__, __METHOD__, 10);
					echo "Unzipping UPGRADE.ZIP\n";
					$zip = new ZipArchive;
					$zip_result = $zip->open( $upgrade_file_name );
					if ( $zip_result === TRUE ) {
						$zip->extractTo( $upgrade_staging_dir );
						$zip->close();
						Debug::Text('Unzipping UPGRADE.ZIP done...', __FILE__, __LINE__, __METHOD__, 10);
						echo "Unzipping UPGRADE.ZIP done...\n";
						$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion() , 'page' => 'unattended_upgrade_unzip' ), 'pre_install.php'), "r");@fclose($handle);
					} else {
						Debug::Text('ERROR: Unzipping UPGRADE.ZIP failed...', __FILE__, __LINE__, __METHOD__, 10);
						echo "ERROR: Unzipping UPGRADE.ZIP failed...\n";
					}
					unset($zip_result, $zip);
					
					//Rename whatever directory that is in the staging dir to
					if ( file_exists($upgrade_staging_dir) ) {
						if ($handle = opendir($upgrade_staging_dir) ) {
							while ( ( $entry = readdir($handle) ) !== FALSE ) {
								if ( $entry != '.' AND $entry != '..' ) {
									$upgrade_staging_extract_dir = $upgrade_staging_dir . DIRECTORY_SEPARATOR . $entry;
									break;
								}
							}
							closedir($handle);
						}

						if ( isset($upgrade_staging_extract_dir) ) {
							$upgrade_staging_latest_dir = $upgrade_staging_dir . DIRECTORY_SEPARATOR . 'latest_version';
							rename( $upgrade_staging_extract_dir, $upgrade_staging_latest_dir );
							Debug::Text('Upgrade Staging Extract Dir: '. $upgrade_staging_extract_dir .' Renaming to: '. $upgrade_staging_latest_dir, __FILE__, __LINE__, __METHOD__, 10);
						} else {
							Debug::Text('ERROR: UPGRADE.ZIP extract directory does not exist...', __FILE__, __LINE__, __METHOD__, 10);
						}
						unset($handle, $entry, $upgrade_staging_extract_dir );
					} else {
						Debug::Text('ERROR: Upgrade staging directory does not exist, cannot continue...', __FILE__, __LINE__, __METHOD__, 10);
						echo "ERROR: Upgrade staging directory does not exist, cannot continue...\n";
					}
					
					if ( isset($upgrade_staging_latest_dir) ) {
						//Check system requirements of new version.
						$latest_unattended_upgrade_tool = $upgrade_staging_latest_dir . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'unattended_upgrade.php';
						if ( file_exists( $latest_unattended_upgrade_tool ) ) {
							$php_cli = $config_vars['path']['php_cli'];
							if ( is_executable( $php_cli ) ) {
								$command = $php_cli .' '. $latest_unattended_upgrade_tool .' --config '. CONFIG_FILE .' --requirements_only';
								system( $command, $exit_code );
								if ( $exit_code == 0 ) {
									Debug::Text('New version system requirements met: Exit Code: '. $exit_code, __FILE__, __LINE__, __METHOD__, 10);
									$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion() , 'page' => 'unattended_upgrade_new_requirements' ), 'pre_install.php'), "r");@fclose($handle);

									$latest_file_list = Misc::getFileList( $upgrade_staging_latest_dir, NULL, TRUE );
									if ( is_array($latest_file_list) ) {
										foreach( $latest_file_list as $latest_file ) {
											$new_file = str_replace( $upgrade_staging_latest_dir, Environment::getBasePath(), $latest_file  );

											//Check if directory exists.
											if ( !file_exists( dirname( $latest_file ) ) ) {
												Debug::Text('Creating new directory: '. dirname( $latest_file ), __FILE__, __LINE__, __METHOD__, 10);
												mkdir( dirname( $latest_file ) );
											}
											Debug::Text('Moving: '. $latest_file .' To: '. $new_file , __FILE__, __LINE__, __METHOD__, 10);
											rename( $latest_file, $new_file );
										}

										Debug::Text('Upgrading schema now...', __FILE__, __LINE__, __METHOD__, 10);
										echo "Upgrading database schema...\n";
										if ( $install_obj->checkAllRequirements( FALSE, array('base_url', 'clean_cache' ) ) == 0  ) {
											$install_obj->setDatabaseConnection( $db ); //Default connection

											//Make sure at least one company exists in the database, this only works for upgrades, not initial installs.
											if ( $install_obj->checkDatabaseExists( $config_vars['database']['database_name'] ) == TRUE ) {
												if ( $install_obj->checkTableExists( 'company' ) == TRUE ) {
													//Table could be created, but check to make sure a company actually exists too.
													$clf = TTnew( 'CompanyListFactory' );
													$clf->getAll();
													if ( $clf->getRecordCount() >= 1 ) {
														$install_obj->setIsUpgrade( TRUE );
													} else {
														//No company exists, send them to the create company page.
														$install_obj->setIsUpgrade( FALSE );
													}
												} else {
													$install_obj->setIsUpgrade( FALSE );
												}
											}

											if ( $install_obj->getIsUpgrade() == TRUE ) {
												if ( $install_obj->checkDatabaseExists( $config_vars['database']['database_name'] ) == TRUE ) {
													//Create SQL, always try to install every schema version, as
													//installSchema() will check if its already been installed or not.
													$install_obj->setDatabaseDriver( $config_vars['database']['type'] );
													$install_obj->createSchemaRange( NULL, NULL ); //All schema versions
													$install_obj->setVersions();
													echo "Upgrading database schema successful!\n";
													$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion() , 'page' => 'unattended_upgrade_new_schema' ), 'pre_install.php'), "r");@fclose($handle);

													echo "Cleaning up temporary files...\n";
													//Send version data before and after upgrade.
													$ttsc->sendCompanyData( $company_id, TRUE );
													$ttsc->sendCompanyVersionData( $company_id );

													//Attempt to update license file if necessary.
													$license = new TTLicense();
													$license->getLicenseFile( FALSE );

													//Clear all cache.
													$install_obj->cleanCacheDirectory();
													$cache->clean();

													$data['installer_enabled'] = 'FALSE';
													$install_obj->writeConfigFile( $data );

													Misc::cleanDir( $upgrade_staging_dir, TRUE, TRUE, TRUE );
													unlink($upgrade_file_name);

													Debug::Text('Upgrade Successfull!', __FILE__, __LINE__, __METHOD__,10);
													echo "Upgrade successfull!\n";
													$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion() , 'page' => 'unattended_upgrade_done' ), 'pre_install.php'), "r");@fclose($handle);
												} else {
													Debug::Text('ERROR: Database does not exist.', __FILE__, __LINE__, __METHOD__,10);
													echo "ERROR: Database does not exists!\n";
												}
											} else {
												echo "ERROR: No company exists for upgrading!\n";
											}
										} else {
											echo "ERROR: System requirements are not satisfied, or a new version exists!\n";
										}
									}
								} else {
									Debug::Text('ERROR: New version system requirements not met...', __FILE__, __LINE__, __METHOD__, 10);
									echo "ERROR: New version system requirements not met...\n";
								}
							} else {
								Debug::text('ERROR: PHP CLI is not executable: '. $php_cli, __FILE__, __LINE__, __METHOD__, 10);
								echo "ERROR: PHP CLI is not executable: ". $php_cli ."\n";
							}
						} else {
							Debug::Text('ERROR: UNATTENDED UPGRADE tool in new version does not exist: '. $latest_unattended_upgrade_tool, __FILE__, __LINE__, __METHOD__, 10);
							echo "ERROR: UNATTENDED UPGRADE tool in new version does not exist: ". $latest_unattended_upgrade_tool ."\n";
						}
					} else {
						Debug::Text('ERROR: Upgrade staging latest directory does not exist, cannot continue...', __FILE__, __LINE__, __METHOD__, 10);
						echo "ERROR: Upgrade staging latest directory does not exist, cannot continue...\n";
					}
				} else {
					Debug::Text('Upgrade File URL not available...', __FILE__, __LINE__, __METHOD__,10);
					echo "ERROR: Unable to download upgrade file at this time, please try again later...\n";
				}
			} else {
				Debug::Text('ERROR: Current system requirements check failed...', __FILE__, __LINE__, __METHOD__,10);
				echo "ERROR: Current system requirements check failed...\n";
			}
		} else {
			echo "Already running latest version: ". APPLICATION_VERSION ."\n";
		}
		Debug::Display();
		Debug::writeToLog();
		exit;
	}
}
Debug::Display();
Debug::writeToLog();
exit(1);
?>
