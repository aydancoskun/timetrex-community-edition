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

/**
 * @group DateTime
 */
class PayrollRemittanceAgencyEventTest extends PHPUnit_Framework_TestCase {
	protected $company_id = NULL;
	protected $user_id = NULL;
	protected $pay_period_schedule_id = NULL;
	protected $pay_period_objs = NULL;
	protected $pay_stub_account_link_arr = NULL;
	protected $legal_entity_id = NULL;
	protected $agency_id = NULL;

	public function setUp() {
		global $dd;
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setTimeZone( 'PST8PDT', TRUE ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		$dd = new DemoData();
		$dd->setEnableQuickPunch( FALSE ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_' . uniqid( NULL, TRUE ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		$this->legal_entity_id = $dd->createLegalEntity( $this->company_id, 10 );
		Debug::text( 'Company ID: ' . $this->company_id, __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( 'Legal Entity ID: ' . $this->legal_entity_id, __FILE__, __LINE__, __METHOD__, 10 );

		//This is only needed to log in with the UI. comment this line out for production
		//$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$currency_id = $dd->createCurrency( $this->company_id, 10 );
		$dd->createUserWageGroups( $this->company_id );

		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 100 );
		Debug::text( 'User ID: ' . $this->user_id, __FILE__, __LINE__, __METHOD__, 10 );
		$user_obj = $this->getUserObject( $this->user_id );
		//Use a consistent hire date, otherwise its difficult to get things correct due to the hire date being in different parts or different pay periods.
		//Make sure it is not on a pay period start date though.
		$user_obj->setHireDate( strtotime( '05-Mar-2017' ) ); //Must not be in the future either, otherwise it could cause failures when the date passes into the past.
		$user_obj->Save( FALSE );

		$rsa_obj = TTnew( 'RemittanceSourceAccountFactory' ); /** @var RemittanceSourceAccountFactory $rsa_obj */
		$rsa_obj->setName( 'Test source account' );
		$rsa_obj->setLegalEntity( $this->legal_entity_id );
		$rsa_obj->setCompany( $this->company_id );
		$rsa_obj->setStatus( 10 );
		$rsa_obj->setType( 2000 );
		$rsa_obj->setCountry( 'US' );
		$rsa_obj->setDataFormat( 10 );
		$rsa_obj->setLastTransactionNumber( 111 );
		$rsa_obj->setCurrency( $currency_id );
		$rsa_id = $rsa_obj->Save();

		$praf = TTnew( 'PayrollRemittanceAgencyFactory' ); /** @var PayrollRemittanceAgencyFactory $praf */

		$praf->setName( 'Testing Agency' );
		$praf->setLegalEntity( $this->legal_entity_id );
		$praf->setStatus( 10 );
		$praf->setType( 10 );
		$praf->setCountry( 'CA' );
		//$praf->setProvince( 'NY' );
		$praf->setAgency( '10:CA:00:00:0010' );
		$praf->setContactUser( $this->user_id );
		$praf->setRemittanceSourceAccount( $rsa_id );

		$this->agency_id = $praf->Save();

		$this->assertEquals( TRUE, TTUUID::isUUID( $this->user_id ), 'company_id is not a UUID' );
		$this->assertEquals( TRUE, TTUUID::isUUID( $this->company_id ), 'user_id is not a UUID' );
		$this->assertEquals( TRUE, TTUUID::isUUID( $this->agency_id ), 'agency_id is not a UUID' );

		return TRUE;
	}

	function createPayStubAccounts() {
		Debug::text('Saving.... Employee Deduction - Other', __FILE__, __LINE__, __METHOD__, 10);
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus(10);
		$pseaf->setType(20);
		$pseaf->setName('Other');
		$pseaf->setOrder(290);

		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}

		Debug::text('Saving.... Employee Deduction - Other2', __FILE__, __LINE__, __METHOD__, 10);
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus(10);
		$pseaf->setType(20);
		$pseaf->setName('Other2');
		$pseaf->setOrder(291);

		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}

		Debug::text('Saving.... Employee Deduction - EI', __FILE__, __LINE__, __METHOD__, 10);
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus(10);
		$pseaf->setType(20);
		$pseaf->setName('EI');
		$pseaf->setOrder(292);

		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}

