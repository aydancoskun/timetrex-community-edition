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

class PermissionTest extends PHPUnit_Framework_TestCase {
	protected $company_id = NULL;
	protected $user_id = NULL;
	protected $pay_period_schedule_id = NULL;
	protected $pay_period_objs = NULL;
	protected $pay_stub_account_link_arr = NULL;

	public function setUp() {
		global $dd;
		Debug::text('Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10);

		TTDate::setTimeZone('PST8PDT', TRUE); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		$dd = new DemoData();
		$dd->setEnableQuickPunch( FALSE ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_'.uniqid( NULL, TRUE ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		Debug::text('Company ID: '. $this->company_id, __FILE__, __LINE__, __METHOD__, 10);
		$this->assertGreaterThan( 0, $this->company_id );

		$dd->createPermissionGroups( $this->company_id ); //Create all permissions.

		$dd->createCurrency( $this->company_id, 10 );

		$this->branch_id = $dd->createBranch( $this->company_id, 10 ); //NY

		$dd->createUserWageGroups( $this->company_id );

		$this->policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x
		$this->policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $this->policy_ids['pay_formula_policy'][100] ); //Regular

		$this->user_id = $dd->createUser( $this->company_id, 100 );

		$this->assertGreaterThan( 0, $this->company_id );
		$this->assertGreaterThan( 0, $this->user_id );

		return TRUE;
	}

	public function tearDown() {
		Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10);

		//$this->deleteAllSchedules();

		return TRUE;
	}

	function createPayPeriodSchedule( $shift_assigned_day = 10, $maximum_shift_time = 57600, $new_shift_trigger_time = 14400 ) {
		$ppsf = new PayPeriodScheduleFactory();

		$ppsf->setCompany( $this->company_id );
		//$ppsf->setName( 'Bi-Weekly'.rand(1000,9999) );
		$ppsf->setName( 'Bi-Weekly' );
		$ppsf->setDescription( 'Pay every two weeks' );
		$ppsf->setType( 20 );
		$ppsf->setStartWeekDay( 0 );


		$anchor_date = TTDate::getBeginWeekEpoch( ( TTDate::getBeginYearEpoch( time() ) - (86400 * (7 * 6) ) ) ); //Start 6 weeks ago

		$ppsf->setAnchorDate( $anchor_date );

		$ppsf->setStartDayOfWeek( TTDate::getDayOfWeek( $anchor_date ) );
		$ppsf->setTransactionDate( 7 );

		$ppsf->setTransactionDateBusinessDay( TRUE );
		$ppsf->setTimeZone('PST8PDT');

		$ppsf->setDayStartTime( 0 );
		$ppsf->setNewDayTriggerTime( $new_shift_trigger_time );
		$ppsf->setMaximumShiftTime( $maximum_shift_time );
		$ppsf->setShiftAssignedDay( $shift_assigned_day );

		$ppsf->setEnableInitialPayPeriods( FALSE );
		if ( $ppsf->isValid() ) {
			$insert_id = $ppsf->Save(FALSE);
			Debug::Text('Pay Period Schedule ID: '. $insert_id, __FILE__, __LINE__, __METHOD__, 10);

			$ppsf->setUser( array($this->user_id) );
			$ppsf->Save();

			$this->pay_period_schedule_id = $insert_id;

			return $insert_id;
		}

		Debug::Text('Failed Creating Pay Period Schedule!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;

	}

	function createPayPeriods( $initial_date = FALSE ) {
		$max_pay_periods = 35;

		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getById( $this->pay_period_schedule_id );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pps_obj = $ppslf->getCurrent();

			for ( $i = 0; $i < $max_pay_periods; $i++ ) {
				if ( $i == 0 ) {
					if ( $initial_date !== FALSE ) {
						$end_date = $initial_date;
					} else {
						//$end_date = TTDate::getBeginYearEpoch( strtotime('01-Jan-07') );
						$end_date = TTDate::getBeginWeekEpoch( ( TTDate::getBeginYearEpoch( time() ) - (86400 * (7 * 6) ) ) );
					}
				} else {
					$end_date = ( $end_date + ( (86400 * 14) ) );
				}

				Debug::Text('I: '. $i .' End Date: '. TTDate::getDate('DATE+TIME', $end_date), __FILE__, __LINE__, __METHOD__, 10);

				$pps_obj->createNextPayPeriod( $end_date, (86400 * 3600), FALSE ); //Don't import punches, as that causes deadlocks when running tests in parallel.
			}

		}

		return TRUE;
	}

