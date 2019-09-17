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
class PayrollDeduction_US_NM extends PayrollDeduction_US {

	var $state_income_tax_rate_options = array(
			20190101 => array(
					10 => array(
							array('income' => 3700, 'rate' => 0, 'constant' => 0),
							array('income' => 9200, 'rate' => 1.7, 'constant' => 0),
							array('income' => 14700, 'rate' => 3.2, 'constant' => 93.50),
							array('income' => 19700, 'rate' => 4.7, 'constant' => 269.50),
							array('income' => 19700, 'rate' => 4.9, 'constant' => 504.50),
					),
					20 => array(
							array('income' => 11550, 'rate' => 0, 'constant' => 0),
							array('income' => 19550, 'rate' => 1.7, 'constant' => 0),
							array('income' => 27550, 'rate' => 3.2, 'constant' => 136),
							array('income' => 35550, 'rate' => 4.7, 'constant' => 392),
							array('income' => 35550, 'rate' => 4.9, 'constant' => 768),
					),
			),
			20180101 => array(
					10 => array(
							array('income' => 3700, 'rate' => 0, 'constant' => 0),
							array('income' => 9200, 'rate' => 1.7, 'constant' => 0),
							array('income' => 14700, 'rate' => 3.2, 'constant' => 93.50),
							array('income' => 19700, 'rate' => 4.7, 'constant' => 269.50),
							array('income' => 19700, 'rate' => 4.9, 'constant' => 504.50),
					),
					20 => array(
							array('income' => 11550, 'rate' => 0, 'constant' => 0),
							array('income' => 19550, 'rate' => 1.7, 'constant' => 0),
							array('income' => 27550, 'rate' => 3.2, 'constant' => 136),
							array('income' => 35550, 'rate' => 4.7, 'constant' => 392),
							array('income' => 35550, 'rate' => 4.9, 'constant' => 768),
					),
			),
			20170101 => array(
					10 => array(
							array('income' => 2300, 'rate' => 0, 'constant' => 0),
							array('income' => 7800, 'rate' => 1.7, 'constant' => 0),
							array('income' => 13300, 'rate' => 3.2, 'constant' => 93.50),
							array('income' => 18300, 'rate' => 4.7, 'constant' => 269.50),
							array('income' => 18300, 'rate' => 4.9, 'constant' => 504.50),
					),
					20 => array(
							array('income' => 8650, 'rate' => 0, 'constant' => 0),
							array('income' => 16650, 'rate' => 1.7, 'constant' => 0),
							array('income' => 24650, 'rate' => 3.2, 'constant' => 136),
							array('income' => 32650, 'rate' => 4.7, 'constant' => 392),
							array('income' => 32650, 'rate' => 4.9, 'constant' => 768),
					),
			),
			20160101 => array(
					10 => array(
							array('income' => 2250, 'rate' => 0, 'constant' => 0),
							array('income' => 7750, 'rate' => 1.7, 'constant' => 0),
							array('income' => 13250, 'rate' => 3.2, 'constant' => 93.50),
							array('income' => 18250, 'rate' => 4.7, 'constant' => 269.50),
							array('income' => 18250, 'rate' => 4.9, 'constant' => 504.50),
					),
					20 => array(
							array('income' => 8550, 'rate' => 0, 'constant' => 0),
							array('income' => 16550, 'rate' => 1.7, 'constant' => 0),
							array('income' => 24550, 'rate' => 3.2, 'constant' => 136),
							array('income' => 32550, 'rate' => 4.7, 'constant' => 392),
							array('income' => 32550, 'rate' => 4.9, 'constant' => 768),
					),
			),
			20150101 => array(
					10 => array(
							array('income' => 2300, 'rate' => 0, 'constant' => 0),
							array('income' => 7800, 'rate' => 1.7, 'constant' => 0),
							array('income' => 13300, 'rate' => 3.2, 'constant' => 93.50),
							array('income' => 18300, 'rate' => 4.7, 'constant' => 269.50),
							array('income' => 18300, 'rate' => 4.9, 'constant' => 504.50),
					),
					20 => array(
							array('income' => 8600, 'rate' => 0, 'constant' => 0),
							array('income' => 16600, 'rate' => 1.7, 'constant' => 0),
							array('income' => 24600, 'rate' => 3.2, 'constant' => 136),
							array('income' => 32600, 'rate' => 4.7, 'constant' => 392),
							array('income' => 32600, 'rate' => 4.9, 'constant' => 768),
					),
			),
			20140101 => array(
					10 => array(
							array('income' => 2250, 'rate' => 0, 'constant' => 0),
							array('income' => 7750, 'rate' => 1.7, 'constant' => 0),
							array('income' => 13250, 'rate' => 3.2, 'constant' => 93.50),
							array('income' => 18250, 'rate' => 4.7, 'constant' => 269.50),
							array('income' => 18250, 'rate' => 4.9, 'constant' => 504.50),
					),
					20 => array(
							array('income' => 8450, 'rate' => 0, 'constant' => 0),
							array('income' => 16450, 'rate' => 1.7, 'constant' => 0),
							array('income' => 24450, 'rate' => 3.2, 'constant' => 136),
							array('income' => 32450, 'rate' => 4.7, 'constant' => 392),
							array('income' => 32450, 'rate' => 4.9, 'constant' => 768),
					),
			),
			20130101 => array(
					10 => array(
							array('income' => 2200, 'rate' => 0, 'constant' => 0),
							array('income' => 7700, 'rate' => 1.7, 'constant' => 0),
							array('income' => 13200, 'rate' => 3.2, 'constant' => 93.50),
							array('income' => 18200, 'rate' => 4.7, 'constant' => 269.50),
							array('income' => 18200, 'rate' => 4.9, 'constant' => 504.50),
					),
					20 => array(
							array('income' => 8300, 'rate' => 0, 'constant' => 0),
							array('income' => 16300, 'rate' => 1.7, 'constant' => 0),
							array('income' => 24300, 'rate' => 3.2, 'constant' => 136),
							array('income' => 32300, 'rate' => 4.7, 'constant' => 392),
							array('income' => 32300, 'rate' => 4.9, 'constant' => 768),
					),
			),
			20120101 => array(
					10 => array(
							array('income' => 2150, 'rate' => 0, 'constant' => 0),
							array('income' => 7650, 'rate' => 1.7, 'constant' => 0),
							array('income' => 13150, 'rate' => 3.2, 'constant' => 93.50),
							array('income' => 18150, 'rate' => 4.7, 'constant' => 269.50),
							array('income' => 18150, 'rate' => 4.9, 'constant' => 504.50),
					),
					20 => array(
							array('income' => 8100, 'rate' => 0, 'constant' => 0),
							array('income' => 16100, 'rate' => 1.7, 'constant' => 0),
							array('income' => 24100, 'rate' => 3.2, 'constant' => 136),
							array('income' => 32100, 'rate' => 4.7, 'constant' => 392),
							array('income' => 32100, 'rate' => 4.9, 'constant' => 768),
					),
			),
			20090101 => array(
					10 => array(
							array('income' => 2050, 'rate' => 0, 'constant' => 0),
							array('income' => 7550, 'rate' => 1.7, 'constant' => 0),
							array('income' => 13050, 'rate' => 3.2, 'constant' => 93.50),
							array('income' => 18050, 'rate' => 4.7, 'constant' => 269.50),
							array('income' => 18050, 'rate' => 4.9, 'constant' => 504.50),
					),
					20 => array(
							array('income' => 7750, 'rate' => 0, 'constant' => 0),
							array('income' => 15750, 'rate' => 1.7, 'constant' => 0),
							array('income' => 23750, 'rate' => 3.2, 'constant' => 136),
							array('income' => 31750, 'rate' => 4.7, 'constant' => 392),
							array('income' => 31750, 'rate' => 4.9, 'constant' => 768),
					),
			),
			20080101 => array(
					10 => array(
							array('income' => 1900, 'rate' => 0, 'constant' => 0),
							array('income' => 7400, 'rate' => 1.7, 'constant' => 0),
							array('income' => 12900, 'rate' => 3.2, 'constant' => 93.50),
							array('income' => 17900, 'rate' => 4.7, 'constant' => 269.50),
							array('income' => 17900, 'rate' => 4.9, 'constant' => 504.50),
					),
					20 => array(
							array('income' => 7250, 'rate' => 0, 'constant' => 0),
							array('income' => 15250, 'rate' => 1.7, 'constant' => 0),
							array('income' => 23250, 'rate' => 3.2, 'constant' => 136),
							array('income' => 31250, 'rate' => 4.7, 'constant' => 392),
							array('income' => 31250, 'rate' => 4.9, 'constant' => 768),
					),
			),
			20070101 => array(
					10 => array(
							array('income' => 1900, 'rate' => 0, 'constant' => 0),
							array('income' => 7400, 'rate' => 1.7, 'constant' => 0),
							array('income' => 12900, 'rate' => 3.2, 'constant' => 93.50),
							array('income' => 17900, 'rate' => 4.7, 'constant' => 269.50),
							array('income' => 17900, 'rate' => 5.3, 'constant' => 504.50),
					),
					20 => array(
							array('income' => 7250, 'rate' => 0, 'constant' => 0),
							array('income' => 15250, 'rate' => 1.7, 'constant' => 0),
							array('income' => 23250, 'rate' => 3.2, 'constant' => 136),
							array('income' => 31250, 'rate' => 4.7, 'constant' => 392),
							array('income' => 31250, 'rate' => 5.3, 'constant' => 768),
					),
			),
			20060101 => array(
					10 => array(
							array('income' => 1800, 'rate' => 0, 'constant' => 0),
							array('income' => 7300, 'rate' => 1.7, 'constant' => 0),
							array('income' => 12800, 'rate' => 3.2, 'constant' => 93.50),
							array('income' => 17800, 'rate' => 4.7, 'constant' => 269.50),
							array('income' => 17800, 'rate' => 5.3, 'constant' => 504.50),
					),
					20 => array(
							array('income' => 6950, 'rate' => 0, 'constant' => 0),
							array('income' => 14950, 'rate' => 1.7, 'constant' => 0),
							array('income' => 22950, 'rate' => 3.2, 'constant' => 136),
							array('income' => 30950, 'rate' => 4.7, 'constant' => 392),
							array('income' => 30950, 'rate' => 5.3, 'constant' => 768),
					),
			),
	);

