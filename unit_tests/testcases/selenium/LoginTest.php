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
require_once( 'TTSeleniumGlobal.php' );
/**
 * @group UI
 */
class UILoginTest extends TTSeleniumGlobal  {
	public function setUp() {
		parent::setUp();

		return TRUE;
	}

	public function tearDown() {
		parent::tearDown();

		return TRUE;
	}

	function testUILoginLogout() {
		$this->Login();
		$this->Logout();
	}

//	function testEditUser() {
//		//TODO: Use input field names/ids rather then positions or xpath indexes.
//		$this->Login();
//
//		//Go to employee list
//		$this->getText('xpath=//div[@id=\'ribbon\']/ul/li[11]/a');
//		$this->click('link=Employee');
//		$this->click('css=#Employee > img');
//		$this->waitForAttribute( 'css=div.view@init_complete' );
//
//		//Add new employee.
//		$this->getText('xpath=//div[@id=\'ribbon\']/ul/li[11]/a');
//		$this->click('css=#addIcon > img');
//		$this->waitForAttribute( 'css=div.view@init_complete' );
//
//		//Enter employee information.
//		$this->type('xpath=id(\'tab0_content_div\')/div[1]/div[13]/div[2]/input', 'selenium.test');
//		$this->fireEvent('xpath=id(\'tab0_content_div\')/div[1]/div[13]/div[2]/input', 'keyup');
//		$this->waitForElementPresent('xpath=id(\'tab0_content_div\')/div[1]/div[13]/div[2]/input[contains(@class,\'error-tip\')]', FALSE );
//
//		$this->type('xpath=id(\'tab0_content_div\')/div[1]/div[15]/div[2]/input', 'demo');
//		$this->fireEvent('xpath=id(\'tab0_content_div\')/div[1]/div[15]/div[2]/input', 'keyup');
//		$this->waitForElementPresent('xpath=id(\'tab0_content_div\')/div[1]/div[15]/div[2]/input[contains(@class,\'error-tip\')]', FALSE );
//
//		$this->type('xpath=id(\'tab0_content_div\')/div[1]/div[17]/div[2]/input', 'demo');
//		$this->fireEvent('xpath=id(\'tab0_content_div\')/div[1]/div[17]/div[2]/input', 'keyup');
//		$this->waitForElementPresent('xpath=id(\'tab0_content_div\')/div[1]/div[17]/div[2]/input[contains(@class,\'error-tip\')]', FALSE );
//
//		$this->type('xpath=(//input[@type=\'text\'])[12]', 'selenium');
//		$this->fireEvent('xpath=(//input[@type=\'text\'])[12]', 'keyup');
//		$this->type('xpath=(//input[@type=\'text\'])[13]', 'test');
//		$this->fireEvent('xpath=(//input[@type=\'text\'])[13]', 'keyup');
//		$this->waitForElementPresent('xpath=(//input[@type=\'text\'])[13][contains(@class,\'error-tip\')]', FALSE );
//
//		$this->waitForElementPresent('xpath=id(\'EmployeeContextMenu\')/div/div/div[1]/ul/li[8][contains(@class,\'disable-image\')]', FALSE );
//		$this->waitForAttribute( 'css=div.edit-view@validate_complete' );
//
//		//Save employee
//		$this->click('xpath=id(\'EmployeeContextMenu\')/div/div/div[1]/ul/li[8]');
//		$this->waitForElementPresent('css=div.popup-loading' );
//		$this->waitForElementPresent('css=div.edit-view', FALSE );
//		$this->waitForAttribute( 'css=div.view@init_complete' );
//
//		//Search for newly created user
//		$this->click('link=BASIC SEARCH');
//		$this->waitForElementPresent('div.ui-tabs-hide', FALSE );
//		$this->type('css=input.t-text-input', 'selenium');
//		$this->click('id=searchBtn');
//		$this->waitForAttribute( 'css=div.search-panel@search_complete' );
//
//		//Select employee
//		$this->uncheck('xpath=//input[contains(@id,\'jqg_employee_view_container_\')]');
//		$this->click('xpath=//input[contains(@id,\'jqg_employee_view_container_\')]');
//		$this->waitForElementPresent('xpath=id(\'EmployeeContextMenu\')/div/div/div[1]/ul/li[5][contains(@class,\'disable-image\')]', FALSE );
//
//		//Delete employee
//		$this->click('id=deleteIcon');
//		$this->isElementPresent('css=div.confirm-alert');
//
//		//Confirm delete
//		$this->click('id=yesBtn');
//		$this->isElementPresent('css=div.no-result-div');
//
//
//		$this->Logout();
//	}
}
?>