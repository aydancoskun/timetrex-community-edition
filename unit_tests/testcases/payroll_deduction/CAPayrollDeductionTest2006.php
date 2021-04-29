<?php /** @noinspection PhpMissingDocCommentInspection */
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2020 TimeTrex Software Inc.
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
 * @group CAPayrollDeductionTest2006
 */
class CAPayrollDeductionTest2006 extends PHPUnit_Framework_TestCase {
	public $company_id = null;

	public function setUp() {
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		require_once( Environment::getBasePath() . '/classes/payroll_deduction/PayrollDeduction.class.php' );

		$this->company_id = PRIMARY_COMPANY_ID;

		TTDate::setTimeZone( 'Etc/GMT+8' ); //Force to non-DST timezone. 'PST' isnt actually valid.
		TTi18n::setLocale( 'en_US', LC_ALL, true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		return true;
	}

	public function tearDown() {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	public function mf( $amount ) {
		return Misc::MoneyFormat( $amount, false );
	}

	//
	//
	//
	//	July 07-2006
	//
	//
	//
	function testCA_2006b_BiWeekly_Claim1_LowIncome() {
		Debug::text( 'CA - BiWeekly - Beginning of 2006 01-Jul-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jul-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 8639 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 585.00 );

		//var_dump($pd_obj->getArray());
		$this->assertEquals( '585.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '31.04', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
	}

	function testCA_2006b_BiWeekly_Claim1_MedIncome() {
		Debug::text( 'CA - BiWeekly - Beginning of 2006 01-Jul-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jul-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 8639 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2399.00 );

		$this->assertEquals( '2399.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '366.60', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
	}

	function testCA_2006b_BiWeekly_Claim1_HighIncome() {
		Debug::text( 'CA - BiWeekly - Beginning of 2006 01-Jul-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jul-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 8639 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 7167.00 );

		$this->assertEquals( '7167.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1668.86', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
	}

	function testCA_2006b_BiWeekly_Claim5_LowIncome() {
		Debug::text( 'CA - BiWeekly - Beginning of 2006 01-Jul-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jul-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 15103.50 ); //Claim Code5
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
		$this->assertEquals( '25.72', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Two Penny off...
	}

	function testCA_2006b_BiWeekly_Claim5_HighIncome() {
		Debug::text( 'CA - BiWeekly - Beginning of 2006 01-Jul-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jul-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 15103.50 ); //Claim Code5
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 7167.00 );

		$this->assertEquals( '7167.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1630.32', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Two Penny off...
	}

	function testCA_2006b_SemiMonthly_Claim1_LowIncome() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2006 01-Jul-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jul-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 8639 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 612.00 );

		$this->assertEquals( '612.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '30.49', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //One penny off
	}

	function testCA_2006b_SemiMonthly_Claim1_MedIncome() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2006 01-Jul-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jul-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 8639 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2711.00 );

		$this->assertEquals( '2711.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '421.81', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //One penny off
	}

	function testCA_2006b_SemiMonthly_Claim1_HighIncome() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2006 01-Jul-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jul-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 8639 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 7746.00 );

		$this->assertEquals( '7746.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1802.64', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //One penny off
	}

	//
	//	January 01-2006
	//
	//  In July 2006 they introduced K4, which makes it so these tests all fail.
	//
	//
	//
	function testCA_2006a_BiWeekly_Claim1_LowIncome() {
		Debug::text( 'CA - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9039 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 587.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '587.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '30.90', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
	}

	function testCA_2006a_BiWeekly_Claim1_MedIncome() {
		Debug::text( 'CA - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9039 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2395.00 );

		$this->assertEquals( '2395.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '361.60', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
	}

	function testCA_2006a_BiWeekly_Claim1_HighIncome() {
		Debug::text( 'CA - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9039 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 7163.00 );

		$this->assertEquals( '7163.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1663.55', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
	}

	function testCA_2006a_BiWeekly_Claim5_LowIncome() {
		Debug::text( 'CA - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 15503.50 ); //Claim Code5
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 811.00 );

		$this->assertEquals( '811.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '24.91', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //One Penny off...
	}

	function testCA_2006a_BiWeekly_Claim5_HighIncome() {
		Debug::text( 'CA - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 15503.50 ); //Claim Code5
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 7163.00 );

		$this->assertEquals( '7163.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1626.25', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //One Penny off...
	}

	function testCA_2006a_SemiMonthly_Claim1_LowIncome() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 9039 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 611.00 );

		$this->assertEquals( '611.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '29.99', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //One penny off
	}

	function testCA_2006a_SemiMonthly_Claim1_MedIncome() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 9039 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2706.00 );

		$this->assertEquals( '2706.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '416.24', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //One penny off
	}

	function testCA_2006a_SemiMonthly_Claim1_HighIncome() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 9039 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 7741.00 );

		$this->assertEquals( '7741.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1796.69', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //One penny off
	}

	//
	// BC - Provincial Taxes
	//
	function testBC_2006a_BiWeekly_Claim1_MedIncome() {
		Debug::text( 'BC - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9039.00 );
		$pd_obj->setProvincialTotalClaimAmount( 8858.00 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2758.00 );


		$this->assertEquals( '2758.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '189.47', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) ); //189.45
	}

	function testBC_2006a_BiWeekly_Claim5_MedIncome() {
		Debug::text( 'BC - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9039.00 );
		$pd_obj->setProvincialTotalClaimAmount( 15833.50 ); //15833.50
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2758.00 );

		$this->assertEquals( '2758.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '173.24', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) ); //173.25
	}

	//
	// AB - Provincial Taxes
	//
	function testAB_2006a_BiWeekly_Claim1_LowIncome() {
		Debug::text( 'AB - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		//$pd_obj = new PayrollDeduction();
		//$pd_obj->setCountry('CA');
		//$pd_obj->setProvince('BC');
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9039.00 );
		$pd_obj->setProvincialTotalClaimAmount( 14799.00 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1428.00 );

		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '1428.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '76.81', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testAB_2006a_BiWeekly_Claim5_LowIncome() {
		Debug::text( 'AB - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		//$pd_obj = new PayrollDeduction();
		//$pd_obj->setCountry('CA');
		//$pd_obj->setProvince('BC');
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9039.00 );
		$pd_obj->setProvincialTotalClaimAmount( 22429.00 ); //22429
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1428.00 );

		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '1428.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '47.46', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	//
	// SK - Provincial Taxes
	//
	function testSK_2006a_BiWeekly_Claim1_MedIncome() {
		Debug::text( 'SK - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'SK' );
		//$pd_obj = new PayrollDeduction();
		//$pd_obj->setCountry('CA');
		//$pd_obj->setProvince('BC');
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9039.00 );
		$pd_obj->setProvincialTotalClaimAmount( 8589.00 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2832.00 );


		$this->assertEquals( '2832.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '291.73', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) ); //291.75
	}

	function testSK_2006a_SemiMonthly_Claim1_MedIncome() {
		Debug::text( 'SK - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'SK' );
		//$pd_obj = new PayrollDeduction();
		//$pd_obj->setCountry('CA');
		//$pd_obj->setProvince('BC');
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 9039.00 );
		$pd_obj->setProvincialTotalClaimAmount( 8589.00 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2816.00 );


		$this->assertEquals( '2816.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '283.28', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) ); //283.30
	}

	//
	// MB - Provincial Taxes
	//
	function testMB_2006a_BiWeekly_Claim1_MedIncome() {
		Debug::text( 'MB - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'MB' );
		//$pd_obj = new PayrollDeduction();
		//$pd_obj->setCountry('CA');
		//$pd_obj->setProvince('BC');
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9039.00 );
		$pd_obj->setProvincialTotalClaimAmount( 7734.00 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2750.00 );


		$this->assertEquals( '2750.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '306.97', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) ); //306.95
	}

	function testMB_2006a_SemiMonthly_Claim1_MedIncome() {
		Debug::text( 'MB - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'MB' );
		//$pd_obj = new PayrollDeduction();
		//$pd_obj->setCountry('CA');
		//$pd_obj->setProvince('BC');
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 9039.00 );
		$pd_obj->setProvincialTotalClaimAmount( 7734.00 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2702.00 );


		$this->assertEquals( '2702.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '284.57', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) ); //284.55
	}

	//
	// ON - Provincial Taxes
	//
	function testON_2006a_BiWeekly_Claim1_MedIncome() {
		Debug::text( 'ON - BiWeekly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'ON' );
		//$pd_obj = new PayrollDeduction();
		//$pd_obj->setCountry('CA');
		//$pd_obj->setProvince('BC');
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9039.00 );
		$pd_obj->setProvincialTotalClaimAmount( 8377.00 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2740.00 );


		$this->assertEquals( '2740.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '214.13', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) ); //214.15
	}

	function testON_2006a_SemiMonthly_Claim1_MedIncome() {
		Debug::text( 'ON - SemiMonthly - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'ON' );
		//$pd_obj = new PayrollDeduction();
		//$pd_obj->setCountry('CA');
		//$pd_obj->setProvince('BC');
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 9039.00 );
		$pd_obj->setProvincialTotalClaimAmount( 8377.00 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2820.00 );


		$this->assertEquals( '2820.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		Debug::text( 'Prov Ded: ' . $pd_obj->getProvincialPayPeriodDeductions(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertEquals( '213.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) ); //214.00
	}

	//
	// CPP / EI
	//
	function testCA_2006a_BiWeekly_CPP_LowIncome() {
		Debug::text( 'CA - BiWeekly - CPP - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9039 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 587.00 );

		$this->assertEquals( '587.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '22.39', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '22.39', $this->mf( $pd_obj->getEmployerCPP() ) );
	}

	function testCA_2006a_SemiMonthly_CPP_LowIncome() {
		Debug::text( 'CA - BiWeekly - CPP - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 9039 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 587.00 );

		$this->assertEquals( '587.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '21.84', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '21.84', $this->mf( $pd_obj->getEmployerCPP() ) );
	}


	function testCA_2006a_SemiMonthly_MAXCPP_LowIncome() {
		Debug::text( 'CA - BiWeekly - MAXCPP - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 9039 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 1909.70 ); //1910.70
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 587.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '587.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getEmployerCPP() ) );
	}

	function testCA_2006a_EI_LowIncome() {
		Debug::text( 'CA - EI - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9039 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 587.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '587.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '10.98', $this->mf( $pd_obj->getEmployeeEI() ) );
		$this->assertEquals( '15.37', $this->mf( $pd_obj->getEmployerEI() ) );
	}

	function testCA_2006a_MAXEI_LowIncome() {
		Debug::text( 'CA - MAXEI - Beginning of 2006 01-Jan-06: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-06' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 9039 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 728.30 ); //729.30 - 1.00

		$pd_obj->setGrossPayPeriodIncome( 587.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '587.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getEmployeeEI() ) );
		$this->assertEquals( '1.40', $this->mf( $pd_obj->getEmployerEI() ) );
	}
}

?>