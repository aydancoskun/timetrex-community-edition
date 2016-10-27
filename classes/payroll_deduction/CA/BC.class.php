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
 * @package PayrollDeduction\CA
 */
class PayrollDeduction_CA_BC extends PayrollDeduction_CA {
	var $provincial_income_tax_rate_options = array(
													20160101 => array(
																	array( 'income' => 38210,	'rate' => 5.06,	'constant' => 0 ),
																	array( 'income' => 76421,	'rate' => 7.7,	'constant' => 1009 ),
																	array( 'income' => 87741,	'rate' => 10.5,	'constant' => 3149 ),
																	array( 'income' => 106543,	'rate' => 12.29,'constant' => 4719 ),
																	array( 'income' => 106543,	'rate' => 14.7,	'constant' => 7287 ),
																),
													20150101 => array(
																	array( 'income' => 37869,	'rate' => 5.06,	'constant' => 0 ),
																	array( 'income' => 75740,	'rate' => 7.7,	'constant' => 1000 ),
																	array( 'income' => 86958,	'rate' => 10.5,	'constant' => 3120 ),
																	array( 'income' => 105592,	'rate' => 12.29,'constant' => 4677 ),
																	array( 'income' => 151050,	'rate' => 14.7,	'constant' => 7222 ),
																	array( 'income' => 151050,	'rate' => 16.8,	'constant' => 10394 ),
																),
													20140101 => array(
																	array( 'income' => 37606,	'rate' => 5.06,	'constant' => 0 ),
																	array( 'income' => 75213,	'rate' => 7.7,	'constant' => 993 ),
																	array( 'income' => 86354,	'rate' => 10.5,	'constant' => 3099 ),
																	array( 'income' => 104858,	'rate' => 12.29,'constant' => 4644 ),
																	array( 'income' => 150000,	'rate' => 14.7,	'constant' => 7172 ),
																	array( 'income' => 150000,	'rate' => 16.8,	'constant' => 10322 ),
																),
													20130101 => array(
																	array( 'income' => 37568,	'rate' => 5.06,	'constant' => 0 ),
																	array( 'income' => 75138,	'rate' => 7.7,	'constant' => 992 ),
																	array( 'income' => 86268,	'rate' => 10.5,	'constant' => 3096 ),
																	array( 'income' => 104754,	'rate' => 12.29,'constant' => 4640 ),
																	array( 'income' => 104754,	'rate' => 14.7,	'constant' => 7164 ),
																),
													20120101 => array(
																	array( 'income' => 37013,	'rate' => 5.06,	'constant' => 0 ),
																	array( 'income' => 74028,	'rate' => 7.7,	'constant' => 977 ),
																	array( 'income' => 84993,	'rate' => 10.5,	'constant' => 3050 ),
																	array( 'income' => 103205,	'rate' => 12.29,'constant' => 4571 ),
																	array( 'income' => 103205,	'rate' => 14.7,	'constant' => 7059 ),
																),
													20110101 => array(
																	array( 'income' => 36146,	'rate' => 5.06,	'constant' => 0 ),
																	array( 'income' => 72293,	'rate' => 7.7,	'constant' => 954 ),
																	array( 'income' => 83001,	'rate' => 10.5,	'constant' => 2978 ),
																	array( 'income' => 100787,	'rate' => 12.29,'constant' => 4464 ),
																	array( 'income' => 100787,	'rate' => 14.7,	'constant' => 6893 ),
																),
													20100101 => array(
																	array( 'income' => 35859,	'rate' => 5.06,	'constant' => 0 ),
																	array( 'income' => 71719,	'rate' => 7.7,	'constant' => 947 ),
																	array( 'income' => 82342,	'rate' => 10.5,	'constant' => 2955 ),
																	array( 'income' => 99987,	'rate' => 12.29,'constant' => 4429 ),
																	array( 'income' => 99987,	'rate' => 14.7,	'constant' => 6838 ),
																),
													20090101 => array(
																	array( 'income' => 35716,	'rate' => 5.06,	'constant' => 0 ),
																	array( 'income' => 71433,	'rate' => 7.7,	'constant' => 943 ),
																	array( 'income' => 82014,	'rate' => 10.5,	'constant' => 2943 ),
																	array( 'income' => 99588,	'rate' => 12.29,'constant' => 4411 ),
																	array( 'income' => 99588,	'rate' => 14.7,	'constant' => 6811 ),
																),
													20080701 => array(
																	array( 'income' => 35016,	'rate' => 5.13,	'constant' => 0 ),
																	array( 'income' => 70033,	'rate' => 7.81,	'constant' => 938 ),
																	array( 'income' => 80406,	'rate' => 10.50,'constant' => 2822 ),
																	array( 'income' => 97636,	'rate' => 12.29,'constant' => 4262 ),
																	array( 'income' => 97636,	'rate' => 14.70,'constant' => 6615 ),
																),
													20080101 => array(
																	array( 'income' => 35016,	'rate' => 5.35,	'constant' => 0 ),
																	array( 'income' => 70033,	'rate' => 8.15,	'constant' => 980 ),
																	array( 'income' => 80406,	'rate' => 10.50,'constant' => 2626 ),
																	array( 'income' => 97636,	'rate' => 12.29,'constant' => 4065 ),
																	array( 'income' => 97636,	'rate' => 14.70,'constant' => 6419 ),
																),
													20070701 => array(
																	array( 'income' => 34397,	'rate' => 5.35,	'constant' => 0 ),
																	array( 'income' => 68794,	'rate' => 8.15,	'constant' => 963 ),
																	array( 'income' => 78984,	'rate' => 10.5,	'constant' => 2580 ),
																	array( 'income' => 95909,	'rate' => 12.3,	'constant' => 4001 ),
																	array( 'income' => 95909,	'rate' => 14.7,	'constant' => 6303 ),
																),
													20070101 => array(
																	array( 'income' => 34397,	'rate' => 6.05,	'constant' => 0 ),
																	array( 'income' => 68794,	'rate' => 9.15,	'constant' => 1066 ),
																	array( 'income' => 78984,	'rate' => 11.70,'constant' => 2821 ),
																	array( 'income' => 95909,	'rate' => 13.70,'constant' => 4400 ),
																	array( 'income' => 95909,	'rate' => 14.70,'constant' => 5359 ),
																),
													20060101 => array(
																	array( 'income' => 33755,	'rate' => 6.05,	'constant' => 0 ),
																	array( 'income' => 67511,	'rate' => 9.15,	'constant' => 1046 ),
																	array( 'income' => 77511,	'rate' => 11.7,	'constant' => 2768 ),
																	array( 'income' => 94121,	'rate' => 13.7,	'constant' => 4318 ),
																	array( 'income' => 94121,	'rate' => 14.7,	'constant' => 5259 ),
																),
													20050701 => array(
																	array( 'income' => 33061,	'rate' => 6.05,	'constant' => 0 ),
																	array( 'income' => 66123,	'rate' => 9.15,	'constant' => 1007 ),
																	array( 'income' => 75917,	'rate' => 11.7,	'constant' => 2663 ),
																	array( 'income' => 92185,	'rate' => 13.7,	'constant' => 4155 ),
																	array( 'income' => 92185,	'rate' => 14.7,	'constant' => 5151 ),
																),
													20050101 => array(
																	array( 'income' => 33061,	'rate' => 6.05,	'constant' => 0 ),
																	array( 'income' => 66123,	'rate' => 9.15,	'constant' => 1025 ),
																	array( 'income' => 75917,	'rate' => 11.7,	'constant' => 2711 ),
																	array( 'income' => 92185,	'rate' => 13.7,	'constant' => 4229 ),
																	array( 'income' => 92185,	'rate' => 14.7,	'constant' => 5151 ),
																),
													20040101 => array(
																	array( 'income' => 32476,	'rate' => 6.05,	'constant' => 0 ),
																	array( 'income' => 64954,	'rate' => 9.15,	'constant' => 1007 ),
																	array( 'income' => 74575,	'rate' => 11.7,	'constant' => 2663 ),
																	array( 'income' => 90555,	'rate' => 13.7,	'constant' => 4155 ),
																	array( 'income' => 90555,	'rate' => 14.7,	'constant' => 5060 ),
																),
													20030101 => array(
																	array( 'income' => 32476,	'rate' => 6.05,	'constant' => 0 ),
																	array( 'income' => 64954,	'rate' => 9.15,	'constant' => 1007 ),
																	array( 'income' => 74575,	'rate' => 11.7,	'constant' => 2663 ),
																	array( 'income' => 90555,	'rate' => 13.7,	'constant' => 4155 ),
																	array( 'income' => 90555,	'rate' => 14.7,	'constant' => 5060 ),
																),
													20020101 => array(
																	array( 'income' => 32476,	'rate' => 6.05,	'constant' => 0 ),
																	array( 'income' => 64954,	'rate' => 9.15,	'constant' => 1007 ),
																	array( 'income' => 74575,	'rate' => 11.7,	'constant' => 2663 ),
																	array( 'income' => 90555,	'rate' => 13.7,	'constant' => 4155 ),
																	array( 'income' => 90555,	'rate' => 14.7,	'constant' => 5060 ),
																),
													20010101 => array(
																	array( 'income' => 32476,	'rate' => 6.05,	'constant' => 0 ),
																	array( 'income' => 64954,	'rate' => 9.15,	'constant' => 1007 ),
																	array( 'income' => 74575,	'rate' => 11.7,	'constant' => 2663 ),
																	array( 'income' => 90555,	'rate' => 13.7,	'constant' => 4155 ),
																	array( 'income' => 90555,	'rate' => 14.7,	'constant' => 5060 ),
																),
													);

