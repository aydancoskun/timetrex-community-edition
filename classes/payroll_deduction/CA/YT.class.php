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
class PayrollDeduction_CA_YT extends PayrollDeduction_CA {
	var $provincial_income_tax_rate_options = array(
													20150701 => array(
																	array( 'income' => 44701,	'rate' => 5.76,	'constant' => 0 ),
																	array( 'income' => 89401,	'rate' => 8.32,	'constant' => 1144 ),
																	array( 'income' => 138586,	'rate' => 10.36,	'constant' => 2968 ),
																	array( 'income' => 500000,	'rate' => 12.84,	'constant' => 6405 ),
																	array( 'income' => 500000,	'rate' => 17.24,	'constant' => 28405 ),
																),
													20150101 => array(
																	array( 'income' => 44701,	'rate' => 7.04,	'constant' => 0 ),
																	array( 'income' => 89401,	'rate' => 9.68,	'constant' => 1180 ),
																	array( 'income' => 138586,	'rate' => 11.44,	'constant' => 2754 ),
																	array( 'income' => 138586,	'rate' => 12.76,	'constant' => 4583 ),
																),
													20140101 => array(
																	array( 'income' => 43953,	'rate' => 7.04,	'constant' => 0 ),
																	array( 'income' => 87907,	'rate' => 9.68,	'constant' => 1160 ),
																	array( 'income' => 136270,	'rate' => 11.44,	'constant' => 2708 ),
																	array( 'income' => 136270,	'rate' => 12.76,	'constant' => 4506 ),
																),
													20130101 => array(
																	array( 'income' => 43561,	'rate' => 7.04,	'constant' => 0 ),
																	array( 'income' => 87123,	'rate' => 9.68,	'constant' => 1150 ),
																	array( 'income' => 135054,	'rate' => 11.44,	'constant' => 2683 ),
																	array( 'income' => 135054,	'rate' => 12.76,	'constant' => 4466 ),
																),
													20120101 => array(
																	array( 'income' => 42707,	'rate' => 7.04,	'constant' => 0 ),
																	array( 'income' => 85414,	'rate' => 9.68,	'constant' => 1127 ),
																	array( 'income' => 132406,	'rate' => 11.44,	'constant' => 2631 ),
																	array( 'income' => 132406,	'rate' => 12.76,	'constant' => 4379 ),
																),
													20110101 => array(
																	array( 'income' => 41544,	'rate' => 7.04,	'constant' => 0 ),
																	array( 'income' => 83088,	'rate' => 9.68,	'constant' => 1097 ),
																	array( 'income' => 128800,	'rate' => 11.44,	'constant' => 2559 ),
																	array( 'income' => 128800,	'rate' => 12.76,	'constant' => 4259 ),
																),
													20100101 => array(
																	array( 'income' => 40970,	'rate' => 7.04,	'constant' => 0 ),
																	array( 'income' => 81941,	'rate' => 9.68,	'constant' => 1082 ),
																	array( 'income' => 127021,	'rate' => 11.44,	'constant' => 2524 ),
																	array( 'income' => 127021,	'rate' => 12.76,	'constant' => 4200 ),
																),
													);

	function getProvincialSurtax() {
		/*
			V1 =
			For YU
				Where T4 <= 6000
				V1 = 0

				Where T4 > 6000
				V1 = 0.10 * ( T4 - 6000 )
		*/

		$T4 = $this->getProvincialBasicTax();
		$V1 = 0;

		//Repealed 01-Jul-2015 retroactively to 01-Jan-2015.
		if ( $this->getDate() >= 20080101 AND $this->getDate() < 20150701 ) {
			if ( $T4 <= 6000 ) {
				$V1 = 0;
			} elseif ( $T4 > 6000 ) {
				$V1 = bcmul( 0.05, bcsub( $T4, 6000 ) );
			}
		}

		Debug::text('V1: '. $V1, __FILE__, __LINE__, __METHOD__, 10);

		return $V1;
	}

	function getProvincialEmploymentCredit() {
		/*
		  K4P = The lesser of
			0.155 * A and
			0.155 * $1000
		*/

		$K4P = 0;
		if ( $this->getProvince() == 'YT' AND $this->getDate() >= 20130101 ) { //Yukon only currently.
			$tmp1_K4P = bcmul( $this->getData()->getProvincialLowestRate(), $this->getAnnualTaxableIncome() );
			$tmp2_K4P = bcmul( $this->getData()->getProvincialLowestRate(), $this->getData()->getFederalEmploymentCreditAmount() ); //This matches the federal employment credit amount currently.

			if ( $tmp2_K4P < $tmp1_K4P ) {
				$K4P = $tmp2_K4P;
			} else {
				$K4P = $tmp1_K4P;
			}
		}

		Debug::text('K4P: '. $K4P, __FILE__, __LINE__, __METHOD__, 10);
		return $K4P;
	}
}
?>