	function getAllPayPeriods() {
		$pplf = new PayPeriodListFactory();
		//$pplf->getByCompanyId( $this->company_id );
		$pplf->getByPayPeriodScheduleId( $this->pay_period_schedule_id );
		if ( $pplf->getRecordCount() > 0 ) {
			foreach( $pplf as $pp_obj ) {
				Debug::text('Pay Period... Start: '. TTDate::getDate('DATE+TIME', $pp_obj->getStartDate() ) .' End: '. TTDate::getDate('DATE+TIME', $pp_obj->getEndDate() ), __FILE__, __LINE__, __METHOD__, 10);

				$this->pay_period_objs[] = $pp_obj;
			}
		}

		$this->pay_period_objs = array_reverse( $this->pay_period_objs );

		return TRUE;
	}

	function editUserPermission( $user_id, $section, $name, $value ) {
		$pclf = TTnew( 'PermissionControlListFactory' );
		$pclf->getByCompanyIdAndUserID( $this->company_id, $user_id );
		if ( $pclf->getRecordCount() > 0 ) {
			$pc_obj = $pclf->getCurrent();

			//Get current permissions
			$permission_arr = $pc_obj->getPermission();

			//Update permissions.
			$permission_arr[$section][$name] = (int)$value;
			$pc_obj->setPermission( $permission_arr );
			if ( $pc_obj->isValid() ) {
				$pc_obj->Save();
				Debug::Text('Success updating permissions...', __FILE__, __LINE__, __METHOD__, 10);
				return TRUE;
			}
		}

		Debug::Text('Failed updating permissions...', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	/*
	 Tests:
		Test basic permission functions.
		Test basic hierarchy permission functions.
		Test full blown reports that contain wages, and test all possible permutations of permissions in regards to wages.


		**** Don't run these in parallel as it seems to cause deadlocks/duplicate IDs on MySQL due to concurrency issues. ****
	*/

	/**
	 * @ORIGINAL: group Permission_testBasicPermissionFunctions
	 * @group Permission_test
	 */
	function testBasicPermissionFunctions() {
		global $dd;

		$permission = TTnew('Permission');
		$permission_arr = $permission->getPermissions( $this->user_id, $this->company_id );
		$this->assertGreaterThan( 40, count($permission_arr) ); //Needs to be low enough for community edtion.

		//Check bogus permission
		$retval = $permission->Check( 'foobarinvalid', 'view', $this->user_id, $this->company_id );
		$this->assertEquals( FALSE, $retval );

		//Check proper permission
		$retval = $permission->Check( 'user', 'view', $this->user_id, $this->company_id );
		$this->assertEquals( TRUE, $retval );

		$retval = $permission->Check( 'company', 'login_other_user', $this->user_id, $this->company_id );
		$this->assertEquals( FALSE, $retval );


		//Check permission levels
		$retval = $permission->getLevel( $this->user_id, $this->company_id );
		$this->assertEquals( 25, $retval );

		return TRUE;
	}

	/**
	 * @ORIGINAL: group Permission_testBasicHierarchyPermissionFunctionsA
	 * @group Permission_test
	 */
	function testBasicHierarchyPermissionFunctionsA() {
		global $dd;

		//Create Supervisor Subordinates Only

		$superior_user_id = $dd->createUser( $this->company_id, 10 );

		//Create Subordinates
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 20 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 21 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 23 );

		//Create non-subordinates.
		$dd->createUser( $this->company_id, 24 );
		$dd->createUser( $this->company_id, 25 );

		//Create authorization hierarchy
		$hierarchy_control_id = $dd->createAuthorizationHierarchyControl( $this->company_id, $subordinate_user_ids );

		//Admin user at the top
		$dd->createAuthorizationHierarchyLevel( $this->company_id, $hierarchy_control_id, $superior_user_id, 1 );

		$permission = TTnew('Permission');
		$permission_arr = $permission->getPermissions( $superior_user_id, $this->company_id );
		$this->assertGreaterThan( 20, count($permission_arr) ); //Needs to be low enough for community edition.

		$permission_children_ids = $permission->getPermissionHierarchyChildren( $this->company_id, $superior_user_id );
		//Debug::Arr( array($subordinate_user_ids, $permission_children_ids), 'aPermission Child Arrays: ', __FILE__, __LINE__, __METHOD__, 10);
		$this->assertEquals( $subordinate_user_ids, $permission_children_ids );

		$this->assertSame( FALSE, $permission->isPermissionChild( TRUE, $permission_children_ids ) );
		$this->assertSame( FALSE, $permission->isPermissionChild( FALSE, $permission_children_ids ) );
		$this->assertSame( FALSE, $permission->isPermissionChild( NULL, $permission_children_ids ) );
		$this->assertSame( FALSE, $permission->isPermissionChild( '', $permission_children_ids ) );
		$this->assertSame( FALSE, $permission->isPermissionChild( 0, $permission_children_ids ) );

		$this->assertSame( FALSE, $permission->isPermissionChild( TRUE, array() ) );
		$this->assertSame( FALSE, $permission->isPermissionChild( FALSE, array() ) );
		$this->assertSame( FALSE, $permission->isPermissionChild( NULL, array() ) );
		$this->assertSame( FALSE, $permission->isPermissionChild( '', array() ) );
		$this->assertSame( FALSE, $permission->isPermissionChild( 0, array() ) );

		$this->assertSame( TRUE, $permission->isPermissionChild( TRUE, NULL ) ); //NULL is used for view_all permissions, so it should be TRUE.
		$this->assertSame( TRUE, $permission->isPermissionChild( FALSE, NULL ) );
		$this->assertSame( TRUE, $permission->isPermissionChild( NULL, NULL ) );
		$this->assertSame( TRUE, $permission->isPermissionChild( '', NULL ) );
		$this->assertSame( TRUE, $permission->isPermissionChild( 0, NULL ) );
		$this->assertSame( TRUE, $permission->isPermissionChild( 99999, NULL ) );

		$this->assertSame( FALSE, $permission->isPermissionChild( 99999, $permission_children_ids ) );
		$this->assertSame( TRUE, $permission->isPermissionChild( $subordinate_user_ids[0], $permission_children_ids ) );

		//Since view_own is enabled, it should add the superior user_id to the array.
		$permission_children_ids = $permission->getPermissionChildren( 'user', 'view', $superior_user_id, $this->company_id );
		//Debug::Arr( array($superior_user_id, $this->company_id, $subordinate_user_ids, $permission_children_ids), 'bPermission Child Arrays: User ID: '. $superior_user_id, __FILE__, __LINE__, __METHOD__, 10);
		$this->assertSame( array_merge( $subordinate_user_ids, (array)$superior_user_id ), $permission_children_ids );

		//Check wage permissions, as no wage permissions should be enabled, no children should be returned.
		$permission_children_ids = $permission->getPermissionChildren( 'wage', 'view', $superior_user_id, $this->company_id );
		//Debug::Arr( array($superior_user_id, $this->company_id, $subordinate_user_ids, $permission_children_ids), 'cPermission Child Arrays: User ID: '. $superior_user_id, __FILE__, __LINE__, __METHOD__, 10);
		$this->assertSame( array(), $permission_children_ids );
		$this->assertSame( FALSE, $permission->isPermissionChild( $subordinate_user_ids[0], $permission_children_ids ) );
		$this->assertSame( FALSE, $permission->isPermissionChild( $superior_user_id, $permission_children_ids ) );
		$this->assertSame( FALSE, $permission->isPermissionChild( 99999, $permission_children_ids ) );

		return TRUE;
	}

	/**
	 * @ORIGINAL: group Permission_testBasicHierarchyPermissionFunctionsB
	 * @group Permission_test
	 */
	function testBasicHierarchyPermissionFunctionsB() {
		global $dd;

		//Create Supervisor Subordinates Only
		$superior_user_id = $dd->createUser( $this->company_id, 10 );

		//Create Subordinates
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 20 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 21 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 23 );

		//Create non-subordinates.
		$dd->createUser( $this->company_id, 24 );
		$dd->createUser( $this->company_id, 25 );

		//Create authorization hierarchy
		$hierarchy_control_id = $dd->createAuthorizationHierarchyControl( $this->company_id, $subordinate_user_ids );

		//Admin user at the top
		$dd->createAuthorizationHierarchyLevel( $this->company_id, $hierarchy_control_id, $superior_user_id, 1 );

		//
		//Add wage, view_own permissions and re-check
		//
		$this->editUserPermission( $superior_user_id, 'wage', 'view_own', TRUE );

		$permission = TTnew('Permission'); //This clears cache
		$permission_children_ids = $permission->getPermissionChildren( 'wage', 'view', $superior_user_id, $this->company_id );
		//Debug::Arr( array($superior_user_id, $this->company_id, $subordinate_user_ids, $permission_children_ids), 'dPermission Child Arrays: User ID: '. $superior_user_id, __FILE__, __LINE__, __METHOD__, 10);
		$this->assertSame(  array( $superior_user_id ), $permission_children_ids );
		$this->assertSame( FALSE, $permission->isPermissionChild( $subordinate_user_ids[0], $permission_children_ids ) );
		$this->assertSame( TRUE, $permission->isPermissionChild( $superior_user_id, $permission_children_ids ) );
		$this->assertSame( FALSE, $permission->isPermissionChild( 99999, $permission_children_ids ) );

		return TRUE;
	}

