<?php /** @noinspection PhpUndefinedFunctionInspection */
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
 * @package Modules\Install
 */
class Install {

	protected $temp_db = null;
	var $config_vars = null;
	protected $database_driver = null;
	protected $is_upgrade = false;
	protected $extended_error_messages = null;
	protected $versions = [
			'system_version' => APPLICATION_VERSION,
	];
	protected $progress_bar_obj = null;
	protected $api_message_id = null;

	protected $critical_disabled_functions = [];

	/**
	 * Install constructor.
	 */
	function __construct() {
		global $config_vars;

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'InstallSchema.class.php' );

		$this->config_vars = $config_vars;

		//Disable caching so we don't exceed maximum memory settings.
		//global $cache;
		//$cache->_onlyMemoryCaching = TRUE; //This shouldn't be required anymore, as it also breaks invalidating cache files.

		ini_set( 'default_socket_timeout', 5 );
		ini_set( 'allow_url_fopen', 1 );

		return true;
	}

	/**
	 * @return null
	 */
	function getDatabaseDriver() {
		return $this->database_driver;
	}

	/**
	 * @param $driver
	 * @return bool
	 */
	function setDatabaseDriver( $driver ) {
		if ( $this->getDatabaseType( $driver ) !== 1 ) {
			$this->database_driver = $this->getDatabaseType( $driver );

			return true;
		}

		return false;
	}

	/**
	 * @return null|ProgressBar
	 */
	function getProgressBarObject() {
		if ( !is_object( $this->progress_bar_obj ) ) {
			$this->progress_bar_obj = new ProgressBar();
		}

		return $this->progress_bar_obj;
	}

	/**
	 * Returns the API messageID for each individual call.
	 * @return bool|null
	 */
	function getAPIMessageID() {
		if ( $this->api_message_id != null ) {
			return $this->api_message_id;
		}

		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setAPIMessageID( $id ) {
		Debug::Text(  'API Message ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $id != '' ) {
			$this->api_message_id = $id;

			return true;
		}

		return false;
	}

	/**
	 * Read .ini file.
	 * Make sure setup_mode is enabled.
	 * @return bool
	 */
	function isInstallMode() {
		if ( isset( $this->config_vars['other']['installer_enabled'] )
				&& $this->config_vars['other']['installer_enabled'] == 1 ) {
			Debug::text( 'Install Mode is ON', __FILE__, __LINE__, __METHOD__, 9 );

			return true;
		}

		Debug::text( 'Install Mode is OFF', __FILE__, __LINE__, __METHOD__, 9 );

		return false;
	}

	/**
	 * @param $key
	 * @param $msg
	 * @return bool
	 */
	function setExtendedErrorMessage( $key, $msg ) {
		if ( isset( $this->extended_error_messages[$key] ) && in_array( $msg, $this->extended_error_messages[$key] ) ) {
			return true;
		} else {
			$this->extended_error_messages[$key][] = $msg;
		}

		return true;
	}

	/**
	 * @param null $key
	 * @return bool|null|string
	 */
	function getExtendedErrorMessage( $key = null ) {
		if ( $key != '' ) {
			if ( isset( $this->extended_error_messages[$key] ) ) {
				return implode( ',', $this->extended_error_messages[$key] );
			}
		} else {
			return $this->extended_error_messages;
		}

		return false;
	}

	/**
	 * Checks if this is the professional version or not
	 * @return int
	 */
	function getTTProductEdition() {
		return getTTProductEdition();
	}

	/**
	 * @return string
	 */
	function getFullApplicationVersion() {
		$retval = APPLICATION_VERSION;

		if ( getTTProductEdition() == TT_PRODUCT_ENTERPRISE ) {
			$retval .= 'E';
		} else if ( getTTProductEdition() == TT_PRODUCT_CORPORATE ) {
			$retval .= 'C';
		} else if ( getTTProductEdition() == TT_PRODUCT_PROFESSIONAL ) {
			$retval .= 'P';
		} else {
			$retval .= 'S';
		}

		return $retval;
	}

	/**
	 * @return bool|string
	 */
	function getLicenseText() {
		$license_file = Environment::getBasePath() . DIRECTORY_SEPARATOR . 'LICENSE';

		if ( is_readable( $license_file ) ) {
			$retval = file_get_contents( $license_file );

			if ( strlen( $retval ) > 10 ) {
				return $retval;
			}
		}

		return false;
	}

	/**
	 * @param $val
	 */
	function setIsUpgrade( $val ) {
		$this->is_upgrade = (bool)$val;
	}

	/**
	 * @return bool
	 */
	function getIsUpgrade() {
		return $this->is_upgrade;
	}

	/**
	 * @param object $db_obj
	 * @return bool
	 */
	function setDatabaseConnection( $db_obj ) {
		if ( is_object( $db_obj ) ) {
			if ( $db_obj instanceof ADOdbLoadBalancer ) {
				$this->temp_db = $db_obj->getConnection( 'master' );
			} else if ( isset( $db_obj->_connectionID ) && is_resource( $db_obj->_connectionID ) || is_object( $db_obj->_connectionID ) ) {
				$this->temp_db = $db_obj;
			}

			//Because InstallSchema_*.class.php files utilize the $db variable through the Factory.class.php directly,
			//  any queries will always be load-balanced, even if $this->temp_db is a different connection, and this can cause deadlocks and should never happen.
			//Therefore, to prevent any chance of loadbalanced connections, make sure $db is always just a single master connection.
			global $db;
			$db = $this->temp_db;

			return true;
		}

		return false;
	}

	/**
	 * @return bool|null
	 */
	function getDatabaseConnection() {
		if ( isset( $this->temp_db ) && ( is_resource( $this->temp_db->_connectionID ) || is_object( $this->temp_db->_connectionID ) ) ) {
			return $this->temp_db;
		}

		return false;
	}

	/**
	 * @param $type
	 * @param $host
	 * @param $user
	 * @param $password
	 * @param $database_name
	 * @return bool
	 * @noinspection PhpUndefinedConstantInspection
	 */
	function setNewDatabaseConnection( $type, $host, $user, $password, $database_name ) {
		if ( $this->getDatabaseConnection() !== false ) {
			$this->getDatabaseConnection()->Close();
		}

		try {
			$db = ADONewConnection( $type );
			$db->SetFetchMode( ADODB_FETCH_ASSOC );
			$db->Connect( $host, $user, $password, $database_name );
			if ( Debug::getVerbosity() == 11 ) {
				$db->debug = true;
			}

			if ( is_resource( $db->_connectionID ) || is_object( $db->_connectionID ) ) {
				$this->setDatabaseConnection( $db );

				return true;
			}
		} catch ( Exception $e ) {
			unset( $e );//code standards

			return false;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return string
	 */
	function HumanBoolean( $bool ) {
		if ( $bool === true || strtolower( trim( $bool ) ) == 'true' ) {
			return 'TRUE';
		} else {
			return 'FALSE';
		}
	}

	/**
	 * @param $new_config_vars
	 * @return bool|mixed
	 */
	function writeConfigFile( $new_config_vars ) {
		if ( is_writeable( CONFIG_FILE ) ) {

			require_once( 'Config.php' );
			$config = new Config();
			$data = $config->parseConfig( CONFIG_FILE, 'inicommented' );
			if ( is_object( $data ) && get_class( $data ) == 'PEAR_Error' ) {
				Debug::Arr( $data, 'ERROR modifying Config File!', __FILE__, __LINE__, __METHOD__, 9 );
			} else {
				global $config_vars;

				//Debug::Arr($data, 'Current Config File!', __FILE__, __LINE__, __METHOD__, 9);
				if ( isset( $new_config_vars['path']['base_url'] ) ) {
					$tmp_base_url = $new_config_vars['path']['base_url'];
				} else if ( isset( $config_vars['path']['base_url'] ) ) {
					$tmp_base_url = $config_vars['path']['base_url'];
				}
				if ( isset( $tmp_base_url ) ) {
					$new_config_vars['path']['base_url'] = preg_replace( '@^(?:http://)?([^/]+)@i', '', $tmp_base_url );
					unset( $tmp_base_url );
				}

				if ( isset( $new_config_vars['other']['primary_company_id'] ) && TTUUID::isUUID( $new_config_vars['other']['primary_company_id'] ) == false ) {
					Debug::Text( 'PRIMARY_COMPANY_ID is attempting to be saved as a non-UUID, ignoring...', __FILE__, __LINE__, __METHOD__, 9 );
					unset( $new_config_vars['other']['primary_company_id'] );
				}

				//Check for bug introduced in v7.4.5 that removed all backslashes from paths, to attempt to put them back automatically.
				//This should be able to be removed by v8.0.
				if ( OPERATING_SYSTEM == 'WIN' ) {
					if ( !isset( $new_config_vars['path']['php_cli'] ) && isset( $config_vars['path']['php_cli'] ) && strpos( $config_vars['path']['php_cli'], '\\' ) === false ) {
						Debug::Text( 'Found php_cli path without backslash, trying to correct...', __FILE__, __LINE__, __METHOD__, 9 );
						$new_config_vars['path']['php_cli'] = str_ireplace( ':TimeTrexphpphp-win.exe', ':\TimeTrex\php\php-win.exe', $config_vars['path']['php_cli'] );
					}
					if ( !isset( $new_config_vars['path']['storage'] ) && isset( $config_vars['path']['storage'] ) && strpos( $config_vars['path']['storage'], '\\' ) === false ) {
						Debug::Text( 'Found storage path without backslash, trying to correct...', __FILE__, __LINE__, __METHOD__, 9 );
						$new_config_vars['path']['storage'] = str_ireplace( ':TimeTrexstorage', ':\TimeTrex\storage', $config_vars['path']['storage'] );
					}
					if ( !isset( $new_config_vars['path']['log'] ) && isset( $config_vars['path']['log'] ) && strpos( $config_vars['path']['log'], '\\' ) === false ) {
						Debug::Text( 'Found log path without backslash, trying to correct...', __FILE__, __LINE__, __METHOD__, 9 );
						$new_config_vars['path']['log'] = str_ireplace( ':TimeTrexlog', ':\TimeTrex\log', $config_vars['path']['log'] );
					}
					if ( !isset( $new_config_vars['cache']['dir'] ) && isset( $config_vars['cache']['dir'] ) && strpos( $config_vars['cache']['dir'], '\\' ) === false ) {
						Debug::Text( 'Found cache path without backslash, trying to correct...', __FILE__, __LINE__, __METHOD__, 9 );
						$new_config_vars['cache']['dir'] = str_ireplace( ':TimeTrexcache', ':\TimeTrex\cache', $config_vars['cache']['dir'] );
					}
				}
				//Clear erroneous INI sections due to same above bug.
				$new_config_vars['installer_enabled'] = 'TT_DELETE';
				$new_config_vars['default_interface'] = 'TT_DELETE';

				//Allow passing any empty array that will just rewrite the existing .INI file fixing any problems.
				if ( is_array( $new_config_vars ) ) {
					foreach ( $new_config_vars as $section => $key_value_map ) {

						if ( !is_array( $key_value_map ) && $key_value_map == 'TT_DELETE' ) {
							$item = $data->searchPath( [ $section ] );
							if ( is_object( $item ) ) {
								$item->removeItem();
							}
						} else {

							$key_value_map = (array)$key_value_map;
							foreach ( $key_value_map as $key => $value ) {
								$item = $data->searchPath( [ $section, $key ] );
								if ( is_object( $item ) ) {
									$item->setContent( $value );
								} else {
									$item = $data->searchPath( [ $section ] );
									if ( is_object( $item ) ) {
										$item->createDirective( $key, $value, null, 'top' );
									} else {
										$item = $data->createSection( $section );
										$item->createDirective( $key, $value, null, 'top' );
									}
								}
							}
						}
					}

					//Debug::Arr($data, 'New Config File!', __FILE__, __LINE__, __METHOD__, 9);
					$retval = $config->writeConfig( CONFIG_FILE, 'inicommented' );
					Debug::text( 'Modified Config File! writeConfig Result: ' . $retval, __FILE__, __LINE__, __METHOD__, 9 );

					//Make sure the first line in the file contains "die".
					$contents = file_get_contents( CONFIG_FILE );

					//Make sure we add back in the PHP code for security reasons.
					//BitRock seems to want to remove this and re-arrange the INI file as well for some odd reason.
					if ( stripos( $contents, '<?php' ) === false ) {
						Debug::text( 'Adding back in security feature...', __FILE__, __LINE__, __METHOD__, 9 );
						$contents = "; <?php die('Unauthorized Access...'); //SECURITY MECHANISM, DO NOT REMOVE//?>\n" . $contents;
					}
					file_put_contents( CONFIG_FILE, $contents );

					return $retval;
				}
			}
		} else {
			Debug::text( 'Config File Not Writable!', __FILE__, __LINE__, __METHOD__, 9 );
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function setVersions() {
		if ( is_array( $this->versions ) ) {
			foreach ( $this->versions as $name => $value ) {
				$result = SystemSettingFactory::setSystemSetting( $name, $value );
				if ( $result === false ) {
					return false;
				}
			}

			//Set the date when the upgrade was performed, so we can tell when the version was installed.
			$result = SystemSettingFactory::setSystemSetting( 'system_version_install_date', time() );
			if ( $result === false ) {
				return false;
			}
		}

		return true;
	}

	/*

		Database Schema functions

	*/

	/**
	 * @param $database_name
	 * @return bool
	 */
	function checkDatabaseExists( $database_name ) {
		Debug::text( 'Database Name: ' . $database_name, __FILE__, __LINE__, __METHOD__, 9 );
		$db_conn = $this->getDatabaseConnection();

		if ( $db_conn == false ) {
			return false;
		}

		$database_arr = $db_conn->MetaDatabases();

		if ( in_array( $database_name, $database_arr ) ) {
			Debug::text( 'Exists - Database Name: ' . $database_name, __FILE__, __LINE__, __METHOD__, 9 );

			return true;
		}

		Debug::text( 'Does not Exist - Database Name: ' . $database_name, __FILE__, __LINE__, __METHOD__, 9 );

		return false;
	}

	/**
	 * @param $database_name
	 * @return bool
	 */
	function createDatabase( $database_name ) {
		Debug::text( 'Database Name: ' . $database_name, __FILE__, __LINE__, __METHOD__, 9 );

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb.inc.php' );

		if ( $database_name == '' ) {
			Debug::text( 'Database Name invalid ', __FILE__, __LINE__, __METHOD__, 9 );

			return false;
		}

		$db_conn = $this->getDatabaseConnection();
		if ( $db_conn == false ) {
			Debug::text( 'No Database Connection.', __FILE__, __LINE__, __METHOD__, 9 );

			return false;
		}
		Debug::text( 'Attempting to Create Database...', __FILE__, __LINE__, __METHOD__, 9 );

		$dict = NewDataDictionary( $db_conn );

		$sqlarray = $dict->CreateDatabase( $database_name );

		return $dict->ExecuteSQLArray( $sqlarray );
	}

	/**
	 * @param $table_name
	 * @return bool
	 */
	function checkTableExists( $table_name ) {
		Debug::text( 'Table Name: ' . $table_name, __FILE__, __LINE__, __METHOD__, 9 );
		$db_conn = $this->getDatabaseConnection();

		if ( $db_conn == false ) {
			return false;
		}

		$table_arr = $db_conn->MetaTables();

		if ( in_array( $table_name, $table_arr ) ) {
			Debug::text( 'Exists - Table Name: ' . $table_name, __FILE__, __LINE__, __METHOD__, 9 );

			return true;
		}

		Debug::text( 'Does not Exist - Table Name: ' . $table_name, __FILE__, __LINE__, __METHOD__, 9 );

		return false;
	}

	/**
	 * @return bool
	 */
	function checkSystemSettingTableExists() {
		global $config_vars;
		if ( $this->checkDatabaseExists( $config_vars['database']['database_name'] ) == true ) {
			if ( $this->checkTableExists( 'company' ) == true ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get all the schema groups available for the current product edition. This helps avoid issues where database schema files may exist for ENTERPRISE edition, but we only want to apply PROFESSIONAL edition schema changes.
	 * @return array
	 */
	function getSchemaGroupsForProductEdition() {
		$product_edition = getTTProductEdition();

		$retarr = [ 'A' ]; //Community

		if ( $product_edition >= TT_PRODUCT_ENTERPRISE ) {
			$retarr[] = 'D';
		}

		if ( $product_edition >= TT_PRODUCT_CORPORATE ) {
			$retarr[] = 'C';
		}

		if ( $product_edition >= TT_PRODUCT_PROFESSIONAL ) {
			$retarr[] = 'B';
		}

		sort( $retarr );

		Debug::Arr( $retarr, 'Available Schema Groups: ', __FILE__, __LINE__, __METHOD__, 9 );

		return $retarr;
	}


	/**
	 * Get all schema versions
	 * A=Community, B=Professional, C=Corporate, D=Enterprise, T=Tax
	 * @param array $group
	 * @return array
	 * @noinspection PhpUnusedLocalVariableInspection
	 */
	function getAllSchemaVersions( $group = [ 'A', 'B', 'C', 'D' ] ) {
		if ( !is_array( $group ) ) {
			$group = [ $group ];
		}

		$is_obj = new InstallSchema( $this->getDatabaseDriver(), '', null, $this->getIsUpgrade() );

		$dir = $is_obj->getSQLFileDirectory();
		$schema_versions = [];
		if ( $handle = opendir( $dir ) ) {
			while ( false !== ( $file = readdir( $handle ) ) ) {
				list( $schema_base_name, $extension ) = explode( '.', $file );
				$schema_group = substr( $schema_base_name, -1, 1 );
				Debug::text( 'Schema: ' . $file . ' Group: ' . $schema_group, __FILE__, __LINE__, __METHOD__, 9 );

				if ( $file != '.' && $file != '..'
						&& substr( $file, 1, 0 ) != '.'
						&& in_array( $schema_group, $group ) ) {
					$schema_versions[] = basename( $file, '.sql' );
				}
			}
			closedir( $handle );
		}

		sort( $schema_versions );
		Debug::Arr( $schema_versions, 'Schema Versions', __FILE__, __LINE__, __METHOD__, 9 );

		return $schema_versions;
	}

	/**
	 * @return bool
	 */
	function handleSchemaGroupChange() {
		//Pre v7.0, if the database version is less than 7.0 we need to *copy* the schema version from group B to C so we don't try to upgrade the database with old schemas.
		if ( $this->getIsUpgrade() == true ) {
			$sslf = TTnew( 'SystemSettingListFactory' ); /** @var SystemSettingListFactory $sslf */
			$sslf->getByName( 'system_version' );
			if ( $sslf->getRecordCount() > 0 ) {
				$ss_obj = $sslf->getCurrent();
				$system_version = $ss_obj->getValue();
				Debug::text( 'System Version: ' . $system_version . ' Application Version: ' . APPLICATION_VERSION, __FILE__, __LINE__, __METHOD__, 9 );

				//If the current version is greater than 7.0 and the system_version in the database is less than 7.0, we know we are upgrading from pre7.0 to post7.0.
				if ( version_compare( APPLICATION_VERSION, '7.0', '>=' ) && version_compare( $system_version, '7.0', '<' ) ) {
					Debug::text( 'Upgrade schema groups...', __FILE__, __LINE__, __METHOD__, 9 );

					$sslf->getByName( 'schema_version_group_B' );
					if ( $sslf->getRecordCount() > 0 ) {
						$ss_obj = $sslf->getCurrent();
						$schema_version_group_b = $ss_obj->getValue();
						Debug::text( 'Schema Version Group B: ' . $schema_version_group_b, __FILE__, __LINE__, __METHOD__, 9 );

						$tmp_name = 'schema_version_group_C';
						$tmp_sslf = TTnew( 'SystemSettingListFactory' ); /** @var SystemSettingListFactory $tmp_sslf */
						$tmp_sslf->getByName( $tmp_name );
						if ( $tmp_sslf->getRecordCount() == 1 ) {
							$tmp_obj = $tmp_sslf->getCurrent();
						} else {
							$tmp_obj = TTnew( 'SystemSettingListFactory' ); /** @var SystemSettingListFactory $tmp_obj */
						}
						$tmp_obj->setName( $tmp_name );
						$tmp_obj->setValue( $schema_version_group_b );
						if ( $tmp_obj->isValid() ) {
							if ( $tmp_obj->Save() === false ) {
								return false;
							}

							return true;
						} else {
							return false;
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Creates DB schema starting at and including start_version, and ending at, including end version.
	 * Starting at NULL is first version, ending at NULL is last version.
	 * @param null $start_version
	 * @param null $end_version
	 * @param array $group
	 * @return bool
	 */
	function createSchemaRange( $start_version = null, $end_version = null, $group = null ) {
		global $cache, $config_vars, $PRIMARY_KEY_IS_UUID;

		if ( $this->checkDatabaseSchema() == 1 ) {
			return false;
		}

		if ( $group == null ) {
			$group = $this->getSchemaGroupsForProductEdition();
		}

		//Some schema changes can take a very long time to complete, make sure PHP doesn't cancel out on us.
		ignore_user_abort( true );
		ini_set( 'max_execution_time', 0 );
		ini_set( 'memory_limit', '-1' );

		//Clear all cache before we do any upgrading, this is especially important during development processes
		//if we are switching between databases or reloading databases.
		$this->cleanCacheDirectory();
		$cache->clean(); //Clear all cache.

		//Disable detailed audit logging during schema upgrades, as it breaks upgrading from pre-audit log versions to post-audit log versions.
		//ie: v2.2.22 to v3.3.2.
		$config_vars['other']['disable_audit_log_detail'] = true;

		$this->handleSchemaGroupChange(); //Copy schema group B to C during v7.0 upgrade.

		$schema_versions = $this->getAllSchemaVersions( $group );

		Debug::Arr( $schema_versions, 'Schema Versions: ', __FILE__, __LINE__, __METHOD__, 9 );

		$total_schema_versions = count( $schema_versions );
		if ( is_array( $schema_versions ) && $total_schema_versions > 0 ) {
			//$this->getDatabaseConnection()->StartTrans();
			if ( $this->getIsUpgrade() == true ) {
				$msg = TTi18n::getText( 'Upgrading database' . '...' );
			} else {
				$msg = TTi18n::getText( 'Initializing database' . '...' );
			}

			//Its possible $this->getIsUpgrade() == TRUE when all the schema is created, but no company record exists yet. See APIInstall->setDatabaseSchema()
			// Try to be smarter on when/how we set the PRIMARY_KEY_IS_UUID flag.
			if ( $this->checkTableExists( 'system_setting' ) == true ) {
				if ( (int)SystemSettingFactory::getSystemSettingValueByKey( 'schema_version_group_A' ) < 1100 ) {
					Debug::Text( '  Upgrading database before first UUID schema version... Setting PRIMARY_KEY_IS_UUID = FALSE', __FILE__, __LINE__, __METHOD__, 1 );
					$PRIMARY_KEY_IS_UUID = false;
					$config_vars['other']['disable_audit_log'] = true; //After v11, when UUID is disabled, disable all audit logging too.
				}
			} else { //Likely no DB schema exists yet, so no UUIDs can exist either.
				$PRIMARY_KEY_IS_UUID = false;
				$config_vars['other']['disable_audit_log'] = true; //After v11, when UUID is disabled, disable all audit logging too.
			}

			if ( PHP_SAPI != 'cli' ) { //Don't bother updating progress bar when being run from the CLI.
				$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_schema_versions, null, $msg );
			}

			//Sequences are no longer used after the change to UUID in v11.
			$this->initializeSequences(); //Initialize sequences before we start the schema upgrade to hopefully avoid duplicate key errors.

			$x = 0;
			foreach ( $schema_versions as $schema_version ) {
				if ( ( $start_version === null || $schema_version >= $start_version )
						&& ( $end_version === null || $schema_version <= $end_version )
				) {

					//Wrap each schema version in its own transaction (compared to all schema versions in one transaction), this reduces the length of time any one transaction
					//is open for and should allow vacuum to run more often on PostgreSQL speeding up subsequency schemas.
					//This may make it harder to test rollback schema upgrades during development though.
					$this->getDatabaseConnection()->StartTrans();

					$create_schema_result = $this->createSchema( $schema_version );

					if ( PHP_SAPI != 'cli' ) {
						$this->getProgressBarObject()->set( $this->getAPIMessageID(), $x );
					}

					if ( $create_schema_result === false ) {
						Debug::text( 'CreateSchema Failed! On Version: ' . $schema_version, __FILE__, __LINE__, __METHOD__, 9 );
						$this->getDatabaseConnection()->FailTrans();

						return false;
					}
					$this->getDatabaseConnection()->CompleteTrans();

					$this->postCreateSchema( $schema_version, $create_schema_result ); //This must be called outside the transaction, so it can handle things like VACUUM.
				}

				//Fast way to clear memory caching only between schema upgrades to make sure it doesn't get too big.
				$cache->_memoryCachingArray = [];
				$cache->_memoryCachingCounter = 0;

				$x++;
			}

			if ( PHP_SAPI != 'cli' ) {
				$this->getProgressBarObject()->stop( $this->getAPIMessageID() );
			}

			//Sequences are no longer used after the change to UUID in v11.
			$this->initializeSequences(); //Initialize sequences after we finish as well just in case new errors were created during upgrade...

			//$this->getDatabaseConnection()->FailTrans();
			//$this->getDatabaseConnection()->CompleteTrans();
		}

		//Update Tax Engine/Data Versions
		Debug::text( 'Updating Tax Engine/Data versions...', __FILE__, __LINE__, __METHOD__, 9 );
		require_once( Environment::getBasePath() . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'payroll_deduction' . DIRECTORY_SEPARATOR . 'PayrollDeduction.class.php' );
		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		SystemSettingFactory::setSystemSetting( 'tax_data_version', $pd_obj->getDataVersion() );
		SystemSettingFactory::setSystemSetting( 'tax_engine_version', $pd_obj->getVersion() );

		//Clear all cache after the upgrade as well, as much of it is unlikely to be used again.
		$this->cleanCacheDirectory();
		$cache->clean();

		//Delete orphan files after schema upgrade is fully completed.
		$this->cleanOrphanFiles();

		return true;
	}

	/**
	 * @param $version
	 * @return bool
	 */
	function createSchema( $version ) {
		if ( $version == '' ) {
			return false;
		}

		//$install = false;

		$group = (string)substr( $version, -1, 1 );
		$version_number = (int)substr( $version, 0, ( strlen( $version ) - 1 ) );

		global $PRIMARY_KEY_IS_UUID;
		Debug::text( 'Version: ' . $version . ' Version Number: ' . $version_number . ' Group: ' . $group . ' Primary Key UUID: ' . (int)$PRIMARY_KEY_IS_UUID, __FILE__, __LINE__, __METHOD__, 9 );

		//Only create schema if current system settings do not exist, or they are
		//older then this current schema version.
		if ( $this->checkTableExists( 'system_setting' ) == true ) {
			Debug::text( 'System Setting Table DOES exist...', __FILE__, __LINE__, __METHOD__, 9 );

			$sslf = TTnew( 'SystemSettingListFactory' ); /** @var SystemSettingListFactory $sslf */
			$sslf->getByName( 'schema_version_group_' . substr( $version, -1, 1 ) );
			if ( $sslf->getRecordCount() > 0 ) {
				$ss_obj = $sslf->getCurrent();
				Debug::text( 'Found System Setting Entry: ' . $ss_obj->getValue(), __FILE__, __LINE__, __METHOD__, 9 );

				//The schema group letter is on the end of the schema version in the DB, so make sure if that is the case we always strip it off.
				$numeric_installed_schema = (int)substr( $ss_obj->getValue(), 0, ( strlen( $ss_obj->getValue() ) - 1 ) );
				Debug::text( 'Schema versions, Installed Schema: ' . $ss_obj->getValue() . '(' . $numeric_installed_schema . ') Current Schema: ' . $version_number, __FILE__, __LINE__, __METHOD__, 9 );

				if ( $numeric_installed_schema < $version_number ) {
					Debug::text( 'Schema version is older, installing...', __FILE__, __LINE__, __METHOD__, 9 );
					$install = true;
				} else {
					Debug::text( 'Schema version is equal, or newer then what we are trying to install...', __FILE__, __LINE__, __METHOD__, 9 );
					$install = false;
				}
			} else {
				Debug::text( 'Did not find System Setting Entry...', __FILE__, __LINE__, __METHOD__, 9 );
				$install = true;
			}
		} else {
			Debug::text( 'System Setting Table does not exist...', __FILE__, __LINE__, __METHOD__, 9 );
			$install = true;
		}

		if ( $install === true ) {
			$is_obj = new InstallSchema( $this->getDatabaseDriver(), $version, $this->getDatabaseConnection(), $this->getIsUpgrade() );
			$retval = $is_obj->InstallSchema();
		} else {
			$retval = 'SKIP'; //Schema wasn't installed, so we need a 3rd retval to tell postCreateSchema() that the schema didn't fail, but was skipped instead.
			//Debug::text('  SKIPPING schema version...', __FILE__, __LINE__, __METHOD__, 9);
		}

		return $retval;
	}

	/**
	 * @param $schema_version
	 * @param $create_schema_result
	 * @return bool
	 */
	function postCreateSchema( $schema_version, $create_schema_result ) {
		if ( $create_schema_result === true ) { //Only run post functions when the schema was actually installed and not skipped because the schema version is already ahead.
			if ( $this->getDatabaseType() == 'postgresql' ) {
				if ( $schema_version == '1100A' ) { //Large UUID change.
					Debug::text( '    Running VACUUM FULL ANALYZE...', __FILE__, __LINE__, __METHOD__, 9 );
					$this->getDatabaseConnection()->Execute( 'VACUUM FULL ANALYZE' );
				}
			}
		} else {
			Debug::text( '  NOT running postCreateSchema() functions, schema version failed or was skipped...', __FILE__, __LINE__, __METHOD__, 9 );
		}

		return true;
	}

	/**
	 * @param object $obj
	 * @param $table
	 * @param $class
	 * @param $db_conn
	 * @return bool
	 */
	function initializeSequence( $obj, $table, $class, $db_conn ) {
		$next_insert_id = $obj->getNextInsertId();
		Debug::Text( 'Table: ' . $table . ' Class: ' . $class . ' Sequence Name: ' . $obj->getSequenceName() . ' Next Insert ID: ' . $next_insert_id, __FILE__, __LINE__, __METHOD__, 10 );

		$max_id = (int)$db_conn->GetOne( 'select max(id) from ' . $table );
		if ( $next_insert_id == 0 || $next_insert_id < $max_id ) {
			Debug::Text( '  Out-of-sync sequence table, fixing... Current Max ID: ' . $max_id . ' Next ID: ' . $next_insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			//This can be helpful with PostgreSQL as well as sequences can get out of sync there too if the schema was created incorrectly.
			$query = 'select setval(\'' . $obj->getSequenceName() . '\', ' . ( $max_id + 1 ) . ')';
			//Debug::Text('  Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);
			$db_conn->Execute( $query );
		} else {
			Debug::Text( '  Sequence is in sync, not updating...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return true;
	}

	/**
	 * This can help prevent race conditions when creating new tables.
	 * It will also correct any corrupt sequences that don't match their parent tables.
	 * @return bool
	 */
	function initializeSequences() {
		global $PRIMARY_KEY_IS_UUID;
		if ( $PRIMARY_KEY_IS_UUID == true ) {
			Debug::Text( '  Skipping sequence initialization, in UUID mode!', __FILE__, __LINE__, __METHOD__, 10 );

			return true; //Sequences can be ignored when using UUID primary keys.
		}

		require_once( Environment::getBasePath() . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'TableMap.inc.php' );
		global $global_table_map;

		$db_conn = $this->getDatabaseConnection();

		if ( $db_conn == false ) {
			return false;
		}

		$table_arr = $db_conn->MetaTables();

		foreach ( $global_table_map as $table => $class ) {
			if ( class_exists( $class ) && in_array( $table, $table_arr ) ) {
				$obj = new $class;

				if ( $obj->getSequenceName() != '' ) {
					$this->initializeSequence( $obj, $table, $class, $db_conn );
				}
			} else {
				Debug::Text( '  Missing class for table: ' . $table . ' Class: ' . $class, __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return true;
	}

	/*

		System Requirements

	*/

	/**
	 * @return string
	 */
	function getPHPVersion() {
		return PHP_VERSION;
	}

	/**
	 * @param null $php_version
	 * @return int
	 */
	function checkPHPVersion( $php_version = null ) {
		// Return
		// 0 = OK
		// 1 = Invalid
		// 2 = UnSupported

		/*
		 *
		 *  *** UPDATE APINotification.class.php, install.php when minimum PHP version changes, as it gives early warning to users. ***
		 *
		 */

		if ( $php_version == null ) {
			$php_version = $this->getPHPVersion();
		}
		Debug::text( 'Comparing with Version: ' . $php_version, __FILE__, __LINE__, __METHOD__, 9 );

		$min_version = '7.2.0';  //Change install.php as well, as some versions break backwards compatibility, so we need early checks as well.
		$max_version = '8.0.99'; //Change install.php as well, as some versions break backwards compatibility, so we need early checks as well.

		$unsupported_versions = [ '' ];

		/*
			Invalid PHP Versions:
				v5.4.0+ - (Fixed as of 10-Apr-13) Fails due to deprecated call-time references (&$), disable for now.
				v5.3.0+ - Fails due to deprecated functions still in use. This is mostly fixed as of v3.1.0-rc1, leave enabled for now.
				v5.0.4 - Fails to assign object values by ref. In ViewTimeSheet.php $smarty->assign_by_ref( $pp_obj->getId() ) fails.
				v5.2.2 - Fails to populate $HTTP_RAW_POST_DATA http://bugs.php.net/bug.php?id=41293
					   - Implemented work around in global.inc.php
		*/
		$invalid_versions = [ '' ];

		if ( version_compare( $php_version, $min_version, '<' ) == 1 ) {
			//Version too low
			$retval = 1;
		} else if ( version_compare( $php_version, $max_version, '>' ) == 1 ) {
			//UnSupported
			$retval = 2;
		} else {
			$retval = 0;
		}

		foreach ( $unsupported_versions as $unsupported_version ) {
			if ( version_compare( $php_version, $unsupported_version, 'eq' ) == 1 ) {
				$retval = 2;
				break;
			}
		}

		foreach ( $invalid_versions as $invalid_version ) {
			if ( version_compare( $php_version, $invalid_version, 'eq' ) == 1 ) {
				$retval = 1;
				break;
			}
		}

		//Debug::text('RetVal: '. $retval, __FILE__, __LINE__, __METHOD__, 9);
		return $retval;
	}

	/**
	 * Require 64-bit PHP versions, since accrual policies with length of service dates >2038 will fail otherwise.
	 * @return int
	 */
	function checkPHPINTSize() {
		return 0; //Don't require this in v12.7.1, wait until at least 12.7.2 or after the US tax form updates.

		if ( PHP_INT_SIZE === 8 ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @param null $type
	 * @return int|string
	 */
	function getDatabaseType( $type = null ) {
		if ( $type != '' ) {
			$db_type = $type;
		} else {
			//$db_type = $this->config_vars['database']['type'];
			$db_type = $this->getDatabaseDriver();
		}

		if ( stristr( $db_type, 'postgres' ) ) {
			$retval = 'postgresql';
		} else {
			$retval = 1;
		}

		return $retval;
	}

	/**
	 * @param $setting_name
	 * @return mixed|null
	 */
	function getPHPINISize( $setting_name ) {
		//
		// NULL = unlimited
		// INT = limited to that value

		$size = convertHumanSizeToBytes( ini_get( $setting_name ) );
		//Debug::text('RAW Limit: '. $size, __FILE__, __LINE__, __METHOD__, 9);

		if ( $size == '' || $size <= 0 ) {
			return null;
		}

		return $size;
	}

	/**
	 * @return string
	 */
	function getPHPConfigFile() {
		return get_cfg_var( "cfg_file_path" );
	}

	/**
	 * @return mixed|string
	 */
	function getConfigFile() {
		return CONFIG_FILE;
	}

	/**
	 * @return string
	 */
	function getPHPIncludePath() {
		return get_cfg_var( "include_path" );
	}

	/**
	 * @return array|bool|null
	 */
	function getDatabaseVersion() {
		$db_conn = $this->getDatabaseConnection();
		if ( $db_conn == false ) {
			Debug::text( 'WARNING: No Database Connection...', __FILE__, __LINE__, __METHOD__, 9 );

			return null;
		}

		if ( $this->getDatabaseType() == 'postgresql' ) {
			$version = @pg_version();
			Debug::Arr( $version, 'PostgreSQL Version: ', __FILE__, __LINE__, __METHOD__, 10 );
			if ( $version == false ) {
				//No connection
				return null;
			} else {
				return $version['server'];
			}
		}

		return false;
	}

	/**
	 * @return array
	 */
	function getDatabaseTypeArray() {
		$retval = [];
		$retval['postgres8'] = 'PostgreSQL';

		return $retval;
	}

	/**
	 * @return int
	 */
	function checkSystemTimeZone() {
		// Return
		//
		// 0 = OK
		// 1 = Invalid

		$retval = 0;

		if ( isset( $this->config_vars['other']['system_timezone'] ) && $this->config_vars['other']['system_timezone'] != '' ) {
			$current_time_zone = date_default_timezone_get();

			$php_timezone_result = @date_default_timezone_set( $this->config_vars['other']['system_timezone'] );
			if ( $php_timezone_result !== true ) {
				Debug::Text( 'ERROR: Invalid timezone for PHP: ' . $this->config_vars['other']['system_timezone'], __FILE__, __LINE__, __METHOD__, 10 );
				$retval = 1;
			}

			@date_default_timezone_set( $current_time_zone );
		}

		return $retval;
	}

	/**
	 * @return int
	 */
	function checkFilePermissions() {
		// Return
		//
		// 0 = OK
		// 1 = Invalid
		// 2 = Unsupported
		if ( PRODUCTION == false || DEPLOYMENT_ON_DEMAND == true ) {
			return 0; //Skip permission checks.
		}

		$start_time = time();

		$is_root_user = Misc::isCurrentOSUserRoot();
		if ( $is_root_user == true ) {
			$web_server_user = Misc::findWebServerOSUser();
			Debug::Text( 'Current user is root, attempt to fix any permissions that fail... New User: ' . $web_server_user, __FILE__, __LINE__, __METHOD__, 10 );
		}

		//Always check the main directory first.
		$main_dir = realpath( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR );

		$dirs = [ $main_dir ];

		//Make sure we check all files inside the log, storage, and cache directories, in case some files were created with the incorrect permissions and can't be overwritten.
		// However if these dirs are all sub-dirs off the main dir, don't bother adding them, as they will be checked when recursively iterating over the main dir anyways.
		if ( isset( $this->config_vars['cache']['dir'] ) && Misc::isSubDirectory( $this->config_vars['cache']['dir'], $main_dir ) == false ) {
			$dirs[] = $this->config_vars['cache']['dir'];
		}
		if ( isset( $this->config_vars['path']['log'] ) && Misc::isSubDirectory( $this->config_vars['path']['log'], $main_dir ) == false ) {
			$dirs[] = $this->config_vars['path']['log'];
		}
		if ( isset( $this->config_vars['path']['storage'] ) && Misc::isSubDirectory( $this->config_vars['path']['storage'], $main_dir ) == false ) {
			$dirs[] = $this->config_vars['path']['storage'];
		}

		if ( PHP_SAPI != 'cli' ) { //Don't bother updating progress bar when being run from the CLI.
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), 10000, null, TTi18n::getText( 'Check File Permission...' ) );
		}

		$i = 0;
		$d = 0;
		$files_checked = 0;
		foreach ( $dirs as $dir ) {
			$x = 0;
			$random_num = rand( 75, 150 ); //Use random numbers so we can skip files at random and hopefully eventually check them all at some point.

			Debug::Text( 'Checking directory readable/writable: ' . $dir . ' Random Num: ' . $random_num, __FILE__, __LINE__, __METHOD__, 10 );
			if ( is_dir( $dir ) && is_readable( $dir ) ) {
				try {
					$rdi = new RecursiveDirectoryIterator( $dir, RecursiveIteratorIterator::SELF_FIRST );
					foreach ( new RecursiveIteratorIterator( $rdi ) as $file_name => $cur ) {
						//If we have checked more than 10,000 files in a single directory, only check roughly every 100th random file after that to avoid this from taking forever if they have hundreds of thousands or millions of punch_images.
						// Always run full loop when $i % 100 == 0 so we update the progress bar at the bottom.
						// Only start skipping checks in directories other than the main TimeTrex directory, as that is typically the most important for upgrades anyways..
						if ( $d > 0 && $x > 10000 && ( $x % $random_num ) != 0 && ( $i % 100 ) != 0 ) {
							//Debug::Text('  Skipping: ('. $i .'-'. $x.')'. $file_name, __FILE__, __LINE__, __METHOD__, 10);
							$i++;
							$x++;
							continue;
						}
						//Debug::Text('  Checking: ('. $i .'-'. $x.')'. $file_name, __FILE__, __LINE__, __METHOD__, 10);


						//Check if its "." or current directory, and format it as a directory, so file_exists() doesn't fail below.
						// If /var/cache/timetrex/ is chmod 660, file_exists() returns FALSE on '/var/cache/timetrex/.' but TRUE on '/var/cache/timetrex/'
						if ( strcmp( basename( $file_name ), '.' ) == 0 ) {
							$file_name = dirname( $file_name ) . DIRECTORY_SEPARATOR;
						}

						//Check if the file is ignored.
						if (
							//strcmp( basename($file_name), '.') == 0 OR //Make sure we do check "." (the current directory). As permissions could be denied on it, but allowed on all sub-dirs/files.
								strcmp( basename( $file_name ), '..' ) == 0
								|| strpos( $file_name, '.git' ) !== false
								|| strcmp( basename( $file_name ), '.htaccess' ) == 0 ) { //.htaccess files often aren't writable by the webserver.
							continue;
						}

						//Its possible if it takes a long time to iterate the files, they could be gone by the time we get to them, so just check them again.
						if ( file_exists( $file_name ) == false ) {
							Debug::Text( '  Skipping: ' . $file_name . ' does not exist... File Exists: ' . (int)file_exists( $file_name ), __FILE__, __LINE__, __METHOD__, 10 );
							continue;
						}

						if ( $is_root_user == true && $web_server_user != false && @fileowner( $file_name ) === 0 ) { //Check if file is owned by root. If so, change the owner before we check is readable/writable.
							Debug::Text( '  Changing ownership of: ' . $file_name, __FILE__, __LINE__, __METHOD__, 10 );
							@chown( $file_name, $web_server_user );
							@chgrp( $file_name, $web_server_user );
						}

						//Debug::Text('Checking readable/writable: '. $file_name, __FILE__, __LINE__, __METHOD__, 10);
						if ( is_readable( $file_name ) == false ) { //Since file_exists() is called a few lines above, no need to do it again here.
							Debug::Text( 'File or directory is not readable: ' . $file_name, __FILE__, __LINE__, __METHOD__, 10 );
							$this->setExtendedErrorMessage( 'checkFilePermissions', 'Not Readable: ' . $file_name );

							return 1; //Invalid
						}

						if ( Misc::isWritable( $file_name ) == false ) {
							Debug::Text( 'File or directory is not writable: ' . $file_name, __FILE__, __LINE__, __METHOD__, 10 );
							$this->setExtendedErrorMessage( 'checkFilePermissions', 'Not writable: ' . $file_name );

							return 1; //Invalid
						}

						//Do this last, as it can take a long time on some systems using a slow file system.
						if ( $i > 0 && ( $i % 1000 ) == 0 ) {
							Debug::Text( '  Batch Completed: ' . $i . ' Current File: ' . $file_name, __FILE__, __LINE__, __METHOD__, 10 );
							if ( PHP_SAPI != 'cli' ) {
								$this->getProgressBarObject()->set( $this->getAPIMessageID(), $i );
							}
						}

						$i++;
						$x++;
						$files_checked++;
					}
					unset( $cur ); //code standards
				} catch ( Exception $e ) {
					Debug::Text( 'Failed opening/reading file or directory: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );

					return 1;
				}
			} else {
				Debug::Text( 'Failed reading directory: ' . $dir, __FILE__, __LINE__, __METHOD__, 10 );
				$this->setExtendedErrorMessage( 'checkFilePermissions', 'Not Readable: ' . $dir );

				return 1;
			}

			Debug::Text( '  Done Checking directory readable/writable: ' . $dir, __FILE__, __LINE__, __METHOD__, 10 );
			$d++;
		}

		if ( PHP_SAPI != 'cli' ) {
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), 10000 );

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );
		}

		Debug::Text( 'All Files/Directories (' . $i . ') are readable/writable! Files Checked: ' . $files_checked . '/' . $i . '(' . round( ( ( $files_checked / $i ) * 100 ) ) . '%) in: ' . ( time() - $start_time ) . 's', __FILE__, __LINE__, __METHOD__, 10 );

		return 0;
	}

	/**
	 * @return int
	 */
	function checkFileChecksums() {
		// Return
		//
		// 0 = OK
		// 1 = Invalid
		// 2 = Unsupported

		if ( PRODUCTION == false || DEPLOYMENT_ON_DEMAND == true ) {
			return 0; //Skip checksums.
		}

		//Load checksum file.

		$checksum_file = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'files.sha1';

		if ( file_exists( $checksum_file ) ) {
			$checksum_data = file_get_contents( $checksum_file );
			$checksums = explode( "\n", $checksum_data );
			unset( $checksum_data );
			if ( is_array( $checksums ) ) {

				if ( PHP_SAPI != 'cli' ) { //Don't bother updating progress bar when being run from the CLI.
					$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $checksums ), null, TTi18n::getText( 'Check File Checksums...' ) );
				}

				$i = 0;
				foreach ( $checksums as $checksum_line ) {

					//1st line contains the TT version for the checksums, make sure it matches current version.
					if ( $i == 0 ) {
						if ( preg_match( '/\d+\.\d+\.\d+/', $checksum_line, $checksum_version ) ) {
							Debug::Text( 'Checksum version: ' . $checksum_version[0], __FILE__, __LINE__, __METHOD__, 10 );
							if ( version_compare( APPLICATION_VERSION, $checksum_version[0], '=' ) ) {
								Debug::Text( 'Checksum version matches!', __FILE__, __LINE__, __METHOD__, 10 );
							} else {
								Debug::Text( 'ERROR: Checksum version DOES NOT match! Version: ' . APPLICATION_VERSION . ' Checksum Version: ' . $checksum_version[0], __FILE__, __LINE__, __METHOD__, 10 );
								$this->setExtendedErrorMessage( 'checkFileChecksums', 'Application version does not match checksum version: ' . $checksum_version[0] );

								return 1;
							}
						} else {
							Debug::Text( 'Checksum version not found in file: ' . $checksum_line, __FILE__, __LINE__, __METHOD__, 10 );
						}
					} else if ( strlen( $checksum_line ) > 1 ) {
						$split_line = explode( ' ', $checksum_line );
						if ( is_array( $split_line ) ) {
							$file_name = Environment::getBasePath() . str_replace( '/', DIRECTORY_SEPARATOR, str_replace( './', '', trim( $split_line[2] ) ) );
							$checksum = trim( $split_line[0] );

							if ( file_exists( $file_name ) ) {
								$my_checksum = @sha1_file( $file_name );
								if ( $my_checksum == $checksum ) {
									//Debug::Text('File: '. $file_name .' Checksum: '. $checksum .' MATCHES', __FILE__, __LINE__, __METHOD__, 10);
									unset( $my_checksum ); //NoOp
								} else {
									Debug::Text( 'File: ' . $file_name . ' Checksum: ' . $my_checksum . ' DOES NOT match provided checksum of: ' . $checksum, __FILE__, __LINE__, __METHOD__, 10 );
									$this->setExtendedErrorMessage( 'checkFileChecksums', 'Checksum does not match: ' . $file_name );

									return 1; //Invalid
								}
								unset( $my_checksum );
							} else {
								Debug::Text( 'File does not exist: ' . $file_name, __FILE__, __LINE__, __METHOD__, 10 );
								$this->setExtendedErrorMessage( 'checkFileChecksums', 'File does not exist: ' . $file_name );

								return 1; //Invalid
							}
						}
						unset( $split_line, $file_name, $checksum );
					}

					if ( PHP_SAPI != 'cli' && ( $i % 100 ) == 0 ) {
						$this->getProgressBarObject()->set( $this->getAPIMessageID(), $i );
					}

					$i++;
				}

				if ( PHP_SAPI != 'cli' ) {
					$this->getProgressBarObject()->stop( $this->getAPIMessageID() );
				}

				return 0; //OK
			}
		} else {
			Debug::Text( 'Checksum file does not exist: ' . $checksum_file, __FILE__, __LINE__, __METHOD__, 10 );
			$this->setExtendedErrorMessage( 'checkFileChecksums', 'Checksum file does not exist: ' . $checksum_file );
		}

		return 1; //Invalid
	}

	/**
	 * @return int
	 */
	function checkDatabaseType() {
		// Return
		//
		// 0 = OK
		// 1 = Invalid

		$retval = 1;

		if ( function_exists( 'pg_connect' ) ) {
			$retval = 0;
		}

		return $retval;
	}

	/**
	 * @return int
	 */
	function checkDatabaseVersion() {
		$db_version = (string)$this->getDatabaseVersion();
		if ( $db_version == null ) {
			Debug::Text( 'WARNING:  No database connection, unable to verify version!', __FILE__, __LINE__, __METHOD__, 10 );

			return 0;
		}

		if ( $this->getDatabaseType() == 'postgresql' ) {
			if ( $db_version == null || version_compare( $db_version, '9.6', '>=' ) == 1 ) { //v9.6 has JSONB support.
				return 0;
			}
		}

		Debug::Text( 'ERROR: Database version failed!', __FILE__, __LINE__, __METHOD__, 10 );

		return 1;
	}

	/**
	 * @return bool|int
	 */
	function checkDatabaseSchema() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			$db_conn = $this->getDatabaseConnection();
			if ( $db_conn == false ) {
				Debug::text( 'No Database Connection.', __FILE__, __LINE__, __METHOD__, 9 );

				return false;
			}

			if ( $this->checkTableExists( 'system_setting' ) == true ) {
				$sslf = TTnew( 'SystemSettingListFactory' ); /** @var SystemSettingListFactory $sslf */
				$sslf->getByName( 'schema_version_group_B' );
				if ( $sslf->getRecordCount() == 1 ) {
					Debug::text( 'ERROR: Database schema out of sync with edition...', __FILE__, __LINE__, __METHOD__, 9 );

					return 1;
				}
			}
		}

		return 0;
	}

	/**
	 * @return bool
	 */
	function isSUDOinstalled() {
		if ( OPERATING_SYSTEM == 'LINUX' ) {
			exec( 'which sudo', $output, $exit_code );
			if ( $exit_code == 0 && $output != '' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getWebServerUser() {
		return Misc::getCurrentOSUser();
	}

	/**
	 * @return bool|string
	 */
	function getScheduleMaintenanceJobsCommand() {
		$command = false;
		if ( OPERATING_SYSTEM == 'LINUX' ) {
			if ( $this->getWebServerUser() != '' ) {
				$command = Environment::getBasePath() . 'install_cron.sh ' . $this->getWebServerUser();
			}
		} else if ( OPERATING_SYSTEM == 'WIN' ) {
			$system_root = getenv( 'SystemRoot' );
			if ( $system_root != '' ) {
				//Example: schtasks /create /SC minute /TN timetrex_maintenance /TR "c:\timetrex\php\php-win.exe" "c:\timetrex\timetrex\maint\cron.php"
				$command = $system_root . '\system32\schtasks /create /SC minute /TN timetrex_maintenance /TR ""' . Environment::getBasePath() . '..\php\php-win.exe" "' . Environment::getBasePath() . 'maint\cron.php""';
			}
		}

		return $command;
	}

	/**
	 * @return int
	 */
	function ScheduleMaintenanceJobs() {
		$command = $this->getScheduleMaintenanceJobsCommand();
		if ( $command != '' ) {
			exec( $command, $output, $exit_code );
			Debug::Arr( $output, 'Schedule Maintenance Jobs Command: ' . $command . ' Exit Code: ' . $exit_code . ' Output: ', __FILE__, __LINE__, __METHOD__, 10 );
			if ( $exit_code == 0 ) {
				return 0;
			}
		}

		return 1; //Fail so we can display the command to the user instead.
	}

	/**
	 * @return string
	 */
	function getBaseURL() {
		return Misc::getURLProtocol() . '://' . Misc::getHostName( true ) . Environment::getBaseURL() . 'install/install.php'; //Check for a specific file, so we can be sure its not incorrect.
	}

	/**
	 * @return mixed
	 */
	function getRecommendedBaseURL() {
		return str_replace( [ 'install', 'api/json' ], [ '', '' ], dirname( $_SERVER['SCRIPT_NAME'] ) ) . '/interface';
	}

	/**
	 * @return int
	 */
	function checkBaseURL() {
		$url = $this->getBaseURL();
		$headers = @get_headers( $url );
		Debug::Arr( $headers, 'Checking Base URL: ' . $url, __FILE__, __LINE__, __METHOD__, 9 );
		if ( isset( $headers[0] ) && stripos( $headers[0], '404' ) !== false ) {
			return 1; //Not found
		} else {
			return 0; //Found
		}
	}

	/**
	 * @return string
	 */
	function getPHPOpenBaseDir() {
		return ini_get( 'open_basedir' );
	}

	/**
	 * @return string
	 */
	function getPHPCLIDirectory() {
		return dirname( $this->getPHPCLI() );
	}

	/**
	 * @return int
	 */
	function checkPHPOpenBaseDir() {
		$open_basedir = $this->getPHPOpenBaseDir();
		Debug::Text( 'Open BaseDir: ' . $open_basedir, __FILE__, __LINE__, __METHOD__, 9 );
		if ( $open_basedir == '' ) {
			return 0;
		} else {
			if ( $this->getPHPCLI() != '' ) {
				//Check if PHPCLIDir is contained in open_basedir, or if open_basedir is contained in the PHPCLIDir.
				//For cases like: open_basedir=/var/www/vhosts/domain/ and php_cli=/var/www/vhosts/domain/usr/bin/
				// Or for cases: open_basedir=/usr/ and php_cli=/usr/bin/
				if ( strpos( $open_basedir, $this->getPHPCLIDirectory() ) !== false || strpos( $this->getPHPCLIDirectory(), $open_basedir ) !== false ) {
					return 0;
				} else {
					Debug::Text( 'PHP CLI Binary (' . dirname( $this->getPHPCLIDirectory() ) . ') NOT found in Open BaseDir: ' . $open_basedir, __FILE__, __LINE__, __METHOD__, 9 );
				}
			} else {
				return 0;
			}
		}

		return 1;
	}

	/**
	 * @return bool
	 */
	function getPHPCLI() {
		if ( isset( $this->config_vars['path']['php_cli'] ) ) {
			return $this->config_vars['path']['php_cli'];
		}

		return false;
	}

	/**
	 * @return int
	 */
	function checkPHPCLIBinary() {
		if ( $this->getPHPCLI() != '' ) {
			//Sometimes the user may mistaken make the PHP CLI the directory, rather than the executeable itself. Make sure we catch that case.
			if ( is_dir( $this->getPHPCLI() ) == false && is_executable( $this->getPHPCLI() ) == true ) {
				return 0;
			}
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkDiskSpace() {
		$free_space = disk_free_space( dirname( __FILE__ ) );
		$total_space = disk_total_space( dirname( __FILE__ ) );
		$free_space_percent = ( ( $free_space / $total_space ) * 100 );

		$min_free_space = 2000000000; //2GB in bytes.
		$min_free_percent = 6;        //6%, due to Linux often having a 5% buffer for root.

		Debug::Text( 'Free Space: ' . $free_space . ' Free Percent: ' . $free_space_percent, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $free_space > $min_free_space && $free_space_percent > $min_free_percent ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return string
	 */
	function getPHPCLIRequirementsCommand() {
		$command = '"' . $this->getPHPCLI() . '" "' . Environment::getBasePath() . 'tools' . DIRECTORY_SEPARATOR . 'unattended_upgrade.php" --config "' . CONFIG_FILE . '" --requirements_only --web_installer';

		return $command;
	}

	/**
	 * Only check this if *not* being called from the CLI to prevent infinite loops.
	 * @return int
	 */
	function checkPHPCLIRequirements() {
		if ( $this->checkPHPCLIBinary() === 0 ) {
			$command = $this->getPHPCLIRequirementsCommand();
			exec( $command, $output, $exit_code );
			Debug::Arr( $output, 'PHP CLI Requirements Command: ' . $command . ' Exit Code: ' . $exit_code . ' Output: ', __FILE__, __LINE__, __METHOD__, 10 );
			if ( $exit_code == 0 ) {
				return 0;
			} else {
				$this->setExtendedErrorMessage( 'checkPHPCLIRequirements', 'PHP CLI Requirements Output: ' . '<br>' . implode( '<br>', (array)$output ) );
			}
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkPEAR() {
		@include_once( 'PEAR.php' );

		if ( class_exists( 'PEAR' ) ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkPEARHTTP_Download() {
		include_once( 'HTTP/Download.php' );

		if ( class_exists( 'HTTP_Download' ) ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkPEARMail() {
		include_once( 'Mail.php' );

		if ( class_exists( 'Mail' ) ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkPEARMail_Mime() {
		include_once( 'Mail/mime.php' );

		if ( class_exists( 'Mail_Mime' ) ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkZIP() {
		if ( class_exists( 'ZipArchive' ) ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkMAIL() {
		if ( function_exists( 'mail' ) ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkGETTEXT() {
		if ( function_exists( 'gettext' ) ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkINTL() {
		//Don't make this a hard requirement in v10 upgrade as its too close to the end of the year.
		return 0;

//		if ( function_exists('locale_get_default') ) {
//			return 0;
//		}
//
//		return 1;
	}

	/**
	 * @return int
	 */
	function checkBCMATH() {
		if ( function_exists( 'bcscale' ) ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkMBSTRING() {
		if ( function_exists( 'mb_detect_encoding' ) ) {
			return 0;
		}

		return 1;
	}

	/**
	 * No longer required, used pure PHP implemented TTDate::EasterDays() instead.
	 * @return int
	 */
	function checkCALENDAR() {
		if ( function_exists( 'easter_date' ) ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkSOAP() {
		if ( class_exists( 'SoapServer' ) ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkOpenSSL() {
		//FIXME: Automated installer on OSX/Linux doesnt compile SSL into PHP.
		if ( function_exists( 'openssl_encrypt' ) || strtoupper( substr( PHP_OS, 0, 3 ) ) !== 'WIN' ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkGD() {
		if ( function_exists( 'imagefontheight' ) ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkJSON() {
		if ( function_exists( 'json_decode' ) ) {
			return 0;
		}

		return 1;
	}

	/**
	 * Not currently mandatory, but can be useful to provide better SOAP timeouts.
	 * @return int
	 */
	function checkCURL() {
		if ( function_exists( 'curl_exec' ) ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkSimpleXML() {
		if ( class_exists( 'SimpleXMLElement' ) ) {
			return 0;
		}

		return 1;
	}


	/**
	 * @return int
	 */
	function checkWritableConfigFile() {
		if ( Misc::isWritable( CONFIG_FILE ) ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkWritableCacheDirectory() {
		if ( isset( $this->config_vars['cache']['dir'] ) && is_dir( $this->config_vars['cache']['dir'] ) && Misc::isWritable( $this->config_vars['cache']['dir'] ) ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkSafeCacheDirectory() {
		//Make sure the storage path isn't inside an publically accessible directory
		if ( isset( $this->config_vars['cache']['dir'] ) && Misc::isSubDirectory( $this->config_vars['cache']['dir'], Environment::getBasePath() ) == false ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @param string $exclude_regex_filter
	 * @return bool
	 */
	function cleanCacheDirectory( $exclude_regex_filter = '\.ZIP|\.lock|.state|upgrade_staging' ) {
		return Misc::cleanDir( $this->config_vars['cache']['dir'], true, true, false, $exclude_regex_filter ); //Don't clean UPGRADE.ZIP file and 'upgrade_staging' directory.
	}

	/**
	 * @return bool
	 */
	function cleanOrphanFiles() {
		if ( PRODUCTION == true ) {
			//Load delete file list.
			$file_list = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'files.delete';

			if ( file_exists( $file_list ) ) {
				$file_list_data = file_get_contents( $file_list );
				$files = explode( "\n", $file_list_data );
				unset( $file_list_data );
				if ( is_array( $files ) ) {
					foreach ( $files as $file ) {
						if ( $file != '' ) {
							$file = Environment::getBasePath() . str_replace( [ '/', '\\' ], DIRECTORY_SEPARATOR, $file ); //Prefix base path to all files.
							if ( file_exists( $file ) ) {
								if ( @dir( $file ) ) {
									Debug::Text( 'Deleting Orphaned Dir: ' . $file, __FILE__, __LINE__, __METHOD__, 9 );
									Misc::cleanDir( $file, true, true, true );
								} else {
									Debug::Text( 'Deleting Orphaned File: ' . $file, __FILE__, __LINE__, __METHOD__, 9 );
									@unlink( $file );
								}
							} else {
								Debug::Text( 'Orphaned File/Dir does not exist, not deleting: ' . $file, __FILE__, __LINE__, __METHOD__, 9 );
							}
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * @return int
	 */
	function checkCleanCacheDirectory() {
		if ( DEPLOYMENT_ON_DEMAND == false ) {
			if ( is_dir( $this->config_vars['cache']['dir'] ) ) {
				$raw_cache_files = @scandir( $this->config_vars['cache']['dir'] );

				if ( is_array( $raw_cache_files ) && count( $raw_cache_files ) > 0 ) {
					foreach ( $raw_cache_files as $cache_file ) {
						if ( $cache_file != '.' && $cache_file != '..' && stristr( $cache_file, '.state' ) === false && stristr( $cache_file, '.lock' ) === false && stristr( $cache_file, '.ZIP' ) === false && stristr( $cache_file, 'upgrade_staging' ) === false ) { //Ignore UPGRADE.ZIP files.
							Debug::Text( 'Cache file remaining: ' . $cache_file, __FILE__, __LINE__, __METHOD__, 9 );

							return 1;
						}
					}
				}
			}
		}

		return 0;
	}

	/**
	 * @return int
	 */
	function checkWritableStorageDirectory() {
		if ( isset( $this->config_vars['path']['storage'] ) && is_dir( $this->config_vars['path']['storage'] ) && Misc::isWritable( $this->config_vars['path']['storage'] ) ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkSafeStorageDirectory() {
		//Make sure the storage path isn't inside an publically accessible directory
		if ( isset( $this->config_vars['path']['storage'] ) && Misc::isSubDirectory( $this->config_vars['path']['storage'], Environment::getBasePath() ) == false ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkWritableLogDirectory() {
		if ( isset( $this->config_vars['path']['log'] ) && is_dir( $this->config_vars['path']['log'] ) && Misc::isWritable( $this->config_vars['path']['log'] ) ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkSafeLogDirectory() {
		//Make sure the storage path isn't inside an publically accessible directory
		if ( isset( $this->config_vars['path']['log'] ) && Misc::isSubDirectory( $this->config_vars['path']['log'], Environment::getBasePath() ) == false ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return array
	 */
	function getCriticalFunctionList() {
		$critical_functions = [ 'system', 'exec', 'passthru', 'shell_exec', 'curl', 'curl_exec', 'curl_multi_exec', 'parse_ini_file', 'unlink', 'rename', 'eval' ]; //'pcntl_alarm'

		return $critical_functions;
	}

	/**
	 * @return string
	 */
	function getCriticalDisabledFunctionList() {
		return implode( ',', $this->critical_disabled_functions );
	}

	/**
	 * Check to see if they have disabled functions in there PHP.ini file.
	 * This can cause all sorts of strange failures, but most often they have system(), exec() and other OS/file system related functions disabled that completely breaks things.
	 * @return int
	 */
	function checkPHPDisabledFunctions() {
		$critical_functions = $this->getCriticalFunctionList();
		$disabled_functions = explode( ',', ini_get( 'disable_functions' ) );

		$this->critical_disabled_functions = array_intersect( $critical_functions, $disabled_functions );
		if ( count( $this->critical_disabled_functions ) == 0 ) {
			return 0;
		}

		Debug::Arr( $this->critical_disabled_functions, 'Disabled functions that must be enabled: ', __FILE__, __LINE__, __METHOD__, 10 );

		return 1;
	}

	/**
	 * @return int
	 */
	function checkPHPSafeMode() {
		if ( ini_get( 'safe_mode' ) != '1' ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkPHPAllowURLFopen() {
		if ( ini_get( 'allow_url_fopen' ) == '1' ) {
			return 0;
		}

		return 1;
	}

	/**
	 * @return int
	 */
	function checkPHPMaxPostSize() {
		if ( PHP_SAPI == 'cli' ) {
			return 0;
		} else {
			$size = $this->getPHPINISize( 'post_max_size' );

			//This must be greater than or equal to checkPHPMaxUploadSize() below.
			$required_max_size = 2; //PHP default is 2M
			if ( getTTProductEdition() >= 20 ) { //Corporate with document management
				$required_max_size = 25;         //Recommend 128M or more?
			}

			if ( $size == null || $size >= ( $required_max_size * 1000 * 1000 ) ) {
				return 0;
			}

			return 1;
		}
	}

	/**
	 * @return int
	 */
	function checkPHPMaxUploadSize() {
		if ( PHP_SAPI == 'cli' ) {
			return 0;
		} else {
			$size = $this->getPHPINISize( 'upload_max_filesize' );

			//This must be less than or equal to checkPHPMaxPostSize() above.
			$required_max_size = 2; //PHP default is 2M
			if ( getTTProductEdition() >= 20 ) { //Corporate with document management
				$required_max_size = 25;         //Recommend 128M or more?
			}

			if ( $size == null || $size >= ( $required_max_size * 1000 * 1000 ) ) {
				return 0;
			}

			return 1;
		}
	}

	/**
	 * @return int
	 */
	function checkPHPMemoryLimit() {
		$size = $this->getPHPINISize( 'memory_limit' );
		//If changing the minimum memory limit, update Global.inc.php as well, because it always tries to force the memory limit to this value.
		if ( $size == null || $size >= ( 512 * 1000 * 1000 ) ) { //512Mbytes - Use * 1000 rather than * 1024 so its easier to determine the limit in Global.inc.php and increase it.
			return 0;
		}

		return 1;
	}

	/**
	 * @return string
	 */
	function getCurrentTimeTrexVersion() {
		return APPLICATION_VERSION;
	}

	/**
	 * @return bool
	 */
	function getLatestTimeTrexVersion() {
		if ( $this->checkSOAP() == 0 ) {
			$ttsc = new TimeTrexSoapClient();

			return $ttsc->getSoapObject()->getInstallerLatestVersion();
		}

		return false;
	}

	/**
	 * @return int
	 */
	function checkTimeTrexVersion() {
		$current_version = $this->getCurrentTimeTrexVersion();
		$latest_version = $this->getLatestTimeTrexVersion();

		if ( $latest_version == false ) {
			return 1;
		} else if ( version_compare( $current_version, $latest_version, '>=' ) == true ) {
			return 0;
		}

		return 2;
	}

	/**
	 * @param bool $post_install_requirements_only
	 * @param bool $exclude_check
	 * @return int
	 */
	function checkAllRequirements( $post_install_requirements_only = false, $exclude_check = false ) {
		// Return
		//
		// 0 = OK
		// 1 = Invalid
		// 2 = Unsupported

		//Total up each OK, Invalid, and Unsupported requirements
		$retarr = [
				0 => 0,
				1 => 0,
				2 => 0,
		];

		//$retarr[1]++; //Test failed requirements.

		$retarr[$this->checkPHPVersion()]++;
		$retarr[$this->checkPHPINTSize()]++;
		$retarr[$this->checkDatabaseType()]++;
		//$retarr[$this->checkDatabaseVersion()]++; //Requires DB connection, which we often won't have.
		$retarr[$this->checkSOAP()]++;
		$retarr[$this->checkBCMATH()]++;
		$retarr[$this->checkMBSTRING()]++;
		//$retarr[$this->checkCALENDAR()]++;
		$retarr[$this->checkGETTEXT()]++;
		$retarr[$this->checkINTL()]++;
		$retarr[$this->checkGD()]++;
		$retarr[$this->checkJSON()]++;
		$retarr[$this->checkSimpleXML()]++;
		$retarr[$this->checkCURL()]++;
		$retarr[$this->checkZIP()]++;
		$retarr[$this->checkMAIL()]++;
		$retarr[$this->checkOpenSSL()]++;

		$retarr[$this->checkPEAR()]++;

		//PEAR modules are bundled as of v1.2.0
		if ( $post_install_requirements_only == false ) {
			if ( !is_array( $exclude_check ) || ( is_array( $exclude_check ) && in_array( 'disk_space', $exclude_check ) == false ) ) {
				$retarr[$this->checkDiskSpace()]++;
			}
			if ( !is_array( $exclude_check ) || ( is_array( $exclude_check ) && in_array( 'base_url', $exclude_check ) == false ) ) {
				$retarr[$this->checkBaseURL()]++;
			}
			if ( !is_array( $exclude_check ) || ( is_array( $exclude_check ) && in_array( 'php_cli', $exclude_check ) == false ) ) {
				$retarr[$this->checkPHPCLIBinary()]++;
				$retarr[$this->checkPHPOpenBaseDir()]++;
			}
			if ( !is_array( $exclude_check ) || ( is_array( $exclude_check ) && in_array( 'php_cli_requirements', $exclude_check ) == false ) ) {
				$retarr[$this->checkPHPCLIRequirements()]++;
			}
			$retarr[$this->checkWritableConfigFile()]++;
			$retarr[$this->checkWritableCacheDirectory()]++;
			$retarr[$this->checkSafeCacheDirectory()]++;
			if ( !is_array( $exclude_check ) || ( is_array( $exclude_check ) && in_array( 'clean_cache', $exclude_check ) == false ) ) {
				$retarr[$this->checkCleanCacheDirectory()]++;
			}
			$retarr[$this->checkWritableStorageDirectory()]++;
			$retarr[$this->checkSafeStorageDirectory()]++;
			$retarr[$this->checkWritableLogDirectory()]++;
			$retarr[$this->checkSafeLogDirectory()]++;
			if ( !is_array( $exclude_check ) || ( is_array( $exclude_check ) && in_array( 'file_permissions', $exclude_check ) == false ) ) {
				$retarr[$this->checkFilePermissions()]++;
			}
			if ( !is_array( $exclude_check ) || ( is_array( $exclude_check ) && in_array( 'file_checksums', $exclude_check ) == false ) ) {
				$retarr[$this->checkFileChecksums()]++;
			}
			$retarr[$this->checkSystemTimeZone()]++;
		}

		$retarr[$this->checkPHPSafeMode()]++;
		$retarr[$this->checkPHPDisabledFunctions()]++;
		$retarr[$this->checkPHPAllowURLFopen()]++;
		$retarr[$this->checkPHPMemoryLimit()]++;
		$retarr[$this->checkPHPMaxPostSize()]++;
		$retarr[$this->checkPHPMaxUploadSize()]++;

		if ( $this->getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			//$retarr[$this->checkPEARValidate()]++;
		}

		//Debug::Arr($retarr, 'RetArr: ', __FILE__, __LINE__, __METHOD__, 9);

		if ( $retarr[1] > 0 ) {
			return 1;
		} else if ( $retarr[2] > 0 ) {
			return 2;
		} else {
			return 0;
		}
	}

	/**
	 * @param bool $post_install_requirements_only
	 * @param bool $exclude_check
	 * @return array|bool
	 */
	function getFailedRequirements( $post_install_requirements_only = false, $exclude_check = false ) {
		$fail_all = false;

		$retarr = [];
		$retarr[] = 'Require';

		if ( $fail_all == true || $this->checkPHPVersion() != 0 ) {
			$retarr[] = 'PHPVersion';
		}

		if ( $fail_all == true || $this->checkPHPINTSize() != 0 ) {
			$retarr[] = 'PHPINT';
		}

		if ( $fail_all == true || $this->checkDatabaseType() != 0 ) {
			$retarr[] = 'DatabaseType';
		}

		//Requires DB connection, which we often won't have.
		//if ( $fail_all == TRUE OR $this->checkDatabaseVersion() != 0 ) {
		//	$retarr[] = 'DatabaseVersion';
		//}

		if ( $fail_all == true || $this->checkSOAP() != 0 ) {
			$retarr[] = 'SOAP';
		}

		if ( $fail_all == true || $this->checkBCMATH() != 0 ) {
			$retarr[] = 'BCMATH';
		}

		if ( $fail_all == true || $this->checkMBSTRING() != 0 ) {
			$retarr[] = 'MBSTRING';
		}

		//if ( $fail_all == TRUE OR $this->checkCALENDAR() != 0 ) {
		//	$retarr[] = 'CALENDAR';
		//}

		if ( $fail_all == true || $this->checkGETTEXT() != 0 ) {
			$retarr[] = 'GETTEXT';
		}

		if ( $fail_all == true || $this->checkINTL() != 0 ) {
			$retarr[] = 'INTL';
		}

		if ( $fail_all == true || $this->checkGD() != 0 ) {
			$retarr[] = 'GD';
		}

		if ( $fail_all == true || $this->checkJSON() != 0 ) {
			$retarr[] = 'JSON';
		}

		if ( $fail_all == true || $this->checkSimpleXML() != 0 ) {
			$retarr[] = 'SIMPLEXML';
		}

		if ( $fail_all == true || $this->checkCURL() != 0 ) {
			$retarr[] = 'CURL';
		}

		if ( $fail_all == true || $this->checkZIP() != 0 ) {
			$retarr[] = 'ZIP';
		}

		if ( $fail_all == true || $this->checkMAIL() != 0 ) {
			$retarr[] = 'MAIL';
		}

		if ( $fail_all == true || $this->checkOpenSSL() != 0 ) {
			$retarr[] = 'OPENSSL';
		}


		//Bundled PEAR modules require the base PEAR package at least
		if ( $fail_all == true || $this->checkPEAR() != 0 ) {
			$retarr[] = 'PEAR';
		}

		if ( $post_install_requirements_only == false ) {
			if ( is_array( $exclude_check ) && in_array( 'disk_space', $exclude_check ) == false ) {
				if ( $fail_all == true || $this->checkDiskSpace() != 0 ) {
					$retarr[] = 'DiskSpace';
				}
			}
			if ( is_array( $exclude_check ) && in_array( 'base_url', $exclude_check ) == false ) {
				if ( $fail_all == true || $this->checkBaseURL() != 0 ) {
					$retarr[] = 'BaseURL';
				}
			}
			if ( is_array( $exclude_check ) && in_array( 'php_cli', $exclude_check ) == false ) {
				if ( $fail_all == true || $this->checkPHPCLIBinary() != 0 ) {
					$retarr[] = 'PHPCLI';
				}
				if ( $fail_all == true || $this->checkPHPOpenBaseDir() != 0 ) {
					$retarr[] = 'PHPOpenBaseDir';
				}
			}
			if ( is_array( $exclude_check ) && in_array( 'php_cli_requirements', $exclude_check ) == false ) {
				if ( $fail_all == true || $this->checkPHPCLIRequirements() != 0 ) {
					$retarr[] = 'PHPCLIReq';
				}
			}

			if ( $fail_all == true || $this->checkWritableConfigFile() != 0 ) {
				$retarr[] = 'WConfigFile';
			}
			if ( $fail_all == true || $this->checkWritableCacheDirectory() != 0 ) {
				$retarr[] = 'WCacheDir';
			}
			if ( $fail_all == true || $this->checkSafeCacheDirectory() != 0 ) {
				$retarr[] = 'UnSafeCacheDir';
			}
			if ( is_array( $exclude_check ) && in_array( 'clean_cache', $exclude_check ) == false ) {
				if ( $fail_all == true || $this->checkCleanCacheDirectory() != 0 ) {
					$retarr[] = 'CleanCacheDir';
				}
			}
			if ( $fail_all == true || $this->checkWritableStorageDirectory() != 0 ) {
				$retarr[] = 'WStorageDir';
			}
			if ( $fail_all == true || $this->checkSafeStorageDirectory() != 0 ) {
				$retarr[] = 'UnSafeStorageDir';
			}
			if ( $fail_all == true || $this->checkWritableLogDirectory() != 0 ) {
				$retarr[] = 'WLogDir';
			}
			if ( $fail_all == true || $this->checkSafeLogDirectory() != 0 ) {
				$retarr[] = 'UnSafeLogDir';
			}
			if ( is_array( $exclude_check ) && in_array( 'file_permissions', $exclude_check ) == false ) {
				if ( $fail_all == true || $this->checkFilePermissions() != 0 ) {
					$retarr[] = 'WFilePermissions';
				}
			}
			if ( is_array( $exclude_check ) && in_array( 'file_checksums', $exclude_check ) == false ) {
				if ( $fail_all == true || $this->checkFileChecksums() != 0 ) {
					$retarr[] = 'WFileChecksums';
				}
			}
			if ( $fail_all == true || $this->checkSystemTimeZone() != 0 ) {
				$retarr[] = 'SystemTimeZone';
			}
		}

		if ( $fail_all == true || $this->checkPHPSafeMode() != 0 ) {
			$retarr[] = 'PHPSafeMode';
		}
		if ( $fail_all == true || $this->checkPHPDisabledFunctions() != 0 ) {
			$retarr[] = 'PHPDisabledFunctions';
		}
		if ( $fail_all == true || $this->checkPHPAllowURLFopen() != 0 ) {
			$retarr[] = 'PHPAllowURLFopen';
		}
		if ( $fail_all == true || $this->checkPHPMemoryLimit() != 0 ) {
			$retarr[] = 'PHPMemoryLimit';
		}
		if ( $fail_all == true || $this->checkPHPMaxPostSize() != 0 ) {
			$retarr[] = 'PHPPostSize';
		}
		if ( $fail_all == true || $this->checkPHPMaxUploadSize() != 0 ) {
			$retarr[] = 'PHPUploadSize';
		}

		if ( $fail_all == true || $this->getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			//if ( $fail_all == true || $this->checkPEARValidate() != 0 ) {
			//	$retarr[] = 'PEARVal';
			//}
		}

		if ( isset( $retarr ) ) {
			return $retarr;
		}

		return false;
	}

	/**
	 * Used by InstallSchema_1100*
	 * @param $matches
	 * @return bool|int|string
	 */
	function regexConvertToUUIDNoHash( $matches ) {
		return $this->regexConvertToUUID( $matches, false );
	}

	/**
	 * @param $matches
	 * @param bool $include_hash
	 * @return bool|int|string
	 */
	function regexConvertToUUID( $matches, $include_hash = true ) {
		$id = '';
		if ( isset( $matches[3] ) ) {
			if ( $include_hash == true ) {
				$id = '#';
			}
			$id .= $matches[1] . ':' . TTUUID::convertIntToUUID( $matches[3] );
			if ( isset( $matches[4] ) ) {
				$id .= $matches[4];
			}
			if ( $include_hash == true ) {
				$id .= '#';
			}
		} else {
			$id = $matches[0];
		}

		return $id;
	}

	/**
	 * Used by InstallSchema_1100*
	 * takes a listfactory result set as first argument.
	 * @param $array
	 * @return array
	 */
	function convertArrayElementsToUUID( $array ) {
		if ( !is_array( $array ) ) {
			return $array;
		}

		$recombined_array = [];
		foreach ( $array as $key => $item ) {
			if ( is_numeric( $item ) ) {
				$recombined_array[$key] = TTUUID::convertIntToUUID( $item );
			} else if ( is_array( $item ) ) {
				$recombined_array[$key] = $this->convertArrayElementsToUUID( $item );
			} else {
				$recombined_array[$key] = $item;
			}
		}

		return $recombined_array;
	}

	/**
	 * @param $columns_data
	 * @return array
	 */
	function processColumns( $columns_data ) {
		$retval = [];
		if ( is_array( $columns_data ) ) {
			foreach ( $columns_data as $key => $value ) {
				$pattern = [ '/^(\w+)(\-)([0-9]{1,10})(_\w+|)$/', '/^(PA|PR|PU|PY)()(\d+)$/', '/^(custom_column)()(\d+)$/' ];

				$new_key = preg_replace_callback( $pattern, [ $this, 'regexConvertToUUIDNoHash' ], trim( $key ) );
				$new_value = preg_replace_callback( $pattern, [ $this, 'regexConvertToUUIDNoHash' ], $value );
				if ( $new_key !== false && $new_value !== false ) {
					$retval[$new_key] = $new_value;
				} else if ( $new_key !== false && $new_value == false ) {
					$retval[$key] = $value;
				} else if ( $new_key == false && $new_value !== false ) {
					$retval[$key] = $new_value;
				} else {
					$retval[$key] = $value;
				}
			}
		}

		return $retval;
	}
}

?>
