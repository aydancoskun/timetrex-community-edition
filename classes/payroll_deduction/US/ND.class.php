<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2021 TimeTrex Software Inc.
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
class PayrollDeduction_US_ND extends PayrollDeduction_US {

	var $state_income_tax_rate_options = [
			20210101 => [
					10 => [
							[ 'income' => 6275, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 46800, 'rate' => 1.10, 'constant' => 0 ],
							[ 'income' => 104375, 'rate' => 2.04, 'constant' => 445.78 ],
							[ 'income' => 210950, 'rate' => 2.27, 'constant' => 1620.31 ],
							[ 'income' => 451275, 'rate' => 2.64, 'constant' => 4039.56 ],
							[ 'income' => 451275, 'rate' => 2.90, 'constant' => 10384.14 ],
					],
					20 => [
							[ 'income' => 12550, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 46400, 'rate' => 1.10, 'constant' => 0 ],
							[ 'income' => 94325, 'rate' => 2.04, 'constant' => 372.35 ],
							[ 'income' => 137125, 'rate' => 2.27, 'constant' => 1350.02 ],
							[ 'income' => 235050, 'rate' => 2.64, 'constant' => 2321.58 ],
							[ 'income' => 235050, 'rate' => 2.90, 'constant' => 4906.80 ],
					],
					40 => [ //Added in 01-Jan-2021 - Head of Household
							[ 'income' => 9400, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 63700, 'rate' => 1.10, 'constant' => 0 ],
							[ 'income' => 149600, 'rate' => 2.04, 'constant' => 597.30 ],
							[ 'income' => 236350, 'rate' => 2.27, 'constant' => 2349.66 ],
							[ 'income' => 454400, 'rate' => 2.64, 'constant' => 4318.89 ],
							[ 'income' => 454400, 'rate' => 2.90, 'constant' => 10075.41 ],
					],
			],
			20200101 => [
					10 => [
							[ 'income' => 6200, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 46325, 'rate' => 1.10, 'constant' => 0 ],
							[ 'income' => 103350, 'rate' => 2.04, 'constant' => 441.38 ],
							[ 'income' => 208850, 'rate' => 2.27, 'constant' => 1604.69 ],
							[ 'income' => 446800, 'rate' => 2.64, 'constant' => 3999.54 ],
							[ 'income' => 446800, 'rate' => 2.90, 'constant' => 10281.42 ],
					],
					20 => [
							[ 'income' => 12400, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 45925, 'rate' => 1.10, 'constant' => 0 ],
							[ 'income' => 93375, 'rate' => 2.04, 'constant' => 368.78 ],
							[ 'income' => 135750, 'rate' => 2.27, 'constant' => 1336.76 ],
							[ 'income' => 232700, 'rate' => 2.64, 'constant' => 2298.67 ],
							[ 'income' => 232700, 'rate' => 2.90, 'constant' => 4858.15 ],
					],
			],
			20190101 => [
					10 => [
							[ 'income' => 4500, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 43000, 'rate' => 1.10, 'constant' => 0 ],
							[ 'income' => 87000, 'rate' => 2.04, 'constant' => 423.50 ],
							[ 'income' => 202000, 'rate' => 2.27, 'constant' => 1321.10 ],
							[ 'income' => 432000, 'rate' => 2.64, 'constant' => 3931.60 ],
							[ 'income' => 432000, 'rate' => 2.90, 'constant' => 10003.60 ],
					],
					20 => [
							[ 'income' => 10400, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 75000, 'rate' => 1.10, 'constant' => 0 ],
							[ 'income' => 141000, 'rate' => 2.04, 'constant' => 710.60 ],
							[ 'income' => 252000, 'rate' => 2.27, 'constant' => 2057.00 ],
							[ 'income' => 440000, 'rate' => 2.64, 'constant' => 4576.70 ],
							[ 'income' => 440000, 'rate' => 2.90, 'constant' => 9539.90 ],
					],
			],
			20180101 => [
					10 => [
							[ 'income' => 4400, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 42000, 'rate' => 1.10, 'constant' => 0 ],
							[ 'income' => 86000, 'rate' => 2.04, 'constant' => 413.60 ],
							[ 'income' => 198000, 'rate' => 2.27, 'constant' => 1311.20 ],
							[ 'income' => 424000, 'rate' => 2.64, 'constant' => 3853.60 ],
							[ 'income' => 424000, 'rate' => 2.90, 'constant' => 9820.00 ],
					],
					20 => [
							[ 'income' => 10200, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 73500, 'rate' => 1.10, 'constant' => 0 ],
							[ 'income' => 139000, 'rate' => 2.04, 'constant' => 696.30 ],
							[ 'income' => 247000, 'rate' => 2.27, 'constant' => 2032.50 ],
							[ 'income' => 431000, 'rate' => 2.64, 'constant' => 4484.10 ],
							[ 'income' => 431000, 'rate' => 2.90, 'constant' => 9341.70 ],
					],
			],
			20170101 => [
					10 => [
							[ 'income' => 4300, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 41000, 'rate' => 1.10, 'constant' => 0 ],
							[ 'income' => 84000, 'rate' => 2.04, 'constant' => 403.70 ],
							[ 'income' => 194000, 'rate' => 2.27, 'constant' => 1280.90 ],
							[ 'income' => 416000, 'rate' => 2.64, 'constant' => 3777.90 ],
							[ 'income' => 416000, 'rate' => 2.90, 'constant' => 9638.70 ],
					],
					20 => [
							[ 'income' => 10000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 72000, 'rate' => 1.10, 'constant' => 0 ],
							[ 'income' => 136000, 'rate' => 2.04, 'constant' => 682.00 ],
							[ 'income' => 242000, 'rate' => 2.27, 'constant' => 1987.60 ],
							[ 'income' => 423000, 'rate' => 2.64, 'constant' => 4393.80 ],
							[ 'income' => 423000, 'rate' => 2.90, 'constant' => 9172.20 ],
					],
			],
			20160101 => [
					10 => [
							[ 'income' => 4300, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 41000, 'rate' => 1.10, 'constant' => 0 ],
							[ 'income' => 83000, 'rate' => 2.04, 'constant' => 403.70 ],
							[ 'income' => 192000, 'rate' => 2.27, 'constant' => 1260.50 ],
							[ 'income' => 413000, 'rate' => 2.64, 'constant' => 3734.80 ],
							[ 'income' => 413000, 'rate' => 2.90, 'constant' => 9569.20 ],
					],
					20 => [
							[ 'income' => 10000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 71000, 'rate' => 1.10, 'constant' => 0 ],
							[ 'income' => 135000, 'rate' => 2.04, 'constant' => 671.00 ],
							[ 'income' => 240000, 'rate' => 2.27, 'constant' => 1976.60 ],
							[ 'income' => 420000, 'rate' => 2.64, 'constant' => 4360.10 ],
							[ 'income' => 420000, 'rate' => 2.90, 'constant' => 9112.10 ],
					],
			],
			20150601 => [
					10 => [
							[ 'income' => 4300, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 41000, 'rate' => 1.10, 'constant' => 0 ],
							[ 'income' => 83000, 'rate' => 2.04, 'constant' => 403.70 ],
							[ 'income' => 191000, 'rate' => 2.27, 'constant' => 1260.50 ],
							[ 'income' => 411000, 'rate' => 2.64, 'constant' => 3712.10 ],
							[ 'income' => 411000, 'rate' => 2.90, 'constant' => 9520.10 ],
					],
					20 => [
							[ 'income' => 10000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 71000, 'rate' => 1.10, 'constant' => 0 ],
							[ 'income' => 134000, 'rate' => 2.04, 'constant' => 671.00 ],
							[ 'income' => 239000, 'rate' => 2.27, 'constant' => 1956.20 ],
							[ 'income' => 418000, 'rate' => 2.64, 'constant' => 4339.70 ],
							[ 'income' => 418000, 'rate' => 2.90, 'constant' => 9065.30 ],
					],
			],
			20150101 => [
					10 => [
							[ 'income' => 4300, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 41000, 'rate' => 1.22, 'constant' => 0 ],
							[ 'income' => 83000, 'rate' => 2.27, 'constant' => 447.74 ],
							[ 'income' => 191000, 'rate' => 2.52, 'constant' => 1401.14 ],
							[ 'income' => 411000, 'rate' => 2.93, 'constant' => 4122.74 ],
							[ 'income' => 411000, 'rate' => 3.22, 'constant' => 10568.00 ],
					],
					20 => [
							[ 'income' => 10000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 71000, 'rate' => 1.22, 'constant' => 0 ],
							[ 'income' => 134000, 'rate' => 2.27, 'constant' => 744.20 ],
							[ 'income' => 239000, 'rate' => 2.52, 'constant' => 2174.30 ],
							[ 'income' => 418000, 'rate' => 2.93, 'constant' => 4820.30 ],
							[ 'income' => 418000, 'rate' => 3.22, 'constant' => 10065.00 ],
					],
			],
			20140101 => [
					10 => [
							[ 'income' => 4200, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 40000, 'rate' => 1.22, 'constant' => 0 ],
							[ 'income' => 82000, 'rate' => 2.27, 'constant' => 436.76 ],
							[ 'income' => 188000, 'rate' => 2.52, 'constant' => 1390.16 ],
							[ 'income' => 405000, 'rate' => 2.93, 'constant' => 4061.36 ],
							[ 'income' => 405000, 'rate' => 3.22, 'constant' => 10416.46 ],
					],
					20 => [
							[ 'income' => 10000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 70000, 'rate' => 1.22, 'constant' => 0 ],
							[ 'income' => 132000, 'rate' => 2.27, 'constant' => 732.00 ],
							[ 'income' => 235000, 'rate' => 2.52, 'constant' => 2139.40 ],
							[ 'income' => 412000, 'rate' => 2.93, 'constant' => 4735.00 ],
							[ 'income' => 412000, 'rate' => 3.22, 'constant' => 9921.10 ],
					],
			],
			20130701 => [
					10 => [
							[ 'income' => 4100, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 39000, 'rate' => 1.22, 'constant' => 0 ],
							[ 'income' => 81000, 'rate' => 2.27, 'constant' => 425.78 ],
							[ 'income' => 185000, 'rate' => 2.52, 'constant' => 1379.18 ],
							[ 'income' => 400000, 'rate' => 2.93, 'constant' => 3999.98 ],
							[ 'income' => 400000, 'rate' => 3.22, 'constant' => 10299.48 ],
					],
					20 => [
							[ 'income' => 10000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 69000, 'rate' => 1.22, 'constant' => 0 ],
							[ 'income' => 130000, 'rate' => 2.27, 'constant' => 719.80 ],
							[ 'income' => 231000, 'rate' => 2.52, 'constant' => 2104.50 ],
							[ 'income' => 405000, 'rate' => 2.93, 'constant' => 4649.70 ],
							[ 'income' => 405000, 'rate' => 3.22, 'constant' => 9747.90 ],
					],
			],
			20130101 => [
					10 => [
							[ 'income' => 4100, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 39000, 'rate' => 1.51, 'constant' => 0 ],
							[ 'income' => 81000, 'rate' => 2.82, 'constant' => 526.99 ],
							[ 'income' => 185000, 'rate' => 3.13, 'constant' => 1711.39 ],
							[ 'income' => 400000, 'rate' => 3.63, 'constant' => 4966.59 ],
							[ 'income' => 400000, 'rate' => 3.99, 'constant' => 12771.09 ],
					],
					20 => [
							[ 'income' => 10000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 69000, 'rate' => 1.51, 'constant' => 0 ],
							[ 'income' => 130000, 'rate' => 2.82, 'constant' => 890.90 ],
							[ 'income' => 231000, 'rate' => 3.13, 'constant' => 2611.10 ],
							[ 'income' => 405000, 'rate' => 3.63, 'constant' => 5772.40 ],
							[ 'income' => 405000, 'rate' => 3.99, 'constant' => 12088.60 ],
					],
			],
			20120101 => [
					10 => [
							[ 'income' => 4000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 38000, 'rate' => 1.51, 'constant' => 0 ],
							[ 'income' => 79000, 'rate' => 2.82, 'constant' => 513.40 ],
							[ 'income' => 180000, 'rate' => 3.13, 'constant' => 1669.60 ],
							[ 'income' => 390000, 'rate' => 3.63, 'constant' => 4830.90 ],
							[ 'income' => 390000, 'rate' => 3.99, 'constant' => 12453.90 ],
					],
					20 => [
							[ 'income' => 9600, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 67000, 'rate' => 1.51, 'constant' => 0 ],
							[ 'income' => 127000, 'rate' => 2.82, 'constant' => 866.74 ],
							[ 'income' => 225000, 'rate' => 3.13, 'constant' => 2558.74 ],
							[ 'income' => 395000, 'rate' => 3.63, 'constant' => 5626.14 ],
							[ 'income' => 395000, 'rate' => 3.99, 'constant' => 11797.14 ],
					],
			],
			20110101 => [
					10 => [
							[ 'income' => 3900, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 37000, 'rate' => 1.84, 'constant' => 0 ],
							[ 'income' => 77000, 'rate' => 3.44, 'constant' => 609.04 ],
							[ 'income' => 176000, 'rate' => 3.81, 'constant' => 1985.04 ],
							[ 'income' => 380000, 'rate' => 4.42, 'constant' => 5756.94 ],
							[ 'income' => 380000, 'rate' => 4.86, 'constant' => 14773.74 ],
					],
					20 => [
							[ 'income' => 9400, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 65000, 'rate' => 1.84, 'constant' => 0 ],
							[ 'income' => 124000, 'rate' => 3.44, 'constant' => 1023.04 ],
							[ 'income' => 220000, 'rate' => 3.81, 'constant' => 3052.64 ],
							[ 'income' => 386000, 'rate' => 4.42, 'constant' => 6710.24 ],
							[ 'income' => 386000, 'rate' => 4.86, 'constant' => 14047.44 ],
					],
			],
			20100101 => [
					10 => [
							[ 'income' => 3800, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 36000, 'rate' => 1.84, 'constant' => 0 ],
							[ 'income' => 76000, 'rate' => 3.44, 'constant' => 592.48 ],
							[ 'income' => 173000, 'rate' => 3.81, 'constant' => 1968.48 ],
							[ 'income' => 376000, 'rate' => 4.42, 'constant' => 5664.18 ],
							[ 'income' => 376000, 'rate' => 4.86, 'constant' => 14636.78 ],
					],
					20 => [
							[ 'income' => 9300, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 64000, 'rate' => 1.84, 'constant' => 0 ],
							[ 'income' => 122000, 'rate' => 3.44, 'constant' => 1006.48 ],
							[ 'income' => 217000, 'rate' => 3.81, 'constant' => 3001.68 ],
							[ 'income' => 381000, 'rate' => 4.42, 'constant' => 6621.18 ],
							[ 'income' => 381000, 'rate' => 4.86, 'constant' => 13869.98 ],
					],
			],
			20090101 => [
					10 => [
							[ 'income' => 3800, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 36000, 'rate' => 2.10, 'constant' => 0 ],
							[ 'income' => 76000, 'rate' => 3.92, 'constant' => 676.20 ],
							[ 'income' => 173000, 'rate' => 4.34, 'constant' => 2244.20 ],
							[ 'income' => 375000, 'rate' => 5.04, 'constant' => 6454.00 ],
							[ 'income' => 375000, 'rate' => 5.54, 'constant' => 16634.80 ],
					],
					20 => [
							[ 'income' => 9300, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 64000, 'rate' => 2.10, 'constant' => 0 ],
							[ 'income' => 122000, 'rate' => 3.92, 'constant' => 1148.70 ],
							[ 'income' => 217000, 'rate' => 4.34, 'constant' => 3422.30 ],
							[ 'income' => 380000, 'rate' => 5.04, 'constant' => 7545.30 ],
							[ 'income' => 380000, 'rate' => 5.54, 'constant' => 15760.50 ],
					],
			],
			20080101 => [
					10 => [
							[ 'income' => 3700, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 34600, 'rate' => 2.10, 'constant' => 0 ],
							[ 'income' => 72800, 'rate' => 3.92, 'constant' => 648.90 ],
							[ 'income' => 166300, 'rate' => 4.34, 'constant' => 2146.34 ],
							[ 'income' => 359200, 'rate' => 5.04, 'constant' => 6204.24 ],
							[ 'income' => 359200, 'rate' => 5.54, 'constant' => 15926.40 ],
					],
					20 => [
							[ 'income' => 9000, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 61600, 'rate' => 2.10, 'constant' => 0 ],
							[ 'income' => 116900, 'rate' => 3.92, 'constant' => 1104.60 ],
							[ 'income' => 208200, 'rate' => 4.34, 'constant' => 3272.36 ],
							[ 'income' => 364700, 'rate' => 5.04, 'constant' => 7234.78 ],
							[ 'income' => 364700, 'rate' => 5.54, 'constant' => 15122.38 ],
					],
			],
			20070101 => [
					10 => [
							[ 'income' => 3600, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 33800, 'rate' => 2.10, 'constant' => 0 ],
							[ 'income' => 71200, 'rate' => 3.92, 'constant' => 634.20 ],
							[ 'income' => 162600, 'rate' => 4.34, 'constant' => 2100.28 ],
							[ 'income' => 351200, 'rate' => 5.04, 'constant' => 6067.04 ],
							[ 'income' => 351200, 'rate' => 5.54, 'constant' => 15572.48 ],
					],
					20 => [
							[ 'income' => 8800, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 60200, 'rate' => 2.10, 'constant' => 0 ],
							[ 'income' => 114300, 'rate' => 3.92, 'constant' => 1079.40 ],
							[ 'income' => 203600, 'rate' => 4.34, 'constant' => 3200.12 ],
							[ 'income' => 356600, 'rate' => 5.04, 'constant' => 7075.74 ],
							[ 'income' => 356600, 'rate' => 5.54, 'constant' => 14786.94 ],
					],
			],
			20060101 => [
					10 => [
							[ 'income' => 3500, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 32500, 'rate' => 2.10, 'constant' => 0 ],
							[ 'income' => 68500, 'rate' => 3.92, 'constant' => 609 ],
							[ 'income' => 156000, 'rate' => 4.34, 'constant' => 2020.20 ],
							[ 'income' => 338100, 'rate' => 5.04, 'constant' => 5839.40 ],
							[ 'income' => 338100, 'rate' => 5.54, 'constant' => 14987 ],
					],
					20 => [
							[ 'income' => 8500, 'rate' => 0, 'constant' => 0 ],
							[ 'income' => 57900, 'rate' => 2.10, 'constant' => 0 ],
							[ 'income' => 110000, 'rate' => 3.92, 'constant' => 1037.40 ],
							[ 'income' => 196000, 'rate' => 4.34, 'constant' => 3079.72 ],
							[ 'income' => 343200, 'rate' => 5.04, 'constant' => 6812.12 ],
							[ 'income' => 343200, 'rate' => 5.54, 'constant' => 14231 ],
					],
			],
	];

