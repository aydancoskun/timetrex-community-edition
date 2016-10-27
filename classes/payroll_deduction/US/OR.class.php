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
/*
 Need to manually calculate the brackets, as the brackets less than 50,000 include the allowance ( 188 ) in the constant.
 Exclude the allowance from each bracket, then getStateTaxPayable() will add $188 (allowance amount) to the constant if the annual income is less than 50,000.
 Check getStateTaxPayable() for the 50,000 setting.
*/
class PayrollDeduction_US_OR extends PayrollDeduction_US {
	var $original_filing_status = NULL;

	var $state_income_tax_rate_options = array(
													20150101 => array(
																10 => array(
																		array( 'income' => 3350,	'rate' => 5,	'constant' => 0 ),
																		array( 'income' => 8400,	'rate' => 7,	'constant' => 167.50 ),
																		array( 'income' => 125000,	'rate' => 9,	'constant' => 521 ),
																		array( 'income' => 125000,	'rate' => 9.9,	'constant' => 11015 ),
																		),
																20 => array(
																		array( 'income' => 6700,	'rate' => 5,	'constant' => 0 ),
																		array( 'income' => 16800,	'rate' => 7,	'constant' => 335 ),
																		array( 'income' => 250000,	'rate' => 9,	'constant' => 1042 ),
																		array( 'income' => 250000,	'rate' => 9.9,	'constant' => 22030 ),
																		),
																),
													20140101 => array(
																10 => array(
																		array( 'income' => 3300,	'rate' => 5,	'constant' => 0 ),
																		array( 'income' => 8250,	'rate' => 7,	'constant' => 165 ),
																		array( 'income' => 125000,	'rate' => 9,	'constant' => 512 ),
																		array( 'income' => 125000,	'rate' => 9.9,	'constant' => 11019 ),
																		),
																20 => array(
																		array( 'income' => 6600,	'rate' => 5,	'constant' => 0 ),
																		array( 'income' => 16500,	'rate' => 7,	'constant' => 330 ),
																		array( 'income' => 250000,	'rate' => 9,	'constant' => 1023 ),
																		array( 'income' => 250000,	'rate' => 9.9,	'constant' => 22038 ),
																		),
																),
													20130101 => array(
																10 => array(
																		array( 'income' => 3250,	'rate' => 5,	'constant' => 0 ),
																		array( 'income' => 8150,	'rate' => 7,	'constant' => 163 ),
																		array( 'income' => 125000,	'rate' => 9,	'constant' => 506 ),
																		array( 'income' => 125000,	'rate' => 9.9,	'constant' => 11022 ),
																		),
																20 => array(
																		array( 'income' => 6500,	'rate' => 5,	'constant' => 0 ),
																		array( 'income' => 16300,	'rate' => 7,	'constant' => 325 ),
																		array( 'income' => 250000,	'rate' => 9,	'constant' => 1011 ),
																		array( 'income' => 250000,	'rate' => 9.9,	'constant' => 22044 ),
																		),
																),
													20120101 => array(
																10 => array(
																		array( 'income' => 3150,	'rate' => 5,	'constant' => 183 ),
																		array( 'income' => 7950,	'rate' => 7,	'constant' => 341 ),
																		array( 'income' => 50000,	'rate' => 9,	'constant' => 677 ),
																		array( 'income' => 125000,	'rate' => 9,	'constant' => 494 ),
																		array( 'income' => 125000,	'rate' => 9.9,	'constant' => 11028 ),
																		),
																20 => array(
																		array( 'income' => 6300,	'rate' => 5,	'constant' => 183 ),
																		array( 'income' => 15900,	'rate' => 7,	'constant' => 498 ),
																		array( 'income' => 50000,	'rate' => 9,	'constant' => 1170 ),
																		array( 'income' => 250000,	'rate' => 9,	'constant' => 987 ),
																		array( 'income' => 250000,	'rate' => 9.9,	'constant' => 22056 ),
																		),
																),
													20070101 => array(
																10 => array(
																		array( 'income' => 2850,	'rate' => 5,	'constant' => 0 ),
																		array( 'income' => 7150,	'rate' => 7,	'constant' => 143 ),
																		array( 'income' => 7150,	'rate' => 9,	'constant' => 444 ),
																		),
																20 => array(
																		array( 'income' => 5700,	'rate' => 5,	'constant' => 0 ),
																		array( 'income' => 14300,	'rate' => 7,	'constant' => 285 ),
																		array( 'income' => 14300,	'rate' => 9,	'constant' => 887 ),
																		),
																),
													20060101 => array(
																10 => array(
																		array( 'income' => 300,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 8030,	'rate' => 7,	'constant' => 0 ),
																		array( 'income' => 8030,	'rate' => 9,	'constant' => 541 ),
																		),
																20 => array(
																		array( 'income' => 2725,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 16065,	'rate' => 7,	'constant' => 0 ),
																		array( 'income' => 16065,	'rate' => 9,	'constant' => 934 ),
																	),
																),
												);

