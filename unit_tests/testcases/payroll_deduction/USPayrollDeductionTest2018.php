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

/**
 * @group USPayrollDeductionTest2018
 */
class USPayrollDeductionTest2018 extends PHPUnit_Framework_TestCase {
	public $company_id = null;

	public function setUp() {
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		require_once( Environment::getBasePath() . '/classes/payroll_deduction/PayrollDeduction.class.php' );

		$this->tax_table_file = dirname( __FILE__ ) . '/USPayrollDeductionTest2018.csv';

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

	public function MatchWithinMarginOfError( $source, $destination, $error = 0 ) {
		//Source: 125.01
		//Destination: 125.00
		//Source: 124.99
		$high_water_mark = bcadd( $destination, $error );
		$low_water_mark = bcsub( $destination, $error );

		if ( $source <= $high_water_mark && $source >= $low_water_mark ) {
			return $destination;
		}

		return $source;
	}

	//
	// January 2018
	//
	function testCSVFile() {
		$this->assertEquals( file_exists( $this->tax_table_file ), true );

		$test_rows = Misc::parseCSV( $this->tax_table_file, true );

		$total_rows = ( count( $test_rows ) + 1 );
		$i = 2;
		foreach ( $test_rows as $row ) {
			//Debug::text('Province: '. $row['province'] .' Income: '. $row['gross_income'], __FILE__, __LINE__, __METHOD__, 10);
			if ( $row['gross_income'] == '' && isset( $row['low_income'] ) && $row['low_income'] != '' && isset( $row['high_income'] ) && $row['high_income'] != '' ) {
				$row['gross_income'] = ( $row['low_income'] + ( ( $row['high_income'] - $row['low_income'] ) / 2 ) );
			}
			if ( $row['country'] != '' && $row['gross_income'] != '' ) {
				//echo $i.'/'.$total_rows.'. Testing Province: '. $row['province'] .' Income: '. $row['gross_income'] ."\n";

				$pd_obj = new PayrollDeduction( $row['country'], $row['province'] );
				$pd_obj->setDate( strtotime( $row['date'] ) );
				$pd_obj->setAnnualPayPeriods( $row['pay_periods'] );

				$pd_obj->setFederalFilingStatus( $row['filing_status'] );
				$pd_obj->setFederalAllowance( $row['allowance'] );

				$pd_obj->setStateFilingStatus( $row['filing_status'] );
				$pd_obj->setStateAllowance( $row['allowance'] );

				//Some states use other values for allowance/deductions.
				switch ( $row['province'] ) {
					case 'GA':
						Debug::text( 'Setting UserValue3: ' . $row['allowance'], __FILE__, __LINE__, __METHOD__, 10 );
						$pd_obj->setUserValue3( $row['allowance'] );
						break;
					case 'IN':
					case 'IL':
					case 'VA':
						Debug::text( 'Setting UserValue1: ' . $row['allowance'], __FILE__, __LINE__, __METHOD__, 10 );
						$pd_obj->setUserValue1( $row['allowance'] );
						break;
				}

				$pd_obj->setFederalTaxExempt( false );
				$pd_obj->setProvincialTaxExempt( false );

				$pd_obj->setGrossPayPeriodIncome( $this->mf( $row['gross_income'] ) );

				//var_dump($pd_obj->getArray());

				$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), $this->mf( $row['gross_income'] ) );
				if ( $row['federal_deduction'] != '' ) {
					$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), $this->MatchWithinMarginOfError( $this->mf( $row['federal_deduction'] ), $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), 0.01 ) );
				}
				if ( $row['provincial_deduction'] != '' ) {
					$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), $this->mf( $row['provincial_deduction'] ) );
				}
			}

			$i++;
		}

		//Make sure all rows are tested.
		$this->assertEquals( $total_rows, ( $i - 1 ) );
	}

	function testCompareWithLastYearCSVFile() {
		$this->assertEquals( file_exists( $this->tax_table_file ), true );

		$test_rows = Misc::parseCSV( $this->tax_table_file, true );

		$total_rows = ( count( $test_rows ) + 1 );
		$i = 2;
		foreach ( $test_rows as $row ) {
			//Debug::text('Province: '. $row['province'] .' Income: '. $row['gross_income'], __FILE__, __LINE__, __METHOD__, 10);
			if ( $row['gross_income'] == '' && isset( $row['low_income'] ) && $row['low_income'] != '' && isset( $row['high_income'] ) && $row['high_income'] != '' ) {
				$row['gross_income'] = ( $row['low_income'] + ( ( $row['high_income'] - $row['low_income'] ) / 2 ) );
			}
			if ( $row['country'] != '' && $row['gross_income'] != '' ) {
				//echo $i.'/'.$total_rows.'. Testing Province: '. $row['province'] .' Income: '. $row['gross_income'] ."\n";

				$pd_obj = new PayrollDeduction( $row['country'], $row['province'] );
				$pd_obj->setDate( strtotime( '-1 year', strtotime( $row['date'] ) ) ); //Get the same date only last year.
				$pd_obj->setAnnualPayPeriods( $row['pay_periods'] );

				$pd_obj->setFederalFilingStatus( $row['filing_status'] );
				$pd_obj->setFederalAllowance( $row['allowance'] );

				$pd_obj->setStateFilingStatus( $row['filing_status'] );
				$pd_obj->setStateAllowance( $row['allowance'] );

				//Some states use other values for allowance/deductions.
				switch ( $row['province'] ) {
					case 'GA':
						Debug::text( 'Setting UserValue3: ' . $row['allowance'], __FILE__, __LINE__, __METHOD__, 10 );
						$pd_obj->setUserValue3( $row['allowance'] );
						break;
					case 'IN':
					case 'IL':
					case 'VA':
						Debug::text( 'Setting UserValue1: ' . $row['allowance'], __FILE__, __LINE__, __METHOD__, 10 );
						$pd_obj->setUserValue1( $row['allowance'] );
						break;
				}

				$pd_obj->setFederalTaxExempt( false );
				$pd_obj->setProvincialTaxExempt( false );

				$pd_obj->setGrossPayPeriodIncome( $this->mf( $row['gross_income'] ) );

				//var_dump($pd_obj->getArray());

				$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), $this->mf( $row['gross_income'] ) );
				//2018 was the year of large tax reform, changes were quite substantial in some cases.
//				if ( $row['federal_deduction'] != '' ) {
//					//$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), $this->MatchWithinMarginOfError( $this->mf( $row['federal_deduction'] ), $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), 0.01 ) );
//					$amount_diff = 0;
//					$amount_diff_percent = 0;
//					if ( $row['federal_deduction'] > 0 ) {
//						$amount_diff = abs( ( $pd_obj->getFederalPayPeriodDeductions() - $row['federal_deduction'] ) );
//						$amount_diff_percent = ( ( $amount_diff / $row['federal_deduction'] ) * 100 );
//					}
//
//					//Debug::text($i.'. Amount: This Year: '. $row['federal_deduction'] .' Last Year: '. $pd_obj->getFederalPayPeriodDeductions() .' Diff Amount: '. $amount_diff .' Percent: '. $amount_diff_percent .'%', __FILE__, __LINE__, __METHOD__, 10);
//					if ( $amount_diff > 1.5 ) {
//						$this->assertLessThan( 3, $amount_diff_percent ); //Should be slightly higher than inflation.
//						$this->assertGreaterThan( 0, $amount_diff_percent );
//					}
//				}
				if ( $row['provincial_deduction'] != '' ) {
					//$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), $this->mf( $row['provincial_deduction'] ) );
					$amount_diff = 0;
					$amount_diff_percent = 0;
					if ( $row['provincial_deduction'] > 0 && $pd_obj->getStatePayPeriodDeductions() > 0 ) {
						$amount_diff = abs( ( $pd_obj->getStatePayPeriodDeductions() - $row['provincial_deduction'] ) );
						$amount_diff_percent = ( ( $amount_diff / $row['provincial_deduction'] ) * 100 );
					}

					Debug::text( $i . '. Amount: This Year: ' . $row['provincial_deduction'] . ' Last Year: ' . $pd_obj->getStatePayPeriodDeductions() . ' Diff Amount: ' . $amount_diff . ' Percent: ' . $amount_diff_percent . '%', __FILE__, __LINE__, __METHOD__, 10 );
					if ( !in_array( $row['province'], [ 'CA', 'DC', 'OR', 'ID', 'KY', 'MO', 'NM', 'UT', 'LA' ] ) && $amount_diff > 5 ) { //Some states had significant changes.
						$this->assertLessThan( 33, $amount_diff_percent ); //Reasonable margin of error.
						$this->assertGreaterThan( 0, $amount_diff_percent );
					}
				}
			}

			$i++;
		}

		//Make sure all rows are tested.
		$this->assertEquals( $total_rows, ( $i - 1 ) );
	}

	function testUS_ID_2018a_Test1() {
		//Example from employer guide.
		Debug::text( 'US - SemiMonthly - Beginning of 2018 01-Jan-2018: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ID' );
		$pd_obj->setDate( strtotime( '01-Jan-2018' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 2 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 4 ); //2 + 2

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1212 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1212' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '11' );
	}

	function testUS_ID_2018a_Test2() {
		//Example from employer guide.
		Debug::text( 'US - SemiMonthly - Beginning of 2018 01-Jan-2018: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ID' );
		$pd_obj->setDate( strtotime( '01-Jan-2018' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 2 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 4 ); //2 + 2

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '12' );
	}

	function testUS_LA_2018a_Test1() {
		//Example from employer guide.
		Debug::text( 'US - SemiMonthly - Beginning of 2018 01-Jan-2018: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'LA' );
		$pd_obj->setDate( strtotime( '01-Jan-2018' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 1 );
		$pd_obj->setUserValue3( 2 ); //Dependent

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 700 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '700' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '19.43' );
	}

	function testUS_LA_2018a_Test2() {
		//Example from employer guide.
		Debug::text( 'US - SemiMonthly - Beginning of 2018 01-Jan-2018: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'LA' );
		$pd_obj->setDate( strtotime( '01-Jan-2018' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateFilingStatus( 20 ); //Married
		$pd_obj->setStateAllowance( 2 );
		$pd_obj->setUserValue3( 3 ); //Dependent

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 4600 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '4600' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '157.12' );
	}

	function testUS_2018a_Test1() {
		Debug::text( 'US - SemiMonthly - Beginning of 2018 01-Jan-2018: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OR' );
		$pd_obj->setDate( strtotime( '01-Jan-2018' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 576.923 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '576.92' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '44.83' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '38.59' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeSocialSecurity() ), '35.77' );
	}

	//
	// US Social Security
	//
	function testUS_2018a_SocialSecurity() {
		Debug::text( 'US - SemiMonthly - Beginning of 2018 01-Jan-2018: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2018' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeSocialSecurity() ), '62.00' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployerSocialSecurity() ), '62.00' );
	}

	function testUS_2018a_SocialSecurity_Max() {
		Debug::text( 'US - SemiMonthly - Beginning of 2018 01-Jan-2018: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2018' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );


		$pd_obj->setYearToDateSocialSecurityContribution( ( $pd_obj->getSocialSecurityMaximumContribution() - 1 ) ); //7347

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeSocialSecurity() ), '1.00' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployerSocialSecurity() ), '1.00' );
	}

	function testUS_2018a_Medicare() {
		Debug::text( 'US - SemiMonthly - Beginning of 2018 01-Jan-2018: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2018' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeMedicare() ), '14.50' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployerMedicare() ), '14.50' );
	}

	function testUS_2018a_Additional_MedicareA() {
		Debug::text( 'US - SemiMonthly - Beginning of 2018 01-Jan-2018: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2018' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );


		$pd_obj->setYearToDateGrossIncome( 199000.00 );
		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeMedicare() ), '14.50' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployerMedicare() ), '14.50' );
	}

	function testUS_2018a_Additional_MedicareB() {
		Debug::text( 'US - SemiMonthly - Beginning of 2018 01-Jan-2018: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2018' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );


		$pd_obj->setYearToDateGrossIncome( 199500.00 );
		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeMedicare() ), '19.00' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployerMedicare() ), '14.50' );
	}

	function testUS_2018a_Additional_MedicareC() {
		Debug::text( 'US - SemiMonthly - Beginning of 2018 01-Jan-2018: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2018' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );


		$pd_obj->setYearToDateGrossIncome( 500000.00 );
		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeMedicare() ), '23.50' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployerMedicare() ), '14.50' );
	}

	function testUS_2018a_Additional_MedicareD() {
		Debug::text( 'US - SemiMonthly - Beginning of 2018 01-Jan-2018: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2018' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );


		$pd_obj->setYearToDateGrossIncome( 0 );
		$pd_obj->setGrossPayPeriodIncome( 500000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '500000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeMedicare() ), '9950.00' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployerMedicare() ), '7250.00' );
	}

	function testUS_2018a_FederalUI_NoState() {
		Debug::text( 'US - SemiMonthly - Beginning of 2018 01-Jan-2018: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2018' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );
		$pd_obj->setYearToDateFederalUIContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalEmployerUI() ), '60.00' );
	}

	function testUS_2018a_FederalUI_NoState_Max() {
		Debug::text( 'US - SemiMonthly - Beginning of 2018 01-Jan-2018: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2018' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateUIRate( 0 );
		$pd_obj->setStateUIWageBase( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );
		$pd_obj->setYearToDateFederalUIContribution( 419 ); //420
		$pd_obj->setYearToDateStateUIContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalEmployerUI() ), '1.00' );
	}

	function testUS_2018a_FederalUI_State_Max() {
		Debug::text( 'US - SemiMonthly - Beginning of 2018 01-Jan-2018: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2018' ) );
		$pd_obj->setAnnualPayPeriods( 24 ); //Semi-Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 );

		$pd_obj->setStateUIRate( 3.51 );
		$pd_obj->setStateUIWageBase( 11000 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );
		$pd_obj->setYearToDateFederalUIContribution( 173.30 ); //174.30
		$pd_obj->setYearToDateStateUIContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1000.00 );

		//var_dump($pd_obj->getArray());

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalEmployerUI() ), '1.00' );
	}
}

?>