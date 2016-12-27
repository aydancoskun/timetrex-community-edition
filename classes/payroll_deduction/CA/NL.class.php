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
class PayrollDeduction_CA_NL extends PayrollDeduction_CA {
	var $provincial_income_tax_rate_options = array(
													20170101 => array(
																	array( 'income' => 35851,	'rate' => 8.7,	'constant' => 0 ),
																	array( 'income' => 71701,	'rate' => 14.5,	'constant' => 2079 ),
																	array( 'income' => 128010,	'rate' => 15.8,	'constant' => 3011 ),
																	array( 'income' => 179214,	'rate' => 17.3,	'constant' => 4932 ),
																	array( 'income' => 179214,	'rate' => 18.3,	'constant' => 6724 ),
																),
													20160701 => array(
																	array( 'income' => 35148,	'rate' => 8.7,	'constant' => 0 ),
																	array( 'income' => 70295,	'rate' => 14.5,	'constant' => 2039 ),
																	array( 'income' => 125500,	'rate' => 15.8,	'constant' => 2952 ),
																	array( 'income' => 175700,	'rate' => 17.3,	'constant' => 4835 ),
																	array( 'income' => 175700,	'rate' => 18.3,	'constant' => 6592 ),
																),
													20160101 => array(
																	array( 'income' => 35148,	'rate' => 7.7,	'constant' => 0 ),
																	array( 'income' => 70295,	'rate' => 12.5,	'constant' => 1687 ),
																	array( 'income' => 125500,	'rate' => 13.3,	'constant' => 2249 ),
																	array( 'income' => 175700,	'rate' => 14.3,	'constant' => 3504 ),
																	array( 'income' => 175700,	'rate' => 15.3,	'constant' => 5261 ),
																),
													20150701 => array(
																	array( 'income' => 35008,	'rate' => 7.7,	'constant' => 0 ),
																	array( 'income' => 70015,	'rate' => 12.5,	'constant' => 1680 ),
																	array( 'income' => 125000,	'rate' => 13.3,	'constant' => 2241 ),
																	array( 'income' => 175000,	'rate' => 14.3,	'constant' => 3491 ),
																	array( 'income' => 175000,	'rate' => 15.3,	'constant' => 5241 ),
																),
													20150101 => array(
																	array( 'income' => 35008,	'rate' => 7.7,	'constant' => 0 ),
																	array( 'income' => 70015,	'rate' => 12.5,	'constant' => 1680 ),
																	array( 'income' => 70015,	'rate' => 13.3,	'constant' => 2241 ),
																),
													20140101 => array(
																	array( 'income' => 34254,	'rate' => 7.7,	'constant' => 0 ),
																	array( 'income' => 68508,	'rate' => 12.5,	'constant' => 1644 ),
																	array( 'income' => 68508,	'rate' => 13.3,	'constant' => 2192 ),
																),
													20130101 => array(
																	array( 'income' => 33748,	'rate' => 7.7,	'constant' => 0 ),
																	array( 'income' => 67496,	'rate' => 12.5,	'constant' => 1620 ),
																	array( 'income' => 67496,	'rate' => 13.3,	'constant' => 2160 ),
																),
													20120101 => array(
																	array( 'income' => 32893,	'rate' => 7.7,	'constant' => 0 ),
																	array( 'income' => 65785,	'rate' => 12.5,	'constant' => 1579 ),
																	array( 'income' => 65785,	'rate' => 13.3,	'constant' => 2105 ),
																),
													20110101 => array(
																	array( 'income' => 31904,	'rate' => 7.7,	'constant' => 0 ),
																	array( 'income' => 63807,	'rate' => 12.5,	'constant' => 1531 ),
																	array( 'income' => 63807,	'rate' => 13.3,	'constant' => 2042 ),
																),
													20100701 => array(
																	array( 'income' => 31278,	'rate' => 7.7,	'constant' => 0 ),
																	array( 'income' => 62556,	'rate' => 12.5,	'constant' => 1501 ),
																	array( 'income' => 62556,	'rate' => 13.3,	'constant' => 2002 ),
																),
													20100101 => array(
																	array( 'income' => 31278,	'rate' => 7.7,	'constant' => 0 ),
																	array( 'income' => 62556,	'rate' => 12.8,	'constant' => 1595 ),
																	array( 'income' => 62556,	'rate' => 15.5,	'constant' => 3284 ),
																),
													20090101 => array(
																	array( 'income' => 31061,	'rate' => 7.7,	'constant' => 0 ),
																	array( 'income' => 62121,	'rate' => 12.8,	'constant' => 1584 ),
																	array( 'income' => 62121,	'rate' => 15.5,	'constant' => 3261 ),
																),
													20080701 => array(
																	array( 'income' => 30215,	'rate' => 7.7,	'constant' => 0 ),
																	array( 'income' => 60429,	'rate' => 12.8,	'constant' => 1541 ),
																	array( 'income' => 60429,	'rate' => 15.5,	'constant' => 3173 ),
																),
													20080101 => array(
																	array( 'income' => 30215,	'rate' => 8.7,	'constant' => 0 ),
																	array( 'income' => 60429,	'rate' => 13.8,	'constant' => 1541 ),
																	array( 'income' => 60429,	'rate' => 16.5,	'constant' => 3173 ),
																),
													20070701 => array(
																	array( 'income' => 30182,	'rate' => 8.7,	'constant' => 0 ),
																	array( 'income' => 60364,	'rate' => 13.8,	'constant' => 1539 ),
																	array( 'income' => 60364,	'rate' => 16.5,	'constant' => 3169 ),
																),
													20070101 => array(
																	array( 'income' => 29590,	'rate' => 10.57,'constant' => 0 ),
																	array( 'income' => 59180,	'rate' => 16.16,'constant' => 1654 ),
																	array( 'income' => 59180,	'rate' => 18.02,'constant' => 2755 ),
																),
													);

