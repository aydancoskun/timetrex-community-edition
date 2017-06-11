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
class PayrollDeduction_US_DE extends PayrollDeduction_US {

	var $state_income_tax_rate_options = array(
			20140101 => array(
					0 => array(
							array('income' => 2000, 'rate' => 0, 'constant' => 0),
							array('income' => 5000, 'rate' => 2.20, 'constant' => 0),
							array('income' => 10000, 'rate' => 3.90, 'constant' => 66),
							array('income' => 20000, 'rate' => 4.80, 'constant' => 261),
							array('income' => 25000, 'rate' => 5.20, 'constant' => 741),
							array('income' => 60000, 'rate' => 5.55, 'constant' => 1001),
							array('income' => 60000, 'rate' => 6.60, 'constant' => 2943.50),
					),
			),
			20100101 => array(
					0 => array(
							array('income' => 2000, 'rate' => 0, 'constant' => 0),
							array('income' => 5000, 'rate' => 2.20, 'constant' => 0),
							array('income' => 10000, 'rate' => 3.90, 'constant' => 66),
							array('income' => 20000, 'rate' => 4.80, 'constant' => 261),
							array('income' => 25000, 'rate' => 5.20, 'constant' => 741),
							array('income' => 60000, 'rate' => 5.55, 'constant' => 1001),
							array('income' => 60000, 'rate' => 6.95, 'constant' => 2943.50),
					),
			),
			20060101 => array(
					0 => array(
							array('income' => 2000, 'rate' => 0, 'constant' => 0),
							array('income' => 5000, 'rate' => 2.20, 'constant' => 0),
							array('income' => 10000, 'rate' => 3.90, 'constant' => 66),
							array('income' => 20000, 'rate' => 4.80, 'constant' => 261),
							array('income' => 25000, 'rate' => 5.20, 'constant' => 741),
							array('income' => 60000, 'rate' => 5.55, 'constant' => 1001),
							array('income' => 60000, 'rate' => 5.95, 'constant' => 2943.50),
					),
			),
	);

	var $state_options = array(
			20060101 => array(
					'standard_deduction' => array(
							10 => 3250,
							20 => 6500,
							30 => 3250,
					),
					'allowance'          => 110,
			),
	);

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$standard_deduction = $this->getStateStandardDeduction();

		$income = bcsub( $annual_income, $standard_deduction );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		if ( isset( $retarr['standard_deduction'][ $this->getStateFilingStatus() ] ) ) {
			$deduction = $retarr['standard_deduction'][ $this->getStateFilingStatus() ];
		} else {
			$deduction = $retarr['standard_deduction'][10]; //Single
		}

		Debug::text( 'Standard Deduction: ' . $deduction, __FILE__, __LINE__, __METHOD__, 10 );

		return $deduction;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		$allowance_arr = $retarr['allowance'];

		$retval = bcmul( $this->getStateAllowance(), $allowance_arr );

		Debug::text( 'State Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function getStateTaxPayable() {
		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getStateRate( $annual_income );
			$prev_income = $this->getData()->getStateRatePreviousIncome( $annual_income );
			$state_constant = $this->getData()->getStateConstant( $annual_income );

			//$retval = bcadd( bcmul( bcsub( $annual_income, $state_rate_income ), $rate ), $state_constant );
			$retval = bcadd( bcmul( bcsub( $annual_income, $prev_income ), $rate ), $state_constant );
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
