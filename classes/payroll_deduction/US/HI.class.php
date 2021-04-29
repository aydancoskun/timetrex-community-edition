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


/**
 * @package PayrollDeduction\US
 */
class PayrollDeduction_US_HI extends PayrollDeduction_US {

	var $state_income_tax_rate_options = [
			20090101 => [
					10 => [
							[ 'income' => 2400, 'rate' => 1.4, 'constant' => 0 ],
							[ 'income' => 4800, 'rate' => 3.2, 'constant' => 34 ],
							[ 'income' => 9600, 'rate' => 5.5, 'constant' => 110 ],
							[ 'income' => 14400, 'rate' => 6.4, 'constant' => 374 ],
							[ 'income' => 19200, 'rate' => 6.8, 'constant' => 682 ],
							[ 'income' => 24000, 'rate' => 7.2, 'constant' => 1008 ],
							[ 'income' => 36000, 'rate' => 7.6, 'constant' => 1354 ],
							[ 'income' => 36000, 'rate' => 7.9, 'constant' => 2266 ],
					],
					20 => [
							[ 'income' => 4800, 'rate' => 1.4, 'constant' => 0 ],
							[ 'income' => 9600, 'rate' => 3.2, 'constant' => 67 ],
							[ 'income' => 19200, 'rate' => 5.5, 'constant' => 221 ],
							[ 'income' => 28800, 'rate' => 6.4, 'constant' => 749 ],
							[ 'income' => 38400, 'rate' => 6.8, 'constant' => 1363 ],
							[ 'income' => 48000, 'rate' => 7.2, 'constant' => 2016 ],
							[ 'income' => 72000, 'rate' => 7.6, 'constant' => 2707 ],
							[ 'income' => 72000, 'rate' => 7.9, 'constant' => 4531 ],
					],
			],
			20070101 => [
					10 => [
							[ 'income' => 2400, 'rate' => 1.4, 'constant' => 0 ],
							[ 'income' => 4800, 'rate' => 3.2, 'constant' => 34 ],
							[ 'income' => 9600, 'rate' => 5.5, 'constant' => 110 ],
							[ 'income' => 14400, 'rate' => 6.4, 'constant' => 374 ],
							[ 'income' => 19200, 'rate' => 6.8, 'constant' => 682 ],
							[ 'income' => 24000, 'rate' => 7.2, 'constant' => 1008 ],
							[ 'income' => 24000, 'rate' => 7.6, 'constant' => 1354 ],
					],
					20 => [
							[ 'income' => 4800, 'rate' => 1.4, 'constant' => 0 ],
							[ 'income' => 9600, 'rate' => 3.2, 'constant' => 67 ],
							[ 'income' => 19200, 'rate' => 5.5, 'constant' => 221 ],
							[ 'income' => 28800, 'rate' => 6.4, 'constant' => 749 ],
							[ 'income' => 38400, 'rate' => 6.8, 'constant' => 1363 ],
							[ 'income' => 48000, 'rate' => 7.2, 'constant' => 2016 ],
							[ 'income' => 48000, 'rate' => 7.6, 'constant' => 2707 ],
					],
			],
			20060101 => [
					10 => [
							[ 'income' => 2000, 'rate' => 1.4, 'constant' => 0 ],
							[ 'income' => 4000, 'rate' => 3.2, 'constant' => 28 ],
							[ 'income' => 8000, 'rate' => 5.5, 'constant' => 92 ],
							[ 'income' => 12000, 'rate' => 6.4, 'constant' => 312 ],
							[ 'income' => 16000, 'rate' => 6.8, 'constant' => 568 ],
							[ 'income' => 20000, 'rate' => 7.2, 'constant' => 840 ],
							[ 'income' => 20000, 'rate' => 7.6, 'constant' => 1128 ],
					],
					20 => [
							[ 'income' => 4000, 'rate' => 1.4, 'constant' => 0 ],
							[ 'income' => 8000, 'rate' => 3.2, 'constant' => 56 ],
							[ 'income' => 16000, 'rate' => 5.5, 'constant' => 184 ],
							[ 'income' => 24000, 'rate' => 6.4, 'constant' => 624 ],
							[ 'income' => 32000, 'rate' => 6.8, 'constant' => 1136 ],
							[ 'income' => 40000, 'rate' => 7.2, 'constant' => 1680 ],
							[ 'income' => 40000, 'rate' => 7.6, 'constant' => 2256 ],
					],
			],
	];

	var $state_options = [
			20110101 => [ //01-Jan-2011
						  'allowance' => 1144,
			],
			20060101 => [
					'allowance' => 1040,
			],
	];

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$state_allowance = $this->getStateAllowanceAmount();

		$income = bcsub( $annual_income, $state_allowance );

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
