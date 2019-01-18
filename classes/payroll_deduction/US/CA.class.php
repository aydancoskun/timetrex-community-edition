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
 * @package PayrollDeduction\US
 */
class PayrollDeduction_US_CA extends PayrollDeduction_US {
	/*
															10 => 'Single',
															20 => 'Married - Spouse Works',
															30 => 'Married - Spouse does not Work',
															40 => 'Head of Household',
	*/

	var $state_income_tax_rate_options = array(
			20180101 => array(
					10 => array(
							array('income' => 8223, 'rate' => 1.1, 'constant' => 0),
							array('income' => 19495, 'rate' => 2.2, 'constant' => 90.45),
							array('income' => 30769, 'rate' => 4.4, 'constant' => 338.43),
							array('income' => 42711, 'rate' => 6.6, 'constant' => 834.49),
							array('income' => 53980, 'rate' => 8.8, 'constant' => 1622.66),
							array('income' => 275738, 'rate' => 10.23, 'constant' => 2614.33),
							array('income' => 330884, 'rate' => 11.33, 'constant' => 25300.17),
							array('income' => 551473, 'rate' => 12.43, 'constant' => 31548.21),
							array('income' => 1000000, 'rate' => 13.53, 'constant' => 58967.42),
							array('income' => 1000000, 'rate' => 14.63, 'constant' => 119653.12),
					),
					20 => array(
							array('income' => 8223, 'rate' => 1.1, 'constant' => 0),
							array('income' => 19495, 'rate' => 2.2, 'constant' => 90.45),
							array('income' => 30769, 'rate' => 4.4, 'constant' => 338.43),
							array('income' => 42711, 'rate' => 6.6, 'constant' => 834.49),
							array('income' => 53980, 'rate' => 8.8, 'constant' => 1622.66),
							array('income' => 275738, 'rate' => 10.23, 'constant' => 2614.33),
							array('income' => 330884, 'rate' => 11.33, 'constant' => 25300.17),
							array('income' => 551473, 'rate' => 12.43, 'constant' => 31548.21),
							array('income' => 1000000, 'rate' => 13.53, 'constant' => 58967.42),
							array('income' => 1000000, 'rate' => 14.63, 'constant' => 119653.12),
					),
					30 => array(
							array('income' => 16446, 'rate' => 1.1, 'constant' => 0),
							array('income' => 38990, 'rate' => 2.2, 'constant' => 180.91),
							array('income' => 61538, 'rate' => 4.4, 'constant' => 676.88),
							array('income' => 85422, 'rate' => 6.6, 'constant' => 1668.99),
							array('income' => 107960, 'rate' => 8.8, 'constant' => 3245.33),
							array('income' => 551476, 'rate' => 10.23, 'constant' => 5228.67),
							array('income' => 661768, 'rate' => 11.33, 'constant' => 50600.36),
							array('income' => 1000000, 'rate' => 12.43, 'constant' => 63096.44),
							array('income' => 1102946, 'rate' => 13.53, 'constant' => 105138.68),
							array('income' => 1102946, 'rate' => 14.63, 'constant' => 119067.26),
					),
					40 => array( //These are different than 30 above.
								 array('income' => 16457, 'rate' => 1.1, 'constant' => 0),
								 array('income' => 38991, 'rate' => 2.2, 'constant' => 181.03),
								 array('income' => 50264, 'rate' => 4.4, 'constant' => 676.78),
								 array('income' => 62206, 'rate' => 6.6, 'constant' => 1172.79),
								 array('income' => 73477, 'rate' => 8.8, 'constant' => 1960.96),
								 array('income' => 375002, 'rate' => 10.23, 'constant' => 2952.81),
								 array('income' => 450003, 'rate' => 11.33, 'constant' => 33798.82),
								 array('income' => 750003, 'rate' => 12.43, 'constant' => 42296.43),
								 array('income' => 1000000, 'rate' => 13.53, 'constant' => 79586.43),
								 array('income' => 1000000, 'rate' => 14.63, 'constant' => 113411.02),
					),
			),
			20170101 => array(
					10 => array(
							array('income' => 8015, 'rate' => 1.1, 'constant' => 0),
							array('income' => 19001, 'rate' => 2.2, 'constant' => 88.17),
							array('income' => 29989, 'rate' => 4.4, 'constant' => 329.86),
							array('income' => 41629, 'rate' => 6.6, 'constant' => 813.33),
							array('income' => 52612, 'rate' => 8.8, 'constant' => 1581.57),
							array('income' => 268750, 'rate' => 10.23, 'constant' => 2548.07),
							array('income' => 322499, 'rate' => 11.33, 'constant' => 24658.99),
							array('income' => 537498, 'rate' => 12.43, 'constant' => 30748.75),
							array('income' => 1000000, 'rate' => 13.53, 'constant' => 57473.13),
							array('income' => 1000000, 'rate' => 14.63, 'constant' => 120049.65),
					),
					20 => array(
							array('income' => 8015, 'rate' => 1.1, 'constant' => 0),
							array('income' => 19001, 'rate' => 2.2, 'constant' => 88.17),
							array('income' => 29989, 'rate' => 4.4, 'constant' => 329.86),
							array('income' => 41629, 'rate' => 6.6, 'constant' => 813.33),
							array('income' => 52612, 'rate' => 8.8, 'constant' => 1581.57),
							array('income' => 268750, 'rate' => 10.23, 'constant' => 2548.07),
							array('income' => 322499, 'rate' => 11.33, 'constant' => 24658.99),
							array('income' => 537498, 'rate' => 12.43, 'constant' => 30748.75),
							array('income' => 1000000, 'rate' => 13.53, 'constant' => 57473.13),
							array('income' => 1000000, 'rate' => 14.63, 'constant' => 120049.65),
					),
					30 => array(
							array('income' => 16030, 'rate' => 1.1, 'constant' => 0),
							array('income' => 38002, 'rate' => 2.2, 'constant' => 176.33),
							array('income' => 59978, 'rate' => 4.4, 'constant' => 659.71),
							array('income' => 83258, 'rate' => 6.6, 'constant' => 1626.65),
							array('income' => 105224, 'rate' => 8.8, 'constant' => 3163.13),
							array('income' => 537500, 'rate' => 10.23, 'constant' => 5096.14),
							array('income' => 644998, 'rate' => 11.33, 'constant' => 49317.97),
							array('income' => 1000000, 'rate' => 12.43, 'constant' => 61497.49),
							array('income' => 1074996, 'rate' => 13.53, 'constant' => 105624.24),
							array('income' => 1074996, 'rate' => 14.63, 'constant' => 115771.20),
					),
					40 => array( //These are different than 30 above.
								 array('income' => 16040, 'rate' => 1.1, 'constant' => 0),
								 array('income' => 38003, 'rate' => 2.2, 'constant' => 176.44),
								 array('income' => 48990, 'rate' => 4.4, 'constant' => 659.63),
								 array('income' => 60630, 'rate' => 6.6, 'constant' => 1143.06),
								 array('income' => 71615, 'rate' => 8.8, 'constant' => 1911.30),
								 array('income' => 365499, 'rate' => 10.23, 'constant' => 2877.98),
								 array('income' => 438599, 'rate' => 11.33, 'constant' => 32942.31),
								 array('income' => 730997, 'rate' => 12.43, 'constant' => 41224.54),
								 array('income' => 1000000, 'rate' => 13.53, 'constant' => 77569.61),
								 array('income' => 1000000, 'rate' => 14.63, 'constant' => 113965.72),
					),
			),
			20160101 => array(
					10 => array(
							array('income' => 7850, 'rate' => 1.1, 'constant' => 0),
							array('income' => 18610, 'rate' => 2.2, 'constant' => 86.35),
							array('income' => 29372, 'rate' => 4.4, 'constant' => 323.07),
							array('income' => 40773, 'rate' => 6.6, 'constant' => 796.60),
							array('income' => 51530, 'rate' => 8.8, 'constant' => 1549.07),
							array('income' => 263222, 'rate' => 10.23, 'constant' => 2495.69),
							array('income' => 315866, 'rate' => 11.33, 'constant' => 24151.78),
							array('income' => 526443, 'rate' => 12.43, 'constant' => 30116.35),
							array('income' => 1000000, 'rate' => 13.53, 'constant' => 56291.07),
							array('income' => 1000000, 'rate' => 14.63, 'constant' => 120363.33),
					),
					20 => array(
							array('income' => 7850, 'rate' => 1.1, 'constant' => 0),
							array('income' => 18610, 'rate' => 2.2, 'constant' => 86.35),
							array('income' => 29372, 'rate' => 4.4, 'constant' => 323.07),
							array('income' => 40773, 'rate' => 6.6, 'constant' => 796.60),
							array('income' => 51530, 'rate' => 8.8, 'constant' => 1549.07),
							array('income' => 263222, 'rate' => 10.23, 'constant' => 2495.69),
							array('income' => 315866, 'rate' => 11.33, 'constant' => 24151.78),
							array('income' => 526443, 'rate' => 12.43, 'constant' => 30116.35),
							array('income' => 1000000, 'rate' => 13.53, 'constant' => 56291.07),
							array('income' => 1000000, 'rate' => 14.63, 'constant' => 120363.33),
					),
					30 => array(
							array('income' => 15700, 'rate' => 1.1, 'constant' => 0),
							array('income' => 37220, 'rate' => 2.2, 'constant' => 172.70),
							array('income' => 58744, 'rate' => 4.4, 'constant' => 646.14),
							array('income' => 81546, 'rate' => 6.6, 'constant' => 1593.20),
							array('income' => 103060, 'rate' => 8.8, 'constant' => 3098.13),
							array('income' => 526444, 'rate' => 10.23, 'constant' => 4991.36),
							array('income' => 631732, 'rate' => 11.33, 'constant' => 48303.54),
							array('income' => 1000000, 'rate' => 12.43, 'constant' => 60232.67),
							array('income' => 1052886, 'rate' => 13.53, 'constant' => 106008.38),
							array('income' => 1052886, 'rate' => 14.63, 'constant' => 113163.86),
					),
					40 => array(
							array('income' => 15700, 'rate' => 1.1, 'constant' => 0),
							array('income' => 37221, 'rate' => 2.2, 'constant' => 172.81),
							array('income' => 47982, 'rate' => 4.4, 'constant' => 646.05),
							array('income' => 59383, 'rate' => 6.6, 'constant' => 1119.53),
							array('income' => 70142, 'rate' => 8.8, 'constant' => 1872.00),
							array('income' => 357981, 'rate' => 10.23, 'constant' => 2818.79),
							array('income' => 429578, 'rate' => 11.33, 'constant' => 32264.72),
							array('income' => 715962, 'rate' => 12.43, 'constant' => 40376.66),
							array('income' => 1000000, 'rate' => 13.53, 'constant' => 75974.19),
							array('income' => 1000000, 'rate' => 14.63, 'constant' => 114404.53),
					),
			),
			20150101 => array(
					10 => array(
							array('income' => 7749, 'rate' => 1.1, 'constant' => 0),
							array('income' => 18371, 'rate' => 2.2, 'constant' => 85.24),
							array('income' => 28995, 'rate' => 4.4, 'constant' => 318.92),
							array('income' => 40250, 'rate' => 6.6, 'constant' => 786.38),
							array('income' => 50869, 'rate' => 8.8, 'constant' => 1529.21),
							array('income' => 259844, 'rate' => 10.23, 'constant' => 2463.68),
							array('income' => 311812, 'rate' => 11.33, 'constant' => 23841.82),
							array('income' => 519687, 'rate' => 12.43, 'constant' => 29729.79),
							array('income' => 1000000, 'rate' => 13.53, 'constant' => 55568.65),
							array('income' => 1000000, 'rate' => 14.63, 'constant' => 120555.00),
					),
					20 => array(
							array('income' => 7749, 'rate' => 1.1, 'constant' => 0),
							array('income' => 18371, 'rate' => 2.2, 'constant' => 85.24),
							array('income' => 28995, 'rate' => 4.4, 'constant' => 318.92),
							array('income' => 40250, 'rate' => 6.6, 'constant' => 786.38),
							array('income' => 50869, 'rate' => 8.8, 'constant' => 1529.21),
							array('income' => 259844, 'rate' => 10.23, 'constant' => 2463.68),
							array('income' => 311812, 'rate' => 11.33, 'constant' => 23841.82),
							array('income' => 519687, 'rate' => 12.43, 'constant' => 29729.79),
							array('income' => 1000000, 'rate' => 13.53, 'constant' => 55568.65),
							array('income' => 1000000, 'rate' => 14.63, 'constant' => 120555.00),
					),
					30 => array(
							array('income' => 15498, 'rate' => 1.1, 'constant' => 0),
							array('income' => 36742, 'rate' => 2.2, 'constant' => 170.48),
							array('income' => 57990, 'rate' => 4.4, 'constant' => 637.85),
							array('income' => 80500, 'rate' => 6.6, 'constant' => 1572.76),
							array('income' => 101738, 'rate' => 8.8, 'constant' => 3058.42),
							array('income' => 519688, 'rate' => 10.23, 'constant' => 4927.36),
							array('income' => 623624, 'rate' => 11.33, 'constant' => 47683.65),
							array('income' => 1000000, 'rate' => 12.43, 'constant' => 59459.60),
							array('income' => 1039000, 'rate' => 13.53, 'constant' => 106243.14),
							array('income' => 1039000, 'rate' => 14.63, 'constant' => 111570.44),
					),
					40 => array(
							array('income' => 15508, 'rate' => 1.1, 'constant' => 0),
							array('income' => 36743, 'rate' => 2.2, 'constant' => 170.59),
							array('income' => 47366, 'rate' => 4.4, 'constant' => 637.76),
							array('income' => 58621, 'rate' => 6.6, 'constant' => 1105.17),
							array('income' => 69242, 'rate' => 8.8, 'constant' => 1848.00),
							array('income' => 353387, 'rate' => 10.23, 'constant' => 2782.65),
							array('income' => 424065, 'rate' => 11.33, 'constant' => 31850.68),
							array('income' => 706774, 'rate' => 12.43, 'constant' => 39858.50),
							array('income' => 1000000, 'rate' => 13.53, 'constant' => 74999.23),
							array('income' => 1000000, 'rate' => 14.63, 'constant' => 114672.71),
					),
			),
			20140101 => array(
					10 => array(
							array('income' => 7582, 'rate' => 1.1, 'constant' => 0),
							array('income' => 17976, 'rate' => 2.2, 'constant' => 83.40),
							array('income' => 28371, 'rate' => 4.4, 'constant' => 312.07),
							array('income' => 39384, 'rate' => 6.6, 'constant' => 769.45),
							array('income' => 49774, 'rate' => 8.8, 'constant' => 1496.31),
							array('income' => 254250, 'rate' => 10.23, 'constant' => 2410.63),
							array('income' => 305100, 'rate' => 11.33, 'constant' => 23328.52),
							array('income' => 508500, 'rate' => 12.43, 'constant' => 29089.83),
							array('income' => 1000000, 'rate' => 13.53, 'constant' => 54372.45),
							array('income' => 1000000, 'rate' => 14.63, 'constant' => 120872.40),
					),
					20 => array(
							array('income' => 7582, 'rate' => 1.1, 'constant' => 0),
							array('income' => 17976, 'rate' => 2.2, 'constant' => 83.40),
							array('income' => 28371, 'rate' => 4.4, 'constant' => 312.07),
							array('income' => 39384, 'rate' => 6.6, 'constant' => 769.45),
							array('income' => 49774, 'rate' => 8.8, 'constant' => 1496.31),
							array('income' => 254250, 'rate' => 10.23, 'constant' => 2410.63),
							array('income' => 305100, 'rate' => 11.33, 'constant' => 23328.52),
							array('income' => 508500, 'rate' => 12.43, 'constant' => 29089.83),
							array('income' => 1000000, 'rate' => 13.53, 'constant' => 54372.45),
							array('income' => 1000000, 'rate' => 14.63, 'constant' => 120872.40),
					),
					30 => array(
							array('income' => 15164, 'rate' => 1.1, 'constant' => 0),
							array('income' => 35952, 'rate' => 2.2, 'constant' => 166.80),
							array('income' => 56742, 'rate' => 4.4, 'constant' => 624.14),
							array('income' => 78768, 'rate' => 6.6, 'constant' => 1538.90),
							array('income' => 99548, 'rate' => 8.8, 'constant' => 2992.62),
							array('income' => 508500, 'rate' => 10.23, 'constant' => 4821.26),
							array('income' => 610200, 'rate' => 11.33, 'constant' => 46657.05),
							array('income' => 1000000, 'rate' => 12.43, 'constant' => 58179.66),
							array('income' => 1017000, 'rate' => 13.53, 'constant' => 106631.80),
							array('income' => 1017000, 'rate' => 14.63, 'constant' => 108931.90),
					),
					40 => array(
							array('income' => 15174, 'rate' => 1.1, 'constant' => 0),
							array('income' => 35952, 'rate' => 2.2, 'constant' => 166.91),
							array('income' => 46346, 'rate' => 4.4, 'constant' => 624.03),
							array('income' => 57359, 'rate' => 6.6, 'constant' => 1081.37),
							array('income' => 67751, 'rate' => 8.8, 'constant' => 1808.23),
							array('income' => 345780, 'rate' => 10.23, 'constant' => 2722.73),
							array('income' => 414936, 'rate' => 11.33, 'constant' => 31165.10),
							array('income' => 691560, 'rate' => 12.43, 'constant' => 39000.47),
							array('income' => 1000000, 'rate' => 13.53, 'constant' => 73384.83),
							array('income' => 1000000, 'rate' => 14.63, 'constant' => 115116.76),
					),
			),
			20130101 => array(
					10 => array(
							array('income' => 7455, 'rate' => 1.1, 'constant' => 0),
							array('income' => 17676, 'rate' => 2.2, 'constant' => 82.01),
							array('income' => 27897, 'rate' => 4.4, 'constant' => 306.87),
							array('income' => 38726, 'rate' => 6.6, 'constant' => 756.59),
							array('income' => 48942, 'rate' => 8.8, 'constant' => 1471.30),
							array('income' => 250000, 'rate' => 10.23, 'constant' => 2370.31),
							array('income' => 300000, 'rate' => 11.33, 'constant' => 22938.54),
							array('income' => 500000, 'rate' => 12.43, 'constant' => 28603.54),
							array('income' => 1000000, 'rate' => 13.53, 'constant' => 53463.54),
							array('income' => 1000000, 'rate' => 14.63, 'constant' => 121113.54),
					),
					20 => array(
							array('income' => 7455, 'rate' => 1.1, 'constant' => 0),
							array('income' => 17676, 'rate' => 2.2, 'constant' => 82.01),
							array('income' => 27897, 'rate' => 4.4, 'constant' => 306.87),
							array('income' => 38726, 'rate' => 6.6, 'constant' => 756.59),
							array('income' => 48942, 'rate' => 8.8, 'constant' => 1471.30),
							array('income' => 250000, 'rate' => 10.23, 'constant' => 2370.31),
							array('income' => 300000, 'rate' => 11.33, 'constant' => 22938.54),
							array('income' => 500000, 'rate' => 12.43, 'constant' => 28603.54),
							array('income' => 1000000, 'rate' => 13.53, 'constant' => 53463.54),
							array('income' => 1000000, 'rate' => 14.63, 'constant' => 121113.54),
					),
					30 => array(
							array('income' => 14910, 'rate' => 1.1, 'constant' => 0),
							array('income' => 35352, 'rate' => 2.2, 'constant' => 164.01),
							array('income' => 55794, 'rate' => 4.4, 'constant' => 613.73),
							array('income' => 77452, 'rate' => 6.6, 'constant' => 1513.18),
							array('income' => 97884, 'rate' => 8.8, 'constant' => 2942.61),
							array('income' => 500000, 'rate' => 10.23, 'constant' => 4740.63),
							array('income' => 600000, 'rate' => 11.33, 'constant' => 45877.10),
							array('income' => 1000000, 'rate' => 12.43, 'constant' => 57207.10),
							array('income' => 1000000, 'rate' => 14.63, 'constant' => 106927.10),
					),
					40 => array(
							array('income' => 14920, 'rate' => 1.1, 'constant' => 0),
							array('income' => 35351, 'rate' => 2.2, 'constant' => 164.12),
							array('income' => 45571, 'rate' => 4.4, 'constant' => 613.60),
							array('income' => 56400, 'rate' => 6.6, 'constant' => 1063.28),
							array('income' => 66618, 'rate' => 8.8, 'constant' => 1777.99),
							array('income' => 340000, 'rate' => 10.23, 'constant' => 2677.17),
							array('income' => 408000, 'rate' => 11.33, 'constant' => 30644.15),
							array('income' => 680000, 'rate' => 12.43, 'constant' => 38348.55),
							array('income' => 1000000, 'rate' => 13.53, 'constant' => 72158.15),
							array('income' => 1000000, 'rate' => 14.63, 'constant' => 115454.15),
					),
			),
			20120101 => array(
					10 => array(
							array('income' => 7316, 'rate' => 1.1, 'constant' => 0),
							array('income' => 17346, 'rate' => 2.2, 'constant' => 80.48),
							array('income' => 27377, 'rate' => 4.4, 'constant' => 301.14),
							array('income' => 38004, 'rate' => 6.6, 'constant' => 742.50),
							array('income' => 48029, 'rate' => 8.8, 'constant' => 1443.88),
							array('income' => 1000000, 'rate' => 10.23, 'constant' => 2326.08),
							array('income' => 1000000, 'rate' => 11.33, 'constant' => 99712.71),
					),
					20 => array(
							array('income' => 7316, 'rate' => 1.1, 'constant' => 0),
							array('income' => 17346, 'rate' => 2.2, 'constant' => 80.48),
							array('income' => 27377, 'rate' => 4.4, 'constant' => 301.14),
							array('income' => 38004, 'rate' => 6.6, 'constant' => 742.50),
							array('income' => 48029, 'rate' => 8.8, 'constant' => 1443.88),
							array('income' => 1000000, 'rate' => 10.23, 'constant' => 2326.08),
							array('income' => 1000000, 'rate' => 11.33, 'constant' => 99712.71),
					),
					30 => array(
							array('income' => 14632, 'rate' => 1.1, 'constant' => 0),
							array('income' => 34692, 'rate' => 2.2, 'constant' => 160.95),
							array('income' => 54754, 'rate' => 4.4, 'constant' => 602.27),
							array('income' => 76008, 'rate' => 6.6, 'constant' => 1485.00),
							array('income' => 96058, 'rate' => 8.8, 'constant' => 2887.76),
							array('income' => 1000000, 'rate' => 10.23, 'constant' => 4652.16),
							array('income' => 1000000, 'rate' => 11.33, 'constant' => 97125.43),
					),
					40 => array(
							array('income' => 14642, 'rate' => 1.1, 'constant' => 0),
							array('income' => 34692, 'rate' => 2.2, 'constant' => 161.06),
							array('income' => 44721, 'rate' => 4.4, 'constant' => 602.16),
							array('income' => 55348, 'rate' => 6.6, 'constant' => 1043.44),
							array('income' => 65376, 'rate' => 8.8, 'constant' => 1744.82),
							array('income' => 1000000, 'rate' => 10.23, 'constant' => 2627.28),
							array('income' => 1000000, 'rate' => 11.33, 'constant' => 98239.32),
					),
			),
			20110101 => array(
					10 => array(
							array('income' => 7124, 'rate' => 1.1, 'constant' => 0),
							array('income' => 16890, 'rate' => 2.2, 'constant' => 78.36),
							array('income' => 26657, 'rate' => 4.4, 'constant' => 293.21),
							array('income' => 37005, 'rate' => 6.6, 'constant' => 722.96),
							array('income' => 46766, 'rate' => 8.8, 'constant' => 1405.93),
							array('income' => 1000000, 'rate' => 10.23, 'constant' => 2264.90),
							array('income' => 1000000, 'rate' => 11.33, 'constant' => 99780.74),
					),
					20 => array(
							array('income' => 7124, 'rate' => 1.1, 'constant' => 0),
							array('income' => 16890, 'rate' => 2.2, 'constant' => 78.36),
							array('income' => 26657, 'rate' => 4.4, 'constant' => 293.21),
							array('income' => 37005, 'rate' => 6.6, 'constant' => 722.96),
							array('income' => 46766, 'rate' => 8.8, 'constant' => 1405.93),
							array('income' => 1000000, 'rate' => 10.23, 'constant' => 2264.90),
							array('income' => 1000000, 'rate' => 11.33, 'constant' => 99780.74),
					),
					30 => array(
							array('income' => 14248, 'rate' => 1.1, 'constant' => 0),
							array('income' => 33780, 'rate' => 2.2, 'constant' => 156.73),
							array('income' => 53314, 'rate' => 4.4, 'constant' => 586.43),
							array('income' => 74010, 'rate' => 6.6, 'constant' => 1445.93),
							array('income' => 93532, 'rate' => 8.8, 'constant' => 2811.87),
							array('income' => 1000000, 'rate' => 10.23, 'constant' => 4529.81),
							array('income' => 1000000, 'rate' => 11.33, 'constant' => 97261.49),
					),
					40 => array(
							array('income' => 14257, 'rate' => 1.1, 'constant' => 0),
							array('income' => 33780, 'rate' => 2.2, 'constant' => 156.83),
							array('income' => 43545, 'rate' => 4.4, 'constant' => 586.34),
							array('income' => 53893, 'rate' => 6.6, 'constant' => 1016.00),
							array('income' => 63657, 'rate' => 8.8, 'constant' => 1698.97),
							array('income' => 1000000, 'rate' => 10.23, 'constant' => 2558.20),
							array('income' => 1000000, 'rate' => 11.33, 'constant' => 98346.09),
					),
			),
			20100101 => array(
					10 => array(
							array('income' => 7060, 'rate' => 1.375, 'constant' => 0),
							array('income' => 16739, 'rate' => 2.475, 'constant' => 97.08),
							array('income' => 26419, 'rate' => 4.675, 'constant' => 336.64),
							array('income' => 36675, 'rate' => 6.875, 'constant' => 789.18),
							array('income' => 46349, 'rate' => 9.075, 'constant' => 1494.28),
							array('income' => 1000000, 'rate' => 10.505, 'constant' => 2372.20),
							array('income' => 1000000, 'rate' => 11.605, 'constant' => 102553.24),
					),
					20 => array(
							array('income' => 7060, 'rate' => 1.375, 'constant' => 0),
							array('income' => 16739, 'rate' => 2.475, 'constant' => 97.08),
							array('income' => 26419, 'rate' => 4.675, 'constant' => 336.64),
							array('income' => 36675, 'rate' => 6.875, 'constant' => 789.18),
							array('income' => 46349, 'rate' => 9.075, 'constant' => 1494.28),
							array('income' => 1000000, 'rate' => 10.505, 'constant' => 2372.20),
							array('income' => 1000000, 'rate' => 11.605, 'constant' => 102553.24),
					),
					30 => array(
							array('income' => 14120, 'rate' => 1.375, 'constant' => 0),
							array('income' => 33478, 'rate' => 2.475, 'constant' => 194.15),
							array('income' => 52838, 'rate' => 4.675, 'constant' => 673.26),
							array('income' => 73350, 'rate' => 6.875, 'constant' => 1578.34),
							array('income' => 92698, 'rate' => 9.075, 'constant' => 2988.54),
							array('income' => 1000000, 'rate' => 10.505, 'constant' => 4744.37),
							array('income' => 1000000, 'rate' => 11.605, 'constant' => 100056.45),
					),
					40 => array(
							array('income' => 14130, 'rate' => 1.375, 'constant' => 0),
							array('income' => 33479, 'rate' => 2.475, 'constant' => 194.29),
							array('income' => 43157, 'rate' => 4.675, 'constant' => 673.18),
							array('income' => 53412, 'rate' => 6.875, 'constant' => 1125.63),
							array('income' => 63089, 'rate' => 9.075, 'constant' => 1830.66),
							array('income' => 1000000, 'rate' => 10.505, 'constant' => 2708.85),
							array('income' => 1000000, 'rate' => 11.605, 'constant' => 101131.35),
					),
			),
			20091101 => array(
					10 => array(
							array('income' => 7168, 'rate' => 1.375, 'constant' => 0),
							array('income' => 16994, 'rate' => 2.475, 'constant' => 98.56),
							array('income' => 26821, 'rate' => 4.675, 'constant' => 341.75),
							array('income' => 37233, 'rate' => 6.875, 'constant' => 801.16),
							array('income' => 47055, 'rate' => 9.075, 'constant' => 1516.99),
							array('income' => 1000000, 'rate' => 10.505, 'constant' => 2408.34),
							array('income' => 1000000, 'rate' => 11.605, 'constant' => 102515.21),
					),
					20 => array(
							array('income' => 7168, 'rate' => 1.375, 'constant' => 0),
							array('income' => 16994, 'rate' => 2.475, 'constant' => 98.56),
							array('income' => 26821, 'rate' => 4.675, 'constant' => 341.75),
							array('income' => 37233, 'rate' => 6.875, 'constant' => 801.16),
							array('income' => 47055, 'rate' => 9.075, 'constant' => 1516.99),
							array('income' => 1000000, 'rate' => 10.505, 'constant' => 2408.34),
							array('income' => 1000000, 'rate' => 11.605, 'constant' => 102515.21),
					),
					30 => array(
							array('income' => 14336, 'rate' => 1.375, 'constant' => 0),
							array('income' => 33988, 'rate' => 2.475, 'constant' => 197.12),
							array('income' => 53642, 'rate' => 4.675, 'constant' => 683.51),
							array('income' => 74466, 'rate' => 6.875, 'constant' => 1602.33),
							array('income' => 94110, 'rate' => 9.075, 'constant' => 3033.98),
							array('income' => 1000000, 'rate' => 10.505, 'constant' => 4816.67),
							array('income' => 1000000, 'rate' => 11.605, 'constant' => 99980.41),
					),
					40 => array(
							array('income' => 14345, 'rate' => 1.375, 'constant' => 0),
							array('income' => 33989, 'rate' => 2.475, 'constant' => 197.24),
							array('income' => 43814, 'rate' => 4.675, 'constant' => 683.43),
							array('income' => 54225, 'rate' => 6.875, 'constant' => 1142.75),
							array('income' => 64050, 'rate' => 9.075, 'constant' => 1858.51),
							array('income' => 1000000, 'rate' => 10.505, 'constant' => 2750.13),
							array('income' => 1000000, 'rate' => 11.605, 'constant' => 101071.68),
					),
			),
			20090501 => array(
					10 => array(
							array('income' => 7168, 'rate' => 1.25, 'constant' => 0),
							array('income' => 16994, 'rate' => 2.25, 'constant' => 89.60),
							array('income' => 26821, 'rate' => 4.25, 'constant' => 310.69),
							array('income' => 37233, 'rate' => 6.25, 'constant' => 728.34),
							array('income' => 47055, 'rate' => 8.25, 'constant' => 1379.09),
							array('income' => 1000000, 'rate' => 9.55, 'constant' => 2189.41),
							array('income' => 1000000, 'rate' => 10.55, 'constant' => 93195.66),
					),
					20 => array(
							array('income' => 7168, 'rate' => 1.25, 'constant' => 0),
							array('income' => 16994, 'rate' => 2.25, 'constant' => 89.60),
							array('income' => 26821, 'rate' => 4.25, 'constant' => 310.69),
							array('income' => 37233, 'rate' => 6.25, 'constant' => 728.34),
							array('income' => 47055, 'rate' => 8.25, 'constant' => 1379.09),
							array('income' => 1000000, 'rate' => 9.55, 'constant' => 2189.41),
							array('income' => 1000000, 'rate' => 10.55, 'constant' => 93195.66),
					),
					30 => array(
							array('income' => 14336, 'rate' => 1.25, 'constant' => 0),
							array('income' => 33988, 'rate' => 2.25, 'constant' => 179.20),
							array('income' => 53642, 'rate' => 4.25, 'constant' => 621.37),
							array('income' => 74466, 'rate' => 6.25, 'constant' => 1456.67),
							array('income' => 94110, 'rate' => 8.25, 'constant' => 2758.17),
							array('income' => 1000000, 'rate' => 9.55, 'constant' => 4378.80),
							array('income' => 1000000, 'rate' => 10.55, 'constant' => 90891.30),
					),
					40 => array(
							array('income' => 14345, 'rate' => 1.25, 'constant' => 0),
							array('income' => 33989, 'rate' => 2.25, 'constant' => 179.31),
							array('income' => 43814, 'rate' => 4.25, 'constant' => 621.30),
							array('income' => 54225, 'rate' => 6.25, 'constant' => 1038.86),
							array('income' => 64050, 'rate' => 8.25, 'constant' => 1689.55),
							array('income' => 1000000, 'rate' => 9.55, 'constant' => 2500.11),
							array('income' => 1000000, 'rate' => 10.55, 'constant' => 91883.34),
					),
			),
			20090101 => array(
					10 => array(
							array('income' => 7168, 'rate' => 1.0, 'constant' => 0),
							array('income' => 16994, 'rate' => 2.0, 'constant' => 71.68),
							array('income' => 26821, 'rate' => 4.0, 'constant' => 268.20),
							array('income' => 37233, 'rate' => 6.0, 'constant' => 661.28),
							array('income' => 47055, 'rate' => 8.0, 'constant' => 1286.00),
							array('income' => 1000000, 'rate' => 9.3, 'constant' => 2071.76),
							array('income' => 1000000, 'rate' => 10.3, 'constant' => 90695.65),
					),
					20 => array(
							array('income' => 7168, 'rate' => 1.0, 'constant' => 0),
							array('income' => 16994, 'rate' => 2.0, 'constant' => 71.68),
							array('income' => 26821, 'rate' => 4.0, 'constant' => 268.20),
							array('income' => 37233, 'rate' => 6.0, 'constant' => 661.28),
							array('income' => 47055, 'rate' => 8.0, 'constant' => 1286.00),
							array('income' => 1000000, 'rate' => 9.3, 'constant' => 2071.76),
							array('income' => 1000000, 'rate' => 10.3, 'constant' => 90695.65),
					),
					30 => array(
							array('income' => 14336, 'rate' => 1.0, 'constant' => 0),
							array('income' => 33988, 'rate' => 2.0, 'constant' => 143.36),
							array('income' => 53642, 'rate' => 4.0, 'constant' => 536.40),
							array('income' => 74466, 'rate' => 6.0, 'constant' => 1322.56),
							array('income' => 94110, 'rate' => 8.0, 'constant' => 2572.00),
							array('income' => 1000000, 'rate' => 9.3, 'constant' => 4143.52),
							array('income' => 1000000, 'rate' => 10.3, 'constant' => 88391.29),
					),
					40 => array(
							array('income' => 14345, 'rate' => 1.0, 'constant' => 0),
							array('income' => 33989, 'rate' => 2.0, 'constant' => 143.45),
							array('income' => 43814, 'rate' => 4.0, 'constant' => 536.33),
							array('income' => 54225, 'rate' => 6.0, 'constant' => 929.33),
							array('income' => 64050, 'rate' => 8.0, 'constant' => 1553.99),
							array('income' => 1000000, 'rate' => 9.3, 'constant' => 2339.99),
							array('income' => 1000000, 'rate' => 10.3, 'constant' => 89383.34),
					),
			),
			20080101 => array(
					10 => array(
							array('income' => 6827, 'rate' => 1.0, 'constant' => 0),
							array('income' => 16185, 'rate' => 2.0, 'constant' => 68.27),
							array('income' => 25544, 'rate' => 4.0, 'constant' => 255.43),
							array('income' => 35460, 'rate' => 6.0, 'constant' => 629.79),
							array('income' => 44814, 'rate' => 8.0, 'constant' => 1224.75),
							array('income' => 999999, 'rate' => 9.3, 'constant' => 1973.07),
							array('income' => 999999, 'rate' => 10.3, 'constant' => 90805.28),
					),
					20 => array(
							array('income' => 6827, 'rate' => 1.0, 'constant' => 0),
							array('income' => 16185, 'rate' => 2.0, 'constant' => 68.27),
							array('income' => 25544, 'rate' => 4.0, 'constant' => 255.43),
							array('income' => 35460, 'rate' => 6.0, 'constant' => 629.79),
							array('income' => 44814, 'rate' => 8.0, 'constant' => 1224.75),
							array('income' => 999999, 'rate' => 9.3, 'constant' => 1973.07),
							array('income' => 999999, 'rate' => 10.3, 'constant' => 90805.28),
					),
					30 => array(
							array('income' => 13654, 'rate' => 1.0, 'constant' => 0),
							array('income' => 32370, 'rate' => 2.0, 'constant' => 136.54),
							array('income' => 51088, 'rate' => 4.0, 'constant' => 510.86),
							array('income' => 70920, 'rate' => 6.0, 'constant' => 1259.58),
							array('income' => 89628, 'rate' => 8.0, 'constant' => 2449.50),
							array('income' => 999999, 'rate' => 9.3, 'constant' => 3946.14),
							array('income' => 999999, 'rate' => 10.3, 'constant' => 88610.64),
					),
					40 => array(
							array('income' => 13662, 'rate' => 1.0, 'constant' => 0),
							array('income' => 32370, 'rate' => 2.0, 'constant' => 136.62),
							array('income' => 41728, 'rate' => 4.0, 'constant' => 510.78),
							array('income' => 51643, 'rate' => 6.0, 'constant' => 885.10),
							array('income' => 61000, 'rate' => 8.0, 'constant' => 1480.00),
							array('income' => 999999, 'rate' => 9.3, 'constant' => 2228.56),
							array('income' => 999999, 'rate' => 10.3, 'constant' => 89555.47),
					),
			),
			20070101 => array(
					10 => array(
							array('income' => 6622, 'rate' => 1.0, 'constant' => 0),
							array('income' => 15698, 'rate' => 2.0, 'constant' => 66.22),
							array('income' => 24776, 'rate' => 4.0, 'constant' => 247.74),
							array('income' => 34394, 'rate' => 6.0, 'constant' => 610.86),
							array('income' => 43467, 'rate' => 8.0, 'constant' => 1187.94),
							array('income' => 999999, 'rate' => 9.3, 'constant' => 1913.78),
							array('income' => 999999, 'rate' => 10.3, 'constant' => 90871.26),
					),
					20 => array(
							array('income' => 6622, 'rate' => 1.0, 'constant' => 0),
							array('income' => 15698, 'rate' => 2.0, 'constant' => 66.22),
							array('income' => 24776, 'rate' => 4.0, 'constant' => 247.74),
							array('income' => 34394, 'rate' => 6.0, 'constant' => 610.86),
							array('income' => 43467, 'rate' => 8.0, 'constant' => 1187.94),
							array('income' => 999999, 'rate' => 9.3, 'constant' => 1913.78),
							array('income' => 999999, 'rate' => 10.3, 'constant' => 90871.26),
					),
					30 => array(
							array('income' => 13244, 'rate' => 1.0, 'constant' => 0),
							array('income' => 31396, 'rate' => 2.0, 'constant' => 132.44),
							array('income' => 49552, 'rate' => 4.0, 'constant' => 495.48),
							array('income' => 68788, 'rate' => 6.0, 'constant' => 1221.72),
							array('income' => 86934, 'rate' => 8.0, 'constant' => 2375.88),
							array('income' => 999999, 'rate' => 9.3, 'constant' => 3827.56),
							array('income' => 999999, 'rate' => 10.3, 'constant' => 88742.61),
					),
					40 => array(
							array('income' => 13251, 'rate' => 1.0, 'constant' => 0),
							array('income' => 31397, 'rate' => 2.0, 'constant' => 132.51),
							array('income' => 40473, 'rate' => 4.0, 'constant' => 495.43),
							array('income' => 50090, 'rate' => 6.0, 'constant' => 858.47),
							array('income' => 59166, 'rate' => 8.0, 'constant' => 1435.49),
							array('income' => 999999, 'rate' => 9.3, 'constant' => 2161.57),
							array('income' => 999999, 'rate' => 10.3, 'constant' => 89659.04),
					),
			),
			20060101 => array(
					10 => array(
							array('income' => 6319, 'rate' => 1.0, 'constant' => 0),
							array('income' => 14979, 'rate' => 2.0, 'constant' => 63.19),
							array('income' => 23641, 'rate' => 4.0, 'constant' => 236.39),
							array('income' => 32819, 'rate' => 6.0, 'constant' => 582.87),
							array('income' => 41476, 'rate' => 8.0, 'constant' => 1133.55),
							array('income' => 999999, 'rate' => 9.3, 'constant' => 1826.11),
							array('income' => 999999, 'rate' => 10.3, 'constant' => 90968.75),
					),
					20 => array(
							array('income' => 6319, 'rate' => 1.0, 'constant' => 0),
							array('income' => 14979, 'rate' => 2.0, 'constant' => 63.19),
							array('income' => 23641, 'rate' => 4.0, 'constant' => 236.39),
							array('income' => 32819, 'rate' => 6.0, 'constant' => 582.87),
							array('income' => 41476, 'rate' => 8.0, 'constant' => 1133.55),
							array('income' => 999999, 'rate' => 9.3, 'constant' => 1826.11),
							array('income' => 999999, 'rate' => 10.3, 'constant' => 90968.75),
					),
					30 => array(
							array('income' => 12638, 'rate' => 1.0, 'constant' => 0),
							array('income' => 29958, 'rate' => 2.0, 'constant' => 126.38),
							array('income' => 47282, 'rate' => 4.0, 'constant' => 472.78),
							array('income' => 65638, 'rate' => 6.0, 'constant' => 1165.74),
							array('income' => 82952, 'rate' => 8.0, 'constant' => 2267.10),
							array('income' => 999999, 'rate' => 9.3, 'constant' => 3652.22),
							array('income' => 999999, 'rate' => 10.3, 'constant' => 88937.59),
					),
					40 => array(
							array('income' => 12644, 'rate' => 1.0, 'constant' => 0),
							array('income' => 29959, 'rate' => 2.0, 'constant' => 126.44),
							array('income' => 38619, 'rate' => 4.0, 'constant' => 472.74),
							array('income' => 47796, 'rate' => 6.0, 'constant' => 819.14),
							array('income' => 56456, 'rate' => 8.0, 'constant' => 1369.76),
							array('income' => 999999, 'rate' => 9.3, 'constant' => 2062.56),
							array('income' => 999999, 'rate' => 10.3, 'constant' => 89812.06),
					),
			),
	);

