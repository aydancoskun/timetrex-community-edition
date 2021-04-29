<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2020 TimeTrex Software Inc.
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
	var $provincial_income_tax_rate_options = [
			20200701 => [
					[ 'income' => 41725, 'rate' => 5.06, 'constant' => 0 ],
					[ 'income' => 83451, 'rate' => 7.7, 'constant' => 1102 ],
					[ 'income' => 95812, 'rate' => 10.5, 'constant' => 3438 ],
					[ 'income' => 116344, 'rate' => 12.29, 'constant' => 5153 ],
					[ 'income' => 157748, 'rate' => 14.7, 'constant' => 7957 ],
					[ 'income' => 220000, 'rate' => 16.8, 'constant' => 11270 ],
					[ 'income' => 220000, 'rate' => 24.2, 'constant' => 27550 ],
			],
			20200101 => [
					[ 'income' => 41725, 'rate' => 5.06, 'constant' => 0 ],
					[ 'income' => 83451, 'rate' => 7.7, 'constant' => 1102 ],
					[ 'income' => 95812, 'rate' => 10.5, 'constant' => 3438 ],
					[ 'income' => 116344, 'rate' => 12.29, 'constant' => 5153 ],
					[ 'income' => 157748, 'rate' => 14.7, 'constant' => 7957 ],
					[ 'income' => 157748, 'rate' => 16.8, 'constant' => 11270 ],
			],
			20190101 => [
					[ 'income' => 40707, 'rate' => 5.06, 'constant' => 0 ],
					[ 'income' => 81416, 'rate' => 7.7, 'constant' => 1075 ],
					[ 'income' => 93476, 'rate' => 10.5, 'constant' => 3354 ],
					[ 'income' => 113506, 'rate' => 12.29, 'constant' => 5028 ],
					[ 'income' => 153900, 'rate' => 14.7, 'constant' => 7763 ],
					[ 'income' => 153900, 'rate' => 16.8, 'constant' => 10995 ],
			],
			20180101 => [
					[ 'income' => 39676, 'rate' => 5.06, 'constant' => 0 ],
					[ 'income' => 79353, 'rate' => 7.7, 'constant' => 1047 ],
					[ 'income' => 91107, 'rate' => 10.5, 'constant' => 3269 ],
					[ 'income' => 110630, 'rate' => 12.29, 'constant' => 4900 ],
					[ 'income' => 150000, 'rate' => 14.7, 'constant' => 7566 ],
					[ 'income' => 150000, 'rate' => 16.8, 'constant' => 10716 ],
			],
			20170101 => [
					[ 'income' => 38898, 'rate' => 5.06, 'constant' => 0 ],
					[ 'income' => 77797, 'rate' => 7.7, 'constant' => 1027 ],
					[ 'income' => 89320, 'rate' => 10.5, 'constant' => 3205 ],
					[ 'income' => 108460, 'rate' => 12.29, 'constant' => 4804 ],
					[ 'income' => 108460, 'rate' => 14.7, 'constant' => 7418 ],
			],
			20160101 => [
					[ 'income' => 38210, 'rate' => 5.06, 'constant' => 0 ],
					[ 'income' => 76421, 'rate' => 7.7, 'constant' => 1009 ],
					[ 'income' => 87741, 'rate' => 10.5, 'constant' => 3149 ],
					[ 'income' => 106543, 'rate' => 12.29, 'constant' => 4719 ],
					[ 'income' => 106543, 'rate' => 14.7, 'constant' => 7287 ],
			],
			20150101 => [
					[ 'income' => 37869, 'rate' => 5.06, 'constant' => 0 ],
					[ 'income' => 75740, 'rate' => 7.7, 'constant' => 1000 ],
					[ 'income' => 86958, 'rate' => 10.5, 'constant' => 3120 ],
					[ 'income' => 105592, 'rate' => 12.29, 'constant' => 4677 ],
					[ 'income' => 151050, 'rate' => 14.7, 'constant' => 7222 ],
					[ 'income' => 151050, 'rate' => 16.8, 'constant' => 10394 ],
			],
			20140101 => [
					[ 'income' => 37606, 'rate' => 5.06, 'constant' => 0 ],
					[ 'income' => 75213, 'rate' => 7.7, 'constant' => 993 ],
					[ 'income' => 86354, 'rate' => 10.5, 'constant' => 3099 ],
					[ 'income' => 104858, 'rate' => 12.29, 'constant' => 4644 ],
					[ 'income' => 150000, 'rate' => 14.7, 'constant' => 7172 ],
					[ 'income' => 150000, 'rate' => 16.8, 'constant' => 10322 ],
			],
			20130101 => [
					[ 'income' => 37568, 'rate' => 5.06, 'constant' => 0 ],
					[ 'income' => 75138, 'rate' => 7.7, 'constant' => 992 ],
					[ 'income' => 86268, 'rate' => 10.5, 'constant' => 3096 ],
					[ 'income' => 104754, 'rate' => 12.29, 'constant' => 4640 ],
					[ 'income' => 104754, 'rate' => 14.7, 'constant' => 7164 ],
			],
			20120101 => [
					[ 'income' => 37013, 'rate' => 5.06, 'constant' => 0 ],
					[ 'income' => 74028, 'rate' => 7.7, 'constant' => 977 ],
					[ 'income' => 84993, 'rate' => 10.5, 'constant' => 3050 ],
					[ 'income' => 103205, 'rate' => 12.29, 'constant' => 4571 ],
					[ 'income' => 103205, 'rate' => 14.7, 'constant' => 7059 ],
			],
			20110101 => [
					[ 'income' => 36146, 'rate' => 5.06, 'constant' => 0 ],
					[ 'income' => 72293, 'rate' => 7.7, 'constant' => 954 ],
					[ 'income' => 83001, 'rate' => 10.5, 'constant' => 2978 ],
					[ 'income' => 100787, 'rate' => 12.29, 'constant' => 4464 ],
					[ 'income' => 100787, 'rate' => 14.7, 'constant' => 6893 ],
			],
			20100101 => [
					[ 'income' => 35859, 'rate' => 5.06, 'constant' => 0 ],
					[ 'income' => 71719, 'rate' => 7.7, 'constant' => 947 ],
					[ 'income' => 82342, 'rate' => 10.5, 'constant' => 2955 ],
					[ 'income' => 99987, 'rate' => 12.29, 'constant' => 4429 ],
					[ 'income' => 99987, 'rate' => 14.7, 'constant' => 6838 ],
			],
			20090101 => [
					[ 'income' => 35716, 'rate' => 5.06, 'constant' => 0 ],
					[ 'income' => 71433, 'rate' => 7.7, 'constant' => 943 ],
					[ 'income' => 82014, 'rate' => 10.5, 'constant' => 2943 ],
					[ 'income' => 99588, 'rate' => 12.29, 'constant' => 4411 ],
					[ 'income' => 99588, 'rate' => 14.7, 'constant' => 6811 ],
			],
			20080701 => [
					[ 'income' => 35016, 'rate' => 5.13, 'constant' => 0 ],
					[ 'income' => 70033, 'rate' => 7.81, 'constant' => 938 ],
					[ 'income' => 80406, 'rate' => 10.50, 'constant' => 2822 ],
					[ 'income' => 97636, 'rate' => 12.29, 'constant' => 4262 ],
					[ 'income' => 97636, 'rate' => 14.70, 'constant' => 6615 ],
			],
			20080101 => [
					[ 'income' => 35016, 'rate' => 5.35, 'constant' => 0 ],
					[ 'income' => 70033, 'rate' => 8.15, 'constant' => 980 ],
					[ 'income' => 80406, 'rate' => 10.50, 'constant' => 2626 ],
					[ 'income' => 97636, 'rate' => 12.29, 'constant' => 4065 ],
					[ 'income' => 97636, 'rate' => 14.70, 'constant' => 6419 ],
			],
			20070701 => [
					[ 'income' => 34397, 'rate' => 5.35, 'constant' => 0 ],
					[ 'income' => 68794, 'rate' => 8.15, 'constant' => 963 ],
					[ 'income' => 78984, 'rate' => 10.5, 'constant' => 2580 ],
					[ 'income' => 95909, 'rate' => 12.3, 'constant' => 4001 ],
					[ 'income' => 95909, 'rate' => 14.7, 'constant' => 6303 ],
			],
			20070101 => [
					[ 'income' => 34397, 'rate' => 6.05, 'constant' => 0 ],
					[ 'income' => 68794, 'rate' => 9.15, 'constant' => 1066 ],
					[ 'income' => 78984, 'rate' => 11.70, 'constant' => 2821 ],
					[ 'income' => 95909, 'rate' => 13.70, 'constant' => 4400 ],
					[ 'income' => 95909, 'rate' => 14.70, 'constant' => 5359 ],
			],
			20060101 => [
					[ 'income' => 33755, 'rate' => 6.05, 'constant' => 0 ],
					[ 'income' => 67511, 'rate' => 9.15, 'constant' => 1046 ],
					[ 'income' => 77511, 'rate' => 11.7, 'constant' => 2768 ],
					[ 'income' => 94121, 'rate' => 13.7, 'constant' => 4318 ],
					[ 'income' => 94121, 'rate' => 14.7, 'constant' => 5259 ],
			],
			20050701 => [
					[ 'income' => 33061, 'rate' => 6.05, 'constant' => 0 ],
					[ 'income' => 66123, 'rate' => 9.15, 'constant' => 1007 ],
					[ 'income' => 75917, 'rate' => 11.7, 'constant' => 2663 ],
					[ 'income' => 92185, 'rate' => 13.7, 'constant' => 4155 ],
					[ 'income' => 92185, 'rate' => 14.7, 'constant' => 5151 ],
			],
			20050101 => [
					[ 'income' => 33061, 'rate' => 6.05, 'constant' => 0 ],
					[ 'income' => 66123, 'rate' => 9.15, 'constant' => 1025 ],
					[ 'income' => 75917, 'rate' => 11.7, 'constant' => 2711 ],
					[ 'income' => 92185, 'rate' => 13.7, 'constant' => 4229 ],
					[ 'income' => 92185, 'rate' => 14.7, 'constant' => 5151 ],
			],
			20040101 => [
					[ 'income' => 32476, 'rate' => 6.05, 'constant' => 0 ],
					[ 'income' => 64954, 'rate' => 9.15, 'constant' => 1007 ],
					[ 'income' => 74575, 'rate' => 11.7, 'constant' => 2663 ],
					[ 'income' => 90555, 'rate' => 13.7, 'constant' => 4155 ],
					[ 'income' => 90555, 'rate' => 14.7, 'constant' => 5060 ],
			],
			20030101 => [
					[ 'income' => 32476, 'rate' => 6.05, 'constant' => 0 ],
					[ 'income' => 64954, 'rate' => 9.15, 'constant' => 1007 ],
					[ 'income' => 74575, 'rate' => 11.7, 'constant' => 2663 ],
					[ 'income' => 90555, 'rate' => 13.7, 'constant' => 4155 ],
					[ 'income' => 90555, 'rate' => 14.7, 'constant' => 5060 ],
			],
			20020101 => [
					[ 'income' => 32476, 'rate' => 6.05, 'constant' => 0 ],
					[ 'income' => 64954, 'rate' => 9.15, 'constant' => 1007 ],
					[ 'income' => 74575, 'rate' => 11.7, 'constant' => 2663 ],
					[ 'income' => 90555, 'rate' => 13.7, 'constant' => 4155 ],
					[ 'income' => 90555, 'rate' => 14.7, 'constant' => 5060 ],
			],
			20010101 => [
					[ 'income' => 32476, 'rate' => 6.05, 'constant' => 0 ],
					[ 'income' => 64954, 'rate' => 9.15, 'constant' => 1007 ],
					[ 'income' => 74575, 'rate' => 11.7, 'constant' => 2663 ],
					[ 'income' => 90555, 'rate' => 13.7, 'constant' => 4155 ],
					[ 'income' => 90555, 'rate' => 14.7, 'constant' => 5060 ],
			],
	];

	/*
		Provincial tax reduction
	*/
	var $provincial_tax_reduction_options = [
			20200101 => [
					'income1' => 21185,
					'income2' => 34556,
					'amount'  => 476,
					'rate'    => 0.0356,
			],
			20190101 => [
					'income1' => 20668,
					'income2' => 33702,
					'amount'  => 464,
					'rate'    => 0.0356,
			],
			20180101 => [
					'income1' => 20144,
					'income2' => 32868.72,
					'amount'  => 453,
					'rate'    => 0.0356,
			],
			20170101 => [
					'income1' => 19749,
					'income2' => 32220.91,
					'amount'  => 444,
					'rate'    => 0.0356,
			],
			20160701 => [ //01-Jul-2016
						  'income1' => 19629,
						  'income2' => 31673.20,
						  'amount'  => 436,
						  'rate'    => 0.0362,
			],
			20160101 => [
					'income1' => 19171,
					'income2' => 31628.14,
					'amount'  => 436,
					'rate'    => 0.035,
			],
			20150701 => [ //01-Jul-2015
						  'income1' => 19673,
						  'income2' => 31567.74,
						  'amount'  => 452,
						  'rate'    => 0.038,
			],
			20150101 => [
					'income1' => 18327,
					'income2' => 31202,
					'amount'  => 412,
					'rate'    => 0.032,
			],
			20140101 => [
					'income1' => 18200,
					'income2' => 30981.25,
					'amount'  => 409,
					'rate'    => 0.032,
			],
			20130101 => [
					'income1' => 18181,
					'income2' => 30962.25,
					'amount'  => 409,
					'rate'    => 0.032,
			],
			20120101 => [
					'income1' => 17913,
					'income2' => 30506.75,
					'amount'  => 403,
					'rate'    => 0.032,
			],
			20110101 => [
					'income1' => 17493,
					'income2' => 29805.50,
					'amount'  => 394,
					'rate'    => 0.032,
			],
			20100101 => [
					'income1' => 17354,
					'income2' => 29541.50,
					'amount'  => 390,
					'rate'    => 0.032,
			],
			20090101 => [
					'income1' => 17285,
					'income2' => 29441.25,
					'amount'  => 389,
					'rate'    => 0.032,
			],
			20080101 => [
					'income1' => 16946,
					'income2' => 28852.25,
					'amount'  => 381,
					'rate'    => 0.032,
			],
			20070701 => [ //07-Jul-2007
						  'income1' => 16646,
						  'income2' => 28364.75,
						  'amount'  => 375,
						  'rate'    => 0.032,
			],
			20070101 => [
					'income1' => 16646,
					'income2' => 27062.67,
					'amount'  => 375,
					'rate'    => 0.032,
			],
			20060101 => [
					'income1' => 16336,
					'income2' => 26558.22,
					'amount'  => 368,
					'rate'    => 0.032,
			],
			20050101 => [
					'income1' => 16000,
					'income2' => 26000,
					'amount'  => 360,
					'rate'    => 0.032,
			],
	];

	function getProvincialTaxReduction() {
		$A = $this->getAnnualTaxableIncome();
		$T4 = $this->getProvincialBasicTax();
//		$V1 = $this->getProvincialSurtax();
//		$Y = 0;
		$S = 0;

		Debug::text( 'BC Specific - Province: ' . $this->getProvince(), __FILE__, __LINE__, __METHOD__, 10 );
		$tax_reduction_data = $this->getProvincialTaxReductionData( $this->getDate() );
		if ( is_array( $tax_reduction_data ) ) {
			if ( $A <= $tax_reduction_data['income1'] ) {
				Debug::text( 'S: Annual Income less than: ' . $tax_reduction_data['income1'], __FILE__, __LINE__, __METHOD__, 10 );
				if ( $T4 > $tax_reduction_data['amount'] ) {
					$S = $tax_reduction_data['amount'];
				} else {
					$S = $T4;
				}
			} else if ( $A > $tax_reduction_data['income1'] && $A <= $tax_reduction_data['income2'] ) {
				Debug::text( 'S: Annual Income less than ' . $tax_reduction_data['income2'], __FILE__, __LINE__, __METHOD__, 10 );

				$tmp_S = bcsub( $tax_reduction_data['amount'], bcmul( bcsub( $A, $tax_reduction_data['income1'] ), $tax_reduction_data['rate'] ) );
				Debug::text( 'Tmp_S: ' . $tmp_S, __FILE__, __LINE__, __METHOD__, 10 );

				if ( $T4 > $tmp_S ) {
					$S = $tmp_S;
				} else {
					$S = $T4;
				}
				unset( $tmp_S );
			}
		}
		Debug::text( 'aS: ' . $S, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $S < 0 ) {
			$S = 0;
		}

		Debug::text( 'bS: ' . $S, __FILE__, __LINE__, __METHOD__, 10 );

		return $S;
	}
}

?>
