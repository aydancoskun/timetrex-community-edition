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

class Form940ReportTest extends PHPUnit\Framework\TestCase {
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

		$this->currency_id = $dd->createCurrency( $this->company_id, 10 );

		//Permissions are required so the user has permissions to run reports.
		$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$dd->createPayStubAccount( $this->company_id );
		$dd->createPayStubAccountLink( $this->company_id );
		$this->getPayStubAccountLinkArray();

		$dd->createUserWageGroups( $this->company_id );

		$dd->createPayrollRemittanceAgency( $this->company_id, null, $this->legal_entity_id ); //Must go before createCompanyDeduction()

		//Company Deductions
		$dd->createCompanyDeduction( $this->company_id, null, $this->legal_entity_id );

		//Create multiple state tax/deductions.
		$sp = TTNew( 'SetupPresets' ); /** @var SetupPresets $sp */
		$sp->setCompany( $this->company_id );
		$sp->setUser( null );
		$sp->PayStubAccounts( 'US', 'CA' );
		$sp->PayrollRemittanceAgencys( 'US', 'CA', null, null, $this->legal_entity_id );
		$sp->CompanyDeductions( 'US', 'CA', null, null, $this->legal_entity_id );

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
			$this->assertTrue( false, 'CA - Unemployment Insurance failed to be created.' );
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
			$this->assertTrue( false, 'NY - Unemployment Insurance failed to be created.' );
		}


		$remittance_source_account_ids[$this->legal_entity_id][] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id, 10 ); // Check
		$remittance_source_account_ids[$this->legal_entity_id][] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id, 20 ); // US - EFT
		$remittance_source_account_ids[$this->legal_entity_id][] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id, 30 ); // CA - EFT

		//createUser() also handles remittance destination accounts.
		$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 100, null, null, null, null, null, null, null, $remittance_source_account_ids );
		$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 10, null, null, null, null, null, null, null, $remittance_source_account_ids );
		$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 11, null, null, null, null, null, null, null, $remittance_source_account_ids );
		$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 12, null, null, null, null, null, null, null, $remittance_source_account_ids );
		$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 13, null, null, null, null, null, null, null, $remittance_source_account_ids );
		$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 14, null, null, null, null, null, null, null, $remittance_source_account_ids );
		$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 15, null, null, null, null, null, null, null, $remittance_source_account_ids );
		$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 16, null, null, null, null, null, null, null, $remittance_source_account_ids );
		$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 17, null, null, null, null, null, null, null, $remittance_source_account_ids );
		$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 18, null, null, null, null, null, null, null, $remittance_source_account_ids );
		$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 19, null, null, null, null, null, null, null, $remittance_source_account_ids );
		$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 20, null, null, null, null, null, null, null, $remittance_source_account_ids ); //Different State
		$this->user_id[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 21, null, null, null, null, null, null, null, $remittance_source_account_ids ); //Different State


		//Get User Object.
		$ulf = new UserListFactory();
		$this->user_obj = $ulf->getById( $this->user_id[0] )->getCurrent();

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$dd->createTaxForms( $this->company_id, $this->user_id[0] );

		$this->assertGreaterThan( 0, $this->company_id );
		$this->assertGreaterThan( 0, $this->user_id[0] );
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	function getPayStubAccountLinkArray() {
		$this->pay_stub_account_link_arr = [
				'total_gross'              => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Total Gross' ),
				'total_deductions'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Total Deductions' ),
				'employer_contribution'    => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Employer Total Contributions' ),
				'net_pay'                  => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Net Pay' ),
				'regular_time'             => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
				'vacation_accrual_release' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Vacation - Accrual Release' ),
				'vacation_accrual'         => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 50, 'Vacation Accrual' ),
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


		$anchor_date = TTDate::getBeginWeekEpoch( TTDate::getBeginYearEpoch() ); //Start 6 weeks ago

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

			$ppsf->setUser( $this->user_id );
			$ppsf->Save();

			$this->pay_period_schedule_id = $insert_id;

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Pay Period Schedule!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function createPayPeriods() {
		$max_pay_periods = 28;

		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getById( $this->pay_period_schedule_id );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pps_obj = $ppslf->getCurrent();

			$end_date = null;
			for ( $i = 0; $i < $max_pay_periods; $i++ ) {
				if ( $i == 0 ) {
					$end_date = TTDate::getEndDayEpoch( strtotime( '23-Dec-2018' ) );
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
						'rate'       => $pse_obj->getRate(),
						'units'      => $pse_obj->getUnits(),
						'amount'     => $pse_obj->getAmount(),
						'ytd_amount' => $pse_obj->getYTDAmount(),
				];
			}
		}

		if ( isset( $ps_entry_arr ) ) {
			return $ps_entry_arr;
		}

		return false;
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

		$psaf->setAuthorized( true );
		if ( $psaf->isValid() ) {
			$psaf->Save();
		} else {
			Debug::text( ' ERROR: Pay Stub Amendment Failed!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return true;
	}

	function createPayStub( $user_id ) {
		for ( $i = 0; $i <= 26; $i++ ) { //Calculate pay stubs for each pay period.
			$cps = new CalculatePayStub();
			$cps->setUser( $user_id );
			$cps->setPayPeriod( $this->pay_period_objs[$i]->getId() );
			$cps->calculate();
		}

		return true;
	}

	/**
	 * @group Form940Report_testMonthlyDepositSingleEmployeeCreditReductionA
	 */
	function testMonthlyDepositSingleEmployeeCreditReductionA() {

		foreach ( $this->user_id as $user_id ) {
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
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.26, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.25, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.24, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.23, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ), $user_id );

			//3rd Quarter - All above 7000 FUTA limit
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.22, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.21, TTDate::getMiddleDayEpoch( $this->pay_period_objs[13]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.28, TTDate::getMiddleDayEpoch( $this->pay_period_objs[13]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.20, TTDate::getMiddleDayEpoch( $this->pay_period_objs[14]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[14]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.19, TTDate::getMiddleDayEpoch( $this->pay_period_objs[15]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[15]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.18, TTDate::getMiddleDayEpoch( $this->pay_period_objs[16]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[16]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.17, TTDate::getMiddleDayEpoch( $this->pay_period_objs[17]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[17]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.16, TTDate::getMiddleDayEpoch( $this->pay_period_objs[18]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[18]->getEndDate() ), $user_id );

			//4th Quarter - All above 7000 FUTA limit
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.15, TTDate::getMiddleDayEpoch( $this->pay_period_objs[19]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[19]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.14, TTDate::getMiddleDayEpoch( $this->pay_period_objs[20]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.28, TTDate::getMiddleDayEpoch( $this->pay_period_objs[20]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.13, TTDate::getMiddleDayEpoch( $this->pay_period_objs[21]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[21]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.12, TTDate::getMiddleDayEpoch( $this->pay_period_objs[22]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[22]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.11, TTDate::getMiddleDayEpoch( $this->pay_period_objs[23]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[23]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.10, TTDate::getMiddleDayEpoch( $this->pay_period_objs[24]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[24]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.09, TTDate::getMiddleDayEpoch( $this->pay_period_objs[25]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[25]->getEndDate() ), $user_id );

			//Extra pay period outside the 1st and 2nd quarter.
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.01, TTDate::getMiddleDayEpoch( $this->pay_period_objs[26]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[26]->getEndDate() ), $user_id );

			$this->createPayStub( $user_id );

			break; //Only do a single employee.
		}


		//Generate Report for 1st Quarter
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = true; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_1st_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0 =>
						[
								'date_month'            => 'January',
								'total_payments'        => '2042.01',
								'exempt_payments'       => '20.67',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '2021.34',
								'before_adjustment_tax' => '12.13',
								'adjustment_tax'        => '76.81',
								'after_adjustment_tax'  => '88.94',
						],
				1 =>
						[
								'date_month'            => 'February',
								'total_payments'        => '2041.89',
								'exempt_payments'       => '20.63',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '2021.26',
								'before_adjustment_tax' => '12.13',
								'adjustment_tax'        => '76.81',
								'after_adjustment_tax'  => '88.94',
						],
				2 =>
						[
								'date_month'            => 'March',
								'total_payments'        => '2041.77',
								'exempt_payments'       => '20.59',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '2021.18',
								'before_adjustment_tax' => '12.13',
								'adjustment_tax'        => '76.80',
								'after_adjustment_tax'  => '88.93',
						],
				3 =>
						[
								'date_month'            => 'Grand Total[3]:',
								'total_payments'        => '6125.67',
								'exempt_payments'       => '61.89',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '6063.78',
								'before_adjustment_tax' => '36.38',
								'adjustment_tax'        => '230.42',
								'after_adjustment_tax'  => '266.81',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 6125.67, $form_objs->objs[0]->l3 );
		$this->assertEquals( 61.89, $form_objs->objs[0]->l4 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5 );
		$this->assertEquals( 61.89, $form_objs->objs[0]->l6 );
		$this->assertEquals( 6063.78, $form_objs->objs[0]->l7 );
		$this->assertEquals( 36.38, $form_objs->objs[0]->l8 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l10 );
		$this->assertEquals( 230.42, $form_objs->objs[0]->l11 );
		$this->assertEquals( 266.80, $form_objs->objs[0]->l12 );
		$this->assertEquals( 266.80, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 266.81, $form_objs->objs[0]->l16a );
		$this->assertEquals( null, $form_objs->objs[0]->l16b );
		$this->assertEquals( null, $form_objs->objs[0]->l16c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16d );


		//Generate Report for 2nd Quarter
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = true; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_2nd_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0 =>
						[
								'date_month'            => 'April',
								'total_payments'        => '2041.65',
								'exempt_payments'       => '20.55',
								'excess_payments'       => '1084.88',
								'taxable_wages'         => '936.22',
								'before_adjustment_tax' => '5.62',
								'adjustment_tax'        => '35.58',
								'after_adjustment_tax'  => '41.19',
						],
				1 =>
						[
								'date_month'            => 'May',
								'total_payments'        => '3062.37',
								'exempt_payments'       => '30.81',
								'excess_payments'       => '3031.56',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				2 =>
						[
								'date_month'            => 'June',
								'total_payments'        => '2041.57',
								'exempt_payments'       => '20.56',
								'excess_payments'       => '2021.01',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				3 =>
						[
								'date_month'            => 'Grand Total[3]:',
								'total_payments'        => '7145.59',
								'exempt_payments'       => '71.92',
								'excess_payments'       => '6137.45',
								'taxable_wages'         => '936.22',
								'before_adjustment_tax' => '5.62',
								'adjustment_tax'        => '35.58',
								'after_adjustment_tax'  => '41.19',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );

		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 13271.26, $form_objs->objs[0]->l3 );
		$this->assertEquals( 133.81, $form_objs->objs[0]->l4 );
		$this->assertEquals( 6137.45, $form_objs->objs[0]->l5 );
		$this->assertEquals( 6271.26, $form_objs->objs[0]->l6 );
		$this->assertEquals( 7000.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 42, $form_objs->objs[0]->l8 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l10 );
		$this->assertEquals( 378.74, $form_objs->objs[0]->l12 );
		$this->assertEquals( 378.74, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 36.38, $form_objs->objs[0]->l16a );
		$this->assertEquals( 41.19, $form_objs->objs[0]->l16b );
		$this->assertEquals( null, $form_objs->objs[0]->l16c );
		$this->assertEquals( null, $form_objs->objs[0]->l16d );


		//Generate Report for 3rd Quarter
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = true; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_3rd_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0 =>
						[
								'date_month'            => 'July',
								'total_payments'        => '2041.51',
								'exempt_payments'       => '20.55',
								'excess_payments'       => '2020.96',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				1 =>
						[
								'date_month'            => 'August',
								'total_payments'        => '2041.45',
								'exempt_payments'       => '20.54',
								'excess_payments'       => '2020.91',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				2 =>
						[
								'date_month'            => 'September',
								'total_payments'        => '2041.41',
								'exempt_payments'       => '20.54',
								'excess_payments'       => '2020.87',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				3 =>
						[
								'date_month'            => 'Grand Total[3]:',
								'total_payments'        => '6124.37',
								'exempt_payments'       => '61.63',
								'excess_payments'       => '6062.74',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 19395.63, $form_objs->objs[0]->l3 );
		$this->assertEquals( 195.44, $form_objs->objs[0]->l4 );
		$this->assertEquals( 12200.19, $form_objs->objs[0]->l5 );
		$this->assertEquals( 12395.63, $form_objs->objs[0]->l6 );
		$this->assertEquals( 7000.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 42.00, $form_objs->objs[0]->l8 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l10 );
		$this->assertEquals( 378.74, $form_objs->objs[0]->l12 );
		$this->assertEquals( 378.74, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 36.38, $form_objs->objs[0]->l16a );
		$this->assertEquals( 5.62, $form_objs->objs[0]->l16b );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16d );


		//Generate Report for 4th Quarter
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = true; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_4th_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0 =>
						[
								'date_month'            => 'October',
								'total_payments'        => '2041.43',
								'exempt_payments'       => '20.57',
								'excess_payments'       => '2020.86',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				1 =>
						[
								'date_month'            => 'November',
								'total_payments'        => '3061.98',
								'exempt_payments'       => '30.81',
								'excess_payments'       => '3031.17',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				2 =>
						[
								'date_month'            => 'December',
								'total_payments'        => '2041.27',
								'exempt_payments'       => '20.54',
								'excess_payments'       => '2020.73',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				3 =>
						[
								'date_month'            => 'Grand Total[3]:',
								'total_payments'        => '7144.68',
								'exempt_payments'       => '71.92',
								'excess_payments'       => '7072.76',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 26540.31, $form_objs->objs[0]->l3 );
		$this->assertEquals( 267.36, $form_objs->objs[0]->l4 );
		$this->assertEquals( 19272.95, $form_objs->objs[0]->l5 );
		$this->assertEquals( 19540.31, $form_objs->objs[0]->l6 );
		$this->assertEquals( 7000.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 42.00, $form_objs->objs[0]->l8 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l10 );
		$this->assertEquals( 378.74, $form_objs->objs[0]->l12 );
		$this->assertEquals( 378.74, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 36.38, $form_objs->objs[0]->l16a );
		$this->assertEquals( 5.62, $form_objs->objs[0]->l16b );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16d );


		//Generate Report for entire year with Line 10
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = true; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_config['time_period']['start_date'] = strtotime( '01-Jan-2019' );
		$report_config['time_period']['end_date'] = strtotime( '31-Dec-2019' ); //Need to do the entire year so 'line_10' from above is used.
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0  =>
						[
								'date_month'            => 'January',
								'total_payments'        => '2042.01',
								'exempt_payments'       => '20.67',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '2021.34',
								'before_adjustment_tax' => '12.13',
								'adjustment_tax'        => '76.81',
								'after_adjustment_tax'  => '88.94',
						],
				1  =>
						[
								'date_month'            => 'February',
								'total_payments'        => '2041.89',
								'exempt_payments'       => '20.63',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '2021.26',
								'before_adjustment_tax' => '12.13',
								'adjustment_tax'        => '76.81',
								'after_adjustment_tax'  => '88.94',
						],
				2  =>
						[
								'date_month'            => 'March',
								'total_payments'        => '2041.77',
								'exempt_payments'       => '20.59',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '2021.18',
								'before_adjustment_tax' => '12.13',
								'adjustment_tax'        => '76.80',
								'after_adjustment_tax'  => '88.93',
						],
				3  =>
						[
								'date_month'            => 'April',
								'total_payments'        => '2041.65',
								'exempt_payments'       => '20.55',
								'excess_payments'       => '1084.88',
								'taxable_wages'         => '936.22',
								'before_adjustment_tax' => '5.62',
								'adjustment_tax'        => '35.58',
								'after_adjustment_tax'  => '41.19',
						],
				4  =>
						[
								'date_month'            => 'May',
								'total_payments'        => '3062.37',
								'exempt_payments'       => '30.81',
								'excess_payments'       => '3031.56',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				5  =>
						[
								'date_month'            => 'June',
								'total_payments'        => '2041.57',
								'exempt_payments'       => '20.56',
								'excess_payments'       => '2021.01',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				6  =>
						[
								'date_month'            => 'July',
								'total_payments'        => '2041.51',
								'exempt_payments'       => '20.55',
								'excess_payments'       => '2020.96',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				7  =>
						[
								'date_month'            => 'August',
								'total_payments'        => '2041.45',
								'exempt_payments'       => '20.54',
								'excess_payments'       => '2020.91',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				8  =>
						[
								'date_month'            => 'September',
								'total_payments'        => '2041.41',
								'exempt_payments'       => '20.54',
								'excess_payments'       => '2020.87',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				9  =>
						[
								'date_month'            => 'October',
								'total_payments'        => '2041.43',
								'exempt_payments'       => '20.57',
								'excess_payments'       => '2020.86',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				10 =>
						[
								'date_month'            => 'November',
								'total_payments'        => '3061.98',
								'exempt_payments'       => '30.81',
								'excess_payments'       => '3031.17',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				11 =>
						[
								'date_month'            => 'December',
								'total_payments'        => '2041.27',
								'exempt_payments'       => '20.54',
								'excess_payments'       => '2020.73',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				12 =>
						[
								'date_month'            => 'Grand Total[12]:',
								'total_payments'        => '26540.31',
								'exempt_payments'       => '267.36',
								'excess_payments'       => '19272.95',
								'taxable_wages'         => '7000.00',
								'before_adjustment_tax' => '42.00',
								'adjustment_tax'        => '266.00',
								'after_adjustment_tax'  => '308.00',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 26540.31, $form_objs->objs[0]->l3 );
		$this->assertEquals( 267.36, $form_objs->objs[0]->l4 );
		$this->assertEquals( 19272.95, $form_objs->objs[0]->l5 );
		$this->assertEquals( 19540.31, $form_objs->objs[0]->l6 );
		$this->assertEquals( 7000.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 42.00, $form_objs->objs[0]->l8 );
		$this->assertEquals( 100.03, $form_objs->objs[0]->l10 );
		$this->assertEquals( 478.77, $form_objs->objs[0]->l12 );
		$this->assertEquals( 478.77, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 266.81, $form_objs->objs[0]->l16a );
		$this->assertEquals( 41.19, $form_objs->objs[0]->l16b );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16d );


		//Generate Report for entire year *without* Line 10
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		//$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = true; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_config['time_period']['start_date'] = strtotime( '01-Jan-2019' );
		$report_config['time_period']['end_date'] = strtotime( '31-Dec-2019' ); //Need to do the entire year so 'line_10' from above is used.
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0  =>
						[
								'date_month'            => 'January',
								'total_payments'        => '2042.01',
								'exempt_payments'       => '20.67',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '2021.34',
								'before_adjustment_tax' => '12.13',
								'adjustment_tax'        => '76.81',
								'after_adjustment_tax'  => '88.94',
						],
				1  =>
						[
								'date_month'            => 'February',
								'total_payments'        => '2041.89',
								'exempt_payments'       => '20.63',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '2021.26',
								'before_adjustment_tax' => '12.13',
								'adjustment_tax'        => '76.81',
								'after_adjustment_tax'  => '88.94',
						],
				2  =>
						[
								'date_month'            => 'March',
								'total_payments'        => '2041.77',
								'exempt_payments'       => '20.59',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '2021.18',
								'before_adjustment_tax' => '12.13',
								'adjustment_tax'        => '76.80',
								'after_adjustment_tax'  => '88.93',
						],
				3  =>
						[
								'date_month'            => 'April',
								'total_payments'        => '2041.65',
								'exempt_payments'       => '20.55',
								'excess_payments'       => '1084.88',
								'taxable_wages'         => '936.22',
								'before_adjustment_tax' => '5.62',
								'adjustment_tax'        => '35.58',
								'after_adjustment_tax'  => '41.19',
						],
				4  =>
						[
								'date_month'            => 'May',
								'total_payments'        => '3062.37',
								'exempt_payments'       => '30.81',
								'excess_payments'       => '3031.56',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				5  =>
						[
								'date_month'            => 'June',
								'total_payments'        => '2041.57',
								'exempt_payments'       => '20.56',
								'excess_payments'       => '2021.01',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				6  =>
						[
								'date_month'            => 'July',
								'total_payments'        => '2041.51',
								'exempt_payments'       => '20.55',
								'excess_payments'       => '2020.96',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				7  =>
						[
								'date_month'            => 'August',
								'total_payments'        => '2041.45',
								'exempt_payments'       => '20.54',
								'excess_payments'       => '2020.91',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				8  =>
						[
								'date_month'            => 'September',
								'total_payments'        => '2041.41',
								'exempt_payments'       => '20.54',
								'excess_payments'       => '2020.87',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				9  =>
						[
								'date_month'            => 'October',
								'total_payments'        => '2041.43',
								'exempt_payments'       => '20.57',
								'excess_payments'       => '2020.86',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				10 =>
						[
								'date_month'            => 'November',
								'total_payments'        => '3061.98',
								'exempt_payments'       => '30.81',
								'excess_payments'       => '3031.17',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				11 =>
						[
								'date_month'            => 'December',
								'total_payments'        => '2041.27',
								'exempt_payments'       => '20.54',
								'excess_payments'       => '2020.73',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				12 =>
						[
								'date_month'            => 'Grand Total[12]:',
								'total_payments'        => '26540.31',
								'exempt_payments'       => '267.36',
								'excess_payments'       => '19272.95',
								'taxable_wages'         => '7000.00',
								'before_adjustment_tax' => '42.00',
								'adjustment_tax'        => '266.00',
								'after_adjustment_tax'  => '308.00',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 26540.31, $form_objs->objs[0]->l3 );
		$this->assertEquals( 267.36, $form_objs->objs[0]->l4 );
		$this->assertEquals( 19272.95, $form_objs->objs[0]->l5 );
		$this->assertEquals( 19540.31, $form_objs->objs[0]->l6 );
		$this->assertEquals( 7000.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 42.00, $form_objs->objs[0]->l8 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l10 );
		$this->assertEquals( 378.74, $form_objs->objs[0]->l12 );
		$this->assertEquals( 378.74, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 266.81, $form_objs->objs[0]->l16a );
		$this->assertEquals( 41.19, $form_objs->objs[0]->l16b );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16d );

		return true;
	}

	/**
	 * @group Form940Report_testMonthlyDepositSingleEmployeeNoCreditReductionA
	 */
	function testMonthlyDepositSingleEmployeeNoCreditReductionA() {

		foreach ( $this->user_id as $user_id ) {
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
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.26, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.25, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.24, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.23, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ), $user_id );

			//3rd Quarter - All above 7000 FUTA limit
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.22, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.21, TTDate::getMiddleDayEpoch( $this->pay_period_objs[13]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.28, TTDate::getMiddleDayEpoch( $this->pay_period_objs[13]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.20, TTDate::getMiddleDayEpoch( $this->pay_period_objs[14]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[14]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.19, TTDate::getMiddleDayEpoch( $this->pay_period_objs[15]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[15]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.18, TTDate::getMiddleDayEpoch( $this->pay_period_objs[16]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[16]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.17, TTDate::getMiddleDayEpoch( $this->pay_period_objs[17]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[17]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.16, TTDate::getMiddleDayEpoch( $this->pay_period_objs[18]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[18]->getEndDate() ), $user_id );

			//4th Quarter - All above 7000 FUTA limit
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.15, TTDate::getMiddleDayEpoch( $this->pay_period_objs[19]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[19]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.14, TTDate::getMiddleDayEpoch( $this->pay_period_objs[20]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.28, TTDate::getMiddleDayEpoch( $this->pay_period_objs[20]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.13, TTDate::getMiddleDayEpoch( $this->pay_period_objs[21]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[21]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.12, TTDate::getMiddleDayEpoch( $this->pay_period_objs[22]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[22]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.11, TTDate::getMiddleDayEpoch( $this->pay_period_objs[23]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[23]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.10, TTDate::getMiddleDayEpoch( $this->pay_period_objs[24]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[24]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.09, TTDate::getMiddleDayEpoch( $this->pay_period_objs[25]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[25]->getEndDate() ), $user_id );

			//Extra pay period outside the 1st and 2nd quarter.
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.01, TTDate::getMiddleDayEpoch( $this->pay_period_objs[26]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[26]->getEndDate() ), $user_id );

			$this->createPayStub( $user_id );

			break; //Only do a single employee.
		}


		//Generate Report for 1st Quarter
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = false; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_1st_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0 =>
						[
								'date_month'            => 'January',
								'total_payments'        => '2042.01',
								'exempt_payments'       => '20.67',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '2021.34',
								'before_adjustment_tax' => '12.13',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '12.13',
						],
				1 =>
						[
								'date_month'            => 'February',
								'total_payments'        => '2041.89',
								'exempt_payments'       => '20.63',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '2021.26',
								'before_adjustment_tax' => '12.13',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '12.13',
						],
				2 =>
						[
								'date_month'            => 'March',
								'total_payments'        => '2041.77',
								'exempt_payments'       => '20.59',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '2021.18',
								'before_adjustment_tax' => '12.13',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '12.13',
						],
				3 =>
						[
								'date_month'            => 'Grand Total[3]:',
								'total_payments'        => '6125.67',
								'exempt_payments'       => '61.89',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '6063.78',
								'before_adjustment_tax' => '36.38',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '36.38',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 6125.67, $form_objs->objs[0]->l3 );
		$this->assertEquals( 61.89, $form_objs->objs[0]->l4 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5 );
		$this->assertEquals( 61.89, $form_objs->objs[0]->l6 );
		$this->assertEquals( 6063.78, $form_objs->objs[0]->l7 );
		$this->assertEquals( 36.38, $form_objs->objs[0]->l8 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l10 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l11 );
		$this->assertEquals( 36.38, $form_objs->objs[0]->l12 );
		$this->assertEquals( 36.38, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 36.38, $form_objs->objs[0]->l16a );
		$this->assertEquals( null, $form_objs->objs[0]->l16b );
		$this->assertEquals( null, $form_objs->objs[0]->l16c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16d );


		//Generate Report for 2nd Quarter
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = false; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_2nd_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0 =>
						[
								'date_month'            => 'April',
								'total_payments'        => '2041.65',
								'exempt_payments'       => '20.55',
								'excess_payments'       => '1084.88',
								'taxable_wages'         => '936.22',
								'before_adjustment_tax' => '5.62',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '5.62',
						],
				1 =>
						[
								'date_month'            => 'May',
								'total_payments'        => '3062.37',
								'exempt_payments'       => '30.81',
								'excess_payments'       => '3031.56',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				2 =>
						[
								'date_month'            => 'June',
								'total_payments'        => '2041.57',
								'exempt_payments'       => '20.56',
								'excess_payments'       => '2021.01',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				3 =>
						[
								'date_month'            => 'Grand Total[3]:',
								'total_payments'        => '7145.59',
								'exempt_payments'       => '71.92',
								'excess_payments'       => '6137.45',
								'taxable_wages'         => '936.22',
								'before_adjustment_tax' => '5.62',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '5.62',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );

		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 13271.26, $form_objs->objs[0]->l3 );
		$this->assertEquals( 133.81, $form_objs->objs[0]->l4 );
		$this->assertEquals( 6137.45, $form_objs->objs[0]->l5 );
		$this->assertEquals( 6271.26, $form_objs->objs[0]->l6 );
		$this->assertEquals( 7000.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 42.00, $form_objs->objs[0]->l8 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l10 );
		$this->assertEquals( 42.00, $form_objs->objs[0]->l12 );
		$this->assertEquals( 42.00, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 36.38, $form_objs->objs[0]->l16a );
		$this->assertEquals( 5.62, $form_objs->objs[0]->l16b );
		$this->assertEquals( null, $form_objs->objs[0]->l16c );
		$this->assertEquals( null, $form_objs->objs[0]->l16d );


		//Generate Report for 3rd Quarter
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = false; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_3rd_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0 =>
						[
								'date_month'            => 'July',
								'total_payments'        => '2041.51',
								'exempt_payments'       => '20.55',
								'excess_payments'       => '2020.96',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				1 =>
						[
								'date_month'            => 'August',
								'total_payments'        => '2041.45',
								'exempt_payments'       => '20.54',
								'excess_payments'       => '2020.91',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				2 =>
						[
								'date_month'            => 'September',
								'total_payments'        => '2041.41',
								'exempt_payments'       => '20.54',
								'excess_payments'       => '2020.87',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				3 =>
						[
								'date_month'            => 'Grand Total[3]:',
								'total_payments'        => '6124.37',
								'exempt_payments'       => '61.63',
								'excess_payments'       => '6062.74',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 19395.63, $form_objs->objs[0]->l3 );
		$this->assertEquals( 195.44, $form_objs->objs[0]->l4 );
		$this->assertEquals( 12200.19, $form_objs->objs[0]->l5 );
		$this->assertEquals( 12395.63, $form_objs->objs[0]->l6 );
		$this->assertEquals( 7000.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 42.00, $form_objs->objs[0]->l8 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l10 );
		$this->assertEquals( 42.00, $form_objs->objs[0]->l12 );
		$this->assertEquals( 42.00, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 36.38, $form_objs->objs[0]->l16a );
		$this->assertEquals( 5.62, $form_objs->objs[0]->l16b );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16d );


		//Generate Report for 4th Quarter
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = false; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_4th_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0 =>
						[
								'date_month'            => 'October',
								'total_payments'        => '2041.43',
								'exempt_payments'       => '20.57',
								'excess_payments'       => '2020.86',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				1 =>
						[
								'date_month'            => 'November',
								'total_payments'        => '3061.98',
								'exempt_payments'       => '30.81',
								'excess_payments'       => '3031.17',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				2 =>
						[
								'date_month'            => 'December',
								'total_payments'        => '2041.27',
								'exempt_payments'       => '20.54',
								'excess_payments'       => '2020.73',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				3 =>
						[
								'date_month'            => 'Grand Total[3]:',
								'total_payments'        => '7144.68',
								'exempt_payments'       => '71.92',
								'excess_payments'       => '7072.76',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 26540.31, $form_objs->objs[0]->l3 );
		$this->assertEquals( 267.36, $form_objs->objs[0]->l4 );
		$this->assertEquals( 19272.95, $form_objs->objs[0]->l5 );
		$this->assertEquals( 19540.31, $form_objs->objs[0]->l6 );
		$this->assertEquals( 7000.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 42.00, $form_objs->objs[0]->l8 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l10 );
		$this->assertEquals( 42.00, $form_objs->objs[0]->l12 );
		$this->assertEquals( 42.00, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 36.38, $form_objs->objs[0]->l16a );
		$this->assertEquals( 5.62, $form_objs->objs[0]->l16b );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16d );


		//Generate Report for entire year with Line 10
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = false; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_config['time_period']['start_date'] = strtotime( '01-Jan-2019' );
		$report_config['time_period']['end_date'] = strtotime( '31-Dec-2019' ); //Need to do the entire year so 'line_10' from above is used.
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0  =>
						[
								'date_month'            => 'January',
								'total_payments'        => '2042.01',
								'exempt_payments'       => '20.67',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '2021.34',
								'before_adjustment_tax' => '12.13',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '12.13',
						],
				1  =>
						[
								'date_month'            => 'February',
								'total_payments'        => '2041.89',
								'exempt_payments'       => '20.63',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '2021.26',
								'before_adjustment_tax' => '12.13',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '12.13',
						],
				2  =>
						[
								'date_month'            => 'March',
								'total_payments'        => '2041.77',
								'exempt_payments'       => '20.59',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '2021.18',
								'before_adjustment_tax' => '12.13',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '12.13',
						],
				3  =>
						[
								'date_month'            => 'April',
								'total_payments'        => '2041.65',
								'exempt_payments'       => '20.55',
								'excess_payments'       => '1084.88',
								'taxable_wages'         => '936.22',
								'before_adjustment_tax' => '5.62',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '5.62',
						],
				4  =>
						[
								'date_month'            => 'May',
								'total_payments'        => '3062.37',
								'exempt_payments'       => '30.81',
								'excess_payments'       => '3031.56',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				5  =>
						[
								'date_month'            => 'June',
								'total_payments'        => '2041.57',
								'exempt_payments'       => '20.56',
								'excess_payments'       => '2021.01',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				6  =>
						[
								'date_month'            => 'July',
								'total_payments'        => '2041.51',
								'exempt_payments'       => '20.55',
								'excess_payments'       => '2020.96',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				7  =>
						[
								'date_month'            => 'August',
								'total_payments'        => '2041.45',
								'exempt_payments'       => '20.54',
								'excess_payments'       => '2020.91',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				8  =>
						[
								'date_month'            => 'September',
								'total_payments'        => '2041.41',
								'exempt_payments'       => '20.54',
								'excess_payments'       => '2020.87',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				9  =>
						[
								'date_month'            => 'October',
								'total_payments'        => '2041.43',
								'exempt_payments'       => '20.57',
								'excess_payments'       => '2020.86',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				10 =>
						[
								'date_month'            => 'November',
								'total_payments'        => '3061.98',
								'exempt_payments'       => '30.81',
								'excess_payments'       => '3031.17',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				11 =>
						[
								'date_month'            => 'December',
								'total_payments'        => '2041.27',
								'exempt_payments'       => '20.54',
								'excess_payments'       => '2020.73',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				12 =>
						[
								'date_month'            => 'Grand Total[12]:',
								'total_payments'        => '26540.31',
								'exempt_payments'       => '267.36',
								'excess_payments'       => '19272.95',
								'taxable_wages'         => '7000.00',
								'before_adjustment_tax' => '42.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '42.00',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 26540.31, $form_objs->objs[0]->l3 );
		$this->assertEquals( 267.36, $form_objs->objs[0]->l4 );
		$this->assertEquals( 19272.95, $form_objs->objs[0]->l5 );
		$this->assertEquals( 19540.31, $form_objs->objs[0]->l6 );
		$this->assertEquals( 7000.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 42.00, $form_objs->objs[0]->l8 );
		$this->assertEquals( 100.03, $form_objs->objs[0]->l10 );
		$this->assertEquals( 142.03, $form_objs->objs[0]->l12 );
		$this->assertEquals( 142.03, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 36.38, $form_objs->objs[0]->l16a );
		$this->assertEquals( 5.62, $form_objs->objs[0]->l16b );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16d );


		//Generate Report for entire year *without* Line 10
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		//$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = false; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_config['time_period']['start_date'] = strtotime( '01-Jan-2019' );
		$report_config['time_period']['end_date'] = strtotime( '31-Dec-2019' ); //Need to do the entire year so 'line_10' from above is used.
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0  =>
						[
								'date_month'            => 'January',
								'total_payments'        => '2042.01',
								'exempt_payments'       => '20.67',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '2021.34',
								'before_adjustment_tax' => '12.13',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '12.13',
						],
				1  =>
						[
								'date_month'            => 'February',
								'total_payments'        => '2041.89',
								'exempt_payments'       => '20.63',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '2021.26',
								'before_adjustment_tax' => '12.13',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '12.13',
						],
				2  =>
						[
								'date_month'            => 'March',
								'total_payments'        => '2041.77',
								'exempt_payments'       => '20.59',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '2021.18',
								'before_adjustment_tax' => '12.13',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '12.13',
						],
				3  =>
						[
								'date_month'            => 'April',
								'total_payments'        => '2041.65',
								'exempt_payments'       => '20.55',
								'excess_payments'       => '1084.88',
								'taxable_wages'         => '936.22',
								'before_adjustment_tax' => '5.62',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '5.62',
						],
				4  =>
						[
								'date_month'            => 'May',
								'total_payments'        => '3062.37',
								'exempt_payments'       => '30.81',
								'excess_payments'       => '3031.56',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				5  =>
						[
								'date_month'            => 'June',
								'total_payments'        => '2041.57',
								'exempt_payments'       => '20.56',
								'excess_payments'       => '2021.01',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				6  =>
						[
								'date_month'            => 'July',
								'total_payments'        => '2041.51',
								'exempt_payments'       => '20.55',
								'excess_payments'       => '2020.96',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				7  =>
						[
								'date_month'            => 'August',
								'total_payments'        => '2041.45',
								'exempt_payments'       => '20.54',
								'excess_payments'       => '2020.91',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				8  =>
						[
								'date_month'            => 'September',
								'total_payments'        => '2041.41',
								'exempt_payments'       => '20.54',
								'excess_payments'       => '2020.87',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				9  =>
						[
								'date_month'            => 'October',
								'total_payments'        => '2041.43',
								'exempt_payments'       => '20.57',
								'excess_payments'       => '2020.86',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				10 =>
						[
								'date_month'            => 'November',
								'total_payments'        => '3061.98',
								'exempt_payments'       => '30.81',
								'excess_payments'       => '3031.17',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				11 =>
						[
								'date_month'            => 'December',
								'total_payments'        => '2041.27',
								'exempt_payments'       => '20.54',
								'excess_payments'       => '2020.73',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				12 =>
						[
								'date_month'            => 'Grand Total[12]:',
								'total_payments'        => '26540.31',
								'exempt_payments'       => '267.36',
								'excess_payments'       => '19272.95',
								'taxable_wages'         => '7000.00',
								'before_adjustment_tax' => '42.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '42.00',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 26540.31, $form_objs->objs[0]->l3 );
		$this->assertEquals( 267.36, $form_objs->objs[0]->l4 );
		$this->assertEquals( 19272.95, $form_objs->objs[0]->l5 );
		$this->assertEquals( 19540.31, $form_objs->objs[0]->l6 );
		$this->assertEquals( 7000.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 42.00, $form_objs->objs[0]->l8 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l10 );
		$this->assertEquals( 42.00, $form_objs->objs[0]->l12 );
		$this->assertEquals( 42.00, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 36.38, $form_objs->objs[0]->l16a );
		$this->assertEquals( 5.62, $form_objs->objs[0]->l16b );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16d );

		return true;
	}

	/**
	 * @group Form940Report_testMonthlyDepositManyEmployeesCreditReductionA
	 */
	function testMonthlyDepositManyEmployeesCreditReductionA() {

		foreach ( $this->user_id as $user_id ) {
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
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.26, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.25, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.24, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.23, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ), $user_id );

			//3rd Quarter - All above 7000 FUTA limit
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.22, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.21, TTDate::getMiddleDayEpoch( $this->pay_period_objs[13]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.28, TTDate::getMiddleDayEpoch( $this->pay_period_objs[13]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.20, TTDate::getMiddleDayEpoch( $this->pay_period_objs[14]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[14]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.19, TTDate::getMiddleDayEpoch( $this->pay_period_objs[15]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[15]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.18, TTDate::getMiddleDayEpoch( $this->pay_period_objs[16]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[16]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.17, TTDate::getMiddleDayEpoch( $this->pay_period_objs[17]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[17]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.16, TTDate::getMiddleDayEpoch( $this->pay_period_objs[18]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[18]->getEndDate() ), $user_id );

			//4th Quarter - All above 7000 FUTA limit
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.15, TTDate::getMiddleDayEpoch( $this->pay_period_objs[19]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[19]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.14, TTDate::getMiddleDayEpoch( $this->pay_period_objs[20]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.28, TTDate::getMiddleDayEpoch( $this->pay_period_objs[20]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.13, TTDate::getMiddleDayEpoch( $this->pay_period_objs[21]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[21]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.12, TTDate::getMiddleDayEpoch( $this->pay_period_objs[22]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[22]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.11, TTDate::getMiddleDayEpoch( $this->pay_period_objs[23]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[23]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.10, TTDate::getMiddleDayEpoch( $this->pay_period_objs[24]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[24]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.09, TTDate::getMiddleDayEpoch( $this->pay_period_objs[25]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[25]->getEndDate() ), $user_id );

			//Extra pay period outside the 1st and 2nd quarter.
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.01, TTDate::getMiddleDayEpoch( $this->pay_period_objs[26]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[26]->getEndDate() ), $user_id );

			$this->createPayStub( $user_id );
		}


		//Generate Report for 1st Quarter
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = true; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_1st_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0 =>
						[
								'date_month'            => 'January',
								'total_payments'        => '26546.13',
								'exempt_payments'       => '268.71',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '26277.42',
								'before_adjustment_tax' => '157.66',
								'adjustment_tax'        => '998.54',
								'after_adjustment_tax'  => '1156.21',
						],
				1 =>
						[
								'date_month'            => 'February',
								'total_payments'        => '26544.57',
								'exempt_payments'       => '268.19',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '26276.38',
								'before_adjustment_tax' => '157.66',
								'adjustment_tax'        => '998.50',
								'after_adjustment_tax'  => '1156.16',
						],
				2 =>
						[
								'date_month'            => 'March',
								'total_payments'        => '26543.01',
								'exempt_payments'       => '267.67',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '26275.34',
								'before_adjustment_tax' => '157.65',
								'adjustment_tax'        => '998.46',
								'after_adjustment_tax'  => '1156.11',
						],
				3 =>
						[
								'date_month'            => 'Grand Total[3]:',
								'total_payments'        => '79633.71',
								'exempt_payments'       => '804.57',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '78829.14',
								'before_adjustment_tax' => '472.97',
								'adjustment_tax'        => '2995.51',
								'after_adjustment_tax'  => '3468.48',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 79633.71, $form_objs->objs[0]->l3 );
		$this->assertEquals( 804.57, $form_objs->objs[0]->l4 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5 );
		$this->assertEquals( 804.57, $form_objs->objs[0]->l6 );
		$this->assertEquals( 78829.14, $form_objs->objs[0]->l7 );
		$this->assertEquals( 472.97, $form_objs->objs[0]->l8 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l10 );
		$this->assertEquals( 2995.51, $form_objs->objs[0]->l11 );
		$this->assertEquals( 3468.48, $form_objs->objs[0]->l12 );
		$this->assertEquals( 3468.48, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 3468.48, $form_objs->objs[0]->l16a );
		$this->assertEquals( null, $form_objs->objs[0]->l16b );
		$this->assertEquals( null, $form_objs->objs[0]->l16c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16d );


		//Generate Report for 2nd Quarter
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = true; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_2nd_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0 =>
						[
								'date_month'            => 'April',
								'total_payments'        => '26541.45',
								'exempt_payments'       => '267.15',
								'excess_payments'       => '14103.44',
								'taxable_wages'         => '12170.86',
								'before_adjustment_tax' => '73.03',
								'adjustment_tax'        => '462.49',
								'after_adjustment_tax'  => '535.52',
						],
				1 =>
						[
								'date_month'            => 'May',
								'total_payments'        => '39810.81',
								'exempt_payments'       => '400.53',
								'excess_payments'       => '39410.28',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				2 =>
						[
								'date_month'            => 'June',
								'total_payments'        => '26540.41',
								'exempt_payments'       => '267.28',
								'excess_payments'       => '26273.13',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				3 =>
						[
								'date_month'            => 'Grand Total[3]:',
								'total_payments'        => '92892.67',
								'exempt_payments'       => '934.96',
								'excess_payments'       => '79786.85',
								'taxable_wages'         => '12170.86',
								'before_adjustment_tax' => '73.03',
								'adjustment_tax'        => '462.49',
								'after_adjustment_tax'  => '535.52',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );

		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 172526.38, $form_objs->objs[0]->l3 );
		$this->assertEquals( 1739.53, $form_objs->objs[0]->l4 );
		$this->assertEquals( 79786.85, $form_objs->objs[0]->l5 );
		$this->assertEquals( 81526.38, $form_objs->objs[0]->l6 );
		$this->assertEquals( 91000.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 546, $form_objs->objs[0]->l8 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l10 );
		$this->assertEquals( 4923.57, $form_objs->objs[0]->l12 );
		$this->assertEquals( 4923.57, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 472.97, $form_objs->objs[0]->l16a );
		$this->assertEquals( 535.52, $form_objs->objs[0]->l16b );
		$this->assertEquals( null, $form_objs->objs[0]->l16c );
		$this->assertEquals( 3915.08, $form_objs->objs[0]->l16d );


		//Generate Report for 3rd Quarter
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = true; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_3rd_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0 =>
						[
								'date_month'            => 'July',
								'total_payments'        => '26539.63',
								'exempt_payments'       => '267.15',
								'excess_payments'       => '26272.48',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				1 =>
						[
								'date_month'            => 'August',
								'total_payments'        => '26538.85',
								'exempt_payments'       => '267.02',
								'excess_payments'       => '26271.83',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				2 =>
						[
								'date_month'            => 'September',
								'total_payments'        => '26538.33',
								'exempt_payments'       => '267.02',
								'excess_payments'       => '26271.31',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				3 =>
						[
								'date_month'            => 'Grand Total[3]:',
								'total_payments'        => '79616.81',
								'exempt_payments'       => '801.19',
								'excess_payments'       => '78815.62',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 252143.19, $form_objs->objs[0]->l3 );
		$this->assertEquals( 2540.72, $form_objs->objs[0]->l4 );
		$this->assertEquals( 158602.47, $form_objs->objs[0]->l5 );
		$this->assertEquals( 161143.19, $form_objs->objs[0]->l6 );
		$this->assertEquals( 91000.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 546.00, $form_objs->objs[0]->l8 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l10 );
		$this->assertEquals( 4923.57, $form_objs->objs[0]->l12 );
		$this->assertEquals( 4923.57, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 472.97, $form_objs->objs[0]->l16a );
		$this->assertEquals( 73.03, $form_objs->objs[0]->l16b );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16c );
		$this->assertEquals( 4377.57, $form_objs->objs[0]->l16d );


		//Generate Report for 4th Quarter
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = true; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_4th_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0 =>
						[
								'date_month'            => 'October',
								'total_payments'        => '26538.59',
								'exempt_payments'       => '267.41',
								'excess_payments'       => '26271.18',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				1 =>
						[
								'date_month'            => 'November',
								'total_payments'        => '39805.74',
								'exempt_payments'       => '400.53',
								'excess_payments'       => '39405.21',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				2 =>
						[
								'date_month'            => 'December',
								'total_payments'        => '26536.51',
								'exempt_payments'       => '267.02',
								'excess_payments'       => '26269.49',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				3 =>
						[
								'date_month'            => 'Grand Total[3]:',
								'total_payments'        => '92880.84',
								'exempt_payments'       => '934.96',
								'excess_payments'       => '91945.88',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 345024.03, $form_objs->objs[0]->l3 );
		$this->assertEquals( 3475.68, $form_objs->objs[0]->l4 );
		$this->assertEquals( 250548.35, $form_objs->objs[0]->l5 );
		$this->assertEquals( 254024.03, $form_objs->objs[0]->l6 );
		$this->assertEquals( 91000.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 546.00, $form_objs->objs[0]->l8 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l10 );
		$this->assertEquals( 4923.57, $form_objs->objs[0]->l12 );
		$this->assertEquals( 4923.57, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 472.97, $form_objs->objs[0]->l16a );
		$this->assertEquals( 73.03, $form_objs->objs[0]->l16b );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16c );
		$this->assertEquals( 4377.57, $form_objs->objs[0]->l16d );


		//Generate Report for entire year with Line 10.
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = true; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_config['time_period']['start_date'] = strtotime( '01-Jan-2019' );
		$report_config['time_period']['end_date'] = strtotime( '31-Dec-2019' ); //Need to do the entire year so 'line_10' from above is used.
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0  =>
						[
								'date_month'            => 'January',
								'total_payments'        => '26546.13',
								'exempt_payments'       => '268.71',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '26277.42',
								'before_adjustment_tax' => '157.66',
								'adjustment_tax'        => '998.54',
								'after_adjustment_tax'  => '1156.21',
						],
				1  =>
						[
								'date_month'            => 'February',
								'total_payments'        => '26544.57',
								'exempt_payments'       => '268.19',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '26276.38',
								'before_adjustment_tax' => '157.66',
								'adjustment_tax'        => '998.50',
								'after_adjustment_tax'  => '1156.16',
						],
				2  =>
						[
								'date_month'            => 'March',
								'total_payments'        => '26543.01',
								'exempt_payments'       => '267.67',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '26275.34',
								'before_adjustment_tax' => '157.65',
								'adjustment_tax'        => '998.46',
								'after_adjustment_tax'  => '1156.11',
						],
				3  =>
						[
								'date_month'            => 'April',
								'total_payments'        => '26541.45',
								'exempt_payments'       => '267.15',
								'excess_payments'       => '14103.44',
								'taxable_wages'         => '12170.86',
								'before_adjustment_tax' => '73.03',
								'adjustment_tax'        => '462.49',
								'after_adjustment_tax'  => '535.52',
						],
				4  =>
						[
								'date_month'            => 'May',
								'total_payments'        => '39810.81',
								'exempt_payments'       => '400.53',
								'excess_payments'       => '39410.28',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				5  =>
						[
								'date_month'            => 'June',
								'total_payments'        => '26540.41',
								'exempt_payments'       => '267.28',
								'excess_payments'       => '26273.13',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				6  =>
						[
								'date_month'            => 'July',
								'total_payments'        => '26539.63',
								'exempt_payments'       => '267.15',
								'excess_payments'       => '26272.48',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				7  =>
						[
								'date_month'            => 'August',
								'total_payments'        => '26538.85',
								'exempt_payments'       => '267.02',
								'excess_payments'       => '26271.83',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				8  =>
						[
								'date_month'            => 'September',
								'total_payments'        => '26538.33',
								'exempt_payments'       => '267.02',
								'excess_payments'       => '26271.31',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				9  =>
						[
								'date_month'            => 'October',
								'total_payments'        => '26538.59',
								'exempt_payments'       => '267.41',
								'excess_payments'       => '26271.18',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				10 =>
						[
								'date_month'            => 'November',
								'total_payments'        => '39805.74',
								'exempt_payments'       => '400.53',
								'excess_payments'       => '39405.21',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				11 =>
						[
								'date_month'            => 'December',
								'total_payments'        => '26536.51',
								'exempt_payments'       => '267.02',
								'excess_payments'       => '26269.49',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				12 =>
						[
								'date_month'            => 'Grand Total[12]:',
								'total_payments'        => '345024.03',
								'exempt_payments'       => '3475.68',
								'excess_payments'       => '250548.35',
								'taxable_wages'         => '91000.00',
								'before_adjustment_tax' => '546.00',
								'adjustment_tax'        => '3458.00',
								'after_adjustment_tax'  => '4004.00',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 345024.03, $form_objs->objs[0]->l3 );
		$this->assertEquals( 3475.68, $form_objs->objs[0]->l4 );
		$this->assertEquals( 250548.35, $form_objs->objs[0]->l5 );
		$this->assertEquals( 254024.03, $form_objs->objs[0]->l6 );
		$this->assertEquals( 91000.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 546, $form_objs->objs[0]->l8 );
		$this->assertEquals( 100.03, $form_objs->objs[0]->l10 );
		$this->assertEquals( 5023.60, $form_objs->objs[0]->l12 );
		$this->assertEquals( 5023.60, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 3468.48, $form_objs->objs[0]->l16a );
		$this->assertEquals( 535.52, $form_objs->objs[0]->l16b );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16c );
		$this->assertEquals( 1019.60, $form_objs->objs[0]->l16d );


		//Generate Report for entire year with Line 10 *without line 10*
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		//$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = true; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_config['time_period']['start_date'] = strtotime( '01-Jan-2019' );
		$report_config['time_period']['end_date'] = strtotime( '31-Dec-2019' ); //Need to do the entire year so 'line_10' from above is used.
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0  =>
						[
								'date_month'            => 'January',
								'total_payments'        => '26546.13',
								'exempt_payments'       => '268.71',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '26277.42',
								'before_adjustment_tax' => '157.66',
								'adjustment_tax'        => '998.54',
								'after_adjustment_tax'  => '1156.21',
						],
				1  =>
						[
								'date_month'            => 'February',
								'total_payments'        => '26544.57',
								'exempt_payments'       => '268.19',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '26276.38',
								'before_adjustment_tax' => '157.66',
								'adjustment_tax'        => '998.50',
								'after_adjustment_tax'  => '1156.16',
						],
				2  =>
						[
								'date_month'            => 'March',
								'total_payments'        => '26543.01',
								'exempt_payments'       => '267.67',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '26275.34',
								'before_adjustment_tax' => '157.65',
								'adjustment_tax'        => '998.46',
								'after_adjustment_tax'  => '1156.11',
						],
				3  =>
						[
								'date_month'            => 'April',
								'total_payments'        => '26541.45',
								'exempt_payments'       => '267.15',
								'excess_payments'       => '14103.44',
								'taxable_wages'         => '12170.86',
								'before_adjustment_tax' => '73.03',
								'adjustment_tax'        => '462.49',
								'after_adjustment_tax'  => '535.52',
						],
				4  =>
						[
								'date_month'            => 'May',
								'total_payments'        => '39810.81',
								'exempt_payments'       => '400.53',
								'excess_payments'       => '39410.28',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				5  =>
						[
								'date_month'            => 'June',
								'total_payments'        => '26540.41',
								'exempt_payments'       => '267.28',
								'excess_payments'       => '26273.13',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				6  =>
						[
								'date_month'            => 'July',
								'total_payments'        => '26539.63',
								'exempt_payments'       => '267.15',
								'excess_payments'       => '26272.48',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				7  =>
						[
								'date_month'            => 'August',
								'total_payments'        => '26538.85',
								'exempt_payments'       => '267.02',
								'excess_payments'       => '26271.83',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				8  =>
						[
								'date_month'            => 'September',
								'total_payments'        => '26538.33',
								'exempt_payments'       => '267.02',
								'excess_payments'       => '26271.31',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				9  =>
						[
								'date_month'            => 'October',
								'total_payments'        => '26538.59',
								'exempt_payments'       => '267.41',
								'excess_payments'       => '26271.18',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				10 =>
						[
								'date_month'            => 'November',
								'total_payments'        => '39805.74',
								'exempt_payments'       => '400.53',
								'excess_payments'       => '39405.21',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				11 =>
						[
								'date_month'            => 'December',
								'total_payments'        => '26536.51',
								'exempt_payments'       => '267.02',
								'excess_payments'       => '26269.49',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				12 =>
						[
								'date_month'            => 'Grand Total[12]:',
								'total_payments'        => '345024.03',
								'exempt_payments'       => '3475.68',
								'excess_payments'       => '250548.35',
								'taxable_wages'         => '91000.00',
								'before_adjustment_tax' => '546.00',
								'adjustment_tax'        => '3458.00',
								'after_adjustment_tax'  => '4004.00',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 345024.03, $form_objs->objs[0]->l3 );
		$this->assertEquals( 3475.68, $form_objs->objs[0]->l4 );
		$this->assertEquals( 250548.35, $form_objs->objs[0]->l5 );
		$this->assertEquals( 254024.03, $form_objs->objs[0]->l6 );
		$this->assertEquals( 91000.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 546, $form_objs->objs[0]->l8 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l10 );
		$this->assertEquals( 4923.57, $form_objs->objs[0]->l12 );
		$this->assertEquals( 4923.57, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 3468.48, $form_objs->objs[0]->l16a );
		$this->assertEquals( 535.52, $form_objs->objs[0]->l16b );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16c );
		$this->assertEquals( 919.57, $form_objs->objs[0]->l16d );

		return true;
	}

	/*
	 * @group Form940Report_testMonthlyDepositManyEmployeesNoCreditReductionA
	 */
	function testMonthlyDepositManyEmployeesNoCreditReductionA() {

		foreach ( $this->user_id as $user_id ) {
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
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.26, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.25, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.24, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.23, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ), $user_id );

			//3rd Quarter - All above 7000 FUTA limit
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.22, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.21, TTDate::getMiddleDayEpoch( $this->pay_period_objs[13]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.28, TTDate::getMiddleDayEpoch( $this->pay_period_objs[13]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.20, TTDate::getMiddleDayEpoch( $this->pay_period_objs[14]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[14]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.19, TTDate::getMiddleDayEpoch( $this->pay_period_objs[15]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[15]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.18, TTDate::getMiddleDayEpoch( $this->pay_period_objs[16]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[16]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.17, TTDate::getMiddleDayEpoch( $this->pay_period_objs[17]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[17]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.16, TTDate::getMiddleDayEpoch( $this->pay_period_objs[18]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[18]->getEndDate() ), $user_id );

			//4th Quarter - All above 7000 FUTA limit
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.15, TTDate::getMiddleDayEpoch( $this->pay_period_objs[19]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[19]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.14, TTDate::getMiddleDayEpoch( $this->pay_period_objs[20]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.28, TTDate::getMiddleDayEpoch( $this->pay_period_objs[20]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.13, TTDate::getMiddleDayEpoch( $this->pay_period_objs[21]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[21]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.12, TTDate::getMiddleDayEpoch( $this->pay_period_objs[22]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[22]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.11, TTDate::getMiddleDayEpoch( $this->pay_period_objs[23]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[23]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.10, TTDate::getMiddleDayEpoch( $this->pay_period_objs[24]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[24]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.09, TTDate::getMiddleDayEpoch( $this->pay_period_objs[25]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[25]->getEndDate() ), $user_id );

			//Extra pay period outside the 1st and 2nd quarter.
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), 1000.01, TTDate::getMiddleDayEpoch( $this->pay_period_objs[26]->getEndDate() ), $user_id );
			$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[26]->getEndDate() ), $user_id );

			$this->createPayStub( $user_id );
		}


		//Generate Report for 1st Quarter
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = false; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_1st_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0 =>
						[
								'date_month'            => 'January',
								'total_payments'        => '26546.13',
								'exempt_payments'       => '268.71',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '26277.42',
								'before_adjustment_tax' => '157.66',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '157.66',
						],
				1 =>
						[
								'date_month'            => 'February',
								'total_payments'        => '26544.57',
								'exempt_payments'       => '268.19',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '26276.38',
								'before_adjustment_tax' => '157.66',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '157.66',
						],
				2 =>
						[
								'date_month'            => 'March',
								'total_payments'        => '26543.01',
								'exempt_payments'       => '267.67',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '26275.34',
								'before_adjustment_tax' => '157.65',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '157.65',
						],
				3 =>
						[
								'date_month'            => 'Grand Total[3]:',
								'total_payments'        => '79633.71',
								'exempt_payments'       => '804.57',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '78829.14',
								'before_adjustment_tax' => '472.97',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '472.97',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 79633.71, $form_objs->objs[0]->l3 );
		$this->assertEquals( 804.57, $form_objs->objs[0]->l4 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l5 );
		$this->assertEquals( 804.57, $form_objs->objs[0]->l6 );
		$this->assertEquals( 78829.14, $form_objs->objs[0]->l7 );
		$this->assertEquals( 472.97, $form_objs->objs[0]->l8 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l10 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l11 );
		$this->assertEquals( 472.97, $form_objs->objs[0]->l12 );
		$this->assertEquals( 472.97, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 472.97, $form_objs->objs[0]->l16a );
		$this->assertEquals( null, $form_objs->objs[0]->l16b );
		$this->assertEquals( null, $form_objs->objs[0]->l16c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16d );


		//Generate Report for 2nd Quarter
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = false; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_2nd_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0 =>
						[
								'date_month'            => 'April',
								'total_payments'        => '26541.45',
								'exempt_payments'       => '267.15',
								'excess_payments'       => '14103.44',
								'taxable_wages'         => '12170.86',
								'before_adjustment_tax' => '73.03',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '73.03',
						],
				1 =>
						[
								'date_month'            => 'May',
								'total_payments'        => '39810.81',
								'exempt_payments'       => '400.53',
								'excess_payments'       => '39410.28',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				2 =>
						[
								'date_month'            => 'June',
								'total_payments'        => '26540.41',
								'exempt_payments'       => '267.28',
								'excess_payments'       => '26273.13',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				3 =>
						[
								'date_month'            => 'Grand Total[3]:',
								'total_payments'        => '92892.67',
								'exempt_payments'       => '934.96',
								'excess_payments'       => '79786.85',
								'taxable_wages'         => '12170.86',
								'before_adjustment_tax' => '73.03',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '73.03',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );

		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 172526.38, $form_objs->objs[0]->l3 );
		$this->assertEquals( 1739.53, $form_objs->objs[0]->l4 );
		$this->assertEquals( 79786.85, $form_objs->objs[0]->l5 );
		$this->assertEquals( 81526.38, $form_objs->objs[0]->l6 );
		$this->assertEquals( 91000.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 546.00, $form_objs->objs[0]->l8 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l10 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l11 );
		$this->assertEquals( 546.00, $form_objs->objs[0]->l12 );
		$this->assertEquals( 546.00, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 472.97, $form_objs->objs[0]->l16a );
		$this->assertEquals( 73.03, $form_objs->objs[0]->l16b );
		$this->assertEquals( null, $form_objs->objs[0]->l16c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16d );


		//Generate Report for 3rd Quarter
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = false; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_3rd_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0 =>
						[
								'date_month'            => 'July',
								'total_payments'        => '26539.63',
								'exempt_payments'       => '267.15',
								'excess_payments'       => '26272.48',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				1 =>
						[
								'date_month'            => 'August',
								'total_payments'        => '26538.85',
								'exempt_payments'       => '267.02',
								'excess_payments'       => '26271.83',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				2 =>
						[
								'date_month'            => 'September',
								'total_payments'        => '26538.33',
								'exempt_payments'       => '267.02',
								'excess_payments'       => '26271.31',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				3 =>
						[
								'date_month'            => 'Grand Total[3]:',
								'total_payments'        => '79616.81',
								'exempt_payments'       => '801.19',
								'excess_payments'       => '78815.62',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 252143.19, $form_objs->objs[0]->l3 );
		$this->assertEquals( 2540.72, $form_objs->objs[0]->l4 );
		$this->assertEquals( 158602.47, $form_objs->objs[0]->l5 );
		$this->assertEquals( 161143.19, $form_objs->objs[0]->l6 );
		$this->assertEquals( 91000.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 546.00, $form_objs->objs[0]->l8 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l10 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l11 );
		$this->assertEquals( 546.00, $form_objs->objs[0]->l12 );
		$this->assertEquals( 546.00, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 472.97, $form_objs->objs[0]->l16a );
		$this->assertEquals( 73.03, $form_objs->objs[0]->l16b );
		$this->assertEquals( null, $form_objs->objs[0]->l16c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16d );


		//Generate Report for 4th Quarter
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = false; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_4th_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0 =>
						[
								'date_month'            => 'October',
								'total_payments'        => '26538.59',
								'exempt_payments'       => '267.41',
								'excess_payments'       => '26271.18',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				1 =>
						[
								'date_month'            => 'November',
								'total_payments'        => '39805.74',
								'exempt_payments'       => '400.53',
								'excess_payments'       => '39405.21',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				2 =>
						[
								'date_month'            => 'December',
								'total_payments'        => '26536.51',
								'exempt_payments'       => '267.02',
								'excess_payments'       => '26269.49',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				3 =>
						[
								'date_month'            => 'Grand Total[3]:',
								'total_payments'        => '92880.84',
								'exempt_payments'       => '934.96',
								'excess_payments'       => '91945.88',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 345024.03, $form_objs->objs[0]->l3 );
		$this->assertEquals( 3475.68, $form_objs->objs[0]->l4 );
		$this->assertEquals( 250548.35, $form_objs->objs[0]->l5 );
		$this->assertEquals( 254024.03, $form_objs->objs[0]->l6 );
		$this->assertEquals( 91000.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 546.00, $form_objs->objs[0]->l8 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l10 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l11 );
		$this->assertEquals( 546.00, $form_objs->objs[0]->l12 );
		$this->assertEquals( 546.00, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 472.97, $form_objs->objs[0]->l16a );
		$this->assertEquals( 73.03, $form_objs->objs[0]->l16b );
		$this->assertEquals( null, $form_objs->objs[0]->l16c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16d );


		//Generate Report for entire year with Line 10.
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = false; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_config['time_period']['start_date'] = strtotime( '01-Jan-2019' );
		$report_config['time_period']['end_date'] = strtotime( '31-Dec-2019' ); //Need to do the entire year so 'line_10' from above is used.
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0  =>
						[
								'date_month'            => 'January',
								'total_payments'        => '26546.13',
								'exempt_payments'       => '268.71',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '26277.42',
								'before_adjustment_tax' => '157.66',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '157.66',
						],
				1  =>
						[
								'date_month'            => 'February',
								'total_payments'        => '26544.57',
								'exempt_payments'       => '268.19',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '26276.38',
								'before_adjustment_tax' => '157.66',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '157.66',
						],
				2  =>
						[
								'date_month'            => 'March',
								'total_payments'        => '26543.01',
								'exempt_payments'       => '267.67',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '26275.34',
								'before_adjustment_tax' => '157.65',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '157.65',
						],
				3  =>
						[
								'date_month'            => 'April',
								'total_payments'        => '26541.45',
								'exempt_payments'       => '267.15',
								'excess_payments'       => '14103.44',
								'taxable_wages'         => '12170.86',
								'before_adjustment_tax' => '73.03',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '73.03',
						],
				4  =>
						[
								'date_month'            => 'May',
								'total_payments'        => '39810.81',
								'exempt_payments'       => '400.53',
								'excess_payments'       => '39410.28',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				5  =>
						[
								'date_month'            => 'June',
								'total_payments'        => '26540.41',
								'exempt_payments'       => '267.28',
								'excess_payments'       => '26273.13',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				6  =>
						[
								'date_month'            => 'July',
								'total_payments'        => '26539.63',
								'exempt_payments'       => '267.15',
								'excess_payments'       => '26272.48',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				7  =>
						[
								'date_month'            => 'August',
								'total_payments'        => '26538.85',
								'exempt_payments'       => '267.02',
								'excess_payments'       => '26271.83',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				8  =>
						[
								'date_month'            => 'September',
								'total_payments'        => '26538.33',
								'exempt_payments'       => '267.02',
								'excess_payments'       => '26271.31',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				9  =>
						[
								'date_month'            => 'October',
								'total_payments'        => '26538.59',
								'exempt_payments'       => '267.41',
								'excess_payments'       => '26271.18',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				10 =>
						[
								'date_month'            => 'November',
								'total_payments'        => '39805.74',
								'exempt_payments'       => '400.53',
								'excess_payments'       => '39405.21',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				11 =>
						[
								'date_month'            => 'December',
								'total_payments'        => '26536.51',
								'exempt_payments'       => '267.02',
								'excess_payments'       => '26269.49',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				12 =>
						[
								'date_month'            => 'Grand Total[12]:',
								'total_payments'        => '345024.03',
								'exempt_payments'       => '3475.68',
								'excess_payments'       => '250548.35',
								'taxable_wages'         => '91000.00',
								'before_adjustment_tax' => '546.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '546.00',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 345024.03, $form_objs->objs[0]->l3 );
		$this->assertEquals( 3475.68, $form_objs->objs[0]->l4 );
		$this->assertEquals( 250548.35, $form_objs->objs[0]->l5 );
		$this->assertEquals( 254024.03, $form_objs->objs[0]->l6 );
		$this->assertEquals( 91000.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 546.00, $form_objs->objs[0]->l8 );
		$this->assertEquals( 100.03, $form_objs->objs[0]->l10 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l11 );
		$this->assertEquals( 646.03, $form_objs->objs[0]->l12 );
		$this->assertEquals( 646.03, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 472.97, $form_objs->objs[0]->l16a );
		$this->assertEquals( 73.03, $form_objs->objs[0]->l16b );
		$this->assertEquals( null, $form_objs->objs[0]->l16c );
		$this->assertEquals( 100.03, $form_objs->objs[0]->l16d );

		//Generate Report for entire year with Line 10 *without line 10*
		$report_obj = new Form940Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['exempt_payments']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		//$form_config['line_10'] = 100.03; //This is ignored unless the time period is the entire year.
		$form_config['enable_credit_reduction_test'] = false; //Forces bogus credit reducation rates for testing.
		$report_obj->setFormConfig( $form_config );


		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_config['time_period']['start_date'] = strtotime( '01-Jan-2019' );
		$report_config['time_period']['end_date'] = strtotime( '31-Dec-2019' ); //Need to do the entire year so 'line_10' from above is used.
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0  =>
						[
								'date_month'            => 'January',
								'total_payments'        => '26546.13',
								'exempt_payments'       => '268.71',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '26277.42',
								'before_adjustment_tax' => '157.66',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '157.66',
						],
				1  =>
						[
								'date_month'            => 'February',
								'total_payments'        => '26544.57',
								'exempt_payments'       => '268.19',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '26276.38',
								'before_adjustment_tax' => '157.66',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '157.66',
						],
				2  =>
						[
								'date_month'            => 'March',
								'total_payments'        => '26543.01',
								'exempt_payments'       => '267.67',
								'excess_payments'       => '0.00',
								'taxable_wages'         => '26275.34',
								'before_adjustment_tax' => '157.65',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '157.65',
						],
				3  =>
						[
								'date_month'            => 'April',
								'total_payments'        => '26541.45',
								'exempt_payments'       => '267.15',
								'excess_payments'       => '14103.44',
								'taxable_wages'         => '12170.86',
								'before_adjustment_tax' => '73.03',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '73.03',
						],
				4  =>
						[
								'date_month'            => 'May',
								'total_payments'        => '39810.81',
								'exempt_payments'       => '400.53',
								'excess_payments'       => '39410.28',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				5  =>
						[
								'date_month'            => 'June',
								'total_payments'        => '26540.41',
								'exempt_payments'       => '267.28',
								'excess_payments'       => '26273.13',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				6  =>
						[
								'date_month'            => 'July',
								'total_payments'        => '26539.63',
								'exempt_payments'       => '267.15',
								'excess_payments'       => '26272.48',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				7  =>
						[
								'date_month'            => 'August',
								'total_payments'        => '26538.85',
								'exempt_payments'       => '267.02',
								'excess_payments'       => '26271.83',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				8  =>
						[
								'date_month'            => 'September',
								'total_payments'        => '26538.33',
								'exempt_payments'       => '267.02',
								'excess_payments'       => '26271.31',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				9  =>
						[
								'date_month'            => 'October',
								'total_payments'        => '26538.59',
								'exempt_payments'       => '267.41',
								'excess_payments'       => '26271.18',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				10 =>
						[
								'date_month'            => 'November',
								'total_payments'        => '39805.74',
								'exempt_payments'       => '400.53',
								'excess_payments'       => '39405.21',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				11 =>
						[
								'date_month'            => 'December',
								'total_payments'        => '26536.51',
								'exempt_payments'       => '267.02',
								'excess_payments'       => '26269.49',
								'taxable_wages'         => '0.00',
								'before_adjustment_tax' => '0.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '0.00',
						],
				12 =>
						[
								'date_month'            => 'Grand Total[12]:',
								'total_payments'        => '345024.03',
								'exempt_payments'       => '3475.68',
								'excess_payments'       => '250548.35',
								'taxable_wages'         => '91000.00',
								'before_adjustment_tax' => '546.00',
								'adjustment_tax'        => '0.00',
								'after_adjustment_tax'  => '546.00',
								'_total'                => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		//
		//***NOTE: When unit testing is enabled Form940ReportTest forces credit reduction rates for some states, so if testing through the UI you must force unit test mode enabled to get the same results.
		//         Also don't forget to setup the Exempt Payments in the UI.
		//
		$this->assertEquals( 345024.03, $form_objs->objs[0]->l3 );
		$this->assertEquals( 3475.68, $form_objs->objs[0]->l4 );
		$this->assertEquals( 250548.35, $form_objs->objs[0]->l5 );
		$this->assertEquals( 254024.03, $form_objs->objs[0]->l6 );
		$this->assertEquals( 91000.00, $form_objs->objs[0]->l7 );
		$this->assertEquals( 546.00, $form_objs->objs[0]->l8 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l10 );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l11 );
		$this->assertEquals( 546.00, $form_objs->objs[0]->l12 );
		$this->assertEquals( 546.00, $form_objs->objs[0]->l13 );
		$this->assertEquals( null, $form_objs->objs[0]->l14 );
		$this->assertEquals( 472.97, $form_objs->objs[0]->l16a );
		$this->assertEquals( 73.03, $form_objs->objs[0]->l16b );
		$this->assertEquals( null, $form_objs->objs[0]->l16c );
		$this->assertEquals( 0.00, $form_objs->objs[0]->l16d );

		return true;
	}
}

?>
