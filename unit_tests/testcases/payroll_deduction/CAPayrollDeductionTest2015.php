<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2016 TimeTrex Software Inc.
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

require_once('PHPUnit/Framework/TestCase.php');

/**
 * @group CAPayrollDeductionTest2015
 */
class CAPayrollDeductionTest2015 extends PHPUnit_Framework_TestCase {
	public $company_id = NULL;

	public function setUp() {
		Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10);

		require_once( Environment::getBasePath().'/classes/payroll_deduction/PayrollDeduction.class.php');

		$this->tax_table_file = dirname(__FILE__).'/CAPayrollDeductionTest2015.csv';

		$this->company_id = PRIMARY_COMPANY_ID;

		TTDate::setTimeZone('Etc/GMT+8'); //Force to non-DST timezone. 'PST' isnt actually valid.

		return TRUE;
	}

	public function tearDown() {
		Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10);
		return TRUE;
	}

	public function mf($amount) {
		return Misc::MoneyFormat($amount, FALSE);
	}

	//
	// January 2015
	//

	//
	// Don't forget to update the Federal Employment Credit in CA.class.php.
	//
	function testCSVFile() {
		$this->assertEquals( file_exists($this->tax_table_file), TRUE);

		$test_rows = Misc::parseCSV( $this->tax_table_file, TRUE );

		$total_rows = ( count($test_rows) + 1 );
		$i = 2;
		foreach( $test_rows as $row ) {
			//Debug::text('Province: '. $row['province'] .' Income: '. $row['gross_income'], __FILE__, __LINE__, __METHOD__, 10);
			if ( isset($row['gross_income']) AND isset($row['low_income']) AND isset($row['high_income'])
					AND $row['gross_income'] == '' AND $row['low_income'] != '' AND $row['high_income'] != '' ) {
				$row['gross_income'] = ( $row['low_income'] + ( ($row['high_income'] - $row['low_income']) / 2 ) );
			}
			if ( $row['country'] != '' AND $row['gross_income'] != '' ) {
				//echo $i.'/'.$total_rows.'. Testing Province: '. $row['province'] .' Income: '. $row['gross_income'] ."\n";

				$pd_obj = new PayrollDeduction( $row['country'], $row['province'] );
				$pd_obj->setDate( strtotime( $row['date'] ) );
				$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
				$pd_obj->setAnnualPayPeriods( $row['pay_periods'] );

				$pd_obj->setFederalTotalClaimAmount( $row['federal_claim'] ); //Amount from 2005, Should use amount from 2007 automatically.
				$pd_obj->setProvincialTotalClaimAmount( $row['provincial_claim'] );
				//$pd_obj->setWCBRate( 0.18 );

				$pd_obj->setEIExempt( FALSE );
				$pd_obj->setCPPExempt( FALSE );

				$pd_obj->setFederalTaxExempt( FALSE );
				$pd_obj->setProvincialTaxExempt( FALSE );

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
		$this->assertEquals( $total_rows, ( $i - 1 ));
	}

	function testCA_2015a_Example() {
		Debug::text('CA - Example Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10);

		$pd_obj = new PayrollDeduction('CA', 'AB');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 52 );

		$pd_obj->setFederalTotalClaimAmount( 29721.00 );
		$pd_obj->setProvincialTotalClaimAmount( 17593 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1100 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1100' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '82.95' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '68.41' );
	}

	function testCA_2015a_Example1() {
		Debug::text('CA - Example1 - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10);

		$pd_obj = new PayrollDeduction('CA', 'AB');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );

		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 18214 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1800 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1800' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '97.81' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '17.37' );
	}

	function testCA_2015a_Example2() {
		Debug::text('CA - Example2 - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10);

		$pd_obj = new PayrollDeduction('CA', 'AB');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 18214 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2300 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2300' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '294.02' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '146.83' );
	}

	function testCA_2015a_Example3() {
		Debug::text('CA - Example3 - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10);

		$pd_obj = new PayrollDeduction('CA', 'AB');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 52 );

		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 18214 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 2500 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2500' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '475.24' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '208.41' );
	}

	function testCA_2015a_Example4() {
		Debug::text('CA - Example1 - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10);

		$pd_obj = new PayrollDeduction('CA', 'BC');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 9938 );
		$pd_obj->setWCBRate( 0 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1560 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1560' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '147.06' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '57.26' );
	}

	function testCA_2015a_GovExample1() {
		Debug::text('CA - Example1 - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10);

		$pd_obj = new PayrollDeduction('CA', 'AB');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 52 );

		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 18214 );
		$pd_obj->setWCBRate( 0 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1030 ); //Take Gross income minus RPP and Union Dues.

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1030' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '120.61' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '61.42' );
	}

	function testCA_2015a_GovExample2() {
		Debug::text('CA - Example1 - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10);

		$pd_obj = new PayrollDeduction('CA', 'BC');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 52 );

		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 9938 );
		$pd_obj->setWCBRate( 0 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1030 ); //Take Gross income minus RPP and Union Dues.

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1030' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '120.61' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '47.09' );
	}

	function testCA_2015a_GovExample3() {
		Debug::text('CA - Example1 - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10);

		$pd_obj = new PayrollDeduction('CA', 'ON');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 52 );

		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 9863 );
		$pd_obj->setWCBRate( 0 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1030 ); //Take Gross income minus RPP and Union Dues.

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1030' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '120.61' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '60.63' );
	}

	function testCA_2015a_GovExample4() {
		Debug::text('CA - Example1 - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10);

		$pd_obj = new PayrollDeduction('CA', 'PE');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 52 );

		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 7708 );
		$pd_obj->setWCBRate( 0 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 1030 ); //Take Gross income minus RPP and Union Dues.

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1030' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '120.61' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '96.59' );
	}

	//
	// CPP/ EI
	//
	function testCA_2015a_BiWeekly_CPP_LowIncome() {
		Debug::text('CA - BiWeekly - CPP - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10);

		$pd_obj = new PayrollDeduction('CA', 'BC');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 585.32 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '585.32' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeCPP() ), '22.31' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployerCPP() ), '22.31' );
	}

	function testCA_2015a_SemiMonthly_CPP_LowIncome() {
		Debug::text('CA - BiWeekly - CPP - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10);

		$pd_obj = new PayrollDeduction('CA', 'BC');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 585.23 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '585.23' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeCPP() ), '21.75' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployerCPP() ), '21.75' );
	}

	function testCA_2015a_SemiMonthly_MAXCPP_LowIncome() {
		Debug::text('CA - BiWeekly - MAXCPP - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10);

		$pd_obj = new PayrollDeduction('CA', 'BC');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );

		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 2478.95 ); //2479.95 - 1.00
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 587.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '587.00' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeCPP() ), '1.00' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployerCPP() ), '1.00' );
	}

	function testCA_2015a_EI_LowIncome() {
		Debug::text('CA - EI - Beginning of 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10);

		$pd_obj = new PayrollDeduction('CA', 'BC');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$pd_obj->setGrossPayPeriodIncome( 587.76 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '587.76' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeEI() ), '11.05' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployerEI() ), '15.47' );
	}

	function testCA_2015a_MAXEI_LowIncome() {
		Debug::text('CA - MAXEI - Beginning of 2006 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__, 10);

		$pd_obj = new PayrollDeduction('CA', 'BC');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );

		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setWCBRate( 0.18 );

		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );

		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );

		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 929.60 ); //930.60 - 1.00

		$pd_obj->setGrossPayPeriodIncome( 587.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '587.00' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeEI() ), '1.00' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployerEI() ), '1.40' );
	}

	function testCA_2015_Federal_Periodic_FormulaA() {
		Debug::text('US - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA', 'BC');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );
		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP1
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '640.71' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP2
		$pd_obj->setDate(strtotime('01-Feb-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '640.71' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP3
		$pd_obj->setDate(strtotime('01-Mar-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '640.71' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP4
		$pd_obj->setDate(strtotime('01-Apr-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '640.71' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP5
		$pd_obj->setDate(strtotime('01-May-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '640.71' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP6
		$pd_obj->setDate(strtotime('01-Jun-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '640.71' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP7
		$pd_obj->setDate(strtotime('01-Jul-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '640.71' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP8
		$pd_obj->setDate(strtotime('01-Aug-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '640.71' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP9
		$pd_obj->setDate(strtotime('01-Sep-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '640.71' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP10
		$pd_obj->setDate(strtotime('01-Oct-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '640.71' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP11
		$pd_obj->setDate(strtotime('01-Nov-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '640.71' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP12
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '640.71' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		$this->assertEquals( $ytd_gross_income, 60000 );
		$this->assertEquals( $ytd_deduction, 7688.4675 );

		//Actual Income/Deductions for the year.
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 60000 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '60000' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '7688.47' );
	}

	function testCA_2015_Federal_NonPeriodic_FormulaA() {
		Debug::text('US - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA', 'BC');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 20 ); //NonPeriodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( TRUE ); //EI/CPP exempt so we don't have to track YTD amounts.
		$pd_obj->setCPPExempt( TRUE );
		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP1
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '683.34' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP2
		$pd_obj->setDate(strtotime('01-Feb-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '683.34' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP3
		$pd_obj->setDate(strtotime('01-Mar-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '683.34' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP4
		$pd_obj->setDate(strtotime('01-Apr-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '683.34' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP5
		$pd_obj->setDate(strtotime('01-May-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '683.34' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP6
		$pd_obj->setDate(strtotime('01-Jun-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '683.34' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP7
		$pd_obj->setDate(strtotime('01-Jul-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '683.34' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP8
		$pd_obj->setDate(strtotime('01-Aug-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '683.34' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP9
		$pd_obj->setDate(strtotime('01-Sep-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '683.34' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP10
		$pd_obj->setDate(strtotime('01-Oct-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '683.34' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP11
		$pd_obj->setDate(strtotime('01-Nov-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '683.34' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP12
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '683.34' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		$this->assertEquals( $ytd_gross_income, 60000 );
		$this->assertEquals( $ytd_deduction, 8200.05 );

		//Actual Income/Deductions for the year.
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 60000 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '60000' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '8200.05' );
	}

	function testCA_2015_Federal_Periodic_FormulaB() {
		Debug::text('CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA', 'BC');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(FALSE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );
		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP1
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '683.34' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP2
		$pd_obj->setDate(strtotime('01-Feb-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '144.09' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP3
		$pd_obj->setDate(strtotime('01-Mar-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '683.34' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP4
		$pd_obj->setDate(strtotime('01-Apr-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '144.09' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP5
		$pd_obj->setDate(strtotime('01-May-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '683.34' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP6
		$pd_obj->setDate(strtotime('01-Jun-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '144.09' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP7
		$pd_obj->setDate(strtotime('01-Jul-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '683.34' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP8
		$pd_obj->setDate(strtotime('01-Aug-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '144.09' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP9
		$pd_obj->setDate(strtotime('01-Sep-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '683.34' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP10
		$pd_obj->setDate(strtotime('01-Oct-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '144.09' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP11
		$pd_obj->setDate(strtotime('01-Nov-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '683.34' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP12
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '144.09' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		$this->assertEquals( $ytd_gross_income, 42000 );
		$this->assertEquals( $ytd_deduction, 4964.55 );

		//Actual Income/Deductions for the year.
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 42000 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '42000' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '4429.05' );
	}

	function testCA_2015_Federal_NonPeriodic_FormulaB() {
		Debug::text('CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA', 'BC');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(FALSE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 20 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );
		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP1
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '683.34' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP2
		$pd_obj->setDate(strtotime('01-Feb-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '54.84' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP3
		$pd_obj->setDate(strtotime('01-Mar-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '651.84' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP4
		$pd_obj->setDate(strtotime('01-Apr-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '86.34' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP5
		$pd_obj->setDate(strtotime('01-May-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '620.34' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP6
		$pd_obj->setDate(strtotime('01-Jun-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '117.84' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP7
		$pd_obj->setDate(strtotime('01-Jul-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '594.09' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP8
		$pd_obj->setDate(strtotime('01-Aug-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '144.09' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP9
		$pd_obj->setDate(strtotime('01-Sep-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '594.09' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP10
		$pd_obj->setDate(strtotime('01-Oct-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '144.09' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP11
		$pd_obj->setDate(strtotime('01-Nov-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '594.09' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		//PP12
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '144.09' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();

		$this->assertEquals( $ytd_gross_income, 42000 );
		$this->assertEquals( $ytd_deduction, 4429.05 );

		//Actual Income/Deductions for the year.
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 42000 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '42000' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '4429.05' );
	}

	function testCA_2015_Federal_NonPeriodic_FormulaC() {
		Debug::text('CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__,10);

		//
		// Full test with EI/CPP YTD amounts.
		//

		$pd_obj = new PayrollDeduction('CA', 'PE');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );
		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;
		$ytd_cpp = 0;
		$ytd_ei = 0;

		//PP1
		$pd_obj->setDate(strtotime('08-Jan-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP2
		$pd_obj->setDate(strtotime('22-Jan-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP3
		$pd_obj->setDate(strtotime('05-Feb-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP4
		$pd_obj->setDate(strtotime('19-Feb-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP5
		$pd_obj->setDate(strtotime('05-Mar-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP6
		$pd_obj->setDate(strtotime('19-Mar-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP7
		$pd_obj->setDate(strtotime('02-Apr-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP8
		$pd_obj->setDate(strtotime('16-Apr-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP9
		$pd_obj->setDate(strtotime('30-Apr-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP10
		$pd_obj->setDate(strtotime('14-May-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP11
		$pd_obj->setDate(strtotime('28-May-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP12
		$pd_obj->setDate(strtotime('11-Jun-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP13
		$pd_obj->setDate(strtotime('25-Jun-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP14
		$pd_obj->setDate(strtotime('09-Jul-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP15
		$pd_obj->setDate(strtotime('23-Jul-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP16
		$pd_obj->setDate(strtotime('06-Aug-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP17
		$pd_obj->setDate(strtotime('20-Aug-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP18
		$pd_obj->setDate(strtotime('03-Sep-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP19
		$pd_obj->setDate(strtotime('17-Sep-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP20
		$pd_obj->setDate(strtotime('01-Oct-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP21
		$pd_obj->setDate(strtotime('15-Oct-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP22
		$pd_obj->setDate(strtotime('29-Oct-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP23
		$pd_obj->setDate(strtotime('12-Nov-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP24
		$pd_obj->setDate(strtotime('26-Nov-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP25 (A)
		$pd_obj->setFormulaType( 10 ); //NonPeriodic
		$pd_obj->setDate(strtotime('10-Dec-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		//$current_pay_period++; //(Multiple runs in this pay period)
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP25 (B)
		$pd_obj->setFormulaType( 20 ); //NonPeriodic
		$pd_obj->setDate(strtotime('10-Dec-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 2 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 150.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '150.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '31.19' ); //12.31?
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP26
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setDate(strtotime('24-Dec-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		$this->assertEquals( $ytd_gross_income, 33431.04 );
		$this->assertEquals( $this->mf($ytd_deduction), 2837.42 );

		//Actual Income/Deductions for the year.
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setGrossPayPeriodIncome( 33431.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '33431.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '2827.19' );

		//Actual Income/Deductions for the year.
		$pd_obj->setFormulaType( 20 ); //NonPeriodic
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setGrossPayPeriodIncome( 33431.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '33431.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '2801.20' );
	}

	function testCA_2015_Federal_NonPeriodic_FormulaC2() {
		Debug::text('CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__,10);

		//
		// Full test with EI/CPP YTD amounts.
		//

		$pd_obj = new PayrollDeduction('CA', 'PE');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(TRUE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 26 );
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11327 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );
		$pd_obj->setFederalTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;
		$ytd_cpp = 0;
		$ytd_ei = 0;

		//PP1
		$pd_obj->setDate(strtotime('08-Jan-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP2
		$pd_obj->setDate(strtotime('22-Jan-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP3
		$pd_obj->setDate(strtotime('05-Feb-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP4
		$pd_obj->setDate(strtotime('19-Feb-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP5
		$pd_obj->setDate(strtotime('05-Mar-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP6
		$pd_obj->setDate(strtotime('19-Mar-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP7
		$pd_obj->setDate(strtotime('02-Apr-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP8
		$pd_obj->setDate(strtotime('16-Apr-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP9
		$pd_obj->setDate(strtotime('30-Apr-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP10
		$pd_obj->setDate(strtotime('14-May-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP11
		$pd_obj->setDate(strtotime('28-May-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP12
		$pd_obj->setDate(strtotime('11-Jun-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP13
		$pd_obj->setDate(strtotime('25-Jun-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP14
		$pd_obj->setDate(strtotime('09-Jul-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP15
		$pd_obj->setDate(strtotime('23-Jul-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP16
		$pd_obj->setDate(strtotime('06-Aug-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP17
		$pd_obj->setDate(strtotime('20-Aug-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP18
		$pd_obj->setDate(strtotime('03-Sep-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP19
		$pd_obj->setDate(strtotime('17-Sep-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP20
		$pd_obj->setDate(strtotime('01-Oct-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP21
		$pd_obj->setDate(strtotime('15-Oct-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP22
		$pd_obj->setDate(strtotime('29-Oct-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP23
		$pd_obj->setDate(strtotime('12-Nov-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP24
		$pd_obj->setDate(strtotime('26-Nov-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
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
		$pd_obj->setDate(strtotime('10-Dec-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 150.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '150.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '0.00' ); //12.31?
		//$current_pay_period++; //(Multiple runs in this pay period)
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP25 (B)
		$pd_obj->setFormulaType( 10 ); //NonPeriodic
		$pd_obj->setDate(strtotime('10-Dec-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 2 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		//PP26
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setDate(strtotime('24-Dec-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setYearToDateCPPContribution( $ytd_cpp );
		$pd_obj->setYearToDateEIContribution( $ytd_ei );
		$pd_obj->setGrossPayPeriodIncome( 1280.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1280.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '107.93' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDeductions();
		$ytd_cpp += $pd_obj->getEmployeeCPP();
		$ytd_ei += $pd_obj->getEmployeeEI();

		$this->assertEquals( $ytd_gross_income, 33431.04 );
		$this->assertEquals( $this->mf($ytd_deduction), 2806.23 );

		//Actual Income/Deductions for the year.
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setGrossPayPeriodIncome( 33431.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '33431.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '2827.19' );

		//Actual Income/Deductions for the year.
		$pd_obj->setFormulaType( 20 ); //NonPeriodic
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setCurrentPayrollRunId( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );
		$pd_obj->setGrossPayPeriodIncome( 33431.04 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '33431.04' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '2801.20' );
	}

	function testCA_2015_Province_Periodic_FormulaB() {
		Debug::text('CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA', 'BC');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(FALSE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP1
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP2
		$pd_obj->setDate(strtotime('01-Feb-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP3
		$pd_obj->setDate(strtotime('01-Mar-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP4
		$pd_obj->setDate(strtotime('01-Apr-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP5
		$pd_obj->setDate(strtotime('01-May-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP6
		$pd_obj->setDate(strtotime('01-Jun-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP7
		$pd_obj->setDate(strtotime('01-Jul-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP8
		$pd_obj->setDate(strtotime('01-Aug-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '77.24' ); //81.99
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP9
		$pd_obj->setDate(strtotime('01-Sep-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP10
		$pd_obj->setDate(strtotime('01-Oct-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '77.24' ); //81.99
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP11
		$pd_obj->setDate(strtotime('01-Nov-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP12
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '77.24' ); //81.99
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		$this->assertEquals( $ytd_gross_income, 42000 );
		$this->assertEquals( $ytd_deduction, 2287.6904999994003 ); //2301.9679999992

		//Actual Income/Deductions for the year.
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 42000 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '42000' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '2234.00' );
	}

	function testCA_2015_Province_NonPeriodic_FormulaB() {
		Debug::text('CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA', 'BC');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(FALSE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 20 ); //NonPeriodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP1
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP2
		$pd_obj->setDate(strtotime('01-Feb-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '70.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP3
		$pd_obj->setDate(strtotime('01-Mar-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP4
		$pd_obj->setDate(strtotime('01-Apr-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '70.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP5
		$pd_obj->setDate(strtotime('01-May-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP6
		$pd_obj->setDate(strtotime('01-Jun-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '70.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP7
		$pd_obj->setDate(strtotime('01-Jul-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP8
		$pd_obj->setDate(strtotime('01-Aug-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '70.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP9
		$pd_obj->setDate(strtotime('01-Sep-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP10
		$pd_obj->setDate(strtotime('01-Oct-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '70.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP11
		$pd_obj->setDate(strtotime('01-Nov-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP12
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '70.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		$this->assertEquals( $ytd_gross_income, 42000 );
		$this->assertEquals( $ytd_deduction, 2234 );

		//Actual Income/Deductions for the year.
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 42000 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '42000' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '2234.00' );
	}

	function testCA_2015_Province_Periodic_FormulaC() {
		Debug::text('CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA', 'BC');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(FALSE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP1
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP2
		$pd_obj->setDate(strtotime('01-Feb-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP3
		$pd_obj->setDate(strtotime('01-Mar-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP4
		$pd_obj->setDate(strtotime('01-Apr-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP5
		$pd_obj->setDate(strtotime('01-May-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP6
		$pd_obj->setDate(strtotime('01-Jun-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP7
		$pd_obj->setDate(strtotime('01-Jul-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '77.24' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP8
		$pd_obj->setDate(strtotime('01-Aug-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '77.24' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP9
		$pd_obj->setDate(strtotime('01-Sep-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '77.24' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP10
		$pd_obj->setDate(strtotime('01-Oct-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '77.24' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP11
		$pd_obj->setDate(strtotime('01-Nov-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP12
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '77.24' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		$this->assertEquals( $ytd_gross_income, 30000 );
		$this->assertEquals( $ytd_deduction, 1399.4841666661998 );

		//Actual Income/Deductions for the year.
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 30000 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '30000' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '1458.43' );
	}

	function testCA_2015_Province_NonPeriodic_FormulaC() {
		Debug::text('CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA', 'BC');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(FALSE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 20 ); //NonPeriodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP1
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP2
		$pd_obj->setDate(strtotime('01-Feb-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP3
		$pd_obj->setDate(strtotime('01-Mar-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP4
		$pd_obj->setDate(strtotime('01-Apr-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP5
		$pd_obj->setDate(strtotime('01-May-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP6
		$pd_obj->setDate(strtotime('01-Jun-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '329.79' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP7
		$pd_obj->setDate(strtotime('01-Jul-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '66.68' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP8
		$pd_obj->setDate(strtotime('01-Aug-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '77.24' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP9
		$pd_obj->setDate(strtotime('01-Sep-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '77.24' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP10
		$pd_obj->setDate(strtotime('01-Oct-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '77.24' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP11
		$pd_obj->setDate(strtotime('01-Nov-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '343.04' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP12
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '77.24' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		$this->assertEquals( $ytd_gross_income, 30000 );
		$this->assertEquals( $ytd_deduction, 1458.4259999999999 );

		//Actual Income/Deductions for the year.
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 30000 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '30000' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '1458.43' );
	}

	function testCA_2015_Province_Periodic_FormulaD() {
		Debug::text('CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA', 'BC');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(FALSE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 10 ); //Periodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP1
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP2
		$pd_obj->setDate(strtotime('01-Feb-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP3
		$pd_obj->setDate(strtotime('01-Mar-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP4
		$pd_obj->setDate(strtotime('01-Apr-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP5
		$pd_obj->setDate(strtotime('01-May-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP6
		$pd_obj->setDate(strtotime('01-Jun-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP7
		$pd_obj->setDate(strtotime('01-Jul-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP8
		$pd_obj->setDate(strtotime('01-Aug-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP9
		$pd_obj->setDate(strtotime('01-Sep-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP10
		$pd_obj->setDate(strtotime('01-Oct-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP11
		$pd_obj->setDate(strtotime('01-Nov-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP12
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		$this->assertEquals( $ytd_gross_income, 45000 );
		$this->assertEquals( $ytd_deduction, 2521.6399999992004 );

		//Actual Income/Deductions for the year.
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 45000 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '45000' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '2465.00' );
	}

	function testCA_2015_Province_NonPeriodic_FormulaD() {
		Debug::text('CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA', 'BC');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(FALSE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 20 ); //NonPeriodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 1;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP1
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP2
		$pd_obj->setDate(strtotime('01-Feb-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP3
		$pd_obj->setDate(strtotime('01-Mar-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP4
		$pd_obj->setDate(strtotime('01-Apr-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP5
		$pd_obj->setDate(strtotime('01-May-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 2000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '81.99' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP6
		$pd_obj->setDate(strtotime('01-Jun-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '329.79' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP7
		$pd_obj->setDate(strtotime('01-Jul-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '272.23' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP8
		$pd_obj->setDate(strtotime('01-Aug-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '253.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP9
		$pd_obj->setDate(strtotime('01-Sep-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '295.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP10
		$pd_obj->setDate(strtotime('01-Oct-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP11
		$pd_obj->setDate(strtotime('01-Nov-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP12
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 5000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '5000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '301.67' );

		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		$this->assertEquals( $ytd_gross_income, 45000 );
		$this->assertEquals( $ytd_deduction, 2465 );

		//Actual Income/Deductions for the year.
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 45000 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '45000' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '2465.00' );
	}

	function testCA_2015_Province_NonPeriodic_FormulaE() {
		Debug::text('CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA', 'BC');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(FALSE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 12 );
		$pd_obj->setFormulaType( 20 ); //NonPeriodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 0 );
		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 5;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP5
		$pd_obj->setDate(strtotime('01-Jun-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1500.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1500.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '0.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP6
		$pd_obj->setDate(strtotime('01-Jun-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 4000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '72.30' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP7
		$pd_obj->setDate(strtotime('01-Jul-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 4000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '144.73' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP8
		$pd_obj->setDate(strtotime('01-Aug-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 4000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '179.35' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP9
		$pd_obj->setDate(strtotime('01-Sep-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 4000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '254.44' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP10
		$pd_obj->setDate(strtotime('01-Oct-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 4000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '254.44' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP11
		$pd_obj->setDate(strtotime('01-Nov-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 4000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '254.44' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP12
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 4000.00 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '254.44' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		$this->assertEquals( $ytd_gross_income, 29500 );
		$this->assertEquals( $this->mf( $ytd_deduction ), 1414.13 );


		//Actual Income/Deductions for the year.
		$pd_obj->setDate(strtotime('01-Dec-2015'));
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 29500 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '29500' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '1414.13' );
	}

	function testCA_2015_Province_NonPeriodic_FormulaF() {
		Debug::text('CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA', 'BC');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(FALSE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );
		$pd_obj->setFormulaType( 20 ); //NonPeriodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 10027 );
		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 5;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP5
		$pd_obj->setDate(strtotime('08-Mar-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 532.74 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '532.74' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '0.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP6
		$pd_obj->setDate(strtotime('23-Mar-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '0.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP7
		$pd_obj->setDate(strtotime('08-Apr-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '0.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP8
		$pd_obj->setDate(strtotime('23-Apr-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '0.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP9
		$pd_obj->setDate(strtotime('08-May-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '0.00' );

		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP10
		$pd_obj->setDate(strtotime('23-May-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '0.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP11
		$pd_obj->setDate(strtotime('08-Jun-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '33.29' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP12
		$pd_obj->setDate(strtotime('23-Jun-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '54.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP13
		$pd_obj->setDate(strtotime('08-Jul-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '54.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP14
		$pd_obj->setDate(strtotime('23-Jul-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '54.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP15
		$pd_obj->setDate(strtotime('08-Aug-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '54.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP16
		$pd_obj->setDate(strtotime('23-Aug-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '54.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP17
		$pd_obj->setDate(strtotime('08-Sep-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '54.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP18
		$pd_obj->setDate(strtotime('23-Sep-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '54.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP19
		$pd_obj->setDate(strtotime('08-Oct-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '54.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP20
		$pd_obj->setDate(strtotime('23-Oct-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '54.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP21
		$pd_obj->setDate(strtotime('08-Nov-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '54.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP22
		$pd_obj->setDate(strtotime('23-Nov-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '54.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP23
		$pd_obj->setDate(strtotime('08-Dec-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '54.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		//PP24
		$pd_obj->setDate(strtotime('23-Dec-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '54.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getProvincialPayPeriodDeductions();

		$this->assertEquals( $ytd_gross_income, 27449.47 );
		$this->assertEquals( $this->mf( $ytd_deduction ), 735.32 );


		//Actual Income/Deductions for the year.
		$pd_obj->setDate(strtotime('01-Dec-2016'));
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 27449.47 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '27449.47' );
		$this->assertEquals( $this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), '735.32' );

	}

	function testCA_2015_Federal_NonPeriodic_FormulaF() {
		Debug::text('CA - SemiMonthly - Beginning of 2015 01-Jan-2015: ', __FILE__, __LINE__, __METHOD__,10);

		$pd_obj = new PayrollDeduction('CA', 'BC');
		$pd_obj->setDate(strtotime('01-Jan-2015'));
		$pd_obj->setEnableCPPAndEIDeduction(FALSE); //Deduct CPP/EI.
		$pd_obj->setAnnualPayPeriods( 24 );
		$pd_obj->setFormulaType( 20 ); //NonPeriodic
		$pd_obj->setFederalTotalClaimAmount( 11038 );
		$pd_obj->setProvincialTotalClaimAmount( 10027 );
		$pd_obj->setEIExempt( FALSE );
		$pd_obj->setCPPExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setProvincialTaxExempt( FALSE );
		$pd_obj->setYearToDateCPPContribution( 0 );
		$pd_obj->setYearToDateEIContribution( 0 );

		$current_pay_period = 5;
		$ytd_gross_income = 0;
		$ytd_deduction = 0;

		//PP5
		$pd_obj->setDate(strtotime('08-Mar-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 532.74 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '532.74' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '0.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP6
		$pd_obj->setDate(strtotime('23-Mar-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '0.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP7
		$pd_obj->setDate(strtotime('08-Apr-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '0.00' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP8
		$pd_obj->setDate(strtotime('23-Apr-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '85.66' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP9
		$pd_obj->setDate(strtotime('08-May-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '133.53' );

		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP10
		$pd_obj->setDate(strtotime('23-May-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '133.53' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP11
		$pd_obj->setDate(strtotime('08-Jun-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '133.53' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP12
		$pd_obj->setDate(strtotime('23-Jun-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '133.53' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP13
		$pd_obj->setDate(strtotime('08-Jul-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '133.53' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP14
		$pd_obj->setDate(strtotime('23-Jul-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '133.53' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP15
		$pd_obj->setDate(strtotime('08-Aug-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '133.53' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP16
		$pd_obj->setDate(strtotime('23-Aug-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '133.53' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP17
		$pd_obj->setDate(strtotime('08-Sep-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '133.53' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP18
		$pd_obj->setDate(strtotime('23-Sep-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '133.53' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP19
		$pd_obj->setDate(strtotime('08-Oct-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '133.53' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP20
		$pd_obj->setDate(strtotime('23-Oct-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '133.53' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP21
		$pd_obj->setDate(strtotime('08-Nov-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '133.53' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP22
		$pd_obj->setDate(strtotime('23-Nov-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '133.53' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP23
		$pd_obj->setDate(strtotime('08-Dec-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '133.53' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		//PP24
		$pd_obj->setDate(strtotime('23-Dec-2016'));
		$pd_obj->setCurrentPayPeriod( $current_pay_period );
		$pd_obj->setYearToDateGrossIncome( $ytd_gross_income );
		$pd_obj->setYearToDateDeduction( $ytd_deduction );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '133.53' );
		$current_pay_period++;
		$ytd_gross_income += $pd_obj->getGrossPayPeriodIncome();
		$ytd_deduction += $pd_obj->getFederalPayPeriodDEductions();

		$this->assertEquals( $ytd_gross_income, 27449.47 );
		$this->assertEquals( $this->mf( $ytd_deduction ), 2222.17 );


		//PP1 (Next moving into the next year)
		$pd_obj->setDate(strtotime('08-Jan-2017'));
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 1416.67 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1416.67' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '133.53' );

		//Actual Income/Deductions for the year.
		$pd_obj->setDate(strtotime('01-Dec-2016'));
		$pd_obj->setAnnualPayPeriods( 1 );
		$pd_obj->setCurrentPayPeriod( 1 );
		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setYearToDateDeduction( 0 );
		$pd_obj->setGrossPayPeriodIncome( 27449.47 );
		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '27449.47' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDEductions() ), '2222.17' );
	}
}
?>