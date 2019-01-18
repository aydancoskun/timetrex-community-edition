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

		//only needed to log in witgh UI
		//FIXME: comment this before commit.
		$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$currency_id = $dd->createCurrency( $this->company_id, 10 );
		$dd->createUserWageGroups( $this->company_id );

		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 100 );
		Debug::text( 'User ID: ' . $this->user_id, __FILE__, __LINE__, __METHOD__, 10 );
		$user_obj = $this->getUserObject( $this->user_id );
		//Use a consistent hire date, otherwise its difficult to get things correct due to the hire date being in different parts or different pay periods.
		//Make sure it is not on a pay period start date though.
		$user_obj->setHireDate( strtotime( '05-Mar-2018' ) );
		$user_obj->Save( FALSE );

		/** @var RemittanceSourceAccountFactory $rsa_obj */
		$rsa_obj = TTnew( 'RemittanceSourceAccountFactory' );
		$rsa_obj->setName( 'Test source account' );
		$rsa_obj->setLegalEntity( $this->legal_entity_id );
		$rsa_obj->setStatus( 10 );
		$rsa_obj->setType( 2000 );
		$rsa_obj->setCountry( 'US' );
		$rsa_obj->setDataFormat( 10 );
		$rsa_obj->setLastTransactionNumber( 111 );
		$rsa_obj->setCurrency( $currency_id );
		$rsa_id = $rsa_obj->Save();

		/** @var PayrollRemittanceAgencyFactory $praf */
		$praf = TTnew( 'PayrollRemittanceAgencyFactory' );

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
		$ulf = TTNew( 'UserListFactory' );
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
		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setDayOfWeek( 0 );
		$praef->setFrequency( 5100 );

		//time edge
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:01AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:00AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '04-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '11-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Dec-2016  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '04-Dec-2016 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '11-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Dec-2016  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '04-Dec-2016 12:00AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '11-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Dec-2016  11:59:59PM' ) ) );

		//reverse day of week edge
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:00AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '02-Dec-2016 12:00AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '03-Dec-2016 12:00AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '31-Nov-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '30-Nov-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '29-Nov-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '28-Nov-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '27-Nov-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '27-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Dec-2016  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '26-Nov-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '27-Nov-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '20-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '26-Nov-2016  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '25-Nov-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '27-Nov-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '20-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '26-Nov-2016  11:59:59PM' ) ) );

		//forward day of week edge

		$result = $praef->calculateNextDate( strtotime( '09-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '11-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Dec-2016  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '10-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '11-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Dec-2016  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '11-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '18-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '11-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '17-Dec-2016  11:59:59PM' ) ) );

		//year edge
		$result = $praef->calculateNextDate( strtotime( '24-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '18-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '24-Dec-2016  11:59:59PM' ) ) );
		//$this->assertEquals( $praef->calculateNextDate( strtotime('24-Dec-2016 11:59PM') ), strtotime('25-Dec-2016 12:00PM'));

		$result = $praef->calculateNextDate( strtotime( '25-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '25-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '26-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '25-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '27-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '25-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '28-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '25-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '29-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '25-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '30-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '25-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '31-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '25-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '01-Jan-2017 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '08-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '02-Jan-2017 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '08-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '03-Jan-2017 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '08-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '04-Jan-2017 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '08-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '05-Jan-2017 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '08-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017  11:59:59PM' ) ) );

		//daylight savings
		$result = $praef->calculateNextDate( strtotime( '12-Mar-2017 01:59AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '19-Mar-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '12-Mar-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '18-Mar-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '12-Mar-2017 02:01AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '19-Mar-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '12-Mar-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '18-Mar-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '12-Mar-2017 11:59AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '19-Mar-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '12-Mar-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '18-Mar-2017  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '05-Nov-2017 01:59AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Nov-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '05-Nov-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '11-Nov-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '05-Nov-2017 02:01AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Nov-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '05-Nov-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '11-Nov-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '05-Nov-2017 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Nov-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '05-Nov-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '11-Nov-2017  11:59:59PM' ) ) );
	}

//	function testLoopWeekly() {
//		/** @var PayrollRemittanceAgencyEventFactory $praef */
//		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
//		$day_of_wk = 0;
//		$praef->setDayOfWeek( 0 );
//		$praef->setFrequency( 5100 );
//
//		$epoch = TTDate::getMiddleDayEpoch( TTDate::getDateOfNextDayOfWeek( TTDate::getBeginYearEpoch( time() ), TTDate::getBeginWeekEpoch( TTDate::getBeginYearEpoch( time() ), $day_of_wk ) ) );
//
//		for ( $i = 0; $i < 104; $i++ ) {
//			Debug::Text( 'testLoopWeekly: ' . TTDate::getDate( 'DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );
//
//			//every 4 weeks, increment the day of week so they all get tested.
//			if ( ( $i % 4 ) === 0 AND $praef->getDayOfWeek() <= 5 ) {
//				$praef->setDayOfWeek( $praef->getDayOfWeek() + 1 );
//			} elseif ( ( $i % 4 ) === 0 ) {
//				$praef->setDayOfWeek( 0 );
//			}
//			$epoch = TTDate::getMiddleDayEpoch( TTDate::getDateOfNextDayOfWeek( $epoch, TTDate::getBeginWeekEpoch( $epoch, $praef->getDayOfWeek() ) ) );
//
//			$artificial_match_value = ( $epoch + 7 * 86400 );
//			$artificial_start_date = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( $epoch ) );
//			$artificial_end_date = TTDate::getMiddleDayEpoch( TTDate::getEndWeekEpoch( $epoch ) );
//			$result = $praef->calculateNextDate( $epoch );
//			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', $artificial_match_value ) );
//			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', $artificial_start_date ) );
//			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', $artificial_end_date ) );
//
//			$epoch = $result['due_date'];
//		}
//	}


	function testMonthly() {
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 4100 );
		$praef->setPrimaryDayOfMonth( 10 );
		$praef->setEffectiveDate( strtotime( '01-Dec-2016' ) );

		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 01:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Nov-2016  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:01AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Nov-2016  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:00AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Nov-2016  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Nov-2016  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Nov-2016  11:59:59PM' ) ) );

		//check that leapyears don't overflow.
		$praef->setPrimaryDayOfMonth( 31 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2016' ) );

		$result = $praef->calculateNextDate( strtotime( '21-Jan-2016 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jan-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Dec-2015  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2015  11:59:59PM' ) ) );


		$result = $praef->calculateNextDate( strtotime( '01-Feb-2016 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '29-Feb-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2016  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '31-Jan-2016 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '29-Feb-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2016  11:59:59PM' ) ) );


		$praef->setPrimaryDayOfMonth( 13 );
		$seed_date = TTDate::getMiddleDayEpoch( strtotime( '31-Dec-' . ( date( 'Y' ) - 1 ) . ' 12:00PM' ) );
		//run current whole year
		for ( $x = 0; $x <= 365; $x++ ) {
			$seed_date = TTDate::incrementDate( $seed_date, 1, 'day' );
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

			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', $due_date ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', $start_date ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', $end_date ) );
		}
	}

//	function testBiWeekly() {
//		/** @var PayrollRemittanceAgencyEventFactory $praef */
//		$praef = TTnew('PayrollRemittanceAgencyEventFactory');
//		$praef->setFrequency( 5000 );
//		$praef->setDayOfWeek(1);
//		$praef->setEffectiveDate(strtotime('01-Dec-2015'));
//
//		//time edge
//		$result = $praef->calculateNextDate( strtotime('01-Dec-2016') );
//		$this->assertEquals( date('r', $result['due_date']), date('r', strtotime('05-Dec-2016 12:00PM')) );
//		$this->assertEquals( date('r', $result['start_date']), date('r', strtotime('27-nov-2016 12:00PM')) );
//		$this->assertEquals( date('r', $result['end_date']), date('r', strtotime('11-Dec-2016 12:00PM')) );
//
//		$praef->setDayOfWeek(2);
//		$this->assertEquals( date('r', $praef->calculateNextDate( strtotime('01-Dec-2016') )), date('r', strtotime('20-Dec-2016 12:00PM')) );
//		$praef->setDayOfWeek(4);
//		$this->assertEquals( date('r', $praef->calculateNextDate( strtotime('23-Feb-2016') )), date('r', strtotime('10-Mar-2016 12:00PM')) );
//	}
//
//	function testBiWeeklyLoop() {
//		/** @var PayrollRemittanceAgencyEventFactory $praef */
//		$praef = TTnew('PayrollRemittanceAgencyEventFactory');
//		$praef->setFrequency( 5000 );
//		$praef->setDayOfWeek(1);
//		$praef->setEffectiveDate(strtotime(time()));
//
//		$epoch = TTDate::getMiddleDayEpoch( TTDate::getDateOfNextDayOfWeek( $praef->getEffectiveDate(), TTDate::getBeginWeekEpoch( $praef->getEffectiveDate(), $praef->getDayOfWeek() ) ) );
//
//		$end_time = TTDate::getEndYearEpoch($epoch + (3*365*86400)); //... 3 years later
//
//		$i = 0;
//		while( $epoch < $end_time ) {
//			Debug::Text( 'testBiWeeklyLoop: '. TTDate::getDate('DATE+TIME', $epoch), __FILE__, __LINE__, __METHOD__, 10);
//
//			$artificial_match_value = TTDate::incrementDate($epoch, 2, 'week' );
//			$epoch = $praef->calculateNextDate( $epoch );
//			$this->assertEquals( date('r', $epoch), date('r', $artificial_match_value) );
//
//			$i++;
//			if ( $i > 500 ) {
//				break;
//			}
//		}
//	}

	function testAnnual() {
		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 2000 );
		$praef->setPrimaryDayOfMonth( 1 );
		$praef->setPrimaryMonth( 12 );
		$praef->setEffectiveDate( strtotime( '01-Dec-2016' ) );

		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Dec-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:01AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Dec-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );


		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Dec-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 11:59PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Dec-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 2000 );
		$praef->setPrimaryDayOfMonth( 12 );
		$praef->setPrimaryMonth( 2 );
		$praef->setEffectiveDate( strtotime( '01-Dec-2012' ) );

		$result = $praef->calculateNextDate( strtotime( '01-Dec-2014' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Feb-2015 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2014  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2014  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '01-Jan-2014' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Feb-2014 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2013  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2013  11:59:59PM' ) ) );


		$result = $praef->calculateNextDate( strtotime( '10-Feb-2014' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Feb-2014 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2013  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2013  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '11-Feb-2014' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Feb-2014 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2013  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2013  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '12-Feb-2014' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Feb-2015 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2014  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2014  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '13-Feb-2014' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '12-Feb-2015 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2014  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2014  11:59:59PM' ) ) );

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
			$this->assertEquals( date( 'r', $epoch ), date( 'r', $artificial_match_value ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', $start_date ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', $end_date ) );
		}
	}


	function testQuarterly() {
		Debug::Text( 'testQuarterly', __FILE__, __LINE__, __METHOD__, 10 );

		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 3000 );
		$praef->setPrimaryDayOfMonth( 1 );
		$praef->setQuarterMonth( 1 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2016' ) );

		$result = $praef->calculateNextDate( strtotime( '02-Dec-2016 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '01-Jan-2016 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Apr-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2016  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '23-Feb-2016 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Apr-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2016  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '01-Apr-2016 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jul-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Jun-2016  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '13-Jun-2016 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Jul-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Jun-2016  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '01-Jul-2016 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Oct-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-2016  11:59:59PM' ) ) );

		$praef->setQuarterMonth( 2 );
		$result = $praef->calculateNextDate( strtotime( '11-Sept-2016 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '01-Nov-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-2016  11:59:59PM' ) ) );

		$praef->setQuarterMonth( 1 );
		$praef->setPrimaryDayOfMonth( 3 );
		//based off effective date
		$result = $praef->calculateNextDate( strtotime( '11-Sept-1981 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Jan-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2015  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2015  11:59:59PM' ) ) );

		//try a really old one
		$praef->setEffectiveDate( strtotime( '01-Jan-1981' ) );
		$result = $praef->calculateNextDate( strtotime( '11-Sept-1981 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Oct-1981 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-1981  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-1981  11:59:59PM' ) ) );

		//try a far future one
		$praef->setEffectiveDate( strtotime( '01-Jan-3610' ) );
		$result = $praef->calculateNextDate( strtotime( '11-Sept-1981 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Jan-3610 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-3609  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-3609  11:59:59PM' ) ) );

		//futurama
		$result = $praef->calculateNextDate( strtotime( '11-Sept-3610 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Oct-3610 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-3610  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-3610  11:59:59PM' ) ) );

		//leap year hitting the extra day with an overshot last day of month
		//31st and month = 2 (feb)
		$praef->setPrimaryDayOfMonth( 31 );
		$praef->setQuarterMonth( 2 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2016' ) );

		//checking for leap year overflow
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2015 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '29-Feb-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2015  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2015  11:59:59PM' ) ) );
		$praef->setPrimaryDayOfMonth( 28 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2016' ) );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2015 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '28-Feb-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2015  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2015  11:59:59PM' ) ) );
		$praef->setPrimaryDayOfMonth( 29 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2016' ) );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2015 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '29-Feb-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2015  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2015  11:59:59PM' ) ) );
		$praef->setPrimaryDayOfMonth( 30 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2016' ) );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2015 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '29-Feb-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2015  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2015  11:59:59PM' ) ) );

		$epoch = time();
		$praef->setPrimaryDayOfMonth( 3 );
		$praef->setQuarterMonth( 1 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2016' ) );

		for ( $i = 0; $i < 365; $i++ ) {
			$artificial_match_value = TTDate::getDateOfNextQuarter( $epoch, $praef->getPrimaryDayOfMonth(), $praef->getQuarterMonth() );

			$prev_quarter_epoch = TTDate::incrementDate( $artificial_match_value, -3, 'month' );
			$start_date = TTDate::getBeginDayEpoch( TTDate::getBeginMonthEpoch( TTDate::getBeginQuarterEpoch( $prev_quarter_epoch ) ) );
			$end_date = TTDate::getEndDayEpoch( TTDate::getEndMonthEpoch( TTDate::getEndQuarterEpoch( $prev_quarter_epoch ) ) );

			$result = $praef->calculateNextDate( $epoch );

//			Debug::Text( 'seed: ' . date( 'r', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );
//			Debug::Text( 'due_date: ' . date( 'r', $result['due_date'] ), __FILE__, __LINE__, __METHOD__, 10 );
//			Debug::Text( 'match_due_date: ' . date( 'r', $artificial_match_value ), __FILE__, __LINE__, __METHOD__, 10 );
//			Debug::Text( 'start_date: ' . date( 'r', $result['start_date'] ), __FILE__, __LINE__, __METHOD__, 10 );
//			Debug::Text( 'match start_date: ' . date( 'r', $start_date ), __FILE__, __LINE__, __METHOD__, 10 );
//			Debug::Text( 'end_date: ' . date( 'r', $result['end_date'] ), __FILE__, __LINE__, __METHOD__, 10 );
//			Debug::Text( 'match end_date: ' . date( 'r', $end_date ), __FILE__, __LINE__, __METHOD__, 10 );

			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', $artificial_match_value ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', $start_date ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', $end_date ) );
		}
	}

	function testSemiWeekly() {
		//Wednesday, Thursday, Friday = Wednesday. Saturday, Sunday, Monday, Tuesday = Friday
		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 64000 );
		$praef->setEffectiveDate( strtotime( '01-Jan-2017' ) );

		$result = $praef->calculateNextDate( strtotime( '01-Sept-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '30-Aug-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '01-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '06-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '02-Sept-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '02-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '05-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '08-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '03-Sept-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '02-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '05-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '08-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '04-Sept-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '02-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '05-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '08-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '05-Sept-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '02-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '05-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '08-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '06-Sept-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '06-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '08-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '13-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '07-Sept-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '06-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '08-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '13-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '08-Sept-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '06-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '08-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '13-Sep-2017 12:00PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '09-Sept-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '09-Sep-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '12-Sep-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Sep-2017 12:00PM' ) ) );

	}

	function testAcceleratedThreshold1() {
		// 10th and 25th of each month. If transaction date falls between 1-15th of the month, pay by 25th. If it falls between 16th and last day, pay on the 10th of the next month.
		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 50000 );
		$praef->setEffectiveDate( strtotime( '01-Jul-2017' ) );

		$result = $praef->calculateNextDate( strtotime( '01-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '02-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '03-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '04-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '13-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '14-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '15-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '16-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '17-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '18-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '19-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '30-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '31-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
	}

	function testAcceleratedThreshold2() {
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 51000 );
		$praef->setEffectiveDate( strtotime( '01-Jul-2017' ) );

		$result = $praef->calculateNextDate( strtotime( '01-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jul-2017  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '05-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '06-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '07-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jul-2017  11:59:59PM' ) ) );
	}

	function testWeekendAvoidance() {
		/** @var PayrollRemittanceAgencyListFactory $praf */
		$praf = TTnew( 'PayrollRemittanceAgencyListFactory' );
		$praf->getById( $this->agency_id );
		/** @var PayrollRemittanceAgencyFactory $pra_obj */
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

		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 5100 );
		$praef->setDayOfWeek( 0 );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:01AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '04-Dec-2016 12:00PM' ) ) );
