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


/*
* Other tax calculators:
	http://payroll.intuit.com/paycheck_calculators/legacy/
	http://www.paycheckcity.com/calculator/salary/
	http://www.pensoft.com/freestuff/stateinformation.aspx
	http://www.yourmoneypage.com/withhold/fedwh1.php

	- List of tax table changes*****:
		http://legacy.americanpayroll.org/paystate/paystateupdate.html
		http://www.tax-tables.com/withholding.html
		http://www.payroll-taxes.com/federal-tax

	- Federal/State tax information: http://www.payroll-taxes.com/state-tax.htm

//
//***Update PayrollDeduction.class.php with updated date/version
//


//State UI wage base rates: http://www.americanpayroll.org/members/stateui/state-ui-2/

//CHANGED-* means document was updated and did change.
//NOCHANGE-* means document was updated for the year, but no changes affected the formulas.
//*CHECKAGAIN-* means document hasn't been updated and needs to be rechecked.

//Change every year usually
CHANGED:16-Dec-16				Federal          		- Google: Notice 1036 http://www.irs.gov/pub/irs-pdf/n1036.pdf
CHANGED:16-Dec-16				'CA' => 'California' 	- http://www.edd.ca.gov/Payroll_Taxes/Rates_and_Withholding.htm *PIT Withholding schedules
CHANGED:16-Dec-16				'MN' => 'Minnesota',	- http://www.revenue.state.mn.us/businesses/withholding/Pages/Forms.aspx *2013 Minnesota Withholding Computer Formula - Calculator: https://www.mndor.state.mn.us/tp/withholdingtaxcalc/_/
CHANGED:18-Dec-16				'OK' => 'Oklahoma',		- http://ok.gov/tax/Forms_&_Publications/Publications/Withholding/ *OW-2, Oklahoma Income Tax Withholding Tables
CHANGED:18-Dec-16				'VT' => 'Vermont',		- http://tax.vermont.gov/business-and-corp/withholding-tax *Income Tax Withholding  Instructions, Tables, and Charts.
CHANGED:18-Dec-16				'KY' => 'Kentucky', 	- http://revenue.ky.gov/Business/Pages/Employer-Payroll-Withholding.aspx *Standard Deduction adjusted each year in Computer Formula (Optional Withholding Method)
CHANGED:18-Dec-16				'ME' => 'Maine',		- http://www.state.me.us/revenue/forms/with/2017.htm -- Check each year on the right of the page.
CHANGED:18-Dec-16				'NC' => 'North Carolina'- http://www.dornc.com/taxes/wh_tax/index.html *Income Tax Withholding Tables & Instructions for Employers, NC30
CHANGED-18-Dec-16				'RI' => 'Rhode Island', - http://www.tax.state.ri.us/misc/software_developers.php *Percentage Method Withholding Tables
CHANGED:27-Dec-16				'MO' => 'Missouri',		- http://dor.mo.gov/business/withhold/ *Click on Withholding Formula to see update for each year.
CHANGED:04-Jan-17				'OR' => 'Oregon',		- http://www.oregon.gov/DOR/programs/businesses/Pages/payroll-updates.aspx *Search: Withholdings Tax Formulas 2013
CHANGED:04-Jan-17				'CO' => 'Colorado',		- https://www.colorado.gov/pacific/tax/withholding-payroll-tax-instructions-and-forms *Form: DR 1098
CHANGED:04-Jan-17				'ND' => 'North Dakota', - http://www.nd.gov/tax/user/businesses/formspublications/income-tax-withholding/income-tax-withholding-instructions--tables *Income Tax Withholding Rates & Instructions Booklet
CHANGED:04-Jan-17				'NM' => 'New Mexico', 	- http://www.tax.newmexico.gov/Businesses/Wage-Withholding-Tax/Pages/Home.aspx *FYI-104 ***Often changes in Jan.
NOCHANGE:16-Dec-16				'CT' => 'Connecticut'	- http://www.ct.gov/drs/cwp/view.asp?a=1509&q=444766 *May have to search for the latest year... Form TPG-211 Withholding Calculation Rules Effective
NOCHANGE:04-Dec-17				'ID' => 'Idaho',		- http://tax.idaho.gov/s-results-pub.cfm?doc=EPB00006&pkey=bus
NOCHANGE:04-Jan-17				'KS' => 'Kansas',		- http://www.ksrevenue.org/forms-btwh.html *Form: KW-100
NOCHANGE:04-Jan-17				'MI' => 'Michigan',		- http://www.michigan.gov/taxes/0,4676,7-238-43519_43531---,00.html *Michigan Income Tax Withholding Guide 446-I
NOCHANGE:04-Jan-17				'IL' => 'Illinois',		- http://www.revenue.state.il.us/Businesses/TaxInformation/Payroll/index.htm *Booklet IL-700-T


CHECKAGAIN:04-Jan-17			'AL' => 'Alabama' 		- http://www.ador.state.al.us/Withholding/index.html *Withholding Tax Tables and Instructions
CHECKAGAIN:04-Jan-17			'OH' => 'Ohio',			- http://www.tax.ohio.gov/employer_withholding.aspx *Withholding Tables/Income Tax Withholding Instructions
CHECKAGAIN:04-Jan-17*NYC NOCHG 'NY' => 'New York',		- http://www.tax.ny.gov/forms/withholding_cur_forms.htm *WATCH NYS=New York State, NYC=New York City. NYS-50-T.1 or .2
CHECKAGAIN:04-Jan-17			'MA' => 'Massachusetts' - http://www.mass.gov/dor/individuals/taxpayer-help-and-resources/tax-guides/withholding-tax-guide.html#calculate *Circular M

//Change less often
NOCHANGE:16-Dec-16				'MD' => 'Maryland',		- http://business.marylandtaxes.com/taxinfo/withholding/default.asp *Maryland Withholding Guide* - Use 1.25% LOCAL INCOME TAX tables, *minus 1.25%*, manually calculate each bracket.  **PAY ATTENTION TO FILING STATUS AND WHICH SIDE ITS ON** Use tax_table_bracket_calculator.ods. See MD.class.php for more information.
NOCHANGE:04-Jan-17				'DE' => 'Delaware',		- http://revenue.delaware.gov/services/WITBk.shtml *http://revenue.delaware.gov/services/wit_folder/section17.shtml
NOCHANGE:04-Jan-17				'GA' => 'Georgia',		- http://dor.georgia.gov/withholding-tax-information or http://dor.georgia.gov/documents/2016-employer-tax-guide *Employers Tax Guide
CHECKAGAIN:04-Jan-17			'HI' => 'Hawaii',		- http://tax.hawaii.gov/forms/a1_b1_5whhold/ *Employers Tax Guide (Booklet A)
CHANGED:18-Dec-16				'NE' => 'Nebraska',		- http://www.revenue.nebraska.gov/withhold.html *Nebraska  Circular EN, Income Tax Withholding on Wages
NOCHANGE:04-Jan-17				'WI' => 'Wisconsin',	- http://www.revenue.wi.gov/forms/with/index.html *Pub W-166, Method "B" calculation
NOCHANGE:04-Jan-17				'DC' => 'D.C.', 		- http://otr.cfo.dc.gov/page/income-tax-withholding-instructions-and-tables *Form: FR-230

//Rarely change
CHECKAGAIN:18-Dec-16			'UT' => 'Utah',			- http://tax.utah.gov/withholding *PUB 14, Withholding Tax Guide
CHECKAGAIN:18-Dec-16			'AR' => 'Arkansas'		- http://www.dfa.arkansas.gov/offices/incomeTax/withholding/Pages/withholdingForms.aspx *Witholding Tax Formula ***They use a minus calculation, so we have to manually calculate each bracket ourselves. Use tax_table_bracket_calculator.ods
CHANGED:18-Dec-16				'IN' => 'Indiana',		- http://www.in.gov/dor/4006.htm#withholding *Departmental Notice #1 DN01
NOCHANGE:18-Dec-16				'IA' => 'Iowa',			- https://tax.iowa.gov/withholding-tax-information-0 *Iowa Withholding Tax Guide
CHECKAGAIN:18-Dec-16			'LA' => 'Louisiana',	- http://revenue.louisiana.gov/WithholdingTax *R-1306
CHECKAGAIN:18-Dec-16			'MS' => 'Mississippi',	- http://www.dor.ms.gov/Business/Pages/Withholding-Tax.aspx *Pub 89-700
CHECKAGAIN:18-Dec-16			'MT' => 'Montana',		- https://revenue.mt.gov/home/businesses/wage_withholding#Filing-Payment-Information-723 *Montana Witholding Tax Guide
CHECKAGAIN:18-Dec-16			'NJ' => 'New Jersey',	- http://www.state.nj.us/treasury/taxation/freqqite.shtml *Withholding Rate Tables
CHECKAGAIN:18-Dec-16			'PA' => 'Pennsylvania', - http://www.revenue.pa.gov/GeneralTaxInformation/Tax%20Types%20and%20Information/Pages/Employer-Withholding.aspx#.VJ3CWAuA *Rev 415 - Employer Withholding Information
CHANGED:18-Dec-16				'SC' => 'South Carolina'- https://dor.sc.gov/tax/withholding/forms *Formula for Computing SC Withholding Tax WH-1603F
CHECKAGAIN:18-Dec-16			'VA' => 'Virginia',		- http://www.tax.virginia.gov/content/withholding-tax *Employer Withholding Instructions
CHECKAGAIN:18-Dec-16			'WV' => 'West Virginia',- http://tax.wv.gov/Business/Withholding/Pages/WithholdingTaxForms.aspx *IT-100.1A
CHECKAGAIN:18-Dec-15			'AZ' => 'Arizona',		- http://www.azdor.gov/Forms/Withholding.aspx *Form A4: Employees choose a straight percent to pick.

	'AK' => 'Alaska',		- NO STATE TAXES
	'FL' => 'Florida',		- NO STATE TAXES
	'NV' => 'Nevada',		- NO STATE TAXES
	'NH' => 'New Hampshire' - NO STATE TAXES
	'SD' => 'South Dakota',	- NO STATE TAXES
	'TN' => 'Tennessee',	- NO STATE TAXES
	'TX' => 'Texas',		- NO STATE TAXES
	'WA' => 'Washington',	- NO STATE TAXES
	'WY' => 'Wyoming'		- NO STATE TAXES

*/

