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


//Each Year:
//  Copy testcases/payroll_deduction/CAPayrollDeductionCRATest2019.csv to the new year. Clear out all lines but the header.
//  Update below "$this->year = 2021;" to the new year.
//  Run: ./run_selenium.sh --filter CAPayrollDeductionCRACompareTest::testCRAToCSVFile <-- This will add lines to the above CSV file once its complete.
//  Run: ./run_selenium.sh --filter CAPayrollDeductionCRACompareTest::testCRAFromCSVFile <-- This will test the PDOC numbers against our own.

/**
 * @group CAPayrollDeductionCRACompareTest
 */
class CAPayrollDeductionCRACompareTest extends PHPUnit\Extensions\Selenium2TestCase {
	private $default_wait_timeout = 4000;//100000;

	function waitUntilByXPath( $xpath, $timeout = null ) {
		if ( $timeout == null ) {
			$timeout = $this->default_wait_timeout;
		}

		$this->waitUntil( function () use ( $xpath ) {
			try {
				$element = $this->byXPath( $xpath );
				if ( $element->displayed() ) {
					return true;
				}
			} catch ( PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e ) {

			}

			return null;
		}, $timeout );
	}

	public function setUp(): void {
		global $selenium_config;
		$this->selenium_config = $selenium_config;

		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		require_once( Environment::getBasePath() . '/classes/payroll_deduction/PayrollDeduction.class.php' );

		$this->year = 2021;

		$this->tax_table_file = dirname( __FILE__ ) . '/../payroll_deduction/CAPayrollDeductionTest' . $this->year . '.csv';
		$this->cra_deduction_test_csv_file = dirname( $this->tax_table_file ) . DIRECTORY_SEPARATOR . 'CAPayrollDeductionCRATest' . $this->year . '.csv';

		$this->company_id = PRIMARY_COMPANY_ID;

		$this->selenium_test_case_runs = 0;

		TTDate::setTimeZone( 'Etc/GMT+8', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		$this->setHost( $selenium_config['host'] );
		$this->setBrowser( $selenium_config['browser'] );
		$this->setBrowserUrl( $selenium_config['default_url'] );
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	function CRAPayrollDeductionOnlineCalculator( $args = [] ) {
		if ( ENABLE_SELENIUM_REMOTE_TESTS != true ) {
			return false;
		}

		Debug::Arr( $args, 'Args: ', __FILE__, __LINE__, __METHOD__, 10 );

		if ( count( $args ) == 0 ) {
			return false;
		}

		try {
			if ( $this->selenium_test_case_runs == 0 ) {
				$url = 'https://www.canada.ca/en/revenue-agency/services/e-services/e-services-businesses/payroll-deductions-online-calculator.html';
				Debug::text( 'Navigating to URL: ' . $url, __FILE__, __LINE__, __METHOD__, 10 );
				$this->url( $url );


				//Click "I Accept"
				$this->waitUntilByXPath( '//a[contains(.,\'I accept\')]' );
				$ae = $this->byXPath( '//a[contains(.,\'I accept\')]' );
				Debug::text( 'Active Element Text: ' . $ae->text(), __FILE__, __LINE__, __METHOD__, 10 );
				$ae->click();

				$this->waitUntilByXPath( '//*[@id="welcome_button_next"]' );
				$ae = $this->byXPath( '//*[@id="welcome_button_next"]' );
				Debug::text( 'Active Element Text: ' . $ae->text(), __FILE__, __LINE__, __METHOD__, 10 );
				$ae->click();
			} else {
				usleep( 500000 );
				$this->waitUntilByXPath( '//*[@id="payrollDeductionsResults_button_modifyCalculationButton"]' );
				$ae = $this->byXPath( '//*[@id="payrollDeductionsResults_button_modifyCalculationButton"]' ); //Modify the current calculation
				Debug::text( 'Active Element Text: ' . $ae->text(), __FILE__, __LINE__, __METHOD__, 10 );
				$ae->click();
			}

			$province_options = [
					'AB' => 'ALBERTA',
					'BC' => 'BRITISH_COLUMBIA',
					'SK' => 'SASKATCHEWAN',
					'MB' => 'MANITOBA',
					'QC' => 'QUEBEC',
					'ON' => 'ONTARIO',
					'NL' => 'NEWFOUNDLAND_AND_LABRADOR',
					'NB' => 'NEW_BRUNSWICK',
					'NS' => 'NOVA_SCOTIA',
					'PE' => 'PRINCE_EDWARD_ISLAND',
					'NT' => 'NORTHWEST_TERRITORIES',
					'YT' => 'YUKON',
					'NU' => 'NUNAVUT',
			];
			Debug::Arr( Option::getByKey( $args['province'], $province_options ), 'Attempting to Select Province Value: ', __FILE__, __LINE__, __METHOD__, 10 );

			$this->waitUntilByXPath( '//*[@id="jurisdiction"]' );
			$ae = $this->byId( 'jurisdiction' );
			$this->select( $ae )->selectOptionByValue( Option::getByKey( $args['province'], $province_options ) );

			$pp_options = [
					52 => 'WEEKLY_52PP',
					26 => 'BI_WEEKLY',
					24 => 'SEMI_MONTHLY',
			];
			$ae = $this->byId( 'payPeriodFrequency' );
			//$this->select( $ae )->selectOptionByLabel( 'Biweekly (26 pay periods a year)' );
			$this->select( $ae )->selectOptionByValue( Option::getByKey( $args['pay_period_schedule'], $pp_options ) );

			$ae = $this->byId( 'datePaidYear' );
			$this->select( $ae )->selectOptionByLabel( date( 'Y', $args['date'] ) );

			$ae = $this->byId( 'datePaidMonth' );
			$this->select( $ae )->selectOptionByLabel( date( 'm', $args['date'] ) ); //Leading 0

			$ae = $this->byId( 'datePaidDay' );
			$this->select( $ae )->selectOptionByLabel( date( 'd', $args['date'] ) ); //Leading 0

			$ae = $this->byId( 'payrollDeductionsStep1_button_next' );
			Debug::text( 'Active Element Text: ' . $ae->text(), __FILE__, __LINE__, __METHOD__, 10 );
			$ae->click();

			$this->waitUntilByXPath( '//*[@id="incomeAmount"]' );
			usleep( 500000 );
			$ae = $this->byId( 'incomeAmount' );
			$ae->click();
			$this->keys( $args['gross_income'] ); //Sometimes some keystrokes get missed, try putting a wait above here.

			$ae = $this->byId( 'payrollDeductionsStep2a_button_next' );
			Debug::text( 'Active Element Text: ' . $ae->text(), __FILE__, __LINE__, __METHOD__, 10 );
			$ae->click();

			$this->waitUntilByXPath( '//*[@id="federalClaimCode"]' );
			if ( isset( $args['federal_claim'] ) ) {
				$ae = $this->byId( 'federalClaimCode' );
				$this->select( $ae )->selectOptionByValue( ( $args['federal_claim'] == 0 ? 'CLAIM_CODE_0' : 'CLAIM_CODE_1' ) ); //Only support 0=$1, 1=Basic Claim
			}

			if ( isset( $args['provincial_claim'] ) && $args['province'] != 'QC' ) { //QC doesn't have provincial claim code.
				$ae = $this->byId( 'provinceTerritoryClaimCode' );
				$this->select( $ae )->selectOptionByValue( ( $args['provincial_claim'] == 0 ? 'CLAIM_CODE_0' : 'CLAIM_CODE_1' ) ); //Only support 0=$1, 1=Basic Claim
			}

			$result_row_offset = 1;

			if ( isset( $args['ytd_cpp_earnings'] ) ) {
				$ae = $this->byId( 'pensionableEarningsYearToDate' );
				$ae->click();
				$this->keys( $args['ytd_cpp_earnings'] );
			}

			if ( isset( $args['ytd_cpp'] ) ) {
				$ae = $this->byId( 'cppOrQppContributionsDeductedYearToDate' );
				$ae->click();
				$this->keys( $args['ytd_cpp'] );
			}

			if ( isset( $args['ytd_ei_earnings'] ) ) {
				$ae = $this->byId( 'insurableEarningsYearToDate' );
				$ae->click();
				$this->keys( $args['ytd_ei_earnings'] );
			}

			if ( isset( $args['ytd_ei'] ) ) {
				$ae = $this->byId( 'employmentInsuranceDeductedYearToDate' );
				$ae->click();
				$this->keys( $args['ytd_ei'] );
			}

			usleep( 500000 );
			$ae = $this->byId( 'payrollDeductionsStep3_button_calculate' );
			Debug::text( 'Active Element Text: ' . $ae->text(), __FILE__, __LINE__, __METHOD__, 10 );
			$ae->click();

			//
			//Handle results here
			//
			$screenshot_file_name = '/tmp/cra_result_screenshot-' . $args['province'] . '-' . $args['federal_claim'] . '-' . $args['provincial_claim'] . '-' . $args['gross_income'] . '.png';
			file_put_contents( $screenshot_file_name, $this->currentScreenshot() );

			//Make sure the gross income matches first.
			$ae = $this->byXPath( '/html/body/div/div/main/section[2]/table[1]/tbody/tr[1]/td[1]' ); //Was: 1
			Debug::Text( 'AE Text (Gross Income) [1]: ' . $ae->text(), __FILE__, __LINE__, __METHOD__, 10 );
			$ae = $this->byXPath( '/html/body/div/div/main/section[2]/table[1]/tbody/tr[1]/td[3]' );
			Debug::Text( 'AE Text (Gross Income) [1]: ' . $ae->text() . ' Expecting: ' . $args['gross_income'], __FILE__, __LINE__, __METHOD__, 10 );
			//$retarr['gross_inc'] = TTi18n::parseFloat( $ae->text() );
			$this->assertEquals( TTi18n::parseFloat( $ae->text() ), $args['gross_income'] );

			$result_row_offset += 5;

			//Federal Tax
			$ae = $this->byXPath( '/html/body/div/div/main/section[2]/table[1]/tbody/tr[' . $result_row_offset . ']' ); //Was: 7
			Debug::Text( 'AE Text (Federal) [' . $result_row_offset . ']: ' . $ae->text(), __FILE__, __LINE__, __METHOD__, 10 );
			$ae = $this->byXPath( '/html/body/div/div/main/section[2]/table[1]/tbody/tr[' . $result_row_offset . ']/td[2]' );
			Debug::Text( 'AE Text (Federal) [' . $result_row_offset . ']: ' . $ae->text(), __FILE__, __LINE__, __METHOD__, 10 );
			$retarr['federal_deduction'] = TTi18n::parseFloat( $ae->text() );

			$result_row_offset += 1;
			//Provincial Tax
			$ae = $this->byXPath( '/html/body/div/div/main/section[2]/table[1]/tbody/tr[' . $result_row_offset . ']' ); //Was: 8
			Debug::Text( 'AE Text (Province) [' . $result_row_offset . ']: ' . $ae->text(), __FILE__, __LINE__, __METHOD__, 10 );
			$ae = $this->byXPath( '/html/body/div/div/main/section[2]/table[1]/tbody/tr[' . $result_row_offset . ']/td[2]' );
			Debug::Text( 'AE Text (Province) [' . $result_row_offset . ']: ' . $ae->text(), __FILE__, __LINE__, __METHOD__, 10 );
			$retarr['provincial_deduction'] = TTi18n::parseFloat( $ae->text() );

//			$result_row_offset += 2;
//			//CPP
//			$ae = $this->byXPath( "/html/body/div/div/main/table[1]/tbody/tr[" . $result_row_offset . "]" ); //Was: 10
//			Debug::Text( 'AE Text (CPP) [' . $result_row_offset . ']: ' . $ae->text(), __FILE__, __LINE__, __METHOD__, 10 );
//			$ae = $this->byXPath( "/html/body/div/div/main/table[1]/tbody/tr[" . $result_row_offset . "]/td[3]" );
//			Debug::Text( 'AE Text (CPP) [' . $result_row_offset . ']: ' . $ae->text(), __FILE__, __LINE__, __METHOD__, 10 );
//			$retarr['cpp_deduction'] = TTi18n::parseFloat( $ae->text() );
//
//			$result_row_offset += 1;
//			//EI
//			$ae = $this->byXPath( "/html/body/div/div/main/table[1]/tbody/tr[" . $result_row_offset . "]" ); //Was: 11
//			Debug::Text( 'AE Text (EI) [' . $result_row_offset . ']: ' . $ae->text(), __FILE__, __LINE__, __METHOD__, 10 );
//			$ae = $this->byXPath( "/html/body/div/div/main/table[1]/tbody/tr[" . $result_row_offset . "]/td[3]" );
//			Debug::Text( 'AE Text (EI) [' . $result_row_offset . ']: ' . $ae->text(), __FILE__, __LINE__, __METHOD__, 10 );
//			$retarr['ei_deduction'] = TTi18n::parseFloat( $ae->text() );

			//Debug::Arr( $this->source(), 'Raw Source: ', __FILE__, __LINE__, __METHOD__, 10);
			//sleep(5);

			$this->selenium_test_case_runs++;
		} catch ( Exception $e ) {
			Debug::Text( 'Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
			file_put_contents( tempnam( '/tmp/', 'cra_result_screenshot' ) . '.png', $this->currentScreenshot() );
			sleep( 15 );
		}

		if ( isset( $retarr ) ) {
			Debug::Arr( $retarr, 'Retarr: ', __FILE__, __LINE__, __METHOD__, 10 );

			return $retarr;
		}

		Debug::Text( 'ERROR: Returning FALSE!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	//Simple control test to ensure the numbers match for a old year like 2016.
	function testCRAControl() {
		$args = [
				'date'                => strtotime( '01-Jan-2016' ),
				'province'            => 'BC',
				'pay_period_schedule' => 26,
				'federal_claim'       => 10000,
				'provincial_claim'    => 10000,
				'gross_income'        => 9933.99,
		];
		$retarr = $this->CRAPayrollDeductionOnlineCalculator( $args );

		$this->assertEquals( 2428.10, $retarr['federal_deduction'] );
		$this->assertEquals( 1153.70, $retarr['provincial_deduction'] );
		//$this->assertEquals( 485.07, $retarr['cpp_deduction'] );
		//$this->assertEquals( 186.76, $retarr['ei_deduction']  );

		return true;
	}

	public function mf( $amount ) {
		return Misc::MoneyRound( $amount );
	}

	function testCRAToCSVFile() {
		$this->assertEquals( true, file_exists( $this->tax_table_file ) );

		if ( file_exists( $this->cra_deduction_test_csv_file ) ) {
			$file = new SplFileObject( $this->cra_deduction_test_csv_file, 'r' );
			$file->seek( PHP_INT_MAX );

			$total_compare_lines = $file->key() + 1;
			unset( $file );
			Debug::text( 'Found existing CRATest file to resume with lines: ' . $total_compare_lines, __FILE__, __LINE__, __METHOD__, 10 );
		}


		$test_rows = Misc::parseCSV( $this->tax_table_file, true );

		$total_rows = ( count( $test_rows ) + 1 );
		$i = 2;
		foreach ( $test_rows as $row ) {
			if ( isset( $total_compare_lines ) && $i < $total_compare_lines ) {
				Debug::text( '  Skipping to line: ' . $total_compare_lines . '/' . $i, __FILE__, __LINE__, __METHOD__, 10 );
				$i++;
				continue;
			}

			Debug::text( 'Province: ' . $row['province'] . ' Income: ' . $row['gross_income'], __FILE__, __LINE__, __METHOD__, 10 );
			if ( isset( $row['gross_income'] ) && isset( $row['low_income'] ) && isset( $row['high_income'] )
					&& $row['gross_income'] == '' && $row['low_income'] != '' && $row['high_income'] != '' ) {
				$row['gross_income'] = ( $row['low_income'] + ( ( $row['high_income'] - $row['low_income'] ) / 2 ) );
			}

			if ( $row['country'] != '' && $row['gross_income'] != '' ) {
				//echo $i.'/'.$total_rows.'. Testing Province: '. $row['province'] .' Income: '. $row['gross_income'] ."\n";
				Debug::text( $i . '/' . $total_rows . '. Testing Province: ' . $row['province'] . ' Income: ' . $row['gross_income'], __FILE__, __LINE__, __METHOD__, 10 );

				$args = [
						'date'                => strtotime( $row['date'] ),
						'province'            => $row['province'],
						'pay_period_schedule' => 26,
						'federal_claim'       => $row['federal_claim'],
						'provincial_claim'    => $row['provincial_claim'],
						'gross_income'        => $this->mf( $row['gross_income'] ),
				];


				//Debug::Arr( $row, 'aFinal Row: ', __FILE__, __LINE__, __METHOD__, 10);
				$tmp_cra_data = $this->CRAPayrollDeductionOnlineCalculator( $args );
				if ( is_array( $tmp_cra_data ) ) {
					$retarr[] = array_merge( $row, $tmp_cra_data );

					//Debug::Arr( $retarr, 'bFinal Row: ', __FILE__, __LINE__, __METHOD__, 10);
					//sleep(2); //Should we be friendly to the Gov't server?
//					if ( $i > 5 ) {
//						break;
//					}
				} else {
					Debug::text( 'ERROR! Data from CRA is invalid!', __FILE__, __LINE__, __METHOD__, 10 );
					break;
				}
			}

			$i++;
		}

		if ( isset( $retarr ) ) {
			//generate column array.
			$column_keys = array_keys( $retarr[0] );
			foreach ( $column_keys as $column_key ) {
				$columns[$column_key] = $column_key;
			}

			//var_dump($test_data);
			//var_dump($retarr);
			//echo Misc::Array2CSV( $retarr, $columns, FALSE, TRUE );
			file_put_contents( $this->cra_deduction_test_csv_file, Misc::Array2CSV( $retarr, $columns, false, true ), FILE_APPEND );

			//Make sure all rows are tested.
			$this->assertEquals( $total_rows, ( $i - 1 ) );
		} else {
			$this->assertEquals( true, false );
		}
	}

	function testCRAFromCSVFile() {
		$this->assertEquals( true, file_exists( $this->cra_deduction_test_csv_file ) );

		$test_rows = Misc::parseCSV( $this->cra_deduction_test_csv_file, true );

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

				$pd_obj->setEIExempt( false );
				$pd_obj->setCPPExempt( false );

				$pd_obj->setFederalTaxExempt( false );
				$pd_obj->setProvincialTaxExempt( false );

				$pd_obj->setYearToDateCPPContribution( 0 );
				$pd_obj->setYearToDateEIContribution( 0 );

				$pd_obj->setGrossPayPeriodIncome( $this->mf( $row['gross_income'] ) );

				$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), $this->mf( $row['gross_income'] ) );
				if ( $row['federal_deduction'] != '' ) {
					$this->assertEqualsWithDelta( (float)$this->mf( $row['federal_deduction'] ), (float)$this->mf( $pd_obj->getFederalPayPeriodDeductions() ), 0.015, 'I: '. $i .' Gross Income: '. $row['gross_income'] .' Province: '. $row['province'] .' Federal Claim: '. $row['federal_claim'] ); //0.015=Allowed Delta
				}
				if ( $row['provincial_deduction'] != '' ) {
					$this->assertEqualsWithDelta( (float)$this->mf( $row['provincial_deduction'] ), (float)$this->mf( $pd_obj->getProvincialPayPeriodDeductions() ), 0.015, 'I: '. $i .' Gross Income: '. $row['gross_income'] .' Province: '. $row['province'] .' Provincial Claim: '. $row['provincial_claim'] ); //0.015=Allowed Delta
				}
			}

			$i++;
		}

		//Make sure all rows are tested.
		$this->assertEquals( $total_rows, ( $i - 1 ) );
	}
}

?>