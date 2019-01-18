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
class PayrollDeduction_US_SC extends PayrollDeduction_US {

	var $state_income_tax_rate_options = array(
			20180101 => array(
					0 => array( //Uses Subtraction method constants.
								array('income' => 2290, 'rate' => 1.4, 'constant' => 0),
								array('income' => 4580, 'rate' => 3, 'constant' => 36.64),
								array('income' => 6870, 'rate' => 4, 'constant' => 82.44),
								array('income' => 9160, 'rate' => 5, 'constant' => 151.14),
								array('income' => 11450, 'rate' => 6, 'constant' => 242.74),
								array('income' => 11450, 'rate' => 7, 'constant' => 357.24),
					),
			),
			20170101 => array(
					0 => array( //Uses Subtraction method constants.
								array('income' => 2140, 'rate' => 1.7, 'constant' => 0),
								array('income' => 4280, 'rate' => 3, 'constant' => 27.82),
								array('income' => 6420, 'rate' => 4, 'constant' => 70.62),
								array('income' => 8560, 'rate' => 5, 'constant' => 134.82),
								array('income' => 10700, 'rate' => 6, 'constant' => 220.42),
								array('income' => 10700, 'rate' => 7, 'constant' => 327.42),
					),
			),
			20060101 => array(
					0 => array( //Uses Subtraction method constants.
								array('income' => 2000, 'rate' => 2, 'constant' => 0),
								array('income' => 4000, 'rate' => 3, 'constant' => 20),
								array('income' => 6000, 'rate' => 4, 'constant' => 60),
								array('income' => 8000, 'rate' => 5, 'constant' => 120),
								array('income' => 10000, 'rate' => 6, 'constant' => 200),
								array('income' => 10000, 'rate' => 7, 'constant' => 300),
					),
			),
	);

	var $state_options = array(
			20180101 => array(
					'standard_deduction_rate'    => 10,
					'standard_deduction_maximum' => 3150,
					'allowance'                  => 2440,
			),
			20170101 => array(
					'standard_deduction_rate'    => 10,
					'standard_deduction_maximum' => 2860,
					'allowance'                  => 2370,
			),
			20060101 => array(
					'standard_deduction_rate'    => 10,
					'standard_deduction_maximum' => 2600,
					'allowance'                  => 2300,
			),
	);

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$standard_deductions = $this->getStateStandardDeduction();
		$allowance = $this->getStateAllowanceAmount();

		$income = bcsub( bcsub( $annual_income, $standard_deductions ), $allowance );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}

	function getStateFederalTaxMaximum() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == FALSE ) {
			return FALSE;
		}

		$maximum = $retarr['federal_tax_maximum'][ $this->getStateFilingStatus() ];

		Debug::text( 'Maximum State allowed Federal Tax: ' . $maximum, __FILE__, __LINE__, __METHOD__, 10 );

		return $maximum;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		if ( $this->getStateAllowance() == 0 ) {
			$deduction = 0;
		} else {
			$rate = bcdiv( $retarr['standard_deduction_rate'], 100 );
			$deduction = bcmul( $this->getAnnualTaxableIncome(), $rate );
			if ( $deduction > $retarr['standard_deduction_maximum'] ) {
				$deduction = $retarr['standard_deduction_maximum'];
			}
		}

		Debug::text( 'Standard Deduction: ' . $deduction, __FILE__, __LINE__, __METHOD__, 10 );

		return $deduction;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		$allowance = $retarr['allowance'];

		$retval = bcmul( $this->getStateAllowance(), $allowance );

		Debug::text( 'State Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function getStateTaxPayable() {
		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getStateRate( $annual_income );
			$state_constant = $this->getData()->getStateConstant( $annual_income );
			//$state_rate_income = $this->getData()->getStateRatePreviousIncome($annual_income);

			//$retval = bcadd( bcmul( bcsub( $annual_income, $state_rate_income ), $rate ), $state_constant );
			$retval = bcsub( bcmul( $annual_income, $rate ), $state_constant );
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
