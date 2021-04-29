<?php
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
 * @package PayrollDeduction\US
 */
/*
 Need to manually calculate the brackets, as the brackets less than 50,000 include the allowance ( 188 ) in the constant.
 Exclude the allowance from each bracket, then getStateTaxPayable() will add $188 (allowance amount) to the constant if the annual income is less than 50,000.
 Check getStateTaxPayable() for the 50,000 setting.
*/

class PayrollDeduction_US_OR extends PayrollDeduction_US {
	var $original_filing_status = null;

	var $state_income_tax_rate_options = [
			20200101 => [
					10 => [
							[ 'income' => 3600, 'rate' => 4.75, 'constant' => 0 ],
							[ 'income' => 9050, 'rate' => 6.75, 'constant' => 171 ],
							[ 'income' => 125000, 'rate' => 8.75, 'constant' => 538.88 ],
							[ 'income' => 125000, 'rate' => 9.9, 'constant' => 10684.50 ],
					],
					20 => [
							[ 'income' => 7200, 'rate' => 4.75, 'constant' => 0 ],
							[ 'income' => 18100, 'rate' => 6.75, 'constant' => 342 ],
							[ 'income' => 250000, 'rate' => 8.75, 'constant' => 1077.75 ],
							[ 'income' => 250000, 'rate' => 9.9, 'constant' => 21369 ],
					],
			],
			20190101 => [
					10 => [
							[ 'income' => 3550, 'rate' => 5, 'constant' => 0 ],
							[ 'income' => 8900, 'rate' => 7, 'constant' => 177.50 ],
							[ 'income' => 125000, 'rate' => 9, 'constant' => 552 ],
							[ 'income' => 125000, 'rate' => 9.9, 'constant' => 11001 ],
					],
					20 => [
							[ 'income' => 7100, 'rate' => 5, 'constant' => 0 ],
							[ 'income' => 17800, 'rate' => 7, 'constant' => 355 ],
							[ 'income' => 250000, 'rate' => 9, 'constant' => 1104 ],
							[ 'income' => 250000, 'rate' => 9.9, 'constant' => 22002 ],
					],
			],
			20180101 => [
					10 => [
							[ 'income' => 3450, 'rate' => 5, 'constant' => 0 ],
							[ 'income' => 8700, 'rate' => 7, 'constant' => 172.5 ],
							[ 'income' => 125000, 'rate' => 9, 'constant' => 540 ],
							[ 'income' => 125000, 'rate' => 9.9, 'constant' => 11007 ],
					],
					20 => [
							[ 'income' => 6900, 'rate' => 5, 'constant' => 0 ],
							[ 'income' => 17400, 'rate' => 7, 'constant' => 345 ],
							[ 'income' => 250000, 'rate' => 9, 'constant' => 1080 ],
							[ 'income' => 250000, 'rate' => 9.9, 'constant' => 22014 ],
					],
			],
			20170101 => [
					10 => [
							[ 'income' => 3400, 'rate' => 5, 'constant' => 0 ],
							[ 'income' => 8500, 'rate' => 7, 'constant' => 170 ],
							[ 'income' => 125000, 'rate' => 9, 'constant' => 527 ],
							[ 'income' => 125000, 'rate' => 9.9, 'constant' => 11012 ],
					],
					20 => [
							[ 'income' => 6800, 'rate' => 5, 'constant' => 0 ],
							[ 'income' => 17000, 'rate' => 7, 'constant' => 340 ],
							[ 'income' => 250000, 'rate' => 9, 'constant' => 1054 ],
							[ 'income' => 250000, 'rate' => 9.9, 'constant' => 22024 ],
					],
			],
			20160101 => [
					10 => [
							[ 'income' => 3350, 'rate' => 5, 'constant' => 0 ],
							[ 'income' => 8450, 'rate' => 7, 'constant' => 167.50 ],
							[ 'income' => 125000, 'rate' => 9, 'constant' => 524.50 ],
							[ 'income' => 125000, 'rate' => 9.9, 'constant' => 11014 ],
					],
					20 => [
							[ 'income' => 6700, 'rate' => 5, 'constant' => 0 ],
							[ 'income' => 16900, 'rate' => 7, 'constant' => 335 ],
							[ 'income' => 250000, 'rate' => 9, 'constant' => 1049 ],
							[ 'income' => 250000, 'rate' => 9.9, 'constant' => 22028 ],
					],
			],
			20150101 => [
					10 => [
							[ 'income' => 3350, 'rate' => 5, 'constant' => 0 ],
							[ 'income' => 8400, 'rate' => 7, 'constant' => 167.50 ],
							[ 'income' => 125000, 'rate' => 9, 'constant' => 521 ],
							[ 'income' => 125000, 'rate' => 9.9, 'constant' => 11015 ],
					],
					20 => [
							[ 'income' => 6700, 'rate' => 5, 'constant' => 0 ],
							[ 'income' => 16800, 'rate' => 7, 'constant' => 335 ],
							[ 'income' => 250000, 'rate' => 9, 'constant' => 1042 ],
							[ 'income' => 250000, 'rate' => 9.9, 'constant' => 22030 ],
					],
			],
			20140101 => [
					10 => [
							[ 'income' => 3300, 'rate' => 5, 'constant' => 0 ],
							[ 'income' => 8250, 'rate' => 7, 'constant' => 165 ],
							[ 'income' => 125000, 'rate' => 9, 'constant' => 512 ],
							[ 'income' => 125000, 'rate' => 9.9, 'constant' => 11019 ],
					],
					20 => [
							[ 'income' => 6600, 'rate' => 5, 'constant' => 0 ],
							[ 'income' => 16500, 'rate' => 7, 'constant' => 330 ],
							[ 'income' => 250000, 'rate' => 9, 'constant' => 1023 ],
							[ 'income' => 250000, 'rate' => 9.9, 'constant' => 22038 ],
					],
			],
			20130101 => [
					10 => [
							[ 'income' => 3250, 'rate' => 5, 'constant' => 0 ],
							[ 'income' => 8150, 'rate' => 7, 'constant' => 163 ],
							[ 'income' => 125000, 'rate' => 9, 'constant' => 506 ],
							[ 'income' => 125000, 'rate' => 9.9, 'constant' => 11022 ],
					],
					20 => [
							[ 'income' => 6500, 'rate' => 5, 'constant' => 0 ],
							[ 'income' => 16300, 'rate' => 7, 'constant' => 325 ],
							[ 'income' => 250000, 'rate' => 9, 'constant' => 1011 ],
							[ 'income' => 250000, 'rate' => 9.9, 'constant' => 22044 ],
					],
			],
			20120101 => [
					10 => [
							[ 'income' => 3150, 'rate' => 5, 'constant' => 183 ],
							[ 'income' => 7950, 'rate' => 7, 'constant' => 341 ],
							[ 'income' => 50000, 'rate' => 9, 'constant' => 677 ],
							[ 'income' => 125000, 'rate' => 9, 'constant' => 494 ],
							[ 'income' => 125000, 'rate' => 9.9, 'constant' => 11028 ],
					],
					20 => [
							[ 'income' => 6300, 'rate' => 5, 'constant' => 183 ],
							[ 'income' => 15900, 'rate' => 7, 'constant' => 498 ],
							[ 'income' => 50000, 'rate' => 9, 'constant' => 1170 ],
							[ 'income' => 250000, 'rate' => 9, 'constant' => 987 ],
							[ 'income' => 250000, 'rate' => 9.9, 'constant' => 22056 ],
					],
			],
			20070101 => [
					10 => [
							[ 'income' => 2850, 'rate' => 5, 'constant' => 0 ],
							[ 'income' => 7150, 'rate' => 7, 'constant' => 143 ],
							[ 'income' => 7150, 'rate' => 9, 'constant' => 444 ],
					],
					20 => [
							[ 'income' => 5700, 'rate' => 5, 'constant' => 0 ],
							[ 'income' => 14300, 'rate' => 7, 'constant' => 285 ],
							[ 'income' => 14300, 'rate' => 9, 'constant' => 887 ],
					],
			],
			20060101 => [
					10 => [
							[ 'income' => 300, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 8030, 'rate' => 7, 'constant' => 0 ],
							[ 'income' => 8030, 'rate' => 9, 'constant' => 541 ],
					],
					20 => [
							[ 'income' => 2725, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 16065, 'rate' => 7, 'constant' => 0 ],
							[ 'income' => 16065, 'rate' => 9, 'constant' => 934 ],
					],
			],
	];

