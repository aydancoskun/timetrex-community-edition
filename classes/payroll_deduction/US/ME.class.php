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
class PayrollDeduction_US_ME extends PayrollDeduction_US {

	var $state_income_tax_rate_options = [
			20200101 => [
					10 => [
							[ 'income' => 22200, 'rate' => 5.8, 'constant' => 0 ],
							[ 'income' => 52600, 'rate' => 6.75, 'constant' => 1288 ],
							[ 'income' => 52600, 'rate' => 7.15, 'constant' => 3340 ],
					],
					20 => [
							[ 'income' => 44450, 'rate' => 5.8, 'constant' => 0 ],
							[ 'income' => 105200, 'rate' => 6.75, 'constant' => 2578 ],
							[ 'income' => 105200, 'rate' => 7.15, 'constant' => 6679 ],
					],
			],
			20190101 => [
					10 => [
							[ 'income' => 21850, 'rate' => 5.8, 'constant' => 0 ],
							[ 'income' => 51700, 'rate' => 6.75, 'constant' => 1267 ],
							[ 'income' => 51700, 'rate' => 7.15, 'constant' => 3282 ],
					],
					20 => [
							[ 'income' => 43700, 'rate' => 5.8, 'constant' => 0 ],
							[ 'income' => 103400, 'rate' => 6.75, 'constant' => 2535 ],
							[ 'income' => 103400, 'rate' => 7.15, 'constant' => 6565 ],
					],
			],
			20180101 => [
					10 => [
							[ 'income' => 21450, 'rate' => 5.8, 'constant' => 0 ],
							[ 'income' => 50750, 'rate' => 6.75, 'constant' => 1244 ],
							[ 'income' => 50750, 'rate' => 7.15, 'constant' => 3222 ],
					],
					20 => [
							[ 'income' => 42900, 'rate' => 5.8, 'constant' => 0 ],
							[ 'income' => 101550, 'rate' => 6.75, 'constant' => 2488 ],
							[ 'income' => 101550, 'rate' => 7.15, 'constant' => 6447 ],
					],
			],
			20170801 => [
					10 => [
							[ 'income' => 21100, 'rate' => 5.8, 'constant' => 0 ],
							[ 'income' => 50000, 'rate' => 6.75, 'constant' => 1224 ],
							[ 'income' => 50000, 'rate' => 7.15, 'constant' => 3175 ],
					],
					20 => [
							[ 'income' => 42250, 'rate' => 5.8, 'constant' => 0 ],
							[ 'income' => 100000, 'rate' => 6.75, 'constant' => 2451 ],
							[ 'income' => 100000, 'rate' => 7.15, 'constant' => 6349 ],
					],
			],
			20170101 => [
					10 => [
							[ 'income' => 21100, 'rate' => 5.8, 'constant' => 0 ],
							[ 'income' => 50000, 'rate' => 6.75, 'constant' => 1224 ],
							[ 'income' => 200001, 'rate' => 7.15, 'constant' => 3175 ],
							[ 'income' => 200001, 'rate' => 10.15, 'constant' => 13900 ],
					],
					20 => [
							[ 'income' => 42250, 'rate' => 5.8, 'constant' => 0 ],
							[ 'income' => 100000, 'rate' => 6.75, 'constant' => 2451 ],
							[ 'income' => 200001, 'rate' => 7.15, 'constant' => 6349 ],
							[ 'income' => 200001, 'rate' => 10.15, 'constant' => 13499 ],
					],
			],
			20160101 => [
					10 => [
							[ 'income' => 8750, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 29800, 'rate' => 5.8, 'constant' => 0 ],
							[ 'income' => 46250, 'rate' => 6.75, 'constant' => 1221 ],
							[ 'income' => 46250, 'rate' => 7.15, 'constant' => 2331 ],
					],
					20 => [
							[ 'income' => 20350, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 62450, 'rate' => 5.8, 'constant' => 0 ],
							[ 'income' => 95350, 'rate' => 6.75, 'constant' => 2442 ],
							[ 'income' => 95350, 'rate' => 7.15, 'constant' => 4663 ],
					],
			],
			20150101 => [
					10 => [
							[ 'income' => 8650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 24350, 'rate' => 6.5, 'constant' => 0 ],
							[ 'income' => 24350, 'rate' => 7.95, 'constant' => 1020.50 ],
					],
					20 => [
							[ 'income' => 20200, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 51600, 'rate' => 6.5, 'constant' => 0 ],
							[ 'income' => 51600, 'rate' => 7.95, 'constant' => 2041 ],
					],
			],
			20140101 => [
					10 => [
							[ 'income' => 8550, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 24250, 'rate' => 6.5, 'constant' => 0 ],
							[ 'income' => 24250, 'rate' => 7.95, 'constant' => 1020.50 ],
					],
					20 => [
							[ 'income' => 20000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 51400, 'rate' => 6.5, 'constant' => 0 ],
							[ 'income' => 51400, 'rate' => 7.95, 'constant' => 2041 ],
					],
			],
			20130101 => [
					10 => [
							[ 'income' => 8450, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 24150, 'rate' => 6.5, 'constant' => 0 ],
							[ 'income' => 24150, 'rate' => 7.95, 'constant' => 1021 ],
					],
					20 => [
							[ 'income' => 17750, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 49150, 'rate' => 6.5, 'constant' => 0 ],
							[ 'income' => 49150, 'rate' => 7.95, 'constant' => 2041 ],
					],
			],
			20120101 => [
					10 => [
							[ 'income' => 3100, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 8200, 'rate' => 2.0, 'constant' => 0 ],
							[ 'income' => 13250, 'rate' => 4.5, 'constant' => 102 ],
							[ 'income' => 23450, 'rate' => 7.0, 'constant' => 329 ],
							[ 'income' => 23450, 'rate' => 8.5, 'constant' => 1043 ],
					],
					20 => [
							[ 'income' => 9050, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 19250, 'rate' => 2.0, 'constant' => 0 ],
							[ 'income' => 29400, 'rate' => 4.5, 'constant' => 204 ],
							[ 'income' => 49750, 'rate' => 7.0, 'constant' => 661 ],
							[ 'income' => 49750, 'rate' => 8.5, 'constant' => 2085 ],
					],
			],
			20110101 => [
					10 => [
							[ 'income' => 2950, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 7950, 'rate' => 2.0, 'constant' => 0 ],
							[ 'income' => 12900, 'rate' => 4.5, 'constant' => 100 ],
							[ 'income' => 22900, 'rate' => 7.0, 'constant' => 323 ],
							[ 'income' => 22900, 'rate' => 8.5, 'constant' => 1023 ],
					],
					20 => [
							[ 'income' => 6800, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 16800, 'rate' => 2.0, 'constant' => 0 ],
							[ 'income' => 26750, 'rate' => 4.5, 'constant' => 200 ],
							[ 'income' => 46700, 'rate' => 7.0, 'constant' => 648 ],
							[ 'income' => 46700, 'rate' => 8.5, 'constant' => 2045 ],
					],
			],
			20100101 => [
					10 => [
							[ 'income' => 2850, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 7800, 'rate' => 2.0, 'constant' => 0 ],
							[ 'income' => 12700, 'rate' => 4.5, 'constant' => 99 ],
							[ 'income' => 22600, 'rate' => 7.0, 'constant' => 320 ],
							[ 'income' => 22600, 'rate' => 8.5, 'constant' => 1013 ],
					],
					20 => [
							[ 'income' => 6700, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 16650, 'rate' => 2.0, 'constant' => 0 ],
							[ 'income' => 26450, 'rate' => 4.5, 'constant' => 199 ],
							[ 'income' => 46250, 'rate' => 7.0, 'constant' => 640 ],
							[ 'income' => 46250, 'rate' => 8.5, 'constant' => 2026 ],
					],
			],
			20090101 => [
					10 => [
							[ 'income' => 2850, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 7900, 'rate' => 2.0, 'constant' => 0 ],
							[ 'income' => 12900, 'rate' => 4.5, 'constant' => 101 ],
							[ 'income' => 23000, 'rate' => 7.0, 'constant' => 326 ],
							[ 'income' => 23000, 'rate' => 8.5, 'constant' => 1033 ],
					],
					20 => [
							[ 'income' => 6650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 16800, 'rate' => 2.0, 'constant' => 0 ],
							[ 'income' => 26800, 'rate' => 4.5, 'constant' => 203 ],
							[ 'income' => 47000, 'rate' => 7.0, 'constant' => 653 ],
							[ 'income' => 47000, 'rate' => 8.5, 'constant' => 2067 ],
					],
			],
			20060101 => [
					10 => [
							[ 'income' => 2300, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 6850, 'rate' => 2.0, 'constant' => 0 ],
							[ 'income' => 11400, 'rate' => 4.5, 'constant' => 91 ],
							[ 'income' => 20550, 'rate' => 7.0, 'constant' => 296 ],
							[ 'income' => 20550, 'rate' => 8.5, 'constant' => 936 ],
					],
					20 => [
							[ 'income' => 5750, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 14900, 'rate' => 2.0, 'constant' => 0 ],
							[ 'income' => 24000, 'rate' => 4.5, 'constant' => 183 ],
							[ 'income' => 42300, 'rate' => 7.0, 'constant' => 593 ],
							[ 'income' => 42300, 'rate' => 8.5, 'constant' => 1874 ],
					],
					30 => [
							[ 'income' => 2875, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 7450, 'rate' => 2.0, 'constant' => 0 ],
							[ 'income' => 12000, 'rate' => 4.5, 'constant' => 92 ],
							[ 'income' => 21150, 'rate' => 7.0, 'constant' => 296 ],
							[ 'income' => 21150, 'rate' => 8.5, 'constant' => 937 ],
					],
			],
	];

