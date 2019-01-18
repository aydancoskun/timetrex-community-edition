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

class PayStubTest extends PHPUnit_Framework_TestCase {
	protected $company_id = NULL;
	protected $user_id = NULL;
	protected $pay_period_schedule_id = NULL;
	protected $pay_period_objs = NULL;
	protected $pay_stub_account_link_arr = NULL;

	public function setUp() {
		Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10);

		$dd = new DemoData();
		$dd->setEnableQuickPunch( FALSE ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_'.uniqid( NULL, TRUE ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		$this->legal_entity_id = $dd->createLegalEntity( $this->company_id, 10 );
		Debug::text('Company ID: '. $this->company_id, __FILE__, __LINE__, __METHOD__, 10);

		$this->currency_id = $dd->createCurrency( $this->company_id, 10 );

		//$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$dd->createUserWageGroups( $this->company_id );

		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 100 );

		$dd->createPayStubAccount( $this->company_id );
		$dd->createPayStubAccountLink( $this->company_id );
		$this->getPayStubAccountLinkArray();

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$this->assertGreaterThan( 0, $this->company_id );
		$this->assertGreaterThan( 0, $this->user_id );

		return TRUE;
	}

	public function tearDown() {
		Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10);

		//$this->deleteAllSchedules();

		return TRUE;
	}

	function getPayStubAccountLinkArray() {
		$this->pay_stub_account_link_arr = array(
			'total_gross' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Total Gross'),
			'total_deductions' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Total Deductions'),
			'employer_contribution' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 40, 'Employer Total Contributions'),
			'net_pay' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 40, 'Net Pay'),
			'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
			);

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


		$anchor_date = TTDate::getBeginWeekEpoch( strtotime('01-Jan-06') ); //Start 6 weeks ago

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

	function createPayPeriods() {
		$max_pay_periods = 29;

		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getById( $this->pay_period_schedule_id );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pps_obj = $ppslf->getCurrent();

			for ( $i = 0; $i < $max_pay_periods; $i++ ) {
				if ( $i == 0 ) {
					//$end_date = TTDate::getBeginYearEpoch();
					$end_date = (strtotime('01-Jan-06') - 86400);
				} else {
					$end_date = ($end_date + ( (86400 * 14) ));
				}

				Debug::Text('I: '. $i .' End Date: '. TTDate::getDate('DATE+TIME', $end_date), __FILE__, __LINE__, __METHOD__, 10);

				$pps_obj->createNextPayPeriod( $end_date, (86400 * 3600), FALSE ); //Don't import punches, as that causes deadlocks when running tests in parallel.
			}

		}

		return TRUE;
	}

	function getAllPayPeriods() {
		$pplf = new PayPeriodListFactory();
		//$pplf->getByCompanyId( $this->company_id );
		$pplf->getByPayPeriodScheduleId( $this->pay_period_schedule_id );
		if ( $pplf->getRecordCount() > 0 ) {
			foreach( $pplf as $pp_obj ) {
				Debug::text('Pay Period... Start: '. TTDate::getDate('DATE+TIME', $pp_obj->getStartDate() ) .' End: '. TTDate::getDate('DATE+TIME', $pp_obj->getEndDate() ), __FILE__, __LINE__, __METHOD__, 10);

				$this->pay_period_objs[] = $pp_obj;
			}
		}

		$this->pay_period_objs = array_reverse( $this->pay_period_objs );

		return TRUE;
	}

	function getPayStubEntryArray( $pay_stub_id ) {
		//Check Pay Stub to make sure it was created correctly.
		$pself = new PayStubEntryListFactory();
		$pself->getByPayStubId( $pay_stub_id ) ;
		if ( $pself->getRecordCount() > 0 ) {
			foreach( $pself as $pse_obj ) {
				$ps_entry_arr[$pse_obj->getPayStubEntryNameId()][] = array(
					'amount' => $pse_obj->getAmount(),
					'ytd_amount' => $pse_obj->getYTDAmount(),
					);
			}
		}

		if ( isset( $ps_entry_arr ) ) {
			return $ps_entry_arr;
		}

		return FALSE;
	}

	/**
	 * @group PayStub_testProRateSalary
	 */
	//Test basic salary calculation.
	function testProRateSalaryCalculation() {
		//Hire Date should be assumed to be the beginning of the day. (inclusive)
		//Termination Date should be assumed to be the end of the day. (inclusive)
		//Wage Effective Date is also assumed to be the beginning of the day (inclusive).
		//
		//So if an employee is hired and terminated on the same day, and is salary, they should get one day pay.

		//									 proRateSalary($salary, $wage_effective_date, $prev_wage_effective_date, $pp_start_date, $pp_end_date, $termination_date )
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime('01-Aug-2016'), FALSE, strtotime('01-Aug-2016'), strtotime('13-Aug-2016'), FALSE );
		$this->assertEquals( $pro_rated_salary, '1000.00' );

		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime('01-Aug-2016'), FALSE, strtotime('01-Aug-2016'), strtotime('13-Aug-2016'), FALSE, strtotime('13-Aug-2016') );
		$this->assertEquals( $pro_rated_salary, '1000.00' );

		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime('01-Aug-2016 6:00AM'), FALSE, strtotime('01-Aug-2016 12:00:00AM'), strtotime('13-Aug-2016 11:59:59PM'), FALSE, strtotime('13-Aug-2016 9:00AM') );
		$this->assertEquals( $pro_rated_salary, '1000.00' );

		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime('01-Aug-2016 6:00AM'), FALSE, strtotime('01-Aug-2016 12:00:00AM'), strtotime('10-Aug-2016 11:59:59PM'), FALSE, strtotime('10-Aug-2016 9:00AM') );
		$this->assertEquals( $pro_rated_salary, '1000.00' );

		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime('02-Aug-2016 6:00AM'), FALSE, strtotime('01-Aug-2016 12:00:00AM'), strtotime('10-Aug-2016 11:59:59PM'), FALSE, strtotime('10-Aug-2016 9:00AM') );
		$this->assertEquals( $pro_rated_salary, '900.00' );
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime('06-Aug-2016 6:00AM'), FALSE, strtotime('01-Aug-2016 12:00:00AM'), strtotime('10-Aug-2016 11:59:59PM'), FALSE, strtotime('10-Aug-2016 9:00AM') );
		$this->assertEquals( $pro_rated_salary, '500.00' );
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime('10-Aug-2016 6:00AM'), FALSE, strtotime('01-Aug-2016 12:00:00AM'), strtotime('10-Aug-2016 11:59:59PM'), FALSE, strtotime('10-Aug-2016 9:00AM') );
		$this->assertEquals( $pro_rated_salary, '100.00' );
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime('11-Aug-2016 6:00AM'), FALSE, strtotime('01-Aug-2016 12:00:00AM'), strtotime('10-Aug-2016 11:59:59PM'), FALSE, strtotime('10-Aug-2016 9:00AM') );
		$this->assertEquals( $pro_rated_salary, '0.00' );

		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime('01-Aug-2016 6:00AM'), FALSE, strtotime('01-Aug-2016 12:00:00AM'), strtotime('10-Aug-2016 11:59:59PM'), FALSE, strtotime('09-Aug-2016 9:00AM') );
		$this->assertEquals( $pro_rated_salary, '900.00' );
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime('01-Aug-2016 6:00AM'), FALSE, strtotime('01-Aug-2016 12:00:00AM'), strtotime('10-Aug-2016 11:59:59PM'), FALSE, strtotime('05-Aug-2016 9:00AM') );
		$this->assertEquals( $pro_rated_salary, '500.00' );
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime('01-Aug-2016 6:00AM'), FALSE, strtotime('01-Aug-2016 12:00:00AM'), strtotime('10-Aug-2016 11:59:59PM'), FALSE, strtotime('01-Aug-2016 9:00AM') );
		$this->assertEquals( $pro_rated_salary, '100.00' );
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime('01-Aug-2016 6:00AM'), FALSE, strtotime('01-Aug-2016 12:00:00AM'), strtotime('10-Aug-2016 11:59:59PM'), FALSE, strtotime('31-Jul-2016 9:00AM') );
		$this->assertEquals( $pro_rated_salary, '0.00' );


		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime('01-Aug-2016'), FALSE, strtotime('01-Aug-2016'), strtotime('10-Aug-2016'), strtotime('01-Aug-2016 9:00AM'), strtotime('10-Aug-2016 9:00AM') );
		$this->assertEquals( $pro_rated_salary, '1000.00' );

		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime('01-Aug-2016'), FALSE, strtotime('01-Aug-2016'), strtotime('11-Aug-2016'), strtotime('02-Aug-2016 9:00AM'), strtotime('10-Aug-2016 9:00AM') );
		$this->assertEquals( $pro_rated_salary, '900.00' );

		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime('01-Jul-2016'), FALSE, strtotime('01-Aug-2016'), strtotime('11-Aug-2016'), strtotime('02-Aug-2016 9:00AM'), strtotime('10-Aug-2016 9:00AM') );
		$this->assertEquals( $pro_rated_salary, '900.00' );

		//
		//Test changing salary in the middle of a pay period.
		//
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime('05-Aug-2016'), FALSE, strtotime('01-Aug-2016'), strtotime('11-Aug-2016'), strtotime('01-Aug-2016 9:00AM'), strtotime('11-Aug-2016 9:00AM') );
		$this->assertEquals( $pro_rated_salary, '600.00' );
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime('01-Jul-2016'), strtotime('05-Aug-2016'), strtotime('01-Aug-2016'), strtotime('11-Aug-2016'), strtotime('01-Aug-2016 9:00AM'), strtotime('10-Aug-2016 9:00AM') );
		$this->assertEquals( $pro_rated_salary, '400.00' );

		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime('01-Aug-2016'), FALSE, strtotime('01-Aug-2016'), strtotime('10-Aug-2016 11:59:59PM') );
		$this->assertEquals( $pro_rated_salary, '1000.00' );
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime('01-Aug-2016'), FALSE, strtotime('01-Aug-2016'), strtotime('10-Aug-2016 11:59:59PM'), strtotime('02-Aug-2016 9:00AM'), strtotime('08-Aug-2016 9:00AM') );
		$this->assertEquals( $pro_rated_salary, '700.00' );
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime('05-Aug-2016'), FALSE, strtotime('01-Aug-2016'), strtotime('10-Aug-2016 11:59:59PM'), strtotime('02-Aug-2016 9:00AM'), strtotime('08-Aug-2016 9:00AM') );
		$this->assertEquals( $pro_rated_salary, '400.00' );
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime('01-Jul-2016'), strtotime('05-Aug-2016'), strtotime('01-Aug-2016'), strtotime('10-Aug-2016 11:59:59PM'), strtotime('02-Aug-2016 9:00AM'), strtotime('08-Aug-2016 9:00AM') );
		$this->assertEquals( $pro_rated_salary, '300.00' );

		return TRUE;
	}

	/**
	 * @group PayStub_testSinglePayStub
	 */
	function testSinglePayStub() {
		//Test all parts of a single pay stub.

		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[0]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle


		//$pay_stub->setStartDate( $this->pay_period_objs[0]->getStartDate() );
		//$pay_stub->setEndDate( $this->pay_period_objs[0]->getEndDate() );
		//$pay_stub->setTransactionDate( $this->pay_period_objs[0]->getTransactionDate() );

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();

		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'state_unemployment' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'NY - Unemployment Insurance'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		//addEntry( $pay_stub_entry_account_id, $amount, $units = NULL, $rate = NULL, $description = NULL, $ps_amendment_id = NULL, $ytd_amount = NULL, $ytd_units = NULL) {
		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );

		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 0, NULL, NULL, NULL, NULL, 1.00 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 0, NULL, NULL, NULL, NULL, 1.00 );
		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 5.01 );

		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			Debug::text('Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10);
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '100.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '110.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '100.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][1]['amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][1]['ytd_amount'], '101.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '25.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '25.04' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][1]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][1]['ytd_amount'], '11.01' );

		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '5.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '4.01' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '211.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '212.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '135.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '136.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '25.06' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '26.06' );

		return TRUE;
	}

	/**
	 * @group PayStub_testSinglePayStubLargeAmounts
	 */
	function testSinglePayStubLargeAmounts() {
		//Test all parts of a single pay stub.

		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[0]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle

		//$pay_stub->setStartDate( $this->pay_period_objs[0]->getStartDate() );
		//$pay_stub->setEndDate( $this->pay_period_objs[0]->getEndDate() );
		//$pay_stub->setTransactionDate( $this->pay_period_objs[0]->getTransactionDate() );

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();

		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'state_unemployment' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'NY - Unemployment Insurance'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		//addEntry( $pay_stub_entry_account_id, $amount, $units = NULL, $rate = NULL, $description = NULL, $ps_amendment_id = NULL, $ytd_amount = NULL, $ytd_units = NULL) {
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10000000.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );

		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 0, NULL, NULL, NULL, NULL, 1.00 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 0, NULL, NULL, NULL, NULL, 1.00 );
		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 5.01 );

		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			Debug::text('Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10);
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '10000000.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '10000010.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '100.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][1]['amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][1]['ytd_amount'], '101.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '25.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '25.04' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][1]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][1]['ytd_amount'], '11.01' );

		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '5.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '4.01' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '10000111.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '10000112.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '10000035.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '10000036.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '25.06' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '26.06' );

		return TRUE;
	}

	/**
	 * @group PayStub_testMultiplePayStub
	 */
	function testMultiplePayStub() {
		//Test all parts of multiple pay stubs that span a year boundary.

		//Start 6 pay periods from the last one. Should be beginning/end of December,
		//Its the TRANSACTION date that counts
		$start_pay_period_id = (count($this->pay_period_objs) - 6);
		Debug::text('Starting Pay Period: '. TTDate::getDate('DATE+TIME', $this->pay_period_objs[$start_pay_period_id]->getStartDate() ), __FILE__, __LINE__, __METHOD__, 10);

		//
		// First Pay Stub
		//

		//Test UnUsed YTD entries...
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[$start_pay_period_id]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'state_unemployment' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'NY - Unemployment Insurance'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );

		//Adjust YTD balance, emulating a YTD PS amendment
		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 0, NULL, NULL, 'Vacation Accrual YTD adjustment', -1, 2.03, 0 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 5.01 );


		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			Debug::text('Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10);
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '100.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '110.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '100.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '100.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '25.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '25.04' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '0.00' ); //YTD adjustment
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '2.03' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][2]['amount'], '5.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][2]['ytd_amount'], '6.04' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '211.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '211.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '135.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '135.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '25.06' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '25.06' );

		unset($pse_arr, $pay_stub_id, $pay_stub);

		//
		//
		//
		//Second Pay Stub
		//
		//
		//
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[($start_pay_period_id + 1)]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'state_unemployment' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'NY - Unemployment Insurance'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		$pay_stub->addEntry( $pse_accounts['regular_time'], 198.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 12.01 );
		//$pay_stub->addEntry( $pse_accounts['over_time_1'], 111.02 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.03 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 53.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 27.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 13.04 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 16.09 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 6.01 );

		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			Debug::text('Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10);
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '198.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '12.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '320.04' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '100.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.03' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '2.03' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '53.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '103.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '27.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '52.08' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '13.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '23.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '16.09' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '31.14' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.03' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '6.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '11.02' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '211.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '422.09' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '80.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '155.10' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '131.00' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '266.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '29.13' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '54.19' );

		unset($pse_arr, $pay_stub_id, $pay_stub);

		//
		// Third Pay Stub
		// THIS SHOULD BE IN THE NEW YEAR, so YTD amounts are zero'd.
		//


		//Test UnUsed YTD entries...
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[($start_pay_period_id + 2)]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'state_unemployment' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'NY - Unemployment Insurance'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 5.01 );

		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			$pay_stub_id = $pay_stub->Save();
			Debug::text('Pay Stub is valid, final save, ID: '. $pay_stub_id, __FILE__, __LINE__, __METHOD__, 10);
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		//
		// IN NEW YEAR, YTD amounts are zero'd!
		//
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '100.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '110.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '100.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '100.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '25.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '25.04' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '5.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '15.03' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '211.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '211.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '135.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '135.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '25.06' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '25.06' );

		unset($pse_arr, $pay_stub_id, $pay_stub);

		return TRUE;
	}

	/**
	 * @group PayStub_testMultiplePayStubAndPayRuns
	 */
	function testMultiplePayStubAndRuns() {
		//Test all parts of multiple pay stubs that span a year boundary.

		//Start 6 pay periods from the last one. Should be beginning/end of December,
		//Its the TRANSACTION date that counts
		$start_pay_period_id = (count($this->pay_period_objs) - 6);
		Debug::text('Starting Pay Period: '. TTDate::getDate('DATE+TIME', $this->pay_period_objs[$start_pay_period_id]->getStartDate() ), __FILE__, __LINE__, __METHOD__, 10);

		//
		// First Pay Stub
		//

		//Test UnUsed YTD entries...
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[$start_pay_period_id]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle
		$pay_stub->setRun( 1 );

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'state_unemployment' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'NY - Unemployment Insurance'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );

		//Adjust YTD balance, emulating a YTD PS amendment
		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 0, NULL, NULL, 'Vacation Accrual YTD adjustment', -1, 2.03, 0 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 5.01 );


		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			Debug::text('Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10);
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '100.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '110.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '100.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '100.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '25.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '25.04' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '0.00' ); //YTD adjustment
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '2.03' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][2]['amount'], '5.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][2]['ytd_amount'], '6.04' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '211.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '211.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '135.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '135.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '25.06' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '25.06' );

		unset($pse_arr, $pay_stub_id, $pay_stub);

		//
		//
		//
		//Second Pay Stub
		//
		//
		//
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[($start_pay_period_id + 1)]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle
		$pay_stub->setRun( 1 );

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'state_unemployment' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'NY - Unemployment Insurance'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		$pay_stub->addEntry( $pse_accounts['regular_time'], 198.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 12.01 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.03 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 53.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 27.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 13.04 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 16.09 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 6.01 );

		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			Debug::text('Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10);
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '198.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '12.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '320.04' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '100.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.03' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '2.03' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '53.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '103.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '27.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '52.08' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '13.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '23.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '16.09' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '31.14' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.03' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '6.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '11.02' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '211.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '422.09' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '80.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '155.10' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '131.00' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '266.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '29.13' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '54.19' );
		unset($pse_arr, $pay_stub_id, $pay_stub);

		//
		//
		//
		//Third Pay Stub -- 2nd run in the same pay period.
		//
		//
		//
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[($start_pay_period_id + 1)]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 20 ); //Normal In-Cycle
		$pay_stub->setRun( 2 );

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'state_unemployment' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'NY - Unemployment Insurance'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		$pay_stub->addEntry( $pse_accounts['regular_time'], 1.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 1.01 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.01 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 1.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 1.01 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 1.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 1.01 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 1.01 );

		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			Debug::text('Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10);
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '1.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '1.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '322.06' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '100.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '3.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '1.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '104.03' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '1.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '53.09' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '1.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '24.06' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '1.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '32.15' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '1.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '11.02' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '3.03' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '425.12' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '2.02' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '157.12' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '1.01' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '268.00' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '2.02' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '56.21' );
		unset($pse_arr, $pay_stub_id, $pay_stub);

		//
		//
		//
		//Forth Pay Stub -- 3nd run in the same pay period.
		//
		//
		//
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[($start_pay_period_id + 1)]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 20 ); //Normal In-Cycle
		$pay_stub->setRun( 3 );

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'state_unemployment' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'NY - Unemployment Insurance'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		$pay_stub->addEntry( $pse_accounts['regular_time'], 1.02 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 1.02 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.02 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 1.02 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 1.02 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 1.02 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 1.02 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 1.02 );

		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			Debug::text('Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10);
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '324.10' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '100.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '4.06' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '105.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '54.11' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '25.08' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '33.17' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '11.02' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '3.06' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '428.18' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '2.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '159.16' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '1.02' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '269.02' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '2.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '58.25' );
		unset($pse_arr, $pay_stub_id, $pay_stub);

		//
		// Last Pay Stub
		// THIS SHOULD BE IN THE NEW YEAR, so YTD amounts are zero'd.
		//


		//Test UnUsed YTD entries...
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[($start_pay_period_id + 2)]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle
		$pay_stub->setRun( 1 );

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'state_unemployment' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'NY - Unemployment Insurance'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 5.01 );

		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			$pay_stub_id = $pay_stub->Save();
			Debug::text('Pay Stub is valid, final save, ID: '. $pay_stub_id, __FILE__, __LINE__, __METHOD__, 10);
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		//
		// IN NEW YEAR, YTD amounts are zero'd!
		//
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '100.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '110.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '100.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '100.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '25.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '25.04' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '5.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '15.03' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '211.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '211.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '135.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '135.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '25.06' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '25.06' );
		unset($pse_arr, $pay_stub_id, $pay_stub);

		return TRUE;
	}

	/**
	 * @group PayStub_testMultiplePayStubAndPayRunsB
	 */
	function testMultiplePayStubAndRunsB() {
		//Test all parts of multiple pay stubs that span a year boundary.

		//Start 6 pay periods from the last one. Should be beginning/end of December,
		//Its the TRANSACTION date that counts
		$start_pay_period_id = (count($this->pay_period_objs) - 6);
		Debug::text('Starting Pay Period: '. TTDate::getDate('DATE+TIME', $this->pay_period_objs[$start_pay_period_id]->getStartDate() ), __FILE__, __LINE__, __METHOD__, 10);

		//
		// First Pay Stub
		//

		//Test UnUsed YTD entries...
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[$start_pay_period_id]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle
		$pay_stub->setRun( 1 );

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'state_unemployment' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'NY - Unemployment Insurance'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );

		//Adjust YTD balance, emulating a YTD PS amendment
		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 0, NULL, NULL, 'Vacation Accrual YTD adjustment', -1, 2.03, 0 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 5.01 );


		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			Debug::text('Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10);
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '100.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '110.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '100.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '100.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '25.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '25.04' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '0.00' ); //YTD adjustment
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '2.03' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][2]['amount'], '5.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][2]['ytd_amount'], '6.04' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '211.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '211.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '135.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '135.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '25.06' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '25.06' );

		unset($pse_arr, $pay_stub_id, $pay_stub);

		//
		//
		//
		//Second Pay Stub
		//
		//
		//
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[($start_pay_period_id + 1)]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle
		$pay_stub->setRun( 1 );

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'state_unemployment' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'NY - Unemployment Insurance'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		$pay_stub->addEntry( $pse_accounts['regular_time'], 198.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 12.01 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.03 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 53.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 27.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 13.04 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 16.09 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 6.01 );

		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			Debug::text('Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10);
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '198.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '12.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '320.04' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '100.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.03' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '2.03' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '53.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '103.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '27.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '52.08' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '13.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '23.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '16.09' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '31.14' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.03' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '6.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '11.02' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '211.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '422.09' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '80.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '155.10' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '131.00' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '266.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '29.13' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '54.19' );
		unset($pse_arr, $pay_stub_id, $pay_stub);

		//
		//
		//
		//Third Pay Stub -- 2nd run in the same pay period.
		//
		//
		//
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[($start_pay_period_id + 1)]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 20 ); //Normal In-Cycle
		$pay_stub->setRun( 2 );

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'state_unemployment' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'NY - Unemployment Insurance'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		$pay_stub->addEntry( $pse_accounts['regular_time'], 1.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 1.01 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.01 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 1.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 1.01 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 1.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 1.01 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 1.01 );

		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			Debug::text('Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10);
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '1.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '1.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '322.06' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '100.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '3.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '1.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '104.03' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '1.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '53.09' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '1.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '24.06' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '1.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '32.15' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '1.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '11.02' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '3.03' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '425.12' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '2.02' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '157.12' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '1.01' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '268.00' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '2.02' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '56.21' );
		unset($pse_arr, $pay_stub_id, $pay_stub);

		//
		//
		//
		//
		// 2nd Last Pay Stub
		// THIS SHOULD BE IN THE NEW YEAR, so YTD amounts are zero'd.
		//
		//
		//
		//
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[($start_pay_period_id + 1)]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 20 ); //Normal In-Cycle
		$pay_stub->setRun( 3 );

		$pay_stub->setDefaultDates();

		$pay_stub->setTransactionDate( ( TTDate::getEndYearEpoch( $pay_stub->getTransactionDate() ) + 86400 ) ); //Push transaction date into new year to zero YTD values.

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'state_unemployment' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'NY - Unemployment Insurance'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		$pay_stub->addEntry( $pse_accounts['regular_time'], 1.02 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 1.02 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.02 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 1.02 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 1.02 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 1.02 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 1.02 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 1.02 );

		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			Debug::text('Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10);
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		//
		// IN NEW YEAR, YTD amounts are zero'd!
		//
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '2.04' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '1.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '1.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '11.02' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '3.06' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '3.06' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '2.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '2.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '1.02' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '1.02' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '2.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '2.04' );
		unset($pse_arr, $pay_stub_id, $pay_stub);

		//
		// Last Pay Stub
		//
		//Test UnUsed YTD entries...
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[($start_pay_period_id + 2)]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 20 ); //Normal In-Cycle
		$pay_stub->setRun( 1 );

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'state_unemployment' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'NY - Unemployment Insurance'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 5.01 );

		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			$pay_stub_id = $pay_stub->Save();
			Debug::text('Pay Stub is valid, final save, ID: '. $pay_stub_id, __FILE__, __LINE__, __METHOD__, 10);
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '100.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '112.06' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '100.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '100.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '2.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '51.03' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '25.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '26.06' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '11.03' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '16.07' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '5.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '15.03' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '211.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '214.10' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '77.09' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '135.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '137.01' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '25.06' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '27.10' );
		unset($pse_arr, $pay_stub_id, $pay_stub);

		return TRUE;
	}

	/**
	 * @group PayStub_testEditMultiplePayStub
	 */
	//Test editing pay stub in the middle of the year, and having the other pay stubs YTD re-calculated.
	function testEditMultiplePayStub() {
		//Test all parts of multiple pay stubs that span a year boundary.

		//Start 6 pay periods from the last one. Should be beginning/end of December,
		//Its the TRANSACTION date that counts
		$start_pay_period_id = (count($this->pay_period_objs) - 6);
		Debug::text('Starting Pay Period: '. TTDate::getDate('DATE+TIME', $this->pay_period_objs[$start_pay_period_id]->getStartDate() ), __FILE__, __LINE__, __METHOD__, 10);

		//
		// First Pay Stub
		//

		//Test UnUsed YTD entries...
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[$start_pay_period_id]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'state_unemployment' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'NY - Unemployment Insurance'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 5.01 );

		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			Debug::text('Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10);
			$first_pay_stub_id = $pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '100.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '110.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '100.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '100.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '25.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '25.04' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '5.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '4.01' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '211.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '211.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '135.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '135.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '25.06' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '25.06' );

		unset($pse_arr, $pay_stub_id, $pay_stub);

		//
		//
		//
		//Second Pay Stub
		//
		//
		//
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[($start_pay_period_id + 1)]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'state_unemployment' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'NY - Unemployment Insurance'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		$pay_stub->addEntry( $pse_accounts['regular_time'], 198.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 12.01 );
		//$pay_stub->addEntry( $pse_accounts['over_time_1'], 111.02 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.03 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 53.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 27.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 13.04 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 16.09 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 6.01 );

		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			Debug::text('Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10);
			$second_pay_stub_id = $pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '198.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '12.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '320.04' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '100.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.03' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '2.03' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '53.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '103.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '27.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '52.08' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '13.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '23.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '16.09' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '31.14' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.03' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '6.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '8.99' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '211.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '422.09' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '80.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '155.10' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '131.00' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '266.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '29.13' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '54.19' );

		unset($pse_arr, $pay_stub_id, $pay_stub);

		//
		// Third Pay Stub
		//

		//Test UnUsed YTD entries...
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[($start_pay_period_id + 2)]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'state_unemployment' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'NY - Unemployment Insurance'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 5.01 );

		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			$third_pay_stub_id = $pay_stub_id = $pay_stub->Save();
			Debug::text('Pay Stub is valid, final save, ID: '. $pay_stub_id, __FILE__, __LINE__, __METHOD__, 10);
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		//
		// IN NEW YEAR, YTD amounts are zero'd!
		//
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '100.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '110.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '100.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '100.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '25.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '25.04' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '5.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '13.00' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '211.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '211.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '135.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '135.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '25.06' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '25.06' );
		unset($pse_arr, $pay_stub_id, $pay_stub);


		//
		//Now edit the first pay stub.
		//
		$pslf = new PayStubListFactory();
		$pay_stub = $pslf->getByID( $first_pay_stub_id )->getCurrent();
		$pay_stub->loadPreviousPayStub();
		$pay_stub->deleteEntries( TRUE );
		$pay_stub->setEnableLinkedAccruals( FALSE );

		$pay_stub->addEntry( $pse_accounts['regular_time'], 105.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], -1.00 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 5.01 );

		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			$pay_stub->Save();

			//Recalculate all pay stubs after this one.
			$pslf = new PayStubListFactory();
			$pslf->getById( $first_pay_stub_id );
			if ( $pslf->getRecordCount() > 0 ) {
				$ps_obj = $pslf->getCurrent();
				$ps_obj->reCalculateYTD();
			}
			unset($ps_obj);

			//Debug::text('Pay Stub is valid, final save, ID: '. $pay_stub_id, __FILE__, __LINE__, __METHOD__,10);
		}

		$pse_arr = $this->getPayStubEntryArray( $first_pay_stub_id );
		//Debug::Arr($pse_arr, 'Pay Stub Entry Arr: ', __FILE__, __LINE__, __METHOD__,10);

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '105.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '115.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '100.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '100.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '25.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '25.04' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '5.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '4.01' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '216.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '216.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '140.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '140.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '25.06' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '25.06' );

		unset($pse_arr, $pay_stub_id, $pay_stub);

		//
		// Confirm YTD values in second pay stub are correct
		//
		Debug::Text('First Pay Stub ID: '. $first_pay_stub_id .' Second Pay Stub ID: '. $second_pay_stub_id, __FILE__, __LINE__, __METHOD__, 10);

		$pse_arr = $this->getPayStubEntryArray( $second_pay_stub_id );
		//Debug::Arr($pse_arr, 'Second Pay Stub Entry Arr: ', __FILE__, __LINE__, __METHOD__,10);

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '198.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '12.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '325.04' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '100.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.03' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '2.03' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '53.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '103.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '27.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '52.08' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '13.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '23.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '16.09' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '31.14' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.03' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '6.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '8.99' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '211.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '427.09' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '80.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '155.10' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '131.00' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '271.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '29.13' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '54.19' );
		unset($pse_arr, $pay_stub_id, $pay_stub);

		//
		// Confirm YTD values in third pay stub are correct
		//
		$pse_arr = $this->getPayStubEntryArray( $third_pay_stub_id );

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '100.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '110.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '100.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '100.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '25.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '25.04' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '5.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '13.00' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '211.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '211.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '135.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '135.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '25.06' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '25.06' );
		unset($pse_arr, $pay_stub_id, $pay_stub);

		return TRUE;
	}

	/**
	 * @group PayStub_testMultiplePayStubAccruals
	 */
	function testMultiplePayStubAccruals() {
		//Test all parts of multiple pay stubs that span a year boundary.

		//Start 6 pay periods from the last one. Should be beginning/end of December,
		//Its the TRANSACTION date that counts
		$start_pay_period_id = (count($this->pay_period_objs) - 8);
		Debug::text('Starting Pay Period: '. TTDate::getDate('DATE+TIME', $this->pay_period_objs[$start_pay_period_id]->getStartDate() ), __FILE__, __LINE__, __METHOD__, 10);

		//
		// First Pay Stub
		//

		//Test UnUsed YTD entries...
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[$start_pay_period_id]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'state_unemployment' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'NY - Unemployment Insurance'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );

		//Adjust YTD balance, emulating a YTD PS amendment
		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], -340.38, NULL, NULL, 'Vacation Accrual YTD adjustment', -1, 0, 0 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 6.13 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 60.03 );

		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			Debug::text('Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10);
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '100.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '110.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '100.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '100.02' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '6.13' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '6.13' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '25.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '25.04' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '15.05' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-340.38' ); //YTD adjustment
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '-6.13' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '0.00' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][2]['amount'], '60.03' ); //YTD adjustment
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][2]['ytd_amount'], '-286.48' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '216.17' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '216.17' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '141.12' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '141.12' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '25.06' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '25.06' );

		unset($pse_arr, $pay_stub_id, $pay_stub);

		//
		//
		//
		//Second Pay Stub
		//
		//
		//
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[($start_pay_period_id + 1)]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'state_unemployment' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'NY - Unemployment Insurance'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		$pay_stub->addEntry( $pse_accounts['regular_time'], 198.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 12.01 );
		//$pay_stub->addEntry( $pse_accounts['over_time_1'], 111.02 );
		//$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.03 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 53.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 27.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 13.04 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 16.09 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 240.01 );

		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			Debug::text('Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10);
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '198.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '12.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '320.04' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '100.02' );

		//$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.03' );
		//$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '2.03' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '53.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '103.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '27.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '52.08' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '13.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '23.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '16.09' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '31.14' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '240.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '-46.47' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '210.02' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '426.19' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '80.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '155.10' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '129.97' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '271.09' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '29.13' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '54.19' );

		unset($pse_arr, $pay_stub_id, $pay_stub);

		//
		// Third Pay Stub
		//


		//Test UnUsed YTD entries...
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[($start_pay_period_id + 2)]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = array(
							'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
							'over_time_1' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Over Time 1'),
							'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
							'federal_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'US - Federal Income Tax'),
							'state_income_tax' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'NY - State Income Tax'),
							'medicare' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'Medicare'),
							'state_unemployment' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 30, 'NY - Unemployment Insurance'),
							'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
							);

		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 65.01 );

		$pay_stub->setEnableProcessEntries(TRUE);
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == TRUE ) {
			$pay_stub_id = $pay_stub->Save();
			Debug::text('Pay Stub is valid, final save, ID: '. $pay_stub_id, __FILE__, __LINE__, __METHOD__, 10);
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		//
		// IN NEW YEAR, YTD amounts are zero'd!
		//
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['amount'], '100.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'], '430.06' );

		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['amount'], '100.02' );
		$this->assertEquals( $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'], '200.04' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '7.13' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'], '50.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'], '153.03' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['amount'], '25.04' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'], '77.12' );

		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['amount'], '10.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'], '33.06' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['amount'], '15.05' );
		$this->assertEquals( $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'], '46.19' );

		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'], '-1.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'], '0.00' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'], '65.01' );
		$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'], '17.54' );

		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'], '211.04' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'], '637.23' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'], '75.05' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'], '230.15' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'], '135.99' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'], '407.08' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'], '25.06' );
		$this->assertEquals( $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'], '79.25' );

		unset($pse_arr, $pay_stub_id, $pay_stub);

		return TRUE;
	}


	/**
	 * @group testRemittanceSourceAccountValidation
	 */
	function testRemittanceSourceAccountValidation() {
		$rsaf = new RemittanceSourceAccountFactory();
		$rsaf->setCompany( $this->company_id );
		$rsaf->setLegalEntity( $this->legal_entity_id );
		$rsaf->setStatus( 10 ); //Enabled
		$rsaf->setName( TTUUID::generateUUID() );
		$rsaf->setCurrency( $this->currency_id );
		$rsaf->setType( 3000 );

		//
		// US
		//
		$rsaf->setCountry( 'US' );
		$rsaf->setDataFormat( 10 ); //10=ACH
		$rsaf->setValue2( 123456789 ); //Branch/Routing
		$rsaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( $rsaf->isValid(), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 0 );
		$rsaf->Validator->resetErrors();

		$rsaf->setValue2( 123456789 ); //Branch/Routing
		$rsaf->setValue3( 12345678901234567 ); //Account Number
		$this->assertEquals( $rsaf->isValid(), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 0 );
		$rsaf->Validator->resetErrors();

		$rsaf->setValue2( '000456789' ); //Branch/Routing
		$rsaf->setValue3( '00045678901234567' ); //Account Number
		$this->assertEquals( $rsaf->isValid(), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 0 );
		$rsaf->Validator->resetErrors();

		//Some bank in Carribean (Antigua?) don't have branch/routing numbers, so we need to accept all zeros.
		$rsaf->setValue2( '000000000' ); //Branch/Routing
		$rsaf->setValue3( '00045678901234567' ); //Account Number
		$this->assertEquals( $rsaf->isValid(), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 0 );
		$rsaf->Validator->resetErrors();

		//Routing number invalid. -- Too short.
		$rsaf->setValue2( 12345678 ); //Branch/Routing
		$rsaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( $rsaf->isValid(), FALSE );
		$this->assertEquals( $rsaf->Validator->hasError( 'value2' ), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 1 );
		$rsaf->Validator->resetErrors();

		//Routing number invalid. -- Too long
		$rsaf->setValue2( 1234567891 ); //Branch/Routing
		$rsaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( $rsaf->isValid(), FALSE );
		$this->assertEquals( $rsaf->Validator->hasError( 'value2' ), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 1 );
		$rsaf->Validator->resetErrors();

		//Account number invalid. -- Too short
		$rsaf->setValue2( 123456789 ); //Branch/Routing
		$rsaf->setValue3( 12 ); //Account Number
		$this->assertEquals( $rsaf->isValid(), FALSE );
		$this->assertEquals( $rsaf->Validator->hasError( 'value3' ), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 1 );
		$rsaf->Validator->resetErrors();

		//Account number invalid. -- Too long
		$rsaf->setValue2( 123456789 ); //Branch/Routing
		$rsaf->setValue3( 123456789012345678 ); //Account Number
		$this->assertEquals( $rsaf->isValid(), FALSE );
		$this->assertEquals( $rsaf->Validator->hasError( 'value3' ), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 1 );
		$rsaf->Validator->resetErrors();

		//Using scientific notation.
		$rsaf->setValue2( '5.18E+11' ); //Branch/Routing
		$rsaf->setValue3( '5.18E+11' ); //Account Number
		$this->assertEquals( $rsaf->isValid(), FALSE );
		$this->assertEquals( $rsaf->Validator->hasError( 'value2' ), TRUE );
		$this->assertEquals( $rsaf->Validator->hasError( 'value3' ), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 2 );
		$rsaf->Validator->resetErrors();

		//Using bogus data.
		$rsaf->setValue2( '123ABC456789' ); //Branch/Routing
		$rsaf->setValue3( '123ABC456789' ); //Account Number
		$this->assertEquals( $rsaf->isValid(), FALSE );
		$this->assertEquals( $rsaf->Validator->hasError( 'value2' ), TRUE );
		$this->assertEquals( $rsaf->Validator->hasError( 'value3' ), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 2 );
		$rsaf->Validator->resetErrors();


		//
		// CA
		//
		$rsaf->setCountry( 'CA' );
		$rsaf->setDataFormat( 20 ); //20=1464
		$rsaf->setValue1( 123 ); //Institution
		$rsaf->setValue2( 12345 ); //Branch/Routing
		$rsaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( $rsaf->isValid(), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 0 );
		$rsaf->Validator->resetErrors();

		$rsaf->setValue1( 123 ); //Institution
		$rsaf->setValue2( 12345 ); //Branch/Routing
		$rsaf->setValue3( 123456789012 ); //Account Number
		$this->assertEquals( $rsaf->isValid(), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 0 );
		$rsaf->Validator->resetErrors();

		$rsaf->setValue1( '001' ); //Institution
		$rsaf->setValue2( '00045' ); //Branch/Routing
		$rsaf->setValue3( '000456789012' ); //Account Number
		$this->assertEquals( $rsaf->isValid(), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 0 );
		$rsaf->Validator->resetErrors();

		//Some bank in Carribean (Antigua?) don't have branch/routing numbers, so we need to accept all zeros.
		$rsaf->setValue1( '000' ); //Institution
		$rsaf->setValue2( '00000' ); //Branch/Routing
		$rsaf->setValue3( '000456789012' ); //Account Number
		$this->assertEquals( $rsaf->isValid(), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 0 );
		$rsaf->Validator->resetErrors();

		//Institution number invalid. -- Too short.
		$rsaf->setValue1( '01' ); //Institution
		$rsaf->setValue2( 12345 ); //Branch/Routing
		$rsaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( $rsaf->isValid(), FALSE );
		$this->assertEquals( $rsaf->Validator->hasError( 'value1' ), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 1 );
		$rsaf->Validator->resetErrors();

		//Institution number invalid. -- Too Long.
		$rsaf->setValue1( '0013' ); //Institution
		$rsaf->setValue2( 12345 ); //Branch/Routing
		$rsaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( $rsaf->isValid(), FALSE );
		$this->assertEquals( $rsaf->Validator->hasError( 'value1' ), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 1 );
		$rsaf->Validator->resetErrors();

		//Branch number invalid. -- Too short.
		$rsaf->setValue1( '001' ); //Institution
		$rsaf->setValue2( 1234 ); //Branch/Routing
		$rsaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( $rsaf->isValid(), FALSE );
		$this->assertEquals( $rsaf->Validator->hasError( 'value2' ), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 1 );
		$rsaf->Validator->resetErrors();

		//Branch number invalid. -- Too long
		$rsaf->setValue1( '001' ); //Institution
		$rsaf->setValue2( 123456 ); //Branch/Routing
		$rsaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( $rsaf->isValid(), FALSE );
		$this->assertEquals( $rsaf->Validator->hasError( 'value2' ), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 1 );
		$rsaf->Validator->resetErrors();

		//Account number invalid. -- Too short
		$rsaf->setValue1( '001' ); //Institution
		$rsaf->setValue2( 12345 ); //Branch/Routing
		$rsaf->setValue3( 12 ); //Account Number
		$this->assertEquals( $rsaf->isValid(), FALSE );
		$this->assertEquals( $rsaf->Validator->hasError( 'value3' ), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 1 );
		$rsaf->Validator->resetErrors();

		//Account number invalid. -- Too long
		$rsaf->setValue1( '001' ); //Institution
		$rsaf->setValue2( 12345 ); //Branch/Routing
		$rsaf->setValue3( 1234567890123 ); //Account Number
		$this->assertEquals( $rsaf->isValid(), FALSE );
		$this->assertEquals( $rsaf->Validator->hasError( 'value3' ), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 1 );
		$rsaf->Validator->resetErrors();

		//Using scientific notation.
		$rsaf->setValue1( '001' ); //Institution
		$rsaf->setValue2( '5.18E+11' ); //Branch/Routing
		$rsaf->setValue3( '5.18E+11' ); //Account Number
		$this->assertEquals( $rsaf->isValid(), FALSE );
		$this->assertEquals( $rsaf->Validator->hasError( 'value2' ), TRUE );
		$this->assertEquals( $rsaf->Validator->hasError( 'value3' ), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 2 );
		$rsaf->Validator->resetErrors();

		//Using bogus data.
		$rsaf->setValue1( '001' ); //Institution
		$rsaf->setValue2( '123AB' ); //Branch/Routing
		$rsaf->setValue3( '123ABC456789' ); //Account Number
		$this->assertEquals( $rsaf->isValid(), FALSE );
		$this->assertEquals( $rsaf->Validator->hasError( 'value2' ), TRUE );
		$this->assertEquals( $rsaf->Validator->hasError( 'value3' ), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 2 );
		$rsaf->Validator->resetErrors();

		//Using bogus data.
		$rsaf->setValue1( '1A1' ); //Institution
		$rsaf->setValue2( '123AB' ); //Branch/Routing
		$rsaf->setValue3( '123ABC456789' ); //Account Number
		$this->assertEquals( $rsaf->isValid(), FALSE );
		$this->assertEquals( $rsaf->Validator->hasError( 'value1' ), TRUE );
		$this->assertEquals( $rsaf->Validator->hasError( 'value2' ), TRUE );
		$this->assertEquals( $rsaf->Validator->hasError( 'value3' ), TRUE );
		$this->assertEquals( count( $rsaf->Validator->getErrorsArray() ), 3 );
		$rsaf->Validator->resetErrors();
	}

	/**
	 * @group testRemittanceDestinationAccountValidation
	 */
	function testRemittanceDestinationAccountValidation() {
		//
		// US
		//
		$rsaf = new RemittanceSourceAccountFactory();
		$rsaf->setCompany( $this->company_id );
		$rsaf->setLegalEntity( $this->legal_entity_id );
		$rsaf->setStatus( 10 ); //Enabled
		$rsaf->setName( TTUUID::generateUUID() );
		$rsaf->setCurrency( $this->currency_id );
		$rsaf->setType( 3000 );
		$rsaf->setCountry( 'US' );
		$rsaf->setDataFormat( 10 ); //10=ACH
		$rsaf->setValue2( 123456789 ); //Branch/Routing
		$rsaf->setValue3( 123456789 ); //Account Number
		$remittance_source_account_id = $rsaf->Save();


		$rdaf = new RemittanceDestinationAccountFactory();
		$rdaf->setUser( $this->user_id );
		$rdaf->setRemittanceSourceAccount( $remittance_source_account_id );
		$rdaf->setStatus( 10 ); //Enabled
		$rdaf->setName( TTUUID::generateUUID() );
		$rdaf->setType( 3000 );
		$rdaf->setAmountType( 10 ); //10=Percent
		$rdaf->setPercentAmount( 100 ); //100%
		$rdaf->setPriority( 5 );
		$rdaf->setValue1( 22 ); //22=Checkings


		$rdaf->setValue2( 123456789 ); //Branch/Routing
		$rdaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( $rdaf->isValid(), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 0 );
		$rdaf->Validator->resetErrors();

		$rdaf->setValue2( 123456789 ); //Branch/Routing
		$rdaf->setValue3( 12345678901234567 ); //Account Number
		$this->assertEquals( $rdaf->isValid(), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 0 );
		$rdaf->Validator->resetErrors();

		$rdaf->setValue2( '000456789' ); //Branch/Routing
		$rdaf->setValue3( '00045678901234567' ); //Account Number
		$this->assertEquals( $rdaf->isValid(), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 0 );
		$rdaf->Validator->resetErrors();

		//Some bank in Carribean (Antigua?) don't have branch/routing numbers, so we need to accept all zeros.
		$rdaf->setValue2( '000000000' ); //Branch/Routing
		$rdaf->setValue3( '00045678901234567' ); //Account Number
		$this->assertEquals( $rdaf->isValid(), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 0 );
		$rdaf->Validator->resetErrors();

		//Routing number invalid. -- Too short.
		$rdaf->setValue2( 12345678 ); //Branch/Routing
		$rdaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( $rdaf->isValid(), FALSE );
		$this->assertEquals( $rdaf->Validator->hasError( 'value2' ), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 1 );
		$rdaf->Validator->resetErrors();

		//Routing number invalid. -- Too long
		$rdaf->setValue2( 1234567891 ); //Branch/Routing
		$rdaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( $rdaf->isValid(), FALSE );
		$this->assertEquals( $rdaf->Validator->hasError( 'value2' ), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 1 );
		$rdaf->Validator->resetErrors();

		//Account number invalid. -- Too short
		$rdaf->setValue2( 123456789 ); //Branch/Routing
		$rdaf->setValue3( 12 ); //Account Number
		$this->assertEquals( $rdaf->isValid(), FALSE );
		$this->assertEquals( $rdaf->Validator->hasError( 'value3' ), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 1 );
		$rdaf->Validator->resetErrors();

		//Account number invalid. -- Too long
		$rdaf->setValue2( 123456789 ); //Branch/Routing
		$rdaf->setValue3( 123456789012345678 ); //Account Number
		$this->assertEquals( $rdaf->isValid(), FALSE );
		$this->assertEquals( $rdaf->Validator->hasError( 'value3' ), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 1 );
		$rdaf->Validator->resetErrors();

		//Using scientific notation.
		$rdaf->setValue2( '5.18E+11' ); //Branch/Routing
		$rdaf->setValue3( '5.18E+11' ); //Account Number
		$this->assertEquals( $rdaf->isValid(), FALSE );
		$this->assertEquals( $rdaf->Validator->hasError( 'value2' ), TRUE );
		$this->assertEquals( $rdaf->Validator->hasError( 'value3' ), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 2 );
		$rdaf->Validator->resetErrors();

		//Using bogus data.
		$rdaf->setValue2( '123ABC456789' ); //Branch/Routing
		$rdaf->setValue3( '123ABC456789' ); //Account Number
		$this->assertEquals( $rdaf->isValid(), FALSE );
		$this->assertEquals( $rdaf->Validator->hasError( 'value2' ), TRUE );
		$this->assertEquals( $rdaf->Validator->hasError( 'value3' ), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 2 );
		$rdaf->Validator->resetErrors();


		//
		// CA
		//
		$rsaf = new RemittanceSourceAccountFactory();
		$rsaf->setCompany( $this->company_id );
		$rsaf->setLegalEntity( $this->legal_entity_id );
		$rsaf->setStatus( 10 ); //Enabled
		$rsaf->setName( TTUUID::generateUUID() );
		$rsaf->setCurrency( $this->currency_id );
		$rsaf->setType( 3000 );
		$rsaf->setCountry( 'CA' );
		$rsaf->setDataFormat( 20 ); //10=1464
		$rsaf->setValue1( 123 ); //Institution
		$rsaf->setValue2( 12345 ); //Transit/Branch / Routing
		$rsaf->setValue3( 123456789 ); //Account Number
		$remittance_source_account_id = $rsaf->Save();


		$rdaf = new RemittanceDestinationAccountFactory();
		$rdaf->setUser( $this->user_id );
		$rdaf->setRemittanceSourceAccount( $remittance_source_account_id );
		$rdaf->setStatus( 10 ); //Enabled
		$rdaf->setName( TTUUID::generateUUID() );
		$rdaf->setType( 3000 );
		$rdaf->setAmountType( 10 ); //10=Percent
		$rdaf->setPercentAmount( 100 ); //100%
		$rdaf->setPriority( 5 );

		$rdaf->setValue1( 123 ); //Institution
		$rdaf->setValue2( 12345 ); //Branch/Routing
		$rdaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( $rdaf->isValid(), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 0 );
		$rdaf->Validator->resetErrors();

		$rdaf->setValue1( 123 ); //Institution
		$rdaf->setValue2( 12345 ); //Branch/Routing
		$rdaf->setValue3( 123456789012 ); //Account Number
		$this->assertEquals( $rdaf->isValid(), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 0 );
		$rdaf->Validator->resetErrors();

		$rdaf->setValue1( '001' ); //Institution
		$rdaf->setValue2( '00045' ); //Branch/Routing
		$rdaf->setValue3( '000456789012' ); //Account Number
		$this->assertEquals( $rdaf->isValid(), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 0 );
		$rdaf->Validator->resetErrors();

		//Some bank in Carribean (Antigua?) don't have branch/routing numbers, so we need to accept all zeros.
		$rdaf->setValue1( '000' ); //Institution
		$rdaf->setValue2( '00000' ); //Branch/Routing
		$rdaf->setValue3( '000456789012' ); //Account Number
		$this->assertEquals( $rdaf->isValid(), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 0 );
		$rdaf->Validator->resetErrors();

		//Institution number invalid. -- Too short.
		$rdaf->setValue1( '01' ); //Institution
		$rdaf->setValue2( 12345 ); //Branch/Routing
		$rdaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( $rdaf->isValid(), FALSE );
		$this->assertEquals( $rdaf->Validator->hasError( 'value1' ), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 1 );
		$rdaf->Validator->resetErrors();

		//Institution number invalid. -- Too Long.
		$rdaf->setValue1( '0013' ); //Institution
		$rdaf->setValue2( 12345 ); //Branch/Routing
		$rdaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( $rdaf->isValid(), FALSE );
		$this->assertEquals( $rdaf->Validator->hasError( 'value1' ), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 1 );
		$rdaf->Validator->resetErrors();

		//Branch number invalid. -- Too short.
		$rdaf->setValue1( '001' ); //Institution
		$rdaf->setValue2( 1234 ); //Branch/Routing
		$rdaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( $rdaf->isValid(), FALSE );
		$this->assertEquals( $rdaf->Validator->hasError( 'value2' ), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 1 );
		$rdaf->Validator->resetErrors();

		//Branch number invalid. -- Too long
		$rdaf->setValue1( '001' ); //Institution
		$rdaf->setValue2( 123456 ); //Branch/Routing
		$rdaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( $rdaf->isValid(), FALSE );
		$this->assertEquals( $rdaf->Validator->hasError( 'value2' ), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 1 );
		$rdaf->Validator->resetErrors();

		//Account number invalid. -- Too short
		$rdaf->setValue1( '001' ); //Institution
		$rdaf->setValue2( 12345 ); //Branch/Routing
		$rdaf->setValue3( 12 ); //Account Number
		$this->assertEquals( $rdaf->isValid(), FALSE );
		$this->assertEquals( $rdaf->Validator->hasError( 'value3' ), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 1 );
		$rdaf->Validator->resetErrors();

		//Account number invalid. -- Too long
		$rdaf->setValue1( '001' ); //Institution
		$rdaf->setValue2( 12345 ); //Branch/Routing
		$rdaf->setValue3( 1234567890123 ); //Account Number
		$this->assertEquals( $rdaf->isValid(), FALSE );
		$this->assertEquals( $rdaf->Validator->hasError( 'value3' ), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 1 );
		$rdaf->Validator->resetErrors();

		//Using scientific notation.
		$rdaf->setValue1( '001' ); //Institution
		$rdaf->setValue2( '5.18E+11' ); //Branch/Routing
		$rdaf->setValue3( '5.18E+11' ); //Account Number
		$this->assertEquals( $rdaf->isValid(), FALSE );
		$this->assertEquals( $rdaf->Validator->hasError( 'value2' ), TRUE );
		$this->assertEquals( $rdaf->Validator->hasError( 'value3' ), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 2 );
		$rdaf->Validator->resetErrors();

		//Using bogus data.
		$rdaf->setValue1( '001' ); //Institution
		$rdaf->setValue2( '123AB' ); //Branch/Routing
		$rdaf->setValue3( '123ABC456789' ); //Account Number
		$this->assertEquals( $rdaf->isValid(), FALSE );
		$this->assertEquals( $rdaf->Validator->hasError( 'value2' ), TRUE );
		$this->assertEquals( $rdaf->Validator->hasError( 'value3' ), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 2 );
		$rdaf->Validator->resetErrors();

		//Using bogus data.
		$rdaf->setValue1( '1A1' ); //Institution
		$rdaf->setValue2( '123AB' ); //Branch/Routing
		$rdaf->setValue3( '123ABC456789' ); //Account Number
		$this->assertEquals( $rdaf->isValid(), FALSE );
		$this->assertEquals( $rdaf->Validator->hasError( 'value1' ), TRUE );
		$this->assertEquals( $rdaf->Validator->hasError( 'value2' ), TRUE );
		$this->assertEquals( $rdaf->Validator->hasError( 'value3' ), TRUE );
		$this->assertEquals( count( $rdaf->Validator->getErrorsArray() ), 3 );
		$rdaf->Validator->resetErrors();
	}
}
?>