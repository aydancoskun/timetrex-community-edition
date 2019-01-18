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
 * @package PayrollDeduction\CA
 */
class PayrollDeduction_CA_ON extends PayrollDeduction_CA {
	var $provincial_income_tax_rate_options = array(
			20180701 => array(
					array('income' => 42960, 'rate' => 5.05, 'constant' => 0),
					array('income' => 71500, 'rate' => 9.15, 'constant' => 1761),
					array('income' => 82000, 'rate' => 12.85, 'constant' => 4407),
					array('income' => 85923, 'rate' => 17.85, 'constant' => 8507),
					array('income' => 92000, 'rate' => 15.84, 'constant' => 6780),
					array('income' => 150000, 'rate' => 23.84, 'constant' => 14140),
					array('income' => 220000, 'rate' => 25.84, 'constant' => 17140),
					array('income' => 220000, 'rate' => 27.90, 'constant' => 21672),
			),
			20180101 => array(
					array('income' => 42960, 'rate' => 5.05, 'constant' => 0),
					array('income' => 85923, 'rate' => 9.15, 'constant' => 1761),
					array('income' => 150000, 'rate' => 11.16, 'constant' => 3488),
					array('income' => 220000, 'rate' => 12.16, 'constant' => 4988),
					array('income' => 220000, 'rate' => 13.16, 'constant' => 7188),
			),
			20170101 => array(
					array('income' => 42201, 'rate' => 5.05, 'constant' => 0),
					array('income' => 84404, 'rate' => 9.15, 'constant' => 1730),
					array('income' => 150000, 'rate' => 11.16, 'constant' => 3427),
					array('income' => 220000, 'rate' => 12.16, 'constant' => 4927),
					array('income' => 220000, 'rate' => 13.16, 'constant' => 7127),
			),
			20160101 => array(
					array('income' => 41536, 'rate' => 5.05, 'constant' => 0),
					array('income' => 83075, 'rate' => 9.15, 'constant' => 1703),
					array('income' => 150000, 'rate' => 11.16, 'constant' => 3373),
					array('income' => 220000, 'rate' => 12.16, 'constant' => 4873),
					array('income' => 220000, 'rate' => 13.16, 'constant' => 7073),
			),
			20150101 => array(
					array('income' => 40922, 'rate' => 5.05, 'constant' => 0),
					array('income' => 81847, 'rate' => 9.15, 'constant' => 1678),
					array('income' => 150000, 'rate' => 11.16, 'constant' => 3323),
					array('income' => 220000, 'rate' => 12.16, 'constant' => 4823),
					array('income' => 220000, 'rate' => 13.16, 'constant' => 7023),
			),
			20140901 => array(
					array('income' => 40120, 'rate' => 5.05, 'constant' => 0),
					array('income' => 80242, 'rate' => 9.15, 'constant' => 1645),
					array('income' => 150000, 'rate' => 11.16, 'constant' => 3258),
					array('income' => 220000, 'rate' => 14.16, 'constant' => 7758),
					array('income' => 514090, 'rate' => 13.16, 'constant' => -6206),
					array('income' => 514090, 'rate' => 17.16, 'constant' => 14358),
			),
			20140101 => array(
					array('income' => 40120, 'rate' => 5.05, 'constant' => 0),
					array('income' => 80242, 'rate' => 9.15, 'constant' => 1645),
					array('income' => 514090, 'rate' => 11.16, 'constant' => 3258),
					array('income' => 514090, 'rate' => 13.16, 'constant' => 13540),
			),
			20130101 => array(
					array('income' => 39723, 'rate' => 5.05, 'constant' => 0),
					array('income' => 79448, 'rate' => 9.15, 'constant' => 1629),
					array('income' => 509000, 'rate' => 11.16, 'constant' => 3226),
					array('income' => 509000, 'rate' => 13.16, 'constant' => 13406),
			),
			20120101 => array(
					array('income' => 39020, 'rate' => 5.05, 'constant' => 0),
					array('income' => 78043, 'rate' => 9.15, 'constant' => 1600),
					array('income' => 78043, 'rate' => 11.16, 'constant' => 3168),
			),
			20110101 => array(
					array('income' => 37774, 'rate' => 5.05, 'constant' => 0),
					array('income' => 75550, 'rate' => 9.15, 'constant' => 1549),
					array('income' => 75550, 'rate' => 11.16, 'constant' => 3067),
			),
			20100101 => array(
					array('income' => 37106, 'rate' => 5.05, 'constant' => 0),
					array('income' => 74214, 'rate' => 9.15, 'constant' => 1521),
					array('income' => 74214, 'rate' => 11.16, 'constant' => 3013),
			),
			20090101 => array(
					array('income' => 36848, 'rate' => 6.05, 'constant' => 0),
					array('income' => 73698, 'rate' => 9.15, 'constant' => 1142),
					array('income' => 73698, 'rate' => 11.16, 'constant' => 2624),
			),
			20080101 => array(
					array('income' => 36020, 'rate' => 6.05, 'constant' => 0),
					array('income' => 72041, 'rate' => 9.15, 'constant' => 1117),
					array('income' => 72041, 'rate' => 11.16, 'constant' => 2565),
			),
			20070101 => array(
					array('income' => 35488, 'rate' => 6.05, 'constant' => 0),
					array('income' => 70976, 'rate' => 9.15, 'constant' => 1100),
					array('income' => 70976, 'rate' => 11.16, 'constant' => 2527),
			),
			20060101 => array(
					array('income' => 34758, 'rate' => 6.05, 'constant' => 0),
					array('income' => 69517, 'rate' => 9.15, 'constant' => 1077),
					array('income' => 69517, 'rate' => 11.16, 'constant' => 2475),
			),
	);

