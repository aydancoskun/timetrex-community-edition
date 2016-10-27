<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2013 TimeTrex Software Inc.
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
 * #292 Westbank, BC V4T 2E9, Canada or at email address info@timetrex.com.
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
/*
 * $Revision: 11758 $
 * $Id: CT.class.php 11758 2013-12-20 20:07:32Z mikeb $
 * $Date: 2013-12-20 12:07:32 -0800 (Fri, 20 Dec 2013) $
 */

/**
 * @package PayrollDeduction\US
 */
class PayrollDeduction_US_CT extends PayrollDeduction_US {
/*
														10 => 'Status "A"',
														20 => 'Status "B"',
														30 => 'Status "C"',
														40 => 'Status "D"',
														//50 => 'Status "E"', //Doesn't exist.
														60 => 'Status "F"',
*/

	var $state_options = array(
        						1388563200 => array( //01-Jan-13
													'allowance' => array(
																			'10' => array(
																							24000 =>  12000,
																							25000 =>  11000,
																							26000 =>  10000,
																							27000 =>  9000,
																							28000 =>  8000,
																							29000 =>  7000,
																							30000 =>  6000,
																							31000 =>  5000,
																							32000 =>  4000,
																							33000 =>  3000,
																							34000 =>  2000,
																							35000 =>  1000,
																							35000 =>  0,
																						),
																			'20' => array(
																							38000 =>  19000,
																							39000 =>  18000,
																							40000 =>  17000,
																							41000 =>  16000,
																							42000 =>  15000,
																							43000 =>  14000,
																							44000 =>  13000,
																							45000 =>  12000,
																							46000 =>  11000,
																							47000 =>  10000,
																							48000 =>  9000,
																							49000 =>  8000,
																							50000 =>  7000,
																							51000 =>  6000,
																							52000 =>  5000,
																							53000 =>  4000,
																							54000 =>  3000,
																							55000 =>  2000,
																							56000 =>  1000,
																							56000 =>  0,
																						),
																			'30' => array(
																							48000 =>  24000,
																							49000 =>  23000,
																							50000 =>  22000,
																							51000 =>  21000,
																							52000 =>  20000,
																							53000 =>  19000,
																							54000 =>  18000,
																							55000 =>  17000,
																							56000 =>  16000,
																							57000 =>  15000,
																							58000 =>  14000,
																							59000 =>  13000,
																							60000 =>  12000,
																							61000 =>  11000,
																							62000 =>  10000,
																							63000 =>  9000,
																							64000 =>  8000,
																							65000 =>  7000,
																							66000 =>  6000,
																							67000 =>  5000,
																							68000 =>  4000,
																							69000 =>  3000,
																							70000 =>  2000,
																							71000 =>  1000,
																							71000 =>  0,
																						),
																			'40' => 0,
																			'50' => 0,
																			'60' => array(
																							29000 =>  14500,
																							30000 =>  13500,
																							31000 =>  12500,
																							32000 =>  11500,
																							33000 =>  10500,
																							34000 =>  9500,
																							35000 =>  8500,
																							36000 =>  7500,
																							37000 =>  6500,
																							38000 =>  5500,
																							39000 =>  4500,
																							40000 =>  3500,
																							41000 =>  2500,
																							42000 =>  1500,
																							43000 =>  500,
																							43000 =>  0,
																						),
																		),
													'phase_out' => array(
                                                                            '10' => array(
                                                                                          'income_start' => 50250,
                                                                                          'income_stop' => 72750,
                                                                                          'income_step' => 2500,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 20,
                                                                                         ),
                                                                            '40' => array(
                                                                                          'income_start' => 50250,
                                                                                          'income_stop' => 72750,
                                                                                          'income_step' => 2500,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 20,
                                                                                         ),
                                                                            '20' => array(
                                                                                          'income_start' => 78500,
                                                                                          'income_stop' => 114500,
                                                                                          'income_step' => 4000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 32,
                                                                                         ),
                                                                            '30' => array(
                                                                                          'income_start' => 100500,
                                                                                          'income_stop' => 145500,
                                                                                          'income_step' => 5000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 40,
                                                                                         ),
                                                                            '60' => array(
                                                                                          'income_start' => 56500,
                                                                                          'income_stop' => 101500,
                                                                                          'income_step' => 5000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 20,
                                                                                         ),
																		),
													'tax_recapture' => array(
                                                                            '10' => array(
                                                                                          'income_start' => 200000,
                                                                                          'income_stop' => 345000,
                                                                                          'income_step' => 5000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 75,
                                                                                         ),
                                                                            '40' => array(
                                                                                          'income_start' => 200000,
                                                                                          'income_stop' => 345000,
                                                                                          'income_step' => 5000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 75,
                                                                                         ),
                                                                            '60' => array(
                                                                                          'income_start' => 200000,
                                                                                          'income_stop' => 345000,
                                                                                          'income_step' => 5000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 75,
                                                                                         ),
                                                                            '20' => array(
                                                                                          'income_start' => 320000,
                                                                                          'income_stop' => 552000,
                                                                                          'income_step' => 8000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 120,
                                                                                         ),
                                                                            '30' => array(
                                                                                          'income_start' => 400000,
                                                                                          'income_stop' => 690000,
                                                                                          'income_step' => 10000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 150,
                                                                                         ),
																		),
													'personal_credit' => array(
																			'10' => array(
																					12000 => 0,
																					15000 => .75,
																					15500 => .70,
																					16000 => .65,
																					16500 => .60,
																					17000 => .55,
																					17500 => .50,
																					18000 => .45,
																					18500 => .40,
																					20000 => .35,
																					20500 => .30,
																					21000 => .25,
																					21500 => .20,
																					25000 => .15,
																					25500 => .14,
																					26000 => .13,
																					26500 => .12,
																					27000 => .11,
																					48000 => .10,
																					48500 => .09,
																					49000 => .08,
																					49500 => .07,
																					50000 => .06,
																					50500 => .05,
																					51000 => .04,
																					51500 => .03,
																					52000 => .02,
																					52500 => .01,
																					52500 => 0,
																			),
																			'20' => array(
																					19000 => 0,
																					24000 => .75,
																					24500 => .70,
																					25000 => .65,
																					25500 => .60,
																					26000 => .55,
																					26500 => .50,
																					27000 => .45,
																					27500 => .40,
																					34000 => .35,
																					34500 => .30,
																					35000 => .25,
																					35500 => .20,
																					44000 => .15,
																					44500 => .14,
																					45000 => .13,
																					45500 => .12,
																					46000 => .11,
																					74000 => .10,
																					74500 => .09,
																					75000 => .08,
																					75500 => .07,
																					76000 => .06,
																					76500 => .05,
																					77000 => .04,
																					77500 => .03,
																					78000 => .02,
																					78500 => .01,
																					78500 => 0,
																						),
																			'30' => array(
																					24000 => 0,
																					30000 => .75,
																					30500 => .70,
																					31000 => .65,
																					31500 => .60,
																					32000 => .55,
																					32500 => .50,
																					33000 => .45,
																					33500 => .40,
																					40000 => .35,
																					40500 => .30,
																					41000 => .25,
																					41500 => .20,
																					50000 => .15,
																					50500 => .14,
																					51000 => .13,
																					51500 => .12,
																					52000 => .11,
																					96000 => .10,
																					96500 => .09,
																					97000 => .08,
																					97500 => .07,
																					98000 => .06,
																					98500 => .05,
																					99000 => .04,
																					99500 => .03,
																					100000 => .02,
																					100500 => .01,
																					100500 => 0,
																						),
																			'40' => 0,
																			'50' => 0,
																			'60' => array(
																					14500 => 0,
																					18100 => .75,
																					18600 => .70,
																					19100 => .65,
																					19600 => .60,
																					20100 => .55,
																					20600 => .50,
																					21100 => .45,
																					21600 => .40,
																					24200 => .35,
																					24700 => .30,
																					25200 => .25,
																					25700 => .20,
																					30200 => .15,
																					30700 => .14,
																					31200 => .13,
																					31700 => .12,
																					32200 => .11,
																					58000 => .10,
																					58500 => .09,
																					59000 => .08,
																					59500 => .07,
																					60000 => .06,
																					60500 => .05,
																					61000 => .04,
																					61500 => .03,
																					62000 => .02,
																					62500 => .01,
																					62600 => 0,
																						),
																		)
													),
        						1357027200 => array( //01-Jan-13
													'allowance' => array(
																			'10' => array(
																							24000 =>  12000,
																							25000 =>  11000,
																							26000 =>  10000,
																							27000 =>  9000,
																							28000 =>  8000,
																							29000 =>  7000,
																							30000 =>  6000,
																							31000 =>  5000,
																							32000 =>  4000,
																							33000 =>  3000,
																							34000 =>  2000,
																							35000 =>  1000,
																							35000 =>  0,
																						),
																			'20' => array(
																							38000 =>  19000,
																							39000 =>  18000,
																							40000 =>  17000,
																							41000 =>  16000,
																							42000 =>  15000,
																							43000 =>  14000,
																							44000 =>  13000,
																							45000 =>  12000,
																							46000 =>  11000,
																							47000 =>  10000,
																							48000 =>  9000,
																							49000 =>  8000,
																							50000 =>  7000,
																							51000 =>  6000,
																							52000 =>  5000,
																							53000 =>  4000,
																							54000 =>  3000,
																							55000 =>  2000,
																							56000 =>  1000,
																							56000 =>  0,
																						),
																			'30' => array(
																							48000 =>  24000,
																							49000 =>  23000,
																							50000 =>  22000,
																							51000 =>  21000,
																							52000 =>  20000,
																							53000 =>  19000,
																							54000 =>  18000,
																							55000 =>  17000,
																							56000 =>  16000,
																							57000 =>  15000,
																							58000 =>  14000,
																							59000 =>  13000,
																							60000 =>  12000,
																							61000 =>  11000,
																							62000 =>  10000,
																							63000 =>  9000,
																							64000 =>  8000,
																							65000 =>  7000,
																							66000 =>  6000,
																							67000 =>  5000,
																							68000 =>  4000,
																							69000 =>  3000,
																							70000 =>  2000,
																							71000 =>  1000,
																							71000 =>  0,
																						),
																			'40' => 0,
																			'50' => 0,
																			'60' => array(
																							28000 =>  14000,
																							29000 =>  13000,
																							30000 =>  12000,
																							31000 =>  11000,
																							32000 =>  10000,
																							33000 =>  9000,
																							34000 =>  8000,
																							35000 =>  7000,
																							36000 =>  6000,
																							37000 =>  5000,
																							38000 =>  4000,
																							39000 =>  3000,
																							40000 =>  2000,
																							41000 =>  1000,
																							41000 =>  0,
																						),
																		),
													'phase_out' => array(
                                                                            '10' => array(
                                                                                          'income_start' => 50250,
                                                                                          'income_stop' => 72750,
                                                                                          'income_step' => 2500,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 20,
                                                                                         ),
                                                                            '40' => array(
                                                                                          'income_start' => 50250,
                                                                                          'income_stop' => 72750,
                                                                                          'income_step' => 2500,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 20,
                                                                                         ),
                                                                            '20' => array(
                                                                                          'income_start' => 78500,
                                                                                          'income_stop' => 114500,
                                                                                          'income_step' => 4000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 32,
                                                                                         ),
                                                                            '30' => array(
                                                                                          'income_start' => 100500,
                                                                                          'income_stop' => 145500,
                                                                                          'income_step' => 5000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 40,
                                                                                         ),
                                                                            '60' => array(
                                                                                          'income_start' => 56500,
                                                                                          'income_stop' => 101500,
                                                                                          'income_step' => 5000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 20,
                                                                                         ),
																		),
													'tax_recapture' => array(
                                                                            '10' => array(
                                                                                          'income_start' => 200000,
                                                                                          'income_stop' => 345000,
                                                                                          'income_step' => 5000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 75,
                                                                                         ),
                                                                            '40' => array(
                                                                                          'income_start' => 200000,
                                                                                          'income_stop' => 345000,
                                                                                          'income_step' => 5000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 75,
                                                                                         ),
                                                                            '60' => array(
                                                                                          'income_start' => 200000,
                                                                                          'income_stop' => 345000,
                                                                                          'income_step' => 5000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 75,
                                                                                         ),
                                                                            '20' => array(
                                                                                          'income_start' => 320000,
                                                                                          'income_stop' => 552000,
                                                                                          'income_step' => 8000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 120,
                                                                                         ),
                                                                            '30' => array(
                                                                                          'income_start' => 400000,
                                                                                          'income_stop' => 690000,
                                                                                          'income_step' => 10000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 150,
                                                                                         ),
																		),
													'personal_credit' => array(
																			'10' => array(
																					12000 => 0,
																					15000 => .75,
																					15500 => .70,
																					16000 => .65,
																					16500 => .60,
																					17000 => .55,
																					17500 => .50,
																					18000 => .45,
																					18500 => .40,
																					20000 => .35,
																					20500 => .30,
																					21000 => .25,
																					21500 => .20,
																					25000 => .15,
																					25500 => .14,
																					26000 => .13,
																					26500 => .12,
																					27000 => .11,
																					48000 => .10,
																					48500 => .09,
																					49000 => .08,
																					49500 => .07,
																					50000 => .06,
																					50500 => .05,
																					51000 => .04,
																					51500 => .03,
																					52000 => .02,
																					52500 => .01,
																					52500 => 0,
																			),
																			'20' => array(
																					19000 => 0,
																					24000 => .75,
																					24500 => .70,
																					25000 => .65,
																					25500 => .60,
																					26000 => .55,
																					26500 => .50,
																					27000 => .45,
																					27500 => .40,
																					34000 => .35,
																					34500 => .30,
																					35000 => .25,
																					35500 => .20,
																					44000 => .15,
																					44500 => .14,
																					45000 => .13,
																					45500 => .12,
																					46000 => .11,
																					74000 => .10,
																					74500 => .09,
																					75000 => .08,
																					75500 => .07,
																					76000 => .06,
																					76500 => .05,
																					77000 => .04,
																					77500 => .03,
																					78000 => .02,
																					78500 => .01,
																					78500 => 0,
																						),
																			'30' => array(
																					24000 => 0,
																					30000 => .75,
																					30500 => .70,
																					31000 => .65,
																					31500 => .60,
																					32000 => .55,
																					32500 => .50,
																					33000 => .45,
																					33500 => .40,
																					40000 => .35,
																					40500 => .30,
																					41000 => .25,
																					41500 => .20,
																					50000 => .15,
																					50500 => .14,
																					51000 => .13,
																					51500 => .12,
																					52000 => .11,
																					96000 => .10,
																					96500 => .09,
																					97000 => .08,
																					97500 => .07,
																					98000 => .06,
																					98500 => .05,
																					99000 => .04,
																					99500 => .03,
																					100000 => .02,
																					100500 => .01,
																					100500 => 0,
																						),
																			'40' => 0,
																			'50' => 0,
																			'60' => array(
																					14000 => 0,
																					17500 => .75,
																					18000 => .70,
																					18500 => .65,
																					19000 => .60,
																					19500 => .55,
																					20000 => .50,
																					20500 => .45,
																					21000 => .40,
																					23300 => .35,
																					23800 => .30,
																					24300 => .25,
																					24800 => .20,
																					29200 => .15,
																					29700 => .14,
																					30200 => .13,
																					30700 => .12,
																					31200 => .11,
																					56000 => .10,
																					56500 => .09,
																					57000 => .08,
																					57500 => .07,
																					58000 => .06,
																					58500 => .05,
																					59000 => .04,
																					59500 => .03,
																					60000 => .02,
																					60500 => .01,
																					60500 => 0,
																						),
																		)
													),
								1325404800 => array( //01-Jan-12
													'allowance' => array(
																			'10' => array(
																							24000 =>  12000,
																							25000 =>  11000,
																							26000 =>  10000,
																							27000 =>  9000,
																							28000 =>  8000,
																							29000 =>  7000,
																							30000 =>  6000,
																							31000 =>  5000,
																							32000 =>  4000,
																							33000 =>  3000,
																							34000 =>  2000,
																							35000 =>  1000,
																							35000 =>  0,
																						),
																			'20' => array(
																							38000 =>  19000,
																							39000 =>  18000,
																							40000 =>  17000,
																							41000 =>  16000,
																							42000 =>  15000,
																							43000 =>  14000,
																							44000 =>  13000,
																							45000 =>  12000,
																							46000 =>  11000,
																							47000 =>  10000,
																							48000 =>  9000,
																							49000 =>  8000,
																							50000 =>  7000,
																							51000 =>  6000,
																							52000 =>  5000,
																							53000 =>  4000,
																							54000 =>  3000,
																							55000 =>  2000,
																							56000 =>  1000,
																							56000 =>  0,
																						),
																			'30' => array(
																							48000 =>  24000,
																							49000 =>  23000,
																							50000 =>  22000,
																							51000 =>  21000,
																							52000 =>  20000,
																							53000 =>  19000,
																							54000 =>  18000,
																							55000 =>  17000,
																							56000 =>  16000,
																							57000 =>  15000,
																							58000 =>  14000,
																							59000 =>  13000,
																							60000 =>  12000,
																							61000 =>  11000,
																							62000 =>  10000,
																							63000 =>  9000,
																							64000 =>  8000,
																							65000 =>  7000,
																							66000 =>  6000,
																							67000 =>  5000,
																							68000 =>  4000,
																							69000 =>  3000,
																							70000 =>  2000,
																							71000 =>  1000,
																							71000 =>  0,
																						),
																			'40' => 0,
																			'50' => 0,
																			'60' => array(
																							27000 =>  13500,
																							28000 =>  12500,
																							29000 =>  11500,
																							30000 =>  10500,
																							31000 =>  9500,
																							32000 =>  8500,
																							33000 =>  7500,
																							34000 =>  6500,
																							35000 =>  5500,
																							36000 =>  4500,
																							37000 =>  3500,
																							38000 =>  2500,
																							39000 =>  1500,
																							40000 =>  500,
																							40000 =>  0,
																						),
																		),
													'phase_out' => array(
                                                                            '10' => array(
                                                                                          'income_start' => 50250,
                                                                                          'income_stop' => 72750,
                                                                                          'income_step' => 2500,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 20,
                                                                                         ),
                                                                            '40' => array(
                                                                                          'income_start' => 50250,
                                                                                          'income_stop' => 72750,
                                                                                          'income_step' => 2500,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 20,
                                                                                         ),
                                                                            '20' => array(
                                                                                          'income_start' => 78500,
                                                                                          'income_stop' => 114500,
                                                                                          'income_step' => 4000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 32,
                                                                                         ),
                                                                            '30' => array(
                                                                                          'income_start' => 100500,
                                                                                          'income_stop' => 145500,
                                                                                          'income_step' => 5000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 40,
                                                                                         ),
                                                                            '60' => array(
                                                                                          'income_start' => 56500,
                                                                                          'income_stop' => 101500,
                                                                                          'income_step' => 5000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 20,
                                                                                         ),
																		),
													'tax_recapture' => array(
                                                                            '10' => array(
                                                                                          'income_start' => 200000,
                                                                                          'income_stop' => 345000,
                                                                                          'income_step' => 5000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 75,
                                                                                         ),
                                                                            '40' => array(
                                                                                          'income_start' => 200000,
                                                                                          'income_stop' => 345000,
                                                                                          'income_step' => 5000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 75,
                                                                                         ),
                                                                            '60' => array(
                                                                                          'income_start' => 200000,
                                                                                          'income_stop' => 345000,
                                                                                          'income_step' => 5000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 75,
                                                                                         ),
                                                                            '20' => array(
                                                                                          'income_start' => 320000,
                                                                                          'income_stop' => 552000,
                                                                                          'income_step' => 8000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 120,
                                                                                         ),
                                                                            '30' => array(
                                                                                          'income_start' => 400000,
                                                                                          'income_stop' => 690000,
                                                                                          'income_step' => 10000,
                                                                                          'result_start' => 0,
                                                                                          'result_step' => 150,
                                                                                         ),
																		),
													'personal_credit' => array(
																			'10' => array(
																					12000 => 0,
																					15000 => .75,
																					15500 => .70,
																					16000 => .65,
																					16500 => .60,
																					17000 => .55,
																					17500 => .50,
																					18000 => .45,
																					18500 => .40,
																					20000 => .35,
																					20500 => .30,
																					21000 => .25,
																					21500 => .20,
																					25000 => .15,
																					25500 => .14,
																					26000 => .13,
																					26500 => .12,
																					27000 => .11,
																					48000 => .10,
																					48500 => .09,
																					49000 => .08,
																					49500 => .07,
																					50000 => .06,
																					50500 => .05,
																					51000 => .04,
																					51500 => .03,
																					52000 => .02,
																					52500 => .01,
																					52500 => 0,
																			),
																			'20' => array(
																					19000 => 0,
																					24000 => .75,
																					24500 => .70,
																					25000 => .65,
																					25500 => .60,
																					26000 => .55,
																					26500 => .50,
																					27000 => .45,
																					27500 => .40,
																					34000 => .35,
																					34500 => .30,
																					35000 => .25,
																					35500 => .20,
																					44000 => .15,
																					44500 => .14,
																					45000 => .13,
																					45500 => .12,
																					46000 => .11,
																					74000 => .10,
																					74500 => .09,
																					75000 => .08,
																					75500 => .07,
																					76000 => .06,
																					76500 => .05,
																					77000 => .04,
																					77500 => .03,
																					78000 => .02,
																					78500 => .01,
																					78500 => 0,
																						),
																			'30' => array(
																					24000 => 0,
																					30000 => .75,
																					30500 => .70,
																					31000 => .65,
																					31500 => .60,
																					32000 => .55,
																					32500 => .50,
																					33000 => .45,
																					33500 => .40,
																					40000 => .35,
																					40500 => .30,
																					41000 => .25,
																					41500 => .20,
																					50000 => .15,
																					50500 => .14,
																					51000 => .13,
																					51500 => .12,
																					52000 => .11,
																					96000 => .10,
																					96500 => .09,
																					97000 => .08,
																					97500 => .07,
																					98000 => .06,
																					98500 => .05,
																					99000 => .04,
																					99500 => .03,
																					100000 => .02,
																					100500 => .01,
																					100500 => 0,
																						),
																			'40' => 0,
																			'50' => 0,
																			'60' => array(
																					13500 => 0,
																					16900 => .75,
																					17400 => .70,
																					17900 => .65,
																					18400 => .60,
																					18900 => .55,
																					19400 => .50,
																					19900 => .45,
																					20400 => .40,
																					22500 => .35,
																					23000 => .30,
																					23500 => .25,
																					24000 => .20,
																					28100 => .15,
																					28600 => .14,
																					29100 => .13,
																					29600 => .12,
																					30100 => .11,
																					54000 => .10,
																					54500 => .09,
																					55000 => .08,
																					55500 => .07,
																					56000 => .06,
																					56500 => .05,
																					57000 => .04,
																					57500 => .03,
																					58000 => .02,
																					58500 => .01,
																					58500 => 0,
																						),
																		)
													),
								1230796800 => array( //01-Jan-09
													'allowance' => array(
																			'10' => array(
																							24000 =>  12000,
																							25000 =>  11000,
																							26000 =>  10000,
																							27000 =>  9000,
																							28000 =>  8000,
																							29000 =>  7000,
																							30000 =>  6000,
																							31000 =>  5000,
																							32000 =>  4000,
																							33000 =>  3000,
																							34000 =>  2000,
																							35000 =>  1000,
																							35000 =>  0,
																						),
																			'20' => array(
																							38000 =>  19000,
																							39000 =>  18000,
																							40000 =>  17000,
																							41000 =>  16000,
																							42000 =>  15000,
																							43000 =>  14000,
																							44000 =>  13000,
																							45000 =>  12000,
																							46000 =>  11000,
																							47000 =>  10000,
																							48000 =>  9000,
																							49000 =>  8000,
																							50000 =>  7000,
																							51000 =>  6000,
																							52000 =>  5000,
																							53000 =>  4000,
																							54000 =>  3000,
																							55000 =>  2000,
																							56000 =>  1000,
																							56000 =>  0,
																						),
																			'30' => array(
																							48000 =>  24000,
																							49000 =>  23000,
																							50000 =>  22000,
																							51000 =>  21000,
																							52000 =>  20000,
																							53000 =>  19000,
																							54000 =>  18000,
																							55000 =>  17000,
																							56000 =>  16000,
																							57000 =>  15000,
																							58000 =>  14000,
																							59000 =>  13000,
																							60000 =>  12000,
																							61000 =>  11000,
																							62000 =>  10000,
																							63000 =>  9000,
																							64000 =>  8000,
																							65000 =>  7000,
																							66000 =>  6000,
																							67000 =>  5000,
																							68000 =>  4000,
																							69000 =>  3000,
																							70000 =>  2000,
																							71000 =>  1000,
																							71000 =>  0,
																						),
																			'40' => 0,
																			'50' => 0,
																			'60' => array(
																							27000 =>  13500,
																							28000 =>  12500,
																							29000 =>  11500,
																							30000 =>  10500,
																							31000 =>  9500,
																							32000 =>  8500,
																							33000 =>  7500,
																							34000 =>  6500,
																							35000 =>  5500,
																							36000 =>  4500,
																							37000 =>  3500,
																							38000 =>  2500,
																							39000 =>  1500,
																							40000 =>  500,
																							40000 =>  0,
																						),
																		),
													'personal_credit' => array(
																			'10' => array(
																					12000 => 0,
																					15000 => .75,
																					15500 => .70,
																					16000 => .65,
																					16500 => .60,
																					17000 => .55,
																					17500 => .50,
																					18000 => .45,
																					18500 => .40,
																					20000 => .35,
																					20500 => .30,
																					21000 => .25,
																					21500 => .20,
																					25000 => .15,
																					25500 => .14,
																					26000 => .13,
																					26500 => .12,
																					27000 => .11,
																					48000 => .10,
																					48500 => .09,
																					49000 => .08,
																					49500 => .07,
																					50000 => .06,
																					50500 => .05,
																					51000 => .04,
																					51500 => .03,
																					52000 => .02,
																					52500 => .01,
																					52500 => 0,

																						),
																			'20' => array(
																					19000 => 0,
																					24000 => .75,
																					24500 => .70,
																					25000 => .65,
																					25500 => .60,
																					26000 => .55,
																					26500 => .50,
																					27000 => .45,
																					27500 => .40,
																					34000 => .35,
																					34500 => .30,
																					35000 => .25,
																					35500 => .20,
																					44000 => .15,
																					44500 => .14,
																					45000 => .13,
																					45500 => .12,
																					46000 => .11,
																					74000 => .10,
																					74500 => .09,
																					75000 => .08,
																					75500 => .07,
																					76000 => .06,
																					76500 => .05,
																					77000 => .04,
																					77500 => .03,
																					78000 => .02,
																					78500 => .01,
																					78500 => 0,
																						),
																			'30' => array(
																					24000 => 0,
																					30000 => .75,
																					30500 => .70,
																					31000 => .65,
																					31500 => .60,
																					32000 => .55,
																					32500 => .50,
																					33000 => .45,
																					33500 => .40,
																					40000 => .35,
																					40500 => .30,
																					41000 => .25,
																					41500 => .20,
																					50000 => .15,
																					50500 => .14,
																					51000 => .13,
																					51500 => .12,
																					52000 => .11,
																					96000 => .10,
																					96500 => .09,
																					97000 => .08,
																					97500 => .07,
																					98000 => .06,
																					98500 => .05,
																					99000 => .04,
																					99500 => .03,
																					100000 => .02,
																					100500 => .01,
																					100500 => 0,
																						),
																			'40' => 0,
																			'50' => 0,
																			'60' => array(
																					13500 => 0,
																					16900 => .75,
																					17400 => .70,
																					17900 => .65,
																					18400 => .60,
																					18900 => .55,
																					19400 => .50,
																					19900 => .45,
																					20400 => .40,
																					22500 => .35,
																					23000 => .30,
																					23500 => .25,
																					24000 => .20,
																					28100 => .15,
																					28600 => .14,
																					29100 => .13,
																					29600 => .12,
																					30100 => .11,
																					54000 => .10,
																					54500 => .09,
																					55000 => .08,
																					55500 => .07,
																					56000 => .06,
																					56500 => .05,
																					57000 => .04,
																					57500 => .03,
																					58000 => .02,
																					58500 => .01,
																					58500 => 0,
																						),
																		)
													),
								1136102400 => array(
													'allowance' => array(
																			'10' => array(
																							24000 =>  12000,
																							25000 =>  11000,
																							26000 =>  10000,
																							27000 =>  9000,
																							28000 =>  8000,
																							29000 =>  7000,
																							30000 =>  6000,
																							31000 =>  5000,
																							32000 =>  4000,
																							33000 =>  3000,
																							34000 =>  2000,
																							35000 =>  1000,
																							35000 =>  0,
																						),
																			'20' => array(
																							38000 =>  19000,
																							39000 =>  18000,
																							40000 =>  17000,
																							41000 =>  16000,
																							42000 =>  15000,
																							43000 =>  14000,
																							44000 =>  13000,
																							45000 =>  12000,
																							46000 =>  11000,
																							47000 =>  10000,
																							48000 =>  9000,
																							49000 =>  8000,
																							50000 =>  7000,
																							51000 =>  6000,
																							52000 =>  5000,
																							53000 =>  4000,
																							54000 =>  3000,
																							55000 =>  2000,
																							56000 =>  1000,
																							56000 =>  0,
																						),
																			'30' => array(
																							48000 =>  24000,
																							49000 =>  23000,
																							50000 =>  22000,
																							51000 =>  21000,
																							52000 =>  20000,
																							53000 =>  19000,
																							54000 =>  18000,
																							55000 =>  17000,
																							56000 =>  16000,
																							57000 =>  15000,
																							58000 =>  14000,
																							59000 =>  13000,
																							60000 =>  12000,
																							61000 =>  11000,
																							62000 =>  10000,
																							63000 =>  9000,
																							64000 =>  8000,
																							65000 =>  7000,
																							66000 =>  6000,
																							67000 =>  5000,
																							68000 =>  4000,
																							69000 =>  3000,
																							70000 =>  2000,
																							71000 =>  1000,
																							71000 =>  0,
																						),
																			'40' => 0,
																			'50' => 0,
																			'60' => array(
																							25250 =>  12625,
																							26250 =>  11625,
																							27250 =>  10625,
																							28250 =>  9625,
																							29250 =>  8625,
																							30250 =>  7625,
																							31250 =>  6625,
																							32250 =>  5625,
																							33250 =>  4625,
																							34250 =>  3625,
																							35250 =>  2625,
																							36250 =>  1625,
																							37250 =>  625,
																							37250 =>  0,
																						),
																		),
													'personal_credit' => array(
																			'10' => array(
																					12000 => 0,
																					15000 => .75,
																					15500 => .70,
																					16000 => .65,
																					16500 => .60,
																					17000 => .55,
																					17500 => .50,
																					18000 => .45,
																					18500 => .40,
																					20000 => .35,
																					20500 => .30,
																					21000 => .25,
																					21500 => .20,
																					25000 => .15,
																					25500 => .14,
																					26000 => .13,
																					26500 => .12,
																					27000 => .11,
																					48000 => .10,
																					48500 => .09,
																					49000 => .08,
																					49500 => .07,
																					50000 => .06,
																					50500 => .05,
																					51000 => .04,
																					51500 => .03,
																					52000 => .02,
																					52500 => .01,
																					52500 => 0,

																						),
																			'20' => array(
																					19000 => 0,
																					24000 => .75,
																					24500 => .70,
																					25000 => .65,
																					25500 => .60,
																					26000 => .55,
																					26500 => .50,
																					27000 => .45,
																					27500 => .40,
																					34000 => .35,
																					34500 => .30,
																					35000 => .25,
																					35500 => .20,
																					44000 => .15,
																					44500 => .14,
																					45000 => .13,
																					45500 => .12,
																					46000 => .11,
																					74000 => .10,
																					74500 => .09,
																					75000 => .08,
																					75500 => .07,
																					76000 => .06,
																					76500 => .05,
																					77000 => .04,
																					77500 => .03,
																					78000 => .02,
																					78500 => .01,
																					78500 => 0,
																						),
																			'30' => array(
																					24000 => 0,
																					30000 => .75,
																					30500 => .70,
																					31000 => .65,
																					31500 => .60,
																					32000 => .55,
																					32500 => .50,
																					33000 => .45,
																					33500 => .40,
																					40000 => .35,
																					40500 => .30,
																					41000 => .25,
																					41500 => .20,
																					50000 => .15,
																					50500 => .14,
																					51000 => .13,
																					51500 => .12,
																					52000 => .11,
																					96000 => .10,
																					96500 => .09,
																					97000 => .08,
																					97500 => .07,
																					98000 => .06,
																					98500 => .05,
																					99000 => .04,
																					99500 => .03,
																					100000 => .02,
																					100500 => .01,
																					100500 => 0,
																						),
																			'40' => 0,
																			'50' => 0,
																			'60' => array(
																					12625 => 0,
																					15750 => .75,
																					16250 => .70,
																					16750 => .65,
																					17250 => .60,
																					17750 => .55,
																					18250 => .50,
																					18750 => .45,
																					19250 => .40,
																					21050 => .35,
																					21550 => .30,
																					22050 => .25,
																					22550 => .20,
																					26300 => .15,
																					26800 => .14,
																					27300 => .13,
																					27800 => .12,
																					28300 => .11,
																					50500 => .10,
																					51000 => .09,
																					51500 => .08,
																					52000 => .07,
																					52500 => .06,
																					53000 => .05,
																					53500 => .04,
																					54000 => .03,
																					54500 => .02,
																					55000 => .01,
																					55000 => 0,
																						),
																		)
													)
								);

