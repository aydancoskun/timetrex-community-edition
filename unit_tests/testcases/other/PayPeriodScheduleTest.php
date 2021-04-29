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

class PayPeriodScheduleTest extends PHPUnit\Framework\TestCase {
	public $company_id = null;

	public function setUp(): void {
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setTimeZone( 'America/Vancouver', true );
		TTDate::setTimeUnitFormat( 10 ); //HH:MM

		$dd = new DemoData();
		$dd->setEnableQuickPunch( false ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_' . uniqid( null, true ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		Debug::text( 'Company ID: ' . $this->company_id, __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertGreaterThan( 0, $this->company_id );

		//$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	function deleteAllSchedules() {
		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getAll();
		foreach ( $ppslf as $pay_period_schedule_obj ) {
			$pay_period_schedule_obj->Delete();
		}

		return true;
	}

	function createPayPeriodSchedule( $type, $start_dow, $transaction_dow, $primary_dom, $secondary_dom, $primary_transaction_dom, $secondary_transaction_dom, $transaction_bd, $day_start_time = '00:00' ) {
		$ppsf = new PayPeriodScheduleFactory();
		$ppsf->setCompany( $this->company_id );

		$ppsf->setName( 'test_' . rand( 1000, 99999 ) );
		$ppsf->setDescription( 'test' );
		/*
											20 	=> 'Bi-Weekly',
											30  => 'Semi-Monthly',
											40	=> 'Monthly + Advance'
		*/
		$ppsf->setType( $type );

		$day_start_time = TTDate::parseTimeUnit( $day_start_time );
		Debug::text( 'parsed Day Start Time: ' . $day_start_time, __FILE__, __LINE__, __METHOD__, 10 );
		$ppsf->setDayStartTime( $day_start_time );

		if ( $type == 10 || $type == 20 || $type == 100 || $type == 200 ) {
			$ppsf->setStartDayOfWeek( $start_dow );
			$ppsf->setTransactionDate( $transaction_dow );
		} else if ( $type == 30 ) {
			$ppsf->setPrimaryDayOfMonth( $primary_dom );
			$ppsf->setSecondaryDayOfMonth( $secondary_dom );
			$ppsf->setPrimaryTransactionDayOfMonth( $primary_transaction_dom );
			$ppsf->setSecondaryTransactionDayOfMonth( $secondary_transaction_dom );
		} else if ( $type == 50 ) {
			$ppsf->setPrimaryDayOfMonth( $primary_dom );
			$ppsf->setPrimaryTransactionDayOfMonth( $primary_transaction_dom );
		}

		$ppsf->setTransactionDateBusinessDay( (bool)$transaction_bd );
		$ppsf->setTimeZone( 'America/Vancouver' );
		$ppsf->setEnableInitialPayPeriods( false );

		if ( $ppsf->isValid() ) {
			$pp_schedule_id = $ppsf->Save();

			$ppslf = new PayPeriodScheduleListFactory();
			$ret_obj = $ppslf->getById( $pp_schedule_id )->getCurrent();


			return $ret_obj;
		}

		return false;
	}

	/**
	 * @group PayPeriodSchedule_testHireAdjustedPayPeriodNumberA
	 */
	//Test adjusted pay period numbers based on the employees hire date. So if they are hired on pay period 40 of the year,
	//it is now 1/13 for that employee, but restarts to 1/52 for the following year.
	function testHireAdjustedPayPeriodNumberA() {
		//	Anchor: 01-Nov-04
		//	Primary: 08-Nov-04
		//	Primary Trans: 12-Nov-04
		//	Secondary: 15-Nov-04
		//	Secondary Trans: 19-Nov-04

		$ret_obj = $this->createPayPeriodSchedule( 100, //Weekly (53)
												   1, //Start DOW - Monday
												   5, //Transaction DOW - Friday
												   null, //Primary DOM
												   null, //Secondary DOM
												   null, //Primary Trans DOM
												   null, //Secondary Trans DOM
												   true //Transaction Business Day
		);

		Debug::text( 'Pay Period Schedule ID: ' . $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setTimeFormat( 'g:i:s A T' );

		$hire_date = strtotime( '23-Sep-04' );

		$ret_obj->getNextPayPeriod( strtotime( '23-Sep-04' ) );
		$next_end_date = $ret_obj->getNextEndDate();

		$hired_pay_period_number = $ret_obj->getHiredPayPeriodNumberAdjustment( $ret_obj->getNextTransactionDate(), $hire_date );
		$hire_adjusted_annual_pay_periods = $ret_obj->getHireAdjustedAnnualPayPeriods( $ret_obj->getNextTransactionDate(), $hire_date );
		$this->assertEquals( 40, $hired_pay_period_number, 'Hired Pay Period Number' );
		//$this->assertEquals( $hire_adjusted_annual_pay_periods, 13, 'Hire Adjusted Annual Pay Periods');
		$this->assertEquals( 53, $hire_adjusted_annual_pay_periods, 'Hire Adjusted Annual Pay Periods' ); //This should always be the total number of pay periods in the year. Only the hire adjusted current pay period gets modified.
		//$this->assertEquals( ( $hired_pay_period_number + $hire_adjusted_annual_pay_periods), $ret_obj->getAnnualPayPeriods(), 'Compare Hire Adjusted to Annual PPs');


		//var_dump($ret_obj->getNextStartDate());
		$this->assertEquals( '27-Sep-04 12:00:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '03-Oct-04 11:59:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '08-Oct-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 1, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '04-Oct-04 12:00:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '10-Oct-04 11:59:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '15-Oct-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 2, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '11-Oct-04 12:00:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '17-Oct-04 11:59:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '22-Oct-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 3, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '18-Oct-04 12:00:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '24-Oct-04 11:59:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '29-Oct-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 4, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '25-Oct-04 12:00:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '31-Oct-04 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '05-Nov-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 5, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Nov-04 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '07-Nov-04 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '12-Nov-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 6, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '08-Nov-04 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '14-Nov-04 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '19-Nov-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 7, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-Nov-04 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '21-Nov-04 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '26-Nov-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 8, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Nov-04 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '28-Nov-04 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '03-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 9, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '29-Nov-04 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '05-Dec-04 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '10-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 10, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '06-Dec-04 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '12-Dec-04 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '2- End Date' );
		$this->assertEquals( '17-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );
		$this->assertEquals( 11, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '2- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '13-Dec-04 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '3- Start Date' );
		$this->assertEquals( '19-Dec-04 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '3- End Date' );
		$this->assertEquals( '24-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '3- Transaction Date' );
		$this->assertEquals( 12, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '3- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '20-Dec-04 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '4- Start Date' );
		$this->assertEquals( '26-Dec-04 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '4- End Date' );
		$this->assertEquals( '31-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '4- Transaction Date' );
		$this->assertEquals( 13, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '4- Pay Period Number' );


		$ret_obj->setType( 10 ); //Switch back to Weekly (52)
		$ret_obj->setAnnualPayPeriods( $ret_obj->calcAnnualPayPeriods() );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '27-Dec-04 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '5- Start Date' );
		$this->assertEquals( '02-Jan-05 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '5- End Date' );
		$this->assertEquals( '07-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '5- Transaction Date' );
		$this->assertEquals( 1, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '5- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '03-Jan-05 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '6- Start Date' );
		$this->assertEquals( '09-Jan-05 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '6- End Date' );
		$this->assertEquals( '14-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '6- Transaction Date' );
		$this->assertEquals( 2, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '6- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '10-Jan-05 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '16-Jan-05 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '21-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );
		$this->assertEquals( 3, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '7- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '17-Jan-05 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '8- Start Date' );
		$this->assertEquals( '23-Jan-05 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '8- End Date' );
		$this->assertEquals( '28-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '8- Transaction Date' );
		$this->assertEquals( 4, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '8- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '24-Jan-05 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '9- Start Date' );
		$this->assertEquals( '30-Jan-05 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '9- End Date' );
		$this->assertEquals( '04-Feb-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '9- Transaction Date' );
		$this->assertEquals( 5, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '9- Pay Period Number' );

		TTDate::setTimeFormat( 'g:i A T' );
		unset( $next_end_date );
	}

	/**
	 * @group PayPeriodSchedule_testHireAdjustedPayPeriodNumberB
	 */
	function testHireAdjustedPayPeriodNumberB() {
		//	Anchor: 01-May-04
		//	Primary: 27-May-04 w/LDOM
		//	Primary Trans: 27-May-04 w/LDOM & BD
		//	Secondary: 27-Jun-04
		//	Secondary Trans: 27-Jun-04 w/LDOM BD
		$ret_obj = $this->createPayPeriodSchedule( 50,
												   null, //Start DOW
												   null, //Transaction DOW
												   1, //Primary DOM
												   null, //Secondary DOM
												   31, //Primary Trans DOM
												   null, //Secondary Trans DOM
												   true //Transaction Business Day
		);

		Debug::text( 'Pay Period Schedule ID: ' . $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

		$hire_date = strtotime( '01-Jul-05' );

		$ret_obj->getNextPayPeriod( strtotime( '01-Jul-05' ) );
		$next_end_date = $ret_obj->getNextEndDate();

		$hired_pay_period_number = $ret_obj->getHiredPayPeriodNumberAdjustment( $ret_obj->getNextTransactionDate(), $hire_date );
		$hire_adjusted_annual_pay_periods = $ret_obj->getHireAdjustedAnnualPayPeriods( $ret_obj->getNextTransactionDate(), $hire_date );
		$this->assertEquals( 6, $hired_pay_period_number, 'Hired Pay Period Number' );
		//$this->assertEquals( $hire_adjusted_annual_pay_periods, 6, 'Hire Adjusted Annual Pay Periods');
		$this->assertEquals( 12, $hire_adjusted_annual_pay_periods, 'Hire Adjusted Annual Pay Periods' ); //This should always be the total number of pay periods in the year. Only the hire adjusted current pay period gets modified.
		//$this->assertEquals( ( $hired_pay_period_number + $hire_adjusted_annual_pay_periods), $ret_obj->getAnnualPayPeriods(), 'Compare Hire Adjusted to Annual PPs');

		$this->assertEquals( '01-Jul-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '31-Jul-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '1- End Date' );
		$this->assertEquals( '31-Jul-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 1, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Aug-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '31-Aug-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '2- End Date' );
		$this->assertEquals( '31-Aug-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );
		$this->assertEquals( 2, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '2- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Sep-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '3- Start Date' );
		$this->assertEquals( '30-Sep-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '3- End Date' );
		$this->assertEquals( '30-Sep-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '3- Transaction Date' );
		$this->assertEquals( 3, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '3- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Oct-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '4- Start Date' );
		$this->assertEquals( '31-Oct-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '4- End Date' );
		$this->assertEquals( '31-Oct-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '4- Transaction Date' );
		$this->assertEquals( 4, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '4- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Nov-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '5- Start Date' );
		$this->assertEquals( '30-Nov-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '5- End Date' );
		$this->assertEquals( '30-Nov-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '5- Transaction Date' );
		$this->assertEquals( 5, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '5- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Dec-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '6- Start Date' );
		$this->assertEquals( '31-Dec-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '6- End Date' );
		$this->assertEquals( '31-Dec-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '6- Transaction Date' );
		$this->assertEquals( 6, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '6- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Jan-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '31-Jan-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '31-Jan-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );
		$this->assertEquals( 1, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '7- Pay Period Number' ); //Restarts here for new year

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Feb-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '8- Start Date' );
		$this->assertEquals( '28-Feb-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '8- End Date' );
		$this->assertEquals( '28-Feb-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '8- Transaction Date' );
		$this->assertEquals( 2, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '8- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Mar-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '9- Start Date' );
		$this->assertEquals( '31-Mar-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '9- End Date' );
		$this->assertEquals( '31-Mar-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '9- Transaction Date' );
		$this->assertEquals( 3, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '9- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Apr-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '10- Start Date' );
		$this->assertEquals( '30-Apr-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '10- End Date' );
		$this->assertEquals( '30-Apr-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '10- Transaction Date' );
		$this->assertEquals( 4, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '10- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-May-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '11- Start Date' );
		$this->assertEquals( '31-May-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '11- End Date' );
		$this->assertEquals( '31-May-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '11- Transaction Date' );
		$this->assertEquals( 5, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '11- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Jun-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '12- Start Date' );
		$this->assertEquals( '30-Jun-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '12- End Date' );
		$this->assertEquals( '30-Jun-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '12- Transaction Date' );
		$this->assertEquals( 6, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '12- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Jul-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '13- Start Date' );
		$this->assertEquals( '31-Jul-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '13- End Date' );
		$this->assertEquals( '31-Jul-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '13- Transaction Date' );
		$this->assertEquals( 7, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '13- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Aug-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '14- Start Date' );
		$this->assertEquals( '31-Aug-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '14- End Date' );
		$this->assertEquals( '31-Aug-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '14- Transaction Date' );
		$this->assertEquals( 8, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '14- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Sep-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '15- Start Date' );
		$this->assertEquals( '30-Sep-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '15- End Date' );
		$this->assertEquals( '30-Sep-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '15- Transaction Date' );
		$this->assertEquals( 9, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '15- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Oct-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '16- Start Date' );
		$this->assertEquals( '31-Oct-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '16- End Date' );
		$this->assertEquals( '31-Oct-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '16- Transaction Date' );
		$this->assertEquals( 10, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '16- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Nov-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '17- Start Date' );
		$this->assertEquals( '30-Nov-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '17- End Date' );
		$this->assertEquals( '30-Nov-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '17- Transaction Date' );
		$this->assertEquals( 11, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '17- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Dec-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '18- Start Date' );
		$this->assertEquals( '31-Dec-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '18- End Date' );
		$this->assertEquals( '31-Dec-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '18- Transaction Date' );
		$this->assertEquals( 12, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '18- Pay Period Number' );

		unset( $next_end_date );
	}

	/**
	 * @group PayPeriodSchedule_testHireAdjustedPayPeriodNumberC
	 */
	function testHireAdjustedPayPeriodNumberC() {
		//	Anchor: 01-May-04
		//	Primary: 27-May-04 w/LDOM
		//	Primary Trans: 27-May-04 w/LDOM & BD
		//	Secondary: 27-Jun-04
		//	Secondary Trans: 27-Jun-04 w/LDOM BD
		$ret_obj = $this->createPayPeriodSchedule( 50,
												   null, //Start DOW
												   null, //Transaction DOW
												   1, //Primary DOM
												   null, //Secondary DOM
												   31, //Primary Trans DOM
												   null, //Secondary Trans DOM
												   true //Transaction Business Day
		);

		Debug::text( 'Pay Period Schedule ID: ' . $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

		$hire_date = strtotime( '01-Jan-06' );

		$ret_obj->getNextPayPeriod( strtotime( '01-Jan-06' ) );
		$next_end_date = $ret_obj->getNextEndDate();

		$hired_pay_period_number = $ret_obj->getHiredPayPeriodNumberAdjustment( $ret_obj->getNextTransactionDate(), $hire_date );
		$hire_adjusted_annual_pay_periods = $ret_obj->getHireAdjustedAnnualPayPeriods( $ret_obj->getNextTransactionDate(), $hire_date );
		$this->assertEquals( 0, $hired_pay_period_number, 'Hired Pay Period Number' );
		$this->assertEquals( 12, $hire_adjusted_annual_pay_periods, 'Hire Adjusted Annual Pay Periods' );
		$this->assertEquals( ( $hired_pay_period_number + $hire_adjusted_annual_pay_periods ), $ret_obj->getAnnualPayPeriods(), 'Compare Hire Adjusted to Annual PPs' );

		$this->assertEquals( '01-Jan-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '31-Jan-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '1- End Date' );
		$this->assertEquals( '31-Jan-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 1, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Feb-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '28-Feb-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '2- End Date' );
		$this->assertEquals( '28-Feb-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );
		$this->assertEquals( 2, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '2- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Mar-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '3- Start Date' );
		$this->assertEquals( '31-Mar-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '3- End Date' );
		$this->assertEquals( '31-Mar-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '3- Transaction Date' );
		$this->assertEquals( 3, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '3- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Apr-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '4- Start Date' );
		$this->assertEquals( '30-Apr-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '4- End Date' );
		$this->assertEquals( '30-Apr-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '4- Transaction Date' );
		$this->assertEquals( 4, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '4- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-May-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '5- Start Date' );
		$this->assertEquals( '31-May-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '5- End Date' );
		$this->assertEquals( '31-May-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '5- Transaction Date' );
		$this->assertEquals( 5, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '5- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Jun-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '6- Start Date' );
		$this->assertEquals( '30-Jun-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '6- End Date' );
		$this->assertEquals( '30-Jun-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '6- Transaction Date' );
		$this->assertEquals( 6, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '6- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Jul-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '31-Jul-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '31-Jul-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );
		$this->assertEquals( 7, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '7- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Aug-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '8- Start Date' );
		$this->assertEquals( '31-Aug-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '8- End Date' );
		$this->assertEquals( '31-Aug-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '8- Transaction Date' );
		$this->assertEquals( 8, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '8- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Sep-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '9- Start Date' );
		$this->assertEquals( '30-Sep-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '9- End Date' );
		$this->assertEquals( '30-Sep-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '9- Transaction Date' );
		$this->assertEquals( 9, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '9- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Oct-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '10- Start Date' );
		$this->assertEquals( '31-Oct-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '10- End Date' );
		$this->assertEquals( '31-Oct-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '10- Transaction Date' );
		$this->assertEquals( 10, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '10- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Nov-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '11- Start Date' );
		$this->assertEquals( '30-Nov-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '11- End Date' );
		$this->assertEquals( '30-Nov-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '11- Transaction Date' );
		$this->assertEquals( 11, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '11- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Dec-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '12- Start Date' );
		$this->assertEquals( '31-Dec-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '12- End Date' );
		$this->assertEquals( '31-Dec-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '12- Transaction Date' );
		$this->assertEquals( 12, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '12- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Jan-07', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '31-Jan-07', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '1- End Date' );
		$this->assertEquals( '31-Jan-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 1, $ret_obj->getHireAdjustedCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate(), $hire_date ), '1- Pay Period Number' ); //Restarts here for new year

		unset( $next_end_date );
	}

	/**
	 * @group PayPeriodSchedule_testWeekly
	 */
	//Weekly
	function testWeekly() {

		//	Anchor: 01-Nov-04
		//	Primary: 08-Nov-04
		//	Primary Trans: 12-Nov-04
		//	Secondary: 15-Nov-04
		//	Secondary Trans: 19-Nov-04

		$ret_obj = $this->createPayPeriodSchedule( 100, //Weekly (53)
												   1, //Start DOW - Monday
												   5, //Transaction DOW - Friday
												   null, //Primary DOM
												   null, //Secondary DOM
												   null, //Primary Trans DOM
												   null, //Secondary Trans DOM
												   true //Transaction Business Day
		);

		Debug::text( 'Pay Period Schedule ID: ' . $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setTimeFormat( 'g:i:s A T' );

		$ret_obj->getNextPayPeriod( strtotime( '23-Sep-04' ) );
		$next_end_date = $ret_obj->getNextEndDate();

		//var_dump($ret_obj->getNextStartDate());
		$this->assertEquals( '27-Sep-04 12:00:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '03-Oct-04 11:59:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '08-Oct-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 41, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '04-Oct-04 12:00:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '10-Oct-04 11:59:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '15-Oct-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 42, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '11-Oct-04 12:00:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '17-Oct-04 11:59:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '22-Oct-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 43, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '18-Oct-04 12:00:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '24-Oct-04 11:59:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '29-Oct-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 44, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '25-Oct-04 12:00:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '31-Oct-04 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '05-Nov-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 45, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Nov-04 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '07-Nov-04 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '12-Nov-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 46, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '08-Nov-04 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '14-Nov-04 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '19-Nov-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 47, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-Nov-04 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '21-Nov-04 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '26-Nov-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 48, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Nov-04 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '28-Nov-04 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '03-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 49, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '29-Nov-04 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '05-Dec-04 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '10-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 50, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '06-Dec-04 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '12-Dec-04 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '2- End Date' );
		$this->assertEquals( '17-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );
		$this->assertEquals( 51, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '2- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '13-Dec-04 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '3- Start Date' );
		$this->assertEquals( '19-Dec-04 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '3- End Date' );
		$this->assertEquals( '24-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '3- Transaction Date' );
		$this->assertEquals( 52, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '3- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '20-Dec-04 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '4- Start Date' );
		$this->assertEquals( '26-Dec-04 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '4- End Date' );
		$this->assertEquals( '31-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '4- Transaction Date' );
		$this->assertEquals( 53, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '4- Pay Period Number' );


		$ret_obj->setType( 10 ); //Switch back to Weekly (52)
		$ret_obj->setAnnualPayPeriods( $ret_obj->calcAnnualPayPeriods() );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '27-Dec-04 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '5- Start Date' );
		$this->assertEquals( '02-Jan-05 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '5- End Date' );
		$this->assertEquals( '07-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '5- Transaction Date' );
		$this->assertEquals( 1, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '5- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '03-Jan-05 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '6- Start Date' );
		$this->assertEquals( '09-Jan-05 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '6- End Date' );
		$this->assertEquals( '14-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '6- Transaction Date' );
		$this->assertEquals( 2, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '6- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '10-Jan-05 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '16-Jan-05 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '21-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );
		$this->assertEquals( 3, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '7- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '17-Jan-05 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '8- Start Date' );
		$this->assertEquals( '23-Jan-05 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '8- End Date' );
		$this->assertEquals( '28-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '8- Transaction Date' );
		$this->assertEquals( 4, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '8- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '24-Jan-05 12:00:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '9- Start Date' );
		$this->assertEquals( '30-Jan-05 11:59:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '9- End Date' );
		$this->assertEquals( '04-Feb-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '9- Transaction Date' );
		$this->assertEquals( 5, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '9- Pay Period Number' );

		TTDate::setTimeFormat( 'g:i A T' );
		unset( $next_end_date );
	}
	/*
		//Disabled while PP start times are disabled.
		function testWeeklyB() {
			TTDate::setTimeFormat('g:i A T');

			//	Anchor: 01-Nov-04
			//	Primary: 08-Nov-04
			//	Primary Trans: 12-Nov-04
			//	Secondary: 15-Nov-04
			//	Secondary Trans: 19-Nov-04
			$ret_obj = $this->createPayPeriodSchedule(			10,
																1, //Start DOW - Monday
																5, //Transaction DOW - Friday
																NULL, //Primary DOM
																NULL, //Secondary DOM
																NULL, //Primary Trans DOM
																NULL, //Secondary Trans DOM
																TRUE, //Transaction Business Day
																'18:00'
																);

			Debug::text('Pay Period Schedule ID: '. $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);

			$ret_obj->getNextPayPeriod( strtotime('29-Nov-04 00:00') );
			$next_end_date = $ret_obj->getNextEndDate();

			$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 			'29-Nov-04 6:00 PM PST', '1- Start Date');
			$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 						'06-Dec-04 5:59 PM PST', '1- End Date');
			$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),			'10-Dec-04', '1- Transaction Date');
			//$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	49, '1- Pay Period Number');

			$ret_obj->getNextPayPeriod( $next_end_date );
			$next_end_date = $ret_obj->getNextEndDate();
			$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 			'06-Dec-04 6:00 PM PST', '2- Start Date');
			$this->assertEquals( TTDate::getDate('DATE+TIME', $next_end_date ), 						'13-Dec-04 5:59 PM PST', '2- End Date');
			$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),			'17-Dec-04', '2- Transaction Date');
			$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	50, '2- Pay Period Number');

			$ret_obj->getNextPayPeriod( $next_end_date );
			$next_end_date = $ret_obj->getNextEndDate();
			$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 			'13-Dec-04 6:00 PM PST', '3- Start Date');
			$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 			'20-Dec-04 5:59 PM PST', '3- End Date');
			$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),			'24-Dec-04', '3- Transaction Date');
			$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	51, '3- Pay Period Number');

			$ret_obj->getNextPayPeriod( $next_end_date );
			$next_end_date = $ret_obj->getNextEndDate();
			$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 			'20-Dec-04 6:00 PM PST', '4- Start Date');
			$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 			'27-Dec-04 5:59 PM PST', '4- End Date');
			$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),			'31-Dec-04', '4- Transaction Date');
			$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	52, '4- Pay Period Number');

			$ret_obj->getNextPayPeriod( $next_end_date );
			$next_end_date = $ret_obj->getNextEndDate();
			$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 			'27-Dec-04 6:00 PM PST', '5- Start Date');
			$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 			'03-Jan-05 5:59 PM PST', '5- End Date');
			$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),			'07-Jan-05', '5- Transaction Date');
			$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	1, '5- Pay Period Number');

			$ret_obj->getNextPayPeriod( $next_end_date );
			$next_end_date = $ret_obj->getNextEndDate();
			$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextStartDate() ), 			'03-Jan-05 6:00 PM PST', '6- Start Date');
			$this->assertEquals( TTDate::getDate('DATE+TIME', $ret_obj->getNextEndDate() ), 			'10-Jan-05 5:59 PM PST', '6- End Date');
			$this->assertEquals( TTDate::getDate('DATE', $ret_obj->getNextTransactionDate()),			'14-Jan-05', '6- Transaction Date');
			$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	2, '6- Pay Period Number');
		}
	*/

	/**
	 * @group PayPeriodSchedule_testWeeklyB
	 */
	//Test while PP start time is ignored. See above once its added again.
	function testWeeklyB() {
		TTDate::setTimeFormat( 'g:i A T' );

		//	Anchor: 01-Nov-04
		//	Primary: 08-Nov-04
		//	Primary Trans: 12-Nov-04
		//	Secondary: 15-Nov-04
		//	Secondary Trans: 19-Nov-04
		$ret_obj = $this->createPayPeriodSchedule( 10,
												   1, //Start DOW - Monday
												   5, //Transaction DOW - Friday
												   null, //Primary DOM
												   null, //Secondary DOM
												   null, //Primary Trans DOM
												   null, //Secondary Trans DOM
												   true, //Transaction Business Day
												   '18:00'
		);

		Debug::text( 'Pay Period Schedule ID: ' . $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

		$ret_obj->getNextPayPeriod( strtotime( '29-Nov-04 00:00' ) );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '29-Nov-04 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '05-Dec-04 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '10-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		//$this->assertEquals( $ret_obj->getCurrentPayPeriodNumber($ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ),	49, '1- Pay Period Number');

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '06-Dec-04 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '12-Dec-04 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '2- End Date' );
		$this->assertEquals( '17-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );
		$this->assertEquals( 50, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '2- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '13-Dec-04 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '3- Start Date' );
		$this->assertEquals( '19-Dec-04 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '3- End Date' );
		$this->assertEquals( '24-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '3- Transaction Date' );
		$this->assertEquals( 51, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '3- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '20-Dec-04 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '4- Start Date' );
		$this->assertEquals( '26-Dec-04 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '4- End Date' );
		$this->assertEquals( '31-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '4- Transaction Date' );
		$this->assertEquals( 52, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '4- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '27-Dec-04 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '5- Start Date' );
		$this->assertEquals( '02-Jan-05 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '5- End Date' );
		$this->assertEquals( '07-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '5- Transaction Date' );
		$this->assertEquals( 1, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '5- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '03-Jan-05 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '6- Start Date' );
		$this->assertEquals( '09-Jan-05 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '6- End Date' );
		$this->assertEquals( '14-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '6- Transaction Date' );
		$this->assertEquals( 2, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '6- Pay Period Number' );

		unset( $next_end_date );
	}

	/**
	 * @group PayPeriodSchedule_testBiWeekly
	 */
	//Bi-Weekly
	function testBiWeekly() {

		//	Anchor: 01-Nov-04
		//	Primary: 15-Nov-04
		//	Primary Trans: 22-Nov-04
		//	Secondary: 29-Nov-04
		//	Secondary Trans: 06-Dec-04
		$ret_obj = $this->createPayPeriodSchedule( 20,
												   1, //Start DOW - Monday
												   8, //Transaction DOW - Monday
												   null, //Primary DOM
												   null, //Secondary DOM
												   null, //Primary Trans DOM
												   null, //Secondary Trans DOM
												   true //Transaction Business Day
		);

		Debug::text( 'Pay Period Schedule ID: ' . $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

		$ret_obj->getNextPayPeriod( strtotime( '27-Nov-04' ) );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '29-Nov-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '12-Dec-04', TTDate::getDate( 'DATE', $next_end_date ), '1- End Date' );
		$this->assertEquals( '20-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 26, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '13-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '26-Dec-04', TTDate::getDate( 'DATE', $next_end_date ), '2- End Date' );
		$this->assertEquals( '03-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );
		$this->assertEquals( 1, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '2- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '27-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '3- Start Date' );
		$this->assertEquals( '09-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '3- End Date' );
		$this->assertEquals( '17-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '3- Transaction Date' );
		$this->assertEquals( 2, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '3- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '10-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '4- Start Date' );
		$this->assertEquals( '23-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '4- End Date' );
		$this->assertEquals( '31-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '4- Transaction Date' );
		$this->assertEquals( 3, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '4- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '24-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '5- Start Date' );
		$this->assertEquals( '06-Feb-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '5- End Date' );
		$this->assertEquals( '14-Feb-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '5- Transaction Date' );
		$this->assertEquals( 4, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '5- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '07-Feb-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '6- Start Date' );
		$this->assertEquals( '20-Feb-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '6- End Date' );
		$this->assertEquals( '28-Feb-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '6- Transaction Date' );
		$this->assertEquals( 5, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '6- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '21-Feb-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '06-Mar-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '14-Mar-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );
		$this->assertEquals( 6, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '7- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '07-Mar-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '8- Start Date' );
		$this->assertEquals( '20-Mar-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '8- End Date' );
		$this->assertEquals( '28-Mar-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '8- Transaction Date' );
		$this->assertEquals( 7, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '8- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '21-Mar-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '9- Start Date' );
		$this->assertEquals( '03-Apr-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '9- End Date' );
		$this->assertEquals( '11-Apr-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '9- Transaction Date' );
		$this->assertEquals( 8, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '9- Pay Period Number' );

		unset( $next_end_date );
	}

	/**
	 * @group PayPeriodSchedule_testBiWeeklyB
	 */
	function testBiWeeklyB() {

		//	Anchor: 01-Nov-04
		//	Primary: 15-Nov-04
		//	Primary Trans: 22-Nov-04
		//	Secondary: 29-Nov-04
		//	Secondary Trans: 06-Dec-04
		$ret_obj = $this->createPayPeriodSchedule( 20,
												   1, //Start DOW - Monday
												   0, //Transaction DOW - Same Day
												   null, //Primary DOM
												   null, //Secondary DOM
												   null, //Primary Trans DOM
												   null, //Secondary Trans DOM
												   true //Transaction Business Day
		);

		Debug::text( 'Pay Period Schedule ID: ' . $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

		$ret_obj->getNextPayPeriod( strtotime( '27-Nov-04' ) );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '29-Nov-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '12-Dec-04', TTDate::getDate( 'DATE', $next_end_date ), '1- End Date' );
		$this->assertEquals( '12-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 25, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '13-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '26-Dec-04', TTDate::getDate( 'DATE', $next_end_date ), '2- End Date' );
		$this->assertEquals( '26-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );
		$this->assertEquals( 26, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '2- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '27-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '3- Start Date' );
		$this->assertEquals( '09-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '3- End Date' );
		$this->assertEquals( '09-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '3- Transaction Date' );
		$this->assertEquals( 1, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '3- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '10-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '4- Start Date' );
		$this->assertEquals( '23-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '4- End Date' );
		$this->assertEquals( '23-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '4- Transaction Date' );
		$this->assertEquals( 2, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '4- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '24-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '5- Start Date' );
		$this->assertEquals( '06-Feb-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '5- End Date' );
		$this->assertEquals( '06-Feb-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '5- Transaction Date' );
		$this->assertEquals( 3, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '5- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '07-Feb-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '6- Start Date' );
		$this->assertEquals( '20-Feb-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '6- End Date' );
		$this->assertEquals( '20-Feb-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '6- Transaction Date' );
		$this->assertEquals( 4, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '6- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '21-Feb-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '06-Mar-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '06-Mar-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );
		$this->assertEquals( 5, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '7- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '07-Mar-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '8- Start Date' );
		$this->assertEquals( '20-Mar-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '8- End Date' );
		$this->assertEquals( '20-Mar-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '8- Transaction Date' );
		$this->assertEquals( 6, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '8- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '21-Mar-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '9- Start Date' );
		$this->assertEquals( '03-Apr-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '9- End Date' );
		$this->assertEquals( '03-Apr-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '9- Transaction Date' );
		$this->assertEquals( 7, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '9- Pay Period Number' );

		unset( $next_end_date );
	}

	/**
	 * @group PayPeriodSchedule_testBiWeeklyC
	 */
	//Test the DST changes in 2007, for the full year.
	function testBiWeeklyC() {

		//	Anchor: 01-Nov-04
		//	Primary: 15-Nov-04
		//	Primary Trans: 22-Nov-04
		//	Secondary: 29-Nov-04
		//	Secondary Trans: 06-Dec-04
		$ret_obj = $this->createPayPeriodSchedule( 20,
												   1, //Start DOW - Monday
												   0, //Transaction DOW - Same Day
												   null, //Primary DOM
												   null, //Secondary DOM
												   null, //Primary Trans DOM
												   null, //Secondary Trans DOM
												   true //Transaction Business Day
		);

		Debug::text( 'Pay Period Schedule ID: ' . $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setTimeFormat( 'g:i A T' );

		$ret_obj->getNextPayPeriod( strtotime( '03-Dec-06' ) );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '04-Dec-06 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '17-Dec-06 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '17-Dec-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 25, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '18-Dec-06 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '31-Dec-06 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '31-Dec-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 26, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '01-Jan-07 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '14-Jan-07 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '14-Jan-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 1, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '15-Jan-07 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '28-Jan-07 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '28-Jan-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 2, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '29-Jan-07 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '11-Feb-07 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '11-Feb-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 3, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '12-Feb-07 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '25-Feb-07 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '25-Feb-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 4, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '26-Feb-07 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '11-Mar-07 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '11-Mar-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 5, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '12-Mar-07 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '25-Mar-07 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '25-Mar-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 6, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '26-Mar-07 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '08-Apr-07 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '08-Apr-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 7, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '09-Apr-07 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '22-Apr-07 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '22-Apr-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 8, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '23-Apr-07 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '06-May-07 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '06-May-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 9, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '07-May-07 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '20-May-07 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '20-May-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 10, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '21-May-07 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '03-Jun-07 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '03-Jun-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 11, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '04-Jun-07 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '17-Jun-07 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '17-Jun-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 12, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '18-Jun-07 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '01-Jul-07 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '01-Jul-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 13, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '02-Jul-07 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '15-Jul-07 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '15-Jul-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 14, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '16-Jul-07 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '29-Jul-07 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '29-Jul-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 15, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '30-Jul-07 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '12-Aug-07 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '12-Aug-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 16, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '13-Aug-07 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '26-Aug-07 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '26-Aug-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 17, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '27-Aug-07 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '09-Sep-07 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '09-Sep-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 18, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '10-Sep-07 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '23-Sep-07 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '23-Sep-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 19, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '24-Sep-07 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '07-Oct-07 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '07-Oct-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 20, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '08-Oct-07 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '21-Oct-07 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '21-Oct-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 21, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '22-Oct-07 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '04-Nov-07 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '04-Nov-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 22, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '05-Nov-07 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '18-Nov-07 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '18-Nov-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 23, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '19-Nov-07 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '02-Dec-07 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '02-Dec-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 24, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '03-Dec-07 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '16-Dec-07 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '16-Dec-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 25, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '17-Dec-07 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '30-Dec-07 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '30-Dec-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 26, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();

		$this->assertEquals( '31-Dec-07 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '13-Jan-08 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '13-Jan-08', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 1, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );
	}

	/**
	 * @group PayPeriodSchedule_testBiWeeklyD
	 */
	//Test years that have 27 pay periods (ie: 2015) when pay period ends on a Sunday, pays on a Thursday
	function testBiWeeklyD() {
		$ret_obj = $this->createPayPeriodSchedule( 200, //BiWeekly (27)
												   1, //Start DOW - Monday (Ends on Sunday)
												   4, //Transaction DOW - Following Friday
												   null, //Primary DOM
												   null, //Secondary DOM
												   null, //Primary Trans DOM
												   null, //Secondary Trans DOM
												   false //Transaction Business Day
		);

		Debug::text( 'Pay Period Schedule ID: ' . $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setTimeFormat( 'g:i A T' );

		$ret_obj->getNextPayPeriod( strtotime( '15-Dec-14' ) );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-Dec-14 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '28-Dec-14 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '01-Jan-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 1, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '29-Dec-14 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '11-Jan-15 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '15-Jan-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 2, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '12-Jan-15 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '25-Jan-15 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '29-Jan-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 3, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '26-Jan-15 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '08-Feb-15 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '12-Feb-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 4, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '09-Feb-15 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '22-Feb-15 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '26-Feb-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 5, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '23-Feb-15 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '08-Mar-15 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '12-Mar-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 6, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '09-Mar-15 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '22-Mar-15 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '26-Mar-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 7, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '23-Mar-15 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '05-Apr-15 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '09-Apr-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 8, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '06-Apr-15 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '19-Apr-15 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '23-Apr-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 9, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '20-Apr-15 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '03-May-15 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '07-May-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 10, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '04-May-15 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '17-May-15 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '21-May-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 11, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '18-May-15 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '31-May-15 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '04-Jun-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 12, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Jun-15 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '14-Jun-15 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '18-Jun-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 13, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-Jun-15 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '28-Jun-15 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '02-Jul-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 14, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '29-Jun-15 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '12-Jul-15 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '16-Jul-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 15, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '13-Jul-15 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '26-Jul-15 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '30-Jul-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 16, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '27-Jul-15 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '09-Aug-15 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '13-Aug-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 17, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '10-Aug-15 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '23-Aug-15 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '27-Aug-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 18, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '24-Aug-15 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '06-Sep-15 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '10-Sep-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 19, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '07-Sep-15 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '20-Sep-15 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '24-Sep-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 20, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '21-Sep-15 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '04-Oct-15 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '08-Oct-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 21, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '05-Oct-15 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '18-Oct-15 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '22-Oct-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 22, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '19-Oct-15 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '01-Nov-15 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '05-Nov-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 23, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '02-Nov-15 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '15-Nov-15 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '19-Nov-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 24, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '16-Nov-15 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '29-Nov-15 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '03-Dec-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 25, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '30-Nov-15 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '13-Dec-15 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '17-Dec-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 26, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		//27th Pay Period
		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '14-Dec-15 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '27-Dec-15 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $next_end_date ), '1- End Date' );
		$this->assertEquals( '31-Dec-15', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 27, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );
	}

	/**
	 * @group PayPeriodSchedule_testSemiMonthly
	 */
	function testSemiMonthly() {
		//	Anchor: 01-Jan-04
		//	Primary: 15-Jan-04
		//	Primary Trans: 25-Jan-04 w/BD
		//	Secondary: 27-Jan-04 w/LDOM
		//	Secondary Trans: 10-Feb-04 w/BD

		$ret_obj = $this->createPayPeriodSchedule( 30,
												   null, //Start DOW
												   null, //Transaction DOW
												   1, //Primary DOM
												   15, //Secondary DOM
												   25, //Primary Trans DOM
												   10, //Secondary Trans DOM
												   true //Transaction Business Day
		);

		Debug::text( 'Pay Period Schedule ID: ' . $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

		$ret_obj->getNextPayPeriod( strtotime( '30-Jan-04' ) );
		$next_end_date = $ret_obj->getNextEndDate();

		Debug::text( 'zzStart: ' . TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( 'zzEnd: ' . TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '01-Feb-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '14-Feb-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '1- End Date' );
		$this->assertEquals( '25-Feb-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 4, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-Feb-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '29-Feb-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '2- End Date' );
		$this->assertEquals( '10-Mar-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );
		$this->assertEquals( 5, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Mar-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '3- Start Date' );
		$this->assertEquals( '14-Mar-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '3- End Date' );
		$this->assertEquals( '25-Mar-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '3- Transaction Date' );
		$this->assertEquals( 6, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-Mar-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '4- Start Date' );
		$this->assertEquals( '31-Mar-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '4- End Date' );
		$this->assertEquals( '09-Apr-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '4- Transaction Date' );
		$this->assertEquals( 7, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Apr-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '5- Start Date' );
		$this->assertEquals( '14-Apr-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '5- End Date' );
		$this->assertEquals( '23-Apr-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '5- Transaction Date' );
		$this->assertEquals( 8, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-Apr-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '6- Start Date' );
		$this->assertEquals( '30-Apr-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '6- End Date' );
		$this->assertEquals( '10-May-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '6- Transaction Date' );
		$this->assertEquals( 9, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-May-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '14-May-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '25-May-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );
		$this->assertEquals( 10, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-May-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '8- Start Date' );
		$this->assertEquals( '31-May-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '8- End Date' );
		$this->assertEquals( '10-Jun-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '8- Transaction Date' );
		$this->assertEquals( 11, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Jun-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '9- Start Date' );
		$this->assertEquals( '14-Jun-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '9- End Date' );
		$this->assertEquals( '25-Jun-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '9- Transaction Date' );
		$this->assertEquals( 12, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-Jun-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '10- Start Date' );
		$this->assertEquals( '30-Jun-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '10- End Date' );
		$this->assertEquals( '09-Jul-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '10- Transaction Date' );
		$this->assertEquals( 13, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Jul-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '11- Start Date' );
		$this->assertEquals( '14-Jul-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '11- End Date' );
		$this->assertEquals( '23-Jul-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '11- Transaction Date' );
		$this->assertEquals( 14, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-Jul-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '12- Start Date' );
		$this->assertEquals( '31-Jul-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '12- End Date' );
		$this->assertEquals( '10-Aug-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '12- Transaction Date' );
		$this->assertEquals( 15, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Aug-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '13- Start Date' );
		$this->assertEquals( '14-Aug-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '13- End Date' );
		$this->assertEquals( '25-Aug-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '13- Transaction Date' );
		$this->assertEquals( 16, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-Aug-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '14- Start Date' );
		$this->assertEquals( '31-Aug-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '14- End Date' );
		$this->assertEquals( '10-Sep-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '14- Transaction Date' );
		$this->assertEquals( 17, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Sep-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '15- Start Date' );
		$this->assertEquals( '14-Sep-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '15- End Date' );
		$this->assertEquals( '24-Sep-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '15- Transaction Date' );
		$this->assertEquals( 18, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-Sep-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '16- Start Date' );
		$this->assertEquals( '30-Sep-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '16- End Date' );
		$this->assertEquals( '08-Oct-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '16- Transaction Date' );
		$this->assertEquals( 19, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Oct-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '17- Start Date' );
		$this->assertEquals( '14-Oct-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '17- End Date' );
		$this->assertEquals( '25-Oct-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '17- Transaction Date' );
		$this->assertEquals( 20, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-Oct-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '18- Start Date' );
		$this->assertEquals( '31-Oct-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '18- End Date' );
		$this->assertEquals( '10-Nov-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '18- Transaction Date' );
		$this->assertEquals( 21, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Nov-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '19- Start Date' );
		$this->assertEquals( '14-Nov-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '19- End Date' );
		$this->assertEquals( '25-Nov-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '19- Transaction Date' );
		$this->assertEquals( 22, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-Nov-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '20- Start Date' );
		$this->assertEquals( '30-Nov-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '20- End Date' );
		$this->assertEquals( '10-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '20- Transaction Date' );
		$this->assertEquals( 23, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '21- Start Date' );
		$this->assertEquals( '14-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '21- End Date' );
		$this->assertEquals( '24-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '21- Transaction Date' );
		$this->assertEquals( 24, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '22- Start Date' );
		$this->assertEquals( '31-Dec-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '22- End Date' );
		$this->assertEquals( '10-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '22- Transaction Date' );
		$this->assertEquals( 1, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '23- Start Date' );
		$this->assertEquals( '14-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '23- End Date' );
		$this->assertEquals( '25-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '23- Transaction Date' );
		$this->assertEquals( 2, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '24- Start Date' );
		$this->assertEquals( '31-Jan-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '24- End Date' );
		$this->assertEquals( '10-Feb-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '24- Transaction Date' );
		$this->assertEquals( 3, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		unset( $next_end_date );
	}

	/**
	 * @group PayPeriodSchedule_testSemiMonthlyB
	 */
	function testSemiMonthlyB() {
		//	Anchor: 24-Apr-04
		//	Primary: 08-May-04
		//	Primary Trans: 15-May-04 w/BD
		//	Secondary: 22-May-04
		//	Secondary Trans: 27-May-04 w/LDOM & BD
		$ret_obj = $this->createPayPeriodSchedule( 30,
												   null, //Start DOW
												   null, //Transaction DOW
												   24, //Primary DOM
												   8, //Secondary DOM
												   15, //Primary Trans DOM
												   31, //Secondary Trans DOM
												   true //Transaction Business Day
		);

		Debug::text( 'Pay Period Schedule ID: ' . $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

		$ret_obj->getNextPayPeriod( strtotime( '01-Dec-03' ) );
		$next_end_date = $ret_obj->getNextEndDate();

		Debug::text( 'zzStart: ' . TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), __FILE__, __LINE__, __METHOD__, 10 );

		$this->assertEquals( '08-Dec-03', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '23-Dec-03', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '1- End Date' );
		$this->assertEquals( '31-Dec-03', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 24, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '24-Dec-03', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '07-Jan-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '1- End Date' );
		$this->assertEquals( '15-Jan-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 1, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '08-Jan-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '23-Jan-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '2- End Date' );
		$this->assertEquals( '30-Jan-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );
		$this->assertEquals( 2, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '24-Jan-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '07-Feb-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '2- End Date' );
		$this->assertEquals( '13-Feb-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );
		$this->assertEquals( 3, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '08-Feb-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '23-Feb-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '2- End Date' );
		$this->assertEquals( '27-Feb-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );
		$this->assertEquals( 4, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '24-Feb-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '07-Mar-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '2- End Date' );
		$this->assertEquals( '15-Mar-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );
		$this->assertEquals( 5, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '08-Mar-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '23-Mar-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '2- End Date' );
		$this->assertEquals( '31-Mar-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );
		$this->assertEquals( 6, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '24-Mar-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '07-Apr-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '2- End Date' );
		$this->assertEquals( '15-Apr-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );
		$this->assertEquals( 7, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '08-Apr-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '23-Apr-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '2- End Date' );
		$this->assertEquals( '30-Apr-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );
		$this->assertEquals( 8, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$ret_obj->getNextEndDate();
		$this->assertEquals( '24-Apr-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '07-May-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '2- End Date' );
		$this->assertEquals( '14-May-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );
		$this->assertEquals( 9, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );


		$ret_obj->getNextPayPeriod( strtotime( '20-Apr-04' ) );
		$next_end_date = $ret_obj->getNextEndDate();

		Debug::text( 'zzStart: ' . TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '24-Apr-04', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '07-May-04', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '1- End Date' );
		$this->assertEquals( '14-May-04', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );
		$this->assertEquals( 9, $ret_obj->getCurrentPayPeriodNumber( $ret_obj->getNextTransactionDate(), $ret_obj->getNextEndDate() ), '1- Pay Period Number' );

		unset( $next_end_date );
	}

	/**
	 * @group PayPeriodSchedule_testSemiMonthlyC
	 */
	function testSemiMonthlyC() {
		//	Anchor: 24-Apr-04
		//	Primary: 08-May-04
		//	Primary Trans: 15-May-04 w/BD
		//	Secondary: 22-May-04
		//	Secondary Trans: 27-May-04 w/LDOM & BD
		$ret_obj = $this->createPayPeriodSchedule( 30,
												   null, //Start DOW
												   null, //Transaction DOW
												   8, //Primary DOM
												   22, //Secondary DOM
												   31, //Primary Trans DOM
												   15, //Secondary Trans DOM
												   true //Transaction Business Day
		);


		Debug::text( 'Pay Period Schedule ID: ' . $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

		$ret_obj->getNextPayPeriod( strtotime( '20-Jun-05' ) );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Jun-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '07-Jul-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '1- End Date' );
		$this->assertEquals( '15-Jul-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '08-Jul-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '21-Jul-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '2- End Date' );
		$this->assertEquals( '29-Jul-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Jul-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '3- Start Date' );
		$this->assertEquals( '07-Aug-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '3- End Date' );
		$this->assertEquals( '15-Aug-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '3- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '08-Aug-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '4- Start Date' );
		$this->assertEquals( '21-Aug-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '4- End Date' );
		$this->assertEquals( '31-Aug-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '4- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Aug-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '5- Start Date' );
		$this->assertEquals( '07-Sep-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '5- End Date' );
		$this->assertEquals( '15-Sep-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '5- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '08-Sep-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '6- Start Date' );
		$this->assertEquals( '21-Sep-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '6- End Date' );
		$this->assertEquals( '30-Sep-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '6- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Sep-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '07-Oct-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '14-Oct-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '08-Oct-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '6- Start Date' );
		$this->assertEquals( '21-Oct-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '6- End Date' );
		$this->assertEquals( '31-Oct-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '6- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Oct-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '07-Nov-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '15-Nov-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '08-Nov-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '6- Start Date' );
		$this->assertEquals( '21-Nov-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '6- End Date' );
		$this->assertEquals( '30-Nov-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '6- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Nov-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '07-Dec-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '15-Dec-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '08-Dec-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '6- Start Date' );
		$this->assertEquals( '21-Dec-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '6- End Date' );
		$this->assertEquals( '30-Dec-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '6- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Dec-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '07-Jan-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '13-Jan-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		unset( $next_end_date );
	}

	/**
	 * @group PayPeriodSchedule_testSemiMonthlyD
	 */
	function testSemiMonthlyD() {
		//	Anchor: 08-May-04
		//	Primary: 22-May-04
		//	Primary Trans: 27-May-04 w/LDOM & BD
		//	Secondary: 05-Jun-04
		//	Secondary Trans: 15-Jun-04 w BD
		$ret_obj = $this->createPayPeriodSchedule( 30,
												   null, //Start DOW
												   null, //Transaction DOW
												   5, //Primary DOM
												   22, //Secondary DOM
												   31, //Primary Trans DOM
												   15, //Secondary Trans DOM
												   true //Transaction Business Day
		);

		Debug::text( 'Pay Period Schedule ID: ' . $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );


		$ret_obj->getNextPayPeriod( strtotime( '20-Jun-05' ) );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Jun-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '04-Jul-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '1- End Date' );
		$this->assertEquals( '15-Jul-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '05-Jul-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '21-Jul-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '2- End Date' );
		$this->assertEquals( '29-Jul-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Jul-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '3- Start Date' );
		$this->assertEquals( '04-Aug-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '3- End Date' );
		$this->assertEquals( '15-Aug-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '3- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '05-Aug-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '4- Start Date' );
		$this->assertEquals( '21-Aug-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '4- End Date' );
		$this->assertEquals( '31-Aug-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '4- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Aug-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '5- Start Date' );
		$this->assertEquals( '04-Sep-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '5- End Date' );
		$this->assertEquals( '15-Sep-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '5- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '05-Sep-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '6- Start Date' );
		$this->assertEquals( '21-Sep-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '6- End Date' );
		$this->assertEquals( '30-Sep-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '6- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Sep-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '04-Oct-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '14-Oct-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '05-Oct-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '8- Start Date' );
		$this->assertEquals( '21-Oct-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '8- End Date' );
		$this->assertEquals( '31-Oct-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '8- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Oct-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '9- Start Date' );
		$this->assertEquals( '04-Nov-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '9- End Date' );
		$this->assertEquals( '15-Nov-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '9- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '05-Nov-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '10- Start Date' );
		$this->assertEquals( '21-Nov-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '10- End Date' );
		$this->assertEquals( '30-Nov-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '10- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Nov-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '11- Start Date' );
		$this->assertEquals( '04-Dec-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '11- End Date' );
		$this->assertEquals( '15-Dec-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '11- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '05-Dec-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '12- Start Date' );
		$this->assertEquals( '21-Dec-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '12- End Date' );
		$this->assertEquals( '30-Dec-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '12- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Dec-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '13- Start Date' );
		$this->assertEquals( '04-Jan-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '13- End Date' );
		$this->assertEquals( '13-Jan-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '13- Transaction Date' );

		unset( $next_end_date );
	}

	/**
	 * @group PayPeriodSchedule_testSemiMonthlyE
	 */
	function testSemiMonthlyE() {
		//	Anchor: 08-May-04
		//	Primary: 22-May-04
		//	Primary Trans: 27-May-04 w/LDOM & BD
		//	Secondary: 05-Jun-04
		//	Secondary Trans: 15-Jun-04 w BD
		$ret_obj = $this->createPayPeriodSchedule( 30,
												   null, //Start DOW
												   null, //Transaction DOW
												   5, //Primary DOM
												   22, //Secondary DOM
												   31, //Primary Trans DOM
												   15, //Secondary Trans DOM
												   true //Transaction Business Day
		);

		Debug::text( 'Pay Period Schedule ID: ' . $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );


		$ret_obj->getNextPayPeriod( strtotime( '20-Jun-08' ) );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Jun-08 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '04-Jul-08 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '1- End Date' );
		$this->assertEquals( '15-Jul-08 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '05-Jul-08 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '21-Jul-08 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '2- End Date' );
		$this->assertEquals( '31-Jul-08 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Jul-08 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '3- Start Date' );
		$this->assertEquals( '04-Aug-08 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '3- End Date' );
		$this->assertEquals( '15-Aug-08 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextTransactionDate() ), '3- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '05-Aug-08 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '4- Start Date' );
		$this->assertEquals( '21-Aug-08 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '4- End Date' );
		$this->assertEquals( '29-Aug-08 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextTransactionDate() ), '4- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Aug-08 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '5- Start Date' );
		$this->assertEquals( '04-Sep-08 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '5- End Date' );
		$this->assertEquals( '15-Sep-08 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextTransactionDate() ), '5- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '05-Sep-08 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '6- Start Date' );
		$this->assertEquals( '21-Sep-08 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '6- End Date' );
		$this->assertEquals( '30-Sep-08 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextTransactionDate() ), '6- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Sep-08 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '04-Oct-08 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '15-Oct-08 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '05-Oct-08 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '8- Start Date' );
		$this->assertEquals( '21-Oct-08 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '8- End Date' );
		$this->assertEquals( '31-Oct-08 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextTransactionDate() ), '8- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Oct-08 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '9- Start Date' );
		$this->assertEquals( '04-Nov-08 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '9- End Date' );
		$this->assertEquals( '14-Nov-08 12:00 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextTransactionDate() ), '9- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '05-Nov-08 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '10- Start Date' );
		$this->assertEquals( '21-Nov-08 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '10- End Date' );
		$this->assertEquals( '28-Nov-08 12:00 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextTransactionDate() ), '10- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Nov-08 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '11- Start Date' );
		$this->assertEquals( '04-Dec-08 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '11- End Date' );
		$this->assertEquals( '15-Dec-08 12:00 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextTransactionDate() ), '11- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '05-Dec-08 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '12- Start Date' );
		$this->assertEquals( '21-Dec-08 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '12- End Date' );
		$this->assertEquals( '31-Dec-08 12:00 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextTransactionDate() ), '12- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Dec-08 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '13- Start Date' );
		$this->assertEquals( '04-Jan-09 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '13- End Date' );
		$this->assertEquals( '15-Jan-09 12:00 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextTransactionDate() ), '13- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '05-Jan-09 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '13- Start Date' );
		$this->assertEquals( '21-Jan-09 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '13- End Date' );
		$this->assertEquals( '30-Jan-09 12:00 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextTransactionDate() ), '13- Transaction Date' );


		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Jan-09 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '13- Start Date' );
		$this->assertEquals( '04-Feb-09 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '13- End Date' );
		$this->assertEquals( '13-Feb-09 12:00 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextTransactionDate() ), '13- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '05-Feb-09 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '13- Start Date' );
		$this->assertEquals( '21-Feb-09 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '13- End Date' );
		$this->assertEquals( '27-Feb-09 12:00 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextTransactionDate() ), '13- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Feb-09 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '13- Start Date' );
		$this->assertEquals( '04-Mar-09 11:59 PM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '13- End Date' );
		$this->assertEquals( '13-Mar-09 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextTransactionDate() ), '13- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '05-Mar-09 12:00 AM PST', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '13- Start Date' );
		$this->assertEquals( '21-Mar-09 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '13- End Date' );
		$this->assertEquals( '31-Mar-09 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextTransactionDate() ), '13- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '22-Mar-09 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '13- Start Date' );
		$this->assertEquals( '04-Apr-09 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '13- End Date' );
		$this->assertEquals( '15-Apr-09 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextTransactionDate() ), '13- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '05-Apr-09 12:00 AM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextStartDate() ), '13- Start Date' );
		$this->assertEquals( '21-Apr-09 11:59 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextEndDate() ), '13- End Date' );
		$this->assertEquals( '30-Apr-09 12:00 PM PDT', TTDate::getDate( 'DATE+TIME', $ret_obj->getNextTransactionDate() ), '13- Transaction Date' );

		unset( $next_end_date );
	}

	/**
	 * @group PayPeriodSchedule_testMonthly
	 */
	function testMonthly() {
		//	Anchor: 01-May-04
		//	Primary: 27-May-04 w/LDOM
		//	Primary Trans: 27-May-04 w/LDOM & BD
		//	Secondary: 27-Jun-04
		//	Secondary Trans: 27-Jun-04 w/LDOM BD
		$ret_obj = $this->createPayPeriodSchedule( 50,
												   null, //Start DOW
												   null, //Transaction DOW
												   1, //Primary DOM
												   null, //Secondary DOM
												   31, //Primary Trans DOM
												   null, //Secondary Trans DOM
												   true //Transaction Business Day
		);

		Debug::text( 'Pay Period Schedule ID: ' . $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );


		$ret_obj->getNextPayPeriod( strtotime( '01-Jul-05' ) );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Jul-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '31-Jul-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '1- End Date' );
		$this->assertEquals( '31-Jul-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Aug-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '31-Aug-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '2- End Date' );
		$this->assertEquals( '31-Aug-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Sep-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '3- Start Date' );
		$this->assertEquals( '30-Sep-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '3- End Date' );
		$this->assertEquals( '30-Sep-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '3- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Oct-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '4- Start Date' );
		$this->assertEquals( '31-Oct-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '4- End Date' );
		$this->assertEquals( '31-Oct-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '4- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Nov-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '5- Start Date' );
		$this->assertEquals( '30-Nov-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '5- End Date' );
		$this->assertEquals( '30-Nov-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '5- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Dec-05', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '6- Start Date' );
		$this->assertEquals( '31-Dec-05', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '6- End Date' );
		$this->assertEquals( '31-Dec-05', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '6- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Jan-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '31-Jan-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '31-Jan-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Feb-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '28-Feb-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '28-Feb-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Mar-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '31-Mar-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '31-Mar-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Apr-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '30-Apr-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '30-Apr-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-May-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '31-May-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '31-May-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Jun-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '30-Jun-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '30-Jun-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Jul-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '31-Jul-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '31-Jul-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Aug-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '31-Aug-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '31-Aug-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Sep-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '30-Sep-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '30-Sep-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Oct-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '31-Oct-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '31-Oct-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Nov-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '30-Nov-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '30-Nov-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Dec-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '31-Dec-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '31-Dec-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		unset( $next_end_date );
	}

	/**
	 * @group PayPeriodSchedule_testMonthlyB
	 */
	//Test month pay period on the 15th of each month.
	function testMonthlyB() {
		//	Anchor: 01-May-04
		//	Primary: 27-May-04 w/LDOM
		//	Primary Trans: 27-May-04 w/LDOM & BD
		//	Secondary: 27-Jun-04
		//	Secondary Trans: 27-Jun-04 w/LDOM BD
		$ret_obj = $this->createPayPeriodSchedule( 50,
												   null, //Start DOW
												   null, //Transaction DOW
												   15, //Primary DOM
												   null, //Secondary DOM
												   15, //Primary Trans DOM
												   null, //Secondary Trans DOM
												   false //Transaction Business Day
		);

		Debug::text( 'Pay Period Schedule ID: ' . $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );


		$ret_obj->getNextPayPeriod( strtotime( '15-Jul-06' ) );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-Jul-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '14-Aug-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '1- End Date' );
		$this->assertEquals( '15-Aug-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-Aug-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '14-Sep-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '2- End Date' );
		$this->assertEquals( '15-Sep-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-Sep-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '3- Start Date' );
		$this->assertEquals( '14-Oct-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '3- End Date' );
		$this->assertEquals( '15-Oct-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '3- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-Oct-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '4- Start Date' );
		$this->assertEquals( '14-Nov-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '4- End Date' );
		$this->assertEquals( '15-Nov-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '4- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-Nov-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '5- Start Date' );
		$this->assertEquals( '14-Dec-06', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '5- End Date' );
		$this->assertEquals( '15-Dec-06', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '5- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '15-Dec-06', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '6- Start Date' );
		$this->assertEquals( '14-Jan-07', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '6- End Date' );
		$this->assertEquals( '15-Jan-07', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '6- Transaction Date' );

		unset( $next_end_date );
	}

	/**
	 * @group PayPeriodSchedule_testMonthlyC
	 */
	function testMonthlyC() {
		//	Anchor: 01-May-09
		//	Primary: 27-May-09 w/LDOM
		//	Primary Trans: 27-May-09 w/LDOM & BD
		//	Secondary: 27-Jun-09
		//	Secondary Trans: 27-Jun-09 w/LDOM BD
		$ret_obj = $this->createPayPeriodSchedule( 50,
												   null, //Start DOW
												   null, //Transaction DOW
												   1, //Primary DOM
												   null, //Secondary DOM
												   31, //Primary Trans DOM
												   null, //Secondary Trans DOM
												   true //Transaction Business Day
		);

		Debug::text( 'Pay Period Schedule ID: ' . $ret_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );

		$ret_obj->getNextPayPeriod( strtotime( '01-Jul-09' ) );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Jul-09', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '1- Start Date' );
		$this->assertEquals( '31-Jul-09', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '1- End Date' );
		$this->assertEquals( '31-Jul-09', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '1- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Aug-09', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '2- Start Date' );
		$this->assertEquals( '31-Aug-09', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '2- End Date' );
		$this->assertEquals( '31-Aug-09', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '2- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Sep-09', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '3- Start Date' );
		$this->assertEquals( '30-Sep-09', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '3- End Date' );
		$this->assertEquals( '30-Sep-09', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '3- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Oct-09', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '4- Start Date' );
		$this->assertEquals( '31-Oct-09', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '4- End Date' );
		$this->assertEquals( '31-Oct-09', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '4- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Nov-09', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '5- Start Date' );
		$this->assertEquals( '30-Nov-09', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '5- End Date' );
		$this->assertEquals( '30-Nov-09', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '5- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Dec-09', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '6- Start Date' );
		$this->assertEquals( '31-Dec-09', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '6- End Date' );
		$this->assertEquals( '31-Dec-09', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '6- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Jan-10', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '31-Jan-10', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '31-Jan-10', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Feb-10', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '28-Feb-10', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '28-Feb-10', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Mar-10', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '31-Mar-10', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '31-Mar-10', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Apr-10', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '30-Apr-10', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '30-Apr-10', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-May-10', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '31-May-10', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '31-May-10', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Jun-10', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '30-Jun-10', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '30-Jun-10', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Jul-10', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '31-Jul-10', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '31-Jul-10', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Aug-10', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '31-Aug-10', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '31-Aug-10', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Sep-10', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '30-Sep-10', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '30-Sep-10', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Oct-10', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '31-Oct-10', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '31-Oct-10', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Nov-10', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '30-Nov-10', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '30-Nov-10', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		$ret_obj->getNextPayPeriod( $next_end_date );
		$next_end_date = $ret_obj->getNextEndDate();
		$this->assertEquals( '01-Dec-10', TTDate::getDate( 'DATE', $ret_obj->getNextStartDate() ), '7- Start Date' );
		$this->assertEquals( '31-Dec-10', TTDate::getDate( 'DATE', $ret_obj->getNextEndDate() ), '7- End Date' );
		$this->assertEquals( '31-Dec-10', TTDate::getDate( 'DATE', $ret_obj->getNextTransactionDate() ), '7- Transaction Date' );

		unset( $next_end_date );
	}
}

?>