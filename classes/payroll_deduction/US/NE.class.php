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
class PayrollDeduction_US_NE extends PayrollDeduction_US {

	var $state_income_tax_rate_options = array(
												20170101 => array( //10=40, 20=30
															10 => array(
																	array( 'income' => 2975,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 5480,	'rate' => 2.26,	'constant' => 0 ),
																	array( 'income' => 17790,	'rate' => 3.22,	'constant' => 56.61 ),
																	array( 'income' => 25780,	'rate' => 4.91,	'constant' => 452.99 ),
																	array( 'income' => 32730,	'rate' => 6.20,	'constant' => 845.30 ),
																	array( 'income' => 61470,	'rate' => 6.59,	'constant' => 1276.20 ),
																	array( 'income' => 61470,	'rate' => 6.95,	'constant' => 3170.17 ),
																	),
															20 => array(
																	array( 'income' => 7100,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 10610,	'rate' => 2.26,	'constant' => 0 ),
																	array( 'income' => 26420,	'rate' => 3.22,	'constant' => 79.33 ),
																	array( 'income' => 41100,	'rate' => 4.91,	'constant' => 588.41 ),
																	array( 'income' => 50990,	'rate' => 6.20,	'constant' => 1309.20 ),
																	array( 'income' => 67620,	'rate' => 6.59,	'constant' => 1922.38 ),
																	array( 'income' => 67620,	'rate' => 6.95,	'constant' => 3018.30 ),
																	),
															30 => array(
																	array( 'income' => 7100,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 10610,	'rate' => 2.26,	'constant' => 0 ),
																	array( 'income' => 26420,	'rate' => 3.22,	'constant' => 79.33 ),
																	array( 'income' => 41100,	'rate' => 4.91,	'constant' => 588.41 ),
																	array( 'income' => 50990,	'rate' => 6.20,	'constant' => 1309.20 ),
																	array( 'income' => 67620,	'rate' => 6.59,	'constant' => 1922.38 ),
																	array( 'income' => 67620,	'rate' => 6.95,	'constant' => 3018.30 ),
																	),
															40 => array(
																	array( 'income' => 2975,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 5480,	'rate' => 2.26,	'constant' => 0 ),
																	array( 'income' => 17790,	'rate' => 3.22,	'constant' => 56.61 ),
																	array( 'income' => 25780,	'rate' => 4.91,	'constant' => 452.99 ),
																	array( 'income' => 32730,	'rate' => 6.20,	'constant' => 845.30 ),
																	array( 'income' => 61470,	'rate' => 6.59,	'constant' => 1276.20 ),
																	array( 'income' => 61470,	'rate' => 6.95,	'constant' => 3170.17 ),
																	),
															),
												20130101 => array(
															10 => array(
																	array( 'income' => 2975,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 5325,	'rate' => 2.26,	'constant' => 0 ),
																	array( 'income' => 17275,	'rate' => 3.22,	'constant' => 53.11 ),
																	array( 'income' => 25025,	'rate' => 4.91,	'constant' => 437.90 ),
																	array( 'income' => 31775,	'rate' => 6.20,	'constant' => 818.43 ),
																	array( 'income' => 59675,	'rate' => 6.59,	'constant' => 1236.93 ),
																	array( 'income' => 59675,	'rate' => 6.95,	'constant' => 3075.54 ),
																	),
															20 => array(
																	array( 'income' => 7100,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 10300,	'rate' => 2.26,	'constant' => 0 ),
																	array( 'income' => 25650,	'rate' => 3.22,	'constant' => 72.32 ),
																	array( 'income' => 39900,	'rate' => 4.91,	'constant' => 566.59 ),
																	array( 'income' => 49500,	'rate' => 6.20,	'constant' => 1266.27 ),
																	array( 'income' => 65650,	'rate' => 6.59,	'constant' => 1861.47 ),
																	array( 'income' => 65650,	'rate' => 6.95,	'constant' => 2925.76 ),
																	),
															30 => array(
																	array( 'income' => 7100,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 10300,	'rate' => 2.26,	'constant' => 0 ),
																	array( 'income' => 25650,	'rate' => 3.22,	'constant' => 72.32 ),
																	array( 'income' => 39900,	'rate' => 4.91,	'constant' => 566.59 ),
																	array( 'income' => 49500,	'rate' => 6.20,	'constant' => 1266.27 ),
																	array( 'income' => 65650,	'rate' => 6.59,	'constant' => 1861.47 ),
																	array( 'income' => 65650,	'rate' => 6.95,	'constant' => 2925.76 ),
																	),
															40 => array(
																	array( 'income' => 2975,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 5325,	'rate' => 2.26,	'constant' => 0 ),
																	array( 'income' => 17275,	'rate' => 3.22,	'constant' => 53.11 ),
																	array( 'income' => 25025,	'rate' => 4.91,	'constant' => 437.90 ),
																	array( 'income' => 31775,	'rate' => 6.20,	'constant' => 818.43 ),
																	array( 'income' => 59675,	'rate' => 6.59,	'constant' => 1236.93 ),
																	array( 'income' => 59675,	'rate' => 6.95,	'constant' => 3075.54 ),
																	),
															),
												20100101 => array(
															10 => array(
																	array( 'income' => 2400,	'rate' => 2.56,	'constant' => 0 ),
																	array( 'income' => 17500,	'rate' => 3.57,	'constant' => 61.44 ),
																	array( 'income' => 27000,	'rate' => 5.12,	'constant' => 600.51 ),
																	array( 'income' => 27000,	'rate' => 6.84,	'constant' => 1086.91 ),
																	),
															20 => array(
																	array( 'income' => 4800,	'rate' => 2.56,	'constant' => 0 ),
																	array( 'income' => 35000,	'rate' => 3.57,	'constant' => 122.88 ),
																	array( 'income' => 54000,	'rate' => 5.12,	'constant' => 1201.02 ),
																	array( 'income' => 54000,	'rate' => 6.84,	'constant' => 2173.82 ),
																	),
															30 => array(
																	array( 'income' => 2400,	'rate' => 2.56,	'constant' => 0 ),
																	array( 'income' => 17500,	'rate' => 3.57,	'constant' => 61.44 ),
																	array( 'income' => 27000,	'rate' => 5.12,	'constant' => 600.51 ),
																	array( 'income' => 27000,	'rate' => 6.84,	'constant' => 1086.91 ),
																	),
															40 => array(
																	array( 'income' => 4500,	'rate' => 2.56,	'constant' => 0 ),
																	array( 'income' => 28000,	'rate' => 3.57,	'constant' => 115.20 ),
																	array( 'income' => 40000,	'rate' => 5.12,	'constant' => 954.15 ),
																	array( 'income' => 40000,	'rate' => 6.84,	'constant' => 1568.55 ),
																	),
															),
												20080101 => array(
															10 => array(
																	array( 'income' => 2200,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 4400,	'rate' => 2.35,	'constant' => 0 ),
																	array( 'income' => 15500,	'rate' => 3.27,	'constant' => 51.70 ),
																	array( 'income' => 22750,	'rate' => 5.02,	'constant' => 414.67 ),
																	array( 'income' => 29000,	'rate' => 6.20,	'constant' => 778.62 ),
																	array( 'income' => 55000,	'rate' => 6.59,	'constant' => 1166.12 ),
																	array( 'income' => 55000,	'rate' => 6.95,	'constant' => 2879.52 ),
																	),
															20 => array(
																	array( 'income' => 6450,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 9450,	'rate' => 2.35,	'constant' => 0 ),
																	array( 'income' => 23750,	'rate' => 3.27,	'constant' => 70.50 ),
																	array( 'income' => 37000,	'rate' => 5.02,	'constant' => 538.11 ),
																	array( 'income' => 46000,	'rate' => 6.20,	'constant' => 1203.26 ),
																	array( 'income' => 61000,	'rate' => 6.59,	'constant' => 1761.26 ),
																	array( 'income' => 61000,	'rate' => 6.95,	'constant' => 2749.76 ),
																	),
															),
												20070101 => array(
															10 => array(
																	array( 'income' => 2200,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 4400,	'rate' => 2.43,	'constant' => 0 ),
																	array( 'income' => 15500,	'rate' => 3.38,	'constant' => 53.46 ),
																	array( 'income' => 22750,	'rate' => 5.19,	'constant' => 428.64 ),
																	array( 'income' => 28100,	'rate' => 6.41,	'constant' => 804.92 ),
																	array( 'income' => 54100,	'rate' => 6.81,	'constant' => 1147.86 ),
																	array( 'income' => 75100,	'rate' => 7.04,	'constant' => 2918.46 ),
																	array( 'income' => 75100,	'rate' => 7.18,	'constant' => 4396.86 ),
																	),
															20 => array(
																	array( 'income' => 5250,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 8250,	'rate' => 2.43,	'constant' => 0 ),
																	array( 'income' => 22400,	'rate' => 3.38,	'constant' => 72.90 ),
																	array( 'income' => 35400,	'rate' => 5.19,	'constant' => 551.17 ),
																	array( 'income' => 42950,	'rate' => 6.41,	'constant' => 1225.87 ),
																	array( 'income' => 58250,	'rate' => 6.81,	'constant' => 1709.83 ),
																	array( 'income' => 75250,	'rate' => 7.04,	'constant' => 2751.76 ),
																	array( 'income' => 75250,	'rate' => 7.18,	'constant' => 3948.56 ),
																	),
															),
												20060101 => array(
															10 => array(
																	array( 'income' => 2000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 4400,	'rate' => 2.49,	'constant' => 0 ),
																	array( 'income' => 15500,	'rate' => 3.47,	'constant' => 54.78 ),
																	array( 'income' => 22750,	'rate' => 5.32,	'constant' => 439.95 ),
																	array( 'income' => 28100,	'rate' => 6.57,	'constant' => 825.65 ),
																	array( 'income' => 54100,	'rate' => 6.98,	'constant' => 1177.15 ),
																	array( 'income' => 75100,	'rate' => 7.22,	'constant' => 2991.95 ),
																	array( 'income' => 75100,	'rate' => 7.36,	'constant' => 4508.15 ),
																	),
															20 => array(
																	array( 'income' => 5250,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 8250,	'rate' => 2.49,	'constant' => 0 ),
																	array( 'income' => 22400,	'rate' => 3.47,	'constant' => 74.70 ),
																	array( 'income' => 35400,	'rate' => 5.32,	'constant' => 565.71 ),
																	array( 'income' => 42950,	'rate' => 6.57,	'constant' => 1257.35 ),
																	array( 'income' => 58250,	'rate' => 6.98,	'constant' => 1753.35 ),
																	array( 'income' => 75250,	'rate' => 7.22,	'constant' => 2821.29 ),
																	array( 'income' => 75250,	'rate' => 7.36,	'constant' => 4048.69 ),
																),
															),
												);

	var $state_options = array(
								20170101 => array( // 01-Jan-2017
													'allowance' => 1960
												   ),
								20130101 => array( // 01-Jan-2013
													'allowance' => 1900
												   ),
								20100101 => array( //01-Jan-2010: Formula changed, this is no longer used.
													'allowance' => 118
													),
								20080101 => array( //01-Jan-2008
													'allowance' => 113
													),
								20070101 => array( //01-Jan-2007
													'allowance' => 111
													),
								20060101 => array( //01-Jan-2006
													'allowance' => 103
													)
								);

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();

		if ( $this->getDate() >= 20130101 ) {
			$state_allowance = $this->getStateAllowanceAmount();
			$income = bcsub( $annual_income, $state_allowance );
		} else {
			$income = $annual_income;
		}

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
			Debug::text('aState Annual Tax Payable: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

			if ( $this->getDate() < 20130101 ) {
				$retval = bcsub( $retval, $this->getStateAllowanceAmount() );
			}
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text('State Annual Tax Payable: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}
}
?>