/**
 * @package PayrollDeduction\US
 */
class PayrollDeduction_US_Data extends PayrollDeduction_Base {
	var $db = NULL;
	var $income_tax_rates = array();
	var $country_primary_currency = 'USD';

	var $federal_allowance = array(
									//01-Jan-17 - No Change.
									20160101 => 4050.00, //01-Jan-16
									20150101 => 4000.00, //01-Jan-15
									20140101 => 3950.00, //01-Jan-14
									20130101 => 3900.00, //01-Jan-13
									20120101 => 3800.00, //01-Jan-12
									20110101 => 3700.00, //01-Jan-11
									//01-Jan-10 - No Change
									20090101 => 3650.00, //01-Jan-09
									20080101 => 3500.00, //01-Jan-08
									20070101 => 3400.00, //01-Jan-07
									20060101 => 3300.00  //01-Jan-06
								);

	//http://www.ssa.gov/pressoffice/factsheets/colafacts2013.htm
	var $social_security_options = array(
									20170101 => array( //2017
													   'maximum_earnings' => 127200,
													   'employee_rate' => 6.2,
													   'employer_rate' => 6.2,
													   //'employee_maximum_contribution' => 7254.00 //Employee
													),
									20150101 => array( //2015
														'maximum_earnings' => 118500,
														'employee_rate' => 6.2,
														'employer_rate' => 6.2,
														//'employee_maximum_contribution' => 7254.00 //Employee
													),
									20140101 => array( //2014
														'maximum_earnings' => 117000,
														'employee_rate' => 6.2,
														'employer_rate' => 6.2,
														//'employee_maximum_contribution' => 7254.00 //Employee
													),
									20130101 => array( //2013
														'maximum_earnings' => 113700,
														'employee_rate' => 6.2,
														'employer_rate' => 6.2,
														//'employee_maximum_contribution' => 7049.40 //Employee
													),
									20120101 => array( //2012
														'maximum_earnings' => 110100,
														'employee_rate' => 4.2,
														'employer_rate' => 6.2,
														//'maximum_contribution' => 4624.20 //Employee
													),
									20110101 => array( //2011 - Employer is still 6.2%
														'maximum_earnings' => 106800,
														'employee_rate' => 4.2,
														'employer_rate' => 6.2,
														//'maximum_contribution' => 4485.60 //Employee
													),
									//2010 - No Change.
									20090101 => array( //2009
														'maximum_earnings' => 106800,
														'employee_rate' => 6.2,
														'employer_rate' => 6.2,
														//'maximum_contribution' => 6621.60 //Employee
													),
									20080101 => array( //2008
														'maximum_earnings' => 102000,
														'employee_rate' => 6.2,
														'employer_rate' => 6.2,
														//'maximum_contribution' => 6324.00 //Employee
													),
									20070101 => array( //2007
														'maximum_earnings' => 97500,
														'employee_rate' => 6.2,
														'employer_rate' => 6.2,
														//'maximum_contribution' => 6045.00 //Employee
													),
									20060101 => array( //2006
														'maximum_earnings' => 94200,
														'employee_rate' => 6.2,
														'employer_rate' => 6.2,
														//'maximum_contribution' => 5840.40 //Employee
													)
								);

