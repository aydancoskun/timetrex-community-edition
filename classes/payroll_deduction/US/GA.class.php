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
class PayrollDeduction_US_GA extends PayrollDeduction_US {
	/*
		protected $state_ga_filing_status_options = array(
															10 => 'Single',
															20 => 'Married - Filing Separately',
															30 => 'Married - Joint One Income',
															40 => 'Married - Joint Two Incomes',
															50 => 'Head of Household',
										);

	*/

	var $state_income_tax_rate_options = array(
			20190101 => array(
					10 => array(
							array('income' => 750, 'rate' => 1.0, 'constant' => 0),
							array('income' => 2250, 'rate' => 2.0, 'constant' => 7.50),
							array('income' => 3750, 'rate' => 3.0, 'constant' => 37.50),
							array('income' => 5250, 'rate' => 4.0, 'constant' => 82.50),
							array('income' => 7000, 'rate' => 5.0, 'constant' => 142.50),
							array('income' => 7000, 'rate' => 5.75, 'constant' => 230),
					),
					20 => array(
							array('income' => 500, 'rate' => 1.0, 'constant' => 0),
							array('income' => 1500, 'rate' => 2.0, 'constant' => 5),
							array('income' => 2500, 'rate' => 3.0, 'constant' => 25),
							array('income' => 3500, 'rate' => 4.0, 'constant' => 55),
							array('income' => 5000, 'rate' => 5.0, 'constant' => 95),
							array('income' => 5000, 'rate' => 5.75, 'constant' => 170),
					),
					30 => array(
							array('income' => 1000, 'rate' => 1.0, 'constant' => 0),
							array('income' => 3000, 'rate' => 2.0, 'constant' => 10),
							array('income' => 5000, 'rate' => 3.0, 'constant' => 50),
							array('income' => 7000, 'rate' => 4.0, 'constant' => 110),
							array('income' => 10000, 'rate' => 5.0, 'constant' => 190),
							array('income' => 10000, 'rate' => 5.75, 'constant' => 340),
					),
					40 => array(
							array('income' => 500, 'rate' => 1.0, 'constant' => 0),
							array('income' => 1500, 'rate' => 2.0, 'constant' => 5),
							array('income' => 2500, 'rate' => 3.0, 'constant' => 25),
							array('income' => 3500, 'rate' => 4.0, 'constant' => 55),
							array('income' => 5000, 'rate' => 5.0, 'constant' => 95),
							array('income' => 5000, 'rate' => 5.75, 'constant' => 170),
					),
					50 => array(
							array('income' => 1000, 'rate' => 1.0, 'constant' => 0),
							array('income' => 3000, 'rate' => 2.0, 'constant' => 10),
							array('income' => 5000, 'rate' => 3.0, 'constant' => 50),
							array('income' => 7000, 'rate' => 4.0, 'constant' => 110),
							array('income' => 10000, 'rate' => 5.0, 'constant' => 190),
							array('income' => 10000, 'rate' => 5.75, 'constant' => 340),
					),
			),
			20060101 => array(
					10 => array(
							array('income' => 750, 'rate' => 1.0, 'constant' => 0),
							array('income' => 2250, 'rate' => 2.0, 'constant' => 7.50),
							array('income' => 3750, 'rate' => 3.0, 'constant' => 37.50),
							array('income' => 5250, 'rate' => 4.0, 'constant' => 82.50),
							array('income' => 7000, 'rate' => 5.0, 'constant' => 142.50),
							array('income' => 7000, 'rate' => 6.0, 'constant' => 230),
					),
					20 => array(
							array('income' => 500, 'rate' => 1.0, 'constant' => 0),
							array('income' => 1500, 'rate' => 2.0, 'constant' => 5),
							array('income' => 2500, 'rate' => 3.0, 'constant' => 25),
							array('income' => 3500, 'rate' => 4.0, 'constant' => 55),
							array('income' => 5000, 'rate' => 5.0, 'constant' => 95),
							array('income' => 5000, 'rate' => 6.0, 'constant' => 170),
					),
					30 => array(
							array('income' => 1000, 'rate' => 1.0, 'constant' => 0),
							array('income' => 3000, 'rate' => 2.0, 'constant' => 10),
							array('income' => 5000, 'rate' => 3.0, 'constant' => 50),
							array('income' => 7000, 'rate' => 4.0, 'constant' => 110),
							array('income' => 10000, 'rate' => 5.0, 'constant' => 190),
							array('income' => 10000, 'rate' => 6.0, 'constant' => 340),
					),
					40 => array(
							array('income' => 500, 'rate' => 1.0, 'constant' => 0),
							array('income' => 1500, 'rate' => 2.0, 'constant' => 5),
							array('income' => 2500, 'rate' => 3.0, 'constant' => 25),
							array('income' => 3500, 'rate' => 4.0, 'constant' => 55),
							array('income' => 5000, 'rate' => 5.0, 'constant' => 95),
							array('income' => 5000, 'rate' => 6.0, 'constant' => 170),
					),
					50 => array(
							array('income' => 1000, 'rate' => 1.0, 'constant' => 0),
							array('income' => 3000, 'rate' => 2.0, 'constant' => 10),
							array('income' => 5000, 'rate' => 3.0, 'constant' => 50),
							array('income' => 7000, 'rate' => 4.0, 'constant' => 110),
							array('income' => 10000, 'rate' => 5.0, 'constant' => 190),
							array('income' => 10000, 'rate' => 6.0, 'constant' => 340),
					),
			),
	);