	function getAdditionalProvincialSurtax() {
		/*
			V2 =

			Where A < 20,000
			V2 = 0

			Where A >

		*/

		$A = $this->getAnnualTaxableIncome();
		$V2 = 0;

		if ( $this->getDate() >= 20160701 ) {
			$tmp_V2_threshold = 1000;

			if ( $A < 50000 ) {
				//This should result in V2 = 0
				$tmp_A_threshold = 0;
				$tmp_V2_constant = 0;
			} elseif ( $A > 50000 AND $A <= 55000 ) {
				$tmp_A_threshold = bcsub( $A, 50000 );
				$tmp_V2_constant = 0;
			} elseif ( $A > 55000 AND $A <= 60000 ) {
				$tmp_A_threshold = bcsub( $A, 55000 );
				$tmp_V2_constant = 100;
			} elseif ( $A > 60000 AND $A <= 65000 ) {
				$tmp_A_threshold = bcsub( $A, 60000 );
				$tmp_V2_constant = 200;
			} elseif ( $A > 65000 AND $A <= 70000 ) {
				$tmp_A_threshold = bcsub( $A, 65000 );
				$tmp_V2_constant = 300;
			} elseif ( $A > 70000 AND $A <= 75000 ) {
				$tmp_A_threshold = bcsub( $A, 70000 );
				$tmp_V2_constant = 400;
			} elseif ( $A > 75000 AND $A <= 80000 ) {
				$tmp_A_threshold = bcsub( $A, 75000 );
				$tmp_V2_constant = 500;
			} elseif ( $A > 80000 AND $A <= 100000 ) {
				$tmp_A_threshold = bcsub( $A, 80000 );
				$tmp_V2_constant = 600;
			} elseif ( $A > 100000 AND $A <= 125000 ) {
				$tmp_A_threshold = bcsub( $A, 100000 );
				$tmp_V2_constant = 700;
			} elseif ( $A > 125000 AND $A <= 175000 ) {
				$tmp_A_threshold = bcsub( $A, 125000 );
				$tmp_V2_constant = 800;
			} elseif ( $A > 175000 AND $A <= 250000 ) {
				$tmp_A_threshold = bcsub( $A, 175000 );
				$tmp_V2_constant = 900;
			} elseif ( $A > 250000 AND $A <= 300000 ) {
				$tmp_A_threshold = bcsub( $A, 250000 );
				$tmp_V2_constant = 1000;
			} elseif ( $A > 300000 AND $A <= 350000 ) {
				$tmp_A_threshold = bcsub( $A, 300000 );
				$tmp_V2_constant = 1100;
			} elseif ( $A > 350000 AND $A <= 400000 ) {
				$tmp_A_threshold = bcsub( $A, 350000 );
				$tmp_V2_constant = 1200;
			} elseif ( $A > 400000 AND $A <= 450000 ) {
				$tmp_A_threshold = bcsub( $A, 400000 );
				$tmp_V2_constant = 1300;
			} elseif ( $A > 450000 AND $A <= 500000 ) {
				$tmp_A_threshold = bcsub( $A, 450000 );
				$tmp_V2_constant = 1400;
			} elseif ( $A > 500000 AND $A <= 550000 ) {
				$tmp_A_threshold = bcsub( $A, 500000 );
				$tmp_V2_constant = 1500;
			} elseif ( $A > 550000 AND $A <= 600000 ) {
				$tmp_A_threshold = bcsub( $A, 550000 );
				$tmp_V2_constant = 1600;
			} elseif ( $A > 600000 ) {
				$tmp_A_threshold = bcsub( $A, 600000 );
				$tmp_V2_constant = 1700;
			}

			if ( $tmp_A_threshold < $tmp_V2_threshold ) {
				$V2 = bcmul( 0.10, $tmp_A_threshold );
			} else {
				$V2 = bcmul( 0.10, $tmp_V2_threshold );
			}

			if ( $tmp_V2_constant > 0 ) {
				$V2 += $tmp_V2_constant;
			}
		}
		Debug::text('V2: '. $V2, __FILE__, __LINE__, __METHOD__, 10);

		return $V2;
	}
}
?>