//

		//
		$pra_obj->setAlwaysOnWeekDay( 2 ); //2=Forward
		$pra_obj->save( FALSE );

		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 5100 );
		$praef->setDayOfWeek( 0 );

		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:01AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '05-Dec-2016 12:00PM' ) ) );


		$pra_obj->setAlwaysOnWeekDay( 3 ); //3=closest business day
		$pra_obj->save( FALSE );

		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 5100 );
		$praef->setDayOfWeek( 0 );


		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:01AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '05-Dec-2016 12:00PM' ) ) );

		$pra_obj->setAlwaysOnWeekDay( 1 ); //1=Backwards
		$pra_obj->save( FALSE );

		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 5100 );
		$praef->setDayOfWeek( 0 );
		$result = $praef->calculateNextDate( strtotime( '01-Dec-2016 12:01AM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '02-Dec-2016 12:00PM' ) ) );

		unset($pra_obj, $praef);

	}

	function testHolidayAvoidance() {
		/** @var PayrollRemittanceAgencyListFactory $praf */
		$praf = TTnew( 'PayrollRemittanceAgencyListFactory' );
		$praf->getById($this->agency_id);
		$pra_obj = $praf->getCurrent();

		$this->assertEquals( TRUE, is_object($pra_obj), 'agency is not an object' );

		$holiday_policy_ids = array();

		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setId( $praef->getNextInsertId() );
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setType( 'T4' );
		$praef->setReminderDays( 2 );
		$praef->setFrequency( 4100 );
		$praef->setPrimaryDayOfMonth( 25 );
		$praef->setStatus( 10 );
		$praef->setEffectiveDate( strtotime( '05-Dec-2017 12:00AM' ) );
		$praef->save( FALSE );

		$rhf_xmas = TTNew( 'RecurringHolidayFactory' );
		$rhf_xmas->setCompany( $this->company_id );
		$rhf_xmas->setName( 'Test - xmas' );
		$rhf_xmas->setType( 10 );
		$rhf_xmas->setDayOfMonth( 25 );
		$rhf_xmas->setMonth( 12 );
		$holiday_policy_ids[] = $rhf_xmas->save();

		/** @var RecurringHolidayFactory $rhf_box */
		$rhf_box = TTNew( 'RecurringHolidayFactory' );
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

		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
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

		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
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

		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
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

		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
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

		/** @var PayrollRemittanceAgencyListFactory $praf */
		$praf = TTnew( 'PayrollRemittanceAgencyListFactory' );
		$praf->getById($this->agency_id);
		$pra_obj = $praf->getCurrent();


//		/** @var PayrollRemittanceAgencyEventFactory $praef */
//		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
//		$praef->setId( FALSE );
//		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
//		$praef->setType( 10 );
//		$praef->setReminderDays( 2 );
//		$praef->setFrequency( 4100 );
//		$praef->setPrimaryDayOfMonth( 25 );
//		$praef->setStatus( 10 );
//		$praef->setEffectiveDate( strtotime( '05-Dec-2017 12:00AM' ) );

		$rhf_xmas = TTNew( 'RecurringHolidayFactory' );
		$rhf_xmas->setCompany( $this->company_id );
		$rhf_xmas->setName( 'Test - xmas' );
		$rhf_xmas->setType( 10 );
		$rhf_xmas->setDayOfMonth( 25 );
		$rhf_xmas->setMonth( 12 );
		$rhf_xmas->setAlwaysOnWeekDay( 2 );
		$holiday_policy_ids[] = $rhf_xmas->save();

		/** @var RecurringHolidayFactory $rhf_box */
		$rhf_box = TTNew( 'RecurringHolidayFactory' );
		$rhf_box->setCompany( $this->company_id );
		$rhf_box->setName( 'Test - boxerday' );
		$rhf_box->setType( 10 );
		$rhf_box->setDayOfMonth( 26 );
		$rhf_box->setMonth( 12 );
		$rhf_box->setAlwaysOnWeekDay( 2 );
		$holiday_policy_ids[] = $rhf_box->save();

		$rhf_box = TTNew( 'RecurringHolidayFactory' );
		$rhf_box->setCompany( $this->company_id );
		$rhf_box->setName( 'Test - 27th dec' );
		$rhf_box->setType( 10 );
		$rhf_box->setDayOfMonth( 27 );
		$rhf_box->setMonth( 12 );
		$rhf_box->setAlwaysOnWeekDay( 2 );
		$holiday_policy_ids[] = $rhf_box->save();

		$rhf_box = TTNew( 'RecurringHolidayFactory' );
		$rhf_box->setCompany( $this->company_id );
		$rhf_box->setName( 'Test - 28th dec' );
		$rhf_box->setType( 10 );
		$rhf_box->setDayOfMonth( 28 );
		$rhf_box->setMonth( 12 );
		$rhf_box->setAlwaysOnWeekDay( 2 );
		$holiday_policy_ids[] = $rhf_box->save();

		$rhf_box = TTNew( 'RecurringHolidayFactory' );
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

		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
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

		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
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

		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
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

		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
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

		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 4200 );

		$praef->setPrimaryDayOfMonth(7);
		$praef->setSecondaryDayOfMonth(22);
		$praef->setDueDateDelayDays(3);
		$praef->setEffectiveDate( strtotime( '01-Jul-2017' ) );

		$result = $praef->calculateNextDate( strtotime( '01-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jun-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jul-2017  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '05-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jun-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '06-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jun-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '07-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jun-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jul-2017  11:59:59PM' ) ) );


		$result = $praef->calculateNextDate( strtotime( '08-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '22-Jul-2017  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '09-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '22-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '21-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '22-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '22-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '22-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '23-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Aug-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '30-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Aug-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '1-Aug-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Aug-2017  11:59:59PM' ) ) );


		$praef->setPrimaryDayOfMonth(10);
		$praef->setSecondaryDayOfMonth(25);
		$praef->setDueDateDelayDays(6);
		$praef->setEffectiveDate( strtotime( '01-Jul-2017' ) );

		$result = $praef->calculateNextDate( strtotime( '01-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '16-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '26-Jun-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '06-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '16-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '26-Jun-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '09-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '16-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '26-Jun-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '10-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '16-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '26-Jun-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '11-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '11-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '25-Jul-2017  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '12-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '11-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '25-Jul-2017  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '24-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '11-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '25-Jul-2017  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '25-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '11-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '25-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '26-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '16-Aug-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '26-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Aug-2017  11:59:59PM' ) ) );


		//ca threshold1
		// 10th and 25th of each month. If transaction date falls between 1-15th of the month, pay by 25th. If it falls between 16th and last day, pay on the 10th of the next month.
		$praef->setPrimaryDayOfMonth(15);
		$praef->setSecondaryDayOfMonth(31);
		$praef->setDueDateDelayDays(10);
		$praef->setEffectiveDate( strtotime( '01-Jul-2017' ) );

		$result = $praef->calculateNextDate( strtotime( '01-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '02-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '03-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '04-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '13-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '14-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '15-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '16-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '17-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '18-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '19-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '30-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '31-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017  11:59:59PM' ) ) );
	}

	function testSemiAnnual() {

		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 2200 );

		$praef->setPrimaryMonth(1);
		$praef->setPrimaryDayOfMonth(10);
		$praef->setSecondaryMonth(6);
		$praef->setSecondaryDayOfMonth(15);
		$praef->setDueDateDelayDays(5);
		$praef->setEffectiveDate( strtotime( '01-Jan-2015' ) );

		$result = $praef->calculateNextDate( strtotime( '01-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Jan-2018 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jun-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Jan-2018  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '01-Mar-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '20-Jun-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '11-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jun-2017  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '05-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jun-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Jan-2017  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '05-Dec-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Jan-2018 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jun-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Jan-2018  11:59:59PM' ) ) );


		$result = $praef->calculateNextDate( strtotime( '09-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jun-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '10-Jan-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '10-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '20-Jun-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '11-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jun-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '11-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '20-Jun-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '11-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jun-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '12-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '20-Jun-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '11-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jun-2017  11:59:59PM' ) ) );

	}

	function testYearToDate() {
		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 2100 );

		$praef->setPrimaryMonth(6);
		$praef->setPrimaryDayOfMonth(20);
		$praef->setDueDateDelayDays(5);

		$result = $praef->calculateNextDate( strtotime( '01-Feb-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jun-2017  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '19-Jun-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jun-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '20-Jun-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jun-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '21-Jun-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2018 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jun-2018  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '01-Aug-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jun-2018 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jun-2018  11:59:59PM' ) ) );

	}

	function testEighthMonthly() {
		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 63000 );

		$result = $praef->calculateNextDate( strtotime( '01-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '06-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Jan-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '02-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '06-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Jan-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '03-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '06-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Jan-2017  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '04-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '05-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '06-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '07-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '04-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '08-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '14-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '11-Jan-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '09-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '14-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '11-Jan-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '10-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '14-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '11-Jan-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '11-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '14-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '11-Jan-2017  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '12-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '18-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '12-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jan-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '15-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '18-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '12-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jan-2017  11:59:59PM' ) ) );


		$result = $praef->calculateNextDate( strtotime( '16-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '22-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '19-Jan-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '19-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '22-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '19-Jan-2017  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '20-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '20-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '22-Jan-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '22-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '20-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '22-Jan-2017  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '23-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '28-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '25-Jan-2017  11:59:59PM' ) ) );
		$result = $praef->calculateNextDate( strtotime( '25-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '28-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '25-Jan-2017  11:59:59PM' ) ) );

		$result = $praef->calculateNextDate( strtotime( '26-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Feb-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '26-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( strtotime( '25-Jan-2017  11:59:59PM' ) ) ) );
		$result = $praef->calculateNextDate( strtotime( '27-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Feb-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '26-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( strtotime( '25-Jan-2017  11:59:59PM' ) ) ) );
		$result = $praef->calculateNextDate( TTDate::getEndMonthEpoch( strtotime( '25-Jan-2017  11:59:59PM' ) ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Feb-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '26-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( strtotime( '25-Jan-2017  11:59:59PM' ) ) ) );

	}

	function testTwiceMonthly() {
		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId($this->agency_id);
		$praef->setFrequency( 61000 );

		//increase the upper bound to test a wider range.
		$testrange = 5;
		for( $year = (date('Y') - $testrange); $year <= ( date('Y') + $testrange ); $year++ ) {
			//January
			$result = $praef->calculateNextDate( strtotime( '01-Jan--' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-' . $year . ' 11:59:59PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '02-Jan-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-' . $year . ' 11:59:59PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '15-Jan-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-' . $year . ' 11:59:59PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '30-Jan-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-' . $year . ' 11:59:59PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '31-Jan-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-' . $year . ' 11:59:59PM' ) ) );

			//Feb
			$result = $praef->calculateNextDate( strtotime( '01-Feb-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Feb-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Feb-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Feb-' . $year . ' 11:59:59PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '02-Feb-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Feb-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Feb-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Feb-' . $year . ' 11:59:59PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '14-Feb-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Feb-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Feb-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Feb-' . $year . ' 11:59:59PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '15-Feb-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Feb-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Feb-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Feb-' . $year . ' 11:59:59PM' ) ) );

			$result = $praef->calculateNextDate( strtotime( '16-Feb-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Mar-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Feb-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$result = $praef->calculateNextDate( strtotime( '17-Feb-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Mar-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Feb-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$result = $praef->calculateNextDate( strtotime( '27-Feb-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Mar-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Feb-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$result = $praef->calculateNextDate( strtotime( '28-Feb-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Mar-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Feb-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );

			//Jul
			$result = $praef->calculateNextDate( strtotime( '01-Jul-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-' . $year . ' 11:59:59PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '02-Jul-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-' . $year . ' 11:59:59PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '14-Jul-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-' . $year . ' 11:59:59PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '15-Jul-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jul-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jul-' . $year . ' 11:59:59PM' ) ) );

			$result = $praef->calculateNextDate( strtotime( '16-Jul-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$result = $praef->calculateNextDate( strtotime( '17-Jul-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$result = $praef->calculateNextDate( strtotime( '30-Jul-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$result = $praef->calculateNextDate( strtotime( '31-Jul-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Aug-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jul-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );

			//Nov
			$result = $praef->calculateNextDate( strtotime( '01-Nov-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Nov-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Nov-' . $year . ' 11:59:59PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '02-Nov-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Nov-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Nov-' . $year . ' 11:59:59PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '14-Nov-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Nov-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Nov-' . $year . ' 11:59:59PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '15-Nov-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Nov-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Nov-' . $year . ' 11:59:59PM' ) ) );

			$result = $praef->calculateNextDate( strtotime( '16-Nov-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Dec-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Nov-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$result = $praef->calculateNextDate( strtotime( '17-Nov-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Dec-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Nov-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$result = $praef->calculateNextDate( strtotime( '29-Nov-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Dec-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Nov-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$result = $praef->calculateNextDate( strtotime( '30-Nov-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Dec-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Nov-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );

			//Dec
			$result = $praef->calculateNextDate( strtotime( '01-Dec-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '26-Dec-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Dec-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Dec-' . $year . ' 11:59:59PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '02-Dec-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '26-Dec-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Dec-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Dec-' . $year . ' 11:59:59PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '14-Dec-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '26-Dec-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Dec-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Dec-' . $year . ' 11:59:59PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '15-Dec-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '26-Dec-' . $year . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Dec-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Dec-' . $year . ' 11:59:59PM' ) ) );

			$result = $praef->calculateNextDate( strtotime( '16-Dec-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jan-' . ($year + 1) .'12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Dec-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$result = $praef->calculateNextDate( strtotime( '17-Dec-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jan-' . ($year + 1) .'12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Dec-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$result = $praef->calculateNextDate( strtotime( '30-Dec-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jan-' . ($year + 1) .'12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Dec-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );
			$result = $praef->calculateNextDate( strtotime( '31-Dec-' . $year . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jan-' . ($year + 1) .'12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Dec-' . $year . ' 12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', TTDate::getEndMonthEpoch( $result['start_date'] ) ) );

			//next jan
			$result = $praef->calculateNextDate( strtotime( '01-Jan-' . ($year + 1 ) . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-' . ($year + 1 ) . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-' . ($year + 1 ) . '12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-' . ($year + 1 ) . '11:59:59PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '02-Jan-' . ($year + 1 ) . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-' . ($year + 1 ) . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-' . ($year + 1 ) . '12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-' . ($year + 1 ) . '11:59:59PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '15-Jan-' . ($year + 1 ) . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-' . ($year + 1 ) . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-' . ($year + 1 ) . '12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-' . ($year + 1 ) . '11:59:59PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '30-Jan-' . ($year + 1 ) . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-' . ($year + 1 ) . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-' . ($year + 1 ) . '12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-' . ($year + 1 ) . '11:59:59PM' ) ) );
			$result = $praef->calculateNextDate( strtotime( '31-Jan-' . ($year + 1 ) . '12:00PM' ) );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Feb-' . ($year + 1 ) . '12:00PM' ) ) );
			$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-' . ($year + 1 ) . '12:00AM' ) ) );
			$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-' . ($year + 1 ) . '11:59:59PM' ) ) );
		}
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

	function testPayPeriod() {
		/** @var PayPeriodScheduleFactory $ppsf */
		$test_start_date = strtotime( '01-Jan-2017 12:00PM' ); //should be day before first pay perdio start date.
		$ppsf_id = $this->setupPayPeriodTest($test_start_date);

		$this->assertEquals( TRUE, TTUUID::isUUID($ppsf_id), 'Pay period schedule must be an object.' );
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 1000 );
		$praef->setPayPeriodSchedule( array($ppsf_id ) );
		$praef->setDueDateDelayDays( 0 );
		$praef->setReminderDays( 3 );

		$test_start_date = strtotime( '01-Jan-2017 12:00PM' ); //should be day before first pay perdio start date.

		$pp_obj = $this->pay_period_objs[0];


		$test_date = $test_start_date;

		//testing every day in every pay period:
		$result = $praef->calculateNextDate( $test_date );
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals(4, count( $result ), '$result should have 3 elements.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', ( $pp_obj->getTransactionDate() + $praef->getDueDateDelayDays() ) ), 'Due date Matches.' );

		$loop_counter = 1;
		while ( $test_date <= $pp_obj->getEndDate() AND $test_date < strtotime('01-Jan-2020') ) {
			$test_date = TTDate::incrementDate( $test_date, 1, 'day' );
			$result = $praef->calculateNextDate( $test_date );
			$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', ( $pp_obj->getTransactionDate() + $praef->getDueDateDelayDays() ) ), 'Due date Matches.' );
			$loop_counter++;
		}

		//testing with static values:

		$pp_obj = $this->pay_period_objs[0];
		$result = $praef->calculateNextDate( $pp_obj->getStartDate() );
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 4, count( $result ), '$result should have 3 elements.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '20-Jan-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '20-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jan-2017 23:59:59' ) ), 'End date Matches.' );


		$pp_obj = $this->pay_period_objs[1];
		$result = $praef->calculateNextDate( $pp_obj->getStartDate() );
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 4, count( $result ), '$result should have 3 elements.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '20-Jan-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '20-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '20-Jan-2017 23:59:59' ) ), 'End date Matches.' );

		$pp_obj = $this->pay_period_objs[2];
		$result = $praef->calculateNextDate( $pp_obj->getStartDate() );
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 4, count( $result ), '$result should have 3 elements.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '03-Feb-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Feb-2017 23:59:59' ) ), 'End date Matches.' );

		$pp_obj = $this->pay_period_objs[3];
		$praef->setDueDateDelayDays( 3 );
		$result = $praef->calculateNextDate( $pp_obj->getStartDate() );
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 4, count( $result ), '$result should have 3 elements.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '20-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '17-Feb-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '17-Feb-2017 23:59:59' ) ), 'End date Matches.' );

		$pp_obj = $this->pay_period_objs[4];
		$praef->setDueDateDelayDays( 6 );
		$result = $praef->calculateNextDate( $pp_obj->getStartDate() );
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 4, count( $result ), '$result should have 3 elements.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '09-Mar-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '03-Mar-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '03-Mar-2017 23:59:59' ) ), 'End date Matches.' );

		$pp_obj = $this->pay_period_objs[5];
		$praef->setDueDateDelayDays( 9 );
		$result = $praef->calculateNextDate( $pp_obj->getStartDate() );
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 4, count( $result ), '$result should have 3 elements.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '26-Mar-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '17-Mar-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '17-Mar-2017 23:59:59' ) ), 'End date Matches.' );

	}


	function testMonthlyQuarterExceptions() {
		//US - Monthly (Quarter Exceptions)
		//Due the 15th day of the month following the monthly withholding period, except for March, June, September and December; then due the last day of the month following the withholding period.
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 60000 );

		$result = $praef->calculateNextDate( strtotime( '01-Jan-2017 12:00PM' ) );

		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 3, count( $result ), '$result should have 3 elements.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime('02-Jan-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime('16-Jan-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime('30-Jan-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime('31-Jan-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime('01-Feb-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Mar-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Feb-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '28-Feb-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime('28-Feb-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Mar-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Feb-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '28-Feb-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime('01-Mar-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Mar-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime('31-Mar-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Mar-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime('01-Apr-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-May-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Apr-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime('30-Apr-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-May-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Apr-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime('01-May-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Jun-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-May-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-May-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime('31-May-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Jun-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-May-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-May-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime('01-Jun-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jun-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Jun-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime('30-Jun-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jun-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Jun-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime('01-Jul-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Aug-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime('31-Jul-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Aug-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime('01-Aug-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Sep-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Aug-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Aug-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime('31-Aug-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Sep-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Aug-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Aug-2017 23:59:59' ) ), 'End date Matches.' );

		$test_start_date = strtotime('01-Sep-2017 12:00PM');
		$result = $praef->calculateNextDate( $test_start_date );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Oct-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Sep-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-2017 23:59:59' ) ), 'End date Matches.' );
		$test_start_date = strtotime('30-Sep-2017 12:00PM');
		$result = $praef->calculateNextDate( $test_start_date );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Oct-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Sep-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime('01-Oct-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Nov-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Oct-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime('31-Oct-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Nov-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Oct-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime('01-Nov-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Dec-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Nov-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime('30-Nov-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Dec-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Nov-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime('01-Dec-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jan-2018 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Dec-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime('31-Dec-2017 12:00PM') );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jan-2018 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Dec-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Dec-2017 23:59:59' ) ), 'End date Matches.' );
	}

	function testQuarterMonthly() {
		//quarter-monthly
		// 	1.The first seven days of the calendar month.
		//	2.The 8th to the 15th day of the calendar month.
		// 	3.The 16th to the 22nd day of the calendar month.
		// 	4.The 23rd day to the end of the calendar month.
		//As a quarter-monthly filer, you are required to pay at least 90 percent of the actual tax due within three banking day following the end
		//of the quarter-monthly period.

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 62000 );

		$result = $praef->calculateNextDate( strtotime( '01-Jan-2017 12:00PM' ) );

		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 3, count( $result ), '$result should have 3 elements.' );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jan-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '07-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '10-Jan-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '07-Jan-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '08-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '18-Jan-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '11-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '18-Jan-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '15-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '18-Jan-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '08-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '15-Jan-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '16-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jan-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '22-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '18-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jan-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '22-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '22-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '25-Jan-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '16-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '22-Jan-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '23-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '28-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '23-Feb-2018 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Mar-2018 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Feb-2018' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '28-Feb-2018 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '28-Feb-2018 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '03-Mar-2018 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '23-Feb-2018' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '28-Feb-2018 23:59:59' ) ), 'End date Matches.' );
	}

	function testUSQuarterly() {
		// (April 30, July 31, and October 31).
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 59000 );

		$result = $praef->calculateNextDate( strtotime( '01-Jan-2017 12:00PM' ) );

		//sanity check
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 3, count( $result ), '$result should have 3 elements.' );

		//Q1
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '15-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '15-Mar-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-Mar-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2017 23:59:59' ) ), 'End date Matches.' );

		//Q2.
		$result = $praef->calculateNextDate( strtotime( '01-Apr-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Jun-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '15-Apr-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Jun-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '15-Jun-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Jun-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '30-Jun-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Jul-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Jun-2017 23:59:59' ) ), 'End date Matches.' );

		//Q3.
		$result = $praef->calculateNextDate( strtotime( '01-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Oct-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '15-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Oct-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '15-Sep-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Oct-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '30-Sep-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '31-Oct-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Sep-2017 23:59:59' ) ), 'End date Matches.' );

		//Q4
		$result = $praef->calculateNextDate( strtotime( '01-Oct-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2018 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2018 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '15-Oct-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2018 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2018 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '15-Dec-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2018 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2018 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-Dec-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '30-Apr-2018 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018' ) ), 'Start date Matches.');
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Mar-2018 23:59:59' ) ), 'End date Matches.' );
	}


	function testUSMonthlyExcludeLastMOQ() {
		// (April 30, July 31, and October 31).
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
		$praef->setPayrollRemittanceAgencyId( $this->agency_id );
		$praef->setFrequency( 60100 );

		$result = $praef->calculateNextDate( strtotime( '01-Jan-2017 12:00PM' ) );

		//sanity check
		$this->assertEquals( TRUE, is_array( $result ), '$result should be  an array.' );
		$this->assertEquals( 3, count( $result ), '$result should have 3 elements.' );

		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '02-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '16-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '30-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-Jan-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '01-Feb-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Mar-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Feb-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '28-Feb-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '28-Feb-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Mar-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Feb-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '28-Feb-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '01-Mar-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-May-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Apr-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '30-Apr-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-May-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Apr-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '01-Apr-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-May-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Apr-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '30-Apr-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-May-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Apr-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Apr-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '01-May-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Jun-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-May-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-May-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-May-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Jun-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-May-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-May-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '01-Jun-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Aug-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Aug-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '01-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Aug-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-Jul-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Aug-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jul-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jul-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '01-Aug-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Sep-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Aug-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Aug-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-Aug-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Sep-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Aug-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Aug-2017 23:59:59' ) ), 'End date Matches.' );

		$test_start_date = strtotime( '01-Sep-2017 12:00PM' );
		$result = $praef->calculateNextDate( $test_start_date );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Nov-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Oct-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-Oct-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Nov-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Oct-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '01-Oct-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Nov-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Oct-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-Oct-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Nov-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Oct-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Oct-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '01-Nov-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Dec-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Nov-2017 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '30-Nov-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Dec-2017 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Nov-2017' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '30-Nov-2017 23:59:59' ) ), 'End date Matches.' );

		$result = $praef->calculateNextDate( strtotime( '01-Dec-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2018 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2018 23:59:59' ) ), 'End date Matches.' );
		$result = $praef->calculateNextDate( strtotime( '31-Dec-2017 12:00PM' ) );
		$this->assertEquals( date( 'r', $result['due_date'] ), date( 'r', strtotime( '15-Feb-2018 12:00:00' ) ), 'Due date Matches.' );
		$this->assertEquals( date( 'r', $result['start_date'] ), date( 'r', strtotime( '01-Jan-2018' ) ), 'Start date Matches.' );
		$this->assertEquals( date( 'r', $result['end_date'] ), date( 'r', strtotime( '31-Jan-2018 23:59:59' ) ), 'End date Matches.' );

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

		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
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

		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 999 );
		Debug::text( 'User ID: ' . $this->user_id, __FILE__, __LINE__, __METHOD__, 10 );
		$user_obj = $this->getUserObject( $this->user_id );
		$user_obj->setHireDate( TTDate::incrementDate( $now, -5, 'day' ) );
		Debug::Text( 'FIRST User Hire Date: ' . TTDate::getDate( 'DATE+TIME', $user_obj->getHireDate() ), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $user_obj->isValid() ) {
			$user_obj->Save( FALSE );
		}


		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
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
		$user_obj->setHireDate( strtotime( $now ) );
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
		$user_obj->setHireDate( TTDate::incrementDate( $now, 30, 'day' ) );
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

		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
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


		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
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
		/** @var PayPeriodScheduleFactory $ppsf */
		/** @var PayrollRemittanceAgencyEventFactory $praef */

		$test_start_date = strtotime( '01-Jan-2017 12:00PM' ); //should be day before first pay perdio start date.
		$ppsf_id = $this->setupPayPeriodTest($test_start_date);

		$this->assertEquals( TRUE, TTUUID::isUUID($ppsf_id), 'Pay period schedule id must be a UUID.' );

		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
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
		/** @var PayrollRemittanceAgencyEventFactory $praef */
		$praef = TTnew( 'PayrollRemittanceAgencyEventFactory' );
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

		$this->assertEquals( date( 'r', $praef->getDueDate() ), date( 'r', strtotime( '10-Dec-2016 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $praef->getStartDate() ), date( 'r', strtotime( '01-Nov-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $praef->getEndDate() ), date( 'r', strtotime( '30-Nov-2016  11:59:59PM' ) ) );

		$praef->setEffectiveDate( strtotime( '01-Jan-2017' ) );
		$praef->setEnableRecalculateDates( TRUE );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE, TRUE );
		}

		$this->assertEquals( date( 'r', $praef->getDueDate() ), date( 'r', strtotime( '10-Jan-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $praef->getStartDate() ), date( 'r', strtotime( '01-Dec-2016  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $praef->getEndDate() ), date( 'r', strtotime( '31-Dec-2016  11:59:59PM' ) ) );

		$praef->setEffectiveDate( strtotime( '01-Feb-2017' ) );
		$praef->setEnableRecalculateDates( TRUE );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE, TRUE );
		}

		$this->assertEquals( date( 'r', $praef->getDueDate() ), date( 'r', strtotime( '10-Feb-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $praef->getStartDate() ), date( 'r', strtotime( '01-Jan-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $praef->getEndDate() ), date( 'r', strtotime( '31-Jan-2017  11:59:59PM' ) ) );

		$praef->setEffectiveDate( strtotime( '01-Mar-2017' ) );
		$praef->setEnableRecalculateDates( TRUE );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE, TRUE );
		}

		$this->assertEquals( date( 'r', $praef->getDueDate() ), date( 'r', strtotime( '10-Mar-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $praef->getStartDate() ), date( 'r', strtotime( '01-Feb-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $praef->getEndDate() ), date( 'r', strtotime( '28-Feb-2017  11:59:59PM' ) ) );

		$praef->setEffectiveDate( strtotime( '01-Apr-2017' ) );
		$praef->setEnableRecalculateDates( TRUE );
		if ( $praef->isValid() ) {
			$praef->Save( FALSE, TRUE );
		}

		$this->assertEquals( date( 'r', $praef->getDueDate() ), date( 'r', strtotime( '10-Apr-2017 12:00PM' ) ) );
		$this->assertEquals( date( 'r', $praef->getStartDate() ), date( 'r', strtotime( '01-Mar-2017  12:00AM' ) ) );
		$this->assertEquals( date( 'r', $praef->getEndDate() ), date( 'r', strtotime( '31-Mar-2017  11:59:59PM' ) ) );
	}
}

?>