	var $federal_ui_options = array(
									20110701 => array( //2011 (July 1st)
														'maximum_earnings' => 7000,
														'rate' => 6.0,
														'minimum_rate' => 0.6,
													),
									20060101 => array( //2006
														'maximum_earnings' => 7000,
														'rate' => 6.2,
														'minimum_rate' => 0.8,
													)
								);

	var $medicare_options = array(
									//No changes in 2015.
									20140101 => array( //2014
														'employee_rate' => 1.45,
														'employee_threshold_rate' => 0.90,
														'employee_threshold' => array(
																					10 => 200000, //Single
																					20 => 125000, //Married - Without Spouse Filing
																					30 => 250000, //Married - With Spouse Filing
																					),
														'employer_rate' => 1.45,
														'employer_threshold' => 200000, //Threshold for Form 941
														),
									20130101 => array( //2013
														'employee_rate' => 1.45,
														'employee_threshold_rate' => 0.90,
														'employee_threshold' => array(
																					10 => 200000, //Single
																					20 => 125000, //Married - Without Spouse Filing
																					30 => 250000, //Married - With Spouse Filing
																					),
														'employer_rate' => 1.45,
														'employer_threshold' => 200000, //Threshold for Form 941
														),
									20060101 => array( //2006
														'employee_rate' => 1.45,
														'employee_threshold_rate' => 0,
														'employee_threshold' => array(
																					10 => 0, //Single
																					20 => 0, //Married - Without Spouse Filing
																					30 => 0, //Married - With Spouse Filing
																					),
														'employer_rate' => 1.45,
														'employer_threshold' => 0, //Threshold for Form 941
														),
								);

	/*
		10 => 'Single or HOH',
		20 => 'Married Without Spouse Filing',
		30 => 'Married With Spouse Filing',

		Calculation type is:
			10 = Percent
			20 = Amount
			30 = Amount less Percent of wages in excess.

		Wage Base is the maximum wage that is eligible for EIC.

	*/
	var $eic_options = array(
								20100101 => array( //01-Jan-10
													10 => array(
																array( 'income' => 8970,  'calculation_type' => 10, 'percent' => 20.40 ),
																array( 'income' => 16450, 'calculation_type' => 20, 'amount' => 1830 ),
																array( 'income' => 16450, 'calculation_type' => 30, 'amount' => 1830, 'percent' => 9.588  ),
																),
													20 => array(
																array( 'income' => 8970,  'calculation_type' => 10, 'percent' => 20.40 ),
																array( 'income' => 21460, 'calculation_type' => 20, 'amount' => 1830 ),
																array( 'income' => 21460, 'calculation_type' => 30, 'amount' => 1830, 'percent' => 9.588  ),
																),
													30 => array(
																array( 'income' => 4485,  'calculation_type' => 10, 'percent' => 20.40 ),
																array( 'income' => 10730, 'calculation_type' => 20, 'amount' => 915 ),
																array( 'income' => 10730, 'calculation_type' => 30, 'amount' => 915, 'percent' => 9.588  ),
																),
													),
								20090401 => array( //01-Apr-09
													10 => array(
																array( 'income' => 8950,  'calculation_type' => 10, 'percent' => 20.40 ),
																array( 'income' => 16420, 'calculation_type' => 20, 'amount' => 1826 ),
																array( 'income' => 16420, 'calculation_type' => 30, 'amount' => 1826, 'percent' => 9.588  ),
																),
													20 => array(
																array( 'income' => 8950,  'calculation_type' => 10, 'percent' => 20.40 ),
																array( 'income' => 21420, 'calculation_type' => 20, 'amount' => 1826 ),
																array( 'income' => 21420, 'calculation_type' => 30, 'amount' => 1826, 'percent' => 9.588  ),
																),
													30 => array(
																array( 'income' => 4475, 'calculation_type' => 10, 'percent' => 20.40 ),
																array( 'income' => 10710, 'calculation_type' => 20, 'amount' => 913 ),
																array( 'income' => 10710, 'calculation_type' => 30, 'amount' => 913, 'percent' => 9.588  ),
																),
													),
								20080101 => array( //01-Jan-08
													10 => array(
																array( 'income' => 8580,  'calculation_type' => 10, 'percent' => 20.40 ),
																array( 'income' => 15740, 'calculation_type' => 20, 'amount' => 1750 ),
																array( 'income' => 15740, 'calculation_type' => 30, 'amount' => 1750, 'percent' => 9.588  ),
																),
													20 => array(
																array( 'income' => 8580,  'calculation_type' => 10, 'percent' => 20.40 ),
																array( 'income' => 18740, 'calculation_type' => 20, 'amount' => 1750 ),
																array( 'income' => 18740, 'calculation_type' => 30, 'amount' => 1750, 'percent' => 9.588  ),
																),
													30 => array(
																array( 'income' => 4290, 'calculation_type' => 10, 'percent' => 20.40 ),
																array( 'income' => 9370, 'calculation_type' => 20, 'amount' => 875 ),
																array( 'income' => 9370, 'calculation_type' => 30, 'amount' => 875, 'percent' => 9.588  ),
																),
													),
							);

