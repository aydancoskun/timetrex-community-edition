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
class PayrollDeduction_US_AL extends PayrollDeduction_US {
	/*
		protected $state_al_filing_status_options = array(
															10 => 'Status "S" Claiming $1500',
															20 => 'Status "M" Claiming $3000',
															30 => 'Status "0"',
															40 => 'Head of Household'
															50 => 'Status "MS"'
										);
	*/
	var $state_income_tax_rate_options = [
			20120101 => [
					10 => [
							[ 'income' => 500, 'rate' => 2, 'constant' => 0 ],
							[ 'income' => 2500, 'rate' => 4, 'constant' => 10 ],
							[ 'income' => 3000, 'rate' => 5, 'constant' => 90 ],
					],
					20 => [
							[ 'income' => 1000, 'rate' => 2, 'constant' => 0 ],
							[ 'income' => 5000, 'rate' => 4, 'constant' => 20 ],
							[ 'income' => 6000, 'rate' => 5, 'constant' => 180 ],
					],
					30 => [
							[ 'income' => 500, 'rate' => 2, 'constant' => 0 ],
							[ 'income' => 2500, 'rate' => 4, 'constant' => 10 ],
							[ 'income' => 3000, 'rate' => 5, 'constant' => 90 ],
					],
					40 => [
							[ 'income' => 500, 'rate' => 2, 'constant' => 0 ],
							[ 'income' => 2500, 'rate' => 4, 'constant' => 10 ],
							[ 'income' => 3000, 'rate' => 5, 'constant' => 90 ],
					],
					50 => [
							[ 'income' => 500, 'rate' => 2, 'constant' => 0 ],
							[ 'income' => 2500, 'rate' => 4, 'constant' => 10 ],
							[ 'income' => 3000, 'rate' => 5, 'constant' => 90 ],
					],
			],
			20060101 => [
					10 => [
							[ 'income' => 500, 'rate' => 2, 'constant' => 0 ],
							[ 'income' => 3000, 'rate' => 4, 'constant' => 10 ],
							[ 'income' => 3000, 'rate' => 5, 'constant' => 110 ],
					],
					20 => [
							[ 'income' => 1000, 'rate' => 2, 'constant' => 0 ],
							[ 'income' => 6000, 'rate' => 4, 'constant' => 20 ],
							[ 'income' => 6000, 'rate' => 5, 'constant' => 220 ],
					],
					30 => [
							[ 'income' => 500, 'rate' => 2, 'constant' => 0 ],
							[ 'income' => 3000, 'rate' => 4, 'constant' => 10 ],
							[ 'income' => 3000, 'rate' => 5, 'constant' => 110 ],
					],
					40 => [
							[ 'income' => 500, 'rate' => 2, 'constant' => 0 ],
							[ 'income' => 3000, 'rate' => 4, 'constant' => 10 ],
							[ 'income' => 3000, 'rate' => 5, 'constant' => 110 ],
					],
					50 => [
							[ 'income' => 500, 'rate' => 2, 'constant' => 0 ],
							[ 'income' => 3000, 'rate' => 4, 'constant' => 10 ],
							[ 'income' => 3000, 'rate' => 5, 'constant' => 110 ],
					],
			],
	];

