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
class PayrollDeduction_US_DC extends PayrollDeduction_US {
/*
														10 => TTi18n::gettext('Single'),
														20 => TTi18n::gettext('Married (Filing Jointly)'),
														30 => TTi18n::gettext('Married (Filing Separately)'),
														40 => TTi18n::gettext('Head of Household'),
*/
	
	var $state_income_tax_rate_options = array(
												20160101 => array(
															10 => array(
																	array( 'income' => 10000,	'rate' => 4.0,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 6.0,	'constant' => 400 ),
																	array( 'income' => 60000,	'rate' => 6.5,	'constant' => 2200 ),
																	array( 'income' => 350000,	'rate' => 8.5,	'constant' => 3500 ),
																	array( 'income' => 1000000,	'rate' => 8.75,	'constant' => 28150 ),
																	array( 'income' => 1000000,	'rate' => 8.95,	'constant' => 85025 ),
																	),
															20 => array(
																	array( 'income' => 10000,	'rate' => 4.0,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 6.0,	'constant' => 400 ),
																	array( 'income' => 60000,	'rate' => 6.5,	'constant' => 2200 ),
																	array( 'income' => 350000,	'rate' => 8.5,	'constant' => 3500 ),
																	array( 'income' => 1000000,	'rate' => 8.75,	'constant' => 28150 ),
																	array( 'income' => 1000000,	'rate' => 8.95,	'constant' => 85025 ),
																	),
															30 => array(
																	array( 'income' => 10000,	'rate' => 4.0,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 6.0,	'constant' => 400 ),
																	array( 'income' => 60000,	'rate' => 6.5,	'constant' => 2200 ),
																	array( 'income' => 350000,	'rate' => 8.5,	'constant' => 3500 ),
																	array( 'income' => 1000000,	'rate' => 8.75,	'constant' => 28150 ),
																	array( 'income' => 1000000,	'rate' => 8.95,	'constant' => 85025 ),
																	),
															40 => array(
																	array( 'income' => 10000,	'rate' => 4.0,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 6.0,	'constant' => 400 ),
																	array( 'income' => 60000,	'rate' => 6.5,	'constant' => 2200 ),
																	array( 'income' => 350000,	'rate' => 8.5,	'constant' => 3500 ),
																	array( 'income' => 1000000,	'rate' => 8.75,	'constant' => 28150 ),
																	array( 'income' => 1000000,	'rate' => 8.95,	'constant' => 85025 ),
																	),
															),
												20150101 => array(
															10 => array(
																	array( 'income' => 10000,	'rate' => 4.0,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 6.0,	'constant' => 400 ),
																	array( 'income' => 60000,	'rate' => 7.0,	'constant' => 2200 ),
																	array( 'income' => 350000,	'rate' => 8.5,	'constant' => 3600 ),
																	array( 'income' => 350000,	'rate' => 8.95,	'constant' => 28250 ),
																	),
															20 => array(
																	array( 'income' => 10000,	'rate' => 4.0,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 6.0,	'constant' => 400 ),
																	array( 'income' => 60000,	'rate' => 7.0,	'constant' => 2200 ),
																	array( 'income' => 350000,	'rate' => 8.5,	'constant' => 3600 ),
																	array( 'income' => 350000,	'rate' => 8.95,	'constant' => 28250 ),
																	),
															30 => array(
																	array( 'income' => 10000,	'rate' => 4.0,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 6.0,	'constant' => 400 ),
																	array( 'income' => 60000,	'rate' => 7.0,	'constant' => 2200 ),
																	array( 'income' => 350000,	'rate' => 8.5,	'constant' => 3600 ),
																	array( 'income' => 350000,	'rate' => 8.95,	'constant' => 28250 ),
																	),
															40 => array(
																	array( 'income' => 10000,	'rate' => 4.0,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 6.0,	'constant' => 400 ),
																	array( 'income' => 60000,	'rate' => 7.0,	'constant' => 2200 ),
																	array( 'income' => 350000,	'rate' => 8.5,	'constant' => 3600 ),
																	array( 'income' => 350000,	'rate' => 8.95,	'constant' => 28250 ),
																	),
															),
												20120101 => array(
															10 => array(
																	array( 'income' => 10000,	'rate' => 4.0,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 6.0,	'constant' => 400 ),
																	array( 'income' => 350000,	'rate' => 8.5,	'constant' => 2200 ),
																	array( 'income' => 350000,	'rate' => 8.95,	'constant' => 28550 ),
																	),
															20 => array(
																	array( 'income' => 10000,	'rate' => 4.0,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 6.0,	'constant' => 400 ),
																	array( 'income' => 350000,	'rate' => 8.5,	'constant' => 2200 ),
																	array( 'income' => 350000,	'rate' => 8.95,	'constant' => 28550 ),
																	),
															30 => array(
																	array( 'income' => 10000,	'rate' => 4.0,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 6.0,	'constant' => 400 ),
																	array( 'income' => 350000,	'rate' => 8.5,	'constant' => 2200 ),
																	array( 'income' => 350000,	'rate' => 8.95,	'constant' => 28550 ),
																	),
															40 => array(
																	array( 'income' => 10000,	'rate' => 4.0,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 6.0,	'constant' => 400 ),
																	array( 'income' => 350000,	'rate' => 8.5,	'constant' => 2200 ),
																	array( 'income' => 350000,	'rate' => 8.95,	'constant' => 28550 ),
																	),
															),
												20100101 => array(
															10 => array(
																	array( 'income' => 4000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 10000,	'rate' => 4.0,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 6.0,	'constant' => 240 ),
																	array( 'income' => 40000,	'rate' => 8.5,	'constant' => 2040 ),
																	),
															20 => array(
																	array( 'income' => 4000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 10000,	'rate' => 4.0,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 6.0,	'constant' => 240 ),
																	array( 'income' => 40000,	'rate' => 8.5,	'constant' => 2040 ),
																	),
															30 => array(
																	array( 'income' => 2000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 10000,	'rate' => 4.0,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 6.0,	'constant' => 320 ),
																	array( 'income' => 40000,	'rate' => 8.5,	'constant' => 2120 ),
																	),
															40 => array(
																	array( 'income' => 4000,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 10000,	'rate' => 4.0,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 6.0,	'constant' => 240 ),
																	array( 'income' => 40000,	'rate' => 8.5,	'constant' => 2040 ),
																	),
															),
												20090101 => array(
															10 => array(
																	array( 'income' => 4200,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 10000,	'rate' => 4.0,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 6.0,	'constant' => 232 ),
																	array( 'income' => 40000,	'rate' => 8.5,	'constant' => 2032 ),
																	),
															20 => array(
																	array( 'income' => 4200,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 10000,	'rate' => 4.0,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 6.0,	'constant' => 232 ),
																	array( 'income' => 40000,	'rate' => 8.5,	'constant' => 2032 ),
																	),
															30 => array(
																	array( 'income' => 2100,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 10000,	'rate' => 4.0,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 6.0,	'constant' => 316 ),
																	array( 'income' => 40000,	'rate' => 8.5,	'constant' => 2116 ),
																	),
															40 => array(
																	array( 'income' => 4200,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 10000,	'rate' => 4.0,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 6.0,	'constant' => 232 ),
																	array( 'income' => 40000,	'rate' => 8.5,	'constant' => 2032 ),
																	),
															),
												20060101 => array(
															10 => array(
																	array( 'income' => 2500,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 10000,	'rate' => 4.5,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 7.0,	'constant' => 337.50 ),
																	array( 'income' => 40000,	'rate' => 8.7,	'constant' => 2437.50 ),
																	),
															20 => array(
																	array( 'income' => 2500,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 10000,	'rate' => 4.5,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 7.0,	'constant' => 337.50 ),
																	array( 'income' => 40000,	'rate' => 8.7,	'constant' => 2437.50 ),
																	),
															30 => array(
																	array( 'income' => 1250,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 10000,	'rate' => 4.5,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 7.0,	'constant' => 393.75 ),
																	array( 'income' => 40000,	'rate' => 8.7,	'constant' => 2493.75 ),
																	),
															40 => array(
																	array( 'income' => 2500,	'rate' => 0,	'constant' => 0 ),
																	array( 'income' => 10000,	'rate' => 4.5,	'constant' => 0 ),
																	array( 'income' => 40000,	'rate' => 7.0,	'constant' => 337.50 ),
																	array( 'income' => 40000,	'rate' => 8.7,	'constant' => 2437.50 ),
																),
															),
												);

	var $state_options = array(
								20150101 => array( //01-Jan-2015
													'allowance' => 1775
													),		
								//01-Jan-2014 - No Changes.
								//01-Jan-2013 - No Changes.
								//01-Jan-2012 - No Changes.
								//01-Jan-2011 - No Changes.
								20100101 => array( //01-Jan-2010
													'allowance' => 1675
													),
								20090101 => array( //01-Jan-09
													'allowance' => 1750
													),
								20060101 => array(
													'allowance' => 1500
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