	/*
		Federal Income Tax Rates
	*/
	var $federal_income_tax_rate_options = array(
													20170101 => array(
																10 => array(
																		array( 'income' => 2300,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 11625,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 40250,	'rate' => 15,	'constant' => 932.50 ),
																		array( 'income' => 94200,	'rate' => 25,	'constant' => 5226.25 ),
																		array( 'income' => 193950,	'rate' => 28,	'constant' => 18713.75 ),
																		array( 'income' => 419000,	'rate' => 33,	'constant' => 46643.75 ),
																		array( 'income' => 420700,	'rate' => 35,	'constant' => 120910.25 ),
																		array( 'income' => 420700,	'rate' => 39.6,	'constant' => 121505.25 ),
																		),
																20 => array(
																		array( 'income' => 8650,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 27300,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 84550,	'rate' => 15,	'constant' => 1865.00 ),
																		array( 'income' => 161750,	'rate' => 25,	'constant' => 10452.50 ),
																		array( 'income' => 242000,	'rate' => 28,	'constant' => 29752.50 ),
																		array( 'income' => 425350,	'rate' => 33,	'constant' => 52222.50 ),
																		array( 'income' => 479350,	'rate' => 35,	'constant' => 112728.00 ),
																		array( 'income' => 479350,	'rate' => 39.6,	'constant' => 131628.00 ),
																		),
																),
													20160101 => array(
																10 => array(
																		array( 'income' => 2250,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 11525,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 39900,	'rate' => 15,	'constant' => 927.50 ),
																		array( 'income' => 93400,	'rate' => 25,	'constant' => 5183.75 ),
																		array( 'income' => 192400,	'rate' => 28,	'constant' => 18558.75 ),
																		array( 'income' => 415600,	'rate' => 33,	'constant' => 46278.75 ),
																		array( 'income' => 417300,	'rate' => 35,	'constant' => 119934.75 ),
																		array( 'income' => 417300,	'rate' => 39.6,	'constant' => 120529.75 ),
																		),
																20 => array(
																		array( 'income' => 8550,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 27100,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 83850,	'rate' => 15,	'constant' => 1855.00 ),
																		array( 'income' => 160450,	'rate' => 25,	'constant' => 10367.50 ),
																		array( 'income' => 240000,	'rate' => 28,	'constant' => 29517.50 ),
																		array( 'income' => 421900,	'rate' => 33,	'constant' => 51791.50 ),
																		array( 'income' => 475500,	'rate' => 35,	'constant' => 111818.50 ),
																		array( 'income' => 475500,	'rate' => 39.6,	'constant' => 130578.50 ),
																		),
																),
													20150101 => array(
																10 => array(
																		array( 'income' => 2300,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 11525,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 39750,	'rate' => 15,	'constant' => 922.50 ),
																		array( 'income' => 93050,	'rate' => 25,	'constant' => 5156.25 ),
																		array( 'income' => 191600,	'rate' => 28,	'constant' => 18481.25 ),
																		array( 'income' => 413800,	'rate' => 33,	'constant' => 46075.25 ),
																		array( 'income' => 415500,	'rate' => 35,	'constant' => 119401.25 ),
																		array( 'income' => 415500,	'rate' => 39.6,	'constant' => 119996.25 ),
																		),
																20 => array(
																		array( 'income' => 8600,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 27050,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 83500,	'rate' => 15,	'constant' => 1845.00 ),
																		array( 'income' => 159800,	'rate' => 25,	'constant' => 10312.50 ),
																		array( 'income' => 239050,	'rate' => 28,	'constant' => 29387.50 ),
																		array( 'income' => 420100,	'rate' => 33,	'constant' => 51577.50 ),
																		array( 'income' => 473450,	'rate' => 35,	'constant' => 111324.00 ),
																		array( 'income' => 473450,	'rate' => 39.6,	'constant' => 129996.50 ),
																		),
																),
													20140101 => array(
																10 => array(
																		array( 'income' => 2250,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 11325,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 39150,	'rate' => 15,	'constant' => 907.50 ),
																		array( 'income' => 91600,	'rate' => 25,	'constant' => 5081.25 ),
																		array( 'income' => 188600,	'rate' => 28,	'constant' => 18193.75 ),
																		array( 'income' => 407350,	'rate' => 33,	'constant' => 45353.75 ),
																		array( 'income' => 409000,	'rate' => 35,	'constant' => 112683.50 ),
																		array( 'income' => 409000,	'rate' => 39.6,	'constant' => 118118.75 ),
																		),
																20 => array(
																		array( 'income' => 8450,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 26600,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 82250,	'rate' => 15,	'constant' => 1815.00 ),
																		array( 'income' => 157300,	'rate' => 25,	'constant' => 10162.50 ),
																		array( 'income' => 235300,	'rate' => 28,	'constant' => 28925.00 ),
																		array( 'income' => 413550,	'rate' => 33,	'constant' => 50765.00 ),
																		array( 'income' => 466050,	'rate' => 35,	'constant' => 109587.50 ),
																		array( 'income' => 466050,	'rate' => 39.6,	'constant' => 127962.50 ),
																		),
																),
													20130101 => array(
																10 => array(
																		array( 'income' => 2200,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 11125,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 38450,	'rate' => 15,	'constant' => 892.50 ),
																		array( 'income' => 90050,	'rate' => 25,	'constant' => 4991.25 ),
																		array( 'income' => 185450,	'rate' => 28,	'constant' => 17891.25 ),
																		array( 'income' => 400550,	'rate' => 33,	'constant' => 44603.25 ),
																		array( 'income' => 402200,	'rate' => 35,	'constant' => 115586.25 ),
																		array( 'income' => 402200,	'rate' => 39.6,	'constant' => 116163.75 ),
																		),
																20 => array(
																		array( 'income' => 8300,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 26150,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 80800,	'rate' => 15,	'constant' => 1785.00 ),
																		array( 'income' => 154700,	'rate' => 25,	'constant' => 9982.50 ),
																		array( 'income' => 231350,	'rate' => 28,	'constant' => 28457.50 ),
																		array( 'income' => 406650,	'rate' => 33,	'constant' => 49919.50 ),
																		array( 'income' => 458300,	'rate' => 35,	'constant' => 107768.50 ),
																		array( 'income' => 458300,	'rate' => 39.6,	'constant' => 125846.00 ),
																		),
																),
													20120101 => array(
																10 => array(
																		array( 'income' => 2150,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 10850,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 37500,	'rate' => 15,	'constant' => 870.00 ),
																		array( 'income' => 87800,	'rate' => 25,	'constant' => 4867.50 ),
																		array( 'income' => 180800,	'rate' => 28,	'constant' => 17442.50 ),
																		array( 'income' => 390500,	'rate' => 33,	'constant' => 43482.50 ),
																		array( 'income' => 390500,	'rate' => 35,	'constant' => 112683.50 ),
																		),
																20 => array(
																		array( 'income' => 8100,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 25500,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 78800,	'rate' => 15,	'constant' => 1740.00 ),
																		array( 'income' => 150800,	'rate' => 25,	'constant' => 9735.00 ),
																		array( 'income' => 225550,	'rate' => 28,	'constant' => 27735.00 ),
																		array( 'income' => 396450,	'rate' => 33,	'constant' => 48665.00 ),
																		array( 'income' => 396450,	'rate' => 35,	'constant' => 105062.00 ),
																		),
																),
													20110101 => array(
																10 => array(
																		array( 'income' => 2100,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 10600,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 36600,	'rate' => 15,	'constant' => 850.00 ),
																		array( 'income' => 85700,	'rate' => 25,	'constant' => 4750.00 ),
																		array( 'income' => 176500,	'rate' => 28,	'constant' => 17025.00 ),
																		array( 'income' => 381250,	'rate' => 33,	'constant' => 42449.00 ),
																		array( 'income' => 381250,	'rate' => 35,	'constant' => 110016.50 ),
																		),
																20 => array(
																		array( 'income' => 7900,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 24900,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 76900,	'rate' => 15,	'constant' => 1700.00 ),
																		array( 'income' => 147250,	'rate' => 25,	'constant' => 9500.00 ),
																		array( 'income' => 220200,	'rate' => 28,	'constant' => 27087.50 ),
																		array( 'income' => 387050,	'rate' => 33,	'constant' => 47513.50 ),
																		array( 'income' => 387050,	'rate' => 35,	'constant' => 102574.00 ),
																		),
																),
													20100101 => array(
																10 => array(
																		array( 'income' => 6050,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 10425,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 36050,	'rate' => 15,	'constant' => 437.50 ),
																		array( 'income' => 67700,	'rate' => 25,	'constant' => 4281.25 ),
																		array( 'income' => 84450,	'rate' => 27,	'constant' => 12193.75 ),
																		array( 'income' => 87700,	'rate' => 30,	'constant' => 16716.25 ),
																		array( 'income' => 173900,	'rate' => 28,	'constant' => 17691.25 ),
																		array( 'income' => 375700,	'rate' => 33,	'constant' => 41827.25 ),
																		array( 'income' => 375700,	'rate' => 35,	'constant' => 108421.25 ),
																		),
																20 => array(
																		array( 'income' => 13750,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 24500,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 75750,	'rate' => 15,	'constant' => 1075.00 ),
																		array( 'income' => 94050,	'rate' => 25,	'constant' => 8762.50 ),
																		array( 'income' => 124050,	'rate' => 27,	'constant' => 13337.50 ),
																		array( 'income' => 145050,	'rate' => 25,	'constant' => 21437.50 ),
																		array( 'income' => 217000,	'rate' => 28,	'constant' => 26687.50 ),
																		array( 'income' => 381400,	'rate' => 33,	'constant' => 46833.50 ),
																		array( 'income' => 381400,	'rate' => 35,	'constant' => 101085.50 ),
																		),
																),
													20090401 => array(
																10 => array(
																		array( 'income' => 7180,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 10400,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 36200,	'rate' => 15,	'constant' => 322 ),
																		array( 'income' => 66530,	'rate' => 25,	'constant' => 4192 ),
																		array( 'income' => 173600,	'rate' => 28,	'constant' => 11774.50 ),
																		array( 'income' => 375000,	'rate' => 33,	'constant' => 41754.10 ),
																		array( 'income' => 375000,	'rate' => 35,	'constant' => 108216.10 ),
																		),
																20 => array(
																		array( 'income' => 15750,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 24450,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 75650,	'rate' => 15,	'constant' => 870 ),
																		array( 'income' => 118130,	'rate' => 25,	'constant' => 8550 ),
																		array( 'income' => 216600,	'rate' => 28,	'constant' => 19170 ),
																		array( 'income' => 380700,	'rate' => 33,	'constant' => 46741.60 ),
																		array( 'income' => 380700,	'rate' => 35,	'constant' => 100894.60 ),
																		),
																),
													20090101 => array(
																10 => array(
																		array( 'income' => 2650,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 10400,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 35400,	'rate' => 15,	'constant' => 775 ),
																		array( 'income' => 84300,	'rate' => 25,	'constant' => 4525 ),
																		array( 'income' => 173600,	'rate' => 28,	'constant' => 16750 ),
																		array( 'income' => 375000,	'rate' => 33,	'constant' => 41754 ),
																		array( 'income' => 375000,	'rate' => 35,	'constant' => 108216 ),
																		),
																20 => array(
																		array( 'income' => 8000,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 23950,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 75650,	'rate' => 15,	'constant' => 1595 ),
																		array( 'income' => 144800,	'rate' => 25,	'constant' => 9350 ),
																		array( 'income' => 216600,	'rate' => 28,	'constant' => 26637.50 ),
																		array( 'income' => 380700,	'rate' => 33,	'constant' => 46741.50 ),
																		array( 'income' => 380700,	'rate' => 35,	'constant' => 100894.50 ),
																		),
																),
													20080101 => array(
																10 => array(
																		array( 'income' => 2650,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 10300,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 33960,	'rate' => 15,	'constant' => 765.00 ),
																		array( 'income' => 79725,	'rate' => 25,	'constant' => 4314.00 ),
																		array( 'income' => 166500,	'rate' => 28,	'constant' => 15755.25 ),
																		array( 'income' => 359650,	'rate' => 33,	'constant' => 4052.25 ),
																		array( 'income' => 359650,	'rate' => 35,	'constant' => 103791.75 ),
																		),
																20 => array(
																		array( 'income' => 8000,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 23550,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 72150,	'rate' => 15,	'constant' => 1555.00 ),
																		array( 'income' => 137850,	'rate' => 25,	'constant' => 8845.00 ),
																		array( 'income' => 207700,	'rate' => 28,	'constant' => 25270.00 ),
																		array( 'income' => 365100,	'rate' => 33,	'constant' => 44828.00 ),
																		array( 'income' => 365100,	'rate' => 35,	'constant' => 96770.00 ),
																		),
																),
													20070101 => array(
																10 => array(
																		array( 'income' => 2650,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 10120,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 33520,	'rate' => 15,	'constant' => 747 ),
																		array( 'income' => 77075,	'rate' => 25,	'constant' => 4257 ),
																		array( 'income' => 162800,	'rate' => 28,	'constant' => 15145.75 ),
																		array( 'income' => 351650,	'rate' => 33,	'constant' => 39148.75 ),
																		array( 'income' => 351650,	'rate' => 35,	'constant' => 101469.25 ),
																		),
																20 => array(
																		array( 'income' => 8000,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 23350,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 70700,	'rate' => 15,	'constant' => 1535 ),
																		array( 'income' => 133800,	'rate' => 25,	'constant' => 8637.50 ),
																		array( 'income' => 203150,	'rate' => 28,	'constant' => 24412.50 ),
																		array( 'income' => 357000,	'rate' => 33,	'constant' => 43830 ),
																		array( 'income' => 357000,	'rate' => 35,	'constant' => 94601 ),
																		),
																),
													20060101 => array(
																10 => array(
																		array( 'income' => 2650,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 10000,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 32240,	'rate' => 15,	'constant' => 735 ),
																		array( 'income' => 73250,	'rate' => 25,	'constant' => 4071 ),
																		array( 'income' => 156650,	'rate' => 28,	'constant' => 14323.50 ),
																		array( 'income' => 338400,	'rate' => 33,	'constant' => 37675.50 ),
																		array( 'income' => 338400,	'rate' => 35,	'constant' => 97653 ),
																		),
																20 => array(
																		array( 'income' => 8000,	'rate' => 0,	'constant' => 0 ),
																		array( 'income' => 22900,	'rate' => 10,	'constant' => 0 ),
																		array( 'income' => 68040,	'rate' => 15,	'constant' => 1490 ),
																		array( 'income' => 126900,	'rate' => 25,	'constant' => 8261 ),
																		array( 'income' => 195450,	'rate' => 28,	'constant' => 22976 ),
																		array( 'income' => 343550,	'rate' => 33,	'constant' => 42170 ),
																		array( 'income' => 343550,	'rate' => 35,	'constant' => 91043 ),
																	),
																),
													);