	/**
	 * @ORIGINAL: group Permission_testBasicHierarchyPermissionFunctionsC
	 * @group Permission_test
	 */
	function testBasicHierarchyPermissionFunctionsC() {
		global $dd;

		//Create Supervisor Subordinates Only
		$superior_user_id = $dd->createUser( $this->company_id, 10 );

		//Create Subordinates
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 20 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 21 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 23 );

		//Create non-subordinates.
		$dd->createUser( $this->company_id, 24 );
		$dd->createUser( $this->company_id, 25 );

		//Create authorization hierarchy
		$hierarchy_control_id = $dd->createAuthorizationHierarchyControl( $this->company_id, $subordinate_user_ids );

		//Admin user at the top
		$dd->createAuthorizationHierarchyLevel( $this->company_id, $hierarchy_control_id, $superior_user_id, 1 );

		//
		//Add wage, view_child permissions and re-check
		//
		$this->editUserPermission( $superior_user_id, 'wage', 'view_child', TRUE );

		$permission = TTnew('Permission'); //This clears cache
		$permission_children_ids = $permission->getPermissionChildren( 'wage', 'view', $superior_user_id, $this->company_id );
		//Debug::Arr( array($superior_user_id, $this->company_id, $subordinate_user_ids, $permission_children_ids), 'ePermission Child Arrays: User ID: '. $superior_user_id, __FILE__, __LINE__, __METHOD__, 10);
		$this->assertSame( $subordinate_user_ids, $permission_children_ids );
		$this->assertSame( TRUE, $permission->isPermissionChild( $subordinate_user_ids[0], $permission_children_ids ) );
		$this->assertSame( FALSE, $permission->isPermissionChild( $superior_user_id, $permission_children_ids ) );
		$this->assertSame( FALSE, $permission->isPermissionChild( 99999, $permission_children_ids ) );


