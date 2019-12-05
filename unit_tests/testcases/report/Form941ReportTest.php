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

class Form941ReportTest extends PHPUnit_Framework_TestCase {
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

		$dd->createPayrollRemittanceAgency( $this->company_id, $this->user_id, $this->legal_entity_id ); //Must go before createCompanyDeduction()

		//Company Deductions
		$dd->createCompanyDeduction( $this->company_id, $this->user_id, $this->legal_entity_id );

		$dd->createUserWageGroups( $this->company_id );

		$remittance_source_account_ids[$this->legal_entity_id][] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id, 10  ); // Check
		$remittance_source_account_ids[$this->legal_entity_id][] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id, 20  ); // US - EFT
		$remittance_source_account_ids[$this->legal_entity_id][] = $dd->createRemittanceSourceAccount( $this->company_id, $this->legal_entity_id, $this->currency_id, 30  ); // CA - EFT

		//createUser() also handles remittance destination accounts.
		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 100, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $remittance_source_account_ids );

		//Get User Object.
		$ulf = new UserListFactory();
		$this->user_obj = $ulf->getById( $this->user_id )->getCurrent();

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$dd->createTaxForms( $this->company_id, $this->user_id );

		$this->assertGreaterThan( 0, $this->company_id );
		$this->assertGreaterThan( 0, $this->user_id );

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

			$ppsf->setUser( array($this->user_id) );
			$ppsf->Save();

			$this->pay_period_schedule_id = $insert_id;

			return $insert_id;
		}

		Debug::Text('Failed Creating Pay Period Schedule!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;

	}

	function createPayPeriods() {
		$max_pay_periods = 28; //Just over one year.

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

	function createPayStubAmendment( $pay_stub_entry_name_id, $amount, $effective_date ) {
		$psaf = new PayStubAmendmentFactory();
		$psaf->setUser( $this->user_id );
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

	function createPayStub( $max = 12 ) {
		for( $i = 0; $i <= $max; $i++ ) { //Calculate pay stubs for each pay period.
			$cps = new CalculatePayStub();
			$cps->setUser( $this->user_id );
			$cps->setPayPeriod( $this->pay_period_objs[$i]->getId() );
			$cps->calculate();
		}

		return TRUE;
	}

	/**
	 * @group Form941Report_testMonthlyDepositA
	 */
	function testMonthlyDepositA() {
		//1st Quarter - Stay below 200,000 medicare limit and 132,900 social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.33, TTDate::getMiddleDayEpoch( $this->pay_period_objs[1]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.33, TTDate::getMiddleDayEpoch( $this->pay_period_objs[1]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.32, TTDate::getMiddleDayEpoch( $this->pay_period_objs[2]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.32, TTDate::getMiddleDayEpoch( $this->pay_period_objs[2]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.31, TTDate::getMiddleDayEpoch( $this->pay_period_objs[3]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.31, TTDate::getMiddleDayEpoch( $this->pay_period_objs[3]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.30, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.30, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ) );

		//2nd Quarter - Cross medicare and social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[5]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[5]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.28, TTDate::getMiddleDayEpoch( $this->pay_period_objs[6]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.28, TTDate::getMiddleDayEpoch( $this->pay_period_objs[6]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[7]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[7]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ) );

		//Extra pay period outside the 1st and 2nd quarter.
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ) );

		$this->createPayStub();

		//Generate Report for 1st Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

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
		$this->assertEquals( $report_output[0]['wages'], 20010.68 );
		$this->assertEquals( $report_output[0]['income_tax'], 6003.24 );
		$this->assertEquals( $report_output[0]['social_security_tax_total'], 2481.32 );
		$this->assertEquals( $report_output[0]['medicare_tax_total'], 580.30 );
		$this->assertEquals( $report_output[0]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[0]['total_tax'], 9064.86 );

		$this->assertEquals( $report_output[1]['date_month'], 'February' );
		$this->assertEquals( $report_output[1]['wages'], 40021.30 );
		$this->assertEquals( $report_output[1]['income_tax'], 12006.46 );
		$this->assertEquals( $report_output[1]['social_security_tax_total'], 4962.64 );
		$this->assertEquals( $report_output[1]['medicare_tax_total'], 1160.60 );
		$this->assertEquals( $report_output[1]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[1]['total_tax'], 18129.70 );

		$this->assertEquals( $report_output[2]['date_month'], 'March' );
		$this->assertEquals( $report_output[2]['wages'], 40021.22 );
		$this->assertEquals( $report_output[2]['income_tax'], 12006.43 );
		$this->assertEquals( $report_output[2]['social_security_tax_total'], 4962.64 );
		$this->assertEquals( $report_output[2]['medicare_tax_total'], 1160.60 );
		$this->assertEquals( $report_output[2]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[2]['total_tax'], 18129.67 );

		//Total
		$this->assertEquals( $report_output[3]['wages'], 100053.20 );
		$this->assertEquals( $report_output[3]['income_tax'], 30016.13 );
		$this->assertEquals( $report_output[3]['social_security_tax_total'], 12406.60 );
		$this->assertEquals( $report_output[3]['medicare_tax_total'], 2901.50 );
		$this->assertEquals( $report_output[3]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[3]['total_tax'], 45324.23 );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		$this->assertEquals( $form_objs->objs[0]->l1, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l2, 100053.20 );
		$this->assertEquals( $form_objs->objs[0]->l3, 30016.13 );
		$this->assertEquals( $form_objs->objs[0]->l5a, 100001.60 );
		$this->assertEquals( $form_objs->objs[0]->l5b, 51.60 );
		$this->assertEquals( $form_objs->objs[0]->l5c, 100053.20 );
		$this->assertEquals( $form_objs->objs[0]->l5d, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l7z, 15308.10 );
		$this->assertEquals( $form_objs->objs[0]->l5_actual_deducted, 15308.10 );
		$this->assertEquals( $form_objs->objs[0]->l15b, TRUE );
		$this->assertEquals( $form_objs->objs[0]->l16_month1, 9064.86 );
		$this->assertEquals( $form_objs->objs[0]->l16_month2, 18129.70 );
		$this->assertEquals( $form_objs->objs[0]->l16_month3, 18129.67 );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, 45324.23 );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );

		$this->assertEquals( $form_objs->objs[0]->l5a2, 12400.20 );
		$this->assertEquals( $form_objs->objs[0]->l5b2, 6.40 );
		$this->assertEquals( $form_objs->objs[0]->l5c2, 2901.54 );
		$this->assertEquals( $form_objs->objs[0]->l5d2, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l5e, 15308.14 );
		$this->assertEquals( $form_objs->objs[0]->l4, TRUE );
		$this->assertEquals( $form_objs->objs[0]->l6, 45324.27 );
		$this->assertEquals( $form_objs->objs[0]->l7, -0.04 );
		$this->assertEquals( $form_objs->objs[0]->l10, 45324.23);
		$this->assertEquals( $form_objs->objs[0]->l12, 45324.23);


		//Generate Report for 2nd Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

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
		$this->assertEquals( $report_output[0]['wages'], 40021.14 );
		$this->assertEquals( $report_output[0]['income_tax'], 12006.40 );
		$this->assertEquals( $report_output[0]['social_security_tax_total'], 4073.00 );
		$this->assertEquals( $report_output[0]['medicare_tax_total'], 1160.60 );
		$this->assertEquals( $report_output[0]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[0]['total_tax'], 17240.00 );

		$this->assertEquals( $report_output[1]['date_month'], 'May' );
		$this->assertEquals( $report_output[1]['wages'], 60031.62 );
		$this->assertEquals( $report_output[1]['income_tax'], 18009.57 );
		$this->assertEquals( $report_output[1]['social_security_tax_total'], 0.00 );
		$this->assertEquals( $report_output[1]['medicare_tax_total'], 1740.90 );
		$this->assertEquals( $report_output[1]['additional_medicare_tax'], 0.95 );
		$this->assertEquals( $report_output[1]['total_tax'], 19751.42 );

		$this->assertEquals( $report_output[2]['date_month'], 'June' );
		$this->assertEquals( $report_output[2]['wages'], 40021.08 );
		$this->assertEquals( $report_output[2]['income_tax'], 12006.38 );
		$this->assertEquals( $report_output[2]['social_security_tax_total'], 0.00 );
		$this->assertEquals( $report_output[2]['medicare_tax_total'], 1160.60 );
		$this->assertEquals( $report_output[2]['additional_medicare_tax'], 360.18 );
		$this->assertEquals( $report_output[2]['total_tax'], 13527.16 );

		//Total
		$this->assertEquals( $report_output[3]['wages'], 140073.84 );
		$this->assertEquals( $report_output[3]['income_tax'], 42022.35 );
		$this->assertEquals( $report_output[3]['social_security_tax_total'], 4073.00 );
		$this->assertEquals( $report_output[3]['medicare_tax_total'], 4062.10 );
		$this->assertEquals( $report_output[3]['additional_medicare_tax'], 361.13 );
		$this->assertEquals( $report_output[3]['total_tax'], 50518.58 );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		$this->assertEquals( $form_objs->objs[0]->l1, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l2, 140073.84 );
		$this->assertEquals( $form_objs->objs[0]->l3, 42022.35 );
		$this->assertEquals( $form_objs->objs[0]->l5a, 32826.23 );
		$this->assertEquals( $form_objs->objs[0]->l5b, 20.57 );
		$this->assertEquals( $form_objs->objs[0]->l5c, 140073.84 );
		$this->assertEquals( $form_objs->objs[0]->l5d, 40127.04 );
		$this->assertEquals( $form_objs->objs[0]->l7z, 8496.23 );
		$this->assertEquals( $form_objs->objs[0]->l5_actual_deducted, 8496.23 );
		$this->assertEquals( $form_objs->objs[0]->l15b, TRUE );
		$this->assertEquals( $form_objs->objs[0]->l16_month1, 17240.00 );
		$this->assertEquals( $form_objs->objs[0]->l16_month2, 19751.42 );
		$this->assertEquals( $form_objs->objs[0]->l16_month3, 13527.16 );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, 50518.58 );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );

		$this->assertEquals( $form_objs->objs[0]->l5a2, 4070.45 );
		$this->assertEquals( $form_objs->objs[0]->l5b2, 2.55 );
		$this->assertEquals( $form_objs->objs[0]->l5c2, 4062.14 );
		$this->assertEquals( $form_objs->objs[0]->l5d2, 361.14 );
		$this->assertEquals( $form_objs->objs[0]->l5e, 8496.28 );
		$this->assertEquals( $form_objs->objs[0]->l4, TRUE );
		$this->assertEquals( $form_objs->objs[0]->l6, 50518.63 );
		$this->assertEquals( $form_objs->objs[0]->l7, -0.05 );
		$this->assertEquals( $form_objs->objs[0]->l10, 50518.58);
		$this->assertEquals( $form_objs->objs[0]->l12, 50518.58);


		//Generate Report for entire year
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_config['time_period']['start_date'] = strtotime('01-Jan-2019');
		$report_config['time_period']['end_date'] = strtotime('30-Jun-2019');
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( $report_output[0]['date_month'], 'January' );
		$this->assertEquals( $report_output[0]['wages'], 20010.68 );
		$this->assertEquals( $report_output[0]['income_tax'], 6003.24 );
		$this->assertEquals( $report_output[0]['social_security_tax_total'], 2481.32 );
		$this->assertEquals( $report_output[0]['medicare_tax_total'], 580.30 );
		$this->assertEquals( $report_output[0]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[0]['total_tax'], 9064.86 );

		$this->assertEquals( $report_output[1]['date_month'], 'February' );
		$this->assertEquals( $report_output[1]['wages'], 40021.30 );
		$this->assertEquals( $report_output[1]['income_tax'], 12006.46 );
		$this->assertEquals( $report_output[1]['social_security_tax_total'], 4962.64 );
		$this->assertEquals( $report_output[1]['medicare_tax_total'], 1160.60 );
		$this->assertEquals( $report_output[1]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[1]['total_tax'], 18129.70 );

		$this->assertEquals( $report_output[2]['date_month'], 'March' );
		$this->assertEquals( $report_output[2]['wages'], 40021.22 );
		$this->assertEquals( $report_output[2]['income_tax'], 12006.43 );
		$this->assertEquals( $report_output[2]['social_security_tax_total'], 4962.64 );
		$this->assertEquals( $report_output[2]['medicare_tax_total'], 1160.60 );
		$this->assertEquals( $report_output[2]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[2]['total_tax'], 18129.67 );

		$this->assertEquals( $report_output[3]['date_month'], 'April' );
		$this->assertEquals( $report_output[3]['wages'], 40021.14 );
		$this->assertEquals( $report_output[3]['income_tax'], 12006.40 );
		$this->assertEquals( $report_output[3]['social_security_tax_total'], 4073.00 );
		$this->assertEquals( $report_output[3]['medicare_tax_total'], 1160.60 );
		$this->assertEquals( $report_output[3]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[3]['total_tax'], 17240.00 );

		$this->assertEquals( $report_output[4]['date_month'], 'May' );
		$this->assertEquals( $report_output[4]['wages'], 60031.62 );
		$this->assertEquals( $report_output[4]['income_tax'], 18009.57 );
		$this->assertEquals( $report_output[4]['social_security_tax_total'], 0.00 );
		$this->assertEquals( $report_output[4]['medicare_tax_total'], 1740.90 );
		$this->assertEquals( $report_output[4]['additional_medicare_tax'], 0.95 );
		$this->assertEquals( $report_output[4]['total_tax'], 19751.42 );

		$this->assertEquals( $report_output[5]['date_month'], 'June' );
		$this->assertEquals( $report_output[5]['wages'], 40021.08 );
		$this->assertEquals( $report_output[5]['income_tax'], 12006.38 );
		$this->assertEquals( $report_output[5]['social_security_tax_total'], 0.00 );
		$this->assertEquals( $report_output[5]['medicare_tax_total'], 1160.60 );
		$this->assertEquals( $report_output[5]['additional_medicare_tax'], 360.18 );
		$this->assertEquals( $report_output[5]['total_tax'], 13527.16 );

		//Total
		$this->assertEquals( $report_output[6]['wages'], 240127.04 );
		$this->assertEquals( $report_output[6]['income_tax'], 72038.48 );
		$this->assertEquals( $report_output[6]['social_security_tax_total'], 16479.60 );
		$this->assertEquals( $report_output[6]['medicare_tax_total'], 6963.60 );
		$this->assertEquals( $report_output[6]['additional_medicare_tax'], 361.13 );
		$this->assertEquals( $report_output[6]['total_tax'], 95842.81 );

		return TRUE;
	}

	/**
	 * @group Form941Report_testMonthlyDepositB
	 */
	function testMonthlyDepositB() {
		//1st Quarter - Stay below 200,000 medicare limit and 132,900 social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[1]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[2]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[3]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ) );

		//2nd Quarter - Stay below 200,000 medicare limit and 132,900 social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[5]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[6]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[7]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ) );

		//3rd Quarter - Cross medicare and social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[13]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[14]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[15]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[16]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[17]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[18]->getEndDate() ) );

		//4th Quarter
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 9221.40, TTDate::getMiddleDayEpoch( $this->pay_period_objs[19]->getEndDate() ) );

		$this->createPayStub( 19 );

		//Generate Report for 1st Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

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
		$this->assertEquals( $report_output[0]['wages'], 9221.40 );
		$this->assertEquals( $report_output[0]['income_tax'], 2222.24 );
		$this->assertEquals( $report_output[0]['social_security_tax_total'], 1143.46 );
		$this->assertEquals( $report_output[0]['medicare_tax_total'], 267.42 );
		$this->assertEquals( $report_output[0]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[0]['total_tax'], 3633.12 );

		$this->assertEquals( $report_output[1]['date_month'], 'February' );
		$this->assertEquals( $report_output[1]['wages'], 18442.80 );
		$this->assertEquals( $report_output[1]['income_tax'], 4444.48 );
		$this->assertEquals( $report_output[1]['social_security_tax_total'], 2286.92 );
		$this->assertEquals( $report_output[1]['medicare_tax_total'], 534.84 );
		$this->assertEquals( $report_output[1]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[1]['total_tax'], 7266.24 );

		$this->assertEquals( $report_output[2]['date_month'], 'March' );
		$this->assertEquals( $report_output[2]['wages'], 18442.80 );
		$this->assertEquals( $report_output[2]['income_tax'], 4444.48 );
		$this->assertEquals( $report_output[2]['social_security_tax_total'], 2286.92 );
		$this->assertEquals( $report_output[2]['medicare_tax_total'], 534.84 );
		$this->assertEquals( $report_output[2]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[2]['total_tax'], 7266.24 );

		//Total
		$this->assertEquals( $report_output[3]['wages'], 46107.00 );
		$this->assertEquals( $report_output[3]['income_tax'], 11111.20 );
		$this->assertEquals( $report_output[3]['social_security_tax_total'], 5717.30 );
		$this->assertEquals( $report_output[3]['medicare_tax_total'], 1337.10 );
		$this->assertEquals( $report_output[3]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[3]['total_tax'], 18165.60 );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		$this->assertEquals( $form_objs->objs[0]->l1, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l2, 46107.00 );
		$this->assertEquals( $form_objs->objs[0]->l3, 11111.20 );
		$this->assertEquals( $form_objs->objs[0]->l5a, 46107.00 );
		$this->assertEquals( $form_objs->objs[0]->l5b, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l5c, 46107.00 );
		$this->assertEquals( $form_objs->objs[0]->l5d, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l7z, 7054.40 );
		$this->assertEquals( $form_objs->objs[0]->l5_actual_deducted, 7054.40 );
		$this->assertEquals( $form_objs->objs[0]->l15b, TRUE );
		$this->assertEquals( $form_objs->objs[0]->l16_month1, 3633.12 );
		$this->assertEquals( $form_objs->objs[0]->l16_month2, 7266.24 );
		$this->assertEquals( $form_objs->objs[0]->l16_month3, 7266.24 );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, 18165.60 );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );

		$this->assertEquals( $form_objs->objs[0]->l5a2, 5717.27 );
		$this->assertEquals( $form_objs->objs[0]->l5b2, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l5c2, 1337.10 );
		$this->assertEquals( $form_objs->objs[0]->l5d2, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l5e, 7054.37 );
		$this->assertEquals( $form_objs->objs[0]->l4, TRUE );
		$this->assertEquals( $form_objs->objs[0]->l6, 18165.57 );
		$this->assertEquals( $form_objs->objs[0]->l7, 0.03 );
		$this->assertEquals( $form_objs->objs[0]->l10, 18165.60);
		$this->assertEquals( $form_objs->objs[0]->l12, 18165.60);


		//Generate Report for 2nd Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

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
		$this->assertEquals( $report_output[0]['wages'], 18442.80 );
		$this->assertEquals( $report_output[0]['income_tax'], 4444.48 );
		$this->assertEquals( $report_output[0]['social_security_tax_total'], 2286.92 );
		$this->assertEquals( $report_output[0]['medicare_tax_total'], 534.84 );
		$this->assertEquals( $report_output[0]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[0]['total_tax'], 7266.24 );

		$this->assertEquals( $report_output[1]['date_month'], 'May' );
		$this->assertEquals( $report_output[1]['wages'], 27664.20 );
		$this->assertEquals( $report_output[1]['income_tax'], 6666.72 );
		$this->assertEquals( $report_output[1]['social_security_tax_total'], 3430.38 );
		$this->assertEquals( $report_output[1]['medicare_tax_total'], 802.26 );
		$this->assertEquals( $report_output[1]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[1]['total_tax'], 10899.36 );

		$this->assertEquals( $report_output[2]['date_month'], 'June' );
		$this->assertEquals( $report_output[2]['wages'], 18442.80 );
		$this->assertEquals( $report_output[2]['income_tax'], 4444.48 );
		$this->assertEquals( $report_output[2]['social_security_tax_total'], 2286.92 );
		$this->assertEquals( $report_output[2]['medicare_tax_total'], 534.84 );
		$this->assertEquals( $report_output[2]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[2]['total_tax'], 7266.24 );

		//Total
		$this->assertEquals( $report_output[3]['wages'], 64549.80 );
		$this->assertEquals( $report_output[3]['income_tax'], 15555.68 );
		$this->assertEquals( $report_output[3]['social_security_tax_total'], 8004.22 );
		$this->assertEquals( $report_output[3]['medicare_tax_total'], 1871.94 );
		$this->assertEquals( $report_output[3]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[3]['total_tax'], 25431.84 );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		$this->assertEquals( $form_objs->objs[0]->l1, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l2, 64549.80 );
		$this->assertEquals( $form_objs->objs[0]->l3, 15555.68 );
		$this->assertEquals( $form_objs->objs[0]->l5a, 64549.80 );
		$this->assertEquals( $form_objs->objs[0]->l5b, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l5c, 64549.80 );
		$this->assertEquals( $form_objs->objs[0]->l5d, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l7z, 9876.16 );
		$this->assertEquals( $form_objs->objs[0]->l5_actual_deducted, 9876.16 );
		$this->assertEquals( $form_objs->objs[0]->l15b, TRUE );
		$this->assertEquals( $form_objs->objs[0]->l16_month1, 7266.24 );
		$this->assertEquals( $form_objs->objs[0]->l16_month2, 10899.36 );
		$this->assertEquals( $form_objs->objs[0]->l16_month3, 7266.24 );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, 25431.84 );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );

		$this->assertEquals( $form_objs->objs[0]->l5a2, 8004.18 );
		$this->assertEquals( $form_objs->objs[0]->l5b2, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l5c2, 1871.94 );
		$this->assertEquals( $form_objs->objs[0]->l5d2, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l5e, 9876.12 );
		$this->assertEquals( $form_objs->objs[0]->l4, TRUE );
		$this->assertEquals( $form_objs->objs[0]->l6, 25431.80 );
		$this->assertEquals( $form_objs->objs[0]->l7, 0.04 );
		$this->assertEquals( $form_objs->objs[0]->l10, 25431.84);
		$this->assertEquals( $form_objs->objs[0]->l12, 25431.84);


		//Generate Report for 3rd Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year_3rd_quarter', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( $report_output[0]['date_month'], 'July' );
		$this->assertEquals( $report_output[0]['wages'], 18442.80 );
		$this->assertEquals( $report_output[0]['income_tax'], 4444.48 );
		$this->assertEquals( $report_output[0]['social_security_tax_total'], 2286.92 );
		$this->assertEquals( $report_output[0]['medicare_tax_total'], 534.84 );
		$this->assertEquals( $report_output[0]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[0]['total_tax'], 7266.24 );

		$this->assertEquals( $report_output[1]['date_month'], 'August' );
		$this->assertEquals( $report_output[1]['wages'], 18442.80 );
		$this->assertEquals( $report_output[1]['income_tax'], 4444.48 );
		$this->assertEquals( $report_output[1]['social_security_tax_total'], 471.16 );
		$this->assertEquals( $report_output[1]['medicare_tax_total'], 534.84 );
		$this->assertEquals( $report_output[1]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[1]['total_tax'], 5450.48 );

		$this->assertEquals( $report_output[2]['date_month'], 'September' );
		$this->assertEquals( $report_output[2]['wages'], 18442.80 );
		$this->assertEquals( $report_output[2]['income_tax'], 4444.48 );
		$this->assertEquals( $report_output[2]['social_security_tax_total'], 0.00 );
		$this->assertEquals( $report_output[2]['medicare_tax_total'], 534.84 );
		$this->assertEquals( $report_output[2]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[2]['total_tax'], 4979.32 );

		//Total
		$this->assertEquals( $report_output[3]['wages'], 55328.40 );
		$this->assertEquals( $report_output[3]['income_tax'], 13333.44 );
		$this->assertEquals( $report_output[3]['social_security_tax_total'], 2758.08 );
		$this->assertEquals( $report_output[3]['medicare_tax_total'], 1604.52 );
		$this->assertEquals( $report_output[3]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[3]['total_tax'], 17696.04 );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		$this->assertEquals( $form_objs->objs[0]->l1, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l2, 55328.40 );
		$this->assertEquals( $form_objs->objs[0]->l3, 13333.44 );
		$this->assertEquals( $form_objs->objs[0]->l5a, 22243.20 );
		$this->assertEquals( $form_objs->objs[0]->l5b, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l5c, 55328.40 );
		$this->assertEquals( $form_objs->objs[0]->l5d, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l7z, 4362.60 );
		$this->assertEquals( $form_objs->objs[0]->l5_actual_deducted, 4362.60 );
		$this->assertEquals( $form_objs->objs[0]->l15b, TRUE );
		$this->assertEquals( $form_objs->objs[0]->l16_month1, 7266.24 );
		$this->assertEquals( $form_objs->objs[0]->l16_month2, 5450.48 );
		$this->assertEquals( $form_objs->objs[0]->l16_month3, 4979.32 );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, 17696.04 );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );

		$this->assertEquals( $form_objs->objs[0]->l5a2, 2758.16 );
		$this->assertEquals( $form_objs->objs[0]->l5b2, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l5c2, 1604.52 );
		$this->assertEquals( $form_objs->objs[0]->l5d2, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l5e, 4362.68 );
		$this->assertEquals( $form_objs->objs[0]->l4, TRUE );
		$this->assertEquals( $form_objs->objs[0]->l6, 17696.12 );
		$this->assertEquals( $form_objs->objs[0]->l7, -0.08 ); //Since the user reached the social security maximum contribution, we have to back out the fractions of the cent from previous quarters, at least to within 0.01.
		$this->assertEquals( $form_objs->objs[0]->l10, 17696.04);
		$this->assertEquals( $form_objs->objs[0]->l12, 17696.04);


		//Generate Report for entire year
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_config['time_period']['start_date'] = strtotime('01-Jan-2019');
		$report_config['time_period']['end_date'] = strtotime('30-Sep-2019');
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( $report_output[0]['date_month'], 'January' );
		$this->assertEquals( $report_output[0]['wages'], 9221.40 );
		$this->assertEquals( $report_output[0]['income_tax'], 2222.24 );
		$this->assertEquals( $report_output[0]['social_security_tax_total'], 1143.46 );
		$this->assertEquals( $report_output[0]['medicare_tax_total'], 267.42 );
		$this->assertEquals( $report_output[0]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[0]['total_tax'], 3633.12 );

		$this->assertEquals( $report_output[1]['date_month'], 'February' );
		$this->assertEquals( $report_output[1]['wages'], 18442.80 );
		$this->assertEquals( $report_output[1]['income_tax'], 4444.48 );
		$this->assertEquals( $report_output[1]['social_security_tax_total'], 2286.92 );
		$this->assertEquals( $report_output[1]['medicare_tax_total'], 534.84 );
		$this->assertEquals( $report_output[1]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[1]['total_tax'], 7266.24 );

		$this->assertEquals( $report_output[2]['date_month'], 'March' );
		$this->assertEquals( $report_output[2]['wages'], 18442.80 );
		$this->assertEquals( $report_output[2]['income_tax'], 4444.48 );
		$this->assertEquals( $report_output[2]['social_security_tax_total'], 2286.92 );
		$this->assertEquals( $report_output[2]['medicare_tax_total'], 534.84 );
		$this->assertEquals( $report_output[2]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[2]['total_tax'], 7266.24 );

		$this->assertEquals( $report_output[3]['date_month'], 'April' );
		$this->assertEquals( $report_output[3]['wages'], 18442.80 );
		$this->assertEquals( $report_output[3]['income_tax'], 4444.48 );
		$this->assertEquals( $report_output[3]['social_security_tax_total'], 2286.92 );
		$this->assertEquals( $report_output[3]['medicare_tax_total'], 534.84 );
		$this->assertEquals( $report_output[3]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[3]['total_tax'], 7266.24 );

		$this->assertEquals( $report_output[4]['date_month'], 'May' );
		$this->assertEquals( $report_output[4]['wages'], 27664.20 );
		$this->assertEquals( $report_output[4]['income_tax'], 6666.72 );
		$this->assertEquals( $report_output[4]['social_security_tax_total'], 3430.38 );
		$this->assertEquals( $report_output[4]['medicare_tax_total'], 802.26 );
		$this->assertEquals( $report_output[4]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[4]['total_tax'], 10899.36 );

		$this->assertEquals( $report_output[5]['date_month'], 'June' );
		$this->assertEquals( $report_output[5]['wages'], 18442.80 );
		$this->assertEquals( $report_output[5]['income_tax'], 4444.48 );
		$this->assertEquals( $report_output[5]['social_security_tax_total'], 2286.92 );
		$this->assertEquals( $report_output[5]['medicare_tax_total'], 534.84 );
		$this->assertEquals( $report_output[5]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[5]['total_tax'], 7266.24 );

		$this->assertEquals( $report_output[6]['date_month'], 'July' );
		$this->assertEquals( $report_output[6]['wages'], 18442.80 );
		$this->assertEquals( $report_output[6]['income_tax'], 4444.48 );
		$this->assertEquals( $report_output[6]['social_security_tax_total'], 2286.92 );
		$this->assertEquals( $report_output[6]['medicare_tax_total'], 534.84 );
		$this->assertEquals( $report_output[6]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[6]['total_tax'], 7266.24 );

		$this->assertEquals( $report_output[7]['date_month'], 'August' );
		$this->assertEquals( $report_output[7]['wages'], 18442.80 );
		$this->assertEquals( $report_output[7]['income_tax'], 4444.48 );
		$this->assertEquals( $report_output[7]['social_security_tax_total'], 471.16 );
		$this->assertEquals( $report_output[7]['medicare_tax_total'], 534.84 );
		$this->assertEquals( $report_output[7]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[7]['total_tax'], 5450.48 );

		$this->assertEquals( $report_output[8]['date_month'], 'September' );
		$this->assertEquals( $report_output[8]['wages'], 18442.80 );
		$this->assertEquals( $report_output[8]['income_tax'], 4444.48 );
		$this->assertEquals( $report_output[8]['social_security_tax_total'], 0.00 );
		$this->assertEquals( $report_output[8]['medicare_tax_total'], 534.84 );
		$this->assertEquals( $report_output[8]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[8]['total_tax'], 4979.32 );

		//Total
		$this->assertEquals( $report_output[9]['wages'], 165985.20 );
		$this->assertEquals( $report_output[9]['income_tax'], 40000.32 );
		$this->assertEquals( $report_output[9]['social_security_tax_total'], 16479.60 );
		$this->assertEquals( $report_output[9]['medicare_tax_total'], 4813.56 );
		$this->assertEquals( $report_output[9]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[9]['total_tax'], 61293.48 );

		return TRUE;
	}

	/**
	 * @group Form941Report_testMonthlyDepositLargePayPeriod
	 */
	function testMonthlyDepositLargePayPeriod() {
		//1st Quarter - Exceed all limits in first pay period
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 250000.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 200.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );

		//Skip a month, then a small pay period.
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 10000.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ) );

		$this->createPayStub();

		//Generate Report for 1st Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

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
		$this->assertEquals( $report_output[0]['wages'], 250200.68 );
		$this->assertEquals( $report_output[0]['income_tax'], 91173.54 );
		$this->assertEquals( $report_output[0]['social_security_tax_total'], 16479.60 );
		$this->assertEquals( $report_output[0]['medicare_tax_total'], 7255.82 );
		$this->assertEquals( $report_output[0]['additional_medicare_tax'], 451.81 );
		$this->assertEquals( $report_output[0]['total_tax'], 115360.77 );

		//February is blank - Skipped

		$this->assertEquals( $report_output[1]['date_month'], 'March' );
		$this->assertEquals( $report_output[1]['wages'], 10010.68 );
		$this->assertEquals( $report_output[1]['income_tax'], 2498.49 );
		$this->assertEquals( $report_output[1]['social_security_tax_total'], 0.00 );
		$this->assertEquals( $report_output[1]['medicare_tax_total'], 290.30 );
		$this->assertEquals( $report_output[1]['additional_medicare_tax'], 90.10 );
		$this->assertEquals( $report_output[1]['total_tax'], 2878.89 );

		//Total
		$this->assertEquals( $report_output[2]['wages'], 260211.36 );
		$this->assertEquals( $report_output[2]['income_tax'], 93672.03 );
		$this->assertEquals( $report_output[2]['social_security_tax_total'], 16479.60 );
		$this->assertEquals( $report_output[2]['medicare_tax_total'], 7546.12 );
		$this->assertEquals( $report_output[2]['additional_medicare_tax'], 541.91 );
		$this->assertEquals( $report_output[2]['total_tax'], 118239.66 );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		$this->assertEquals( $form_objs->objs[0]->l1, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l2, 260211.36 );
		$this->assertEquals( $form_objs->objs[0]->l3, 93672.03 );
		$this->assertEquals( $form_objs->objs[0]->l5a, 132699.66 );
		$this->assertEquals( $form_objs->objs[0]->l5b, 200.34 );
		$this->assertEquals( $form_objs->objs[0]->l5c, 260211.36 );
		$this->assertEquals( $form_objs->objs[0]->l5d, 60211.36 );
		$this->assertEquals( $form_objs->objs[0]->l7z, 24567.63 );
		$this->assertEquals( $form_objs->objs[0]->l5_actual_deducted, 24567.63 );
		$this->assertEquals( $form_objs->objs[0]->l15b, TRUE );
		$this->assertEquals( $form_objs->objs[0]->l16_month1, 115360.77 );
		//$this->assertEquals( $form_objs->objs[0]->l16_month2, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l16_month3, 2878.89 );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, 118239.66);
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );

		$this->assertEquals( $form_objs->objs[0]->l5a2, 16454.76 );
		$this->assertEquals( $form_objs->objs[0]->l5b2, 24.84 );
		$this->assertEquals( $form_objs->objs[0]->l5c2, 7546.13 );
		$this->assertEquals( $form_objs->objs[0]->l5d2, 541.90 );
		$this->assertEquals( $form_objs->objs[0]->l5e, 24567.63 );
		$this->assertEquals( $form_objs->objs[0]->l4, TRUE );
		$this->assertEquals( $form_objs->objs[0]->l6, 118239.66 );
		$this->assertEquals( $form_objs->objs[0]->l7, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l10, 118239.66);
		$this->assertEquals( $form_objs->objs[0]->l12, 118239.66);

		return TRUE;
	}

	/**
	 * @group Form941Report_testSemiWeeklyDeposit
	 */
	function testSemiWeeklyDeposit() {
		//1st Quarter - Stay below 200,000 medicare limit and 132,900 social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.34, TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.33, TTDate::getMiddleDayEpoch( $this->pay_period_objs[1]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.33, TTDate::getMiddleDayEpoch( $this->pay_period_objs[1]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.32, TTDate::getMiddleDayEpoch( $this->pay_period_objs[2]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.32, TTDate::getMiddleDayEpoch( $this->pay_period_objs[2]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.31, TTDate::getMiddleDayEpoch( $this->pay_period_objs[3]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.31, TTDate::getMiddleDayEpoch( $this->pay_period_objs[3]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.30, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.30, TTDate::getMiddleDayEpoch( $this->pay_period_objs[4]->getEndDate() ) );

		//2nd Quarter - Cross medicare and social security limit
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[5]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.29, TTDate::getMiddleDayEpoch( $this->pay_period_objs[5]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.28, TTDate::getMiddleDayEpoch( $this->pay_period_objs[6]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.28, TTDate::getMiddleDayEpoch( $this->pay_period_objs[6]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[7]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[7]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[8]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[9]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[10]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[11]->getEndDate() ) );

		//Extra pay period outside the 1st and 2nd quarter.
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'), 20000.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ) );
		$this->createPayStubAmendment( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Tips'), 10.27, TTDate::getMiddleDayEpoch( $this->pay_period_objs[12]->getEndDate() ) );

		$this->createPayStub();

		//Generate Report for 1st Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['deposit_schedule'] = 20; //Semi-Weekly
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
		$this->assertEquals( $report_output[0]['wages'], 20010.68 );
		$this->assertEquals( $report_output[0]['income_tax'], 6003.24 );
		$this->assertEquals( $report_output[0]['social_security_tax_total'], 2481.32 );
		$this->assertEquals( $report_output[0]['medicare_tax_total'], 580.30 );
		$this->assertEquals( $report_output[0]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[0]['total_tax'], 9064.86 );

		$this->assertEquals( $report_output[1]['date_month'], 'February' );
		$this->assertEquals( $report_output[1]['wages'], 40021.30 );
		$this->assertEquals( $report_output[1]['income_tax'], 12006.46 );
		$this->assertEquals( $report_output[1]['social_security_tax_total'], 4962.64 );
		$this->assertEquals( $report_output[1]['medicare_tax_total'], 1160.60 );
		$this->assertEquals( $report_output[1]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[1]['total_tax'], 18129.70 );

		$this->assertEquals( $report_output[2]['date_month'], 'March' );
		$this->assertEquals( $report_output[2]['wages'], 40021.22 );
		$this->assertEquals( $report_output[2]['income_tax'], 12006.43 );
		$this->assertEquals( $report_output[2]['social_security_tax_total'], 4962.64 );
		$this->assertEquals( $report_output[2]['medicare_tax_total'], 1160.60 );
		$this->assertEquals( $report_output[2]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[2]['total_tax'], 18129.67 );

		//Total
		$this->assertEquals( $report_output[3]['wages'], 100053.20 );
		$this->assertEquals( $report_output[3]['income_tax'], 30016.13 );
		$this->assertEquals( $report_output[3]['social_security_tax_total'], 12406.60 );
		$this->assertEquals( $report_output[3]['medicare_tax_total'], 2901.50 );
		$this->assertEquals( $report_output[3]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[3]['total_tax'], 45324.23 );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		$this->assertEquals( $form_objs->objs[0]->l1, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l2, 100053.20 );
		$this->assertEquals( $form_objs->objs[0]->l3, 30016.13 );
		$this->assertEquals( $form_objs->objs[0]->l5a, 100001.60 );
		$this->assertEquals( $form_objs->objs[0]->l5b, 51.60 );
		$this->assertEquals( $form_objs->objs[0]->l5c, 100053.20 );
		$this->assertEquals( $form_objs->objs[0]->l5d, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l7z, 15308.10 );
		$this->assertEquals( $form_objs->objs[0]->l5_actual_deducted, 15308.10 );
		$this->assertEquals( $form_objs->objs[0]->l15b, TRUE );
		$this->assertEquals( $form_objs->objs[0]->l16_month1, FALSE );
		$this->assertEquals( $form_objs->objs[0]->l16_month2, FALSE );
		$this->assertEquals( $form_objs->objs[0]->l16_month3, FALSE );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, 0.00 );
		//$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );

		$this->assertEquals( $form_objs->objs[0]->l5a2, 12400.20 );
		$this->assertEquals( $form_objs->objs[0]->l5b2, 6.40 );
		$this->assertEquals( $form_objs->objs[0]->l5c2, 2901.54 );
		$this->assertEquals( $form_objs->objs[0]->l5d2, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l5e, 15308.14 );
		$this->assertEquals( $form_objs->objs[0]->l4, TRUE );
		$this->assertEquals( $form_objs->objs[0]->l6, 45324.27 );
		$this->assertEquals( $form_objs->objs[0]->l7, -0.04 );
		$this->assertEquals( $form_objs->objs[0]->l10, 45324.23);
		$this->assertEquals( $form_objs->objs[0]->l12, 45324.23);

		//Schedule B
		//var_dump($form_objs->objs[1]->data);
		$this->assertEquals( $form_objs->objs[1]->month1[25], 9064.86 );
		$this->assertEquals( $form_objs->objs[1]->month1_total, 9064.86 );

		$this->assertEquals( $form_objs->objs[1]->month2[8], 9064.85 );
		$this->assertEquals( $form_objs->objs[1]->month2[22], 9064.85 );
		$this->assertEquals( $form_objs->objs[1]->month2_total, 18129.70 );

		$this->assertEquals( $form_objs->objs[1]->month3[8], 9064.84 );
		$this->assertEquals( $form_objs->objs[1]->month3[22], 9064.83 );
		$this->assertEquals( $form_objs->objs[1]->month3_total, 18129.67 );

		$this->assertEquals( $form_objs->objs[1]->total, 45324.23 );
		$this->assertEquals( $form_objs->objs[1]->total, $form_objs->objs[0]->l10 );


		//Generate Report for 2nd Quarter
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['deposit_schedule'] = 20; //Semi-Weekly
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
		$this->assertEquals( $report_output[0]['wages'], 40021.14 );
		$this->assertEquals( $report_output[0]['income_tax'], 12006.40 );
		$this->assertEquals( $report_output[0]['social_security_tax_total'], 4073.00 );
		$this->assertEquals( $report_output[0]['medicare_tax_total'], 1160.60 );
		$this->assertEquals( $report_output[0]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[0]['total_tax'], 17240.00 );

		$this->assertEquals( $report_output[1]['date_month'], 'May' );
		$this->assertEquals( $report_output[1]['wages'], 60031.62 );
		$this->assertEquals( $report_output[1]['income_tax'], 18009.57 );
		$this->assertEquals( $report_output[1]['social_security_tax_total'], 0.00 );
		$this->assertEquals( $report_output[1]['medicare_tax_total'], 1740.90 );
		$this->assertEquals( $report_output[1]['additional_medicare_tax'], 0.95 );
		$this->assertEquals( $report_output[1]['total_tax'], 19751.42 );

		$this->assertEquals( $report_output[2]['date_month'], 'June' );
		$this->assertEquals( $report_output[2]['wages'], 40021.08 );
		$this->assertEquals( $report_output[2]['income_tax'], 12006.38 );
		$this->assertEquals( $report_output[2]['social_security_tax_total'], 0.00 );
		$this->assertEquals( $report_output[2]['medicare_tax_total'], 1160.60 );
		$this->assertEquals( $report_output[2]['additional_medicare_tax'], 360.18 );
		$this->assertEquals( $report_output[2]['total_tax'], 13527.16 );

		//Total
		$this->assertEquals( $report_output[3]['wages'], 140073.84 );
		$this->assertEquals( $report_output[3]['income_tax'], 42022.35 );
		$this->assertEquals( $report_output[3]['social_security_tax_total'], 4073.00 );
		$this->assertEquals( $report_output[3]['medicare_tax_total'], 4062.10 );
		$this->assertEquals( $report_output[3]['additional_medicare_tax'], 361.13 );
		$this->assertEquals( $report_output[3]['total_tax'], 50518.58 );


		$report_obj->_outputPDFForm( 'pdf_form' ); //Calculate values for Form so they can be checked too.
		$form_objs = $report_obj->getFormObject();
		//var_dump($form_objs->objs[0]->data);

		$this->assertObjectHasAttribute( 'objs', $form_objs );
		$this->assertArrayHasKey( '0', $form_objs->objs );
		$this->assertObjectHasAttribute( 'data', $form_objs->objs[0] );

		$this->assertEquals( $form_objs->objs[0]->l1, 0.00 );
		$this->assertEquals( $form_objs->objs[0]->l2, 140073.84 );
		$this->assertEquals( $form_objs->objs[0]->l3, 42022.35 );
		$this->assertEquals( $form_objs->objs[0]->l5a, 32826.23 );
		$this->assertEquals( $form_objs->objs[0]->l5b, 20.57 );
		$this->assertEquals( $form_objs->objs[0]->l5c, 140073.84 );
		$this->assertEquals( $form_objs->objs[0]->l5d, 40127.04 );
		$this->assertEquals( $form_objs->objs[0]->l7z, 8496.23 );
		$this->assertEquals( $form_objs->objs[0]->l5_actual_deducted, 8496.23 );
		$this->assertEquals( $form_objs->objs[0]->l15b, TRUE );
		$this->assertEquals( $form_objs->objs[0]->l16_month1, FALSE );
		$this->assertEquals( $form_objs->objs[0]->l16_month2, FALSE );
		$this->assertEquals( $form_objs->objs[0]->l16_month3, FALSE );
		$this->assertEquals( $form_objs->objs[0]->l16_month_total, 0.00 );
		//$this->assertEquals( $form_objs->objs[0]->l16_month_total, $form_objs->objs[0]->l12 );

		$this->assertEquals( $form_objs->objs[0]->l5a2, 4070.45 );
		$this->assertEquals( $form_objs->objs[0]->l5b2, 2.55 );
		$this->assertEquals( $form_objs->objs[0]->l5c2, 4062.14 );
		$this->assertEquals( $form_objs->objs[0]->l5d2, 361.14 );
		$this->assertEquals( $form_objs->objs[0]->l5e, 8496.28 );
		$this->assertEquals( $form_objs->objs[0]->l4, TRUE );
		$this->assertEquals( $form_objs->objs[0]->l6, 50518.63 );
		$this->assertEquals( $form_objs->objs[0]->l7, -0.05 );
		$this->assertEquals( $form_objs->objs[0]->l10, 50518.58);
		$this->assertEquals( $form_objs->objs[0]->l12, 50518.58);

		//Schedule B
		//var_dump($form_objs->objs[1]->data);
		$this->assertEquals( $form_objs->objs[1]->month1[5], 9064.82 );
		$this->assertEquals( $form_objs->objs[1]->month1[19], 8175.18 );
		$this->assertEquals( $form_objs->objs[1]->month1_total, 17240.00 );

		$this->assertEquals( $form_objs->objs[1]->month2[3], 6583.49 );
		$this->assertEquals( $form_objs->objs[1]->month2[17], 6583.49 );
		$this->assertEquals( $form_objs->objs[1]->month2[31], 6584.44 );
		$this->assertEquals( $form_objs->objs[1]->month2_total, 19751.42 );

		$this->assertEquals( $form_objs->objs[1]->month3[14], 6763.58 );
		$this->assertEquals( $form_objs->objs[1]->month3[28], 6763.58 );
		$this->assertEquals( $form_objs->objs[1]->month3_total, 13527.16 );

		$this->assertEquals( $form_objs->objs[1]->total, 50518.58 );
		$this->assertEquals( $form_objs->objs[1]->total, $form_objs->objs[0]->l10 );


		//Generate Report for entire year
		$report_obj = new Form941Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setFormConfig( $report_obj->getCompanyFormConfig() );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_month' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_config['time_period']['start_date'] = strtotime('01-Jan-2019');
		$report_config['time_period']['end_date'] = strtotime('30-Jun-2019');
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'raw' );
		//var_dump($report_output);

		$this->assertEquals( $report_output[0]['date_month'], 'January' );
		$this->assertEquals( $report_output[0]['wages'], 20010.68 );
		$this->assertEquals( $report_output[0]['income_tax'], 6003.24 );
		$this->assertEquals( $report_output[0]['social_security_tax_total'], 2481.32 );
		$this->assertEquals( $report_output[0]['medicare_tax_total'], 580.30 );
		$this->assertEquals( $report_output[0]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[0]['total_tax'], 9064.86 );

		$this->assertEquals( $report_output[1]['date_month'], 'February' );
		$this->assertEquals( $report_output[1]['wages'], 40021.30 );
		$this->assertEquals( $report_output[1]['income_tax'], 12006.46 );
		$this->assertEquals( $report_output[1]['social_security_tax_total'], 4962.64 );
		$this->assertEquals( $report_output[1]['medicare_tax_total'], 1160.60 );
		$this->assertEquals( $report_output[1]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[1]['total_tax'], 18129.70 );

		$this->assertEquals( $report_output[2]['date_month'], 'March' );
		$this->assertEquals( $report_output[2]['wages'], 40021.22 );
		$this->assertEquals( $report_output[2]['income_tax'], 12006.43 );
		$this->assertEquals( $report_output[2]['social_security_tax_total'], 4962.64 );
		$this->assertEquals( $report_output[2]['medicare_tax_total'], 1160.60 );
		$this->assertEquals( $report_output[2]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[2]['total_tax'], 18129.67 );

		$this->assertEquals( $report_output[3]['date_month'], 'April' );
		$this->assertEquals( $report_output[3]['wages'], 40021.14 );
		$this->assertEquals( $report_output[3]['income_tax'], 12006.40 );
		$this->assertEquals( $report_output[3]['social_security_tax_total'], 4073.00 );
		$this->assertEquals( $report_output[3]['medicare_tax_total'], 1160.60 );
		$this->assertEquals( $report_output[3]['additional_medicare_tax'], 0.00 );
		$this->assertEquals( $report_output[3]['total_tax'], 17240.00 );

		$this->assertEquals( $report_output[4]['date_month'], 'May' );
		$this->assertEquals( $report_output[4]['wages'], 60031.62 );
		$this->assertEquals( $report_output[4]['income_tax'], 18009.57 );
		$this->assertEquals( $report_output[4]['social_security_tax_total'], 0.00 );
		$this->assertEquals( $report_output[4]['medicare_tax_total'], 1740.90 );
		$this->assertEquals( $report_output[4]['additional_medicare_tax'], 0.95 );
		$this->assertEquals( $report_output[4]['total_tax'], 19751.42 );

		$this->assertEquals( $report_output[5]['date_month'], 'June' );
		$this->assertEquals( $report_output[5]['wages'], 40021.08 );
		$this->assertEquals( $report_output[5]['income_tax'], 12006.38 );
		$this->assertEquals( $report_output[5]['social_security_tax_total'], 0.00 );
		$this->assertEquals( $report_output[5]['medicare_tax_total'], 1160.60 );
		$this->assertEquals( $report_output[5]['additional_medicare_tax'], 360.18 );
		$this->assertEquals( $report_output[5]['total_tax'], 13527.16 );

		//Total
		$this->assertEquals( $report_output[6]['wages'], 240127.04 );
		$this->assertEquals( $report_output[6]['income_tax'], 72038.48 );
		$this->assertEquals( $report_output[6]['social_security_tax_total'], 16479.60 );
		$this->assertEquals( $report_output[6]['medicare_tax_total'], 6963.60 );
		$this->assertEquals( $report_output[6]['additional_medicare_tax'], 361.13 );
		$this->assertEquals( $report_output[6]['total_tax'], 95842.81 );

		return TRUE;
	}

}
?>
