<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2017 TimeTrex Software Inc.
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
		$str = 'This is a sample string to be encrypted and decrytped.';
		$encrypted_str = Misc::encrypt( $str );
		$decrypted_str = Misc::decrypt( $encrypted_str );

		$this->assertEquals( $str, $decrypted_str );
	}

	/**
	 * @group testDatabaseLoadBalancerA
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
			//Debug::Arr( $db_connection_obj,  'zzzAdding DB Connection...', __FILE__, __LINE__, __METHOD__, 1);
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
	 * @group testDatabaseLoadBalancerB
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
			//Debug::Arr( $db_connection_obj,  'zzzAdding DB Connection...', __FILE__, __LINE__, __METHOD__, 1);
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
	 * @group testDatabaseLoadBalancerC
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
	 * @group testDatabaseLoadBalancerD
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
	 * @group testDatabaseLoadBalancerE
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

		$this->assertGreaterThan( 600, $retarr[2] );
		$this->assertLessThan( 750, $retarr[2] );
	}

	/**
	 * @group testDatabaseLoadBalancerF
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
	 * @group testDatabaseLoadBalancerG
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
	 * @group testDatabaseLoadBalancerH
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
	 * @group testDatabaseLoadBalancerI
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
	 * @group testDatabaseLoadBalancerSessionVarsA
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
	 * @group testDatabaseLoadBalancerSessionVarsB
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

	function testPasswordStrength() {
		//Numbers
		$this->assertEquals( Misc::getPasswordStrength('1'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('12'), 1 );

		$this->assertEquals( Misc::getPasswordStrength('123'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('1234'), 1 );

		$this->assertEquals( Misc::getPasswordStrength('12345'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('123456'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('1234567'), 1 );

		$this->assertEquals( Misc::getPasswordStrength('12345678'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('123456789'), 1 );

		$this->assertEquals( Misc::getPasswordStrength('1234567890'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('12345678901'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('123456789012'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('1234567890123'), 1 );

		$this->assertEquals( Misc::getPasswordStrength('12345678901234'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('123456789012345'), 1 );

		$this->assertEquals( Misc::getPasswordStrength('987654321'), 1 ); //Backwards

		//Letters
		$this->assertEquals( Misc::getPasswordStrength('a'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('ab'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('abc'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('abcd'), 1 );

		$this->assertEquals( Misc::getPasswordStrength('abcde'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('abcdef'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('abcdefg'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('abcdefgh'), 1 );

		$this->assertEquals( Misc::getPasswordStrength('abcdefghi'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('abcdefghij'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('abcdefghijk'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('abcdefghijkl'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('abcdefghijklm'), 1 );

		$this->assertEquals( Misc::getPasswordStrength('abcdefghijklmn'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('abcdefghijklmno'), 1 );

		$this->assertEquals( Misc::getPasswordStrength('ihgfedcba'), 1 ); //Backwards

		//Half letters, half numbers
		$this->assertEquals( Misc::getPasswordStrength('a1'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('ab12'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('abc123'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('abcd1234'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('abcde12345'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('abcdef123456'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('abcdefg1234567'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('abcdefgh12345678'), 1 );


		//All the same char.
		$this->assertEquals( Misc::getPasswordStrength('aaaaaa'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('aaabbb'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('aaaccc'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('111111'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('111222'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('111333'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('123123'), 1 );

		//Some what real passwords.
		$this->assertEquals( Misc::getPasswordStrength('test'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('pear'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('orange'), 1 );

		$this->assertEquals( Misc::getPasswordStrength('!Qa12'), 2 ); //Unique, but not enough characters to make it difficult.
		$this->assertEquals( Misc::getPasswordStrength('2000'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('696969'), 2 );
		$this->assertEquals( Misc::getPasswordStrength('trustno1'), 2 );

		$this->assertEquals( Misc::getPasswordStrength('abababababababab'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('abcabcabcabcabc'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('abcdabcdabcdabcd'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('abcd.abcd^abcd#abcd'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('abc123'), 1 );
		$this->assertEquals( Misc::getPasswordStrength('test123'), 2 );
		$this->assertEquals( Misc::getPasswordStrength('admin123'), 3 );
		$this->assertEquals( Misc::getPasswordStrength('pear123'), 3 );
		$this->assertEquals( Misc::getPasswordStrength('pear1234'), 3 );
		$this->assertEquals( Misc::getPasswordStrength('pear12345'), 3 );
		$this->assertEquals( Misc::getPasswordStrength('orange123456'), 4 );
		$this->assertEquals( Misc::getPasswordStrength('car123456789'), 1 ); //Too many consecutive.
		$this->assertEquals( Misc::getPasswordStrength('cars123456789'), 1 ); //Too many consecutive.
		$this->assertEquals( Misc::getPasswordStrength('orange123456789'), 1 ); //Too many consecutive.
		$this->assertEquals( Misc::getPasswordStrength('superabundant123456789'), 6 ); //Too many consecutive.

		$this->assertEquals( Misc::getPasswordStrength('cars.8.apple'), 4 );
		$this->assertEquals( Misc::getPasswordStrength('cars.8#apple'), 4 );

		$this->assertEquals( Misc::getPasswordStrength('password'), 1 ); //Dictionary word
		$this->assertEquals( Misc::getPasswordStrength('Password'), 1 ); //Dictionary word
		$this->assertEquals( Misc::getPasswordStrength('password1'), 1 ); //Dictionary word with one extra char.
		$this->assertEquals( Misc::getPasswordStrength('password11'), 3 );
		$this->assertEquals( Misc::getPasswordStrength('1password'), 1 ); //Dictionary word with one extra char.
		$this->assertEquals( Misc::getPasswordStrength('password!'), 1 ); //Dictionary word with one extra char.
		$this->assertEquals( Misc::getPasswordStrength('!password'), 1 ); //Dictionary word with one extra char.
		$this->assertEquals( Misc::getPasswordStrength('qwerty'), 1 ); //Dictionary word
		$this->assertEquals( Misc::getPasswordStrength('dragon'), 1 ); //Dictionary word

		$this->assertEquals( Misc::getPasswordStrength('superabundant'), 1 ); //Dictionary word
		$this->assertEquals( Misc::getPasswordStrength('Super.Abundant#41'), 5 ); //Dictionary word
		$this->assertEquals( Misc::getPasswordStrength('pearappleorange'), 3 );
		$this->assertEquals( Misc::getPasswordStrength('pear.apple@orange#strawberry'), 5 );
		$this->assertEquals( Misc::getPasswordStrength('superabundant123'), 4 );

		$this->assertEquals( Misc::getPasswordStrength('Superabundant.123'), 5 );
		$this->assertEquals( Misc::getPasswordStrength('Super^91Pear.87'), 5 );
		$this->assertEquals( Misc::getPasswordStrength('Super^91Bop.87'), 5 );

		$this->assertEquals( Misc::getPasswordStrength('a1j8U4y7K2qA.#@5.'), 7 );
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
	}

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
		$child_dir =  '/var/www/TimeTrex556688/storage';
		$this->assertEquals( Misc::isSubDirectory( $child_dir, $parent_dir ), TRUE );

		$parent_dir = '/var/www/TimeTrex556688/';
		$child_dir =  '/var/www/TimeTrex556688/storage/';
		$this->assertEquals( Misc::isSubDirectory( $child_dir, $parent_dir ), TRUE );

		//This directory should exist for this test to be accurate.
		$parent_dir = '/etc/cron.d';
		$child_dir =  '/etc/cron.daily';
		$this->assertEquals( Misc::isSubDirectory( $child_dir, $parent_dir ), FALSE );
	}

	function testSOAPClient() {
		$ttsc = TTnew('TimeTrexSoapClient');
		$this->assertEquals( $ttsc->ping(), TRUE );
	}
}
?>