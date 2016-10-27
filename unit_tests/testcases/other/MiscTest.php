<?php
require_once('PHPUnit/Framework/TestCase.php');

class MiscTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10);
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
	
}
?>