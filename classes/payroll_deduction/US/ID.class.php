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
class PayrollDeduction_US_ID extends PayrollDeduction_US {

	var $state_income_tax_rate_options = [
			20200616 => [
					10 => [
							[ 'income' => 12400, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 13968, 'rate' => 1.125, 'constant' => 0 ],
							[ 'income' => 15536, 'rate' => 3.125, 'constant' => 18 ],
							[ 'income' => 17104, 'rate' => 3.625, 'constant' => 67 ],
							[ 'income' => 18672, 'rate' => 4.625, 'constant' => 124 ],
							[ 'income' => 20240, 'rate' => 5.625, 'constant' => 197 ],
							[ 'income' => 24160, 'rate' => 6.625, 'constant' => 285 ],
							[ 'income' => 24160, 'rate' => 6.925, 'constant' => 545 ],
					],
					20 => [
							[ 'income' => 24800, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 27936, 'rate' => 1.125, 'constant' => 0 ],
							[ 'income' => 31072, 'rate' => 3.125, 'constant' => 35 ],
							[ 'income' => 34208, 'rate' => 3.625, 'constant' => 133 ],
							[ 'income' => 37344, 'rate' => 4.625, 'constant' => 247 ],
							[ 'income' => 40480, 'rate' => 5.625, 'constant' => 392 ],
							[ 'income' => 48320, 'rate' => 6.625, 'constant' => 568 ],
							[ 'income' => 48320, 'rate' => 6.925, 'constant' => 1087 ],
					],
			],
			20190627 => [
					10 => [
							[ 'income' => 12200, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 13741, 'rate' => 1.125, 'constant' => 0 ],
							[ 'income' => 15281, 'rate' => 3.125, 'constant' => 17 ],
							[ 'income' => 16822, 'rate' => 3.625, 'constant' => 65 ],
							[ 'income' => 18362, 'rate' => 4.625, 'constant' => 121 ],
							[ 'income' => 19903, 'rate' => 5.625, 'constant' => 192 ],
							[ 'income' => 23754, 'rate' => 6.625, 'constant' => 279 ],
							[ 'income' => 23754, 'rate' => 6.925, 'constant' => 534 ],
					],
					20 => [
							[ 'income' => 24400, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 27482, 'rate' => 1.125, 'constant' => 0 ],
							[ 'income' => 30562, 'rate' => 3.125, 'constant' => 35 ],
							[ 'income' => 33644, 'rate' => 3.625, 'constant' => 131 ],
							[ 'income' => 36724, 'rate' => 4.625, 'constant' => 243 ],
							[ 'income' => 39806, 'rate' => 5.625, 'constant' => 385 ],
							[ 'income' => 47508, 'rate' => 6.625, 'constant' => 558 ],
							[ 'income' => 47508, 'rate' => 6.925, 'constant' => 1068 ],
					],
			],
			20180101 => [ //01-Jan-2018 (Guide updated Apr 2018, but it was retroactive.)
						  10 => [
								  [ 'income' => 12000, 'rate' => 0, 'constant' => 0 ],
								  [ 'income' => 13504, 'rate' => 1.125, 'constant' => 0 ],
								  [ 'income' => 15008, 'rate' => 3.125, 'constant' => 17 ],
								  [ 'income' => 16511, 'rate' => 3.625, 'constant' => 64 ],
								  [ 'income' => 18015, 'rate' => 4.625, 'constant' => 118 ],
								  [ 'income' => 19519, 'rate' => 5.625, 'constant' => 188 ],
								  [ 'income' => 23279, 'rate' => 6.625, 'constant' => 273 ],
								  [ 'income' => 23279, 'rate' => 6.925, 'constant' => 522 ],
						  ],
						  20 => [
								  [ 'income' => 24000, 'rate' => 0, 'constant' => 0 ],
								  [ 'income' => 27008, 'rate' => 1.125, 'constant' => 0 ],
								  [ 'income' => 30016, 'rate' => 3.125, 'constant' => 34 ],
								  [ 'income' => 33022, 'rate' => 3.625, 'constant' => 128 ],
								  [ 'income' => 36030, 'rate' => 4.625, 'constant' => 237 ],
								  [ 'income' => 39038, 'rate' => 5.625, 'constant' => 376 ],
								  [ 'income' => 46558, 'rate' => 6.625, 'constant' => 545 ],
								  [ 'income' => 46558, 'rate' => 6.925, 'constant' => 1043 ],
						  ],
			],
			20160101 => [ //01-Jan-2016 (Guide updated June 2016, but it was retroactive.)
						  10 => [
								  [ 'income' => 2250, 'rate' => 0, 'constant' => 0 ],
								  [ 'income' => 3704, 'rate' => 1.6, 'constant' => 0 ],
								  [ 'income' => 5158, 'rate' => 3.6, 'constant' => 23 ],
								  [ 'income' => 6612, 'rate' => 4.1, 'constant' => 75 ],
								  [ 'income' => 8066, 'rate' => 5.1, 'constant' => 135 ],
								  [ 'income' => 9520, 'rate' => 6.1, 'constant' => 209 ],
								  [ 'income' => 13155, 'rate' => 7.1, 'constant' => 298 ],
								  [ 'income' => 13155, 'rate' => 7.4, 'constant' => 556 ],
						  ],
						  20 => [
								  [ 'income' => 8550, 'rate' => 0, 'constant' => 0 ],
								  [ 'income' => 11458, 'rate' => 1.6, 'constant' => 0 ],
								  [ 'income' => 14366, 'rate' => 3.6, 'constant' => 47 ],
								  [ 'income' => 17274, 'rate' => 4.1, 'constant' => 152 ],
								  [ 'income' => 20182, 'rate' => 5.1, 'constant' => 271 ],
								  [ 'income' => 23090, 'rate' => 6.1, 'constant' => 419 ],
								  [ 'income' => 30360, 'rate' => 7.1, 'constant' => 596 ],
								  [ 'income' => 30360, 'rate' => 7.4, 'constant' => 1112 ],
						  ],
			],
			20140601 => [
					10 => [
							[ 'income' => 2250, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 3679, 'rate' => 1.6, 'constant' => 0 ],
							[ 'income' => 5108, 'rate' => 3.6, 'constant' => 23 ],
							[ 'income' => 6537, 'rate' => 4.1, 'constant' => 74 ],
							[ 'income' => 7966, 'rate' => 5.1, 'constant' => 133 ],
							[ 'income' => 9395, 'rate' => 6.1, 'constant' => 206 ],
							[ 'income' => 12968, 'rate' => 7.1, 'constant' => 293 ],
							[ 'income' => 12968, 'rate' => 7.4, 'constant' => 547 ],
					],
					20 => [
							[ 'income' => 8450, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 11308, 'rate' => 1.6, 'constant' => 0 ],
							[ 'income' => 14166, 'rate' => 3.6, 'constant' => 46 ],
							[ 'income' => 17024, 'rate' => 4.1, 'constant' => 149 ],
							[ 'income' => 19882, 'rate' => 5.1, 'constant' => 266 ],
							[ 'income' => 22740, 'rate' => 6.1, 'constant' => 412 ],
							[ 'income' => 29886, 'rate' => 7.1, 'constant' => 586 ],
							[ 'income' => 29886, 'rate' => 7.4, 'constant' => 1093 ],
					],
			],
			20130521 => [
					10 => [
							[ 'income' => 2200, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 3609, 'rate' => 1.6, 'constant' => 0 ],
							[ 'income' => 5018, 'rate' => 3.6, 'constant' => 23 ],
							[ 'income' => 6427, 'rate' => 4.1, 'constant' => 74 ],
							[ 'income' => 7836, 'rate' => 5.1, 'constant' => 132 ],
							[ 'income' => 9245, 'rate' => 6.1, 'constant' => 204 ],
							[ 'income' => 12768, 'rate' => 7.1, 'constant' => 290 ],
							[ 'income' => 12768, 'rate' => 7.4, 'constant' => 540 ],
					],
					20 => [
							[ 'income' => 8300, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 11118, 'rate' => 1.6, 'constant' => 0 ],
							[ 'income' => 13936, 'rate' => 3.6, 'constant' => 45 ],
							[ 'income' => 16754, 'rate' => 4.1, 'constant' => 146 ],
							[ 'income' => 19572, 'rate' => 5.1, 'constant' => 262 ],
							[ 'income' => 22390, 'rate' => 6.1, 'constant' => 406 ],
							[ 'income' => 29436, 'rate' => 7.1, 'constant' => 578 ],
							[ 'income' => 29436, 'rate' => 7.4, 'constant' => 1078 ],
					],
			],
			20130101 => [
					10 => [
							[ 'income' => 2150, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 3530, 'rate' => 1.6, 'constant' => 0 ],
							[ 'income' => 4910, 'rate' => 3.6, 'constant' => 22 ],
							[ 'income' => 6290, 'rate' => 4.1, 'constant' => 72 ],
							[ 'income' => 7670, 'rate' => 5.1, 'constant' => 129 ],
							[ 'income' => 9050, 'rate' => 6.1, 'constant' => 199 ],
							[ 'income' => 12500, 'rate' => 7.1, 'constant' => 283 ],
							[ 'income' => 12500, 'rate' => 7.4, 'constant' => 528 ],
					],
					20 => [
							[ 'income' => 8100, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10860, 'rate' => 1.6, 'constant' => 0 ],
							[ 'income' => 13620, 'rate' => 3.6, 'constant' => 44 ],
							[ 'income' => 16380, 'rate' => 4.1, 'constant' => 143 ],
							[ 'income' => 19140, 'rate' => 5.1, 'constant' => 256 ],
							[ 'income' => 21900, 'rate' => 6.1, 'constant' => 397 ],
							[ 'income' => 28800, 'rate' => 7.1, 'constant' => 565 ],
							[ 'income' => 28800, 'rate' => 7.4, 'constant' => 1055 ],
					],
			],
			20120101 => [
					10 => [
							[ 'income' => 2100, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 3438, 'rate' => 1.6, 'constant' => 0 ],
							[ 'income' => 4776, 'rate' => 3.6, 'constant' => 21 ],
							[ 'income' => 6114, 'rate' => 4.1, 'constant' => 69 ],
							[ 'income' => 7452, 'rate' => 5.1, 'constant' => 124 ],
							[ 'income' => 8790, 'rate' => 6.1, 'constant' => 192 ],
							[ 'income' => 12135, 'rate' => 7.1, 'constant' => 274 ],
							[ 'income' => 28860, 'rate' => 7.4, 'constant' => 511 ],
							[ 'income' => 28860, 'rate' => 7.8, 'constant' => 1749 ],
					],
					20 => [
							[ 'income' => 7900, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 10576, 'rate' => 1.6, 'constant' => 0 ],
							[ 'income' => 13252, 'rate' => 3.6, 'constant' => 43 ],
							[ 'income' => 15928, 'rate' => 4.1, 'constant' => 139 ],
							[ 'income' => 18604, 'rate' => 5.1, 'constant' => 249 ],
							[ 'income' => 21280, 'rate' => 6.1, 'constant' => 385 ],
							[ 'income' => 27970, 'rate' => 7.1, 'constant' => 548 ],
							[ 'income' => 61420, 'rate' => 7.4, 'constant' => 1023 ],
							[ 'income' => 61420, 'rate' => 7.8, 'constant' => 3498 ],
					],
			],
			20090101 => [
					10 => [
							[ 'income' => 1950, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 3222, 'rate' => 1.6, 'constant' => 0 ],
							[ 'income' => 4494, 'rate' => 3.6, 'constant' => 20 ],
							[ 'income' => 5766, 'rate' => 4.1, 'constant' => 66 ],
							[ 'income' => 7038, 'rate' => 5.1, 'constant' => 118 ],
							[ 'income' => 8310, 'rate' => 6.1, 'constant' => 183 ],
							[ 'income' => 11490, 'rate' => 7.1, 'constant' => 261 ],
							[ 'income' => 27391, 'rate' => 7.4, 'constant' => 487 ],
							[ 'income' => 27391, 'rate' => 7.8, 'constant' => 1664 ],
					],
					20 => [
							[ 'income' => 7400, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 9944, 'rate' => 1.6, 'constant' => 0 ],
							[ 'income' => 12488, 'rate' => 3.6, 'constant' => 41 ],
							[ 'income' => 15032, 'rate' => 4.1, 'constant' => 133 ],
							[ 'income' => 17576, 'rate' => 5.1, 'constant' => 237 ],
							[ 'income' => 20120, 'rate' => 6.1, 'constant' => 367 ],
							[ 'income' => 26480, 'rate' => 7.1, 'constant' => 522 ],
							[ 'income' => 58282, 'rate' => 7.4, 'constant' => 974 ],
							[ 'income' => 58282, 'rate' => 7.8, 'constant' => 3327 ],
					],
			],
			20060101 => [
					10 => [
							[ 'income' => 1800, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 2959, 'rate' => 1.6, 'constant' => 0 ],
							[ 'income' => 4118, 'rate' => 3.6, 'constant' => 19 ],
							[ 'income' => 5277, 'rate' => 4.1, 'constant' => 61 ],
							[ 'income' => 6436, 'rate' => 5.1, 'constant' => 109 ],
							[ 'income' => 7594, 'rate' => 6.1, 'constant' => 168 ],
							[ 'income' => 10492, 'rate' => 7.1, 'constant' => 239 ],
							[ 'income' => 24978, 'rate' => 7.4, 'constant' => 445 ],
							[ 'income' => 24978, 'rate' => 7.8, 'constant' => 1517 ],
					],
					20 => [
							[ 'income' => 6800, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 9118, 'rate' => 1.6, 'constant' => 0 ],
							[ 'income' => 11436, 'rate' => 3.6, 'constant' => 37 ],
							[ 'income' => 13754, 'rate' => 4.1, 'constant' => 120 ],
							[ 'income' => 16072, 'rate' => 5.1, 'constant' => 215 ],
							[ 'income' => 18388, 'rate' => 6.1, 'constant' => 333 ],
							[ 'income' => 24184, 'rate' => 7.1, 'constant' => 474 ],
							[ 'income' => 53156, 'rate' => 7.4, 'constant' => 886 ],
							[ 'income' => 53156, 'rate' => 7.8, 'constant' => 3030 ],
					],
			],
	];

	var $state_options = [
			20180101 => [ //01-Jan-2018 (Guide updated Apr 2018, but it was retroactive.)
						  'allowance' => 2960,
			],
			20160101 => [ //01-Jan-2016 (Guide updated June 2016, but it was retroactive.)
						  'allowance' => 4050,
			],
			20140601 => [ //01-Jun-2014
						  'allowance' => 3950,
			],
			20130521 => [ //21-May-2013
						  'allowance' => 3900,
			],
			20130101 => [ //01-Jan-2013
						  'allowance' => 3800,
			],
			20120101 => [ //01-Jan-2009
						  'allowance' => 3700,
			],
			20090101 => [ //01-Jan-2009
						  'allowance' => 3500,
			],
			20060101 => [ //01-Jan-2006
						  'allowance' => 3200,
			],
	];

	function getStatePayPeriodDeductionRoundedValue( $amount ) {
		return $this->RoundNearestDollar( $amount );
	}

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
