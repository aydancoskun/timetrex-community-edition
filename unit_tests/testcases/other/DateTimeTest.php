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
 * @group DateTime
 */
class DateTimeTest extends PHPUnit\Framework\TestCase {
	public function setUp(): void {
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setTimeZone( 'Etc/GMT+8', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		//If using loadbalancer, we need to make a SQL query to initiate at least one connection to a database.
		//This is needed for testTimeZone() to work with the load balancer.
		global $db;
		$db->Execute( 'SELECT 1' );
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	function testTimeUnit1() {
		Debug::text( 'Testing Time Unit Format: hh:mm', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setDateFormat( 'dMY' );
		TTDate::setTimeFormat( 'g:i A' );

		/*
			10 	=> 'hh:mm (2:15)',
			12 	=> 'hh:mm:ss (2:15:59)',
			20 	=> 'Hours (2.25)',
			22 	=> 'Hours (2.241)',
			30 	=> 'Minutes (135)'
		*/
		TTDate::setTimeUnitFormat( 10 );

		$this->assertEquals( 60, TTDate::parseTimeUnit( '00:01' ) );
		$this->assertEquals( -60, TTDate::parseTimeUnit( '-00:01' ) );


		$this->assertEquals( 3600, TTDate::parseTimeUnit( '01:00' ) );
		$this->assertEquals( 36000, TTDate::parseTimeUnit( '10:00' ) );
		$this->assertEquals( 360000, TTDate::parseTimeUnit( '100:00' ) );
		$this->assertEquals( 3600000, TTDate::parseTimeUnit( '1000:00' ) );
		$this->assertEquals( 3600000, TTDate::parseTimeUnit( '1,000:00' ) );
		$this->assertEquals( 36000000, TTDate::parseTimeUnit( '10,000:00' ) );
		$this->assertEquals( 36000060, TTDate::parseTimeUnit( '10,000:01.5' ) );

		$this->assertEquals( 3600, TTDate::parseTimeUnit( '01' ) );
		$this->assertEquals( 3600, TTDate::parseTimeUnit( '1' ) );
		$this->assertEquals( -3600, TTDate::parseTimeUnit( '-1' ) );
		$this->assertEquals( 3600, TTDate::parseTimeUnit( '1:' ) );
		$this->assertEquals( 3600, TTDate::parseTimeUnit( '1:00:00' ) );
		$this->assertEquals( 3601, TTDate::parseTimeUnit( '1:00:01' ) );
		$this->assertEquals( 3601, TTDate::parseTimeUnit( '"1:00:01"' ) );

		$this->assertEquals( 3600, TTDate::parseTimeUnit( '00:60' ) );
		$this->assertEquals( 3600, TTDate::parseTimeUnit( ':60' ) );
		$this->assertEquals( 60, TTDate::parseTimeUnit( ':1' ) );

		$this->assertEquals( 3601, TTDate::parseTimeUnit( '1:00:01.5' ) );
		$this->assertEquals( 3660, TTDate::parseTimeUnit( '1:1.5' ) );

		//Hybrid mode
		$this->assertEquals( 3600, TTDate::parseTimeUnit( '1.000' ) );
		$this->assertEquals( 3600, TTDate::parseTimeUnit( '1.00' ) );
		$this->assertEquals( 3600, TTDate::parseTimeUnit( '1' ) );
		$this->assertEquals( -3600, TTDate::parseTimeUnit( '-1' ) );
		$this->assertEquals( 3600, TTDate::parseTimeUnit( '01' ) );

		$this->assertEquals( 900, TTDate::parseTimeUnit( '0.25' ) );
		$this->assertEquals( 1800, TTDate::parseTimeUnit( '0.50' ) );

		$this->assertEquals( 1200, TTDate::parseTimeUnit( '0.34' ) ); //Automatically rounds to nearest 1min



		TTDate::setTimeUnitFormat( 12 ); //HH:MM:SS
		$this->assertEquals( 60, TTDate::parseTimeUnit( '00:01' ) );
		$this->assertEquals( -60, TTDate::parseTimeUnit( '-00:01' ) );


		$this->assertEquals( 3600, TTDate::parseTimeUnit( '01:00' ) );
		$this->assertEquals( 36000, TTDate::parseTimeUnit( '10:00' ) );
		$this->assertEquals( 360000, TTDate::parseTimeUnit( '100:00' ) );
		$this->assertEquals( 3600000, TTDate::parseTimeUnit( '1000:00' ) );
		$this->assertEquals( 3600000, TTDate::parseTimeUnit( '1,000:00' ) );
		$this->assertEquals( 36000000, TTDate::parseTimeUnit( '10,000:00' ) );
		$this->assertEquals( 36000060, TTDate::parseTimeUnit( '10,000:01.5' ) );

		$this->assertEquals( 3600, TTDate::parseTimeUnit( '01' ) );
		$this->assertEquals( 3600, TTDate::parseTimeUnit( '1' ) );
		$this->assertEquals( -3600, TTDate::parseTimeUnit( '-1' ) );
		$this->assertEquals( 3600, TTDate::parseTimeUnit( '1:' ) );
		$this->assertEquals( 3600, TTDate::parseTimeUnit( '1:00:00' ) );
		$this->assertEquals( 3601, TTDate::parseTimeUnit( '1:00:01' ) );
		$this->assertEquals( 3601, TTDate::parseTimeUnit( '"1:00:01"' ) );

		$this->assertEquals( 3600, TTDate::parseTimeUnit( '00:60' ) );
		$this->assertEquals( 3600, TTDate::parseTimeUnit( ':60' ) );
		$this->assertEquals( 60, TTDate::parseTimeUnit( ':1' ) );

		$this->assertEquals( 3601, TTDate::parseTimeUnit( '1:00:01.5' ) );
		$this->assertEquals( 3660, TTDate::parseTimeUnit( '1:1.5' ) );


		TTDate::setTimeUnitFormat( 40 ); //Seconds
		$this->assertEquals( 3600, TTDate::parseTimeUnit( '3600' ) );
		$this->assertEquals( 3600, TTDate::parseTimeUnit( '3600.001' ) );
		$this->assertEquals( 3600.001, TTDate::parseTimeUnit( '"3600.001"' ) );
		$this->assertEquals( 3629, TTDate::parseTimeUnit( '3629.001' ) );
		$this->assertEquals( 3629.001, TTDate::parseTimeUnit( '"3629.001"' ) );
	}

	function testTimeUnit2() {
		Debug::text( 'Testing Time Unit Format: Decimal', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setDateFormat( 'dMY' );
		TTDate::setTimeFormat( 'g:i A' );

		/*
			10 	=> 'hh:mm (2:15)',
			12 	=> 'hh:mm:ss (2:15:59)',
			20 	=> 'Hours (2.25)',
			22 	=> 'Hours (2.241)',
			30 	=> 'Minutes (135)'
		*/
		TTDate::setTimeUnitFormat( 20 );

		$this->assertEquals( 3600, TTDate::parseTimeUnit( '1.000' ) );
		$this->assertEquals( 3600, TTDate::parseTimeUnit( '1.00' ) );
		$this->assertEquals( 36000, TTDate::parseTimeUnit( '10.00' ) );
		$this->assertEquals( 360000, TTDate::parseTimeUnit( '100.00' ) );
		$this->assertEquals( 3600000, TTDate::parseTimeUnit( '1000.00' ) );
		$this->assertEquals( 3600000, TTDate::parseTimeUnit( '1000.0001' ) );
		$this->assertEquals( 3600000.36, TTDate::parseTimeUnit( '"1000.0001"' ) );
		$this->assertEquals( 3600000, TTDate::parseTimeUnit( '1,000.00' ) );
		$this->assertEquals( 3600000, TTDate::parseTimeUnit( '"1,000.00"' ) );
		$this->assertEquals( 3600000, TTDate::parseTimeUnit( ' "1, 000.00" ' ) );
		$this->assertEquals( -3600000, TTDate::parseTimeUnit( ' "-1, 000.00" ' ) );
		$this->assertEquals( 3600, TTDate::parseTimeUnit( '1' ) );
		$this->assertEquals( -3600, TTDate::parseTimeUnit( '-1' ) );
		$this->assertEquals( 3600, TTDate::parseTimeUnit( '01' ) );

		$this->assertEquals( 900, TTDate::parseTimeUnit( '0.25' ) );
		$this->assertEquals( 1800, TTDate::parseTimeUnit( '0.50' ) );

		$this->assertEquals( 1200, TTDate::parseTimeUnit( '0.34' ) ); //Automatically rounds to nearest 1min

		//Hybrid mode
		$this->assertEquals( 60, TTDate::parseTimeUnit( '00:01' ) );
		$this->assertEquals( -60, TTDate::parseTimeUnit( '-00:01' ) );

		$this->assertEquals( 3600, TTDate::parseTimeUnit( '01:00' ) );
		$this->assertEquals( 3600, TTDate::parseTimeUnit( '01' ) );
		$this->assertEquals( 3600, TTDate::parseTimeUnit( '1' ) );
		$this->assertEquals( -3600, TTDate::parseTimeUnit( '-1' ) );
		$this->assertEquals( 3600, TTDate::parseTimeUnit( '1:' ) );
		$this->assertEquals( 3600, TTDate::parseTimeUnit( '1:00:00' ) );

		$this->assertEquals( 3600, TTDate::parseTimeUnit( '00:60' ) );
		$this->assertEquals( 3600, TTDate::parseTimeUnit( ':60' ) );
		$this->assertEquals( 60, TTDate::parseTimeUnit( ':1' ) );

		$this->assertEquals( 3600, TTDate::parseTimeUnit( '1:00:01.5' ) );
		$this->assertEquals( 3660, TTDate::parseTimeUnit( '1:1.5' ) );
	}

	function testTimeUnit3() {
		Debug::text( 'Testing Time Unit Format: Decimal', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setDateFormat( 'dMY' );
		TTDate::setTimeFormat( 'g:i A' );

		/*
			10 	=> 'hh:mm (2:15)',
			12 	=> 'hh:mm:ss (2:15:59)',
			20 	=> 'Hours (2.25)',
			22 	=> 'Hours (2.241)',
			30 	=> 'Minutes (135)'
		*/
		TTDate::setTimeUnitFormat( 20 );

		$this->assertEquals( TTDate::parseTimeUnit( '0.02' ), ( 1 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.03' ), ( 2 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.05' ), ( 3 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.07' ), ( 4 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.08' ), ( 5 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.10' ), ( 6 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.12' ), ( 7 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.13' ), ( 8 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.15' ), ( 9 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.17' ), ( 10 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.18' ), ( 11 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.20' ), ( 12 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.22' ), ( 13 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.23' ), ( 14 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.25' ), ( 15 * 60 ) );

		$this->assertEquals( TTDate::parseTimeUnit( '0.27' ), ( 16 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.28' ), ( 17 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.30' ), ( 18 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.32' ), ( 19 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.33' ), ( 20 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.35' ), ( 21 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.37' ), ( 22 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.38' ), ( 23 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.40' ), ( 24 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.42' ), ( 25 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.43' ), ( 26 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.45' ), ( 27 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.47' ), ( 28 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.48' ), ( 29 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.50' ), ( 30 * 60 ) );


		$this->assertEquals( TTDate::parseTimeUnit( '0.52' ), ( 31 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.53' ), ( 32 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.55' ), ( 33 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.57' ), ( 34 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.58' ), ( 35 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.60' ), ( 36 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.62' ), ( 37 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.63' ), ( 38 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.65' ), ( 39 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.67' ), ( 40 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.68' ), ( 41 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.70' ), ( 42 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.72' ), ( 43 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.73' ), ( 44 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.75' ), ( 45 * 60 ) );

		$this->assertEquals( TTDate::parseTimeUnit( '0.77' ), ( 46 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.78' ), ( 47 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.80' ), ( 48 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.82' ), ( 49 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.84' ), ( 50 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.85' ), ( 51 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.87' ), ( 52 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.89' ), ( 53 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.90' ), ( 54 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.92' ), ( 55 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.94' ), ( 56 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.95' ), ( 57 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.97' ), ( 58 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '0.99' ), ( 59 * 60 ) );
		$this->assertEquals( TTDate::parseTimeUnit( '1.00' ), ( 60 * 60 ) );
	}

	function testTimeUnit4() {
		Debug::text( 'Testing Time Unit Format: Decimal', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setDateFormat( 'dMY' );
		TTDate::setTimeFormat( 'g:i A' );

		/*
			10 	=> 'hh:mm (2:15)',
			12 	=> 'hh:mm:ss (2:15:59)',
			20 	=> 'Hours (2.25)',
			22 	=> 'Hours (2.241)',
			30 	=> 'Minutes (135)'
		*/
		TTDate::setTimeUnitFormat( 10 );
		$this->assertEquals( '01:00', TTDate::getTimeUnit( 3600 ) );
		$this->assertEquals( '01:01', TTDate::getTimeUnit( 3660 ) );
		$this->assertEquals( '10:01', TTDate::getTimeUnit( 36060 ) );
		$this->assertEquals( '10:11', TTDate::getTimeUnit( 36660 ) );
		$this->assertEquals( '100:11', TTDate::getTimeUnit( 360660 ) );
		$this->assertEquals( '1000:11', TTDate::getTimeUnit( 3600660 ) );
		$this->assertEquals( '10000:11', TTDate::getTimeUnit( 36000660 ) );
		$this->assertEquals( '100000:11', TTDate::getTimeUnit( 360000660 ) );
		$this->assertEquals( '1000000:11', TTDate::getTimeUnit( 3600000660 ) );
		//$this->assertEquals( TTDate::getTimeUnit( ( PHP_INT_MAX + PHP_INT_MAX ) ),  				'ERR(FLOAT)' ); //This is passing a float that is losing precision.
		$this->assertEquals( '5124095576030431:00', TTDate::getTimeUnit( bcadd( PHP_INT_MAX, PHP_INT_MAX ) ) );
		$this->assertEquals( '5124095576030431:11', TTDate::getTimeUnit( bcadd( bcadd( PHP_INT_MAX, PHP_INT_MAX ), 660 ) ) );


		TTDate::setTimeUnitFormat( 10 );
		$this->assertEquals( '-01:00', TTDate::getTimeUnit( -3600 ) );
		$this->assertEquals( '-01:01', TTDate::getTimeUnit( -3660 ) );
		$this->assertEquals( '-10:01', TTDate::getTimeUnit( -36060 ) );
		$this->assertEquals( '-10:11', TTDate::getTimeUnit( -36660 ) );
		$this->assertEquals( '-100:11', TTDate::getTimeUnit( -360660 ) );
		$this->assertEquals( '-1000:11', TTDate::getTimeUnit( -3600660 ) );
		$this->assertEquals( '-10000:11', TTDate::getTimeUnit( -36000660 ) );
		$this->assertEquals( '-100000:11', TTDate::getTimeUnit( -360000660 ) );
		$this->assertEquals( '-1000000:11', TTDate::getTimeUnit( -3600000660 ) );
		//$this->assertEquals( TTDate::getTimeUnit( ( ( PHP_INT_MAX + PHP_INT_MAX ) * -1 ) ), 		'ERR(FLOAT)' );
		$this->assertEquals( '-5124095576030431:00', TTDate::getTimeUnit( bcmul( bcadd( PHP_INT_MAX, PHP_INT_MAX ), -1 ) ) );


		TTDate::setTimeUnitFormat( 12 );
		$this->assertEquals( '01:00:00', TTDate::getTimeUnit( 3600 ) );
		$this->assertEquals( '01:01:01', TTDate::getTimeUnit( 3661 ) );
		$this->assertEquals( '10:01:00', TTDate::getTimeUnit( 36060 ) );
		$this->assertEquals( '10:11:00', TTDate::getTimeUnit( 36660 ) );
		$this->assertEquals( '100:11:00', TTDate::getTimeUnit( 360660 ) );
		$this->assertEquals( '1000:11:00', TTDate::getTimeUnit( 3600660 ) );
		$this->assertEquals( '10000:11:00', TTDate::getTimeUnit( 36000660 ) );
		$this->assertEquals( '100000:11:00', TTDate::getTimeUnit( 360000660 ) );
		$this->assertEquals( '1000000:11:00', TTDate::getTimeUnit( 3600000660 ) );
		//$this->assertEquals( TTDate::getTimeUnit( ( PHP_INT_MAX + PHP_INT_MAX ) ), 	'ERR(FLOAT)' );
		$this->assertEquals( '5124095576030431:00:14', TTDate::getTimeUnit( bcadd( PHP_INT_MAX, PHP_INT_MAX ) ) );

		$this->assertEquals( '9223372036854775807:00:49', TTDate::getTimeUnit( bcmul( PHP_INT_MAX, PHP_INT_MAX ) ) );

		$this->assertEquals( '9223372036854775807:00:49', TTDate::getTimeUnit( bcadd( bcmul( PHP_INT_MAX, PHP_INT_MAX ), '0.99999' ) ) );
		$this->assertEquals( '9223372036854775807:00:49', TTDate::getTimeUnit( bcadd( bcmul( PHP_INT_MAX, PHP_INT_MAX ), '0.00001' ) ) ); // (float)0.00001 gets converted to scientific notation when casted to string, resulting in bcadd() throwing a non-well formed argument error.


		TTDate::setTimeUnitFormat( 12 );
		$this->assertEquals( '-01:00:00', TTDate::getTimeUnit( -3600 ) );
		$this->assertEquals( '-01:01:01', TTDate::getTimeUnit( -3661 ) );
		$this->assertEquals( '-10:01:00', TTDate::getTimeUnit( -36060 ) );
		$this->assertEquals( '-10:11:00', TTDate::getTimeUnit( -36660 ) );
		$this->assertEquals( '-100:11:00', TTDate::getTimeUnit( -360660 ) );
		$this->assertEquals( '-1000:11:00', TTDate::getTimeUnit( -3600660 ) );
		$this->assertEquals( '-10000:11:00', TTDate::getTimeUnit( -36000660 ) );
		$this->assertEquals( '-100000:11:00', TTDate::getTimeUnit( -360000660 ) );
		$this->assertEquals( '-1000000:11:00', TTDate::getTimeUnit( -3600000660 ) );
		//$this->assertEquals( TTDate::getTimeUnit( ( ( PHP_INT_MAX + PHP_INT_MAX ) * -1 ) ), 		'ERR(FLOAT)' );
		$this->assertEquals( '-5124095576030431:00:14', TTDate::getTimeUnit( bcmul( bcadd( PHP_INT_MAX, PHP_INT_MAX ), -1 ) ) );

		$this->assertEquals( '-9223372036854775807:00:49', TTDate::getTimeUnit( bcmul( bcmul( PHP_INT_MAX, PHP_INT_MAX ), -1 ) ) );


		TTDate::setTimeUnitFormat( 23 );
		$this->assertEquals( '1.0000', TTDate::getTimeUnit( 3600 ) );
		$this->assertEquals( '1.0167', TTDate::getTimeUnit( 3660 ) );
		$this->assertEquals( '10.0167', TTDate::getTimeUnit( 36060 ) );
		$this->assertEquals( '10.1833', TTDate::getTimeUnit( 36660 ) );
		$this->assertEquals( '100.1833', TTDate::getTimeUnit( 360660 ) );
		$this->assertEquals( '1,000.1833', TTDate::getTimeUnit( 3600660 ) );
		$this->assertEquals( '10,000.1833', TTDate::getTimeUnit( 36000660 ) );
		$this->assertEquals( '100,000.1833', TTDate::getTimeUnit( 360000660 ) );
		$this->assertEquals( '1,000,000.1833', TTDate::getTimeUnit( 3600000660 ) );
		$this->assertEquals( '5,124,095,576,030,431.0000', TTDate::getTimeUnit( ( PHP_INT_MAX + PHP_INT_MAX ) ) ); //This is passing a float that is losing precision.
		$this->assertEquals( '5,124,095,576,030,431.0000', TTDate::getTimeUnit( bcadd( PHP_INT_MAX, PHP_INT_MAX ) ) );
		$this->assertEquals( '5,124,095,576,030,431.0000', TTDate::getTimeUnit( bcadd( bcadd( PHP_INT_MAX, PHP_INT_MAX ), 660 ) ) );

		$this->assertEquals( '0.0000', TTDate::getTimeUnit( '' ) );
		$this->assertEquals( '0.0000', TTDate::getTimeUnit( '--' ) );
		$this->assertEquals( '0.0000', TTDate::getTimeUnit( 'XYZ' ) );
		$this->assertEquals( '0.0000', TTDate::getTimeUnit( null ) );
		$this->assertEquals( '0.0000', TTDate::getTimeUnit( false ) );
		$this->assertEquals( '0.0000', TTDate::getTimeUnit( true ) );
	}

	function testDate_DMY_1() {
		Debug::text( 'Testing Date Format: d-M-y', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setDateFormat( 'd-M-y' );
		TTDate::setTimeFormat( 'g:i A' );

		$this->assertEquals( 1109318400, TTDate::parseDateTime( '25-Feb-05' ) );

		$this->assertEquals( 1109390940, TTDate::parseDateTime( '25-Feb-05 8:09PM' ) );
		$this->assertEquals( 1109347740, TTDate::parseDateTime( '25-Feb-05 8:09 AM' ) );
		$this->assertEquals( 1109347750, TTDate::parseDateTime( '25-Feb-05 8:09:10 AM' ) );
		$this->assertEquals( 1109336950, TTDate::parseDateTime( '25-Feb-05 8:09:10 AM EST' ) );

		$this->assertEquals( 1109383740, TTDate::parseDateTime( '25-Feb-05 18:09' ) );
		$this->assertEquals( 1109383750, TTDate::parseDateTime( '25-Feb-05 18:09:10' ) );
		$this->assertEquals( 1109372950, TTDate::parseDateTime( '25-Feb-05 18:09:10 EST' ) );

		//MST7MDT has been deprecated and does not work. Most 3 letter timezones have too.
		$this->assertEquals( 1109380150, TTDate::parseDateTime( '25-Feb-05 18:09:10 MST' ) );
		$this->assertEquals( 1109380150, TTDate::parseDateTime( '25-Feb-05 18:09:10 America/Edmonton' ) );


		//Fails on PHP 5.1.2 due to strtotime()
		//TTDate::setDateFormat('d/M/y');
		//TTDate::setTimeFormat('g:i A');

		//$this->assertEquals( TTDate::parseDateTime('25/Feb/05'), 1109318400 );

		//$this->assertEquals( TTDate::parseDateTime('25/Feb/05 8:09PM'), 1109390940 );
		//$this->assertEquals( TTDate::parseDateTime('25/Feb/05 8:09 AM'), 1109347740 );
		//$this->assertEquals( TTDate::parseDateTime('25/Feb/05 8:09:10 AM'), 1109347750 );
		//$this->assertEquals( TTDate::parseDateTime('25/Feb/05 8:09:10 AM EST'), 1109336950 );

		//$this->assertEquals( TTDate::parseDateTime('25/Feb/05 18:09'), 1109383740 );
		//$this->assertEquals( TTDate::parseDateTime('25/Feb/05 18:09:10'), 1109383750 );
		//$this->assertEquals( TTDate::parseDateTime('25/Feb/05 18:09:10 EST'), 1109372950 );


		TTDate::setDateFormat( 'd-M-Y' );
		TTDate::setTimeFormat( 'g:i A' );

		$this->assertEquals( 1109318400, TTDate::parseDateTime( '25-Feb-2005' ) );

		$this->assertEquals( 1109390940, TTDate::parseDateTime( '25-Feb-2005 8:09PM' ) );
		$this->assertEquals( 1109347740, TTDate::parseDateTime( '25-Feb-2005 8:09 AM' ) );
		$this->assertEquals( 1109347750, TTDate::parseDateTime( '25-Feb-2005 8:09:10 AM' ) );
		$this->assertEquals( 1109336950, TTDate::parseDateTime( '25-Feb-2005 8:09:10 AM EST' ) );

		$this->assertEquals( 1109383740, TTDate::parseDateTime( '25-Feb-2005 18:09' ) );
		$this->assertEquals( 1109383750, TTDate::parseDateTime( '25-Feb-2005 18:09:10' ) );
		$this->assertEquals( 1109372950, TTDate::parseDateTime( '25-Feb-2005 18:09:10 EST' ) );

		//Fails on PHP 5.1.2 due to strtotime()

		//TTDate::setDateFormat('d/M/Y');
		//TTDate::setTimeFormat('g:i A');

		//$this->assertEquals( TTDate::parseDateTime('25/Feb/2005'), 1109318400 );

		//$this->assertEquals( TTDate::parseDateTime('25/Feb/2005 8:09PM'), 1109390940 );
		//$this->assertEquals( TTDate::parseDateTime('25/Feb/2005 8:09 AM'), 1109347740 );
		//$this->assertEquals( TTDate::parseDateTime('25/Feb/2005 8:09:10 AM'), 1109347750 );
		//$this->assertEquals( TTDate::parseDateTime('25/Feb/2005 8:09:10 AM EST'), 1109336950 );

		//$this->assertEquals( TTDate::parseDateTime('25/Feb/2005 18:09'), 1109383740 );
		//$this->assertEquals( TTDate::parseDateTime('25/Feb/2005 18:09:10'), 1109383750 );
		//$this->assertEquals( TTDate::parseDateTime('25/Feb/2005 18:09:10 EST'), 1109372950 );
	}

	function testDate_DMY_2() {
		Debug::text( 'Testing Date Format: dMY', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setDateFormat( 'dMY' );
		TTDate::setTimeFormat( 'g:i A' );

		$this->assertEquals( 1109318400, TTDate::parseDateTime( '25Feb2005' ) );

		$this->assertEquals( 1109390940, TTDate::parseDateTime( '25Feb2005 8:09PM' ) );
		$this->assertEquals( 1109347740, TTDate::parseDateTime( '25Feb2005 8:09 AM' ) );
		$this->assertEquals( 1109347750, TTDate::parseDateTime( '25Feb2005 8:09:10 AM' ) );
		$this->assertEquals( 1109336950, TTDate::parseDateTime( '25Feb2005 8:09:10 AM EST' ) );

		$this->assertEquals( 1109383740, TTDate::parseDateTime( '25Feb2005 18:09' ) );
		$this->assertEquals( 1109383750, TTDate::parseDateTime( '25Feb2005 18:09:10' ) );
		$this->assertEquals( 1109372950, TTDate::parseDateTime( '25Feb2005 18:09:10 EST' ) );
	}

	function testDate_DMY_3() {
		Debug::text( 'Testing Date Format: d-m-y', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setDateFormat( 'd-m-y' );

		TTDate::setTimeFormat( 'g:i A' );
		$this->assertEquals( 1109318400, TTDate::parseDateTime( '25-02-2005' ) );
		$this->assertEquals( 1109390940, TTDate::parseDateTime( '25-02-2005 8:09PM' ) );
		$this->assertEquals( 1109347740, TTDate::parseDateTime( '25-02-2005 8:09 AM' ) );

		TTDate::setTimeFormat( 'g:i A T' );
		$this->assertEquals( 1109336940, TTDate::parseDateTime( '25-02-2005 8:09 AM EST' ) );

		TTDate::setTimeFormat( 'G:i' );
		$this->assertEquals( 1109383740, TTDate::parseDateTime( '25-02-2005 18:09' ) );
		$this->assertEquals( 1109372940, TTDate::parseDateTime( '25-02-2005 18:09 EST' ) );

		//
		// Different separator
		//

		TTDate::setDateFormat( 'd/m/y' );

		TTDate::setTimeFormat( 'g:i A' );
		$this->assertEquals( 1109318400, TTDate::parseDateTime( '25/02/2005' ) );
		$this->assertEquals( 1109390940, TTDate::parseDateTime( '25/02/2005 8:09PM' ) );
		$this->assertEquals( 1109347740, TTDate::parseDateTime( '25/02/2005 8:09 AM' ) );

		TTDate::setTimeFormat( 'g:i A T' );
		$this->assertEquals( 1109336940, TTDate::parseDateTime( '25/02/2005 8:09 AM EST' ) );

		TTDate::setTimeFormat( 'G:i' );
		$this->assertEquals( 1109383740, TTDate::parseDateTime( '25/02/2005 18:09' ) );
		$this->assertEquals( 1109372940, TTDate::parseDateTime( '25/02/2005 18:09 EST' ) );
	}

	function testDate_MDY_1() {
		Debug::text( 'Testing Date Format: m-d-y', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setDateFormat( 'm-d-y' );
		TTDate::setTimeZone( 'America/Vancouver' ); //Force to non-DST timezone. 'PST' isnt actually valid.

		TTDate::setTimeFormat( 'g:i A' );
		$this->assertEquals( 1109318400, TTDate::parseDateTime( '02-25-2005' ) );
		$this->assertEquals( 1109318400, TTDate::parseDateTime( '02-25-05' ) );

		$this->assertEquals( 1161932400, TTDate::parseDateTime( '10-27-06' ) );

		$this->assertEquals( 1109390940, TTDate::parseDateTime( '02-25-2005 8:09PM' ) );
		$this->assertEquals( 1109347740, TTDate::parseDateTime( '02-25-2005 8:09 AM' ) );

		TTDate::setTimeFormat( 'g:i A T' );
		$this->assertEquals( 1109336940, TTDate::parseDateTime( '02-25-2005 8:09 AM EST' ) );

		TTDate::setTimeFormat( 'G:i' );
		$this->assertEquals( 1109383740, TTDate::parseDateTime( '02-25-2005 18:09' ) );
		$this->assertEquals( 1109372940, TTDate::parseDateTime( '02-25-2005 18:09 EST' ) );

		//
		// Different separator
		//
		TTDate::setDateFormat( 'm/d/y' );

		TTDate::setTimeFormat( 'g:i A' );
		$this->assertEquals( 1109318400, TTDate::parseDateTime( '02/25/2005' ) );
		$this->assertEquals( 1109390940, TTDate::parseDateTime( '02/25/2005 8:09PM' ) );
		$this->assertEquals( 1109347740, TTDate::parseDateTime( '02/25/2005 8:09 AM' ) );

		TTDate::setTimeFormat( 'g:i A T' );
		$this->assertEquals( 1109336940, TTDate::parseDateTime( '02/25/2005 8:09 AM EST' ) );

		TTDate::setTimeFormat( 'G:i' );
		$this->assertEquals( 1109383740, TTDate::parseDateTime( '02/25/2005 18:09' ) );
		$this->assertEquals( 1109372940, TTDate::parseDateTime( '02/25/2005 18:09 EST' ) );
	}

	function testDate_MDY_2() {
		Debug::text( 'Testing Date Format: M-d-y', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setDateFormat( 'M-d-y' );

		TTDate::setTimeFormat( 'g:i A' );
		$this->assertEquals( 1109318400, TTDate::parseDateTime( 'Feb-25-2005' ) );
		$this->assertEquals( 1109318400, TTDate::parseDateTime( 'Feb-25-05' ) );
		$this->assertEquals( 1109390940, TTDate::parseDateTime( 'Feb-25-2005 8:09PM' ) );
		$this->assertEquals( 1109347740, TTDate::parseDateTime( 'Feb-25-2005 8:09 AM' ) );

		TTDate::setTimeFormat( 'g:i A T' );
		$this->assertEquals( 1109336940, TTDate::parseDateTime( 'Feb-25-2005 8:09 AM EST' ) );

		TTDate::setTimeFormat( 'G:i' );
		$this->assertEquals( 1109383740, TTDate::parseDateTime( 'Feb-25-2005 18:09' ) );
		$this->assertEquals( 1109372940, TTDate::parseDateTime( 'Feb-25-2005 18:09 EST' ) );
	}

	function testDate_MDY_3() {
		Debug::text( 'Testing Date Format: m-d-y (two digit year)', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setDateFormat( 'm-d-y' );

		TTDate::setTimeFormat( 'g:i A' );
		$this->assertEquals( 1109318400, TTDate::parseDateTime( '02-25-05' ) );
		$this->assertEquals( 1109390940, TTDate::parseDateTime( '02-25-05 8:09PM' ) );
		$this->assertEquals( 1109347740, TTDate::parseDateTime( '02-25-05 8:09 AM' ) );

		TTDate::setTimeFormat( 'g:i A T' );
		$this->assertEquals( 1109336940, TTDate::parseDateTime( '02-25-05 8:09 AM EST' ) );

		TTDate::setTimeFormat( 'G:i' );
		$this->assertEquals( 1109383740, TTDate::parseDateTime( '02-25-05 18:09' ) );
		$this->assertEquals( 1109372940, TTDate::parseDateTime( '02-25-05 18:09 EST' ) );

		//Try test before 1970, like 1920 - *1920 fails after 2010 has passed, try a different value.

		$this->assertEquals( -468604800, TTDate::parseDateTime( '02-25-55' ) );
		$this->assertEquals( -468532260, TTDate::parseDateTime( '02-25-55 8:09PM' ) );
		$this->assertEquals( -468575460, TTDate::parseDateTime( '02-25-55 8:09 AM' ) );
	}


	function testDate_YMD_1() {
		Debug::text( 'Testing Date Format: Y-m-d', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setDateFormat( 'Y-m-d' );

		TTDate::setTimeFormat( 'g:i A' );
		$this->assertEquals( 1109318400, TTDate::parseDateTime( '2005-02-25' ) );
		$this->assertEquals( 1109318400, TTDate::parseDateTime( '05-02-25' ) );
		$this->assertEquals( 1109390940, TTDate::parseDateTime( '2005-02-25 8:09PM' ) );
		$this->assertEquals( 1109347740, TTDate::parseDateTime( '2005-02-25 8:09 AM' ) );

		TTDate::setTimeFormat( 'g:i A T' );
		$this->assertEquals( 1109336940, TTDate::parseDateTime( '2005-02-25 8:09 AM EST' ) );

		TTDate::setTimeFormat( 'G:i' );
		$this->assertEquals( 1109383740, TTDate::parseDateTime( '2005-02-25 18:09' ) );
		$this->assertEquals( 1109372940, TTDate::parseDateTime( '2005-02-25 18:09 EST' ) );
	}

	function testTime1() {
		Debug::text( 'Testing Date Format: Y-m-d', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setDateFormat( 'Y-m-d' );

		TTDate::setTimeFormat( 'g:i A' );
		$this->assertEquals( 1109390940, TTDate::parseDateTime( '2005-02-25 8:09PM' ) );
		$this->assertEquals( 1109347740, TTDate::parseDateTime( '2005-02-25 8:09 AM' ) );
		$this->assertEquals( 1109347740, TTDate::parseDateTime( '2005-02-25 08:09 AM' ) );

		$this->assertEquals( 1109347740, TTDate::parseDateTime( '2005-02-25 08:09 A' ) );
		$this->assertEquals( 1109347740, TTDate::parseDateTime( '2005-02-25 8:09A' ) );
		$this->assertEquals( 1109347740, TTDate::parseDateTime( '2005-02-25 8:09A   ' ) );

		$this->assertEquals( 1109390940, TTDate::parseDateTime( '2005-02-25 8:09 P' ) );
		$this->assertEquals( 1109390940, TTDate::parseDateTime( '2005-02-25 8:09P' ) );
		$this->assertEquals( 1109390940, TTDate::parseDateTime( '2005-02-25 8:09P   ' ) );


		$this->assertEquals( 1109347740, TTDate::parseDateTime( '2005-02-25 0809 AM' ) );
		$this->assertEquals( 1109347740, TTDate::parseDateTime( '2005-02-25 0809 A' ) );

		$this->assertEquals( 1109390940, TTDate::parseDateTime( '2005-02-25 8:09 PM' ) );
		$this->assertEquals( 1109390940, TTDate::parseDateTime( '2005-02-25 0809 PM' ) );
		$this->assertEquals( 1109390940, TTDate::parseDateTime( '2005-02-25 0809 P' ) );

		$this->assertEquals( 1109347740, TTDate::parseDateTime( '2005-02-25 809 AM' ) );
		$this->assertEquals( 1109347740, TTDate::parseDateTime( '2005-02-25 809 A' ) );
		$this->assertEquals( 1109390940, TTDate::parseDateTime( '2005-02-25 809 PM' ) );
		$this->assertEquals( 1109390940, TTDate::parseDateTime( '2005-02-25 809 P' ) );

		$this->assertEquals( 1109357100, TTDate::parseDateTime( '2005-02-25 1045 AM' ) );
		$this->assertEquals( 1109357100, TTDate::parseDateTime( '2005-02-25 1045 A' ) );
		$this->assertEquals( 1109400300, TTDate::parseDateTime( '2005-02-25 1045 PM' ) );
		$this->assertEquals( 1109400300, TTDate::parseDateTime( '2005-02-25 1045 P' ) );

		$this->assertEquals( 1109357100, TTDate::parseDateTime( '2005-02-25 10:45 A PDT' ) ); //Test with timezone tacked on the end too.
		$this->assertEquals( 1109400300, TTDate::parseDateTime( '2005-02-25 10:45 P PDT' ) ); //Test with timezone tacked on the end too.

		$this->assertEquals( strtotime( '8:45 AM'), TTDate::parseDateTime( '845 A' ) );
		$this->assertEquals( strtotime( '8:45 AM'), TTDate::parseDateTime( '845 AM' ) );
		$this->assertEquals( strtotime( '8:45 PM'), TTDate::parseDateTime( '845 P' ) );
		$this->assertEquals( strtotime( '8:45 PM'), TTDate::parseDateTime( '845 PM' ) );

		$this->assertEquals( strtotime( '8:45 AM'), TTDate::parseDateTime( '0845 A' ) );
		$this->assertEquals( strtotime( '8:45 AM'), TTDate::parseDateTime( '0845 AM' ) );
		$this->assertEquals( strtotime( '8:45 PM'), TTDate::parseDateTime( '0845 P' ) );
		$this->assertEquals( strtotime( '8:45 PM'), TTDate::parseDateTime( '0845 PM' ) );

		$this->assertEquals( strtotime( '10:45 AM'), TTDate::parseDateTime( '1045 A' ) );
		$this->assertEquals( strtotime( '10:45 AM'), TTDate::parseDateTime( '1045 AM' ) );
		$this->assertEquals( strtotime( '10:45 PM'), TTDate::parseDateTime( '1045 P' ) );
		$this->assertEquals( strtotime( '10:45 PM'), TTDate::parseDateTime( '1045 PM' ) );

		$this->assertEquals( strtotime( '8:00 AM'), TTDate::parseDateTime( '8 A' ) );
		$this->assertEquals( strtotime( '8:00 AM'), TTDate::parseDateTime( '8A' ) );
		$this->assertEquals( strtotime( '8:00 PM'), TTDate::parseDateTime( '8 P' ) );
		$this->assertEquals( strtotime( '8:00 PM'), TTDate::parseDateTime( '8P' ) );

		$this->assertEquals( strtotime( '8:00 AM'), TTDate::parseDateTime( '8 a' ) );
		$this->assertEquals( strtotime( '8:00 AM'), TTDate::parseDateTime( '8a' ) );
		$this->assertEquals( strtotime( '8:00 PM'), TTDate::parseDateTime( '8 p' ) );
		$this->assertEquals( strtotime( '8:00 PM'), TTDate::parseDateTime( '8p' ) );
	}

	function test_getDayOfNextWeek() {
		Debug::text( 'Testing Date Format: Y-m-d', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setDateFormat( 'Y-m-d' );

		TTDate::setTimeFormat( 'g:i A' );
		$this->assertEquals( TTDate::getDateOfNextDayOfWeek( strtotime( '29-Dec-06' ), strtotime( '27-Dec-06' ) ), strtotime( '03-Jan-07' ) );
		$this->assertEquals( TTDate::getDateOfNextDayOfWeek( strtotime( '25-Dec-06' ), strtotime( '28-Dec-06' ) ), strtotime( '28-Dec-06' ) );
		$this->assertEquals( TTDate::getDateOfNextDayOfWeek( strtotime( '31-Dec-06' ), strtotime( '25-Dec-06' ) ), strtotime( '01-Jan-07' ) );
	}

	function test_getDateOfNextDayOfMonth() {
		Debug::text( 'Testing Date Format: Y-m-d', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setDateFormat( 'Y-m-d' );

		TTDate::setTimeFormat( 'g:i A' );
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime( '01-Dec-06' ), strtotime( '02-Dec-06' ) ), strtotime( '02-Dec-06' ) );
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime( '14-Dec-06' ), strtotime( '23-Nov-06' ) ), strtotime( '23-Dec-06' ) );
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime( '14-Dec-06' ), strtotime( '13-Dec-06' ) ), strtotime( '13-Jan-07' ) );
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime( '14-Dec-06' ), strtotime( '14-Dec-06' ) ), strtotime( '14-Dec-06' ) );
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime( '14-Dec-06 12:00:00 PM' ), strtotime( '14-Dec-06 12:00:00 PM' ) ), strtotime( '14-Dec-06 12:00:00 AM' ) ); //Always returns beginning of day epoch.
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime( '14-Dec-06 12:00:00 PM' ), strtotime( '14-Dec-06 12:01:00 PM' ) ), strtotime( '14-Dec-06 12:00:00 AM' ) ); //Always returns beginning of day epoch.
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime( '14-Dec-06 12:01:00 PM' ), strtotime( '14-Dec-06 12:00:00 PM' ) ), strtotime( '14-Dec-06 12:00:00 AM' ) ); //Always returns beginning of day epoch.
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime( '12-Dec-06' ), strtotime( '01-Dec-04' ) ), strtotime( '01-Jan-07' ) );

		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime( '12-Dec-06' ), null, 1 ), strtotime( '01-Jan-07' ) );
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime( '12-Dec-06' ), null, 12 ), strtotime( '12-Dec-06' ) );
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime( '12-Dec-06' ), null, 31 ), strtotime( '31-Dec-06' ) );

		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime( '01-Feb-07' ), null, 31 ), strtotime( '28-Feb-07' ) );
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime( '01-Feb-08' ), null, 29 ), strtotime( '29-Feb-08' ) );
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime( '01-Feb-08' ), null, 31 ), strtotime( '29-Feb-08' ) );

		//Anchor Epoch: 09-Apr-04 11:59 PM PDT Day Of Month Epoch:  Day Of Month: 24<br>
		$this->assertEquals( TTDate::getDateOfNextDayOfMonth( strtotime( '09-Apr-04' ), null, 24 ), strtotime( '24-Apr-04' ) );
	}

	function test_parseEpoch() {
		Debug::text( 'Testing Date Parsing of EPOCH!', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setDateFormat( 'm-d-y' );

		TTDate::setTimeFormat( 'g:i A' );
		$this->assertEquals( TTDate::parseDateTime( 1162670400 ), (int)1162670400 );


		TTDate::setDateFormat( 'Y-m-d' );

		TTDate::setTimeFormat( 'g:i A' );
		$this->assertEquals( TTDate::parseDateTime( 1162670400 ), (int)1162670400 );

		$this->assertEquals( 600, TTDate::parseDateTime( 600 ) );    //Test small epochs that may conflict with 24hr time that just has the time and not a date.
		$this->assertEquals( 1800, TTDate::parseDateTime( 1800 ) );  //Test small epochs that may conflict with 24hr time that just has the time and not a date.

		$this->assertEquals( -600, TTDate::parseDateTime( -600 ) );   //Test small epochs that may conflict with 24hr time that just has the time and not a date.
		$this->assertEquals( -1800, TTDate::parseDateTime( -1800 ) ); //Test small epochs that may conflict with 24hr time that just has the time and not a date.
	}

	function test_roundTime() {
		//10 = Down
		//20 = Average
		//30 = Up

		//Test rounding down by 15minutes
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:06 AM' ), ( 60 * 15 ), 10 ), strtotime( '15-Apr-07 8:00 AM' ) );
		//Test rounding down by 5minutes
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:06 AM' ), ( 60 * 5 ), 10 ), strtotime( '15-Apr-07 8:05 AM' ) );
		//Test rounding down by 5minutes when no rounding should occur.
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:05 AM' ), ( 60 * 5 ), 10 ), strtotime( '15-Apr-07 8:05 AM' ) );

		//Test rounding down by 15minutes with 3minute grace.
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 4:58 PM' ), ( 60 * 15 ), 10, ( 60 * 3 ) ), strtotime( '15-Apr-07 5:00 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 4:56 PM' ), ( 60 * 15 ), 10, ( 60 * 3 ) ), strtotime( '15-Apr-07 4:45 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:11 PM' ), ( 60 * 15 ), 10, ( 60 * 3 ) ), strtotime( '15-Apr-07 5:00 PM' ) );
		//Test rounding down by 5minutes with 2minute grace
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:11 PM' ), ( 60 * 5 ), 10, ( 60 * 2 ) ), strtotime( '15-Apr-07 5:10 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:07 PM' ), ( 60 * 5 ), 10, ( 60 * 2 ) ), strtotime( '15-Apr-07 5:05 PM' ) );


		//Test rounding avg by 15minutes
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:06 AM' ), ( 60 * 15 ), 20 ), strtotime( '15-Apr-07 8:00 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:06:59 AM' ), ( 60 * 15 ), 20 ), strtotime( '15-Apr-07 8:00 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:07:29 AM' ), ( 60 * 15 ), 20 ), strtotime( '15-Apr-07 8:00 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:07:30 AM' ), ( 60 * 15 ), 20 ), strtotime( '15-Apr-07 8:15 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:07:59 AM' ), ( 60 * 15 ), 20 ), strtotime( '15-Apr-07 8:15 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:08 AM' ), ( 60 * 15 ), 20 ), strtotime( '15-Apr-07 8:15 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:08:01 AM' ), ( 60 * 15 ), 20 ), strtotime( '15-Apr-07 8:15 AM' ) );

		//Test rounding avg by 5minutes
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:06 AM' ), ( 60 * 5 ), 20 ), strtotime( '15-Apr-07 8:05 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:07 AM' ), ( 60 * 5 ), 20 ), strtotime( '15-Apr-07 8:05 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:07:01 AM' ), ( 60 * 5 ), 20 ), strtotime( '15-Apr-07 8:05 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:07:29 AM' ), ( 60 * 5 ), 20 ), strtotime( '15-Apr-07 8:05 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:07:30 AM' ), ( 60 * 5 ), 20 ), strtotime( '15-Apr-07 8:10 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:07:31 AM' ), ( 60 * 5 ), 20 ), strtotime( '15-Apr-07 8:10 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:07:59 AM' ), ( 60 * 5 ), 20 ), strtotime( '15-Apr-07 8:10 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:08 AM' ), ( 60 * 5 ), 20 ), strtotime( '15-Apr-07 8:10 AM' ) );
		//Test rounding avg by 5minutes when no rounding should occur.
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:05 AM' ), ( 60 * 5 ), 20 ), strtotime( '15-Apr-07 8:05 AM' ) );

		//Test rounding avg by 1minute -- This is another special case that we have to be exactly proper rounding for.
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:00:00 AM' ), ( 60 * 1 ), 20 ), strtotime( '15-Apr-07 8:00 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:00:01 AM' ), ( 60 * 1 ), 20 ), strtotime( '15-Apr-07 8:00 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:00:29 AM' ), ( 60 * 1 ), 20 ), strtotime( '15-Apr-07 8:00 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:00:30 AM' ), ( 60 * 1 ), 20 ), strtotime( '15-Apr-07 8:01 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:00:31 AM' ), ( 60 * 1 ), 20 ), strtotime( '15-Apr-07 8:01 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:00:59 AM' ), ( 60 * 1 ), 20 ), strtotime( '15-Apr-07 8:01 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:01:00 AM' ), ( 60 * 1 ), 20 ), strtotime( '15-Apr-07 8:01 AM' ) );


		//When doing a 15min average rounding, US law states 7mins and 59 seconds can be rounded down in favor of the employer, and 8mins and 0 seconds must be rounded up. See TTDate::roundTime() for more details.
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:06 AM' ), ( 60 * 15 ), 27 ), strtotime( '15-Apr-07 8:00 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:06:59 AM' ), ( 60 * 15 ), 27 ), strtotime( '15-Apr-07 8:00 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:07:00 AM' ), ( 60 * 15 ), 27 ), strtotime( '15-Apr-07 8:15 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:07:29 AM' ), ( 60 * 15 ), 27 ), strtotime( '15-Apr-07 8:15 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:07:30 AM' ), ( 60 * 15 ), 27 ), strtotime( '15-Apr-07 8:15 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:07:59 AM' ), ( 60 * 15 ), 27 ), strtotime( '15-Apr-07 8:15 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:08 AM' ), ( 60 * 15 ), 27 ), strtotime( '15-Apr-07 8:15 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:08:01 AM' ), ( 60 * 15 ), 27 ), strtotime( '15-Apr-07 8:15 AM' ) );

		//When doing a 15min average rounding, US law states 7mins and 59 seconds can be rounded down in favor of the employer, and 8mins and 0 seconds must be rounded up. See TTDate::roundTime() for more details.
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:05:01 PM' ), ( 60 * 15 ), 25 ), strtotime( '15-Apr-07 5:00 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:06 PM' ), ( 60 * 15 ), 25 ), strtotime( '15-Apr-07 5:00 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:06:59 PM' ), ( 60 * 15 ), 25 ), strtotime( '15-Apr-07 5:00 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:07:29 PM' ), ( 60 * 15 ), 25 ), strtotime( '15-Apr-07 5:00 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:07:30 PM' ), ( 60 * 15 ), 25 ), strtotime( '15-Apr-07 5:00 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:07:59 PM' ), ( 60 * 15 ), 25 ), strtotime( '15-Apr-07 5:00 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:08 PM' ), ( 60 * 15 ), 25 ), strtotime( '15-Apr-07 5:15 PM' ) );

		//When doing a 15min average rounding, US law states 7mins and 59 seconds can be rounded down in favor of the employer, and 8mins and 0 seconds must be rounded up. See TTDate::roundTime() for more details.
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:06 PM' ), ( 60 * 5 ), 25 ), strtotime( '15-Apr-07 5:05 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:07 PM' ), ( 60 * 5 ), 25 ), strtotime( '15-Apr-07 5:05 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:07:01 PM' ), ( 60 * 5 ), 25 ), strtotime( '15-Apr-07 5:05 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:07:29 PM' ), ( 60 * 5 ), 25 ), strtotime( '15-Apr-07 5:05 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:07:30 PM' ), ( 60 * 5 ), 25 ), strtotime( '15-Apr-07 5:05 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:07:31 PM' ), ( 60 * 5 ), 25 ), strtotime( '15-Apr-07 5:05 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:07:59 PM' ), ( 60 * 5 ), 25 ), strtotime( '15-Apr-07 5:05 PM' ) );


		//Test rounding avg by 1minute -- This is another special case that we have to be exactly proper rounding for.
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:00:00 PM' ), ( 60 * 1 ), 27 ), strtotime( '15-Apr-07 5:00 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:00:01 PM' ), ( 60 * 1 ), 27 ), strtotime( '15-Apr-07 5:00 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:00:29 PM' ), ( 60 * 1 ), 27 ), strtotime( '15-Apr-07 5:00 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:00:30 PM' ), ( 60 * 1 ), 27 ), strtotime( '15-Apr-07 5:01 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:00:31 PM' ), ( 60 * 1 ), 27 ), strtotime( '15-Apr-07 5:01 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:00:59 PM' ), ( 60 * 1 ), 27 ), strtotime( '15-Apr-07 5:01 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:01:00 PM' ), ( 60 * 1 ), 27 ), strtotime( '15-Apr-07 5:01 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:00:00 PM' ), ( 60 * 1 ), 25 ), strtotime( '15-Apr-07 5:00 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:00:01 PM' ), ( 60 * 1 ), 25 ), strtotime( '15-Apr-07 5:00 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:00:29 PM' ), ( 60 * 1 ), 25 ), strtotime( '15-Apr-07 5:00 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:00:30 PM' ), ( 60 * 1 ), 25 ), strtotime( '15-Apr-07 5:01 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:00:31 PM' ), ( 60 * 1 ), 25 ), strtotime( '15-Apr-07 5:01 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:00:59 PM' ), ( 60 * 1 ), 25 ), strtotime( '15-Apr-07 5:01 PM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 5:01:00 PM' ), ( 60 * 1 ), 25 ), strtotime( '15-Apr-07 5:01 PM' ) );


		//Test rounding up by 15minutes
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:06 AM' ), ( 60 * 15 ), 30 ), strtotime( '15-Apr-07 8:15 AM' ) );
		//Test rounding up by 5minutes
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:06 AM' ), ( 60 * 5 ), 30 ), strtotime( '15-Apr-07 8:10 AM' ) );
		//Test rounding up by 5minutes when no rounding should occur.
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:05 AM' ), ( 60 * 5 ), 30 ), strtotime( '15-Apr-07 8:05 AM' ) );

		//Test rounding up by 15minutes with 3minute grace.
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:01 AM' ), ( 60 * 15 ), 30, ( 60 * 3 ) ), strtotime( '15-Apr-07 8:00 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:04 AM' ), ( 60 * 15 ), 30, ( 60 * 3 ) ), strtotime( '15-Apr-07 8:15 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:03 AM' ), ( 60 * 15 ), 30, ( 60 * 3 ) ), strtotime( '15-Apr-07 8:00 AM' ) );
		//Test rounding up by 5minutes with 2minute grace
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:03 AM' ), ( 60 * 5 ), 30, ( 60 * 2 ) ), strtotime( '15-Apr-07 8:05 AM' ) );
		$this->assertEquals( (int)TTDate::roundTime( strtotime( '15-Apr-07 8:01 AM' ), ( 60 * 5 ), 30, ( 60 * 2 ) ), strtotime( '15-Apr-07 8:00 AM' ) );


		//Test time units
		$this->assertEquals( (int)TTDate::roundTime( TTDate::parseTimeUnit( '1:05:00' ), ( 60 * 15 ), 20 ), TTDate::parseTimeUnit( '1:00' ) );
		$this->assertEquals( (int)TTDate::roundTime( TTDate::parseTimeUnit( '1:07:29' ), ( 60 * 15 ), 20 ), TTDate::parseTimeUnit( '1:00' ) );
		$this->assertEquals( (int)TTDate::roundTime( TTDate::parseTimeUnit( '1:07:30' ), ( 60 * 15 ), 20 ), TTDate::parseTimeUnit( '1:15' ) );
		$this->assertEquals( (int)TTDate::roundTime( TTDate::parseTimeUnit( '1:07:31' ), ( 60 * 15 ), 20 ), TTDate::parseTimeUnit( '1:15' ) );
		$this->assertEquals( (int)TTDate::roundTime( TTDate::parseTimeUnit( '1:07:59' ), ( 60 * 15 ), 20 ), TTDate::parseTimeUnit( '1:15' ) );

		//Test time units with negative values.
		$this->assertEquals( (int)TTDate::roundTime( TTDate::parseTimeUnit( '-1:05:00' ), ( 60 * 15 ), 20 ), TTDate::parseTimeUnit( '-1:00' ) );
		$this->assertEquals( (int)TTDate::roundTime( TTDate::parseTimeUnit( '-1:07:29' ), ( 60 * 15 ), 20 ), TTDate::parseTimeUnit( '-1:00' ) );
		$this->assertEquals( (int)TTDate::roundTime( TTDate::parseTimeUnit( '-1:07:30' ), ( 60 * 15 ), 20 ), TTDate::parseTimeUnit( '-1:15' ) );
		$this->assertEquals( (int)TTDate::roundTime( TTDate::parseTimeUnit( '-1:07:31' ), ( 60 * 15 ), 20 ), TTDate::parseTimeUnit( '-1:15' ) );
		$this->assertEquals( (int)TTDate::roundTime( TTDate::parseTimeUnit( '-1:07:59' ), ( 60 * 15 ), 20 ), TTDate::parseTimeUnit( '-1:15' ) );

		$this->assertEquals( (int)TTDate::roundTime( ( TTDate::parseTimeUnit( '1:05:00' ) * -1 ), ( 60 * 15 ), 20 ), ( TTDate::parseTimeUnit( '1:00' ) * -1 ) );
		$this->assertEquals( (int)TTDate::roundTime( ( TTDate::parseTimeUnit( '1:07:29' ) * -1 ), ( 60 * 15 ), 20 ), ( TTDate::parseTimeUnit( '1:00' ) * -1 ) );
		$this->assertEquals( (int)TTDate::roundTime( ( TTDate::parseTimeUnit( '1:07:30' ) * -1 ), ( 60 * 15 ), 20 ), ( TTDate::parseTimeUnit( '1:15' ) * -1 ) );
		$this->assertEquals( (int)TTDate::roundTime( ( TTDate::parseTimeUnit( '1:07:31' ) * -1 ), ( 60 * 15 ), 20 ), ( TTDate::parseTimeUnit( '1:15' ) * -1 ) );
		$this->assertEquals( (int)TTDate::roundTime( ( TTDate::parseTimeUnit( '1:07:59' ) * -1 ), ( 60 * 15 ), 20 ), ( TTDate::parseTimeUnit( '1:15' ) * -1 ) );

		$this->assertEquals( TTDate::roundTime( 90.12345, 60, 10 ), (int)60 ); //Make sure partial seconds are stripped off.
		$this->assertEquals( TTDate::roundTime( 90.00001, 60, 10 ), (int)60 ); //Make sure partial seconds are stripped off.
		$this->assertEquals( TTDate::roundTime( 90.99999, 60, 10 ), (int)60 ); //Make sure partial seconds are stripped off.

		//Test rounding with negative grace times.
		$this->assertEquals( TTDate::roundTime( strtotime( '15-Apr-07 8:00 AM' ), ( 60 * 15 ), 10, 0 ), strtotime( '15-Apr-07 8:00 AM' ) ); //10=Down
		$this->assertEquals( TTDate::roundTime( strtotime( '15-Apr-07 8:00 AM' ), ( 60 * 15 ), 10, 60 ), strtotime( '15-Apr-07 8:00 AM' ) ); //10=Down
		$this->assertEquals( TTDate::roundTime( strtotime( '15-Apr-07 8:00 AM' ), ( 60 * 15 ), 10, -60 ), strtotime( '15-Apr-07 7:45 AM' ) ); //10=Down

		$this->assertEquals( TTDate::roundTime( strtotime( '15-Apr-07 8:00 AM' ), ( 60 * 15 ), 30, 0 ), strtotime( '15-Apr-07 8:00 AM' ) ); //30=Up
		$this->assertEquals( TTDate::roundTime( strtotime( '15-Apr-07 8:00 AM' ), ( 60 * 15 ), 30, 60 ), strtotime( '15-Apr-07 8:00 AM' ) ); //30=Up
		$this->assertEquals( TTDate::roundTime( strtotime( '15-Apr-07 8:00 AM' ), ( 60 * 15 ), 30, -60 ), strtotime( '15-Apr-07 8:15 AM' ) ); //30=Up
	}

	function test_graceTime() {
		$this->assertEquals( (int)TTDate::graceTime( strtotime( '15-Apr-07 7:58 AM' ), ( 60 * 5 ), strtotime( '15-Apr-07 8:00 AM' ) ), strtotime( '15-Apr-07 8:00 AM' ) );
		$this->assertEquals( (int)TTDate::graceTime( strtotime( '15-Apr-07 7:58:23 AM' ), ( 60 * 5 ), strtotime( '15-Apr-07 8:00 AM' ) ), strtotime( '15-Apr-07 8:00 AM' ) );
	}

	function test_calculateTimeOnEachDayBetweenRange() {
		$test1_result = TTDate::calculateTimeOnEachDayBetweenRange( strtotime( '01-Jan-09 8:00AM' ), strtotime( '01-Jan-09 11:30PM' ) );
		$this->assertCount( 1, $test1_result );
		$this->assertEquals( 55800, $test1_result[1230796800] );

		$test2_result = TTDate::calculateTimeOnEachDayBetweenRange( strtotime( '01-Jan-09 4:00PM' ), strtotime( '02-Jan-09 8:00AM' ) );
		$this->assertCount( 2, $test2_result );
		$this->assertEquals( 28800, $test2_result[1230796800] );
		$this->assertEquals( 28800, $test2_result[1230883200] );

		$test3_result = TTDate::calculateTimeOnEachDayBetweenRange( strtotime( '01-Jan-09 4:00PM' ), strtotime( '03-Jan-09 8:00AM' ) );
		$this->assertCount( 3, $test3_result );
		$this->assertEquals( 28800, $test3_result[1230796800] );
		$this->assertEquals( 86400, $test3_result[1230883200] );
		$this->assertEquals( 28800, $test3_result[1230969600] );

		$test4_result = TTDate::calculateTimeOnEachDayBetweenRange( strtotime( '01-Jan-09 4:00PM' ), strtotime( '9-Jan-09 8:00AM' ) );
		$this->assertCount( 9, $test4_result );
		$this->assertEquals( 28800, $test4_result[1230796800] );
		$this->assertEquals( 86400, $test4_result[1230883200] );
		$this->assertEquals( 86400, $test4_result[1230969600] );
		$this->assertEquals( 86400, $test4_result[1231056000] );
		$this->assertEquals( 86400, $test4_result[1231142400] );
		$this->assertEquals( 86400, $test4_result[1231228800] );
		$this->assertEquals( 86400, $test4_result[1231315200] );
		$this->assertEquals( 86400, $test4_result[1231401600] );
		$this->assertEquals( 28800, $test4_result[1231488000] );

		$test5_result = TTDate::calculateTimeOnEachDayBetweenRange( strtotime( '01-Jan-09 12:00AM' ), strtotime( '01-Jan-09 12:59:59PM' ) );
		$this->assertCount( 1, $test5_result );
		$this->assertEquals( 46799, $test5_result[1230796800] );

		$test5_result = TTDate::calculateTimeOnEachDayBetweenRange( strtotime( '01-Jan-09 12:00AM' ), strtotime( '02-Jan-09 12:00AM' ) );
		$this->assertCount( 1, $test5_result );
		$this->assertEquals( 86400, $test5_result[1230796800] );

		$test5_result = TTDate::calculateTimeOnEachDayBetweenRange( strtotime( '01-Jan-09 12:01AM' ), strtotime( '02-Jan-09 12:01AM' ) );
		$this->assertCount( 2, $test5_result );
		$this->assertEquals( 86340, $test5_result[1230796800] );
		$this->assertEquals( 60, $test5_result[1230883200] );

		$test5_result = TTDate::calculateTimeOnEachDayBetweenRange( strtotime( '01-Jan-09 1:53PM' ), strtotime( '03-Jan-09 6:12AM' ) );
		$this->assertCount( 3, $test5_result );
		$this->assertEquals( 36420, $test5_result[1230796800] );
		$this->assertEquals( 86400, $test5_result[1230883200] );
		$this->assertEquals( 22320, $test5_result[1230969600] );
	}

	function test_calculateFiscalYearFromEpoch() {
		/*
		For example, the United States government fiscal year for 2016 is:

		1st quarter: 1 October 2015 – 31 December 2015
		2nd quarter: 1 January 2016 – 31 March 2016
		3rd quarter: 1 April 2016 – 30 June 2016
		4th quarter: 1 July 2016 – 30 September 2016
		*/

		$this->assertEquals( 2015, TTDate::getFiscalYearFromEpoch( strtotime( '30-Sep-2015 12:00AM' ), 'US' ) );
		$this->assertEquals( 2016, TTDate::getFiscalYearFromEpoch( strtotime( '01-Oct-2015 12:00AM' ), 'US' ) );
		$this->assertEquals( 2016, TTDate::getFiscalYearFromEpoch( strtotime( '01-Jan-2016 8:00AM' ), 'US' ) );
		$this->assertEquals( 2016, TTDate::getFiscalYearFromEpoch( strtotime( '29-Sep-2016 12:00AM' ), 'US' ) );
		$this->assertEquals( 2016, TTDate::getFiscalYearFromEpoch( strtotime( '30-Sep-2016 12:00AM' ), 'US' ) );
		$this->assertEquals( 2016, TTDate::getFiscalYearFromEpoch( strtotime( '30-Sep-2016 11:59:59PM' ), 'US' ) );
		$this->assertEquals( 2017, TTDate::getFiscalYearFromEpoch( strtotime( '01-Oct-2016 12:00AM' ), 'US' ) );

		$this->assertEquals( 2016, TTDate::getFiscalYearFromEpoch( strtotime( '30-Sep-2016 12:00AM' ), 'US' ) );
		$this->assertEquals( 2017, TTDate::getFiscalYearFromEpoch( strtotime( '01-Oct-2016 12:00AM' ), 'US' ) );
		$this->assertEquals( 2017, TTDate::getFiscalYearFromEpoch( strtotime( '01-Jan-2017 8:00AM' ), 'US' ) );
		$this->assertEquals( 2017, TTDate::getFiscalYearFromEpoch( strtotime( '29-Sep-2017 12:00AM' ), 'US' ) );
		$this->assertEquals( 2017, TTDate::getFiscalYearFromEpoch( strtotime( '30-Sep-2017 12:00AM' ), 'US' ) );
		$this->assertEquals( 2017, TTDate::getFiscalYearFromEpoch( strtotime( '30-Sep-2017 11:59:59PM' ), 'US' ) );
		$this->assertEquals( 2018, TTDate::getFiscalYearFromEpoch( strtotime( '01-Oct-2017 12:00AM' ), 'US' ) );

		/*
		In Canada,[9] the government's financial year runs from 1 April to 31 March (Example 1 April 2015 to 31 March 2016 for the current financial year).
		 */
		$this->assertEquals( 2014, TTDate::getFiscalYearFromEpoch( strtotime( '31-Mar-2015 12:00AM' ), 'CA' ) );
		$this->assertEquals( 2015, TTDate::getFiscalYearFromEpoch( strtotime( '01-Apr-2015 12:00AM' ), 'CA' ) );
		$this->assertEquals( 2015, TTDate::getFiscalYearFromEpoch( strtotime( '31-Dec-2015 8:00AM' ), 'CA' ) );
		$this->assertEquals( 2015, TTDate::getFiscalYearFromEpoch( strtotime( '31-Mar-2016 11:59AM' ), 'CA' ) );
		$this->assertEquals( 2016, TTDate::getFiscalYearFromEpoch( strtotime( '01-Apr-2016 12:00AM' ), 'CA' ) );
	}

	function test_getWeek() {
		//Match up with PHP's function
		$date1 = strtotime( '01-Nov-09 12:00PM' );
		$this->assertEquals( 44, date( 'W', $date1 ) );
		$this->assertEquals( 44, TTDate::getWeek( $date1, 1 ) );

		$date1 = strtotime( '02-Nov-09 12:00PM' );
		$this->assertEquals( 45, date( 'W', $date1 ) );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 1 ) );

		$date1 = strtotime( '03-Nov-09 12:00PM' );
		$this->assertEquals( 45, date( 'W', $date1 ) );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 1 ) );

		$date1 = strtotime( '04-Nov-09 12:00PM' );
		$this->assertEquals( 45, date( 'W', $date1 ) );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 1 ) );

		$date1 = strtotime( '05-Nov-09 12:00PM' );
		$this->assertEquals( 45, date( 'W', $date1 ) );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 1 ) );

		$date1 = strtotime( '06-Nov-09 12:00PM' );
		$this->assertEquals( 45, date( 'W', $date1 ) );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 1 ) );

		$date1 = strtotime( '07-Nov-09 12:00PM' );
		$this->assertEquals( 45, date( 'W', $date1 ) );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 1 ) );

		$date1 = strtotime( '08-Nov-09 12:00PM' );
		$this->assertEquals( 45, date( 'W', $date1 ) );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 1 ) );

		$date1 = strtotime( '09-Nov-09 12:00PM' );
		$this->assertEquals( 46, date( 'W', $date1 ) );
		$this->assertEquals( 46, TTDate::getWeek( $date1, 1 ) );

		//Test with Sunday as start day of week.
		$date1 = strtotime( '01-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 0 ) );

		$date1 = strtotime( '02-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 0 ) );

		$date1 = strtotime( '03-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 0 ) );

		$date1 = strtotime( '04-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 0 ) );

		$date1 = strtotime( '05-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 0 ) );

		$date1 = strtotime( '06-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 0 ) );

		$date1 = strtotime( '07-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 0 ) );

		$date1 = strtotime( '08-Nov-09 12:00PM' );
		$this->assertEquals( 46, TTDate::getWeek( $date1, 0 ) );

		$date1 = strtotime( '09-Nov-09 12:00PM' );
		$this->assertEquals( 46, TTDate::getWeek( $date1, 0 ) );


		//Test with Tuesday as start day of week.
		$date1 = strtotime( '01-Nov-09 12:00PM' );
		$this->assertEquals( 44, TTDate::getWeek( $date1, 2 ) );

		$date1 = strtotime( '02-Nov-09 12:00PM' );
		$this->assertEquals( 44, TTDate::getWeek( $date1, 2 ) );

		$date1 = strtotime( '03-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 2 ) );

		$date1 = strtotime( '04-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 2 ) );

		$date1 = strtotime( '05-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 2 ) );

		$date1 = strtotime( '06-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 2 ) );

		$date1 = strtotime( '07-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 2 ) );

		$date1 = strtotime( '08-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 2 ) );

		$date1 = strtotime( '09-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 2 ) );

		$date1 = strtotime( '10-Nov-09 12:00PM' );
		$this->assertEquals( 46, TTDate::getWeek( $date1, 2 ) );

		$date1 = strtotime( '11-Nov-09 12:00PM' );
		$this->assertEquals( 46, TTDate::getWeek( $date1, 2 ) );


		//Test with Wed as start day of week.
		$date1 = strtotime( '03-Nov-09 12:00PM' );
		$this->assertEquals( 44, TTDate::getWeek( $date1, 3 ) );

		$date1 = strtotime( '04-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 3 ) );

		$date1 = strtotime( '05-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 3 ) );

		//Test with Thu as start day of week.
		$date1 = strtotime( '04-Nov-09 12:00PM' );
		$this->assertEquals( 44, TTDate::getWeek( $date1, 4 ) );

		$date1 = strtotime( '05-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 4 ) );

		$date1 = strtotime( '06-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 4 ) );

		//Test with Fri as start day of week.
		$date1 = strtotime( '05-Nov-09 12:00PM' );
		$this->assertEquals( 44, TTDate::getWeek( $date1, 5 ) );

		$date1 = strtotime( '06-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 5 ) );

		$date1 = strtotime( '07-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 5 ) );

		//Test with Sat as start day of week.
		$date1 = strtotime( '06-Nov-09 12:00PM' );
		$this->assertEquals( 44, TTDate::getWeek( $date1, 6 ) );

		$date1 = strtotime( '07-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 6 ) );

		$date1 = strtotime( '08-Nov-09 12:00PM' );
		$this->assertEquals( 45, TTDate::getWeek( $date1, 6 ) );

		//Test with different years
		$date1 = strtotime( '31-Dec-09 12:00PM' );
		$this->assertEquals( 53, TTDate::getWeek( $date1, 1 ) );

		$date1 = strtotime( '01-Jan-10 12:00PM' );
		$this->assertEquals( 53, TTDate::getWeek( $date1, 1 ) );

		$date1 = strtotime( '04-Jan-10 12:00PM' );
		$this->assertEquals( 1, TTDate::getWeek( $date1, 1 ) );

		$date1 = strtotime( '03-Jan-10 12:00PM' );
		$this->assertEquals( 1, TTDate::getWeek( $date1, 0 ) );

		$date1 = strtotime( '09-Jan-10 12:00PM' );
		$this->assertEquals( 1, TTDate::getWeek( $date1, 6 ) );


		//Start on Monday as thats what PHP uses.
		for ( $i = strtotime( '07-Jan-13' ); $i < strtotime( '06-Jan-13' ); $i += ( 86400 * 7 ) ) {
			$this->assertEquals( TTDate::getWeek( $i, 1 ), date( 'W', $i ) );
		}

		//Start on Sunday.
		$this->assertEquals( 52, TTDate::getWeek( strtotime( '29-Dec-12' ), 0 ) );
		$this->assertEquals( 1, TTDate::getWeek( strtotime( '30-Dec-12' ), 0 ) );
		$this->assertEquals( 1, TTDate::getWeek( strtotime( '31-Dec-12' ), 0 ) );
		$this->assertEquals( 1, TTDate::getWeek( strtotime( '01-Jan-12' ), 0 ) );
		$this->assertEquals( 1, TTDate::getWeek( strtotime( '02-Jan-12' ), 0 ) );
		$this->assertEquals( 1, TTDate::getWeek( strtotime( '03-Jan-12' ), 0 ) );
		$this->assertEquals( 1, TTDate::getWeek( strtotime( '04-Jan-12' ), 0 ) );
		$this->assertEquals( 1, TTDate::getWeek( strtotime( '05-Jan-12' ), 0 ) );
		$this->assertEquals( 2, TTDate::getWeek( strtotime( '06-Jan-13' ), 0 ) );

		$this->assertEquals( 15, TTDate::getWeek( strtotime( '09-Apr-13' ), 0 ) );
		$this->assertEquals( 26, TTDate::getWeek( strtotime( '28-Jun-13' ), 0 ) );

		//Start on every other day of the week
		$this->assertEquals( 25, TTDate::getWeek( strtotime( '28-Jun-13' ), 6 ) );
		$this->assertEquals( 25, TTDate::getWeek( strtotime( '27-Jun-13' ), 5 ) );
		$this->assertEquals( 25, TTDate::getWeek( strtotime( '26-Jun-13' ), 4 ) );
		$this->assertEquals( 25, TTDate::getWeek( strtotime( '25-Jun-13' ), 3 ) );
		$this->assertEquals( 25, TTDate::getWeek( strtotime( '24-Jun-13' ), 2 ) );
		$this->assertEquals( 25, TTDate::getWeek( strtotime( '23-Jun-13' ), 1 ) );
		$this->assertEquals( 25, TTDate::getWeek( strtotime( '22-Jun-13' ), 0 ) );
	}

	function test_getNearestWeekDay() {
		$date1 = strtotime( '16-Jan-2010 12:00PM' ); //Sat
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 0 ), strtotime( '16-Jan-2010 12:00PM' ) );
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 1 ), strtotime( '15-Jan-2010 12:00PM' ) );
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 2 ), strtotime( '18-Jan-2010 12:00PM' ) );
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 3 ), strtotime( '15-Jan-2010 12:00PM' ) );
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 10 ), strtotime( '16-Jan-2010 12:00PM' ) ); //Split Sat=Sat, Sun=Mon
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 20 ), strtotime( '15-Jan-2010 12:00PM' ) ); //Split Sat=Fri, Sun=Sun

		$date2 = strtotime( '17-Jan-2010 12:00PM' ); //Sun
		$this->assertEquals( TTDate::getNearestWeekDay( $date2, 3 ), strtotime( '18-Jan-2010 12:00PM' ) );
		$this->assertEquals( TTDate::getNearestWeekDay( $date2, 10 ), strtotime( '18-Jan-2010 12:00PM' ) ); //Split Sat=Sat, Sun=Mon
		$this->assertEquals( TTDate::getNearestWeekDay( $date2, 20 ), strtotime( '17-Jan-2010 12:00PM' ) ); //Split Sat=Fri, Sun=Sun

		$holidays = [
				TTDate::getBeginDayEpoch( strtotime( '15-Jan-2010' ) ),
		];
		$date1 = strtotime( '16-Jan-2010 12:00PM' );
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 3, $holidays ), strtotime( '14-Jan-2010 12:00PM' ) );

		$holidays = [
				TTDate::getBeginDayEpoch( strtotime( '15-Jan-2010' ) ),
				TTDate::getBeginDayEpoch( strtotime( '14-Jan-2010' ) ),
		];
		$date1 = strtotime( '16-Jan-2010 12:00PM' );
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 1, $holidays ), strtotime( '13-Jan-2010 12:00PM' ) );
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 3, $holidays ), strtotime( '18-Jan-2010 12:00PM' ) );

		$holidays = [
				TTDate::getBeginDayEpoch( strtotime( '15-Jan-2010' ) ),
				TTDate::getBeginDayEpoch( strtotime( '14-Jan-2010' ) ),
				TTDate::getBeginDayEpoch( strtotime( '18-Jan-2010' ) ),
		];
		$date1 = strtotime( '16-Jan-2010 12:00PM' );
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 1, $holidays ), strtotime( '13-Jan-2010 12:00PM' ) );
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 3, $holidays ), strtotime( '13-Jan-2010 12:00PM' ) );

		$holidays = [
				TTDate::getBeginDayEpoch( strtotime( '15-Jan-2010' ) ),
				TTDate::getBeginDayEpoch( strtotime( '14-Jan-2010' ) ),
				TTDate::getBeginDayEpoch( strtotime( '13-Jan-2010' ) ),
				TTDate::getBeginDayEpoch( strtotime( '18-Jan-2010' ) ),
		];
		$date1 = strtotime( '16-Jan-2010 12:00PM' );
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 1, $holidays ), strtotime( '12-Jan-2010 12:00PM' ) );
		$this->assertEquals( TTDate::getNearestWeekDay( $date1, 3, $holidays ), strtotime( '19-Jan-2010 12:00PM' ) );

		//Make sure we don't get into any infinite loops if a non-epoch value is passed in.
		$this->assertEquals( TTDate::getNearestWeekDay( false, 0 ), false );
		$this->assertEquals( TTDate::getNearestWeekDay( true, 0 ), true );
		$this->assertEquals( TTDate::getNearestWeekDay( null, 0 ), null );
		$this->assertEquals( TTDate::getNearestWeekDay( '', 0 ), '' );
		$this->assertEquals( TTDate::getNearestWeekDay( 0, 0 ), 0 );

		$this->assertEquals( TTDate::getNearestWeekDay( false, 3 ), false );
		$this->assertEquals( TTDate::getNearestWeekDay( true, 3 ), true );
		$this->assertEquals( TTDate::getNearestWeekDay( null, 3 ), null );
		$this->assertEquals( TTDate::getNearestWeekDay( '', 3 ), '' );
		$this->assertEquals( TTDate::getNearestWeekDay( 0, 3 ), 0 );
	}

	function test_timePeriodDates() {
		Debug::text( 'Testing Time Period Dates!', __FILE__, __LINE__, __METHOD__, 10 );
		TTDate::setTimeZone( 'America/Vancouver' );

		$dates = TTDate::getTimePeriodDates( 'custom_date', strtotime( '15-Jul-10 12:00 PM' ), null, [ 'start_date' => strtotime( '10-Jul-10 12:43 PM' ), 'end_date' => strtotime( '12-Jul-10 12:43 PM' ) ] );
		$this->assertEquals( $dates['start_date'], (int)1278745200 );
		$this->assertEquals( $dates['end_date'], (int)1279004399 );

		$dates = TTDate::getTimePeriodDates( 'custom_time', strtotime( '15-Jul-10 12:00 PM' ), null, [ 'start_date' => strtotime( '10-Jul-10 12:43 PM' ), 'end_date' => strtotime( '12-Jul-10 12:53 PM' ) ] );
		$this->assertEquals( $dates['start_date'], (int)1278790980 );
		$this->assertEquals( $dates['end_date'], (int)1278964380 );

		$dates = TTDate::getTimePeriodDates( 'today', strtotime( '15-Jul-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1279177200 );
		$this->assertEquals( $dates['end_date'], (int)1279263599 );

		$dates = TTDate::getTimePeriodDates( 'yesterday', strtotime( '15-Jul-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1279090800 );
		$this->assertEquals( $dates['end_date'], (int)1279177199 );

		$dates = TTDate::getTimePeriodDates( 'last_24_hours', strtotime( '15-Jul-10 12:43 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1279136580 );
		$this->assertEquals( $dates['end_date'], (int)1279222980 );

		$dates = TTDate::getTimePeriodDates( 'this_week', strtotime( '15-Jul-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1278831600 );
		$this->assertEquals( $dates['end_date'], (int)1279436399 );

		$dates = TTDate::getTimePeriodDates( 'last_week', strtotime( '15-Jul-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1278226800 );
		$this->assertEquals( $dates['end_date'], (int)1278831599 );

		$dates = TTDate::getTimePeriodDates( 'last_7_days', strtotime( '15-Jul-10 12:43 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1278572400 );
		$this->assertEquals( $dates['end_date'], (int)1279177199 );

		$dates = TTDate::getTimePeriodDates( 'this_month', strtotime( '15-Jul-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1277967600 );
		$this->assertEquals( $dates['end_date'], (int)1280645999 );

		$dates = TTDate::getTimePeriodDates( 'last_month', strtotime( '15-Jul-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1275375600 );
		$this->assertEquals( $dates['end_date'], (int)1277967599 );

		$dates = TTDate::getTimePeriodDates( 'last_month', strtotime( '15-Mar-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1265011200 );
		$this->assertEquals( $dates['end_date'], (int)1267430399 );

		$dates = TTDate::getTimePeriodDates( 'last_30_days', strtotime( '15-Jul-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1276585200 );
		$this->assertEquals( $dates['end_date'], (int)1279177199 );

		$dates = TTDate::getTimePeriodDates( 'this_quarter', strtotime( '15-Jul-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1277967600 );
		$this->assertEquals( $dates['end_date'], (int)1285916399 );

		$dates = TTDate::getTimePeriodDates( 'last_quarter', strtotime( '15-Jul-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1270105200 );
		$this->assertEquals( $dates['end_date'], (int)1277967599 );

		$dates = TTDate::getTimePeriodDates( 'last_90_days', strtotime( '15-Jul-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1271401200 );
		$this->assertEquals( $dates['end_date'], (int)1279177199 );

		$dates = TTDate::getTimePeriodDates( 'this_year_1st_quarter', strtotime( '15-Jul-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1262332800 );
		$this->assertEquals( $dates['end_date'], (int)1270105199 );

		$dates = TTDate::getTimePeriodDates( 'this_year_2nd_quarter', strtotime( '15-Jul-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1270105200 );
		$this->assertEquals( $dates['end_date'], (int)1277967599 );

		$dates = TTDate::getTimePeriodDates( 'this_year_3rd_quarter', strtotime( '15-Jul-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1277967600 );
		$this->assertEquals( $dates['end_date'], (int)1285916399 );

		$dates = TTDate::getTimePeriodDates( 'this_year_4th_quarter', strtotime( '15-Jul-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1285916400 );
		$this->assertEquals( $dates['end_date'], (int)1293868799 );

		$dates = TTDate::getTimePeriodDates( 'last_year_1st_quarter', strtotime( '15-Jul-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1230796800 );
		$this->assertEquals( $dates['end_date'], (int)1238569199 );

		$dates = TTDate::getTimePeriodDates( 'last_year_2nd_quarter', strtotime( '15-Jul-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1238569200 );
		$this->assertEquals( $dates['end_date'], (int)1246431599 );

		$dates = TTDate::getTimePeriodDates( 'last_year_3rd_quarter', strtotime( '15-Jul-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1246431600 );
		$this->assertEquals( $dates['end_date'], (int)1254380399 );

		$dates = TTDate::getTimePeriodDates( 'last_year_4th_quarter', strtotime( '15-Jul-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1254380400 );
		$this->assertEquals( $dates['end_date'], (int)1262332799 );

		$dates = TTDate::getTimePeriodDates( 'last_3_months', strtotime( '15-May-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1266134400 );
		$this->assertEquals( $dates['end_date'], (int)1273906799 );

		$dates = TTDate::getTimePeriodDates( 'last_6_months', strtotime( '15-May-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1258185600 );
		$this->assertEquals( $dates['end_date'], (int)1273906799 );

		$dates = TTDate::getTimePeriodDates( 'last_9_months', strtotime( '15-May-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1250233200 );
		$this->assertEquals( $dates['end_date'], (int)1273906799 );

		$dates = TTDate::getTimePeriodDates( 'last_12_months', strtotime( '15-Jul-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1247554800 );
		$this->assertEquals( $dates['end_date'], (int)1279177199 );

		$dates = TTDate::getTimePeriodDates( 'this_year', strtotime( '15-Jul-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1262332800 );
		$this->assertEquals( $dates['end_date'], (int)1293868799 );

		$dates = TTDate::getTimePeriodDates( 'last_year', strtotime( '15-Jul-10 12:00 PM' ) );
		$this->assertEquals( $dates['start_date'], (int)1230796800 );
		$this->assertEquals( $dates['end_date'], (int)1262332799 );
	}

	function test_getEndWeekEpoch() {
		TTDate::setTimeZone( 'America/Vancouver' ); //Force to timezone with DST.

		$this->assertEquals( strtotime( '11-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '11-May-2019 12:00AM' ), 0 ) ); //Week starts on Sunday
		$this->assertEquals( strtotime( '18-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '12-May-2019 12:00AM' ), 0 ) ); //Week starts on Sunday
		$this->assertEquals( strtotime( '18-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '13-May-2019 12:00AM' ), 0 ) ); //Week starts on Sunday
		$this->assertEquals( strtotime( '18-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '14-May-2019 12:00AM' ), 0 ) ); //Week starts on Sunday
		$this->assertEquals( strtotime( '18-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '15-May-2019 12:00AM' ), 0 ) ); //Week starts on Sunday
		$this->assertEquals( strtotime( '18-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '16-May-2019 12:00AM' ), 0 ) ); //Week starts on Sunday
		$this->assertEquals( strtotime( '18-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '17-May-2019 12:00AM' ), 0 ) ); //Week starts on Sunday
		$this->assertEquals( strtotime( '18-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '18-May-2019 12:00AM' ), 0 ) ); //Week starts on Sunday
		$this->assertEquals( strtotime( '25-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '19-May-2019 12:00AM' ), 0 ) ); //Week starts on Sunday

		$this->assertEquals( strtotime( '12-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '12-May-2019 12:00AM' ), 1 ) ); //Week starts on Monday
		$this->assertEquals( strtotime( '19-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '13-May-2019 12:00AM' ), 1 ) ); //Week starts on Monday
		$this->assertEquals( strtotime( '19-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '14-May-2019 12:00AM' ), 1 ) ); //Week starts on Monday
		$this->assertEquals( strtotime( '19-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '15-May-2019 12:00AM' ), 1 ) ); //Week starts on Monday
		$this->assertEquals( strtotime( '19-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '16-May-2019 12:00AM' ), 1 ) ); //Week starts on Monday
		$this->assertEquals( strtotime( '19-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '17-May-2019 12:00AM' ), 1 ) ); //Week starts on Monday
		$this->assertEquals( strtotime( '19-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '18-May-2019 12:00AM' ), 1 ) ); //Week starts on Monday
		$this->assertEquals( strtotime( '19-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '19-May-2019 12:00AM' ), 1 ) ); //Week starts on Monday
		$this->assertEquals( strtotime( '26-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '20-May-2019 12:00AM' ), 1 ) ); //Week starts on Monday

		$this->assertEquals( strtotime( '13-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '13-May-2019 12:00AM' ), 2 ) ); //Week starts on Tuesday
		$this->assertEquals( strtotime( '20-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '14-May-2019 12:00AM' ), 2 ) ); //Week starts on Tuesday
		$this->assertEquals( strtotime( '20-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '15-May-2019 12:00AM' ), 2 ) ); //Week starts on Tuesday
		$this->assertEquals( strtotime( '20-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '16-May-2019 12:00AM' ), 2 ) ); //Week starts on Tuesday
		$this->assertEquals( strtotime( '20-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '17-May-2019 12:00AM' ), 2 ) ); //Week starts on Tuesday
		$this->assertEquals( strtotime( '20-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '18-May-2019 12:00AM' ), 2 ) ); //Week starts on Tuesday
		$this->assertEquals( strtotime( '20-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '19-May-2019 12:00AM' ), 2 ) ); //Week starts on Tuesday
		$this->assertEquals( strtotime( '20-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '20-May-2019 12:00AM' ), 2 ) ); //Week starts on Tuesday
		$this->assertEquals( strtotime( '27-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '21-May-2019 12:00AM' ), 2 ) ); //Week starts on Tuesday

		$this->assertEquals( strtotime( '14-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '14-May-2019 12:00AM' ), 3 ) ); //Week starts on Wednesday
		$this->assertEquals( strtotime( '21-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '15-May-2019 12:00AM' ), 3 ) ); //Week starts on Wednesday
		$this->assertEquals( strtotime( '21-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '16-May-2019 12:00AM' ), 3 ) ); //Week starts on Wednesday
		$this->assertEquals( strtotime( '21-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '17-May-2019 12:00AM' ), 3 ) ); //Week starts on Wednesday
		$this->assertEquals( strtotime( '21-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '18-May-2019 12:00AM' ), 3 ) ); //Week starts on Wednesday
		$this->assertEquals( strtotime( '21-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '19-May-2019 12:00AM' ), 3 ) ); //Week starts on Wednesday
		$this->assertEquals( strtotime( '21-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '20-May-2019 12:00AM' ), 3 ) ); //Week starts on Wednesday
		$this->assertEquals( strtotime( '21-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '21-May-2019 12:00AM' ), 3 ) ); //Week starts on Wednesday
		$this->assertEquals( strtotime( '28-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '22-May-2019 12:00AM' ), 3 ) ); //Week starts on Wednesday

		$this->assertEquals( strtotime( '17-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '17-May-2019 12:00AM' ), 6 ) ); //Week starts on Saturday
		$this->assertEquals( strtotime( '24-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '18-May-2019 12:00AM' ), 6 ) ); //Week starts on Saturday
		$this->assertEquals( strtotime( '24-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '19-May-2019 12:00AM' ), 6 ) ); //Week starts on Saturday
		$this->assertEquals( strtotime( '24-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '20-May-2019 12:00AM' ), 6 ) ); //Week starts on Saturday
		$this->assertEquals( strtotime( '24-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '21-May-2019 12:00AM' ), 6 ) ); //Week starts on Saturday
		$this->assertEquals( strtotime( '24-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '22-May-2019 12:00AM' ), 6 ) ); //Week starts on Saturday
		$this->assertEquals( strtotime( '24-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '23-May-2019 12:00AM' ), 6 ) ); //Week starts on Saturday
		$this->assertEquals( strtotime( '24-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '24-May-2019 12:00AM' ), 6 ) ); //Week starts on Saturday
		$this->assertEquals( strtotime( '31-May-2019 11:59:59PM' ), TTDate::getEndWeekEpoch( strtotime( '25-May-2019 12:00AM' ), 6 ) ); //Week starts on Saturday
	}

	function test_getYearQuarters() {
		$quarters = TTDate::getYearQuarters( strtotime( '01-Jan-2019' ) );
		$this->assertCount( 4, $quarters );
		$this->assertEquals( strtotime( '01-Jan-2019 12:00AM' ), $quarters[1]['start'] );
		$this->assertEquals( strtotime( '31-Mar-2019 11:59:59PM' ), $quarters[1]['end'] );
		$this->assertEquals( strtotime( '01-Apr-2019 12:00AM' ), $quarters[2]['start'] );
		$this->assertEquals( strtotime( '30-Jun-2019 11:59:59PM' ), $quarters[2]['end'] );
		$this->assertEquals( strtotime( '01-Jul-2019 12:00AM' ), $quarters[3]['start'] );
		$this->assertEquals( strtotime( '30-Sep-2019 11:59:59PM' ), $quarters[3]['end'] );
		$this->assertEquals( strtotime( '01-Oct-2019 12:00AM' ), $quarters[4]['start'] );
		$this->assertEquals( strtotime( '31-Dec-2019 11:59:59PM' ), $quarters[4]['end'] );


		$quarters = TTDate::getYearQuarters( strtotime( '01-Jan-2019' ), 1 );
		$this->assertCount( 2, $quarters );
		$this->assertEquals( strtotime( '01-Jan-2019 12:00AM' ), $quarters['start'] );
		$this->assertEquals( strtotime( '31-Mar-2019 11:59:59PM' ), $quarters['end'] );


		$quarters = TTDate::getYearQuarters( strtotime( '01-Jan-2019' ), 2 );
		$this->assertCount( 2, $quarters );
		$this->assertEquals( strtotime( '01-Apr-2019 12:00AM' ), $quarters['start'] );
		$this->assertEquals( strtotime( '30-Jun-2019 11:59:59PM' ), $quarters['end'] );
	}

	function test_DST() {
		TTDate::setTimeZone( 'America/Vancouver' ); //Force to timezone with DST.

		$this->assertEquals( false, TTDate::doesRangeSpanDST( strtotime( '03-Nov-12 10:00PM' ), strtotime( '04-Nov-12 1:59AM' ) ) );
		$this->assertEquals( true, TTDate::doesRangeSpanDST( strtotime( '03-Nov-12 10:00PM' ), strtotime( '04-Nov-12 2:00AM' ) ) );
		$this->assertEquals( true, TTDate::doesRangeSpanDST( strtotime( '03-Nov-12 10:00PM' ), strtotime( '04-Nov-12 2:01AM' ) ) );
		$this->assertEquals( false, TTDate::doesRangeSpanDST( strtotime( '04-Nov-12 12:30AM' ), strtotime( '04-Nov-12 1:59AM' ) ) );
		$this->assertEquals( true, TTDate::doesRangeSpanDST( strtotime( '04-Nov-12 12:30AM' ), strtotime( '04-Nov-12 2:00AM' ) ) );
		$this->assertEquals( true, TTDate::doesRangeSpanDST( strtotime( '04-Nov-12 1:00AM' ), strtotime( '04-Nov-12 6:30AM' ) ) );


		$this->assertEquals( false, TTDate::doesRangeSpanDST( strtotime( '09-Mar-13 10:00PM' ), strtotime( '10-Mar-13 1:59AM' ) ) );
		$this->assertEquals( true, TTDate::doesRangeSpanDST( strtotime( '09-Mar-13 10:00PM' ), strtotime( '10-Mar-13 2:00AM' ) ) );
		$this->assertEquals( true, TTDate::doesRangeSpanDST( strtotime( '09-Mar-13 10:00PM' ), strtotime( '10-Mar-13 2:01AM' ) ) );
		$this->assertEquals( false, TTDate::doesRangeSpanDST( strtotime( '10-Mar-13 12:30AM' ), strtotime( '10-Mar-13 1:59AM' ) ) );
		$this->assertEquals( true, TTDate::doesRangeSpanDST( strtotime( '10-Mar-13 12:30AM' ), strtotime( '10-Mar-13 2:00AM' ) ) );
		$this->assertEquals( true, TTDate::doesRangeSpanDST( strtotime( '10-Mar-13 1:30AM' ), strtotime( '10-Mar-13 6:30AM' ) ) );


		$this->assertEquals( 0, TTDate::getDSTOffset( strtotime( '03-Nov-12 10:00PM' ), strtotime( '04-Nov-12 1:59AM' ) ) );
		$this->assertEquals( -3600, TTDate::getDSTOffset( strtotime( '03-Nov-12 10:00PM' ), strtotime( '04-Nov-12 2:00AM' ) ) );
		$this->assertEquals( -3600, TTDate::getDSTOffset( strtotime( '03-Nov-12 10:00PM' ), strtotime( '04-Nov-12 2:01AM' ) ) );
		$this->assertEquals( 0, TTDate::getDSTOffset( strtotime( '04-Nov-12 12:30AM' ), strtotime( '04-Nov-12 1:59AM' ) ) );
		$this->assertEquals( -3600, TTDate::getDSTOffset( strtotime( '04-Nov-12 12:30AM' ), strtotime( '04-Nov-12 2:00AM' ) ) );
		$this->assertEquals( -3600, TTDate::getDSTOffset( strtotime( '04-Nov-12 1:00AM' ), strtotime( '04-Nov-12 6:30AM' ) ) );


		$this->assertEquals( 0, TTDate::getDSTOffset( strtotime( '09-Mar-13 10:00PM' ), strtotime( '10-Mar-13 1:59AM' ) ) );
		$this->assertEquals( 3600, TTDate::getDSTOffset( strtotime( '09-Mar-13 10:00PM' ), strtotime( '10-Mar-13 2:00AM' ) ) );
		$this->assertEquals( 3600, TTDate::getDSTOffset( strtotime( '09-Mar-13 10:00PM' ), strtotime( '10-Mar-13 2:01AM' ) ) );
		$this->assertEquals( 0, TTDate::getDSTOffset( strtotime( '10-Mar-13 12:30AM' ), strtotime( '10-Mar-13 1:59AM' ) ) );
		$this->assertEquals( 3600, TTDate::getDSTOffset( strtotime( '10-Mar-13 12:30AM' ), strtotime( '10-Mar-13 2:00AM' ) ) );
		$this->assertEquals( 3600, TTDate::getDSTOffset( strtotime( '10-Mar-13 1:30AM' ), strtotime( '10-Mar-13 6:30AM' ) ) );


		//This is a quirk with PHP assuming that PST/PDT both mean PST8PDT, and since 05-Nov-2016 is the day DST changed, it adds an hour and uses PDT timezone instead.
		//  Not really testing anything useful here, other than confirming this quirk exists.
		$this->assertEquals( '04-Nov-16 6:00 PM PDT', TTDate::getDate( 'DATE+TIME', strtotime( '04-Nov-2016 6:00PM PDT' ) ) );
		$this->assertEquals( '04-Nov-16 7:00 PM PDT', TTDate::getDate( 'DATE+TIME', strtotime( '04-Nov-2016 6:00PM PST' ) ) );
		$this->assertEquals( '05-Nov-16 7:00 AM PDT', TTDate::getDate( 'DATE+TIME', strtotime( '05-Nov-2016 6:00AM PST' ) ) );
		$this->assertEquals( '06-Nov-16 6:00 AM PST', TTDate::getDate( 'DATE+TIME', strtotime( '06-Nov-2016 6:00AM PST' ) ) );
		$this->assertEquals( '06-Nov-16 5:00 AM PST', TTDate::getDate( 'DATE+TIME', strtotime( '06-Nov-2016 6:00AM PDT' ) ) );
	}

	function test_inApplyFrequencyWindow() {
		//Annually
		$frequency_criteria = [
				'month'         => 1,
				'day_of_month'  => 2,
				'day_of_week'   => 0,
				//'quarter' => 0,
				'quarter_month' => 0,
				'date'          => 0,
		];

		//No range
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 20, strtotime( '01-Jan-2010' ), strtotime( '01-Jan-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 20, strtotime( '02-Jan-2010' ), strtotime( '02-Jan-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 20, strtotime( '03-Jan-2010' ), strtotime( '03-Jan-2010' ), $frequency_criteria ) );
		//Range
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 20, strtotime( '01-Jan-2010' ), strtotime( '03-Jan-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 20, strtotime( '02-Jan-2010 12:00PM' ), strtotime( '02-Jan-2010 12:00PM' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 20, strtotime( '02-Jan-2010 12:00AM' ), strtotime( '02-Jan-2010 11:59PM' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 20, TTDate::incrementDate( strtotime( '01-Jan-2010' ), -7, 'day' ), strtotime( '01-Jan-2010' ), $frequency_criteria ) ); //Range
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 20, TTDate::incrementDate( strtotime( '02-Jan-2010' ), -7, 'day' ), strtotime( '02-Jan-2010' ), $frequency_criteria ) );  //Range
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 20, TTDate::incrementDate( strtotime( '03-Jan-2010' ), -7, 'day' ), strtotime( '03-Jan-2010' ), $frequency_criteria ) );  //Range
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 20, TTDate::incrementDate( strtotime( '04-Jan-2010' ), -7, 'day' ), strtotime( '04-Jan-2010' ), $frequency_criteria ) );  //Range
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 20, TTDate::incrementDate( strtotime( '05-Jan-2010' ), -7, 'day' ), strtotime( '05-Jan-2010' ), $frequency_criteria ) );  //Range
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 20, TTDate::incrementDate( strtotime( '06-Jan-2010' ), -7, 'day' ), strtotime( '06-Jan-2010' ), $frequency_criteria ) );  //Range
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 20, TTDate::incrementDate( strtotime( '07-Jan-2010' ), -7, 'day' ), strtotime( '07-Jan-2010' ), $frequency_criteria ) );  //Range
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 20, TTDate::incrementDate( strtotime( '08-Jan-2010' ), -7, 'day' ), strtotime( '08-Jan-2010' ), $frequency_criteria ) );  //Range
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 20, TTDate::incrementDate( strtotime( '09-Jan-2010' ), -7, 'day' ), strtotime( '09-Jan-2010' ), $frequency_criteria ) );  //Range
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 20, TTDate::incrementDate( strtotime( '10-Jan-2010' ), -7, 'day' ), strtotime( '10-Jan-2010' ), $frequency_criteria ) ); //Range


		//Quarterly
		$frequency_criteria = [
				'month'         => 0,
				'day_of_month'  => 15,
				'day_of_week'   => 0,
				//'quarter' => 3,
				'quarter_month' => 2,
				'date'          => 0,
		];

		//No range
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 25, strtotime( '14-Feb-2010' ), strtotime( '14-Feb-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 25, strtotime( '15-Feb-2010' ), strtotime( '15-Feb-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 25, strtotime( '16-Feb-2010' ), strtotime( '16-Feb-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 25, strtotime( '14-May-2010' ), strtotime( '14-May-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 25, strtotime( '15-May-2010' ), strtotime( '15-May-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 25, strtotime( '16-May-2010' ), strtotime( '16-May-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 25, strtotime( '14-Aug-2010' ), strtotime( '14-Aug-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 25, strtotime( '15-Aug-2010' ), strtotime( '15-Aug-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 25, strtotime( '16-Aug-2010' ), strtotime( '16-Aug-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 25, strtotime( '14-Nov-2010' ), strtotime( '14-Nov-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 25, strtotime( '15-Nov-2010' ), strtotime( '15-Nov-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 25, strtotime( '16-Nov-2010' ), strtotime( '16-Nov-2010' ), $frequency_criteria ) );

		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 25, strtotime( '14-Jan-2010' ), strtotime( '14-Jan-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 25, strtotime( '14-Mar-2010' ), strtotime( '14-Mar-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 25, strtotime( '14-Apr-2010' ), strtotime( '14-Apr-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 25, strtotime( '14-Jun-2010' ), strtotime( '14-Jun-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 25, strtotime( '14-Jul-2010' ), strtotime( '14-Jul-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 25, strtotime( '14-Sep-2010' ), strtotime( '14-Sep-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 25, strtotime( '14-Oct-2010' ), strtotime( '14-Oct-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 25, strtotime( '14-Dec-2010' ), strtotime( '14-Dec-2010' ), $frequency_criteria ) );

		//Range
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 25, strtotime( '01-Aug-2010' ), strtotime( '15-Aug-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 25, strtotime( '15-Aug-2010' ), strtotime( '20-Aug-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 25, strtotime( '16-Aug-2010' ), strtotime( '20-Aug-2010' ), $frequency_criteria ) );

		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 25, strtotime( '01-Jul-2010' ), strtotime( '15-Aug-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 25, strtotime( '01-Jun-2010' ), strtotime( '15-Aug-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 25, strtotime( '01-May-2010' ), strtotime( '15-Aug-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 25, strtotime( '01-Apr-2010' ), strtotime( '15-Aug-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 25, strtotime( '01-Apr-2009' ), strtotime( '15-Aug-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 25, strtotime( '16-Aug-2009' ), strtotime( '14-Dec-2010' ), $frequency_criteria ) );

		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 25, strtotime( '19-Aug-2009' ), strtotime( '14-Nov-2009' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 25, strtotime( '19-Aug-2009' ), strtotime( '15-Nov-2009' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 25, strtotime( '19-Aug-2009' ), strtotime( '15-Nov-2010' ), $frequency_criteria ) );

		//Monthly
		$frequency_criteria = [
				'month'         => 2,
				'day_of_month'  => 31,
				'day_of_week'   => 0,
				//'quarter' => 0,
				'quarter_month' => 0,
				'date'          => 0,
		];

		//No range
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 30, strtotime( '27-Feb-2010' ), strtotime( '27-Feb-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 30, strtotime( '28-Feb-2010' ), strtotime( '28-Feb-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 30, strtotime( '01-Mar-2010' ), strtotime( '01-Mar-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 30, strtotime( '27-Feb-2011' ), strtotime( '27-Feb-2011' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 30, strtotime( '28-Feb-2011' ), strtotime( '28-Feb-2011' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 30, strtotime( '01-Mar-2011' ), strtotime( '01-Mar-2011' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 30, strtotime( '28-Feb-2012' ), strtotime( '28-Feb-2012' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 30, strtotime( '29-Feb-2012' ), strtotime( '29-Feb-2012' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 30, strtotime( '01-Mar-2012' ), strtotime( '01-Mar-2012' ), $frequency_criteria ) );

		//Range
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 30, strtotime( '28-Feb-2010' ), strtotime( '05-Mar-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 30, strtotime( '22-Feb-2010' ), strtotime( '28-Feb-2010' ), $frequency_criteria ) );


		//Weekly
		$frequency_criteria = [
				'month'         => 0,
				'day_of_month'  => 0,
				'day_of_week'   => 2, //Tuesday
				//'quarter' => 0,
				'quarter_month' => 0,
				'date'          => 0,
		];

		//No range
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 40, strtotime( '12-Apr-2010' ), strtotime( '12-Apr-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 40, strtotime( '13-Apr-2010' ), strtotime( '13-Apr-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 40, strtotime( '14-Apr-2010' ), strtotime( '14-Apr-2010' ), $frequency_criteria ) );

		//Range
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 40, strtotime( '07-Apr-2010' ), strtotime( '12-Apr-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 40, strtotime( '14-Apr-2010' ), strtotime( '19-Apr-2010' ), $frequency_criteria ) );

		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 40, strtotime( '11-Apr-2010' ), strtotime( '17-Apr-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 40, strtotime( '12-Apr-2010' ), strtotime( '18-Apr-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 40, strtotime( '13-Apr-2010' ), strtotime( '19-Apr-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 40, strtotime( '14-Apr-2010' ), strtotime( '20-Apr-2010' ), $frequency_criteria ) );

		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 40, strtotime( '12-Apr-2010' ), strtotime( '17-Apr-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 40, strtotime( '13-Apr-2010' ), strtotime( '17-Apr-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 40, strtotime( '14-Apr-2010' ), strtotime( '17-Apr-2010' ), $frequency_criteria ) );

		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 40, strtotime( '11-Apr-2010' ), strtotime( '18-Apr-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 40, strtotime( '11-Apr-2010' ), strtotime( '19-Apr-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 40, strtotime( '11-Apr-2010' ), strtotime( '24-Apr-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 40, strtotime( '11-Apr-2010' ), strtotime( '25-Apr-2010' ), $frequency_criteria ) );

		//Specific date
		$frequency_criteria = [
				'month'         => 0,
				'day_of_month'  => 0,
				'day_of_week'   => 0,
				//'quarter' => 0,
				'quarter_month' => 0,
				'date'          => strtotime( '01-Jan-2010' ),
		];

		//No range
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 100, strtotime( '01-Jan-2010' ), strtotime( '01-Jan-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 100, strtotime( '31-Dec-2009' ), strtotime( '01-Jan-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 100, strtotime( '01-Jan-2010' ), strtotime( '02-Jan-2010' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 100, strtotime( '30-Dec-2009' ), strtotime( '31-Dec-2009' ), $frequency_criteria ) );
		$this->assertEquals( false, TTDate::inApplyFrequencyWindow( 100, strtotime( '02-Jan-2010' ), strtotime( '03-Jan-2010' ), $frequency_criteria ) );

		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 100, strtotime( '01-Jan-2009' ), strtotime( '01-Jan-2010' ), $frequency_criteria ) );
		$this->assertEquals( true, TTDate::inApplyFrequencyWindow( 100, strtotime( '01-Jan-2009' ), strtotime( '01-Jan-2011' ), $frequency_criteria ) );
	}

	//Compare pure PHP implementation of EasterDays to PHP calendar extension.
	function test_EasterDays() {
		if ( function_exists( 'easter_days' ) ) {
			for ( $i = 2000; $i < 2050; $i++ ) {
				$this->assertEquals( easter_days( $i ), TTDate::getEasterDays( $i ) );
			}
		}
	}

	function testTimeZones() {
		$upf = new UserPreferenceFactory();
		$zones = $upf->getOptions( 'time_zone' );

		foreach ( $zones as $zone => $name ) {
			$retval = TTDate::setTimeZone( Misc::trimSortPrefix( $zone ), true, true );
			$this->assertEquals( true, $retval, 'Failed TZ: ' . $name );
		}
	}

	function testDeprecatedTimeZones() {
		$upf = new UserPreferenceFactory();

		$countries = ['CA', 'US', ''];
		foreach( $countries as $country ) {
			$zones = $upf->getOptions( 'deprecated_timezone', [ 'country' => $country ] );

			//Make sure all
			foreach ( $zones as $deprecated_zone => $new_zone ) {
				if ( strpos( $deprecated_zone, 'AST4ADT' ) !== false || strpos( $deprecated_zone, 'YST9YDT' ) !== false || strpos( $deprecated_zone, 'Canada/East-Saskatchewan' ) !== false || strpos( $deprecated_zone, 'CST5CDT' ) !== false || strpos( $deprecated_zone, 'US/Pacific-New' ) !== false) {
					continue;
				}

				$deprecated_zone_retval = TTDate::setTimeZone( Misc::trimSortPrefix( $deprecated_zone ), true, true );
				$this->assertNotFalse( $deprecated_zone_retval );
				$deprecated_time = strtotime( '2020-01-01 00:00:00' );

				$new_zone_retval = TTDate::setTimeZone( Misc::trimSortPrefix( $new_zone ), true, true );
				$this->assertNotFalse( $new_zone_retval );
				$new_time = strtotime( '2020-01-01 00:00:00' );

				$this->assertEquals( $deprecated_time, $new_time, 'Depcreated Zone: ' . $deprecated_zone . ' New Zone: ' . $new_zone );
			}
		}
	}

	function testCheckForDeprecatedTimeZones() {
		$countries = ['CA', 'US', ''];
		foreach( $countries as $country ) {
			$upf = new UserPreferenceFactory();
			$zones = $upf->getOptions( 'time_zone' );
			$deprecated_zones = $upf->getOptions( 'deprecated_timezone', [ 'country' => $country ] );
			$location_zones = $upf->getOptions( 'location_timezone' );
			$area_code_zones = $upf->getOptions( 'area_code_timezone' );

			//Make sure all
			foreach ( $zones as $zone => $name ) {
				$this->assertTrue( !isset( $deprecated_zones[$zone] ), 'UserPreference Zone is deprecated: ' . $zone );
				if ( isset( $deprecated_zones[$zone] ) ) {
					$this->assertTrue( !isset( $zones[$deprecated_zones[$zone]] ), 'Deprecrated Linked Zone does not exist: ' . $deprecated_zones[$zone] );
				}
			}

			foreach ( $deprecated_zones as $deprecated_zone => $new_zone ) {
				$this->assertTrue( isset( $zones[$new_zone] ), 'Zone linked to deprecated zone is invalid!: ' . $new_zone );
			}

			foreach ( $location_zones as $country => $zone_arr ) {
				if ( is_array( $zone_arr ) ) {
					foreach ( $zone_arr as $zone ) {
						if ( isset( $deprecated_zones[$zone] ) ) {
							$this->assertTrue( false, 'Location TimeZone Map: Zone is deprecated: ' . $zone . ' Should be: ' . $deprecated_zones[$zone] );
						}

						//Make sure detected timezone are actually valid and in the main timezone list.
						if ( $zone != '' && !isset( $zones[$zone] ) ) {
							$this->assertTrue( false, 'Area Code TimeZone Map: Zone is does not exist in main Time Zone list: ' . $zone );
						}
					}
				} else {
					if ( isset( $deprecated_zones[$zone_arr] ) ) {
						$this->assertTrue( false, 'Location TimeZone Map: Zone is deprecated: ' . $zone_arr . ' Should be: ' . $deprecated_zones[$zone_arr] );
					}
				}
			}

			foreach ( $area_code_zones as $area_code => $arr ) {
				$zone = $arr['time_zone'];
				if ( $zone != '' && isset( $deprecated_zones[$zone] ) ) {
					$this->assertTrue( false, 'Area Code TimeZone Map: Zone is deprecated: ' . $zone . ' Should be: ' . $deprecated_zones[$zone] );
				}

				//Make sure detected timezone are actually valid and in the main timezone list.
				if ( $zone != '' && !isset( $zones[$zone] ) ) {
					$this->assertTrue( false, 'Area Code TimeZone Map: Zone is does not exist in main Time Zone list: ' . $zone );
				}
			}
		}
	}

	function testReportDatesA() {
		$uf = TTnew( 'UserFactory' ); /** @var UserFactory $uf */
		$uf->getUserPreferenceObject()->setDateFormat( 'm-d-y' );

		$pre_process_dates = TTDate::getReportDates( null, strtotime( '03-Mar-2015 08:00AM' ), false, $uf ); //Sortable dates
		//var_dump( $pre_process_dates );
		$this->assertEquals( '2015-03-03', $pre_process_dates['date_stamp'] );

		TTDate::setDateFormat( 'Y-m-d' );
		$this->assertEquals( '2015-03-03', TTDate::getReportDates( 'date_stamp', $pre_process_dates['date_stamp'], true, $uf ) );

		TTDate::setDateFormat( 'd-m-y' );
		$this->assertEquals( '03-03-15', TTDate::getReportDates( 'date_stamp', $pre_process_dates['date_stamp'], true, $uf ) );
	}

	function testDoesRangeSpanMidnight() {
		$this->assertEquals( false, TTDate::doesRangeSpanMidnight( strtotime( '01-Jan-2016 8:00AM' ), strtotime( '01-Jan-2016 5:00PM' ), false ) );
		$this->assertEquals( false, TTDate::doesRangeSpanMidnight( strtotime( '01-Jan-2016 8:00AM' ), strtotime( '01-Jan-2016 11:59:59PM' ), false ) );
		$this->assertEquals( false, TTDate::doesRangeSpanMidnight( strtotime( '01-Jan-2016 8:00AM' ), strtotime( '01-Jan-2016 12:00:00AM' ), false ) );
		$this->assertEquals( false, TTDate::doesRangeSpanMidnight( strtotime( '01-Jan-2016 8:00AM' ), strtotime( '02-Jan-2016 12:00:00AM' ), false ) );
		$this->assertEquals( true, TTDate::doesRangeSpanMidnight( strtotime( '01-Jan-2016 8:00AM' ), strtotime( '02-Jan-2016 1:00:00AM' ), false ) );
		$this->assertEquals( true, TTDate::doesRangeSpanMidnight( strtotime( '02-Jan-2016 1:00:00AM' ), strtotime( '01-Jan-2016 8:00AM' ), false ) );
		$this->assertEquals( true, TTDate::doesRangeSpanMidnight( strtotime( '01-Jan-2016 8:00AM' ), strtotime( '03-Jan-2016 1:00:00AM' ), false ) );
		$this->assertEquals( false, TTDate::doesRangeSpanMidnight( strtotime( '01-Jan-2016 2:00:00AM' ), strtotime( '01-Jan-2016 2:00:00AM' ), false ) );

		$this->assertEquals( false, TTDate::doesRangeSpanMidnight( strtotime( '02-Jan-2016 12:00:00AM' ), strtotime( '02-Jan-2016 1:00:00AM' ), false ) );
		$this->assertEquals( true, TTDate::doesRangeSpanMidnight( strtotime( '01-Jan-2016 12:00:00AM' ), strtotime( '03-Jan-2016 12:00:00AM' ), false ) );
		$this->assertEquals( true, TTDate::doesRangeSpanMidnight( strtotime( '01-Jan-2016 12:00AM' ), strtotime( '02-Jan-2016 8:00:00AM' ), false ) );
		$this->assertEquals( true, TTDate::doesRangeSpanMidnight( strtotime( '01-Jan-2016 12:00AM' ), strtotime( '03-Jan-2016 8:00:00AM' ), false ) );


		$this->assertEquals( true, TTDate::doesRangeSpanMidnight( strtotime( '02-Jan-2016 12:00AM' ), strtotime( '02-Jan-2016 1:00:00AM' ), true ) );
		$this->assertEquals( true, TTDate::doesRangeSpanMidnight( strtotime( '02-Jan-2016 12:00AM' ), strtotime( '02-Jan-2016 12:00:00AM' ), true ) );
		$this->assertEquals( true, TTDate::doesRangeSpanMidnight( strtotime( '02-Jan-2016 12:00AM' ), strtotime( '03-Jan-2016 12:00:00AM' ), true ) );
		$this->assertEquals( true, TTDate::doesRangeSpanMidnight( strtotime( '01-Jan-2016 12:00AM' ), strtotime( '03-Jan-2016 8:00:00AM' ), true ) );
		$this->assertEquals( true, TTDate::doesRangeSpanMidnight( strtotime( '01-Jan-2016 8:00AM' ), strtotime( '02-Jan-2016 12:00:00AM' ), true ) );
		$this->assertEquals( true, TTDate::doesRangeSpanMidnight( strtotime( '01-Jan-2016 8:00AM' ), strtotime( '03-Jan-2016 8:00:00AM' ), true ) );
	}


	function testsplitDateRange() {
		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '01-Jan-2016 8:00AM' ), strtotime( '02-Jan-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '01-Jan-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '02-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '02-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '02-Jan-2016 8:00AM' ) );
		$this->assertCount( 2, $split_range_arr );

		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '01-Jan-2016 8:00AM' ), strtotime( '03-Jan-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '01-Jan-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '02-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '02-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '03-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[2]['start_time_stamp'], strtotime( '03-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[2]['end_time_stamp'], strtotime( '03-Jan-2016 8:00AM' ) );
		$this->assertCount( 3, $split_range_arr );


		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '01-Jan-2016 8:00AM' ), strtotime( '04-Jan-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '01-Jan-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '02-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '02-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '03-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[2]['start_time_stamp'], strtotime( '03-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[2]['end_time_stamp'], strtotime( '04-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['start_time_stamp'], strtotime( '04-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['end_time_stamp'], strtotime( '04-Jan-2016 8:00AM' ) );
		$this->assertCount( 4, $split_range_arr );


		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '01-Jan-2016 8:00AM' ), strtotime( '01-Jan-2016 8:00PM' ), strtotime( '01-Jan-2016 7:00AM' ), strtotime( '01-Jan-2016 3:00PM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '01-Jan-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '01-Jan-2016 3:00PM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '01-Jan-2016 3:00PM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '01-Jan-2016 8:00PM' ) );
		$this->assertCount( 2, $split_range_arr );


		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '01-Jan-2016 8:00AM' ), strtotime( '01-Jan-2016 8:00PM' ), strtotime( '01-Jan-2016 3:00PM' ), strtotime( '01-Jan-2016 9:00PM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '01-Jan-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '01-Jan-2016 3:00PM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '01-Jan-2016 3:00PM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '01-Jan-2016 8:00PM' ) );
		$this->assertCount( 2, $split_range_arr );


		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '01-Jan-2016 8:00AM' ), strtotime( '01-Jan-2016 8:00PM' ), strtotime( '01-Jan-2016 9:00AM' ), strtotime( '01-Jan-2016 7:00PM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '01-Jan-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '01-Jan-2016 9:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '01-Jan-2016 9:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '01-Jan-2016 7:00PM' ) );
		$this->assertEquals( $split_range_arr[2]['start_time_stamp'], strtotime( '01-Jan-2016 7:00PM' ) );
		$this->assertEquals( $split_range_arr[2]['end_time_stamp'], strtotime( '01-Jan-2016 8:00PM' ) );
		$this->assertCount( 3, $split_range_arr );


		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '01-Jan-2016 3:00PM' ), strtotime( '01-Jan-2016 11:00PM' ), strtotime( '01-Jan-2016 12:00AM' ), strtotime( '02-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '01-Jan-2016 3:00PM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '01-Jan-2016 11:00PM' ) );
		$this->assertCount( 1, $split_range_arr );


		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '01-Jan-2016 8:00AM' ), strtotime( '02-Jan-2016 8:00AM' ), strtotime( '01-Jan-2016 11:00PM' ), strtotime( '02-Jan-2016 1:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '01-Jan-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '01-Jan-2016 11:00PM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '01-Jan-2016 11:00PM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '02-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[2]['start_time_stamp'], strtotime( '02-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[2]['end_time_stamp'], strtotime( '02-Jan-2016 1:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['start_time_stamp'], strtotime( '02-Jan-2016 1:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['end_time_stamp'], strtotime( '02-Jan-2016 8:00AM' ) );
		$this->assertCount( 4, $split_range_arr );

		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '01-Jan-2016 8:00AM' ), strtotime( '02-Jan-2016 8:00AM' ), strtotime( '01-Jan-2016 11:00PM' ), strtotime( '01-Jan-2016 1:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '01-Jan-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '01-Jan-2016 11:00PM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '01-Jan-2016 11:00PM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '02-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[2]['start_time_stamp'], strtotime( '02-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[2]['end_time_stamp'], strtotime( '02-Jan-2016 1:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['start_time_stamp'], strtotime( '02-Jan-2016 1:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['end_time_stamp'], strtotime( '02-Jan-2016 8:00AM' ) );
		$this->assertCount( 4, $split_range_arr );

		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '01-Jan-2016 8:00AM' ), strtotime( '04-Jan-2016 8:00AM' ), strtotime( '01-Jan-2016 11:00PM' ), strtotime( '02-Jan-2016 1:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '01-Jan-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '01-Jan-2016 11:00PM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '01-Jan-2016 11:00PM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '02-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[2]['start_time_stamp'], strtotime( '02-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[2]['end_time_stamp'], strtotime( '02-Jan-2016 1:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['start_time_stamp'], strtotime( '02-Jan-2016 1:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['end_time_stamp'], strtotime( '02-Jan-2016 11:00PM' ) );
		$this->assertEquals( $split_range_arr[4]['start_time_stamp'], strtotime( '02-Jan-2016 11:00PM' ) );
		$this->assertEquals( $split_range_arr[4]['end_time_stamp'], strtotime( '03-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[5]['start_time_stamp'], strtotime( '03-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[5]['end_time_stamp'], strtotime( '03-Jan-2016 1:00AM' ) );//should be 1am. things are wrong south of here.
		$this->assertEquals( $split_range_arr[6]['start_time_stamp'], strtotime( '03-Jan-2016 1:00AM' ) );
		$this->assertEquals( $split_range_arr[6]['end_time_stamp'], strtotime( '03-Jan-2016 11:00PM' ) );
		$this->assertEquals( $split_range_arr[7]['start_time_stamp'], strtotime( '03-Jan-2016 11:00PM' ) );
		$this->assertEquals( $split_range_arr[7]['end_time_stamp'], strtotime( '04-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[8]['start_time_stamp'], strtotime( '04-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[8]['end_time_stamp'], strtotime( '04-Jan-2016 1:00AM' ) );
		$this->assertEquals( $split_range_arr[9]['start_time_stamp'], strtotime( '04-Jan-2016 1:00AM' ) );
		$this->assertEquals( $split_range_arr[9]['end_time_stamp'], strtotime( '04-Jan-2016 8:00AM' ) );
		$this->assertCount( 10, $split_range_arr );

		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '01-Jan-2016 8:00AM' ), strtotime( '03-Jan-2016 8:00PM' ), strtotime( '01-Jan-2016 9:00AM' ), strtotime( '01-Jan-2016 7:00PM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '01-Jan-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '01-Jan-2016 9:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '01-Jan-2016 9:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '01-Jan-2016 7:00PM' ) );
		$this->assertEquals( $split_range_arr[2]['start_time_stamp'], strtotime( '01-Jan-2016 7:00PM' ) );
		$this->assertEquals( $split_range_arr[2]['end_time_stamp'], strtotime( '02-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['start_time_stamp'], strtotime( '02-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['end_time_stamp'], strtotime( '02-Jan-2016 9:00AM' ) );
		$this->assertEquals( $split_range_arr[4]['start_time_stamp'], strtotime( '02-Jan-2016 9:00AM' ) );
		$this->assertEquals( $split_range_arr[4]['end_time_stamp'], strtotime( '02-Jan-2016 7:00PM' ) );
		$this->assertEquals( $split_range_arr[5]['start_time_stamp'], strtotime( '02-Jan-2016 7:00PM' ) );
		$this->assertEquals( $split_range_arr[5]['end_time_stamp'], strtotime( '03-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[6]['start_time_stamp'], strtotime( '03-Jan-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[6]['end_time_stamp'], strtotime( '03-Jan-2016 9:00AM' ) );
		$this->assertEquals( $split_range_arr[7]['start_time_stamp'], strtotime( '03-Jan-2016 9:00AM' ) );
		$this->assertEquals( $split_range_arr[7]['end_time_stamp'], strtotime( '03-Jan-2016 7:00PM' ) );
		$this->assertEquals( $split_range_arr[8]['start_time_stamp'], strtotime( '03-Jan-2016 7:00PM' ) );
		$this->assertEquals( $split_range_arr[8]['end_time_stamp'], strtotime( '03-Jan-2016 8:00PM' ) );
		$this->assertCount( 9, $split_range_arr );

		//start and end only one day apart
		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '10-Mar-2016 8:00AM' ), strtotime( '11-Mar-2016 8:00PM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '10-Mar-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '11-Mar-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '11-Mar-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '11-Mar-2016 8:00PM' ) );
		$this->assertCount( 2, $split_range_arr );

		TTDate::setTimeZone( 'America/Vancouver', true ); //Force to timezone that observes DST.
		//spans daylight savings time in spring
		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '13-Mar-2016 8:00AM' ), strtotime( '14-Mar-2016 8:00PM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '13-Mar-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '14-Mar-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '14-Mar-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '14-Mar-2016 8:00PM' ) );
		$this->assertCount( 2, $split_range_arr );

		//spans daylight savings time in spring with filter
		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '13-Mar-2016 8:00AM' ), strtotime( '14-Mar-2016 8:00PM' ), strtotime( '01-Jan-2016 9:00AM' ), strtotime( '01-Jan-2016 7:00PM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '13-Mar-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '13-Mar-2016 9:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '13-Mar-2016 9:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '13-Mar-2016 7:00PM' ) );
		$this->assertEquals( $split_range_arr[2]['start_time_stamp'], strtotime( '13-Mar-2016 7:00PM' ) );
		$this->assertEquals( $split_range_arr[2]['end_time_stamp'], strtotime( '14-Mar-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['start_time_stamp'], strtotime( '14-Mar-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['end_time_stamp'], strtotime( '14-Mar-2016 9:00AM' ) );
		$this->assertEquals( $split_range_arr[4]['start_time_stamp'], strtotime( '14-Mar-2016 9:00AM' ) );
		$this->assertEquals( $split_range_arr[4]['end_time_stamp'], strtotime( '14-Mar-2016 7:00PM' ) );
		$this->assertEquals( $split_range_arr[5]['start_time_stamp'], strtotime( '14-Mar-2016 7:00PM' ) );
		$this->assertEquals( $split_range_arr[5]['end_time_stamp'], strtotime( '14-Mar-2016 8:00PM' ) );
		$this->assertCount( 6, $split_range_arr );

		//spans daylight savings time in spring with filter where the filter spans 11-2
		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '12-Mar-2016 8:00AM' ), strtotime( '13-Mar-2016 4:00AM' ), strtotime( '12-Mar-2016 11:00PM' ), strtotime( '13-Mar-2016 2:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '12-Mar-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '12-Mar-2016 11:00PM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '12-Mar-2016 11:00PM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '13-Mar-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[2]['start_time_stamp'], strtotime( '13-Mar-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[2]['end_time_stamp'], strtotime( '13-Mar-2016 3:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['start_time_stamp'], strtotime( '13-Mar-2016 3:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['end_time_stamp'], strtotime( '13-Mar-2016 4:00AM' ) );
		$this->assertCount( 4, $split_range_arr );

		//spans daylight savings time in fall
		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '6-Nov-2016 8:00AM' ), strtotime( '7-Nov-2016 8:00PM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '6-Nov-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '7-Nov-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '7-Nov-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '7-Nov-2016 8:00PM' ) );
		$this->assertCount( 2, $split_range_arr );

		//spans daylight savings time in fall with filter
		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '6-Nov-2016 8:00AM' ), strtotime( '7-Nov-2016 8:00PM' ), strtotime( '01-Jan-2016 9:00AM' ), strtotime( '01-Jan-2016 7:00PM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '6-Nov-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '6-Nov-2016 9:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '6-Nov-2016 9:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '6-Nov-2016 7:00PM' ) );
		$this->assertEquals( $split_range_arr[2]['start_time_stamp'], strtotime( '6-Nov-2016 7:00PM' ) );
		$this->assertEquals( $split_range_arr[2]['end_time_stamp'], strtotime( '7-Nov-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['start_time_stamp'], strtotime( '7-Nov-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['end_time_stamp'], strtotime( '7-Nov-2016 9:00AM' ) );
		$this->assertEquals( $split_range_arr[4]['start_time_stamp'], strtotime( '7-Nov-2016 9:00AM' ) );
		$this->assertEquals( $split_range_arr[4]['end_time_stamp'], strtotime( '7-Nov-2016 7:00PM' ) );
		$this->assertEquals( $split_range_arr[5]['start_time_stamp'], strtotime( '7-Nov-2016 7:00PM' ) );
		$this->assertEquals( $split_range_arr[5]['end_time_stamp'], strtotime( '7-Nov-2016 8:00PM' ) );
		$this->assertCount( 6, $split_range_arr );

		//http://stackoverflow.com/questions/2613338/date-returning-wrong-day-although-the-timestamp-is-correct
		//fall daylight savings. illustrating the missing hour
		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '6-Nov-2016 8:00AM' ), ( strtotime( '6-Nov-2016 1:00AM' ) + 7200 ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '06-Nov-2016 08:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '06-Nov-2016 02:00AM' ) );
		$this->assertCount( 1, $split_range_arr );

		//the missing hour shows up in the filter
		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '5-Nov-2016 8:00AM' ), ( strtotime( '6-Nov-2016 1:00AM' ) + 7200 ), strtotime( '6-Nov-2016 8:00AM' ), strtotime( '6-Nov-2016 1:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '5-Nov-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '6-Nov-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '6-Nov-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '6-Nov-2016 1:00AM' ) );
		$this->assertEquals( $split_range_arr[2]['start_time_stamp'], strtotime( '6-Nov-2016 1:00AM' ) );
		$this->assertEquals( $split_range_arr[2]['end_time_stamp'], strtotime( '6-Nov-2016 2:00AM' ) );
		$this->assertCount( 3, $split_range_arr );

		//fall daylight savings. illustrating the missing hour
		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '5-Nov-2016 8:00AM' ), ( strtotime( '6-Nov-2016 1:00AM' ) + 7200 ), strtotime( '6-Nov-2016 8:00AM' ), ( strtotime( '6-Nov-2016 1:00AM' ) + 3600 ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '05-Nov-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '06-Nov-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '06-Nov-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '06-Nov-2016 1:00AM' ) );
		$this->assertEquals( $split_range_arr[2]['start_time_stamp'], strtotime( '06-Nov-2016 1:00AM' ) );
		$this->assertEquals( $split_range_arr[2]['end_time_stamp'], strtotime( '06-Nov-2016 2:00AM' ) );
		$this->assertCount( 3, $split_range_arr );

		//spring daylight savings. illustrating the extra hour
		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '13-Mar-2016 8:00AM' ), ( strtotime( '13-Mar-2016 12:00AM' ) + 7200 ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '13-Mar-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '13-Mar-2016 3:00AM' ) );
		$this->assertCount( 1, $split_range_arr );

		//spring daylight savings. illustrating the extra hour
		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '11-Mar-2016 8:00AM' ), ( strtotime( '13-Mar-2016 12:00AM' ) + 7200 ), strtotime( '13-Mar-2016 4:00AM' ), ( strtotime( '13-Mar-2016 1:00AM' ) + 7200 ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '11-Mar-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '12-Mar-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '12-Mar-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '12-Mar-2016 4:00AM' ) );
		$this->assertEquals( $split_range_arr[2]['start_time_stamp'], strtotime( '12-Mar-2016 4:00AM' ) );
		$this->assertEquals( $split_range_arr[2]['end_time_stamp'], strtotime( '13-Mar-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['start_time_stamp'], strtotime( '13-Mar-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['end_time_stamp'], strtotime( '13-Mar-2016 3:00AM' ) );
		$this->assertCount( 4, $split_range_arr );

		//leap year illustration
		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '28-Feb-2016 8:00AM' ), ( strtotime( '1-Mar-2016 12:00AM' ) + 7200 ), strtotime( '28-Feb-2016 4:00AM' ), ( strtotime( '28-Feb-2016 1:00AM' ) + 7200 ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '28-Feb-2016 8:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '29-Feb-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '29-Feb-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '29-Feb-2016 3:00AM' ) );
		$this->assertEquals( $split_range_arr[2]['start_time_stamp'], strtotime( '29-Feb-2016 3:00AM' ) );
		$this->assertEquals( $split_range_arr[2]['end_time_stamp'], strtotime( '29-Feb-2016 4:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['start_time_stamp'], strtotime( '29-Feb-2016 4:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['end_time_stamp'], strtotime( '01-Mar-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[4]['start_time_stamp'], strtotime( '01-Mar-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[4]['end_time_stamp'], strtotime( '01-Mar-2016 2:00AM' ) );
		$this->assertCount( 5, $split_range_arr );

		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( 'Tue, 03 May 2016 06:00:00 -0600' ), strtotime( 'Thu, 05 May 2016 15:00:00 -0600' ), strtotime( 'Tue, 03 May 2016 08:00:00 -0600' ), strtotime( 'Tue, 03 May 2016 15:00:00 -0600' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '03 May 2016 06:00:00 -0600' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '03 May 2016 08:00:00 -0600' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( 'Tue, 03 May 2016 08:00:00 -0600' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '03 May 2016 15:00:00 -0600' ) );
		$this->assertEquals( $split_range_arr[2]['start_time_stamp'], strtotime( '03 May 2016 15:00:00 -0600' ) );
		$this->assertEquals( $split_range_arr[2]['end_time_stamp'], strtotime( '04-May-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['start_time_stamp'], strtotime( '04-May-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[3]['end_time_stamp'], strtotime( '04 May 2016 08:00:00 -0600' ) );
		$this->assertEquals( $split_range_arr[4]['start_time_stamp'], strtotime( '04 May 2016 08:00:00 -0600' ) );
		$this->assertEquals( $split_range_arr[4]['end_time_stamp'], strtotime( '04 May 2016 15:00:00 -0600' ) );
		$this->assertEquals( $split_range_arr[5]['start_time_stamp'], strtotime( '04 May 2016 15:00:00 -0600' ) );
		$this->assertEquals( $split_range_arr[5]['end_time_stamp'], strtotime( '05-May-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[6]['start_time_stamp'], strtotime( '05-May-2016 12:00AM' ) );
		$this->assertEquals( $split_range_arr[6]['end_time_stamp'], strtotime( '05 May 2016 08:00:00 -0600' ) );
		$this->assertEquals( $split_range_arr[7]['start_time_stamp'], strtotime( '05 May 2016 08:00:00 -0600' ) );
		$this->assertEquals( $split_range_arr[7]['end_time_stamp'], strtotime( '05 May 2016 15:00:00 -0600' ) );
		$this->assertCount( 8, $split_range_arr );

		//multi-year test
		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '15-Jun-2010 8:00AM' ), strtotime( '30-Aug-2016 5:00PM' ), strtotime( '25-Jun-2010 2:00AM' ), strtotime( '26-Aug-2016 10:00PM' ) );
		//first 3 days
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '2010-06-15 08:00:00am' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '2010-06-15 10:00:00pm' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '2010-06-15 10:00:00pm' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '2010-06-16 12:00:00am' ) );
		$this->assertEquals( $split_range_arr[2]['start_time_stamp'], strtotime( '2010-06-16 12:00:00am' ) );
		$this->assertEquals( $split_range_arr[2]['end_time_stamp'], strtotime( '2010-06-16 02:00:00am' ) );
		//a few from the middle
		$this->assertEquals( $split_range_arr[3000]['start_time_stamp'], strtotime( '2013-03-11 02:00:00am' ) );
		$this->assertEquals( $split_range_arr[3000]['end_time_stamp'], strtotime( '2013-03-11 10:00:00pm' ) );
		$this->assertEquals( $split_range_arr[3001]['start_time_stamp'], strtotime( '2013-03-11 10:00:00pm' ) );
		$this->assertEquals( $split_range_arr[3001]['end_time_stamp'], strtotime( '2013-03-12 12:00:00am' ) );
		$this->assertEquals( $split_range_arr[3002]['start_time_stamp'], strtotime( '2013-03-12 12:00:00am' ) );
		$this->assertEquals( $split_range_arr[3002]['end_time_stamp'], strtotime( '2013-03-12 02:00:00am' ) );
		//last 3 days
		$this->assertEquals( $split_range_arr[6802]['start_time_stamp'], strtotime( '2016-08-29 10:00:00pm' ) );
		$this->assertEquals( $split_range_arr[6802]['end_time_stamp'], strtotime( '2016-08-30 12:00:00am' ) );
		$this->assertEquals( $split_range_arr[6803]['start_time_stamp'], strtotime( '2016-08-30 12:00:00am' ) );
		$this->assertEquals( $split_range_arr[6803]['end_time_stamp'], strtotime( '2016-08-30 02:00:00am' ) );
		$this->assertEquals( $split_range_arr[6804]['start_time_stamp'], strtotime( '2016-08-30 02:00:00am' ) );
		$this->assertEquals( $split_range_arr[6804]['end_time_stamp'], strtotime( '2016-08-30 05:00:00pm' ) );
		$this->assertCount( 6805, $split_range_arr );

		//#2329 <24 hrs between with midnight filter provided.
		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '04-Jul-17 9:55 PM' ), strtotime( '05-Jul-17 1:00 AM' ), strtotime( '04-Jul-17 12:00 AM' ), strtotime( '04-Jul-17 10:00 PM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '04-Jul-17 9:55 PM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '04-Jul-17 10:00 PM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '04-Jul-17 10:00 PM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '05-Jul-17 12:00 AM' ) );
		$this->assertEquals( $split_range_arr[2]['start_time_stamp'], strtotime( '05-Jul-17 12:00 AM' ) );
		$this->assertEquals( $split_range_arr[2]['end_time_stamp'], strtotime( '05-Jul-17 1:00 AM' ) );
		$this->assertCount( 3, $split_range_arr );

		//same day
		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '04-Jul-17 9:55 PM' ), strtotime( '04-Jul-17 11:00 PM' ), strtotime( '04-Jul-17 10:00 PM' ), strtotime( '04-Jul-17 10:50 PM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '04-Jul-17 9:55 PM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '04-Jul-17 10:00 PM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '04-Jul-17 10:00 PM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '04-Jul-17 10:50 PM' ) );
		$this->assertEquals( $split_range_arr[2]['start_time_stamp'], strtotime( '04-Jul-17 10:50 PM' ) );
		$this->assertEquals( $split_range_arr[2]['end_time_stamp'], strtotime( '04-Jul-17 11:00 PM' ) );
		$this->assertCount( 3, $split_range_arr );

		//next day < 24hrs between
		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '04-Jul-17 9:55 PM' ), strtotime( '05-Jul-17 8:00 PM' ), strtotime( '04-Jul-17 10:00 PM' ), strtotime( '04-Jul-17 10:50 PM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '04-Jul-17 9:55 PM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '04-Jul-17 10:00 PM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '04-Jul-17 10:00 PM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '04-Jul-17 10:50 PM' ) );
		$this->assertEquals( $split_range_arr[2]['start_time_stamp'], strtotime( '04-Jul-17 10:50 PM' ) );
		$this->assertEquals( $split_range_arr[2]['end_time_stamp'], strtotime( '05-Jul-17 12:00 AM' ) );
		$this->assertEquals( $split_range_arr[3]['start_time_stamp'], strtotime( '05-Jul-17 12:00 AM' ) );
		$this->assertEquals( $split_range_arr[3]['end_time_stamp'], strtotime( '05-Jul-17 8:00 PM' ) );
		$this->assertCount( 4, $split_range_arr );


		//next day < 24hrs between reversed filters (greater, then less.)
		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '04-Jul-17 9:55 PM' ), strtotime( '05-Jul-17 8:00 PM' ), strtotime( '04-Jul-17 10:50 PM' ), strtotime( '04-Jul-17 10:00 PM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '04-Jul-17 9:55 PM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '04-Jul-17 10:00 PM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '04-Jul-17 10:00 PM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '04-Jul-17 10:50 PM' ) );
		$this->assertEquals( $split_range_arr[2]['start_time_stamp'], strtotime( '04-Jul-17 10:50 PM' ) );
		$this->assertEquals( $split_range_arr[2]['end_time_stamp'], strtotime( '05-Jul-17 12:00 AM' ) );
		$this->assertEquals( $split_range_arr[3]['start_time_stamp'], strtotime( '05-Jul-17 12:00 AM' ) );
		$this->assertEquals( $split_range_arr[3]['end_time_stamp'], strtotime( '05-Jul-17 8:00 PM' ) );
		$this->assertCount( 4, $split_range_arr );


		//very small window
		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '04-Jul-17 9:55 PM' ), strtotime( '04-Jul-17 09:58 PM' ), strtotime( '04-Jul-17 09:56 PM' ), strtotime( '04-Jul-17 09:57 PM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '04-Jul-17 9:55 PM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '04-Jul-17 09:56 PM' ) );
		$this->assertEquals( $split_range_arr[1]['start_time_stamp'], strtotime( '04-Jul-17 09:56 PM' ) );
		$this->assertEquals( $split_range_arr[1]['end_time_stamp'], strtotime( '04-Jul-17 09:57 PM' ) );
		$this->assertEquals( $split_range_arr[2]['start_time_stamp'], strtotime( '04-Jul-17 09:57 PM' ) );
		$this->assertEquals( $split_range_arr[2]['end_time_stamp'], strtotime( '04-Jul-17 09:58 PM' ) );
		$this->assertCount( 3, $split_range_arr );

		//with filter dates after the end time
		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '01-Jan-17 08:00AM' ), strtotime( '01-Jan-17 05:00 PM' ), strtotime( '02-Jan-17 06:00 PM' ), strtotime( '02-Jan-17 07:00 PM' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '01-Jan-17 08:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '01-Jan-17 05:00 PM' ) );
		$this->assertCount( 1, $split_range_arr );

		//with filter dates before the start time.
		$split_range_arr = TTDate::splitDateRangeAtMidnight( strtotime( '01-Jan-17 08:00AM' ), strtotime( '01-Jan-17 05:00 PM' ), strtotime( '02-Jan-17 03:00 AM' ), strtotime( '02-Jan-17 05:00 am' ) );
		$this->assertEquals( $split_range_arr[0]['start_time_stamp'], strtotime( '01-Jan-17 08:00AM' ) );
		$this->assertEquals( $split_range_arr[0]['end_time_stamp'], strtotime( '01-Jan-17 05:00 PM' ) );
		$this->assertCount( 1, $split_range_arr );
	}

	/**
	 * Magic days and all the problems they leave for us.
	 */
	function testDSTMagic() {
		TTDate::setTimeFormat( 'g:i A T' );

		TTDate::setTimeZone( 'America/Vancouver', true ); //Force to timezone that observes DST.
		$time_stamp = 1457859600; //13-Mar-2016 1:00AM
		$this->assertEquals( strtotime( '13-Mar-2016 1:00AM' ), $time_stamp );
		$this->assertEquals( '13-Mar-16 1:00 AM PST', TTDate::getDate( 'DATE+TIME', $time_stamp ) );

		$this->assertEquals( strtotime( '13-Mar-2016 2:00AM' ), ( $time_stamp + 3600 ) );
		$this->assertEquals( '13-Mar-16 3:00 AM PDT', TTDate::getDate( 'DATE+TIME', ( $time_stamp + 3600 ) ) );
		$this->assertEquals( '14-Mar-16 1:00 AM PDT', TTDate::getDate( 'DATE+TIME', TTDate::incrementDate( $time_stamp, 1, 'day' ) ) );
		$this->assertEquals( '14-Mar-16 2:00 AM PDT', TTDate::getDate( 'DATE+TIME', ( $time_stamp + 86400 ) ) );

		TTDate::setTimeFormat( 'g:i A' );
		TTDate::setTimeZone( 'Etc/GMT+8', true ); //Force to timezone that does not observe DST.
		$time_stamp = 1457859600; //13-Mar-2016 1:00AM
		$this->assertEquals( strtotime( '13-Mar-2016 1:00AM PST' ), $time_stamp );
		$this->assertEquals( '13-Mar-16 1:00 AM', TTDate::getDate( 'DATE+TIME', $time_stamp ) ); //Was: GMT+8 - But some versions of PHP return "-08", so just ignore the timezone setting for this case.

		$this->assertEquals( strtotime( '13-Mar-2016 2:00AM PST' ), ( $time_stamp + 3600 ) );
		$this->assertEquals( '13-Mar-16 2:00 AM', TTDate::getDate( 'DATE+TIME', ( $time_stamp + 3600 ) ) );                         //Was: GMT+8
		$this->assertEquals( '14-Mar-16 1:00 AM', TTDate::getDate( 'DATE+TIME', TTDate::incrementDate( $time_stamp, 1, 'day' ) ) ); //Was: GMT+8

		TTDate::setTimeFormat( 'g:i A T' );
		TTDate::setTimeZone( 'America/Vancouver', true ); //Force to timezone that observes DST.
		$time_stamp = 1478419200; //13-Mar-2016 1:00AM
		$this->assertEquals( strtotime( '06-Nov-2016 1:00AM' ), $time_stamp );
		$this->assertEquals( '06-Nov-16 1:00 AM PDT', TTDate::getDate( 'DATE+TIME', $time_stamp ) );

		$this->assertEquals( '06-Nov-16 1:00 AM PST', TTDate::getDate( 'DATE+TIME', ( $time_stamp + 3600 ) ) );

		$this->assertEquals( strtotime( '06-Nov-2016 2:00AM' ), ( $time_stamp + 7200 ) );
		$this->assertEquals( '06-Nov-16 2:00 AM PST', TTDate::getDate( 'DATE+TIME', ( $time_stamp + 7200 ) ) );
		$this->assertEquals( '07-Nov-16 1:00 AM PST', TTDate::getDate( 'DATE+TIME', TTDate::incrementDate( $time_stamp, 1, 'day' ) ) );
		$this->assertEquals( '07-Nov-16 12:00 AM PST', TTDate::getDate( 'DATE+TIME', ( $time_stamp + 86400 ) ) );


		//http://stackoverflow.com/questions/2613338/date-returning-wrong-day-although-the-timestamp-is-correct
		//illustrating that +86400 will not always give you tomorrow.
		$time_stamp = strtotime( '05-Nov-2016 12:00AM' );
		$time_stamp += 86400;
		$this->assertEquals( '06-Nov-16 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', ( $time_stamp ) ) ); //normal operation
		$time_stamp += 86400;
		$this->assertEquals( '06-Nov-16 11:00 PM PST', TTDate::getDate( 'DATE+TIME', ( $time_stamp ) ) ); //extra day!!!
		$time_stamp += 86400;
		$this->assertEquals( '07-Nov-16 11:00 PM PST', TTDate::getDate( 'DATE+TIME', ( $time_stamp ) ) ); //normal operation

		//and the same for fall daylight savings
		$time_stamp = strtotime( '15-Mar-2016 12:00AM' );
		$time_stamp -= 86400;
		$this->assertEquals( '14-Mar-16 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', ( $time_stamp ) ) ); //normal operation
		$time_stamp -= 86400;
		$this->assertEquals( '12-Mar-16 11:00 PM PST', TTDate::getDate( 'DATE+TIME', ( $time_stamp ) ) ); //missing day!!! where is 13th?
		$time_stamp -= 86400;
		$this->assertEquals( '11-Mar-16 11:00 PM PST', TTDate::getDate( 'DATE+TIME', ( $time_stamp ) ) ); //normal operation


		//illustrating that +86400 will not always give you tomorrow, but the middle day epoch will dodge the issue.
		//if we do all the math on the middle of day epoch and continually force it back to noon we can avoid problems with dst
		$time_stamp = TTDate::getMiddleDayEpoch( strtotime( '04-Nov-2016 12:00AM' ) );
		$time_stamp = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $time_stamp ), 1, 'day' );
		$this->assertEquals( '05-Nov-16 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', ( $time_stamp ) ) ); //normal operation
		$time_stamp = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $time_stamp ), 1, 'day' );
		$this->assertEquals( '06-Nov-16 12:00 PM PST', TTDate::getDate( 'DATE+TIME', ( $time_stamp ) ) ); //normal operation
		$time_stamp = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $time_stamp ), 1, 'day' );
		$this->assertEquals( '07-Nov-16 12:00 PM PST', TTDate::getDate( 'DATE+TIME', ( $time_stamp ) ) ); //normal operation
		$time_stamp = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $time_stamp ), 1, 'day' );
		$this->assertEquals( '08-Nov-16 12:00 PM PST', TTDate::getDate( 'DATE+TIME', ( $time_stamp ) ) ); //normal operation
	}

	function testCalendarArr() {
		$calendar_arr = TTDate::getCalendarArray( strtotime( '09-Mar-2019 12:00:00 PM' ), strtotime( '11-Mar-2019 12:00:00 PM' ) );

		$match_arr = [
				0  =>
						[
								'epoch'             => 1551600000,
								'date_stamp'        => '2019-03-03',
								'start_day_of_week' => 0,
								'day_of_week'       => null,
								'day_of_month'      => null,
								'month_name'        => null,
								'month_short_name'  => '',
								'month'             => '3',
								'is_new_month'        => false,
								'is_new_week'         => true,
						],
				1  =>
						[
								'epoch'             => 1551686400,
								'date_stamp'        => '2019-03-04',
								'start_day_of_week' => 0,
								'day_of_week'       => null,
								'day_of_month'      => null,
								'month_name'        => null,
								'month_short_name'  => '',
								'month'             => '3',
								'is_new_month'        => false,
								'is_new_week'         => false,
						],
				2  =>
						[
								'epoch'             => 1551772800,
								'date_stamp'        => '2019-03-05',
								'start_day_of_week' => 0,
								'day_of_week'       => null,
								'day_of_month'      => null,
								'month_name'        => null,
								'month_short_name'  => '',
								'month'             => '3',
								'is_new_month'        => false,
								'is_new_week'         => false,
						],
				3  =>
						[
								'epoch'             => 1551859200,
								'date_stamp'        => '2019-03-06',
								'start_day_of_week' => 0,
								'day_of_week'       => null,
								'day_of_month'      => null,
								'month_name'        => null,
								'month_short_name'  => '',
								'month'             => '3',
								'is_new_month'        => false,
								'is_new_week'         => false,
						],
				4  =>
						[
								'epoch'             => 1551945600,
								'date_stamp'        => '2019-03-07',
								'start_day_of_week' => 0,
								'day_of_week'       => null,
								'day_of_month'      => null,
								'month_name'        => null,
								'month_short_name'  => '',
								'month'             => '3',
								'is_new_month'        => false,
								'is_new_week'         => false,
						],
				5  =>
						[
								'epoch'             => 1552032000,
								'date_stamp'        => '2019-03-08',
								'start_day_of_week' => 0,
								'day_of_week'       => null,
								'day_of_month'      => null,
								'month_name'        => null,
								'month_short_name'  => '',
								'month'             => '3',
								'is_new_month'        => false,
								'is_new_week'         => false,
						],
				6  =>
						[
								'epoch'             => 1552118400,
								'date_stamp'        => '2019-03-09',
								'start_day_of_week' => 0,
								'day_of_week'       => null,
								'day_of_month'      => null,
								'month_name'        => null,
								'month_short_name'  => '',
								'month'             => '3',
								'is_new_month'        => false,
								'is_new_week'         => false,
						],
				7  =>
						[
								'epoch'             => 1552204800,
								'date_stamp'        => '2019-03-10',
								'start_day_of_week' => 0,
								'day_of_week'       => 'Sun',
								'day_of_month'      => '10',
								'month_name'        => 'March',
								'month_short_name'  => 'Mar',
								'month'             => '3',
								'is_new_month'        => false,
								'is_new_week'         => true,
						],
				8  =>
						[
								'epoch'             => 1552291200,
								'date_stamp'        => '2019-03-11',
								'start_day_of_week' => 0,
								'day_of_week'       => 'Mon',
								'day_of_month'      => '11',
								'month_name'        => 'March',
								'month_short_name'  => 'Mar',
								'month'             => '3',
								'is_new_month'        => false,
								'is_new_week'         => false,
						],
				9  =>
						[
								'epoch'             => 1552377600,
								'date_stamp'        => '2019-03-12',
								'start_day_of_week' => 0,
								'day_of_week'       => null,
								'day_of_month'      => null,
								'month_name'        => null,
								'month_short_name'  => '',
								'month'             => '3',
								'is_new_month'        => false,
								'is_new_week'         => false,
						],
				10 =>
						[
								'epoch'             => 1552464000,
								'date_stamp'        => '2019-03-13',
								'start_day_of_week' => 0,
								'day_of_week'       => null,
								'day_of_month'      => null,
								'month_name'        => null,
								'month_short_name'  => '',
								'month'             => '3',
								'is_new_month'        => false,
								'is_new_week'         => false,
						],
				11 =>
						[
								'epoch'             => 1552550400,
								'date_stamp'        => '2019-03-14',
								'start_day_of_week' => 0,
								'day_of_week'       => null,
								'day_of_month'      => null,
								'month_name'        => null,
								'month_short_name'  => '',
								'month'             => '3',
								'is_new_month'        => false,
								'is_new_week'         => false,
						],
				12 =>
						[
								'epoch'             => 1552636800,
								'date_stamp'        => '2019-03-15',
								'start_day_of_week' => 0,
								'day_of_week'       => null,
								'day_of_month'      => null,
								'month_name'        => null,
								'month_short_name'  => '',
								'month'             => '3',
								'is_new_month'        => false,
								'is_new_week'         => false,
						],
				13 =>
						[
								'epoch'             => 1552723200,
								'date_stamp'        => '2019-03-16',
								'start_day_of_week' => 0,
								'day_of_week'       => null,
								'day_of_month'      => null,
								'month_name'        => null,
								'month_short_name'  => '',
								'month'             => '3',
								'is_new_month'        => false,
								'is_new_week'         => false,
						],
		];

		$this->assertEquals( $calendar_arr, $match_arr );
	}

	function testIncrementDate() {
		TTDate::setTimeZone( 'America/Vancouver', true ); //Force to timezone that observes DST.

		//Increment date across DST switch-over
		$this->assertEquals( TTDate::incrementDate( strtotime( '09-Mar-2019 12:00:00 PM' ), 1, 'day' ), strtotime( '10-Mar-2019 12:00:00 PM' ) );
		$this->assertEquals( TTDate::incrementDate( strtotime( '09-Mar-2019 12:00:00 PM' ), 2, 'day' ), strtotime( '11-Mar-2019 12:00:00 PM' ) );

		$this->assertEquals( TTDate::incrementDate( strtotime( '02-Nov-2019 12:00:00 PM' ), 1, 'day' ), strtotime( '03-Nov-2019 12:00:00 PM' ) );
		$this->assertEquals( TTDate::incrementDate( strtotime( '02-Nov-2019 12:00:00 PM' ), 2, 'day' ), strtotime( '04-Nov-2019 12:00:00 PM' ) );

		//General increments
		$this->assertEquals( TTDate::incrementDate( strtotime( '02-Nov-2019 12:00:00 PM' ), 1, 'week' ), strtotime( '09-Nov-2019 12:00:00 PM' ) );

		$this->assertEquals( TTDate::incrementDate( strtotime( '02-Nov-2019 12:00:00 PM' ), 1, 'month' ), strtotime( '02-Dec-2019 12:00:00 PM' ) );
		$this->assertEquals( TTDate::incrementDate( strtotime( '15-Nov-2019 12:00:00 PM' ), 1, 'month' ), strtotime( '15-Dec-2019 12:00:00 PM' ) );
		$this->assertEquals( TTDate::incrementDate( strtotime( '31-Dec-2019 12:00:00 PM' ), -1, 'month' ), strtotime( '30-Nov-2019 12:00:00 PM' ) ); //December has 31 days, and Nov only has 30.
		$this->assertEquals( TTDate::incrementDate( strtotime( '31-Jan-2019 12:00:00 PM' ), 1, 'month' ), strtotime( '28-Feb-2019 12:00:00 PM' ) ); //January has 31 days, and Feb 2019 only has 28.
		$this->assertEquals( TTDate::incrementDate( strtotime( '31-Mar-2021 12:00:00 PM' ), -1, 'month' ), strtotime( '28-Feb-2021 12:00:00 PM' ) ); //March has 31 days, and Apr 2021 only has 30.
		$this->assertEquals( TTDate::incrementDate( strtotime( '31-Mar-2021 12:00:00 PM' ), 1, 'month' ), strtotime( '30-Apr-2021 12:00:00 PM' ) ); //March has 31 days, and Apr 2021 only has 30.
		$this->assertEquals( TTDate::incrementDate( strtotime( '28-Feb-2021 12:00:00 PM' ), 1, 'month' ), strtotime( '28-Mar-2021 12:00:00 PM' ) ); //March has 31 days, and Apr 2021 only has 30.
		$this->assertEquals( TTDate::incrementDate( strtotime( '31-Dec-2019 12:00:00 PM' ), 1, 'month' ), strtotime( '31-Jan-2020 12:00:00 PM' ) ); //Test crossing year boundary
		$this->assertEquals( TTDate::incrementDate( strtotime( '29-Feb-2020 12:00:00 PM' ), 12, 'month' ), strtotime( '28-Feb-2021 12:00:00 PM' ) );
		$this->assertEquals( TTDate::incrementDate( strtotime( '29-Feb-2020 12:00:00 PM' ), -12, 'month' ), strtotime( '28-Feb-2019 12:00:00 PM' ) );


		$this->assertEquals( TTDate::incrementDate( strtotime( '02-Nov-2019 12:00:00 PM' ), 1, 'year' ), strtotime( '02-Nov-2020 12:00:00 PM' ) );
		$this->assertEquals( TTDate::incrementDate( strtotime( '29-Feb-2020 12:00:00 PM' ), 1, 'year' ), strtotime( '28-Feb-2021 12:00:00 PM' ) );
		$this->assertEquals( TTDate::incrementDate( strtotime( '29-Feb-2020 12:00:00 PM' ), -1, 'year' ), strtotime( '28-Feb-2019 12:00:00 PM' ) );
		$this->assertEquals( TTDate::incrementDate( strtotime( '29-Feb-2020 12:00:00 PM' ), 4, 'quarter' ), strtotime( '28-Feb-2021 12:00:00 PM' ) );
		$this->assertEquals( TTDate::incrementDate( strtotime( '29-Feb-2020 12:00:00 PM' ), -4, 'quarter' ), strtotime( '28-Feb-2019 12:00:00 PM' ) );

		$this->assertEquals( TTDate::incrementDate( strtotime( '31-Mar-2021 12:00:00 PM' ), 1, 'quarter' ), strtotime( '30-Jun-2021 12:00:00 PM' ) );
		$this->assertEquals( TTDate::incrementDate( strtotime( '30-Sep-2021 12:00:00 PM' ), 1, 'quarter' ), strtotime( '30-Dec-2021 12:00:00 PM' ) );
		$this->assertEquals( TTDate::incrementDate( strtotime( '30-Jun-2021 12:00:00 PM' ), -1, 'quarter' ), strtotime( '30-Mar-2021 12:00:00 PM' ) );

		$this->assertEquals( TTDate::incrementDate( strtotime( '09-Nov-2019 12:00:00 PM' ), -1, 'week' ), strtotime( '02-Nov-2019 12:00:00 PM' ) );
		$this->assertEquals( TTDate::incrementDate( strtotime( '02-Dec-2019 12:00:00 PM' ), -1, 'month' ), strtotime( '02-Nov-2019 12:00:00 PM' ) );
		$this->assertEquals( TTDate::incrementDate( strtotime( '02-Nov-2020 12:00:00 PM' ), -1, 'year' ), strtotime( '02-Nov-2019 12:00:00 PM' ) );
	}

	function testDatePeriodGenerator() {
		TTDate::setTimeZone( 'America/Vancouver', true ); //Force to timezone that observes DST.

		//Test original loop code vs. new loop code.
		$original_date_arr = $new_date_arr = [];
		$x = 0;
		for ( $date = TTDate::getMiddleDayEpoch( strtotime( '27-Oct-19 12:00 PM' ) ); $date < strtotime( '28-Oct-19 11:59:59 PM' ); $date += 86400 ) {
			switch ( $x ) {
				case 0:
					$this->assertEquals( '27-Oct-19 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 1:
					$this->assertEquals( '28-Oct-19 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
			}

			$original_date_arr[] = $x;
			$x++;
		}
		$this->assertEquals( 2, $x );

		$period = TTDate::getDatePeriod( TTDate::getMiddleDayEpoch( strtotime( '27-Oct-19 12:00 PM' ) ), strtotime( '28-Oct-19 11:59:59 PM' ), 'P1D', false );
		$x = 0;
		foreach ( $period as $i => $date ) {
			switch ( $x ) {
				case 0:
					$this->assertEquals( '27-Oct-19 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 1:
					$this->assertEquals( '28-Oct-19 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
			}

			$new_date_arr[] = $x;
			$x++;
		}
		$this->assertEquals( 2, $x );
		$this->assertEquals( $original_date_arr, $new_date_arr );

		//Test original loop code vs. new loop code.
		$original_date_arr = $new_date_arr = [];
		$x = 0;
		for ( $date = TTDate::getMiddleDayEpoch( strtotime( '27-Oct-19 12:00 AM' ) ); $date < strtotime( '28-Oct-19 12:00 PM' ); $date += 86400 ) {
			switch ( $x ) {
				case 0:
					$this->assertEquals( '27-Oct-19 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
			}

			$original_date_arr[] = $x;
			$x++;
		}
		$this->assertEquals( 1, $x );

		$period = TTDate::getDatePeriod( TTDate::getMiddleDayEpoch( strtotime( '27-Oct-19 12:00 AM' ) ), strtotime( '28-Oct-19 12:00 PM' ), 'P1D', false );
		$x = 0;
		foreach ( $period as $i => $date ) {
			switch ( $x ) {
				case 0:
					$this->assertEquals( '27-Oct-19 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
			}

			$new_date_arr[] = $x;
			$x++;
		}
		$this->assertEquals( 1, $x );
		$this->assertEquals( $original_date_arr, $new_date_arr );


		//Test original loop code vs. new loop code.
		$original_date_arr = $new_date_arr = [];
		$x = 0;
		for ( $date = ( TTDate::getMiddleDayEpoch( strtotime( '20-Nov-2018 00:00:00' ) ) - 86400 ); $date <= ( TTDate::getMiddleDayEpoch( strtotime( '20-Nov-2018 23:59:59' ) ) + 86400 ); $date += 86400 ) {
			switch ( $x ) {
				case 0:
					$this->assertEquals( '19-Nov-18 12:00 PM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 1:
					$this->assertEquals( '20-Nov-18 12:00 PM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 2:
					$this->assertEquals( '21-Nov-18 12:00 PM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
			}

			$original_date_arr[] = $x;
			$x++;
		}
		$this->assertEquals( 3, $x );

		$period = TTDate::getDatePeriod( ( TTDate::getMiddleDayEpoch( strtotime( '20-Nov-2018 00:00:00' ) ) - 86400 ), ( TTDate::getMiddleDayEpoch( strtotime( '20-Nov-2018 23:59:59' ) ) + 86400 ) );
		$x = 0;
		foreach ( $period as $i => $date ) {
			switch ( $x ) {
				case 0:
					$this->assertEquals( '19-Nov-18 12:00 PM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 1:
					$this->assertEquals( '20-Nov-18 12:00 PM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 2:
					$this->assertEquals( '21-Nov-18 12:00 PM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
			}

			$new_date_arr[] = $x;
			$x++;
		}
		$this->assertEquals( 3, $x );
		$this->assertEquals( $original_date_arr, $new_date_arr );


		//Test original loop code vs. new loop code across DST(a).
		$original_date_arr = $new_date_arr = [];
		$x = 0;
		for ( $i = ( TTDate::getMiddleDayEpoch( strtotime( '09-Mar-2019 12:00:00 PM' ) ) - 86400 ); $i <= ( TTDate::getMiddleDayEpoch( strtotime( '11-Mar-2019 12:00:00 PM' ) ) + 86400 ); $i += 86400 ) {
			$original_date_arr[] = $x;
			$x++;
		}
		$this->assertEquals( 4, $x ); //4 iterations is *incorrect* due to DST

		$period = TTDate::getDatePeriod( ( TTDate::getMiddleDayEpoch( strtotime( '09-Mar-2019 12:00:00 PM' ) ) - 86400 ), ( TTDate::getMiddleDayEpoch( strtotime( '11-Mar-2019 12:00:00 PM' ) ) + 86400 ) );
		$x = 0;
		foreach ( $period as $i => $date ) {
			$new_date_arr[] = $x;
			$x++;
		}
		$this->assertEquals( 5, $x ); //5 iterations is correct due to DST

		array_pop( $new_date_arr );
		$this->assertEquals( $original_date_arr, $new_date_arr );


		//Test original loop code vs. new loop code across DST(b).
		$original_date_arr = $new_date_arr = [];
		$x = 0;
		for ( $i = ( TTDate::getMiddleDayEpoch( strtotime( '03-Nov-2013 00:00:00' ) ) - 86400 ); $i <= ( TTDate::getMiddleDayEpoch( strtotime( '03-Nov-2013 10:00:00' ) ) + 86400 ); $i += 86400 ) {
			$original_date_arr[] = $x;
			$x++;
		}
		$this->assertEquals( 3, $x ); //3 iterations is *correct* due to DST

		//$period = TTDate::getDatePeriod( (TTDate::getMiddleDayEpoch(strtotime('03-Nov-2013 00:00:00')) - 86400), (TTDate::getMiddleDayEpoch( strtotime('03-Nov-2013 10:00:00') ) + 86400) ); //This only returns 2 days.
		$period = TTDate::getDatePeriod( TTDate::incrementDate( strtotime( '03-Nov-2013 00:00:00' ), -1, 'day' ), TTDate::incrementDate( strtotime( '03-Nov-2013 10:00:00' ), 1, 'day' ) );
		$x = 0;
		foreach ( $period as $i => $date ) {
			switch ( $i ) {
				case 0:
					$this->assertEquals( '02-Nov-13 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 1:
					$this->assertEquals( '03-Nov-13 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 2:
					$this->assertEquals( '04-Nov-13 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
			}

			$new_date_arr[] = $x;
			$x++;
		}
		$this->assertEquals( 3, $x ); //3 iterations is correct due to DST

		$this->assertEquals( $original_date_arr, $new_date_arr );


		$period = TTDate::getDatePeriod( strtotime( '09-Mar-2019 12:00:00 PM' ), strtotime( '10-Mar-2019 12:00:00 PM' ) );
		$x = 0;
		foreach ( $period as $i => $date ) {
			switch ( $i ) {
				case 0:
					$this->assertEquals( '09-Mar-19 12:00 PM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 1:
					$this->assertEquals( '10-Mar-19 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
			}
			$x++;
		}
		$this->assertEquals( 2, $x );

		$period = TTDate::getDatePeriod( strtotime( '09-Mar-2019 12:00:00 PM' ), strtotime( '10-Mar-2019 12:00:01 PM' ) );
		$x = 0;
		foreach ( $period as $i => $date ) {
			switch ( $i ) {
				case 0:
					$this->assertEquals( '09-Mar-19 12:00 PM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 1:
					$this->assertEquals( '10-Mar-19 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
			}
			$x++;
		}
		$this->assertEquals( 2, $x );


		$period = TTDate::getDatePeriod( strtotime( '09-Mar-2019 12:00:00 PM' ), strtotime( '11-Mar-2019 12:00:00 PM' ) );
		foreach ( $period as $i => $date ) {
			switch ( $i ) {
				case 0:
					$this->assertEquals( '09-Mar-19 12:00 PM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 1:
					$this->assertEquals( '10-Mar-19 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 2:
					$this->assertEquals( '11-Mar-19 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
			}
		}

		$period = TTDate::getDatePeriod( strtotime( '09-Mar-2019 12:00 AM' ), strtotime( '11-Mar-2019 12:00 AM' ) );
		foreach ( $period as $i => $date ) {
			switch ( $i ) {
				case 0:
					$this->assertEquals( '09-Mar-19 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 1:
					$this->assertEquals( '10-Mar-19 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 2:
					$this->assertEquals( '11-Mar-19 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
			}
		}


		$period = TTDate::getDatePeriod( strtotime( '02-Nov-2019 12:00:00 PM' ), strtotime( '04-Nov-2019 12:00:00 PM' ) );
		foreach ( $period as $i => $date ) {
			switch ( $i ) {
				case 0:
					$this->assertEquals( '02-Nov-19 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 1:
					$this->assertEquals( '03-Nov-19 12:00 PM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 2:
					$this->assertEquals( '04-Nov-19 12:00 PM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
			}
		}

		$period = TTDate::getDatePeriod( strtotime( '02-Nov-2019 12:00:00 AM' ), strtotime( '04-Nov-2019 12:00:00 AM' ) );
		foreach ( $period as $i => $date ) {
			switch ( $i ) {
				case 0:
					$this->assertEquals( '02-Nov-19 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 1:
					$this->assertEquals( '03-Nov-19 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 2:
					$this->assertEquals( '04-Nov-19 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
			}
		}

		$period = TTDate::getDatePeriod( strtotime( '02-Nov-2019 12:00:00 PM' ), strtotime( '03-Nov-2019 12:00:00 PM' ), 'PT1H' ); //Every hour.
		foreach ( $period as $i => $date ) {
			switch ( $i ) {
				case 0:
					$this->assertEquals( '02-Nov-19 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 1:
					$this->assertEquals( '02-Nov-19 1:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 2:
					$this->assertEquals( '02-Nov-19 2:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 3:
					$this->assertEquals( '02-Nov-19 3:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 4:
					$this->assertEquals( '02-Nov-19 4:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 5:
					$this->assertEquals( '02-Nov-19 5:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 6:
					$this->assertEquals( '02-Nov-19 6:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 7:
					$this->assertEquals( '02-Nov-19 7:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 8:
					$this->assertEquals( '02-Nov-19 8:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 9:
					$this->assertEquals( '02-Nov-19 9:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 10:
					$this->assertEquals( '02-Nov-19 10:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 11:
					$this->assertEquals( '02-Nov-19 11:00 PM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 12:
					$this->assertEquals( '03-Nov-19 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 13:
					$this->assertEquals( '03-Nov-19 1:00 AM PDT', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 14:
					$this->assertEquals( '03-Nov-19 2:00 AM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 15:
					$this->assertEquals( '03-Nov-19 3:00 AM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 16:
					$this->assertEquals( '03-Nov-19 4:00 AM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 17:
					$this->assertEquals( '03-Nov-19 5:00 AM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 18:
					$this->assertEquals( '03-Nov-19 6:00 AM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 19:
					$this->assertEquals( '03-Nov-19 7:00 AM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 20:
					$this->assertEquals( '03-Nov-19 8:00 AM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 21:
					$this->assertEquals( '03-Nov-19 9:00 AM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 22:
					$this->assertEquals( '03-Nov-19 10:00 AM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 23:
					$this->assertEquals( '03-Nov-19 11:00 AM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
				case 24:
					$this->assertEquals( '03-Nov-19 12:00 PM PST', TTDate::getDate( 'DATE+TIME', $date ) );
					break;
			}
		}
	}

	function testDateOfNextQuarter() {
		$this->assertEquals( '01-Jul-19 12:00 PM -08', TTDate::getDate( 'DATE+TIME', TTDate::getDateOfNextQuarter( strtotime( '01-Apr-19 12:00:00' ), 1, 1 ) ) );

		$this->assertEquals( '03-Oct-81 12:00 PM -08', TTDate::getDate( 'DATE+TIME', TTDate::getDateOfNextQuarter( strtotime( '11-Sep-1981 12:00:00' ), 3, 1 ) ) );

		$this->assertEquals( '30-Apr-19 12:00 PM -08', TTDate::getDate( 'DATE+TIME', TTDate::getDateOfNextQuarter( strtotime( '31-Jan-2019 12:00:00' ), 31, 1 ) ) );
		$this->assertEquals( '31-Jul-19 12:00 PM -08', TTDate::getDate( 'DATE+TIME', TTDate::getDateOfNextQuarter( strtotime( '30-Apr-2019 12:00:00' ), 31, 1 ) ) );
		$this->assertEquals( '31-Oct-19 12:00 PM -08', TTDate::getDate( 'DATE+TIME', TTDate::getDateOfNextQuarter( strtotime( '31-Jul-2019 12:00:00' ), 31, 1 ) ) );
		$this->assertEquals( '31-Jan-20 12:00 PM -08', TTDate::getDate( 'DATE+TIME', TTDate::getDateOfNextQuarter( strtotime( '31-Oct-2019 12:00:00' ), 31, 1 ) ) );
	}

	function testGetDateArray() {
		$date_arr = TTDate::getDateArray( strtotime( '01-Dec-2016' ), strtotime( '31-Dec-2016' ) ); //No DayOfWeek filter.
		$this->assertCount( 31, $date_arr );

		$date_arr = TTDate::getDateArray( strtotime( '01-Dec-2016' ), strtotime( '31-Dec-2016' ), false ); //No DayOfWeek filter.
		$this->assertCount( 31, $date_arr );

		$date_arr = TTDate::getDateArray( strtotime( '01-Dec-2016' ), strtotime( '31-Dec-2016' ), 0 ); //Filter Sundays
		$this->assertCount( 4, $date_arr );
		$this->assertEquals( $date_arr[0], strtotime( '04-Dec-2016' ) );
		$this->assertEquals( $date_arr[1], strtotime( '11-Dec-2016' ) );
		$this->assertEquals( $date_arr[2], strtotime( '18-Dec-2016' ) );
		$this->assertEquals( $date_arr[3], strtotime( '25-Dec-2016' ) );

		$date_arr = TTDate::getDateArray( strtotime( '01-Dec-2016' ), strtotime( '31-Dec-2016' ), 1 ); //Filter Mondays
		$this->assertCount( 4, $date_arr );

		$date_arr = TTDate::getDateArray( strtotime( '01-Dec-2016' ), strtotime( '31-Dec-2016' ), 2 ); //Filter Tuesdays
		$this->assertCount( 4, $date_arr );

		$date_arr = TTDate::getDateArray( strtotime( '01-Dec-2016' ), strtotime( '31-Dec-2016' ), 3 ); //Filter Wednesday
		$this->assertCount( 4, $date_arr );

		$date_arr = TTDate::getDateArray( strtotime( '01-Dec-2016' ), strtotime( '31-Dec-2016' ), 4 ); //Filter Thursdays
		$this->assertCount( 5, $date_arr );

		$date_arr = TTDate::getDateArray( strtotime( '01-Dec-2016' ), strtotime( '31-Dec-2016' ), 5 ); //Filter Fridays
		$this->assertCount( 5, $date_arr );

		$date_arr = TTDate::getDateArray( strtotime( '01-Dec-2016' ), strtotime( '31-Dec-2016' ), 6 ); //Filter Saturdays
		$this->assertCount( 5, $date_arr );
		$this->assertEquals( $date_arr[0], strtotime( '03-Dec-2016' ) );
		$this->assertEquals( $date_arr[1], strtotime( '10-Dec-2016' ) );
		$this->assertEquals( $date_arr[2], strtotime( '17-Dec-2016' ) );
		$this->assertEquals( $date_arr[3], strtotime( '24-Dec-2016' ) );
		$this->assertEquals( $date_arr[4], strtotime( '31-Dec-2016' ) );
	}

	function testIsConsecutiveDays() {
		//Use timezone that observes DST.
		TTDate::setTimeZone( 'America/Vancouver', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		//Spring DST change
		$date_array = [
				strtotime( 'Fri, 10 Mar 2017 00:00:00 -0800' ),
				strtotime( 'Sat, 11 Mar 2017 00:00:00 -0800' ),
				strtotime( 'Sun, 12 Mar 2017 00:00:00 -0700' ),
		];
		$this->assertEquals( true, TTDate::isConsecutiveDays( $date_array ) );


		$date_array = [
				strtotime( 'Thu, 09 Mar 2017 00:00:00 -0800' ),
				strtotime( 'Sat, 10 Mar 2017 00:00:00 -0800' ),
				strtotime( 'Sun, 12 Mar 2017 00:00:00 -0700' ),
		];
		$this->assertEquals( false, TTDate::isConsecutiveDays( $date_array ) );


		$date_array = [
				strtotime( 'Thu, 09 Mar 2017 00:00:00 -0800' ),
				strtotime( 'Sat, 11 Mar 2017 00:00:00 -0800' ),
				strtotime( 'Sun, 12 Mar 2017 00:00:00 -0700' ),
		];
		$this->assertEquals( false, TTDate::isConsecutiveDays( $date_array ) );

		//FALL DST change.
		$date_array = [
				1509692400, //strtotime('Fri, 03 Nov 2017 00:00:00 -0700'), //1509692400=Fri, 03 Nov 2017 00:00:00 -0700
				1509778800, //strtotime('Sat, 04 Nov 2017 00:00:00 -0700'), //1509778800=Sat, 04 Nov 2017 00:00:00 -0700
				//1509865200, //strtotime('Sun, 05 Nov 2017 00:00:00 -0800'), //1509912000=Sun, 05 Nov 2017 00:00:00 -0800
				1509912000, //strtotime('Sun, 05 Nov 2017 00:00:00 -0800'), //1509912000=Sun, 05 Nov 2017 12:00:00 -0800
		];
		$this->assertEquals( true, TTDate::isConsecutiveDays( $date_array ) );

		$date_array = [
				1509692400, //strtotime('Fri, 03 Nov 2017 00:00:00 -0700'), //1509692400=Fri, 03 Nov 2017 00:00:00 -0700
				1509778800, //strtotime('Sat, 04 Nov 2017 00:00:00 -0700'), //1509778800=Sat, 04 Nov 2017 00:00:00 -0700
				1509865200, //strtotime('Sun, 05 Nov 2017 00:00:00 -0800'), //1509912000=Sun, 05 Nov 2017 00:00:00 -0800
				//1509912000, //strtotime('Sun, 05 Nov 2017 00:00:00 -0800'), //1509912000=Sun, 05 Nov 2017 12:00:00 -0800
		];
		$this->assertEquals( true, TTDate::isConsecutiveDays( $date_array ) );


		$date_array = [
				strtotime( 'Fri, 03 Nov 2017 00:00:00 -0700' ), //1509692400=Fri, 03 Nov 2017 00:00:00 -0700
				strtotime( 'Sat, 04 Nov 2017 00:00:00 -0700' ), //1509778800=Sat, 04 Nov 2017 00:00:00 -0700
				strtotime( 'Sun, 05 Nov 2017 00:00:00 -0800' ), //1509912000=Sun, 05 Nov 2017 00:00:00 -0800
		];
		$this->assertEquals( true, TTDate::isConsecutiveDays( $date_array ) );
	}

	function testLocationTimeZone() {
		$upf = TTnew( 'UserPreferenceFactory' ); /** @var UserPreferenceFactory $upf */

		$this->assertEquals( 'America/Vancouver', $upf->getLocationTimeZone( 'CA', 'BC' ) );

		$this->assertEquals( 'America/Los_Angeles', $upf->getLocationTimeZone( 'US', 'WA' ) );
		$this->assertEquals( 'America/Los_Angeles', $upf->getLocationTimeZone( 'US', 'WA', '2065555555' ) );
		$this->assertEquals( 'America/New_York', $upf->getLocationTimeZone( 'US', 'NY' ) );
		$this->assertEquals( 'America/New_York', $upf->getLocationTimeZone( 'US', 'NY', '2065555555' ) ); //Province doesn't match, so it uses that instead.

		$this->assertEquals( 'America/New_York', $upf->getLocationTimeZone( 'US', 'FL', '8135555555' ) ); //Same state with different timezones based on phone number.
		$this->assertEquals( 'America/Chicago', $upf->getLocationTimeZone( 'US', 'FL', '8505555555' ) );  //Same state with different timezones based on phone number.

		$this->assertEquals( 'America/Antigua', $upf->getLocationTimeZone( 'AG', '' ) );
		$this->assertEquals( 'America/Antigua', $upf->getLocationTimeZone( 'AG', '00' ) );

		$this->assertEquals( 'America/Nassau', $upf->getLocationTimeZone( 'BS', '' ) );
		$this->assertEquals( 'America/Nassau', $upf->getLocationTimeZone( 'BS', '', '2525555555' ) );
		$this->assertEquals( 'America/Nassau', $upf->getLocationTimeZone( 'BS', '00', '2525555555' ) );
	}

	function testISODateParsing() {
		TTDate::setTimeZone( 'America/New_York', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		$this->assertEquals( '2019-01-31', TTDate::getISODateStamp( strtotime( '31-Jan-2019' ) ) );
		$test_date = strtotime( TTDate::getISODateStamp( strtotime( '31-Jan-2019' ) ) );
		$this->assertEquals( '2019-01-31', TTDate::getISODateStamp( $test_date ) );

		$test_date = TTDate::getISODateStamp( strtotime( '31-Jan-2019' ) ); //As if we are passing a string date through an API
		TTDate::setTimeZone( 'America/Vancouver', true ); //Change timezone to something different as its an API server.
		$this->assertEquals( strtotime( $test_date ), strtotime( '31-Jan-2019' ) ); //Parse string date to the same value on the API server.


		TTDate::setTimeZone( 'America/New_York', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		$this->assertEquals( 'Thu, 31 Jan 2019 22:22:22 -0500', TTDate::getISOTimeStamp( strtotime( '31-Jan-2019 22:22:22' ) ) );
		$test_date = strtotime( TTDate::getISOTimeStamp( strtotime( '31-Jan-2019 22:22:22' ) ) );
		$this->assertEquals( 'Thu, 31 Jan 2019 22:22:22 -0500', TTDate::getISOTimeStamp( $test_date ) );

		$test_date = TTDate::getISOTimeStamp( strtotime( '31-Jan-2019 22:22:22' ) ); //As if we are passing a string date through an API
		TTDate::setTimeZone( 'America/Vancouver', true ); //Change timezone to something different as its an API server.
		$this->assertEquals( strtotime( $test_date ), strtotime( 'Thu, 31 Jan 2019 22:22:22 -0500' ) ); //Parse string date to the same value on the API server.
	}

	function testHumanTimeSince() {
		$this->assertEquals( '0.0 sec', TTDate::getHumanTimeSince( strtotime( '01-Dec-2019 12:00:00' ), strtotime( '01-Dec-2019 12:00:00' ) ) );
		$this->assertEquals( '2.0 secs', TTDate::getHumanTimeSince( strtotime( '01-Dec-2019 12:00:02' ), strtotime( '01-Dec-2019 12:00:00' ) ) );
		$this->assertEquals( '2.0 secs', TTDate::getHumanTimeSince( strtotime( '01-Dec-2019 11:59:58' ), strtotime( '01-Dec-2019 12:00:00' ) ) );
		$this->assertEquals( '62.0 secs', TTDate::getHumanTimeSince( strtotime( '01-Dec-2019 12:01:02' ), strtotime( '01-Dec-2019 12:00:00' ) ) );
		$this->assertEquals( '3.0 mins', TTDate::getHumanTimeSince( strtotime( '01-Dec-2019 11:57:02' ), strtotime( '01-Dec-2019 12:00:00' ) ) );
		$this->assertEquals( '2.0 hrs', TTDate::getHumanTimeSince( strtotime( '01-Dec-2019 09:57:02' ), strtotime( '01-Dec-2019 12:00:00' ) ) );
		$this->assertEquals( '48.0 hrs', TTDate::getHumanTimeSince( strtotime( '29-Nov-2019 12:00:00' ), strtotime( '01-Dec-2019 12:00:00' ) ) );
		$this->assertEquals( '3.0 days', TTDate::getHumanTimeSince( strtotime( '28-Nov-2019 12:00:00' ), strtotime( '01-Dec-2019 12:00:00' ) ) );
		$this->assertEquals( '3.0 days', TTDate::getHumanTimeSince( strtotime( '04-Dec-2019 12:00:00' ), strtotime( '01-Dec-2019 12:00:00' ) ) );
		$this->assertEquals( '8.0 days', TTDate::getHumanTimeSince( strtotime( '23-Nov-2019 12:00:00' ), strtotime( '01-Dec-2019 12:00:00' ) ) );
		$this->assertEquals( '2.1 wks', TTDate::getHumanTimeSince( strtotime( '16-Nov-2019 12:00:00' ), strtotime( '01-Dec-2019 12:00:00' ) ) );
		$this->assertEquals( '4.3 wks', TTDate::getHumanTimeSince( strtotime( '01-Nov-2019 12:00:00' ), strtotime( '01-Dec-2019 12:00:00' ) ) );
		$this->assertEquals( '4.0 mths', TTDate::getHumanTimeSince( strtotime( '01-Aug-2019 12:00:00' ), strtotime( '01-Dec-2019 12:00:00' ) ) );
		$this->assertEquals( '12.0 mths', TTDate::getHumanTimeSince( strtotime( '01-Dec-2018 12:00:00' ), strtotime( '01-Dec-2019 12:00:00' ) ) );
		$this->assertEquals( '24.0 mths', TTDate::getHumanTimeSince( strtotime( '01-Dec-2017 12:00:00' ), strtotime( '01-Dec-2019 12:00:00' ) ) );
		$this->assertEquals( '3.0 yrs', TTDate::getHumanTimeSince( strtotime( '01-Dec-2016 12:00:00' ), strtotime( '01-Dec-2019 12:00:00' ) ) );
		$this->assertEquals( '19.0 yrs', TTDate::getHumanTimeSince( strtotime( '01-Dec-2000 12:00:00' ), strtotime( '01-Dec-2019 12:00:00' ) ) );
		$this->assertEquals( '114.0 yrs', TTDate::getHumanTimeSince( strtotime( '01-Dec-1905 12:00:00' ), strtotime( '01-Dec-2019 12:00:00' ) ) );
		$this->assertEquals( '81.0 yrs', TTDate::getHumanTimeSince( strtotime( '01-Dec-2100 12:00:00' ), strtotime( '01-Dec-2019 12:00:00' ) ) );
	}

	function testTimeStampOverLap() {
		$this->assertEquals( true, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 17:00:00' ) ) ); //Exact match
		$this->assertEquals( false, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '02-Dec-2019 8:00:00' ), strtotime( '02-Dec-2019 17:00:00' ) ) );
		$this->assertEquals( false, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 17:05:00' ), strtotime( '01-Dec-2019 23:00:00' ) ) );

		$this->assertEquals( false, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 23:00:00' ) ) ); //Does not overlap, must be able to end one shift and start a new one at the same time.
		$this->assertEquals( false, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 23:00:00' ), strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 17:00:00' ) ) ); //Does not overlap, must be able to end one shift and start a new one at the same time.

		$this->assertEquals( false, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 01:00:00' ), strtotime( '01-Dec-2019 08:00:00' ) ) ); //Does not overlap, must be able to end one shift and start a new one at the same time.
		$this->assertEquals( false, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 01:00:00' ), strtotime( '01-Dec-2019 08:00:00' ), strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 17:00:00' ) ) ); //Does not overlap, must be able to end one shift and start a new one at the same time.

		$this->assertEquals( false, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 23:00:00' ) ) );
		$this->assertEquals( true, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 16:59:59' ), strtotime( '01-Dec-2019 23:00:00' ) ) );
		$this->assertEquals( true, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 12:00:00' ), strtotime( '01-Dec-2019 23:00:00' ) ) );
		$this->assertEquals( true, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 08:00:00' ), strtotime( '01-Dec-2019 23:00:00' ) ) );
		$this->assertEquals( true, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 07:59:59' ), strtotime( '01-Dec-2019 23:00:00' ) ) );
		$this->assertEquals( true, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 01:00:00' ), strtotime( '01-Dec-2019 23:00:00' ) ) );
		$this->assertEquals( true, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 01:00:00' ), strtotime( '01-Dec-2019 17:00:01' ) ) );
		$this->assertEquals( true, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 01:00:00' ), strtotime( '01-Dec-2019 12:00:00' ) ) );
		$this->assertEquals( false, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 01:00:00' ), strtotime( '01-Dec-2019 08:00:00' ) ) ); //Does not overlap, must be able to end one shift and start a new one at the same time.
		$this->assertEquals( false, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 01:00:00' ), strtotime( '01-Dec-2019 07:59:59' ) ) );
		$this->assertEquals( false, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 01:00:00' ), strtotime( '01-Dec-2019 07:00:00' ) ) );

		$this->assertEquals( false, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 23:00:00' ) ) );
		$this->assertEquals( true, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 17:00:01' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 23:00:00' ) ) );
		$this->assertEquals( true, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 20:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 23:00:00' ) ) );
		$this->assertEquals( true, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 23:00:00' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 23:00:00' ) ) );
		$this->assertEquals( true, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 23:00:01' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 23:00:00' ) ) );
		$this->assertEquals( true, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 8:00:00' ), strtotime( '01-Dec-2019 23:59:01' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 23:00:00' ) ) );
		$this->assertEquals( true, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 16:59:59' ), strtotime( '01-Dec-2019 23:59:01' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 23:00:00' ) ) );
		$this->assertEquals( true, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 17:00:01' ), strtotime( '01-Dec-2019 23:59:01' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 23:00:00' ) ) );
		$this->assertEquals( true, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 22:59:59' ), strtotime( '01-Dec-2019 23:59:01' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 23:00:00' ) ) );
		$this->assertEquals( false, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 23:00:00' ), strtotime( '01-Dec-2019 23:59:01' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 23:00:00' ) ) );
		$this->assertEquals( false, TTDate::isTimeOverLap( strtotime( '01-Dec-2019 23:00:01' ), strtotime( '01-Dec-2019 23:59:01' ), strtotime( '01-Dec-2019 17:00:00' ), strtotime( '01-Dec-2019 23:00:00' ) ) );


		$this->assertEquals( false, TTDate::isTimeOverLap( strtotime( '22-Dec-13 12:00 AM PST' ), strtotime( '22-Dec-13 12:00 AM PST' ), strtotime( '29-Dec-13 12:00 AM PST' ), strtotime( '04-Jan-14 11:59 PM PST' ) ) );
		$this->assertEquals( true, TTDate::isTimeOverLap( strtotime( '22-Dec-13 12:00 AM PST' ), strtotime( '22-Dec-13 12:00 AM PST' ), strtotime( '22-Dec-13 12:00 AM PST' ), strtotime( '28-Dec-13 11:59 PM PST' ) ) );
		$this->assertEquals( true, TTDate::isTimeOverLap( strtotime( '22-Dec-13 12:00 AM PST' ), strtotime( '28-Dec-13 11:59 PM PST' ), strtotime( '22-Dec-13 12:00 AM PST' ), strtotime( '22-Dec-13 12:00 AM PST' ) ) );
	}

	function testConvertTimeZone() {
		TTDate::setTimeZone( 'America/Phoenix', true ); //Force to non-DST timezone. 'PST' isnt actually valid, but Arizona doesn't observe DST.

		$this->assertEquals( '15-Jan-21 2:00 PM MST', TTDate::getDate( 'DATE+TIME', TTDate::convertTimeZone( strtotime('15-Jan-2021 11:00:00 AM PST'), 'America/New_York') ) );
		$this->assertEquals( '15-Jan-21 1:00 PM MST', TTDate::getDate( 'DATE+TIME', TTDate::convertTimeZone( strtotime('15-Jan-2021 11:00:00 AM MST'), 'America/New_York') ) );
		$this->assertEquals( '15-Jan-21 11:00 AM MST', TTDate::getDate( 'DATE+TIME', TTDate::convertTimeZone( strtotime('15-Jan-2021 2:00:00 PM EST'), 'America/Vancouver') ) );
		$this->assertEquals( '15-Jan-21 10:00 AM MST', TTDate::getDate( 'DATE+TIME', TTDate::convertTimeZone( strtotime('15-Jan-2021 10:00:00 AM MST'), 'America/Phoenix') ) );
		$this->assertEquals( '15-Jan-21 9:00 AM MST', TTDate::getDate( 'DATE+TIME', TTDate::convertTimeZone( strtotime('15-Jan-2021 10:00:00 AM MDT'), 'America/Phoenix') ) );

		$this->assertEquals( '15-Mar-21 2:00 PM MST', TTDate::getDate( 'DATE+TIME', TTDate::convertTimeZone( strtotime('15-Mar-2021 11:00:00 AM PDT'), 'America/New_York') ) );
		$this->assertEquals( '15-Mar-21 1:00 PM MST', TTDate::getDate( 'DATE+TIME', TTDate::convertTimeZone( strtotime('15-Mar-2021 11:00:00 AM MDT'), 'America/New_York') ) );
		$this->assertEquals( '15-Mar-21 11:00 AM MST', TTDate::getDate( 'DATE+TIME', TTDate::convertTimeZone( strtotime('15-Mar-2021 2:00:00 PM EDT'), 'America/Vancouver') ) );
		$this->assertEquals( '15-Mar-21 10:00 AM MST', TTDate::getDate( 'DATE+TIME', TTDate::convertTimeZone( strtotime('15-Mar-2021 10:00:00 AM MST'), 'America/Phoenix') ) );
		$this->assertEquals( '15-Mar-21 9:00 AM MST', TTDate::getDate( 'DATE+TIME', TTDate::convertTimeZone( strtotime('15-Mar-2021 10:00:00 AM MDT'), 'America/Phoenix') ) );
	}

	function testDateDifference() {
		TTDate::setTimeZone( 'America/Vancouver', true ); //Force to non-DST timezone. 'PST' isnt actually valid.

		$this->assertEquals( 0, TTDate::getDateDifference( strtotime('15-Jan-2021 11:00:00 AM PST'), strtotime('15-Jan-2021 11:00:00 AM PST'), '%a' ) ); //Days
		$this->assertEquals( 1, TTDate::getDateDifference( strtotime('15-Jan-2021 11:00:00 AM PST'), strtotime('16-Jan-2021 11:00:00 AM PST'), '%a' ) ); //Days
		$this->assertEquals( 31, TTDate::getDateDifference( strtotime('15-Jan-2021 11:00:00 AM PST'), strtotime('15-Feb-2021 11:00:00 AM PST'), '%a' ) ); //Days

		$this->assertEquals( 0, TTDate::getDateDifference( strtotime('15-Jan-2021 11:00:00 AM PST'), strtotime('14-Feb-2021 11:00:00 AM PST'), '%m' ) ); //Months
		$this->assertEquals( 1, TTDate::getDateDifference( strtotime('15-Jan-2021 11:00:00 AM PST'), strtotime('15-Feb-2021 11:00:00 AM PST'), '%m' ) ); //Months

		$this->assertEquals( 0, TTDate::getDateDifference( strtotime('15-Jan-2021 11:00:00 AM PST'), strtotime('14-Jan-2022 11:00:00 AM PST'), '%y' ) ); //Years
		$this->assertEquals( 1, TTDate::getDateDifference( strtotime('15-Jan-2021 11:00:00 AM PST'), strtotime('15-Jan-2022 11:00:00 AM PST'), '%y' ) ); //Years

		$this->assertEquals( 10, TTDate::getDateDifference( strtotime('15-Jan-2010 11:00:00 AM PST'), strtotime('15-Jan-2020 11:00:00 AM PST'), '%y' ) ); //Years
		$this->assertEquals( 20, TTDate::getDateDifference( strtotime('15-Jan-2000 11:00:00 AM PST'), strtotime('15-Jan-2020 11:00:00 AM PST'), '%y' ) ); //Years
		$this->assertEquals( 100, TTDate::getDateDifference( strtotime('15-Jan-2000 11:00:00 AM PST'), strtotime('15-Jan-2100 11:00:00 AM PST'), '%y' ) ); //Years
	}

	function testGetISOTimeStampWithMilliseconds() {
		$this->assertEquals( '2021-02-19 14:35:37.000000 -0800', TTDate::getISOTimeStampWithMilliseconds(1613774137) );
		$this->assertEquals( '2021-02-19 14:35:37.000000 -0800', TTDate::getISOTimeStampWithMilliseconds( (float)1613774137) );
		$this->assertEquals( '2021-02-19 14:35:37.000000 -0800', TTDate::getISOTimeStampWithMilliseconds( (float)1613774137.0000) );
		$this->assertEquals( '2021-02-19 14:35:37.000001 -0800', TTDate::getISOTimeStampWithMilliseconds( (float)1613774137.000001) );
	}
}

?>