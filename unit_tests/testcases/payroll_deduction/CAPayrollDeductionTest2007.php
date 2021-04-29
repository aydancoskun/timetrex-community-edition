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
 * @group CAPayrollDeductionTest2007
 */
class CAPayrollDeductionTest2007 extends PHPUnit\Framework\TestCase {
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
	// January 2007
	//
	function testCA_2007a_BasicClaimAmount() {
		Debug::text( 'CA - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		//$pd_obj->setDate(strtotime('01-Jan-07 12:00:00 PST'));
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 8128 ); //Amount from 2005, Should use amount from 2007 automatically.
		$pd_obj->setProvincialTotalClaimAmount( 8858 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2770.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '2770.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '441.09', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '188.28', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2007a_BasicClaimAmountB() {
		Debug::text( 'CA - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 9027 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2770.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '2770.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '441.09', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '188.28', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2007a_BiWeekly_Claim1_LowIncome() {
		Debug::text( 'CA - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 589.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '589.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '26.97', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
	}

	function testCA_2007a_BiWeekly_Claim1_MedIncome() {
		Debug::text( 'CA - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2407.00 );

		$this->assertEquals( '2407.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '361.23', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
	}

	function testCA_2007a_BiWeekly_Claim1_HighIncome() {
		Debug::text( 'CA - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 7199.00 );

		$this->assertEquals( '7199.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1665.56', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
	}

	function testCA_2007a_BiWeekly_Claim5_LowIncome() {
		Debug::text( 'CA - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 15537.00 ); //Claim Code5
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 815.00 );

		$this->assertEquals( '815.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '20.24', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //One Penny off...
	}

	function testCA_2007a_BiWeekly_Claim5_HighIncome() {
		Debug::text( 'CA - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 15537.00 ); //Claim Code5
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 7199.00 );

		$this->assertEquals( '7199.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1626.16', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //One Penny off...
	}

	function testCA_2007a_SemiMonthly_Claim1_LowIncome() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 615.00 );

		$this->assertEquals( '615.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '25.88', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //One penny off
	}

	function testCA_2007a_SemiMonthly_Claim1_MedIncome() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2720.00 );

		$this->assertEquals( '2720.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '416.07', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //One penny off
	}

	function testCA_2007a_SemiMonthly_Claim1_HighIncome() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2006 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 7781.00 );

		$this->assertEquals( '7781.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1799.16', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //One penny off
	}

	//
	// CPP/ EI
	//
	function testCA_2007a_BiWeekly_CPP_LowIncome() {
		Debug::text( 'CA - BiWeekly - CPP - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 585.32 );

		$this->assertEquals( '585.32', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '22.31', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '22.31', $this->mf( $pd_obj->getEmployerCPP() ) );
	}

	function testCA_2007a_SemiMonthly_CPP_LowIncome() {
		Debug::text( 'CA - BiWeekly - CPP - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 585.23 );

		$this->assertEquals( '585.23', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '21.75', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '21.75', $this->mf( $pd_obj->getEmployerCPP() ) );
	}

	function testCA_2007a_EI_LowIncome() {
		Debug::text( 'CA - EI - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 587.76 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '587.76', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '10.58', $this->mf( $pd_obj->getEmployeeEI() ) );
		$this->assertEquals( '14.81', $this->mf( $pd_obj->getEmployerEI() ) );
	}

	//
	// BC - Provincial Taxes
	//
	function testBC_2007b_BiWeekly_Claim1_MedIncome() {
		Debug::text( 'BC - BiWeekly - Beginning of 2007 01-Jul-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jul-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 9027.00 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2774.00 );


		$this->assertEquals( '2774.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '167.89', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testBC_2007b_BiWeekly_Claim5_MedIncome() {
		Debug::text( 'BC - BiWeekly - Beginning of 2007 01-Jul-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jul-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 16135.50 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2774.00 );

		$this->assertEquals( '2774.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '153.26', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testBC_2007a_BiWeekly_Claim1_MedIncome() {
		Debug::text( 'BC - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 9027.00 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2770.00 );


		$this->assertEquals( '2770.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '188.28', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testBC_2007a_BiWeekly_Claim5_MedIncome() {
		Debug::text( 'BC - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 16135.50 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2770.00 );

		$this->assertEquals( '2770.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '171.74', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	//
	// AB - Provincial Taxes
	//
	function testAB_2007a_BiWeekly_Claim1_LowIncome() {
		Debug::text( 'AB - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 15535 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1422.00 );

		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '1422.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '73.52', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testAB_2007a_BiWeekly_Claim5_LowIncome() {
		Debug::text( 'AB - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 23338 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1422.00 );

		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '1422.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '43.51', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	//
	// SK - Provincial Taxes
	//
	function testSK_2007a_BiWeekly_Claim1_MedIncome() {
		Debug::text( 'SK - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'SK' );
		//$pd_obj = new PayrollDeduction();
		//$pd_obj->setCountry('CA');
		//$pd_obj->setProvince('BC');
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 8778.00 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2840.00 );


		$this->assertEquals( '2840.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '291.06', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testSK_2007a_SemiMonthly_Claim1_MedIncome() {
		Debug::text( 'SK - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'SK' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 8778.00 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2824.00 );


		$this->assertEquals( '2824.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '282.47', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	//
	// MB - Provincial Taxes
	//
	function testMB_2007a_BiWeekly_Claim1_MedIncome() {
		Debug::text( 'MB - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'MB' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 7834 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2754.00 );


		$this->assertEquals( '2754.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '300.34', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testMB_2007a_SemiMonthly_Claim1_MedIncome() {
		Debug::text( 'MB - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'MB' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 7834 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2705.00 );


		$this->assertEquals( '2705.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '277.05', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	//
	// ON - Provincial Taxes
	//
	function testON_2007a_BiWeekly_Claim1_MedIncome() {
		Debug::text( 'ON - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'ON' );
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 8553 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2749.00 );


		$this->assertEquals( '2749.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '211.60', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testON_2007a_SemiMonthly_Claim1_MedIncome() {
		Debug::text( 'ON - SemiMonthly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'ON' );
		//$pd_obj = new PayrollDeduction();
		//$pd_obj->setCountry('CA');
		//$pd_obj->setProvince('BC');
		$pd_obj->setDate( strtotime( '01-Jan-07' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 8929 );
		$pd_obj->setProvincialTotalClaimAmount( 8553 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2830.00 );


		$this->assertEquals( '2830.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '212.50', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) ); //214.00
	}
}

?>