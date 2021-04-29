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

class FormW2ReportTest extends PHPUnit\Framework\TestCase {
	protected $company_id = null;
	protected $user_id = null;
	protected $pay_period_schedule_id = null;
	protected $pay_period_objs = null;
	protected $pay_stub_account_link_arr = null;

	public function setUp(): void {
		global $dd;
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setTimeZone( 'America/Vancouver', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		//Skip setup for all testEFile* tests, as they don't need any of this data.
		if ( strpos( $this->getName(), 'testEFile' ) === false ) {
			$dd = new DemoData();
			$dd->setEnableQuickPunch( false );                     //Helps prevent duplicate punch IDs and validation failures.
			$dd->setUserNamePostFix( '_' . uniqid( null, true ) ); //Needs to be super random to prevent conflicts and random failing tests.
			$dd->setDate( TTDate::strtotime( '30-Dec-2020' ) );
			$dd->setRandomSeed( $dd->getDate() ); //Force the random seed to always be the same, even if the UserNamePostFix is different.

			$this->company_id = $dd->createCompany();
			$this->legal_entity_id = $dd->createLegalEntity( $this->company_id, 10 );
			Debug::text( 'Company ID: ' . $this->company_id, __FILE__, __LINE__, __METHOD__, 10 );

			$this->currency_id = $dd->createCurrency( $this->company_id, 10 );

			//Permissions are required so the user has permissions to run reports.
			$dd->createPermissionGroups( $this->company_id, 40 );  //Administrator only.

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
			$this->user_obj->setWorkEmail( 'demoadmin@abc-company.com' ); //Force a consistent/stable email address.
			if ( $this->user_obj->isValid() ) {
				$this->user_obj->Save( false );
			}

			$this->createPayPeriodSchedule();
			$this->createPayPeriods();
			$this->getAllPayPeriods();

			$dd->createTaxForms( $this->company_id, $this->user_id[0] );

			$this->assertGreaterThan( 0, $this->company_id );
			$this->assertGreaterThan( 0, $this->user_id[0] );
		}
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
		$psaf->setStatus( 50 );                                  //Active

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
	 * @group FormW2Report_testEFileFederalA
	 */
	function testEFileFederalA() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = null;         //Federal.
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l15a_state'    => 'NY',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'NYC',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileFederalB
	 */
	function testEFileFederalB() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = null;         //Federal.
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'NY',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'NYC',

				//'l15b_state'    => 'NY',
				//'l15b_state_id' => '11223355',
				//'l16b'          => '16.02',
				//'l17b'          => '17.02',
				//'l18b'          => '18.02',
				//'l19b'          => '19.02',
				//'l20b'          => 'YONKER',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );


	}

	/**
	 * @group FormW2Report_testEFileFederalC
	 */
	function testEFileFederalC() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = null;         //Federal.
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'NY',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'NYC',

				'l15b_state'    => 'NY',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'YONKER',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * Multiple employees
	 * @group FormW2Report_testEFileFederalMultiEmployeeA
	 */
	function testEFileFederalMultiEmployeeA() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = null;         //Federal.
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'NY',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'NYC',

				'l15b_state'    => 'NY',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'YONKER',
		];
		$fw2_obj->addRecord( $ee_data );