	/*
		Provincial surtax
	*/
	var $provincial_surtax_options = array(
			20180101 => array( //2018
							   'income1' => 4638,
							   'income2' => 5936,
							   'rate1'   => 0.20,
							   'rate2'   => 0.36,
			),
			20170101 => array( //2017
							   'income1' => 4556,
							   'income2' => 5831,
							   'rate1'   => 0.20,
							   'rate2'   => 0.36,
			),
			20160101 => array( //2016
							   'income1' => 4484,
							   'income2' => 5739,
							   'rate1'   => 0.20,
							   'rate2'   => 0.36,
			),
			20150101 => array( //2015
							   'income1' => 4418,
							   'income2' => 5654,
							   'rate1'   => 0.20,
							   'rate2'   => 0.36,
			),
			20140101 => array( //2014
							   'income1' => 4331,
							   'income2' => 5543,
							   'rate1'   => 0.20,
							   'rate2'   => 0.36,
			),
			20130101 => array( //2013
							   'income1' => 4289,
							   'income2' => 5489,
							   'rate1'   => 0.20,
							   'rate2'   => 0.36,
			),
			20120101 => array( //2012
							   'income1' => 4213,
							   'income2' => 5392,
							   'rate1'   => 0.20,
							   'rate2'   => 0.36,
			),
			20110101 => array( //2011
							   'income1' => 4078,
							   'income2' => 5219,
							   'rate1'   => 0.20,
							   'rate2'   => 0.36,
			),
			20100101 => array( //2010
							   'income1' => 4006,
							   'income2' => 5127,
							   'rate1'   => 0.20,
							   'rate2'   => 0.36,
			),
			20090101 => array( //2009
							   'income1' => 4257,
							   'income2' => 5370,
							   'rate1'   => 0.20,
							   'rate2'   => 0.36,
			),
			20080101 => array( //2008
							   'income1' => 4162,
							   'income2' => 5249,
							   'rate1'   => 0.20,
							   'rate2'   => 0.36,
			),
			20070101 => array( //2007
							   'income1' => 4100,
							   'income2' => 5172,
							   'rate1'   => 0.20,
							   'rate2'   => 0.36,
			),
			20060101 => array( //2006
							   'income1' => 4016,
							   'income2' => 5065,
							   'rate1'   => 0.20,
							   'rate2'   => 0.36,
			),
	);

	/*
		Provincial tax reduction
	*/
	var $provincial_tax_reduction_options = array(
			20180101 => array( //2018
							   'amount' => 239,
			),
			20170101 => array( //2017
							   'amount' => 235,
			),
			20160101 => array( //2016
							   'amount' => 231,
			),
			20150101 => array( //2015
							   'amount' => 228,
			),
			20140101 => array( //2014
							   'amount' => 223,
			),
			20130101 => array( //2013
							   'amount' => 221,
			),
			20120101 => array( //2012
							   'amount' => 217,
			),
			20110101 => array( //2011
							   'amount' => 210,
			),
			20100101 => array( //2010
							   'amount' => 206,
			),
			20090101 => array( //2009
							   'amount' => 205,
			),
			20080101 => array( //2008
							   'amount' => 201,
			),
			20070101 => array( //2007
							   'amount' => 198,
			),
			20060101 => array( //2006
							   'amount' => 194,
			),
	);

