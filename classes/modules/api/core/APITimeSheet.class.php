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
 * @package API\Core
 */
class APITimeSheet extends APIFactory {
	protected $main_class = false;

	/**
	 * APITimeSheet constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * Get all necessary dates for building the TimeSheet in a single call, this is mainly as a performance optimization.
	 * @param bool $base_date
	 * @return array
	 * @internal param array $data filter data
	 */
	function getTimeSheetDates( $base_date = null ) {
		$epoch = TTDate::parseDateTime( $base_date );


		if ( $epoch == '' || $epoch < 946728000 || $epoch > ( time() + ( 3650 * 86400 ) ) ) { //Make sure date is after 01-Jan-2000 and before 10 years in the future.
			$epoch = TTDate::getTime();
		}

		$start_date = TTDate::getBeginWeekEpoch( $epoch, $this->getCurrentUserPreferenceObject()->getStartWeekDay() );
		$end_date = TTDate::getEndWeekEpoch( $epoch, $this->getCurrentUserPreferenceObject()->getStartWeekDay() );

		$retarr = [
				'base_date'          => $epoch,
				'start_date'         => $start_date,
				'end_date'           => $end_date,
				'base_display_date'  => TTDate::getAPIDate( 'DATE', $epoch ),
				'start_display_date' => TTDate::getAPIDate( 'DATE', $start_date ),
				'end_display_date'   => TTDate::getAPIDate( 'DATE', $end_date ),
		];

		return $retarr;
	}