		Debug::text('Saving.... Employee Deduction - CPP', __FILE__, __LINE__, __METHOD__, 10);
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus(10);
		$pseaf->setType(20);
		$pseaf->setName('CPP');
		$pseaf->setOrder(293);

		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}

		//Link Account EI and CPP accounts
		$pseallf = new PayStubEntryAccountLinkListFactory();
		$pseallf->getByCompanyId( $this->company_id );
		if ( $pseallf->getRecordCount() > 0 ) {
			$pseal_obj = $pseallf->getCurrent();
			$pseal_obj->setEmployeeEI( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'EI') );
			$pseal_obj->setEmployeeCPP( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'CPP') );
			$pseal_obj->Save();
		}


		return TRUE;
	}

	function createPayPeriodSchedule() {
		$ppsf = new PayPeriodScheduleFactory();

		$ppsf->setCompany( $this->company_id );
		//$ppsf->setName( 'Bi-Weekly'.rand(1000,9999) );
		$ppsf->setName( 'Bi-Weekly' );
		$ppsf->setDescription( 'Pay every two weeks' );
		$ppsf->setType( 20 );
		$ppsf->setStartWeekDay( 0 );


		$anchor_date = TTDate::getBeginWeekEpoch( TTDate::getBeginYearEpoch() ); //Start 6 weeks ago

		$ppsf->setAnchorDate( $anchor_date );

		$ppsf->setStartDayOfWeek( TTDate::getDayOfWeek( $anchor_date ) );
		$ppsf->setTransactionDate( 7 );

		$ppsf->setTransactionDateBusinessDay( TRUE );
		$ppsf->setTimeZone('PST8PDT');

		$ppsf->setDayStartTime( 0 );
		$ppsf->setNewDayTriggerTime( (4 * 3600) );
		$ppsf->setMaximumShiftTime( (16 * 3600) );

		$ppsf->setEnableInitialPayPeriods( FALSE );
		if ( $ppsf->isValid() ) {
			$insert_id = $ppsf->Save(FALSE);
			Debug::Text('Pay Period Schedule ID: '. $insert_id, __FILE__, __LINE__, __METHOD__, 10);

			$ppsf->setUser( array($this->user_id) );
			$ppsf->Save();

			$this->pay_period_schedule_id = $insert_id;

			return $insert_id;
		}

		Debug::Text('Failed Creating Pay Period Schedule!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;

	}

	function addUserToPayPeriodSchedule($pay_period_schedule_id, $user_id ) {
			$ppsuf = new PayPeriodScheduleUserFactory();
			$ppsuf->setUser($user_id);
			$ppsuf->setPayPeriodSchedule($pay_period_schedule_id);
			if ( $ppsuf->isValid() ) {
				$ppsuf->save();
			}
	}

	function createPayPeriods( $initial_date, $pay_period_schedule_id ) {
		$max_pay_periods = 35;

		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getById( $pay_period_schedule_id );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pps_obj = $ppslf->getCurrent();

			for ( $i = 0; $i < $max_pay_periods; $i++ ) {
				if ( $i == 0 ) {
					if ( $initial_date !== FALSE ) {
						$end_date = $initial_date;
					} else {
						//$end_date = TTDate::getBeginYearEpoch( strtotime('01-Jan-07') );
						$end_date = TTDate::getBeginWeekEpoch( ( TTDate::getBeginYearEpoch( time() ) - (86400 * (7 * 6) ) ) );
					}
				} else {
					$end_date = ($end_date + ( (86400 * 14) ));
				}

				Debug::Text('I: '. $i .' End Date: '. TTDate::getDate('DATE+TIME', $end_date), __FILE__, __LINE__, __METHOD__, 10);

				$pps_obj->createNextPayPeriod( $end_date, (86400 * 3600), FALSE ); //Don't import punches, as that causes deadlocks when running tests in parallel.
			}

		}

		return TRUE;
	}


	function getAllPayPeriods($pay_period_schedule_id) {
		$pplf = new PayPeriodListFactory();
		//$pplf->getByCompanyId( $this->company_id );
		$pplf->getByPayPeriodScheduleId( $pay_period_schedule_id );
		if ( $pplf->getRecordCount() > 0 ) {
			foreach( $pplf as $pp_obj ) {
				Debug::text('Pay Period... Start: '. TTDate::getDate('DATE+TIME', $pp_obj->getStartDate() ) .' End: '. TTDate::getDate('DATE+TIME', $pp_obj->getEndDate() ), __FILE__, __LINE__, __METHOD__, 10);

				$this->pay_period_objs[] = $pp_obj;
			}
		}

		$this->pay_period_objs = array_reverse( $this->pay_period_objs );

		return TRUE;
	}

	function getUserObject( $user_id ) {
		$ulf = TTNew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getById( $user_id );
		if ( $ulf->getRecordCount() > 0 ) {
			return $ulf->getCurrent();
		}

		return FALSE;
	}

	public function tearDown() {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );

		return TRUE;
	}

	/**
	 * test the weekly frequency
	 */
	function testWeekly() {
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setDayOfWeek( 0 );
		$praef->setFrequency( 5100 );

		//time edges of 01-Dec 2016
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:01AM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:00AM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );

		//time edges of 04-Dec 2016
		$result = $praef->calculateNextDate( strtotime( '04-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '11-Dec-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '04-Dec-2016 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '11-Dec-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '04-Dec-2016 12:00AM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '11-Dec-2016 12:00PM' ) ) );

		//reverse day of week edge
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:00AM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '02-Dec-2016 12:00AM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '03-Dec-2016 12:00AM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );

		//checking every day for a week (like cron)
		$result = $praef->calculateNextDate( strtotime( '25-Nov-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '20-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '26-Nov-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '27-Nov-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '26-Nov-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '20-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '26-Nov-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '27-Nov-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '27-Nov-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '28-Nov-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '29-Nov-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '30-Nov-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '31-Nov-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );

		//forward day of week edge
		$result = $praef->calculateNextDate( strtotime( '09-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '11-Dec-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '10-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '11-Dec-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '11-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '11-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '17-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '18-Dec-2016 12:00PM' ) ) );

		//2 weeks spanning a year edge (like cron)
		$result = $praef->calculateNextDate( strtotime( '24-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '18-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '24-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Dec-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '25-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '25-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '26-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '25-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '27-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '25-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '28-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '25-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '29-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '25-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '30-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '25-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '31-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '25-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '01-Jan-2017 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '08-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '02-Jan-2017 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '08-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '03-Jan-2017 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '08-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '04-Jan-2017 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '08-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '05-Jan-2017 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '08-Jan-2017 12:00PM' ) ) );

		//daylight savings
		$result = $praef->calculateNextDate( strtotime( '12-Mar-2017 01:59AM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '12-Mar-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '18-Mar-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '19-Mar-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '12-Mar-2017 02:01AM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '12-Mar-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '18-Mar-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '19-Mar-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '12-Mar-2017 11:59AM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '12-Mar-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '18-Mar-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '19-Mar-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '05-Nov-2017 01:59AM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '05-Nov-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '11-Nov-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Nov-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '05-Nov-2017 02:01AM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '05-Nov-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '11-Nov-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Nov-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '05-Nov-2017 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '05-Nov-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '11-Nov-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Nov-2017 12:00PM' ) ) );

		//chaining test (like wizard)
		$result = $praef->calculateNextDate( strtotime( '01-Jan-2017 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '08-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//08-Jan-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '14-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//15-Jan-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '15-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '21-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '22-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//22-Jan-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '22-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '28-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '29-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//29-Jan-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '29-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '04-Feb-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '05-Feb-2017 12:00PM' ) ) );
	}

	function testMonthly() {
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 4100 );
		$praef->setPrimaryDayOfMonth( 10 );
		$praef->setEffectiveDate( strtotime( '01-Dec-2016' ) );

		//testing minutes edge
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 01:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Nov-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Dec-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:01AM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Nov-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Dec-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:00AM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Nov-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Dec-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Nov-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Dec-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Nov-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Dec-2016 12:00PM' ) ) );

		//check that leapyears don't overflow.
		$praef->setPrimaryDayOfMonth( 31 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2016' ) );
		$result = $praef->calculateNextDate( strtotime( '21-Jan-2016 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Dec-2015  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2015  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jan-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '01-Feb-2016 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '29-Feb-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '31-Jan-2016 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '29-Feb-2016 12:00PM' ) ) );


		//chained test (like wizard)
		$praef->setPrimaryDayOfMonth( 10 );
		$praef->setEffectiveDate( strtotime( '01-Dec-2016' ) );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 01:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Nov-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Dec-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//10-Dec-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//10-Jan-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//10-Feb-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Feb-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '28-Feb-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Mar-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//10-Mar-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Mar-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Apr-2017 12:00PM' ) ) );


		$praef->setPrimaryDayOfMonth( 13 );
		$seed_date = TTDate::getMiddleDayEpoch( strtotime( '31-Dec-' . ( date( 'Y' ) - 1 ) . ' 12:00PM' ) );
		//run current whole year
		for ( $x = 0; $x <= 365; $x++ ) {
//			Debug::Text( 'testLoopMonthly '.$x.' seed_date: '. TTDate::getDate('DATE+TIME', $seed_date), __FILE__, __LINE__, __METHOD__, 10);

			$due_date = TTDate::getDateOfNextDayOfMonth( ( $seed_date + 86400 ), FALSE, $praef->getPrimaryDayOfMonth() );
			$month_before_due_date = TTDate::incrementDate( $due_date, -1, 'month' );

			$start_date = TTDate::getBeginDayEpoch( TTDate::getBeginMonthEpoch( $month_before_due_date ) );
			$end_date = TTDate::getEndDayEpoch( TTDate::getEndMonthEpoch( $month_before_due_date ) );
			$due_date = TTDate::getMiddleDayEpoch( $due_date );

//			Debug::Text("compare start_date: ".date('r', $start_date), __FILE__, __LINE__, __METHOD__, 10);
//			Debug::Text("compare end_date: ".date('r', $end_date), __FILE__, __LINE__, __METHOD__, 10);
//			Debug::Text("compare due_date: ".date('r', $due_date), __FILE__, __LINE__, __METHOD__, 10);

			$result = $praef->calculateNextDate( $seed_date );

			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', $start_date ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', $end_date ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', $due_date ) );
			$seed_date = $result['due_date'];
		}
	}

	//biweekly frequency is commented out in the factory.
//	function testBiWeekly() {
//		$praef = TTnew('PayrollRemittanceAgencyEventFactory');
//		$praef->setFrequency( 5000 );
//		$praef->setDayOfWeek(1);
//		$praef->setEffectiveDate(strtotime('01-Dec-2015'));
//
//		//time edge
//		$result = $praef->calculateNextDate( strtotime('01-Dec-2016') );
//		$this->assertEquals( date('r', $result['start_date']), date('r', strtotime('27-nov-2016 12:00PM')) );
//		$this->assertEquals( date('r', $result['end_date']), date('r', strtotime('11-Dec-2016 12:00PM')) );
//		$this->assertEquals( date('r', $result['due_date']), date('r', strtotime('05-Dec-2016 12:00PM')) );
//
//		$praef->setDayOfWeek(2);
//		$this->assertEquals( date('r', $praef->calculateNextDate( strtotime('01-Dec-2016') )), date('r', strtotime('20-Dec-2016 12:00PM')) );
//		$praef->setDayOfWeek(4);
//		$this->assertEquals( date('r', $praef->calculateNextDate( strtotime('23-Feb-2016') )), date('r', strtotime('10-Mar-2016 12:00PM')) );
//	}

	function testAnnual() {
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 2000 );
		$praef->setPrimaryDayOfMonth( 1 );
		$praef->setPrimaryMonth( 12 );
		$praef->setEffectiveDate( strtotime( '01-Dec-2016' ) );

		//testing time edges/variations
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Dec-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:01AM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Dec-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Dec-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Dec-2017 12:00PM' ) ) );

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 2000 );
		$praef->setPrimaryDayOfMonth( 12 );
		$praef->setPrimaryMonth( 2 );
		$praef->setEffectiveDate( strtotime( '01-Dec-2012' ) );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2014' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2014  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2014  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Feb-2015 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '01-Jan-2014' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2013  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2013  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Feb-2014 12:00PM' ) ) );

		//testing a few consecutive days ( like cron )
		$result = $praef->calculateNextDate( strtotime( '10-Feb-2014' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2013  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2013  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Feb-2014 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '11-Feb-2014' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2013  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2013  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Feb-2014 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '12-Feb-2014' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2014  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2014  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Feb-2015 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '13-Feb-2014' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2014  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2014  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Feb-2015 12:00PM' ) ) );

		//chained test ( like wizard )
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 2000 );
		$praef->setPrimaryDayOfMonth( 12 );
		$praef->setPrimaryMonth( 2 );
		$praef->setEffectiveDate( strtotime( '01-Dec-2012' ) );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2014' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2014 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2014 11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Feb-2015 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//12-Feb-2015 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2015 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2015 11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Feb-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//12-Feb-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2016 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016 11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Feb-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//12-Feb-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2017 11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Feb-2018 12:00PM' ) ) );

		//chained loop test.
		$praef->setPrimaryDayOfMonth( 10 );
		$praef->setPrimaryMonth( 2 );
		$praef->setEffectiveDate( time() );
		$epoch = TTDate::getMiddleDayEpoch( $praef->getEffectiveDate() );
		for ( $i = 0; $i < 25; $i++ ) {
			$artificial_match_value = mktime( 0, 0, 0, $praef->getPrimaryMonth(), $praef->getPrimaryDayOfMonth(), date( 'Y', $epoch ) );
			$artificial_match_value = TTDate::getMiddleDayEpoch( $artificial_match_value );
			if ( $artificial_match_value <= $epoch ) {
				$artificial_match_value = TTDate::incrementDate( $artificial_match_value, 1, 'year' );
			}
			$year_before_match = TTDate::incrementDate( $artificial_match_value, -1, 'year' );
			$result = $praef->calculateNextDate( $epoch );
			$epoch = $result['due_date'];
			$start_date = TTDate::getBeginDayEpoch( TTDate::getBeginYearEpoch( $year_before_match ) );
			$end_date = TTDate::getEndDayEpoch( TTDate::getEndYearEpoch( $year_before_match ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', $start_date ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', $end_date ) );
			$this->assertEquals( date( 'r', $epoch ), date( 'r', $artificial_match_value ) );
		}
	}


	function testQuarterlyA() {
		Debug::Text( 'testQuarterly', __FILE__, __LINE__, __METHOD__, 10 );

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 3000 );
		$praef->setPrimaryDayOfMonth( 1 );
		$praef->setQuarterMonth( 1 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2015' ) );

		//chained test (like wizard)
		$result = $praef->calculateNextDate( strtotime( '01-Oct-2015 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2015  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2015  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jan-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //01-Jan-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Apr-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //01-Apr-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Jun-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jul-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //01-Jul-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Oct-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //01-Oct-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2016 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jan-2017 12:00PM' ) ) );


		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 3000 );
		$praef->setPrimaryDayOfMonth( 1 );
		$praef->setQuarterMonth( 1 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2016' ) );

		//change quarter month
		$praef->setQuarterMonth( 2 );
		$result = $praef->calculateNextDate( strtotime( '11-Sep-2016 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Nov-2016 12:00PM' ) ) );

		$praef->setQuarterMonth( 1 );
		$praef->setPrimaryDayOfMonth( 3 );
		//based off effective date
		$result = $praef->calculateNextDate( strtotime( '11-Sep-1981 12:00PM' ) ); //Before effective date, so it gets set to that instead: '01-Jan-2016'
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2016 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2016 11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Apr-2016 12:00PM' ) ) );

		//try a really old one
		$praef->setEffectiveDate( strtotime( '01-Jan-1981' ) );
		$result = $praef->calculateNextDate( strtotime( '11-Sep-1981 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-1981  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-1981  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Oct-1981 12:00PM' ) ) );

		//try a far future one
		$praef->setEffectiveDate( strtotime( '01-Jan-2025' ) );
		$result = $praef->calculateNextDate( strtotime( '11-Sep-1981 12:00PM' ) ); //Before effective date, so it gets set to that instead: '01-Jan-2016'
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2025 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2025 11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Apr-2025 12:00PM' ) ) );

		//leap year hitting the extra day with an overshot last day of month
		//31st and month = 2 (feb)
		$praef->setPrimaryDayOfMonth( 31 );
		$praef->setQuarterMonth( 2 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2015' ) );

		//checking for leap year overflow
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2015 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2015  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2015  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '29-Feb-2016 12:00PM' ) ) );
		$praef->setPrimaryDayOfMonth( 28 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2015' ) );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2015 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2015  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2015  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '28-Feb-2016 12:00PM' ) ) );
		$praef->setPrimaryDayOfMonth( 29 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2015' ) );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2015 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2015  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2015  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '29-Feb-2016 12:00PM' ) ) );
		$praef->setPrimaryDayOfMonth( 30 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2015' ) );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2015 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2015  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2015  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '29-Feb-2016 12:00PM' ) ) );
	}

	function testQuarterlyB() {
		Debug::Text( 'testQuarterly', __FILE__, __LINE__, __METHOD__, 10 );

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 3000 );
		$praef->setPrimaryDayOfMonth( 31 );
		$praef->setQuarterMonth( 1 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2015' ) );

		//chained test (like wizard)
		$result = $praef->calculateNextDate( strtotime( '01-Oct-2015 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2015  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2015  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jan-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //01-Jan-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //01-Apr-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Jun-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //01-Jul-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Oct-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //01-Oct-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2016 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jan-2017 12:00PM' ) ) );


		$praef->setPrimaryDayOfMonth( 31 );
		$praef->setQuarterMonth( 2 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2015' ) );

		//chained test (like wizard)
		$result = $praef->calculateNextDate( strtotime( '01-Oct-2015 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2015  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2015  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '29-Feb-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //01-Jan-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-May-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //01-Apr-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Jun-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Aug-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //01-Jul-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Nov-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //01-Oct-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2016 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '28-Feb-2017 12:00PM' ) ) );
	}

	function testSemiWeekly() {
		//Wednesday, Thursday, Friday = Wednesday. Saturday, Sunday, Monday, Tuesday = Friday
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 64000 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2017' ) );

		//test consecutive days (like cron)
		$result = $praef->calculateNextDate( strtotime( '01-Sep-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '30-Aug-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '01-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '06-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '02-Sep-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '02-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '05-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '08-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '03-Sep-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '02-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '05-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '08-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '04-Sep-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '02-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '05-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '08-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '05-Sep-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '02-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '05-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '08-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '06-Sep-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '06-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '08-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '13-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '07-Sep-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '06-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '08-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '13-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '08-Sep-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '06-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '08-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '13-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '09-Sep-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '09-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '12-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Sep-2017 12:00PM' ) ) );

		//chained test (like wizard)
		$result = $praef->calculateNextDate( strtotime( '01-Sep-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '30-Aug-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '01-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '06-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //06-Sep-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '06-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '08-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '13-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //13-Sep-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '13-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '20-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //20-Sep-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '20-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '22-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '27-Sep-2017 12:00PM' ) ) );

	}

	function testAcceleratedThreshold1() {
		// 10th and 25th of each month. If transaction date falls between 1-15th of the month, pay by 25th. If it falls between 16th and last day, pay on the 10th of the next month.
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 50000 );
		$praef->setEffectiveDate( strtotime( '01-Jul-2017' ) );

		//tets consecutive days (like cron)
		$result = $praef->calculateNextDate( strtotime( '01-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '02-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '03-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '04-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '13-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '14-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '15-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '16-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '17-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '18-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '19-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '30-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '31-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );


		//chained test(like wizard)
		$result = $praef->calculateNextDate( $result['due_date'] ); //10-Aug-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Aug-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Aug-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Aug-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //25-Aug-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Aug-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Aug-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //10-Sep-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //25-Sep-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Oct-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //10-Oct-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Oct-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Oct-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //25-Oct-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Oct-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Oct-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Nov-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //10-Nov-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Nov-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Nov-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //10-Nov-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Nov-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Nov-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Dec-2017 12:00PM' ) ) );

	}

	function testAcceleratedThreshold2() {
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 51000 );
		$praef->setEffectiveDate( strtotime( '01-Jul-2017' ) );
		$result = $praef->calculateNextDate( strtotime( '01-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jul-2017 12:00PM' ) ) );

		//consecutive days (like cron)
		$result = $praef->calculateNextDate( strtotime( '05-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '06-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '07-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jul-2017 12:00PM' ) ) );

		//chained tests (like wizard)
		$result = $praef->calculateNextDate( $result['due_date'] ); //10-Jul-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '14-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '17-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //17-Jul-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '15-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '21-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '24-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //24-Jul-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '22-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Aug-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //03-Aug-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Aug-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Aug-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //10-Aug-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Aug-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '14-Aug-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '17-Aug-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //17-Aug-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '15-Aug-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '21-Aug-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '24-Aug-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //24-Aug-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '22-Aug-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Aug-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Sep-2017 12:00PM' ) ) );
	}

	function testWeekendAvoidance() {
		$praf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $praf */
		$praf->getById( $this->agency_id );
		$pra_obj = $praf->getCurrent();

		$this->assertEquals( TRUE, is_object($pra_obj), 'agency is not an object' );

		/**
		 * 0 => TTi18n::gettext('No'),
		 * 1 => TTi18n::gettext('Yes - Previous Business Day'),
		 * 2 => TTi18n::gettext('Yes - Next Business Day'),
		 * 3 => TTi18n::gettext('Yes - Closest Business Day'),
		 */


//
		$pra_obj->setAlwaysOnWeekDay( 0 ); //no weekend check.
		$pra_obj->save( FALSE );

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 5100 );
		$praef->setDayOfWeek( 0 );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:01AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );
//

		//
		$pra_obj->setAlwaysOnWeekDay( 2 ); //2=Forward
		$pra_obj->save( FALSE );

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 5100 );
		$praef->setDayOfWeek( 0 );

		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:01AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '05-Dec-2016 12:00PM' ) ) );


		$pra_obj->setAlwaysOnWeekDay( 3 ); //3=closest business day
		$pra_obj->save( FALSE );

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 5100 );
		$praef->setDayOfWeek( 0 );


		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:01AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '05-Dec-2016 12:00PM' ) ) );

		$pra_obj->setAlwaysOnWeekDay( 1 ); //1=Backwards
		$pra_obj->save( FALSE );

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 5100 );
		$praef->setDayOfWeek( 0 );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:01AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '02-Dec-2016 12:00PM' ) ) );

		unset($pra_obj, $praef);

	}

	function testHolidayAvoidance() {
		$praf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $praf */
		$praf->getById($this->agency_id);
		$pra_obj = $praf->getCurrent();

		$this->assertEquals( TRUE, is_object($pra_obj), 'agency is not an object' );

		$holiday_policy_ids = array();

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setId( $praef->getNextInsertId() );
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setType( 'T4' );
		$praef->setReminderDays( 2 );
		$praef->setFrequency( 4100 );
		$praef->setPrimaryDayOfMonth( 25 );
		$praef->setStatus( 10 );
		$praef->setEffectiveDate( strtotime( '05-Dec-2017 12:00AM' ) );
		$praef->save( FALSE );

		$rhf_xmas = TTNew( 'RecurringHolidayFactory' ); /** @var RecurringHolidayFactory $rhf_xmas */
		$rhf_xmas->setCompany( $this->company_id );
		$rhf_xmas->setName( 'Test - xmas' );
		$rhf_xmas->setType( 10 );
		$rhf_xmas->setDayOfMonth( 25 );
		$rhf_xmas->setMonth( 12 );
		$holiday_policy_ids[] = $rhf_xmas->save();

		$rhf_box = TTNew( 'RecurringHolidayFactory' ); /** @var RecurringHolidayFactory $rhf_box */
		$rhf_box->setCompany( $this->company_id );
		$rhf_box->setName( 'Test - boxerday' );
		$rhf_box->setType( 10 );
		$rhf_box->setDayOfMonth( 26 );
		$rhf_box->setMonth( 12 );
		$holiday_policy_ids[] = $rhf_box->save();


		$pra_obj->setRecurringHoliday( $holiday_policy_ids );
		$holidays = $pra_obj->getRecurringHoliday();
		$this->assertGreaterThanOrEqual( 2, count( $holidays ), 'Holiday Count is wrong' );

		$holidates = $praef->getRecurringHolidayDates( strtotime( '05-Dec-2017 12:00AM' ) );
		$this->assertGreaterThanOrEqual( 2, count( $holidates ), 'Holiday date Count is wrong: ' . count( $holidates ) );


		$pra_obj->setAlwaysOnWeekDay( 0 ); //1=Backwards
		$pra_obj->save( FALSE );

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setType( 10 );
		$praef->setReminderDays( 2 );
		$praef->setFrequency( 4100 );
		$praef->setPrimaryDayOfMonth( 25 );
		$praef->setStatus( 10 );

		$result = $praef->calculateNextDate( strtotime( '05-Dec-2017 12:01AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Dec-2017 12:00PM' ) ) );

		$pra_obj->setAlwaysOnWeekDay( 1 ); //1=Backwards
		$pra_obj->save( FALSE );

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setType( 10 );
		$praef->setReminderDays( 2 );
		$praef->setFrequency( 4100 );
		$praef->setPrimaryDayOfMonth( 25 );
		$praef->setStatus( 10 );

		$result = $praef->calculateNextDate( strtotime( '05-Dec-2017 12:01AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '22-Dec-2017 12:00PM' ) ) );

		$pra_obj->setAlwaysOnWeekDay( 2 ); //1=Backwards
		$pra_obj->save( FALSE );

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setType( 10 );
		$praef->setReminderDays( 2 );
		$praef->setFrequency( 4100 );
		$praef->setPrimaryDayOfMonth( 25 );
		$praef->setStatus( 10 );

		$result = $praef->calculateNextDate( strtotime( '05-Dec-2017 12:00AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '27-Dec-2017 12:00PM' ) ) );

		$pra_obj->setAlwaysOnWeekDay( 3 ); //1=Backwards
		$pra_obj->save( FALSE );

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setType( 10 );
		$praef->setReminderDays( 2 );
		$praef->setFrequency( 4100 );
		$praef->setPrimaryDayOfMonth( 25 );
		$praef->setStatus( 10 );

		$result = $praef->calculateNextDate( strtotime( '05-Dec-2017 12:01AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '27-Dec-2017 12:00PM' ) ) );
	}


	function testWeekLongHolidayAvoidance() {
		$holiday_policy_ids = array();

		$praf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $praf */
		$praf->getById($this->agency_id);
		$pra_obj = $praf->getCurrent();


//		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
//		$praef->setId( FALSE );
//		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
//		$praef->setType( 10 );
//		$praef->setReminderDays( 2 );
//		$praef->setFrequency( 4100 );
//		$praef->setPrimaryDayOfMonth( 25 );
//		$praef->setStatus( 10 );
//		$praef->setEffectiveDate( strtotime( '05-Dec-2017 12:00AM' ) );

		$rhf_xmas = TTNew( 'RecurringHolidayFactory' ); /** @var RecurringHolidayFactory $rhf_xmas */
		$rhf_xmas->setCompany( $this->company_id );
		$rhf_xmas->setName( 'Test - xmas' );
		$rhf_xmas->setType( 10 );
		$rhf_xmas->setDayOfMonth( 25 );
		$rhf_xmas->setMonth( 12 );
		$rhf_xmas->setAlwaysOnWeekDay( 2 );
		$holiday_policy_ids[] = $rhf_xmas->save();

		$rhf_box = TTNew( 'RecurringHolidayFactory' ); /** @var RecurringHolidayFactory $rhf_box */
		$rhf_box->setCompany( $this->company_id );
		$rhf_box->setName( 'Test - boxerday' );
		$rhf_box->setType( 10 );
		$rhf_box->setDayOfMonth( 26 );
		$rhf_box->setMonth( 12 );
		$rhf_box->setAlwaysOnWeekDay( 2 );
		$holiday_policy_ids[] = $rhf_box->save();

		$rhf_box = TTNew( 'RecurringHolidayFactory' ); /** @var RecurringHolidayFactory $rhf_box */
		$rhf_box->setCompany( $this->company_id );
		$rhf_box->setName( 'Test - 27th dec' );
		$rhf_box->setType( 10 );
		$rhf_box->setDayOfMonth( 27 );
		$rhf_box->setMonth( 12 );
		$rhf_box->setAlwaysOnWeekDay( 2 );
		$holiday_policy_ids[] = $rhf_box->save();

		$rhf_box = TTNew( 'RecurringHolidayFactory' ); /** @var RecurringHolidayFactory $rhf_box */
		$rhf_box->setCompany( $this->company_id );
		$rhf_box->setName( 'Test - 28th dec' );
		$rhf_box->setType( 10 );
		$rhf_box->setDayOfMonth( 28 );
		$rhf_box->setMonth( 12 );
		$rhf_box->setAlwaysOnWeekDay( 2 );
		$holiday_policy_ids[] = $rhf_box->save();

		$rhf_box = TTNew( 'RecurringHolidayFactory' ); /** @var RecurringHolidayFactory $rhf_box */
		$rhf_box->setCompany( $this->company_id );
		$rhf_box->setName( 'Test - 29th dec' );
		$rhf_box->setType( 10 );
		$rhf_box->setDayOfMonth( 29 );
		$rhf_box->setMonth( 12 );
		$rhf_box->setAlwaysOnWeekDay( 2 );
		$holiday_policy_ids[] = $rhf_box->save();


		$pra_obj->setRecurringHoliday( $holiday_policy_ids );
		$holidays = $pra_obj->getRecurringHoliday();
		$this->assertGreaterThanOrEqual( 2, count( $holidays ), 'Holiday Count is wrong' );

		/**
		 * 0 => TTi18n::gettext('No'),
		 * 1 => TTi18n::gettext('Yes - Previous Business Day'),
		 * 2 => TTi18n::gettext('Yes - Next Business Day'),
		 * 3 => TTi18n::gettext('Yes - Closest Business Day'),
		 */

		$pra_obj->setAlwaysOnWeekDay( 0 ); //1=none
		$pra_obj->save( FALSE );

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setEffectiveDate( strtotime( '05-Dec-2017 12:00AM' ) );
		$praef->setLastDueDate( strtotime( '05-Dec-2017 12:00AM' ) );
		$praef->setType( 10 );
		$praef->setReminderDays( 2 );
		$praef->setFrequency( 4100 );
		$praef->setPrimaryDayOfMonth( 25 );
		$praef->setStatus( 10 );

		$holidates = $praef->getRecurringHolidayDates( strtotime( '05-Dec-2017 12:00AM' ) );
		$this->assertGreaterThanOrEqual( 2, count( $holidates ), 'Holiday date Count is wrong: ' . count( $holidates ) );

		$result = $praef->calculateNextDate( strtotime( '05-Dec-2017 12:01AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Dec-2017 12:00PM' ) ) );

		$pra_obj->setAlwaysOnWeekDay( 1 ); //1=Backwards
		$pra_obj->save( FALSE );

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setEffectiveDate( strtotime( '05-Dec-2017 12:00AM' ) );
		$praef->setLastDueDate( strtotime( '05-Dec-2017 12:00AM' ) );
		$praef->setType( 10 );
		$praef->setReminderDays( 2 );
		$praef->setFrequency( 4100 );
		$praef->setPrimaryDayOfMonth( 25 );
		$praef->setStatus( 10 );
		$result = $praef->calculateNextDate( strtotime( '05-Dec-2017 12:01AM' ) );

		$this->assertEquals( date( 'r', $praef->getEffectiveDate() ), date( 'r', strtotime( '05-Dec-2017 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $praef->getLastDueDate() ), date( 'r', strtotime( '05-Dec-2017 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '22-Dec-2017 12:00PM' ) ) );

		$pra_obj->setAlwaysOnWeekDay( 2 ); //2=next
		$pra_obj->save( FALSE );

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setEffectiveDate( strtotime( '05-Dec-2017 12:00AM' ) );
		$praef->setLastDueDate( strtotime( '05-Dec-2017 12:00AM' ) );
		$praef->setType( 10 );
		$praef->setFrequency( 4100 );
		$praef->setPrimaryDayOfMonth( 25 );
		$praef->setStatus( 10 );
		$result = $praef->calculateNextDate( strtotime( '05-Dec-2017 12:01AM' ) );

		$this->assertEquals( date( 'r', $praef->getEffectiveDate() ), date( 'r', strtotime( '05-Dec-2017 12:00AM' ) ), 'getEffectiveDate does not match' );
		$this->assertEquals( date( 'r', $praef->getLastDueDate() ), date( 'r', strtotime( '05-Dec-2017 12:00AM' ) ), 'getLastDueDate does not match' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jan-2018 12:00PM' ) ) );

		$pra_obj->setAlwaysOnWeekDay( 3 ); //closest
		$pra_obj->save( FALSE );

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setEffectiveDate( strtotime( '05-Dec-2017 12:00AM' ) );
		$praef->setLastDueDate( strtotime( '05-Dec-2017 12:00AM' ) );
		$praef->setType( 10 );
		$praef->setFrequency( 4100 );
		$praef->setPrimaryDayOfMonth( 25 );
		$praef->setStatus( 10 );
		$result = $praef->calculateNextDate( strtotime( '05-Dec-2017 12:01AM' ) );

		$this->assertEquals( date( 'r', $praef->getEffectiveDate() ), date( 'r', strtotime( '05-Dec-2017 12:00AM' ) ), 'getEffectiveDate does not match' );
		$this->assertEquals( date( 'r', $praef->getLastDueDate() ), date( 'r', strtotime( '05-Dec-2017 12:00AM' ) ), 'getLastDueDate does not match' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '22-Dec-2017 12:00PM' ) ) );
	}

	function testSemiMonthlyFrequency() {

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 4200 );

		$praef->setPrimaryDayOfMonth(7);
		$praef->setSecondaryDayOfMonth(22);
		$praef->setDueDateDelayDays(3);
		$praef->setEffectiveDate( strtotime( '01-Jul-2017' ) );

		$result = $praef->calculateNextDate( strtotime( '01-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jun-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jul-2017 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '05-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jun-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '06-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jun-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '07-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jun-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jul-2017 12:00PM' ) ) );


		$result = $praef->calculateNextDate( strtotime( '08-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '22-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '09-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '22-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '21-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '22-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '22-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '22-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '23-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Aug-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '30-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Aug-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '1-Aug-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Aug-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );


		$praef->setPrimaryDayOfMonth(10);
		$praef->setSecondaryDayOfMonth(25);
		$praef->setDueDateDelayDays(6);
		$praef->setEffectiveDate( strtotime( '01-Jul-2017' ) );

		$result = $praef->calculateNextDate( strtotime( '01-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '26-Jun-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '16-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '06-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '26-Jun-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '16-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '09-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '26-Jun-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '16-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '10-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '26-Jun-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '16-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '11-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '11-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '25-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2017 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '12-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '11-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '25-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2017 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '24-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '11-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '25-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2017 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '25-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '11-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '25-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '26-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '26-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Aug-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '16-Aug-2017 12:00PM' ) ) );


		//ca threshold1
		// 10th and 25th of each month. If transaction date falls between 1-15th of the month, pay by 25th. If it falls between 16th and last day, pay on the 10th of the next month.
		$praef->setPrimaryDayOfMonth(15);
		$praef->setSecondaryDayOfMonth(31);
		$praef->setDueDateDelayDays(10);
		$praef->setEffectiveDate( strtotime( '01-Jul-2017' ) );

		$result = $praef->calculateNextDate( strtotime( '01-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '02-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '03-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '04-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '13-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '14-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '15-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '16-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '17-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '18-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '19-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '30-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '31-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );

		//chained tests (like wizard)
		$result = $praef->calculateNextDate( $result['due_date']); //10-Aug-2017 12:00P
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Aug-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Aug-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Aug-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date']); //25-Aug-2017 12:00P
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Aug-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Aug-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date']); //10-Sep-2017 12:00P
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date']); //25-Sep-2017 12:00P
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Oct-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date']); //10-Oct-2017 12:00P
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Oct-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Oct-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date']); //25-Oct-2017 12:00P
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Oct-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Oct-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Nov-2017 12:00PM' ) ) );
	}

	function testSemiAnnual() {

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 2200 );

		$praef->setPrimaryMonth(1);
		$praef->setPrimaryDayOfMonth(10);
		$praef->setSecondaryMonth(6);
		$praef->setSecondaryDayOfMonth(15);
		$praef->setDueDateDelayDays(5);
		$praef->setEffectiveDate( strtotime( '01-Jan-2015' ) );

		$result = $praef->calculateNextDate( strtotime( '01-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jun-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Jan-2018  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Jan-2018 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '01-Mar-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '11-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jun-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '20-Jun-2017 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '05-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jun-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Jan-2017 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '05-Dec-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jun-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Jan-2018  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Jan-2018 12:00PM' ) ) );


		$result = $praef->calculateNextDate( strtotime( '09-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jun-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '10-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '11-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jun-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '20-Jun-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '11-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '11-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jun-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '20-Jun-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '12-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '11-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jun-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '20-Jun-2017 12:00PM' ) ) );

		//chained tests (like wizard)

		$result = $praef->calculateNextDate( $result['due_date'] );//20-Jun-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jun-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Jan-2018  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Jan-2018 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//15-Jan-2018 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '11-Jan-2018  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jun-2018  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '20-Jun-2018 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//20-Jun-2018 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jun-2018  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Jan-2019  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Jan-2019 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//15-Jan-2019 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '11-Jan-2019  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jun-2019  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '20-Jun-2019 12:00PM' ) ) );
	}

	function testYearToDate() {
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 2100 );

		$praef->setPrimaryMonth(6); //Jun
		$praef->setPrimaryDayOfMonth(20);
		$praef->setDueDateDelayDays(5);

		$result = $praef->calculateNextDate( strtotime( '01-Feb-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jun-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2017 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '19-Jun-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jun-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '19-Jun-2017 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jun-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2017 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '20-Jun-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jun-2018  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2018 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '21-Jun-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jun-2018  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2018 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '22-Jun-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jun-2018  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2018 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '23-Jun-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jun-2018  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2018 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '24-Jun-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jun-2018  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2018 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '25-Jun-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jun-2018  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2018 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '26-Jun-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jun-2018  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2018 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '01-Aug-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jun-2018  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2018 12:00PM' ) ) );


		//chained testing (like wizard)
		$result = $praef->calculateNextDate( $result['due_date'] ); //25-Jun-2018 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2019  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jun-2019  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2019 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //25-Jun-2019 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2020  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jun-2020  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2020 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //25-Jun-2020 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2021  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jun-2021  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2021 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //25-Jun-2021 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2022  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jun-2022  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2022 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] ); //25-Jun-2022 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2023  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jun-2023  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2023 12:00PM' ) ) );
	}

	function testYearToDateB() {
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 2100 );

		$praef->setPrimaryMonth(12); //Dec
		$praef->setPrimaryDayOfMonth(1);
		$praef->setDueDateDelayDays(0);

		$result = $praef->calculateNextDate( strtotime( '30-Nov-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '01-Dec-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Dec-2017 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '30-Nov-2017 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '01-Dec-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Dec-2017 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '01-Dec-2017' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '01-Dec-2018  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Dec-2018 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '01-Dec-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '01-Dec-2018  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Dec-2018 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '01-Dec-2017 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '01-Dec-2018  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Dec-2018 12:00PM' ) ) );
	}


	function testEighthMonthly() {
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 63000 );

		$result = $praef->calculateNextDate( strtotime( '01-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '06-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '02-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '06-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '03-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '06-Jan-2017 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '04-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '05-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '06-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '07-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jan-2017 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '08-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '11-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '14-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '09-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '11-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '14-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '10-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '11-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '14-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '11-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '11-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '14-Jan-2017 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '12-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '12-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '18-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '15-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '12-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '18-Jan-2017 12:00PM' ) ) );


		$result = $praef->calculateNextDate( strtotime( '16-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '19-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '22-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '19-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '19-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '22-Jan-2017 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '20-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '20-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '22-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '22-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '20-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '22-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jan-2017 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '23-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '25-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '28-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '25-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '25-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '28-Jan-2017 12:00PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '26-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '26-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( strtotime( '25-Jan-2017  11:59:59PM' ) ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Feb-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '27-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '26-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( strtotime( '25-Jan-2017  11:59:59PM' ) ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Feb-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( TTDate::getEndMonthEpoch( strtotime( '25-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '26-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( strtotime( '25-Jan-2017  11:59:59PM' ) ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Feb-2017 12:00PM' ) ) );


		//chained tests (like wizard)
		$result = $praef->calculateNextDate( strtotime( '25-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '25-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '28-Jan-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//28-Jan-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '26-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Feb-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//03-Feb-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Feb-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Feb-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '06-Feb-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//06-Feb-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Feb-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Feb-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//10-Feb-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Feb-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '11-Feb-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '14-Feb-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//14-Feb-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '12-Feb-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Feb-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '18-Feb-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//18-Feb-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Feb-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '19-Feb-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '22-Feb-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//22-Feb-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '20-Feb-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '22-Feb-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Feb-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//25-Feb-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Feb-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '25-Feb-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '28-Feb-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//28-Feb-2017 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '26-Feb-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '28-Feb-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Mar-2017 12:00PM' ) ) );


	}

	function testTwiceMonthly() {
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 61000 );

		//increase the upper bound to test a wider range.
		$testrange = 5;
		//test edges across 5 years
		for( $year = (date('Y') - $testrange); $year <= ( date('Y') + $testrange ); $year++ ) {
			//January
			$result = $praef->calculateNextDate( strtotime( '01-Jan--' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-' . $year . ' 11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '02-Jan-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-' . $year . ' 11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '15-Jan-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-' . $year . ' 11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '30-Jan-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-' . $year . ' 11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '31-Jan-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-' . $year . ' 11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-' . $year . '12:00PM' ) ) );

			//Feb
			$result = $praef->calculateNextDate( strtotime( '01-Feb-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Feb-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Feb-' . $year . ' 11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Feb-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '02-Feb-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Feb-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Feb-' . $year . ' 11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Feb-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '14-Feb-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Feb-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Feb-' . $year . ' 11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Feb-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '15-Feb-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Feb-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Feb-' . $year . ' 11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Feb-' . $year . '12:00PM' ) ) );

			$result = $praef->calculateNextDate( strtotime( '16-Feb-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Feb-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Mar-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '17-Feb-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Feb-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Mar-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '27-Feb-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Feb-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Mar-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '28-Feb-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Feb-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Mar-' . $year . '12:00PM' ) ) );

			//Jul
			$result = $praef->calculateNextDate( strtotime( '01-Jul-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-' . $year . ' 11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '02-Jul-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-' . $year . ' 11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '14-Jul-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-' . $year . ' 11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '15-Jul-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-' . $year . ' 11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-' . $year . '12:00PM' ) ) );

			$result = $praef->calculateNextDate( strtotime( '16-Jul-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '17-Jul-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '30-Jul-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '31-Jul-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-' . $year . '12:00PM' ) ) );

			//Nov
			$result = $praef->calculateNextDate( strtotime( '01-Nov-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Nov-' . $year . ' 11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Nov-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '02-Nov-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Nov-' . $year . ' 11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Nov-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '14-Nov-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Nov-' . $year . ' 11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Nov-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '15-Nov-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Nov-' . $year . ' 11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Nov-' . $year . '12:00PM' ) ) );

			$result = $praef->calculateNextDate( strtotime( '16-Nov-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Nov-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Dec-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '17-Nov-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Nov-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Dec-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '29-Nov-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Nov-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Dec-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '30-Nov-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Nov-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Dec-' . $year . '12:00PM' ) ) );

			//Dec
			$result = $praef->calculateNextDate( strtotime( '01-Dec-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Dec-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Dec-' . $year . ' 11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '26-Dec-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '02-Dec-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Dec-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Dec-' . $year . ' 11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '26-Dec-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '14-Dec-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Dec-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Dec-' . $year . ' 11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '26-Dec-' . $year . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '15-Dec-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Dec-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Dec-' . $year . ' 11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '26-Dec-' . $year . '12:00PM' ) ) );

			$result = $praef->calculateNextDate( strtotime( '16-Dec-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Dec-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jan-' . ($year + 1) .'12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '17-Dec-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Dec-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jan-' . ($year + 1) .'12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '30-Dec-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Dec-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jan-' . ($year + 1) .'12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '31-Dec-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Dec-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jan-' . ($year + 1) .'12:00PM' ) ) );

			//next jan
			$result = $praef->calculateNextDate( strtotime( '01-Jan-' . ($year + 1 ) . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-' . ($year + 1 ) . '12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-' . ($year + 1 ) . '11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-' . ($year + 1 ) . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '02-Jan-' . ($year + 1 ) . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-' . ($year + 1 ) . '12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-' . ($year + 1 ) . '11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-' . ($year + 1 ) . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '15-Jan-' . ($year + 1 ) . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-' . ($year + 1 ) . '12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-' . ($year + 1 ) . '11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-' . ($year + 1 ) . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '30-Jan-' . ($year + 1 ) . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-' . ($year + 1 ) . '12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-' . ($year + 1 ) . '11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-' . ($year + 1 ) . '12:00PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '31-Jan-' . ($year + 1 ) . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-' . ($year + 1 ) . '12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-' . ($year + 1 ) . '11:59:59PM' ) ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-' . ($year + 1 ) . '12:00PM' ) ) );
		}

		//chained  tests (like wizard)
		$result = $praef->calculateNextDate( strtotime( '31-Jan-2016 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2016 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2016 11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//10-Feb-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Feb-2016 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Feb-2016 11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Feb-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//25-Feb-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Feb-2016 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '29-Feb-2016 11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Mar-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//10-Mar-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Mar-2016 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Mar-2016 11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Mar-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//25-Mar-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Mar-2016 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2016 11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Apr-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//10-Apr-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2016 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Apr-2016 11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Apr-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//25-Apr-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Apr-2016 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Apr-2016 11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-May-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//10-May-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-May-2016 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-May-2016 11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-May-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//25-May-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-May-2016 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-May-2016 11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jun-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//10-Jun-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jun-2016 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jun-2016 11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2016 12:00PM' ) ) );
		$result = $praef->calculateNextDate( $result['due_date'] );//25-Jun-2016 12:00PM
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jun-2016 12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Jun-2016 11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jul-2016 12:00PM' ) ) );
	}

	function setupPayPeriodTest( $seed_date ) {
		$pay_period_schedule_id = $this->createPayPeriodSchedule();

		$this->assertEquals( TRUE, TTUUID::isUUID($pay_period_schedule_id), '$pay_period_schedule_id is not a uuid ');

		$psa_id = $this->createPayStubAccounts();

		$cdf = new CompanyDeductionFactory();
		$cdf->setPayrollRemittanceAgency($this->agency_id);
		$cdf->setCompany($this->company_id);
		$cdf->setCountry('CA');
		$cdf->setProvince('BC');
		$cdf->setLegalEntity($this->legal_entity_id);
		$cdf->setStatus(10);
		$cdf->setType(10);
		$cdf->setName('Auto-generated Company Deduction');
		$cdf->setCalculation(15);
		$cdf->setCalculationOrder(186);
		$cdf->setPayStubEntryAccount($psa_id);
		$cd_id = $cdf->save( FALSE);

		$udf = new UserDeductionFactory();
		$udf->setUser($this->user_id);
		$udf->setCompanyDeduction($cd_id);
		$udf->save();

		$this->createPayPeriods($seed_date, $pay_period_schedule_id);
		$this->getAllPayPeriods($pay_period_schedule_id);

		//return the payperiodschedule object so that we can call $ppsf->createNextPayPeriod( $date ) in our tests;
		return $pay_period_schedule_id;
	}

	function testPayPeriodA() {
		$test_start_date = strtotime( '01-Jan-2017 12:00PM' ); //should be day before first pay perdio start date.
		$ppsf_id = $this->setupPayPeriodTest($test_start_date);

		$this->assertEquals( TRUE, TTUUID::isUUID($ppsf_id), 'Pay period schedule must be an object.' );
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setId( $praef->getNextInsertId() );
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setStatus( 10 );
		$praef->setType( 'T4SD' );
		$praef->setFrequency( 1000 );
		$praef->setDueDateDelayDays( 0 );
		$praef->setReminderDays( 3 );
		$praef->setPayPeriodSchedule( array( $ppsf_id ) );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE );
		}


		$test_start_date = strtotime( '02-Jan-2017 12:00PM' ); //Should be after the start date of the first pay period to mimic a transaction date.

		$pp_obj = $this->pay_period_objs[0];


		$test_date = $test_start_date;

		Debug::text( 'Loop Test...', __FILE__, __LINE__, __METHOD__, 10 );
		//testing every day in every pay period:
		$result = $praef->calculateNextDate( $test_date );
		$this->assertEquals( TRUE, is_array( $result ), '$result should be an array.' );
		$this->assertEquals(4, count( $result ), '$result should have 3 elements.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', ( $pp_obj->getTransactionDate() + ( $praef->getDueDateDelayDays() * 86400 ) ) ), 'Due date Matches.' );

		$loop_counter = 1;
		while ( $test_date <= $pp_obj->getEndDate() AND $test_date < strtotime('01-Jan-2020') ) {
			$test_date = TTDate::incrementDate( $test_date, 1, 'day' );
			$result = $praef->calculateNextDate( $test_date );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', ( $pp_obj->getTransactionDate() + ( $praef->getDueDateDelayDays() * 86400 ) ) ), 'Due date Matches.' );
			$loop_counter++;
		}


		//testing with static values:
		Debug::text( 'Static Test...', __FILE__, __LINE__, __METHOD__, 10 );
		$praef->setDueDateDelayDays( 0 );
		$pp_obj = $this->pay_period_objs[0];
		$result = $praef->calculateNextDate( $pp_obj->getTransactionDate() );
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 4, count( $result ), '$result should have 3 elements.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '21-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Feb-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$pp_obj = $this->pay_period_objs[1];
		$praef->setDueDateDelayDays( 3 );
		$result = $praef->calculateNextDate( $pp_obj->getTransactionDate() );
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 4, count( $result ), '$result should have 3 elements.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Feb-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '17-Feb-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '20-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$pp_obj = $this->pay_period_objs[2];
		$praef->setDueDateDelayDays( 6 );
		$result = $praef->calculateNextDate( $pp_obj->getTransactionDate() );
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 4, count( $result ), '$result should have 3 elements.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '18-Feb-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Mar-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '09-Mar-2017 12:00:00' ) ), 'Due date Matches.' );
		$pp_obj = $this->pay_period_objs[3];
		$praef->setDueDateDelayDays( 9 );
		$result = $praef->calculateNextDate( $pp_obj->getTransactionDate() );
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 4, count( $result ), '$result should have 3 elements.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Mar-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '17-Mar-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '26-Mar-2017 12:00:00' ) ), 'Due date Matches.' );


		Debug::text( 'Wizard Test...', __FILE__, __LINE__, __METHOD__, 10 );
		$praef->setDueDateDelayDays( 0 );
		//chained tests (like wizard) also iterates through all 4 pay periods
		$pp_obj = $this->pay_period_objs[0];
		$result = $praef->calculateNextDate( $pp_obj->getTransactionDate() );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '21-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Feb-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$praef->setDueDateDelayDays( 3 );
		//Needs to use end_date, because due_date can be all over the map due to the due date delay.
		$result = $praef->calculateNextDate($result['end_date']); //03-Feb-2017 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Feb-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '17-Feb-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '20-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$praef->setDueDateDelayDays( 6 );
		$result = $praef->calculateNextDate($result['end_date']); //17-Feb-2017 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '18-Feb-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Mar-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '09-Mar-2017 12:00:00' ) ), 'Due date Matches.' );
		$praef->setDueDateDelayDays( 9 );
		$result = $praef->calculateNextDate($result['end_date']); //17-Mar-2017 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Mar-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '17-Mar-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '26-Mar-2017 12:00:00' ) ), 'Due date Matches.' );
	}

	function testPayPeriodB() {
		$test_start_date = strtotime( '01-Jan-2017 12:00PM' ); //should be day before first pay perdio start date.
		$ppsf_id = $this->setupPayPeriodTest($test_start_date);

		$this->assertEquals( TRUE, TTUUID::isUUID($ppsf_id), 'Pay period schedule must be an object.' );
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setId( $praef->getNextInsertId() );
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setStatus( 10 );
		$praef->setType( 'T4SD' );
		$praef->setFrequency( 1000 );
		$praef->setDueDateDelayDays( 1 );
		$praef->setReminderDays( 3 );
		$praef->setPayPeriodSchedule( array( $ppsf_id ) );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE );
		}


		$test_start_date = strtotime( '02-Jan-2017 12:00PM' ); //Should be after the start date of the first pay period to mimic a transaction date.

		$pp_obj = $this->pay_period_objs[0];


		$test_date = $test_start_date;

		Debug::text( 'Loop Test...', __FILE__, __LINE__, __METHOD__, 10 );
		//testing every day in every pay period:
		$result = $praef->calculateNextDate( $test_date );
		$this->assertEquals( TRUE, is_array( $result ), '$result should be an array.' );
		$this->assertEquals(4, count( $result ), '$result should have 3 elements.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', ( $pp_obj->getTransactionDate() + ( $praef->getDueDateDelayDays() * 86400 ) ) ), 'Due date Matches.' );

		$loop_counter = 1;
		while ( $test_date <= $pp_obj->getEndDate() AND $test_date < strtotime('01-Jan-2020') ) {
			$test_date = TTDate::incrementDate( $test_date, 1, 'day' );
			$result = $praef->calculateNextDate( $test_date );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', ( $pp_obj->getTransactionDate() + ( $praef->getDueDateDelayDays() * 86400 ) ) ), 'Due date Matches.' );
			$loop_counter++;
		}


		//testing with static values:
		Debug::text( 'Static Test...', __FILE__, __LINE__, __METHOD__, 10 );
		$praef->setDueDateDelayDays( 0 );
		$pp_obj = $this->pay_period_objs[0];
		$result = $praef->calculateNextDate( $pp_obj->getTransactionDate() );
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 4, count( $result ), '$result should have 3 elements.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '21-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Feb-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$pp_obj = $this->pay_period_objs[1];
		$praef->setDueDateDelayDays( 3 );
		$result = $praef->calculateNextDate( $pp_obj->getTransactionDate() );
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 4, count( $result ), '$result should have 3 elements.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Feb-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '17-Feb-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '20-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$pp_obj = $this->pay_period_objs[2];
		$praef->setDueDateDelayDays( 6 );
		$result = $praef->calculateNextDate( $pp_obj->getTransactionDate() );
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 4, count( $result ), '$result should have 3 elements.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '18-Feb-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Mar-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '09-Mar-2017 12:00:00' ) ), 'Due date Matches.' );
		$pp_obj = $this->pay_period_objs[3];
		$praef->setDueDateDelayDays( 9 );
		$result = $praef->calculateNextDate( $pp_obj->getTransactionDate() );
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 4, count( $result ), '$result should have 3 elements.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Mar-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '17-Mar-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '26-Mar-2017 12:00:00' ) ), 'Due date Matches.' );


		Debug::text( 'Wizard Test...', __FILE__, __LINE__, __METHOD__, 10 );
		$praef->setDueDateDelayDays( 0 );
		//chained tests (like wizard) also iterates through all 4 pay periods
		$pp_obj = $this->pay_period_objs[0];
		$result = $praef->calculateNextDate( $pp_obj->getTransactionDate() );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '21-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Feb-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$praef->setDueDateDelayDays( 3 );
		//Needs to use end_date, because due_date can be all over the map due to the due date delay.
		$result = $praef->calculateNextDate($result['end_date']); //03-Feb-2017 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Feb-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '17-Feb-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '20-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$praef->setDueDateDelayDays( 6 );
		$result = $praef->calculateNextDate($result['end_date']); //17-Feb-2017 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '18-Feb-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Mar-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '09-Mar-2017 12:00:00' ) ), 'Due date Matches.' );
		$praef->setDueDateDelayDays( 9 );
		$result = $praef->calculateNextDate($result['end_date']); //17-Mar-2017 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Mar-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '17-Mar-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '26-Mar-2017 12:00:00' ) ), 'Due date Matches.' );
	}

	function testPayPeriodC() {
		$test_start_date = strtotime( '01-Jan-2017 12:00PM' ); //should be day before first pay perdio start date.
		$ppsf_id = $this->setupPayPeriodTest($test_start_date);

		$this->assertEquals( TRUE, TTUUID::isUUID($ppsf_id), 'Pay period schedule must be an object.' );
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setId( $praef->getNextInsertId() );
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setStatus( 10 );
		$praef->setType( 'T4SD' );
		$praef->setFrequency( 1000 );
		$praef->setDueDateDelayDays( 1 );
		$praef->setReminderDays( 3 );
		$praef->setPayPeriodSchedule( array( $ppsf_id ) );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE );
		}


		$test_start_date = strtotime( '02-Jan-2017 12:00PM' ); //Should be after the start date of the first pay period to mimic a transaction date.

		$praef->setLastDueDate( $test_start_date );

		Debug::text( 'Static Test...', __FILE__, __LINE__, __METHOD__, 10 );

		$praef->setEnableRecalculateDates( TRUE );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE );
		}
		$this->assertEquals( date( 'r', $praef->getStartDate() ), date( 'r', strtotime( '03-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $praef->getEndDate() ), date( 'r', strtotime( '20-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $praef->getDueDate() ), date( 'r', strtotime( '21-Jan-2017 12:00:00' ) ), 'Due date Matches.' );
		$praef->setLastDueDate( $praef->getDueDate() );

		$praef->setEnableRecalculateDates( TRUE );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE );
		}
		$this->assertEquals( date( 'r', $praef->getStartDate() ), date( 'r', strtotime( '21-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $praef->getEndDate() ), date( 'r', strtotime( '03-Feb-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $praef->getDueDate() ), date( 'r', strtotime( '04-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$praef->setLastDueDate( $praef->getDueDate() );


		$praef->setEnableRecalculateDates( TRUE );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE );
		}
		$this->assertEquals( date( 'r', $praef->getStartDate() ), date( 'r', strtotime( '04-Feb-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $praef->getEndDate() ), date( 'r', strtotime( '17-Feb-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $praef->getDueDate() ), date( 'r', strtotime( '18-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$praef->setLastDueDate( $praef->getDueDate() );


		$praef->setEnableRecalculateDates( TRUE );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE );
		}
		$this->assertEquals( date( 'r', $praef->getStartDate() ), date( 'r', strtotime( '18-Feb-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $praef->getEndDate() ), date( 'r', strtotime( '03-Mar-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $praef->getDueDate() ), date( 'r', strtotime( '04-Mar-2017 12:00:00' ) ), 'Due date Matches.' );
		$praef->setLastDueDate( $praef->getDueDate() );
	}

	function testMonthlyQuarterExceptions() {
		//US - Monthly (Quarter Exceptions)
		//Due the 15th day of the month following the monthly withholding period, except for March, June, September and December; then due the last day of the month following the withholding period.
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 60000 );

		$result = $praef->calculateNextDate( strtotime( '01-Jan-2017 12:00PM' ) );

		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 3, count( $result ), '$result should have 3 elements.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime('02-Jan-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime('16-Jan-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime('30-Jan-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime('31-Jan-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime('01-Feb-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Feb-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '28-Feb-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Mar-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime('28-Feb-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Feb-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '28-Feb-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Mar-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime('01-Mar-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Mar-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime('31-Mar-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Mar-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime('01-Apr-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Apr-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-May-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime('30-Apr-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Apr-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-May-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime('01-May-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-May-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-May-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Jun-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime('31-May-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-May-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-May-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Jun-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime('01-Jun-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jun-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Jun-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime('30-Jun-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jun-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Jun-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime('01-Jul-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Aug-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime('31-Jul-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Aug-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime('01-Aug-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Aug-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Aug-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Sep-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime('31-Aug-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Aug-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Aug-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Sep-2017 12:00:00' ) ), 'Due date Matches.' );

		$test_start_date = strtotime('01-Sep-2017 12:00PM');
		$result = $praef->calculateNextDate( $test_start_date );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Sep-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Oct-2017 12:00:00' ) ), 'Due date Matches.' );
		$test_start_date = strtotime('30-Sep-2017 12:00PM');
		$result = $praef->calculateNextDate( $test_start_date );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Sep-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Oct-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime('01-Oct-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Oct-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Nov-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime('31-Oct-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Oct-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Nov-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime('01-Nov-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Nov-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Dec-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime('30-Nov-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Nov-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Dec-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime('01-Dec-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Dec-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jan-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime('31-Dec-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Dec-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jan-2018 12:00:00' ) ), 'Due date Matches.' );

		//chained testing (like wizard)
		$result = $praef->calculateNextDate( strtotime('01-Dec-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Dec-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jan-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( $result['due_date'] );//31-Jan-2018 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( $result['due_date'] );//15-Feb-2018 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Feb-2018' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '28-Feb-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Mar-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( $result['due_date'] );//31-Mar-2018 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Mar-2018' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( $result['due_date'] );//30-Apr-2018 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2018' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Apr-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-May-2018 12:00:00' ) ), 'Due date Matches.' );
	}

	function testQuarterMonthly() {
		//quarter-monthly
		// 	1.The first seven days of the calendar month.
		//	2.The 8th to the 15th day of the calendar month.
		// 	3.The 16th to the 22nd day of the calendar month.
		// 	4.The 23rd day to the end of the calendar month.
		//As a quarter-monthly filer, you are required to pay at least 90 percent of the actual tax due within three banking day following the end
		//of the quarter-monthly period.

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 62000 );

		$result = $praef->calculateNextDate( strtotime( '01-Jan-2017 12:00PM' ) );

		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 3, count( $result ), '$result should have 3 elements.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jan-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '07-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jan-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '08-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '18-Jan-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '11-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '18-Jan-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '15-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '18-Jan-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '16-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '22-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jan-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '18-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '22-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jan-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '22-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '22-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jan-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '23-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '28-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Feb-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '23-Feb-2018 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Feb-2018' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '28-Feb-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Mar-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '28-Feb-2018 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Feb-2018' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '28-Feb-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Mar-2018 12:00:00' ) ), 'Due date Matches.' );


		//chained test(like wizard)
		$result = $praef->calculateNextDate( strtotime( '28-Feb-2018 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Feb-2018' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '28-Feb-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Mar-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( $result['due_date'] );//03-Mar-2018 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Mar-2018 00:00:00' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Mar-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Mar-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( $result['due_date'] );//10-Mar-2018 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Mar-2018 00:00:00' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Mar-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '18-Mar-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( $result['due_date'] );//18-Mar-2018 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Mar-2018 00:00:00' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '22-Mar-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Mar-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( $result['due_date'] );//25-Mar-2018 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Mar-2018 00:00:00' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Apr-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( $result['due_date'] );//03-Apr-2018 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2018 00:00:00' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Apr-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Apr-2018 12:00:00' ) ), 'Due date Matches.' );
	}

	function testUSQuarterly() {
		// (April 30, July 31, and October 31).
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 59000 );

		$result = $praef->calculateNextDate( strtotime( '01-Jan-2017 12:00PM' ) );

		//sanity check
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 3, count( $result ), '$result should have 3 elements.' );

		//Q1
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '15-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '15-Mar-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-Mar-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2017 12:00:00' ) ), 'Due date Matches.' );

		//Q2.
		$result = $praef->calculateNextDate( strtotime( '01-Apr-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Jun-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '15-Apr-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Jun-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '15-Jun-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Jun-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '30-Jun-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Jun-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2017 12:00:00' ) ), 'Due date Matches.' );

		//Q3.
		$result = $praef->calculateNextDate( strtotime( '01-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Oct-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '15-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Oct-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '15-Sep-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Oct-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '30-Sep-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Oct-2017 12:00:00' ) ), 'Due date Matches.' );

		//Q4
		$result = $praef->calculateNextDate( strtotime( '01-Oct-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '15-Oct-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2018 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '15-Dec-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-Dec-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2018 12:00:00' ) ), 'Due date Matches.' );

		//chained tests  (like wizard)
		$result = $praef->calculateNextDate( $result['due_date'] );//30-Apr-2018 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2018' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Jun-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( $result['due_date'] );//31-Jul-2018 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2018' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Oct-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( $result['due_date'] );//31-Oct-2018 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2019' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2019 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2019 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( $result['due_date'] );//30-Apr-2018 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2019' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Jun-2019 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2019 12:00:00' ) ), 'Due date Matches.' );
	}


	function testUSMonthlyExcludeLastMOQ() {
		// (April 30, July 31, and October 31).
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 60100 );

		$result = $praef->calculateNextDate( strtotime( '01-Jan-2017 12:00PM' ) );

		//sanity check
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 3, count( $result ), '$result should have 3 elements.' );

		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '02-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '16-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '30-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '01-Feb-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Feb-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '28-Feb-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Mar-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '28-Feb-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Feb-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '28-Feb-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Mar-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '01-Mar-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Apr-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-May-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '30-Apr-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Apr-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-May-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '01-Apr-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Apr-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-May-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '30-Apr-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Apr-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-May-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '01-May-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-May-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-May-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Jun-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-May-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-May-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-May-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Jun-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '01-Jun-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Aug-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Aug-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '01-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Aug-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Aug-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '01-Aug-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Aug-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Aug-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Sep-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-Aug-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Aug-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Aug-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Sep-2017 12:00:00' ) ), 'Due date Matches.' );

		$test_start_date = strtotime( '01-Sep-2017 12:00PM' );
		$result = $praef->calculateNextDate( $test_start_date );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Oct-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Nov-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-Oct-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Oct-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Nov-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '01-Oct-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Oct-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Nov-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-Oct-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Oct-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Nov-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '01-Nov-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Nov-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Dec-2017 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '30-Nov-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Nov-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Dec-2017 12:00:00' ) ), 'Due date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '01-Dec-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-Dec-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2018 12:00:00' ) ), 'Due date Matches.' );

		//chained tests (like wizard)
		$result = $praef->calculateNextDate( $result['due_date'] );//15-Feb-2018 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Feb-2018' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '28-Feb-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Mar-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( $result['due_date'] );//15-March-2018 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2018' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Apr-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-May-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( $result['due_date'] );//15-May-2018 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-May-2018' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-May-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Jun-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( $result['due_date'] );//15-Jun-2018 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2018' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Aug-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( $result['due_date'] );//15-Aug-2018 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Aug-2018' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Aug-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Sep-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( $result['due_date'] );//15-Sep-2018 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2018' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Oct-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Nov-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( $result['due_date'] );//15-Nov-2018 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-2018' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Nov-2018 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Dec-2018 12:00:00' ) ), 'Due date Matches.' );
		$result = $praef->calculateNextDate( $result['due_date'] );//15-Dec-2018 12:00:00
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2019' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2019 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2019 12:00:00' ) ), 'Due date Matches.' );
	}

	function testOnHire() {
		global $dd;
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999 );
		Debug::text( 'User ID: ' . $this->user_id, __FILE__, __LINE__, __METHOD__, 10 );
		$user_obj = $this->getUserObject( $this->user_id );
		$user_obj->setHireDate( strtotime( '05-Mar-2016' ) );
		if ( $user_obj->isValid() ) {
			$user_obj->Save( FALSE );
		}

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setDueDateDelayDays( 10 );
		$praef->setStatus( 10 ); //enabled
		$praef->setType( 'T4' );
		$praef->setReminderDays( 0 );
		$praef->setFrequency( 90100 ); //On Hire Event Frequency
		$praef->setEffectiveDate( strtotime( '05-Mar-2016' ) );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE );
		}

		$result = $praef->calculateNextDate( NULL, strtotime( '05-Mar-2016' ) );
		Debug::Arr( $result, 'FIRST RESULT: ', __FILE__, __LINE__, __METHOD__, 10 );

		//sanity check
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 3, count( $result ), '$result should have 3 elements.' );

		$this->assertNotEmpty( $result['start_date'], '$result elements should not be empty.' );
		$this->assertNotEmpty( $result['end_date'], '$result elements should not be empty.' );
		$this->assertNotEmpty( $result['due_date'], '$result elements should not be empty.' );

		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '05-Mar-2016 00:00:00' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '14-Mar-2016 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Mar-2016 12:00:00' ) ), 'Due date Matches.' );


		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999 );
		Debug::text( 'User ID: ' . $this->user_id, __FILE__, __LINE__, __METHOD__, 10 );
		$user_obj = $this->getUserObject( $this->user_id );
		$user_obj->setHireDate( strtotime( '07-Mar-2016' ) );
		if ( $user_obj->isValid() ) {
			$user_obj->Save( FALSE );
		}

		$result = $praef->calculateNextDate( NULL, strtotime( '07-Mar-2016' ) );
		Debug::Arr( $result, 'SECOND RESULT: ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '05-Mar-2016 00:00:00' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '14-Mar-2016 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Mar-2016 12:00:00' ) ), 'Due date Matches.' );


		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999 );
		Debug::text( 'User ID: ' . $this->user_id, __FILE__, __LINE__, __METHOD__, 10 );
		$user_obj = $this->getUserObject( $this->user_id );
		$user_obj->setHireDate( strtotime( '20-Mar-2016' ) );
		if ( $user_obj->isValid() ) {
			$user_obj->Save( FALSE );
		}
		$result = $praef->calculateNextDate( NULL, strtotime( '20-Mar-2016' ) );
		Debug::Arr( $result, 'THIRD RESULT: ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '20-Mar-2016 00:00:00' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '29-Mar-2016 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Mar-2016 12:00:00' ) ), 'Due date Matches.' );
	}

	function testOnHireGaps() {
		global $dd;
		$now = TTDate::getMiddleDayEpoch( time() );
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		//ensure that we set the admin user's hire date to  some time in the past so it doesn't interfere with the following tests.
		$user_obj = $this->getUserObject( $this->user_id );
		$user_obj->setHireDate( TTDate::incrementDate( $now, -5, 'month' ) );
		if ( $user_obj->isValid() ) {
			$user_obj->Save( FALSE );
		}


		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999 );
		Debug::text( 'User ID: ' . $this->user_id, __FILE__, __LINE__, __METHOD__, 10 );
		$user_obj = $this->getUserObject( $this->user_id );
		$user_obj->setHireDate( TTDate::incrementDate( $now, -5, 'day' ) );
		Debug::Text( 'FIRST User Hire Date: ' . TTDate::getDate( 'DATE+TIME', $user_obj->getHireDate() ), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $user_obj->isValid() ) {
			$user_obj->Save( FALSE );
		}


		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setDueDateDelayDays( 10 );
		$praef->setStatus( 10 ); //enabled
		$praef->setType( 'T4' );
		$praef->setReminderDays( 0 );
		$praef->setFrequency( 90100 ); //On Hire Event Frequency
		$praef->setEffectiveDate( $now );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE );
		}
		$praef->getPayrollRemittanceAgencyObject()->setAlwaysOnWeekDay(0);
		$praef->getPayrollRemittanceAgencyObject()->save();


		$result = $praef->calculateNextDate(NULL, $now);
		//Debug::Arr( $result, 'FIRST RESULT: ', __FILE__, __LINE__, __METHOD__, 10 );

		//sanity check
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 3, count( $result ), '$result should have 3 elements.' );

		$this->assertNotEmpty( $result['start_date'], '$result elements should not be empty.' );
		$this->assertNotEmpty( $result['end_date'], '$result elements should not be empty.' );
		$this->assertNotEmpty( $result['due_date'], '$result elements should not be empty.' );

		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', TTDate::getBeginDayEpoch( TTDate::incrementDate( $now, -5, 'day' ) ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndDayEpoch( TTDate::incrementDate( $now, 4, 'day' ) ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', TTDate::getMiddleDayEpoch( TTDate::incrementDate( $result['end_date'], 1, 'day' ) ) ), 'Due date Matches.' );


		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999 );
		Debug::text( 'User ID: ' . $this->user_id, __FILE__, __LINE__, __METHOD__, 10 );
		$user_obj = $this->getUserObject( $this->user_id );
		$user_obj->setHireDate( $now );
		Debug::Text( 'SECOND User Hire Date: ' . TTDate::getDate( 'DATE+TIME', $user_obj->getHireDate() ), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $user_obj->isValid() ) {
			$user_obj->Save( FALSE );
		}

		$result = $praef->calculateNextDate( NULL, $now );
		//Debug::Arr( $result, 'SECOND RESULT: ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', TTDate::getBeginDayEpoch( TTDate::incrementDate( $now, -5, 'day' ) ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndDayEpoch( TTDate::incrementDate( $now, 4, 'day' ) ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', TTDate::getMiddleDayEpoch( TTDate::incrementDate( $now, 5, 'day' ) ) ), 'Due date Matches.' );


		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999 );
		Debug::Text( 'User ID: ' . $this->user_id, __FILE__, __LINE__, __METHOD__, 10 );
		$user_obj = $this->getUserObject( $this->user_id );
		$user_obj->setHireDate( TTDate::incrementDate( $now, 30, 'day' ) );
		Debug::Text( 'THIRD User Hire Date: ' . TTDate::getDate( 'DATE+TIME', $user_obj->getHireDate() ).' User ID: '.$this->user_id, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $user_obj->isValid() ) {
			$user_obj->Save( FALSE );
		}
		$result = $praef->calculateNextDate( NULL, $now );
		//Debug::Arr( $result, 'THIRD RESULT: ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', TTDate::getBeginDayEpoch( TTDate::incrementDate( $now, -5, 'day' ) ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndDayEpoch( TTDate::incrementDate( $now, 4, 'day' ) ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', TTDate::getMiddleDayEpoch( TTDate::incrementDate( $result['end_date'], 1, 'day' ) ) ), 'Due date Matches.' );


		//test gap. should return empty dates
		$praef->setEnableRecalculateDates( TRUE );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE );
		}
		$result = $praef->calculateNextDate( NULL, TTDate::incrementDate( $now, 20, 'day' ) );
		//Debug::Arr( $result, 'FOURTH RESULT: ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 3, count( $result ), '$result should have 3 elements.' );
		$this->assertEmpty( $result['start_date'], '$result elements should  be empty.' );
		$this->assertEmpty( $result['end_date'], '$result elements should  be empty.' );
		$this->assertEmpty( $result['due_date'], '$result elements should  be empty.' );


		$praef->setEnableRecalculateDates( TRUE );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE );
		}

		Debug::Text( 'Expected User ID: '.$this->user_id, __FILE__, __LINE__, __METHOD__, 10 );
		$result = $praef->calculateNextDate( NULL, TTDate::incrementDate( $now, 31, 'day' ) );
		//Debug::Arr( $result, 'FIFTH RESULT: ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 3, count( $result ), '$result should have 3 elements.' );
		$this->assertNotEmpty( $result['start_date'], '$result elements should not be empty.' );
		$this->assertNotEmpty( $result['end_date'], '$result elements should not be empty.' );
		$this->assertNotEmpty( $result['due_date'], '$result elements should not be empty.' );

		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', TTDate::getBeginDayEpoch( TTDate::incrementDate( $now, 30, 'day' ) ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndDayEpoch( TTDate::incrementDate( $now, 39, 'day' ) ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', TTDate::getMiddleDayEpoch( TTDate::incrementDate( $result['end_date'], 1, 'day' ) ) ), 'Due date Matches.' );
	}



	function testOnTermination() {
		global $dd;
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999 );
		Debug::text( 'User ID: ' . $this->user_id, __FILE__, __LINE__, __METHOD__, 10 );
		$user_obj = $this->getUserObject( $this->user_id );
		$user_obj->setTerminationDate( strtotime( '05-Mar-2016' ) );
		if ( $user_obj->isValid() ) {
			$user_obj->Save( FALSE );
		}

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setDueDateDelayDays( 10 );
		$praef->setStatus( 10 ); //enabled
		$praef->setType( 'T4' );
		$praef->setReminderDays( 0 );
		$praef->setFrequency( 90200 ); //On Hire Event Frequency
		$praef->setEffectiveDate( strtotime( '05-Mar-2016' ) );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE );
		}
		$praef->getPayrollRemittanceAgencyObject()->setAlwaysOnWeekDay(0);
		$praef->getPayrollRemittanceAgencyObject()->save();

		$result = $praef->calculateNextDate( NULL, strtotime( '05-Mar-2016' ) );
		Debug::Arr( $result, 'FIRST RESULT: ', __FILE__, __LINE__, __METHOD__, 10 );

		//sanity check
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 3, count( $result ), '$result should have 3 elements.' );

		$this->assertNotEmpty( $result['start_date'], '$result elements should not be empty.' );
		$this->assertNotEmpty( $result['end_date'], '$result elements should not be empty.' );
		$this->assertNotEmpty( $result['due_date'], '$result elements should not be empty.' );

		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '05-Mar-2016 00:00:00' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '14-Mar-2016 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Mar-2016 12:00:00' ) ), 'Due date Matches.' );


		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999 );
		Debug::text( 'User ID: ' . $this->user_id, __FILE__, __LINE__, __METHOD__, 10 );
		$user_obj = $this->getUserObject( $this->user_id );
		$user_obj->setTerminationDate( strtotime( '07-Mar-2016' ) );
		if ( $user_obj->isValid() ) {
			$user_obj->Save( FALSE );
		}

		$result = $praef->calculateNextDate( NULL, strtotime( '07-Mar-2016' ) );
		Debug::Arr( $result, 'SECOND RESULT: ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '05-Mar-2016 00:00:00' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '14-Mar-2016 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Mar-2016 12:00:00' ) ), 'Due date Matches.' );


		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999 );
		Debug::text( 'User ID: ' . $this->user_id, __FILE__, __LINE__, __METHOD__, 10 );
		$user_obj = $this->getUserObject( $this->user_id );
		$user_obj->setTerminationDate( strtotime( '20-Mar-2016' ) );
		if ( $user_obj->isValid() ) {
			$user_obj->Save( FALSE );
		}
		$result = $praef->calculateNextDate( NULL, strtotime( '20-Mar-2016' ) );
		Debug::Arr( $result, 'THIRD RESULT: ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '20-Mar-2016 00:00:00' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '29-Mar-2016 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Mar-2016 12:00:00' ) ), 'Due date Matches.' );
	}

	function testOnTerminationGaps() {
		global $dd;
		$now = TTDate::getMiddleDayEpoch( time() );
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999 );
		Debug::text( 'User ID: ' . $this->user_id, __FILE__, __LINE__, __METHOD__, 10 );
		$user_obj = $this->getUserObject( $this->user_id );
		$user_obj->setTerminationDate( TTDate::incrementDate( $now, -5, 'day' ) );
		Debug::Text( 'FIRST User Hire Date: ' . TTDate::getDate( 'DATE+TIME', $user_obj->getHireDate() ), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $user_obj->isValid() ) {
			$user_obj->Save( FALSE );
		}


		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setDueDateDelayDays( 10 );
		$praef->setStatus( 10 ); //enabled
		$praef->setType( 'T4' );
		$praef->setReminderDays( 0 );
		$praef->setFrequency( 90200 ); //On Hire Event Frequency
		$praef->setEffectiveDate( $now );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE );
		}

		$result = $praef->calculateNextDate();
		//Debug::Arr( $result, 'FIRST RESULT: ', __FILE__, __LINE__, __METHOD__, 10 );

		//sanity check
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 3, count( $result ), '$result should have 3 elements.' );

		$this->assertNotEmpty( $result['start_date'], '$result elements should not be empty.' );
		$this->assertNotEmpty( $result['end_date'], '$result elements should not be empty.' );
		$this->assertNotEmpty( $result['due_date'], '$result elements should not be empty.' );

		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', TTDate::getBeginDayEpoch( TTDate::incrementDate( $now, -5, 'day' ) ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndDayEpoch( TTDate::incrementDate( $now, 4, 'day' ) ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', TTDate::incrementDate( $now, 5, 'day' ) ), 'Due date Matches.' );


		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999 );
		Debug::text( 'User ID: ' . $this->user_id, __FILE__, __LINE__, __METHOD__, 10 );
		$user_obj = $this->getUserObject( $this->user_id );
		$user_obj->setTerminationDate( strtotime( $now ) );
		Debug::Text( 'SECOND User Hire Date: ' . TTDate::getDate( 'DATE+TIME', $user_obj->getHireDate() ), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $user_obj->isValid() ) {
			$user_obj->Save( FALSE );
		}

		$result = $praef->calculateNextDate();
		//Debug::Arr( $result, 'SECOND RESULT: ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', TTDate::getBeginDayEpoch( TTDate::incrementDate( $now, -5, 'day' ) ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndDayEpoch( TTDate::incrementDate( $now, 4, 'day' ) ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', TTDate::incrementDate( $now, 5, 'day' ) ), 'Due date Matches.' );


		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999 );
		Debug::Text( 'User ID: ' . $this->user_id, __FILE__, __LINE__, __METHOD__, 10 );
		$user_obj = $this->getUserObject( $this->user_id );
		$user_obj->setTerminationDate( TTDate::incrementDate( $now, 30, 'day' ) );
		Debug::Text( 'THIRD User Hire Date: ' . TTDate::getDate( 'DATE+TIME', $user_obj->getHireDate() ), __FILE__, __LINE__, __METHOD__, 10 );

		if ( $user_obj->isValid() ) {
			$user_obj->Save( FALSE );
		}
		$result = $praef->calculateNextDate();
		//Debug::Arr( $result, 'THIRD RESULT: ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', TTDate::getBeginDayEpoch( TTDate::incrementDate( $now, -5, 'day' ) ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndDayEpoch( TTDate::incrementDate( $now, 4, 'day' ) ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', TTDate::incrementDate( $now, 5, 'day' ) ), 'Due date Matches.' );


		//test gap. should return empty dates
		$praef->setEnableRecalculateDates( TRUE );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE );
		}
		$result = $praef->calculateNextDate( NULL, TTDate::incrementDate( $now, 20, 'day' ) );
		//Debug::Arr( $result, 'FOURTH RESULT: ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 3, count( $result ), '$result should have 3 elements.' );
		$this->assertEmpty( $result['start_date'], '$result elements should  be empty.' );
		$this->assertEmpty( $result['end_date'], '$result elements should  be empty.' );
		$this->assertEmpty( $result['due_date'], '$result elements should  be empty.' );


		$praef->setEnableRecalculateDates( TRUE );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE );
		}
		$result = $praef->calculateNextDate( NULL, TTDate::incrementDate( $now, 31, 'day' ) );
		//Debug::Arr( $result, 'FIFTH RESULT: ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 3, count( $result ), '$result should have 3 elements.' );
		$this->assertNotEmpty( $result['start_date'], '$result elements should not be empty.' );
		$this->assertNotEmpty( $result['end_date'], '$result elements should not be empty.' );
		$this->assertNotEmpty( $result['due_date'], '$result elements should not be empty.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', TTDate::getBeginDayEpoch( TTDate::incrementDate( $now, 30, 'day' ) ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndDayEpoch( TTDate::incrementDate( $now, 39, 'day' ) ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', TTDate::incrementDate( $now, 40, 'day' ) ), 'Due date Matches.' );
	}

	function testOnTerminationByPayPeriod() {
		global $dd;

		$test_start_date = strtotime( '01-Jan-2017 12:00PM' ); //should be day before first pay perdio start date.
		$ppsf_id = $this->setupPayPeriodTest($test_start_date);

		$this->assertEquals( TRUE, TTUUID::isUUID($ppsf_id), 'Pay period schedule id must be a UUID.' );

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 90310 ); //On Hire Event Frequency
		$praef->setDueDateDelayDays( 10 );
		$praef->setReminderDays( 3 );
		$praef->setType( 'T4' );
		$praef->setStatus( 10 ); //enabled
		$praef->setEffectiveDate( strtotime( '01-Jan-2017' ) );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE, TRUE );
		}

		//workaround for set requiring an object_id
		$praef->setPayPeriodSchedule( array($ppsf_id ) );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE, TRUE );
		}

		$user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999 );
		Debug::text( 'User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );
		$user_obj = $this->getUserObject( $user_id );
		$user_obj->setTerminationDate( strtotime( '01-Jan-2017' ) );
		if ( $user_obj->isValid() ) {
			$user_obj->Save();
		}
		$this->addUserToPayPeriodSchedule($ppsf_id, $user_id );

		$fake_time = strtotime( '01-Jan-2017' );
		$result = $praef->calculateNextDate( NULL, $fake_time );

		Debug::Arr( $result, 'FIRST RESULT: ', __FILE__, __LINE__, __METHOD__, 10 );

		//sanity check
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 3, count( $result ), '$result should have 3 elements.' );

		//failure.
		$this->assertNotEmpty( $result['start_date'], '$result elements should not be empty.' );
		$this->assertNotEmpty( $result['end_date'], '$result elements should not be empty.' );
		$this->assertNotEmpty( $result['due_date'], '$result elements should not be empty.' );

		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017 00:00:00' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '14-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '24-Jan-2017 12:00:00' ) ), 'Due date Matches.' );


		$user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999 );
		Debug::text( 'User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );
		$user_obj = $this->getUserObject( $user_id );
		$user_obj->setTerminationDate( strtotime( '10-Jan-2017' ) );
		if ( $user_obj->isValid() ) {
			$user_obj->Save();
		}
		$this->addUserToPayPeriodSchedule($ppsf_id, $user_id );

		$fake_time = strtotime( '01-Jan-2017' );
		$result = $praef->calculateNextDate( NULL, $fake_time );

		Debug::Arr( $result, 'SECOND RESULT: ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017 00:00:00' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '14-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '24-Jan-2017 12:00:00' ) ), 'Due date Matches.' );


		$user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999 );
		Debug::text( 'User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );
		$user_obj = $this->getUserObject( $user_id );
		$user_obj->setTerminationDate( strtotime( '10-Feb-2017' ) );
		if ( $user_obj->isValid() ) {
			$user_obj->Save();
		}
		$this->addUserToPayPeriodSchedule($ppsf_id, $user_id );

		$fake_time = strtotime( '10-Jan-2017' );
		$result = $praef->calculateNextDate( NULL, $fake_time );

		Debug::Arr( $result, 'SECOND RESULT: ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017 00:00:00' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '14-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '24-Jan-2017 12:00:00' ) ), 'Due date Matches.' );


		$praef->setEnableRecalculateDates( TRUE );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE );
		}

		$fake_time = strtotime( '25-Jan-2017' );
		$result = $praef->calculateNextDate( NULL, $fake_time );

		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 3, count( $result ), '$result should have 3 elements.' );
		$this->assertEmpty( $result['start_date'], '$result elements should  be empty.' );
		$this->assertEmpty( $result['end_date'], '$result elements should  be empty.' );
		$this->assertEmpty( $result['due_date'], '$result elements should  be empty.' );

		$fake_time = strtotime( '09-Feb-2017' );
		$result = $praef->calculateNextDate( NULL, $fake_time );
		//$result = $praef->calculateNextDate();

		Debug::Arr( $result, 'THIRD RESULT: ', __FILE__, __LINE__, __METHOD__, 10 );
		//LEFT OFF HERE
		$this->assertEquals( date( 'r', (int)$result['start_date'] ), date( 'r', strtotime( '29-Jan-2017 00:00:00' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', (int)$result['end_date'] ), date( 'r', strtotime( '11-Feb-2017 23:59:59' ) ), 'End date Matches.' );
		$this->assertEquals( date( 'r', (int)$result['due_date'] ), date( 'r', strtotime( '21-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
	}

	//testing that recalc is happening properly on save.
	function testPreSave() {
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 4100 ); //monthly
		$praef->setPrimaryDayOfMonth( 10 );
		$praef->setEffectiveDate( strtotime( '01-Dec-2016' ) );

		$praef->setDueDateDelayDays( 0 );
		$praef->setReminderDays( 3 );
		$praef->setType( 'T4' );
		$praef->setStatus( 10 ); //enabled

		if ( $praef->isValid() ) {
			$praef->Save( FALSE, TRUE );
		}

		$this->assertEquals( date( 'r', $praef->getStartDate() ), date( 'r', strtotime( '01-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $praef->getEndDate() ), date( 'r', strtotime( '30-Nov-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $praef->getDueDate() ), date( 'r', strtotime( '10-Dec-2016 12:00PM' ) ) );

		$praef->setEffectiveDate( strtotime( '01-Jan-2017' ) );
		$praef->setEnableRecalculateDates( TRUE );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE, TRUE );
		}

		$this->assertEquals( date( 'r', $praef->getStartDate() ), date( 'r', strtotime( '01-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $praef->getEndDate() ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $praef->getDueDate() ), date( 'r', strtotime( '10-Jan-2017 12:00PM' ) ) );

		$praef->setEffectiveDate( strtotime( '01-Feb-2017' ) );
		$praef->setEnableRecalculateDates( TRUE );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE, TRUE );
		}

		$this->assertEquals( date( 'r', $praef->getStartDate() ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $praef->getEndDate() ), date( 'r', strtotime( '31-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $praef->getDueDate() ), date( 'r', strtotime( '10-Feb-2017 12:00PM' ) ) );

		$praef->setEffectiveDate( strtotime( '01-Mar-2017' ) );
		$praef->setEnableRecalculateDates( TRUE );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE, TRUE );
		}

		$this->assertEquals( date( 'r', $praef->getStartDate() ), date( 'r', strtotime( '01-Feb-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $praef->getEndDate() ), date( 'r', strtotime( '28-Feb-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $praef->getDueDate() ), date( 'r', strtotime( '10-Mar-2017 12:00PM' ) ) );

		$praef->setEffectiveDate( strtotime( '01-Apr-2017' ) );
		$praef->setEnableRecalculateDates( TRUE );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE, TRUE );
		}

		$this->assertEquals( date( 'r', $praef->getStartDate() ), date( 'r', strtotime( '01-Mar-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $praef->getEndDate() ), date( 'r', strtotime( '31-Mar-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $praef->getDueDate() ), date( 'r', strtotime( '10-Apr-2017 12:00PM' ) ) );
	}

	function testReminderDays() {
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 4100 ); //monthly
		$praef->setPrimaryDayOfMonth( 10 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2016' ) );

		$praef->setDueDateDelayDays( 0 );
		$praef->setType( 'T4' );
		$praef->setStatus( 10 ); //enabled

		$praef->setReminderDays( 0 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2016' ) );
		$praef->setEnableRecalculateDates( TRUE );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE, TRUE );
		}
		$this->assertEquals( date( 'r', $praef->getNextReminderDate() ), date( 'r', strtotime( '10-Jan-2016 12:00PM' ) ) );

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 4100 ); //monthly
		$praef->setPrimaryDayOfMonth( 10 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2016' ) );

		$praef->setDueDateDelayDays( 0 );
		$praef->setType( 'T4' );
		$praef->setStatus( 10 ); //enabled

		$praef->setReminderDays( 3 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2016' ) );
		$praef->setEnableRecalculateDates( TRUE );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE, TRUE );
		}
		$this->assertEquals( date( 'r', $praef->getNextReminderDate() ), date( 'r', strtotime( '07-Jan-2016 12:00PM' ) ) );

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 4100 ); //monthly
		$praef->setPrimaryDayOfMonth( 10 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2016' ) );

		$praef->setDueDateDelayDays( 0 );
		$praef->setType( 'T4' );
		$praef->setStatus( 10 ); //enabled

		$praef->setReminderDays( 5 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2016' ) );
		$praef->setEnableRecalculateDates( TRUE );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE, TRUE );
		}
		$this->assertEquals( date( 'r', $praef->getNextReminderDate() ), date( 'r', strtotime( '05-Jan-2016 12:00PM' ) ) );

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 4100 ); //monthly
		$praef->setPrimaryDayOfMonth( 10 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2016' ) );

		$praef->setDueDateDelayDays( 0 );
		$praef->setType( 'T4' );
		$praef->setStatus( 10 ); //enabled

		$praef->setReminderDays( -5 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2016' ) );
		$praef->setEnableRecalculateDates( TRUE );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE, TRUE );
		}
		$this->assertEquals( date( 'r', $praef->getNextReminderDate(TRUE) ), date( 'r', strtotime( '15-Jan-2016 12:00PM' ) ) );

		//Make sure you test the case where this is no due date, and therefore no reminder date.
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 4100 ); //monthly
		$praef->setPrimaryDayOfMonth( 10 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2016' ) );

		$praef->setDueDateDelayDays( 0 );
		$praef->setType( 'T4' );
		$praef->setStatus( 10 ); //enabled

		$praef->setReminderDays( -5 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2016' ) );

		//not saved. no due date means no reminder date.
		$this->assertEquals( $praef->getNextReminderDate(TRUE), FALSE );


		//Make sure you test the case where this is no due date, and therefore no reminder date.
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' ); /** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 1000 ); //each pay period (with no pay period set)
		$praef->setDueDateDelayDays( 0 );
		$praef->setType( 'T4' );
		$praef->setStatus( 10 ); //enabled
		$praef->setReminderDays( -5 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2016' ) );

		//not saved. no due date means no reminder date.
		$this->assertEquals( $praef->getNextReminderDate(TRUE), FALSE );
		$praef->setEnableRecalculateDates( TRUE );

		if ( $praef->isValid() ) {
			$praef->Save( FALSE, TRUE );
		}
		//saved. should not be able to calculate a due date because no payperiods exist.
		$this->assertEquals( $praef->getNextReminderDate(TRUE), FALSE );
	}
}

?>