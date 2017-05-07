<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2017 TimeTrex Software Inc.
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
class PayrollDeduction_US_KS extends PayrollDeduction_US {

	var $state_income_tax_rate_options = array(
												20150101 => array(
															10 => array(
																	array( 'income' => 3000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 18000,	'rate' => 2.7,	'constant' => 0 ),
																	array( 'income' => 18000,	'rate' => 4.6,	'constant' => 405 ),
																	),
															20 => array(
																	array( 'income' => 6000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 36000,	'rate' => 2.7,	'constant' => 0 ),
																	array( 'income' => 36000,	'rate' => 4.6,	'constant' => 810 ),
																	),
															),
												20140101 => array(
															10 => array(
																	array( 'income' => 3000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 18000,	'rate' => 2.7,	'constant' => 0 ),
																	array( 'income' => 18000,	'rate' => 4.8,	'constant' => 405 ),
																	),
															20 => array(
																	array( 'income' => 6000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 36000,	'rate' => 2.7,	'constant' => 0 ),
																	array( 'income' => 36000,	'rate' => 4.8,	'constant' => 810 ),
																	),
															),
												20130101 => array(
															10 => array(
																	array( 'income' => 3000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 18000,	'rate' => 3.0,	'constant' => 0 ),
																	array( 'income' => 18000,	'rate' => 4.9,	'constant' => 450 ),
																	),
															20 => array(
																	array( 'income' => 6000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 36000,	'rate' => 3.0,	'constant' => 0 ),
																	array( 'income' => 36000,	'rate' => 4.9,	'constant' => 900 ),
																	),
															),
												20060101 => array(
															10 => array(
																	array( 'income' => 3000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 18000,	'rate' => 3.5,	'constant' => 0 ),
																	array( 'income' => 33000,	'rate' => 6.25,	'constant' => 525 ),
																	array( 'income' => 33000,	'rate' => 6.45,	'constant' => 1462.50 ),
																	),
															20 => array(
																	array( 'income' => 6000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 36000,	'rate' => 3.5,	'constant' => 0 ),
																	array( 'income' => 66000,	'rate' => 6.25,	'constant' => 1050 ),
																	array( 'income' => 66000,	'rate' => 6.45,	'constant' => 2925 ),
																),
															),
												);

	var $state_options = array(
								20060101 => array(
													'allowance' => 2250
													)
								);

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$state_allowance = $this->getStateAllowanceAmount();

		$income = bcsub( $annual_income, $state_allowance );

		Debug::text('State Annual Taxable Income: '. $income, __FILE__, __LINE__, __METHOD__, 10);

		return $income;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		$allowance_arr = $retarr['allowance'];

		$retval = bcmul( $this->getStateAllowance(), $allowance_arr );

		Debug::text('State Allowance Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function getStateTaxPayable() {
		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getStateRate($annual_income);
			$state_constant = $this->getData()->getStateConstant($annual_income);
			$state_rate_income = $this->getData()->getStateRatePreviousIncome($annual_income);

			$retval = bcadd( bcmul( bcsub( $annual_income, $state_rate_income ), $rate ), $state_constant );
			//$retval = bcadd( bcmul( $annual_income, $rate ), $state_constant );
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text('State Annual Tax Payable: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}
}
?>
