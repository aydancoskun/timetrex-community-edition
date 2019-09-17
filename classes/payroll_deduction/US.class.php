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
class PayrollDeduction_US extends PayrollDeduction_US_Data {
	//
	// Federal
	//
	function setFederalFilingStatus( $value ) {
		//Check for invalid value, default to single if found.
		if ( $value > 20 ) {
			$value = 10; //Single
		}
		$this->data['federal_filing_status'] = $value;

		return TRUE;
	}

	function getFederalFilingStatus() {
		if ( isset( $this->data['federal_filing_status'] ) AND $this->data['federal_filing_status'] != '' ) {
			return $this->data['federal_filing_status'];
		}

		return 10; //Single
	}

	function setFederalAllowance( $value ) {
		$this->data['federal_allowance'] = $value;

		return TRUE;
	}

	function getFederalAllowance() {
		if ( isset( $this->data['federal_allowance'] ) ) {
			return $this->data['federal_allowance'];
		}

		return FALSE;
	}

	function setFederalAdditionalDeduction( $value ) {
		$this->data['federal_additional_deduction'] = $value;

		return TRUE;
	}

	function getFederalAdditionalDeduction() {
		if ( isset( $this->data['federal_additional_deduction'] ) ) {
			return $this->data['federal_additional_deduction'];
		}

		return FALSE;
	}

