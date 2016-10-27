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
class PayrollDeduction_US_OK extends PayrollDeduction_US {

	var $state_income_tax_rate_options = array(
												20160101 => array(
															10 => array(
																	array( 'income' => 6300,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 7300,	'rate' => 0.5,	'constant' => 0 ),
																	array( 'income' => 8800,	'rate' => 1.0,	'constant' => 5 ),
																	array( 'income' => 10050,	'rate' => 2.0,	'constant' => 20 ),
																	array( 'income' => 11200,	'rate' => 3.0,	'constant' => 45 ),
																	array( 'income' => 13500,	'rate' => 4.0,	'constant' => 79.50 ),
																	array( 'income' => 13500,	'rate' => 5.0,	'constant' => 171.50 ),
																	),
															20 => array(
																	array( 'income' => 12600,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 14600,	'rate' => 0.5,	'constant' => 0 ),
																	array( 'income' => 17600,	'rate' => 1.0,	'constant' => 10 ),
																	array( 'income' => 20100,	'rate' => 2.0,	'constant' => 40 ),
																	array( 'income' => 22400,	'rate' => 3.0,	'constant' => 90 ),
																	array( 'income' => 24800,	'rate' => 4.0,	'constant' => 159 ),
																	array( 'income' => 24800,	'rate' => 5.0,	'constant' => 255 ),
																	),
															),
												20150101 => array(
															10 => array(
																	array( 'income' => 6300,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 7300,	'rate' => 0.5,	'constant' => 0 ),
																	array( 'income' => 8800,	'rate' => 1.0,	'constant' => 5 ),
																	array( 'income' => 10050,	'rate' => 2.0,	'constant' => 20 ),
																	array( 'income' => 11200,	'rate' => 3.0,	'constant' => 45 ),
																	array( 'income' => 13500,	'rate' => 4.0,	'constant' => 79.50 ),
																	array( 'income' => 15000,	'rate' => 5.0,	'constant' => 171.50 ),
																	array( 'income' => 15000,	'rate' => 5.25,	'constant' => 246.50 ),
																	),
															20 => array(
																	array( 'income' => 12600,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 14600,	'rate' => 0.5,	'constant' => 0 ),
																	array( 'income' => 17600,	'rate' => 1.0,	'constant' => 10 ),
																	array( 'income' => 20100,	'rate' => 2.0,	'constant' => 40 ),
																	array( 'income' => 22400,	'rate' => 3.0,	'constant' => 90 ),
																	array( 'income' => 24800,	'rate' => 4.0,	'constant' => 159 ),
																	array( 'income' => 27600,	'rate' => 5.0,	'constant' => 255 ),
																	array( 'income' => 27600,	'rate' => 5.25,	'constant' => 395 ),
																	),
															),
												20140101 => array(
															10 => array(
																	array( 'income' => 6200,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 7200,	'rate' => 0.5,	'constant' => 0 ),
																	array( 'income' => 8700,	'rate' => 1.0,	'constant' => 5 ),
																	array( 'income' => 9950,	'rate' => 2.0,	'constant' => 20 ),
																	array( 'income' => 11100,	'rate' => 3.0,	'constant' => 45 ),
																	array( 'income' => 13400,	'rate' => 4.0,	'constant' => 79.50 ),
																	array( 'income' => 14900,	'rate' => 5.0,	'constant' => 171.50 ),
																	array( 'income' => 14900,	'rate' => 5.25,	'constant' => 246.50 ),
																	),
															20 => array(
																	array( 'income' => 12400,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 14400,	'rate' => 0.5,	'constant' => 0 ),
																	array( 'income' => 17400,	'rate' => 1.0,	'constant' => 10 ),
																	array( 'income' => 19900,	'rate' => 2.0,	'constant' => 40 ),
																	array( 'income' => 22200,	'rate' => 3.0,	'constant' => 90 ),
																	array( 'income' => 24600,	'rate' => 4.0,	'constant' => 159 ),
																	array( 'income' => 27400,	'rate' => 5.0,	'constant' => 255 ),
																	array( 'income' => 27400,	'rate' => 5.25,	'constant' => 395 ),
																	),
															),
												20130101 => array(
															10 => array(
																	array( 'income' => 6100,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 7100,	'rate' => 0.5,	'constant' => 0 ),
																	array( 'income' => 8600,	'rate' => 1.0,	'constant' => 5 ),
																	array( 'income' => 9850,	'rate' => 2.0,	'constant' => 20 ),
																	array( 'income' => 11000,	'rate' => 3.0,	'constant' => 45 ),
																	array( 'income' => 13300,	'rate' => 4.0,	'constant' => 79.50 ),
																	array( 'income' => 14800,	'rate' => 5.0,	'constant' => 171.50 ),
																	array( 'income' => 14800,	'rate' => 5.25,	'constant' => 246.50 ),
																	),
															20 => array(
																	array( 'income' => 10150,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 12150,	'rate' => 0.5,	'constant' => 0 ),
																	array( 'income' => 15150,	'rate' => 1.0,	'constant' => 10 ),
																	array( 'income' => 17650,	'rate' => 2.0,	'constant' => 40 ),
																	array( 'income' => 19950,	'rate' => 3.0,	'constant' => 90 ),
																	array( 'income' => 22350,	'rate' => 4.0,	'constant' => 159 ),
																	array( 'income' => 25150,	'rate' => 5.0,	'constant' => 255 ),
																	array( 'income' => 25150,	'rate' => 5.25,	'constant' => 395 ),
																	),
															),
												20110101 => array(
															10 => array(
																	array( 'income' => 5800,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 6800,	'rate' => 0.5,	'constant' => 0 ),
																	array( 'income' => 8300,	'rate' => 1.0,	'constant' => 5 ),
																	array( 'income' => 9550,	'rate' => 2.0,	'constant' => 20 ),
																	array( 'income' => 10700,	'rate' => 3.0,	'constant' => 45 ),
																	array( 'income' => 13000,	'rate' => 4.0,	'constant' => 79.50 ),
																	array( 'income' => 14500,	'rate' => 5.0,	'constant' => 171.50 ),
																	array( 'income' => 14500,	'rate' => 5.5,	'constant' => 246.50 ),
																	),
															20 => array(
																	array( 'income' => 11600,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 13600,	'rate' => 0.5,	'constant' => 0 ),
																	array( 'income' => 16600,	'rate' => 1.0,	'constant' => 10 ),
																	array( 'income' => 19100,	'rate' => 2.0,	'constant' => 40 ),
																	array( 'income' => 21400,	'rate' => 3.0,	'constant' => 90 ),
																	array( 'income' => 23800,	'rate' => 4.0,	'constant' => 159 ),
																	array( 'income' => 26600,	'rate' => 5.0,	'constant' => 255 ),
																	array( 'income' => 26600,	'rate' => 5.5,	'constant' => 395 ),
																	),
															),
												20100101 => array(
															10 => array(
																	array( 'income' => 5700,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 6700,	'rate' => 0.5,	'constant' => 0 ),
																	array( 'income' => 8200,	'rate' => 1.0,	'constant' => 5 ),
																	array( 'income' => 9450,	'rate' => 2.0,	'constant' => 20 ),
																	array( 'income' => 10600,	'rate' => 3.0,	'constant' => 45 ),
																	array( 'income' => 12900,	'rate' => 4.0,	'constant' => 79.50 ),
																	array( 'income' => 14400,	'rate' => 5.0,	'constant' => 171.50 ),
																	array( 'income' => 14400,	'rate' => 5.5,	'constant' => 246.50 ),
																	),
															20 => array(
																	array( 'income' => 11400,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 13400,	'rate' => 0.5,	'constant' => 0 ),
																	array( 'income' => 16400,	'rate' => 1.0,	'constant' => 10 ),
																	array( 'income' => 18900,	'rate' => 2.0,	'constant' => 40 ),
																	array( 'income' => 21200,	'rate' => 3.0,	'constant' => 90 ),
																	array( 'income' => 23600,	'rate' => 4.0,	'constant' => 159 ),
																	array( 'income' => 26400,	'rate' => 5.0,	'constant' => 255 ),
																	array( 'income' => 26400,	'rate' => 5.5,	'constant' => 395 ),
																	),
															),
												20090101 => array(
															10 => array(
																	array( 'income' => 4250,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 5250,	'rate' => 0.5,	'constant' => 0 ),
																	array( 'income' => 6750,	'rate' => 1.0,	'constant' => 5 ),
																	array( 'income' => 8000,	'rate' => 2.0,	'constant' => 20 ),
																	array( 'income' => 9150,	'rate' => 3.0,	'constant' => 45 ),
																	array( 'income' => 11450,	'rate' => 4.0,	'constant' => 79.50 ),
																	array( 'income' => 12950,	'rate' => 5.0,	'constant' => 171.50 ),
																	array( 'income' => 12950,	'rate' => 5.5,	'constant' => 246.50 ),
																	),
															20 => array(
																	array( 'income' => 8500,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 10500,	'rate' => 0.5,	'constant' => 0 ),
																	array( 'income' => 13500,	'rate' => 1.0,	'constant' => 10 ),
																	array( 'income' => 16000,	'rate' => 2.0,	'constant' => 40 ),
																	array( 'income' => 18300,	'rate' => 3.0,	'constant' => 90 ),
																	array( 'income' => 20700,	'rate' => 4.0,	'constant' => 159 ),
																	array( 'income' => 23500,	'rate' => 5.0,	'constant' => 255 ),
																	array( 'income' => 23500,	'rate' => 5.5,	'constant' => 395 ),
																	),
															),
												20070101 => array(
															10 => array(
																	array( 'income' => 2750,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 3750,	'rate' => 0.5,	'constant' => 0 ),
																	array( 'income' => 5250,	'rate' => 1.0,	'constant' => 5 ),
																	array( 'income' => 6500,	'rate' => 2.0,	'constant' => 20 ),
																	array( 'income' => 7650,	'rate' => 3.0,	'constant' => 45 ),
																	array( 'income' => 9950,	'rate' => 4.0,	'constant' => 79.50 ),
																	array( 'income' => 11450,	'rate' => 5.0,	'constant' => 171.50 ),
																	array( 'income' => 11450,	'rate' => 5.65,	'constant' => 246.50 ),
																	),
															20 => array(
																	array( 'income' => 5500,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 7500,	'rate' => 0.5,	'constant' => 0 ),
																	array( 'income' => 10500,	'rate' => 1.0,	'constant' => 10 ),
																	array( 'income' => 13000,	'rate' => 2.0,	'constant' => 40 ),
																	array( 'income' => 15300,	'rate' => 3.0,	'constant' => 90 ),
																	array( 'income' => 17700,	'rate' => 4.0,	'constant' => 159 ),
																	array( 'income' => 20500,	'rate' => 5.0,	'constant' => 255 ),
																	array( 'income' => 20500,	'rate' => 5.65,	'constant' => 395 ),
																	),
															),
												20060101 => array(
															10 => array(
																	array( 'income' => 2000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 3000,	'rate' => 0.5,	'constant' => 0 ),
																	array( 'income' => 4500,	'rate' => 1.0,	'constant' => 5 ),
																	array( 'income' => 5750,	'rate' => 2.0,	'constant' => 20 ),
																	array( 'income' => 6900,	'rate' => 3.0,	'constant' => 45 ),
																	array( 'income' => 9200,	'rate' => 4.0,	'constant' => 79.50 ),
																	array( 'income' => 10700,	'rate' => 5.0,	'constant' => 171.50 ),
																	array( 'income' => 12500,	'rate' => 6.0,	'constant' => 246.50 ),
																	array( 'income' => 12500,	'rate' => 6.25,	'constant' => 354.50 ),
																	),
															20 => array(
																	array( 'income' => 3000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 5000,	'rate' => 0.5,	'constant' => 0 ),
																	array( 'income' => 8500,	'rate' => 1.0,	'constant' => 10 ),
																	array( 'income' => 10500,	'rate' => 2.0,	'constant' => 40 ),
																	array( 'income' => 12800,	'rate' => 3.0,	'constant' => 90 ),
																	array( 'income' => 15200,	'rate' => 4.0,	'constant' => 159 ),
																	array( 'income' => 18000,	'rate' => 5.0,	'constant' => 255 ),
																	array( 'income' => 24000,	'rate' => 6.0,	'constant' => 395 ),
																	array( 'income' => 24000,	'rate' => 6.25,	'constant' => 755 ),
																),
															),
												);


	var $state_options = array(
								20060101 => array(
													'allowance' => 1000
													)
								);

	function getStatePayPeriodDeductionRoundedValue( $amount ) {
		return $this->RoundNearestDollar( $amount );
	}
								
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