	var $state_options = [
			20200101 => [
					'allowance'                    => 4300,
					'standard_deduction'           => [
							'10' => 9550,
							'20' => 21950,
					],
					'standard_deduction_threshold' => [
							'10' => [ 82900, 157900, 75000 ], //Min/Max/Divisor
							'20' => [ 165800, 315800, 150000 ], //Min/Max/Divsor
					],
			],
			20190101 => [
					'allowance'                    => 4200,
					'standard_deduction'           => [
							'10' => 9350,
							'20' => 21550,
					],
					'standard_deduction_threshold' => [
							'10' => [ 81450, 156450, 75000 ], //Min/Max/Divisor
							'20' => [ 162950, 312950, 150000 ], //Min/Max/Divsor
					],
			],
			20180101 => [
					'allowance'                    => 4150,
					'standard_deduction'           => [
							'10' => 8950,
							'20' => 20750,
					],
					'standard_deduction_threshold' => [
							'10' => [ 71100, 142200, 75000 ], //Min/Max/Divisor
							'20' => [ 142200, 292200, 150000 ], //Min/Max/Divsor
					],
			],
			20170101 => [ //Standard Deduction formula seems to have changed slightly in 2017.
						  'allowance'                    => 4050,
						  'standard_deduction'           => [
								  '10' => 8750,
								  '20' => 20350,
						  ],
						  'standard_deduction_threshold' => [
								  '10' => [ 70000, 145000, 75000 ], //Min/Max/Divisor
								  '20' => [ 140000, 290000, 150000 ], //Min/Max/Divsor
						  ],
			],
			20160101 => [
					'allowance' => 4050,
			],
			20150101 => [
					'allowance' => 4000,
			],
			20140101 => [
					'allowance' => 3950,
			],
			20130101 => [
					'allowance' => 3900,
			],
			//01-Jan-12: No Change.
			//01-Jan-11: No Change.
			//01-Jan-10: No Change.
			//01-Jan-09: No Change.
			20060101 => [
					'allowance' => 2850,
			],
	];

