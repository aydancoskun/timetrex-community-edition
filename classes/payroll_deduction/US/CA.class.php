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
 * @package PayrollDeduction\US
 */
class PayrollDeduction_US_CA extends PayrollDeduction_US {
	/*
															10 => 'Single',
															20 => 'Married - Spouse Works',
															30 => 'Married - Spouse does not Work',
															40 => 'Head of Household',
	*/

	var $state_income_tax_rate_options = [
			20200101 => [
					10 => [
							[ 'income' => 8809, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 20883, 'rate' => 2.2, 'constant' => 96.90 ],
							[ 'income' => 32960, 'rate' => 4.4, 'constant' => 362.53 ],
							[ 'income' => 45753, 'rate' => 6.6, 'constant' => 893.92 ],
							[ 'income' => 57824, 'rate' => 8.8, 'constant' => 1738.26 ],
							[ 'income' => 295373, 'rate' => 10.23, 'constant' => 2800.51 ],
							[ 'income' => 354445, 'rate' => 11.33, 'constant' => 27101.77 ],
							[ 'income' => 590742, 'rate' => 12.43, 'constant' => 33794.63 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 63166.35 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 118538.96 ],
					],
					20 => [
							[ 'income' => 8809, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 20883, 'rate' => 2.2, 'constant' => 96.90 ],
							[ 'income' => 32960, 'rate' => 4.4, 'constant' => 362.53 ],
							[ 'income' => 45753, 'rate' => 6.6, 'constant' => 893.92 ],
							[ 'income' => 57824, 'rate' => 8.8, 'constant' => 1738.26 ],
							[ 'income' => 295373, 'rate' => 10.23, 'constant' => 2800.51 ],
							[ 'income' => 354445, 'rate' => 11.33, 'constant' => 27101.77 ],
							[ 'income' => 590742, 'rate' => 12.43, 'constant' => 33794.63 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 63166.35 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 118538.96 ],
					],
					30 => [
							[ 'income' => 17618, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 41766, 'rate' => 2.2, 'constant' => 193.80 ],
							[ 'income' => 65920, 'rate' => 4.4, 'constant' => 725.06 ],
							[ 'income' => 91506, 'rate' => 6.6, 'constant' => 1787.84 ],
							[ 'income' => 115648, 'rate' => 8.8, 'constant' => 3476.52 ],
							[ 'income' => 590746, 'rate' => 10.23, 'constant' => 5601.02 ],
							[ 'income' => 708890, 'rate' => 11.33, 'constant' => 54203.55 ],
							[ 'income' => 1000000, 'rate' => 12.43, 'constant' => 67589.27 ],
							[ 'income' => 1181484, 'rate' => 13.53, 'constant' => 103774.24 ],
							[ 'income' => 1181484, 'rate' => 14.63, 'constant' => 128329.03 ],
					],
					40 => [ //These are different than 30 above.
							[ 'income' => 17629, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 41768, 'rate' => 2.2, 'constant' => 193.92 ],
							[ 'income' => 53843, 'rate' => 4.4, 'constant' => 724.98 ],
							[ 'income' => 66636, 'rate' => 6.6, 'constant' => 1256.28 ],
							[ 'income' => 78710, 'rate' => 8.8, 'constant' => 2100.62 ],
							[ 'income' => 401705, 'rate' => 10.23, 'constant' => 3163.13 ],
							[ 'income' => 482047, 'rate' => 11.33, 'constant' => 36205.52 ],
							[ 'income' => 803410, 'rate' => 12.43, 'constant' => 45308.27 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 85253.69 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 111852.32 ],
					],
			],
			20190101 => [
					10 => [
							[ 'income' => 8544, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 20255, 'rate' => 2.2, 'constant' => 93.98 ],
							[ 'income' => 31969, 'rate' => 4.4, 'constant' => 351.62 ],
							[ 'income' => 44377, 'rate' => 6.6, 'constant' => 867.04 ],
							[ 'income' => 56085, 'rate' => 8.8, 'constant' => 1685.97 ],
							[ 'income' => 286492, 'rate' => 10.23, 'constant' => 2716.27 ],
							[ 'income' => 343788, 'rate' => 11.33, 'constant' => 26286.91 ],
							[ 'income' => 572980, 'rate' => 12.43, 'constant' => 32778.55 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 61267.12 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 119042.93 ],
					],
					20 => [
							[ 'income' => 8544, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 20255, 'rate' => 2.2, 'constant' => 93.98 ],
							[ 'income' => 31969, 'rate' => 4.4, 'constant' => 351.62 ],
							[ 'income' => 44377, 'rate' => 6.6, 'constant' => 867.04 ],
							[ 'income' => 56085, 'rate' => 8.8, 'constant' => 1685.97 ],
							[ 'income' => 286492, 'rate' => 10.23, 'constant' => 2716.27 ],
							[ 'income' => 343788, 'rate' => 11.33, 'constant' => 26286.91 ],
							[ 'income' => 572980, 'rate' => 12.43, 'constant' => 32778.55 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 61267.12 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 119042.93 ],
					],
					30 => [
							[ 'income' => 17088, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 40510, 'rate' => 2.2, 'constant' => 187.97 ],
							[ 'income' => 63938, 'rate' => 4.4, 'constant' => 703.25 ],
							[ 'income' => 88754, 'rate' => 6.6, 'constant' => 1734.08 ],
							[ 'income' => 112170, 'rate' => 8.8, 'constant' => 3371.94 ],
							[ 'income' => 572984, 'rate' => 10.23, 'constant' => 5432.55 ],
							[ 'income' => 687576, 'rate' => 11.33, 'constant' => 52573.82 ],
							[ 'income' => 1000000, 'rate' => 12.43, 'constant' => 65557.09 ],
							[ 'income' => 1145961, 'rate' => 13.53, 'constant' => 104391.39 ],
							[ 'income' => 1145961, 'rate' => 14.63, 'constant' => 124139.90 ],
					],
					40 => [ //These are different than 30 above.
							[ 'income' => 17099, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 40512, 'rate' => 2.2, 'constant' => 188.09 ],
							[ 'income' => 52224, 'rate' => 4.4, 'constant' => 703.18 ],
							[ 'income' => 64632, 'rate' => 6.6, 'constant' => 1218.51 ],
							[ 'income' => 76343, 'rate' => 8.8, 'constant' => 2037.44 ],
							[ 'income' => 389627, 'rate' => 10.23, 'constant' => 3068.01 ],
							[ 'income' => 467553, 'rate' => 11.33, 'constant' => 35116.96 ],
							[ 'income' => 779253, 'rate' => 12.43, 'constant' => 43945.98 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 82690.29 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 112557.36 ],
					],
			],
			20180101 => [
					10 => [
							[ 'income' => 8223, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 19495, 'rate' => 2.2, 'constant' => 90.45 ],
							[ 'income' => 30769, 'rate' => 4.4, 'constant' => 338.43 ],
							[ 'income' => 42711, 'rate' => 6.6, 'constant' => 834.49 ],
							[ 'income' => 53980, 'rate' => 8.8, 'constant' => 1622.66 ],
							[ 'income' => 275738, 'rate' => 10.23, 'constant' => 2614.33 ],
							[ 'income' => 330884, 'rate' => 11.33, 'constant' => 25300.17 ],
							[ 'income' => 551473, 'rate' => 12.43, 'constant' => 31548.21 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 58967.42 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 119653.12 ],
					],
					20 => [
							[ 'income' => 8223, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 19495, 'rate' => 2.2, 'constant' => 90.45 ],
							[ 'income' => 30769, 'rate' => 4.4, 'constant' => 338.43 ],
							[ 'income' => 42711, 'rate' => 6.6, 'constant' => 834.49 ],
							[ 'income' => 53980, 'rate' => 8.8, 'constant' => 1622.66 ],
							[ 'income' => 275738, 'rate' => 10.23, 'constant' => 2614.33 ],
							[ 'income' => 330884, 'rate' => 11.33, 'constant' => 25300.17 ],
							[ 'income' => 551473, 'rate' => 12.43, 'constant' => 31548.21 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 58967.42 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 119653.12 ],
					],
					30 => [
							[ 'income' => 16446, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 38990, 'rate' => 2.2, 'constant' => 180.91 ],
							[ 'income' => 61538, 'rate' => 4.4, 'constant' => 676.88 ],
							[ 'income' => 85422, 'rate' => 6.6, 'constant' => 1668.99 ],
							[ 'income' => 107960, 'rate' => 8.8, 'constant' => 3245.33 ],
							[ 'income' => 551476, 'rate' => 10.23, 'constant' => 5228.67 ],
							[ 'income' => 661768, 'rate' => 11.33, 'constant' => 50600.36 ],
							[ 'income' => 1000000, 'rate' => 12.43, 'constant' => 63096.44 ],
							[ 'income' => 1102946, 'rate' => 13.53, 'constant' => 105138.68 ],
							[ 'income' => 1102946, 'rate' => 14.63, 'constant' => 119067.26 ],
					],
					40 => [ //These are different than 30 above.
							[ 'income' => 16457, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 38991, 'rate' => 2.2, 'constant' => 181.03 ],
							[ 'income' => 50264, 'rate' => 4.4, 'constant' => 676.78 ],
							[ 'income' => 62206, 'rate' => 6.6, 'constant' => 1172.79 ],
							[ 'income' => 73477, 'rate' => 8.8, 'constant' => 1960.96 ],
							[ 'income' => 375002, 'rate' => 10.23, 'constant' => 2952.81 ],
							[ 'income' => 450003, 'rate' => 11.33, 'constant' => 33798.82 ],
							[ 'income' => 750003, 'rate' => 12.43, 'constant' => 42296.43 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 79586.43 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 113411.02 ],
					],
			],
			20170101 => [
					10 => [
							[ 'income' => 8015, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 19001, 'rate' => 2.2, 'constant' => 88.17 ],
							[ 'income' => 29989, 'rate' => 4.4, 'constant' => 329.86 ],
							[ 'income' => 41629, 'rate' => 6.6, 'constant' => 813.33 ],
							[ 'income' => 52612, 'rate' => 8.8, 'constant' => 1581.57 ],
							[ 'income' => 268750, 'rate' => 10.23, 'constant' => 2548.07 ],
							[ 'income' => 322499, 'rate' => 11.33, 'constant' => 24658.99 ],
							[ 'income' => 537498, 'rate' => 12.43, 'constant' => 30748.75 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 57473.13 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 120049.65 ],
					],
					20 => [
							[ 'income' => 8015, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 19001, 'rate' => 2.2, 'constant' => 88.17 ],
							[ 'income' => 29989, 'rate' => 4.4, 'constant' => 329.86 ],
							[ 'income' => 41629, 'rate' => 6.6, 'constant' => 813.33 ],
							[ 'income' => 52612, 'rate' => 8.8, 'constant' => 1581.57 ],
							[ 'income' => 268750, 'rate' => 10.23, 'constant' => 2548.07 ],
							[ 'income' => 322499, 'rate' => 11.33, 'constant' => 24658.99 ],
							[ 'income' => 537498, 'rate' => 12.43, 'constant' => 30748.75 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 57473.13 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 120049.65 ],
					],
					30 => [
							[ 'income' => 16030, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 38002, 'rate' => 2.2, 'constant' => 176.33 ],
							[ 'income' => 59978, 'rate' => 4.4, 'constant' => 659.71 ],
							[ 'income' => 83258, 'rate' => 6.6, 'constant' => 1626.65 ],
							[ 'income' => 105224, 'rate' => 8.8, 'constant' => 3163.13 ],
							[ 'income' => 537500, 'rate' => 10.23, 'constant' => 5096.14 ],
							[ 'income' => 644998, 'rate' => 11.33, 'constant' => 49317.97 ],
							[ 'income' => 1000000, 'rate' => 12.43, 'constant' => 61497.49 ],
							[ 'income' => 1074996, 'rate' => 13.53, 'constant' => 105624.24 ],
							[ 'income' => 1074996, 'rate' => 14.63, 'constant' => 115771.20 ],
					],
					40 => [ //These are different than 30 above.
							[ 'income' => 16040, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 38003, 'rate' => 2.2, 'constant' => 176.44 ],
							[ 'income' => 48990, 'rate' => 4.4, 'constant' => 659.63 ],
							[ 'income' => 60630, 'rate' => 6.6, 'constant' => 1143.06 ],
							[ 'income' => 71615, 'rate' => 8.8, 'constant' => 1911.30 ],
							[ 'income' => 365499, 'rate' => 10.23, 'constant' => 2877.98 ],
							[ 'income' => 438599, 'rate' => 11.33, 'constant' => 32942.31 ],
							[ 'income' => 730997, 'rate' => 12.43, 'constant' => 41224.54 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 77569.61 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 113965.72 ],
					],
			],
			20160101 => [
					10 => [
							[ 'income' => 7850, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 18610, 'rate' => 2.2, 'constant' => 86.35 ],
							[ 'income' => 29372, 'rate' => 4.4, 'constant' => 323.07 ],
							[ 'income' => 40773, 'rate' => 6.6, 'constant' => 796.60 ],
							[ 'income' => 51530, 'rate' => 8.8, 'constant' => 1549.07 ],
							[ 'income' => 263222, 'rate' => 10.23, 'constant' => 2495.69 ],
							[ 'income' => 315866, 'rate' => 11.33, 'constant' => 24151.78 ],
							[ 'income' => 526443, 'rate' => 12.43, 'constant' => 30116.35 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 56291.07 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 120363.33 ],
					],
					20 => [
							[ 'income' => 7850, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 18610, 'rate' => 2.2, 'constant' => 86.35 ],
							[ 'income' => 29372, 'rate' => 4.4, 'constant' => 323.07 ],
							[ 'income' => 40773, 'rate' => 6.6, 'constant' => 796.60 ],
							[ 'income' => 51530, 'rate' => 8.8, 'constant' => 1549.07 ],
							[ 'income' => 263222, 'rate' => 10.23, 'constant' => 2495.69 ],
							[ 'income' => 315866, 'rate' => 11.33, 'constant' => 24151.78 ],
							[ 'income' => 526443, 'rate' => 12.43, 'constant' => 30116.35 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 56291.07 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 120363.33 ],
					],
					30 => [
							[ 'income' => 15700, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 37220, 'rate' => 2.2, 'constant' => 172.70 ],
							[ 'income' => 58744, 'rate' => 4.4, 'constant' => 646.14 ],
							[ 'income' => 81546, 'rate' => 6.6, 'constant' => 1593.20 ],
							[ 'income' => 103060, 'rate' => 8.8, 'constant' => 3098.13 ],
							[ 'income' => 526444, 'rate' => 10.23, 'constant' => 4991.36 ],
							[ 'income' => 631732, 'rate' => 11.33, 'constant' => 48303.54 ],
							[ 'income' => 1000000, 'rate' => 12.43, 'constant' => 60232.67 ],
							[ 'income' => 1052886, 'rate' => 13.53, 'constant' => 106008.38 ],
							[ 'income' => 1052886, 'rate' => 14.63, 'constant' => 113163.86 ],
					],
					40 => [
							[ 'income' => 15700, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 37221, 'rate' => 2.2, 'constant' => 172.81 ],
							[ 'income' => 47982, 'rate' => 4.4, 'constant' => 646.05 ],
							[ 'income' => 59383, 'rate' => 6.6, 'constant' => 1119.53 ],
							[ 'income' => 70142, 'rate' => 8.8, 'constant' => 1872.00 ],
							[ 'income' => 357981, 'rate' => 10.23, 'constant' => 2818.79 ],
							[ 'income' => 429578, 'rate' => 11.33, 'constant' => 32264.72 ],
							[ 'income' => 715962, 'rate' => 12.43, 'constant' => 40376.66 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 75974.19 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 114404.53 ],
					],
			],
			20150101 => [
					10 => [
							[ 'income' => 7749, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 18371, 'rate' => 2.2, 'constant' => 85.24 ],
							[ 'income' => 28995, 'rate' => 4.4, 'constant' => 318.92 ],
							[ 'income' => 40250, 'rate' => 6.6, 'constant' => 786.38 ],
							[ 'income' => 50869, 'rate' => 8.8, 'constant' => 1529.21 ],
							[ 'income' => 259844, 'rate' => 10.23, 'constant' => 2463.68 ],
							[ 'income' => 311812, 'rate' => 11.33, 'constant' => 23841.82 ],
							[ 'income' => 519687, 'rate' => 12.43, 'constant' => 29729.79 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 55568.65 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 120555.00 ],
					],
					20 => [
							[ 'income' => 7749, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 18371, 'rate' => 2.2, 'constant' => 85.24 ],
							[ 'income' => 28995, 'rate' => 4.4, 'constant' => 318.92 ],
							[ 'income' => 40250, 'rate' => 6.6, 'constant' => 786.38 ],
							[ 'income' => 50869, 'rate' => 8.8, 'constant' => 1529.21 ],
							[ 'income' => 259844, 'rate' => 10.23, 'constant' => 2463.68 ],
							[ 'income' => 311812, 'rate' => 11.33, 'constant' => 23841.82 ],
							[ 'income' => 519687, 'rate' => 12.43, 'constant' => 29729.79 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 55568.65 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 120555.00 ],
					],
					30 => [
							[ 'income' => 15498, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 36742, 'rate' => 2.2, 'constant' => 170.48 ],
							[ 'income' => 57990, 'rate' => 4.4, 'constant' => 637.85 ],
							[ 'income' => 80500, 'rate' => 6.6, 'constant' => 1572.76 ],
							[ 'income' => 101738, 'rate' => 8.8, 'constant' => 3058.42 ],
							[ 'income' => 519688, 'rate' => 10.23, 'constant' => 4927.36 ],
							[ 'income' => 623624, 'rate' => 11.33, 'constant' => 47683.65 ],
							[ 'income' => 1000000, 'rate' => 12.43, 'constant' => 59459.60 ],
							[ 'income' => 1039000, 'rate' => 13.53, 'constant' => 106243.14 ],
							[ 'income' => 1039000, 'rate' => 14.63, 'constant' => 111570.44 ],
					],
					40 => [
							[ 'income' => 15508, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 36743, 'rate' => 2.2, 'constant' => 170.59 ],
							[ 'income' => 47366, 'rate' => 4.4, 'constant' => 637.76 ],
							[ 'income' => 58621, 'rate' => 6.6, 'constant' => 1105.17 ],
							[ 'income' => 69242, 'rate' => 8.8, 'constant' => 1848.00 ],
							[ 'income' => 353387, 'rate' => 10.23, 'constant' => 2782.65 ],
							[ 'income' => 424065, 'rate' => 11.33, 'constant' => 31850.68 ],
							[ 'income' => 706774, 'rate' => 12.43, 'constant' => 39858.50 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 74999.23 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 114672.71 ],
					],
			],
			20140101 => [
					10 => [
							[ 'income' => 7582, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 17976, 'rate' => 2.2, 'constant' => 83.40 ],
							[ 'income' => 28371, 'rate' => 4.4, 'constant' => 312.07 ],
							[ 'income' => 39384, 'rate' => 6.6, 'constant' => 769.45 ],
							[ 'income' => 49774, 'rate' => 8.8, 'constant' => 1496.31 ],
							[ 'income' => 254250, 'rate' => 10.23, 'constant' => 2410.63 ],
							[ 'income' => 305100, 'rate' => 11.33, 'constant' => 23328.52 ],
							[ 'income' => 508500, 'rate' => 12.43, 'constant' => 29089.83 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 54372.45 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 120872.40 ],
					],
					20 => [
							[ 'income' => 7582, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 17976, 'rate' => 2.2, 'constant' => 83.40 ],
							[ 'income' => 28371, 'rate' => 4.4, 'constant' => 312.07 ],
							[ 'income' => 39384, 'rate' => 6.6, 'constant' => 769.45 ],
							[ 'income' => 49774, 'rate' => 8.8, 'constant' => 1496.31 ],
							[ 'income' => 254250, 'rate' => 10.23, 'constant' => 2410.63 ],
							[ 'income' => 305100, 'rate' => 11.33, 'constant' => 23328.52 ],
							[ 'income' => 508500, 'rate' => 12.43, 'constant' => 29089.83 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 54372.45 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 120872.40 ],
					],
					30 => [
							[ 'income' => 15164, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 35952, 'rate' => 2.2, 'constant' => 166.80 ],
							[ 'income' => 56742, 'rate' => 4.4, 'constant' => 624.14 ],
							[ 'income' => 78768, 'rate' => 6.6, 'constant' => 1538.90 ],
							[ 'income' => 99548, 'rate' => 8.8, 'constant' => 2992.62 ],
							[ 'income' => 508500, 'rate' => 10.23, 'constant' => 4821.26 ],
							[ 'income' => 610200, 'rate' => 11.33, 'constant' => 46657.05 ],
							[ 'income' => 1000000, 'rate' => 12.43, 'constant' => 58179.66 ],
							[ 'income' => 1017000, 'rate' => 13.53, 'constant' => 106631.80 ],
							[ 'income' => 1017000, 'rate' => 14.63, 'constant' => 108931.90 ],
					],
					40 => [
							[ 'income' => 15174, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 35952, 'rate' => 2.2, 'constant' => 166.91 ],
							[ 'income' => 46346, 'rate' => 4.4, 'constant' => 624.03 ],
							[ 'income' => 57359, 'rate' => 6.6, 'constant' => 1081.37 ],
							[ 'income' => 67751, 'rate' => 8.8, 'constant' => 1808.23 ],
							[ 'income' => 345780, 'rate' => 10.23, 'constant' => 2722.73 ],
							[ 'income' => 414936, 'rate' => 11.33, 'constant' => 31165.10 ],
							[ 'income' => 691560, 'rate' => 12.43, 'constant' => 39000.47 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 73384.83 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 115116.76 ],
					],
			],
			20130101 => [
					10 => [
							[ 'income' => 7455, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 17676, 'rate' => 2.2, 'constant' => 82.01 ],
							[ 'income' => 27897, 'rate' => 4.4, 'constant' => 306.87 ],
							[ 'income' => 38726, 'rate' => 6.6, 'constant' => 756.59 ],
							[ 'income' => 48942, 'rate' => 8.8, 'constant' => 1471.30 ],
							[ 'income' => 250000, 'rate' => 10.23, 'constant' => 2370.31 ],
							[ 'income' => 300000, 'rate' => 11.33, 'constant' => 22938.54 ],
							[ 'income' => 500000, 'rate' => 12.43, 'constant' => 28603.54 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 53463.54 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 121113.54 ],
					],
					20 => [
							[ 'income' => 7455, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 17676, 'rate' => 2.2, 'constant' => 82.01 ],
							[ 'income' => 27897, 'rate' => 4.4, 'constant' => 306.87 ],
							[ 'income' => 38726, 'rate' => 6.6, 'constant' => 756.59 ],
							[ 'income' => 48942, 'rate' => 8.8, 'constant' => 1471.30 ],
							[ 'income' => 250000, 'rate' => 10.23, 'constant' => 2370.31 ],
							[ 'income' => 300000, 'rate' => 11.33, 'constant' => 22938.54 ],
							[ 'income' => 500000, 'rate' => 12.43, 'constant' => 28603.54 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 53463.54 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 121113.54 ],
					],
					30 => [
							[ 'income' => 14910, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 35352, 'rate' => 2.2, 'constant' => 164.01 ],
							[ 'income' => 55794, 'rate' => 4.4, 'constant' => 613.73 ],
							[ 'income' => 77452, 'rate' => 6.6, 'constant' => 1513.18 ],
							[ 'income' => 97884, 'rate' => 8.8, 'constant' => 2942.61 ],
							[ 'income' => 500000, 'rate' => 10.23, 'constant' => 4740.63 ],
							[ 'income' => 600000, 'rate' => 11.33, 'constant' => 45877.10 ],
							[ 'income' => 1000000, 'rate' => 12.43, 'constant' => 57207.10 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 106927.10 ],
					],
					40 => [
							[ 'income' => 14920, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 35351, 'rate' => 2.2, 'constant' => 164.12 ],
							[ 'income' => 45571, 'rate' => 4.4, 'constant' => 613.60 ],
							[ 'income' => 56400, 'rate' => 6.6, 'constant' => 1063.28 ],
							[ 'income' => 66618, 'rate' => 8.8, 'constant' => 1777.99 ],
							[ 'income' => 340000, 'rate' => 10.23, 'constant' => 2677.17 ],
							[ 'income' => 408000, 'rate' => 11.33, 'constant' => 30644.15 ],
							[ 'income' => 680000, 'rate' => 12.43, 'constant' => 38348.55 ],
							[ 'income' => 1000000, 'rate' => 13.53, 'constant' => 72158.15 ],
							[ 'income' => 1000000, 'rate' => 14.63, 'constant' => 115454.15 ],
					],
			],
			20120101 => [
					10 => [
							[ 'income' => 7316, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 17346, 'rate' => 2.2, 'constant' => 80.48 ],
							[ 'income' => 27377, 'rate' => 4.4, 'constant' => 301.14 ],
							[ 'income' => 38004, 'rate' => 6.6, 'constant' => 742.50 ],
							[ 'income' => 48029, 'rate' => 8.8, 'constant' => 1443.88 ],
							[ 'income' => 1000000, 'rate' => 10.23, 'constant' => 2326.08 ],
							[ 'income' => 1000000, 'rate' => 11.33, 'constant' => 99712.71 ],
					],
					20 => [
							[ 'income' => 7316, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 17346, 'rate' => 2.2, 'constant' => 80.48 ],
							[ 'income' => 27377, 'rate' => 4.4, 'constant' => 301.14 ],
							[ 'income' => 38004, 'rate' => 6.6, 'constant' => 742.50 ],
							[ 'income' => 48029, 'rate' => 8.8, 'constant' => 1443.88 ],
							[ 'income' => 1000000, 'rate' => 10.23, 'constant' => 2326.08 ],
							[ 'income' => 1000000, 'rate' => 11.33, 'constant' => 99712.71 ],
					],
					30 => [
							[ 'income' => 14632, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 34692, 'rate' => 2.2, 'constant' => 160.95 ],
							[ 'income' => 54754, 'rate' => 4.4, 'constant' => 602.27 ],
							[ 'income' => 76008, 'rate' => 6.6, 'constant' => 1485.00 ],
							[ 'income' => 96058, 'rate' => 8.8, 'constant' => 2887.76 ],
							[ 'income' => 1000000, 'rate' => 10.23, 'constant' => 4652.16 ],
							[ 'income' => 1000000, 'rate' => 11.33, 'constant' => 97125.43 ],
					],
					40 => [
							[ 'income' => 14642, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 34692, 'rate' => 2.2, 'constant' => 161.06 ],
							[ 'income' => 44721, 'rate' => 4.4, 'constant' => 602.16 ],
							[ 'income' => 55348, 'rate' => 6.6, 'constant' => 1043.44 ],
							[ 'income' => 65376, 'rate' => 8.8, 'constant' => 1744.82 ],
							[ 'income' => 1000000, 'rate' => 10.23, 'constant' => 2627.28 ],
							[ 'income' => 1000000, 'rate' => 11.33, 'constant' => 98239.32 ],
					],
			],
			20110101 => [
					10 => [
							[ 'income' => 7124, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 16890, 'rate' => 2.2, 'constant' => 78.36 ],
							[ 'income' => 26657, 'rate' => 4.4, 'constant' => 293.21 ],
							[ 'income' => 37005, 'rate' => 6.6, 'constant' => 722.96 ],
							[ 'income' => 46766, 'rate' => 8.8, 'constant' => 1405.93 ],
							[ 'income' => 1000000, 'rate' => 10.23, 'constant' => 2264.90 ],
							[ 'income' => 1000000, 'rate' => 11.33, 'constant' => 99780.74 ],
					],
					20 => [
							[ 'income' => 7124, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 16890, 'rate' => 2.2, 'constant' => 78.36 ],
							[ 'income' => 26657, 'rate' => 4.4, 'constant' => 293.21 ],
							[ 'income' => 37005, 'rate' => 6.6, 'constant' => 722.96 ],
							[ 'income' => 46766, 'rate' => 8.8, 'constant' => 1405.93 ],
							[ 'income' => 1000000, 'rate' => 10.23, 'constant' => 2264.90 ],
							[ 'income' => 1000000, 'rate' => 11.33, 'constant' => 99780.74 ],
					],
					30 => [
							[ 'income' => 14248, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 33780, 'rate' => 2.2, 'constant' => 156.73 ],
							[ 'income' => 53314, 'rate' => 4.4, 'constant' => 586.43 ],
							[ 'income' => 74010, 'rate' => 6.6, 'constant' => 1445.93 ],
							[ 'income' => 93532, 'rate' => 8.8, 'constant' => 2811.87 ],
							[ 'income' => 1000000, 'rate' => 10.23, 'constant' => 4529.81 ],
							[ 'income' => 1000000, 'rate' => 11.33, 'constant' => 97261.49 ],
					],
					40 => [
							[ 'income' => 14257, 'rate' => 1.1, 'constant' => 0 ],
							[ 'income' => 33780, 'rate' => 2.2, 'constant' => 156.83 ],
							[ 'income' => 43545, 'rate' => 4.4, 'constant' => 586.34 ],
							[ 'income' => 53893, 'rate' => 6.6, 'constant' => 1016.00 ],
							[ 'income' => 63657, 'rate' => 8.8, 'constant' => 1698.97 ],
							[ 'income' => 1000000, 'rate' => 10.23, 'constant' => 2558.20 ],
							[ 'income' => 1000000, 'rate' => 11.33, 'constant' => 98346.09 ],
					],
			],
			20100101 => [
					10 => [
							[ 'income' => 7060, 'rate' => 1.375, 'constant' => 0 ],
							[ 'income' => 16739, 'rate' => 2.475, 'constant' => 97.08 ],
							[ 'income' => 26419, 'rate' => 4.675, 'constant' => 336.64 ],
							[ 'income' => 36675, 'rate' => 6.875, 'constant' => 789.18 ],
							[ 'income' => 46349, 'rate' => 9.075, 'constant' => 1494.28 ],
							[ 'income' => 1000000, 'rate' => 10.505, 'constant' => 2372.20 ],
							[ 'income' => 1000000, 'rate' => 11.605, 'constant' => 102553.24 ],
					],
					20 => [
							[ 'income' => 7060, 'rate' => 1.375, 'constant' => 0 ],
							[ 'income' => 16739, 'rate' => 2.475, 'constant' => 97.08 ],
							[ 'income' => 26419, 'rate' => 4.675, 'constant' => 336.64 ],
							[ 'income' => 36675, 'rate' => 6.875, 'constant' => 789.18 ],
							[ 'income' => 46349, 'rate' => 9.075, 'constant' => 1494.28 ],
							[ 'income' => 1000000, 'rate' => 10.505, 'constant' => 2372.20 ],
							[ 'income' => 1000000, 'rate' => 11.605, 'constant' => 102553.24 ],
					],
					30 => [
							[ 'income' => 14120, 'rate' => 1.375, 'constant' => 0 ],
							[ 'income' => 33478, 'rate' => 2.475, 'constant' => 194.15 ],
							[ 'income' => 52838, 'rate' => 4.675, 'constant' => 673.26 ],
							[ 'income' => 73350, 'rate' => 6.875, 'constant' => 1578.34 ],
							[ 'income' => 92698, 'rate' => 9.075, 'constant' => 2988.54 ],
							[ 'income' => 1000000, 'rate' => 10.505, 'constant' => 4744.37 ],
							[ 'income' => 1000000, 'rate' => 11.605, 'constant' => 100056.45 ],
					],
					40 => [
							[ 'income' => 14130, 'rate' => 1.375, 'constant' => 0 ],
							[ 'income' => 33479, 'rate' => 2.475, 'constant' => 194.29 ],
							[ 'income' => 43157, 'rate' => 4.675, 'constant' => 673.18 ],
							[ 'income' => 53412, 'rate' => 6.875, 'constant' => 1125.63 ],
							[ 'income' => 63089, 'rate' => 9.075, 'constant' => 1830.66 ],
							[ 'income' => 1000000, 'rate' => 10.505, 'constant' => 2708.85 ],
							[ 'income' => 1000000, 'rate' => 11.605, 'constant' => 101131.35 ],
					],
			],
			20091101 => [
					10 => [
							[ 'income' => 7168, 'rate' => 1.375, 'constant' => 0 ],
							[ 'income' => 16994, 'rate' => 2.475, 'constant' => 98.56 ],
							[ 'income' => 26821, 'rate' => 4.675, 'constant' => 341.75 ],
							[ 'income' => 37233, 'rate' => 6.875, 'constant' => 801.16 ],
							[ 'income' => 47055, 'rate' => 9.075, 'constant' => 1516.99 ],
							[ 'income' => 1000000, 'rate' => 10.505, 'constant' => 2408.34 ],
							[ 'income' => 1000000, 'rate' => 11.605, 'constant' => 102515.21 ],
					],
					20 => [
							[ 'income' => 7168, 'rate' => 1.375, 'constant' => 0 ],
							[ 'income' => 16994, 'rate' => 2.475, 'constant' => 98.56 ],
							[ 'income' => 26821, 'rate' => 4.675, 'constant' => 341.75 ],
							[ 'income' => 37233, 'rate' => 6.875, 'constant' => 801.16 ],
							[ 'income' => 47055, 'rate' => 9.075, 'constant' => 1516.99 ],
							[ 'income' => 1000000, 'rate' => 10.505, 'constant' => 2408.34 ],
							[ 'income' => 1000000, 'rate' => 11.605, 'constant' => 102515.21 ],
					],
					30 => [
							[ 'income' => 14336, 'rate' => 1.375, 'constant' => 0 ],
							[ 'income' => 33988, 'rate' => 2.475, 'constant' => 197.12 ],
							[ 'income' => 53642, 'rate' => 4.675, 'constant' => 683.51 ],
							[ 'income' => 74466, 'rate' => 6.875, 'constant' => 1602.33 ],
							[ 'income' => 94110, 'rate' => 9.075, 'constant' => 3033.98 ],
							[ 'income' => 1000000, 'rate' => 10.505, 'constant' => 4816.67 ],
							[ 'income' => 1000000, 'rate' => 11.605, 'constant' => 99980.41 ],
					],
					40 => [
							[ 'income' => 14345, 'rate' => 1.375, 'constant' => 0 ],
							[ 'income' => 33989, 'rate' => 2.475, 'constant' => 197.24 ],
							[ 'income' => 43814, 'rate' => 4.675, 'constant' => 683.43 ],
							[ 'income' => 54225, 'rate' => 6.875, 'constant' => 1142.75 ],
							[ 'income' => 64050, 'rate' => 9.075, 'constant' => 1858.51 ],
							[ 'income' => 1000000, 'rate' => 10.505, 'constant' => 2750.13 ],
							[ 'income' => 1000000, 'rate' => 11.605, 'constant' => 101071.68 ],
					],
			],
			20090501 => [
					10 => [
							[ 'income' => 7168, 'rate' => 1.25, 'constant' => 0 ],
							[ 'income' => 16994, 'rate' => 2.25, 'constant' => 89.60 ],
							[ 'income' => 26821, 'rate' => 4.25, 'constant' => 310.69 ],
							[ 'income' => 37233, 'rate' => 6.25, 'constant' => 728.34 ],
							[ 'income' => 47055, 'rate' => 8.25, 'constant' => 1379.09 ],
							[ 'income' => 1000000, 'rate' => 9.55, 'constant' => 2189.41 ],
							[ 'income' => 1000000, 'rate' => 10.55, 'constant' => 93195.66 ],
					],
					20 => [
							[ 'income' => 7168, 'rate' => 1.25, 'constant' => 0 ],
							[ 'income' => 16994, 'rate' => 2.25, 'constant' => 89.60 ],
							[ 'income' => 26821, 'rate' => 4.25, 'constant' => 310.69 ],
							[ 'income' => 37233, 'rate' => 6.25, 'constant' => 728.34 ],
							[ 'income' => 47055, 'rate' => 8.25, 'constant' => 1379.09 ],
							[ 'income' => 1000000, 'rate' => 9.55, 'constant' => 2189.41 ],
							[ 'income' => 1000000, 'rate' => 10.55, 'constant' => 93195.66 ],
					],
					30 => [
							[ 'income' => 14336, 'rate' => 1.25, 'constant' => 0 ],
							[ 'income' => 33988, 'rate' => 2.25, 'constant' => 179.20 ],
							[ 'income' => 53642, 'rate' => 4.25, 'constant' => 621.37 ],
							[ 'income' => 74466, 'rate' => 6.25, 'constant' => 1456.67 ],
							[ 'income' => 94110, 'rate' => 8.25, 'constant' => 2758.17 ],
							[ 'income' => 1000000, 'rate' => 9.55, 'constant' => 4378.80 ],
							[ 'income' => 1000000, 'rate' => 10.55, 'constant' => 90891.30 ],
					],
					40 => [
							[ 'income' => 14345, 'rate' => 1.25, 'constant' => 0 ],
							[ 'income' => 33989, 'rate' => 2.25, 'constant' => 179.31 ],
							[ 'income' => 43814, 'rate' => 4.25, 'constant' => 621.30 ],
							[ 'income' => 54225, 'rate' => 6.25, 'constant' => 1038.86 ],
							[ 'income' => 64050, 'rate' => 8.25, 'constant' => 1689.55 ],
							[ 'income' => 1000000, 'rate' => 9.55, 'constant' => 2500.11 ],
							[ 'income' => 1000000, 'rate' => 10.55, 'constant' => 91883.34 ],
					],
			],
			20090101 => [
					10 => [
							[ 'income' => 7168, 'rate' => 1.0, 'constant' => 0 ],
							[ 'income' => 16994, 'rate' => 2.0, 'constant' => 71.68 ],
							[ 'income' => 26821, 'rate' => 4.0, 'constant' => 268.20 ],
							[ 'income' => 37233, 'rate' => 6.0, 'constant' => 661.28 ],
							[ 'income' => 47055, 'rate' => 8.0, 'constant' => 1286.00 ],
							[ 'income' => 1000000, 'rate' => 9.3, 'constant' => 2071.76 ],
							[ 'income' => 1000000, 'rate' => 10.3, 'constant' => 90695.65 ],
					],
					20 => [
							[ 'income' => 7168, 'rate' => 1.0, 'constant' => 0 ],
							[ 'income' => 16994, 'rate' => 2.0, 'constant' => 71.68 ],
							[ 'income' => 26821, 'rate' => 4.0, 'constant' => 268.20 ],
							[ 'income' => 37233, 'rate' => 6.0, 'constant' => 661.28 ],
							[ 'income' => 47055, 'rate' => 8.0, 'constant' => 1286.00 ],
							[ 'income' => 1000000, 'rate' => 9.3, 'constant' => 2071.76 ],
							[ 'income' => 1000000, 'rate' => 10.3, 'constant' => 90695.65 ],
					],
					30 => [
							[ 'income' => 14336, 'rate' => 1.0, 'constant' => 0 ],
							[ 'income' => 33988, 'rate' => 2.0, 'constant' => 143.36 ],
							[ 'income' => 53642, 'rate' => 4.0, 'constant' => 536.40 ],
							[ 'income' => 74466, 'rate' => 6.0, 'constant' => 1322.56 ],
							[ 'income' => 94110, 'rate' => 8.0, 'constant' => 2572.00 ],
							[ 'income' => 1000000, 'rate' => 9.3, 'constant' => 4143.52 ],
							[ 'income' => 1000000, 'rate' => 10.3, 'constant' => 88391.29 ],
					],
					40 => [
							[ 'income' => 14345, 'rate' => 1.0, 'constant' => 0 ],
							[ 'income' => 33989, 'rate' => 2.0, 'constant' => 143.45 ],
							[ 'income' => 43814, 'rate' => 4.0, 'constant' => 536.33 ],
							[ 'income' => 54225, 'rate' => 6.0, 'constant' => 929.33 ],
							[ 'income' => 64050, 'rate' => 8.0, 'constant' => 1553.99 ],
							[ 'income' => 1000000, 'rate' => 9.3, 'constant' => 2339.99 ],
							[ 'income' => 1000000, 'rate' => 10.3, 'constant' => 89383.34 ],
					],
			],
			20080101 => [
					10 => [
							[ 'income' => 6827, 'rate' => 1.0, 'constant' => 0 ],
							[ 'income' => 16185, 'rate' => 2.0, 'constant' => 68.27 ],
							[ 'income' => 25544, 'rate' => 4.0, 'constant' => 255.43 ],
							[ 'income' => 35460, 'rate' => 6.0, 'constant' => 629.79 ],
							[ 'income' => 44814, 'rate' => 8.0, 'constant' => 1224.75 ],
							[ 'income' => 999999, 'rate' => 9.3, 'constant' => 1973.07 ],
							[ 'income' => 999999, 'rate' => 10.3, 'constant' => 90805.28 ],
					],
					20 => [
							[ 'income' => 6827, 'rate' => 1.0, 'constant' => 0 ],
							[ 'income' => 16185, 'rate' => 2.0, 'constant' => 68.27 ],
							[ 'income' => 25544, 'rate' => 4.0, 'constant' => 255.43 ],
							[ 'income' => 35460, 'rate' => 6.0, 'constant' => 629.79 ],
							[ 'income' => 44814, 'rate' => 8.0, 'constant' => 1224.75 ],
							[ 'income' => 999999, 'rate' => 9.3, 'constant' => 1973.07 ],
							[ 'income' => 999999, 'rate' => 10.3, 'constant' => 90805.28 ],
					],
					30 => [
							[ 'income' => 13654, 'rate' => 1.0, 'constant' => 0 ],
							[ 'income' => 32370, 'rate' => 2.0, 'constant' => 136.54 ],
							[ 'income' => 51088, 'rate' => 4.0, 'constant' => 510.86 ],
							[ 'income' => 70920, 'rate' => 6.0, 'constant' => 1259.58 ],
							[ 'income' => 89628, 'rate' => 8.0, 'constant' => 2449.50 ],
							[ 'income' => 999999, 'rate' => 9.3, 'constant' => 3946.14 ],
							[ 'income' => 999999, 'rate' => 10.3, 'constant' => 88610.64 ],
					],
					40 => [
							[ 'income' => 13662, 'rate' => 1.0, 'constant' => 0 ],
							[ 'income' => 32370, 'rate' => 2.0, 'constant' => 136.62 ],
							[ 'income' => 41728, 'rate' => 4.0, 'constant' => 510.78 ],
							[ 'income' => 51643, 'rate' => 6.0, 'constant' => 885.10 ],
							[ 'income' => 61000, 'rate' => 8.0, 'constant' => 1480.00 ],
							[ 'income' => 999999, 'rate' => 9.3, 'constant' => 2228.56 ],
							[ 'income' => 999999, 'rate' => 10.3, 'constant' => 89555.47 ],
					],
			],
			20070101 => [
					10 => [
							[ 'income' => 6622, 'rate' => 1.0, 'constant' => 0 ],
							[ 'income' => 15698, 'rate' => 2.0, 'constant' => 66.22 ],
							[ 'income' => 24776, 'rate' => 4.0, 'constant' => 247.74 ],
							[ 'income' => 34394, 'rate' => 6.0, 'constant' => 610.86 ],
							[ 'income' => 43467, 'rate' => 8.0, 'constant' => 1187.94 ],
							[ 'income' => 999999, 'rate' => 9.3, 'constant' => 1913.78 ],
							[ 'income' => 999999, 'rate' => 10.3, 'constant' => 90871.26 ],
					],
					20 => [
							[ 'income' => 6622, 'rate' => 1.0, 'constant' => 0 ],
							[ 'income' => 15698, 'rate' => 2.0, 'constant' => 66.22 ],
							[ 'income' => 24776, 'rate' => 4.0, 'constant' => 247.74 ],
							[ 'income' => 34394, 'rate' => 6.0, 'constant' => 610.86 ],
							[ 'income' => 43467, 'rate' => 8.0, 'constant' => 1187.94 ],
							[ 'income' => 999999, 'rate' => 9.3, 'constant' => 1913.78 ],
							[ 'income' => 999999, 'rate' => 10.3, 'constant' => 90871.26 ],
					],
					30 => [
							[ 'income' => 13244, 'rate' => 1.0, 'constant' => 0 ],
							[ 'income' => 31396, 'rate' => 2.0, 'constant' => 132.44 ],
							[ 'income' => 49552, 'rate' => 4.0, 'constant' => 495.48 ],
							[ 'income' => 68788, 'rate' => 6.0, 'constant' => 1221.72 ],
							[ 'income' => 86934, 'rate' => 8.0, 'constant' => 2375.88 ],
							[ 'income' => 999999, 'rate' => 9.3, 'constant' => 3827.56 ],
							[ 'income' => 999999, 'rate' => 10.3, 'constant' => 88742.61 ],
					],
					40 => [
							[ 'income' => 13251, 'rate' => 1.0, 'constant' => 0 ],
							[ 'income' => 31397, 'rate' => 2.0, 'constant' => 132.51 ],
							[ 'income' => 40473, 'rate' => 4.0, 'constant' => 495.43 ],
							[ 'income' => 50090, 'rate' => 6.0, 'constant' => 858.47 ],
							[ 'income' => 59166, 'rate' => 8.0, 'constant' => 1435.49 ],
							[ 'income' => 999999, 'rate' => 9.3, 'constant' => 2161.57 ],
							[ 'income' => 999999, 'rate' => 10.3, 'constant' => 89659.04 ],
					],
			],
			20060101 => [
					10 => [
							[ 'income' => 6319, 'rate' => 1.0, 'constant' => 0 ],
							[ 'income' => 14979, 'rate' => 2.0, 'constant' => 63.19 ],
							[ 'income' => 23641, 'rate' => 4.0, 'constant' => 236.39 ],
							[ 'income' => 32819, 'rate' => 6.0, 'constant' => 582.87 ],
							[ 'income' => 41476, 'rate' => 8.0, 'constant' => 1133.55 ],
							[ 'income' => 999999, 'rate' => 9.3, 'constant' => 1826.11 ],
							[ 'income' => 999999, 'rate' => 10.3, 'constant' => 90968.75 ],
					],
					20 => [
							[ 'income' => 6319, 'rate' => 1.0, 'constant' => 0 ],
							[ 'income' => 14979, 'rate' => 2.0, 'constant' => 63.19 ],
							[ 'income' => 23641, 'rate' => 4.0, 'constant' => 236.39 ],
							[ 'income' => 32819, 'rate' => 6.0, 'constant' => 582.87 ],
							[ 'income' => 41476, 'rate' => 8.0, 'constant' => 1133.55 ],
							[ 'income' => 999999, 'rate' => 9.3, 'constant' => 1826.11 ],
							[ 'income' => 999999, 'rate' => 10.3, 'constant' => 90968.75 ],
					],
					30 => [
							[ 'income' => 12638, 'rate' => 1.0, 'constant' => 0 ],
							[ 'income' => 29958, 'rate' => 2.0, 'constant' => 126.38 ],
							[ 'income' => 47282, 'rate' => 4.0, 'constant' => 472.78 ],
							[ 'income' => 65638, 'rate' => 6.0, 'constant' => 1165.74 ],
							[ 'income' => 82952, 'rate' => 8.0, 'constant' => 2267.10 ],
							[ 'income' => 999999, 'rate' => 9.3, 'constant' => 3652.22 ],
							[ 'income' => 999999, 'rate' => 10.3, 'constant' => 88937.59 ],
					],
					40 => [
							[ 'income' => 12644, 'rate' => 1.0, 'constant' => 0 ],
							[ 'income' => 29959, 'rate' => 2.0, 'constant' => 126.44 ],
							[ 'income' => 38619, 'rate' => 4.0, 'constant' => 472.74 ],
							[ 'income' => 47796, 'rate' => 6.0, 'constant' => 819.14 ],
							[ 'income' => 56456, 'rate' => 8.0, 'constant' => 1369.76 ],
							[ 'income' => 999999, 'rate' => 9.3, 'constant' => 2062.56 ],
							[ 'income' => 999999, 'rate' => 10.3, 'constant' => 89812.06 ],
					],
			],
	];