		return TRUE;
	}

	/**
	 * @ORIGINAL: group Permission_testBasicHierarchyPermissionFunctionsD
	 * @group Permission_test
	 */
	function testBasicHierarchyPermissionFunctionsD() {
		global $dd;

		//Create Supervisor Subordinates Only
		$superior_user_id = $dd->createUser( $this->company_id, 10 );

		//Create Subordinates
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 20 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 21 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 23 );

		//Create non-subordinates.
		$dd->createUser( $this->company_id, 24 );
		$dd->createUser( $this->company_id, 25 );

		//Create authorization hierarchy
		$hierarchy_control_id = $dd->createAuthorizationHierarchyControl( $this->company_id, $subordinate_user_ids );

		//Admin user at the top
		$dd->createAuthorizationHierarchyLevel( $this->company_id, $hierarchy_control_id, $superior_user_id, 1 );

		//
		//Add wage, view_own AND view_child permissions and re-check
		//
		$this->editUserPermission( $superior_user_id, 'wage', 'view_own', TRUE );
		$this->editUserPermission( $superior_user_id, 'wage', 'view_child', TRUE );

		$permission = TTnew('Permission'); //This clears cache
		$permission_children_ids = $permission->getPermissionChildren( 'wage', 'view', $superior_user_id, $this->company_id );
		//Debug::Arr( array($superior_user_id, $this->company_id, $subordinate_user_ids, $permission_children_ids), 'fPermission Child Arrays: User ID: '. $superior_user_id, __FILE__, __LINE__, __METHOD__, 10);
		$this->assertSame( array_merge( $subordinate_user_ids, (array)$superior_user_id ), $permission_children_ids );
		$this->assertSame( TRUE, $permission->isPermissionChild( $subordinate_user_ids[0], $permission_children_ids ) );
		$this->assertSame( TRUE, $permission->isPermissionChild( $superior_user_id, $permission_children_ids ) );
		$this->assertSame( FALSE, $permission->isPermissionChild( 99999, $permission_children_ids ) );

		return TRUE;
	}

	/**
	 * @ORIGINAL: group Permission_testBasicHierarchyPermissionFunctionsE
	 * @group Permission_test
	 */
	function testBasicHierarchyPermissionFunctionsE() {
		global $dd;

		//Create Supervisor Subordinates Only

		$superior_user_id = $dd->createUser( $this->company_id, 10 );

		//Create Subordinates
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 20 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 21 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 23 );

		//Create non-subordinates.
		$dd->createUser( $this->company_id, 24 );
		$dd->createUser( $this->company_id, 25 );

		//Create authorization hierarchy
		$hierarchy_control_id = $dd->createAuthorizationHierarchyControl( $this->company_id, $subordinate_user_ids );

		//Admin user at the top
		$dd->createAuthorizationHierarchyLevel( $this->company_id, $hierarchy_control_id, $superior_user_id, 1 );

		//
		//Add wage, view permissions and re-check
		//
		$this->editUserPermission( $superior_user_id, 'wage', 'view', TRUE );

		$permission = TTnew('Permission'); //This clears cache
		$permission_children_ids = $permission->getPermissionChildren( 'wage', 'view', $superior_user_id, $this->company_id );
		//Debug::Arr( array($superior_user_id, $this->company_id, $subordinate_user_ids, $permission_children_ids), 'gPermission Child Arrays: User ID: '. $superior_user_id, __FILE__, __LINE__, __METHOD__, 10);
		$this->assertSame( NULL, $permission_children_ids );
		$this->assertSame( TRUE, $permission->isPermissionChild( $subordinate_user_ids[0], $permission_children_ids ) );
		$this->assertSame( TRUE, $permission->isPermissionChild( $superior_user_id, $permission_children_ids ) );
		$this->assertSame( TRUE, $permission->isPermissionChild( 99999, $permission_children_ids ) );

		return TRUE;
	}

	/**
	 * @ORIGINAL: group Permission_testBasicHierarchyPermissionFunctionsF
	 * @group Permission_test
	 */
	function testBasicHierarchyPermissionFunctionsF() {
		global $dd;

		//Create Supervisor Subordinates Only
		$superior_user_id = $dd->createUser( $this->company_id, 10 );

		//Create Subordinates
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 20 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 21 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 23 );

		//Create non-subordinates.
		$dd->createUser( $this->company_id, 24 );
		$dd->createUser( $this->company_id, 25 );

		//Create authorization hierarchy
		$hierarchy_control_id = $dd->createAuthorizationHierarchyControl( $this->company_id, $subordinate_user_ids );

		//Admin user at the top
		$dd->createAuthorizationHierarchyLevel( $this->company_id, $hierarchy_control_id, $superior_user_id, 1 );

		//
		//Add wage, view AND view_own AND view_child permissions and re-check
		//
		$this->editUserPermission( $superior_user_id, 'wage', 'view', TRUE );
		$this->editUserPermission( $superior_user_id, 'wage', 'view_own', TRUE );
		$this->editUserPermission( $superior_user_id, 'wage', 'view_child', TRUE );

		$permission = TTnew('Permission'); //This clears cache
		$permission_children_ids = $permission->getPermissionChildren( 'wage', 'view', $superior_user_id, $this->company_id );
		//Debug::Arr( array($superior_user_id, $this->company_id, $subordinate_user_ids, $permission_children_ids), 'hPermission Child Arrays: User ID: '. $superior_user_id, __FILE__, __LINE__, __METHOD__, 10);
		$this->assertSame( NULL, $permission_children_ids );
		$this->assertSame( TRUE, $permission->isPermissionChild( $subordinate_user_ids[0], $permission_children_ids ) );
		$this->assertSame( TRUE, $permission->isPermissionChild( $superior_user_id, $permission_children_ids ) );
		$this->assertSame( TRUE, $permission->isPermissionChild( 99999, $permission_children_ids ) );

		return TRUE;
	}

	/**
	 * @ORIGINAL: group Permission_testUserSummaryReportPermissionsA
	 * @group Permission_test
	 */
	function testUserSummaryReportPermissionsA() {
		global $dd;

		//Create Supervisor Subordinates Only
		$superior_user_id = $dd->createUser( $this->company_id, 10 );

		//Create Subordinates
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 20 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 21 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 23 );

		//Create non-subordinates.
		$dd->createUser( $this->company_id, 24 );
		$dd->createUser( $this->company_id, 25 );

		//Create authorization hierarchy
		$hierarchy_control_id = $dd->createAuthorizationHierarchyControl( $this->company_id, $subordinate_user_ids );

		//Admin user at the top
		$dd->createAuthorizationHierarchyLevel( $this->company_id, $hierarchy_control_id, $superior_user_id, 1 );


		$this->editUserPermission( $superior_user_id, 'user', 'view', TRUE ); //View all employees, but not all wages.
		//$this->editUserPermission( $superior_user_id, 'wage', 'view', TRUE );
		//$this->editUserPermission( $superior_user_id, 'wage', 'view_own', TRUE );
		//$this->editUserPermission( $superior_user_id, 'wage', 'view_child', TRUE );
		//$permission = TTnew('Permission'); //This clears cache

		$ulf = TTnew('UserListFactory');
		$user_obj = $ulf->getById( $superior_user_id )->getCurrent();

		//Global current_user/current_company as this is required to properly check permissions in each report
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$config['other']['disable_grand_total'] = TRUE;
		$config['columns'][] = 'employee_number';
		$config['columns'][] = 'first_name';
		$config['columns'][] = 'last_name';
		$config['columns'][] = 'hourly_rate';
		$config['sort'][] = array( 'employee_number' => 'asc' ); //Force sort, so it doesn't change on us.

		$report_obj = TTnew('UserSummaryReport');
		$report_obj->setUserObject( $user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setConfig( (array)$config );
		$output_data = $report_obj->getOutput( 'raw' );

		$this->assertEquals( 7, count($output_data) );
		$this->assertArrayHasKey('employee_number', $output_data[0] );
		$this->assertArrayNotHasKey('hourly_rate', $output_data[0] );
		$this->assertArrayNotHasKey('hourly_rate', $output_data[1] );
		$this->assertArrayNotHasKey('hourly_rate', $output_data[2] );
		$this->assertArrayNotHasKey('hourly_rate', $output_data[3] );
		$this->assertArrayNotHasKey('hourly_rate', $output_data[4] );
		$this->assertArrayNotHasKey('hourly_rate', $output_data[5] );
		$this->assertArrayNotHasKey('hourly_rate', $output_data[6] );

		return TRUE;
	}

	/**
	 * @ORIGINAL: group Permission_testUserSummaryReportPermissionsB
	 * @group Permission_test
	 */
	function testUserSummaryReportPermissionsB() {
		global $dd;

		//Create Supervisor Subordinates Only
		$superior_user_id = $dd->createUser( $this->company_id, 10 );

		//Create Subordinates
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 20 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 21 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 23 );

		//Create non-subordinates.
		$dd->createUser( $this->company_id, 24 );
		$dd->createUser( $this->company_id, 25 );

		//Create authorization hierarchy
		$hierarchy_control_id = $dd->createAuthorizationHierarchyControl( $this->company_id, $subordinate_user_ids );

		//Admin user at the top
		$dd->createAuthorizationHierarchyLevel( $this->company_id, $hierarchy_control_id, $superior_user_id, 1 );

		$this->editUserPermission( $superior_user_id, 'user', 'view', TRUE ); //View all employees, but not all wages.
		//$this->editUserPermission( $superior_user_id, 'wage', 'view', TRUE );
		$this->editUserPermission( $superior_user_id, 'wage', 'view_own', TRUE );
		//$this->editUserPermission( $superior_user_id, 'wage', 'view_child', TRUE );
		//$permission = TTnew('Permission'); //This clears cache

		$ulf = TTnew('UserListFactory');
		$user_obj = $ulf->getById( $superior_user_id )->getCurrent();

		//Global current_user/current_company as this is required to properly check permissions in each report
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$config['other']['disable_grand_total'] = TRUE;
		$config['columns'][] = 'employee_number';
		$config['columns'][] = 'first_name';
		$config['columns'][] = 'last_name';
		$config['columns'][] = 'hourly_rate';
		$config['sort'][] = array( 'employee_number' => 'asc' ); //Force sort, so it doesn't change on us.

		$report_obj = TTnew('UserSummaryReport');
		$report_obj->setUserObject( $user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setConfig( (array)$config );
		$output_data = $report_obj->getOutput( 'raw' );

		$this->assertEquals( 7, count($output_data) );
		$this->assertArrayHasKey('employee_number', $output_data[0] );
		$this->assertArrayHasKey('hourly_rate', $output_data[0] );
		$this->assertEquals( 21.50, $output_data[0]['hourly_rate'] );
		$this->assertArrayNotHasKey('hourly_rate', $output_data[1] );
		$this->assertArrayNotHasKey('hourly_rate', $output_data[2] );
		$this->assertArrayNotHasKey('hourly_rate', $output_data[3] );
		$this->assertArrayNotHasKey('hourly_rate', $output_data[4] );
		$this->assertArrayNotHasKey('hourly_rate', $output_data[5] );
		$this->assertArrayNotHasKey('hourly_rate', $output_data[6] );

		return TRUE;
	}

	/**
	 * @ORIGINAL: group Permission_testUserSummaryReportPermissionsC
	 * @group Permission_test
	 */
	function testUserSummaryReportPermissionsC() {
		global $dd;

		//Create Supervisor Subordinates Only
		$superior_user_id = $dd->createUser( $this->company_id, 10 );

		//Create Subordinates
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 20 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 21 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 23 );

		//Create non-subordinates.
		$dd->createUser( $this->company_id, 24 );
		$dd->createUser( $this->company_id, 25 );

		//Create authorization hierarchy
		$hierarchy_control_id = $dd->createAuthorizationHierarchyControl( $this->company_id, $subordinate_user_ids );

		//Admin user at the top
		$dd->createAuthorizationHierarchyLevel( $this->company_id, $hierarchy_control_id, $superior_user_id, 1 );

		$this->editUserPermission( $superior_user_id, 'user', 'view', TRUE ); //View all employees, but not all wages.
		//$this->editUserPermission( $superior_user_id, 'wage', 'view', TRUE );
		//$this->editUserPermission( $superior_user_id, 'wage', 'view_own', TRUE );
		$this->editUserPermission( $superior_user_id, 'wage', 'view_child', TRUE );
		//$permission = TTnew('Permission'); //This clears cache

		$ulf = TTnew('UserListFactory');
		$user_obj = $ulf->getById( $superior_user_id )->getCurrent();

		//Global current_user/current_company as this is required to properly check permissions in each report
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$config['other']['disable_grand_total'] = TRUE;
		$config['columns'][] = 'employee_number';
		$config['columns'][] = 'first_name';
		$config['columns'][] = 'last_name';
		$config['columns'][] = 'hourly_rate';
		$config['sort'][] = array( 'employee_number' => 'asc' ); //Force sort, so it doesn't change on us.

		$report_obj = TTnew('UserSummaryReport');
		$report_obj->setUserObject( $user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setConfig( (array)$config );
		$output_data = $report_obj->getOutput( 'raw' );

		$this->assertEquals( 7, count($output_data) );
		$this->assertArrayHasKey('employee_number', $output_data[0] );
		$this->assertArrayNotHasKey('hourly_rate', $output_data[0] );

		$this->assertArrayHasKey('hourly_rate', $output_data[1] );
		$this->assertEquals( 21.50, $output_data[1]['hourly_rate'] );
		$this->assertArrayHasKey('hourly_rate', $output_data[2] );
		$this->assertEquals( 21.50, $output_data[2]['hourly_rate'] );
		$this->assertArrayHasKey('hourly_rate', $output_data[3] );
		$this->assertEquals( 21.50, $output_data[3]['hourly_rate'] );

		$this->assertArrayNotHasKey('hourly_rate', $output_data[4] );
		$this->assertArrayNotHasKey('hourly_rate', $output_data[5] );
		$this->assertArrayNotHasKey('hourly_rate', $output_data[6] );


		return TRUE;
	}

	/**
	 * @ORIGINAL: group Permission_testUserSummaryReportPermissionsD
	 * @group Permission_test
	 */
	function testUserSummaryReportPermissionsD() {
		global $dd;

		//Create Supervisor Subordinates Only
		$superior_user_id = $dd->createUser( $this->company_id, 10 );

		//Create Subordinates
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 20 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 21 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 23 );

		//Create non-subordinates.
		$dd->createUser( $this->company_id, 24 );
		$dd->createUser( $this->company_id, 25 );

		//Create authorization hierarchy
		$hierarchy_control_id = $dd->createAuthorizationHierarchyControl( $this->company_id, $subordinate_user_ids );

		//Admin user at the top
		$dd->createAuthorizationHierarchyLevel( $this->company_id, $hierarchy_control_id, $superior_user_id, 1 );

		$this->editUserPermission( $superior_user_id, 'user', 'view', TRUE ); //View all employees, but not all wages.
		//$this->editUserPermission( $superior_user_id, 'wage', 'view', TRUE );
		$this->editUserPermission( $superior_user_id, 'wage', 'view_own', TRUE );
		$this->editUserPermission( $superior_user_id, 'wage', 'view_child', TRUE );
		//$permission = TTnew('Permission'); //This clears cache

		$ulf = TTnew('UserListFactory');
		$user_obj = $ulf->getById( $superior_user_id )->getCurrent();

		//Global current_user/current_company as this is required to properly check permissions in each report
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$config['other']['disable_grand_total'] = TRUE;
		$config['columns'][] = 'employee_number';
		$config['columns'][] = 'first_name';
		$config['columns'][] = 'last_name';
		$config['columns'][] = 'hourly_rate';
		$config['sort'][] = array( 'employee_number' => 'asc' ); //Force sort, so it doesn't change on us.

		$report_obj = TTnew('UserSummaryReport');
		$report_obj->setUserObject( $user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setConfig( (array)$config );
		$output_data = $report_obj->getOutput( 'raw' );

		$this->assertEquals( 7, count($output_data) );
		$this->assertArrayHasKey('employee_number', $output_data[0] );
		$this->assertArrayHasKey('hourly_rate', $output_data[0] );
		$this->assertEquals( 21.50, $output_data[0]['hourly_rate'] );
		$this->assertArrayHasKey('hourly_rate', $output_data[1] );
		$this->assertEquals( 21.50, $output_data[1]['hourly_rate'] );
		$this->assertArrayHasKey('hourly_rate', $output_data[2] );
		$this->assertEquals( 21.50, $output_data[2]['hourly_rate'] );
		$this->assertArrayHasKey('hourly_rate', $output_data[3] );
		$this->assertEquals( 21.50, $output_data[3]['hourly_rate'] );

		$this->assertArrayNotHasKey('hourly_rate', $output_data[4] );
		$this->assertArrayNotHasKey('hourly_rate', $output_data[5] );
		$this->assertArrayNotHasKey('hourly_rate', $output_data[6] );

		return TRUE;
	}

	/**
	 * @ORIGINAL: group Permission_testUserSummaryReportPermissionsE
	 * @group Permission_test
	 */
	function testUserSummaryReportPermissionsE() {
		global $dd;

		//Create Supervisor Subordinates Only
		$superior_user_id = $dd->createUser( $this->company_id, 10 );

		//Create Subordinates
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 20 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 21 );
		$subordinate_user_ids[] = $dd->createUser( $this->company_id, 23 );

		//Create non-subordinates.
		$dd->createUser( $this->company_id, 24 );
		$dd->createUser( $this->company_id, 25 );

		//Create authorization hierarchy
		$hierarchy_control_id = $dd->createAuthorizationHierarchyControl( $this->company_id, $subordinate_user_ids );

		//Admin user at the top
		$dd->createAuthorizationHierarchyLevel( $this->company_id, $hierarchy_control_id, $superior_user_id, 1 );

		$this->editUserPermission( $superior_user_id, 'user', 'view', TRUE ); //View all employees, but not all wages.
		$this->editUserPermission( $superior_user_id, 'wage', 'view', TRUE );
		$this->editUserPermission( $superior_user_id, 'wage', 'view_own', TRUE );
		$this->editUserPermission( $superior_user_id, 'wage', 'view_child', TRUE );
		//$permission = TTnew('Permission'); //This clears cache

		$ulf = TTnew('UserListFactory');
		$user_obj = $ulf->getById( $superior_user_id )->getCurrent();

		//Global current_user/current_company as this is required to properly check permissions in each report
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$config['other']['disable_grand_total'] = TRUE;
		$config['columns'][] = 'employee_number';
		$config['columns'][] = 'first_name';
		$config['columns'][] = 'last_name';
		$config['columns'][] = 'hourly_rate';
		$config['sort'][] = array( 'employee_number' => 'asc' ); //Force sort, so it doesn't change on us.

		$report_obj = TTnew('UserSummaryReport');
		$report_obj->setUserObject( $user_obj );
		$report_obj->setPermissionObject( new Permission() );
		$report_obj->setConfig( (array)$config );
		$output_data = $report_obj->getOutput( 'raw' );

		$this->assertEquals( 7, count($output_data) );
		$this->assertArrayHasKey('employee_number', $output_data[0] );
		$this->assertArrayHasKey('hourly_rate', $output_data[0] );
		$this->assertGreaterThan( 10.00, $output_data[0]['hourly_rate'] );
		$this->assertArrayHasKey('hourly_rate', $output_data[1] );
		$this->assertGreaterThan( 10.00, $output_data[1]['hourly_rate'] );
		$this->assertArrayHasKey('hourly_rate', $output_data[2] );
		$this->assertGreaterThan( 10.00, $output_data[2]['hourly_rate'] );
		$this->assertArrayHasKey('hourly_rate', $output_data[3] );
		$this->assertGreaterThan( 10.00, $output_data[3]['hourly_rate'] );
		$this->assertArrayHasKey('hourly_rate', $output_data[4] );
		$this->assertGreaterThan( 10.00, $output_data[4]['hourly_rate'] );
		$this->assertArrayHasKey('hourly_rate', $output_data[5] );
		$this->assertGreaterThan( 10.00, $output_data[5]['hourly_rate'] );
		$this->assertArrayHasKey('hourly_rate', $output_data[6] );
		$this->assertGreaterThan( 10.00, $output_data[6]['hourly_rate'] );

		return TRUE;
	}
}
?>