	function getProvincialTaxReduction() {
		$A = $this->getAnnualTaxableIncome();
		$T4 = $this->getProvincialBasicTax();
		$V1 = $this->getProvincialSurtax();
		$Y = 0;
		$S = 0;

		Debug::text( 'ON Specific - Province: ' . $this->getProvince(), __FILE__, __LINE__, __METHOD__, 10 );
		$tax_reduction_data = $this->getProvincialTaxReductionData( $this->getDate() );
		if ( is_array( $tax_reduction_data ) ) {
			$tmp_Sa = bcadd( $T4, $V1 );
			$tmp_Sb = bcsub( bcmul( 2, bcadd( $tax_reduction_data['amount'], $Y ) ), bcadd( $T4, $V1 ) );

			if ( $tmp_Sa < $tmp_Sb ) {
				$S = $tmp_Sa;
			} else {
				$S = $tmp_Sb;
			}
		}
		Debug::text( 'aS: ' . $S, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $S < 0 ) {
			$S = 0;
		}

		Debug::text( 'bS: ' . $S, __FILE__, __LINE__, __METHOD__, 10 );

		return $S;
	}

	function getProvincialSurtax() {
		/*
			V1 =
			For Ontario
				Where T4 <= 4016
				V1 = 0

				Where T4 > 4016 <= 5065
				V1 = 0.20 * ( T4 - 4016 )

				Where T4 > 5065
				V1 = 0.20 * (T4 - 4016) + 0.36 * (T4 - 5065)

		*/

		$V1 = 0;
		if ( $this->getDate() < 20180701 ) { //Repealed July 1st 2018.
			$T4 = $this->getProvincialBasicTax();

			$surtax_data = $this->getProvincialSurTaxData( $this->getDate() );
			if ( is_array( $surtax_data ) ) {
				if ( $T4 < $surtax_data['income1'] ) {
					$V1 = 0;
				} elseif ( $T4 > $surtax_data['income1'] AND $T4 <= $surtax_data['income2'] ) {
					$V1 = bcmul( $surtax_data['rate1'], bcsub( $T4, $surtax_data['income1'] ) );
				} elseif ( $T4 > $surtax_data['income2'] ) {
					$V1 = bcadd( bcmul( $surtax_data['rate1'], bcsub( $T4, $surtax_data['income1'] ) ), bcmul( $surtax_data['rate2'], bcsub( $T4, $surtax_data['income2'] ) ) );
				}
			}
		}

		Debug::text( 'V1: ' . $V1, __FILE__, __LINE__, __METHOD__, 10 );

		return $V1;
	}

	function getAdditionalProvincialSurtax() {
		/*
			V2 =

			Where A < 20,000
			V2 = 0

			Where A >

		*/

		$A = $this->getAnnualTaxableIncome();
		$V2 = 0;

		if ( $this->getDate() >= 20060101 ) {
			if ( $A < 20000 ) {
				$V2 = 0;
			} elseif ( $A > 20000 AND $A <= 36000 ) {
				$tmp_V2 = bcmul( 0.06, bcsub( $A, 20000 ) );

				if ( $tmp_V2 > 300 ) {
					$V2 = 300;
				} else {
					$V2 = $tmp_V2;
				}
			} elseif ( $A > 36000 AND $A <= 48000 ) {
				$tmp_V2 = bcadd( 300, bcmul( 0.06, bcsub( $A, 36000 ) ) );

				if ( $tmp_V2 > 450 ) {
					$V2 = 450;
				} else {
					$V2 = $tmp_V2;
				}
			} elseif ( $A > 48000 AND $A <= 72000 ) {
				$tmp_V2 = bcadd( 450, bcmul( 0.25, bcsub( $A, 48000 ) ) );

				if ( $tmp_V2 > 600 ) {
					$V2 = 600;
				} else {
					$V2 = $tmp_V2;
				}
			} elseif ( $A > 72000 AND $A <= 200000 ) {
				$tmp_V2 = bcadd( 600, bcmul( 0.25, bcsub( $A, 72000 ) ) );

				if ( $tmp_V2 > 750 ) {
					$V2 = 750;
				} else {
					$V2 = $tmp_V2;
				}
			} elseif ( $A > 200000 ) {
				$tmp_V2 = bcadd( 750, bcmul( 0.25, bcsub( $A, 200000 ) ) );

				if ( $tmp_V2 > 900 ) {
					$V2 = 900;
				} else {
					$V2 = $tmp_V2;
				}
			}
		}

		Debug::text( 'V2: ' . $V2, __FILE__, __LINE__, __METHOD__, 10 );

		return $V2;
	}
}

?>