	/*
		Provincial tax reduction
	*/
	var $provincial_tax_reduction_options = array(
													20160701 => array( //2016 (Jul 1)
																	   	'income1' => 19629,
																	   	'income2' => 31673.20,
																	   	'amount' => 436,
																	   	'rate' => 0.0362,
													),
													20160101 => array( //2016
																		'income1' => 19171,
																		'income2' => 31628.14,
																		'amount' => 436,
																		'rate' => 0.035,
																		),		
													20150701 => array( //2015 (Jul 1)
																		'income1' => 19673,
																		'income2' => 31567.74,
																		'amount' => 452,
																		'rate' => 0.038,
																		),
													20150101 => array( //2015
																		'income1' => 18327,
																		'income2' => 31202,
																		'amount' => 412,
																		'rate' => 0.032,
																		),
													20140101 => array( //2014
																		'income1' => 18200,
																		'income2' => 30981.25,
																		'amount' => 409,
																		'rate' => 0.032,
																		),
													20130101 => array( //2013
																		'income1' => 18181,
																		'income2' => 30962.25,
																		'amount' => 409,
																		'rate' => 0.032,
																		),
													20120101 => array( //2012
																		'income1' => 17913,
																		'income2' => 30506.75,
																		'amount' => 403,
																		'rate' => 0.032,
																		),
													20110101 => array( //2011
																		'income1' => 17493,
																		'income2' => 29805.50,
																		'amount' => 394,
																		'rate' => 0.032,
																),
													20100101 => array( //2010
																		'income1' => 17354,
																		'income2' => 29541.50,
																		'amount' => 390,
																		'rate' => 0.032,
																),
													20090101 => array( //2009
																		'income1' => 17285,
																		'income2' => 29441.25,
																		'amount' => 389,
																		'rate' => 0.032,
																),
													20080101 => array( //2008
																		'income1' => 16946,
																		'income2' => 28852.25,
																		'amount' => 381,
																		'rate' => 0.032,
																),
													20070701 => array( //2007 (July)
																		'income1' => 16646,
																		'income2' => 28364.75,
																		'amount' => 375,
																		'rate' => 0.032,
																),
													20070101	=> array( //2007 (Jan)
																		'income1' => 16646,
																		'income2' => 27062.67,
																		'amount' => 375,
																		'rate' => 0.032,
																),
													20060101 => array( //2006
																		'income1' => 16336,
																		'income2' => 26558.22,
																		'amount' => 368,
																		'rate' => 0.032,
																),
													20050101 => array( //2005
																		'income1' => 16000,
																		'income2' => 26000,
																		'amount' => 360,
																		'rate' => 0.032,
																),
													);