	function setYearToDateSocialSecurityContribution( $value ) {
		if ( $value > 0 ) {
			$this->data['social_security_ytd_contribution'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getYearToDateSocialSecurityContribution() {
		if ( isset( $this->data['social_security_ytd_contribution'] ) ) {
			return $this->data['social_security_ytd_contribution'];
		}

		return 0;
	}

	function setYearToDateFederalUIContribution( $value ) {
		if ( $value > 0 ) {
			$this->data['federal_ui_ytd_contribution'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getYearToDateFederalUIContribution() {
		if ( isset( $this->data['federal_ui_ytd_contribution'] ) ) {
			return $this->data['federal_ui_ytd_contribution'];
		}

		return 0;
	}

	function setFederalTaxExempt( $value ) {
		$this->data['federal_tax_exempt'] = $value;

		return TRUE;
	}

	function getFederalTaxExempt() {
		if ( isset( $this->data['federal_tax_exempt'] ) ) {
			return $this->data['federal_tax_exempt'];
		}

		return FALSE;
	}

	//
	// State
	//
	function setStateFilingStatus( $value ) {
		$this->data['state_filing_status'] = $value;

		return TRUE;
	}

	function getStateFilingStatus() {
		if ( isset( $this->data['state_filing_status'] ) AND $this->data['state_filing_status'] != '' ) {
			return $this->data['state_filing_status'];
		}

		return 10; //Single
	}

	function setStateAllowance( $value ) {
		$this->data['state_allowance'] = (int)$value; //Don't allow fractions, like 1.5 allowances, as this can cause problems with rate lookups failing when its expecting 1 or 2, and it gets 1.5

		return TRUE;
	}

	function getStateAllowance() {
		if ( isset( $this->data['state_allowance'] ) ) {
			return $this->data['state_allowance'];
		}

		return FALSE;
	}

	function setStateAdditionalDeduction( $value ) {
		$this->data['state_additional_deduction'] = $value;

		return TRUE;
	}

	function getStateAdditionalDeduction() {
		if ( isset( $this->data['state_additional_deduction'] ) ) {
			return $this->data['state_additional_deduction'];
		}

		return FALSE;
	}

	//Default to 0 unless otherwise defined in a State specific class.
	function getStateTaxPayable() {
		if ( $this->getProvincialTaxExempt() == TRUE ) {
			Debug::text( 'State Tax Exempt!', __FILE__, __LINE__, __METHOD__, 10 );

			return 0;
		} else {
			return $this->_getStateTaxPayable();
		}
	}

	function _getStateTaxPayable() {
		return 0;
	}

	function getStatePayPeriodDeductionRoundedValue( $amount ) {
		return $amount;
	}

	function getStatePayPeriodDeductions() {
		if ( $this->getFormulaType() == 20 ) {
			Debug::text( 'Formula Type: ' . $this->getFormulaType() . ' YTD Payable: ' . $this->getStateTaxPayable() . ' YTD Paid: ' . $this->getYearToDateDeduction() . ' Current PP: ' . $this->getCurrentPayPeriod(), __FILE__, __LINE__, __METHOD__, 10 );
			$retval = $this->calcNonPeriodicDeduction( $this->getStateTaxPayable(), $this->getYearToDateDeduction() );

			//Ensure that the tax amount doesn't exceed the highest possible tax rate plus 25% for "catch-up" purposes.
			$highest_taxable_amount = bcmul( $this->getGrossPayPeriodIncome(), bcmul( $this->getStateHighestRate(), 1.25 ) );
			if ( $highest_taxable_amount > 0 AND $retval > $highest_taxable_amount ) {
				$retval = $highest_taxable_amount;
				Debug::text( 'State tax amount exceeds highest tax bracket rate, capping amount at: ' . $highest_taxable_amount, __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			$retval = bcdiv( $this->getStateTaxPayable(), $this->getAnnualPayPeriods() );
		}

		Debug::text( 'State Pay Period Deductions: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->getStatePayPeriodDeductionRoundedValue( $retval );
	}

	//
	// District
	//

	//Generic district functions that handle straight percentages for any district unless otherwise overloaded.
	//for custom formulas.
	function getDistrictPayPeriodDeductions() {
		if ( $this->getFormulaType() == 20 ) {
			Debug::text( 'Formula Type: ' . $this->getFormulaType() . ' YTD Payable: ' . $this->getDistrictTaxPayable() . ' YTD Paid: ' . $this->getYearToDateDeduction() . ' Current PP: ' . $this->getCurrentPayPeriod(), __FILE__, __LINE__, __METHOD__, 10 );
			$retval = $this->calcNonPeriodicDeduction( $this->getDistrictTaxPayable(), $this->getYearToDateDeduction() );

			//Ensure that the tax amount doesn't exceed the highest possible tax rate plus 25% for "catch-up" purposes.
			$highest_taxable_amount = bcmul( $this->getGrossPayPeriodIncome(), bcmul( $this->getDistrictHighestRate(), 1.25 ) );
			if ( $highest_taxable_amount > 0 AND $retval > $highest_taxable_amount ) {
				$retval = $highest_taxable_amount;
				Debug::text( 'District tax amount exceeds highest tax bracket rate, capping amount at: ' . $highest_taxable_amount, __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			$retval = bcdiv( $this->getDistrictTaxPayable(), $this->getAnnualPayPeriods() );
		}

		Debug::text( 'District Pay Period Deductions: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function getDistrictAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();

		return $annual_income;
	}

	function getDistrictTaxPayable() {
		$annual_income = $this->getDistrictAnnualTaxableIncome();

		if ( $annual_income > 0 ) {
			$rate = bcdiv( $this->getUserValue2(), 100 );
			$retval = bcmul( $annual_income, $rate );
		}

		if ( !isset( $retval ) OR $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'zzDistrict Annual Tax Payable: ' . $retval . ' User Value 2: ' . $this->getUserValue2() . ' Annual Income: ' . $annual_income, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function setDistrictFilingStatus( $value ) {
		$this->data['district_filing_status'] = $value;

		return TRUE;
	}

	function getDistrictFilingStatus() {
		if ( isset( $this->data['district_filing_status'] ) ) {
			return $this->data['district_filing_status'];
		}

		return 10; //Single
	}

	function setDistrictAllowance( $value ) {
		$this->data['district_allowance'] = $value;

		return TRUE;
	}

	function getDistrictAllowance() {
		if ( isset( $this->data['district_allowance'] ) ) {
			return $this->data['district_allowance'];
		}

		return FALSE;
	}

	function setYearToDateStateUIContribution( $value ) {
		if ( $value > 0 ) {
			$this->data['state_ui_ytd_contribution'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getYearToDateStateUIContribution() {
		if ( isset( $this->data['state_ui_ytd_contribution'] ) ) {
			return $this->data['state_ui_ytd_contribution'];
		}

		return 0;
	}

	function setStateUIRate( $value ) {
		if ( $value > 0 ) {
			$this->data['state_ui_rate'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getStateUIRate() {
		if ( isset( $this->data['state_ui_rate'] ) ) {
			return $this->data['state_ui_rate'];
		}

		return 0;
	}

	function setStateUIWageBase( $value ) {
		if ( $value > 0 ) {
			$this->data['state_ui_wage_base'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getStateUIWageBase() {
		if ( isset( $this->data['state_ui_wage_base'] ) ) {
			return $this->data['state_ui_wage_base'];
		}

		return 0;
	}

	function setProvincialTaxExempt( $value ) {
		$this->data['provincial_tax_exempt'] = $value;

		return TRUE;
	}

	function getProvincialTaxExempt() {
		if ( isset( $this->data['provincial_tax_exempt'] ) ) {
			return $this->data['provincial_tax_exempt'];
		}

		return FALSE;
	}

	function setSocialSecurityExempt( $value ) {
		$this->data['social_security_exempt'] = $value;

		return TRUE;
	}

	function getSocialSecurityExempt() {
		if ( isset( $this->data['social_security_exempt'] ) ) {
			return $this->data['social_security_exempt'];
		}

		return FALSE;
	}

	function setMedicareExempt( $value ) {
		$this->data['medicare_exempt'] = $value;

		return TRUE;
	}

	function getMedicareExempt() {
		if ( isset( $this->data['medicare_exempt'] ) ) {
			return $this->data['medicare_exempt'];
		}

		return FALSE;
	}

	function setUIExempt( $value ) {
		$this->data['ui_exempt'] = $value;

		return TRUE;
	}

	function getUIExempt() {
		if ( isset( $this->data['ui_exempt'] ) ) {
			return $this->data['ui_exempt'];
		}

		return FALSE;
	}

	//
	// Calculation Functions
	//
	function getAnnualTaxableIncome() {
		if ( $this->getFormulaType() == 20 ) {
			Debug::text( 'Formula Type: ' . $this->getFormulaType() . ' YTD Gross: ' . $this->getYearToDateGrossIncome() . ' This Gross: ' . $this->getGrossPayPeriodIncome() . ' Current PP: ' . $this->getCurrentPayPeriod(), __FILE__, __LINE__, __METHOD__, 10 );
			$retval = $this->calcNonPeriodicIncome( $this->getYearToDateGrossIncome(), $this->getGrossPayPeriodIncome() );
		} else {
			$retval = bcmul( $this->getGrossPayPeriodIncome(), $this->getAnnualPayPeriods() );
		}
		Debug::text( 'Annual Taxable Income: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $retval < 0 ) {
			$retval = 0;
		}

		return $retval;
	}

	//
	// Federal Tax
	//
	function getFederalPayPeriodDeductions() {
		if ( $this->getFormulaType() == 20 ) {
			Debug::text( 'Formula Type: ' . $this->getFormulaType() . ' YTD Payable: ' . $this->getFederalTaxPayable() . ' YTD Paid: ' . $this->getYearToDateDeduction() . ' Current PP: ' . $this->getCurrentPayPeriod(), __FILE__, __LINE__, __METHOD__, 10 );
			$retval = $this->calcNonPeriodicDeduction( $this->getFederalTaxPayable(), $this->getYearToDateDeduction() );

			//Ensure that the tax amount doesn't exceed the highest possible tax rate plus 25% for "catch-up" purposes.
			$highest_taxable_amount = bcmul( $this->getGrossPayPeriodIncome(), bcmul( $this->getFederalHighestRate(), 1.25 ) );
			if ( $highest_taxable_amount > 0 AND $retval > $highest_taxable_amount ) {
				$retval = $highest_taxable_amount;
				Debug::text( 'Federal tax amount exceeds highest tax bracket rate, capping amount at: ' . $highest_taxable_amount, __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			$retval = bcdiv( $this->getFederalTaxPayable(), $this->getAnnualPayPeriods() );
		}

		Debug::text( 'Federal Pay Period Deductions: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function getFederalTaxPayable() {
		if ( $this->getFederalTaxExempt() == TRUE ) {
			Debug::text( 'Federal Tax Exempt!', __FILE__, __LINE__, __METHOD__, 10 );

			return 0;
		}

		$annual_taxable_income = $this->getAnnualTaxableIncome();
		$annual_allowance = bcmul( $this->getFederalAllowanceAmount( $this->getDate() ), $this->getFederalAllowance() );

		Debug::text( 'Annual Taxable Income: ' . $annual_taxable_income, __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( 'Allowance: ' . $annual_allowance, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $annual_taxable_income > $annual_allowance ) {
			$modified_annual_taxable_income = bcsub( $annual_taxable_income, $annual_allowance );
			$rate = $this->getData()->getFederalRate( $modified_annual_taxable_income );
			$federal_constant = $this->getData()->getFederalConstant( $modified_annual_taxable_income );
			$federal_rate_income = $this->getData()->getFederalRatePreviousIncome( $modified_annual_taxable_income );

			$retval = bcadd( bcmul( bcsub( $modified_annual_taxable_income, $federal_rate_income ), $rate ), $federal_constant );
		} else {
			Debug::text( 'Income is less then allowance: ', __FILE__, __LINE__, __METHOD__, 10 );

			$retval = 0;
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'RetVal: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	//
	// Social Security
	//
	function getAnnualEmployeeSocialSecurity() {
		if ( $this->getSocialSecurityExempt() == TRUE ) {
			return 0;
		}

		$annual_income = $this->getAnnualTaxableIncome();
		$rate = bcdiv( $this->getSocialSecurityRate(), 100 );
		$maximum_contribution = $this->getSocialSecurityMaximumContribution();

		Debug::text( 'Rate: ' . $rate . ' Maximum Contribution: ' . $maximum_contribution, __FILE__, __LINE__, __METHOD__, 10 );

		$amount = bcmul( $annual_income, $rate );
		$max_amount = $maximum_contribution;

		if ( $amount > $max_amount ) {
			$retval = $max_amount;
		} else {
			$retval = $amount;
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		return $retval;
	}

	function getEmployeeSocialSecurity() {
		if ( $this->getSocialSecurityExempt() == TRUE ) {
			return 0;
		}

		$type = 'employee';

		$pay_period_income = $this->getGrossPayPeriodIncome();
		$rate = bcdiv( $this->getSocialSecurityRate( $type ), 100 );
		$maximum_contribution = $this->getSocialSecurityMaximumContribution( $type );
		$ytd_contribution = $this->getYearToDateSocialSecurityContribution();

		Debug::text( 'Rate: ' . $rate . ' YTD Contribution: ' . $ytd_contribution, __FILE__, __LINE__, __METHOD__, 10 );

		$amount = bcmul( $pay_period_income, $rate );
		$max_amount = bcsub( $maximum_contribution, $ytd_contribution );

		if ( $amount > $max_amount ) {
			$retval = $max_amount;
		} else {
			$retval = $amount;
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		return $retval;
	}

	function getEmployerSocialSecurity() {
		if ( $this->getSocialSecurityExempt() == TRUE ) {
			return 0;
		}

		$type = 'employer';

		$pay_period_income = $this->getGrossPayPeriodIncome();
		$rate = bcdiv( $this->getSocialSecurityRate( $type ), 100 );
		$maximum_contribution = $this->getSocialSecurityMaximumContribution( $type );
		$ytd_contribution = $this->getYearToDateSocialSecurityContribution();

		Debug::text( 'Rate: ' . $rate . ' YTD Contribution: ' . $ytd_contribution, __FILE__, __LINE__, __METHOD__, 10 );

		$amount = bcmul( $pay_period_income, $rate );
		$max_amount = bcsub( $maximum_contribution, $ytd_contribution );

		if ( $amount > $max_amount ) {
			$retval = $max_amount;
		} else {
			$retval = $amount;
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		return $retval;
	}


	//
	// Medicare
	//
	function getAnnualEmployeeMedicare() {
		return bcmul( $this->getEmployeeMedicare(), $this->getAnnualPayPeriods() );
	}

	function getEmployeeMedicare() {
		if ( $this->getMedicareExempt() == TRUE ) {
			return 0;
		}

		$pay_period_income = $this->getGrossPayPeriodIncome();

		$rate_data = $this->getMedicareRate();
		$rate = bcdiv( $rate_data['employee_rate'], 100 );
		Debug::text( 'Rate: ' . $rate, __FILE__, __LINE__, __METHOD__, 10 );

		$amount = bcmul( $pay_period_income, $rate );
		Debug::text( 'Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );

		$threshold_income = $this->getMedicareAdditionalEmployerThreshold();
		Debug::text( 'Threshold Income: ' . $threshold_income, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $threshold_income > 0 AND ( $this->getYearToDateGrossIncome() + $this->getGrossPayPeriodIncome() ) > $threshold_income ) {
			if ( $this->getYearToDateGrossIncome() < $threshold_income ) {
				$threshold_income = bcsub( bcadd( $this->getYearToDateGrossIncome(), $this->getGrossPayPeriodIncome() ), $threshold_income );
			} else {
				$threshold_income = $pay_period_income;
			}
			Debug::text( 'bThreshold Income: ' . $threshold_income, __FILE__, __LINE__, __METHOD__, 10 );
			$threshold_amount = bcmul( $threshold_income, bcdiv( $rate_data['employee_threshold_rate'], 100 ) );
			Debug::text( 'Threshold Amount: ' . $threshold_amount, __FILE__, __LINE__, __METHOD__, 10 );
			$amount = bcadd( $amount, $threshold_amount );
		}

		if ( $amount < 0 ) {
			$amount = 0;
		}

		return $amount;
	}

	function getEmployerMedicare() {
		//return $this->getEmployeeMedicare();
		if ( $this->getMedicareExempt() == TRUE ) {
			return 0;
		}

		$pay_period_income = $this->getGrossPayPeriodIncome();

		$rate_data = $this->getMedicareRate();
		$rate = bcdiv( $rate_data['employer_rate'], 100 );
		Debug::text( 'Rate: ' . $rate, __FILE__, __LINE__, __METHOD__, 10 );

		$amount = bcmul( $pay_period_income, $rate );

		if ( $amount < 0 ) {
			$amount = 0;
		}

		return $amount;
	}

	//
	// Federal UI
	//
	function getFederalEmployerUI() {
		if ( $this->getUIExempt() == TRUE ) {
			return 0;
		}

		$pay_period_income = $this->getGrossPayPeriodIncome();
		$rate = bcdiv( $this->getFederalUIRate(), 100 );
		$maximum_contribution = $this->getFederalUIMaximumContribution();
		$ytd_contribution = $this->getYearToDateFederalUIContribution();

		Debug::text( 'Rate: ' . $rate . ' YTD Contribution: ' . $ytd_contribution . ' Maximum: ' . $maximum_contribution, __FILE__, __LINE__, __METHOD__, 10 );

		$amount = bcmul( $pay_period_income, $rate );
		$max_amount = bcsub( $maximum_contribution, $ytd_contribution );

		if ( $amount > $max_amount ) {
			$retval = $max_amount;
		} else {
			$retval = $amount;
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		return $retval;
	}

	function getPayPeriodTaxDeductions() {
		return bcadd( $this->getFederalPayPeriodDeductions(), $this->getStatePayPeriodDeductions() );
	}

	function getPayPeriodEmployeeTotalDeductions() {
		//return $this->getPayPeriodTaxDeductions() + $this->getEmployeeCPP() + $this->getEmployeeEI();
		return bcadd( bcadd( $this->getPayPeriodTaxDeductions(), $this->getEmployeeSocialSecurity() ), $this->getEmployeeMedicare() );
	}

	function getPayPeriodEmployeeNetPay() {
		return bcsub( $this->getGrossPayPeriodIncome(), $this->getPayPeriodEmployeeTotalDeductions() );
	}

	function RoundNearestDollar( $amount ) {
		return round( $amount, 0 );
	}

	/*
		Use this to get all useful values.
	*/
	function getArray() {

		$array = array(
				'gross_pay'                => $this->getGrossPayPeriodIncome(),
				'federal_tax'              => $this->getFederalPayPeriodDeductions(),
				'state_tax'                => $this->getStatePayPeriodDeductions(),
				/*
										'employee_social_security' => $this->getEmployeeSocialSecurity(),
										'employer_social_security' => $this->getEmployeeSocialSecurity(),
										'employee_medicare' => $this->getEmployeeMedicare(),
										'employer_medicare' => $this->getEmployerMedicare(),
				*/
				'employee_social_security' => $this->getEmployeeSocialSecurity(),
				'federal_employer_ui'      => $this->getFederalEmployerUI(),
				//						'state_employer_ui' => $this->getStateEmployerUI(),

		);

		Debug::Arr( $array, 'Deductions Array:', __FILE__, __LINE__, __METHOD__, 10 );

		return $array;
	}
}

?>