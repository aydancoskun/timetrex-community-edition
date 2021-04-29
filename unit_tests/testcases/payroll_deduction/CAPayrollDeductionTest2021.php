<?php /** @noinspection PhpMissingDocCommentInspection */
/** @noinspection PhpMissingDocCommentInspection */
/** @noinspection ALL */
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
 * @group CAPayrollDeductionTest2021
 */
class CAPayrollDeductionTest2021 extends PHPUnit\Framework\TestCase {
	public $company_id = null;

	public function setUp(): void {
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		require_once( Environment::getBasePath() . '/classes/payroll_deduction/PayrollDeduction.class.php' );

		$this->tax_table_file = dirname( __FILE__ ) . '/CAPayrollDeductionTest2021.csv';

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
	// January 2021
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
					$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), $this->mf( $row['provincial_deduction'] ), 'I: '. $i .' State: '. $row['province'] .' Income: '. $row['gross_income'] );
				}
			}

			$i++;
		}

		//Make sure all rows are tested.
		$this->assertEquals( $total_rows, ( $i - 1 ) );
	}

	//Test that the tax changes from one year to the next are without a specified threshold.
	function testCompareWithLastYearCSVFile() {
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
				$pd_obj->setDate( strtotime( '-1 year', strtotime( $row['date'] ) ) ); //Get the same date only last year.
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
					//$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), $this->mf( $row['federal_deduction'] ) );
					$amount_diff = 0;
					$amount_diff_percent = 0;
					if ( $row['federal_deduction'] > 0 ) {
						$amount_diff = abs( ( $pd_obj->getFederalPayPeriodDeductions() - $row['federal_deduction'] ) );
						$amount_diff_percent = ( ( $amount_diff / $row['federal_deduction'] ) * 100 );
					}

					//Debug::text($i.'. Amount: This Year: '. $row['federal_deduction'] .' Last Year: '. $pd_obj->getFederalPayPeriodDeductions() .' Diff Amount: '. $amount_diff .' Percent: '. $amount_diff_percent .'%', __FILE__, __LINE__, __METHOD__, 10);
					if ( $amount_diff > 5.5 ) {
						$this->assertLessThan( 15.0, $amount_diff_percent ); //Should be slightly higher than inflation.
						$this->assertGreaterThanOrEqual( 0, $amount_diff_percent );
					} else {
						$this->assertLessThan( 8.0, $amount_diff_percent ); //Should be slightly higher than inflation.
						$this->assertGreaterThanOrEqual( 0, $amount_diff_percent );
					}
				}
				if ( $row['provincial_deduction'] != '' ) {
					//$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), $this->mf( $row['provincial_deduction'] ) );
					$amount_diff = 0;
					$amount_diff_percent = 0;
					if ( $row['provincial_deduction'] > 0 && $pd_obj->getProvincialPayPeriodDeductions() > 0 ) {
						$amount_diff = abs( ( $pd_obj->getProvincialPayPeriodDeductions() - $row['provincial_deduction'] ) );
						$amount_diff_percent = ( ( $amount_diff / $row['provincial_deduction'] ) * 100 );
					}

					Debug::text( $i . '. Amount: This Year: ' . $row['provincial_deduction'] . ' Last Year: ' . $pd_obj->getProvincialPayPeriodDeductions() . ' Diff Amount: ' . $amount_diff . ' Percent: ' . $amount_diff_percent . '%', __FILE__, __LINE__, __METHOD__, 10 );
					if ( $amount_diff > 5.50 ) {
						$this->assertLessThan( 26, $amount_diff_percent ); //Reasonable margin of error.
						$this->assertGreaterThan( 0, $amount_diff_percent );
					}
				}
			}

			$i++;
		}

		//Make sure all rows are tested.
		$this->assertEquals( $total_rows, ( $i - 1 ) );
	}

	function testCA_2021a_Example() {
		Debug::text( 'CA - Example Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		$pd_obj->setDate( strtotime( '01-Jan-2021' ) );
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
		$this->assertEquals( '73.29', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '65.41', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_Example1() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		$pd_obj->setDate( strtotime( '01-Jan-2021' ) );
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
		$this->assertEquals( '65.09', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '7.53', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_Example2() {
		Debug::text( 'CA - Example2 - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		$pd_obj->setDate( strtotime( '01-Jan-2021' ) );
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
		$this->assertEquals( '258.06', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '140.28', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_Example3() {
		Debug::text( 'CA - Example3 - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		$pd_obj->setDate( strtotime( '01-Jan-2021' ) );
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
		$this->assertEquals( '439.30', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '204.95', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_Example4() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2021' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11809 );
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
		$this->assertEquals( '131.74', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '52.21', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_Example5() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2021' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11809 );
		$pd_obj->setProvincialTotalClaimAmount( 9938 );
		$pd_obj->setWCBRate( 0 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 8300 );

		$this->assertEquals( '8300.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1818.14', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '926.73', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_NS_Example1() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'NS' );
		$pd_obj->setDate( strtotime( '01-Jan-2021' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 0 );
		$pd_obj->setProvincialTotalClaimAmount( 0 ); //Claim amount of 0 is always considered $0 no matter what.
		$pd_obj->setWCBRate( 0 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 192 ); //Low income, where the full claim amount is used.

		$this->assertEquals( '192.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '20.62', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '16.34', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_NS_Example2() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'NS' );
		$pd_obj->setDate( strtotime( '01-Jan-2021' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 0 );
		$pd_obj->setProvincialTotalClaimAmount( 100 );
		$pd_obj->setWCBRate( 0 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1923 ); //Medium income, where part of the claim amount is reduced.

		$this->assertEquals( '1923.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '264.09', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '172.39', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_NS_Example3() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'NS' );
		$pd_obj->setDate( strtotime( '01-Jan-2021' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 0 );
		$pd_obj->setProvincialTotalClaimAmount( 11481 );
		$pd_obj->setWCBRate( 0 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 9615 ); //High income, over the threshold that reduces the claim amount.

		$this->assertEquals( '9615.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '2322.76', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '1635.88', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_GovExample1() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		$pd_obj->setDate( strtotime( '01-Jan-2021' ) );
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
		$this->assertEquals( '105.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '58.88', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_GovExample2() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2021' ) );
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
		$this->assertEquals( '105.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '43.64', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_GovExample3() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'ON' );
		$pd_obj->setDate( strtotime( '01-Jan-2021' ) );
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
		$this->assertEquals( '105.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '56.15', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_GovExample4() {
		Debug::text( 'CA - Example1 - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'PE' );
		$pd_obj->setDate( strtotime( '01-Jan-2021' ) );
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
		$this->assertEquals( '105.54', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '91.02', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	//
	// CPP/ EI
	//
	function testCA_2021a_BiWeekly_CPP_LowIncome() {
		Debug::text( 'CA - BiWeekly - CPP - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2021' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11809 );
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
		$this->assertEquals( '24.56', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '24.56', $this->mf( $pd_obj->getEmployerCPP() ) );
	}

	function testCA_2021a_SemiMonthly_CPP_LowIncome() {
		Debug::text( 'CA - BiWeekly - CPP - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2021' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 11809 );
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
		$this->assertEquals( '23.95', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '23.95', $this->mf( $pd_obj->getEmployerCPP() ) );
	}

	function testCA_2021a_SemiMonthly_MAXCPP_LowIncome() {
		Debug::text( 'CA - BiWeekly - MAXCPP - Beginning of 2021 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2021' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 11809 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( ( $pd_obj->getCPPEmployeeMaximumContribution() - 1 ) ); //2544.30 - 1.00
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 587.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '587.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getEmployerCPP() ) );
	}

	function testCA_2021a_EI_LowIncome() {
		Debug::text( 'CA - EI - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2021' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11809 );
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
		$this->assertEquals( '9.29', $this->mf( $pd_obj->getEmployeeEI() ) );
		$this->assertEquals( '13.00', $this->mf( $pd_obj->getEmployerEI() ) );
	}

	function testCA_2021a_MAXEI_LowIncome() {
		Debug::text( 'CA - MAXEI - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '01-Jan-2021' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11809 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( ( $pd_obj->getEIEmployeeMaximumContribution() - 1 ) );

		$pd_obj->setGrossPayPeriodIncome( 587.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '587.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getEmployeeEI() ) );
		$this->assertEquals( '1.40', $this->mf( $pd_obj->getEmployerEI() ) );
	}


	function testCA_2021a_MAXEI_MAXCPPa() {
		Debug::text( 'CA - MAXEI/MAXCPP - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '10-Nov-2021' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11474 );
		$pd_obj->setProvincialTotalClaimAmount( 10027 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2569.21 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '2569.21', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '132.69', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '132.69', $this->mf( $pd_obj->getEmployerCPP() ) );
		$this->assertEquals( '40.59', $this->mf( $pd_obj->getEmployeeEI() ) );
		$this->assertEquals( '56.83', $this->mf( $pd_obj->getEmployerEI() ) );
		$this->assertEquals( '312.68', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '125.55', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_MAXEI_MAXCPPa2() {
		Debug::text( 'CA - MAXEI/MAXCPP - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		$pd_obj->setDate( strtotime( '04-Nov-2021' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 12069 );
		$pd_obj->setProvincialTotalClaimAmount( 19369 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( ( $pd_obj->getCPPEmployeeMaximumContribution() - 126.35 ) );
		$pd_obj->setYearToDateEIContribution( ( $pd_obj->getEIEmployeeMaximumContribution() - 14.30 ) ); //Test with this pay stub reaching the maximum limit.

		$pd_obj->setEmployeeCPPForPayPeriod( ( $pd_obj->getCPPEmployeeMaximumContribution() - 126.35 ) );
		$pd_obj->setEmployeeEIForPayPeriod( ( $pd_obj->getEIEmployeeMaximumContribution() - 14.30 ) );

		$pd_obj->setGrossPayPeriodIncome( 2612.12 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '2612.12', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '126.35', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '126.35', $this->mf( $pd_obj->getEmployerCPP() ) );
		$this->assertEquals( '14.30', $this->mf( $pd_obj->getEmployeeEI() ) );
		$this->assertEquals( '20.02', $this->mf( $pd_obj->getEmployerEI() ) );
		$this->assertEquals( '321.48', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Should match the same federal amount in the below function.
		$this->assertEquals( '171.12', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_MAXEI_MAXCPPa3() {
		Debug::text( 'CA - MAXEI/MAXCPP - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'AB' );
		$pd_obj->setDate( strtotime( '04-Nov-2021' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 12069 );
		$pd_obj->setProvincialTotalClaimAmount( 19369 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( $pd_obj->getCPPEmployeeMaximumContribution() );
		$pd_obj->setYearToDateEIContribution( $pd_obj->getEIEmployeeMaximumContribution() ); //Test with this pay stub past the maximum limit.

//		$pd_obj->setEmployeeCPPForPayPeriod( 126.35 );
//		$pd_obj->setEmployeeEIForPayPeriod( 14.30 );

		$pd_obj->setGrossPayPeriodIncome( 2612.12 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '2612.12', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getEmployerCPP() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getEmployeeEI() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getEmployerEI() ) );
		$this->assertEquals( '321.48', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) ); //Should match the same federal amount in the above function.
		$this->assertEquals( '171.12', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_MAXEI_MAXCPPb() {
		Debug::text( 'CA - MAXEI/MAXCPP - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '10-Nov-2021' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11474 );
		$pd_obj->setProvincialTotalClaimAmount( 10027 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( ( $pd_obj->getCPPEmployeeMaximumContribution() - 20 ) ); //2544.30 - 20.00
		$pd_obj->setYearToDateEIContribution( ( $pd_obj->getEIEmployeeMaximumContribution() - 20 ) ); //955.04 - 20.00

		$pd_obj->setGrossPayPeriodIncome( 2569.21 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '2569.21', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '20.00', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '20.00', $this->mf( $pd_obj->getEmployerCPP() ) );
		$this->assertEquals( '20.00', $this->mf( $pd_obj->getEmployeeEI() ) );
		$this->assertEquals( '28.00', $this->mf( $pd_obj->getEmployerEI() ) );
		$this->assertEquals( '312.68', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '125.55', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_MAXEI_MAXCPPc() {
		Debug::text( 'CA - MAXEI/MAXCPP - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '10-Nov-2021' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11474 );
		$pd_obj->setProvincialTotalClaimAmount( 10027 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( ( $pd_obj->getCPPEmployeeMaximumContribution() - 1 ) ); //2544.30 - 1.00
		$pd_obj->setYearToDateEIContribution( ( $pd_obj->getEIEmployeeMaximumContribution() - 1 ) ); //955.04 - 1.00

		$pd_obj->setGrossPayPeriodIncome( 2569.21 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '2569.21', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getEmployerCPP() ) );
		$this->assertEquals( '1.00', $this->mf( $pd_obj->getEmployeeEI() ) );
		$this->assertEquals( '1.40', $this->mf( $pd_obj->getEmployerEI() ) );
		$this->assertEquals( '312.68', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '125.55', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_MAXEI_MAXCPPd() {
		Debug::text( 'CA - MAXEI/MAXCPP - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '10-Nov-2021' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11474 );
		$pd_obj->setProvincialTotalClaimAmount( 10027 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( $pd_obj->getCPPEmployeeMaximumContribution() ); //2544.30
		$pd_obj->setYearToDateEIContribution( $pd_obj->getEIEmployeeMaximumContribution() ); //955.04

		$pd_obj->setGrossPayPeriodIncome( 2569.21 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '2569.21', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getEmployerCPP() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getEmployeeEI() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getEmployerEI() ) );
		$this->assertEquals( '312.68', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '125.55', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_MAXEI_MAXCPPe() {
		Debug::text( 'CA - MAXEI/MAXCPP - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '10-Nov-2021' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 12069 );
		$pd_obj->setProvincialTotalClaimAmount( 10682 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( $pd_obj->getCPPEmployeeMaximumContribution() ); //2544.30
		$pd_obj->setYearToDateEIContribution( $pd_obj->getEIEmployeeMaximumContribution() ); //955.04

		$pd_obj->setGrossPayPeriodIncome( 2865.38 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '2865.38', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getEmployerCPP() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getEmployeeEI() ) );
		$this->assertEquals( '0.00', $this->mf( $pd_obj->getEmployerEI() ) );
		$this->assertEquals( '373.40', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '148.35', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_MAXEI_MAXCPPf() {
		Debug::text( 'CA - MAXEI/MAXCPP - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '10-Nov-2021' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 12069 );
		$pd_obj->setProvincialTotalClaimAmount( 10682 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( ( $pd_obj->getCPPEmployeeMaximumContribution() - 16.45 ) );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2865.38 );

		//$pd_obj->setEmployeeCPPForPayPeriod( 16.45 );
		//$pd_obj->setEmployeeCPPForPayPeriod( $pd_obj->getEmployeeCPP() );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '2865.38', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '16.45', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '16.45', $this->mf( $pd_obj->getEmployerCPP() ) );
		$this->assertEquals( '45.27', $this->mf( $pd_obj->getEmployeeEI() ) );
		$this->assertEquals( '63.38', $this->mf( $pd_obj->getEmployerEI() ) );
		$this->assertEquals( '373.40', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '148.35', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_MAXEI_MAXCPPf2() {
		Debug::text( 'CA - MAXEI/MAXCPP - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '10-Nov-2021' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 12069 );
		$pd_obj->setProvincialTotalClaimAmount( 10682 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( ( $pd_obj->getCPPEmployeeMaximumContribution() - 16.45 ) );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2865.38 );

		$pd_obj->setEmployeeCPPForPayPeriod( 16.45 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '2865.38', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '16.45', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '16.45', $this->mf( $pd_obj->getEmployerCPP() ) );
		$this->assertEquals( '45.27', $this->mf( $pd_obj->getEmployeeEI() ) );
		$this->assertEquals( '63.38', $this->mf( $pd_obj->getEmployerEI() ) );

		//Below should be: 389.13, however because we call setEmployeeCPPForPayPeriod( 16.45 ) with a specific amount (lower than what it normally would be because we are approaching the maximum CPP limt),
		// that amount has to be used which causes the Fed/Prov. tax to be increased due to getEmployeeCPPForPayPeriod() being lower than expected.
		// In the normal formula it never takes the maximum CPP limit into account when calculating the per pay period CPP amount. But we have to do that currently to handle things like RRSP contributions that are calculated on tax vs. CPP different.
		// At least until we have a way of passing in annual gross income separate from F=RRSP amount.
		$this->assertEquals( '373.40', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '148.35', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_MAXEI_MAXCPPg() {
		Debug::text( 'CA - MAXEI/MAXCPP - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'BC' );
		$pd_obj->setDate( strtotime( '10-Nov-2021' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11474 );
		$pd_obj->setProvincialTotalClaimAmount( 10027 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1900.00 ); //Less than EI/CPP maximum earnings for the year.

		//var_dump($pd_obj->getArray());

		$this->assertEquals( '1900.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '96.21', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '96.21', $this->mf( $pd_obj->getEmployerCPP() ) );
		$this->assertEquals( '30.02', $this->mf( $pd_obj->getEmployeeEI() ) );
		$this->assertEquals( '42.03', $this->mf( $pd_obj->getEmployerEI() ) );
		$this->assertEquals( '179.96', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '75.52', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}

	function testCA_2021a_RRSP() {
		Debug::text( 'CA - RRSP Contribution - Beginning of 01-Jan-2021: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'CA', 'ON' );
		$pd_obj->setDate( strtotime( '01-Jan-2021' ) );
		$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11809 );
		$pd_obj->setProvincialTotalClaimAmount( 10171 );
		//$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( false );
		$pd_obj->setCPPExempt( false );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );


		//Gross=1600, RRSP=32.00
		$pd_obj->setGrossPayPeriodIncome( 1568 ); //Less the RRSP deduction of $32.

		$pd_obj->setEmployeeCPPForPayPeriod( 72.54 ); //Force CPP amount based on $1600 gross
		$pd_obj->setEmployeeEIForPayPeriod( 26.08 ); //Force EI amount based on $1600 gross

		$this->assertEquals( '1568.00', $this->mf( $pd_obj->getGrossPayPeriodIncome() ) );
		$this->assertEquals( '72.54', $this->mf( $pd_obj->getEmployeeCPP() ) );
		$this->assertEquals( '26.08', $this->mf( $pd_obj->getEmployeeEI() ) );
		$this->assertEquals( '133.49', $this->mf( $pd_obj->getFederalPayPeriodDeductions() ) );
		$this->assertEquals( '70.38', $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ) );
	}
}

?>