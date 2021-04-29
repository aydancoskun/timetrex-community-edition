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
class PayrollDeduction_US_KY extends PayrollDeduction_US {

	var $state_income_tax_rate_options = [
		//20180101 - Switched to 5% flat rate, see getStateTaxPayable() below
		20060101 => [
				0 => [
						[ 'income' => 3000, 'rate' => 2, 'constant' => 0 ],
						[ 'income' => 4000, 'rate' => 3, 'constant' => 60 ],
						[ 'income' => 5000, 'rate' => 4, 'constant' => 90 ],
						[ 'income' => 8000, 'rate' => 5, 'constant' => 130 ],
						[ 'income' => 75000, 'rate' => 5.8, 'constant' => 280 ],
						[ 'income' => 75000, 'rate' => 6, 'constant' => 4166 ],
				],
		],
	];

	var $state_options = [
			20200101 => [
					'standard_deduction' => 2650,
					'allowance'          => 0, //Removed as of 2018
			],
			20190101 => [
					'standard_deduction' => 2590,
					'allowance'          => 0, //Removed as of 2018
			],
			20180101 => [
					'standard_deduction' => 2530,
					'allowance'          => 0, //Removed as of 2018
			],
			20170101 => [
					'standard_deduction' => 2480,
					'allowance'          => 10,
			],
			20160101 => [
					'standard_deduction' => 2460,
					'allowance'          => 20,
			],
			20150101 => [
					'standard_deduction' => 2440,
					'allowance'          => 20,
			],
			20140101 => [
					'standard_deduction' => 2400,
					'allowance'          => 20,
			],
			20130101 => [
					'standard_deduction' => 2360,
					'allowance'          => 20,
			],
			20120101 => [
					'standard_deduction' => 2290,
					'allowance'          => 20,
			],
			20090101 => [
					'standard_deduction' => 2190,
					'allowance'          => 20,
			],
			20080101 => [
					'standard_deduction' => 2100,
					'allowance'          => 20,
			],
			20070101 => [
					'standard_deduction' => 2050,
					'allowance'          => 20,
			],
			20060101 => [
					'standard_deduction' => 1970,
					'allowance'          => 22,
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

		Debug::text( 'State Standard Deduction Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

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
			if ( $this->getDate() >= 20180101 ) {        //Switched to flat rate 5%
				$retval = bcmul( $annual_income, 0.05 ); //5%
			} else {
				$rate = $this->getData()->getStateRate( $annual_income );
				$prev_income = $this->getData()->getStateRatePreviousIncome( $annual_income );
				$state_constant = $this->getData()->getStateConstant( $annual_income );

				$retval = bcadd( bcmul( bcsub( $annual_income, $prev_income ), $rate ), $state_constant );
			}
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
