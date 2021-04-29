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
class PayrollDeduction_US_MN extends PayrollDeduction_US {

	var $state_income_tax_rate_options = [
			20200101 => [
					10 => [
							[ 'income' => 3800, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 30760, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 92350, 'rate' => 6.80, 'constant' => 1442.36 ],
							[ 'income' => 168200, 'rate' => 7.85, 'constant' => 5630.48 ],
							[ 'income' => 168200, 'rate' => 9.85, 'constant' => 11584.71 ],
					],
					20 => [
							[ 'income' => 11900, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 51310, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 168470, 'rate' => 6.80, 'constant' => 2108.44 ],
							[ 'income' => 285370, 'rate' => 7.85, 'constant' => 10075.32 ],
							[ 'income' => 285370, 'rate' => 9.85, 'constant' => 19251.97 ],
					],
			],
			20190101 => [
					10 => [
							[ 'income' => 2400, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 28920, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 89510, 'rate' => 7.05, 'constant' => 1418.82 ],
							[ 'income' => 166290, 'rate' => 7.85, 'constant' => 5690.42 ],
							[ 'income' => 166290, 'rate' => 9.85, 'constant' => 11717.65 ],
					],
					20 => [
							[ 'income' => 9050, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 47820, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 163070, 'rate' => 7.05, 'constant' => 2074.20 ],
							[ 'income' => 282200, 'rate' => 7.85, 'constant' => 10199.33 ],
							[ 'income' => 282200, 'rate' => 9.85, 'constant' => 19551.04 ],
					],
			],
			20180101 => [
					10 => [
							[ 'income' => 2350, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 28240, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 87410, 'rate' => 7.05, 'constant' => 1385.12 ],
							[ 'income' => 162370, 'rate' => 7.85, 'constant' => 5556.61 ],
							[ 'income' => 162370, 'rate' => 9.85, 'constant' => 11440.97 ],
					],
					20 => [
							[ 'income' => 8850, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 46700, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 159230, 'rate' => 7.05, 'constant' => 2024.98 ],
							[ 'income' => 275550, 'rate' => 7.85, 'constant' => 9958.35 ],
							[ 'income' => 275550, 'rate' => 9.85, 'constant' => 19089.47 ],
					],
			],
			20170101 => [
					10 => [
							[ 'income' => 2300, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 27690, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 85700, 'rate' => 7.05, 'constant' => 1358.37 ],
							[ 'income' => 159210, 'rate' => 7.85, 'constant' => 5448.08 ],
							[ 'income' => 159210, 'rate' => 9.85, 'constant' => 11218.62 ],
					],
					20 => [
							[ 'income' => 8650, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 45760, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 156100, 'rate' => 7.05, 'constant' => 1985.39 ],
							[ 'income' => 270160, 'rate' => 7.85, 'constant' => 9764.36 ],
							[ 'income' => 270160, 'rate' => 9.85, 'constant' => 18718.07 ],
					],
			],
			20160101 => [
					10 => [
							[ 'income' => 2250, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 27430, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 84990, 'rate' => 7.05, 'constant' => 1347.13 ],
							[ 'income' => 157900, 'rate' => 7.85, 'constant' => 5405.11 ],
							[ 'income' => 157900, 'rate' => 9.85, 'constant' => 11128.55 ],
					],
					20 => [
							[ 'income' => 8550, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 45370, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 154820, 'rate' => 7.05, 'constant' => 1969.87 ],
							[ 'income' => 267970, 'rate' => 7.85, 'constant' => 9686.10 ],
							[ 'income' => 267970, 'rate' => 9.85, 'constant' => 18568.38 ],
					],
			],
			20150101 => [
					10 => [
							[ 'income' => 2300, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 27370, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 84660, 'rate' => 7.05, 'constant' => 1341.25 ],
							[ 'income' => 157250, 'rate' => 7.85, 'constant' => 5380.20 ],
							[ 'income' => 157250, 'rate' => 9.85, 'constant' => 11078.52 ],
					],
					20 => [
							[ 'income' => 8600, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 45250, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 154220, 'rate' => 7.05, 'constant' => 1960.78 ],
							[ 'income' => 266860, 'rate' => 7.85, 'constant' => 9643.17 ],
							[ 'income' => 266860, 'rate' => 9.85, 'constant' => 18485.41 ],
					],
			],
			20140101 => [
					10 => [
							[ 'income' => 2250, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 26930, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 83330, 'rate' => 7.05, 'constant' => 1320.38 ],
							[ 'income' => 154790, 'rate' => 7.85, 'constant' => 5296.58 ],
							[ 'income' => 154790, 'rate' => 9.85, 'constant' => 10906.19 ],
					],
					20 => [
							[ 'income' => 6400, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 42480, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 149750, 'rate' => 7.05, 'constant' => 1930.28 ],
							[ 'income' => 260640, 'rate' => 7.85, 'constant' => 9492.82 ],
							[ 'income' => 260640, 'rate' => 9.85, 'constant' => 18197.69 ],
					],
			],
			20130101 => [
					10 => [
							[ 'income' => 2200, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 26470, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 81930, 'rate' => 7.05, 'constant' => 1298.45 ],
							[ 'income' => 81930, 'rate' => 7.85, 'constant' => 5208.38 ],
					],
					20 => [
							[ 'income' => 6250, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 41730, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 147210, 'rate' => 7.05, 'constant' => 1898.18 ],
							[ 'income' => 147210, 'rate' => 7.85, 'constant' => 9334.52 ],
					],
			],
			20120101 => [
					10 => [
							[ 'income' => 2150, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 25820, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 79880, 'rate' => 7.05, 'constant' => 1266.35 ],
							[ 'income' => 79880, 'rate' => 7.85, 'constant' => 5077.58 ],
					],
					20 => [
							[ 'income' => 6100, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 40690, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 143530, 'rate' => 7.05, 'constant' => 1850.57 ],
							[ 'income' => 143530, 'rate' => 7.85, 'constant' => 9100.79 ],
					],
			],
			20110101 => [
					10 => [
							[ 'income' => 2100, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 25200, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 77990, 'rate' => 7.05, 'constant' => 1235.85 ],
							[ 'income' => 77990, 'rate' => 7.85, 'constant' => 4957.55 ],
					],
					20 => [
							[ 'income' => 5950, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 39720, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 140120, 'rate' => 7.05, 'constant' => 1806.70 ],
							[ 'income' => 140120, 'rate' => 7.85, 'constant' => 8884.90 ],
					],
			],
			20100101 => [
					10 => [
							[ 'income' => 2050, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 24820, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 76830, 'rate' => 7.05, 'constant' => 1218.20 ],
							[ 'income' => 76830, 'rate' => 7.85, 'constant' => 4884.91 ],
					],
					20 => [
							[ 'income' => 7750, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 41030, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 139970, 'rate' => 7.05, 'constant' => 1780.48 ],
							[ 'income' => 139970, 'rate' => 7.85, 'constant' => 8755.75 ],
					],
			],
			20090101 => [
					10 => [
							[ 'income' => 2050, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 24780, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 76700, 'rate' => 7.05, 'constant' => 1216.06 ],
							[ 'income' => 76700, 'rate' => 7.85, 'constant' => 4876.42 ],
					],
					20 => [
							[ 'income' => 7750, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 40970, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 139720, 'rate' => 7.05, 'constant' => 1777.27 ],
							[ 'income' => 139720, 'rate' => 7.85, 'constant' => 8739.15 ],
					],
			],
			20080101 => [
					10 => [
							[ 'income' => 1950, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 23750, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 73540, 'rate' => 7.05, 'constant' => 1166.30 ],
							[ 'income' => 73540, 'rate' => 7.85, 'constant' => 4676.50 ],
					],
					20 => [
							[ 'income' => 7400, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 39260, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 133980, 'rate' => 7.05, 'constant' => 1704.51 ],
							[ 'income' => 133980, 'rate' => 7.85, 'constant' => 8382.27 ],
					],
			],
			20070101 => [
					10 => [
							[ 'income' => 1950, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 23260, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 71940, 'rate' => 7.05, 'constant' => 1140.09 ],
							[ 'income' => 71940, 'rate' => 7.85, 'constant' => 4572.03 ],
					],
					20 => [
							[ 'income' => 7300, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 38450, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 131050, 'rate' => 7.05, 'constant' => 1666.53 ],
							[ 'income' => 131050, 'rate' => 7.85, 'constant' => 8194.83 ],
					],
			],
			20060101 => [
					10 => [
							[ 'income' => 1850, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 22360, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 69210, 'rate' => 7.05, 'constant' => 1097.29 ],
							[ 'income' => 69210, 'rate' => 7.85, 'constant' => 4400.22 ],
					],
					20 => [
							[ 'income' => 6150, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 36130, 'rate' => 5.35, 'constant' => 0 ],
							[ 'income' => 125250, 'rate' => 7.05, 'constant' => 1603.93 ],
							[ 'income' => 125250, 'rate' => 7.85, 'constant' => 7886.89 ],
					],
			],
	];

	var $state_options = [
			20200101 => [
					'allowance' => 4300,
			],
			//01-Jan-19 - No Change
			20180101 => [
					'allowance' => 4150,
			],
			//01-Jan-17 - No Change
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
			20120101 => [
					'allowance' => 3800,
			],
			20110101 => [
					'allowance' => 3700,
			],
			//01-Jan-10: No Change
			20090101 => [
					'allowance' => 3650,
			],
			20080101 => [
					'allowance' => 3500,
			],
			20070101 => [
					'allowance' => 3400,
			],
			20060101 => [
					'allowance' => 3300,
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