	var $state_options = [
			20200101 => [
				//Standard Deduction Table
				'standard_deduction' => [
					//First entry is 0,1 allowance, second is for 2 or more.
					'10' => [ 4537, 4537 ],
					'20' => [ 4537, 4537 ],
					'30' => [ 4537, 9074 ],
					'40' => [ 9074, 9074 ],
				],
				//Exemption Allowance Table - Annual amount for 1 allowance.
				'allowance'          => [
						'10' => 134.20,
						'20' => 134.20,
						'30' => 134.20,
						'40' => 134.20,
				],
				//Low Income Exemption Table
				'minimum_income'     => [
					//First entry is 0,1 allowance, 2nd is 2 or more.
					'10' => [ 15042, 15042 ],
					'20' => [ 15042, 15042 ],
					'30' => [ 15042, 30083 ],
					'40' => [ 30083, 30083 ],
				],
			],
			20190101 => [
				//Standard Deduction Table
				'standard_deduction' => [
					//First entry is 0,1 allowance, second is for 2 or more.
					'10' => [ 4401, 4401 ],
					'20' => [ 4401, 4401 ],
					'30' => [ 4401, 8802 ],
					'40' => [ 8802, 8802 ],
				],
				//Exemption Allowance Table
				'allowance'          => [
						'10' => 129.80,
						'20' => 129.80,
						'30' => 129.80,
						'40' => 129.80,
				],
				//Low Income Exemption Table
				'minimum_income'     => [
					//First entry is 0,1 allowance, 2nd is 2 or more.
					'10' => [ 14573, 14573 ],
					'20' => [ 14573, 14573 ],
					'30' => [ 14573, 29146 ],
					'40' => [ 29146, 29146 ],
				],
			],
			20180101 => [
				//Standard Deduction Table
				'standard_deduction' => [
					//First entry is 0,1 allowance, second is for 2 or more.
					'10' => [ 4236, 4236 ],
					'20' => [ 4236, 4236 ],
					'30' => [ 4236, 8472 ],
					'40' => [ 8472, 8472 ],
				],
				//Exemption Allowance Table
				'allowance'          => [
						'10' => 125.40,
						'20' => 125.40,
						'30' => 125.40,
						'40' => 125.40,
				],
				//Low Income Exemption Table
				'minimum_income'     => [
					//First entry is 0,1 allowance, 2nd is 2 or more.
					'10' => [ 14048, 14048 ],
					'20' => [ 14048, 14048 ],
					'30' => [ 14048, 28095 ],
					'40' => [ 28095, 28095 ],
				],
			],
			20170101 => [
				//Standard Deduction Table
				'standard_deduction' => [
					//First entry is 0,1 allowance, second is for 2 or more.
					'10' => [ 4129, 4129 ],
					'20' => [ 4129, 4129 ],
					'30' => [ 4129, 8258 ],
					'40' => [ 8258, 8258 ],
				],
				//Exemption Allowance Table
				'allowance'          => [
						'10' => 122.10,
						'20' => 122.10,
						'30' => 122.10,
						'40' => 122.10,
				],
				//Low Income Exemption Table
				'minimum_income'     => [
					//First entry is 0,1 allowance, 2nd is 2 or more.
					'10' => [ 13687, 13687 ],
					'20' => [ 13687, 13687 ],
					'30' => [ 13687, 27373 ],
					'40' => [ 27373, 27373 ],
				],
			],
			20160101 => [
				//Standard Deduction Table
				'standard_deduction' => [
					//First entry is 0,1 allowance, second is for 2 or more.
					'10' => [ 4044, 4044 ],
					'20' => [ 4044, 4044 ],
					'30' => [ 4044, 8088 ],
					'40' => [ 8088, 8088 ],
				],
				//Exemption Allowance Table
				'allowance'          => [
						'10' => 119.90,
						'20' => 119.90,
						'30' => 119.90,
						'40' => 119.90,
				],
				//Low Income Exemption Table
				'minimum_income'     => [
					//First entry is 0,1 allowance, 2nd is 2 or more.
					'10' => [ 13419, 13419 ],
					'20' => [ 13419, 13419 ],
					'30' => [ 13419, 26838 ],
					'40' => [ 26838, 26838 ],
				],
			],
			20150101 => [
				//Standard Deduction Table
				'standard_deduction' => [
					//First entry is 0,1 allowance, second is for 2 or more.
					'10' => [ 3992, 3992 ],
					'20' => [ 3992, 3992 ],
					'30' => [ 3992, 7984 ],
					'40' => [ 7984, 7984 ],
				],
				//Exemption Allowance Table
				'allowance'          => [
						'10' => 118.80,
						'20' => 118.80,
						'30' => 118.80,
						'40' => 118.80,
				],
				//Low Income Exemption Table
				'minimum_income'     => [
					//First entry is 0,1 allowance, 2nd is 2 or more.
					'10' => [ 13267, 13267 ],
					'20' => [ 13267, 13267 ],
					'30' => [ 13267, 26533 ],
					'40' => [ 26533, 26533 ],
				],
			],
			20140101 => [
				//Standard Deduction Table
				'standard_deduction' => [
					//First entry is 0,1 allowance, second is for 2 or more.
					'10' => [ 3906, 3906 ],
					'20' => [ 3906, 3906 ],
					'30' => [ 3906, 7812 ],
					'40' => [ 7812, 7812 ],
				],
				//Exemption Allowance Table
				'allowance'          => [
						'10' => 116.60,
						'20' => 116.60,
						'30' => 116.60,
						'40' => 116.60,
				],
				//Low Income Exemption Table
				'minimum_income'     => [
					//First entry is 0,1 allowance, 2nd is 2 or more.
					'10' => [ 12997, 12997 ],
					'20' => [ 12997, 12997 ],
					'30' => [ 12997, 25994 ],
					'40' => [ 25994, 25994 ],
				],
			],
			20130101 => [
					'standard_deduction' => [
						//First entry is 0,1 allowance, second is for 2 or more.
						'10' => [ 3841, 3841 ],
						'20' => [ 3841, 3841 ],
						'30' => [ 3841, 7682 ],
						'40' => [ 7682, 7682 ],
					],
					'allowance'          => [
							'10' => 114.40,
							'20' => 114.40,
							'30' => 114.40,
							'40' => 114.40,
					],
					'minimum_income'     => [
						//First entry is 0,1 allowance, 2nd is 2 or more.
						'10' => [ 12769, 12769 ],
						'20' => [ 12769, 12769 ],
						'30' => [ 12769, 25537 ],
						'40' => [ 25537, 25537 ],
					],
			],
			20120101 => [
					'standard_deduction' => [
						//First entry is 0,1 allowance, second is for 2 or more.
						'10' => [ 3769, 3769 ],
						'20' => [ 3769, 3769 ],
						'30' => [ 3769, 7538 ],
						'40' => [ 7538, 7538 ],
					],
					'allowance'          => [
							'10' => 112.20,
							'20' => 112.20,
							'30' => 112.20,
							'40' => 112.20,
					],
					'minimum_income'     => [
						//First entry is 0,1 allowance, 2nd is 2 or more.
						'10' => [ 12527, 12527 ],
						'20' => [ 12527, 12527 ],
						'30' => [ 12527, 25054 ],
						'40' => [ 25054, 25054 ],
					],
			],
			20110101 => [
					'standard_deduction' => [
						//First entry is 0,1 allowance, second is for 2 or more.
						'10' => [ 3670, 3670 ],
						'20' => [ 3670, 3670 ],
						'30' => [ 3670, 7340 ],
						'40' => [ 7340, 7340 ],
					],
					'allowance'          => [
							'10' => 108.90,
							'20' => 108.90,
							'30' => 108.90,
							'40' => 108.90,
					],
			],
			20100101 => [
					'standard_deduction' => [
						//First entry is 0,1 allowance, second is for 2 or more.
						'10' => [ 3637, 3637 ],
						'20' => [ 3637, 3637 ],
						'30' => [ 3637, 7274 ],
						'40' => [ 7274, 7274 ],
					],
					'allowance'          => [
							'10' => 107.80,
							'20' => 107.80,
							'30' => 107.80,
							'40' => 107.80,
					],
			],
			20091101 => [
					'standard_deduction' => [
						//First entry is 0,1 allowance, second is for 2 or more.
						'10' => [ 3692, 3692 ],
						'20' => [ 3692, 3692 ],
						'30' => [ 3692, 7384 ],
						'40' => [ 7384, 7384 ],
					],
					'allowance'          => [
							'10' => 108.90,
							'20' => 108.90,
							'30' => 108.90,
							'40' => 108.90,
					],
			],
			20090101 => [
					'standard_deduction' => [
						//First entry is 0,1 allowance, second is for 2 or more.
						'10' => [ 3692, 3692 ],
						'20' => [ 3692, 3692 ],
						'30' => [ 3692, 7384 ],
						'40' => [ 7384, 7384 ],
					],
					'allowance'          => [
							'10' => 99,
							'20' => 99,
							'30' => 99,
							'40' => 99,
					],
			],
			20080101 => [
					'standard_deduction' => [
						//First entry is 0,1 allowance, second is for 2 or more.
						'10' => [ 3516, 3516 ],
						'20' => [ 3516, 3516 ],
						'30' => [ 3516, 7032 ],
						'40' => [ 7032, 7032 ],
					],
					'allowance'          => [
							'10' => 94,
							'20' => 94,
							'30' => 94,
							'40' => 94,
					],
			],
			20070101 => [
					'standard_deduction' => [
						//First entry is 0,1 allowance, second is for 2 or more.
						'10' => [ 3410, 3410 ],
						'20' => [ 3410, 3410 ],
						'30' => [ 3410, 6820 ],
						'40' => [ 6820, 6820 ],
					],
					'allowance'          => [
							'10' => 91,
							'20' => 91,
							'30' => 91,
							'40' => 91,
					],
			],
			20060101 => [
					'standard_deduction' => [
						//First entry is 0,1 allowance, second is for 2 or more.
						'10' => [ 3254, 3254 ],
						'20' => [ 3254, 3254 ],
						'30' => [ 3254, 6508 ],
						'40' => [ 6508, 6508 ],
					],
					'allowance'          => [
							'10' => 87,
							'20' => 87,
							'30' => 87,
							'40' => 87,
					],
			],
	];

