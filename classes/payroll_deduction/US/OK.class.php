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


/**
 * @package PayrollDeduction\US
 */
class PayrollDeduction_US_OK extends PayrollDeduction_US {

	var $state_income_tax_rate_options = [
		//20200101 - No Change
		//20190101 - No Change
		//20180101 - No Change
		20170101 => [
				10 => [
						[ 'income' => 6350, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 7350, 'rate' => 0.5, 'constant' => 0 ],
						[ 'income' => 8850, 'rate' => 1.0, 'constant' => 5 ],
						[ 'income' => 10100, 'rate' => 2.0, 'constant' => 20 ],
						[ 'income' => 11250, 'rate' => 3.0, 'constant' => 45 ],
						[ 'income' => 13550, 'rate' => 4.0, 'constant' => 79.50 ],
						[ 'income' => 13550, 'rate' => 5.0, 'constant' => 171.50 ],
				],
				20 => [
						[ 'income' => 12700, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 14700, 'rate' => 0.5, 'constant' => 0 ],
						[ 'income' => 17700, 'rate' => 1.0, 'constant' => 10 ],
						[ 'income' => 20200, 'rate' => 2.0, 'constant' => 40 ],
						[ 'income' => 22500, 'rate' => 3.0, 'constant' => 90 ],
						[ 'income' => 24900, 'rate' => 4.0, 'constant' => 159 ],
						[ 'income' => 24900, 'rate' => 5.0, 'constant' => 255 ],
				],
		],
		20160101 => [
				10 => [
						[ 'income' => 6300, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 7300, 'rate' => 0.5, 'constant' => 0 ],
						[ 'income' => 8800, 'rate' => 1.0, 'constant' => 5 ],
						[ 'income' => 10050, 'rate' => 2.0, 'constant' => 20 ],
						[ 'income' => 11200, 'rate' => 3.0, 'constant' => 45 ],
						[ 'income' => 13500, 'rate' => 4.0, 'constant' => 79.50 ],
						[ 'income' => 13500, 'rate' => 5.0, 'constant' => 171.50 ],
				],
				20 => [
						[ 'income' => 12600, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 14600, 'rate' => 0.5, 'constant' => 0 ],
						[ 'income' => 17600, 'rate' => 1.0, 'constant' => 10 ],
						[ 'income' => 20100, 'rate' => 2.0, 'constant' => 40 ],
						[ 'income' => 22400, 'rate' => 3.0, 'constant' => 90 ],
						[ 'income' => 24800, 'rate' => 4.0, 'constant' => 159 ],
						[ 'income' => 24800, 'rate' => 5.0, 'constant' => 255 ],
				],
		],
		20150101 => [
				10 => [
						[ 'income' => 6300, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 7300, 'rate' => 0.5, 'constant' => 0 ],
						[ 'income' => 8800, 'rate' => 1.0, 'constant' => 5 ],
						[ 'income' => 10050, 'rate' => 2.0, 'constant' => 20 ],
						[ 'income' => 11200, 'rate' => 3.0, 'constant' => 45 ],
						[ 'income' => 13500, 'rate' => 4.0, 'constant' => 79.50 ],
						[ 'income' => 15000, 'rate' => 5.0, 'constant' => 171.50 ],
						[ 'income' => 15000, 'rate' => 5.25, 'constant' => 246.50 ],
				],
				20 => [
						[ 'income' => 12600, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 14600, 'rate' => 0.5, 'constant' => 0 ],
						[ 'income' => 17600, 'rate' => 1.0, 'constant' => 10 ],
						[ 'income' => 20100, 'rate' => 2.0, 'constant' => 40 ],
						[ 'income' => 22400, 'rate' => 3.0, 'constant' => 90 ],
						[ 'income' => 24800, 'rate' => 4.0, 'constant' => 159 ],
						[ 'income' => 27600, 'rate' => 5.0, 'constant' => 255 ],
						[ 'income' => 27600, 'rate' => 5.25, 'constant' => 395 ],
				],
		],
		20140101 => [
				10 => [
						[ 'income' => 6200, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 7200, 'rate' => 0.5, 'constant' => 0 ],
						[ 'income' => 8700, 'rate' => 1.0, 'constant' => 5 ],
						[ 'income' => 9950, 'rate' => 2.0, 'constant' => 20 ],
						[ 'income' => 11100, 'rate' => 3.0, 'constant' => 45 ],
						[ 'income' => 13400, 'rate' => 4.0, 'constant' => 79.50 ],
						[ 'income' => 14900, 'rate' => 5.0, 'constant' => 171.50 ],
						[ 'income' => 14900, 'rate' => 5.25, 'constant' => 246.50 ],
				],
				20 => [
						[ 'income' => 12400, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 14400, 'rate' => 0.5, 'constant' => 0 ],
						[ 'income' => 17400, 'rate' => 1.0, 'constant' => 10 ],
						[ 'income' => 19900, 'rate' => 2.0, 'constant' => 40 ],
						[ 'income' => 22200, 'rate' => 3.0, 'constant' => 90 ],
						[ 'income' => 24600, 'rate' => 4.0, 'constant' => 159 ],
						[ 'income' => 27400, 'rate' => 5.0, 'constant' => 255 ],
						[ 'income' => 27400, 'rate' => 5.25, 'constant' => 395 ],
				],
		],
		20130101 => [
				10 => [
						[ 'income' => 6100, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 7100, 'rate' => 0.5, 'constant' => 0 ],
						[ 'income' => 8600, 'rate' => 1.0, 'constant' => 5 ],
						[ 'income' => 9850, 'rate' => 2.0, 'constant' => 20 ],
						[ 'income' => 11000, 'rate' => 3.0, 'constant' => 45 ],
						[ 'income' => 13300, 'rate' => 4.0, 'constant' => 79.50 ],
						[ 'income' => 14800, 'rate' => 5.0, 'constant' => 171.50 ],
						[ 'income' => 14800, 'rate' => 5.25, 'constant' => 246.50 ],
				],
				20 => [
						[ 'income' => 10150, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 12150, 'rate' => 0.5, 'constant' => 0 ],
						[ 'income' => 15150, 'rate' => 1.0, 'constant' => 10 ],
						[ 'income' => 17650, 'rate' => 2.0, 'constant' => 40 ],
						[ 'income' => 19950, 'rate' => 3.0, 'constant' => 90 ],
						[ 'income' => 22350, 'rate' => 4.0, 'constant' => 159 ],
						[ 'income' => 25150, 'rate' => 5.0, 'constant' => 255 ],
						[ 'income' => 25150, 'rate' => 5.25, 'constant' => 395 ],
				],
		],
		20110101 => [
				10 => [
						[ 'income' => 5800, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 6800, 'rate' => 0.5, 'constant' => 0 ],
						[ 'income' => 8300, 'rate' => 1.0, 'constant' => 5 ],
						[ 'income' => 9550, 'rate' => 2.0, 'constant' => 20 ],
						[ 'income' => 10700, 'rate' => 3.0, 'constant' => 45 ],
						[ 'income' => 13000, 'rate' => 4.0, 'constant' => 79.50 ],
						[ 'income' => 14500, 'rate' => 5.0, 'constant' => 171.50 ],
						[ 'income' => 14500, 'rate' => 5.5, 'constant' => 246.50 ],
				],
				20 => [
						[ 'income' => 11600, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 13600, 'rate' => 0.5, 'constant' => 0 ],
						[ 'income' => 16600, 'rate' => 1.0, 'constant' => 10 ],
						[ 'income' => 19100, 'rate' => 2.0, 'constant' => 40 ],
						[ 'income' => 21400, 'rate' => 3.0, 'constant' => 90 ],
						[ 'income' => 23800, 'rate' => 4.0, 'constant' => 159 ],
						[ 'income' => 26600, 'rate' => 5.0, 'constant' => 255 ],
						[ 'income' => 26600, 'rate' => 5.5, 'constant' => 395 ],
				],
		],
		20100101 => [
				10 => [
						[ 'income' => 5700, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 6700, 'rate' => 0.5, 'constant' => 0 ],
						[ 'income' => 8200, 'rate' => 1.0, 'constant' => 5 ],
						[ 'income' => 9450, 'rate' => 2.0, 'constant' => 20 ],
						[ 'income' => 10600, 'rate' => 3.0, 'constant' => 45 ],
						[ 'income' => 12900, 'rate' => 4.0, 'constant' => 79.50 ],
						[ 'income' => 14400, 'rate' => 5.0, 'constant' => 171.50 ],
						[ 'income' => 14400, 'rate' => 5.5, 'constant' => 246.50 ],
				],
				20 => [
						[ 'income' => 11400, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 13400, 'rate' => 0.5, 'constant' => 0 ],
						[ 'income' => 16400, 'rate' => 1.0, 'constant' => 10 ],
						[ 'income' => 18900, 'rate' => 2.0, 'constant' => 40 ],
						[ 'income' => 21200, 'rate' => 3.0, 'constant' => 90 ],
						[ 'income' => 23600, 'rate' => 4.0, 'constant' => 159 ],
						[ 'income' => 26400, 'rate' => 5.0, 'constant' => 255 ],
						[ 'income' => 26400, 'rate' => 5.5, 'constant' => 395 ],
				],
		],
		20090101 => [
				10 => [
						[ 'income' => 4250, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 5250, 'rate' => 0.5, 'constant' => 0 ],
						[ 'income' => 6750, 'rate' => 1.0, 'constant' => 5 ],
						[ 'income' => 8000, 'rate' => 2.0, 'constant' => 20 ],
						[ 'income' => 9150, 'rate' => 3.0, 'constant' => 45 ],
						[ 'income' => 11450, 'rate' => 4.0, 'constant' => 79.50 ],
						[ 'income' => 12950, 'rate' => 5.0, 'constant' => 171.50 ],
						[ 'income' => 12950, 'rate' => 5.5, 'constant' => 246.50 ],
				],
				20 => [
						[ 'income' => 8500, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 10500, 'rate' => 0.5, 'constant' => 0 ],
						[ 'income' => 13500, 'rate' => 1.0, 'constant' => 10 ],
						[ 'income' => 16000, 'rate' => 2.0, 'constant' => 40 ],
						[ 'income' => 18300, 'rate' => 3.0, 'constant' => 90 ],
						[ 'income' => 20700, 'rate' => 4.0, 'constant' => 159 ],
						[ 'income' => 23500, 'rate' => 5.0, 'constant' => 255 ],
						[ 'income' => 23500, 'rate' => 5.5, 'constant' => 395 ],
				],
		],
		20070101 => [
				10 => [
						[ 'income' => 2750, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 3750, 'rate' => 0.5, 'constant' => 0 ],
						[ 'income' => 5250, 'rate' => 1.0, 'constant' => 5 ],
						[ 'income' => 6500, 'rate' => 2.0, 'constant' => 20 ],
						[ 'income' => 7650, 'rate' => 3.0, 'constant' => 45 ],
						[ 'income' => 9950, 'rate' => 4.0, 'constant' => 79.50 ],
						[ 'income' => 11450, 'rate' => 5.0, 'constant' => 171.50 ],
						[ 'income' => 11450, 'rate' => 5.65, 'constant' => 246.50 ],
				],
				20 => [
						[ 'income' => 5500, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 7500, 'rate' => 0.5, 'constant' => 0 ],
						[ 'income' => 10500, 'rate' => 1.0, 'constant' => 10 ],
						[ 'income' => 13000, 'rate' => 2.0, 'constant' => 40 ],
						[ 'income' => 15300, 'rate' => 3.0, 'constant' => 90 ],
						[ 'income' => 17700, 'rate' => 4.0, 'constant' => 159 ],
						[ 'income' => 20500, 'rate' => 5.0, 'constant' => 255 ],
						[ 'income' => 20500, 'rate' => 5.65, 'constant' => 395 ],
				],
		],
		20060101 => [
				10 => [
						[ 'income' => 2000, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 3000, 'rate' => 0.5, 'constant' => 0 ],
						[ 'income' => 4500, 'rate' => 1.0, 'constant' => 5 ],
						[ 'income' => 5750, 'rate' => 2.0, 'constant' => 20 ],
						[ 'income' => 6900, 'rate' => 3.0, 'constant' => 45 ],
						[ 'income' => 9200, 'rate' => 4.0, 'constant' => 79.50 ],
						[ 'income' => 10700, 'rate' => 5.0, 'constant' => 171.50 ],
						[ 'income' => 12500, 'rate' => 6.0, 'constant' => 246.50 ],
						[ 'income' => 12500, 'rate' => 6.25, 'constant' => 354.50 ],
				],
				20 => [
						[ 'income' => 3000, 'rate' => 0, 'constant' => 0 ],
						[ 'income' => 5000, 'rate' => 0.5, 'constant' => 0 ],
						[ 'income' => 8500, 'rate' => 1.0, 'constant' => 10 ],
						[ 'income' => 10500, 'rate' => 2.0, 'constant' => 40 ],
						[ 'income' => 12800, 'rate' => 3.0, 'constant' => 90 ],
						[ 'income' => 15200, 'rate' => 4.0, 'constant' => 159 ],
						[ 'income' => 18000, 'rate' => 5.0, 'constant' => 255 ],
						[ 'income' => 24000, 'rate' => 6.0, 'constant' => 395 ],
						[ 'income' => 24000, 'rate' => 6.25, 'constant' => 755 ],
				],
		],
	];


	var $state_options = [
			20060101 => [
					'allowance' => 1000,
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
