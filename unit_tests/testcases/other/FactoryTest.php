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
 * @group JobApplication
 */
class FactoryTest extends PHPUnit_Framework_TestCase {
	protected $company_id = NULL;
	protected $user_id = NULL;
	protected $currency_id = NULL;
	protected $branch_ids = NULL;
	protected $department_ids = NULL;
	protected $user_title_ids = NULL;
	protected $user_ids = NULL;

	public function setUp() {
		global $dd;
		Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10);

		$dd = new DemoData();
		$dd->setEnableQuickPunch( FALSE ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_'.uniqid( NULL, TRUE ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		$this->legal_entity_id = $dd->createLegalEntity( $this->company_id, 10 );
		Debug::text('Company ID: '. $this->company_id, __FILE__, __LINE__, __METHOD__, 10);

		//$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$this->currency_id = $dd->createCurrency( $this->company_id, 10 );

		$dd->createUserWageGroups( $this->company_id );

		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 100 );

		$this->branch_ids[] = $dd->createBranch( $this->company_id, 10 );
		$this->branch_ids[] = $dd->createBranch( $this->company_id, 20 );

		$this->department_ids[] = $dd->createDepartment( $this->company_id, 10 );
		$this->department_ids[] = $dd->createDepartment( $this->company_id, 20 );

		$this->user_title_ids[] = $dd->createUserTitle( $this->company_id, 10 );

		$this->user_ids[] = $dd->createUser( $this->company_id, $this->legal_entity_id, 10 );

		$this->assertGreaterThan( 0, $this->company_id );
		$this->assertGreaterThan( 0, $this->user_id );

		return TRUE;
	}

	public function tearDown() {
		Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	//Test to make sure the FactoryListIterator is properly clearing objects/sub-objects (ie: Validator) between loop iterations.
	function testFactoryListIteratorA() {
		//Create some test records.
		$utf = new UserTitleFactory();
		$utf->setCompany( $this->company_id );
		$utf->setName('Test0');
		if ( $utf->isValid() ) {
			$utf->Save();
		}

		$utf = new UserTitleFactory();
		$utf->setCompany( $this->company_id );
		$utf->setName('Test1');
		if ( $utf->isValid() ) {
			$utf->Save();
		}

		$utf = new UserTitleFactory();
		$utf->setCompany( $this->company_id );
		$utf->setName('Test2');
		if ( $utf->isValid() ) {
			$utf->Save();
		}

		$utf = new UserTitleFactory();
		$utf->setCompany( $this->company_id );
		$utf->setName('Test3');
		if ( $utf->isValid() ) {
			$utf->Save();
		}

		$utlf = new UserTitleListFactory();
		$utlf->getByCompanyId( $this->company_id );
		$this->assertGreaterThanOrEqual( 3, $utlf->getRecordCount() );
		if ( $utlf->getRecordCount() > 0 ) {
			$i = 0;
			foreach( $utlf as $ut_obj ) {
				if ( $i == 0 ) {
					$this->assertTrue( $ut_obj->isValid() );
					if ( $ut_obj->isValid() ) {
						$ut_obj->Save();
					}
				} elseif ( $i == 1 ) {
					$ut_obj->setName('');
					$this->assertFalse( $ut_obj->isValid() );
				} elseif ( $i == 2 ) {
					$this->assertTrue( $ut_obj->isValid() );
					if ( $ut_obj->isValid() ) {
						$ut_obj->Save();
					}
				} elseif ( $i == 3 ) {
					$ut_obj->setName('');
					$this->assertFalse( $ut_obj->isValid() );
				}

				$i++;
			}
		}

		return TRUE;
	}


}
?>
