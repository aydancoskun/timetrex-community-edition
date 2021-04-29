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

class TaxSummaryReportTest extends PHPUnit\Framework\TestCase {
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
		$max_pay_periods = 14;

		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getById( $this->pay_period_schedule_id );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pps_obj = $ppslf->getCurrent();

			$end_date = null;
			for ( $i = 0; $i < $max_pay_periods; $i++ ) {
				if ( $i == 0 ) {
					$end_date = TTDate::getBeginYearEpoch( strtotime( '01-Jan-2019' ) );
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
		for ( $i = 0; $i <= 12; $i++ ) { //Calculate pay stubs for each pay period.
			$cps = new CalculatePayStub();
			$cps->setUser( $user_id );
			$cps->setPayPeriod( $this->pay_period_objs[$i]->getId() );
			$cps->calculate();
		}

		return true;
	}

	function getCompanyDeductionIDByName( $name ) {
		$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
		$cdlf->getByCompanyIdAndName( $this->company_id, $name );
		if ( $cdlf->getRecordCount() > 0 ) {
			$cd_obj = $cdlf->getCurrent();

			return $cd_obj->getId();
		} else {
			Debug::text( ' ERROR: Unable to find Company Deduction record! Name: ' . $name, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * @group TaxSummaryReport_testUSFederalUIQuarterlyReport
	 */
	function testUSFederalUIQuarterlyReport() {
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
		$report_obj = new TaxSummaryReport();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$report_obj->setFormConfig( $form_config );

		//$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );
		$report_config['columns'] = [ 'full_name', 'transaction-date_quarter_year', 'transaction-pay_period', 'subject_wages', 'taxable_wages', 'tax_withheld' ];
		$report_config['group'] = [ 'full_name', 'transaction-date_quarter_year', 'transaction-pay_period' ];
		$report_config['sub_total'] = [ 'full_name', 'transaction-date_quarter_year' ];
		$report_config['sort'] = [ [ 'full_name' => 'asc' ], [ 'transaction-date_quarter_year' => 'asc' ], [ 'transaction-pay_period' => 'asc' ] ];

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_1st_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_config['company_deduction_id'] = $this->getCompanyDeductionIDByName( 'US - Federal Unemployment Insurance' );
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '06-Jan-19 -> 19-Jan-19',
								'subject_wages'                 => '1010.68',
								'taxable_wages'                 => '1010.68',
								'tax_withheld'                  => '6.06',
						],
				1  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '20-Jan-19 -> 02-Feb-19',
								'subject_wages'                 => '1010.66',
								'taxable_wages'                 => '1010.66',
								'tax_withheld'                  => '6.06',
						],
				2  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '03-Feb-19 -> 16-Feb-19',
								'subject_wages'                 => '1010.64',
								'taxable_wages'                 => '1010.64',
								'tax_withheld'                  => '6.06',
						],
				3  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '17-Feb-19 -> 02-Mar-19',
								'subject_wages'                 => '1010.62',
								'taxable_wages'                 => '1010.62',
								'tax_withheld'                  => '6.06',
						],
				4  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '03-Mar-19 -> 16-Mar-19',
								'subject_wages'                 => '1010.60',
								'taxable_wages'                 => '1010.60',
								'tax_withheld'                  => '6.06',
						],
				5  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '1-2019',
								'subject_wages'                 => '5053.20',
								'taxable_wages'                 => '5053.20',
								'tax_withheld'                  => '30.30',
								'_subtotal'                     => true,
						],
				6  =>
						[
								'full_name'     => 'Administrator, Mr.',
								'subject_wages' => '5053.20',
								'taxable_wages' => '5053.20',
								'tax_withheld'  => '30.30',
								'_subtotal'     => true,
						],
				7  =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '06-Jan-19 -> 19-Jan-19',
								'subject_wages'                 => '1010.68',
								'taxable_wages'                 => '1010.68',
								'tax_withheld'                  => '6.06',
						],
				8  =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '20-Jan-19 -> 02-Feb-19',
								'subject_wages'                 => '1010.66',
								'taxable_wages'                 => '1010.66',
								'tax_withheld'                  => '6.06',
						],
				9  =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '03-Feb-19 -> 16-Feb-19',
								'subject_wages'                 => '1010.64',
								'taxable_wages'                 => '1010.64',
								'tax_withheld'                  => '6.06',
						],
				10 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '17-Feb-19 -> 02-Mar-19',
								'subject_wages'                 => '1010.62',
								'taxable_wages'                 => '1010.62',
								'tax_withheld'                  => '6.06',
						],
				11 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '03-Mar-19 -> 16-Mar-19',
								'subject_wages'                 => '1010.60',
								'taxable_wages'                 => '1010.60',
								'tax_withheld'                  => '6.06',
						],
				12 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '1-2019',
								'subject_wages'                 => '5053.20',
								'taxable_wages'                 => '5053.20',
								'tax_withheld'                  => '30.30',
								'_subtotal'                     => true,
						],
				13 =>
						[
								'full_name'     => 'Doe, Jane',
								'subject_wages' => '5053.20',
								'taxable_wages' => '5053.20',
								'tax_withheld'  => '30.30',
								'_subtotal'     => true,
						],
				14 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '06-Jan-19 -> 19-Jan-19',
								'subject_wages'                 => '1010.68',
								'taxable_wages'                 => '1010.68',
								'tax_withheld'                  => '6.06',
						],
				15 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '20-Jan-19 -> 02-Feb-19',
								'subject_wages'                 => '1010.66',
								'taxable_wages'                 => '1010.66',
								'tax_withheld'                  => '6.06',
						],
				16 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '03-Feb-19 -> 16-Feb-19',
								'subject_wages'                 => '1010.64',
								'taxable_wages'                 => '1010.64',
								'tax_withheld'                  => '6.06',
						],
				17 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '17-Feb-19 -> 02-Mar-19',
								'subject_wages'                 => '1010.62',
								'taxable_wages'                 => '1010.62',
								'tax_withheld'                  => '6.06',
						],
				18 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '03-Mar-19 -> 16-Mar-19',
								'subject_wages'                 => '1010.60',
								'taxable_wages'                 => '1010.60',
								'tax_withheld'                  => '6.06',
						],
				19 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '1-2019',
								'subject_wages'                 => '5053.20',
								'taxable_wages'                 => '5053.20',
								'tax_withheld'                  => '30.30',
								'_subtotal'                     => true,
						],
				20 =>
						[
								'full_name'     => 'Doe, John',
								'subject_wages' => '5053.20',
								'taxable_wages' => '5053.20',
								'tax_withheld'  => '30.30',
								'_subtotal'     => true,
						],
				21 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '06-Jan-19 -> 19-Jan-19',
								'subject_wages'                 => '1010.68',
								'taxable_wages'                 => '1010.68',
								'tax_withheld'                  => '6.06',
						],
				22 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '20-Jan-19 -> 02-Feb-19',
								'subject_wages'                 => '1010.66',
								'taxable_wages'                 => '1010.66',
								'tax_withheld'                  => '6.06',
						],
				23 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '03-Feb-19 -> 16-Feb-19',
								'subject_wages'                 => '1010.64',
								'taxable_wages'                 => '1010.64',
								'tax_withheld'                  => '6.06',
						],
				24 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '17-Feb-19 -> 02-Mar-19',
								'subject_wages'                 => '1010.62',
								'taxable_wages'                 => '1010.62',
								'tax_withheld'                  => '6.06',
						],
				25 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '03-Mar-19 -> 16-Mar-19',
								'subject_wages'                 => '1010.60',
								'taxable_wages'                 => '1010.60',
								'tax_withheld'                  => '6.06',
						],
				26 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '1-2019',
								'subject_wages'                 => '5053.20',
								'taxable_wages'                 => '5053.20',
								'tax_withheld'                  => '30.30',
								'_subtotal'                     => true,
						],
				27 =>
						[
								'full_name'     => 'Erschoff, Tamera',
								'subject_wages' => '5053.20',
								'taxable_wages' => '5053.20',
								'tax_withheld'  => '30.30',
								'_subtotal'     => true,
						],
				28 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '06-Jan-19 -> 19-Jan-19',
								'subject_wages'                 => '1010.68',
								'taxable_wages'                 => '1010.68',
								'tax_withheld'                  => '6.06',
						],
				29 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '20-Jan-19 -> 02-Feb-19',
								'subject_wages'                 => '1010.66',
								'taxable_wages'                 => '1010.66',
								'tax_withheld'                  => '6.06',
						],
				30 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '03-Feb-19 -> 16-Feb-19',
								'subject_wages'                 => '1010.64',
								'taxable_wages'                 => '1010.64',
								'tax_withheld'                  => '6.06',
						],
				31 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '17-Feb-19 -> 02-Mar-19',
								'subject_wages'                 => '1010.62',
								'taxable_wages'                 => '1010.62',
								'tax_withheld'                  => '6.06',
						],
				32 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '03-Mar-19 -> 16-Mar-19',
								'subject_wages'                 => '1010.60',
								'taxable_wages'                 => '1010.60',
								'tax_withheld'                  => '6.06',
						],
				33 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '1-2019',
								'subject_wages'                 => '5053.20',
								'taxable_wages'                 => '5053.20',
								'tax_withheld'                  => '30.30',
								'_subtotal'                     => true,
						],
				34 =>
						[
								'full_name'     => 'Simmons, Theodora',
								'subject_wages' => '5053.20',
								'taxable_wages' => '5053.20',
								'tax_withheld'  => '30.30',
								'_subtotal'     => true,
						],
				35 =>
						[
								'transaction-pay_period' => 'Grand Total[25]:',
								'subject_wages'          => '25266.00',
								'taxable_wages'          => '25266.00',
								'tax_withheld'           => '151.50',
								'_total'                 => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );


		//Generate Report for 2nd Quarter
		$report_obj = new TaxSummaryReport();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$report_obj->setFormConfig( $form_config );

		//$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );
		$report_config['columns'] = [ 'full_name', 'transaction-date_quarter_year', 'transaction-pay_period', 'subject_wages', 'taxable_wages', 'tax_withheld' ];
		$report_config['group'] = [ 'full_name', 'transaction-date_quarter_year', 'transaction-pay_period' ];
		$report_config['sub_total'] = [ 'full_name', 'transaction-date_quarter_year' ];
		$report_config['sort'] = [ [ 'full_name' => 'asc' ], [ 'transaction-date_quarter_year' => 'asc' ], [ 'transaction-pay_period' => 'asc' ] ];

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_2nd_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_config['company_deduction_id'] = $this->getCompanyDeductionIDByName( 'US - Federal Unemployment Insurance' );
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export( $report_output );

		$should_match_arr = [
				0  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '17-Mar-19 -> 30-Mar-19',
								'subject_wages'                 => '1010.58',
								'taxable_wages'                 => '1010.58',
								'tax_withheld'                  => '6.06',
						],
				1  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '31-Mar-19 -> 13-Apr-19',
								'subject_wages'                 => '1010.56',
								'taxable_wages'                 => '936.22',
								'tax_withheld'                  => '5.62',
						],
				2  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '14-Apr-19 -> 27-Apr-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				3  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '28-Apr-19 -> 11-May-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				4  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '12-May-19 -> 25-May-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				5  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '26-May-19 -> 08-Jun-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				6  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '09-Jun-19 -> 22-Jun-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				7  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '2-2019',
								'subject_wages'                 => '7073.84',
								'taxable_wages'                 => '1946.80',
								'tax_withheld'                  => '11.68',
								'_subtotal'                     => true,
						],
				8  =>
						[
								'full_name'     => 'Administrator, Mr.',
								'subject_wages' => '7073.84',
								'taxable_wages' => '1946.80',
								'tax_withheld'  => '11.68',
								'_subtotal'     => true,
						],
				9  =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '17-Mar-19 -> 30-Mar-19',
								'subject_wages'                 => '1010.58',
								'taxable_wages'                 => '1010.58',
								'tax_withheld'                  => '6.06',
						],
				10 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '31-Mar-19 -> 13-Apr-19',
								'subject_wages'                 => '1010.56',
								'taxable_wages'                 => '936.22',
								'tax_withheld'                  => '5.62',
						],
				11 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '14-Apr-19 -> 27-Apr-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				12 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '28-Apr-19 -> 11-May-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				13 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '12-May-19 -> 25-May-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				14 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '26-May-19 -> 08-Jun-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				15 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '09-Jun-19 -> 22-Jun-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				16 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '2-2019',
								'subject_wages'                 => '7073.84',
								'taxable_wages'                 => '1946.80',
								'tax_withheld'                  => '11.68',
								'_subtotal'                     => true,
						],
				17 =>
						[
								'full_name'     => 'Doe, Jane',
								'subject_wages' => '7073.84',
								'taxable_wages' => '1946.80',
								'tax_withheld'  => '11.68',
								'_subtotal'     => true,
						],
				18 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '17-Mar-19 -> 30-Mar-19',
								'subject_wages'                 => '1010.58',
								'taxable_wages'                 => '1010.58',
								'tax_withheld'                  => '6.06',
						],
				19 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '31-Mar-19 -> 13-Apr-19',
								'subject_wages'                 => '1010.56',
								'taxable_wages'                 => '936.22',
								'tax_withheld'                  => '5.62',
						],
				20 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '14-Apr-19 -> 27-Apr-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				21 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '28-Apr-19 -> 11-May-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				22 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '12-May-19 -> 25-May-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				23 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '26-May-19 -> 08-Jun-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				24 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '09-Jun-19 -> 22-Jun-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				25 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '2-2019',
								'subject_wages'                 => '7073.84',
								'taxable_wages'                 => '1946.80',
								'tax_withheld'                  => '11.68',
								'_subtotal'                     => true,
						],
				26 =>
						[
								'full_name'     => 'Doe, John',
								'subject_wages' => '7073.84',
								'taxable_wages' => '1946.80',
								'tax_withheld'  => '11.68',
								'_subtotal'     => true,
						],
				27 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '17-Mar-19 -> 30-Mar-19',
								'subject_wages'                 => '1010.58',
								'taxable_wages'                 => '1010.58',
								'tax_withheld'                  => '6.06',
						],
				28 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '31-Mar-19 -> 13-Apr-19',
								'subject_wages'                 => '1010.56',
								'taxable_wages'                 => '936.22',
								'tax_withheld'                  => '5.62',
						],
				29 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '14-Apr-19 -> 27-Apr-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				30 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '28-Apr-19 -> 11-May-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				31 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '12-May-19 -> 25-May-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				32 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '26-May-19 -> 08-Jun-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				33 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '09-Jun-19 -> 22-Jun-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				34 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '2-2019',
								'subject_wages'                 => '7073.84',
								'taxable_wages'                 => '1946.80',
								'tax_withheld'                  => '11.68',
								'_subtotal'                     => true,
						],
				35 =>
						[
								'full_name'     => 'Erschoff, Tamera',
								'subject_wages' => '7073.84',
								'taxable_wages' => '1946.80',
								'tax_withheld'  => '11.68',
								'_subtotal'     => true,
						],
				36 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '17-Mar-19 -> 30-Mar-19',
								'subject_wages'                 => '1010.58',
								'taxable_wages'                 => '1010.58',
								'tax_withheld'                  => '6.06',
						],
				37 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '31-Mar-19 -> 13-Apr-19',
								'subject_wages'                 => '1010.56',
								'taxable_wages'                 => '936.22',
								'tax_withheld'                  => '5.62',
						],
				38 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '14-Apr-19 -> 27-Apr-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				39 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '28-Apr-19 -> 11-May-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				40 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '12-May-19 -> 25-May-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				41 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '26-May-19 -> 08-Jun-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				42 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '09-Jun-19 -> 22-Jun-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				43 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '2-2019',
								'subject_wages'                 => '7073.84',
								'taxable_wages'                 => '1946.80',
								'tax_withheld'                  => '11.68',
								'_subtotal'                     => true,
						],
				44 =>
						[
								'full_name'     => 'Simmons, Theodora',
								'subject_wages' => '7073.84',
								'taxable_wages' => '1946.80',
								'tax_withheld'  => '11.68',
								'_subtotal'     => true,
						],
				45 =>
						[
								'transaction-pay_period' => 'Grand Total[35]:',
								'subject_wages'          => '35369.20',
								'taxable_wages'          => '9734.00',
								'tax_withheld'           => '58.40',
								'_total'                 => true,
						],

		];
		$this->assertEquals( $report_output, $should_match_arr );


		//Generate Report for entire year
		$report_obj = new TaxSummaryReport();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$report_obj->setFormConfig( $form_config );

		//$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );
		$report_config['columns'] = [ 'full_name', 'transaction-date_quarter_year', 'transaction-pay_period', 'subject_wages', 'taxable_wages', 'tax_withheld' ];
		$report_config['group'] = [ 'full_name', 'transaction-date_quarter_year', 'transaction-pay_period' ];
		$report_config['sub_total'] = [ 'full_name', 'transaction-date_quarter_year' ];
		$report_config['sort'] = [ [ 'full_name' => 'asc' ], [ 'transaction-date_quarter_year' => 'asc' ], [ 'transaction-pay_period' => 'asc' ] ];

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_config['time_period']['start_date'] = strtotime( '01-Jan-2019' );
		$report_config['time_period']['end_date'] = strtotime( '30-Jun-2019' );
		$report_config['company_deduction_id'] = $this->getCompanyDeductionIDByName( 'US - Federal Unemployment Insurance' );
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_export($report_output);

		$should_match_arr = [
				0  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '06-Jan-19 -> 19-Jan-19',
								'subject_wages'                 => '1010.68',
								'taxable_wages'                 => '1010.68',
								'tax_withheld'                  => '6.06',
						],
				1  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '20-Jan-19 -> 02-Feb-19',
								'subject_wages'                 => '1010.66',
								'taxable_wages'                 => '1010.66',
								'tax_withheld'                  => '6.06',
						],
				2  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '03-Feb-19 -> 16-Feb-19',
								'subject_wages'                 => '1010.64',
								'taxable_wages'                 => '1010.64',
								'tax_withheld'                  => '6.06',
						],
				3  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '17-Feb-19 -> 02-Mar-19',
								'subject_wages'                 => '1010.62',
								'taxable_wages'                 => '1010.62',
								'tax_withheld'                  => '6.06',
						],
				4  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '03-Mar-19 -> 16-Mar-19',
								'subject_wages'                 => '1010.60',
								'taxable_wages'                 => '1010.60',
								'tax_withheld'                  => '6.06',
						],
				5  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '1-2019',
								'subject_wages'                 => '5053.20',
								'taxable_wages'                 => '5053.20',
								'tax_withheld'                  => '30.30',
								'_subtotal'                     => true,
						],
				6  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '17-Mar-19 -> 30-Mar-19',
								'subject_wages'                 => '1010.58',
								'taxable_wages'                 => '1010.58',
								'tax_withheld'                  => '6.06',
						],
				7  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '31-Mar-19 -> 13-Apr-19',
								'subject_wages'                 => '1010.56',
								'taxable_wages'                 => '936.22',
								'tax_withheld'                  => '5.62',
						],
				8  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '14-Apr-19 -> 27-Apr-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				9  =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '28-Apr-19 -> 11-May-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				10 =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '12-May-19 -> 25-May-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				11 =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '26-May-19 -> 08-Jun-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				12 =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '09-Jun-19 -> 22-Jun-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				13 =>
						[
								'full_name'                     => 'Administrator, Mr.',
								'transaction-date_quarter_year' => '2-2019',
								'subject_wages'                 => '7073.84',
								'taxable_wages'                 => '1946.80',
								'tax_withheld'                  => '11.68',
								'_subtotal'                     => true,
						],
				14 =>
						[
								'full_name'     => 'Administrator, Mr.',
								'subject_wages' => '12127.04',
								'taxable_wages' => '7000.00',
								'tax_withheld'  => '41.98',
								'_subtotal'     => true,
						],
				15 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '06-Jan-19 -> 19-Jan-19',
								'subject_wages'                 => '1010.68',
								'taxable_wages'                 => '1010.68',
								'tax_withheld'                  => '6.06',
						],
				16 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '20-Jan-19 -> 02-Feb-19',
								'subject_wages'                 => '1010.66',
								'taxable_wages'                 => '1010.66',
								'tax_withheld'                  => '6.06',
						],
				17 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '03-Feb-19 -> 16-Feb-19',
								'subject_wages'                 => '1010.64',
								'taxable_wages'                 => '1010.64',
								'tax_withheld'                  => '6.06',
						],
				18 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '17-Feb-19 -> 02-Mar-19',
								'subject_wages'                 => '1010.62',
								'taxable_wages'                 => '1010.62',
								'tax_withheld'                  => '6.06',
						],
				19 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '03-Mar-19 -> 16-Mar-19',
								'subject_wages'                 => '1010.60',
								'taxable_wages'                 => '1010.60',
								'tax_withheld'                  => '6.06',
						],
				20 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '1-2019',
								'subject_wages'                 => '5053.20',
								'taxable_wages'                 => '5053.20',
								'tax_withheld'                  => '30.30',
								'_subtotal'                     => true,
						],
				21 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '17-Mar-19 -> 30-Mar-19',
								'subject_wages'                 => '1010.58',
								'taxable_wages'                 => '1010.58',
								'tax_withheld'                  => '6.06',
						],
				22 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '31-Mar-19 -> 13-Apr-19',
								'subject_wages'                 => '1010.56',
								'taxable_wages'                 => '936.22',
								'tax_withheld'                  => '5.62',
						],
				23 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '14-Apr-19 -> 27-Apr-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				24 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '28-Apr-19 -> 11-May-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				25 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '12-May-19 -> 25-May-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				26 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '26-May-19 -> 08-Jun-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				27 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '09-Jun-19 -> 22-Jun-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				28 =>
						[
								'full_name'                     => 'Doe, Jane',
								'transaction-date_quarter_year' => '2-2019',
								'subject_wages'                 => '7073.84',
								'taxable_wages'                 => '1946.80',
								'tax_withheld'                  => '11.68',
								'_subtotal'                     => true,
						],
				29 =>
						[
								'full_name'     => 'Doe, Jane',
								'subject_wages' => '12127.04',
								'taxable_wages' => '7000.00',
								'tax_withheld'  => '41.98',
								'_subtotal'     => true,
						],
				30 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '06-Jan-19 -> 19-Jan-19',
								'subject_wages'                 => '1010.68',
								'taxable_wages'                 => '1010.68',
								'tax_withheld'                  => '6.06',
						],
				31 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '20-Jan-19 -> 02-Feb-19',
								'subject_wages'                 => '1010.66',
								'taxable_wages'                 => '1010.66',
								'tax_withheld'                  => '6.06',
						],
				32 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '03-Feb-19 -> 16-Feb-19',
								'subject_wages'                 => '1010.64',
								'taxable_wages'                 => '1010.64',
								'tax_withheld'                  => '6.06',
						],
				33 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '17-Feb-19 -> 02-Mar-19',
								'subject_wages'                 => '1010.62',
								'taxable_wages'                 => '1010.62',
								'tax_withheld'                  => '6.06',
						],
				34 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '03-Mar-19 -> 16-Mar-19',
								'subject_wages'                 => '1010.60',
								'taxable_wages'                 => '1010.60',
								'tax_withheld'                  => '6.06',
						],
				35 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '1-2019',
								'subject_wages'                 => '5053.20',
								'taxable_wages'                 => '5053.20',
								'tax_withheld'                  => '30.30',
								'_subtotal'                     => true,
						],
				36 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '17-Mar-19 -> 30-Mar-19',
								'subject_wages'                 => '1010.58',
								'taxable_wages'                 => '1010.58',
								'tax_withheld'                  => '6.06',
						],
				37 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '31-Mar-19 -> 13-Apr-19',
								'subject_wages'                 => '1010.56',
								'taxable_wages'                 => '936.22',
								'tax_withheld'                  => '5.62',
						],
				38 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '14-Apr-19 -> 27-Apr-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				39 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '28-Apr-19 -> 11-May-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				40 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '12-May-19 -> 25-May-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				41 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '26-May-19 -> 08-Jun-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				42 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '09-Jun-19 -> 22-Jun-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				43 =>
						[
								'full_name'                     => 'Doe, John',
								'transaction-date_quarter_year' => '2-2019',
								'subject_wages'                 => '7073.84',
								'taxable_wages'                 => '1946.80',
								'tax_withheld'                  => '11.68',
								'_subtotal'                     => true,
						],
				44 =>
						[
								'full_name'     => 'Doe, John',
								'subject_wages' => '12127.04',
								'taxable_wages' => '7000.00',
								'tax_withheld'  => '41.98',
								'_subtotal'     => true,
						],
				45 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '06-Jan-19 -> 19-Jan-19',
								'subject_wages'                 => '1010.68',
								'taxable_wages'                 => '1010.68',
								'tax_withheld'                  => '6.06',
						],
				46 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '20-Jan-19 -> 02-Feb-19',
								'subject_wages'                 => '1010.66',
								'taxable_wages'                 => '1010.66',
								'tax_withheld'                  => '6.06',
						],
				47 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '03-Feb-19 -> 16-Feb-19',
								'subject_wages'                 => '1010.64',
								'taxable_wages'                 => '1010.64',
								'tax_withheld'                  => '6.06',
						],
				48 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '17-Feb-19 -> 02-Mar-19',
								'subject_wages'                 => '1010.62',
								'taxable_wages'                 => '1010.62',
								'tax_withheld'                  => '6.06',
						],
				49 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '03-Mar-19 -> 16-Mar-19',
								'subject_wages'                 => '1010.60',
								'taxable_wages'                 => '1010.60',
								'tax_withheld'                  => '6.06',
						],
				50 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '1-2019',
								'subject_wages'                 => '5053.20',
								'taxable_wages'                 => '5053.20',
								'tax_withheld'                  => '30.30',
								'_subtotal'                     => true,
						],
				51 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '17-Mar-19 -> 30-Mar-19',
								'subject_wages'                 => '1010.58',
								'taxable_wages'                 => '1010.58',
								'tax_withheld'                  => '6.06',
						],
				52 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '31-Mar-19 -> 13-Apr-19',
								'subject_wages'                 => '1010.56',
								'taxable_wages'                 => '936.22',
								'tax_withheld'                  => '5.62',
						],
				53 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '14-Apr-19 -> 27-Apr-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				54 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '28-Apr-19 -> 11-May-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				55 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '12-May-19 -> 25-May-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				56 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '26-May-19 -> 08-Jun-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				57 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '09-Jun-19 -> 22-Jun-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				58 =>
						[
								'full_name'                     => 'Erschoff, Tamera',
								'transaction-date_quarter_year' => '2-2019',
								'subject_wages'                 => '7073.84',
								'taxable_wages'                 => '1946.80',
								'tax_withheld'                  => '11.68',
								'_subtotal'                     => true,
						],
				59 =>
						[
								'full_name'     => 'Erschoff, Tamera',
								'subject_wages' => '12127.04',
								'taxable_wages' => '7000.00',
								'tax_withheld'  => '41.98',
								'_subtotal'     => true,
						],
				60 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '06-Jan-19 -> 19-Jan-19',
								'subject_wages'                 => '1010.68',
								'taxable_wages'                 => '1010.68',
								'tax_withheld'                  => '6.06',
						],
				61 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '20-Jan-19 -> 02-Feb-19',
								'subject_wages'                 => '1010.66',
								'taxable_wages'                 => '1010.66',
								'tax_withheld'                  => '6.06',
						],
				62 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '03-Feb-19 -> 16-Feb-19',
								'subject_wages'                 => '1010.64',
								'taxable_wages'                 => '1010.64',
								'tax_withheld'                  => '6.06',
						],
				63 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '17-Feb-19 -> 02-Mar-19',
								'subject_wages'                 => '1010.62',
								'taxable_wages'                 => '1010.62',
								'tax_withheld'                  => '6.06',
						],
				64 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '1-2019',
								'transaction-pay_period'        => '03-Mar-19 -> 16-Mar-19',
								'subject_wages'                 => '1010.60',
								'taxable_wages'                 => '1010.60',
								'tax_withheld'                  => '6.06',
						],
				65 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '1-2019',
								'subject_wages'                 => '5053.20',
								'taxable_wages'                 => '5053.20',
								'tax_withheld'                  => '30.30',
								'_subtotal'                     => true,
						],
				66 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '17-Mar-19 -> 30-Mar-19',
								'subject_wages'                 => '1010.58',
								'taxable_wages'                 => '1010.58',
								'tax_withheld'                  => '6.06',
						],
				67 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '31-Mar-19 -> 13-Apr-19',
								'subject_wages'                 => '1010.56',
								'taxable_wages'                 => '936.22',
								'tax_withheld'                  => '5.62',
						],
				68 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '14-Apr-19 -> 27-Apr-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				69 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '28-Apr-19 -> 11-May-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				70 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '12-May-19 -> 25-May-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				71 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '26-May-19 -> 08-Jun-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				72 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '2-2019',
								'transaction-pay_period'        => '09-Jun-19 -> 22-Jun-19',
								'subject_wages'                 => '1010.54',
								'taxable_wages'                 => '0.00',
								'tax_withheld'                  => '0.00',
						],
				73 =>
						[
								'full_name'                     => 'Simmons, Theodora',
								'transaction-date_quarter_year' => '2-2019',
								'subject_wages'                 => '7073.84',
								'taxable_wages'                 => '1946.80',
								'tax_withheld'                  => '11.68',
								'_subtotal'                     => true,
						],
				74 =>
						[
								'full_name'     => 'Simmons, Theodora',
								'subject_wages' => '12127.04',
								'taxable_wages' => '7000.00',
								'tax_withheld'  => '41.98',
								'_subtotal'     => true,
						],
				75 =>
						[
								'transaction-pay_period' => 'Grand Total[60]:',
								'subject_wages'          => '60635.20',
								'taxable_wages'          => '35000.00',
								'tax_withheld'           => '209.90',
								'_total'                 => true,
						],
		];
		$this->assertEquals( $report_output, $should_match_arr );

		return true;
	}
}

?>