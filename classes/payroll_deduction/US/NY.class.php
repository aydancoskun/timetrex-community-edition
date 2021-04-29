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
class PayrollDeduction_US_NY extends PayrollDeduction_US { //Use New York State (NYS) withholding guide.
	/*
															10 => 'Single',
															20 => 'Married',

	Used to be:
															10 => 'Single',
															20 => 'Married - Spouse Works',
															30 => 'Married - Spouse does not Work',
															40 => 'Head of Household',
	*/

	var $state_income_tax_rate_options = [
			20210101 => [
					10 => [
							[ 'income' => 8500, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11700, 'rate' => 4.5, 'constant' => 340 ],
							[ 'income' => 13900, 'rate' => 5.25, 'constant' => 484 ],
							[ 'income' => 21400, 'rate' => 5.90, 'constant' => 600 ],
							[ 'income' => 80650, 'rate' => 5.97, 'constant' => 1042 ],
							[ 'income' => 96800, 'rate' => 6.33, 'constant' => 4579 ],
							[ 'income' => 107650, 'rate' => 7.38, 'constant' => 5602 ],
							[ 'income' => 157650, 'rate' => 7.88, 'constant' => 6402 ],
							[ 'income' => 215400, 'rate' => 6.83, 'constant' => 10342 ],
							[ 'income' => 265400, 'rate' => 9.59, 'constant' => 14287 ],
							[ 'income' => 1077550, 'rate' => 7.35, 'constant' => 19082 ],
							[ 'income' => 1127550, 'rate' => 52.08, 'constant' => 78775 ],
							[ 'income' => 1127550, 'rate' => 9.62, 'constant' => 104815 ],
					],
					20 => [
							[ 'income' => 8500, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11700, 'rate' => 4.5, 'constant' => 340 ],
							[ 'income' => 13900, 'rate' => 5.25, 'constant' => 484 ],
							[ 'income' => 21400, 'rate' => 5.90, 'constant' => 600 ],
							[ 'income' => 80650, 'rate' => 5.97, 'constant' => 1042 ],
							[ 'income' => 96800, 'rate' => 6.33, 'constant' => 4579 ],
							[ 'income' => 107650, 'rate' => 7.28, 'constant' => 5602 ],
							[ 'income' => 157650, 'rate' => 7.78, 'constant' => 6391 ],
							[ 'income' => 211550, 'rate' => 7.99, 'constant' => 10281 ],
							[ 'income' => 323200, 'rate' => 6.83, 'constant' => 14588 ],
							[ 'income' => 373200, 'rate' => 10.71, 'constant' => 22214 ],
							[ 'income' => 1077550, 'rate' => 7.35, 'constant' => 27569 ],
							[ 'income' => 2155350, 'rate' => 7.65, 'constant' => 79338 ],
							[ 'income' => 2205350, 'rate' => 94.54, 'constant' => 161790 ],
							[ 'income' => 2205350, 'rate' => 9.62, 'constant' => 209060 ],
					],
			],
			20200101 => [
					10 => [
							[ 'income' => 8500, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11700, 'rate' => 4.5, 'constant' => 340 ],
							[ 'income' => 13900, 'rate' => 5.25, 'constant' => 484 ],
							[ 'income' => 21400, 'rate' => 5.90, 'constant' => 600 ],
							[ 'income' => 80650, 'rate' => 6.09, 'constant' => 1042 ],
							[ 'income' => 96800, 'rate' => 6.41, 'constant' => 4650 ],
							[ 'income' => 107650, 'rate' => 7.45, 'constant' => 5686 ],
							[ 'income' => 157650, 'rate' => 7.95, 'constant' => 6494 ],
							[ 'income' => 215400, 'rate' => 6.91, 'constant' => 10469 ],
							[ 'income' => 265400, 'rate' => 9.25, 'constant' => 14459 ],
							[ 'income' => 1077550, 'rate' => 7.35, 'constant' => 19084 ],
							[ 'income' => 1127550, 'rate' => 52.08, 'constant' => 78777 ],
							[ 'income' => 1127550, 'rate' => 9.62, 'constant' => 104817 ],
					],
					20 => [
							[ 'income' => 8500, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11700, 'rate' => 4.5, 'constant' => 340 ],
							[ 'income' => 13900, 'rate' => 5.25, 'constant' => 484 ],
							[ 'income' => 21400, 'rate' => 5.90, 'constant' => 600 ],
							[ 'income' => 80650, 'rate' => 6.09, 'constant' => 1042 ],
							[ 'income' => 96800, 'rate' => 6.41, 'constant' => 4650 ],
							[ 'income' => 107650, 'rate' => 7.46, 'constant' => 5686 ],
							[ 'income' => 157650, 'rate' => 7.96, 'constant' => 6495 ],
							[ 'income' => 211550, 'rate' => 7.94, 'constant' => 10475 ],
							[ 'income' => 323200, 'rate' => 6.91, 'constant' => 14755 ],
							[ 'income' => 373200, 'rate' => 10.19, 'constant' => 22470 ],
							[ 'income' => 1077550, 'rate' => 7.35, 'constant' => 27565 ],
							[ 'income' => 2155350, 'rate' => 7.65, 'constant' => 79334 ],
							[ 'income' => 2205350, 'rate' => 94.54, 'constant' => 161786 ],
							[ 'income' => 2205350, 'rate' => 9.62, 'constant' => 209056 ],
					],
			],
			//20190101 - No Change
			20180101 => [
					10 => [
							[ 'income' => 8500, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11700, 'rate' => 4.5, 'constant' => 340 ],
							[ 'income' => 13900, 'rate' => 5.25, 'constant' => 484 ],
							[ 'income' => 21400, 'rate' => 5.90, 'constant' => 600 ],
							[ 'income' => 80650, 'rate' => 6.21, 'constant' => 1042 ],
							[ 'income' => 96800, 'rate' => 6.49, 'constant' => 4721 ],
							[ 'income' => 107650, 'rate' => 7.52, 'constant' => 5770 ],
							[ 'income' => 157650, 'rate' => 8.02, 'constant' => 6585 ],
							[ 'income' => 215400, 'rate' => 6.99, 'constant' => 10595 ],
							[ 'income' => 265400, 'rate' => 8.90, 'constant' => 14632 ],
							[ 'income' => 1077550, 'rate' => 7.35, 'constant' => 19082 ],
							[ 'income' => 1127550, 'rate' => 52.08, 'constant' => 78775 ],
							[ 'income' => 1127550, 'rate' => 9.62, 'constant' => 104815 ],
					],
					20 => [
							[ 'income' => 8500, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11700, 'rate' => 4.5, 'constant' => 340 ],
							[ 'income' => 13900, 'rate' => 5.25, 'constant' => 484 ],
							[ 'income' => 21400, 'rate' => 5.90, 'constant' => 600 ],
							[ 'income' => 80650, 'rate' => 6.21, 'constant' => 1042 ],
							[ 'income' => 96800, 'rate' => 6.49, 'constant' => 4721 ],
							[ 'income' => 107650, 'rate' => 7.64, 'constant' => 5770 ],
							[ 'income' => 157650, 'rate' => 8.14, 'constant' => 6599 ],
							[ 'income' => 211550, 'rate' => 7.90, 'constant' => 10669 ],
							[ 'income' => 323200, 'rate' => 6.99, 'constant' => 14927 ],
							[ 'income' => 373200, 'rate' => 9.68, 'constant' => 22731 ],
							[ 'income' => 1077550, 'rate' => 7.35, 'constant' => 27571 ],
							[ 'income' => 2155350, 'rate' => 7.65, 'constant' => 79341 ],
							[ 'income' => 2205350, 'rate' => 94.54, 'constant' => 161792 ],
							[ 'income' => 2205350, 'rate' => 9.62, 'constant' => 209062 ],
					],
			],
			20170101 => [
					10 => [
							[ 'income' => 8500, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11700, 'rate' => 4.5, 'constant' => 340 ],
							[ 'income' => 13900, 'rate' => 5.25, 'constant' => 484 ],
							[ 'income' => 21400, 'rate' => 5.90, 'constant' => 600 ],
							[ 'income' => 80650, 'rate' => 6.45, 'constant' => 1042 ],
							[ 'income' => 96800, 'rate' => 6.65, 'constant' => 4864 ],
							[ 'income' => 107650, 'rate' => 7.58, 'constant' => 5938 ],
							[ 'income' => 161550, 'rate' => 8.08, 'constant' => 6760 ],
							[ 'income' => 215400, 'rate' => 7.15, 'constant' => 11115 ],
							[ 'income' => 269300, 'rate' => 8.15, 'constant' => 14965 ],
							[ 'income' => 1077550, 'rate' => 7.35, 'constant' => 19358 ],
							[ 'income' => 1131500, 'rate' => 49.02, 'constant' => 78765 ],
							[ 'income' => 1131500, 'rate' => 9.62, 'constant' => 105211 ],
					],
					20 => [
							[ 'income' => 8500, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11700, 'rate' => 4.5, 'constant' => 340 ],
							[ 'income' => 13900, 'rate' => 5.25, 'constant' => 484 ],
							[ 'income' => 21400, 'rate' => 5.90, 'constant' => 600 ],
							[ 'income' => 80650, 'rate' => 6.45, 'constant' => 1042 ],
							[ 'income' => 96800, 'rate' => 6.65, 'constant' => 4864 ],
							[ 'income' => 107650, 'rate' => 7.28, 'constant' => 5938 ],
							[ 'income' => 161550, 'rate' => 7.78, 'constant' => 6727 ],
							[ 'income' => 215400, 'rate' => 8.08, 'constant' => 10921 ],
							[ 'income' => 323200, 'rate' => 7.15, 'constant' => 15272 ],
							[ 'income' => 377100, 'rate' => 8.15, 'constant' => 22980 ],
							[ 'income' => 1077550, 'rate' => 7.35, 'constant' => 27373 ],
							[ 'income' => 2155350, 'rate' => 7.65, 'constant' => 78856 ],
							[ 'income' => 2209300, 'rate' => 88.42, 'constant' => 161307 ],
							[ 'income' => 2209300, 'rate' => 9.62, 'constant' => 209010 ],
					],
			],
			20160101 => [
					10 => [
							[ 'income' => 8450, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11650, 'rate' => 4.5, 'constant' => 338 ],
							[ 'income' => 13850, 'rate' => 5.25, 'constant' => 482 ],
							[ 'income' => 21300, 'rate' => 5.90, 'constant' => 598 ],
							[ 'income' => 80150, 'rate' => 6.45, 'constant' => 1037 ],
							[ 'income' => 96200, 'rate' => 6.65, 'constant' => 4833 ],
							[ 'income' => 106950, 'rate' => 7.58, 'constant' => 5900 ],
							[ 'income' => 160500, 'rate' => 8.08, 'constant' => 6715 ],
							[ 'income' => 214000, 'rate' => 7.15, 'constant' => 11042 ],
							[ 'income' => 267500, 'rate' => 8.15, 'constant' => 14867 ],
							[ 'income' => 1070350, 'rate' => 7.35, 'constant' => 19227 ],
							[ 'income' => 1123950, 'rate' => 49.02, 'constant' => 78237 ],
							[ 'income' => 1123950, 'rate' => 9.62, 'constant' => 104512 ],
					],
					20 => [
							[ 'income' => 8450, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11650, 'rate' => 4.5, 'constant' => 338 ],
							[ 'income' => 13850, 'rate' => 5.25, 'constant' => 482 ],
							[ 'income' => 21300, 'rate' => 5.90, 'constant' => 598 ],
							[ 'income' => 80150, 'rate' => 6.45, 'constant' => 1037 ],
							[ 'income' => 96200, 'rate' => 6.65, 'constant' => 4833 ],
							[ 'income' => 106950, 'rate' => 7.28, 'constant' => 5900 ],
							[ 'income' => 160500, 'rate' => 7.78, 'constant' => 6683 ],
							[ 'income' => 214000, 'rate' => 8.08, 'constant' => 10849 ],
							[ 'income' => 321050, 'rate' => 7.15, 'constant' => 15172 ],
							[ 'income' => 374600, 'rate' => 8.15, 'constant' => 22826 ],
							[ 'income' => 1070350, 'rate' => 7.35, 'constant' => 27190 ],
							[ 'income' => 2140900, 'rate' => 7.65, 'constant' => 78328 ],
							[ 'income' => 2194500, 'rate' => 88.42, 'constant' => 160225 ],
							[ 'income' => 2194500, 'rate' => 9.62, 'constant' => 207618 ],
					],
			],
			20150101 => [
					10 => [
							[ 'income' => 8400, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11600, 'rate' => 4.5, 'constant' => 336 ],
							[ 'income' => 13750, 'rate' => 5.25, 'constant' => 480 ],
							[ 'income' => 21150, 'rate' => 5.90, 'constant' => 593 ],
							[ 'income' => 79600, 'rate' => 6.45, 'constant' => 1029 ],
							[ 'income' => 95550, 'rate' => 6.65, 'constant' => 4800 ],
							[ 'income' => 106200, 'rate' => 7.58, 'constant' => 5860 ],
							[ 'income' => 159350, 'rate' => 8.08, 'constant' => 6667 ],
							[ 'income' => 212500, 'rate' => 7.15, 'constant' => 10962 ],
							[ 'income' => 265600, 'rate' => 8.15, 'constant' => 14762 ],
							[ 'income' => 1062650, 'rate' => 7.35, 'constant' => 19090 ],
							[ 'income' => 1115850, 'rate' => 49.62, 'constant' => 103752 ],
							[ 'income' => 1115850, 'rate' => 49.02, 'constant' => 77673 ],
					],
					20 => [
							[ 'income' => 8400, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11600, 'rate' => 4.5, 'constant' => 336 ],
							[ 'income' => 13750, 'rate' => 5.25, 'constant' => 480 ],
							[ 'income' => 21150, 'rate' => 5.90, 'constant' => 593 ],
							[ 'income' => 79600, 'rate' => 6.45, 'constant' => 1029 ],
							[ 'income' => 95550, 'rate' => 6.65, 'constant' => 4800 ],
							[ 'income' => 106200, 'rate' => 7.28, 'constant' => 5860 ],
							[ 'income' => 159350, 'rate' => 7.78, 'constant' => 6635 ],
							[ 'income' => 212500, 'rate' => 8.08, 'constant' => 10771 ],
							[ 'income' => 318750, 'rate' => 7.15, 'constant' => 15065 ],
							[ 'income' => 371900, 'rate' => 8.15, 'constant' => 22662 ],
							[ 'income' => 1062650, 'rate' => 7.35, 'constant' => 26994 ],
							[ 'income' => 2125450, 'rate' => 7.65, 'constant' => 77764 ],
							[ 'income' => 2178650, 'rate' => 9.62, 'constant' => 206107 ],
							[ 'income' => 2178650, 'rate' => 88.42, 'constant' => 159068 ],
					],
			],
			20140101 => [
					10 => [
							[ 'income' => 8300, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11450, 'rate' => 4.5, 'constant' => 332 ],
							[ 'income' => 13550, 'rate' => 5.25, 'constant' => 474 ],
							[ 'income' => 20850, 'rate' => 5.90, 'constant' => 584 ],
							[ 'income' => 78400, 'rate' => 6.45, 'constant' => 1015 ],
							[ 'income' => 94100, 'rate' => 6.65, 'constant' => 4727 ],
							[ 'income' => 104600, 'rate' => 7.58, 'constant' => 5771 ],
							[ 'income' => 156900, 'rate' => 8.08, 'constant' => 6567 ],
							[ 'income' => 209250, 'rate' => 7.15, 'constant' => 10792 ],
							[ 'income' => 261550, 'rate' => 8.15, 'constant' => 14535 ],
							[ 'income' => 1046350, 'rate' => 7.35, 'constant' => 18798 ],
							[ 'income' => 1098700, 'rate' => 49.02, 'constant' => 76481 ],
							[ 'income' => 1098700, 'rate' => 9.62, 'constant' => 102143 ],
					],
					20 => [
							[ 'income' => 8300, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11450, 'rate' => 4.5, 'constant' => 332 ],
							[ 'income' => 13550, 'rate' => 5.25, 'constant' => 474 ],
							[ 'income' => 20850, 'rate' => 5.90, 'constant' => 584 ],
							[ 'income' => 78400, 'rate' => 6.45, 'constant' => 1015 ],
							[ 'income' => 94100, 'rate' => 6.65, 'constant' => 4727 ],
							[ 'income' => 104600, 'rate' => 7.28, 'constant' => 5771 ],
							[ 'income' => 156900, 'rate' => 7.78, 'constant' => 6535 ],
							[ 'income' => 209250, 'rate' => 8.08, 'constant' => 10604 ],
							[ 'income' => 313850, 'rate' => 7.15, 'constant' => 14834 ],
							[ 'income' => 366200, 'rate' => 8.15, 'constant' => 22313 ],
							[ 'income' => 1046350, 'rate' => 7.35, 'constant' => 26579 ],
							[ 'income' => 2092800, 'rate' => 7.65, 'constant' => 76570 ],
							[ 'income' => 2145150, 'rate' => 88.42, 'constant' => 156624 ],
							[ 'income' => 2145150, 'rate' => 9.62, 'constant' => 202912 ],
					],
			],
			20130101 => [
					10 => [
							[ 'income' => 8200, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11300, 'rate' => 4.5, 'constant' => 328 ],
							[ 'income' => 13350, 'rate' => 5.25, 'constant' => 468 ],
							[ 'income' => 20550, 'rate' => 5.90, 'constant' => 575 ],
							[ 'income' => 77150, 'rate' => 6.45, 'constant' => 1000 ],
							[ 'income' => 92600, 'rate' => 6.65, 'constant' => 4651 ],
							[ 'income' => 102900, 'rate' => 7.58, 'constant' => 5678 ],
							[ 'income' => 154350, 'rate' => 8.08, 'constant' => 6459 ],
							[ 'income' => 205850, 'rate' => 7.15, 'constant' => 10616 ],
							[ 'income' => 257300, 'rate' => 8.15, 'constant' => 14298 ],
							[ 'income' => 1029250, 'rate' => 7.35, 'constant' => 18491 ],
							[ 'income' => 1080750, 'rate' => 49.02, 'constant' => 75230 ],
							[ 'income' => 1080750, 'rate' => 9.62, 'constant' => 100475 ],
					],
					20 => [
							[ 'income' => 8200, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11300, 'rate' => 4.5, 'constant' => 328 ],
							[ 'income' => 13350, 'rate' => 5.25, 'constant' => 468 ],
							[ 'income' => 20550, 'rate' => 5.90, 'constant' => 575 ],
							[ 'income' => 77150, 'rate' => 6.45, 'constant' => 1000 ],
							[ 'income' => 92600, 'rate' => 6.65, 'constant' => 4651 ],
							[ 'income' => 102900, 'rate' => 7.28, 'constant' => 5678 ],
							[ 'income' => 154350, 'rate' => 7.78, 'constant' => 6428 ],
							[ 'income' => 205850, 'rate' => 8.08, 'constant' => 10431 ],
							[ 'income' => 308750, 'rate' => 7.15, 'constant' => 14592 ],
							[ 'income' => 360250, 'rate' => 8.15, 'constant' => 21949 ],
							[ 'income' => 1029250, 'rate' => 7.35, 'constant' => 26147 ],
							[ 'income' => 2058550, 'rate' => 7.65, 'constant' => 75318 ],
							[ 'income' => 2110050, 'rate' => 88.42, 'constant' => 154059 ],
							[ 'income' => 2110050, 'rate' => 9.62, 'constant' => 199596 ],
					],
			],
			20100101 => [
					10 => [
							[ 'income' => 8000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11000, 'rate' => 4.5, 'constant' => 320 ],
							[ 'income' => 13000, 'rate' => 5.25, 'constant' => 455 ],
							[ 'income' => 20000, 'rate' => 5.90, 'constant' => 560 ],
							[ 'income' => 90000, 'rate' => 6.85, 'constant' => 973 ],
							[ 'income' => 100000, 'rate' => 7.64, 'constant' => 5768 ],
							[ 'income' => 150000, 'rate' => 8.14, 'constant' => 6532 ],
							[ 'income' => 200000, 'rate' => 7.35, 'constant' => 10602 ],
							[ 'income' => 300000, 'rate' => 8.35, 'constant' => 14277 ],
							[ 'income' => 350000, 'rate' => 12.35, 'constant' => 22627 ],
							[ 'income' => 500000, 'rate' => 8.35, 'constant' => 28802 ],
							[ 'income' => 550000, 'rate' => 20.67, 'constant' => 41327 ],
							[ 'income' => 550000, 'rate' => 9.77, 'constant' => 51662 ],
					],
					20 => [
							[ 'income' => 8000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11000, 'rate' => 4.5, 'constant' => 320 ],
							[ 'income' => 13000, 'rate' => 5.25, 'constant' => 455 ],
							[ 'income' => 20000, 'rate' => 5.90, 'constant' => 560 ],
							[ 'income' => 90000, 'rate' => 6.85, 'constant' => 973 ],
							[ 'income' => 100000, 'rate' => 7.64, 'constant' => 5768 ],
							[ 'income' => 150000, 'rate' => 8.14, 'constant' => 6532 ],
							[ 'income' => 300000, 'rate' => 7.35, 'constant' => 10602 ],
							[ 'income' => 350000, 'rate' => 14.35, 'constant' => 21627 ],
							[ 'income' => 500000, 'rate' => 8.35, 'constant' => 28802 ],
							[ 'income' => 550000, 'rate' => 20.67, 'constant' => 41327 ],
							[ 'income' => 550000, 'rate' => 9.77, 'constant' => 51662 ],
					],
			],
			20090501 => [
					10 => [
							[ 'income' => 8000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11000, 'rate' => 4.5, 'constant' => 320 ],
							[ 'income' => 13000, 'rate' => 5.25, 'constant' => 455 ],
							[ 'income' => 20000, 'rate' => 5.90, 'constant' => 560 ],
							[ 'income' => 90000, 'rate' => 6.85, 'constant' => 973 ],
							[ 'income' => 100000, 'rate' => 7.64, 'constant' => 5768 ],
							[ 'income' => 150000, 'rate' => 8.14, 'constant' => 6532 ],
							[ 'income' => 200000, 'rate' => 7.35, 'constant' => 10602 ],
							[ 'income' => 300000, 'rate' => 8.85, 'constant' => 14277 ],
							[ 'income' => 350000, 'rate' => 14.85, 'constant' => 23127 ],
							[ 'income' => 500000, 'rate' => 8.85, 'constant' => 30552 ],
							[ 'income' => 550000, 'rate' => 27.33, 'constant' => 43827 ],
							[ 'income' => 550000, 'rate' => 11.03, 'constant' => 57492 ],
					],
					20 => [
							[ 'income' => 8000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11000, 'rate' => 4.5, 'constant' => 320 ],
							[ 'income' => 13000, 'rate' => 5.25, 'constant' => 455 ],
							[ 'income' => 20000, 'rate' => 5.90, 'constant' => 560 ],
							[ 'income' => 90000, 'rate' => 6.85, 'constant' => 973 ],
							[ 'income' => 100000, 'rate' => 7.64, 'constant' => 5768 ],
							[ 'income' => 150000, 'rate' => 8.14, 'constant' => 6532 ],
							[ 'income' => 300000, 'rate' => 7.35, 'constant' => 10602 ],
							[ 'income' => 350000, 'rate' => 17.85, 'constant' => 21627 ],
							[ 'income' => 500000, 'rate' => 8.85, 'constant' => 30552 ],
							[ 'income' => 550000, 'rate' => 27.33, 'constant' => 43827 ],
							[ 'income' => 550000, 'rate' => 11.03, 'constant' => 57492 ],
					],
			],
			20060101 => [
					10 => [
							[ 'income' => 8000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11000, 'rate' => 4.5, 'constant' => 320 ],
							[ 'income' => 13000, 'rate' => 5.25, 'constant' => 455 ],
							[ 'income' => 20000, 'rate' => 5.90, 'constant' => 580 ],
							[ 'income' => 90000, 'rate' => 6.85, 'constant' => 973 ],
							[ 'income' => 100000, 'rate' => 7.64, 'constant' => 5768 ],
							[ 'income' => 150000, 'rate' => 8.14, 'constant' => 6532 ],
							[ 'income' => 150000, 'rate' => 7.35, 'constant' => 10604 ],
					],
					20 => [
							[ 'income' => 8000, 'rate' => 4.0, 'constant' => 0 ],
							[ 'income' => 11000, 'rate' => 4.5, 'constant' => 320 ],
							[ 'income' => 13000, 'rate' => 5.25, 'constant' => 455 ],
							[ 'income' => 20000, 'rate' => 5.90, 'constant' => 580 ],
							[ 'income' => 90000, 'rate' => 6.85, 'constant' => 973 ],
							[ 'income' => 100000, 'rate' => 7.64, 'constant' => 5768 ],
							[ 'income' => 150000, 'rate' => 8.14, 'constant' => 6532 ],
							[ 'income' => 150000, 'rate' => 7.35, 'constant' => 10604 ],
					],
			],
	];

