<?php
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


/*

 ** Formula partially based on: http://i2i.nfc.usda.gov/Publications/Tax_Formulas/State_City_County/taxla.html

 10 = Single
 20 = Married Filing Jointly

*/

/**
 * @package PayrollDeduction\US
 */
class PayrollDeduction_US_LA extends PayrollDeduction_US {

	var $state_income_tax_rate_options = [
			20180216 => [ //16-Feb-2018 - LA publication R-1306 doesn't give actual tax brackets, instead we had to calculate them based on the formula they provide. 0.021, +0.180 (3.9%) +0.165 (5.55%)
						  10 => [
								  [ 'income' => 12500, 'rate' => 2.1, 'constant' => 0 ],
								  [ 'income' => 50000, 'rate' => 3.9, 'constant' => 262.50 ],
								  [ 'income' => 50000, 'rate' => 5.55, 'constant' => 1725.00 ],
						  ],
						  20 => [
								  [ 'income' => 25000, 'rate' => 2.2, 'constant' => 0 ],
								  [ 'income' => 100000, 'rate' => 3.95, 'constant' => 550.00 ],
								  [ 'income' => 100000, 'rate' => 5.64, 'constant' => 3512.50 ],
						  ],
			],
			20090701 => [ //LA publication R-1306 doesn't give actual tax brackets, instead we had to calculate them based on the formula they provide. 0.21, +0.160 (3.7%) +0.135 (5.05%)
						  10 => [
								  [ 'income' => 12500, 'rate' => 2.1, 'constant' => 0 ],
								  [ 'income' => 50000, 'rate' => 3.7, 'constant' => 262.50 ],
								  [ 'income' => 50000, 'rate' => 5.05, 'constant' => 1650.00 ],
						  ],
						  20 => [
								  [ 'income' => 25000, 'rate' => 2.1, 'constant' => 0 ],
								  [ 'income' => 100000, 'rate' => 3.75, 'constant' => 525.00 ],
								  [ 'income' => 100000, 'rate' => 5.10, 'constant' => 3337.50 ],
						  ],
			],
	];


	var $state_options = [
			20180216 => [
					'allowance'           => 4500,
					'dependant_allowance' => 1000,
					'allowance_rates'     => [ //Personal exceptions
											   10 => [
													   0 => [ 12500, 2.1, 0 ],
													   1 => [ 12500, 1.8, 262.50 ],
											   ],
											   20 => [
													   0 => [ 25000, 2.1, 0 ],
													   1 => [ 25000, 1.75, 525 ],
											   ],
					],
			],
			20060101 => [
					'allowance'           => 4500,
					'dependant_allowance' => 1000,
					'allowance_rates'     => [ //Personal exceptions
											   10 => [
													   0 => [ 12500, 2.1, 0 ],
													   1 => [ 12500, 3.7, 262.50 ],
											   ],
											   20 => [
													   0 => [ 25000, 2.1, 0 ],
													   1 => [ 25000, 3.75, 525 ],
											   ],
					],
			],
	];

	function getStateTotalAllowanceAmount() {
		$retval = bcadd( $this->getStateAllowanceAmount(), $this->getStateDependantAllowanceAmount() );

		Debug::text( 'State Total Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

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

	function getStateDependantAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$allowance_arr = $retarr['dependant_allowance'];

		$retval = bcmul( $this->getUserValue3(), $allowance_arr );

		Debug::text( 'State Dependant Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function getDataByIncome( $income, $arr ) {
		if ( !is_array( $arr ) ) {
			return false;
		}

		$prev_value = 0;
		$total_rates = count( $arr ) - 1;
		$i = 0;
		foreach ( $arr as $key => $values ) {
			if ( $income > $prev_value && $income <= $values[0] ) {
				return $values;
			} else if ( $i == $total_rates ) {
				return $values;
			}
			$prev_value = $values[0];
			$i++;
		}

		return false;
	}

	function getStateTaxableAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$retval = 0;
		if ( $this->getStateTotalAllowanceAmount() > 0 && isset( $retarr['allowance_rates'][$this->getStateFilingStatus()] ) ) {
			$standard_deduction_arr = $this->getDataByIncome( $this->getStateTotalAllowanceAmount(), $retarr['allowance_rates'][$this->getStateFilingStatus()] );
			//Debug::Arr($standard_deduction_arr, 'State Taxable Allowance: '. $this->getStateTotalAllowanceAmount(), __FILE__, __LINE__, __METHOD__, 10);

			$retval = bcadd( bcmul( $this->getStateTotalAllowanceAmount(), bcdiv( $standard_deduction_arr[1], 100 ) ), $standard_deduction_arr[2] );

			Debug::text( 'State Taxable Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $retval;
	}

	function _getStateTaxPayable() {
		$annual_income = $this->getAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getStateRate( $annual_income );
			$state_constant = $this->getData()->getStateConstant( $annual_income );
			$prev_income = $this->getData()->getStateRatePreviousIncome( $annual_income );

			Debug::text( 'Rate: ' . $rate . ' Constant: ' . $state_constant . ' Prev Rate Income: ' . $prev_income, __FILE__, __LINE__, __METHOD__, 10 );
			$retval = bcadd( bcmul( bcsub( $annual_income, $prev_income ), $rate ), $state_constant );
			Debug::text( 'Inital State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

			$retval = bcsub( $retval, $this->getStateTaxableAllowanceAmount() );
			Debug::text( 'Final State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
