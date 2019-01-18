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
 * @group Schedule
 */
class ScheduleTest extends PHPUnit_Framework_TestCase {
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
		$this->legal_entity_id = $dd->createLegalEntity( $this->company_id, 10 );
		Debug::text('Company ID: '. $this->company_id, __FILE__, __LINE__, __METHOD__, 10);

		//$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$dd->createCurrency( $this->company_id, 10 );

		$this->branch_id = $dd->createBranch( $this->company_id, 10 ); //NY
		$this->department_id = $dd->createDepartment( $this->company_id, 10 );


		$dd->createPayStubAccount( $this->company_id );
		$this->createPayStubAccounts();
		//$this->createPayStubAccrualAccount();
		$dd->createPayStubAccountLink( $this->company_id );
		$this->getPayStubAccountLinkArray();

		$dd->createUserWageGroups( $this->company_id );

		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 100 );
		$this->user_id2 = $dd->createUser( $this->company_id, $this->legal_entity_id, 10 );

		$this->policy_ids['accrual_policy_account'][20] = $dd->createAccrualPolicyAccount( $this->company_id, 20 ); //Vacation
		$this->policy_ids['accrual_policy_account'][30] = $dd->createAccrualPolicyAccount( $this->company_id, 30 ); //Sick

		$this->policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x
		$this->policy_ids['pay_formula_policy'][120] = $dd->createPayFormulaPolicy( $this->company_id, 120, $this->policy_ids['accrual_policy_account'][20] ); //Vacation
		$this->policy_ids['pay_formula_policy'][130] = $dd->createPayFormulaPolicy( $this->company_id, 130, $this->policy_ids['accrual_policy_account'][30] ); //Sick

		$this->policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $this->policy_ids['pay_formula_policy'][100] ); //Regular
		$this->policy_ids['pay_code'][900] = $dd->createPayCode( $this->company_id, 900, $this->policy_ids['pay_formula_policy'][120] ); //Vacation
		$this->policy_ids['pay_code'][910] = $dd->createPayCode( $this->company_id, 910, $this->policy_ids['pay_formula_policy'][130] ); //Sick

		$this->policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, array( $this->policy_ids['pay_code'][100] ) ); //Regular
		$this->policy_ids['contributing_shift_policy'][10] = $dd->createContributingShiftPolicy( $this->company_id, 10, $this->policy_ids['contributing_pay_code_policy'][10] ); //Regular

		$this->policy_ids['regular'][10] = $dd->createRegularTimePolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][10], $this->policy_ids['pay_code'][100] );

		$this->policy_ids['absence_policy'][10] = $dd->createAbsencePolicy( $this->company_id, 10, $this->policy_ids['pay_code'][900] ); //Vacation
		$this->policy_ids['absence_policy'][30] = $dd->createAbsencePolicy( $this->company_id, 30, $this->policy_ids['pay_code'][910] ); //Sick

		$this->assertGreaterThan( 0, $this->company_id );
		$this->assertGreaterThan( 0, $this->user_id );

		return TRUE;
	}

	public function tearDown() {
		Debug::text('Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10);

		//$this->deleteAllSchedules();

		return TRUE;
	}

	function getPayStubAccountLinkArray() {
		$this->pay_stub_account_link_arr = array(
			'total_gross' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Total Gross'),
			'total_deductions' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Total Deductions'),
			'employer_contribution' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 40, 'Employer Total Contributions'),
			'net_pay' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 40, 'Net Pay'),
			'regular_time' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 10, 'Regular Time'),
			);

		return TRUE;
	}

	function createPayStubAccounts() {
		Debug::text('Saving.... Employee Deduction - Other', __FILE__, __LINE__, __METHOD__, 10);
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus(10);
		$pseaf->setType(20);
		$pseaf->setName('Other');
		$pseaf->setOrder(290);

		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}

		Debug::text('Saving.... Employee Deduction - Other2', __FILE__, __LINE__, __METHOD__, 10);
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus(10);
		$pseaf->setType(20);
		$pseaf->setName('Other2');
		$pseaf->setOrder(291);

		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}

		Debug::text('Saving.... Employee Deduction - EI', __FILE__, __LINE__, __METHOD__, 10);
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus(10);
		$pseaf->setType(20);
		$pseaf->setName('EI');
		$pseaf->setOrder(292);

		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}

		Debug::text('Saving.... Employee Deduction - CPP', __FILE__, __LINE__, __METHOD__, 10);
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus(10);
		$pseaf->setType(20);
		$pseaf->setName('CPP');
		$pseaf->setOrder(293);

		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}

		//Link Account EI and CPP accounts
		$pseallf = new PayStubEntryAccountLinkListFactory();
		$pseallf->getByCompanyId( $this->company_id );
		if ( $pseallf->getRecordCount() > 0 ) {
			$pseal_obj = $pseallf->getCurrent();
			$pseal_obj->setEmployeeEI( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'EI') );
			$pseal_obj->setEmployeeCPP( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($this->company_id, 20, 'CPP') );
			$pseal_obj->Save();
		}


		return TRUE;
	}

	function createPayPeriodSchedule( $shift_assigned_day = 10 ) {
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
		$ppsf->setNewDayTriggerTime( (4 * 3600) );
		$ppsf->setMaximumShiftTime( (16 * 3600) );
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
					$end_date = ($end_date + ( (86400 * 14) ));
				}

				Debug::Text('I: '. $i .' End Date: '. TTDate::getDate('DATE+TIME', $end_date), __FILE__, __LINE__, __METHOD__, 10);

				$pps_obj->createNextPayPeriod( $end_date, (86400 * 3600), FALSE ); //Don't import punches, as that causes deadlocks when running tests in parallel.
			}

		}

		return TRUE;
	}

	function createMealPolicy( $type_id ) {
		$mpf = TTnew( 'MealPolicyFactory' );

		$mpf->setCompany( $this->company_id );

		switch ( $type_id ) {
			case 10: //60min auto-deduct.
				$mpf->setName( '60min (AutoDeduct)' );
				$mpf->setType( 10 ); //AutoDeduct
				$mpf->setTriggerTime( (3600 * 5) );
				$mpf->setAmount( 3600 );
				$mpf->setStartWindow( (3600 * 4) );
				$mpf->setWindowLength( (3600 * 2) );
				break;
		}

		$mpf->setPayCode( $this->policy_ids['pay_code'][100] );

		if ( $mpf->isValid() ) {
			$insert_id = $mpf->Save();
			Debug::Text('Meal Policy ID: '. $insert_id, __FILE__, __LINE__, __METHOD__, 10);

			return $insert_id;
		}

		Debug::Text('Failed Creating Meal Policy!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	function createSchedulePolicy( $meal_policy_id, $full_shift_absence_policy_id = 0, $partial_shift_absence_policy_id = 0 ) {
		$spf = TTnew( 'SchedulePolicyFactory' );

		$spf->setCompany( $this->company_id );
		$spf->setName( 'Schedule Policy' );
		$spf->setFullShiftAbsencePolicyID( $full_shift_absence_policy_id );
		$spf->setPartialShiftAbsencePolicyID( $partial_shift_absence_policy_id );
		$spf->setStartStopWindow( (3600 * 2) );

		if ( $spf->isValid() ) {
			$insert_id = $spf->Save(FALSE);

			$spf->setMealPolicy( $meal_policy_id );
			Debug::Text('Schedule Policy ID: '. $insert_id, __FILE__, __LINE__, __METHOD__, 10);

			return $insert_id;
		}

		Debug::Text('Failed Creating Schedule Policy!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	function createSchedule( $user_id, $date_stamp, $data = NULL ) {
		$sf = TTnew( 'ScheduleFactory' );
		$sf->setCompany( $this->company_id );
		$sf->setUser( $user_id );
		//$sf->setUserDateId( UserDateFactory::findOrInsertUserDate( $user_id, $date_stamp) );

		if ( isset($data['replaced_id'] ) ) {
			$sf->setReplacedId( $data['replaced_id'] );
		}

		if ( isset($data['status_id']) ) {
			$sf->setStatus( $data['status_id'] );
		} else {
			$sf->setStatus( 10 );
		}

		if ( isset($data['schedule_policy_id']) ) {
			$sf->setSchedulePolicyID( $data['schedule_policy_id'] );
		}

		if ( isset($data['absence_policy_id']) ) {
			$sf->setAbsencePolicyID( $data['absence_policy_id'] );
		}
		if ( isset($data['branch_id']) ) {
			$sf->setBranch( $data['branch_id'] );
		}
		if ( isset($data['department_id']) ) {
			$sf->setDepartment( $data['department_id'] );
		}

		if ( isset($data['job_id']) ) {
			$sf->setJob( $data['job_id'] );
		}

		if ( isset($data['job_item_id'] ) ) {
			$sf->setJobItem( $data['job_item_id'] );
		}

		if ( $data['start_time'] != '') {
			$start_time = strtotime( $data['start_time'], $date_stamp ) ;
		}
		if ( $data['end_time'] != '') {
			Debug::Text('End Time: '. $data['end_time'] .' Date Stamp: '. $date_stamp, __FILE__, __LINE__, __METHOD__, 10);
			$end_time = strtotime( $data['end_time'], $date_stamp ) ;
			Debug::Text('bEnd Time: '. $data['end_time'] .' - '. TTDate::getDate('DATE+TIME', $data['end_time']), __FILE__, __LINE__, __METHOD__, 10);
		}

		$sf->setStartTime( $start_time );
		$sf->setEndTime( $end_time );

		if ( $sf->isValid() ) {
			$sf->setEnableReCalculateDay(TRUE);
			$insert_id = $sf->Save();
			Debug::Text('Schedule ID: '. $insert_id, __FILE__, __LINE__, __METHOD__, 10);

			return $insert_id;
		}

		Debug::Text('Failed Creating Schedule!', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
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

	function getUserDateTotalArray( $start_date, $end_date ) {
		$udtlf = new UserDateTotalListFactory();

		$date_totals = array();

		//Get only system totals.
		$udtlf->getByCompanyIDAndUserIdAndObjectTypeAndStartDateAndEndDate( $this->company_id, $this->user_id, array(5, 20, 25, 30, 40, 50, 100, 110), $start_date, $end_date);
		if ( $udtlf->getRecordCount() > 0 ) {
			foreach($udtlf as $udt_obj) {
				$date_totals[$udt_obj->getDateStamp()][] = array(
												'date_stamp' => $udt_obj->getDateStamp(),
												'id' => $udt_obj->getId(),

												//Keep legacy status_id/type_id for now, so we don't have to change as many unit tests.
												'status_id' => $udt_obj->getStatus(),
												'type_id' => $udt_obj->getType(),
												'src_object_id' => $udt_obj->getSourceObject(),

												'object_type_id' => $udt_obj->getObjectType(),
												'pay_code_id' => $udt_obj->getPayCode(),

												'branch_id' => $udt_obj->getBranch(),
												'department_id' => $udt_obj->getDepartment(),
												'total_time' => $udt_obj->getTotalTime(),
												'name' => $udt_obj->getName(),

												'quantity' => $udt_obj->getQuantity(),
												'bad_quantity' => $udt_obj->getBadQuantity(),

												'hourly_rate' => $udt_obj->getHourlyRate(),
												//Override only shows for SYSTEM override columns...
												//Need to check Worked overrides too.
												'tmp_override' => $udt_obj->getOverride()
												);
			}
		}

		return $date_totals;
	}

	function getPunchDataArray( $start_date, $end_date ) {
		$plf = new PunchListFactory();

		$plf->getByCompanyIDAndUserIdAndStartDateAndEndDate( $this->company_id, $this->user_id, $start_date, $end_date );
		if ( $plf->getRecordCount() > 0 ) {
			//Only return punch_control data for now
			$i = 0;
			$prev_punch_control_id = NULL;
			foreach( $plf as $p_obj ) {
				if ( $prev_punch_control_id == NULL OR $prev_punch_control_id != $p_obj->getPunchControlID() ) {
					$date_stamp = TTDate::getBeginDayEpoch( $p_obj->getPunchControlObject()->getDateStamp() );
					$p_obj->setUser( $this->user_id );
					$p_obj->getPunchControlObject()->setPunchObject( $p_obj );

					$retarr[$date_stamp][$i] = array(
													'id' => $p_obj->getPunchControlObject()->getID(),
													'branch_id' => $p_obj->getPunchControlObject()->getBranch(),
													'date_stamp' => $date_stamp,
													//'user_date_id' => $p_obj->getPunchControlObject()->getUserDateID(),
													'shift_data' => $p_obj->getPunchControlObject()->getShiftData()
													);

					$prev_punch_control_id = $p_obj->getPunchControlID();
					$i++;
				}

			}

			if ( isset($retarr) ) {
				return $retarr;
			}
		}

		return array(); //Return blank array to make count() not complain about FALSE.
	}

	function getCurrentAccrualBalance( $user_id, $accrual_policy_account_id = NULL ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $accrual_policy_account_id == '' ) {
			$accrual_policy_account_id = $this->getId();
		}

		//Check min/max times of accrual policy.
		$ablf = TTnew( 'AccrualBalanceListFactory' );
		$ablf->getByUserIdAndAccrualPolicyAccount( $user_id, $accrual_policy_account_id );
		if ( $ablf->getRecordCount() > 0 ) {
			$accrual_balance = $ablf->getCurrent()->getBalance();
		} else {
			$accrual_balance = 0;
		}

		Debug::Text('&nbsp;&nbsp; Current Accrual Balance: '. $accrual_balance, __FILE__, __LINE__, __METHOD__, 10);

		return $accrual_balance;
	}

	/*
	 Tests:
		- Spanning midnight
		- Spanning DST.

	*/
	function testScheduleA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );


		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( $meal_policy_id );
		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 8:00AM',
																	'end_time' => '5:00PM',
																  ) );

		$slf = TTNew('ScheduleListFactory');
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( (8 * 3600), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}

		return TRUE;
	}

	function testScheduleB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::getBeginWeekEpoch( time() ); //Use current year
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = (TTDate::getBeginWeekEpoch( time() ) + (86400 * 1.5)); //Use current year, handle DST.
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );


		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( $meal_policy_id );
		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 11:00PM',
																	'end_time' => '8:00AM',
																  ) );

		$slf = TTNew('ScheduleListFactory');
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp2, TTDate::getDate('DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( (8 * 3600), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}

		return TRUE;
	}

	//DST time should be recorded based on the time the employee actually works, therefore one hour more on this day.
	function testScheduleDSTFall() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods( strtotime('01-Jan-2013') );
		$this->getAllPayPeriods();

		$date_epoch = strtotime('02-Nov-2013'); //Use current year
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = strtotime('03-Nov-2013'); //Use current year
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => 0,
																	'start_time' => ' 11:00PM',
																	'end_time' => '7:00AM',
																  ) );

		$slf = TTNew('ScheduleListFactory');
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp2, TTDate::getDate('DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( (9 * 3600), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}

		return TRUE;
	}

	//DST time should be recorded based on the time the employee actually works, therefore one hour more on this day.
	function testScheduleDSTFallB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods( strtotime('01-Jan-2013') );
		$this->getAllPayPeriods();

		$date_epoch = strtotime('05-Nov-2016'); //Use current year
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = strtotime('05-Nov-2016'); //Use current year
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		TTDate::setTimeFormat('g:i A T');
		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
				'schedule_policy_id' => 0,
				//'start_time' => '6:00PM PDT', //These will fail due to parsing PDT/PST -- ALSO SEE: test_DST() regarding the "quirk" about PST date parsing.
				//'end_time' => '6:00AM PST', //These will fail due to parsing PDT/PST -- ALSO SEE: test_DST() regarding the "quirk" about PST date parsing.
				'start_time' => '6:00PM America/Vancouver',
				'end_time' => '6:00AM America/Vancouver',
		) );
		TTDate::setTimeFormat('g:i A');

		$slf = TTNew('ScheduleListFactory');
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( '05-Nov-16', TTDate::getDate('DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( '06-Nov-16', TTDate::getDate('DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( (13 * 3600), $s_obj->getTotalTime() ); //6PM -> 6AM = 12hrs, plus 1hr DST.
		} else {
			$this->assertEquals( TRUE, FALSE );
		}

		return TRUE;
	}

	//DST time should be recorded based on the time the employee actually works, therefore one hour less on this day.
	function testScheduleDSTSpring() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods( strtotime('01-Jan-2013') );
		$this->getAllPayPeriods();

		$date_epoch = strtotime('09-Mar-2013'); //Use current year
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch2 = strtotime('10-Mar-2013'); //Use current year
		$date_stamp2 = TTDate::getDate('DATE', $date_epoch2 );

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => 0,
																	'start_time' => ' 11:00PM',
																	'end_time' => '7:00AM',
																  ) );

		$slf = TTNew('ScheduleListFactory');
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp2, TTDate::getDate('DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( (7 * 3600), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}

		return TRUE;
	}


	function testScheduleUnderTimePolicyA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL, //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									NULL, //Break
									NULL, //Accrual
									NULL, //Expense
									array( $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ), //Absence
									array($this->policy_ids['regular'][10]) //Regular
									);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( array( 0 ), 0, $this->policy_ids['absence_policy'][10] ); //Partial Shift Only
		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 8:00AM',
																	'end_time' => '4:00PM',
																  ) );

		$slf = TTNew('ScheduleListFactory');
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( (8 * 3600), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}


		//Create punches to trigger undertime on same day.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 3:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//var_dump( $udt_arr );

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['object_type_id'], 5 ); //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (8 * 3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['object_type_id'], 20 ); //Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] ); //Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (7 * 3600) );
		//Absence Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['object_type_id'], 25 ); //Absence
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][900] ); //Absence
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1 * 3600) );
		//Absence Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['object_type_id'], 50 ); //Absence
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $this->policy_ids['pay_code'][900] ); //Absence
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (1 * 3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		//Check Accrual Balance
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][20] );
		$this->assertEquals( $accrual_balance, (1 * -3600) );

		//Add a 0.5hr absence of the same type, but because there is already an entry for this,
		//this will take precedance and override the undertime absence.
		//Therefore it shouldn't change the accrual due to the conflict detection.
		$absence_id = $dd->createAbsence( $this->user_id, $date_epoch, (0.5 * 3600), $this->policy_ids['absence_policy'][10], TRUE );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][20] );
		$this->assertEquals( $accrual_balance, (0.5 * -3600) );

		$dd->deleteAbsence( $absence_id );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][20] );
		$this->assertEquals( $accrual_balance, (1.0 * -3600) );


		//Add a 1hr absence of the same type, but because there is already an entry for this,
		//this will take precedance and override the undertime absence.
		//Therefore it shouldn't change the accrual due to the conflict detection.
		$absence_id = $dd->createAbsence( $this->user_id, $date_epoch, (1 * 3600), $this->policy_ids['absence_policy'][10] );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][20] );
		$this->assertEquals( $accrual_balance, (1 * -3600) );

		$dd->deleteAbsence( $absence_id );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][20] );
		$this->assertEquals( $accrual_balance, (1 * -3600) );


		//Add a 2hr absence of the same type, this should adjust the accrual balance by 2hrs though.
		$absence_id = $dd->createAbsence( $this->user_id, $date_epoch, (2 * 3600), $this->policy_ids['absence_policy'][10], TRUE );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][20] );
		$this->assertEquals( $accrual_balance, (2 * -3600) );

		$dd->deleteAbsence( $absence_id );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][20] );
		$this->assertEquals( $accrual_balance, (1 * -3600) );


		//Add a 1hr absence of a *different*  type
		$absence_id = $dd->createAbsence( $this->user_id, $date_epoch, (1 * 3600), $this->policy_ids['absence_policy'][30], TRUE );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][30] );
		$this->assertEquals( $accrual_balance, (1 * -3600) );

		$dd->deleteAbsence( $absence_id );
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][30] );
		$this->assertEquals( $accrual_balance, (0 * -3600) );

		return TRUE;
	}

	function testScheduleUnderTimePolicyB() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL, //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									NULL, //Break
									NULL, //Accrual
									NULL, //Expense
									array( $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ), //Absence
									array($this->policy_ids['regular'][10]) //Regular
									);

		$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( array( 0 ), 0, 0 ); //No undertime
		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 8:00AM',
																	'end_time' => '4:00PM',
																  ) );

		$slf = TTNew('ScheduleListFactory');
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( (8 * 3600), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}


		//Create punches to trigger undertime on same day.
		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 3:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$punch_arr = $this->getPunchDataArray( TTDate::getBeginDayEpoch($date_epoch), TTDate::getEndDayEpoch($date_epoch) );
		//print_r($punch_arr);
		$this->assertEquals( 1, count($punch_arr[$date_epoch]) );
		$this->assertEquals( $date_epoch, $punch_arr[$date_epoch][0]['date_stamp'] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//var_dump( $udt_arr );

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['object_type_id'], 5 ); //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (7 * 3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['object_type_id'], 20 ); //Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] ); //Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (7 * 3600) );
		//Absence Time
		//$this->assertEquals( $udt_arr[$date_epoch][2]['object_type_id'], 25 ); //Absence
		//$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][900] ); //Absence
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );
		//Absence Time
		//$this->assertEquals( $udt_arr[$date_epoch][3]['object_type_id'], 50 ); //Absence
		//$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $this->policy_ids['pay_code'][900] ); //Absence
		//$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (1*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		//Check Accrual Balance
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][20] );
		$this->assertEquals( $accrual_balance, 0 );

		return TRUE;
	}

	function testScheduleUnderTimePolicyC() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL, //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									NULL, //Premium
									NULL, //Round
									array($this->user_id), //Users
									NULL, //Break
									NULL, //Accrual
									NULL, //Expense
									array( $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ), //Absence
									array($this->policy_ids['regular'][10]) //Regular
									);

		$date_epoch = TTDate::getBeginDayEpoch( ( time() - 86400 ) ); //This needs to be before today, as CalculatePolicy() restricts full shift undertime to only previous days.
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$meal_policy_id = $this->createMealPolicy( 10 ); //60min autodeduct
		$schedule_policy_id = $this->createSchedulePolicy( array( 0 ), $this->policy_ids['absence_policy'][10], 0 ); //Full Shift Undertime
		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
																	'schedule_policy_id' => $schedule_policy_id,
																	'start_time' => ' 8:00AM',
																	'end_time' => '4:00PM',
																  ) );

		$slf = TTNew('ScheduleListFactory');
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( (8 * 3600), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//var_dump( $udt_arr );

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['object_type_id'], 5 ); //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (8 * 3600) );
		//Absence Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['object_type_id'], 25 ); //Absence
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][900] ); //Absence
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (8 * 3600) );
		//Absence Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['object_type_id'], 50 ); //Absence
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][900] ); //Absence
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (8 * 3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		//Check Accrual Balance
		$accrual_balance = $this->getCurrentAccrualBalance( $this->user_id, $this->policy_ids['accrual_policy_account'][20] );
		$this->assertEquals( $accrual_balance, (-8 * 3600) );

		return TRUE;
	}

	function testScheduleConflictA() {
		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
								   NULL, //Meal
								   NULL, //Exception
								   NULL, //Holiday
								   NULL, //OT
								   NULL, //Premium
								   NULL, //Round
								   array($this->user_id), //Users
								   NULL, //Break
								   NULL, //Accrual
								   NULL, //Expense
								   array( $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ), //Absence
								   array($this->policy_ids['regular'][10]) //Regular
		);

		$date_epoch = TTDate::getBeginDayEpoch( ( time() - 86400 ) ); //This needs to be before today, as CalculatePolicy() restricts full shift undertime to only previous days.
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$schedule_policy_id = $this->createSchedulePolicy( array( 0 ), $this->policy_ids['absence_policy'][10], 0 ); //Full Shift Undertime
		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => ' 8:30AM',
				'end_time'           => '4:30PM',
		) );

		$slf = TTNew('ScheduleListFactory');
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( (8 * 3600), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
				'schedule_policy_id' => $schedule_policy_id,
				'start_time' => ' 8:30AM',
				'end_time' => '4:30PM',
		) );
		$this->assertEquals( $schedule_id, FALSE ); //Validation error should occur, conflicting start/end time.

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
				'schedule_policy_id' => $schedule_policy_id,
				'start_time' => ' 8:35AM',
				'end_time' => '4:30PM',
		) );
		$this->assertEquals( $schedule_id, FALSE ); //Validation error should occur, conflicting start/end time.

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
				'schedule_policy_id' => $schedule_policy_id,
				'start_time' => ' 8:30AM',
				'end_time' => '4:35PM',
		) );
		$this->assertEquals( $schedule_id, FALSE ); //Validation error should occur, conflicting start/end time.

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
				'schedule_policy_id' => $schedule_policy_id,
				'start_time' => ' 8:25AM',
				'end_time' => '4:30PM',
		) );
		$this->assertEquals( $schedule_id, FALSE ); //Validation error should occur, conflicting start/end time.

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
				'schedule_policy_id' => $schedule_policy_id,
				'start_time' => ' 8:30AM',
				'end_time' => '4:25PM',
		) );
		$this->assertEquals( $schedule_id, FALSE ); //Validation error should occur, conflicting start/end time.

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
				'schedule_policy_id' => $schedule_policy_id,
				'start_time' => ' 8:25AM',
				'end_time' => '4:25PM',
		) );
		$this->assertEquals( $schedule_id, FALSE ); //Validation error should occur, conflicting start/end time.

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
				'schedule_policy_id' => $schedule_policy_id,
				'start_time' => ' 8:35AM',
				'end_time' => '4:35PM',
		) );
		$this->assertEquals( $schedule_id, FALSE ); //Validation error should occur, conflicting start/end time.

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
				'schedule_policy_id' => $schedule_policy_id,
				'start_time' => ' 8:25AM',
				'end_time' => '4:35PM',
		) );
		$this->assertEquals( $schedule_id, FALSE ); //Validation error should occur, conflicting start/end time.

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
				'schedule_policy_id' => $schedule_policy_id,
				'start_time' => ' 1:00PM',
				'end_time' => '1:05PM',
		) );
		$this->assertEquals( $schedule_id, FALSE ); //Validation error should occur, conflicting start/end time.

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
				'schedule_policy_id' => $schedule_policy_id,
				'start_time' => ' 1:25AM',
				'end_time' => '11:35PM',
		) );
		$this->assertEquals( $schedule_id, FALSE ); //Validation error should occur, conflicting start/end time.

		return TRUE;
	}

	function testOpenScheduleConflictA() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return TRUE;
		}

		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
								   NULL, //Meal
								   NULL, //Exception
								   NULL, //Holiday
								   NULL, //OT
								   NULL, //Premium
								   NULL, //Round
								   array($this->user_id), //Users
								   NULL, //Break
								   NULL, //Accrual
								   NULL, //Expense
								   array( $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ), //Absence
								   array($this->policy_ids['regular'][10]) //Regular
		);

		$date_epoch = TTDate::getBeginDayEpoch( ( time() - 86400 ) ); //This needs to be before today, as CalculatePolicy() restricts full shift undertime to only previous days.
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$iso_date_stamp = date('Ymd', $date_epoch );

		$schedule_policy_id = $this->createSchedulePolicy( array( 0 ), $this->policy_ids['absence_policy'][10], 0 ); //Full Shift Undertime

		//Create OPEN shift.
		$open_schedule_id = $this->createSchedule( 0, $date_epoch, array(
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '8:30AM',
				'end_time'           => '4:30PM',
		) );

		$slf = TTNew('ScheduleListFactory');
		$slf->getByID( $open_schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( (8 * 3600), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '8:30AM',
				'end_time'           => '4:30PM',
		) );
		$slf = TTNew('ScheduleListFactory');
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( (8 * 3600), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}

		//Populate global variables for current_user.
		$ulf = TTnew('UserListFactory');
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew('ScheduleFactory');
		$schedule_arr = $sf->getScheduleArray( array('start_date' => $date_epoch, 'end_date' => $date_epoch ) );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 0, $schedule_arr[$iso_date_stamp] );
		if ( isset($schedule_arr[$iso_date_stamp][0]) ) {
			$this->assertEquals( 1, count($schedule_arr[$iso_date_stamp]) );
			$this->assertEquals( $schedule_id, $schedule_arr[$iso_date_stamp][0]['id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][0]['date_stamp'] );
			$this->assertEquals( $open_schedule_id, $schedule_arr[$iso_date_stamp][0]['replaced_id'] );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}

		$tmp_schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
				'schedule_policy_id' => $schedule_policy_id,
				'start_time' => ' 8:30AM',
				'end_time' => '4:30PM',
		) );
		$this->assertEquals( $tmp_schedule_id, FALSE ); //Validation error should occur, conflicting start/end time.


		$tmp_schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
				'schedule_policy_id' => $schedule_policy_id,
				'start_time' => ' 8:30AM',
				'end_time' => '4:30PM',
				'replaced_id' => $schedule_id,
		) );
		$this->assertEquals( $tmp_schedule_id, FALSE ); //Validation error should occur, conflicting start/end time.


		//Attempt to "fill" a shift already assigned to a user to someone else.
		$schedule_id2 = $this->createSchedule( $this->user_id2, $date_epoch, array(
				'schedule_policy_id' => $schedule_policy_id,
				'start_time' => ' 8:30AM',
				'end_time' => '4:30PM',
				'replaced_id' => $schedule_id,
		) );
		$slf = TTNew('ScheduleListFactory');
		$slf->getByID( $schedule_id2 );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( (8 * 3600), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}

		$tmp_schedule_id = $this->createSchedule( $this->user_id2, $date_epoch, array(
				'schedule_policy_id' => $schedule_policy_id,
				'start_time' => ' 8:30AM',
				'end_time' => '4:30PM',
				'replaced_id' => $schedule_id,
		) );
		$this->assertEquals( $tmp_schedule_id, FALSE ); //Validation error should occur, conflicting start/end time.


		$sf = TTNew('ScheduleFactory');
		$schedule_arr = $sf->getScheduleArray( array('start_date' => $date_epoch, 'end_date' => $date_epoch ) );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 0, $schedule_arr[$iso_date_stamp] );
		$this->assertArrayHasKey( 1, $schedule_arr[$iso_date_stamp] );
		if ( isset($schedule_arr[$iso_date_stamp][0]) ) {
			$this->assertEquals( 2, count($schedule_arr[$iso_date_stamp]) );
			$this->assertEquals( $schedule_id, $schedule_arr[$iso_date_stamp][0]['id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][0]['date_stamp'] );
			$this->assertEquals( $open_schedule_id, $schedule_arr[$iso_date_stamp][0]['replaced_id'] );

			$this->assertEquals( $schedule_id2, $schedule_arr[$iso_date_stamp][1]['id'] );
			$this->assertEquals( $this->user_id2, $schedule_arr[$iso_date_stamp][1]['user_id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][1]['date_stamp'] );
			$this->assertEquals( TTUUID::getZeroID(), $schedule_arr[$iso_date_stamp][1]['replaced_id'] );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}

		//Now delete the schedules and make sure the original open shift reappears.
		$dd->deleteSchedule( $schedule_id );
		$dd->deleteSchedule( $schedule_id2 );
		$sf = TTNew('ScheduleFactory');

		$schedule_arr = $sf->getScheduleArray( array('start_date' => $date_epoch, 'end_date' => $date_epoch ) );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 0, $schedule_arr[$iso_date_stamp] );
		if ( isset($schedule_arr[$iso_date_stamp][0]) ) {
			$this->assertEquals( 1, count($schedule_arr[$iso_date_stamp]) );
			$this->assertEquals( $open_schedule_id, $schedule_arr[$iso_date_stamp][0]['id'] );
			$this->assertEquals( TTUUID::getZeroID(), $schedule_arr[$iso_date_stamp][0]['user_id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][0]['date_stamp'] );
			$this->assertEquals( TTUUID::getZeroID(), $schedule_arr[$iso_date_stamp][0]['replaced_id'] );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}

		return TRUE;
	}

	function testOpenScheduleConflictB() {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			return TRUE;
		}

		global $dd;

		$this->createPayPeriodSchedule( 10 );
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
								   NULL, //Meal
								   NULL, //Exception
								   NULL, //Holiday
								   NULL, //OT
								   NULL, //Premium
								   NULL, //Round
								   array($this->user_id), //Users
								   NULL, //Break
								   NULL, //Accrual
								   NULL, //Expense
								   array( $this->policy_ids['absence_policy'][10], $this->policy_ids['absence_policy'][30] ), //Absence
								   array($this->policy_ids['regular'][10]) //Regular
		);

		$date_epoch = TTDate::getBeginDayEpoch( ( time() - 86400 ) ); //This needs to be before today, as CalculatePolicy() restricts full shift undertime to only previous days.
		$date_stamp = TTDate::getDate('DATE', $date_epoch );
		$iso_date_stamp = date('Ymd', $date_epoch );

		$schedule_policy_id = $this->createSchedulePolicy( array( 0 ), $this->policy_ids['absence_policy'][10], 0 ); //Full Shift Undertime

		//Create OPEN shift.
		$open_schedule_id = $this->createSchedule( 0, $date_epoch, array(
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '8:30AM',
				'end_time'           => '4:30PM',
		) );

		$slf = TTNew('ScheduleListFactory');
		$slf->getByID( $open_schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( (8 * 3600), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, array(
				'schedule_policy_id' => $schedule_policy_id,
				'start_time'         => '8:30AM',
				'end_time'           => '4:30PM',
		) );
		$slf = TTNew('ScheduleListFactory');
		$slf->getByID( $schedule_id );
		if ( $slf->getRecordCount() == 1 ) {
			$s_obj = $slf->getCurrent();
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getStartTime() ) );
			$this->assertEquals( $date_stamp, TTDate::getDate('DATE', $s_obj->getEndTime() ) );
			$this->assertEquals( (8 * 3600), $s_obj->getTotalTime() );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}

		//Populate global variables for current_user.
		$ulf = TTnew('UserListFactory');
		$user_obj = $ulf->getById( $this->user_id )->getCurrent();
		global $current_user, $current_company;
		$current_user = $user_obj;
		$current_company = $user_obj->getCompanyObject();

		$sf = TTNew('ScheduleFactory');
		$schedule_arr = $sf->getScheduleArray( array('start_date' => $date_epoch, 'end_date' => $date_epoch ) );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 0, $schedule_arr[$iso_date_stamp] );
		if ( isset($schedule_arr[$iso_date_stamp][0]) ) {
			$this->assertEquals( 1, count($schedule_arr[$iso_date_stamp]) );
			$this->assertEquals( $schedule_id, $schedule_arr[$iso_date_stamp][0]['id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][0]['date_stamp'] );
			$this->assertEquals( $open_schedule_id, $schedule_arr[$iso_date_stamp][0]['replaced_id'] );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}


		//Now edit the filled shift and change just the note, make sure the open shift does not reappear.
		$dd->editSchedule( $schedule_id, array('note' => 'Test1') );
		$sf = TTNew('ScheduleFactory');
		$schedule_arr = $sf->getScheduleArray( array('start_date' => $date_epoch, 'end_date' => $date_epoch ) );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 0, $schedule_arr[$iso_date_stamp] );
		if ( isset($schedule_arr[$iso_date_stamp][0]) ) {
			$this->assertEquals( 1, count($schedule_arr[$iso_date_stamp]) );
			$this->assertEquals( $schedule_id, $schedule_arr[$iso_date_stamp][0]['id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][0]['date_stamp'] );
			$this->assertEquals( $open_schedule_id, $schedule_arr[$iso_date_stamp][0]['replaced_id'] );
			$this->assertEquals( 'Test1', $schedule_arr[$iso_date_stamp][0]['note'] );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}


		//Now change the start/end time and confirm that the original open shift reappears since it no longer matches.
		$dd->editSchedule( $schedule_id, array('start_time' => strtotime( '8:35AM', $date_epoch ) ) );
		$sf = TTNew('ScheduleFactory');
		$schedule_arr = $sf->getScheduleArray( array('start_date' => $date_epoch, 'end_date' => $date_epoch ) );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 1, $schedule_arr[$iso_date_stamp] );
		if ( isset($schedule_arr[$iso_date_stamp][1]) ) {
			$this->assertEquals( 2, count($schedule_arr[$iso_date_stamp]) );
			$this->assertEquals( $schedule_id, $schedule_arr[$iso_date_stamp][1]['id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][1]['date_stamp'] );
			$this->assertEquals( TTUUID::getZeroID(), $schedule_arr[$iso_date_stamp][1]['replaced_id'] );
			$this->assertEquals( 'Test1', $schedule_arr[$iso_date_stamp][1]['note'] );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}


		//Now change the start/end time back and confirm that the original open shift is filled again.
		$dd->editSchedule( $schedule_id, array('start_time' => strtotime( '8:30AM', $date_epoch ) ) );
		$sf = TTNew('ScheduleFactory');
		$schedule_arr = $sf->getScheduleArray( array('start_date' => $date_epoch, 'end_date' => $date_epoch ) );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 0, $schedule_arr[$iso_date_stamp] );
		if ( isset($schedule_arr[$iso_date_stamp][0]) ) {
			$this->assertEquals( 1, count($schedule_arr[$iso_date_stamp]) );
			$this->assertEquals( $schedule_id, $schedule_arr[$iso_date_stamp][0]['id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][0]['date_stamp'] );
			$this->assertEquals( $open_schedule_id, $schedule_arr[$iso_date_stamp][0]['replaced_id'] );
			$this->assertEquals( 'Test1', $schedule_arr[$iso_date_stamp][0]['note'] );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}


		//Now change the branch and confirm that the original open shift reappears since it no longer matches.
		$dd->editSchedule( $schedule_id, array('branch_id' => $this->branch_id ) );
		$sf = TTNew('ScheduleFactory');
		$schedule_arr = $sf->getScheduleArray( array('start_date' => $date_epoch, 'end_date' => $date_epoch ) );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 1, $schedule_arr[$iso_date_stamp] );
		if ( isset($schedule_arr[$iso_date_stamp][1]) ) {
			$this->assertEquals( 2, count($schedule_arr[$iso_date_stamp]) );
			$this->assertEquals( $schedule_id, $schedule_arr[$iso_date_stamp][1]['id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][1]['date_stamp'] );
			$this->assertEquals( TTUUID::getZeroID(), $schedule_arr[$iso_date_stamp][1]['replaced_id'] );
			$this->assertEquals( 'Test1', $schedule_arr[$iso_date_stamp][1]['note'] );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}


		//Now change the branch back and confirm that the original open shift is filled again.
		$dd->editSchedule( $schedule_id, array('branch_id' => 0 ) );
		$sf = TTNew('ScheduleFactory');
		$schedule_arr = $sf->getScheduleArray( array('start_date' => $date_epoch, 'end_date' => $date_epoch ) );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 0, $schedule_arr[$iso_date_stamp] );
		if ( isset($schedule_arr[$iso_date_stamp][0]) ) {
			$this->assertEquals( 1, count($schedule_arr[$iso_date_stamp]) );
			$this->assertEquals( $schedule_id, $schedule_arr[$iso_date_stamp][0]['id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][0]['date_stamp'] );
			$this->assertEquals( $open_schedule_id, $schedule_arr[$iso_date_stamp][0]['replaced_id'] );
			$this->assertEquals( 'Test1', $schedule_arr[$iso_date_stamp][0]['note'] );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}


		//Now change the department and confirm that the original open shift reappears since it no longer matches.
		$dd->editSchedule( $schedule_id, array('department_id' => $this->department_id ) );
		$sf = TTNew('ScheduleFactory');
		$schedule_arr = $sf->getScheduleArray( array('start_date' => $date_epoch, 'end_date' => $date_epoch ) );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 1, $schedule_arr[$iso_date_stamp] );
		if ( isset($schedule_arr[$iso_date_stamp][1]) ) {
			$this->assertEquals( 2, count($schedule_arr[$iso_date_stamp]) );
			$this->assertEquals( $schedule_id, $schedule_arr[$iso_date_stamp][1]['id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][1]['date_stamp'] );
			$this->assertEquals( TTUUID::getZeroID(), $schedule_arr[$iso_date_stamp][1]['replaced_id'] );
			$this->assertEquals( 'Test1', $schedule_arr[$iso_date_stamp][1]['note'] );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}


		//Now change the department back and confirm that the original open shift is filled again.
		$dd->editSchedule( $schedule_id, array('department_id' => 0 ) );
		$sf = TTNew('ScheduleFactory');
		$schedule_arr = $sf->getScheduleArray( array('start_date' => $date_epoch, 'end_date' => $date_epoch ) );
		//var_dump($schedule_arr);
		$this->assertArrayHasKey( $iso_date_stamp, $schedule_arr );
		$this->assertArrayHasKey( 0, $schedule_arr[$iso_date_stamp] );
		if ( isset($schedule_arr[$iso_date_stamp][0]) ) {
			$this->assertEquals( 1, count($schedule_arr[$iso_date_stamp]) );
			$this->assertEquals( $schedule_id, $schedule_arr[$iso_date_stamp][0]['id'] );
			$this->assertEquals( $date_stamp, $schedule_arr[$iso_date_stamp][0]['date_stamp'] );
			$this->assertEquals( $open_schedule_id, $schedule_arr[$iso_date_stamp][0]['replaced_id'] );
			$this->assertEquals( 'Test1', $schedule_arr[$iso_date_stamp][0]['note'] );
		} else {
			$this->assertEquals( TRUE, FALSE );
		}

		return TRUE;
	}
}
?>