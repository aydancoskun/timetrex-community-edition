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
class PayrollDeduction_US_ND extends PayrollDeduction_US {

	var $state_income_tax_rate_options = array(
												20170101 => array(
															10 => array(
																	array( 'income' => 4300,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 41000,	'rate' => 1.10,	'constant' => 0 ),
																	array( 'income' => 84000,	'rate' => 2.04,	'constant' => 403.70 ),
																	array( 'income' => 194000,	'rate' => 2.27,	'constant' => 1280.90 ),
																	array( 'income' => 416000,	'rate' => 2.64,	'constant' => 3777.90 ),
																	array( 'income' => 416000,	'rate' => 2.90,	'constant' => 9638.70 ),
																	),
															20 => array(
																	array( 'income' => 10000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 72000,	'rate' => 1.10,	'constant' => 0 ),
																	array( 'income' => 136000,	'rate' => 2.04,	'constant' => 682.00 ),
																	array( 'income' => 242000,	'rate' => 2.27,	'constant' => 1987.60 ),
																	array( 'income' => 423000,	'rate' => 2.64,	'constant' => 4393.80 ),
																	array( 'income' => 423000,	'rate' => 2.90,	'constant' => 9172.20 ),
																	),
															),
												20160101 => array(
															10 => array(
																	array( 'income' => 4300,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 41000,	'rate' => 1.10,	'constant' => 0 ),
																	array( 'income' => 83000,	'rate' => 2.04,	'constant' => 403.70 ),
																	array( 'income' => 192000,	'rate' => 2.27,	'constant' => 1260.50 ),
																	array( 'income' => 413000,	'rate' => 2.64,	'constant' => 3734.80 ),
																	array( 'income' => 413000,	'rate' => 2.90,	'constant' => 9569.20 ),
																	),
															20 => array(
																	array( 'income' => 10000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 71000,	'rate' => 1.10,	'constant' => 0 ),
																	array( 'income' => 135000,	'rate' => 2.04,	'constant' => 671.00 ),
																	array( 'income' => 240000,	'rate' => 2.27,	'constant' => 1976.60 ),
																	array( 'income' => 420000,	'rate' => 2.64,	'constant' => 4360.10 ),
																	array( 'income' => 420000,	'rate' => 2.90,	'constant' => 9112.10 ),
																	),
															),
												20150601 => array(
															10 => array(
																	array( 'income' => 4300,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 41000,	'rate' => 1.10,	'constant' => 0 ),
																	array( 'income' => 83000,	'rate' => 2.04,	'constant' => 403.70 ),
																	array( 'income' => 191000,	'rate' => 2.27,	'constant' => 1260.50 ),
																	array( 'income' => 411000,	'rate' => 2.64,	'constant' => 3712.10 ),
																	array( 'income' => 411000,	'rate' => 2.90,	'constant' => 9520.10 ),
																	),
															20 => array(
																	array( 'income' => 10000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 71000,	'rate' => 1.10,	'constant' => 0 ),
																	array( 'income' => 134000,	'rate' => 2.04,	'constant' => 671.00 ),
																	array( 'income' => 239000,	'rate' => 2.27,	'constant' => 1956.20 ),
																	array( 'income' => 418000,	'rate' => 2.64,	'constant' => 4339.70 ),
																	array( 'income' => 418000,	'rate' => 2.90,	'constant' => 9065.30 ),
																	),
															),
												20150101 => array(
															10 => array(
																	array( 'income' => 4300,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 41000,	'rate' => 1.22,	'constant' => 0 ),
																	array( 'income' => 83000,	'rate' => 2.27,	'constant' => 447.74 ),
																	array( 'income' => 191000,	'rate' => 2.52,	'constant' => 1401.14 ),
																	array( 'income' => 411000,	'rate' => 2.93,	'constant' => 4122.74 ),
																	array( 'income' => 411000,	'rate' => 3.22,	'constant' => 10568.00 ),
																	),
															20 => array(
																	array( 'income' => 10000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 71000,	'rate' => 1.22,	'constant' => 0 ),
																	array( 'income' => 134000,	'rate' => 2.27,	'constant' => 744.20 ),
																	array( 'income' => 239000,	'rate' => 2.52,	'constant' => 2174.30 ),
																	array( 'income' => 418000,	'rate' => 2.93,	'constant' => 4820.30 ),
																	array( 'income' => 418000,	'rate' => 3.22,	'constant' => 10065.00 ),
																	),
															),
												20140101 => array(
															10 => array(
																	array( 'income' => 4200,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 1.22,	'constant' => 0 ),
																	array( 'income' => 82000,	'rate' => 2.27,	'constant' => 436.76 ),
																	array( 'income' => 188000,	'rate' => 2.52,	'constant' => 1390.16 ),
																	array( 'income' => 405000,	'rate' => 2.93,	'constant' => 4061.36 ),
																	array( 'income' => 405000,	'rate' => 3.22,	'constant' => 10416.46 ),
																	),
															20 => array(
																	array( 'income' => 10000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 70000,	'rate' => 1.22,	'constant' => 0 ),
																	array( 'income' => 132000,	'rate' => 2.27,	'constant' => 732.00 ),
																	array( 'income' => 235000,	'rate' => 2.52,	'constant' => 2139.40 ),
																	array( 'income' => 412000,	'rate' => 2.93,	'constant' => 4735.00 ),
																	array( 'income' => 412000,	'rate' => 3.22,	'constant' => 9921.10 ),
																	),
															),
												20130701 => array(
															10 => array(
																	array( 'income' => 4100,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 39000,	'rate' => 1.22,	'constant' => 0 ),
																	array( 'income' => 81000,	'rate' => 2.27,	'constant' => 425.78 ),
																	array( 'income' => 185000,	'rate' => 2.52,	'constant' => 1379.18 ),
																	array( 'income' => 400000,	'rate' => 2.93,	'constant' => 3999.98 ),
																	array( 'income' => 400000,	'rate' => 3.22,	'constant' => 10299.48 ),
																	),
															20 => array(
																	array( 'income' => 10000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 69000,	'rate' => 1.22,	'constant' => 0 ),
																	array( 'income' => 130000,	'rate' => 2.27,	'constant' => 719.80 ),
																	array( 'income' => 231000,	'rate' => 2.52,	'constant' => 2104.50 ),
																	array( 'income' => 405000,	'rate' => 2.93,	'constant' => 4649.70 ),
																	array( 'income' => 405000,	'rate' => 3.22,	'constant' => 9747.90 ),
																	),
															),
												20130101 => array(
															10 => array(
																	array( 'income' => 4100,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 39000,	'rate' => 1.51,	'constant' => 0 ),
																	array( 'income' => 81000,	'rate' => 2.82,	'constant' => 526.99 ),
																	array( 'income' => 185000,	'rate' => 3.13,	'constant' => 1711.39 ),
																	array( 'income' => 400000,	'rate' => 3.63,	'constant' => 4966.59 ),
																	array( 'income' => 400000,	'rate' => 3.99,	'constant' => 12771.09 ),
																	),
															20 => array(
																	array( 'income' => 10000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 69000,	'rate' => 1.51,	'constant' => 0 ),
																	array( 'income' => 130000,	'rate' => 2.82,	'constant' => 890.90 ),
																	array( 'income' => 231000,	'rate' => 3.13,	'constant' => 2611.10 ),
																	array( 'income' => 405000,	'rate' => 3.63,	'constant' => 5772.40 ),
																	array( 'income' => 405000,	'rate' => 3.99,	'constant' => 12088.60 ),
																	),
															),
												20120101 => array(
															10 => array(
																	array( 'income' => 4000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 38000,	'rate' => 1.51,	'constant' => 0 ),
																	array( 'income' => 79000,	'rate' => 2.82,	'constant' => 513.40 ),
																	array( 'income' => 180000,	'rate' => 3.13,	'constant' => 1669.60 ),
																	array( 'income' => 390000,	'rate' => 3.63,	'constant' => 4830.90 ),
																	array( 'income' => 390000,	'rate' => 3.99,	'constant' => 12453.90 ),
																	),
															20 => array(
																	array( 'income' => 9600,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 67000,	'rate' => 1.51,	'constant' => 0 ),
																	array( 'income' => 127000,	'rate' => 2.82,	'constant' => 866.74 ),
																	array( 'income' => 225000,	'rate' => 3.13,	'constant' => 2558.74 ),
																	array( 'income' => 395000,	'rate' => 3.63,	'constant' => 5626.14 ),
																	array( 'income' => 395000,	'rate' => 3.99,	'constant' => 11797.14 ),
																	),
															),
												20110101 => array(
															10 => array(
																	array( 'income' => 3900,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 37000,	'rate' => 1.84,	'constant' => 0 ),
																	array( 'income' => 77000,	'rate' => 3.44,	'constant' => 609.04 ),
																	array( 'income' => 176000,	'rate' => 3.81,	'constant' => 1985.04 ),
																	array( 'income' => 380000,	'rate' => 4.42,	'constant' => 5756.94 ),
																	array( 'income' => 380000,	'rate' => 4.86,	'constant' => 14773.74 ),
																	),
															20 => array(
																	array( 'income' => 9400,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 65000,	'rate' => 1.84,	'constant' => 0 ),
																	array( 'income' => 124000,	'rate' => 3.44,	'constant' => 1023.04 ),
																	array( 'income' => 220000,	'rate' => 3.81,	'constant' => 3052.64 ),
																	array( 'income' => 386000,	'rate' => 4.42,	'constant' => 6710.24 ),
																	array( 'income' => 386000,	'rate' => 4.86,	'constant' => 14047.44 ),
																	),
															),
												20100101 => array(
															10 => array(
																	array( 'income' => 3800,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 36000,	'rate' => 1.84,	'constant' => 0 ),
																	array( 'income' => 76000,	'rate' => 3.44,	'constant' => 592.48 ),
																	array( 'income' => 173000,	'rate' => 3.81,	'constant' => 1968.48 ),
																	array( 'income' => 376000,	'rate' => 4.42,	'constant' => 5664.18 ),
																	array( 'income' => 376000,	'rate' => 4.86,	'constant' => 14636.78 ),
																	),
															20 => array(
																	array( 'income' => 9300,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 64000,	'rate' => 1.84,	'constant' => 0 ),
																	array( 'income' => 122000,	'rate' => 3.44,	'constant' => 1006.48 ),
																	array( 'income' => 217000,	'rate' => 3.81,	'constant' => 3001.68 ),
																	array( 'income' => 381000,	'rate' => 4.42,	'constant' => 6621.18 ),
																	array( 'income' => 381000,	'rate' => 4.86,	'constant' => 13869.98 ),
																	),
															),
												20090101 => array(
															10 => array(
																	array( 'income' => 3800,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 36000,	'rate' => 2.10,	'constant' => 0 ),
																	array( 'income' => 76000,	'rate' => 3.92,	'constant' => 676.20 ),
																	array( 'income' => 173000,	'rate' => 4.34,	'constant' => 2244.20 ),
																	array( 'income' => 375000,	'rate' => 5.04,	'constant' => 6454.00 ),
																	array( 'income' => 375000,	'rate' => 5.54,	'constant' => 16634.80 ),
																	),
															20 => array(
																	array( 'income' => 9300,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 64000,	'rate' => 2.10,	'constant' => 0 ),
																	array( 'income' => 122000,	'rate' => 3.92,	'constant' => 1148.70 ),
																	array( 'income' => 217000,	'rate' => 4.34,	'constant' => 3422.30 ),
																	array( 'income' => 380000,	'rate' => 5.04,	'constant' => 7545.30 ),
																	array( 'income' => 380000,	'rate' => 5.54,	'constant' => 15760.50 ),
																	),
															),
												20080101 => array(
															10 => array(
																	array( 'income' => 3700,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 34600,	'rate' => 2.10,	'constant' => 0 ),
																	array( 'income' => 72800,	'rate' => 3.92,	'constant' => 648.90 ),
																	array( 'income' => 166300,	'rate' => 4.34,	'constant' => 2146.34 ),
																	array( 'income' => 359200,	'rate' => 5.04,	'constant' => 6204.24 ),
																	array( 'income' => 359200,	'rate' => 5.54,	'constant' => 15926.40 ),
																	),
															20 => array(
																	array( 'income' => 9000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 61600,	'rate' => 2.10,	'constant' => 0 ),
																	array( 'income' => 116900,	'rate' => 3.92,	'constant' => 1104.60 ),
																	array( 'income' => 208200,	'rate' => 4.34,	'constant' => 3272.36 ),
																	array( 'income' => 364700,	'rate' => 5.04,	'constant' => 7234.78 ),
																	array( 'income' => 364700,	'rate' => 5.54,	'constant' => 15122.38 ),
																	),
															),
												20070101 => array(
															10 => array(
																	array( 'income' => 3600,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 33800,	'rate' => 2.10,	'constant' => 0 ),
																	array( 'income' => 71200,	'rate' => 3.92,	'constant' => 634.20 ),
																	array( 'income' => 162600,	'rate' => 4.34,	'constant' => 2100.28 ),
																	array( 'income' => 351200,	'rate' => 5.04,	'constant' => 6067.04 ),
																	array( 'income' => 351200,	'rate' => 5.54,	'constant' => 15572.48 ),
																	),
															20 => array(
																	array( 'income' => 8800,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 60200,	'rate' => 2.10,	'constant' => 0 ),
																	array( 'income' => 114300,	'rate' => 3.92,	'constant' => 1079.40 ),
																	array( 'income' => 203600,	'rate' => 4.34,	'constant' => 3200.12 ),
																	array( 'income' => 356600,	'rate' => 5.04,	'constant' => 7075.74 ),
																	array( 'income' => 356600,	'rate' => 5.54,	'constant' => 14786.94 ),
																	),
															),
												20060101 => array(
															10 => array(
																	array( 'income' => 3500,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 32500,	'rate' => 2.10,	'constant' => 0 ),
																	array( 'income' => 68500,	'rate' => 3.92,	'constant' => 609 ),
																	array( 'income' => 156000,	'rate' => 4.34,	'constant' => 2020.20 ),
																	array( 'income' => 338100,	'rate' => 5.04,	'constant' => 5839.40 ),
																	array( 'income' => 338100,	'rate' => 5.54,	'constant' => 14987 ),
																	),
															20 => array(
																	array( 'income' => 8500,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 57900,	'rate' => 2.10,	'constant' => 0 ),
																	array( 'income' => 110000,	'rate' => 3.92,	'constant' => 1037.40 ),
																	array( 'income' => 196000,	'rate' => 4.34,	'constant' => 3079.72 ),
																	array( 'income' => 343200,	'rate' => 5.04,	'constant' => 6812.12 ),
																	array( 'income' => 343200,	'rate' => 5.54,	'constant' => 14231 ),
																),
															),
												);

	var $state_options = array(
								//01-Jan-17 - No Change.
								20160101 => array( //01-Jan-16
													'allowance' => 4050
													),
								20140101 => array( //01-Jan-14
													'allowance' => 3950
													),
								20130101 => array( //01-Jan-13
													'allowance' => 3900
													),
								20120101 => array( //01-Jan-12
													'allowance' => 3800
													),
								20110101 => array( //01-Jan-11
													'allowance' => 3700
													),
								//01-Jan-10: No Change.
								20090101 => array( //01-Jan-09
													'allowance' => 3650
													),
								20080101 => array(
													'allowance' => 3500
													),
								20070101 => array(
													'allowance' => 3400
													),
								20060101 => array(
													'allowance' => 3300
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
