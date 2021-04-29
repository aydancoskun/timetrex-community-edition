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
 * @group CAPayrollDeductionTest2014
 */
class CAPayrollDeductionTest2014 extends PHPUnit_Framework_TestCase {
	public $company_id = null;

	public function setUp() {
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		require_once( Environment::getBasePath() . '/classes/payroll_deduction/PayrollDeduction.class.php' );

		$this->tax_table_file = dirname( __FILE__ ) . '/CAPayrollDeductionTest2014.csv';

		$this->company_id = PRIMARY_COMPANY_ID;

		TTDate::setTimeZone( 'Etc/GMT+8' ); //Force to non-DST timezone. 'PST' isnt actually valid.

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
	// January 2014
	//
	function testCSVFile() {
		$this->assertEquals( true, file_exists( $this->tax_table_file ) );

		$test_rows = Misc::parseCSV( $this->tax_table_file, true );

		$total_rows = ( count( $test_rows ) + 1 );
		$i = 2;
		foreach ( $test_rows as $row ) {
			//Debug::text('Province: '. $row['province'] .' Income: '. $row['gross_income'], __FILE__, __LINE__, __METHOD__, 10);
			if ( isset( $row['gross_income'] ) && isset( $row['low_income'] ) && isset( $row['high_income'] )
					&& $row['gross_income'] == '' && $row['low_income'] != '' && $row['high_income'] != '' ) {
				$row['gross_income'] = ( $row['low_income'] + ( ( $row['high_income'] - $row['low_income'] ) / 2 ) );
			}
			if ( $row['country'] != '' && $row['gross_income'] != '' ) {
				//echo $i.'/'.$total_rows.'. Testing Province: '. $row['province'] .' Income: '. $row['gross_income'] ."\n";

				$pd_obj = new PayrollDeduction( $row['country'], $row['province'] );
				$pd_obj->setDate( strtotime( $row['date'] ) );
				$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
				$pd_obj->setAnnualPayPeriods( $row['pay_periods'] );

				$pd_obj->setFederalTotalClaimAmount( $row['federal_claim'] ); //Amount from 2005, Should use amount from 2007 automatically.
				$pd_obj->setProvincialTotalClaimAmount( $row['provincial_claim'] );
				//$pd_obj->setWCBRate( 0.18 );

				$pd_obj->setEIExempt( false );
				$pd_obj->setCPPExempt( false );

				$pd_obj->setFederalTaxExempt( false );
				$pd_obj->setProvincialTaxExempt( false );

				$pd_obj->setYearToDateCPPContribution( 0 );
				$pd_obj->setYearToDateEIContribution( 0 );

				$pd_obj->setGrossPayPeriodIncome( $this->mf( $row['gross_income'] ) );

				//var_dump($pd_obj->getArray());

				$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), $this->mf( $row['gross_income'] ) );
				if ( $row['federal_deduction'] != '' ) {
					$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), $this->mf( $row['federal_deduction'] ) );
				}
				if ( $row['provincial_deduction'] != '' ) {
					$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), $this->mf( $row['provincial_deduction'] ) );
				}
			}

			$i++;
		}

		//Make sure all rows are tested.
		$this->assertEquals( $total_rows, ( $i - 1 ) );
	}

	function testCA_ClaimAmountAdjustmentA() {
		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2013' ) );
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 10276 );

		$this->assertEquals( '11038', $this->mf( $pd_obj->getFederalTotalClaimAmount() ) );
		$this->assertEquals( '10276', $this->mf( $pd_obj->getProvincialTotalClaimAmount() ) );
	}

	//Test claim amount set lower than the minimum.
	function testCA_ClaimAmountAdjustmentB() {
		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2013' ) );
		$pd_obj->setFederalTotalClaimAmount( 10000 );
		$pd_obj->setProvincialTotalClaimAmount( 10000 );

		$this->assertEquals( '11038', $this->mf( $pd_obj->getFederalTotalClaimAmount() ) );
		$this->assertEquals( '10276', $this->mf( $pd_obj->getProvincialTotalClaimAmount() ) );
	}

	//Test claim amount at 0.
	function testCA_ClaimAmountAdjustmentC() {
		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2013' ) );
		$pd_obj->setFederalTotalClaimAmount( 0 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );

		$this->assertEquals( '0', $this->mf( $pd_obj->getFederalTotalClaimAmount() ) );
		$this->assertEquals( '0', $this->mf( $pd_obj->getProvincialTotalClaimAmount() ) );
	}

	//Test claim amount higher than minimum.
	function testCA_ClaimAmountAdjustmentD() {
		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2013' ) );
		$pd_obj->setFederalTotalClaimAmount( 11040 );
		$pd_obj->setProvincialTotalClaimAmount( 10280 );

		$this->assertEquals( '11040', $this->mf( $pd_obj->getFederalTotalClaimAmount() ) );
		$this->assertEquals( '10280', $this->mf( $pd_obj->getProvincialTotalClaimAmount() ) );
	}

	//Test claim amount at next years values, should be reverted back to current year.
	function testCA_ClaimAmountAdjustmentE() {
		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2013' ) );
		$pd_obj->setFederalTotalClaimAmount( 11138 );
		$pd_obj->setProvincialTotalClaimAmount( 9869 );

		$this->assertEquals( '11038', $this->mf( $pd_obj->getFederalTotalClaimAmount() ) );
		$this->assertEquals( '10276', $this->mf( $pd_obj->getProvincialTotalClaimAmount() ) );
	}

	function testCA_2014a_Example() {
		Debug::text( 'CA - Example Beginning of 01-Jan-2014: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		$pd_obj->setDate( strtotime( '01-Jan-2014' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 52 );

		$pd_obj->setFederalTotalClaimAmount( 28964.50 );
		$pd_obj->setProvincialTotalClaimAmount( 17593 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1100 );

		$this->assertEquals( '1100', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '86.39', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '69.37', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2014a_Example1() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2014: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		$pd_obj->setDate( strtotime( '01-Jan-2014' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );

		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 17593 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1800 );

		$this->assertEquals( '1800', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '100.41', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '20.92', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2014a_Example2() {
		Debug::text( 'CA - Example2 - Beginning of 01-Jan-2014: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		$pd_obj->setDate( strtotime( '01-Jan-2014' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 17593 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2300 );

		$this->assertEquals( '2300', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '297.63', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '148.75', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2014a_Example3() {
		Debug::text( 'CA - Example3 - Beginning of 01-Jan-2014: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		$pd_obj->setDate( strtotime( '01-Jan-2014' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 52 );

		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 17593 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2500 );

		$this->assertEquals( '2500', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '478.20', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '209.37', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2014a_Example4() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2014: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2014' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 10276 );
		$pd_obj->setWCBRate( 0 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1560 );

		$this->assertEquals( '1560', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '148.26', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '57.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2014a_GovExample1() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2014: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		$pd_obj->setDate( strtotime( '01-Jan-2014' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 52 );

		$pd_obj->setFederalTotalClaimAmount( 11138 );
		$pd_obj->setProvincialTotalClaimAmount( 17787 );
		$pd_obj->setWCBRate( 0 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1030 ); //Take Gross income minus RPP and Union Dues.

		$this->assertEquals( '1030', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '122.41', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '62.37', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2014a_GovExample2() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2014: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2014' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 52 );

		$pd_obj->setFederalTotalClaimAmount( 11138 );
		$pd_obj->setProvincialTotalClaimAmount( 9869 );
		$pd_obj->setWCBRate( 0 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1030 ); //Take Gross income minus RPP and Union Dues.

		$this->assertEquals( '1030', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '122.41', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '47.36', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2014a_GovExample3() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2014: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'ON' );
		$pd_obj->setDate( strtotime( '01-Jan-2014' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 52 );

		$pd_obj->setFederalTotalClaimAmount( 11138 );
		$pd_obj->setProvincialTotalClaimAmount( 9670 );
		$pd_obj->setWCBRate( 0 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1030 ); //Take Gross income minus RPP and Union Dues.

		$this->assertEquals( '1030', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '122.41', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '61.51', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2014a_GovExample4() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2014: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'PE' );
		$pd_obj->setDate( strtotime( '01-Jan-2014' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 52 );

		$pd_obj->setFederalTotalClaimAmount( 11138 );
		$pd_obj->setProvincialTotalClaimAmount( 7708 );
		$pd_obj->setWCBRate( 0 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1030 ); //Take Gross income minus RPP and Union Dues.

		$this->assertEquals( '1030', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '122.41', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '96.72', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	//
	// CPP/ EI
	//
	function testCA_2014a_BiWeekly_CPP_LowIncome() {
		Debug::text( 'CA - BiWeekly - CPP - Beginning of 01-Jan-2014: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2014' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11038 );
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

	function testCA_2014a_SemiMonthly_CPP_LowIncome() {
		Debug::text( 'CA - BiWeekly - CPP - Beginning of 01-Jan-2014: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2014' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 11038 );
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

	function testCA_2014a_SemiMonthly_MAXCPP_LowIncome() {
		Debug::text( 'CA - BiWeekly - MAXCPP - Beginning of 2014 01-Jan-2014: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2014' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 2424.50 ); //2425.50 - 1.00
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 587.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '587.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getEmployerCPP() ) );
	}

	function testCA_2014a_EI_LowIncome() {
		Debug::text( 'CA - EI - Beginning of 01-Jan-2014: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2014' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11038 );
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
		$this->assertEquals( '11.05', $this->mf( $pd_obj->getEmployeeEI() ) );
		$this->assertEquals( '15.47', $this->mf( $pd_obj->getEmployerEI() ) );
	}

	function testCA_2014a_MAXEI_LowIncome() {
		Debug::text( 'CA - MAXEI - Beginning of 2006 01-Jan-2014: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2014' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 912.68 ); //913.68 - 1.00

		$pd_obj->setGrossPayPeriodIncome( 587.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '587.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getEmployeeEI() ) );
		$this->assertEquals( '1.40', $this->mf( $pd_obj->getEmployerEI() ) );
	}
}

?>