	var $state_options = array(
			20180101 => array( //01-Jan-18
							   //Standard Deduction Table
							   'standard_deduction' => array(
								   //First entry is 0,1 allowance, second is for 2 or more.
								   '10' => array(4236.00, 4236.00),
								   '20' => array(4236.00, 4236.00),
								   '30' => array(4236.00, 8472.00),
								   '40' => array(8472.00, 8472.00),
							   ),
							   //Exemption Allowance Table
							   'allowance'          => array(
									   '10' => 125.40,
									   '20' => 125.40,
									   '30' => 125.40,
									   '40' => 125.40,
							   ),
							   //Low Income Exemption Table
							   'minimum_income'     => array(
								   //First entry is 0,1 allowance, 2nd is 2 or more.
								   '10' => array(14048.00, 14048.00),
								   '20' => array(14048.00, 14048.00),
								   '30' => array(14048.00, 28095.00),
								   '40' => array(28095.00, 28095.00),
							   ),
			),
			20170101 => array( //01-Jan-17
							   //Standard Deduction Table
							   'standard_deduction' => array(
								   //First entry is 0,1 allowance, second is for 2 or more.
								   '10' => array(4129.00, 4129.00),
								   '20' => array(4129.00, 4129.00),
								   '30' => array(4129.00, 8258.00),
								   '40' => array(8258.00, 8258.00),
							   ),
							   //Exemption Allowance Table
							   'allowance'          => array(
									   '10' => 122.10,
									   '20' => 122.10,
									   '30' => 122.10,
									   '40' => 122.10,
							   ),
							   //Low Income Exemption Table
							   'minimum_income'     => array(
								   //First entry is 0,1 allowance, 2nd is 2 or more.
								   '10' => array(13687.00, 13687.00),
								   '20' => array(13687.00, 13687.00),
								   '30' => array(13687.00, 27373.00),
								   '40' => array(27373.00, 27373.00),
							   ),
			),
			20160101 => array( //01-Jan-16
							   //Standard Deduction Table
							   'standard_deduction' => array(
								   //First entry is 0,1 allowance, second is for 2 or more.
								   '10' => array(4044.00, 4044.00),
								   '20' => array(4044.00, 4044.00),
								   '30' => array(4044.00, 8088.00),
								   '40' => array(8088.00, 8088.00),
							   ),
							   //Exemption Allowance Table
							   'allowance'          => array(
									   '10' => 119.90,
									   '20' => 119.90,
									   '30' => 119.90,
									   '40' => 119.90,
							   ),
							   //Low Income Exemption Table
							   'minimum_income'     => array(
								   //First entry is 0,1 allowance, 2nd is 2 or more.
								   '10' => array(13419.00, 13419.00),
								   '20' => array(13419.00, 13419.00),
								   '30' => array(13419.00, 26838.00),
								   '40' => array(26838.00, 26838.00),
							   ),
			),
			20150101 => array( //01-Jan-15
							   //Standard Deduction Table
							   'standard_deduction' => array(
								   //First entry is 0,1 allowance, second is for 2 or more.
								   '10' => array(3992.00, 3992.00),
								   '20' => array(3992.00, 3992.00),
								   '30' => array(3992.00, 7984.00),
								   '40' => array(7984.00, 7984.00),
							   ),
							   //Exemption Allowance Table
							   'allowance'          => array(
									   '10' => 118.80,
									   '20' => 118.80,
									   '30' => 118.80,
									   '40' => 118.80,
							   ),
							   //Low Income Exemption Table
							   'minimum_income'     => array(
								   //First entry is 0,1 allowance, 2nd is 2 or more.
								   '10' => array(13267.00, 13267.00),
								   '20' => array(13267.00, 13267.00),
								   '30' => array(13267.00, 26533.00),
								   '40' => array(26533.00, 26533.00),
							   ),
			),
			20140101 => array( //01-Jan-14
							   //Standard Deduction Table
							   'standard_deduction' => array(
								   //First entry is 0,1 allowance, second is for 2 or more.
								   '10' => array(3906.00, 3906.00),
								   '20' => array(3906.00, 3906.00),
								   '30' => array(3906.00, 7812.00),
								   '40' => array(7812.00, 7812.00),
							   ),
							   //Exemption Allowance Table
							   'allowance'          => array(
									   '10' => 116.60,
									   '20' => 116.60,
									   '30' => 116.60,
									   '40' => 116.60,
							   ),
							   //Low Income Exemption Table
							   'minimum_income'     => array(
								   //First entry is 0,1 allowance, 2nd is 2 or more.
								   '10' => array(12997.00, 12997.00),
								   '20' => array(12997.00, 12997.00),
								   '30' => array(12997.00, 25994.00),
								   '40' => array(25994.00, 25994.00),
							   ),
			),
			20130101 => array( //01-Jan-13
							   'standard_deduction' => array(
								   //First entry is 0,1 allowance, second is for 2 or more.
								   '10' => array(3841.00, 3841.00),
								   '20' => array(3841.00, 3841.00),
								   '30' => array(3841.00, 7682.00),
								   '40' => array(7682.00, 7682.00),
							   ),
							   'allowance'          => array(
									   '10' => 114.40,
									   '20' => 114.40,
									   '30' => 114.40,
									   '40' => 114.40,
							   ),
							   'minimum_income'     => array(
								   //First entry is 0,1 allowance, 2nd is 2 or more.
								   '10' => array(12769.00, 12769.00),
								   '20' => array(12769.00, 12769.00),
								   '30' => array(12769.00, 25537.00),
								   '40' => array(25537.00, 25537.00),
							   ),
			),
			20120101 => array( //01-Jan-12
							   'standard_deduction' => array(
								   //First entry is 0,1 allowance, second is for 2 or more.
								   '10' => array(3769.00, 3769.00),
								   '20' => array(3769.00, 3769.00),
								   '30' => array(3769.00, 7538.00),
								   '40' => array(7538.00, 7538.00),
							   ),
							   'allowance'          => array(
									   '10' => 112.20,
									   '20' => 112.20,
									   '30' => 112.20,
									   '40' => 112.20,
							   ),
							   'minimum_income'     => array(
								   //First entry is 0,1 allowance, 2nd is 2 or more.
								   '10' => array(12527.00, 12527.00),
								   '20' => array(12527.00, 12527.00),
								   '30' => array(12527.00, 25054.00),
								   '40' => array(25054.00, 25054.00),
							   ),
			),
			20110101 => array( //01-Jan-11
							   'standard_deduction' => array(
								   //First entry is 0,1 allowance, second is for 2 or more.
								   '10' => array(3670.00, 3670.00),
								   '20' => array(3670.00, 3670.00),
								   '30' => array(3670.00, 7340.00),
								   '40' => array(7340.00, 7340.00),
							   ),
							   'allowance'          => array(
									   '10' => 108.90,
									   '20' => 108.90,
									   '30' => 108.90,
									   '40' => 108.90,
							   ),
			),
			20100101 => array( //01-Jan-10
							   'standard_deduction' => array(
								   //First entry is 0,1 allowance, second is for 2 or more.
								   '10' => array(3637.00, 3637.00),
								   '20' => array(3637.00, 3637.00),
								   '30' => array(3637.00, 7274.00),
								   '40' => array(7274.00, 7274.00),
							   ),
							   'allowance'          => array(
									   '10' => 107.80,
									   '20' => 107.80,
									   '30' => 107.80,
									   '40' => 107.80,
							   ),
			),
			20091101 => array( //01-Nov-09
							   'standard_deduction' => array(
								   //First entry is 0,1 allowance, second is for 2 or more.
								   '10' => array(3692.00, 3692.00),
								   '20' => array(3692.00, 3692.00),
								   '30' => array(3692.00, 7384.00),
								   '40' => array(7384.00, 7384.00),
							   ),
							   'allowance'          => array(
									   '10' => 108.90,
									   '20' => 108.90,
									   '30' => 108.90,
									   '40' => 108.90,
							   ),
			),
			20090101 => array(
					'standard_deduction' => array(
						//First entry is 0,1 allowance, second is for 2 or more.
						'10' => array(3692.00, 3692.00),
						'20' => array(3692.00, 3692.00),
						'30' => array(3692.00, 7384.00),
						'40' => array(7384.00, 7384.00),
					),
					'allowance'          => array(
							'10' => 99.00,
							'20' => 99.00,
							'30' => 99.00,
							'40' => 99.00,
					),
			),
			20080101 => array(
					'standard_deduction' => array(
						//First entry is 0,1 allowance, second is for 2 or more.
						'10' => array(3516.00, 3516.00),
						'20' => array(3516.00, 3516.00),
						'30' => array(3516.00, 7032.00),
						'40' => array(7032.00, 7032.00),
					),
					'allowance'          => array(
							'10' => 94.00,
							'20' => 94.00,
							'30' => 94.00,
							'40' => 94.00,
					),
			),
			20070101 => array(
					'standard_deduction' => array(
						//First entry is 0,1 allowance, second is for 2 or more.
						'10' => array(3410.00, 3410.00),
						'20' => array(3410.00, 3410.00),
						'30' => array(3410.00, 6820.00),
						'40' => array(6820.00, 6820.00),
					),
					'allowance'          => array(
							'10' => 91.00,
							'20' => 91.00,
							'30' => 91.00,
							'40' => 91.00,
					),
			),
			20060101 => array(
					'standard_deduction' => array(
						//First entry is 0,1 allowance, second is for 2 or more.
						'10' => array(3254.00, 3254.00),
						'20' => array(3254.00, 3254.00),
						'30' => array(3254.00, 6508.00),
						'40' => array(6508.00, 6508.00),
					),
					'allowance'          => array(
							'10' => 87.00,
							'20' => 87.00,
							'30' => 87.00,
							'40' => 87.00,
					),
			),
	);

