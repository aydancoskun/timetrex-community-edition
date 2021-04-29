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

/**
 * @group USPayrollDeductionTest2006
 */
class USPayrollDeductionTest2006 extends PHPUnit\Framework\TestCase {
	public $company_id = null;

	public function setUp(): void {
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		require_once( Environment::getBasePath() . '/classes/payroll_deduction/PayrollDeduction.class.php' );

		$this->company_id = PRIMARY_COMPANY_ID;

		TTDate::setTimeZone( 'Etc/GMT+8' ); //Force to non-DST timezone. 'PST' isnt actually valid.
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	public function mf( $amount ) {
		return Misc::MoneyRound( $amount );
	}

	//
	//
	//
	// 2006
	//
	//
	//

	//
	// US - Federal Taxes
	//

	function testUS_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
	}

	function testUS_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '56.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //56.54
	}

	function testUS_2006a_BiWeekly_Married_LowIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 3 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '31.15', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
	}

	function testUS_2006a_SemiMonthly_Single_LowIncome() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '97.50', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //97.50
	}

	function testUS_2006a_SemiMonthly_Married_LowIncome() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '52.92', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //52.92
	}

	function testUS_2006a_SemiMonthly_Single_MedIncome() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 2000.00 );

		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '299.42', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //299.42
	}

	function testUS_2006a_SemiMonthly_Single_HighIncome() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
	}

	function testUS_2006a_SemiMonthly_Single_LowIncome_3Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 3 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '56.25', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //56.25
	}

	function testUS_2006a_SemiMonthly_Single_LowIncome_5Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 5 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '20.21', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //20.21
	}

	function testUS_2006a_SemiMonthly_Single_LowIncome_8AllowancesA() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //0.00
	}

	function testUS_2006a_SemiMonthly_Single_LowIncome_8AllowancesB() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1300.00 );

		$this->assertEquals( '1300.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '8.96', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //8.96
	}


	//
	// US Social Security
	//
	function testUS_2006a_SocialSecurity() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '62.00', $this->mf( $pd_obj->getEmployeeSocialSecurity() ) );
	}

	function testUS_2006a_SocialSecurity_Max() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 5839.40 ); //5840.40

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getEmployeeSocialSecurity() ) );
	}

	function testUS_2006a_Medicare() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '14.50', $this->mf( $pd_obj->getEmployeeMedicare() ) );
		$this->assertEquals( '14.50', $this->mf( $pd_obj->getEmployerMedicare() ) );
	}

	function testUS_2006a_FederalUI_NoState() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );
		$pd_obj->setYearToDateFederalUIContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '62.00', $this->mf( $pd_obj->getFederalEmployerUI() ) );
	}

	function testUS_2006a_FederalUI_NoState_Max() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateUIRate( 0 );
		$pd_obj->setStateUIWageBase( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );
		$pd_obj->setYearToDateFederalUIContribution( 433 ); //434
		$pd_obj->setYearToDateStateUIContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getFederalEmployerUI() ) );
	}

	function testUS_2006a_FederalUI_State_Max() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateUIRate( 3.51 );
		$pd_obj->setStateUIWageBase( 11000 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );
		$pd_obj->setYearToDateFederalUIContribution( 187.30 ); //188.30
		$pd_obj->setYearToDateStateUIContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getFederalEmployerUI() ) );
	}

	//
	// State Income Taxes
	//

	//
	// MO
	//
	function testMO_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '31.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //31.00
	}

	function testMO_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '56.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //56.54
		$this->assertEquals( '33.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //33.00
	}

	function testMO_2006a_SemiMonthly_Single_LowIncome() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '97.50', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //97.50
		$this->assertEquals( '29.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //33.00
	}

	function testMO_2006a_SemiMonthly_Single_LowIncome_8AllowancesB() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 8 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1300.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1300.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '8.96', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //8.96
		$this->assertEquals( '31.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );  //31.00
	}

	function testMO_2006a_SemiMonthly_Married_HighIncome() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '601.08', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //601.08
		$this->assertEquals( '202.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //202.00
	}

	function testMO_2006a_StateUI() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateUIRate( 3.51 );
		$pd_obj->setStateUIWageBase( 11000 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );
		$pd_obj->setYearToDateFederalUIContribution( 0 );
		$pd_obj->setYearToDateStateUIContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '26.90', $this->mf( $pd_obj->getFederalEmployerUI() ) );
		$this->assertEquals( '35.10', $this->mf( $pd_obj->getStateEmployerUI() ) );
	}

	function testMO_2006a_StateUI_Max() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateUIRate( 3.51 );
		$pd_obj->setStateUIWageBase( 11000 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );
		$pd_obj->setYearToDateFederalUIContribution( 187.30 ); //188.30
		$pd_obj->setYearToDateStateUIContribution( 385.10 ); //386.10

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getFederalEmployerUI() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getStateEmployerUI() ) );
	}

	//
	// CA
	//
	function testCA_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CA' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '17.70', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //17.70
	}

	function testCA_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CA' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Married, one person working
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '9.29', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );     //9.29
	}

	function testCA_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CA' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Married, one person working
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '148.52', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //148.52
	}

	//
	// NY
	//
	function testNY_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NY' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '33.71', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //17.70
	}

	function testNY_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NY' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '32.58', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //29.54
	}

	function testNY_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NY' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '213.29', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //213.29
	}

	//
	// NY - NYC
	//
	function testNY_NYC_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NY', 'NYC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setDistrictFilingStatus( 10 ); //Single
		$pd_obj->setDistrictAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '33.71', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //17.70
		$this->assertEquals( '21.19', $this->mf( $pd_obj->getDistrictPayPeriodDeductions() ) ); //21.19
	}

	function testNY_NYC_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NY', 'NYC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setDistrictFilingStatus( 20 ); //Married
		$pd_obj->setDistrictAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '32.58', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //29.54
		$this->assertEquals( '20.48', $this->mf( $pd_obj->getDistrictPayPeriodDeductions() ) ); //20.48
	}

	function testNY_NYC_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NY', 'NYC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setDistrictFilingStatus( 20 ); //Married
		$pd_obj->setDistrictAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );  //823.73
		$this->assertEquals( '213.29', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //213.29
		$this->assertEquals( '125.04', $this->mf( $pd_obj->getDistrictPayPeriodDeductions() ) ); //125.04
	}

	//
	// NY - Yonkers
	//
	function testNY_Yonkers_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NY', 'YONKERS' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '33.71', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //17.70
	}

	function testNY_Yonkers_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NY', 'YONKERS' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '32.58', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //29.54
	}

	function testNY_Yonkers_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NY', 'YONKERS' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '213.29', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //213.29
	}

	//
	// IL
	//
	function testIL_2006a_BiWeekly_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'IL' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1( 1 ); //Line 1 on form
		$pd_obj->setUserValue2( 1 ); //Line 2 on form

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '26.54', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //26.54
	}

	function testIL_2006a_BiWeekly_LowIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'IL' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1( 2 ); //Line 1 on form
		$pd_obj->setUserValue2( 3 ); //Line 2 on form

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '21.92', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //21.92
	}

	function testIL_2006a_SemiMonthly_HighIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'IL' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1( 2 ); //Line 1 on form
		$pd_obj->setUserValue2( 3 ); //Line 2 on form

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '111.25', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //21.25
	}

	//
	// PA
	//
	function testPA_2006a_BiWeekly_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'PA' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '30.70', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //30.70
	}

	//
	// OH
	//
	function testOH_2006a_BiWeekly_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OH' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '27.40', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //27.40
	}

	function testOH_2006a_BiWeekly_LowIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OH' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 3 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '25.08', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //25.08
	}

	function testOH_2006a_SemiMonthly_HighIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OH' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 3 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '184.52', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //184.52
	}

	//
	// MI
	//
	function testMI_2006a_BiWeekly_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MI' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '34.05', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //34.05
	}

	function testMI_2006a_BiWeekly_LowIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MI' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 3 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '24.15', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //24.15
	}

	//
	// GA
	//
	function testGA_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'GA' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setUserValue2( 1 ); //Employee/Spouse
		$pd_obj->setUserValue3( 1 ); //Dependant

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '34.23', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //34.23
	}

	function testGA_2006a_BiWeekly_Single_LowIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'GA' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setUserValue2( 3 ); //Employee/Spouse
		$pd_obj->setUserValue3( 3 ); //Dependant

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '8.08', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );     //8.08
	}

	function testGA_2006a_BiWeekly_Single_HighIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'GA' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setUserValue2( 1 ); //Employee/Spouse
		$pd_obj->setUserValue3( 1 ); //Dependant

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '212.08', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) ); //212.08
	}

	function testGA_2006a_BiWeekly_MarriedSeparate_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'GA' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 );
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married Separately
		$pd_obj->setUserValue2( 1 ); //Employee/Spouse
		$pd_obj->setUserValue3( 1 ); //Dependant

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '36.08', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //38.38
	}

	function testGA_2006a_BiWeekly_MarriedOneIncome_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'GA' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 );
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Married OneIncome
		$pd_obj->setUserValue2( 1 ); //Employee/Spouse
		$pd_obj->setUserValue3( 1 ); //Dependant

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '19.08', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testGA_2006a_BiWeekly_MarriedTwoIncome_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'GA' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 );
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 40 ); //Married OneIncome
		$pd_obj->setUserValue2( 1 ); //Employee/Spouse
		$pd_obj->setUserValue3( 1 ); //Dependant

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '36.08', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testGA_2006a_BiWeekly_Head_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'GA' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 );
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 50 ); //Head
		$pd_obj->setUserValue2( 1 ); //Employee/Spouse
		$pd_obj->setUserValue3( 1 ); //Dependant

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '31.54', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //31.54
	}


	//
	// NJ
	//

	function testNJ_2006a_BiWeekly_RateA_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NJ' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Rate A
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '15.38', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //15.38
	}

	function testNJ_2006a_BiWeekly_RateB_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NJ' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Rate B
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '15.38', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //15.38
	}

	function testNJ_2006a_BiWeekly_RateC_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NJ' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Rate B
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '15.96', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //15.38
	}


	function testNJ_2006a_SemiMonthly_RateA_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NJ' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Rate A
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '160.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //160
	}

	function testNJ_2006a_SemiMonthly_RateD_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NJ' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 40 ); //Rate D
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '132.42', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //132.42
	}

	//
	// NC
	//
	function testNC_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '50.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //50.00
	}

	function testNC_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '51.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //51.00
	}

	function testNC_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '229.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //229.00
	}

	//
	// VA
	//
	function testVA_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'VA' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1( 1 ); //Allowance
		$pd_obj->setUserValue2( 0 ); //Age65 allowance

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '38.97', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //38.97
	}

	function testVA_2006a_BiWeekly_BlindAllowance_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'VA' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1( 1 ); //Allowance
		$pd_obj->setUserValue2( 2 ); //Age65 allowance

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '35.43', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //35.43
	}

	function testVA_2006a_SemiMonthly_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'VA' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1( 4 ); //Allowance
		$pd_obj->setUserValue2( 4 ); //Age65 allowance

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '195.79', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //192.79
	}

	//
	// MA
	//
	/*
		function testMA_2006a_BiWeekly_Single_LowIncome() {
			Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','MA');
			$pd_obj->setDate(strtotime('01-Jan-06'));
			$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

			$pd_obj->setFederalFilingStatus( 10 ); //Single
			$pd_obj->setFederalAllowance( 1 );

			$pd_obj->setStateFilingStatus( 10 ); //Single
			$pd_obj->setStateAllowance( 1 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 1000.00 );

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '45.00' ); //45.00
		}

		function testMA_2006a_BiWeekly_Head_LowIncome() {
			Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','MA');
			$pd_obj->setDate(strtotime('01-Jan-06'));
			$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

			$pd_obj->setFederalFilingStatus( 20 ); //Married
			$pd_obj->setFederalAllowance( 1 );

			$pd_obj->setStateFilingStatus( 20 ); //Head of Household
			$pd_obj->setStateAllowance( 1 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 1000.00 );

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '56.54' ); //56.54
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '40.72' ); //40.72
		}

		function testMA_2006a_BiWeekly_Blind_LowIncome() {
			Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','MA');
			$pd_obj->setDate(strtotime('01-Jan-06'));
			$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

			$pd_obj->setFederalFilingStatus( 20 ); //Married
			$pd_obj->setFederalAllowance( 1 );

			$pd_obj->setStateFilingStatus( 30 ); //Blind
			$pd_obj->setStateAllowance( 1 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 1000.00 );

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '56.54' ); //56.54
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '40.51' ); //40.51
		}

		function testMA_2006a_SemiMonthly_Single_HighIncome() {
			Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','MA');
			$pd_obj->setDate(strtotime('01-Jan-06'));
			$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

			$pd_obj->setFederalFilingStatus( 20 ); //Married
			$pd_obj->setFederalAllowance( 1 );

			$pd_obj->setStateFilingStatus( 10 ); //Regular
			$pd_obj->setStateAllowance( 4 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 4000.00 );

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '601.08' ); //601.08
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '183.94' ); //183.94
		}

		function testMA_2006a_SemiMonthly_Blind_HighIncome() {
			Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','MA');
			$pd_obj->setDate(strtotime('01-Jan-06'));
			$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

			$pd_obj->setFederalFilingStatus( 20 ); //Married
			$pd_obj->setFederalAllowance( 1 );

			$pd_obj->setStateFilingStatus( 30 ); //Regular
			$pd_obj->setStateAllowance( 4 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 4000.00 );

			//var_dump($pd_obj->getArray());

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '601.08' ); //601.08
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '179.08' ); //179.08
		}
	*/
	//
	// IN
	//
	function testIN_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'IN' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1( 1 ); //Allowance
		$pd_obj->setUserValue2( 1 ); //Dependant Allowance

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '30.73', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //30.73
	}


	function testIN_2006a_SemiMonthly_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'IN' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1( 4 ); //Allowance
		$pd_obj->setUserValue2( 4 ); //Dependant allowance

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '121.83', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //121.83
	}

	//
	// IN - Counties
	//
	function testIN_ALL_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'IN', 'ALL' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1( 1 ); //Allowance
		$pd_obj->setUserValue2( 1 ); //Dependant Allowance
		$pd_obj->setUserValue3( 1.25 ); //County Rate

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '30.73', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //30.73
		$this->assertEquals( '11.30', $this->mf( $pd_obj->getDistrictPayPeriodDeductions() ) ); //11.30
	}

	//
	// AZ
	//
	function testAZ_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'AZ' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1( 10 ); //Percent of Federal

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1250.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1250.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '139.04', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //139.04
		$this->assertEquals( '125.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //13.90
	}

	//
	// MD
	//
	function testMD_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MD', 'ALL' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1( 7.68 ); //County Tax Percent - Allegany
		$pd_obj->setUserValue2( 1 ); //Allowances

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '70.89', $this->mf( $pd_obj->getDistrictPayPeriodDeductions() ) ); //70.89
	}

	function testMD_2006a_SemiMonthly_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MD', 'ALL' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setUserValue1( 7.37 ); //County Tax Percent - Dorchester
		$pd_obj->setUserValue2( 8 ); //Allowances

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );  //823.73
		$this->assertEquals( '288.66', $this->mf( $pd_obj->getDistrictPayPeriodDeductions() ) ); //288.66
	}

	//
	// WI
	//
	function testWI_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'WI' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '51.95', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //51.92
	}

	function testWI_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'WI' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '49.11', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //49.08
	}

	function testWI_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'WI' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '245.28', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //245.25
	}

	//
	// MN
	//
	function testMN_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MN' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '43.13', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //43.13
	}

	function testMN_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MN' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '34.05', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //34.05
	}

	function testMN_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MN' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '165.15', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //165.15
	}

	//
	// CO
	//
	function testCO_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '37.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //37.13
	}

	function testCO_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '28.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //27.96
	}

	function testCO_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CO' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '121.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //120.77
	}

	//
	// AL
	//
	/*
		function testAL_2006a_BiWeekly_Single_LowIncome() {
			Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','AL');
			$pd_obj->setDate(strtotime('01-Jan-06'));
			$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

			$pd_obj->setFederalFilingStatus( 10 ); //Single
			$pd_obj->setFederalAllowance( 0 );

			$pd_obj->setStateFilingStatus( 10 ); // State "S"
			$pd_obj->setUserValue2( 1 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 1000 );

			//var_dump($pd_obj->getArray());

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '120.58' ); //120.58
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '36.09' ); //36.09
		}

		function testAL_2006a_BiWeekly_Single_MediumIncome() {
			Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','AL');
			$pd_obj->setDate(strtotime('01-Jan-06'));
			$pd_obj->setAnnualPayPeriods( 12 ); //Monthly
			$pd_obj->setFederalFilingStatus( 10 ); //Single
			$pd_obj->setFederalAllowance( 0 );

			$pd_obj->setStateFilingStatus( 10 ); //Single
			$pd_obj->setUserValue2( 0 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 2083 );

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2083.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '248.70' );
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '75.88' );
		}


		function testAL_2006a_BiWeekly_Married_LowIncome() {
			Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','AL');
			$pd_obj->setDate(strtotime('01-Jan-06'));
			$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

			$pd_obj->setFederalFilingStatus( 10 ); //Single
			$pd_obj->setFederalAllowance( 0 );

			$pd_obj->setStateFilingStatus( 20 ); //Married
			$pd_obj->setUserValue2( 1 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 1000.00 );

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '120.58' ); //120.58
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '28.78' ); //26.86
		}

		function testAL_2006a_BiWeekly_Married_MediumIncome() {
			Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','AL');
			$pd_obj->setDate(strtotime('01-Jan-06'));
			$pd_obj->setAnnualPayPeriods( 12 ); //Monthly
			$pd_obj->setFederalFilingStatus( 10 ); //Single
			$pd_obj->setFederalAllowance( 0 );

			$pd_obj->setStateFilingStatus( 20 ); //Married
			$pd_obj->setUserValue2( 0 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 2083 );

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2083.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '248.70' );
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '60.05' );
		}

		function testAL_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
			Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','AL');
			$pd_obj->setDate(strtotime('01-Jan-06'));
			$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

			$pd_obj->setFederalFilingStatus( 10 ); //Single
			$pd_obj->setFederalAllowance( 1 );

			$pd_obj->setStateFilingStatus( 20 ); //Married
			$pd_obj->setUserValue2( 8 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 4000.00 );

			//var_dump($pd_obj->getArray());

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '137.98' ); //135.90
		}
	*/
	//
	// SC
	//
	function testSC_2006a_BiWeekly_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'SC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 346.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '346.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '13.07', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) ); //13.07
	}

	function testSC_2006a_BiWeekly_LowIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'SC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '58.46', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //58.46
	}

	function testSC_2006a_SemiMonthly_HighIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'SC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '253.21', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //253.21
	}

	//
	// KY
	//
	function testKY_2006a_BiWeekly_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'KY' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 346.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '346.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '8.90', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) ); //8.90
	}

	function testKY_2006a_BiWeekly_LowIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'KY' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '46.53', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //46.53
	}

	function testKY_2006a_SemiMonthly_HighIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'KY' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '220.24', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //220.33
	}

	//
	// OR
	//
	function testOR_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OR' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '73.87', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //73.88
	}

	function testOR_2006a_BiWeekly_Single_LowIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OR' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 3 ); //Should switch to married tax tables.

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '43.41', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //42.82
	}

	function testOR_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OR' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '55.25', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //55.05
	}

	function testOR_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OR' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '270.46', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //266.91
	}

	//
	// OK
	//
	function testOK_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OK' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '46.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //46.00
	}

	function testOK_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OK' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '31.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //31.00
	}

	function testOK_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OK' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '198.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //198.00
	}

	//
	// CT
	//
	function testCT_2006a_BiWeekly_StatusA_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CT' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //"A"

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '20.08', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //20.08
	}

	function testCT_2006a_BiWeekly_StatusB_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CT' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //"B"

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '3.63', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );     //3.63
	}

	function testCT_2006a_BiWeekly_StatusC_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CT' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //"C"

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '0.58', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );     //0.58
	}

	function testCT_2006a_BiWeekly_StatusD_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CT' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 40 ); //"D"

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '42.31', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //42.31
	}

	function testCT_2006a_BiWeekly_StatusE_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CT' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 50 ); //"E"

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );     //0.00
	}

	function testCT_2006a_BiWeekly_StatusF_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CT' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 60 ); //"F"

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '16.96', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //16.75
	}

	function testCT_2006a_BiWeekly_StatusA_MedIncomeA() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CT' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //"A"

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1500.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1500.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '60.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) ); //60.00
	}

	function testCT_2006a_BiWeekly_StatusA_MedIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CT' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //"A"

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 2000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '82.50', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) ); //82.50
	}

	function testCT_2006a_BiWeekly_StatusA_MedIncomeC() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CT' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //"A"

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 2500.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '2500.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '116.67', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) ); //116.67
	}

	function testCT_2006a_BiWeekly_StatusA_HighIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'CT' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //"A"

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '191.67', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) ); //191.67
	}

	//
	// IA
	//
	function testIA_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'IA' );
		$pd_obj->setDate( strtotime( '02-Apr-06' ) ); //02-Apr-06 as rates changed on the 1st.
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '38.09', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //38.09
	}

	function testIA_2006a_BiWeekly_Single_LowIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'IA' );
		$pd_obj->setDate( strtotime( '02-Apr-06' ) ); //02-Apr-06 as rates changed on the 1st.
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '36.55', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //36.55
	}

	function testIA_2006a_BiWeekly_Single_LowIncomeC() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'IA' );
		$pd_obj->setDate( strtotime( '02-Apr-06' ) ); //02-Apr-06 as rates changed on the 1st.
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 2 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '29.03', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //29.03
	}

	function testIA_2006a_BiWeekly_Single_HighIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'IA' );
		$pd_obj->setDate( strtotime( '02-Apr-06' ) ); //02-Apr-06 as rates changed on the 1st.
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 2 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '201.85', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //201.85
	}

	//
	// MS
	//
	function testMS_2006a_BiWeekly_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MS' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '40.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //39.81
	}

	function testMS_2006a_BiWeekly_LowIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MS' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Married - Spouse doesn't work
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '35.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //35.38
	}

	function testMS_2006a_BiWeekly_LowIncomeC() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MS' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 40 ); //HoH
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '38.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //37.69
	}

	function testMS_2006a_SemiMonthly_HighIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MS' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 6000 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '176.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //176.46
	}

	//
	// AR
	//
	function testAR_2006a_BiWeekly_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'AR' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '39.23', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //39.23
	}

	function testAR_2006a_BiWeekly_LowIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'AR' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 3 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '36.92', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //36.92
	}

	function testAR_2006a_BiWeekly_HighIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'AR' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Month

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 3 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '243.75', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //243.75
	}

	//
	// KS
	//
	function testKS_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'KS' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '39.42', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //39.42
	}

	function testKS_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'KS' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '23.89', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //23.89
	}

	function testKS_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'KS' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '154.13', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //154.13
	}

	//
	// NM
	//
	function testNM_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NM' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '36.12', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //36.12
	}

	function testNM_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NM' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '14.83', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //14.71
	}

	function testNM_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NM' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '118.24', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //116.47
	}

	//
	// WV
	//
	function testWV_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'WV' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '36.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //36.00
	}

	function testWV_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'WV' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Two Earners
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '36.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //36.00
	}

	function testWV_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'WV' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '189.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //189
	}

	//
	// NE
	//
	function testNE_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'NE' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '39.97', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //39.97
	}
	/*
		function testNE_2006a_BiWeekly_Married_LowIncome() {
			Debug::text('US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','NE');
			$pd_obj->setDate(strtotime('01-Jan-06'));
			$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

			$pd_obj->setFederalFilingStatus( 10 ); //Single
			$pd_obj->setFederalAllowance( 1 );

			$pd_obj->setStateFilingStatus( 20 ); //Married
			$pd_obj->setStateAllowance( 1 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 1000.00 );

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '101.54' ); //101.54
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '26.08' ); //26.08
		}

		function testNE_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
			Debug::text('US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__,10);

			$pd_obj = new PayrollDeduction('US','NE');
			$pd_obj->setDate(strtotime('01-Jan-06'));
			$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

			$pd_obj->setFederalFilingStatus( 10 ); //Single
			$pd_obj->setFederalAllowance( 1 );

			$pd_obj->setStateFilingStatus( 20 ); //Married
			$pd_obj->setStateAllowance( 8 );

			$pd_obj->setFederalTaxExempt( FALSE );
			$pd_obj->setProvincialTaxExempt( FALSE );

			$pd_obj->setGrossPayPeriodIncome( 4000.00 );

			//var_dump($pd_obj->getArray());

			$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
			$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '823.73' ); //823.73
			$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '195.77' ); //195.77
		}
	*/
	//
	// ID
	//
	function testID_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ID' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '61.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //61.00
	}

	function testID_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ID' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '30.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //30.00
	}

	function testID_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ID' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '182.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //182.00
	}

	//
	// ME
	//
	function testME_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ME' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '54.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //54.00
	}

	function testME_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ME' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '21.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //21.00
	}

	function testME_2006a_BiWeekly_Married_LowIncomeB() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ME' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Married - Two incomes
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '43.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //43.00
	}

	function testME_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ME' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '188.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //188.00
	}

	//
	// HI
	//
	function testHI_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'HI' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '60.92', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //60.92
	}

	function testHI_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'HI' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '46.20', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //46.20
	}

	function testHI_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'HI' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '244.99', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //244.99
	}

	//
	// RI
	//
	function testRI_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'RI' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '33.68', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //33.68
	}

	function testRI_2006a_BiWeekly_Single_HighIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'RI' );
		$pd_obj->setDate( strtotime( '01-Jul-06' ) );
		$pd_obj->setAnnualPayPeriods( 12 ); //Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 5833.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '5833.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1125.83', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '312.71', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );
	}

	function testRI_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'RI' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '23.58', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //23.58
	}

	function testRI_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'RI' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '121.11', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //121.11
	}

	//
	// MT
	//
	function testMT_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MT' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '44.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //44.00
	}

	function testMT_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MT' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '184.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //184.00
	}

	//
	// DE
	//
	function testDE_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'DE' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '34.00', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //34.00
	}

	function testDE_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'DE' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '23.35', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //23.35
	}

	function testDE_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'DE' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Married - Separately
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '167.17', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //167.17
	}

	//
	// ND
	//
	function testND_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ND' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '18.17', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //18.17
	}

	function testND_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ND' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '11.47', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //11.47
	}

	function testND_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ND' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '62.34', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //62.34
	}

	//
	// VT
	//
	function testVT_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'VT' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '32.33', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //32.33
	}

	function testVT_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'VT' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '20.35', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //20.35
	}

	function testVT_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'VT' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '111.60', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //111.60
	}

	//
	// DC
	//
	function testDC_2006a_BiWeekly_Single_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'DC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '56.06', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //56.06
	}

	function testDC_2006a_BiWeekly_Married_LowIncome() {
		Debug::text( 'US - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'DC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Married - Separately
		$pd_obj->setStateAllowance( 1 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( '1000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '101.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //101.54
		$this->assertEquals( '54.18', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );    //54.18
	}

	function testDC_2006a_SemiMonthly_Married_HighIncome_8Allowances() {
		Debug::text( 'US - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'DC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );

		$pd_obj->setStateFilingStatus( 30 ); //Married - Separately
		$pd_obj->setStateAllowance( 8 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '823.73', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //823.73
		$this->assertEquals( '263.41', $this->mf( $pd_obj->getStatePayPeriodDeductions() ) );   //263.41
	}
}

?>