	function getStatePayPeriodDeductionRoundedValue( $amount ) {
		return $this->RoundNearestDollar( $amount );
	}

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$state_deductions = $this->getStateStandardDeduction();
		$state_allowance = $this->getStateAllowanceAmount();

		$income = bcsub( bcsub( $annual_income, $state_deductions ), $state_allowance );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		if ( !isset( $retarr['standard_deduction'][$this->getStateFilingStatus()] ) ) {
			return false;
		}

		if ( !isset( $retarr['standard_deduction_threshold'][$this->getStateFilingStatus()] ) ) {
			return false;
		}

		$annual_income = $this->getAnnualTaxableIncome();
		$deduction = $retarr['standard_deduction'][$this->getStateFilingStatus()];
		$thresholds = $retarr['standard_deduction_threshold'][$this->getStateFilingStatus()];

		if ( $annual_income <= $thresholds[0] ) {
			$retval = $deduction;
		} else if ( $annual_income >= $thresholds[1] ) {
			$retval = 0;
		} else {
			$retval = bcmul( bcsub( 1, bcdiv( bcsub( $annual_income, $thresholds[0] ), $thresholds[2], 4 ) ), $deduction );
		}

		Debug::text( 'Standard Deduction: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$allowance_arr = $retarr['allowance'];

		$retval = bcmul( $this->getStateAllowance(), $allowance_arr );

		Debug::text( 'State Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _getStateTaxPayable() {
		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getStateRate( $annual_income );
			$state_constant = $this->getData()->getStateConstant( $annual_income );
			$state_rate_income = $this->getData()->getStateRatePreviousIncome( $annual_income );

			$retval = bcadd( bcmul( bcsub( $annual_income, $state_rate_income ), $rate ), $state_constant );
			//$retval = bcadd( bcmul( $annual_income, $rate ), $state_constant );
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