	var $state_options = [
			2020101  => [
					'standard_deduction'  => [
							'10' => 2315,
							'20' => 4630,
					],
					'allowance'           => 210,
					'federal_tax_maximum' => 6950,
					'phase_out'           => [
							'10' => [
									50000  => 6950,
									125000 => 6950,
									130000 => 5550,
									135000 => 4150,
									140000 => 2750,
									145000 => 1350,
									145000 => 0,
							],
							'20' => [
									50000  => 6950,
									250000 => 6950,
									260000 => 5550,
									270000 => 4150,
									280000 => 2750,
									290000 => 1350,
									290000 => 0,
							],
					],
			],
			2019101  => [
					'standard_deduction'  => [
							'10' => 2270,
							'20' => 4545,
					],
					'allowance'           => 206,
					'federal_tax_maximum' => 6800,
					'phase_out'           => [
							'10' => [
									50000  => 6800,
									125000 => 6800,
									130000 => 5450,
									135000 => 4100,
									140000 => 2700,
									145000 => 1350,
									145000 => 0,
							],
							'20' => [
									50000  => 6800,
									250000 => 6800,
									260000 => 5450,
									270000 => 4100,
									280000 => 2700,
									290000 => 1350,
									290000 => 0,
							],
					],
			],
			2018101  => [
					'standard_deduction'  => [
							'10' => 2215,
							'20' => 4435,
					],
					'allowance'           => 201,
					'federal_tax_maximum' => 6650,
					'phase_out'           => [
							'10' => [
									50000  => 6650,
									125000 => 6650,
									130000 => 5300,
									135000 => 4000,
									140000 => 2650,
									145000 => 1300,
									145000 => 0,
							],
							'20' => [
									50000  => 6650,
									250000 => 6650,
									260000 => 5300,
									270000 => 4000,
									280000 => 2650,
									290000 => 1300,
									290000 => 0,
							],
					],
			],
			20170101 => [
					'standard_deduction'  => [
							'10' => 2175,
							'20' => 4350,
					],
					'allowance'           => 197,
					'federal_tax_maximum' => 6550,
					'phase_out'           => [
							'10' => [
									50000  => 6550,
									125000 => 6550,
									130000 => 5200,
									135000 => 3900,
									140000 => 2600,
									145000 => 1300,
									145000 => 0,
							],
							'20' => [
									50000  => 6550,
									250000 => 6550,
									260000 => 5200,
									270000 => 3900,
									280000 => 2600,
									290000 => 1300,
									290000 => 0,
							],
					],
			],
			20160101 => [
					'standard_deduction'  => [
							'10' => 2155,
							'20' => 4315,
					],
					'allowance'           => 195,
					'federal_tax_maximum' => 6500,
					'phase_out'           => [
							'10' => [
									50000  => 6500,
									125000 => 6500,
									130000 => 5200,
									135000 => 3900,
									140000 => 2600,
									145000 => 1300,
									145000 => 0,
							],
							'20' => [
									50000  => 6500,
									250000 => 6500,
									260000 => 5200,
									270000 => 3900,
									280000 => 2600,
									290000 => 1300,
									290000 => 0,
							],
					],
			],
			20150101 => [
					'standard_deduction'  => [
							'10' => 2145,
							'20' => 4295,
					],
					'allowance'           => 194,
					'federal_tax_maximum' => 6450,
					'phase_out'           => [
							'10' => [
									50000  => 6450,
									125000 => 6450,
									130000 => 5150,
									135000 => 3850,
									140000 => 2550,
									145000 => 1250,
									145000 => 0,
							],
							'20' => [
									50000  => 6450,
									250000 => 6450,
									260000 => 5150,
									270000 => 3850,
									280000 => 2550,
									290000 => 1250,
									290000 => 0,
							],
					],
			],
			20140101 => [
					'standard_deduction'  => [
							'10' => 2115,
							'20' => 4230,
					],
					'allowance'           => 191,
					'federal_tax_maximum' => 6350,
					'phase_out'           => [
							'10' => [
									50000  => 6350,
									125000 => 6350,
									130000 => 5050,
									135000 => 3800,
									140000 => 2500,
									145000 => 1250,
									145000 => 0,
							],
							'20' => [
									50000  => 6350,
									250000 => 6350,
									260000 => 5050,
									270000 => 3800,
									280000 => 2500,
									290000 => 1250,
									290000 => 0,
							],
					],
			],
			20130101 => [
					'standard_deduction'  => [
							'10' => 2080,
							'20' => 4160,
					],
					'allowance'           => 188,
					'federal_tax_maximum' => 6250,
					'phase_out'           => [
							'10' => [
									50000  => 6250,
									125000 => 6250,
									130000 => 5000,
									135000 => 3750,
									140000 => 2500,
									145000 => 1250,
									145000 => 0,
							],
							'20' => [
									50000  => 6250,
									250000 => 6250,
									260000 => 5000,
									270000 => 3750,
									280000 => 2500,
									290000 => 1250,
									290000 => 0,
							],
					],
			],
			20120101 => [
					'standard_deduction'  => [
							'10' => 2025,
							'20' => 4055,
					],
					'allowance'           => 183,
					'federal_tax_maximum' => 6100,
					'phase_out'           => [
							'10' => [
									50000  => 6100,
									125000 => 6100,
									130000 => 4850,
									135000 => 3650,
									140000 => 2400,
									145000 => 1200,
									145000 => 0,
							],
							'20' => [
									50000  => 6100,
									250000 => 6100,
									260000 => 4850,
									270000 => 3650,
									280000 => 2400,
									290000 => 1200,
									290000 => 0,
							],
					],
			],
			20100101 => [
					'standard_deduction'  => [
							'10' => 1950,
							'20' => 3900,
					],
					'allowance'           => 177,
					'federal_tax_maximum' => 5850,
			],
			20090101 => [
					'standard_deduction'  => [
							'10' => 1945,
							'20' => 3895,
					],
					'allowance'           => 176,
					'federal_tax_maximum' => 5850,
			],
			20070101 => [
					'standard_deduction'  => [
							'10' => 1870,
							'20' => 3740,
					],
					'allowance'           => 165,
					'federal_tax_maximum' => 5500,
			],
			20060101 => [
					'standard_deduction'  => [
							'10' => 0,
							'20' => 0,
					],
					'allowance'           => 154,
					'federal_tax_maximum' => 4500,
			],
	];

