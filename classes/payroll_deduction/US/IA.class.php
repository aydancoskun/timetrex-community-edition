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
class PayrollDeduction_US_IA extends PayrollDeduction_US {

	var $state_income_tax_rate_options = array(
			20200101 => array(
					0 => array(
							array('income' => 1480, 'rate' => 0.33, 'constant' => 0),
							array('income' => 2959, 'rate' => 0.67, 'constant' => 4.88),
							array('income' => 5918, 'rate' => 2.25, 'constant' => 14.79),
							array('income' => 13316, 'rate' => 4.14, 'constant' => 81.37),
							array('income' => 22193, 'rate' => 5.63, 'constant' => 387.65),
							array('income' => 29590, 'rate' => 5.96, 'constant' => 887.43),
							array('income' => 44385, 'rate' => 6.25, 'constant' => 1328.29),
							array('income' => 66578, 'rate' => 7.44, 'constant' => 2252.98),
							array('income' => 66578, 'rate' => 8.53, 'constant' => 3904.14),
					),
			),
			20190101 => array(
					0 => array(
							array('income' => 1333, 'rate' => 0.33, 'constant' => 0),
							array('income' => 2666, 'rate' => 0.67, 'constant' => 4.40),
							array('income' => 5331, 'rate' => 2.25, 'constant' => 13.33),
							array('income' => 11995, 'rate' => 4.14, 'constant' => 73.29),
							array('income' => 19992, 'rate' => 5.63, 'constant' => 349.18),
							array('income' => 26656, 'rate' => 5.96, 'constant' => 799.41),
							array('income' => 39984, 'rate' => 6.25, 'constant' => 1196.58),
							array('income' => 59976, 'rate' => 7.44, 'constant' => 2029.58),
							array('income' => 59976, 'rate' => 8.53, 'constant' => 3516.98),
					),
			),
			20060401 => array(
					0 => array(
							array('income' => 1300, 'rate' => 0.36, 'constant' => 0),
							array('income' => 2600, 'rate' => 0.72, 'constant' => 4.68),
							array('income' => 5200, 'rate' => 2.43, 'constant' => 14.04),
							array('income' => 11700, 'rate' => 4.50, 'constant' => 77.22),
							array('income' => 19500, 'rate' => 6.12, 'constant' => 369.72),
							array('income' => 26000, 'rate' => 6.48, 'constant' => 847.08),
							array('income' => 39000, 'rate' => 6.80, 'constant' => 1268.28),
							array('income' => 58500, 'rate' => 7.92, 'constant' => 2152.28),
							array('income' => 58500, 'rate' => 8.98, 'constant' => 3696.68),
					),
			),
	);

	var $state_options = array(
			20200101 => array(
					'standard_deduction' => array(1880.00, 4630.00), //First is 0 or 1 allowances. 2nd is 2 or more allowances
					'allowance'          => 40,
			),
			20190101 => array(
					'standard_deduction' => array(1690.00, 4160.00),
					'allowance'          => 40,
			),
			20060401 => array( //01-Apr-06
					'standard_deduction' => array(1650.00, 4060.00),
					'allowance'          => 40,
			),
			20060101 => array(
					'standard_deduction' => array(1500.00, 2600.00),
					'allowance'          => 40,
			),
	);

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$federal_tax = $this->getFederalTaxPayable();

		$state_deductions = $this->getStateStandardDeduction();

		$income = bcsub( bcsub( $annual_income, $federal_tax ), $state_deductions );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		$deduction = $retarr['standard_deduction'];

		if ( $this->getStateAllowance() <= 1 ) {
			$retval = $deduction[0];
		} else {
			$retval = $deduction[1];
		}

		Debug::text( 'Standard Deduction: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		$allowance = $retarr['allowance'];

		$retval = bcmul( $allowance, $this->getStateAllowance() );

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
