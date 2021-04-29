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
class PayrollDeduction_CA_SK extends PayrollDeduction_CA {
	var $provincial_income_tax_rate_options = [
			20180101 => [ //Same as 20170701
						  [ 'income' => 45225, 'rate' => 10.5, 'constant' => 0 ],
						  [ 'income' => 129214, 'rate' => 12.5, 'constant' => 905 ],
						  [ 'income' => 129214, 'rate' => 14.5, 'constant' => 3489 ],
			],
			20170701 => [
					[ 'income' => 45225, 'rate' => 10.5, 'constant' => 0 ],
					[ 'income' => 129214, 'rate' => 12.5, 'constant' => 905 ],
					[ 'income' => 129214, 'rate' => 14.5, 'constant' => 3489 ],
			],
			20170101 => [
					[ 'income' => 45225, 'rate' => 11, 'constant' => 0 ],
					[ 'income' => 129214, 'rate' => 13, 'constant' => 905 ],
					[ 'income' => 129214, 'rate' => 15, 'constant' => 3489 ],
			],
			20160101 => [
					[ 'income' => 44601, 'rate' => 11, 'constant' => 0 ],
					[ 'income' => 127430, 'rate' => 13, 'constant' => 892 ],
					[ 'income' => 127430, 'rate' => 15, 'constant' => 3441 ],
			],
			20150101 => [
					[ 'income' => 44028, 'rate' => 11, 'constant' => 0 ],
					[ 'income' => 125795, 'rate' => 13, 'constant' => 881 ],
					[ 'income' => 125795, 'rate' => 15, 'constant' => 3396 ],
			],
			20140101 => [
					[ 'income' => 43292, 'rate' => 11, 'constant' => 0 ],
					[ 'income' => 123692, 'rate' => 13, 'constant' => 866 ],
					[ 'income' => 123692, 'rate' => 15, 'constant' => 3340 ],
			],
			20130101 => [
					[ 'income' => 42906, 'rate' => 11, 'constant' => 0 ],
					[ 'income' => 122589, 'rate' => 13, 'constant' => 858 ],
					[ 'income' => 122589, 'rate' => 15, 'constant' => 3310 ],
			],
			20120101 => [
					[ 'income' => 42065, 'rate' => 11, 'constant' => 0 ],
					[ 'income' => 120185, 'rate' => 13, 'constant' => 841 ],
					[ 'income' => 120185, 'rate' => 15, 'constant' => 3245 ],
			],
			20110101 => [
					[ 'income' => 40919, 'rate' => 11, 'constant' => 0 ],
					[ 'income' => 116911, 'rate' => 13, 'constant' => 818 ],
					[ 'income' => 116911, 'rate' => 15, 'constant' => 3157 ],
			],
			20100101 => [
					[ 'income' => 40354, 'rate' => 11, 'constant' => 0 ],
					[ 'income' => 115297, 'rate' => 13, 'constant' => 807 ],
					[ 'income' => 115297, 'rate' => 15, 'constant' => 3113 ],
			],
			20090101 => [
					[ 'income' => 40113, 'rate' => 11, 'constant' => 0 ],
					[ 'income' => 114610, 'rate' => 13, 'constant' => 802 ],
					[ 'income' => 114610, 'rate' => 15, 'constant' => 3094 ],
			],
			20080101 => [
					[ 'income' => 39135, 'rate' => 11, 'constant' => 0 ],
					[ 'income' => 111814, 'rate' => 13, 'constant' => 783 ],
					[ 'income' => 111814, 'rate' => 15, 'constant' => 3019 ],
			],
			20070101 => [
					[ 'income' => 38405, 'rate' => 11.0, 'constant' => 0 ],
					[ 'income' => 109720, 'rate' => 13.0, 'constant' => 768 ],
					[ 'income' => 109720, 'rate' => 15.0, 'constant' => 2963 ],
			],
			20060101 => [
					[ 'income' => 37579, 'rate' => 11, 'constant' => 0 ],
					[ 'income' => 107367, 'rate' => 13, 'constant' => 752 ],
					[ 'income' => 107367, 'rate' => 15, 'constant' => 2899 ],
			],
	];
}

?>