	function __construct() {
		global $db;

		$this->db = $db;

		return TRUE;
	}

	function getData() {
		global $cache;

		$country = $this->getCountry();
		$state = $this->getProvince();
		$district = $this->getDistrict();
		$epoch = $this->getDate();

		$federal_status = $this->getFederalFilingStatus();
		if ( empty($federal_status) ) {
			$federal_status = 10;
		}
		$state_status = $this->getStateFilingStatus();
		if ( empty($state_status) ) {
			$state_status = 10;
		}
		$district_status = $this->getDistrictFilingStatus();

		if ( $epoch == NULL OR $epoch == '' ) {
			$epoch = $this->getISODate( TTDate::getTime() );
		}

		Debug::text('Using ('. $state .'/'. $district .') values from: '. TTDate::getDate('DATE+TIME', $this->getDateEpoch($epoch) ) .' Status: State: '. $state_status, __FILE__, __LINE__, __METHOD__, 10);

		$this->income_tax_rates = FALSE;
		if ( isset($this->federal_income_tax_rate_options) AND count($this->federal_income_tax_rate_options) > 0 ) {
			$prev_income = 0;
			$prev_rate = 0;
			$prev_constant = 0;

			$federal_income_tax_rate_options = $this->getDataFromRateArray($epoch, $this->federal_income_tax_rate_options );
			if ( !isset($federal_income_tax_rate_options[$federal_status]) AND isset($federal_income_tax_rate_options[0]) ) {
				$federal_status = 0;
			}

			if ( isset($federal_income_tax_rate_options[$federal_status]) ) {
				foreach( $federal_income_tax_rate_options[$federal_status] as $data ) {
					$this->income_tax_rates['federal'][] = array(
															'prev_income' => $prev_income,
															'income' => $data['income'],
															'prev_rate' => ( $prev_rate / 100 ),
															'rate' => ( $data['rate'] / 100 ),
															'prev_constant' => $prev_constant,
															'constant' => $data['constant']
															);

					$prev_income = $data['income'];
					$prev_rate = $data['rate'];
					$prev_constant = $data['constant'];
				}
			}
			unset($prev_income, $prev_rate, $prev_constant, $data, $federal_income_tax_rate_options);
		}

		if ( isset($this->state_income_tax_rate_options) AND count($this->state_income_tax_rate_options) > 0 ) {
			$prev_income = 0;
			$prev_rate = 0;
			$prev_constant = 0;

			$state_income_tax_rate_options = $this->getDataFromRateArray($epoch, $this->state_income_tax_rate_options );
			if ( !isset($state_income_tax_rate_options[$state_status]) AND isset($state_income_tax_rate_options[0]) ) {
				$state_status = 0;
			}

			if ( isset($state_income_tax_rate_options[$state_status]) ) {
				foreach( $state_income_tax_rate_options[$state_status] as $data ) {
					$this->income_tax_rates['state'][] = array(
															'prev_income' => $prev_income,
															'income' => $data['income'],
															'prev_rate' => ( $prev_rate / 100 ),
															'rate' => ( $data['rate'] / 100 ),
															'prev_constant' => $prev_constant,
															'constant' => $data['constant']
															);

					$prev_income = $data['income'];
					$prev_rate = $data['rate'];
					$prev_constant = $data['constant'];
				}
			}
			unset($prev_income, $prev_rate, $prev_constant, $data, $state_income_tax_rate_options);
		}

		if ( isset($this->district_income_tax_rate_options) AND count($this->district_income_tax_rate_options) > 0 ) {
			$prev_income = 0;
			$prev_rate = 0;
			$prev_constant = 0;

			$district_income_tax_rate_options = $this->getDataFromRateArray($epoch, $this->district_income_tax_rate_options );
			if ( !isset($district_income_tax_rate_options[$district_status]) AND isset($district_income_tax_rate_options[0]) ) {
				$district_status = 0;
			}

			if ( isset($district_income_tax_rate_options[$district_status]) ) {
				foreach( $district_income_tax_rate_options[$district_status] as $data ) {
					$this->income_tax_rates['district'][] = array(
															'prev_income' => $prev_income,
															'income' => $data['income'],
															'prev_rate' => ( $prev_rate / 100 ),
															'rate' => ( $data['rate'] / 100 ),
															'prev_constant' => $prev_constant,
															'constant' => $data['constant']
															);

					$prev_income = $data['income'];
					$prev_rate = $data['rate'];
					$prev_constant = $data['constant'];
				}
			}
			unset($prev_income, $prev_rate, $prev_constant, $district_income_tax_rate_options);
		}

		if ( isset($this->income_tax_rates) AND is_array($this->income_tax_rates) ) {
			foreach( $this->income_tax_rates as $type => $brackets ) {
				$i = 0;
				$total_brackets = ( count($brackets) - 1 );
				foreach( $brackets as $key => $bracket_data ) {
					if ( $i == 0 ) {
						$first = TRUE;
					} else {
						$first = FALSE;
					}

					if ( $i == $total_brackets ) {
						$last = TRUE;
					} else {
						$last = FALSE;
					}

					$this->income_tax_rates[$type][$key]['first'] = $first;
					$this->income_tax_rates[$type][$key]['last'] = $last;

					$i++;
				}
			}
		}

		//Debug::Arr($this->income_tax_rates, 'Income Tax Rates: ', __FILE__, __LINE__, __METHOD__, 10);
		return $this;
	}

