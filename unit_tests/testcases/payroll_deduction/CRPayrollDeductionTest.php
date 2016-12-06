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

require_once('PHPUnit/Framework/TestCase.php');

/**
 * @group CRPayrollDeductionTest
 */
class CRPayrollDeductionTest extends PHPUnit_Framework_TestCase {
	public $company_id = NULL;

	public function setUp() {
		Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10);

		require_once( Environment::getBasePath().'/classes/payroll_deduction/PayrollDeduction.class.php');

		$this->company_id = PRIMARY_COMPANY_ID;

		TTDate::setTimeZone('Etc/GMT+8'); //Force to non-DST timezone. 'PST' isnt actually valid.

		return TRUE;
	}

	public function tearDown() {
		Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10);
		return TRUE;
	}

	public function mf($amount) {
		return Misc::MoneyFormat($amount, FALSE);
	}

	//
	//
	//
	// 2007
	//
	//
	//

	function testCR_2007a_BiWeekly_Single_LowIncome() {
		Debug::text('CR - BiWeekly - Beginning of 2007 01-Jan-07: ', __FILE__, __LINE__, __METHOD__, 10);

		$pd_obj = new PayrollDeduction('CR', NULL);
		$pd_obj->setDate(strtotime('01-Jan-07'));
		$pd_obj->setAnnualPayPeriods( 26 ); //Bi-Weekly

		$pd_obj->setFederalFilingStatus( 10 ); //Single
		$pd_obj->setFederalAllowance( 1 );
		//$pd_obj->setUserCurrency('CRC');

		$pd_obj->setGrossPayPeriodIncome( 260000.00 );

		$this->assertEquals( $this->mf( $pd_obj->getGrossPayPeriodIncome() ), '260000.00' );
		$this->assertEquals( $this->mf( $pd_obj->getFederalPayPeriodDeductions() ), '3993.85' ); //100.73
	}
}
?>