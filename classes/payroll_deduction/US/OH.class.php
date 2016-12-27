<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2016 TimeTrex Software Inc.
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
class PayrollDeduction_US_OH extends PayrollDeduction_US {

	var $state_income_tax_rate_options = array(
												20150801 => array(
															0 => array(
																	array( 'income' => 5000,	'rate' => 0.556,	'constant' => 0 ),
																	array( 'income' => 10000,	'rate' => 1.112,	'constant' => 27.80 ),
																	array( 'income' => 15000,	'rate' => 2.226,	'constant' => 83.40 ),
																	array( 'income' => 20000,	'rate' => 2.782,	'constant' => 194.70 ),
																	array( 'income' => 40000,	'rate' => 3.338,	'constant' => 333.80 ),
																	array( 'income' => 80000,	'rate' => 3.894,	'constant' => 1001.40 ),
																	array( 'income' => 100000,	'rate' => 4.451,	'constant' => 2559.00 ),
																	array( 'income' => 100000,	'rate' => 5.563,	'constant' => 3449.20 ),
																	),
															),
												20140701 => array(
															0 => array(
																	array( 'income' => 5000,	'rate' => 0.574,	'constant' => 0 ),
																	array( 'income' => 10000,	'rate' => 1.148,	'constant' => 28.70 ),
																	array( 'income' => 15000,	'rate' => 2.297,	'constant' => 86.10 ),
																	array( 'income' => 20000,	'rate' => 2.871,	'constant' => 200.95 ),
																	array( 'income' => 40000,	'rate' => 3.445,	'constant' => 344.50 ),
																	array( 'income' => 80000,	'rate' => 4.019,	'constant' => 1033.50 ),
																	array( 'income' => 100000,	'rate' => 4.593,	'constant' => 2641.10 ),
																	array( 'income' => 100000,	'rate' => 5.741,	'constant' => 3559.70 ),
																	),
															),
												20130901 => array(
															0 => array(
																	array( 'income' => 5000,	'rate' => 0.581,	'constant' => 0 ),
																	array( 'income' => 10000,	'rate' => 1.161,	'constant' => 29.05 ),
																	array( 'income' => 15000,	'rate' => 2.322,	'constant' => 87.10 ),
																	array( 'income' => 20000,	'rate' => 2.903,	'constant' => 203.20 ),
																	array( 'income' => 40000,	'rate' => 3.483,	'constant' => 348.35 ),
																	array( 'income' => 80000,	'rate' => 4.064,	'constant' => 1044.95 ),
																	array( 'income' => 100000,	'rate' => 4.644,	'constant' => 2670.55 ),
																	array( 'income' => 100000,	'rate' => 5.805,	'constant' => 3599.35 ),
																	),
															),
												20090101 => array(
															0 => array(
																	array( 'income' => 5000,	'rate' => 0.638,	'constant' => 0 ),
																	array( 'income' => 10000,	'rate' => 1.276,	'constant' => 31.90 ),
																	array( 'income' => 15000,	'rate' => 2.552,	'constant' => 95.70 ),
																	array( 'income' => 20000,	'rate' => 3.190,	'constant' => 223.30 ),
																	array( 'income' => 40000,	'rate' => 3.828,	'constant' => 382.80 ),
																	array( 'income' => 80000,	'rate' => 4.466,	'constant' => 1148.40 ),
																	array( 'income' => 100000,	'rate' => 5.103,	'constant' => 2934.80 ),
																	array( 'income' => 100000,	'rate' => 6.379,	'constant' => 3955.40 ),
																	),
															),
												20080101 => array(
															0 => array(
																	array( 'income' => 5000,	'rate' => 0.672,	'constant' => 0 ),
																	array( 'income' => 10000,	'rate' => 1.344,	'constant' => 33.60 ),
																	array( 'income' => 15000,	'rate' => 2.687,	'constant' => 100.80 ),
																	array( 'income' => 20000,	'rate' => 3.360,	'constant' => 235.15 ),
																	array( 'income' => 40000,	'rate' => 4.031,	'constant' => 403.15 ),
																	array( 'income' => 80000,	'rate' => 4.703,	'constant' => 1209.35 ),
																	array( 'income' => 100000,	'rate' => 5.375,	'constant' => 3090.55 ),
																	array( 'income' => 100000,	'rate' => 6.718,	'constant' => 4165.55 ),
																	),
															),
												20060101 => array(
															0 => array(
																	array( 'income' => 5000,	'rate' => 0.774,	'constant' => 0 ),
																	array( 'income' => 10000,	'rate' => 1.547,	'constant' => 38.70 ),
																	array( 'income' => 15000,	'rate' => 3.094,	'constant' => 116.05 ),
																	array( 'income' => 20000,	'rate' => 3.868,	'constant' => 270.75 ),
																	array( 'income' => 40000,	'rate' => 4.642,	'constant' => 464.15 ),
																	array( 'income' => 80000,	'rate' => 5.416,	'constant' => 1392.55 ),
																	array( 'income' => 100000,	'rate' => 6.189,	'constant' => 3558.95 ),
																	array( 'income' => 100000,	'rate' => 7.736,	'constant' => 4796.75 ),
																),
															),
												);

	var $state_options = array(
								//01-Jan-09: No Change.
								20080101 => array(
													'allowance' => 650
													),
								20060101 => array(
													'allowance' => 650
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