	private function getStateRateArray( $input_arr, $income ) {
		if ( !is_array( $input_arr ) ) {
			return 0;
		}

		$total_rates = ( count( $input_arr ) - 1 );
		$prev_bracket = 0;
		$i = 0;
		foreach ( $input_arr as $bracket => $value ) {
//			Debug::text( 'Bracket: ' . $bracket . ' Value: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

			if ( $income >= $prev_bracket && $income < $bracket ) {
				Debug::text( 'Found Bracket: ' . $bracket . ' Returning: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

				return $value;
			} else if ( $i == $total_rates ) {
				Debug::text( 'Found Last Bracket: ' . $bracket . ' Returning: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

				return $value;
			}

			$prev_bracket = $bracket;
			$i++;
		}

		return false;
	}

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$federal_tax = $this->getFederalTaxPayable();

		if ( $federal_tax > $this->getStateFederalTaxMaximum() ) {
			$federal_tax = $this->getStateFederalTaxMaximum();
		}

		$income = bcsub( bcsub( $annual_income, $federal_tax ), $this->getStateStandardDeduction() );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}

	function getStateFederalTaxMaximum() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$maximum = $retarr['federal_tax_maximum'];

		if ( isset( $retarr['phase_out'][$this->getStateFilingStatus()] ) ) {
			$phase_out_arr = $retarr['phase_out'][$this->getStateFilingStatus()];
			$phase_out_maximum = $this->getStateRateArray( $phase_out_arr, $this->getAnnualTaxableIncome() );
			if ( $maximum > $phase_out_maximum ) {
				Debug::text( 'Maximum allowed Federal Tax exceeded phase out maximum of: ' . $phase_out_maximum, __FILE__, __LINE__, __METHOD__, 10 );
				$maximum = $phase_out_maximum;
			}
		}

		Debug::text( 'Maximum State allowed Federal Tax: ' . $maximum, __FILE__, __LINE__, __METHOD__, 10 );

		return $maximum;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		if ( $this->original_filing_status == $this->getStateFilingStatus() && isset( $retarr['standard_deduction'][$this->getStateFilingStatus()] ) ) {
			$deduction = $retarr['standard_deduction'][$this->getStateFilingStatus()];
		} else {
			$deduction = $retarr['standard_deduction'][10];
		}

		Debug::text( 'Standard Deduction: ' . $deduction, __FILE__, __LINE__, __METHOD__, 10 );

		return $deduction;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$allowance_arr = $retarr['allowance'];

		$retval = bcmul( $this->getStateAllowance(), $allowance_arr );

		Debug::text( 'State Allowance Amount: ' . $retval . ' Allowances: ' . $this->getStateAllowance(), __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _getStateTaxPayable() {
		//IF exemptions are 3 or more, change filing status to married.
		$this->original_filing_status = $this->getStateFilingStatus();

		if ( $this->getStateFilingStatus() == 10 && $this->getStateAllowance() >= 3 ) {
			Debug::text( 'Forcing to Married Filing Status from: ' . $this->getStateAllowance(), __FILE__, __LINE__, __METHOD__, 10 );
			$this->setStateFilingStatus( 20 ); //Married tax rates.
		}

		$annual_income = $this->getStateAnnualTaxableIncome();

		if ( $this->getDate() >= 20170101 && ( ( $this->original_filing_status == 10 && $annual_income > 100000 ) || ( $this->original_filing_status == 20 && $annual_income > 200000 ) ) ) {
			Debug::text( 'Income over the 100,000 or 200,000 threshold, forcing allowances to 0.', __FILE__, __LINE__, __METHOD__, 10 );
			$this->setStateAllowance( 0 );
		}

		$retval = 0;

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getStateRate( $annual_income );
			$state_constant = $this->getData()->getStateConstant( $annual_income );
			if ( $this->getDate() >= 20120101 && $annual_income < 50000 ) { //01-Jan-2012 (was 2011?)
				$state_array = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
				$state_constant += $state_array['allowance'];
			}
			$state_rate_income = $this->getData()->getStateRatePreviousIncome( $annual_income );

			$retval = bcadd( bcmul( bcsub( $annual_income, $state_rate_income ), $rate ), $state_constant );
		}

		$retval = bcsub( $retval, $this->getStateAllowanceAmount() );

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
