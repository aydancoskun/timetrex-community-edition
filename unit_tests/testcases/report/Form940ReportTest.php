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

class Form940ReportTest extends PHPUnit_Framework_TestCase {
	protected $company_id = NULL;
	protected $user_id = NULL;
	protected $pay_period_schedule_id = NULL;
	protected $pay_period_objs = NULL;
	protected $pay_stub_account_link_arr = NULL;

	public function setUp() {
		global $dd;
		Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setTimeZone('PST8PDT', TRUE); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		$dd = new DemoData();
		$dd->setEnableQuickPunch( FALSE ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_'.uniqid( NULL, TRUE ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		$this->legal_entity_id = $dd->createLegalEntity( $this->company_id, 10 );
		Debug::text('Company ID: '. $this->company_id, __FILE__, __LINE__, __METHOD__, 10);

		$this->currency_id = $dd->createCurrency( $this->company_id, 10 );

		//Permissions are required so the user has permissions to run reports.
		$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$dd->createPayStubAccount( $this->company_id );
		$dd->createPayStubAccountLink( $this->company_id );
		$this->getPayStubAccountLinkArray();

		$dd->createUserWageGroups( $this->company_id );

		$dd->createPayrollRemittanceAgency( $this->company_id, NULL, $this->legal_entity_id ); //Must go before createCompanyDeduction()

		//Company Deductions
		$dd->createCompanyDeduction( $this->company_id, NULL, $this->legal_entity_id );

		//Create multiple state tax/deductions.
		$sp = TTNew('SetupPresets'); /** @var SetupPresets $sp */
		$sp->setCompany( $this->company_id );
		$sp->setUser( NULL );
		$sp->PayStubAccounts( 'US', 'CA' );
		$sp->PayrollRemittanceAgencys( 'US', 'CA', NULL, NULL, $this->legal_entity_id );
		$sp->CompanyDeductions( 'US', 'CA', NULL, NULL, $this->legal_entity_id );

		//Need to define the California State Unemployment Percent.
		$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
		$cdlf->getByCompanyIdAndName( $this->company_id, 'CA - Unemployment Insurance - Employer' );
		if ( $cdlf->getRecordCount() > 0 ) {
			$cd_obj = $cdlf->getCurrent();
			$cd_obj->setUserValue1( 0.047 ); //Percent.
			if ( $cd_obj->isValid() ) {
				$cd_obj->Save();
			}
		} else {
			$this->assertTrue( FALSE, 'CA - Unemployment Insurance failed to be created.' );
		}

		//Need to define the California State Unemployment Percent.
		$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
		$cdlf->getByCompanyIdAndName( $this->company_id, 'NY - Unemployment Insurance - Employer' );
		if ( $cdlf->getRecordCount() > 0 ) {
			$cd_obj = $cdlf->getCurrent();
			$cd_obj->setUserValue1( 0.056 ); //Percent.
			if ( $cd_obj->isValid() ) {
				$cd_obj->Save();
			}
		} else {
			$this->assertTrue( FALSE, 'NY - Unemployment Insurance failed to be created.' );
		}


		$remittance_source_account_ids[$this->legal_entity_id][] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id, 10  ); // Check
		$remittance_source_account_ids[$this->legal_entity_id][] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id, 20  ); // US - EFT
		$remittance_source_account_ids[$this->legal_entity_id][] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id, 30  ); // CA - EFT

		//createUser() also handles remittance destination accounts.
		$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 100, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $remittance_source_account_ids );
		$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $remittance_source_account_ids );
		$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $remittance_source_account_ids );
		$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $remittance_source_account_ids ); //Different State
		$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 21, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $remittance_source_account_ids ); //Different State


		//Get User Object.
		$ulf = new UserListFactory();
		$this->user_obj = $ulf->getById( $this->user_id[0] )->getCurrent();

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$dd->createTaxForms( $this->company_id, $this->user_id[0] );

		$this->assertGreaterThan( 0, $this->company_id );
		$this->assertGreaterThan( 0, $this->user_id[0] );

		return TRUE;
	}

	public function tearDown() {
		Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	function getPayStubAccountLinkArray() {
		$this->pay_stub_account_link_arr = array(
			'total_gross' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Total Gross'),
			'total_deductions' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Total Deductions'),
			'employer_contribution' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 40, 'Employer Total Contributions'),
			'net_pay' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 40, 'Net Pay'),
			'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
			'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Vacation - Accrual Release'),
			'vacation_accrual' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 50, 'Vacation Accrual'),
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

			$ppsf->setUser( $this->user_id );
			$ppsf->Save();

			$this->pay_period_schedule_id = $insert_id;

			return $insert_id;
		}

		Debug::Text('Failed Creating Pay Period Schedule!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;

	}

	function createPayPeriods() {
		$max_pay_periods = 14;

		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getById( $this->pay_period_schedule_id );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pps_obj = $ppslf->getCurrent();

			for ( $i = 0; $i < $max_pay_periods; $i++ ) {
				if ( $i == 0 ) {
					$end_date = TTDate::getBeginYearEpoch( strtotime('01-Jan-2019') );
				} else {
					$end_date = TTDate::incrementDate( $end_date, 14, 'day' );
				}

				Debug::Text('I: '. $i .' End Date: '. TTDate::getDate('DATE+TIME', $end_date), __FILE__, __LINE__, __METHOD__, 10);

				$pps_obj->createNextPayPeriod( $end_date, (86400 + 3600), FALSE ); //Don't import punches, as that causes deadlocks when running tests in parallel.
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
					'rate' => $pse_obj->getRate(),
					'units' => $pse_obj->getUnits(),
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

	function createPayStubAmendment( $pay_stub_entry_name_id, $amount, $effective_date, $user_id ) {
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $user_id );
		$psaf->setPayStubEntryNameId( $pay_stub_entry_name_id ); //CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Bonus')
		$psaf->setStatus( 50 ); //Active

		$psaf->setType( 10 );
//		$psaf->setRate( 10 );
//		$psaf->setUnits( 10 );
		$psaf->setAmount( $amount );

		$psaf->setEffectiveDate( $effective_date );

		$psaf->setAuthorized(TRUE);
		if ( $psaf->isValid() ) {
			$psaf->Save();
		} else {
			Debug::text(' ERROR: Pay Stub Amendment Failed!', __FILE__, __LINE__, __METHOD__, 10);
		}

		return TRUE;
	}

	function createPayStub( $user_id ) {
		for( $i = 0; $i <= 12; $i++ ) { //Calculate pay stubs for each pay period.
			$cps = new CalculatePayStub();
			$cps->setUser( $user_id );
			$cps->setPayPeriod( $this->pay_period_objs[$i]->getId() );
			$cps->calculate();
		}

		return TRUE;
	}

	/**
	 * @group Form940Report_testMonthlyDeposit
	 */
	function testMonthlyDeposit() {
		foreach( $this->user_id as $user_id ) {
			//1st Quarter - Stay below 7000 FUTA limit
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.33, TTDate::getMiddleDayEpoch( $this->pay_period_objs[1]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.33, TTDate::getMiddleDayEpoch( $this->pay_period_objs[1]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.32, TTDate::getMiddleDayEpoch( $this->pay_period_objs[2]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.32, TTDate::getMiddleDayEpoch( $this->pay_period_objs[2]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.31, TTDate::getMiddleDayEpoch( $this->pay_period_objs[3]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.31, TTDate::getMiddleDayEpoch( $this->pay_period_objs[3]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.30, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.30, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ), $user_id );

			//2nd Quarter - Cross 7000 FUTA limit
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[5]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[5]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.28, TTDate::getMiddleDayEpoch( $this->pay_period_objs[6]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.28, TTDate::getMiddleDayEpoch( $this->pay_period_objs[6]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[7]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[7]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ), $user_id );

			//Extra pay period outside the 1st and 2nd quarter.
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ), $user_id );

			$this->createPayStub( $user_id );
		}



		//Generate Report for 1st Quarter
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = array( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips') ); //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$report_obj->setFormConfig( $form_config );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_1st_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( $report_output[0]['date_month'], 'January' );
		$this->assertEquals( $report_output[0]['total_payments'], 5105.10 );
		$this->assertEquals( $report_output[0]['exempt_payments'], 51.70 );
		$this->assertEquals( $report_output[0]['excess_payments'], 0.00 );
		$this->assertEquals( $report_output[0]['taxable_wages'], 5053.40 );
		$this->assertEquals( $report_output[0]['before_adjustment_tax'], 30.32 );
		$this->assertEquals( $report_output[0]['adjustment_tax'], 192.03 );
		$this->assertEquals( $report_output[0]['after_adjustment_tax'], 222.35 );

		$this->assertEquals( $report_output[1]['date_month'], 'February' );
		$this->assertEquals( $report_output[1]['total_payments'], 10209.75 );
		$this->assertEquals( $report_output[1]['exempt_payments'], 103.25 );
		$this->assertEquals( $report_output[1]['excess_payments'], 0.00 );
		$this->assertEquals( $report_output[1]['taxable_wages'], 10106.50 );
		$this->assertEquals( $report_output[1]['before_adjustment_tax'], 60.64 );
		$this->assertEquals( $report_output[1]['adjustment_tax'], 384.05 );
		$this->assertEquals( $report_output[1]['after_adjustment_tax'], 444.69 );

		$this->assertEquals( $report_output[2]['date_month'], 'March' );
		$this->assertEquals( $report_output[2]['total_payments'], 10209.15 );
		$this->assertEquals( $report_output[2]['exempt_payments'], 103.05 );
		$this->assertEquals( $report_output[2]['excess_payments'], 0.00 );
		$this->assertEquals( $report_output[2]['taxable_wages'], 10106.10 );
		$this->assertEquals( $report_output[2]['before_adjustment_tax'], 60.64 );
		$this->assertEquals( $report_output[2]['adjustment_tax'], 384.03 );
		$this->assertEquals( $report_output[2]['after_adjustment_tax'], 444.67 );

		//Total
		$this->assertEquals( $report_output[3]['total_payments'], 25524.00 );
		$this->assertEquals( $report_output[3]['exempt_payments'], 258.00 );
		$this->assertEquals( $report_output[3]['excess_payments'], 0.00 );
		$this->assertEquals( $report_output[3]['taxable_wages'], 25266.00 );
		$this->assertEquals( $report_output[3]['before_adjustment_tax'], 151.60 );
		$this->assertEquals( $report_output[3]['adjustment_tax'], 960.11 );
		$this->assertEquals( $report_output[3]['after_adjustment_tax'], 1111.70 );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		$this->assertEquals( $form_objs->objs[0]->l3, 25524.00 );
		$this->assertEquals( $form_objs->objs[0]->l4, 258.00 );
		$this->assertEquals( $form_objs->objs[0]->l5, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l6, 258.00 );
		$this->assertEquals( $form_objs->objs[0]->l7, 25266.00 );
		$this->assertEquals( $form_objs->objs[0]->l8, 151.5960 );
		$this->assertEquals( $form_objs->objs[0]->l10, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l12, 151.5960 );
		$this->assertEquals( $form_objs->objs[0]->l14, 151.5960 );
		$this->assertEquals( $form_objs->objs[0]->l16a, 1111.7040 );



		//Generate Report for 2nd Quarter
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = array( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips') ); //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_2nd_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( $report_output[0]['date_month'], 'April' );
		$this->assertEquals( $report_output[0]['total_payments'], 10208.55 );
		$this->assertEquals( $report_output[0]['exempt_payments'], 102.85 );
		$this->assertEquals( $report_output[0]['excess_payments'], 371.70 );
		$this->assertEquals( $report_output[0]['taxable_wages'], 9734.00 );
		$this->assertEquals( $report_output[0]['before_adjustment_tax'], 58.40 );
		$this->assertEquals( $report_output[0]['adjustment_tax'], 369.89 );
		$this->assertEquals( $report_output[0]['after_adjustment_tax'], 428.30 );

		$this->assertEquals( $report_output[1]['date_month'], 'May' );
		$this->assertEquals( $report_output[1]['total_payments'], 15312.15 );
		$this->assertEquals( $report_output[1]['exempt_payments'], 154.05 );
		$this->assertEquals( $report_output[1]['excess_payments'], 15158.10 );
		$this->assertEquals( $report_output[1]['taxable_wages'], 0.00 );
		$this->assertEquals( $report_output[1]['before_adjustment_tax'], 0.00 );
		$this->assertEquals( $report_output[1]['adjustment_tax'], 0.00 );
		$this->assertEquals( $report_output[1]['after_adjustment_tax'], 0.00 );

		$this->assertEquals( $report_output[2]['date_month'], 'June' );
		$this->assertEquals( $report_output[2]['total_payments'], 10208.10 );
		$this->assertEquals( $report_output[2]['exempt_payments'], 102.70 );
		$this->assertEquals( $report_output[2]['excess_payments'], 10105.40 );
		$this->assertEquals( $report_output[2]['taxable_wages'], 0.00 );
		$this->assertEquals( $report_output[2]['before_adjustment_tax'], 0.00 );
		$this->assertEquals( $report_output[2]['adjustment_tax'], 0.00 );
		$this->assertEquals( $report_output[2]['after_adjustment_tax'], 0.00 );

		//Total
		$this->assertEquals( $report_output[3]['total_payments'], 35728.80 );
		$this->assertEquals( $report_output[3]['exempt_payments'], 359.60 );
		$this->assertEquals( $report_output[3]['excess_payments'], 25635.20 );
		$this->assertEquals( $report_output[3]['taxable_wages'], 9734.00 );
		$this->assertEquals( $report_output[3]['before_adjustment_tax'], 58.40 );
		$this->assertEquals( $report_output[3]['adjustment_tax'], 369.89 );
		$this->assertEquals( $report_output[3]['after_adjustment_tax'], 428.30 );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		$this->assertEquals( $form_objs->objs[0]->l3, 61252.80 );
		$this->assertEquals( $form_objs->objs[0]->l4, 617.60 );
		$this->assertEquals( $form_objs->objs[0]->l5, 25635.20 );
		$this->assertEquals( $form_objs->objs[0]->l6, 26252.80 );
		$this->assertEquals( $form_objs->objs[0]->l7, 35000.00 );
		$this->assertEquals( $form_objs->objs[0]->l8, 210.00 );
		$this->assertEquals( $form_objs->objs[0]->l10, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l12, 210.00 );
		$this->assertEquals( $form_objs->objs[0]->l14, 210.00 );
		$this->assertEquals( $form_objs->objs[0]->l16a, 151.5960 );
		$this->assertEquals( $form_objs->objs[0]->l16b, 428.2960 );



		//Generate Report for entire year
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = array( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips') ); //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_config['time_period']['start_date'] = strtotime('01-Jan-2019');
		$report_config['time_period']['end_date'] = strtotime('30-Jun-2019'); //Need to do the entire year so 'line_10' from above is used.
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( $report_output[0]['date_month'], 'January' );
		$this->assertEquals( $report_output[0]['total_payments'], 5105.10 );
		$this->assertEquals( $report_output[0]['exempt_payments'], 51.70 );
		$this->assertEquals( $report_output[0]['excess_payments'], 0.00 );
		$this->assertEquals( $report_output[0]['taxable_wages'], 5053.40 );
		$this->assertEquals( $report_output[0]['before_adjustment_tax'], 30.32 );
		$this->assertEquals( $report_output[0]['adjustment_tax'], 192.03 ); //This is different from 1st and 2nd quarter due to 'line_10' above.
		$this->assertEquals( $report_output[0]['after_adjustment_tax'], 222.35 ); //This is different from 1st and 2nd quarter due to 'line_10' above.

		$this->assertEquals( $report_output[1]['date_month'], 'February' );
		$this->assertEquals( $report_output[1]['total_payments'], 10209.75 );
		$this->assertEquals( $report_output[1]['exempt_payments'], 103.25 );
		$this->assertEquals( $report_output[1]['excess_payments'], 0.00 );
		$this->assertEquals( $report_output[1]['taxable_wages'], 10106.50 );
		$this->assertEquals( $report_output[1]['before_adjustment_tax'], 60.64 );
		$this->assertEquals( $report_output[1]['adjustment_tax'], 384.05 );
		$this->assertEquals( $report_output[1]['after_adjustment_tax'], 444.69 );

		$this->assertEquals( $report_output[2]['date_month'], 'March' );
		$this->assertEquals( $report_output[2]['total_payments'], 10209.15 );
		$this->assertEquals( $report_output[2]['exempt_payments'], 103.05 );
		$this->assertEquals( $report_output[2]['excess_payments'], 0.00 );
		$this->assertEquals( $report_output[2]['taxable_wages'], 10106.10 );
		$this->assertEquals( $report_output[2]['before_adjustment_tax'], 60.64 );
		$this->assertEquals( $report_output[2]['adjustment_tax'], 384.03 );
		$this->assertEquals( $report_output[2]['after_adjustment_tax'], 444.67 );

		$this->assertEquals( $report_output[3]['date_month'], 'April' );
		$this->assertEquals( $report_output[3]['total_payments'], 10208.55 );
		$this->assertEquals( $report_output[3]['exempt_payments'], 102.85 );
		$this->assertEquals( $report_output[3]['excess_payments'], 371.70 );
		$this->assertEquals( $report_output[3]['taxable_wages'], 9734.00 );
		$this->assertEquals( $report_output[3]['before_adjustment_tax'], 58.40 );
		$this->assertEquals( $report_output[3]['adjustment_tax'], 369.89 );
		$this->assertEquals( $report_output[3]['after_adjustment_tax'], 428.30 );

		$this->assertEquals( $report_output[4]['date_month'], 'May' );
		$this->assertEquals( $report_output[4]['total_payments'], 15312.15 );
		$this->assertEquals( $report_output[4]['exempt_payments'], 154.05 );
		$this->assertEquals( $report_output[4]['excess_payments'], 15158.10 );
		$this->assertEquals( $report_output[4]['taxable_wages'], 0.00 );
		$this->assertEquals( $report_output[4]['before_adjustment_tax'], 0.00 );
		$this->assertEquals( $report_output[4]['adjustment_tax'], 0.00 );
		$this->assertEquals( $report_output[4]['after_adjustment_tax'], 0.00 );

		$this->assertEquals( $report_output[5]['date_month'], 'June' );
		$this->assertEquals( $report_output[5]['total_payments'], 10208.10 );
		$this->assertEquals( $report_output[5]['exempt_payments'], 102.70 );
		$this->assertEquals( $report_output[5]['excess_payments'], 10105.40 );
		$this->assertEquals( $report_output[5]['taxable_wages'], 0.00 );
		$this->assertEquals( $report_output[5]['before_adjustment_tax'], 0.00 );
		$this->assertEquals( $report_output[5]['adjustment_tax'], 0.00 );
		$this->assertEquals( $report_output[5]['after_adjustment_tax'], 0.00 );

		//Total
		$this->assertEquals( $report_output[6]['total_payments'], 61252.80 );
		$this->assertEquals( $report_output[6]['exempt_payments'], 617.60 );
		$this->assertEquals( $report_output[6]['excess_payments'], 25635.20 );
		$this->assertEquals( $report_output[6]['taxable_wages'], 35000.00 );
		$this->assertEquals( $report_output[6]['before_adjustment_tax'], 210.00 );
		$this->assertEquals( $report_output[6]['adjustment_tax'], 1330.00 );
		$this->assertEquals( $report_output[6]['after_adjustment_tax'], 1540.00 );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		$this->assertEquals( $form_objs->objs[0]->l3, 61252.80 );
		$this->assertEquals( $form_objs->objs[0]->l4, 617.60 );
		$this->assertEquals( $form_objs->objs[0]->l5, 25635.20 );
		$this->assertEquals( $form_objs->objs[0]->l6, 26252.80 );
		$this->assertEquals( $form_objs->objs[0]->l7, 35000.00 );
		$this->assertEquals( $form_objs->objs[0]->l8, 210.00 );
		$this->assertEquals( $form_objs->objs[0]->l10, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l12, 210.00 );
		$this->assertEquals( $form_objs->objs[0]->l14, 210.00 );
		$this->assertEquals( $form_objs->objs[0]->l16a, 1111.7040 );
		$this->assertEquals( $form_objs->objs[0]->l16b, 428.2960 );

		return TRUE;
	}
}
?>
