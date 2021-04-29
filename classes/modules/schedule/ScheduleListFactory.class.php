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
 * @package Modules\Schedule
 */
class ScheduleListFactory extends ScheduleFactory implements IteratorAggregate {

	/**
	 * @param int $limit   Limit the number of records returned
	 * @param int $page    Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return $this
	 */
	function getAll( $limit = null, $page = null, $where = null, $order = null ) {
		$query = '
					select	*
					from	' . $this->getTable() . '
					WHERE deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, null, $limit, $page );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|ScheduleListFactory
	 */
	function getById( $id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		$ph = [
				'id' => TTUUID::castUUID( $id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|ScheduleListFactory
	 */
	function getByCompanyID( $company_id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'a.start_time' => 'asc', 'a.status_id' => 'desc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$uf = new UserFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		//Status sorting MUST be desc first, otherwise transfer punches are completely out of order.
		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $uf->getTable() . ' as c ON ( a.user_id = c.id AND c.deleted = 0 )
					where	 a.company_id = ?
						AND  a.deleted = 0
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $id         UUID
	 * @param string $company_id UUID
	 * @return bool|ScheduleListFactory
	 */
	function getByIdAndCompanyId( $id, $company_id ) {
		return $this->getByCompanyIDAndId( $company_id, $id );
	}

	/**
	 * @param string $company_id UUID
	 * @param string $id         UUID
	 * @return bool|ScheduleListFactory
	 */
	function getByCompanyIDAndId( $company_id, $id ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( $id == '' ) {
			return false;
		}

		$uf = new UserFactory();

		$ph = [
				'company_id'  => TTUUID::castUUID( $company_id ),
				'company_id2' => TTUUID::castUUID( $company_id ),
		];

		//Status sorting MUST be desc first, otherwise transfer punches are completely out of order.
		//Always include the user_id, this is required for mass edit to function correctly and not assign schedules to OPEN employee all the time.
		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $uf->getTable() . ' as c ON ( a.user_id = c.id AND c.deleted = 0 )
					where	( c.company_id = ? OR a.company_id = ? )
						AND a.id in (' . $this->getListSQL( $id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 )
					ORDER BY a.start_time asc, a.status_id desc
					';

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $date_stamp EPOCH
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|ScheduleListFactory
	 */
	function getByUserIdAndDateStamp( $user_id, $date_stamp, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $date_stamp == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'start_time' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'user_id'    => TTUUID::castUUID( $user_id ),
				'date_stamp' => $this->db->BindDate( $date_stamp ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	user_id = ?
						AND date_stamp = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $date_stamp EPOCH
	 * @param int $status_id
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|ScheduleListFactory
	 */
	function getByUserIdAndDateStampAndStatus( $user_id, $date_stamp, $status_id, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $date_stamp == '' ) {
			return false;
		}

		if ( $status_id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'start_time' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'user_id'    => TTUUID::castUUID( $user_id ),
				'date_stamp' => $this->db->BindDate( $date_stamp ),
				'status_id'  => (int)$status_id,
		];

		$query = '
					select	*
					from	' . $this->getTable() . '
					where	user_id = ?
						AND date_stamp = ?
						AND status_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|ScheduleListFactory
	 */
	function getByUserIdAndStartDateAndEndDate( $user_id, $start_date, $end_date, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		$additional_order_fields = [ 'a.date_stamp' ];

		if ( $order == null ) {
			$order = [ 'a.date_stamp' => 'asc', 'a.status_id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'start_date' => $this->db->BindDate( $start_date ),
				'end_date'   => $this->db->BindDate( $end_date ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					where	a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND a.user_id in (' . $this->getListSQL( $user_id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id       UUID
	 * @param int $epoch            EPOCH
	 * @param int $week_start_epoch EPOCH
	 * @return bool|int
	 */
	function getWeekWorkTimeSumByUserIDAndEpochAndStartWeekEpoch( $user_id, $epoch, $week_start_epoch ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $epoch == '' ) {
			return false;
		}

		if ( $week_start_epoch == '' ) {
			return false;
		}

		$ph = [
				'user_id'          => TTUUID::castUUID( $user_id ),
				'week_start_epoch' => $this->db->BindDate( $week_start_epoch ),
				'epoch'            => $this->db->BindDate( $epoch ),
		];

		//DO NOT Include paid absences. Only count regular time towards weekly overtime.
		//And other weekly overtime polices!
		$query = '
					select	sum(a.total_time)
					from	' . $this->getTable() . ' as a
					where
						a.user_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp < ?
						AND a.status_id = 10
						AND a.deleted = 0
				';
		$total = $this->db->GetOne( $query, $ph );

		if ( $total === false ) {
			$total = 0;
		}
		Debug::text( 'Total: ' . $total, __FILE__, __LINE__, __METHOD__, 10 );

		return $total;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $type_id
	 * @param $direction
	 * @param int $date       EPOCH
	 * @param int $limit      Limit the number of records returned
	 * @param int $page       Page number of records to return for pagination
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|ScheduleListFactory
	 */
	function getByUserIdAndTypeAndDirectionFromDate( $user_id, $type_id, $direction, $date, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $type_id == '' ) {
			return false;
		}

		if ( $direction == '' ) {
			return false;
		}

		if ( $date == '' ) {
			return false;
		}

		if ( $order == null ) {
			$strict = false;

			$order = [ 'a.date_stamp' => 'asc' ];    //When direction is after, we need to get the days in the proper order (ASC)
			if ( strtolower( $direction ) == 'before' ) {
				$order = [ 'a.date_stamp' => 'desc' ]; //When direction is before, we need to get the days in the proper order (DESC)
				$direction = '<';
			} else if ( strtolower( $direction ) == 'after' ) {
				$direction = '>';
			} else {
				return false;
			}
		} else {
			$strict = true;
		}

		$ph = [
				'date'    => $this->db->BindDate( $date ),
				'type_id' => (int)$type_id,
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					where	a.date_stamp ' . $direction . ' ?
						AND a.status_id = ?
						AND a.user_id in (' . $this->getListSQL( $user_id, $ph, 'uuid' ) . ')
						AND ( a.deleted = 0 )
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		//Debug::Arr($ph, 'Query: '. $query .' Limit: '. $limit, __FILE__, __LINE__, __METHOD__, 10);
		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @param int $start_date    EPOCH
	 * @param int $end_date      EPOCH
	 * @param string $id         UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|ScheduleListFactory
	 */
	function getConflictingByCompanyIdAndUserIdAndStartDateAndEndDate( $company_id, $user_id, $start_date, $end_date, $id = null, $where = null, $order = null ) {
		Debug::Text( 'User ID: ' . $user_id . ' Start Date: ' . $start_date . ' End Date: ' . $end_date, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $company_id == '' ) {
			return false;
		}

		if ( $user_id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		if ( $id == '' ) {
			$id = TTUUID::getZeroId(); //Leaving this as NULL can cause the SQL query to not return rows when it should.
		}

		$start_timestamp = $this->db->BindTimeStamp( (int)$start_date );
		$end_timestamp = $this->db->BindTimeStamp( (int)$end_date );

		$ph = [
				'company_id'   => TTUUID::castUUID( $company_id ),
				'user_id'      => TTUUID::castUUID( $user_id ),
				'start_date_a' => $this->db->BindDate( ( $start_date - 86400 ) ), //Need to expand the date_stamp restriction by at least a day to cover shifts that span midnight.
				'end_date_b'   => $this->db->BindDate( ( $end_date + 86400 ) ), //Need to expand the date_stamp restriction by at least a day to cover shifts that span midnight.
				'id'           => TTUUID::castUUID( $id ),
				'start_date1'  => $start_timestamp,
				'end_date1'    => $end_timestamp,
				'start_date2'  => $start_timestamp,
				'end_date2'    => $end_timestamp,
				'start_date3'  => $start_timestamp,
				'end_date3'    => $end_timestamp,
				'start_date4'  => $start_timestamp,
				'end_date4'    => $end_timestamp,
				'start_date5'  => $start_timestamp,
				'end_date5'    => $end_timestamp,
		];

		//Add filter on date_stamp for optimization
		// Make sure we ignore any records that have been replaced by other records already.
		$query = '
					SELECT	a.*
					FROM	' . $this->getTable() . ' as a
					LEFT JOIN ' . $this->getTable() . ' as b ON ( a.id = b.replaced_id AND b.deleted = 0 )
					WHERE a.company_id = ?
						AND a.user_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND a.id != ?
						AND b.id IS NULL
						AND
						(
							( a.start_time >= ? AND a.end_time <= ? )
							OR
							( a.start_time >= ? AND a.start_time < ? )
							OR
							( a.end_time > ? AND a.end_time <= ? )
							OR
							( a.start_time <= ? AND a.end_time >= ? )
							OR
							( a.start_time = ? AND a.end_time = ? )
						)
						AND ( a.deleted = 0 )
					ORDER BY start_time, user_id, created_date';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $epoch      EPOCH
	 * @return bool|mixed
	 */
	function getScheduleObjectByUserIdAndEpoch( $user_id, $epoch ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $epoch == '' ) {
			return false;
		}

		//Need to handle schedules on next/previous dates from when the punch is.
		//ie: if the schedule started on 11:30PM on Jul 5th and the punch is 01:00AM on Jul 6th.
		//These two functions are almost identical: PunchFactory::findScheduleId() and ScheduleListFactory::getScheduleObjectByUserIdAndEpoch()
		$slf = new ScheduleListFactory();
		$slf->getByUserIdAndStartDateAndEndDate( $user_id, ( TTDate::getMiddleDayEpoch( $epoch ) - 86400 ), ( TTDate::getMiddleDayEpoch( $epoch ) + 86400 ), null, [ 'a.date_stamp' => 'asc', 'a.status_id' => 'asc', 'a.start_time' => 'desc' ] );
		if ( $slf->getRecordCount() > 0 ) {
			Debug::Text( ' Found User Date ID! User: ' . $user_id . ' Epoch: ' . TTDate::getDATE( 'DATE+TIME', $epoch ) . '(' . $epoch . ')', __FILE__, __LINE__, __METHOD__, 10 );
			$retval = false;
			$best_diff = false;
			//Check for schedule policy
			foreach ( $slf as $s_obj ) {
				Debug::text( ' Checking Schedule ID: ' . $s_obj->getID() . ' Start: ' . TTDate::getDate( 'DATE+TIME', $s_obj->getStartTime() ) . ' End: ' . TTDate::getDate( 'DATE+TIME', $s_obj->getEndTime() ), __FILE__, __LINE__, __METHOD__, 10 );

				//If the Start/Stop window is large (ie: 6-8hrs) we need to find the closest schedule.
				$schedule_diff = $s_obj->inScheduleDifference( $epoch );

				//If its an absent shift, weight the schedule difference to at least that of the start/stop window so we prefer working shifts by that are within that amount of time. Almost like a reverse start/stop window.
				//   See PunchFactory::getDefaultPunchSettings() for more comments on this.
				if ( $s_obj->getStatus() == 20 ) { //20=Absent
					$schedule_diff += $s_obj->getStartStopWindow();
				}

				if ( $schedule_diff === 0 ) {
					Debug::text( ' Within schedule times. ', __FILE__, __LINE__, __METHOD__, 10 );

					return $s_obj;
				} else {
					if ( $schedule_diff > 0 && ( $best_diff === false || $schedule_diff < $best_diff ) ) {
						Debug::text( ' Within schedule start/stop time by: ' . $schedule_diff . ' Prev Best Diff: ' . $best_diff, __FILE__, __LINE__, __METHOD__, 10 );
						$best_diff = $schedule_diff;
						$retval = $s_obj;
					}
				}
			}

			if ( isset( $retval ) && is_object( $retval ) ) {
				return $retval;
			}
		}

		return false;
	}

	/**
	 * Find all *committed* open shifts that conflict, so they can be entered in the replaced_id field.
	 * @param string $company_id    UUID
	 * @param $user_id
	 * @param $start_time
	 * @param $end_time
	 * @param string $branch_id     UUID
	 * @param string $department_id UUID
	 * @param string $job_id        UUID
	 * @param string $job_item_id   UUID
	 * @param int $replaced_id
	 * @param int $limit            Limit the number of records returned
	 * @param int $page             Page number of records to return for pagination
	 * @param array $where          Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order          Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|ScheduleListFactory
	 * @throws DBError
	 */
	function getConflictingOpenShiftSchedule( $company_id, $user_id, $start_time, $end_time, $branch_id, $department_id, $job_id, $job_item_id, $replaced_id = 0, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' || $start_time == '' || $end_time == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'a.created_date' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		Debug::Text( 'Getting conflicting Open Shifts...', __FILE__, __LINE__, __METHOD__, 10 );

		$uf = new UserFactory();
		$rsf = new RecurringScheduleFactory();

		$ph = [
				'user_id'       => TTUUID::castUUID( $user_id ),
				'company_id'    => TTUUID::castUUID( $company_id ),
				'open_user_id'  => TTUUID::getZeroID(), //Open Shift
				'start_time'    => $this->db->BindTimeStamp( (int)$start_time ),
				'end_time'      => $this->db->BindTimeStamp( (int)$end_time ),
				'branch_id'     => TTUUID::castUUID( $branch_id ),
				'department_id' => TTUUID::castUUID( $department_id ),
				'job_id'        => TTUUID::castUUID( $job_id ),
				'job_item_id'   => TTUUID::castUUID( $job_item_id ),
		];


		//Handle cases where the user edits/saves (no changes) a recurring schedule that is already filling an OPEN shift. The commit shift shouldn't also fill a commited OPEN shift too.
		//  We do this by joining to the recurring_schedule table and ensuring that the committed shift doesn't override a recurring schedule.
		$query = '
					SELECT	a.*
					FROM	' . $this->getTable() . ' as a
					LEFT JOIN ' . $this->getTable() . ' as b ON ( a.id = b.replaced_id AND b.deleted = 0 )
					LEFT JOIN ' . $uf->getTable() . ' as uf ON ( a.user_id = uf.id AND uf.deleted = 0 )
					LEFT JOIN ' . $rsf->getTable() . ' AS rsf ON (
						rsf.user_id = ?			
						AND a.date_stamp = rsf.date_stamp			 
						AND a.start_time = rsf.start_time
						AND a.end_Time = rsf.end_time
						AND rsf.deleted = 0
					)
					
					WHERE	( 	a.company_id = ?
								AND a.user_id = ?
								AND a.start_time = ?
								AND a.end_time = ?
								AND a.branch_id = ?
								AND a.department_id = ?
								AND a.job_id = ?
								AND a.job_item_id = ?
								AND ( a.replaced_id = \'' . TTUUID::getZeroID() . '\' AND b.replaced_id IS NULL )
								AND ( rsf.id IS NULL )
								AND a.deleted = 0
							)
					';

		if ( TTUUID::isUUID( $replaced_id ) && $replaced_id != TTUUID::getZeroID() && $replaced_id != TTUUID::getNotExistID() ) {
			//Make sure when passed a $replaced_id, we also make sure that record still matches all necessary items to fill the original open shift.
			$ph += [
					'user_id2'       => TTUUID::getZeroID(), //Open Shift
					'start_time2'    => $this->db->BindTimeStamp( (int)$start_time ),
					'end_time2'      => $this->db->BindTimeStamp( (int)$end_time ),
					'branch_id2'     => TTUUID::castUUID( $branch_id ),
					'department_id2' => TTUUID::castUUID( $department_id ),
					'job_id2'        => TTUUID::castUUID( $job_id ),
					'job_item_id2'   => TTUUID::castUUID( $job_item_id ),
			];

			$query .= ' OR ( a.id = \'' . TTUUID::castUUID( $replaced_id ) . '\' AND a.user_id = ? AND a.start_time = ? AND a.end_time = ? AND a.branch_id = ? AND a.department_id = ? AND a.job_id = ? AND a.job_item_id = ? AND a.deleted = 0 ) ';
			$order = ( [ 'a.id' => ' = \'' . TTUUID::castUUID( $replaced_id ) . '\' desc' ] + $order );
		}

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );
		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

	/**
	 * @param $company_id
	 * @param $filter_data
	 * @param null $limit
	 * @param null $page
	 * @param null $where
	 * @param null $order
	 * @return array|bool
	 * @throws ReflectionException
	 */
	function getOverriddenOpenShiftRecurringSchedules( $company_id, $filter_data, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		//Must always force the same order as thats critical to this function working.
		$order = [ 'layer_order' => 'asc', 'a.user_id' => 'asc', 'a.start_time' => 'asc' ];
		$strict = false;

		//if ( $order == null ) {
		//	$order = [ 'layer_order' => 'asc', 'uf.last_name' => 'asc', 'a.start_time' => 'asc' ];
		//	$strict = false;
		//} else {
		//	$strict = true;
		//}

		Debug::Text( 'Getting overrriden Open Shifts...', __FILE__, __LINE__, __METHOD__, 10 );

		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		if ( isset( $filter_data['pay_period_ids'] ) ) {
			$filter_data['pay_period_id'] = $filter_data['pay_period_ids'];
		}

		if ( isset( $filter_data['user_status_ids'] ) ) {
			$filter_data['user_status_id'] = $filter_data['user_status_ids'];
		}
		if ( isset( $filter_data['user_title_ids'] ) ) {
			$filter_data['title_id'] = $filter_data['user_title_ids'];
		}
		if ( isset( $filter_data['group_ids'] ) ) {
			$filter_data['group_id'] = $filter_data['group_ids'];
		}
		if ( isset( $filter_data['default_branch_ids'] ) ) {
			$filter_data['default_branch_id'] = $filter_data['default_branch_ids'];
		}
		if ( isset( $filter_data['default_department_ids'] ) ) {
			$filter_data['default_department_id'] = $filter_data['default_department_ids'];
		}
		if ( isset( $filter_data['status_ids'] ) ) {
			$filter_data['status_id'] = $filter_data['status_ids'];
		}
		if ( isset( $filter_data['branch_ids'] ) ) {
			$filter_data['schedule_branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset( $filter_data['department_ids'] ) ) {
			$filter_data['schedule_department_id'] = $filter_data['department_ids'];
		}
		if ( isset( $filter_data['schedule_branch_ids'] ) ) {
			$filter_data['schedule_branch_id'] = $filter_data['schedule_branch_ids'];
		}
		if ( isset( $filter_data['schedule_department_ids'] ) ) {
			$filter_data['schedule_department_id'] = $filter_data['schedule_department_ids'];
		}

		if ( isset( $filter_data['exclude_job_ids'] ) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_job_ids'];
		}
		if ( isset( $filter_data['include_job_ids'] ) ) {
			$filter_data['include_job_id'] = $filter_data['include_job_ids'];
		}
		if ( isset( $filter_data['job_group_ids'] ) ) {
			$filter_data['job_group_id'] = $filter_data['job_group_ids'];
		}
		if ( isset( $filter_data['job_item_ids'] ) ) {
			$filter_data['job_item_id'] = $filter_data['job_item_ids'];
		}

		//Since these are OPEN shifts, none of them are assigned to pay periods. So we need to convert filtered Pay Period IDs to date ranges instead.
		if ( isset( $filter_data['pay_period_id'] ) && !isset( $filter_data['start_date'] ) && !isset( $filter_data['end_date'] ) ) {
			$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
			$pay_period_dates = $pplf->getStartAndEndDateRangeFromCompanyIdAndPayPeriodId( $company_id, $filter_data['pay_period_id'] );
			if ( is_array( $pay_period_dates ) ) {
				$filter_data['start_date'] = $pay_period_dates['start_date'];
				$filter_data['end_date'] = $pay_period_dates['end_date'];
				Debug::Arr( $filter_data['pay_period_id'], '  Converted Pay Period IDs to Dates: Start: ' . TTDate::getDate( 'DATE+TIME', $pay_period_dates['start_date'] ) . ' End: ' . TTDate::getDate( 'DATE+TIME', $pay_period_dates['end_date'] ), __FILE__, __LINE__, __METHOD__, 10 );
				unset( $filter_data['pay_period_id'] );
			}

			unset( $pplf, $pay_period_dates );
		}

		$uf = new UserFactory();
		$apf = new AbsencePolicyFactory();
		$sf = new ScheduleFactory();
		$rsf = new RecurringScheduleFactory();
		$rscf = new RecurringScheduleControlFactory();

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jf = new JobFactory();
			$jif = new JobItemFactory();
		}

		$ph['company_id1'] = TTUUID::castUUID( $company_id );

		//Check for committed OPEN schedules that override open recurring schedules.
		//  Check against replaced_id to ensure we ignore cases where 1 of 2 recurring OPEN shifts are overridden by a committed OPEN shift (basically an edit/save without any changes),
		//  then the remaining recurring OPEN shift is filled by an employee by editing the OPEN shift, changing the employee, and saving.
		//  Essentially we are two levels deep of overrides here, so there should still be one OPEN shift displayed in this case.
		$query = '
					SELECT
						a.id as id,
						a.company_id as company_id,
						a.user_id as user_id,
						a.status_id as status_id,
						a.date_stamp as date_stamp,
						a.start_time as start_time,
						a.end_time as end_time,
						
						a.branch_id as branch_id,
						a.department_id as department_id,
						a.job_id as job_id,
						a.job_item_id as job_item_id,
						
						uf.default_branch_id as default_branch_id,
						uf.default_department_id as default_department_id, 
						uf.default_job_id as default_job_id,
						uf.default_job_item_id as default_job_item_id,
												
						a.total_time as total_time,
						a.schedule_policy_id as schedule_policy_id,
						a.absence_policy_id as absence_policy_id,
						a.deleted as deleted,
						a.layer_order as layer_order					
					FROM
						(
								SELECT
										rsf_b.id as id,
										rsf_b.company_id as company_id,
										rsf_b.user_id as user_id,
										rsf_b.status_id as status_id,
										rsf_b.date_stamp as date_stamp,
										rsf_b.start_time as start_time,
										rsf_b.end_time as end_time,

										rsf_b.branch_id as branch_id,
										rsf_b.department_id as department_id,
										rsf_b.job_id as job_id,
										rsf_b.job_item_id as job_item_id,
										rsf_b.total_time as total_time,
										rsf_b.schedule_policy_id as schedule_policy_id,
										rsf_b.absence_policy_id as absence_policy_id,
										rsf_b.deleted as deleted,
										CASE WHEN rsf_b.user_id = \'' . TTUUID::getZeroID() . '\' THEN 8 ELSE 1 END as layer_order
								FROM ' . $rsf->getTable() . ' as rsf_b
								LEFT JOIN ' . $rscf->getTable() . ' as rscf_b ON ( rsf_b.recurring_schedule_control_id = rscf_b.id )
								
								WHERE rsf_b.company_id = ?
								';

		$query .= ( isset( $filter_data['start_date'] ) ) ? $this->getWhereClauseSQL( 'rsf_b.start_time', $filter_data['start_date'], 'start_timestamp', $ph ) : null;
		$query .= ( isset( $filter_data['end_date'] ) ) ? $this->getWhereClauseSQL( 'rsf_b.start_time', $filter_data['end_date'], 'end_timestamp', $ph ) : null;

		$query .= '		
									AND ( rsf_b.deleted = 0 AND rscf_b.deleted = 0 )

							UNION ALL

								SELECT
										sf_b.id as id,
										sf_b.company_id as company_id,
										sf_b.user_id as user_id,
										sf_b.status_id as status_id,
										sf_b.date_stamp as date_stamp,
										sf_b.start_time as start_time,
										sf_b.end_time as end_time,
			
										sf_b.branch_id as branch_id,
										sf_b.department_id as department_id,
										sf_b.job_id as job_id,
										sf_b.job_item_id as job_item_id,
										sf_b.total_time as total_time,
										sf_b.schedule_policy_id as schedule_policy_id,
										sf_b.absence_policy_id as absence_policy_id,
										sf_b.deleted as deleted,
										CASE WHEN sf_b.user_id = \'' . TTUUID::getZeroID() . '\' THEN 9 ELSE 2 END as layer_order
								FROM ' . $sf->getTable() . ' as sf_b
								WHERE sf_b.company_id = ?
								';

		$ph['company_id2'] = TTUUID::castUUID( $company_id );

		$query .= ( isset( $filter_data['start_date'] ) ) ? $this->getWhereClauseSQL( 'sf_b.start_time', $filter_data['start_date'], 'start_timestamp', $ph ) : null;
		$query .= ( isset( $filter_data['end_date'] ) ) ? $this->getWhereClauseSQL( 'sf_b.start_time', $filter_data['end_date'], 'end_timestamp', $ph ) : null;

		$query .= '
								
									AND ( sf_b.deleted = 0 )
						) as a
					LEFT JOIN ' . $uf->getTable() . ' as uf ON a.user_id = uf.id
					LEFT JOIN ' . $apf->getTable() . ' as apf ON a.absence_policy_id = apf.id
					';
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= '	LEFT JOIN ' . $jf->getTable() . ' as x ON a.job_id = x.id';
			$query .= '	LEFT JOIN ' . $jif->getTable() . ' as y ON a.job_item_id = y.id';
		}

		$ph['company_id3'] = TTUUID::castUUID( $company_id );
		$query .= '	WHERE a.company_id = ? ';

		$query .= ( isset( $filter_data['schedule_branch_id'] ) ) ? $this->getWhereClauseSQL( 'a.branch_id', $filter_data['schedule_branch_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['schedule_department_id'] ) ) ? $this->getWhereClauseSQL( 'a.department_id', $filter_data['schedule_department_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['schedule_policy_id'] ) ) ? $this->getWhereClauseSQL( 'a.schedule_policy_id', $filter_data['schedule_policy_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['absence_policy_id'] ) ) ? $this->getWhereClauseSQL( 'a.absence_policy_id', $filter_data['absence_policy_id'], 'uuid_list', $ph ) : null;

		//$query .= ( isset($filter_data['pay_period_id']) ) ? $this->getWhereClauseSQL( 'a.pay_period_id', $filter_data['pay_period_id'], 'uuid_list', $ph ) : NULL;

		$query .= ( isset( $filter_data['user_status_id'] ) ) ? $this->getWhereClauseSQL( 'uf.status_id', $filter_data['user_status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['group_id'] ) ) ? $this->getWhereClauseSQL( 'uf.group_id', $filter_data['group_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_branch_id'] ) ) ? $this->getWhereClauseSQL( 'uf.default_branch_id', $filter_data['default_branch_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_department_id'] ) ) ? $this->getWhereClauseSQL( 'uf.default_department_id', $filter_data['default_department_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['title_id'] ) ) ? $this->getWhereClauseSQL( 'uf.title_id', $filter_data['title_id'], 'uuid_list', $ph ) : null;

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= ( isset( $filter_data['job_id'] ) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['job_id'], 'uuid_list', $ph ) : null;
			$query .= ( isset( $filter_data['job_status_id'] ) ) ? $this->getWhereClauseSQL( 'x.status_id', $filter_data['job_status_id'], 'numeric_list', $ph ) : null;
			$query .= ( isset( $filter_data['include_job_id'] ) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['include_job_id'], 'uuid_list', $ph ) : null;
			$query .= ( isset( $filter_data['exclude_job_id'] ) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['exclude_job_id'], 'not_uuid_list', $ph ) : null;
			$query .= ( isset( $filter_data['job_group_id'] ) ) ? $this->getWhereClauseSQL( 'x.group_id', $filter_data['job_group_id'], 'uuid_list', $ph ) : null;
			$query .= ( isset( $filter_data['job_item_id'] ) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['job_item_id'], 'uuid_list', $ph ) : null;
		}

		//These aren't needed here, asn they are filtered in the UNION SELECTs above. This seems to slow things down substantially in some cases.
		//$query .= ( isset($filter_data['start_date']) ) ? $this->getWhereClauseSQL( 'a.start_time', $filter_data['start_date'], 'start_timestamp', $ph ) : NULL;
		//$query .= ( isset($filter_data['end_date']) ) ? $this->getWhereClauseSQL( 'a.start_time', $filter_data['end_date'], 'end_timestamp', $ph ) : NULL;

		$query .= '
						AND ( a.deleted = 0 AND ( uf.deleted IS NULL OR uf.deleted = 0 ) )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );


		$rows = $this->db->GetAll( $query, $ph );
		Debug::Text( '  Shifts to process: '. count($rows), __FILE__, __LINE__, __METHOD__, 10 );
		//Debug::Query($query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		if ( is_array( $rows ) ) {
			$i = 0;
			$retarr = [];
			$recurring_schedules = [];
			foreach( $rows as $key => $row ) {
				$row_iso_date_stamp = TTDate::getISODateStamp( strtotime( $row['start_time'] ) );

				//Debug::Text( 'Row: ' . $row['id'] . '['. $row['layer_order'] .'] ( User: '. $row['user_id'] .' Start: ' . TTDate::getDate( 'DATE+TIME', $row['start_time'] ) . ' End: ' . TTDate::getDate( 'DATE+TIME', $row['end_time'] ) . ')', __FILE__, __LINE__, __METHOD__, 10 );

				if ( $row['layer_order'] == 1 ) { //Recurring shifts
					$recurring_schedules[ $row['user_id'] ][$row_iso_date_stamp][] = $row;
				} else if ( $row['layer_order'] == 2 ) { //Override shifts
					if ( isset($recurring_schedules[ $row['user_id'] ]) ) {
						foreach ( $recurring_schedules[$row['user_id']] as $rs_user_date => $rs_user_date_rows ) {
							foreach ( $rs_user_date_rows as $rs_key => $rs_row ) {
								//Committed shifts overlap recurring shifts in any way whatsoever.
								if ( $row['user_id'] == $rs_row['user_id']
										&& $row['user_id'] != TTUUID::getZeroID()
										&& TTDate::isTimeOverLap( strtotime( $row['start_time'] ), strtotime( $row['end_time'] ), strtotime( $rs_row['start_time'] ), strtotime( $rs_row['end_time'] ) )
										&& ( $row['branch_id'] == $rs_row['branch_id'] || ( $rs_row['branch_id'] == TTUUID::getNotExistID() && ( $row['branch_id'] == TTUUID::getZeroID() || $rs_row['default_branch_id'] == $row['default_branch_id'] ) ) || ( $row['branch_id'] == TTUUID::getNotExistID() && $rs_row['branch_id'] == $row['default_branch_id'] ) )
										&& ( $row['department_id'] == $rs_row['department_id'] || ( $rs_row['department_id'] == TTUUID::getNotExistID() && ( $row['department_id'] == TTUUID::getZeroID() || $rs_row['default_department_id'] == $row['default_department_id'] ) ) || ( $row['department_id'] == TTUUID::getNotExistID() && $rs_row['department_id'] == $row['default_department_id'] ) )
										&& ( $row['job_id'] == $rs_row['job_id'] || ( $rs_row['job_id'] == TTUUID::getNotExistID() && ( $row['job_id'] == TTUUID::getZeroID() || $rs_row['default_job_id'] == $row['default_job_id'] ) ) || ( $row['job_id'] == TTUUID::getNotExistID() && $rs_row['job_id'] == $row['default_job_id'] ) )
										&& ( $row['job_item_id'] == $rs_row['job_item_id'] || ( $rs_row['job_item_id'] == TTUUID::getNotExistID() && ( $row['job_item_id'] == TTUUID::getZeroID() || $rs_row['default_job_item_id'] == $row['default_job_item_id'] ) ) || ( $row['job_item_id'] == TTUUID::getNotExistID() && $rs_row['job_item_id'] == $row['default_job_item_id'] ) )
								) {
									//Debug::Text( '  Committed Shift overrides Recurring Shift: ' . $row['id'] . '[' . $row['layer_order'] . '] ( Start: ' . TTDate::getDate( 'DATE+TIME', $row['start_time'] ) . ' End: ' . TTDate::getDate( 'DATE+TIME', $row['end_time'] ) . ') That overlaps recurring shift: ' . $rs_row['id'] . ' ( Start: ' . TTDate::getDate( 'DATE+TIME', $rs_row['start_time'] ) . ' End: ' . TTDate::getDate( 'DATE+TIME', $rs_row['end_time'] ) . ')', __FILE__, __LINE__, __METHOD__, 10 );
									$recurring_schedules[$row['user_id']][$row_iso_date_stamp][$rs_key] = $row;
									continue 3;
								}

								$i++;
							}
						}
					}

					//No override found, because "continue 2" didn't trigger above, keep this shift.
					//Debug::Text( '   Committed Shift DOES NOT override Recurring Shift: ' . $row['id'] . '['. $row['layer_order'] .'] ( Start: ' . TTDate::getDate( 'DATE+TIME', $row['start_time'] ) . ' End: ' . TTDate::getDate( 'DATE+TIME', $row['end_time'] ) . ')', __FILE__, __LINE__, __METHOD__, 10 );
					$recurring_schedules[$row['user_id']][$row_iso_date_stamp][] = $row;
				} else if ( $row['layer_order'] == 8 ) { //OPEN recurring shifts
					foreach( $recurring_schedules as $rs_user_id => $rs_user_date_rows ) {
						if ( isset($rs_user_date_rows[$row_iso_date_stamp]) ) {
							foreach ( $rs_user_date_rows[$row_iso_date_stamp] as $rs_key => $rs_row ) {
								//All shifts overlapping OPEN shifts must match exactly
								if ( !isset( $rs_row['override'] )
										&& $row['user_id'] == TTUUID::getZeroID()
										&& $row['status_id'] == 10 && $rs_row['status_id'] == 10 //Absence shifts can never fill OPEN shifts. If a working shift that is filling an open shift changes to absence, the open shift should be unfilled.
										&& $row['start_time'] == $rs_row['start_time'] && $row['end_time'] == $rs_row['end_time']
										&& ( ( $row['branch_id'] != TTUUID::getNotExistID() && $row['branch_id'] == $rs_row['branch_id'] ) || ( $row['branch_id'] == TTUUID::getNotExistID() && ( $row['branch_id'] == $rs_row['branch_id'] || $rs_row['branch_id'] == TTUUID::getZeroID() || ( $rs_row['branch_id'] == TTUUID::getNotExistID() && ( $rs_row['default_branch_id'] == TTUUID::getZeroID() ) ) || $rs_row['branch_id'] == $rs_row['default_branch_id'] ) ) || ( $rs_row['branch_id'] == TTUUID::getNotExistID() && $row['branch_id'] == $rs_row['default_branch_id'] ) )
										&& ( ( $row['department_id'] != TTUUID::getNotExistID() && $row['department_id'] == $rs_row['department_id'] ) || ( $row['department_id'] == TTUUID::getNotExistID() && ( $row['department_id'] == $rs_row['department_id'] || $rs_row['department_id'] == TTUUID::getZeroID() || ( $rs_row['department_id'] == TTUUID::getNotExistID() && ( $rs_row['default_department_id'] == TTUUID::getZeroID() ) ) || $rs_row['department_id'] == $rs_row['default_department_id'] ) ) || ( $rs_row['department_id'] == TTUUID::getNotExistID() && $row['department_id'] == $rs_row['default_department_id'] ) )
										&& ( ( $row['job_id'] != TTUUID::getNotExistID() && $row['job_id'] == $rs_row['job_id'] ) || ( $row['job_id'] == TTUUID::getNotExistID() && ( $row['job_id'] == $rs_row['job_id'] || $rs_row['job_id'] == TTUUID::getZeroID() || ( $rs_row['job_id'] == TTUUID::getNotExistID() && ( $rs_row['default_job_id'] == TTUUID::getZeroID() ) ) || $rs_row['job_id'] == $rs_row['default_job_id'] ) ) || ( $rs_row['job_id'] == TTUUID::getNotExistID() && $row['job_id'] == $rs_row['default_job_id'] ) )
										&& ( ( $row['job_item_id'] != TTUUID::getNotExistID() && $row['job_item_id'] == $rs_row['job_item_id'] ) || ( $row['job_item_id'] == TTUUID::getNotExistID() && ( $row['job_item_id'] == $rs_row['job_item_id'] || $rs_row['job_item_id'] == TTUUID::getZeroID() || ( $rs_row['job_item_id'] == TTUUID::getNotExistID() && ( $rs_row['default_job_item_id'] == TTUUID::getZeroID() ) ) || $rs_row['job_item_id'] == $rs_row['default_job_item_id'] ) ) || ( $rs_row['job_item_id'] == TTUUID::getNotExistID() && $row['job_item_id'] == $rs_row['default_job_item_id'] ) )
								) {
									//Debug::Text( '  Recurring OR Committed Shift overrides OPEN shift: ' . $row['id'] . '[' . $row['layer_order'] . '] ( Start: ' . TTDate::getDate( 'DATE+TIME', $row['start_time'] ) . ' End: ' . TTDate::getDate( 'DATE+TIME', $row['end_time'] ) . ') That overlaps recurring shift: ' . $rs_row['id'] . ' ( Start: ' . TTDate::getDate( 'DATE+TIME', $rs_row['start_time'] ) . ' End: ' . TTDate::getDate( 'DATE+TIME', $rs_row['end_time'] ) . ')', __FILE__, __LINE__, __METHOD__, 10 );
									unset( $recurring_schedules[$rs_user_id][$row_iso_date_stamp][$rs_key] );
									$retarr[] = $row['id']; //Exclude $row in this case.
									continue 3;             //Move to next $row in outer loop
								}

								$i++;
							}
						}
					}

					//No override found, because "continue 3" didn't trigger above, keep this shift.
					//Debug::Text( '    No override found, keep OPEN recurring Shift: ' . $row['id'] . '['. $row['layer_order'] .'] ( Start: ' . TTDate::getDate( 'DATE+TIME', $row['start_time'] ) . ' End: ' . TTDate::getDate( 'DATE+TIME', $row['end_time'] ) . ')', __FILE__, __LINE__, __METHOD__, 10 );
					$recurring_schedules[$row['user_id']][$row_iso_date_stamp][] = array_merge( $row, [ 'override' => 1 ] );
				} else if ( $row['layer_order'] == 9 ) { //OPEN committed shifts
					foreach( $recurring_schedules as $rs_user_id => $rs_user_date_rows ) {
						if ( isset($rs_user_date_rows[$row_iso_date_stamp]) ) {
							foreach ( $rs_user_date_rows[$row_iso_date_stamp] as $rs_key => $rs_row ) {
								//All shifts overlapping OPEN shifts must match exactly
								if ( $row['user_id'] == $rs_row['user_id']
										&& $row['start_time'] == $rs_row['start_time'] && $row['end_time'] == $rs_row['end_time']
										&& ( $row['branch_id'] == $rs_row['branch_id'] || ( $rs_row['branch_id'] == TTUUID::getNotExistID() && $row['branch_id'] == TTUUID::getZeroID() ) )
										&& ( $row['department_id'] == $rs_row['department_id'] || ( $rs_row['department_id'] == TTUUID::getNotExistID() && $row['department_id'] == TTUUID::getZeroID() ) )
										&& ( $row['job_id'] == $rs_row['job_id'] || ( $rs_row['job_id'] == TTUUID::getNotExistID() && $row['job_id'] == TTUUID::getZeroID() ) )
										&& ( $row['job_item_id'] == $rs_row['job_item_id'] || ( $rs_row['job_item_id'] == TTUUID::getNotExistID() && $row['job_item_id'] == TTUUID::getZeroID() ) )
								) {
									//Debug::Text( '  OPEN Committed Shift overrides OPEN shift: ' . $row['id'] . '[' . $row['layer_order'] . '] ( Start: ' . TTDate::getDate( 'DATE+TIME', $row['start_time'] ) . ' End: ' . TTDate::getDate( 'DATE+TIME', $row['end_time'] ) . ') That overlaps recurring shift: ' . $rs_row['id'] . ' ( Start: ' . TTDate::getDate( 'DATE+TIME', $rs_row['start_time'] ) . ' End: ' . TTDate::getDate( 'DATE+TIME', $rs_row['end_time'] ) . ')', __FILE__, __LINE__, __METHOD__, 10 );
									unset( $recurring_schedules[$rs_user_id][$row_iso_date_stamp][$rs_key] );
									$retarr[] = $rs_row['id']; //Exclude $rs_row (recurring) in this case.
									continue 3;
								}

								$i++;
							}
						}
					}
				}
			}

			Debug::Text( 'Total Loops: ' . $i, __FILE__, __LINE__, __METHOD__, 10 );

			$retarr = array_unique( $retarr );
			unset($recurring_schedules, $rs_user_rows, $row_row, $rs_user_id, $row);
		}
		unset($rows);

		if ( isset( $retarr ) ) {
			//Debug::Arr($retarr, 'Excluded Recurring OPEN shifts: ', __FILE__, __LINE__, __METHOD__, 10);
			Debug::Text( 'Excluded Recurring OPEN shifts: ' . count( $retarr ), __FILE__, __LINE__, __METHOD__, 10 );

			return $retarr;
		}

		Debug::Text( 'NO Excluded Recurring OPEN shifts...', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $filter_data
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|ScheduleListFactory
	 */
	function getSearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		$exclude_recurring_schedule_ids = $this->getOverriddenOpenShiftRecurringSchedules( $company_id, $filter_data );

		if ( !is_array( $order ) ) {
			//Use Filter Data ordering if its set.
			if ( isset( $filter_data['sort_column'] ) && $filter_data['sort_order'] ) {
				$order = [ Misc::trimSortPrefix( $filter_data['sort_column'] ) => $filter_data['sort_order'] ];
			}
		}

		$additional_order_fields = [ 'pay_period_id', 'user_id', 'last_name' ];

		$sort_column_aliases = [
				'pay_period' => 'udf.pay_period',
				'user_id'    => 'udf.user_id',
				'status_id'  => 'a.status_id',
				'last_name'  => 'uf.last_name',
				'first_name' => 'uf.first_name',
		];

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) { //Needed for unit tests to pass when doing pure edition tests.
			$additional_order_fields = array_merge( [ 'jf.name', 'jif.name' ], $additional_order_fields );

			$sort_column_aliases = array_merge( [
														'job'      => 'jf.name',
														'job_item' => 'jif.name',
												], $sort_column_aliases );
		}

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == null ) {
			//$order = array( 'udf.pay_period_id' => 'asc', 'udf.user_id' => 'asc', 'a.start_time' => 'asc' );
			//Sort by start_time first, then user, so when only showing 1st page, it has all employees working on the first day, not just some of the employees for multiple days.
			$order = [ 'a.user_id' => '= \'' . TTUUID::getZeroID() . '\' desc', 'a.start_time' => 'asc', 'uf.last_name' => 'asc', 'a.recurring_schedule_id' => 'asc', 'a.id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		if ( isset( $filter_data['exclude_user_ids'] ) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_ids'];
		}
		if ( isset( $filter_data['include_user_ids'] ) ) {
			$filter_data['id'] = $filter_data['include_user_ids'];
		}
		if ( isset( $filter_data['include_user_id'] ) ) {
			$filter_data['id'] = $filter_data['include_user_id'];
		}

		if ( isset( $filter_data['pay_period_ids'] ) ) {
			$filter_data['pay_period_id'] = $filter_data['pay_period_ids'];
		}

		if ( isset( $filter_data['user_status_ids'] ) ) {
			$filter_data['user_status_id'] = $filter_data['user_status_ids'];
		}
		if ( isset( $filter_data['user_title_ids'] ) ) {
			$filter_data['title_id'] = $filter_data['user_title_ids'];
		}
		if ( isset( $filter_data['group_ids'] ) ) {
			$filter_data['group_id'] = $filter_data['group_ids'];
		}
		if ( isset( $filter_data['default_branch_ids'] ) ) {
			$filter_data['default_branch_id'] = $filter_data['default_branch_ids'];
		}
		if ( isset( $filter_data['default_department_ids'] ) ) {
			$filter_data['default_department_id'] = $filter_data['default_department_ids'];
		}
		if ( isset( $filter_data['status_ids'] ) ) {
			$filter_data['status_id'] = $filter_data['status_ids'];
		}
		if ( isset( $filter_data['branch_ids'] ) ) {
			$filter_data['schedule_branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset( $filter_data['department_ids'] ) ) {
			$filter_data['schedule_department_id'] = $filter_data['department_ids'];
		}
		if ( isset( $filter_data['schedule_branch_ids'] ) ) {
			$filter_data['schedule_branch_id'] = $filter_data['schedule_branch_ids'];
		}
		if ( isset( $filter_data['schedule_department_ids'] ) ) {
			$filter_data['schedule_department_id'] = $filter_data['schedule_department_ids'];
		}

		if ( isset( $filter_data['exclude_job_ids'] ) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_job_ids'];
		}
		if ( isset( $filter_data['include_job_ids'] ) ) {
			$filter_data['include_job_id'] = $filter_data['include_job_ids'];
		}
		if ( isset( $filter_data['job_group_ids'] ) ) {
			$filter_data['job_group_id'] = $filter_data['job_group_ids'];
		}
		if ( isset( $filter_data['job_item_ids'] ) ) {
			$filter_data['job_item_id'] = $filter_data['job_item_ids'];
		}

		$uf = new UserFactory();
		$uwf = new UserWageFactory();
		$apf = new AbsencePolicyFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$sf = new ScheduleFactory();
		$rsf = new RecurringScheduleFactory();
		$rscf = new RecurringScheduleControlFactory();

		//When filtering by pay_period_id (ie: this pay period), if their are recurring scheduled shifts in the future but before the end of this pay period,
		// they won't be displayed on the report because recurring_schedule records are not assigned to pay_period_id, since they are mostly in the future.
		// therefore we need to join to the pay_period table to figure out which pay_period the future dates belong too.
		// This isn't perfect, and will really only be useful for one pay period in the future, and doesn't handle PP schedule changes very well, but it should suffice for now.
		$ppsuf = new PayPeriodScheduleUserFactory();
		$ppsf = new PayPeriodScheduleFactory();
		$ppf = new PayPeriodFactory();

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jf = new JobFactory();
			$jgf = new JobGroupFactory();
			$jif = new JobItemFactory();
			$jigf = new JobItemGroupFactory();
		}

		$ph = [];

		$query = '
					select
							a.id as id,
							a.id as schedule_id,
							a.recurring_schedule_id as recurring_schedule_id,
							a.replaced_id as replaced_id,
							a.status_id as status_id,
							a.start_time as start_time,
							a.end_time as end_time,

							a.branch_id as branch_id,
							bfb.name as branch,
							a.department_id as department_id,
							dfb.name as department,
							a.job_id as job_id,
							a.job_item_id as job_item_id,
							a.total_time as total_time,
							a.schedule_policy_id as schedule_policy_id,
							a.absence_policy_id as absence_policy_id,
							apf.name as absence_policy,
							apf.type_id as absence_policy_type_id,

							a.note as note,

							a.created_date as created_date,
							a.updated_date as updated_date,

							bf.name as default_branch,
							df.name as default_department,
							ugf.name as "group",
							utf.name as title,

							a.user_id as user_id,
							a.date_stamp as date_stamp,
							a.pay_period_id as pay_period_id,
							ppf.start_date as pay_period_start_date,
							ppf.end_date as pay_period_end_date,
							ppf.transaction_date as pay_period_transaction_date,

							uf.first_name as first_name,
							uf.last_name as last_name,
							uf.default_branch_id as default_branch_id,
							uf.default_department_id as default_department_id,
							uf.title_id as title_id,
							uf.group_id as group_id,
							uf.created_by as user_created_by,

							uwf.id as user_wage_id,
							uwf.hourly_rate as user_wage_hourly_rate,
							uwf.labor_burden_percent as user_labor_burden_percent,
							uwf.effective_date as user_wage_effective_date, ';

		$query .= Permission::getPermissionIsChildIsOwnerSQL( ( isset( $filter_data['permission_current_user_id'] ) ) ? $filter_data['permission_current_user_id'] : TTUUID::getZeroID(), 'a.user_id', false, ( isset( $filter_data['permission_is_id'] ) && $filter_data['permission_is_id'] == TTUUID::getZeroID() ) ? $filter_data['permission_is_id'] : null );

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= ',
							jfb.name as default_job,
							jifb.name as default_job_item,

							jf.name as job,
							jf.description as job_description,
							jf.status_id as job_status_id,
							jf.manual_id as job_manual_id,
							jf.branch_id as job_branch_id,
							jbf.name as job_branch,
							jf.department_id as job_department_id,
							jdf.name as job_department,
							jf.group_id as job_group_id,
							jgf.name as job_group,
							jf.other_id1 as job_other_id1,
							jf.other_id2 as job_other_id2,
							jf.other_id3 as job_other_id3,
							jf.other_id4 as job_other_id4,
							jf.other_id5 as job_other_id5,
							jf.address1 as job_address1,
							jf.address2 as job_address2,
							jf.city as job_city,
							jf.country as job_country,
							jf.province as job_province,
							jf.postal_code as job_postal_code,
							jf.longitude as job_longitude,
							jf.latitude as job_latitude,
							jf.location_note as job_location_note,
							jif.name as job_item,
							jif.description as job_item_description,
							jif.manual_id as job_item_manual_id,
							jif.group_id as job_item_group_id,
							jigf.name as job_item_group
							';
		}

		$query .= '
					FROM (
							(
								SELECT
									sf.id as id,
									sf.id as schedule_id,
									sf.replaced_id as replaced_id,
									NULL as recurring_schedule_id,
									sf.company_id as company_id,

									sf.user_id as user_id,
									sf.recurring_schedule_template_control_id,
									sf.date_stamp as date_stamp,
									sf.pay_period_id as pay_period_id,

									sf.status_id as status_id,
									sf.start_time as start_time,
									sf.end_time as end_time,

									sf.branch_id as branch_id,
									sf.department_id as department_id,
									sf.job_id as job_id,
									sf.job_item_id as job_item_id,
									sf.total_time as total_time,
									sf.schedule_policy_id as schedule_policy_id,
									sf.absence_policy_id as absence_policy_id,

									sf.note as note,

									sf.created_date as created_date,
									sf.updated_date as updated_date,
									sf.deleted as deleted
								FROM ' . $sf->getTable() . ' as sf
								LEFT JOIN ' . $sf->getTable() . ' as sf_b ON ( sf.id = sf_b.replaced_id AND sf_b.deleted = 0 )
								WHERE sf.company_id = \'' . TTUUID::castUUID( $company_id ) . '\' 
								';

		if ( isset( $filter_data['start_date'] ) && !is_array( $filter_data['start_date'] ) && trim( $filter_data['start_date'] ) != '' ) {
			$ph[] = $this->db->BindDate( ( (int)$filter_data['start_date'] - 86400 ) );
			$query .= ' AND sf.date_stamp >= ?';
		}
		if ( isset( $filter_data['end_date'] ) && !is_array( $filter_data['end_date'] ) && trim( $filter_data['end_date'] ) != '' ) {
			$ph[] = $this->db->BindDate( ( (int)$filter_data['end_date'] + 86400 ) );
			$query .= ' AND sf.date_stamp <= ?';
		}

		$query .= ' 
				
									AND sf_b.replaced_id IS NULL								
									AND sf.deleted = 0
							)
						UNION ALL
							(
								SELECT
									NULL as id,
									rsf.id as schedule_id,
									NULL as replaced_id,
									rsf.id as recurring_schedule_id,
									rsf.company_id as company_id,

									rsf.user_id as user_id,
									rsf.recurring_schedule_template_control_id,
									rsf.date_stamp as date_stamp,
									ppf.id as pay_period_id,

									rsf.status_id as status_id,
									rsf.start_time as start_time,
									rsf.end_time as end_time,

									CASE WHEN rsf.branch_id = \'' . TTUUID::getNotExistID() . '\' THEN uf_b.default_branch_id ELSE rsf.branch_id END as branch_id,
									CASE WHEN rsf.department_id = \'' . TTUUID::getNotExistID() . '\' THEN uf_b.default_department_id ELSE rsf.department_id END as department_id,
									CASE WHEN rsf.job_id = \'' . TTUUID::getNotExistID() . '\' THEN uf_b.default_job_id ELSE rsf.job_id END as job_id,
									CASE WHEN rsf.job_item_id = \'' . TTUUID::getNotExistID() . '\' THEN uf_b.default_job_item_id ELSE rsf.job_item_id END as job_item_id,

									rsf.total_time as total_time,
									rsf.schedule_policy_id as schedule_policy_id,
									rsf.absence_policy_id as absence_policy_id,

									rsf.note as note,

									rsf.created_date as created_date,
									rsf.updated_date as updated_date,
									rsf.deleted as deleted
								FROM ' . $rsf->getTable() . ' as rsf
								LEFT JOIN ' . $rscf->getTable() . ' as rscf ON rsf.recurring_schedule_control_id = rscf.id
								LEFT JOIN ' . $uf->getTable() . ' as uf_b ON rsf.user_id = uf_b.id
								LEFT JOIN ' . $ppsuf->getTable() . ' as ppsuf ON ( rsf.user_id = ppsuf.user_id )
								LEFT JOIN ' . $ppsf->getTable() . ' as ppsf ON ( ppsuf.pay_period_schedule_id = ppsf.id AND ppsf.deleted = 0 )
								LEFT JOIN ' . $ppf->getTable() . ' as ppf ON ( ppf.pay_period_schedule_id = ppsuf.pay_period_schedule_id AND rsf.date_stamp >= ppf.start_date AND rsf.date_stamp <= ppf.end_date AND ppf.deleted = 0 )
								LEFT JOIN schedule as sf_b ON (
																( sf_b.user_id != \'' . TTUUID::getZeroID() . '\' AND sf_b.user_id = rsf.user_id ) ';

		if ( isset( $filter_data['start_date'] ) && !is_array( $filter_data['start_date'] ) && trim( (string)$filter_data['start_date'] ) != '' ) {
			$ph[] = $this->db->BindDate( ( (int)$filter_data['start_date'] - 86400 ) );
			$query .= ' AND sf_b.date_stamp >= ?';
		}
		if ( isset( $filter_data['end_date'] ) && !is_array( $filter_data['end_date'] ) && trim( (string)$filter_data['end_date'] ) != '' ) {
			$ph[] = $this->db->BindDate( ( (int)$filter_data['end_date'] + 86400 ) );
			$query .= ' AND sf_b.date_stamp <= ?';
		}

		$query .= '									AND
																(
																sf_b.start_time >= rsf.start_time AND sf_b.end_time <= rsf.end_time
																OR
																sf_b.start_time >= rsf.start_time AND sf_b.start_time < rsf.end_time
																OR
																sf_b.end_time > rsf.start_time AND sf_b.end_time <= rsf.end_time
																OR
																sf_b.start_time <= rsf.start_time AND sf_b.end_time >= rsf.end_time
																OR
																sf_b.start_time = rsf.start_time AND sf_b.end_time = rsf.end_time
																)
																AND sf_b.deleted = 0
															)
								WHERE sf_b.id is NULL
									AND rsf.company_id = \'' . TTUUID::castUUID( $company_id ) . '\'
									AND ( uf_b.hire_date IS NULL OR uf_b.hire_date <= rsf.date_stamp )
									AND ( uf_b.termination_date IS NULL OR uf_b.termination_date >= rsf.date_stamp )';

		if ( $exclude_recurring_schedule_ids != false ) {
			$query .= ' AND rsf.id NOT IN ( ' . $this->getListSQL( $exclude_recurring_schedule_ids ) . ' ) ';
		}

		$query .= '
									AND ( rsf.deleted = 0 AND rscf.deleted = 0 AND ( ppsf.deleted IS NULL OR ppsf.deleted = 0 ) AND ( uf_b.deleted IS NULL OR uf_b.deleted = 0 ) )
							)
					) as a

					LEFT JOIN ' . $uf->getTable() . ' as uf ON ( a.user_id = uf.id AND uf.deleted = 0 )
					LEFT JOIN ' . $bf->getTable() . ' as bf ON ( uf.default_branch_id = bf.id AND bf.deleted = 0)
					LEFT JOIN ' . $bf->getTable() . ' as bfb ON ( a.branch_id = bfb.id AND bfb.deleted = 0)
					LEFT JOIN ' . $df->getTable() . ' as df ON ( uf.default_department_id = df.id AND df.deleted = 0)
					LEFT JOIN ' . $df->getTable() . ' as dfb ON ( a.department_id = dfb.id AND dfb.deleted = 0)
					LEFT JOIN ' . $ugf->getTable() . ' as ugf ON ( uf.group_id = ugf.id AND ugf.deleted = 0 )
					LEFT JOIN ' . $utf->getTable() . ' as utf ON ( uf.title_id = utf.id AND utf.deleted = 0 )
					LEFT JOIN ' . $apf->getTable() . ' as apf ON ( a.absence_policy_id = apf.id )
					LEFT JOIN ' . $ppf->getTable() . ' as ppf ON ( a.pay_period_id = ppf.id AND ppf.deleted = 0 )
					LEFT JOIN ' . $uwf->getTable() . ' as uwf ON uwf.id = (select z.id
																from ' . $uwf->getTable() . ' as z
																where z.user_id = a.user_id
																	and z.effective_date <= a.date_stamp
																	and z.deleted = 0
																	order by z.effective_date desc limit 1)
					';
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= '
						LEFT JOIN ' . $jf->getTable() . ' as jfb ON ( uf.default_job_id = jfb.id AND jfb.deleted = 0 )
						LEFT JOIN ' . $jif->getTable() . ' as jifb ON ( uf.default_job_item_id = jifb.id AND jifb.deleted = 0 )
			
						LEFT JOIN ' . $jf->getTable() . ' as jf ON ( a.job_id = jf.id AND jf.deleted = 0 )
						LEFT JOIN ' . $jif->getTable() . ' as jif ON ( a.job_item_id = jif.id AND jif.deleted = 0 )
						LEFT JOIN ' . $bf->getTable() . ' as jbf ON ( jf.branch_id = jbf.id AND jbf.deleted = 0 )
						LEFT JOIN ' . $df->getTable() . ' as jdf ON ( jf.department_id = jdf.id AND jdf.deleted = 0 )
						LEFT JOIN ' . $jgf->getTable() . ' as jgf ON ( jf.group_id = jgf.id AND jgf.deleted = 0 )
						LEFT JOIN ' . $jigf->getTable() . ' as jigf ON ( jif.group_id = jigf.id AND jigf.deleted = 0 )
						';
		}

		$query .= Permission::getPermissionHierarchySQL( $company_id, ( isset( $filter_data['permission_current_user_id'] ) ) ? $filter_data['permission_current_user_id'] : 0, 'a.user_id' );

		$query .= '	WHERE ( a.company_id = \'' . TTUUID::castUUID( $company_id ) . '\' )';

		$query .= Permission::getPermissionIsChildIsOwnerFilterSQL( $filter_data, 'a.user_id' );
		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		//Make sure we filter on user_date.user_id column, to handle OPEN shifts.
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['recurring_schedule_id'] ) ) ? $this->getWhereClauseSQL( 'a.recurring_schedule_id', $filter_data['recurring_schedule_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['schedule_id'] ) ) ? $this->getWhereClauseSQL( 'a.schedule_id', $filter_data['schedule_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['user_status_id'] ) ) ? $this->getWhereClauseSQL( 'uf.status_id', $filter_data['user_status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['group_id'] ) ) ? $this->getWhereClauseSQL( 'uf.group_id', $filter_data['group_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_branch_id'] ) ) ? $this->getWhereClauseSQL( 'uf.default_branch_id', $filter_data['default_branch_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_department_id'] ) ) ? $this->getWhereClauseSQL( 'uf.default_department_id', $filter_data['default_department_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['title_id'] ) ) ? $this->getWhereClauseSQL( 'uf.title_id', $filter_data['title_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['first_name'] ) ) ? $this->getWhereClauseSQL( 'uf.first_name', $filter_data['first_name'], 'text_metaphone', $ph ) : null;
		$query .= ( isset( $filter_data['last_name'] ) ) ? $this->getWhereClauseSQL( 'uf.last_name', $filter_data['last_name'], 'text_metaphone', $ph ) : null;

		$query .= ( isset( $filter_data['recurring_schedule_template_control_id'] ) ) ? $this->getWhereClauseSQL( 'a.recurring_schedule_template_control_id', $filter_data['recurring_schedule_template_control_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['schedule_branch_id'] ) ) ? $this->getWhereClauseSQL( 'a.branch_id', $filter_data['schedule_branch_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['schedule_department_id'] ) ) ? $this->getWhereClauseSQL( 'a.department_id', $filter_data['schedule_department_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['schedule_policy_id'] ) ) ? $this->getWhereClauseSQL( 'a.schedule_policy_id', $filter_data['schedule_policy_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['absence_policy_id'] ) ) ? $this->getWhereClauseSQL( 'a.absence_policy_id', $filter_data['absence_policy_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['pay_period_id'] ) ) ? $this->getWhereClauseSQL( 'a.pay_period_id', $filter_data['pay_period_id'], 'uuid_list', $ph ) : null;

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= ( isset( $filter_data['job_id'] ) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['job_id'], 'uuid_list', $ph ) : null;
			$query .= ( isset( $filter_data['job_status_id'] ) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['job_status_id'], 'numeric_list', $ph ) : null;
			$query .= ( isset( $filter_data['include_job_id'] ) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['include_job_id'], 'uuid_list', $ph ) : null;
			$query .= ( isset( $filter_data['exclude_job_id'] ) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['exclude_job_id'], 'not_uuid_list', $ph ) : null;
			$query .= ( isset( $filter_data['job_group_id'] ) ) ? $this->getWhereClauseSQL( 'jf.group_id', $filter_data['job_group_id'], 'uuid_list', $ph ) : null;
			$query .= ( isset( $filter_data['job_item_id'] ) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['job_item_id'], 'uuid_list', $ph ) : null;
		}

		$query .= ( isset( $filter_data['date_stamp'] ) ) ? $this->getWhereClauseSQL( 'a.date_stamp', $filter_data['date_stamp'], 'date_range_datestamp', $ph ) : null;
		$query .= ( isset( $filter_data['start_date'] ) ) ? $this->getWhereClauseSQL( 'a.date_stamp', $filter_data['start_date'], 'start_datestamp', $ph ) : null;
		$query .= ( isset( $filter_data['end_date'] ) ) ? $this->getWhereClauseSQL( 'a.date_stamp', $filter_data['end_date'], 'end_datestamp', $ph ) : null;

		$query .= ( isset( $filter_data['start_time'] ) ) ? $this->getWhereClauseSQL( 'a.start_time', $filter_data['start_time'], 'start_timestamp', $ph ) : null;
		$query .= ( isset( $filter_data['end_time'] ) ) ? $this->getWhereClauseSQL( 'a.end_time', $filter_data['end_time'], 'end_timestamp', $ph ) : null;

		$query .= '
						AND ( a.deleted = 0 AND ( uf.deleted IS NULL OR uf.deleted = 0 ) )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );
		//Debug::Query($query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param $filter_data
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|ScheduleListFactory
	 */
	function getAPISearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( !is_array( $order ) ) {
			//Use Filter Data ordering if its set.
			if ( isset( $filter_data['sort_column'] ) && $filter_data['sort_order'] ) {
				$order = [ Misc::trimSortPrefix( $filter_data['sort_column'] ) => $filter_data['sort_order'] ];
			}
		}

		$additional_order_fields = [ 'schedule_policy_id', 'schedule_policy', 'absence_policy', 'first_name', 'last_name', 'user_status_id', 'group_id', 'group', 'title_id', 'title', 'default_branch_id', 'default_branch', 'default_department_id', 'default_department', 'total_time', 'date_stamp', 'pay_period_id', 'j.name', 'k.name' ];

		$sort_column_aliases = [
				'status'       => 'status_id',
				'first_name'   => 'd.first_name',
				'last_name'    => 'd.last_name',
				'user_status'  => 'user_status_id',
				'branch'       => 'j.name',
				'department'   => 'k.name',
				'updated_date' => 'a.updated_date',
				'created_date' => 'a.created_date',
		];

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) { //Needed for unit tests to pass when doing pure edition tests.
			$additional_order_fields = array_merge( [ 'w.name', 'x.name' ], $additional_order_fields );

			$sort_column_aliases = array_merge( [
														'job'      => 'w.name',
														'job_item' => 'x.name',
												], $sort_column_aliases );
		}

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == null ) {
			$order = [ 'a.start_time' => 'desc', 'd.last_name' => 'asc', 'd.first_name' => 'asc', 'a.user_id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		//if ( isset($filter_data['exclude_user_ids']) ) {
		//	$filter_data['exclude_id'] = $filter_data['exclude_user_ids'];
		//}
		if ( isset( $filter_data['exclude_user_ids'] ) ) {
			$filter_data['exclude_user_id'] = $filter_data['exclude_user_ids'];
		}

		if ( isset( $filter_data['include_user_ids'] ) ) {
			$filter_data['user_id'] = $filter_data['include_user_ids'];
		}
		if ( isset( $filter_data['user_status_ids'] ) ) {
			$filter_data['user_status_id'] = $filter_data['user_status_ids'];
		}
		if ( isset( $filter_data['user_title_ids'] ) ) {
			$filter_data['title_id'] = $filter_data['user_title_ids'];
		}
		if ( isset( $filter_data['group_ids'] ) ) {
			$filter_data['group_id'] = $filter_data['group_ids'];
		}
		if ( isset( $filter_data['default_branch_ids'] ) ) {
			$filter_data['default_branch_id'] = $filter_data['default_branch_ids'];
		}
		if ( isset( $filter_data['default_department_ids'] ) ) {
			$filter_data['default_department_id'] = $filter_data['default_department_ids'];
		}
		if ( isset( $filter_data['branch_ids'] ) ) {
			$filter_data['branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset( $filter_data['department_ids'] ) ) {
			$filter_data['department_id'] = $filter_data['department_ids'];
		}

		if ( isset( $filter_data['include_job_ids'] ) ) {
			$filter_data['include_job_id'] = $filter_data['include_job_ids'];
		}
		if ( isset( $filter_data['job_group_ids'] ) ) {
			$filter_data['job_group_id'] = $filter_data['job_group_ids'];
		}
		if ( isset( $filter_data['job_item_ids'] ) ) {
			$filter_data['job_item_id'] = $filter_data['job_item_ids'];
		}
		if ( isset( $filter_data['pay_period_ids'] ) ) {
			$filter_data['pay_period_id'] = $filter_data['pay_period_ids'];
		}

		if ( isset( $filter_data['start_time'] ) ) {
			$filter_data['start_date'] = $filter_data['start_time'];
		}
		if ( isset( $filter_data['end_time'] ) ) {
			$filter_data['end_date'] = $filter_data['end_time'];
		}

		$spf = new SchedulePolicyFactory();
		$apf = new AbsencePolicyFactory();
		$uf = new UserFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$sf = new ScheduleFactory();
		$uwf = new UserWageFactory();

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jf = new JobFactory();
			$jif = new JobItemFactory();
		}

		$ph = [
				'company_id'  => TTUUID::castUUID( $company_id ),
				'company_id2' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select
							a.id as id,
							a.id as schedule_id,
							a.replaced_id as replaced_id,
							a.status_id as status_id,
							a.start_time as start_time,
							a.end_time as end_time,

							a.branch_id as branch_id,
							j.name as branch,
							a.department_id as department_id,
							k.name as department,
							a.job_id as job_id,
							a.job_item_id as job_item_id,
							a.total_time as total_time,
							a.schedule_policy_id as schedule_policy_id,
							a.absence_policy_id as absence_policy_id,
							a.recurring_schedule_template_control_id as recurring_schedule_template_control_id,
							a.note as note,

							a.other_id1 as other_id1,
							a.other_id2 as other_id2,
							a.other_id3 as other_id3,
							a.other_id4 as other_id4,
							a.other_id5 as other_id5,

							i.name as schedule_policy,
							apf.name as absence_policy,

							a.user_id as user_id,
							a.date_stamp as date_stamp,
							a.pay_period_id as pay_period_id,

							d.first_name as first_name,
							d.last_name as last_name,
							d.status_id as user_status_id,
							d.group_id as group_id,
							g.name as "group",
							d.title_id as title_id,
							h.name as title,
							d.default_branch_id as default_branch_id,
							e.name as default_branch,
							d.default_department_id as default_department_id,
							f.name as default_department,
							d.created_by as user_created_by,

							m.id as user_wage_id,
							m.effective_date as user_wage_effective_date,

							a.created_date as created_date,
							a.created_by as created_by,
							a.updated_date as updated_date,
							a.updated_by as updated_by,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name';

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= ',
						w.name as job,
						w.status_id as job_status_id,
						w.manual_id as job_manual_id,
						w.branch_id as job_branch_id,
						w.department_id as job_department_id,
						w.group_id as job_group_id,

						x.name as job_item,
						x.manual_id as job_item_manual_id,
						x.group_id as job_item_group_id
						';
		}

		$query .= '
					from	' . $this->getTable() . ' as a
							LEFT JOIN ' . $sf->getTable() . ' as sf ON ( a.id = sf.replaced_id AND sf.deleted = 0 )
							LEFT JOIN ' . $spf->getTable() . ' as i ON a.schedule_policy_id = i.id
							LEFT JOIN ' . $uf->getTable() . ' as d ON ( a.user_id = d.id AND d.deleted = 0 )

							LEFT JOIN ' . $bf->getTable() . ' as e ON ( d.default_branch_id = e.id AND e.deleted = 0)
							LEFT JOIN ' . $df->getTable() . ' as f ON ( d.default_department_id = f.id AND f.deleted = 0)
							LEFT JOIN ' . $ugf->getTable() . ' as g ON ( d.group_id = g.id AND g.deleted = 0 )
							LEFT JOIN ' . $utf->getTable() . ' as h ON ( d.title_id = h.id AND h.deleted = 0 )

							LEFT JOIN ' . $bf->getTable() . ' as j ON ( a.branch_id = j.id AND j.deleted = 0)
							LEFT JOIN ' . $df->getTable() . ' as k ON ( a.department_id = k.id AND k.deleted = 0)

							LEFT JOIN ' . $apf->getTable() . ' as apf ON a.absence_policy_id = apf.id

							LEFT JOIN ' . $uwf->getTable() . ' as m ON m.id = (select m.id
																		from ' . $uwf->getTable() . ' as m
																		where m.user_id = a.user_id
																			and m.effective_date <= a.date_stamp
																			and m.deleted = 0
																			order by m.effective_date desc limit 1)
					';
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= '	LEFT JOIN ' . $jf->getTable() . ' as w ON a.job_id = w.id';
			$query .= '	LEFT JOIN ' . $jif->getTable() . ' as x ON a.job_item_id = x.id';
		}

		$query .= '
						LEFT JOIN ' . $uf->getTable() . ' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					WHERE ( d.company_id = ? OR a.company_id = ? ) AND sf.replaced_id IS NULL ';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['user_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_user_id'] ) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['exclude_user_id'], 'not_uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['user_status_id'] ) ) ? $this->getWhereClauseSQL( 'd.status_id', $filter_data['user_status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['group_id'] ) ) ? $this->getWhereClauseSQL( 'd.group_id', $filter_data['group_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['legal_entity_id'] ) ) ? $this->getWhereClauseSQL( 'd.legal_entity_id', $filter_data['legal_entity_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_branch_id'] ) ) ? $this->getWhereClauseSQL( 'd.default_branch_id', $filter_data['default_branch_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_department_id'] ) ) ? $this->getWhereClauseSQL( 'd.default_department_id', $filter_data['default_department_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['title_id'] ) ) ? $this->getWhereClauseSQL( 'd.title_id', $filter_data['title_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['branch_id'] ) ) ? $this->getWhereClauseSQL( 'a.branch_id', $filter_data['branch_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['department_id'] ) ) ? $this->getWhereClauseSQL( 'a.department_id', $filter_data['department_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['schedule_policy_id'] ) ) ? $this->getWhereClauseSQL( 'a.schedule_policy_id', $filter_data['schedule_policy_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['absence_policy_id'] ) ) ? $this->getWhereClauseSQL( 'a.absence_policy_id', $filter_data['absence_policy_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['pay_period_id'] ) ) ? $this->getWhereClauseSQL( 'a.pay_period_id', $filter_data['pay_period_id'], 'uuid_list', $ph ) : null;

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= ( isset( $filter_data['job_status_id'] ) ) ? $this->getWhereClauseSQL( 'w.status_id', $filter_data['job_status_id'], 'numeric_list', $ph ) : null;
			$query .= ( isset( $filter_data['include_job_id'] ) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['include_job_id'], 'uuid_list', $ph ) : null;
			$query .= ( isset( $filter_data['exclude_job_id'] ) ) ? $this->getWhereClauseSQL( 'a.job_id', $filter_data['exclude_job_id'], 'not_uuid_list', $ph ) : null;
			$query .= ( isset( $filter_data['job_group_id'] ) ) ? $this->getWhereClauseSQL( 'w.group_id', $filter_data['job_group_id'], 'uuid_list', $ph ) : null;
			$query .= ( isset( $filter_data['job_item_id'] ) ) ? $this->getWhereClauseSQL( 'a.job_item_id', $filter_data['job_item_id'], 'uuid_list', $ph ) : null;
		}

		$query .= ( isset( $filter_data['date_stamp'] ) ) ? $this->getWhereClauseSQL( 'a.date_stamp', $filter_data['date_stamp'], 'date_range_datestamp', $ph ) : null;
		$query .= ( isset( $filter_data['start_date'] ) ) ? $this->getWhereClauseSQL( 'a.date_stamp', TTDate::parseDateTime( $filter_data['start_date'] ), 'start_datestamp', $ph ) : null;
		$query .= ( isset( $filter_data['end_date'] ) ) ? $this->getWhereClauseSQL( 'a.date_stamp', TTDate::parseDateTime( $filter_data['end_date'] ), 'end_datestamp', $ph ) : null;

		$query .= ( isset( $filter_data['start_time'] ) ) ? $this->getWhereClauseSQL( 'a.start_time', $filter_data['start_time'], 'start_timestamp', $ph ) : null;
		$query .= ( isset( $filter_data['end_time'] ) ) ? $this->getWhereClauseSQL( 'a.end_time', $filter_data['end_time'], 'end_timestamp', $ph ) : null;

		$query .= ( isset( $filter_data['created_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.created_by', 'y.first_name', 'y.last_name' ], $filter_data['created_by'], 'user_id_or_name', $ph ) : null;
		$query .= ( isset( $filter_data['updated_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.updated_by', 'z.first_name', 'z.last_name' ], $filter_data['updated_by'], 'user_id_or_name', $ph ) : null;

		$query .= ' AND ( a.deleted = 0 ) ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}
}

?>