	function getStateAnnualTaxableIncome() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		$minimum_income = 0;
		if ( isset( $retarr['minimum_income'] ) AND isset( $retarr['minimum_income'][ $this->getStateFilingStatus() ] ) ) {
			$minimum_income_arr = $retarr['minimum_income'][ $this->getStateFilingStatus() ];
			if ( $this->getStateAllowance() == 0 OR $this->getStateAllowance() == 1 ) {
				$minimum_income = $minimum_income_arr[0];
			} elseif ( $this->getStateAllowance() >= 2 ) {
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
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		if ( isset( $retarr['standard_deduction'][ $this->getStateFilingStatus() ] ) ) {
			$deduction_arr = $retarr['standard_deduction'][ $this->getStateFilingStatus() ];
		} else {
			$deduction_arr = $retarr['standard_deduction'][10];
		}

		$deduction = 0;
		if ( $this->getStateAllowance() == 0 OR $this->getStateAllowance() == 1 ) {
			$deduction = $deduction_arr[0];
		} elseif ( $this->getStateAllowance() >= 2 ) {
			$deduction = $deduction_arr[1];
		}
		Debug::text( 'Standard Deduction: ' . $deduction . ' Allowances: ' . $this->getStateAllowance() . ' Filing Status: ' . $this->getStateFilingStatus(), __FILE__, __LINE__, __METHOD__, 10 );

		return $deduction;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == FALSE ) {
			return FALSE;
		}

		if ( isset( $retarr['allowance'][ $this->getStateFilingStatus() ] ) ) {
			$allowance = $retarr['allowance'][ $this->getStateFilingStatus() ];
		} else {
			$allowance = 0;
		}

		$retval = 0;
		if ( $this->getStateAllowance() == 0 ) {
			$retval = 0;
		} elseif ( $this->getStateAllowance() >= 1 ) {
			$retval = bcmul( $allowance, $this->getStateAllowance() );
		}

		Debug::text( 'State Allowance Amount: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	function getStateTaxPayable() {
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
		if ( $this->getUIExempt() == TRUE ) {
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