	var $state_options = array(
								20150101 => array( //01-Jan-15
												'standard_deduction' => array(
																			'10' => 2145,
																			'20' => 4295,
																			),
												'allowance' => 194,
												'federal_tax_maximum' => 6450,
												'phase_out' => array(
																		'10' => array(
																						50000 =>  6450,
																						125000 => 6450,
																						130000 => 5150,
																						135000 => 3850,
																						140000 => 2550,
																						145000 => 1250,
																						145000 => 0,
																					 ),
																		'20' => array(
																						50000 =>  6450,
																						250000 => 6450,
																						260000 => 5150,
																						270000 => 3850,
																						280000 => 2550,
																						290000 => 1250,
																						290000 => 0,
																					 ),
																	),
												),
								20140101 => array( //01-Jan-14
												'standard_deduction' => array(
																			'10' => 2115,
																			'20' => 4230,
																			),
												'allowance' => 191,
												'federal_tax_maximum' => 6350,
												'phase_out' => array(
																		'10' => array(
																						50000 =>  6350,
																						125000 => 6350,
																						130000 => 5050,
																						135000 => 3800,
																						140000 => 2500,
																						145000 => 1250,
																						145000 => 0,
																					 ),
																		'20' => array(
																						50000 =>  6350,
																						250000 => 6350,
																						260000 => 5050,
																						270000 => 3800,
																						280000 => 2500,
																						290000 => 1250,
																						290000 => 0,
																					 ),
																	),
												),
								20130101 => array( //01-Jan-13
												'standard_deduction' => array(
																			'10' => 2080,
																			'20' => 4160,
																			),
												'allowance' => 188,
												'federal_tax_maximum' => 6250,
												'phase_out' => array(
																		'10' => array(
																						50000 =>  6250,
																						125000 => 6250,
																						130000 => 5000,
																						135000 => 3750,
																						140000 => 2500,
																						145000 => 1250,
																						145000 => 0,
																					 ),
																		'20' => array(
																						50000 =>  6250,
																						250000 => 6250,
																						260000 => 5000,
																						270000 => 3750,
																						280000 => 2500,
																						290000 => 1250,
																						290000 => 0,
																					 ),
																	),
												),
								20120101 => array( //01-Jan-12
												'standard_deduction' => array(
																			'10' => 2025,
																			'20' => 4055,
																			),
												'allowance' => 183,
												'federal_tax_maximum' => 6100,
												'phase_out' => array(
																		'10' => array(
																						50000 =>  6100,
																						125000 => 6100,
																						130000 => 4850,
																						135000 => 3650,
																						140000 => 2400,
																						145000 => 1200,
																						145000 => 0,
																					 ),
																		'20' => array(
																						50000 =>  6100,
																						250000 => 6100,
																						260000 => 4850,
																						270000 => 3650,
																						280000 => 2400,
																						290000 => 1200,
																						290000 => 0,
																					 ),
																	),
												),
								20100101 => array( //01-Jan-10
												'standard_deduction' => array(
																			'10' => 1950,
																			'20' => 3900,
																			),
												'allowance' => 177,
												'federal_tax_maximum' => 5850
												),
								20090101 => array( //01-Jan-09
												'standard_deduction' => array(
																			'10' => 1945,
																			'20' => 3895,
																			),
												'allowance' => 176,
												'federal_tax_maximum' => 5850
												),
								20070101 => array(
 													'standard_deduction' => array(
																				'10' => 1870,
																				'20' => 3740,
																				),
													'allowance' => 165,
													'federal_tax_maximum' => 5500
													),
								20060101 => array(
 													'standard_deduction' => array(
																				'10' => 0,
																				'20' => 0,
																				),
													'allowance' => 154,
													'federal_tax_maximum' => 4500
													)
								);

