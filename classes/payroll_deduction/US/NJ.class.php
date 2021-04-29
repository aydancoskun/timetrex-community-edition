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
/*
														10 => TTi18n::gettext('Rate "A"'),
														20 => TTi18n::gettext('Rate "B"'),
														30 => TTi18n::gettext('Rate "C"'),
														40 => TTi18n::gettext('Rate "D"'),
														50 => TTi18n::gettext('Rate "E"'),

*/

class PayrollDeduction_US_NJ extends PayrollDeduction_US {

	var $state_income_tax_rate_options = [
			20060101 => [
					10 => [
							[ 'income' => 20000, 'rate' => 1.5, 'constant' => 0 ],
							[ 'income' => 35000, 'rate' => 2.0, 'constant' => 300 ],
							[ 'income' => 40000, 'rate' => 3.9, 'constant' => 600 ],
							[ 'income' => 75000, 'rate' => 6.1, 'constant' => 795 ],
							[ 'income' => 500000, 'rate' => 7.0, 'constant' => 2930 ],
							[ 'income' => 500000, 'rate' => 9.9, 'constant' => 32680 ],
					],
					20 => [
							[ 'income' => 20000, 'rate' => 1.5, 'constant' => 0 ],
							[ 'income' => 50000, 'rate' => 2.0, 'constant' => 300 ],
							[ 'income' => 70000, 'rate' => 2.7, 'constant' => 900 ],
							[ 'income' => 80000, 'rate' => 3.9, 'constant' => 1440 ],
							[ 'income' => 150000, 'rate' => 6.1, 'constant' => 1830 ],
							[ 'income' => 500000, 'rate' => 7.0, 'constant' => 6100 ],
							[ 'income' => 500000, 'rate' => 9.9, 'constant' => 30600 ],
					],
					30 => [
							[ 'income' => 20000, 'rate' => 1.5, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 2.3, 'constant' => 300 ],
							[ 'income' => 50000, 'rate' => 2.8, 'constant' => 760 ],
							[ 'income' => 60000, 'rate' => 3.5, 'constant' => 1040 ],
							[ 'income' => 150000, 'rate' => 5.6, 'constant' => 1390 ],
							[ 'income' => 500000, 'rate' => 6.6, 'constant' => 6430 ],
							[ 'income' => 500000, 'rate' => 9.9, 'constant' => 29530 ],
					],
					40 => [
							[ 'income' => 20000, 'rate' => 1.5, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 2.7, 'constant' => 300 ],
							[ 'income' => 50000, 'rate' => 3.4, 'constant' => 840 ],
							[ 'income' => 60000, 'rate' => 4.3, 'constant' => 1180 ],
							[ 'income' => 150000, 'rate' => 5.6, 'constant' => 1610 ],
							[ 'income' => 500000, 'rate' => 6.5, 'constant' => 6650 ],
							[ 'income' => 500000, 'rate' => 9.9, 'constant' => 29400 ],
					],
					50 => [
							[ 'income' => 20000, 'rate' => 1.5, 'constant' => 0 ],
							[ 'income' => 35000, 'rate' => 2.0, 'constant' => 300 ],
							[ 'income' => 100000, 'rate' => 5.8, 'constant' => 600 ],
							[ 'income' => 500000, 'rate' => 6.5, 'constant' => 4370 ],
							[ 'income' => 500000, 'rate' => 9.9, 'constant' => 30370 ],
					],
			],
	];

	var $state_options = [
			20060101 => [
					'allowance' => 1000,
			],
	];

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$state_allowance = $this->getStateAllowanceAmount();

		$income = bcsub( $annual_income, $state_allowance );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$allowance_arr = $retarr['allowance'];

		$retval = bcmul( $this->getStateAllowance(), $allowance_arr );

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
			//$retval = bcadd( bcmul( $annual_income, $rate ), $state_constant );
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
