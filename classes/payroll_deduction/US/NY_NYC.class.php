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
class PayrollDeduction_US_NY_NYC extends PayrollDeduction_US_NY {
/*
 														10 => 'Single',
														20 => 'Married',

Used to be:
														10 => 'Single',
														20 => 'Married - Spouse Works',
														30 => 'Married - Spouse does not Work',
														40 => 'Head of Household',
*/

	var $district_income_tax_rate_options = array(
												20160101 => array(
															10 => array(
																	array( 'income' => 8000,	'rate' => 1.9,	'constant' => 0 ),
																	array( 'income' => 8700,	'rate' => 2.65,	'constant' => 152 ),
																	array( 'income' => 15000,	'rate' => 3.1,	'constant' => 171 ),
																	array( 'income' => 25000,	'rate' => 3.7,	'constant' => 366 ),
																	array( 'income' => 60000,	'rate' => 3.9,	'constant' => 736 ),
																	array( 'income' => 500000,	'rate' => 4.0,	'constant' => 2101 ),
																	array( 'income' => 500000,	'rate' => 4.25,	'constant' => 20828.46 ), //Just the constant changed by the looks of it.
																	),
															20 => array(
																	array( 'income' => 8000,	'rate' => 1.9,	'constant' => 0 ),
																	array( 'income' => 8700,	'rate' => 2.65,	'constant' => 152 ),
																	array( 'income' => 15000,	'rate' => 3.1,	'constant' => 171 ),
																	array( 'income' => 25000,	'rate' => 3.7,	'constant' => 366 ),
																	array( 'income' => 60000,	'rate' => 3.9,	'constant' => 736 ),
																	array( 'income' => 500000,	'rate' => 4.0,	'constant' => 2101 ),
																	array( 'income' => 500000,	'rate' => 4.25,	'constant' => 20828.46 ),
																	),
															),
												20150601 => array(
															10 => array(
																	array( 'income' => 8000,	'rate' => 1.9,	'constant' => 0 ),
																	array( 'income' => 8700,	'rate' => 2.65,	'constant' => 152 ),
																	array( 'income' => 15000,	'rate' => 3.1,	'constant' => 171 ),
																	array( 'income' => 25000,	'rate' => 3.7,	'constant' => 366 ),
																	array( 'income' => 60000,	'rate' => 3.9,	'constant' => 736 ),
																	array( 'income' => 500000,	'rate' => 4.0,	'constant' => 2101 ),
																	array( 'income' => 500000,	'rate' => 4.25,	'constant' => 20834.16 ),
																	),
															20 => array(
																	array( 'income' => 8000,	'rate' => 1.9,	'constant' => 0 ),
																	array( 'income' => 8700,	'rate' => 2.65,	'constant' => 152 ),
																	array( 'income' => 15000,	'rate' => 3.1,	'constant' => 171 ),
																	array( 'income' => 25000,	'rate' => 3.7,	'constant' => 366 ),
																	array( 'income' => 60000,	'rate' => 3.9,	'constant' => 736 ),
																	array( 'income' => 500000,	'rate' => 4.0,	'constant' => 2101 ),
																	array( 'income' => 500000,	'rate' => 4.25,	'constant' => 20834.16 ),
																	),
															),
												20110101 => array(
															10 => array(
																	array( 'income' => 8000,	'rate' => 1.9,	'constant' => 0 ),
																	array( 'income' => 8700,	'rate' => 2.65,	'constant' => 152 ),
																	array( 'income' => 15000,	'rate' => 3.1,	'constant' => 171 ),
																	array( 'income' => 25000,	'rate' => 3.7,	'constant' => 366 ),
																	array( 'income' => 60000,	'rate' => 3.9,	'constant' => 736 ),
																	array( 'income' => 500000,	'rate' => 4.0,	'constant' => 2101 ),
																	array( 'income' => 500000,	'rate' => 4.25,	'constant' => 19701 ),
																	),
															20 => array(
																	array( 'income' => 8000,	'rate' => 1.9,	'constant' => 0 ),
																	array( 'income' => 8700,	'rate' => 2.65,	'constant' => 152 ),
																	array( 'income' => 15000,	'rate' => 3.1,	'constant' => 171 ),
																	array( 'income' => 25000,	'rate' => 3.7,	'constant' => 366 ),
																	array( 'income' => 60000,	'rate' => 3.9,	'constant' => 736 ),
																	array( 'income' => 500000,	'rate' => 4.0,	'constant' => 2101 ),
																	array( 'income' => 500000,	'rate' => 4.25,	'constant' => 19701 ),
																	),
															),
												20060101 => array(
															10 => array(
																	array( 'income' => 8000,	'rate' => 1.9,	'constant' => 0 ),
																	array( 'income' => 8700,	'rate' => 2.65,	'constant' => 152 ),
																	array( 'income' => 15000,	'rate' => 3.10,	'constant' => 172 ),
																	array( 'income' => 25000,	'rate' => 3.70,	'constant' => 366 ),
																	array( 'income' => 60000,	'rate' => 3.90,	'constant' => 736 ),
																	array( 'income' => 60000,	'rate' => 4.00,	'constant' => 2101 ),
																	),
															20 => array(
																	array( 'income' => 8000,	'rate' => 1.9,	'constant' => 0 ),
																	array( 'income' => 8700,	'rate' => 2.65,	'constant' => 152 ),
																	array( 'income' => 15000,	'rate' => 3.10,	'constant' => 172 ),
																	array( 'income' => 25000,	'rate' => 3.70,	'constant' => 366 ),
																	array( 'income' => 60000,	'rate' => 3.90,	'constant' => 736 ),
																	array( 'income' => 60000,	'rate' => 4.00,	'constant' => 2101 ),
																),
															),
												);