	var $state_options = array(
			//01-Jan-2019 - No Change
			20180101 => array( //01-Jan-2018
							   'allowance' => 4150,
			),
			//01-Jan-2017 - No Change
			20160101 => array( //01-Jan-2016
							   'allowance' => 4050,
			),
			20150101 => array( //01-Jan-2015
							   'allowance' => 4000,
			),
			20140101 => array( //01-Jan-2014
							   'allowance' => 3950,
			),
			20130101 => array( //01-Jan-2013
							   'allowance' => 3900,
			),
			20120101 => array( //01-Jan-2012
							   'allowance' => 3800,
			),
			20090101 => array( //01-Jan-2009
							   'allowance' => 3650,
			),
			20080101 => array(
					'allowance' => 3450,
			),
			20070101 => array(
					'allowance' => 3450,
			),
			20060101 => array(
					'allowance' => 3250,
			),
	);

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$state_allowance = $this->getStateAllowanceAmount();

		$income = bcsub( $annual_income, $state_allowance );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		$allowance_arr = $retarr['allowance'];

		$allowances = $this->getStateAllowance();

		//As of 01-Jan-2019, the allowances is capped at 3, however they should still be reported properly.
		if ( $this->getDate() >= 20190101 AND $allowances > 3 ) {
			$allowances = 3;
		}

		$retval = bcmul( $allowances, $allowance_arr );

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
