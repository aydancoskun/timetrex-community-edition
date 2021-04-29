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

class RecurringHolidayTest extends PHPUnit\Framework\TestCase {
	protected $company_id = null;
	protected $user_id = null;
	protected $pay_period_schedule_id = null;
	protected $pay_period_objs = null;
	protected $pay_stub_account_link_arr = null;

	public function setUp(): void {
		global $dd;
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setTimeZone( 'America/Vancouver', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		$dd = new DemoData();
		$dd->setEnableQuickPunch( false ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_' . uniqid( null, true ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		$this->legal_entity_id = $dd->createLegalEntity( $this->company_id, 10 );
		Debug::text( 'Company ID: ' . $this->company_id, __FILE__, __LINE__, __METHOD__, 10 );

		//$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$dd->createCurrency( $this->company_id, 10 );
		$dd->createUserWageGroups( $this->company_id );

		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 100 );
		$user_obj = $this->getUserObject( $this->user_id );
		//Use a consistent hire date, otherwise its difficult to get things correct due to the hire date being in different parts or different pay periods.
		//Make sure it is not on a pay period start date though.
		$user_obj->setHireDate( strtotime( '05-Mar-2001' ) );
		$user_obj->Save( false );

		$this->assertGreaterThan( 0, $this->company_id );
		$this->assertGreaterThan( 0, $this->user_id );
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	function getUserObject( $user_id ) {
		$ulf = TTNew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getById( $user_id );
		if ( $ulf->getRecordCount() > 0 ) {
			return $ulf->getCurrent();
		}

		return false;
	}

	/**
	 * @group RecurringHoliday_testRecurringHolidayDatesA
	 */
	function testRecurringHolidayDatesA() {
		//First Monday in August
		$rhf = TTNew( 'RecurringHolidayFactory' ); /** @var RecurringHolidayFactory $rhf */
		$rhf->setCompany( $this->company_id );
		$rhf->setName( 'BC - British Columbia Day' );
		$rhf->setType( 20 );
		$rhf->setWeekInterval( 1 );
		$rhf->setDayOfWeek( 1 );
		$rhf->setMonth( 8 );
		$rhf->setAlwaysOnWeekDay( 3 ); //Closest

		$next_date = $rhf->getNextDate( strtotime( '15-Aug-2015' ) );
		//Debug::text('Next Date: '. TTDate::getDate('DATE+TIME', $next_date ), __FILE__, __LINE__, __METHOD__, 10);
		$this->assertEquals( $next_date, strtotime( '01-Aug-2016 12:00PM PDT' ) );

		$next_date = $rhf->getNextDate( strtotime( '15-Aug-2014' ) );
		//Debug::text('Next Date: '. TTDate::getDate('DATE+TIME', $next_date ), __FILE__, __LINE__, __METHOD__, 10);
		$this->assertEquals( $next_date, strtotime( '03-Aug-2015 12:00PM PDT' ) );

		$next_date = $rhf->getNextDate( strtotime( '15-Aug-2013' ) );
		//Debug::text('Next Date: '. TTDate::getDate('DATE+TIME', $next_date ), __FILE__, __LINE__, __METHOD__, 10);
		$this->assertEquals( $next_date, strtotime( '04-Aug-2014 12:00PM PDT' ) );

		$next_date = $rhf->getNextDate( strtotime( '15-Aug-2012' ) );
		//Debug::text('Next Date: '. TTDate::getDate('DATE+TIME', $next_date ), __FILE__, __LINE__, __METHOD__, 10);
		$this->assertEquals( $next_date, strtotime( '05-Aug-2013 12:00PM PDT' ) );

		$next_date = $rhf->getNextDate( strtotime( '15-Aug-2011' ) );
		//Debug::text('Next Date: '. TTDate::getDate('DATE+TIME', $next_date ), __FILE__, __LINE__, __METHOD__, 10);
		$this->assertEquals( $next_date, strtotime( '06-Aug-2012 12:00PM PDT' ) );

		$next_date = $rhf->getNextDate( strtotime( '15-Aug-2010' ) );
		//Debug::text('Next Date: '. TTDate::getDate('DATE+TIME', $next_date ), __FILE__, __LINE__, __METHOD__, 10);
		$this->assertEquals( $next_date, strtotime( '01-Aug-2011 12:00PM PDT' ) );
	}

	/**
	 * @group RecurringHoliday_testRecurringHolidayDatesB
	 */
	function testRecurringHolidayDatesB() {
		//First Monday in August
		$rhf = TTNew( 'RecurringHolidayFactory' ); /** @var RecurringHolidayFactory $rhf */
		$rhf->setCompany( $this->company_id );
		$rhf->setName( 'BC - Family' );
		$rhf->setType( 20 );
		$rhf->setWeekInterval( 2 );
		$rhf->setDayOfWeek( 1 );
		$rhf->setMonth( 2 );
		$rhf->setAlwaysOnWeekDay( 3 ); //Closest

		$next_date = $rhf->getNextDate( strtotime( '01-Mar-2015' ) );
		//Debug::text('Next Date: '. TTDate::getDate('DATE+TIME', $next_date ), __FILE__, __LINE__, __METHOD__, 10);
		$this->assertEquals( $next_date, strtotime( '08-Feb-2016 12:00PM PST' ) );

		$next_date = $rhf->getNextDate( strtotime( '01-Mar-2014' ) );
		Debug::text( 'Next Date: ' . TTDate::getDate( 'DATE+TIME', $next_date ), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( $next_date, strtotime( '09-Feb-2015 12:00PM PST' ) );

		$next_date = $rhf->getNextDate( strtotime( '01-Mar-2013' ) );
		//Debug::text('Next Date: '. TTDate::getDate('DATE+TIME', $next_date ), __FILE__, __LINE__, __METHOD__, 10);
		$this->assertEquals( $next_date, strtotime( '10-Feb-2014 12:00PM PST' ) );

		$next_date = $rhf->getNextDate( strtotime( '01-Mar-2012' ) );
		//Debug::text('Next Date: '. TTDate::getDate('DATE+TIME', $next_date ), __FILE__, __LINE__, __METHOD__, 10);
		$this->assertEquals( $next_date, strtotime( '11-Feb-2013 12:00PM PST' ) );

		$next_date = $rhf->getNextDate( strtotime( '01-Mar-2011' ) );
		//Debug::text('Next Date: '. TTDate::getDate('DATE+TIME', $next_date ), __FILE__, __LINE__, __METHOD__, 10);
		$this->assertEquals( $next_date, strtotime( '13-Feb-2012 12:00PM PST' ) );

		$next_date = $rhf->getNextDate( strtotime( '01-Mar-2010' ) );
		//Debug::text('Next Date: '. TTDate::getDate('DATE+TIME', $next_date ), __FILE__, __LINE__, __METHOD__, 10);
		$this->assertEquals( $next_date, strtotime( '14-Feb-2011 12:00PM PST' ) );
	}

	/**
	 * @group RecurringHoliday_testRecurringHolidayDatesC
	 */
	function testRecurringHolidayDatesC() {
		//First Monday in August
		$rhf = TTNew( 'RecurringHolidayFactory' ); /** @var RecurringHolidayFactory $rhf */
		$rhf->setCompany( $this->company_id );
		$rhf->setName( 'US - Thanksgiving' );
		$rhf->setType( 20 );
		$rhf->setWeekInterval( 4 );
		$rhf->setDayOfWeek( 4 );
		$rhf->setMonth( 11 );
		$rhf->setAlwaysOnWeekDay( 0 );

		$next_date = $rhf->getNextDate( strtotime( '28-Nov-2013' ) );
		Debug::text( 'Next Date: ' . TTDate::getDate( 'DATE+TIME', $next_date ), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( $next_date, strtotime( '28-Nov-2013 12:00PM PST' ) );

		$start_date = strtotime( '29-Nov-2013 12:00PM' );
		$end_date = strtotime( '27-Nov-2014 12:00PM' );
		$n = 0;
		//for( $i = $start_date; $i < $end_date; $i += 86400 ) {
		foreach ( TTDate::getDatePeriod( $start_date, $end_date, 'P1D', false ) as $i ) {
			$next_date = $rhf->getNextDate( $i );
			Debug::text( 'N: ' . $n . ' Next Date: ' . TTDate::getDate( 'DATE+TIME', $next_date ), __FILE__, __LINE__, __METHOD__, 10 );
			$this->assertEquals( $next_date, strtotime( '27-Nov-2014 12:00PM PST' ) );
			$n++;
		}

		$start_date = strtotime( '28-Nov-2014 12:00PM' );
		$end_date = strtotime( '26-Nov-2015 12:00PM' );
		$n = 0;
		//for( $i = $start_date; $i < $end_date; $i += 86400 ) {
		foreach ( TTDate::getDatePeriod( $start_date, $end_date, 'P1D', false ) as $i ) {
			$next_date = $rhf->getNextDate( $i );
			Debug::text( 'N: ' . $n . ' Next Date: ' . TTDate::getDate( 'DATE+TIME', $next_date ), __FILE__, __LINE__, __METHOD__, 10 );
			$this->assertEquals( $next_date, strtotime( '26-Nov-2015 12:00PM PST' ) );
			$n++;
		}

		$next_date = $rhf->getNextDate( strtotime( '27-Nov-2015' ) );
		Debug::text( 'Next Date: ' . TTDate::getDate( 'DATE+TIME', $next_date ), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( $next_date, strtotime( '24-Nov-2016 12:00PM PST' ) );

		$next_date = $rhf->getNextDate( strtotime( '28-Nov-2015' ) );
		Debug::text( 'Next Date: ' . TTDate::getDate( 'DATE+TIME', $next_date ), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( $next_date, strtotime( '24-Nov-2016 12:00PM PST' ) );

		$next_date = $rhf->getNextDate( strtotime( '29-Nov-2015' ) );
		Debug::text( 'Next Date: ' . TTDate::getDate( 'DATE+TIME', $next_date ), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( $next_date, strtotime( '24-Nov-2016 12:00PM PST' ) );

		$next_date = $rhf->getNextDate( strtotime( '30-Nov-2015' ) );
		Debug::text( 'Next Date: ' . TTDate::getDate( 'DATE+TIME', $next_date ), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( $next_date, strtotime( '24-Nov-2016 12:00PM PST' ) );

		$next_date = $rhf->getNextDate( strtotime( '01-Dec-2015' ) );
		Debug::text( 'Next Date: ' . TTDate::getDate( 'DATE+TIME', $next_date ), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( $next_date, strtotime( '24-Nov-2016 12:00PM PST' ) );
	}
}

?>