	function getStateAnnualTaxableIncome() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		$minimum_income = 0;
		if ( isset( $retarr['minimum_income'] ) && isset( $retarr['minimum_income'][$this->getStateFilingStatus()] ) ) {
			$minimum_income_arr = $retarr['minimum_income'][$this->getStateFilingStatus()];
			if ( $this->getStateAllowance() == 0 || $this->getStateAllowance() == 1 ) {
				$minimum_income = $minimum_income_arr[0];
			} else if ( $this->getStateAllowance() >= 2 ) {
				$minimum_income = $minimum_income_arr[1];
			}
		}

		if ( $this->getAnnualTaxableIncome() <= $minimum_income ) {
			return 0; //Below minimum income threshold, no withholding.
		}

		return bcsub( $this->getAnnualTaxableIncome(), $this->getStateStandardDeduction() );
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		if ( isset( $retarr['standard_deduction'][$this->getStateFilingStatus()] ) ) {
			$deduction_arr = $retarr['standard_deduction'][$this->getStateFilingStatus()];
		} else {
			$deduction_arr = $retarr['standard_deduction'][10];
		}

		$deduction = 0;
		if ( $this->getStateAllowance() == 0 || $this->getStateAllowance() == 1 ) {
			$deduction = $deduction_arr[0];
		} else if ( $this->getStateAllowance() >= 2 ) {
			$deduction = $deduction_arr[1];
		}
		Debug::text( 'Standard Deduction: ' . $deduction . ' Allowances: ' . $this->getStateAllowance() . ' Filing Status: ' . $this->getStateFilingStatus(), __FILE__, __LINE__, __METHOD__, 10 );

