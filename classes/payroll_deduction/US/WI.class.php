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

		******** USE Calculation Method "B" *********

*/

/**
 * @package PayrollDeduction\US
 */
class PayrollDeduction_US_WI extends PayrollDeduction_US {

	var $state_income_tax_rate_options = [
			20140401 => [
					10 => [
							[ 'income' => 5730, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 15200, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 16486, 'rate' => 4.48, 'constant' => 378.80 ],
							[ 'income' => 26227, 'rate' => 6.5408, 'constant' => 436.41 ],
							[ 'income' => 62950, 'rate' => 7.0224, 'constant' => 1073.55 ],
							[ 'income' => 240190, 'rate' => 6.27, 'constant' => 3652.39 ],
							[ 'income' => 240190, 'rate' => 7.65, 'constant' => 14765.34 ],
					],
					20 => [
							[ 'income' => 7870, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 18780, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 21400, 'rate' => 5.84, 'constant' => 436.40 ],
							[ 'income' => 28308, 'rate' => 7.008, 'constant' => 589.41 ],
							[ 'income' => 60750, 'rate' => 7.524, 'constant' => 1073.52 ],
							[ 'income' => 240190, 'rate' => 6.27, 'constant' => 3514.46 ],
							[ 'income' => 240190, 'rate' => 7.65, 'constant' => 14765.35 ],
					],
			],
			20100101 => [
					10 => [
							[ 'income' => 4000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10620, 'rate' => 4.6, 'constant' => 0 ],
							[ 'income' => 13602, 'rate' => 5.152, 'constant' => 304.52 ],
							[ 'income' => 22486, 'rate' => 6.888, 'constant' => 458.15 ],
							[ 'income' => 43953, 'rate' => 7.28, 'constant' => 1070.08 ],
							[ 'income' => 149330, 'rate' => 6.5, 'constant' => 2632.88 ],
							[ 'income' => 219200, 'rate' => 6.75, 'constant' => 9482.39 ],
							[ 'income' => 219200, 'rate' => 7.75, 'constant' => 14198.62 ],
					],
					20 => [
							[ 'income' => 5500, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 14950, 'rate' => 4.6, 'constant' => 0 ],
							[ 'income' => 15375, 'rate' => 5.52, 'constant' => 434.70 ],
							[ 'income' => 23667, 'rate' => 7.38, 'constant' => 458.16 ],
							[ 'income' => 42450, 'rate' => 7.8, 'constant' => 1070.11 ],
							[ 'income' => 149330, 'rate' => 6.5, 'constant' => 2535.18 ],
							[ 'income' => 219200, 'rate' => 6.75, 'constant' => 9482.38 ],
							[ 'income' => 219200, 'rate' => 7.75, 'constant' => 14198.61 ],
					],
			],
			20060101 => [
					10 => [
							[ 'income' => 4000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10620, 'rate' => 4.6, 'constant' => 0 ],
							[ 'income' => 11825, 'rate' => 5.154, 'constant' => 305 ],
							[ 'income' => 18629, 'rate' => 6.888, 'constant' => 367 ],
							[ 'income' => 43953, 'rate' => 7.280, 'constant' => 836 ],
							[ 'income' => 115140, 'rate' => 6.5, 'constant' => 2680 ],
							[ 'income' => 115140, 'rate' => 6.75, 'constant' => 7307 ],
					],
					20 => [
							[ 'income' => 5500, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 13470, 'rate' => 4.6, 'constant' => 0 ],
							[ 'income' => 14950, 'rate' => 6.15, 'constant' => 367 ],
							[ 'income' => 20067, 'rate' => 7.38, 'constant' => 458 ],
							[ 'income' => 42450, 'rate' => 7.8, 'constant' => 836 ],
							[ 'income' => 115140, 'rate' => 6.5, 'constant' => 2582 ],
							[ 'income' => 115140, 'rate' => 6.75, 'constant' => 7307 ],
					],
			],
	];

	var $state_options = [
		//01-Jan-10: No Change.
		20060101 => [
				'allowance' => 22,
		],
	];

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();

		$income = $annual_income;

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
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
		}

		$retval = $retval - $this->getStateAllowanceAmount();

		if ( $retval < 0 ) {
			$retval = 0;
		}
		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
