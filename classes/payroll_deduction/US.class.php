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
		if ( in_array( $value, [ 10, 20, 40 ] ) == false ) {
			$value = 10; //Single
		}

		$this->data['federal_filing_status'] = (int)$value;

		return true;
	}

	function getFederalFilingStatus() {
		if ( isset( $this->data['federal_filing_status'] ) && $this->data['federal_filing_status'] != '' ) {
			return $this->data['federal_filing_status'];
		}

		return 10; //Single
	}

	function setFederalAllowance( $value ) {
		$this->data['federal_allowance'] = $value;

		return true;
	}

	function getFederalAllowance() {
		if ( isset( $this->data['federal_allowance'] ) ) {
			return $this->data['federal_allowance'];
		}

		return false;
	}

	function setFederalFormW4Version( $value ) {
		//Check for invalid value, default to single if found.
		if ( in_array( $value, [ 2019, 2020 ] ) == false ) {
			$value = 2019; //Default to 2019 version.
		}

		$this->data['federal_form_w4_version'] = (string)$value; //2019 or 2010

		return true;
	}

	function getFederalFormW4Version() {
		if ( isset( $this->data['federal_form_w4_version'] ) ) {
			return $this->data['federal_form_w4_version'];
		}

		return 2019; //Default to 2019 version if not set.
	}


	function setFederalMultipleJobs( $value ) {
		$this->data['federal_multiple_jobs'] = (bool)$value; //Boolean Yes/No

		return true;
	}

	function getFederalMultipleJobs() {
		if ( isset( $this->data['federal_multiple_jobs'] ) ) {
			return $this->data['federal_multiple_jobs'];
		}

		return false;
	}

	function setFederalClaimDependents( $value ) {
		$this->data['federal_claim_dependents'] = $value;

		return true;
	}

	function getFederalClaimDependents() {
		if ( isset( $this->data['federal_claim_dependents'] ) ) {
			return $this->data['federal_claim_dependents'];
		}

		return false;
	}

	function setFederalOtherIncome( $value ) {
		$this->data['federal_other_income'] = $value;

		return true;
	}

	function getFederalOtherIncome() {
		if ( isset( $this->data['federal_other_income'] ) ) {
			return $this->data['federal_other_income'];
		}

		return false;
	}

	function setFederalDeductions( $value ) {
		$this->data['federal_deductions'] = $value;

		return true;
	}

	function getFederalDeductions() {
		if ( isset( $this->data['federal_deductions'] ) ) {
			return $this->data['federal_deductions'];
		}

		return false;
	}

	function setFederalAdditionalDeduction( $value ) {
		$this->data['federal_additional_deduction'] = $value;

		return true;
	}

	function getFederalAdditionalDeduction() {
		if ( isset( $this->data['federal_additional_deduction'] ) ) {
			return $this->data['federal_additional_deduction'];
		}

		return false;
	}

	function setYearToDateSocialSecurityContribution( $value ) {
		if ( $value > 0 ) {
			$this->data['social_security_ytd_contribution'] = $value;

			return true;
		}

		return false;
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

			return true;
		}

		return false;
	}

	function getYearToDateFederalUIContribution() {
		if ( isset( $this->data['federal_ui_ytd_contribution'] ) ) {
			return $this->data['federal_ui_ytd_contribution'];
		}

		return 0;
	}

	function setFederalTaxExempt( $value ) {
		$this->data['federal_tax_exempt'] = $value;

		return true;
	}

	function getFederalTaxExempt() {
		if ( isset( $this->data['federal_tax_exempt'] ) ) {
			return $this->data['federal_tax_exempt'];
		}

		return false;
	}

	//
	// State
	//
	function setStateFilingStatus( $value ) {
		$this->data['state_filing_status'] = $value;

		return true;
	}

	function getStateFilingStatus() {
		if ( isset( $this->data['state_filing_status'] ) && $this->data['state_filing_status'] != '' ) {
			return $this->data['state_filing_status'];
		}

		return 10; //Single
	}

	function setStateAllowance( $value ) {
		$this->data['state_allowance'] = (int)$value; //Don't allow fractions, like 1.5 allowances, as this can cause problems with rate lookups failing when its expecting 1 or 2, and it gets 1.5

		return true;
	}

	function getStateAllowance() {
		if ( isset( $this->data['state_allowance'] ) ) {
			return $this->data['state_allowance'];
		}

		return false;
	}

	function setStateAdditionalDeduction( $value ) {
		$this->data['state_additional_deduction'] = $value;

		return true;
	}

	function getStateAdditionalDeduction() {
		if ( isset( $this->data['state_additional_deduction'] ) ) {
			return $this->data['state_additional_deduction'];
		}

		return false;
	}

	//Default to 0 unless otherwise defined in a State specific class.
	function getStateTaxPayable() {
		if ( $this->getProvincialTaxExempt() == true ) {
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
			if ( $highest_taxable_amount > 0 && $retval > $highest_taxable_amount ) {
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
			if ( $highest_taxable_amount > 0 && $retval > $highest_taxable_amount ) {
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

		if ( !isset( $retval ) || $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'zzDistrict Annual Tax Payable: ' . $retval . ' User Value 2: ' . $this->getUserValue2() . ' Annual Income: ' . $annual_income, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function setDistrictFilingStatus( $value ) {
		$this->data['district_filing_status'] = $value;

		return true;
	}

	function getDistrictFilingStatus() {
		if ( isset( $this->data['district_filing_status'] ) ) {
			return $this->data['district_filing_status'];
		}

		return 10; //Single
	}

	function setDistrictAllowance( $value ) {
		$this->data['district_allowance'] = $value;

		return true;
	}

	function getDistrictAllowance() {
		if ( isset( $this->data['district_allowance'] ) ) {
			return $this->data['district_allowance'];
		}

		return false;
	}

	function setYearToDateStateUIContribution( $value ) {
		if ( $value > 0 ) {
			$this->data['state_ui_ytd_contribution'] = $value;

			return true;
		}

		return false;
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

			return true;
		}

		return false;
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

			return true;
		}

		return false;
	}

	function getStateUIWageBase() {
		if ( isset( $this->data['state_ui_wage_base'] ) ) {
			return $this->data['state_ui_wage_base'];
		}

		return 0;
	}

	function setProvincialTaxExempt( $value ) {
		$this->data['provincial_tax_exempt'] = $value;

		return true;
	}

	function getProvincialTaxExempt() {
		if ( isset( $this->data['provincial_tax_exempt'] ) ) {
			return $this->data['provincial_tax_exempt'];
		}

		return false;
	}

	function setSocialSecurityExempt( $value ) {
		$this->data['social_security_exempt'] = $value;

		return true;
	}

	function getSocialSecurityExempt() {
		if ( isset( $this->data['social_security_exempt'] ) ) {
			return $this->data['social_security_exempt'];
		}

		return false;
	}

	function setMedicareExempt( $value ) {
		$this->data['medicare_exempt'] = $value;

		return true;
	}

	function getMedicareExempt() {
		if ( isset( $this->data['medicare_exempt'] ) ) {
			return $this->data['medicare_exempt'];
		}

		return false;
	}

	function setUIExempt( $value ) {
		$this->data['ui_exempt'] = $value;

		return true;
	}

	function getUIExempt() {
		if ( isset( $this->data['ui_exempt'] ) ) {
			return $this->data['ui_exempt'];
		}

		return false;
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
			if ( $highest_taxable_amount > 0 && $retval > $highest_taxable_amount ) {
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
		if ( $this->getFederalTaxExempt() == true ) {
			Debug::text( 'Federal Tax Exempt!', __FILE__, __LINE__, __METHOD__, 10 );

			return 0;
		}

		$annual_taxable_income = $this->getAnnualTaxableIncome();
		if ( $this->getDate() >= 20200101 && $this->getFederalFormW4Version() == 2020 ) { //See Form W4 Version check below as well.
			$annual_taxable_income = bcadd( $annual_taxable_income, $this->getFederalOtherIncome() );

			$filing_status_adjustment = 0;
			if ( $this->getFederalMultipleJobs() == false ) {
				if ( $this->getFederalFilingStatus() == 20 ) {                                                    //Married Filing Jointly
					$filing_status_adjustment = bcmul( $this->getFederalAllowanceAmount( $this->getDate() ), 3 ); //$12,600 (4,200 * 3 )
				} else {
					$filing_status_adjustment = bcmul( $this->getFederalAllowanceAmount( $this->getDate() ), 2 ); //$8,400 ( 4,200 * 2 )
				}
			}
			Debug::text( 'Filing Status Adjustment: ' . $filing_status_adjustment . ' W4 Deductions: ' . $this->getFederalDeductions(), __FILE__, __LINE__, __METHOD__, 10 );

			$annual_taxable_income = bcsub( bcsub( $annual_taxable_income, $filing_status_adjustment ), $this->getFederalDeductions() );
			Debug::text( '2020 W4 - Annual Taxable Income: ' . $annual_taxable_income . ' Other Income: ' . $this->getFederalOtherIncome() . ' ', __FILE__, __LINE__, __METHOD__, 10 );
		} else {
			$annual_allowance = bcmul( $this->getFederalAllowanceAmount( $this->getDate() ), $this->getFederalAllowance() );
			Debug::text( 'Legacy W4 - Annual Taxable Income: ' . $annual_taxable_income . 'Allowance: ' . $annual_allowance, __FILE__, __LINE__, __METHOD__, 10 );

			if ( $annual_taxable_income > $annual_allowance ) {
				$annual_taxable_income = bcsub( $annual_taxable_income, $annual_allowance );
			} else {
				Debug::text( 'Income is less then allowance: ', __FILE__, __LINE__, __METHOD__, 10 );
				$annual_taxable_income = 0;
			}
		}

		if ( $annual_taxable_income > 0 ) {
			Debug::text( 'Annual Taxable Income: ' . $annual_taxable_income, __FILE__, __LINE__, __METHOD__, 10 );
			$rate = $this->getData()->getFederalRate( $annual_taxable_income );
			$federal_constant = $this->getData()->getFederalConstant( $annual_taxable_income );
			$federal_rate_income = $this->getData()->getFederalRatePreviousIncome( $annual_taxable_income );

			$retval = bcadd( bcmul( bcsub( $annual_taxable_income, $federal_rate_income ), $rate ), $federal_constant );

			if ( $this->getDate() >= 20200101 && $this->getFederalFormW4Version() == 2020 ) {  //See Form W4 Version check above as well.
				$additional_deduction = 0;
				if ( $this->getFederalAdditionalDeduction() > 0 ) {
					$additional_deduction = bcmul( $this->getFederalAdditionalDeduction(), $this->getAnnualPayPeriods() ); //Federal Deduction amount from 2020 W4 is *PER PAY PERIOD*
				}

				Debug::text( 'Claimed Dependent Amount: ' . $this->getFederalClaimDependents() . ' Additional Deduction: ' . $additional_deduction, __FILE__, __LINE__, __METHOD__, 10 );
				$retval = bcsub( bcadd( $retval, $additional_deduction ), $this->getFederalClaimDependents() );
			}

			if ( $retval < 0 ) {
				$retval = 0;
			}
		} else {
			$retval = 0;
		}

		Debug::text( 'RetVal: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	//
	// Social Security
	//
	function getAnnualEmployeeSocialSecurity() {
		if ( $this->getSocialSecurityExempt() == true ) {
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
		if ( $this->getSocialSecurityExempt() == true ) {
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
		if ( $this->getSocialSecurityExempt() == true ) {
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
		if ( $this->getMedicareExempt() == true ) {
			return 0;
		}

		$pay_period_income = $this->getGrossPayPeriodIncome();

		$rate_data = $this->getMedicareRate();
		$rate = bcdiv( $rate_data['employee_rate'], 100 );
		Debug::text( 'Rate: ' . $rate, __FILE__, __LINE__, __METHOD__, 10 );

		$amount = round( bcmul( $pay_period_income, $rate ), 2 ); //Must round separately from additional medicare, as they are broken out in tax reports.
		Debug::text( 'Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );

		$threshold_income = $this->getMedicareAdditionalEmployerThreshold();
		Debug::text( 'Threshold Income: ' . $threshold_income, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $threshold_income > 0 && bcadd( $this->getYearToDateGrossIncome(), $this->getGrossPayPeriodIncome() ) > $threshold_income ) {
			if ( $this->getYearToDateGrossIncome() < $threshold_income ) {
				$threshold_income = bcsub( bcadd( $this->getYearToDateGrossIncome(), $this->getGrossPayPeriodIncome() ), $threshold_income );
			} else {
				$threshold_income = $pay_period_income;
			}
			Debug::text( 'bThreshold Income: ' . $threshold_income, __FILE__, __LINE__, __METHOD__, 10 );
			$threshold_amount = round( bcmul( $threshold_income, bcdiv( $rate_data['employee_threshold_rate'], 100 ) ), 2 ); //Must round separately from regular medicare, as they are broken out in tax reports.
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
		if ( $this->getMedicareExempt() == true ) {
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
		if ( $this->getUIExempt() == true ) {
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

		$array = [
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

		];

		Debug::Arr( $array, 'Deductions Array:', __FILE__, __LINE__, __METHOD__, 10 );

		return $array;
	}
}

?>