		return $deduction;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		if ( isset( $retarr['allowance'][$this->getStateFilingStatus()] ) ) {
			$allowance = $retarr['allowance'][$this->getStateFilingStatus()];
		} else {
			$allowance = 0;
		}

		$retval = 0;
		if ( $this->getStateAllowance() == 0 ) {
			$retval = 0;
		} else if ( $this->getStateAllowance() >= 1 ) {
			$retval = bcmul( $allowance, $this->getStateAllowance() );
		}

		Debug::text( 'State Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function _getStateTaxPayable() {
		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getStateRate( $annual_income );
			$prev_income = $this->getData()->getStateRatePreviousIncome( $annual_income );
			$state_constant = $this->getData()->getStateConstant( $annual_income );

			$retval = bcsub( bcadd( bcmul( bcsub( $annual_income, $prev_income ), $rate ), $state_constant ), $this->getStateAllowanceAmount() );
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $retval < 0 ) {
			$retval = 0;
		}

		return $retval;
	}

	function getStateEmployerUI() {
		if ( $this->getUIExempt() == true ) {
			return 0;
		}

		$pay_period_income = $this->getGrossPayPeriodIncome();
		$rate = bcdiv( $this->getStateUIRate(), 100 );
		$maximum_contribution = bcmul( $this->getStateUIWageBase(), $rate );
		$ytd_contribution = $this->getYearToDateStateUIContribution();

		Debug::text( 'Rate: ' . $rate . ' YTD Contribution: ' . $ytd_contribution . ' Maximum: ' . $maximum_contribution, __FILE__, __LINE__, __METHOD__, 10 );

		$amount = bcmul( $pay_period_income, $rate );
		$max_amount = bcsub( $maximum_contribution, $ytd_contribution );

		if ( $amount > $max_amount ) {
			$retval = $max_amount;
		} else {
			$retval = $amount;
		}

		return $retval;
	}

}

?>