	var $state_options = array(
//			10 => 'Single',
//			20 => 'Married - Filing Separately',
//			30 => 'Married - Joint One Income',
//			40 => 'Married - Joint Two Incomes',
//			50 => 'Head of Household',

			20190101 => array(
					'standard_deduction'  => array(
							'10' => 4600.00,
							'20' => 3000.00,
							'30' => 6000.00,
							'40' => 3000.00,
							'50' => 4600.00,
					),
					'employee_allowance'  => array( //Personal Allowance
													'10' => 2700.00,
													'20' => 3700.00,
													'30' => 7400.00,
													'40' => 3700.00,
													'50' => 2700.00,
					),
					'dependant_allowance' => 3000,
			),

			20060101 => array(
					'standard_deduction'  => array(
							'10' => 2300.00,
							'20' => 1500.00,
							'30' => 3000.00,
							'40' => 1500.00,
							'50' => 2300.00,
					),
					'employee_allowance'  => array( //Personal Allowance
													'10' => 2700.00,
													'20' => 3700.00,
													'30' => 7400.00,
													'40' => 3700.00,
													'50' => 2700.00,
					),
					'dependant_allowance' => 3000,
			),
	);

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$state_deductions = $this->getStateStandardDeduction();
		$state_employee_allowance = $this->getStateEmployeeAllowanceAmount();
		$state_dependant_allowance = $this->getStateDependantAllowanceAmount();

		$income = bcsub( bcsub( bcsub( $annual_income, $state_deductions ), $state_employee_allowance ), $state_dependant_allowance );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		$deduction = $retarr['standard_deduction'][ $this->getStateFilingStatus() ];

		Debug::text( 'Standard Deduction: ' . $deduction, __FILE__, __LINE__, __METHOD__, 10 );

		return $deduction;
	}

	function getStateEmployeeAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		$allowance_arr = $retarr['employee_allowance'][ $this->getStateFilingStatus() ];

		$retval = bcmul( $this->getUserValue2(), $allowance_arr );

		Debug::text( 'State Employee Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function getStateDependantAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		$allowance_arr = $retarr['dependant_allowance'];

		$retval = bcmul( $this->getUserValue3(), $allowance_arr );

		Debug::text( 'State Dependant Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

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
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

}

?>
