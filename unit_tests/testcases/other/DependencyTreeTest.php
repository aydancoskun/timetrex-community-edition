<?php /** @noinspection PhpMissingDocCommentInspection */
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
 * @group DependencyTree
 */
class DependencyTreeTest extends PHPUnit\Framework\TestCase {
	public function setUp(): void {
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );
		require_once( Environment::getBasePath() . '/classes/modules/core/DependencyTree.class.php' );
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	function indexOf( $tofind, $arr ) {
		foreach ( $arr as $k => $v ) {
			if ( $tofind == $v ) {
				return $k;
			}
		}

		return -1;
	}

	function testSimple_1() {
		//Unit Test 1 - Simple
		$deptree = new DependencyTree();
		$deptree->addNode( 'A-1', [ 8 ], [ 10 ] );
		$deptree->addNode( 'A-2', [ 10 ], [ 12 ] );
		$deptree->addNode( 'A-3', [ 12 ], [ 13 ] );

		$result = $deptree->_buildTree();

		//$should_match = [
		//		0 => 'A-1',
		//		1 => 'A-2',
		//		2 => 'A-3',
		//];

		$test1 = $this->indexOf( 'A-1', $result ) < $this->indexOf( 'A-2', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test2 = $this->indexOf( 'A-2', $result ) < $this->indexOf( 'A-3', $result ) ? true : false;
		$this->assertEquals( true, $test2 );

		$test3 = $this->indexOf( 'A-3', $result ) == 2 ? true : false;
		$this->assertEquals( true, $test3 );
	}

	function testModerate_1() {
		//Unit Test 2 - Moderate
		$deptree = new DependencyTree();

		$deptree->addNode( 'A-1', [ 8 ], [ 10 ] );
		$deptree->addNode( 'A-2', [ 10 ], [ 12 ] );
		$deptree->addNode( 'A-3', [ 12 ], [ 13 ] );

		$deptree->addNode( 'B-1', [ 10 ], [ 20 ] );
		$deptree->addNode( 'B-2', [ 20 ], [ 22 ] );

		$result = $deptree->_buildTree();

		//var_dump($result);
		//$should_match = [
		//		0 => 'A-1',
		//		1 => 'A-2',
		//		2 => 'A-3',
		//		3 => 'B-1',
		//		4 => 'B-2',
		//];

		$test1 = $this->indexOf( 'A-1', $result ) < $this->indexOf( 'A-2', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test2 = $this->indexOf( 'A-2', $result ) < $this->indexOf( 'A-3', $result ) ? true : false;
		$this->assertEquals( true, $test2 );

		$test3 = $this->indexOf( 'B-1', $result ) < $this->indexOf( 'B-2', $result ) ? true : false;
		$this->assertEquals( true, $test3 );

		$test4 = $this->indexOf( 'B-1', $result ) > $this->indexOf( 'A-2', $result ) ? true : false;
		$this->assertEquals( true, $test4 );
	}

	function testCircularDependency_1() {

		//Unit Test 3 - Simple Circ. Dep test
		$deptree = new DependencyTree();

		$deptree->addNode( 'A', [ 'B' ], [ 'A' ], 2 );
		$deptree->addNode( 'B', [ 'A' ], [ 'B' ], 1 );

		$result = $deptree->_buildTree();

		//$should_match = [
		//		0 => 'B',
		//		1 => 'A',
		//];

		$test1 = $this->indexOf( 'B', $result ) < $this->indexOf( 'A', $result ) ? true : false;
		$this->assertEquals( true, $test1 );
	}

	function testCircularDependency_1B() {

		//Unit Test 3 - Simple Circ. Dep test
		$deptree = new DependencyTree();

		$deptree->addNode( 'A', [ 'B' ], [ 'A' ] ); //No sort order
		$deptree->addNode( 'B', [ 'A' ], [ 'B' ] ); //No sort order

		$result = $deptree->_buildTree();

		//$should_match = [
		//		0 => 'A',
		//		1 => 'B',
		//];

		$test1 = $this->indexOf( 'A', $result ) < $this->indexOf( 'B', $result ) ? true : false;
		$this->assertEquals( true, $test1 );
	}

	function testCircularDependency_2() {

		//Unit Test 3 - Simple Circ. Dep test with large string based orders.
		$deptree = new DependencyTree();

		$deptree->addNode( 'A', [ 'B' ], [ 'A' ], 'BTEST12345678901234567890123456789012345678901234567890123456789012345678901234567890' );
		$deptree->addNode( 'B', [ 'A' ], [ 'B' ], 'ATEST12345678901234567890123456789012345678901234567890123456789012345678901234567890' );

		$result = $deptree->_buildTree();

		//$should_match = [
		//		0 => 'B',
		//		1 => 'A',
		//];

		$test1 = $this->indexOf( 'B', $result ) < $this->indexOf( 'A', $result ) ? true : false;
		$this->assertEquals( true, $test1 );
	}

	function testCircularDependency_3() {

		//Unit Test 3 - Simple Circ. Dep test with large string based orders.
		$deptree = new DependencyTree();

		$deptree->addNode( 'A', [ 'B' ], [ 'A' ], 'A12345678901234567890123456789012345678901234567890123456789012345678901234567890B' );
		$deptree->addNode( 'B', [ 'A' ], [ 'B' ], 'A12345678901234567890123456789012345678901234567890123456789012345678901234567890A' );

		$result = $deptree->_buildTree();

		//$should_match = [
		//		0 => 'B',
		//		1 => 'A',
		//];

		$test1 = $this->indexOf( 'B', $result ) < $this->indexOf( 'A', $result ) ? true : false;
		$this->assertEquals( true, $test1 );
	}

	function testCircularDependency_4() {

		//Unit Test 4 - Simple Circ. Dep test within the same node
		$deptree = new DependencyTree();

		$deptree->addNode( 'SS-EE', [ 'HSA' ], [ 'EE' ], '50000800000000122' );
		$deptree->addNode( 'SS-ER', [ 'HSA' ], [ 'ER' ], '60000810000000123' );
		$deptree->addNode( 'HSA', [ 'HSA' ], [ 'HSA' ], '50001000000001778' );

		$result = $deptree->_buildTree();

		//$should_match = [
		//		0 => 'HSA',
		//		1 => 'SS-EE',
		//		2 => 'SS-ER',
		//];

		$test1 = $this->indexOf( 'HSA', $result ) < $this->indexOf( 'SS-EE', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test2 = $this->indexOf( 'HSA', $result ) < $this->indexOf( 'SS-ER', $result ) ? true : false;
		$this->assertEquals( true, $test2 );

		$test3 = $this->indexOf( 'SS-EE', $result ) < $this->indexOf( 'SS-ER', $result ) ? true : false;
		$this->assertEquals( true, $test3 );
	}

	function testCircularDependency_4b() {

		//Unit Test 4 - Simple Circ. Dep test within the same node
		$deptree = new DependencyTree();

		$deptree->addNode( 'SS-EE', [ 'HSA' ], [ 'EE' ], '50000800000000122' );
		$deptree->addNode( 'SS-ER', [ 'HSA' ], [ 'ER' ], '60000810000000123' );
		$deptree->addNode( 'HSA', [ 'HSA', 'HSA2' ], [ 'HSA' ], '50001000000001778' );
		$deptree->addNode( 'HSA2', [], [ 'HSA2' ], '50001000000001779' );

		$result = $deptree->_buildTree();

		//$should_match = [
		//		0 => 'HSA2',
		//		1 => 'HSA',
		//		2 => 'SS-EE',
		//		3 => 'SS-ER',
		//];

		$test1 = $this->indexOf( 'HSA2', $result ) < $this->indexOf( 'HSA', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test2 = $this->indexOf( 'HSA', $result ) < $this->indexOf( 'SS-EE', $result ) ? true : false;
		$this->assertEquals( true, $test2 );

		$test3 = $this->indexOf( 'HSA', $result ) < $this->indexOf( 'SS-ER', $result ) ? true : false;
		$this->assertEquals( true, $test3 );

		$test4 = $this->indexOf( 'SS-EE', $result ) < $this->indexOf( 'SS-ER', $result ) ? true : false;
		$this->assertEquals( true, $test4 );
	}

	function testHard_1() {
		//Unit Test 4 - Harder
		$deptree = new DependencyTree();

		$deptree->addNode( 'A-2', [ 10, 30 ], [ 20 ] );
		$deptree->addNode( 'A-1', [], [ 10 ] );
		$deptree->addNode( 'B-1', [], [ 30 ] );

		$result = $deptree->_buildTree();

		//$should_match = [
		//		0 => 'A-1',
		//		1 => 'B-1',
		//		2 => 'A-2',
		//];

		$test1 = $this->indexOf( 'A-1', $result ) < $this->indexOf( 'B-1', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test2 = $this->indexOf( 'B-1', $result ) < $this->indexOf( 'A-2', $result ) ? true : false;
		$this->assertEquals( true, $test2 );

		$test3 = $this->indexOf( 'A-2', $result ) == 2 ? true : false;
		$this->assertEquals( true, $test3 );
	}

	function testHard_2() {
		//Unit Test 5 - Hardest
		$deptree = new DependencyTree();

		$deptree->addNode( ' Test2', [ 40 ], [ 200 ] );
		$deptree->addNode( 'VacAccrual', [ 10, 20, 40 ], [ 99 ], 50 );
		$deptree->addNode( 'VacRelease', [ 99 ], [ 20 ], 100 );

		$deptree->addNode( 'Test1', [], [ 40 ] );
		$deptree->addNode( 'Test3', [], [ 10 ] );

		$result = $deptree->_buildTree();
		//var_dump($result);

		//$should_match = [
		//		0 => 'Test1',
		//		1 => 'Test3',
		//		2 => 'VacAccrual',
		//		3 => 'VacRelease',
		//		4 => ' Test2',
		//];

		$test1 = $this->indexOf( 'Test1', $result ) < $this->indexOf( 'Test3', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test2 = $this->indexOf( 'Test3', $result ) < $this->indexOf( 'VacAccrual', $result ) ? true : false;
		$this->assertEquals( true, $test2 );

		$test2b = $this->indexOf( ' Test2', $result ) < $this->indexOf( 'VacAccrual', $result ) ? true : false;
		$this->assertEquals( true, $test2b );

		$test3 = $this->indexOf( 'VacAccrual', $result ) < $this->indexOf( 'VacRelease', $result ) ? true : false;
		$this->assertEquals( true, $test3 );

		$test4 = $this->indexOf( 'VacRelease', $result ) == 4 ? true : false;
		$this->assertEquals( true, $test4 );
	}

	function testHard_3() {
		//Unit Test 6 - Double Hardest
		$deptree = new DependencyTree();

		$deptree->addNode( 'Test5', [ 200, 99, 20, 40, 10, 500 ], [ 999 ] );

		$deptree->addNode( ' Test2', [ 40 ], [ 200 ] );
		$deptree->addNode( 'VacAccrual', [ 10, 20, 40 ], [ 99 ], 50 );
		$deptree->addNode( 'VacRelease', [ 99 ], [ 20 ], 100 );

		$deptree->addNode( 'Test1', [], [ 40 ] );
		$deptree->addNode( 'Test3', [], [ 10 ] );

		$deptree->addNode( 'Test4', [ 20 ], [ 500 ] );

		$result = $deptree->_buildTree();
		//var_dump($result);

		//$should_match = [
		//		0 => 'Test1',
		//		1 => 'Test3',
		//		2 => 'VacAccrual',
		//		3 => 'VacRelease',
		//		4 => ' Test2',
		//		5 => 'Test4',
		//		6 => 'Test5',
		//];

		$test1 = $this->indexOf( 'Test1', $result ) < $this->indexOf( 'Test3', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test2 = $this->indexOf( 'Test3', $result ) < $this->indexOf( 'VacAccrual', $result ) ? true : false;
		$this->assertEquals( true, $test2 );

		$test2b = $this->indexOf( ' Test2', $result ) < $this->indexOf( 'Test4', $result ) ? true : false;
		$this->assertEquals( true, $test2b );

		$test2c = $this->indexOf( ' Test4', $result ) < $this->indexOf( 'VacAccrual', $result ) ? true : false;
		$this->assertEquals( true, $test2c );

		$test3 = $this->indexOf( 'VacAccrual', $result ) < $this->indexOf( 'VacRelease', $result ) ? true : false;
		$this->assertEquals( true, $test3 );

		$test4 = $this->indexOf( 'Test5', $result ) > $this->indexOf( 'Test4', $result ) ? true : false;
		$this->assertEquals( true, $test4 );
		$test4b = $this->indexOf( 'Test5', $result ) > $this->indexOf( 'VacAccrual', $result ) ? true : false;
		$this->assertEquals( true, $test4b );

		$test5 = $this->indexOf( 'Test5', $result ) == 6 ? true : false;
		$this->assertEquals( true, $test5 );
	}

	function testPerf_1() {
		//Unit Test 7 - Performance test
		$deptree = new DependencyTree();

		$deptree->addNode( 'U1288', [ 143 ], [ 146 ], 60 );
		$deptree->addNode( 'U1287', [ 159, 136 ], [ 143 ], 50 );
		$deptree->addNode( 'U1289', [ 159, 136 ], [ 144 ], 50 );
		$deptree->addNode( 'U1290', [ 144 ], [ 147 ], 60 );
		$deptree->addNode( 'U1291', [ 159, 136 ], [ 148 ], 60 );
		$deptree->addNode( 'U1292', [ 159, 136 ], [ 140 ], 50 );
		$deptree->addNode( 'U1293', [ 159, 136 ], [ 141 ], 50 );

		$deptree->addNode( 'U1294', [ 159, 136, 159 ], [ 151 ], 50 );
		$deptree->addNode( 'P2458', [ 151 ], [ 159 ], 40 );
		$deptree->addNode( 'P2265', [], [ 136 ], 40 );

		$result = $deptree->_buildTree();
		//var_dump($result);

		//$should_match = [
		//		0 => 'P2265',
		//		1 => 'P2458',
		//		2 => 'U1294',
		//		3 => 'U1287',
		//		4 => 'U1288',
		//		5 => 'U1289',
		//		6 => 'U1290',
		//		7 => 'U1291',
		//		8 => 'U1292',
		//		9 => 'U1293',
		//];

		$test1 = $this->indexOf( 'P2265', $result ) < $this->indexOf( 'P2458', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test2 = $this->indexOf( 'P2458', $result ) < $this->indexOf( 'U1287', $result ) ? true : false;
		$this->assertEquals( true, $test2 );

		$test3 = $this->indexOf( 'U1287', $result ) < $this->indexOf( 'U1289', $result ) ? true : false;
		$this->assertEquals( true, $test3 );

		$test4 = $this->indexOf( 'U1289', $result ) < $this->indexOf( 'U1292', $result ) ? true : false;
		$this->assertEquals( true, $test4 );

		$test5 = $this->indexOf( 'U1292', $result ) < $this->indexOf( 'U1293', $result ) ? true : false;
		$this->assertEquals( true, $test5 );

		$test6 = $this->indexOf( 'U1293', $result ) < $this->indexOf( 'U1294', $result ) ? true : false;
		$this->assertEquals( true, $test6 );

		$test7 = $this->indexOf( 'U1294', $result ) < $this->indexOf( 'U1291', $result ) ? true : false;
		$this->assertEquals( true, $test7 );

		$test8 = $this->indexOf( 'U1291', $result ) < $this->indexOf( 'U1288', $result ) ? true : false;
		$this->assertEquals( true, $test8 );

		$test9 = $this->indexOf( 'U1288', $result ) < $this->indexOf( 'U1290', $result ) ? true : false;
		$this->assertEquals( true, $test9 );

		$test10 = $this->indexOf( 'U1290', $result ) == 9 ? true : false;
		$this->assertEquals( true, $test10 );
	}

	function testHard_4() {
		$deptree = new DependencyTree();
		// 1st requires, 2nd provides
		// p3072 p3071, u2022 u2130 u2129
		//
		//
		//

		$deptree->addNode( 'U2022', [ 268, 266 ], [ 265 ], 30 );
		$deptree->addNode( 'U2130', [ 268, 266 ], [ 260 ], 50 );
		$deptree->addNode( 'U2129', [ 268, 266 ], [ 257 ], 50 );

		$deptree->addNode( 'P3072', [], [ 268 ], 40 );
		$deptree->addNode( 'P3071', [ 265 ], [ 266 ], 40 );
		$deptree->addNode( 'P3073', [], [ 283 ], 50 );

		$result = $deptree->_buildTree();
		//var_dump($result);

		//$should_match = [
		//		0 => 'P3072',
		//		1 => 'U2022',
		//		2 => 'P3071',
		//		3 => 'U2129',
		//		4 => 'U2130',
		//		5 => 'P3073',
		//];

		$test1 = $this->indexOf( 'P3072', $result ) < $this->indexOf( 'P3073', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test2 = $this->indexOf( 'P3073', $result ) < $this->indexOf( 'U2022', $result ) ? true : false;
		$this->assertEquals( true, $test2 );

		$test3 = $this->indexOf( 'U2022', $result ) < $this->indexOf( 'P3071', $result ) ? true : false;
		$this->assertEquals( true, $test3 );

		$test4 = $this->indexOf( 'P3071', $result ) < $this->indexOf( 'U2129', $result ) ? true : false;
		$this->assertEquals( true, $test4 );

		$test5 = $this->indexOf( 'U2129', $result ) < $this->indexOf( 'U2130', $result ) ? true : false;
		$this->assertEquals( true, $test5 );

		$test10 = $this->indexOf( 'U2130', $result ) == 5 ? true : false;
		$this->assertEquals( true, $test10 );
	}

	function testHard_5() {
		/*

	public 'raw_data' =>
		array
		  'U2069' =>
			object(DependencyTreeNode)[113]
			  protected 'data' =>
				array
				  'id' => 'U2069' (length=5)
				  'requires' =>
					array
					  0 => '101' (length=3)
					  1 => '102' (length=3)
					  2 => '120' (length=3)
					  3 => '254' (length=3)
					  4 => '128' (length=3)
					  5 => '103' (length=3)
					  6 => '104' (length=3)
					  7 => '123' (length=3)
					  8 => '124' (length=3)
					  9 => '125' (length=3)
					  10 => '126' (length=3)
					  11 => '129' (length=3)
					  12 => '130' (length=3)
					  13 => '256' (length=3)
					  14 => '105' (length=3)
					  15 => '119' (length=3)
				  'provides' =>
					array
					  0 => '254' (length=3)
				  'order' => 40
		  'U2060' =>
			object(DependencyTreeNode)[131]
			  protected 'data' =>
				array
				  'id' => 'U2060' (length=5)
				  'requires' =>
					array
					  0 => '101' (length=3)
					  1 => '102' (length=3)
					  2 => '120' (length=3)
					  3 => '254' (length=3)
					  4 => '128' (length=3)
					  5 => '103' (length=3)
					  6 => '104' (length=3)
					  7 => '123' (length=3)
					  8 => '124' (length=3)
					  9 => '125' (length=3)
					  10 => '126' (length=3)
					  11 => '129' (length=3)
					  12 => '130' (length=3)
					  13 => '256' (length=3)
					  14 => '105' (length=3)
					  15 => '119' (length=3)
					  16 => '255' (length=3)
				  'provides' =>
					array
					  0 => '110' (length=3)
				  'order' => 50
		  'U2061' =>
			object(DependencyTreeNode)[144]
			  protected 'data' =>
				array
				  'id' => 'U2061' (length=5)
				  'requires' =>
					array
					  0 => '110' (length=3)
				  'provides' =>
					array
					  0 => '113' (length=3)
				  'order' => 60
		  'U2062' =>
			object(DependencyTreeNode)[153]
			  protected 'data' =>
				array
				  'id' => 'U2062' (length=5)
				  'requires' =>
					array
					  0 => '101' (length=3)
					  1 => '102' (length=3)
					  2 => '120' (length=3)
					  3 => '254' (length=3)
					  4 => '128' (length=3)
					  5 => '103' (length=3)
					  6 => '104' (length=3)
					  7 => '123' (length=3)
					  8 => '124' (length=3)
					  9 => '125' (length=3)
					  10 => '126' (length=3)
					  11 => '129' (length=3)
					  12 => '130' (length=3)
					  13 => '256' (length=3)
					  14 => '105' (length=3)
					  15 => '119' (length=3)
					  16 => '255' (length=3)
				  'provides' =>
					array
					  0 => '111' (length=3)
				  'order' => 50
		  'U2063' =>
			object(DependencyTreeNode)[166]
			  protected 'data' =>
				array
				  'id' => 'U2063' (length=5)
				  'requires' =>
					array
					  0 => '111' (length=3)
				  'provides' =>
					array
					  0 => '114' (length=3)
				  'order' => 60
		  'U2064' =>
			object(DependencyTreeNode)[175]
			  protected 'data' =>
				array
				  'id' => 'U2064' (length=5)
				  'requires' =>
					array
					  0 => '101' (length=3)
					  1 => '102' (length=3)
					  2 => '120' (length=3)
					  3 => '254' (length=3)
					  4 => '128' (length=3)
					  5 => '103' (length=3)
					  6 => '104' (length=3)
					  7 => '123' (length=3)
					  8 => '124' (length=3)
					  9 => '125' (length=3)
					  10 => '126' (length=3)
					  11 => '129' (length=3)
					  12 => '130' (length=3)
					  13 => '256' (length=3)
					  14 => '105' (length=3)
					  15 => '119' (length=3)
				  'provides' =>
					array
					  0 => '115' (length=3)
				  'order' => 60
		  'U2065' =>
			object(DependencyTreeNode)[186]
			  protected 'data' =>
				array
				  'id' => 'U2065' (length=5)
				  'requires' =>
					array
					  0 => '101' (length=3)
					  1 => '102' (length=3)
					  2 => '120' (length=3)
					  3 => '254' (length=3)
					  4 => '128' (length=3)
					  5 => '103' (length=3)
					  6 => '104' (length=3)
					  7 => '123' (length=3)
					  8 => '124' (length=3)
					  9 => '125' (length=3)
					  10 => '126' (length=3)
					  11 => '129' (length=3)
					  12 => '130' (length=3)
					  13 => '256' (length=3)
					  14 => '105' (length=3)
					  15 => '119' (length=3)
				  'provides' =>
					array
					  0 => '127' (length=3)
				  'order' => 50
		  'U2066' =>
			object(DependencyTreeNode)[197]
			  protected 'data' =>
				array
				  'id' => 'U2066' (length=5)
				  'requires' =>
					array
					  0 => '101' (length=3)
					  1 => '102' (length=3)
					  2 => '120' (length=3)
					  3 => '254' (length=3)
					  4 => '128' (length=3)
					  5 => '103' (length=3)
					  6 => '104' (length=3)
					  7 => '123' (length=3)
					  8 => '124' (length=3)
					  9 => '125' (length=3)
					  10 => '126' (length=3)
					  11 => '129' (length=3)
					  12 => '130' (length=3)
					  13 => '256' (length=3)
					  14 => '105' (length=3)
					  15 => '119' (length=3)
					  16 => '127' (length=3)
				  'provides' =>
					array
					  0 => '107' (length=3)
				  'order' => 50
		  'U2067' =>
			object(DependencyTreeNode)[208]
			  protected 'data' =>
				array
				  'id' => 'U2067' (length=5)
				  'requires' =>
					array
					  empty
				  'provides' =>
					array
					  0 => '109' (length=3)
				  'order' => 50
		  'U2068' =>
			object(DependencyTreeNode)[193]
			  protected 'data' =>
				array
				  'id' => 'U2068' (length=5)
				  'requires' =>
					array
					  0 => '101' (length=3)
					  1 => '102' (length=3)
					  2 => '120' (length=3)
					  3 => '254' (length=3)
					  4 => '128' (length=3)
					  5 => '103' (length=3)
					  6 => '104' (length=3)
					  7 => '123' (length=3)
					  8 => '124' (length=3)
					  9 => '125' (length=3)
					  10 => '126' (length=3)
					  11 => '129' (length=3)
					  12 => '130' (length=3)
					  13 => '256' (length=3)
					  14 => '105' (length=3)
					  15 => '119' (length=3)
					  16 => '127' (length=3)
					  17 => '121' (length=3)
				  'provides' =>
					array
					  0 => '108' (length=3)
				  'order' => 50
		  'P3217' =>
			object(DependencyTreeNode)[230]
			  protected 'data' =>
				array
				  'id' => 'P3217' (length=5)
				  'requires' =>
					array
					  0 => null
				  'provides' =>
					array
					  0 => 256
				  'order' => 40
		  'P3290' =>
			object(DependencyTreeNode)[218]
			  protected 'data' =>
				array
				  'id' => 'P3290' (length=5)
				  'requires' =>
					array
					  0 => null
				  'provides' =>
					array
					  0 => 256
				  'order' => 40
		  'P3294' =>
			object(DependencyTreeNode)[236]
			  protected 'data' =>
				array
				  'id' => 'P3294' (length=5)
				  'requires' =>
					array
					  0 => null
				  'provides' =>
					array
					  0 => 256
				  'order' => 40
		*/
		$deptree = new DependencyTree();

		$deptree->addNode( 'U2029',
						   [ 101, 102, 120, 254, 128, 103, 104, 123, 124, 125, 126, 129, 130, 256, 105, 119 ],
						   [ 254 ], 40 );

		$deptree->addNode( 'U2060',
						   [ 101, 102, 120, 254, 128, 103, 104, 123, 124, 125, 126, 129, 130, 256, 105, 119, 255 ],
						   [ 110 ], 50 );

		$deptree->addNode( 'U2061',
						   [ 110 ],
						   [ 113 ], 60 );

		$deptree->addNode( 'U2062',
						   [ 101, 102, 120, 254, 128, 103, 104, 123, 124, 125, 126, 129, 130, 256, 105, 119, 255 ],
						   [ 111 ], 50 );

		$deptree->addNode( 'U2063',
						   [ 111 ],
						   [ 114 ], 60 );

		$deptree->addNode( 'U2064',
						   [ 101, 102, 120, 254, 128, 103, 104, 123, 124, 125, 126, 129, 130, 256, 105, 119 ],
						   [ 115 ], 60 );

		$deptree->addNode( 'U2065',
						   [ 101, 102, 120, 254, 128, 103, 104, 123, 124, 125, 126, 129, 130, 256, 105, 119 ],
						   [ 127 ], 50 );

		$deptree->addNode( 'U2066',
						   [ 101, 102, 120, 254, 128, 103, 104, 123, 124, 125, 126, 129, 130, 256, 105, 119, 127 ],
						   [ 107 ], 50 );

		$deptree->addNode( 'U2067',
						   [],
						   [ 109 ], 50 );

		$deptree->addNode( 'U2068',
						   [ 101, 102, 120, 254, 128, 103, 104, 123, 124, 125, 126, 129, 130, 256, 105, 119, 127, 121 ],
						   [ 108 ], 50 );

		$deptree->addNode( 'P3217', [ null ], [ 256 ], 40 );

		//
		//Uncomment any of the ADDNODE lines below to cause the infinite loop
		//
		$deptree->addNode( 'P3290', [ null ], [ 256 ], 40 );
		$deptree->addNode( 'P3294', [ null ], [ 256 ], 40 );

		$result = $deptree->_buildTree();
		//var_dump($result);

		//THIS MAY NOT BE CORRECT...
		//$should_match = [
		//		0  => 'P3217',
		//		1  => 'P3290',
		//		2  => 'P3294',
		//		3  => 'U2029',
		//		4  => 'U2060',
		//		5  => 'U2061',
		//		6  => 'U2062',
		//		7  => 'U2063',
		//		8  => 'U2064',
		//		9  => 'U2065',
		//		10 => 'U2066',
		//		11 => 'U2068',
		//		12 => 'U2067',
		//];

		$test1 = $this->indexOf( 'P3217', $result ) < $this->indexOf( 'P3290', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'P3290', $result ) < $this->indexOf( 'P3294', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'P3294', $result ) < $this->indexOf( 'U2067', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'U2067', $result ) < $this->indexOf( 'U2029', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'U2029', $result ) < $this->indexOf( 'U2060', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'U2060', $result ) < $this->indexOf( 'U2062', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'U2062', $result ) < $this->indexOf( 'U2065', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'U2065', $result ) < $this->indexOf( 'U2064', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'U2064', $result ) < $this->indexOf( 'U2066', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'U2066', $result ) < $this->indexOf( 'U2068', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'U2068', $result ) < $this->indexOf( 'U2061', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'U2061', $result ) < $this->indexOf( 'U2063', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test10 = $this->indexOf( 'U2063', $result ) == 12 ? true : false;
		$this->assertEquals( true, $test10 );
	}

	function testHard_6() {
		//Unit Test 12 - Performance test
		$deptree = new DependencyTree();
		/*
		public 'raw_data' =>
			array
			  'U156' =>
				object(DependencyTreeNode)[115]
				  protected 'data' =>
					array
					  'id' => 'U156' (length=4)
					  'requires' =>
						array
						  0 => '254' (length=3)
					  'provides' =>
						array
						  0 => '254' (length=3)
					  'order' => 40
					  'treenumber' => 0
			  'U149' =>
				object(DependencyTreeNode)[134]
				  protected 'data' =>
					array
					  'id' => 'U149' (length=4)
					  'requires' =>
						array
						  0 => '254' (length=3)
						  1 => '255' (length=3)
					  'provides' =>
						array
						  0 => '110' (length=3)
					  'order' => 50
					  'treenumber' => 0
			  'U150' =>
				object(DependencyTreeNode)[141]
				  protected 'data' =>
					array
					  'id' => 'U150' (length=4)
					  'requires' =>
						array
						  0 => '110' (length=3)
					  'provides' =>
						array
						  0 => '113' (length=3)
					  'order' => 60
					  'treenumber' => 0
			  'U151' =>
				object(DependencyTreeNode)[152]
				  protected 'data' =>
					array
					  'id' => 'U151' (length=4)
					  'requires' =>
						array
						  0 => '254' (length=3)
						  1 => '255' (length=3)
					  'provides' =>
						array
						  0 => '111' (length=3)
					  'order' => 50
					  'treenumber' => 0
			  'U152' =>
				object(DependencyTreeNode)[159]
				  protected 'data' =>
					array
					  'id' => 'U152' (length=4)
					  'requires' =>
						array
						  0 => '111' (length=3)
					  'provides' =>
						array
						  0 => '114' (length=3)
					  'order' => 60
					  'treenumber' => 0
			  'U153' =>
				object(DependencyTreeNode)[168]
				  protected 'data' =>
					array
					  'id' => 'U153' (length=4)
					  'requires' =>
						array
						  0 => '254' (length=3)
					  'provides' =>
						array
						  0 => '115' (length=3)
					  'order' => 60
					  'treenumber' => 0
			  'U998' =>
				object(DependencyTreeNode)[177]
				  protected 'data' =>
					array
					  'id' => 'U998' (length=4)
					  'requires' =>
						array
						  0 => '254' (length=3)
					  'provides' =>
						array
						  0 => '127' (length=3)
					  'order' => 50
					  'treenumber' => 0
			  'U154' =>
				object(DependencyTreeNode)[185]
				  protected 'data' =>
					array
					  'id' => 'U154' (length=4)
					  'requires' =>
						array
						  0 => '254' (length=3)
						  1 => '127' (length=3)
					  'provides' =>
						array
						  0 => '107' (length=3)
					  'order' => 50
					  'treenumber' => 0
			  'U155' =>
				object(DependencyTreeNode)[197]
				  protected 'data' =>
					array
					  'id' => 'U155' (length=4)
					  'requires' =>
						array
						  0 => '254' (length=3)
						  1 => '127' (length=3)
						  2 => '121' (length=3)
					  'provides' =>
						array
						  0 => '108' (length=3)
					  'order' => 50
					  'treenumber' => 0
			  'U4452' =>
				object(DependencyTreeNode)[204]
				  protected 'data' =>
					array
					  'id' => 'U4452' (length=5)
					  'requires' =>
						array
						  0 => '254' (length=3)
					  'provides' =>
						array
						  0 => '255' (length=3)
					  'order' => 60
					  'treenumber' => 0
			  'P3475' =>
				object(DependencyTreeNode)[190]
				  protected 'data' =>
					array
					  'id' => 'P3475' (length=5)
					  'requires' =>
						array
						  empty
					  'provides' =>
						array
						  0 => 121
					  'order' => 50
					  'treenumber' =>
		*/

		//
		//U4452 should be above U149 always!!
		//
		$deptree->addNode( 'U156', [ 254 ], [ 254 ], 40 );

		//$deptree->addNode('U4452', array(254), array(255), 60 ); //Works if I put this here

		$deptree->addNode( 'U149', [ 254, 255 ], [ 110 ], 50 );
		$deptree->addNode( 'U153', [ 254 ], [ 115 ], 60 );

		$deptree->addNode( 'U4452', [ 254 ], [ 255 ], 60 ); //Fails if I put it here

		$deptree->addNode( 'U998', [ 254 ], [ 127 ], 50 );

		$result = $deptree->_buildTree();
		//var_dump($result);

		//$should_match = [
		//		0 => 'U156',
		//		1 => 'U4452',
		//		2 => 'U149',
		//		3 => 'U153',
		//		4 => 'U998',
		//];


		$test1 = $this->indexOf( 'U156', $result ) < $this->indexOf( 'U998', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'U998', $result ) < $this->indexOf( 'U153', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'U153', $result ) < $this->indexOf( 'U4452', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'U4452', $result ) < $this->indexOf( 'U149', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test5 = $this->indexOf( 'U149', $result ) == 4 ? true : false;
		$this->assertEquals( true, $test5 );
	}

	function testTwoTrees_1() {
		$deptree = new DependencyTree();
		$deptree->setTreeOrdering( true );


		//
		//U4452 should be above U149 always!!
		//

		//Tree 1
		$deptree->addNode( 'U156', [ 254 ], [ 254 ], 40 );
		$deptree->addNode( 'U149', [ 254, 255 ], [ 110 ], 50 );
		$deptree->addNode( 'U153', [ 254 ], [ 115 ], 60 );
		$deptree->addNode( 'U4452', [ 254 ], [ 255 ], 60 ); //Fails if I put it here
		$deptree->addNode( 'U998', [ 254 ], [ 127 ], 50 );

		//Tree 2
		$deptree->addNode( 'Z156', [ 2540 ], [ 2540 ], 40 );
		$deptree->addNode( 'Z149', [ 2540, 2550 ], [ 1100 ], 50 );
		$deptree->addNode( 'Z153', [ 2540 ], [ 1150 ], 60 );
		$deptree->addNode( 'Z4452', [ 2540 ], [ 2550 ], 60 ); //Fails if I put it here
		$deptree->addNode( 'Z998', [ 2540 ], [ 1270 ], 50 );

		$result = $deptree->_buildTree();
		//var_dump($result);

		//$should_match = [
		//		0 => 'U156',
		//		1 => 'U4452',
		//		2 => 'U149',
		//		3 => 'U153',
		//		4 => 'U998',
		//];


		//Tree 1
		$test1 = $this->indexOf( 'U156', $result ) < $this->indexOf( 'U998', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'U998', $result ) < $this->indexOf( 'U153', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'U153', $result ) < $this->indexOf( 'U4452', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'U4452', $result ) < $this->indexOf( 'U149', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test5 = $this->indexOf( 'U149', $result ) == 4 ? true : false;
		$this->assertEquals( true, $test5 );

		//Tree 2
		$test1 = $this->indexOf( 'Z156', $result ) < $this->indexOf( 'Z998', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'Z998', $result ) < $this->indexOf( 'Z153', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'Z153', $result ) < $this->indexOf( 'Z4452', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'Z4452', $result ) < $this->indexOf( 'Z149', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test5 = $this->indexOf( 'Z149', $result ) == 9 ? true : false;
		$this->assertEquals( true, $test5 );
	}

	function testTwoTrees_2() {
		$deptree = new DependencyTree();
		$deptree->setTreeOrdering( false );


		//
		//U4452 should be above U149 always!!
		//

		//Tree 1
		$deptree->addNode( 'U156', [ 254 ], [ 254 ], 40 );
		$deptree->addNode( 'U149', [ 254, 255 ], [ 110 ], 50 );
		$deptree->addNode( 'U153', [ 254 ], [ 115 ], 60 );
		$deptree->addNode( 'U4452', [ 254 ], [ 255 ], 60 ); //Fails if I put it here
		$deptree->addNode( 'U998', [ 254 ], [ 127 ], 50 );

		//Tree 2
		$deptree->addNode( 'Z156', [ 2540 ], [ 2540 ], 40 );
		$deptree->addNode( 'Z149', [ 2540, 2550 ], [ 1100 ], 50 );
		$deptree->addNode( 'Z153', [ 2540 ], [ 1150 ], 60 );
		$deptree->addNode( 'Z4452', [ 2540 ], [ 2550 ], 60 ); //Fails if I put it here
		$deptree->addNode( 'Z998', [ 2540 ], [ 1270 ], 50 );

		$result = $deptree->_buildTree();
		//var_dump($result);

		//$should_match = [
		//		0 => 'U156',
		//		1 => 'U4452',
		//		2 => 'U149',
		//		3 => 'U153',
		//		4 => 'U998',
		//];


		//Tree 1
		$test1 = $this->indexOf( 'U156', $result ) < $this->indexOf( 'U998', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'U998', $result ) < $this->indexOf( 'U153', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'U153', $result ) < $this->indexOf( 'U4452', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'U4452', $result ) < $this->indexOf( 'U149', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		//Tree 2
		$test1 = $this->indexOf( 'Z156', $result ) < $this->indexOf( 'Z998', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'Z998', $result ) < $this->indexOf( 'Z153', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'Z153', $result ) < $this->indexOf( 'Z4452', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'Z4452', $result ) < $this->indexOf( 'Z149', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		//Combined Trees
		$test1 = $this->indexOf( 'U156', $result ) < $this->indexOf( 'Z156', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'U998', $result ) < $this->indexOf( 'Z153', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'U153', $result ) < $this->indexOf( 'Z4452', $result ) ? true : false;
		$this->assertEquals( true, $test1 );

		$test1 = $this->indexOf( 'U4452', $result ) < $this->indexOf( 'Z149', $result ) ? true : false;
		$this->assertEquals( true, $test1 );
	}
	/*
	$deptree->addNode('P17', 	array(), 			array(124), 6000380 );

	$deptree->addNode('U7897', 	array(104,121), 	array(120), 6000100 ); //Average Vacation Rate (per Day)
	$deptree->addNode('U7905', 	array(120), 		array(104), 4000200 ); //Vacation Pay (Based on Average Rate/Day)
	$deptree->addNode('U7907', 	array(104,121), 	array(121), 4000100 ); //Vac Normalization
	$deptree->addNode('U7909', 	array(121,104), 	array(92), 	5000205 ); //Income Tax


	U7905 (R: 120 P: 104) -> U7897 (R:104,121 P: 120)
							 U7907 (R:104,121 P: 121)

	 */
}

?>