	var $district_options = array(
								20060101 => array(
													'standard_deduction' => array(
																				'10' => 5000.00,
																				'20' => 5500.00,
																				'30' => 5500.00,
																				'40' => 5000.00,
																				),
													'allowance' => array(
																				'10' => 1000,
																				'20' => 1000,
																				'30' => 1000,
																				'40' => 1000,
																				),
													)
								);

	function getDistrictAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$federal_tax = $this->getFederalTaxPayable();
		$district_deductions = $this->getDistrictStandardDeduction();
		$district_allowance = $this->getDistrictAllowanceAmount();

		$income = bcsub( bcsub( $annual_income, $district_deductions), $district_allowance );

		Debug::text('District Annual Taxable Income: '. $income, __FILE__, __LINE__, __METHOD__, 10);

		return $income;
	}

	function getDistrictStandardDeduction() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->district_options);
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		if ( isset($retarr['standard_deduction'][$this->getDistrictFilingStatus()]) ) {
			$deduction = $retarr['standard_deduction'][$this->getDistrictFilingStatus()];
		} else {
			$deduction = $retarr['standard_deduction'][10];
		}

		Debug::text('Standard Deduction: '. $deduction, __FILE__, __LINE__, __METHOD__, 10);

		return $deduction;
	}

	function getDistrictAllowanceAmount() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->district_options);
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		if ( isset($retarr['allowance'][$this->getDistrictFilingStatus()]) ) {
			$allowance = $retarr['allowance'][$this->getDistrictFilingStatus()];
		} else {
			$allowance = $retarr['allowance'][10];
		}

		if ( $this->getDistrictAllowance() == 0 ) {
			$retval = 0;
		} else {
			$retval = bcmul( $this->getDistrictAllowance(), $allowance );
		}

		Debug::text('District Allowance Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);


		return $retval;
	}

	function getDistrictTaxPayable() {
		$annual_income = $this->getDistrictAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getDistrictRate($annual_income);
			$district_constant = $this->getData()->getDistrictConstant($annual_income);
			$district_rate_income = $this->getData()->getDistrictRatePreviousIncome($annual_income);

			$retval = bcadd( bcmul( bcsub( $annual_income, $district_rate_income ), $rate ), $district_constant );
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text('District Annual Tax Payable: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}
}
?>