	/**
	 * Get all data for displaying the timesheet.
	 * @param string $user_id UUID
	 * @param int $base_date  EPOCH
	 * @param bool $data
	 * @return array|bool
	 */
	function getTimeSheetData( $user_id, $base_date = null, $data = null ) {
		if ( $user_id == '' || TTUUID::isUUID( $user_id ) == false ) {
			//This isn't really permission issue, but in cases where the user can't see any employees timesheets, we want to display an error to them at least.
			//return $this->returnHandler( FALSE );
			return $this->getPermissionObject()->PermissionDenied();
		}
		$user_id = TTUUID::castUUID( $user_id );

		if ( $base_date == '' ) {
			return $this->returnHandler( false );
		}

		$profile_start = microtime( true );

		if ( !$this->getPermissionObject()->Check( 'punch', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'punch', 'view' ) || $this->getPermissionObject()->Check( 'punch', 'view_child' ) || $this->getPermissionObject()->Check( 'punch', 'view_own' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		//Check for ===FALSE on permission_children_ids, as that means their are no children assigned to them and they don't have view all permissions.
		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'punch', 'view' );
		if ( $data['filter_data']['permission_children_ids'] === false || ( is_array( $data['filter_data']['permission_children_ids'] ) && !in_array( $user_id, $data['filter_data']['permission_children_ids'] ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		//
		//Get timesheet start/end dates.
		//
		$timesheet_dates = $this->getTimesheetDates( $base_date );

		//Include all dates within the timesheet range.
		$timesheet_dates['pay_period_date_map'] = []; //Add array containing date => pay_period_id pairs.

		//
		//Get PayPeriod information
		//
		$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */

		$pplf->StartTransaction();
		//Make sure we all pay periods that fall within the start/end date, so we can properly display the timesheet range at the top.
		$primary_pay_period_id = TTUUID::getZeroID();
		$pay_period_ids = [];
		$pplf->getByUserIdAndOverlapStartDateAndEndDate( $user_id, $timesheet_dates['start_date'], $timesheet_dates['end_date'] );
		if ( $pplf->getRecordCount() > 0 ) {
			foreach ( $pplf as $pp_obj ) {
				$pay_period_ids[] = $pp_obj->getId();
				if ( $pp_obj->getStartDate() <= $timesheet_dates['base_date'] && $pp_obj->getEndDate() >= $timesheet_dates['base_date'] ) {
					$primary_pay_period_id = $pp_obj->getId();
				}
				$timesheet_dates['pay_period_date_map'] += (array)$pp_obj->getPayPeriodDates( $timesheet_dates['start_date'], $timesheet_dates['end_date'], true );
			}
			unset( $pp_obj );
		}
		//Debug::Text('Pay Periods: '. $pplf->getRecordCount() .' Primary Pay Period: '. $primary_pay_period_id, __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($timesheet_dates, 'TimeSheet Dates: ', __FILE__, __LINE__, __METHOD__, 10);

		//
		//Get punches
		//
		$punch_data = [];
		$filter_data = $this->initializeFilterAndPager( [ 'filter_data' => [ 'start_date' => $timesheet_dates['start_date'], 'end_date' => $timesheet_dates['end_date'], 'user_id' => $user_id ] ], true );

		//Carry over timesheet filter options.
		if ( isset( $data['filter_data']['branch_id'] ) ) {
			$filter_data['filter_data']['branch_id'] = $data['filter_data']['branch_id'];
		}
		if ( isset( $data['filter_data']['department_id'] ) ) {
			$filter_data['filter_data']['department_id'] = $data['filter_data']['department_id'];
		}
		if ( isset( $data['filter_data']['job_id'] ) ) {
			$filter_data['filter_data']['job_id'] = $data['filter_data']['job_id'];
		}
		if ( isset( $data['filter_data']['job_item_id'] ) ) {
			$filter_data['filter_data']['job_item_id'] = $data['filter_data']['job_item_id'];
		}

		$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
		$plf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $filter_data['filter_data'], $filter_data['filter_items_per_page'], $filter_data['filter_page'], null, [ 'b.pay_period_id' => 'asc', 'b.user_id' => 'asc', 'a.time_stamp' => 'asc', 'a.punch_control_id' => 'asc', 'a.status_id' => 'asc' ] ); //Order is critical to the timesheet layout.
		Debug::Text( 'Punch Record Count: ' . $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $plf->getRecordCount() > 0 ) {
			//Reduces data transfer by about half.
			$punch_columns = [
					'id'                => true,
					'user_id'           => true,
					'transfer'          => true,
					'type_id'           => true,
					'type'              => true,
					'status_id'         => true,
					'status'            => true,
					'time_stamp'        => true,
					'raw_time_stamp'    => true,
					'punch_date'        => true,
					'punch_time'        => true,
					'punch_control_id'  => true,
					'longitude'         => true,
					'latitude'          => true,
					'position_accuracy' => true,
					'date_stamp'        => true,
					'pay_period_id'     => true,
					'note'              => true,
					'tainted'           => true,
					'has_image'         => true,
					'branch_id'         => true,
					'department_id'     => true,
					'job_id'            => true,
					'job_item_id'       => true,
			];

			foreach ( $plf as $p_obj ) {
				//$punch_data[] = $p_obj->getObjectAsArray( NULL, $data['filter_data']['permission_children_ids'] );
				//Don't need to pass permission_children_ids, as Flex uses is_owner/is_child from the timesheet user record instead, not the punch record.
				$punch_data[] = $p_obj->getObjectAsArray( $punch_columns );
			}
		}

		$meal_and_break_total_data = PunchFactory::calcMealAndBreakTotalTime( $punch_data );
		if ( $meal_and_break_total_data === false ) {
			$meal_and_break_total_data = [];
		}

		//Get Wage Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$wage_permission_children_ids = $this->getPermissionObject()->getPermissionChildren( 'wage', 'view' );

		//
		//Get total time for day/pay period
		//
		$user_date_total_data = [];
		$absence_user_date_total_data = [];
		$udt_filter_data = $this->initializeFilterAndPager( [ 'filter_data' => [ 'start_date' => $timesheet_dates['start_date'], 'end_date' => $timesheet_dates['end_date'], 'user_id' => $user_id ] ], true );

		//Carry over timesheet filter options.
		if ( isset( $data['filter_data']['branch_id'] ) ) {
			$udt_filter_data['filter_data']['branch_id'] = $data['filter_data']['branch_id'];
		}
		if ( isset( $data['filter_data']['department_id'] ) ) {
			$udt_filter_data['filter_data']['department_id'] = $data['filter_data']['department_id'];
		}
		if ( isset( $data['filter_data']['job_id'] ) ) {
			$udt_filter_data['filter_data']['job_id'] = $data['filter_data']['job_id'];
		}
		if ( isset( $data['filter_data']['job_item_id'] ) ) {
			$udt_filter_data['filter_data']['job_item_id'] = $data['filter_data']['job_item_id'];
		}

		$udtlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udtlf */
		$udtlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $udt_filter_data['filter_data'], $udt_filter_data['filter_items_per_page'], $udt_filter_data['filter_page'], null, $udt_filter_data['filter_sort'] );
		Debug::Text( 'User Date Total Record Count: ' . $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $udtlf->getRecordCount() > 0 ) {
			//Specifying the columns is about a 30% speed up for large timesheets.
			$udt_columns = [
					'id'         => true,
					'user_id'    => true,
					'date_stamp' => true,

					//'status_id' => TRUE,
					//'type_id' => TRUE,

					'object_type_id' => true,
					'src_object_id'  => true,
					'pay_code_id'    => true,
					'policy_name'    => true,
					'name'           => true,

					'branch_id'     => true,
					'branch'        => true,
					'department_id' => true,
					'department'    => true,
					'job_id'        => true,
					'job'           => true,
					'job_item_id'   => true,
					'job_item'      => true,

					'total_time' => true,

					'pay_period_id' => true,

					'override' => true,
					'note'     => true,
			];

			if ( $this->getPermissionObject()->isPermissionChild( $user_id, $wage_permission_children_ids ) ) {
				$udt_columns['total_time_amount'] = true;
				$udt_columns['hourly_rate'] = true;
			}

			foreach ( $udtlf as $udt_obj ) {
				//Don't need to pass permission_children_ids, as Flex uses is_owner/is_child from the timesheet user record instead, not the punch record.
				//$user_date_total = $udt_obj->getObjectAsArray( NULL, $data['filter_data']['permission_children_ids'] );
				$user_date_total = $udt_obj->getObjectAsArray( $udt_columns );
				$user_date_total_data[] = $user_date_total;

				//Extract just absence records so we can send those to the user, rather than all UDT rows as only absences are used.
				if ( $user_date_total['object_type_id'] == 50 ) {
					$absence_user_date_total_data[] = $user_date_total;
				}

				//Get all pay periods that have total time assigned to them.
				$timesheet_dates['pay_period_date_map'][$user_date_total['date_stamp']] = $pay_period_ids[] = $user_date_total['pay_period_id'];

				//Adjust primary pay period if the pay period schedules were changed mid-way through perhaps.
				if ( $timesheet_dates['base_display_date'] == $user_date_total['date_stamp'] && $timesheet_dates['pay_period_date_map'][$user_date_total['date_stamp']] != $primary_pay_period_id ) {
					$primary_pay_period_id = $user_date_total['pay_period_id'];
					Debug::Text( 'Changing primary pay period to: ' . $primary_pay_period_id, __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
			unset( $user_date_total );
		}
		Debug::Arr( $timesheet_dates['pay_period_date_map'], 'Date/Pay Period IDs. Primary Pay Period ID: ' . $primary_pay_period_id, __FILE__, __LINE__, __METHOD__, 10 );

		$accumulated_user_date_total_data = UserDateTotalFactory::calcAccumulatedTime( $user_date_total_data );
		if ( $accumulated_user_date_total_data === false ) {
			$accumulated_user_date_total_data = [];
		}
		unset( $user_date_total_data );

		//Get data for all pay periods
		$pay_period_data = [];
		$pplf->getByIDList( $pay_period_ids );
		if ( $pplf->getRecordCount() > 0 ) {
			$pp_columns = [
					'id'               => true,
					'status_id'        => true,
					//'status' => TRUE,
					'type_id'          => true,
					//'type' => TRUE,
					//'pay_period_schedule_id' => TRUE,
					'start_date'       => true,
					'end_date'         => true,
					'transaction_date' => true,
			];

			foreach ( $pplf as $pp_obj ) {
				$pay_period_data[$pp_obj->getId()] = $pp_obj->getObjectAsArray( $pp_columns );
				$pay_period_data[$pp_obj->getId()]['timesheet_verify_type_id'] = $pp_obj->getTimeSheetVerifyType();
				$pay_period_data[$pp_obj->getId()]['start_date_epoch'] = $pp_obj->getStartDate();
				$pay_period_data[$pp_obj->getId()]['end_date_epoch'] = $pp_obj->getEndDate();
			}
		}
		unset( $pp_obj );

		//Fill in payperiod gaps in timesheet primarily for migrating from one schedule to another or for new hires in middle of a pay period
		$calendar_dates = TTDate::getCalendarArray( $timesheet_dates['start_date'], $timesheet_dates['end_date'], 0, false );
		foreach ( $calendar_dates as $tmp_date ) {
			if ( !isset( $timesheet_dates['pay_period_date_map'][TTDate::getDate( 'DATE', $tmp_date['epoch'] )] ) ) {
				foreach ( $pay_period_data as $tmp_pp_data ) {
					if ( $tmp_date['epoch'] >= $tmp_pp_data['start_date_epoch'] && $tmp_date['epoch'] <= $tmp_pp_data['end_date_epoch'] ) {
						$timesheet_dates['pay_period_date_map'][TTDate::getDate( 'DATE', $tmp_date['epoch'] )] = $tmp_pp_data['id'];
					}
				}
			}
		}
		ksort( $timesheet_dates['pay_period_date_map'] );
		unset( $calendar_dates, $tmp_date, $tmp_pp_data );

		$pp_user_date_total_data = [];
		$pay_period_accumulated_user_date_total_data = [];
		if ( isset( $primary_pay_period_id ) && TTUUID::isUUID( $primary_pay_period_id ) && $primary_pay_period_id != TTUUID::getZeroID() && $primary_pay_period_id != TTUUID::getNotExistID() ) {
			$pp_udt_filter_data = $this->initializeFilterAndPager( [ 'filter_data' => [ 'pay_period_id' => $primary_pay_period_id, 'user_id' => $user_id ] ], true );

			//Carry over timesheet filter options.
			if ( isset( $data['filter_data']['branch_id'] ) ) {
				$pp_udt_filter_data['filter_data']['branch_id'] = $data['filter_data']['branch_id'];
			}
			if ( isset( $data['filter_data']['department_id'] ) ) {
				$pp_udt_filter_data['filter_data']['department_id'] = $data['filter_data']['department_id'];
			}
			if ( isset( $data['filter_data']['job_id'] ) ) {
				$pp_udt_filter_data['filter_data']['job_id'] = $data['filter_data']['job_id'];
			}
			if ( isset( $data['filter_data']['job_item_id'] ) ) {
				$pp_udt_filter_data['filter_data']['job_item_id'] = $data['filter_data']['job_item_id'];
			}

			$udtlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udtlf */
			$udtlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $pp_udt_filter_data['filter_data'], $pp_udt_filter_data['filter_items_per_page'], $pp_udt_filter_data['filter_page'], null, $pp_udt_filter_data['filter_sort'] );
			Debug::Text( 'PP User Date Total Record Count: ' . $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
			if ( $udtlf->getRecordCount() > 0 ) {
				//Specifying the columns is about a 30% speed up for large timesheets.
				//This is only needed for calcAccumulatedTime().
				$udt_columns = [
						'object_type_id' => true,
						'date_stamp'     => true,
						'name'           => true,
						'pay_code_id'    => true,
						'total_time'     => true,

						'branch_id'     => true,
						'branch'        => true,
						'department_id' => true,
						'department'    => true,
						'job_id'        => true,
						'job'           => true,
						'job_item_id'   => true,
						'job_item'      => true,
				];

				if ( $this->getPermissionObject()->isPermissionChild( $user_id, $wage_permission_children_ids ) ) {
					$udt_columns['total_time_amount'] = true;
					$udt_columns['hourly_rate'] = true;
				}

				foreach ( $udtlf as $udt_obj ) {
					$pp_user_date_total_data[] = $udt_obj->getObjectAsArray( $udt_columns );
				}

				$pay_period_accumulated_user_date_total_data = UserDateTotalFactory::calcAccumulatedTime( $pp_user_date_total_data, false );
				if ( isset( $pay_period_accumulated_user_date_total_data['total'] ) ) {
					$pay_period_accumulated_user_date_total_data = $pay_period_accumulated_user_date_total_data['total'];
				} else {
					$pay_period_accumulated_user_date_total_data = [];
				}
			}
		}
		unset( $pp_user_date_total_data );


		//
		//Get Exception data, use the same filter data as punches.
		//
		$exception_data = [];

		$elf = TTnew( 'ExceptionListFactory' ); /** @var ExceptionListFactory $elf */
		$elf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $filter_data['filter_data'], $filter_data['filter_items_per_page'], $filter_data['filter_page'], null, $filter_data['filter_sort'] );
		Debug::Text( 'Exception Record Count: ' . $elf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $elf->getRecordCount() > 0 ) {
			//Reduces data transfer.
			$exception_columns = [
					'id'                         => true,
					'date_stamp'                 => true,
					'exception_policy_id'        => true,
					'punch_control_id'           => true,
					'punch_id'                   => true,
					'type_id'                    => true,
					'type'                       => true,
					'severity_id'                => true,
					'severity'                   => true,
					'exception_color'            => true,
					'exception_background_color' => true,
					'exception_policy_type_id'   => true,
					'exception_policy_type'      => true,
					'pay_period_id'              => true,
			];

			foreach ( $elf as $e_obj ) {
				$exception_data[] = $e_obj->getObjectAsArray( $exception_columns );
			}
		}
		unset( $elf, $e_obj, $exception_columns );

		//
		//Get request data, so authorized/pending can be shown in a request row for each day.
		//If there are two requests for both authorized and pending, the pending is displayed.
		//
		$request_data = [];

		$rlf = TTnew( 'RequestListFactory' ); /** @var RequestListFactory $rlf */
		$rlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $filter_data['filter_data'], $filter_data['filter_items_per_page'], $filter_data['filter_page'], null, $filter_data['filter_sort'] );
		Debug::Text( 'Request Record Count: ' . $rlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $rlf->getRecordCount() > 0 ) {
			$request_columns = [
					'id'         => true,
					'user_id'    => true,
					'type_id'    => true,
					'status_id'  => true,
					'status'     => true,
					'date_stamp' => true,
					'authorized' => true,
			];

			foreach ( $rlf as $r_obj ) {
				$request_data[] = $r_obj->getObjectAsArray( $request_columns );
			}
		}
		unset( $rlf, $r_obj, $request_columns );

		//
		//Get timesheet verification information.
		//
		$timesheet_verify_data = [];
		if ( isset( $primary_pay_period_id ) && TTUUID::isUUID( $primary_pay_period_id ) && $primary_pay_period_id != TTUUID::getZeroID() && $primary_pay_period_id != TTUUID::getNotExistID() ) {
			$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' ); /** @var PayPeriodTimeSheetVerifyListFactory $pptsvlf */
			$pptsvlf->getByPayPeriodIdAndUserId( $primary_pay_period_id, $user_id );

			if ( $pptsvlf->getRecordCount() > 0 ) {
				$pptsv_obj = $pptsvlf->getCurrent();
				$pptsv_obj->setCurrentUser( $this->getCurrentUserObject()->getId() );
			} else {
				$pptsv_obj = $pptsvlf;
				$pptsv_obj->setCurrentUser( $this->getCurrentUserObject()->getId() );
				$pptsv_obj->setUser( $user_id );
				$pptsv_obj->setPayPeriod( $primary_pay_period_id );
				//$pptsv_obj->setStatus( 45 ); //Pending Verification
			}

			$verification_window_dates = $pptsv_obj->getVerificationWindowDates();
			if ( is_array( $verification_window_dates ) ) {
				$verification_window_dates['start'] = TTDate::getAPIDate( 'DATE', $verification_window_dates['start'] );
				$verification_window_dates['end'] = TTDate::getAPIDate( 'DATE', $verification_window_dates['end'] );
			}


			$timesheet_verify_data = [
					'id'                                       => $pptsv_obj->getId(),
					'user_verified'                            => $pptsv_obj->getUserVerified(),
					'user_verified_date'                       => $pptsv_obj->getUserVerifiedDate(),
					'status_id'                                => $pptsv_obj->getStatus(),
					'status'                                   => Option::getByKey( $pptsv_obj->getStatus(), $pptsv_obj->getOptions( 'status' ) ),
					'pay_period_id'                            => $pptsv_obj->getPayPeriod(),
					'user_id'                                  => $pptsv_obj->getUser(),
					'authorized'                               => $pptsv_obj->getAuthorized(),
					'is_hierarchy_superior'                    => $pptsv_obj->isHierarchySuperior(),
					'display_verify_button'                    => $pptsv_obj->displayVerifyButton(),
					'verification_box_color'                   => $pptsv_obj->getVerificationBoxColor(),
					'verification_status_display'              => $pptsv_obj->getVerificationStatusDisplay(),
					'previous_pay_period_verification_display' => $pptsv_obj->displayPreviousPayPeriodVerificationNotice(),
					'verification_confirmation_message'        => $pptsv_obj->getVerificationConfirmationMessage(),
					'verification_window_dates'                => $verification_window_dates,

					'created_date' => $pptsv_obj->getCreatedDate(),
					'created_by'   => $pptsv_obj->getCreatedBy(),
					'updated_date' => $pptsv_obj->getUpdatedDate(),
					'updated_by'   => $pptsv_obj->getUpdatedBy(),
					//'deleted_date' => $pptsv_obj->getDeletedDate(),
					//'deleted_by' => $pptsv_obj->getDeletedBy()
			];
			unset( $pptsvlf, $pptsv_obj, $verification_window_dates );

			if ( isset( $pay_period_data[$primary_pay_period_id] ) ) {
				$timesheet_verify_data['pay_period_verify_type_id'] = $pay_period_data[$primary_pay_period_id]['timesheet_verify_type_id'];
			}
		}

		//
		//Get holiday data.
		//
		$holiday_data = [];
		$hlf = TTnew( 'HolidayListFactory' ); /** @var HolidayListFactory $hlf */
		$hlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), [ 'start_date' => $timesheet_dates['start_date'], 'end_date' => $timesheet_dates['end_date'], 'user_id' => $user_id ], $filter_data['filter_items_per_page'], $filter_data['filter_page'], null, $filter_data['filter_sort'] );
		Debug::Text( 'Holiday Record Count: ' . $hlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $hlf->getRecordCount() > 0 ) {
			$holiday_columns = [
					'id'                => true,
					'holiday_policy_id' => true,
					'date_stamp'        => true,
					'name'              => true,
			];

			foreach ( $hlf as $h_obj ) {
				$holiday_data[] = $h_obj->getObjectAsArray( $holiday_columns );
			}
		}
		unset( $hlf, $h_obj, $holiday_columns );

		$pplf->CommitTransaction();

		$retarr = [
				'timesheet_dates' => $timesheet_dates,
				'pay_period_data' => $pay_period_data,

				'punch_data' => $punch_data,

				'holiday_data' => $holiday_data,

				'user_date_total_data'                        => $absence_user_date_total_data, //Currently just absence records, as those are the only ones used.
				'accumulated_user_date_total_data'            => $accumulated_user_date_total_data,
				'pay_period_accumulated_user_date_total_data' => $pay_period_accumulated_user_date_total_data,
				'meal_and_break_total_data'                   => $meal_and_break_total_data,

				'exception_data'        => $exception_data,
				'request_data'          => $request_data,
				'timesheet_verify_data' => $timesheet_verify_data,
		];

		//Debug::Arr($retarr, 'TimeSheet Data: ', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Text( 'TimeSheet Data: User ID:' . $user_id . ' Base Date: ' . $base_date . ' in: ' . ( microtime( true ) - $profile_start ) . 's', __FILE__, __LINE__, __METHOD__, 10 );

		return $this->returnHandler( $retarr );
	}

	/**
	 * Get all data for displaying the timesheet.
	 * @param string $user_id UUID
	 * @param int $base_date  EPOCH
	 * @return array
	 */
	function getTimeSheetTotalData( $user_id, $base_date = null ) {
		$retarr = [];

		$timesheet_data = $this->stripReturnHandler( $this->getTimeSheetData( $user_id, $base_date ) );
		if ( is_array( $timesheet_data ) ) {
			$retarr = [
					'timesheet_dates' => $timesheet_data['timesheet_dates'],
					'pay_period_data' => $timesheet_data['pay_period_data'],

					'accumulated_user_date_total_data'            => $timesheet_data['accumulated_user_date_total_data'],
					'pay_period_accumulated_user_date_total_data' => $timesheet_data['pay_period_accumulated_user_date_total_data'],
					'timesheet_verify_data'                       => $timesheet_data['timesheet_verify_data'],
			];
		}

		//Debug::Arr($retarr, 'TimeSheet Total Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return $this->returnHandler( $retarr );
	}

	/**
	 * ReCalculate timesheet/policies
	 * @param string|string[] $pay_period_ids UUID
	 * @param string $user_ids                UUID
	 * @return array|bool
	 */
	function reCalculateTimeSheet( $pay_period_ids, $user_ids = null ) {
		//Debug::text('Recalculating Employee Timesheet: User ID: '. $user_ids .' Pay Period ID: '. $pay_period_ids, __FILE__, __LINE__, __METHOD__, 10);
		//Debug::setVerbosity(11);

		if ( !$this->getPermissionObject()->Check( 'punch', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'punch', 'edit' ) || $this->getPermissionObject()->Check( 'punch', 'edit_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( Misc::isSystemLoadValid() == false ) { //Check system load before anything starts.
			Debug::Text( 'ERROR: System load exceeded, preventing new recalculation processes from starting...', __FILE__, __LINE__, __METHOD__, 10 );

			return $this->returnHandler( false );
		}

		//Use report maximum execution time here because larger customers may need more time to recalculate many employees, similar to a report.
		global $config_vars;
		if ( isset( $config_vars['other']['report_maximum_execution_limit'] ) && $config_vars['other']['report_maximum_execution_limit'] != '' ) {
			$maximum_execution_time = $config_vars['other']['report_maximum_execution_limit'];
			Debug::Text( 'Setting maximum execution time: ' . $maximum_execution_time, __FILE__, __LINE__, __METHOD__, 10 );
			ini_set( 'max_execution_time', $maximum_execution_time );
		}

		//Make sure pay period is not CLOSED.
		//We can re-calc on locked though.
		$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
		$pplf->getByIdList( $pay_period_ids, null, [ 'start_date' => 'asc' ] );
		if ( $pplf->getRecordCount() > 0 ) {
			foreach ( $pplf as $pp_obj ) {
				Debug::Text( 'Recalculating Pay Period: ' . $pp_obj->getId() . ' Start Date: ' . TTDate::getDate( 'DATE', $pp_obj->getStartDate() ), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $pp_obj->getStatus() != 20 ) {
					$recalculate_company = false;

					$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
					if ( is_array( $user_ids ) && count( $user_ids ) > 0 && isset( $user_ids[0] )
							&& TTUUID::isUUID( $user_ids[0] ) && $user_ids[0] != TTUUID::getZeroID() && $user_ids[0] != TTUUID::getNotExistID() ) {
						$ulf->getByIdAndCompanyId( $user_ids, $this->getCurrentCompanyObject()->getId() );
					} else if ( $this->getPermissionObject()->Check( 'punch', 'edit' ) == true ) { //Make sure they have the permissions to recalculate all employees.
						TTLog::addEntry( $this->getCurrentCompanyObject()->getId(), 500, TTi18n::gettext( 'Recalculating Company TimeSheet' ), $this->getCurrentUserObject()->getId(), 'user_date_total' );
						$ulf->getByCompanyId( $this->getCurrentCompanyObject()->getId() );
						$recalculate_company = true;
					} else {
						return $this->getPermissionObject()->PermissionDenied();
					}

					if ( $ulf->getRecordCount() > 0 ) {
						$start_date = $pp_obj->getStartDate();
						$end_date = $pp_obj->getEndDate();
						Debug::text( 'Found users to re-calculate: ' . $ulf->getRecordCount() . ' Start: ' . TTDate::getDate( 'DATE', $start_date ) . ' End: ' . TTDate::getDate( 'DATE', $end_date ), __FILE__, __LINE__, __METHOD__, 10 );

						$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'ReCalculating Pay Period Ending' ) . ': ' . TTDate::getDate( 'DATE', $pp_obj->getEndDate() ) );

						$x = 1;
						foreach ( $ulf as $u_obj ) {
							if ( Misc::isSystemLoadValid() == false ) { //Check system load as the user could ask to calculate decades worth at a time.
								Debug::Text( 'ERROR: System load exceeded, stopping recalculation...', __FILE__, __LINE__, __METHOD__, 10 );
								break;
							}

							//Ignore terminated employees when recalculating company. However allow all employees to be recalculated if they are selected individually.
							if (
									( $u_obj->getStatus() == 10 || $ulf->getRecordCount() == 1 ) //Always recalculate if just a single employee is selected.
									||
									(
											$u_obj->getStatus() != 10
											&&
											(
													(
															$recalculate_company == true
															&&
															( $u_obj->getTerminationDate() != '' && TTDate::getMiddleDayEpoch( $u_obj->getTerminationDate() ) > TTDate::getMiddleDayEpoch( $start_date ) ) //Only recaclulate terminated employees if they were terminated within this pay period.
													)
													||
													(
															$recalculate_company == false
															&&
															( $u_obj->getTerminationDate() != '' && TTDate::getMiddleDayEpoch( $u_obj->getTerminationDate() ) > ( TTDate::getMiddleDayEpoch( $start_date ) - ( 86400 * 90 ) ) ) //If the user was terminated more than 3 months ago, skip recalculating.
													)
													||
													( $u_obj->getTerminationDate() == '' && TTDate::getMiddleDayEpoch( $u_obj->getUpdatedDate() ) > ( TTDate::getMiddleDayEpoch( $start_date ) - ( 86400 * 30 ) ) ) //If user is terminated and no termination date is set, only recalculate if the user record has been updated in the last 30 days.
											)
									)
							) {
								TTLog::addEntry( $u_obj->getId(), 500, TTi18n::gettext( 'Recalculating Employee TimeSheet' ) . ': ' . $u_obj->getFullName() . ' ' . TTi18n::gettext( 'From' ) . ': ' . TTDate::getDate( 'DATE', $start_date ) . ' ' . TTi18n::gettext( 'To' ) . ': ' . TTDate::getDate( 'DATE', $end_date ), $this->getCurrentUserObject()->getId(), 'user_date_total' );

								$transaction_function = function () use ( $u_obj, $start_date, $end_date ) {
									$cp = TTNew( 'CalculatePolicy' ); /** @var CalculatePolicy $cp */
									$cp->setUserObject( $u_obj );
									$cp->getUserObject()->setTransactionMode( 'REPEATABLE READ' );
									$cp->addPendingCalculationDate( $start_date, $end_date );
									$cp->calculate(); //This sets timezone itself.
									$cp->Save();
									$cp->getUserObject()->setTransactionMode(); //Back to default isolation level.

									return true;
								};

								$u_obj->RetryTransaction( $transaction_function, 3, 3 ); //Set retry_sleep this fairly high so real-time punches have a chance to get saved between retries.
							}
//							else {
//								Debug::text('Skipping inactive or terminated user: '. $u_obj->getID() .' Status: '. $u_obj->getStatus() .' Termination Date: '. TTDate::getDate('DATE', $u_obj->getTerminationDate() ) .' Updated Date: '. TTDate::getDate('DATE', $u_obj->getUpdatedDate() ), __FILE__, __LINE__, __METHOD__, 10);
//							}

							$this->getProgressBarObject()->set( $this->getAPIMessageID(), $x );

							$x++;
						}

						$this->getProgressBarObject()->stop( $this->getAPIMessageID() );
					} else {
						Debug::text( 'No Users to calculate!', __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::text( 'Pay Period is CLOSED: ', __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
		}

		return $this->returnHandler( true );
	}

	/**
	 * Verify/Authorize timesheet
	 * @param integer $user_id       User ID of the timesheet that is being verified.
	 * @param integer $pay_period_id Pay Period ID of the timesheet that is being verified.
	 * @param bool $enable_authorize Create authorization record or not, should only be TRUE if called from the TimeSheet view and not from the TimeSheet Authorization View.
	 * @return array|bool
	 */
	function verifyTimeSheet( $user_id, $pay_period_id, $enable_authorize = true ) {
		if ( $user_id != '' && $pay_period_id != '' ) {
			Debug::text( 'Verifying Pay Period TimeSheet ', __FILE__, __LINE__, __METHOD__, 10 );

			$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
			/** @return array
			 * @var PayPeriodTimeSheetVerifyListFactory $pptsvlf
			 */

			$transaction_function = function () use ( $pptsvlf, $user_id, $pay_period_id, $enable_authorize ) {
				$pptsvlf->setTransactionMode( 'REPEATABLE READ' );
				$pptsvlf->StartTransaction();
				$pptsvlf->getByPayPeriodIdAndUserId( $pay_period_id, $user_id );
				if ( $pptsvlf->getRecordCount() == 0 ) {
					Debug::text( 'Timesheet NOT verified by employee yet.', __FILE__, __LINE__, __METHOD__, 10 );
					$pptsvf = TTnew( 'PayPeriodTimeSheetVerifyFactory' ); /** @var PayPeriodTimeSheetVerifyFactory $pptsvf */
				} else {
					Debug::text( 'Timesheet re-verified by employee, or superior...', __FILE__, __LINE__, __METHOD__, 10 );
					$pptsvf = $pptsvlf->getCurrent();
				}

				$pptsvf->setCurrentUser( $this->getCurrentUserObject()->getId() );
				$pptsvf->setUser( $user_id );
				$pptsvf->setPayPeriod( $pay_period_id );

				$pptsvf->setEnableAuthorize( $enable_authorize );

				if ( $pptsvf->isValid() ) {
					$pptsvf->Save( false );

					$retval = $this->returnHandler( $pptsvf->getId() );
				} else {
					$pptsvlf->FailTransaction();

					$retval = $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), $pptsvf->Validator->getErrorsArray(), [ 'total_records' => 1, 'valid_records' => 0 ] );
				}

				$pptsvlf->CommitTransaction();
				$pptsvlf->setTransactionMode();

				return [ $retval ];
			};

			list( $retval ) = $pptsvlf->RetryTransaction( $transaction_function, 3, 3 ); //Set retry_sleep this fairly high so real-time punches have a chance to get saved between retries.

			return $retval; //This is a returnHandler()
		}

		return $this->returnHandler( false );
	}

}

?>
