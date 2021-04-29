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

class PayStubTest extends PHPUnit\Framework\TestCase {
	protected $company_id = null;
	protected $user_id = null;
	protected $pay_period_schedule_id = null;
	protected $pay_period_objs = null;
	protected $pay_stub_account_link_arr = null;

	public function setUp(): void {
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		$dd = new DemoData();
		$dd->setEnableQuickPunch( false ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_' . uniqid( null, true ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		$this->legal_entity_id = $dd->createLegalEntity( $this->company_id, 10 );
		Debug::text( 'Company ID: ' . $this->company_id, __FILE__, __LINE__, __METHOD__, 10 );

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
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	function getPayStubAccountLinkArray() {
		$this->pay_stub_account_link_arr = [
				'total_gross'           => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Total Gross' ),
				'total_deductions'      => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Total Deductions' ),
				'employer_contribution' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Employer Total Contributions' ),
				'net_pay'               => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Net Pay' ),
				'regular_time'          => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
		];

		return true;
	}

	function createPayPeriodSchedule() {
		$ppsf = new PayPeriodScheduleFactory();

		$ppsf->setCompany( $this->company_id );
		//$ppsf->setName( 'Bi-Weekly'.rand(1000,9999) );
		$ppsf->setName( 'Bi-Weekly' );
		$ppsf->setDescription( 'Pay every two weeks' );
		$ppsf->setType( 20 );
		$ppsf->setStartWeekDay( 0 );


		$anchor_date = TTDate::getBeginWeekEpoch( strtotime( '01-Jan-06' ) ); //Start 6 weeks ago

		$ppsf->setAnchorDate( $anchor_date );

		$ppsf->setStartDayOfWeek( TTDate::getDayOfWeek( $anchor_date ) );
		$ppsf->setTransactionDate( 7 );

		$ppsf->setTransactionDateBusinessDay( true );
		$ppsf->setTimeZone( 'America/Vancouver' );

		$ppsf->setDayStartTime( 0 );
		$ppsf->setNewDayTriggerTime( ( 4 * 3600 ) );
		$ppsf->setMaximumShiftTime( ( 16 * 3600 ) );

		$ppsf->setEnableInitialPayPeriods( false );
		if ( $ppsf->isValid() ) {
			$insert_id = $ppsf->Save( false );
			Debug::Text( 'Pay Period Schedule ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			$ppsf->setUser( [ $this->user_id ] );
			$ppsf->Save();

			$this->pay_period_schedule_id = $insert_id;

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Pay Period Schedule!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function createPayPeriods() {
		$max_pay_periods = 29;

		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getById( $this->pay_period_schedule_id );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pps_obj = $ppslf->getCurrent();

			$end_date = null;
			for ( $i = 0; $i < $max_pay_periods; $i++ ) {
				if ( $i == 0 ) {
					//$end_date = TTDate::getBeginYearEpoch();
					$end_date = ( strtotime( '01-Jan-06' ) - 86400 );
				} else {
					$end_date = TTDate::incrementDate( $end_date, 14, 'day' );
				}

				Debug::Text( 'I: ' . $i . ' End Date: ' . TTDate::getDate( 'DATE+TIME', $end_date ), __FILE__, __LINE__, __METHOD__, 10 );

				$pps_obj->createNextPayPeriod( $end_date, ( 86400 + 3600 ), false ); //Don't import punches, as that causes deadlocks when running tests in parallel.
			}
		}

		return true;
	}

	function getAllPayPeriods() {
		$pplf = new PayPeriodListFactory();
		//$pplf->getByCompanyId( $this->company_id );
		$pplf->getByPayPeriodScheduleId( $this->pay_period_schedule_id );
		if ( $pplf->getRecordCount() > 0 ) {
			foreach ( $pplf as $pp_obj ) {
				Debug::text( 'Pay Period... Start: ' . TTDate::getDate( 'DATE+TIME', $pp_obj->getStartDate() ) . ' End: ' . TTDate::getDate( 'DATE+TIME', $pp_obj->getEndDate() ), __FILE__, __LINE__, __METHOD__, 10 );

				$this->pay_period_objs[] = $pp_obj;
			}
		}

		$this->pay_period_objs = array_reverse( $this->pay_period_objs );

		return true;
	}

	function getPayStubEntryArray( $pay_stub_id ) {
		//Check Pay Stub to make sure it was created correctly.
		$pself = new PayStubEntryListFactory();
		$pself->getByPayStubId( $pay_stub_id );
		if ( $pself->getRecordCount() > 0 ) {
			foreach ( $pself as $pse_obj ) {
				$ps_entry_arr[$pse_obj->getPayStubEntryNameId()][] = [
						'amount'     => Misc::MoneyRound( $pse_obj->getAmount() ),
						'ytd_amount' => Misc::MoneyRound( $pse_obj->getYTDAmount() ),
				];
			}
		}

		if ( isset( $ps_entry_arr ) ) {
			return $ps_entry_arr;
		}

		return false;
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
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime( '01-Aug-2016' ), false, strtotime( '01-Aug-2016' ), strtotime( '13-Aug-2016' ), false );
		$this->assertEquals( '1000.00', Misc::MoneyRound( $pro_rated_salary ) );

		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime( '01-Aug-2016' ), false, strtotime( '01-Aug-2016' ), strtotime( '13-Aug-2016' ), false, strtotime( '13-Aug-2016' ) );
		$this->assertEquals( '1000.00', Misc::MoneyRound( $pro_rated_salary ) );

		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime( '01-Aug-2016 6:00AM' ), false, strtotime( '01-Aug-2016 12:00:00AM' ), strtotime( '13-Aug-2016 11:59:59PM' ), false, strtotime( '13-Aug-2016 9:00AM' ) );
		$this->assertEquals( '1000.00', Misc::MoneyRound( $pro_rated_salary ) );

		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime( '01-Aug-2016 6:00AM' ), false, strtotime( '01-Aug-2016 12:00:00AM' ), strtotime( '10-Aug-2016 11:59:59PM' ), false, strtotime( '10-Aug-2016 9:00AM' ) );
		$this->assertEquals( '1000.00', Misc::MoneyRound( $pro_rated_salary ) );

		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime( '02-Aug-2016 6:00AM' ), false, strtotime( '01-Aug-2016 12:00:00AM' ), strtotime( '10-Aug-2016 11:59:59PM' ), false, strtotime( '10-Aug-2016 9:00AM' ) );
		$this->assertEquals( '900.00', Misc::MoneyRound( $pro_rated_salary ) );
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime( '06-Aug-2016 6:00AM' ), false, strtotime( '01-Aug-2016 12:00:00AM' ), strtotime( '10-Aug-2016 11:59:59PM' ), false, strtotime( '10-Aug-2016 9:00AM' ) );
		$this->assertEquals( '500.00', Misc::MoneyRound( $pro_rated_salary ) );
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime( '10-Aug-2016 6:00AM' ), false, strtotime( '01-Aug-2016 12:00:00AM' ), strtotime( '10-Aug-2016 11:59:59PM' ), false, strtotime( '10-Aug-2016 9:00AM' ) );
		$this->assertEquals( '100.00', Misc::MoneyRound( $pro_rated_salary ) );
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime( '11-Aug-2016 6:00AM' ), false, strtotime( '01-Aug-2016 12:00:00AM' ), strtotime( '10-Aug-2016 11:59:59PM' ), false, strtotime( '10-Aug-2016 9:00AM' ) );
		$this->assertEquals( '0.00', Misc::MoneyRound( $pro_rated_salary ) );

		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime( '01-Aug-2016 6:00AM' ), false, strtotime( '01-Aug-2016 12:00:00AM' ), strtotime( '10-Aug-2016 11:59:59PM' ), false, strtotime( '09-Aug-2016 9:00AM' ) );
		$this->assertEquals( '900.00', Misc::MoneyRound( $pro_rated_salary ) );
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime( '01-Aug-2016 6:00AM' ), false, strtotime( '01-Aug-2016 12:00:00AM' ), strtotime( '10-Aug-2016 11:59:59PM' ), false, strtotime( '05-Aug-2016 9:00AM' ) );
		$this->assertEquals( '500.00', Misc::MoneyRound( $pro_rated_salary ) );
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime( '01-Aug-2016 6:00AM' ), false, strtotime( '01-Aug-2016 12:00:00AM' ), strtotime( '10-Aug-2016 11:59:59PM' ), false, strtotime( '01-Aug-2016 9:00AM' ) );
		$this->assertEquals( '100.00', Misc::MoneyRound( $pro_rated_salary ) );
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime( '01-Aug-2016 6:00AM' ), false, strtotime( '01-Aug-2016 12:00:00AM' ), strtotime( '10-Aug-2016 11:59:59PM' ), false, strtotime( '31-Jul-2016 9:00AM' ) );
		$this->assertEquals( '0.00', Misc::MoneyRound( $pro_rated_salary ) );


		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime( '01-Aug-2016' ), false, strtotime( '01-Aug-2016' ), strtotime( '10-Aug-2016' ), strtotime( '01-Aug-2016 9:00AM' ), strtotime( '10-Aug-2016 9:00AM' ) );
		$this->assertEquals( '1000.00', Misc::MoneyRound( $pro_rated_salary ) );

		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime( '01-Aug-2016' ), false, strtotime( '01-Aug-2016' ), strtotime( '11-Aug-2016' ), strtotime( '02-Aug-2016 9:00AM' ), strtotime( '10-Aug-2016 9:00AM' ) );
		$this->assertEquals( '900.00', Misc::MoneyRound( $pro_rated_salary ) );

		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime( '01-Jul-2016' ), false, strtotime( '01-Aug-2016' ), strtotime( '11-Aug-2016' ), strtotime( '02-Aug-2016 9:00AM' ), strtotime( '10-Aug-2016 9:00AM' ) );
		$this->assertEquals( '900.00', Misc::MoneyRound( $pro_rated_salary ) );

		//
		//Test changing salary in the middle of a pay period.
		//
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime( '05-Aug-2016' ), false, strtotime( '01-Aug-2016' ), strtotime( '11-Aug-2016' ), strtotime( '01-Aug-2016 9:00AM' ), strtotime( '11-Aug-2016 9:00AM' ) );
		$this->assertEquals( '600.00', Misc::MoneyRound( $pro_rated_salary ) );
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime( '01-Jul-2016' ), strtotime( '05-Aug-2016' ), strtotime( '01-Aug-2016' ), strtotime( '11-Aug-2016' ), strtotime( '01-Aug-2016 9:00AM' ), strtotime( '10-Aug-2016 9:00AM' ) );
		$this->assertEquals( '400.00', Misc::MoneyRound( $pro_rated_salary ) );

		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime( '01-Aug-2016' ), false, strtotime( '01-Aug-2016' ), strtotime( '10-Aug-2016 11:59:59PM' ) );
		$this->assertEquals( '1000.00', Misc::MoneyRound( $pro_rated_salary ) );
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime( '01-Aug-2016' ), false, strtotime( '01-Aug-2016' ), strtotime( '10-Aug-2016 11:59:59PM' ), strtotime( '02-Aug-2016 9:00AM' ), strtotime( '08-Aug-2016 9:00AM' ) );
		$this->assertEquals( '700.00', Misc::MoneyRound( $pro_rated_salary ) );
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime( '05-Aug-2016' ), false, strtotime( '01-Aug-2016' ), strtotime( '10-Aug-2016 11:59:59PM' ), strtotime( '02-Aug-2016 9:00AM' ), strtotime( '08-Aug-2016 9:00AM' ) );
		$this->assertEquals( '400.00', Misc::MoneyRound( $pro_rated_salary ) );
		$pro_rated_salary = UserWageFactory::proRateSalary( 1000.00, strtotime( '01-Jul-2016' ), strtotime( '05-Aug-2016' ), strtotime( '01-Aug-2016' ), strtotime( '10-Aug-2016 11:59:59PM' ), strtotime( '02-Aug-2016 9:00AM' ), strtotime( '08-Aug-2016 9:00AM' ) );
		$this->assertEquals( '300.00', Misc::MoneyRound( $pro_rated_salary ) );

		return true;
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

		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'state_unemployment'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'NY - Unemployment Insurance' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		//addEntry( $pay_stub_entry_account_id, $amount, $units = NULL, $rate = NULL, $description = NULL, $ps_amendment_id = NULL, $ytd_amount = NULL, $ytd_units = NULL) {
		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );

		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 0, null, null, null, null, 1.00 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 0, null, null, null, null, 1.00 );
		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 5.01 );

		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			Debug::text( 'Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10 );
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( '100.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '110.02', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['over_time_1']][1]['amount'] );
		$this->assertEquals( '101.02', $pse_arr[$pse_accounts['over_time_1']][1]['ytd_amount'] );

		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][1]['amount'] );
		$this->assertEquals( '11.01', $pse_arr[$pse_accounts['medicare']][1]['ytd_amount'] );

		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );
		$this->assertEquals( '-1.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );
		$this->assertEquals( '5.01', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
		$this->assertEquals( '4.01', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '211.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '212.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '135.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '136.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '26.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );

		return true;
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

		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'state_unemployment'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'NY - Unemployment Insurance' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		//addEntry( $pay_stub_entry_account_id, $amount, $units = NULL, $rate = NULL, $description = NULL, $ps_amendment_id = NULL, $ytd_amount = NULL, $ytd_units = NULL) {
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10000000.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );

		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 0, null, null, null, null, 1.00 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 0, null, null, null, null, 1.00 );
		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 5.01 );

		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			Debug::text( 'Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10 );
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( '10000000.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '10000010.02', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['over_time_1']][1]['amount'] );
		$this->assertEquals( '101.02', $pse_arr[$pse_accounts['over_time_1']][1]['ytd_amount'] );

		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][1]['amount'] );
		$this->assertEquals( '11.01', $pse_arr[$pse_accounts['medicare']][1]['ytd_amount'] );

		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );
		$this->assertEquals( '-1.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );
		$this->assertEquals( '5.01', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
		$this->assertEquals( '4.01', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '10000111.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '10000112.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '10000035.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '10000036.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '26.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );

		return true;
	}

	/**
	 * @group PayStub_testMultiplePayStub
	 */
	function testMultiplePayStub() {
		//Test all parts of multiple pay stubs that span a year boundary.

		//Start 6 pay periods from the last one. Should be beginning/end of December,
		//Its the TRANSACTION date that counts
		$start_pay_period_id = ( count( $this->pay_period_objs ) - 6 );
		Debug::text( 'Starting Pay Period: ' . TTDate::getDate( 'DATE+TIME', $this->pay_period_objs[$start_pay_period_id]->getStartDate() ), __FILE__, __LINE__, __METHOD__, 10 );

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
		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'state_unemployment'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'NY - Unemployment Insurance' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );

		//Adjust YTD balance, emulating a YTD PS amendment
		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 0, null, null, 'Vacation Accrual YTD adjustment', -1, 2.03, 0 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 5.01 );


		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			Debug::text( 'Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10 );
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( '100.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '110.02', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );
		$this->assertEquals( '-1.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );

		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] ); //YTD adjustment
		$this->assertEquals( '2.03', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '5.01', $pse_arr[$pse_accounts['vacation_accrual']][2]['amount'] );
		$this->assertEquals( '6.04', $pse_arr[$pse_accounts['vacation_accrual']][2]['ytd_amount'] );

		$this->assertEquals( '211.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '211.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '135.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '135.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );

		unset( $pse_arr, $pay_stub_id, $pay_stub );

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
		$pay_stub->setPayPeriod( $this->pay_period_objs[( $start_pay_period_id + 1 )]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'state_unemployment'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'NY - Unemployment Insurance' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		$pay_stub->addEntry( $pse_accounts['regular_time'], 198.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 12.01 );
		//$pay_stub->addEntry( $pse_accounts['over_time_1'], 111.02 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.03 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 53.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 27.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 13.04 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 16.09 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 6.01 );

		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			Debug::text( 'Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10 );
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( '198.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '12.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '320.04', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		$this->assertEquals( '1.03', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '2.03', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '53.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '103.02', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '27.04', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '52.08', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '13.04', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '23.05', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '16.09', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '31.14', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );
		$this->assertEquals( '-1.03', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );
		$this->assertEquals( '6.01', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
		$this->assertEquals( '11.02', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '211.05', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '422.09', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '80.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '155.10', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '131.00', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '266.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '29.13', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '54.19', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );

		unset( $pse_arr, $pay_stub_id, $pay_stub );

		//
		// Third Pay Stub
		// THIS SHOULD BE IN THE NEW YEAR, so YTD amounts are zero'd.
		//


		//Test UnUsed YTD entries...
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[( $start_pay_period_id + 2 )]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'state_unemployment'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'NY - Unemployment Insurance' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 5.01 );

		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			$pay_stub_id = $pay_stub->Save();
			Debug::text( 'Pay Stub is valid, final save, ID: ' . $pay_stub_id, __FILE__, __LINE__, __METHOD__, 10 );
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		//
		// IN NEW YEAR, YTD amounts are zero'd!
		//
		$this->assertEquals( '100.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '110.02', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );
		$this->assertEquals( '-1.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );
		$this->assertEquals( '5.01', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
		$this->assertEquals( '15.03', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '211.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '211.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '135.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '135.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );

		unset( $pse_arr, $pay_stub_id, $pay_stub );

		return true;
	}

	/**
	 * @group PayStub_testMultiplePayStubAndPayRuns
	 */
	function testMultiplePayStubAndRuns() {
		//Test all parts of multiple pay stubs that span a year boundary.

		//Start 6 pay periods from the last one. Should be beginning/end of December,
		//Its the TRANSACTION date that counts
		$start_pay_period_id = ( count( $this->pay_period_objs ) - 6 );
		Debug::text( 'Starting Pay Period: ' . TTDate::getDate( 'DATE+TIME', $this->pay_period_objs[$start_pay_period_id]->getStartDate() ), __FILE__, __LINE__, __METHOD__, 10 );

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
		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'state_unemployment'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'NY - Unemployment Insurance' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );

		//Adjust YTD balance, emulating a YTD PS amendment
		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 0, null, null, 'Vacation Accrual YTD adjustment', -1, 2.03, 0 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 5.01 );


		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			Debug::text( 'Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10 );
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( '100.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '110.02', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );
		$this->assertEquals( '-1.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );

		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] ); //YTD adjustment
		$this->assertEquals( '2.03', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '5.01', $pse_arr[$pse_accounts['vacation_accrual']][2]['amount'] );
		$this->assertEquals( '6.04', $pse_arr[$pse_accounts['vacation_accrual']][2]['ytd_amount'] );

		$this->assertEquals( '211.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '211.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '135.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '135.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );

		unset( $pse_arr, $pay_stub_id, $pay_stub );

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
		$pay_stub->setPayPeriod( $this->pay_period_objs[( $start_pay_period_id + 1 )]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle
		$pay_stub->setRun( 1 );

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'state_unemployment'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'NY - Unemployment Insurance' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		$pay_stub->addEntry( $pse_accounts['regular_time'], 198.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 12.01 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.03 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 53.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 27.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 13.04 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 16.09 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 6.01 );

		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			Debug::text( 'Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10 );
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( '198.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '12.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '320.04', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		$this->assertEquals( '1.03', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '2.03', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '53.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '103.02', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '27.04', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '52.08', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '13.04', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '23.05', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '16.09', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '31.14', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );
		$this->assertEquals( '-1.03', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );
		$this->assertEquals( '6.01', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
		$this->assertEquals( '11.02', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '211.05', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '422.09', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '80.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '155.10', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '131.00', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '266.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '29.13', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '54.19', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );
		unset( $pse_arr, $pay_stub_id, $pay_stub );

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
		$pay_stub->setPayPeriod( $this->pay_period_objs[( $start_pay_period_id + 1 )]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 20 ); //Normal In-Cycle
		$pay_stub->setRun( 2 );

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'state_unemployment'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'NY - Unemployment Insurance' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		$pay_stub->addEntry( $pse_accounts['regular_time'], 1.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 1.01 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.01 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 1.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 1.01 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 1.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 1.01 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 1.01 );

		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			Debug::text( 'Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10 );
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( '1.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '1.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '322.06', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		$this->assertEquals( '1.01', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '3.04', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '1.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '104.03', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '1.01', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '53.09', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '1.01', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '24.06', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '1.01', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '32.15', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );
		$this->assertEquals( '-1.01', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );
		$this->assertEquals( '1.01', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
		$this->assertEquals( '11.02', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '3.03', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '425.12', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '2.02', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '157.12', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '1.01', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '268.00', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '2.02', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '56.21', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );
		unset( $pse_arr, $pay_stub_id, $pay_stub );

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
		$pay_stub->setPayPeriod( $this->pay_period_objs[( $start_pay_period_id + 1 )]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 20 ); //Normal In-Cycle
		$pay_stub->setRun( 3 );

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'state_unemployment'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'NY - Unemployment Insurance' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		$pay_stub->addEntry( $pse_accounts['regular_time'], 1.02 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 1.02 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.02 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 1.02 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 1.02 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 1.02 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 1.02 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 1.02 );

		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			Debug::text( 'Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10 );
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( '1.02', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '1.02', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '324.10', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		$this->assertEquals( '1.02', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '4.06', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '1.02', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '105.05', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '1.02', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '54.11', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '1.02', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '25.08', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '1.02', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '33.17', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );
		$this->assertEquals( '-1.02', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );
		$this->assertEquals( '1.02', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
		$this->assertEquals( '11.02', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '3.06', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '428.18', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '2.04', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '159.16', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '1.02', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '269.02', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '2.04', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '58.25', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );
		unset( $pse_arr, $pay_stub_id, $pay_stub );

		//
		// Last Pay Stub
		// THIS SHOULD BE IN THE NEW YEAR, so YTD amounts are zero'd.
		//


		//Test UnUsed YTD entries...
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[( $start_pay_period_id + 2 )]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle
		$pay_stub->setRun( 1 );

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'state_unemployment'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'NY - Unemployment Insurance' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 5.01 );

		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			$pay_stub_id = $pay_stub->Save();
			Debug::text( 'Pay Stub is valid, final save, ID: ' . $pay_stub_id, __FILE__, __LINE__, __METHOD__, 10 );
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		//
		// IN NEW YEAR, YTD amounts are zero'd!
		//
		$this->assertEquals( '100.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '110.02', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );
		$this->assertEquals( '-1.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );
		$this->assertEquals( '5.01', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
		$this->assertEquals( '15.03', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '211.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '211.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '135.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '135.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );
		unset( $pse_arr, $pay_stub_id, $pay_stub );

		return true;
	}

	/**
	 * @group PayStub_testMultiplePayStubAndPayRunsB
	 */
	function testMultiplePayStubAndRunsB() {
		//Test all parts of multiple pay stubs that span a year boundary.

		//Start 6 pay periods from the last one. Should be beginning/end of December,
		//Its the TRANSACTION date that counts
		$start_pay_period_id = ( count( $this->pay_period_objs ) - 6 );
		Debug::text( 'Starting Pay Period: ' . TTDate::getDate( 'DATE+TIME', $this->pay_period_objs[$start_pay_period_id]->getStartDate() ), __FILE__, __LINE__, __METHOD__, 10 );

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
		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'state_unemployment'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'NY - Unemployment Insurance' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );

		//Adjust YTD balance, emulating a YTD PS amendment
		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 0, null, null, 'Vacation Accrual YTD adjustment', -1, 2.03, 0 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 5.01 );


		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			Debug::text( 'Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10 );
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( '100.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '110.02', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );
		$this->assertEquals( '-1.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );

		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] ); //YTD adjustment
		$this->assertEquals( '2.03', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '5.01', $pse_arr[$pse_accounts['vacation_accrual']][2]['amount'] );
		$this->assertEquals( '6.04', $pse_arr[$pse_accounts['vacation_accrual']][2]['ytd_amount'] );

		$this->assertEquals( '211.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '211.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '135.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '135.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );

		unset( $pse_arr, $pay_stub_id, $pay_stub );

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
		$pay_stub->setPayPeriod( $this->pay_period_objs[( $start_pay_period_id + 1 )]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle
		$pay_stub->setRun( 1 );

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'state_unemployment'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'NY - Unemployment Insurance' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		$pay_stub->addEntry( $pse_accounts['regular_time'], 198.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 12.01 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.03 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 53.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 27.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 13.04 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 16.09 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 6.01 );

		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			Debug::text( 'Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10 );
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( '198.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '12.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '320.04', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		$this->assertEquals( '1.03', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '2.03', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '53.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '103.02', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '27.04', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '52.08', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '13.04', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '23.05', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '16.09', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '31.14', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );
		$this->assertEquals( '-1.03', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );
		$this->assertEquals( '6.01', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
		$this->assertEquals( '11.02', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '211.05', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '422.09', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '80.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '155.10', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '131.00', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '266.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '29.13', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '54.19', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );
		unset( $pse_arr, $pay_stub_id, $pay_stub );

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
		$pay_stub->setPayPeriod( $this->pay_period_objs[( $start_pay_period_id + 1 )]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 20 ); //Normal In-Cycle
		$pay_stub->setRun( 2 );

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'state_unemployment'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'NY - Unemployment Insurance' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		$pay_stub->addEntry( $pse_accounts['regular_time'], 1.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 1.01 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.01 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 1.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 1.01 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 1.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 1.01 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 1.01 );

		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			Debug::text( 'Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10 );
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( '1.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '1.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '322.06', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		$this->assertEquals( '1.01', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '3.04', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '1.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '104.03', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '1.01', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '53.09', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '1.01', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '24.06', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '1.01', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '32.15', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );
		$this->assertEquals( '-1.01', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );
		$this->assertEquals( '1.01', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
		$this->assertEquals( '11.02', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '3.03', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '425.12', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '2.02', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '157.12', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '1.01', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '268.00', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '2.02', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '56.21', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );
		unset( $pse_arr, $pay_stub_id, $pay_stub );

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
		$pay_stub->setPayPeriod( $this->pay_period_objs[( $start_pay_period_id + 1 )]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 20 ); //Normal In-Cycle
		$pay_stub->setRun( 3 );

		$pay_stub->setDefaultDates();

		$pay_stub->setTransactionDate( TTDate::incrementDate( TTDate::getEndYearEpoch( $pay_stub->getTransactionDate() ), 1, 'day' ) ); //Push transaction date into new year to zero YTD values.

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'state_unemployment'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'NY - Unemployment Insurance' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		$pay_stub->addEntry( $pse_accounts['regular_time'], 1.02 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 1.02 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.02 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 1.02 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 1.02 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 1.02 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 1.02 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 1.02 );

		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			Debug::text( 'Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10 );
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		//
		// IN NEW YEAR, YTD amounts are zero'd!
		//
		$this->assertEquals( '1.02', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '1.02', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '2.04', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '1.02', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '1.02', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '1.02', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '1.02', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '1.02', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '1.02', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '1.02', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '1.02', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '1.02', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '1.02', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );
		$this->assertEquals( '-1.02', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );
		$this->assertEquals( '1.02', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
		$this->assertEquals( '11.02', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '3.06', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '3.06', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '2.04', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '2.04', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '1.02', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '1.02', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '2.04', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '2.04', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );
		unset( $pse_arr, $pay_stub_id, $pay_stub );

		//
		// Last Pay Stub
		//
		//Test UnUsed YTD entries...
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[( $start_pay_period_id + 2 )]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 20 ); //Normal In-Cycle
		$pay_stub->setRun( 1 );

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'state_unemployment'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'NY - Unemployment Insurance' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 5.01 );

		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			$pay_stub_id = $pay_stub->Save();
			Debug::text( 'Pay Stub is valid, final save, ID: ' . $pay_stub_id, __FILE__, __LINE__, __METHOD__, 10 );
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( '100.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '112.06', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '2.02', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '51.03', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '26.06', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '11.03', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '16.07', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );
		$this->assertEquals( '-1.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );
		$this->assertEquals( '5.01', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
		$this->assertEquals( '15.03', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '211.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '214.10', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '77.09', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '135.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '137.01', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '27.10', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );
		unset( $pse_arr, $pay_stub_id, $pay_stub );

		return true;
	}

	/**
	 * @group PayStub_testEditMultiplePayStub
	 */
	//Test editing pay stub in the middle of the year, and having the other pay stubs YTD re-calculated.
	function testEditMultiplePayStub() {
		//Test all parts of multiple pay stubs that span a year boundary.

		//Start 6 pay periods from the last one. Should be beginning/end of December,
		//Its the TRANSACTION date that counts
		$start_pay_period_id = ( count( $this->pay_period_objs ) - 6 );
		Debug::text( 'Starting Pay Period: ' . TTDate::getDate( 'DATE+TIME', $this->pay_period_objs[$start_pay_period_id]->getStartDate() ), __FILE__, __LINE__, __METHOD__, 10 );

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
		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'state_unemployment'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'NY - Unemployment Insurance' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 5.01 );

		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			Debug::text( 'Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10 );
			$first_pay_stub_id = $pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( '100.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '110.02', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );
		$this->assertEquals( '-1.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );
		$this->assertEquals( '5.01', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
		$this->assertEquals( '4.01', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '211.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '211.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '135.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '135.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );

		unset( $pse_arr, $pay_stub_id, $pay_stub );

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
		$pay_stub->setPayPeriod( $this->pay_period_objs[( $start_pay_period_id + 1 )]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'state_unemployment'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'NY - Unemployment Insurance' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		$pay_stub->addEntry( $pse_accounts['regular_time'], 198.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 12.01 );
		//$pay_stub->addEntry( $pse_accounts['over_time_1'], 111.02 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.03 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 53.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 27.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 13.04 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 16.09 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 6.01 );

		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			Debug::text( 'Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10 );
			$second_pay_stub_id = $pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( '198.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '12.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '320.04', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		$this->assertEquals( '1.03', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '2.03', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '53.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '103.02', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '27.04', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '52.08', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '13.04', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '23.05', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '16.09', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '31.14', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );
		$this->assertEquals( '-1.03', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );
		$this->assertEquals( '6.01', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
		$this->assertEquals( '8.99', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '211.05', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '422.09', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '80.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '155.10', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '131.00', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '266.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '29.13', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '54.19', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );

		unset( $pse_arr, $pay_stub_id, $pay_stub );

		//
		// Third Pay Stub
		//

		//Test UnUsed YTD entries...
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[( $start_pay_period_id + 2 )]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'state_unemployment'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'NY - Unemployment Insurance' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 5.01 );

		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			$third_pay_stub_id = $pay_stub_id = $pay_stub->Save();
			Debug::text( 'Pay Stub is valid, final save, ID: ' . $pay_stub_id, __FILE__, __LINE__, __METHOD__, 10 );
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		//
		// IN NEW YEAR, YTD amounts are zero'd!
		//
		$this->assertEquals( '100.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '110.02', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );
		$this->assertEquals( '-1.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );
		$this->assertEquals( '5.01', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
		$this->assertEquals( '13.00', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '211.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '211.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '135.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '135.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );
		unset( $pse_arr, $pay_stub_id, $pay_stub );


		//
		//Now edit the first pay stub.
		//
		$pslf = new PayStubListFactory();
		$pay_stub = $pslf->getByID( $first_pay_stub_id )->getCurrent();
		$pay_stub->loadPreviousPayStub();
		$pay_stub->deleteEntries( true );
		$pay_stub->setEnableLinkedAccruals( false );

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

		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			$pay_stub->Save();

			//Recalculate all pay stubs after this one.
			$pslf = new PayStubListFactory();
			$pslf->getById( $first_pay_stub_id );
			if ( $pslf->getRecordCount() > 0 ) {
				$ps_obj = $pslf->getCurrent();
				$ps_obj->reCalculateYTD();
			}
			unset( $ps_obj );
			//Debug::text('Pay Stub is valid, final save, ID: '. $pay_stub_id, __FILE__, __LINE__, __METHOD__,10);
		}

		$pse_arr = $this->getPayStubEntryArray( $first_pay_stub_id );
		//Debug::Arr($pse_arr, 'Pay Stub Entry Arr: ', __FILE__, __LINE__, __METHOD__,10);

		$this->assertEquals( '105.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '115.02', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );
		$this->assertEquals( '-1.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );
		$this->assertEquals( '5.01', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
		$this->assertEquals( '4.01', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '216.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '216.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '140.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '140.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );

		unset( $pse_arr, $pay_stub_id, $pay_stub );

		//
		// Confirm YTD values in second pay stub are correct
		//
		Debug::Text( 'First Pay Stub ID: ' . $first_pay_stub_id . ' Second Pay Stub ID: ' . $second_pay_stub_id, __FILE__, __LINE__, __METHOD__, 10 );

		$pse_arr = $this->getPayStubEntryArray( $second_pay_stub_id );
		//Debug::Arr($pse_arr, 'Second Pay Stub Entry Arr: ', __FILE__, __LINE__, __METHOD__,10);

		$this->assertEquals( '198.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '12.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '325.04', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		$this->assertEquals( '1.03', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '2.03', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '53.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '103.02', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '27.04', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '52.08', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '13.04', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '23.05', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '16.09', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '31.14', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );
		$this->assertEquals( '-1.03', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );
		$this->assertEquals( '6.01', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
		$this->assertEquals( '8.99', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '211.05', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '427.09', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '80.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '155.10', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '131.00', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '271.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '29.13', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '54.19', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );
		unset( $pse_arr, $pay_stub_id, $pay_stub );

		//
		// Confirm YTD values in third pay stub are correct
		//
		$pse_arr = $this->getPayStubEntryArray( $third_pay_stub_id );

		$this->assertEquals( '100.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '110.02', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );
		$this->assertEquals( '-1.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );
		$this->assertEquals( '5.01', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
		$this->assertEquals( '13.00', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '211.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '211.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '135.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '135.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );
		unset( $pse_arr, $pay_stub_id, $pay_stub );

		return true;
	}

	/**
	 * @group PayStub_testMultiplePayStubAccruals
	 */
	function testMultiplePayStubAccruals() {
		//Test all parts of multiple pay stubs that span a year boundary.

		//Start 6 pay periods from the last one. Should be beginning/end of December,
		//Its the TRANSACTION date that counts
		$start_pay_period_id = ( count( $this->pay_period_objs ) - 8 );
		Debug::text( 'Starting Pay Period: ' . TTDate::getDate( 'DATE+TIME', $this->pay_period_objs[$start_pay_period_id]->getStartDate() ), __FILE__, __LINE__, __METHOD__, 10 );

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
		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'state_unemployment'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'NY - Unemployment Insurance' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );

		//Adjust YTD balance, emulating a YTD PS amendment
		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], -340.38, null, null, 'Vacation Accrual YTD adjustment', -1, 0, 0 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 6.13 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 60.03 );

		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			Debug::text( 'Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10 );
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( '100.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '110.02', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		$this->assertEquals( '6.13', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '6.13', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );

		$this->assertEquals( '-340.38', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] ); //YTD adjustment
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );

		$this->assertEquals( '-6.13', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '60.03', $pse_arr[$pse_accounts['vacation_accrual']][2]['amount'] ); //YTD adjustment
		$this->assertEquals( '-286.48', $pse_arr[$pse_accounts['vacation_accrual']][2]['ytd_amount'] );

		$this->assertEquals( '216.17', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '216.17', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '141.12', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '141.12', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );

		unset( $pse_arr, $pay_stub_id, $pay_stub );

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
		$pay_stub->setPayPeriod( $this->pay_period_objs[( $start_pay_period_id + 1 )]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'state_unemployment'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'NY - Unemployment Insurance' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		$pay_stub->addEntry( $pse_accounts['regular_time'], 198.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 12.01 );
		//$pay_stub->addEntry( $pse_accounts['over_time_1'], 111.02 );
		//$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.03 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 53.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 27.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 13.04 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 16.09 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 240.01 );

		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			Debug::text( 'Pay Stub is valid, final save.', __FILE__, __LINE__, __METHOD__, 10 );
			$pay_stub_id = $pay_stub->Save();
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		$this->assertEquals( '198.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '12.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '320.04', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		//$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'], '1.03' );
		//$this->assertEquals( $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'], '2.03' );
		$this->assertEquals( '53.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '103.02', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '27.04', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '52.08', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '13.04', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '23.05', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '16.09', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '31.14', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );

		$this->assertEquals( '240.01', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '-46.47', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );

		$this->assertEquals( '210.02', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '426.19', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '80.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '155.10', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '129.97', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '271.09', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '29.13', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '54.19', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );

		unset( $pse_arr, $pay_stub_id, $pay_stub );

		//
		// Third Pay Stub
		//


		//Test UnUsed YTD entries...
		$pay_stub = new PayStubFactory();
		$pay_stub->setUser( $this->user_id );
		$pay_stub->setCurrency( $pay_stub->getUserObject()->getCurrency() );
		$pay_stub->setPayPeriod( $this->pay_period_objs[( $start_pay_period_id + 2 )]->getId() );
		$pay_stub->setStatus( 10 ); //New
		$pay_stub->setType( 10 ); //Normal In-Cycle

		$pay_stub->setDefaultDates();

		$pay_stub->loadPreviousPayStub();
		$pse_accounts = [
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'over_time_1'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Over Time 1' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'federal_income_tax'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'US - Federal Income Tax' ),
				'state_income_tax'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'NY - State Income Tax' ),
				'medicare'                 => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'Medicare' ),
				'state_unemployment'       => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 30, 'NY - Unemployment Insurance' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
		];

		$pay_stub->addEntry( $pse_accounts['regular_time'], 100.01 );
		$pay_stub->addEntry( $pse_accounts['regular_time'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['over_time_1'], 100.02 );
		$pay_stub->addEntry( $pse_accounts['vacation_accrual_release'], 1.00 );

		$pay_stub->addEntry( $pse_accounts['federal_income_tax'], 50.01 );
		$pay_stub->addEntry( $pse_accounts['state_income_tax'], 25.04 );

		$pay_stub->addEntry( $pse_accounts['medicare'], 10.01 );
		$pay_stub->addEntry( $pse_accounts['state_unemployment'], 15.05 );

		$pay_stub->addEntry( $pse_accounts['vacation_accrual'], 65.01 );

		$pay_stub->setEnableProcessEntries( true );
		$pay_stub->processEntries();
		if ( $pay_stub->isValid() == true ) {
			$pay_stub_id = $pay_stub->Save();
			Debug::text( 'Pay Stub is valid, final save, ID: ' . $pay_stub_id, __FILE__, __LINE__, __METHOD__, 10 );
		}

		$pse_arr = $this->getPayStubEntryArray( $pay_stub_id );

		//
		// IN NEW YEAR, YTD amounts are zero'd!
		//
		$this->assertEquals( '100.01', $pse_arr[$pse_accounts['regular_time']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['regular_time']][0]['ytd_amount'] );
		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['regular_time']][1]['amount'] );
		$this->assertEquals( '430.06', $pse_arr[$pse_accounts['regular_time']][1]['ytd_amount'] );

		$this->assertEquals( '100.02', $pse_arr[$pse_accounts['over_time_1']][0]['amount'] );
		$this->assertEquals( '200.04', $pse_arr[$pse_accounts['over_time_1']][0]['ytd_amount'] );

		$this->assertEquals( '1.00', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['amount'] );
		$this->assertEquals( '7.13', $pse_arr[$pse_accounts['vacation_accrual_release']][0]['ytd_amount'] );
		$this->assertEquals( '50.01', $pse_arr[$pse_accounts['federal_income_tax']][0]['amount'] );
		$this->assertEquals( '153.03', $pse_arr[$pse_accounts['federal_income_tax']][0]['ytd_amount'] );
		$this->assertEquals( '25.04', $pse_arr[$pse_accounts['state_income_tax']][0]['amount'] );
		$this->assertEquals( '77.12', $pse_arr[$pse_accounts['state_income_tax']][0]['ytd_amount'] );

		$this->assertEquals( '10.01', $pse_arr[$pse_accounts['medicare']][0]['amount'] );
		$this->assertEquals( '33.06', $pse_arr[$pse_accounts['medicare']][0]['ytd_amount'] );
		$this->assertEquals( '15.05', $pse_arr[$pse_accounts['state_unemployment']][0]['amount'] );
		$this->assertEquals( '46.19', $pse_arr[$pse_accounts['state_unemployment']][0]['ytd_amount'] );

		$this->assertEquals( '-1.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['amount'] );
		$this->assertEquals( '0.00', $pse_arr[$pse_accounts['vacation_accrual']][0]['ytd_amount'] );
		$this->assertEquals( '65.01', $pse_arr[$pse_accounts['vacation_accrual']][1]['amount'] );
		$this->assertEquals( '17.54', $pse_arr[$pse_accounts['vacation_accrual']][1]['ytd_amount'] );

		$this->assertEquals( '211.04', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['amount'] );
		$this->assertEquals( '637.23', $pse_arr[$this->pay_stub_account_link_arr['total_gross']][0]['ytd_amount'] );
		$this->assertEquals( '75.05', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['amount'] );
		$this->assertEquals( '230.15', $pse_arr[$this->pay_stub_account_link_arr['total_deductions']][0]['ytd_amount'] );
		$this->assertEquals( '135.99', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['amount'] );
		$this->assertEquals( '407.08', $pse_arr[$this->pay_stub_account_link_arr['net_pay']][0]['ytd_amount'] );
		$this->assertEquals( '25.06', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['amount'] );
		$this->assertEquals( '79.25', $pse_arr[$this->pay_stub_account_link_arr['employer_contribution']][0]['ytd_amount'] );

		unset( $pse_arr, $pay_stub_id, $pay_stub );

		return true;
	}


	/**
	 * @group testRemittanceSourceAccountValidation
	 */
	function testRemittanceSourceAccountValidation() {
		$rsaf = new RemittanceSourceAccountFactory();
		$rsaf->setCompany( $this->company_id );
		$rsaf->setLegalEntity( $this->legal_entity_id );
		$rsaf->setStatus( 10 ); //Enabled
		$rsaf->setName( 'testRemittanceSourceAccountValidation' );
		$rsaf->setCurrency( $this->currency_id );
		$rsaf->setType( 3000 );

		//
		// US
		//
		$rsaf->setCountry( 'US' );
		$rsaf->setDataFormat( 10 ); //10=ACH
		$rsaf->setValue2( 123456789 ); //Branch/Routing
		$rsaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( true, $rsaf->isValid() );
		$this->assertCount( 0, $rsaf->Validator->getErrorsArray() );
		$rsaf->Validator->resetErrors();

		$rsaf->setValue2( 123456789 ); //Branch/Routing
		$rsaf->setValue3( 12345678901234567 ); //Account Number
		$this->assertEquals( true, $rsaf->isValid() );
		$this->assertCount( 0, $rsaf->Validator->getErrorsArray() );
		$rsaf->Validator->resetErrors();

		$rsaf->setValue2( '000456789' ); //Branch/Routing
		$rsaf->setValue3( '00045678901234567' ); //Account Number
		$this->assertEquals( true, $rsaf->isValid() );
		$this->assertCount( 0, $rsaf->Validator->getErrorsArray() );
		$rsaf->Validator->resetErrors();

		//Some bank in Carribean (Antigua?) don't have branch/routing numbers, so we need to accept all zeros.
		$rsaf->setValue2( '000000000' ); //Branch/Routing
		$rsaf->setValue3( '00045678901234567' ); //Account Number
		$this->assertEquals( true, $rsaf->isValid() );
		$this->assertCount( 0, $rsaf->Validator->getErrorsArray() );
		$rsaf->Validator->resetErrors();

		//Routing number invalid. -- Too short.
		$rsaf->setValue2( 12345678 ); //Branch/Routing
		$rsaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( false, $rsaf->isValid() );
		$this->assertEquals( true, $rsaf->Validator->hasError( 'value2' ) );
		$this->assertCount( 1, $rsaf->Validator->getErrorsArray() );
		$rsaf->Validator->resetErrors();

		//Routing number invalid. -- Too long
		$rsaf->setValue2( 1234567891 ); //Branch/Routing
		$rsaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( false, $rsaf->isValid() );
		$this->assertEquals( true, $rsaf->Validator->hasError( 'value2' ) );
		$this->assertCount( 1, $rsaf->Validator->getErrorsArray() );
		$rsaf->Validator->resetErrors();

		//Account number invalid. -- Too short
		$rsaf->setValue2( 123456789 ); //Branch/Routing
		$rsaf->setValue3( 12 ); //Account Number
		$this->assertEquals( false, $rsaf->isValid() );
		$this->assertEquals( true, $rsaf->Validator->hasError( 'value3' ) );
		$this->assertCount( 1, $rsaf->Validator->getErrorsArray(), $rsaf->Validator->getTextErrors() );
		$rsaf->Validator->resetErrors();

		//Account number invalid. -- Too long
		$rsaf->setValue2( 123456789 ); //Branch/Routing
		$rsaf->setValue3( 123456789012345678 ); //Account Number
		$this->assertEquals( false, $rsaf->isValid() );
		$this->assertEquals( true, $rsaf->Validator->hasError( 'value3' ) );
		$this->assertCount( 1, $rsaf->Validator->getErrorsArray() );
		$rsaf->Validator->resetErrors();

		//Using scientific notation.
		$rsaf->setValue2( '5.18E+11' ); //Branch/Routing
		$rsaf->setValue3( '5.18E+11' ); //Account Number
		$this->assertEquals( false, $rsaf->isValid() );
		$this->assertEquals( true, $rsaf->Validator->hasError( 'value2' ) );
		$this->assertEquals( true, $rsaf->Validator->hasError( 'value3' ) );
		$this->assertCount( 2, $rsaf->Validator->getErrorsArray() );
		$rsaf->Validator->resetErrors();

		//Using bogus data.
		$rsaf->setValue2( '123ABC456789' ); //Branch/Routing
		$rsaf->setValue3( '123!@$456789' ); //Account Number
		$this->assertEquals( false, $rsaf->isValid() );
		$this->assertEquals( true, $rsaf->Validator->hasError( 'value2' ) );
		$this->assertEquals( true, $rsaf->Validator->hasError( 'value3' ) );
		$this->assertCount( 2, $rsaf->Validator->getErrorsArray() );
		$rsaf->Validator->resetErrors();


		//
		// CA
		//
		$rsaf->setCountry( 'CA' );
		$rsaf->setDataFormat( 20 ); //20=1464
		$rsaf->setValue1( 123 ); //Institution
		$rsaf->setValue2( 12345 ); //Branch/Routing
		$rsaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( true, $rsaf->isValid() );
		$this->assertCount( 0, $rsaf->Validator->getErrorsArray() );
		$rsaf->Validator->resetErrors();

		$rsaf->setValue1( 123 ); //Institution
		$rsaf->setValue2( 12345 ); //Branch/Routing
		$rsaf->setValue3( 123456789012 ); //Account Number
		$this->assertEquals( true, $rsaf->isValid() );
		$this->assertCount( 0, $rsaf->Validator->getErrorsArray() );
		$rsaf->Validator->resetErrors();

		$rsaf->setValue1( '001' ); //Institution
		$rsaf->setValue2( '00045' ); //Branch/Routing
		$rsaf->setValue3( '000456789012' ); //Account Number
		$this->assertEquals( true, $rsaf->isValid() );
		$this->assertCount( 0, $rsaf->Validator->getErrorsArray() );
		$rsaf->Validator->resetErrors();

		//Some bank in Carribean (Antigua?) don't have branch/routing numbers, so we need to accept all zeros.
		$rsaf->setValue1( '000' ); //Institution
		$rsaf->setValue2( '00000' ); //Branch/Routing
		$rsaf->setValue3( '000456789012' ); //Account Number
		$this->assertEquals( true, $rsaf->isValid() );
		$this->assertCount( 0, $rsaf->Validator->getErrorsArray() );
		$rsaf->Validator->resetErrors();

		//Institution number invalid. -- Too short.
		$rsaf->setValue1( '01' ); //Institution
		$rsaf->setValue2( 12345 ); //Branch/Routing
		$rsaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( false, $rsaf->isValid() );
		$this->assertEquals( true, $rsaf->Validator->hasError( 'value1' ) );
		$this->assertCount( 1, $rsaf->Validator->getErrorsArray() );
		$rsaf->Validator->resetErrors();

		//Institution number invalid. -- Too Long.
		$rsaf->setValue1( '0013' ); //Institution
		$rsaf->setValue2( 12345 ); //Branch/Routing
		$rsaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( false, $rsaf->isValid() );
		$this->assertEquals( true, $rsaf->Validator->hasError( 'value1' ) );
		$this->assertCount( 1, $rsaf->Validator->getErrorsArray() );
		$rsaf->Validator->resetErrors();

		//Branch number invalid. -- Too short.
		$rsaf->setValue1( '001' ); //Institution
		$rsaf->setValue2( 1234 ); //Branch/Routing
		$rsaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( false, $rsaf->isValid() );
		$this->assertEquals( true, $rsaf->Validator->hasError( 'value2' ) );
		$this->assertCount( 1, $rsaf->Validator->getErrorsArray() );
		$rsaf->Validator->resetErrors();

		//Branch number invalid. -- Too long
		$rsaf->setValue1( '001' ); //Institution
		$rsaf->setValue2( 123456 ); //Branch/Routing
		$rsaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( false, $rsaf->isValid() );
		$this->assertEquals( true, $rsaf->Validator->hasError( 'value2' ) );
		$this->assertCount( 1, $rsaf->Validator->getErrorsArray() );
		$rsaf->Validator->resetErrors();

		//Account number invalid. -- Too short
		$rsaf->setValue1( '001' ); //Institution
		$rsaf->setValue2( 12345 ); //Branch/Routing
		$rsaf->setValue3( 12 ); //Account Number
		$this->assertEquals( false, $rsaf->isValid() );
		$this->assertEquals( true, $rsaf->Validator->hasError( 'value3' ) );
		$this->assertCount( 1, $rsaf->Validator->getErrorsArray() );
		$rsaf->Validator->resetErrors();

		//Account number invalid. -- Too long
		$rsaf->setValue1( '001' ); //Institution
		$rsaf->setValue2( 12345 ); //Branch/Routing
		$rsaf->setValue3( 1234567890123 ); //Account Number
		$this->assertEquals( false, $rsaf->isValid() );
		$this->assertEquals( true, $rsaf->Validator->hasError( 'value3' ) );
		$this->assertCount( 1, $rsaf->Validator->getErrorsArray() );
		$rsaf->Validator->resetErrors();

		//Using scientific notation.
		$rsaf->setValue1( '001' ); //Institution
		$rsaf->setValue2( '5.18E+11' ); //Branch/Routing
		$rsaf->setValue3( '5.18E+11' ); //Account Number
		$this->assertEquals( false, $rsaf->isValid() );
		$this->assertEquals( true, $rsaf->Validator->hasError( 'value2' ) );
		$this->assertEquals( true, $rsaf->Validator->hasError( 'value3' ) );
		$this->assertCount( 2, $rsaf->Validator->getErrorsArray() );
		$rsaf->Validator->resetErrors();

		//Using bogus data.
		$rsaf->setValue1( '001' ); //Institution
		$rsaf->setValue2( '123AB' ); //Branch/Routing
		$rsaf->setValue3( '123ABC456789' ); //Account Number
		$this->assertEquals( false, $rsaf->isValid() );
		$this->assertEquals( true, $rsaf->Validator->hasError( 'value2' ) );
		$this->assertEquals( true, $rsaf->Validator->hasError( 'value3' ) );
		$this->assertCount( 2, $rsaf->Validator->getErrorsArray() );
		$rsaf->Validator->resetErrors();

		//Using bogus data.
		$rsaf->setValue1( '1A1' ); //Institution
		$rsaf->setValue2( '123AB' ); //Branch/Routing
		$rsaf->setValue3( '123ABC456789' ); //Account Number
		$this->assertEquals( false, $rsaf->isValid() );
		$this->assertEquals( true, $rsaf->Validator->hasError( 'value1' ) );
		$this->assertEquals( true, $rsaf->Validator->hasError( 'value2' ) );
		$this->assertEquals( true, $rsaf->Validator->hasError( 'value3' ) );
		$this->assertCount( 3, $rsaf->Validator->getErrorsArray() );
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
		$rsaf->setName( 'testRemittanceDestinationAccountValidationA' );
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
		$rdaf->setName( 'testRemittanceDestinationAccountValidationB' );
		$rdaf->setType( 3000 );
		$rdaf->setAmountType( 10 ); //10=Percent
		$rdaf->setPercentAmount( 100 ); //100%
		$rdaf->setPriority( 5 );
		$rdaf->setValue1( 22 ); //22=Checkings


		$rdaf->setValue2( 123456789 ); //Branch/Routing
		$rdaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( true, $rdaf->isValid() );
		$this->assertCount( 0, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();

		$rdaf->setValue2( 123456789 ); //Branch/Routing
		$rdaf->setValue3( 12345678901234567 ); //Account Number
		$this->assertEquals( true, $rdaf->isValid() );
		$this->assertCount( 0, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();

		$rdaf->setValue2( '000456789' ); //Branch/Routing
		$rdaf->setValue3( '00045678901234567' ); //Account Number
		$this->assertEquals( true, $rdaf->isValid() );
		$this->assertCount( 0, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();

		//Some bank in Carribean (Antigua?) don't have branch/routing numbers, so we need to accept all zeros.
		$rdaf->setValue2( '000000000' ); //Branch/Routing
		$rdaf->setValue3( '00045678901234567' ); //Account Number
		$this->assertEquals( true, $rdaf->isValid() );
		$this->assertCount( 0, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();

		//Routing number invalid. -- Too short.
		$rdaf->setValue2( 12345678 ); //Branch/Routing
		$rdaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( false, $rdaf->isValid() );
		$this->assertEquals( true, $rdaf->Validator->hasError( 'value2' ) );
		$this->assertCount( 1, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();

		//Routing number invalid. -- Too long
		$rdaf->setValue2( 1234567891 ); //Branch/Routing
		$rdaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( false, $rdaf->isValid() );
		$this->assertEquals( true, $rdaf->Validator->hasError( 'value2' ) );
		$this->assertCount( 1, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();

		//Account number invalid. -- Too short
		$rdaf->setValue2( 123456789 ); //Branch/Routing
		$rdaf->setValue3( 12 ); //Account Number
		$this->assertEquals( false, $rdaf->isValid() );
		$this->assertEquals( true, $rdaf->Validator->hasError( 'value3' ) );
		$this->assertCount( 1, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();

		//Account number invalid. -- Too long
		$rdaf->setValue2( 123456789 ); //Branch/Routing
		$rdaf->setValue3( 123456789012345678 ); //Account Number
		$this->assertEquals( false, $rdaf->isValid() );
		$this->assertEquals( true, $rdaf->Validator->hasError( 'value3' ) );
		$this->assertCount( 1, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();

		//Using scientific notation.
		$rdaf->setValue2( '5.18E+11' ); //Branch/Routing
		$rdaf->setValue3( '5.18E+11' ); //Account Number
		$this->assertEquals( false, $rdaf->isValid() );
		$this->assertEquals( true, $rdaf->Validator->hasError( 'value2' ) );
		$this->assertEquals( true, $rdaf->Validator->hasError( 'value3' ) );
		$this->assertCount( 2, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();

		//Using bogus data.
		$rdaf->setValue2( '123ABC456789' ); //Branch/Routing
		$rdaf->setValue3( '123!@$456789' ); //Account Number
		$this->assertEquals( false, $rdaf->isValid() );
		$this->assertEquals( true, $rdaf->Validator->hasError( 'value2' ) );
		$this->assertEquals( true, $rdaf->Validator->hasError( 'value3' ) );
		$this->assertCount( 2, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();


		//
		// CA
		//
		$rsaf = new RemittanceSourceAccountFactory();
		$rsaf->setCompany( $this->company_id );
		$rsaf->setLegalEntity( $this->legal_entity_id );
		$rsaf->setStatus( 10 ); //Enabled
		$rsaf->setName( 'testRemittanceDestinationAccountValidationC' );
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
		$rdaf->setName( 'testRemittanceDestinationAccountValidationD' );
		$rdaf->setType( 3000 );
		$rdaf->setAmountType( 10 ); //10=Percent
		$rdaf->setPercentAmount( 100 ); //100%
		$rdaf->setPriority( 5 );

		$rdaf->setValue1( 123 ); //Institution
		$rdaf->setValue2( 12345 ); //Branch/Routing
		$rdaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( true, $rdaf->isValid() );
		$this->assertCount( 0, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();

		$rdaf->setValue1( 123 ); //Institution
		$rdaf->setValue2( 12345 ); //Branch/Routing
		$rdaf->setValue3( 123456789012 ); //Account Number
		$this->assertEquals( true, $rdaf->isValid() );
		$this->assertCount( 0, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();

		$rdaf->setValue1( '001' ); //Institution
		$rdaf->setValue2( '00045' ); //Branch/Routing
		$rdaf->setValue3( '000456789012' ); //Account Number
		$this->assertEquals( true, $rdaf->isValid() );
		$this->assertCount( 0, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();

		//Some bank in Carribean (Antigua?) don't have branch/routing numbers, so we need to accept all zeros.
		$rdaf->setValue1( '000' ); //Institution
		$rdaf->setValue2( '00000' ); //Branch/Routing
		$rdaf->setValue3( '000456789012' ); //Account Number
		$this->assertEquals( true, $rdaf->isValid() );
		$this->assertCount( 0, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();

		//Institution number invalid. -- Too short.
		$rdaf->setValue1( '01' ); //Institution
		$rdaf->setValue2( 12345 ); //Branch/Routing
		$rdaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( false, $rdaf->isValid() );
		$this->assertEquals( true, $rdaf->Validator->hasError( 'value1' ) );
		$this->assertCount( 1, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();

		//Institution number invalid. -- Too Long.
		$rdaf->setValue1( '0013' ); //Institution
		$rdaf->setValue2( 12345 ); //Branch/Routing
		$rdaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( false, $rdaf->isValid() );
		$this->assertEquals( true, $rdaf->Validator->hasError( 'value1' ) );
		$this->assertCount( 1, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();

		//Branch number invalid. -- Too short.
		$rdaf->setValue1( '001' ); //Institution
		$rdaf->setValue2( 1234 ); //Branch/Routing
		$rdaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( false, $rdaf->isValid() );
		$this->assertEquals( true, $rdaf->Validator->hasError( 'value2' ) );
		$this->assertCount( 1, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();

		//Branch number invalid. -- Too long
		$rdaf->setValue1( '001' ); //Institution
		$rdaf->setValue2( 123456 ); //Branch/Routing
		$rdaf->setValue3( 123456789 ); //Account Number
		$this->assertEquals( false, $rdaf->isValid() );
		$this->assertEquals( true, $rdaf->Validator->hasError( 'value2' ) );
		$this->assertCount( 1, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();

		//Account number invalid. -- Too short
		$rdaf->setValue1( '001' ); //Institution
		$rdaf->setValue2( 12345 ); //Branch/Routing
		$rdaf->setValue3( 12 ); //Account Number
		$this->assertEquals( false, $rdaf->isValid() );
		$this->assertEquals( true, $rdaf->Validator->hasError( 'value3' ) );
		$this->assertCount( 1, $rdaf->Validator->getErrorsArray(), $rsaf->Validator->getTextErrors() );
		$rdaf->Validator->resetErrors();

		//Account number invalid. -- Too long
		$rdaf->setValue1( '001' ); //Institution
		$rdaf->setValue2( 12345 ); //Branch/Routing
		$rdaf->setValue3( 1234567890123 ); //Account Number
		$this->assertEquals( false, $rdaf->isValid() );
		$this->assertEquals( true, $rdaf->Validator->hasError( 'value3' ) );
		$this->assertCount( 1, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();

		//Using scientific notation.
		$rdaf->setValue1( '001' ); //Institution
		$rdaf->setValue2( '5.18E+11' ); //Branch/Routing
		$rdaf->setValue3( '5.18E+11' ); //Account Number
		$this->assertEquals( false, $rdaf->isValid() );
		$this->assertEquals( true, $rdaf->Validator->hasError( 'value2' ) );
		$this->assertEquals( true, $rdaf->Validator->hasError( 'value3' ) );
		$this->assertCount( 2, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();

		//Using bogus data.
		$rdaf->setValue1( '001' ); //Institution
		$rdaf->setValue2( '123AB' ); //Branch/Routing
		$rdaf->setValue3( '123!@$456789' ); //Account Number
		$this->assertEquals( false, $rdaf->isValid() );
		$this->assertEquals( true, $rdaf->Validator->hasError( 'value2' ) );
		$this->assertEquals( true, $rdaf->Validator->hasError( 'value3' ) );
		$this->assertCount( 2, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();

		//Using bogus data.
		$rdaf->setValue1( '1A1' ); //Institution
		$rdaf->setValue2( '123AB' ); //Branch/Routing
		$rdaf->setValue3( '123!@$456789' ); //Account Number
		$this->assertEquals( false, $rdaf->isValid() );
		$this->assertEquals( true, $rdaf->Validator->hasError( 'value1' ) );
		$this->assertEquals( true, $rdaf->Validator->hasError( 'value2' ) );
		$this->assertEquals( true, $rdaf->Validator->hasError( 'value3' ) );
		$this->assertCount( 3, $rdaf->Validator->getErrorsArray() );
		$rdaf->Validator->resetErrors();
	}

	/**
	 * @group testZeroDollarUserWage
	 */
	function testZeroDollarUserWage() {
		$uwf = TTnew( 'UserWageFactory' );
		$uwf->setUser( $this->user_id );
		$uwf->setType( 10 );
		$uwf->setWageGroup( TTUUID::getZeroID() );
		$uwf->setEffectiveDate( TTDate::incrementDate( time(), 1, 'day' ) );
		$uwf->setWage( 0 ); //$0 wages should be accepted.

		if ( $uwf->isValid() ) {
			$result = $uwf->Save();
			$this->assertEquals( true, TTUUID::isUUID( $result ) );
		} else {
			$this->assertTrue( false );
		}
	}
}

?>