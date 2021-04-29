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
 * @group USPayrollDeductionTest2020
 */
class USPayrollDeductionTest2020 extends PHPUnit_Framework_TestCase {
	public $company_id = null;

	public function setUp() {
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		require_once( Environment::getBasePath() . '/classes/payroll_deduction/PayrollDeduction.class.php' );

		$this->tax_table_file = dirname( __FILE__ ) . '/USPayrollDeductionTest2020.csv';

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
	// January 2020
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

				$pd_obj = new PayrollDeduction( $row['country'], ( ( $row['province'] == '00' ) ? 'AK' : $row['province'] ) ); //Valid state is needed to calculate something, even for just federal numbers.
				$pd_obj->setDate( strtotime( $row['date'] ) );
				$pd_obj->setAnnualPayPeriods( $row['pay_periods'] );

				//Federal
				$pd_obj->setFederalFormW4Version( $row['federal_form_w4_version'] );
				$pd_obj->setFederalFilingStatus( $row['filing_status'] );
				$pd_obj->setFederalAllowance( $row['allowance'] );
				$pd_obj->setFederalMultipleJobs( false ); //2020 or newer W4 settings.
				$pd_obj->setFederalClaimDependents( $row['federal_claim_dependents'] );
				$pd_obj->setFederalOtherIncome( $row['federal_other_income'] );
				$pd_obj->setFederalDeductions( $row['federal_other_deductions'] );
				$pd_obj->setFederalAdditionalDeduction( 0 );

				//State
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

				$pd_obj = new PayrollDeduction( $row['country'], ( ( $row['province'] == '00' ) ? 'AK' : $row['province'] ) ); //Valid state is needed to calculate something, even for just federal numbers.
				$pd_obj->setDate( strtotime( '-1 year', strtotime( $row['date'] ) ) ); //Get the same date only last year.
				$pd_obj->setAnnualPayPeriods( $row['pay_periods'] );

				//Federal
				$pd_obj->setFederalFormW4Version( $row['federal_form_w4_version'] );
				$pd_obj->setFederalFilingStatus( $row['filing_status'] );
				$pd_obj->setFederalAllowance( $row['allowance'] );
				$pd_obj->setFederalMultipleJobs( false ); //2020 or newer W4 settings.
				$pd_obj->setFederalClaimDependents( $row['federal_claim_dependents'] );
				$pd_obj->setFederalOtherIncome( $row['federal_other_income'] );
				$pd_obj->setFederalDeductions( $row['federal_other_deductions'] );
				$pd_obj->setFederalAdditionalDeduction( 0 );

				//State
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
					//$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), $this->MatchWithinMarginOfError( $this->mf( $row['federal_deduction'] ), $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), 0.01 ) );
					$amount_diff = 0;
					$amount_diff_percent = 0;
					if ( $row['federal_deduction'] > 0 ) {
						$amount_diff = abs( ( $pd_obj->getFederalPayPeriodDeductions() - $row['federal_deduction'] ) );
						$amount_diff_percent = ( ( $amount_diff / $row['federal_deduction'] ) * 100 );
					}

					Debug::text( $i . '. Amount: This Year: ' . $row['federal_deduction'] . ' Last Year: ' . $pd_obj->getFederalPayPeriodDeductions() . ' Diff Amount: ' . $amount_diff . ' Percent: ' . $amount_diff_percent . '%', __FILE__, __LINE__, __METHOD__, 10 );
					//2019 to 2020 has significant differences, so this check is useless.
//					if ( $amount_diff > 5 ) {
//						$this->assertLessThan( 5, $amount_diff_percent ); //Should be slightly higher than inflation.
//						$this->assertGreaterThan( 0, $amount_diff_percent );
//					}
				}