	var $state_options = [
		//2021 - No Change
		//2020 - No Change
		//2019 - No Change
		//2018 - No Change
		20170101 => [ // 01-Jan-2017
					  'standard_deduction' => [
							  '10' => 7400.00,
							  '20' => 7950.00,
							  '30' => 7950.00,
							  '40' => 7400.00,
					  ],
					  'allowance'          => [
							  '10' => 1000,
							  '20' => 1000,
							  '30' => 1000,
							  '40' => 1000,
					  ],
		],
		20160101 => [ // 01-Jan-2016
					  'standard_deduction' => [
							  '10' => 7400.00,
							  '20' => 7900.00,
							  '30' => 7900.00,
							  '40' => 7400.00,
					  ],
					  'allowance'          => [
							  '10' => 1000,
							  '20' => 1000,
							  '30' => 1000,
							  '40' => 1000,
					  ],
		],
		20150101 => [ // 01-Jan-2015
					  'standard_deduction' => [
							  '10' => 7350.00,
							  '20' => 7850.00,
							  '30' => 7850.00,
							  '40' => 7350.00,
					  ],
					  'allowance'          => [
							  '10' => 1000,
							  '20' => 1000,
							  '30' => 1000,
							  '40' => 1000,
					  ],
		],
		20140101 => [ // 01-Jan-2014
					  'standard_deduction' => [
							  '10' => 7250.00,
							  '20' => 7750.00,
							  '30' => 7750.00,
							  '40' => 7250.00,
					  ],
					  'allowance'          => [
							  '10' => 1000,
							  '20' => 1000,
							  '30' => 1000,
							  '40' => 1000,
					  ],
		],
		20130101 => [ // 01-Jan-2013
					  'standard_deduction' => [
							  '10' => 7150.00,
							  '20' => 7650.00,
							  '30' => 7650.00,
							  '40' => 7150.00,
					  ],
					  'allowance'          => [
							  '10' => 1000,
							  '20' => 1000,
							  '30' => 1000,
							  '40' => 1000,
					  ],
		],
		20060101 => [
				'standard_deduction' => [
						'10' => 6975.00,
						'20' => 7475.00,
						'30' => 7475.00,
						'40' => 6975.00,
				],
				'allowance'          => [
						'10' => 1000,
						'20' => 1000,
						'30' => 1000,
						'40' => 1000,
				],
		],
	];

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$federal_tax = $this->getFederalTaxPayable();
		$state_deductions = $this->getStateStandardDeduction();
		$state_allowance = $this->getStateAllowanceAmount();

		$income = bcsub( bcsub( $annual_income, $state_deductions ), $state_allowance );

		Debug::text( 'State Annual Taxable Income: ' . $income, __FILE__, __LINE__, __METHOD__, 10 );

		return $income;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray( $this->getDate(), $this->state_options );
		if ( $retarr == false ) {
			return false;
		}

		if ( isset( $retarr['standard_deduction'][$this->getStateFilingStatus()] ) ) {
			$deduction = $retarr['standard_deduction'][$this->getStateFilingStatus()];
		} else {
			$deduction = 0;
		}

		Debug::text( 'Standard Deduction: ' . $deduction, __FILE__, __LINE__, __METHOD__, 10 );

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

		if ( $this->getStateAllowance() == 0 ) {
			$retval = 0;
		} else {
			$retval = bcmul( $this->getStateAllowance(), $allowance );
		}

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
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text( 'State Annual Tax Payable: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}
}

?>
