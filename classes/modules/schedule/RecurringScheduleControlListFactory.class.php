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
 * @package Modules\Schedule
 */
class RecurringScheduleControlListFactory extends RecurringScheduleControlFactory implements IteratorAggregate {

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
	 * @return bool|RecurringScheduleControlListFactory
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
	 * @param string $id         UUID
	 * @param string $company_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RecurringScheduleControlListFactory
	 */
	function getByIdAndCompanyId( $id, $company_id, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $company_id == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'id'         => TTUUID::castUUID( $id ),
		];

		$query = '
					select	*
					from	' . $this->getTable() . ' as a
					where	company_id = ?
						AND id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id   UUID
	 * @param int $limit   Limit the number of records returned
	 * @param int $page    Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RecurringScheduleControlListFactory
	 */
	function getByCompanyId( $id, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'last_name' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$additional_sort_fields = [ 'name', 'description', 'last_name' ];

		$rsuf = new RecurringScheduleUserFactory();
		$rstcf = new RecurringScheduleTemplateControlFactory();
		$uf = new UserFactory();

		$ph = [
				'id' => TTUUID::castUUID( $id ),
		];


		$query = '
					select	a.*,
							b.name as name,
							b.description as description,
							c.user_id as user_id,
							d.last_name as last_name
					from	' . $this->getTable() . ' as a,
							' . $rstcf->getTable() . ' as b,
							' . $rsuf->getTable() . ' as c,
							' . $uf->getTable() . ' as d
					where	a.recurring_schedule_template_control_id = b.id
						AND a.id = c.recurring_schedule_control_id
						AND c.user_id = d.id
						AND a.company_id = ?
						AND ( a.deleted = 0 AND b.deleted=0 AND d.deleted=0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_sort_fields );

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @param array $where    Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order    Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RecurringScheduleControlListFactory
	 */
	function getByUserIDAndStartDateAndEndDate( $user_id, $start_date, $end_date, $where = null, $order = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		if ( $order == null ) {
			//$order = array( 'type_id' => 'asc' );
			$strict = false;
		} else {
			$strict = true;
		}

		$start_date_stamp = $this->db->BindDate( $start_date );
		$end_date_stamp = $this->db->BindDate( $end_date );

		$rsuf = new RecurringScheduleUserFactory();
		$rstcf = new RecurringScheduleTemplateControlFactory();

		$ph = [
				'user_id'     => TTUUID::castUUID( $user_id ),
				'start_date1' => $start_date_stamp,
				'end_date1'   => $end_date_stamp,
				'start_date2' => $start_date_stamp,
				'start_date3' => $start_date_stamp,
				'end_date3'   => $end_date_stamp,
				'start_date4' => $start_date_stamp,
				'end_date4'   => $end_date_stamp,
				'start_date5' => $start_date_stamp,
				'end_date5'   => $end_date_stamp,
				'start_date6' => $start_date_stamp,
				'end_date6'   => $end_date_stamp,
		];
		/*

							from	'. $this->getTable() .' as a,
									'. $rsuf->getTable() .' as b
							where	a.id = b.recurring_schedule_control_id

		*/
		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $rstcf->getTable() . ' as b ON a.recurring_schedule_template_control_id = b.id
						LEFT JOIN ' . $rsuf->getTable() . ' as c ON a.id = c.recurring_schedule_control_id
					WHERE c.user_id = ?
						AND
						(
							(a.start_date >= ? AND a.start_date <= ? AND a.end_date IS NULL )
							OR
							(a.start_date <= ? AND a.end_date IS NULL )
							OR
							(a.start_date >= ? AND a.end_date <= ? )
							OR
							(a.start_date >= ? AND a.start_date <= ? )
							OR
							(a.end_date >= ? AND a.end_date <= ? )
							OR
							(a.start_date <= ? AND a.end_date >= ? )
						)
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $end_date      EPOCH
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RecurringScheduleControlListFactory
	 */
	function getByCompanyIdAndEndDate( $company_id, $end_date, $where = null, $order = null ) {
		if ( $end_date == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'a.company_id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'end_date'   => $this->db->BindDate( $end_date ),
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
					where	 a.company_id = ?
						AND ( a.end_date IS NOT NULL AND a.end_date <= ? )
						AND ( a.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @return mixed
	 */
	function getMostCommonDisplayWeeksByCompanyId( $company_id, $where = null ) {
		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$rstcf = new RecurringScheduleTemplateControlFactory();

		$query = '
					SELECT	a.display_weeks as display_weeks
					FROM	' . $this->getTable() . ' as a
					LEFT JOIN ' . $rstcf->getTable() . ' as b ON a.recurring_schedule_template_control_id = b.id
					WHERE	 a.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )
					GROUP BY a.display_weeks
					ORDER BY count(*) DESC
					LIMIT 1
					';

		$query .= $this->getWhereSQL( $where );

		$result = $this->db->GetOne( $query, $ph );

		return $result;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $start_date    EPOCH
	 * @param int $end_date      EPOCH
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RecurringScheduleControlListFactory
	 */
	function getByCompanyIdAndStartDateAndEndDate( $company_id, $start_date, $end_date, $where = null, $order = null ) {
		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'a.company_id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$start_date_stamp = $this->db->BindDate( $start_date );
		$end_date_stamp = $this->db->BindDate( $end_date );

		//$rsuf = new RecurringScheduleUserFactory();
		$rstcf = new RecurringScheduleTemplateControlFactory();

		$ph = [
				'company_id'  => TTUUID::castUUID( $company_id ),
				'start_date1' => $start_date_stamp,
				'end_date1'   => $end_date_stamp,
				'start_date2' => $start_date_stamp,
				'start_date3' => $start_date_stamp,
				'end_date3'   => $end_date_stamp,
				'start_date4' => $start_date_stamp,
				'end_date4'   => $end_date_stamp,
				'start_date5' => $start_date_stamp,
				'end_date5'   => $end_date_stamp,
				'start_date6' => $start_date_stamp,
				'end_date6'   => $end_date_stamp,
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $rstcf->getTable() . ' as b ON a.recurring_schedule_template_control_id = b.id
					where	 a.company_id = ?
						AND
						(
							(a.start_date >= ? AND a.start_date <= ? AND a.end_date IS NULL )
							OR
							(a.start_date <= ? AND a.end_date IS NULL )
							OR
							(a.start_date >= ? AND a.end_date <= ? )
							OR
							(a.start_date >= ? AND a.start_date <= ? )
							OR
							(a.end_date >= ? AND a.end_date <= ? )
							OR
							(a.start_date <= ? AND a.end_date >= ? )
						)
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $id         UUID
	 * @param int $start_date    EPOCH
	 * @param int $end_date      EPOCH
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RecurringScheduleControlListFactory
	 */
	function getByCompanyIdAndIDAndStartDateAndEndDate( $company_id, $id, $start_date, $end_date, $where = null, $order = null ) {
		if ( $start_date == '' ) {
			return false;
		}

		if ( $end_date == '' ) {
			return false;
		}

		if ( $order == null ) {
			$order = [ 'a.company_id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$start_date_stamp = $this->db->BindDate( $start_date );
		$end_date_stamp = $this->db->BindDate( $end_date );

		//$rsuf = new RecurringScheduleUserFactory();
		$rstcf = new RecurringScheduleTemplateControlFactory();

		$ph = [
				'company_id'  => TTUUID::castUUID( $company_id ),
				//'id' => $id,
				'start_date1' => $start_date_stamp,
				'end_date1'   => $end_date_stamp,
				'start_date2' => $start_date_stamp,
				'start_date3' => $start_date_stamp,
				'end_date3'   => $end_date_stamp,
				'start_date4' => $start_date_stamp,
				'end_date4'   => $end_date_stamp,
				'start_date5' => $start_date_stamp,
				'end_date5'   => $end_date_stamp,
				'start_date6' => $start_date_stamp,
				'end_date6'   => $end_date_stamp,
		];

		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $rstcf->getTable() . ' as b ON a.recurring_schedule_template_control_id = b.id
					where	 a.company_id = ?
						AND
						(
							(a.start_date >= ? AND a.start_date <= ? AND a.end_date IS NULL )
							OR
							(a.start_date <= ? AND a.end_date IS NULL )
							OR
							(a.start_date >= ? AND a.end_date <= ? )
							OR
							(a.start_date >= ? AND a.start_date <= ? )
							OR
							(a.end_date >= ? AND a.end_date <= ? )
							OR
							(a.start_date <= ? AND a.end_date >= ? )
						)
						AND a.id in (' . $this->getListSQL( $id, $ph, 'uuid' ) . ')						
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $id         UUID
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return $this
	 */
	function getByCompanyIdAndTemplateID( $company_id, $id, $where = null, $order = null ) {
		if ( $order == null ) {
			$order = [ 'a.company_id' => 'asc', 'a.start_date' => 'asc', 'a.id' => 'asc' ];
			$strict = false;
		} else {
			$strict = true;
		}

		$rstcf = new RecurringScheduleTemplateControlFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'id'         => TTUUID::castUUID( $id ),
		];

		//Don't filter on b.deleted=0, as this is mainly called when deleting a RecurringScheduleTemplateControl record
		//at which point the record is already deleted and therefore this won't return any data, causing things to break.
		//This shouldn't be called from any other function most likely.
		//AND b.deleted = 0
		$query = '
					select	a.*
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $rstcf->getTable() . ' as b ON a.recurring_schedule_template_control_id = b.id
					where	 a.company_id = ?
						AND a.recurring_schedule_template_control_id = ?
						AND ( a.deleted = 0 )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param $filter_data
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RecurringScheduleControlListFactory
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

		$additional_order_fields = [ 'recurring_schedule_template_control', 'recurring_schedule_template_control_description' ];
		if ( $order == null ) {
			$order = [ 'recurring_schedule_template_control_id' => 'asc', ];
			$strict = false;
		} else {
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$rsuf = new RecurringScheduleUserFactory();
		$rstcf = new RecurringScheduleTemplateControlFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	distinct a.*,
							ab.name as recurring_schedule_template_control,
							ab.description as recurring_schedule_template_control_description
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $rstcf->getTable() . ' as ab ON ( a.recurring_schedule_template_control_id = ab.id AND ab.deleted = 0 )
						LEFT JOIN ' . $rsuf->getTable() . ' as rsuf ON ( a.id = rsuf.recurring_schedule_control_id )

					where	a.company_id = ?
					';

		//Make sure supervisor (subordinates only) can see/edit any recurring schedule assigned to their subordinates
		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'rsuf.user_id', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['recurring_schedule_template_control_id'] ) ) ? $this->getWhereClauseSQL( 'a.recurring_schedule_template_control_id', $filter_data['recurring_schedule_template_control_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'rsuf.user_id', $filter_data['user_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['created_by'] ) ) ? $this->getWhereClauseSQL( 'a.created_by', $filter_data['created_by'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['updated_by'] ) ) ? $this->getWhereClauseSQL( 'a.updated_by', $filter_data['updated_by'], 'uuid_list', $ph ) : null;

		$query .= ' AND a.deleted = 0 ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param $filter_data
	 * @param int $limit         Limit the number of records returned
	 * @param int $page          Page number of records to return for pagination
	 * @param array $where       Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order       Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|RecurringScheduleControlListFactory
	 */
	function getAPIExpandedSearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = null, $page = null, $where = null, $order = null ) {
		if ( $company_id == '' ) {
			return false;
		}

		if ( isset( $filter_data['user_status_id'] ) ) {
			$filter_data['status_id'] = $filter_data['user_status_id'];
			unset( $filter_data['user_status_id'] );
		}

		if ( !is_array( $order ) ) {
			//Use Filter Data ordering if its set.
			if ( isset( $filter_data['sort_column'] ) && $filter_data['sort_order'] ) {
				$order = [ Misc::trimSortPrefix( $filter_data['sort_column'] ) => $filter_data['sort_order'] ];
			}
		}

		$additional_order_fields = [ 'first_name', 'last_name', 'title', 'user_group', 'default_branch', 'default_department', 'recurring_schedule_template_control', 'recurring_schedule_template_control_description' ];
		if ( $order == null ) {
			$order = [ 'recurring_schedule_template_control_id' => 'asc', ];
			$strict = false;
		} else {
			$strict = true;
		}
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$rsuf = new RecurringScheduleUserFactory();
		$rstcf = new RecurringScheduleTemplateControlFactory();

		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
		];

		$query = '
					select	a.*,
							ac.user_id as user_id,
							ab.name as recurring_schedule_template_control,
							ab.description as recurring_schedule_template_control_description,
							b.first_name as first_name,
							b.last_name as last_name,
							b.country as country,
							b.province as province,

							c.id as default_branch_id,
							c.name as default_branch,
							d.id as default_department_id,
							d.name as default_department,
							e.id as group_id,
							e.name as user_group,
							f.id as title_id,
							f.name as title,
							
							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name							
					from	' . $this->getTable() . ' as a
						LEFT JOIN ' . $rstcf->getTable() . ' as ab ON ( a.recurring_schedule_template_control_id = ab.id )
						LEFT JOIN ' . $rsuf->getTable() . ' as ac ON a.id = ac.recurring_schedule_control_id
						LEFT JOIN ' . $uf->getTable() . ' as b ON ( ac.user_id = b.id AND b.deleted = 0 )
						LEFT JOIN ' . $bf->getTable() . ' as c ON ( b.default_branch_id = c.id AND c.deleted = 0)
						LEFT JOIN ' . $df->getTable() . ' as d ON ( b.default_department_id = d.id AND d.deleted = 0)
						LEFT JOIN ' . $ugf->getTable() . ' as e ON ( b.group_id = e.id AND e.deleted = 0 )
						LEFT JOIN ' . $utf->getTable() . ' as f ON ( b.title_id = f.id AND f.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN ' . $uf->getTable() . ' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	a.company_id = ?
					';

		$query .= ( isset( $filter_data['permission_children_ids'] ) ) ? $this->getWhereClauseSQL( 'ac.user_id', $filter_data['permission_children_ids'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['id'] ) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['exclude_id'] ) ) ? $this->getWhereClauseSQL( 'ac.user_id', $filter_data['exclude_id'], 'not_uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['user_id'] ) ) ? $this->getWhereClauseSQL( 'ac.user_id', $filter_data['user_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['recurring_schedule_template_control_id'] ) ) ? $this->getWhereClauseSQL( 'a.recurring_schedule_template_control_id', $filter_data['recurring_schedule_template_control_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['status_id'] ) ) ? $this->getWhereClauseSQL( 'b.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : null;
		$query .= ( isset( $filter_data['group_id'] ) ) ? $this->getWhereClauseSQL( 'b.group_id', $filter_data['group_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['legal_entity_id'] ) ) ? $this->getWhereClauseSQL( 'b.legal_entity_id', $filter_data['legal_entity_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_branch_id'] ) ) ? $this->getWhereClauseSQL( 'b.default_branch_id', $filter_data['default_branch_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['default_department_id'] ) ) ? $this->getWhereClauseSQL( 'b.default_department_id', $filter_data['default_department_id'], 'uuid_list', $ph ) : null;
		$query .= ( isset( $filter_data['title_id'] ) ) ? $this->getWhereClauseSQL( 'b.title_id', $filter_data['title_id'], 'uuid_list', $ph ) : null;

		$query .= ( isset( $filter_data['country'] ) ) ? $this->getWhereClauseSQL( 'b.country', $filter_data['country'], 'upper_text_list', $ph ) : null;
		$query .= ( isset( $filter_data['province'] ) ) ? $this->getWhereClauseSQL( 'b.province', $filter_data['province'], 'upper_text_list', $ph ) : null;

		$query .= ( isset( $filter_data['created_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.created_by', 'y.first_name', 'y.last_name' ], $filter_data['created_by'], 'user_id_or_name', $ph ) : null;
		$query .= ( isset( $filter_data['updated_by'] ) ) ? $this->getWhereClauseSQL( [ 'a.updated_by', 'z.first_name', 'z.last_name' ], $filter_data['updated_by'], 'user_id_or_name', $ph ) : null;

		$query .= ' AND ( a.deleted = 0 AND ab.deleted = 0 ) ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

//		Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);

		$this->rs = $this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

}

?>