		$ee_data = [
				'ssn'      => '123-45-6799',
				'address1' => '3322 CARRINGTON ST',
				'address2' => 'UNIT 827',
				'city'     => 'SEATTLE',
				'state'    => 'MS',
				'zip_code' => '12572',

				//'control_number' => '0001',

				'first_name'  => 'JANE',
				'middle_name' => 'N',
				'last_name'   => 'SMITH',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'MS',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'NYC',

				'l15b_state'    => 'MS',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'YONKER',
		];
		$fw2_obj->addRecord( $ee_data );

		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );


	}


	/**
	 * Multiple employees
	 * @group FormW2Report_testEFileFederalMultiEmployeeB
	 */
	function testEFileFederalMultiEmployeeB() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = null;         //Federal.
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'NY',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'NYC',

				'l15b_state'    => 'MS',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'YONKER',
		];
		$fw2_obj->addRecord( $ee_data );

		$ee_data = [
				'ssn'      => '123-45-6799',
				'address1' => '3322 CARRINGTON ST',
				'address2' => 'UNIT 827',
				'city'     => 'SEATTLE',
				'state'    => 'MS',
				'zip_code' => '12572',

				//'control_number' => '0001',

				'first_name'  => 'JANE',
				'middle_name' => 'N',
				'last_name'   => 'SMITH',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'NY',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'NYC',

				'l15b_state'    => 'AL',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'YONKER',
		];
		$fw2_obj->addRecord( $ee_data );

		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateAL
	 */
	function testEFileStateAL() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'AL';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'AL',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'AL',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateAR
	 */
	function testEFileStateAR() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'AR';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'AR',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'AR',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateAZ
	 */
	function testEFileStateAZ() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'AZ';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'AZ',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'AZ',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );


	}

	/**
	 * @group FormW2Report_testEFileStateCO
	 */
	function testEFileStateCO() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'CO';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'CO',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'CO',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateCT
	 */
	function testEFileStateCT() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'CT';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'CT',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'CT',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );


	}

	/**
	 * @group FormW2Report_testEFileStateDC
	 */
	function testEFileStateDC() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'DC';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'DC',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'DC',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateDE
	 */
	function testEFileStateDE() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'DE';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'DE',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'DE',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );


	}

	/**
	 * @group FormW2Report_testEFileStateGA
	 */
	function testEFileStateGA() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'GA';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'GA',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'GA',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateIA
	 */
	function testEFileStateIA() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'IA';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.
		$fw2_obj->state_secondary_id = '33445566'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'IA',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'IA',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',

				'l15c_state'    => 'MO',
				'l15c_state_id' => '11223366',
				'l15c_state_control_number' => '655',
				'l16c'          => '16.03',
				'l17c'          => '17.03',
				'l18c'          => '18.03',
				'l19c'          => '19.03',
				'l20c'          => 'LOC3',

		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateMultiEmployeeIA
	 */
	function testEFileStateMultiEmployeeIA() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'IA';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.
		$fw2_obj->state_secondary_id = '33445566'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'IA',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'IA',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',

				'l15c_state'    => 'MO',
				'l15c_state_id' => '11223366',
				'l15c_state_control_number' => '655',
				'l16c'          => '16.03',
				'l17c'          => '17.03',
				'l18c'          => '18.03',
				'l19c'          => '19.03',
				'l20c'          => 'LOC3',

		];
		$fw2_obj->addRecord( $ee_data );

		$ee_data = [
				'ssn'      => '123-45-6799',
				'address1' => '3322 CARRINGTON ST',
				'address2' => 'UNIT 827',
				'city'     => 'SEATTLE',
				'state'    => 'MS',
				'zip_code' => '12572',

				//'control_number' => '0001',

				'first_name'  => 'JANE',
				'middle_name' => 'N',
				'last_name'   => 'SMITH',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'IA',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'IA',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',

				'l15c_state'    => 'MO',
				'l15c_state_id' => '11223366',
				'l15c_state_control_number' => '655',
				'l16c'          => '16.03',
				'l17c'          => '17.03',
				'l18c'          => '18.03',
				'l19c'          => '19.03',
				'l20c'          => 'LOC3',

		];
		$fw2_obj->addRecord( $ee_data );

		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateIL
	 */
	function testEFileStateIL() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'IL';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'IL',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'IL',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateIN
	 */
	function testEFileStateIN() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'IN';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'IN',
				'l15a_state_id' => '11223344 987',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'IN',
				'l15b_state_id' => '11223355 987',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateKS
	 */
	function testEFileStateKS() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'KS';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'KPER', //KPER must appear on W2 for KS.
				'l14a'      => 87.78,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'KS',
				'l15a_state_id' => '0361234567F89', //Special Kansas Withholding Account Number.
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'KS',
				'l15b_state_id' => '0361234567F99',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateKY
	 */
	function testEFileStateKY() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'KY';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'KY',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'KY',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );


	}

	/**
	 * @group FormW2Report_testEFileStateMA
	 */
	function testEFileStateMA() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'MA';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'MA',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'MA',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateME
	 */
	function testEFileStateME() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'ME';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'ME',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'ME',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );


	}

	/**
	 * @group FormW2Report_testEFileStateMI
	 */
	function testEFileStateMI() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'MI';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'MI',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'MI',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateMN
	 */
	function testEFileStateMN() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'MN';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'MN',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'MN',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );


	}

	/**
	 * @group FormW2Report_testEFileStateMO
	 */
	function testEFileStateMO() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'MO';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'MO',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'MO',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateMS
	 */
	function testEFileStateMS() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'MS';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'MS',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'MS',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );


	}

	/**
	 * @group FormW2Report_testEFileStateMT
	 */
	function testEFileStateMT() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'MT';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'MT',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'MT',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateNC
	 */
	function testEFileStateNC() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'NC';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'NC',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'NC',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );


	}

	/**
	 * @group FormW2Report_testEFileStateND
	 */
	function testEFileStateND() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'ND';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'ND',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'ND',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateNE
	 */
	function testEFileStateNE() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'NE';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'NE',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'NE',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateNY
	 */
	function testEFileStateNY() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'NY';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1' 			=> 1.01,
				'l2' 			=> 2.02,
				'l3' 			=> 3.03,
				'l4'          => 4.04,
				'l5'          => 5.05,
				'l6'          => 6.06,
				'l7'          => 7.07,
				'l8'          => 8.08,
				'l10'         => 10.10,
				'l11'         => 11.11,
				'l12a_code'   => 'A',
				'l12a'        => 12.01,
				'l12b_code'   => 'R',
				'l12b'        => 12.02,
				'l12c_code'   => 'S',
				'l12c'        => 12.03,
				'l12d_code'   => 'T',
				'l12d'        => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'NY',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'NYC',

				'l15b_state'    => 'NY',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'YONKER',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ .'.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ .'_'. __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateOK
	 */
	function testEFileStateOK() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'OK';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'OK',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'OK',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateOR
	 */
	function testEFileStateOR() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'OR';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'OR',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'OR',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStatePA
	 */
	function testEFileStatePA() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'PA';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'PA',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'PA',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateSC
	 */
	function testEFileStateSC() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'SC';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'SC',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'SC',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateUT
	 */
	function testEFileStateUT() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'UT';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'UT',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'UT',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateVA
	 */
	function testEFileStateVA() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'VA';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'VA',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'VA',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateVT
	 */
	function testEFileStateVT() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'VT';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'VT',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'VT',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testEFileStateWI
	 */
	function testEFileStateWI() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();
		$fw2_obj = $gf->getFormObject( 'w2', 'US' );
		$fw2_obj->setType( 'government' );
		$fw2_obj->setDebug( false );
		$fw2_obj->setShowBackground( true );
		$fw2_obj->year = 2020;
		$fw2_obj->ein = '92-9356262';
		$fw2_obj->trade_name = 'ACME USA EAST';
		$fw2_obj->company_address1 = '123 MAIN ST';
		$fw2_obj->company_address2 = 'UNIT 123';
		$fw2_obj->company_city = 'NEW YORK';
		$fw2_obj->company_state = 'NY';
		$fw2_obj->company_zip_code = '12345';

		$fw2_obj->contact_name = 'MR ADMINISTRATOR';
		$fw2_obj->contact_phone = '555-555-5555';
		$fw2_obj->contact_phone_ext = '';
		$fw2_obj->contact_fax = '444-444-4444';
		$fw2_obj->contact_email = 'DEMOADMIN1@ABC-COMPANY.COM';

		$fw2_obj->kind_of_employer = 'N';
		$fw2_obj->efile_state = 'WI';
		$fw2_obj->efile_user_id = 'EF123456'; //Must be 8 chars.

		$ee_data = [
				'ssn'      => '123-45-6789',
				'address1' => '4187 SPRINGFIELD ST',
				'address2' => 'UNIT 319',
				'city'     => 'NEW YORK',
				'state'    => 'NY',
				'zip_code' => '00572',

				//'control_number' => '0001',

				'first_name'  => 'JOHN',
				'middle_name' => 'M',
				'last_name'   => 'DOE',

				'l1'        => 1.01,
				'l2'        => 2.02,
				'l3'        => 3.03,
				'l4'        => 4.04,
				'l5'        => 5.05,
				'l6'        => 6.06,
				'l7'        => 7.07,
				'l8'        => 8.08,
				'l10'       => 10.10,
				'l11'       => 11.11,
				'l12a_code' => 'A',
				'l12a'      => 12.01,
				'l12b_code' => 'R',
				'l12b'      => 12.02,
				'l12c_code' => 'S',
				'l12c'      => 12.03,
				'l12d_code' => 'T',
				'l12d'      => 12.04,

				'l13a' => true,
				'l13b' => false,
				'l13c' => true,

				'l14a_name' => 'Test1',
				'l14a'      => 3.55,
				'l14b_name' => 'Test2',
				'l14b'      => 55.56,
				'l14c_name' => 'Test3',
				'l14c'      => 1253345.57,
				'l14d_name' => 'Test4',
				'l14d'      => 13.58,

				'l15a_state'    => 'WI',
				'l15a_state_id' => '11223344',
				'l15a_state_control_number' => '654',
				'l16a'          => '16.01',
				'l17a'          => '17.01',
				'l18a'          => '18.01',
				'l19a'          => '19.01',
				'l20a'          => 'LOC1',

				'l15b_state'    => 'WI',
				'l15b_state_id' => '11223355',
				'l15b_state_control_number' => '653',
				'l16b'          => '16.02',
				'l17b'          => '17.02',
				'l18b'          => '18.02',
				'l19b'          => '19.02',
				'l20b'          => 'LOC2',
		];
		$fw2_obj->addRecord( $ee_data );
		$gf->addForm( $fw2_obj );

		$output = $gf->output( 'efile' );
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $output );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $output, $output );
	}

	/**
	 * @group FormW2Report_testFederalEFileWithFederalAndStateTaxesA
	 */
	function testFederalEFileWithFederalAndStateTaxesA() {

		$i = 0;
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

			if ( $i > 2 )  {
				break; //Only create pay stubs for three employees.
			}

			$i++;
		}

		//Generate W2 eFile Report.
		$report_obj = new FormW2Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['efile_state'] = ''; //Blank for federal
		$form_config['l10']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['l10']['exclude_pay_stub_entry_account'] = [];
		$form_config['l11']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['l11']['exclude_pay_stub_entry_account'] = [];

		$form_config['l12a_code'] = 'A';
		$form_config['l12a']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['l12a']['exclude_pay_stub_entry_account'] = [];
		$form_config['l12b_code'] = 'R';
		$form_config['l12b']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['l12b']['exclude_pay_stub_entry_account'] = [];
		$form_config['l12c_code'] = 'S';
		$form_config['l12c']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['l12c']['exclude_pay_stub_entry_account'] = [];
		$form_config['l12d_code'] = 'T';
		$form_config['l12d']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['l12d']['exclude_pay_stub_entry_account'] = [];

		$form_config['l14a_name'] = 'Test1';
		$form_config['l14a']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['l14a']['exclude_pay_stub_entry_account'] = [];
		$form_config['l14b_name'] = 'Test2';
		$form_config['l14b']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['l14b']['exclude_pay_stub_entry_account'] = [];
		$form_config['l14c_name'] = 'Test3';
		$form_config['l14c']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['l14c']['exclude_pay_stub_entry_account'] = [];
		$form_config['l14d_name'] = 'Test4';
		$form_config['l14d']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['l14d']['exclude_pay_stub_entry_account'] = [];
		$report_obj->setFormConfig( $form_config );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_employee' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'efile' );
		//var_export($report_output);
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $report_output['data'] );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $report_output['data'], $report_output['data'] );

		return true;
	}

	/**
	 * @group FormW2Report_testNYEFileWithFederalAndStateTaxesA
	 */
	function testNYEFileWithFederalAndStateTaxesA() {
		$i = 0;
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

			if ( $i > 2 )  {
				break; //Only create pay stubs for three employees.
			}
		}

		//Generate W2 eFile Report.
		$report_obj = new FormW2Report();
		$report_obj->setUserObject( $this->user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$form_config = $report_obj->getCompanyFormConfig();
		$form_config['efile_state'] = 'NY'; //NY format
		$form_config['l10']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['l10']['exclude_pay_stub_entry_account'] = [];
		$form_config['l11']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['l11']['exclude_pay_stub_entry_account'] = [];

		$form_config['l12a_code'] = 'A';
		$form_config['l12a']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['l12a']['exclude_pay_stub_entry_account'] = [];
		$form_config['l12b_code'] = 'R';
		$form_config['l12b']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['l12b']['exclude_pay_stub_entry_account'] = [];
		$form_config['l12c_code'] = 'S';
		$form_config['l12c']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['l12c']['exclude_pay_stub_entry_account'] = [];
		$form_config['l12d_code'] = 'T';
		$form_config['l12d']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['l12d']['exclude_pay_stub_entry_account'] = [];

		$form_config['l14a_name'] = 'Test1';
		$form_config['l14a']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['l14a']['exclude_pay_stub_entry_account'] = [];
		$form_config['l14b_name'] = 'Test2';
		$form_config['l14b']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['l14b']['exclude_pay_stub_entry_account'] = [];
		$form_config['l14c_name'] = 'Test3';
		$form_config['l14c']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ), CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['l14c']['exclude_pay_stub_entry_account'] = [];
		$form_config['l14d_name'] = 'Test4';
		$form_config['l14d']['include_pay_stub_entry_account'] = [ CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Tips' ) ]; //Exempt Payments
		$form_config['l14d']['exclude_pay_stub_entry_account'] = [];
		$report_obj->setFormConfig( $form_config );

		$report_config = Misc::trimSortPrefix( $report_obj->getTemplate( 'by_employee' ) );

		$report_config['time_period']['time_period'] = 'custom_date';
		$report_dates = TTDate::getTimePeriodDates( 'this_year', TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getEndDate() ) );
		$report_config['time_period']['start_date'] = $report_dates['start_date'];
		$report_config['time_period']['end_date'] = $report_dates['end_date'];
		$report_obj->setConfig( $report_config );
		//var_dump($report_config);

		$report_output = $report_obj->getOutput( 'efile' );
		//var_export($report_output);
		//file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt', $report_output['data'] );
		$this->assertEquals( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . __CLASS__ . '_' . __FUNCTION__ . '.txt' ), $report_output['data'], $report_output['data'] );
	}

	/**
	 * @group FormW2Report_testFormW2SerializeUnSerialize
	 */
	function testFormW2SerializeUnSerialize() {
		require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

		$gf = new GovernmentForms();

		$fw2 = $gf->getFormObject( 'w2', 'US' );
		$fw2->setType( 'government' );
		$fw2->setShowInstructionPage( false );
		$fw2->year = '2020';
		$fw2->kind_of_employer = 'N';

		$fw2->name = 'Legal Company Name';
		$fw2->trade_name = 'Legal Company Trade Name';
		$fw2->company_address1 = '123 Main St';
		$fw2->company_city = 'New York';
		$fw2->company_state = 'NY';
		$fw2->company_zip_code = '12345';

		$fw2->ein = '123456789';
		$fw2->efile_user_id = 'EF123456';

		$fw2->contact_name = 'John Doe';
		$fw2->contact_phone = '555-555-5555';
		$fw2->contact_phone_ext = '123';
		$fw2->contact_email = 'test@test.com';

		$ee_data = [
				'control_number'      => 1,
				'first_name'          => 'Jane',
				'middle_name'         => 'M',
				'last_name'           => 'Doe',
				'address1'            => '456 Main St',
				'address2'            => 'Unit #123',
				'city'                => 'Seattle',
				'state'               => 'WA',
				'employment_province' => 'WA',
				'zip_code'            => '12345',
				'ssn'                 => '123456789',
				'employee_number'     => 1,
				'l1'                  => 1.01,
				'l2'                  => 1.02,
				'l3'                  => 1.03,
				'l4'                  => 1.04,
				'l5'                  => 1.05,
				'l6'                  => 1.06,
				'l7'                  => 1.07,
				'l8'                  => 1.08,
				'l10'                 => 1.09,
				'l11'                 => 1.10,
				'l12a_code'           => null,
				'l12a'                => null,
				'l12b_code'           => null,
				'l12b'                => null,
				'l12c_code'           => null,
				'l12c'                => null,
				'l12d_code'           => null,
				'l12d'                => null,
				'l13b'                => false,
				'l14a_name'           => null,
				'l14a'                => null,
				'l14b_name'           => null,
				'l14b'                => null,
				'l14c_name'           => null,
				'l14c'                => null,
				'l14d_name'           => null,
				'l14d'                => null,
		];

		$fw2->addRecord( $ee_data );
		$gf->addForm( $fw2 );
		$original_efile_data = $gf->output( 'EFILE', false );
		$serialized_data = $gf->serialize( false );

		//Create new GovernmentForms object and unserialize the data into it and ensure it matches the original above.
		$gfb = new GovernmentForms();
		$gfb->unserialize( $serialized_data );
		$unserialized_efile_data = $gfb->output( 'EFILE', false );

		unset( $gf, $gfb, $serialized_data );

		$this->assertEquals( $original_efile_data, $unserialized_efile_data );
	}
}

?>