	function getStatePayPeriodDeductions() {
		return bcdiv( $this->getStateTaxPayable(), $this->getAnnualPayPeriods() );
	}

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();

		$state_allowance = $this->getStateAllowanceAmount();

		$income = bcsub($annual_income, $state_allowance);

		Debug::text('State Annual Taxable Income: '. $income, __FILE__, __LINE__, __METHOD__, 10);

		return $income;
	}

	private function getStateRateArray($input_arr, $income) {
		if ( !is_array($input_arr) ) {
			return 0;
		}

		$total_rates = count($input_arr) - 1;
		$prev_bracket=0;
		$i=0;
		foreach( $input_arr as $bracket => $value ) {
			Debug::text('Bracket: '. $bracket .' Value: '.$value, __FILE__, __LINE__, __METHOD__, 10);

			if ($income > $prev_bracket AND $income <= $bracket) {
				Debug::text('Found Bracket: '. $bracket  .' Returning: '. $value, __FILE__, __LINE__, __METHOD__, 10);

				return $value;
			} elseif ($i == $total_rates) {
				Debug::text('Found Last Bracket: '. $bracket .' Returning: '. $value, __FILE__, __LINE__, __METHOD__, 10);
				return $value;
			}

			$prev_bracket = $bracket;
			$i++;
		}

		return FALSE;
	}

	private function getDynamicStateRateArray($input_arr, $income) {
		if ( !is_array($input_arr) ) {
			return 0;
		}

		$total_rates = ($input_arr['income_stop']-$input_arr['income_start'])/$input_arr['income_step'];
		$prev_bracket=0;
        $value=$input_arr['result_start'];
		$i=0;
        for( $bracket=$input_arr['income_start']; $bracket <= $input_arr['income_stop']; $bracket+=$input_arr['income_step']) {
			Debug::text('Bracket: '. $bracket .' Prev Bracket: '. $prev_bracket .' Result: '.$value, __FILE__, __LINE__, __METHOD__, 10);

			if ( $income > $prev_bracket AND $income <= $bracket ) {
				Debug::text('Found Bracket: '. $bracket  .' Returning: '. $value, __FILE__, __LINE__, __METHOD__, 10);

				return $value;
			} elseif ( $i == $total_rates ) {
				Debug::text('Found Last Bracket: '. $bracket .' Returning: '. $value, __FILE__, __LINE__, __METHOD__, 10);
                $value += $input_arr['result_step'];
				return $value;
			}

			$prev_bracket = $bracket;
            $value += $input_arr['result_step'];
			$i++;
        }

		return FALSE;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;
		}

		$annual_income = $this->getStateAnnualTaxableIncome();

		$rate = $this->getData()->getStateRate($annual_income);
		$state_constant = $this->getData()->getStateConstant($annual_income);
		$state_rate_income = $this->getData()->getStateRatePreviousIncome($annual_income);

		$deduction = bcadd( bcmul( bcsub( $annual_income, $state_rate_income ), $rate ), $state_constant );

        //Add in phase out and recapture amounts.
        $deduction = bcadd( bcadd( $deduction, $this->getStatePhaseOutAmount() ), $this->getStateTaxRecaptureAmount() );

		return $deduction;
	}

	function getStateAllowanceAmount() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;
		}

		$allowance_arr = $retarr['allowance'][$this->getStateFilingStatus()];
		if ( is_array($allowance_arr) ) {
			$retval = $this->getStateRateArray( $allowance_arr, $this->getAnnualTaxableIncome() );
		} else {
			$retval = 0;
		}

		Debug::text('State Allowance Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}


	function getStatePhaseOutAmount() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;
		}

        if ( isset($retarr['phase_out']) ) {
            $allowance_arr = $retarr['phase_out'][$this->getStateFilingStatus()];
            if ( is_array($allowance_arr) ) {
                $retval = $this->getDynamicStateRateArray( $allowance_arr, $this->getAnnualTaxableIncome() );
            } else {
                $retval = 0;
            }
        } else {
            $retval = 0;
        }

		Debug::text('State Phase-Out Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function getStateTaxRecaptureAmount() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;
		}

        if ( isset($retarr['tax_recapture']) ) {
            $allowance_arr = $retarr['tax_recapture'][$this->getStateFilingStatus()];
            if ( is_array($allowance_arr) ) {
                $retval = $this->getDynamicStateRateArray( $allowance_arr, $this->getAnnualTaxableIncome() );
            } else {
                $retval = 0;
            }
        } else {
            $retval = 0;
        }

		Debug::text('State Tax Recapture Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function getStateTaxCreditDecimal() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;
		}

		$allowance_arr = $retarr['personal_credit'][$this->getStateFilingStatus()];

		if ( is_array($allowance_arr) ) {
			$retval = $this->getStateRateArray( $allowance_arr, $this->getAnnualTaxableIncome() );
		} else {
			$retval = 0;
		}

		Debug::text('State Tax Credit Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function getStateTaxPayable() {
		$retval = bcmul( $this->getStateStandardDeduction(), bcsub( 1, $this->getStateTaxCreditDecimal() ) );

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text('State Annual Tax Payable: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}
}
?>