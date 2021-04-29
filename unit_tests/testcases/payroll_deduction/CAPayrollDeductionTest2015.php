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
 * @group CAPayrollDeductionTest2015
 */
class CAPayrollDeductionTest2015 extends PHPUnit\Framework\TestCase {
	public $company_id = null;

	public function setUp(): void {
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		require_once( Environment::getBasePath() . '/classes/payroll_deduction/PayrollDeduction.class.php' );

		$this->tax_table_file = dirname( __FILE__ ) . '/CAPayrollDeductionTest2015.csv';

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
	// January 2015
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

	function testCA_2015a_Example() {
		Debug::text( 'CA - Example Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 52 );

		$pd_obj->setFederalTotalClaimAmount( 29721.00 );
		$pd_obj->setProvincialTotalClaimAmount( 17593 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1100 );

		$this->assertEquals( '1100.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '82.95', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '68.41', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2015a_Example1() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );

		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 18214 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1800 );

		$this->assertEquals( '1800.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '97.81', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '17.37', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2015a_Example2() {
		Debug::text( 'CA - Example2 - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 18214 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2300 );

		$this->assertEquals( '2300.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '294.02', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '146.83', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2015a_Example3() {
		Debug::text( 'CA - Example3 - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 52 );

		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 18214 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2500 );

		$this->assertEquals( '2500.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '475.24', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '208.41', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2015a_Example4() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 9938 );
		$pd_obj->setWCBRate( 0 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1560 );

		$this->assertEquals( '1560.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '147.06', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '57.26', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2015a_GovExample1() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 52 );

		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 18214 );
		$pd_obj->setWCBRate( 0 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1030 ); //Take Gross income minus RPP and Union Dues.

		$this->assertEquals( '1030.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '120.61', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '61.42', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2015a_GovExample2() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 52 );

		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 9938 );
		$pd_obj->setWCBRate( 0 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1030 ); //Take Gross income minus RPP and Union Dues.

		$this->assertEquals( '1030.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '120.61', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '47.09', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2015a_GovExample3() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'ON' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 52 );

		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 9863 );
		$pd_obj->setWCBRate( 0 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1030 ); //Take Gross income minus RPP and Union Dues.

		$this->assertEquals( '1030.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '120.61', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '60.63', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2015a_GovExample4() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'PE' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 52 );

		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 7708 );
		$pd_obj->setWCBRate( 0 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1030 ); //Take Gross income minus RPP and Union Dues.

		$this->assertEquals( '1030.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '120.61', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '96.59', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	//
	// CPP/ EI
	//
	function testCA_2015a_BiWeekly_CPP_LowIncome() {
		Debug::text( 'CA - BiWeekly - CPP - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
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

	function testCA_2015a_SemiMonthly_CPP_LowIncome() {
		Debug::text( 'CA - BiWeekly - CPP - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
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

	function testCA_2015a_SemiMonthly_MAXCPP_LowIncome() {
		Debug::text( 'CA - BiWeekly - MAXCPP - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 2478.95 ); //2479.95 - 1.00
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 587.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '587.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getEmployerCPP() ) );
	}

	function testCA_2015a_EI_LowIncome() {
		Debug::text( 'CA - EI - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
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

	function testCA_2015a_MAXEI_LowIncome() {
		Debug::text( 'CA - MAXEI - Beginning of 2006 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
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
		$pd_obj->setYearToDateEIContribution( 929.60 ); //930.60 - 1.00

		$pd_obj->setGrossPayPeriodIncome( 587.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '587.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getEmployeeEI() ) );
		$this->assertEquals( '1.40', $this->mf( $pd_obj->getEmployerEI() ) );
	}

	function testCA_2015_Federal_Periodic_FormulaA() {
		Debug::text( 'US - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP1
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '640.71', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP2
		$pd_obj->setDate( strtotime( '01-Feb-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '640.71', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP3
		$pd_obj->setDate( strtotime( '01-Mar-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '640.71', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP4
		$pd_obj->setDate( strtotime( '01-Apr-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '640.71', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP5
		$pd_obj->setDate( strtotime( '01-May-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '640.71', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP6
		$pd_obj->setDate( strtotime( '01-Jun-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '640.71', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP7
		$pd_obj->setDate( strtotime( '01-Jul-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '640.71', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP8
		$pd_obj->setDate( strtotime( '01-Aug-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '640.71', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP9
		$pd_obj->setDate( strtotime( '01-Sep-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '640.71', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP10
		$pd_obj->setDate( strtotime( '01-Oct-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '640.71', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP11
		$pd_obj->setDate( strtotime( '01-Nov-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '640.71', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP12
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '640.71', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		$this->assertEquals( 60000, $ytd_gross_income );
		$this->assertEquals( 7688.4675, $ytd_deduction );

		//Actual Income/Deductions for the year.
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 60000 );
		$this->assertEquals( '60000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '7688.47', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
	}

	function testCA_2015_Federal_NonPeriodic_FormulaA() {
		Debug::text( 'US - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 20 ); //NonPeriodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( true ); //EI/CPP exempt so we don't have to track YTD amounts.
		$pd_obj->setCPPExempt( true );
		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP1
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '683.34', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP2
		$pd_obj->setDate( strtotime( '01-Feb-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '683.34', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP3
		$pd_obj->setDate( strtotime( '01-Mar-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '683.34', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP4
		$pd_obj->setDate( strtotime( '01-Apr-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '683.34', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP5
		$pd_obj->setDate( strtotime( '01-May-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '683.34', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP6
		$pd_obj->setDate( strtotime( '01-Jun-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '683.34', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP7
		$pd_obj->setDate( strtotime( '01-Jul-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '683.34', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP8
		$pd_obj->setDate( strtotime( '01-Aug-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '683.34', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP9
		$pd_obj->setDate( strtotime( '01-Sep-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '683.34', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP10
		$pd_obj->setDate( strtotime( '01-Oct-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '683.34', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP11
		$pd_obj->setDate( strtotime( '01-Nov-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '683.34', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP12
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '683.34', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		$this->assertEquals( 60000, $ytd_gross_income );
		$this->assertEquals( 8200.05, $ytd_deduction );

		//Actual Income/Deductions for the year.
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 60000 );
		$this->assertEquals( '60000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '8200.05', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
	}

	function testCA_2015_Federal_Periodic_FormulaB() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( false ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP1
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '683.34', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP2
		$pd_obj->setDate( strtotime( '01-Feb-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '144.09', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP3
		$pd_obj->setDate( strtotime( '01-Mar-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '683.34', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP4
		$pd_obj->setDate( strtotime( '01-Apr-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '144.09', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP5
		$pd_obj->setDate( strtotime( '01-May-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '683.34', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP6
		$pd_obj->setDate( strtotime( '01-Jun-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '144.09', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP7
		$pd_obj->setDate( strtotime( '01-Jul-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '683.34', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP8
		$pd_obj->setDate( strtotime( '01-Aug-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '144.09', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP9
		$pd_obj->setDate( strtotime( '01-Sep-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '683.34', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP10
		$pd_obj->setDate( strtotime( '01-Oct-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '144.09', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP11
		$pd_obj->setDate( strtotime( '01-Nov-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '683.34', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP12
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '144.09', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		$this->assertEquals( 42000, $ytd_gross_income );
		$this->assertEquals( 4964.55, $ytd_deduction );

		//Actual Income/Deductions for the year.
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 42000 );
		$this->assertEquals( '42000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '4429.05', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
	}

	function testCA_2015_Federal_NonPeriodic_FormulaB() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( false ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 20 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP1
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '683.34', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP2
		$pd_obj->setDate( strtotime( '01-Feb-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '54.84', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP3
		$pd_obj->setDate( strtotime( '01-Mar-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '651.84', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP4
		$pd_obj->setDate( strtotime( '01-Apr-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '86.34', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP5
		$pd_obj->setDate( strtotime( '01-May-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '620.34', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP6
		$pd_obj->setDate( strtotime( '01-Jun-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '117.84', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP7
		$pd_obj->setDate( strtotime( '01-Jul-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '594.09', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP8
		$pd_obj->setDate( strtotime( '01-Aug-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '144.09', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP9
		$pd_obj->setDate( strtotime( '01-Sep-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '594.09', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP10
		$pd_obj->setDate( strtotime( '01-Oct-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '144.09', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP11
		$pd_obj->setDate( strtotime( '01-Nov-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '594.09', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP12
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '144.09', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		$this->assertEquals( 42000, $ytd_gross_income );
		$this->assertEquals( 4429.05, $ytd_deduction );

		//Actual Income/Deductions for the year.
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 42000 );
		$this->assertEquals( '42000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '4429.05', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
	}

	function testCA_2015_Federal_NonPeriodic_FormulaC() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		//
		// Has mostly periodic pay periods, then a few out-of-cycle ones.
		//

		//
		// Full test with EI/CPP YTD amounts.
		//

		$pd_obj = new PayrollDeduction( 'CA', 'PE' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;
		$ytd_cpp = 0;
		$ytd_ei = 0;

		//PP1
		$pd_obj->setDate( strtotime( '08-Jan-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP2
		$pd_obj->setDate( strtotime( '22-Jan-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP3
		$pd_obj->setDate( strtotime( '05-Feb-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP4
		$pd_obj->setDate( strtotime( '19-Feb-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP5
		$pd_obj->setDate( strtotime( '05-Mar-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP6
		$pd_obj->setDate( strtotime( '19-Mar-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP7
		$pd_obj->setDate( strtotime( '02-Apr-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP8
		$pd_obj->setDate( strtotime( '16-Apr-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP9
		$pd_obj->setDate( strtotime( '30-Apr-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP10
		$pd_obj->setDate( strtotime( '14-May-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP11
		$pd_obj->setDate( strtotime( '28-May-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP12
		$pd_obj->setDate( strtotime( '11-Jun-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP13
		$pd_obj->setDate( strtotime( '25-Jun-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP14
		$pd_obj->setDate( strtotime( '09-Jul-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP15
		$pd_obj->setDate( strtotime( '23-Jul-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP16
		$pd_obj->setDate( strtotime( '06-Aug-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP17
		$pd_obj->setDate( strtotime( '20-Aug-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP18
		$pd_obj->setDate( strtotime( '03-Sep-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP19
		$pd_obj->setDate( strtotime( '17-Sep-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP20
		$pd_obj->setDate( strtotime( '01-Oct-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP21
		$pd_obj->setDate( strtotime( '15-Oct-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP22
		$pd_obj->setDate( strtotime( '29-Oct-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP23
		$pd_obj->setDate( strtotime( '12-Nov-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP24
		$pd_obj->setDate( strtotime( '26-Nov-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP25 (A)
		$pd_obj->setFormulaType( 10 ); //NonPeriodic
		$pd_obj->setDate( strtotime( '10-Dec-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		//$current_pay_period++; //(Multiple runs in this pay period)
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP25 (B)
		$pd_obj->setFormulaType( 20 ); //NonPeriodic
		$pd_obj->setDate( strtotime( '10-Dec-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 2 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 150.00 );
		$this->assertEquals( '150.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '20.96', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Was: 31.19, but probably should be: 12.31?
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP26
		$pd_obj->setFormulaType( 10 );                                                         //Periodic
		$pd_obj->setDate( strtotime( '24-Dec-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		$this->assertEquals( 33431.04, $ytd_gross_income );
		$this->assertEquals( 2827.19, $this->mf( $ytd_deduction ) ); //Was: 2837.42

		//Actual Income/Deductions for the year.
		$pd_obj->setFormulaType( 10 );                               //Periodic
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setGrossPayPeriodIncome( 33431.04 );
		$this->assertEquals( '33431.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '2827.19', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );

		//Actual Income/Deductions for the year.
		$pd_obj->setFormulaType( 20 );                               //NonPeriodic
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setGrossPayPeriodIncome( 33431.04 );
		$this->assertEquals( '33431.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '2801.20', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
	}

	function testCA_2015_Federal_NonPeriodic_FormulaC2() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		//
		// Has mostly periodic pay periods, then a few out-of-cycle ones.
		//

		//
		// Full test with EI/CPP YTD amounts.
		//

		$pd_obj = new PayrollDeduction( 'CA', 'PE' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;
		$ytd_cpp = 0;
		$ytd_ei = 0;

		//PP1
		$pd_obj->setDate( strtotime( '08-Jan-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP2
		$pd_obj->setDate( strtotime( '22-Jan-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP3
		$pd_obj->setDate( strtotime( '05-Feb-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP4
		$pd_obj->setDate( strtotime( '19-Feb-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP5
		$pd_obj->setDate( strtotime( '05-Mar-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP6
		$pd_obj->setDate( strtotime( '19-Mar-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP7
		$pd_obj->setDate( strtotime( '02-Apr-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP8
		$pd_obj->setDate( strtotime( '16-Apr-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP9
		$pd_obj->setDate( strtotime( '30-Apr-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP10
		$pd_obj->setDate( strtotime( '14-May-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP11
		$pd_obj->setDate( strtotime( '28-May-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP12
		$pd_obj->setDate( strtotime( '11-Jun-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP13
		$pd_obj->setDate( strtotime( '25-Jun-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP14
		$pd_obj->setDate( strtotime( '09-Jul-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP15
		$pd_obj->setDate( strtotime( '23-Jul-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP16
		$pd_obj->setDate( strtotime( '06-Aug-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP17
		$pd_obj->setDate( strtotime( '20-Aug-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP18
		$pd_obj->setDate( strtotime( '03-Sep-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP19
		$pd_obj->setDate( strtotime( '17-Sep-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP20
		$pd_obj->setDate( strtotime( '01-Oct-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP21
		$pd_obj->setDate( strtotime( '15-Oct-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP22
		$pd_obj->setDate( strtotime( '29-Oct-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP23
		$pd_obj->setDate( strtotime( '12-Nov-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP24
		$pd_obj->setDate( strtotime( '26-Nov-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//
		// Do the A/B pay period in reverse order from testCA_2015_Federal_NonPeriodic_FormulaC, to make sure it works out the same either way.
		//

		//PP25 (A)
		$pd_obj->setFormulaType( 20 ); //NonPeriodic
		$pd_obj->setDate( strtotime( '10-Dec-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 150.00 );
		$this->assertEquals( '150.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //12.31?
		//$current_pay_period++; //(Multiple runs in this pay period)
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP25 (B)
		$pd_obj->setFormulaType( 10 );                                                        //NonPeriodic
		$pd_obj->setDate( strtotime( '10-Dec-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 2 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP26
		$pd_obj->setFormulaType( 10 );                                                        //Periodic
		$pd_obj->setDate( strtotime( '24-Dec-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		$this->assertEquals( 33431.04, $ytd_gross_income );
		$this->assertEquals( 2806.23, $this->mf( $ytd_deduction ) );

		//Actual Income/Deductions for the year.
		$pd_obj->setFormulaType( 10 );                                                        //Periodic
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setGrossPayPeriodIncome( 33431.04 );
		$this->assertEquals( '33431.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '2827.19', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );

		//Actual Income/Deductions for the year.
		$pd_obj->setFormulaType( 20 );                                                        //NonPeriodic
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setGrossPayPeriodIncome( 33431.04 );
		$this->assertEquals( '33431.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '2801.20', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
	}

	function testCA_2015_Federal_Periodic_Match_NonPeriodic_FormulaA() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		//
		// Make sure that a NonPeriodic formula for the first pay period after hire matches the period one.
		//

		$pd_obj = new PayrollDeduction( 'CA', 'PE' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( true ); //CPP Exempt, to eliminate the CPP exemption amount as cause for differences.
		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		//First PP
		$pd_obj->setDate( strtotime( '08-Jan-2015' ) );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$federal_pp_deductions = $pd_obj->getFederalPayPeriodDeductions();
		$this->assertEquals( '116.44', $this->mf( $federal_pp_deductions ) );

		//
		//Leave all $pd_obj settings the same, just switch to non-periodic
		//
		$pd_obj = new PayrollDeduction( 'CA', 'PE' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );
		$pd_obj->setFormulaType( 20 ); //Non-Periodic
		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( true ); //CPP Exempt, to eliminate the CPP exemption amount as cause for differences.
		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		//First PP
		$pd_obj->setDate( strtotime( '08-Jan-2015' ) );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '116.44', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Should be exactly or very close to the above periodic result.

	}

	function testCA_2015_Federal_Periodic_Match_NonPeriodic_FormulaB() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		//
		// Make sure that a NonPeriodic formula for the first pay period after hire matches the period one.
		//

		//
		// Full test with EI/CPP YTD amounts with dynamically calculated CPP amounts.
		//
		$pd_obj = new PayrollDeduction( 'CA', 'PE' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );
		$pd_obj->setHireAdjustedAnnualPayPeriods( 26 );
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		//First PP
		$pd_obj->setDate( strtotime( '08-Jan-2015' ) );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setHireAdjustedCurrentPayPeriod( 1 );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '56.70', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '107.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );

		//
		//Leave all $pd_obj settings the same, just switch to non-periodic
		//
		$pd_obj = new PayrollDeduction( 'CA', 'PE' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );
		$pd_obj->setHireAdjustedAnnualPayPeriods( 26 );
		$pd_obj->setFormulaType( 20 ); //Non-Periodic
		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		//First PP
		$pd_obj->setDate( strtotime( '08-Jan-2015' ) );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setHireAdjustedCurrentPayPeriod( 1 );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '63.36', $this->mf( $pd_obj->getEmployeeCPP() ) );                 //Higher CPP amount because we have to exclude the CPP Exemption on a out-of-cycle pay period.
		$this->assertEquals( '106.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Should be exactly or very close to the above periodic result.
	}

	function testCA_2015_Federal_Periodic_Match_NonPeriodic_FormulaC() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		//
		// Make sure that a NonPeriodic formula for the first pay period after hire matches the period one.
		//

		//
		// Full test with EI/CPP YTD amounts. However use static CPP amounts rather than calculated ones, as they can differ due to the CPP exemption of $3500.
		//
		$pd_obj = new PayrollDeduction( 'CA', 'PE' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );
		$pd_obj->setHireAdjustedAnnualPayPeriods( 26 );
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		//First PP
		$pd_obj->setDate( strtotime( '08-Jan-2015' ) );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setHireAdjustedCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setEmployeeCPPForPayPeriod( 63.36 );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '106.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );

		//
		//Leave all $pd_obj settings the same, just switch to non-periodic
		//
		$pd_obj = new PayrollDeduction( 'CA', 'PE' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );
		$pd_obj->setHireAdjustedAnnualPayPeriods( 26 );
		$pd_obj->setFormulaType( 20 ); //Non-Periodic
		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		//First PP
		$pd_obj->setDate( strtotime( '08-Jan-2015' ) );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setHireAdjustedCurrentPayPeriod( 1 );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setEmployeeCPPForPayPeriod( 63.36 );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '106.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Should be exactly or very close to the above periodic result.
	}

	function testCA_2015_Federal_Periodic_Match_NonPeriodic_FormulaD() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		//
		// Make sure that a NonPeriodic formula for the first pay period after hire matches the period one.
		//

		//
		// Full test with EI/CPP YTD amounts. However use static CPP amounts rather than calculated ones, as they can differ due to the CPP exemption of $3500.
		//
		$pd_obj = new PayrollDeduction( 'CA', 'PE' );
		$pd_obj->setDate( strtotime( '01-Jul-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );
		$pd_obj->setHireAdjustedAnnualPayPeriods( 13 );
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		//First PP
		$pd_obj->setDate( strtotime( '01-Jul-2015' ) );
		$pd_obj->setCurrentPayPeriod( 13 );
		$pd_obj->setHireAdjustedCurrentPayPeriod( 1 );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setEmployeeCPPForPayPeriod( 63.36 );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '106.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );

		//
		//Leave all $pd_obj settings the same, just switch to non-periodic
		//
		$pd_obj = new PayrollDeduction( 'CA', 'PE' );
		$pd_obj->setDate( strtotime( '01-Jul-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );
		$pd_obj->setHireAdjustedAnnualPayPeriods( 13 );
		$pd_obj->setFormulaType( 20 ); //Non-Periodic
		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		//First PP
		$pd_obj->setDate( strtotime( '01-Jul-2015' ) );
		$pd_obj->setCurrentPayPeriod( 13 );
		$pd_obj->setHireAdjustedCurrentPayPeriod( 1 );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setEmployeeCPPForPayPeriod( 63.36 );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '34.97', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Was: 33.96 Should be exactly or very close to the above periodic result.
	}

	function testCA_2015_Federal_Periodic_Match_NonPeriodic_FormulaE() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		//
		// Make sure that a NonPeriodic formula for the first pay period after hire matches the period one.
		//

		//
		// Full test with EI/CPP YTD amounts. However use static CPP amounts rather than calculated ones, as they can differ due to the CPP exemption of $3500.
		//
		$pd_obj = new PayrollDeduction( 'CA', 'PE' );
		$pd_obj->setDate( strtotime( '15-Dec-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );
		$pd_obj->setHireAdjustedAnnualPayPeriods( 26 );
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		//First PP
		$pd_obj->setDate( strtotime( '15-Dec-2015' ) );
		$pd_obj->setCurrentPayPeriod( 26 );
		$pd_obj->setHireAdjustedCurrentPayPeriod( 1 );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setEmployeeCPPForPayPeriod( 63.36 );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '106.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );

		//
		//Leave all $pd_obj settings the same, just switch to non-periodic
		//
		$pd_obj = new PayrollDeduction( 'CA', 'PE' );
		$pd_obj->setDate( strtotime( '15-Dec-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );
		$pd_obj->setHireAdjustedAnnualPayPeriods( 26 );
		$pd_obj->setFormulaType( 20 ); //Non-Periodic
		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		//First PP
		$pd_obj->setDate( strtotime( '15-Dec-2015' ) );
		$pd_obj->setCurrentPayPeriod( 26 );
		$pd_obj->setHireAdjustedCurrentPayPeriod( 1 );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setEmployeeCPPForPayPeriod( 63.36 );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '106.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
	}

	function testCA_2015_Federal_Periodic_Match_NonPeriodic_FormulaF() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		//
		// Make sure that a NonPeriodic formula for the first pay period after hire matches the period one.
		//

		//
		// Full test with EI/CPP YTD amounts. However use static CPP amounts rather than calculated ones, as they can differ due to the CPP exemption of $3500.
		//
		$pd_obj = new PayrollDeduction( 'CA', 'PE' );
		$pd_obj->setDate( strtotime( '01-Oct-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );
		$pd_obj->setHireAdjustedAnnualPayPeriods( 26 );
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		//First PP
		$pd_obj->setDate( strtotime( '01-Oct-2015' ) );
		$pd_obj->setCurrentPayPeriod( 22 );
		$pd_obj->setHireAdjustedCurrentPayPeriod( 1 );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setEmployeeCPPForPayPeriod( 63.36 );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '106.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );

		//
		//Leave all $pd_obj settings the same, just switch to non-periodic
		//
		$pd_obj = new PayrollDeduction( 'CA', 'PE' );
		$pd_obj->setDate( strtotime( '01-Oct-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );
		$pd_obj->setHireAdjustedAnnualPayPeriods( 26 );
		$pd_obj->setFormulaType( 20 ); //Non-Periodic
		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		//First PP
		$pd_obj->setDate( strtotime( '01-Oct-2015' ) );
		$pd_obj->setCurrentPayPeriod( 22 );
		$pd_obj->setHireAdjustedCurrentPayPeriod( 1 );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setEmployeeCPPForPayPeriod( 63.36 );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( '1280.04', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '106.93', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
	}

	function testCA_2015_Federal_Periodic_Match_NonPeriodic_FormulaH() {
		Debug::text( 'US - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setHireAdjustedAnnualPayPeriods( 6 );
		$pd_obj->setFormulaType( 20 ); //NonPeriodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( true ); //EI/CPP exempt so we don't have to track YTD amounts.
		$pd_obj->setCPPExempt( true );
		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 6;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;


		//Test starting in the middle of the year.

		//PP7
		$pd_obj->setDate( strtotime( '01-Jul-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setHireAdjustedCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '438.17', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP8
		$pd_obj->setDate( strtotime( '01-Aug-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setHireAdjustedCurrentPayPeriod( 2 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '438.18', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP9
		$pd_obj->setDate( strtotime( '01-Sep-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setHireAdjustedCurrentPayPeriod( 3 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '438.18', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP10
		$pd_obj->setDate( strtotime( '01-Oct-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setHireAdjustedCurrentPayPeriod( 4 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '438.17', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP11
		$pd_obj->setDate( strtotime( '01-Nov-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setHireAdjustedCurrentPayPeriod( 5 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '438.18', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP12
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setHireAdjustedCurrentPayPeriod( 6 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '438.18', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		$this->assertEquals( 30000, $ytd_gross_income );
		$this->assertEquals( 2629.05, $ytd_deduction );

		//Actual Income/Deductions for the year.
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 30000 );
		$this->assertEquals( '30000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '2629.05', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
	}

	function testCA_2015_Province_Periodic_FormulaB() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( false ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP1
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP2
		$pd_obj->setDate( strtotime( '01-Feb-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP3
		$pd_obj->setDate( strtotime( '01-Mar-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP4
		$pd_obj->setDate( strtotime( '01-Apr-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP5
		$pd_obj->setDate( strtotime( '01-May-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP6
		$pd_obj->setDate( strtotime( '01-Jun-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP7
		$pd_obj->setDate( strtotime( '01-Jul-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP8
		$pd_obj->setDate( strtotime( '01-Aug-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '77.24', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) ); //81.99
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP9
		$pd_obj->setDate( strtotime( '01-Sep-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP10
		$pd_obj->setDate( strtotime( '01-Oct-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '77.24', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) ); //81.99
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP11
		$pd_obj->setDate( strtotime( '01-Nov-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP12
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '77.24', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) ); //81.99
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		$this->assertEquals( 42000, $ytd_gross_income );
		$this->assertEquals( 2287.6904999994003, $ytd_deduction ); //2301.9679999992

		//Actual Income/Deductions for the year.
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 42000 );
		$this->assertEquals( '42000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '2234.00', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2015_Province_NonPeriodic_FormulaB() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( false ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 20 ); //NonPeriodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP1
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP2
		$pd_obj->setDate( strtotime( '01-Feb-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '70.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP3
		$pd_obj->setDate( strtotime( '01-Mar-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP4
		$pd_obj->setDate( strtotime( '01-Apr-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '70.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP5
		$pd_obj->setDate( strtotime( '01-May-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP6
		$pd_obj->setDate( strtotime( '01-Jun-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '70.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP7
		$pd_obj->setDate( strtotime( '01-Jul-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP8
		$pd_obj->setDate( strtotime( '01-Aug-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '70.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP9
		$pd_obj->setDate( strtotime( '01-Sep-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP10
		$pd_obj->setDate( strtotime( '01-Oct-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '70.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP11
		$pd_obj->setDate( strtotime( '01-Nov-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP12
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '70.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		$this->assertEquals( 42000, $ytd_gross_income );
		$this->assertEquals( 2234, $ytd_deduction );

		//Actual Income/Deductions for the year.
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 42000 );
		$this->assertEquals( '42000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '2234.00', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2015_Province_Periodic_FormulaC() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( false ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP1
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP2
		$pd_obj->setDate( strtotime( '01-Feb-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP3
		$pd_obj->setDate( strtotime( '01-Mar-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP4
		$pd_obj->setDate( strtotime( '01-Apr-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP5
		$pd_obj->setDate( strtotime( '01-May-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP6
		$pd_obj->setDate( strtotime( '01-Jun-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP7
		$pd_obj->setDate( strtotime( '01-Jul-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '77.24', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP8
		$pd_obj->setDate( strtotime( '01-Aug-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '77.24', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP9
		$pd_obj->setDate( strtotime( '01-Sep-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '77.24', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP10
		$pd_obj->setDate( strtotime( '01-Oct-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '77.24', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP11
		$pd_obj->setDate( strtotime( '01-Nov-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP12
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '77.24', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		$this->assertEquals( 30000, $ytd_gross_income );
		$this->assertEquals( 1399.4841666661998, $ytd_deduction );

		//Actual Income/Deductions for the year.
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 30000 );
		$this->assertEquals( '30000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1458.43', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2015_Province_NonPeriodic_FormulaC() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( false ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 20 ); //NonPeriodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP1
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP2
		$pd_obj->setDate( strtotime( '01-Feb-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP3
		$pd_obj->setDate( strtotime( '01-Mar-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP4
		$pd_obj->setDate( strtotime( '01-Apr-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP5
		$pd_obj->setDate( strtotime( '01-May-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP6
		$pd_obj->setDate( strtotime( '01-Jun-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '329.79', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP7
		$pd_obj->setDate( strtotime( '01-Jul-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '66.68', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP8
		$pd_obj->setDate( strtotime( '01-Aug-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '77.24', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP9
		$pd_obj->setDate( strtotime( '01-Sep-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '77.24', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP10
		$pd_obj->setDate( strtotime( '01-Oct-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '77.24', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP11
		$pd_obj->setDate( strtotime( '01-Nov-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '343.04', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP12
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '77.24', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		$this->assertEquals( 30000, $ytd_gross_income );
		$this->assertEquals( 1458.4259999999999, $ytd_deduction );

		//Actual Income/Deductions for the year.
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 30000 );
		$this->assertEquals( '30000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1458.43', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2015_Province_Periodic_FormulaD() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( false ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP1
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP2
		$pd_obj->setDate( strtotime( '01-Feb-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP3
		$pd_obj->setDate( strtotime( '01-Mar-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP4
		$pd_obj->setDate( strtotime( '01-Apr-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP5
		$pd_obj->setDate( strtotime( '01-May-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP6
		$pd_obj->setDate( strtotime( '01-Jun-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP7
		$pd_obj->setDate( strtotime( '01-Jul-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP8
		$pd_obj->setDate( strtotime( '01-Aug-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP9
		$pd_obj->setDate( strtotime( '01-Sep-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP10
		$pd_obj->setDate( strtotime( '01-Oct-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP11
		$pd_obj->setDate( strtotime( '01-Nov-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP12
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		$this->assertEquals( 45000, $ytd_gross_income );
		$this->assertEquals( 2521.6399999992004, $ytd_deduction );

		//Actual Income/Deductions for the year.
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 45000 );
		$this->assertEquals( '45000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '2465.00', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2015_Province_NonPeriodic_FormulaD() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( false ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 20 ); //NonPeriodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP1
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP2
		$pd_obj->setDate( strtotime( '01-Feb-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP3
		$pd_obj->setDate( strtotime( '01-Mar-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP4
		$pd_obj->setDate( strtotime( '01-Apr-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP5
		$pd_obj->setDate( strtotime( '01-May-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( '2000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '81.99', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP6
		$pd_obj->setDate( strtotime( '01-Jun-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '329.79', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP7
		$pd_obj->setDate( strtotime( '01-Jul-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '272.23', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP8
		$pd_obj->setDate( strtotime( '01-Aug-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '253.00', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP9
		$pd_obj->setDate( strtotime( '01-Sep-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '295.00', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP10
		$pd_obj->setDate( strtotime( '01-Oct-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP11
		$pd_obj->setDate( strtotime( '01-Nov-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP12
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( '5000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '301.67', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		$this->assertEquals( 45000, $ytd_gross_income );
		$this->assertEquals( 2465, $ytd_deduction );

		//Actual Income/Deductions for the year.
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 45000 );
		$this->assertEquals( '45000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '2465.00', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2015_Province_NonPeriodic_FormulaE() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( false ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 20 ); //NonPeriodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 5;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP5
		$pd_obj->setDate( strtotime( '01-Jun-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1500.00 );
		$this->assertEquals( '1500.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP6
		$pd_obj->setDate( strtotime( '01-Jun-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 4000.00 );
		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '72.30', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP7
		$pd_obj->setDate( strtotime( '01-Jul-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 4000.00 );
		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '144.73', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP8
		$pd_obj->setDate( strtotime( '01-Aug-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 4000.00 );
		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '179.35', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP9
		$pd_obj->setDate( strtotime( '01-Sep-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 4000.00 );
		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '254.44', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP10
		$pd_obj->setDate( strtotime( '01-Oct-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 4000.00 );
		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '254.44', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP11
		$pd_obj->setDate( strtotime( '01-Nov-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 4000.00 );
		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '254.44', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP12
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 4000.00 );
		$this->assertEquals( '4000.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '254.44', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		$this->assertEquals( 29500, $ytd_gross_income );
		$this->assertEquals( 1414.13, $this->mf( $ytd_deduction ) );


		//Actual Income/Deductions for the year.
		$pd_obj->setDate( strtotime( '01-Dec-2015' ) );
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 29500 );
		$this->assertEquals( '29500.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1414.13', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2015_Province_NonPeriodic_FormulaF() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( false ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );
		$pd_obj->setFormulaType( 20 ); //NonPeriodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 10027 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 5;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP5
		$pd_obj->setDate( strtotime( '08-Mar-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 532.74 );
		$this->assertEquals( '532.74', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP6
		$pd_obj->setDate( strtotime( '23-Mar-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP7
		$pd_obj->setDate( strtotime( '08-Apr-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP8
		$pd_obj->setDate( strtotime( '23-Apr-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP9
		$pd_obj->setDate( strtotime( '08-May-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );

		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP10
		$pd_obj->setDate( strtotime( '23-May-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP11
		$pd_obj->setDate( strtotime( '08-Jun-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '33.29', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP12
		$pd_obj->setDate( strtotime( '23-Jun-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '54.00', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP13
		$pd_obj->setDate( strtotime( '08-Jul-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '46.80', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) ); //Formulas changed on Jul 1st 2016.
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP14
		$pd_obj->setDate( strtotime( '23-Jul-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '54.05', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP15
		$pd_obj->setDate( strtotime( '08-Aug-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '54.05', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP16
		$pd_obj->setDate( strtotime( '23-Aug-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '54.05', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP17
		$pd_obj->setDate( strtotime( '08-Sep-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '54.05', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP18
		$pd_obj->setDate( strtotime( '23-Sep-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '54.05', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP19
		$pd_obj->setDate( strtotime( '08-Oct-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '54.05', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP20
		$pd_obj->setDate( strtotime( '23-Oct-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '54.05', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP21
		$pd_obj->setDate( strtotime( '08-Nov-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '54.05', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP22
		$pd_obj->setDate( strtotime( '23-Nov-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '54.05', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP23
		$pd_obj->setDate( strtotime( '08-Dec-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '54.05', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP24
		$pd_obj->setDate( strtotime( '23-Dec-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '54.05', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		$this->assertEquals( 27449.47, $ytd_gross_income );
		$this->assertEquals( 728.68, $this->mf( $ytd_deduction ) );


		//Actual Income/Deductions for the year.
		$pd_obj->setDate( strtotime( '01-Dec-2016' ) );
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 27449.47 );
		$this->assertEquals( '27449.47', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '728.68', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2015_Federal_NonPeriodic_FormulaF() {
		Debug::text( 'CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2015' ) );
		$pd_obj->setEnableCPPAndEIDeduction( false ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );
		$pd_obj->setFormulaType( 20 ); //NonPeriodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 10027 );
		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 5;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP5
		$pd_obj->setDate( strtotime( '08-Mar-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 532.74 );
		$this->assertEquals( '532.74', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP6
		$pd_obj->setDate( strtotime( '23-Mar-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP7
		$pd_obj->setDate( strtotime( '08-Apr-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP8
		$pd_obj->setDate( strtotime( '23-Apr-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '85.66', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP9
		$pd_obj->setDate( strtotime( '08-May-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '133.53', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );

		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP10
		$pd_obj->setDate( strtotime( '23-May-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '133.53', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP11
		$pd_obj->setDate( strtotime( '08-Jun-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '133.53', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP12
		$pd_obj->setDate( strtotime( '23-Jun-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '133.53', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP13
		$pd_obj->setDate( strtotime( '08-Jul-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '133.53', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP14
		$pd_obj->setDate( strtotime( '23-Jul-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '133.53', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP15
		$pd_obj->setDate( strtotime( '08-Aug-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '133.53', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP16
		$pd_obj->setDate( strtotime( '23-Aug-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '133.53', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP17
		$pd_obj->setDate( strtotime( '08-Sep-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '133.53', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP18
		$pd_obj->setDate( strtotime( '23-Sep-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '133.53', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP19
		$pd_obj->setDate( strtotime( '08-Oct-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '133.53', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP20
		$pd_obj->setDate( strtotime( '23-Oct-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '133.53', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP21
		$pd_obj->setDate( strtotime( '08-Nov-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '133.53', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP22
		$pd_obj->setDate( strtotime( '23-Nov-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '133.53', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP23
		$pd_obj->setDate( strtotime( '08-Dec-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '133.53', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP24
		$pd_obj->setDate( strtotime( '23-Dec-2016' ) );
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '133.53', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		$this->assertEquals( 27449.47, $ytd_gross_income );
		$this->assertEquals( 2222.17, $this->mf( $ytd_deduction ) );


		//PP1 (Next moving into the next year)
		$pd_obj->setDate( strtotime( '08-Jan-2017' ) );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( '1416.67', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '132.42', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );

		//Actual Income/Deductions for the year.
		$pd_obj->setDate( strtotime( '01-Dec-2016' ) );
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 27449.47 );
		$this->assertEquals( '27449.47', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '2222.17', $this->mf( $pd_obj->getFederalPayPeriodDEductions() ) );
	}
}

?>