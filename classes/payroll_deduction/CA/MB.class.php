<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2017 TimeTrex Software Inc.
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
class PayrollDeduction_CA_MB extends PayrollDeduction_CA {
	var $provincial_income_tax_rate_options = array(
													20170101 => array(
																	array( 'income' => 31465,	'rate' => 10.8,	'constant' => 0 ),
																	array( 'income' => 68005,	'rate' => 12.75,'constant' => 614 ),
																	array( 'income' => 68005,	'rate' => 17.4,	'constant' => 3776 ),
																),
													20090101 => array(
																	array( 'income' => 31000,	'rate' => 10.8,	'constant' => 0 ),
																	array( 'income' => 67000,	'rate' => 12.75,'constant' => 605 ),
																	array( 'income' => 67000,	'rate' => 17.4,	'constant' => 3720 ),
																),
													20080101 => array(
																	array( 'income' => 30544,	'rate' => 10.90,'constant' => 0 ),
																	array( 'income' => 66000,	'rate' => 12.75,'constant' => 565 ),
																	array( 'income' => 66000,	'rate' => 17.40,'constant' => 3634 ),
																),
													20070101 => array(
																	array( 'income' => 30544,	'rate' => 10.9,	'constant' => 0 ),
																	array( 'income' => 65000,	'rate' => 13.0,	'constant' => 641 ),
																	array( 'income' => 65000,	'rate' => 17.4,	'constant' => 3501 ),
																),
													20060101 => array(
																	array( 'income' => 30544,	'rate' => 10.9,	'constant' => 0 ),
																	array( 'income' => 65000,	'rate' => 13.5,	'constant' => 794 ),
																	array( 'income' => 65000,	'rate' => 17.4,	'constant' => 3329 ),
																),
													);
}
?>