	private function getStateRateArray($input_arr, $income) {
		if ( !is_array($input_arr) ) {
			return 0;
		}

		$total_rates = count($input_arr) - 1;
		$prev_bracket=0;
		$i=0;
		foreach( $input_arr as $bracket => $value ) {
			Debug::text('Bracket: '. $bracket .' Value: '.$value, __FILE__, __LINE__, __METHOD__, 10);

			if ($income >= $prev_bracket AND $income < $bracket) {
				Debug::text('Found Bracket: '. $bracket  .' Returning: '. $value, __FILE__, __LINE__, __METHOD__, 10);

				return $value;
			} elseif ($i == $total_rates) {
				Debug::text('Found Last Bracket: '. $bracket .' Returning: '. $value, __FILE__, __LINE__, __METHOD__, 10);
				return $value;
			}

			$prev_bracket = $bracket;
			$i++;
		}

		return FALSE;
	}

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$federal_tax = $this->getFederalTaxPayable();

		if ( $federal_tax > $this->getStateFederalTaxMaximum() ) {
			$federal_tax = $this->getStateFederalTaxMaximum();
		}

		$income = bcsub( bcsub( $annual_income, $federal_tax), $this->getStateStandardDeduction() );

		Debug::text('State Annual Taxable Income: '. $income, __FILE__, __LINE__, __METHOD__, 10);

		return $income;
	}

	function getStateFederalTaxMaximum() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;
		}

		$maximum = $retarr['federal_tax_maximum'];

		if ( isset($retarr['phase_out'][$this->getStateFilingStatus()]) ) {
			$phase_out_arr = $retarr['phase_out'][$this->getStateFilingStatus()];
			$phase_out_maximum = $this->getStateRateArray($phase_out_arr, $this->getAnnualTaxableIncome() );
			if ( $maximum > $phase_out_maximum ) {
				Debug::text('Maximum allowed Federal Tax exceeded phase out maximum of: '. $phase_out_maximum, __FILE__, __LINE__, __METHOD__, 10);
				$maximum = $phase_out_maximum;
			}
		}

		Debug::text('Maximum State allowed Federal Tax: '. $maximum, __FILE__, __LINE__, __METHOD__, 10);

		return $maximum;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;
		}

		if ( $this->original_filing_status == $this->getStateFilingStatus() AND isset($retarr['standard_deduction'][$this->getStateFilingStatus()]) ) {
			$deduction = $retarr['standard_deduction'][$this->getStateFilingStatus()];
		} else {
			$deduction = $retarr['standard_deduction'][10];
		}

		Debug::text('Standard Deduction: '. $deduction, __FILE__, __LINE__, __METHOD__, 10);

		return $deduction;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;
		}

		$allowance_arr = $retarr['allowance'];

		$retval = bcmul( $this->getStateAllowance(), $allowance_arr );

		Debug::text('State Allowance Amount: '. $retval .' Allowances: '. $this->getStateAllowance(), __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function getStateTaxPayable() {
		//IF exemptions are 3 or more, change filing status to married.
		$this->original_filing_status = $this->getStateFilingStatus();

		if ( $this->getStateFilingStatus() == 10 AND $this->getStateAllowance() >= 3 ) {
			Debug::text('Forcing to Married Filing Status from: '. $this->getStateAllowance(), __FILE__, __LINE__, __METHOD__, 10);
			$this->setStateFilingStatus(20); //Married tax rates.
		}

		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getStateRate($annual_income);
			$state_constant = $this->getData()->getStateConstant($annual_income);
			if ( $this->getDate() >= 20120101 AND $annual_income < 50000 )  { //01-Jan-2012 (was 2011?)
				$state_array = $this->getDataFromRateArray($this->getDate(), $this->state_options);
				$state_constant += $state_array['allowance'];
			}
			$state_rate_income = $this->getData()->getStateRatePreviousIncome($annual_income);

			$retval = bcadd( bcmul( bcsub( $annual_income, $state_rate_income ), $rate ), $state_constant );
			//$retval = bcadd( bcmul( $annual_income, $rate ), $state_constant );
		}

		$retval = bcsub( $retval, $this->getStateAllowanceAmount() );

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text('State Annual Tax Payable: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}
}
?>
