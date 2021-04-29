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
class PayrollDeduction_CA_NU extends PayrollDeduction_CA {
	var $provincial_income_tax_rate_options = [
			20200101 => [
					[ 'income' => 46277, 'rate' => 4.0, 'constant' => 0 ],
					[ 'income' => 92555, 'rate' => 7.0, 'constant' => 1388 ],
					[ 'income' => 150473, 'rate' => 9.0, 'constant' => 3239 ],
					[ 'income' => 150473, 'rate' => 11.5, 'constant' => 7001 ],
			],
			20190101 => [
					[ 'income' => 45414, 'rate' => 4.0, 'constant' => 0 ],
					[ 'income' => 90829, 'rate' => 7.0, 'constant' => 1362 ],
					[ 'income' => 147667, 'rate' => 9.0, 'constant' => 3179 ],
					[ 'income' => 147667, 'rate' => 11.5, 'constant' => 6871 ],
			],
			20180101 => [
					[ 'income' => 44437, 'rate' => 4.0, 'constant' => 0 ],
					[ 'income' => 88874, 'rate' => 7.0, 'constant' => 1333 ],
					[ 'income' => 144488, 'rate' => 9.0, 'constant' => 3111 ],
					[ 'income' => 144488, 'rate' => 11.5, 'constant' => 6723 ],
			],
			20170101 => [
					[ 'income' => 43780, 'rate' => 4.0, 'constant' => 0 ],
					[ 'income' => 87560, 'rate' => 7.0, 'constant' => 1313 ],
					[ 'income' => 142353, 'rate' => 9.0, 'constant' => 3065 ],
					[ 'income' => 142353, 'rate' => 11.5, 'constant' => 6623 ],
			],
			20160101 => [
					[ 'income' => 43176, 'rate' => 4.0, 'constant' => 0 ],
					[ 'income' => 86351, 'rate' => 7.0, 'constant' => 1295 ],
					[ 'income' => 140388, 'rate' => 9.0, 'constant' => 3022 ],
					[ 'income' => 140388, 'rate' => 11.5, 'constant' => 6532 ],
			],
			20150101 => [
					[ 'income' => 42622, 'rate' => 4.0, 'constant' => 0 ],
					[ 'income' => 85243, 'rate' => 7.0, 'constant' => 1279 ],
					[ 'income' => 138586, 'rate' => 9.0, 'constant' => 2984 ],
					[ 'income' => 138586, 'rate' => 11.5, 'constant' => 6448 ],
			],
			20140101 => [
					[ 'income' => 41909, 'rate' => 4.0, 'constant' => 0 ],
					[ 'income' => 83818, 'rate' => 7.0, 'constant' => 1257 ],
					[ 'income' => 136270, 'rate' => 9.0, 'constant' => 2934 ],
					[ 'income' => 136270, 'rate' => 11.5, 'constant' => 6340 ],
			],
			20130101 => [
					[ 'income' => 41535, 'rate' => 4.0, 'constant' => 0 ],
					[ 'income' => 83071, 'rate' => 7.0, 'constant' => 1246 ],
					[ 'income' => 135054, 'rate' => 9.0, 'constant' => 2907 ],
					[ 'income' => 135054, 'rate' => 11.5, 'constant' => 6284 ],
			],
			20120101 => [
					[ 'income' => 40721, 'rate' => 4.0, 'constant' => 0 ],
					[ 'income' => 81442, 'rate' => 7.0, 'constant' => 1222 ],
					[ 'income' => 132406, 'rate' => 9.0, 'constant' => 2850 ],
					[ 'income' => 132406, 'rate' => 11.5, 'constant' => 6161 ],
			],
			20110101 => [
					[ 'income' => 39612, 'rate' => 4.0, 'constant' => 0 ],
					[ 'income' => 79224, 'rate' => 7.0, 'constant' => 1188 ],
					[ 'income' => 128800, 'rate' => 9.0, 'constant' => 2773 ],
					[ 'income' => 128800, 'rate' => 11.5, 'constant' => 5993 ],
			],
			20100101 => [
					[ 'income' => 39065, 'rate' => 4.0, 'constant' => 0 ],
					[ 'income' => 78130, 'rate' => 7.0, 'constant' => 1172 ],
					[ 'income' => 127021, 'rate' => 9.0, 'constant' => 2735 ],
					[ 'income' => 127021, 'rate' => 11.5, 'constant' => 5910 ],
			],
			20090101 => [
					[ 'income' => 38832, 'rate' => 4.0, 'constant' => 0 ],
					[ 'income' => 77664, 'rate' => 7.0, 'constant' => 1165 ],
					[ 'income' => 126264, 'rate' => 9.0, 'constant' => 2718 ],
					[ 'income' => 126264, 'rate' => 11.5, 'constant' => 5875 ],
			],
			20080101 => [
					[ 'income' => 37885, 'rate' => 4, 'constant' => 0 ],
					[ 'income' => 75770, 'rate' => 7, 'constant' => 1137 ],
					[ 'income' => 123184, 'rate' => 9, 'constant' => 2652 ],
					[ 'income' => 123184, 'rate' => 11.5, 'constant' => 5732 ],
			],
			20070101 => [
					[ 'income' => 37178, 'rate' => 4.0, 'constant' => 0 ],
					[ 'income' => 74357, 'rate' => 7.0, 'constant' => 1115 ],
					[ 'income' => 120887, 'rate' => 9.0, 'constant' => 2602 ],
					[ 'income' => 120887, 'rate' => 11.5, 'constant' => 5625 ],
			],
	];
}

?>
