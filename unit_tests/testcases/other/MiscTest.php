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

class MiscTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10);

		TTi18n::setLocale( 'en_US', LC_ALL, TRUE ); //This fixes problems with NumberFormat when the locale is changed and not changed back.

		return TRUE;
	}

	public function tearDown() {
		Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10);
		return TRUE;
	}

	/**
	 * @group testEncryptionA
	 */
	function testEncryptionA() {
		//Make sure we force the salt so its consistent even when the timetrex.ini.php is not.
		global $config_vars;
		$config_vars['other']['salt'] = 'f0328b0863222ff98b848537fe1038b2';

		$str = 'This is a sample string to be encrypted and decrytped.';
		$encrypted_str = Misc::encrypt( $str );
		$decrypted_str = Misc::decrypt( $encrypted_str );
		$this->assertEquals( $str, $decrypted_str );

		if ( version_compare( PHP_VERSION, '7.1', '<=' ) ) { //PHP v7.2+ no longer has MCRYPT extension.
			//can we still decrypt version 1?
			$mastercard_unencrypted = 5454545454545454;
			$mastercard_old_encrypted = 'oqp5HtmFgYCiqnke2YWBgA==';
			$decrypted_str = Misc::decrypt( $mastercard_old_encrypted );
			$this->assertEquals( $mastercard_unencrypted, $decrypted_str );

			$visa_unencrypted = 4111111111111111;
			$visa_old_encrypted = '4G30xI80TEZf8RFMRPE56w==';

			//testing upgrading encryption from version1 to version2
			$decrypted_str = Misc::decrypt( $visa_old_encrypted );

			//does v1 decryption work?
			$this->assertEquals( $visa_unencrypted, $decrypted_str );

			//does v1 upgrade cleanly to v2 encrypt?
			$new_encrypted_value = Misc::encrypt( $decrypted_str );
			$decrypted_str = Misc::decrypt( $new_encrypted_value );
			$this->assertEquals( $visa_unencrypted, $decrypted_str );

			//decrypt unencrypted data
			$this->assertEquals( $visa_unencrypted,  Misc::decrypt( $visa_unencrypted ) );
		}

		//check the case for the colon.
		$x = 'x:z';
		$this->assertEquals( $x,  Misc::decrypt( $x ) );

	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerA
	 */
	function testDatabaseLoadBalancerA() {
		global $config_vars;

		require_once( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'adodb'. DIRECTORY_SEPARATOR .'adodb-loadbalancer.inc.php');


		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = array(
						  array( $host, 'master', 100 ),
						  array( $host, 'master', 100 ),
						);

		foreach( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1);
			//if ( $db_host_arr[2] == 100 ) {
			//	$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'].'9', $config_vars['database']['password'], $config_vars['database']['database_name'] );
			//} else {
				$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			//}
			//Debug::Arr( $db_connection_obj,  'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1);
			$db_connection_obj->getADODbObject()->SetFetchMode(ADODB_FETCH_ASSOC);
			$db_connection_obj->getADODbObject()->noBlobs = TRUE; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset($type, $db_connection_obj);

		$retarr = array();
		$max = 1000;
		for( $i = 0; $i < $max; $i++ ) {
			$connection_id = $db->getConnectionByWeight( 'master' );
			if ( !isset($retarr[$connection_id]) ) {
				$retarr[$connection_id] = 0;
			}
			$retarr[$connection_id]++;
		}
		//var_dump($retarr);

		$this->assertGreaterThan( 400, $retarr[0] );
		$this->assertLessThan( 600, $retarr[0] );

		$this->assertGreaterThan( 400, $retarr[1] );
		$this->assertLessThan( 600, $retarr[1] );
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerB
	 */
	function testDatabaseLoadBalancerB() {
		global $config_vars;

		require_once( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'adodb'. DIRECTORY_SEPARATOR .'adodb-loadbalancer.inc.php');

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = array(
						  array( $host, 'master', 100 ),
						  array( $host, 'master', 200 ),
						);

		foreach( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1);
			//if ( $db_host_arr[2] == 100 ) {
			//	$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'].'9', $config_vars['database']['password'], $config_vars['database']['database_name'] );
			//} else {
				$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			//}
			//Debug::Arr( $db_connection_obj,  'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1);
			$db_connection_obj->getADODbObject()->SetFetchMode(ADODB_FETCH_ASSOC);
			$db_connection_obj->getADODbObject()->noBlobs = TRUE; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset($type, $db_connection_obj);

		$retarr = array();
		$max = 1000;
		for( $i = 0; $i < $max; $i++ ) {
			$connection_id = $db->getConnectionByWeight( 'master' );
			if ( !isset($retarr[$connection_id]) ) {
				$retarr[$connection_id] = 0;
			}
			$retarr[$connection_id]++;
		}
		//var_dump($retarr);

		$this->assertGreaterThan( 200, $retarr[0] );
		$this->assertLessThan( 450, $retarr[0] );

		$this->assertGreaterThan( 450, $retarr[1] );
		$this->assertLessThan( 800, $retarr[1] );
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerC
	 */
	function testDatabaseLoadBalancerC() {
		global $config_vars;

		require_once( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'adodb'. DIRECTORY_SEPARATOR .'adodb-loadbalancer.inc.php');

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = array(
						  array( $host, 'master', 0 ),
						  array( $host, 'master', 100 ),
						);

		foreach( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1);
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode(ADODB_FETCH_ASSOC);
			$db_connection_obj->getADODbObject()->noBlobs = TRUE; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset($type, $db_connection_obj);

		$retarr = array( 0 => 0, 1 => 0 );
		$max = 1000;
		for( $i = 0; $i < $max; $i++ ) {
			$connection_id = $db->getConnectionByWeight( 'master' );
			if ( !isset($retarr[$connection_id]) ) {
				$retarr[$connection_id] = 0;
			}
			$retarr[$connection_id]++;
		}
		//var_dump($retarr);
		$diff = abs( $retarr[0] - $retarr[1] );

		$this->assertEquals( 1000, $diff );
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerD
	 */
	function testDatabaseLoadBalancerD() {
		global $config_vars;

		require_once( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'adodb'. DIRECTORY_SEPARATOR .'adodb-loadbalancer.inc.php');

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = array(
						  array( $host, 'master', 0 ),
						  array( $host, 'slave', 100 ),
						);

		foreach( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1);
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode(ADODB_FETCH_ASSOC);
			$db_connection_obj->getADODbObject()->noBlobs = TRUE; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset($type, $db_connection_obj);

		$retarr = array( 0 => 0, 1 => 0 );
		$max = 1000;
		for( $i = 0; $i < $max; $i++ ) {
			//$connection_id = $db->getConnectionByWeight( 'master' );
			$connection_id = $db->getLoadBalancedConnection( 'master' );
			if ( !isset($retarr[$connection_id]) ) {
				$retarr[$connection_id] = 0;
			}
			$retarr[$connection_id]++;
		}
		//var_dump($retarr);
		$diff = abs( $retarr[0] - $retarr[1] );

		$this->assertEquals( 1000, $diff );
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerE
	 */
	function testDatabaseLoadBalancerE() {
		global $config_vars;

		require_once( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'adodb'. DIRECTORY_SEPARATOR .'adodb-loadbalancer.inc.php');

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = array(
						  array( $host, 'master', 10 ),
						  array( $host, 'slave', 100 ),
						  array( $host, 'slave', 200 ),
						);

		foreach( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1);
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode(ADODB_FETCH_ASSOC);
			$db_connection_obj->getADODbObject()->noBlobs = TRUE; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset($type, $db_connection_obj);

		$retarr = array( 0 => 0, 1 => 0, 2 => 0 );
		$max = 1000;
		for( $i = 0; $i < $max; $i++ ) {
			$connection_id = $db->getConnectionByWeight( 'slave' );
			//$connection_id = $db->getLoadBalancedConnection( 'master' );
			if ( !isset($retarr[$connection_id]) ) {
				$retarr[$connection_id] = 0;
			}
			$retarr[$connection_id]++;
		}
		//var_dump($retarr);

		$this->assertGreaterThan( 1, $retarr[0] );
		$this->assertLessThan( 100, $retarr[0] );

		$this->assertGreaterThan( 250, $retarr[1] );
		$this->assertLessThan( 400, $retarr[1] );

		$this->assertGreaterThan( 550, $retarr[2] );
		$this->assertLessThan( 750, $retarr[2] );
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerF
	 */
	function testDatabaseLoadBalancerF() {
		global $config_vars;

		require_once( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'adodb'. DIRECTORY_SEPARATOR .'adodb-loadbalancer.inc.php');

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = array(
						  array( $host, 'master', 10 ),
						  array( $host, 'slave', 100 ),
						  array( $host, 'slave', 200 ),
						);

		foreach( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1);
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode(ADODB_FETCH_ASSOC);
			$db_connection_obj->getADODbObject()->noBlobs = TRUE; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset($type, $db_connection_obj);

		$db->removeConnection(1); //Remove first slave to test failover.

		$retarr = array( 0 => 0, 1 => 0, 2 => 0 );
		$max = 1000;
		for( $i = 0; $i < $max; $i++ ) {
			//$connection_id = $db->getConnectionByWeight( 'slave' );
			$connection_id = $db->getLoadBalancedConnection( 'slave' );
			if ( !isset($retarr[$connection_id]) ) {
				$retarr[$connection_id] = 0;
			}
			$retarr[$connection_id]++;
		}
		//var_dump($retarr);

		$this->assertGreaterThan( 1, $retarr[0] );
		$this->assertLessThan( 100, $retarr[0] );

		$this->assertEquals( 0, $retarr[1] );

		$this->assertGreaterThan( 800, $retarr[2] );
		$this->assertLessThan( 1000, $retarr[2] );
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerG
	 */
	function testDatabaseLoadBalancerG() {
		global $config_vars;

		require_once( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'adodb'. DIRECTORY_SEPARATOR .'adodb-loadbalancer.inc.php');

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = array(
						  array( $host, 'master', 100 ),
						  array( $host, 'slave', 100 ),
						  array( $host, 'slave', 100 ),
						);

		foreach( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1);
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode(ADODB_FETCH_ASSOC);
			$db_connection_obj->getADODbObject()->noBlobs = TRUE; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset($type, $db_connection_obj);

		$retarr = array( 0 => 0, 1 => 0, 2 => 0 );
		$max = 100;
		for( $i = 0; $i < $max; $i++ ) {
			$db->Execute('SELECT 1');
			//$connection_id = $db->getConnectionByWeight( 'slave' );
			$connection_id = $db->getLoadBalancedConnection( 'slave' );
			if ( !isset($retarr[$connection_id]) ) {
				$retarr[$connection_id] = 0;
			}
			$retarr[$connection_id]++;
		}
		//var_dump($retarr);

		if ( $retarr[0] > 0 ) {
			$this->assertEquals( 100, $retarr[0] );
			$this->assertEquals( 0, $retarr[1] );
			$this->assertEquals( 0, $retarr[2] );
		} elseif ( $retarr[1] > 0 ) {
			$this->assertEquals( 0, $retarr[0] );
			$this->assertEquals( 100, $retarr[1] );
			$this->assertEquals( 0, $retarr[2] );
		} elseif ( $retarr[2] > 0 ) {
			$this->assertEquals( 0, $retarr[0] );
			$this->assertEquals( 0, $retarr[1] );
			$this->assertEquals( 100, $retarr[2] );
		}
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerH
	 */
	function testDatabaseLoadBalancerH() {
		global $config_vars;

		require_once( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'adodb'. DIRECTORY_SEPARATOR .'adodb-loadbalancer.inc.php');

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = array(
						  array( $host, 'master', 0 ),
						  array( $host, 'slave', 100 ),
						  array( $host, 'slave', 100 ),
						);

		foreach( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1);
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode(ADODB_FETCH_ASSOC);
			$db_connection_obj->getADODbObject()->noBlobs = TRUE; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset($type, $db_connection_obj);

		$retarr = array( 0 => 0, 1 => 0, 2 => 0 );
		$max = 100;
		for( $i = 0; $i < $max; $i++ ) {
			//Test going in/out of transactions to make sure they are pinned to the master properly.
			if ( $i == 10 OR $i == 80 ) {
				$db->BeginTrans();
			}
			$db->Execute('SELECT 1');

			if ( $i == 20 OR $i == 90 ) {
				$db->CommitTrans();
			}

			$connection_id = $db->getLoadBalancedConnection( 'slave' );
			if ( !isset($retarr[$connection_id]) ) {
				$retarr[$connection_id] = 0;
			}
			$retarr[$connection_id]++;
		}
		//var_dump($retarr);

		$this->assertEquals( 20, $retarr[0] ); //20 transaction in total pinned to master.

		$this->assertGreaterThanOrEqual( 0, $retarr[1] );
		$this->assertLessThanOrEqual( 100, $retarr[1] );

		$this->assertGreaterThanOrEqual( 0, $retarr[2] );
		$this->assertLessThanOrEqual( 100, $retarr[2] );
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerI
	 */
	function testDatabaseLoadBalancerI() {
		global $config_vars;

		require_once( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'adodb'. DIRECTORY_SEPARATOR .'adodb-loadbalancer.inc.php');

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = array(
						  array( $host, 'master', 0 ),
						  array( $host, 'slave', 100 ),
						  array( $host, 'slave', 100 ),
						);

		foreach( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1);
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode(ADODB_FETCH_ASSOC);
			$db_connection_obj->getADODbObject()->noBlobs = TRUE; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset($type, $db_connection_obj);

		$retarr = array( 0 => 0, 1 => 0, 2 => 0 );
		$max = 100;
		for( $i = 0; $i < $max; $i++ ) {
			//Test going in/out of *nested* transactions to make sure they are pinned to the master properly.
			if ( $i == 10 OR $i == 15 ) {
				$db->StartTrans();
			}
			$db->Execute('SELECT 1');

			if ( $i == 20 OR $i == 25 ) {
				$db->CompleteTrans();
			}

			$connection_id = $db->getLoadBalancedConnection( 'slave' );
			if ( !isset($retarr[$connection_id]) ) {
				$retarr[$connection_id] = 0;
			}
			$retarr[$connection_id]++;
		}
		//var_dump($retarr);

		$this->assertEquals( 15, $retarr[0] ); //15 transaction in total pinned to master.

		$this->assertGreaterThanOrEqual( 0, $retarr[1] );
		$this->assertLessThanOrEqual( 100, $retarr[1] );

		$this->assertGreaterThanOrEqual( 0, $retarr[2] );
		$this->assertLessThanOrEqual( 100, $retarr[2] );
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerSessionVarsA
	 */
	function testDatabaseLoadBalancerSessionVarsA() {
		global $config_vars;

		require_once( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'adodb'. DIRECTORY_SEPARATOR .'adodb-loadbalancer.inc.php');

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = array(
						  array( $host, 'master', 100 ),
						  array( $host, 'slave', 100 ),
						  array( $host, 'slave', 100 ),
						);

		foreach( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1);
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode(ADODB_FETCH_ASSOC);
			$db_connection_obj->getADODbObject()->noBlobs = TRUE; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset($type, $db_connection_obj);

		if ( strncmp($db->databaseType, 'postgres', 8) == 0 ) {
			$db->_getConnection(0);
			$db->_getConnection(1);

			$time_zone = 'EST5EDT';

			//SET calls should be intercepted and run on the entire cluster automatically.
			$db->Execute('SET SESSION TIME ZONE '. $time_zone );
			$results = $db->ClusterExecute( 'SHOW TIME ZONE', FALSE, TRUE, TRUE ); //Only existing connections.
			//var_dump($result);
			$this->assertEquals( 2, count($results) ); //Only two connections established so far.
			foreach( $results as $key => $rs ) {
				Debug::Text( 'Testing result from connection: '. $key .' Result: '. $rs->fields['TimeZone'], __FILE__, __LINE__, __METHOD__, 1);
				if ( !$rs ) {
					Debug::Text( 'Testing result from connection: '. $key, __FILE__, __LINE__, __METHOD__, 1);
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'], 'Query failed!' );
				} else {
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'] );
				}
			}

			//
			//Test cluster wide execution.
			//

			$results = $db->ClusterExecute( 'SET SESSION TIME ZONE '. $db->qstr($time_zone), FALSE, TRUE, FALSE );
			$results = $db->ClusterExecute( 'SHOW TIME ZONE', FALSE, TRUE, FALSE );

			//var_dump($result);
			$this->assertEquals( 3, count($results) );
			foreach( $results as $key => $rs ) {
				Debug::Text( 'Testing result from connection: '. $key .' Result: '. $rs->fields['TimeZone'], __FILE__, __LINE__, __METHOD__, 1);
				if ( !$rs ) {
					Debug::Text( 'Testing result from connection: '. $key, __FILE__, __LINE__, __METHOD__, 1);
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'], 'Query failed!' );
				} else {
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'] );
				}
			}

		}
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerSessionVarsB
	 */
	function testDatabaseLoadBalancerSessionVarsB() {
		global $config_vars;

		require_once( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'adodb'. DIRECTORY_SEPARATOR .'adodb-loadbalancer.inc.php');

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = array(
						  array( $host, 'master', 100 ),
						  array( $host, 'slave', 100 ),
						  array( $host, 'slave', 100 ),
						);

		foreach( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1);
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode(ADODB_FETCH_ASSOC);
			$db_connection_obj->getADODbObject()->noBlobs = TRUE; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset($type, $db_connection_obj);

		if ( strncmp($db->databaseType, 'postgres', 8) == 0 ) {
			$time_zone = 'EST5EDT';
			$db->setSessionVariable( 'TIME ZONE', $time_zone );

			$db->_getConnection(0);
			$db->_getConnection(1);

			$results = $db->ClusterExecute( 'SHOW TIME ZONE', FALSE, TRUE, TRUE ); //Only existing connections.
			//var_dump($result);
			$this->assertEquals( 2, count($results) ); //Only two connections established so far.
			foreach( $results as $key => $rs ) {
				Debug::Text( 'Testing result from connection: '. $key .' Result: '. $rs->fields['TimeZone'], __FILE__, __LINE__, __METHOD__, 1);
				if ( !$rs ) {
					Debug::Text( 'Testing result from connection: '. $key, __FILE__, __LINE__, __METHOD__, 1);
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'], 'Query failed!' );
				} else {
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'] );
				}
			}

			$db->_getConnection(2);

			//
			//Test cluster wide execution.
			//

			$results = $db->ClusterExecute( 'SHOW TIME ZONE', FALSE, TRUE, FALSE );

			//var_dump($result);
			$this->assertEquals( 3, count($results) );
			foreach( $results as $key => $rs ) {
				Debug::Text( 'Testing result from connection: '. $key .' Result: '. $rs->fields['TimeZone'], __FILE__, __LINE__, __METHOD__, 1);
				if ( !$rs ) {
					Debug::Text( 'Testing result from connection: '. $key, __FILE__, __LINE__, __METHOD__, 1);
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'], 'Query failed!' );
				} else {
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'] );
				}
			}

			//Change timezone, make sure it happens across all connections.

			$time_zone = 'CST6CDT';
			$db->setSessionVariable( 'TIME ZONE', $time_zone );
			//
			//Test cluster wide execution.
			//

			$results = $db->ClusterExecute( 'SHOW TIME ZONE', FALSE, TRUE, FALSE );

			//var_dump($result);
			$this->assertEquals( 3, count($results) );
			foreach( $results as $key => $rs ) {
				Debug::Text( 'Testing result from connection: '. $key .' Result: '. $rs->fields['TimeZone'], __FILE__, __LINE__, __METHOD__, 1);
				if ( !$rs ) {
					Debug::Text( 'Testing result from connection: '. $key, __FILE__, __LINE__, __METHOD__, 1);
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'], 'Query failed!' );
				} else {
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'] );
				}
			}

		}
	}

	/**
	 * @group MiscTest_testBeforeAndAfterDecimal
	 */
	function testBeforeAndAfterDecimal() {
		$this->assertEquals( Misc::getBeforeDecimal( 0 ), '0' );
		$this->assertEquals( Misc::getBeforeDecimal( 1 ), '1' );
		$this->assertEquals( Misc::getBeforeDecimal( 53 ), '53' );
		$this->assertEquals( Misc::getBeforeDecimal( -53 ), '-53' );
		$this->assertEquals( Misc::getBeforeDecimal( 3.14 ), '3' );
		$this->assertEquals( Misc::getBeforeDecimal( -3.14 ), '-3' );
		$this->assertEquals( Misc::getBeforeDecimal( -3.1 ), '-3' );
		$this->assertEquals( Misc::getBeforeDecimal( -123456789.123456789 ), '-123456789' );
		$this->assertEquals( Misc::getBeforeDecimal( 123456789.123456789 ), '123456789' );

		$this->assertEquals( Misc::getAfterDecimal( 0 ), '0' );
		$this->assertEquals( Misc::getAfterDecimal( 1 ), '0' );
		$this->assertEquals( Misc::getAfterDecimal( 3 ), '0' );
		$this->assertEquals( Misc::getAfterDecimal( -3 ), '0' );
		$this->assertEquals( Misc::getAfterDecimal( -3.1, TRUE ), '10' );
		$this->assertEquals( Misc::getAfterDecimal( -3.1, FALSE ), '1' );
		$this->assertEquals( Misc::getAfterDecimal( 3.14 ), '14' );
		$this->assertEquals( Misc::getAfterDecimal( -3.14 ), '14' );
		$this->assertEquals( Misc::getAfterDecimal( -123456789.123456789, TRUE ), '12' );

		$this->assertEquals( Misc::getAfterDecimal( -123456789.123456789, FALSE ), '12346' );//float precision overflow
		$this->assertEquals( Misc::getAfterDecimal( '123456789.123456789', FALSE ), '123456789' );
	}

	/**
	 * @group MiscTest_testFormatNumber
	 */
	function testFormatNumber() {
		$this->assertSame( TTi18n::FormatNumber( '100.00', TRUE ), '100.00' );
		$this->assertSame( TTi18n::FormatNumber( '100', TRUE ), '100.00' );
		$this->assertSame( TTi18n::FormatNumber( '100.01000', TRUE ), '100.01' );
		$this->assertSame( TTi18n::FormatNumber( '100.0101', TRUE ), '100.0101' );

		$this->assertSame( TTi18n::FormatNumber( '100.0100', TRUE, 1, 2 ), '100.01' );
		$this->assertSame( TTi18n::FormatNumber( '100.0123', TRUE, 1, 2 ), '100.01' );
		$this->assertSame( TTi18n::FormatNumber( '100.1', TRUE, 1, 2 ), '100.1' );
		$this->assertSame( TTi18n::FormatNumber( '100', TRUE, 1, 2 ), '100.0' );

		$this->assertSame( TTi18n::FormatNumber( '100.1000', FALSE, 1, 2 ), '100.1' );
		$this->assertSame( TTi18n::FormatNumber( '100.1234', FALSE, 1, 2 ), '100.12' );
		$this->assertSame( TTi18n::FormatNumber( '100', FALSE, 1, 2 ), '100' );
		$this->assertSame( TTi18n::FormatNumber( '100', FALSE ), '100' );
		$this->assertSame( TTi18n::FormatNumber( '100.12345', FALSE ), '100.12' );

		$this->assertSame( TTi18n::FormatNumber( '100.0000', TRUE, 0, 2 ), '100' );
		$this->assertSame( TTi18n::FormatNumber( '100.0000', TRUE, 0, 2 ), '100' ); //Make sure we don't get "100."
	}

	/**
	 * @group MiscTest_testMoneyFormat
	 */
	function testMoneyFormat() {
		//see the I18nTest that compares this function to the numberformat in i18n.
		Debug::Text( 'Thousands Separator: '. TTi18n::getThousandsSymbol() .' Decimal Symbol: '. TTi18n::getDecimalSymbol(), __FILE__, __LINE__, __METHOD__, 1);
		if ( TTi18n::getThousandsSymbol() == ',' AND TTi18n::getDecimalSymbol() == '.' ) {
			$this->assertEquals( Misc::MoneyFormat( 12345.152, TRUE ), '12,345.15' );
			$this->assertEquals( Misc::MoneyFormat( 12345.151, FALSE ), '12345.15' );
			$this->assertEquals( Misc::MoneyFormat( 12345.15, TRUE ), '12,345.15' );
			$this->assertEquals( Misc::MoneyFormat( 12345.15, FALSE ), '12345.15' );
			$this->assertEquals( Misc::MoneyFormat( 12345.1, TRUE ), '12,345.10' );
			$this->assertEquals( Misc::MoneyFormat( 12345.5, FALSE ), '12345.50' );
			$this->assertEquals( Misc::MoneyFormat( 12345.12345 ), '12,345.12' );
			$this->assertEquals( Misc::MoneyFormat( -12345.12345 ), '-12,345.12' );
		} else {
			Debug::Text( 'ERROR: Locale differs, skipping unit tests...', __FILE__, __LINE__, __METHOD__, 1);
		}

		TTi18n::setLocale( 'es_ES' );
		Debug::Text( 'Thousands Separator: '. TTi18n::getThousandsSymbol() .' Decimal Symbol: '. TTi18n::getDecimalSymbol(), __FILE__, __LINE__, __METHOD__, 1);
		if ( TTi18n::getThousandsSymbol() == '.' AND TTi18n::getDecimalSymbol() == ',' ) {
			$this->assertEquals( Misc::MoneyFormat( 12345.12345 ), '12.345,12' );
			$this->assertEquals( Misc::MoneyFormat( -12345.12345 ), '-12.345,12' );
		} else {
			Debug::Text( 'ERROR: Locale differs, skipping unit tests...', __FILE__, __LINE__, __METHOD__, 1);
		}
	}

	/**
	 * @group MiscTest_testUnitConvert
	 */
	function testUnitConvert() {
		$this->assertEquals( UnitConvert::convert( 'mm', 'mm', 1 ), 1 );
		$this->assertEquals( UnitConvert::convert( 'm', 'mm', 1 ), 1000 );
		$this->assertEquals( UnitConvert::convert( 'mm', 'm', 1 ), 0.001 );

		$this->assertEquals( UnitConvert::convert( 'km', 'm', 1 ), 1000 );
		$this->assertEquals( UnitConvert::convert( 'm', 'km', 1 ), 0.001 );

		$this->assertEquals( UnitConvert::convert( 'mi', 'mm', 1 ), 1609344 );
		$this->assertEquals( UnitConvert::convert( 'mm', 'mi', 1 ), (1 / 1609344) );

		$this->assertEquals( UnitConvert::convert( 'km', 'mi', 1 ), 0.62137119223733395 );
		$this->assertEquals( UnitConvert::convert( 'mi', 'km', 1 ), 1.6093439999999999 );
		$this->assertEquals( UnitConvert::convert( 'm', 'mi', 1 ), 0.00062137119223733392 );
	}

	/**
	 * @group MiscTest_testPasswordStrength
	 */
	function testPasswordStrength() {
		//Numbers
		$this->assertEquals( TTPassword::getPasswordStrength('1'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('12'), 1 );

		$this->assertEquals( TTPassword::getPasswordStrength('123'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('1234'), 1 );

		$this->assertEquals( TTPassword::getPasswordStrength('12345'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('123456'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('1234567'), 1 );

		$this->assertEquals( TTPassword::getPasswordStrength('12345678'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('123456789'), 1 );

		$this->assertEquals( TTPassword::getPasswordStrength('1234567890'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('12345678901'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('123456789012'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('1234567890123'), 1 );

		$this->assertEquals( TTPassword::getPasswordStrength('12345678901234'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('123456789012345'), 1 );

		$this->assertEquals( TTPassword::getPasswordStrength('987654321'), 1 ); //Backwards

		//Letters
		$this->assertEquals( TTPassword::getPasswordStrength('a'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('ab'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('abc'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('abcd'), 1 );

		$this->assertEquals( TTPassword::getPasswordStrength('abcde'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('abcdef'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('abcdefg'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('abcdefgh'), 1 );

		$this->assertEquals( TTPassword::getPasswordStrength('abcdefghi'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('abcdefghij'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('abcdefghijk'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('abcdefghijkl'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('abcdefghijklm'), 1 );

		$this->assertEquals( TTPassword::getPasswordStrength('abcdefghijklmn'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('abcdefghijklmno'), 1 );

		$this->assertEquals( TTPassword::getPasswordStrength('ihgfedcba'), 1 ); //Backwards

		//Half letters, half numbers
		$this->assertEquals( TTPassword::getPasswordStrength('a1'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('ab12'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('abc123'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('abcd1234'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('abcde12345'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('abcdef123456'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('abcdefg1234567'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('abcdefgh12345678'), 1 );


		//All the same char.
		$this->assertEquals( TTPassword::getPasswordStrength('aaaaaa'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('aaabbb'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('aaaccc'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('111111'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('111222'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('111333'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('123123'), 1 );

		//Some what real passwords.
		$this->assertEquals( TTPassword::getPasswordStrength('test'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('pear'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('orange'), 1 );

		$this->assertEquals( TTPassword::getPasswordStrength('!Qa12'), 2 ); //Unique, but not enough characters to make it difficult.
		$this->assertEquals( TTPassword::getPasswordStrength('2000'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('696969'), 2 );
		$this->assertEquals( TTPassword::getPasswordStrength('trustno1'), 2 );

		$this->assertEquals( TTPassword::getPasswordStrength('abababababababab'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('abcabcabcabcabc'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('abcdabcdabcdabcd'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('abcd.abcd^abcd#abcd'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('abc123'), 1 );
		$this->assertEquals( TTPassword::getPasswordStrength('test123'), 2 );
		$this->assertEquals( TTPassword::getPasswordStrength('admin123'), 3 );
		$this->assertEquals( TTPassword::getPasswordStrength('pear123'), 3 );
		$this->assertEquals( TTPassword::getPasswordStrength('pear1234'), 3 );
		$this->assertEquals( TTPassword::getPasswordStrength('pear12345'), 3 );
		$this->assertEquals( TTPassword::getPasswordStrength('orange123456'), 4 );
		$this->assertEquals( TTPassword::getPasswordStrength('car123456789'), 1 ); //Too many consecutive.
		$this->assertEquals( TTPassword::getPasswordStrength('cars123456789'), 1 ); //Too many consecutive.
		$this->assertEquals( TTPassword::getPasswordStrength('orange123456789'), 1 ); //Too many consecutive.
		$this->assertEquals( TTPassword::getPasswordStrength('superabundant123456789'), 6 ); //Too many consecutive.

		$this->assertEquals( TTPassword::getPasswordStrength('cars.8.apple'), 4 );
		$this->assertEquals( TTPassword::getPasswordStrength('cars.8#apple'), 4 );

		$this->assertEquals( TTPassword::getPasswordStrength('password'), 1 ); //Dictionary word
		$this->assertEquals( TTPassword::getPasswordStrength('Password'), 1 ); //Dictionary word
		$this->assertEquals( TTPassword::getPasswordStrength('password1'), 1 ); //Dictionary word with one extra char.
		$this->assertEquals( TTPassword::getPasswordStrength('password11'), 3 );
		$this->assertEquals( TTPassword::getPasswordStrength('1password'), 1 ); //Dictionary word with one extra char.
		$this->assertEquals( TTPassword::getPasswordStrength('password!'), 1 ); //Dictionary word with one extra char.
		$this->assertEquals( TTPassword::getPasswordStrength('!password'), 1 ); //Dictionary word with one extra char.
		$this->assertEquals( TTPassword::getPasswordStrength('qwerty'), 1 ); //Dictionary word
		$this->assertEquals( TTPassword::getPasswordStrength('dragon'), 1 ); //Dictionary word

		$this->assertEquals( TTPassword::getPasswordStrength('superabundant'), 1 ); //Dictionary word
		$this->assertEquals( TTPassword::getPasswordStrength('Super.Abundant#41'), 5 ); //Dictionary word
		$this->assertEquals( TTPassword::getPasswordStrength('pearappleorange'), 3 );
		$this->assertEquals( TTPassword::getPasswordStrength('pear.apple@orange#strawberry'), 5 );
		$this->assertEquals( TTPassword::getPasswordStrength('superabundant123'), 4 );

		$this->assertEquals( TTPassword::getPasswordStrength('Superabundant.123'), 5 );
		$this->assertEquals( TTPassword::getPasswordStrength('Super^91Pear.87'), 5 );
		$this->assertEquals( TTPassword::getPasswordStrength('Super^91Bop.87'), 5 );

		$this->assertEquals( TTPassword::getPasswordStrength('a1j8U4y7K2qA.#@5.'), 7 );
	}

	/**
	 * @group MiscTest_testRandomPasswordStregth
	 */
	function testRandomPasswordStregth() {
		for ( $i = 0; $i < 10000; $i++ ) {
			$random_password = TTPassword::generateRandomPassword( 14 );
			$this->assertGreaterThan( 3, TTPassword::getPasswordStrength( $random_password ), $random_password ); //14 character random password should always be above 3 on the password strength.
		}
	}

	/**
	 * @group testLockFile
	 */
	function testLockFile() {
		global $config_vars;

		$lock_file_name = $config_vars['cache']['dir'] . DIRECTORY_SEPARATOR . 'unit_test' . '.lock';
		@unlink($lock_file_name);

		//Test with default timeout.
		$lock_file = new LockFile( $lock_file_name );
		if ( $lock_file->exists() == FALSE ) {
			$lock_file->create();

			$this->assertGreaterThan( 0, $lock_file->getCurrentPID() );
			$this->assertEquals( $lock_file->isPIDRunning( $lock_file->getCurrentPID() ), TRUE );
			$this->assertEquals( $lock_file->exists(), TRUE );
		}

		$lock_file->delete();
		$this->assertEquals( $lock_file->exists(), FALSE );

		//Test with really short timeout
		$lock_file = new LockFile( $lock_file_name );
		$lock_file->max_lock_file_age = 1;
		if ( $lock_file->exists() == FALSE ) {
			$lock_file->create();

			Debug::Text( '  Sleeping...', __FILE__, __LINE__, __METHOD__, 10 );
			sleep(2);

			$this->assertGreaterThan( 0, $lock_file->getCurrentPID() );
			$this->assertEquals( $lock_file->isPIDRunning( $lock_file->getCurrentPID() ), TRUE );
			$this->assertEquals( $lock_file->exists(), TRUE );
		}

		$lock_file->delete();
		$this->assertEquals( $lock_file->exists(), FALSE );


		//Test without PID
		$lock_file = new LockFile( $lock_file_name );
		$lock_file->use_pid = FALSE;
		$lock_file->max_lock_file_age = 1;
		if ( $lock_file->exists() == FALSE ) {
			$lock_file->create();

			sleep(2);

			$this->assertEquals( FALSE, $lock_file->getCurrentPID() );
			$this->assertEquals( $lock_file->isPIDRunning( $lock_file->getCurrentPID() ), FALSE );
			$this->assertEquals( $lock_file->exists(), FALSE );
		}

		$lock_file->delete();
		$this->assertEquals( $lock_file->exists(), FALSE );
	}

	/**
	 * @group testRemoteHTTP
	 */
	function testRemoteHTTP() {
		$url = 'www.timetrex.com/blank.html';

		$header_size = (int)Misc::getRemoteHTTPFileSize('http://'.$url);
		$this->assertEquals( (int)$header_size, 30 ); //30 Bytes.

		$header_size = (int)Misc::getRemoteHTTPFileSize('https://'.$url);
		$this->assertEquals( (int)$header_size, 30 ); //30 Bytes.


		$temp_file_name = tempnam( '/tmp/', 'unit_test_http_' );
		$size = Misc::downloadHTTPFile( 'http://'.$url, $temp_file_name );
		//Debug::Text( ' Temp File Name: '. $temp_file_name, __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( (int)$size, (int)$header_size ); //Make sure the downloaded size matches the header size too.
		$this->assertEquals( (int)$size, 30 ); //30 Bytes.
		$this->assertEquals( filesize($temp_file_name), (int)$size ); //30 Bytes.
		unlink($temp_file_name);

		$temp_file_name = tempnam( '/tmp/', 'unit_test_http_' );
		//Debug::Text( ' Temp File Name: '. $temp_file_name, __FILE__, __LINE__, __METHOD__, 10 );
		$size = (int)Misc::downloadHTTPFile( 'https://'.$url, $temp_file_name );
		$this->assertEquals( (int)$size, (int)$header_size ); //Make sure the downloaded size matches the header size too.
		$this->assertEquals( (int)$size, 30 ); //30 Bytes.
		$this->assertEquals( filesize($temp_file_name), (int)$size ); //30 Bytes.
		unlink($temp_file_name);


		//Test downloading to the same file that should already exist from above.
		//Debug::Text( ' Temp File Name: '. $temp_file_name, __FILE__, __LINE__, __METHOD__, 10 );
		$size = (int)Misc::downloadHTTPFile( 'https://'.$url, $temp_file_name );
		$this->assertEquals( (int)$size, (int)$header_size ); //Make sure the downloaded size matches the header size too.
		$this->assertEquals( (int)$size, 30 ); //30 Bytes.
		$this->assertEquals( filesize($temp_file_name), (int)$size ); //30 Bytes.
		unlink($temp_file_name);


		//Test downloading to a directory without permissions, or one that doesn't exist.
		$temp_file_name = '/root'.tempnam( '/tmp/', 'unit_test_http_' );
		Debug::Text( ' Temp File Name: '. $temp_file_name, __FILE__, __LINE__, __METHOD__, 10 );
		$retval = Misc::downloadHTTPFile( 'https://'.$url, $temp_file_name );

		$this->assertEquals( $retval, FALSE ); //Download should fail without PHP warnings.
		@unlink($temp_file_name);
	}

	/**
	 * @group testgetAmountUpToLimit
	 */
	function testgetAmountUpToLimit() {
		//Positive Amount and Positive Limit, should return amount up to the limit.
		$this->assertEquals( Misc::getAmountUpToLimit( 0, 100 ), 0 );
		$this->assertEquals( Misc::getAmountUpToLimit( 1, 100 ), 1 );
		$this->assertEquals( Misc::getAmountUpToLimit( 50, 100 ), 50 );
		$this->assertEquals( Misc::getAmountUpToLimit( 98, 100 ), 98 );
		$this->assertEquals( Misc::getAmountUpToLimit( 99, 100 ), 99 );
		$this->assertEquals( Misc::getAmountUpToLimit( 100, 100 ), 100 );
		$this->assertEquals( Misc::getAmountUpToLimit( 101, 100 ), 100 );
		$this->assertEquals( Misc::getAmountUpToLimit( 200, 100 ), 100 );
		$this->assertEquals( Misc::getAmountUpToLimit( 201, 100 ), 100 );
		$this->assertEquals( Misc::getAmountUpToLimit( 1001, 100 ), 100 );

		//Positive Amount and Negative Limit should always return 0
		$this->assertEquals( Misc::getAmountUpToLimit( 101, -100 ), 0 );
		$this->assertEquals( Misc::getAmountUpToLimit( 100, -100 ), 0 );
		$this->assertEquals( Misc::getAmountUpToLimit( 99, -100 ), 0 );
		$this->assertEquals( Misc::getAmountUpToLimit( 99, -100 ), 0 );
		$this->assertEquals( Misc::getAmountUpToLimit( 0, -100 ), 0 );

		//Negative amounts, but positive limits should always return the amount.
		$this->assertEquals( Misc::getAmountUpToLimit( -100, 100 ), -100 );
		$this->assertEquals( Misc::getAmountUpToLimit( -99, 100 ), -99 );
		$this->assertEquals( Misc::getAmountUpToLimit( -98, 100 ), -98 );

		//Negative amounts and negative limits should be treated as "getAmountDownToLimit".
		$this->assertEquals( Misc::getAmountUpToLimit( -1001, -100 ), -100 );
		$this->assertEquals( Misc::getAmountUpToLimit( -200, -100 ), -100 );
		$this->assertEquals( Misc::getAmountUpToLimit( -101, -100 ), -100 );
		$this->assertEquals( Misc::getAmountUpToLimit( -100, -100 ), -100 );
		$this->assertEquals( Misc::getAmountUpToLimit( -99, -100 ), -99 );
		$this->assertEquals( Misc::getAmountUpToLimit( -98, -100 ), -98 );
		$this->assertEquals( Misc::getAmountUpToLimit( -50, -100 ), -50 );
		$this->assertEquals( Misc::getAmountUpToLimit( -1, -100 ), -1 );
		$this->assertEquals( Misc::getAmountUpToLimit( -0, -100 ), -0 );

		//Test no limit
		$this->assertEquals( Misc::getAmountUpToLimit( 100, FALSE ), 100 );
		$this->assertEquals( Misc::getAmountUpToLimit( 100, TRUE ), 100 );
		$this->assertEquals( Misc::getAmountUpToLimit( 100, NULL ), 100 );
		$this->assertEquals( Misc::getAmountUpToLimit( 100, '' ), 100 );
		$this->assertEquals( Misc::getAmountUpToLimit( -100, '' ), -100 );


		//Amount DIff. - Positive Amount and Positive Limit, should return amount up to the limit.
		$this->assertEquals( Misc::getAmountDifferenceUpToLimit( 0, 100 ), 100 );
		$this->assertEquals( Misc::getAmountDifferenceUpToLimit( 1, 100 ), 99 );
		$this->assertEquals( Misc::getAmountDifferenceUpToLimit( 50, 100 ), 50 );
		$this->assertEquals( Misc::getAmountDifferenceUpToLimit( 98, 100 ), 2 );
		$this->assertEquals( Misc::getAmountDifferenceUpToLimit( 99, 100 ), 1 );
		$this->assertEquals( Misc::getAmountDifferenceUpToLimit( 100, 100 ), 0 );
		$this->assertEquals( Misc::getAmountDifferenceUpToLimit( 101, 100 ), 0 );
		$this->assertEquals( Misc::getAmountDifferenceUpToLimit( 200, 100 ), 0 );
		$this->assertEquals( Misc::getAmountDifferenceUpToLimit( 201, 100 ), 0 );
		$this->assertEquals( Misc::getAmountDifferenceUpToLimit( 1001, 100 ), 0 );

		//Amount Diff Negative amounts and negative limits should be treated as "getAmountDownToLimit".
		$this->assertEquals( Misc::getAmountDifferenceUpToLimit( -1001, -100 ), 0 );
		$this->assertEquals( Misc::getAmountDifferenceUpToLimit( -200, -100 ), 0 );
		$this->assertEquals( Misc::getAmountDifferenceUpToLimit( -101, -100 ), 0 );
		$this->assertEquals( Misc::getAmountDifferenceUpToLimit( -100, -100 ), 0 );
		$this->assertEquals( Misc::getAmountDifferenceUpToLimit( -99, -100 ), -1 );
		$this->assertEquals( Misc::getAmountDifferenceUpToLimit( -98, -100 ), -2 );
		$this->assertEquals( Misc::getAmountDifferenceUpToLimit( -50, -100 ), -50 );
		$this->assertEquals( Misc::getAmountDifferenceUpToLimit( -1, -100 ), -99 );
		$this->assertEquals( Misc::getAmountDifferenceUpToLimit( -0, -100 ), -100 );
	}

	/**
	 * @group testIsSubDirectory
	 */
	function testIsSubDirectory() {
		$parent_dir = '/';
		$child_dir = '/var';
		$this->assertEquals( Misc::isSubDirectory( $child_dir, $parent_dir ), TRUE );

		$parent_dir = '/var';
		$child_dir = '/usr';
		$this->assertEquals( Misc::isSubDirectory( $child_dir, $parent_dir ), FALSE );

		$parent_dir = '/var';
		$child_dir = '/usr/';
		$this->assertEquals( Misc::isSubDirectory( $child_dir, $parent_dir ), FALSE );

		$parent_dir = '/var/';
		$child_dir = '/usr/';
		$this->assertEquals( Misc::isSubDirectory( $child_dir, $parent_dir ), FALSE );

		//Test with directories that do not exist.
		$parent_dir = '/var/www/TimeTrex556688';
		$child_dir = '/var/www/TimeTrex556688Test';
		$this->assertEquals( Misc::isSubDirectory( $child_dir, $parent_dir ), FALSE );

		$parent_dir = '/var/www/TimeTrex556688/';
		$child_dir = '/var/www/TimeTrex556688Test/';
		$this->assertEquals( Misc::isSubDirectory( $child_dir, $parent_dir ), FALSE );


		$parent_dir = '/var/www/TimeTrex556688Test';
		$child_dir = '/var/www/TimeTrex556688';
		$this->assertEquals( Misc::isSubDirectory( $child_dir, $parent_dir ), FALSE );

		$parent_dir = '/var/www/TimeTrex556688';
		$child_dir = '/var/www/TimeTrex556688/storage';
		$this->assertEquals( Misc::isSubDirectory( $child_dir, $parent_dir ), TRUE );

		$parent_dir = '/var/www/TimeTrex556688/';
		$child_dir = '/var/www/TimeTrex556688/storage/';
		$this->assertEquals( Misc::isSubDirectory( $child_dir, $parent_dir ), TRUE );

		//This directory should exist for this test to be accurate.
		$parent_dir = '/etc/cron.d';
		$child_dir = '/etc/cron.daily';
		$this->assertEquals( Misc::isSubDirectory( $child_dir, $parent_dir ), FALSE );
	}

	/**
	 * @group testSOAPClient
	 */
	function testSOAPClient() {
		$ttsc = TTnew('TimeTrexSoapClient'); /** @var TimeTrexSoapClient $ttsc */
		$this->assertEquals( $ttsc->ping(), TRUE );
	}

	/**
	 * @group testCensorString
	 */
	function testCensorString() {
		$this->assertEquals( Misc::censorString('0'), 'X' );
		$this->assertEquals( Misc::censorString('00'), 'XX' );
		$this->assertEquals( Misc::censorString('000'), '0X0' );
		$this->assertEquals( Misc::censorString('0000'), '0XX0' );
		$this->assertEquals( Misc::censorString('00000'), '0XXX0' );
		$this->assertEquals( Misc::censorString('000000'), '00XX00' );
		$this->assertEquals( Misc::censorString('0000000'), '00XXX00' );
		$this->assertEquals( Misc::censorString('00000000'), '00XXXX00' );
		$this->assertEquals( Misc::censorString('000000000'), '000XXX000' );
		$this->assertEquals( Misc::censorString('123456789'), '123XXX789' );
		$this->assertEquals( Misc::censorString('12345678901234567890'), '123456XXXXXXXX567890' );

		//censorString( $str, $censor_char = 'X', $min_first_chunk_size = NULL, $max_first_chunk_size = NULL, $min_last_chunk_size = NULL, $max_last_chunk_size = NULL )
		$this->assertEquals( Misc::censorString('4111222233334444', 'X', 4, 4, 4, 4), '4111XXXXXXXX4444' );

		$uf = TTnew('UserFactory'); /** @var UserFactory $uf */
		$this->assertEquals( $uf->getSecureSIN('0'), 'X' );
		$this->assertEquals( $uf->getSecureSIN('00'), 'XX' );
		$this->assertEquals( $uf->getSecureSIN('000'), 'XXX' );
		$this->assertEquals( $uf->getSecureSIN('0000'), 'XXXX' );
		$this->assertEquals( $uf->getSecureSIN('00000'), 'XXXXX' );
		$this->assertEquals( $uf->getSecureSIN('000000'), 'XXXXXX' );
		$this->assertEquals( $uf->getSecureSIN('0000000'), '0XX0000' );
		$this->assertEquals( $uf->getSecureSIN('00000000'), '0XXX0000' );
		$this->assertEquals( $uf->getSecureSIN('000000000'), '0XXXX0000' );
		$this->assertEquals( $uf->getSecureSIN('123456789'), '1XXXX6789' );
	}

	/**
	 * @group testUUID
	 */
	function testUUID() {
		//Make sure UUIDs are unique at least across 1 million tight iterations.
		$max = 1000000;
		for( $i = 0; $i < $max; $i++ ) {
			$uuid_arr[] = TTUUID::generateUUID();
		}
		$unique_uuid_arr = array_unique($uuid_arr);

		$this->assertEquals( count($uuid_arr) , count($unique_uuid_arr) );
		unset( $uuid_arr, $unique_uuid_arr );
	}

	/**
	 * @group testTruncateUUID
	 */
	function testTruncateUUID() {
		//Make sure UUIDs converted from INTs still get the most unique UUID data first.
		$this->assertEquals( TTUUID::truncateUUID( TTUUID::getConversionPrefix().'-000000192136', 12, FALSE ), '000000192136' );
		$this->assertEquals( TTUUID::truncateUUID( TTUUID::getConversionPrefix().'-000000191922', 12, FALSE ), '000000191922' );
		$this->assertEquals( TTUUID::truncateUUID( '11e7b349-9af4-7bc0-af20-999999191922', 12, FALSE ), '9af47bc0af20' );
		$this->assertEquals( TTUUID::truncateUUID( '11e7b349-24dc-7bc0-af20-21ea65522ba3', 12, FALSE ), '24dc7bc0af20' );
		$this->assertEquals( TTUUID::truncateUUID( '11e7a84a-9af4-e9e0-b077-21ea65522ba3', 12, FALSE ), '9af4e9e0b077' );
	}

	/**
	 * @group testParsingUUID
	 */
	function testParsingUUID() {
		//Make sure UUIDs converted from INTs still get the most unique UUID data first.
		$this->assertEquals( TTUUID::castUUID( ' 11e7b349-9af4-7bc0-af20-999999191922 ' ), '11e7b349-9af4-7bc0-af20-999999191922' );
		$this->assertEquals( TTUUID::castUUID( '11e7b349-9af4-7bc0-af20-999999191922' ), '11e7b349-9af4-7bc0-af20-999999191922' );
		$this->assertEquals( TTUUID::castUUID( array( '11e7b349-9af4-7bc0-af20-999999191922' ) ), '00000000-0000-0000-0000-000000000000' );
		$this->assertEquals( TTUUID::castUUID( '' ), '00000000-0000-0000-0000-000000000000' );
		$this->assertEquals( TTUUID::castUUID( NULL, TRUE ), NULL ); //Allow NULLs
		$this->assertEquals( TTUUID::castUUID( NULL, FALSE ), '00000000-0000-0000-0000-000000000000' ); //Don't allow NULLs
		$this->assertEquals( TTUUID::castUUID( FALSE ), '00000000-0000-0000-0000-000000000000' );
		$this->assertEquals( TTUUID::castUUID( TRUE ), '00000000-0000-0000-0000-000000000000' );
		$this->assertEquals( TTUUID::castUUID( 0 ), '00000000-0000-0000-0000-000000000000' );
		$this->assertEquals( TTUUID::castUUID( '0' ), '00000000-0000-0000-0000-000000000000' );

		$this->assertEquals( TTUUID::isUUID( '11e7b349-9af4-7bc0-af20-999999191922' ), TRUE );
		$this->assertEquals( TTUUID::isUUID( array( '11e7b349-9af4-7bc0-af20-999999191922' ) ), FALSE );
		$this->assertEquals( TTUUID::isUUID( ' 11e7b349-9af4-7bc0-af20-999999191922 ' ), FALSE ); //This is not trimmed as it has to be able to go straight into PostgreSQL without complaint.
	}

	/**
	 * @group testUUIDSorting
	 */
	function testUUIDSorting() {
		//Make sure UUIDs can be sorted and appear in time order as they were created.
		$max = 10000;
		for( $i = 0; $i < $max; $i++ ) {
			$uuid_arr[] = TTUUID::generateUUID();
		}
		$sorted_uuid_arr = $uuid_arr;

		sort($sorted_uuid_arr);
		$diff_uuid_arr = array_diff_assoc( $uuid_arr, $sorted_uuid_arr);
		$this->assertEquals( count($diff_uuid_arr), 0 );

		//Reverse the sort and confirm all differences.
		rsort($sorted_uuid_arr);
		$diff_uuid_arr = array_diff_assoc( $uuid_arr, $sorted_uuid_arr);
		$this->assertEquals( count($diff_uuid_arr), $max );

		//Use a strcmp sort and confirm it still is in the correct order.
		usort($sorted_uuid_arr, 'strcmp');
		$diff_uuid_arr = array_diff_assoc( $uuid_arr, $sorted_uuid_arr);
		$this->assertEquals( count($diff_uuid_arr), 0 );

		//Natural sort will be the wrong order and therefore have many differences.
		usort($sorted_uuid_arr, 'strnatcasecmp');
		$diff_uuid_arr = array_diff_assoc( $uuid_arr, $sorted_uuid_arr);
		$this->assertGreaterThan( 0, count($diff_uuid_arr) );

		unset( $uuid_arr, $sorted_uuid_arr, $diff_uuid_arr );
	}
}
?>