	function getRateArray($income, $type) {
		Debug::text('Calculating '. $type .' Taxes on: $'. $income, __FILE__, __LINE__, __METHOD__, 10);

		if ( isset($this->income_tax_rates[$type]) ) {
			$rates = $this->income_tax_rates[$type];
		} else {
			Debug::text('aNO INCOME TAX RATES FOUND!!!!!! '. $type .' Taxes on: $'. $income, __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ( count($rates) == 0 ) {
			Debug::text('bNO INCOME TAX RATES FOUND!!!!!! '. $type .' Taxes on: $'. $income, __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$prev_value = 0;
		$total_rates = ( count($rates) - 1 );
		$i = 0;
		foreach ($rates as $key => $values) {
			$value = $values['income'];
			$rate = $values['rate'];
			$constant = $values['constant'];

			//Debug::text('Key: '. $key .' Value: '. $value .' Rate: '. $rate .' Constant: '. $constant .' Previous Value: '. $prev_value , __FILE__, __LINE__, __METHOD__, 10);

			if ($income > $prev_value AND $income <= $value) {
				//Debug::text('Found Key: '. $key, __FILE__, __LINE__, __METHOD__, 10);

				return $this->income_tax_rates[$type][$key];
			} elseif ($i == $total_rates) {
				//Debug::text('Found Last Key: '. $key, __FILE__, __LINE__, __METHOD__, 10);
				return $this->income_tax_rates[$type][$key];
			}

			$prev_value = $value;
			$i++;
		}

		return FALSE;
	}

	function getEICRateArray( $income, $type ) {
		Debug::text('Calculating '. $type .' Taxes on: $'. $income, __FILE__, __LINE__, __METHOD__, 10);

		$eic_options = $this->getDataFromRateArray( $this->getDate(), $this->eic_options);
		if ( $eic_options == FALSE ) {
			Debug::text('aNO INCOME TAX RATES FOUND!!!!!! '. $type .' Taxes on: $'. $income, __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ( isset($eic_options[$type]) ) {
			$rates = $eic_options[$type];
		} else {
			Debug::text('bNO INCOME TAX RATES FOUND!!!!!! '. $type .' Taxes on: $'. $income, __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ( count($rates) == 0 ) {
			Debug::text('cNO INCOME TAX RATES FOUND!!!!!! '. $type .' Taxes on: $'. $income, __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$prev_value = 0;
		$total_rates = ( count($rates) - 1 );
		$i = 0;
		foreach ($rates as $key => $values) {
			$value = $values['income'];

			//Debug::text('Key: '. $key .' Income: '. $value , __FILE__, __LINE__, __METHOD__, 10);

			if ($income > $prev_value AND $income <= $value) {
				//Debug::text('Found Key: '. $key, __FILE__, __LINE__, __METHOD__, 10);
				return $eic_options[$type][$key];
			} elseif ($i == $total_rates) {
				//Debug::text('Found Last Key: '. $key, __FILE__, __LINE__, __METHOD__, 10);
				return $eic_options[$type][$key];
			}

			$prev_value = $value;
			$i++;
		}

		return FALSE;

	}

	function getFederalRate($income) {
		$arr = $this->getRateArray($income, 'federal');
		Debug::text('Federal Rate: '. $arr['rate'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['rate'];
	}

	function getFederalPreviousRate($income) {
		$arr = $this->getRateArray($income, 'federal');
		Debug::text('Federal Previous Rate: '. $arr['prev_rate'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['prev_rate'];
	}

	function getFederalRatePreviousIncome($income) {
		$arr = $this->getRateArray($income, 'federal');
		Debug::text('Federal Rate Previous Income: '. $arr['prev_income'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['prev_income'];
	}

	function getFederalRateIncome($income) {
		$arr = $this->getRateArray($income, 'federal');
		Debug::text('Federal Rate Income: '. $arr['income'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['income'];
	}

	function getFederalConstant($income) {
		$arr = $this->getRateArray($income, 'federal');
		Debug::text('Federal Constant: '. $arr['constant'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['constant'];
	}

	function getFederalAllowanceAmount($date) {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->federal_allowance);
		if ( $retarr != FALSE ) {
			return $retarr;
		}

		return FALSE;
	}


	function getStateRate($income) {
		$arr = $this->getRateArray($income, 'state');
		Debug::text('State Rate: '. $arr['rate'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['rate'];
	}

	function getStatePreviousRate($income) {
		$arr = $this->getRateArray($income, 'state');
		Debug::text('State Previous Rate: '. $arr['prev_rate'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['prev_rate'];
	}

	function getStateRatePreviousIncome($income) {
		$arr = $this->getRateArray($income, 'state');
		Debug::text('State Rate Previous Income: '. $arr['prev_income'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['prev_income'];
	}

	function getStateRateIncome($income) {
		$arr = $this->getRateArray($income, 'state');
		Debug::text('State Rate Income: '. $arr['income'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['income'];
	}

	function getStateConstant($income) {
		$arr = $this->getRateArray($income, 'state');
		Debug::text('State Constant: '. $arr['constant'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['constant'];
	}

	function getStatePreviousConstant($income) {
		$arr = $this->getRateArray($income, 'state');
		Debug::text('State Previous Constant: '. $arr['prev_constant'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['prev_constant'];
	}

	function getDistrictRate($income) {
		$arr = $this->getRateArray($income, 'district');
		Debug::text('District Rate: '. $arr['rate'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['rate'];
	}

	function getDistrictRatePreviousIncome($income) {
		$arr = $this->getRateArray($income, 'district');
		Debug::text('District Rate Previous Income: '. $arr['prev_income'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['prev_income'];
	}

	function getDistrictRateIncome($income) {
		$arr = $this->getRateArray($income, 'district');
		Debug::text('District Rate Income: '. $arr['income'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['income'];
	}

	function getDistrictConstant($income) {
		$arr = $this->getRateArray($income, 'district');
		Debug::text('District Constant: '. $arr['constant'], __FILE__, __LINE__, __METHOD__, 10);
		return $arr['constant'];
	}

	//Social Security
	function getSocialSecurityMaximumEarnings() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->social_security_options);
		if ( $retarr != FALSE ) {
			return $retarr['maximum_earnings'];
		}

		return FALSE;
	}

	function getSocialSecurityMaximumContribution( $type = 'employee' ) {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->social_security_options);
		if ( $retarr != FALSE ) {
			return bcmul( $this->getSocialSecurityMaximumEarnings(), bcdiv( $this->getSocialSecurityRate( $type ), 100 ) );
		}

		return FALSE;
	}

	function getSocialSecurityRate( $type = 'employee' ) {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->social_security_options);
		if ( $retarr != FALSE ) {
			return $retarr[$type.'_rate'];
		}

		return FALSE;
	}

	//Medicare
	function getMedicareRate() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->medicare_options);
		if ( $retarr != FALSE ) {
			return $retarr;
		}

		return FALSE;
	}
	function getMedicareAdditionalEmployerThreshold() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->medicare_options);
		if ( isset($retarr['employer_threshold']) ) {
			return $retarr['employer_threshold'];
		}

		return FALSE;
	}

	function getMedicareAdditionalThresholdRate() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->medicare_options);
		if ( isset($retarr['employee_threshold_rate']) ) {
			return $retarr['employee_threshold_rate'];
		}

		return FALSE;
	}


	//Federal UI
	function getFederalUIRate() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->federal_ui_options);
		if ( $retarr != FALSE ) {
			if ( $this->getStateUIRate() > bcsub( $retarr['rate'], $this->getFederalUIMinimumRate() ) ) {
				$retval = $this->getFederalUIMinimumRate();
			} else {
				$retval = ( $retarr['rate'] - $this->getStateUIRate() );
			}

			return $retval;
		}

		return FALSE;
	}

	function getFederalUIMinimumRate() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->federal_ui_options);
		if ( $retarr != FALSE ) {
			return $retarr['minimum_rate'];
		}

		return FALSE;
	}

	function getFederalUIMaximumEarnings() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->federal_ui_options);
		if ( $retarr != FALSE ) {
			return $retarr['maximum_earnings'];
		}

		return FALSE;
	}

	function getFederalUIMaximumContribution() {
		$retval = bcmul( $this->getFederalUIMaximumEarnings(), bcdiv( $this->getFederalUIRate(), 100 ) );
		if ( $retval != FALSE ) {
			return $retval;
		}

		return FALSE;
	}
}
?>
