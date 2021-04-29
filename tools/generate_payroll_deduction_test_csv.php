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

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes/payroll_deduction/PayrollDeduction.class.php' );

if ( $argc < 2 OR in_array( $argv[1], array('--help', '-help', '-h', '-?') ) ) {
	$help_output = "Usage: generate_payroll_deduction_test_csv.php [country_code] [date]\n";
	echo $help_output;
} else {
	$country = strtoupper( $argv[1] );
	$effective_date = strtotime( $argv[2] );

	$cf = new CompanyFactory();
	$province_arr = $cf->getOptions( 'province' );

	$province_arr['US']['00'] = 'NONE';//Make an option for Federal only.

	if ( !isset( $province_arr[ $country ] ) ) {
		echo "Country does not have any province/states.\n";
	}
	ksort( $province_arr[ $country ] );


	$pay_periods = 26;
	$static_test_data = array(
			'CA' => array(
					'income'           => array(
							192, //5000/year
							384, //10000/year
							961, //25000/year
							1923, //50000,
							3846, //100000,
							6223, //180000, //Should be in the middle of the BPAF brackets.
							9615, //250000
					),
					'federal_claim'    => array(0, 100), //Use lowest non-zero value.
					'provincial_claim' => array(0, 100), //Use lowest non-zero value.
			),
			'US' => array(
					'income'                   => array(
							192, //5000/year
							384, //10000/year
							961, //25000/year
							1923, //50000,
							3846, //100000,
							9615, //250000
							76923, //2000000 (For states with higher tax brackets)
					),

					//2020 Federal W2 variables.
					'filing_status'            => array(10, 20, 40), //Federal filing statuses.
					'federal_form_w4_version'  => array(2019, 2020),
					'federal_claim_dependents' => array(0, 2500, 5000),
					'federal_other_income'     => array(0, 10000),
					'federal_other_deductions' => array(0, 1000),

					'allowance' => array(0, 1, 2, 3, 5),
			),
	);

	$test_data = array();

	if ( $country != '' AND isset( $province_arr[ $country ] ) AND $effective_date != '' ) {
		foreach ( $province_arr[ $country ] as $province_code => $province ) {
			//echo "Province: $province_code\n";
			$raw_result = array();

			$pd_obj = new PayrollDeduction( $country, $province_code );

			//echo 'Tax Bracket Rows: '. $result->RecordCount() ."\n";
			if ( $country == 'US' ) { //US
				if ( isset( $pd_obj->obj->state_income_tax_rate_options ) ) {
					$raw_result = $pd_obj->obj->getDataFromRateArray( $effective_date, $pd_obj->obj->state_income_tax_rate_options );
				}

				$result = array();
				foreach ( $raw_result as $raw_filing_status => $row_a ) {
					foreach ( $row_a as $row ) {
						$row['status'] = ( $raw_filing_status == 0 ) ? 10 : $raw_filing_status;
						$result[] = $row;
					}
				}
				unset( $raw_result, $raw_filing_status, $row_a, $row );

				if ( count( $result ) == 0 ) {
					//Use static test rates.

					$test_data[ $country ][ $province_code ] = $static_test_data[ $country ];
					if ( $province_code != '00' ) {
						$test_data[ $country ][ $province_code ]['filing_status'] = array(10); //No tax brackets, only use a single filing status.
						$test_data[ $country ][ $province_code ]['allowance'] = array(0); //No tax brackets, only use 0 for allowances as it likely doesn't matter.
					}
				} else {
					//Always include the same income brackets for testing, AS WELL as one to test each individual bracket.
					$test_data[ $country ][ $province_code ] = $static_test_data[ $country ];

					$i = 1;
					$prev_income = null;
					$prev_status = null;
					$prev_province = null;
					foreach ( $result as $tax_row ) {
						//Test $100 less then the first bracket, and $100 more then all other brackets for each status.
						$income = round( ( $tax_row['income'] / $pay_periods ) );
						$variance = round( ( 100 / $pay_periods ) );

						if ( $prev_income == null OR $prev_income > $income ) {
							//echo "First bracket! $country $province ".$tax_row['income']." T: ". ($tax_row['income']-$variance) ."\n";
							$test_data[ $country ][ $province_code ]['income'][] = ( $income - $variance );
							$test_data[ $country ][ $province_code ]['filing_status'][] = $tax_row['status'];
						}

						$test_data[ $country ][ $province_code ]['income'][] = ( $income + $variance );
						$test_data[ $country ][ $province_code ]['filing_status'][] = $tax_row['status'];
						$test_data[ $country ][ $province_code ]['allowance'] = $static_test_data[ $country ]['allowance'];

						$test_data[ $country ][ $province_code ]['income'] = array_unique( $test_data[ $country ][ $province_code ]['income'] );
						$test_data[ $country ][ $province_code ]['filing_status'] = array_unique( $test_data[ $country ][ $province_code ]['filing_status'] );

						$prev_income = $income;
						$prev_status = $tax_row['status'];
						$prev_province = $province_code;
						$i++;
						unset( $income );
					}
				}

				if ( $province_code != '00' ) {
					$test_data[ $country ][ $province_code ]['federal_form_w4_version'] = array(2020);
					$test_data[ $country ][ $province_code ]['federal_claim_dependents'] = array(0);
					$test_data[ $country ][ $province_code ]['federal_other_income'] = array(0);
					$test_data[ $country ][ $province_code ]['federal_other_deductions'] = array(0);
				}

				foreach ( $test_data[ $country ][ $province_code ]['filing_status'] as $filing_status ) {
					foreach ( $test_data[ $country ][ $province_code ]['allowance'] as $allowance ) {
						foreach ( $test_data[ $country ][ $province_code ]['federal_form_w4_version'] as $federal_form_w4_version ) {
							foreach ( $test_data[ $country ][ $province_code ]['federal_claim_dependents'] as $federal_claim_dependents ) {
								foreach ( $test_data[ $country ][ $province_code ]['federal_other_income'] as $federal_other_income ) {
									foreach ( $test_data[ $country ][ $province_code ]['federal_other_deductions'] as $federal_other_deductions ) {
										foreach ( $test_data[ $country ][ $province_code ]['income'] as $income ) {
											$pd_obj = new PayrollDeduction( $country, ( ( $province_code == '00' ) ? 'AK' : $province_code ) ); //Valid state is needed to calculate something, even for just federal numbers.
											$pd_obj->setDate( $effective_date );
											$pd_obj->setAnnualPayPeriods( $pay_periods );

											//Federal
											$pd_obj->setFederalFormW4Version( $federal_form_w4_version );
											$pd_obj->setFederalFilingStatus( $filing_status );
											$pd_obj->setFederalAllowance( $allowance );
											$pd_obj->setFederalMultipleJobs( false ); //2020 or newer W4 settings.
											$pd_obj->setFederalClaimDependents( $federal_claim_dependents );
											$pd_obj->setFederalOtherIncome( $federal_other_income );
											$pd_obj->setFederalDeductions( $federal_other_deductions );
											$pd_obj->setFederalAdditionalDeduction( 0 );
											$pd_obj->setProvincialTaxExempt( false );

											//State
											$pd_obj->setStateFilingStatus( $filing_status );
											$pd_obj->setStateAllowance( $allowance );
											$pd_obj->setFederalTaxExempt( false );

											switch ( $province_code ) {
												case 'GA':
													$pd_obj->setUserValue3( $allowance );
													break;
												case 'IN':
												case 'IL':
												case 'VA':
													$pd_obj->setUserValue1( $allowance );
													break;
											}

											$pd_obj->setGrossPayPeriodIncome( $income );

											//echo 'State: '. $province_code .' Income: '. $income .' Claim Dependents: '. $federal_claim_dependents .' Other Income: '. $federal_other_income ."\n";
											//flush();
											//ob_flush();

											$retarr[] = array(
													'country'                  => $country,
													'province'                 => $province_code,
													'date'                     => date( 'm/d/y', $effective_date ),
													'pay_periods'              => $pay_periods,
													'filing_status'            => $filing_status,
													'allowance'                => $allowance,
													'federal_form_w4_version'  => $federal_form_w4_version,
													'federal_claim_dependents' => $federal_claim_dependents,
													'federal_other_income'     => $federal_other_income,
													'federal_other_deductions' => $federal_other_deductions,
													'gross_income'             => $income,
													'federal_deduction'        => Misc::MoneyRound( $pd_obj->getFederalPayPeriodDeductions() ),
													'provincial_deduction'     => Misc::MoneyRound( $pd_obj->getStatePayPeriodDeductions() ),
											);
										}
									}
								}
							}
						}
					}
				}
			} else if ( $country == 'CA' ) { //Canada
				$result = array();
				if ( isset( $pd_obj->obj->provincial_income_tax_rate_options ) ) {
					$result = $pd_obj->obj->getDataFromRateArray( $effective_date, $pd_obj->obj->provincial_income_tax_rate_options );
				}

				if ( count( $result ) == 0 ) {
					//Use static test rates.
					$test_data[ $country ][ $province_code ] = $static_test_data[ $country ];
				} else {
					$test_data[ $country ][ $province_code ] = $static_test_data[ $country ];

					$i = 1;
					$prev_income = null;
					$prev_status = null;
					$prev_province = null;
					foreach ( $result as $tax_row ) {
						if ( $tax_row['income'] == 0 ) {
							continue;
						}

						//Test $100 less then the first bracket, and $100 more then all other brackets for each status.
						$income = round( $tax_row['income'] / $pay_periods );
						$variance = round( 100 / $pay_periods );

						if ( $prev_income == null OR $prev_income > $income ) {
							//echo "First bracket! $country $province ".$tax_row['income']." T: ". ($tax_row['income']-$variance) ."\n";
							$test_data[ $country ][ $province_code ]['income'][] = $income - $variance;
						}

						$test_data[ $country ][ $province_code ]['income'][] = $income + $variance;
						$test_data[ $country ][ $province_code ]['federal_claim'] = $static_test_data[ $country ]['federal_claim'];
						$test_data[ $country ][ $province_code ]['provincial_claim'] = $static_test_data[ $country ]['provincial_claim'];

						$test_data[ $country ][ $province_code ]['income'] = array_unique( $test_data[ $country ][ $province_code ]['income'] );

						$prev_income = $income;
						$prev_status = ( isset( $tax_row['status'] ) ) ? $tax_row['status'] : null;
						$prev_province = $province_code;
						$i++;
						unset( $income );
					}
				}

				foreach ( $test_data[ $country ][ $province_code ]['provincial_claim'] as $provincial_claim ) {
					foreach ( $test_data[ $country ][ $province_code ]['federal_claim'] as $federal_claim ) {
						foreach ( $test_data[ $country ][ $province_code ]['income'] as $income ) {
							$pd_obj = new PayrollDeduction( $country, $province_code );
							$pd_obj->setDate( $effective_date );
							$pd_obj->setAnnualPayPeriods( $pay_periods );
							$pd_obj->setEnableCPPAndEIDeduction( true ); //Deduct CPP/EI.

							$pd_obj->setFederalTotalClaimAmount( $federal_claim );
							$pd_obj->setProvincialTotalClaimAmount( $provincial_claim );

							$pd_obj->setEIExempt( false );
							$pd_obj->setCPPExempt( false );

							$pd_obj->setFederalTaxExempt( false );
							$pd_obj->setProvincialTaxExempt( false );

							$pd_obj->setYearToDateCPPContribution( 0 );
							$pd_obj->setYearToDateEIContribution( 0 );

							$pd_obj->setGrossPayPeriodIncome( $income );

							$retarr[] = array(
									'country'              => $country,
									'province'             => $province_code,
									'date'                 => date( 'm/d/y', $effective_date ),
									'pay_periods'          => $pay_periods,
									'federal_claim'        => $pd_obj->getFederalTotalClaimAmount(),
									'provincial_claim'     => $pd_obj->getProvincialTotalClaimAmount(),
									'gross_income'         => $income,
									'federal_deduction'    => Misc::MoneyRound( $pd_obj->getFederalPayPeriodDeductions() ),
									'provincial_deduction' => Misc::MoneyRound( $pd_obj->getProvincialPayPeriodDeductions() ),
							);
						}
					}
				}
			}
		}

		//generate column array.
		$column_keys = array_keys( $retarr[0] );
		foreach ( $column_keys as $column_key ) {
			$columns[ $column_key ] = $column_key;
		}

		//var_dump($test_data);
		//var_dump($retarr);
		echo Misc::Array2CSV( $retarr, $columns, false, $include_header = true );
	}
}
//Debug::Display();
?>