	var $state_options = [
			20130709 => [ //09-Jul-13 (was 13-Jul-09)
						  'standard_deduction_rate'    => 0,
						  'standard_deduction_maximum' => [
								  '10' => [
									  //1 = Income
									  //2 = Reduce By
									  //3 = Reduce by for every amount over the prev income level.
									  //4 = Previous Income
									  0 => [ 20499, 2500, 0, 0, 0 ],
									  1 => [ 30000, 2500, 25, 500, 20499 ],
									  2 => [ 30000, 2000, 0, 0, 30000 ],
								  ],
								  '20' => [
										  0 => [ 20499, 7500, 0, 0, 0 ],
										  1 => [ 30000, 7500, 175, 500, 20499 ],
										  2 => [ 30000, 4000, 0, 0, 30000 ],
								  ],
								  '30' => [
										  0 => [ 20499, 2500, 0, 0, 0 ],
										  1 => [ 30000, 2500, 25, 500, 20000 ],
										  2 => [ 30000, 2000, 0, 0, 30000 ],
								  ],
								  '40' => [
										  0 => [ 20499, 4700, 0, 0, 0 ],
										  1 => [ 30000, 4700, 135, 500, 20499 ],
										  2 => [ 30000, 2000, 0, 0, 30000 ],
								  ],
								  '50' => [
										  0 => [ 10249, 3750, 0, 0, 0 ],
										  1 => [ 15000, 3750, 88, 250, 10249 ],
										  2 => [ 15000, 2000, 0, 0, 15000 ],
								  ],
						  ],
						  'personal_deduction'         => [
								  '10' => 1500,
								  '20' => 3000,
								  '30' => 0,
								  '40' => 3000,
								  '50' => 1500,
						  ],

						  'dependant_allowance' => [
								  0 => [ 20000, 1000 ],
								  1 => [ 100000, 500 ],
								  2 => [ 100000, 300 ],
						  ],
			],
			20070101 => [
					'standard_deduction_rate'    => 0,
					'standard_deduction_maximum' => [
							'10' => [
								//1 = Income
								//2 = Reduce By
								//3 = Reduce by for every amount over the prev income level.
								//4 = Previous Income
								0 => [ 20000, 2500, 0, 0, 0 ],
								1 => [ 30000, 2500, 25, 500, 20000 ],
								2 => [ 30000, 2000, 0, 0, 30000 ],
							],
							'20' => [
									0 => [ 20000, 7500, 0, 0, 0 ],
									1 => [ 30000, 7500, 175, 500, 20000 ],
									2 => [ 30000, 4000, 0, 0, 30000 ],
							],
							'30' => [
									0 => [ 20000, 2500, 0, 0, 0 ],
									1 => [ 30000, 2500, 25, 500, 20000 ],
									2 => [ 30000, 2000, 0, 0, 30000 ],
							],
							'40' => [
									0 => [ 20000, 4700, 0, 0, 0 ],
									1 => [ 30000, 4700, 135, 500, 20000 ],
									2 => [ 30000, 2000, 0, 0, 30000 ],
							],
							'50' => [
									0 => [ 10000, 3750, 0, 0, 0 ],
									1 => [ 15000, 3750, 88, 250, 10000 ],
									2 => [ 15000, 2000, 0, 0, 15000 ],
							],
					],
					'personal_deduction'         => [
							'10' => 1500,
							'20' => 3000,
							'30' => 0,
							'40' => 3000,
							'50' => 1500,
					],

					'dependant_allowance' => [
							0 => [ 20000, 1000 ],
							1 => [ 100000, 500 ],
							2 => [ 100000, 300 ],
					],
			],
			20060101 => [
					'standard_deduction_rate'    => 20,
					'standard_deduction_maximum' => [
							'10' => 2000,
							'20' => 4000,
							'30' => 2000,
							'40' => 2000,
							'50' => 2000,
					],
					'personal_deduction'         => [
							'10' => 1500,
							'20' => 3000,
							'30' => 0,
							'40' => 3000,
							'50' => 1500,
					],

					'dependant_allowance' => 300,
			],
	];

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$federal_tax = $this->getFederalTaxPayable();
		$standard_deduction = $this->getStateStandardDeduction();
		$personal_deduction = $this->getStatePersonalDeduction();
		$dependant_allowance = $this->getStateDependantAllowanceAmount();

