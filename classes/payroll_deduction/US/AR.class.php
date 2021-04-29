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
class PayrollDeduction_US_AR extends PayrollDeduction_US {

	var $state_income_tax_rate_options = [
		//As of 20200301 use the tax brackets verbatim, where the "constant" is minused rather than added.
		20200301 => [
				0 => [
						[ 'income' => 4599, 'rate' => 0.0, 'constant' => 0 ],
						[ 'income' => 9099, 'rate' => 2.0, 'constant' => 91.98 ],
						[ 'income' => 13699, 'rate' => 3.0, 'constant' => 182.97 ],
						[ 'income' => 22599, 'rate' => 3.4, 'constant' => 237.77 ],
						[ 'income' => 37899, 'rate' => 5.0, 'constant' => 421.46 ],
						[ 'income' => 80800, 'rate' => 5.9, 'constant' => 762.55 ],
						[ 'income' => 81800, 'rate' => 6.6, 'constant' => 1243.40 ],
						[ 'income' => 82800, 'rate' => 6.6, 'constant' => 1143.40 ],
						[ 'income' => 84100, 'rate' => 6.6, 'constant' => 1043.40 ],
						[ 'income' => 85200, 'rate' => 6.6, 'constant' => 943.40 ],
						[ 'income' => 86200, 'rate' => 6.6, 'constant' => 843.40 ],
						[ 'income' => 86200, 'rate' => 6.6, 'constant' => 803.40 ],
				],
		],
		20150101 => [
				0 => [
						[ 'income' => 4300, 'rate' => 0.9, 'constant' => 0 ],
						[ 'income' => 8400, 'rate' => 2.4, 'constant' => 38.70 ],
						[ 'income' => 12600, 'rate' => 3.4, 'constant' => 137.10 ],
						[ 'income' => 21000, 'rate' => 4.4, 'constant' => 279.90 ],
						[ 'income' => 35100, 'rate' => 5.90, 'constant' => 649.50 ],
						[ 'income' => 35100, 'rate' => 6.90, 'constant' => 1481.40 ],
				],
		],
		20060101 => [
				0 => [
						[ 'income' => 3000, 'rate' => 1.0, 'constant' => 0 ],
						[ 'income' => 6000, 'rate' => 2.5, 'constant' => 30 ],
						[ 'income' => 9000, 'rate' => 3.5, 'constant' => 105 ],
						[ 'income' => 15000, 'rate' => 4.5, 'constant' => 210 ],
						[ 'income' => 25000, 'rate' => 6.0, 'constant' => 480 ],
						[ 'income' => 25000, 'rate' => 7.0, 'constant' => 1080 ],
				],
		],
	];

	var $state_options = [
			20150101 => [ //01-Jan-2015
						  'standard_deduction' => 2200,
						  'allowance'          => 26,
			],
			20060101 => [ //01-Jan-2006
						  'standard_deduction' => 2000,
						  'allowance'          => 20,
			],
	];

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$standard_deduction = $this->getStateStandardDeduction();

		$income = bcsub( $annual_income, $standard_deduction );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$retval = $retarr['standard_deduction'];

		Debug::text( 'State Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

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
			$prev_income = $this->getData()->getStateRatePreviousIncome( $annual_income );

			//Switch to using actual government formula with minus rather than addition so we don't have to calculate the brackets manually.
			if ( $this->getDate() >= 20200301 ) {
				$retval = bcsub( bcmul( $annual_income, $rate ), $state_constant );
			} else {
				$retval = bcadd( bcmul( bcsub( $annual_income, $prev_income ), $rate ), $state_constant );
			}
		}

		Debug::text( 'State Annual Tax Payable before allowance: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );
		$retval = bcsub( $retval, $this->getStateAllowanceAmount() );

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