				if ( $row['provincial_deduction'] != '' ) {
					//$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), $this->mf( $row['provincial_deduction'] ) );
					$amount_diff = 0;
					$amount_diff_percent = 0;
					if ( $row['provincial_deduction'] > 0 && $pd_obj->getStatePayPeriodDeductions() > 0 ) {
						$amount_diff = abs( ( $pd_obj->getStatePayPeriodDeductions() - $row['provincial_deduction'] ) );
						$amount_diff_percent = ( ( $amount_diff / $row['provincial_deduction'] ) * 100 );
					}

					Debug::text( $i . '. Amount: This Year: ' . $row['provincial_deduction'] . ' Last Year: ' . $pd_obj->getStatePayPeriodDeductions() . ' Diff Amount: ' . $amount_diff . ' Percent: ' . $amount_diff_percent . '%', __FILE__, __LINE__, __METHOD__, 10 );
					if ( !in_array( $row['province'], [ '00', 'IA', 'ND', 'OR', 'MN', 'NM', 'CO' ] ) && $amount_diff > 5 ) { //Some states had significant changes.
						$this->assertLessThan( 15, $amount_diff_percent ); //Reasonable margin of error.
						$this->assertGreaterThan( 0, $amount_diff_percent );
					}
				}
			}

			$i++;
		}

		//Make sure all rows are tested.
		$this->assertEquals( $total_rows, ( $i - 1 ) );
	}

	function testUS_2020_Test2019W4() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 2 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 9615 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '9615' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '2228.90' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '0' );
	}

	function testUS_2020_Test2020W4SingleJob1() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 9615 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '9615' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '2228.90' ); //2228.90
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '0' );
	}

	function testUS_2020_Test2020W4TwoJobs1() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( true );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 9615 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '9615' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '2797.08' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '0' );
	}

	function testUS_2020_Test2020W4OneJobWithDependents1() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 2500 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 9615 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '9615' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '2132.75' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '0' );
	}

	function testUS_2020_Test2020W4OneJobWithOtherIncome1() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 10000 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 9615 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '9615' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '2363.52' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '0' );
	}

	function testUS_2020_Test2020W4OneJobWithDeductions1() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 5000 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 9615 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '9615' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '2161.60' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '0' );
	}

	function testUS_2020_Test2020W4OneJobWithAdditionalDeduction1() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 9615 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '9615' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '2228.90' ); //2128.90 + 100
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '0' );
	}

	//Examples from 15-T publication.
	function testUS_2020_Test2020W4WageBracket1a() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1925' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '166.17' ); //Should be about 214 based on tax tables.
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '0' );
	}

	//Examples from 15-T publication.
	function testUS_2020_Test2020W4WageBracket1b() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 2 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1925' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '166.17' ); //Should be about 166 based on tax tables.
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '0' );
	}

	//Examples from 15-T publication.
	function testUS_2020_Test2020W4WageBracket2a() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( true );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1925' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '395.30' ); //Should be about 395 based on tax tables.
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '0' );
	}

	//Examples from 15-T publication.
	function testUS_2020_Test2020W4WageBracket2b() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married Filing Jointly
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( true );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1925' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '290.91' ); //Should be about 291 based on tax tables.
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '0' );
	}

	//Examples from 15-T publication.
	function testUS_2020_Test2020W4WageBracket2c() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 40 ); //Head of Household
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( true );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1925' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '362.21' ); //Should be about 362 based on tax tables.
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '0' );
	}

	//Examples from 15-T publication.
	function testUS_2020_Test2020W4WageBracket3a() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 40 ); //Head of Household
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2020 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1925' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '235.90' ); //Should be about 236 based on 2020 W4 standard withholding tax tables.
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '0' );
	}

	//Examples from 15-T publication.
	function testUS_2020_Test2019W4WageBracket1a() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2019 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1925' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '211.23' ); //Should be about 212 based on tax tables.
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '0' );
	}

	//Examples from 15-T publication.
	function testUS_2020_Test2019W4WageBracket1b() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married
		$pd_obj->setFederalAllowance( 2 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2019 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1925' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '176.10' ); //Should be about 177 based on tax tables.
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '0' );
	}

	//Examples from 15-T publication.
	function testUS_2020_Test2019W4WageBracket2a() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2019 );
		$pd_obj->setFederalMultipleJobs( true );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1925' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '395.30' ); //Should be about 395 based on tax tables.
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '0' );
	}

	//Examples from 15-T publication.
	function testUS_2020_Test2019W4WageBracket2b() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 20 ); //Married Filing Jointly
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2019 );
		$pd_obj->setFederalMultipleJobs( true );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1925' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '290.91' ); //Should be about 291 based on tax tables.
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '0' );
	}

	//Examples from 15-T publication.
	function testUS_2020_Test2019W4WageBracket2c() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 40 ); //Head of Household
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2019 );
		$pd_obj->setFederalMultipleJobs( true );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1925' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '362.21' ); //Should be about 362 based on tax tables.
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '0' );
	}

	//Examples from 15-T publication.
	function testUS_2020_Test2019W4WageBracket3a() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'TX' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
		$pd_obj->setAnnualPayPeriods( 52 ); //Weekly

		$pd_obj->setFederalFilingStatus( 40 ); //Head of Household
		$pd_obj->setFederalAllowance( 0 ); //2019 or older W4

		//2020 or newer W4
		$pd_obj->setFederalFormW4Version( 2019 );
		$pd_obj->setFederalMultipleJobs( false );
		$pd_obj->setFederalClaimDependents( 0 );
		$pd_obj->setFederalOtherIncome( 0 );
		$pd_obj->setFederalDeductions( 0 );
		$pd_obj->setFederalAdditionalDeduction( 0 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1925 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1925' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '274.04' ); //Should be about 236 based on 2020 W4 standard withholding tax tables.
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '0' );
	}

	function testUS_ID_2020a_Test1() {
		//Example from employer guide.
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ID' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
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
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '10' );
	}

	function testUS_ID_2020a_Test2() {
		//Example from employer guide.
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'ID' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
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
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '11' );
	}

	function testUS_LA_2020a_Test1() {
		//Example from employer guide.
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'LA' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
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
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '20.35' );
	}

	function testUS_LA_2020a_Test2() {
		//Example from employer guide.
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'LA' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
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
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '167.92' );
	}

	function testUS_2020a_Test1() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'OR' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
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
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '44.10' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '37.15' );
		$this->assertEquals( $this->mf( $pd_obj->getEmployeeSocialSecurity() ), '35.77' );
	}

	function testUS_AR_2020a_Test1() {
		//Example from employer guide.
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'AR' );
		$pd_obj->setDate( strtotime( '01-Mar-2020' ) );
		$pd_obj->setAnnualPayPeriods( 12 ); //Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 2 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 2 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 2127 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '2127' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '57.73' );
	}

	function testUS_AR_2020a_Test2() {
		//Example from employer guide.
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'AR' );
		$pd_obj->setDate( strtotime( '01-Mar-2020' ) );
		$pd_obj->setAnnualPayPeriods( 12 ); //Monthly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 2 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 2 );

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 8333.33 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '8333.33' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '466.62' );
	}

	function testUS_MS_2020a_Test1() {
		//Example from employer guide.
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MS' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 2 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 0 ); //Exemption Claimed Amount

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1890 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1890' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '80.85' );
	}

	function testUS_MS_2020a_Test2() {
		//Example from employer guide.
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MS' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
		$pd_obj->setAnnualPayPeriods( 26 ); //BiWeekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 2 );

		$pd_obj->setStateFilingStatus( 10 ); //Single
		$pd_obj->setStateAllowance( 18000 ); //Exemption Claimed Amount

		$pd_obj->setYearToDateSocialSecurityContribution( 0 );

		$pd_obj->setFederalTaxExempt( false );
		$pd_obj->setProvincialTaxExempt( false );

		$pd_obj->setGrossPayPeriodIncome( 1890 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '1890' );
		$this->assertEquals( $this->mf( $pd_obj->getStatePayPeriodDeductions() ), '46.23' );
	}

	//
	// US Social Security
	//
	function testUS_2020a_SocialSecurity() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
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

	function testUS_2020a_SocialSecurity_Max() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
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

	function testUS_2020a_Medicare() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
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

	function testUS_2020a_Additional_MedicareA() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
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
		$this->assertEquals( $this->mf( $pd_obj->getMedicareAdditionalEmployerThreshold() ), '200000.00' );
	}

	function testUS_2020a_Additional_MedicareB() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
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
		$this->assertEquals( $this->mf( $pd_obj->getMedicareAdditionalEmployerThreshold() ), '200000.00' );
	}

	function testUS_2020a_Additional_MedicareC() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
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
		$this->assertEquals( $this->mf( $pd_obj->getMedicareAdditionalEmployerThreshold() ), '200000.00' );
	}

	function testUS_2020a_Additional_MedicareD() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
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
		$this->assertEquals( $this->mf( $pd_obj->getMedicareAdditionalEmployerThreshold() ), '200000.00' );
	}

	function testUS_2020a_FederalUI_NoState() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
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

	function testUS_2020a_FederalUI_NoState_Max() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
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

	function testUS_2020a_FederalUI_State_Max() {
		Debug::text( 'US - SemiMonthly - Beginning of 2020 01-Jan-2020: ', __FILE__, __LINE__, __METHOD__, 10 );

		$pd_obj = new PayrollDeduction( 'US', 'MO' );
		$pd_obj->setDate( strtotime( '01-Jan-2020' ) );
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