	var $state_options = [
			//01-Jan-21 - No Change.
			20200101 => [
					'allowance' => 4300,
			],
			20190101 => [
					'allowance' => 4200,
			],
			20180101 => [
					'allowance' => 4150,
			],
			//01-Jan-17 - No Change.
			20160101 => [
					'allowance' => 4050,
			],
			20140101 => [
					'allowance' => 3950,
			],
			20130101 => [
					'allowance' => 3900,
			],
			20120101 => [
					'allowance' => 3800,
			],
			20110101 => [
					'allowance' => 3700,
			],
			//01-Jan-10: No Change.
			20090101 => [
					'allowance' => 3650,
			],
			20080101 => [
					'allowance' => 3500,
			],
			20070101 => [
					'allowance' => 3400,
			],
			20060101 => [
					'allowance' => 3300,
			],
	];

	function getStatePayPeriodDeductionRoundedValue( $amount ) {
		if ( $this->getDate() >= 20210101 ) { //ND was always supposed to round to nearest dollar, but we weren't doing it prior to 2021.
			return $this->RoundNearestDollar( $amount );
		}

		return $amount;
	}

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();

		//01-Jan-2021 - They changed their formula to be slighly more based off the Federal W4 for 2020 and after.
		if ( $this->getDate() >= 20210101 && $this->getFederalFormW4Version() == 2020 ) {
			$state_allowance = 0;
		} else {
			//Only consider allowances if the Federal Form W4 is before 2020.
			$state_allowance = $this->getStateAllowanceAmount();
		}

		$income = bcsub( $annual_income, $state_allowance );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$allowance_arr = $retarr['allowance'];

		$retval = bcmul( $this->getStateAllowance(), $allowance_arr );

		Debug::text( 'State Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _getStateTaxPayable() {
		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getStateRate( $annual_income );
			$state_constant = $this->getData()->getStateConstant( $annual_income );
			$state_rate_income = $this->getData()->getStateRatePreviousIncome( $annual_income );

			$retval = bcadd( bcmul( bcsub( $annual_income, $state_rate_income ), $rate ), $state_constant );
			//$retval = bcadd( bcmul( $annual_income, $rate ), $state_constant );
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
