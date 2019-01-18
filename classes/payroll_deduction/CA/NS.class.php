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
class PayrollDeduction_CA_NS extends PayrollDeduction_CA {
	var $provincial_income_tax_rate_options = array(
			20110101 => array(
					array('income' => 29590, 'rate' => 8.79, 'constant' => 0),
					array('income' => 59180, 'rate' => 14.95, 'constant' => 1823),
					array('income' => 93000, 'rate' => 16.67, 'constant' => 2841),
					array('income' => 150000, 'rate' => 17.5, 'constant' => 3613),
					array('income' => 150000, 'rate' => 21.0, 'constant' => 8863),
			),
			20100701 => array(
					array('income' => 29590, 'rate' => 8.79, 'constant' => 0),
					array('income' => 59180, 'rate' => 14.95, 'constant' => 1823),
					array('income' => 93000, 'rate' => 16.67, 'constant' => 2841),
					array('income' => 150000, 'rate' => 17.5, 'constant' => 3613),
					array('income' => 150000, 'rate' => 24.5, 'constant' => 14113),
			),
			20070101 => array(
					array('income' => 29590, 'rate' => 8.79, 'constant' => 0),
					array('income' => 59180, 'rate' => 14.95, 'constant' => 1823),
					array('income' => 93000, 'rate' => 16.67, 'constant' => 2841),
					array('income' => 93000, 'rate' => 17.5, 'constant' => 3613),
			),
	);

//	function getProvincialSurtax() {
//		/*
//			V1 =
//			For NS
//				Where T4 <= 10000
//				V1 = 0
//
//				Where T4 > 10000
//				V1 = 0.10 * ( T4 - 10000 )
//		*/
//
//		$T4 = $this->getProvincialBasicTax();
//		$V1 = 0;
//
//		//This was phased at some point, but not 100% sure when.
//		if ( $this->getDate() >= 20080101 ) {
//			if ( $T4 <= 10000 ) {
//				$V1 = 0;
//			} elseif ( $T4 > 10000 ) {
//				$V1 = bcmul( 0.10, bcsub( $T4, 10000 ) );
//			}
//		}
//
//		Debug::text('V1: '. $V1, __FILE__, __LINE__, __METHOD__, 10);
//
//		return $V1;
//	}
}

?>
