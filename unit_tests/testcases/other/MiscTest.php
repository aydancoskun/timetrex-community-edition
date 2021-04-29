<?php /** @noinspection PhpMissingDocCommentInspection */

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

class MiscTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		TTi18n::setLocale( 'en_US', LC_ALL, true ); //This fixes problems with NumberFormat when the locale is changed and not changed back.

		return true;
	}

	public function tearDown() {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );

		return true;
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
			$this->assertEquals( $visa_unencrypted, Misc::decrypt( $visa_unencrypted ) );
		}

		//check the case for the colon.
		$x = 'x:z';
		$this->assertEquals( $x, Misc::decrypt( $x ) );

		//Test that changing the salt will not decrypt the string.
		$str = 'This is a sample string to be encrypted and decrytped.';
		$encrypted_str = Misc::encrypt( $str );
		$config_vars['other']['salt'] = 'zzzzzzzzzzzzzzzzzzzzzzzzzz';
		$decrypted_str = Misc::decrypt( $encrypted_str );
		$this->assertNotEquals( $str, $decrypted_str );
	}

	/**
	 * @group MiscTest_testDatabaseLoadBalancerA
	 */
	function testDatabaseLoadBalancerA() {
		global $config_vars;

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );


		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'master', 100 ],
				[ $host, 'master', 100 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			//if ( $db_host_arr[2] == 100 ) {
			//	$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'].'9', $config_vars['database']['password'], $config_vars['database']['database_name'] );
			//} else {
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			//}
			//Debug::Arr( $db_connection_obj,  'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1);
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db_connection_obj->getADODbObject()->noBlobs = true; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		$retarr = [];
		$max = 1000;
		for ( $i = 0; $i < $max; $i++ ) {
			$connection_id = $db->getConnectionByWeight( 'master' );
			if ( !isset( $retarr[$connection_id] ) ) {
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

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'master', 100 ],
				[ $host, 'master', 200 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			//if ( $db_host_arr[2] == 100 ) {
			//	$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'].'9', $config_vars['database']['password'], $config_vars['database']['database_name'] );
			//} else {
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			//}
			//Debug::Arr( $db_connection_obj,  'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1);
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db_connection_obj->getADODbObject()->noBlobs = true; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		$retarr = [];
		$max = 1000;
		for ( $i = 0; $i < $max; $i++ ) {
			$connection_id = $db->getConnectionByWeight( 'master' );
			if ( !isset( $retarr[$connection_id] ) ) {
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

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'master', 0 ],
				[ $host, 'master', 100 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db_connection_obj->getADODbObject()->noBlobs = true; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		$retarr = [ 0 => 0, 1 => 0 ];
		$max = 1000;
		for ( $i = 0; $i < $max; $i++ ) {
			$connection_id = $db->getConnectionByWeight( 'master' );
			if ( !isset( $retarr[$connection_id] ) ) {
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

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'master', 0 ],
				[ $host, 'slave', 100 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db_connection_obj->getADODbObject()->noBlobs = true; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		$retarr = [ 0 => 0, 1 => 0 ];
		$max = 1000;
		for ( $i = 0; $i < $max; $i++ ) {
			//$connection_id = $db->getConnectionByWeight( 'master' );
			$connection_id = $db->getLoadBalancedConnection( 'master' );
			if ( !isset( $retarr[$connection_id] ) ) {
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

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'master', 10 ],
				[ $host, 'slave', 100 ],
				[ $host, 'slave', 200 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db_connection_obj->getADODbObject()->noBlobs = true; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		$retarr = [ 0 => 0, 1 => 0, 2 => 0 ];
		$max = 1000;
		for ( $i = 0; $i < $max; $i++ ) {
			$connection_id = $db->getConnectionByWeight( 'slave' );
			//$connection_id = $db->getLoadBalancedConnection( 'master' );
			if ( !isset( $retarr[$connection_id] ) ) {
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

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'master', 10 ],
				[ $host, 'slave', 100 ],
				[ $host, 'slave', 200 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db_connection_obj->getADODbObject()->noBlobs = true; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		$db->removeConnection( 1 ); //Remove first slave to test failover.

		$retarr = [ 0 => 0, 1 => 0, 2 => 0 ];
		$max = 1000;
		for ( $i = 0; $i < $max; $i++ ) {
			//$connection_id = $db->getConnectionByWeight( 'slave' );
			$connection_id = $db->getLoadBalancedConnection( 'slave' );
			if ( !isset( $retarr[$connection_id] ) ) {
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

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'master', 100 ],
				[ $host, 'slave', 100 ],
				[ $host, 'slave', 100 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db_connection_obj->getADODbObject()->noBlobs = true; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		$retarr = [ 0 => 0, 1 => 0, 2 => 0 ];
		$max = 100;
		for ( $i = 0; $i < $max; $i++ ) {
			$db->Execute( 'SELECT 1' );
			//$connection_id = $db->getConnectionByWeight( 'slave' );
			$connection_id = $db->getLoadBalancedConnection( 'slave' );
			if ( !isset( $retarr[$connection_id] ) ) {
				$retarr[$connection_id] = 0;
			}
			$retarr[$connection_id]++;
		}
		//var_dump($retarr);

		if ( $retarr[0] > 0 ) {
			$this->assertEquals( 100, $retarr[0] );
			$this->assertEquals( 0, $retarr[1] );
			$this->assertEquals( 0, $retarr[2] );
		} else if ( $retarr[1] > 0 ) {
			$this->assertEquals( 0, $retarr[0] );
			$this->assertEquals( 100, $retarr[1] );
			$this->assertEquals( 0, $retarr[2] );
		} else if ( $retarr[2] > 0 ) {
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

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'master', 0 ],
				[ $host, 'slave', 100 ],
				[ $host, 'slave', 100 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db_connection_obj->getADODbObject()->noBlobs = true; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		$retarr = [ 0 => 0, 1 => 0, 2 => 0 ];
		$max = 100;
		for ( $i = 0; $i < $max; $i++ ) {
			//Test going in/out of transactions to make sure they are pinned to the master properly.
			if ( $i == 10 || $i == 80 ) {
				$db->BeginTrans();
			}
			$db->Execute( 'SELECT 1' );

			if ( $i == 20 || $i == 90 ) {
				$db->CommitTrans();
			}

			$connection_id = $db->getLoadBalancedConnection( 'slave' );
			if ( !isset( $retarr[$connection_id] ) ) {
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

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'master', 0 ],
				[ $host, 'slave', 100 ],
				[ $host, 'slave', 100 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db_connection_obj->getADODbObject()->noBlobs = true; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		$retarr = [ 0 => 0, 1 => 0, 2 => 0 ];
		$max = 100;
		for ( $i = 0; $i < $max; $i++ ) {
			//Test going in/out of *nested* transactions to make sure they are pinned to the master properly.
			if ( $i == 10 || $i == 15 ) {
				$db->StartTrans();
			}
			$db->Execute( 'SELECT 1' );

			if ( $i == 20 || $i == 25 ) {
				$db->CompleteTrans();
			}

			$connection_id = $db->getLoadBalancedConnection( 'slave' );
			if ( !isset( $retarr[$connection_id] ) ) {
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

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'master', 100 ],
				[ $host, 'slave', 100 ],
				[ $host, 'slave', 100 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db_connection_obj->getADODbObject()->noBlobs = true; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		if ( strncmp( $db->databaseType, 'postgres', 8 ) == 0 ) {
			$db->_getConnection( 0 );
			$db->_getConnection( 1 );

			$time_zone = 'America/New_York';

			//SET calls should be intercepted and run on the entire cluster automatically.
			$db->Execute( 'SET SESSION TIME ZONE ' . '\''. $time_zone .'\'' );
			$results = $db->ClusterExecute( 'SHOW TIME ZONE', false, true, true ); //Only existing connections.
			//var_dump($result);
			$this->assertCount( 2, $results );                                     //Only two connections established so far.
			foreach ( $results as $key => $rs ) {
				Debug::Text( 'Testing result from connection: ' . $key . ' Result: ' . $rs->fields['TimeZone'], __FILE__, __LINE__, __METHOD__, 1 );
				if ( !$rs ) {
					Debug::Text( 'Testing result from connection: ' . $key, __FILE__, __LINE__, __METHOD__, 1 );
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'], 'Query failed!' );
				} else {
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'] );
				}
			}

			//
			//Test cluster wide execution.
			//

			$db->ClusterExecute( 'SET SESSION TIME ZONE ' . $db->qstr( $time_zone ), false, true, false );
			$results = $db->ClusterExecute( 'SHOW TIME ZONE', false, true, false );

			//var_dump($result);
			$this->assertCount( 3, $results );
			foreach ( $results as $key => $rs ) {
				Debug::Text( 'Testing result from connection: ' . $key . ' Result: ' . $rs->fields['TimeZone'], __FILE__, __LINE__, __METHOD__, 1 );
				if ( !$rs ) {
					Debug::Text( 'Testing result from connection: ' . $key, __FILE__, __LINE__, __METHOD__, 1 );
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

		require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'adodb' . DIRECTORY_SEPARATOR . 'adodb-loadbalancer.inc.php' );

		$db = new ADOdbLoadBalancer();

		//In case load balancing is used, parse out just the first host.
		$host_arr = Misc::parseDatabaseHostString( $config_vars['database']['host'] );
		$host = $host_arr[0][0];
		$db_hosts = [
				[ $host, 'master', 100 ],
				[ $host, 'slave', 100 ],
				[ $host, 'slave', 100 ],
		];

		foreach ( $db_hosts as $db_host_arr ) {
			Debug::Text( 'Adding DB Connection...', __FILE__, __LINE__, __METHOD__, 1 );
			$db_connection_obj = new ADOdbLoadBalancerConnection( $config_vars['database']['type'], $db_host_arr[1], $db_host_arr[2], (bool)$config_vars['database']['persistent_connections'], $db_host_arr[0], $config_vars['database']['user'], $config_vars['database']['password'], $config_vars['database']['database_name'] );
			$db_connection_obj->getADODbObject()->SetFetchMode( ADODB_FETCH_ASSOC );
			$db_connection_obj->getADODbObject()->noBlobs = true; //Optimization to tell ADODB to not bother checking for blobs in any result set.
			$db_connection_obj->getADODbObject()->fmtTimeStamp = "'Y-m-d H:i:s'";
			$db->addConnection( $db_connection_obj );
		}
		unset( $type, $db_connection_obj );

		if ( strncmp( $db->databaseType, 'postgres', 8 ) == 0 ) {
			$time_zone = 'America/New_York';
			$db->setSessionVariable( 'TIME ZONE', '\''. $time_zone .'\'' );

			$db->_getConnection( 0 );
			$db->_getConnection( 1 );

			$results = $db->ClusterExecute( 'SHOW TIME ZONE', false, true, true ); //Only existing connections.
			//var_dump($result);
			$this->assertCount( 2, $results );                                     //Only two connections established so far.
			foreach ( $results as $key => $rs ) {
				Debug::Text( 'Testing result from connection: ' . $key . ' Result: ' . $rs->fields['TimeZone'], __FILE__, __LINE__, __METHOD__, 1 );
				if ( !$rs ) {
					Debug::Text( 'Testing result from connection: ' . $key, __FILE__, __LINE__, __METHOD__, 1 );
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'], 'Query failed!' );
				} else {
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'] );
				}
			}

			$db->_getConnection( 2 );

			//
			//Test cluster wide execution.
			//

			$results = $db->ClusterExecute( 'SHOW TIME ZONE', false, true, false );

			//var_dump($result);
			$this->assertCount( 3, $results );
			foreach ( $results as $key => $rs ) {
				Debug::Text( 'Testing result from connection: ' . $key . ' Result: ' . $rs->fields['TimeZone'], __FILE__, __LINE__, __METHOD__, 1 );
				if ( !$rs ) {
					Debug::Text( 'Testing result from connection: ' . $key, __FILE__, __LINE__, __METHOD__, 1 );
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'], 'Query failed!' );
				} else {
					$this->assertEquals( $time_zone, $rs->fields['TimeZone'] );
				}
			}

			//Change timezone, make sure it happens across all connections.

			$time_zone = 'America/Chicago';
			$db->setSessionVariable( 'TIME ZONE', '\''. $time_zone .'\'' );
			//
			//Test cluster wide execution.
			//

			$results = $db->ClusterExecute( 'SHOW TIME ZONE', false, true, false );

			//var_dump($result);
			$this->assertCount( 3, $results );
			foreach ( $results as $key => $rs ) {
				Debug::Text( 'Testing result from connection: ' . $key . ' Result: ' . $rs->fields['TimeZone'], __FILE__, __LINE__, __METHOD__, 1 );
				if ( !$rs ) {
					Debug::Text( 'Testing result from connection: ' . $key, __FILE__, __LINE__, __METHOD__, 1 );
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
		$this->assertEquals( '0', Misc::getBeforeDecimal( 0 ) );
		$this->assertEquals( '1', Misc::getBeforeDecimal( 1 ) );
		$this->assertEquals( '53', Misc::getBeforeDecimal( 53 ) );
		$this->assertEquals( '-53', Misc::getBeforeDecimal( -53 ) );
		$this->assertEquals( '3', Misc::getBeforeDecimal( 3.14 ) );
		$this->assertEquals( '-3', Misc::getBeforeDecimal( -3.14 ) );
		$this->assertEquals( '-3', Misc::getBeforeDecimal( -3.1 ) );
		$this->assertEquals( '510', Misc::getBeforeDecimal( 510.9 ) );
		$this->assertEquals( '-510', Misc::getBeforeDecimal( -510.9 ) );

		$this->assertEquals( '123456789012', Misc::getBeforeDecimal( 123456789012.12 ) );
		$this->assertEquals( '1234567890', Misc::getBeforeDecimal( 1234567890.1234 ) );
		$this->assertEquals( '123456789', Misc::getBeforeDecimal( 123456789.123456789 ) );  // Float precision overflow
		$this->assertEquals( '123456789', Misc::getBeforeDecimal( '123456789.123456789' ) );

		$this->assertEquals( '-123456789012', Misc::getBeforeDecimal( -123456789012.12 ) );
		$this->assertEquals( '-1234567890', Misc::getBeforeDecimal( -1234567890.1234 ) );
		$this->assertEquals( '-123456789', Misc::getBeforeDecimal( -123456789.123456789 ) );  // Float precision overflow
		$this->assertEquals( '-123456789', Misc::getBeforeDecimal( '-123456789.123456789' ) );


		$this->assertEquals( '0', Misc::getAfterDecimal( 0 ) );
		$this->assertEquals( '0', Misc::getAfterDecimal( 1 ) );
		$this->assertEquals( '0', Misc::getAfterDecimal( 3 ) );
		$this->assertEquals( '0', Misc::getAfterDecimal( -3 ) );
		$this->assertEquals( '10', Misc::getAfterDecimal( -3.1, true ) );
		$this->assertEquals( '1', Misc::getAfterDecimal( -3.1, false ) );
		$this->assertEquals( '14', Misc::getAfterDecimal( 3.14 ) );
		$this->assertEquals( '14', Misc::getAfterDecimal( -3.14 ) );
		$this->assertEquals( '90', Misc::getAfterDecimal( 510.9 ) );
		$this->assertEquals( '90', Misc::getAfterDecimal( -510.9 ) );
		$this->assertEquals( '12', Misc::getAfterDecimal( -123456789.123456789, true ) );

		$this->assertEquals( '12', Misc::getAfterDecimal( 123456789012.12, false ) );
		$this->assertEquals( '1234', Misc::getAfterDecimal( 1234567890.1234, false ) );
		$this->assertEquals( '12346', Misc::getAfterDecimal( 123456789.123456789, false ) );       // Float precision overflow
		$this->assertEquals( '123456789', Misc::getAfterDecimal( '123456789.123456789', false ) ); //Passed as string, so no float precision overflow.

		$this->assertEquals( '12', Misc::getAfterDecimal( -123456789012.12, false ) );
		$this->assertEquals( '1234', Misc::getAfterDecimal( -1234567890.1234, false ) );
		$this->assertEquals( '12346', Misc::getAfterDecimal( -123456789.123456789, false ) );       // Float precision overflow
		$this->assertEquals( '123456789', Misc::getAfterDecimal( '-123456789.123456789', false ) ); //Passed as string, so no float precision overflow.
	}

	/**
	 * @group MiscTest_testFormatNumber
	 */
	function testFormatNumber() {
		$this->assertSame( TTi18n::FormatNumber( '100.00', true ), '100.00' );
		$this->assertSame( TTi18n::FormatNumber( '100', true ), '100.00' );
		$this->assertSame( TTi18n::FormatNumber( '100.01000', true ), '100.01' );
		$this->assertSame( TTi18n::FormatNumber( '100.0101', true ), '100.0101' );

		$this->assertSame( TTi18n::FormatNumber( '100.0100', true, 1, 2 ), '100.01' );
		$this->assertSame( TTi18n::FormatNumber( '100.0123', true, 1, 2 ), '100.01' );
		$this->assertSame( TTi18n::FormatNumber( '100.1', true, 1, 2 ), '100.1' );
		$this->assertSame( TTi18n::FormatNumber( '100', true, 1, 2 ), '100.0' );

		$this->assertSame( TTi18n::FormatNumber( '100.1000', false, 1, 2 ), '100.1' );
		$this->assertSame( TTi18n::FormatNumber( '100.1234', false, 1, 2 ), '100.12' );
		$this->assertSame( TTi18n::FormatNumber( '100', false, 1, 2 ), '100' );
		$this->assertSame( TTi18n::FormatNumber( '100', false ), '100' );
		$this->assertSame( TTi18n::FormatNumber( '100.12345', false ), '100.12' );

		$this->assertSame( TTi18n::FormatNumber( '100.0000', true, 0, 2 ), '100' );
		$this->assertSame( TTi18n::FormatNumber( '100.0000', true, 0, 2 ), '100' ); //Make sure we don't get "100."
	}

	/**
	 * @group MiscTest_testMoneyFormat
	 */
	function testMoneyFormat() {
		//see the I18nTest that compares this function to the numberformat in i18n.
		Debug::Text( 'Thousands Separator: ' . TTi18n::getThousandsSymbol() . ' Decimal Symbol: ' . TTi18n::getDecimalSymbol(), __FILE__, __LINE__, __METHOD__, 1 );
		if ( TTi18n::getThousandsSymbol() == ',' && TTi18n::getDecimalSymbol() == '.' ) {
			$this->assertEquals( '12,345.15', Misc::MoneyFormat( 12345.152, true ) );
			$this->assertEquals( '12345.15', Misc::MoneyFormat( 12345.151, false ) );
			$this->assertEquals( '12,345.15', Misc::MoneyFormat( 12345.15, true ) );
			$this->assertEquals( '12345.15', Misc::MoneyFormat( 12345.15, false ) );
			$this->assertEquals( '12,345.10', Misc::MoneyFormat( 12345.1, true ) );
			$this->assertEquals( '12345.50', Misc::MoneyFormat( 12345.5, false ) );
			$this->assertEquals( '12,345.12', Misc::MoneyFormat( 12345.12345 ) );
			$this->assertEquals( '-12,345.12', Misc::MoneyFormat( -12345.12345 ) );
		} else {
			Debug::Text( 'ERROR: Locale differs, skipping unit tests...', __FILE__, __LINE__, __METHOD__, 1 );
		}

		TTi18n::setLocale( 'es_ES' );
		Debug::Text( 'Thousands Separator: ' . TTi18n::getThousandsSymbol() . ' Decimal Symbol: ' . TTi18n::getDecimalSymbol(), __FILE__, __LINE__, __METHOD__, 1 );
		if ( TTi18n::getThousandsSymbol() == '.' && TTi18n::getDecimalSymbol() == ',' ) {
			$this->assertEquals( '12.345,12', Misc::MoneyFormat( 12345.12345 ) );
			$this->assertEquals( '-12.345,12', Misc::MoneyFormat( -12345.12345 ) );
		} else {
			Debug::Text( 'ERROR: Locale differs, skipping unit tests...', __FILE__, __LINE__, __METHOD__, 1 );
		}
	}

	/**
	 * @group MiscTest_testUnitConvert
	 */
	function testUnitConvert() {
		$this->assertEquals( 1, UnitConvert::convert( 'mm', 'mm', 1 ) );
		$this->assertEquals( 1000, UnitConvert::convert( 'm', 'mm', 1 ) );
		$this->assertEquals( 0.001, UnitConvert::convert( 'mm', 'm', 1 ) );

		$this->assertEquals( 1000, UnitConvert::convert( 'km', 'm', 1 ) );
		$this->assertEquals( 0.001, UnitConvert::convert( 'm', 'km', 1 ) );

		$this->assertEquals( 1609344, UnitConvert::convert( 'mi', 'mm', 1 ) );
		$this->assertEquals( UnitConvert::convert( 'mm', 'mi', 1 ), ( 1 / 1609344 ) );

		$this->assertEquals( 0.62137119223733395, UnitConvert::convert( 'km', 'mi', 1 ) );
		$this->assertEquals( 1.6093439999999999, UnitConvert::convert( 'mi', 'km', 1 ) );
		$this->assertEquals( 0.00062137119223733392, UnitConvert::convert( 'm', 'mi', 1 ) );
	}

	/**
	 * @group MiscTest_testPasswordStrength
	 */
	function testPasswordStrength() {
		//Numbers
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '1' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '12' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( '123' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '1234' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( '12345' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '123456' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '1234567' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( '12345678' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '123456789' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( '1234567890' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '12345678901' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '123456789012' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '1234567890123' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( '12345678901234' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '123456789012345' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( '987654321' ) ); //Backwards

		//Letters
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'a' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'ab' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abc' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcd' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcde' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdef' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefg' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefgh' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefghi' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefghij' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefghijk' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefghijkl' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefghijklm' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefghijklmn' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefghijklmno' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'ihgfedcba' ) ); //Backwards

		//Half letters, half numbers
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'a1' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'ab12' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abc123' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcd1234' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcde12345' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdef123456' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefg1234567' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdefgh12345678' ) );


		//All the same char.
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'aaaaaa' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'aaabbb' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'aaaccc' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '111111' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '111222' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '111333' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '123123' ) );

		//Some what real passwords.
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'test' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'pear' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'orange' ) );

		$this->assertEquals( 2, TTPassword::getPasswordStrength( '!Qa12' ) ); //Unique, but not enough characters to make it difficult.
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '2000' ) );
		$this->assertEquals( 2, TTPassword::getPasswordStrength( '696969' ) );
		$this->assertEquals( 2, TTPassword::getPasswordStrength( 'trustno1' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abababababababab' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcabcabcabcabc' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcdabcdabcdabcd' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abcd.abcd^abcd#abcd' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'abc123' ) );
		$this->assertEquals( 2, TTPassword::getPasswordStrength( 'test123' ) );
		$this->assertEquals( 3, TTPassword::getPasswordStrength( 'admin123' ) );
		$this->assertEquals( 3, TTPassword::getPasswordStrength( 'pear123' ) );
		$this->assertEquals( 3, TTPassword::getPasswordStrength( 'pear1234' ) );
		$this->assertEquals( 3, TTPassword::getPasswordStrength( 'pear12345' ) );
		$this->assertEquals( 4, TTPassword::getPasswordStrength( 'orange123456' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'car123456789' ) );           //Too many consecutive.
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'cars123456789' ) );          //Too many consecutive.
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'orange123456789' ) );        //Too many consecutive.
		$this->assertEquals( 6, TTPassword::getPasswordStrength( 'superabundant123456789' ) ); //Too many consecutive.

		$this->assertEquals( 4, TTPassword::getPasswordStrength( 'cars.8.apple' ) );
		$this->assertEquals( 4, TTPassword::getPasswordStrength( 'cars.8#apple' ) );

		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'password' ) );  //Dictionary word
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'Password' ) );  //Dictionary word
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'password1' ) ); //Dictionary word with one extra char.
		$this->assertEquals( 3, TTPassword::getPasswordStrength( 'password11' ) );
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '1password' ) ); //Dictionary word with one extra char.
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'password!' ) ); //Dictionary word with one extra char.
		$this->assertEquals( 1, TTPassword::getPasswordStrength( '!password' ) ); //Dictionary word with one extra char.
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'qwerty' ) );    //Dictionary word
		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'dragon' ) );    //Dictionary word

		$this->assertEquals( 1, TTPassword::getPasswordStrength( 'superabundant' ) );     //Dictionary word
		$this->assertEquals( 5, TTPassword::getPasswordStrength( 'Super.Abundant#41' ) ); //Dictionary word
		$this->assertEquals( 3, TTPassword::getPasswordStrength( 'pearappleorange' ) );
		$this->assertEquals( 5, TTPassword::getPasswordStrength( 'pear.apple@orange#strawberry' ) );
		$this->assertEquals( 4, TTPassword::getPasswordStrength( 'superabundant123' ) );

		$this->assertEquals( 5, TTPassword::getPasswordStrength( 'Superabundant.123' ) );
		$this->assertEquals( 5, TTPassword::getPasswordStrength( 'Super^91Pear.87' ) );
		$this->assertEquals( 5, TTPassword::getPasswordStrength( 'Super^91Bop.87' ) );

		$this->assertEquals( 7, TTPassword::getPasswordStrength( 'a1j8U4y7K2qA.#@5.' ) );
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
		@unlink( $lock_file_name );

		//Test with default timeout.
		$lock_file = new LockFile( $lock_file_name );
		if ( $lock_file->exists() == false ) {
			$lock_file->create();

			$this->assertGreaterThan( 0, $lock_file->getCurrentPID() );
			$this->assertEquals( true, $lock_file->isPIDRunning( $lock_file->getCurrentPID() ) );
			$this->assertEquals( true, $lock_file->exists() );
		}

		$lock_file->delete();
		$this->assertEquals( false, $lock_file->exists() );

		//Test with really short timeout
		$lock_file = new LockFile( $lock_file_name );
		$lock_file->max_lock_file_age = 1;
		if ( $lock_file->exists() == false ) {
			$lock_file->create();

			Debug::Text( '  Sleeping...', __FILE__, __LINE__, __METHOD__, 10 );
			sleep( 2 );

			$this->assertGreaterThan( 0, $lock_file->getCurrentPID() );
			$this->assertEquals( true, $lock_file->isPIDRunning( $lock_file->getCurrentPID() ) );
			$this->assertEquals( true, $lock_file->exists() );
		}

		$lock_file->delete();
		$this->assertEquals( false, $lock_file->exists() );


		//Test without PID
		$lock_file = new LockFile( $lock_file_name );
		$lock_file->use_pid = false;
		$lock_file->max_lock_file_age = 1;
		if ( $lock_file->exists() == false ) {
			$lock_file->create();

			sleep( 2 );

			$this->assertEquals( false, $lock_file->getCurrentPID() );
			$this->assertEquals( false, $lock_file->isPIDRunning( $lock_file->getCurrentPID() ) );
			$this->assertEquals( false, $lock_file->exists() );
		}

		$lock_file->delete();
		$this->assertEquals( false, $lock_file->exists() );
	}

	/**
	 * @group testRemoteHTTP
	 */
	function testRemoteHTTP() {
		$url = 'www.timetrex.com/blank.html';

		$header_size = (int)Misc::getRemoteHTTPFileSize( 'http://' . $url );
		$this->assertEquals( 30, (int)$header_size ); //30 Bytes.

		$header_size = (int)Misc::getRemoteHTTPFileSize( 'https://' . $url );
		$this->assertEquals( 30, (int)$header_size );                   //30 Bytes.


		$temp_file_name = tempnam( '/tmp/', 'unit_test_http_' );
		$size = Misc::downloadHTTPFile( 'http://' . $url, $temp_file_name );
		//Debug::Text( ' Temp File Name: '. $temp_file_name, __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( (int)$size, (int)$header_size );           //Make sure the downloaded size matches the header size too.
		$this->assertEquals( 30, (int)$size );                          //30 Bytes.
		$this->assertEquals( filesize( $temp_file_name ), (int)$size ); //30 Bytes.
		unlink( $temp_file_name );

		$temp_file_name = tempnam( '/tmp/', 'unit_test_http_' );
		//Debug::Text( ' Temp File Name: '. $temp_file_name, __FILE__, __LINE__, __METHOD__, 10 );
		$size = (int)Misc::downloadHTTPFile( 'https://' . $url, $temp_file_name );
		$this->assertEquals( (int)$size, (int)$header_size );           //Make sure the downloaded size matches the header size too.
		$this->assertEquals( 30, (int)$size );                          //30 Bytes.
		$this->assertEquals( filesize( $temp_file_name ), (int)$size ); //30 Bytes.
		unlink( $temp_file_name );


		//Test downloading to the same file that should already exist from above.
		//Debug::Text( ' Temp File Name: '. $temp_file_name, __FILE__, __LINE__, __METHOD__, 10 );
		$size = (int)Misc::downloadHTTPFile( 'https://' . $url, $temp_file_name );
		$this->assertEquals( (int)$size, (int)$header_size );           //Make sure the downloaded size matches the header size too.
		$this->assertEquals( 30, (int)$size );                          //30 Bytes.
		$this->assertEquals( filesize( $temp_file_name ), (int)$size ); //30 Bytes.
		unlink( $temp_file_name );


		//Test downloading to a directory without permissions, or one that doesn't exist.
		$temp_file_name = '/root' . tempnam( '/tmp/', 'unit_test_http_' );
		Debug::Text( ' Temp File Name: ' . $temp_file_name, __FILE__, __LINE__, __METHOD__, 10 );
		$retval = Misc::downloadHTTPFile( 'https://' . $url, $temp_file_name );

		$this->assertEquals( false, $retval ); //Download should fail without PHP warnings.
		@unlink( $temp_file_name );
	}

	/**
	 * @group testgetAmountToLimit
	 */
	function testgetAmountToLimit() {
		//Positive Amount and Positive Limit, should return amount up to the limit.
		$this->assertEquals( 0, Misc::getAmountToLimit( 0, 100 ) );
		$this->assertEquals( 1, Misc::getAmountToLimit( 1, 100 ) );
		$this->assertEquals( 50, Misc::getAmountToLimit( 50, 100 ) );
		$this->assertEquals( 98, Misc::getAmountToLimit( 98, 100 ) );
		$this->assertEquals( 99, Misc::getAmountToLimit( 99, 100 ) );
		$this->assertEquals( 100, Misc::getAmountToLimit( 100, 100 ) );
		$this->assertEquals( 100, Misc::getAmountToLimit( 101, 100 ) );
		$this->assertEquals( 100, Misc::getAmountToLimit( 200, 100 ) );
		$this->assertEquals( 100, Misc::getAmountToLimit( 201, 100 ) );
		$this->assertEquals( 100, Misc::getAmountToLimit( 1001, 100 ) );

		//Positive Amount and Negative Limit should always return 0
		$this->assertEquals( 0, Misc::getAmountToLimit( 101, -100 ) );
		$this->assertEquals( 0, Misc::getAmountToLimit( 100, -100 ) );
		$this->assertEquals( 0, Misc::getAmountToLimit( 99, -100 ) );
		$this->assertEquals( 0, Misc::getAmountToLimit( 98, -100 ) );
		$this->assertEquals( 0, Misc::getAmountToLimit( 0, -100 ) );

		//Positive amounts and 0 limit should always return the amount.
		$this->assertEquals( 101, Misc::getAmountToLimit( 101, 0 ) );
		$this->assertEquals( 100, Misc::getAmountToLimit( 100, 0 ) );
		$this->assertEquals( 99, Misc::getAmountToLimit( 99, 0 ) );
		$this->assertEquals( 98, Misc::getAmountToLimit( 98, 0 ) );
		$this->assertEquals( 0, Misc::getAmountToLimit( 0, 0 ) );


		//Negative amounts, but positive limits should always return 0.
		$this->assertEquals( 0, Misc::getAmountToLimit( -101, 100 ) );
		$this->assertEquals( 0, Misc::getAmountToLimit( -100, 100 ) );
		$this->assertEquals( 0, Misc::getAmountToLimit( -99, 100 ) );
		$this->assertEquals( 0, Misc::getAmountToLimit( -98, 100 ) );
		$this->assertEquals( 0, Misc::getAmountToLimit( 0, 100 ) );

		//Negative amounts and 0 limit should always return the amount.
		$this->assertEquals( -101, Misc::getAmountToLimit( -101, 0 ) );
		$this->assertEquals( -100, Misc::getAmountToLimit( -100, 0 ) );
		$this->assertEquals( -99, Misc::getAmountToLimit( -99, 0 ) );
		$this->assertEquals( -98, Misc::getAmountToLimit( -98, 0 ) );
		$this->assertEquals( 0, Misc::getAmountToLimit( 0, 0 ) );


		//Negative amounts and negative limits should be treated as "getAmountDownToLimit".
		$this->assertEquals( -100, Misc::getAmountToLimit( -1001, -100 ) );
		$this->assertEquals( -100, Misc::getAmountToLimit( -200, -100 ) );
		$this->assertEquals( -100, Misc::getAmountToLimit( -101, -100 ) );
		$this->assertEquals( -100, Misc::getAmountToLimit( -100, -100 ) );
		$this->assertEquals( -99, Misc::getAmountToLimit( -99, -100 ) );
		$this->assertEquals( -98, Misc::getAmountToLimit( -98, -100 ) );
		$this->assertEquals( -50, Misc::getAmountToLimit( -50, -100 ) );
		$this->assertEquals( -1, Misc::getAmountToLimit( -1, -100 ) );
		$this->assertEquals( -0, Misc::getAmountToLimit( -0, -100 ) );

		//Test non-float/integer limit
		$this->assertEquals( 100, Misc::getAmountToLimit( 100, false ) );
		$this->assertEquals( 100, Misc::getAmountToLimit( 100, true ) );
		$this->assertEquals( 100, Misc::getAmountToLimit( 100, null ) );
		$this->assertEquals( 100, Misc::getAmountToLimit( 100, '' ) );
		$this->assertEquals( -100, Misc::getAmountToLimit( -100, false ) );
		$this->assertEquals( -100, Misc::getAmountToLimit( -100, true ) );
		$this->assertEquals( -100, Misc::getAmountToLimit( -100, null ) );
		$this->assertEquals( -100, Misc::getAmountToLimit( -100, '' ) );

		//Test float/int 0 limit
		$this->assertEquals( 100, Misc::getAmountToLimit( 100, 0 ) );
		$this->assertEquals( -100, Misc::getAmountToLimit( -100, 0 ) );


		//Amount DIff. - Positive Amount and Positive Limit, should return amount up to the limit.
		$this->assertEquals( 300, Misc::getAmountDifferenceToLimit( -200, 100 ) ); //This could be 0, or +300, or 100?
		$this->assertEquals( 101, Misc::getAmountDifferenceToLimit( -1, 100 ) );   //This could be 0, or +101
		$this->assertEquals( 100, Misc::getAmountDifferenceToLimit( 0, 100 ) );
		$this->assertEquals( 99, Misc::getAmountDifferenceToLimit( 1, 100 ) );
		$this->assertEquals( 50, Misc::getAmountDifferenceToLimit( 50, 100 ) );
		$this->assertEquals( 2, Misc::getAmountDifferenceToLimit( 98, 100 ) );
		$this->assertEquals( 1, Misc::getAmountDifferenceToLimit( 99, 100 ) );
		$this->assertEquals( 0, Misc::getAmountDifferenceToLimit( 100, 100 ) );
		$this->assertEquals( 0, Misc::getAmountDifferenceToLimit( 101, 100 ) );
		$this->assertEquals( 0, Misc::getAmountDifferenceToLimit( 200, 100 ) );
		$this->assertEquals( 0, Misc::getAmountDifferenceToLimit( 201, 100 ) );
		$this->assertEquals( 0, Misc::getAmountDifferenceToLimit( 1001, 100 ) );

		//Amount Diff Negative amounts and negative limits should be treated as "getAmountDownToLimit".
		$this->assertEquals( 0, Misc::getAmountDifferenceToLimit( -1001, -100 ) );
		$this->assertEquals( 0, Misc::getAmountDifferenceToLimit( -200, -100 ) );
		$this->assertEquals( 0, Misc::getAmountDifferenceToLimit( -101, -100 ) );
		$this->assertEquals( 0, Misc::getAmountDifferenceToLimit( -100, -100 ) );
		$this->assertEquals( -1, Misc::getAmountDifferenceToLimit( -99, -100 ) );
		$this->assertEquals( -2, Misc::getAmountDifferenceToLimit( -98, -100 ) );
		$this->assertEquals( -50, Misc::getAmountDifferenceToLimit( -50, -100 ) );
		$this->assertEquals( -99, Misc::getAmountDifferenceToLimit( -1, -100 ) );
		$this->assertEquals( -100, Misc::getAmountDifferenceToLimit( -0, -100 ) );
		$this->assertEquals( -100, Misc::getAmountDifferenceToLimit( 0, -100 ) );
		$this->assertEquals( -101, Misc::getAmountDifferenceToLimit( 1, -100 ) );                             //This could be 0, or -101
		$this->assertEquals( -300, Misc::getAmountDifferenceToLimit( 200, -100 ) );                           //This could be 0, or -300

		//When limit is 0, the result should be the opposite sign of the amount. Treated as AmountDifferenceDownToLimit essentially.
		$this->assertEquals( 5000, Misc::getAmountDifferenceToLimit( -5000, 0 ) );
		$this->assertEquals( 50, Misc::getAmountDifferenceToLimit( -50, 0 ) );
		$this->assertEquals( 2, Misc::getAmountDifferenceToLimit( -2, 0 ) );
		$this->assertEquals( 1, Misc::getAmountDifferenceToLimit( -1, 0 ) );
		$this->assertEquals( 0, Misc::getAmountDifferenceToLimit( 0, 0 ) );
		$this->assertEquals( -1, Misc::getAmountDifferenceToLimit( 1, 0 ) );
		$this->assertEquals( -2, Misc::getAmountDifferenceToLimit( 2, 0 ) );
		$this->assertEquals( -50, Misc::getAmountDifferenceToLimit( 50, 0 ) );
		$this->assertEquals( -5000, Misc::getAmountDifferenceToLimit( 5000, 0 ) );

		//Mimic how UserDeduction handles Fixed Amount w/Target for Loan amounts.
		$this->assertEquals( 0, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( 1001, 0 ), 100 ) ); //0 is the amount difference, so the result is 0.
		$this->assertEquals( 0, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( 101, 0 ), 100 ) );
		$this->assertEquals( 0, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( 100, 0 ), 100 ) );
		$this->assertEquals( 0, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( 99, 0 ), 100 ) );
		$this->assertEquals( 0, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( 1, 0 ), 100 ) );
		$this->assertEquals( 0, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( 0, 0 ), 100 ) );
		$this->assertEquals( 1, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( -1, 0 ), 100 ) );
		$this->assertEquals( 99, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( -99, 0 ), 100 ) );
		$this->assertEquals( 100, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( -100, 0 ), 100 ) );
		$this->assertEquals( 100, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( -101, 0 ), 100 ) );
		$this->assertEquals( 100, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( -1001, 0 ), 100 ) );

		$this->assertEquals( 0, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( 1001, 1 ), 100 ) ); //0 is the amount difference, so the result is 0.
		$this->assertEquals( 0, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( 101, 1 ), 100 ) );
		$this->assertEquals( 0, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( 100, 1 ), 100 ) );
		$this->assertEquals( 0, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( 99, 1 ), 100 ) );
		$this->assertEquals( 0, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( 1, 1 ), 100 ) );
		$this->assertEquals( 1, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( 0, 1 ), 100 ) );
		$this->assertEquals( 2, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( -1, 1 ), 100 ) );
		$this->assertEquals( 100, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( -99, 1 ), 100 ) );
		$this->assertEquals( 100, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( -100, 1 ), 100 ) );
		$this->assertEquals( 100, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( -101, 1 ), 100 ) );
		$this->assertEquals( 100, Misc::getAmountToLimit( Misc::getAmountDifferenceToLimit( -1001, 1 ), 100 ) );

		//UserDeduction could possibly uses abs() so a limit of 0 will continue to work if the amount is higher or lower than it.
		// Since the Tax/Deduction record is almost always a Employee Deduction, the resulting amount should always be a positive value.
		// This seems like a way to shoot the user in the foot though as it would allow incorrect setup where balance amount is positive rather than negative to "kinda work"
		$this->assertEquals( 100, Misc::getAmountToLimit( abs( Misc::getAmountDifferenceToLimit( -1001, 0 ) ), 100 ) );
		$this->assertEquals( 100, Misc::getAmountToLimit( abs( Misc::getAmountDifferenceToLimit( -101, 0 ) ), 100 ) );
		$this->assertEquals( 100, Misc::getAmountToLimit( abs( Misc::getAmountDifferenceToLimit( -100, 0 ) ), 100 ) );
		$this->assertEquals( 99, Misc::getAmountToLimit( abs( Misc::getAmountDifferenceToLimit( -99, 0 ) ), 100 ) );
		$this->assertEquals( 1, Misc::getAmountToLimit( abs( Misc::getAmountDifferenceToLimit( -1, 0 ) ), 100 ) );
		$this->assertEquals( 0, Misc::getAmountToLimit( abs( Misc::getAmountDifferenceToLimit( 0, 0 ) ), 100 ) );
		$this->assertEquals( 1, Misc::getAmountToLimit( abs( Misc::getAmountDifferenceToLimit( 1, 0 ) ), 100 ) );
		$this->assertEquals( 99, Misc::getAmountToLimit( abs( Misc::getAmountDifferenceToLimit( 99, 0 ) ), 100 ) );
		$this->assertEquals( 100, Misc::getAmountToLimit( abs( Misc::getAmountDifferenceToLimit( 100, 0 ) ), 100 ) );
		$this->assertEquals( 100, Misc::getAmountToLimit( abs( Misc::getAmountDifferenceToLimit( 101, 0 ) ), 100 ) );
		$this->assertEquals( 100, Misc::getAmountToLimit( abs( Misc::getAmountDifferenceToLimit( 1001, 0 ) ), 100 ) );
	}

	/**
	 * @group testgetAmountAroundLimit
	 */
	function testgetAmountAroundLimit() {
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 100, 'over_limit' => 0 ], Misc::getAmountAroundLimit( 0, 0, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 1, 'under_limit' => 99, 'over_limit' => 0 ], Misc::getAmountAroundLimit( 1, 0, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 50, 'under_limit' => 50, 'over_limit' => 0 ], Misc::getAmountAroundLimit( 50, 0, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 99, 'under_limit' => 1, 'over_limit' => 0 ], Misc::getAmountAroundLimit( 99, 0, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 100, 'under_limit' => 0, 'over_limit' => 0 ], Misc::getAmountAroundLimit( 100, 0, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 100, 'under_limit' => 0, 'over_limit' => 1 ], Misc::getAmountAroundLimit( 101, 0, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 100, 'under_limit' => 0, 'over_limit' => 50 ], Misc::getAmountAroundLimit( 150, 0, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 100, 'under_limit' => 0, 'over_limit' => 101 ], Misc::getAmountAroundLimit( 201, 0, 100 ) );

		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 90, 'over_limit' => 0 ], Misc::getAmountAroundLimit( 0, 10, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 1, 'under_limit' => 89, 'over_limit' => 0 ], Misc::getAmountAroundLimit( 1, 10, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 1, 'under_limit' => 49, 'over_limit' => 0 ], Misc::getAmountAroundLimit( 1, 50, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 1, 'under_limit' => 2, 'over_limit' => 0 ], Misc::getAmountAroundLimit( 1, 97, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 1, 'under_limit' => 1, 'over_limit' => 0 ], Misc::getAmountAroundLimit( 1, 98, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 1, 'under_limit' => 0, 'over_limit' => 0 ], Misc::getAmountAroundLimit( 1, 99, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => 1 ], Misc::getAmountAroundLimit( 1, 100, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => 1 ], Misc::getAmountAroundLimit( 1, 101, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => 5 ], Misc::getAmountAroundLimit( 1, 105, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => 100 ], Misc::getAmountAroundLimit( 1, 200, 100 ) );

		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 10, 'over_limit' => 0 ], Misc::getAmountAroundLimit( 0, 90, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 1, 'under_limit' => 9, 'over_limit' => 0 ], Misc::getAmountAroundLimit( 1, 90, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 8, 'under_limit' => 2, 'over_limit' => 0 ], Misc::getAmountAroundLimit( 8, 90, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 9, 'under_limit' => 1, 'over_limit' => 0 ], Misc::getAmountAroundLimit( 9, 90, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 10, 'under_limit' => 0, 'over_limit' => 0 ], Misc::getAmountAroundLimit( 10, 90, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 10, 'under_limit' => 0, 'over_limit' => 1 ], Misc::getAmountAroundLimit( 11, 90, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 10, 'under_limit' => 0, 'over_limit' => 2 ], Misc::getAmountAroundLimit( 12, 90, 100 ) );
		$this->assertEquals( [ 'adjusted_amount' => 10, 'under_limit' => 0, 'over_limit' => 40 ], Misc::getAmountAroundLimit( 50, 90, 100 ) );


		//Test an example that may be used in a report to ensure the YTD amount never exceeds the limit.
		$ytd_amount = 0;

		$tmp_amount_around_limit_arr = Misc::getAmountAroundLimit( 0, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 100, 'over_limit' => 0 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 0, $ytd_amount );

		$tmp_amount_around_limit_arr = Misc::getAmountAroundLimit( 10, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 10, 'under_limit' => 90, 'over_limit' => 0 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 10, $ytd_amount );

		$tmp_amount_around_limit_arr = Misc::getAmountAroundLimit( 50, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 50, 'under_limit' => 40, 'over_limit' => 0 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 60, $ytd_amount );

		$tmp_amount_around_limit_arr = Misc::getAmountAroundLimit( 30, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 30, 'under_limit' => 10, 'over_limit' => 0 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 90, $ytd_amount );

		$tmp_amount_around_limit_arr = Misc::getAmountAroundLimit( 9, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 9, 'under_limit' => 1, 'over_limit' => 0 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 99, $ytd_amount );

		$tmp_amount_around_limit_arr = Misc::getAmountAroundLimit( 1, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 1, 'under_limit' => 0, 'over_limit' => 0 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 100, $ytd_amount );

		$tmp_amount_around_limit_arr = Misc::getAmountAroundLimit( 1, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => 1 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 100, $ytd_amount );

		$tmp_amount_around_limit_arr = Misc::getAmountAroundLimit( 99, $ytd_amount, 100 );
		$this->assertEquals( [ 'adjusted_amount' => 0, 'under_limit' => 0, 'over_limit' => 99 ], $tmp_amount_around_limit_arr );
		$ytd_amount += $tmp_amount_around_limit_arr['adjusted_amount'];
		$this->assertEquals( 100, $ytd_amount );
	}

	/**
	 * @group testMoneyRoundDifference
	 */
	function testMoneyRoundDifference() {
		$this->assertEquals( 0.00, Misc::MoneyRoundDifference( '100.01', 2 ) );
		$this->assertEquals( -0.001, Misc::MoneyRoundDifference( '100.011', 2 ) ); //Rounded Value=100.01, Different is -0.001
		$this->assertEquals( 0.005, Misc::MoneyRoundDifference( '100.015', 2 ) );  //Rounded Value=100.02, Different is -0.001
		$this->assertEquals( 0.001, Misc::MoneyRoundDifference( '100.019', 2 ) );  //Rounded Value=100.02, Different is -0.001
		$this->assertEquals( 0.0000001, Misc::MoneyRoundDifference( '100.0199999', 2 ) );
	}

	/**
	 * @group testIsSubDirectory
	 */
	function testIsSubDirectory() {
		$parent_dir = '/';
		$child_dir = '/var';
		$this->assertEquals( true, Misc::isSubDirectory( $child_dir, $parent_dir ) );

		$parent_dir = '/var';
		$child_dir = '/usr';
		$this->assertEquals( false, Misc::isSubDirectory( $child_dir, $parent_dir ) );

		$parent_dir = '/var';
		$child_dir = '/usr/';
		$this->assertEquals( false, Misc::isSubDirectory( $child_dir, $parent_dir ) );

		$parent_dir = '/var/';
		$child_dir = '/usr/';
		$this->assertEquals( false, Misc::isSubDirectory( $child_dir, $parent_dir ) );

		//Test with directories that do not exist.
		$parent_dir = '/var/www/TimeTrex556688';
		$child_dir = '/var/www/TimeTrex556688Test';
		$this->assertEquals( false, Misc::isSubDirectory( $child_dir, $parent_dir ) );

		$parent_dir = '/var/www/TimeTrex556688/';
		$child_dir = '/var/www/TimeTrex556688Test/';
		$this->assertEquals( false, Misc::isSubDirectory( $child_dir, $parent_dir ) );


		$parent_dir = '/var/www/TimeTrex556688Test';
		$child_dir = '/var/www/TimeTrex556688';
		$this->assertEquals( false, Misc::isSubDirectory( $child_dir, $parent_dir ) );

		$parent_dir = '/var/www/TimeTrex556688';
		$child_dir = '/var/www/TimeTrex556688/storage';
		$this->assertEquals( true, Misc::isSubDirectory( $child_dir, $parent_dir ) );

		$parent_dir = '/var/www/TimeTrex556688/';
		$child_dir = '/var/www/TimeTrex556688/storage/';
		$this->assertEquals( true, Misc::isSubDirectory( $child_dir, $parent_dir ) );

		//This directory should exist for this test to be accurate.
		$parent_dir = '/etc/cron.d';
		$child_dir = '/etc/cron.daily';
		$this->assertEquals( false, Misc::isSubDirectory( $child_dir, $parent_dir ) );
	}

	/**
	 * @group testSOAPClient
	 */
	function testSOAPClient() {
		$ttsc = TTnew( 'TimeTrexSoapClient' ); /** @var TimeTrexSoapClient $ttsc */
		$this->assertEquals( true, $ttsc->ping() );
	}

	/**
	 * @group testCensorString
	 */
	function testCensorString() {
		$this->assertEquals( '*', Misc::censorString( '0' ) );
		$this->assertEquals( '**', Misc::censorString( '00' ) );
		$this->assertEquals( '0*0', Misc::censorString( '000' ) );
		$this->assertEquals( '0**0', Misc::censorString( '0000' ) );
		$this->assertEquals( '0***0', Misc::censorString( '00000' ) );
		$this->assertEquals( '00**00', Misc::censorString( '000000' ) );
		$this->assertEquals( '00***00', Misc::censorString( '0000000' ) );
		$this->assertEquals( '00****00', Misc::censorString( '00000000' ) );
		$this->assertEquals( '000***000', Misc::censorString( '000000000' ) );
		$this->assertEquals( '123***789', Misc::censorString( '123456789' ) );
		$this->assertEquals( '123456********567890', Misc::censorString( '12345678901234567890' ) );

		//censorString( $str, $censor_char = '*', $min_first_chunk_size = NULL, $ma*_first_chunk_size = NULL, $min_last_chunk_size = NULL, $ma*_last_chunk_size = NULL )
		$this->assertEquals( '4111********4444', Misc::censorString( '4111222233334444', '*', 4, 4, 4, 4 ) );

		$uf = TTnew( 'UserFactory' ); /** @var UserFactory $uf */
		$this->assertEquals( '*', $uf->getSecureSIN( '0' ) );
		$this->assertEquals( '**', $uf->getSecureSIN( '00' ) );
		$this->assertEquals( '***', $uf->getSecureSIN( '000' ) );
		$this->assertEquals( '****', $uf->getSecureSIN( '0000' ) );
		$this->assertEquals( '*****', $uf->getSecureSIN( '00000' ) );
		$this->assertEquals( '******', $uf->getSecureSIN( '000000' ) );
		$this->assertEquals( '0**0000', $uf->getSecureSIN( '0000000' ) );
		$this->assertEquals( '0***0000', $uf->getSecureSIN( '00000000' ) );
		$this->assertEquals( '0****0000', $uf->getSecureSIN( '000000000' ) );
		$this->assertEquals( '1****6789', $uf->getSecureSIN( '123456789' ) );
	}

	/**
	 * @group testUUID
	 */
	function testUUID() {
		//Make sure UUIDs are unique at least across 1 million tight iterations.
		$max = 1000000;
		for ( $i = 0; $i < $max; $i++ ) {
			$uuid_arr[] = TTUUID::generateUUID();
		}
		$unique_uuid_arr = array_unique( $uuid_arr );

		$this->assertSameSize( $uuid_arr, $unique_uuid_arr );
		unset( $uuid_arr, $unique_uuid_arr );
	}

	/**
	 * @group testTruncateUUID
	 */
	function testTruncateUUID() {
		//Make sure UUIDs converted from INTs still get the most unique UUID data first.
		$this->assertEquals( '000000192136', TTUUID::truncateUUID( TTUUID::getConversionPrefix() . '-000000192136', 12, false ) );
		$this->assertEquals( '000000191922', TTUUID::truncateUUID( TTUUID::getConversionPrefix() . '-000000191922', 12, false ) );
		$this->assertEquals( '9af47bc0af20', TTUUID::truncateUUID( '11e7b349-9af4-7bc0-af20-999999191922', 12, false ) );
		$this->assertEquals( '24dc7bc0af20', TTUUID::truncateUUID( '11e7b349-24dc-7bc0-af20-21ea65522ba3', 12, false ) );
		$this->assertEquals( '9af4e9e0b077', TTUUID::truncateUUID( '11e7a84a-9af4-e9e0-b077-21ea65522ba3', 12, false ) );
		$this->assertEquals( '9af4-e9e0-b0', TTUUID::truncateUUID( '11e7a84a-9af4-e9e0-b077-21ea65522ba3', 12, true ) );
		$this->assertEquals( '9af4-e9e0-b077', TTUUID::truncateUUID( '11e7a84a-9af4-e9e0-b077-21ea65522ba3', 15, true ) ); //Only 14 chars due to trailing dash being removed.
	}

	/**
	 * @group testParsingUUID
	 */
	function testParsingUUID() {
		//Make sure UUIDs converted from INTs still get the most unique UUID data first.
		$this->assertEquals( '11e7b349-9af4-7bc0-af20-999999191922', TTUUID::castUUID( ' 11e7b349-9af4-7bc0-af20-999999191922 ' ) );
		$this->assertEquals( '11e7b349-9af4-7bc0-af20-999999191922', TTUUID::castUUID( '11e7b349-9af4-7bc0-af20-999999191922' ) );
		/** @noinspection PhpParamsInspection */
		$this->assertEquals( '00000000-0000-0000-0000-000000000000', TTUUID::castUUID( [ '11e7b349-9af4-7bc0-af20-999999191922' ] ) );
		$this->assertEquals( '00000000-0000-0000-0000-000000000000', TTUUID::castUUID( '' ) );
		$this->assertEquals( null, TTUUID::castUUID( null, true ) );                                    //Allow NULLs
		$this->assertEquals( '00000000-0000-0000-0000-000000000000', TTUUID::castUUID( null, false ) ); //Don't allow NULLs
		$this->assertEquals( '00000000-0000-0000-0000-000000000000', TTUUID::castUUID( false ) );
		$this->assertEquals( '00000000-0000-0000-0000-000000000000', TTUUID::castUUID( true ) );
		$this->assertEquals( '00000000-0000-0000-0000-000000000000', TTUUID::castUUID( 0 ) );
		$this->assertEquals( '00000000-0000-0000-0000-000000000000', TTUUID::castUUID( '0' ) );

		$this->assertEquals( true, TTUUID::isUUID( '11e7b349-9af4-7bc0-af20-999999191922' ) );
		/** @noinspection PhpParamsInspection */
		$this->assertEquals( false, TTUUID::isUUID( [ '11e7b349-9af4-7bc0-af20-999999191922' ] ) );
		$this->assertEquals( false, TTUUID::isUUID( ' 11e7b349-9af4-7bc0-af20-999999191922 ' ) ); //This is not trimmed as it has to be able to go straight into PostgreSQL without complaint.


		global $PRIMARY_KEY_IS_UUID;
		$tmp_primary_key_is_uuid = $PRIMARY_KEY_IS_UUID; //Save current UUID key setting.

		$PRIMARY_KEY_IS_UUID = false;
		$this->assertEquals( 0, TTUUID::castUUID( '' ) );
		$this->assertEquals( null, TTUUID::castUUID( null, true ) ); //Allow NULLs
		$this->assertEquals( 0, TTUUID::castUUID( null, false ) );   //Don't allow NULLs
		$this->assertEquals( 0, TTUUID::castUUID( false ) );
		$this->assertEquals( 1, TTUUID::castUUID( true ) );
		$this->assertEquals( 0, TTUUID::castUUID( 0 ) );
		$this->assertEquals( 0, TTUUID::castUUID( '0' ) );
		$this->assertEquals( 0, TTUUID::castUUID( 'a1e7b349-9af4-7bc0-af20-999999191922' ) );
		$this->assertEquals( 110000000, TTUUID::castUUID( '11e7b349-9af4-7bc0-af20-999999191922' ) ); //PHP quirk in casting to int because it starts with a digit.
		$this->assertEquals( 123456789, TTUUID::castUUID( 123456789 ) );
		$this->assertEquals( 123456789, TTUUID::castUUID( '123456789' ) );

		$PRIMARY_KEY_IS_UUID = $tmp_primary_key_is_uuid; //Restore original UUID key setting.
	}

	/**
	 * @group testUUIDSorting
	 */
	function testUUIDSorting() {
		//Make sure UUIDs can be sorted and appear in time order as they were created.
		$max = 10000;
		for ( $i = 0; $i < $max; $i++ ) {
			$uuid_arr[] = TTUUID::generateUUID();
		}
		$sorted_uuid_arr = $uuid_arr;

		sort( $sorted_uuid_arr );
		$diff_uuid_arr = array_diff_assoc( $uuid_arr, $sorted_uuid_arr );
		$this->assertCount( 0, $diff_uuid_arr );

		//Reverse the sort and confirm all differences.
		rsort( $sorted_uuid_arr );
		$diff_uuid_arr = array_diff_assoc( $uuid_arr, $sorted_uuid_arr );
		$this->assertEquals( count( $diff_uuid_arr ), $max );

		//Use a strcmp sort and confirm it still is in the correct order.
		usort( $sorted_uuid_arr, 'strcmp' );
		$diff_uuid_arr = array_diff_assoc( $uuid_arr, $sorted_uuid_arr );
		$this->assertCount( 0, $diff_uuid_arr );

		//Natural sort will be the wrong order and therefore have many differences.
		usort( $sorted_uuid_arr, 'strnatcasecmp' );
		$diff_uuid_arr = array_diff_assoc( $uuid_arr, $sorted_uuid_arr );
		$this->assertGreaterThan( 0, count( $diff_uuid_arr ) );

		unset( $uuid_arr, $sorted_uuid_arr, $diff_uuid_arr );
	}

	/**
	 * @group testStringToUUID
	 */
	function testStringToUUID() {
		$this->assertEquals( 'ffffffff-ffff-ffff-ffff-ffffffffffff', TTUUID::convertStringToUUID( false ) );
		$this->assertEquals( 'ffffffff-ffff-ffff-ffff-ffffffffffff', TTUUID::convertStringToUUID( null ) );
		$this->assertEquals( 'ffffffff-ffff-ffff-ffff-ffffffffffff', TTUUID::convertStringToUUID( '') );
		$this->assertEquals( 'ffffffff-ffff-ffff-ffff-fffffffffff1', TTUUID::convertStringToUUID( '1') );
		$this->assertEquals( 'ffffffff-ffff-ffff-ffff-123456789012', TTUUID::convertStringToUUID( '123456789012') );

		$this->assertEquals( '12345678-9012-3456-7890-123456789012', TTUUID::convertStringToUUID( '12345678901234567890123456789012') );
		$this->assertEquals( '12345678-9012-3456-7890-123456789012', TTUUID::convertStringToUUID( '12345678901234567890123456789012ZZZZZZ') );
	}

	/**
	 * @group testRemoveTrailingZeros
	 */
	function testRemoveTrailingZeros() {
		TTi18n::setLocale( 'en_US' );

		$this->assertEquals( 12.9, Misc::removeTrailingZeros( 12.9 ) );
		$this->assertEquals( '12.90', Misc::removeTrailingZeros( 12.90 ) );
		$this->assertEquals( '12.90', Misc::removeTrailingZeros( 12.900 ) );
		$this->assertEquals( '12.90', Misc::removeTrailingZeros( 12.9000 ) );
		$this->assertEquals( '12.900', Misc::removeTrailingZeros( 12.9000, 3 ) );

		$this->assertEquals( -12.9, Misc::removeTrailingZeros( -12.9 ) );
		$this->assertEquals( '-12.90', Misc::removeTrailingZeros( -12.90 ) );
		$this->assertEquals( '-12.90', Misc::removeTrailingZeros( -12.900 ) );
		$this->assertEquals( '-12.90', Misc::removeTrailingZeros( -12.9000 ) );
		$this->assertEquals( '-12.900', Misc::removeTrailingZeros( -12.9000, 3 ) );

		$this->assertEquals( '12.90', Misc::removeTrailingZeros( '12.9' ) );
		$this->assertEquals( '12.90', Misc::removeTrailingZeros( '12.90' ) );
		$this->assertEquals( '12.90', Misc::removeTrailingZeros( '12.900' ) );
		$this->assertEquals( '12.90', Misc::removeTrailingZeros( '12.9000' ) );
		$this->assertEquals( '12.900', Misc::removeTrailingZeros( '12.9000', 3 ) );

		$this->assertEquals( '-12.90', Misc::removeTrailingZeros( '-12.9' ) );
		$this->assertEquals( '-12.90', Misc::removeTrailingZeros( '-12.90' ) );
		$this->assertEquals( '-12.90', Misc::removeTrailingZeros( '-12.900' ) );
		$this->assertEquals( '-12.90', Misc::removeTrailingZeros( '-12.9000' ) );
		$this->assertEquals( '-12.900', Misc::removeTrailingZeros( '-12.9000', 3 ) );

		$this->assertEquals( '12.90', Misc::removeTrailingZeros( TTi18n::parseFloat( '12,9', 1 ) ) );
		$this->assertEquals( '12.90', Misc::removeTrailingZeros( TTi18n::parseFloat( '12,90', 2 ) ) );
		$this->assertEquals( '12.90', Misc::removeTrailingZeros( TTi18n::parseFloat( '12,900', 3 ) ) );
		$this->assertEquals( '12.90', Misc::removeTrailingZeros( TTi18n::parseFloat( '12,9000', 4 ) ) );

		TTi18n::setLocale( 'es_ES' );
		if ( TTi18n::getThousandsSymbol() == '.' && TTi18n::getDecimalSymbol() == ',' ) {
			$this->assertEquals( 12.9, Misc::removeTrailingZeros( 12.9 ) );
			$this->assertEquals( '12.90', Misc::removeTrailingZeros( 12.90 ) );
			$this->assertEquals( '12.90', Misc::removeTrailingZeros( 12.900 ) );
			$this->assertEquals( '12.90', Misc::removeTrailingZeros( 12.9000 ) );
			$this->assertEquals( '12.900', Misc::removeTrailingZeros( 12.9000, 3 ) );

			$this->assertEquals( -12.9, Misc::removeTrailingZeros( -12.9 ) );
			$this->assertEquals( '-12.90', Misc::removeTrailingZeros( -12.90 ) );
			$this->assertEquals( '-12.90', Misc::removeTrailingZeros( -12.900 ) );
			$this->assertEquals( '-12.90', Misc::removeTrailingZeros( -12.9000 ) );
			$this->assertEquals( '-12.900', Misc::removeTrailingZeros( -12.9000, 3 ) );

			$this->assertEquals( '12.90', Misc::removeTrailingZeros( '12.9' ) );
			$this->assertEquals( '12.90', Misc::removeTrailingZeros( '12.90' ) );
			$this->assertEquals( '12.90', Misc::removeTrailingZeros( '12.900' ) );
			$this->assertEquals( '12.90', Misc::removeTrailingZeros( '12.9000' ) );
			$this->assertEquals( '12.900', Misc::removeTrailingZeros( '12.9000', 3 ) );

			$this->assertEquals( '-12.90', Misc::removeTrailingZeros( '-12.9' ) );
			$this->assertEquals( '-12.90', Misc::removeTrailingZeros( '-12.90' ) );
			$this->assertEquals( '-12.90', Misc::removeTrailingZeros( '-12.900' ) );
			$this->assertEquals( '-12.90', Misc::removeTrailingZeros( '-12.9000' ) );
			$this->assertEquals( '-12.900', Misc::removeTrailingZeros( '-12.9000', 3 ) );

			$this->assertEquals( '12.90', Misc::removeTrailingZeros( TTi18n::parseFloat( '12,9', 1 ) ) );
			$this->assertEquals( '12.90', Misc::removeTrailingZeros( TTi18n::parseFloat( '12,90', 2 ) ) );
			$this->assertEquals( '12.90', Misc::removeTrailingZeros( TTi18n::parseFloat( '12,900', 3 ) ) );
			$this->assertEquals( '12.90', Misc::removeTrailingZeros( TTi18n::parseFloat( '12,9000', 4 ) ) );

			$this->assertEquals( '12.90', Misc::removeTrailingZeros( TTi18n::parseFloat( '12,9', 1 ) ) );
			$this->assertEquals( '12.90', Misc::removeTrailingZeros( TTi18n::parseFloat( '12,90', 2 ) ) );
			$this->assertEquals( '12.90', Misc::removeTrailingZeros( TTi18n::parseFloat( '12,900', 3 ) ) );
			$this->assertEquals( '12.90', Misc::removeTrailingZeros( TTi18n::parseFloat( '12,9000', 4 ) ) );
		}
	}

	/**
	 * @group testMoneyRound
	 */
	function testMoneyRound() {
		$this->assertEquals( 1.12, Misc::MoneyRound( 1.1234, 2 ) );
		$this->assertEquals( 1.12, Misc::MoneyRound( 1.12456, 2 ) );
		$this->assertEquals( 1.13, Misc::MoneyRound( 1.1256, 2 ) );
		$this->assertEquals( 1234567890.15, Misc::MoneyRound( 1234567890.145, 2 ) );
		$this->assertEquals( 1234567890123456780.15, Misc::MoneyRound( 1234567890123456780.145, 2 ) );
		$this->assertEquals( 1234567890123456789123456789.15, Misc::MoneyRound( 1234567890123456789123456789.145, 2 ) );
		$this->assertEquals( 1000000000000000000000000000000000.15, Misc::MoneyRound( 1000000000000000000000000000000000.145, 2 ) );

		$currency_obj = new CurrencyFactory();
		$currency_obj->setRoundDecimalPlaces( 3 );

		$this->assertEquals( 1.123, Misc::MoneyRound( 1.1234, null, $currency_obj ) );
		$this->assertEquals( 1.124, Misc::MoneyRound( 1.12444, null, $currency_obj ) );
		$this->assertEquals( 1.126, Misc::MoneyRound( 1.1256, null, $currency_obj ) );

		$this->assertEquals( 1.123, Misc::MoneyRound( 1.1234, 2, $currency_obj ) );
		$this->assertEquals( 1.124, Misc::MoneyRound( 1.12444, 2, $currency_obj ) );
		$this->assertEquals( 1.126, Misc::MoneyRound( 1.1256, 2, $currency_obj ) );
	}

	/**
	 * @group testInArrayKey
	 */
	function testOptionGetByValue() {
		$options = [
				10 => 'test1',
				20 => 'Test2',
				30 => 'TEST3',
		];

		$this->assertEquals( 10, Option::getByValue( 'test1', $options ) );
		$this->assertEquals( 10, Option::getByValue( 'Test1', $options ) ); //Test case insensitive match
		$this->assertEquals( 10, Option::getByValue( 'TEST1', $options ) ); //Test case insensitive match

		$this->assertEquals( 20, Option::getByValue( 'Test2', $options ) );
		$this->assertEquals( 20, Option::getByValue( 'test2', $options ) ); //Test case insensitive match
		$this->assertEquals( 20, Option::getByValue( 'TEST2', $options ) ); //Test case insensitive match

		$this->assertEquals( 30, Option::getByValue( 'TEST3', $options ) );
	}

	/**
	 * @group testFloatComparison
	 */
	function testFloatComparison() {
		$float1 = (float)845.92;
		$float2 = (float)14.3;
		$float3 = (float)860.22;
		$added_floats = ( $float1 + $float2 ); //860.22

		if ( $added_floats == $float3 ) {
			$this->assertTrue( false ); //This is to show the float comparison problem. Actual value should be opposite of this.
		} else {
			$this->assertTrue( true );
		}

		if ( $added_floats >= $float3 ) {
			$this->assertTrue( false ); //This is to show the float comparison problem. Actual value should be opposite of this.
		} else {
			$this->assertTrue( true );
		}

		$this->assertEquals( 0, bccomp( $added_floats, $float3 ) );        //0=Equal
		$this->assertEquals( 0, bccomp( $added_floats, (float)860.22 ) );  //0=Equal
		$this->assertEquals( 1, bccomp( $added_floats, (float)860.21 ) );  //1=Greater Than
		$this->assertEquals( -1, bccomp( $added_floats, (float)860.23 ) ); //-1=Less Than

		$this->assertEquals( true, Misc::compareFloat( $added_floats, $float3, '==' ) );
		$this->assertEquals( true, Misc::compareFloat( $added_floats, (float)860.22, '==' ) );
		$this->assertEquals( false, Misc::compareFloat( $added_floats, (float)860.21, '==' ) );

		$this->assertEquals( true, Misc::compareFloat( $added_floats, (float)860.22, '>=' ) );
		$this->assertEquals( true, Misc::compareFloat( $added_floats, (float)860.21, '>=' ) );
		$this->assertEquals( true, Misc::compareFloat( $added_floats, (float)860.01, '>=' ) );

		$this->assertEquals( true, Misc::compareFloat( $added_floats, (float)860.22, '<=' ) );
		$this->assertEquals( true, Misc::compareFloat( $added_floats, (float)860.23, '<=' ) );
		$this->assertEquals( true, Misc::compareFloat( $added_floats, (float)860.33, '<=' ) );

		$this->assertEquals( false, Misc::compareFloat( $added_floats, (float)860.22, '>' ) );
		$this->assertEquals( true, Misc::compareFloat( $added_floats, (float)860.21, '>' ) );
		$this->assertEquals( true, Misc::compareFloat( $added_floats, (float)860.01, '>' ) );

		$this->assertEquals( false, Misc::compareFloat( $added_floats, (float)860.22, '<' ) );
		$this->assertEquals( true, Misc::compareFloat( $added_floats, (float)860.23, '<' ) );
		$this->assertEquals( true, Misc::compareFloat( $added_floats, (float)860.33, '<' ) );
	}

	/**
	 * @group testStripDuplicateSlashes
	 */
	function testStripDuplicateSlashes() {
		$this->assertEquals( 'http://www.domain.com/test/test2/test3/api.php', Environment::stripDuplicateSlashes( 'http://www.domain.com//test//test2//test3/api.php' ) );
		$this->assertEquals( 'www.domain.com/test/test2/test3/api.php', Environment::stripDuplicateSlashes( 'www.domain.com//test//test2//test3/api.php' ) );
		$this->assertEquals( '/api/json/api.php', Environment::stripDuplicateSlashes( '/api//json//api.php' ) );
		$this->assertEquals( '/api/json/api.php', Environment::stripDuplicateSlashes( '//api//json//api.php' ) );
		$this->assertEquals( '/api/json/api.php', Environment::stripDuplicateSlashes( '//////api///////json//////api.php' ) );
	}

	/**
	 * @group testAuthenticationParseEndPointAPI
	 */
	function testAuthenticationParseEndPointAPI() {
		global $config_vars;
		define( 'TIMETREX_JSON_API', true ); //Need to have at least API define() set.

		$authentication = new Authentication;

		$config_vars['path']['base_url'] = '/interface';
		$this->assertEquals( 'json/api', $authentication->parseEndPointID( '/api/json/api.php' ) );
		$this->assertEquals( 'soap/api', $authentication->parseEndPointID( '/api/soap/api.php' ) );
		$this->assertEquals( 'report/api', $authentication->parseEndPointID( '/api/report/api.php' ) );
		$this->assertEquals( 'time_clock/api', $authentication->parseEndPointID( '/api/time_clock/api.php' ) );
		$this->assertEquals( 'soap/server', $authentication->parseEndPointID( '/soap/server.php' ) );

		$config_vars['path']['base_url'] = '/interface/';
		$this->assertEquals( 'json/api', $authentication->parseEndPointID( '/api/json/api.php' ) );
		$this->assertEquals( 'soap/api', $authentication->parseEndPointID( '/api/soap/api.php' ) );
		$this->assertEquals( 'report/api', $authentication->parseEndPointID( '/api/report/api.php' ) );
		$this->assertEquals( 'time_clock/api', $authentication->parseEndPointID( '/api/time_clock/api.php' ) );
		$this->assertEquals( 'soap/server', $authentication->parseEndPointID( '/soap/server.php' ) );

		$config_vars['path']['base_url'] = '/interface//';
		$this->assertEquals( 'json/api', $authentication->parseEndPointID( '/api/json/api.php' ) );
		$this->assertEquals( 'soap/api', $authentication->parseEndPointID( '/api/soap/api.php' ) );
		$this->assertEquals( 'report/api', $authentication->parseEndPointID( '/api/report/api.php' ) );
		$this->assertEquals( 'time_clock/api', $authentication->parseEndPointID( '/api/time_clock/api.php' ) );
		$this->assertEquals( 'soap/server', $authentication->parseEndPointID( '/soap/server.php' ) );

		$config_vars['path']['base_url'] = '//interface//';
		$this->assertEquals( 'json/api', $authentication->parseEndPointID( '/api/json/api.php' ) );
		$this->assertEquals( 'soap/api', $authentication->parseEndPointID( '/api/soap/api.php' ) );
		$this->assertEquals( 'report/api', $authentication->parseEndPointID( '/api/report/api.php' ) );
		$this->assertEquals( 'time_clock/api', $authentication->parseEndPointID( '/api/time_clock/api.php' ) );
		$this->assertEquals( 'soap/server', $authentication->parseEndPointID( '/soap/server.php' ) );

		$config_vars['path']['base_url'] = '/interface//////';
		$this->assertEquals( 'json/api', $authentication->parseEndPointID( '/api/json/api.php' ) );
		$this->assertEquals( 'soap/api', $authentication->parseEndPointID( '/api/soap/api.php' ) );
		$this->assertEquals( 'report/api', $authentication->parseEndPointID( '/api/report/api.php' ) );
		$this->assertEquals( 'time_clock/api', $authentication->parseEndPointID( '/api/time_clock/api.php' ) );
		$this->assertEquals( 'soap/server', $authentication->parseEndPointID( '/soap/server.php' ) );

		$config_vars['path']['base_url'] = '//////interface//////';
		$this->assertEquals( 'json/api', $authentication->parseEndPointID( '/api/json/api.php' ) );
		$this->assertEquals( 'soap/api', $authentication->parseEndPointID( '/api/soap/api.php' ) );
		$this->assertEquals( 'report/api', $authentication->parseEndPointID( '/api/report/api.php' ) );
		$this->assertEquals( 'time_clock/api', $authentication->parseEndPointID( '/api/time_clock/api.php' ) );
		$this->assertEquals( 'soap/server', $authentication->parseEndPointID( '/soap/server.php' ) );

		$config_vars['path']['base_url'] = '/timetrex/interface';
		$this->assertEquals( 'json/api', $authentication->parseEndPointID( '/timetrex//api/json/api.php' ) );
		$this->assertEquals( 'soap/api', $authentication->parseEndPointID( '/timetrex//api/soap/api.php' ) );
		$this->assertEquals( 'report/api', $authentication->parseEndPointID( '/timetrex//api/report/api.php' ) );
		$this->assertEquals( 'time_clock/api', $authentication->parseEndPointID( '/timetrex//api/time_clock/api.php' ) );
	}

	/**
	 * @group testAuthenticationParseEndPointLegacySOAP
	 */
	function testAuthenticationParseEndPointLegacySOAP() {
		global $config_vars;
		define( 'TIMETREX_LEGACY_SOAP_API', true ); //Its possible TIMETREX_JSON_API is still defined when this run, if the above function runs first.

		$authentication = new Authentication;

		$config_vars['path']['base_url'] = '/interface';
		$this->assertEquals( 'soap/server', $authentication->parseEndPointID( '/soap/server.php' ) );

		$config_vars['path']['base_url'] = '/timetrex/interface';
		$this->assertEquals( 'soap/server', $authentication->parseEndPointID( '/timetrex//soap/server.php' ) );
	}

	/**
	 * @group testHumanSizeToBytes
	 */
	function testHumanSizeToBytes() {
		$this->assertEquals( -1, convertHumanSizeToBytes( '-1' ) );
		$this->assertEquals( 1, convertHumanSizeToBytes( '1' ) );
		$this->assertEquals( 1000, convertHumanSizeToBytes( '1000' ) );
		$this->assertEquals( 1000, convertHumanSizeToBytes( '1K' ) );
		$this->assertEquals( 1000000, convertHumanSizeToBytes( '1M' ) );
		$this->assertEquals( 1000000000, convertHumanSizeToBytes( '1G' ) );
	}
}

?>