		Debug::text( 'Federal Annual Tax: ' . $federal_tax, __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( 'Standard Deduction: ' . $standard_deduction, __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( 'Personal Deduction: ' . $personal_deduction, __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( 'Dependant Allowance: ' . $dependant_allowance, __FILE__, __LINE__, __METHOD__, 10 );

		$income = bcsub( bcsub( bcsub( bcsub( $annual_income, $standard_deduction ), $personal_deduction ), $dependant_allowance ), $federal_tax );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}

	function getDataByIncome( $income, $arr ) {
		if ( !is_array( $arr ) ) {
			return false;
		}

		$prev_value = 0;
		$total_rates = count( $arr ) - 1;
		$i = 0;
		foreach ( $arr as $key => $values ) {
			if ( $this->getAnnualTaxableIncome() > $prev_value && $this->getAnnualTaxableIncome() <= $values[0] ) {
				return $values;
			} else if ( $i == $total_rates ) {
				return $values;
			}
			$prev_value = $values[0];
			$i++;
		}

		return false;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		if ( $this->getDate() >= 20070101 ) {
			Debug::text( 'Standard Deduction Formula (NEW)', __FILE__, __LINE__, __METHOD__, 10 );
			$deduction_arr = $this->getDataByIncome( $this->getAnnualTaxableIncome(), $retarr['standard_deduction_maximum'][$this->getStateFilingStatus()] );

			if ( $deduction_arr[3] > 0 ) {
				Debug::text( 'Complex Standard Deduction Formula (NEW)', __FILE__, __LINE__, __METHOD__, 10 );
				//Find out how far we're over the previous income level.
				$deduction = bcsub( $deduction_arr[1], bcmul( ceil( bcdiv( bcsub( $this->getAnnualTaxableIncome(), $deduction_arr[4] ), $deduction_arr[3] ) ), $deduction_arr[2] ) );
			} else {
				Debug::text( 'Basic Standard Deduction Formula (NEW)', __FILE__, __LINE__, __METHOD__, 10 );
				$deduction = $deduction_arr[1];
			}
		} else {
			Debug::text( 'Standard Deduction Forumla (OLD)', __FILE__, __LINE__, __METHOD__, 10 );
			$rate = bcdiv( $retarr['standard_deduction_rate'], 100 );

			$deduction = bcmul( $this->getAnnualTaxableIncome(), $rate );

			if ( $deduction >= $retarr['standard_deduction_maximum'][$this->getStateFilingStatus()] ) {
				$deduction = $retarr['standard_deduction_maximum'][$this->getStateFilingStatus()];
			}
		}

		Debug::text( 'Standard Deduction: ' . $deduction . ' Filing Status: ' . $this->getStateFilingStatus(), __FILE__, __LINE__, __METHOD__, 10 );

		return $deduction;
	}

	function getStatePersonalDeduction() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$deduction = $retarr['personal_deduction'][$this->getStateFilingStatus()];

		Debug::text( 'Personal Deduction: ' . $deduction . ' Filing Status: ' . $this->getStateFilingStatus(), __FILE__, __LINE__, __METHOD__, 10 );

		return $deduction;
	}

	function getStateDependantAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		if ( $this->getDate() >= 20070101 ) {
			$allowance_arr = $this->getDataByIncome( $this->getAnnualTaxableIncome(), $retarr['dependant_allowance'] );
			$allowance = $allowance_arr[1];
		} else {
			$allowance = $retarr['dependant_allowance'];
		}

		$retval = bcmul( $allowance, $this->getStateAllowance() );

		Debug::text( 'State Dependant Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _getStateTaxPayable() {
		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getStateRate( $annual_income );
			$state_constant = $this->getData()->getStateConstant( $annual_income );
			$prev_income = $this->getData()->getStateRatePreviousIncome( $annual_income );

			Debug::text( 'Rate: ' . $rate . ' Constant: ' . $state_constant . ' Prev Rate Income: ' . $prev_income, __FILE__, __LINE__, __METHOD__, 10 );
			$retval = bcadd( bcmul( bcsub( $annual_income, $prev_income ), $rate ), $state_constant );
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