	function getProvincialTaxReduction() {
		$A = $this->getAnnualTaxableIncome();
		$T4 = $this->getProvincialBasicTax();
		$V1 = $this->getProvincialSurtax();
		$Y = 0;
		$S = 0;

		Debug::text('BC Specific - Province: '. $this->getProvince(), __FILE__, __LINE__, __METHOD__, 10);
		$tax_reduction_data = $this->getProvincialTaxReductionData( $this->getDate() );
		if ( is_array($tax_reduction_data) ) {
			if ( $A <= $tax_reduction_data['income1'] ) {
				Debug::text('S: Annual Income less than: '. $tax_reduction_data['income1'], __FILE__, __LINE__, __METHOD__, 10);
				if ( $T4 > $tax_reduction_data['amount'] ) {
					$S = $tax_reduction_data['amount'];
				} else {
					$S = $T4;
				}
			} elseif ( $A > $tax_reduction_data['income1'] AND $A <= $tax_reduction_data['income2'] ) {
				Debug::text('S: Annual Income less than '. $tax_reduction_data['income2'], __FILE__, __LINE__, __METHOD__, 10);

				$tmp_S = bcsub( $tax_reduction_data['amount'], bcmul( bcsub( $A, $tax_reduction_data['income1'] ), $tax_reduction_data['rate'] ) );
				Debug::text('Tmp_S: '. $tmp_S, __FILE__, __LINE__, __METHOD__, 10);

				if ( $T4 > $tmp_S ) {
					$S = $tmp_S;
				} else {
					$S = $T4;
				}
				unset($tmp_S);
			}
		}
		Debug::text('aS: '. $S, __FILE__, __LINE__, __METHOD__, 10);

		if ( $S < 0 ) {
			$S = 0;
		}

		Debug::text('bS: '. $S, __FILE__, __LINE__, __METHOD__, 10);

		return $S;
	}
}
?>
